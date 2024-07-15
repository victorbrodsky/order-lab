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

/**
 * Created by Oleg Ivanov
 * User: oli2002
 * Date: 10/4/13
 * Time: 12:56 PM
 */

namespace App\UserdirectoryBundle\Util;


use App\TranslationalResearchBundle\Entity\AntibodyList;
use App\TranslationalResearchBundle\Entity\CollDivList;
use App\TranslationalResearchBundle\Entity\CollLabList;
use App\TranslationalResearchBundle\Entity\CompCategoryList;
use App\TranslationalResearchBundle\Entity\IrbApprovalTypeList;
use App\TranslationalResearchBundle\Entity\IrbStatusList;
use App\TranslationalResearchBundle\Entity\OtherRequestedServiceList;
use App\TranslationalResearchBundle\Entity\PriceTypeList;
use App\TranslationalResearchBundle\Entity\ProjectTypeList;
use App\TranslationalResearchBundle\Entity\RequesterGroupList;
use App\TranslationalResearchBundle\Entity\SpecialtyList;
use App\TranslationalResearchBundle\Entity\TissueProcessingServiceList;
use App\UserdirectoryBundle\Entity\Document;
use App\UserdirectoryBundle\Entity\InterfaceTransferList;
use App\UserdirectoryBundle\Entity\TransferData;
use App\UserdirectoryBundle\Entity\TransferStatusList;
use App\UserdirectoryBundle\Entity\User;
use App\UserdirectoryBundle\Entity\UserInfo;
use App\UserdirectoryBundle\Form\DataTransformer\GenericTreeTransformer;
use Doctrine\ORM\EntityManagerInterface;
use phpseclib3\Crypt\PublicKeyLoader;
use phpseclib3\Crypt\RSA;
use phpseclib3\Net\SFTP;
use phpseclib3\Net\SSH2;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Bundle\SecurityBundle\Security;

use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;


class InterfaceTransferUtil {

    protected $em;
    protected $container;
    protected $security;
    protected $verifyPeer;

    public function __construct( EntityManagerInterface $em, ContainerInterface $container, Security $security ) {
        $this->em = $em;
        $this->container = $container;
        $this->security = $security;
        $this->verifyPeer = true;
        //$this->verifyPeer = false; //not recommended
    }

    //Require ssh
    //http://pecl.php.net/package/ssh2
    public function testTransferFile( InterfaceTransferList $transfer ) {

        if( !$transfer ) {
            return null;
        }

        //Send file via sftp to server

        $strServer = $transfer->getTransferDestination();  //"159.203.95.150";
        $strServerPort = "22";
        $strServerUsername = $transfer->getSshUsername();
        $strServerPassword = $transfer->getSshPassword();

//        $path = DIRECTORY_SEPARATOR.
//            "src".DIRECTORY_SEPARATOR.
//            "App".DIRECTORY_SEPARATOR.
//            "UserdirectoryBundle".DIRECTORY_SEPARATOR.
//            "Temp".DIRECTORY_SEPARATOR
//        ;

        $dstFile = "dst_file.csv";
        $srcFile = "src_file.csv";

        $dstPath = "/usr/local/bin/order-lab-homepagemanager/orderflex";
        $dstFilePath = $dstPath . $this->getPath("/") . $dstFile;

        //$dstTestFilePath = $dstPath . $this->getPath("/") . "test_file.txt";
        //$dstTestFilePath = $dstPath . "/" . "test_file.txt";

        $projectDir = $this->container->get('kernel')->getProjectDir(); //order-lab\orderflex
        //destination path: src\App\UserdirectoryBundle\Util
        $srcFilePath = $projectDir . $this->getPath("/") . $srcFile;
        //exit('$srcFilePath='.$srcFilePath);
        //exit('$dstFilePath='.$dstFilePath);
        echo "srcFilePath=$srcFilePath <br>";
        echo "dstFilePath=$dstFilePath <br>";
        //echo "dstTestFilePath=$dstTestFilePath <br>";

        $srcFilePath = realpath($srcFilePath);

        if( file_exists($srcFilePath) ) {
            echo "The source file $srcFilePath exists";
        } else {
            echo "The file $srcFilePath does not exist";
            return NULL;
        }

        //connect to server
        $dstConnection = ssh2_connect($strServer, $strServerPort);

        if( ssh2_auth_password($dstConnection, $strServerUsername, $strServerPassword) ){
            //Initialize SFTP subsystem

            echo "Connected to $strServer <br>";
            try {
                $dstSFTP = ssh2_sftp($dstConnection);
                echo "dstSFTP=$dstSFTP <br>";
                //$dstSFTP = intval($dstSFTP);
                //echo "dstSFTP=$dstSFTP <br>";

                //$dstFile = fopen("ssh2.sftp://{$dstSFTP}/".$srcFile, 'w');

                //$dstFile = fopen("ssh2.sftp://" . intval($dstSFTP) . "/" . $srcFile, 'r'); //w or r
                //dump($dstFile);

                $dstFile = fopen("ssh2.sftp://{$dstSFTP}/".$dstFilePath, 'w');

                if ( !$dstFile ) {
                    throw new \Exception('File open failed. file=' . $srcFile);
                }

                //$dstTestFile = fopen("ssh2.sftp://{$dstSFTP}/".$dstTestFilePath, 'r');
                //$contents = stream_get_contents($dstTestFile);
                //dump($contents);

                $srcFile = fopen($srcFilePath, 'r');

                $writtenBytes = stream_copy_to_stream($srcFile, $dstFile);
                echo "writtenBytes=$writtenBytes <br>";
                fclose($dstFile);
                fclose($srcFile);

                //echo "Write <br>";
                //fwrite($dstFile, "Testing");
                //echo "Close <br>";
                //fclose($dstFile);
            } catch ( Exception $e ) {
                throw new \Exception('Error to transfer file=' . $srcFile . '; Error='.$e->getMessage());
            }

        }else{
            echo "Unable to authenticate on server";
        }
    }

    //TODO: create transfer interface page and call this function sendTransfer
    //Get all transfers from TransferData with status 'Ready' and make sftp transfer to the remote server
    public function sendTransfer() {
        //1) get data from TransferData
        $transferDatas = $this->getTransfers('Ready');
        echo "transferDatas=".count($transferDatas)."<br>";

        $resArr = array();
        foreach($transferDatas as $transferData) {
            $singleTransferRes = $this->sendSingleTransfer($transferData);
            $resArr[] = $singleTransferRes;
        }

        $resStr = NULL;
        if( count($resArr) > 0 ) {
            $resStr = "Send transfer completed: " . implode("; ",$resArr);
        } else {
            $resStr = "Send transfer not completed: nothing to transfer.";
        }

        return $resStr;
        //exit('EOF sendTransfer');
    }

    public function sendSingleTransfer( TransferData $transferData ) {

        //$logger = $this->container->get('logger');
        echo "transferStatus=".$transferData->getTransferStatus()."<br>";

        if( $transferData->getTransferStatus() != 'Ready' ) {
            return null;
        }

        $interfaceTransfer = $transferData->getInterfaceTransfer();
        $strServer = $interfaceTransfer->getTransferDestination();

        if( !$interfaceTransfer ) {
            echo "interface transfer is null <br>";
            return null;
        }

        //Get $transferableEntity (i.e. antibody)
        $className = $transferData->getClassName();
        $localId = $transferData->getLocalId();
        if( $className && $localId ) {
            $transferableEntity = $this->em->getRepository($className)->find($localId);
        }
        //$transferableEntity = $this->findExistingTransferableEntity($entityId,$globalId,$className);

        //Create json with antibody data
        $jsonFile = $this->createJsonFile($transferableEntity, $className);

        $instanceId = $transferData->getInstanceId();
        $jsonFile['instanceId'] = $instanceId;

        //dump($jsonFile);
        //exit('111');

        //Step 1: get application path with curl
        $remoteAppPath = $this->getAppPathCurl($interfaceTransfer,$jsonFile);
        //$logger->notice('remoteAppPath='.$remoteAppPath);
        //exit('remoteAppPath='.$remoteAppPath);
        $jsonFile['apppath'] = $remoteAppPath;

        //Step 2: send files with sftp
        //send associated files (i.e. documents)
        $resFiles = $this->sendAssociatedFiles($interfaceTransfer,$jsonFile);

        //add files path to $jsonFile
        $jsonFile['files'] = $resFiles;

        //Step 3: send data with curl
        $res = $this->sendDataCurl($interfaceTransfer,$jsonFile);

        $status = NULL;
        if( $res === true ) {
            //set status to 'Completed'
            $status = $this->em->getRepository(TransferStatusList::class)->findOneByName('Completed');
            $msg = "Entity ".$className." ID ". $localId .", name ".$jsonFile['name']." has been successfully transfered to the remote server ".$strServer;
        } else {
            //Failed
            $status = $this->em->getRepository(TransferStatusList::class)->findOneByName('Failed');
            $msg = "Entity ".$className." ID ". $localId .", name ".$jsonFile['name']." failed to transfer to the remote server ".$strServer;
        }

        if( $status ) {
            $transferData->setTransferStatus($status);
            $this->em->flush(); //testing

            //TODO: Add to EventLog
        }

        return $msg;
    }

    public function sendAssociatedFiles( InterfaceTransferList $transfer, $jsonFile ) {
        //dump($jsonFile);
        //exit('111');

        $strServer = $transfer->getTransferDestination();  //"159.203.95.150";
        $strServerPort = "22";
        $strServerUsername = $transfer->getSshUsername();
        $strServerPassword = $transfer->getSshPassword();

        //connect to server
        $dstConnection = ssh2_connect($strServer, $strServerPort);

        if( ssh2_auth_password($dstConnection, $strServerUsername, $strServerPassword) ){
            //Ok, continue
            echo "Connected to $strServer <br>";
        } else {
            exit("Unable to connect to the remote server");
        }

        $appPath = $jsonFile['apppath'];

        $resArr = array();
        foreach( $jsonFile['documents'] as $document ) {
            $path = $document['path'];
            $label = $document['label'];
            $uniqueId = $jsonFile['id']."-".$document['id']; //$jsonFile['id'] - transferable entity ID
            $sentFile = $this->sendSingleFile($dstConnection,$appPath,$path,$uniqueId);
            if( $sentFile ) {
                $resArr[] = array(
                    'uniqueid' => $uniqueId,
                    'filepath' => $sentFile,
                    'label' => $label,
                    'uniquename' => $document['uniquename'],
                    'originalnameclean' => $document['originalnameclean']
                );
            }
        }

        return $resArr;
        //exit("EOF sendAssociatedFiles");
    }

    //Require ssh
    //http://pecl.php.net/package/ssh2
    public function sendSingleFile( $dstConnection, $appPath, $filePath, $uniqueId ) {

        //$filePath: http://127.0.0.1:8000\Uploaded/directory/documents\65663c5c4180e.jpg
        $dstFile = basename($filePath);
        echo "dstFile=$dstFile <br>";

        //$dstFile = "dst_file.csv";
        //$srcFile = "src_file.csv";

        //TODO: get remote server full url for upload path:
        //Use curl
        //$dstPath = "/usr/local/bin/order-lab-homepagemanager/orderflex";
        $dstFullPath = $appPath . $this->getPath("/") . $uniqueId . "/";
        $dstFilePath = $dstFullPath . $dstFile;

        //$projectDir = $this->container->get('kernel')->getProjectDir(); //order-lab\orderflex
        //destination path: src\App\UserdirectoryBundle\Util
        //$srcFilePath = $projectDir . $this->getPath("/") . $srcFile;
        $srcFilePath = $filePath;
        //exit('$srcFilePath='.$srcFilePath);
        //exit('$dstFilePath='.$dstFilePath);
        echo "srcFilePath=$srcFilePath <br>";
        echo "dstFilePath=$dstFilePath <br>";
        //echo "dstTestFilePath=$dstTestFilePath <br>";

        $srcFilePath = realpath($srcFilePath);

        if( file_exists($srcFilePath) ) {
            echo "The source file $srcFilePath exists <br>";
        } else {
            echo "The file $srcFilePath does not exist <br>";
            return NULL;
        }

        try {
            $dstSFTP = ssh2_sftp($dstConnection);
            echo "dstSFTP=$dstSFTP <br>";
            //$dstSFTP = intval($dstSFTP);
            //echo "dstSFTP=$dstSFTP <br>";

            //$dstFile = fopen("ssh2.sftp://{$dstSFTP}/".$srcFile, 'w');

            //$dstFile = fopen("ssh2.sftp://" . intval($dstSFTP) . "/" . $srcFile, 'r'); //w or r
            //dump($dstFile);

            if (file_exists('ssh2.sftp://' . $dstFullPath)) {
                //OK
            } else {
                //exit('$dstFullPath='.$dstFullPath);
                ssh2_sftp_mkdir($dstSFTP, $dstFullPath, 0755, true);
                ssh2_exec($dstConnection, 'chown apache:apache '.$dstFullPath);
                //exit('$stream='.$stream);
            }

            $dstFile = fopen("ssh2.sftp://{$dstSFTP}/".$dstFilePath, 'w');

            if ( !$dstFile ) {
                throw new \Exception('File open failed. file=' . $srcFilePath);
            }

            //$dstTestFile = fopen("ssh2.sftp://{$dstSFTP}/".$dstTestFilePath, 'r');
            //$contents = stream_get_contents($dstTestFile);
            //dump($contents);

            $srcFile = fopen($srcFilePath, 'r');

            $writtenBytes = stream_copy_to_stream($srcFile, $dstFile);
            echo "writtenBytes=$writtenBytes <br>";
            fclose($dstFile);
            fclose($srcFile);

            //echo "Write <br>";
            //fwrite($dstFile, "Testing");
            //echo "Close <br>";
            //fclose($dstFile);

            return $dstFilePath;

        } catch ( Exception $e ) {
            throw new \Exception('Error to transfer file=' . $srcFile . '; Error='.$e->getMessage());
        }

        return NULL;
        //exit("EOF sendSingleFile");
    }

