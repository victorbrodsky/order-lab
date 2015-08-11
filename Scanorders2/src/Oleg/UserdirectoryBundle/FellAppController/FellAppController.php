<?php

namespace Oleg\UserdirectoryBundle\FellAppController;


use Oleg\UserdirectoryBundle\Entity\CurriculumVitae;
use Oleg\UserdirectoryBundle\Entity\Document;
use Oleg\UserdirectoryBundle\Entity\FellowshipApplication;
use Oleg\UserdirectoryBundle\Entity\FellowshipSubspecialty;
use Oleg\UserdirectoryBundle\Entity\Training;
use Oleg\UserdirectoryBundle\Entity\User;
use Oleg\UserdirectoryBundle\Util\UserUtil;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;



class FellAppController extends Controller {

    /**
     * Show home page
     *
     * @Route("/", name="fellapp_home")
     * @Template("OlegUserdirectoryBundle:FellApp:home.html.twig")
     */
    public function indexAction() {

        if(
            false == $this->get('security.context')->isGranted('ROLE_USER') ||              // authenticated (might be anonymous)
            false == $this->get('security.context')->isGranted('IS_AUTHENTICATED_FULLY')    // authenticated (NON anonymous)
        ){
            return $this->redirect( $this->generateUrl('login') );
        }

        //echo "fellapp home <br>";

        $em = $this->getDoctrine()->getManager();

        //$fellApps = $em->getRepository('OlegUserdirectoryBundle:FellowshipApplication')->findAll();
        $repository = $this->getDoctrine()->getRepository('OlegUserdirectoryBundle:FellowshipApplication');
        $dql =  $repository->createQueryBuilder("ent");
        $dql->select('ent');
        $dql->groupBy('ent');
        //$dql->leftJoin("ent.creator", "creator");

        $limit = 100;
        $query = $em->createQuery($dql);
        $paginator  = $this->get('knp_paginator');
        $fellApps = $paginator->paginate(
            $query,
            $this->get('request')->query->get('page', 1), /*page number*/
            $limit/*limit per page*/
        );


        return array(
            'entities' => $fellApps,
        );
    }


    /**
     * Show home page
     *
     * @Route("/populate", name="fellapp_populate")
     */
    public function populateSpreadsheetAction() {

        echo "fellapp populateSpreadsheet <br>";

        //get latest spreadsheet file from Uploaded/fellapp/Spreadsheets
        $em = $this->getDoctrine()->getManager();
        $fellappSpreadsheetType = $em->getRepository('OlegUserdirectoryBundle:DocumentTypeList')->findOneByName('Fellowship Application Spreadsheet');
        $documents = $em->getRepository('OlegUserdirectoryBundle:Document')->findBy(
            array('type' => $fellappSpreadsheetType),
            array('createdate'=>'desc'),
            1   //limit to one
        );

        if( count($documents) == 1 ) {
            $document = $documents[0];
        }

        $inputFileName = $document->getServerPath();    //'Uploaded/fellapp/Spreadsheets/Pathology Fellowships Application Form (Responses).xlsx';
        $this->populateSpreadsheet($inputFileName);

        return $this->redirect( $this->generateUrl('fellapp_home') );
    }


