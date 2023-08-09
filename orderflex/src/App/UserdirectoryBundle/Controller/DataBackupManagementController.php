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


use App\UserdirectoryBundle\Form\BackupManagementType;
use App\UserdirectoryBundle\Entity\SiteParameters;
use Doctrine\DBAL\Configuration;
use App\UserdirectoryBundle\Controller\OrderAbstractController;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

class DataBackupManagementController extends OrderAbstractController
{
    /**
     * Backup management page with JSON configuration
     */
    #[Route(path: '/data-backup-management', name: 'employees_data_backup_management', methods: ['GET'])]
    #[Template('AppUserdirectoryBundle/DataBackup/data_backup_management.html.twig')]
    public function dataBackupManagementShowAction(Request $request)
    {
        if( false === $this->isGranted('ROLE_PLATFORM_DEPUTY_ADMIN') ) {
            return $this->redirect( $this->generateUrl('employees-nopermission') );
        }

        $userServiceUtil = $this->container->get('user_service_utility');

        $title = "Data Backup Management";
        $note = "Unique 'idname' must be included somwhere in the command";

        $entity = $userServiceUtil->getSingleSiteSettingParameter();

        //$form = $this->createEditForm($entity, $cycle="show");

        return array(
            'entity' => $entity,
            //'form' => $form->createView(),
            'title' => $title,
            'note' => $note,
            //'cycle' => $cycle,
            'sitename' => "employees",
            'returnurl' => "employees_data_backup_management"
        );
    }

    /**
     * NOT USED
     *
     * Backup management page with JSON configuration
     */
    #[Route(path: '/data-backup-management/edit', name: 'employees_data_backup_management_edit', methods: ['GET', 'POST'])]
    #[Template('AppUserdirectoryBundle/DataBackup/data_backup_management.html.twig')]
    public function dataBackupManagementUpdateAction(Request $request)
    {
        if( false === $this->isGranted('ROLE_PLATFORM_DEPUTY_ADMIN') ) {
            return $this->redirect( $this->generateUrl('employees-nopermission') );
        }

        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $userServiceUtil = $this->container->get('user_service_utility');
        $userSecUtil = $this->container->get('user_security_utility');

        $title = "Data Backup Management";
        $note = "Unique 'idname' must be included somwhere in the command";

        $entity = $userServiceUtil->getSingleSiteSettingParameter();

        $dbBackupConfigOrig = $entity->getDbBackupConfig();
        $filesBackupConfigOrig = $entity->getFilesBackupConfig();

        $form = $this->createEditForm($entity, $cycle="edit");

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $entity = $form->getData();
            //dump($entity);

            $eventStr = "";

            $dbBackupConfig = $entity->getDbBackupConfig();
            if( $dbBackupConfig != $dbBackupConfigOrig ) {
                $eventStr = $eventStr . "Site Settings parameter [dbBackupConfig] has been updated by ".$user;
                $eventStr = $eventStr . "<br>original value:<br>".$dbBackupConfigOrig;
                $eventStr = $eventStr . "<br>updated value:<br>".$dbBackupConfig;
                $eventStr = $eventStr . "<br><br>";
            }
            //echo "dbBackupConfig=$dbBackupConfig <br>";

            $filesBackupConfig = $entity->getFilesBackupConfig();
            if( $filesBackupConfig != $filesBackupConfigOrig ) {
                $eventStr = $eventStr . "Site Settings parameter [filesBackupConfig] has been updated by ".$user;
                $eventStr = $eventStr . "<br>original value:<br>".$filesBackupConfigOrig;
                $eventStr = $eventStr . "<br>updated value:<br>".$filesBackupConfig;
                $eventStr = $eventStr . "<br><br>";
            }
            //echo "filesBackupConfig=$filesBackupConfig <br>";

            //dump($eventStr);
            //exit('111');

            if( $eventStr ) {
                $em->flush();

                //add a new eventlog record for an updated parameter
                $eventType = "Site Settings Parameter Updated";
                $sitename = "employees";
                $userSecUtil->createUserEditEvent($sitename, $eventStr, $user, $entity, $request, $eventType);
            }

            $this->addFlash(
                'notice',
                $eventStr
            );

            return $this->redirectToRoute('employees_data_backup_management');
        }

