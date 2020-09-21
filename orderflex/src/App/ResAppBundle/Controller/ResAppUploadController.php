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
 * Date: 10/15/14
 * Time: 11:57 AM
 */

namespace App\ResAppBundle\Controller;

use App\UserdirectoryBundle\Util\LargeFileDownloader;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\UserdirectoryBundle\Controller\UploadController;
use Symfony\Component\HttpFoundation\Response;


class ResAppUploadController extends UploadController {

    /**
     * @Route("/file-delete", name="resapp_file_delete", methods={"GET", "POST", "DELETE"})
     */
    public function deleteFileAction(Request $request) {
        if( false == $this->get('security.authorization_checker')->isGranted('ROLE_RESAPP_COORDINATOR') && false == $this->get('security.authorization_checker')->isGranted('ROLE_RESAPP_DIRECTOR') ){
            //exit('no resapp permission');
            return $this->redirect( $this->generateUrl('resapp-nopermission') );
        }

        return $this->deleteFileMethod($request);
    }

    /**
     * $id - document id
     *
     * @Route("/file-download/{id}/{eventtype}", name="resapp_file_download", methods={"GET"}, requirements={"id" = "\d+"})
     */
    public function downloadFileAction(Request $request,$id,$eventtype=null) {

        if( false == $this->get('security.authorization_checker')->isGranted('ROLE_RESAPP_USER') ){
            return $this->redirect( $this->generateUrl('resapp-nopermission') );
        }

        //// quick fix for non-existing http://c.med.cornell.edu/order/fellowship-applications/file-download/7281
//        $userSecUtil = $this->container->get('user_security_utility');
//        $environment = $userSecUtil->getSiteSettingParameter('environment');
//        if( $id == 7281 && $environment == "live" ) {
//            $id = 7327;
//        }
        /////// EOF ///////

        return $this->downloadFileMethod($request,$id,$this->getParameter('resapp.sitename'),$eventtype);
    }


    /**
     * $id - document id
     *
     * @Route("/file-view/{id}/{viewType}/{eventtype}", name="resapp_file_view", methods={"GET"}, requirements={"id" = "\d+"})
     */
    public function viewFileAction(Request $request,$id,$eventtype=null,$viewType=null) {

        if( false == $this->get('security.authorization_checker')->isGranted('ROLE_RESAPP_USER') ){
            return $this->redirect( $this->generateUrl('resapp-nopermission') );
        }

        return $this->viewFileMethod($request,$id,$this->getParameter('resapp.sitename'),$eventtype,$viewType);
    }


} 