    public function populateSpreadsheet( $inputFileName ) {

        echo "inputFileName=".$inputFileName."<br>";

        $service = $this->getGoogleService();
        if( !$service ) {
            $this->get('session')->getFlashBag()->add(
                'warning',
                "Google API service failed!"
            );
            $logger = $this->container->get('logger');
            $logger->warning("Google API service failed!");

            return $this->redirect( $this->generateUrl('fellapp_home') );
        }

        $uploadPath = 'Uploaded/fellapp/FellowshipApplicantUploads/';

        try {
            $inputFileType = \PHPExcel_IOFactory::identify($inputFileName);
            $objReader = \PHPExcel_IOFactory::createReader($inputFileType);
            $objPHPExcel = $objReader->load($inputFileName);
        } catch(Exception $e) {
            throw new IOException('Error loading file "'.pathinfo($inputFileName,PATHINFO_BASENAME).'": '.$e->getMessage());
        }

        //$sheetData = $objPHPExcel->getActiveSheet()->toArray(null,true,true,true);
        //var_dump($sheetData);

        $em = $this->getDoctrine()->getManager();
        $default_time_zone = $this->container->getParameter('default_time_zone');

        $userSecUtil = $this->container->get('user_security_utility');
        $userkeytype = $userSecUtil->getUsernameType('local-user');

        ////////////// add system user /////////////////
        $systemUser = $userSecUtil->findSystemUser();
        ////////////// end of add system user /////////////////

        $sheet = $objPHPExcel->getSheet(0);
        $highestRow = $sheet->getHighestRow();
        $highestColumn = $sheet->getHighestColumn();

        $headers = $rowData = $sheet->rangeToArray('A' . 1 . ':' . $highestColumn . 1,
            NULL,
            TRUE,
            FALSE);
        //print_r($headers);

        $count = 0;

        //for each user in excel
        for ($row = 3; $row <= $highestRow; $row++){

            $count++;

            //  Read a row of data into an array
            $rowData = $sheet->rangeToArray('A' . $row . ':' . $highestColumn . $row,
                NULL,
                TRUE,
                FALSE);

            //print_r($rowData);

            //$id = $rowData[0][0];
            $id = $this->getValueByHeaderName('ID',$rowData,$headers);
            echo "id=".$id."<br>";

            //check if the user already exists in DB by $id
            $user = $em->getRepository('OlegUserdirectoryBundle:User')->findOneByPrimaryPublicUserId($id);
            if( $user ) {
                //skip this applicant because it's already exists in DB
                continue;
            }

            //create excel user
            $user = new User();
            $user->setKeytype($userkeytype);
            $user->setPrimaryPublicUserId($id);

            //set unique username
            $usernameUnique = $user->createUniqueUsername();
            $user->setUsername($usernameUnique);
            $user->setUsernameCanonical($usernameUnique);

            $email = $this->getValueByHeaderName('email',$rowData,$headers);
            //echo "email=".$email."<br>";

            $lastName = $this->getValueByHeaderName('lastName',$rowData,$headers);
            $firstName = $this->getValueByHeaderName('firstName',$rowData,$headers);
            $middleName = $this->getValueByHeaderName('middleName',$rowData,$headers);
            $displayName = $firstName." ".$lastName;
            if( $middleName ) {
                $displayName = $firstName." ".$middleName." ".$lastName;
            }

            $user->setEmail($email);
            $user->setEmailCanonical($email);
            $user->setFirstName($firstName);
            $user->setLastName($lastName);
            $user->setMiddleName($middleName);
            $user->setDisplayName($displayName);
            $user->setPassword("");
            $user->setCreatedby('googleapi');
            $user->getPreferences()->setTimezone($default_time_zone);
            $em->persist($user);

            //fellowshipType
            $fellowshipType = $this->getValueByHeaderName('fellowshipType',$rowData,$headers);
            $fellowshipTypeEntity = $em->getRepository('OlegUserdirectoryBundle:FellowshipSubspecialty')->findOneByName($fellowshipType);
            if( !$fellowshipTypeEntity ) {
                $fellTypeCount = count($em->getRepository('OlegUserdirectoryBundle:FellowshipSubspecialty')->findAll())*10;
                $fellowshipTypeEntity = new FellowshipSubspecialty();
                $userutil = new UserUtil();
                $userutil->setDefaultList($fellowshipTypeEntity,$fellTypeCount,$user,$fellowshipType);
                $em->persist($fellowshipTypeEntity);
            }
            $training = new Training($user);
            $training->setFellowshipSubspecialty($fellowshipTypeEntity);
            $user->addTraining($training);

            //trainingPeriodStart
            $trainingPeriodStart = $this->getValueByHeaderName('trainingPeriodStart',$rowData,$headers);
            $fellowshipApplication = new FellowshipApplication($user);
            $fellowshipApplication->setStartDate($trainingPeriodStart);

            //trainingPeriodEnd
            $trainingPeriodEnd = $this->getValueByHeaderName('trainingPeriodEnd',$rowData,$headers);
            $fellowshipApplication->setEndDate($trainingPeriodEnd);

            //uploadedPhotoUrl
            $uploadedPhotoUrl = $this->getValueByHeaderName('uploadedPhotoUrl',$rowData,$headers);
            //echo "uploadedPhotoUrl=".$uploadedPhotoUrl."<br>";
            $uploadedPhotoId = $this->getFileIdByUrl( $uploadedPhotoUrl );
            $uploadedPhotoDb = $this->downloadFileToServer($user, $service, $uploadedPhotoId, null, $uploadPath);
            if( !$uploadedPhotoDb ) {
                throw new IOException('Unable to download file to server: uploadedPhotoUrl='.$uploadedPhotoUrl.', fileID='.$uploadedPhotoDb->getId());
            }
            $user->setAvatar($uploadedPhotoDb); //set this file as Avatar

            //uploadedCVUrl
            $uploadedCVUrl = $this->getValueByHeaderName('uploadedCVUrl',$rowData,$headers);
            $uploadedCVUrlId = $this->getFileIdByUrl( $uploadedCVUrl );
            $uploadedCVUrlDb = $this->downloadFileToServer($user, $service, $uploadedCVUrlId, null, $uploadPath);
            if( !$uploadedCVUrlDb ) {
                throw new IOException('Unable to download file to server: uploadedCVUrl='.$uploadedCVUrl.', fileID='.$uploadedCVUrlDb->getId());
            }
            $cv = new CurriculumVitae($user);
            $cv->getAttachmentContainer()->getDocumentContainers()->first()->addDocument($uploadedCVUrlDb);
            $user->getCredentials()->addCv($cv);

            //uploadedCoverLetterUrl
            $uploadedCoverLetterUrl = $this->getValueByHeaderName('uploadedCoverLetterUrl',$rowData,$headers);
            $uploadedCoverLetterUrlId = $this->getFileIdByUrl( $uploadedCoverLetterUrl );
            $uploadedCoverLetterUrlDb = $this->downloadFileToServer($user, $service, $uploadedCoverLetterUrlId, null, $uploadPath);
            if( !$uploadedCoverLetterUrlDb ) {
                throw new IOException('Unable to download file to server: uploadedCoverLetterUrl='.$uploadedCoverLetterUrl.', fileID='.$uploadedCoverLetterUrlDb->getId());
            }
            $fellowshipApplication->getAttachmentContainer()->getDocumentContainers()->first()->addDocument($uploadedCoverLetterUrlDb);

            //usl upload

            //presentAddressStreet1


            exit(1);
        }


        echo "count=".$count."<br>";
        exit('end populate');
    }


