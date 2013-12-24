<?php

namespace Oleg\OrderformBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Oleg\OrderformBundle\Entity\OrderInfo;
use Oleg\OrderformBundle\Form\OrderInfoType;
use Oleg\OrderformBundle\Entity\Patient;
use Oleg\OrderformBundle\Form\PatientType;
use Oleg\OrderformBundle\Entity\ClinicalHistory;
use Oleg\OrderformBundle\Entity\PatientMrn;
use Oleg\OrderformBundle\Entity\Procedure;
use Oleg\OrderformBundle\Form\ProcedureType;
use Oleg\OrderformBundle\Entity\Accession;
use Oleg\OrderformBundle\Form\AccessionType;
use Oleg\OrderformBundle\Entity\Part;
//use Oleg\OrderformBundle\Entity\DiffDiagnoses;
use Oleg\OrderformBundle\Entity\RelevantScans;
use Oleg\OrderformBundle\Entity\SpecialStains;
use Oleg\OrderformBundle\Form\PartType;
use Oleg\OrderformBundle\Entity\Block;
use Oleg\OrderformBundle\Form\BlockType;
use Oleg\OrderformBundle\Entity\Slide;
use Oleg\OrderformBundle\Form\SlideType;
use Oleg\OrderformBundle\Entity\Scan;
use Oleg\OrderformBundle\Entity\Stain;
//use Oleg\OrderformBundle\Entity\PartPaper;

use Oleg\OrderformBundle\Entity\Educational;
use Oleg\OrderformBundle\Form\EducationalType;
use Oleg\OrderformBundle\Entity\Research;
use Oleg\OrderformBundle\Form\ResearchType;

use Oleg\OrderformBundle\Form\SlideMultiType;

use Oleg\OrderformBundle\Helper\ErrorHelper;
use Oleg\OrderformBundle\Helper\FormHelper;
use Oleg\OrderformBundle\Helper\EmailUtil;

//use Oleg\OrderformBundle\Entity\DataQuality;

//ScanOrder joins OrderInfo + Scan
//@Route("/multi")
/**
 * OrderInfo controller.
 */
class MultyScanOrderController extends Controller {

    /**
     * Edit: If the form exists, use this function
     * @Route("/multi/edit/{id}", name="exist_edit", requirements={"id" = "\d+"})
     * @Method("POST")
     * @Template("OlegOrderformBundle:MultyScanOrder:new.html.twig")
     */
    public function editAction( $id )
    {

        if (false === $this->get('security.context')->isGranted('ROLE_USER')) {
            return $this->render('OlegOrderformBundle:Security:login.html.twig');
        }

        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('OlegOrderformBundle:Slide')->findOneBy($id);

        $em->persist($entity);
        $em->flush();

        $this->showMultyAction($entity->getId(), "show");

    }


    /**
     * Creates a new OrderInfo entity.
     *
     * @Route("/", name="singleorder_create")
     * @Route("/multi/research/new", name="res_create")
     * @Route("/multi/educational/new", name="edu_create")
     * @Route("/multi/clinical/new", name="clinical_create")
     * @Method("POST")
     * @Template("OlegOrderformBundle:MultyScanOrder:new.html.twig")
     */
    public function multyCreateAction(Request $request)
    { 

        //echo "multi new controller !!!! <br>";
        //exit();

        if (false === $this->get('security.context')->isGranted('ROLE_USER')) {
            return $this->render('OlegOrderformBundle:Security:login.html.twig'

            );
        }

        $em = $this->getDoctrine()->getManager();

        //echo " controller multy<br>";
        //exit();

        $entity  = new OrderInfo();
                      
        //initialize all form's fields, even if they are empty
        $user = $this->get('security.context')->getToken()->getUser();

        $entity->addProvider($user);

        $status = 'invalid';

        $patient = new Patient(true,$status,$user);
        $entity->addPatient($patient);

        $procedure = new Procedure(true,$status,$user);
        $patient->addProcedure($procedure);

        $accession = new Accession(true,$status,$user);
        $procedure->addAccession($accession);

        $part = new Part(true,$status,$user);
        $accession->addPart($part);

        $block = new Block(true,$status,$user);
        $part->addBlock($block);

        $slide = new Slide(true,'valid',$user); //Slides are always valid by default
        $block->addSlide($slide);
        

        $request = $this->container->get('request');
        $routeName = $request->get('_route');
        //echo "routeName=".$routeName;

        if( $routeName == "singleorder_create" ) {
            $type = "singleorder";
        } elseif( $routeName == "clinical_create") {
            $type = "clinical";
        } elseif( $routeName == "edu_create") {
            $type = "educational";
        } elseif( $routeName == "res_create") {
            $type = "research";
        } else {
            $type = "singleorder";
        }

        $params = array('type'=>$type, 'cicle'=>'create', 'service'=>null);

//        echo "controller create=";
//        print_r($params);
//        echo "<br>";

        $form = $this->createForm(new OrderInfoType($params,$entity), $entity);
//        $form->bind($request);
        $form->handleRequest($request);

        //check if the orderform already exists, so it's edit case
        //TODO: edit id is empty. Why??
//        echo "id=".$entity->getId()."<br>";
//        echo "entity count=".count($entity)."<br>";
//        echo "patient count=".count($entity->getPatient())." patient=".$entity->getPatient()[0]."<br>";
//        $id = $form["id"]->getData();
//        $provider = $form["provider"]->getData();
//        echo "form field id=".$id.", provider=".$provider."<br>";
//        //$request  = $this->getRequest();
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
            }

