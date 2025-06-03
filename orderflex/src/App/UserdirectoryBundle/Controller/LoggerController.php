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

namespace App\UserdirectoryBundle\Controller;



use App\UserdirectoryBundle\Entity\Roles; //process.py script: replaced namespace by ::class: added use line for classname=Roles
use App\UserdirectoryBundle\Entity\EventObjectTypeList;
use App\UserdirectoryBundle\Entity\User;
use App\UserdirectoryBundle\Form\LoggerFilterType;
use Symfony\Component\Form\Extension\Core\DataTransformer\DateTimeToStringTransformer;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use App\UserdirectoryBundle\Controller\OrderAbstractController;


use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Component\Routing\Annotation\Route;
use App\UserdirectoryBundle\Entity\Logger;
use App\UserdirectoryBundle\Form\LoggerType;
use Symfony\Component\HttpFoundation\Response;

/**
 * Logger controller.
 */
#[Route(path: '/event-log')]
class LoggerController extends OrderAbstractController
{

    /**
     * Lists all Logger entities.
     */
    #[Route(path: '/', name: 'employees_logger', methods: ['GET'])]
    #[Template('AppUserdirectoryBundle/Logger/index.html.twig')]
    public function indexAction(Request $request)
    {
        $params = array(
            'sitename'=>$this->getParameter('employees.sitename')
        );
        return $this->listLogger($params,$request);
    }

    /**
     * Lists all Logger entities across all sites.
     */
    #[Route(path: '/all-sites/', name: 'employees_logger_allsites', methods: ['GET'])]
    #[Template('AppUserdirectoryBundle/Logger/index.html.twig')]
    public function indexAllAction(Request $request)
    {
        $params = array(
            'allsites'=>true
        );
        return $this->listLogger($params,$request);
    }

    /**
     * Lists audit log for a specific user
     */
    #[Route(path: '/user/{id}', name: 'employees_logger_user_with_id', methods: ['GET'])]
    #[Route(path: '/user', name: 'employees_logger_user', methods: ['GET'])]
    #[Template('AppUserdirectoryBundle/Logger/logger_object.html.twig')]
    public function getAuditLogAction(Request $request)
    {

        $postData = $request->get('postData');
        $userid = $request->get('id');
        $onlyheader = $request->get('onlyheader');

        //echo "postData=<br>";
        //print_r($postData);

        $eventStr = null;
        if( $userid ) {
            $eventStr = $this->getEventStrByUserid($userid);
        }

        $entityName = 'User';

        $params = array(
            'sitename'=>$this->getParameter('employees.sitename'),
            'entityNamespace'=>'App\UserdirectoryBundle\Entity',
            'entityName'=>$entityName,
            'entityId'=>$userid,
            'eventStr'=>$eventStr,
            'postData'=>$postData,
            'onlyheader'=>true
        );

        $logger =  $this->listLogger($params,$request);

        return $logger;
    }

    #[Route(path: '/user/{id}/all', name: 'employees_logger_user_all', methods: ['GET'])]
    #[Template('AppUserdirectoryBundle/Logger/index.html.twig')]
    public function getAuditLogAllAction(Request $request)
    {
        $postData = $request->get('postData');
        $userid = $request->get('id');
        //$onlyheader = $request->get('onlyheader');

        //echo "postData=<br>";
        //print_r($postData);


        $eventStr = null;
        if( $userid ) {
            $eventStr = $this->getEventStrByUserid($userid);
        }

        $entityName = 'User';

        $params = array(
            'sitename'=>$this->getParameter('employees.sitename'),
            'entityNamespace'=>'App\UserdirectoryBundle\Entity',
            'entityName'=>$entityName,
            'entityId'=>$userid,
            'eventStr'=>$eventStr,
            'postData'=>$postData,
            'onlyheader'=>false,
            'allsites'=>true
        );

        $logger =  $this->listLogger($params,$request);

        return $logger;
    }

