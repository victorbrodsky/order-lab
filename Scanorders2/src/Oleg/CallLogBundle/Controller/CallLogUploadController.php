<?php
/**
 * Created by PhpStorm.
 * User: oli2002
 * Date: 10/15/14
 * Time: 11:57 AM
 */

namespace Oleg\CallLogBundle\Controller;

use Oleg\UserdirectoryBundle\Util\LargeFileDownloader;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

use Oleg\UserdirectoryBundle\Controller\UploadController;
use Symfony\Component\HttpFoundation\Response;


class CallLogUploadController extends UploadController {

    /**
     * @Route("/file-delete", name="calllog_file_delete")
     * @Method("DELETE")
     */
    public function deleteFileAction(Request $request) {
        return $this->deleteFileMethod($request);
    }

    /**
     * $id - document id
     *
     * @Route("/file-download/{id}/{eventtype}", name="calllog_file_download", requirements={"id" = "\d+"})
     * @Method("GET")
     */
    public function downloadFileAction(Request $request,$id,$eventtype=null) {
        return $this->downloadFileMethod($request,$id,$this->container->getParameter('calllog.sitename'),$eventtype);
    }


    /**
     * $id - document id
     *
     * @Route("/file-view/{id}/{viewType}/{eventtype}", name="calllog_file_view", requirements={"id" = "\d+"})
     * @Method("GET")
     */
    public function viewFileAction(Request $request,$id,$eventtype=null, $viewType=null) {
        return $this->viewFileMethod($request,$id,$this->container->getParameter('calllog.sitename'),$eventtype,$viewType);
    }


} 