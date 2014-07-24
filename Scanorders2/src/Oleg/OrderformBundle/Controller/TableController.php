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
     * @Template("OlegOrderformBundle:MultiScanOrder:viewtable.html.twig")
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
            $rowArr['Accession Type'] = $acckey->getKeytype()->getName();
            $rowArr['Accession Number'] = $acckey->getField();

            //part: 1
            $rowArr['Part Name'] = $part->obtainValidKeyField()->getField();

            //block: 1
            $rowArr['Block Name'] = $block->obtainValidKeyField()->getField();

            //slide: 4
            $rowArr['Stain'] = $slide->getStain()->first()->getField()->getName();
            $rowArr['Scan Magnificaiton'] = $slide->getScan()->first()->getField();
            $rowArr['Diagnosis'] = $part->obtainValidField('disident',$id)->getField();
            $rowArr['Reason for Scan/Note'] = $slide->getScan()->first()->getNote();

            //part 1
            $rowArr['Source Organ'] = ( $part->obtainValidField('sourceOrgan',$id)->getField() ? $part->obtainValidField('sourceOrgan',$id)->getField()->getName() : null );

            //patient: 4
            $patientkey = $patient->obtainValidKeyField();
            $rowArr['MRN Type'] = $patientkey->getKeytype()->getName();
            $rowArr['MRN'] = $patientkey->getField();
            $rowArr['Patient DOB'] = $transformer->transform($patient->obtainValidField('dob',$id)->getField());
            $rowArr['Clinical Summary'] = $patient->obtainValidField('clinicalHistory',$id)->getField();

            //accession: 1
            $rowArr['Accession Date'] = $transformer->transform($accession->obtainValidField('accessionDate',$id)->getField());

            //procedure: 6
            $rowArr['Procedure Type'] = ( $procedure->getName()->first()->getField() ? $procedure->getName()->first()->getField()->getId() : null );
            $rowArr['Encounter Date'] = $transformer->transform($procedure->obtainValidField('encounterDate',$id)->getField());
            $rowArr["Patient's Last Name"] = $procedure->obtainValidField('patlastname',$id)->getField();
            $rowArr["Patient's First Name"] = $procedure->obtainValidField('patfirstname',$id)->getField();
            $rowArr["Patient's Middle Name"] = $procedure->obtainValidField('patmiddlename',$id)->getField();
            $rowArr['Patient Sex'] = $procedure->obtainValidField('patsex',$id)->getField();
            $rowArr['Patient Age'] = $procedure->obtainValidField('patage',$id)->getField();
            $rowArr['Clinical History'] = $procedure->obtainValidField('pathistory',$id)->getField();

            //part: 5
            $rowArr['Gross Description'] = $part->obtainValidField('description',$id)->getField();
            $rowArr['Differential Diagnoses'] = $part->obtainValidField('diffDisident',$id)->getField();
            $rowArr['Type of Disease'] = $part->obtainValidField('diseaseType',$id)->getField();
            $rowArr['Origin of Disease'] = $part->obtainValidField('diseaseType',$id)->getOrigin();
            $rowArr['Primary Site of Disease Origin'] = ( $part->obtainValidField('diseaseType',$id)->getPrimaryOrgan() ? $part->obtainValidField('diseaseType',$id)->getPrimaryOrgan()->getName() : null );

            //block: 3
            $rowArr['Block Section Source'] = $block->obtainValidField('sectionsource',$id)->getField();
            $rowArr['Associated Special Stain Name'] = $block->obtainValidField('specialStains',$id)->getStaintype()->getName();
            $rowArr['Associated Special Stain Result'] = $block->obtainValidField('specialStains',$id)->getField();

            //slide: 5
            $rowArr['Slide Title'] = $slide->getTitle();
            $rowArr['Slide Type'] = $slide->getSlidetype()->getName();
            $rowArr['Microscopic Description'] = $slide->getMicroscopicdescr();
            $rowArr['Link(s) to related image(s)'] = $slide->getRelevantScans()->first()->getField();
            $rowArr['Region to Scan'] = $slide->getScan()->first()->getScanregion();

            $jsonData[] = $rowArr;
            //array_push($jsonData, $rowArr);
        }

        //print_r($jsonData);
        //var_dump($jsonData);

        return $this->render('OlegOrderformBundle:MultiScanOrder:viewtable.html.twig', array(
            'orderdata' => json_encode($jsonData),
            'entity' => $orderinfo,
            'form' => $form->createView(),
            'type' => $type,
            'formtype' => $orderinfo->getType(),
            'history' => null
        ));

    }


    /**
     * @Route("/scan-order/multi-slide-table-view/new", name="table_create")
     * @Template("OlegOrderformBundle:MultiScanOrder:viewtable.html.twig")
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

        return $this->render('OlegOrderformBundle:MultiScanOrder:viewtable.html.twig', array(
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
     * @Template("OlegOrderformBundle:MultiScanOrder:viewtable.html.twig")
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

        $headers = array_shift($data);
        //var_dump($columnData);

        //echo "entity inst=".$entity->getInstitution()."<br>";

        $count = 0;
        foreach( $data as $row ) {
            //var_dump($row);
            //echo "<br>";

            $accValue = $this->getValueByHeaderName('Accession Number',$row,$headers);

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
        $mrntype = $mrnTransformer->reverseTransform($this->getValueByHeaderName('MRN Type',$row,$columnData));
        $patientmrn->setKeytype($mrntype);
        $mrnValue = $this->getValueByHeaderName('MRN',$row,$columnData);
        $patientmrn->setField($mrnValue);
        $patientmrn->setOriginal($mrnValue);
        $patient->addMrn($patientmrn);

        //dob
        $dob = $this->getValueByHeaderName('Patient DOB',$row,$columnData);
        if( $force || $dob && $dob != '' ) {
            $patientdob = new PatientDob($status,$provider,$source);
            if( $dob == "" ) {
                $dobFormat = NULL;
            } else {
                $dobFormat = new \DateTime($dob);
            }
            //echo "dobFormat=".date('d/M/Y', $dobFormat)."<br>";
            $patientdob->setField($dobFormat);
            $patient->addDob($patientdob);
        }

        //Clinical History
        $clsum = $this->getValueByHeaderName('Clinical Summary',$row,$columnData);
        if( $force || $clsum && $clsum != '' ) {
            $patientch = new PatientClinicalHistory($status,$provider,$source);
            $patientch->setField($clsum);
            $patient->addClinicalHistory($patientch);
        }

        ///////////////// Procedure /////////////////
        $procedure = new Procedure(false, $status, $provider, $source);

        //Procedure name
        $ptype = $this->getValueByHeaderName('Procedure Type',$row,$columnData);
        if( $force || $ptype && $ptype != '' ) {
            $procedureTransform = new ProcedureTransformer($em,$provider);
            $procedurenameList = $procedureTransform->reverseTransform($ptype); //ProcedureList
            $procedureName = new ProcedureName($status,$provider,$source);
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
        if( $force || $encounterDate && $encounterDate != '' ) {
            if( $encounterDate == "" ) {
                $encounterDateFormat = NULL;
            } else {
                $encounterDateFormat = new \DateTime($encounterDate);
            }
            $encounterDateObj = new ProcedureEncounterDate($status,$provider,$source);
            $encounterDateObj->setField($encounterDateFormat);
            $procedure->addEncounterDate($encounterDateObj);
        }

        //Procedure Last Name
        $patlastname = $this->getValueByHeaderName("Patient's Last Name",$row,$columnData);
        if( $force || $patlastname && $patlastname != '' ) {
            $patlastnameObj = new ProcedurePatlastname($status,$provider,$source);
            $patlastnameObj->setField($patlastname);
            $procedure->addPatlastname($patlastnameObj);
        }

        //Procedure First Name
        $patfirstname = $this->getValueByHeaderName("Patient's First Name",$row,$columnData);
        if( $force || $patfirstname && $patfirstname != '' ) {
            $patfirstnameObj = new ProcedurePatfirstname($status,$provider,$source);
            $patfirstnameObj->setField($patfirstname);
            $procedure->addPatfirstname($patfirstnameObj);
        }

        //Procedure Middle Name
        $patmiddlename = $this->getValueByHeaderName("Patient's Middle Name",$row,$columnData);
        if( $force || $patmiddlename && $patmiddlename != '' ) {
            $patmiddlenameObj = new ProcedurePatmiddlename($status,$provider,$source);
            $patmiddlenameObj->setField($patmiddlename);
            $procedure->addPatmiddlename($patmiddlenameObj);
        }

        //Procedure Sex
        $patsex = $this->getValueByHeaderName('Patient Sex',$row,$columnData);
        if( $force || $patsex && $patsex != '' ) {
            $patsexObj = new ProcedurePatsex($status,$provider,$source);
            $patsexObj->setField($patsex);
            $procedure->addPatsex($patsexObj);
        }

        //Procedure Age
        $patage = $this->getValueByHeaderName('Patient Age',$row,$columnData);
        if( $force || $patage && $patage != '' ) {
            $patageObj = new ProcedurePatage($status,$provider,$source);
            $patageObj->setField($patage);
            $procedure->addPatage($patageObj);
        }

        //Clinical History
        $pathistory = $this->getValueByHeaderName('Clinical History',$row,$columnData);
        if( $force || $pathistory && $pathistory != '' ) {
            $pathistoryObj = new ProcedurePathistory($status,$provider,$source);
            $pathistoryObj->setField($pathistory);
            $procedure->addPathistory($pathistoryObj);
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
        if( $force || $accessionDate && $accessionDate != '' ) {
            if( $encounterDate == "" ) {
                $accessionDateFormat = NULL;
            } else {
                $accessionDateFormat = new \DateTime($accessionDate);
            }
            $accessionDateObj = new AccessionAccessionDate($status,$provider,$source);
            $accessionDateObj->setField($accessionDateFormat);
            $accession->addAccessionDate($accessionDateObj);
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
        if( $force || $partso && $partso != '' ) {
            $sourceOrganTransformer = new SourceOrganTransformer($em,$provider);
            $sourceOrganList = $sourceOrganTransformer->reverseTransform($partso); //OrganList
            $partSourceOrgan = new PartSourceOrgan($status,$provider,$source);
            $partSourceOrgan->setField($sourceOrganList);
            $part->addSourceOrgan($partSourceOrgan);
        }

        //Gross Description
        $partgd = $this->getValueByHeaderName('Gross Description',$row,$columnData);
        if( $force || $partgd && $partgd != '' ) {
            $partDescription = new PartDescription($status,$provider,$source);
            $partDescription->setField($partgd);
            $part->addDescription($partDescription);
        }

        //Diagnosis
        $partdiag = $this->getValueByHeaderName('Diagnosis',$row,$columnData);
        if( $force || $partdiag && $partdiag != '' ) {
            $partDisident = new PartDisident($status,$provider,$source);
            $partDisident->setField($partdiag);
            $part->addDisident($partDisident);
        }

        //Differential Diagnoses
        $partdiffdiag = $this->getValueByHeaderName('Differential Diagnoses',$row,$columnData);
        if( $force || $partdiffdiag && $partdiffdiag != '' ) {
            $partDiffDisident = new PartDiffDisident($status,$provider,$source);
            $partDiffDisident->setField($partdiffdiag);
            $part->addDiffDisident($partDiffDisident);
        }

        //Type of Disease
        $partdistype = $this->getValueByHeaderName('Type of Disease',$row,$columnData);
        if( $force || $partdistype && $partdistype != '' ) {
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
        if( $force || $sections && $sections != '' ) {
            $blocksection = new BlockSectionsource($status,$provider,$source);
            $blocksection->setField($sections);
            $block->addSectionsource($blocksection);
        }

        //Block: Results of Special Stains: StainList + field
        $specialStainValue = $this->getValueByHeaderName('Associated Special Stain Result',$row,$columnData);
        if( $force || $specialStainValue && $specialStainValue != '' ) {
            $stainTransformer = new StainTransformer($em,$provider);

            //special stain type might be null in table, so get one from StainList with smallest 'orderinlist'
            $specialstainList = $stainTransformer->reverseTransform($this->getValueByHeaderName('Associated Special Stain Name',$row,$columnData)); //list
            if( $specialstainList == null ) {
                $stainList = $em->getRepository('OlegOrderformBundle:StainList')->findBy(array(), array('orderinlist'=>'ASC'));
                $specialstainList = $stainList[0];
            }

            $specialstain = new BlockSpecialStains($status,$provider,$source);
            $specialstain->setStaintype($specialstainList); //StainList
            $specialstain->setField($specialStainValue);    //field
            $block->addSpecialStain($specialstain);
        }

        $part->addBlock($block);

        ////////////////// Slide /////////////////
        $slide = new Slide(false, $status, $provider, $source);

        //Slide set Sequence
        $slide->setSequence($count);

        //Slide Title
        $slide->setTitle($this->getValueByHeaderName('Slide Title',$row,$columnData));

        //Microscopic Description
        $slide->setMicroscopicdescr($this->getValueByHeaderName('Microscopic Description',$row,$columnData));

        //Slide Type
        $slidetype = $em->getRepository('OlegOrderformBundle:SlideType')->findOneByName($this->getValueByHeaderName('Slide Type',$row,$columnData));
        $slide->setSlidetype($slidetype);

        //Stain
        $stainValue = $this->getValueByHeaderName('Stain',$row,$columnData);
        if( $force || $stainValue && $stainValue != '' ) {
            $stainTransformer = new StainTransformer($em,$provider);
            $stainList = $stainTransformer->reverseTransform($stainValue);

            $stain = new Stain($status,$provider,$source);
            $stain->setField($stainList);

            $slide->addStain($stain);
        }

        ///// Scan /////
        $scan = new Scan($status,$provider,$source);

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
        if( $force || $relevantScans && $relevantScans != '' ) {
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