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

/**
 * Created by JetBrains PhpStorm.
 * User: oli2002
 * Date: 4/28/14
 * Time: 1:34 PM
 * To change this template use File | Settings | File Templates.
 */

namespace App\OrderformBundle\Controller;


use App\OrderformBundle\Entity\PartParttitle;
use App\UserdirectoryBundle\Form\DataTransformer\GenericTreeTransformer;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Form\Extension\Core\DataTransformer\DateTimeToStringTransformer;

use App\OrderformBundle\Entity\Message;
use App\OrderformBundle\Form\MessageType;

use App\OrderformBundle\Entity\Patient;
use App\OrderformBundle\Entity\ClinicalHistory;
use App\OrderformBundle\Entity\PatientMrn;
use App\OrderformBundle\Entity\PatientDob;
use App\OrderformBundle\Entity\PatientClinicalHistory;

use App\OrderformBundle\Entity\Procedure;
use App\OrderformBundle\Entity\ProcedureEncounter;
use App\OrderformBundle\Entity\ProcedureName;

//use App\OrderformBundle\Entity\ProcedurePatsuffix;
//use App\OrderformBundle\Entity\ProcedurePatlastname;
//use App\OrderformBundle\Entity\ProcedurePatfirstname;
//use App\OrderformBundle\Entity\ProcedurePatmiddlename;
//use App\OrderformBundle\Entity\ProcedurePatsex;
//use App\OrderformBundle\Entity\ProcedurePatage;
//use App\OrderformBundle\Entity\ProcedurePathistory;
//use App\OrderformBundle\Entity\ProcedureEncounterDate;

use App\OrderformBundle\Entity\Accession;
use App\OrderformBundle\Entity\AccessionAccession;
use App\OrderformBundle\Entity\AccessionAccessionDate;

use App\OrderformBundle\Entity\Part;
use App\OrderformBundle\Entity\PartPartname;
use App\OrderformBundle\Entity\PartSourceOrgan;
use App\OrderformBundle\Entity\PartDescription;
use App\OrderformBundle\Entity\PartDisident;
use App\OrderformBundle\Entity\PartDiffDisident;
use App\OrderformBundle\Entity\PartDiseaseType;
use App\OrderformBundle\Entity\PartPaper;

use App\OrderformBundle\Entity\Block;
use App\OrderformBundle\Entity\BlockBlockname;
use App\OrderformBundle\Entity\BlockSectionsource;

use App\OrderformBundle\Entity\RelevantScans;
use App\OrderformBundle\Entity\BlockSpecialStains;
use App\OrderformBundle\Entity\Slide;
use App\OrderformBundle\Entity\Stain;

use App\OrderformBundle\Entity\Educational;
use App\OrderformBundle\Entity\Research;

use App\OrderformBundle\Form\SlideMultiType;

use App\OrderformBundle\Helper\ErrorHelper;
use App\OrderformBundle\Helper\ScanEmailUtil;
use App\OrderformBundle\Security\Util\SecurityUtil;
use App\UserdirectoryBundle\Util\UserUtil;

use App\OrderformBundle\Form\DataTransformer\ProcedureTransformer;
use App\OrderformBundle\Form\DataTransformer\MrnTypeTransformer;
use App\OrderformBundle\Form\DataTransformer\AccessionTypeTransformer;
use App\OrderformBundle\Form\DataTransformer\SourceOrganTransformer;
use App\OrderformBundle\Form\DataTransformer\StainTransformer;

use App\UserdirectoryBundle\Form\DataTransformer\StringTransformer;

use App\OrderformBundle\Entity\Encounter;
use App\OrderformBundle\Entity\EncounterDate;
use App\OrderformBundle\Entity\EncounterPatfirstname;
use App\OrderformBundle\Entity\EncounterPathistory;
use App\OrderformBundle\Entity\EncounterPatlastname;
use App\OrderformBundle\Entity\EncounterPatmiddlename;
use App\OrderformBundle\Entity\EncounterPatsex;
use App\OrderformBundle\Entity\EncounterPatsuffix;
use App\OrderformBundle\Entity\Endpoint;
use App\OrderformBundle\Entity\Imaging;
use App\OrderformBundle\Entity\ProcedureDate;
use App\OrderformBundle\Entity\ProcedureNumber;
use App\OrderformBundle\Entity\EncounterPatage;
use App\OrderformBundle\Entity\ScanOrder;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;


class TableController extends Controller {

