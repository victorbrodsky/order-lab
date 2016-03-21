<?php
/**
 * Created by PhpStorm.
 * User: ch3
 * Date: 3/21/2016
 * Time: 10:43 AM
 */

namespace Oleg\FellAppBundle\Util;


//TODO: implement
// "Delete successfully imported applications from Google Drive",
// "deletion of rows from the spreadsheet on Google Drive upon successful import"
// "Automatically delete downloaded applications that are older than [X] year(s)".


use Oleg\UserdirectoryBundle\Util\UserUtil;

class GoogleSheetManagement {

    protected $em;
    protected $sc;
    protected $container;

    public function __construct( $em, $sc, $container ) {

        $this->em = $em;
        $this->sc = $sc;
        $this->container = $container;
    }




    //delete: imported rows from the sheet on Google Drive and associated uploaded files from the Google Drive.
    public function deleteImportedApplicationAndUploadsFromGoogleDrive($fellowshipApplication) {

        $logger = $this->container->get('logger');
        $fellappUtil = $this->container->get('fellapp_util');
        $userSecUtil = $this->container->get('user_security_utility');
        $systemUser = $userSecUtil->findSystemUser();
        $userUtil = new UserUtil();

        //cinava7@yahoo.com_Doe_Linda_2016-03-15_17_59_53
        //$rowId = $fellowshipApplication->getGoogleFormId();

        $service = $this->getGoogleService();

        if( !$service ) {
            $event = "Google API service failed!";
            $logger->warning($event);
            $userSecUtil->createUserEditEvent($this->container->getParameter('fellapp.sitename'),$event,$systemUser,null,null,'Error');
            $fellappUtil->sendEmailToSystemEmail($event, $event);
            return null;
        }
        exit('service ok');

        //https://drive.google.com/open?id=1DN1BEbONKNmFpHU6xBo69YSLjXCnhRy0IbyXrwMzEzc
        //$excelId = "1DN1BEbONKNmFpHU6xBo69YSLjXCnhRy0IbyXrwMzEzc";
        $excelId = $userUtil->getSiteSetting($this->em,'excelIdFellApp');

        //http://stackoverflow.com/questions/23400842/delete-row-in-google-spredsheet-with-php-curl?

        //https://api.kdyby.org/class-Google_Http_Request.html
        //https://developers.google.com/google-apps/spreadsheets/#deleting_a_list_row:
        // DELETE https://spreadsheets.google.com/feeds/list/key/worksheetId/private/full/rowId/rowVersion

        https://docs.google.com/a/pathologysystems.org/spreadsheets/d/1hNJUm-EWC33tEyvgkcBJQ7lO1PcFwxfi3vMuB96etno/edit?usp=sharing
        $excelId = "1hNJUm-EWC33tEyvgkcBJQ7lO1PcFwxfi3vMuB96etno";
        $rowId = 3;

        //excel download test
        $response = $this->downloadFile($service, $excelId, 'excel');
        echo "response=".$response."<br>";
        if( !$response ) {
            throw new IOException('Error file response is empty: file id='.$excelId);
        }
        exit('1');

        $deleteUrl = "https://spreadsheets.google.com/feeds/list/key/".$excelId."/private/full/".$rowId;
        $request = new \Google_Http_Request($deleteUrl, 'DELETE', null, null);
        $httpRequest = $service->getClient()->getAuth()->authenticatedRequest($request);
        echo "res code=".$httpRequest->getResponseHttpCode()."<br>";

        if ($httpRequest->getResponseHttpCode() == 200) {
            return $httpRequest->getResponseBody();
        } else {
            // An error occurred.
            return null;
        }


        exit(1);
        return true;
    }




    public function getGoogleService() {
        //$client_email = '1040591934373-1sjcosdt66bmani0kdrr5qmc5fibmvk5@developer.gserviceaccount.com';
        $userUtil = new UserUtil();
        $client_email = $userUtil->getSiteSetting($this->em,'clientEmailFellApp');

        //$pkey = __DIR__ . '/../Util/FellowshipApplication-f1d9f98353e5.p12';
        $pkey = $userUtil->getSiteSetting($this->em,'p12KeyPathFellApp');
        if( !$pkey ) {
            $logger = $this->container->get('logger');
            $logger->warning('p12KeyPathFellApp is not defined in Site Parameters. p12KeyPathFellApp='.$pkey);
        }

        //$user_to_impersonate = 'olegivanov@pathologysystems.org';
        $user_to_impersonate = $userUtil->getSiteSetting($this->em,'userImpersonateEmailFellApp');

        $res = $this->authenticationP12Key($pkey,$client_email,$user_to_impersonate);
        return $res['service'];
    }

    //Using OAuth 2.0 for Server to Server Applications: using PKCS12 certificate file
    //https://developers.google.com/api-client-library/php/auth/service-accounts
    //1) Create a service account by Google Developers Console.
    //2) Delegate domain-wide authority to the service account.
    //3) Impersonate a user account.
    public function authenticationP12Key($pkey,$client_email,$user_to_impersonate) {
        echo "pkey=".$pkey."<br>";
        $private_key = file_get_contents($pkey); //notasecret

        $userUtil = new UserUtil();
        $googleDriveApiUrlFellApp = $userUtil->getSiteSetting($this->em,'googleDriveApiUrlFellApp');
        if( !$googleDriveApiUrlFellApp ) {
            throw new \InvalidArgumentException('googleDriveApiUrlFellApp is not defined in Site Parameters.');
        }
        $scopes = array($googleDriveApiUrlFellApp); //'https://www.googleapis.com/auth/drive'

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
            echo 'before refreshTokenWithAssertion<br>';
            //print_r($credentials);
            //exit('1');
            $client->getAuth()->refreshTokenWithAssertion($credentials); //causes timeout on localhost: OAuth ERR_CONNECTION_RESET
            exit('after');
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