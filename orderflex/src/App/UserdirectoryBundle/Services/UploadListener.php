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
 * Created by PhpStorm.
 * User: oli2002
 * Date: 10/16/14
 * Time: 9:55 AM
 */

namespace App\UserdirectoryBundle\Services;


//use Doctrine\ORM\EntityManager;
use App\UserdirectoryBundle\Entity\User;
use App\UserdirectoryBundle\Form\DataTransformer\GenericTreeTransformer;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

use Oneup\UploaderBundle\Event\PostPersistEvent;
use Oneup\UploaderBundle\Event\PreUploadEvent;

use App\UserdirectoryBundle\Entity\Document;
use Symfony\Bundle\SecurityBundle\Security;


class UploadListener {

    private $container;
    private $em;
    protected $security;

    public function __construct(ContainerInterface $container, EntityManagerInterface $em, Security $security)
    {
        $this->container = $container;
        $this->em = $em;
        $this->security = $security;
    }

    public function onUpload(PostPersistEvent $event)
    {
        $request = $event->getRequest();
        $logger = $this->container->get('logger');
        $userid = $request->get('userid');
        $originalfilename = $request->get('filename');
        $documentType = $request->get('documenttype');
        $sitename = $request->get('sitename');
        $authUserId = $request->get('authuserid');

        //$holdername = $request->get('holdername');
        //$holderid = $request->get('holderid');
        //$docfieldname = $request->get('docfieldname');

        //echo "userid=".$userid."<br>";
        //echo "originalfilename=".$originalfilename."<br>";
        //echo "documentType=".$documentType."<br>";

        $file = $event->getFile();

        $path = $file->getPath();
        //echo "path=".$path."<br>";
        $uniquefilename = $file->getFilename();
        //echo "uniquefilename=".$uniquefilename."<br>";
        $size = $file->getSize();

        //creator: subjectUser
        //echo "userid=".$userid."<br>";
        $user = null;
        if( $userid ) {
            $user = $this->em->getRepository(User::class)->find($userid);
        } else {
            //for new object (i.e. application) userid might not be set. Therefore, use the logged in user.
            $user = $this->security->getUser();
        }
        //exit('onUpload: user='.$user.", authUserId=".$authUserId);
        $authUser = null;
        if( $authUserId ) {
            $authUser = $this->em->getRepository(User::class)->find($authUserId);
        }
        //echo "user=".$user.", authUser=".$authUser."<br>";
        $logger->notice("onUpload: user=".$user.", authUser=".$authUser);
        //exit('onUpload');

        $object = new Document($user);
        $object->setCleanOriginalname($originalfilename);
        $object->setUniquename($uniquefilename);
        $object->setUploadDirectory($path);
        $object->setSize($size);

        if( $documentType ) {
            //$documentTypeObject = $this->em->getRepository('AppUserdirectoryBundle:DocumentTypeList')->findOneByName($documentType);
            $transformer = new GenericTreeTransformer($this->em, $authUser, "DocumentTypeList", "UserdirectoryBundle");
            $documentType = trim((string)$documentType);
            $documentTypeObject = $transformer->reverseTransform($documentType);
            if( $documentTypeObject ) {
                $object->setType($documentTypeObject);
            }
        }

        //generate two thumbnails
        $userServiceUtil = $this->container->get('user_service_utility');
        $userServiceUtil->generateTwoThumbnails($object);

        //$this->processHolder($object,$holdername,$holderid,$docfieldname);

        //exit('exit upload listener');
        $this->em->persist($object);
        $this->em->flush();

        //set event log for server upload
        $this->setUploadEventLog($request,$object,$authUser,$sitename,null);

        $response = $event->getResponse();
        $response['documentid'] = $object->getId();
        $response['documentsrc'] = $object->getRelativeUploadFullPath();

    }

    public function preUpload(PreUploadEvent $event)
    {
        $file = $event->getFile();

        $filename = $file->getFilename();
        //echo "preupload filename=".$filename."<br>";

        $filebasename = $file->getBasename();
        //echo "preupload filebasename=".$filebasename."<br>";
    }



//    public function processHolder($object,$holdername,$holderid,$docfieldname) {
//        if( $holdername && $holderid && $docfieldname ) {
//            $holder = $this->em->getRepository($holdername)->find($holderid);
//            $addMethod = "add".$docfieldname;
//            $holder->$addMethod($object);
//        }
//    }


    public function setUploadEventLog($request,$document,$user, $sitename=null,$eventtype=null) {

        //try to get document type
        if( !$eventtype ) {
            $documentType = $document->getType();
            if( $documentType ) {
                $eventtype = $documentType->getName() . " Uploaded";
            } else {
                $eventtype = "Document Downloaded";
            }
        }

        if( $eventtype && $sitename ) {
            $userSecUtil = $this->container->get('user_security_utility');
            $eventDescription = "Document has been uploaded to the server by " . $user;
            $userSecUtil->createUserEditEvent($sitename,$eventDescription,$user,$document,$request,$eventtype);
        }
    }

} 