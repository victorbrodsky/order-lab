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

namespace Oleg\VacReqBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

//vacreq site

class DefaultController extends Controller
{

    /**
     * @Route("/about", name="vacreq_about_page")
     * @Template("OlegUserdirectoryBundle:Default:about.html.twig")
     */
    public function aboutAction( Request $request ) {
        return array('sitename'=>$this->container->getParameter('vacreq.sitename'));
    }

//    /**
//     * @Route("/", name="vacreq_home")
//     * @Template("OlegVacReqBundle:Request:index.html.twig")
//     * @Method("GET")
//     */
//    public function indexAction()
//    {
//        if( false == $this->get('security.authorization_checker')->isGranted('ROLE_VACREQ_USER') ) {
//            return $this->redirect( $this->generateUrl('vacreq-nopermission') );
//        }
//
//        $em = $this->getDoctrine()->getManager();
//
//        $vacReqRequests = $em->getRepository('OlegVacReqBundle:VacReqRequest')->findAll();
//
//        return array(
//            'vacReqRequests' => $vacReqRequests
//        );
//    }


    /**
     * @Route("/download-excel/", name="vacreq_download_excel")
     * @Route("/download-excel-with-ids/{ids}", name="vacreq_download_excel_get_ids")
     * @Method({"POST"})
     */
    public function downloadExcelAction( Request $request ) {
        if( false == $this->get('security.authorization_checker')->isGranted('ROLE_VACREQ_USER') ) {
            return $this->redirect( $this->generateUrl('vacreq-nopermission') );
        }

        $vacreqUtil = $this->get('vacreq_util');


        $ids = $request->request->get('ids');
        //echo "ids=".$ids."<br>";
        //exit('111');

        $excelBlob = $vacreqUtil->createtListExcel($ids);

        $fileName = "Stats".".xlsx";

        $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($excelBlob, 'Xlsx');
        //ob_end_clean();
        //$writer->setIncludeCharts(true);

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');

        header('Content-Disposition: attachment;filename="'.$fileName.'"');
        //header('Content-Disposition: attachment;filename="fileres.xlsx"');

        // Write file to the browser
        $writer->save('php://output');

        exit();
    }
}
