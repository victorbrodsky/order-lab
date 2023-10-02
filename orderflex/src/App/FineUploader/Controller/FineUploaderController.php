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

namespace App\FineUploader\Controller;

use App\FineUploader\UploadHandler;
use App\UserdirectoryBundle\Controller\OrderAbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class FineUploaderController extends OrderAbstractController
{

    //FineUploader server
    //https://github.com/t12ung/php-traditional-server/blob/master/endpoint.php
    #[Route(path: '/upload-chunk-file/{uuid}', name: 'fineuploader_upload_chunk_file', methods: ['GET','POST','DELETE'], options: ['expose' => true])]
    public function uploadChunkFileAction(Request $request, $uuid=null)
    {
        if (false === $this->isGranted('ROLE_PLATFORM_ADMIN')) {
            return $this->redirect($this->generateUrl('employees-nopermission'));
        }

        $logger = $this->container->get('logger');
        $result = array('error' => 'Logical error');

        //Get target folder to keep the final uploaded file
        //The uploaded file will be stored in this folder with uuid as a prefix
        $userSecUtil = $this->container->get('user_security_utility');
        $networkDrivePath = $userSecUtil->getSiteSettingParameter('networkDrivePath');

        $uploader = new UploadHandler();

        // Specify the list of valid extensions, ex. array("jpeg", "xml", "bmp")
        $uploader->allowedExtensions = array(); // all files types allowed by default

        // Specify max file size in bytes.
        //$uploader->sizeLimit = 10 * 1024 * 1024; // default is 10 MiB
        $uploader->sizeLimit = null;

        // Specify the input name set in the javascript.
        $uploader->inputName = "qqfile"; // matches Fine Uploader's default inputName value by default

        //temp folder to keep uploaded file
        $uploadDir = 'Uploaded' . DIRECTORY_SEPARATOR . "temp"; $this->getParameter('employees.uploadpath');

        // If you want to use the chunking/resume feature, specify the folder to temporarily save parts.
        $uploader->chunksFolder = $uploadDir.DIRECTORY_SEPARATOR."chunks";

        $method = $this->get_request_method();
        //echo "method=$method<br>";
        //$method = $_SERVER["REQUEST_METHOD"];

        if ($method == "POST") {
            header("Content-Type: text/plain");

            // Assumes you have a chunking.success.endpoint set to point here with a query parameter of "done".
            // For example: /myserver/handlers/endpoint.php?done
            if (isset($_GET["done"])) {
                $result = $uploader->combineChunks($uploadDir,$networkDrivePath,null,null); //,null,$logger
            }
            // Handles upload requests
            else {
                // Call handleUpload() with the name of the folder, relative to PHP's getcwd()
                $result = $uploader->handleUpload($uploadDir,$networkDrivePath,null,null); //,null,$logger

                // To return a name used for uploaded file you can use the following line.
                $result["uploadName"] = $uploader->getUploadName();
            }

            //$finalFileName = $uploader->getUploadName();
            //echo json_encode($result);
        }
        // for delete file requests
        else if ($method == "DELETE") {

//            dump($_FILES);
//            dump($_REQUEST);
//            exit('111');

            //$result = $uploader->handleDelete($uploadDir,$networkDrivePath);
            $result = $uploader->handleFinalTargetDelete($networkDrivePath);
            //echo json_encode($result);
        }
        else {
            header("HTTP/1.0 405 Method Not Allowed");
            $result = array('error' => 'Method Not Allowed');
        }

        $response = new Response();
        $response->setContent(json_encode($result));
        return $response;
    }

    // This will retrieve the "intended" request method.  Normally, this is the
    // actual method of the request.  Sometimes, though, the intended request method
    // must be hidden in the parameters of the request.  For example, when attempting to
    // delete a file using a POST request. In that case, "DELETE" will be sent along with
    // the request in a "_method" parameter.
    function get_request_method() {
        global $HTTP_RAW_POST_DATA;

        if(isset($HTTP_RAW_POST_DATA)) {
            parse_str($HTTP_RAW_POST_DATA, $_POST);
        }

        if (isset($_POST["_method"]) && $_POST["_method"] != null) {
            return $_POST["_method"];
        }

        return $_SERVER["REQUEST_METHOD"];
    }

}