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

use App\UserdirectoryBundle\Entity\ListAbstract;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * One-off migration helper: convert the legacy PHP-serialized "updateAuthorRoles"
 * column (from the removed Doctrine "array" type) to JSON across EVERY table whose
 * entity extends ListAbstract (a MappedSuperclass, ~303 concrete entities).
 *
 * Tables are discovered dynamically from Doctrine metadata, so no table names are
 * hardcoded. All reads/writes use raw DBAL SQL and therefore bypass the entity
 * mapping (important: the field can be re-mapped to "json" independently of when
 * this runs).
 *
 * Run this BEFORE the schema migration that changes the column type to "json".
 *
 * Dry-run by default. Pass --apply to actually write.
 *
 *   php bin/console app:convert-list-roles-to-json
 *   php bin/console app:convert-list-roles-to-json --apply
 */
class ConvertListRolesToJsonCommand extends Command
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
            ->setName('app:convert-list-roles-to-json')
            ->setDescription('Convert legacy serialized updateAuthorRoles to JSON across all ListAbstract subclass tables')
            ->addOption('apply', null, InputOption::VALUE_NONE, 'Actually write changes (default is dry-run)')
            ->addOption('alter-schema', null, InputOption::VALUE_NONE, 'Also ALTER each column to native json type (requires --apply)')
            ->addOption('field', null, InputOption::VALUE_REQUIRED, 'Entity field to convert', 'updateAuthorRoles');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $apply = (bool) $input->getOption('apply');
        $alterSchema = (bool) $input->getOption('alter-schema');
        $field = $input->getOption('field');

        if ($alterSchema && !$apply) {
            $output->writeln('<error>--alter-schema requires --apply (data must be converted before the type change).</error>');
            return Command::FAILURE;
        }

        $connection = $this->em->getConnection();
        $platform = $connection->getDatabasePlatform();
        $isPostgres = stripos($platform::class, 'PostgreSQL') !== false;

        //Open a single CSV backup for all tables
        $logsDir = $this->container->get('kernel')->getLogDir();
        $backupFile = $logsDir . DIRECTORY_SEPARATOR . 'list_' . strtolower($field) . '_backup_' . date('Ymd_His') . '.csv';
        $handle = fopen($backupFile, 'w');
        fputcsv($handle, array('table', 'id', 'value_original'));

        //Discover all concrete entities that extend ListAbstract
        $allMeta = $this->em->getMetadataFactory()->getAllMetadata();

        $tablesProcessed = 0;
        $rowsTotal = 0;
        $rowsConverted = 0;
        $rowsAlreadyJson = 0;
        $rowsFailed = 0;
        $tablesSkipped = 0;
        $tablesAltered = 0;

        foreach ($allMeta as $meta) {
            if ($meta->isMappedSuperclass) {
                continue;
            }

            $refl = $meta->getReflectionClass();
            if (!$refl || !$refl->isSubclassOf(ListAbstract::class)) {
                continue;
            }

            //Field must exist and be mapped on this entity
            if (!$meta->hasField($field)) {
                $tablesSkipped++;
                continue;
            }

            $tableName = $meta->getTableName();
            $columnName = $meta->getColumnName($field);
            $idColumn = $meta->getSingleIdentifierColumnName();

            $quotedTable = $platform->quoteIdentifier($tableName);
            $quotedColumn = $platform->quoteIdentifier($columnName);
            $quotedId = $platform->quoteIdentifier($idColumn);

            $rows = $connection->fetchAllAssociative(
                "SELECT $quotedId AS id, $quotedColumn AS val FROM $quotedTable WHERE $quotedColumn IS NOT NULL"
            );

            $tablesProcessed++;

            foreach ($rows as $row) {
                $id = $row['id'];
                $raw = $row['val'];

                $rowsTotal++;

                //Backup original value first
                fputcsv($handle, array($tableName, $id, $raw));

                //Skip values already valid JSON (idempotent / re-runnable)
                $decoded = json_decode($raw, true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                    $rowsAlreadyJson++;
                    continue;
                }

                //Legacy "array" type stored PHP serialize() output
                $value = @unserialize($raw);
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
                $tablesAltered++;
            }

            $output->writeln(sprintf(
                ' - %s (%s): %d row(s)%s',
                $tableName,
                $columnName,
                count($rows),
                $alterSchema ? ' [altered to json]' : ''
            ));
        }

        fclose($handle);

        $output->writeln('');
        $output->writeln(sprintf('Mode:                 %s', $apply ? 'APPLIED' : 'DRY-RUN (no DB changes; add --apply to write)'));
        $output->writeln(sprintf('Field:                %s', $field));
        $output->writeln(sprintf('Backup file:          %s', $backupFile));
        $output->writeln(sprintf('Tables processed:     %d', $tablesProcessed));
        $output->writeln(sprintf('Tables skipped:       %d', $tablesSkipped));
        $output->writeln(sprintf('Tables altered:       %d', $tablesAltered));
        $output->writeln(sprintf('Rows total:           %d', $rowsTotal));
        $output->writeln(sprintf('Rows converted%s: %d', $apply ? '' : ' (would)', $rowsConverted));
        $output->writeln(sprintf('Rows already JSON:    %d', $rowsAlreadyJson));
        $output->writeln(sprintf('Unserialize failures: %d', $rowsFailed));

        return Command::SUCCESS;
    }
}
