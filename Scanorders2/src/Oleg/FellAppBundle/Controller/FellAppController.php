<?php

namespace Oleg\FellAppBundle\Controller;

use Oleg\UserdirectoryBundle\Entity\User;
use Oleg\OrderformBundle\Helper\ErrorHelper;
use Oleg\UserdirectoryBundle\Entity\AccessRequest;
use Oleg\UserdirectoryBundle\Entity\Reference;
use Oleg\FellAppBundle\Form\FellAppFilterType;
use Oleg\FellAppBundle\Form\FellowshipApplicationType;
use Oleg\UserdirectoryBundle\Util\UserUtil;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;



class FellAppController extends Controller {

    /**
     * Show home page
     *
     * @Route("/", name="fellapp_home")
     * @Template("OlegFellAppBundle:Default:home.html.twig")
     */
    public function indexAction(Request $request) {        
        
        if(
            false == $this->get('security.context')->isGranted('ROLE_USER') ||              // authenticated (might be anonymous)
            false == $this->get('security.context')->isGranted('IS_AUTHENTICATED_FULLY')    // authenticated (NON anonymous)
        ){
            return $this->redirect( $this->generateUrl('login') );
        }

        //echo "fellapp home <br>";

        $em = $this->getDoctrine()->getManager();
        $fellappUtil = $this->container->get('fellapp_util');

        $searchFlag = false;
        $currentYear = date("Y")+2;


        //create fellapp filter
        $params = array(
            'fellTypes' => $fellappUtil->getFellowshipTypesWithSpecials(),
        );
        $filterform = $this->createForm(new FellAppFilterType($params), null);

        $filterform->bind($request);  //use bind instead of handleRequest. handleRequest does not get filter data

        $search = $filterform['search']->getData();
        $filter = $filterform['filter']->getData();
        $startDate = $filterform['startDate']->getData();
        $hidden = $filterform['hidden']->getData();
        $archived = $filterform['archived']->getData();
        $completed = $filterform['completed']->getData();
        //$page = $request->get('page');
        //echo "<br>startDate=".$startDate."<br>";
        //echo "<br>filter=".$filter."<br>";
        //echo "<br>search=".$search."<br>";

        if( !$startDate ) {
            return $this->redirect( $this->generateUrl('fellapp_home',array('filter[startDate]'=>$currentYear)) );
        }

        //$fellApps = $em->getRepository('OlegUserdirectoryBundle:FellowshipApplication')->findAll();
        $repository = $this->getDoctrine()->getRepository('OlegFellAppBundle:FellowshipApplication');
        $dql =  $repository->createQueryBuilder("fellapp");
        $dql->select('fellapp');
        //$dql->groupBy('fellapp');
        $dql->orderBy("fellapp.timestamp","DESC");
        $dql->leftJoin("fellapp.fellowshipSubspecialty", "fellowshipSubspecialty");
        $dql->leftJoin("fellapp.user", "applicant");
        $dql->leftJoin("applicant.infos", "applicantinfos");
        $dql->leftJoin("applicant.credentials", "credentials");
        $dql->leftJoin("credentials.examinations", "examinations");

        if( $search ) {
            $dql->leftJoin("applicant.infos", "userinfos");
            $dql->andWhere("userinfos.firstName LIKE '%".$search."%' OR userinfos.lastName LIKE '%".$search."%'");
            $searchFlag = true;
        }

        if( $filter && $filter != "ALL" ) {
            $dql->andWhere("fellowshipSubspecialty.id = ".$filter);
            $searchFlag = true;
        }

        if( !$hidden ) {
            $dql->andWhere("fellapp.applicationStatus != 'hide'");
        } else {
            $searchFlag = true;
        }

        if( !$archived ) {
            $dql->andWhere("fellapp.applicationStatus != 'archive'");
        } else {
            $searchFlag = true;
        }

        if( !$completed ) {
            $dql->andWhere("fellapp.applicationStatus != 'complete'");
        } else {
            $searchFlag = true;
        }

        $ldap = false;
        if($ldap) 
        if( $startDate ) {
            //$transformer = new DateTimeToStringTransformer(null,null,'Y-m-d');
            //$dateStr = $transformer->transform($startDate);
            //$dql->andWhere("fellapp.startDate >= '".$startDate."'");
            //$dql->andWhere("year(fellapp.startDate) = '".$startDate->format('Y')."'");
            $startDateStr = $startDate->format('Y');
            $bottomDate = "01-01-".$startDateStr;
            $topDate = "12-31-".$startDateStr;
            $dql->andWhere("fellapp.startDate BETWEEN '" . $bottomDate . "'" . " AND " . "'" . $topDate . "'" );

            if( $startDateStr != $currentYear ) {
                $searchFlag = true;
            }
        }


        //echo "dql=".$dql."<br>";

        $limit = 100;
        $query = $em->createQuery($dql);
        $paginator  = $this->get('knp_paginator');
        $fellApps = $paginator->paginate(
            $query,
            $this->get('request')->query->get('page', 1), /*page number*/
            //$request->query->getInt('page', 1),
            $limit      /*limit per page*/
        );


        $em = $this->getDoctrine()->getManager();
        $eventtype = $em->getRepository('OlegUserdirectoryBundle:EventTypeList')->findOneByName("Import of Fellowship Applications");
        $lastImportTimestamps = $this->getDoctrine()->getRepository('OlegUserdirectoryBundle:Logger')->findBy(array('eventType'=>$eventtype),array('creationdate'=>'DESC'),1);
        if( count($lastImportTimestamps) != 1 ) {
            $lastImportTimestamp = null;
        } else {
            $lastImportTimestamp = $lastImportTimestamps[0]->getCreationdate();
        }

        $accessreqs = $fellappUtil->getActiveAccessReq();

        $completed = $fellappUtil->getFellAppByStatusAndYear('complete',$currentYear);
        $completedTotal = $fellappUtil->getFellAppByStatusAndYear('complete');

        $hidden = $fellappUtil->getFellAppByStatusAndYear('hide',$currentYear);
        $hiddenTotal = $fellappUtil->getFellAppByStatusAndYear('hide');

        $archived = $fellappUtil->getFellAppByStatusAndYear('archive',$currentYear);
        $archivedTotal = $fellappUtil->getFellAppByStatusAndYear('archive');

        $active = $fellappUtil->getFellAppByStatusAndYear('active',$currentYear);
        $activeTotal = $fellappUtil->getFellAppByStatusAndYear('active');

        //echo "timezone=".date_default_timezone_get()."<br>";

        return array(
            'entities' => $fellApps,
            'pathbase' => 'fellapp',
            'lastImportTimestamp' => $lastImportTimestamp,
            'fellappfilter' => $filterform->createView(),
            'startDate' => $startDate,
            'accessreqs' => count($accessreqs),
            'currentYear' => $currentYear,
            'hiddenTotal' => count($hiddenTotal),
            'archivedTotal' => count($archivedTotal),
            'hidden' => count($hidden),
            'archived' => count($archived),
            'active' => count($active),
            'activeTotal' => count($activeTotal),
            'completed' => count($completed),
            'completedTotal' => count($completedTotal),
            'searchFlag' => $searchFlag,
            'serverTimeZone' => "" //date_default_timezone_get()
        );
    }

//    //check for active access requests
//    public function getActiveAccessReq() {
//        if( !$this->get('security.context')->isGranted('ROLE_FELLAPP_ADMIN') ) {
//            return null;
//        }
//        $userSecUtil = $this->get('user_security_utility');
//        $accessreqs = $userSecUtil->getUserAccessRequestsByStatus($this->container->getParameter('fellapp.sitename'),AccessRequest::STATUS_ACTIVE);
//        return $accessreqs;
//    }




