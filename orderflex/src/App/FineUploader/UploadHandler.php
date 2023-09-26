<?php

/**
 * Do not use or reference this directly from your client-side code.
 * Instead, this should be required via the endpoint.php or endpoint-cors.php
 * file(s).
 */

//Credit to: https://github.com/t12ung/php-traditional-server/blob/master/src/UploadHandler.php


namespace App\FineUploader;

class UploadHandler {

    public $allowedExtensions = array();
    public $sizeLimit = null;
    public $inputName = 'qqfile';
    public $chunksFolder = 'chunks';

    public $chunksCleanupProbability = 0.001; // Once in 1000 requests on avg
    public $chunksExpireIn = 604800; // One week

    protected $uploadName;

    /**
     * Get the original filename
     */
    public function getName(){
        if (isset($_REQUEST['qqfilename']))
            return $_REQUEST['qqfilename'];

        if (isset($_FILES[$this->inputName]))
            return $_FILES[$this->inputName]['name'];
    }

    public function getInitialFiles() {
        $initialFiles = array();

        for ($i = 0; $i < 5000; $i++) {
            array_push(
                $initialFiles,
                array(
                    "name" => "name" + $i,
                    uuid => "uuid" + $i,
                    thumbnailUrl => "/test/dev/handlers/vendor/fineuploader/php-traditional-server/fu.png"
                )
            );
        }

        return $initialFiles;
    }

    /**
     * Get the name of the uploaded file
     */
    public function getUploadName(){
        return $this->uploadName;
    }

    public function combineChunks($uploadDirectory, $finalTargetDir, $name = null, $logger=null) {
        $uuid = $_POST['qquuid'];
        if ($name === null){
            $name = $this->getName();
        }
        $targetFolder = $this->chunksFolder.DIRECTORY_SEPARATOR.$uuid;
        $totalParts = isset($_REQUEST['qqtotalparts']) ? (int)$_REQUEST['qqtotalparts'] : 1;

        //Uploaded\directory\3021a4e7-ec84-4060-b83e-284be7e3dd40\backupdb-test-20230918-164528-scanordercopy.dump.gz
        $targetPath = join(DIRECTORY_SEPARATOR, array($uploadDirectory, $uuid, $name));
        
        //targetPath=Uploaded\directory\backupdb-test-20230918-164528-scanordercopy.dump.gz
        //$targetPath = join(DIRECTORY_SEPARATOR, array($uploadDirectory, $name));
        //$targetPath = $uploadDirectory.DIRECTORY_SEPARATOR.$name;
        //echo "targetPath=$targetPath<br>";
        
        $this->uploadName = $name;

        if( !file_exists($targetPath) ){
            mkdir(dirname($targetPath), 0777, true);
        }
        $target = fopen($targetPath, 'wb');

        for ($i=0; $i<$totalParts; $i++){
            $chunk = fopen($targetFolder.DIRECTORY_SEPARATOR.$i, "rb");
            if( $chunk !== false ) {
                stream_copy_to_stream($chunk, $target);
                fclose($chunk);
            }
        }

        // Success
        fclose($target);

        for ($i=0; $i<$totalParts; $i++){
            unlink($targetFolder.DIRECTORY_SEPARATOR.$i);
        }

        rmdir($targetFolder);

        if (!is_null($this->sizeLimit) && filesize($targetPath) > $this->sizeLimit) {
            unlink($targetPath);
            http_response_code(413);
            return array("success" => false, "uuid" => $uuid, "preventRetry" => true);
        }

        //Move file from uuid to final destination
        $this->moveToFinalTargetPath($targetPath,$finalTargetDir,$uuid,$logger);

        return array("success" => true, "uuid" => $uuid);
    }