    public function getValueByHeaderName($header, $row, $headers) {

        $res = null;

        //echo "header=".$header."<br>";
        //print_r($headers);
        //print_r($row);

        $key = array_search($header, $headers[0]);
        //echo "key=".$key."<br>";

        if( array_key_exists($key, $row[0]) ) {
            $res = $row[0][$key];
        }

        return $res;
    }

    //parse url and get file id
    public function getFileIdByUrl( $url ) {
        //https://drive.google.com/a/pathologysystems.org/file/d/0B2FwyaXvFk1eSDQ0MkJKSjhLN1U/view?usp=drivesdk
        $urlArr = explode("/d/", $url);
        $urlSecond = $urlArr[1];
        $urlSecondArr = explode("/", $urlSecond);
        $url = $urlSecondArr[0];
        return $url;
    }



    /**
     * Import spreadsheet to C:\Program Files (x86)\Aperio\Spectrum\htdocs\order\scanorder\Scanorders2\web\Uploaded\fellapp\Spreadsheets
     *
     * @Route("/import", name="fellapp_import")
     */
    public function importAction() {

        echo "fellapp import <br>";

        $service = $this->getGoogleService();
        if( !$service ) {
            $this->get('session')->getFlashBag()->add(
                'warning',
                "Google API service failed!"
            );
            $logger = $this->container->get('logger');
            $logger->warning("Google API service failed!");
        }

        if( $service ) {

            //https://drive.google.com/open?id=1DN1BEbONKNmFpHU6xBo69YSLjXCnhRy0IbyXrwMzEzc
            $excelId = "1DN1BEbONKNmFpHU6xBo69YSLjXCnhRy0IbyXrwMzEzc";

            $user = $this->get('security.context')->getToken()->getUser();
            $path = 'Uploaded/fellapp/Spreadsheets/';
            $fileDb = $this->downloadFileToServer($user, $service, $excelId, 'excel', $path);

            if( $fileDb ) {
                $this->get('session')->getFlashBag()->add(
                    'notice',
                    "Fellowship Application Spreadsheet file has been successful downloaded to the server with id=" . $fileDb->getId().", title=".$fileDb->getUniquename()
                );
            } else {
                $this->get('session')->getFlashBag()->add(
                    'warning',
                    "Fellowship Application Spreadsheet download failed!"
                );
                $logger = $this->container->get('logger');
                $logger->warning("Fellowship Application Spreadsheet download failed!");
            }

        }

        return $this->redirect( $this->generateUrl('fellapp_home') );

//        //$excelFile = $this->printFile($service, $excelId);
//
//        //$response = $this->downloadFile($service, $excelFile, 'excel');
//
//        //echo "response=".$response."<br>";
//
//        exit(1);
//
//
////        $files = $service->files->listFiles();
////        echo "count files=".count($files)."<br>";
////        //echo "<pre>"; print_r($files);
////        foreach( $files as $item ) {
////            echo "title=".$item['title']."<br>";
////        }
//
//        //https://drive.google.com/open?id=0B2FwyaXvFk1edWdMdTlFTUt1aVU
//        $folderId = "0B2FwyaXvFk1edWdMdTlFTUt1aVU";
//        //https://drive.google.com/open?id=0B2FwyaXvFk1efmc2VGVHUm5yYjJRWGFYYTF0Z2N6am9iUFVzcTc1OXdoWEl1Vmc0LWdZc0E
//        //$folderId = "0B2FwyaXvFk1efmc2VGVHUm5yYjJRWGFYYTF0Z2N6am9iUFVzcTc1OXdoWEl1Vmc0LWdZc0E";
//        //$files = $this->printFilesInFolder($service, $folderId);
//
//
//        $photoId = "0B2FwyaXvFk1eRnJVS1N0MWhkc0E";
//        $file = $this->printFile($service, $photoId);
//        $response = $this->downloadFile($service, $file);
//        echo "response=".$response."<br>";
//
//        exit('1');
//
//        // Exchange authorization code for access token
//        //$accessToken = $client->authenticate($authCode);
//        //$client->setAccessToken($accessToken);
//
//        $fileId = "1DN1BEbONKNmFpHU6xBo69YSLjXCnhRy0IbyXrwMzEzc";
//
//        $file = $this->printFile($service, $fileId);
//
//        echo "after file <br>";
//
//        $response = $this->downloadFile($service,$file);
//
//        print_r($response);
//
//        echo "response=".$response."<br>";
//        //exit();
//        return $response;
//
//        return $this->redirect( $this->generateUrl('fellapp_home') );
    }






