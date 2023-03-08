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
 * User: oli2002
 * Date: 3/08/2023
 * Time: 11:51 AM
 */

namespace App\FellAppBundle\Util;

//use https://github.com/asimlqt/php-google-spreadsheet-client/blob/master/README.md
//install:
//1) composer.phar install
//2) composer.phar update

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Google\Spreadsheet\DefaultServiceRequest;
use Google\Spreadsheet\ServiceRequestFactory;
use Google\Spreadsheet\Spreadsheet;
use Google\Spreadsheet\SpreadsheetService;
use App\UserdirectoryBundle\Entity\Document;
use App\UserdirectoryBundle\Form\DataTransformer\GenericTreeTransformer;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\Filesystem\Exception\IOException;
use App\FellAppBundle\Util\CustomDefaultServiceRequest;
use Symfony\Bundle\SecurityBundle\Security;

class GoogleSheetManagementV2 {

    protected $em;
    protected $container;
    protected $security;

    public function __construct( EntityManagerInterface $em, ContainerInterface $container, Security $security ) {
        $this->em = $em;
        $this->container = $container;
        $this->security = $security;
    }

    function testFileDownload() {
        //Test files are located in FellowshipApplication/TestFiles
        $files = array(
            "17PwcM0qPAAz8KcitIBayMzTj6XW8GSsu", //"1ohvKGunEsvSowwpozfjvjtyesN0iUeF2"; //Word

            //"1Bkz0jkDWn8ymagMf6EPZQZ2Nyf18kaPXI2aqKm_eX-U", //"1is-0L26e_W76hL-UfAuuZEEo8p9ycnwnn02hZ9lzFek"; //PDF
            "1maBuBYjB_xEiQi8lqtNDzUhQwEDrFi_o", //PDF

            "1fd-vjpmQKdVXDiAhEzcP-5fFDZEl2kKW67nrRrtfcWg", //"17inHCzyZNyZ98E_ZngUjkUKWNp3D2J8Ri2TZWR5Oi1k"; //Google Docs
            "1NwCFOUZ6oTyiehtSzPuxuddsnxbqgPeUCn516eEW05o", //"1beJAujYBEwPdi3RI7YAb4a8NcrBj5l0vhY6Zsa01Ohg"; //Google Sheets

            //"1imVshtA63nsr5oQOyW3cWXzXV_zhjHtyCwTKgjR8MAM", //Image 1b_tL1MDsS6fCysBcP6X7MjhdS9jryiYf
            "1pg88L0cf8Lgv1bsLaAdJGqAZewYgHzVJ" //Image
        );

        $service = $this->getGoogleService();

        $res = array();
        foreach($files as $fileId) {
            $file = $this->getFileById($fileId,$service);
            //dump($file);
            //exit('111');
            if( $file ) {
                //dump($file);
                //exit('222');
                $resFile = $this->downloadFile($service, $file, null, false);
                //dump($resFile);
                //exit('111');
                if( $resFile ) {
                    $res[] = $resFile;
                }
            }
        }

        return count($res);
    }



    public function allowModifySOurceGoogleDrive() {

        $userSecUtil = $this->container->get('user_security_utility');
        $environment = $userSecUtil->getSiteSettingParameter('environment');

        if( $environment == "live" ) {
            return true;
        }

        //Never delete sources for non live server
        return false;
    }

    public function getGoogleService() {
        $res = $this->authenticationGoogle();
        if( $res ) {
            return $res['service'];
        }

        return NULL;
    }

    public function authenticationGoogle() {
        //return $this->authenticationP12Key();
        return $this->authenticationGoogleOAuth();
    }

    //Authentication based on "google/apiclient": "v2.2.3" and credentials.json
    //https://developers.google.com/people/quickstart/php
    public function authenticationGoogleOAuth() {
        $logger = $this->container->get('logger');
        $userSecUtil = $this->container->get('user_security_utility');

        $pkey = $userSecUtil->getSiteSettingParameter('p12KeyPathFellApp');
        if( !$pkey ) {
            $logger->warning('p12KeyPathFellApp/credentials.json is not defined in Site Parameters. File='.$pkey);
        }

        //$client = $this->getClient();
        //$service = new \Google_Service_Drive($client);

        $client = new \Google_Client();

        $client->setApplicationName('Fellowship Applications');
        $client->setAccessType('offline');
        $client->setIncludeGrantedScopes(true);
        $client->setScopes(array('https://www.googleapis.com/auth/drive'));

        //$client->setDeveloperKey($pkey);
        $client->setAuthConfig($pkey);

        $service = new \Google_Service_Drive($client);

        $res = array(
            'client' => $client,
            'service' => $service
        );

        return $res;
    }

    function getFileById( $fileId, $service=null ) {
        if( !$service ) {
            $service = $this->getGoogleService();
        }
        if( !$service ) {
            return null;
        }
        $file = $service->files->get($fileId);
        return $file;
    }




}

