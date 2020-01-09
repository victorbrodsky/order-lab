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

use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\RedirectResponse;


use App\OrderformBundle\Form\FilterType;
use App\OrderformBundle\Entity\Document;

use App\UserdirectoryBundle\Entity\Logger;
use App\UserdirectoryBundle\Entity\AccessRequest;

//ScanOrder joins Message + Scan
/**
 * Message controller.
 *
 * @Route("/")
 */
class ScanOrderController extends Controller {

    protected $limit = 50;

    /**
     * @Route("/about", name="scan_about_page")
     * @Template("AppUserdirectoryBundle/Default/about.html.twig")
     */
    public function aboutAction( Request $request ) {
        return array('sitename'=>$this->container->getParameter('scan.sitename'));
    }

    /**
     * Lists all Message entities.
     *
     * @Route("/", name="scan_home")
     * @Method("GET")
     * @Template("AppOrderformBundle/Default/home.html.twig")
     */
    public function indexAction( Request $request ) {

        if(
            false == $this->get('security.authorization_checker')->isGranted('ROLE_USER') ||              // authenticated (might be anonymous)
            false == $this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')    // authenticated (NON anonymous)
        ){
            return $this->redirect( $this->generateUrl('login') );
        }

        $unprocessed = $this->getUnprocessedOrders();

        $sliderequests = $this->getUnprocessedSlideRequests();

        //check for active user requests
        $accountreqs = $this->getActiveAccountReq();

        //check for active access requests
        $accessreqs = $this->getActiveAccessReq();

        return array(
            'unprocessed' => $unprocessed,
            'sliderequests' => $sliderequests,
            'accountreqs' => count($accountreqs),
            'accessreqs' => count($accessreqs)
        );
    }



    /**
     * Lists all Message entities.
     *
     * @Route("/my-scan-orders", name="my-scan-orders")
     * @Route("/incoming-scan-orders", name="incoming-scan-orders")
     * @Method("GET")
     * @Template("AppOrderformBundle/ScanOrder/index.html.twig")
     */
    public function orderListAction( Request $request ) {

        $em = $this->getDoctrine()->getManager();

        $routeName = $request->get('_route');
        //echo "routeName=".$routeName."<br>";

        if( $routeName == "incoming-scan-orders" && false === $this->get('security.authorization_checker')->isGranted('ROLE_SCANORDER_PROCESSOR')) {
            return $this->redirect( $this->generateUrl('my-scan-orders') );
        }

        //by user
        $user = $this->get('security.token_storage')->getToken()->getUser();
        //echo "user=".$user;
        if( !is_object($user) ) {
            return $this->redirect( $this->generateUrl('scan-nopermission') );
        }

        if( $routeName == "incoming-scan-orders" ) {
            $services = $this->getServiceFilter();
        } else {
            $orderUtil = $this->get('scanorder_utility');
            $services = $orderUtil->generateUserFilterOptions($user);
        }

//        $adminemail = $this->container->getParameter('scanorder.adminemail');
//        echo "adminemail=".$adminemail."<br>";
        //exit();
        //throw new \Exception( 'Test' );
        //http://knpbundles.com/craue/CraueConfigBundle
        //$this->get('craue_config')->set('ldap_driver_host', 'a.wcmc-ad.net');

        //create filters
        $params = array();
        $params['services'] = $services;
        $params['statuses'] = $this->getStatusFilter($routeName);
        $form = $this->createForm(FilterType::class, null, array ('form_custom_value'=>$params));


        $form->submit($request);  //use bind instead of handleRequest. handleRequest does not get filter data

        $search = $form->get('search')->getData();
        $filter = $form->get('filter')->getData();
        $service = $form->get('service')->getData();
        $page = $request->get('page');

        //service
        //echo "<br>service=".$service."<br>";
        //exit();
        //echo "<br>search=".$search."<br>";

        $increaseMaxExecTime = false;

        if( $search != "" ) {
            return $this->createComplexSearchPage( $form, $routeName, $service, $filter, $search, $page );
        }

        $repository = $this->getDoctrine()->getRepository('AppOrderformBundle:Message');

        $withSearch = true;
        $res = $this->getDQL( $repository, $service, $filter, $search, $routeName, $withSearch );

        $dql = $res['dql'];

        $criteriastr = $res['criteriastr'];
        $showprovider = $res['showprovider'];
        $showproxyuser = $res['showproxyuser'];

        if( $criteriastr != "" ) {
            $dql->where($criteriastr);
        }

        $params = $this->get('request_stack')->getCurrentRequest()->query->all();
        $sort = $this->get('request_stack')->getCurrentRequest()->query->get('sort');

        //echo "sort=".$sort.", page=".$page."<br>";

        if( $routeName == "my-scan-orders" ) {
            if( $sort == '' ) {
                if( $params == null || count($params) == 0 ) {
                    $dql->orderBy("message.orderdate","DESC");
                }
                if( $sort != 'message.oid' ) {
                    $dql->orderBy("message.orderdate","DESC");
                }
            }
        }

        if( $routeName == "incoming-scan-orders" ) {
            if( $sort == '' ) {
                $dql->orderBy("message.priority","DESC");
                $dql->addOrderBy("message.deadline","ASC");
                $dql->addOrderBy("message.orderdate","DESC");
            }
        }

        //pass sorting parameters directly to query; Somehow, knp_paginator stoped correctly create pagination according to sorting parameters
//        if( $sort && $sort != '' ) {
//            $postData = $request->query->all();
//            $dql = $dql . " ORDER BY $postData[sort] $postData[direction]";
//        }

        //echo "order List Action: dql=".$dql;

        if( $increaseMaxExecTime ) {
            $max_exec_time = ini_get('max_execution_time');
            ini_set('max_execution_time', 300); //300 seconds = 5 minutes
        }

        $limit = $this->limit;

        $query = $em->createQuery($dql);
        $paginator  = $this->get('knp_paginator');

        $pagination = $paginator->paginate(
            $query,
            $request->query->get('page', 1),   /*page number*/
            $limit                                          /*limit per page*/
        );
        //exit('1');

        //testing
//        echo "<br>";
//        foreach( $pagination as $page ) {
//            echo "Order ID:".$page[0]->getId()." has Institution Scope ".$page[0]->getInstitution()."(ID ".$page[0]->getInstitution()->getId().")<br>";
//        }
//        $postData = $request->query->all();
//        $pagination->setParam('sort', $postData['sort']);
//        $pagination->setParam('direction', $postData['direction']);
//        $options = $pagination->getPaginatorOptions(); // options given to paginator when paginated
//        print_r($options);
//        $paramss = $pagination->getCustomParameters();
//        print_r($paramss);

        //check for active user requests
        $accountreqs = $this->getActiveAccountReq();

        //check for active access requests
        $accessreqs = $this->getActiveAccessReq();

        $processorComments = $em->getRepository('AppOrderformBundle:ProcessorComments')->findAll();

        if( $increaseMaxExecTime ) {
            ini_set('max_execution_time', $max_exec_time); //set back to the original value
        }

        ////////////////// Testing pagination //////////////////
//        $em    = $this->get('doctrine.orm.entity_manager');
//        $postData = $request->query->all();
//        $dql1   = "SELECT message, COUNT(slides.id) AS slidecount FROM AppOrderformBundle:Message message INNER JOIN message.slide slides GROUP BY message ORDER BY $postData[sort] $postData[direction]";
//        $query1 = $em->createQuery($dql1);
//        echo "dql=".$dql1."<br>";
//        $paginator  = $this->get('knp_paginator');
//        $pagination1 = $paginator->paginate(
//            $query1,
//            $this->get('request')->query->get('page', 1)/*page number*/,
//            10  /*limit per page*/
//        );
//        foreach( $pagination1 as $page ) {
//            //echo "id=".$page->getId()."<br>";
//            echo "id=".$page[0]->getId()."<br>";
//            //echo "1=".$page['slidecount']."<br>";
//            //print_r($page['message']);
//        }
        //exit('end');
        ////////////////// EOF Testing pagination //////////////////

        //echo "<br>pagination count=".count($pagination)."<br>";

        return array(
            'form' => $form->createView(),
            'showprovider' => $showprovider,
            'showproxyuser' => $showproxyuser,
            'pagination' => $pagination,
            'accountreqs' => $accountreqs,
            'accessreqs' => $accessreqs,
            'routename' => $routeName,
            'comments' => $processorComments
        );
    }