    public function getEventStrByUserid( $userid ) {
        $em = $this->getDoctrine()->getManager();
        $subjectUser = $em->getRepository(User::class)->find($userid);
        if( $subjectUser ) {
            $cwid = $subjectUser->getPrimaryPublicUserId();
            return $cwid;
        }
        return null;
    }

    protected function listLogger( $params, $request ) {

        $sitename = ( array_key_exists('sitename', $params) ? $params['sitename'] : null);
        $allsites = ( array_key_exists('allsites', $params) ? $params['allsites'] : null);
        $entityNamespace = ( array_key_exists('entityNamespace', $params) ? $params['entityNamespace'] : null);
        $entityName = ( array_key_exists('entityName', $params) ? $params['entityName'] : null);
        $entityId = ( array_key_exists('entityId', $params) ? $params['entityId'] : null);
        $eventStr = ( array_key_exists('eventStr', $params) ? $params['eventStr'] : null);
        $postData = ( array_key_exists('postData', $params) ? $params['postData'] : null);
        $onlyheader = ( array_key_exists('onlyheader', $params) ? $params['onlyheader'] : null);
        //$acrossSites = ( array_key_exists('acrossSites', $params) ? $params['acrossSites'] : false);

        //echo "entityId=".$entityId."<br>";

        $em = $this->getDoctrine()->getManager();

        //get site name from abbreviation $sitename
        $userSecUtil = $this->container->get('user_security_utility');
        $sitenameObject = $userSecUtil->getSiteBySitename($sitename);
        if( $sitenameObject ) {
            $sitenameFull = $sitenameObject->getName();
        } else {
            $sitenameFull = "All Sites";
        }

        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:Roles'] by [Roles::class]
        $roles = $em->getRepository(Roles::class)->findAll();
        $rolesArr = array();
        //if( $this->isGranted('ROLE_SCANORDER_ADMIN') ) {
            foreach( $roles as $role ) {
                $rolesArr[$role->getName()] = $role->getAlias();
            }
        //}

        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:Logger'] by [Logger::class]
        $repository = $this->getDoctrine()->getRepository(Logger::class);
        $dql = $repository->createQueryBuilder("logger");

        $dql->innerJoin('logger.eventType', 'eventType');
        $dql->leftJoin('logger.objectType', 'objectType');
        $dql->leftJoin('logger.site', 'site');

        $dql = $this->addCustomDql($dql);

        //$dql->where("logger.entityId IS NOT NULL AND loggerEntity IS NULL");

        if( $sitename ) {
            if ($allsites == null || $allsites == false) {
                //echo "sitename=".$sitename."<br>";
                //$dql->andWhere("logger.siteName = '".$sitename."'");
                //$dql->andWhere("site.abbreviation = '".$sitename."' OR logger.siteName ='".$sitename."'");
                $dql->andWhere("site.abbreviation = '" . $sitename . "'");
            }
        }

        $createLogger = null;
        $updateLogger = null;
        //exit('111111');

        //test
        //$entityNamespace = " ";
        //$entityName = " ";
        //$entityId = 2;

        //get only specific object log
        if( $entityNamespace && $entityName && $entityId ) {
            //'App\UserdirectoryBundle\Entity'
            //$namepartsArr = explode("\\", $entityNamespace);
            //$repName = $namepartsArr[0].$namepartsArr[1];
            //echo "entityNamespace=".$entityNamespace."<br>";
            //echo "0=".$namepartsArr[0]."<br>";
            //$subjectUser = $em->getRepository($repName.':'.$entityName)->find($entityId);

            $queryParameters = array( 'entityNamespace'=>$entityNamespace, 'entityName'=>$entityName, 'entityId'=>"'".$entityId."'" );

            $dql->andWhere('logger.entityNamespace = :entityNamespace');
            $dql->andWhere('logger.entityName = :entityName');
            //$dql->andWhere('logger.objectType = :objectType');

            //exit('22222');
            $dql->andWhere("logger.entityId = :entityId");

            if( $onlyheader ) {

                /////////////// get created info ///////////////
                $dql2 = clone $dql;
                $dql2->andWhere("eventType.name = 'New user record added'");
                $dql2->orderBy("logger.id","ASC");
                //echo "dql2=".$dql2."<br>";

                //$query2 = $em->createQuery($dql2);
                $query2 = $dql2->getQuery();
                $query2->setParameters( $queryParameters );
                $query2->setMaxResults(1);

                $loggers = $query2->getResult();
                //echo "logger count=".count($loggers)."<br>";
                if( count($loggers) > 0 ) {
                    $createLogger = $loggers[0];
                    //echo "logger id=".$createLogger->getId()."<br>";
                    //echo "logger eventType=".$createLogger->getEventType()->getName()."<br>";
                }

                /////////////// get updated info ///////////////
                $dql3 = clone $dql;
                $dql3->andWhere("eventType.name = 'User record updated'");
                $dql3->orderBy("logger.id","DESC");
                //echo "dql2=".$dql3."<br>";

                //$query3 = $em->createQuery($dql3);
                $query3 = $dql3->getQuery();
                $query3->setParameters( $queryParameters );
                $query3->setMaxResults(1);
                $loggers = $query3->getResult();
                //echo "logger count=".count($loggers)."<br>";
                if( count($loggers) > 0 ) {
                    $updateLogger = $loggers[0];
                }

                return array(
                    'roles' => $rolesArr,
                    'sitename' => $sitename,            //fellapp
                    'sitenameFull' => $sitenameFull,    //fellowship-applications
                    'createLogger' => $createLogger,
                    'updateLogger' => $updateLogger
                );

            } //if onlyheader

        } //if entityNamespace entityName entityId

        //add OR to get records with this eventStr in the event title (field "event")
        if( $eventStr ) {
            $dql->orWhere("logger.event LIKE :eventStr");
            $queryParameters['eventStr'] = '%'.$eventStr.'%';
        }

        if( $allsites ) {
            if( $entityId && $entityName == "User" ) {
                if( strval($entityId) == strval(intval($entityId)) ) {
                    //all activities by this user: logger.user = $entityId
                    $dql->orWhere("logger.user = :subjectUser");
                    $queryParameters['subjectUser'] = $entityId;
                }
            }
        }

        if( $postData == null ) {
//            if( $request == null ) {
//                $request = $this->container->get('request');
//            }
		    $postData = $request->query->all();
        }

		if( !isset($postData['sort']) ) { 
			$dql->orderBy("logger.creationdate","DESC");
		}

        $filterRes = $this->processLoggerFilter($dql,$request,$params);
        $filterform = $filterRes['form'];
        $dqlParameters = $filterRes['dqlParameters'];
        $filtered = $filterRes['filtered'];
        //print_r($dqlParameters);

		//pass sorting parameters directly to query; Somehow, knp_paginator stoped correctly create pagination according to sorting parameters       
//		if( isset($postData['sort']) ) {
//            $dql = $dql . " ORDER BY $postData[sort] $postData[direction]";
//        }
		
        $limit = 30;
        $query = $dql->getQuery(); //$query = $em->createQuery($dql);

        //echo "dql=".$dql."<br>";
        //echo "dql=".$query->getSql()."<br>";

        if( $entityNamespace && $entityName && $entityId ) {
            //$query->setParameters( $queryParameters );
            //add parameters
            $dqlParameters = array_merge($queryParameters, $dqlParameters);
        }

        if( count($dqlParameters) > 0 ) {
            $query->setParameters( $dqlParameters );
        }

        //dump($dqlParameters);
        //$logs = $query->getResult();
        //echo "logs=".count($logs)."<br>";
        //exit('111');

        $paginator  = $this->container->get('knp_paginator');
        $pagination = $paginator->paginate(
            $query,
            $request->query->get('page', 1), /*page number*/
            $limit                          /*limit per page*/
        );
        //echo "<br>pagination=".count($pagination)."<br>";

//        foreach( $pagination as $logger ) {
//            echo "logger entity = ". $logger->getEntityName() . " " .$logger->getEntityId() . "<br>";
//            //echo "record logger = ". $logger['loggerEntity']['entityName'] . "<br>";
//            //echo "loggerEntity = (". $logger['loggerEntity'] . ")<br>";
//            //print_r($row);
//        }

        $eventlogTitle = $this->getParameter('eventlog_title');
//        if( $filtered ) {
//            $eventlogTitle = $eventlogTitle . " showing " . count($pagination) . " matching event(s)";
//        }
//        $eventlogTitle = $eventlogTitle . " (total matching " . $pagination->getTotalItemCount() . ")";
        //showing 3 of 5 matching event(s)
        $eventlogTitle = $eventlogTitle . " showing " . count($pagination) . " of " . $pagination->getTotalItemCount() . " matching event(s)";

        $route = $request->get('_route');
        //echo "route=".$route."<br>";

        return array(
            'filterform' => $filterform,
            'loggerfilter' => $filterform->createView(),
            'pagination' => $pagination,
            'roles' => $rolesArr,
            'sitename' => $sitename,            //fellapp
            'sitenameFull' => $sitenameFull,    //fellowship-applications
            'createLogger' => $createLogger,
            'updateLogger' => $updateLogger,
            'filtered' => $filtered,
            'routename' => $route,
            'userid' => $entityId,
            //'titlePostfix' => " event(s)",
            'eventLogTitle' => $eventlogTitle
        );
    }

