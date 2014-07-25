<?php
/**
 * Created by JetBrains PhpStorm.
 * User: oli2002
 * Date: 4/28/14
 * Time: 1:34 PM
 * To change this template use File | Settings | File Templates.
 */

namespace Oleg\OrderformBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Form\Extension\Core\DataTransformer\DateTimeToStringTransformer;

use Oleg\OrderformBundle\Entity\OrderInfo;
use Oleg\OrderformBundle\Form\OrderInfoType;

use Oleg\OrderformBundle\Entity\Patient;
use Oleg\OrderformBundle\Entity\ClinicalHistory;
use Oleg\OrderformBundle\Entity\PatientMrn;
use Oleg\OrderformBundle\Entity\PatientDob;
use Oleg\OrderformBundle\Entity\PatientClinicalHistory;

use Oleg\OrderformBundle\Entity\Procedure;
use Oleg\OrderformBundle\Entity\ProcedureEncounter;
use Oleg\OrderformBundle\Entity\ProcedureName;

use Oleg\OrderformBundle\Entity\ProcedurePatlastname;
use Oleg\OrderformBundle\Entity\ProcedurePatfirstname;
use Oleg\OrderformBundle\Entity\ProcedurePatmiddlename;
use Oleg\OrderformBundle\Entity\ProcedurePatsex;
use Oleg\OrderformBundle\Entity\ProcedurePatage;
use Oleg\OrderformBundle\Entity\ProcedurePathistory;
use Oleg\OrderformBundle\Entity\ProcedureEncounterDate;

use Oleg\OrderformBundle\Entity\Accession;
use Oleg\OrderformBundle\Entity\AccessionAccession;
use Oleg\OrderformBundle\Entity\AccessionAccessionDate;

use Oleg\OrderformBundle\Entity\Part;
use Oleg\OrderformBundle\Entity\PartPartname;
use Oleg\OrderformBundle\Entity\PartSourceOrgan;
use Oleg\OrderformBundle\Entity\PartDescription;
use Oleg\OrderformBundle\Entity\PartDisident;
use Oleg\OrderformBundle\Entity\PartDiffDisident;
use Oleg\OrderformBundle\Entity\PartDiseaseType;
use Oleg\OrderformBundle\Entity\PartPaper;

use Oleg\OrderformBundle\Entity\Block;
use Oleg\OrderformBundle\Entity\BlockBlockname;
use Oleg\OrderformBundle\Entity\BlockSectionsource;

use Oleg\OrderformBundle\Entity\RelevantScans;
use Oleg\OrderformBundle\Entity\BlockSpecialStains;
use Oleg\OrderformBundle\Entity\Slide;
use Oleg\OrderformBundle\Entity\Scan;
use Oleg\OrderformBundle\Entity\Stain;

use Oleg\OrderformBundle\Entity\Educational;
use Oleg\OrderformBundle\Entity\Research;
use Oleg\OrderformBundle\Helper\OrderUtil;

use Oleg\OrderformBundle\Form\SlideMultiType;

use Oleg\OrderformBundle\Helper\ErrorHelper;
use Oleg\OrderformBundle\Helper\EmailUtil;
use Oleg\OrderformBundle\Security\Util\SecurityUtil;
use Oleg\OrderformBundle\Helper\UserUtil;

use Oleg\OrderformBundle\Form\DataTransformer\ProcedureTransformer;
use Oleg\OrderformBundle\Form\DataTransformer\MrnTypeTransformer;
use Oleg\OrderformBundle\Form\DataTransformer\AccessionTypeTransformer;
use Oleg\OrderformBundle\Form\DataTransformer\SourceOrganTransformer;
use Oleg\OrderformBundle\Form\DataTransformer\StainTransformer;
use Oleg\OrderformBundle\Form\DataTransformer\StringTransformer;


class TableController extends Controller {

