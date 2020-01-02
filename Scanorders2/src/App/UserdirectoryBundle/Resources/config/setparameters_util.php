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

//include_once('setparameters_function.php');

function getDBParameter( $row, $originalParam, $name ) {
//    if( strtoupper(substr(PHP_OS, 0, 3)) === 'WIN' ) {
//        //keep it for MSSQL
//    } else {
//        $name = strtolower($name);
//    }

    //1) try as it is
    if( array_key_exists($name, $row) ) {
        //echo "1 parameter=".$row[$name]."<br>";
        return trim($row[$name]);
    }

    //2) try with lowercase for postgresql
    $name = strtolower($name);
    if( array_key_exists($name, $row) ) {
        //echo "2 parameter=".$row[$name]."<br>";
        return trim($row[$name]);
    }

    return $originalParam;
}

function isWindows() {
    if( strtoupper(substr(PHP_OS, 0, 3)) === 'WIN' ) {
        //Windows
        return true;
    }
    return false;
}