    public function addCustomDql($dql) {
        $dql->select('logger');
        return $dql;
    }



    public function processLoggerFilter( $dql, $request, $params ) {

        //$params = array();
        $dqlParameters = array();
        $filterRes = array();

        $filtered = false;

        //////////////////// get list of users with "unknown" user ////////////////////
        $em = $this->getDoctrine()->getManager();
        $repository = $this->getDoctrine()->getRepository(User::class);
        $dqlFilterUser = $repository->createQueryBuilder('user');
        $dqlFilterUser->select('user');
        $dqlFilterUser->leftJoin("user.infos","infos");
        //$dqlFilterUser->where("user.keytype IS NOT NULL");
        $dqlFilterUser->orderBy("infos.lastName","ASC");
        //$queryFilterUser = $em->createQuery($dqlFilterUser);
        $queryFilterUser = $dqlFilterUser->getQuery();
        $filterUsers = $queryFilterUser->getResult();
        //echo "count=".count($filterUsers)."<br>";
        //add unknown dummy user
        $unknown = new User();
        $unknown->setDisplayName("unknown");
        $em->persist($unknown);
        //$filterUsers[] = $unknown;
        array_unshift($filterUsers, $unknown);
        $params['filterUsers'] = $filterUsers;
        //////////////////// EOF get list of users with "unknown" user ////////////////////

        //Start Date, Start Time, End Date, End Time, User [Select2 dropdown), Event Type [Entity Updated], [Free Text Search value for Event column] [Filter Button]
        //$filterform = $this->createForm(new LoggerFilterType($params), null);
        $filterform = $this->createLoggerFilter($request,$params);

        //$filterform->submit($request);
        $filterform->handleRequest($request);

        $startdate = $filterform['startdate']->getData();
        $enddate = $filterform['enddate']->getData();
        $search = $filterform['search']->getData();
        $eventTypes = $filterform['eventType']->getData();

        $ip = $filterform['ip']->getData();
        $roles = $filterform['roles']->getData();
        $objectTypes = $filterform['objectType']->getData();
        $objectId = $filterform['objectId']->getData();

        if( isset($filterform['sites']) ) {
            $sites = $filterform['sites']->getData();
        } else {
            $sites = null;
        }

        $currentUser = $this->getUser();

        //echo "eventTypes=".$eventTypes."<br>";
        //foreach( $eventTypes as $eventType ) {
        //    echo "eventType=".$eventType."<br>";
        //}
        //exit("process loger filter");

        if( $search ) {
            $dql->andWhere("LOWER(logger.event) LIKE LOWER(:searchEvent)");
            $dqlParameters['searchEvent'] = '%'.$search.'%';

            $filtered = true;
        }

//        $users = $filterform['user']->getData();
//        if( $users && count($users) > 0 ) {
//            $where = "";
//            foreach( $users as $user ) {
//                if( $where != "" ) {
//                    $where .= " OR ";
//                }
//                if( $user->getId() ) {
//                    $where .= "logger.user = :loggerUser";
//                    $dqlParameters['loggerUser'] = $user->getId();
//                } else {
//                    $where .= "logger.user.id IS NULL";
//                }
//            }
//            $dql->andWhere($where);
//
//            $filtered = true;
//        }

        if( $eventTypes && count($eventTypes)>0 ) {
            //echo "eventTypes=".$eventTypes[0]."<br>";
            //exit();
            $where = "";
            foreach( $eventTypes as $eventType ) {
                if( $eventType->getId() ) {
                    if( $where != "" ) {
                        $where .= " OR ";
                    }
                    $where .= "eventType.id=".$eventType->getId();
                }
            }
            $dql->andWhere($where);

            $filtered = true;
        }

        if( $startdate ) {
            $dql->andWhere("logger.creationdate >= :startdate");

            $startdate = $this->convertFromUserTimezonetoUTC($startdate,$currentUser);
            $dqlParameters['startdate'] = $startdate;

            $filtered = true;
        }

        if( $enddate ) {
            $dql->andWhere("logger.creationdate <= :enddate");

            $enddate = $this->convertFromUserTimezonetoUTC($enddate,$currentUser);
            $dqlParameters['enddate'] = $enddate;

            $filtered = true;
        }

        if( $ip ) {
            $dql->andWhere("logger.ip LIKE :ip");
            $dqlParameters['ip'] = '%'.$ip.'%';

            $filtered = true;
        }

        if( $objectTypes ) {
            $objectTypeStr = "";
            foreach( $objectTypes as $objectType ) {
                //echo "objectType=".$objectType."<br>";
                if( $objectTypeStr ) {
                    $objectTypeStr .= " OR ";
                }
                $objectTypeStr .= "logger.objectType = " . $objectType->getId();
            }
            if( $objectTypeStr ) {
                $dql->andWhere($objectTypeStr);
            }
            //$dql->andWhere("logger.objectType = :objectType");
            //$dqlParameters['objectType'] = $objectType;

            $filtered = true;
        }

        if( $objectId ) {
            //echo "objectId=$objectId<br>";
            if( strpos((string)$objectId, ',') !== false ) {
                $objectIdArr = explode(",",$objectId);
                $dql->andWhere("logger.entityId IN (:objectId)");
                $dqlParameters['objectId'] = $objectIdArr;
            } else {
                $dql->andWhere("logger.entityId = :objectId");
                //$dqlParameters['objectId'] = "'".$objectId."'";
                $dqlParameters['objectId'] = $objectId;
            }


            $filtered = true;
        }

        if( $roles && count($roles)>0 ) {
            $where = "";
            foreach( $roles as $role ) {
                if( $role->getId() ) {
                    if( $where != "" ) {
                        $where .= " OR ";
                    }
                    //$where .= 'eventType.roles LIKE %"'.$role->getName().'"%';
                    //$where .= "logger.roles LIKE '%land%'";
                    $where .= "logger.roles LIKE " . "'%".$role->getName()."%'";
                }
            }
            $dql->andWhere($where);

            $filtered = true;
        }

        if( $sites ) {
            $sitesStr = "";
            foreach( $sites as $site ) {
                if( $sitesStr ) {
                    $sitesStr .= " OR ";
                }
                $sitesStr .= "site.id = " . $site->getId();
            }
            if( $sitesStr ) {
                $dql->andWhere($sitesStr);
            }

            $filtered = true;
        }

        //process optional fields by different bundles (i.e. calllog)
        $filtered = $this->processOptionalFields($dql,$dqlParameters,$filterform,$filtered);

//        echo "<pre>";
//        print_r($dqlParameters);
//        echo "</pre>";

        $filterRes['form'] = $filterform;
        $filterRes['dqlParameters'] = $dqlParameters;
        $filterRes['filtered'] = $filtered;

        return $filterRes;
    }