    public function getGoogleService() {
        $client_email = '1040591934373-1sjcosdt66bmani0kdrr5qmc5fibmvk5@developer.gserviceaccount.com';
        $pkey = __DIR__ . '/../Util/FellowshipApplication-f1d9f98353e5.p12';
        $user_to_impersonate = 'olegivanov@pathologysystems.org';
        $res = $this->authenticationP12Key($pkey,$client_email,$user_to_impersonate);
        return $res['service'];
    }

    //Using OAuth 2.0 for Server to Server Applications: using PKCS12 certificate file
    //https://developers.google.com/api-client-library/php/auth/service-accounts
    //1) Create a service account by Google Developers Console.
    //2) Delegate domain-wide authority to the service account.
    //3) Impersonate a user account.
    public function authenticationP12Key($pkey,$client_email,$user_to_impersonate) {
        $private_key = file_get_contents($pkey); //notasecret
        $scopes = array('https://www.googleapis.com/auth/drive');
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
        if ($client->getAuth()->isAccessTokenExpired()) {
            $client->getAuth()->refreshTokenWithAssertion();
        }

        $service = new \Google_Service_Drive($client);

        $res = array(
            'client' => $client,
            'credentials' => $credentials,
            'service' => $service
        );

        return $res;
    }

    public function downloadFileToServer($user, $service, $fileId, $type, $path) {
        $file = null;
        try {
            $file = $service->files->get($fileId);
        } catch (Exception $e) {
            throw new IOException('Google API: Unable to get file by file id='.$fileId.". An error occurred: " . $e->getMessage());
        }

        $em = $this->getDoctrine()->getManager();

        if( $file ) {

            //check if file already exists by file id
            $documentDb = $em->getRepository('OlegUserdirectoryBundle:Document')->findOneByUniqueid($file->getId());
            if( $documentDb ) {
                return $documentDb;
            }

            $response = $this->downloadFile($service, $file, $type);
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
            //$fileExt = pathinfo($file->getTitle(), PATHINFO_EXTENSION);

            $fileUniqueName = $currentDatetimeTimestamp.'_id='.$file->getId();  //.'_title='.$fileTitle;

            $filesize = $file->getFileSize();
            if( !$filesize ) {
                $filesize = mb_strlen($response) / 1024; //KBs,
            }

            $object = new Document($user);
            $object->setUniqueid($file->getId());
            $object->setOriginalname($file->getTitle());
            $object->setUniquename($fileUniqueName);
            $object->setUploadDirectory($path);
            $object->setSize($filesize);

            if( $type && $type == 'excel' ) {
                $fellappSpreadsheetType = $em->getRepository('OlegUserdirectoryBundle:DocumentTypeList')->findOneByName('Fellowship Application Spreadsheet');
            } else {
                $fellappSpreadsheetType = $em->getRepository('OlegUserdirectoryBundle:DocumentTypeList')->findOneByName('Fellowship Application Upload');
            }
            if( $fellappSpreadsheetType ) {
                $object->setType($fellappSpreadsheetType);
            }

            $em->persist($object);
            $em->flush($object);

            $fullpath = $this->get('kernel')->getRootDir() . '/../web/'.$path;
            $target_file = $fullpath . $fileUniqueName;

            //$target_file = $fullpath . 'uploadtestfile.jpg';
            //echo "target_file=".$target_file."<br>";
            if( !file_exists($fullpath) ) {
                // 0600 - Read and write for owner, nothing for everybody else
                mkdir($fullpath, 0600, true);
                chmod($fullpath, 0600);
            }

            file_put_contents($target_file, $response);

            return $object;
        }

        return null;
    }

