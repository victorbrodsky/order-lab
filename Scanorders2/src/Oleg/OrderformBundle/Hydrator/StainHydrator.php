<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Oleg\OrderformBundle\Hydrator;

use Doctrine\ORM\Internal\Hydration\AbstractHydrator;
use PDO;

class StainHydrator extends AbstractHydrator
{
    
    
//    protected function _hydrateAll()
//    {
//        return $this->_stmt->fetchAll(PDO::FETCH_ASSOC);
//    }
    
    protected function hydrateAllData_1()
    {
        //return $this->_stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $result = [];
        foreach($this->_stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $result[] = $row;
        }

        return $result;
    }
    
    protected function hydrateAllData()     
    {         
        $result = array();         
        $cache  = array();         
        foreach($this->_stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            print_r($row);
            echo "<br>";
            $this->hydrateRowData($row, $cache, $result);
        }

        return $result;
    }

    protected function hydrateRowData(array $row, array &$cache, array &$result)
    {
        if(count($row) == 0) {
            return false;
        }

        $keys = array_keys($row);

        // Assume first column is id field
        $id = $row[$keys[0]];

        $value = false;

        if(count($row) == 2) {
            // If only one more field assume that this is the value field
            $value = $row[$keys[1]];
        } else {
            // Remove ID field and add remaining fields as value array
            array_shift($row);
            $value = $row;
        }

        $result[$id] = $value;

        //echo "id=".$id."<br>";
        //echo "value=".$value."<br>";
        //print_r($result);
        //exit();
    }
    
    
}