    /**
     * @Route("/scan-order/multi-slide-table-view/{id}/amend", name="table_amend", requirements={"id" = "\d+"})
     * @Route("/scan-order/multi-slide-table-view/{id}/show", name="table_show", requirements={"id" = "\d+"})
     * @Route("/scan-order/multi-slide-table-view/{id}/edit", name="table_edit", requirements={"id" = "\d+"})
     * @Template("AppOrderformBundle:MultiScanOrder:newtable.html.twig")
     */
    public function multiTableShowAction( Request $request, $id ) {

        if( false === $this->get('security.authorization_checker')->isGranted('ROLE_SCANORDER_SUBMITTER') &&
            false === $this->get('security.authorization_checker')->isGranted('ROLE_SCANORDER_ORDERING_PROVIDER')
        ) {
            return $this->redirect( $this->generateUrl('scan-nopermission') );
        }

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

        $em = $this->getDoctrine()->getManager();

        $message = $em->getRepository('AppOrderformBundle:Message')->findOneByOid($id);

        if( $routeName == "table_show") {
            $actions = array('show');
        }
        if( $routeName == "table_amend") {
            $actions = array('amend');
        }
        if( $routeName == "table_edit") {
            $actions = array('edit');
        }

        $secUtil = $this->get('order_security_utility');
        if( $message && !$secUtil->isUserAllowOrderActions($message, $user, $actions) ) {
            return $this->redirect( $this->generateUrl('scan-nopermission') );
        }

        //redirect by status
        $orderUtil = $this->get('scanorder_utility');
        $redirect = $orderUtil->redirectOrderByStatus($message,$routeName);
        if( $redirect ) {
            return $redirect;
        }

        $transformer = new DateTimeToStringTransformer(null,null,'m/d/Y');

        $type = "show";
        $disable = true;

        //echo "route name=".$routeName."<br>";
        if( $routeName == "table_amend") {
            $disable = false;
            $type = "amend";
            //echo "amend! <br>";
        }

        if( $routeName == "table_edit") {
            $disable = false;
            $type = "edit";
            //echo "amend! <br>";
        }

        if( $message->getStatus() == "Submitted" || $message->getStatus() == "Amended" || $message->getStatus() == "Not Submitted" ) {
            $fieldstatus = "valid";
        } else
        if( $message->getStatus() == "Superseded" ) {
            //status for superseded, canceled can be "deleted-by-amended-order" or "canceled-by-amended-order" or "valid". By setting status to null, we saying that we do not know the status, so the first
            $fieldstatus = "deleted-by-amended-order";
        } else {
            //status for all other types including canceled can be "canceled-by-amended-order" or "valid".
            //By setting status to null, we saying that we do not know the status, so we will use the first field belonging to this order id (obtainStatusField will return the first field with provided order id)
            $fieldstatus = null;
        }

        //$permittedServices = $userSiteSettings->getScanOrdersServicesScope();
        $scanOrderInstitutionScope = $userSiteSettings->getScanOrderInstitutionScope();

        //set default department and division
        //$defaultsDepDiv = $securityUtil->getDefaultDepartmentDivision($message,$userSiteSettings);
        //$department = $defaultsDepDiv['department'];
        //$division = $defaultsDepDiv['division'];

        $permittedInstitutions = $orderUtil->getAllScopeInstitutions($permittedInstitutions,$message);

        $params = array(
            'type' => $message->getMessageCategory()->getName(),
            'cycle' => $type,
            'institutions' => $permittedInstitutions,
            //'services'=>$permittedServices,
            'user'=>$user,
            'em' => $em,
            //'division'=>$division,
            //'department'=>$department
        );
        $form = $this->createForm(MessageType::class, $message, array(
            'form_custom_value' => $params,
            'form_custom_value_entity' => $message,
            'disabled' => $disable
        ));

        //$slides = $message->getSlide();
        $query = $em->createQuery('
            SELECT slide
            FROM AppOrderformBundle:Slide slide
            INNER JOIN slide.message message
            WHERE message.oid = :id
            ORDER BY slide.sequence ASC'
        )->setParameter('id', $id);

        $slides = $query->getResult();

        $jsonData = array();

        foreach( $slides as $slide ) {

            $block = $slide->getBlock();
            $part = $block->getPart();
            $accession = $part->getAccession();
            $procedure = $accession->getProcedure();
            $encounter = $procedure->getEncounter();
            $patient = $encounter->getPatient();

            //accession: 2
            $acckey = $accession->obtainValidKeyField();
            $rowArr['Accession Type']['id'] = $acckey->getId();
            $rowArr['Accession Type']['value'] = $acckey->getKeytype()->getName();
            $rowArr['Accession Number']['id'] = $acckey->getId();
            $rowArr['Accession Number']['value'] = $acckey->getField();

            //part: 1
            $partname = $part->obtainValidKeyField();
            $rowArr['Part ID']['id'] = $partname->getId();
            $rowArr['Part ID']['value'] = $partname->getField();

            //block: 1
            $blockname = $block->obtainValidKeyField();
            $rowArr['Block ID']['id'] = $blockname->getId();
            $rowArr['Block ID']['value'] = $blockname->getField();

            //slide: 4
            $stain = $slide->getStain()->first();
            $rowArr['Stain']['id'] = $stain->getId();
            $rowArr['Stain']['value'] = $stain->getField()->getName();

            $scan = $slide->getScan()->first();
            $rowArr['Scan Magnificaiton']['id'] = $scan->getId();
            $rowArr['Scan Magnificaiton']['value'] = $scan->getMagnification()."";

            //echo "part:".$part;
            $partdiadnosis = $part->obtainStatusField('disident',$fieldstatus,$id);
            if( $partdiadnosis ) {
                $rowArr['Diagnosis']['id'] = $partdiadnosis->getId();
                $rowArr['Diagnosis']['value'] = $partdiadnosis->getField();
            }

            $rowArr['Reason for Scan/Note']['id'] = $scan->getId();
            $rowArr['Reason for Scan/Note']['value'] = $scan->getNote();

            //part 1
            $sourceorgan = $part->obtainStatusField('sourceOrgan',$fieldstatus,$id);
            if( $sourceorgan ) {
                $rowArr['Source Organ']['id'] = $sourceorgan->getId();
                $rowArr['Source Organ']['value'] = ( $sourceorgan->getField() ? $sourceorgan->getField()->getName() : null );
            }

            //part 2
            $parttitle = $part->obtainStatusField('parttitle',$fieldstatus,$id);
            if( $parttitle ) {
                $rowArr['Part Title']['id'] = $parttitle->getId();
                $rowArr['Part Title']['value'] = ( $parttitle->getField() ? $parttitle->getField()->getName() : null );
            }

            //patient: 4
            $patientkey = $patient->obtainValidKeyField();
            $rowArr['MRN Type']['id'] = $patientkey->getId();
            $rowArr['MRN Type']['value'] = $patientkey->getKeytype()->getName();
            $rowArr['MRN']['id'] = $patientkey->getId();
            $rowArr['MRN']['value'] = $patientkey->getField();

            $dob = $patient->obtainStatusField('dob',$fieldstatus,$id);
            if( $dob ) {
                $rowArr['Patient DOB']['id'] = $dob->getId();
                $rowArr['Patient DOB']['value'] = $transformer->transform($dob->getField());
            }

            $clinicalHistory = $patient->obtainStatusField('clinicalHistory',$fieldstatus,$id);
            if( $clinicalHistory ) {
                $rowArr['Clinical Summary']['id'] = $clinicalHistory->getId();
                $rowArr['Clinical Summary']['value'] = $clinicalHistory->getField();
            }

            //accession: 1
            $accessionDate = $accession->obtainStatusField('accessionDate',$fieldstatus,$id);
            if( $accessionDate ) {
                $rowArr['Accession Date']['id'] = $accessionDate->getId();
                $rowArr['Accession Date']['value'] = $transformer->transform($accessionDate->getField());
            }

            //procedure: 1
            $proceduretype = $procedure->getName()->first();
            $rowArr['Procedure Type']['id'] = $proceduretype->getId();
            $rowArr['Procedure Type']['value'] = ( $proceduretype->getField() ? $proceduretype->getField()->getId() : null );

            //encounter: 8
            $encounterdate = $encounter->obtainStatusField('date',$fieldstatus,$id);
            if( $encounterdate ) {
                $rowArr['Encounter Date']['id'] = $encounterdate->getId();
                $rowArr['Encounter Date']['value'] = $transformer->transform($encounterdate->getField());
            }

            $patsuffix = $encounter->obtainStatusField('patsuffix',$fieldstatus,$id);
            if( $patsuffix ) {
                $rowArr["Patient's Suffix"]['id'] = $patsuffix->getId();
                $rowArr["Patient's Suffix"]['value'] = $patsuffix->getField();
            }

            $patlastname = $encounter->obtainStatusField('patlastname',$fieldstatus,$id);
            if( $patlastname ) {
                $rowArr["Patient's Last Name"]['id'] = $patlastname->getId();
                $rowArr["Patient's Last Name"]['value'] = $patlastname->getField();
            }

            $patfirstname = $encounter->obtainStatusField('patfirstname',$fieldstatus,$id);
            if( $patfirstname ) {
                $rowArr["Patient's First Name"]['id'] = $patfirstname->getId();
                $rowArr["Patient's First Name"]['value'] = $patfirstname->getField();
            }

            $patmiddlename = $encounter->obtainStatusField('patmiddlename',$fieldstatus,$id);
            if( $patmiddlename ) {
                $rowArr["Patient's Middle Name"] = $patmiddlename->getId();
                $rowArr["Patient's Middle Name"] = $patmiddlename->getField();
            }

            $patsex = $encounter->obtainStatusField('patsex',$fieldstatus,$id);
            if( $patsex ) {
                $rowArr['Patient Sex']['id'] = $patsex->getId();
                $rowArr['Patient Sex']['value'] = $patsex->getField();
            }

            $patage = $encounter->obtainStatusField('patage',$fieldstatus,$id);
            if( $patage ) {
                $rowArr['Patient Age']['id'] = $patage->getId();
                $rowArr['Patient Age']['value'] = $patage->getField();
            }

            $pathistory = $encounter->obtainStatusField('pathistory',$fieldstatus,$id);
            if( $pathistory ) {
                $rowArr['Clinical History']['id'] = $pathistory->getId();
                $rowArr['Clinical History']['value'] = $pathistory->getField();
            }

            //part: 5
            $description = $part->obtainStatusField('description',$fieldstatus,$id);
            if( $description ) {
                $rowArr['Gross Description']['id'] = $description->getId();
                $rowArr['Gross Description']['value'] = $description->getField();
            }

            $diffDisident = $part->obtainStatusField('diffDisident',$fieldstatus,$id);
            if( $diffDisident ) {
                $rowArr['Differential Diagnoses']['id'] = $diffDisident->getId();
                $rowArr['Differential Diagnoses']['value'] = $diffDisident->getField();
            }

            $diseaseType = $part->obtainStatusField('diseaseType',$fieldstatus,$id);
            if( $diseaseType ) {
                $rowArr['Type of Disease']['id'] = $diseaseType->getId();
                //$rowArr['Type of Disease']['value'] = $diseaseType->getField();
                $rowArr['Type of Disease']['value'] = $diseaseType->getDiseaseTypes()->first().""; //TODO: now it's multiple

                $rowArr['Origin of Disease']['id'] = $diseaseType->getId();
                //$rowArr['Origin of Disease']['value'] = $diseaseType->getOrigin();
                $rowArr['Origin of Disease']['value'] = $diseaseType->getDiseaseOrigins()->first().""; //TODO: now it's multiple

                $rowArr['Primary Site of Disease Origin']['id'] = $diseaseType->getId();
                $rowArr['Primary Site of Disease Origin']['value'] = ( $diseaseType->getPrimaryOrgan() ? $diseaseType->getPrimaryOrgan()->getName() : null );
            }

            //block: 3
            $sectionsource = $block->obtainStatusField('sectionsource',$fieldstatus,$id);
            if( $sectionsource ) {
                $rowArr['Block Section Source']['id'] = $sectionsource->getId();
                $rowArr['Block Section Source']['value'] = $sectionsource->getField();
            }

            $specialStains = $block->obtainStatusField('specialStains',$fieldstatus,$id);
            if( $specialStains ) {
                $rowArr['Associated Special Stain Name']['id'] = $specialStains->getId();
                $rowArr['Associated Special Stain Name']['value'] = $specialStains->getStaintype()->getName();
                $rowArr['Associated Special Stain Result']['id'] = $specialStains->getId();
                $rowArr['Associated Special Stain Result']['value'] = $specialStains->getField();
            }

            //slide: 5
            $rowArr['Slide Title']['id'] = $slide->getId();
            $rowArr['Slide Title']['value'] = $slide->getTitle();

            $rowArr['Slide Type']['id'] = $slide->getSlidetype()->getId();
            $rowArr['Slide Type']['id'] = $slide->getSlidetype()->getName();

            $rowArr['Microscopic Description']['id'] = $slide->getId();
            $rowArr['Microscopic Description']['value'] = $slide->getMicroscopicdescr();

            $rowArr['Link(s) to related image(s)']['id'] = $slide->getRelevantScans()->first()->getId();
            $rowArr['Link(s) to related image(s)']['value'] = $slide->getRelevantScans()->first()->getField();

            $rowArr['Region to Scan']['id'] = $scan->getId();
            $rowArr['Region to Scan']['value'] = $scan->getScanregion();

            $jsonData[] = $rowArr;
            //array_push($jsonData, $rowArr);
        }

        //print_r($jsonData);
        //var_dump($jsonData);

        //History
        $history = null;

        if( $routeName == "table_show") {

            //$history = $em->getRepository('AppOrderformBundle:History')->findByCurrentid( $entity->getOid(), array('changedate' => 'DESC') );
            $repository = $this->getDoctrine()->getRepository('AppOrderformBundle:History');
            $dql = $repository->createQueryBuilder("h");
            $dql->innerJoin("h.message", "message");
            $dql->leftJoin("h.eventtype", "eventtype");
            $dql->where("h.currentid = :oid AND (eventtype.name = 'Initial Order Submission' OR eventtype.name = 'Status Changed' OR eventtype.name = 'Amended Order Submission')");
            $dql->orderBy('h.changedate','DESC');
            $dql->setParameter('oid',$message->getOid());
            $history = $dql->getQuery()->getResult();

        }

        return $this->render('AppOrderformBundle:MultiScanOrder:newtable.html.twig', array(
            'orderdata' => json_encode($jsonData),
            'entity' => $message,
            'form' => $form->createView(),
            'type' => $type,
            'formtype' => $message->getMessageCategory()->getName(),
            'history' => $history,
            'amendable' => $secUtil->isUserAllowOrderActions($message, $user, array('amend')),
            'changestatus' => $secUtil->isUserAllowOrderActions($message, $user, array('changestatus'))
        ));

    }