    public function moveToFinalTargetPath( $targetPath, $finalTargetDir, $uuid, $logger=null ) {
        if($logger) $logger->notice('move ToFinalTargetPath: $targetPath='.$targetPath.', $finalTargetDir='.$finalTargetDir);

        if( !file_exists($finalTargetDir) ){
            mkdir($finalTargetDir, 0777, true);
            if($logger) $logger->notice('move ToFinalTargetPath: create $finalTargetDir='.$finalTargetDir);
        }

        $finalFileName = $finalTargetDir . DIRECTORY_SEPARATOR . $uuid."_".pathinfo($targetPath, PATHINFO_BASENAME);

        //C:\Users\ch3\Documents\MyDocs\WCMC\Backup\db_backup_manag\edd1fe1c-63a1-40a0-8358-f927f8e8e8a0_fox.jpg
        //echo "set finalFileName=".$this->finalFileName."<br>";

        //Moving files with rename()
        rename($targetPath, $finalFileName);//$finalTargetDir . DIRECTORY_SEPARATOR . $uuid."_".pathinfo($targetPath, PATHINFO_BASENAME));
        if($logger) $logger->notice('move ToFinalTargetPath: after move by rename');
        if( !file_exists($finalFileName) ){
            if($logger) $logger->notice('move ToFinalTargetPath: after move $finalFileName does not exist');
        } else {
            if($logger) $logger->notice('move ToFinalTargetPath: after move $finalFileName exist');
        }

        //Delete original file
        rmdir(dirname($targetPath));
        if($logger) $logger->notice('move ToFinalTargetPath: after rmdir');
    }

    public function getTargetFilePath($uploadDirectory) {
        $uuid = $_POST['qquuid'];
        $target = join(DIRECTORY_SEPARATOR, [$uploadDirectory, $uuid, $this->getUploadName()]);
        return $target;
    }

    /**
     * Process the upload.
     * @param string $uploadDirectory Target directory.
     * @param string $name Overwrites the name of the file.
     */
    public function handleUpload($uploadDirectory, $finalTargetDir, $name = null, $logger=null){

        if (is_writable($this->chunksFolder) &&
            1 == mt_rand(1, 1/$this->chunksCleanupProbability)){

            // Run garbage collection
            $this->cleanupChunks();
        }

        // Check that the max upload size specified in class configuration does not
        // exceed size allowed by server config
        if ($this->toBytes(ini_get('post_max_size')) < $this->sizeLimit ||
            $this->toBytes(ini_get('upload_max_filesize')) < $this->sizeLimit){
            $neededRequestSize = max(1, $this->sizeLimit / 1024 / 1024) . 'M';
            return array('error'=>"Server error. Increase post_max_size and upload_max_filesize to ".$neededRequestSize);
        }

        if ($this->isInaccessible($uploadDirectory)){
            return array('error' => "Server error. Uploads directory isn't writable");
        }

        $type = $_SERVER['CONTENT_TYPE'];
        if (isset($_SERVER['HTTP_CONTENT_TYPE'])) {
            $type = $_SERVER['HTTP_CONTENT_TYPE'];
        }

        if(!isset($type)) {
            return array('error' => "No files were uploaded.");
        } else if (strpos(strtolower($type), 'multipart/') !== 0){
            return array('error' => "Server error. Not a multipart request. Please set forceMultipart to default value (true).");
        }

        // Get size and name
        $file = $_FILES[$this->inputName];
        $size = $file['size'];
        if (isset($_REQUEST['qqtotalfilesize'])) {
            $size = $_REQUEST['qqtotalfilesize'];
        }

        if ($name === null){
            $name = $this->getName();
        }

        // check file error
        if($file['error']) {
            return array('error' => 'Upload Error #'.$file['error']);
        }

        // Validate name
        if ($name === null || $name === ''){
            return array('error' => 'File name empty.');
        }

        // Validate file size
        if ($size == 0){
            return array('error' => 'File is empty.');
        }

        if (!is_null($this->sizeLimit) && $size > $this->sizeLimit) {
            return array('error' => 'File is too large.', 'preventRetry' => true);
        }

        // Validate file extension
        $pathinfo = pathinfo($name);
        $ext = isset($pathinfo['extension']) ? $pathinfo['extension'] : '';

        if($this->allowedExtensions && !in_array(strtolower($ext), array_map("strtolower", $this->allowedExtensions))){
            $these = implode(', ', $this->allowedExtensions);
            return array('error' => 'File has an invalid extension, it should be one of '. $these . '.');
        }

        // Save a chunk
        $totalParts = isset($_REQUEST['qqtotalparts']) ? (int)$_REQUEST['qqtotalparts'] : 1;

        $uuid = $_REQUEST['qquuid'];
        if ($totalParts > 1){
            # chunked upload
            if($logger) $logger->notice('chunked upload. $totalParts='.$totalParts);

            $chunksFolder = $this->chunksFolder;
            $partIndex = (int)$_REQUEST['qqpartindex'];

            if (!is_writable($chunksFolder) && !is_executable($uploadDirectory)){
                return array('error' => "Server error. Chunks directory isn't writable or executable.");
            }

            $targetFolder = $this->chunksFolder.DIRECTORY_SEPARATOR.$uuid;

            if (!file_exists($targetFolder)){
                mkdir($targetFolder, 0777, true);
            }

            $target = $targetFolder.'/'.$partIndex;
            $success = move_uploaded_file($_FILES[$this->inputName]['tmp_name'], $target);

            return array("success" => true, "uuid" => $uuid);

        }
        else {
            # non-chunked upload
            if($logger) $logger->notice('non-chunked upload');

            $target = join(DIRECTORY_SEPARATOR, array($uploadDirectory, $uuid, $name));

            if ($target){
                $this->uploadName = basename($target);

                if (!is_dir(dirname($target))){
                    mkdir(dirname($target), 0777, true);
                }
                if (move_uploaded_file($file['tmp_name'], $target)){

                    //Move file from uuid to final destination
                    $this->moveToFinalTargetPath($target,$finalTargetDir,$uuid,$logger);

                    return array('success'=> true, "uuid" => $uuid);
                }
            }

            return array('error'=> 'Could not save uploaded file.' .
                'The upload was cancelled, or server error encountered');
        }
    }