    public function createLoggerFilter($request,$params) {
        //$userid = $params['entityId'];
        $userid = ( array_key_exists('entityId', $params) ? $params['entityId'] : null);
        //echo "userid=".$userid."<br>";

        //disabled
//        $disbaled = false;
//        if( isset($params['disabled']) && $params['disabled'] ) {
//            $disbaled = true;
//        }

        $routename = $request->get('_route');
        //echo "route=".$routename."<br>";
        //Start Date, Start Time, End Date, End Time, User [Select2 dropdown), Event Type [Entity Updated], [Free Text Search value for Event column] [Filter Button]
        return $this->createForm(LoggerFilterType::class, null, array(
            'method' => 'GET',
            //'disabled' => $disbaled,
            'action' => $this->generateUrl($routename, array('id' => $userid)),
            'attr' => array('class'=>'well form-search'),
            'form_custom_value' => $params,
        ));
    }
    public function processOptionalFields( $dql, &$dqlParameters, $filterform, $filtered ) {
        $users = $filterform['user']->getData();
        //echo "LoggerController: user count=".count($users)."<br>";
        if( $users && count($users) > 0 ) {
            $where = "";
            foreach( $users as $user ) {
                //echo "user id=".$user->getId()."<br>";
                if( $where != "" ) {
                    $where .= " OR ";
                }
                if( $user->getId() ) {
                    $where .= "logger.user = :loggerUser";
                    $dqlParameters['loggerUser'] = $user->getId();
                } else {
                    $where .= "logger.user.id IS NULL";
                }
            }
            $dql->andWhere($where);

            $filtered = true;
        }

        return $filtered;
    }


