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

            //uploadedPhotoUrl
            $uploadedPhotoUrl = $this->getValueByHeaderName('uploadedPhotoUrl',$rowData,$headers);
            echo "uploadedPhotoUrl=".$uploadedPhotoUrl."<br>";

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

//        $credentials = '{
//          "private_key_id": "1b61c9fb73ac18736622fdb95d08b8056b87f579",
//          "private_key": "-----BEGIN PRIVATE KEY-----\nMIIEvQIBADANBgkqhkiG9w0BAQEFAASCBKcwggSjAgEAAoIBAQDh2VtnsxKxMap7\nH/QE7i+UT6lck27eZ0dHa8SMV9312uYpkRzgceL2kwp8I3NRN0OXwbOG+8zUC+jV\nXR74DWmoUmlwAqaPiB4TKLIFc+inJOcS+wKvNWGlDM5cv6I1TUqfXBckRjQ3SzCE\nEYauOrZHbS/Db8AQJVENlqYTwav4PlN+rZTLmJO81tJuyAwEFm5ymD+fc8ibu/04\nUELXKY8hSKTnGFUBwBIl6A0fs/d4djTOyrgDsxFWAky5AbobAjIDJI+AX+mr/rVx\np/CH1dW+/OzMiZgQ/34d+phuyr/F/iRz3BPXJt54GUFJHbeXbC+X4dJ0qDSE8bMq\nvjwcb8n5AgMBAAECggEBAJveVLUW43mjG1NqVBDrCa9D41De949Km+jwuW9aXPeG\noX5ihhlowAkIph3SoY2VpHKh3nL0aQKXTZOjdvIe36Kpbdc+HRHGEWuLlIEq45An\nacqxrcKaBs/QLMPaBerfcvbUqawBP5xBqjQbnGW2Y4KcGnC5OUZrWqsUI35TFYp2\nvTOkXNAA5yG8lpvLUoNB/BLO1UyyIot37NsSdPZmhctxS6MJv3GBJoW02VMUexEz\nIAyv/FDMXkxBt+JdG/Q76vaekL45qXFXjncROre0VBRQx8Jw7QgfZ5D+4MxzyFdu\nv1bhX3qB4A+UY1Y+wgOs89ivNJ2BK7pOOxWsB/X37GECgYEA+PVvW9lUrYmENvbv\n8Z7ZocrRHHtckyVAaSCUFdqLJz6W/pAXNx7+eCSwOu43mKX3DxshfVWVrAyxn/Vq\nz/KHkGfUPaJpCntIyWK/2iI91XHavRmj9TLDKSUTjvMZg9LVSk/B/SKJdG792gpT\nQitUl3g2k3mqyoWzXHaxGrZBZa0CgYEA6DyZLOlEez8KVsbVvzAFDXgAr+gnplA3\n/ymFxaGNjKFTmQaXVFDw5WP86AejHh6QwQYDZeUo4/bN3G6GqFBZOCt/jNP6Vm27\nDRYS5pCdZumpGMH7ixFU0ZRm1xFq6QwUcwHgtLt3lG6H7vqWeeQiazVUTTpqp9c6\nUtUcR1IlRv0CgYAygxG6EAlnQFyMDmQ2oOVFN3JgFgN9c3RzIAILwRC0wLVAJxoe\nu/IjjEYZXtX26c2LyhRsap34j4bGjrPCR1IMEZT1gGtRjhwBiECm0IW9NeGMtpQW\nntsMERK70UUfAvr1neMdKhG7hv2IbMnhxgrexKxGFcx6VNBEdWyPn+T67QKBgHU7\nmetU+e/pO9PgXag8mmBZMqeZ3uIS3qGdGV1Rlz3ldmjqLdwvW9vAZLvQlyQuM85s\ntaxrSQAC55qd5LX0kYVMWAAERfv5OpJ5kSL436xCycyop81k+1csvdlVfo2UPoJr\n8T3q4It6XH5j2zA+3K0X561wjsSZXmTQFY1fR1gVAoGAFIV6vBKsh/tqJ19mrh+h\nAWp0rFWpJWMLNNhU8fvrMwm0ZswCWei+tMnDDDsgLko1hwUXvbKQGVmxrb3o42nI\n4um7f27gPCs/gNksYibg3jKWplPhbYybWTRdFZ0Q4FUfP/tuL3cbw605TcQEjDW5\ni1Onh2wEBQYAZYwFER7Ssak\u003d\n-----END PRIVATE KEY-----\n",
//          "client_email": "1040591934373-c7rvicvmf22s0slqfejftmpmc9n1dqah@developer.gserviceaccount.com",
//          "client_id": "1040591934373-c7rvicvmf22s0slqfejftmpmc9n1dqah.apps.googleusercontent.com",
//          "type": "service_account"
//        }';
//        $service = $this->buildService($credentials);

        //$this->getFilesByAuthUrl();
        //$this->getFilesByPkey();
        $client_email = '1040591934373-1sjcosdt66bmani0kdrr5qmc5fibmvk5@developer.gserviceaccount.com';
        $pkey = __DIR__ . '/../Util/FellowshipApplication-f1d9f98353e5.p12';
        $user_to_impersonate = 'olegivanov@pathologysystems.org';
        $res = $this->authenticationP12Key($pkey,$client_email,$user_to_impersonate);

        $service = $res['service'];

//        $files = $service->files->listFiles();
//        echo "count files=".count($files)."<br>";
//        //echo "<pre>"; print_r($files);
//        foreach( $files as $item ) {
//            echo "title=".$item['title']."<br>";
//        }

        //https://drive.google.com/open?id=0B2FwyaXvFk1edWdMdTlFTUt1aVU
        $folderId = "0B2FwyaXvFk1edWdMdTlFTUt1aVU";
        //https://drive.google.com/open?id=0B2FwyaXvFk1efmc2VGVHUm5yYjJRWGFYYTF0Z2N6am9iUFVzcTc1OXdoWEl1Vmc0LWdZc0E
        //$folderId = "0B2FwyaXvFk1efmc2VGVHUm5yYjJRWGFYYTF0Z2N6am9iUFVzcTc1OXdoWEl1Vmc0LWdZc0E";
        //$files = $this->printFilesInFolder($service, $folderId);


        $photoId = "0B2FwyaXvFk1eRnJVS1N0MWhkc0E";
        $file = $this->printFile($service, $photoId);
        //$response = $this->downloadFile($service, $file);
        //echo "response=".$response."<br>";

        exit('1');

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
