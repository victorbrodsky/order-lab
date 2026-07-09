<?php
/**
 * Copyright (c) 2017 Cornell University
 *
 *  Licensed under the Apache License, Version 2.0 (the "License");
 *  you may not use this file except in compliance with the License.
 *  You may obtain a copy of the License at
 *
 *  http://www.apache.org/licenses/LICENSE-2.0
 *
 *  Unless required by applicable law or agreed to in writing, software
 *  distributed under the License is distributed on an "AS IS" BASIS,
 *  WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 *  See the License for the specific language governing permissions and
 *  limitations under the License.
 */

namespace App\UserdirectoryBundle\Command;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * General one-off migration helper: convert legacy PHP-serialized column(s)
 * (from the removed Doctrine "array" type) to JSON.
 *
 * Target either a single entity (--class) or every mapped entity (--all).
 * By default the command auto-detects columns whose Doctrine type is "array"
 * or "json" (json is included because a field may already be re-mapped to json
 * in code while its rows are still serialized in the DB). Restrict to one field
 * with --field.
 *
 * All reads/writes use raw DBAL SQL and bypass the entity mapping. The per-row
 * logic is idempotent: rows that are already valid JSON are skipped, serialized
 * rows are converted.
 *
 * Dry-run by default. Pass --apply to write. Pass --alter-schema (requires
 * --apply) to also change the column type to native json.
 *
 *   php bin/console app:convert-array-to-json --class=Logger
 *   php bin/console app:convert-array-to-json --class=Logger --apply --alter-schema
 *   php bin/console app:convert-array-to-json --all --apply --alter-schema
 */
class ConvertArrayToJsonCommand extends Command
{
    private $container;
    private $em;

    public function __construct(ContainerInterface $container, EntityManagerInterface $em)
    {
        parent::__construct();

        $this->container = $container;
        $this->em = $em;
    }

