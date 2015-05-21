<?php
/**
 * Created by PhpStorm.
 * User: oli2002
 * Date: 10/15/14
 * Time: 11:57 AM
 */

namespace Oleg\OrderformBundle\Controller;

use Oleg\UserdirectoryBundle\Util\LargeFileDownloader;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

use Oleg\UserdirectoryBundle\Controller\UploadController;
use Symfony\Component\HttpFoundation\Response;

//error_reporting(E_ALL);
//include_once '\DatabaseRoutines.php';
//include_once '\cImageFile.php';

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
        //$compressedFileLocation = "C://Images/SampleData/1376592216_rat_liver_tox.jpg";
        //echo "compressedFileLocation Rows=".$compressedFileLocation."<br>";
        //////////////////////////////////////////////////////////

        //////////////// testing: order memory usage ////////////////
        //$mem = memory_get_usage(true);
        //echo "order mem = ".$mem. " => " .round($mem/1000000,2)." Mb<br>";
        //exit('1');
        //////////////// EOF order memory usage ////////////////

        //2) show image in Aperio's image viewer http://c.med.cornell.edu/imageserver/@@_DGjlRH2SJIRkb9ZOOr1sJEuLZRwLUhWzDSDb-sG0U61NzwQ4a8Byw==/@73660/view.apml

        $response = new Response();

        if( $compressedFileLocation ) {

            $fileLocArr = explode("\\",$compressedFileLocation);
            //$fileLocArr = explode("/",$compressedFileLocation);
            $originalFileName = $fileLocArr[ count($fileLocArr)-1 ];
            //echo "originalFileName=".$originalFileName."<br>";
            $originalname = $tablename."_Image_ID_" . $originalFileName;
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

                $originalname = $tablename."_Image_ID_" . $imageid.".sis";

                $contentFlagOk = true;
            }

