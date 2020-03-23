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

namespace App\OrderformBundle\Controller;






//use App\CrnBundle\Entity\CrnEntryTagsList;
use App\OrderformBundle\Entity\AmendmentReasonList;
use App\OrderformBundle\Entity\CalllogAttachmentTypeList;
use App\OrderformBundle\Entity\CalllogEntryTagsList;
use App\OrderformBundle\Entity\CalllogTaskTypeList;
use App\OrderformBundle\Entity\CourseGroupType;
use App\OrderformBundle\Entity\DiseaseOriginList;
use App\OrderformBundle\Entity\DiseaseTypeList;
use App\OrderformBundle\Entity\EmbedderInstructionList;
use App\OrderformBundle\Entity\EncounterInfoType;
use App\OrderformBundle\Entity\EncounterInfoTypeList;
use App\OrderformBundle\Entity\EncounterStatusList;
use App\OrderformBundle\Entity\ImageAnalysisAlgorithmList;
use App\OrderformBundle\Entity\Magnification;
use App\OrderformBundle\Entity\MessageStatusList;
use App\OrderformBundle\Entity\MessageTypeClassifiers;
use App\OrderformBundle\Entity\PatientListHierarchy;
use App\OrderformBundle\Entity\PatientListHierarchyGroupType;
use App\OrderformBundle\Entity\PatientRecordStatusList;
use App\OrderformBundle\Entity\ResearchGroupType;
//use App\OrderformBundle\Entity\SystemAccountRequestType;
use App\UserdirectoryBundle\Controller\OrderAbstractController;
//use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
//use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;

use App\OrderformBundle\Entity\AccessionType;
use App\OrderformBundle\Entity\EncounterType;
use App\OrderformBundle\Entity\ProcedureType;
use App\OrderformBundle\Entity\StainList;
use App\OrderformBundle\Entity\OrganList;
use App\OrderformBundle\Entity\ProcedureList;
use App\OrderformBundle\Entity\Status;
use App\OrderformBundle\Entity\SlideType;
use App\OrderformBundle\Entity\MrnType;
use App\OrderformBundle\Helper\FormHelper;
use App\OrderformBundle\Entity\RegionToScan;
use App\OrderformBundle\Entity\ProcessorComments;
use App\OrderformBundle\Entity\Urgency;
use App\OrderformBundle\Entity\ProgressCommentsEventTypeList;
use App\OrderformBundle\Entity\RaceList;
use App\OrderformBundle\Entity\OrderDelivery;
use App\OrderformBundle\Entity\MessageCategory;
use App\OrderformBundle\Entity\PatientTypeList;


use App\UserdirectoryBundle\Util\UserUtil;
use App\UserdirectoryBundle\Controller\AdminController;
use App\UserdirectoryBundle\Entity\SiteParameters;


/**
 * @Route("/admin")
 */
class ScanAdminController extends AdminController
{
    /**
     * Admin Page
     *
     * @Route("/lists/", name="admin_index", methods={"GET"})
     * @Template("AppOrderformBundle/Admin/index.html.twig")
     */
    public function indexAction()
    {

        $environment = 'dev'; //default

        $em = $this->getDoctrine()->getManager();
        $params = $roles = $em->getRepository('AppUserdirectoryBundle:SiteParameters')->findAll();

        if( count($params) > 1 ) {
            throw new \Exception( 'Must have only one parameter object. Found '.count($params).'object(s)' );
        }

        if( count($params) == 1 ) {
            $param = $params[0];
            $environment = $param->getEnvironment();
        }

        return $this->render('AppOrderformBundle/Admin/index.html.twig', array('environment'=>$environment));
    }

    /**
     * Admin Page
     *
     * @Route("/hierarchies/", name="scan_admin_hierarchy_index", methods={"GET"})
     * @Template("AppOrderformBundle/Admin/hierarchy-index.html.twig")
     */
    public function indexHierarchyAction()
    {

        $environment = 'dev'; //default

        $em = $this->getDoctrine()->getManager();
        $params = $roles = $em->getRepository('AppUserdirectoryBundle:SiteParameters')->findAll();

        if( count($params) > 1 ) {
            throw new \Exception( 'Must have only one parameter object. Found '.count($params).'object(s)' );
        }

        if( count($params) == 1 ) {
            $param = $params[0];
            $environment = $param->getEnvironment();
        }

        $filters = $this->getDefaultHierarchyFilter();

        return $this->render('AppOrderformBundle/Admin/hierarchy-index.html.twig', array('environment'=>$environment,'filters'=>$filters));
    }


    /**
     * Populate DB
     *
     * @Route("/populate-all-lists-with-default-values", name="generate_all", methods={"GET"})
     * @Template()
     */
    public function generateAllAction()
    {

        if( false === $this->get('security.authorization_checker')->isGranted('ROLE_SCANORDER_PROCESSOR') ) {
            return $this->redirect( $this->generateUrl($this->getParameter('scan.sitename').'-order-nopermission') );
        }

        //$max_exec_time = ini_get('max_execution_time');
        //ini_set('max_execution_time', 900); //900 seconds = 15 minutes

        //$default_time_zone = $this->getParameter('default_time_zone');

        $msg = $this->generateScanorderAll();

        $this->get('session')->getFlashBag()->add(
            'notice',
            $msg
        );

        //ini_set('max_execution_time', $max_exec_time); //set back to the original value

        return $this->redirect($this->generateUrl('employees_siteparameters'));

        //return $this->redirect($this->generateUrl('admin_index'));
    }

    public function generateScanorderAll() {

        $logger = $this->container->get('logger');
        $logger->notice("Start generateScanorderAll");

        $max_exec_time = ini_get('max_execution_time');
        ini_set('max_execution_time', 900); //900 seconds = 15 minutes

        //$default_time_zone = $this->getParameter('default_time_zone');


        $count_pattype = $this->generatePatientType();
        $count_acctype = $this->generateAccessionType();
        $count_enctype = $this->generateEncounterType();
        $count_EncounterInfoType = $this->generateEncounterInfoType();
        $count_proceduretype = $this->generateProcedureType();
        $count_MessageTypeClassifiers = $this->generateMessageTypeClassifiers();
        $count_generateMessageCategory = $this->generateMessageCategory();
        $count_stain = $this->generateStains();
        $count_organ = $this->generateOrgans();
        $count_procedure = $this->generateProcedures();
        $count_status = $this->generateStatuses();
        $count_EmbedderInstructionList = $this->generateEmbedderInstructionList();
        $count_slidetype = $this->generateSlideType();
        $count_mrntype = $this->generateMrnType();
        $count_OrderDelivery = $this->generateOrderDelivery();
        $count_RegionToScan = $this->generateRegionToScan();
        $count_comments = $this->generateProcessorComments();
        $count_urgency = $this->generateUrgency();
        $count_progressCommentsEventType = $this->generateProgressCommentsEventType();
        $count_generateMagnifications = $this->generateMagnifications();
        $count_generateImageAnalysisAlgorithmList = $this->generateImageAnalysisAlgorithmList();
        $count_race = $this->generateRace();
        $count_DiseaseTypeList = $this->generateDiseaseTypeList();
        $count_DiseaseOriginList = $this->generateDiseaseOriginList();
        $count_ResearchGroupType = $this->generateResearchGroupType();
        $count_CourseGroupType = $this->generateCourseGroupType();
        //$count_SystemAccountRequestType = $this->generateSystemAccountRequestType();
        $count_AmendmentReason = $this->generateAmendmentReason();
        $count_PatientListHierarchyGroupType = $this->generatePatientListHierarchyGroupType();
        $count_PatientListHierarchy = $this->generatePatientListHierarchy();
        $count_generateEncounterStatus = $this->generateEncounterStatus();
        $count_generatePatientRecordStatus = $this->generatePatientRecordStatus();
        $count_generateMessageStatus = $this->generateMessageStatus();
        $count_generateCalllogEntryTagsList = $this->generateCalllogEntryTagsList();
        $count_generateCalllogAttachmentTypeList = $this->generateCalllogAttachmentTypeList();
        $count_generateCalllogTaskTypeList = $this->generateCalllogTaskTypeList();
        //$count_generateCrnEntryTagsList = $this->generateCrnEntryTagsList();

        $msg =
            'Generated Tables: '.
            //'Roles='.$count_roles.', '.
            'Patient Types='.$count_pattype.', '.
            'Accession Types='.$count_acctype.', '.
            'Encounter Types='.$count_proceduretype.', '.
            'Procedure Types='.$count_enctype.', '.
            'MessageTypeClassifiers='.$count_MessageTypeClassifiers.', '.
            'Message Category='.$count_generateMessageCategory.', '.
            'Stains='.$count_stain.', '.
            'Organs='.$count_organ.', '.
            'Procedures='.$count_procedure.', '.
            'DiseaseTypes='.$count_DiseaseTypeList.', '.
            'DiseaseOrigins='.$count_DiseaseOriginList.', '.
            'Statuses='.$count_status.', '.
            'Slide Types='.$count_slidetype.', '.
            'MRN Types='.$count_mrntype.', '.
            'Order Delivery='.$count_OrderDelivery.', '.
            'Region To Scan='.$count_RegionToScan.', '.
            'Processor Comments='.$count_comments.', '.
            'Urgency='.$count_urgency.', '.
            'Progress and Comments EventTypes='.$count_progressCommentsEventType.', '.
            'Races='.$count_race.', '.
            'Magnifications='.$count_generateMagnifications.', '.
            'Embedder Instructions ='.$count_EmbedderInstructionList.', '.
            'ImageAnalysisAlgorithmList='.$count_generateImageAnalysisAlgorithmList.', '.
            'Research Group Types='.$count_ResearchGroupType.', '.
            'Educational Group Types='.$count_CourseGroupType.', '.
            //'SystemAccountRequestTypes='.$count_SystemAccountRequestType.', '.
            'AmendmentReasons='.$count_AmendmentReason.', '.
            'PatientListHierarchyGroupType='.$count_PatientListHierarchyGroupType.', '.
            'PatientListHierarchy='.$count_PatientListHierarchy.', '.
            'EncounterInfoType='.$count_EncounterInfoType.', '.
            'EncounterStatus='.$count_generateEncounterStatus.', '.
            'PatientRecordStatus='.$count_generatePatientRecordStatus.', '.
            'MessageStatus='.$count_generateMessageStatus.', '.
            'CalllogEntryTagsList='.$count_generateCalllogEntryTagsList.', '.
            'CalllogAttachmentTypeList='.$count_generateCalllogAttachmentTypeList.', '.
            'CalllogTaskTypeList='.$count_generateCalllogTaskTypeList.', '.
//            'CrnEntryTagsList='.$count_generateCrnEntryTagsList.', '.

            ' (Note: -1 means that this table is already exists)';

        $logger->notice("Finished generateScanorderAll. msg=".$msg);

        return $msg;
    }


