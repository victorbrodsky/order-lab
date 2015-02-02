<?php

namespace Oleg\OrderformBundle\Controller;


use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Form\Extension\Core\DataTransformer\DateTimeToStringTransformer;

use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer;
use Symfony\Component\Serializer\Encoder\JsonEncoder;

use Oleg\OrderformBundle\Entity\OrderInfo;
use Oleg\OrderformBundle\Form\OrderInfoType;
use Oleg\OrderformBundle\Entity\Patient;
use Oleg\OrderformBundle\Entity\ClinicalHistory;
use Oleg\OrderformBundle\Entity\Procedure;
use Oleg\OrderformBundle\Entity\Accession;
use Oleg\OrderformBundle\Entity\Part;
use Oleg\OrderformBundle\Entity\Block;
use Oleg\OrderformBundle\Entity\Slide;
use Oleg\OrderformBundle\Entity\Educational;
use Oleg\OrderformBundle\Entity\Research;

use Oleg\OrderformBundle\Form\SlideMultiType;

use Oleg\OrderformBundle\Helper\ErrorHelper;
use Oleg\OrderformBundle\Helper\ScanEmailUtil;
use Oleg\UserdirectoryBundle\Util\UserUtil;
use Oleg\OrderformBundle\Security\Util\SecurityUtil;


//ScanOrder joins OrderInfo + Scan
/**
 * OrderInfo controller.
 */
class MultiScanOrderController extends Controller {

    //for testing data structure
    private $datastructure = 'datastructure';


