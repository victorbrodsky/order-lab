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
    public function deleteFileAction(Request $request) {
        return $this->deleteFileMethod($request);
    }

    public function deleteFileMethod(Request $request) {
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
                $dql->innerJoin("comment.documents", "documents");
                $dql->where("documents = :document");
                $query = $em->createQuery($dql)->setParameter("document",$document);
                $comments = $query->getResult();

                //echo "comment count=".count($comments)." ";
                if( count($comments) > 1 ) {
                    throw new \Exception( 'More than one comment found, count='.count($comments) );
                }

                if( count($comments) > 0 ) {
                    $comment = $comments[0];
                    if( $comment->getId() == $commentid ) {
                        $comment->removeDocument($document);
                        $em->persist($comment);
                        $count++;
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


    /**
     * @Route("/file-download/{id}", name="employees_file_download", requirements={"id" = "\d+"})
     * @Method("GET")
     */
    public function downloadFileAction($id) {
        return $this->downloadFileMethod($id);
    }

    public function downloadFileMethod($id) {
        $em = $this->getDoctrine()->getManager();
        $document = $em->getRepository('OlegUserdirectoryBundle:Document')->find($id);

        $response = new Response();

        if( $document ) {

            $originalname = $document->getOriginalname();
            $abspath = $document->getAbsoluteUploadFullPath();
            $size = $document->getSize();

            $response->headers->set('Content-Type', 'application/unknown');
            $response->headers->set('Content-Description', 'File Transfer');
            $response->headers->set('Content-Disposition', 'attachment; filename="'.$originalname.'"');
            $response->headers->set('Content-Length', $size);
            $response->headers->set('Content-Transfer-Encoding', 'binary');
            $response->setContent(file_get_contents($abspath));

        } else {
            $response->setContent('error');
        }

        return $response;
    }




} 