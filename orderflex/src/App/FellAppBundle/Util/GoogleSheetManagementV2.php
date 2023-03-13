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
use Google\Service\Drive;
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


use Google\Auth\CredentialsLoader;
use Google\Auth\Middleware\AuthTokenMiddleware;
use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;

//Auth (Create Google Cloud Project, enable APIs, config OAuth, create access credentials): https://developers.google.com/workspace/guides/get-started
//Search files: https://developers.google.com/drive/api/guides/search-files

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

        $service = $this->getGoogleService();

//        $folderId = "1b_tL1MDsS6fCysBcP6X7MjhdS9jryiYf";
//        $optParams = array(
//            'pageSize' => 10,
//            'fields' => "nextPageToken, files(contentHints/thumbnail,fileExtension,iconLink,id,name,size,thumbnailLink,webContentLink,webViewLink,mimeType,parents)",
//            'q' => "'".$folderId."' in parents"
//        );
        //$files = $service->files->listFiles($optParams);

        $optParams = array(
            'pageSize' => 10,
            //'fields' => 'files(id,name,mimeType)',
            //'q' => 'mimeType = "application/vnd.google-apps.spreadsheet" and "root" in parents',
            'orderBy' => 'name'
        );
        $results = $service->files->listFiles($optParams);
        $files = $results->getFiles();