    /**
     * Creates a new OrderInfo entity.
     *
     * @Route("/scan-order/one-slide/create", name="singleorder_create")
     * @Route("/scan-order/multi-slide/create", name="multi_create")
     * @Method("POST")
     * @Template("OlegOrderformBundle:MultiScanOrder:new.html.twig")
     */
    public function multyCreateAction(Request $request)
    { 

        //echo "multi new controller !!!! <br>";
        //exit();

        if( false === $this->get('security.context')->isGranted('ROLE_SCANORDER_SUBMITTER')
        ) {
            return $this->redirect( $this->generateUrl('scan_home') );
        }

        $em = $this->getDoctrine()->getManager();

        $entity  = new OrderInfo();

        $user = $this->get('security.context')->getToken()->getUser();

        //check if user has at least one institution
        $securityUtil = $this->get('order_security_utility');
        $userSiteSettings = $securityUtil->getUserPerSiteSettings($user);
        if( !$userSiteSettings ) {
            $orderUtil = $this->get('scanorder_utility');
            $orderUtil->setWarningMessageNoInstitution($user);
            return $this->redirect( $this->generateUrl('scan_home') );
        }
        $permittedInstitutions = $userSiteSettings->getPermittedInstitutionalPHIScope();
        if( count($permittedInstitutions) == 0 ) {
            $orderUtil = $this->get('scanorder_utility');
            $orderUtil->setWarningMessageNoInstitution($user);
            return $this->redirect( $this->generateUrl('scan_home') );
        }

        $request = $this->container->get('request');
        $routeName = $request->get('_route');
        //echo "routeName=".$routeName;

        if( $routeName == "singleorder_create" ) {
            $type = "One-Slide Scan Order";
            $new_order = "single_new";
        } elseif( $routeName == "multi_create") {
            $type = "Multi-Slide Scan Order";
            $new_order = "multi_new";
        } else {
            $type = "One-Slide Scan Order";
            $new_order = "single_new";
        }

        $permittedServices = $userSiteSettings->getScanOrdersServicesScope();

        $params = array(
            'type'=>$type,
            'cycle'=>'create',
            'user'=>$user,
            'institutions'=>$permittedInstitutions,
            'services'=>$permittedServices,
            'datastructure'=>$this->datastructure
        );

        $form = $this->createForm(new OrderInfoType($params,$entity), $entity);

        $form->handleRequest($request);


//        echo "provider2=".$entity->getProvider()."<br>";
        //oleg_orderformbundle_orderinfotype_patient_0_procedure_0_accession_0_accession_0_field
//        $patient = $form["patient"][0]->getData();
//        $mrn = $patient->getMrn()->first()->getField();
//        echo "mrn=".$mrn."<br>";
//
//        $accession = $form["patient"][0]["procedure"][0]["accession"][0]->getData();
//        $accessionNum = $accession->getAccession()->first()->getField();
//        $accessionType = $accession->getAccession()->first()->getKeytype();
//        //oleg_orderformbundle_orderinfotype_patient_0_procedure_0_accession_0_accession_0_keytype
//        echo "accessionNum=".$accessionNum.", accessionType=".$accessionType."<br>";
        //exit();

        //$dataq = $form["dataquality"][0]["accession"]->getData();
        //echo "dataq=".$dataq."<br>";

        //$partname = $form["patient"][0]["procedure"][0]["accession"][0]["part"][0]["partname"][0]['field']->getData();
        //echo "partname data:<br>";
        //print_r($partname);
        //echo "partname field="

        //$paper = $form["patient"][0]["procedure"][0]["accession"][0]["part"][0]["paper"][0]->getData();
        //echo "<br>paper data:<br>";
        //print_r($paper);
        //exit();

        //check if the orderform already exists, so it's edit case
//        echo "id=".$entity->getId()."<br>";
//        echo "entity count=".count($entity)."<br>";
//        echo "patient count=".count($entity->getPatient())." patient=".$entity->getPatient()[0]."<br>";
//        $id = $form["id"]->getData();
//        $provider = $form["provider"]->getData();
//        echo "form field id=".$id.", provider=".$provider."<br>";
//        //$request  = $this->get('request_stack')->getCurrentRequest();
//        $idrequest = $request->query->get('id');
//        echo "idreq=".$idrequest."<br>";
//        exit();
//        if( $entity->getId() && $entity->getId() > 0 ) {
//            $this->editAction( $entity->getId() );
//            return;
//        }

        if(0) {
            $errorHelper = new ErrorHelper();
            $errors = $errorHelper->getErrorMessages($form);
            echo "<br>form errors:<br>";
            print_r($errors);
        }
        
        //exit("Before validation main entity:<br>");
//       if( $form->isValid() ) {
//           echo "form is valid !!! <br>";
//       } else {
//           echo "form is not valid ??? <br>";
//       }
//        print_r($form->getErrors());
//        echo "<br>errors:<br>";
//        print_r($form->getErrorsAsString());
//        echo "<br>";
        //exit("controller exit");

        if( $form->isValid() ) {

            //exit("controller exit");

            if( isset($_POST['btnSubmit']) ) {
                $cycle = 'new';
                $status = $em->getRepository('OlegOrderformBundle:Status')->findOneByName('Submitted');
                $entity->setStatus($status);
            }

            if( isset($_POST['btnAmend']) ) {
                $cycle = 'amend';
                $status = $em->getRepository('OlegOrderformBundle:Status')->findOneByName('Amended');
                $entity->setStatus($status);
            }

            if( isset($_POST['btnSave']) || isset($_POST['btnSaveOnIdleTimeout']) ) {
                $cycle = 'edit';
                $status = $em->getRepository('OlegOrderformBundle:Status')->findOneByName('Not Submitted');
                $entity->setStatus($status);
            }

            //Set current user as a provider for this entity. Replace the form's provider with the current user.
            $entity->setProvider($user);

            //Add dataqualities to entity
            $dataqualities = $form->get('conflicts')->getData();
            $orderUtil = $this->get('scanorder_utility');
            $orderUtil->setDataQualityAccMrn($entity,$dataqualities);

            /////////////////// process and save form //////////////////////////////
            $entity = $em->getRepository('OlegOrderformBundle:OrderInfo')->processOrderInfoEntity( $entity, $user, $type, $this->get('router'), $this->container );

            if( isset($_POST['btnSubmit']) || isset($_POST['btnAmend']) || isset($_POST['btnSave']) || isset($_POST['btnSaveOnIdleTimeout']) ) {

                $conflictStr = "";
                foreach( $entity->getDataqualityMrnAcc() as $dq ) {
                    $conflictStr = $conflictStr . "\r\n".$dq->getDescription()."\r\n"."Resolved by replacing: ".$dq->getAccession()." => ".$dq->getNewaccession()."\r\n";
                }

                //email
                $email = $user->getEmail();
                $emailUtil = new ScanEmailUtil();

                $submitStatusStr = null;
                if( isset($_POST['btnAmend']) ) {
                    $submitStatusStr = "has been successfully amended";
                } else
                if( isset($_POST['btnSave']) || isset($_POST['btnSaveOnIdleTimeout']) ) {
                    $submitStatusStr = "is saved but not submitted";
                }

                $orderurl = $this->generateUrl( 'multy_show',array('id'=>$entity->getOid()), true ); //was $entity->getId()

                $emailUtil->sendEmail( $email, $em, $entity, $orderurl, null, $conflictStr, $submitStatusStr );

                if( isset($_POST['btnSaveOnIdleTimeout']) ) {
                    return $this->redirect($this->generateUrl('scan_idlelogout-saveorder',array('flag'=>'saveorder')));
                }

                if( count($entity->getDataqualityMrnAcc()) > 0 ) {
                    $conflictsStr = "MRN-Accession Conflict Resolved by Replacing:";
                    foreach( $entity->getDataqualityMrnAcc() as $dq ) {
                        $conflictsStr .= "<br>".$dq->getAccession()." => ".$dq->getNewaccession();
                    }
                } else {
                    $conflictsStr = "noconflicts";
                }

                $session = $request->getSession();
                $submittedData = array(
                    'oid' => $entity->getOid(),
                    'cycle' => $cycle,
                    'neworder' => $new_order,
                    'conflicts' => $conflictsStr
                );
                $session->set('submittedData', $submittedData);

                unset($_POST);

                return $this->redirect($this->generateUrl('scan-order-submitted-get'));

            } //if submit, amend, timeout

        }

        throw new \Exception( 'Form is not valid Errors='.$form->getErrorsAsString() );

    }

