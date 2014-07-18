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
use Oleg\OrderformBundle\Helper\OrderUtil;
use Oleg\OrderformBundle\Helper\EmailUtil;
use Oleg\OrderformBundle\Helper\UserUtil;
use Oleg\OrderformBundle\Security\Util\SecurityUtil;


//ScanOrder joins OrderInfo + Scan
/**
 * OrderInfo controller.
 */
class MultiScanOrderController extends Controller {

//    /**
//     * Edit: If the form exists, use this function
//     * @Route("/scan-order/edit/{id}", name="exist_edit", requirements={"id" = "\d+"})
//     * @Method("POST")
//     * @Template("OlegOrderformBundle:MultiScanOrder:new.html.twig")
//     */
//    public function editAction( $id )
//    {
//
//        if( false === $this->get('security.context')->isGranted('ROLE_SCANORDER_SUBMITTER')
//        ) {
//            return $this->redirect( $this->generateUrl('scan-order-nopermission') );
//        }
//
//        $em = $this->getDoctrine()->getManager();
//        $secUtil = new SecurityUtil($em,$this->get('security.context'),$this->get('session') );
//        if( !$secUtil->isCurrentUserAllow($id) ) {
//            return $this->redirect( $this->generateUrl('scan-order-nopermission') );
//        }
//
//        $em = $this->getDoctrine()->getManager();
//        $entity = $em->getRepository('OlegOrderformBundle:Slide')->findOneBy($id);
//
//        $em->persist($entity);
//        $em->flush();
//
//        $this->showMultyAction($entity->getId(), "show");
//
//    }


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
            return $this->redirect( $this->generateUrl('scan-order-home') );
        }

        $em = $this->getDoctrine()->getManager();

        $entity  = new OrderInfo();

        $user = $this->get('security.context')->getToken()->getUser();

//        $status = 'valid';    //invalid
//        $source = 'scanorder';
//
//        $patient = new Patient(true,$status,$user,$source);
//        $entity->addPatient($patient);
//
//        $procedure = new Procedure(true,$status,$user,$source);
//        $patient->addProcedure($procedure);
//
//        $accession = new Accession(true,$status,$user,$source);
//        $procedure->addAccession($accession);
//
//        $part = new Part(true,$status,$user,$source);
//        $accession->addPart($part);
//
//        $block = new Block(true,$status,$user,$source);
//        $part->addBlock($block);
//
//        $slide = new Slide(true,'valid',$user,$source); //Slides are always valid by default
//        $block->addSlide($slide);

        $request = $this->container->get('request');
        $routeName = $request->get('_route');
        //echo "routeName=".$routeName;

        if( $routeName == "singleorder_create" ) {
            $type = "One Slide Scan Order";
            $new_order = "single_new";
        } elseif( $routeName == "multi_create") {
            $type = "Multi-Slide Scan Order";
            $new_order = "multi_new";
        } else {
            $type = "One Slide Scan Order";
            $new_order = "single_new";
        }

        $params = array('type'=>$type, 'cicle'=>'create', 'service'=>null, 'user'=>$user);

//        echo "controller create=";
//        print_r($params);
//        echo "<br>";

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
//        exit();

        //$dataq = $form["dataquality"][0]["accession"]->getData();
        //echo "dataq=".$dataq."<br>";

        //check if the orderform already exists, so it's edit case
        //TODO: edit id is empty. Why??
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
        
        //echo "Before validation main entity:<br>";