    /**
     * Populate DB
     *
     * @Route("/populate-stain-list-with-default-values", name="generate_stain", methods={"GET"})
     * @Template()
     */
    public function generateStainAction()
    {

        if( false === $this->get('security.authorization_checker')->isGranted('ROLE_SCANORDER_PROCESSOR') ) {
            return $this->redirect( $this->generateUrl($this->getParameter('scan.sitename').'-order-nopermission') );
        }

        $count = $this->generateStains();
        if( $count >= 0 ) {

            $this->get('session')->getFlashBag()->add(
                'notice',
                'Generated '.$count. ' stain records.'
            );

            return $this->redirect($this->generateUrl('employees_siteparameters'));

        } else {

            $this->get('session')->getFlashBag()->add(
                'notice',
                'This table is already exists!'
            );

            return $this->redirect($this->generateUrl('employees_siteparameters'));
        }

    }


    /**
     * Populate DB
     *
     * @Route("/populate-organ-list-with-default-values", name="generate_organ", methods={"GET"})
     * @Template()
     */
    public function generateOrganAction()
    {

        $count = $this->generateOrgans();

        if( $count >= 0 ) {

            $this->get('session')->getFlashBag()->add(
                'notice',
                'Created '.$count. ' organ records'
            );

            return $this->redirect($this->generateUrl('organlist'));

        } else {

            $this->get('session')->getFlashBag()->add(
                'notice',
                'This table is already exists!'
            );

            return $this->redirect($this->generateUrl('employees_siteparameters'));
        }

    }



    /**
     * Populate DB
     *
     * @Route("/populate-procedure-types-list-with-default-values", name="generate_procedure", methods={"GET"})
     * @Template()
     */
    public function generateProcedureAction()
    {

//        $em = $this->getDoctrine()->getManager();
//        $entities = $em->getRepository('AppOrderformBundle:ProcedureList')->findAll();

        $count = $this->generateProcedures();

        if( $count >= 0 ) {

            $this->get('session')->getFlashBag()->add(
                'notice',
                'Created '.$count. ' procedure records'
            );

            return $this->redirect($this->generateUrl('procedurelist'));
        } else {

            $this->get('session')->getFlashBag()->add(
                'notice',
                'This table is already exists!'
            );

            return $this->redirect($this->generateUrl('employees_siteparameters'));
        }

    }


//    /**
//     * Populate DB
//     *
//     * @Route("/genpathservice", name="generate_pathservice", methods={"GET"})
//     * @Template()
//     */
//    public function generatePathServiceAction()
//    {
//
//        $count = $this->generatePathServices();
//        if( $count >= 0 ) {
//
//            $this->get('session')->getFlashBag()->add(
//                'notice',
//                'Created '.$count. ' stain records'
//            );
//
//            return $this->redirect($this->generateUrl('stainlist'));
//
//        } else {
//
//            $this->get('session')->getFlashBag()->add(
//                'notice',
//                'This table is already exists!'
//            );
//
//            return $this->redirect($this->generateUrl('employees_siteparameters'));
//        }
//
//    }

    /**
     * Populate DB
     *
     * @Route("/genslidetype", name="generate_slidetype", methods={"GET"})
     * @Template()
     */
    public function generateSlideTypeAction()
    {

        $count = $this->generateSlideType();
        if( $count >= 0 ) {

            $this->get('session')->getFlashBag()->add(
                'notice',
                'Created '.$count. ' slide types records'
            );

            return $this->redirect($this->generateUrl('slidetype'));

        } else {

            $this->get('session')->getFlashBag()->add(
                'notice',
                'This table is already exists!'
            );

            return $this->redirect($this->generateUrl('employees_siteparameters'));
        }

    }

    /**
     * Populate DB
     *
     * @Route("/genmrntype", name="generate_mrntype", methods={"GET"})
     * @Template()
     */
    public function generateMrnTypeAction()
    {

        $count = $this->generateMrnType();
        if( $count >= 0 ) {

            $this->get('session')->getFlashBag()->add(
                'notice',
                'Created '.$count. ' mrn type records'
            );

            return $this->redirect($this->generateUrl('mrntype'));

        } else {

            $this->get('session')->getFlashBag()->add(
                'notice',
                'This table is already exists!'
            );

            return $this->redirect($this->generateUrl('employees_siteparameters'));
        }

    }


//////////////////////////////////////////////////////////////////////////////


//    //return -1 if failed
//    //return number of generated records
//    public function generateStains_OLD() {
//
//        $helper = new FormHelper();
//        $stains = $helper->getStains();
//
//        $username = $this->get('security.token_storage')->getToken()->getUser();
//
//        $em = $this->getDoctrine()->getManager();
//        $entities = $em->getRepository('AppOrderformBundle:StainList')->findAll();
//
//        if( $entities ) {
//
//            return -1;
//        }
//
//        $count = 1;
//        foreach( $stains as $stain ) {
//            $stainList = new StainList();
//            $this->setDefaultList($stainList,$count,$username,$stain);
//
//            $em->persist($stainList);
//            $em->flush();
//
//            $count = $count + 10;
//        }
//
//        return round($count/10);
//    }