    /**
     * @Route("/show/{id}", name="fellapp_show")
     * @Route("/edit/{id}", name="fellapp_edit")
     * @Route("/download/{id}", name="fellapp_download")
     *
     * @Template("OlegFellAppBundle:Form:new.html.twig")
     */
    public function showAction(Request $request, $id) {

        //echo "clientip=",$request->getClientIp(),"<br>";
        
        $routeName = $request->get('_route');
        $userSecUtil = $this->container->get('user_security_utility');

        if( $routeName == "fellapp_edit" ) {
            if( false == $this->get('security.context')->isGranted('ROLE_FELLAPP_ADMIN') ){
                return $this->redirect( $this->generateUrl('fellapp-nopermission') );
            }
        }
             
        if( $routeName == 'fellapp_download' ) {
            //download link can be accessed by a console as localhost with role IS_AUTHENTICATED_ANONYMOUSLY, so simulate login manually           
            if( !($this->get('security.context')->getToken()->getUser() instanceof User) ) {
                $firewall = 'ldap_fellapp_firewall';               
                $systemUser = $userSecUtil->findSystemUser();
                if( $systemUser ) {
                    $token = new UsernamePasswordToken($systemUser, null, $firewall, $systemUser->getRoles());
                    $this->get('security.context')->setToken($token);
                    //$this->get('security.token_storage')->setToken($token);
                }
                $logger = $this->container->get('logger');
                $logger->notice("Logged in as systemUser=".$systemUser);
            }
        }

        if( false == $this->get('security.context')->isGranted('ROLE_FELLAPP_USER') ){
            return $this->redirect( $this->generateUrl('fellapp-nopermission') );
        }
        
        //echo "fellapp download!!!!!!!!!!!!!!! <br>";       

        $entity = $this->getDoctrine()->getRepository('OlegFellAppBundle:FellowshipApplication')->find($id);

        if( !$entity ) {
            throw $this->createNotFoundException('Unable to find Fellowship Application by id='.$id);
        }

        $args = $this->getShowParameters($id,$routeName);

        if( $routeName == 'fellapp_download' ) {
            return $this->render('OlegFellAppBundle:Form:download.html.twig', $args);
        }

        //event log
        $actionStr = "viewed";
        $eventType = 'Fellowship Applicant Page Viewed';
        $user = $this->get('security.context')->getToken()->getUser();
        //$userSecUtil = $this->container->get('user_security_utility');
        $event = "Fellowship Application with ID".$id." has been ".$actionStr." by ".$user;
        $userSecUtil->createUserEditEvent($this->container->getParameter('fellapp.sitename'),$event,$user,$entity,$request,$eventType);
        
        return $this->render('OlegFellAppBundle:Form:new.html.twig', $args);
    }

