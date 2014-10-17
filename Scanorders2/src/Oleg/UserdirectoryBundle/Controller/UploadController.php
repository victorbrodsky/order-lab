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

            //find object where document is belongs
            //$comment = $this->getDoctrine()->getRepository('OlegUserdirectoryBundle:'.$commentclass)->findOneBy(array('id'=>$commentid,'documents'=>$document));

            $repository = $this->getDoctrine()->getRepository('OlegUserdirectoryBundle:'.$commentclass);
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
                $comment->removeDocument($document);
                $em->persist($comment);
                $count++;
            }

            $count++;
            $em->remove($document);
            $em->flush();

        }

        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');
        $response->setContent($count);
        return $response;
    }




} 