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
use App\UserdirectoryBundle\Form\UploadSingleFileType;
use Doctrine\DBAL\Configuration;
use App\UserdirectoryBundle\Controller\OrderAbstractController;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\String\Slugger\SluggerInterface;

use TusPhp\Exception\TusException;
//use TusPhp\Exception\FileException;
use TusPhp\Exception\ConnectionException;


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
            return $this->redirect($this->generateUrl('employees_home'));
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

        //Testing
        //$res = $userServiceUtil->removeOldBackupFiles($networkDrivePath);
        //echo "Testing: removeOldBackupFiles: $res <br>";

        //$param = $userSecUtil->getSingleSiteSettingsParam();
        $param = $userServiceUtil->getSingleSiteSettingParameter();
        if( !$param ) {
            $this->addFlash(
                'pnotify-error',
                "Cannot continue with Backup: Please initialize the system with Miscellaneous on the Site Settings page"
            );
            return $this->redirect($this->generateUrl('employees_home'));
        }

        if( file_exists($networkDrivePath) == false ) {
            $this->createBackupPath($networkDrivePath);
            $this->addFlash(
                'notice',
                "Create backup folder $networkDrivePath on the server"
            );
        }
        if( file_exists($networkDrivePath) == false ) {
            $this->addFlash(
                'pnotify-error',
                "Could not create backup folder $networkDrivePath"
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

        $form = $this->createForm(UploadSingleFileType::class);
//        $form->handleRequest($request);
//        if ($form->isSubmitted() && $form->isValid()) {
//            /** @var UploadedFile $uploadFile */
//            $uploadFile = $form->get('uploadfile')->getData();
//            exit('manualBackupRestoreManagementAction uploadFile');
//        }

        //estimate DB backup time based on the size of /var/lib/pgsql
        $dbFolder = null;
        $dbBackupTime = 1; //min time
        $dbBackupSize = null;
        if( $userServiceUtil->isWindows() == false ) {
            $dbFolder = '/var/lib/pgsql/'; //Centos, Alma, Rhel
            if( !file_exists($dbFolder) ) {
                $dbFolder = '/var/lib/postgresql/'; //Ubuntu 22
            }
//
//            $io = popen('/usr/bin/du -sk ' . $dbFolder, 'r');
//            $size = fgets($io);
//            echo "DB 0size=$size, dbFolder=$dbFolder <br>";
//            $size = fgets($io, 4096);
//            echo "DB size=$size, dbFolder=$dbFolder <br>";
//            $size = substr($size, 0, strpos($size, "\t"));
//            pclose($io);
//
//            //SELECT pg_size_pretty( pg_database_size('dbname') );
//            $dbname = $this->getParameter('database_name');
//            $sql = "SELECT pg_size_pretty( pg_database_size('$dbname') );";
//            $logger->notice("sql=" . $sql);
//            $conn = $this->getConnection();
//            $stmt = $conn->prepare($sql);
//            $results = $stmt->executeQuery();
//            echo "DB results=$results<br>";

//            $size2 = $this->folderSize($dbFolder);
//            if( $size2 ) {
//                //$size2 = round($size2/1024);
//                $size2 = $this->convertBytesToReadable($size2);
//                echo "DB size2=$size2, dbFolder=$dbFolder <br>";
//            }

            $size = $this->getDbSize(); //bytes
            //echo "getDbSize=$size <br>";
            //exit('111');
            if( $size ) {
                $sizeGb = round($size / (1024 * 1000 * 1000)); //GB
                //echo 'Directory: ' . $dbFolder . ' => Size: ' . $size;
                //Assume 1 min for 2 GB
                if( $sizeGb ) {
                    $dbBackupTime = $sizeGb; //"; DB backup should take about " . $size . " min.";
                }
                $dbBackupSize = $this->convertBytesToReadable($size);
            }
        } else {
            $size = $this->getDbSize(); //bytes
            //echo "getDbSize=$size <br>";
            //exit('111');
            if( $size ) {
                $sizeGb = round($size / (1024 * 1000 * 1000)); //GB
                //echo 'Directory: ' . $dbFolder . ' => Size: ' . $size;
                //Assume 1 min for 1 GB
                if( $sizeGb ) {
                    $dbBackupTime = $sizeGb; //"; DB backup should take about " . $size . " min.";
                }
                $dbBackupSize = $this->convertBytesToReadable($size);
                //echo "dbBackupTime=$dbBackupTime, dbBackupSize=$dbBackupSize <br>";
            }
        }

        //estimate upload backup time based on the size of Uploaded folder
        $uploadFilesFolder = null;
        $uploadFilesBackupTime = 1; //min time 1 min
        $uploadFilesBackupSize = null;
        if( $userServiceUtil->isWindows() == false ) {
            $projectRoot = $this->container->get('kernel')->getProjectDir();
            $uploadFilesFolder = $projectRoot.DIRECTORY_SEPARATOR."public".DIRECTORY_SEPARATOR."Uploaded".DIRECTORY_SEPARATOR;

            $io = popen('/usr/bin/du -sk ' . $uploadFilesFolder, 'r');
            $size = fgets($io, 4096);
            //echo "Uploaded size=$size, uploadFilesFolder=$uploadFilesFolder <br>";
            $size = substr($size, 0, strpos($size, "\t"));
            pclose($io);

            //$size = $this->folderSize($uploadFilesFolder);
            //$size2 = $this->dirSize($uploadFilesFolder);
            //echo "Uploaded size2=$size2, uploadFilesFolder=$uploadFilesFolder <br>";
//            if( $size2 ) {
//                //$size2 = round($size2/1024);
//                $size2 = $this->convertBytesToReadable($size2);
//                echo "Uploaded size2=$size2, uploadFilesFolder=$uploadFilesFolder <br>";
//            }

            if( $size ) {
                $sizeGb = round($size / (1024 * 1000)); //GB
                //echo 'Directory: ' . $uploadFilesFolder . ' => Size: ' . $size;
                //Assume 1 min for 1 GB
                if( $sizeGb ) {
                    $uploadFilesBackupTime = $sizeGb; //"; Uploaded files backup should take about " . $size . " min.";
                }
                $uploadFilesBackupSize = $this->convertBytesToReadable($size);
            }
        }

        //echo "dbBackupTime=$dbBackupTime, uploadFilesBackupTime=$uploadFilesBackupTime <br>";
        $estimateTimeMsg = null;
        if( $dbBackupTime && $uploadFilesBackupTime ) {
            //Depending on the amount of data, database back up or restore should complete in under 5 minutes;
            // back up or restore of the uploaded files should complete in under 10 minutes.
            $estimateTimeMsg = "Depending on the amount of data,".
                " database back up or restore should complete in under $dbBackupTime minutes;" .
                " back up or restore of the uploaded files should complete in under $uploadFilesBackupTime minutes.";
        }
        if( $dbBackupTime && !$uploadFilesBackupTime ) {
            $estimateTimeMsg = "Depending on the amount of data,".
                " database back up or restore should complete in under $dbBackupTime minutes;";
        }
        if( !$dbBackupTime && $uploadFilesBackupTime ) {
            $estimateTimeMsg = "Depending on the amount of data,".
                " back up or restore of the uploaded files should complete in under $uploadFilesBackupTime minutes.";
        }

        //get free disk space for Upload and DB
        $now = new \DateTime();
        $now = $now->format('m/d/Y \a\t m:i:s A T');
        //echo "dbFolder=$dbFolder <br>";
        $dbFreeSpace = $this->getFreeSpace($dbFolder);
        $uploadFreeSpace = $this->getFreeSpace($uploadFilesFolder);

        if( $dbFreeSpace[1] == $uploadFreeSpace[1] ) {
            //Available free storage space on this server now (10/10/2023 at 10:07:15 PM EST) is XX GB.
            //Current database size is: YY GB. Current size of the folder with uploaded files used by the system is: 26.70 GB
            $freeSpace = "Available free storage space on this server now ($now) is $dbFreeSpace[1].".
                " Current database size is: $dbBackupSize.".
                " Current size of the folder with uploaded files used by the system is: $uploadFilesBackupSize";
        } else {
            //Available free storage space on this server now (10/10/2023 at 10:07:15 PM EST) is XX GB for the database (partition "A") and NN GB for the files (partition "B").
            //Current database size is: YY GB. Current size of the folder with uploaded files used by the system is: 26.70 GB
            $freeSpace = "Available free storage space on this server now ($now)".
                " is $dbFreeSpace[1] for the database (partition $dbFolder)".
                " and $uploadFreeSpace[1] for the files (partition $uploadFilesFolder)";
        }

        //$freeSpace = "Available Free Storage Space Now ($now) for DB: ".$dbFreeSpace[1].
        //    ", and for Uploaded Files: ".$uploadFreeSpace[1];
        //Available free storage space on this server now (10/10/2023 at 10:07:15 PM EST) is XX GB.
        //Current database size is: YY GB. Size of the folder with uploaded files used by the system is: 26.70 GB
        //$freeSpace = "Available free storage space on this server now ($now) is $dbFreeSpace[1] for database".
        //    " and $uploadFreeSpace[1] for upload files.";

        //Logged in users
        $loggedInUsers = $userSecUtil->getLoggedInUserEntities();
        
        $maintenanceStatus = $userSecUtil->getSiteSettingParameter('maintenance');
        if( $maintenanceStatus ) {
            $maintenanceStatus = "<span class='text-danger'>Enabled</span>";
            $pathMaintenanceStop = $this->generateUrl('employees_change_maintenance_status',['status'=>'disable']);
            $maintenanceAction = "<a".
                        " general-data-confirm='Are you sure you want to Stop Maintenance Mode (Enable users to log in)?'".
                        " class='btn btn-info' href='".$pathMaintenanceStop."'".
                        ">Stop Maintenance Mode (Enable users to log in)</a>";
        } else {
            $maintenanceStatus = "<span class='text-success'>Disabled</span>";
            $pathMaintenanceStart = $this->generateUrl('employees_change_maintenance_status',['status'=>'enable']);
            $maintenanceAction = "<a".
                " general-data-confirm='Start Maintenance Mode (Log out users, prevent logins,".
                " and show maintenance mode message on the login page)?'".
                " class='btn btn-info' href='".$pathMaintenanceStart."'".
                ">Start Maintenance Mode (Log out users, prevent logins, and show maintenance mode message on the login page)</a>";
        }
        

        return array(
            'sitename' => $sitename,
            'title' => "Data Backup Management",
            'cycle' => 'new',
            'networkDrivePath' => $networkDrivePath,
            'backupFiles' => $backupFiles,
            'environments' => $environments,
            'form' => $form,
            //'dbBackupTime' => $dbBackupTime,
            //'uploadFilesBackupTime' => $uploadFilesBackupTime,
            'estimateTimeMsg' => $estimateTimeMsg,
            'dbFreeSpaceBytes' => $dbFreeSpace[0],
            'uploadFreeSpaceBytes' => $uploadFreeSpace[0],
            'freeSpace' => $freeSpace,
            'loggedInUsers' => $loggedInUsers,
            'maintenanceStatus' => $maintenanceStatus,
            'maintenanceAction' => $maintenanceAction
        );
    }
    public function getFreeSpace( $folder ) {
        //get free disk space for Upload and DB
        $bytes = disk_free_space($folder);
        $si_prefix = array( 'B', 'KB', 'MB', 'GB', 'TB', 'EB', 'ZB', 'YB' );
        $base = 1024;
        $class = min((int)log($bytes , $base) , count($si_prefix) - 1);
        //echo $folder.": ".$bytes . '<br />';
        $res = sprintf('%1.2f' , $bytes / pow($base,$class)) . ' ' . $si_prefix[$class];
        return array($base,$res);
    }
    public function convertBytesToReadable( $bytes ) {
        $si_prefix = array( 'B', 'KB', 'MB', 'GB', 'TB', 'EB', 'ZB', 'YB' );
        $base = 1024;
        $class = min((int)log($bytes , $base) , count($si_prefix) - 1);
        //echo $folder.": ".$bytes . '<br />';
        $res = sprintf('%1.2f' , $bytes / pow($base,$class)) . ' ' . $si_prefix[$class];
        return $res; //array($base,$res);
    }
    //https://gist.github.com/eusonlito/5099936
    public function folderSize($dir)
    {
        $size = 0;
        foreach (glob(rtrim($dir, '/').'/*', GLOB_NOSORT) as $each) {
            $size += is_file($each) ? filesize($each) : $this->folderSize($each);
        }
        return $size;
    }
    /**
     * Get the directory size
     * @param  string $directory
     * @return integer
     */
    function dirSize($directory) {
        $size = 0;
        foreach(new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($directory)) as $file){
            $size+=$file->getSize();
        }
        return $size;
    }
    function getDbSize() {
        $dbname = $this->getParameter('database_name');
        //SELECT pg_size_pretty( pg_database_size('dbname') );
        //$sql = "SELECT pg_size_pretty(pg_database_size('$dbname'));";
        $sql = "SELECT pg_database_size('$dbname');";
        //echo "sql=" . $sql . "<br>";
        $conn = $this->getConnection();
        $stmt = $conn->prepare($sql);
        $results = $stmt->executeQuery()->fetchAll(\PDO::FETCH_COLUMN);
        if( $results && count($results) > 0 ) {
            return $results[0];
        }
        return null;
        //dump($results);
        //exit('111');
    }

    #[Route(path: '/change-maintenance-status/{status}', name: 'employees_change_maintenance_status', methods: ['GET'])]
    #[Template('AppUserdirectoryBundle/DataBackup/manual_backup_restore.html.twig')]
    public function changeMaintenanceStatusAction(Request $request, $status) {
        if( false === $this->isGranted('ROLE_PLATFORM_ADMIN') ) {
            return $this->redirect( $this->generateUrl('employees-nopermission') );
        }

        $em = $this->getDoctrine()->getManager();
        //$userSecUtil = $this->container->get('user_security_utility');
        //$param = $userSecUtil->getSingleSiteSettingsParam();
        $userServiceUtil = $this->container->get('user_service_utility');
        $param = $userServiceUtil->getSingleSiteSettingParameter();

        if( $status == 'enable' ) {
            $param->setMaintenance(true);
            $this->addFlash(
                'notice',
                "Maintenance mode enabled"
            );
            $em->flush();
        }
        elseif ( $status == 'disable' ) {
            $param->setMaintenance(false);
            $this->addFlash(
                'notice',
                "Maintenance mode disabled"
            );
            $em->flush();
        } else {
            $this->addFlash(
                'notice',
                "Invalid status. Maintenance mode is unchanged"
            );
        }

        return $this->redirect($this->generateUrl('employees_manual_backup_restore'));
    }

    //NOT USED. Use asynchronous version via ajax
    //TODO: from None\nconfigparser.NoSectionError: No section: 'postgresql'
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
            // $res = $this->dbManagePython($networkDrivePath,'backup'); //Use python script pg_dump
            $userServiceUtil = $this->container->get('user_service_utility');
            $res = $userServiceUtil->dbManagePython($networkDrivePath,'backup'); //Working: Use python script pg_dump
            //exit($res);

            $resStatus = $res['status'];
            $resStr = $res['message'];

            if( $resStatus == 'OK' ) {
                $resStr = "Backup successfully created in folder $networkDrivePath";
                $this->addFlash(
                    'notice',
                    $resStr
                );

                //Event Log
                $user = $this->getUser();
                $sitename = $this->getParameter('employees.sitename');
                $userSecUtil->createUserEditEvent($sitename,$resStr,$user,null,$request,'Create Backup Database');
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

    #[Route(path: '/create-db-backup-ajax/', name: 'employees_create_db_backup_ajax', methods: ['POST'], options: ['expose' => true])]
    public function createDbBackupAjaxAction(Request $request) {

        if( false === $this->isGranted('ROLE_PLATFORM_ADMIN') ) {
            //return $this->redirect( $this->generateUrl('employees-nopermission') );
            $res = array(
                'message' => "Error DB backup",
                'status' => 'Error'
            );
            $response = new Response();
            $response->setContent(json_encode($res));
            return $response;
        }

        //networkDrivePath
        $userSecUtil = $this->container->get('user_security_utility');
        $networkDrivePath = $userSecUtil->getSiteSettingParameter('networkDrivePath');

        //echo "networkDrivePath=".$networkDrivePath."<br>";
        if( !$networkDrivePath ) {
            //exit("No networkDrivePath is defined");
//            $this->addFlash(
//                'pnotify-error',
//                //'notice',
//                "Cannot continue
//                 with Backup: No Network Drive Path is defined in the Site Settings"
//            );
//            return $this->redirect($this->generateUrl('employees_manual_backup_restore'));
            $res = array(
                'message' => "No Network Drive Path is defined in the Site Settings",
                'status' => 'Error'
            );
            $response = new Response();
            $response->setContent(json_encode($res));
            return $response;
        }

        if( $networkDrivePath ) {

//            //Testing
//            $res = array(
//                'message' => "Test OK",
//                'status' => 'OK'
//            );
//            $response = new Response();
//            $response->setContent(json_encode($res));
//            return $response;

            //create backup
            //$res = $this->creatingBackupSQLFull($networkDrivePath); //Use php based pg_dump
            // $res = $this->dbManagePython($networkDrivePath,'backup'); //Use python script pg_dump
            $userServiceUtil = $this->container->get('user_service_utility');
            $resPython = $userServiceUtil->dbManagePython($networkDrivePath,'backup'); //Working: Use python script pg_dump
            //exit($res);

            //$resStatus = $resPython['status'];
            //$resStr = $resPython['message'];

            if( $resPython && $resPython['status'] == 'OK' ) {
                $resStr = "DB backup successfully created in folder $networkDrivePath" . ".<br>" . $resPython['message'];
                //Event Log
                $user = $this->getUser();
                $sitename = $this->getParameter('employees.sitename');
                $userSecUtil->createUserEditEvent($sitename,$resStr,$user,null,$request,'Create Backup Database');
                //Send email
                $emailUtil = $this->container->get('user_mailer_utility');
                $subject = "DB backup successfully created";
                if( $user ) {
                    $usersEmails[] = $user->getSingleEmail();
                }
                $siteEmail = $userSecUtil->getSiteSettingParameter('siteEmail');
                if( $siteEmail ) {
                    $usersEmails[] = $siteEmail;
                }
                //                 $email, $subject, $message, $em, $ccs=null, $adminemail=null
                $emailUtil->sendEmail($usersEmails, $subject, $resStr);

//                $res = array(
//                    'message' => "Backup successfully created in folder ".  addslashes($networkDrivePath),
//                    'status' => 'OK'
//                );
                $res = array(
                    'message' => sprintf("DB backup successfully created in folder %s", $networkDrivePath),
                    'status' => 'OK'
                );
            } else {
//                $this->addFlash(
//                    'pnotify-error',
//                    $resStr
//                );
            }

        } else {
//            $this->addFlash(
//                'pnotify-error',
//                "Error backup"
//            );
            $res = array(
                'message' => "Error DB backup",
                'status' => 'Error'
            );
        }

        $response = new Response();
        //$response->headers->set('Content-Type', 'application/json');
        $response->setContent(json_encode($res));
        return $response;
    }
    #[Route(path: '/create-upload-folder-backup-ajax/', name: 'employees_create_uploadfolder_backup_ajax', methods: ['POST'], options: ['expose' => true])]
    public function createUploadFolderBackupAjaxAction(Request $request) {

        if( false === $this->isGranted('ROLE_PLATFORM_ADMIN') ) {
            //return $this->redirect( $this->generateUrl('employees-nopermission') );
            $res = array(
                'message' => "Error UploadFolder backup",
                'status' => 'Error'
            );
            $response = new Response();
            $response->setContent(json_encode($res));
            return $response;
        }

        //networkDrivePath
        $userSecUtil = $this->container->get('user_security_utility');
        $networkDrivePath = $userSecUtil->getSiteSettingParameter('networkDrivePath');

        //echo "networkDrivePath=".$networkDrivePath."<br>";
        if( !$networkDrivePath ) {
            //exit("No networkDrivePath is defined");
//            $this->addFlash(
//                'pnotify-error',
//                //'notice',
//                "Cannot continue
//                 with Backup: No Network Drive Path is defined in the Site Settings"
//            );
//            return $this->redirect($this->generateUrl('employees_manual_backup_restore'));
            $res = array(
                'message' => "No Network Drive Path is defined in the Site Settings",
                'status' => 'Error'
            );
            $response = new Response();
            $response->setContent(json_encode($res));
            return $response;
        }

        if( $networkDrivePath ) {

//            //Testing
//            $res = array(
//                'message' => "Test OK",
//                'status' => 'OK'
//            );
//            $response = new Response();
//            $response->setContent(json_encode($res));
//            return $response;

            //create backup
            //$res = $this->creatingBackupSQLFull($networkDrivePath); //Use php based pg_dump
            // $res = $this->dbManagePython($networkDrivePath,'backup'); //Use python script pg_dump
            $userServiceUtil = $this->container->get('user_service_utility');
            //$resPython = $userServiceUtil->dbManagePython($networkDrivePath,'backup'); //Working: Use python script pg_dump
            $resPython = $userServiceUtil->createBackupUpload();
            //exit($res);

            //$resStatus = $resPython['status'];
            //$resStr = $resPython['message'];

            if( $resPython && $resPython['status'] == 'OK' ) {
                $resStr = "Backup of the uploaded folder has been successfully created in folder $networkDrivePath" . ".<br>" . $resPython['message'];
                //Event Log
                $user = $this->getUser();
                $sitename = $this->getParameter('employees.sitename');
                $userSecUtil->createUserEditEvent($sitename,$resStr,$user,null,$request,'Create Backup Database');
                //Send email
                $emailUtil = $this->container->get('user_mailer_utility');
                $subject = "Backup of the uploaded folder has been successfully created";
                if( $user ) {
                    $usersEmails[] = $user->getSingleEmail();
                }
                $siteEmail = $userSecUtil->getSiteSettingParameter('siteEmail');
                if( $siteEmail ) {
                    $usersEmails[] = $siteEmail;
                }
                //                 $email, $subject, $message, $em, $ccs=null, $adminemail=null
                $emailUtil->sendEmail($usersEmails, $subject, $resStr);

//                $res = array(
//                    'message' => "Backup successfully created in folder ".  addslashes($networkDrivePath),
//                    'status' => 'OK'
//                );
                $res = array(
                    'message' => sprintf("Backup of the uploaded folder has been successfully created in folder %s", $networkDrivePath),
                    'status' => 'OK'
                );
            } else {
//                $this->addFlash(
//                    'pnotify-error',
//                    $resStr
//                );
            }

        } else {
//            $this->addFlash(
//                'pnotify-error',
//                "Error backup"
//            );
            $res = array(
                'message' => "Error uploaded folder backup",
                'status' => 'Error'
            );
        }

        $response = new Response();
        //$response->headers->set('Content-Type', 'application/json');
        $response->setContent(json_encode($res));
        return $response;
    }

    #[Route(path: '/restore-backup-ajax/', name: 'employees_restore_backup_ajax', methods: ['POST'], options: ['expose' => true])]
    public function restoreDbAjaxAction( Request $request ) {

        if( false === $this->isGranted('ROLE_PLATFORM_ADMIN') ) {
            return $this->redirect( $this->generateUrl('employees-nopermission') );
        }

        $logger = $this->container->get('logger');
        $em = $this->getDoctrine()->getManager();
        $userSecUtil = $this->container->get('user_security_utility');
        $userServiceUtil = $this->container->get('user_service_utility');

        $environment = $userSecUtil->getSiteSettingParameter('environment');
        if( $environment == 'live' ) {
            $liveServerMsg = "Live server: restore not allowed. ".
            "Change environment from 'live' to 'test', 'dev' or 'demo' in the site settings.";
            exit($liveServerMsg);
            $logger->notice($liveServerMsg);
            $output = array(
                'status' => "NOTOK",
                'message' => $liveServerMsg
            );
            $response = new Response();
            $response->setContent(json_encode($output));
            return $response;
        }
        //exit('Not Allowed');

        $backupFileName = $request->get('fileId');
        $env = $request->get('env');
        $logger->notice("restoreDbAjaxAction: backupFilePath=".$backupFileName."; env=".$env);
        //echo "backupFilePath=".$fileId."; env=".$env."<br>";
        //exit('111');

        if( str_contains($backupFileName,'backupdb') && str_contains($backupFileName,'.gz') ) {
            //ok
        } else {
            $output = array(
                'status' => "NOTOK",
                'message' => "Invalid backup DB file name:".
                    $backupFileName.
                    " File must contain 'backupdb' and '.gz' substring.".
                    " Example: backupdb-***.dump.gz"
            );
            $response = new Response();
            $response->setContent(json_encode($output));
            return $response;
        }

        //Restore DB
        $output = $this->restoreDBWrapper($backupFileName,$env);

        $response = new Response();
        $response->setContent(json_encode($output));
        return $response;
    }
    public function restoreDBWrapper( $backupFileName, $env ) {
        if( false === $this->isGranted('ROLE_PLATFORM_ADMIN') ) {
            $output = array(
                'status' => 'NOTOK',
                'message' => "No permission"
            );
            return $output;
        }

        ini_set('max_execution_time', 0);
        ini_set('max_input_time', 0);
        ini_set("default_socket_timeout", 6000); //sec

        $logger = $this->container->get('logger');
        $em = $this->getDoctrine()->getManager();
        $userSecUtil = $this->container->get('user_security_utility');
        $userServiceUtil = $this->container->get('user_service_utility');

        $user = $this->getUser();
        $userStr = $user."";

        //Original site settings
        $origVersion = $userSecUtil->getSiteSettingParameter('version');
        $version = "Restored $backupFileName by $userStr on " . $date = date('Y-m-d H-i-s');
        if( $origVersion ) {
            $version = $origVersion . "\n\r" . $version;
        }

        $siteEmail = $userSecUtil->getSiteSettingParameter('siteEmail');
        if( !$siteEmail ) {
            $siteEmail = "myemail@example.com";
        }
        //$exceptionUsers = $userSecUtil->getSiteSettingParameter('emailCriticalErrorExceptionUsers');
        //$mailerDeliveryAddresses = (string)$userSecUtil->getSiteSettingParameter('mailerDeliveryAddresses');
        $monitorScript = $userSecUtil->getSiteSettingParameter('monitorScript');
        if( $monitorScript ) {
            $monitorScript = str_replace("'","''",$monitorScript);
        }
        $connectionChannel = $userSecUtil->getSiteSettingParameter('connectionChannel');
        if( !$connectionChannel ) {
            $connectionChannel = 'http';
        }

        $filesBackupConfig = $userSecUtil->getSiteSettingParameter('filesBackupConfig');
        $logger->notice("restore DBWrapper: filesBackupConfig=$filesBackupConfig");
        if( $filesBackupConfig ) {
            $filesBackupConfig = str_replace("'","''",$filesBackupConfig);
        }
        $logger->notice("restore DBWrapper: modified filesBackupConfig=$filesBackupConfig");

        //Get restart db version for 'sudo systemctl restart postgresql-14'
        $postgreVersionStr = $userServiceUtil->getDBVersionStr(); //postgresql-14
        $logger->notice("restore DBWrapper: postgreVersionStr=$postgreVersionStr");

        //if(0) {
        //$mailerDeliveryAddresses = (string)$userSecUtil->getSiteSettingParameter('mailerDeliveryAddresses');
        //$environment = $userSecUtil->getSiteSettingParameter('environment');
        //$liveSiteRootUrl = $userSecUtil->getSiteSettingParameter('liveSiteRootUrl');
        //$connectionChannel = $userSecUtil->getSiteSettingParameter('connectionChannel');
        //}

        //exit('Under construction: backupFilePath='.$backupFileName);
        //create backup

        $networkDrivePathOrig = $userSecUtil->getSiteSettingParameter('networkDrivePath');
        $networkDrivePath = realpath($networkDrivePathOrig);
        //$backupFilePath = $networkDrivePath. DIRECTORY_SEPARATOR . $backupFilePath;

        //Event Log
//        $user = $this->getUser();
//        $sitename = $this->getParameter('employees.sitename');
//        $resStr = "Restoring database from backup " . $backupFileName . " located in folder " . $networkDrivePath .
//            " by " . $user . ". Site settings parameters: env=$env, mailerdeliveryaddresses=$siteEmail, connectionChannel=$connectionChannel";
//        $userSecUtil->createUserEditEvent($sitename,$resStr,$user,null,$request,'Restore Backup Database');

        $logger->notice("Before dbManagePython: networkDrivePath=$networkDrivePath");

        //$res = $this->restoringBackupSQLFull($backupFilePath);
        //$res = $this->restoringBackupSQLFull_Plain($backupFilePath);
        //$res = $this->dbManagePython($networkDrivePath,'restore',$backupFileName); //Working: Use python script pg_restore
        $userServiceUtil = $this->container->get('user_service_utility');
        //$res = $userServiceUtil->dbManagePython($networkDrivePath,'backup'); //Use python script pg_restore
        $res = $userServiceUtil->dbManagePython($networkDrivePath,'restore',$sync=false,$backupFileName); //Use python script pg_restore
        //exit($res);
        
        //Testing
        $output = array(
            'status' => 'OK',
            'message' => "dbManagePython Message=".implode("; ",$res)
        );
        return $output;

        $logger->notice("After dbManagePython");

        $resStatus = $res['status'];
        $resStr = $res['message'];

        $logger->notice("After dbManagePython: status=$resStatus, resStr=$resStr");

        if( $resStatus == 'OK' ) {

            //$param = $userSecUtil->getSingleSiteSettingsParam();
            $userServiceUtil = $this->container->get('user_service_utility');
            $param = $userServiceUtil->getSingleSiteSettingParameter();
            $logger->notice("After get settings parameters. paramId=" . $param->getId());

            if( $param && $param->getId() ) {

                $logger->notice("before getConnection");
                $conn = $this->getConnection();
                $logger->notice("after getConnection");

//                    if( $env == 'live' ) {
//                        $siteEmail == '';
//                    }

                //filesBackupConfig - json file with backup config (server dependents: /srv/order-lab/orderflex/public)
                //monitorScript - json file with health monitor config (server dependents: /srv/order-lab/webmonitor/webmonitor.py, /srv/order-lab/orderflex/var/log/webmonitor.log )

                ///////////// Update site parameters for newly restored DB /////////////////
                if(1) {
//                    //App\\UserdirectoryBundle\\Entity\\SiteParameters (user_siteparameters)
//                    $sql = "UPDATE user_siteparameters" .
//                        " SET mailerdeliveryaddresses='$siteEmail', environment='$env', version='$version'" .
//                        //", filesBackupConfig='$filesBackupConfig'".
//                        ", monitorScript='$monitorScript'" .
//                        ", connectionChannel='$connectionChannel'" .
//                        ", networkDrivePath='$networkDrivePathOrig'" .
//                        " WHERE id=" . $param->getId();
//                    //$sql = "SELECT id, mailerdeliveryaddresses FROM user_siteparameters";
//                    $logger->notice("sql=" . $sql);
//
//                    $stmt = $conn->prepare($sql);
//                    $logger->notice("restore DBWrapper: after prepare");
//
//                    $results = $stmt->executeQuery();
//                    $logger->notice("restore DBWrapper: after executeQuery");

                    $setparams =
                        "mailerdeliveryaddresses='$siteEmail', environment='$env', version='$version'" .
                        //", filesBackupConfig='$filesBackupConfig'".
                        ", monitorScript='$monitorScript'" .
                        ", connectionChannel='$connectionChannel'" .
                        ", networkDrivePath='$networkDrivePathOrig'"
                    ;

                    if( $filesBackupConfig ) {
                        $setparams = $setparams . ", filesBackupConfig='$filesBackupConfig'";
                    }

                    //App\\UserdirectoryBundle\\Entity\\SiteParameters (user_siteparameters)
                    $sql = "UPDATE user_siteparameters" .
                        " SET " . $setparams .
//                        "mailerdeliveryaddresses='$siteEmail', environment='$env', version='$version'" .
//                        ", filesBackupConfig='$filesBackupConfig'".
//                        ", monitorScript='$monitorScript'" .
//                        ", connectionChannel='$connectionChannel'" .
//                        ", networkDrivePath='$networkDrivePathOrig'" .
                        " WHERE id=" . $param->getId();
                    //$sql = "SELECT id, mailerdeliveryaddresses FROM user_siteparameters";
                    $logger->notice("sql=" . $sql);

                    $stmt = $conn->prepare($sql);
                    $logger->notice("restore DBWrapper: after prepare");

                    $results = $stmt->executeQuery();
                    $logger->notice("restore DBWrapper: after executeQuery");
                }
                ///////////// EOF Update site parameters for newly restored DB /////////////////

                //try to restart postgres sudo systemctl restart postgresql-14
                if(0) {
                    if ($postgreVersionStr) {
                        $dbRestartCommand = "sudo systemctl restart $postgreVersionStr";
                        $logger->notice("restore DBWrapper: before dbRestartCommand=$dbRestartCommand");

                        $res = $userServiceUtil->runCommandByPython($dbRestartCommand);
                        $logger->notice("restore DBWrapper: after dbRestartCommand: res=" . $res);

                        $resApache = $userServiceUtil->restartApache();
                        $logger->notice("restore DBWrapper: after restartApache: resApache=" . $resApache);

                        //test
                        //$testParam = $userSecUtil->getSiteSettingParameter('connectionChannel');
                        //$logger->notice("restore DBWrapper: testParam=$testParam");

                        ///////////// Update site parameters for newly restored DB /////////////////
//                    $logger->notice("restore DBWrapper: before em");
//                    $em = $this->getDoctrine()->getManager();
//                    $param = $userSecUtil->getSingleSiteSettingsParam();
//                    $logger->notice("restore DBWrapper: param id=".$param->getId());
//                    $param->setMailerDeliveryAddresses($siteEmail);
//                    $param->setEnvironment($env);
//                    $param->setVersion($version);
//                    $param->setFilesBackupConfig($filesBackupConfig);
//                    $param->setMonitorScript($monitorScript);
//                    $param->setConnectionChannel($connectionChannel);
//                    $param->setNetworkDrivePath($networkDrivePathOrig);
//                    $logger->notice("restore DBWrapper: before flush param");
//                    $em->flush();
//                    $logger->notice("restore DBWrapper: after flush param");
                        ///////////// EOF Update site parameters for newly restored DB /////////////////
                    }
                }

//                    //re-deploy
//                    $projectRoot = $this->container->get('kernel')->getProjectDir();
//                    //$projectRoot = C:\Users\ch3\Documents\MyDocs\WCMC\ORDER\order-lab\orderflex
//                    $this->runProcess("bash " . $projectRoot . DIRECTORY_SEPARATOR . "deploy.sh");

                //Generate cron jobs only for live server
                if( $env == 'live' ) {
                    //Generate cron jobs:
                    //1) Create cron jobs (Email spooling, Fellowship Import, Fellowship Verification, Unpaid Invoices, Project Expiration)
                    // - directory/admin/list/generate-cron-jobs/
                    $userServiceUtil->createCrons();

                    //2) Create status cron job (check if the system in the maintenance mode):
                    // - directory/admin/list/generate-cron-jobs/status
                    //$userServiceUtil->createStatusCronLinux(); //included in $userServiceUtil->createCrons();

                    //3) Create useradstatus cron job (update users AD status)
                    // - directory/admin/list/generate-useradstatus-cron/
                    $userServiceUtil->createUserADStatusCron('6h');

                    //4) Create backup cron jobs based on the JSON file
                    // - /directory/admin/list/update-cron-job/uploads-live-HOURLY/filesBackupConfig
                    // - /directory/admin/list/update-cron-job/db-mount-HOURLY/filesBackupConfig
                }
            }//if $param

            $logger->notice("restore DBWrapper: after param if");

            $resStr =
                "Restored database " . $backupFileName . " by " . $userStr . "<br>" .
                $resStr .
                "<br>The next steps would be:".
                " <br>- Make sure that the local administrator user and associated password".
                " is set if the backup is used outside the institutional intranet network".
                " <br>- Make sure the  public 'Uploaded' folder corresponds to the restored DB.".
                " <br>- Verify the site settings.".
                //" Specifically, currently, connectionChannel=$connectionChannel, mailerdeliveryaddresses=$siteEmail".
                " The following site settings parameters were preserved from the original DB:".
                " mailerdeliveryaddresses, monitorScript, connectionChannel, networkDrivePath, filesBackupConfig".
                " <br>- Verify cron jobs. Replace the working paths if the server is different".
                " <br>- It might be necessary to run the deploy_prod.sh script."
            ;

            $this->addFlash(
                'notice',
                $resStr
            );
            $logger->notice("restore DBWrapper: after addFlash");

            //$logger->notice("After restore DB: resStr=".$resStr);

//            if( $siteEmail ) {
//                //sendEmail uses DB => don't do call it here
//                $emailUtil = $this->container->get('user_mailer_utility');
//                $subject = "Warning: Database restored by ".$userStr;
//                //                 $email, $subject, $message, $em, $ccs=null, $adminemail=null
//                $emailUtil->sendEmail($siteEmail, $subject, $resStr);
//                $logger->notice("restore DBWrapper: after send email");
//            }

            //Can't use doctrine directly: SQLSTATE[HY000]: General error: 7 FATAL:  terminating connection due to administrator command server closed the connection unexpectedly
            //Event Log
            //$user = $this->getUser();
            //$sitename = $this->getParameter('employees.sitename');
            //$userSecUtil->createUserEditEvent($sitename,$resStr,$user,null,$request,'Restore Backup Database');

            $output = array(
                'status' => 'OK',
                'message' => $resStr
            );
        } else { //if $resStatus
            $output = array(
                'status' => 'NOTOK',
                'message' => $resStr
            );
        }

        return $output;
    }

    //http://127.0.0.1/directory/send-confirmation-email/
    #[Route(path: '/send-confirmation-email', name: 'employees_send_confirmation_email', methods: ['POST'])]
    public function sendConfirmationEmailAction( Request $request )
    {
        //TODO: add Rate Limiter
        //$limiter = $anonymousApiLimiter->create($request->getClientIp());
        // the argument of consume() is the number of tokens to consume
        // and returns an object of type Limit
//        if (false === $limiter->consume(1)->isAccepted()) {
//            throw new TooManyRequestsHttpException();
//        }

        $userServiceUtil = $this->container->get('user_service_utility');
        $status = $request->get('status');
        $userServiceUtil->completeDbRestoreEmail($status);
        //return new Response('Email sent!');
        return new JsonResponse(['message' => 'Email sent', 'status' => 200]);
    }

    #[Route(path: '/post-restore-ajax/', name: 'employees_post_restore_ajax', methods: ['POST'], options: ['expose' => true])]
    public function postRestoreAjaxAction( Request $request )
    {
        $logger = $this->container->get('logger');
        $logger->notice("postRestoreAjaxAction");

        if (false === $this->isGranted('ROLE_PLATFORM_ADMIN')) {
            return $this->redirect($this->generateUrl('employees-nopermission'));
        }

        $output = $this->postRestore($request);
        $response = new Response();
        $response->setContent(json_encode($output));
        return $response;

        if(0) {
            ini_set('max_execution_time', 0);
            ini_set('max_input_time', 0);
            ini_set("default_socket_timeout", 6000); //sec

            $logger = $this->container->get('logger');
            //$em = $this->getDoctrine()->getManager();
            //$userSecUtil = $this->container->get('user_security_utility');
            //$userServiceUtil = $this->container->get('user_service_utility');

            $logger->notice("before deploy");

            //re-deploy
            $projectRoot = $this->container->get('kernel')->getProjectDir();
            $this->runProcess("bash " . $projectRoot . DIRECTORY_SEPARATOR . "deploy.sh");

            $logger->notice("Post restore completed");

            $output = array(
                'status' => 'OK',
                'message' => 'Post restore completed'
            );
            $response = new Response();
            $response->setContent(json_encode($output));
            return $response;
        }
    }
    public function postRestore( Request $request )
    {
        $logger = $this->container->get('logger');
        $logger->notice("postRestoreAjaxAction");

        if (false === $this->isGranted('ROLE_PLATFORM_ADMIN')) {
            return $this->redirect($this->generateUrl('employees-nopermission'));
        }

        ini_set('max_execution_time', 0);
        ini_set('max_input_time', 0);
        ini_set("default_socket_timeout", 6000); //sec

        //$em = $this->getDoctrine()->getManager();
        //$userSecUtil = $this->container->get('user_security_utility');
        //$userServiceUtil = $this->container->get('user_service_utility');

        $logger->notice("before deploy");

        //re-deploy
        $projectRoot = $this->container->get('kernel')->getProjectDir();
        $this->runProcess("bash " . $projectRoot . DIRECTORY_SEPARATOR . "deploy.sh");

        $logger->notice("Post restore completed");

        $output = array(
            'status' => 'OK',
            'message' => 'Post restore completed: deploy.sh script'
        );

        return $output;
    }

    #[Route(path: '/post-restore-eventlog-ajax/', name: 'employees_post_restore_eventlog_ajax', methods: ['POST'], options: ['expose' => true])]
    public function postRestoreEventLogAjaxAction( Request $request )
    {
        $logger = $this->container->get('logger');
        $logger->notice("postRestoreAjaxAction");
        
        if (false === $this->isGranted('ROLE_PLATFORM_ADMIN')) {
            return $this->redirect($this->generateUrl('employees-nopermission'));
        }

        $userSecUtil = $this->container->get('user_security_utility');
        $user = $this->getUser();
        $userStr = $user."";
        $sitename = $this->getParameter('employees.sitename');

        $type = $request->get('type');
        $msg = $request->get('msg');

        $resStr = "Restored ".$type." by $userStr. msg=$msg";

        //Event Log
        $userSecUtil->createUserEditEvent($sitename,$resStr,$user,null,$request,'Restore Backup Database');

        $output = array(
            'status' => 'OK',
            'message' => 'event log completed'
        );
        $response = new Response();
        $response->setContent(json_encode($output));
        return $response;
    }
    #[Route(path: '/post-restore-eventlog/{type}/{msg}', name: 'employees_post_restore_eventlog', methods: ['GET'], options: ['expose' => true])]
    public function postRestoreEventLogAction( Request $request, $type, $msg )
    {
        $logger = $this->container->get('logger');
        $logger->notice("postRestoreEventLogAction");

        if (false === $this->isGranted('ROLE_PLATFORM_ADMIN')) {
            return $this->redirect($this->generateUrl('employees-nopermission'));
        }

        $userSecUtil = $this->container->get('user_security_utility');
        //$emailUtil = $this->container->get('user_mailer_utility');

        $user = $this->getUser();
        $userStr = $user."";

        $type = $request->get('type'); //db or files
        $msg = $request->get('msg');

        if( $type ) {
            $type = strtoupper($type);
        }

        if( $msg == 'timeout' ) {
            $msg = "Restored with Gateway Timeout";
        }

        $resStr = "Restored ".$type." by $userStr. msg=$msg";
        $logger->notice("postRestoreEventLogAction: before event log: $resStr");

        //Event Log
        $sitename = $this->getParameter('employees.sitename');
        $userSecUtil->createUserEditEvent($sitename,$resStr,$user,null,$request,'Restore Backup Database');
        $logger->notice("postRestoreEventLogAction: after event log");

//        //sendEmail uses DB => don't do call it here
//        $siteEmail = $userSecUtil->getSiteSettingParameter('siteEmail');
//        if( $siteEmail ) {
//            $subject = $type." restored by " . $userStr;
//            //                 $email, $subject, $message, $em, $ccs=null, $adminemail=null
//            $emailUtil->sendEmail($siteEmail, $subject, $resStr);
//            $logger->notice("postRestoreEventLogAction: after send email");
//        }

        return $this->redirect($this->generateUrl('employees_manual_backup_restore'));
    }

//    public function updateSiteSettingsParam() {
//        $userSecUtil = $this->container->get('user_security_utility');
//        $param = $userSecUtil->getSingleSiteSettingsParam();
//
//    }

    //NOT USED, was replaced by restoreDbAjaxAction. Call restore directly
    #[Route(path: '/restore-backup/{backupFilePath}', name: 'employees_restore_backup', methods: ['GET'], options: ['expose' => true])]
    #[Template('AppUserdirectoryBundle/DataBackup/data_backup_management.html.twig')]
    public function restoreDirectBackupAction( Request $request, $backupFilePath ) {

        //exit('Not Allowed');

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

            $output = $this->restoreDBWrapper($backupFilePath,$env='test');

            if( $output['status'] == 'OK' ) {
                $this->addFlash(
                    'notice',
                    $output['message']
                );
            } else {
                $this->addFlash(
                    'warning',
                    $output['message']
                );
            }

            $postOutput = $this->postRestore($request);
            if( $postOutput['status'] == 'OK' ) {
                $this->addFlash(
                    'notice',
                    $postOutput['message']
                );
            } else {
                $this->addFlash(
                    'warning',
                    $postOutput['message']
                );
            }
        }

        return $this->redirect($this->generateUrl('employees_manual_backup_restore'));
    }