    public function getShowParameters($id,$routeName) {
             
        $user = $this->get('security.context')->getToken()->getUser(); 

//        echo "user=".$user."<br>";
//        if( !($user instanceof User) ) {
//            echo "no user object <br>";
//            $userSecUtil = $this->container->get('user_security_utility');
//            $user = $userSecUtil->findSystemUser();
//        }               
        
        $em = $this->getDoctrine()->getManager();

        //$fellApps = $em->getRepository('OlegFellAppBundle:FellowshipApplication')->findAll();
        $entity = $this->getDoctrine()->getRepository('OlegFellAppBundle:FellowshipApplication')->find($id);

        if( !$entity ) {
            throw $this->createNotFoundException('Unable to find Fellowship Application by id='.$id);
        }


        //$routeName = $request->get('_route');

        if( $routeName == "fellapp_show" ) {
            $cycle = 'show';
            $disabled = true;
            $method = "GET";
            $action = $this->generateUrl('fellapp_edit', array('id' => $entity->getId()));
        }

        if( $routeName == "fellapp_edit" ) {
            $cycle = 'edit';
            $disabled = false;
            $method = "PUT";
            $action = $this->generateUrl('fellapp_update', array('id' => $entity->getId()));
        }

        if( $routeName == "fellapp_download" ) {
            $cycle = 'download';
            $disabled = true;
            $method = "GET";
            $action = null; //$this->generateUrl('fellapp_update', array('id' => $entity->getId()));
        }

        $params = array(
            'cycle' => $cycle,
            'sc' => $this->get('security.context'),
            'em' => $em,
            'user' => $entity->getUser(),
            'cloneuser' => null,
            'roles' => $user->getRoles()
        );

        $form = $this->createForm(
            new FellowshipApplicationType($params),
            $entity,
            array(
                'disabled' => $disabled,
                'method' => $method,
                'action' => $action
            )
        );


        return array(
            'form' => $form->createView(),
            'entity' => $entity,
            'pathbase' => 'fellapp',
            'cycle' => $cycle,
            'sitename' => $this->container->getParameter('fellapp.sitename')
        );
    }