    //requirements={"id" = "\d+"}
    /**
     * Deletes a Message entity.
     *
     * @Route("/scan-order/{id}/delete", name="scanorder_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, $id)
    {

        if (false === $this->get('security.authorization_checker')->isGranted('ROLE_SCANORDER_ADMIN')) {
            return $this->redirect( $this->generateUrl('scan-nopermission') );
        }

        $form = $this->createDeleteForm($id);
        $form->submit($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('AppOrderformBundle:Message')->findOneByOid($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find Message entity.');
            }

//            $scan_entities = $em->getRepository('AppOrderformBundle:Imaging')->
//                    findBy(array('scanorder_id'=>$id));

//            $scan_entities = $em->getRepository('AppOrderformBundle:Imaging')->findBy(
//                array('scanorder' => $id)
//            );
            $entity->removeAllChildren();

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('scanorder'));
    }

    /**
     * Change status of message
     *
     * @Route("/scan-order/{id}/status/{status}/", name="scanorder_status")
     * @Method("GET")
     * @Template()
     */
    public function statusAction(Request $request, $id, $status) {

        if( false === $this->get('security.authorization_checker')->isGranted('ROLE_SCANORDER_SUBMITTER') ) {
            return $this->redirect( $this->generateUrl('scan-nopermission') );
        }
        
        $em = $this->getDoctrine()->getManager();

        $user = $this->get('security.token_storage')->getToken()->getUser();

        $orderUtil = $this->get('scanorder_utility');

        //make uppercase: cancel, sumbit, un-cancel (Un-Cancel)
        //$status = str_replace("-"," ",$status);
        $status = ucwords($status);
        //$status = str_replace(" ","-",$status);

        $res = $orderUtil->changeStatus($id, $status, $user);

        if( $res['result'] == 'conflict' ) {   //redirect to amend
            return $this->redirect( $this->generateUrl( 'order_amend', array('id' => $res['oid']) ) );
        }

        if( $res['result'] == 'nopermission' ) {
            return $this->redirect( $this->generateUrl('scan-nopermission') );
        }

        $this->get('session')->getFlashBag()->add('status-changed',$res['message']);

        $referer_url = $request->headers->get('referer');

        return new RedirectResponse($referer_url);
    }

    /**
     * Creates a form to delete a Message entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id)
    {
        return $this->createFormBuilder(array('id' => $id))
            ->add('id', HiddenType::class)
            ->getForm()
        ;
    }
    
    
    /**   
     * @Route("/thanks", name="thanks")
     * 
     * @Template("AppOrderformBundle/ScanOrder/thanks.html.twig")
     */
    public function thanksAction( $oid = '' )
    {    
        
        return $this->render('AppOrderformBundle/ScanOrder/thanks.html.twig',
            array(
                'oid' => $oid
            ));
    }

    public function getStatusFilter($routeName) {
        $em = $this->getDoctrine()->getManager();

//        if( $this->get('security.authorization_checker')->isGranted('ROLE_SCANORDER_PROCESSOR') ) {
        if( $routeName == "incoming-scan-orders" ) {
            $statuses = $em->getRepository('AppOrderformBundle:Status')->findAll();
        } else {
            $repository = $this->getDoctrine()->getRepository('AppOrderformBundle:Status');
            $dql = $repository->createQueryBuilder("status");
            //$dql->where('status.action IS NOT NULL');
            $dql->where("status.name != 'Superseded'");
            $statuses = $dql->getQuery()->getResult();
        }

        $filterType = array();

        //add at the top
        if( $routeName == "incoming-scan-orders" ) {
            $dataReviews = array(
                "All Statuses (except Not Submitted)" => "All Statuses (except Not Submitted)",
            );

            foreach( $dataReviews as $key => $value ) {
                $filterType[$key] = $value;
            }
        }

        //add special cases statuses
        $specials = array(
            "All" => "All Statuses",
            "All Not Filled" => "All Not Filled",
            "All On Hold" => "All On Hold",
            "All Stat" => "All Stat",
            //All Canceled here
            "All Submitted & Amended" => "All Submitted & Amended",
            "All Filled" => "All Filled",
            "All Filled & Not Returned" => "All Filled & Not Returned",
            "All Filled & Returned" => "All Filled & Returned",
            "With New Comments" => "With New Comments",
            "With Comments" => "With Comments",
            "Stat & Not Filled" => "Stat & Not Filled",
            "Stat & Filled" => "Stat & Filled"
        );

        foreach( $specials as $key => $value ) {
            //$filterType[$key] = $value;
            $filterType[$value] = $key; //flipped
            if( $value == "All Stat" ) {
                $filterType["All Canceled"] = "All Canceled";   //add after Not Submitted
            }
        }

        //add statuses from DB
        foreach( $statuses as $status ) {
            //echo "type: id=".$status->getId().", name=".$status->getName()."<br>";
            //$filterType[$status->getId()] = $status->getName(); //flipped
            $filterType[$status->getName()] = $status->getId();
        }

        //add Data Review
        if( $routeName == "incoming-scan-orders" ) {
            $dataReviews = array(
                "No Course Director Link" => "No Course Director Link",
                "No Principal Investigator Link" => "No Principal Investigator Link"
            );

            foreach( $dataReviews as $key => $value ) {
                //$filterType[$key] = $value;
                $filterType[$value] = $key; //flipped
            }
        }

        return $filterType;
    }
    

    public function getServiceFilter() {
        $em = $this->getDoctrine()->getManager();

        if( $this->get('security.authorization_checker')->isGranted('ROLE_SCANORDER_PROCESSOR') ) {
            $statuses = $em->getRepository('AppUserdirectoryBundle:Institution')->findAll(); //filter by Level = 4?
        } 

        //add special cases
        $specials = array(
            "All" => "All Services",          
        );

        $filterType = array();
        foreach( $specials as $key => $value ) {
            $filterType[$key] = $value;
        }

        //add statuses
        foreach( $statuses as $status ) {
            //echo "type: id=".$status->getId().", name=".$status->getName()."<br>";
            $filterType[$status->getId()] = $status->getName();           
        }

        return $filterType;
    }