//       if( $form->isValid() ) {
//           echo "form is valid !!! <br>";
//       } else {
//           echo "form is not valid ??? <br>";
//       }
//        echo "form errors=".print_r($form->getErrors())."<br>";
//        exit("controller exit");

        if( 1 ) {

            //exit("controller exit");

            if( isset($_POST['btnSubmit']) ) {
                $cicle = 'new';
                $status = $em->getRepository('OlegOrderformBundle:Status')->findOneByName('Submitted');
                $entity->setStatus($status);
            }

            if( isset($_POST['btnAmend']) ) {
                $cicle = 'amend';
                $status = $em->getRepository('OlegOrderformBundle:Status')->findOneByName('Amended');
                $entity->setStatus($status);
            }

            if( isset($_POST['btnSave']) || isset($_POST['btnSaveOnIdleTimeout']) ) {
                $cicle = 'edit';
                $status = $em->getRepository('OlegOrderformBundle:Status')->findOneByName('Not Submitted');
                $entity->setStatus($status);
            }

            //Add dataqualities to entity
            $dataqualities = $form->get('conflicts')->getData();
            $orderUtil = new OrderUtil($em);
            $orderUtil->setDataQuality($entity,$dataqualities);

            //Set current user as a provider for this entity. Replace the form's provider with the current user.
            $entity->setProvider($user);

            /////////////////// process and save form //////////////////////////////
            $entity = $em->getRepository('OlegOrderformBundle:OrderInfo')->processOrderInfoEntity( $entity, $user, $type, $this->get('router') );

            if( isset($_POST['btnSubmit']) || isset($_POST['btnAmend']) || isset($_POST['btnSave']) || isset($_POST['btnSaveOnIdleTimeout']) ) {

                $conflictStr = "";
                foreach( $entity->getDataquality() as $dq ) {
                    $conflictStr = $conflictStr . "\r\n".$dq->getDescription()."\r\n"."Resolved by replacing: ".$dq->getAccession()." => ".$dq->getNewaccession()."\r\n";
                }

                //email
                $email = $user->getEmail();
                $emailUtil = new EmailUtil();

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
                    return $this->redirect($this->generateUrl('idlelogout-saveorder',array('flag'=>'saveorder')));
                }

                if( count($entity->getDataquality()) > 0 ) {
                    $conflictsStr = "MRN-Accession Conflict Resolved by Replacing:";
                    foreach( $entity->getDataquality() as $dq ) {
                        $conflictsStr .= "<br>".$dq->getAccession()." => ".$dq->getNewaccession();
                    }
                } else {
                    $conflictsStr = "noconflicts";
                }

                return $this->redirect($this->generateUrl('scan-order-submitted-get',
                    array(
                        'oid' => $entity->getOid(),
                        'cicle' => $cicle,
                        'neworder' => $new_order,
                        'conflicts' => $conflictsStr
                    )
                ));

//                return $this->render('OlegOrderformBundle:ScanOrder:thanks.html.twig', array(
//                    'oid' => $entity->getOid(),
//                    'conflicts' => $conflicts,
//                    'cicle' => $cicle,
//                    'neworder' => $new_order
//                ));

            }


        }

        //form always valid, so this will not be reached anyway
        return array(           
            'form'   => $form->createView(),
            'type' => 'new',
            'formtype' => $entity->getType()
        );    
    }

    /**
     * @Route("/scan-order/submitted/{oid}/{cicle}/{neworder}/{conflicts}", name="scan-order-submitted-get")
     * @Method("GET")
     */
    public function thanksScanorderGetAction($oid,$conflicts,$cicle,$neworder) {

        //echo "conflicts=".$conflicts."<br>";
        return $this->render('OlegOrderformBundle:ScanOrder:thanks.html.twig', array(
            'oid' => $oid,
            'conflicts' => $conflicts,
            'cicle' => $cicle,
            'neworder' => $neworder
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
            return $this->redirect( $this->generateUrl('scan-order-home') );
        }

        $user = $this->get('security.context')->getToken()->getUser();
        $em = $this->getDoctrine()->getManager();

        //check if user has at least one institution
        if( count($user->getInstitution()) == 0 ) {
//            $this->get('session')->getFlashBag()->add(
//                'warning',
//                'You must be assigned to at least one institution to make an order. Please contact the system administrator by emailing '.$this->container->getParameter('default_system_email').'.'
//            );
            $orderUtil = new OrderUtil($em);
            $userUrl = $this->generateUrl('showuser', array('id' => $user->getId()),true);
            $homeUrl = $this->generateUrl('main_common_home',array(),true);
            $sysEmail = $this->container->getParameter('default_system_email');
            $orderUtil->setWarningMessageNoInstitution($user,$userUrl,$this->get('session')->getFlashBag(),$sysEmail,$homeUrl);

            return $this->redirect( $this->generateUrl('scan-order-home') );
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

        $source = 'scanorder';
        $status = 'valid';

        $entity->setPurpose("For Internal Use by WCMC Department of Pathology");

        $entity->setProvider($user);

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
            $type = "One Slide Scan Order";
        } else {
            $type = "One Slide Scan Order";
        }

        if( $lastProxy ) {
            $entity->setProxyuser($lastProxy);
        } else {
            $entity->setProxyuser($user);
        }

        //$slide2 = new Slide();
        //$block->addSlide($slide2);

        //get pathology service for this user
        $service = $user->getPathologyServices();

        //set the first service
        if( count($service) > 0 ) {
            $entity->setPathologyService($service->first());
        }

        $params = array('type'=>$type, 'cicle'=>'new', 'service'=>$service, 'user'=>$user);
        $form   = $this->createForm( new OrderInfoType($params, $entity), $entity );

        if( $routeName != "single_new") {
            return $this->render('OlegOrderformBundle:MultiScanOrder:new.html.twig', array(
                'form' => $form->createView(),
                'type' => 'new',
                'formtype' => $type
            ));
        } else {
            //echo "newsingle: <br>";
            return $this->render('OlegOrderformBundle:MultiScanOrder:newsingle.html.twig', array(
                'form' => $form->createView(),
                'cycle' => 'new',
                'formtype' => $type
            ));
        }

    }


    /**
     * Displays a form to view, update, amend an OrderInfo + Scan entities. $id is oid of the orderinfo object
     * @Route("/scan-order/{id}/edit", name="multy_edit", requirements={"id" = "\d+"})
     * @Route("/scan-order/{id}/amend", name="order_amend", requirements={"id" = "\d+"})
     * @Route("/scan-order/{id}/show", name="multy_show", requirements={"id" = "\d+"})
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

        $secUtil = new SecurityUtil($em,$this->get('security.context'),$this->get('session') );
        if( !$secUtil->isCurrentUserAllow($id) ) {
            return $this->redirect( $this->generateUrl('scan-order-nopermission') );
        }

        $user = $this->get('security.context')->getToken()->getUser();

        $userUtil = new UserUtil();

        //TODO: is it possible to filter orderinfo by JOINs?

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

        $request = $this->container->get('request');
        $routeName = $request->get('_route');

        if( $entity && !$userUtil->hasPermission($entity,$this->get('security.context')) ) {
            return $this->redirect( $this->generateUrl('scan-order-nopermission') );
        }

        //if show not submitted => change url
        if( $entity->getStatus()."" == "Not Submitted" && $routeName != "multy_edit" ) {
            return $this->redirect($this->generateUrl('multy_edit',array('id'=>$entity->getId())));
        }

        //echo $entity;
        //echo $entity->getStatus();
        //echo "<br>Procedure count=".count( $entity->getProcedure() );

        //patient
        foreach( $entity->getPatient() as $patient ) {

            //check if patient has this orderinfo
            if( !$this->hasOrderInfo($patient,$id) ) {
                $entity->removePatient($patient);
                continue;
            }

            if( ! $userUtil->hasPermission($patient, $this->get('security.context')) ) {
                $entity->removePatient($patient);
                continue;
            }

//            if( $entity->getType() == "Table-View Scan Order" ) {
//                //$patient->obtainOneValidObjectPatient($user);
//                //$patient->obtainOneValidObject(array('mrn','dob','clinicalHistory'),$user,false);
//                //$patientArr['patient'] = $patient->obtainOneValidObject( array( array('mrn','field','keytype'), 'dob', 'clinicalHistory' ), $user, true );
//                //array_push($jsonData, $patientArr);
//                //var_dump($patientArr);
//                //print_r($patientArr);
//
////                //now patient's array fields have only one 'valid' field
////                $jsonData['patient'][$patient->getId()]['mrn'] = $patient->getMrn()->first()->getField();
////                $jsonData['patient'][$patient->getId()]['mrntype'] = $patient->getMrn()->first()->getKeytype()->getName();
////                $jsonData['patient'][$patient->getId()]['clinicalHistory'] = $patient->getClinicalHistory()->first()->getField();
////                //dob
////                $transformer = new DateTimeToStringTransformer(null,null,'m/d/Y');
////                $dobStr = $transformer->transform($patient->getDob()->first()->getField());
////                $jsonData['patient'][$patient->getId()]['dob'] = $dobStr;
//            }

            //procedure
            foreach( $patient->getProcedure() as $procedure ) {

                if( !$this->hasOrderInfo($procedure,$id) ) {
                    $patient->removeProcedure($procedure);
                    continue;
                }

                if( ! $userUtil->hasPermission($procedure, $this->get('security.context')) ) {
                    $patient->removeChildren($procedure);
                    continue;
                }

//                if( $entity->getType() == "Table-View Scan Order" ) {
//                    //$procedureArr = $procedure->obtainOneValidObject(array('name','encounterDate','patlastname','patfirstname','patmiddlename','patage','patsex','pathistory'),$user, true);
//                    //print_r($procedureArr);
//                    $procedureArr = array();
//                    $procedureArr['procedure'] = $procedure->obtainOneValidObject(array('name','encounterDate','patlastname','patfirstname','patmiddlename','patage','patsex','pathistory'),$user, true);
//                    array_push($patientArr['patient'], $procedureArr);
//                    //now patient's array fields have only one 'valid' field
//                    //$jsonData['patient'][$patient->getId()]['procedure'][$procedure->getId()]['name'] = $procedure->getName()->first()->getField();
//                    //$jsonData['patient'][$patient->getId()]['procedure'][$procedure->getId()]['encounterDate'] = $procedure->getEncounterDate()->first()->getField();
//                }

                //accession
                foreach( $procedure->getAccession() as $accession ) {
                    if( !$this->hasOrderInfo($accession,$id) ) {
                        $procedure->removeAccession($accession);
                        continue;
                    }

                    if( ! $userUtil->hasPermission($accession, $this->get('security.context')) ) {
                        $procedure->removeChildren($accession);
                        continue;
                    }

                    //part
                    foreach( $accession->getPart() as $part ) {
                       if( !$this->hasOrderInfo($part,$id) ) {
                            $accession->removePart($part);
                            continue;
                        }

                        if( ! $userUtil->hasPermission($part, $this->get('security.context')) ) {
                            $accession->removeChildren($part);
                            continue;
                        }

                        //block
                        $blockArr = array();
                        foreach( $part->getBlock() as $block ) {
                            if( !$this->hasOrderInfo($block,$id) ) {
                                $part->removeBlock($block);
                                continue;
                            }

                            if( ! $userUtil->hasPermission($block, $this->get('security.context')) ) {
                                $part->removeChildren($block);
                                continue;
                            }

                            //slide
                            $slideArr = array();
                            foreach( $block->getSlide() as $slide ) {

                                //check if this slides can be viewed by this user
                                $permission = true;
                                if( !$userUtil->hasPermission($slide, $this->get('security.context')) ) {
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

        //echo "show id=".$entity->getId()."<br>";
        //use always multy because we use nested forms to display single and multy slide orders
        $single_multy = $entity->getType();

        if( $single_multy == 'single' ) {
            $single_multy = 'multy';
        }

        //echo "route=".$routeName.", type=".$type."<br>";

        $params = array('type'=>$single_multy, 'cicle'=>$type, 'service'=>null, 'user'=>$user);
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
            $dql->where("h.currentid = :oid AND (h.eventtype = 'Initial Order Submission' OR h.eventtype = 'Status Changed' OR h.eventtype = 'Amended Order Submission')");
            $dql->orderBy('h.changedate','DESC');
            $dql->setParameter('oid',$entity->getOid());
            $history = $dql->getQuery()->getResult();

        }

        if( false && $entity->getType() == "Table-View Scan Order" ) {

            $jsonData = array();

            $patients = $entity->getPatient();

            //$patientArr = array();
            $jsonData['patient'] = array();
            foreach( $patients as $patient ) {
                //$patient->obtainOneValidObject(array( 'mrn', 'dob', 'clinicalHistory' ), $user, false);
                //$jsonData['patient'][$patient->getId()] = $patient->obtainOneValidObject( array( array('mrn','field','keytype'), 'dob', 'clinicalHistory' ), $user, true );
                //array_push($jsonData['patient'], $patient->obtainOneValidObject( array( array('mrn','field','keytype'), 'dob', 'clinicalHistory' ), $user, true ));
            }

//            $jsonData = array(
//                'patient' => array('mrn'=>'5','name'=>'John'),
//                'accession' => array('accession number'=>'S11-5','date'=>'10-10-2013')
//            );

            //$serializer = new Serializer(array(new GetSetMethodNormalizer()), array('json' => new JsonEncoder()));

//            $entityTest = new OrderInfo();
//            $patientTest = new Patient(true,'valid',$user);
//            $entityTest->addPatient($patientTest);
//            $procedureTest = new Procedure(true,'valid',$user);
//            $patientTest->addProcedure($procedureTest);

            //$serializer = $this->container->get('jms_serializer');
            //$jsonData = $serializer->serialize($patientTest, 'json');

            print_r($jsonData);

            return $this->render('OlegOrderformBundle:MultiScanOrder:viewtable.html.twig', array(
                'xdata' => json_encode($jsonData),
                //'entity' => $entity,
                'form' => $form->createView(),
                'type' => $type,    //form cicle: new, show, amend ...
                'formtype' => $entity->getType(),
                'history' => $history
            ));

        } else {

            return array(
                'entity' => $entity,
                'form' => $form->createView(),
                'type' => $type,    //form cicle: new, show, amend ...
                'formtype' => $entity->getType(),
                'history' => $history
            );

        }

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
