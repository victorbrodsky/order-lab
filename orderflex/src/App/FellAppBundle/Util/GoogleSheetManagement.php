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

namespace App\FellAppBundle\Util;

//use https://github.com/asimlqt/php-google-spreadsheet-client/blob/master/README.md
//install:
//1) composer.phar install
//2) composer.phar update

//TODO: implement
// "Delete successfully imported applications from Google Drive",
// "deletion of rows from the spreadsheet on Google Drive upon successful import"
// "Automatically delete downloaded applications that are older than [X] year(s)".

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

class GoogleSheetManagement {

    protected $em;
    protected $container;

    public function __construct( EntityManagerInterface $em, ContainerInterface $container ) {

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

        //$serviceRequest = new CustomDefaultServiceRequest($accessToken); //use my custom class to set CURLOPT_SSL_VERIFYPEER to false in DefaultServiceRequest
//        $serviceRequest = $this->container->get('fellapp_customd_defaultservicerequest');
//        $serviceRequest->setAccessRequest($accessToken);

        //TODO:  Cannot autowire service "App\FellAppBundle\Util\CustomDefaultServiceRequest": argument "$accessToken" of method "__construct()" is type-hinted "string", you should configure its value explicitly.
//        $serviceRequest = new DefaultServiceRequest($accessToken,"OAuth");
//        $serviceRequest::CURLOPT_SSL_VERIFYPEER = false;

        //1) Use CustomDefaultServiceRequest which extends DefaultServiceRequest with CURLOPT_SSL_VERIFYPEER = false;
        //$serviceRequest = new CustomDefaultServiceRequest($accessToken); //use my custom class to set CURLOPT_SSL_VERIFYPEER to false in DefaultServiceRequest
        //2) Use DefaultServiceRequest and setSslVerifyPeer to false => TODO: test it!
        $serviceRequest = new DefaultServiceRequest($accessToken);
        $serviceRequest->setSslVerifyPeer(false);

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
        $logger = $this->container->get('logger');

        $file = null;
        try {
            $file = $service->files->get($fileId);
        } catch (Exception $e) {
            throw new IOException('Google API: Unable to get file by file id='.$fileId.". An error occurred: " . $e->getMessage());
        }

        if( $file ) {

            $documentType = trim($documentType);

            //check if file already exists by file id
            $documentDb = $this->em->getRepository('AppUserdirectoryBundle:Document')->findOneByUniqueid($file->getId());
            if( $documentDb && $documentType != 'Fellowship Application Backup Spreadsheet' ) {
                //$event = "Document already exists with uniqueid=".$file->getId()."; fileId=".$fileId;
                //$logger->notice($event);
                return $documentDb;
            }

            //$logger->notice("Attempt to download file from Google drive file id=".$fileId."; title=".$file->getTitle());
            $googlesheetmanagement = $this->container->get('fellapp_googlesheetmanagement');
            $response = $googlesheetmanagement->downloadFile($service, $file, $documentType);
            //echo "response=".$response."<br>";
            if( !$response ) {
                throw new IOException('Error application file response is empty: file id='.$fileId."; documentType=".$documentType);
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

            //TODO: use $file->getCreatedTime for creation date? (https://developers.google.com/drive/api/v3/reference/files#createdTime)
            //$file->getCreatedTime is available only in 2.0 google/apiclient
            //https://developers.google.com/resources/api-libraries/documentation/drive/v3/php/latest/class-Google_Service_Drive_DriveFile.html

            //clean originalname
            $object->setCleanOriginalname($file->getTitle());

//            if( $type && $type == 'excel' ) {
//                $fellappSpreadsheetType = $this->em->getRepository('AppUserdirectoryBundle:DocumentTypeList')->findOneByName('Fellowship Application Spreadsheet');
//            } else {
//                $fellappSpreadsheetType = $this->em->getRepository('AppUserdirectoryBundle:DocumentTypeList')->findOneByName('Fellowship Application Document');
//            }
            $transformer = new GenericTreeTransformer($this->em, $author, "DocumentTypeList", "UserdirectoryBundle");
            //$documentType = trim($documentType);
            $documentTypeObject = $transformer->reverseTransform($documentType);
            if( $documentTypeObject ) {
                $object->setType($documentTypeObject);
            }

            $this->em->persist($object);

            $root = $this->container->get('kernel')->getRootDir();
            //echo "root=".$root."<br>";
            //$fullpath = $this->get('kernel')->getRootDir() . '/../web/'.$path;
            $fullpath = $root . '/../public/'.$path;
            $target_file = $fullpath . "/" . $fileUniqueName;

            //$target_file = $fullpath . 'uploadtestfile.jpg';
            //echo "target_file=".$target_file."<br>";
            if( !file_exists($fullpath) ) {
                // 0600 - Read/write/execute for owner, nothing for everybody else
                mkdir($fullpath, 0700, true);
                chmod($fullpath, 0700);
            }

            file_put_contents($target_file, $response);

            //generate two thumbnails
            //$logger = $this->container->get('logger');
            $userServiceUtil = $this->container->get('user_service_utility');
            //$logger->notice("Before thumbnails generated for document ID=".$object->getId());
            $resImage = $userServiceUtil->generateTwoThumbnails($object);
            if( $resImage ) {
                $logger->notice("Thumbnails generated=".$resImage);
            }

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
    function findOneFolderByFolderNameAndParentFolder($service, $folderId, $fileName) {
        $pageToken = NULL;

        do {
            try {

                if ($pageToken) {
                    $parameters['pageToken'] = $pageToken;
                }

                //$parameters = array();
                //$parameters = array('q' => "trashed=false and title='config.json'");
                //$children = $service->children->listChildren($folderId, $parameters);
                $parameters = array('q' => "mimeType='application/vnd.google-apps.folder' and trashed=false and '".$folderId."' in parents and title='".$fileName."'");
                //$parameters = array('q' => "mimeType='application/vnd.google-apps.folder' and trashed=false and title='".$fileName."'");
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

    //Google drive does not search by ancestors. Therefore find in steps.
    //FellowshipApplication -> Responses -> RecommendationLetters -> (RecommendationLetterUploads, Spreadsheets, Template)
    function findOneRecLetterUploadFolder($service, $folderId) {
        //1) use folderId to find folder "Responses"
        $folder = $this->findOneFolderByFolderNameAndParentFolder($service,$folderId,"Responses");
        if( !$folder ) {
            return NULL;
        }

        //2 use folder "Response" to find folder "RecommendationLetters"
        $folder = $this->findOneFolderByFolderNameAndParentFolder($service,$folder->getId(),"RecommendationLetters");
        if( !$folder ) {
            return NULL;
        }

        //3) use folder "RecommendationLetters" to find folder "RecommendationLetterUploads"
        $folder = $this->findOneFolderByFolderNameAndParentFolder($service,$folder->getId(),"RecommendationLetterUploads");
        if( !$folder ) {
            return NULL;
        }

        return $folder;
    }

    //Google drive does not search by ancestors
    //FellowshipApplication -> Responses -> RecommendationLetters -> (RecommendationLetterUploads, Spreadsheets, Template)
    function findOneRecLetterSpreadsheetFolder($service, $folderId) {
        //1) use folderId to find folder "Responses"
        $folder = $this->findOneFolderByFolderNameAndParentFolder($service,$folderId,"Responses");
        if( !$folder ) {
            return NULL;
        }

        //2 use folder "Response" to find folder "RecommendationLetters"
        $folder = $this->findOneFolderByFolderNameAndParentFolder($service,$folder->getId(),"RecommendationLetters");
        if( !$folder ) {
            return NULL;
        }

        //3) use folder "RecommendationLetters" to find folder "Spreadsheets"
        $folder = $this->findOneFolderByFolderNameAndParentFolder($service,$folder->getId(),"Spreadsheets");
        if( !$folder ) {
            return NULL;
        }

        return $folder;
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
        //$res = $this->authenticationP12Key();
        $res = $this->authenticationGoogle();
        $obj_client_auth = $res['client'];
        $obj_token  = json_decode($obj_client_auth->getAccessToken() );
        return $obj_token->access_token;
    }

    public function getGoogleService() {
        //$res = $this->authenticationP12Key();
        $res = $this->authenticationGoogle();
        return $res['service'];
    }

    public function authenticationGoogle() {
        return $this->authenticationP12Key();
        //return $this->authenticationGoogleOAuth();
    }

    //Probably, it's better to use Server to Server authentication by using P12 key
    //Depreciated in google/apiclient v2.0 https://github.com/googleapis/google-api-php-client/blob/master/UPGRADING.mds
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
            $logger->warning('p12KeyPathFellApp/credentials.json is not defined in Site Parameters. File='.$pkey);
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
    //Authentication based on "google/apiclient": "v2.2.3" and credentials.json
    //https://developers.google.com/people/quickstart/php
    public function authenticationGoogleOAuth() {

        $logger = $this->container->get('logger');
        $userSecUtil = $this->container->get('user_security_utility');

        //$client_email = '1040591934373-1sjcosdt66bmani0kdrr5qmc5fibmvk5@developer.gserviceaccount.com';
        //$client_email = $userSecUtil->getSiteSettingParameter('clientEmailFellApp');

        //$pkey = __DIR__ . '/../Util/FellowshipApplication-f1d9f98353e5.p12';
        $pkey = $userSecUtil->getSiteSettingParameter('p12KeyPathFellApp');
        if( !$pkey ) {
            $logger->warning('p12KeyPathFellApp/credentials.json is not defined in Site Parameters. File='.$pkey);
        }

        //$user_to_impersonate = 'olegivanov@pathologysystems.org';
//        $user_to_impersonate = $userSecUtil->getSiteSettingParameter('userImpersonateEmailFellApp');

        //echo "pkey=".$pkey."<br>";
//        $private_key = file_get_contents($pkey); //notasecret

//        $googleDriveApiUrlFellApp = $userSecUtil->getSiteSettingParameter('googleDriveApiUrlFellApp');
//        if( !$googleDriveApiUrlFellApp ) {
//            throw new \InvalidArgumentException('googleDriveApiUrlFellApp is not defined in Site Parameters.');
//        }

        //PHP API scopes: https://developers.google.com/api-client-library/php/auth/service-accounts#creatinganaccount
        //set scopes on developer console:
        // https://admin.google.com/pathologysystems.org/AdminHome?chromeless=1#OGX:ManageOauthClients
        //scopes: example: "https://spreadsheets.google.com/feeds https://docs.google.com/feeds"
        //$scopes = array($googleDriveApiUrlFellApp); //'https://www.googleapis.com/auth/drive'
//        $scopes = $googleDriveApiUrlFellApp;

        $client = $this->getClient();

        //$service = null;
        $service = new \Google_Service_Drive($client);

        //try {
//            $client = $this->getClient();
//            $service = new \Google_Service_Drive($client);
//        } catch(Exception $e) {
//            $subject = "Failed to authenticate to Google";
//            $event = "Failed to authenticate to Google: " . $e->getMessage();
//            $logger->error($event);
//            //$userSecUtil->createUserEditEvent($this->container->getParameter('fellapp.sitename'),$event,$systemUser,null,null,'Error');
//            $userSecUtil->sendEmailToSystemEmail($subject, $event);
//        }

        $res = array(
            'client' => $client,
            'service' => $service
        );

        return $res;
    }
    //https://stackoverflow.com/questions/34130068/fatal-error-class-google-auth-assertioncredentials-not-found
    public function getClient() {

        $logger = $this->container->get('logger');
        $userSecUtil = $this->container->get('user_security_utility');

        $user_to_impersonate = $userSecUtil->getSiteSettingParameter('userImpersonateEmailFellApp');
        if( !$user_to_impersonate ) {
            throw new \InvalidArgumentException('userImpersonateEmailFellApp is not defined in Site Parameters.');
        }

        $credentialsJsonFile = $userSecUtil->getSiteSettingParameter('p12KeyPathFellApp');
        if( !$credentialsJsonFile ) {
            $logger->warning('$credentialsJsonFile is not defined in Site Parameters. $credentialsJsonFile='.$credentialsJsonFile);
        }

        //$client_email = $userSecUtil->getSiteSettingParameter('clientEmailFellApp');


        $client = new \Google_Client();

        $client->setApplicationName('Fellowship Applications');
        //$client->setScopes(\Google_Service_PeopleService::CONTACTS_READONLY);
        //$client->setAuthConfig($credentialsJsonFile);
        $client->setAccessType('offline');
        //$client->setPrompt('select_account consent');
        $client->setIncludeGrantedScopes(true);   // incremental auth

        // set the scope(s) that will be used
        $client->setScopes(array('https://www.googleapis.com/auth/drive'));


        // this is needed only if you need to perform
        // domain-wide admin actions, and this must be
        // an admin account on the domain; it is not
        // necessary in your example but provided for others
        //$client->setSubject($user_to_impersonate);

        // set the authorization configuration using the 2.0 style
//        $client->setAuthConfig(array(
//            'type' => 'service_account',
//            'client_email' => '1040591934373-1sjcosdt66bmani0kdrr5qmc5fibmvk5@developer.gserviceaccount.com', //'395545742105@developer.gserviceaccount.com',
//            'client_id'   => '1040591934373-1sjcosdt66bmani0kdrr5qmc5fibmvk5.apps.googleusercontent.com', //'395545742105.apps.googleusercontent.com',
//            'private_key' => 'b444d50c0264f39580c1c4f63fef2d8f73b5e896'
//        ));

        $client->setAuthConfig($credentialsJsonFile);

        return $client;
    }
    /**
     * https://github.com/googleapis/google-api-php-client
     * https://github.com/googleapis/google-api-php-client/blob/master/UPGRADING.md
     *
     * Returns an authorized API client.
     * @return Google_Client the authorized client object
     */
    function getClient2() {

        $logger = $this->container->get('logger');
        $userSecUtil = $this->container->get('user_security_utility');

        //$client_email = '1040591934373-1sjcosdt66bmani0kdrr5qmc5fibmvk5@developer.gserviceaccount.com';
        //$client_email = $userSecUtil->getSiteSettingParameter('clientEmailFellApp');

        //$pkey = __DIR__ . '/../Util/FellowshipApplication-f1d9f98353e5.p12';
        $credentialsJsonFile = $userSecUtil->getSiteSettingParameter('p12KeyPathFellApp');
        if( !$credentialsJsonFile ) {
            $logger->warning('$credentialsJsonFile is not defined in Site Parameters. $credentialsJsonFile='.$credentialsJsonFile);
        }

        $scopes = $userSecUtil->getSiteSettingParameter('googleDriveApiUrlFellApp');
        if( !$scopes ) {
            throw new \InvalidArgumentException('Google scope is not defined in Site Parameters.');
        }

        $client = new \Google_Client();
        $client->setApplicationName('Fellowship Applications');
        //$client->setScopes(\Google_Service_PeopleService::CONTACTS_READONLY);
        $client->setAuthConfig($credentialsJsonFile);
        //$client->setAccessType('offline');
        //$client->setPrompt('select_account consent');

        //$client->setDeveloperKey("AIzaSyBlJc1rS1mBLXD5sYEEOwBvSB1NhUwJ-rI");

        //$client->addScope($scopes);
        $client->addScope("https://www.googleapis.com/auth/drive");
        //$client->addScope(\Google_Service_Drive::DRIVE_METADATA_READONLY);

        $user_to_impersonate = $userSecUtil->getSiteSettingParameter('userImpersonateEmailFellApp');
        if( !$user_to_impersonate ) {
            throw new \InvalidArgumentException('userImpersonateEmailFellApp is not defined in Site Parameters.');
        }

        $client->setSubject($user_to_impersonate);

        // Load previously authorized token from a file, if it exists.
        // The file token.json stores the user's access and refresh tokens, and is
        // created automatically when the authorization flow completes for the first
        // time.
        $tokenPath = 'token.json';
        if (file_exists($tokenPath)) {
            $accessToken = json_decode(file_get_contents($tokenPath), true);
            $client->setAccessToken($accessToken);
        }

        // If there is no previous token or it's expired.
        if(0) {
            if ($client->isAccessTokenExpired()) {
                // Refresh the token if possible, else fetch a new one.
                if ($client->getRefreshToken()) {
                    $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
                } else {
                    // Request authorization from the user.
                    $authUrl = $client->createAuthUrl();
                    printf("Open the following link in your browser:\n%s\n", $authUrl);
                    print 'Enter verification code: ';
                    $authCode = trim(fgets(STDIN));
                    $authCode = "4/iAHGyavRVejz5AyTchoSwNXMkOSVOpHIdI0f4a-wqJedC1AZNN-GYgI";

                    // Exchange authorization code for an access token.
                    $accessToken = $client->fetchAccessTokenWithAuthCode($authCode);
                    $client->setAccessToken($accessToken);

                    // Check to see if there was an error.
                    if (array_key_exists('error', $accessToken)) {
                        throw new Exception(join(', ', $accessToken));
                    }
                }
                // Save the token to a file.
                if (!file_exists(dirname($tokenPath))) {
                    mkdir(dirname($tokenPath), 0700, true);
                }
                file_put_contents($tokenPath, json_encode($client->getAccessToken()));
            }
        }

        return $client;
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
        $logger = $this->container->get('logger');
        if( $type && ($type == 'Fellowship Application Spreadsheet' || $type == 'Fellowship Application Backup Spreadsheet' || $type == 'Fellowship Recommendation Letter Spreadsheet') ) {
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
                //$logger->notice("download file: response=".$httpRequest->getResponseHttpCode()."; file id=".$file->getId()."; type=".$type);
                //$logger->notice("getResponseBody=".$httpRequest->getResponseBody());
                return $httpRequest->getResponseBody();
            } else {
                // An error occurred.
                $logger->error("Error download file: invalid response =".$httpRequest->getResponseHttpCode());
                return null;
            }
        } else {
            // The file doesn't have any content stored on Drive.
            $logger->error("Error download file: downloadUrl is null=".$downloadUrl);
            return null;
        }
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

            print "Title: " . $file->getTitle();
            print "; Description: " . $file->getDescription();
            print "; MIME type: " . $file->getMimeType();
            print "<br>";
        } catch (Exception $e) {
            print "An error occurred: " . $e->getMessage();
        }
    }






    //1)  Import sheets from Google Drive
    //1a)   import all sheets from Google Drive folder
    //1b)   add successefull downloaded sheets to DataFile DB object with status "active"
    public function getConfigOnGoogleDrive() {

        if( $this->container->get('security.authorization_checker')->isGranted('ROLE_FELLAPP_ADMIN') === false ) {
            //return $this->redirect( $this->generateUrl('fellapp-nopermission') );
            return NULL;
        }

        $logger = $this->container->get('logger');
        $userSecUtil = $this->container->get('user_security_utility');
        //$systemUser = $userSecUtil->findSystemUser();

        //get Google service
        $googlesheetmanagement = $this->container->get('fellapp_googlesheetmanagement');
        $service = $googlesheetmanagement->getGoogleService();

        if( !$service ) {
            $event = "Google API service failed!";
            exit($event);
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
        //echo "folder ID=".$configFileFolderIdFellApp."<br>";

        if( 1 ) {
            $configFile = $this->findConfigFileInFolder($service, $configFileFolderIdFellApp, "config.json");
            $contentConfigFile = $this->downloadGeneralFile($service, $configFile);

            //$contentConfigFile = str_replace(",",", ",$contentConfigFile);
//            //echo $content;
//            echo "<pre>";
//            print_r($contentConfigFile);
//            echo "</pre>";

            return $contentConfigFile;

//            $response = new Response();
//            $response->headers->set('Content-Type', 'application/json');
//            $response->setContent(json_encode($content));
            //echo $response;

            //exit();

            //return $configFile;
            //exit('111');
        } else {
            //get all files in google folder
            //ID=0B2FwyaXvFk1efmlPOEl6WWItcnBveVlDWWh6RTJxYzYyMlY2MjRSalRvUjdjdzMycmo5U3M
            //$parameters = array('q' => "'".$configFileFolderIdFellApp."' in parents and trashed=false and name contains 'config.json'");
            //$parameters = array('q' => "'".$configFileFolderIdFellApp."' in parents and trashed=false");
            $parameters = array('q' => "'" . $configFileFolderIdFellApp . "' in parents and trashed=false and title='config.json'");
            $files = $service->files->listFiles($parameters);

            foreach ($files->getItems() as $file) {
                echo "file=" . $file->getId() . "<br>";
                echo "File Title=" . $file->getTitle() . "<br>";
            }

            return $file;
        }


        return NULL;
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
                $parameters = array('q' => "'".$folderId."' in parents and trashed=false and title='".$fileName."'");
                $files = $service->files->listFiles($parameters);

                foreach ($files->getItems() as $file) {
                    //echo "File ID=" . $file->getId()."<br>";
                    //echo "File Title=" . $file->getTitle()."<br>";

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

    /**
     * Download a file's content.
     *
     * @param Google_Service_Drive $service Drive API service instance.
     * @param File $file Drive File instance.
     * @return String The file's content if successful, null otherwise.
     */
    function downloadGeneralFile($service, $file) {
        $downloadUrl = $file->getDownloadUrl();
        if ($downloadUrl) {
            $request = new \Google_Http_Request($downloadUrl, 'GET', null, null);
            $httpRequest = $service->getClient()->getAuth()->authenticatedRequest($request);
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