    //populate stains from Excel sheet downloaded from the system
    public function generateStains() {

        $username = $this->get('security.token_storage')->getToken()->getUser();

        $em = $this->getDoctrine()->getManager();

        $inputFileName = __DIR__ . '/../Resources/Stains.xlsm';

        if( !file_exists($inputFileName) ) {
            return 0;
        }

        try {
            $inputFileType = \PhpOffice\PhpSpreadsheet\IOFactory::identify($inputFileName);
            $objReader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader($inputFileType);
            $objPHPExcel = $objReader->load($inputFileName);
        } catch(Exception $e) {
            die('Error loading file "'.pathinfo($inputFileName,PATHINFO_BASENAME).'": '.$e->getMessage());
        }

        $sheet = $objPHPExcel->getSheet(0);
        $highestRow = $sheet->getHighestRow();
        $highestColumn = $sheet->getHighestColumn();

        $count = 10;

        //0       1                         2           3               4          5           6         7
        //ID	Name	                Short Name	Abbreviation	Description	Original	Synonyms	Type	 Display Order	///Creator	Creation Date	Updated By	Updated On
        //1 	Hematoxylin and Eosin		            H&E				                                default	   10	        ///username	42,256.68	username (CWID) - username	42,342.79

        $firstRowWithData = 2; //2

        //for each row in excel
        for( $row = $firstRowWithData; $row <= $highestRow; $row++ ) {

            //  Read a row of data into an array
            $rowData = $sheet->rangeToArray('A' . $row . ':' . $highestColumn . $row,
                NULL,
                TRUE,
                FALSE);

            //echo $row.": ";
            //print_r($rowData);
            //echo "<br>";

            $stainId = trim($rowData[0][0]);
            $stainName = trim($rowData[0][1]);
            $stainShortName = trim($rowData[0][2]);
            $stainAbbr = trim($rowData[0][3]);
            $stainDescription = trim($rowData[0][4]);
            //Original 5
            $synonym = trim($rowData[0][6]);
            //LIS Name 7
            $type = trim($rowData[0][8]);
            $order = trim($rowData[0][9]);

//            echo "stainId=".$stainId."<br>";
//            echo "stainName=".$stainName."<br>";
//            echo "synonym=".$synonym."<br>";
//            echo "type=".$type."<br>";
//            echo "order=".$order."<br>";
            //exit('import stains');

            if( !$order ) {
                //echo "Don't update (display order does not exists): order=".$order."<br>";
                continue;
            }

            if( $order && !is_numeric($order) ) {
                //echo "Don't update (display order is not an integer): order=".$order." ???!!!<br>";
                continue;
            }

            if( $type == 'disabled' ) {
                //echo "Don't update: type=".$type." !!!!!!!!!!!!!!!<br>";
                continue;
            }

            //if( !$stainName || $stainName == "" ) {
            //    echo "Don't update: stainName=".$stainName." !!!!!!!!!!!!!!<br>";
            //    continue;
            //}

            //if( $em->getRepository('AppOrderformBundle:StainList')->findOneByName($stainName) ) {
            //    continue;
            //}

            //exit('stain exit');

            if( $stainName ) {
                $entity = $em->getRepository('AppOrderformBundle:StainList')->findOneByName($stainName);
            }

            if( !$entity ) {
                //exit("Stain not found!!!!!!!!!! ID=".$stainId);
                $entity = new StainList();
                $this->setDefaultList($entity,$order,$username,$stainName);
                $em->persist($entity);
            }

            if( $stainName ) {
                $entity->setName($stainName);
            }

            if( $stainShortName ) {
                $entity->setShortname($stainShortName);
            }

            if( $stainAbbr ) {
                $entity->setAbbreviation($stainAbbr);
            }

            if( $stainDescription ) {
                $entity->setDescription($stainDescription);
            }

            if( $order ) {
                $entity->setOrderinlist($order);
            }

            if( $synonym ) {
                //echo "synonym=".$synonym."<br>";
                $synonymEntity = $em->getRepository('AppOrderformBundle:StainList')->findOneByName($synonym);
                if( !$synonymEntity ) {
                    //exit("Synonim not found!!!!!!!!!!!!!! Name=".$synonym);
                    //$count = $count + 10;
                    $synonymEntity = new StainList();
                    $this->setDefaultList($synonymEntity,$count,$username,$synonym);
                    //$em->persist($entity);
                    $em->persist($synonymEntity);
                    //$em->flush();
                }

                $entity->addSynonym($synonymEntity);
                //echo $entity.": add synonym=".$synonymEntity."<br>";
            }

            //Create full title according to name, abbreviation, short name and synonyms
            $entity->createFullTitle();

            //echo "Update stain=".$entity."<br>";
            //exit();

            //$em->persist($entity);
            $em->flush();
            //exit();

            $count = $count + 10;
        }

        //exit('stain exit, count='.$count);
        return round($count/10);
    }

//    /**
//     * Remove disabled stains
//     *
//     * @Route("/remove-disabled-stains", name="remove-disabled-stains", methods={"GET"})
//     * @Template()
//     */
//    public function removeDeactivatedStainsAction() {
//
//        exit('disabled');
//
//        if( false === $this->get('security.authorization_checker')->isGranted('ROLE_PLATFORM_DEPUTY_ADMIN') ) {
//            return $this->redirect( $this->generateUrl($this->getParameter('scan.sitename').'-order-nopermission') );
//        }
//
//        $em = $this->getDoctrine()->getManager();
//        $stains = $em->getRepository('AppOrderformBundle:StainList')->findAll();
//
//        $count = 0;
//
//        foreach( $stains as $stain ) {
//            if( $stain->getType() == "disabled" ) {
//                echo "remove disabled stain ".$stain."<br>";
//                //$em->remove($stain);
//                //$em->flush();
//                $count++;
//            }
//        }
//
//        $this->get('session')->getFlashBag()->add(
//            'notice',
//            'Removed disabled '.$count. ' stains.'
//        );
//
//        return $this->redirect($this->generateUrl('stain-list'));
//    }

    /**
     * Remove all stains: Danger function: will remove all orders (patients) and stains
     *
     * @Route("/remove-all-stains", name="remove-all-stains", methods={"GET"})
     * @Template()
     */
    public function removeAllOrdersStainsAction() {

        if( false === $this->get('security.authorization_checker')->isGranted('ROLE_PLATFORM_DEPUTY_ADMIN') ) {
            return $this->redirect( $this->generateUrl($this->getParameter('scan.sitename').'-order-nopermission') );
        }

        //1) remove messages with patients: danger !!!
        $orderUtil = $this->get('scanorder_utility');
        $removedMessagesCount = $orderUtil->removeAllOrdersPatients();

        $this->get('session')->getFlashBag()->add(
            'notice',
            'Removed '.$removedMessagesCount. ' messages.'
        );

        //Do it manually by dropping tables.
        //Stain list has two dependencies: scan_stain and scan_blockSpecialStain tables which have to be dropped first before dropping scan_stainList table
        if(0) {
            //2) remove stains
            $count = $orderUtil->removeAllStains();

            $this->get('session')->getFlashBag()->add(
                'notice',
                'Removed '.$count. ' stains.'
            );
        }

        return $this->redirect($this->generateUrl('stain-list'));
    }

    //populate stains from Excel sheet
    public function generateStainsV1() {

        $username = $this->get('security.token_storage')->getToken()->getUser();

        $em = $this->getDoctrine()->getManager();

        $inputFileName = __DIR__ . '/../Resources/Stains_v1.xlsx';

        try {
            $inputFileType = \PhpOffice\PhpSpreadsheet\IOFactory::identify($inputFileName);
            $objReader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader($inputFileType);
            $objPHPExcel = $objReader->load($inputFileName);
        } catch(Exception $e) {
            die('Error loading file "'.pathinfo($inputFileName,PATHINFO_BASENAME).'": '.$e->getMessage());
        }

        $sheet = $objPHPExcel->getSheet(0);
        $highestRow = $sheet->getHighestRow();
        $highestColumn = $sheet->getHighestColumn();

        $count = 10;

        //for each row in excel
        for( $row = 2; $row <= $highestRow; $row++ ){

            $color = $objPHPExcel->getActiveSheet()->getStyle('A'.$row)->getFill()->getStartColor()->getRGB();

            if( $color != '000000' ) {
                continue;
            }

            //echo "A cell color=".$color."<br>";

            //  Read a row of data into an array
            $rowData = $sheet->rangeToArray('A' . $row . ':' . $highestColumn . $row,
                NULL,
                TRUE,
                FALSE);

            //echo $row.": ";
            //var_dump($rowData);
            //echo "<br>";

            //ResidencySpecialty	FellowshipSubspecialty	BoardCertificationAvailable
            //$oldStainName = trim($rowData[0][0]);
            $stainName = trim($rowData[0][1]);
            $stainShortName = trim($rowData[0][2]);
            $stainAbbr = trim($rowData[0][3]);
            //$stainLISName = $rowData[0][4];
            //$stainLISAbbr = $rowData[0][5];
            $synonyms = trim($rowData[0][6]);


            //echo "stainName=".$stainName."<br>";
            //echo "synonyms=".$synonyms."<br>";

            if( !$stainName || $stainName == "" ) {
                continue;
            }

            if( $em->getRepository('AppOrderformBundle:StainList')->findOneByName($stainName) ) {
                continue;
            }

            //exit('stain exit');

            $entity = new StainList();
            $this->setDefaultList($entity,$count,$username,$stainName);

            if( $stainShortName ) {
                $entity->setShortname($stainShortName);
            }

            if( $stainAbbr ) {
                $entity->setAbbreviation($stainAbbr);
            }

            //echo "stain=".$entity.", ShortName=".$entity->getShortname().", Abbr=".$entity->getAbbreviation()."<br>";

            //synonyms
            $synonymsArr = explode(",", $synonyms);
            foreach( $synonymsArr as $synonym ) {
                $synonym = trim($synonym);

                if( !$synonym || $synonym == "" ) {
                    continue;
                }

                $synonymEntity = $em->getRepository('AppOrderformBundle:StainList')->findOneByName($synonym);
                if( !$synonymEntity ) {

                    $count = $count + 10;
                    $synonymEntity = new StainList();
                    $this->setDefaultList($synonymEntity,$count,$username,$synonym);

                    $em->persist($entity);
                    $em->persist($synonymEntity);
                    $em->flush();

                }

                $entity->addSynonym($synonymEntity);
                //echo "synonym=".$synonymEntity."<br>";
                //exit();
            }

            $em->persist($entity);
            $em->flush();

            $count = $count + 10;

        }

        //exit('stain exit, count='.$count);
        return round($count/10);
    }