    /**
     * @Route("/update/{id}", name="fellapp_update")
     * @Method("PUT")
     * @Template("OlegFellAppBundle:Form:new.html.twig")
     */
    public function updateAction(Request $request, $id) {

        if(
            false == $this->get('security.context')->isGranted('ROLE_USER') ||              // authenticated (might be anonymous)
            false == $this->get('security.context')->isGranted('IS_AUTHENTICATED_FULLY')    // authenticated (NON anonymous)
        ){
            return $this->redirect( $this->generateUrl('login') );
        }

        echo "update <br>";
        //exit('update');

        $entity = $this->getDoctrine()->getRepository('OlegFellAppBundle:FellowshipApplication')->find($id);

        if( !$entity ) {
            throw $this->createNotFoundException('Unable to find Fellowship Application by id='.$id);
        }

        $cycle = 'edit';
        $user = $this->get('security.context')->getToken()->getUser();

        $params = array(
            'cycle' => $cycle,
            'sc' => $this->get('security.context'),
            'em' => $this->getDoctrine()->getManager(),
            'user' => $entity->getUser(),
            'cloneuser' => null,
            'roles' => $user->getRoles()
        );

        $form = $this->createForm( new FellowshipApplicationType($params), $entity );

        $form->handleRequest($request);

        if( !$form->isSubmitted() ) {
            echo "form is not submitted<br>";
            $form->submit($request);
        }


//        if ($form->isDisabled()) {
//            echo "form is disabled<br>";
//        }
//        if (count($form->getErrors(true)) > 0) {
//            echo "form has errors<br>";
//        }
//        echo "errors:<br>";
//        $string = (string) $form->getErrors(true);
//        echo "string errors=".$string."<br>";
//        echo "getErrors count=".count($form->getErrors())."<br>";
//        echo "getErrorsAsString()=".$form->getErrorsAsString()."<br>";
        //print_r($form->getErrors());
//        echo "<br>string errors:<br>";
//        print_r($form->getErrorsAsString());
//        echo "<br>";
//        exit();

        if(0) {
            $errorHelper = new ErrorHelper();
            $errors = $errorHelper->getErrorMessages($form);
            echo "<br>form errors:<br>";
            print_r($errors);
        }

        if( $form->isValid() ) {

            //exit('form valid');

            $this->processDocuments($entity);

            //set update author application
            $em = $this->getDoctrine()->getManager();
            $userUtil = new UserUtil();
            $sc = $this->get('security.context');
            $userUtil->setUpdateInfo($entity,$em,$sc);

            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();

//            //update report
//            $fellappUtil = $this->container->get('fellapp_util');
//            $fellappUtil->addFellAppReportToQueue( $id );

            //set logger for update
            $userSecUtil = $this->container->get('user_security_utility');
            $systemUser = $userSecUtil->findSystemUser();
            $event = "Fellowship Application with ID " . $id . " has been updated by " . $user;
            $userSecUtil->createUserEditEvent($this->container->getParameter('fellapp.sitename'),$event,$systemUser,$entity,$request,'Fellowship Application Updated');

            //$response = new Response($id);
            //$this->container->get('kernel')->terminate($request,$response);

            return $this->redirect($this->generateUrl('fellapp_show',array('id' => $entity->getId())));

            //$args = $this->getShowParameters($id,"fellapp_show");
            //return $this->render('OlegFellAppBundle:Form:new.html.twig', $args);
        }

        //echo 'form invalid <br>';
        //exit('form invalid');

        return array(
            'form' => $form->createView(),
            'entity' => $entity,
            'pathbase' => 'fellapp',
            'cycle' => $cycle
        );


    }

    //process upload documents: CurriculumVitae(documents), FellowshipApplication(coverLetters), Examination(scores), FellowshipApplication(lawsuitDocuments), FellowshipApplication(reprimandDocuments)
    public function processDocuments($application) {

        $em = $this->getDoctrine()->getManager();

        $userUtil = new UserUtil();
        $sc = $this->get('security.context');

        $subjectUser = $application->getUser();

        //CurriculumVitae
        foreach( $subjectUser->getCredentials()->getCvs() as $cv ) {
            $em->getRepository('OlegUserdirectoryBundle:Document')->processDocuments( $cv );
        }

        //FellowshipApplication(coverLetters)
        $em->getRepository('OlegUserdirectoryBundle:Document')->processDocuments( $application, 'coverLetter' );
        $em->getRepository('OlegUserdirectoryBundle:Document')->processDocuments( $application, 'lawsuitDocument');
        $em->getRepository('OlegUserdirectoryBundle:Document')->processDocuments( $application, 'reprimandDocument' );

        //Examination
        foreach( $subjectUser->getCredentials()->getExaminations() as $examination ) {
            $em->getRepository('OlegUserdirectoryBundle:Document')->processDocuments( $examination );
        }

    }


