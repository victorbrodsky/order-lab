<?php

/**
 * Created by PhpStorm.
 * User: ch3
 * Date: 11/26/2019
 * Time: 3:31 PM
 */

namespace App\Migration;

//use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

//Update:   php bin/console doctrine:schema:update --force
//Status:   php bin/console doctrine:migrations:status

//Pre-Generating: php bin/console cache:clear
//Pre-Generating: php bin/console doctrine:cache:clear-metadata

//Status: php bin/console doctrine:migrations:status
//Generate: php bin/console doctrine:migrations:diff

//Migrate:  php bin/console doctrine:migrations:migrate --all-or-nothing

//Skip:     php bin/console doctrine:migrations:version YYYYMMDDHHMMSS --add

//In VersionYYYYMMDDHHMM.php
//1) Add "use App\Migration\PostgresMigration;"
//2) Rename after extends "AbstractMigration" to "PostgresMigration":
//   sed -i -e "s/AbstractMigration/PostgresMigration/g" Version....php
//3) Rename [addSql] to [processSql]:
//   sed -i -e "s/addSql/processSql/g" Version....php
class PostgresMigration extends AbstractMigration implements ContainerAwareInterface
{

    private $container;
    private $indexArr = array();
    private $foreignkeyArr = array();
    private $counter = 0;

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    public function up(Schema $schema) : void {}
    public function down(Schema $schema) : void {}

    public function createIndexArr() {
        $newline = "\n";
        $em = $this->container->get('doctrine.orm.entity_manager');
        $sm = $em->getConnection()->getSchemaManager();
        $tables = $sm->listTables();
        //ALTER INDEX idx_15b668721aca1422 RENAME TO IDX_5AFC0F4BCD46F646
        foreach ($tables as $table) {
            $indexes = $sm->listTableIndexes($table->getName());
            foreach ($indexes as $index) {
                //echo $index->getName() . ': ' . ($index->isUnique() ? 'unique' : 'not unique') . "\n";
                $this->indexArr[$index->getName()] = $table->getName();
            }
            $foreignkeys = $sm->listTableForeignKeys($table->getName());
            foreach ($foreignkeys as $foreignkey) {
                //echo $foreignkey->getName() . ': ' . ($foreignkey->isUnique() ? 'unique' : 'not unique') . "\n";
                $this->foreignkeyArr[$foreignkey->getName()] = $table->getName();
            }
        }
        echo "Found " . count($this->indexArr) . " indexes in " . count($tables) . " tables" . $newline;
        echo "Found " . count($this->foreignkeyArr) . " foreign keys in " . count($tables) . " tables" . $newline;
    }


    public function processSql($sql) {

        if( count($this->indexArr) == 0 ) {
            $this->createIndexArr();
        }

        $this->counter++;


        $newline = "\n";

        //Always skip: An exception occurred while executing 'DROP INDEX "primary"':
        if( $sql == 'DROP INDEX "primary"' ) {
            echo $this->counter.":###Ignore1 ".$sql.$newline;
            return FALSE;
        }

        //CREATE SEQUENCE transres_committeereview_id_seq
//        if( strpos($sql, 'CREATE SEQUENCE ') !== false ) {
//            echo $this->counter.":###Ignore2 ".$sql.$newline;
//            return FALSE;
//        }

        //Case: DROP INDEX idx_d267b39c33f7837
        if( strpos($sql, 'DROP INDEX ') !== false ) {
            $sqlArr = explode(" ",$sql);
            if( count($sqlArr) == 3 ) {
                //We need the index 3
                $sqlIndex = $sqlArr[2];
                if( !$this->indexExistsSimple($sqlIndex) ) {
                    return FALSE;
                }
            }
        }

        //ALTER TABLE calllog_calllogentrymessage_document ADD PRIMARY KEY (message_id, document_id)
        //Always skip: Primary keys are already exists
        if( strpos($sql, ' ADD PRIMARY KEY ') !== FALSE ) {
            echo $this->counter.":###Ignore3 ".$sql.$newline;
            return FALSE;
        }

        //ALTER INDEX idx_15b668721aca1422 RENAME TO IDX_5AFC0F4BCD46F646
        //ALTER INDEX uniq_821d2431c161af2500000 RENAME TO UNIQ_821D2431C161AF25
        if( strpos($sql, 'ALTER INDEX ') !== false && strpos($sql, ' RENAME TO ') !== false ) {
            $sqlArr = explode(" ",$sql);
            //if( count($sqlArr) == 6 ) {
                //We need the index 3
                $sqlIndex = $sqlArr[2];
                echo "!!!sqlIndex=[".$sqlIndex."]".$newline;
                if( !$this->indexExistsSimple($sqlIndex) ) {
                    return FALSE;
                }
            //}
        }

        //ALTER INDEX idx_7ecb11f7378898f400000 RENAME TO IDX_7ECB11F7378898F4

        //if( strpos($sql, 'idx_7ecb11f7378898f400000') !== false ) {
        //    exit("exit: ".$sql);
        //}

        echo $this->counter.": Process sql=".$sql.$newline;
        $this->addSql($sql);

    }

    public function indexExistsSimple($sqlIndex) {
        $newline = "\n";
        $processArr = array();
        $name = 'undefined key/index';

        $sqlIndex = trim($sqlIndex);

        if(
            strpos($sqlIndex, 'IDX_') !== false     ||
            strpos($sqlIndex, 'idx_') !== false     ||  //idx_15b668721aca1422
            strpos($sqlIndex, '_idx') !== false     ||    //CREATE INDEX oid_idx ON scan_message (oid)
            strpos($sqlIndex, 'uniq_') !== false    ||
            strpos($sqlIndex, 'UNIQ_') !== false
        ) {
            $processArr = $this->indexArr;
            $name = "index";
        }
        if( strpos($sqlIndex, 'FK_') !== false || strpos($sqlIndex, 'fk_') !== false ) {
            $processArr = $this->foreignkeyArr;
            $name = "foreign key";
        }

        //echo "processArr count=".count($processArr).$newline;

        foreach( $processArr as $index => $table ) {
            $index = trim($index);
            //echo $index->getName() . ': ' . ($index->isUnique() ? 'unique' : 'not unique') . "\n";
            if (strtolower($sqlIndex) == strtolower($index)) {
                echo $this->counter . ": Found $name=" . $sqlIndex . " (" . $table . ")." . $newline;
                return true;
            }
        }
        echo $this->counter . ": NotFound $name=" . $sqlIndex . $newline;

        return false;
    }


















}