    /**
     * Process a delete.
     * @param string $uploadDirectory Target directory.
     * @params string $name Overwrites the name of the file.
     *
     */
    public function handleDelete($uploadDirectory, $finalTargetDir, $name=null)
    {
        if ($this->isInaccessible($uploadDirectory)) {
            return array('error' => "Server error. Uploads directory isn't writable" . ((!$this->isWindows()) ? " or executable." : "."));
        }

        $targetFolder = $uploadDirectory;
        $uuid = false;
        $method = $_SERVER["REQUEST_METHOD"];
        if ($method == "DELETE") {
            $url = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
            $tokens = explode('/', $url);
            $uuid = $tokens[sizeof($tokens)-1];
        } else if ($method == "POST") {
            $uuid = $_REQUEST['qquuid'];
        } else {
            return array("success" => false,
                "error" => "Invalid request method! ".$method
            );
        }

        $target = join(DIRECTORY_SEPARATOR, array($targetFolder, $uuid));

        if (is_dir($target)){
            $this->removeDir($target);
            return array("success" => true, "uuid" => $uuid);
        } else {
            return array("success" => false,
                "error" => "File not found! Unable to delete.".$url,
                "path" => $uuid
            );
        }

    }
    /**
     * Process a delete from the final target path.
     * @param string $uploadDirectory Target directory.
     * @params string $name Overwrites the name of the file.
     *
     */
    public function handleFinalTargetDelete( $finalTargetDir  )
    {
        $uuid = false;
        $method = $_SERVER["REQUEST_METHOD"];
        if ($method == "DELETE") {
            $url = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
            $tokens = explode('/', $url);
            $uuid = $tokens[sizeof($tokens)-1];
        } else if ($method == "POST") {
            $uuid = $_REQUEST['qquuid'];
        } else {
            return array("success" => false,
                "error" => "Invalid request method! ".$method
            );
        }

        //find file by uuid
        $file = null;
        $files = glob($finalTargetDir . DIRECTORY_SEPARATOR . $uuid . "*");
        foreach($files as $file) {
            break;
        }

        //Remove from destination path
        //echo "unlink finalFileName=".$this->finalFileName."<br>";
        unlink($file);
    }