    /**
     * @Route("/scan-order/submitted/successfully", name="scan-order-submitted-get")
     * @Method("GET")
     */
    public function thanksScanorderGetAction(Request $request) {

        $session = $request->getSession();
        $submittedData = $session->get('submittedData');

        //echo "conflicts=".$submittedData['conflicts']."<br>";
        return $this->render('OlegOrderformBundle:ScanOrder:thanks.html.twig', array(
            'oid' => $submittedData['oid'],
            'conflicts' => $submittedData['conflicts'],
            'cycle' => $submittedData['cycle'],
            'neworder' => $submittedData['neworder']
        ));
    }
    
    /**
     * Displays a form to create a new OrderInfo + Scan entities.
     *
     * @Route("/scan-order/one-slide/new", name="single_new")
     * @Route("/scan-order/multi-slide/new", name="multi_new")
     * @Method("GET")
     * @Template("OlegOrderformBundle:MultiScanOrder:new.html.twig")
     */
    public function newMultyAction()
    {

        if( false === $this->get('security.context')->isGranted('ROLE_SCANORDER_SUBMITTER') ) {
            return $this->redirect( $this->generateUrl('scan_home') );
        }

        $user = $this->get('security.context')->getToken()->getUser();
        $em = $this->getDoctrine()->getManager();

        $orderUtil = $this->get('scanorder_utility');

        //check if user has at least one institution
        $securityUtil = $this->get('order_security_utility');
        $userSiteSettings = $securityUtil->getUserPerSiteSettings($user);
        if( !$userSiteSettings ) {
            $orderUtil->setWarningMessageNoInstitution($user);
            return $this->redirect( $this->generateUrl('scan_home') );
        }
        $permittedInstitutions = $userSiteSettings->getPermittedInstitutionalPHIScope();
        if( count($permittedInstitutions) == 0 ) {
            $orderUtil->setWarningMessageNoInstitution($user);
            return $this->redirect( $this->generateUrl('scan_home') );
        }

        $entity = new OrderInfo();

        //***************** get ordering provider from most recent order ***************************//
        $lastProxy = null;
        //$orderWithOrderingProvider = $em->getRepository('OlegOrderformBundle:History')->findByProvider($user);
        $repository = $this->getDoctrine()->getRepository('OlegOrderformBundle:OrderInfo');
        $dql =  $repository->createQueryBuilder("orderinfo");
        $dql->select('orderinfo');
        $dql->innerJoin("orderinfo.provider", "provider");
        $dql->leftJoin("orderinfo.proxyuser", "proxyuser");
        $dql->where("provider=:user AND proxyuser IS NOT NULL");
        $dql->orderBy("orderinfo.orderdate","DESC");
        $query = $em->createQuery($dql)->setParameter('user', $user)->setMaxResults(1);
        $lastOrderWithProxies = $query->getResult();
        //echo "count=".count($lastOrderWithProxies)."<br>";

        if( count($lastOrderWithProxies) > 0 ) {
            if( count($lastOrderWithProxies) > 1 ) {
                throw new \Exception( 'More than one orderinfo found count='.count($lastOrderWithProxies).' objects' );
            }
            $lastOrderWithProxy = $lastOrderWithProxies[0];
            $lastProxy = $lastOrderWithProxy->getProxyuser();
        } else {
            $lastProxy = null;
        }
        //echo "lastProxy=".$lastProxy."<br>";
        //***************** end of get ordering provider from most recent order ***************************//

        //echo "MultiScanOrderController: User=".$user."<br>";
        //$email = $user->getEmail();

        $source = $securityUtil->getDefaultSourceSystem(); //'scanorder';
        $status = 'valid';

        $entity->setPurpose("For Internal Use by WCMC Department of Pathology");

        $entity->setProvider($user);

        if( $lastProxy ) {
            $entity->setProxyuser($lastProxy);
        } else {
            $entity->setProxyuser($user);
        }

        $patient = new Patient(true,$status,$user,$source);
        $entity->addPatient($patient);

        $procedure = new Procedure(true,$status,$user,$source);
        $patient->addProcedure($procedure);

        $accession = new Accession(true,$status,$user,$source);
        $procedure->addAccession($accession);

        $part = new Part(true,$status,$user,$source);
        $accession->addPart($part);

        $block = new Block(true,$status,$user,$source);
        $part->addBlock($block);

        $slide = new Slide(true,'valid',$user,$source); //Slides are always valid by default
        $block->addSlide($slide);

        $edu = new Educational();
        $entity->setEducational($edu);

        $res = new Research();
        $entity->setResearch($res);

        $request = $this->container->get('request');
        $routeName = $request->get('_route');
        //echo "newMultyAction: routeName=".$routeName."<br>";

        if( $routeName == "multi_new") {
            $type = "Multi-Slide Scan Order";
        } elseif( $routeName == "single_new") {
            $type = "One-Slide Scan Order";
        } else {
            $type = "One-Slide Scan Order";
        }

        //set the default service
        $entity->setService($userSiteSettings->getDefaultService());

        ////////////////// set previous service from the last order if default is null //////////////////
        if( !$userSiteSettings->getDefaultService() ) {
            //echo "find prev service <br>";
            $previousOrder = $orderUtil->getPreviousOrderinfo();
            //$this->getDoctrine()->getRepository('OlegOrderformBundle:OrderInfo')->findBy(array(), array('orderdate' => 'ASC'),1); //limit to one result
            if( $previousOrder ) {
                $entity->setService($previousOrder->getService());
                //echo "prev service set<br>";
            }
        }
        ////////////////// EOF set previous service from the last order if default is null //////////////////

        //set the default institution
        $entity->setInstitution($permittedInstitutions->first());

        //set default department and division
        $defaultsDepDiv = $securityUtil->getDefaultDepartmentDivision($entity,$userSiteSettings);
        $department = $defaultsDepDiv['department'];
        $division = $defaultsDepDiv['division'];


        $permittedServices = $userSiteSettings->getScanOrdersServicesScope();

        $params = array(
            'type'=>$type,
            'cycle'=>'new',
            'institutions'=>$permittedInstitutions,
            'services'=>$permittedServices,
            'user'=>$user,
            'division'=>$division,
            'department'=>$department,
            'returnSlide'=>$orderUtil->getOrderReturnSlidesLocation($entity),
            'datastructure'=>$this->datastructure
        );
        $form   = $this->createForm( new OrderInfoType($params, $entity), $entity );

        if( $routeName != "single_new") {
            return $this->render('OlegOrderformBundle:MultiScanOrder:new.html.twig', array(
                'form' => $form->createView(),
                'type' => 'new',
                'formtype' => $type,
                'datastructure' => $this->datastructure
            ));
        } else {
            //echo "newsingle: <br>";
            return $this->render('OlegOrderformBundle:MultiScanOrder:newsingle.html.twig', array(
                'form' => $form->createView(),
                'cycle' => 'new',
                'formtype' => $type,
                'datastructure' => $this->datastructure
            ));
        }

    }