        return array(
            'entity' => $entity,
            'form' => $form->createView(),
            'title' => $title,
            'note' => $note,
            'cycle' => $cycle
        );
    }

    //NOT USED
    private function createEditForm( SiteParameters $entity, $cycle )
    {
        //$em = $this->getDoctrine()->getManager();

        $params = array(
            'cycle' => $cycle
        );

        $disabled = false;
        if( $cycle == "show" ) {
            $disabled = true;
        }

        $form = $this->createForm(BackupManagementType::class, $entity, array(
            'form_custom_value' => $params,
            //'action' => $this->generateUrl($sitename.'_siteparameters_update', array('id' => $entity->getId(), 'param' => $param )),
            //'method' => 'PUT',
            'disabled' => $disabled
        ));

        //if( $disabled === false ) {
        //    $form->add('submit', SubmitType::class, array('label' => 'Update', 'attr'=>array('class'=>'btn btn-warning','style'=>'margin-top: 15px;')));
        //}

        return $form;
    }





    //NOT USED below. Old version of backup
    /**
     * NOT USED
     */
    #[Route(path: '/list/generate-cron-jobs/dbbackup', name: 'user_generate_cron_dbbackup', methods: ['GET'])]
    public function generateDbBackupCronAction(Request $request)
    {
        if( false === $this->isGranted('ROLE_PLATFORM_DEPUTY_ADMIN') ) {
            return $this->redirect( $this->generateUrl($this->getParameter('employees.sitename').'-nopermission') );
        }

        $userServiceUtil = $this->container->get('user_service_utility');

        //add ExternalUrlMonitor: view-test monitors view
        $res = $userServiceUtil->createDbBackupCronLinux();

        $this->addFlash(
            'notice',
            $res
        );

        return $this->redirect($this->generateUrl('employees_data_backup_management'));
    }

    /**
     * NOT USED
     */
    #[Route(path: '/list/generate-cron-jobs/filesbackup', name: 'user_generate_cron_filesbackup', methods: ['GET'])]
    public function generateFilesBackupCronAction(Request $request)
    {
        if( false === $this->isGranted('ROLE_PLATFORM_DEPUTY_ADMIN') ) {
            return $this->redirect( $this->generateUrl($this->getParameter('employees.sitename').'-nopermission') );
        }

        $userServiceUtil = $this->container->get('user_service_utility');

        //add ExternalUrlMonitor: view-test monitors view
        $res = $userServiceUtil->createFilesBackupCronLinux();

        $this->addFlash(
            'notice',
            $res
        );

        return $this->redirect($this->generateUrl('employees_data_backup_management'));
    }


    
    
    
    
    //NOT USED below. Old version of backup
    /**
     * Manual backup/restore using a user's local folder
     *
     * Resources:
     * https://blogs.msdn.microsoft.com/brian_swan/2010/07/01/restoring-a-sql-server-database-from-php/
     * https://channaly.wordpress.com/2012/01/31/backup-and-restoring-mssql-database-with-php/
     * https://blogs.msdn.microsoft.com/brian_swan/2010/04/06/backup-and-restore-a-database-with-the-sql-server-driver-for-php/
     * Bundle (no MSSQL): https://github.com/dizda/CloudBackupBundle
     *
     * Table specific backup/restore:
     * http://www.php-mysql-tutorial.com/wikis/mysql-tutorials/using-php-to-backup-mysql-databases.aspx
     * https://www.phpclasses.org/package/5761-PHP-Dump-a-Microsoft-SQL-server-database.html#view_files/files/29084
     */
    #[Route(path: '/manual-backup-restore/', name: 'employees_manual_backup_restore', methods: ['GET'])]
    #[Template('AppUserdirectoryBundle/DataBackup/manual_backup_restore.html.twig')]
    public function dataBackupManagementAction_ORIG(Request $request) {

        if( false === $this->isGranted('ROLE_PLATFORM_ADMIN') ) {
            return $this->redirect( $this->generateUrl('employees-nopermission') );
        }

        //networkDrivePath
        $userSecUtil = $this->container->get('user_security_utility');
        $networkDrivePath = $userSecUtil->getSiteSettingParameter('networkDrivePath');
        echo "networkDrivePath=".$networkDrivePath."<br>";
        if( !$networkDrivePath ) {
            //exit("No networkDrivePath is defined");
            $this->addFlash(
                'pnotify-error',
                "Cannot continue with Backup: No Network Drive Path is defined in the Site Settings"
            );
            return $this->redirect($this->generateUrl('employees_home'));
        }

        $sitename = "employees";

        //get backup files
        $backupFiles = $this->getBackupFiles($networkDrivePath); //employees_manual_backup_restore

        return array(
            'sitename' => $sitename,
            'title' => "Data Backup Management",
            'cycle' => 'new',
            'networkDrivePath' => $networkDrivePath,
            'backupFiles' => $backupFiles
        );
    }


    #[Route(path: '/create-backup/', name: 'employees_create_backup', methods: ['GET'])]
    #[Template('AppUserdirectoryBundle/DataBackup/data_backup_management.html.twig')]
    public function createBackupAction(Request $request) {

        if( false === $this->isGranted('ROLE_PLATFORM_ADMIN') ) {
            return $this->redirect( $this->generateUrl('employees-nopermission') );
        }

        //networkDrivePath
        $userSecUtil = $this->container->get('user_security_utility');
        $networkDrivePath = $userSecUtil->getSiteSettingParameter('networkDrivePath');
        echo "networkDrivePath=".$networkDrivePath."<br>";
        if( !$networkDrivePath ) {
            //exit("No networkDrivePath is defined");
            $this->addFlash(
                'pnotify-error',
                //'notice',
                "Cannot continue with Backup: No Network Drive Path is defined in the Site Settings"
            );
            return $this->redirect($this->generateUrl('employees_manual_backup_restore'));
        }

        $em = $this->getDoctrine()->getManager();
        $sitename = "employees";

        if( $networkDrivePath ) {

            //create backup
            //$backupfile = "c:\\backup\\test.bak";
            //$networkDrivePath = "c:\\backup\\";
            $res = $this->creatingBackupSQLFull($networkDrivePath);
            //exit($res);

            $this->addFlash(
                'notice',
                $res
            );

        } else {
            $this->addFlash(
                'pnotify-error',
                "Error backup"
            );
        }

        return $this->redirect($this->generateUrl('employees_manual_backup_restore'));
    }


    #[Route(path: '/restore-backup/{backupFilePath}', name: 'employees_restore_backup', methods: ['GET'], options: ['expose' => true])]
    #[Template('AppUserdirectoryBundle/DataBackup/data_backup_management.html.twig')]
    public function restoreBackupAction( Request $request, $backupFilePath ) {

        if( false === $this->isGranted('ROLE_PLATFORM_ADMIN') ) {
            return $this->redirect( $this->generateUrl('employees-nopermission') );
        }

//        $hostname = $request->getSchemeAndHttpHost();
//        echo "hostname=$hostname<br>";
//        if( strpos((string)$hostname, 'med.cornell.edu') !== false ) {
//            exit("Live server: Under construction!!!");
//        }
        $userSecUtil = $this->container->get('user_security_utility');
        $environment = $userSecUtil->getSiteSettingParameter('environment');
        if( $environment == 'live' ) {
            exit("Live server: Under construction!!!");
        }
        //exit('Under construction!!!');

        //networkDrivePath
//        $userSecUtil = $this->container->get('user_security_utility');
//        $networkDrivePath = $userSecUtil->getSiteSettingParameter('networkDrivePath');
//        if( !$networkDrivePath ) {
//            //exit("No networkDrivePath is defined");
//            $this->addFlash(
//                'error',
//                "Cannot continue with Backup: No Network Drive Path is defined in the Site Settings"
//            );
//            return $this->redirect($this->generateUrl('employees_data_backup_management'));
//        }

        echo "backupFilePath=".$backupFilePath."<br>";

        //get backup files
        //$backupFiles = $this->getBackupFiles($networkDrivePath);

        $sitename = "employees";

        if( $backupFilePath ) {

            //exit('Under construction: backupFilePath='.$backupFilePath);
            //create backup

            $userSecUtil = $this->container->get('user_security_utility');
            $networkDrivePath = $userSecUtil->getSiteSettingParameter('networkDrivePath');
            $networkDrivePath = realpath($networkDrivePath);
            $backupFilePath = $networkDrivePath. DIRECTORY_SEPARATOR . $backupFilePath;

            $res = $this->restoringBackupSQLFull($backupFilePath);
            //exit($res);

            $this->addFlash(
                'notice',
                $res
            );

        }

        return $this->redirect($this->generateUrl('employees_manual_backup_restore'));

//        return array(
//            'sitename' => $sitename,
//            'title' => "Data Backup Management",
//            'cycle' => 'new',
//            'networkDrivePath' => $networkDrivePath,
//            'backupFiles' => $backupFiles
//        );
    }




    ///////////////// UTIL METHODS /////////////////////

    public function getBackupFiles( $networkDrivePath ) {
        if( !$networkDrivePath ) {
            return null;
        }

        if (file_exists($networkDrivePath)) {
            echo "The path=$networkDrivePath";
        } else {
            //echo "The file $networkDrivePath does not exist";
            return null;
        }

        //echo "networkDrivePath=$networkDrivePath <br>";

        $files = scandir($networkDrivePath); //with dots
        //dump($files);
        //exit('111');

        $backupFiles = array();
        if( $files && is_array($files) ) {
            $files = array_diff($files, array('..', '.'));
            foreach( $files as $file ) {
                echo "file=$file <br>";
                //if( is_dir($file) === false ) {
                //if( is_file($file) ) {
                if( pathinfo($file, PATHINFO_EXTENSION) ) {
                    $fileOption = array("id" => $file, "name" => $file);
                    $backupFiles[] = $fileOption;
                }
            }
        }

//        $file0 = array("id"=>null,"name"=>"");
//        $file1 = array("id"=>1,"name"=>"file 1");
//        $backupFiles[] = $file1;
//        $file2 = array("id"=>2,"name"=>"file 2");
//        $backupFiles = array($file0,$file1,$file2);

        return $backupFiles;
    }

    public function getConnection() {
//        $dbname = "ScanOrder";
//        $uid = "symfony2";
//        $pwd = "symfony2";
//        $host = "127.0.0.1";
//        $driver = "pdo_sqlsrv";

        //https://stackoverflow.com/questions/7953053/call-to-undefined-function-sqlsrv-connect-when-trying-to-connect-to-azure-db
        $dbname = $this->getParameter('database_name');
        $uid = $this->getParameter('database_user');
        $pwd = $this->getParameter('database_password');
        $host = $this->getParameter('database_host');
        $driver = $this->getParameter('database_driver');
        $serverName = gethostname();    //"COLLAGE";
        echo "serverName=".$serverName."<br>";
        echo "driver=".$driver."<br>";
        //$pwd = $pwd."1";

        //$serverName = "tcp:sample.database.windows.net, 1433";

        if( 0 ) {
            $connOptions = array("Database"=>$dbname, "UID"=>$uid, "PWD"=>$pwd);
            $conn = sqlsrv_connect($serverName, $connOptions); //it does not work for php > 5.3 ???

            //testing
//            $sql = "SELECT * FROM user_siteParameters";
//            echo "sql=".$sql."<br>";
//            $params = sqlsrv_query($conn, $sql);
//            $res = $params->fetch();
//            echo "env=".$res['environment']."<br>";
        }

        if( 1 ) {
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
            //$sql = "SELECT * FROM user_siteParameters";
            //echo "sql=".$sql."<br>";
            //$params = $conn->query($sql); // Simple, but has several drawbacks
            //$res = $params->fetch();
            //echo "env=".$res['environment']."<br>";
        }

        if( $conn !== false ) {
            echo "Connection established.<br />";
        }else{
            echo "Connection could not be established.<br />";
            die( print_r( sqlsrv_errors(), true));
        }

        return $conn;
    }

