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

namespace App\VacReqBundle\Controller;

use App\UserdirectoryBundle\Controller\OrderAbstractController;
//use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
//use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

//vacreq site

class DefaultController extends OrderAbstractController
{

    /**
     * @Route("/about", name="vacreq_about_page")
     * @Template("AppUserdirectoryBundle/Default/about.html.twig")
     */
    public function aboutAction( Request $request ) {
        return array('sitename'=>$this->getParameter('vacreq.sitename'));
    }

//    /**
//     * @Route("/", name="vacreq_home")
//     * @Template("AppVacReqBundle/Request/index.html.twig", methods={"GET"})
//     */
//    public function indexAction()
//    {
//        if( false == $this->get('security.authorization_checker')->isGranted('ROLE_VACREQ_USER') ) {
//            return $this->redirect( $this->generateUrl('vacreq-nopermission') );
//        }
//
//        $em = $this->getDoctrine()->getManager();
//
//        $vacReqRequests = $em->getRepository('AppVacReqBundle:VacReqRequest')->findAll();
//
//        return array(
//            'vacReqRequests' => $vacReqRequests
//        );
//    }


    /**
     * //@Route("/download-spreadsheet-with-ids/{ids}", name="vacreq_download_spreadsheet_get_ids")
     *
     * @Route("/download-spreadsheet/", name="vacreq_download_spreadsheet", methods={"POST"})
     */
    public function downloadExcelAction( Request $request ) {
        if( false == $this->get('security.authorization_checker')->isGranted('ROLE_VACREQ_USER') ) {
            return $this->redirect( $this->generateUrl('vacreq-nopermission') );
        }

        $vacreqUtil = $this->get('vacreq_util');


        $ids = $request->request->get('ids');
        //echo "ids=".$ids."<br>";
        //exit('111');

        $fileName = "Stats".".xlsx";

        if(0) {
            $fileName = "PhpOffice_".$fileName;

            $excelBlob = $vacreqUtil->createtListExcel($ids);

            $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($excelBlob, 'Xlsx');
            //ob_end_clean();
            //$writer->setIncludeCharts(true);

            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');

            header('Content-Disposition: attachment;filename="' . $fileName . '"');
            //header('Content-Disposition: attachment;filename="fileres.xlsx"');

            // Write file to the browser
            $writer->save('php://output');
        } else {
            //Spout
            $vacreqUtil->createtListExcelSpout( $ids, $fileName );
        }

        exit();
    }
}
