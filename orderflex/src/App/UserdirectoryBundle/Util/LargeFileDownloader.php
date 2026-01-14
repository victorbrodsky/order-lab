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

namespace App\UserdirectoryBundle\Util;

/** 
* @author     Jack Mason 
* @website    volunteer @ http://www.osipage.com, web access application and bookmarking tool.   
* @copyright Free script, use anywhere as you like, no attribution required
* @created    2014 
* The script is capable of downloading really large files in PHP. Files greater than 2GB may fail in 32-bit windows or similar system.
* All incorrect headers have been removed and no nonsense code remains in this script. Should work well.
* The best and most recommended way to download files with PHP is using xsendfile, learn 
* more here: https://tn123.org/mod_xsendfile/
*/


class LargeFileDownloader {

    protected $logger;

    function __construct( $logger=null ) {
        /* You may need these ini settings too */
        set_time_limit(600);
        ini_set('memory_limit', '2048M');

        if( $logger ) {
            $this->logger = $logger;
        }
    }


    //download large files
    //tested on 8GB file http://c.med.cornell.edu/order/scan/image-viewer/pacsvendor%20eSlide%20Manager%20on%20C.MED.CORNELL.EDU/Download/Slide/53748
    public function downloadLargeFile( $filepath, $filename=null, $size=null, $retbytes=true, $action="download", $viewType=null ) {

        //exit ("downloadLargeFile: filepath=".$filepath. ", filename=" . $filename. ", size=".$size);
        //$this->logger->notice("downloadLargeFile: downloadLargeFile: filepath=".$filepath. ", filename=" . $filename. ", size=".$size);
        //For multitenancy: filepath=http://view.online:80/Uploaded/directory/avatars/avatar/20240708194741

        $filenameClean = str_replace("\\", "/", $filepath);
        //exit ("downloadLargeFile: filenameClean=$filenameClean");

        if( empty($filenameClean) ) {
            exit ("File $filenameClean is not available");
            //exit;
        }

        if( !$filename ) {
            //exit; //exit if file is not provided i.e. "https://view.med.cornell.edu//" (i.e. project ID 3374)
            $filename = basename($filenameClean);
        }

//        if( file_exists($filenameClean) ) {
//            echo "2file exists [$filenameClean]<br>";
//        } else {
//            echo "2file does not exist [$filenameClean]<br>";
//        }

        if( !$size ) {
            //filesize can not get size for http/https http://view.online:80/Uploaded/directory/avatars/avatar/20240708194741
            $size = filesize($filenameClean); //Returns the size of the file in bytes, or FALSE (and generates an error of level E_WARNING) in case of an error.
        }
        //$this->logger->notice("downloadLargeFile: after filesize 1 size=$size");

        //echo $filenameClean.": size=".$size."<br>";
        //echo "filename=".$filename."<br>";
        //exit('111');
        //remove commas
        $filename = str_replace(",", "_", $filename);

        ///// remove dots except extension /////
        $dotscount = substr_count($filename, '.');
        if( $dotscount > 1 ) {
            $ext = pathinfo($filename, PATHINFO_EXTENSION);
            if ($ext) {
                $filename = str_replace("." . $ext, "", $filename);

                //sanitize http://stackoverflow.com/questions/2021624/string-sanitizer-for-filename
                // Remove anything which isn't a word, whitespace, number
                // or any of the following caracters -_~,;[]().
                // If you don't need to handle multi-byte characters
                // you can use preg_replace rather than mb_ereg_replace
                // Thanks @Lukasz Rysiak!
                $filename = mb_ereg_replace("([^\w\s\d\-_~,;\[\]\(\).])", '_', $filename);
                // Remove any runs of periods (thanks falstro!)
                $filename = mb_ereg_replace("([\.]{2,})", '_', $filename);

                $filename = str_replace(".", "_", $filename);
                $filename = str_replace("(", "_", $filename);
                $filename = str_replace(")", "_", $filename);
                $filename = str_replace(" ", "_", $filename);

                $filename = $filename . "." . $ext;
            }
        }
        //echo "filepath=".$filepath."<br>";
        //echo "filename=".$filename."<br>";
        //echo "filenameClean=".$filenameClean."<br>";
        //echo "size=".$size."<br>";
        //var_dump(stream_get_wrappers());
//        if( is_readable($filepath) ) {
//            echo "file readable [$filepath]<br>";
//        } else {
//            echo "file is not readable [$filepath]<br>";
//        }
        //exit('111');
        ///// EOF remove dots except extension /////

        $mimeType = $this->getMimeType($filename);
        //echo "readfile filename=".$filename."; mimeType=".$mimeType."; viewType=".$viewType."<br>";
        //exit("111");

        //$this->logger->notice("downloadLargeFile: before filesize 2 size=$size");
        if( $mimeType ) {
            if( !$size ) {
                //$this->logger->notice('downloadLargeFile: filenameClean='.$filenameClean.", size=$size");
                //logger -> http://view.online/c/demo-institution/demo-department/Uploaded/fellapp/documents/1765962108_jessica-santiago.jpeg
                $size = filesize($filenameClean); //Returns the size of the file in bytes, or FALSE (and generates an error of level E_WARNING) in case of an error.
                //$this->logger->notice('downloadLargeFile: 1 size='.$size);
            }
        }

        //echo "readfile filename=".$filename."; mimeType=".$mimeType."; viewType=".$viewType."; size=".$size."<br>";
        //echo "readfile filenameClean=".$filenameClean."; size=".$size."<br>";
        //dump($this->getFileContent($filenameClean));
        //exit('111');

//        $size = $size."000";
//        //$size = 1460000;
//        header('Content-Disposition: inline; filename=' . $filename);
//        header('Accept-Ranges: bytes');
//        header('Content-Type: ' . $mimeType);
//        //header('Expires: 0');
//        //header('Cache-Control: must-revalidate');
//        //header('Pragma: public');
//        //header('Content-Length: ' . $size);
        //echo $this->getFileContent($filenameClean);
        //$filecontent = file_get_contents($filenameClean); //, false, stream_context_create($arrContextOptions));
        //dump(file_get_contents($filenameClean));
        //exit;

        if(1) {
            //header('Content-Description: File Transfer');
            header('Content-Type: ' . $mimeType);
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            //header('Content-Length: ' . $size); //this caused the error viewing file
            //header("Content-Range: 0-".($size-1)."/".$size);

            if ($action == "download") {
                header('Content-Disposition: attachment; filename=' . $filename);
            } elseif ($action == "view") {
                header('Content-Disposition: inline; filename=' . $filename);
                header('Accept-Ranges: bytes');
            } else {
                //default as "download" action
                header('Content-Disposition: attachment; filename=' . $filename);
            }
        }

        //if( $viewType == 'snapshot' ) {
        if( strpos((string)$viewType, 'snapshot') !== false ) {
            //TODO: fix image resize
            //we will need at least two thumbnails for each avatar -
            // one big image for the application view/edit page and
            // one smaller for list of the applications or vacation requests. 
            //Not big difference is speed. For example, for fellapp list 30 sec with images vs 28 sec without images
            if(0) {
                $viewTypeArr = explode("-", $viewType);
                if (count($viewTypeArr) > 1) {
                    $resize = $viewTypeArr[1];
                } else {
                    $resize = null;
                }
            }

            $resize = null; //disable resize for now. //use pre-generated thumbnail instead

            if( !$resize ) {
                echo $this->getFileContent($filenameClean);
            } else {
                //don't use this. Use pre-generated thumbnail instead.
                if(0) {
                    //snapshot is used in diff pages (use snapshot small, medium, large?).
                    //exit('111');
                    $width = $resize;
                    $height = $resize;
                    //$resizedImg = $this->Img_Resize($filenameClean,2);
                    $resizedImg = $this->resizeImage($filenameClean, $width, $height);
                    //$resizedImg = $this->croppedThumbnail($filenameClean, $width, $height);
                    //$resizedImg = $filenameClean; //testing
                    //echo file_get_contents($resizedImg);
                    //readfile($resizedImg);
                    echo $this->getFileContent($resizedImg);
                }
            }

        } else {
            //$useAlwaysRequalrFileContent = false; //readfile_chunked does not work
            $useAlwaysRequalrFileContent = true;
            //use regular readfile for file less than 3000000=>3 000 000 bytes => 3MB
            if( $useAlwaysRequalrFileContent || $size < 3000000 ) {
                //readfile($filenameClean); //use for files less than 10MB => 10000000 bytes
                //echo "filenameClean=".$filenameClean."<br>";
                //echo file_get_contents($filenameClean);

                //echo "size is < 3000000 <br>";
                echo $this->getFileContent($filenameClean);

            } else {
                //echo "size is > 3000000 <br>";
                $this->readfile_chunked($filenameClean);
            }
        }

        exit;
        return;
    }

