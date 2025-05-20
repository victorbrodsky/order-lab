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

//To initialize this bundle make sure:
//1) add a new source to SourceSystemList "Deidentifier"
//2) add a new AccessionType "Deidentifier ID"
//3) add new roles by running "Populate All Lists With Default Values" in user directory list manager
//4) add permission "Generate new Deidentifier ID" (Object:Accession, Action:create)
//5) add permission "Search by Deidentifier ID" (Object:Accession, Action:read)
//6) run "Synchronise DB with the source code changes" on the "List Manager" page to sync roles (assign site) and sync the code with EventTypeList in DB
//7) add permissions to ROLE_DEIDENTIFICATOR_WCM_NYP_ENQUIRER: Search by Deidentifier ID (WCMC,NYP)
//8) add permissions to ROLE_DEIDENTIFICATOR_WCM_NYP_GENERATOR: Generate new Deidentifier ID (WCMC,NYP)
//9) add permissions to ROLE_DEIDENTIFICATOR_WCM_NYP_HONEST_BROKER: Generate new Deidentifier ID (WCMC,NYP) and Search by Deidentifier ID (WCMC,NYP)


namespace App\DeidentifierBundle\Controller;



use App\OrderformBundle\Entity\AccessionType;
use App\TranslationalResearchBundle\Entity\Product;
use App\UserdirectoryBundle\Entity\Grant;
use App\UserdirectoryBundle\Entity\Institution; //process.py script: replaced namespace by ::class: added use line for classname=Institution
use App\DeidentifierBundle\Form\DeidentifierSearchType;
use App\OrderformBundle\Entity\Accession;
use App\OrderformBundle\Entity\AccessionAccession;
use App\UserdirectoryBundle\Entity\AccessRequest;
use App\UserdirectoryBundle\Controller\OrderAbstractController;