    public function generateOrgans() {

        $em = $this->getDoctrine()->getManager();
        $entities = $em->getRepository('AppOrderformBundle:OrganList')->findAll();

        if( $entities ) {

            return -1;
        }

        $helper = new FormHelper();
        $organs = $helper->getSourceOrgan();

        $username = $this->get('security.token_storage')->getToken()->getUser();

        $count = 10;
        foreach( $organs as $organ ) {

            $list = new OrganList();
            $this->setDefaultList($list,$count,$username,$organ);

            $em->persist($list);
            $em->flush();

            $count = $count + 10;
        }


        return round($count/10);
    }

    public function generateProcedures() {

        $em = $this->getDoctrine()->getManager();
        $entities = $em->getRepository('AppOrderformBundle:ProcedureList')->findAll();

        if( $entities ) {

           return -1;
        }

        $helper = new FormHelper();
        $procedures = $helper->getProcedure();

        $username = $this->get('security.token_storage')->getToken()->getUser();

        $count = 10;
        foreach( $procedures as $procedure ) {

            $list = new ProcedureList();
            $this->setDefaultList($list,$count,$username,$procedure);

            $em->persist($list);
            $em->flush();

            $count = $count + 10;
        }

        return round($count/10);
    }

    public function generateStatuses() {

        $username = $this->get('security.token_storage')->getToken()->getUser();

        $em = $this->getDoctrine()->getManager();
        $entities = $em->getRepository('AppOrderformBundle:Status')->findAll();

        if( $entities ) {
            return -1;
        }

        $statuses = array(
            "Not Submitted", "Submitted", "Amended",
            "Superseded", "Canceled by Submitter", "Canceled by Processor",
            "On Hold: Awaiting Slides", "On Hold: Slides Received",
            "Filled: Scanned", "Filled: Some Scanned", "Filled: Not Scanned",
            "Filled: Scanned & Returned", "Filled: Some Scanned & Returned", "Filled: Not Scanned & Returned"
        );

        $count = 10;

        foreach( $statuses as $statusStr ) {

            $status = new Status();
            $this->setDefaultList($status,$count,$username,null);

            //Regular
            switch( $statusStr )
            {

                case "Not Submitted":
                    $status->setName("Not Submitted");
                    $status->setAction("On Hold");
                    break;
                case "Submitted":
                    $status->setName("Submitted");
                    $status->setAction("Submit");
                    break;
                case "Amended":
                    $status->setName("Amended");
                    $status->setAction("Amend");
                    break;
                case "Canceled by Submitter":
                    $status->setName("Canceled by Submitter");
                    $status->setAction("Cancel");
                    break;
                case "Canceled by Processor":
                    $status->setName("Canceled by Processor");
                    $status->setAction("Cancel");
                    break;

                case "Superseded":
                    $status->setName("Superseded");
                    $status->setAction("Supersede");
                    break;
                default:
                    break;
            }

            //Filled
            if( strpos($statusStr,'Filled') !== false ) {
                $status->setName($statusStr);
                $status->setAction($statusStr);
            }

            //On Hold
            if( strpos($statusStr,'On Hold') !== false ) {
                $status->setName($statusStr);
                $status->setAction($statusStr);
            }

            $em->persist($status);
            $em->flush();

            $count = $count + 10;
        } //foreach

        return round($count/10);
    }

    public function generateSlideType() {

        $helper = new FormHelper();
        $types = $helper->getSlideType();

        $username = $this->get('security.token_storage')->getToken()->getUser();

        $em = $this->getDoctrine()->getManager();
        $entities = $em->getRepository('AppOrderformBundle:SlideType')->findAll();

        if( $entities ) {

            return -1;
        }

        $count = 10;
        foreach( $types as $type ) {

            $slideType = new SlideType();
            $this->setDefaultList($slideType,$count,$username,$type);

            if( $type == "TMA" ) {
                $slideType->setType('TMA');
            }

            $em->persist($slideType);
            $em->flush();

            $count = $count + 10;
        }

        return round($count/10);
    }

    public function generateMrnType() {

        $username = $this->get('security.token_storage')->getToken()->getUser();

        $em = $this->getDoctrine()->getManager();
//        $entities = $em->getRepository('AppOrderformBundle:MrnType')->findAll();
//        if( $entities ) {
//            return -1;
//        }

        $types = array(
            'New York Hospital MRN',
            'Epic Ambulatory Enterprise ID Number',
            'Weill Medical College IDX System MRN',
            'Enterprise Master Patient Index',
            'Uptown Hospital ID',
            'NYH Health Quest Corporate Person Index',
            'New York Downtown Hospital',
            'De-Identified NYH Tissue Bank Research Patient ID',
            'De-Identified Personal Educational Slide Set Patient ID',
            'De-Identified Personal Research Project Patient ID',
            'California Tumor Registry Patient ID',
            'Specify Another Patient ID Issuer',
            'Auto-generated MRN',
            'Existing Auto-generated MRN',
            'Merge ID'
        );

        $count = 10;
        foreach( $types as $type ) {

            $mrnType = $em->getRepository('AppOrderformBundle:MrnType')->findOneByName($type);
            if( $mrnType ) {
                continue;
            }

            $mrnType = new MrnType();
            $this->setDefaultList($mrnType,$count,$username,$type);

            $em->persist($mrnType);
            $em->flush();

            $count = $count + 10;
        }

        return round($count/10);
    }

    //positive level - default level's title
    //negative level - all other title
    public function generateMessageTypeClassifiers() {

        $username = $this->get('security.token_storage')->getToken()->getUser();

        $em = $this->getDoctrine()->getManager();

        $types = array(
            'Message Class' => 0, //positive level - default level's title
            'Message Subclass' => 1,
            'Message Group' => 2,
            'Service' => 3,
            'Issue' => 4,
        );

        $count = 10;
        foreach( $types as $name=>$level ) {

            $messageTypeClassifier = $em->getRepository('AppOrderformBundle:MessageTypeClassifiers')->findOneByName($name);
            if( $messageTypeClassifier ) {
                continue;
            } else {
                $messageTypeClassifier = new MessageTypeClassifiers();
            }

            $this->setDefaultList($messageTypeClassifier,$count,$username,$name);

            $messageTypeClassifier->setLevel($level);

            $em->persist($messageTypeClassifier);
            $em->flush();

            $count = $count + 10;
        }

        return round($count/10);
    }