    protected function configure(): void
    {
        $this
            ->setName('app:convert-array-to-json')
            ->setDescription('Convert legacy serialized array column(s) to JSON for one entity (--class) or all (--all)')
            ->addOption('class', null, InputOption::VALUE_REQUIRED, 'Target entity: short name (e.g. Logger) or FQCN')
            ->addOption('all', null, InputOption::VALUE_NONE, 'Target every mapped entity')
            ->addOption('field', null, InputOption::VALUE_REQUIRED, 'Restrict to a single field (default: auto-detect array/json fields)')
            ->addOption('apply', null, InputOption::VALUE_NONE, 'Actually write changes (default is dry-run)')
            ->addOption('alter-schema', null, InputOption::VALUE_NONE, 'Also ALTER column(s) to native json type (requires --apply)');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $classOpt = $input->getOption('class');
        $all = (bool) $input->getOption('all');
        $fieldOpt = $input->getOption('field');
        $apply = (bool) $input->getOption('apply');
        $alterSchema = (bool) $input->getOption('alter-schema');

        if (!$all && !$classOpt) {
            $output->writeln('<error>Specify a target: --class=<Entity> or --all.</error>');
            return Command::FAILURE;
        }
        if ($alterSchema && !$apply) {
            $output->writeln('<error>--alter-schema requires --apply (data must be converted before the type change).</error>');
            return Command::FAILURE;
        }

        $connection = $this->em->getConnection();
        $platform = $connection->getDatabasePlatform();
        $isPostgres = stripos($platform::class, 'PostgreSQL') !== false;

        //Resolve target metadata
        $allMeta = $this->em->getMetadataFactory()->getAllMetadata();
        $targets = array();
        $superclassNames = array();

        foreach ($allMeta as $meta) {
            if ($all) {
                if ($meta->isMappedSuperclass) {
                    continue;
                }
                $targets[] = $meta;
                continue;
            }

            $refl = $meta->getReflectionClass();
            $isRequested = $meta->getName() === $classOpt || ($refl && $refl->getShortName() === $classOpt);

            if ($isRequested && !$meta->isMappedSuperclass) {
                $targets[] = $meta;
            } elseif ($isRequested && $meta->isMappedSuperclass) {
                $superclassNames[] = $meta->getName();
            }
        }

        //If a MappedSuperclass was requested, expand to all concrete subclasses
        if (count($superclassNames) > 0) {
            foreach ($allMeta as $meta) {
                if ($meta->isMappedSuperclass) {
                    continue;
                }
                foreach ($superclassNames as $superName) {
                    if (in_array($superName, $meta->parentClasses, true) || is_subclass_of($meta->getName(), $superName)) {
                        $targets[] = $meta;
                        break;
                    }
                }
            }
        }

        //De-duplicate targets
        $seen = array();
        $uniqueTargets = array();
        foreach ($targets as $meta) {
            if (!isset($seen[$meta->getName()])) {
                $seen[$meta->getName()] = true;
                $uniqueTargets[] = $meta;
            }
        }
        $targets = $uniqueTargets;

        if (!$all && count($targets) === 0) {
            $output->writeln(sprintf('<error>No mapped entity matched --class=%s</error>', $classOpt));
            return Command::FAILURE;
        }

        //Open a single CSV backup
        $logsDir = $this->container->get('kernel')->getLogDir();
        $label = $all ? 'all' : preg_replace('/[^A-Za-z0-9]+/', '_', (string) $classOpt);
        $backupFile = $logsDir . DIRECTORY_SEPARATOR . 'array_to_json_backup_' . $label . '_' . date('Ymd_His') . '.csv';
        $handle = fopen($backupFile, 'w');
        fputcsv($handle, array('table', 'column', 'id', 'value_original'));

        $columnsProcessed = 0;
        $columnsAltered = 0;
        $rowsTotal = 0;
        $rowsConverted = 0;
        $rowsAlreadyJson = 0;
        $rowsFailed = 0;

        foreach ($targets as $meta) {
            //Determine which fields to convert
            $fields = array();
            if ($fieldOpt) {
                if ($meta->hasField($fieldOpt)) {
                    $fields[] = $fieldOpt;
                }
            } else {
                foreach ($meta->getFieldNames() as $fn) {
                    $type = $meta->getTypeOfField($fn);
                    if ($type === 'array' || $type === 'json') {
                        $fields[] = $fn;
                    }
                }
            }

            if (count($fields) === 0) {
                continue;
            }

            $tableName = $meta->getTableName();
            $idColumn = $meta->getSingleIdentifierColumnName();
            $quotedTable = $platform->quoteIdentifier($tableName);
            $quotedId = $platform->quoteIdentifier($idColumn);

            foreach ($fields as $field) {
                $columnName = $meta->getColumnName($field);
                $quotedColumn = $platform->quoteIdentifier($columnName);

                $rows = $connection->fetchAllAssociative(
                    "SELECT $quotedId AS id, $quotedColumn AS val FROM $quotedTable WHERE $quotedColumn IS NOT NULL"
                );

                $columnsProcessed++;
                $colConverted = 0;

                foreach ($rows as $row) {
                    $id = $row['id'];
                    $raw = $row['val'];

                    $rowsTotal++;

                    //Backup original value first
                    fputcsv($handle, array($tableName, $columnName, $id, $raw));

                    //Skip values already valid JSON (idempotent / re-runnable)
                    $decoded = json_decode((string) $raw, true);
                    if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                        $rowsAlreadyJson++;
                        continue;
                    }

                    //Legacy "array" type stored PHP serialize() output
                    $value = @unserialize((string) $raw);
                    if ($value === false && $raw !== 'b:0;') {
                        $rowsFailed++;
                        $value = array();
                    }
                    if (!is_array($value)) {
                        $value = ($value === null) ? array() : array($value);
                    }

                    if ($apply) {
                        $connection->executeStatement(
                            "UPDATE $quotedTable SET $quotedColumn = :val WHERE $quotedId = :id",
                            array('val' => json_encode(array_values($value)), 'id' => $id)
                        );
                    }
                    $rowsConverted++;
                    $colConverted++;
                }

                //Convert the column type to native json AFTER its data is valid JSON
                if ($alterSchema) {
                    if ($isPostgres) {
                        $connection->executeStatement(
                            "ALTER TABLE $quotedTable ALTER $quotedColumn TYPE JSON USING $quotedColumn::json"
                        );
                        //Drop the legacy (DC2Type:array) doctrine type comment
                        $connection->executeStatement(
                            "COMMENT ON COLUMN $quotedTable.$quotedColumn IS NULL"
                        );
                    } else {
                        $connection->executeStatement(
                            "ALTER TABLE $quotedTable MODIFY $quotedColumn JSON"
                        );
                    }
                    $columnsAltered++;
                }

                $output->writeln(sprintf(
                    ' - %s.%s: %d row(s), %d converted%s',
                    $tableName,
                    $columnName,
                    count($rows),
                    $colConverted,
                    $alterSchema ? ' [altered to json]' : ''
                ));
            }
        }

        fclose($handle);

        $output->writeln('');
        $output->writeln(sprintf('Mode:                 %s', $apply ? 'APPLIED' : 'DRY-RUN (no DB changes; add --apply to write)'));
        $output->writeln(sprintf('Target:               %s', $all ? 'ALL entities' : $classOpt));
        $output->writeln(sprintf('Backup file:          %s', $backupFile));
        $output->writeln(sprintf('Columns processed:    %d', $columnsProcessed));
        $output->writeln(sprintf('Columns altered:      %d', $columnsAltered));
        $output->writeln(sprintf('Rows total:           %d', $rowsTotal));
        $output->writeln(sprintf('Rows converted%s: %d', $apply ? '' : ' (would)', $rowsConverted));
        $output->writeln(sprintf('Rows already JSON:    %d', $rowsAlreadyJson));
        $output->writeln(sprintf('Unserialize failures: %d', $rowsFailed));

        return Command::SUCCESS;
    }
}
