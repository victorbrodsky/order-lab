<?php

/**
 * PHP Server-Side Example for Fine Uploader (traditional endpoint handler).
 * Maintained by Widen Enterprises.
 *
 * This example:
 *  - handles chunked and non-chunked requests
 *  - supports the concurrent chunking feature
 *  - assumes all upload requests are multipart encoded
 *  - handles delete requests
 *  - handles cross-origin environments
 *
 * Follow these steps to get up and running with Fine Uploader in a PHP environment:
 *
 * 1. Setup your client-side code, as documented on http://docs.fineuploader.com.
 *
 * 2. Use composer to install to your project:
 *    composer require t12ung/php-traditional-server
 *
 * 3. Ensure your php.ini file contains appropriate values for
 *    max_input_time, upload_max_filesize and post_max_size.
 *
 * 4. Ensure your "chunks" and "files" (or $uploadFolder) folders exist and are writable.
 *    "chunks" is only needed if you have enabled the chunking feature client-side.
 *
 * 5. If you have chunking enabled in Fine Uploader, you MUST set a value for the `chunking.success.endpoint` option.
 *    This will be called by Fine Uploader when all chunks for a file have been successfully uploaded, triggering the
 *    PHP server to combine all parts into one file. This is particularly useful for the concurrent chunking feature,
 *    but is now required in all cases if you are making use of this PHP example.
 */

namespace App\FineUploader;

//Credire to https://github.com/t12ung/php-traditional-server/blob/master/src/FileUploader.php
//Example how to use. No used directly.

class FileUploader
{
    private $uploader;
    private $_HEADERS;
    private $uploadFolder = 'files';
    private $callback = [
        'upload' => null,
        'delete' => null
    ];
    public $method;

    public function __construct()
    {
        $this->uploader = new UploadHandler();
        $this->method = $this->get_request_method();
        $this->_HEADERS = $this->parseRequestHeaders();
    }

    public function setConfig(array $config)
    {
        if (!empty($config)) {

            if (!empty($config['restrictTypes']) && is_array($config['restrictTypes'])) {
                // Specify the list of valid extensions, ex. array("jpeg", "xml", "bmp").
                // All files types allowed by default.
                $this->uploader->allowedExtensions = $config['restrictTypes'];
            }

            if (!empty($config['sizeLimit']) && is_integer($config['sizeLimit'])) {
                // Specify max file size in bytes.
                $this->uploader->sizeLimit = $config['sizeLimit'];
            }

            if (!empty($config['inputName']) && is_string($config['inputName'])) {
                // Specify the input name set in the javascript.
                // Matches Fine Uploader's default inputName value 'qqfile' by default.
                $this->uploader->inputName = $config['inputName'];
            }

            if (!empty($config['chunksFolder']) && is_string($config['chunksFolder'])) {
                // If you want to use the chunking/resume feature, specify the folder to temporarily save parts.
                // Defaults to 'chunks'.
                $this->uploader->chunksFolder = $config['chunksFolder'];
            }

            if (!empty($config['uploadFolder']) && is_string($config['uploadFolder'])) {
                // The directory used to save uploads to, default "files"
                $this->uploadFolder = rtrim($config['uploadFolder'], DIRECTORY_SEPARATOR);
            }

            if (!empty($config['callback']) && is_array($config['callback'])) {
                // The callback methods to execute on the file before sending a response.
                // The uploaded destination filepath is passed as the first parameter automatically,
                // followed by any other defined callback parameters.
                // Example structure: 'upload' => ['callback' => <callable type>, 'param' => <single param or array>]
                $callback = $config['callback'];
                foreach ($callback as $method => $c) {
                    if ( in_array($method, array_keys($this->callback)) && !empty($c['callable']) ) {
                        if (is_callable($c['callable'])) {
                            $this->callback[$method] = $c;
                        }
                    }
                }
            }

        } else {
            return false;
        }

        return $this;
    }