use App\UserdirectoryBundle\Entity\SiteList;
use App\UserdirectoryBundle\Entity\User;
use Symfony\Bridge\Twig\Attribute\Template;
//use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class DefaultController extends OrderAbstractController
{

//    public function __construct(private \Doctrine\Persistence\ManagerRegistry $managerRegistry) {
    //
    //    }
    //[Template('AppUserdirectoryBundle/Default/about.html.twig')]
    #[Route(path: '/about', name: 'deidentifier_about_page')]
    #[Template('AppUserdirectoryBundle/Default/about.html.twig')]
    public function aboutAction( Request $request ) {

//        //restart DB
//        $userServiceUtil = $this->container->get('user_service_utility');
//        $res = $userServiceUtil->restartDb();
//        echo "res=$res <br>";
//
//        $postgreVersionStr = $userServiceUtil->getDBVersionStr(); //postgresql-14
//        $dbRestartCommand = "sudo systemctl stop $postgreVersionStr";
//        $res = $userServiceUtil->runCommandByPython( $dbRestartCommand );
//        echo"stop res=$res <br>";
//
//        //test
//        $userSecUtil = $this->container->get('user_security_utility');
//        $testParam = $userSecUtil->getSiteSettingParameter('connectionChannel');
//        echo"restore DBWrapper: testParam=$testParam <br>";
//
//        exit('111');

//        $postgreVersionStr = $userServiceUtil->getDBVersionStr(); //postgresql-14
//        $dbRestartCommand = "sudo stop restart $postgreVersionStr";
//        echo "dbRestartCommand=$dbRestartCommand <br>";
//
//        $projectRoot = $this->container->get('kernel')->getProjectDir();
//        $projectRoot = str_replace('order-lab', '', $projectRoot);
//        $parentRoot = str_replace('orderflex', '', $projectRoot);
//        $parentRoot = str_replace(DIRECTORY_SEPARATOR . DIRECTORY_SEPARATOR, '', $parentRoot);
//
//        $managePackagePath = $parentRoot .
//            DIRECTORY_SEPARATOR . "order-lab" .
//            DIRECTORY_SEPARATOR . "utils" .
//            DIRECTORY_SEPARATOR . "db-manage" .
//            DIRECTORY_SEPARATOR . "postgres-manage-python";
//        echo "managePackagePath=$managePackagePath <br>";
//
//        $pythonScriptPath = $managePackagePath . DIRECTORY_SEPARATOR . "restart_db.py";
//
//        $command = "python '$pythonScriptPath' --command  '$dbRestartCommand'";
//        echo "command=$command <br>";
//
//        //$res = $userServiceUtil->runProcess($dbRestartCommand);
//        $res = $this->runProcess($command);
//        exit('res='.$res);

//        $userServiceUtil = $this->container->get('user_service_utility');
//        $ver = $userServiceUtil->getDBVersionStr();
//        exit('ver='.$ver);
//
//        //Testing IDENTITY comparing to AUTO
//        $em = $this->getDoctrine()->getManager();
//        //$em = $this->managerRegistry->getManager();
//        $user = $this->getUser();
//
//        $product = new Product($user);
//        $em->persist($product);
//        echo "product id (IDENTITY)=".$product->getId()."<br>";
//
//        $site = new SiteList($user);
//        $em->persist($site);
//        echo "site id (Auto)=".$site->getId()."<br>";
//
//        $grant = new Grant($user);
//        //$em->getRepository(Grant::class)->testGrant();
//        $em->persist($grant);
//        echo "grant id (Auto)=".$grant->getId()."<br>";

        //return $this->redirect( $this->generateUrl('deidentifier-nopermission') );
        //return $this->redirectToRoute('deidentifier-nopermission');
        //exit('111');

//        $userSecUtil = $this->container->get('user_security_utility');
//        $em = $this->getDoctrine()->getManager();
//        $user = $em->getRepository(User::class)->find(582);
//        $info = $userSecUtil->getHeadInfo($user);
//        dump($info);
//        exit('111');

//        $logger = $this->container->get('logger');
//        $demoDbUtil = $this->container->get('demodb_utility');
//        //Verify DB
//        $verifyRes = $demoDbUtil->verifyDemoDb();
//        if( $verifyRes ) {
//            $verifyMsg = 'Some of the demos were not generated: ' . $verifyRes;
//            echo $verifyMsg."<br>";
//            $logger->error($verifyMsg);
//            $userSecUtil = $this->container->get('user_security_utility');
//            $userSecUtil->sendEmailToSystemEmail("DB Demos Error", $verifyMsg);
//        }
//        exit('111');

        return array('sitename'=>$this->getParameter('deidentifier.sitename'));
    }

    public function runProcess($script) {
        //$process = new Process($script);
        $process = Process::fromShellCommandline($script);
        //$process->setTimeout(1800); //sec; 1800 sec => 30 min
        $process->setTimeout(7200); //7200 sec => 2 hours
        $process->run();
        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }
        return $process->getOutput();
    }

    #[Route(path: '/navbar/{accessionTypeStr}/{accessionTypeId}/{accessionNumber}', name: 'deidentifier_navbar', methods: ['GET'])]
    #[Template('AppDeidentifierBundle/Default/navbar.html.twig')]
    //[Template("AppDeidentifierBundle/Default/navbar.html.twig")]
    public function deidentifierNavbarAction( Request $request, $accessionTypeStr, $accessionTypeId, $accessionNumber ) {

        if( false == $this->isGranted('ROLE_DEIDENTIFICATOR_USER') ) {
            return $this->redirect( $this->generateUrl('deidentifier-nopermission') );
        }

        $em = $this->getDoctrine()->getManager();
        //process.py script: replaced namespace by ::class: ['AppOrderformBundle:AccessionType'] by [AccessionType::class]
        $accessionTypes = $em->getRepository(AccessionType::class)->findBy( array('type'=>array('default','user-added')) );


        $accessionTypeStr = trim((string)$accessionTypeStr);
        $accessionTypeId = trim((string)$accessionTypeId);
        $accessionNumber = trim((string)$accessionNumber);

        //echo "accessionNumber=".$accessionNumber."<br>";
        //echo "accessionTypeStr=".$accessionTypeStr."<br>";
        //echo "accessionTypeId=".$accessionTypeId."<br>";

//        if( $accessionTypeId ) {
//            $accessionTypeObj = $em->getRepository('AppOrderformBundle:AccessionType')->find($accessionTypeId);
//        }

        return array(
            'accessiontypes' => $accessionTypes,
            'accessionTypeId' => $accessionTypeId,
            'accessionTypeStr' => $accessionTypeStr,    //$accessionTypeObj."",
            'accessionNumber' => $accessionNumber,
        );
    }

    #[Route(path: '/', name: 'deidentifier_home', methods: ['GET'])]
    #[Template('AppDeidentifierBundle/Default/index.html.twig')]
    //[Template("AppDeidentifierBundle/Default/index.html.twig")]
    public function indexAction( Request $request ) {

        if( false == $this->isGranted('ROLE_DEIDENTIFICATOR_USER') ){
            //exit('deidentifier: no permission');
            return $this->redirect( $this->generateUrl('deidentifier-nopermission') );
        }

//        //email testing
//        $emailUtil = $this->container->get('user_mailer_utility');
//        $emailUtil->sendEmail( 'oli2002@med.cornell.edu', "Test email !!!", "Test email body !!!", "oli2002@med.cornell.edu,cinava@yahoo.com" );

        //check for active access requests
        $accessreqs = $this->getActiveAccessReq();
        //echo "accessreq count=".count($accessreqs)."<br>";
        $accessreqsCount = 0;
        if( is_array($accessreqs) ) {
            $accessreqsCount = count($accessreqs);
        }

        $form = $this->createGenerateForm();

        //$accessionTypes = $em->getRepository('AppOrderformBundle:AccessionType')->findBy( array('type'=>array('default','user-added')) );

        return array(
            //'accessiontypes' => $accessionTypes,
            'accessreqs' => $accessreqsCount,
            'form' => $form->createView(),
            //'msg' => "test test test test"
        );
    }

    public function createGenerateForm() {
        $securityUtil = $this->container->get('user_security_utility');
        $userSecUtil = $this->container->get('user_security_utility');
        $user = $this->getUser();
        //permittedInstitutions for generation
        //echo "user=".$user."<br>";
        $userSiteSettings = $securityUtil->getUserPerSiteSettings($user);
        if( $userSiteSettings ) {
            $permittedInstitutions = $userSiteSettings->getPermittedInstitutionalPHIScope();
            $orderUtil = $this->container->get('scanorder_utility');
            $permittedInstitutions = $orderUtil->getAllScopeInstitutions($permittedInstitutions, null);

            //set default "WCM-NYP Collaboration" as institution
            $defaultInstitution = null;;
            foreach ($permittedInstitutions as $permittedInstitution) {
                //echo "permittedInstitution=".$permittedInstitution."<br>";
                if ($permittedInstitution->getName() == "WCM-NYP Collaboration") {
                    $defaultInstitution = $permittedInstitution;
                    break;
                }
            }
        } else {
            $permittedInstitutions = array();
            $defaultInstitution = null;
        }

        $defaultAccessionType = $userSecUtil->getSiteSettingParameter('defaultDeidentifierAccessionType');

        $params = array(
            'permittedInstitutions' => $permittedInstitutions,
            'defaultInstitution' => $defaultInstitution,
            'defaultAccessionType' => $defaultAccessionType
        );

        //search box
        $form = $this->createForm(DeidentifierSearchType::class, null, array('form_custom_value'=>$params));

        return $form;
    }

