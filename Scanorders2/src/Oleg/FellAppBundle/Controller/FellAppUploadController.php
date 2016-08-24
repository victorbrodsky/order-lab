<?php
/**
 * Created by PhpStorm.
 * User: oli2002
 * Date: 10/15/14
 * Time: 11:57 AM
 */

namespace Oleg\FellAppBundle\Controller;

use Oleg\UserdirectoryBundle\Util\LargeFileDownloader;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

use Oleg\UserdirectoryBundle\Controller\UploadController;
use Symfony\Component\HttpFoundation\Response;


class FellAppUploadController extends UploadController {

    /**
     * @Route("/file-delete", name="fellapp_file_delete")
     * @Method({"GET", "POST", "DELETE"})
     */
    public function deleteFileAction(Request $request) {
        if( false == $this->get('security.context')->isGranted('ROLE_FELLAPP_COORDINATOR') && false == $this->get('security.context')->isGranted('ROLE_FELLAPP_DIRECTOR') ){
            //exit('no fellapp permission');
            return $this->redirect( $this->generateUrl('fellapp-nopermission') );
        }

        return $this->deleteFileMethod($request);
    }

    /**
     * $id - document id
     *
     * @Route("/file-download/{id}/{eventtype}", name="fellapp_file_download", requirements={"id" = "\d+"})
     * @Method("GET")
     */
    public function downloadFileAction(Request $request,$id,$eventtype=null) {

        if( false == $this->get('security.context')->isGranted('ROLE_FELLAPP_USER') ){
            return $this->redirect( $this->generateUrl('fellapp-nopermission') );
        }

        return $this->downloadFileMethod($request,$id,$this->container->getParameter('fellapp.sitename'),$eventtype);
    }


    /**
     * $id - document id
     *
     * @Route("/file-view/{id}/{viewType}/{eventtype}", name="fellapp_file_view", requirements={"id" = "\d+"})
     * @Method("GET")
     */
    public function viewFileAction(Request $request,$id,$eventtype=null, $viewType=null) {

        if( false == $this->get('security.context')->isGranted('ROLE_FELLAPP_USER') ){
            return $this->redirect( $this->generateUrl('fellapp-nopermission') );
        }

        return $this->viewFileMethod($request,$id,$this->container->getParameter('fellapp.sitename'),$eventtype,$viewType);
    }


} 