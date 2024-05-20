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
 * Created by Oleg Ivanov
 * User: oli2002
 * Date: 10/4/13
 * Time: 12:56 PM
 */

namespace App\UserdirectoryBundle\Util;


use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Bundle\SecurityBundle\Security;


class InterfaceTransferUtil {

    protected $em;
    protected $container;
    protected $security;

    public function __construct( EntityManagerInterface $em, ContainerInterface $container, Security $security ) {
        $this->em = $em;
        $this->container = $container;
        $this->security = $security;
    }

    //Require ssh
    //http://pecl.php.net/package/ssh2

    public function transferFile() {
        
        
        //Send file via sftp to server

        $strServer = "143.198.22.81";
        $strServerPort = "22";
        $strServerUsername = "root";
        $strServerPassword = "mypass";
        $csv_filename = "test_file.csv";

        //connect to server
        $resConnection = ssh2_connect($strServer, $strServerPort);

        if(ssh2_auth_password($resConnection, $strServerUsername, $strServerPassword)){
            //Initialize SFTP subsystem

            echo "connected";
            $resSFTP = ssh2_sftp($resConnection);

            $resFile = fopen("ssh2.sftp://{$resSFTP}/".$csv_filename, 'w');
            fwrite($resFile, "Testing");
            fclose($resFile);

        }else{
            echo "Unable to authenticate on server";
        }
    }


}