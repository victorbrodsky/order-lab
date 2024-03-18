<?php

/**
 * Created by PhpStorm.
 * User: Oleg Ivanov oli2002
 * Date: 01/24/2024
 * Time: 12:28 PM
 */

if (!function_exists('getDBParameter')) {
    function getDBParameter($inputRow, $originalParam, $name)
    {
        //dump($inputRow);exit('222');
        $row = array();
        if( is_array($inputRow) && count($inputRow) > 0 ) {
            $row = $inputRow[0];
        }

        //1) try as it is
        if (array_key_exists($name, $row)) {
            //echo "1 parameter=".$row[$name]."<br>";
            return trim((string)$row[$name]);
        }

        //2) try with lowercase for postgresql
        $name = strtolower($name);
        if (array_key_exists($name, $row)) {
            //echo "2 parameter=".$row[$name]."<br>";
            return trim((string)$row[$name]);
        }

        return $originalParam;
    }
}
if (!function_exists('isWindows')) {
    function isWindows()
    {
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            //Windows
            return true;
        }
        return false;
    }
}
if (!function_exists('getNestedTreeBreadCrumb')) {
    function getNestedTreeBreadCrumb( $nodeId, $conn, $urlSlug='' )
    {
        //Get parent's abbreviation
        $hostedGroupSql = "SELECT * FROM " . 'user_hostedusergrouplist' .
            " WHERE id=$nodeId AND (type='default' OR type='user-added')";
        $hostedGroup = $conn->executeQuery($hostedGroupSql);
        $hostedGroupRows = $hostedGroup->fetchAllAssociative();
        //dump($hostedGroupRows);
        $id = $hostedGroupRows[0]['id'];
        $parentId = $hostedGroupRows[0]['parent_id'];
        $thisUrlSlug = $hostedGroupRows[0]['urlslug'];
        //echo '$thisUrlSlug='.$thisUrlSlug."<br>";
        if( $thisUrlSlug ) {
            //$urlSlug = $thisUrlSlug . '/' . $urlSlug;   //not working with c/wcm/pathology
            $urlSlug = $thisUrlSlug . '-' . $urlSlug; //working with c-wcm-pathology
        }
        if( $parentId ) {
            $urlSlug = getNestedTreeBreadCrumb($parentId,$conn,$urlSlug);
        }
        //$urlSlug = rtrim($urlSlug, "/");
        $urlSlug = rtrim($urlSlug, "-");
        //exit('getNestedTreeBreadCrumb, $urlSlug='.$urlSlug);

//            //Set id of this hosted user group
//            $container->setParameter($urlSlug."-id",$id);
//            $container->setParameter($urlSlug."-databaseHost",      $hostedGroupRows[0]['databaseHost']);
//            $container->setParameter($urlSlug."-databasePort",      $hostedGroupRows[0]['databasePort']);
//            $container->setParameter($urlSlug."-databaseName",      $hostedGroupRows[0]['databaseName']);
//            $container->setParameter($urlSlug."-databaseUser",      $hostedGroupRows[0]['databaseUser']);
//            $container->setParameter($urlSlug."-databasePassword",  $hostedGroupRows[0]['databasePassword']);

        return $urlSlug;
    }
}


//if (!function_exists('connectDBParameter')) {
//    function connectDBParameter($systemdbConnectionParams, $config)
//    {
//        $conn = \Doctrine\DBAL\DriverManager::getConnection($systemdbConnectionParams, $config);
//        return $conn;
//    }
//}
