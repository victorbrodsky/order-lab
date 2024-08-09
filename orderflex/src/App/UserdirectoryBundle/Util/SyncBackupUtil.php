<?php
/**
 * Created by PhpStorm.
 * User: cinav
 * Date: 8/5/2024
 * Time: 4:54 PM
 */

namespace App\UserdirectoryBundle\Util;

use App\UserdirectoryBundle\Entity\InterfaceTransferList;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Bundle\SecurityBundle\Security;


class SyncBackupUtil
{
    protected $em;
    protected $container;
    protected $security;
    protected $verifyPeer;

    public function __construct( EntityManagerInterface $em, ContainerInterface $container, Security $security ) {
        $this->em = $em;
        $this->container = $container;
        $this->security = $security;
    }

    public function downloadBackupFilesFromPublic() {
        $interfaceTransferUtil = $this->container->get('interface_transfer_utility');
        $userServiceUtil = $this->container->get('user_service_utility');

        //send request to public (remote) server to send back the backup files as a response

        //1) Get remote project path
        $jsonFile = array(
            'id' => 'None',
            'className' => 'Project',
            'datetime' => time(),
            'random' => rand()
        );
//        $userSecUtil = $this->container->get('user_security_utility');
//        $secretKey = $userSecUtil->getSiteSettingParameter('secretKey');
//        $hash = hash('sha512', $secretKey . serialize($jsonFile));
//        $jsonFile['hash'] = $hash;
        //Use InterfaceTransferList 'Project' to get server ip or url
        //$interfaceTransfer = $this->em->getRepository(InterfaceTransferList::class)->findOneByName('Project');
        $interfaceTransfer = $interfaceTransferUtil->getInterfaceTransferByName($entityName='Project');
        if( !$interfaceTransfer ) {
            //exit("Not fount InterfaceTransferList by name Project");
            return false;
        }

        $serverBaseName = $interfaceTransfer->getTransferSourceBase();  //http://view.online/directory/transfer-interface/get-app-path
        $serverName = $interfaceTransfer->getTransferSource();  //http://view.online/wcm/pathology/directory/transfer-interface/get-app-path 
        echo "downloadBackupFilesFromPublic: serverName=$serverName <br>";

        //$remoteAppPath = $interfaceTransferUtil->getAppPathCurl($serverName,$jsonFile);
        //echo "downloadBackupFilesFromPublic: remoteAppPath=$remoteAppPath <br>";

        //2) Get latest filenames
        //$file = $interfaceTransferUtil->downloadFile( $jsonObject, $transferableEntity, $field, $adder );
        $privateKeyContent = $interfaceTransfer->getSshPassword();
        //echo "downloadBackupFilesFromPublic: privateKeyContent=$privateKeyContent <br>";
        if( !$privateKeyContent ) {
            //exit("No private key specified in $entityName");
            return false;
        }

        $sshConnection = $interfaceTransferUtil->getRemoteConnection($serverBaseName,$privateKeyContent);

        //$uniquename = null; //get the latest 'backupdb' and 'backupfiles' files
        //$uniquename = 'backupdb-live-WCMEXT-20240806-160005-tenantapp1.dump.gz';
        //$uniquename = 'backupfiles-live_2024-08-06-16-00-08.tar.gz';
        //TODO: use networkDrivePath
        //$userSecUtil = $this->container->get('user_security_utility');
        //$networkDrivePath = $userSecUtil->getSiteSettingParameter('networkDrivePath');
        //$sourcePath = $remoteAppPath.'/'.'var'.'/'.'backups';

        $sourcePath = $interfaceTransferUtil->getRemoteBackupPathCurl($serverName,$jsonFile);
        $sourcePath = rtrim($sourcePath,'/');
        echo "downloadBackupFilesFromPublic: sourcePath=$sourcePath <br>";

        $files = $interfaceTransferUtil->listRemoteFiles($sshConnection, $sourcePath);

        //https://stackoverflow.com/questions/54999763/getting-latest-file-from-sftp-in-php-using-curl
        // filter out folders     
        $files_only_callback = function($a) {
            return ($a["type"] == NET_SFTP_TYPE_REGULAR);
        };
        $files = array_filter($files, $files_only_callback);

        $files_db_callback = function($a) {
            return (str_contains($a['filename'],'backupdb'));
        };
        $dbFiles = array_filter($files, $files_db_callback);

        $files_upload_callback = function($a) {
            return (str_contains($a['filename'],'backupfiles'));
        };
        $uploadFiles = array_filter($files, $files_upload_callback);

        // sort by timestamp
        // In PHP 7, you can use spaceship operator instead:
        usort($dbFiles, function($a, $b) { return $b["mtime"] <=> $a["mtime"]; });
        usort($uploadFiles, function($a, $b) { return $b["mtime"] <=> $a["mtime"]; });

        $latestDbFile = $dbFiles[0]["filename"];
        echo "latestDbFile=".$latestDbFile."<br>";

        $latestUploadFile = $uploadFiles[0]["filename"];
        echo "latestUploadFile=".$latestUploadFile."<br>";

//        foreach ($files as $file) {
//            dump($file);
//            //echo "file=".$file['filename']."<br>";
//        }
        //exit('111');
        //return $files;

        //3) downloadFile
        $files = array();
        //$destinationFile - puts them into a dedicated network shared folder (subfolder of where the view.med.cornell.edu backups are uploaded.)
        //get /mnt/ folder on live: /mnt/pathology/view-backup/upload-backup

        //$projectRoot = $this->container->get('kernel')->getProjectDir();
        //$projectpath = $projectRoot.'/var/backups/';
        $userSecUtil = $this->container->get('user_security_utility');
        $networkDrivePath = $userSecUtil->getSiteSettingParameter('networkDrivePath');

        //remove right '/'
        $networkDrivePath = rtrim($networkDrivePath,'/');

        if (!file_exists($networkDrivePath)) {
            echo "downloadBackupFilesFromPublic: networkDrivePath=[$networkDrivePath] does not exist.<br>";
            mkdir($networkDrivePath, 0777, true);
        }
        echo "downloadBackupFilesFromPublic: networkDrivePath=[$networkDrivePath]<br>";

        //a) backupdb
        $sourceDbFile = $sourcePath.'/'.$latestDbFile;
        echo "sourceDbFile=".$sourceDbFile."<br>";
        //$destinationDbFileName = $serverName.'-'.$latestDbFile;
        $destinationDbFileName = $latestDbFile;
        echo "destinationDbFileName=".$destinationDbFileName."<br>";
        $destinationDbFile = $networkDrivePath.'/'.$destinationDbFileName;
        echo "destinationDbFile=".$destinationDbFile."<br>";
        //TODO: check if the file does not exists
        if( file_exists($destinationDbFile) ) {
            exit("File $destinationDbFile already exists");
            return false;
        }
        //TODO: keep only limited number of files $keepNumber (just run: UserServiceUtil->removeOldBackupFiles)
        $userServiceUtil->removeOldBackupFiles();

        $outputDbRes = $interfaceTransferUtil->getRemoteFile($sshConnection, $sourceDbFile, $destinationDbFile);
        if( $outputDbRes ) {
            //return false;
            echo "destinationDbFile=".$destinationDbFile."<br>";
            $files[] = $destinationDbFile;
        } else {
            echo "getRemoteFile failed: sourceDbFile=".$sourceDbFile."<br>";
        }

        //b) backupfiles
        $sourceUploadFile = $sourcePath.'/'.$latestUploadFile;
        echo "sourceUploadFile=".$sourceUploadFile."<br>";
        //$destinationUploadFileName = $serverName.'-'.$latestUploadFile;
        $destinationUploadFileName = $latestUploadFile;
        $destinationUploadFile = $networkDrivePath.$destinationUploadFileName;
        echo "destinationUploadFile=".$destinationUploadFile."<br>";
        $outputUploadRes = $interfaceTransferUtil->getRemoteFile($sshConnection, $sourceUploadFile, $destinationUploadFile);
        if( $outputUploadRes ) {
            //return false;
            echo "destinationUploadFile=".$destinationUploadFile."<br>";
            $files[] = $destinationDbFile;
        } else {
            echo "getRemoteFile failed: sourceUploadFile=".$sourceUploadFile."<br>";
        }

        //downloadFile
        //employees_transfer_interface_get_app_path
        //get-app-path
        //getAppPathCurl

        return $files; //"downloadBackupFilesFromPublic";
    }

}