    function urlExists($url){
        $headers=get_headers($url);
        return stripos($headers[0],"200 OK")?true:false;
    }
    function does_url_exists($url) {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_NOBODY, true);
        curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if ($code == 200) {
            $status = true;
        } else {
            $status = false;
        }
        curl_close($ch);
        return $status;
    }

    public function getFileContent($filenameClean) {
        $arrContextOptions=array(
            "ssl"=>array(
                "verify_peer"=>false,
                "verify_peer_name"=>false,
            ),
        );

        //$use_include_path = FILE_USE_INCLUDE_PATH; //or true
        $use_include_path = false;

        //echo "before get content filenameClean=$filenameClean<br>";
        //exit('000');
        //TODO: check for possible error: Failed to open stream: HTTP request failed! HTTP/1.1 404 Not Found
        //$response = file_get_contents($filenameClean, $use_include_path, stream_context_create($arrContextOptions));
        $response = NULL;
        try {
            $response = file_get_contents($filenameClean, $use_include_path, stream_context_create($arrContextOptions));
        }
        catch (\Exception $ignored)
        {
            // do nothing... php will ignore and continue
            // but maybe use "ignored" as name to silence IDE warnings.
            //$logger = $this->container->get('logger');
            //$logger->error("Error in file_get_contents: ".$ignored);
        }

        //exit('111');
        return $response;
    }

    //form: http://php.net/manual/en/function.readfile.php
    public function readfile_chunked($filename,$retbytes=true) {
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
                $cnt += strlen((string)$buffer);
            }
        }
        $status = fclose($handle);
        if ($retbytes && $status) {
            return $cnt; // return num. bytes delivered like readfile() does.
        }
        return $status;
    }

    public function getMimeType($filename) {
        $mimeType = 'application/octet-stream';

        //extension
        $ext = pathinfo($filename, PATHINFO_EXTENSION);
        if( $ext == 'pdf' ) {
            $mimeType = 'application/pdf';
        }
        if( $ext == 'doc' || $ext == 'docx' ) {
            $mimeType = 'application/msword';
        }
        if( $ext == 'xlc' || $ext == 'xls' ) {
            $mimeType = 'application/vnd.ms-excel';
        }
        if( $ext == 'xlsx' ) {
            $mimeType = 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';
        }
        if( $ext == 'jpe' || $ext == 'jpeg' || $ext == 'jpg' ) {
            $mimeType = 'image/jpeg';
        }
        if( $ext == 'bmp' ) {
            $mimeType = 'image/bmp';
        }
        if( $ext == 'gif' ) {
            $mimeType = 'image/gif';
        }
        if( $ext == 'tif' ) {
            $mimeType = 'image/tif';
        }

        return $mimeType;
    }




    //resize the image by proportion
    function Img_Resize($path,$proportion) {

        $x = getimagesize($path);
        $width  = $x['0'];
        $height = $x['1'];

        $rs_width  = $width / $proportion;//resize to half of the original width.
        $rs_height = $height / $proportion;//resize to half of the original height.

        switch ($x['mime']) {
            case "image/gif":
                $img = imagecreatefromgif($path);
                break;
            case "image/jpeg":
                $img = imagecreatefromjpeg($path);
                break;
            case "image/png":
                $img = imagecreatefrompng($path);
                break;
        }

        $img_base = imagecreatetruecolor($rs_width, $rs_height);
        imagecopyresized($img_base, $img, 0, 0, 0, 0, $rs_width, $rs_height, $width, $height);

        $path_info = pathinfo($path);
        switch ($path_info['extension']) {
            case "gif":
                imagegif($img_base, $path);
                break;
            case "jpeg":
            case "jpg":
                imagejpeg($img_base, $path);
                break;
            case "png":
                imagepng($img_base, $path);
                break;
        }

    }
    /**
     * Resize an image and keep the proportions
     * @author Allison Beckwith <allison@planetargon.com>
     * @param string $filename
     * @param integer $max_width
     * @param integer $max_height
     * @return image
     */
    function resizeImage($filename, $max_width, $max_height) {
        // Get new dimensions
        list($orig_width, $orig_height) = getimagesize($filename);

        $width = $orig_width;
        $height = $orig_height;

        # taller
        if ($height > $max_height) {
            $width = ($max_height / $height) * $width;
            $height = $max_height;
        }

        # wider
        if ($width > $max_width) {
            $height = ($max_width / $width) * $height;
            $width = $max_width;
        }

        // Resample
        $image_p = imagecreatetruecolor($width, $height);

        $image = imagecreatefromjpeg($filename);

        imagecopyresampled($image_p, $image, 0, 0, 0, 0,
            $width, $height, $orig_width, $orig_height);

        // Output
        return imagejpeg($image_p, null, 100);
    }
    function croppedThumbnail($imgSrc,$thumbnail_width,$thumbnail_height) { //$imgSrc is a FILE - Returns an image resource.
        //getting the image dimensions
        list($width_orig, $height_orig) = getimagesize($imgSrc);
        $myImage = imagecreatefromjpeg($imgSrc);
        $ratio_orig = $width_orig/$height_orig;

        if ($thumbnail_width/$thumbnail_height > $ratio_orig) {
            $new_height = $thumbnail_width/$ratio_orig;
            $new_width = $thumbnail_width;
        } else {
            $new_width = $thumbnail_height*$ratio_orig;
            $new_height = $thumbnail_height;
        }

        $x_mid = $new_width/2;  //horizontal middle
        $y_mid = $new_height/2; //vertical middle

        $process = imagecreatetruecolor(round($new_width), round($new_height));

        imagecopyresampled($process, $myImage, 0, 0, 0, 0, $new_width, $new_height, $width_orig, $height_orig);
        $thumb = imagecreatetruecolor($thumbnail_width, $thumbnail_height);
        imagecopyresampled($thumb, $process, 0, 0, ($x_mid-($thumbnail_width/2)), ($y_mid-($thumbnail_height/2)), $thumbnail_width, $thumbnail_height, $thumbnail_width, $thumbnail_height);

        imagedestroy($process);
        imagedestroy($myImage);
        //return $thumb;

        return imagejpeg($thumb, null, 100);
    }



    ////////////////////// NOT USED BELOW //////////////////////
    //Does not work properly
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
                  //readfile($filePath);
                  echo $this->getFileContent($filePath);
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