    //convert given datetime from user's timezone to UTC. Use UTC in DB query. 12:00 => 17:00 +5
    public function convertFromUserTimezonetoUTC($datetime,$user) {

        //$user_tz = 'America/New_York';
        $user_tz = $user->getPreferences()->getTimezone();

        //echo "input datetime=".$datetime->format('Y-m-d H:i')."<br>";
        $datetimeTz = new \DateTime($datetime->format('Y-m-d H:i'), new \DateTimeZone($user_tz) );
        $datetimeUTC = $datetimeTz->setTimeZone(new \DateTimeZone('UTC'));
        //echo "output datetime=".$datetimeUTC->format('Y-m-d H:i')."<br>";

        return $datetimeUTC;
    }


    #[Route(path: '/find-subject-entity-by-object-type-id/{action}/{objectNamespace}/{objectType}/{objectId}', name: 'employees_find_subject_entity', methods: ['GET'])]
    public function permissionActionSubjectEntityAction($action, $objectNamespace, $objectType, $objectId) {

        if( false == $this->isGranted('IS_AUTHENTICATED_FULLY') ){
            return $this->redirect( $this->generateUrl('employees-nopermission') );
        }

        $em = $this->getDoctrine()->getManager();

        //App\UserdirectoryBundle\Entity
        //$objectNamespaceArr = explode("\\",$objectNamespace);
        //$objectNamespaceClean = $objectNamespaceArr[0].$objectNamespaceArr[1];

        $objectName = $em->getRepository(EventObjectTypeList::class)->find($objectType);
        if( !$objectName ) {
            throw $this->createNotFoundException('Unable to find EventObjectTypeList by objectType id='.$objectType);
        }

        //$subjectEntity = $em->getRepository($objectNamespaceClean.':'.$objectName)->find($objectId);
        $subjectEntity = $em->getRepository($objectNamespace.'\\'.$objectName)->find($objectId);

        if( $this->isGranted($action,$subjectEntity) ) {
            $res = 1;
        } else {
            $res = 0;
        }

        $response = new Response();
        $response->setContent($res);
        return $response;
    }