    //https://stackoverflow.com/questions/28256655/connect-to-sftp-using-php-and-private-key
    //https://phpseclib.com/docs/why
    public function sendSingleFilePhpseclib() {
        $sftp = new SFTP('sftp.server.com');

        $privateKey = RSA::createKey();
        $public = $privateKey->getPublicKey();

        // in case that key has a password
        $privateKey->setPassword('private key password');

        // load the private key
        $privateKey->loadKey(file_get_contents('/path/to/privatekey.pem'));

        // login via sftp
        if (!$sftp->login('username', $privateKey)) {
            throw new Exception('sFTP login failed');
        }

        // now you can list what's in here
        $filesAndFolders = $sftp->nlist();

        // you can change directory
        $sftp->chdir('coolstuffdir');

        // get a file
        $sftp->get('remoteFile', 'localFile');
    }

    public function sendDataCurl( InterfaceTransferList $interfaceTransfer, $jsonFile ) {

        //dump($jsonFile);
        //exit('111');

        //Send data with curl and secret key
        //$secretKey = $interfaceTransfer->getSshPassword(); //use SshPassword for now
        //$secretKey = $_ENV['APP_SECRET']; //get .env parameter
        //Use the same secret for now.
        //It is better to use $interfaceTransfer secretKey (add field) which should be the same as a receiver' app secret key
        $userSecUtil = $this->container->get('user_security_utility');
        $secretKey = $userSecUtil->getSiteSettingParameter('secretKey');

        //Add hash and security key
        $hash = hash('sha512', $secretKey . serialize($jsonFile));
        $jsonFile['hash'] = $hash;

        $data_string = json_encode($jsonFile);
        $strServer = $interfaceTransfer->getTransferDestination();  //"159.203.95.150";
        $url = 'http://'.$strServer.'/directory/transfer-interface/receive-transfer';
        echo "url=$url <br>";
        $ch = curl_init($url);

        if(1) {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
            //curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HEADER, 0);
//            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
//                'Content-Type: application/json',
//                '"Content-Length: ' . strlen($data_string) . '"'
//            ));
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json',
                'Content-Length: ' . strlen($data_string)
            ));
        }

        $result = curl_exec($ch);
        $status = curl_getinfo($ch);
        curl_close($ch);

        //dump($status);
        //dump($result);
        //exit('222');

        if( $result ) {
            $result = json_decode($result, true);
            if( !$result ) {
                return false;
            }
            $checksum = $result['checksum'];
            $valid = $result['valid'];
            $transferResult = $result['transferResult'];

            //dump($result);
            //exit('222');

            if ($checksum === $hash && $valid === true && $transferResult === true) {
                echo "Successefully sent: " . $jsonFile['className'] . ", ID=" . $jsonFile['id'] . " <br>";
                return true;
            }
        }

        //exit('222');
        return false;
    }

    public function getAppPathCurl( InterfaceTransferList $interfaceTransfer, $jsonFile ) {
        $remoteAppPath = NULL;

        //Send data with curl and secret key
        //$secretKey = $interfaceTransfer->getSshPassword(); //use SshPassword for now
        //$secretKey = $_ENV['APP_SECRET']; //get .env parameter
        $userSecUtil = $this->container->get('user_security_utility');
        $secretKey = $userSecUtil->getSiteSettingParameter('secretKey');

        //Add hash and security key
        $hash = hash('sha512', $secretKey . serialize($jsonFile));
        $jsonFile['hash'] = $hash;

        $data_string = json_encode($jsonFile);
        $strServer = $interfaceTransfer->getTransferDestination();  //"159.203.95.150";
        $url = 'http://'.$strServer.'/directory/transfer-interface/get-app-path';
        echo "url=$url <br>";
        $ch = curl_init($url);

        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Content-Length: ' . strlen($data_string)
        ));

        $result = curl_exec($ch);
        //$status = curl_getinfo($ch);
        curl_close($ch);

        //dump($result);
        //exit('111');

        if( $result ) {
            $result = json_decode($result, true);
            if( !$result ) {
                return NULL;
            }
            $checksum = $result['checksum'];
            $valid = $result['valid'];
            $transferResult = $result['transferResult'];
            $apppath = $result['apppath'];

            //dump($result);
            //exit('222');

            if ($checksum === $hash && $valid === true && $transferResult === true) {
                echo "Successefully sent: " . $jsonFile['className'] . ", ID=" . $jsonFile['id'] . " <br>";
                return $apppath;
            }
        }

        return NULL;
    }

    public function createJsonFile( $transferableEntity, $className ) {
        $jsonFile = null;
        //echo "createJsonFile: className=$className <br>";

        //Case: AntibodyList
        if( str_contains($className, 'TranslationalResearchBundle') && str_contains($className, 'AntibodyList') ) {
            //make json from TranslationalResearchBundle AntibodyList entity
            $jsonFile = $transferableEntity->toJson();
            $jsonFile['className'] = $className;
        }

        //Case: some other transferable object

        return $jsonFile;
    }



    public function getTransfers( $statusStr, $paginator=false, $request=null ) {
        $repository = $this->em->getRepository(TransferData::class);
        $dql =  $repository->createQueryBuilder("transfer");
        $dql->select('transfer');

        $dql->leftJoin('transfer.transferStatus','transferStatus');

        $dql->where('LOWER(transferStatus.name) = LOWER(:transferStatus)');

        $query = $dql->getQuery();
        $query->setParameters(
            array(
                'transferStatus' => $statusStr
            )
        );

        if( $paginator === true && $request ) {
            $limit = 20;
            //$query = $dql->getQuery();
            $paginator  = $this->container->get('knp_paginator');
            $transferDatas = $paginator->paginate(
                $query,
                $request->query->get('page', 1), /*page number*/
                $limit,     /*limit per page*/
                array(
                    'defaultSortFieldName' => 'transfer.updatedate',
                    'defaultSortDirection' => 'DESC',
                    'wrap-queries'=>true
                )
            );
        } else {
            $transferDatas = $query->getResult();
        }

        return $transferDatas;
    }

    public function getPath( $separator=DIRECTORY_SEPARATOR  ) {
        ///var/temp/
        $path = $separator.
            //"src".$separator.
            //"App".$separator.
            //"UserdirectoryBundle".$separator.
            "var".$separator.
            "temp".$separator
        ;
        return $path;
    }

    //find if TransferData has this antibody with status 'Ready' or 'ready'
    public function findTransferData_ORIG( $entity, $statusStr ) {
        $mapper = $this->classListMapper($entity);
        $className = $mapper['className'];

        $repository = $this->em->getRepository(TransferData::class);
        $dql =  $repository->createQueryBuilder("transfer");
        $dql->select('transfer');

        $dql->leftJoin('transfer.transferStatus','transferStatus');

        $dql->where('transfer.entityId = :entityId AND transfer.className = :className');
        $dql->andWhere('LOWER(transferStatus.name) = LOWER(:transferStatus)');

        $query = $dql->getQuery();

        $query->setParameters(
            array(
                'entityId' => $entity->getId(),
                'className' => $className,
                'transferStatus' => $statusStr
            )
        );

        $transfers = $query->getResult();

        //Get single transfer data
        $transfer = NULL;
        if (count($transfers) > 0) {
            //Can we have the same multiple transfers?
            $transfer = $transfers[0];
        }
        if (count($transfers) == 1) {
            $transfer = $transfers[0];
        }

        return $transfer;
    }

    //Used by DoctrineListener->setTrabsferable() find or create if TransferData has this antibody, project ...
    public function findCreateTransferData( $entity ) {

        $logger = $this->container->get('logger');
        $transferData = $this->findTransferData($entity);

        if( $transferData ) {
            //set status to 'Ready'
            $statusReady = $this->em->getRepository(TransferStatusList::class)->findOneByName('Ready');
            $transferData->setTransferStatus($statusReady);
            $logger->notice('findCreateTransferData: found existing TransferData ID='.
                $transferData->getId().", className=".$transferData->getClassName().
                ", localId=".$transferData->getLocalId()
            );
        } else {
            //Create TransferData
            $transferData = $this->createTransferData($entity,$status='Ready');
            $logger->notice('findCreateTransferData: created new TransferData ID='.
                $transferData->getId().", className=".$transferData->getClassName().
                ", localId=".$transferData->getLocalId()
            );
        }

        return $transferData;
    }

    public function findTransferData( $entity ) {
        $mapper = $this->classListMapper($entity);
        $className = $mapper['className'];
        return $this->findTransferDataByLocalId($entity->getId(),$className);
    }

    public function findTransferDataByLocalId( $localId, $className ) {
        $repository = $this->em->getRepository(TransferData::class);
        $dql =  $repository->createQueryBuilder("transfer");
        $dql->select('transfer');

        //$dql->leftJoin('transfer.transferStatus','transferStatus');

        $dql->where('transfer.localId = :localId AND transfer.className = :className');

        $query = $dql->getQuery();

        $query->setParameters(
            array(
                'localId' => $localId,
                'className' => $className,
            )
        );

        $transfers = $query->getResult();

        //Get single transfer data
        $transfer = NULL;
        if (count($transfers) > 0) {
            //Can we have the same multiple transfers?
            $transfer = $transfers[0];
        }
        if (count($transfers) == 1) {
            $transfer = $transfers[0];
        }

        return $transfer;
    }

    public function findTransferDataByGlobalId( $globalId, $className ) {
        $repository = $this->em->getRepository(TransferData::class);
        $dql =  $repository->createQueryBuilder("transfer");
        $dql->select('transfer');

        //$dql->leftJoin('transfer.transferStatus','transferStatus');

        $dql->where('transfer.globalId = :globalId AND transfer.className = :className');

        $query = $dql->getQuery();

        //$userSecUtil = $this->container->get('user_security_utility');
        //$instanceId = $uploadPath = $userSecUtil->getSiteSettingParameter('instanceId');

        $query->setParameters(
            array(
                'globalId' => $globalId,
                'className' => $className,
            )
        );

        $transfers = $query->getResult();

        //Get single transfer data
        $transfer = NULL;
        if (count($transfers) > 0) {
            //Can we have the same multiple transfers?
            $transfer = $transfers[0];
        }
        if (count($transfers) == 1) {
            $transfer = $transfers[0];
        }

        return $transfer;
    }

    public function findAllTransferDataByClassname( $className, $statusStr ) {
        $repository = $this->em->getRepository(TransferData::class);
        $dql =  $repository->createQueryBuilder("transfer");
        $dql->select('transfer');

        $dql->leftJoin('transfer.transferStatus','transferStatus');

        $dql->where('transfer.className = :className');
        $dql->andWhere('LOWER(transferStatus.name) = LOWER(:transferStatus)');

        $query = $dql->getQuery();

        $query->setParameters(
            array(
                'className' => $className,
                'transferStatus' => $statusStr
            )
        );

        $transfers = $query->getResult();

        return $transfers;
    }

    public function findTransferDataByObjectAndLocalId( $localId, $className ) {
        $repository = $this->em->getRepository(TransferData::class);
        $dql =  $repository->createQueryBuilder("transfer");
        $dql->select('transfer');

        $dql->leftJoin('transfer.transferStatus','transferStatus');

        $dql->where('transfer.localId = :localId AND transfer.className = :className');

        $query = $dql->getQuery();

        $query->setParameters(
            array(
                'localId' => $localId,
                'className' => $className,
            )
        );

        $transfers = $query->getResult();

        //Get single transfer data
        $transfer = NULL;
        if (count($transfers) > 0) {
            //Can we have the same multiple transfers?
            $transfer = $transfers[0];
        }
        if (count($transfers) == 1) {
            $transfer = $transfers[0];
        }

        return $transfer;
    }


    public function classListMapper( $entity ) {

        $className = get_class($entity);
        $entityName = $this->getEntityName($className);

        $res = array();
        $res['className'] = $className; //'App\UserdirectoryBundle\Entity\InterfaceTransferList'
        $res['entityName'] = $entityName; //'InterfaceTransferList'

        return $res;
    }

    //https://www.php.net/manual/en/function.get-class.php
    function getEntityName($classname)
    {
        if ($pos = strrpos($classname, '\\')) return substr($classname, $pos + 1);
        return $pos;
    }

    public function createTransferData( $entity, $status='Ready' ) {
        //$userSecUtil = $this->container->get('user_security_utility');
        $user = $this->security->getUser();

        $transfer = new TransferData($user);

        $status = $this->em->getRepository(TransferStatusList::class)->findOneByName($status);
        if( $status ) {
            $transfer->setTransferStatus($status);
        }

        $mapper = $this->classListMapper($entity);
        $className = $mapper['className'];
        $entityName = $mapper['entityName'];

        $transfer->setClassName($className);

        $transfer->setLocalId($entity->getId());

        //NOT USED $globalId, $instanceId
//        $instanceId = $uploadPath = $userSecUtil->getSiteSettingParameter('instanceId');
//        if( !$instanceId ) {
//            $instanceId = 'NA';
//        }
//        $transfer->setInstanceId($instanceId); //Server ID
//        $globalId = $transfer->createGlobalId(); //$globalId = $localId.'@'.$instanceId
//        $transfer->setGlobalId($globalId);

        //echo "entityName=$entityName <br>";
        $interfaceTransfer = $this->em->getRepository(InterfaceTransferList::class)->findOneByName($entityName);
        if( $interfaceTransfer ) {
            //echo "added interfaceTransfer with ID=".$interfaceTransfer->getId()."<br>";
            $transfer->setInterfaceTransfer($interfaceTransfer);
        }

        $this->em->persist($transfer);
        //$this->em->flush();

        //$transfers = $this->em->getRepository(TransferData::class)->findAll();
        //echo "transfer=".count($transfers)."<br>";
        //exit("transfer=".$transfer->getId());

        return $transfer;
    }

    //TODO: get InterfaceTransferList by full classname
    public function getInterfaceTransferByName( $name ) {
        $interfaceTransfer = $this->em->getRepository(InterfaceTransferList::class)->findOneByName($name);
        return $interfaceTransfer;
    }

    public function getInterfaceTransferByEntity( $entity ) {
        $mapper = $this->classListMapper($entity);
        $entityName = $mapper['entityName'];
        $interfaceTransfer = $this->getInterfaceTransferByName($entityName);
        return $interfaceTransfer;
    }

    //Add to InterfaceTransfer the way to distinguish if it should be added to the TransferData:
    // AntibodyList -> server should be master (master to slave)
    // Project -> server should be slave (slave to master)
    //TODO: add to InterfaceTransfer sync direction: local to remote, remote to local, both
    public function isMasterTransferServer( $entity ) {
//        $mapper = $this->classListMapper($entity);
//        //$className = $mapper['className'];
//        $entityName = $mapper['entityName'];
//        $interfaceTransfer = $this->getInterfaceTransferByName($entityName);
        $interfaceTransfer = $this->getInterfaceTransferByEntity($entity);
        if( $interfaceTransfer ) {
            if( $interfaceTransfer->getTransferDestination() ) {
                return true;
            }
        }
        
        return false;
    }

    public function receiveTransfer( $receiveData ) {
        $logger = $this->container->get('logger');

        $className = $receiveData['className'];
        //$entityName = $this->getEntityName($className);
        $transferableEntity = NULL;

        //Case: AntibodyList
        if( str_contains($className, 'TranslationalResearchBundle') && str_contains($className, 'AntibodyList') ) {

            $transferableEntity = $this->receiveAntibody($receiveData);

//            $logger->notice('AntibodyList: className='.$className);
//            $entityId = $receiveData['id'];
//
//            if( $className && $entityId ) {
//                $logger->notice('AntibodyList: entityId='.$entityId);
//                //find unique existing antibody by name and description and comment
//                //$transferableEntity = $this->em->getRepository($className)->findOneByName($name);
//
//                //$sourceId = $receiveData['sourceId'];
//                $globalId = $receiveData['globalId'];
//                //$name = $receiveData['name'];
//                //$description = $receiveData['description'];
//                //$comment = $receiveData['comment'];
//
////                $matchingArr = array(
////                    //'sourceId' => $sourceId
////                    'instanceId' => $instanceId
////                    //'name' => $name,
////                    //'description' => $description,
////                    //'comment' => $comment
////                );
////                $transferableEntity = $this->findExistingTransferableEntity($className,$matchingArr);
//
//                $transferableEntity = $this->findExistingTransferableEntity($entityId,$globalId,$className);
//
//                if( $transferableEntity ) {
//                    $logger->notice('receiveTransfer: found transferableEntity ID='.$transferableEntity->getId());
//                    $update = $transferableEntity->updateByJson($receiveData, $this->em, $className);
//                    if( $update ) {
//
//                        $updated = false;
//                        if( method_exists($transferableEntity, 'setOpenToPublic') ) {
//                            $transferableEntity->setOpenToPublic(true);
//                            $updated = true;
//                        }
//                        if( method_exists($transferableEntity, 'setType') ) {
//                            $transferableEntity->setType('user-added');
//                            $updated = true;
//                        }
//
//                        if( $updated ) {
//                            $this->em->flush();
//                        }
//                    }
//                } else {
//                    if(1) {
//                        //create new entity
//                        $logger->notice('receiveTransfer: create new AntibodyList, Project ...');
//                        $transferableEntity = new $className();
//                        $update = $transferableEntity->updateByJson($receiveData, $this->em, $className);
//                        if ($update) {
//                            $transferableEntity->setOpenToPublic(true);
//                            //$transferableEntity->setSourceId($sourceId);
//                            $transferableEntity->setType('user-added');
//                            $this->em->persist($transferableEntity);
//                            $this->em->flush();
//                            $logger->notice('receiveTransfer: after creation new AntibodyList flush: id=' . $transferableEntity->getId());
//                        }
//                    }
//                }
//
//                //Attach documents
//                //1) remove all existing documents and attach new
//                //$transferableEntity->clearDocuments();
//                //Remove entity document by ID and delete file
//                //$transferableEntity->clearImageData();
//                //$projectRoot = $this->container->get('kernel')->getProjectDir(); //\order-lab\orderflex
//                foreach($transferableEntity->getDocuments() as $document) {
//                    //remove file
//                    //$file = $projectRoot."/"."public"."/".$document->getUploadDirectory();
//                    $file = $document->getFullServerPath();
//                    unlink($file);
//                    $this->em->remove($document);
//                }
//                foreach($transferableEntity->getVisualInfos() as $visualInfo) {
//                    foreach( $visualInfo->getDocuments() as $visualInfoDocument ) {
//                        $file = $visualInfoDocument->getFullServerPath();
//                        unlink($file);
//                        $this->em->remove($visualInfoDocument);
//                    }
//                    $this->em->remove($visualInfo);
//                }
//
//                $documentDatas = $receiveData['files'];
//                foreach($documentDatas as $documentArr) {
////                    'uniqueid' => $uniqueId,
////                    'filepath' => $sentFile,
////                    'label' => $label
//                    $document = $this->receiveAssociatedDocument($documentArr,$className);
//                    $logger->notice('receiveTransfer: document id='.$document->getId());
//                    $transferableEntity->addDocument($document);
//                    $this->em->flush();
//                    $logger->notice('receiveTransfer: after flush: document id='.$document->getId());
//                }
//
//                //TODO: check $transferableEntity how many documents
//                foreach( $transferableEntity->getImageData() as $image) {
//                    $logger->notice('receiveTransfer: image id='.$image['id']."; path=".$image['path']."; url=".$image['url']);
//                }
//
//            }
        }

        //Case: Project
        if( str_contains($className, 'TranslationalResearchBundle') && str_contains($className, 'Project') ) {
            //$transferableEntity = $this->receiveProject($receiveData);
        }

        //$transferData = $interfaceTransferUtil->find CreateTransferData($entity);

        return $transferableEntity;
    }

    public function receiveAntibody($receiveData) {
        $logger = $this->container->get('logger');
        $className = $receiveData['className'];
        $logger->notice('AntibodyList: className='.$className);
        $entityId = $receiveData['id'];
        $transferableEntity = NULL;

        if( $className && $entityId ) {
            $logger->notice('AntibodyList: entityId='.$entityId);
            //find unique existing antibody by name and description and comment
            //$transferableEntity = $this->em->getRepository($className)->findOneByName($name);

            //$sourceId = $receiveData['sourceId'];
            $globalId = $receiveData['globalId'];
            //$name = $receiveData['name'];
            //$description = $receiveData['description'];
            //$comment = $receiveData['comment'];

//                $matchingArr = array(
//                    //'sourceId' => $sourceId
//                    'instanceId' => $instanceId
//                    //'name' => $name,
//                    //'description' => $description,
//                    //'comment' => $comment
//                );
//                $transferableEntity = $this->findExistingTransferableEntity($className,$matchingArr);

            $transferableEntity = $this->findExistingTransferableEntity($entityId,$globalId,$className);

            if( $transferableEntity ) {
                $logger->notice('receiveTransfer: found transferableEntity ID='.$transferableEntity->getId());
                $update = $transferableEntity->updateByJson($receiveData, $this->em, $className);
                if( $update ) {
                    $transferableEntity->setOpenToPublic(true);
                    $transferableEntity->setType('user-added');
                    $this->em->flush();
                }
            } else {
                if(1) {
                    //create new entity
                    $logger->notice('receiveTransfer: create new AntibodyList');
                    $transferableEntity = new $className();
                    $update = $transferableEntity->updateByJson($receiveData, $this->em, $className);
                    if ($update) {
                        $transferableEntity->setOpenToPublic(true);
                        //$transferableEntity->setSourceId($sourceId);
                        $transferableEntity->setType('user-added');
                        $this->em->persist($transferableEntity);
                        $this->em->flush();
                        $logger->notice('receiveTransfer: after creation new AntibodyList flush: id=' . $transferableEntity->getId());
                    }
                }
            }

            //Attach documents
            //1) remove all existing documents and attach new
            //$transferableEntity->clearDocuments();
            //Remove entity document by ID and delete file
            //$transferableEntity->clearImageData();
            //$projectRoot = $this->container->get('kernel')->getProjectDir(); //\order-lab\orderflex
            foreach($transferableEntity->getDocuments() as $document) {
                //remove file
                //$file = $projectRoot."/"."public"."/".$document->getUploadDirectory();
                $file = $document->getFullServerPath();
                unlink($file);
                $this->em->remove($document);
            }
            foreach($transferableEntity->getVisualInfos() as $visualInfo) {
                foreach( $visualInfo->getDocuments() as $visualInfoDocument ) {
                    $file = $visualInfoDocument->getFullServerPath();
                    unlink($file);
                    $this->em->remove($visualInfoDocument);
                }
                $this->em->remove($visualInfo);
            }

            $documentDatas = $receiveData['files'];
            foreach($documentDatas as $documentArr) {
//                    'uniqueid' => $uniqueId,
//                    'filepath' => $sentFile,
//                    'label' => $label
                $document = $this->receiveAssociatedDocument($documentArr,$className);
                $logger->notice('receiveTransfer: document id='.$document->getId());
                $transferableEntity->addDocument($document);
                $this->em->flush();
                $logger->notice('receiveTransfer: after flush: document id='.$document->getId());
            }

            //TODO: check $transferableEntity how many documents
            //foreach( $transferableEntity->getImageData() as $image) {
            //    $logger->notice('receiveTransfer: image id='.$image['id']."; path=".$image['path']."; url=".$image['url']);
            //}

        }

        return $transferableEntity;
    }

    public function receiveProject($receiveData) {
        $logger = $this->container->get('logger');
        $className = $receiveData['className'];
        $logger->notice('Project: className='.$className);
        $entityId = $receiveData['id'];

        if( $className && $entityId ) {
            $logger->notice('Project: entityId='.$entityId);
            $globalId = $receiveData['globalId'];
            $transferableEntity = $this->findExistingTransferableEntity($entityId,$globalId,$className);

            if( $transferableEntity ) {
                $logger->notice('receiveTransfer: found transferableEntity ID='.$transferableEntity->getId());
                $update = $transferableEntity->updateByJson($receiveData, $this->em, $className);
                if( $update ) {
                    $this->em->flush();
                }
            } else {
                if(1) {
                    //create new entity
                    $logger->notice('receiveTransfer: create new Project');
                    $transferableEntity = new $className();
                    $update = $transferableEntity->updateByJson($receiveData, $this->em, $className);
                    if ($update) {
                        $this->em->persist($transferableEntity);
                        $this->em->flush();
                        $logger->notice('receiveTransfer: after creation new AntibodyList flush: id=' . $transferableEntity->getId());
                    }
                }
            }

            //Attach documents
            //1) remove all existing documents and attach new
            //$transferableEntity->clearDocuments();
            //Remove entity document by ID and delete file
            //$transferableEntity->clearImageData();
            //$projectRoot = $this->container->get('kernel')->getProjectDir(); //\order-lab\orderflex
            foreach($transferableEntity->getDocuments() as $document) {
                //remove file
                //$file = $projectRoot."/"."public"."/".$document->getUploadDirectory();
                $file = $document->getFullServerPath();
                unlink($file);
                $this->em->remove($document);
            }
            foreach($transferableEntity->getVisualInfos() as $visualInfo) {
                foreach( $visualInfo->getDocuments() as $visualInfoDocument ) {
                    $file = $visualInfoDocument->getFullServerPath();
                    unlink($file);
                    $this->em->remove($visualInfoDocument);
                }
                $this->em->remove($visualInfo);
            }

            $documentDatas = $receiveData['files'];
            foreach($documentDatas as $documentArr) {
//                    'uniqueid' => $uniqueId,
//                    'filepath' => $sentFile,
//                    'label' => $label
                $document = $this->receiveAssociatedDocument($documentArr,$className);
                $logger->notice('receiveTransfer: document id='.$document->getId());
                $transferableEntity->addDocument($document);
                $this->em->flush();
                $logger->notice('receiveTransfer: after flush: document id='.$document->getId());
            }

            //TODO: check $transferableEntity how many documents
            foreach( $transferableEntity->getImageData() as $image) {
                $logger->notice('receiveTransfer: image id='.$image['id']."; path=".$image['path']."; url=".$image['url']);
            }

        }
    }

    public function receiveAssociatedDocument( $documentArr, $className ) {
        $userSecUtil = $this->container->get('user_security_utility');
        $logger = $this->container->get('logger');

        $uniqueId = $documentArr['uniqueid'];
        $filepath = $documentArr['filepath'];
        //$label = $documentArr['label'];
        $uniquename = $documentArr['uniquename'];
        $originalnameclean = $documentArr['originalnameclean'];

        $author = null;
        $logger->notice("receive AssociatedDocument: uniqueId=$uniqueId, filepath=$filepath");

        $filesize = null;
        $filepath = realpath($filepath);

        //move file from $this->getPath("/") to $uploadPath
        $uploadPath = NULL;
        if( str_contains($className, 'TranslationalResearchBundle') && str_contains($className, 'AntibodyList') ) {
            //move to Uploaded\transres\documents
            $uploadPath = $userSecUtil->getSiteSettingParameter('transresuploadpath');
        }
        if( !$uploadPath ) {
            $uploadPath = "TransferableUploads";
            $logger->warning('Upload path is not defined. Use default "'.$uploadPath.'" folder.');
        }

        $projectRoot = $this->container->get('kernel')->getProjectDir(); //C:\Users\ch3\Documents\MyDocs\WCMC\ORDER\order-lab\orderflex
        $uploadDir = 'Uploaded'.'/'.$uploadPath;
        $uploadPath = $projectRoot.'/public/'.$uploadDir;

        //create upload folder if does not exist
        if( !file_exists($uploadPath) ) {
            mkdir($uploadPath, 0755, true);
        }

        if( $filepath && file_exists($filepath) ) {
            //Move to Upload folder
            $logger->notice("receive AssociatedDocument: move from=$filepath, to=".$uploadPath."/".$uniquename);
            rename($filepath, $uploadPath."/".$uniquename); //testing
            //Delete temp folder 4-25234
            //Delete '4-25234' in /usr/local/bin/order-lab-homepagemanager/orderflex/var/temp/4-25234/
            $tempFilepath = dirname($filepath);
            $logger->notice("AssociatedDocument: tempFilepath=$tempFilepath");
            //$this->deleteDir( $tempFilepath );
            //rmdir($tempFilepath); //testing

            $filepath = $uploadPath."/".$uniquename;
        }

        //$logger->notice("AssociatedDocument: after realpath filepath=$filepath");
        if( $filepath ) {
            if( file_exists($filepath) ) {
                $logger->notice("receive AssociatedDocument: filepath exists: uniqueId=$uniqueId, filepath=$filepath");
                //$filesize = $filepath->getFileSize();
                //if (!$filesize) {
                    $filesize = filesize($filepath);
                //}
            } else {
                $logger->notice("receive AssociatedDocument: filepath does not exist=$filepath");
            }
        }
        $logger->notice("receive AssociatedDocument: uniqueId=$uniqueId, filepath=$filepath, filesize=$filesize");

        //TODO: create document and VisualInfo according to the $documentArr
        $object = new Document($author);
        $object->setUniquename($uniquename);
        $object->setCleanOriginalname($originalnameclean);
        //$object->setTitle($uniqueTitle);
        $object->setUniqueid($uniqueId);
        $object->setSize($filesize);

//        $documentType = 'Antibody Image'
//        $transformer = new GenericTreeTransformer($this->em, $author, "DocumentTypeList", "UserdirectoryBundle");
//        $documentType = trim((string)$documentType);
//        $documentTypeObject = $transformer->reverseTransform($documentType);
//        if( $documentTypeObject ) {
//            $object->setType($documentTypeObject);
//        }

        //$uploadPath should be Uploaded/transres/documents
        $object->setUploadDirectory($uploadDir);

        $this->em->persist($object);

        return $object;
    }