    /**
     * @Route("/status/{id}/{status}", name="fellapp_status")
     */
    public function statusAction(Request $request, $id,$status) {

        if(
            false == $this->get('security.context')->isGranted('ROLE_USER') ||              // authenticated (might be anonymous)
            false == $this->get('security.context')->isGranted('IS_AUTHENTICATED_FULLY')    // authenticated (NON anonymous)
        ){
            return $this->redirect( $this->generateUrl('login') );
        }

        echo "status <br>";

        $em = $this->getDoctrine()->getManager();

        //$fellApps = $em->getRepository('OlegUserdirectoryBundle:FellowshipApplication')->findAll();
        $entity = $this->getDoctrine()->getRepository('OlegFellAppBundle:FellowshipApplication')->find($id);

        if( !$entity ) {
            throw $this->createNotFoundException('Unable to find Fellowship Application by id='.$id);
        }


        $entity->setApplicationStatus($status);

        $em->persist($entity);
        $em->flush();

        switch($status) {
            case 'active':
                $eventType = 'Fellowship Application Status changed to Active';
                break;
            case 'archive':
                $eventType = 'Fellowship Application Status changed to Archived';
                break;
            case 'hide':
                $eventType = 'Fellowship Application Status changed to Hidden';
                break;
            case 'complete':
                $eventType = 'Fellowship Application Status changed to Complete';
                break;
            default:
                $eventType = 'Fellowship Application Status changed to Active';
        }

        $userSecUtil = $this->container->get('user_security_utility');
        $user = $this->get('security.context')->getToken()->getUser();
        $event = $eventType . '; application ID ' . $id . ' by user ' . $user;
        $userSecUtil->createUserEditEvent($this->container->getParameter('fellapp.sitename'),$event,$user,$entity,$request,$eventType);

        return $this->redirect( $this->generateUrl('fellapp_home') );
    }


    /**
     * @Route("/resend-emails/{id}", name="fellapp_resendemails")
     */
    public function resendemailsAction(Request $request, $id) {

        if(
            false == $this->get('security.context')->isGranted('ROLE_USER') ||              // authenticated (might be anonymous)
            false == $this->get('security.context')->isGranted('IS_AUTHENTICATED_FULLY')    // authenticated (NON anonymous)
        ){
            return $this->redirect( $this->generateUrl('login') );
        }

        echo "resendemails <br>";

        $em = $this->getDoctrine()->getManager();

        $entity = $this->getDoctrine()->getRepository('OlegFellAppBundle:FellowshipApplication')->find($id);

        if( !$entity ) {
            throw $this->createNotFoundException('Unable to find Fellowship Application by id='.$id);
        }


        $userSecUtil = $this->container->get('user_security_utility');
        $systemUser = $userSecUtil->findSystemUser();
        $event = "Resend emails for fellowship application ID " . $id;
        $userSecUtil->createUserEditEvent($this->container->getParameter('fellapp.sitename'),$event,$systemUser,$entity,$request,'Fellowship Application Resend Emails');

        return $this->redirect( $this->generateUrl('fellapp_home') );
    }


