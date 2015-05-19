<?php
/**
 * Created by PhpStorm.
 * User: oli2002
 * Date: 10/15/14
 * Time: 11:57 AM
 */

namespace Oleg\OrderformBundle\Controller;


use Oleg\OrderformBundle\Helper\smbclient;
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


    //Aperio API for authenticated user: http://c.med.cornell.edu/imageserver/@73956?GETPATH => \\collage\images\2015-05-14\73956.svs

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

        //1) get image url info by imageid

        ////////////////// aperio DB ////////////////////////////
        $aperioEm = $this->getDoctrine()->getManager('aperio');

        $aperioConnection = $aperioEm->getConnection();

        $statement = $aperioConnection->prepare(
            'SELECT * FROM [Aperio].[dbo].[Image] a WHERE a.ImageId = :imageId'
        );
        $statement->bindValue('imageId', intval($imageid));
        $statement->execute();

        // for SELECT queries
        $results = $statement->fetchAll();  // note: !== $connection->fetchAll()!

        // for INSERT, UPDATE, DELETE queries
        $affected_rows = $statement->rowCount();

        //echo "Affected Rows=".$affected_rows."<br>";

        //echo "<br>Result:<br>";
        //print_r($results);
        //echo "<br><br>";

        if( $affected_rows != 1 && count($results) != 1 ) {
            throw $this->createNotFoundException('Unable to find unique image with id='.$imageid);
        }

        $compressedFileLocation = $results[0]['CompressedFileLocation'];
        //echo "compressedFileLocation Rows=".$compressedFileLocation."<br>";
        //////////////////////////////////////////////////////////

        //2) show image in Aperio's image viewer http://c.med.cornell.edu/imageserver/@@_DGjlRH2SJIRkb9ZOOr1sJEuLZRwLUhWzDSDb-sG0U61NzwQ4a8Byw==/@73660/view.apml

        $response = new Response();

        if( $compressedFileLocation ) {

            $originalname = $tablename."_Image_ID_" . $imageid.".sis";
            $size = 1;

            $contentFile = 'Not implemented link type ' . $type;
            $contentFlagOk = false;

            if( $type == 'Via ImageScope' ) {
                $contentFile = "<SIS>".
                    "<CloseAllImages/>".
                    "<ViewingMode></ViewingMode>".
                    "<Image>".
                    "<URL>".$compressedFileLocation."</URL>".
                    "<Title>".$originalname."</Title>".
                    "</Image>".
                    "</SIS>";
                $contentFlagOk = true;
            }

            if( $type == 'Download' ) {

                if( 0 ) {
                    //file://///Collage/Gross/S14-571/S14-571_1.jpg
                    $file = "file://///collage/images/2015-05-18/75105.svs";
                    $contentFile = fopen($file,"r");
                }

                if( 0 ) {

                $originalname = "1376592216_rat_liver_tox.jpeg";
                $localFile = "C:/Images/SampleData/1376592216_rat_liver_tox.jpg";
                $contentFile = file_get_contents($localFile);
                $size = filesize($localFile);
                //echo "size=".$size."<br>";
                $contentFlagOk = true;
                //exit('2');

                }
                if( 0 ) {

//                $w = stream_get_wrappers();
//                echo 'openssl: ',  extension_loaded  ('openssl') ? 'yes':'no', "<br>";
//                echo 'http wrapper: ', in_array('http', $w) ? 'yes':'no', "<br>";
//                echo 'https wrapper: ', in_array('https', $w) ? 'yes':'no', "<br>";
//                echo 'wrappers: ', var_dump($w);

                //$remoteFile = 'file:\"' . $compressedFileLocation;
                // \\140.251.33.101\Gross\S13-12343
                //$fileTest = "collageimage://S14-571/S14-571_1.jpg";
                //$contentFile = file_get_contents($fileTest);
                //$contentFile = file_get_contents('collageimage://S13-12343/S13-12343_1.jpg');

                $fileTest = '\\\\Collage\\Gross\\S14-571\\S14-571_1.jpg';
                //$fileTest = '//Collage/Gross/S14-571/S14-571_1.jpg';
                $contentFile = fopen($fileTest,"r");
                exit('1');

                $fileTest = "file://collage.med.cornell.edu/Gross/S14-571/S14-571_1.jpg";
                $contentFile = file_get_contents($fileTest);
                exit('1');

                //$smbc = new smbclient("//collage.med.cornell.edu/Gross", 'svc_aperio_spectrum', 'Aperi0,123');
                //$contentFile = file_get_contents($smbc);

                //if (!$smbc->get ('path/to/desired/file.txt', '/tmp/localfile.txt'))
//                if( !$smbc->get($compressedFileLocation, 'C:/tmp/localfile.txt') )
//                {
//                    print "Failed to retrieve file:\n";
//                    print join ("\n", $smbc->get_last_stdout());
//
//                }
//                else
//                {
//                    print "Transferred file successfully.";
//                }
//                exit('1');

                $compressedFileLocationConverted = str_replace("\\","/",$compressedFileLocation);
                $remoteFile = 'file:' . $compressedFileLocationConverted;
                echo "remoteFile=".$remoteFile."<br>";

                $urlTest = '<a href="'.$fileTest.'">Test Download</a>';
                echo $urlTest."<br>";

                $contentFile = file_get_contents($remoteFile);

                $size = filesize($contentFile);
                echo "size=".$size."<br>";

                $contentFlagOk = true;

                }
            }


            if( 0 )  {
                function SMBMap($username, $password, $server, $dir) {
                    $command = "mount -t smbfs -o username=$username,password=$password //$server/$dir /mnt/tmp";
                    echo "command=".$command."<br>";
                    echo system($command);
                }

                function SMBRelease() {
                    $command = "umount /mnt/tmp";
                    echo system($command);
                }

                function GetFiles($dir) {
                    $files = array();
                    if (is_dir($dir)) {
                        if ($dh = opendir($dir)) {
                            while (($file = readdir($dh)) !== false) {
                                $files[] = $file."{".filetype("$dir/$file")."}";
                            }
                            closedir($dh);
                        }
                    }
                    return $files;
                }

                SMBMap("svc_aperio_spectrum", "Aperi0,123", "140.251.33.101", "Gross");
                $any = GetFiles("/S14-571/");
                SMBRelease();
                print_r($any);
                exit('1');
            }

            if( 0 ) {
                system('net use K: \\servername\sharename /user:username password /persistent:no');
                $share = opendir('\\\\servername\\sharename');
            }

            if( 0 ) {

                //collage.med.cornell.edu/Gross/S14-571/S14-571_1.jpg
                //"smb://user:pass@server/share/path";

                include "smb.php";
                $dir = "smb://Collage/Gross";
                //$dir = "smb://svc_aperio_spectrum:Aperi0,123@Collage/Gross";

                //$url =

                if( is_dir($dir) ) {
                    $dh = opendir($dir);
                    $count = 0;
                    while( ($file=readdir($dh)) !== false && $count < 3 ) {
                        echo "filename: $file - filetype: ".filetype($dir.'/'.$file)."<br>";
                        $count++;
                    }
                    closedir($dh);
                }
                else {
                    echo $dir." not found!!!<br>";
                }

            }

            if( 0 ) {

                $smb = new smbclient("collage.med.cornell.edu","svc_aperio_spectrum","Aperi0,123");

                $remoteFile = "\\\\Collage\\Gross\\S14-571\\S14-571_1.jpg";
                $localFile = "C:\tmp\test.jpg";

                $res = $smb->get($remoteFile,$localFile);
                echo "res=".$res."<br>";
                exit('1');
            }

            if( 0 ) {

                $dirs = array(
                    "\\Collage\testoleg",
                    "\\\\Collage\\testoleg",
                    "//Collage/testoleg/",
                    "//collage/testoleg/image.jpg",
                    "\\\\collage\\testoleg\\image.jpg",
                    "//collage/testoleg/",
                    "//collage/testoleg",
                    "file://collage/testoleg",
                    "file:///collage/E:/testoleg",
                    "//Collage/E:/testoleg",
                    "//collage.med.cornell.edu/testoleg"
                );

                foreach( $dirs as $dir ) {
                    if( is_dir($dir) ){
                        echo "success <br>";
                    } else {
                        echo "failed dir=".$dir."<br>";
                    }
                    //$contentFile = file_get_contents($dir."/image.jpg");
                    $content = $this->get_content($dir);
                    echo " => ";
                    var_dump ($content);

                    $returned_content = $this->get_data($dir);
                    echo " => ";
                    var_dump ($returned_content);
                }

                $contentFile = file_get_contents("file://collage/testoleg/image.jpg");

                $content = $this->get_content("file://///Collage/testoleg/image.jpg");
                var_dump ($content);
                echo "<br>";

                $returned_content = $this->get_data("file://\\Collage\testoleg\image.jpg");
                var_dump ($returned_content);
                echo "<br>";

                //curl file://\\Collage\sharedrive\testoleg\image.jpg
                //curl -k --user svc_aperio_spectrum -1 -o filename "ftp://collage.med.cornell.edu/testoleg/image.jpg"

            }

            if( 1 ) {

                //$homepage = file_get_contents('http://collage.med.cornell.edu/order/');
                //echo $homepage;

                //$lines = file('file://collage/testoleg/image.jpg');

                //$contents = file_get_contents("\\\\collage\\testoleg\\image.jpg");
                //echo $contents;

                //$contents = file_get_contents("//collage/testoleg/image.jpg");
                //echo $contents;

                $lurl = $this->get_fcontent("\\\\collage\\testoleg\\image.jpg");
                echo"cid:".$lurl[0]."<BR>";
            }

            if( $contentFlagOk ) {
                $response->headers->set('Content-Type', 'application/unknown');
                $response->headers->set('Content-Description', 'File Transfer');
                $response->headers->set('Content-Disposition', 'attachment; filename="'.$originalname.'"');
                $response->headers->set('Content-Length', $size);
                $response->headers->set('Content-Transfer-Encoding', 'binary');
            }



        } else {
            $contentFile = 'error';
        }

        $response->setContent($contentFile);

        //exit('exit imageFileAction');

        return $response;
    }


    public function get_content($url)
    {
        $ch = curl_init();

        curl_setopt ($ch, CURLOPT_URL, $url);
        curl_setopt ($ch, CURLOPT_HEADER, 0);

        ob_start();

        curl_exec ($ch);
        curl_close ($ch);
        $string = ob_get_contents();

        ob_end_clean();

        return $string;
    }

    public function get_data($url) {
        $ch = curl_init();
        $timeout = 5;
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        $data = curl_exec($ch);
        curl_close($ch);
        return $data;
    }

    public function get_fcontent( $url,  $javascript_loop = 0, $timeout = 5 ) {
        $url = str_replace( "&amp;", "&", urldecode(trim($url)) );

        $cookie = tempnam ("/tmp", "CURLCOOKIE");
        $ch = curl_init();
        curl_setopt( $ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows; U; Windows NT 5.1; rv:1.7.3) Gecko/20041001 Firefox/0.10.1" );
        curl_setopt( $ch, CURLOPT_URL, $url );
        curl_setopt( $ch, CURLOPT_COOKIEJAR, $cookie );
        curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, true );
        curl_setopt( $ch, CURLOPT_ENCODING, "" );
        curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
        curl_setopt( $ch, CURLOPT_AUTOREFERER, true );
        curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );    # required for https urls
        curl_setopt( $ch, CURLOPT_CONNECTTIMEOUT, $timeout );
        curl_setopt( $ch, CURLOPT_TIMEOUT, $timeout );
        curl_setopt( $ch, CURLOPT_MAXREDIRS, 10 );
        $content = curl_exec( $ch );
        $response = curl_getinfo( $ch );
        curl_close ( $ch );

        if ($response['http_code'] == 301 || $response['http_code'] == 302) {
            ini_set("user_agent", "Mozilla/5.0 (Windows; U; Windows NT 5.1; rv:1.7.3) Gecko/20041001 Firefox/0.10.1");

            if ( $headers = get_headers($response['url']) ) {
                foreach( $headers as $value ) {
                    if ( substr( strtolower($value), 0, 9 ) == "location:" )
                        return get_url( trim( substr( $value, 9, strlen($value) ) ) );
                }
            }
        }

        if (    ( preg_match("/>[[:space:]]+window\.location\.replace\('(.*)'\)/i", $content, $value) || preg_match("/>[[:space:]]+window\.location\=\"(.*)\"/i", $content, $value) ) && $javascript_loop < 5) {
            return get_url( $value[1], $javascript_loop+1 );
        } else {
            return array( $content, $response );
        }
    }



    //$em = $this->getDoctrine()->getManager();

    //$document = $em->getRepository('OlegUserdirectoryBundle:Document')->find($id);

    //AperioAuthentication
    //Array ( [ReturnCode] => 0 [ReturnText] => [Token] => g9_qgXEA7Q2aMKLzYsdHv3yFn0HaUjhtOvDhZgIaipW47PMTJQryCQ==
    //[UserId] => 3 [FullName] => Administrator [LoginName] => administrator [Phone] => [E_Mail] => [LastLoginTime] => 2015-05-13 14:23:37
    //[PasswordDaysLeft] => -1 [UserMustChangePassword] => False [StartPage] => [AutoView] => [ViewingMode] => [DisableLicenseWarning] => [ScanDataGroupId] => )

    //$user = $this->get('security.context')->getToken()->getUser();