//    //NOT USED. Use dbManagePython in UserServiceUtil instead
//    //Backup DB
//    //Use python's script order-lab\utils\db-manage\postgres-manage-python\manage_postgres_db.py
//    public function dbManagePython( $networkDrivePath, $action, $backupFileName=null ) {
//        exit("NOT USED. Use dbManagePython in UserServiceUtil instead");
//        if ( false == $this->isGranted('ROLE_PLATFORM_DEPUTY_ADMIN') ) {
//            return $this->redirect($this->generateUrl('employees-nopermission'));
//        }
//
//        $userServiceUtil = $this->container->get('user_service_utility');
//        if( $userServiceUtil->isWindows() ){
//            $res = array(
//                'status' => "NOTOK",
//                'message' => "DB management is not implemented for Windows"
//            );
//            return $res;
//        }
//
//        //manage_postgres_db.py is using sample.config file with a local storage as a destination path=/tmp/backups/
//        //$filepath is provided by site settings networkDrivePath => manage_postgres_db.py should accept --path
//
//        $logger = $this->container->get('logger');
//
//        $dbName = $this->getParameter('database_name');
//        if( !$dbName ) {
//            $res = array(
//                'status' => "NOTOK",
//                'message' => "Logical error: database_name is not defined in the parameters.yml"
//            );
//            return $res;
//        }
//
//        //ini_set('memory_limit', 0);
////        ini_set('max_execution_time', 0);
////        ini_set('max_input_time', 0);
////        ini_set("default_socket_timeout", 6000); //sec
//
//        $projectRoot = $this->container->get('kernel')->getProjectDir();
//        //echo "projectRoot=".$projectRoot."<br>";
//
//        //For multitenancy is not 'order-lab' anymore, but 'order-lab-tenantapp1'
//        //$projectRoot = str_replace('order-lab', '', $projectRoot);
//        $parentRoot = str_replace('orderflex', '', $projectRoot);
//        $parentRoot = str_replace(DIRECTORY_SEPARATOR . DIRECTORY_SEPARATOR, '', $parentRoot);
//        //echo "parentRoot=".$parentRoot."<br>";
//        //exit('111');
//
//        $managePackagePath = $parentRoot .
//            //DIRECTORY_SEPARATOR . 'order-lab' .
//            //DIRECTORY_SEPARATOR . "utils" .
//            "utils" .
//            DIRECTORY_SEPARATOR . "db-manage" .
//            DIRECTORY_SEPARATOR . "postgres-manage-python";
//        //echo 'scriptPath='.$scriptPath."<br>";
//
//        //config file
//        $configFilePath = $managePackagePath . DIRECTORY_SEPARATOR . "db.config";
//
//        //TODO: check/create d.config file
//        //[setup]
//        //storage_engine=LOCAL
//        //[local_storage]
//        //path=C:\Users\ch3\Documents\MyDocs\WCMC\Backup\db_backup_manag\
//        //[postgresql]
//        //host=127.0.0.1
//        //port=5432
//        //db=ScanOrder
//        //user=username
//        //password=userpassword
//
//        $pythonScriptPath = $managePackagePath . DIRECTORY_SEPARATOR . "manage_postgres_db.py";
//        //exit('pythonScriptPath='.$pythonScriptPath);
//
//        //python in virtualenv'ed scripts: /path/to/venv/bin/python3
//        if( $userServiceUtil->isWindows() ){
//            $pythonEnvPath = $managePackagePath .
//                DIRECTORY_SEPARATOR . "venv" .
//                DIRECTORY_SEPARATOR . "Scripts" . //Windows
//                DIRECTORY_SEPARATOR . "python";
//        } else {
//            $pythonEnvPath = $managePackagePath .
//                DIRECTORY_SEPARATOR . "venv" .
//                DIRECTORY_SEPARATOR . "bin" . //Linux
//                DIRECTORY_SEPARATOR . "python";
//        }
//        //echo "pythonEnvPath=".$pythonEnvPath."<br>";
//        if( file_exists($pythonEnvPath) ) {
//            //echo "The file $filename exists";
//        } else {
//            $msg = "Error in DB management (action $action): The file $pythonEnvPath does not exist.".
//                " Make sure pytnon's environment venv has been installed";
//            $res = array(
//                'status' => "NOTOK",
//                'message' => $msg
//            );
//            return $res;
//        }
//
//        //$command = "$pythonEnvPath $pythonScriptPath --configfile $configFilePath --action list --verbose true --path $networkDrivePath";
//        //$command = "$pythonEnvPath $pythonScriptPath --configfile $configFilePath --action list_dbs --verbose true --path $networkDrivePath";
//
//        $dbUsername = $this->getParameter('database_user');
//        $dbPassword = $this->getParameter('database_password');
//
//        $command = "$pythonEnvPath $pythonScriptPath".
//            " --configfile $configFilePath --verbose true".
//            " --path $networkDrivePath".
//            " --source-db $dbName".
//            " --user $dbUsername".
//            " --password $dbPassword"
//        ;
//
//        if( $action == 'backup' ) {
//            //backup
//
//            //prefix - string to add to the backup filename "backup-prefix-..."
//            $userSecUtil = $this->container->get('user_security_utility');
//            $environment = $userSecUtil->getSiteSettingParameter('environment');
//            if( !$environment ) {
//                $environment = "unknownenv";
//            }
//            //better to use instanceId
//            $instanceId = $userSecUtil->getSiteSettingParameter('instanceId');
//            if( !$instanceId ) {
//                $instanceId = "unknowinstanceId";
//            }
//
//            //TODO: check error
//            // /usr/local/bin/order-lab-tenantapp1/utils/db-manage/postgres-manage-python/venv/bin/python /usr/local/bin/order-lab-tenantapp1/utils/db-manage/postgres-manage-python/manage_postgres_db.py --configfile /usr/local/bin/order-lab-tenantapp1/utils/db-manage/postgres-manage-python/db.config --verbose true --path /usr/local/bin/order-lab-tenantapp1/orderflex/var/backups/ --source-db tenantapp1 --user symfony --password symfony --action backup --prefix live
//
//            $command = $command . " --action backup --prefix ".$environment."-".$instanceId;
//        } elseif( $action == 'restore' ) {
//            //restore
//            if( $backupFileName ) {
//                $command = $command . " --action restore --date $backupFileName";
//            } else {
//                $msg = "Error in DB management (action $action): backup file is not provided";
//                $res = array(
//                    'status' => "NOTOK",
//                    'message' => $msg
//                );
//                return $res;
//            }
//        } else {
//            //Invalid action
//            $msg = "Error in DB management (action $action): invalid action ".$action;
//            $res = array(
//                'status' => "NOTOK",
//                'message' => $msg
//            );
//            return $res;
//        }
//
//        $logger->notice("command=[".$command."]");
//        $res = $this->runProcess($command);
//        //echo "python res=".$res."<br>";
//        //exit('111');
//        $res = array(
//            'status' => "OK",
//            'message' => $res
//        );
//        return $res;
//    }

    //NOT USED. Use asynchronous version via ajax
    //Create a backup of the uploaded folder order-lab\orderflex\public\Uploaded\
    #[Route(path: '/create-backup-upload/', name: 'employees_create_backup_upload', methods: ['GET'])]
    #[Template('AppUserdirectoryBundle/DataBackup/data_backup_management.html.twig')]
    public function createUploadBackupAction(Request $request) {

        if( false === $this->isGranted('ROLE_PLATFORM_ADMIN') ) {
            return $this->redirect( $this->generateUrl('employees-nopermission') );
        }

        $logger = $this->container->get('logger');
        $userSecUtil = $this->container->get('user_security_utility');
        $userServiceUtil = $this->container->get('user_service_utility');

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

            ////////// Create upload backup ///////////////
            if(0) {
                set_time_limit(7200); //3600 seconds => 1 hours, 7200 sec => 2 hours
                //set_time_limit(900); //900 sec => 15 min

                //create backup tar -zcvf archive.tar.gz directory/
                $networkDrivePath = realpath($networkDrivePath); //C:\Users\ch3\Documents\MyDocs\WCMC\Backup\db_backup_manag
                //exit($networkDrivePath);

                $environment = $userSecUtil->getSiteSettingParameter('environment');
                if (!$environment) {
                    $environment = "unknownenv";
                }

                $date = date('Y-m-d-H-i-s');
                $archiveFile = "backupfiles-" . $environment . "_" . $date . ".tar.gz";
                $archiveFile = $networkDrivePath . DIRECTORY_SEPARATOR . $archiveFile;
                //echo "archiveFile=".$archiveFile."<br>";

                $projectRoot = $this->container->get('kernel')->getProjectDir();
                //echo "projectRoot=".$projectRoot."<br>";
                $folder = $projectRoot . DIRECTORY_SEPARATOR . "public";//.DIRECTORY_SEPARATOR."Uploaded";
                //$folder = $projectRoot.DIRECTORY_SEPARATOR."public".DIRECTORY_SEPARATOR."Uploaded".DIRECTORY_SEPARATOR."calllog";
                //echo "folder=".$folder."<br>";
                //exit('111');

                //Error: The command "tar -zcvf /opt/order-lab/orderflex/var/backups/backupfiles-test_2023-09-12-20-28-20.tar.gz
                // /opt/order-lab/orderflex/public/Uploaded" failed. Exit Code: 2(Misuse of shell builtins)

                $targetFolder = "Uploaded";
                //$targetFolder = "UploadedTest"; //testing

                //use tar.gz archive
                $command = "tar -zcf $archiveFile -C $folder $targetFolder"; //create backup
                //echo "command=".$command."<br>";

                $logger->notice("createUploadBackupAction. before command=" . $command);

                $res = $this->runProcess($command);
                //exit("res=".$res);

                $logger->notice("createUploadBackupAction. after res=" . $res);

                if (!$res) {
                    $res = "Uploaded folder backup $archiveFile has been successfully created";
                }
            }
            ////////// EOF Create upload backup ///////////////

            $resUploadFolderBackup = $userServiceUtil->createBackupUpload();
            $resUploadFolderBackupStr = implode(', ', $resUploadFolderBackup);

            if( $resUploadFolderBackup && $resUploadFolderBackup['status'] == 'OK' ) {
                $this->addFlash(
                    'notice',
                    $resUploadFolderBackupStr
                );
            } else {
                $res = "Error: Uploaded folder backup has not been created";
                $this->addFlash(
                    'warning',
                    $resUploadFolderBackupStr
                );
            }

            //Event Log
            $user = $this->getUser();
            $sitename = $this->getParameter('employees.sitename');
            $userSecUtil->createUserEditEvent($sitename,$resUploadFolderBackupStr,$user,null,$request,'Create Backup Upload Files');

            $this->addFlash(
                'notice',
                $resUploadFolderBackupStr
            );

        } else {
            $this->addFlash(
                'pnotify-error',
                "Error creating backup of the uploaded folder"
            );
        }

        return $this->redirect($this->generateUrl('employees_manual_backup_restore'));
    }

    #[Route(path: '/restore-backup-files-ajax/', name: 'employees_restore_backup_files_ajax', methods: ['POST'], options: ['expose' => true])]
    public function restoreBackupFilesAjaxAction( Request $request )
    {

        if (false === $this->isGranted('ROLE_PLATFORM_ADMIN')) {
            return $this->redirect($this->generateUrl('employees-nopermission'));
        }

        $logger = $this->container->get('logger');
        $em = $this->getDoctrine()->getManager();
        $userSecUtil = $this->container->get('user_security_utility');
        $userServiceUtil = $this->container->get('user_service_utility');

        $environment = $userSecUtil->getSiteSettingParameter('environment');
        if( $environment == 'live' ) {
            exit("Live server: Under construction!!!");
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
        $logger->notice("restore BackupFilesAjaxAction backupFilePath=".$backupFileName."; env=".$env);
        //echo "backupFilePath=".$fileId."; env=".$env."<br>";
        //exit('111');

        if( str_contains($backupFileName,'backupfiles') && str_contains($backupFileName,'.tar.gz') ) {
            //ok
        } else {
            $output = array(
                'status' => "NOTOK",
                'message' => "Invalid backup uploads file name:".
                    $backupFileName.
                    " File must contain 'backupfiles' and '.tar.gz' substring.".
                    " Example: backupfiles-***.tar.gz"
            );
            $response = new Response();
            $response->setContent(json_encode($output));
            return $response;
        }

        //get backup files
        //$backupFiles = $this->getBackupFiles($networkDrivePath);

        //networkDrivePath
        $networkDrivePath = $userSecUtil->getSiteSettingParameter('networkDrivePath');
        //echo "networkDrivePath=".$networkDrivePath."<br>";
        if( !$networkDrivePath ) {
            //exit("No networkDrivePath is defined");
            $output = array(
                'status' => 'NOTOK',
                'message' => 'Network Drive Path is not defined in the Site Settings'
            );
            $response = new Response();
            $response->setContent(json_encode($output));
            return $response;
        }

        $sitename = "employees";

        if( $backupFileName ) {

            set_time_limit(7200); //3600 seconds => 1 hours, 7200 sec => 2 hours

            $networkDrivePath = realpath($networkDrivePath); //C:\Users\ch3\Documents\MyDocs\WCMC\Backup\db_backup_manag

            $archiveFile = $networkDrivePath.DIRECTORY_SEPARATOR.$backupFileName;
            //echo "archiveFile=".$archiveFile."<br>";

            $projectRoot = $this->container->get('kernel')->getProjectDir();
            //echo "projectRoot=".$projectRoot."<br>";
            $folder = $projectRoot . DIRECTORY_SEPARATOR . "public";
            //echo "folder=".$folder."<br>";

            $targetFolder = "Uploaded";
            //$targetFolder = "UploadedTest"; //testing

            $date = date('Y-m-d-H-i-s');

            //Rename current Upload folder (Windows 'move')
            $moveCommand = "mv";
            if( $userServiceUtil->isWindows() ){
                $moveCommand = "move";
            }

            //Move target folder to folder_date
            $command = $moveCommand . " " . $folder . DIRECTORY_SEPARATOR . $targetFolder .
                " " . $folder . DIRECTORY_SEPARATOR . $targetFolder."_".$date; //restore
            //echo "mv command=".$command."<br>";
            $logger->notice("restore BackupFilesAjaxAction mv command=".$command);
            $res = $this->runProcess($command);

            //Create new folder instead of moved
            $command = "mkdir $folder".DIRECTORY_SEPARATOR.$targetFolder;
            //echo "mkdir command=".$command."<br>";
            $logger->notice("restore BackupFilesAjaxAction mkdir command=".$command);
            $res = $this->runProcess($command);

            //use tar.gz un-archive
            $command = "tar -xf $archiveFile -C $folder";
            //echo "tar command=".$command."<br>";
            $logger->notice("restore BackupFilesAjaxAction tar command=".$command);

            if(0) {
                $res = $this->runProcess($command);
                //exit("res=".$res);
                $logger->notice("restore BackupFilesAjaxAction: after tar");

                $msg = "Uploaded folder backup $archiveFile has been successfully created.".
                    " As a precaution, the original $targetFolder folder has been moved to " .
                    $targetFolder."_".$date . " and can be deleted later";

                if( !$res ) {
                    //$logger->notice("restore res is empty");
                } else {
                    //$logger->notice("restore res is not empty. res=".$res);
                    $msg = $msg . "; res=".$res;
                }

                //Event Log
                $user = $this->getUser();
                $sitename = $this->getParameter('employees.sitename');
                $userSecUtil->createUserEditEvent($sitename,$msg,$user,null,$request,'Restore Backup Upload Files');
            } else {
                $extractionTime = null;
                $filesize = filesize($archiveFile);
                if( $filesize ) {
                    $extractionTime = round( ($filesize/1024*1024) / 10 ); //kB,MB / 10 ~ min
                }

                $commandArr = explode(" ",$command);
                $process = $this->runAsyncProcess($commandArr);
                $logger->notice("restore BackupFilesAjaxAction: after tar async");

                $msg = "Uploaded folder backup $archiveFile has been started asynchronously.".
                    " It might take up to $extractionTime minutes.".
                    " As a precaution, the original $targetFolder folder has been moved to " .
                    $targetFolder."_".$date . " and can be deleted later";
                //Event Log
                $user = $this->getUser();
                $sitename = $this->getParameter('employees.sitename');
                $userSecUtil->createUserEditEvent($sitename,$msg,$user,null,$request,'Restore Backup Upload Files');

                $process->wait();
                // ... do things after the process has finished
                $logger->notice("restore BackupFilesAjaxAction: after wait");
                $msg = "Restore of uploaded folder backup $archiveFile has been successfully completed.".
                    " As a precaution, the original $targetFolder folder has been moved to " .
                    $targetFolder."_".$date . " and can be deleted later";
                //Event Log
                $user = $this->getUser();
                $sitename = $this->getParameter('employees.sitename');
                $userSecUtil->createUserEditEvent($sitename,$msg,$user,null,$request,'Restore Backup Upload Files');
            }

            $output = array(
                'status' => 'OK',
                'message' => $msg
            );

        } else {
            $output = array(
                'status' => 'NOTOK',
                'message' => 'Backup upload file is not provided'
            );
        }

        $logger->notice("restore BackupFilesAjaxAction: sending response");
        $response = new Response();
        $response->setContent(json_encode($output));
        return $response;
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
        //dump($files);
        //exit('111');
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
//        foreach (scandir($dir, $sorting_order) as $file) {
//            if ($file[0] === '.') {
//                continue;
//            }
//            if( is_dir($file) ) {
//                $this->better_scandir($file,$sorting_order,$ret);
//            }
//            $files[$file] = filemtime($dir . '/' . $file);
//        } // foreach
        $files = $this->getFiles( $dir, $sorting_order );
        //$files = $this->getAllFilesRecursive($dir,$sorting_order,$files);
        //dump($files);
        //exit('111');

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
    public function scanAllDir( $dir, $sorting_order ) {
        $result = [];
        foreach(scandir($dir,$sorting_order) as $filename) {
            if ($filename[0] === '.') continue;
            $filePath = $dir . '/' . $filename;
            if (is_dir($filePath)) {
                foreach ($this->scanAllDir($filePath,$sorting_order) as $childFilename) {
                    $result[] = $filename . '/' . $childFilename;
                }
            } else {
                $result[] = $filename;
            }
        }
        return $result;
    }
    function getAllFilesRecursive($dir, $sorting_order, &$results = array()) {
        $files = scandir($dir,$sorting_order);

        foreach ($files as $key => $value) {
            $path = realpath($dir . DIRECTORY_SEPARATOR . $value);
            if (!is_dir($path)) {
                $results[] = $path;
            } else if ($value != "." && $value != "..") {
                $this->getAllFilesRecursive($path, $sorting_order, $results);
                //$results[] = $path;
            }
        }

        return $results;
    }
    public function getFiles( $dir, $sorting_order ) {
        /****************************************************************************/
        // Roll through the scandir values.
        $files = array();
        foreach (scandir($dir, $sorting_order) as $file) {
            if ($file[0] === '.') {
                continue;
            }
            $files[$file] = filemtime($dir . '/' . $file);
        } // foreach

        return $files;
    }


    public function runProcess($script) {
        //$process = new Process($script);
        $process = Process::fromShellCommandline($script);
        //$process->setTimeout(1800); //sec; 1800 sec => 30 min
        $process->setTimeout(7200); //7200 sec => 2 hours
        $process->run();
        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }
        return $process->getOutput();
    }
    public function runAsyncProcess($script) {
        $process = new Process($script);
        //$process->setTimeout(1800); //sec; 1800 sec => 30 min
        $process->setTimeout(7200); //7200 sec => 2 hours
        $process->start();
        return $process;

        //while ($process->isRunning()) {
            // waiting for process to finish
        //}
        //return $process->getOutput();
    }


    #[Route(path: '/download-backup-file/{filename}', name: 'employees_download_backup_file', methods: ['GET'], options: ['expose' => true])]
    public function downloadBackupFileAction( Request $request, $filename=null )
    {
        //exit('downloadBackupFileAction');
        if( false == $this->isGranted('ROLE_PLATFORM_DEPUTY_ADMIN') ) {
            return $this->redirect($this->generateUrl('employees-nopermission'));
        }

        $userSecUtil = $this->container->get('user_security_utility');
        $em = $this->getDoctrine()->getManager();

        //$backupFileName = $request->get('fileId');
        //$filename = $request->query->get('filename');

        $networkDrivePath = $userSecUtil->getSiteSettingParameter('networkDrivePath');
        $networkDrivePath = realpath($networkDrivePath);

        $filePath = $networkDrivePath . DIRECTORY_SEPARATOR . $filename;
        $filePath = realpath($filePath);
        //exit('$filePath='.$filePath);

        return $this->file($filePath, $filename);
        $response = new BinaryFileResponse($filePath);
        $response->send();
    }

    #[Route(path: '/delete-file/{filename}', name: 'employees_delete_file', methods: ['GET'], options: ['expose' => true])]
    public function deleteFileAction( Request $request, $filename=null )
    {
        //exit('downloadBackupFileAction');
        if( false == $this->isGranted('ROLE_PLATFORM_DEPUTY_ADMIN') ) {
            return $this->redirect($this->generateUrl('employees-nopermission'));
        }

        $userSecUtil = $this->container->get('user_security_utility');
        $em = $this->getDoctrine()->getManager();

        $networkDrivePath = $userSecUtil->getSiteSettingParameter('networkDrivePath');
        $networkDrivePath = realpath($networkDrivePath);

        $filePath = $networkDrivePath . DIRECTORY_SEPARATOR . $filename;
        $filePath = realpath($filePath);
        //exit('$filePath='.$filePath);

        //rmdir(dirname($filePath));
        unlink($filePath);

        $this->addFlash(
            'notice',
            "File $filename has been deleted"
        );

        return $this->redirect($this->generateUrl('employees_manual_backup_restore'));
    }
    
    //https://symfony.com/doc/current/controller/upload_file.html
    #[Route(path: '/upload-backup-file/', name: 'employees_upload_backup_file', methods: ['POST'])]
    public function uploadBackupFileAction(Request $request, SluggerInterface $slugger) {
        if( false === $this->isGranted('ROLE_PLATFORM_ADMIN') ) {
            return $this->redirect( $this->generateUrl('employees-nopermission') );
        }

        $logger = $this->container->get('logger');

        //https://code-boxx.com/upload-large-files-php/
//        upload_max_filesize = 150M    //PHP_INI_PERDIR
//        post_max_size = 150M          //PHP_INI_PERDIR
//        max_input_time = 300          //PHP_INI_PERDIR
//        max_execution_time = 300      //PHP_INI_ALL
        //Make sure upload_max_filesize is large enough
        //upload_max_filesize is only changeable in PHP_INI_PERDIR (php.ini or .htaccess file; ini_set("upload_max_filesize", "150M")
        //max_input_time in PHP_INI_PERDIR (sec)
        //ini_set('upload_max_filesize', '20G');
        //ini_set('post_max_size', '20G');
        //ini_set('max_input_time', '-1');
        ini_set('max_execution_time', '3000');

        $userSecUtil = $this->container->get('user_security_utility');

        //$logger->notice("uploadBackupFileAction: before createForm");
        $form = $this->createForm(UploadSingleFileType::class);
        //$logger->notice("uploadBackupFileAction: after createForm");

        $form->handleRequest($request);
        //$logger->notice("uploadBackupFileAction: after handleRequest");

        if( $form->isSubmitted() ) {
            //$logger->notice("uploadBackupFileAction: isSubmitted");
        } else {
            $logger->notice("uploadBackupFileAction: NO isSubmitted");
            $this->addFlash(
                'warning',
                "Upload file logical error: form is not submitted"
            );
        }
        if( $form->isValid() ) {
            //$logger->notice("uploadBackupFileAction: isValid");
        } else {
            $logger->notice("uploadBackupFileAction: NO isValid");
            $this->addFlash(
                'warning',
                "Upload file logical error: form is not valid. 
                Please make sure upload_max_filesize and post_max_size in php.ini 
                are set to a value bigger than upload file size".
                "<br> Currently upload_max_filesize=".ini_get('upload_max_filesize').
                ", and post_max_size=".ini_get('post_max_size')
            );
        }

        if( $form->isSubmitted() && $form->isValid() ) {
            /** @var UploadedFile $uploadFile */
            $uploadFile = $form->get('uploadfile')->getData();
            //$logger->notice("uploadBackupFileAction: isSubmitted uploadFile=$uploadFile");

            if( $uploadFile ) {
                //$logger->notice("uploadFile=$uploadFile");
                $originalFilename = pathinfo($uploadFile->getClientOriginalName(), PATHINFO_FILENAME);
                // this is needed to safely include the file name as part of the URL
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$uploadFile->guessExtension();
                echo "safeFilename=$safeFilename <br>";
                echo "newFilename=$newFilename <br>";

                $networkDrivePath = $userSecUtil->getSiteSettingParameter('networkDrivePath');
                echo "networkDrivePath=$networkDrivePath <br>";

                if( file_exists($networkDrivePath) == false ) {
                    $logger->notice("Warning: Undefined networkDrivePath. Attempting to create $networkDrivePath");
//                    $this->addFlash(
//                        'warning',
//                        "Warning: Undefined networkDrivePath. Attempting to create $networkDrivePath"
//                    );
                    echo "Warning: Undefined networkDrivePath. Attempting to create $networkDrivePath <br>";

//                    //create $networkDrivePath /usr/local/bin/order-lab/orderflex/var/backups/
//                    //Create new folder instead of moved
//                    $command = "mkdir $networkDrivePath";
//                    $logger->notice("mkdir command=".$command);
//                    //echo "mkdir command=".$command."<br>";
//                    $res = $this->runProcess($command);
                    $this->createBackupPath($networkDrivePath);
                }

                if( file_exists($networkDrivePath) == false ) {
                    $logger->notice("Error: Undefined networkDrivePath");
                    $this->addFlash(
                        'warning',
                        "Error: Undefined networkDrivePath".$networkDrivePath
                    );
                    return $this->redirect($this->generateUrl('employees_manual_backup_restore'));
                }

                $networkDrivePath = realpath($networkDrivePath);

                // Move the file to the directory where brochures are stored
                try {
                    //$logger->notice("before move. networkDrivePath=$networkDrivePath, newFilename=$newFilename");
                    $uploadFile->move(
                        //$this->getParameter('backup_upload_directory'),
                        $networkDrivePath,
                        $newFilename
                    );
                    //$logger->notice("after move");

                    $this->addFlash(
                        'notice',
                        'Backup file has successfully uploaded as '.$newFilename
                    );
                    return $this->redirect($this->generateUrl('employees_manual_backup_restore'));
                } catch( FileException $e ) {
                    $this->addFlash(
                        'warning',
                        "An error occurred while uploading backup file. ".$e->getMessage()
                    );
                    // ... handle exception if something happens during file upload
                    echo "An error occurred while uploading backup file. ".$e->getMessage();
                    exit('error backup uploaded');
                }
            } else {
                $logger->notice("Upload file is not provided");
                //exit('upload file is not provided');
                $this->addFlash(
                    'warning',
                    "Upload file is not provided"
                );
                //exit('upload file is not provided');
                return $this->redirect($this->generateUrl('employees_manual_backup_restore'));
            }

            //dump($uploadFile);
            //exit('uploadBackupFileAction uploadFile');
            $logger->notice("End of 'if' uploadBackupFileAction uploadFile");
        }

        //exit('uploadBackupFileAction end');
        $logger->notice("uploadBackupFileAction end");
        return $this->redirect($this->generateUrl('employees_manual_backup_restore'));
    }

    //create $networkDrivePath /usr/local/bin/order-lab/orderflex/var/backups/
    public function createBackupPath( $path ) {
        if( file_exists($path) == false ) {
            $logger = $this->container->get('logger');
            $logger->notice("Warning: Undefined networkDrivePath. Attempting to create $path");
            //echo "Warning: Undefined networkDrivePath. Attempting to create $path <br>";
            //create $path /usr/local/bin/order-lab/orderflex/var/backups/
            //Create new folder instead of moved
            $command = "mkdir $path";
            //$logger->notice("mkdir command=".$command);
            //echo "mkdir command=".$command."<br>";
            $this->runProcess($command);
        }
    }


    //NOT USED
    //methods: ['GET','POST','DELETE','PATCH'],
    //https://github.com/ankitpokhrel/tus-php
    #[Route(path: '/upload-uppy-file/{key}', name: 'employees_upload_uppy_file', options: ['expose' => true])]
    public function uploadUppyAction(Request $request, $key=null)
    {
        if (false === $this->isGranted('ROLE_PLATFORM_ADMIN')) {
            return $this->redirect($this->generateUrl('employees-nopermission'));
        }

        //exit('key='.$key);

        if(0) {
            $client = new \TusPhp\Tus\Client('http://tus-php-server');

            // Alert: Sanitize all inputs properly in production code
            if (!empty($_FILES)) {
                $fileMeta = $_FILES['tus_file'];
                $uploadKey = hash_file('md5', $fileMeta['tmp_name']);

                try {
                    $client->setKey($uploadKey)->file($fileMeta['tmp_name'], 'chunk_a');

                    // Upload 50MB starting from 10MB
                    $bytesUploaded = $client->seek(10000000)->upload(50000000);
                    $partialKey1 = $client->getKey();
                    $checksum = $client->getChecksum();

                    // Upload first 10MB
                    $bytesUploaded = $client->setFileName('chunk_b')->seek(0)->upload(10000000);
                    $partialKey2 = $client->getKey();

                    // Upload remaining bytes starting from 60,000,000 bytes (60MB => 50000000 + 10000000)
                    $bytesUploaded = $client->setFileName('chunk_c')->seek(60000000)->upload();
                    $partialKey3 = $client->getKey();

                    $client->setFileName($fileMeta['name'])->concat($uploadKey, $partialKey2, $partialKey1, $partialKey3);

                    header('Location: ' . $_SERVER['HTTP_REFERER'] . '?state=uploaded');
                } //catch (ConnectionException | TusPhp\Exception\FileException | TusException $e) {
                catch ( ConnectionException $e ) {
                    header('Location: ' . $_SERVER['HTTP_REFERER'] . '?state=failed');
                }
            }

            exit(0);
        }


        //https://processwire.com/talk/topic/22212-uppy-tusphp-and-processwire-for-large-file-uploads/
        // Create TusPhp server
        $server = new \TusPhp\Tus\Server();
        // Set path to endpoint - no trailing slash here
        $apiPath = $this->generateUrl('employees_upload_uppy_file');
        $server->setApiPath($apiPath);
        // Set upload directory
        $userSecUtil = $this->container->get('user_security_utility');
        $networkDrivePath = $userSecUtil->getSiteSettingParameter('networkDrivePath');
        $server->setUploadDir($networkDrivePath);

        // Listener function for when an upload is completed
        $server->event()->addListener('tus-server.upload.complete', function(\TusPhp\Events\TusEvent $event) {

            // Get path of uploaded file
            $file_path = $event->getFile()->getFilePath();

            // Add uploaded file to "files" field on Home page
            $p = wire('pages')->get(1);
            $p->of(false);
            $p->files->add($file_path);
            $p->save('files');

        });

        // Send response
        $response = $server->serve();
        $response->send();

        // Exit from current PHP process
        // Could probably use PW halt here as an alternative
        // return $this->halt();
        exit(0);

        $server   = new \TusPhp\Tus\Server('file');
        $response = $server->serve();
        dump($response);
        exit('111');
        $response->send();
        exit(0); // Exit from current PHP process.

        $logger = $this->container->get('logger');

        $baseUrl = 'http://tus-php-server';

        $client = new \TusPhp\Tus\Client($baseUrl);

        dump($_SERVER);
        dump($_FILES);
        exit('111');

//        // Alert: Sanitize all inputs properly in production code
//        if( ! empty($_FILES) ) {
//            echo "files";
//            $fileMeta  = $_FILES['tus_file'];
//            $uploadKey = hash_file('md5', $fileMeta['tmp_name']);
//
//            try {
//                $client->setKey($uploadKey)->file($fileMeta['tmp_name'], 'chunk_a');
//
//                // Upload 50MB starting from 10MB
//                $bytesUploaded = $client->seek(10000000)->upload(50000000);
//                $partialKey1   = $client->getKey();
//                $checksum      = $client->getChecksum();
//
//                // Upload first 10MB
//                $bytesUploaded = $client->setFileName('chunk_b')->seek(0)->upload(10000000);
//                $partialKey2   = $client->getKey();
//
//                // Upload remaining bytes starting from 60,000,000 bytes (60MB => 50000000 + 10000000)
//                $bytesUploaded = $client->setFileName('chunk_c')->seek(60000000)->upload();
//                $partialKey3   = $client->getKey();
//
//                $client->setFileName($fileMeta['name'])->concat($uploadKey, $partialKey2, $partialKey1, $partialKey3);
//
//                header('Location: ' . $_SERVER['HTTP_REFERER'] . '?state=uploaded');
//            } catch (ConnectionException | FileException | TusException $e) {
//                header('Location: ' . $_SERVER['HTTP_REFERER'] . '?state=failed');
//            }
//        } else {
//            echo "no files";
//        }

        $result = array("res");
        $response = new Response();
        $response->setContent(json_encode($result));
        return $response;
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
        //echo "serverName=".$serverName."<br>";
        //echo "driver=".$driver."<br>";
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
            //echo "Connection established.<br />";
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
            return "File is not compatable with current database " . $dbInfo;
        }

        $memory_limit = ini_get('memory_limit');
        echo "Current memory limit is: " . $memory_limit . "<br>";
        echo "Peak memory usage: " . memory_get_peak_usage() . "<br>";
        ini_set('memory_limit', 0);
        ini_set('max_execution_time', 0);
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