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
 * Date: 3/21/2016
 * Time: 10:43 AM
 */

namespace Oleg\FellAppBundle\Util;

//use https://github.com/asimlqt/php-google-spreadsheet-client/blob/master/README.md
//install:
//1) composer.phar install
//2) composer.phar update

//TODO: implement
// "Delete successfully imported applications from Google Drive",
// "deletion of rows from the spreadsheet on Google Drive upon successful import"
// "Automatically delete downloaded applications that are older than [X] year(s)".


use Google\Spreadsheet\DefaultServiceRequest;
use Google\Spreadsheet\ServiceRequestFactory;
use Google\Spreadsheet\Spreadsheet;
use Google\Spreadsheet\SpreadsheetService;
use Oleg\UserdirectoryBundle\Entity\Document;
use Oleg\UserdirectoryBundle\Form\DataTransformer\GenericTreeTransformer;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\Filesystem\Exception\IOException;

class GoogleSheetManagement {

    protected $em;
    protected $container;

    public function __construct( $em, $container ) {

        $this->em = $em;
        $this->container = $container;
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

    //////////////////////////////////////////////////////////////////////////////////
    //////////////////////// Methods Modifying Google Drive //////////////////////////

    //foreach file in the row => delete this file from Google Drive
    public function deleteRowInListFeed( $listFeed ) {

        $logger = $this->container->get('logger');

        //Never delete sources for non live server
        if( !$this->allowModifySOurceGoogleDrive() ) {
            $logger->error("Delete Row in ListFeed: Never delete sources for non production environment");
            return false;
        }

        $service = $this->getGoogleService();

        $deletedRows = 0;

        //identify file by presence of string 'drive.google.com/a/pathologysystems.org/file/d/'
        $fileStrFlag = 'drive.google.com/a/pathologysystems.org/file/d/';

        foreach( $listFeed->getEntries() as $entry ) {
            $values = $entry->getValues();
            //echo "list:<br>";
            //print_r($values );
            //echo "<br>";
            //echo "lastname=".$values['lastname']."<br>";

            //4.a) foreach file in the row => delete this file from Google Drive
            foreach( $values as $cellValue ) {

                if( strpos($cellValue, $fileStrFlag) !== false ) {
                    //echo 'this is file = '.$cellValue." => ";
                    //get Google Drive file ID from https://drive.google.com/a/pathologysystems.org/file/d/0B2FwyaXvFk1eWGJQQ29CbjVvNms/view?usp=drivesdk
                    $fileID = $this->getFileId($cellValue);
                    //echo 'fileID = '.$fileID."<br>";
                    $res = $this->deleteFile($service,$fileID);
                    if( $res ) {
                        //echo 'File was deleted with fileID = '.$fileID."<br>";
                    } else {
                        //echo 'Failed to delete file with fileID = '.$fileID."<br>";
                        $logger->warning('Failed to delete file with fileID = '.$fileID);
                    }
                }
            } //foreach cell


            //delete this row (entry)
            $entry->delete();

            $deletedRows++;

        }//foreach row


        //exit(1);
        return $deletedRows;
    }

    /**
     * Permanently delete a file, skipping the trash.
     *
     * @param Google_Service_Drive $service Drive API service instance.
     * @param String $fileId ID of the file to delete.
     */
    function deleteFile($service, $fileId) {
        $logger = $this->container->get('logger');

        //Never delete sources for non live server
        if( !$this->allowModifySOurceGoogleDrive() ) {
            $logger->error("Delete File: Never delete sources for non production environment");
            return false;
        }

        $result = false;

        try {
            $service->files->delete($fileId);
            $result = true;
            $logger->notice("File from Google Drive deleted successfully; fileId=".$fileId);
        } catch (Exception $e) {
            $event = "File from Google Drive deletion failed. An error occurred: " . $e->getMessage();
            $logger->error($event);
        }

        return $result;
    }
    //////////////////////// Methods Modifying Google Drive //////////////////////////
    //////////////////////////////////////////////////////////////////////////////////


    public function getSheetByFileId( $fileId ) {

        $logger = $this->container->get('logger');
        $userSecUtil = $this->container->get('user_security_utility');

        //get Google access token
        $accessToken = $this->getGoogleToken();

        if( !$accessToken ) {
            $systemUser = $userSecUtil->findSystemUser();
            $event = "Google API access Token empty";
            $logger->warning($event);
            $userSecUtil->createUserEditEvent($this->container->getParameter('fellapp.sitename'),$event,$systemUser,null,null,'Error');
            $userSecUtil->sendEmailToSystemEmail($event, $event);
            return null;
        }

        //0 initialize ServiceRequestFactory
        $serviceRequest = new CustomDefaultServiceRequest($accessToken); //use my custom class to set CURLOPT_SSL_VERIFYPEER to false in DefaultServiceRequest
        ServiceRequestFactory::setInstance($serviceRequest);
        $spreadsheetService = new SpreadsheetService();

        //1) find spreadsheet
        $spreadsheet = $spreadsheetService->getSpreadsheetById($fileId);
        if( !$spreadsheet ) {
            throw new IOException('Spreadsheet not found by key='.$fileId);
        }

        //2) find worksheet by name
        $worksheetFeed = $spreadsheet->getWorksheets();
        $worksheet = $worksheetFeed->getByTitle('Form Responses 1');

        return $worksheet;
    }

    public function deleteAllRowsWithUploads( $fileId ) {

        $worksheet = $this->getSheetByFileId($fileId);

        //get all rows in worksheet
        $listFeed = $worksheet->getListFeed();

        $deletedRows = $this->deleteRowInListFeed( $listFeed );

        return $deletedRows;
    }

    //delete: imported rows from the sheet on Google Drive and associated uploaded files from the Google Drive.
    //1) find row in worksheet by rowid (don't use '@'. In google GS '@' is replaced by '_') cinava_yahoo.com_Doe1_Linda1_2016-03-22_17_30_04
    //2) foreach file in the row => delete this file from Google Drive and delete this row
    public function deleteImportedApplicationAndUploadsFromGoogleDrive( $worksheet, $rowId ) {

        $logger = $this->container->get('logger');

        //cinava7_yahoo.com_Doe_Linda_2016-03-15_17_59_53
        //$rowId = "cinava_yahoo.com_Doe1_Linda1_2016-03-22_17_30_04";
        if( !$rowId ) {
            $logger->warning('Fellowship Application Google Form ID does not exists. rowId='.$rowId);
        }

        //1) find row in worksheet by rowid (don't use '@'. In google GS '@' is replaced by '_') cinava_yahoo.com_Doe1_Linda1_2016-03-22_17_30_04
        $listFeed = $worksheet->getListFeed( array("sq" => "id = " . $rowId) ); //it's a row

        //2) foreach file in the row => delete this file from Google Drive
        $deletedRows = $this->deleteRowInListFeed( $listFeed );

        //exit(1);
        return $deletedRows;
    }

    function getFileId($str) {
        // https://drive.google.com/a/pathologysystems.org/file/d/0B2FwyaXvFk1eWGJQQ29CbjVvNms/view?usp=drivesdk
        $parts = explode("/",$str);
        //echo "count=".count($parts)."<br>";
        if( count($parts) == 9 ) {
            //$keyId = $parts[7];
            //echo "keyId=".$keyId."<br>";
            return $parts[7];
        }
        return null;
    }


    /**
     * Retrieve a list of File resources.
     *
     * @param Google_Service_Drive $folderId folder ID.
     * @param Google_Service_Drive $service Drive API service instance.
     * @return Array List of Google_Service_Drive_DriveFile resources.
     */
    function retrieveFilesByFolderId($folderId,$service) {
        $result = array();
        $pageToken = NULL;

        do {
            try {
                $parameters = array('q' => "'".$folderId."' in parents and trashed=false");
                if ($pageToken) {
                    $parameters['pageToken'] = $pageToken;
                }
                $files = $service->files->listFiles($parameters);

                $result = array_merge($result, $files->getItems());
                $pageToken = $files->getNextPageToken();
            } catch (Exception $e) {
                //print "An error occurred: " . $e->getMessage();
                $pageToken = NULL;
            }
        } while ($pageToken);
        return $result;
    }


    public function downloadFileToServer($author, $service, $fileId, $documentType, $path) {
        $file = null;
        try {
            $file = $service->files->get($fileId);
        } catch (Exception $e) {
            throw new IOException('Google API: Unable to get file by file id='.$fileId.". An error occurred: " . $e->getMessage());
        }

        if( $file ) {

            //check if file already exists by file id
            $documentDb = $this->em->getRepository('OlegUserdirectoryBundle:Document')->findOneByUniqueid($file->getId());
            if( $documentDb && $documentType != 'Fellowship Application Backup Spreadsheet' ) {
                //$logger = $this->container->get('logger');
                //$event = "Document already exists with uniqueid=".$file->getId();
                //$logger->warning($event);
                return $documentDb;
            }

            $googlesheetmanagement = $this->container->get('fellapp_googlesheetmanagement');
            $response = $googlesheetmanagement->downloadFile($service, $file, $documentType);
            //echo "response=".$response."<br>";
            if( !$response ) {
                throw new IOException('Error file response is empty: file id='.$fileId);
            }

            //create unique file name
            $currentDatetime = new \DateTime();
            $currentDatetimeTimestamp = $currentDatetime->getTimestamp();

            //$fileTitle = trim($file->getTitle());
            //$fileTitle = str_replace(" ","",$fileTitle);
            //$fileTitle = str_replace("-","_",$fileTitle);
            //$fileTitle = 'testfile.jpg';
            $fileExt = pathinfo($file->getTitle(), PATHINFO_EXTENSION);
            $fileExtStr = "";
            if( $fileExt ) {
                $fileExtStr = ".".$fileExt;
            }

            $fileUniqueName = $currentDatetimeTimestamp.'ID'.$file->getId().$fileExtStr;  //.'_title='.$fileTitle;
            //echo "fileUniqueName=".$fileUniqueName."<br>";

            $filesize = $file->getFileSize();
            if( !$filesize ) {
                $filesize = mb_strlen($response) / 1024; //KBs,
            }


            $object = new Document($author);
            $object->setUniqueid($file->getId());
            $object->setUniquename($fileUniqueName);
            $object->setUploadDirectory($path);
            $object->setSize($filesize);

            //clean originalname
            $object->setCleanOriginalname($file->getTitle());


//            if( $type && $type == 'excel' ) {
//                $fellappSpreadsheetType = $this->em->getRepository('OlegUserdirectoryBundle:DocumentTypeList')->findOneByName('Fellowship Application Spreadsheet');
//            } else {
//                $fellappSpreadsheetType = $this->em->getRepository('OlegUserdirectoryBundle:DocumentTypeList')->findOneByName('Fellowship Application Document');
//            }
            $transformer = new GenericTreeTransformer($this->em, $author, "DocumentTypeList", "UserdirectoryBundle");
            $documentType = trim($documentType);
            $documentTypeObject = $transformer->reverseTransform($documentType);
            if( $documentTypeObject ) {
                $object->setType($documentTypeObject);
            }

            $this->em->persist($object);

            $root = $this->container->get('kernel')->getRootDir();
            //echo "root=".$root."<br>";
            //$fullpath = $this->get('kernel')->getRootDir() . '/../web/'.$path;
            $fullpath = $root . '/../web/'.$path;
            $target_file = $fullpath . "/" . $fileUniqueName;

            //$target_file = $fullpath . 'uploadtestfile.jpg';
            //echo "target_file=".$target_file."<br>";
            if( !file_exists($fullpath) ) {
                // 0600 - Read/write/execute for owner, nothing for everybody else
                mkdir($fullpath, 0700, true);
                chmod($fullpath, 0700);
            }

            file_put_contents($target_file, $response);

            return $object;
        }

        return null;
    }


    /**
     * Search for folder by the parent folder ID and folder Name.
     */
    function findFolderByFolderNameAndParentFolder_OLD($service,$parentFolderId,$folderName) {
        $result = array();
        $pageToken = NULL;

        do {
            try {

                $parameters = array('q' => "'" . $parentFolderId . "' in parents and trashed=false and title='config.json'");
                $files = $service->files->listFiles($parameters);

                foreach ($files->getItems() as $file) {
                    echo "file=" . $file->getId() . "<br>";
                    echo "File Title=" . $file->getTitle() . "<br>";
                }

                return $file;


                //files.list?q=mimetype=application/vnd.google-apps.folder and trashed=false&fields=parents,name
                $parameters = array('q' => "mimetype=application/vnd.google-apps.folder and '".$parentFolderId."' in parents and trashed=false and title='".$folderName."'");
                $parameters = array('q' => "'" . $parentFolderId . "' in parents and trashed=false and title='config.json'");
                if ($pageToken) {
                    $parameters['pageToken'] = $pageToken;
                }
                $folders = $service->files->listFiles($parameters);
                foreach ($folders->getItems() as $folder) {
                    echo "file=" . $folder->getId() . "<br>";
                    echo "File Title=" . $folder->getTitle() . "<br>";
                    $result = $folder;
                }

                //$result = array_merge($result, $folders->getItems());

                $pageToken = $folders->getNextPageToken();
            } catch (Exception $e) {
                //print "An error occurred: " . $e->getMessage();
                $pageToken = NULL;
            }
        } while ($pageToken);
        return $result;
    }
    /**
     * @param Google_Service_Drive $service Drive API service instance.
     * @param String $folderId ID of the folder to print files from.
     * @param String $fileName Name (Title) of the config file to find.
     */
    function findFolderByFolderNameAndParentFolder($service, $folderId, $fileName) {
        $pageToken = NULL;

        do {
            try {

                if ($pageToken) {
                    $parameters['pageToken'] = $pageToken;
                }

                //$parameters = array();
                //$parameters = array('q' => "trashed=false and title='config.json'");
                //$children = $service->children->listChildren($folderId, $parameters);
                $parameters = array('q' => "'".$folderId."' in parents and trashed=false and title='".$fileName."'");
                $files = $service->files->listFiles($parameters);

                foreach ($files->getItems() as $file) {
                    echo "File ID=" . $file->getId()."<br>";
                    echo "File Title=" . $file->getTitle()."<br>";

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


//    /**
//     * Retrieve a list of revisions.
//     *
//     * @param String $fileId ID of the file to retrieve revisions for.
//     * @return Array List of Google_Servie_Drive_Revision resources.
//     */
//    function retrieveRevisions($fileId) {
//        $service = $this->getGoogleService();
//        $revisionItems = null;
//
//        try {
//            $revisions = $service->revisions->listRevisions($fileId);
//            $revisionItems = $revisions->getItems();
//        } catch (Exception $e) {
//            print "An error occurred: " . $e->getMessage();
//        }
//
//        if( $revisionItems ) {
//            //$lastRevision = null;
//            $revcount = 0;
//            foreach ($revisions as $revision) { //revision is Google_Service_Drive_Revision object
//                echo "<br>";
//                print_r($revision);
//                echo "<br>";
//                $revisionId = $revision->getId();
//                echo "revisionId=".$revisionId."<br>";
//                //$lastRevision = $revision;
//                $revcount++;
//            }
//            echo "revcount=" . $revcount . "<br>";
//
//            echo "delete revision id=".$revisionId."; time=".$revision->getModifiedDate()."<br>";
//            //https://developers.google.com/drive/v3/reference/revisions/delete?authuser=1#try-it
//            $revision = $service->revisions->get($fileId, $revisionId);
//            //$revision->delete();
//            //$revision->setPublished(true);
//            $revision->setPinned(false);
//            $revision->setDownloadUrl('www.yahoo.com');
//            $revision->setMimeType('testTTT');
//            $revision->setExportLinks(null);
//            $service->revisions->update($fileId, $revisionId, $revision);
//
//            $revision = $service->revisions->get($fileId, $revisionId);
//            echo "<br>";
//            print_r($revision);
//            echo "<br>";
//
//            //$this->removeRevision($service, $fileId, $revisionId);
//        }
//
//        return NULL;
//    }

//    /**
//     * https://developers.google.com/drive/v2/reference/revisions/delete#try-it
//     * Remove a revision.
//     *
//     * @param Google_Service_Drive $service Drive API service instance.
//     * @param String $fileId ID of the file to remove the revision for.
//     * @param String $revisionId ID of the revision to remove.
//     */
//    function removeRevision($service, $fileId, $revisionId) {
//        try {
//            $service->revisions->delete($fileId, $revisionId);
//        } catch (Exception $e) {
//            print "An error occurred: " . $e->getMessage();
//        }
//    }



//    function searchSheet() {
//        $accessToken = $this->getGoogleToken();
//
//        //use my custom class to set CURLOPT_SSL_VERIFYPEER to false in DefaultServiceRequest
//        $serviceRequest = new CustomDefaultServiceRequest($accessToken);
//        ServiceRequestFactory::setInstance($serviceRequest);
//
//        $spreadsheetService = new SpreadsheetService();
//        //$spreadsheetFeed = $spreadsheetService->getSpreadsheets();
//        //$spreadsheet = $spreadsheetFeed->getByTitle('Fellapp-test');
//        $key = '1hNJUm-EWC33tEyvgkcBJQ7lO1PcFwxfi3vMuB96etno';
//        //$spreadsheet = $this->getByKey($spreadsheetFeed,$key);
//
//        $spreadsheet = $spreadsheetService->getSpreadsheetById($key);
//        if( !$spreadsheet ) {
//            throw new IOException('Spreadsheet not found by key='.$key);
//        }
//
//        $worksheetFeed = $spreadsheet->getWorksheets();
//        $worksheet = $worksheetFeed->getByTitle('Form Responses 1');
//        $listFeed = $worksheet->getListFeed();
//
//        $rowTitle = "cinava7@yahoo.com_Doe_Linda_2016-03-15_17_59_53";
//        $rowTitle = "cinava7@";
//        $rowTitle = "testid1";
//        $rowTitle = "test1emailcinava@yahoo.com";
//        $rowTitle = "test1emailcinava_yahoo.com_Doe_Linda_2016-03-15_17_59_5";
//        $rowTitle = "cinava7_yahoo.com_Doe_Linda_2016-03-15_17_59_53";
//
//        //$rowTitle = urlencode($rowTitle);
//        //echo "rowTitle=".$rowTitle."<br>";
//
//        $listFeed = $worksheet->getListFeed(array("sq" => "id = $rowTitle", "reverse" => "true"));
//
//        $entries = $listFeed->getEntries();
//        foreach( $entries as $entry ) {
//            echo "list:<br>";
//            $values = $entry->getValues();
//            print_r($values );
//            echo "<br>";
//            echo "lastname=".$values['lastname']."<br>";
//        }
//        echo "eof list<br><br>";
//
//        //echo "<br><br>full list:<br>";
//        //print_r($listFeed);
//    }

//    /**
//     * Gets a spreadhseet from the feed by its key . i.e. the id of
//     * the spreadsheet in google drive. This method will return only the
//     * first spreadsheet found with the specified title.
//     *
//     * https://drive.google.com/open?id=1hNJUm-EWC33tEyvgkcBJQ7lO1PcFwxfi3vMuB96etno
//     * key=1hNJUm-EWC33tEyvgkcBJQ7lO1PcFwxfi3vMuB96etno
//     *
//     * @param string $title
//     *
//     * @return \Google\Spreadsheet\Spreadsheet|null
//     */
//    public function getByKey($spreadsheetFeed,$key)
//    {
//        foreach( $spreadsheetFeed->getXml()->entry as $entry ) {
//            //full id: https://spreadsheets.google.com/feeds/spreadsheets/private/full/1hNJUm-EWC33tEyvgkcBJQ7lO1PcFwxfi3vMuB96etno
//            $id = $entry->id->__toString();
//            //echo "id=".$id."<br>";
//            $parts = explode("/",$id);
//            //echo "count=".count($parts)."<br>";
//            if( count($parts) == 8 ) {
//                $keyId = $parts[7];
//                //echo "keyId=".$keyId."<br>";
//                if( $keyId == $key) {
//                    return new Spreadsheet($entry);
//                }
//            }
//        }
//        return null;
//    }












    public function getGoogleToken() {
        $res = $this->authenticationP12Key();
        $obj_client_auth = $res['client'];
        $obj_token  = json_decode($obj_client_auth->getAccessToken() );
        return $obj_token->access_token;
    }

    public function getGoogleService() {
        $res = $this->authenticationP12Key();
        return $res['service'];
    }

    //Using OAuth 2.0 for Server to Server Applications: using PKCS12 certificate file
    //Security page: https://admin.google.com/pathologysystems.org/AdminHome?fral=1#SecuritySettings:
    //Credentials page: https://console.developers.google.com/apis/credentials?project=turnkey-delight-103315&authuser=1
    //https://developers.google.com/api-client-library/php/auth/service-accounts
    //1) Create a service account by Google Developers Console.
    //2) Delegate domain-wide authority to the service account.
    //3) Impersonate a user account.
    public function authenticationP12Key() {

        $logger = $this->container->get('logger');
        $userSecUtil = $this->container->get('user_security_utility');

        //$client_email = '1040591934373-1sjcosdt66bmani0kdrr5qmc5fibmvk5@developer.gserviceaccount.com';
        $client_email = $userSecUtil->getSiteSettingParameter('clientEmailFellApp');

        //$pkey = __DIR__ . '/../Util/FellowshipApplication-f1d9f98353e5.p12';
        $pkey = $userSecUtil->getSiteSettingParameter('p12KeyPathFellApp');
        if( !$pkey ) {
            $logger->warning('p12KeyPathFellApp is not defined in Site Parameters. p12KeyPathFellApp='.$pkey);
        }

        //$user_to_impersonate = 'olegivanov@pathologysystems.org';
        $user_to_impersonate = $userSecUtil->getSiteSettingParameter('userImpersonateEmailFellApp');

        //echo "pkey=".$pkey."<br>";
        $private_key = file_get_contents($pkey); //notasecret

        $googleDriveApiUrlFellApp = $userSecUtil->getSiteSettingParameter('googleDriveApiUrlFellApp');
        if( !$googleDriveApiUrlFellApp ) {
            throw new \InvalidArgumentException('googleDriveApiUrlFellApp is not defined in Site Parameters.');
        }

        //PHP API scopes: https://developers.google.com/api-client-library/php/auth/service-accounts#creatinganaccount
        //set scopes on developer console:
        // https://admin.google.com/pathologysystems.org/AdminHome?chromeless=1#OGX:ManageOauthClients
        //scopes: example: "https://spreadsheets.google.com/feeds https://docs.google.com/feeds"
        //$scopes = array($googleDriveApiUrlFellApp); //'https://www.googleapis.com/auth/drive'
        $scopes = $googleDriveApiUrlFellApp;

        try {

            $credentials = new \Google_Auth_AssertionCredentials(
                $client_email,
                $scopes,
                $private_key,
                'notasecret',                                 // Default P12 password
                'http://oauth.net/grant_type/jwt/1.0/bearer', // Default grant type
                $user_to_impersonate
            );

            $client = new \Google_Client();
            $client->setAssertionCredentials($credentials);

            if( $client->getAuth()->isAccessTokenExpired() ) {
                //echo 'before refreshTokenWithAssertion<br>';
                //print_r($credentials);
                //exit('before');
                $client->getAuth()->refreshTokenWithAssertion($credentials); //causes timeout on localhost: OAuth ERR_CONNECTION_RESET
                //exit('after');
            }

            $service = new \Google_Service_Drive($client);

        } catch(Exception $e) {
            $subject = "Failed to authenticate to Google using P12";
            $event = "Failed to authenticate to Google using P12: " . $e->getMessage();
            $logger->error($event);
            //$userSecUtil->createUserEditEvent($this->container->getParameter('fellapp.sitename'),$event,$systemUser,null,null,'Error');
            $userSecUtil->sendEmailToSystemEmail($subject, $event);
        }

        $res = array(
            'client' => $client,
            'credentials' => $credentials,
            'service' => $service
        );

        return $res;
    }


    /**
     * Download a file's content.
     *
     * @param Google_Servie_Drive $service Drive API service instance.
     * @param Google_Servie_Drive_DriveFile $file Drive File instance.
     * @param String $type Document type string.
     * @return String The file's content if successful, null otherwise.
     */
    function downloadFile($service, $file, $type=null) {
        if( $type && ($type == 'Fellowship Application Spreadsheet' || $type == 'Fellowship Application Backup Spreadsheet') ) {
            $downloadUrl = $file->getExportLinks()['text/csv'];
        } else {
            $downloadUrl = $file->getDownloadUrl();
        }
        //echo "downloadUrl=".$downloadUrl."<br>";
        if ($downloadUrl) {
            $request = new \Google_Http_Request($downloadUrl, 'GET', null, null);
            $httpRequest = $service->getClient()->getAuth()->authenticatedRequest($request);
            //echo "res code=".$httpRequest->getResponseHttpCode()."<br>";
            if ($httpRequest->getResponseHttpCode() == 200) {
                return $httpRequest->getResponseBody();
            } else {
                // An error occurred.
                return null;
            }
        } else {
            // The file doesn't have any content stored on Drive.
            return null;
        }
    }



}