<?php
/**
 * Created by PhpStorm.
 * User: oli2002
 * Date: 10/16/14
 * Time: 9:55 AM
 */

namespace Oleg\UserdirectoryBundle\Services;


use Doctrine\ORM\EntityManager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Security\Core\SecurityContext;

use Oneup\UploaderBundle\Event\PostPersistEvent;
use Oneup\UploaderBundle\Event\PreUploadEvent;

use Oleg\UserdirectoryBundle\Entity\Document;



class UploadListener {

    private $container;
    private $em;
    private $sc;

    public function __construct(ContainerInterface $container, EntityManager $em, SecurityContext $sc)
    {
        $this->container = $container;
        $this->em = $em;
        $this->sc = $sc;
    }

    public function onUpload(PostPersistEvent $event)
    {

        $request = $event->getRequest();
        $userid = $request->get('userid');
        $originalfilename = $request->get('filename');

        //$holdername = $request->get('holdername');
        //$holderid = $request->get('holderid');
        //$docfieldname = $request->get('docfieldname');
        //echo "userid=".$userid."<br>";

        $file = $event->getFile();


        $path = $file->getPath();
        //echo "path=".$path."<br>";
        $uniquefilename = $file->getFilename();
        //echo "filename=".$filename."<br>";
        $size = $file->getSize();

        //creator: subjectUser
        $user = $this->em->getRepository('OlegUserdirectoryBundle:User')->find($userid);

        $object = new Document($user);
        $object->setOriginalname($originalfilename);
        $object->setUniquename($uniquefilename);
        $object->setUploadDirectory($path);
        $object->setSize($size);

        //$this->processHolder($object,$holdername,$holderid,$docfieldname);

        $this->em->persist($object);
        $this->em->flush();


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

} 