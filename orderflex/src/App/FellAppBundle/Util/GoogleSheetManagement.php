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

use App\FellAppBundle\Entity\GoogleFormConfig;
use Doctrine\ORM\EntityManagerInterface;
use Google\Client;
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

class GoogleSheetManagement {

    protected $em;
    protected $container;
    protected $security;

    public function __construct( EntityManagerInterface $em, ContainerInterface $container, Security $security ) {
        $this->em = $em;
        $this->container = $container;
        $this->security = $security;
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

        if( !$service ) {
            $event = "Google API service failed!";
            //exit($event);
            $logger->warning("deleteRowInListFeed: ".$event);
            return false;
        }

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

                if( strpos((string)$cellValue, $fileStrFlag) !== false ) {
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
                $parameters = array(
                    'q' => "'".$folderId."' in parents and trashed=false",
                    'fields' => 'nextPageToken, files(*)'
                );
                if ($pageToken) {
                    $parameters['pageToken'] = $pageToken;
                }
                $files = $service->files->listFiles($parameters);

                $result = array_merge($result, $files->getFiles());
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

        //$fileId = '1EEZ85D4sNeffSLb35_72qi8TdjD9nLyJ'; //testing

        $file = null;
        try {
            $file = $service->files->get($fileId);
        } catch (Exception $e) {
            throw new IOException('Google API: Unable to get file by file id='.$fileId.". An error occurred: " . $e->getMessage());
        }

        if( $file ) {

            $documentType = trim((string)$documentType);

            //check if file already exists by file id
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:Document'] by [Document::class]
            $documentDb = $this->em->getRepository(Document::class)->findOneByUniqueid($file->getId());
            if( $documentDb && $documentType != 'Fellowship Application Backup Spreadsheet' ) {
                //$event = "Document already exists with uniqueid=".$file->getId()."; fileId=".$fileId;
                //$logger->notice($event);
                return $documentDb;
            }

            //$logger->notice("Attempt to download file from Google drive file id=".$fileId."; title=".$file->getTitle());
            $googlesheetmanagement = $this->container->get('fellapp_googlesheetmanagement');
            $response = $googlesheetmanagement->downloadFile($service, $file, $documentType);

            //echo "fileId=".$file->getId()."<br>";
            //$response = $this->downloadGeneralFile($service, $file);
            //dump($response);
            //exit('111');

            //echo "response=".$response."<br>";
            if( !$response ) {
                throw new IOException('Error application file response is empty: file id='.$fileId."; documentType=".$documentType);
            }

            //exit('file ok');

            //create unique file name
            $currentDatetime = new \DateTime();
            $currentDatetimeTimestamp = $currentDatetime->getTimestamp();

            //$fileTitle = trim((string)$file->getTitle());
            //$fileTitle = str_replace(" ","",$fileTitle);
            //$fileTitle = str_replace("-","_",$fileTitle);
            //$fileTitle = 'testfile.jpg';
            $fileExt = pathinfo($file->getName(), PATHINFO_EXTENSION);
            $fileExtStr = "";
            if( $fileExt ) {
                $fileExtStr = ".".$fileExt;
            }

            $fileUniqueName = $currentDatetimeTimestamp.'ID'.$file->getId().$fileExtStr;  //.'_title='.$fileTitle;
            //echo "fileUniqueName=".$fileUniqueName."<br>";

            //$filesize = $file->getFileSize();
            $filesize = $file->getSize();
            if( !$filesize ) {
                $filesize = mb_strlen((string)$response) / 1024; //KBs,
            }

            //exit('file ok: filesize='.$filesize);

            $object = new Document($author);
            $object->setUniqueid($file->getId());
            $object->setUniquename($fileUniqueName);
            $object->setUploadDirectory($path);
            $object->setSize($filesize);

            //TODO: use $file->getCreatedTime for creation date? (https://developers.google.com/drive/api/v3/reference/files#createdTime)
            //$file->getCreatedTime is available only in 2.0 google/apiclient
            //https://developers.google.com/resources/api-libraries/documentation/drive/v3/php/latest/class-Google_Service_Drive_DriveFile.html

            //clean originalname
            $object->setCleanOriginalname($file->getName());

//            if( $type && $type == 'excel' ) {
//                $fellappSpreadsheetType = $this->em->getRepository('AppUserdirectoryBundle:DocumentTypeList')->findOneByName('Fellowship Application Spreadsheet');
//            } else {
//                $fellappSpreadsheetType = $this->em->getRepository('AppUserdirectoryBundle:DocumentTypeList')->findOneByName('Fellowship Application Document');
//            }
            $transformer = new GenericTreeTransformer($this->em, $author, "DocumentTypeList", "UserdirectoryBundle");
            //$documentType = trim((string)$documentType);
            $documentTypeObject = $transformer->reverseTransform($documentType);
            if( $documentTypeObject ) {
                $object->setType($documentTypeObject);
            }

            $this->em->persist($object);

            //$root = $this->container->get('kernel')->getRootDir();
            //echo "root=".$root."<br>";
            //$fullpath = $this->get('kernel')->getRootDir() . '/../web/'.$path;
            //$fullpath = $root . '/../public/'.$path;
            $fullpath = $this->container->get('kernel')->getProjectDir() . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . $path;

            $target_file = $fullpath . DIRECTORY_SEPARATOR . $fileUniqueName;

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


//    /**
//     * Search for folder by the parent folder ID and folder Name.
//     */
//    function findFolderByFolderNameAndParentFolder_OLD($service,$parentFolderId,$folderName) {
//        $result = array();
//        $pageToken = NULL;
//
//        do {
//            try {
//
//                $parameters = array('q' => "'" . $parentFolderId . "' in parents and trashed=false and name='config.json'");
//                $files = $service->files->listFiles($parameters);
//
//                foreach ($files->getFiles() as $file) {
//                    echo "file=" . $file->getId() . "<br>";
//                    echo "File Title=" . $file->getName() . "<br>";
//                }
//
//                return $file;
//
//
//                //files.list?q=mimetype=application/vnd.google-apps.folder and trashed=false&fields=parents,name
//                $parameters = array('q' => "mimetype=application/vnd.google-apps.folder and '".$parentFolderId."' in parents and trashed=false and name='".$folderName."'");
//                $parameters = array('q' => "'" . $parentFolderId . "' in parents and trashed=false and name='config.json'");
//                if ($pageToken) {
//                    $parameters['pageToken'] = $pageToken;
//                }
//                $folders = $service->files->listFiles($parameters);
//                foreach ($folders->getFiles() as $folder) {
//                    echo "file=" . $folder->getId() . "<br>";
//                    echo "File Title=" . $folder->getName() . "<br>";
//                    $result = $folder;
//                }
//
//                //$result = array_merge($result, $folders->getFiles());
//
//                $pageToken = $folders->getNextPageToken();
//            } catch (Exception $e) {
//                //print "An error occurred: " . $e->getMessage();
//                $pageToken = NULL;
//            }
//        } while ($pageToken);
//        return $result;
//    }
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
                ////$parameters = array('q' => "mimeType='application/vnd.google-apps.folder' and trashed=false and title='".$fileName."'");
                //$parameters = array('q' => "mimeType='application/vnd.google-apps.folder' and trashed=false and '".$folderId."' in parents and name='".$fileName."'");

                //https://github.com/googleapis/google-api-php-client/issues/1257
                $parameters = array(
                    'q' => "'" . $folderId . "' in parents and trashed=false and name='".$fileName."'",
                    //'fields' => 'nextPageToken, files(id, name, size, webViewLink, mimeType)'
                    'fields' => 'nextPageToken, files(*)'
                    //'fields' => 'files(*)'
                );
                //$parameters = array('q' => "'" . $folderId . "' in parents and trashed=false and name='config.json'");
                $files = $service->files->listFiles($parameters);
                echo "files count=".count($files)."<br>";

                foreach ($files->getFiles() as $file) {
                    echo "File ID=" . $file->getId()."<br>";
                    echo "File Title=" . $file->getName()."<br>";
                    echo "File Size=" . $file->getSize()."<br>";

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
        if( $res ) {
            return $res['service'];
        }

        return NULL;
    }

    public function authenticationGoogle() {
        //return $this->authenticationP12Key();
        //return $this->authenticationGoogleOAuth();
        return $this->authGoogleServiceAccount();
    }

    //https://github.com/googleapis/google-api-php-client/blob/master/UPGRADING.md
//    public function authenticationGoogleV2() {
//        // OR use environment variables (recommended)
//        putenv('GOOGLE_APPLICATION_CREDENTIALS=/path/to/service-account.json');
//        $client = $this->getClient();
//        $client->useApplicationDefaultCredentials();
//    }

    //Probably, it's better to use Server to Server authentication by using P12 key
    //Depreciated in google/apiclient v2.0 https://github.com/googleapis/google-api-php-client/blob/master/UPGRADING.mds
    //Using OAuth 2.0 for Server to Server Applications: using PKCS12 certificate file
    //Security page: https://admin.google.com/pathologysystems.org/AdminHome?fral=1#SecuritySettings:
    //Credentials page: https://console.developers.google.com/apis/credentials?project=turnkey-delight-103315&authuser=1
    //https://developers.google.com/api-client-library/php/auth/service-accounts
    //1) Create a service account by Google Developers Console.
    //2) Delegate domain-wide authority to the service account.
    //3) Impersonate a user account.
    //Deprecated. Use JSON
    public function authenticationP12Key() {

        $logger = $this->container->get('logger');
        $userSecUtil = $this->container->get('user_security_utility');

        //$client_email = '1040591934373-1sjcosdt66bmani0kdrr5qmc5fibmvk5@developer.gserviceaccount.com';
        $client_email = $userSecUtil->getSiteSettingParameter('clientEmailFellApp'); //Deprecated. Use JSON

        //$pkey = __DIR__ . '/../Util/FellowshipApplication-f1d9f98353e5.p12';
        $pkey = $userSecUtil->getSiteSettingParameter('p12KeyPathFellApp');
        if( !$pkey ) {
            $logger->warning('p12KeyPathFellApp/credentials.json is not defined in Site Parameters. File='.$pkey);
        }

        //dump($pkey);
        //exit('$pkey exit');

        if( is_file($pkey) == false ) {
            return false;
        }

        //$user_to_impersonate = 'olegivanov@pathologysystems.org';
        $user_to_impersonate = $userSecUtil->getSiteSettingParameter('userImpersonateEmailFellApp');

        //echo "pkey=".$pkey."<br>";
        $private_key = file_get_contents($pkey); //notasecret

        //$googleDriveApiUrlFellApp = $userSecUtil->getSiteSettingParameter('googleDriveApiUrlFellApp');
        $googleDriveApiUrlFellApp = $userSecUtil->getSiteSettingParameter('googleDriveApiUrlFellApp',$this->container->getParameter('fellapp.sitename'));
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
//    //Authentication based on "google/apiclient": "v2.2.3" and credentials.json
//    //https://developers.google.com/people/quickstart/php
//    public function authenticationGoogleOAuth() {
//
//        $logger = $this->container->get('logger');
//        $userSecUtil = $this->container->get('user_security_utility');
//
//        //$client_email = '1040591934373-1sjcosdt66bmani0kdrr5qmc5fibmvk5@developer.gserviceaccount.com';
//        //$client_email = $userSecUtil->getSiteSettingParameter('clientEmailFellApp');
//
//        //$pkey = __DIR__ . '/../Util/FellowshipApplication-f1d9f98353e5.p12';
//        //$pkey = $userSecUtil->getSiteSettingParameter('p12KeyPathFellApp');
//        $pkey = $userSecUtil->getSiteSettingParameter('authPathFellApp',$this->getParameter('fellapp.sitename'));
//        if( !$pkey ) {
//            $logger->warning('authPathFellApp is not defined in Fellowship Site Parameters. File='.$pkey);
//        }
//
//        //$user_to_impersonate = 'olegivanov@pathologysystems.org';
////        $user_to_impersonate = $userSecUtil->getSiteSettingParameter('userImpersonateEmailFellApp');
//
//        //echo "pkey=".$pkey."<br>";
////        $private_key = file_get_contents($pkey); //notasecret
//
////        $googleDriveApiUrlFellApp = $userSecUtil->getSiteSettingParameter('googleDriveApiUrlFellApp');
////        if( !$googleDriveApiUrlFellApp ) {
////            throw new \InvalidArgumentException('googleDriveApiUrlFellApp is not defined in Site Parameters.');
////        }
//
//        //PHP API scopes: https://developers.google.com/api-client-library/php/auth/service-accounts#creatinganaccount
//        //set scopes on developer console:
//        // https://admin.google.com/pathologysystems.org/AdminHome?chromeless=1#OGX:ManageOauthClients
//        //scopes: example: "https://spreadsheets.google.com/feeds https://docs.google.com/feeds"
//        //$scopes = array($googleDriveApiUrlFellApp); //'https://www.googleapis.com/auth/drive'
////        $scopes = $googleDriveApiUrlFellApp;
//
//        $client = $this->getClient();
//
//        //$service = null;
//        $service = new \Google_Service_Drive($client);
//
//        //try {
////            $client = $this->getClient();
////            $service = new \Google_Service_Drive($client);
////        } catch(Exception $e) {
////            $subject = "Failed to authenticate to Google";
////            $event = "Failed to authenticate to Google: " . $e->getMessage();
////            $logger->error($event);
////            //$userSecUtil->createUserEditEvent($this->container->getParameter('fellapp.sitename'),$event,$systemUser,null,null,'Error');
////            $userSecUtil->sendEmailToSystemEmail($subject, $event);
////        }
//
//        $res = array(
//            'client' => $client,
//            'service' => $service
//        );
//
//        return $res;
//    }

    public function authGoogleServiceAccount() {
        $logger = $this->container->get('logger');
        $userSecUtil = $this->container->get('user_security_utility');

        //$credentialsJsonFile = $userSecUtil->getSiteSettingParameter('p12KeyPathFellApp');
        $credentialsJsonFile = $userSecUtil->getSiteSettingParameter('authPathFellApp',$this->container->getParameter('fellapp.sitename'));
        if( !$credentialsJsonFile ) {
            $logger->warning('JSON service key is not defined in Fellowship Site Parameters, in field authPathFellApp. Json file='.$credentialsJsonFile);
            //exit('authPathFellApp is not set');
            $res = array(
                'client' => null,
                'service' => null
            );

            return $res;
        }
        //dump($credentialsJsonFile);
        //exit('111');

        if( file_exists($credentialsJsonFile) == false ) {
            $logger->warning('JSON service key does not exits. Json file='.$credentialsJsonFile);
            //exit('authPathFellApp is not set');
            $res = array(
                'client' => null,
                'service' => null
            );

            return $res;
        }

        //$scopes = $userSecUtil->getSiteSettingParameter('googleDriveApiUrlFellApp');
        $scopes = $userSecUtil->getSiteSettingParameter('googleDriveApiUrlFellApp',$this->container->getParameter('fellapp.sitename'));
        if( !$scopes ) {
            $logger->warning('Google scope are not defined in Fellowship Site Parameters, in field googleDriveApiUrlFellApp');
        }

        //space or comma separated
        $scopesArr = array();
        if( $scopes && str_contains($scopes,' ') ) {
            $scopesArr = explode(' ', $scopes);
        }
        if( $scopes && str_contains($scopes,',') ) {
            $scopesArr = explode(',', $scopes);
        }

        $client = new Client();
        $client->setAuthConfig($credentialsJsonFile);
        $client->addScope(Drive::DRIVE);

        if( count($scopesArr) > 0 ) {
            foreach( $scopesArr as $scope ) {
                $client->addScope($scope);
            }
        } else {
            if( $scopes ) {
                $client->addScope($scopes);
            }
        }

        //"error": "unauthorized_client",
        //"error_description": "Client is unauthorized to retrieve access tokens using this method, or client not authorized for any of the scopes requested."
//        $user_to_impersonate = $userSecUtil->getSiteSettingParameter('userImpersonateEmailFellApp');
//        if( $user_to_impersonate ) {
//            $client->setSubject($user_to_impersonate);
//        }

        //$client->addScope("https://www.googleapis.com/auth/drive");
        //$client->addScope("https://www.googleapis.com/auth/drive.file");
        //$client->addScope("https://www.googleapis.com/auth/drive.metadata");
        //$client->addScope("https://www.googleapis.com/auth/drive.appdata");
        //$client->addScope("https://spreadsheets.google.com/feeds");

        //Json and api key gives the same "File not found"
        //$client->setApplicationName("Fellowship Application 2");
        //$client->setDeveloperKey("");
        //$client->addScope("https://www.googleapis.com/auth/drive");
        //$client->setSubject("google-drive-service-account@ambient-highway-380513.iam.gserviceaccount.com");

        //$client->setSubject("1040591934373-c7rvicvmf22s0slqfejftmpmc9n1dqah@developer.gserviceaccount.com");
        //$client->setDeveloperKey('');
        //$driveService = new Drive($client);
        $service = new Drive($client);

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

        //$user_to_impersonate = $userSecUtil->getSiteSettingParameter('userImpersonateEmailFellApp');
        //if( !$user_to_impersonate ) {
        //    throw new \InvalidArgumentException('userImpersonateEmailFellApp is not defined in Site Parameters.');
        //}

        //$credentialsJsonFile = $userSecUtil->getSiteSettingParameter('p12KeyPathFellApp');
        $credentialsJsonFile = $userSecUtil->getSiteSettingParameter('authPathFellApp',$this->container->getParameter('fellapp.sitename'));
        if( !$credentialsJsonFile ) {
            $logger->warning('authPathFellApp is not defined in Fellowship Site Parameters. $credentialsJsonFile='.$credentialsJsonFile);
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
//            'private_key' => ''
//        ));

        $client->setAuthConfig($credentialsJsonFile);

        return $client;
    }
//    /**
//     * https://github.com/googleapis/google-api-php-client
//     * https://github.com/googleapis/google-api-php-client/blob/master/UPGRADING.md
//     *
//     * Returns an authorized API client.
//     * @return Google_Client the authorized client object
//     */
//    function getClient2() {
//
//        $logger = $this->container->get('logger');
//        $userSecUtil = $this->container->get('user_security_utility');
//
//        //$client_email = '1040591934373-1sjcosdt66bmani0kdrr5qmc5fibmvk5@developer.gserviceaccount.com';
//        //$client_email = $userSecUtil->getSiteSettingParameter('clientEmailFellApp');
//
//        //$pkey = __DIR__ . '/../Util/FellowshipApplication-f1d9f98353e5.p12';
//        $credentialsJsonFile = $userSecUtil->getSiteSettingParameter('p12KeyPathFellApp');
//        if( !$credentialsJsonFile ) {
//            $logger->warning('$credentialsJsonFile is not defined in Site Parameters. $credentialsJsonFile='.$credentialsJsonFile);
//        }
//
//        $scopes = $userSecUtil->getSiteSettingParameter('googleDriveApiUrlFellApp');
//        if( !$scopes ) {
//            throw new \InvalidArgumentException('Google scope is not defined in Site Parameters.');
//        }
//
//        $client = new \Google_Client();
//        $client->setApplicationName('Fellowship Applications');
//        //$client->setScopes(\Google_Service_PeopleService::CONTACTS_READONLY);
//        $client->setAuthConfig($credentialsJsonFile);
//        //$client->setAccessType('offline');
//        //$client->setPrompt('select_account consent');
//
//        //$client->setDeveloperKey("xxxxxxxxxxxx");
//
//        //$client->addScope($scopes);
//        $client->addScope("https://www.googleapis.com/auth/drive");
//        //$client->addScope(\Google_Service_Drive::DRIVE_METADATA_READONLY);
//
//        $user_to_impersonate = $userSecUtil->getSiteSettingParameter('userImpersonateEmailFellApp');
//        if( !$user_to_impersonate ) {
//            throw new \InvalidArgumentException('userImpersonateEmailFellApp is not defined in Site Parameters.');
//        }
//
//        $client->setSubject($user_to_impersonate);
//
//        // Load previously authorized token from a file, if it exists.
//        // The file token.json stores the user's access and refresh tokens, and is
//        // created automatically when the authorization flow completes for the first
//        // time.
//        $tokenPath = 'token.json';
//        if (file_exists($tokenPath)) {
//            $accessToken = json_decode(file_get_contents($tokenPath), true);
//            $client->setAccessToken($accessToken);
//        }
//
//        // If there is no previous token or it's expired.
//        if(0) {
//            if ($client->isAccessTokenExpired()) {
//                // Refresh the token if possible, else fetch a new one.
//                if ($client->getRefreshToken()) {
//                    $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
//                } else {
//                    // Request authorization from the user.
//                    $authUrl = $client->createAuthUrl();
//                    printf("Open the following link in your browser:\n%s\n", $authUrl);
//                    print 'Enter verification code: ';
//                    $authCode = trim(fgets(STDIN));
//                    $authCode = "xxxxxxxxxxxxxxx";
//
//                    // Exchange authorization code for an access token.
//                    $accessToken = $client->fetchAccessTokenWithAuthCode($authCode);
//                    $client->setAccessToken($accessToken);
//
//                    // Check to see if there was an error.
//                    if (array_key_exists('error', $accessToken)) {
//                        throw new Exception(join(', ', $accessToken));
//                    }
//                }
//                // Save the token to a file.
//                if (!file_exists(dirname($tokenPath))) {
//                    mkdir(dirname($tokenPath), 0700, true);
//                }
//                file_put_contents($tokenPath, json_encode($client->getAccessToken()));
//            }
//        }
//
//        return $client;
//    }


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
                $mimeType,
                array(
                    'alt' => 'media'
                )
            );
            
            $content = $response->getBody()->getContents();
            return $content;
        }  catch(Exception $e) {
            //echo "Error Message: ".$e;
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
                    'alt' => 'media',
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

    function onDownloadFileError( $subject, $body, $sendEmail=true ) {
        $logger = $this->container->get('logger');

        if( !$sendEmail ) {
            $logger->error("Skipped to send error eventlog and email: ".$subject);
        }

        ////////////////// ERROR //////////////////
        //$logger->error("Subject: ".$subject);
        //$logger->error("Body: ".$body);

        //Create error notification email
        //$subject = "ERROR: can not download $type file for Fellowship Application";
        //$body = "Error downloading $type file: invalid response=".$httpRequest->getResponseHttpCode().
        //    "; downloadUrl=".$downloadUrl."; fileId=".$fileId;

        $userSecUtil = $this->container->get('user_security_utility');
        $systemUser = $userSecUtil->findSystemUser();

        //$sendEmail = true;
        //$sendEmail = false; //testing
        if( $sendEmail ) {

            $userSecUtil->sendEmailToSystemEmail($subject, $body);

            //Send email to admins
            $emails = $userSecUtil->getUserEmailsByRole($this->container->getParameter('fellapp.sitename'), "Platform Administrator");
            $ccs = $userSecUtil->getUserEmailsByRole($this->container->getParameter('fellapp.sitename'), "Administrator");
            if (!$emails) {
                $emails = $ccs;
                $ccs = null;
            }
            $emailUtil = $this->container->get('user_mailer_utility');
            $emailUtil->sendEmail($emails, $subject, $body, $ccs);

            $userSecUtil->createUserEditEvent($this->container->getParameter('fellapp.sitename'),$body,$systemUser,null,null,'Error');
        } else {
            $logger->error("Skipped to send error eventlog and email: ".$subject);
        }
        ////////////////// EOF ERROR //////////////////
    }

    function testFileDownload() {
        //Test files are located in FellowshipApplication/TestFiles
        $files = array(
            "17PwcM0qPAAz8KcitIBayMzTj6XW8GSsu", //"1ohvKGunEsvSowwpozfjvjtyesN0iUeF2"; //Word

            ////"1Bkz0jkDWn8ymagMf6EPZQZ2Nyf18kaPXI2aqKm_eX-U", //"1is-0L26e_W76hL-UfAuuZEEo8p9ycnwnn02hZ9lzFek"; //PDF
            "1maBuBYjB_xEiQi8lqtNDzUhQwEDrFi_o", //PDF

            "1fd-vjpmQKdVXDiAhEzcP-5fFDZEl2kKW67nrRrtfcWg", //"17inHCzyZNyZ98E_ZngUjkUKWNp3D2J8Ri2TZWR5Oi1k"; //Google Docs
            "1NwCFOUZ6oTyiehtSzPuxuddsnxbqgPeUCn516eEW05o", //"1beJAujYBEwPdi3RI7YAb4a8NcrBj5l0vhY6Zsa01Ohg"; //Google Sheets

            //"1imVshtA63nsr5oQOyW3cWXzXV_zhjHtyCwTKgjR8MAM", //Image 1b_tL1MDsS6fCysBcP6X7MjhdS9jryiYf
            "1pg88L0cf8Lgv1bsLaAdJGqAZewYgHzVJ", //Image

            //"1EEZ85D4sNeffSLb35_72qi8TdjD9nLyJ" //JSON config file
            "1Jeq07UgOb6i1TKqZEXzM4Rdeu69m_0Li" //Copy of config-fellapp.json JSON config file located in FellowshipApplication
        );

//        $files1 = array(
//            //"17PwcM0qPAAz8KcitIBayMzTj6XW8GSsu", //"1ohvKGunEsvSowwpozfjvjtyesN0iUeF2"; //Word
//
//            //"1Bkz0jkDWn8ymagMf6EPZQZ2Nyf18kaPXI2aqKm_eX-U", //"1is-0L26e_W76hL-UfAuuZEEo8p9ycnwnn02hZ9lzFek"; //PDF
//            //"1maBuBYjB_xEiQi8lqtNDzUhQwEDrFi_o", //PDF
//
//            //"1fd-vjpmQKdVXDiAhEzcP-5fFDZEl2kKW67nrRrtfcWg", //"17inHCzyZNyZ98E_ZngUjkUKWNp3D2J8Ri2TZWR5Oi1k"; //Google Docs
//            //"1NwCFOUZ6oTyiehtSzPuxuddsnxbqgPeUCn516eEW05o", //"1beJAujYBEwPdi3RI7YAb4a8NcrBj5l0vhY6Zsa01Ohg"; //Google Sheets
//            //"1imVshtA63nsr5oQOyW3cWXzXV_zhjHtyCwTKgjR8MAM", //Image 1b_tL1MDsS6fCysBcP6X7MjhdS9jryiYf
//            "1pg88L0cf8Lgv1bsLaAdJGqAZewYgHzVJ" //Image
//        );

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

    /**
     * Download a file's content.
     *
     * @param Google_Servie_Drive $service Drive API service instance.
     * @param Google_Servie_Drive_DriveFile $file Drive File instance.
     * @param String $type Document type string.
     * @return String The file's content if successful, null otherwise.
     */
    function downloadFile_OLD($service, $file, $type=null) {

//        $response = $this->downloadGeneralFile($service,$file);
//        dump($response);
//        exit('df');
//        return $response;

        ////////////// testing //////////////
        //$fileId = $file->getId();
        //$content = $service->files->get($fileId, array(
        //    'alt' => 'media' ));
        //$content = $service->files->get($fileId);
        //dump($content);
        //$link = $content['exportLinks'];
        //$exportLinks = $link['text/csv'];
        //echo "exportLinks=".$exportLinks."<br>";
        //$request = new \Google_Http_Request($exportLinks, 'GET', null, null);
        //$httpRequest = $service->getClient()->getAuth()->authenticatedRequest($request);
        //return $httpRequest->getResponseBody();
        //dump($httpRequest->getResponseBody());
        //exit('333');
        //return $content;
        ////////////// EOF testing //////////////

        $logger = $this->container->get('logger');

        $fileId = $file->getId();
        $fileName = $file->getName();

        $mimeType = $file->getMimeType();
        $logger->notice("mimeType=".$mimeType);
        //https://developers.google.com/drive/api/guides/ref-export-formats
        //https://developers.google.com/drive/api/guides/mime-types
        //Google Docs: mimeType=application/vnd.google-apps.document
        //Word: application/msword
        //Microsoft Word: mimeType=application/vnd.openxmlformats-officedocument.wordprocessingml.document
        //PDF: mimeType=application/pdf
        //Google Sheets: mimeType=application/vnd.google-apps.spreadsheet
        //$fileType = $file->getFileType($mimeType);
        //$logger->notice("fileType=".$fileType);

        if( $type && (  $type == 'Fellowship Application Spreadsheet' ||
                        $type == 'Fellowship Application Backup Spreadsheet' ||
                        $type == 'Fellowship Recommendation Letter Spreadsheet'
                     )
        ) {
            //$downloadUrl = $file->getExportLinks()['text/csv'];

            //$exportLinks = $file->getExportLinks();
            //$downloadUrl = $exportLinks['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'];

            //exportLink does not work anymore (since ~26 June 2020) for CSV files. The body has 307 Temporary Redirect: The document has moved here.
            //Therefore, use api file export HTTP request: https://developers.google.com/drive/api/v3/reference/files/export
            //$fileId = $file->getId();

            //$downloadUrl = 'https://www.googleapis.com/drive/v3/files/'.$fileId.'/export?mimeType=text/csv';
            $downloadUrl = 'https://www.googleapis.com/drive/v2/files/'.$fileId.'/export?mimeType=text/csv';

        }
        elseif( $mimeType == 'application/vnd.google-apps.document' ) {
            //"message": "The requested conversion is not supported."
            //$response = $this->downloadGeneralFileGoogleDoc($service,$file);
            //dump($response);
            //exit("testing...");
            //$logger->notice("downloadGeneralFileGoogleDoc response=".$response);
            //return $response;
            //$downloadUrl = 'https://www.googleapis.com/drive/v2/files/'.$fileId.'/export?mimeType=application/vnd.google-apps.document';
            $downloadUrl = 'https://www.googleapis.com/drive/v2/files/'.$fileId.'?source=downloadUrl&mimeType=application/vnd.google-apps.document';
            //$logger->notice("Skipped: application/vnd.google-apps.document");
            //return null;
        }
        elseif( $mimeType == 'application/msword' ) {
            $downloadUrl = 'https://www.googleapis.com/drive/v2/files/'.$fileId.'/export?mimeType=application/msword';
        }
        else {
            //$downloadUrl = $file->getDownloadUrl();
            $downloadUrl = 'https://www.googleapis.com/drive/v2/files/'.$fileId.'?alt=media&source=downloadUrl'; //working
        }

        $body = "Logical error downloading file (ID $fileId)";

        //testing
        //$downloadUrl = null;

//        echo "downloadUrl=".$downloadUrl."<br>";
//        $request = new \Google_Http_Request($downloadUrl, 'GET', null, null);
//        $httpRequest = $service->getClient()->getAuth()->authenticatedRequest($request);
//        echo "res code=".$httpRequest->getResponseHttpCode()."<br>";
//        dump($httpRequest->getResponseBody());
//        exit('testing response body');

        if ($downloadUrl) {
            //Use downloadGeneralFile()?
            $request = new \Google_Http_Request($downloadUrl, 'GET', null, null);
            $httpRequest = $service->getClient()->getAuth()->authenticatedRequest($request);
            //echo "res code=".$httpRequest->getResponseHttpCode()."<br>";

            //dump($httpRequest->getResponseBody());
            //exit('testing response body');

            $logger->notice("getResponseHttpCode=".$httpRequest->getResponseHttpCode());

            if( $httpRequest->getResponseHttpCode() == 200 ) {

                //dump($httpRequest->getResponseBody());
                //exit('testing response body');

                //$logger->notice("download file: response=".$httpRequest->getResponseHttpCode()."; file id=".$file->getId()."; type=".$type);
                $logger->notice("getResponseBody=".$httpRequest->getResponseBody());
                return $httpRequest->getResponseBody();
            } else {
                // An error occurred.
                //TODO: test why: 307 Temporary Redirect: The document has moved here
                //https://github.com/googleapis/google-api-php-client/issues/102
                //https://stackoverflow.com/questions/39340374/php-google-drive-api-http-response
                //return $httpRequest->getResponseBody(); //testing
                //exit("Error download file: invalid response =".$httpRequest->getResponseHttpCode());
                //Letter in World Doc format (Google Docs) => error:
                //getResponseBody={  "error": {   "errors": [    {     "domain": "global",     "reason": "fileNotDownloadable",
                //     "message": "Only files with binary content can be downloaded. Use Export with Docs Editors files.",
                //     "locationType": "parameter",     "location": "alt"    }   ],   "code": 403,
                //   "message": "Only files with binary content can be downloaded. Use Export with Docs Editors files."  } }  [] []
                $logger->error("getResponseBody=".$httpRequest->getResponseBody());

                $body = "Error downloading $type file (Name:$fileName, ID:$fileId): invalid response =".
                    $httpRequest->getResponseHttpCode().
                    "; downloadUrl=".$downloadUrl.
                    "; getResponseBody=".$httpRequest->getResponseBody()
                ;
                //return null;
            }
        } else {
            // The file doesn't have any content stored on Drive.
            $body = "Error downloading $type file (Name:$fileName, ID:$fileId): downloadUrl is null; downloadUrl=".$downloadUrl;
            //return null;
        }

        ////////////////// ERROR //////////////////
        $logger->error($body);

        //Create error notification email
        $subject = "ERROR: can not download $type file for Fellowship Application";
        //$body = "Error downloading $type file: invalid response=".$httpRequest->getResponseHttpCode().
        //    "; downloadUrl=".$downloadUrl."; fileId=".$fileId;

        $userSecUtil = $this->container->get('user_security_utility');
        $systemUser = $userSecUtil->findSystemUser();

        $sendEmail = true;
        $sendEmail = false; //testing
        if( $sendEmail ) {

            $userSecUtil->sendEmailToSystemEmail($subject, $body);

            //Send email to admins
            $emails = $userSecUtil->getUserEmailsByRole($this->container->getParameter('fellapp.sitename'), "Platform Administrator");
            $ccs = $userSecUtil->getUserEmailsByRole($this->container->getParameter('fellapp.sitename'), "Administrator");
            if (!$emails) {
                $emails = $ccs;
                $ccs = null;
            }
            $emailUtil = $this->container->get('user_mailer_utility');
            $emailUtil->sendEmail($emails, $subject, $body, $ccs);
        } else {
            $logger->error("Skipped to send error email: ".$subject);
        }

        $userSecUtil->createUserEditEvent($this->container->getParameter('fellapp.sitename'),$body,$systemUser,null,null,'Error');
        ////////////////// EOF ERROR //////////////////
        
        return null;
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

            print "Title: " . $file->getName();
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

        if( $this->security->isGranted('ROLE_FELLAPP_ADMIN') === false ) {
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
            //exit($event);
            $logger->warning("getConfigOnGoogleDrive: ".$event);
            return NULL;
        }

        //TODO: use configFileFolderIdFellApp to store unique config file name
//        $configFileFolderIdFellApp = $userSecUtil->getSiteSettingParameter('configFileFolderIdFellApp');
//        if( !$configFileFolderIdFellApp ) {
//            $logger->warning('Google Drive Folder ID with config file is not defined in Site Parameters. configFileFolderIdFellApp='.$configFileFolderIdFellApp);
//            return NULL;
//        }

        //$configFile = $this->findConfigFileInFolder($service, $configFileFolderIdFellApp, "config.json");

        //Use the unique config file name "config-fellapp.json" in GAS and in PHP
        $configFile = $this->findConfigFileByName($service, "config-fellapp.json");
        //echo "configFile ID=".$configFile->getId()."<br>";

        $contentConfigFile = $this->downloadGeneralFile($service, $configFile);

        return $contentConfigFile;
    }

    function findConfigFileByName($service, $fileName) {
        $pageToken = NULL;

        do {
            try {

                if ($pageToken) {
                    $parameters['pageToken'] = $pageToken;
                }

                //https://stackoverflow.com/questions/36605461/downloading-a-file-with-google-drive-api-with-php
                //The getItems() method in v2 will become getFiles() in v3 and the getTitle() will become getName()
                $parameters = array(
                    'q' => "trashed=false and name='".$fileName."'",
                    'fields' => 'nextPageToken, files(*)'
                );

                $files = $service->files->listFiles($parameters); //Google_Service_Drive_FileList

                //dump($files);
                //exit('111');

                if( count($files->getFiles()) > 1 ) {
                    $errorMsg = "Multiple ".count($files->getFiles())." config json files '".$fileName.
                        "' found in Google Drive. Please make sure only one config file with name '$fileName' exists";
                    exit($errorMsg);
                }

                foreach( $files->getFiles() as $file ) {
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

    /**
     * Find a file by file name and parent folder id
     *
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

//    /**
//     * Download a file's content.
//     *
//     * @param Google_Service_Drive $service Drive API service instance.
//     * @param File $file Drive File instance.
//     * @return String The file's content if successful, null otherwise.
//     */
//    function downloadGeneralFileV1($service, $file) {
//        $downloadUrl = $file->getDownloadUrl();
//        //$downloadUrl = $file->getWebContentLink();
//
//        //$metadata = $service->get($file->getId());
//
//        //exit("downloadUrl=".$downloadUrl);
//        if ($downloadUrl) {
//            $request = new \Google_Http_Request($downloadUrl, 'GET', null, null);
//            $httpRequest = $service->getClient()->getAuth()->authenticatedRequest($request);
//            if ($httpRequest->getResponseHttpCode() == 200) {
//                return $httpRequest->getResponseBody();
//            } else {
//                // An error occurred.
//                return null;
//            }
//        } else {
//            // The file doesn't have any content stored on Drive.
//            return null;
//        }
//    }

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

    function getGoogleConfigParameter( $parameterName ) {
        $configs = $this->em->getRepository(GoogleFormConfig::class)->findAll();

        $config = null;

        if( count($configs) == 0 ) {
            return null;
        }

        if( count($configs) > 1 ) {
            $logger = $this->container->get('logger');
            $msg = 'Must have only one Google config object. Found '.count($configs).' object(s).';
            $logger->error($msg);
            exit($msg);
            //throw new \Exception( 'Must have only one parameter object. Found '.count($params).' object(s)' );
        }

        if( count($configs) > 0 ) {
            $config = $configs[0];
        }

        if( $config == null ) {
            return null;
        }

        if( $parameterName == null ) {
            return $config;
        }

        $getSettingMethod = "get".$parameterName;

        return $config->$getSettingMethod();
    }

    

}

