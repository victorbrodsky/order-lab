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
//https://developers.google.com/drive/api/v2/reference/files/get

//1) Go to https://console.cloud.google.com and create new or choose existing project (i.e. "FellowshipAuth")
//2) Click to "IAM & Admin"
//3) On the left side click "Service Accounts"
//4) Under "Key" section click "ADD KEY" and create a new key in JSON format for google api v2
//5) Set the field "Full Path to p12 key or service account credentials.json file for accessing the Google Drive API" to this JSON ket path
//6) Share Google Drive folder with the user from the service ket. Otherwise, the file list will contain only 1 file "Getting started"
//7) 

class GoogleSheetManagementV2 {

    protected $em;
    protected $container;
    protected $security;

    public function __construct( EntityManagerInterface $em, ContainerInterface $container, Security $security ) {
        $this->em = $em;
        $this->container = $container;
        $this->security = $security;
    }

    public function getService() {
        //$credentialsJsonFile = __DIR__ . '/../Util/turnkey-delight.json';
        //$credentialsJsonFile = __DIR__ . '/../Util/ambient-highway.json';
        $credentialsJsonFile = __DIR__ . '/../Util/turnkey-delight-serviceaccount2.json'; //based on "Service Account 2"
        //$credentialsJsonFile = __DIR__ . '/../Util/client_secret_5.json'; //oAuth

        //$pkey = __DIR__ . '/../Util/FellowshipApplication-f1d9f98353e5.p12';
        $logger = $this->container->get('logger');
        $userSecUtil = $this->container->get('user_security_utility');
        $credentialsJsonFile = $userSecUtil->getSiteSettingParameter('p12KeyPathFellApp');
        if( !$credentialsJsonFile ) {
            $logger->warning('JSON service key is not defined in Site Parameters, in field p12KeyPathFellApp. Json file='.$credentialsJsonFile);
        }

        $client = new \Google\Client();
        $client->setAuthConfig($credentialsJsonFile);
        //$client->addScope(Drive::DRIVE);

        $client->addScope("https://www.googleapis.com/auth/drive");
        $client->addScope("https://www.googleapis.com/auth/drive.file");
        $client->addScope("https://www.googleapis.com/auth/drive.metadata");
        $client->addScope("https://www.googleapis.com/auth/drive.appdata");
        $client->addScope("https://spreadsheets.google.com/feeds");

        //$user_to_impersonate = $userSecUtil->getSiteSettingParameter('userImpersonateEmailFellApp');
        //if( !$user_to_impersonate ) {
        //    throw new \InvalidArgumentException('userImpersonateEmailFellApp is not defined in Site Parameters.');
        //}

        //$user_to_impersonate = "olegivanov@pathologysystems.org";
        //$client->setSubject($user_to_impersonate);

        //Json and api key gives the same "File not found"
        //$client->setApplicationName("Fellowship Application 2");
        //$client->setDeveloperKey("");
        //$client->addScope("https://www.googleapis.com/auth/drive");
        //$client->setSubject("google-drive-service-account@ambient-highway-380513.iam.gserviceaccount.com");

        //$client->setSubject("1040591934373-c7rvicvmf22s0slqfejftmpmc9n1dqah@developer.gserviceaccount.com");
        //$client->setDeveloperKey('');
        //$driveService = new Drive($client);
        $service = new \Google\Service\Drive($client);
        return $service;
    }

    /**
     * Retrieve a list of File resources.
     *
     * @return Array List of Google_Service_Drive_DriveFile resources.
     */
    function retrieveAllFiles($service=null) {
        if( !$service ) {
            $service = $this->getService();
        }

        try {
            $parameters = array(
                //'incompleteSearch' => true
                "spaces" => "drive",
                //'items' => array()
            );
            $files = $service->files->listFiles($parameters);
        } catch (Exception $e) {
            print "An error occurred: " . $e->getMessage();
        }
        return $files;
    }

    /**
     * Print a file's metadata.
     *
     * @param Google_Service_Drive $service Drive API service instance.
     * @param string $fileId ID of the file to print metadata for.
     */
    function printFile($service, $fileId) {
        try {
            $file = $service->files->get($fileId);
            //dump($file);
            print "Title: " . $file->getName()."<br>";
            print "Description: " . $file->getDescription()."<br>";
            print "MIME type: " . $file->getMimeType()."<br>";
        } catch (Exception $e) {
            print "An error occurred: " . $e->getMessage();
        }
    }


