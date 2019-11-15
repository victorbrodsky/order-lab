<?php
/**
 * Created by PhpStorm.
 * User: ch3
 * Date: 11/15/2019
 * Time: 11:46 AM
 */

namespace Oleg\UserdirectoryBundle\Controller;


use Doctrine\DBAL\Schema\PostgreSqlSchemaManager as PostgreSqlSchemaManagerBaseController;

class PostgreSqlSchemaManager extends PostgreSqlSchemaManagerBaseController
{
    
    /**
     * {@inheritdoc}
     */
    protected function _getPortableSequenceDefinition($sequence)
    {
        if ($sequence['schemaname'] != 'public') {
            $sequenceName = $sequence['schemaname'] . "." . $sequence['relname'];
        } else {
            $sequenceName = $sequence['relname'];
        }

        //$data = $this->_conn->fetchAll('SELECT min_value, increment_by FROM ' . $this->_platform->quoteIdentifier($sequenceName));
        //FIX ISSUE: SELECT min_value, increment_by FROM (https://github.com/doctrine/dbal/issues/2868)
        $version = floatval($this->_conn->getWrappedConnection()->getServerVersion());
        if ($version >= 10) {
            $data = $this->_conn->fetchAll('SELECT min_value, increment_by FROM pg_sequences WHERE schemaname = \'public\' AND sequencename = '.$this->_conn->quote($sequenceName));
        }
        else
        {
            $data = $this->_conn->fetchAll('SELECT min_value, increment_by FROM ' . $this->_platform->quoteIdentifier($sequenceName));
        }


        return new Sequence($sequenceName, $data[0]['increment_by'], $data[0]['min_value']);
    }

}