//    function deleteDir(string $dirPath): void {
//        if (! is_dir($dirPath)) {
//            throw new InvalidArgumentException("$dirPath must be a directory");
//        }
//        //array_map('unlink', glob("$dirPath/*.*"));
//        rmdir($dirPath);
//    }
//
//    function deleteDir_2(string $dirPath): void {
//        if (! is_dir($dirPath)) {
//            throw new InvalidArgumentException("$dirPath must be a directory");
//        }
//        if (substr($dirPath, strlen($dirPath) - 1, 1) != '/') {
//            $dirPath .= '/';
//        }
//        $files = glob($dirPath . '*', GLOB_MARK);
//        foreach ($files as $file) {
//            if (is_dir($file)) {
//                deleteDir($file);
//            } else {
//                unlink($file);
//            }
//        }
//        rmdir($dirPath);
//    }

    public function findExistingTransferableEntity( $entityId, $globalId, $className ) {
        //$logger = $this->container->get('logger');

        //1) get transferData by $globalId, $className
        $transferData = $this->findTransferDataByGlobalId($globalId,$className);

        if( !$transferData ) {
            return NULL;
        }

        $localId = $transferData->getLocalId();

        $transferableEntity = $this->em->getRepository($className)->find($localId);

        return $transferableEntity;
    }
    public function findExistingTransferableEntity_ORIG( $className, $matchingArr ) {
        $logger = $this->container->get('logger');

        $repository = $this->em->getRepository($className);
        $dql =  $repository->createQueryBuilder("entity");
        $dql->select('entity');

        $parameters = array();

        foreach($matchingArr as $key=>$value) {
            $dql->andWhere('entity.'.$key.' = :entity'.$key);
            $parameters['entity'.$key] = $value;
            $logger->notice('findExistingTransferableEntity: entity'.$key.'=>'.$value);
        }

        $query = $dql->getQuery();

        if( count($parameters) > 0 ) {
            $query->setParameters($parameters);
        }

        $entities = $query->getResult();

        if( count($entities) === 1 ) {
            return $entities[0];
        }

        if( count($entities) > 0 ) {
            return $entities[0];
        }

        return null;
    }




    //TODO: create transfer interface page and call this function getTransfer
    //Run on internal (master)
    //Send request to the external asking to send back all new/updated projects
    //Handle the response from the slave server (external) and add/update the project on the master server (internal)
    public function getSlaveToMasterTransfer() {
        //$testing = true;
        $testing = false;

        $userUtil = $this->container->get('user_utility');
        $session = $userUtil->getSession();

        $userSecUtil = $this->container->get('user_security_utility');
        $instanceId = $uploadPath = $userSecUtil->getSiteSettingParameter('instanceId');
        if( !$instanceId ) {
            $instanceId = 'NA';
        }

        //1) send CURL request to slave to transfer data and receive projects as $transferDatas
        $transferDatas = $this->getSlaveToMasterTransferCurl('App\TranslationalResearchBundle\Entity\Project');

        if( !$transferDatas ) {
            //$resStr = "Get transfer not completed: response is null";
            $resStr = "Nothing to transfer";
            return $resStr;
        }

        //Array of new/updated projects
        $jsonRes = $transferDatas['transferResult'];
        //dump($jsonRes);
        //exit('getSlaveToMasterTransfer: jsonRes');

        // $loader is any of the valid loaders explained later in this article
//        $classMetadataFactory = new ClassMetadataFactory($loader);
//        $normalizer = new ObjectNormalizer($classMetadataFactory);
//        $serializer = new Serializer([$normalizer]);

        $confirmationResponse = array();

        $encoders = [new XmlEncoder(), new JsonEncoder()];
        $normalizers = [new ObjectNormalizer()];
        $serializer = new Serializer($normalizers, $encoders);

        $resArr = array();
        foreach($jsonRes as $jsonObject) {

            //dump($jsonObject);
            //exit('getSlaveToMasterTransfer: $jsonObject');

            //echo "transferData=".$jsonObject."<br>";
            $title = $jsonObject['title'];
            //echo "title=$title <br>";

            //IDs on external server
            $localId = $jsonObject['id'];
            $oid = $jsonObject['oid'];
            $sourceId = $jsonObject['sourceId'];
            $globalId = $jsonObject['globalId'];
            $className = $jsonObject['className'];
            $transferableEntity = NULL;
            $resStr = "";

            //Find if exists by $globalId
            //$transferData = $this->find CreateTransferData($transferableEntity);
//            $transferData = $this->findTransferDataByGlobalId($globalId,$className);
//            echo "transferData=".$transferData."<br>";
//
//            if( $transferData ) {
//                $localId = $transferData->getLocalId();
//                $transferableEntity = $this->em->getRepository($className)->find($localId);
//                if( !$transferableEntity ) {
//                    $transferableEntity = $this->em->getRepository($className)->find($globalId);
//                }
//                echo "transferData exists: transferableEntity=".$transferableEntity->getId()."<br>";
//                //AbstractNormalizer::OBJECT_TO_POPULATE => $person
//                $resStr = "Update existing Project with ID ".$transferableEntity->getId().", title " . $transferableEntity->getTitle();
//            }

            //globalId is set if the object has been transferred previously. In this case the object will be updated
            if( $globalId ) {
                $transferableEntity = $this->em->getRepository($className)->findOneByGlobalId($globalId);
            }

            //Additional check if Project already exists by the source ID
            //if( !$transferableEntity ) {
                //$transferableEntity = $this->em->getRepository($className)->findOneBySourceId($sourceId);
            //}

//            if( !$transferableEntity ) {
//                $transferableEntity = $this->em->getRepository($className)->findOneByOid($oid);
//                $resStr = "Update existing Project found by OID $oid, title " . $title;
//            }
            //echo "Final transferableEntity=".$transferableEntity."<br>";
            //echo "Final resStr=$resStr <br>";

            $new = true;
            $actionStr = 'Create new Project';

            //TODO: fix update Project, now only create New Project
            //An exception occurred while executing a query: SQLSTATE[23505]:
            // Unique violation: 7 ERROR: duplicate key value violates
            // unique constraint "pk__transres__3213e83f716f45b5"
            //DETAIL: Key (id)=(1) already exists.
            if( $transferableEntity ) {
                $new = false;
                $actionStr = 'Update Project';
                //$resStr = "Get transfer not completed: Project ".$transferableEntity->getOid()." already exists.";
                //$resArr[] = $resStr;
                //continue;
            }

            //dump($jsonObject);
            //exit('deserialize');
            $transferableEntity = $this->deserializeObject($jsonObject,$className,$serializer,$transferableEntity);
            if( $transferableEntity ) {
                //echo "PrePersist: transferableEntity ID=".$transferableEntity->getId().", title=".$transferableEntity->getTitle()."<br>";

                //dump($transferableEntity);
                //exit('123');

                //TODO: error: spl_object_id(): Argument #1 ($object) must be of type object, array given
                if( $new ) {
                    $this->em->persist($transferableEntity);
                }

                if ($testing === false) {
                    //set export ID or use TransferData to get it
                    //$transferableEntity->setExportId($localId);
                    //$description = $transferableEntity->getDescription();
                    //$transferableEntity->getDescription($description . "\n "." Transfered with Gloabl ID=".$globalId);

                    $this->em->flush(); //disable for testing

                    //post create/update
                    $postUpdate = false;
                    if( !$transferableEntity->getGlobalId() ) {
                        //Global ID is the same on all servers
                        $globalId = $transferableEntity->getId() . '@' . $instanceId;
                        $transferableEntity->setGlobalId($globalId);
                        $postUpdate = true;
                    }
                    if( !$transferableEntity->getSourceId() ) {
                        $transferableEntity->setSourceId($sourceId);
                        $postUpdate = true;
                    }
                    if( !$transferableEntity->getOid() || ($transferableEntity->getOid() != $transferableEntity->createAndGetOid() ) ) {
                        $transferableEntity->generateOid();
                        $postUpdate = true;
                    }

                    if( $postUpdate ) {
                        $this->em->flush(); //disable for testing
                    }

                    $confirmationResponse[] = array(
                        'instanceId' => $instanceId,
                        'className' => $className,
                        'localId' => $localId,      //id on the external (slave)
                        'sourceId' => $sourceId,    //source id on the external (slave)
                        'oid' => $oid,              //oid on the internal (master)
                        'globalId' => $globalId     //global id on the internal (master) for newly created, or updated objects
                    );

                    $resStr = $actionStr." "
                        . "ID=". $transferableEntity->getId()
                        . "; sourceId=" . $transferableEntity->getSourceId() //WCMINT - product server, WCMTEST - test server
                        . "; oid=" . $transferableEntity->getOid()
                        . "; globalId=" . $transferableEntity->getGlobalId()
                        . ", title " . $transferableEntity->getTitle();

                    //Event Log
                    $eventType = "Project Transferred";
                    //$userSecUtil->createUserEditEvent($this->container->getParameter('employees.sitename'), $resStr, null, null, null, $eventType);
                    $userSecUtil->createUserEditEvent(
                        $this->container->getParameter('translationalresearch.sitename'),
                        $resStr,
                        null,
                        $transferableEntity,
                        null,
                        $eventType
                    );

                    //Send Notification emails for projects involving Computational Pathology or a request for a bioinformatician
                    if( $transferableEntity->sendComputationalEmail() ) {
                        $transresUtil = $this->container->get('transres_util');
                        $compEmailRes = $transresUtil->sendComputationalEmail($transferableEntity);
                        $session->getFlashBag()->add(
                            'notice',
                            "Notification emails for projects involving Computational Pathology".
                            " or a request for a bioinformatician".
                            " have been sent: " . $compEmailRes
                        );
                    }
                }
            }

            $resArr[] = $resStr;
            //exit('EOF getSlaveToMasterTransfer: ' . $resStr);

        } //foreach $jsonObject

        //Both internal and external servers would have a “Global ID” of “101@WCMINT”,
        //and the “Source ID” on the internal will be “3@WCMEXT”.
        //send Global IDs (“Global ID” of “101@WCMINT”) to slave as confirmation
        $this->sendConfirmationToSourceServer($confirmationResponse); //$transferableEntity,$jsonObject['localId']);

        $resStr = NULL;
        if( count($resArr) > 0 ) {
            $resStr = "Get transfer completed: " . implode("; ",$resArr);
        } else {
            $resStr = "Get transfer not completed: nothing to transfer.";
        }

        if( $testing ) {
            exit('EOF getSlaveToMasterTransfer: ' . $resStr);
        }
        return $resStr;
    }

    public function deserializeObject( $jsonObject, $className, $serializer, $objectToPopulate=null ) {
        $transferableEntity = NULL;
        $serilizeFormat = 'xml';

        //dump($jsonObject);
        //exit('deserialize Object');

        //Case Project
        if( $className && $className == 'App\TranslationalResearchBundle\Entity\Project' ) {
            //$jsonObjectStr = json_encode($jsonObject);
            //$transferableEntity = $serializer->deserialize($jsonObjectStr, $className, 'json');

            //IGNORED_ATTRIBUTES
            $contextIGNOREDArr = [
                'submitter',
                'createDate',
                'updateUser',
                'updateDate',

                //Requesters
                'principalInvestigators',
                'principalIrbInvestigator',
                'coInvestigators',
                'pathologists',
                'contacts',
                'billingContact',
                'targetStateRequester',
                'submitInvestigators',

                'requesterGroup',
                'projectSpecialty',
                'exemptIrbApproval',
                'exemptIACUCApproval',
                'irbStatusList',
                'collDivs',
                'collLabs',
                'priceList',
                'compTypes',
                'tissueProcessingServices',
                'restrictedServices',
                'projectType',
                'irbExpirationDate',
                'expectedExpirationDate',
                'iacucExpirationDate',
                'studyDuration',

                //Files
                'documents',
                'irbApprovalLetters',
                'humanTissueForms'
            ];

            if(1) {
                if ($objectToPopulate) {
                    $updateProjectOriginalId = $objectToPopulate->getId();
                    //echo "deserialize Object: Update project " . $objectToPopulate->getOid() . ", original id=" . $updateProjectOriginalId . "<br>";
                    $transferableEntity = $serializer->denormalize(
                        $jsonObject,
                        $className,
                        $serilizeFormat,
                        [
                            AbstractNormalizer::IGNORED_ATTRIBUTES => $contextIGNOREDArr,
                            AbstractNormalizer::OBJECT_TO_POPULATE => $objectToPopulate
                        ]
                    );

                    $transferableEntity->setId($updateProjectOriginalId);

                    //$updateProjectId = $transferableEntity->getId();
                    //exit('$updateProjectId='.$objectToPopulate->getId());
                } else {
                    //echo "deserialize Object: Create new project <br>";
                    $transferableEntity = $serializer->denormalize(
                        $jsonObject,
                        $className,
                        $serilizeFormat,
                        [
                            AbstractNormalizer::IGNORED_ATTRIBUTES => $contextIGNOREDArr
                        ]
                    );
                }
            }

//            $transferableEntity = $serializer->denormalize(
//                $jsonObject,
//                $className,
//                $serilizeFormat,
//                [
//                    AbstractNormalizer::IGNORED_ATTRIBUTES => [
//                        'exemptIrbApproval',
//                        'exemptIACUCApproval',
//                        'irbStatusList'
//                    ]
//                ]
//            );

            //submitter
            //$submitterEmail = $jsonObject['submitter']['email'];
            //$submitterUsername = $jsonObject['submitter']['username'];
            //Search user by email and create if not found
            //addNewUserAjax
            //constractNewUser
            $this->convertUser($jsonObject,$transferableEntity,'submitter');

            //updateUser
            $this->convertUser($jsonObject,$transferableEntity,'updateUser');

            //principalInvestigators
            $this->convertUsers($jsonObject,$transferableEntity,'principalInvestigators','principalInvestigator');

            //'principalIrbInvestigator' => ['username','email'],
            $this->convertUser($jsonObject,$transferableEntity,'principalIrbInvestigator');

            //'coInvestigators' => ['username','email'],
            $this->convertUsers($jsonObject,$transferableEntity,'coInvestigators','coInvestigator');

            //'pathologists' => ['username','email'],
            $this->convertUsers($jsonObject,$transferableEntity,'pathologists','pathologist');

            //'contacts' => ['username','email'],
            $this->convertUsers($jsonObject,$transferableEntity,'contacts','contact');

            //'billingContact' => ['username','email'],
            $this->convertUser($jsonObject,$transferableEntity,'billingContact');

            //'targetStateRequester' => ['username','email'],
            $this->convertUser($jsonObject,$transferableEntity,'targetStateRequester');

             //'submitInvestigators' => ['username','email'],
            $this->convertUsers($jsonObject,$transferableEntity,'submitInvestigators','submitInvestigator');


            //createDate
            $this->convertDate($jsonObject,$transferableEntity,'createDate');

            //updateDate
            $this->convertDate($jsonObject,$transferableEntity,'updateDate');

            //irbExpirationDate
            $this->convertDate($jsonObject,$transferableEntity,'irbExpirationDate');

            //expectedExpirationDate
            $this->convertDate($jsonObject,$transferableEntity,'expectedExpirationDate');

            //studyDuration
            $this->convertDate($jsonObject,$transferableEntity,'studyDuration');

            //iacucExpirationDate
            $this->convertDate($jsonObject,$transferableEntity,'iacucExpirationDate');

            //projectSpecialty
            if( isset($jsonObject['projectSpecialty']) ) {
                $projectSpecialtyName = $jsonObject['projectSpecialty']['name'];
                //echo "projectSpecialtyName=" . $projectSpecialtyName . "<br>";
                //Find one by name SpecialtyList
                $projectSpecialtyEntity = $this->em->getRepository(SpecialtyList::class)->findOneByName($projectSpecialtyName);
                $transferableEntity->setProjectSpecialty($projectSpecialtyEntity);
            }

            //exemptIrbApproval
            if( isset($jsonObject['exemptIrbApproval']) ) {
                $exemptIrbApprovalName = $jsonObject['exemptIrbApproval']['name'];
                //echo "exemptIrbApprovalName=" . $exemptIrbApprovalName . "<br>";
                //Find one by name IrbApprovalTypeList
                $exemptIrbApprovalEntity = $this->em->getRepository(IrbApprovalTypeList::class)->findOneByName($exemptIrbApprovalName);
                $transferableEntity->setExemptIrbApproval($exemptIrbApprovalEntity);
            }

            //exemptIACUCApproval
            if( isset($jsonObject['exemptIACUCApproval']) ) {
                $exemptIACUCApprovalName = $jsonObject['exemptIACUCApproval']['name'];
                //echo "exemptIACUCApprovalName=" . $exemptIACUCApprovalName . "<br>";
                //Find one by name IrbApprovalTypeList, the same as exemptIrbApproval
                $exemptIACUCApprovalEntity = $this->em->getRepository(IrbApprovalTypeList::class)->findOneByName($exemptIACUCApprovalName);
                $transferableEntity->setExemptIACUCApproval($exemptIACUCApprovalEntity);
            }

            //irbStatusList
            if( isset($jsonObject['irbStatusList']) ) {
                $irbStatusListName = $jsonObject['irbStatusList']['name'];
                //echo "irbStatusList=" . $irbStatusListName . "<br>";
                $irbStatusListEntity = $this->em->getRepository(IrbStatusList::class)->findOneByName($irbStatusListName);
                $transferableEntity->setIrbStatusList($irbStatusListEntity);
            }

            //requesterGroup RequesterGroupList
            if( isset($jsonObject['requesterGroup']) ) {
                $listName = $jsonObject['requesterGroup']['name'];
                //echo "requesterGroup=" . $listName . "<br>";
                $listEntity = $this->em->getRepository(RequesterGroupList::class)->findOneByName($listName);
                $transferableEntity->setRequesterGroup($listEntity);
            }

            //collDivs CollDivList
            //dump($jsonObject);
            //exit('collDivs='.print_r($jsonObject['collDivs']));
            if( isset($jsonObject['collDivs']) ) {
                $collDivsArr = $jsonObject['collDivs'];
                //echo "collDivsArr=" . $collDivsArr . "<br>";
                foreach($collDivsArr as $collDivs) {
                    $collDivsName = $collDivs['name'];
                    $collDivsEntity = $this->em->getRepository(CollDivList::class)->findOneByName($collDivsName);
                    $transferableEntity->addCollDiv($collDivsEntity);
                }
            }
            //exit('collDivs='.print_r($jsonObject['collDivs']));

            //collLabs CollLabList
            if( isset($jsonObject['collLabs']) ) {
                $collLabsArr = $jsonObject['collLabs'];
                foreach($collLabsArr as $collLabs) {
                    $collLabsName = $collLabs['name'];
                    $collLabsEntity = $this->em->getRepository(CollLabList::class)->findOneByName($collLabsName);
                    $transferableEntity->addCollLab($collLabsEntity);
                }
            }

            //compTypes CompCategoryList
            if( isset($jsonObject['compTypes']) ) {
                $compTypesArr = $jsonObject['compTypes'];
                foreach($compTypesArr as $compTypes) {
                    $compTypesName = $compTypes['name'];
                    $compTypesEntity = $this->em->getRepository(CompCategoryList::class)->findOneByName($compTypesName);
                    $transferableEntity->addCompType($compTypesEntity);
                }
            }

            //tissueProcessingServices TissueProcessingServiceList
            if( isset($jsonObject['tissueProcessingServices']) ) {
                $itemsArr = $jsonObject['tissueProcessingServices'];
                foreach($itemsArr as $item) {
                    $itemName = $item['name'];
                    $itemEntity = $this->em->getRepository(TissueProcessingServiceList::class)->findOneByName($itemName);
                    $transferableEntity->addTissueProcessingService($itemEntity);
                }
            }

            //restrictedServices OtherRequestedServiceList
            if( isset($jsonObject['restrictedServices']) ) {
                $itemsArr = $jsonObject['restrictedServices'];
                foreach($itemsArr as $item) {
                    $itemName = $item['name'];
                    $itemEntity = $this->em->getRepository(OtherRequestedServiceList::class)->findOneByName($itemName);
                    $transferableEntity->addRestrictedService($itemEntity);
                }
            }

            //priceList PriceTypeList
            if( isset($jsonObject['priceList']) ) {
                $priceListName = $jsonObject['priceList']['name'];
                //echo "priceListName=" . $priceListName . "<br>";
                $priceListEntity = $this->em->getRepository(PriceTypeList::class)->findOneByName($priceListName);
                $transferableEntity->setPriceList($priceListEntity);
            }

            //projectType ProjectTypeList
            if( isset($jsonObject['projectType']) ) {
                $projectTypeName = $jsonObject['projectType']['name'];
                //echo "projectTypeName=" . $projectTypeName . "<br>";
                $projectTypeListEntity = $this->em->getRepository(ProjectTypeList::class)->findOneByName($projectTypeName);
                $transferableEntity->setProjectType($projectTypeListEntity);
            }

            //Files
            if(1) {
                //example: "documents":[],
                //"irbApprovalLetters":[{"originalname":"sample.pdf","uniqueid":null,"uploadDirectory":"Uploaded\/transres\/documents"}],
                //"humanTissueForms":[]"
                //uploadDirectory='Uploaded/transres/documents'
                //'documents',
                if (isset($jsonObject['documents'])) {
                    $this->downloadFile($jsonObject, $transferableEntity, 'documents','addDocument');
                }
                //'irbApprovalLetters',
                if (isset($jsonObject['irbApprovalLetters'])) {
                    $this->downloadFile($jsonObject, $transferableEntity, 'irbApprovalLetters','addIrbApprovalLetter');
                }
                //'humanTissueForms'
                if (isset($jsonObject['humanTissueForms'])) {
                    $this->downloadFile($jsonObject, $transferableEntity, 'humanTissueForms','addHumanTissueForm');
                }
            }
            //exit('exit after downloadFile');

//            echo "deserialize Object: ".$className.": transferableEntity ID=".$transferableEntity->getId()."<br>";
//
//            $submitter = $transferableEntity->getSubmitter();
//            echo "submitter=".$submitter."<br>";
//
//            $pis = $transferableEntity->getPrincipalInvestigators();
//            foreach($pis as $pi) {
//                echo "pi=".$pi."<br>";
//            }

            //$irbExpirationDate = $transferableEntity->getIrbExpirationDate();
           //echo "irbExpirationDate=".print_r($irbExpirationDate)."<br>";

            //dump($jsonObject);
            //exit('deserialize');
        }

        return $transferableEntity;
    }

    public function convertUser( $jsonObject, $transferableEntity, $fieldName ) {

        if( !isset($jsonObject[$fieldName]) ) {
            return $transferableEntity;
        }

        $logger = $this->container->get('logger');
        $userSecUtil = $this->container->get('user_security_utility');

        $email = $jsonObject[$fieldName]['email'];
        $username = $jsonObject[$fieldName]['username'];

        $userInfos = NULL;
        if( isset($singleUser['infos']) ) {
            $userInfos = $singleUser['infos'][0];
        }

        //echo "convertUser: $username, $email"."<br>";
        $logger->notice("convertUser: $username, $email");

        $user = $this->em->getRepository(User::class)->findOneByEmailCanonical($email);

        if(1) {
            if (!$user) {
                $users = $this->em->getRepository(User::class)->findUserByUserInfoEmail($email);
                if (count($users) > 0) {
                    $user = $users[0];
                }
            }
            if (!$user) {
                //Check if username is email
                $user = $userSecUtil->findUserByUsernameAsEmail($username);
            }
            if (!$user) {
                $user = $userSecUtil->getUserByUserstr($username);
            }
        }

        if( !$user ) {
            $user = $userSecUtil->constractNewUser($username);

            //$systemEmail = $userSecUtil->getSiteSettingParameter('siteEmail');
            $user->setEmail($username);
            $user->setEmailCanonical($email);
            $user->setCreatedby('system');
            $user->addRole('ROLE_USERDIRECTORY_OBSERVER');
            $user->setEnabled(false);
            //$user->setLocked(true);

            //TODO: fix user info details
            if( $userInfos ) {
                if( !$user->getUserInfo() ) {
                    $userInfo = new UserInfo();
                    $user->addInfo($userInfo);
                }
                //$userInfoEntity = new UserInfo();
                $user->setFirstName( $userInfos['firstName'] );
                $user->setLastName( $userInfos['lastName'] );
                $user->setMiddleName( $userInfos['middleName'] );
                $user->setDisplayName( $userInfos['displayName'] );
                $user->setPreferredPhone( $userInfos['preferredPhone'] );
                $user->setPreferredMobilePhone( $userInfos['preferredMobilePhone'] );
            }

            $this->em->persist($user);
        }

        if( $user ) {
            $setter = 'set'.$fieldName;
            $transferableEntity->$setter($user);

            //echo "User added ".$user."<br>";
        } else {
            //echo "User not found: $username, $email"."<br>";
        }

        return $transferableEntity;
    }

    public function convertUsers( $jsonObject, $transferableEntity, $fieldName, $setterBaseName ) {
        if( !isset($jsonObject[$fieldName]) ) {
            return $transferableEntity;
        }

        $logger = $this->container->get('logger');
        $userSecUtil = $this->container->get('user_security_utility');

        foreach($jsonObject[$fieldName] as $singleUser) {
            //dump($singleUser);
            //exit('111');

            $email = $singleUser['email'];
            $username = $singleUser['username'];

            $userInfos = NULL;
            if( isset($singleUser['infos']) ) {
                $userInfos = $singleUser['infos'][0];
            }

            //echo "convertUsers: $username, $email"."<br>";
            $logger->notice("convertUsers: $username, $email");

            $user = $this->em->getRepository(User::class)->findOneByEmailCanonical($email);

            if(1) {
                if (!$user) {
                    $users = $this->em->getRepository(User::class)->findUserByUserInfoEmail($email);
                    if (count($users) > 0) {
                        $user = $users[0];
                    }
                }
                if (!$user) {
                    //Check if username is email
                    $user = $userSecUtil->findUserByUsernameAsEmail($username);
                }
                if (!$user) {
                    $user = $userSecUtil->getUserByUserstr($username);
                }
            }

            if( !$user ) {
                $user = $userSecUtil->constractNewUser($username);

                //$systemEmail = $userSecUtil->getSiteSettingParameter('siteEmail');
                $user->setEmail($email);
                $user->setEmailCanonical($email);
                $user->setCreatedby('system');
                $user->addRole('ROLE_USERDIRECTORY_OBSERVER');
                $user->setEnabled(false);
                //$user->setLocked(true);

                //TODO: fix user info details
                if( $userInfos ) {
                    if( !$user->getUserInfo() ) {
                        $userInfo = new UserInfo();
                        $user->addInfo($userInfo);
                    }
                    //$userInfoEntity = new UserInfo();
                    $user->setFirstName( $userInfos['firstName'] );
                    $user->setLastName( $userInfos['lastName'] );
                    $user->setMiddleName( $userInfos['middleName'] );
                    $user->setDisplayName( $userInfos['displayName'] );
                    $user->setPreferredPhone( $userInfos['preferredPhone'] );
                    $user->setPreferredMobilePhone( $userInfos['preferredMobilePhone'] );
                }

                $this->em->persist($user);
            }

            if( $user ) {
                $setter = 'add'.$setterBaseName;
                $transferableEntity->$setter($user);

                //echo "User added ".$user."<br>";
            } else {
                //echo "User not found: $username, $email"."<br>";
            }
        }

        return $transferableEntity;
    }

    public function convertDate( $jsonObject, $transferableEntity, $fieldName ) {
        if( isset($jsonObject[$fieldName]) && isset($jsonObject[$fieldName]['timestamp']) ) {
            //OK
        } else {
            return $transferableEntity;
        }
        $dateTimestamp = $jsonObject[$fieldName]['timestamp'];
        //echo $fieldName.": dateTimestamp=".$dateTimestamp."<br>";
        $timezone = $jsonObject[$fieldName]['timezone']['name'];
        $date = new \DateTime('now', new \DateTimeZone($timezone));
        $date->setTimestamp($dateTimestamp);
        $setterMethod = 'set'.$fieldName;
        $getterMethod = 'get'.$fieldName;
        $transferableEntity->$setterMethod($date);
        //echo $fieldName.": date=".$transferableEntity->$getterMethod()->format('m/d/Y')."<br>";
        return $transferableEntity;
    }


    //SSH or SFTP connection by phpseclib3
    //On master server (server which will get the file from slave): provide a private key in field 'SSH password/key' in InterfaceTransferList
    //On slave server (server which has a file to transfer to master): Specify the public key to use by changing AuthorizedKeysFile:
    //1) vim /etc/ssh/sshd_config
    //2) AuthorizedKeysFile /etc/ssh/ssh_host_ed25519_key.pub
    //3) sudo systemctl restart sshd
    public function connectByPublicKey( $transferableEntity, $type='SFTP' ) {
        $mapper = $this->classListMapper($transferableEntity);
        $entityName = $mapper['entityName'];

        $strServer = NULL;
        $interfaceTransfer = $this->getInterfaceTransferByName($entityName);
        if( $interfaceTransfer ) {
            $strServer = $interfaceTransfer->getTransferSource();  //"159.203.95.150";
            //Since $strServer might be 'view.online/c/wcm/pathology' for multitenancy, get only view.online
            //$strServerArr = parse_url($strServer);
            //$pathInfo = pathinfo($strServer, PHP_URL_HOST);
            $exploded_server = explode('/', $strServer);
            //dump($exploded_server);exit('111');
            //return $strServerArr['scheme']."://".$result['host'];
            if( count($exploded_server) > 0 ) {
                $strServer = $exploded_server[0];
            }
        } else {
            return false;
        }

        //$server_address = '68.183.146.32'; //'68.183.144.189';
        //$strServer = 'view.online';
        //$strServer = '68.183.146.32';
        //$strServer = '68.183.144.189';
        $strServerPort = "22";
        //$strServerUsername = $interfaceTransfer->getSshUsername();
        $privateKeyContent = $interfaceTransfer->getSshPassword();

        //$strServerUsername = '';
        //$strServerPassword = '';
        //echo "strServer=$strServer, strServerPort=$strServerPort, privateKeyContent=$privateKeyContent <br>";

        //testing phpseclib
        //$command = 'pwd'; //root

//        $ssh = new SSH2($strServer);
//        if (!$ssh->login($strServerUsername, $strServerPassword)) {
//            $output ='Login Failed';
//        }
        //Change AuthorizedKeysFile .ssh/authorized_keys to
        //AuthorizedKeysFile /etc/ssh/id_ed25519_2.pub //public key on slave
        //$private_key_path = "C:/Users/cinav/.ssh/id_ed25519_2"; //private key on master
        //$privateKeyContent = file_get_contents($private_key_path);
        $key = PublicKeyLoader::load($privateKeyContent);

        if( $type == 'SFTP' ) {
            $sshConnection = new SFTP($strServer);
        } else {
            $sshConnection = new SSH2($strServer);
        }

//        if (!$sftpConnection->login('root', $key)) {
//            throw new \Exception('SFTP login failed with private key');
//        } else{
//            //$projectRoot = $this->container->get('kernel')->getProjectDir();
//            //$testFile = '/usr/local/bin/order-lab-tenantapp1/orderflex/public/Uploaded/transres/documents/668c329c96a32.pdf';
//            //$sftpConnection->get($testFile, $projectRoot.'\testFileLocal.pdf');
//            //exit('Copied file ');
//            return $sftpConnection;
//        }
//        //exit('111');

        if( !$sshConnection->login('root', $key) ) {
            throw new \Exception($type.' login failed with private key');
        } else{
            return $sshConnection;
        }

        return null;
    }

    //$jsonObject, $transferableEntity, 'humanTissueForms'
    public function downloadFile( $jsonObject, $transferableEntity, $field, $adder ) {
        //$sshConnection = $this->connectByPublicKey($transferableEntity,'SSH');
        //$output = $sshConnection->exec('pwd');

        try {
            $sftpConnection = $this->connectByPublicKey($transferableEntity,'SFTP');
        } catch( \Exception $e ) {
            //echo 'Caught connection exception: ', $e->getMessage(), "\n";
            return false;
        }

        $sftpConnection->enableDatePreservation(); //preserver original file last modified date

        //dump($jsonObject);
        //exit('111');

        $transferRes = array();

        $instanceId = $jsonObject['instanceId'];
        $apppath = $jsonObject['apppath'];
        $jsonDocuments = $jsonObject[$field];
        //echo $field.": jsonDocuments count=".count($jsonDocuments)."<br>";

        foreach($jsonDocuments as $jsonDocument) {
            //Example $jsonObject: "irbApprovalLetters":[{"originalname":"sample.pdf","uniqueid":null,
            //"uniquename" => "668c329c96a32.pdf","uploadDirectory":"Uploaded\/transres\/documents"},
            //"type" => array:1 ["name" => "IRB Approval Letter"]],
            //$originalname = $jsonDocument['originalname'];
            //$uniqueid = $jsonDocument['uniqueid'];
//            $type = $jsonDocument['type'];
//            $typeName = NULL;
//            if( isset($type['type']['name']) ) {
//                $typeName = $type['type']['name'];
//            }
            $uniquename = $jsonDocument['uniquename'];
            $uploadDirectory = $jsonDocument['uploadDirectory']; //add additional check if upload directory on this server the same

            //TODO: transfer file with the same name and add to the project
            //source file
            $sourceFile = $apppath.'/'.'public'.'/'.$uploadDirectory.'/'.$uniquename;

            //copy file to public/Uploaded/transres/documents
            $projectRoot = $this->container->get('kernel')->getProjectDir();
            $destinationFileName = $instanceId.'-'.$uniquename;
            $destinationFile = $projectRoot.'/public/Uploaded/transres/documents/'.$destinationFileName;
            //$testFile = '/usr/local/bin/order-lab-tenantapp1/orderflex/public/Uploaded/transres/documents/668c329c96a32.pdf';

            //echo "sourceFile=".$sourceFile.", destinationFile=".$destinationFile."<br>";

            $output = $sftpConnection->get($sourceFile, $destinationFile);

            if( $output ) {
                //echo "Transfer file success <br>";
                $document = $this->createNewDocumentFromJson($jsonDocument,$destinationFileName,$destinationFile);
                if( $document ) {
                    $this->em->persist($document);
                    $transferableEntity->$adder($document);
                }
            } else {
                //echo "Transfer file fail <br>";
            }

            $transferRes[] = $output;
        }

        //echo $field.': downloadFile='.implode(', ',$transferRes);
        return true;
    }

    public function createNewDocumentFromJson( $jsonDocument, $fileUniqueName, $filePath ) {

        $documentDb = $this->em->getRepository(Document::class)->findOneByUniquename($fileUniqueName);
        if( $documentDb ) {
            $logger = $this->container->get('logger');
            $event = "Document already exists with fileUniqueName=".$fileUniqueName;
            $logger->notice($event);
            //return $documentDb;
            return NULL;
        }

        $userSecUtil = $this->container->get('user_security_utility');
        $systemUser = $userSecUtil->findSystemUser();

        $originalname = $jsonDocument['originalname'];
        $uniqueid = $jsonDocument['uniqueid'];
        //$uniquename = $jsonDocument['uniquename'];
        $uploadDirectory = $jsonDocument['uploadDirectory'];
        $type = $jsonDocument['type'];
        $documentTypeName = NULL;
        if( isset($type['type']['name']) ) {
            $documentTypeName = $type['type']['name'];
        }

        $filesize = NULL;
        if( file_exists($filePath) ) {
            $filesize = filesize($filePath);
        } else {
            return NULL;
        }

        $object = new Document($systemUser);
        $object->setUniqueid($uniqueid);
        $object->setUniquename($fileUniqueName);
        $object->setUploadDirectory($uploadDirectory);
        $object->setOriginalname($originalname);
        $object->setSize($filesize);

        //TODO: use $file->getCreatedTime for creation date? (https://developers.google.com/drive/api/v3/reference/files#createdTime)
        //$file->getCreatedTime is available only in 2.0 google/apiclient
        //https://developers.google.com/resources/api-libraries/documentation/drive/v3/php/latest/class-Google_Service_Drive_DriveFile.html

        //clean originalname
        $object->setCleanOriginalname($originalname);

        $transformer = new GenericTreeTransformer($this->em, $systemUser, "DocumentTypeList", "UserdirectoryBundle");
        $documentTypeName = trim((string)$documentTypeName);
        $documentTypeObject = $transformer->reverseTransform($documentTypeName);
        if( $documentTypeObject ) {
            $object->setType($documentTypeObject);
        }

        return $object;
    }