//        $response = $service->files->get(
//            "1maBuBYjB_xEiQi8lqtNDzUhQwEDrFi_o",
//            array(
//                'alt' => 'media'
//                //'mimeType' => 'application/json'
//            )
//        );
//        dump($files);
//        exit('111');

        echo "files count=".count($files)."<br>";
        dump($files);
        foreach($files as $file) {
            echo $file->getName()."<br>";
        }
        exit('111');

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
    public function searchFiles()
    {
        //$credentialsJsonFile = __DIR__ . '/../Util/FellowshipApplication-f1d9f98353e5.p12';
        //$credentialsJsonFile = __DIR__ . '/../Util/client_secret_4.json';
        //$credentialsJsonFile = __DIR__ . '/../Util/turnkey-delight.json';
        $credentialsJsonFile = __DIR__ . '/../Util/ambient-highway.json';
        try {
            $client = new \Google\Client();
            $client->setAuthConfig($credentialsJsonFile);
            $client->addScope(Drive::DRIVE);
            //$client->setSubject("1040591934373-c7rvicvmf22s0slqfejftmpmc9n1dqah@developer.gserviceaccount.com");
            //$client->setDeveloperKey('');
            //$driveService = new Drive($client);
            $driveService = new \Google\Service\Drive($client);
            //$file = $this->getFileById("1maBuBYjB_xEiQi8lqtNDzUhQwEDrFi_o",$driveService);
            $files = array();
            $pageToken = null;
            do {
                $response = $driveService->files->listFiles(array(
                    //'q' => "mimeType='application/pdf'",
                    //'q' => "mimeType='application/vnd.google-apps.spreadsheet'",
                    'spaces' => 'drive',
                    'pageToken' => $pageToken,
                    'fields' => 'nextPageToken, files(id, name)',
                ));
                foreach ($response->files as $file) {
                    printf("Found file: %s (%s)\n", $file->name, $file->id);
                }
                array_push($files, $response->files);

                $pageToken = $response->pageToken;
            } while ($pageToken != null);
            return $files;
        } catch(Exception $e) {
            echo "Error Message: ".$e;
        }
    }

    //1)  Import sheets from Google Drive
    //1a)   import all sheets from Google Drive folder
    //1b)   add successefull downloaded sheets to DataFile DB object with status "active"
    public function getConfigOnGoogleDrive() {

        if( $this->security->isGranted('ROLE_FELLAPP_ADMIN') === false ) {
            //return $this->redirect( $this->generateUrl('fellapp-nopermission') );
            return NULL;
        }

        $logger = $this->container->get('logger');
        $userSecUtil = $this->container->get('user_security_utility');
        //$systemUser = $userSecUtil->findSystemUser();

        //get Google service
        $googlesheetmanagement2 = $this->container->get('fellapp_googlesheetmanagement_v2');
        $service = $googlesheetmanagement2->getGoogleService();

        if( !$service ) {
            $event = "Google API service failed!";
            //exit($event);
            $logger->warning("getConfigOnGoogleDrive: ".$event);
            return NULL;
        }

        //echo "service ok <br>";

        //https://drive.google.com/file/d/1EEZ85D4sNeffSLb35_72qi8TdjD9nLyJ/view?usp=sharing
//        $fileId = "1EEZ85D4sNeffSLb35_72qi8TdjD9nLyJ"; //config.json
//        //$fileId = "0B2FwyaXvFk1efmlPOEl6WWItcnBveVlDWWh6RTJxYzYyMlY2MjRSalRvUjdjdzMycmo5U3M"; //FellowshipApplication
//        $file = null;
//        try {
//            $file = $service->files->get($fileId);
//            exit("fileId=".$file->getId()."; title=".$file->getTitle());
//        } catch (Exception $e) {
//            throw new IOException('Google API: Unable to get file by file id='.$fileId.". An error occurred: " . $e->getMessage());
//        }

        $configFileFolderIdFellApp = $userSecUtil->getSiteSettingParameter('configFileFolderIdFellApp');
        if( !$configFileFolderIdFellApp ) {
            $logger->warning('Google Drive Folder ID with config file is not defined in Site Parameters. configFileFolderIdFellApp='.$configFileFolderIdFellApp);
            return NULL;
        }
        //$folderIdFellApp = "0B2FwyaXvFk1efmlPOEl6WWItcnBveVlDWWh6RTJxYzYyMlY2MjRSalRvUjdjdzMycmo5U3M";
        echo "folder ID=".$configFileFolderIdFellApp."<br>";

        $configFile = $this->findConfigFileInFolder($service, $configFileFolderIdFellApp, "config.json");

        dump($configFile);

        return $configFile;
    }
    /**
     * @param Google_Service_Drive $service Drive API service instance.
     * @param String $folderId ID of the folder to print files from.
     * @param String $fileName Name (Title) of the config file to find.
     */
    function findConfigFileInFolder($service, $folderId, $fileName) {
        $pageToken = NULL;

        do {
            try {

                if ($pageToken) {
                    $parameters['pageToken'] = $pageToken;
                }

                //$parameters = array();
                //$parameters = array('q' => "trashed=false and title='config.json'");
                //$children = $service->children->listChildren($folderId, $parameters);
                //$parameters = array('q' => "'".$folderId."' in parents and trashed=false and title='".$fileName."'");

                //TODO: Error calling GET https://www.googleapis.com/drive/v3/
                //Error calling GET https://www.googleapis.com/drive/v3/files?q=%270B2FwyaXvFk1efmlPOEl6WWItcnBveVlDWWh6RTJxYzYyMlY2MjRSalRvUjdjdzMycmo5U3M%27
                //+in+parents+and+trashed%3Dfalse+and+title%3D%27config.json%27: (400) Invalid Value

                //q="mimeType='application/vnd.google-apps.spreadsheet' and parents in '{}'".format(folder_id)
                //$parameters = array('q' => "'".$folderId."' in parents and title='".$fileName."'");

                //https://stackoverflow.com/questions/36605461/downloading-a-file-with-google-drive-api-with-php
                //The getItems() method in v2 will become getFiles() in v3 and the getTitle() will become getName()
                $parameters = array(
                    'q' => "'".$folderId."' in parents and trashed=false and name='".$fileName."'",
                    'fields' => 'nextPageToken, files(*)'
                );

                $files = $service->files->listFiles($parameters); //Google_Service_Drive_FileList

                //dump($files);
                //exit('111');

                foreach ($files->getFiles() as $file) {
                    //echo "File ID=" . $file->getId()."<br>";
                    //echo "File Title=" . $file->getName()."<br>";

                    return $file;
                }
                $pageToken = $files->getNextPageToken();
            } catch (Exception $e) {
                print "An error occurred: " . $e->getMessage();
                $pageToken = NULL;
            }
        } while ($pageToken);

        return NULL;
    }
    function downloadGeneralFile($service,$file,$sendEmail=true) {
        $logger = $this->container->get('logger');
        $logger->notice("downloadGeneralFile process by file get");
        try {
            $fileId = $file->getId();
            $response = $service->files->get(
                $fileId,
                array(
                    'alt' => 'media'
                )
            );

            return $response;
        } catch(Exception $e) {
            //echo "Error Message: " . $e;
            $subject = "ERROR: downloadGeneralFile can not download fileid=$fileId file, mimetype=".$file->getMimeType();
            $body = $subject . "; Error=" . $e;
            $this->onDownloadFileError($subject,$body,$sendEmail);
        }
        return null;
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
        //$client->setSubject("1040591934373-1sjcosdt66bmani0kdrr5qmc5fibmvk5@developer.gserviceaccount.com");
        //$client->setDeveloperKey("");

        //$scopes = [ Drive::DRIVE ];

        //https://console.cloud.google.com/iam-admin
        //Click: "Service Accounts"
        //Click: "Service account 2"
        //Keys: Add Key => json


        //$pkey = __DIR__ . '/../Util/FellowshipApplication-f1d9f98353e5.p12';
        //$credentialsJsonFile = __DIR__ . '/../Util/client_secret_4.json';
        $credentialsJsonFile = __DIR__ . '/../Util/turnkey-delight.json';
        //$homepage = file_get_contents($credentialsJsonFile);
        //echo $homepage;

        //echo "credentialsJsonFile=$credentialsJsonFile <br>";
        $client->setAuthConfig($credentialsJsonFile);

        // make the request
        //$response = $client->get('drive/v2/files');
        //print_r((string) $response->getBody());

        //https://github.com/googleapis/google-api-php-client
        //$service = new \Google_Service_Drive($client);
        $service = new \Google\Service\Drive($client);

        $res = array(
            'client' => $client,
            'service' => $service
        );

        return $res;
    }

    public function runTest() {
//    use Google\Auth\CredentialsLoader;
//    use Google\Auth\Middleware\AuthTokenMiddleware;
//    use GuzzleHttp\Client;
//    use GuzzleHttp\HandlerStack;

// Define the Google Application Credentials array
        $jsonKey = ['key' => ''];

// define the scopes for your API call
        $scopes = ['https://www.googleapis.com/auth/drive.readonly'];

// Load credentials
        $creds = CredentialsLoader::makeCredentials($scopes, $jsonKey);

// optional caching
// $creds = new FetchAuthTokenCache($creds, $cacheConfig, $cache);

// create middleware
        $middleware = new AuthTokenMiddleware($creds);
        $stack = HandlerStack::create();
        $stack->push($middleware);

// create the HTTP client
        $client = new Client([
            'handler' => $stack,
            'base_uri' => 'https://www.googleapis.com',
            'auth' => 'google_auth'  // authorize all requests
        ]);

// make the request
        $response = $client->get('drive/v2/files');

// show the result!
        print_r((string) $response->getBody());
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

