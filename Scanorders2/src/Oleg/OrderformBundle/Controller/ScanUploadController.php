<?php
/**
 * Created by PhpStorm.
 * User: oli2002
 * Date: 10/15/14
 * Time: 11:57 AM
 */

namespace Oleg\OrderformBundle\Controller;


use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

use Oleg\UserdirectoryBundle\Controller\UploadController;


class ScanUploadController extends UploadController {

    /**
     * @Route("/file-delete", name="scan_file_delete")
     * @Method("POST")
     */
    public function deleteFileAction(Request $request) {
        return $this->deleteFileMethod($request);
    }

    /**
     * @Route("/file-download/{id}", name="scan_file_download", requirements={"id" = "\d+"})
     * @Method("GET")
     */
    public function downloadFileAction($id) {
        return $this->downloadFileMethod($id);
    }

} 