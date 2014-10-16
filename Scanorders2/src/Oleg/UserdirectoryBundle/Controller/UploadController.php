<?php
/**
 * Created by PhpStorm.
 * User: oli2002
 * Date: 10/15/14
 * Time: 11:57 AM
 */

namespace Oleg\UserdirectoryBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;


class UploadController extends Controller {


    /**
     * @Route("/file-delete", name="employees_file_delete")
     * @Method("POST")
     */
    public function uploadFileAction(Request $request) {

        $documentid = $request->get('documentid');
        $commentid = $request->get('commentid');
        $commenttype = $request->get('commenttype');
        echo "documentid=".$documentid;

        //exit('my uploader');

        //find document with id
        $em = $this->getDoctrine()->getManager();
        $document = $em->getRepository('OlegUserdirectoryBundle:Document')->find($documentid);

        $count = 0;

        //find object where document is belongs
        $adminComment = $this->getDoctrine()->getRepository('OlegUserdirectoryBundle:AdminComment')->findOneBy(array('documents'=>$documentid));
        if( $adminComment ) {
            $adminComment->removeDocument($document);
            $em->persist($adminComment);
            $count++;
        }

        $publicComment = $this->getDoctrine()->getRepository('OlegUserdirectoryBundle:PublicComment')->findOneBy(array('documents'=>$documentid));
        if( $publicComment ) {
            $publicComment->removeDocument($document);
            $em->persist($publicComment);
            $count++;
        }

        $confComment = $this->getDoctrine()->getRepository('OlegUserdirectoryBundle:ConfidentialComment')->findOneBy(array('documents'=>$documentid));
        if( $confComment ) {
            $confComment->removeDocument($document);
            $em->persist($confComment);
            $count++;
        }

        $privateComment = $this->getDoctrine()->getRepository('OlegUserdirectoryBundle:PrivateComment')->findOneBy(array('documents'=>$documentid));
        if( $privateComment ) {
            $privateComment->removeDocument($document);
            $em->persist($privateComment);
            $count++;
        }

        $em->remove($document);
        $em->flush();

        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');
        $response->setContent($count);
        return $response;
    }




} 