//        //print_r($this->get('session'));
//        $sessionId = $this->get('session')->getId();
//        //$sessionId = 'es1yx28s0rccwcc4s0oog8g04gg4cwg';
//        echo "session id=".$sessionId."<br>";
//
////        echo "php session:<br>";
////        print_r($_SESSION);
////        echo "<br>";
//
//        //DataServer Error: -7002: Failed to execute method DataServer.ImageProxy.GetRecordImages: Token is invalid or has timed out.
//		$_SESSION['AuthToken'] = $sessionId;
//
//        //echo "session:<br>";
//        //print_r($_SESSION ['AuthToken']);
//        //echo "<br>";
//
//        //imageId=23
//        //url=\\win-vtbcq31qg86\images\1376592217_1368_3005ER.svs
//
//        $Id = $imageid;
//        $TableName = $tablename;
//
//        echo "ADB_GetRecordImages: Id=".$Id.", TableName=".$TableName."<br>";
//        $RecordImages = ADB_GetRecordImages($Id, $TableName);
//        echo "Slide's RecordImages count=".count($RecordImages)."<br>";
//
//        foreach( $RecordImages as $image ) {
//            echo $TableName." image:<br>";
//            print_r($image);
//            echo "<br>";
//
//            $ImageFile = new \cImageFile();
//
//            $ImageFile->SetFilePath($image['CompressedFileLocation']);
//            $ImageServerURL = $ImageFile->GetURL();
//
//            echo $TableName.": ImageServerURL=".$ImageServerURL."<br>";
//            echo $TableName.": ImageId=".$Id."<br>";
//
//        }


} 