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

/**
 * Created by PhpStorm.
 * User: ch3
 * Date: 6/29/2017
 * Time: 11:23 AM
 */

namespace App\UserdirectoryBundle\Controller;


use Doctrine\DBAL\Configuration;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;

class DataBackupManagementController extends Controller
{

    /**
     * Resources:
     * https://blogs.msdn.microsoft.com/brian_swan/2010/07/01/restoring-a-sql-server-database-from-php/
     * https://channaly.wordpress.com/2012/01/31/backup-and-restoring-mssql-database-with-php/
     * https://blogs.msdn.microsoft.com/brian_swan/2010/04/06/backup-and-restore-a-database-with-the-sql-server-driver-for-php/
     * Bundle (no MSSQL): https://github.com/dizda/CloudBackupBundle
     *
     * Table specific backup/restore:
     * http://www.php-mysql-tutorial.com/wikis/mysql-tutorials/using-php-to-backup-mysql-databases.aspx
     * https://www.phpclasses.org/package/5761-PHP-Dump-a-Microsoft-SQL-server-database.html#view_files/files/29084
     *
     * @Route("/data-backup-management/", name="employees_data_backup_management")
     * @Template("AppUserdirectoryBundle/DataBackup/data_backup_management.html.twig")
     * @Method("GET")
     */
    public function dataBackupManagementAction(Request $request) {

        if( false === $this->get('security.authorization_checker')->isGranted('ROLE_PLATFORM_ADMIN') ) {
            return $this->redirect( $this->generateUrl('employees-nopermission') );
        }

        //networkDrivePath
        $userSecUtil = $this->container->get('user_security_utility');
        $networkDrivePath = $userSecUtil->getSiteSettingParameter('networkDrivePath');
        if( !$networkDrivePath ) {
            //exit("No networkDrivePath is defined");
            $this->get('session')->getFlashBag()->add(
                'pnotify-error',
                "Cannot continue with Backup: No Network Drive Path is defined in the Site Settings"
            );
            return $this->redirect($this->generateUrl('employees_home'));
        }

        $sitename = "employees";

        //get backup files
        $backupFiles = $this->getBackupFiles($networkDrivePath);

        return array(
            'sitename' => $sitename,
            'title' => "Data Backup Management",
            'cycle' => 'new',
            'networkDrivePath' => $networkDrivePath,
            'backupFiles' => $backupFiles
        );
    }


    /**
     * //@Template("AppUserdirectoryBundle/DataBackup/create_backup.html.twig")
     *
     * @Route("/create-backup/", name="employees_create_backup")
     * @Template("AppUserdirectoryBundle/DataBackup/data_backup_management.html.twig")
     * @Method("GET")
     */
    public function createBackupAction(Request $request) {

        if( false === $this->get('security.authorization_checker')->isGranted('ROLE_PLATFORM_ADMIN') ) {
            return $this->redirect( $this->generateUrl('employees-nopermission') );
        }

        //networkDrivePath
        $userSecUtil = $this->container->get('user_security_utility');
        $networkDrivePath = $userSecUtil->getSiteSettingParameter('networkDrivePath');
        if( !$networkDrivePath ) {
            //exit("No networkDrivePath is defined");
            $this->get('session')->getFlashBag()->add(
                'pnotify-error',
                "Cannot continue with Backup: No Network Drive Path is defined in the Site Settings"
            );
            return $this->redirect($this->generateUrl('employees_data_backup_management'));
        }

        $em = $this->getDoctrine()->getManager();
        $sitename = "employees";


        if( $networkDrivePath ) {

            //create backup
            //$backupfile = "c:\\backup\\test.bak";
            //$networkDrivePath = "c:\\backup\\";
            $res = $this->creatingBackupSQLFull($networkDrivePath);
            //exit($res);

            $this->get('session')->getFlashBag()->add(
                'notice',
                $res
            );

            return $this->redirect($this->generateUrl('employees_data_backup_management'));
        }


        $this->get('session')->getFlashBag()->add(
            'pnotify-error',
            "Error backup"
        );

        return $this->redirect($this->generateUrl('employees_data_backup_management'));
//        return array(
//            //'form' => $form->createView(),
//            'sitename' => $sitename,
//            'title' => "Create Backup",
//            'cycle' => 'new'
//        );
    }