    /**
     * @Route("/scan-order/multi-slide-table-view/new", name="table_create")
     * @Template("AppOrderformBundle:MultiScanOrder:newtable.html.twig")
     */
    public function multiTableCreationAction()
    {

        if( false === $this->get('security.authorization_checker')->isGranted('ROLE_SCANORDER_SUBMITTER') ) {
            return $this->redirect( $this->generateUrl('scan_home') );
        }

        $user = $this->get('security.token_storage')->getToken()->getUser();

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

        $em = $this->getDoctrine()->getManager();

        $entity = new Message();
        $scanOrder = new ScanOrder();
        $scanOrder->setMessage($entity);

        $system = $securityUtil->getDefaultSourceSystem();  //'scanorder';

        //set Source object
        $source = new Endpoint();
        $source->setSystem($system);
        $entity->addSource($source);
        //set Destination object
        $destination = new Endpoint();
        $entity->addDestination($destination);

        $entity->setPurpose("For Internal Use by the Department of Pathology");

        $entity->setProvider($user);
        $orderUtil->setLastOrderWithProxyuser($user,$entity);

        $patient = new Patient(true,'invalid',$user,$system);
        $entity->addPatient($patient);

        $edu = new Educational();
        $entity->setEducational($edu);

        $res = new Research();
        $entity->setResearch($res);

        ////////////////// set previous ScanOrderInstitutionScope from the last order if getDefaultInstitution is null //////////////////
        if( !$userSiteSettings->getDefaultInstitution() ) {
            $previousOrder = $orderUtil->getPreviousMessage('Scan Order');
            if( $previousOrder ) {
                if( $previousOrder->getScanOrder() ) {
                    $entity->getScanOrder()->setScanOrderInstitutionScope($previousOrder->getScanOrder()->getScanOrderInstitutionScope());
                }
            }
        } else {
            $entity->getScanOrder()->setScanOrderInstitutionScope($userSiteSettings->getDefaultInstitution());
        }
        ////////////////// EOF set previous ScanOrderInstitutionScope from the last order if default is null //////////////////

        //set default department and division
        //$defaultsDepDiv = $securityUtil->getDefaultDepartmentDivision($entity,$userSiteSettings);
        //$department = $defaultsDepDiv['department'];
        //$division = $defaultsDepDiv['division'];

        $type = "Table-View Scan Order";

        //set order category
        $category = $em->getRepository('AppOrderformBundle:MessageCategory')->findOneByName( $type );
        $entity->setMessageCategory($category);

        //$permittedInstitutions = $userSiteSettings->getPermittedInstitutionalPHIScope();
        $permittedInstitutions = $orderUtil->getAllScopeInstitutions($permittedInstitutions,$entity);

        //set Institutional PHI Scope
        $entity->setInstitution($permittedInstitutions->first());

        //set Performing organization:
        //"Weill Cornell Medical College > Department of Pathology and Laboratory Medicine > Pathology Informatics > Scanning Service"
        $orderUtil->setDefaultPerformingOrganization($entity);

        //set "Slide Delivery"
        $defaultDelivery = $userSecUtil->getNotEmptyDefaultSiteParameter('defaultScanDelivery','AppOrderformBundle:OrderDelivery');
        $scanOrder->setDelivery($defaultDelivery);

        //set "Scanner"
        $defaultDelivery = $userSecUtil->getNotEmptyDefaultSiteParameter('defaultScanner','App\UserdirectoryBundle\Entity\Equipment');
        $entity->setEquipment($defaultDelivery);

        //set $defaultAccessionType
        $defaultAccessionType = null;
        $defaultAccessionTypeEntity = $userSecUtil->getNotEmptyDefaultSiteParameter('defaultScanAccessionType',null);
        if( $defaultAccessionTypeEntity ) {
            $defaultAccessionType = $defaultAccessionTypeEntity->getName();
        }

        //$defaultMrnType
        $defaultMrnType = null;
        $defaultMrnTypeEntity = $userSecUtil->getNotEmptyDefaultSiteParameter('defaultScanMrnType',null);
        if( $defaultMrnTypeEntity ) {
            $defaultMrnType = $defaultMrnTypeEntity->getName();
        }

        $params = array(
            'type'=>$type,
            'cycle'=>'new',
            'institutions'=>$permittedInstitutions,
            //'services'=>$permittedServices,
            'user'=>$user,
            'em' => $em,
            //'division'=>$division,
            //'department'=>$department,
            'destinationLocation'=>$orderUtil->getOrderReturnLocations($entity)
        );
        $form = $this->createForm(MessageType::class, $entity, array(
            'form_custom_value' => $params,
            'form_custom_value_entity' => $entity
        ));

        return $this->render('AppOrderformBundle:MultiScanOrder:newtable.html.twig', array(
            'form' => $form->createView(),
            'cycle' => 'new',
            'formtype' => $type,
            'type' => 'new',
            'orderdata' => null,
            'defaultAccessionType' => $defaultAccessionType,
            'defaultMrnType' => $defaultMrnType
        ));
    }

