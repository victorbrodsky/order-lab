<?php
/**
 * Created by PhpStorm.
 * User: oli2002
 * Date: 10/15/14
 * Time: 11:57 AM
 */

namespace Oleg\OrderformBundle\Controller;


use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

use Oleg\UserdirectoryBundle\Controller\UploadController;
use Symfony\Component\HttpFoundation\Response;

include_once '\DatabaseRoutines.php';
include_once '\cImageFile.php';

class ScanUploadController extends UploadController {

    /**
     * @Route("/file-delete", name="scan_file_delete")
     * @Method("POST")
     */
    public function deleteFileAction(Request $request) {
        return $this->deleteFileMethod($request);
    }

    /**
     * @Route("/file-download/{id}", name="scan_file_download", requirements={"id" = "\d+"})
     * @Method("GET")
     */
    public function downloadFileAction($id) {
        return $this->downloadFileMethod($id);
    }



    //Aperio communicate to DB by using "soap call" http://www.aperio.com/webservices/
    //$res = $client->__soapCall(	'GetRecordImages',		//SOAP Method Name
    //                              $ParamsArray); 			//Parameters

    //images are stores in a single svs file.
    //cTable.php GetImages function Show the list of record images

    //There are two ways to show slide image:
    //1) using 'Aperio Image Scope' with generated 'sis' file
    //2) using 'Web Scope': http://192.168.37.128/imageserver/@@Y4XGX_n725b-quq6RExmLlOJHFwi8MvoiWTyPOMAcSE6lO1I16q5fg==/@23/view.apml
    //Note: for the (2) way, Aperio authentication is required providing token Y4XGX_n725b-quq6RExmLlOJHFwi8MvoiWTyPOMAcSE6lO1I16q5fg==


    /**
     * @Route("/image-viewer/{system}/{type}/{tablename}/{imageid}", name="scan_image_viewer", requirements={"imageid" = "\d+"})
     * @Method("GET")
     */
    public function imageFileAction($system,$type,$tablename,$imageid) {

        $em = $this->getDoctrine()->getManager();

        //$document = $em->getRepository('OlegUserdirectoryBundle:Document')->find($id);

        //AperioAuthentication
        //Array ( [ReturnCode] => 0 [ReturnText] => [Token] => g9_qgXEA7Q2aMKLzYsdHv3yFn0HaUjhtOvDhZgIaipW47PMTJQryCQ==
        //[UserId] => 3 [FullName] => Administrator [LoginName] => administrator [Phone] => [E_Mail] => [LastLoginTime] => 2015-05-13 14:23:37
        //[PasswordDaysLeft] => -1 [UserMustChangePassword] => False [StartPage] => [AutoView] => [ViewingMode] => [DisableLicenseWarning] => [ScanDataGroupId] => )

        $user = $this->get('security.context')->getToken()->getUser();

        //print_r($this->get('session'));
        $sessionId = $this->get('session')->getId();
        //$sessionId = 'es1yx28s0rccwcc4s0oog8g04gg4cwg';
        echo "session id=".$sessionId."<br>";

//        echo "php session:<br>";
//        print_r($_SESSION);
//        echo "<br>";

        //DataServer Error: -7002: Failed to execute method DataServer.ImageProxy.GetRecordImages: Token is invalid or has timed out.
		$_SESSION['AuthToken'] = $sessionId;

        //echo "session:<br>";
        //print_r($_SESSION ['AuthToken']);
        //echo "<br>";

        //imageId=23
        //url=\\win-vtbcq31qg86\images\1376592217_1368_3005ER.svs

        $Id = $imageid;
        $TableName = $tablename;

        echo "ADB_GetRecordImages: Id=".$Id.", TableName=".$TableName."<br>";
        $RecordImages = ADB_GetRecordImages($Id, $TableName);
        echo "Slide's RecordImages count=".count($RecordImages)."<br>";

        foreach( $RecordImages as $image ) {
            echo $TableName." image:<br>";
            print_r($image);
            echo "<br>";

            $ImageFile = new \cImageFile();

            $ImageFile->SetFilePath($image['CompressedFileLocation']);
            $ImageServerURL = $ImageFile->GetURL();

            echo $TableName.": ImageServerURL=".$ImageServerURL."<br>";
            echo $TableName.": ImageId=".$Id."<br>";

        }



        //1) get image info by imageid

        //2) show image in Aperio's image viewer http://c.med.cornell.edu/imageserver/@@_DGjlRH2SJIRkb9ZOOr1sJEuLZRwLUhWzDSDb-sG0U61NzwQ4a8Byw==/@73660/view.apml

        $response = new Response();

        exit('exit imageFileAction');

        return $response;
    }



} 