    /**
     * @Route("/restore-backup/{backupFilePath}", name="employees_restore_backup", options={"expose"=true})
     * @Template("AppUserdirectoryBundle/DataBackup/data_backup_management.html.twig")
     * @Method("GET")
     */
    public function restoreBackupAction( Request $request, $backupFilePath ) {

        if( false === $this->get('security.authorization_checker')->isGranted('ROLE_PLATFORM_ADMIN') ) {
            return $this->redirect( $this->generateUrl('employees-nopermission') );
        }

        $hostname = $request->getSchemeAndHttpHost();
        echo "hostname=$hostname<br>";
        if( strpos($hostname, 'med.cornell.edu') !== false ) {
            exit("Under construction!!!");
        }
        exit('Under construction!!!');

        //networkDrivePath
        $userSecUtil = $this->container->get('user_security_utility');
        $networkDrivePath = $userSecUtil->getSiteSettingParameter('networkDrivePath');
        if( !$networkDrivePath ) {
            //exit("No networkDrivePath is defined");
            $this->get('session')->getFlashBag()->add(
                'error',
                "Cannot continue with Backup: No Network Drive Path is defined in the Site Settings"
            );
            return $this->redirect($this->generateUrl('employees_data_backup_management'));
        }

        echo "backupFilePath=".$backupFilePath."<br>";

        //get backup files
        $backupFiles = $this->getBackupFiles($networkDrivePath);

        $sitename = "employees";

        if( $backupFilePath ) {

            //create backup
            $res = $this->restoringBackupSQLFull($networkDrivePath);
            //exit($res);

            $this->get('session')->getFlashBag()->add(
                'notice',
                $res
            );

            $this->get('session')->getFlashBag()->add(
                'pnotify',
                //"DB has been restored by backup ".$backupFilePath
                $res
            );

            return $this->redirect($this->generateUrl('employees_data_backup_management'));
        }

        return array(
            'sitename' => $sitename,
            'title' => "Data Backup Management",
            'cycle' => 'new',
            'networkDrivePath' => $networkDrivePath,
            'backupFiles' => $backupFiles
        );
    }




    ///////////////// UTIL METHODS /////////////////////

    public function getBackupFiles( $networkDrivePath ) {
        if( !$networkDrivePath ) {
            return null;
        }

        if (file_exists($networkDrivePath)) {
            //echo "The file $networkDrivePath exists";
        } else {
            //echo "The file $networkDrivePath does not exist";
            return null;
        }

        $file0 = array("id"=>null,"name"=>"");
//        $file1 = array("id"=>1,"name"=>"file 1");
//        $file2 = array("id"=>2,"name"=>"file 2");
//        $backupFiles = array($file0,$file1,$file2);

        $backupFiles = array($file0);

        //$files = scandir($networkDrivePath); //with dots
        $files = array_diff(scandir($networkDrivePath), array('..', '.'));

        foreach( $files as $file ) {
            $fileOption = array("id"=>$file,"name"=>$file);
            $backupFiles[] = $fileOption;
        }

        return $backupFiles;
    }

