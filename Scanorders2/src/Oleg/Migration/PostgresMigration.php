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
// rename after extends "AbstractMigration" to "PostgresMigration"
class PostgresMigration extends AbstractMigration
{

    public function up(Schema $schema)
    {

    }

    public function down(Schema $schema)
    {

    }

    //addSql($sql, array $params = Array, array $types = Array)
    public function addSql( $sql, array $params = [], array $types = [] ) {
        //wrapper for addSql
        if( strpos($sql, ' ADD PRIMARY KEY ') !== false ) {
            return false;
        }

        //'ALTER INDEX idx_e573a753bdd0acfa RENAME TO IDX_6BE23A97726D9566'
        if( strpos($sql, ' RENAME TO IDX_') !== false ) {
            //has string
            //it's ok to rename with 5 zeros '00000': ALTER INDEX idx_c6f1cf80537a132900000 RENAME TO IDX_C6F1CF80537A1329
            if( strpos($sql, '00000') !== false ) {
                //has 00000
                $this->addParentSql($sql);
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

        $this->addParentSql($sql);
    }

    public function addParentSql($sql) {
        parent::addSql($sql);
    }


}