    /**
     * Displays an error message for the logger's "Object ID" url
     */
    #[Route(path: '/warning-message/', name: 'logger_warning_message', methods: ['GET'])]
    #[Template('AppUserdirectoryBundle/Logger/warning.html.twig')]
    public function warningLoggerAction(Request $request)
    {
        $message = $request->get('message');

        return array(
            'title' => "Logger Warning Message",
            'message' => $message
        );
    }



    //////////////// Currently not used ////////////////////
    /**
     * Creates a new Logger entity.
     */
    #[Route(path: '/', name: 'employees_logger_create', methods: ['POST'])]
    #[Template('AppUserdirectoryBundle/Logger/new.html.twig')]
    public function createAction(Request $request)
    {
        return $this->createLogger($request,$this->getParameter('employees.sitename'));
    }

    protected function createLogger(Request $request, $sitename) {
        $userSecUtil = $this->container->get('user_security_utility');
        $site = $userSecUtil->getSiteBySitename($sitename);
        $entity = new Logger($site);
        $form = $this->createCreateForm($entity, $sitename);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('logger_show', array('id' => $entity->getId())));
        }

        $this->addFlash(
            'notice',
            'Failed to create log'
        );

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
            'sitename' => $sitename
        );
    }

    /**
     * Creates a form to create a Logger entity.
     *
     * @param Logger $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    protected function createCreateForm(Logger $entity, $sitename)
    {
        $form = $this->createForm(LoggerType::class, $entity, array(
            'action' => $this->generateUrl($sitename.'_logger_create'),
            'method' => 'POST',
        ));

        $form->add('submit', SubmitType::class, array('label' => 'Create'));

        return $form;
    }


    /**
     * Displays a form to create a new Logger entity.
     */
    #[Route(path: '/new', name: 'logger_new', methods: ['GET'])]
    public function newAction()
    {
        $userSecUtil = $this->container->get('user_security_utility');
        $site = $userSecUtil->getSiteBySitename($this->getParameter('employees.sitename'));

        $entity = new Logger($site);
        $form   = $this->createCreateForm($entity,$this->getParameter('employees.sitename'));

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * Finds and displays a Logger entity.
     */
    #[Route(path: '/{id}', name: 'logger_show', methods: ['GET'])]
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:Logger'] by [Logger::class]
        $entity = $em->getRepository(Logger::class)->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Logger entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Displays a form to edit an existing Logger entity.
     */
    #[Route(path: '/{id}/edit', name: 'logger_edit', methods: ['GET'])]
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:Logger'] by [Logger::class]
        $entity = $em->getRepository(Logger::class)->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Logger entity.');
        }

        $editForm = $this->createEditForm($entity);
        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
    * Creates a form to edit a Logger entity.
    *
    * @param Logger $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createEditForm(Logger $entity)
    {
        $form = $this->createForm(LoggerType::class, $entity, array(
            'action' => $this->generateUrl('logger_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));

        $form->add('submit', SubmitType::class, array('label' => 'Update'));

        return $form;
    }
    /**
     * Edits an existing Logger entity.
     */
    #[Route(path: '/{id}', name: 'logger_update', methods: ['PUT'])]
    #[Template('AppUserdirectoryBundle/Logger/edit.html.twig')]
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:Logger'] by [Logger::class]
        $entity = $em->getRepository(Logger::class)->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Logger entity.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $em->flush();

            return $this->redirect($this->generateUrl('logger_edit', array('id' => $id)));
        }

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }
    /**
     * Deletes a Logger entity.
     */
    #[Route(path: '/{id}', name: 'logger_delete', methods: ['DELETE'])]
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:Logger'] by [Logger::class]
            $entity = $em->getRepository(Logger::class)->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find Logger entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('logger'));
    }

    /**
     * Creates a form to delete a Logger entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('logger_delete', array('id' => $id)))
            ->setMethod('DELETE')
            ->add('submit', SubmitType::class, array('label' => 'Delete'))
            ->getForm()
        ;
    }



}