//    //SQL Server Database backup FULL
//    //https://blogs.msdn.microsoft.com/brian_swan/2010/04/06/backup-and-restore-a-database-with-the-sql-server-driver-for-php/
//    public function creatingBackupSQLFull_OLD( $filepath ) {
//        $msg = null;
//        $timePrefix = date("d-m-Y-H-i-s");
//
//        $conn = $this->getConnection();
//        $dbname = $this->getParameter('database_name');
//        echo "dbname=".$dbname."<br>";
//
//        $backupfile = $filepath . "testbackup_$timePrefix.bak";
//        echo "backupfile=".$backupfile."<br>";
//
//        //$em = $this->getDoctrine()->getManager();
//        //sqlsrv_configure( "WarningsReturnAsErrors", 0 );
//
////        ////////////////// 1) make sure that the recovery model of your database is set to FULL (Requires log backups.) ////////////////////
////        $setRecovery = false;
////        if( $setRecovery ) {
////            $sql = "ALTER DATABASE $dbname SET RECOVERY FULL";
////            $stmt = sqlsrv_query($conn, $sql);
////            if ($stmt === false) {
////                die(print_r(sqlsrv_errors()));
////            } else {
////                $msg = "Recovery model set to FULL<br>";
////                echo $msg;
////            }
////        }
////        ////////////////// EOF 1 ////////////////////
//
//        ////////////////// 2) Full //////////////////
//        //1. Creating a full (as opposed to a differential) database backup. This essentially creates a copy of your database.
//        $sql = "BACKUP DATABASE $dbname TO DISK = '".$backupfile."'";
//        $sql = "SELECT id FROM crn_crntask";
//        echo "FULL sql=".$sql."<br>";
//
////        $params['backupfile'] = $backupfile;
////        $query = $em->getConnection()->prepare($sql);
////        $res = $query->execute($params);
////        echo "res=".$res."<br>";
//
//        $stmt = sqlsrv_query($conn, $sql);
//        //$stmt = $conn->query($sql);
//
//        if($stmt === false)
//        {
//            die(print_r(sqlsrv_errors(),true));
//        }
//        else
//        {
//            $msg = $msg . "<br>" . "Database backed up to $backupfile; stmt=".$stmt;
//            echo $msg."<br>";
//            exit('Write Full backup file to disk: '.$backupfile); //this is required to write file to disk (?)
//        }
//        ////////////////// EOF 2 //////////////////
//
//        ////////////////// 3) Backup log //////////////////
//        //2. Create periodic log backups. These capture activity since the last backup.
//        //$msgLog = $this->creatingBackupSQLLog($filepath);
//        //$msg = $msg . "<br>" . $msgLog;
//
//        return $msg;
//    }
//    public function creatingBackupSQLFull_OLD1( $filepath ) {
//        $em = $this->getDoctrine()->getManager();
//        $msg = null;
//
//        $timePrefix = date("d-m-Y-H-i-s");
//        $backupfile = $filepath . "testbackup_$timePrefix.bak";
//        echo "backupfile=".$backupfile."<br>";
//
//        $dbname = $this->getParameter('database_name');
//        echo "dbname=".$dbname."<br>";
//
//        ////////////////// 2) Full //////////////////
//        //1. Creating a full (as opposed to a differential) database backup. This essentially creates a copy of your database.
//        $sql = "BACKUP DATABASE $dbname TO DISK = '".$backupfile."'";
//        //$sql = "BACKUP DATABASE ScanOrder TO DISK = 'C:\\db_backup_managtestbackup_07-08-2023-16-10-05.bak'";
//        //$sql = "SELECT id FROM crn_crntask";
//        echo "FULL sql=".$sql."<br>";
//
////        $sql = "
////          SELECT id, field
////          FROM scan_patientlastname
////          WHERE field LIKE '%Doe%'
////        ";
//
//        $params['backupfile'] = $backupfile;
//        $query = $em->getConnection()->prepare($sql);
//        //$res = $query->execute($params);
//        $res = $query->execute();
//        //echo "res=".$res."<br>";
//
//        //$results = $query->fetchAll();
//        //dump($results);
//
//        //$query = $em->createQuery($sql);
//        //$res = $query->getResult();
//
//        dump($res);
//        exit('111');
//
//        return $res;
//        ////////////////// EOF 2 //////////////////
//
//        ////////////////// 3) Backup log //////////////////
//        //2. Create periodic log backups. These capture activity since the last backup.
//        //$msgLog = $this->creatingBackupSQLLog($filepath);
//        //$msg = $msg . "<br>" . $msgLog;
//
//        return $msg;
//    }
    public function creatingBackupSQLFull( $filepath ) {
        $em = $this->getDoctrine()->getManager();
        $msg = null;

        $timePrefix = date("d-m-Y-H-i-s");
        $backupfile = $filepath . "testbackup_$timePrefix.bak";
        echo "backupfile=".$backupfile."<br>";

        $dbname = $this->getParameter('database_name');
        $uid = $this->getParameter('database_user');
        $pwd = $this->getParameter('database_password');
        $host = $this->getParameter('database_host');
        $driver = $this->getParameter('database_driver');
        echo "dbname=".$dbname."<br>";
        echo "uid=".$uid."<br>";
        echo "pwd=".$pwd."<br>";
        echo "host=".$host."<br>";

        ////////////////// 2) Full //////////////////
        //1. Creating a full (as opposed to a differential) database backup. This essentially creates a copy of your database.
        //$sql = "BACKUP DATABASE $dbname TO DISK = '".$backupfile."'";
        //$sql = "BACKUP DATABASE ScanOrder TO DISK = 'C:\\db_backup_managtestbackup_07-08-2023-16-10-05.bak'";
        //$sql = "SELECT id FROM crn_crntask";
        //$sql = "pg_dump -U postgres $dbname > $backupfile";

        //exec('pg_dump --dbname=postgresql://username:password@127.0.0.1:5432/mydatabase > dbbackup.sql',$output);
        $sql = 'pg_dump --dbname=postgresql://'.$uid.':'.$pwd.'@'.$host.':5432/'.$dbname.' > '.$backupfile;

        echo "FULL sql=".$sql."<br>";

//        $sql = "
//          SELECT id, field
//          FROM scan_patientlastname
//          WHERE field LIKE '%Doe%'
//        ";

//        $res = exec($sql,$output,$return);
//        print_r($output);
//
//        if( $return == 1 && count($output) > 0 ) {
//            dump($output);
//            dump($return);
//        }

        $process = Process::fromShellCommandline($sql);
        $process->setTimeout(1800); //sec; 1800 sec => 30 min
        $process->run();
        if( !$process->isSuccessful() ) {
            throw new ProcessFailedException($process);
        }
        $res = $process->getOutput();
        $res = $res . " Successefully backup DataBase $dbname to $backupfile";

        //$res = shell_exec($sql);

        //$params['backupfile'] = $backupfile;
        //$query = $em->getConnection()->prepare($sql);
        //$res = $query->execute($params);
        //$res = $query->execute();
        //echo "res=".$res."<br>";
        //$res = $em->getConnection()->exec($sql);

        //$results = $query->fetchAll();
        //dump($results);

        //$query = $em->createQuery($sql);
        //$res = $query->getResult();

        //dump($res);
        //exit('111');

        return $res;
        ////////////////// EOF 2 //////////////////

        ////////////////// 3) Backup log //////////////////
        //2. Create periodic log backups. These capture activity since the last backup.
        //$msgLog = $this->creatingBackupSQLLog($filepath);
        //$msg = $msg . "<br>" . $msgLog;
        //return $msg;
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



    public function restoringBackupSQLFull($backupFilePath) {
        $em = $this->getDoctrine()->getManager();
        $res = null;

        $dbname = $this->getParameter('database_name');
        $dbname = "ScanOrderTest"; //testing replace for testing
        $uid = $this->getParameter('database_user');
        $pwd = $this->getParameter('database_password');
        $host = $this->getParameter('database_host');
        $driver = $this->getParameter('database_driver');
        echo "dbname=".$dbname."<br>";
        echo "uid=".$uid."<br>";
        echo "pwd=".$pwd."<br>";
        echo "host=".$host."<br>";
        echo "backupFilePath=".$backupFilePath."<br>";

        //exec('pg_dump --dbname=postgresql://username:password@127.0.0.1:5432/mydatabase > dbbackup.sql',$output);
        //$sql = 'pg_dump --dbname=postgresql://'.$uid.':'.$pwd.'@'.$host.':5432/'.$dbname.' > '.$backupfile;
        $sql = 'pg_restore --dbname=postgresql://'.$uid.':'.$pwd.'@'.$host.':5432/'.$dbname.' '.$backupFilePath;

        echo "FULL sql=".$sql."<br>";

//        $sql = "
//          SELECT id, field
//          FROM scan_patientlastname
//          WHERE field LIKE '%Doe%'
//        ";

        if(1) {
            $process = Process::fromShellCommandline($sql);
            $process->setTimeout(1800); //sec; 1800 sec => 30 min
            $process->run();
            if (!$process->isSuccessful()) {
                throw new ProcessFailedException($process);
            }
            $res = $process->getOutput();
            $res = "Successefully restore backup DataBase $dbname from $backupFilePath. " . $res;
        } else {
            $res = "Restore process is disabled";
        }

        //dump($res);
        //exit('111');

        return $res;
    }
    public function restoringBackupSQLFull_MSSQL($networkDrivePath) {
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