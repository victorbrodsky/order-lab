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

        //send request to public (remote) server to send back the backup files as a response

        //1) Get remote project path
        $jsonFile = array(
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
            exit("Not fount InterfaceTransferList by name Project");
        }

        $serverBaseName = $interfaceTransfer->getTransferSourceBase();  //http://view.online/directory/transfer-interface/get-app-path
        $serverName = $interfaceTransfer->getTransferSource();  //http://view.online/wcm/pathology/directory/transfer-interface/get-app-path 
        echo "downloadBackupFilesFromPublic: serverName=$serverName <br>";

        $remoteAppPath = $interfaceTransferUtil->getAppPathCurl($serverName,$jsonFile);
        echo "downloadBackupFilesFromPublic: remoteAppPath=$remoteAppPath <br>";

        //2) downloadFile
        //$file = $interfaceTransferUtil->downloadFile( $jsonObject, $transferableEntity, $field, $adder );
        $privateKeyContent = $interfaceTransfer->getSshPassword();
        //echo "downloadBackupFilesFromPublic: privateKeyContent=$privateKeyContent <br>";
        if( !$privateKeyContent ) {
            exit("No private key");
            return false;
        }

        $uniquename = null; //get the latest 'backupdb' and 'backupfiles' files
        //$uniquename = 'backupdb-live-WCMEXT-20240806-160005-tenantapp1.dump.gz';
        //$uniquename = 'backupfiles-live_2024-08-06-16-00-08.tar.gz';
        $sourcePath = $remoteAppPath.'/'.'var'.'/'.'backups';
        $sourceFile = $sourcePath.'/'.$uniquename;

        $files = $interfaceTransferUtil->listRemoteFiles($serverBaseName, $privateKeyContent, $sourcePath);
        return $files;

        //$destinationFile - puts them into a dedicated network shared folder (subfolder of where the view.med.cornell.edu backups are uploaded.)
        $projectRoot = $this->container->get('kernel')->getProjectDir();
        $destinationFileName = $serverName.'-'.$uniquename;
        $destinationFile = $projectRoot.'/var/backups/'.$destinationFileName;

        $outputRes = $interfaceTransferUtil->getRemoteFile($serverName, $privateKeyContent, $sourceFile, $destinationFile);
        if( $outputRes ) {
            return false;
        }

        //downloadFile
        //employees_transfer_interface_get_app_path
        //get-app-path
        //getAppPathCurl


        return "downloadBackupFilesFromPublic";
    }
}