            if( isset($_POST['btnAmend']) ) {
                $cicle = 'amend';               
            }

            $entity->setCicle($cicle);

            $entity = $em->getRepository('OlegOrderformBundle:OrderInfo')->processOrderInfoEntity( $entity, $type );

            if( isset($_POST['btnSave']) ) {
                $status = $em->getRepository('OlegOrderformBundle:Status')->findOneByName('Not Submitted');             
                $entity->setStatus($status);
            }

            if( isset($_POST['btnSubmit']) || isset($_POST['btnAmend']) ) {

                $conflictStr = "";
                foreach( $entity->getDataquality() as $dq ) {
                    $conflictStr = $conflictStr . "\r\n".$dq->getDescription()."\r\n"."Resolved by replacing: ".$dq->getAccession()." => ".$dq->getNewaccession()."\r\n";
                }

                //email
                //$email = $this->get('security.context')->getToken()->getAttribute('email');
                $user = $this->get('security.context')->getToken()->getUser();
                $email = $user->getEmail();
                $emailUtil = new EmailUtil();


                if( isset($_POST['btnAmend']) ) {
                    $text =
                        "Thank You For Your Order !\r\n"
                        . "Order #" . $entity->getId() . " Successfully Amended.\r\n"
                        . "Confirmation Email was sent to " . $email . "\r\n";
                } else {
                    $text = null;
                }

                $emailUtil->sendEmail( $email, $entity, $text, $conflictStr );


                $conflicts = array();
                foreach( $entity->getDataquality() as $dq ) {
                    $conflicts[] = $dq->getDescription()."\nResolved by replacing:\n".$dq->getAccession()." => ".$dq->getNewaccession();
                }

                return $this->render('OlegOrderformBundle:ScanOrder:thanks.html.twig', array(
                    'orderid' => $entity->getId(),
                    'originalid' => $entity->getOriginalid(),
                    'conflicts' => $conflicts,
                    'cicle' => $cicle
                ));
            }