    public function generateMessageCategory() {

        $username = $this->get('security.token_storage')->getToken()->getUser();

        $categories = array(

            'Order' => array(
                'Scan Order' => array(
                    'One-Slide Scan Order',
                    'Multi-Slide Scan Order',
                    'Table-View Scan Order'
                ),
                'Slide Return Request',
                'Encounter Order',
                'Procedure Order',
                'Referral Order',
                'Tissue Examination',
                'Embed Block Order',
                'Slide Order',
                'Stain Slide Order',
                'Lab Order' => array(
                    'Outside Lab Order - Comprehensive',
                    'Outside Lab Order on Part',
                    'Lab Order Requisition'
                ),
                'Image Analysis Order',
                //'Requisition to Accession',
                'Autopsy Images',
                'Gross Images',
                'Block Images',
            ),
            'Report' => array(
                'Image Analysis Report',
                'Lab Report',
                'Outside Report',
                'Block Report',
                'Slide Report',
                'Stain Report',
                'Scan Report'

            ),
            'Note' => array(
                'Encounter Note' => array(
                    'Pathology Call Log Entry' => array(
                        //Service level
                        'Transfusion Medicine' => array(
                            //Issue level
                            'First dose plasma',
                            'First dose platelets',
                            'Third+ dose platelets',
                            'Cryoprecipitate',
                            'MTP',
                            'Emergency release',
                            'Payson transfusion',
                            'Incompatible crossmatch',
                            'Transfusion reaction',
                            'Complex platelet summary',
                            'Complex factor summary',
                            'WinRho',
                            'Special needs',
                            'Other'
                        ),
                        'Microbiology' => array(
                            'Antibiotic sensitivity approval',
                            'Viracor testing',
                            'Critical Value',
                            'Other'
                        ),
                        'Coagulation' => array(
                            'Critical Value',
                            'Other'
                        ),
                        'Hematology' => array(
                            'Peripheral Blood Smear Review',
                            'Other'
                        ),
                        'Chemistry' => array(
                            //Issue level
                            'Critical Value',
                            'Aberrant analyte',
                            'Other'
                        ),
                        'Cytogenetics' => array(
                            'Other'
                        ),
                        'Molecular' => array(
                            'Other'
                        ),
                        'Other' => array(
                            'Other'
                        ),
                        'Test' => array(
                        ),
                    ),
                    'Critical Result Notification' => array(
                        //Service level
                        'Dermatopathology' => array(
                            //Issue level
                            'Malignancy'
                        )
                    )
                ),
                'Procedure Note'
            ),
            'HemePath Translational Research' => array(
                'HemePath Translational Research Project',
                'HemePath Translational Research Request'
            )

        );

        $count = 10;
        $level = 0;

        $count = $this->addNestedsetCategory(null,$categories,$level,$username,$count);

        //exit('EOF message category');

        return round($count/10);
    }
    public function addNestedsetCategory($parentCategory,$categories,$level,$username,$count) {

        $em = $this->getDoctrine()->getManager();

        foreach( $categories as $category=>$subcategory ) {

            $name = $category;

            if( $subcategory && !is_array($subcategory) ) {
                $name = $subcategory;
            }

            //find by name and by parent ($parentCategory) if exists
            if( $parentCategory ) {
                $mapper = array(
                    'prefix' => "App",
                    'className' => "MessageCategory",
                    'bundleName' => "OrderformBundle"
                );
                $messageCategory = $em->getRepository('AppOrderformBundle:MessageCategory')->findByChildnameAndParent($name,$parentCategory,$mapper);
            } else {
                $messageCategory = $em->getRepository('AppOrderformBundle:MessageCategory')->findOneByName($name);
            }

            if( !$messageCategory ) {
                //make category
                $messageCategory = new MessageCategory();

                $this->setDefaultList($messageCategory,null,$username,$name);
                $messageCategory->setLevel($level);

                //try to get default group by level
                if( !$messageCategory->getOrganizationalGroupType() ) {
                    if( $messageCategory->getLevel() ) {
                        $messageTypeClassifier = $em->getRepository('AppOrderformBundle:MessageTypeClassifiers')->findOneByLevel($messageCategory->getLevel());
                        if ($messageTypeClassifier) {
                            $messageCategory->setOrganizationalGroupType($messageTypeClassifier);
                        }
                    }
                }

                $count = $count + 10;
            }

//            echo $level.": category=".$name.", count=".$count."<br>";
//            echo "subcategory:<br>";
//            print_r($subcategory);
//            echo "<br><br>";
//            echo "messageCategory=".$messageCategory->getName()."<br>";

            //add to parent
            if( $parentCategory ) {
                $em->persist($parentCategory);
                $parentCategory->addChild($messageCategory);
            }

            //$messageCategory->printTree();

            //make children
            if( $subcategory && is_array($subcategory) && count($subcategory) > 0 ) {
                $count = $this->addNestedsetCategory($messageCategory,$subcategory,$level+1,$username,$count);
            }

            //testing
            if( 0 ) {
                if ($messageCategory->getOrganizationalGroupType()) {
                    $label = $messageCategory->getOrganizationalGroupType()->getName();
                } else {
                    $label = null;
                }
                if ($messageCategory->getParent()) {
                    $parent = $messageCategory->getParent()->getName();
                } else {
                    $parent = null;
                }
                echo $messageCategory.": label=".$label."; level=".$messageCategory->getLevel()."; parent=".$parent."<br>";
            }

            $em->persist($messageCategory);
            $em->flush();
        }

        return $count;
    }

    public function generatePatientType() {

        $em = $this->getDoctrine()->getManager();
        $entities = $em->getRepository('AppOrderformBundle:PatientType')->findAll();

        if( $entities ) {
            return -1;
        }

        $types = array(
            'Adolescent Methodon',
            'Adult Methodon',
            'Cardiac Rehab',
            'Clinic Ambulatory',
            'Dental Clinic',
            'Dialysis',
            'Emergency Room',
            'Inpatient',
            'Institutional',
            'Lower Manhattan Hospital',
            'Manual Charge',
            'Non Patient',
            'Outpatient Hospital',
            'Outpatient Surgery',
            'Phys Hand Children',
            'PreAdmission Testing',
            'Pre-Admit',
            'Private Amb Lab',
            'Private Radiology',
            'QUALITY CONTROL',
            'Queens Center',
            'Radiation Therapy',
            'Rehab Medicine',
            'Research',
            'Single Visit',
            'Special Hematology'
        );

        $username = $this->get('security.token_storage')->getToken()->getUser();

        $count = 10;
        foreach( $types as $type ) {

            $accType = new PatientTypeList();
            $this->setDefaultList($accType,$count,$username,$type);

            $em->persist($accType);
            $em->flush();

            $count = $count + 10;

        } //foreach

        return round($count/10);
    }


    public function generateAccessionType() {

        $em = $this->getDoctrine()->getManager();
        $entities = $em->getRepository('AppOrderformBundle:AccessionType')->findAll();

        if( $entities ) {
            return -1;
        }

        $types = array(
            'NYH CoPath Anatomic Pathology Accession Number' => 'NYP CoPath',
            'De-Identified NYH Tissue Bank Research Specimen ID' => '',
            'De-Identified Personal Educational Slide Set Specimen ID' => '',
            'De-Identified Personal Research Project Specimen ID' => '',
            'California Tumor Registry Specimen ID' => '',
            'Specify Another Specimen ID Issuer' => '',
            'TMA Slide' => '',
            'Auto-generated Accession Number' => '',
            'Existing Auto-generated Accession Number' => '',
            'Deidentifier ID' => ''
        );

        $username = $this->get('security.token_storage')->getToken()->getUser();

        $count = 10;
        foreach( $types as $type => $abbreviation ) {

            $accType = new AccessionType();
            $this->setDefaultList($accType,$count,$username,$type);

            if( $type == "TMA Slide" ) {
                $accType->setType('TMA');
            }

            if( $abbreviation ) {
                $accType->setAbbreviation($abbreviation);
            }

            $em->persist($accType);
            $em->flush();

            $count = $count + 10;

        } //foreach

        return round($count/10);
    }


    public function generateEncounterType() {

        $em = $this->getDoctrine()->getManager();
        $entities = $em->getRepository('AppOrderformBundle:EncounterType')->findAll();

        if( $entities ) {
            return -1;
        }

        $types = array(
            'Auto-generated Encounter Number',
            'Existing Auto-generated Encounter Number'
        );

        $username = $this->get('security.token_storage')->getToken()->getUser();

        $count = 10;
        foreach( $types as $type ) {

            $encType = new EncounterType();
            $this->setDefaultList($encType,$count,$username,$type);

            $em->persist($encType);
            $em->flush();

            $count = $count + 10;

        } //foreach

        return round($count/10);
    }

    public function generateEncounterInfoType() {

        $em = $this->getDoctrine()->getManager();
        $entities = $em->getRepository('AppOrderformBundle:EncounterInfoTypeList')->findAll();

        if( $entities ) {
            return -1;
        }

        $types = array(
            'Call to Pathology',
        );

        $username = $this->get('security.token_storage')->getToken()->getUser();

        $count = 10;
        foreach( $types as $type ) {

            $encType = new EncounterInfoTypeList();
            $this->setDefaultList($encType,$count,$username,$type);

            $em->persist($encType);
            $em->flush();

            $count = $count + 10;

        } //foreach

        return round($count/10);
    }

    public function generateProcedureType() {

        $em = $this->getDoctrine()->getManager();
        $entities = $em->getRepository('AppOrderformBundle:ProcedureType')->findAll();

        if( $entities ) {
            return -1;
        }

        $types = array(
            'Auto-generated Procedure Number',
            'Existing Auto-generated Procedure Number'
        );

        $username = $this->get('security.token_storage')->getToken()->getUser();

        $count = 10;
        foreach( $types as $type ) {

            $encType = new ProcedureType();
            $this->setDefaultList($encType,$count,$username,$type);

            $em->persist($encType);
            $em->flush();

            $count = $count + 10;

        } //foreach

        return round($count/10);
    }



    public function generateOrderDelivery() {

        $username = $this->get('security.token_storage')->getToken()->getUser();

        $em = $this->getDoctrine()->getManager();
        $entities = $em->getRepository('AppOrderformBundle:OrderDelivery')->findAll();

        if( $entities ) {
            return -1;
        }

        $userutil = new UserUtil();
        $adminemail = $userutil->getSiteSetting($em,'siteEmail');

        $types = array(
            "I'll give slides to Melody - ST1015E (212) 746-2993",
            "I have given slides to Melody already",
            "I will drop the slides off at C-458A (212) 746-6406",
            "I have handed the slides to Liza already",
            "I will write S on the slide & submit as a consult",
            "I will write S4 on the slide & submit as a consult",
            "I will email ".$adminemail." about it",
            "Please e-mail me to set the time & pick up slides",
        );

        $count = 10;
        foreach( $types as $type ) {

            $listEntity = new OrderDelivery();
            $this->setDefaultList($listEntity,$count,$username,$type);

            $em->persist($listEntity);
            $em->flush();

            $count = $count + 10;
        }

        return round($count/10);
    }