    /**
     * @Route("/remove/{id}", name="fellapp_remove")
     */
    public function removeAction($id) {

        if( false == $this->get('security.context')->isGranted('ROLE_PLATFORM_ADMIN') ){
            return $this->redirect( $this->generateUrl('fellapp-nopermission') );
        }

        echo "remove <br>";
        exit('not supported');

        $em = $this->getDoctrine()->getManager();

        $entity = $this->getDoctrine()->getRepository('OlegFellAppBundle:FellowshipApplication')->find($id);

        if( !$entity ) {
            throw $this->createNotFoundException('Unable to find Fellowship Application by id='.$id);
        }

        if(0) {
            $user = $entity->getUser();

            $entity->setApplicationStatus('archive');

            $user->setUsernameForce($user->getUsername()."-deleted");

            $user->setPrimaryPublicUserId($user->getPrimaryPublicUserId()."-deleted");

            $avatar = $user->getAvatar();
            $em->persist($avatar);
            $em->remove($avatar);
            $user->setAvatar(NULL);

            //$em->remove($entity);

            $em->flush();
        }

        return $this->redirect( $this->generateUrl('fellapp_home') );



        $credentials = $user->getCredentials();

        echo "training count=".count($user->getTrainings())."<br>";
        foreach( $user->getTrainings() as $training  ) {
            echo "remove training<br>";
            $user->remove($training);
            $em->remove($training);
            //$em->flush();
        }

        foreach( $entity->getCoverLetters() as $item ) {
            $entity->removeCoverLetter($item);
            $em->remove($item);
        }

        //$em->remove($credentials);
        //$em->flush();

        //exit('ok');

        //$em->remove($user);
        //$em->flush();

        $em->remove($entity);
        $em->flush();


        return $this->redirect( $this->generateUrl('fellapp_home') );
    }




    /**
     * Import and populate applicants from Google
     *
     * @Route("/populate_import", name="fellapp_import_populate")
     */
    public function importAndPopulateAction(Request $request) {

        $fellappUtil = $this->container->get('fellapp_util');
        $error = false;

        //1) import
        $fileDb = $fellappUtil->importFellApp();

        if( $fileDb ) {
            $event = "Fellowship Application Spreadsheet file has been successful downloaded to the server with id=" . $fileDb->getId().", title=".$fileDb->getUniquename();
            $flashType = 'notice';
        } else {
            $event = "Fellowship Application Spreadsheet download failed!";
            $flashType = 'warning';
            $error = true;
        }

        $this->get('session')->getFlashBag()->add(
            $flashType,
            $event
        );

        if( $error ) {
            return $this->redirect( $this->generateUrl('fellapp_home') );
        }

        //2) populate
        $populatedCount = $fellappUtil->populateFellApp();

        if( $populatedCount >= 0 ) {
            $event = "Populated ".$populatedCount." Fellowship Applicantions.";
            $flashType = 'notice';
        } else {
            $event = "Google API service failed!";
            $flashType = 'warning';
        }

        $this->get('session')->getFlashBag()->add(
            $flashType,
            $event
        );

        return $this->redirect( $this->generateUrl('fellapp_home') );
    }

    /**
     * Show home page
     *
     * @Route("/populate", name="fellapp_populate")
     */
    public function populateSpreadsheetAction(Request $request) {

        $fellappUtil = $this->container->get('fellapp_util');
        $populatedCount = $fellappUtil->populateFellApp();

        if( $populatedCount >= 0 ) {
            $event = "Populated ".$populatedCount." Fellowship Applicantions.";
            $flashType = 'notice';
        } else {
            $event = "Google API service failed!";
            $flashType = 'warning';
        }

        $this->get('session')->getFlashBag()->add(
            $flashType,
            $event
        );

        return $this->redirect( $this->generateUrl('fellapp_home') );
    }