    function testFiles( $service ) {

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

    /**
     * Download a file's content.
     *
     * @param Google_Servie_Drive $service Drive API service instance.
     * @param Google_Servie_Drive_DriveFile $file Drive File instance.
     * @param String $type Document type string.
     * @return String The file's content if successful, null otherwise.
     */
    function downloadFile($service, $file, $type=null, $sendEmail=true) {
        $logger = $this->container->get('logger');
        $mimeType = $file->getMimeType();
        $logger->notice("downloadFile: mimeType=".$mimeType);
        //echo "mimeType=$mimeType <br>";
        $fileId = $file->getId();
        //echo "fileId=[$fileId], mimeType=[$mimeType] <br>";

        if( $mimeType == 'application/vnd.google-apps.spreadsheet' ) {
            //Google Sheets - works
            //echo "Case: Google Sheets <br>";
            $mimeType = 'text/csv'; //'application/vnd.google-apps.spreadsheet';
        }
        elseif( $mimeType == 'application/vnd.google-apps.document' ) {
            //Google Docs - works
            //echo "Case: Google Docs <br>";
            $mimeType = 'application/pdf';
        }
        elseif( $mimeType == 'application/pdf' ) {
            //PDF - works
            //echo "Case: PDF <br>";
            $mimeType = 'application/pdf'; //not working anymore?
            return $this->downloadGeneralFile($service,$file,$sendEmail); //working for pdf
        }
        elseif( $mimeType == 'application/msword' ) {
            //Word - works with get file (downloadGeneralFile), not working with export
            //echo "Case: Word <br>";
            //$mimeType = 'application/pdf'; //testing
            return $this->downloadGeneralFile($service,$file,$sendEmail);
        }
        else {
            //echo "Case: ALl others <br>";
            return $this->downloadGeneralFile($service,$file,$sendEmail);
        }

        $logger->notice("downloadFile: process by file export");
        try {
            $response = $service->files->export(
                $fileId,
                //'application/pdf',
                $mimeType,
                array(
                    'alt' => 'media'
                )
            );
            $content = $response->getBody()->getContents();
            return $content;
        }  catch(Exception $e) {
            echo "Error Message: ".$e;
            //exit("Error Message: ".$e);
            $subject = "ERROR: downloadFile can not download fileid=$fileId file, mimetype=".$file->getMimeType();
            $body = $subject . "; Error=" . $e;
            $this->onDownloadFileError($subject,$body,$sendEmail);
        }
        return null;
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
            $content = $response->getBody()->getContents();
            return $content;
        } catch(Exception $e) {
            //echo "Error Message: " . $e;
            $subject = "ERROR: downloadGeneralFile can not download fileid=$fileId file, mimetype=".$file->getMimeType();
            $body = $subject . "; Error=" . $e;
            $this->onDownloadFileError($subject,$body,$sendEmail);
        }
        return null;
    }
    function downloadFileTest($service,$file,$sendEmail=true) {
        $mimeType = $file->getMimeType();
        echo "mimeType=$mimeType <br>";

        $mimeType = 'text/plain';
        $mimeType = 'application/json';
        $mimeType = 'text/csv';

        try {
            $fileId = $file->getId();

            if(1) {
                $response = $service->files->get(
                    $fileId,
                    array(
                        'alt' => 'media'
                    )
                );
            } else {
                $response = $service->files->export(
                    $fileId,
                    //'application/pdf',
                    //'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                    $mimeType,
                    array(
                        'alt' => 'media'
                    )
                );
            }

            $content = $response->getBody()->getContents();
            return $content;
        } catch(Exception $e) {
            //echo "Error Message: " . $e;
            $subject = "ERROR: downloadGeneralFile can not download fileid=$fileId file, mimetype=".$file->getMimeType();
            $body = $subject . "; Error=" . $e;
            $this->onDownloadFileError($subject,$body,$sendEmail);
        }
        return null;
    }

    function onDownloadFileError( $subject, $body, $sendEmail=true ) {
        return null;
    }