    public function generateRegionToScan() {

        $username = $this->get('security.token_storage')->getToken()->getUser();

        $em = $this->getDoctrine()->getManager();
        $entities = $em->getRepository('AppOrderformBundle:RegionToScan')->findAll();

        if( $entities ) {
            return -1;
        }

        $types = array(
            "Entire Slide",
            "Any one of the levels",
            "Region circled by marker"
        );

        $count = 10;
        foreach( $types as $type ) {

            $listEntity = new RegionToScan();
            $this->setDefaultList($listEntity,$count,$username,$type);

            $em->persist($listEntity);
            $em->flush();

            $count = $count + 10;
        }

        return round($count/10);
    }


    public function generateProcessorComments() {

        $username = $this->get('security.token_storage')->getToken()->getUser();

        $em = $this->getDoctrine()->getManager();
        $entities = $em->getRepository('AppOrderformBundle:ProcessorComments')->findAll();

        if( $entities ) {
            return -1;
        }

        $types = array(
            "Slide(s) damaged and can not be scanned",
            "Slide(s) returned before being scanned",
            "Slide(s) could not be scanned due to focusing issues"
        );

        $count = 1;
        foreach( $types as $type ) {

            $listEntity = new ProcessorComments();
            $this->setDefaultList($listEntity,$count,$username,$type);

            $em->persist($listEntity);
            $em->flush();

            $count = $count + 10;
        }

        return round($count/10);
    }


    public function generateUrgency() {

        $username = $this->get('security.token_storage')->getToken()->getUser();

        $em = $this->getDoctrine()->getManager();
        $entities = $em->getRepository('AppOrderformBundle:Urgency')->findAll();

        if( $entities ) {
            return -1;
        }

        $types = array(
            'As soon as possible', 'Urgently (the patient is waiting in my office)'
        );

        $count = 10;
        foreach( $types as $type ) {

            $listEntity = new Urgency();
            $this->setDefaultList($listEntity,$count,$username,$type);

            $em->persist($listEntity);
            $em->flush();

            $count = $count + 10;
        }

        return round($count/10);
    }

    public function generateProgressCommentsEventType() {
        $username = $this->get('security.token_storage')->getToken()->getUser();

        $em = $this->getDoctrine()->getManager();
        $entities = $em->getRepository('AppOrderformBundle:ProgressCommentsEventTypeList')->findAll();

        if( $entities ) {
            return -1;
        }

        $types = array(
            'Initial Order Submission',
            'Amended Order Submission',
            'Auto-saved at the time of auto-logout',
            'Status Changed',
            'Data Reviewed',
            'Progress & Comments Viewed',
            'Comment Added',
            'Initial Slide Return Request Submission',
            'Slide Return Request Status Changed',
            'Slide Return Request Comment Added'
        );

        $count = 10;
        foreach( $types as $type ) {

            $listEntity = new ProgressCommentsEventTypeList();
            $this->setDefaultList($listEntity,$count,$username,$type);

            $em->persist($listEntity);
            $em->flush();

            $count = $count + 10;
        }

        return round($count/10);
    }


    public function generateMagnifications() {
        $username = $this->get('security.token_storage')->getToken()->getUser();

        $em = $this->getDoctrine()->getManager();
        $entities = $em->getRepository('AppOrderformBundle:Magnification')->findAll();

        if( $entities ) {
            return -1;
        }

        $types = array(
            '20X',
            '40X'
        );

        $count = 10;
        foreach( $types as $type ) {

            $listEntity = new Magnification();
            $this->setDefaultList($listEntity,$count,$username,$type);

            $em->persist($listEntity);
            $em->flush();

            $count = $count + 10;
        }

        return round($count/10);
    }

    //http://indicalab.com/products/ except Image Analysis Hyper-Cluster
    public function generateImageAnalysisAlgorithmList() {
        $username = $this->get('security.token_storage')->getToken()->getUser();

        $em = $this->getDoctrine()->getManager();
        $entities = $em->getRepository('AppOrderformBundle:ImageAnalysisAlgorithmList')->findAll();

        if( $entities ) {
            return -1;
        }

        $types = array(
            'Break-Apart & Fusion FISH',
            'Amplification & Deletion FISH',
            'Multiplex RNA FISH',
            'Fluorescent Object Colocalization',
            'Cell-Based Immunofluorescence',
            'Double Stain Cytoplasmic & Nuclear IHC',
            'Membrane IHC Quantification',
            'SISH & Dual CISH Quantification',
            'Chromogenic RNA ISH',
            'Steatosis Quantification',
            'Adipose Tissue Quantification',
            'Muscle Fiber Quantification',
            'Pancreatic Islet Quantification',
            'Micro-hemorrhage Counting',
            'Amyloid Plaque Counting',
            'Axon Quantification',
            'Lung Quantification',
            'Microglial Activation Quantification',
            'FREE ImageScope Eyedropper',
            'Photoreceptor Analysis',
            'HALO',
            'Muscle Fiber – Fluorescence',
            'Epidermal Layer Thickness',
            'Retina Layer Thickness',
            'Nucleoli Quantification',
            'Protein Foci Quantification',
            'Fluorescent Microvessel Quantification',
            'Fluorescent Membrane Quantification',
            'Retinal Vascular Quantification',
            'Tissue Microarray (TMA)',
            'Tissue Classification',
            'Glomeruli Podocyte Quantification',
            'Multiplex DNA FISH',
            'Circulating Tumor Cell (CTC)',
            'Brightfield Microvessel Quantification',
            'Fluorescent Islet Quantification',
            'Immune Cell Proximity',
            'Serial Section Analysis'
        );

        $count = 10;
        foreach( $types as $type ) {

            $listEntity = new ImageAnalysisAlgorithmList();
            $this->setDefaultList($listEntity,$count,$username,$type);

            $em->persist($listEntity);
            $em->flush();

            $count = $count + 10;
        }

        return round($count/10);
    }


    public function generateRace() {

        $username = $this->get('security.token_storage')->getToken()->getUser();

        $em = $this->getDoctrine()->getManager();
        $entities = $em->getRepository('AppOrderformBundle:RaceList')->findAll();

        if( $entities ) {
            return -1;
        }

        //http://nces.ed.gov/ipeds/reic/definitions.asp
        $types = array(
            'Hispanic or Latino',
            'American Indian or Alaska Native',
            'Asian',
            'Black or African American',
            'Native Hawaiian or Other Pacific Islander',
            'White'
        );

        $count = 10;
        foreach( $types as $type ) {

            $listEntity = new RaceList();
            $this->setDefaultList($listEntity,$count,$username,$type);

            $em->persist($listEntity);
            $em->flush();

            $count = $count + 10;
        }

        return round($count/10);
    }


    public function generateDiseaseTypeList() {

        $username = $this->get('security.token_storage')->getToken()->getUser();

        $em = $this->getDoctrine()->getManager();
        $entities = $em->getRepository('AppOrderformBundle:DiseaseTypeList')->findAll();

        if( $entities ) {
            return -1;
        }

        $types = array(
            'Neoplastic',
            'Non-Neoplastic',
            'None',
            'Unspecified'
        );

        $count = 10;
        foreach( $types as $type ) {

            $listEntity = new DiseaseTypeList();
            $this->setDefaultList($listEntity,$count,$username,$type);

            $em->persist($listEntity);
            $em->flush();

            $count = $count + 10;
        }

        return round($count/10);
    }

    public function generateDiseaseOriginList() {

        $username = $this->get('security.token_storage')->getToken()->getUser();

        $em = $this->getDoctrine()->getManager();
        $entities = $em->getRepository('AppOrderformBundle:DiseaseOriginList')->findAll();

        if( $entities ) {
            return -1;
        }

        $types = array(
            'Primary',
            'Metastatic',
            'Unspecified'
        );

        $count = 10;
        foreach( $types as $type ) {

            $listEntity = new DiseaseOriginList();
            $this->setDefaultList($listEntity,$count,$username,$type);

            $em->persist($listEntity);
            $em->flush();

            $count = $count + 10;
        }

        return round($count/10);
    }


    public function generateEmbedderInstructionList() {

        $username = $this->get('security.token_storage')->getToken()->getUser();

        $em = $this->getDoctrine()->getManager();
        $entities = $em->getRepository('AppOrderformBundle:EmbedderInstructionList')->findAll();

        if( $entities ) {
            return -1;
        }

        $types = array(
            'On Edge',
            'En Face'
        );

        $count = 10;
        foreach( $types as $type ) {

            $listEntity = new EmbedderInstructionList();
            $this->setDefaultList($listEntity,$count,$username,$type);

            $em->persist($listEntity);
            $em->flush();

            $count = $count + 10;
        }

        return round($count/10);
    }