    /**
     * Creates a new Table Message.

     * @Route("/scan-order/multi-slide-table-view/submit", name="table_create_submit")
     * @Method("POST")
     * @Template("AppOrderformBundle:MultiScanOrder:newtable.html.twig")
     */
    public function multyCreateAction(Request $request)
    {

        //echo "table new controller !!!! <br>";
        //$data = $request->request->all();
        //echo "data: => <br>";
        //var_dump($data);
        //echo " => ";
        //exit();

        $em = $this->getDoctrine()->getManager();

        $user = $this->get('security.token_storage')->getToken()->getUser();

        $entity = new Message();

        $type = "Table-View Scan Order";

        //set order category
        $category = $em->getRepository('AppOrderformBundle:MessageCategory')->findOneByName( $type );
        $entity->setMessageCategory($category);

        $params = array('type'=>$type, 'cycle'=>'new', 'service'=>null, 'user'=>$user, 'em' => $em);

        $form = $this->createForm(MessageType::class, $entity, array(
            'form_custom_value' => $params,
            'form_custom_value_entity' => $entity
        ));

        //$form->submit($request);
        $form->handleRequest($request);

//        if( $form->isValid() ) {
//            echo "form is valid <br>";
//        } else {
//            echo "form is not valid! <br>";
//        }

        $clickedbtn = $form->get('clickedbtn')->getData();
        //var_dump($clickedbtn);
        //exit("<br>afterpost");

        if( $clickedbtn == 'btnSubmit' ) {
            $cycle = 'new';
            $status = $em->getRepository('AppOrderformBundle:Status')->findOneByName('Submitted');
            $entity->setStatus($status);
        }

        if( $clickedbtn == 'btnAmend' ) {
            $cycle = 'amend';
            $status = $em->getRepository('AppOrderformBundle:Status')->findOneByName('Amended');
            $entity->setStatus($status);
        }

        if( $clickedbtn == 'btnSaveOnIdleTimeout' ) {
            $cycle = 'edit';
            $status = $em->getRepository('AppOrderformBundle:Status')->findOneByName('Not Submitted');
            $entity->setStatus($status);
        }


        //////////////// process handsontable rows ////////////////
        $datajson = $form->get('datalocker')->getData();

        $data = json_decode($datajson, true);
        //var_dump($data);

        if( $data == null ) {
            throw new \Exception( 'Table order data is null.' );
        }

        $rowCount = 0;

        //$headers = array_shift($data);
        $headers = $data["header"];
        //var_dump($headers);
        //echo "<br><br>";

        //echo "entity inst=".$entity->getInstitution()."<br>";
        //exit();

        $count = 0;
        foreach( $data["row"] as $row ) {
//            echo "<br>row:<br>";
//            var_dump($row);
//            echo "<br>";
            //exit();

            $accArr = $this->getValueByHeaderName('Accession Number',$row,$headers);
            $accValue = $accArr['val'];
            //echo "accValue=".$accValue." <br> ";

            if( !$accValue || $accValue == '' ) {
                continue;   //skip row if accession number is empty
            }

            //echo $rowCount.": accType=".$row[0].", acc=".$row[1]." \n ";
            $rowCount++;

            $patient = $this->constractPatientByTableData($row,$headers,$count);

            $entity->addPatient($patient);

            //echo $patient->getProcedure()->first()->getAccession()->first();

            $count++;

        }//foreach row
        //////////////// process handsontable rows ////////////////

        //exit('table order testing');

        $user = $this->get('security.token_storage')->getToken()->getUser();
        $entity->setProvider($user);

        //add dataqualities to entity
        $dataqualities = $form->get('conflicts')->getData();
        $orderUtil = $this->get('scanorder_utility');
        $orderUtil->setDataQualityAccMrn($entity,$dataqualities);

        $entity = $em->getRepository('AppOrderformBundle:Message')->processMessageEntity( $entity, $user, $type, $this->get('router'), $this->container );

//        $response = new Response();
//        $response->headers->set('Content-Type', 'application/json');
//        $response->setContent(json_encode('ok'));
//        return $response;

        $conflictStr = "";
        foreach( $entity->getDataqualityMrnAcc() as $dq ) {
            $conflictStr = $conflictStr . "<br>".$dq->getDescription()."<br>"."Resolved by replacing: ".$dq->getAccession()." => ".$dq->getNewaccession()."<br>";
        }

        $submitStatusStr = null;
        if( isset($_POST['btnAmend']) ) {
            $submitStatusStr = "has been successfully amended";
        } else
            if( isset($_POST['btnSave']) || isset($_POST['btnSaveOnIdleTimeout']) ) {
                $submitStatusStr = "is saved but not submitted";
            }

        $orderurl = $this->generateUrl( 'multy_show',array('id'=>$entity->getOid()), UrlGeneratorInterface::ABSOLUTE_URL );    //was $entity->getId()

        //email
        $scanEmailUtil = new ScanEmailUtil($em,$this->container);
        $scanEmailUtil->sendScanEmail( $user->getEmail(), $entity, $orderurl, null, $conflictStr, $submitStatusStr );

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

        unset($_POST);

        $session = $request->getSession();
        $submittedData = array(
            'oid' => $entity->getOid(),
            'cycle' => $cycle,
            'neworder' => "table_create",
            'conflicts' => $conflictsStr
        );
        $session->set('submittedData', $submittedData);

        unset($_POST);

        return $this->redirect($this->generateUrl('scan-order-submitted-get'));

    }