    public function searchFiles()
    {
        //"Service Account 2" - p12 key is working
        //"Service Account 2" - json -> result 1 file "Getting started" id "0B0PyCK-oDTOEc3RhcnRlcl9maWxl"
        //https://stackoverflow.com/questions/27455510/google-service-drive-retrievelistofallfiles-only-returning-one-file
        //Share folder to user from json file
        //1040591934373-1sjcosdt66bmani0kdrr5qmc5fibmvk5@developer.gserviceaccount.com is external to
        // Weill Cornell Medical College Pathology Department, who owns the item.
        // This organization encourages caution when sharing externally.

        //$credentialsJsonFile = __DIR__ . '/../Util/FellowshipApplication-f1d9f98353e5.p12';
        //$credentialsJsonFile = __DIR__ . '/../Util/client_secret_4.json';
        //$credentialsJsonFile = __DIR__ . '/../Util/turnkey-delight.json';
        //$credentialsJsonFile = __DIR__ . '/../Util/turnkey-delight-2.json';
        $credentialsJsonFile = __DIR__ . '/../Util/turnkey-delight-serviceaccount2.json'; //based on "Service Account 2"
        //$credentialsJsonFile = __DIR__ . '/../Util/quickstart.json';
        //$credentialsJsonFile = __DIR__ . '/../Util/quickstart-FellowshipAuth.json';
        //$credentialsJsonFile = __DIR__ . '/../Util/ambient-highway.json';
        //$credentialsJsonFile = __DIR__ . '/../Util/ambient-highway-2.json';
        //$credentialsJsonFile = __DIR__ . '/../Util/quickstart-cinava.json';
        //$credentialsJsonFile = __DIR__ . '/../Util/client_secret_cinava_oauth.json';
        //$credentialsJsonFile = json_decode(file_get_contents($credentialsJsonFile), true);

        echo "credentialsJsonFile=$credentialsJsonFile <br>";

        try {
            $client = new \Google\Client();
            $client->setApplicationName('Fellowship Applications');
            $client->setAccessType('offline');
            $client->setIncludeGrantedScopes(true);
            //$client->setScopes(array('https://www.googleapis.com/auth/drive'));
            $client->setAuthConfig($credentialsJsonFile);
            //$scopes = [ Drive::DRIVE ];
            //$client->setScopes($scopes);
            $client->addScope(Drive::DRIVE);
            //$client->addScope("https://www.googleapis.com/auth/drive");
            //$client->addScope("https://www.googleapis.com/auth/drive.file");
            //$client->addScope("https://www.googleapis.com/auth/drive.metadata");
            //$client->addScope("https://www.googleapis.com/auth/drive.appdata");
            //$client->addScope("https://spreadsheets.google.com/feeds");
            //$client->setSubject("1040591934373-c7rvicvmf22s0slqfejftmpmc9n1dqah@developer.gserviceaccount.com");
            //$client->setDeveloperKey('');
            //$driveService = new Drive($client);
            $driveService = new Drive($client);
            //$file = $this->getFileById("1maBuBYjB_xEiQi8lqtNDzUhQwEDrFi_o",$driveService);
            //$folderId = "0B2FwyaXvFk1efmhvOVhLSlczSmNMdXJudV9Xb3NpLU9nbmhnZXhPRlJwN2sxd1RGTjZpckE";
            //$fileName = '';
            $files = array();
            $pageToken = null;
            do {
                //$fileId = "1mzVYbtdN72PPEqJ0qlWwon6-ca9epH8iP86mjjpSjLw";
                //$downloadUrl = 'https://www.googleapis.com/drive/v2/files/'.$fileId.'?alt=media&source=downloadUrl';
                //$request = new Google_Http_Request($downloadUrl, 'GET', null, null);
                //$httpRequest = $driveService->getClient()->getAuth()->authenticatedRequest($request);

                //$body = $httpRequest->getResponseBody();
                //dump($body);
                //exit("downloadGeneralFileGoogleDoc");

                $response = $driveService->files->listFiles(array(
                    //'q' => "mimeType='application/pdf'",
                    //'q' => "mimeType='application/vnd.google-apps.spreadsheet'",
                    //'q' => "'".$folderId."' in parents",
                    //'q' => "mimeType='application/vnd.google-apps.folder'",
                    //'spaces' => 'drive',
                    //'pageToken' => $pageToken,
                    //'incompleteSearch' => true,
                    //"mimeType" => "application/vnd.google-apps.spreadsheet",
                    //'supportsAllDrives' => true,
                    //'fields' => 'nextPageToken, files(id, name)',
                    //'pageSize' => 10
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
    public function getConfigOnGoogleDrive( $configFileName='config-fellapp.json' ) {

        if( $this->security->isGranted('ROLE_FELLAPP_ADMIN') === false ) {
            //return $this->redirect( $this->generateUrl('fellapp-nopermission') );
            return NULL;
        }

        $logger = $this->container->get('logger');
        $userSecUtil = $this->container->get('user_security_utility');
        //$systemUser = $userSecUtil->findSystemUser();

        //get Google service
        $service = $this->getGoogleService();

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

        $configFile = $this->findConfigFileInFolder($service, $configFileFolderIdFellApp, $configFileName);
        //dump($configFile);

        //$contentConfigFile = $this->downloadGeneralFile($service, $configFile); #v1,2 #"message": "Only files with binary content can be downloaded. Use Export with Docs Editors files.",
        //$contentConfigFile = $this->downloadFile($service, $configFile);    #v3
        $contentConfigFile = $this->downloadFileTest($service, $configFile);    #v3
        dump($contentConfigFile);

        //use webContentLink

        //$configFileContent = json_decode($contentConfigFile, true);
        //exit('111');

        return $contentConfigFile;
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

        $credentialsJsonFile = $userSecUtil->getSiteSettingParameter('p12KeyPathFellApp');
        if( !$credentialsJsonFile ) {
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
        //$credentialsJsonFile = __DIR__ . '/../Util/turnkey-delight.json';
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