    /**
     * Returns a path to use with this upload. Check that the name does not exist,
     * and appends a suffix otherwise.
     * @param string $uploadDirectory Target directory
     * @param string $filename The name of the file to use.
     */
    protected function getUniqueTargetPath($uploadDirectory, $filename)
    {
        // Allow only one process at the time to get a unique file name, otherwise
        // if multiple people would upload a file with the same name at the same time
        // only the latest would be saved.

        if (function_exists('sem_acquire')){
            $lock = sem_get(ftok(__FILE__, 'u'));
            sem_acquire($lock);
        }

        $pathinfo = pathinfo($filename);
        $base = $pathinfo['filename'];
        $ext = isset($pathinfo['extension']) ? $pathinfo['extension'] : '';
        $ext = $ext == '' ? $ext : '.' . $ext;

        $unique = $base;
        $suffix = 0;

        // Get unique file name for the file, by appending random suffix.

        while (file_exists($uploadDirectory . DIRECTORY_SEPARATOR . $unique . $ext)){
            $suffix += rand(1, 999);
            $unique = $base.'-'.$suffix;
        }

        $result =  $uploadDirectory . DIRECTORY_SEPARATOR . $unique . $ext;

        // Create an empty target file
        if (!touch($result)){
            // Failed
            $result = false;
        }

        if (function_exists('sem_acquire')){
            sem_release($lock);
        }

        return $result;
    }

    /**
     * Deletes all file parts in the chunks folder for files uploaded
     * more than chunksExpireIn seconds ago
     */
    protected function cleanupChunks(){
        foreach (scandir($this->chunksFolder) as $item){
            if ($item == "." || $item == "..")
                continue;

            $path = $this->chunksFolder.DIRECTORY_SEPARATOR.$item;

            if (!is_dir($path))
                continue;

            if (time() - filemtime($path) > $this->chunksExpireIn){
                $this->removeDir($path);
            }
        }
    }

    /**
     * Removes a directory and all files contained inside
     * @param string $dir
     */
    protected function removeDir($dir){
        foreach (scandir($dir) as $item){
            if ($item == "." || $item == "..")
                continue;

            if (is_dir($item)){
                $this->removeDir($item);
            } else {
                unlink(join(DIRECTORY_SEPARATOR, array($dir, $item)));
            }

        }
        rmdir($dir);
    }

    /**
     * Converts a given size with units to bytes.
     * @param string $str
     */
    protected function toBytes($str){
        $str = trim($str);
        $last = strtolower($str[strlen($str)-1]);
        $val;
        if(is_numeric($last)) {
            $val = (int) $str;
        } else {
            $val = (int) substr($str, 0, -1);
        }
        switch($last) {
            case 'g': case 'G': $val *= 1024;
            case 'm': case 'M': $val *= 1024;
            case 'k': case 'K': $val *= 1024;
        }
        return $val;
    }

    /**
     * Determines whether a directory can be accessed.
     *
     * is_executable() is not reliable on Windows prior PHP 5.0.0
     *  (http://www.php.net/manual/en/function.is-executable.php)
     * The following tests if the current OS is Windows and if so, merely
     * checks if the folder is writable;
     * otherwise, it checks additionally for executable status (like before).
     *
     * @param string $directory The target directory to test access
     */
    protected function isInaccessible($directory) {
        $isWin = $this->isWindows();

        $folderInaccessible = ($isWin) ? !is_writable($directory) : ( !is_writable($directory) && !is_executable($directory) );

        if( $folderInaccessible ) {
            mkdir($directory, 0755, true);
        }

        $folderInaccessible = ($isWin) ? !is_writable($directory) : ( !is_writable($directory) && !is_executable($directory) );

        return $folderInaccessible;
    }

    /**
     * Determines is the OS is Windows or not
     *
     * @return boolean
     */

    protected function isWindows() {
        $isWin = (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN');
        return $isWin;
    }

}