    public function constractPatientByTableData( $row, $columnData, $count ) {

        $force = true; //true - create fields even if the value is empty
        $status = "valid";
        $provider = $this->get('security.token_storage')->getToken()->getUser();
        $securityUtil = $this->get('order_security_utility');
        $system = $securityUtil->getDefaultSourceSystem();    //'scanorder';
        $em = $this->getDoctrine()->getManager();

        /////////////// Patient ///////////////////
        $patient = new Patient(false, $status, $provider, $system);

        //mrn
        $patientmrn = new PatientMrn($status,$provider,$system);
        $mrnTransformer = new MrnTypeTransformer($em,$provider);
        $mrntypeArr = $this->getValueByHeaderName('MRN Type',$row,$columnData);
        $mrntype = $mrnTransformer->reverseTransform($mrntypeArr['val']);
        $patientmrn->setId($mrntypeArr['id']);
        $patientmrn->setKeytype($mrntype);
        $mrnArr = $this->getValueByHeaderName('MRN',$row,$columnData);
        $patientmrn->setField($mrnArr['val']);
        $patientmrn->setOriginal($mrnArr['val']);
        $patient->addMrn($patientmrn);

        //dob
        $dobArr = $this->getValueByHeaderName('Patient DOB',$row,$columnData);
        if( $force || $dobArr['val'] && $dobArr['val'] != '' ) {
            $patientdob = new PatientDob($status,$provider,$system);
            if( $dobArr['val'] == "" ) {
                $dobFormat = NULL;
            } else {
                $dobFormat = new \DateTime($dobArr['val']);
            }
            //echo "dobFormat=".date('d/M/Y', $dobFormat)."<br>";
            $patientdob->setField($dobFormat);
            $patientdob->setId($dobArr['id']);
            $patient->addDob($patientdob);
        }

        //Clinical History
        $clsumArr = $this->getValueByHeaderName('Clinical Summary',$row,$columnData);
        if( $force || $clsumArr['val'] && $clsumArr['val'] != '' ) {
            $patientch = new PatientClinicalHistory($status,$provider,$system);
            $patientch->setField($clsumArr['val']);
            $patientch->setId($clsumArr['id']);
            $patient->addClinicalHistory($patientch);
        }

        ///////////////// Encounter /////////////////
        $encounter = new Encounter(false, $status, $provider, $system);
        $patient->addEncounter($encounter);

        //add encounter simple fields
        //Encounter Date
        $encounterDateArr = $this->getValueByHeaderName('Encounter Date',$row,$columnData);
        if( $force || $encounterDateArr['val'] && $encounterDateArr['val'] != '' ) {
            if( $encounterDateArr['val'] == "" ) {
                $encounterDateFormat = NULL;
            } else {
                $encounterDateFormat = new \DateTime($encounterDateArr['val']);
            }
            $encounterDateObj = new EncounterDate($status,$provider,$system);
            $encounterDateObj->setField($encounterDateFormat);
            $encounterDateObj->setId($encounterDateArr['id']);
            $encounter->addDate($encounterDateObj);
        }

        //Encounter Suffix
        $patsuffixArr = $this->getValueByHeaderName("Patient's Suffix",$row,$columnData);
        if( $force || $patsuffixArr['val'] && $patsuffixArr['val'] != '' ) {
            $patsuffixObj = new EncounterPatsuffix($status,$provider,$system);
            $patsuffixObj->setField($patsuffixArr['val']);
            $patsuffixObj->setId($patsuffixArr['id']);
            $encounter->addPatsuffix($patsuffixObj);
        }

        //Encounter Last Name
        $patlastnameArr = $this->getValueByHeaderName("Patient's Last Name",$row,$columnData);
        if( $force || $patlastnameArr['val'] && $patlastnameArr['val'] != '' ) {
            $patlastnameObj = new EncounterPatlastname($status,$provider,$system);
            $patlastnameObj->setField($patlastnameArr['val']);
            $patlastnameObj->setId($patlastnameArr['id']);
            $encounter->addPatlastname($patlastnameObj);
        }

        //Encounter First Name
        $patfirstnameArr = $this->getValueByHeaderName("Patient's First Name",$row,$columnData);
        if( $force || $patfirstnameArr['val'] && $patfirstnameArr['val'] != '' ) {
            $patfirstnameObj = new EncounterPatfirstname($status,$provider,$system);
            $patfirstnameObj->setField($patfirstnameArr['val']);
            $patfirstnameObj->setId($patfirstnameArr['id']);
            $encounter->addPatfirstname($patfirstnameObj);
        }

        //Encounter Middle Name
        $patmiddlenameArr = $this->getValueByHeaderName("Patient's Middle Name",$row,$columnData);
        if( $force || $patmiddlenameArr['val'] && $patmiddlenameArr['val'] != '' ) {
            $patmiddlenameObj = new EncounterPatmiddlename($status,$provider,$system);
            $patmiddlenameObj->setField($patmiddlenameArr['val']);
            $patmiddlenameObj->setId($patmiddlenameArr['id']);
            $encounter->addPatmiddlename($patmiddlenameObj);
        }

        //Encounter Sex
        $patsexArr = $this->getValueByHeaderName('Patient Sex',$row,$columnData);
        if( $force || $patsexArr['val'] && $patsexArr['val'] != '' ) {
            $patsexObj = new EncounterPatsex($status,$provider,$system);
            $sexlist = $em->getRepository('AppUserdirectoryBundle:SexList')->findOneByName($patsexArr['val']);
            $patsexObj->setField($sexlist);
            $patsexObj->setId($patsexArr['id']);
            $encounter->addPatsex($patsexObj);
        }

        //Encounter Age
        $patageArr = $this->getValueByHeaderName('Patient Age',$row,$columnData);
        if( $force || $patageArr['val'] && $patageArr['id'] != '' ) {
            $patageObj = new EncounterPatage($status,$provider,$system);
            $patageObj->setField($patageArr['val']);
            $patageObj->setId($patageArr['id']);
            $encounter->addPatage($patageObj);
        }

        //Encounter Clinical History
        $pathistoryArr = $this->getValueByHeaderName('Clinical History',$row,$columnData);
        if( $force || $pathistoryArr['val'] && $pathistoryArr['val'] != '' ) {
            $pathistoryObj = new EncounterPathistory($status,$provider,$system);
            $pathistoryObj->setField($pathistoryArr['val']);
            $pathistoryObj->setId($pathistoryArr['id']);
            $encounter->addPathistory($pathistoryObj);
        }

        ///////////////// Procedure /////////////////
        $procedure = new Procedure(false, $status, $provider, $system);

        //Procedure name
        $ptypeArr = $this->getValueByHeaderName('Procedure Type',$row,$columnData);
        if( $force || $ptypeArr['val'] && $ptypeArr['val'] != '' ) {
            $procedureTransform = new ProcedureTransformer($em,$provider);
            $procedurenameList = $procedureTransform->reverseTransform($ptypeArr['val']); //ProcedureList
            $procedureName = new ProcedureName($status,$provider,$system);
            $procedureName->setField($procedurenameList);
            $procedureName->setId($ptypeArr['id']);
            $procedure->addName($procedureName);
        }

        //Procedure Encounter Number
        $procedureenc = new ProcedureNumber($status,$provider,$system);
        $procedure->addNumber($procedureenc);

        $encounter->addProcedure($procedure);



        ///////////////// Accession /////////////////
        $accession = new Accession(false, $status, $provider, $system);

        //AccessionAccession
        $accArr = $this->getValueByHeaderName('Accession Number',$row,$columnData);
        $accacc = new AccessionAccession($status,$provider,$system);
        $accacc->setField($accArr['val']);
        $accacc->setOriginal($accArr['val']);
        $accacc->setId($accArr['id']);
        $accTransformer = new AccessionTypeTransformer($em,$provider);
        $acctype = $accTransformer->reverseTransform($this->getValueByHeaderName('Accession Type',$row,$columnData));
        $accacc->setKeytype($acctype);
        $accession->addAccession($accacc);

        //Accession Date
        $accessionDateArr = $this->getValueByHeaderName('Accession Date',$row,$columnData);
        if( $force || $accessionDateArr['val'] && $accessionDateArr['val'] != '' ) {
            if( $encounterDateArr['val'] == "" ) {
                $accessionDateFormat = NULL;
            } else {
                $accessionDateFormat = new \DateTime($accessionDateArr['val']);
            }
            $accessionDateObj = new AccessionAccessionDate($status,$provider,$system);
            $accessionDateObj->setField($accessionDateFormat);
            $accessionDateObj->setId($accessionDateArr['id']);
            $accession->addAccessionDate($accessionDateObj);
        }

        $procedure->addAccession($accession);

        ///////////////// Part /////////////////
        $part = new Part(false, $status, $provider, $system);

        //part ID
        $partname = new PartPartname($status,$provider,$system);
        $pnameArr = $this->getValueByHeaderName('Part ID',$row,$columnData);
        //echo "pname=".$pname."<br>";
        $partname->setField($pnameArr['val']);
        $partname->setId($pnameArr['id']);
        $part->addPartname($partname);

        //Source Organ
        $partsoArr = $this->getValueByHeaderName('Source Organ',$row,$columnData);
        if( $force || $partsoArr['val'] && $partsoArr['val'] != '' ) {
            $sourceOrganTransformer = new SourceOrganTransformer($em,$provider);
            $sourceOrganList = $sourceOrganTransformer->reverseTransform($partsoArr['val']); //OrganList
            $partSourceOrgan = new PartSourceOrgan($status,$provider,$system);
            $partSourceOrgan->setField($sourceOrganList);
            $partSourceOrgan->setId($partsoArr['id']);
            $part->addSourceOrgan($partSourceOrgan);
        }

        //Part Title
        $parttitleArr = $this->getValueByHeaderName('Part Title',$row,$columnData);
        if( $force || $parttitleArr['val'] && $parttitleArr['val'] != '' ) {
            $parttitleTransformer = new GenericTreeTransformer($em,$provider,'ParttitleList','OrderformBundle');
            $parttitleList = $parttitleTransformer->reverseTransform($parttitleArr['val']); //ParttitleList
            $parttitle = new PartParttitle($status,$provider,$system);
            $parttitle->setField($parttitleList);
            $parttitle->setId($partsoArr['id']);
            $part->addParttitle($parttitle);
        }

        //Gross Description
        $partgdArr = $this->getValueByHeaderName('Gross Description',$row,$columnData);
        if( $force || $partgdArr['val'] && $partgdArr['val'] != '' ) {
            $partDescription = new PartDescription($status,$provider,$system);
            $partDescription->setField($partgdArr['val']);
            $partDescription->setId($partgdArr['id']);
            $part->addDescription($partDescription);
        }

        //Diagnosis
        $partdiagArr = $this->getValueByHeaderName('Diagnosis',$row,$columnData);
        if( $force || $partdiagArr['val'] && $partdiagArr['val'] != '' ) {
            $partDisident = new PartDisident($status,$provider,$system);
            $partDisident->setField($partdiagArr['val']);
            $partDisident->setId($partdiagArr['id']);
            $part->addDisident($partDisident);
        }

        //Differential Diagnoses
        $partdiffdiagArr = $this->getValueByHeaderName('Differential Diagnoses',$row,$columnData);
        if( $force || $partdiffdiagArr['val'] && $partdiffdiagArr['val'] != '' ) {
            $partDiffDisident = new PartDiffDisident($status,$provider,$system);
            $partDiffDisident->setField($partdiffdiagArr['val']);
            $partDiffDisident->setId($partdiffdiagArr['id']);
            $part->addDiffDisident($partDiffDisident);
        }

        //Type of Disease
        $partdistypeArr = $this->getValueByHeaderName('Type of Disease',$row,$columnData);
        if( $force || $partdistypeArr['val'] && $partdistypeArr['val'] != '' ) {
            $partDiseaseType = new PartDiseaseType($status,$provider,$system);
            //$partDiseaseType->setField($partdistypeArr['val']);
            $partDiseaseType->setId($partdistypeArr['id']);

            //addDiseaseType
            //echo "<br>DiseaseType=".$partdistypeArr['val']."<br>";
            $diseaseType = $em->getRepository('AppOrderformBundle:DiseaseTypeList')->findOneByName($partdistypeArr['val']);
            $partDiseaseType->addDiseaseType($diseaseType);
            //exit();

            //Origin of Disease
            $diseaseOrigin = $em->getRepository('AppOrderformBundle:DiseaseOriginList')->findOneByName($this->getValueByHeaderName('Origin of Disease',$row,$columnData)['val']);
            $partDiseaseType->addDiseaseOrigin($diseaseOrigin);

            //Primary Site of Disease Origin
            $sourceOrganTransformer = new SourceOrganTransformer($em,$provider);
            $primaryOrganList = $sourceOrganTransformer->reverseTransform($this->getValueByHeaderName('Primary Site of Disease Origin',$row,$columnData)['val']); //OrganList
            $partDiseaseType->setPrimaryOrgan($primaryOrganList);
            $part->addDiseaseType($partDiseaseType);
        }

        //paper
        $partPaper = new PartPaper($status,$provider,$system);
        $part->addPaper( $partPaper );

        $accession->addPart($part);

        ///////////////// Block /////////////////
        $block = new Block(false, $status, $provider, $system);

        //block ID
        $blockname = new BlockBlockname($status,$provider,$system);
        $blocknameArr = $this->getValueByHeaderName('Block ID',$row,$columnData);
        $blockname->setId($blocknameArr['id']);
        $blockname->setField($blocknameArr['val']);
        $block->addBlockname($blockname);

        //Block: Section Source
        $sectionsArr = $this->getValueByHeaderName('Block Section Source',$row,$columnData);
        if( $force || $sectionsArr['val'] && $sectionsArr['val'] != '' ) {
            $blocksection = new BlockSectionsource($status,$provider,$system);
            $blocksection->setField($sectionsArr['val']);
            $blocksection->setId($sectionsArr['id']);
            $block->addSectionsource($blocksection);
        }

        //Block: Results of Special Stains: StainList + field
        $specialStainValueArr = $this->getValueByHeaderName('Associated Special Stain Result',$row,$columnData);
        if( $force || $specialStainValueArr['val'] && $specialStainValueArr['val'] != '' ) {
            $stainTransformer = new StainTransformer($em,$provider);

            //special stain type might be null in table, so get one from StainList with smallest 'orderinlist'
            $specialstainList = $stainTransformer->reverseTransform($this->getValueByHeaderName('Associated Special Stain Name',$row,$columnData)['val']); //list
            if( $specialstainList == null ) {
                $stainList = $em->getRepository('AppOrderformBundle:StainList')->findBy(array(), array('orderinlist'=>'ASC'));
                $specialstainList = $stainList[0];
            }

            $specialstain = new BlockSpecialStains($status,$provider,$system);
            $specialstain->setStaintype($specialstainList); //StainList
            $specialstain->setField($specialStainValueArr['val']);    //field
            $specialstain->setId($specialStainValueArr['id']);
            $block->addSpecialStain($specialstain);
        }

        $part->addBlock($block);

        ////////////////// Slide /////////////////
        $slide = new Slide(false, $status, $provider, $system);

        $slide->setId($this->getValueByHeaderName('Slide Title',$row,$columnData)['id']);

        //Slide set Sequence
        $slide->setSequence($count);

        //Slide Title
        $slide->setTitle($this->getValueByHeaderName('Slide Title',$row,$columnData)['val']);

        //Microscopic Description
        $slide->setMicroscopicdescr($this->getValueByHeaderName('Microscopic Description',$row,$columnData)['val']);

        //Slide Type
        $slidetype = $em->getRepository('AppOrderformBundle:SlideType')->findOneByName($this->getValueByHeaderName('Slide Type',$row,$columnData)['val']);
        $slide->setSlidetype($slidetype);

        //Stain
        $stainArr = $this->getValueByHeaderName('Stain',$row,$columnData);
        if( $force || $stainArr['val'] && $stainArr['val'] != '' ) {
            $stainTransformer = new StainTransformer($em,$provider);
            $stainList = $stainTransformer->reverseTransform($stainArr['val']);

            $stain = new Stain($status,$provider,$system);
            $stain->setField($stainList);
            $stain->setId($stainArr['id']);

            $slide->addStain($stain);
        }

        ///// Scan /////
        $scan = new Imaging($status,$provider,$system);

        //Scan: Scan Magnificaiton
        $magArr = $this->getValueByHeaderName('Scan Magnificaiton',$row,$columnData);
        //echo "<br>mag=".$magArr['id']."<br>";

        //setMagnification
        $mag = $em->getRepository('AppOrderformBundle:Magnification')->findOneByName($magArr['val']);
        $scan->setMagnification($mag);
        $scan->setId($magArr['id']);

        //Scan: Region to Scan
        $regTransformer = new StringTransformer($em,$provider);
        $scanregion = $regTransformer->reverseTransform($this->getValueByHeaderName('Region to Scan',$row,$columnData)['val']);
        //echo "scanregion=".$scanregion."<br>";
        $scan->setScanregion($scanregion);

        //Scan: Reason for Scan/Note
        $note = $this->getValueByHeaderName('Reason for Scan/Note',$row,$columnData)['val'];
        //echo "note=".$note."<br>";
        $scan->setNote($note);

        $slide->addScan($scan);
        ///// EOF Scan /////

        //Link(s) to related image(s)
        $relevantScansArr = $this->getValueByHeaderName('Link(s) to related image(s)',$row,$columnData);
        if( $force || $relevantScansArr['val'] && $relevantScansArr['val'] != '' ) {
            $relScan = new RelevantScans($status,$provider,$system);
            $relScan->setField($relevantScansArr['val']);
            $relScan->setId($relevantScansArr['id']);
            $slide->addRelevantScan($relScan);
        }


        $block->addSlide($slide);

        return $patient;
    }

    public function getValueByHeaderName($header, $row, $headers) {

        $res = array();

        $key = array_search($header, $headers);

        $res['val'] = $row[$key]['value'];

        $id = null;

        if( array_key_exists('id', $row[$key]) ) {
            $id = $row[$key]['id'];
            //echo "id=".$id.", val=".$res['val']."<br>";
        }

        $res['id'] = $id;

        return $res;

        //return $row[$key];
    }

//    public function getClassType($col, $columnData) {
//
//        $header = $columnData[$col];
//        switch($header) {
//            case 'Accession Type':
//                $className = "accType";
//                break;
//            case 'Accession Number':
//                $className = "acc";
//                break;
//        }
//
//        return $className;
//    }

}