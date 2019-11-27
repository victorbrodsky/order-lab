<?php

/**
 * Created by PhpStorm.
 * User: ch3
 * Date: 11/26/2019
 * Time: 3:31 PM
 */

namespace Oleg\Migration;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

//In VersionYYYYMMDDHHMM.php
//1) Add "use Oleg\Migration\PostgresMigration;"
//2) Rename after extends "AbstractMigration" to "PostgresMigration"
//3) Rename [addSql] to [processSql($schema,]
class PostgresMigration extends AbstractMigration
{

    public function up(Schema $schema){}
    public function down(Schema $schema){}

    //TODO: check if index exists in schema
    //https://www.doctrine-project.org/projects/doctrine-dbal/en/2.9/reference/schema-manager.html
    public function indexExists($sql,$schema) {

        $sqlIndex = null;
        $sqlArr = explode(" ",$sql);

        //Case: DROP INDEX idx_d267b39c33f7837
        if( strpos($sql, 'DROP INDEX ') !== false ) {
            if( count($sqlArr) == 3 ) {
                //We need the index 3
                $sqlIndex = $sqlArr[2];
            }
        }

        //ALTER INDEX idx_15b668721aca1422 RENAME TO IDX_5AFC0F4BCD46F646
        if( strpos($sql, 'ALTER INDEX ') !== false && strpos($sql, ' RENAME TO ') !== false ) {
            if( count($sqlArr) == 6 ) {
                //We need the index 3
                $sqlIndex = $sqlArr[2];
            }
        }

        //CREATE UNIQUE INDEX UNIQ_D267B39C33F7837 ON calllog_calllogentrymessage_document (document_id)
        if( strpos($sql, 'CREATE UNIQUE INDEX ') !== false && strpos($sql, ' ON ') !== false ) {
            if( count($sqlArr) > 7 ) {
                //We need the index 4
                $sqlIndex = $sqlArr[3];
            }
        }

        //Skip:
        //ALTER TABLE calllog_calllogentrymessage_document ADD PRIMARY KEY (message_id, document_id)

        //index from sql not found => assume it does not exist
        if( !$sqlIndex ) {
            return false;
        }

        return $this->indexExistsSimple($sqlIndex,$schema);
    }
    public function indexExistsSimple($sqlIndex,$schema) {
        $tables = $schema->listTables();
        //ALTER INDEX idx_15b668721aca1422 RENAME TO IDX_5AFC0F4BCD46F646
        foreach ($tables as $table) {
            $indexes = $schema->listTableIndexes($table->getName());
            foreach ($indexes as $index) {
                //echo $index->getName() . ': ' . ($index->isUnique() ? 'unique' : 'not unique') . "\n";
                if( $sqlIndex == $index ) {
                    return true;
                }
            }
        }
        return false;
    }

    public function processSql($schema,$sql) {
        $this->processComplexSql($sql,$schema);
    }
    public function processSimpleSql($sql) {
        $this->processComplexSql($sql,null);
    }

    //TODO: Skip a statement in a Doctrine migration if a index is present
    //https://stackoverflow.com/questions/49897499/skip-a-statement-in-a-doctrine-migration-if-a-column-is-present
    public function processComplexSql($sql,$schema=null) {
        //wrapper for processSql

        $newline = "\r\n";

        //Always skip: An exception occurred while executing 'DROP INDEX "primary"':
        if( $sql == 'DROP INDEX "primary"' ) {
            echo "Ignore ".$sql.$newline;
            return FALSE;
        }
//        if( strpos($sql, 'DROP INDEX ') !== false && strpos($sql, 'primary') !== false ) {
//            return false;
//        }

        //Always skip: Primary keys are already exists
        if( strpos($sql, ' ADD PRIMARY KEY ') !== FALSE ) {
            echo "Ignore ".$sql.$newline;
            return FALSE;
        }

        if( $this->indexExists($sql,$schema) === FALSE ) {
            echo "Ignore " . $sql . $newline;
            return FALSE;
        }

        if(0) {
            //TODO: Check if exists
            //ALTER INDEX idx_e573a753bdd0acfa RENAME TO IDX_6BE23A97726D9566
            if (strpos($sql, ' RENAME TO IDX_') !== FALSE) {
                //has string
                //it's ok to rename with 5 zeros '00000': ALTER INDEX idx_c6f1cf80537a132900000 RENAME TO IDX_C6F1CF80537A1329
                if (strpos($sql, '00000') !== FALSE) {
                    //has 00000
                    $this->addSql($sql);
                    return FALSE;
                } else {
                    //does not have 00000
                    echo "Ignore " . $sql . $newline;
                    return FALSE;
                }
            }

            //TODO: Check if exists
            //DROP INDEX IDX_22984163C33F7837
            if (strpos($sql, 'DROP INDEX IDX_') !== false) {
                return false;
            }
        }

        $this->addSql($sql);
    }


    //addSql($sql, array $params = Array, array $types = Array)
    //public function processSql( $sql, array $params = [], array $types = [] ) {

    public function processSql_old( $sql ) {
        //wrapper for addSql

        //An exception occurred while executing 'DROP INDEX "primary"':
//        if( $sql == 'DROP INDEX "primary"' ) {
//            return false;
//        }
        if( strpos($sql, 'DROP INDEX ') !== false && strpos($sql, 'primary') !== false ) {
            return false;
        }

        if( strpos($sql, ' ADD PRIMARY KEY ') !== false ) {
            return false;
        }

        //'ALTER INDEX idx_e573a753bdd0acfa RENAME TO IDX_6BE23A97726D9566'
        if( strpos($sql, ' RENAME TO IDX_') !== false ) {
            //has string
            //it's ok to rename with 5 zeros '00000': ALTER INDEX idx_c6f1cf80537a132900000 RENAME TO IDX_C6F1CF80537A1329
            if( strpos($sql, '00000') !== false ) {
                //has 00000
                $this->addSql($sql);
                return false;
            } else {
                //does not have 00000
                echo "ignore ".$sql;
                return false;
            }
        }

        //Drop index DROP INDEX IDX_22984163C33F7837
        if( strpos($sql, 'DROP INDEX IDX_') !== false ) {
            return false;
        }

        $this->addSql($sql);
    }



}