<?php
/**
 * Created by PhpStorm.
 * User: oli2002
 * Date: 10/15/14
 * Time: 11:57 AM
 */

namespace Oleg\UserdirectoryBundle\Controller;

use Oleg\FellAppBundle\Entity\FellowshipApplication;
use Oleg\UserdirectoryBundle\Entity\Examination;
use Oleg\UserdirectoryBundle\Entity\User;
use Oleg\UserdirectoryBundle\Util\LargeFileDownloader;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;


class UploadController extends Controller {


    /**
     * @Route("/file-delete", name="employees_file_delete")
     * @Method("DELETE")
     */
    public function deleteFileAction(Request $request) {
        return $this->deleteFileMethod($request);
    }

    public function deleteFileMethod(Request $request) {

        if( false == $this->get('security.context')->isGranted('IS_AUTHENTICATED_FULLY') ){
            return $this->redirect( $this->generateUrl('employees-nopermission') );
        }

        $documentid = $request->get('documentid');
        $commentid = $request->get('commentid');
        $commentclass = $request->get('commenttype');    //comment class
        //echo "documentid=".$documentid."<br>";
        //echo "commentid=".$commentid."<br>";
        //echo "commentclass=".$commentclass."<br>";

        //exit('my uploader');

        //find document with id
        $em = $this->getDoctrine()->getManager();
        $document = $em->getRepository('OlegUserdirectoryBundle:Document')->find($documentid);
        //echo "document=".$document." => ";

        $count = 0;

        if( $document ) {

            //document absolute path
            $documentPath = $document->getServerPath();
            //echo "documentPath=".$documentPath."<br>";

            //find object where document is belongs
            //$comment = $this->getDoctrine()->getRepository('OlegUserdirectoryBundle:'.$commentclass)->findOneBy(array('id'=>$commentid,'documents'=>$document));

            if( $commentid && $commentid != 'undefined' && $commentclass && $commentclass != 'undefined' ) {

                $repository = $this->getDoctrine()->getRepository($commentclass);
                $dql = $repository->createQueryBuilder("comment");
                $dql->select('comment');
                //$dql->innerJoin("comment.documents", "documents");
                $this->setHolderDocumentsDql($dql,$commentclass);
                $dql->where("documents = :document");
                $query = $em->createQuery($dql)->setParameter("document",$document);
                $comments = $query->getResult();

                //echo "comment count=".count($comments)." ";
                if( count($comments) > 1 ) {
                    throw new \Exception( 'More than one holder found, count='.count($comments) );
                }

                if( count($comments) > 0 ) {
                    $comment = $comments[0];
                    if( $comment->getId() == $commentid ) {
                        $comment->removeDocument($document);
                        $em->persist($comment);
                        $count++;

//                        //update report for fellapp
//                        $logger = $this->container->get('logger');
//                        $logger->notice("delete document from comment=".$comment);
//                        if( $comment instanceof FellowshipApplication ) {
//                            //update report
//                            $fellappUtil = $this->container->get('fellapp_util');
//                            $fellappUtil->addFellAppReportToQueue( $comment->getId() );
//                        }
                    }
                }

            } //if commentid && $commentclass

            $count++;
            $em->remove($document);
            $em->flush();

            //remove file from folder
            if( is_file($documentPath) ) {
                unlink($documentPath);
            }

        }

        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');
        $response->setContent($count);
        return $response;
    }

    public function setHolderDocumentsDql($dql,$commentclass) {

        switch( $commentclass ) {
            case "OlegFellAppBundle:FellowshipApplication":
                $str = "comment.coverLetters";
                break;
            case "OlegUserdirectoryBundle:Examination":
                $str = "comment.scores";
                break;
            default:
                $str = "comment.documents";
        }

        //echo "dql str=".$str."<br>";

        $dql->innerJoin($str, "documents");
    }







    /**
     * @Route("/file-download/{id}", name="employees_file_download", requirements={"id" = "\d+"})
     * @Method("GET")
     */
    public function downloadFileAction(Request $request, $id) {
        return $this->downloadFileMethod($request,$id);
    }

    public function downloadFileMethod($request,$id) {

        if( false == $this->get('security.context')->isGranted('IS_AUTHENTICATED_FULLY') ){
            return $this->redirect( $this->generateUrl('employees-nopermission') );
        }

        $em = $this->getDoctrine()->getManager();
        $document = $em->getRepository('OlegUserdirectoryBundle:Document')->find($id);

        $response = new Response();

        if( $document ) {

            $originalname = $document->getOriginalname();
            $abspath = $document->getAbsoluteUploadFullPath();
            $size = $document->getSize();

            $downloader = new LargeFileDownloader();
            $downloader->downloadLargeFile($abspath,$originalname,$size);

            exit;
//            $referer = $request->headers->get('referer');
//            echo "referer=".$referer."<br>";
//            if( strpos($referer, '/login') ) {
//                return $this->redirect( $this->generateUrl('main_common_home') );
//            } else {
//                exit;
//            }

            if(0) {
                $response->headers->set('Content-Type', 'application/unknown');
                $response->headers->set('Content-Description', 'File Transfer');
                $response->headers->set('Content-Disposition', 'attachment; filename="'.$originalname.'"');
                $response->headers->set('Content-Length', $size);
                $response->headers->set('Content-Transfer-Encoding', 'binary');
                $response->setContent(file_get_contents($abspath));
            }

        } else {
            $response->setContent('error');
        }

        return $response;
    }



    /**
     * @Route("/file-view/{id}", name="employees_file_view", requirements={"id" = "\d+"})
     * @Method("GET")
     */
    public function viewFileAction(Request $request, $id) {
        return $this->viewFileMethod($request,$id);
    }

    public function viewFileMethod($request,$id) {

        if( false == $this->get('security.context')->isGranted('IS_AUTHENTICATED_FULLY') ){
            return $this->redirect( $this->generateUrl('employees-nopermission') );
        }

        $em = $this->getDoctrine()->getManager();
        $document = $em->getRepository('OlegUserdirectoryBundle:Document')->find($id);

        $response = new Response();

        if( $document ) {

            $originalname = $document->getOriginalname();
            $abspath = $document->getAbsoluteUploadFullPath();
            $size = $document->getSize();

            //$downloader = new LargeFileDownloader();
            ////$filepath, $filename=null, $size=null, $retbytes=true, $action="download"
            //$downloader->downloadLargeFile($abspath,$originalname,$size,true,"view");

            header('Content-Type: application/pdf');
            header('Content-Disposition: inline; filename='.$originalname);
            readfile($abspath);

            exit;
        } else {
            $response->setContent('error');
        }

        return $response;
    }


} 