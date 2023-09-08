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


use App\UserdirectoryBundle\Entity\User;
use App\UserdirectoryBundle\Form\BackupManagementType;
use App\UserdirectoryBundle\Entity\SiteParameters;
use Doctrine\DBAL\Configuration;
use App\UserdirectoryBundle\Controller\OrderAbstractController;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;
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
     * Manual backup/restore using a user's local folder
     * Resources: https://github.com/valferon/postgres-manage-python
     */
    #[Route(path: '/manual-backup-restore/', name: 'employees_manual_backup_restore', methods: ['GET'])]
    #[Template('AppUserdirectoryBundle/DataBackup/manual_backup_restore.html.twig')]
    public function manualBackupRestoreManagementAction(Request $request) {
        if( false === $this->isGranted('ROLE_PLATFORM_ADMIN') ) {
            return $this->redirect( $this->generateUrl('employees-nopermission') );
        }

        $userServiceUtil = $this->container->get('user_service_utility');
        if( $userServiceUtil->isWindows() ){
            $this->addFlash(
                'pnotify-error',
                "DB management is not implemented for Windows"
            );
        }

        //networkDrivePath
        $userSecUtil = $this->container->get('user_security_utility');
        $networkDrivePath = $userSecUtil->getSiteSettingParameter('networkDrivePath');
        //echo "networkDrivePath=".$networkDrivePath."<br>";
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

        //'choices' => array("live"=>"live", "test"=>"test", "dev"=>"dev"),
        $environmentsArr = $userServiceUtil->getEnvironments();
        $environments = array();
        foreach( $environmentsArr as $id => $name ) {
            $environments[] = array('id'=>$id, 'name'=>$name);
        }

        return array(
            'sitename' => $sitename,
            'title' => "Data Backup Management",
            'cycle' => 'new',
            'networkDrivePath' => $networkDrivePath,
            'backupFiles' => $backupFiles,
            'environments' => $environments
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
        //echo "networkDrivePath=".$networkDrivePath."<br>";
        if( !$networkDrivePath ) {
            //exit("No networkDrivePath is defined");
            $this->addFlash(
                'pnotify-error',
                //'notice',
                "Cannot continue with Backup: No Network Drive Path is defined in the Site Settings"
            );
            return $this->redirect($this->generateUrl('employees_manual_backup_restore'));
        }

        if( $networkDrivePath ) {

            //create backup
            //$res = $this->creatingBackupSQLFull($networkDrivePath); //Use php based pg_dump
            $res = $this->dbManagePython($networkDrivePath,'backup'); //Use python script pg_dump
            //exit($res);

            $resStatus = $res['status'];
            $resStr = $res['message'];

            if( $resStatus == 'OK' ) {
                $resStr = "Backup successfully created in folder $networkDrivePath";
                $this->addFlash(
                    'notice',
                    $resStr
                );
            } else {
                $this->addFlash(
                    'pnotify-error',
                    $resStr
                );
            }

        } else {
            $this->addFlash(
                'pnotify-error',
                "Error backup"
            );
        }

        return $this->redirect($this->generateUrl('employees_manual_backup_restore'));
    }

    #[Route(path: '/restore-backup-ajax/', name: 'employees_restore_backup_ajax', methods: ['POST'], options: ['expose' => true])]
    public function restoreBackupAjaxAction( Request $request ) {

        if( false === $this->isGranted('ROLE_PLATFORM_ADMIN') ) {
            return $this->redirect( $this->generateUrl('employees-nopermission') );
        }

        $logger = $this->container->get('logger');
        $em = $this->getDoctrine()->getManager();
        $userSecUtil = $this->container->get('user_security_utility');
        $userServiceUtil = $this->container->get('user_service_utility');

        $environment = $userSecUtil->getSiteSettingParameter('environment');
        if( $environment == 'live' ) {
            //exit("Live server: Under construction!!!");
            $logger->notice("Live server: restore not allowed");
            $output = array(
                'status' => "NOTOK",
                'message' => "Live server: restore not allowed. Change environment from 'live' to 'test' or 'dev' in the site settings."
            );
            $response = new Response();
            $response->setContent(json_encode($output));
            return $response;
        }
        //exit('Not Allowed');

        $backupFileName = $request->get('fileId');
        $env = $request->get('env');
        $logger->notice("backupFilePath=".$backupFileName."; env=".$env);
        //echo "backupFilePath=".$fileId."; env=".$env."<br>";
        //exit('111');

        //get backup files
        //$backupFiles = $this->getBackupFiles($networkDrivePath);

        $sitename = "employees";

        if( $backupFileName ) {

            //Original site settings
            $siteEmail = $userSecUtil->getSiteSettingParameter('siteEmail');
            if( !$siteEmail ) {
                $siteEmail = "myemail@example.com";
            }
            $exceptionUsers = $userSecUtil->getSiteSettingParameter('emailCriticalErrorExceptionUsers');
            //$mailerDeliveryAddresses = (string)$userSecUtil->getSiteSettingParameter('mailerDeliveryAddresses');

            if(0) {
                $mailerDeliveryAddresses = (string)$userSecUtil->getSiteSettingParameter('mailerDeliveryAddresses');
                $environment = $userSecUtil->getSiteSettingParameter('environment');
                $liveSiteRootUrl = $userSecUtil->getSiteSettingParameter('liveSiteRootUrl');
                $connectionChannel = $userSecUtil->getSiteSettingParameter('connectionChannel');
            }

            //exit('Under construction: backupFilePath='.$backupFileName);
            //create backup

            $networkDrivePath = $userSecUtil->getSiteSettingParameter('networkDrivePath');
            $networkDrivePath = realpath($networkDrivePath);
            //$backupFilePath = $networkDrivePath. DIRECTORY_SEPARATOR . $backupFilePath;

            $logger->notice("Before dbManagePython: networkDrivePath=$networkDrivePath");

            //$res = $this->restoringBackupSQLFull($backupFilePath);
            //$res = $this->restoringBackupSQLFull_Plain($backupFilePath);
            $res = $this->dbManagePython($networkDrivePath,'restore',$backupFileName); //Use python script pg_restore
            //exit($res);

            $logger->notice("After dbManagePython");

            $resStatus = $res['status'];
            $resStr = $res['message'];

            $logger->notice("After dbManagePython: status=$resStatus, resStr=$resStr");

            if( $resStatus == 'OK' ) {

                $logger->notice("before getConnection");
                $conn = $this->getConnection();
                $logger->notice("after getConnection");

                //App\\UserdirectoryBundle\\Entity\\SiteParameters
                $sql =  "UPDATE user_siteparameters".
                        " SET mailerdeliveryaddresses='$siteEmail', environment='$env', version='3'".
                        " WHERE id=1";
                //$sql = "SELECT id, mailerdeliveryaddresses FROM user_siteparameters";
                $logger->notice("sql=".$sql);
                $stmt = $conn->prepare($sql);
                $logger->notice("after prepare");
                //$statement->execute();
                //$logger->notice("after execute");
                //$res = $statement->fetchAll();
                //$logger->notice("after fetchAll. res=".$res);
                $results = $stmt->executeQuery();
                $logger->notice("after executeQuery");
                $resStr = print_r($results->fetchAll());
                $logger->notice("after executeQuery. res=".$resStr);

//                //$em = $this->container->get('doctrine.orm.entity_manager');
//                $em = $this->getDoctrine()->getManager();
//                //$sm = $em->getConnection()->getSchemaManager();
//                $logger->notice("before createSchemaManager");
//                $sm = $em->getConnection()->createSchemaManager();
//                $logger->notice("after createSchemaManager");
//                $tables = $sm->listTables();
//                $logger->notice("tables count=".count($tables));

                //$userServiceUtil->updateSiteSettingParametersAfterRestore($env,$exceptionUsers,$siteEmail);

                //set site settings parameters
                if(0) {
                    $logger->notice("set site settings parameters");

                    //$this->getConnection();

                    //restart postgresql server? sudo systemctl restart httpd.service
                    //$command = "systemctl restart httpd.service";
                    $command = "sudo systemctl restart postgresql-14";
                    $logger->notice("command=[".$command."]");
                    $res = $this->runProcess($command);
                    $logger->notice("systemctl restart postgresql-14: res=".$res);

                    $projectRoot = $this->container->get('kernel')->getProjectDir();
                    //$projectRoot = C:\Users\ch3\Documents\MyDocs\WCMC\ORDER\order-lab\orderflex
                    $this->runProcess("bash ".$projectRoot.DIRECTORY_SEPARATOR."deploy.sh");

                    //$em = $this->getDoctrine()->getManager();
                    //https://stackoverflow.com/questions/42116749/restore-doctrine-connection-after-failed-flush
                    $em = $this->getDoctrine()->resetManager();
                    //$em = $this->getDoctrine()->getManager();

                    $param = $userSecUtil->getSingleSiteSettingsParam();
                    $logger->notice("After get settings parameters. paramId=" . $param->getId());

                    if(0) {
                        /////// set original parameters //////////
                        //mailerDeliveryAddresses to admin
                        //environment
                        //liveSiteRootUrl
                        //networkDrivePath
                        //connectionChannel
                        $param->setMailerDeliveryAddresses($mailerDeliveryAddresses);
                        $param->setEnvironment($environment);
                        $param->setLiveSiteRootUrl($liveSiteRootUrl);
                        $param->setNetworkDrivePath($networkDrivePath);
                        $param->setConnectionChannel($connectionChannel);
                        /////// EOF set original parameters //////////
                    }

                    /////// set parameters //////////
                    //set environment
                    $param->setEnvironment($env);

                    if( $env != 'live' ) {
                        //prevent sending emails to real users for not live environment
                        $param->setMailerDeliveryAddresses($siteEmail);
                    }

                    //prevent sending critical emails
                    foreach ($exceptionUsers as $exceptionUser) {
                        $param->addEmailCriticalErrorExceptionUser($exceptionUser);
                    }
                    /////// EOF set parameters //////////

                    $logger->notice("After set settings parameters. Before flush");
                    $em->flush();

                    $logger->notice("After flush");
                }

                $resStr =
                    "Restored database " . $backupFileName . "<br>" .
                    $resStr .
                    " <br>The next step would be to make sure the  public 'Uploaded' folder corresponds to the restored DB.".
                    " <br>Also it might be required to run the deploy_prod.sh script."
                ;

                $output = array(
                    'status' => 'OK',
                    'message' => $resStr
                );
            } else {
                $output = array(
                    'status' => 'NOTOK',
                    'message' => $resStr
                );
            }

            $logger->notice("Before sending response");
            $response = new Response();
            $response->setContent(json_encode($output));
            return $response;

        }

        $output = array(
            'status' => 'NOTOK',
            'message' => 'Backup file is not provided'
        );
        $response = new Response();
        $response->setContent(json_encode($output));
        return $response;
    }

    //NOT USED, was replaced by restoreBackupAjaxAction. Call restore directly
    #[Route(path: '/restore-backup/{backupFilePath}', name: 'employees_restore_backup', methods: ['GET'], options: ['expose' => true])]
    #[Template('AppUserdirectoryBundle/DataBackup/data_backup_management.html.twig')]
    public function restoreBackupAction_ORIG( Request $request, $backupFilePath ) {

        exit('Not Allowed');

        if( false === $this->isGranted('ROLE_PLATFORM_ADMIN') ) {
            return $this->redirect( $this->generateUrl('employees-nopermission') );
        }

        $userSecUtil = $this->container->get('user_security_utility');
        $environment = $userSecUtil->getSiteSettingParameter('environment');
        if( $environment == 'live' ) {
            exit("Live server: Under construction!!!");
        }

        //echo "backupFilePath=".$backupFilePath."<br>";
        if( $backupFilePath ) {

            $userSecUtil = $this->container->get('user_security_utility');
            $networkDrivePath = $userSecUtil->getSiteSettingParameter('networkDrivePath');
            $networkDrivePath = realpath($networkDrivePath);

            $res = $this->dbManagePython($networkDrivePath,'restore',$backupFilePath); //Use python script pg_restore

            $resStr = $res['message'];

            $this->addFlash(
                'notice',
                $resStr
            );
        }

        return $this->redirect($this->generateUrl('employees_manual_backup_restore'));
    }

    //Use python's script order-lab\utils\db-manage\postgres-manage-python\manage_postgres_db.py
    public function dbManagePython( $networkDrivePath, $action, $backupFileName=null ) {

        $userServiceUtil = $this->container->get('user_service_utility');
        if( $userServiceUtil->isWindows() ){
            $res = array(
                'status' => "NOTOK",
                'message' => "DB management is not implemented for Windows"
            );
            return $res;
        }

        //manage_postgres_db.py is using sample.config file with a local storage as a destination path=/tmp/backups/
        //$filepath is provided by site settings networkDrivePath => manage_postgres_db.py should accept --path

        $logger = $this->container->get('logger');

        $dbName = $this->getParameter('database_name');
        if( !$dbName ) {
            $res = array(
                'status' => "NOTOK",
                'message' => "Logical error: database_name is not defined in the parameters.yml"
            );
            return $res;
        }

        $projectRoot = $this->container->get('kernel')->getProjectDir();
        //echo "projectRoot=".$projectRoot."<br>";

        $projectRoot = str_replace('order-lab', '', $projectRoot);
        $parentRoot = str_replace('orderflex', '', $projectRoot);
        $parentRoot = str_replace(DIRECTORY_SEPARATOR . DIRECTORY_SEPARATOR, '', $parentRoot);

        $managePackagePath = $parentRoot .
            DIRECTORY_SEPARATOR . "order-lab" .
            DIRECTORY_SEPARATOR . "utils" .
            DIRECTORY_SEPARATOR . "db-manage" .
            DIRECTORY_SEPARATOR . "postgres-manage-python";
        //echo 'scriptPath='.$scriptPath."<br>";

        //config file
        $configFilePath = $managePackagePath . DIRECTORY_SEPARATOR . "db.config";

        $pythonScriptPath = $managePackagePath . DIRECTORY_SEPARATOR . "manage_postgres_db.py";
        //exit('111='.$pythonScriptPath);

        //python in virtualenv'ed scripts: /path/to/venv/bin/python3
        if( $userServiceUtil->isWindows() ){
            $pythonEnvPath = $managePackagePath .
                DIRECTORY_SEPARATOR . "venv" .
                DIRECTORY_SEPARATOR . "Scripts" . //Windows
                DIRECTORY_SEPARATOR . "python";
        } else {
            $pythonEnvPath = $managePackagePath .
                DIRECTORY_SEPARATOR . "venv" .
                DIRECTORY_SEPARATOR . "bin" . //Linux
                DIRECTORY_SEPARATOR . "python";
        }
        //echo "pythonEnvPath=".$pythonEnvPath."<br>";
        if( file_exists($pythonEnvPath) ) {
            //echo "The file $filename exists";
        } else {
            $msg = "Error in DB management (action $action): The file $pythonEnvPath does not exist.".
                " Make sure pytnon's environment venv has been installed";
            $res = array(
                'status' => "NOTOK",
                'message' => $msg
            );
            return $res;
        }

        //$command = "$pythonEnvPath $pythonScriptPath --configfile $configFilePath --action list --verbose true --path $networkDrivePath";
        //$command = "$pythonEnvPath $pythonScriptPath --configfile $configFilePath --action list_dbs --verbose true --path $networkDrivePath";

        $command = "$pythonEnvPath $pythonScriptPath".
            " --configfile $configFilePath --verbose true".
            " --path $networkDrivePath".
            " --source-db $dbName"
        ;

        if( $action == 'backup' ) {
            //backup
            $command = $command . " --action backup";
        } elseif( $action == 'restore' ) {
            //restore
            if( $backupFileName ) {
                $command = $command . " --action restore --date $backupFileName";
            } else {
                $msg = "Error in DB management (action $action): backup file is not provided";
                $res = array(
                    'status' => "NOTOK",
                    'message' => $msg
                );
                return $res;
            }
        } else {
            //Invalid action
            $msg = "Error in DB management (action $action): invalid action ".$action;
            $res = array(
                'status' => "NOTOK",
                'message' => $msg
            );
            return $res;
        }

        $logger->notice("command=[".$command."]");
        $res = $this->runProcess($command);
        //echo "python res=".$res."<br>";
        //exit('111');
        $res = array(
            'status' => "OK",
            'message' => $res
        );
        return $res;
    }

    //Create Upload Folder Backup order-lab\orderflex\public\Uploaded\
    #[Route(path: '/create-backup-upload/', name: 'employees_create_backup_upload', methods: ['GET'])]
    #[Template('AppUserdirectoryBundle/DataBackup/data_backup_management.html.twig')]
    public function createUploadBackupAction(Request $request) {

        if( false === $this->isGranted('ROLE_PLATFORM_ADMIN') ) {
            return $this->redirect( $this->generateUrl('employees-nopermission') );
        }

        //networkDrivePath
        $userSecUtil = $this->container->get('user_security_utility');
        $networkDrivePath = $userSecUtil->getSiteSettingParameter('networkDrivePath');
        //echo "networkDrivePath=".$networkDrivePath."<br>";
        if( !$networkDrivePath ) {
            //exit("No networkDrivePath is defined");
            $this->addFlash(
                'pnotify-error',
                //'notice',
                "Cannot continue with Backup: No Network Drive Path is defined in the Site Settings"
            );
            return $this->redirect($this->generateUrl('employees_manual_backup_restore'));
        }

        if( $networkDrivePath ) {

            //create backup tar -zcvf archive.tar.gz directory/
            $networkDrivePath = realpath($networkDrivePath); //C:\Users\ch3\Documents\MyDocs\WCMC\Backup\db_backup_manag
            //exit($networkDrivePath);

            $archiveFile = "upload_".$date = date('Y-m-d-H-i-s').".tar.gz";
            $archiveFile = $networkDrivePath.DIRECTORY_SEPARATOR.$archiveFile;
            echo "archiveFile=".$archiveFile."<br>";

            $projectRoot = $this->container->get('kernel')->getProjectDir();
            //echo "projectRoot=".$projectRoot."<br>";
            $folder = $projectRoot.DIRECTORY_SEPARATOR."public".DIRECTORY_SEPARATOR."Uploaded";
            $folder = $projectRoot.DIRECTORY_SEPARATOR."public".DIRECTORY_SEPARATOR."Uploaded".DIRECTORY_SEPARATOR."calllog";
            echo "folder=".$folder."<br>";
            //exit('111');

            $command = "tar -zcvf $archiveFile $folder";
            $res = $this->runProcess($command);
            exit("res=".$res);

            if( !$res ) {
                $res = "Uploaded folder backup $archiveFile has been successfully created";
            }

            $this->addFlash(
                'notice',
                $res
            );

//            if( $res ) {
//                $resStr = "Backup successfully created in folder $networkDrivePath";
//                $this->addFlash(
//                    'notice',
//                    $resStr
//                );
//            } else {
//                $this->addFlash(
//                    'pnotify-error',
//                    $resStr
//                );
//            }

        } else {
            $this->addFlash(
                'pnotify-error',
                "Error backup"
            );
        }

        return $this->redirect($this->generateUrl('employees_manual_backup_restore'));
    }


    public function getBackupFiles( $networkDrivePath ) {
        if( !$networkDrivePath ) {
            return null;
        }

        if (file_exists($networkDrivePath)) {
            //echo "The path=$networkDrivePath";
        } else {
            //echo "The file $networkDrivePath does not exist";
            return null;
        }

        //echo "networkDrivePath=$networkDrivePath <br>";

        //$files = scandir($networkDrivePath); //with dots
        $files = $this->better_scandir($networkDrivePath,SCANDIR_SORT_DESCENDING);
        //dump($files);
        //exit('111');

        $backupFiles = array();
        if( $files && is_array($files) ) {
            $files = array_diff($files, array('..', '.'));
            foreach( $files as $file ) {
                //echo "file=$file <br>";
                $ext = pathinfo($file, PATHINFO_EXTENSION);
                if( $ext && $ext != 'log' && $ext != 'txt' ) {
                    $filePath = $networkDrivePath . DIRECTORY_SEPARATOR . $file;
                    $fileName = $file .
                        " (" .
                        date("F d Y H:i:s", filemtime($filePath)) .
                        ", " . $this->formatSizeUnits(filesize($filePath)) .
                        ")";
                    $fileOption = array("id" => $file, "name" => $fileName);
                    $backupFiles[] = $fileOption;
                }
            }
        }

        return $backupFiles;
    }
    public function formatSizeUnits($bytes)
    {
        if ($bytes >= 1073741824)
        {
            $bytes = number_format($bytes / 1073741824, 2) . ' GB';
        }
        elseif ($bytes >= 1048576)
        {
            $bytes = number_format($bytes / 1048576, 2) . ' MB';
        }
        elseif ($bytes >= 1024)
        {
            $bytes = number_format($bytes / 1024, 2) . ' KB';
        }
        elseif ($bytes > 1)
        {
            $bytes = $bytes . ' bytes';
        }
        elseif ($bytes == 1)
        {
            $bytes = $bytes . ' byte';
        }
        else
        {
            $bytes = '0 bytes';
        }

        return $bytes;
    }
    //https://stackoverflow.com/questions/11923235/scandir-to-sort-by-date-modified
    public function better_scandir($dir, $sorting_order = SCANDIR_SORT_ASCENDING) {

        /****************************************************************************/
        // Roll through the scandir values.
        $files = array();
        foreach (scandir($dir, $sorting_order) as $file) {
            if ($file[0] === '.') {
                continue;
            }
            $files[$file] = filemtime($dir . '/' . $file);
        } // foreach

        /****************************************************************************/
        // Sort the files array.
        if ($sorting_order == SCANDIR_SORT_ASCENDING) {
            asort($files, SORT_NUMERIC);
        }
        else {
            arsort($files, SORT_NUMERIC);
        }

        /****************************************************************************/
        // Set the final return value.
        $ret = array_keys($files);

        /****************************************************************************/
        // Return the final value.
        return $ret;

    } // better_scandir


    public function runProcess($script) {
        //$process = new Process($script);
        $process = Process::fromShellCommandline($script);
        $process->setTimeout(1800); //sec; 1800 sec => 30 min
        $process->run();
        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }
        return $process->getOutput();
    }







    ///////////////// NOT USED, OLD /////////////////////
    /**
     * Manual backup/restore using a user's local folder
     *
     * Resources: https://github.com/valferon/postgres-manage-python
     * https://blogs.msdn.microsoft.com/brian_swan/2010/07/01/restoring-a-sql-server-database-from-php/
     * https://channaly.wordpress.com/2012/01/31/backup-and-restoring-mssql-database-with-php/
     * https://blogs.msdn.microsoft.com/brian_swan/2010/04/06/backup-and-restore-a-database-with-the-sql-server-driver-for-php/
     * Bundle (no MSSQL): https://github.com/dizda/CloudBackupBundle
     *
     * Table specific backup/restore:
     * http://www.php-mysql-tutorial.com/wikis/mysql-tutorials/using-php-to-backup-mysql-databases.aspx
     * https://www.phpclasses.org/package/5761-PHP-Dump-a-Microsoft-SQL-server-database.html#view_files/files/29084
     */

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
        $backupfile = $filepath . "testbackup_$timePrefix.sql";
        echo "backupfile=".$backupfile."<br>";

        $dbname = $this->getParameter('database_name');
        $uid = $this->getParameter('database_user');
        $pwd = $this->getParameter('database_password');
        $host = $this->getParameter('database_host');
        $driver = $this->getParameter('database_driver');

        //$uid = 'postgresql';

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
        //$sql = 'pg_dump --dbname=postgresql://'.$uid.':'.$pwd.'@'.$host.':5432/'.$dbname.' > '.$backupfile; //working
        $sql = 'pg_dump --dbname=postgresql://'.$uid.':'.$pwd.'@'.$host.':5432/'.$dbname.' -f '.$backupfile; //working

        //C:\xampp\pgsql\14\bin\pg_dump.exe --file "10AUGU~2.SQL" --host "157.139.226.86" --port "5432" --username "symfony" --no-password --verbose --format=c --blobs "ScanOrder"
        //C:\xampp\pgsql\14\bin\pg_dump.exe --file "C:\\Users\\ch3\\DOCUME~1\\MyDocs\\WCMC\\Backup\\DB_BAC~1\\Dev\\10AUGU~3.SQL" --host "127.0.0.1" --port "5432" --username "postgres" --no-password --verbose --format=c --blobs "ScanOrder"
        //$sql = "pg_dump --file '$backupfile' --host '$host' --port '5432' --username '$uid' --no-password --verbose --format=c --blobs '$dbname'";

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

    //Restore to the empty DB (no more than 9 users)
    //$backupFilePath is plain, sql file
    //Use DB ScanOrderTest
    public function restoringBackupSQLFull_Plain($backupFilePath) {
        if (file_exists($backupFilePath)) {
            //echo "The file $filename exists";
        } else {
            return "The file $backupFilePath does not exist";
        }

        $em = $this->getDoctrine()->getManager();

        //1 admininstrator user + 8 tests users = 9 users
        $users = $em->getRepository(User::class)->findAll();
        echo "users=".count($users)."<br>";
        if( count($users) > 9 ) {
            //return "Exit: Users are already populated in DB, therefore DB is not empty.";
        }
        //exit('111');

        //check if db compatable with filename
        $userServiceUtil = $this->container->get('user_service_utility');
        $dbInfo = $userServiceUtil->getDbVersion(); //PostgreSQL 14.3, compiled by Visual C++ build 1914, 64-bit
        $dbInfoLower = strtolower($dbInfo);
        echo "$dbInfoLower=".$dbInfoLower."<br>";
        if( str_contains($dbInfoLower, 'postgresql') === false ) {
            return "File ".$filename. " is not compatable with current database " . $dbInfo;
        }

        $memory_limit = ini_get('memory_limit');
        echo "Current memory limit is: " . $memory_limit . "<br>";
        echo "Peak memory usage: " . memory_get_peak_usage() . "<br>";
        ini_set('memory_limit', '-1');
        ini_set('max_execution_time', '-1');
        $memory_limit = ini_get('memory_limit');
        echo "memory_limit: " . $memory_limit . "<br>";
        $max_execution_time = ini_get('max_execution_time');
        echo "max_execution_time: " . $max_execution_time . "<br>";
        //exit('111');

        //1) drop current DB
        if(0) {
            $logger = $this->container->get('logger');
            $userServiceUtil = $this->container->get('user_service_utility');
            $phpPath = $userServiceUtil->getPhpPath();
            $projectRoot = $this->container->get('kernel')->getProjectDir();

            //drop existing DB: php bin/console doctrine:database:drop --force
            if (0) {
                //request.CRITICAL: Uncaught PHP Exception
                // Symfony\Component\Process\Exception\ProcessFailedException:
                // "The command "/opt/remi/php82/root/usr/bin/php
                // /opt/order-lab/orderflex/bin/console doctrine:database:drop --force" failed.
                //  Exit Code: 1(General error)  Working directory: /opt/order-lab/orderflex/public
                //  Output: ================ Could not drop database "ScanOrderTest"
                // for connection named default An exception occurred while executing
                // a query: SQLSTATE[55006]: Object in use: 7 ERROR:  database "ScanOrderTest"
                // is being accessed by other users DETAIL:  There is 1 other session using the database.
                //$drop = $phpPath . ' ' . $projectRoot . '/bin/console doctrine:database:drop --force --verbose';
                $drop = $phpPath . ' ' . $projectRoot . '/bin/console doctrine:schema:drop --full-database --force --verbose';
                $logger->notice("drop command=[" . $drop . "]");
                $res = $this->runProcess($drop);
                echo "drop res=" . $res . "<br>";
                $logger->notice("drop res=".$res);
            } else {
                //DROP DATABASE db_name WITH (FORCE)
                echo "Start drop DB <br>";
                $sqlDrop = 'DROP DATABASE ' . $dbname . ' WITH (FORCE)';
                $em->getConnection()->exec($sqlDrop);  // Execute native SQL
                $em->flush();
            }
        }

        echo "Start restore. Read file: " . $backupFilePath . "<br>";
        $sql = file_get_contents($backupFilePath);  // Read file contents

        echo "Start restore: exec dql from file: " . $backupFilePath . "<br>";
        $em->getConnection()->exec($sql);  // Execute native SQL

        $em->flush();

        //exit("generateAntibodyList: Finished");
        return true;
    }
    public function restoringBackupSQLFull($backupFilePath) {
        exit("Not Allowed!");
        $em = $this->getDoctrine()->getManager();
        $res = null;

        $dbname = $this->getParameter('database_name');
        $uid = $this->getParameter('database_user');
        $pwd = $this->getParameter('database_password');
        $host = $this->getParameter('database_host');
        $driver = $this->getParameter('database_driver');

        $dbname = "ScanOrderTest"; //testing replace for testing
        $uid = 'postgres';

        echo "dbname=".$dbname."<br>";
        echo "uid=".$uid."<br>";
        echo "pwd=".$pwd."<br>";
        echo "host=".$host."<br>";
        echo "backupFilePath=".$backupFilePath."<br>";

        //exec('pg_dump --dbname=postgresql://username:password@127.0.0.1:5432/mydatabase > dbbackup.sql',$output);
        //$sql = 'pg_dump --dbname=postgresql://'.$uid.':'.$pwd.'@'.$host.':5432/'.$dbname.' > '.$backupfile;
        //pg_restore --dbname=postgresql://username:password@127.0.0.1:5432/mydatabase --verbose
        //pg_restore -d newdb db.dump
        //$sql = 'pg_restore --dbname=postgresql://'.$uid.':'.$pwd.'@'.$host.':5432/'.$dbname.' '.$backupFilePath;
        //$sql = 'pg_restore -d --dbname=postgresql://'.$uid.':'.$pwd.'@'.$host.':5432/'.$dbname.' '.$backupFilePath;
        //$sql = 'pg_restore --verbose --dbname=postgresql://'.$uid.':'.$pwd.'@'.$host.':5432/'.$dbname.' < '.$backupFilePath;
        //pg_restore.exe --host "127.0.0.1" --port "5432" --username "postgres" --no-password --dbname "ScanOrderTest" --verbose backupfile
        //$ospath = '/c/xampp/pgsql/14/bin/';
        //$ospath = "C:\\xampp\\pgsql\\14\\bin\\";
        $ospath = "";
        $sql = $ospath."pg_restore --host '$host' --port 5432 --username '$uid' --no-password --dbname '$dbname' --verbose '$backupFilePath'";
        //$sql = $ospath."pg_restore --help";

        echo "FULL sql=".$sql."<br>";

//        $sql = "
//          SELECT id, field
//          FROM scan_patientlastname
//          WHERE field LIKE '%Doe%'
//        ";

        if(1) {
            $logger = $this->container->get('logger');
            $userServiceUtil = $this->container->get('user_service_utility');
            $phpPath = $userServiceUtil->getPhpPath();
            $projectRoot = $this->container->get('kernel')->getProjectDir();

            //1) drop existing DB: php bin/console doctrine:database:drop --force
            if(1) {
                if (1) {
                    //request.CRITICAL: Uncaught PHP Exception
                    // Symfony\Component\Process\Exception\ProcessFailedException:
                    // "The command "/opt/remi/php82/root/usr/bin/php
                    // /opt/order-lab/orderflex/bin/console doctrine:database:drop --force" failed.
                    //  Exit Code: 1(General error)  Working directory: /opt/order-lab/orderflex/public
                    //  Output: ================ Could not drop database "ScanOrderTest"
                    // for connection named default An exception occurred while executing
                    // a query: SQLSTATE[55006]: Object in use: 7 ERROR:  database "ScanOrderTest"
                    // is being accessed by other users DETAIL:  There is 1 other session using the database.
                    //$drop = $phpPath . ' ' . $projectRoot . '/bin/console doctrine:database:drop --force --verbose';
                    $drop = $phpPath . ' ' . $projectRoot . '/bin/console doctrine:schema:drop --full-database --force --verbose';
                    $logger->notice("drop command=[" . $drop . "]");
                    $res = $this->runProcess($drop);
                    echo "drop res=" . $res . "<br>";
                    $logger->notice("drop res=".$res);
                } else {
                    //DROP DATABASE db_name WITH (FORCE)
                    $sqlDrop = 'DROP DATABASE ' . $dbname . ' WITH (FORCE)';
                    $em->getConnection()->exec($sqlDrop);  // Execute native SQL
                    $em->flush();
                }
            }

            //2 create DB: php bin/console doctrine:database:create
            $create = $phpPath . ' ' . $projectRoot.'/bin/console doctrine:database:create';
            $logger->notice("create command=[".$create."]");
            $res = $this->runProcess($create);
            echo "create res=".$res."<br>";

            //Restore DB
            $res = $this->runProcess($sql);
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