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
//use Oleg\OrderformBundle\Entity\PatientName;
//use Oleg\OrderformBundle\Entity\PatientSex;
use Oleg\OrderformBundle\Entity\PatientDob;
//use Oleg\OrderformBundle\Entity\PatientAge;
use Oleg\OrderformBundle\Entity\PatientClinicalHistory;

use Oleg\OrderformBundle\Entity\Procedure;
use Oleg\OrderformBundle\Entity\ProcedureEncounter;
use Oleg\OrderformBundle\Entity\ProcedureName;

use Oleg\OrderformBundle\Entity\Accession;
use Oleg\OrderformBundle\Entity\AccessionAccession;

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

use Oleg\OrderformBundle\Form\SlideMultiType;

use Oleg\OrderformBundle\Helper\ErrorHelper;
//use Oleg\OrderformBundle\Helper\FormHelper;
use Oleg\OrderformBundle\Helper\EmailUtil;

use Oleg\OrderformBundle\Form\DataTransformer\ProcedureTransformer;
use Oleg\OrderformBundle\Form\DataTransformer\MrnTypeTransformer;
use Oleg\OrderformBundle\Form\DataTransformer\AccessionTypeTransformer;
use Oleg\OrderformBundle\Form\DataTransformer\SourceOrganTransformer;
use Oleg\OrderformBundle\Form\DataTransformer\StainTransformer;
use Oleg\OrderformBundle\Form\DataTransformer\StringTransformer;


class TableController extends Controller {


    /**
     * @Route("/scan-order/multi-slide-table-view/new", name="table_create")
     * @Template("OlegOrderformBundle:MultyScanOrder:multitable.html.twig")
     */
    public function multiTableCreationAction()
    {

        $em = $this->getDoctrine()->getManager();

        $entity = new OrderInfo();
        $user = $this->get('security.context')->getToken()->getUser();

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

        $params = array('type'=>$type, 'cicle'=>'new', 'service'=>$service);
        $form = $this->createForm( new OrderInfoType($params, $entity), $entity );

        return $this->render('OlegOrderformBundle:MultyScanOrder:newtable.html.twig', array(
            'form' => $form->createView(),
            'cycle' => 'new',
            'formtype' => $type
        ));
    }