//    function testconnect() {
//        $host = '68.183.146.32';
//        $login = '';
//        $password = '';
//        $co = ssh2_connect($host, 22);
//        if (false === $co)
//            throw new \Exception('Can\'t connect to remote server');
//        $result = ssh2_auth_password($co, $login, $password);
//        var_dump($result);
//        if ($result === false) {
//            throw new \Exception('Authentication failed!');
//        }
//        echo 'Connection estabilished';
//    }
    //TODO: CURL encrypt - decrypt json
    //$ciphertext = $private->getPublicKey()->encrypt($plaintext);
    //echo $private->decrypt($ciphertext);
    //ssh2 https://phpseclib.com/docs/why
    //The public key can be used to encrypt messages that only the private key can decrypt.
    //Therefore: 1) place public key on external server and use this key to ecrypt file
    //2) place private key on internal server and use it to decrypt the file
    //Use ftp to get file from public server
//    public function downloadFile_test( $jsonObject, $transferableEntity, $field ) {
//
////        try {
////            $this->testconnect();
////        } catch (\Exception $e) {
////            echo 'Caught exception: ', $e->getMessage(), "\n";
////        }
////        exit('000');
//
//        $sshConnection = $this->connectByPublicKey($transferableEntity);
//        $output = $sshConnection->exec('pwd');
//        exit('111: '.$output);
//
//        $mapper = $this->classListMapper($transferableEntity);
//        //$className = $mapper['className'];
//        $entityName = $mapper['entityName'];
//
//        $strServer = NULL;
//        $interfaceTransfer = $this->getInterfaceTransferByName($entityName);
//        if( $interfaceTransfer ) {
//            $strServer = $interfaceTransfer->getTransferSource();  //"159.203.95.150";
//        } else {
//            return false;
//        }
//
//        //$server_address = '68.183.146.32'; //'68.183.144.189';
//        $strServer = 'view.online';
//        $strServer = '68.183.146.32';
//        //$strServer = '68.183.144.189';
//        $strServerPort = "22";
//        $strServerUsername = $interfaceTransfer->getSshUsername();
//        $strServerPassword = $interfaceTransfer->getSshPassword();
//
//        //$strServerUsername = '';
//        //$strServerPassword = '';
//        echo "strServer=$strServer, strServerPort=$strServerPort, strServerUsername=$strServerUsername, strServerPassword=$strServerPassword <br>";
//
//        //testing phpseclib
//        $command = 'pwd'; //root
//
////        $ssh = new SSH2($strServer);
////        if (!$ssh->login($strServerUsername, $strServerPassword)) {
////            $output ='Login Failed';
////        }
//        //Change AuthorizedKeysFile .ssh/authorized_keys to
//        //AuthorizedKeysFile /etc/ssh/id_ed25519_2.pub //public key on slave
//        $private_key_path = "C:/Users/cinav/.ssh/id_ed25519_2"; //private key on master
//        $key = PublicKeyLoader::load(file_get_contents($private_key_path));
//        $ssh = new SSH2($strServer);
//        if (!$ssh->login('root', $key)) {
//            throw new \Exception('Login failed');
//        }
//        else{
//            $output = $ssh->exec($command);
//        }
//        exit('111: '.$output);
//
//        //change root password: passwd
//        $dstConnection = ssh2_connect($strServer, $strServerPort);
//
//        //$auth_methods = ssh2_auth_none($dstConnection, 'apache');
//        //dump($auth_methods);
//        //exit('000');
////        if (in_array('password', $auth_methods)) {
////            echo "Server supports password based authentication\n";
////        }
//
//        if( ssh2_auth_password($dstConnection, $strServerUsername, $strServerPassword) ){
//            //Ok, continue
//            echo "Connected to $strServer <br>";
//        } else {
//            ///var/log/auth.log
//            exit("Unable to connect to the remote server");
//        }
//
//        //Test read file
//        $srcFile = '';
//        $dstSFTP = ssh2_sftp($dstConnection);
//        echo "dstSFTP=$dstSFTP <br>";
//        //$dstSFTP = intval($dstSFTP);
//        //echo "dstSFTP=$dstSFTP <br>";
//        //$dstFile = fopen("ssh2.sftp://{$dstSFTP}/".$srcFile, 'w');
//        //$dstFile = fopen("ssh2.sftp://" . intval($dstSFTP) . "/" . $srcFile, 'r'); //w or r
//        //dump($dstFile);
//
//        $testFilePath = '/usr/local/bin/order-lab-tenantapp1/orderflex/public/Uploaded/directory/documents/66858c411569b.png';
//
//        $dstFile = fopen("ssh2.sftp://{$dstSFTP}/".$testFilePath, 'r');
//        if ( !$dstFile ) {
//            throw new \Exception('File open failed. file=' . $testFilePath);
//        }
//
//        //$dstTestFile = fopen("ssh2.sftp://{$dstSFTP}/".$dstTestFilePath, 'r');
//        //$contents = stream_get_contents($dstTestFile);
//        //dump($contents);
//
//        $srcFile = fopen($testFilePath, 'r');
//
//        $writtenBytes = stream_copy_to_stream($srcFile, $dstFile);
//        echo "writtenBytes=$writtenBytes <br>";
//        fclose($dstFile);
//        fclose($srcFile);
//
//        exit('111');
//
//        $ssh = new SSH2($server_address);
//        $ssh->login('root');
//        $ssh->read('User Name:');
//        $ssh->write("username\n");
//        $ssh->read('Password:');
//        $ssh->write("password\n");
//
//        $ssh->setTimeout(1);
//        $ssh->read();
//        $ssh->write("ls -la\n");
//        echo $ssh->read();
//        exit('111');
//
//        $key = PublicKeyLoader::load(file_get_contents($private_key_path));
//        $ssh = new SSH2($server_address);
//        if (!$ssh->login('root', $key)) {
//            throw new \Exception('Login failed');
//        }
//
//        //$key = RSA::loadFormat('PKCS1', file_get_contents($public_key_path));
//
//        $connection = ssh2_connect($server_address, $port, array('hostkey'=>'ssh-rsa'));
//        if (!@ssh2_auth_pubkey_file($connection, $username, $public_key_path, $private_key_path, $password))
//        {
//            echo '<h3 class="error">Unable to authenticate. Check ssh key pair.</h3>';
//        } else {
//            echo '<h3 class="success">Authenticated.</h3>';
//        }
//        exit('222');
//
//        $ssh = new SSH2($server_address);
//        //$privateKeyFilePath = "C:/Users/cinav/.ssh/id_ed25519";
//        $ssh->login('root', PublicKeyLoader::load(file_get_contents($private_key_path)));
//        print_r($ssh->getErrors());
//        //print_r($ssh->getLastError());
//
//        echo $ssh->exec('ls -la');
//
//        exit('222');
//
//        $privateKey = RSA::createKey();
//        $public = $privateKey->getPublicKey();
//
//        //public key: /usr/local/bin/order-lab/orderflex/public/Uploaded/transres/documents
//
//        // in case that key has a password
//        $privateKey->withPassword('private key password');
//
//        // load the private key
//        $privateKeyFilePath = "C:/Users/cinav/.ssh/id_ed25519";
//        $privateKey->loadKey(file_get_contents($privateKeyFilePath));
//        echo "privateKey=".$privateKey."<br>";
//
//
//
//        $mapper = $this->classListMapper($transferableEntity);
//        //$className = $mapper['className'];
//        $entityName = $mapper['entityName'];
//
//        $strServer = NULL;
//        $interfaceTransfer = $this->getInterfaceTransferByName($entityName);
//        if( $interfaceTransfer ) {
//            $strServer = $interfaceTransfer->getTransferSource();  //"159.203.95.150";
//        } else {
//            return false;
//        }
//
//        // login via sftp
//        if (!$sftp->login('username', $privateKey)) {
//            throw new Exception('sFTP login failed');
//        }
//
//        // now you can list what's in here
//        $filesAndFolders = $sftp->nlist();
//
//        $ssh = new SSH2($strServer);
//        $ssh->login(
//            'username',
//            PublicKeyLoader::load(file_get_contents('/home/ubuntu/privkey'))
//        );
//
//        $strServerPort = "22";
//        $strServerUsername = $interfaceTransfer->getSshUsername();
//        $strServerPassword = $interfaceTransfer->getSshPassword();
//
//        //connect to server
//        $dstConnection = ssh2_connect($strServer, $strServerPort);
//
//        if( ssh2_auth_password($dstConnection, $strServerUsername, $strServerPassword) ){
//            //Ok, continue
//            echo "Connected to $strServer <br>";
//        } else {
//            exit("Unable to connect to the remote server");
//        }
//
//        //$ssh = new SSH2('domain.tld');
//        //$ssh->login('username', PublicKeyLoader::load(file_get_contents('/home/ubuntu/privkey')/*, 'password'*/);
//
//    }
//    public function downloadFile_orig( $jsonObject, $transferableEntity, $field ) {
//
//        $server_address = '68.183.144.189';
//        $port = 22;
//        $username = 'root';
//        $public_key_path = "path/pubkey.pub";
//        $private_key_path = "path/privakey";
//        $private_key_path = '/path/privakey.';
//        $public_key_path = "/path/pubkey.pub";
//        $password = '';
//        $local_file = 'path/test.txt';
//
//        //$ssh = new SSH2($server_address);
//        //if (!$ssh->login('username', 'mypass')) {
//        //    throw new \Exception('Login failed');
//        //}
//
//        $conn_id = ftp_connect($server_address);
//
//        // login with username and password
//        $login_result = ftp_login($conn_id, $username, 'mypass');
//
//        // try to download $server_file and save to $local_file
//        if (ftp_get($conn_id, $local_file, $private_key_path, FTP_BINARY)) {
//            echo "Successfully written to $local_file\n";
//        }
//        else {
//            echo "There was a problem\n";
//        }
//// close the connection
//        ftp_close($conn_id);
//        exit('111');
//
//        //change root password: passwd
//        $dstConnection = ssh2_connect($server_address, $port);
//        if( ssh2_auth_password($dstConnection, $username, 'mypass') ){
//            //Ok, continue
//            echo "Connected to $server_address <br>";
//        } else {
//            exit("Unable to connect to the remote server");
//        }
//        exit('111');
//
//        $ssh = new SSH2($server_address);
//        $ssh->login('root');
//        $ssh->read('User Name:');
//        $ssh->write("username\n");
//        $ssh->read('Password:');
//        $ssh->write("password\n");
//
//        $ssh->setTimeout(1);
//        $ssh->read();
//        $ssh->write("ls -la\n");
//        echo $ssh->read();
//        exit('111');
//
//        $key = PublicKeyLoader::load(file_get_contents($private_key_path));
//        $ssh = new SSH2($server_address);
//        if (!$ssh->login('root', $key)) {
//            throw new \Exception('Login failed');
//        }
//
//        //$key = RSA::loadFormat('PKCS1', file_get_contents($public_key_path));
//
//        $connection = ssh2_connect($server_address, $port, array('hostkey'=>'ssh-rsa'));
//        if (!@ssh2_auth_pubkey_file($connection, $username, $public_key_path, $private_key_path, $password))
//        {
//            echo '<h3 class="error">Unable to authenticate. Check ssh key pair.</h3>';
//        } else {
//            echo '<h3 class="success">Authenticated.</h3>';
//        }
//        exit('222');
//
//        $ssh = new SSH2($server_address);
//        //$privateKeyFilePath = "C:/Users/cinav/.ssh/id_ed25519";
//        $ssh->login('root', PublicKeyLoader::load(file_get_contents($private_key_path)));
//        print_r($ssh->getErrors());
//        //print_r($ssh->getLastError());
//
//        echo $ssh->exec('ls -la');
//
//        exit('222');
//
//        $privateKey = RSA::createKey();
//        $public = $privateKey->getPublicKey();
//
//        //public key: /usr/local/bin/order-lab/orderflex/public/Uploaded/transres/documents
//
//        // in case that key has a password
//        $privateKey->withPassword('private key password');
//
//        // load the private key
//        $privateKeyFilePath = "C:/Users/cinav/.ssh/id_ed25519";
//        $privateKey->loadKey(file_get_contents($privateKeyFilePath));
//        //echo "privateKey=".$privateKey."<br>";
//
//        $mapper = $this->classListMapper($transferableEntity);
//        //$className = $mapper['className'];
//        $entityName = $mapper['entityName'];
//
//        $strServer = NULL;
//        $interfaceTransfer = $this->getInterfaceTransferByName($entityName);
//        if( $interfaceTransfer ) {
//            $strServer = $interfaceTransfer->getTransferSource();  //"159.203.95.150";
//        } else {
//            return false;
//        }
//
//        // login via sftp
//        if (!$sftp->login('username', $privateKey)) {
//            throw new Exception('sFTP login failed');
//        }
//
//        // now you can list what's in here
//        $filesAndFolders = $sftp->nlist();
//
//        $ssh = new SSH2($strServer);
//        $ssh->login(
//            'username',
//            PublicKeyLoader::load(file_get_contents('/home/ubuntu/privkey'))
//        );
//
//        $strServerPort = "22";
//        $strServerUsername = $interfaceTransfer->getSshUsername();
//        $strServerPassword = $interfaceTransfer->getSshPassword();
//
//        //connect to server
//        $dstConnection = ssh2_connect($strServer, $strServerPort);
//
//        if( ssh2_auth_password($dstConnection, $strServerUsername, $strServerPassword) ){
//            //Ok, continue
//            //echo "Connected to $strServer <br>";
//        } else {
//            exit("Unable to connect to the remote server");
//        }
//
//        //$ssh = new SSH2('domain.tld');
//        //$ssh->login('username', PublicKeyLoader::load(file_get_contents('/home/ubuntu/privkey')/*, 'password'*/);
//
//    }

    //Run on internal (master) server
    //send request to remote server to send all transferable in the response
    public function getSlaveToMasterTransferCurl($className) {
        $userUtil = $this->container->get('user_utility');
        $session = $userUtil->getSession();
        $userSecUtil = $this->container->get('user_security_utility');

        //TODO: move secret key to the Transfer Interface for each source or destination server
        $secretKey = $userSecUtil->getSiteSettingParameter('secretKey');
        if( !$secretKey ) {
            $session->getFlashBag()->add(
                'warning',
                "Please set a Secret Key in the site settings"
            );
            //return "Please set a Secret Key in the site settings";
            return false;
        }

        $entityName = $this->getEntityName($className);
        $interfaceTransfer = $this->getInterfaceTransferByName($entityName);

        if( !$interfaceTransfer ) {
            $session->getFlashBag()->add(
                'warning',
                "Please create a transfer interface with the source and destination information"
            );
            //return "Please create a transfer interface with the source and destination information";
            return false;
        }

        //Check if $instanceId is set
        $userSecUtil = $this->container->get('user_security_utility');
        $instanceId = $uploadPath = $userSecUtil->getSiteSettingParameter('instanceId');
        if( !$instanceId ) {
            $session->getFlashBag()->add(
                'warning',
                "Please set an Instance ID in the site settings"
            );
            //return "Please set an Instance ID in the site settings";
            return false;
        }

        //Add hash and security key
        $jsonFile = array();
        $jsonFile['className'] = $className;

        $hash = hash('sha512', $secretKey . serialize($jsonFile));
        $jsonFile['hash'] = $hash;

        $data_string = json_encode($jsonFile);
        $strServer = $interfaceTransfer->getTransferSource();  //view.online
        $remoteCertificate = $interfaceTransfer->getRemoteCertificate();  //path to crt or pem file

        if( !file_exists($remoteCertificate) ) {
            $session->getFlashBag()->add(
                'warning',
                "Remote certificate $remoteCertificate for curl does not exists"
            );
            return false;
        } else {
            $session->getFlashBag()->add(
                'notice',
                "Remote certificate $remoteCertificate for curl exists"
            );
        }

        //http://view.online/directory/transfer-interface/slave-to-master-transfer
        $url = 'https://'.$strServer.'/directory/transfer-interface/slave-to-master-transfer';
        //$url = 'https://'.'view-test.med.cornell.edu'.'/directory/transfer-interface/slave-to-master-transfer';

        //echo "url=$url <br>";
        //exit('111');
        $ch = curl_init($url);
        //exit('111');

        if(1) {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json',
                'Content-Length: ' . strlen($data_string)
            ));

            //$this->verifyPeer = true;
            if( $this->verifyPeer ) {
                //https://stackoverflow.com/questions/4372710/php-curl-https
                //curl_setopt($ch,CURLOPT_SSL_VERIFYPEER, false); is a quick fix
                //The proper way is: curl_setopt($ch, CURLOPT_CAINFO, $_SERVER['DOCUMENT_ROOT'] .  "/../cacert-2017-09-20.pem");
                //Fix: https://unitstep.net/blog/2009/05/05/using-curl-in-php-to-access-https-ssltls-protected-sites/
                //1) visit remote site view.online
                //2) view certificate => export/download as pem or crt
                //3) use this remote certificate in CURLOPT_CAINFO
                //Install: yum install ca-certificates
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
                curl_setopt($ch,CURLOPT_SSL_VERIFYHOST, 2);
                curl_setopt($ch, CURLOPT_CAINFO, $remoteCertificate);
                //curl_setopt($ch, CURLOPT_CAINFO, 'C:\Users\cinav\Documents\WCMC\Certificate\view-online\view-online.pem');
            } else {
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); //This is dangerous - remove it. use CURLOPT_CAINFO
            }
        }

        $result = curl_exec($ch);
        $status = curl_getinfo($ch);
        $error = curl_error($ch);
        curl_close($ch);

        //dump($error);
        //dump($status);
        //dump($result);
        //exit('111');

        if( $status['http_code'] != 200 || $error ) {
            $session->getFlashBag()->add(
                'warning',
                "Curl failed: "." url=".$url." => http_code=".$status['http_code']."; error=".$error
            );
        }

        if( $status['http_code'] == 200 && $result ) {
            $result = json_decode($result, true);
            //if( !$result ) {
            //    return false;
            //}
            $checksum = $result['checksum'];
            $valid = $result['valid'];
            //$transferResult = $result['transferResult'];

            //dump($result);
            //echo 'hash='.$hash.'<br>';
            //exit('222');

            //&& $transferResult
            if ($checksum === $hash && $valid === true ) {
                //echo "Successfully sent: " . $jsonFile['className'] . " <br>";
                return $result;
            } else {
                //return "Curl is not valid";
                $session->getFlashBag()->add(
                    'warning',
                    "Curl: invalid handshake: ".
                    "Hash: [".$checksum . "]?=[" . $hash . "]".
                    ", valid=".$valid
                    //.", transferResult=".var_dump($transferResult)
                );
            }
        }

        //exit('get SlaveToMasterTransferCurl false');
        return false;
    }

    //Run on external (slave)
    public function sendSlavetoMasterTransfer( $jsonFile ) {
        $logger = $this->container->get('logger');

        $userSecUtil = $this->container->get('user_security_utility');
        $instanceId = $uploadPath = $userSecUtil->getSiteSettingParameter('instanceId');
        if( !$instanceId ) {
            $instanceId = 'NA';
        }

        //1) get TransferData
        $className = $jsonFile['className'];
        $transferDatas = $this->findAllTransferDataByClassname($className,'Ready');

        $encoders = [new XmlEncoder(), new JsonEncoder()];
        $normalizers = [new ObjectNormalizer()];
        $serializer = new Serializer($normalizers, $encoders);

        $jsonRes = array();
        //$jsonRes['count'] = count($transferDatas);
        $logger->notice('$transferableEntity count='.count($transferDatas));

        foreach($transferDatas as $transferData) {
            $localId = $transferData->getLocalId();
            //$globalId = $transferData->getGlobalId();
            $className = $transferData->getClassName();
            $sourceId = $localId."@".$instanceId;

            $transferableEntity = $this->em->getRepository($className)->find($localId);
            $globalId = $transferableEntity->getGlobalId();
            $logger->notice('$transferableEntity ID='.$transferableEntity->getId()."; globalId=".$globalId);

            //set sourceId if not set yet
            if( !$transferableEntity->getSourceId() ) {
                $transferableEntity->setSourceId($sourceId);
                $this->em->flush();
            }

            //$serilizeFormat = 'json';
            $serilizeFormat = 'xml';
            //$serilizeFormat = NULL;

            //context for Project
            $context = [AbstractNormalizer::ATTRIBUTES => [
                'id',
                'oid',
                'sourceId',
                'globalId',
                'createDate',
                'submitter' => ['username','email'],
                'updateUser' => ['username','email'],
                'updateDate',

                //Requesters
                'principalInvestigators' => [
                    'username',
                    'email',
                    'infos'=>[
                        'firstName',
                        'middleName',
                        'lastName',
                        'displayName',
                        'preferredPhone',
                        'preferredMobilePhone'
                    ]
                ],
                'principalIrbInvestigator' => ['username','email'],
                'coInvestigators' => ['username','email'],
                'pathologists' => ['username','email'],
                'contacts' => ['username','email'],
                'billingContact' => ['username','email'],
                'targetStateRequester' => ['username','email'],
                'submitInvestigators' => ['username','email'],

                'requesterGroup' => ['name'],
                'state',
                'title',
                'projectSpecialty' => ['name'],
                'exemptIrbApproval' => ['name'],
                'irbNumber',
                'irbExpirationDate',
                'irbStatusList' => ['name'],
                'irbStatusExplain',
                'exemptIACUCApproval' => ['name'],
                'iacucNumber',
                'iacucExpirationDate',
                'projectType' => ['name'],
                'description',
                'collDivs' => ['name'],
                'collLabs' => ['name'],
                'compTypes' => ['name'],
                'hypothesis',
                'needStatSupport',
                'amountStatSupport',
                'needInfSupport',
                'amountInfSupport',
                'studyPopulation',
                'numberPatient',
                'numberLabReport',
                'studyDuration',
                'priceList' => ['name'],
                'funded',
                'collDepartment',
                'collInst',
                'collInstPi',
                'essentialInfo',
                'objective',
                'strategy',
                'expectedResults',
                'fundByPath',
                'fundDescription',
                'otherResource',
                'fundedAccountNumber',
                'totalCost',
                'noBudgetLimit',
                'approvedProjectBudget',
                'expectedExpirationDate',
                'involveHumanTissue',
                'requireTissueProcessing',
                'totalNumberOfPatientsProcessing',
                'totalNumberOfSpecimensProcessing',
                'tissueNumberOfBlocksPerCase',
                'tissueProcessingServices' => ['name'],
                'requireArchivalProcessing',
                'totalNumberOfPatientsArchival',
                'totalNumberOfSpecimensArchival',
                'totalNumberOfBlocksPerCase',
                'quantityOfSlidesPerBlockStained',
                'quantityOfSlidesPerBlockUnstained',
                'quantityOfSlidesPerBlockUnstainedIHC',
                'quantityOfSpecialStainsPerBlock',
                'quantityOfParaffinSectionsRnaDnaPerBlock',
                'quantityOfTmaCoresRnaDnaAnalysisPerBlock',
                'restrictedServices' => ['name'],
                'tissueFormComment',
                
                //Files
                'documents' => ['uploadDirectory','originalname','uniqueid','uniquename','type'=>['name']],
                'irbApprovalLetters' => ['uploadDirectory','originalname','uniqueid','uniquename','type'=>['name']],
                'humanTissueForms' => ['uploadDirectory','originalname','uniqueid','uniquename','type'=>['name']],
            ]];

            //https://symfony.com/doc/current/components/serializer.html#handling-serialization-depth
            $json = $serializer->normalize(
                $transferableEntity,
                $serilizeFormat, //'json',
                $context
//                [AbstractNormalizer::ATTRIBUTES => [
//                    'id',
//                    'oid',
//                    'title',
//                    'irbNumber'
//                ]]
            );

            //$json['sourceId'] = $sourceId;
            //$json['globalId'] = $globalId; //if transfer is the first time, than $globalId is NULL
            $json['instanceId'] = $instanceId;
            $json['className'] = $className;
            $json['apppath'] = $this->container->get('kernel')->getProjectDir(); ///usr/local/bin/order-lab/orderflex

            //dump($json);
            //exit('sendSlavetoMasterTransfer');

            $logger->notice('$json: id='.$json['id'].", oid=".$json['oid']);
            //$logger->notice(print_r($json));

            $jsonRes[] = $json;
        }

        return $jsonRes;
    }

    //Run on internal (master)
    public function sendConfirmationToSourceServer( $confirmationResponse ) { //$transferableEntity, $localId ) {

        if( count($confirmationResponse) == 0 ) {
            return null;
        }

        $userUtil = $this->container->get('user_utility');
        $session = $userUtil->getSession();

        $className = $confirmationResponse[0]['className'];
        $entityName = $this->getEntityName($className);
        //echo "send ConfirmationToSourceServer: className=$className, $entityName <br>";

        $userSecUtil = $this->container->get('user_security_utility');
        $secretKey = $userSecUtil->getSiteSettingParameter('secretKey');

        $interfaceTransfer = $this->getInterfaceTransferByName($entityName);

        //Add hash and security key
        $jsonFile = array();
        $jsonFile['className'] = $className;

        $hash = hash('sha512', $secretKey . serialize($confirmationResponse));
        $jsonFile['hash'] = $hash;

        //$jsonFile['localId'] = $transferableEntity->getGlobalId(); //original source id
        //$jsonFile['globalId'] = $transferableEntity->getGlobalId();
        $jsonFile['confirmationResponse'] = $confirmationResponse;

        $data_string = json_encode($jsonFile);
        //dump($data_string);
        //exit('111');

        $strServer = $interfaceTransfer->getTransferSource();  //view.online
        $remoteCertificate = $interfaceTransfer->getRemoteCertificate();  //path to crt or pem file

        //http://view.online/directory/transfer-interface/slave-to-master-transfer
        //Send back to slave (external) global ID of newly generated Project
        $url = 'https://'.$strServer.'/directory/transfer-interface/confirmation-master-to-slave';

        //echo "url=$url <br>";
        $ch = curl_init($url);

        if(1) {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json',
                'Content-Length: ' . strlen($data_string)
            ));

            if( $this->verifyPeer ) {
                //https://stackoverflow.com/questions/4372710/php-curl-https
                //curl_setopt($ch,CURLOPT_SSL_VERIFYPEER, false); is a quick fix
                //The proper way is: curl_setopt($ch, CURLOPT_CAINFO, $_SERVER['DOCUMENT_ROOT'] .  "/../cacert-2017-09-20.pem");
                //Fix: https://unitstep.net/blog/2009/05/05/using-curl-in-php-to-access-https-ssltls-protected-sites/
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
                curl_setopt($ch,CURLOPT_SSL_VERIFYHOST, 2);
                curl_setopt($ch, CURLOPT_CAINFO, $remoteCertificate);
            } else {
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); //Danger TODO: use CURLOPT_CAINFO
            }
        }


        $result = curl_exec($ch);
        $status = curl_getinfo($ch);
        $error = curl_error($ch);
        curl_close($ch);

        //dump($status);
        //dump($result);
        //exit('111');

        if( $status['http_code'] != 200 || $error ) {
            $session->getFlashBag()->add(
                'warning',
                "Curl failed: "." url=".$url." => ".$error
            );
            return false;
        }

        if( $status['http_code'] == 200 && $result ) {
            $result = json_decode($result, true);
            //if( !$result ) {
            //    return false;
            //}
            $checksum = $result['checksum'];
            $valid = $result['valid'];
            $transferResult = $result['transferResult'];

            //dump($result);
            //echo 'hash='.$hash.'<br>';
            //exit('222');

            if ($checksum === $hash && $valid === true && $transferResult) {
                //echo "Successefully sent: " . $jsonFile['className'] . " <br>";
                return $result;
            }
        }

        //exit('get SlaveToMasterTransferCurl false');
        return false;
    }

    //Run on external (slave)
    //$confirmationJsonFile includes only successfully transferred objects
    public function receiveConfirmationOnSlave( $confirmationJsonFile ) {
//        $confirmationResponse[] = array(
//            'className' => $className,
//            'localId' => $localId,
//            'oid' => $oid,
//            'sourceId' => $sourceId,
//            'globalId' => $globalId,
//            'instanceId' => $instanceId,
//        );

        //dump($confirmationJsonFile);
        //exit('123');

        $userSecUtil = $this->container->get('user_security_utility');

        $res = array();
        foreach($confirmationJsonFile as $singleConfirmationJsonFile) {
            $className = $singleConfirmationJsonFile['className'];
            $localId = $singleConfirmationJsonFile['localId'];
            $globalId = $singleConfirmationJsonFile['globalId'];
            $instanceId = $singleConfirmationJsonFile['instanceId'];

            if( $instanceId != 'WCMINT' ) {
                continue;
            }

            $transferableEntity = $this->em->getRepository($className)->find($localId);
            if ($transferableEntity) {
                if ($globalId) {

                    //set TransferData status to "Completed"
                    $transferData = $this->findTransferDataByLocalId($localId, $className);
                    $status = $this->em->getRepository(TransferStatusList::class)->findOneByName("Completed");
                    $transferData->setTransferStatus($status);

                    if (!$transferableEntity->getGlobalId()) {
                        $transferableEntity->setGlobalId($globalId);
                    }

                    $this->em->flush(); //disable for testing

                    $transferMsg = "Transfer confirmed on the external server: globalId=$globalId ($className)";
                    $res[] = $transferMsg;

                    //Event Log
                    $eventType = "Project Transferred";
                    //$userSecUtil->createUserEditEvent($this->container->getParameter('employees.sitename'), $resStr, null, null, null, $eventType);
                    $userSecUtil->createUserEditEvent(
                        $this->container->getParameter('translationalresearch.sitename'),
                        $transferMsg,
                        null,
                        $transferableEntity,
                        null,
                        $eventType
                    );
                }
            }
        }

        return $res;
    }



}