    //Service filter
    public function addUserServices( $service, $routeName, $user, $criterions ) {

        $criteriastr = "";
        $em = $this->getDoctrine()->getManager();

        if( $this->get('security.authorization_checker')->isGranted('ROLE_SCANORDER_DIVISION_CHIEF') ) {
            return $criteriastr;
        }

        //check if user has Per Site Settings
        $securityUtil = $this->get('order_security_utility');
        $userSiteSettings = $securityUtil->getUserPerSiteSettings($user);
        if( !$userSiteSettings ) {
            return $criteriastr;
        }

        //for "My Orders" get all user services and chief services
        if( $routeName == "my-scan-orders" ) {

            $userServices = $userSiteSettings->getScanOrdersServicesScope();
            //echo "userServices count=".count($userServices)."<br>";

            if( $this->get('security.authorization_checker')->isGranted('ROLE_SCANORDER_SERVICE_CHIEF') ) {
                $chiefServices = $userSiteSettings->getChiefServices();
                //echo "chief services count=".count($chiefServices)."<br>";
                //if( $userServices && count($userServices) > 0 ) {
                //$userServices = array_merge($userServices, $chiefServices);
                    foreach( $chiefServices as $serv ) {
                        if( !$userServices->contains($serv) ) {
                            //echo "add=".$serv."<br>";
                            $userServices->add($serv);
                        }
                    }
                //}
            }

            //echo "final userServices count=".count($userServices)."<br>";
            foreach( $userServices as $service ) {
                if( $service && $service != "" ) {
                    if( $criteriastr != "" ) {
                        $criteriastr .= " OR ";
                    }
                    $criteriastr .= " scanorder.scanOrderInstitutionScope=".$service->getId();
                }
            }//foreach

        }

        //for "Incoming Orders" select only chosen service
        if( $routeName == "incoming-scan-orders" ) {

            if( is_numeric($service)  ) {

                $siteUserService = $em->getRepository('AppUserdirectoryBundle:Institution')->find($service);

                if( !$siteUserService ) {
                    throw new \Exception( 'Unable to find Service '.$service );
                }

                $criteriastr = " scanorder.scanOrderInstitutionScope=".$siteUserService->getId();

            }

        }

        if( $criterions != "" ) {
            if( $criteriastr != "" ) {
                $criteriastr = " OR (" . $criteriastr . ") ";
            }
        } else {
            $criteriastr = " (" . $criteriastr . ") ";
        }

        return $criteriastr;
    }
//    //Service filter
//    public function addUserServices_new( $service, $routeName, $user, $criterions ) {
//
//        $criteriastr = "";
//        $em = $this->getDoctrine()->getManager();
//
//        if( $this->get('security.authorization_checker')->isGranted('ROLE_SCANORDER_DIVISION_CHIEF') ) {
//            return $criteriastr;
//        }
//
//        //check if user has Per Site Settings
//        $securityUtil = $this->get('order_security_utility');
//        $userSiteSettings = $securityUtil->getUserPerSiteSettings($user);
//        if( !$userSiteSettings ) {
//            return $criteriastr;
//        }
//
//        //for "My Orders" get all user services and chief services
//        if( $routeName == "my-scan-orders" ) {
//
//            //TODO: now we have a single ScanOrderInstitutionScope. Before, we had multiple ScanOrder Service Scopes
//            $userScanOrderInstitutionScope = $userSiteSettings->getScanOrderInstitutionScope();
//            $criteriastr .= " scanorder.scanOrderInstitutionScope=".$userScanOrderInstitutionScope->getId();
//
//            if( $this->get('security.authorization_checker')->isGranted('ROLE_SCANORDER_SERVICE_CHIEF') ) {
//                $chiefServices = $userSiteSettings->getChiefServices();
//                if( $userScanOrderInstitutionScope && count($userServices)>0 ) {
//                    //$services = array_merge($userServices, $chiefServices);
//                    foreach( $chiefServices as $serv ) {
//                        $userServices->add($serv);
//                    }
//                }
//            }
//
//            //TODO: now we don't have "Chief of the following Service(s) for Scope" option in "Per Site User Settings Editable by Administrator"
////            foreach( $userServices as $service ) {
////                if( $service && $service != "" ) {
////                    if( $criteriastr != "" ) {
////                        $criteriastr .= " OR ";
////                    }
////                    $criteriastr .= " scanorder.service=".$service->getId();
////                }
////            }//foreach
//
//        }
//
//        //for "Incoming Orders" select only chosen service
//        if( $routeName == "incoming-scan-orders" ) {
//
//            if( is_numeric($service)  ) {
//
//                $siteUserService = $em->getRepository('AppUserdirectoryBundle:Institution')->find($service);
//
//                if( !$siteUserService ) {
//                    throw new \Exception( 'Unable to find Service by id '.$service );
//                }
//
//                $criteriastr = " scanorder.scanOrderInstitutionScope=".$siteUserService->getId();
//
//            }
//
//        }
//
//        if( $criterions != "" ) {
//            if( $criteriastr != "" ) {
//                $criteriastr = " OR (" . $criteriastr . ") ";
//            }
//        } else {
//            $criteriastr = " (" . $criteriastr . ") ";
//        }
//
//        return $criteriastr;
//    }


    /**
     * Finds and displays a unprocessed orders.
     */
    public function getUnprocessedOrders()
    {
        $unprocessed = 0;
        $em = $this->getDoctrine()->getManager();

        /////////// institution ///////////
        $user = $this->get('security.token_storage')->getToken()->getUser();
        $orderUtil = $this->get('scanorder_utility');
        $instStr = $orderUtil->getInstitutionQueryCriterion($user);
//        $instStr = "";
//        foreach( $user->getInstitutions() as $inst ) {
//            if( $instStr != "" ) {
//                $instStr = $instStr . " OR ";
//            }
//            $instStr = $instStr . 'message.institution='.$inst->getId();
//        }
//        if( $instStr == "" ) {
//            $instStr = "1=0";
//        }
        if( $instStr != "" ) {
            $instStr = " AND (" . $instStr . ") ";
        }
        //echo "instStr=".$instStr."<br>";
        /////////// EOF institution ///////////

        $repository = $this->getDoctrine()->getRepository('AppOrderformBundle:Message');
        $dql =  $repository->createQueryBuilder("message");
        $dql->innerJoin("message.status", "status");
        $dql->leftJoin("message.institution", "institution");
        //$dql->innerJoin("message.institution", "institution");

        $dql->where("status.name NOT LIKE '%Filled%' AND status.name NOT LIKE '%Not Submitted%'" . $instStr);

        $query = $em->createQuery($dql);
        $unprocessedOrders = $query->getResult();

        if( $unprocessedOrders && count($unprocessedOrders) > 0 ) {
            $unprocessed = count($unprocessedOrders);
        }

        return $unprocessed;
    }