    public function getConnection() {
//        $dbname = "ScanOrder";
//        $uid = "symfony2";
//        $pwd = "symfony2";
//        $host = "127.0.0.1";
//        $driver = "pdo_sqlsrv";

        $dbname = $this->getParameter('database_name');
        $uid = $this->getParameter('database_user');
        $pwd = $this->getParameter('database_password');
        $host = $this->getParameter('database_host');
        $driver = $this->getParameter('database_driver');
        $serverName = gethostname();    //"COLLAGE";
        echo "serverName=".$serverName."<br>";
        echo "driver=".$driver."<br>";
        //$pwd = $pwd."1";

        if( 1 ) {
            $connOptions = array("Database"=>$dbname, "UID"=>$uid, "PWD"=>$pwd);
            $conn = sqlsrv_connect($serverName, $connOptions); //it does not work for php > 5.3 ???

            //testing
//            $sql = "SELECT * FROM user_siteParameters";
//            echo "sql=".$sql."<br>";
//            $params = sqlsrv_query($conn, $sql);
//            $res = $params->fetch();
//            echo "env=".$res['environment']."<br>";
        }

        if( 0 ) {
            $config = new \Doctrine\DBAL\Configuration();
            $connectionParams = array(
                'dbname' => $dbname,
                'user' => $uid,
                'password' => $pwd,
                'host' => $host,
                'driver' => $driver,
            );
            $conn = \Doctrine\DBAL\DriverManager::getConnection($connectionParams, $config);

            //testing
            $sql = "SELECT * FROM user_siteParameters";
            echo "sql=".$sql."<br>";
            $params = $conn->query($sql); // Simple, but has several drawbacks
            $res = $params->fetch();
            echo "env=".$res['environment']."<br>";
        }

        if( $conn ) {
            echo "Connection established.<br />";
        }else{
            echo "Connection could not be established.<br />";
            die( print_r( sqlsrv_errors(), true));
        }



        return $conn;

        //$em = $this->getDoctrine()->getManager();
        //return $em->getConnection();
    }

    //SQL Server Database backup FULL
    //https://blogs.msdn.microsoft.com/brian_swan/2010/04/06/backup-and-restore-a-database-with-the-sql-server-driver-for-php/
    public function creatingBackupSQLFull( $filepath ) {
        $msg = null;
        $timePrefix = date("d-m-Y-H-i-s");
        //echo "timePrefix=".$timePrefix."<br>";
        //$timePrefix = str_replace(" ","_",$timePrefix);
        $conn = $this->getConnection();
        $dbname = $this->getParameter('database_name');
        echo "dbname=".$dbname."<br>";

        //$backupfile = "testbackup_$timePrefix.bak";
        //$backupfile = "c:\\backup\\testbackup_$timePrefix.bak";
        //$backupfile = $filepath . DIRECTORY_SEPARATOR . "testbackup_$timePrefix.bak";
        $backupfile = $filepath . "testbackup_$timePrefix.bak";
        echo "backupfile=".$backupfile."<br>";

        //create file on disk
        //$myfile = fopen($backupfile, "w") or die("Unable to open file!");
        //fclose($backupfile);
        //touch($backupfile);
        //chmod($backupfile, 777);

        //$em = $this->getDoctrine()->getManager();
        sqlsrv_configure( "WarningsReturnAsErrors", 0 );

//        ////////////////// 1) make sure that the recovery model of your database is set to FULL (Requires log backups.) ////////////////////
//        $setRecovery = false;
//        if( $setRecovery ) {
//            $sql = "ALTER DATABASE $dbname SET RECOVERY FULL";
//            $stmt = sqlsrv_query($conn, $sql);
//            if ($stmt === false) {
//                die(print_r(sqlsrv_errors()));
//            } else {
//                $msg = "Recovery model set to FULL<br>";
//                echo $msg;
//            }
//        }
//        ////////////////// EOF 1 ////////////////////

        ////////////////// 2) Full //////////////////
        //1. Creating a full (as opposed to a differential) database backup. This essentially creates a copy of your database.
        $sql = "BACKUP DATABASE $dbname TO DISK = '".$backupfile."'";
        echo "FULL sql=".$sql."<br>";

//        $params['backupfile'] = $backupfile;
//        $query = $em->getConnection()->prepare($sql);
//        $res = $query->execute($params);
//        echo "res=".$res."<br>";

        $stmt = sqlsrv_query($conn, $sql);
        //$stmt = $conn->query($sql);

        if($stmt === false)
        {
            die(print_r(sqlsrv_errors(),true));
        }
        else
        {
            $msg = $msg . "<br>" . "Database backed up to $backupfile; stmt=".$stmt;
            echo $msg."<br>";
            exit('Write Full backup file to disk: '.$backupfile); //this is required to write file to disk (?)
        }
        ////////////////// EOF 2 //////////////////

        ////////////////// 3) Backup log //////////////////
        //2. Create periodic log backups. These capture activity since the last backup.
        //$msgLog = $this->creatingBackupSQLLog($filepath);
        //$msg = $msg . "<br>" . $msgLog;

        return $msg;
    }

