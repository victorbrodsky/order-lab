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
use App\TranslationalResearchBundle\Entity\IrbApprovalTypeList;
use App\TranslationalResearchBundle\Entity\IrbStatusList;
use App\TranslationalResearchBundle\Entity\PriceTypeList;
use App\TranslationalResearchBundle\Entity\SpecialtyList;
use App\UserdirectoryBundle\Entity\Document;
use App\UserdirectoryBundle\Entity\InterfaceTransferList;
use App\UserdirectoryBundle\Entity\TransferData;
use App\UserdirectoryBundle\Entity\TransferStatusList;
use App\UserdirectoryBundle\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
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

    public function __construct( EntityManagerInterface $em, ContainerInterface $container, Security $security ) {
        $this->em = $em;
        $this->container = $container;
        $this->security = $security;
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
            //$this->em->flush(); //testing

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

        $transferData = $this->findTransferData($entity);

        if( $transferData ) {
            //set status to 'Ready'
            $statusReady = $this->em->getRepository(TransferStatusList::class)->findOneByName('Ready');
            $transferData->setTransferStatus($statusReady);
        } else {
            //Create TransferData
            $transferData = $this->createTransferData($entity,$status='Ready');
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

    //Add to InterfaceTransfer the way to distinguish if it should be added to the TransferData:
    // AntibodyList -> server should be master (master to slave)
    // Project -> server should be slave (slave to master)
    //TODO: add to InterfaceTransfer sync direction: local to remote, remote to local, both
    public function isMasterTransferServer( $entity ) {
        $mapper = $this->classListMapper($entity);
        //$className = $mapper['className'];
        $entityName = $mapper['entityName'];

        $interfaceTransfer = $this->getInterfaceTransferByName($entityName);
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

        $userSecUtil = $this->container->get('user_security_utility');
        $instanceId = $uploadPath = $userSecUtil->getSiteSettingParameter('instanceId');
        if( !$instanceId ) {
            $instanceId = 'NA';
        }

        //1) send CURL request to slave to transfer data and receive projects as $transferDatas
        $transferDatas = $this->getSlaveToMasterTransferCurl('App\TranslationalResearchBundle\Entity\Project');

        if( !$transferDatas ) {
            $resStr = "Get transfer not completed: response is null";
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
            echo "title=$title <br>";

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
            if( !$transferableEntity ) {
                //$transferableEntity = $this->em->getRepository($className)->findOneBySourceId($sourceId);
            }

//            if( !$transferableEntity ) {
//                $transferableEntity = $this->em->getRepository($className)->findOneByOid($oid);
//                $resStr = "Update existing Project found by OID $oid, title " . $title;
//            }
            echo "Final transferableEntity=".$transferableEntity."<br>";
            echo "Final resStr=$resStr <br>";

            //TODO: fix update Project, now only create New Project
            //An exception occurred while executing a query: SQLSTATE[23505]:
            // Unique violation: 7 ERROR: duplicate key value violates
            // unique constraint "pk__transres__3213e83f716f45b5"
            //DETAIL: Key (id)=(1) already exists.
            if( $transferableEntity ) {
                $resStr = "Get transfer not completed: Project ".$transferableEntity->getOid()." already exists.";
                //return $resStr;
                $resArr[] = $resStr;
                continue;
            }

            //dump($jsonObject);
            //exit('deserialize');
            $transferableEntity = $this->deserializeObject($jsonObject,$className,$serializer,$transferableEntity);
            if( $transferableEntity ) {
                echo "PrePersist: transferableEntity ID=".$transferableEntity->getId().", title=".$transferableEntity->getTitle()."<br>";

                //dump($transferableEntity);
                //exit('123');

                //TODO: error: spl_object_id(): Argument #1 ($object) must be of type object, array given
                $this->em->persist($transferableEntity);
                if ($testing === false) {
                    //set export ID or use TransferData to get it
                    //$transferableEntity->setExportId($localId);
                    //$description = $transferableEntity->getDescription();
                    //$transferableEntity->getDescription($description . "\n "." Transfered with Gloabl ID=".$globalId);

                    //$this->em->flush(); //disable for testing

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
                        //$this->em->flush(); //disable for testing
                    }

                    $confirmationResponse[] = array(
                        'className' => $className,
                        'localId' => $localId,      //id on the external (slave)
                        'sourceId' => $sourceId,    //source id on the external (slave)
                        'oid' => $oid,              //oid on the internal (master)
                        'globalId' => $globalId     //global id on the internal (master) for newly created, or updated objects
                    );

                    $resStr = "Create new Project with "
                        . "id=". $transferableEntity->getId()
                        . "; sourceId=" . $transferableEntity->getSourceId()
                        . "; oid=" . $transferableEntity->getOid()
                        . "; globalId=" . $transferableEntity->getGlobalId()
                        . ", title " . $transferableEntity->getTitle();
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

//            $context = [AbstractNormalizer::IGNORED_ATTRIBUTES => [
//                'submitter',
//                'createDate',
//                'updateUser',
//                'updateDate',
//                'projectSpecialty',
//                'irbExpirationDate',
//                'exemptIrbApproval',
//                'exemptIACUCApproval',
//                'irbStatusList',
//                'className',
//            ]];
//
//            $context = [
//                AbstractNormalizer::IGNORED_ATTRIBUTES => [
//                    'id',
//                    'oid',
//                    'sourceId',
//                    'globalId',
//                    'title',
//                    'irbNumber'
//                ],
//                AbstractNormalizer::OBJECT_TO_POPULATE => $objectToPopulate
//            ];

            if(1) {
                if ($objectToPopulate) {
                    echo "deserialize Object: Update project " . $objectToPopulate->getOid() . "<br>";
                    $transferableEntity = $serializer->denormalize(
                        $jsonObject,
                        $className,
                        $serilizeFormat,
                        [
                            AbstractNormalizer::IGNORED_ATTRIBUTES => [
                                'submitter',
                                'createDate',
                                'updateUser',
                                'updateDate',
                                'projectSpecialty',
                                'irbExpirationDate',
                                'exemptIrbApproval',
                                'exemptIACUCApproval',
                                'irbStatusList',
                                'collDivs',
                                'collLabs',
                                'priceList'
                            ],
                            AbstractNormalizer::OBJECT_TO_POPULATE => $objectToPopulate
                        ]
                    );
                } else {
                    echo "deserialize Object: Create new project <br>";
                    $transferableEntity = $serializer->denormalize(
                        $jsonObject,
                        $className,
                        $serilizeFormat,
                        [
                            AbstractNormalizer::IGNORED_ATTRIBUTES => [
                                'submitter',
                                'createDate',
                                'updateUser',
                                'updateDate',
                                'projectSpecialty',
                                'irbExpirationDate',
                                'exemptIrbApproval',
                                'exemptIACUCApproval',
                                'irbStatusList',
                                'collDivs',
                                'collLabs',
                                'priceList'
                            ]
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

            //createDate
            $this->convertDate($jsonObject,$transferableEntity,'createDate');

            //updateDate
            $this->convertDate($jsonObject,$transferableEntity,'updateDate');

            //irbExpirationDate
            $this->convertDate($jsonObject,$transferableEntity,'irbExpirationDate');

            //projectSpecialty
            if( isset($jsonObject['projectSpecialty']) ) {
                $projectSpecialtyName = $jsonObject['projectSpecialty']['name'];
                echo "projectSpecialtyName=" . $projectSpecialtyName . "<br>";
                //Find one by name SpecialtyList
                $projectSpecialtyEntity = $this->em->getRepository(SpecialtyList::class)->findOneByName($projectSpecialtyName);
                $transferableEntity->setProjectSpecialty($projectSpecialtyEntity);
            }

            //exemptIrbApproval
            if( isset($jsonObject['exemptIrbApproval']) ) {
                $exemptIrbApprovalName = $jsonObject['exemptIrbApproval']['name'];
                echo "exemptIrbApprovalName=" . $exemptIrbApprovalName . "<br>";
                //Find one by name IrbApprovalTypeList
                $exemptIrbApprovalEntity = $this->em->getRepository(IrbApprovalTypeList::class)->findOneByName($exemptIrbApprovalName);
                $transferableEntity->setExemptIrbApproval($exemptIrbApprovalEntity);
            }

            //exemptIACUCApproval
            if( isset($jsonObject['exemptIACUCApproval']) ) {
                $exemptIACUCApprovalName = $jsonObject['exemptIACUCApproval']['name'];
                echo "exemptIACUCApprovalName=" . $exemptIACUCApprovalName . "<br>";
                //Find one by name IrbApprovalTypeList, the same as exemptIrbApproval
                $exemptIACUCApprovalEntity = $this->em->getRepository(IrbApprovalTypeList::class)->findOneByName($exemptIACUCApprovalName);
                $transferableEntity->setExemptIACUCApproval($exemptIACUCApprovalEntity);
            }

            //irbStatusList
            if( isset($jsonObject['irbStatusList']) ) {
                $irbStatusListName = $jsonObject['irbStatusList']['name'];
                echo "irbStatusList=" . $irbStatusListName . "<br>";
                $irbStatusListEntity = $this->em->getRepository(IrbStatusList::class)->findOneByName($irbStatusListName);
                $transferableEntity->setIrbStatusList($irbStatusListEntity);
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

            //priceList PriceTypeList
            if( isset($jsonObject['priceList']) ) {
                $priceListName = $jsonObject['priceList']['name'];
                echo "priceListName=" . $priceListName . "<br>";
                $priceListEntity = $this->em->getRepository(PriceTypeList::class)->findOneByName($priceListName);
                $transferableEntity->setPriceList($priceListEntity);
            }

            echo "deserialize Object: ".$className.": transferableEntity ID=".$transferableEntity->getId()."<br>";

            //$submitter = $transferableEntity->getSubmitter();
            //echo "submitter=".print_r($submitter)."<br>";

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

        echo "convertUser: $username, $email"."<br>";
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
        }

        if( $user ) {
            $setter = 'set'.$fieldName;
            $transferableEntity->$setter($user);

            echo "User added ".$user."<br>";
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
        echo $fieldName.": dateTimestamp=".$dateTimestamp."<br>";
        $timezone = $jsonObject[$fieldName]['timezone']['name'];
        $date = new \DateTime('now', new \DateTimeZone($timezone));
        $date->setTimestamp($dateTimestamp);
        $setterMethod = 'set'.$fieldName;
        $getterMethod = 'get'.$fieldName;
        $transferableEntity->$setterMethod($date);
        echo $fieldName.": date=".$transferableEntity->$getterMethod()->format('m/d/Y')."<br>";
        return $transferableEntity;
    }


    //Run on internal (master) server
    //send request to remote server to send all transferable in the response
    public function getSlaveToMasterTransferCurl($className) {
        $userSecUtil = $this->container->get('user_security_utility');
        $secretKey = $userSecUtil->getSiteSettingParameter('secretKey');

        $entityName = $this->getEntityName($className);
        $interfaceTransfer = $this->getInterfaceTransferByName($entityName);

        //Add hash and security key
        $jsonFile = array();
        $jsonFile['className'] = $className;

        $hash = hash('sha512', $secretKey . serialize($jsonFile));
        $jsonFile['hash'] = $hash;

        $data_string = json_encode($jsonFile);
        $strServer = $interfaceTransfer->getTransferSource();  //view.online

        //http://view.online/directory/transfer-interface/slave-to-master-transfer
        $url = 'https://'.$strServer.'/directory/transfer-interface/slave-to-master-transfer';

        echo "url=$url <br>";
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

            curl_setopt($ch,CURLOPT_SSL_VERIFYPEER, false); //Danger TODO: use CURLOPT_CAINFO
            //https://stackoverflow.com/questions/4372710/php-curl-https
            //curl_setopt($ch,CURLOPT_SSL_VERIFYPEER, false); is a quick fix
            //The proper way is: curl_setopt($ch, CURLOPT_CAINFO, $_SERVER['DOCUMENT_ROOT'] .  "/../cacert-2017-09-20.pem");
            //Fix: https://unitstep.net/blog/2009/05/05/using-curl-in-php-to-access-https-ssltls-protected-sites/
        }

        $result = curl_exec($ch);
        //$status = curl_getinfo($ch);
        curl_close($ch);

        //dump($status);
        //dump($result);
        //exit('111');

        if( $result ) {
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
                echo "Successefully sent: " . $jsonFile['className'] . " <br>";
                return $result;
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
                'state',
                'title',
                'projectSpecialty' => ['name'],
                'exemptIrbApproval' => ['name'],
                'irbNumber',
                'irbExpirationDate',
                'irbStatusList' => ['name'],
                'exemptIACUCApproval' => ['name'],
                'iacucNumber',
                'iacucExpirationDate',
                'projectType' => ['name'],
                'description',
                'collDivs' => ['name'],
                'collLabs' => ['name'],
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
                //'involveHumanTissue',
                //'requireTissueProcessing',
                //'requireArchivalProcessing'
                'tissueFormComment'
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

        $className = $confirmationResponse[0]['className'];
        $entityName = $this->getEntityName($className);
        echo "send ConfirmationToSourceServer: className=$className, $entityName <br>";

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

        //http://view.online/directory/transfer-interface/slave-to-master-transfer
        //Send back to slave (external) global ID of newly generated Project
        $url = 'https://'.$strServer.'/directory/transfer-interface/confirmation-master-to-slave';

        echo "url=$url <br>";
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

            curl_setopt($ch,CURLOPT_SSL_VERIFYPEER, false); //Danger TODO: use CURLOPT_CAINFO
            //https://stackoverflow.com/questions/4372710/php-curl-https
            //curl_setopt($ch,CURLOPT_SSL_VERIFYPEER, false); is a quick fix
            //The proper way is: curl_setopt($ch, CURLOPT_CAINFO, $_SERVER['DOCUMENT_ROOT'] .  "/../cacert-2017-09-20.pem");
            //Fix: https://unitstep.net/blog/2009/05/05/using-curl-in-php-to-access-https-ssltls-protected-sites/
        }

        $result = curl_exec($ch);
        //$status = curl_getinfo($ch);
        curl_close($ch);

        //dump($status);
        dump($result);
        exit('111');

        if( $result ) {
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
                echo "Successefully sent: " . $jsonFile['className'] . " <br>";
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
//            'globalId' => $globalId
//        );

        $res = array();
        foreach($confirmationJsonFile as $singleConfirmationJsonFile) {
            $className = $singleConfirmationJsonFile['className'];
            $localId = $singleConfirmationJsonFile['localId'];
            $globalId = $singleConfirmationJsonFile['globalId'];

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

                    $this->em->flush();
                    $res[] = "Confirmed transfer in external: globalId=$globalId ($className)";
                }
            }
        }

        return $res;
    }

}