    /**
     * Creates a new Table OrderInfo.

     * @Route("/scan-order/multi-slide-table-view/submit", name="table_create_submit")
     * @Method("POST")
     * @Template("OlegOrderformBundle:MultyScanOrder:multitable.html.twig")
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

        $entity = new OrderInfo();

        $type = "Table-View Scan Order";
        $params = array('type'=>$type, 'cicle'=>'new', 'service'=>null);

        $form = $this->createForm(new OrderInfoType($params,$entity), $entity);

        //$form->bind($request);
        $form->handleRequest($request);

//        if( $form->isValid() ) {
//            echo "form is valid <br>";
//        } else {
//            echo "form is not valid! <br>";
//        }

        //////////////// process handsontable rows ////////////////
        $datajson = $form->get('datalocker')->getData();

        $data = json_decode($datajson, true);
        //var_dump($data);

        if( $data == null ) {
            throw new \Exception( 'Table order data is null.' );
        }

        $rowCount = 0;

        $headers = array_shift($data);
        //var_dump($columnData);

        foreach( $data as $row ) {
            //var_dump($row);
            //echo "<br>";

            $accValue = $this->getValueByHeaderName('Accession Number',$row,$headers);

            if( !$accValue || $accValue == '' ) {
                continue;   //skip row if accession number is empty
            }

            //echo $rowCount.": accType=".$row[0].", acc=".$row[1]." \n ";
            $rowCount++;

            $patient = $this->constractPatientByTableData($row,$headers);

            $entity->addPatient($patient);

            //echo $patient->getProcedure()->first()->getAccession()->first();

        }//foreach row
        //////////////// process handsontable rows ////////////////

        //exit('table order testing');

        $user = $this->get('security.context')->getToken()->getUser();
        $entity->setProvider($user);

        $status = $em->getRepository('OlegOrderformBundle:Status')->findOneByName('Submitted');
        $entity->setStatus($status);

        $entity = $em->getRepository('OlegOrderformBundle:OrderInfo')->processOrderInfoEntity( $entity, $user, $type, $this->get('router') );

//        $response = new Response();
//        $response->headers->set('Content-Type', 'application/json');
//        $response->setContent(json_encode('ok'));
//        return $response;

        $conflictStr = "";
        foreach( $entity->getDataquality() as $dq ) {
            $conflictStr = $conflictStr . "\r\n".$dq->getDescription()."\r\n"."Resolved by replacing: ".$dq->getAccession()." => ".$dq->getNewaccession()."\r\n";
        }

        $conflicts = array();
        foreach( $entity->getDataquality() as $dq ) {
            $conflicts[] = $dq->getDescription()."\nResolved by replacing:\n".$dq->getAccession()." => ".$dq->getNewaccession();
        }

        $orderurl = $this->generateUrl( 'multy_show',array('id'=>$entity->getId()), true );

        //email
        $emailUtil = new EmailUtil();
        $emailUtil->sendEmail( $user->getEmail(), $em, $entity, $orderurl, null, $conflictStr, null );

        return $this->render('OlegOrderformBundle:ScanOrder:thanks.html.twig', array(
            'oid' => $entity->getOid(),
            'conflicts' => $conflicts,
            'cicle' => 'new',
            'neworder' => "table_create"
        ));

    }

    public function constractPatientByTableData($row, $columnData) {

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
        $mrntype = $mrnTransformer->reverseTransform($this->getValueByHeaderName('MRN Type',$row,$columnData));
        $patientmrn->setKeytype($mrntype);
        $mrnValue = $this->getValueByHeaderName('MRN',$row,$columnData);
        $patientmrn->setField($mrnValue);
        $patientmrn->setOriginal($mrnValue);
        $patient->addMrn($patientmrn);

        //dob
        $dob = $this->getValueByHeaderName('Patient DOB',$row,$columnData);
        if( !$force || $dob && $dob != '' ) {
            $patientdob = new PatientDob($status,$provider,$source);
            $dobFormat = new \DateTime($dob);
            $patientdob->setField($dobFormat);
            $patient->addDob($patientdob);
        }

        //Clinical History
        $clsum = $this->getValueByHeaderName('Clinical Summary',$row,$columnData);
        if( !$force || $clsum && $clsum != '' ) {
            $patientch = new PatientClinicalHistory($status,$provider,$source);
            $patientch->setField($clsum);
            $patient->addClinicalHistory($patientch);
        }

//        //name
//        $name = $this->getValueByHeaderName('Patient Name',$row,$columnData);
//        if( !$force || $name && $name != '' ) {
//            $patientname = new PatientName($status,$provider,$source);
//            $patientname->setField($name);
//            $patient->addName($patientname);
//        }
//
//        //sex
//        $sex = $this->getValueByHeaderName('Patient Sex',$row,$columnData);
//        if( !$force || $sex && $sex != '' ) {
//            $patientsex = new PatientSex($status,$provider,$source);
//            $patientsex->setField($sex);
//            $patient->addSex($patientsex);
//        }
//
//        //age
//        $age = $this->getValueByHeaderName('Patient Age',$row,$columnData);
//        if( !$force || $age && $age != '' ) {
//            $patientage = new PatientAge($status,$provider,$source);
//            $patientage->setField($age);
//            $patient->addAge($patientage);
//        }

        //echo "name=".$patient->getName()->first()."<br>";
        //exit();
        //return $patient;

        ///////////////// Procedure /////////////////
        $procedure = new Procedure(false, $status, $provider, $source);

        //Procedure name
        $ptype = $this->getValueByHeaderName('Procedure Type',$row,$columnData);
        if( !$force || $ptype && $ptype != '' ) {
            $procedureTransform = new ProcedureTransformer($em,$provider);
            $procedurenameList = $procedureTransform->reverseTransform($ptype); //ProcedureList
            $procedureName = new ProcedureName($status, $provider, $source);
            $procedureName->setField($procedurenameList);
            $procedure->addName($procedureName);
        }

        //Procedure Encounter
        $procedureenc = new ProcedureEncounter($status,$provider,$source);
        $procedure->addEncounter($procedureenc);

        $patient->addProcedure($procedure);

        //add procedure simple fields
        //Encounter Date
        $encounterDate = $this->getValueByHeaderName('Encounter Date',$row,$columnData);
        if( !$force || $encounterDate && $encounterDate != '' ) {
            $encounterDateFormat = new \DateTime($encounterDate);
            $procedure->setEncounterDate($encounterDateFormat);
        }

        //Patient Name
        $patname = $this->getValueByHeaderName('Patient Name',$row,$columnData);
        if( !$force || $patname && $patname != '' ) {
            $procedure->setPatname($patname);
        }

        //Patient Sex
        $patsex = $this->getValueByHeaderName('Patient Sex',$row,$columnData);
        if( !$force || $patsex && $patsex != '' ) {
            $procedure->setPatsex($patsex);
        }

        //Patient Age
        $patage = $this->getValueByHeaderName('Patient Age',$row,$columnData);
        if( !$force || $patage && $patage != '' ) {
            $procedure->setPatage($patage);
        }

        //Clinical History
        $pathistory = $this->getValueByHeaderName('Clinical History',$row,$columnData);
        if( !$force || $pathistory && $pathistory != '' ) {
            $procedure->setPathistory($pathistory);
        }


        ///////////////// Accession /////////////////
        $accession = new Accession(false, $status, $provider, $source);

        //AccessionAccession
        $accValue = $this->getValueByHeaderName('Accession Number',$row,$columnData);
        $accacc = new AccessionAccession($status,$provider,$source);
        $accacc->setField($accValue);
        $accacc->setOriginal($accValue);
        $accTransformer = new AccessionTypeTransformer($em,$provider);
        $acctype = $accTransformer->reverseTransform($this->getValueByHeaderName('Accession Type',$row,$columnData));
        $accacc->setKeytype($acctype);
        $accession->addAccession($accacc);

        //Accession Date
        $accessionDate = $this->getValueByHeaderName('Accession Date',$row,$columnData);
        if( !$force || $accessionDate && $accessionDate != '' ) {
            $accessionDateFormat = new \DateTime($accessionDate);
            $accession->setAccessionDate($accessionDateFormat);
        }

        $procedure->addAccession($accession);

        ///////////////// Part /////////////////
        $part = new Part(false, $status, $provider, $source);

        //part name
        $partname = new PartPartname($status,$provider,$source);
        $pname = $this->getValueByHeaderName('Part Name',$row,$columnData);
        //echo "pname=".$pname."<br>";
        $partname->setField($pname);
        $part->addPartname($partname);

        //Source Organ
        $partso = $this->getValueByHeaderName('Source Organ',$row,$columnData);
        if( !$force || $partso && $partso != '' ) {
            $sourceOrganTransformer = new SourceOrganTransformer($em,$provider);
            $sourceOrganList = $sourceOrganTransformer->reverseTransform($partso); //OrganList
            $partSourceOrgan = new PartSourceOrgan($status, $provider, $source);
            $partSourceOrgan->setField($sourceOrganList);
            $part->addSourceOrgan($partSourceOrgan);
        }

        //Gross Description
        $partgd = $this->getValueByHeaderName('Gross Description',$row,$columnData);
        if( !$force || $partgd && $partgd != '' ) {
            $partDescription = new PartDescription($status,$provider,$source);
            $partDescription->setField($partgd);
            $part->addDescription($partDescription);
        }

        //Diagnosis
        $partdiag = $this->getValueByHeaderName('Diagnosis',$row,$columnData);
        if( !$force || $partdiag && $partdiag != '' ) {
            $partDisident = new PartDisident($status,$provider,$source);
            $partDisident->setField($partdiag);
            $part->addDisident($partDisident);
        }

        //Differential Diagnoses
        $partdiffdiag = $this->getValueByHeaderName('Differential Diagnoses',$row,$columnData);
        if( !$force || $partdiffdiag && $partdiffdiag != '' ) {
            $partDiffDisident = new PartDiffDisident($status,$provider,$source);
            $partDiffDisident->setField($partdiffdiag);
            $part->addDiffDisident($partDiffDisident);
        }

        //Type of Disease
        $partdistype = $this->getValueByHeaderName('Type of Disease',$row,$columnData);
        if( !$force || $partdistype && $partdistype != '' ) {
            $partDiseaseType = new PartDiseaseType($status,$provider,$source);
            $partDiseaseType->setField($partdistype);
            //Origin of Disease
            $partDiseaseType->setOrigin($this->getValueByHeaderName('Origin of Disease',$row,$columnData));
            //Primary Site of Disease Origin
            $sourceOrganTransformer = new SourceOrganTransformer($em,$provider);
            $primaryOrganList = $sourceOrganTransformer->reverseTransform($this->getValueByHeaderName('Primary Site of Disease Origin',$row,$columnData)); //OrganList
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
        $blockname->setField($this->getValueByHeaderName('Block Name',$row,$columnData));
        $block->addBlockname($blockname);

        //Block: Section Source
        $sections = $this->getValueByHeaderName('Block Section Source',$row,$columnData);
        if( !$force || $sections && $sections != '' ) {
            $blocksection = new BlockSectionsource($status,$provider,$source);
            $blocksection->setField($sections);
            $block->addSectionsource($blocksection);
        }

        //Block: Results of Special Stains: StainList + field
        $specialStainValue = $this->getValueByHeaderName('Associated Special Stain Result',$row,$columnData);
        if( !$force || $specialStainValue && $specialStainValue != '' ) {
            $stainTransformer = new StainTransformer($em,$provider);
            $specialstainList = $stainTransformer->reverseTransform($this->getValueByHeaderName('Associated Special Stain Name',$row,$columnData)); //list
            $specialstain = new BlockSpecialStains($status,$provider,$source);
            $specialstain->setStaintype($specialstainList); //StainList
            $specialstain->setField($specialStainValue);    //field
            $block->addSpecialStain($specialstain);
        }

        $part->addBlock($block);

        ////////////////// Slide /////////////////
        $slide = new Slide(false, $status, $provider, $source);

        //Slide Title
        $slide->setTitle($this->getValueByHeaderName('Slide Title',$row,$columnData));

        //Microscopic Description
        $slide->setMicroscopicdescr($this->getValueByHeaderName('Microscopic Description',$row,$columnData));

        //Slide Type
        $slidetype = $em->getRepository('OlegOrderformBundle:SlideType')->findOneByName($this->getValueByHeaderName('Slide Type',$row,$columnData));
        $slide->setSlidetype($slidetype);

        //Stain
        $stainValue = $this->getValueByHeaderName('Stain',$row,$columnData);
        if( !$force || $stainValue && $stainValue != '' ) {
            $stainTransformer = new StainTransformer($em,$provider);
            $stainList = $stainTransformer->reverseTransform($stainValue);

            $stain = new Stain($status, $provider, $source);
            $stain->setField($stainList);

            $slide->addStain($stain);
        }

        ///// Scan /////
        $scan = new Scan($status, $provider, $source);

        //Scan: Scan Magnificaiton
        $mag = $this->getValueByHeaderName('Scan Magnificaiton',$row,$columnData);
        //echo "<br>mag=".$mag."<br>";
        $scan->setField($mag);

        //Scan: Region to Scan
        $regTransformer = new StringTransformer($em,$provider);
        $scanregion = $regTransformer->reverseTransform($this->getValueByHeaderName('Region to Scan',$row,$columnData));
        //echo "scanregion=".$scanregion."<br>";
        $scan->setScanregion($scanregion);

        //Scan: Reason for Scan/Note
        $note = $this->getValueByHeaderName('Reason for Scan/Note',$row,$columnData);
        //echo "note=".$note."<br>";
        $scan->setNote($note);

        $slide->addScan($scan);
        ///// EOF Scan /////

        //Link(s) to related image(s)
        $relevantScans = $this->getValueByHeaderName('Link(s) to related image(s)',$row,$columnData);
        if( !$force || $relevantScans && $relevantScans != '' ) {
            $relScan = new RelevantScans($status,$provider,$source);
            $relScan->setField($relevantScans);
            $slide->addRelevantScan($relScan);
        }


        $block->addSlide($slide);

        return $patient;
    }

    public function getValueByHeaderName($header, $row, $headers) {
        $key = array_search($header, $headers);
        return $row[$key];
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