    /**
     * Import spreadsheet to C:\Program Files (x86)\Aperio\Spectrum\htdocs\order\scanorder\Scanorders2\web\Uploaded\fellapp\Spreadsheets
     *
     * @Route("/import", name="fellapp_import")
     */
    public function importAction(Request $request) {

        $fellappUtil = $this->container->get('fellapp_util');
        $fileDb = $fellappUtil->importFellApp();

        if( $fileDb ) {
            $event = "Fellowship Application Spreadsheet file has been successful downloaded to the server with id=" . $fileDb->getId().", title=".$fileDb->getUniquename();
            $flashType = 'notice';
        } else {
            $event = "Fellowship Application Spreadsheet download failed!";
            $flashType = 'warning';
        }

        $this->get('session')->getFlashBag()->add(
            $flashType,
            $event
        );

        //exit('import event'.$event);

        return $this->redirect( $this->generateUrl('fellapp_home') );

//        //$excelFile = $this->printFile($service, $excelId);
//
//        //$response = $this->downloadFile($service, $excelFile, 'excel');
//
//        //echo "response=".$response."<br>";
//
//        exit(1);
//
//
////        $files = $service->files->listFiles();
////        echo "count files=".count($files)."<br>";
////        //echo "<pre>"; print_r($files);
////        foreach( $files as $item ) {
////            echo "title=".$item['title']."<br>";
////        }
//
//        //https://drive.google.com/open?id=0B2FwyaXvFk1edWdMdTlFTUt1aVU
//        $folderId = "0B2FwyaXvFk1edWdMdTlFTUt1aVU";
//        //https://drive.google.com/open?id=0B2FwyaXvFk1efmc2VGVHUm5yYjJRWGFYYTF0Z2N6am9iUFVzcTc1OXdoWEl1Vmc0LWdZc0E
//        //$folderId = "0B2FwyaXvFk1efmc2VGVHUm5yYjJRWGFYYTF0Z2N6am9iUFVzcTc1OXdoWEl1Vmc0LWdZc0E";
//        //$files = $this->printFilesInFolder($service, $folderId);
//
//
//        $photoId = "0B2FwyaXvFk1eRnJVS1N0MWhkc0E";
//        $file = $this->printFile($service, $photoId);
//        $response = $this->downloadFile($service, $file);
//        echo "response=".$response."<br>";
//
//        exit('1');
//
//        // Exchange authorization code for access token
//        //$accessToken = $client->authenticate($authCode);
//        //$client->setAccessToken($accessToken);
//
//        $fileId = "1DN1BEbONKNmFpHU6xBo69YSLjXCnhRy0IbyXrwMzEzc";
//
//        $file = $this->printFile($service, $fileId);
//
//        echo "after file <br>";
//
//        $response = $this->downloadFile($service,$file);
//
//        print_r($response);
//
//        echo "response=".$response."<br>";
//        //exit();
//        return $response;
//
//        return $this->redirect( $this->generateUrl('fellapp_home') );
    }




    /**
     * @Route("/update-report/", name="fellapp_update_report", options={"expose"=true})
     * @Method("POST")
     */
    public function updateReportAction(Request $request) {

        $id = $request->get('id');

        //update report
        $fellappRepGen = $this->container->get('fellapp_reportgenerator');
        $fellappRepGen->addFellAppReportToQueue( $id, 'overwrite' );

        $response = new Response();
        $response->setContent('Sent to queue');
        return $response;
    }


    /**
     * Download application using
     * https://github.com/KnpLabs/KnpSnappyBundle
     * https://github.com/devandclick/EnseparHtml2pdfBundle
     *
     * @Route("/download-pdf/{id}", name="fellapp_download_pdf")
     */
    public function downloadReportAction(Request $request, $id) {

        if( false == $this->get('security.context')->isGranted('ROLE_FELLAPP_USER') ){
            return $this->redirect( $this->generateUrl('fellapp-nopermission') );
        }



        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('OlegFellAppBundle:FellowshipApplication')->find($id);

        if( !$entity ) {
            throw $this->createNotFoundException('Unable to find Fellowship Application by id='.$id);
        }

        //event log
        $user = $this->get('security.context')->getToken()->getUser();
        $userSecUtil = $this->container->get('user_security_utility');
        $event = "Report for Fellowship Application with ID".$id." has been downloaded by ".$user;
        $userSecUtil->createUserEditEvent($this->container->getParameter('fellapp.sitename'),$event,$user,$entity,null,'Complete Fellowship Application Downloaded');

        $reportDocument = $entity->getRecentReport();

        if( $reportDocument ) {

            return $this->redirect( $this->generateUrl('employees_file_download',array('id' => $reportDocument->getId())) );

        } else {

            //TODO: implement report generator manager
            if(1) {
                //create report
                $fellappRepGen = $this->container->get('fellapp_reportgenerator');
                $argument = 'asap';
                if( $this->get('security.context')->isGranted('ROLE_FELLAPP_ADMIN') ) {
                    $argument = 'overwrite';
                }
                $fellappRepGen->addFellAppReportToQueue( $id, $argument );

                //exit('fellapp_download_pdf exit');

                $this->get('session')->getFlashBag()->add(
                    'warning',
                    'Application Report is not ready yet. Please try again later.'
                );

                return $this->redirect( $this->generateUrl('fellapp_show',array('id' => $id)) );
            }

        }

        //create report
        $fellappRepGen = $this->container->get('fellapp_reportgenerator');
        $res = $fellappRepGen->addFellAppReportToQueue( $id, true ); //create report on download click
        $filename = $res['filename'];
        $report = $res['report'];
        $size = $res['size'];

        //render report
        $response = new Response();
        $response->headers->set('Content-Type', 'application/pdf');
        $response->headers->set('Content-Description', 'File Transfer');
        $response->headers->set('Content-Disposition', 'attachment; filename="'.$filename.'"');
        $response->headers->set('Content-Length', $size);
        $response->headers->set('Content-Transfer-Encoding', 'binary');
        $response->setContent(file_get_contents($report));

        return $response;
    }