    /**
     * Displays a form to view, update, amend an OrderInfo + Scan entities. $id is oid of the orderinfo object
     * @Route("/scan-order/{id}/edit", name="multy_edit", requirements={"id" = "\d+"})
     * @Route("/scan-order/{id}/amend", name="order_amend", requirements={"id" = "\d+"})
     * @Route("/scan-order/{id}/show", name="multy_show", requirements={"id" = "\d+"})
     * @Route("/scan-order/data-structure/{id}/show", name="scan_datastructure", requirements={"id" = "\d+"})
     * @Method("GET")
     * @Template("OlegOrderformBundle:MultiScanOrder:new.html.twig")
     */
    public function showMultyAction( Request $request, $id, $type = "show" )
    {

        if( false === $this->get('security.context')->isGranted('ROLE_SCANORDER_SUBMITTER') &&
            false === $this->get('security.context')->isGranted('ROLE_SCANORDER_ORDERING_PROVIDER')
        ) {
            return $this->redirect( $this->generateUrl('scan-order-nopermission') );
        }

        $em = $this->getDoctrine()->getManager();

        $user = $this->get('security.context')->getToken()->getUser();

        //check if user has at least one institution
        $securityUtil = $this->get('order_security_utility');
        $userSiteSettings = $securityUtil->getUserPerSiteSettings($user);
        if( !$userSiteSettings ) {
            $orderUtil = $this->get('scanorder_utility');
            $orderUtil->setWarningMessageNoInstitution($user);
            return $this->redirect( $this->generateUrl('scan_home') );
        }
        $permittedInstitutions = $userSiteSettings->getPermittedInstitutionalPHIScope();
        if( count($permittedInstitutions) == 0 ) {
            $orderUtil = $this->get('scanorder_utility');
            $orderUtil->setWarningMessageNoInstitution($user);
            return $this->redirect( $this->generateUrl('scan_home') );
        }

        //INNER JOIN orderinfo.block block
//        INNER JOIN orderinfo.patient patient
//        INNER JOIN orderinfo.procedure procedure
//        INNER JOIN orderinfo.accession accession
//        INNER JOIN orderinfo.part part
//        INNER JOIN orderinfo.slide slide
        $query = $em->createQuery('
            SELECT orderinfo
            FROM OlegOrderformBundle:OrderInfo orderinfo
            WHERE orderinfo.oid = :id'
        )->setParameter('id', $id);

        $entities = $query->getResult();

        //echo "<br>orderinfo count=".count( $entities )."<br>";

        if( count( $entities ) == 0 ) {
            throw $this->createNotFoundException('Unable to find OrderInfo entity with oid='.$id);
        }

        if( count( $entities ) == 0 ) {
            throw $this->createNotFoundException('More than one OrderInfo entity found.');
        } else {
            $entity = $entities[0];
        }

        //order memory usage
        $mem = memory_get_usage(true);
        $entity_tmp = clone $entity;
        $mem = memory_get_usage(true) - $mem;
        echo "order mem old = 2.36 Mb<br>";
        echo "order mem = ".$mem. " => " .round($mem/1000000,2)." Mb<br>";
        unset($entity_tmp);

        $routeName = $request->get('_route');

        if( $routeName == "multy_show") {
            $actions = array('show');
        }
        if( $routeName == "order_amend") {
            $actions = array('amend');
        }
        if( $routeName == "multy_edit") {
            $actions = array('edit');
        }

        $datastructure = null;
        if( $routeName == "scan_datastructure") {
            $actions = array('edit'); //show
            $datastructure = "datastructure";
            $source = $securityUtil->getDefaultSourceSystem();
        }

        if( $entity && !$securityUtil->isUserAllowOrderActions($entity, $user, $actions) ) {
            return $this->redirect( $this->generateUrl('scan-order-nopermission') );
        }

        //redirect to show table view controller if form type is "Table-View Scan Order"
        if( $entity->getType() == "Table-View Scan Order" ) {
            return $this->redirect($this->generateUrl('table_show',array('id'=>$entity->getOid())));
        }

        //redirect by status
        $orderUtil = $this->get('scanorder_utility');
        $redirect = $orderUtil->redirectOrderByStatus($entity,$routeName);
        if( $redirect != null ) {
            return $redirect;
        }

        //echo $entity;
        //echo $entity->getStatus();
        //echo "<br>Patient count=".count( $entity->getPatient() );

        //patient
        foreach( $entity->getPatient() as $patient ) {

            //check if patient has this orderinfo
            if( !$this->hasOrderInfo($patient,$id) ) {
                $entity->removePatient($patient);
                continue;
            }
            
            if( !$securityUtil->hasUserPermission($patient, $user) ) {
                $entity->removePatient($patient);
                continue;
            }

            if( $datastructure ) {
                $patient->addExtraFields('valid',$user,$source); //invalid - to not show on view
            }

            //procedure
            foreach( $patient->getProcedure() as $procedure ) {

                if( !$this->hasOrderInfo($procedure,$id) ) {
                    $patient->removeProcedure($procedure);
                    continue;
                }

                if( !$securityUtil->hasUserPermission($procedure, $user) ) {
                    $patient->removeChildren($procedure);
                    continue;
                }

                if( $datastructure ) {
                    $procedure->addExtraFields('valid',$user,$source); //invalid - to not show on view
                }

                //accession
                foreach( $procedure->getAccession() as $accession ) {
                    if( !$this->hasOrderInfo($accession,$id) ) {
                        $procedure->removeAccession($accession);
                        continue;
                    }

                    if( !$securityUtil->hasUserPermission($accession, $user) ) {
                        $procedure->removeChildren($accession);
                        continue;
                    }

                    if( $datastructure ) {
                        $accession->addExtraFields('valid',$user,$source); //invalid - to not show on view
                    }

                    //part
                    foreach( $accession->getPart() as $part ) {
                       if( !$this->hasOrderInfo($part,$id) ) {
                            $accession->removePart($part);
                            continue;
                        }

                        if( !$securityUtil->hasUserPermission($part, $user) ) {
                            $accession->removeChildren($part);
                            continue;
                        }

                        //block
                        foreach( $part->getBlock() as $block ) {
                            if( !$this->hasOrderInfo($block,$id) ) {
                                $part->removeBlock($block);
                                continue;
                            }

                            if( ! $securityUtil->hasUserPermission($block, $user) ) {
                                $part->removeChildren($block);
                                continue;
                            }

                            //slide
                            foreach( $block->getSlide() as $slide ) {

                                //check if this slides can be viewed by this user
                                $permission = true;
                                if( !$securityUtil->hasUserPermission($slide, $user) ) {
                                    //echo " (".$slide->getProvider()->getId().") ?= (".$user->getId().") => ";
                                    if( $slide->getProvider()->getId() != $user->getId() ) {
                                        $permission = false;
                                    }
                                }

                                //echo "permission=".$permission;

                                if( !$this->hasOrderInfo($slide,$id) || !$permission ) {
                                    $block->removeSlide($slide);
                                    $entity->removeSlide($slide);
                                    continue;
                                }


                            }//slide

                        }//block
                    }//part
                }//accession
            }//procedure
        }//patient

        //echo "<br>Procedure count=".count( $entity->getProcedure() );
        //echo "<br>Slide count=".count( $entity->getSlide() );

        if( count( $entity->getSlide() ) == 0 ) {
            //this orderinfo does not have slides to show or the user don't have permission to view this orderinfo's slides
            throw $this->createNotFoundException('Nothing to display.');
        }

        $disable = true;

        if( $type == "edit" || $routeName == "multy_edit") {
            $disable = false;
            $type = "edit";
        }

        if( $routeName == "order_amend") {
            $disable = false;
            $type = "amend";
        }

        if( $routeName == "scan_datastructure") {
            $disable = false;
            $type = "edit";
        }

        //echo "show id=".$entity->getId()."<br>";
        //use always multy because we use nested forms to display single and multy slide orders
        $single_multy = $entity->getType();

        if( $single_multy == 'single' ) {
            $single_multy = 'multy';
        }

        //echo "route=".$routeName.", type=".$type."<br>";

        $permittedServices = $userSiteSettings->getScanOrdersServicesScope();

        //set default department and division
        $defaultsDepDiv = $securityUtil->getDefaultDepartmentDivision($entity,$userSiteSettings);
        $department = $defaultsDepDiv['department'];
        $division = $defaultsDepDiv['division'];

        $params = array(
            'type'=>$single_multy,
            'cycle'=>$type,
            'institutions'=>$permittedInstitutions,
            'services'=>$permittedServices,
            'user'=>$user,
            'division'=>$division,
            'department'=>$department,
            'datastructure' => $datastructure
        );
        $form   = $this->createForm( new OrderInfoType($params,$entity), $entity, array('disabled' => $disable) );

        //echo "type=".$entity->getType();
        //exit();

//        $id = $form["id"]->getData();
//        $provider = $form["provider"]->getData();
//        echo "id=".$id.", provider=".$provider.", type=".$type."<br>";

        //History
        $history = null;

        if( $routeName == "multy_show") {

            //$history = $em->getRepository('OlegOrderformBundle:History')->findByCurrentid( $entity->getOid(), array('changedate' => 'DESC') );
            $repository = $this->getDoctrine()->getRepository('OlegOrderformBundle:History');
            $dql = $repository->createQueryBuilder("h");
            $dql->innerJoin("h.orderinfo", "orderinfo");
            $dql->innerJoin("h.eventtype", "eventtype");
            $dql->where("h.currentid = :oid AND (eventtype.name = 'Initial Order Submission' OR eventtype.name = 'Status Changed' OR eventtype.name = 'Amended Order Submission')");
            $dql->orderBy('h.changedate','DESC');
            $dql->setParameter('oid',$entity->getOid());
            $history = $dql->getQuery()->getResult();

        }

        return array(
            'entity' => $entity,
            'form' => $form->createView(),
            'type' => $type,    //form cycle: new, show, amend ...
            'formtype' => $entity->getType(),
            'history' => $history,
            'amendable' => $securityUtil->isUserAllowOrderActions($entity, $user, array('amend')),
            'changestatus' => $securityUtil->isUserAllowOrderActions($entity, $user, array('changestatus')),
            'datastructure' => $datastructure
        );


    }

    public function hasOrderInfo( $entity, $id ) {
        $has = false;
        foreach( $entity->getOrderInfo() as $child ) {
            if( $child->getOid() == $id ) {
                $has = true;
                break;
            }
        }
        return $has;
    }

    /**
     * Displays a form to create a new OrderInfo + Scan entities.
     * @Route("/scan-order/download/{id}", name="download_file", requirements={"id" = "\d+"})
     * @Method("GET")
     */
    public function downloadAction($id) {

        $em = $this->getDoctrine()->getManager();
        $file = $em->getRepository('OlegOrderformBundle:Document')->findOneById($id);

        $html =     //"header('Content-type: application/pdf');".
                    "header('Content-Disposition: attachment; filename=".$file->getName()."');".
                    "readfile('".$file->getPath()."');";

        return $html;

    }




}
