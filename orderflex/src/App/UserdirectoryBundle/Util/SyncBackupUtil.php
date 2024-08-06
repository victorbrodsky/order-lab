<?php
/**
 * Created by PhpStorm.
 * User: cinav
 * Date: 8/5/2024
 * Time: 4:54 PM
 */

namespace App\UserdirectoryBundle\Util;

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
            'datetime' => time(),
            'random' => rand()
        );
        $userSecUtil = $this->container->get('user_security_utility');
        $secretKey = $userSecUtil->getSiteSettingParameter('secretKey');
        $hash = hash('sha512', $secretKey . serialize($jsonFile));
        $jsonFile['hash'] = $hash;
        //Use InterfaceTransferList 'Project' to get server ip or url
        $interfaceTransfer = $this->em->getRepository(InterfaceTransferList::class)->findOneByName('Project');
        $serverName = $interfaceTransfer->getTransferDestination();  //"159.203.95.150";
        $remoteAppPath = $interfaceTransferUtil->getAppPathCurl($serverName,$jsonFile);

        //2) downloadFile
        //$file = $interfaceTransferUtil->downloadFile( $jsonObject, $transferableEntity, $field, $adder );
        try {
            $sftpConnection = $this->connectByPublicKey($transferableEntity,'SFTP');
        } catch( \Exception $e ) {
            //echo 'Caught connection exception: ', $e->getMessage(), "\n";
            return false;
        }
        $sftpConnection->enableDatePreservation(); //preserver original file last modified date

        //downloadFile
        //employees_transfer_interface_get_app_path
        //get-app-path
        //getAppPathCurl


        return "downloadBackupFilesFromPublic";
    }
}