    ///////////////////// un used methods //////////////////////////
    /**
     * Print files belonging to a folder.
     *
     * @param Google_Service_Drive $service Drive API service instance.
     * @param String $folderId ID of the folder to print files from.
     */
    function printFilesInFolder($service, $folderId) {
        $pageToken = NULL;

        do {
            try {
                $parameters = array();
                if ($pageToken) {
                    $parameters['pageToken'] = $pageToken;
                }
                $children = $service->children->listChildren($folderId, $parameters);
                echo "count=".count($children->getItems())."<br>";

                foreach ($children->getItems() as $child) {
                    //print 'File Id: ' . $child->getId()."<br>";
                    //print_r($child);
                    $this->printFile($service,$child->getId());
                }
                $pageToken = $children->getNextPageToken();
            } catch (Exception $e) {
                print "An error occurred: " . $e->getMessage();
                $pageToken = NULL;
            }
        } while ($pageToken);
    }

    function getFilesByAuthUrl() {
        $client_id = "1040591934373-hhm896qpgdaiiblaco9jdfvirkh5f65q.apps.googleusercontent.com";
        $client_secret = "RgXkEm2_1T8yKYa3Vw_tIhoO";
        $redirect_uri = 'urn:ietf:wg:oauth:2.0:oob';    //"http://localhost";

        $res = $this->buildService($client_id,$client_secret,$redirect_uri);

        $service = $res['service'];
        $client = $res['client'];

        $authUrl = $client->createAuthUrl();
        echo "authUrl=".$authUrl."<br>";

        // Exchange authorization code for access token
        $accessToken = $client->authenticate('4/OrVeRdkw9eByckCs7Gtn0B4eUwhERny8AqFOAwy29fY');
        $client->setAccessToken($accessToken);

        $files = $service->files->listFiles();
        echo "count files=".count($files)."<br>";
        echo "<pre>"; print_r($files);
    }

    /**
     * Build a Drive service object.
     */
    function buildService($client_id,$client_secret,$redirect_uri) {
        $client = new \Google_Client();
        $client->setClientId($client_id);
        $client->setClientSecret($client_secret);
        $client->setRedirectUri($redirect_uri);

        //$client->addScope("https://www.googleapis.com/auth/drive");
        $client->setScopes(array('https://www.googleapis.com/auth/drive'));
        $client->setAccessType('offline');

        $service = new \Google_Service_Drive($client);

        $res = array(
            'client' => $client,
            'service' => $service
        );
        return $res;
    }

    /**
     * Print a file's metadata.
     *
     * @param apiDriveService $service Drive API service instance.
     * @param string $fileId ID of the file to print metadata for.
     */
    function printFile($service, $fileId) {
        $file = null;
        try {
            $file = $service->files->get($fileId);

            print "Title: " . $file->getTitle()."<br>";
            print "ID: " . $file->getId()."<br>";
            print "Size: " . $file->getFileSize()."<br>";
            //print "URL: " . $file->getDownloadUrl()."<br>";
            print "Description: " . $file->getDescription()."<br>";
            print "MIME type: " . $file->getMimeType()."<br>"."<br>";

        } catch (Exception $e) {
            print "An error occurred: " . $e->getMessage();
        }
        return $file;
    }



    /**
     * Show home page
     *
     * @Route("/test", name="fellapp_test")
     * @Method("GET")
     */
    public function testAction() {

        //include_once "vendor/google/apiclient/examples/simple-query.php";
        include_once "vendor/google/apiclient/examples/user-example.php";
        //include_once "vendor/google/apiclient/examples/idtoken.php";



        return new Response("OK Test");
    }

}