    /**
     * Finds and displays a unprocessed (active) Slide Return Requests.
     */
    public function getUnprocessedSlideRequests()
    {
        $unprocessed = 0;
        $em = $this->getDoctrine()->getManager();

        //$slideReturnRequest = $em->getRepository('AppOrderformBundle:SlideReturnRequest')->findByStatus('active');

        /////////// institution ///////////
        $user = $this->get('security.token_storage')->getToken()->getUser();
        $orderUtil = $this->get('scanorder_utility');
        $instStr = $orderUtil->getInstitutionQueryCriterion($user);
//        $instStr = "";
//        foreach( $user->getInstitutions() as $inst ) {
//            if( $instStr != "" ) {
//                $instStr = $instStr . " OR ";
//            }
//            $instStr = $instStr . 'message.institution='.$inst->getId();
//        }
//        if( $instStr == "" ) {
//            $instStr = "1=0";
//        }
        if( $instStr != "" ) {
            $instStr = " AND (" . $instStr . ") ";
        }
        //echo "instStr=".$instStr."<br>";
        /////////// EOF institution ///////////

        $repository = $this->getDoctrine()->getRepository('AppOrderformBundle:SlideReturnRequest');
        $dql =  $repository->createQueryBuilder("req");
        $dql->innerJoin("req.message", "message");
        $dql->leftJoin("message.institution", "institution");
        $dql->where("req.status='active'" . $instStr);
        //echo "dql=".$dql;
        $query = $em->createQuery($dql);
        $slideReturnRequest = $query->getResult();

        if( $slideReturnRequest && count($slideReturnRequest) > 0 ) {
            $unprocessed = count($slideReturnRequest);
        }

        return $unprocessed;
    }

    //check for active user requests
    public function getActiveAccountReq() {
        $em = $this->getDoctrine()->getManager();
        $accountreqs = array();
        if( $this->get('security.authorization_checker')->isGranted('ROLE_SCANORDER_PROCESSOR') ) {
            //$accountreqs = $em->getRepository('AppUserdirectoryBundle:UserRequest')->findByStatus("active");
            $accountreqs = $em->getRepository('AppUserdirectoryBundle:UserRequest')->findBy(
                array(
                    "status"=>"active",
                    "siteName"=>$this->container->getParameter('scan.sitename')
                )
            );
        }
        return $accountreqs;
    }

    //check for active access requests
    public function getActiveAccessReq() {
        if( !$this->get('security.authorization_checker')->isGranted('ROLE_SCANORDER_PROCESSOR') ) {
            return null;
        }
        $userSecUtil = $this->get('user_security_utility');
        $accessreqs = $userSecUtil->getUserAccessRequestsByStatus($this->container->getParameter('scan.sitename'),AccessRequest::STATUS_ACTIVE);
        return $accessreqs;
    }


    public function createComplexSearchPage( $form, $routeName, $service, $filter, $search, $page ) {

        $searchObjects = [
            'message.oid',
            'educational.courseTitle',
            'research.projectTitle',
            'provider',
            'proxyuser',
            'directorUser',
            'principalUser',
            'accession',
            'patient.mrn',
            'patient.name',
            'part.disident',
            'part.diffDisident',
            'scan.note',
            'pathistory.field',
            'procedureType.name',
            'sectionsource.field',
            'description.field',
            'slides.microscopicdescr',
            'diseaseType.field',
            'StainList.name',
            'specialStains.field',
            'clinicalHistory.field'
        ];

        return $this->render('AppOrderformBundle/ScanOrder/index-search.html.twig', array(
            'form' => $form->createView(),
//            'showprovider' => $showprovider,
//            'showproxyuser' => $showproxyuser,
//            'pagination' => $pagination,
//            'accountreqs' => $accountreqs,
//            'accessreqs' => $accessreqs,
            'routename' => $routeName,
//            'comments' => $processorComments
            'service' => $service,
            'filter' => $filter,
            'search' => $search,
            'page' => $page,
            'searchObjects' => $searchObjects
        ));
    }

    /**
     * Find accession by #
     * @Route("/scanorder-complex-search", name="scanorder-complex-search")
     * @Method("POST")
     */
    public function getSearchViewAjaxAction( Request $request ) {

        $routename   = $request->get('routename');
        $service   = $request->get('service');
        $filter   = $request->get('filter');
        $search   = $request->get('search');
        $searchObject   = $request->get('searchobject');
        $page   = $request->get('page');

        //echo "routename=".$routename.", search=".$search.", searchObject=".$searchObject."<br>";

        return $this->getSearchViewAction( $request, $routename, $service, $filter, $search, $searchObject, $page );
    }

    //render the search result a single search objects
    public function getSearchViewAction( $request, $routeName, $service, $filter, $search, $searchObject, $page ) {
        $viewArr = $this->getSearchViewArray( $request, $routeName, $service, $filter, $search, $searchObject, $page );

        //////// record to EventLog ////////
        if( !$page || $page == "" ) {
            $em = $this->getDoctrine()->getManager();
            $user = $this->get('security.token_storage')->getToken()->getUser();
            $roles = $user->getRoles();

            $count = count($viewArr['pagination']);
            if( $count == $viewArr['limit'] ) {
                $count = $count . "+";
            }

            $userSecUtil = $this->get('user_security_utility');
            $site = $userSecUtil->getSiteBySitename($this->container->getParameter('scan.sitename'));
            $logger = new Logger($site);
            $logger->setUser($user);
            $logger->setRoles($roles);
            $logger->setUsername($user."");
            $logger->setIp($request->getClientIp());
            $logger->setUseragent($_SERVER['HTTP_USER_AGENT']);
            $logger->setWidth($request->get('display_width'));
            $logger->setHeight($request->get('display_height'));
            $logger->setEvent( 'Search for "' . $search . '" in ' . $viewArr['searchObjectName'] . '. ' . $count . ' results found.' );

            $eventtype = $em->getRepository('AppUserdirectoryBundle:EventTypeList')->findOneByName('Search');
            $logger->setEventType($eventtype);

            $em->persist($logger);
            $em->flush();
        }
        //////// EOF EventLog ////////

        return $this->render('AppOrderformBundle/ScanOrder/one-search-result.html.twig', $viewArr);
    }


    //render the search results for all search objects
    public function getSearchAllViewAction( $request, $routeName, $service, $filter, $search, $searchObjects, $page ) {

        $renderedViewArr = array();

        $resArr = array();

        foreach( $searchObjects as $searchObject ) {
            $viewArr = $this->getSearchViewArray( $request, $routeName, $service, $filter, $search, $searchObject, $page );

            //$renderedView = $this->render('AppOrderformBundle/ScanOrder/one-search-result.html.twig', $viewArr);
            $renderedView = $this->renderView('AppOrderformBundle/ScanOrder/one-search-result.html.twig', $viewArr);

            $renderedViewArr[] = $renderedView;
            $resArr[] = 'Search for "' . $viewArr['search'] . '" in ' . $viewArr['searchObjectName'] . '. ' . count($viewArr['pagination']) . ' results found.';
        }


        //////// record to EventLog ////////
        if( !$page || $page == "" ) {
            $em = $this->getDoctrine()->getManager();
            $user = $this->get('security.token_storage')->getToken()->getUser();
            $roles = $user->getRoles();

            $count = count($viewArr['pagination']);
            if( $count == $viewArr['limit'] ) {
                $count = $count . "+";
            }

            $userSecUtil = $this->get('user_security_utility');
            $site = $userSecUtil->getSiteBySitename($this->container->getParameter('scan.sitename'));

            $logger = new Logger($site);
            $logger->setUser($user);
            $logger->setRoles($roles);
            $logger->setUsername($user."");
            $logger->setIp($request->getClientIp());
            $logger->setUseragent($_SERVER['HTTP_USER_AGENT']);
            $logger->setWidth($request->get('display_width'));
            $logger->setHeight($request->get('display_height'));
            //$logger->setEvent( 'Search for "' . $search . '" in ' . $viewArr['searchObjectName'] . '. ' . $count . ' results found.' );
            $logger->setEvent( implode("<br>",$resArr) );

            $eventtype = $em->getRepository('AppUserdirectoryBundle:EventTypeList')->findOneByName('Search');
            $logger->setEventType($eventtype);

            $em->persist($logger);
            $em->flush();
        }
        //////// EOF EventLog ////////

        return $this->render('AppOrderformBundle/ScanOrder/all-search-result.html.twig', array('views'=>$renderedViewArr));
    }

