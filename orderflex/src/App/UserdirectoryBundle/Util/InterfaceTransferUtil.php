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
use App\UserdirectoryBundle\Entity\Document;
use App\UserdirectoryBundle\Entity\InterfaceTransferList;
use App\UserdirectoryBundle\Entity\TransferData;
use App\UserdirectoryBundle\Entity\TransferStatusList;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Bundle\SecurityBundle\Security;


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
    public function transferFile( InterfaceTransferList $transfer ) {

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
            $resStr = "Transfer completed: " . implode("; ",$resArr);
        } else {
            $resStr = "Transfer not completed: nothing to transfer.";
        }

        return $resStr;
        //exit('EOF sendTransfer');
    }

    public function sendSingleTransfer( TransferData $transferData ) {

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
        $entityId = $transferData->getEntityId();
        if( $className && $entityId ) {
            $transferableEntity = $this->em->getRepository($className)->find($entityId);
        }

        //Create json with antibody data
        $jsonFile= $this->createJsonFile($transferableEntity, $className);

        //dump($jsonFile);
        //exit('111');

        //Step 2: send files with sftp
        //if( $res === true ) {
        //send associated files (i.e. documents) transferFile
        $resFiles = $this->sendAssociatedFiles($interfaceTransfer,$jsonFile);

        //add files path to $jsonFile
        $jsonFile['files'] = $resFiles;
        //}

        //Step 1: send data with curl
        $res = $this->sendDataCurl($interfaceTransfer,$jsonFile);

        $msg = "";
        $status = NULL;
        if( $res === true ) {
            //set status to 'Completed'
            $status = $this->em->getRepository(TransferStatusList::class)->findOneByName('Completed');
            $msg = "Entity ".$className." with ID ". $entityId ." has been successfully transfered to the remote server ".$strServer;
        } else {
            //Failed
            $status = $this->em->getRepository(TransferStatusList::class)->findOneByName('Failed');
            $msg = "Entity ".$className." with ID ". $entityId ." failed to transfer to the remote server ".$strServer;
        }

        if( $status ) {
            $transferData->setTransferStatus($status);
            //$this->em->flush(); //testing

            //TODO: Add to EventLog
        }

        return $msg;
    }

    public function sendAssociatedFiles( InterfaceTransferList $transfer, $jsonFile ) {
        dump($jsonFile);
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

        $resArr = array();
        foreach( $jsonFile['documents'] as $document ) {
            $path = $document['path'];
            $label = $document['label'];
            $uniqueId = $jsonFile['id']."-".$document['id']; //$jsonFile['id'] - transferable entity ID
            $sentFile = $this->sendSingleFile($dstConnection,$path,$uniqueId);
            if( $sentFile ) {
                $resArr[] = array(
                    'uniqueid' => $uniqueId,
                    'filepath' => $sentFile,
                    'label' => $label
                );
            }
        }

        return $resArr;
        exit("EOF sendAssociatedFiles");
    }

    //Require ssh
    //http://pecl.php.net/package/ssh2
    public function sendSingleFile( $dstConnection, $filePath, $uniqueId ) {

        //$filePath: http://127.0.0.1:8000\Uploaded/directory/documents\65663c5c4180e.jpg
        $dstFile = basename($filePath);
        echo "dstFile=$dstFile <br>";

        //$dstFile = "dst_file.csv";
        //$srcFile = "src_file.csv";

        $dstPath = "/usr/local/bin/order-lab-homepagemanager/orderflex";
        $dstFullPath = $dstPath . $this->getPath("/") . $uniqueId . "/";
        $dstFilePath = $dstFullPath . $dstFile;

        //$dstTestFilePath = $dstPath . $this->getPath("/") . "test_file.txt";
        //$dstTestFilePath = $dstPath . "/" . "test_file.txt";

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
                ssh2_sftp_mkdir($dstSFTP, $dstFullPath, 0700, true);
            }

            $dstFile = fopen("ssh2.sftp://{$dstSFTP}/".$dstFilePath, 'w');

            if ( !$dstFile ) {
                throw new \Exception('File open failed. file=' . $srcFilePath);
            }

            //$dstTestFile = fopen("ssh2.sftp://{$dstSFTP}/".$dstTestFilePath, 'r');
            //$contents = stream_get_contents($dstTestFile);
            //dump($contents);

            $srcFile = fopen($srcFilePath, 'r');

            //$writtenBytes = stream_copy_to_stream($srcFile, $dstFile);
            //echo "writtenBytes=$writtenBytes <br>";
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
        exit("EOF sendSingleFile");
    }

    public function sendDataCurl( InterfaceTransferList $interfaceTransfer, $jsonFile ) {

        dump($jsonFile);
        exit('111');

        //Send data with curl and secret key
        //$secretKey = $interfaceTransfer->getSshPassword(); //use SshPassword for now
        $secretKey = $_ENV['APP_SECRET']; //get .env parameter

        //Add hash and security key
        $hash = hash('sha512', $secretKey . serialize($jsonFile));
        $jsonFile['hash'] = $hash;

        $data_string = json_encode($jsonFile);
        $strServer = $interfaceTransfer->getTransferDestination();  //"159.203.95.150";
        $url = 'http://'.$strServer.'/directory/receive-transfer';
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
        //$status = curl_getinfo($ch);
        curl_close($ch);

        //dump($status);
        //dump($result);
        //exit('222');

        if( $result ) {
            $result = json_decode($result, true);
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
    public function findTransferData( $entity, $statusStr ) {

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

        $transfer = NULL;

        if( count($transfers) > 0 ) {
            //Can we have the same multiple transfers?
            $transfer = $transfers[0];
        }

        if( count($transfers) == 1 ) {
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

        $transfer->setEntityId($entity->getId());

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

    public function getInterfaceTransferByName( $name ) {
        $interfaceTransfer = $this->em->getRepository(InterfaceTransferList::class)->findOneByName($name);
        return $interfaceTransfer;
    }

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

    public function receiveTransfer($receiveData) {
        $logger = $this->container->get('logger');

        $className = $receiveData['className'];
        //$entityName = $this->getEntityName($className);

        //Case: AntibodyList
        if( str_contains($className, 'TranslationalResearchBundle') && str_contains($className, 'AntibodyList') ) {
            $logger->notice('AntibodyList: className='.$className);
            $entityId = $receiveData['id'];
            
            if( $className && $entityId ) {
                $logger->notice('AntibodyList: entityId='.$entityId);
                //find unique existing antibody by name and description and comment
                //$transferableEntity = $this->em->getRepository($className)->findOneByName($name);
                
                $name = $receiveData['name'];
                $description = $receiveData['description'];
                $comment = $receiveData['comment'];
                
                $matchingArr = array(
                    'name' => $name,
                    //'description' => $description,
                    //'comment' => $comment
                );
                $transferableEntity = $this->findExistingTransferableEntity($className,$matchingArr);
                if( $transferableEntity ) {
                    $update = $transferableEntity->updateByJson($receiveData, $this->em, $className);
                    if( $update ) {
                        $transferableEntity->setOpenToPublic(true);
                        $transferableEntity->setType('user-added');
                        $this->em->flush();
                    }
                } else {
                    //create new entity
                    $logger->notice('receiveTransfer: create new AntibodyList');
                    $transferableEntity = new $className();
                    $update = $transferableEntity->updateByJson($receiveData, $this->em, $className);
                    if( $update ) {
                        $transferableEntity->setOpenToPublic(true);
                        $transferableEntity->setType('user-added');
                        $this->em->persist($transferableEntity);
                        $this->em->flush();
                        $logger->notice('receiveTransfer: after creation new AntibodyList flush: id='.$transferableEntity->getId());
                    }
                }

                //Attach documents
                //1) remove all existing documents and attach new
                $transferableEntity->clearDocuments();
                $documentDatas = $receiveData['files'];
                foreach($documentDatas as $documentArr) {
//                    'uniqueid' => $uniqueId,
//                    'filepath' => $sentFile,
//                    'label' => $label
                    $document = $this->createAssociatedDocument($documentArr);
                    $logger->notice('receiveTransfer: document id='.$document->getId());
                    $transferableEntity->addDocument($document);
                    $this->em->flush();
                    $logger->notice('receiveTransfer: after flush: document id='.$document->getId());
                }


            }
        }

        return true;
    }

    public function createAssociatedDocument($documentArr) {
        $logger = $this->container->get('logger');
        $uniqueId = $documentArr['uniqueid'];
        $filepath = $documentArr['filepath'];
        $label = $documentArr['label'];
        $author = null;
        $logger->notice("createAssociatedDocument: uniqueId=$uniqueId, filepath=$filepath");

        $filesize = null;
        $filepath = realpath($filepath);
        if( $filepath ) {
            if( file_exists($filepath) ) {
                $filesize = $filepath->getFileSize();
                if (!$filesize) {
                    $filesize = filesize($filepath);
                }
            }
        }
        $logger->notice("createAssociatedDocument: filepath=$filepath, filesize=$filesize");

        $object = new Document($author);
        $object->setName($label);
        $object->setCleanOriginalname($label);
        //$object->setTitle($uniqueTitle);
        $object->setUniqueid($uniqueId);
        //$object->setUniquename($fileUniqueName);
        //$object->setUploadDirectory($path);
        $object->setSize($filesize);

//        $documentType = 'Antibody Image'
//        $transformer = new GenericTreeTransformer($this->em, $author, "DocumentTypeList", "UserdirectoryBundle");
//        $documentType = trim((string)$documentType);
//        $documentTypeObject = $transformer->reverseTransform($documentType);
//        if( $documentTypeObject ) {
//            $object->setType($documentTypeObject);
//        }

        $this->em->persist($object);

        return $object;
    }

    ////Add Original ID (oid) to match the unique transferable entity between source and destination servers?
    public function findExistingTransferableEntity( $className, $matchingArr ) {
        //$transferableEntity = $this->em->getRepository($className)->findOneByName($name);

        $repository = $this->em->getRepository($className);
        $dql =  $repository->createQueryBuilder("entity");
        $dql->select('entity');

        $parameters = array();

        foreach($matchingArr as $key=>$value) {
            $dql->andWhere('entity.'.$key.' = :entity'.$key);
            $parameters['entity.'.$key] = $value;
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
    
}