//    public function getAccessionTypesAction() {
    //        $em = $this->getDoctrine()->getManager();
    //        $accessionTypes = $em->getRepository('AppOrderformBundle:AccessionType')->findBy( array('type'=>array('default','user-added')) );
    //
    //        $accessionTypeArr = array();
    //        foreach( $accessionTypes as $accessionType) {
    //            $accessionTypeObject = array('id'=>$accessionType->getId(),'text'=>$accessionType."");
    //            $accessionTypeArr[] = $accessionTypeObject;
    //        }
    //
    //        //return $accessionTypes;
    //
    //        $response = new Response();
    //        $response->setContent($accessionTypeArr);
    //        return $response;
    //    }
    /**
     * Search for Accession Number
     */
    #[Route(path: '/re-identify/', name: 'deidentifier_search', methods: ['GET'])]
    #[Template('AppDeidentifierBundle/Search/search.html.twig')]
    //[Template("AppDeidentifierBundle/Search/search.html.twig")]
    public function searchAction( Request $request ) {

//        if( false == $this->isGranted('ROLE_DEIDENTIFICATOR_ENQUIRER') ){
//            return $this->redirect( $this->generateUrl('deidentifier-nopermission') );
//        }
        if( false == $this->isGranted("read", "Accession") ){
            //exit('nopermission');
            return $this->redirect( $this->generateUrl('deidentifier-nopermission') );
        }

        $em = $this->getDoctrine()->getManager();

        $user = $this->getUser();

        //get search string
        $accessionNumber = $request->query->get('accessionNumber');
        $accessionType = $request->query->get('accessionType');

        $accessionNumber = trim((string)$accessionNumber);
        $accessionType = trim((string)$accessionType);

        //echo "accessionNumber=".$accessionNumber."<br>";
        //echo "accessionType=".$accessionType."<br>";

        $error = null;
        $pagination = null;

        //Search across all institutions that are listed in PHI Scope of the user by default
        $securityUtil = $this->container->get('user_security_utility');
        $userSiteSettings = $securityUtil->getUserPerSiteSettings($user);
        $permittedInstitutions = $userSiteSettings->getPermittedInstitutionalPHIScope();
        $orderUtil = $this->container->get('scanorder_utility');
        $permittedInstitutions = $orderUtil->getAllScopeInstitutions($permittedInstitutions,null);
        $institutionIds = array();
        foreach( $permittedInstitutions as $permittedInstitution ) {
            $institutionIds[] = $permittedInstitution->getId();
        }
        $query = $this->getAccessionQuery($accessionType,$accessionNumber,$institutionIds,$request);
        //echo "sql=".$query->getSql()."<br>";

        //$objectsFound = $query->getResult(); //accessions
        //echo "pagination count=" . count($pagination) . "<br>";
        //exit();

        if( $query ) {

            $limit = 20;
            $paginator  = $this->container->get('knp_paginator');
            $pagination = $paginator->paginate(
                $query,
                $request->query->get('page', 1),   /*page number*/
                $limit,                            /*limit per page*/
                array('defaultSortFieldName' => 'accessionAccession.id', 'defaultSortDirection' => 'asc', 'wrap-queries'=>true)
            );

            //echo "pagination count=" . count($pagination) . "<br>";
        }

        //$accessionTypes = $em->getRepository('AppOrderformBundle:AccessionType')->findBy( array('type'=>array('default','user-added')) );

        //process.py script: replaced namespace by ::class: ['AppOrderformBundle:AccessionType'] by [AccessionType::class]
        $accessionTypeObj = $em->getRepository(AccessionType::class)->find($accessionType);

        //Event Log
        $event = "Deidentifier Search with Accession Type " . $accessionTypeObj ." and  Accession Number " . $accessionNumber;
        $userSecUtil = $this->container->get('user_security_utility');
        $userSecUtil->createUserEditEvent($this->getParameter('deidentifier.sitename'),$event,$user,null,$request,'Search by Deidentifier ID conducted');

        return array(
            'accessionTypeId' => $accessionType,
            'accessionTypeStr' => $accessionTypeObj."",
            'accessionNumber' => $accessionNumber,
            //'accessiontypes' => $accessionTypes,
            'pagination' => $pagination //accessions
        );
    }

    public function searchAccession($accessionTypeId,$accessionNumber,$institutions,$single=false) {
        $em = $this->getDoctrine()->getManager();

        $extra = array();
        $extra["keytype"] = $accessionTypeId;

        $validity = array('valid','deidentifier-valid','deidentifier');

        //$institutions = array();
        //$institutions[] = $inst->getId();

        //findOneByIdJoinedToField already include collaboration based on the provided permitted $institutions
        //process.py script: replaced namespace by ::class: ['AppOrderformBundle:Accession'] by [Accession::class]
        $accessions = $em->getRepository(Accession::class)->findOneByIdJoinedToField($institutions,$accessionNumber,"Accession","accession",$validity,$single,$extra);

        return $accessions;
    }





    #[Route(path: '/generate/', name: 'deidentifier_generate', methods: ['GET'])]
    #[Template('AppDeidentifierBundle/Default/index.html.twig')]
    //[Template("AppDeidentifierBundle/Default/index.html.twig")]
    public function generateAction( Request $request ) {

//        if( false == $this->isGranted('ROLE_DEIDENTIFICATOR_GENERATOR') ){
//            return $this->redirect( $this->generateUrl('deidentifier-nopermission') );
//        }
        if( false == $this->isGranted("create", "Accession") ){
            return $this->redirect( $this->generateUrl('deidentifier-nopermission') );
        }

        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();

        //get search string
        $institution = $request->query->get('institution');
        $accessionNumber = $request->query->get('accessionNumber');
        $accessionTypeId = $request->query->get('accessionType');

        $institution = trim((string)$institution);
        $accessionNumber = trim((string)$accessionNumber);
        $accessionTypeId = trim((string)$accessionTypeId);

        //echo "institution=".$institution."<br>";
        //echo "accessionNumber=(".$accessionNumber.")<br>";
        //echo "accessionType=".$accessionTypeId."<br>";
        //exit();

        //process.py script: replaced namespace by ::class: ['AppOrderformBundle:AccessionType'] by [AccessionType::class]
        $accessionTypeObj = $em->getRepository(AccessionType::class)->find($accessionTypeId);

        if( !$accessionNumber ) {
            $this->addFlash(
                'warning',
                'Accession Number is not provided.'
            );
            return $this->redirect( $this->generateUrl('deidentifier_home') );
        }

//        if(
//            $accessionTypeObj->getName() != "Deidentifier ID" &&
//            strpos((string)$accessionTypeObj->getName(),'De-Identified') === false
//        )
         if( strpos((string)$accessionTypeObj->getName(),'CoPath Anatomic Pathology Accession Number') !== false ) {
            //check if accession number is not empty containing only something like "-"
            //echo "accessionNumber=".$accessionNumber."<br>";
            //$accessMaskValid = preg_match('/[A-Za-z]{2,}[1-9]{2,}-[1-9]/',$accessionNumber);
            $accessMaskValid = preg_match('/^[A-Za-z]{1,2}[1-9][0-9]{0,1}-[1-9][0-9]{0,5}$/', $accessionNumber);
            if (!$accessMaskValid) {
                //exit('mask invalid');
                $msg = "Valid accession numbers must start with up to two letters followed by two digits, then followed by up to six digits with no leading zeros (e.g. SC14-231956).";
                $this->addFlash(
                    'warning',
                    'Accession Number is not valid. ' . $msg
                );
                return $this->redirect($this->generateUrl('deidentifier_home'));
            }
        }
        //exit('mask is valid');

        $accessionNumberClean = preg_replace('/\s+/', '', $accessionNumber);
        $accessionNumberClean = preg_replace('/-/', '', $accessionNumberClean);
        if( !$accessionNumberClean ) {
            $this->addFlash(
                'warning',
                'Accession Number is empty.'
            );
            return $this->redirect( $this->generateUrl('deidentifier_home') );
        }

        if( !$accessionTypeId ) {
            $this->addFlash(
                'warning',
                'Accession Type is not provided.'
            );
            return $this->redirect( $this->generateUrl('deidentifier_home') );
        }

        if( !$institution ) {
            $this->addFlash(
                'warning',
                'Institution is not provided.'
            );
            return $this->redirect( $this->generateUrl('deidentifier_home') );
        }

        $institutions = array($institution);

        $accession = $this->searchAccession($accessionTypeId,$accessionNumber,$institutions,true);

        $msg = '';
        if( !$accession ) {
            $accession = $this->createNewAccession($accessionTypeId,$accessionNumber,$institution);
            $msg = 'New generated Accession <strong>' . $accession->obtainFullObjectName() . '</strong>';
        }

        $accessionId = $accession->getId();

        //echo "generateAction: accessionId=".$accessionId."<br>";

        //get a new deidentifier number

        $deidentifier = $this->getNewDeidentificator($accessionId);
        //exit('new deidentifier='.$deidentifier);

        $accession = $this->addNewDeidentifier($accessionId,$deidentifier);

        $msg = '<strong>' . $deidentifier . '</strong>' . ' generated for ' . $accession->obtainFullValidKeyName() . '<br>' . $msg;

//        $this->addFlash(
//            'notice',
//            $msg
//        );

        //$pathParams = $this->getPathParams($accession);
        //return $this->redirect( $this->generateUrl('deidentifier_home',$pathParams) );

        $form = $this->createGenerateForm();
        //$accessionTypes = $em->getRepository('AppOrderformBundle:AccessionType')->findBy( array('type'=>array('default','user-added')) );

        //Event Log
        //process.py script: replaced namespace by ::class: ['AppOrderformBundle:AccessionType'] by [AccessionType::class]
        $accessionTypeObj = $em->getRepository(AccessionType::class)->find($accessionTypeId);
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:Institution'] by [Institution::class]
        $institutionObj = $em->getRepository(Institution::class)->find($institution);
        //$event = "Deidentifier Generate with Accession Type " . $accessionTypeObj .",  Accession Number " . $accessionNumber . " and Institution " . $institutionObj;
        $event = "Deidentifier ID ".$deidentifier." generated for ".$accessionTypeObj." ".$accessionNumber." (Institution: ".$institutionObj.")";
        $userSecUtil = $this->container->get('user_security_utility');
        $userSecUtil->createUserEditEvent($this->getParameter('deidentifier.sitename'),$event,$user,$accession,$request,'Generate Accession Deidentifier ID');

        //check for active access requests
        //$accessreqs = $this->getActiveAccessReq();

        return array(
            //'permittedInstitutions' => $permittedInstitutions,
            //'accessiontypes' => $accessionTypes,
            //'accessreqs' => count($accessreqs),
            'form' => $form->createView(),
            'msg' => $msg,
            'institutionGen' => $institution,
            'accessionNumberGen' => $accessionNumber,
            'accessionTypeGen' => $accessionTypeId,

            //'pagination' => $pagination //accessions
        );
    }

    private function createNewAccession($accessionType,$accessionNumber,$institution) {

        if( !$accessionNumber ) {
            throw $this->createNotFoundException("Generate a new deidentifier: No accession number is provided. accessionNumber=".$accessionNumber);
        }

        if( !$accessionType ) {
            throw $this->createNotFoundException("Generate a new deidentifier: No accession type is provided. accessionType=".$accessionType);
        }

        if( !$institution ) {
            throw $this->createNotFoundException("Generate a new deidentifier: No institution is provided. institution=".$institution);
        }

        $em = $this->getDoctrine()->getManager();

        $user = $this->getUser();

        //parameters
        $extra = array();
        $extra["keytype"] = $accessionType;

        //create a new accession object
        //$status, $provider, $className, $fieldName, $parent = null, $fieldValue = null, $extra = null, $withfields = true, $flush=true
        //process.py script: replaced namespace by ::class: ['AppOrderformBundle:Accession'] by [Accession::class]
        $accession = $em->getRepository(Accession::class)->createElement(
            $institution,       //institution
            "valid",            //status. if null => STATUS_RESERVED
            $user,              //provider
            "Accession",        //$className
            "accession",        //$fieldName
            null,               //$parent
            $accessionNumber,   //$fieldValue
            $extra,             //$extra
            false               //$withfields
        );

        if( !$accession ) {
            throw $this->createNotFoundException('Unable to create a new Accession with Accession Number='.$accessionNumber);
        }

        //set source
        $securityUtil = $this->container->get('user_security_utility');
        $source = $securityUtil->getDefaultSourceSystem($this->getParameter('deidentifier.sitename'));
        if( !$source ) {
            throw $this->createNotFoundException('Unable to find Deidentifier in SourceSystemList by name='."ORDER Deidentifier");
        }
        $accession->setSource($source);

        $em->persist($accession);
        $em->flush($accession);

        return $accession;
    }

    public function getPathParams($accession) {
        $pathParams = array();
        if( $accession ) {
            $key = $accession->obtainValidKeyfield();
            if( $key ) {
                $accessionType = $key->getKeytype()->getId();
                $accessionNumber = $key->getField();

                $pathParams = array(
                    'accessionType' => $accessionType,
                    'accessionNumber' => $accessionNumber
                );

            } else {
                throw $this->createNotFoundException('Unable to find a valid Accession Number.');
            }
            //exit('accessionNumber='.$accessionNumber);
        }
        return $pathParams;
    }

    public function getAccessionQuery($accessionTypeId,$accessionNumber,$institutions,$request) {
        $em = $this->getDoctrine()->getManager();

        //first get accession
        $accessions = $this->searchAccession($accessionTypeId,$accessionNumber,$institutions,false);
        if( !$accessions || count($accessions) == 0 ) {
            return null;
            //exit("accession is not found; accessionType=" . $accessionTypeId . ", accessionNumber=" . $accessionNumber . ", institution=" . $institution);
        }
        //echo "accession count=".count($accessions)."<br>";
        //echo "accessionTypeId=".$accessionTypeId."<br>";

        //$repository = $em->getRepository('AppOrderformBundle:AccessionAccession');
        $repository = $em->getRepository(AccessionAccession::class);
        $dql =  $repository->createQueryBuilder("accessionAccession");
        $dql->select('accessionAccession');
        $dql->leftJoin("accessionAccession.accession", "accession");
        $dql->leftJoin("accession.institution", "institution");
        $dql->leftJoin("accessionAccession.keytype", "keytype");

        $dql->leftJoin("accession.procedure", "procedure");
        $dql->leftJoin("procedure.encounter", "encounter");
        $dql->leftJoin("encounter.patient", "patient");
        $dql->leftJoin("patient.lastname", "lastname");

        //$dql->where("accession = :accession"); // AND keytype.id = :accessionType

        $accessionIdArr = array();
        foreach( $accessions as $accession ) {
            //echo "acc=".$accession."<br>";
            $accessionIdArr[] = "accession = " . $accession->getId();
        }
        $accessionIdStr = implode(" OR ", $accessionIdArr);
        $dql->where($accessionIdStr);

        //pass sorting parameters directly to query; Somehow, knp_paginator stoped correctly create pagination according to sorting parameters
        $postData = $request->query->all();
        if( isset($postData['sort']) ) {
            $dql = $dql . " ORDER BY $postData[sort] $postData[direction]";
        }

        $query = $dql->getQuery();

//        $query->setParameters( array(
//                'accession' => $accession->getId(),
//                //'accessionNumber' => '%'.$accessionNumber.'%',
//                //'accessionType' => $accessionType->getId()
//            )
//        );

        //echo "sql=".$query->getSql()."<br>";

        return $query;
    }

