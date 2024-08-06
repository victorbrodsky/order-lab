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
        //downloadFile
        //employees_transfer_interface_get_app_path
        //get-app-path
        //getAppPathCurl


        return "downloadBackupFilesFromPublic";
    }
}