    /**
     * Print files belonging to a folder.
     *
     * @param Google_Service_Drive $service Drive API service instance.
     * @param String $folderId ID of the folder to print files from.
     */
    function printFilesInFolder($service, $folderId) {
        $pageToken = NULL;

        do {
            try {
                $parameters = array();
                if ($pageToken) {
                    $parameters['pageToken'] = $pageToken;
                }
                $children = $service->children->listChildren($folderId, $parameters);
                echo "count=".count($children->getItems())."<br>";

                foreach ($children->getItems() as $child) {
                    //print 'File Id: ' . $child->getId()."<br>";
                    //print_r($child);
                    $this->printFile($service,$child->getId());
                }
                $pageToken = $children->getNextPageToken();
            } catch (Exception $e) {
                print "An error occurred: " . $e->getMessage();
                $pageToken = NULL;
            }
        } while ($pageToken);
    }

//    function getFilesByPkey() {
//
//        $client_id = "1040591934373-1sjcosdt66bmani0kdrr5qmc5fibmvk5.apps.googleusercontent.com";
//        $client_email = '1040591934373-1sjcosdt66bmani0kdrr5qmc5fibmvk5@developer.gserviceaccount.com';
//
//        $pkey = __DIR__ . '/../Util/FellowshipApplication-f1d9f98353e5.p12';
//        $private_key = file_get_contents($pkey); //notasecret
//
//        $scopes = array('https://www.googleapis.com/auth/drive');   //array('https://www.googleapis.com/auth/sqlservice.admin');
//
////        $credentials = new \Google_Auth_AssertionCredentials(
////            $client_email,
////            $scopes,
////            $private_key
////        );
//
//        $user_to_impersonate = 'olegivanov@pathologysystems.org';
//        $credentials = new \Google_Auth_AssertionCredentials(
//            $client_email,
//            $scopes,
//            $private_key,
//            'notasecret',                                 // Default P12 password
//            'http://oauth.net/grant_type/jwt/1.0/bearer', // Default grant type
//            $user_to_impersonate
//        );
//
//        $client = new \Google_Client();
//        //$client->setAccessType('offline');
//        $client->setAssertionCredentials($credentials);
//        if ($client->getAuth()->isAccessTokenExpired()) {
//            $client->getAuth()->refreshTokenWithAssertion();
//        }
//
////        $sqladmin = new \Google_Service_SQLAdmin($client);
////        $response = $sqladmin->instances->listInstances('examinable-example-123')->getItems();
////        echo json_encode($response) . "\n";
//
//        $service = new \Google_Service_Drive($client);
//
//        $files = $service->files->listFiles();
//        echo "count files=".count($files)."<br>";
//        //echo "<pre>"; print_r($files);
//
//        foreach( $files as $item ) {
//            echo "title=".$item['title']."<br>";
//        }
//
//    }