    /**
     * @Route("/scan-order/multi-slide-table-view/{id}/amend", name="table_amend", requirements={"id" = "\d+"})
     * @Route("/scan-order/multi-slide-table-view/{id}/show", name="table_show", requirements={"id" = "\d+"})
     * @Template("OlegOrderformBundle:MultiScanOrder:newtable.html.twig")
     */
    public function multiTableShowAction( Request $request, $id ) {

        if( false === $this->get('security.context')->isGranted('ROLE_SCANORDER_SUBMITTER') &&
            false === $this->get('security.context')->isGranted('ROLE_SCANORDER_ORDERING_PROVIDER')
        ) {
            return $this->redirect( $this->generateUrl('scan-order-nopermission') );
        }

        $routeName = $request->get('_route');

        $em = $this->getDoctrine()->getManager();

        $secUtil = new SecurityUtil($em,$this->get('security.context'),$this->get('session') );
        if( !$secUtil->isCurrentUserAllow($id) ) {
            return $this->redirect( $this->generateUrl('scan-order-nopermission') );
        }

        $user = $this->get('security.context')->getToken()->getUser();

        $userUtil = new UserUtil();

        $orderinfo = $em->getRepository('OlegOrderformBundle:OrderInfo')->findOneByOid($id);

        if( $orderinfo && !$userUtil->hasPermission($orderinfo,$this->get('security.context')) ) {
            return $this->redirect( $this->generateUrl('scan-order-nopermission') );
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

        if( $orderinfo->getStatus() == "Superseded" ) {
            $fieldstatus = "deleted-by-amended-order";
        } else {
            $fieldstatus = "valid";
        }

        $params = array('type'=>$orderinfo->getType(), 'cicle'=>$type, 'service'=>null, 'user'=>$user);
        $form = $this->createForm( new OrderInfoType($params,$orderinfo), $orderinfo, array('disabled' => $disable) );

        //$slides = $orderinfo->getSlide();
        $query = $em->createQuery('
            SELECT slide
            FROM OlegOrderformBundle:Slide slide
            INNER JOIN slide.orderinfo orderinfo
            WHERE orderinfo.oid = :id
            ORDER BY slide.sequence ASC'
        )->setParameter('id', $id);

        $slides = $query->getResult();

        $jsonData = array();

        foreach( $slides as $slide ) {

            $block = $slide->getBlock();
            $part = $block->getPart();
            $accession = $part->getAccession();
            $procedure = $accession->getProcedure();
            $patient = $procedure->getPatient();

            //accession: 2
            $acckey = $accession->obtainValidKeyField();
            $rowArr['Accession Type']['id'] = $acckey->getId();
            $rowArr['Accession Type']['value'] = $acckey->getKeytype()->getName();
            $rowArr['Accession Number']['id'] = $acckey->getId();
            $rowArr['Accession Number']['value'] = $acckey->getField();

            //part: 1
            $partname = $part->obtainValidKeyField();
            $rowArr['Part Name']['id'] = $partname->getId();
            $rowArr['Part Name']['value'] = $partname->getField();

            //block: 1
            $blockname = $block->obtainValidKeyField();
            $rowArr['Block Name']['id'] = $blockname->getId();
            $rowArr['Block Name']['value'] = $blockname->getField();

            //slide: 4
            $stain = $slide->getStain()->first();
            $rowArr['Stain']['id'] = $stain->getId();
            $rowArr['Stain']['value'] = $stain->getField()->getName();

            $scan = $slide->getScan()->first();
            $rowArr['Scan Magnificaiton']['id'] = $scan->getId();
            $rowArr['Scan Magnificaiton']['value'] = $scan->getField();

            $partdiadnosis = $part->obtainStatusField('disident',$fieldstatus,$id);
            $rowArr['Diagnosis']['id'] = $partdiadnosis->getId();
            $rowArr['Diagnosis']['value'] = $partdiadnosis->getField();

            $rowArr['Reason for Scan/Note']['id'] = $scan->getId();
            $rowArr['Reason for Scan/Note']['value'] = $scan->getNote();

            //part 1
            $sourceorgan = $part->obtainStatusField('sourceOrgan',$fieldstatus,$id);
            $rowArr['Source Organ']['id'] = $sourceorgan->getId();
            $rowArr['Source Organ']['value'] = ( $sourceorgan->getField() ? $sourceorgan->getField()->getName() : null );

            //patient: 4
            $patientkey = $patient->obtainValidKeyField();
            $rowArr['MRN Type']['id'] = $patientkey->getId();
            $rowArr['MRN Type']['value'] = $patientkey->getKeytype()->getName();
            $rowArr['MRN']['id'] = $patientkey->getId();
            $rowArr['MRN']['value'] = $patientkey->getField();

            $dob = $patient->obtainStatusField('dob',$fieldstatus,$id);
            $rowArr['Patient DOB']['id'] = $dob->getId();
            $rowArr['Patient DOB']['value'] = $transformer->transform($dob->getField());

            $clinicalHistory = $patient->obtainStatusField('clinicalHistory',$fieldstatus,$id);
            $rowArr['Clinical Summary']['id'] = $clinicalHistory->getId();
            $rowArr['Clinical Summary']['value'] = $clinicalHistory->getField();

            //accession: 1
            $accessionDate = $accession->obtainStatusField('accessionDate',$fieldstatus,$id);
            $rowArr['Accession Date']['id'] = $accessionDate->getId();
            $rowArr['Accession Date']['value'] = $transformer->transform($accessionDate->getField());

            //procedure: 6
            $proceduretype = $procedure->getName()->first();
            $rowArr['Procedure Type']['id'] = $proceduretype->getId();
            $rowArr['Procedure Type']['value'] = ( $proceduretype->getField() ? $proceduretype->getField()->getId() : null );

            $encounterdate = $procedure->obtainStatusField('encounterDate',$fieldstatus,$id);
            $rowArr['Encounter Date']['id'] = $encounterdate->getId();
            $rowArr['Encounter Date']['value'] = $transformer->transform($encounterdate->getField());

            $patlastname = $procedure->obtainStatusField('patlastname',$fieldstatus,$id);
            $rowArr["Patient's Last Name"]['id'] = $patlastname->getId();
            $rowArr["Patient's Last Name"]['value'] = $patlastname->getField();

            $patfirstname = $procedure->obtainStatusField('patfirstname',$fieldstatus,$id);
            $rowArr["Patient's First Name"]['id'] = $patfirstname->getId();
            $rowArr["Patient's First Name"]['value'] = $patfirstname->getField();

            $patmiddlename = $procedure->obtainStatusField('patmiddlename',$fieldstatus,$id);
            $rowArr["Patient's Middle Name"] = $patmiddlename->getId();
            $rowArr["Patient's Middle Name"] = $patmiddlename->getField();

            $patsex = $procedure->obtainStatusField('patsex',$fieldstatus,$id);
            $rowArr['Patient Sex']['id'] = $patsex->getId();
            $rowArr['Patient Sex']['value'] = $patsex->getField();

            $patage = $procedure->obtainStatusField('patage',$fieldstatus,$id);
            $rowArr['Patient Age']['id'] = $patage->getId();
            $rowArr['Patient Age']['value'] = $patage->getField();

            $pathistory = $procedure->obtainStatusField('pathistory',$fieldstatus,$id);
            $rowArr['Clinical History']['id'] = $pathistory->getId();
            $rowArr['Clinical History']['value'] = $pathistory->getField();

            //part: 5
            $description = $part->obtainStatusField('description',$fieldstatus,$id);
            $rowArr['Gross Description']['id'] = $description->getId();
            $rowArr['Gross Description']['value'] = $description->getField();

            $diffDisident = $part->obtainStatusField('diffDisident',$fieldstatus,$id);
            $rowArr['Differential Diagnoses']['id'] = $diffDisident->getId();
            $rowArr['Differential Diagnoses']['value'] = $diffDisident->getField();

            $diseaseType = $part->obtainStatusField('diseaseType',$fieldstatus,$id);
            $rowArr['Type of Disease']['id'] = $diseaseType->getId();
            $rowArr['Type of Disease']['value'] = $diseaseType->getField();

            $rowArr['Origin of Disease']['id'] = $diseaseType->getId();
            $rowArr['Origin of Disease']['value'] = $diseaseType->getOrigin();

            $rowArr['Primary Site of Disease Origin']['id'] = $diseaseType->getId();
            $rowArr['Primary Site of Disease Origin']['value'] = ( $diseaseType->getPrimaryOrgan() ? $diseaseType->getPrimaryOrgan()->getName() : null );

            //block: 3
            $sectionsource = $block->obtainStatusField('sectionsource',$fieldstatus,$id);
            $rowArr['Block Section Source']['id'] = $sectionsource->getId();
            $rowArr['Block Section Source']['value'] = $sectionsource->getField();

            $specialStains = $block->obtainStatusField('specialStains',$fieldstatus,$id);
            $rowArr['Associated Special Stain Name']['id'] = $specialStains->getId();
            $rowArr['Associated Special Stain Name']['value'] = $specialStains->getStaintype()->getName();
            $rowArr['Associated Special Stain Result']['id'] = $specialStains->getId();
            $rowArr['Associated Special Stain Result']['value'] = $specialStains->getField();

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

            //$history = $em->getRepository('OlegOrderformBundle:History')->findByCurrentid( $entity->getOid(), array('changedate' => 'DESC') );
            $repository = $this->getDoctrine()->getRepository('OlegOrderformBundle:History');
            $dql = $repository->createQueryBuilder("h");
            $dql->innerJoin("h.orderinfo", "orderinfo");
            $dql->where("h.currentid = :oid AND (h.eventtype = 'Initial Order Submission' OR h.eventtype = 'Status Changed' OR h.eventtype = 'Amended Order Submission')");
            $dql->orderBy('h.changedate','DESC');
            $dql->setParameter('oid',$orderinfo->getOid());
            $history = $dql->getQuery()->getResult();

        }

        return $this->render('OlegOrderformBundle:MultiScanOrder:newtable.html.twig', array(
            'orderdata' => json_encode($jsonData),
            'entity' => $orderinfo,
            'form' => $form->createView(),
            'type' => $type,
            'formtype' => $orderinfo->getType(),
            'history' => $history
        ));

    }


    /**
     * @Route("/scan-order/multi-slide-table-view/new", name="table_create")
     * @Template("OlegOrderformBundle:MultiScanOrder:newtable.html.twig")
     */
    public function multiTableCreationAction()
    {

        if( false === $this->get('security.context')->isGranted('ROLE_SCANORDER_SUBMITTER') ) {
            return $this->redirect( $this->generateUrl('scan-order-home') );
        }

        $user = $this->get('security.context')->getToken()->getUser();

        //check if user has at least one institution
        if( count($user->getInstitution()) == 0 ) {
            $em = $this->getDoctrine()->getManager();
            $orderUtil = new OrderUtil($em);
            $userUrl = $this->generateUrl('showuser', array('id' => $user->getId()),true);
            $homeUrl = $this->generateUrl('main_common_home',array(),true);
            $sysEmail = $this->container->getParameter('default_system_email');
            $orderUtil->setWarningMessageNoInstitution($user,$userUrl,$this->get('session')->getFlashBag(),$sysEmail,$homeUrl);
            return $this->redirect( $this->generateUrl('scan-order-home') );
        }

        $em = $this->getDoctrine()->getManager();

        $entity = new OrderInfo();

        //***************** get ordering provider from most recent order ***************************//
        $lastProxy = null;
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
        if( $lastProxy ) {
            $entity->setProxyuser($lastProxy);
        } else {
            $entity->setProxyuser($user);
        }
        //***************** end of get ordering provider from most recent order ***************************//

        $source = 'scanorder';

        $entity->setPurpose("For Internal Use by WCMC Department of Pathology");

        $entity->setProvider($user);
        $entity->setProxyuser($user);

        $patient = new Patient(true,'invalid',$user,$source);
        $entity->addPatient($patient);

        $edu = new Educational();
        $entity->setEducational($edu);

        $res = new Research();
        $entity->setResearch($res);

        $service = $user->getPathologyServices();

        //set the first service
        if( count($service) > 0 ) {
            $entity->setPathologyService($service->first());
        }

        $type = "Table-View Scan Order";

        $params = array('type'=>$type, 'cicle'=>'new', 'service'=>$service, 'user'=>$user);
        $form = $this->createForm( new OrderInfoType($params, $entity), $entity );

        return $this->render('OlegOrderformBundle:MultiScanOrder:newtable.html.twig', array(
            'form' => $form->createView(),
            'cycle' => 'new',
            'formtype' => $type,
            'type' => 'new',
            'orderdata' => null,
        ));
    }

    /**
     * Creates a new Table OrderInfo.

     * @Route("/scan-order/multi-slide-table-view/submit", name="table_create_submit")
     * @Method("POST")
     * @Template("OlegOrderformBundle:MultiScanOrder:newtable.html.twig")
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

        $user = $this->get('security.context')->getToken()->getUser();

        $entity = new OrderInfo();

        $type = "Table-View Scan Order";
        $params = array('type'=>$type, 'cicle'=>'new', 'service'=>null, 'user'=>$user);

        $form = $this->createForm(new OrderInfoType($params,$entity), $entity);

        //$form->bind($request);
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
            $cicle = 'new';
            $status = $em->getRepository('OlegOrderformBundle:Status')->findOneByName('Submitted');
            $entity->setStatus($status);
        }

        if( $clickedbtn == 'btnAmend' ) {
            $cicle = 'amend';
            $status = $em->getRepository('OlegOrderformBundle:Status')->findOneByName('Amended');
            $entity->setStatus($status);
        }

        if( $clickedbtn == 'btnSaveOnIdleTimeout' ) {
            $cicle = 'edit';
            $status = $em->getRepository('OlegOrderformBundle:Status')->findOneByName('Not Submitted');
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

        $user = $this->get('security.context')->getToken()->getUser();
        $entity->setProvider($user);

        //add dataqualities to entity
        $dataqualities = $form->get('conflicts')->getData();
        $orderUtil = new OrderUtil($em);
        $orderUtil->setDataQuality($entity,$dataqualities);

        $entity = $em->getRepository('OlegOrderformBundle:OrderInfo')->processOrderInfoEntity( $entity, $user, $type, $this->get('router') );

//        $response = new Response();
//        $response->headers->set('Content-Type', 'application/json');
//        $response->setContent(json_encode('ok'));
//        return $response;

        $conflictStr = "";
        foreach( $entity->getDataquality() as $dq ) {
            $conflictStr = $conflictStr . "\r\n".$dq->getDescription()."\r\n"."Resolved by replacing: ".$dq->getAccession()." => ".$dq->getNewaccession()."\r\n";
        }

        $submitStatusStr = null;
        if( isset($_POST['btnAmend']) ) {
            $submitStatusStr = "has been successfully amended";
        } else
            if( isset($_POST['btnSave']) || isset($_POST['btnSaveOnIdleTimeout']) ) {
                $submitStatusStr = "is saved but not submitted";
            }

        $orderurl = $this->generateUrl( 'multy_show',array('id'=>$entity->getOid()), true );    //was $entity->getId()

        //email
        $emailUtil = new EmailUtil();
        $emailUtil->sendEmail( $user->getEmail(), $em, $entity, $orderurl, null, $conflictStr, $submitStatusStr );

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
                'conflicts' => $conflictsStr,
                'cicle' => $cicle,
                'neworder' => "table_create"
            )
        ));

    }

    public function constractPatientByTableData( $row, $columnData, $count ) {

        $force = true; //true - create fields even if the value is empty
        $status = "valid";
        $provider = $this->get('security.context')->getToken()->getUser();
        $source = "scanorder";
        $em = $this->getDoctrine()->getManager();

        /////////////// Patient ///////////////////
        $patient = new Patient(false, $status, $provider, $source);

        //mrn
        $patientmrn = new PatientMrn($status,$provider,$source);
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
            $patientdob = new PatientDob($status,$provider,$source);
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
            $patientch = new PatientClinicalHistory($status,$provider,$source);
            $patientch->setField($clsumArr['val']);
            $patientch->setId($clsumArr['id']);
            $patient->addClinicalHistory($patientch);
        }

        ///////////////// Procedure /////////////////
        $procedure = new Procedure(false, $status, $provider, $source);

        //Procedure name
        $ptypeArr = $this->getValueByHeaderName('Procedure Type',$row,$columnData);
        if( $force || $ptypeArr['val'] && $ptypeArr['val'] != '' ) {
            $procedureTransform = new ProcedureTransformer($em,$provider);
            $procedurenameList = $procedureTransform->reverseTransform($ptypeArr['val']); //ProcedureList
            $procedureName = new ProcedureName($status,$provider,$source);
            $procedureName->setField($procedurenameList);
            $procedureName->setId($ptypeArr['id']);
            $procedure->addName($procedureName);
        }

        //Procedure Encounter
        $procedureenc = new ProcedureEncounter($status,$provider,$source);
        $procedure->addEncounter($procedureenc);

        $patient->addProcedure($procedure);

        //add procedure simple fields
        //Encounter Date
        $encounterDateArr = $this->getValueByHeaderName('Encounter Date',$row,$columnData);
        if( $force || $encounterDateArr['val'] && $encounterDateArr['val'] != '' ) {
            if( $encounterDateArr['val'] == "" ) {
                $encounterDateFormat = NULL;
            } else {
                $encounterDateFormat = new \DateTime($encounterDateArr['val']);
            }
            $encounterDateObj = new ProcedureEncounterDate($status,$provider,$source);
            $encounterDateObj->setField($encounterDateFormat);
            $encounterDateObj->setId($encounterDateArr['id']);
            $procedure->addEncounterDate($encounterDateObj);
        }

        //Procedure Last Name
        $patlastnameArr = $this->getValueByHeaderName("Patient's Last Name",$row,$columnData);
        if( $force || $patlastnameArr['val'] && $patlastnameArr['val'] != '' ) {
            $patlastnameObj = new ProcedurePatlastname($status,$provider,$source);
            $patlastnameObj->setField($patlastnameArr['val']);
            $patlastnameObj->setId($patlastnameArr['id']);
            $procedure->addPatlastname($patlastnameObj);
        }

        //Procedure First Name
        $patfirstnameArr = $this->getValueByHeaderName("Patient's First Name",$row,$columnData);
        if( $force || $patfirstnameArr['val'] && $patfirstnameArr['val'] != '' ) {
            $patfirstnameObj = new ProcedurePatfirstname($status,$provider,$source);
            $patfirstnameObj->setField($patfirstnameArr['val']);
            $patfirstnameObj->setId($patfirstnameArr['id']);
            $procedure->addPatfirstname($patfirstnameObj);
        }

        //Procedure Middle Name
        $patmiddlenameArr = $this->getValueByHeaderName("Patient's Middle Name",$row,$columnData);
        if( $force || $patmiddlenameArr['val'] && $patmiddlenameArr['val'] != '' ) {
            $patmiddlenameObj = new ProcedurePatmiddlename($status,$provider,$source);
            $patmiddlenameObj->setField($patmiddlenameArr['val']);
            $patmiddlenameObj->setId($patmiddlenameArr['id']);
            $procedure->addPatmiddlename($patmiddlenameObj);
        }

        //Procedure Sex
        $patsexArr = $this->getValueByHeaderName('Patient Sex',$row,$columnData);
        if( $force || $patsexArr['val'] && $patsexArr['val'] != '' ) {
            $patsexObj = new ProcedurePatsex($status,$provider,$source);
            $patsexObj->setField($patsexArr['val']);
            $patsexObj->setId($patsexArr['id']);
            $procedure->addPatsex($patsexObj);
        }

        //Procedure Age
        $patageArr = $this->getValueByHeaderName('Patient Age',$row,$columnData);
        if( $force || $patageArr['val'] && $patageArr['id'] != '' ) {
            $patageObj = new ProcedurePatage($status,$provider,$source);
            $patageObj->setField($patageArr['val']);
            $patageObj->setId($patageArr['id']);
            $procedure->addPatage($patageObj);
        }

        //Clinical History
        $pathistoryArr = $this->getValueByHeaderName('Clinical History',$row,$columnData);
        if( $force || $pathistoryArr['val'] && $pathistoryArr['val'] != '' ) {
            $pathistoryObj = new ProcedurePathistory($status,$provider,$source);
            $pathistoryObj->setField($pathistoryArr['val']);
            $pathistoryObj->setId($pathistoryArr['id']);
            $procedure->addPathistory($pathistoryObj);
        }


        ///////////////// Accession /////////////////
        $accession = new Accession(false, $status, $provider, $source);

        //AccessionAccession
        $accArr = $this->getValueByHeaderName('Accession Number',$row,$columnData);
        $accacc = new AccessionAccession($status,$provider,$source);
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
            $accessionDateObj = new AccessionAccessionDate($status,$provider,$source);
            $accessionDateObj->setField($accessionDateFormat);
            $accessionDateObj->setId($accessionDateArr['id']);
            $accession->addAccessionDate($accessionDateObj);
        }

        $procedure->addAccession($accession);

        ///////////////// Part /////////////////
        $part = new Part(false, $status, $provider, $source);

        //part name
        $partname = new PartPartname($status,$provider,$source);
        $pnameArr = $this->getValueByHeaderName('Part Name',$row,$columnData);
        //echo "pname=".$pname."<br>";
        $partname->setField($pnameArr['val']);
        $partname->setId($pnameArr['id']);
        $part->addPartname($partname);

        //Source Organ
        $partsoArr = $this->getValueByHeaderName('Source Organ',$row,$columnData);
        if( $force || $partsoArr['val'] && $partsoArr['val'] != '' ) {
            $sourceOrganTransformer = new SourceOrganTransformer($em,$provider);
            $sourceOrganList = $sourceOrganTransformer->reverseTransform($partsoArr['val']); //OrganList
            $partSourceOrgan = new PartSourceOrgan($status,$provider,$source);
            $partSourceOrgan->setField($sourceOrganList);
            $partSourceOrgan->setId($partsoArr['id']);
            $part->addSourceOrgan($partSourceOrgan);
        }

        //Gross Description
        $partgdArr = $this->getValueByHeaderName('Gross Description',$row,$columnData);
        if( $force || $partgdArr['val'] && $partgdArr['val'] != '' ) {
            $partDescription = new PartDescription($status,$provider,$source);
            $partDescription->setField($partgdArr['val']);
            $partDescription->setId($partgdArr['id']);
            $part->addDescription($partDescription);
        }

        //Diagnosis
        $partdiagArr = $this->getValueByHeaderName('Diagnosis',$row,$columnData);
        if( $force || $partdiagArr['val'] && $partdiagArr['val'] != '' ) {
            $partDisident = new PartDisident($status,$provider,$source);
            $partDisident->setField($partdiagArr['val']);
            $partDisident->setId($partdiagArr['id']);
            $part->addDisident($partDisident);
        }

        //Differential Diagnoses
        $partdiffdiagArr = $this->getValueByHeaderName('Differential Diagnoses',$row,$columnData);
        if( $force || $partdiffdiagArr['val'] && $partdiffdiagArr['val'] != '' ) {
            $partDiffDisident = new PartDiffDisident($status,$provider,$source);
            $partDiffDisident->setField($partdiffdiagArr['val']);
            $partDiffDisident->setId($partdiffdiagArr['id']);
            $part->addDiffDisident($partDiffDisident);
        }

        //Type of Disease
        $partdistypeArr = $this->getValueByHeaderName('Type of Disease',$row,$columnData);
        if( $force || $partdistypeArr['val'] && $partdistypeArr['val'] != '' ) {
            $partDiseaseType = new PartDiseaseType($status,$provider,$source);
            $partDiseaseType->setField($partdistypeArr['val']);
            $partDiseaseType->setId($partdistypeArr['id']);
            //Origin of Disease
            $partDiseaseType->setOrigin($this->getValueByHeaderName('Origin of Disease',$row,$columnData)['val']);
            //Primary Site of Disease Origin
            $sourceOrganTransformer = new SourceOrganTransformer($em,$provider);
            $primaryOrganList = $sourceOrganTransformer->reverseTransform($this->getValueByHeaderName('Primary Site of Disease Origin',$row,$columnData)['val']); //OrganList
            $partDiseaseType->setPrimaryOrgan($primaryOrganList);
            $part->addDiseaseType($partDiseaseType);
        }

        //paper
        $partPaper = new PartPaper($status,$provider,$source);
        $part->addPaper( $partPaper );

        $accession->addPart($part);

        ///////////////// Block /////////////////
        $block = new Block(false, $status, $provider, $source);

        //block name
        $blockname = new BlockBlockname($status,$provider,$source);
        $blocknameArr = $this->getValueByHeaderName('Block Name',$row,$columnData);
        $blockname->setId($blocknameArr['id']);
        $blockname->setField($blocknameArr['val']);
        $block->addBlockname($blockname);

        //Block: Section Source
        $sectionsArr = $this->getValueByHeaderName('Block Section Source',$row,$columnData);
        if( $force || $sectionsArr['val'] && $sectionsArr['val'] != '' ) {
            $blocksection = new BlockSectionsource($status,$provider,$source);
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
                $stainList = $em->getRepository('OlegOrderformBundle:StainList')->findBy(array(), array('orderinlist'=>'ASC'));
                $specialstainList = $stainList[0];
            }

            $specialstain = new BlockSpecialStains($status,$provider,$source);
            $specialstain->setStaintype($specialstainList); //StainList
            $specialstain->setField($specialStainValueArr['val']);    //field
            $specialstain->setId($specialStainValueArr['id']);
            $block->addSpecialStain($specialstain);
        }

        $part->addBlock($block);

        ////////////////// Slide /////////////////
        $slide = new Slide(false, $status, $provider, $source);

        $slide->setId($this->getValueByHeaderName('Slide Title',$row,$columnData)['id']);

        //Slide set Sequence
        $slide->setSequence($count);

        //Slide Title
        $slide->setTitle($this->getValueByHeaderName('Slide Title',$row,$columnData)['val']);

        //Microscopic Description
        $slide->setMicroscopicdescr($this->getValueByHeaderName('Microscopic Description',$row,$columnData)['val']);

        //Slide Type
        $slidetype = $em->getRepository('OlegOrderformBundle:SlideType')->findOneByName($this->getValueByHeaderName('Slide Type',$row,$columnData)['val']);
        $slide->setSlidetype($slidetype);

        //Stain
        $stainArr = $this->getValueByHeaderName('Stain',$row,$columnData);
        if( $force || $stainArr['val'] && $stainArr['val'] != '' ) {
            $stainTransformer = new StainTransformer($em,$provider);
            $stainList = $stainTransformer->reverseTransform($stainArr['val']);

            $stain = new Stain($status,$provider,$source);
            $stain->setField($stainList);
            $stain->setId($stainArr['id']);

            $slide->addStain($stain);
        }

        ///// Scan /////
        $scan = new Scan($status,$provider,$source);

        //Scan: Scan Magnificaiton
        $magArr = $this->getValueByHeaderName('Scan Magnificaiton',$row,$columnData);
        //echo "<br>mag=".$mag."<br>";
        $scan->setField($magArr['val']);
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
            $relScan = new RelevantScans($status,$provider,$source);
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