    // This will retrieve the "intended" request method.  Normally, this is the
    // actual method of the request.  Sometimes, though, the intended request method
    // must be hidden in the parameters of the request.  For example, when attempting to
    // send a DELETE request in a cross-origin environment in IE9 or older, it is not
    // possible to send a DELETE request.  So, we send a POST with the intended method,
    // DELETE, in a "_method" parameter.
    private function get_request_method()
    {
        global $HTTP_RAW_POST_DATA;

        // This should only evaluate to true if the Content-Type is undefined
        // or unrecognized, such as when XDomainRequest has been used to
        // send the request.
        if(isset($HTTP_RAW_POST_DATA)) {
            parse_str($HTTP_RAW_POST_DATA, $_POST);
        }

        if (isset($_POST["_method"]) && $_POST["_method"] != null) {
            return $_POST["_method"];
        }

        return $_SERVER["REQUEST_METHOD"];
    }

    private function parseRequestHeaders()
    {
        $headers = array();
        foreach($_SERVER as $key => $value) {
            if (substr($key, 0, 5) <> 'HTTP_') {
                continue;
            }
            $header = str_replace(' ', '-', ucwords(str_replace('_', ' ', strtolower(substr($key, 5)))));
            $headers[$header] = $value;
        }
        return $headers;
    }

    private function checkIframe(): bool
    {
        if (!isset($this->_HEADERS['X-Requested-With']) || $this->_HEADERS['X-Requested-With'] != "XMLHttpRequest") {
            return true;
        }
        return false;
    }

    private function handleCorsRequest() {
        header("Access-Control-Allow-Origin: *");
    }

    /*
     * iframe uploads require the content-type to be 'text/html' and return some JSON
     * along with self-executingjavascript (iframe.ss.response) that will parse the JSON
     * and pass it along to Fine Uploader via window.postMessage
     */
    private function jsonResponse(array $result)
    {
        if ($this->checkIframe() == true) {
            header("Content-Type: text/html");
            echo json_encode($result) . "<script src='/node_modules/fine-uploader/fine-uploader/iframe.xss.response.js'></script>";
        } else {
            echo json_encode($result);
        }
        exit;
    }

    /*
     * handle pre-flighted requests. Needed for CORS operation
     */
    public function handlePreflight()
    {
        if ($this->method == "OPTIONS") {
            $this->handleCorsRequest();
            header("Access-Control-Allow-Methods: POST, DELETE");
            header("Access-Control-Allow-Credentials: true");
            header("Access-Control-Allow-Headers: Content-Type, X-Requested-With, Cache-Control");
            exit;
        } else {
            $this->invalidRequest();
        }
    }

    public function delete()
    {
        if ($this->method == "DELETE") {
            $this->handleCorsRequest();

            $result = $this->uploader->handleDelete($this->uploadFolder);

            $this->jsonResponse($result);
        } else {
            $this->invalidRequest();
        }
    }

    public function upload()
    {
        if ($this->method == "POST") {
            header("Content-Type: text/plain");

            // Assumes you have a chunking.success.endpoint set to point here with a query parameter of "done".
            // For example: /myserver/handlers/endpoint.php?done
            if (isset($_GET["done"])) {
                $result = $this->uploader->combineChunks($this->uploadFolder);
            }
            // Handles upload requests
            else {
                // Call handleUpload() with the name of the folder, relative to PHP's getcwd()
                $result = $this->uploader->handleUpload($this->uploadFolder);

                $callback = $this->callback[__FUNCTION__];
                if ($callback && !empty($callback['callable'])) {
                    $file = $this->uploader->getTargetFilePath($this->uploadFolder);
                    $func = $callback['callable'];
                    $param = $callback['param'] ?? [];
                    if ($param && !is_array($param)) $param = [$param];
                    array_unshift($param, $file);

                    $return = call_user_func_array($func, $param);
                    if ($return !== true) $result = $return;
                }

                // To return a name used for uploaded file you can use the following line.
                $result["uploadName"] = $this->uploader->getUploadName();

                $this->jsonResponse($result);
            }
        } else {
            $this->invalidRequest();
        }
    }

    private function invalidRequest()
    {
        header("HTTP/1.0 405 Method Not Allowed");
        exit;
    }

    public function initDirectories()
    {

    }

}