    public function generateResearchGroupType() {

        $em = $this->getDoctrine()->getManager();
        $entities = $em->getRepository('AppOrderformBundle:ResearchGroupType')->findAll();

        if( $entities ) {
            return -1;
        }

        $elements = array(
            'Research Project Title' => 0,
            'Research Set Title' => 1,
        );

        $username = $this->get('security.token_storage')->getToken()->getUser();

        $count = 10;
        foreach( $elements as $name=>$level ) {

            $entity = new ResearchGroupType();
            $this->setDefaultList($entity,$count,$username,$name);

            $entity->setLevel($level);

            $em->persist($entity);
            $em->flush();

            $count = $count + 10;

        } //foreach

        return round($count/10);

    }


    public function generateCourseGroupType() {

        $em = $this->getDoctrine()->getManager();
        $entities = $em->getRepository('AppOrderformBundle:CourseGroupType')->findAll();

        if( $entities ) {
            return -1;
        }

        $elements = array(
            'Course Title' => 0,
            'Lesson Title' => 1,
        );

        $username = $this->get('security.token_storage')->getToken()->getUser();

        $count = 10;
        foreach( $elements as $name=>$level ) {

            $entity = new CourseGroupType();
            $this->setDefaultList($entity,$count,$username,$name);

            $entity->setLevel($level);

            $em->persist($entity);
            $em->flush();

            $count = $count + 10;

        } //foreach

        return round($count/10);

    }

    public function generateAmendmentReason() {

        $em = $this->getDoctrine()->getManager();
        $entities = $em->getRepository('AppOrderformBundle:AmendmentReasonList')->findAll();

        if( $entities ) {
            return -1;
        }

        $elements = array(
            "Error Corrected",
            "Typo Corrected",
            "Information Added"
        );

        $username = $this->get('security.token_storage')->getToken()->getUser();

        $count = 10;
        foreach( $elements as $name ) {

            $entity = new AmendmentReasonList();
            $this->setDefaultList($entity,$count,$username,$name);

            $em->persist($entity);
            $em->flush();

            $count = $count + 10;

        } //foreach

        return round($count/10);

    }

    public function generateEncounterStatus() {

        $em = $this->getDoctrine()->getManager();
        $entities = $em->getRepository('AppOrderformBundle:EncounterStatusList')->findAll();

        if( $entities ) {
            return -1;
        }

        $elements = array(
            "Open",
            "Closed",
            "Deleted"
        );

        $username = $this->get('security.token_storage')->getToken()->getUser();

        $count = 10;
        foreach( $elements as $name ) {

            $entity = new EncounterStatusList();
            $this->setDefaultList($entity,$count,$username,$name);

            $em->persist($entity);
            $em->flush();

            $count = $count + 10;

        } //foreach

        return round($count/10);

    }

    public function generatePatientRecordStatus() {

        $em = $this->getDoctrine()->getManager();
        $entities = $em->getRepository('AppOrderformBundle:PatientRecordStatusList')->findAll();

        if( $entities ) {
            return -1;
        }

        $elements = array(
            "Active" => "A",
            "Inactive" => "I",
            "Deleted" => "D"
        );

        $username = $this->get('security.token_storage')->getToken()->getUser();

        $count = 10;
        foreach( $elements as $name => $abbreviation ) {

            $entity = new PatientRecordStatusList();
            $this->setDefaultList($entity,$count,$username,$name);

            if( $abbreviation ) {
                $entity->setAbbreviation($abbreviation);
            }

            $em->persist($entity);
            $em->flush();

            $count = $count + 10;

        } //foreach

        return round($count/10);
    }

    public function generateMessageStatus() {

        $em = $this->getDoctrine()->getManager();

        $elements = array(
            "Draft",
            "Signed",
            "Deleted",
            "Signed, Amended",
            "Post-signature Draft",
            "Post-amendment Draft",
            "Post-deletion Draft"
        );

        $username = $this->get('security.token_storage')->getToken()->getUser();

        $count = 10;
        foreach( $elements as $name ) {

            $entity = $em->getRepository('AppOrderformBundle:MessageStatusList')->findOneByName($name);
            if( $entity ) {
                continue;
            }

            $entity = new MessageStatusList();
            $this->setDefaultList($entity,$count,$username,$name);

            $em->persist($entity);
            $em->flush();

            $count = $count + 10;

        } //foreach

        return round($count/10);

    }

    public function generateCalllogEntryTagsList() {

        $em = $this->getDoctrine()->getManager();

        $elements = array(
            "Sign out issue",
            "Educational",
            "Red Book"
        );

        $username = $this->get('security.token_storage')->getToken()->getUser();

        $count = 10;
        foreach( $elements as $name ) {

            $entity = $em->getRepository('AppOrderformBundle:CalllogEntryTagsList')->findOneByName($name);
            if( $entity ) {
                continue;
            }

            $entity = new CalllogEntryTagsList();
            $this->setDefaultList($entity,$count,$username,$name);

            $em->persist($entity);
            $em->flush();

            $count = $count + 10;

        } //foreach

        return round($count/10);

    }

//    public function generateCrnEntryTagsList() {
//
//        $em = $this->getDoctrine()->getManager();
//
//        $elements = array(
////            "Sign out issue",
////            "Educational",
////            "Red Book"
//        );
//
//        $username = $this->get('security.token_storage')->getToken()->getUser();
//
//        $count = 10;
//        foreach( $elements as $name ) {
//
//            $entity = $em->getRepository('AppOrderformBundle:CrnEntryTagsList')->findOneByName($name);
//            if( $entity ) {
//                continue;
//            }
//
//            $entity = new CrnEntryTagsList();
//            $this->setDefaultList($entity,$count,$username,$name);
//
//            $em->persist($entity);
//            $em->flush();
//
//            $count = $count + 10;
//
//        } //foreach
//
//        return round($count/10);
//
//    }

    public function generateCalllogAttachmentTypeList() {

        $em = $this->getDoctrine()->getManager();

        $elements = array(
            "Image",
            "Document",
            "Clinical Guideline",
            "Research Publication",
            "Multiple Attachment Types"
        );

        $username = $this->get('security.token_storage')->getToken()->getUser();

        $count = 10;
        foreach( $elements as $name ) {

            $entity = $em->getRepository('AppOrderformBundle:CalllogAttachmentTypeList')->findOneByName($name);
            if( $entity ) {
                continue;
            }

            $entity = new CalllogAttachmentTypeList();
            $this->setDefaultList($entity,$count,$username,$name);

            $em->persist($entity);
            $em->flush();

            $count = $count + 10;

        } //foreach

        return round($count/10);

    }

    public function generateCalllogTaskTypeList() {

        $em = $this->getDoctrine()->getManager();

        $elements = array(
            "Contact Healthcare Provider",
            "Order a medication",
            "Order blood products",
            "Check lab results",
            "Other"
        );

        $username = $this->get('security.token_storage')->getToken()->getUser();

        $count = 10;
        foreach( $elements as $name ) {

            $entity = $em->getRepository('AppOrderformBundle:CalllogTaskTypeList')->findOneByName($name);
            if( $entity ) {
                continue;
            }

            $entity = new CalllogTaskTypeList();
            $this->setDefaultList($entity,$count,$username,$name);

            $em->persist($entity);
            $em->flush();

            $count = $count + 10;

        } //foreach

        return round($count/10);

    }

    public function generatePatientListHierarchyGroupType() {

        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('AppOrderformBundle:PatientListHierarchyGroupType')->findAll();
        if( $entities ) {
            return -1;
        }

        $elements = array(
            0 => "Patient List",
            1 => "Patient List",
            2 => "Patient List",
            3 => "Patient List",
            4 => "Patient",
        );

        $username = $this->get('security.token_storage')->getToken()->getUser();

        $count = 10;
        foreach( $elements as $level=>$name ) {

//            $entity = $em->getRepository('AppOrderformBundle:PatientListHierarchyGroupType')->findOneByName($name);
//            if( $entity ) {
//                continue;
//            }

            $entity = new PatientListHierarchyGroupType();
            $this->setDefaultList($entity,$count,$username,$name);

            $entity->setLevel($level);

            //echo "creating PatientListHierarchyGroupType: name=".$entity->getName()."; level=".$entity->getLevel()."<br>";
            $em->persist($entity);
            $em->flush();

            $count = $count + 10;

        } //foreach

        return round($count/10);

    }