    public function getSearchViewArray( $request, $routeName, $service, $filter, $search, $searchObject, $page ) {

        $securityUtil = $this->get('order_security_utility');
        $filter = $securityUtil->mysql_escape_mimic($filter);
        $search = $securityUtil->mysql_escape_mimic($search);

        //***************** Search filetr ***************************//
        if( $search == "" ) {
            $viewArr = array(
                'pagination' => array(),
            );
            return $viewArr;
        }

        $em = $this->getDoctrine()->getManager();

        $repository = $this->getDoctrine()->getRepository('AppOrderformBundle:Message');

        $withSearch = false;
        $res = $this->getDQL( $repository, $service, $filter, $search, $routeName, $withSearch );

        $dql = $res['dql'];
        $criteriastrOrig = $res['criteriastr'];
        $showprovider = $res['showprovider'];
        $showproxyuser = $res['showproxyuser'];


        //Start making a search string
        $criteriastr = "";

        $searchStr = " LIKE '%" . $search . "%'";

        //echo "<br>searchObject=".$searchObject."<br>";

        switch( $searchObject ) {
            case 'message.oid':
                //message oid
                //if( is_numeric($search) ) {
                $criteriastr .= "message.oid".$searchStr;
                //}
                $searchObjectName = "Order ID";
                break;
            case 'educational.courseTitle':
                //educational
                //$criteriastr .= "educational.courseTitleStr".$searchStr;
                $dql->leftJoin("educational.courseTitle", "courseTitle");
                $criteriastr .= " courseTitle.name".$searchStr;
                $searchObjectName = "Course Title";
                break;
//            case 'educational.lessonTitleStr':
//                //educational
//                $criteriastr .= "educational.lessonTitleStr".$searchStr;
//                $searchObjectName = "Lesson Title";
//                break;
            case 'research.projectTitle':
                $dql->leftJoin("research.projectTitle", "projectTitle");
                $criteriastr .= "projectTitle.name".$searchStr;
                $searchObjectName = "Research Project Title";
                break;
//            case 'research.setTitleStr':
//                //educational
//                $criteriastr .= "research.setTitleStr".$searchStr;
//                $searchObjectName = "Research Set Title";
//                break;
            case 'provider':
                $criteriastr .= "provider.username".$searchStr;
                $criteriastr .= " OR providerinfos.displayName".$searchStr;
                $searchObjectName = "Submitter";
                break;
            case 'proxyuser':
                $criteriastr .= "proxyuser.username".$searchStr;
                $criteriastr .= " OR proxyuserinfos.displayName".$searchStr;
                $searchObjectName = "Ordering Provider";
                break;
            case 'directorUser':
                //$dql->leftJoin("director.director", "directorUser");
                $dql->leftJoin("directorUser.infos", "directorUserInfos");
                $criteriastr .= "directorUser.username".$searchStr;
                $criteriastr .= "OR directorUserInfos.displayName".$searchStr;
                $searchObjectName = "Course Director";
                break;
            case 'principalUser':
                //$dql->leftJoin("principal.principal", "principalUser");
                $dql->leftJoin("principalUser.infos", "principalUserInfos");
                $criteriastr .= "principalUser.username".$searchStr;
                $criteriastr .= " OR principalUserInfos.displayName".$searchStr;
                $searchObjectName = "Principal Investigator";
                break;
            case 'accession':
                $dql->leftJoin("message.accession", "accessionObj");
                $dql->leftJoin("accessionObj.accession", "accession");
                $criteriastr .= "accession.field".$searchStr;
                $searchObjectName = "Accession Number";
                break;
            case 'patient.mrn':
                $dql->leftJoin("message.patient", "patient");
                $dql->leftJoin("patient.mrn", "mrn");
                $criteriastr .= "mrn.field".$searchStr;
                $searchObjectName = "MRN";
                break;
            case 'patient.name':
                $dql->leftJoin("message.patient", "patient");
                $dql->leftJoin("patient.lastname", "lastname");
                $dql->leftJoin("patient.firstname", "firstname");
                $dql->leftJoin("patient.middlename", "middlename");
                $criteriastr .= "lastname.field".$searchStr;
                $criteriastr .= "OR firstname.field".$searchStr;
                $criteriastr .= "OR middlename.field".$searchStr;
                $searchObjectName = "Patient Name";
                break;
            case 'part.disident':
                $dql->leftJoin("message.part", "part");
                $dql->leftJoin("part.disident", "disident");
                $criteriastr .= "disident.field".$searchStr;
                $searchObjectName = "Diagnosis";
                break;
            case 'part.diffDisident':
                $dql->leftJoin("message.part", "part");
                $dql->leftJoin("part.diffDisident", "diffDisident");
                $criteriastr .= "diffDisident.field".$searchStr;
                $searchObjectName = "Differential Diagnoses";
                break;
            case 'scan.note':
                $dql->leftJoin("slides.scan", "scan");
                $criteriastr .= "scan.note".$searchStr;
                $searchObjectName = "Reason for Scan/Note";
                break;
            case 'pathistory.field':
                $dql->innerJoin("message.encounter", "encounter");
                $dql->leftJoin("encounter.pathistory", "pathistory");
                $criteriastr .= "pathistory.field".$searchStr;
                $searchObjectName = "Clinical History";
                break;
            case 'procedureType.name':
                $dql->innerJoin("message.procedure", "procedure");
                $dql->leftJoin("procedure.name", "procedureName");
                $dql->leftJoin("procedureName.field", "procedureType");
                $criteriastr .= "procedureType.name".$searchStr;
                $searchObjectName = "Procedure Type";
                break;
            case 'sectionsource.field':
                $dql->leftJoin("message.block", "block");
                $dql->leftJoin("block.sectionsource", "sectionsource");
                $criteriastr .= "sectionsource.field".$searchStr;
                $searchObjectName = "Source Organ";
                break;
            case 'description.field':
                //part Gross Description
                $dql->leftJoin("message.part", "part");
                $dql->leftJoin("part.description", "description");
                $criteriastr .= "description.field".$searchStr;
                $searchObjectName = "Gross Description";
                break;
            case 'slides.microscopicdescr':
                $criteriastr .= "slides.microscopicdescr".$searchStr;
                $searchObjectName = "Microscopic Description";
                break;
            case 'diseaseType.field':
                $dql->leftJoin("message.part", "part");
                $dql->leftJoin("part.diseaseType", "diseaseType");
                $criteriastr .= "diseaseType.field".$searchStr;
                $searchObjectName = "Disease Type";
                break;
            case 'StainList.name':
                $dql->innerJoin("slides.stain", "stain");
                $dql->leftJoin("stain.field", "StainList");
                $criteriastr .= "StainList.name".$searchStr;
                $searchObjectName = "Stain Name";
                break;
            case 'specialStains.field':
                //Special Stain Results (both stain name and the result field)
                $dql->leftJoin("message.block", "block");
                $dql->leftJoin("block.specialStains", "specialStains");
                $dql->leftJoin("specialStains.staintype", "specialStainsStainList");
                $criteriastr .= "specialStainsStainList.name".$searchStr;
                $criteriastr .= " OR specialStains.field".$searchStr;
                $searchObjectName = "Special Stain Results";
                break;
            case 'clinicalHistory.field':
                //Clinical Summary
                $dql->leftJoin("message.patient", "patient");
                $dql->leftJoin("patient.clinicalHistory", "clinicalHistory");
                $criteriastr .= "clinicalHistory.field".$searchStr;
                $searchObjectName = "Clinical Summary";
                break;
            default:
                $searchObjectName = "";
                //echo "searchObject is not found = ".$searchObject."<br>";
        }

        //$criteriastr .= " ) ";

        $increaseMaxExecTime = true;

        if( $criteriastr != "" ) {

            if( $criteriastrOrig != "" ) {
                $criteriastrOrig = $criteriastrOrig . " AND ( " . $criteriastr . " ) ";
            } else {
                $criteriastrOrig = $criteriastr;
            }

            $dql->where($criteriastrOrig);
        }

        //$dql->where($criteriastrOrig);
        //$dql->where("1=1");

        $params = $this->get('request_stack')->getCurrentRequest()->query->all();
        $sort = $this->get('request_stack')->getCurrentRequest()->query->get('sort');

        if( $routeName == "my-scan-orders" ) {
            if( $params == null || count($params) == 0 ) {
                $dql->orderBy("message.orderdate","DESC");
            }
            if( $sort != 'message.oid' ) {
                $dql->orderBy("message.orderdate","DESC");
            }
        }

        if( $routeName == "incoming-scan-orders" ) {
            if( $sort == '' ) {
                $dql->orderBy("message.priority","DESC");
                $dql->addOrderBy("message.deadline","ASC");
                $dql->addOrderBy("message.orderdate","DESC");
            }
        }

        //echo "<br>dql=".$dql."<br>";

        if( $increaseMaxExecTime ) {
            $max_exec_time = ini_get('max_execution_time');
            ini_set('max_execution_time', 300); //300 seconds = 5 minutes
        }

        $limit = $this->limit;

        if( !$page && $page == "" ) {
            $page = 1;
        }

        $query = $em->createQuery($dql);

        $paginator  = $this->get('knp_paginator');

        $pagination = $paginator->paginate(
            $query,
            $request->query->get('page', $page), /*page number*/
            $limit  /*limit per page*/
        );

        //echo "<br>".$searchObjectName.": count=".count($pagination)."<br>";

        //check for active user requests
        $accountreqs = $this->getActiveAccountReq();

        //check for active access requests
        $accessreqs = $this->getActiveAccessReq();

        $processorComments = $em->getRepository('AppOrderformBundle:ProcessorComments')->findAll();

        if( $increaseMaxExecTime ) {
            ini_set('max_execution_time', $max_exec_time); //set back to the original value
        }

//        if( $searchObject == "accession" ) {
//            //echo "dql=".$dql."<br>";
//
//        }
        //echo "<br>".$searchObjectName.": count=".count($pagination)."<br>";


        $viewArr = array(
            'showprovider' => $showprovider,
            'showproxyuser' => $showproxyuser,
            'pagination' => $pagination,
            'accountreqs' => $accountreqs,
            'accessreqs' => $accessreqs,
            'routename' => $routeName,
            'comments' => $processorComments,
            'searchObjectName' => $searchObjectName,
            'search' => $search,
            'limit' => $limit
        );

//        return $this->render('AppOrderformBundle/ScanOrder/one-search-result.html.twig', array(
//            //'form' => $form->createView(),
//            'showprovider' => $showprovider,
//            'showproxyuser' => $showproxyuser,
//            'pagination' => $pagination,
//            'accountreqs' => $accountreqs,
//            'accessreqs' => $accessreqs,
//            'routename' => $routeName,
//            'comments' => $processorComments,
//            'searchObjectName' => $searchObjectName,
//            'search' => $search
//        ));

        return $viewArr;
    }