//    public function get_AccessionQuery_ORIG_ACCESSION($accessionType,$accessionNumber,$institution,$request) {
//        $em = $this->getDoctrine()->getManager();
//        $repository = $em->getRepository('AppOrderformBundle:Accession');
//        $dql =  $repository->createQueryBuilder("accession");
//        $dql->select('accession');
//        $dql->leftJoin("accession.accession", "accessionAccession");
//        $dql->leftJoin("accessionAccession.keytype", "keytype");
//
//        $dql->where("accessionAccession.field = :accessionNumber AND keytype.id = :accessionType AND accession.institution = :institution");
//
//        //pass sorting parameters directly to query; Somehow, knp_paginator stoped correctly create pagination according to sorting parameters
//        $postData = $request->query->all();
//        if( isset($postData['sort']) ) {
//            $dql = $dql . " ORDER BY $postData[sort] $postData[direction]";
//        }
//
//        $query = $em->createQuery($dql);
//
//        $query->setParameters( array(
//                'accessionNumber' => $accessionNumber,
//                //'accessionNumber' => '%'.$accessionNumber.'%',
//                'accessionType' => $accessionType->getId(),
//                'institution' => $institution->getId()
//            )
//        );
//
//        //echo "sql=".$query->getSql()."<br>";
//
//        return $query;
//    }

    //get a new deidentifier number
    public function getNewDeidentificator($accessionId) {

        if( !$this->isGranted('ROLE_DEIDENTIFICATOR_USER') ) {
            return null;
        }

        $defaultDeidentifierPrefix = "DID";
        $defaultDeidentifier = $defaultDeidentifierPrefix."-1";

        if( !$accessionId ) {
            return $defaultDeidentifier;
        }

        $em = $this->getDoctrine()->getManager();

        $deidentifier = null;

        //process.py script: replaced namespace by ::class: ['AppOrderformBundle:AccessionType'] by [AccessionType::class]
        $deidentifierType = $em->getRepository(AccessionType::class)->findOneByName("Deidentifier ID");
        if( !$deidentifierType ) {
            throw $this->createNotFoundException('Unable to find Deidentifier ID AccessionType entity.');
        }

        /////////////////////// get maxDeidentifier /////////////////////////
        $repository = $em->getRepository(AccessionAccession::class);
        $dql =  $repository->createQueryBuilder("accessionAccession");

        //use something like: SELECT MAX(CAST(SUBSTRING(invoice_number, 4, length(invoice_number)-3) AS UNSIGNED))
        //$dql->select('MAX(CAST(accessionAccession.original AS UNSIGNED)) as maxDeidentifier'); //working correct with cast and original field
        //DID-10 => start at index 5
        //UNSIGNED is not defined in SQL server version used in pacsvendor => use INTEGER
        $castAs = "INTEGER";
        if( $this->getParameter('database_driver') == 'pdo_mysql' ) {
            $castAs = "UNSIGNED";
        }
        $dql->select('MAX(CAST(SUBSTRING(accessionAccession.field, 5) AS '.$castAs.')) as maxDeidentifier');

        //$dql->where("accessionAccession.accession = :accessionId AND accessionAccession.keytype = :accessionType");
        $dql->where("accessionAccession.keytype = :accessionType");
        //$dql->orderBy("fellapp.interviewScore","ASC");

        $query = $dql->getQuery();

        $query->setParameters( array(
                //'accessionId' => $accessionId,
                'accessionType' => $deidentifierType->getId()
            )
        );
        $accessionAccessions = $query->getResult();
        //echo "accessionAccessions count=".count($accessionAccessions)."<br>";
        ////////////////////////////////////////////////

        if( count($accessionAccessions) == 1 ) {
            $accessionAccession = $accessionAccessions[0];

//            echo "accessionAccession:<br>";
//            print_r($accessionAccession);
//            echo "<br>";

            $maxDeidentifier = $accessionAccession['maxDeidentifier'];
            //echo "maxDeidentifier=".$maxDeidentifier."<br>";

            if( !$maxDeidentifier ) {
                $deidentifier = $defaultDeidentifier;
            } else {
                //echo "maxDeidentifier=".$maxDeidentifier."<br>";
                $maxDeidentifierInt = intval($maxDeidentifier);
                $deidentifierIntNext = $maxDeidentifierInt + 1;
                $deidentifier = $defaultDeidentifierPrefix."-".$deidentifierIntNext;
            }

        } else {
            throw $this->createNotFoundException('Unable to find a single Accession entity.');
        }

        //exit('deidentifier='.$deidentifier);
        return $deidentifier;
    }

    public function addNewDeidentifier( $accessionId, $deidentifier ) {

        if( !$accessionId ) {
            throw $this->createNotFoundException('Accession ID is not provided.');
        }

        if( !$deidentifier ) {
            throw $this->createNotFoundException('Deidentifier Number is not provided.');
        }

        $em = $this->getDoctrine()->getManager();
        //process.py script: replaced namespace by ::class: ['AppOrderformBundle:Accession'] by [Accession::class]
        $accession = $em->getRepository(Accession::class)->find($accessionId);

        if( !$accession ) {
            throw $this->createNotFoundException('Accession is not found by ID ' . $accessionId);
        }

        $user = $this->getUser();

        $status = 'deidentified-valid';
        $securityUtil = $this->container->get('user_security_utility');
        $source = $securityUtil->getDefaultSourceSystem($this->getParameter('deidentifier.sitename'));
        if( !$source ) {
            throw $this->createNotFoundException('Unable to find Deidentifier in SourceSystemList by name='."ORDER Deidentifier");
        }

        $accessionAccession = new AccessionAccession($status,$user,$source);

        //process.py script: replaced namespace by ::class: ['AppOrderformBundle:AccessionType'] by [AccessionType::class]
        $deidentifierType = $em->getRepository(AccessionType::class)->findOneByName("Deidentifier ID");
        if( !$deidentifierType ) {
            throw $this->createNotFoundException('Unable to find Deidentifier ID AccessionType entity.');
        }
        $accessionAccession->setKeytype($deidentifierType);

        $accessionAccession->setField($deidentifier);

        //get original number
        $pieces = explode("-", $deidentifier);
        $maxDeidentifierStr = $pieces[1];
        $maxDeidentifierInt = intval($maxDeidentifierStr);
        //echo "maxDeidentifierInt=".$maxDeidentifierInt."<br>";

        $accessionAccession->setOriginal($maxDeidentifierInt);

        $accession->addAccession( $accessionAccession );

        $em->flush($accession);

        return $accession;
    }

    //check for active access requests
    public function getActiveAccessReq() {
        if( !$this->isGranted('ROLE_DEIDENTIFICATOR_ADMIN') ) {
            return null;
        }
        $userSecUtil = $this->container->get('user_security_utility');
        $accessreqs = $userSecUtil->getUserAccessRequestsByStatus($this->getParameter('deidentifier.sitename'),AccessRequest::STATUS_ACTIVE);
        return $accessreqs;
    }



}