    public function generatePatientListHierarchy() {

        $em = $this->getDoctrine()->getManager();
        $username = $this->get('security.token_storage')->getToken()->getUser();

        //$levelGroup = $em->getRepository('AppOrderformBundle:PatientListHierarchyGroupType')->findOneByName('Patient List');

        $items = array(
            "Patient Lists",                    //level 0
            "Weill Cornell",                    //level 1
            "Pathology Call Log Book Lists",    //level 2
            "Pathology Call Complex Patients"   //level 3
        );

        $count = 10;
        $level = 0;
        $parentItem = null;

        //$count = $this->addNestedsetPatientListHierarchy(null,$items,$level,$username,$count);

        foreach( $items as $name ) {

            if( $parentItem ) {
                $mapper = array(
                    'prefix' => "App",
                    'className' => "PatientListHierarchy",
                    'bundleName' => "OrderformBundle"
                );
                $item = $em->getRepository('AppOrderformBundle:PatientListHierarchy')->findByChildnameAndParent($name,$parentItem,$mapper);
            } else {
                $item = $em->getRepository('AppOrderformBundle:PatientListHierarchy')->findOneByName($name);
            }

            if( $item ) {
                continue;
            }

            //make category
            $item = new PatientListHierarchy();

            $this->setDefaultList($item,$count,$username,$name);
            $item->setLevel($level);

            //find org group level
            $levelGroup = $em->getRepository('AppOrderformBundle:PatientListHierarchyGroupType')->findOneByLevel($level);
            if( !$levelGroup ) {
                exit("PatientListHierarchyGroupType not found by level ".$level);
            }

            $item->setOrganizationalGroupType($levelGroup);

            $level++;
            $count = $count + 10;

            //add to parent
            if( $parentItem ) {
                $em->persist($parentItem);
                $parentItem->addChild($item);
            }

            $parentItem = $item;

            //$item->printTree();

            $em->persist($item);
            $em->flush();
        }
        //exit('EOF message category');

        return round($count/10);
    }
//    public function addNestedsetPatientListHierarchy($parentItem,$items,$level,$username,$count) {
//
//        $em = $this->getDoctrine()->getManager();
//
//        foreach( $items as $category ) { //=>$subcategory
//
//            $name = $category['name'];
//
////            if( $subcategory && !is_array($subcategory) ) {
////                $name = $subcategory;
////            }
//
//            //find by name and by parent ($parentItem) if exists
//            if( $parentItem ) {
//                $mapper = array(
//                    'prefix' => "App",
//                    'className' => "PatientListHierarchy",
//                    'bundleName' => "OrderformBundle"
//                );
//                $item = $em->getRepository('AppOrderformBundle:PatientListHierarchy')->findByChildnameAndParent($name,$parentItem,$mapper);
//            } else {
//                $item = $em->getRepository('AppOrderformBundle:PatientListHierarchy')->findOneByName($name);
//            }
//
//            if( !$item ) {
//                //make category
//                $item = new PatientListHierarchy();
//
//                $this->setDefaultList($item,$count,$username,$name);
//                $item->setLevel($level);
//
//                $count = $count + 10;
//            }
//
//            if( !$item->getEntityNamespace() && !$item->getEntityName() ) {
//                if( $category['entityNamespace'] ) {
//                    $item->setEntityNamespace($category['entityNamespace']);
//                }
//                if( $category['entityName'] ) {
//                    $item->setEntityName($category['entityName']);
//                }
//            }
//
////            echo $level.": category=".$name.", count=".$count."<br>";
////            echo "subcategory:<br>";
////            print_r($subcategory);
////            echo "<br><br>";
////            echo "messageCategory=".$item->getName()."<br>";
//
//            //add to parent
//            if( $parentItem ) {
//                $em->persist($parentItem);
//                $parentItem->addChild($item);
//            }
//
//            //$item->printTree();
//
//            //make children
//            //if( $subcategory && is_array($subcategory) && count($subcategory) > 0 ) {
//            //    $count = $this->addNestedsetPatientListHierarchy($item,$subcategory,$level+1,$username,$count);
//            //}
//
//            $em->persist($item);
//            $em->flush();
//        }
//
//        return $count;
//    }

    ////////////////// Scan Tree Util //////////////////////
    //to initialize JS, add "getJstree('OrderformBundle','MessageCategory');" to formReady.js
    /**
     * @Route("/list/research-project-titles-tree/", name="scan_tree_researchprojecttitles_list", methods={"GET"})
     * @Route("/list/educational-course-titles-tree/", name="scan_tree_educationalcoursetitles_list", methods={"GET"})
     * @Route("/list/message-categories-tree/", name="scan_tree_messagecategories_list", methods={"GET"})
     * @Route("/list/patient-lists-tree/", name="scan_tree_patientlisthierarchy_list", methods={"GET"})
     */
    public function institutionTreeAction(Request $request)
    {
        if( false === $this->get('security.authorization_checker')->isGranted('ROLE_SCANORDER_PROCESSOR') ) {
            return $this->redirect( $this->generateUrl($this->getParameter('scan.sitename').'-order-nopermission') );
        }

        return $this->compositeTree($request,$this->getParameter('scan.sitename'));
    }

    public function getMapper($routeName) {

        $bundlePreffix = "App";
        $bundleName = "OrderformBundle";
        $className = null;
        $title = null;
        $nodeshowpath = null;

        if( $routeName == "scan_tree_researchprojecttitles_list" ) {
            $bundleName = "OrderformBundle";
            $className = "ProjectTitleTree";
            $title = "Project Titles Tree Management";
            $nodeshowpath = "researchprojecttitles_show";
        }

        if( $routeName == "scan_tree_educationalcoursetitles_list" ) {
            $bundleName = "OrderformBundle";
            $className = "CourseTitleTree";
            $title = "Course Titles Tree Management";
            $nodeshowpath = "educationalcoursetitles_show";
        }

        if( $routeName == "scan_tree_messagecategories_list" ) {
            $bundleName = "OrderformBundle";
            $className = "MessageCategory";
            $title = "Message Categories Tree Management";
            $nodeshowpath = "messagecategorys_show";
        }

        if( $routeName == "scan_tree_patientlisthierarchy_list" ) {
            $bundleName = "OrderformBundle";
            $className = "PatientListHierarchy";
            $title = "Patient Lists Hierarchy Management";
            $nodeshowpath = "patientlisthierarchys_show";
        }

        if( $routeName == "scan_tree_patientlisthierarchy_list" ) {
            $bundleName = "OrderformBundle";
            $className = "PatientListHierarchy";
            $title = "Patient Lists Hierarchy Management";
            $nodeshowpath = "patientlisthierarchys_show";
        }

        $mapper = array(
            'bundlePreffix' => $bundlePreffix,
            'bundleName' => $bundleName,
            'className' => $className,
            'title' => $title,
            'nodeshowpath' => $nodeshowpath
        );

        return $mapper;
    }



    /**
     * replace the old prefix NOENCOUNTERIDPROVIDED with a new prefix AUTOGENERATEDENCOUNTERID
     * http://localhost/order/scan/admin/fix-autogenerated-id/
     *
     * @Route("/fix-autogenerated-id/", name="scan_admin_fix-autogenerated-id", methods={"GET"})
     */
    public function fixAutogeneratedIdAction()
    {

        //Encounter: old-NOENCOUNTERIDPROVIDED, new-AUTOGENERATEDENCOUNTERID
        $oldNumberId = "NOENCOUNTERIDPROVIDED";
        $newNumberId = "AUTOGENERATEDENCOUNTERID";
        $entityName = "EncounterNumber";
        $fieldName = "field";
        $this->findAndReplaceOldByNew($entityName,$fieldName,$oldNumberId,$newNumberId);

    }
    public function findAndReplaceOldByNew( $entityName, $fieldName, $oldNumberId, $newNumberId ) {
        $em = $this->getDoctrine()->getManager();
        //$encounterNumbers = $em->getRepository('AppOrderformBundle:EncounterNumber')->findOneByName("Auto-generated Encounter Number");
        $repository = $em->getRepository('AppOrderformBundle:'.$entityName);
        $dql = $repository->createQueryBuilder("numberid");
        //$dql->leftJoin("numberid.number", "number");
        $dql->where("numberid.".$fieldName." LIKE :oldNumberId");
        $parameters['oldNumberId'] = '%'.$oldNumberId.'%';

        $query = $em->createQuery($dql);
        $query->setParameters($parameters);
        //echo $mergeId.":sql=".$query->getSql()."<br>";
        $numberObjects = $query->getResult();

        $count = 0;

        foreach( $numberObjects as $numberObject ) {
            $field = $numberObject->getField();
            //$originalField = $numberObject->getOriginal();
            echo ($count+1).": field ".$field;

            //$fieldIndex = str_replace($oldNumberId."-","",$field);
            $fieldArr = explode("-",$field);

            if( count($fieldArr) != 2 ) {
                throw new \Exception( $field .' must have exactly 2 parts. Found '.count($fieldArr).' parts' );
                //continue;
            }

            $fieldIndex = $fieldArr[1];

            $newField = $newNumberId.'-'.$fieldIndex;
            echo " => ".$newField . "<br>";

            //save this field to DB
            $numberObject->setField($newField);
            //$em->persist($numberObject);
            $count++;
        }

        if( $count > 0 ) {
            $em->flush();
        }

        exit('Modified '.$count." fields.");
    }

}