    public function getDQL( $repository, $service, $filter, $search, $routeName, $withSearch = false ) {

        $securityUtil = $this->get('order_security_utility');
        $filter = $securityUtil->mysql_escape_mimic($filter);
        $search = $securityUtil->mysql_escape_mimic($search);

        $user = $this->get('security.token_storage')->getToken()->getUser();

        if( $routeName == "incoming-scan-orders" ) {
            $commentFlag = 'admin';
        } else {
            $commentFlag = null;
        }

        $dql = $repository->createQueryBuilder("message");

        //innerJoin must exist, otherwise empty result will be returned
        $dql->innerJoin("message.slide", "slides");
        $dql->innerJoin("message.provider", "provider");
        $dql->innerJoin("message.messageCategory", "messageCategory");
        $dql->innerJoin("message.status", "status");

        $dql->leftJoin("provider.infos", "providerinfos");

        $dql->leftJoin("message.proxyuser", "proxyuserWrapper");
        $dql->leftJoin("proxyuserWrapper.user", "proxyuser");
        $dql->leftJoin("proxyuser.infos", "proxyuserinfos");

        $dql->leftJoin("message.destinations", "destinations");
        $dql->leftJoin("message.scanorder", "scanorder");
        $dql->leftJoin("destinations.location", "destinationlocation");

        $dql->leftJoin("message.institution", "institution");

        $dql->select('message, COUNT(slides.id) AS slidecount');

        $dql->groupBy('message');
        $dql->addGroupBy('status.name');
        $dql->addGroupBy('messageCategory.name');
        $dql->addGroupBy('provider.username');

        //$dql->having("( (COUNT(message) > 1) AND (COUNT(status.name) > 1) AND (COUNT(messageCategory.name) > 1) AND (COUNT(provider.username) > 1) )");
        //$dql->having("( COUNT(message) > 1 )");

        $dql->leftJoin("message.history", "history"); //history might not exist, so use leftJoin
        $dql->leftJoin("history.eventtype", "eventtype");

        $dql->leftJoin("message.educational", "educational");
        $dql->leftJoin("educational.userWrappers", "directorUserWrappers");
        $dql->leftJoin("directorUserWrappers.user", "directorUser");

        $dql->leftJoin("message.research", "research");
        $dql->leftJoin("research.userWrappers", "userWrappers");
        $dql->leftJoin("userWrappers.user", "principalUser");


        $institution = false;

        //$increaseMaxExecTime = false;

        $criteriastr = "";

        //filter by message category
        $criteriastr .= "(";
        $criteriastr .= "messageCategory.name LIKE '%Scan Order%'";
        $criteriastr .= ")";


        //***************** Pathology Service filetr ***************************//
        $showprovider = 'false';
        $showproxyuser = 'false';

        //***************** Status filetr ***************************//
        //echo "status filter = ".$filter."<br>";
        if( $filter && is_numeric($filter) && $filter > 0 ) {

            //echo "numeric filter=".$filter;
            if( $criteriastr != "" ) {
                $criteriastr .= " AND ";
            }
            $criteriastr .= " status.id=" . $filter;
        }

        //filter special cases
        if( is_string($filter) || $filter == "" ) {

            //echo "string filter=".$filter;

            $filterStr = "";
            switch( $filter ) {

                case "With New Comments":
                    $orderUtil = $this->get('scanorder_utility');
                    $newCommentsCriteriaStr = "( " . $orderUtil->getCommentsCriteriaStr('new_comments',$commentFlag) . " ) ";
                    $filterStr = $newCommentsCriteriaStr;
                    break;
                case "With Comments":
                    $orderUtil = $this->get('scanorder_utility');
                    $newCommentsCriteriaStr = "( " . $orderUtil->getCommentsCriteriaStr('all_comments',$commentFlag) . " ) ";
                    $filterStr = $newCommentsCriteriaStr;
                    break;
                case "All":
                    break;
                case "":
                    if( $routeName == "incoming-scan-orders" ) {
                        $filterStr = " status.name != 'Not Submitted'";
                    }
                    break;
                case "All Statuses (except Not Submitted)":
                    $filterStr = " status.name != 'Not Submitted' AND status.name != 'Superseded'";
                    break;
                case "All Filled":
                    $filterStr = " status.name LIKE '%Filled%'";
                    break;
                case "All Filled & Returned":
                    $filterStr = " status.name LIKE '%Filled%' AND status.name LIKE '%Returned%'";
                    break;
                case "All Filled & Not Returned":
                    $filterStr = " status.name LIKE '%Filled%' AND status.name NOT LIKE '%Returned%'";
                    break;
                case "All Not Filled":
                    $filterStr = " status.name NOT LIKE '%Filled%' AND status.name NOT LIKE '%Canceled%' AND status.name != 'Not Submitted' AND status.name != 'Superseded' ";
                    break;
                case "All On Hold":
                    $filterStr = " status.name LIKE '%On Hold%'";
                    break;
                case "All Canceled":
                    $filterStr = " status.name = 'Canceled by Submitter' OR status.name = 'Canceled by Processor'";
                    break;
                case "All Submitted & Amended":
                    $filterStr = " status.name = 'Submitted' OR status.name = 'Amended'";
                    break;
                case "All Stat":
                    $filterStr = " message.priority = 'Stat'";
                    break;
                case "Stat & Not Filled":
                    $filterStr = " message.priority = 'Stat' AND status.name NOT LIKE '%Filled%'";
                    break;
                case "Stat & Filled":
                    $filterStr = " message.priority = 'Stat' AND status.name LIKE '%Filled%'";
                    break;
                case "No Course Director Link":
                    $filterStr = " educational IS NOT NULL AND directorUser.id IS NULL AND status.name != 'Superseded'";
                    break;
                case "No Principal Investigator Link":
                    $filterStr = " research IS NOT NULL AND principalUser.id IS NULL AND status.name != 'Superseded'";
                    break;
                default:
                    ;
            }

            if( $filterStr != "" ) {
                $filterStr = " (". $filterStr .") ";
            }

            if( $criteriastr != "" && $filterStr != "" ) {
                $criteriastr .= " AND ". $filterStr ." ";
            } else {
                $criteriastr .= $filterStr;
            }

        }
        //***************** END of Status filetr ***************************//

        //***************** Superseded filter ***************************//
        if( false === $this->get('security.authorization_checker')->isGranted('ROLE_SCANORDER_PROCESSOR') ) {
            //$superseded_status = $em->getRepository('AppOrderformBundle:Status')->findOneByName('Superseded');
            if( $criteriastr != "" ) {
                $criteriastr .= " AND ";
            }
            $criteriastr .= " status.name != 'Superseded'";
        }
        //***************** END of Superseded filetr ***************************//

        //***************** User filter ***************************//
        if( $routeName == "my-scan-orders" ) {

            $crituser = "";

            //echo $routeName.": service=".$service."<br>";
            //select only orders where this user is author or proxy user, except "Where I am the Course Director" and "Where I am the Principal Investigator" cases.
            if( $service == "" || $service == "My Orders" ) {

                //show only my order and the orders where I'm a proxy
                //Where I am the Submitter and Where I am the Ordering Provider: $service == "My Orders"

                $crituser .= "( provider.id=".$user->getId();

                //***************** Proxy User Orders *************************//
                $crituser .= " OR proxyuser.id=".$user->getId();
                //***************** END of Proxy User Orders *************************//

                //***************** service filter: show all orders with chosen service matched with current user's service *****************//
                $allservices = $this->addUserServices( $service, $routeName, $user, $crituser );
                if( $allservices != "" ) {
                    $showprovider = 'true';
                    $crituser .= $allservices;
                }
                //***************** EOF: service filter: show all orders with chosen service matched with current user's service *****************//

                $crituser .= " )";
            }

            //show all for ROLE_SCANORDER_DIVISION_CHIEF: remove all user's restriction
            if( $this->get('security.authorization_checker')->isGranted('ROLE_SCANORDER_DIVISION_CHIEF') ) {
                //echo "ROLE_SCANORDER_DIVISION_CHIEF";
                $crituser = "";
            }

            if( $service == "Where I am the Submitter" ) {
                //echo "Where I am the Submitter <br>";
                if( $crituser != "" ) {
                    $crituser .= " AND ";
                }
                $crituser .= "provider.id=".$user->getId();
            }
            if( $service == "Where I am the Ordering Provider" ) {
                //echo "Where I am the Ordering Provider <br>";
                if( $crituser != "" ) {
                    $crituser .= " AND ";
                }
                //***************** Proxy User Orders *************************//
                $crituser .= "proxyuser.id=".$user->getId();
                //***************** END of Proxy User Orders *************************//
            }
            if( $service == "Where I am the Course Director" ) {
                if( $crituser != "" ) {
                    $crituser .= " AND ";
                }
                $crituser .= "directorUser.id=".$user->getId();
            }
            if( $service == "Where I am the Principal Investigator" ) {
                if( $crituser != "" ) {
                    $crituser .= " AND ";
                }
                $crituser .= "principalUser.id=".$user->getId();
            }
            //use history to get amended author
            if( $service == "Where I am the Amendment Author" ) {
                if( $crituser != "" ) {
                    $crituser .= " AND ";
                }
                $crituser .= "history.provider=".$user->getId()." AND eventtype.name='Amended Order Submission'";
            }

            //"All ".$service->getName()." Orders"; => $service is service's id
            if( is_int($service) ) {
                //echo "service=".$service."<br>";
                $showprovider = 'true';
                if( $crituser != "" ) {
                    $crituser .= " AND ";
                }
                $crituser .= "scanorder.scanOrderInstitutionScope=".$service;
            }

            //show chosen collaboration institution
            $institution = false;
            if( strpos($service,'collaborationkey') !== false ) {
                $pieces = explode("-", $service);
                $institutionId = $pieces[1];
                //echo "collaboration institutionId=".$institutionId."<br>";

                $em = $this->getDoctrine()->getManager();
                $node = $em->getRepository('AppUserdirectoryBundle:Institution')->find($institutionId);
                //echo "inst=".$node."<br>";

                $institutionalCriteriaStr = $em->getRepository('AppUserdirectoryBundle:Institution')->selectNodesUnderParentNode( $node, "institution", false );


                //add collaboration inst
                $rootNode = $em->getRepository('AppUserdirectoryBundle:Institution')->find($node->getRoot());
                //echo "rootNode=".$rootNode."<br>";

                //All Collaboration => get all children and their institutions
                $allInstitutionalCriteriaArr = array();
                if( $rootNode->getName()."" == "All Collaborations" ) {
                    $childrenNodes = $em->getRepository('AppUserdirectoryBundle:Institution')->getChildren($rootNode);
                    //echo "childrenNodes count=".count($childrenNodes)."<br>";
                    foreach( $childrenNodes as $childrenNode ) {
                        foreach( $childrenNode->getCollaborationInstitutions() as $collInst ) {
                            //echo "collInst=".$collInst."<br>";
                            $allInstitutionalCriteriaArr[] = $em->getRepository('AppUserdirectoryBundle:Institution')->selectNodesUnderParentNode( $collInst, "institution", false );
                        }
                    }
                }

                //All Institutions => disregard institutions => show all institutions
                if( $rootNode->getName()."" == "All Institutions" ) {
                    $institutionalCriteriaStr = "";
                }

                if( $institutionalCriteriaStr && count($allInstitutionalCriteriaArr) > 0 ) {
                    $institutionalCriteriaStr = $institutionalCriteriaStr . " OR " . implode(" OR ", $allInstitutionalCriteriaArr);
                }

                if( $institutionalCriteriaStr ) {
                    if ($crituser != "") {
                        $crituser .= " AND ";
                    }
                    $crituser .= "(" . $institutionalCriteriaStr . ")";
                    //echo "crituser=".$crituser."<br>";

                    $institution = true;
                }
            }

            if( $criteriastr != "" && $crituser != "" ) {
                $criteriastr = $criteriastr." AND ".$crituser;
            } else {
                $criteriastr .= $crituser;
            }

        }
        //***************** END of User filetr ***************************//

        if( $routeName == "incoming-scan-orders" ) {
            //echo "admin index filter <br>";
            //***************** Data Review filter ***************************//
            //            "No Course Director Link" => "No Course Director Link",
            //            "No Principal Investigator Link" => "No Principal Investigator Link"
            //***************** End of Service filter ***************************//

            //filter by service
            $critservice = "";
            if( is_int($service) ) {
                //echo "service=".$service."<br>";
                $showproxyuser = 'true';
                $critservice = "scanorder.scanOrderInstitutionScope=".$service;
            }

            if( $criteriastr != "" && $critservice != "" ) {
                $criteriastr = $criteriastr." AND ".$critservice;
            } else {
                $criteriastr .= $critservice;
            }
        }


        //***************** Search filetr ***************************//
        if( $withSearch && $search != "" ) {
            //echo "withSearch=".$withSearch.", search=".$search;

            if( $criteriastr != "" ) {
                $criteriastr .= " AND ";
            }

            $criteriastr .= " ( ";

            $searchStr = " LIKE '%" . $search . "%'";

            if( is_numeric($search) ) {
                $criteriastr .= "message.oid=".$search;
                $criteriastr .= " OR ";
            }

            //educational
            $dql->leftJoin("educational.courseTitle", "courseTitle");
            $criteriastr .= " courseTitle.name".$searchStr;
            //$criteriastr .= " OR educational.lessonTitleStr".$searchStr;

            //Course Director
            $dql->leftJoin("directorUser.infos", "directorUserInfos");
            $criteriastr .= " OR directorUser.username".$searchStr;
            $criteriastr .= " OR directorUserInfos.displayName".$searchStr;

            //reasearch
            $dql->leftJoin("research.projectTitle", "projectTitle");
            $criteriastr .= " OR projectTitle.name".$searchStr;
            //$criteriastr .= " OR research.setTitleStr".$searchStr;

            //Principal Investigator: principalUser
            $dql->leftJoin("principalUser.infos", "principalUserInfos");
            $criteriastr .= " OR principalUser.username".$searchStr;
            $criteriastr .= " OR principalUserInfos.displayName".$searchStr;

            //Submitter
            $criteriastr .= " OR provider.username".$searchStr;
            $criteriastr .= " OR providerinfos.displayName".$searchStr;

            //Ordering Provider
            $criteriastr .= " OR proxyuser.username".$searchStr;
            $criteriastr .= " OR proxyuserinfos.displayName".$searchStr;

            //accession
            $dql->leftJoin("message.accession", "accessionObj");
            $dql->leftJoin("accessionObj.accession", "accession");
            $criteriastr .= " OR accession.field".$searchStr;

            //MRN
            $dql->leftJoin("message.patient", "patient");
            $dql->leftJoin("patient.mrn", "mrn");
            $criteriastr .= " OR mrn.field".$searchStr;

            //patient last name
            $dql->leftJoin("patient.lastname", "lastname");
            $criteriastr .= " OR lastname.field".$searchStr;

            //patient first name
            $dql->leftJoin("patient.firstname", "firstname");
            $criteriastr .= " OR firstname.field".$searchStr;

            //Diagnosis
            $dql->leftJoin("message.part", "part");
            $dql->leftJoin("part.disident", "disident");
            $criteriastr .= " OR disident.field".$searchStr;

            //Differential Diagnoses
            $dql->leftJoin("part.diffDisident", "diffDisident");
            $criteriastr .= " OR diffDisident.field".$searchStr;

            //Reason for Scan/Note
            $dql->leftJoin("slides.scan", "scan");
            $criteriastr .= " OR scan.note".$searchStr;

            //Clinical History
            $dql->leftJoin("message.encounter", "encounter");
            $dql->leftJoin("encounter.pathistory", "pathistory");
            $criteriastr .= " OR pathistory.field".$searchStr;

            //Procedure Type
            $dql->leftJoin("message.procedure", "procedure");
            $dql->leftJoin("procedure.name", "procedureName");
            $dql->leftJoin("procedureName.field", "procedureType");
            $criteriastr .= " OR procedureType.name".$searchStr;

            //Source Organ
            $dql->leftJoin("message.block", "block");
            $dql->leftJoin("block.sectionsource", "sectionsource");
            $criteriastr .= " OR sectionsource.field".$searchStr;

            //part Gross Description
            $dql->leftJoin("part.description", "description");
            $criteriastr .= " OR description.field".$searchStr;

            //Microscopic Description
            $criteriastr .= " OR slides.microscopicdescr".$searchStr;

            //Disease Type [Neoplastic, non-neoplastic, metastatic]
            $dql->leftJoin("part.diseaseType", "diseaseType");
            $criteriastr .= " OR diseaseType.field".$searchStr;

            //Stain Name
            $dql->leftJoin("slides.stain", "stain");
            $dql->leftJoin("stain.field", "StainList");
            $criteriastr .= " OR StainList.name".$searchStr;

            //Special Stain Results (both stain name and the result field)
            $dql->leftJoin("block.specialStains", "specialStains");
            $dql->leftJoin("specialStains.staintype", "specialStainsStainList");
            $criteriastr .= " OR specialStainsStainList.name".$searchStr;
            $criteriastr .= " OR specialStains.field".$searchStr;

            //Clinical Summary
            $dql->leftJoin("patient.clinicalHistory", "clinicalHistory");
            $criteriastr .= " OR clinicalHistory.field".$searchStr;

            $criteriastr .= " ) ";

            //$increaseMaxExecTime = true;
        }
        //***************** END of Search filetr ***************************//

        /////////// institution ///////////
        if( $institution === false ) {
            $orderUtil = $this->get('scanorder_utility');
            //$dql->leftJoin("message.institution", "institution");
            $criteriastr = $orderUtil->addInstitutionQueryCriterion($user,$criteriastr);
        }
        /////////// EOF institution ///////////

        //echo "<br>criteriastr=".$criteriastr."<br>";

        $res = array();
        $res['dql'] = $dql;
        $res['criteriastr'] = $criteriastr;
        $res['showprovider'] = $showprovider;
        $res['showproxyuser'] = $showproxyuser;


        return $res;
    }

}