    function getFilesByAuthUrl() {
        $client_id = "1040591934373-hhm896qpgdaiiblaco9jdfvirkh5f65q.apps.googleusercontent.com";
        $client_secret = "RgXkEm2_1T8yKYa3Vw_tIhoO";
        $redirect_uri = 'urn:ietf:wg:oauth:2.0:oob';    //"http://localhost";

        $res = $this->buildService($client_id,$client_secret,$redirect_uri);

        $service = $res['service'];
        $client = $res['client'];

        $authUrl = $client->createAuthUrl();
        echo "authUrl=".$authUrl."<br>";

        // Exchange authorization code for access token
        $accessToken = $client->authenticate('4/OrVeRdkw9eByckCs7Gtn0B4eUwhERny8AqFOAwy29fY');
        $client->setAccessToken($accessToken);

        $files = $service->files->listFiles();
        echo "count files=".count($files)."<br>";
        echo "<pre>"; print_r($files);
    }


    /**
     * Build a Drive service object.
     *
     * @param String credentials Json representation of the OAuth 2.0
     *     credentials.
     * @return Google_Service_Drive service object.
     */
    function buildService_ORIG($credentials) {
        $apiClient = new \Google_Client();
        $apiClient->setUseObjects(true);
        $apiClient->setAccessToken($credentials);
        return new \Google_Service_Drive($apiClient);
    }

    function buildService($client_id,$client_secret,$redirect_uri) {
        $client = new \Google_Client();
        $client->setClientId($client_id);
        $client->setClientSecret($client_secret);
        $client->setRedirectUri($redirect_uri);

        //$client->addScope("https://www.googleapis.com/auth/drive");
        $client->setScopes(array('https://www.googleapis.com/auth/drive'));
        $client->setAccessType('offline');

        $service = new \Google_Service_Drive($client);

        $res = array(
            'client' => $client,
            'service' => $service
        );
        return $res;
    }

    /**
     * Print a file's metadata.
     *
     * @param apiDriveService $service Drive API service instance.
     * @param string $fileId ID of the file to print metadata for.
     */
    function printFile($service, $fileId) {
        $file = null;
        try {
            $file = $service->files->get($fileId);

            print "Title: " . $file->getTitle()."<br>";
            print "ID: " . $file->getId()."<br>";
            print "Size: " . $file->getFileSize()."<br>";
            //print "URL: " . $file->getDownloadUrl()."<br>";
            print "Description: " . $file->getDescription()."<br>";
            print "MIME type: " . $file->getMimeType()."<br>"."<br>";

        } catch (Exception $e) {
            print "An error occurred: " . $e->getMessage();
        }
        return $file;
    }


    /**
     * Download a file's content.
     *
     * @param Google_Servie_Drive $service Drive API service instance.
     * @param Google_Servie_Drive_DriveFile $file Drive File instance.
     * @return String The file's content if successful, null otherwise.
     */
    function downloadFile($service, $file, $type=null) {
        if( $type && $type == 'excel' ) {
            $downloadUrl = $file->getExportLinks()['text/csv'];
        } else {
            $downloadUrl = $file->getDownloadUrl();
        }
        echo "downloadUrl=".$downloadUrl."<br>";
        if ($downloadUrl) {
            $request = new \Google_Http_Request($downloadUrl, 'GET', null, null);
            $httpRequest = $service->getClient()->getAuth()->authenticatedRequest($request);
            echo "res code=".$httpRequest->getResponseHttpCode()."<br>";
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



    /**
     * Show home page
     *
     * @Route("/test", name="fellapp_test")
     * @Method("GET")
     */
    public function testAction() {

        //include_once "vendor/google/apiclient/examples/simple-query.php";
        include_once "vendor/google/apiclient/examples/user-example.php";
        //include_once "vendor/google/apiclient/examples/idtoken.php";



        return new Response("OK Test");
    }

}