    //2. Create periodic log backups. These capture activity since the last backup.
    //Suppose you create a full database backup every night at midnight.
    // Then, to capture any transactions that occur between backups,
    // you need to backup your transaction log periodically.
    // Again, a simple script does this. And, again, this process might be automated:
    public function creatingBackupSQLLog( $filepath ) {
        $msg = null;
        $timePrefix = date("d-m-Y-H-i-s");
        //echo "timePrefix=".$timePrefix."<br>";
        //$timePrefix = str_replace(" ","_",$timePrefix);
        $conn = $this->getConnection();
        $dbname = $this->getParameter('database_name');
        echo "dbname=".$dbname."<br>";
        //exit('exit 1');

        ////////////////// 1) make sure that the recovery model of your database is set to FULL (Requires log backups.) ////////////////////
        $setRecovery = false;
        if( $setRecovery ) {
            $sql = "ALTER DATABASE $dbname SET RECOVERY FULL";
            $stmt = sqlsrv_query($conn, $sql);
            if ($stmt === false) {
                die(print_r(sqlsrv_errors()));
            } else {
                $msg = "Recovery model set to FULL<br>";
                echo $msg;
            }
        }
        ////////////////// EOF 1 ////////////////////

        //$backupfileLog = "c:\\backup\\testbackupLog_$timePrefix.bak";
        //$backupfileLog = $filepath . DIRECTORY_SEPARATOR . "testbackupLog_$timePrefix.bak";
        $backupfileLog = $filepath . "testbackupLog_$timePrefix.bak";

        $sql = "BACKUP LOG $dbname TO DISK = '".$backupfileLog."' WITH NORECOVERY";
        echo "LOG sql=".$sql."<br>";
        $stmt = sqlsrv_query($conn, $sql);
        if($stmt === false)
        {
            die(print_r(sqlsrv_errors()));
        }
        else
        {
            $msg = "Transaction log backed up to $backupfileLog";
            echo $msg."<br>";
            exit('exit to write to disk Log backup');
        }

        return $msg;
    }



    public function restoringBackupSQLFull($networkDrivePath) {
        $msg = null;
        $timePrefix = date("d-m-Y-H-i-s");
        //echo "timePrefix=".$timePrefix."<br>";
        //$timePrefix = str_replace(" ","_",$timePrefix);
        $conn = $this->getConnection();
        $dbname = $this->getParameter('database_name');
        echo "dbname=".$dbname."<br>";

        //Restore DB.
        $sql = "RESTORE DATABASE $dbname FROM DISK = '".$networkDrivePath."' WITH RECOVERY";
        echo "RESTORE sql=".$sql."<br>";
        $stmt = sqlsrv_query($conn, $sql);
        if($stmt === false)
        {
            die(print_r(sqlsrv_errors()));
        }
        else
        {
            $msg = "Database restored from $networkDrivePath</br>";
            echo $msg;
        }

        //Put DB into usable state.
        $sql = "USE $dbname";
        echo "USE sql=".$sql."<br>";
        $stmt = sqlsrv_query($conn, $sql);
        if($stmt === false)
        {
            die(print_r(sqlsrv_errors()));
        }
        else
        {
            $msg = $msg . "Using TestDB</br>";
        }

        return $msg;
    }
}