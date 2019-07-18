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

namespace Oleg\OrderformBundle\Controller;



use Oleg\UserdirectoryBundle\Entity\InstitutionWrapper;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Form\Extension\Core\DataTransformer\DateTimeToStringTransformer;

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer;
use Symfony\Component\Serializer\Encoder\JsonEncoder;

use Oleg\OrderformBundle\Entity\Message;
use Oleg\OrderformBundle\Form\MessageType;
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
use Oleg\OrderformBundle\Entity\Encounter;
use Oleg\OrderformBundle\Entity\Endpoint;
use Oleg\OrderformBundle\Entity\ScanOrder;
use Oleg\OrderformBundle\Helper\ErrorHelper;
use Oleg\OrderformBundle\Helper\ScanEmailUtil;
use Oleg\UserdirectoryBundle\Util\UserUtil;
use Oleg\OrderformBundle\Security\Util\SecurityUtil;


//ScanOrder joins Message + Scan
/**
 * Message controller.
 */
class MultiScanOrderController extends Controller {

    private $datastructure = null;
    //for testing data structure
    //private $datastructure = 'datastructure';


    /**
     * Creates a new Message entity.
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

        if( false === $this->get('security.authorization_checker')->isGranted('ROLE_SCANORDER_SUBMITTER')
        ) {
            return $this->redirect( $this->generateUrl('scan_home') );
        }

        $em = $this->getDoctrine()->getManager();

        $entity  = new Message();

        $user = $this->get('security.token_storage')->getToken()->getUser();

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

        //set order category
        $category = $em->getRepository('OlegOrderformBundle:MessageCategory')->findOneByName( $type );
        $entity->setMessageCategory($category);

        //$scanOrderInstitutionScope = $userSiteSettings->getScanOrderInstitutionScope();

        $orderUtil = $this->get('scanorder_utility');
        $permittedInstitutions = $orderUtil->getAllScopeInstitutions($permittedInstitutions,$entity);

        $params = array(
            'type'=>$type,  //category
            'cycle'=>'create',
            'user'=>$user,
            'em' => $em,
            'serviceContainer' => $this->container,
            'institutions' => $permittedInstitutions,
            //'scanOrderInstitutionScope'=>$scanOrderInstitutionScope,
            'datastructure'=>$this->datastructure
        );

        $form = $this->createForm(MessageType::class, $entity, array(
            'form_custom_value' => $params,
            'form_custom_value_entity' => $entity
        ));

        $form->handleRequest($request);

//        echo "provider2=".$entity->getProvider()."<br>";
        //oleg_orderformbundle_messagetype_patient_0_procedure_0_accession_0_accession_0_field
//        $patient = $form["patient"][0]->getData();
//        echo "patient=".$patient."<br>";
//        $mrn = $patient->getMrn()->first()->getField();
//        echo "mrn=".$mrn."<br>";
        //$dob = $patient->getDob()->first()->getField();
        //echo "dob=".$dob."<br>";
//
//        $accession = $form["patient"][0]["procedure"][0]["accession"][0]->getData();
//        $accessionNum = $accession->getAccession()->first()->getField();
//        $accessionType = $accession->getAccession()->first()->getKeytype();
//        //oleg_orderformbundle_messagetype_patient_0_procedure_0_accession_0_accession_0_keytype
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

        //$part = $form["patient"][0]["encounter"][0]["procedure"][0]["accession"][0]["part"][0]->getData();
        //echo "form diffdissident count=".count($part->getDiffDisident())."<br>";
        //$block = $form["patient"][0]["encounter"][0]["procedure"][0]["accession"][0]["part"][0]["block"][0]->getData();
        //echo "form special stain count=".count($block->getSpecialStains())."<br>";
        //echo "<br>block data:<br>";
        //print_r($block->getSpecialStains());
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

            //exit("Before validation main entity:<br>");
            if ($form->isValid()) {
                echo "form is valid !!! <br>";
            } else {
                echo "form is not valid ??? <br>";
            }
            echo "<br>errors:<br>" . $form->getErrors() . "<br>";
            echo "errors as string=" . $form->getErrorsAsString() . "<br>";
            //echo "order patient=".$entity->getPatient()->first();
        }

        //oleg_orderformbundle_messagetype_equipment
        //echo "equipmentForm=".$form["equipment"]->getData()."<br>";
        //echo "Equipment=".$entity->getEquipment()->getId().":".$entity->getEquipment()->getName()."<br>";
        //oleg_orderformbundle_messagetype_institution
        //$institutionForm = $form["institution"]->getData();
        //echo "institutionForm=".$institutionForm."<br>";
        //echo "permittedInstitution=".$entity->getInstitution()->getId().":".$entity->getInstitution()->getName()."<br>";
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
            $entity = $em->getRepository('OlegOrderformBundle:Message')->processMessageEntity( $entity, $user, $type, $this->get('router'), $this->container );

            if( isset($_POST['btnSubmit']) || isset($_POST['btnAmend']) || isset($_POST['btnSave']) || isset($_POST['btnSaveOnIdleTimeout']) ) {

                $conflictStr = "";
                foreach( $entity->getDataqualityMrnAcc() as $dq ) {
                    $conflictStr = $conflictStr . "<br>".$dq->getDescription()."<br>"."Resolved by replacing: ".$dq->getAccession()." => ".$dq->getNewaccession()."<br>";
                }

                //email
                $email = $user->getEmail();
                $scanEmailUtil = new ScanEmailUtil($em,$this->container);

                $submitStatusStr = null;
                if( isset($_POST['btnAmend']) ) {
                    $submitStatusStr = "has been successfully amended";
                } else
                if( isset($_POST['btnSave']) || isset($_POST['btnSaveOnIdleTimeout']) ) {
                    $submitStatusStr = "is saved but not submitted";
                }

                //$orderurl = $this->generateUrl( 'multy_show',array('id'=>$entity->getOid()), UrlGeneratorInterface::ABSOLUTE_URL ); //was $entity->getId()
                //$scanEmailUtil->sendEmail( $email, $entity, $orderurl, null, $conflictStr, $submitStatusStr );

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
     * Displays a form to create a new Message + Scan entities.
     *
     * @Route("/scan-order/one-slide/new", name="single_new")
     * @Route("/scan-order/multi-slide/new", name="multi_new")
     * @Method("GET")
     * @Template("OlegOrderformBundle:MultiScanOrder:new.html.twig")
     */
    public function newMultyAction(Request $request)
    {

        if( false === $this->get('security.authorization_checker')->isGranted('ROLE_SCANORDER_SUBMITTER') ) {
            return $this->redirect( $this->generateUrl('scan_home') );
        }

        $user = $this->get('security.token_storage')->getToken()->getUser();
        $em = $this->getDoctrine()->getManager();

        $orderUtil = $this->get('scanorder_utility');
        $userSecUtil = $this->get('user_security_utility');

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

        $entity = new Message();

        //set scan order
        $scanOrder = new ScanOrder();
        $scanOrder->setMessage($entity);

        //echo "MultiScanOrderController: User=".$user."<br>";
        //$email = $user->getEmail();

        $system = $securityUtil->getDefaultSourceSystem(); //'scanorder';
        $status = 'valid';

        $entity->setPurpose("For Internal Use by the Department of Pathology");

        $entity->setProvider($user);
        $orderUtil->setLastOrderWithProxyuser($user,$entity);

        //set Source object
        $source = new Endpoint();
        $source->setSystem($system);
        $entity->addSource($source);

        //set Destination object
        $destination = new Endpoint();
        //$destination->setSystem($system);
        $entity->addDestination($destination);

        $patient = new Patient(true,$status,$user,$system);
        $entity->addPatient($patient);

        $encounter = new Encounter(true,$status,$user,$system);
        $patient->addEncounter($encounter);

        $procedure = new Procedure(true,$status,$user,$system);
        $encounter->addProcedure($procedure);

        $accession = new Accession(true,$status,$user,$system);
        $procedure->addAccession($accession);

        $part = new Part(true,$status,$user,$system);
        $accession->addPart($part);

        $block = new Block(true,$status,$user,$system);
        $part->addBlock($block);

        $slide = new Slide(true,'valid',$user,$system); //Slides are always valid by default
        $block->addSlide($slide);

        $edu = new Educational();
        $entity->setEducational($edu);

        $res = new Research();
        $entity->setResearch($res);

        $routeName = $request->get('_route');
        //echo "newMultyAction: routeName=".$routeName."<br>";

        if( $routeName == "multi_new") {
            $type = "Multi-Slide Scan Order";
        } elseif( $routeName == "single_new") {
            $type = "One-Slide Scan Order";
        } else {
            $type = "One-Slide Scan Order";
        }

        //set order category
        $category = $em->getRepository('OlegOrderformBundle:MessageCategory')->findOneByName( $type );
        $entity->setMessageCategory($category);

        //set the default service (now institution)
        //TODO: implement it
//        echo 'default inst='.$userSiteSettings->getInstitution()."<br>";
//        //$entity->setInstitution($userSiteSettings->getInstitution());
//        $entity->getScanorder()->setInstitution($userSiteSettings->getInstitution());

        ////////////////// set previous service from the last order if default is null //////////////////
        ////////////////// set previous institution from the last order if default getDefaultInstitution is null //////////////////
        //TODO: implement it
        if( !$userSiteSettings->getDefaultInstitution() ) {
            //echo "find prev service <br>";
            $previousOrder = $orderUtil->getPreviousMessage('Scan Order');
            //echo $previousOrder;
            //$this->getDoctrine()->getRepository('OlegOrderformBundle:Message')->findBy(array(), array('orderdate' => 'ASC'),1); //limit to one result
            if( $previousOrder ) {
                if( $previousOrder->getScanOrder() ) {
                    //echo "prev service=".$previousOrder->getScanOrder()->getService()->getName()."<br>";
                    //$entity->getScanOrder()->setService($previousOrder->getScanOrder()->getService());
                    $entity->getScanOrder()->setScanOrderInstitutionScope($previousOrder->getScanOrder()->getScanOrderInstitutionScope());
                }
                //echo "prev service set<br>";
            }
        } else {
            $entity->getScanOrder()->setScanOrderInstitutionScope($userSiteSettings->getDefaultInstitution());
        }
        //echo 'default ScanOrderInstitutionScope='.$entity->getScanOrder()->getScanOrderInstitutionScope()."<br>";
        ////////////////// EOF set previous service from the last order if default is null //////////////////

        $permittedInstitutions = $orderUtil->getAllScopeInstitutions($permittedInstitutions,$entity);

        //set Institutional PHI Scope
        $entity->setInstitution($permittedInstitutions->first());

        //set Performing organization:
        //"Weill Cornell Medical College > Department of Pathology and Laboratory Medicine > Pathology Informatics > Scanning Service"
        $orderUtil->setDefaultPerformingOrganization($entity);

        //set "Slide Delivery"
        $defaultDelivery = $userSecUtil->getNotEmptyDefaultSiteParameter('defaultScanDelivery','OlegOrderformBundle:OrderDelivery');
        $scanOrder->setDelivery($defaultDelivery);

        //set "Scanner"
        $defaultDelivery = $userSecUtil->getNotEmptyDefaultSiteParameter('defaultScanner','Oleg\UserdirectoryBundle\Entity\Equipment');
        $entity->setEquipment($defaultDelivery);

        //set default department and division
        //TODO: implement it
//        $defaultsDepDiv = $securityUtil->getDefaultDepartmentDivision($entity,$userSiteSettings);
//        $department = $defaultsDepDiv['department'];
//        $division = $defaultsDepDiv['division'];
        //$scanOrderInstitutionScope = $userSiteSettings->getScanOrderInstitutionScope();

        $params = array(
            'type'=>$type,
            'cycle'=>'new',
            'institutions' => $permittedInstitutions,
            //'scanOrderInstitutionScope'=>$scanOrderInstitutionScope,
            'user'=>$user,
            'em' => $em,
            'serviceContainer' => $this->container,
            //'division'=>$division,
            //'department'=>$department,
            'destinationLocation'=>$orderUtil->getOrderReturnLocations($entity),
            'datastructure'=>$this->datastructure
        );
        $form = $this->createForm(MessageType::class, $entity, array(
            'form_custom_value' => $params,
            'form_custom_value_entity' => $entity
        ));

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
     * Displays a form to view, update, amend an Message + Scan entities. $id is oid of the message object
     * @Route("/scan-order/{id}/edit", name="multy_edit", requirements={"id" = "\d+"})
     * @Route("/scan-order/{id}/amend", name="order_amend", requirements={"id" = "\d+"})
     * @Route("/scan-order/{id}/show", name="multy_show", requirements={"id" = "\d+"})
     * @Route("/scan-order/data-structure/{id}/show", name="scan_datastructure", requirements={"id" = "\d+"})
     * @Method("GET")
     * @Template("OlegOrderformBundle:MultiScanOrder:new.html.twig")
     */
    public function showMultyAction( Request $request, $id, $type = "show" )
    {

        if( false === $this->get('security.authorization_checker')->isGranted('ROLE_SCANORDER_SUBMITTER') &&
            false === $this->get('security.authorization_checker')->isGranted('ROLE_SCANORDER_ORDERING_PROVIDER')
        ) {
            return $this->redirect( $this->generateUrl('scan-nopermission') );
        }

        $em = $this->getDoctrine()->getManager();

        $user = $this->get('security.token_storage')->getToken()->getUser();

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

        //INNER JOIN message.block block
//        INNER JOIN message.patient patient
//        INNER JOIN message.procedure procedure
//        INNER JOIN message.accession accession
//        INNER JOIN message.part part
//        INNER JOIN message.slide slide
        $query = $em->createQuery('
            SELECT message
            FROM OlegOrderformBundle:Message message
            WHERE message.oid = :oid'
        )->setParameter('oid', $id);

        $entities = $query->getResult();

        //echo "<br>message count=".count( $entities )."<br>";

        if( count( $entities ) == 0 ) {
            throw $this->createNotFoundException('More than one Message entity found with oid='.$id);
        } else {
            $entity = $entities[0];
        }

        //////////////// testing: order memory usage ////////////////
//        $mem = memory_get_usage(true);
//        $entity_tmp = clone $entity;
//        $mem = memory_get_usage(true) - $mem;
//        echo "order mem old = 2.36 Mb<br>";
//        echo "order mem = ".$mem. " => " .round($mem/1000000,2)." Mb<br>";
//        unset($entity_tmp);
        //////////////// EOF order memory usage ////////////////

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

        $collaborationTypesStrArr = array("Union");

        $datastructure = null;
        if( $routeName == "scan_datastructure") {
            $actions = array('edit'); //show extra fields
            $datastructure = "datastructure";
            $system = $securityUtil->getDefaultSourceSystem();
        }

        //Note: can be replaced by voter:
        //if( $entity && !$this->get('security.authorization_checker')->isGranted(implode(",",$actions),$entity) ) {
        if( $entity && !$securityUtil->isUserAllowOrderActions($entity, $user, $actions) ) {
            //exit('isUserAllowOrderActions false');
            return $this->redirect( $this->generateUrl('scan-nopermission') );
        }
        //exit('isUserAllowOrderActions true');

        $messageCategory = $entity->getMessageCategory()->getName()."";

        //redirect to show table view controller if form type is "Table-View Scan Order"
        if( $messageCategory == "Table-View Scan Order" ) {
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

        $extraStatus = 'valid'; //invalid - to not show extra fields on view

        //patient
        foreach( $entity->getPatient() as $patient ) {

            //check if patient has this message
            if( !$this->hasMessage($patient,$id) ) {
                $entity->removePatient($patient);
                continue;
            }

            //$viewGranted = $this->denyAccessUnlessGranted('view', $patient);
            //echo "viewGranted=".$viewGranted."<br>";
            //$actions = array('show111');
            if( !$securityUtil->hasUserPermission( $patient, $user, $collaborationTypesStrArr, $actions ) ) {
            //if( false === $this->get('security.authorization_checker')->isGranted('view', $patient) ) { // check for "view" access: calls all voters
                $entity->removePatient($patient);
                continue;
            }

            if( $datastructure ) {
                $patient->addExtraFields($extraStatus,$user,$system);
            }

            //encounter
            foreach( $patient->getEncounter() as $encounter ) {

                if( !$this->hasMessage($encounter,$id) ) {
                    $patient->removeEncounter($encounter);
                    continue;
                }

                if( !$securityUtil->hasUserPermission($encounter, $user, $collaborationTypesStrArr, $actions) ) {
                    $patient->removeChildren($encounter);
                    continue;
                }

                if( $datastructure ) {
                    $encounter->addExtraFields($extraStatus,$user,$system);
                }

                //procedure
                foreach( $encounter->getProcedure() as $procedure ) {

                    if( !$this->hasMessage($procedure,$id) ) {
                        $encounter->removeProcedure($procedure);
                        continue;
                    }

                    if( !$securityUtil->hasUserPermission($procedure, $user, $collaborationTypesStrArr, $actions) ) {
                        $encounter->removeChildren($procedure);
                        continue;
                    }

                    if( $datastructure ) {
                        $procedure->addExtraFields($extraStatus,$user,$system);
                    }

                    //accession
                    foreach( $procedure->getAccession() as $accession ) {

                        //echo "accession".$accession."<br>";

                        if( !$this->hasMessage($accession,$id) ) {
                            $procedure->removeAccession($accession);
                            continue;
                        }

                        if( !$securityUtil->hasUserPermission($accession, $user, $collaborationTypesStrArr, $actions) ) {
                            //echo "accession permission not ok!!! <br>";
                            $procedure->removeChildren($accession);
                            continue;
                        } else {
                            //echo "accession permission ok <br>";
                        }

                        if( $datastructure ) {
                            $accession->addExtraFields($extraStatus,$user,$system);
                        }

                        //part
                        foreach( $accession->getPart() as $part ) {
                           if( !$this->hasMessage($part,$id) ) {
                                $accession->removePart($part);
                                continue;
                            }

                            if( !$securityUtil->hasUserPermission($part, $user, $collaborationTypesStrArr, $actions) ) {
                                $accession->removeChildren($part);
                                continue;
                            }

                            $part->createEmptyArrayFields();

                            //block
                            foreach( $part->getBlock() as $block ) {
                                if( !$this->hasMessage($block,$id) ) {
                                    $part->removeBlock($block);
                                    continue;
                                }

                                if( ! $securityUtil->hasUserPermission($block, $user, $collaborationTypesStrArr, $actions) ) {
                                    $part->removeChildren($block);
                                    continue;
                                }

                                $block->createEmptyArrayFields();

                                //slide
                                foreach( $block->getSlide() as $slide ) {

                                    //check if this slides can be viewed by this user
                                    $permission = true;
                                    if( !$securityUtil->hasUserPermission($slide, $user, $collaborationTypesStrArr, $actions) ) {
                                        //echo " (".$slide->getProvider()->getId().") ?= (".$user->getId().") => ";
                                        if( $slide->getProvider()->getId() != $user->getId() ) {
                                            $permission = false;
                                        }
                                    }

                                    //echo "permission=".$permission;

                                    if( !$this->hasMessage($slide,$id) || !$permission ) {
                                        $block->removeSlide($slide);
                                        $entity->removeSlide($slide);
                                        continue;
                                    }


                                }//slide

                            }//block
                        }//part
                    }//accession
                }//procedure
            }//encounter
        }//patient

        //echo "<br>Procedure count=".count( $entity->getProcedure() );
        //echo "<br>Slide count=".count( $entity->getSlide() );

        if( count( $entity->getSlide() ) == 0 ) {
            //this message does not have slides to show or the user don't have permission to view this message's slides
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
            if( $datastructure == 'datastructure' ) {
                //testing data structure
                $disable = false;
                $type = "edit";
            } else {
                $disable = true;
                $type = "show";
            }


        }

        //echo "show id=".$entity->getId()."<br>";
        //use always multy because we use nested forms to display single and multy slide orders
        $single_multy = $entity->getMessageCategory()->getName();

        if( $single_multy == 'single' ) {
            $single_multy = 'multy';
        }

        //include current message institution to the $permittedInstitutions
//        if( $entity->getInstitution() && !$permittedInstitutions->contains($entity->getInstitution()) ) {
//            $permittedInstitutions->add($entity->getInstitution());
//        }
        $permittedInstitutions = $orderUtil->getAllScopeInstitutions($permittedInstitutions,$entity);

        //echo "route=".$routeName.", type=".$type."<br>";

        //$scanOrderInstitutionScope = $userSiteSettings->getScanOrderInstitutionScope();

        //set default department and division
        //$defaultsDepDiv = $securityUtil->getDefaultDepartmentDivision($entity,$userSiteSettings);
        //$department = $defaultsDepDiv['department'];
        //$division = $defaultsDepDiv['division'];

        $params = array(
            'type' => $single_multy,
            'cycle' => $type,
            'institutions' => $permittedInstitutions,
            //'scanOrderInstitutionScope'=>$scanOrderInstitutionScope,
            'user' => $user,
            'em' => $em,
            'serviceContainer' => $this->container,
            //'division'=>$division,
            //'department'=>$department,
            'datastructure' => $datastructure
        );
        $form = $this->createForm(MessageType::class, $entity, array(
            'form_custom_value' => $params,
            'form_custom_value_entity' => $entity,
            'disabled' => $disable
        ));

        //echo "type=".$entity->getMessageCategory();
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
            $dql->innerJoin("h.message", "message");
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
            'formtype' => $entity->getMessageCategory()->getName(),
            'history' => $history,
            'amendable' => $securityUtil->isUserAllowOrderActions($entity, $user, array('amend')),
            'changestatus' => $securityUtil->isUserAllowOrderActions($entity, $user, array('changestatus')),
            'datastructure' => $datastructure
        );


    }

    public function hasMessage( $entity, $id ) {
        $has = false;
        foreach( $entity->getMessage() as $child ) {
            if( $child->getOid() == $id ) {
                $has = true;
                break;
            }
        }
        return $has;
    }

    /**
     * Displays a form to create a new Message + Scan entities.
     * @Route("/scan-order/download/{id}", name="download_file", requirements={"id" = "\d+"})
     * @Method("GET")
     */
    public function downloadAction($id) {

        $em = $this->getDoctrine()->getManager();
        $file = $em->getRepository('OlegUserdirectoryBundle:Document')->findOneById($id);

        $html =     //"header('Content-type: application/pdf');".
                    "header('Content-Disposition: attachment; filename=".$file->getName()."');".
                    "readfile('".$file->getPath()."');";

        return $html;

    }




}
