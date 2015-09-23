<?php

namespace Oleg\FellAppBundle\Controller;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityNotFoundException;
use Oleg\FellAppBundle\Entity\FellowshipApplication;
use Oleg\FellAppBundle\Entity\Interview;
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
use Symfony\Component\Form\Extension\Core\DataTransformer\DateTimeToStringTransformer;
use Symfony\Component\Form\FormError;
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

        if( false == $this->get('security.context')->isGranted('ROLE_FELLAPP_USER') ){
            return $this->redirect( $this->generateUrl('fellapp-nopermission') );
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
        $complete = $filterform['complete']->getData();
        $interviewee = $filterform['interviewee']->getData();
        $active = $filterform['active']->getData();
        $reject = $filterform['reject']->getData();
        $onhold = $filterform['onhold']->getData();
        //$page = $request->get('page');
        //echo "<br>startDate=".$startDate."<br>";
        //echo "<br>filter=".$filter."<br>";
        //echo "<br>search=".$search."<br>";

        $filterParams = $request->query->all();

        if( count($filterParams) == 0 ) {
            return $this->redirect( $this->generateUrl('fellapp_home',
                array(
                    'filter[startDate]' => $currentYear,
                    'filter[active]' => 1,
                    'filter[complete]' => 1,
                    'filter[interviewee]' => 1,
                    'filter[onhold]' => 1,
                )
            ) );
        }

        //$fellApps = $em->getRepository('OlegUserdirectoryBundle:FellowshipApplication')->findAll();
        $repository = $this->getDoctrine()->getRepository('OlegFellAppBundle:FellowshipApplication');
        $dql =  $repository->createQueryBuilder("fellapp");
        $dql->select('fellapp');
        //$dql->groupBy('fellapp');
        $dql->orderBy("fellapp.timestamp","DESC");
        $dql->leftJoin("fellapp.appStatus", "appStatus");
        $dql->leftJoin("fellapp.fellowshipSubspecialty", "fellowshipSubspecialty");
        $dql->leftJoin("fellapp.user", "applicant");
        $dql->leftJoin("applicant.infos", "applicantinfos");
        //$dql->leftJoin("applicant.credentials", "credentials");
        $dql->leftJoin("fellapp.examinations", "examinations");

        if( $search ) {
            $dql->leftJoin("applicant.infos", "userinfos");
            $dql->andWhere("userinfos.firstName LIKE '%".$search."%' OR userinfos.lastName LIKE '%".$search."%'");
            $searchFlag = true;
        }

        $fellSubspecId = null;
        if( $filter && $filter != "ALL" ) {
            $dql->andWhere("fellowshipSubspecialty.id = ".$filter);
            $searchFlag = true;
            $fellSubspecId = $filter;
        }

        $orWhere = array();

        if( $hidden ) {
            $orWhere[] = "appStatus.name = 'hide'";
            $searchFlag = true;
        } else {
            //$searchFlag = true;
        }

        if( $archived ) {
            $orWhere[] = "appStatus.name = 'archive'";
            $searchFlag = true;
        } else {
            //$searchFlag = true;
        }

        if( $complete ) {
            $orWhere[] = "appStatus.name = 'complete'";
            $searchFlag = true;
        } else {
            //$searchFlag = true;
        }

        if( $interviewee ) {
            $orWhere[] = "appStatus.name = 'interviewee'";
            $searchFlag = true;
        } else {
            //$searchFlag = true;
        }

        if( $active ) {
            $orWhere[] = "appStatus.name = 'active'";
            $searchFlag = true;
        } else {
            //$searchFlag = true;
        }

        if( $reject ) {
            $orWhere[] = "appStatus.name = 'reject'";
            $searchFlag = true;
        }

        if( $onhold ) {
            $orWhere[] = "appStatus.name = 'onhold'";
            $searchFlag = true;
        }

        if( count($orWhere) > 0 ) {
            $orWhereStr = implode(" OR ",$orWhere);
            $dql->andWhere("(".$orWhereStr.")");
        }

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

        $complete = $fellappUtil->getFellAppByStatusAndYear('complete',$fellSubspecId,$currentYear);
        $completeTotal = $fellappUtil->getFellAppByStatusAndYear('complete',$fellSubspecId);

        $hidden = $fellappUtil->getFellAppByStatusAndYear('hide',$fellSubspecId,$currentYear);
        $hiddenTotal = $fellappUtil->getFellAppByStatusAndYear('hide',$fellSubspecId);

        $archived = $fellappUtil->getFellAppByStatusAndYear('archive',$fellSubspecId,$currentYear);
        $archivedTotal = $fellappUtil->getFellAppByStatusAndYear('archive',$fellSubspecId);

        $active = $fellappUtil->getFellAppByStatusAndYear('active',$fellSubspecId,$currentYear);
        $activeTotal = $fellappUtil->getFellAppByStatusAndYear('active',$fellSubspecId);

        $interviewee = $fellappUtil->getFellAppByStatusAndYear('interviewee',$fellSubspecId,$currentYear);
        $intervieweeTotal = $fellappUtil->getFellAppByStatusAndYear('interviewee',$fellSubspecId);

        $reject = $fellappUtil->getFellAppByStatusAndYear('reject',$fellSubspecId,$currentYear);
        $rejectTotal = $fellappUtil->getFellAppByStatusAndYear('reject',$fellSubspecId);

        $onhold = $fellappUtil->getFellAppByStatusAndYear('onhold',$fellSubspecId,$currentYear);
        $onholdTotal = $fellappUtil->getFellAppByStatusAndYear('onhold',$fellSubspecId);

        //echo "timezone=".date_default_timezone_get()."<br>";

        return array(
            'entities' => $fellApps,
            'pathbase' => 'fellapp',
            'lastImportTimestamp' => $lastImportTimestamp,
            'fellappfilter' => $filterform->createView(),
            'startDate' => $startDate,
            'filter' => $fellSubspecId,
            'accessreqs' => count($accessreqs),
            'currentYear' => $currentYear,
            'hiddenTotal' => count($hiddenTotal),
            'archivedTotal' => count($archivedTotal),
            'hidden' => count($hidden),
            'archived' => count($archived),
            'active' => count($active),
            'activeTotal' => count($activeTotal),
            'reject' => count($reject),
            'rejectTotal' => count($rejectTotal),
            'onhold' => count($onhold),
            'onholdTotal' => count($onholdTotal),
            'complete' => count($complete),
            'completeTotal' => count($completeTotal),
            'interviewee' => count($interviewee),
            'intervieweeTotal' => count($intervieweeTotal),
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

        //echo "clientip=".$request->getClientIp()."<br>";
        //$ip = $this->container->get('request')->getClientIp();
        //echo "ip=".$ip."<br>";

        $em = $this->getDoctrine()->getManager();
        $logger = $this->container->get('logger');
        $routeName = $request->get('_route');
        $userSecUtil = $this->container->get('user_security_utility');

        $actionStr = "viewed";
        $eventType = 'Fellowship Applicant Page Viewed';

        //admin can edit
        if( $routeName == "fellapp_edit" ) {
            $actionStr = "updated";
            $eventType = 'Fellowship Application Updated';
            if( false == $this->get('security.context')->isGranted('ROLE_FELLAPP_COORDINATOR') ){
                return $this->redirect( $this->generateUrl('fellapp-nopermission') );
            }
        }

        //download: user or localhost
        if( $routeName == 'fellapp_download' ) {
            $user = $this->get('security.context')->getToken()->getUser();
            //download link can be accessed by a console as localhost with role IS_AUTHENTICATED_ANONYMOUSLY, so simulate login manually           
            if( !($user instanceof User) ) {
                $firewall = 'ldap_fellapp_firewall';               
                $systemUser = $userSecUtil->findSystemUser();
                if( $systemUser ) {
                    $token = new UsernamePasswordToken($systemUser, null, $firewall, $systemUser->getRoles());
                    $this->get('security.context')->setToken($token);
                    //$this->get('security.token_storage')->setToken($token);
                }
                $logger->notice("Download view: Logged in as systemUser=".$systemUser);
            } else {
                $logger->notice("Download view: Token user is valid security.context user=".$user);
            }
        }

        //user can view
        if( false == $this->get('security.context')->isGranted('ROLE_FELLAPP_USER') ){
            return $this->redirect( $this->generateUrl('fellapp-nopermission') );
        }
        
        //echo "fellapp download!!!!!!!!!!!!!!! <br>";       

        $entity = $em->getRepository('OlegFellAppBundle:FellowshipApplication')->find($id);

        if( !$entity ) {
            throw $this->createNotFoundException('Unable to find Fellowship Application by id='.$id);
        }

        $args = $this->getShowParameters($routeName,$id);

        if( $routeName == 'fellapp_download' ) {
            return $this->render('OlegFellAppBundle:Form:download.html.twig', $args);
        }


        //event log
        $user = $this->get('security.context')->getToken()->getUser();
        //$userEntity = $em->getRepository('OlegUserdirectoryBundle:User')->find($user->getId());
        //$userSecUtil = $this->container->get('user_security_utility');
        $event = "Fellowship Application with ID".$id." has been ".$actionStr." by ".$user;
        $userSecUtil->createUserEditEvent($this->container->getParameter('fellapp.sitename'),$event,$user,$entity,$request,$eventType);
        
        return $this->render('OlegFellAppBundle:Form:new.html.twig', $args);
    }


    /**
     * @Route("/new/", name="fellapp_new")
     *
     * @Template("OlegFellAppBundle:Form:new.html.twig")
     */
    public function newAction(Request $request) {

        //admin can edit
        if( false == $this->get('security.context')->isGranted('ROLE_FELLAPP_COORDINATOR') ){
            return $this->redirect( $this->generateUrl('fellapp-nopermission') );
        }

        $user = $this->get('security.context')->getToken()->getUser();

        //$user = new User();
        $addobjects = true;
        $applicant = new User($addobjects);
        $applicant->setPassword("");
        $applicant->setCreatedby('manual');

        $fellowshipApplication = new FellowshipApplication($user);
        $fellowshipApplication->setTimestamp(new \DateTime());

        $applicant->addFellowshipApplication($fellowshipApplication);

        $routeName = $request->get('_route');
        $args = $this->getShowParameters($routeName,null,$fellowshipApplication);

        return $this->render('OlegFellAppBundle:Form:new.html.twig', $args);
    }


    public function getShowParameters($routeName, $id=null, $entity=null) {
             
        $user = $this->get('security.context')->getToken()->getUser(); 

//        echo "user=".$user."<br>";
//        if( !($user instanceof User) ) {
//            echo "no user object <br>";
//            $userSecUtil = $this->container->get('user_security_utility');
//            $user = $userSecUtil->findSystemUser();
//        }               
        
        $em = $this->getDoctrine()->getManager();

        if( $id ) {
            //$fellApps = $em->getRepository('OlegFellAppBundle:FellowshipApplication')->findAll();
            $entity = $this->getDoctrine()->getRepository('OlegFellAppBundle:FellowshipApplication')->find($id);

            if( !$entity ) {
                throw $this->createNotFoundException('Unable to find Fellowship Application by id='.$id);
            }
        } else {
            if( !$entity ) {
                throw $this->createNotFoundException('Fellowship Application entity was not provided: id='.$id.", entity=".$entity);
            }
        }

        //add empty fields if they are not exist
        $fellappUtil = $this->container->get('fellapp_util');
        $fellappUtil->addEmptyFellAppFields($entity);

        if( $routeName == "fellapp_show" ) {
            $cycle = 'show';
            $disabled = true;
            $method = "GET";
            $action = $this->generateUrl('fellapp_edit', array('id' => $entity->getId()));
        }

        if( $routeName == "fellapp_new" ) {
            $cycle = 'new';
            $disabled = false;
            $method = "POST";
            $action = $this->generateUrl('fellapp_create_applicant');
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


        //clear em, because createUserEditEvent will flush em
        $em = $this->getDoctrine()->getManager();
        $em->clear();

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

        if( false == $this->get('security.context')->isGranted('ROLE_FELLAPP_COORDINATOR') && false == $this->get('security.context')->isGranted('ROLE_FELLAPP_DIRECTOR') ){
            return $this->redirect( $this->generateUrl('fellapp-nopermission') );
        }

        //echo "update <br>";
        //exit('update');

        $entity = $this->getDoctrine()->getRepository('OlegFellAppBundle:FellowshipApplication')->find($id);

        if( !$entity ) {
            throw $this->createNotFoundException('Unable to find Fellowship Application by id='.$id);
        }

        // Create an ArrayCollection of the current interviews
        $originalInterviews = new ArrayCollection();
        foreach( $entity->getInterviews() as $interview) {
            $originalInterviews->add($interview);
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
            //echo "form is not submitted<br>";
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
//        print_r($form->getErrors());
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

            /////////////// Process Removed Collections ///////////////
            $removedCollections = array();

            $removedInfo = $this->removeCollection($originalInterviews,$entity->getInterviews(),$entity);
            if( $removedInfo ) {
                $removedCollections[] = $removedInfo;
            }
            /////////////// EOF Process Removed Collections ///////////////

            $this->calculateScore($entity);

            $this->processDocuments($entity);

            //set update author application
            $em = $this->getDoctrine()->getManager();
            $userUtil = new UserUtil();
            $sc = $this->get('security.context');
            $userUtil->setUpdateInfo($entity,$em,$sc);


            /////////////// Add event log on edit (edit or add collection) ///////////////
            /////////////// Must run before removeCollection() function which flash DB. When DB is flashed getEntityChangeSet() will not work ///////////////
            $changedInfoArr = $this->setEventLogChanges($entity);

            //set Edit event log for removed collection and changed fields or added collection
            if( count($changedInfoArr) > 0 || count($removedCollections) > 0 ) {
                $user = $this->get('security.context')->getToken()->getUser();
                $event = "Fellowship Application ".$entity->getId()." information has been changed by ".$user.":"."<br>";
                $event = $event . implode("<br>", $changedInfoArr);
                $event = $event . "<br>" . implode("<br>", $removedCollections);
                $userSecUtil = $this->get('user_security_utility');
                $userSecUtil->createUserEditEvent($this->container->getParameter('fellapp.sitename'),$event,$user,$entity,$request);
            }


            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();

            //update report if report does not exists
            //if( count($entity->getReports()) == 0 ) {
                $fellappRepGen = $this->container->get('fellapp_reportgenerator');
                $fellappRepGen->addFellAppReportToQueue( $id, 'overwrite' );
                $this->get('session')->getFlashBag()->add(
                    'notice',
                    'A new Complete Fellowship Application PDF will be generated.'
                );
            //}

            //set logger for update
            $userSecUtil = $this->container->get('user_security_utility');
            $systemUser = $userSecUtil->findSystemUser();
            $event = "Fellowship Application with ID " . $id . " has been updated by " . $user;
            $userSecUtil->createUserEditEvent($this->container->getParameter('fellapp.sitename'),$event,$systemUser,$entity,$request,'Fellowship Application Updated');


            return $this->redirect($this->generateUrl('fellapp_show',array('id' => $entity->getId())));
        }

        //echo 'form invalid <br>';
        //exit('form invalid');

        return array(
            'form' => $form->createView(),
            'entity' => $entity,
            'pathbase' => 'fellapp',
            'cycle' => $cycle,
            'sitename' => $this->container->getParameter('fellapp.sitename')
        );
    }

    public function calculateScore($entity) {
        $count = 0;
        $score = 0;
        foreach( $entity->getInterviews() as $interview ) {
            $totalRank = $interview->getTotalRank();
            if( $totalRank ) {
                $score = $score + $totalRank;
                $count++;
            }
        }
        if( $count > 0 ) {
            $score = $score/$count;
        }

        $entity->setInterviewScore($score);
    }

    public function setEventLogChanges($entity) {

        $em = $this->getDoctrine()->getManager();

        $uow = $em->getUnitOfWork();
        $uow->computeChangeSets(); // do not compute changes if inside a listener

        $eventArr = array();

        //log simple fields
        $changeset = $uow->getEntityChangeSet($entity);
        $eventArr = $this->addChangesToEventLog( $eventArr, $changeset );

        //interviews
        foreach( $entity->getInterviews() as $subentity ) {
            $changeset = $uow->getEntityChangeSet($subentity);
            $text = "("."interview ".$this->getEntityId($subentity).")";
            $eventArr = $this->addChangesToEventLog( $eventArr, $changeset, $text );
        }

        return $eventArr;
    }
    public function removeCollection($originalArr,$currentArr,$entity) {
        $em = $this->getDoctrine()->getManager();
        $removeArr = array();

        foreach( $originalArr as $element ) {
            if( false === $currentArr->contains($element) ) {
                $removeArr[] = "<strong>"."Removed: ".$element." ".$this->getEntityId($element)."</strong>";

                if( $element instanceof Interview ) {
                    $entity->removeInterview($element);
                    //$element->setInterviewer(NULL);
                    $em->remove($element);
                }
            }
        } //foreach

        return implode("<br>", $removeArr);
    }
    public function addChangesToEventLog( $eventArr, $changeset, $text="" ) {

        $changeArr = array();

        //process $changeset: author, subjectuser, oldvalue, newvalue
        foreach( $changeset as $key => $value ) {
            if( $value[0] != $value[1] ) {

                if( is_object($key) ) {
                    //if $key is object then skip it, because we don't want to have non-informative record such as: credentials(stateLicense New): old value=, new value=Credentials
                    continue;
                }

                $field = $key;

                $oldValue = $value[0];
                $newValue = $value[1];

                if( $oldValue instanceof \DateTime ) {
                    $oldValue = $this->convertDateTimeToStr($value[0]);
                }
                if( $newValue instanceof \DateTime ) {
                    $newValue = $this->convertDateTimeToStr($value[1]);
                }

                if( is_array($oldValue) ) {
                    $oldValue = implode(",",$oldValue);
                }
                if( is_array($newValue) ) {
                    $newValue = implode(",",$newValue);
                }

                $event = "<strong>".$field.$text."</strong>".": "."old value=".$oldValue.", new value=".$newValue;
                //echo "event=".$event."<br>";
                //exit();

                $changeArr[] = $event;
            }
        }

        if( count($changeArr) > 0 ) {
            $eventArr[] = implode("<br>", $changeArr);
        }

        return $eventArr;

    }

    public function convertDateTimeToStr($datetime) {
        $transformer = new DateTimeToStringTransformer(null,null,'m/d/Y');
        $dateStr = $transformer->transform($datetime);
        return $dateStr;
    }
    public function getEntityId($entity) {
        if( $entity->getId() ) {
            return "ID=".$entity->getId();
        }
        return "New";
    }

    /**
     * @Route("/applicant/new", name="fellapp_create_applicant")
     * @Method("POST")
     * @Template("OlegFellAppBundle:Form:new.html.twig")
     */
    public function createApplicantAction( Request $request )
    {

        if( false == $this->get('security.context')->isGranted('ROLE_FELLAPP_COORDINATOR') && false == $this->get('security.context')->isGranted('ROLE_FELLAPP_DIRECTOR') ){
            return $this->redirect( $this->generateUrl('fellapp-nopermission') );
        }

        $em = $this->getDoctrine()->getManager();
        $user = $this->get('security.context')->getToken()->getUser();

        $fellowshipApplication = new FellowshipApplication($user);

        $activeStatus = $em->getRepository('OlegFellAppBundle:FellAppStatus')->findOneByName("active");
        if( !$activeStatus ) {
            throw new EntityNotFoundException('Unable to find FellAppStatus by name='."active");
        }
        $fellowshipApplication->setAppStatus($activeStatus);

        if( !$fellowshipApplication->getUser() ) {
            //new applicant
            $addobjects = false;
            $applicant = new User($addobjects);
            $applicant->setPassword("");
            $applicant->setCreatedby('manual');
            $applicant->addFellowshipApplication($fellowshipApplication);
        }

        //add empty fields if they are not exist
        $fellappUtil = $this->container->get('fellapp_util');
        $fellappUtil->addEmptyFellAppFields($fellowshipApplication);

        $params = array(
            'cycle' => 'new',
            'sc' => $this->get('security.context'),
            'em' => $this->getDoctrine()->getManager(),
            'user' => $fellowshipApplication->getUser(),
            'cloneuser' => null,
            'roles' => $user->getRoles()
        );
        $form = $this->createForm( new FellowshipApplicationType($params), $fellowshipApplication );

        $form->handleRequest($request);

        if( !$form->isSubmitted() ) {
            //echo "form is not submitted<br>";
            $form->submit($request);
        }

        $applicant = $fellowshipApplication->getUser();

        if( !$fellowshipApplication->getFellowshipSubspecialty() ) {
            $form['fellowshipSubspecialty']->addError(new FormError('Please select in the Fellowship Type before uploading'));
        }
        if( !$applicant->getEmail() ) {
            $form['user']['infos'][0]['email']->addError(new FormError('Please fill in the email before uploading'));
        }
        if( !$applicant->getFirstName() ) {
            $form['user']['infos'][0]['firstName']->addError(new FormError('Please fill in the First Name before uploading'));
        }
        if( !$applicant->getLastName() ) {
            $form['user']['infos'][0]['lastName']->addError(new FormError('Please fill in the Last Name before uploading'));
        }

        if( $form->isValid() ) {

            //set user
            $userSecUtil = $this->container->get('user_security_utility');
            $userkeytype = $userSecUtil->getUsernameType('local-user');
            if( !$userkeytype ) {
                throw new EntityNotFoundException('Unable to find local user keytype');
            }
            $applicant->setKeytype($userkeytype);

            $currentDateTime = new \DateTime();
            $currentDateTimeStr = $currentDateTime->format('m-d-Y-h-i-s');

            //Last Name + First Name + Email
            $applicantname = $applicant->getLastName()."_".$applicant->getFirstName()."_".$applicant->getEmail()."_".$currentDateTimeStr;
            $applicant->setPrimaryPublicUserId($applicantname);

            //set unique username
            $applicantnameUnique = $applicant->createUniqueUsername();
            $applicant->setUsername($applicantnameUnique);
            $applicant->setUsernameCanonical($applicantnameUnique);

            $applicant->setEmailCanonical($applicant->getEmail());
            $applicant->setPassword("");
            $applicant->setCreatedby('manual');

            $default_time_zone = $this->container->getParameter('default_time_zone');
            $applicant->getPreferences()->setTimezone($default_time_zone);
            $applicant->setLocked(true);

            //exit('form valid');

            $this->processDocuments($fellowshipApplication);

            //set update author application
//            $em = $this->getDoctrine()->getManager();
//            $userUtil = new UserUtil();
//            $sc = $this->get('security.context');
//            $userUtil->setUpdateInfo($fellowshipApplication,$em,$sc);

            //exit('eof new applicant');

            $em = $this->getDoctrine()->getManager();
            $em->persist($fellowshipApplication);
            $em->persist($applicant);
            $em->flush();

            //update report if report does not exists
            //if( count($entity->getReports()) == 0 ) {
            $fellappRepGen = $this->container->get('fellapp_reportgenerator');
            $fellappRepGen->addFellAppReportToQueue( $fellowshipApplication->getId(), 'overwrite' );
            $this->get('session')->getFlashBag()->add(
                'notice',
                'A new Complete Fellowship Application PDF will be generated.'
            );
            //}

            //set logger for update
            $userSecUtil = $this->container->get('user_security_utility');
            $systemUser = $userSecUtil->findSystemUser();
            $event = "Fellowship Application with ID " . $fellowshipApplication->getId() . " has been created by " . $user;
            $userSecUtil->createUserEditEvent($this->container->getParameter('fellapp.sitename'),$event,$systemUser,$fellowshipApplication,$request,'Fellowship Application Updated');


            return $this->redirect($this->generateUrl('fellapp_show',array('id' => $fellowshipApplication->getId())));
        }

        //echo 'form invalid <br>';
        //exit('form invalid');

        return array(
            'form' => $form->createView(),
            'entity' => $fellowshipApplication,
            'pathbase' => 'fellapp',
            'cycle' => 'new',
            'sitename' => $this->container->getParameter('fellapp.sitename')
        );

    }



    //process upload documents: CurriculumVitae(documents), FellowshipApplication(coverLetters), Examination(scores), FellowshipApplication(lawsuitDocuments), FellowshipApplication(reprimandDocuments)
    public function processDocuments($application) {

        $em = $this->getDoctrine()->getManager();

        //Avatar
        $em->getRepository('OlegUserdirectoryBundle:Document')->processDocuments( $application, 'avatar' );

        //CurriculumVitae
        $em->getRepository('OlegUserdirectoryBundle:Document')->processDocuments( $application, 'cv' );

        //FellowshipApplication(coverLetters)
        $em->getRepository('OlegUserdirectoryBundle:Document')->processDocuments( $application, 'coverLetter' );
        $em->getRepository('OlegUserdirectoryBundle:Document')->processDocuments( $application, 'lawsuitDocument');
        $em->getRepository('OlegUserdirectoryBundle:Document')->processDocuments( $application, 'reprimandDocument' );

        //Examination
        foreach( $application->getExaminations() as $examination ) {
            $em->getRepository('OlegUserdirectoryBundle:Document')->processDocuments( $examination );
        }

        //Reference .documents
        foreach( $application->getReferences() as $reference ) {
            $em->getRepository('OlegUserdirectoryBundle:Document')->processDocuments( $reference );
        }

        //Other .documents
        $em->getRepository('OlegUserdirectoryBundle:Document')->processDocuments( $application );

        //.itinerarys
        $em->getRepository('OlegUserdirectoryBundle:Document')->processDocuments( $application, 'itinerary' );

    }


    /**
     * @Route("/status/{id}/{status}", name="fellapp_status")
     * @Method("GET")
     */
    public function statusAction( Request $request, $id, $status ) {

        //echo "status <br>";

        if( false == $this->get('security.context')->isGranted('ROLE_FELLAPP_COORDINATOR') && false == $this->get('security.context')->isGranted('ROLE_FELLAPP_DIRECTOR') ){
            return $this->redirect( $this->generateUrl('fellapp-nopermission') );
        }

        $em = $this->getDoctrine()->getManager();

        //$fellApps = $em->getRepository('OlegUserdirectoryBundle:FellowshipApplication')->findAll();
        $entity = $this->getDoctrine()->getRepository('OlegFellAppBundle:FellowshipApplication')->find($id);

        if( !$entity ) {
            throw $this->createNotFoundException('Unable to find Fellowship Application by id='.$id);
        }


        //get status object
        //$entity->setApplicationStatus($status);
        $statusObj = $em->getRepository('OlegFellAppBundle:FellAppStatus')->findOneByName($status);
        if( !$statusObj ) {
            throw new EntityNotFoundException('Unable to find FellAppStatus by name='.$status);
        }

        //change status
        $entity->setAppStatus($statusObj);

        $em->persist($entity);
        $em->flush();

        $eventType = 'Fellowship Application Status changed to ' . $statusObj->getAction();

        $userSecUtil = $this->container->get('user_security_utility');
        $user = $this->get('security.context')->getToken()->getUser();
        $event = $eventType . '; application ID ' . $id . ' by user ' . $user;
        $userSecUtil->createUserEditEvent($this->container->getParameter('fellapp.sitename'),$event,$user,$entity,$request,$eventType);

        //return $this->redirect( $this->generateUrl('fellapp_home'));

        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');
        $response->setContent(json_encode("ok"));
        return $response;
    }


//    /**
//     * @Route("/status-sync/", name="fellapp_sincstatus")
//     * @Method("GET")
//     */
//    public function syncStatusAction( Request $request ) {
//
//        $em = $this->getDoctrine()->getManager();
//        $applications = $this->getDoctrine()->getRepository('OlegFellAppBundle:FellowshipApplication')->findAll();
//
//        foreach( $applications as $application ) {
//            $status = $application->getApplicationStatus();
//            $statusObj = $em->getRepository('OlegFellAppBundle:FellAppStatus')->findOneByName($status);
//            if( !$statusObj ) {
//                throw new EntityNotFoundException('Unable to find FellAppStatus by name='.$status);
//            }
//            $application->setAppStatus($statusObj);
//            //$application->setApplicationStatus(NULL);
//        }
//
//        $em->flush();
//
//        return $this->redirect( $this->generateUrl('fellapp_home') );
//    }


    /**
     * @Route("/interview/show/{id}", name="fellapp_interview_show")
     * @Route("/interview/edit/{id}", name="fellapp_interview_edit")
     * @Method("GET")
     * @Template("OlegFellAppBundle:Interview:new.html.twig")
     */
    public function interviewAction( Request $request, $id ) {

        //echo "status <br>";

        if( false == $this->get('security.context')->isGranted('ROLE_FELLAPP_INTERVIEWER') ){
            return $this->redirect( $this->generateUrl('fellapp-nopermission') );
        }

        $em = $this->getDoctrine()->getManager();

        $interview = $em->getRepository('OlegFellAppBundle:Interview')->find($id);

        if( !$interview ) {
            throw $this->createNotFoundException('Unable to find Fellowship Application Interview by id='.$id);
        }

        $routeName = $request->get('_route');

        if( $routeName == "fellapp_interview_show" ) {
           $cycle = "show";
        }

        if( $routeName == "fellapp_interview_edit" ) {
            $cycle = "edit";
        }

        $params = array(
            'cycle' => $cycle,
            'sc' => $this->get('security.context'),
            'em' => $em,
            'interviewer' => $interview->getInterviewer()
        );
        $form = $this->createForm( new InterviewType($params), $interview );

        return array(
            'form' => $form->createView(),
            'entity' => $interview,
            'pathbase' => 'fellapp',
            'cycle' => $cycle,
            'sitename' => $this->container->getParameter('fellapp.sitename')
        );

    }

//    /**
//     * @Route("/interview/new/{fellappid}/{interviewid}", name="fellapp_interview_new")
//     * @Route("/interview/new/{fellappid}/{interviewid}", name="fellapp_interview_new")
//     * @Method("GET")
//     * @Template("OlegFellAppBundle:Interview:new.html.twig")
//     */
//    public function createInterviewAction( Request $request ) {
//
//        //echo "status <br>";
//
//        if( false == $this->get('security.context')->isGranted('ROLE_FELLAPP_INTERVIEWER') ){
//            return $this->redirect( $this->generateUrl('fellapp-nopermission') );
//        }
//
//        $em = $this->getDoctrine()->getManager();
//
//        $interview = $this->getDoctrine()->getRepository('OlegFellAppBundle:Interview')->find($id);
//
//        if( !$interview ) {
//            throw $this->createNotFoundException('Unable to find Fellowship Application Interview by id='.$id);
//        }
//
//        $cycle = "new";
//
//        $params = array(
//            'cycle' => $cycle,
//            'sc' => $this->get('security.context'),
//            'em' => $this->getDoctrine()->getManager(),
//        );
//        $form = $this->createForm( new InterviewType($params), $interview );
//
//        return array(
//            'form' => $form->createView(),
//            'entity' => $interview,
//            'pathbase' => 'fellapp',
//            'cycle' => $cycle,
//            'sitename' => $this->container->getParameter('fellapp.sitename')
//        );
//
//    }

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

        if( false == $this->get('security.context')->isGranted('ROLE_FELLAPP_ADMIN') ){
            return $this->redirect( $this->generateUrl('fellapp-nopermission') );
        }

        //echo "remove <br>";
        exit('remove not supported');

        return $this->redirect( $this->generateUrl('fellapp_home') );
    }




    /**
     * Import and populate applicants from Google
     *
     * @Route("/populate-import", name="fellapp_import_populate")
     */
    public function importAndPopulateAction(Request $request) {

        if( false == $this->get('security.context')->isGranted('ROLE_FELLAPP_ADMIN') ){
            return $this->redirect( $this->generateUrl('fellapp-nopermission') );
        }

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

        if( false == $this->get('security.context')->isGranted('ROLE_FELLAPP_ADMIN') ){
            return $this->redirect( $this->generateUrl('fellapp-nopermission') );
        }

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

        if( false == $this->get('security.context')->isGranted('ROLE_FELLAPP_ADMIN') ){
            return $this->redirect( $this->generateUrl('fellapp-nopermission') );
        }

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




//    /**
//     * NOT USED NOW
//     * update report by js
//     *
//     * @Route("/update-report/", name="fellapp_update_report", options={"expose"=true})
//     * @Method("POST")
//     */
//    public function updateReportAction(Request $request) {
//
//        $id = $request->get('id');
//
//        $em = $this->getDoctrine()->getManager();
//        $entity = $em->getRepository('OlegFellAppBundle:FellowshipApplication')->find($id);
//
//        if( !$entity ) {
//            throw $this->createNotFoundException('Unable to find Fellowship Application by id='.$id);
//        }
//
//        echo "reports = " . count($entity->getReports()) . "<br>";
//        exit();
//
//        //update report if report does not exists
//        if( count($entity->getReports()) == 0 ) {
//            $fellappRepGen = $this->container->get('fellapp_reportgenerator');
//            $fellappRepGen->addFellAppReportToQueue( $id, 'overwrite' );
//        }
//
//        $response = new Response();
//        $response->setContent('Sent to queue');
//        return $response;
//    }


    /**
     * Download application using
     * https://github.com/KnpLabs/KnpSnappyBundle
     * https://github.com/devandclick/EnseparHtml2pdfBundle
     *
     * @Route("/download-pdf/{id}", name="fellapp_download_pdf")
     * @Method("GET")
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
        //echo "report=".$reportDocument."<br>";
        //exit();

        if( $reportDocument ) {

            return $this->redirect( $this->generateUrl('employees_file_download',array('id' => $reportDocument->getId())) );

        } else {

            //create report
            $fellappRepGen = $this->container->get('fellapp_reportgenerator');
            $argument = 'asap';
            //if( $this->get('security.context')->isGranted('ROLE_FELLAPP_COORDINATOR') ) {
                //$argument = 'overwrite';
            //}
            $fellappRepGen->addFellAppReportToQueue( $id, $argument );

            //exit('fellapp_download_pdf exit');

            $this->get('session')->getFlashBag()->add(
                'warning',
                'Complete Application PDF is not ready yet. Please try again later.'
            );

            return $this->redirect( $this->generateUrl('fellapp_show',array('id' => $id)) );
        }

    }


    /**
     * @Route("/regenerate-all-complete-application-pdfs/", name="fellapp_regenerate_reports")
     *
     * @Template("OlegFellAppBundle:Form:new.html.twig")
     */
    public function regenerateAllReportsAction(Request $request) {

        if( false == $this->get('security.context')->isGranted('ROLE_FELLAPP_ADMIN') ){
            return $this->redirect( $this->generateUrl('fellapp-nopermission') );
        }

        $fellappRepGen = $this->container->get('fellapp_reportgenerator');
        $numDeleted = $fellappRepGen->regenerateAllReports();

        $em = $this->getDoctrine()->getManager();
        $fellapps = $em->getRepository('OlegFellAppBundle:FellowshipApplication')->findAll();
        $estimatedTime = count($fellapps)*5; //5 min for each report
        $this->get('session')->getFlashBag()->add(
            'notice',
            'All Application Reports will be regenerated. Estimated processing time for ' . count($fellapps) . ' reports is ' . $estimatedTime . ' minutes. Number of deleted processes in queue ' . $numDeleted
        );

        return $this->redirect( $this->generateUrl('fellapp_home') );
    }

    /**
     * @Route("/reset-queue-and-run/", name="fellapp_reset_queue_run")
     *
     * @Template("OlegFellAppBundle:Form:new.html.twig")
     */
    public function resetQueueRunAction(Request $request) {

        if( false == $this->get('security.context')->isGranted('ROLE_FELLAPP_ADMIN') ){
            return $this->redirect( $this->generateUrl('fellapp-nopermission') );
        }

        $fellappRepGen = $this->container->get('fellapp_reportgenerator');
        $numUpdated = $fellappRepGen->resetQueueRun();

        $em = $this->getDoctrine()->getManager();
        $processes = $em->getRepository('OlegFellAppBundle:Process')->findAll();
        $estimatedTime = count($processes)*5; //5 min for each report
        $this->get('session')->getFlashBag()->add(
            'notice',
            'Queue with ' . count($processes) . ' will be re-run. Estimated processing time is ' . $estimatedTime . ' minutes. Number of reset processes in queue ' . $numUpdated
        );

        return $this->redirect( $this->generateUrl('fellapp_home') );
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