            if (isset($_POST['btnSave'])) {
//                $response = $this->forward('OlegOrderformBundle:ScanOrder:multi', array(
//                    'id'  => $entity->getId(),
//                ));
                $this->showMultyAction($entity->getId(), "edit");
            }

        }

        //form always valid, so this will not be reached anyway
        return array(           
            'form'   => $form->createView(),
            'type' => 'new',
            'multy' => 'multy'
        );    
    }    
    
    /**
     * Displays a form to create a new OrderInfo + Scan entities.
     *
     * @Route("/", name="single_new")
     * @Route("/multi/research/new", name="res_new")
     * @Route("/multi/educational/new", name="edu_new")
     * @Route("/multi/clinical/new", name="clinical_new")
     * @Method("GET")
     * @Template("OlegOrderformBundle:MultyScanOrder:new.html.twig")
     */
    public function newMultyAction()
    {

        if (false === $this->get('security.context')->isGranted('ROLE_USER')) {
            return $this->render('OlegOrderformBundle:Security:login.html.twig');
        }

        $em = $this->getDoctrine()->getManager();

        $entity = new OrderInfo();
        $user = $this->get('security.context')->getToken()->getUser();

        //echo $user."<br>";
        //$email = $user->getEmail();

//        $dataQuality = new DataQuality();
//        $entity->addDataQuality($dataQuality);

        $entity->addProvider($user);

        $patient = new Patient(true,'invalid',$user);
        $entity->addPatient($patient);

        $procedure = new Procedure(true,'invalid',$user);
        $patient->addProcedure($procedure);

        $accession = new Accession(true,'invalid',$user);
        $procedure->addAccession($accession);

        $part = new Part(true,'invalid',$user);
        $accession->addPart($part);

        $block = new Block(true,'invalid',$user);
        $part->addBlock($block);

        $slide = new Slide(true,'valid',$user); //Slides are always valid by default
        $block->addSlide($slide);

        $request = $this->container->get('request');
        $routeName = $request->get('_route');
        //echo "routeName=".$routeName;

        if( $routeName == "clinical_new") {
            $type = "clinical";
        } elseif( $routeName == "edu_new") {
            //echo " add edu ";
            $type = "educational";
            $edu = new Educational();
            $entity->setEducational($edu);
        } elseif( $routeName == "res_new") {
            $type = "research";
            $res = new Research();
            $entity->setResearch($res);
        } elseif( $routeName == "single_new") {
            $type = "singleorder";
        } else {
            $type = "singleorder";
        }

        //$slide2 = new Slide();
        //$block->addSlide($slide2);

        //get pathology service for this user
        $service = $user->getPathologyServices();

        $params = array('type'=>$type, 'cicle'=>'new', 'service'=>$service);
        $form   = $this->createForm( new OrderInfoType($params, $entity), $entity );
        
//        return array(
//            'form' => $form->createView(),
//            'type' => 'new',
//            'multy' => $type
//        );

        if( $routeName != "single_new") {
            return $this->render('OlegOrderformBundle:MultyScanOrder:new.html.twig', array(
                'form' => $form->createView(),
                'type' => 'new',
                'multy' => $type
            ));
        } else {
            return $this->render('OlegOrderformBundle:MultyScanOrder:newsingle.html.twig', array(
                'form' => $form->createView(),
                'cycle' => 'new'
            ));
        }

    }


    /**
     * Displays a form to create a new OrderInfo + Scan entities.
     * @Route("/edit/{id}", name="multy_edit", requirements={"id" = "\d+"})
     * @Route("/amend/{id}", name="order_amend", requirements={"id" = "\d+"})
     * @Route("/show/{id}", name="multy_show", requirements={"id" = "\d+"})
     * @Method("GET")
     * @Template("OlegOrderformBundle:MultyScanOrder:new.html.twig")
     */
    public function showMultyAction( $id, $type = "show" )
    {

        if (false === $this->get('security.context')->isGranted('ROLE_USER')) {
            return $this->render('OlegOrderformBundle:Security:login.html.twig');
        }

        $em = $this->getDoctrine()->getManager();

        //TODO: is it possible to filter orderinfo by JOINs?

        //INNER JOIN orderinfo.block block
        $query = $em->createQuery('
            SELECT orderinfo
            FROM OlegOrderformBundle:OrderInfo orderinfo
            INNER JOIN orderinfo.patient patient
            INNER JOIN orderinfo.procedure procedure
            INNER JOIN orderinfo.accession accession
            INNER JOIN orderinfo.part part

            INNER JOIN orderinfo.slide slide
            WHERE orderinfo.id = :id'
        )->setParameter('id', $id);

        $entities = $query->getResult();

        //echo "<br>orderinfo count=".count( $entities )."<br>";

        if( count( $entities ) == 0 ) {
            throw $this->createNotFoundException('Unable to find OrderInfo entity.');
        }

        if( count( $entities ) == 0 ) {
            throw $this->createNotFoundException('More than one OrderInfo entity found.');
        } else {
            $entity = $entities[0];
        }

        //echo $entity;
        //echo $entity->getStatus();
        //echo "<br>Procedure count=".count( $entity->getProcedure() );

        //patient
        foreach( $entity->getPatient() as $patient ) {

//            echo "<br>patient order info count=".count( $patient->getOrderInfo() )."<br>";
            //check if patient has this orderinfo
            if( !$this->hasOrderInfo($patient,$id) ) {
                //echo "remove patient!!!! <br>";
                $entity->removePatient($patient);
                continue;
            }

//            echo "<br>patient has procedure count=".count( $patient->getProcedure() )."<br>";

            //procdeure
            foreach( $patient->getProcedure() as $procedure ) {

                if( !$this->hasOrderInfo($procedure,$id) ) {
                    $patient->removeProcedure($procedure);
                    continue;
                }

                //accession
                foreach( $procedure->getAccession() as $accession ) {
                    if( !$this->hasOrderInfo($accession,$id) ) {
                        $procedure->removeAccession($accession);
                        continue;
                    }

                    //part
                    foreach( $accession->getPart() as $part ) {
                       if( !$this->hasOrderInfo($part,$id) ) {
                            $accession->removePart($part);
                            continue;
                        }
                        //echo "diff diagnoses=".count($part->getDiffDiagnoses())."<br>";

                        //block
                        foreach( $part->getBlock() as $block ) {
                            if( !$this->hasOrderInfo($block,$id) ) {
                                $part->removeBlock($block);
                                continue;
                            }

                            //slide
                            foreach( $block->getSlide() as $slide ) {
                                if( !$this->hasOrderInfo($slide,$id) ) {
                                    $block->removeSlide($slide);
                                    continue;
                                }
                            }//slide
                        }//block
                    }//part
                }//accession
            }//procedure
        }//patient

        //echo "<br>Procedure count=".count( $entity->getProcedure() );

        $disable = true;

        $request = $this->container->get('request');
        $routeName = $request->get('_route');

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

        $params = array('type'=>$single_multy, 'cicle'=>$type, 'service'=>null);
        $form   = $this->createForm( new OrderInfoType($params,$entity), $entity, array('disabled' => $disable) );

        //echo "type=".$entity->getType();
        //exit();

//        $id = $form["id"]->getData();
//        $provider = $form["provider"]->getData();
//        echo "id=".$id.", provider=".$provider.", type=".$type."<br>";

        return array(
            'form' => $form->createView(),
            'type' => $type,
            'multy' => $entity->getType()
        );
    }

    public function hasOrderInfo( $entity, $id ) {
        $has = false;
        foreach( $entity->getOrderInfo() as $child ) {
            if( $child->getId() == $id ) {
                $has = true;
            }
        }
        return $has;
    }

    /**
     * Displays a form to create a new OrderInfo + Scan entities.
     * @Route("/download/{id}", name="download_file")
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