//            if( $type == 'Download-SmallFile' ) {
//
//                //ini_set('memory_limit', '2048M'); //128M
//
//                $compressedFileLocationConverted = str_replace("\\","/",$compressedFileLocation);
//                $remoteFile = $compressedFileLocationConverted;
//                //echo "remoteFile=".$remoteFile."<br>";
//
//                //$urlTest = '<a href="'.$remoteFile.'">Test Download</a>';
//                //echo $urlTest."<br>";
//
//                $contentFile = file_get_contents($remoteFile);
//                //echo "contentFile=".$contentFile."<br>";
//                //exit();
//
//                $size = filesize($remoteFile);
//                //echo "size=".$size."<br>";
//
//                $contentFlagOk = true;
//            }

            if( $type == 'Download' ) {

                //$compressedFileLocation = "C:/Images/GIID-153-001.svs"; //testing file on dev

                $downloader = new LargeFileDownloader();
                $downloader->downloadLargeFile($compressedFileLocation);

                exit;
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

        //$response->setContent($contentFile);

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



    function readfile_chunked($filename,$retbytes=true) {
        $chunksize = 1*(1024*1024); // how many bytes per chunk
        $buffer = '';
        $cnt =0;
        // $handle = fopen($filename, 'rb');
        $handle = fopen($filename, 'rb');
        if ($handle === false) {
            return false;
        }
        while (!feof($handle)) {
            $buffer = fread($handle, $chunksize);
            echo $buffer;
            ob_flush();
            flush();
            if ($retbytes) {
                $cnt += strlen($buffer);
            }
        }
        $status = fclose($handle);
        if ($retbytes && $status) {
            return $cnt; // return num. bytes delivered like readfile() does.
        }
        return $status;

    }


    //THE DOWNLOAD SCRIPT
    //$filePath = "D:/Software/versions/windows/windows_7.rar"; // set your download file path here.
    //download($filePath); // calls download function
    function download($filePath)
    {
        if(!empty($filePath))
        {
            $fileInfo = pathinfo($filePath);
            $fileName  = $fileInfo['basename'];
            $fileExtnesion   = $fileInfo['extension'];
            $default_contentType = "application/octet-stream";
            $content_types_list = $this->mimeTypes();
            // to find and use specific content type, check out this IANA page : http://www.iana.org/assignments/media-types/media-types.xhtml
            if (array_key_exists($fileExtnesion, $content_types_list))
            {
                $contentType = $content_types_list[$fileExtnesion];
            }
            else
            {
                $contentType =  $default_contentType;
            }
            if(file_exists($filePath))
            {
                $size = filesize($filePath);
                $offset = 0;
                $length = $size;
                //HEADERS FOR PARTIAL DOWNLOAD FACILITY BEGINS
                if(isset($_SERVER['HTTP_RANGE']))
                {
                    preg_match('/bytes=(\d+)-(\d+)?/', $_SERVER['HTTP_RANGE'], $matches);
                    $offset = intval($matches[1]);
                    $length = intval($matches[2]) - $offset;
                    $fhandle = fopen($filePath, 'r');
                    fseek($fhandle, $offset); // seek to the requested offset, this is 0 if it's not a partial content request
                    $data = fread($fhandle, $length);
                    fclose($fhandle);
                    header('HTTP/1.1 206 Partial Content');
                    header('Content-Range: bytes ' . $offset . '-' . ($offset + $length) . '/' . $size);
                }//HEADERS FOR PARTIAL DOWNLOAD FACILITY BEGINS
                //USUAL HEADERS FOR DOWNLOAD
                header("Content-Disposition: attachment;filename=".$fileName);
                header('Content-Type: '.$contentType);
                header("Accept-Ranges: bytes");
                header("Pragma: public");
                header("Expires: -1");
                header("Cache-Control: no-cache");
                header("Cache-Control: public, must-revalidate, post-check=0, pre-check=0");
                header("Content-Length: ".filesize($filePath));
                $chunksize = 8 * (1024 * 1024); //8MB (highest possible fread length)
                if ($size > $chunksize)
                {
                    $handle = fopen($_FILES["file"]["tmp_name"], 'rb');
                    $buffer = '';
                    while (!feof($handle) && (connection_status() === CONNECTION_NORMAL))
                    {
                        $buffer = fread($handle, $chunksize);
                        print $buffer;
                        ob_flush();
                        flush();
                    }
                    if(connection_status() !== CONNECTION_NORMAL)
                    {
                        echo "Connection aborted";
                    }
                    fclose($handle);
                }
                else
                {
                    ob_clean();
                    flush();
                    readfile($filePath);
                }
            }
            else
            {
                echo 'File does not exist!';
            }
        }
        else
        {
            echo 'There is no file to download!';
        }
    }


    /* Function to get correct MIME type for download */
    public function mimeTypes()
    {
        /* Just add any required MIME type if you are going to download something not listed here.*/
        $mime_types = array("323" => "text/h323",
            "acx" => "application/internet-property-stream",
            "ai" => "application/postscript",
            "aif" => "audio/x-aiff",
            "aifc" => "audio/x-aiff",
            "aiff" => "audio/x-aiff",
            "asf" => "video/x-ms-asf",
            "asr" => "video/x-ms-asf",
            "asx" => "video/x-ms-asf",
            "au" => "audio/basic",
            "avi" => "video/x-msvideo",
            "axs" => "application/olescript",
            "bas" => "text/plain",
            "bcpio" => "application/x-bcpio",
            "bin" => "application/octet-stream",
            "bmp" => "image/bmp",
            "c" => "text/plain",
            "cat" => "application/vnd.ms-pkiseccat",
            "cdf" => "application/x-cdf",
            "cer" => "application/x-x509-ca-cert",
            "class" => "application/octet-stream",
            "clp" => "application/x-msclip",
            "cmx" => "image/x-cmx",
            "cod" => "image/cis-cod",
            "cpio" => "application/x-cpio",
            "crd" => "application/x-mscardfile",
            "crl" => "application/pkix-crl",
            "crt" => "application/x-x509-ca-cert",
            "csh" => "application/x-csh",
            "css" => "text/css",
            "dcr" => "application/x-director",
            "der" => "application/x-x509-ca-cert",
            "dir" => "application/x-director",
            "dll" => "application/x-msdownload",
            "dms" => "application/octet-stream",
            "doc" => "application/msword",
            "dot" => "application/msword",
            "dvi" => "application/x-dvi",
            "dxr" => "application/x-director",
            "eps" => "application/postscript",
            "etx" => "text/x-setext",
            "evy" => "application/envoy",
            "exe" => "application/octet-stream",
            "fif" => "application/fractals",
            "flr" => "x-world/x-vrml",
            "gif" => "image/gif",
            "gtar" => "application/x-gtar",
            "gz" => "application/x-gzip",
            "h" => "text/plain",
            "hdf" => "application/x-hdf",
            "hlp" => "application/winhlp",
            "hqx" => "application/mac-binhex40",
            "hta" => "application/hta",
            "htc" => "text/x-component",
            "htm" => "text/html",
            "html" => "text/html",
            "htt" => "text/webviewhtml",
            "ico" => "image/x-icon",
            "ief" => "image/ief",
            "iii" => "application/x-iphone",
            "ins" => "application/x-internet-signup",
            "isp" => "application/x-internet-signup",
            "jfif" => "image/pipeg",
            "jpe" => "image/jpeg",
            "jpeg" => "image/jpeg",
            "jpg" => "image/jpeg",
            "js" => "application/x-javascript",
            "latex" => "application/x-latex",
            "lha" => "application/octet-stream",
            "lsf" => "video/x-la-asf",
            "lsx" => "video/x-la-asf",
            "lzh" => "application/octet-stream",
            "m13" => "application/x-msmediaview",
            "m14" => "application/x-msmediaview",
            "m3u" => "audio/x-mpegurl",
            "man" => "application/x-troff-man",
            "mdb" => "application/x-msaccess",
            "me" => "application/x-troff-me",
            "mht" => "message/rfc822",
            "mhtml" => "message/rfc822",
            "mid" => "audio/mid",
            "mny" => "application/x-msmoney",
            "mov" => "video/quicktime",
            "movie" => "video/x-sgi-movie",
            "mp2" => "video/mpeg",
            "mp3" => "audio/mpeg",
            "mpa" => "video/mpeg",
            "mpe" => "video/mpeg",
            "mpeg" => "video/mpeg",
            "mpg" => "video/mpeg",
            "mpp" => "application/vnd.ms-project",
            "mpv2" => "video/mpeg",
            "ms" => "application/x-troff-ms",
            "mvb" => "application/x-msmediaview",
            "nws" => "message/rfc822",
            "oda" => "application/oda",
            "p10" => "application/pkcs10",
            "p12" => "application/x-pkcs12",
            "p7b" => "application/x-pkcs7-certificates",
            "p7c" => "application/x-pkcs7-mime",
            "p7m" => "application/x-pkcs7-mime",
            "p7r" => "application/x-pkcs7-certreqresp",
            "p7s" => "application/x-pkcs7-signature",
            "pbm" => "image/x-portable-bitmap",
            "pdf" => "application/pdf",
            "pfx" => "application/x-pkcs12",
            "pgm" => "image/x-portable-graymap",
            "pko" => "application/ynd.ms-pkipko",
            "pma" => "application/x-perfmon",
            "pmc" => "application/x-perfmon",
            "pml" => "application/x-perfmon",
            "pmr" => "application/x-perfmon",
            "pmw" => "application/x-perfmon",
            "pnm" => "image/x-portable-anymap",
            "pot" => "application/vnd.ms-powerpoint",
            "ppm" => "image/x-portable-pixmap",
            "pps" => "application/vnd.ms-powerpoint",
            "ppt" => "application/vnd.ms-powerpoint",
            "prf" => "application/pics-rules",
            "ps" => "application/postscript",
            "pub" => "application/x-mspublisher",
            "qt" => "video/quicktime",
            "ra" => "audio/x-pn-realaudio",
            "ram" => "audio/x-pn-realaudio",
            "ras" => "image/x-cmu-raster",
            "rgb" => "image/x-rgb",
            "rmi" => "audio/mid",
            "roff" => "application/x-troff",
            "rtf" => "application/rtf",
            "rtx" => "text/richtext",
            "scd" => "application/x-msschedule",
            "sct" => "text/scriptlet",
            "setpay" => "application/set-payment-initiation",
            "setreg" => "application/set-registration-initiation",
            "sh" => "application/x-sh",
            "shar" => "application/x-shar",
            "sit" => "application/x-stuffit",
            "snd" => "audio/basic",
            "spc" => "application/x-pkcs7-certificates",
            "spl" => "application/futuresplash",
            "src" => "application/x-wais-source",
            "sst" => "application/vnd.ms-pkicertstore",
            "stl" => "application/vnd.ms-pkistl",
            "stm" => "text/html",
            "svg" => "image/svg+xml",
            "sv4cpio" => "application/x-sv4cpio",
            "sv4crc" => "application/x-sv4crc",
            "t" => "application/x-troff",
            "tar" => "application/x-tar",
            "tcl" => "application/x-tcl",
            "tex" => "application/x-tex",
            "texi" => "application/x-texinfo",
            "texinfo" => "application/x-texinfo",
            "tgz" => "application/x-compressed",
            "tif" => "image/tiff",
            "tiff" => "image/tiff",
            "tr" => "application/x-troff",
            "trm" => "application/x-msterminal",
            "tsv" => "text/tab-separated-values",
            "txt" => "text/plain",
            "uls" => "text/iuls",
            "ustar" => "application/x-ustar",
            "vcf" => "text/x-vcard",
            "vrml" => "x-world/x-vrml",
            "wav" => "audio/x-wav",
            "wcm" => "application/vnd.ms-works",
            "wdb" => "application/vnd.ms-works",
            "wks" => "application/vnd.ms-works",
            "wmf" => "application/x-msmetafile",
            "wps" => "application/vnd.ms-works",
            "wri" => "application/x-mswrite",
            "wrl" => "x-world/x-vrml",
            "wrz" => "x-world/x-vrml",
            "xaf" => "x-world/x-vrml",
            "xbm" => "image/x-xbitmap",
            "xla" => "application/vnd.ms-excel",
            "xlc" => "application/vnd.ms-excel",
            "xlm" => "application/vnd.ms-excel",
            "xls" => "application/vnd.ms-excel",
            "xlt" => "application/vnd.ms-excel",
            "xlw" => "application/vnd.ms-excel",
            "xof" => "x-world/x-vrml",
            "xpm" => "image/x-xpixmap",
            "xwd" => "image/x-xwindowdump",
            "z" => "application/x-compress",
            "rar" => "application/x-rar-compressed",
            "zip" => "application/zip");
        return $mime_types;
    }


} 