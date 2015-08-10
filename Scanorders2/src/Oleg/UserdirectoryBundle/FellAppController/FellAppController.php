<?php

namespace Oleg\UserdirectoryBundle\FellAppController;


use Oleg\UserdirectoryBundle\Entity\FellowshipApplication;
use Oleg\UserdirectoryBundle\Entity\FellowshipSubspecialty;
use Oleg\UserdirectoryBundle\Entity\Training;
use Oleg\UserdirectoryBundle\Entity\User;
use Oleg\UserdirectoryBundle\Util\UserUtil;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
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

        $inputFileName = 'Uploaded/fellapp/Spreadsheets/Pathology Fellowships Application Form (Responses).xlsx';
        $this->populateSpreadsheet($inputFileName);

        return $this->redirect( $this->generateUrl('fellapp_home') );
    }


    public function populateSpreadsheet( $inputFileName ) {

        echo "inputFileName=".$inputFileName."<br>";

        try {
            $inputFileType = \PHPExcel_IOFactory::identify($inputFileName);
            $objReader = \PHPExcel_IOFactory::createReader($inputFileType);
            $objPHPExcel = $objReader->load($inputFileName);
        } catch(Exception $e) {
            die('Error loading file "'.pathinfo($inputFileName,PATHINFO_BASENAME).'": '.$e->getMessage());
        }

        //$sheetData = $objPHPExcel->getActiveSheet()->toArray(null,true,true,true);
        //var_dump($sheetData);

        $em = $this->getDoctrine()->getManager();
        $default_time_zone = $this->container->getParameter('default_time_zone');

        $userSecUtil = $this->container->get('user_security_utility');
        $userkeytype = $userSecUtil->getUsernameType('local-user');

        $count = 0;

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

        //for each user in excel
        for ($row = 3; $row <= $highestRow; $row++){
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

            //presentAddressStreet1


        }


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


    /**
     * Show home page
     *
     * @Route("/import", name="fellapp_import")
     */
    public function importAction() {

        echo "fellapp import <br>";

        $credentials = '{
  "private_key_id": "2917cc7b719694a2e6e9f77bdc0d8d0411c0451e",
  "private_key": "-----BEGIN PRIVATE KEY-----\nMIIEvgIBADANBgkqhkiG9w0BAQEFAASCBKgwggSkAgEAAoIBAQCoZVCjuANc+ftL\nY3vd0EtXCwqJHxypuKJIAoeJnpEfBh24PJete22u305gFNZYmvkTzJUXXyTfSNIy\nVPckby8TCpeUP/gNZ9DwlRStxI7FKxsV6I7pd64UDvArcwAAu2MK0EOZXkM8ukEb\n65G/RqiGFqO1b18TNMLcuUJuhUJ1n/wJoQFUDW5AmKbmKMquNjZ1eNHr0enMMKr0\n3PbsmT6H3e1/mVxcRqYOtftc3fCbrYzvRPinfTdhmgc1uvwuC19ecoLms4bCxFq7\nvrzRUYotd3zuM02idzbsUz57p8Ek6FQC0FibwqN/naAkGjzM4b/tmqmixJ2O3N6D\nNGVlJcUDAgMBAAECggEBAI3hn0nyFtNVxIdGcOz5PSE/qkbSMeJGMGUfzHcFZRyQ\nmKXMV7fRkR6QA4csx1SsbkRiURP6FCUVRBUoRXsrOqx+dixwoV0aJY8u7NdkDy7N\nHOseoJrRPZik5XKWWGBFgjNwOiSUqW4XOBiAKLLSo9cmaBTMm3yhLbWvWeuo19Al\n9tZPwUTC/vA5hhb+3hr44Y9V2Ztsm4ljkU9zRIi9sMd4dPaVf78aZ/2l0d1cjPlk\ndXtxrN19LF8uwqgLJNiNW1ynTgSamPRjxd7bkO5K0FUPKwl7q559Aovoef0cQaT2\nKKxUQH2cbAjYJLLeho+a+coei0H1oujnTDMIJZFzqAECgYEA4FWTfgd76gzc6wzT\nSn7VNqw+8pxU/aksAVk/NIXAVDgVbE3yc/tOhELxe2qqGSOv/tgTX0OVV8Ua4VG1\ndv/ug1rru3LW/umzILOVdQtFYT7vKRKv5LYRJYTK8FXhkx+c3QgMEiH5MGlU6Mfd\nhPLlnyjToqAsxUTjA8d5ARRmFwMCgYEAwCpf1awSgVvtQYHfcxDBzybQg//3p+kz\nNzg2zwV32sssfHzw9IEUI4B3wUFGbACod89DShQbmsDWyBjnEzS5FIdUZj3nIYhI\nhpTVorVGZ+Q7uBREGjzVVM0BEZUiQCbX0AUCIvQ7shiS0En3CggZLlj5xxyzLPrh\nyZ/UdiODOgECgYA/Di6/7PCaj/UEqH03YkEh9fZXkTOefQ+ebWyDodi2k3EKGTq9\n+PRP3tUrgIbBPDO66RdA3qk6m297x9C+2x86krLR5GykCCJOXcvzszBULjFhFRyV\np8tYBWRZe3pFNUyNIDbsXdpCDklMiOkt9mwueXZLLsSGyl8Y79eGQyqS6wKBgHWe\n+j3dLw6C5/v6tHzHuvlCtsq0+C+Mq86W1+VrYWtIhRhFmW7vOxZn4eUmQSaGWJfN\nA1DqceMNOeoMZBP7Z3XLR7u1FC4QLuRBYWpQLqIUrwEDVpQAvEtFl+vdLrO5kss+\n5YnjmE5wgByByXYYcuFNkMVxKbLUdTNmYzNUlVQBAoGBALKJQsuwuQYaunTytDrz\nyHIPvz46QnktwPvluxMeIfUN41c0aaAocYpYwomEsnSXfOs0px7CyRHv0qlBF4Lz\njmOZONkyWtgIwXLEZOe3DOnx52g6MLhWyd4hkIKR2pYwnfkTzkCYrS/iueHVUlDQ\nmSEvVUdlEfGbhtjZTVnFtzGh\n-----END PRIVATE KEY-----\n",
  "client_email": "1040591934373-1sjcosdt66bmani0kdrr5qmc5fibmvk5@developer.gserviceaccount.com",
  "client_id": "1040591934373-1sjcosdt66bmani0kdrr5qmc5fibmvk5.apps.googleusercontent.com",
  "type": "service_account"
}';

        //$service = $this->buildService($credentials);

        $client_id = "1040591934373-hhm896qpgdaiiblaco9jdfvirkh5f65q.apps.googleusercontent.com";
        $client_secret = "RgXkEm2_1T8yKYa3Vw_tIhoO";
        $redirect_uri = 'urn:ietf:wg:oauth:2.0:oob';    //"http://localhost";

        $service = $this->buildService($client_id,$client_secret,$redirect_uri);

        //$authUrl = $client->createAuthUrl();
        //echo "authUrl=".$authUrl."<br>";

        // Exchange authorization code for access token
        //$accessToken = $client->authenticate($authCode);
        //$client->setAccessToken($accessToken);

        $fileId = "1DN1BEbONKNmFpHU6xBo69YSLjXCnhRy0IbyXrwMzEzc";

        $file = $this->printFile($service, $fileId);

        echo "after file <br>";

        $response = $this->downloadFile($service,$file);

        print_r($response);

        echo "response=".$response."<br>";
        //exit();
        return $response;

        return $this->redirect( $this->generateUrl('fellapp_home') );
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
        $client->addScope("https://www.googleapis.com/auth/drive");
        return new \Google_Service_Drive($client);
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

            print "Title: " . $file->getTitle();
            print "Description: " . $file->getDescription();
            print "MIME type: " . $file->getMimeType();

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
    function downloadFile($service, $file) {
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
