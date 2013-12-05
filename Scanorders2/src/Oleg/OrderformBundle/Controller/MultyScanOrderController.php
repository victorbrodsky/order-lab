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

//ScanOrder joins OrderInfo + Scan
/**
 * OrderInfo controller.
 *
 * @Route("/multi")
 */
class MultyScanOrderController extends Controller {
   
    /**
     * Lists all OrderInfo entities.
     *
     * @Route("/index", name="multyIndex")
     * @Method("GET")
     * @Template("OlegOrderformBundle:MultyScanOrder:index.html.twig")
     */
    public function multyIndexAction() {
        
        if (false === $this->get('security.context')->isGranted('ROLE_USER')) {
            //throw new AccessDeniedException();
            return $this->render('OlegOrderformBundle:Security:login.html.twig');
        }
        
        $em = $this->getDoctrine()->getManager();
        
        //findAll();
        $entities = $em->getRepository('OlegOrderformBundle:OrderInfo')->                   
                    findBy(array(), array('orderdate'=>'desc')); 
       
        //$slides = $em->getRepository('OlegOrderformBundle:Slide')->findAll();
        
        return array(
            'entities' => $entities,  
            //'slides' => $slides
        );
    }


    /**
     * Edit: If the form exists, use this function
     * @Route("/edit/{id}", name="exist_edit", requirements={"id" = "\d+"})
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
     * @Route("/single/new", name="singleorder_create")
     * @Route("/research/new", name="res_create")
     * @Route("/educational/new", name="edu_create")
     * @Route("/clinical/new", name="clinical_create")
     * @Method("POST")
     * @Template("OlegOrderformBundle:MultyScanOrder:new.html.twig")
     */
    public function multyCreateAction(Request $request)
    { 

//        echo "multi new controller !!!! <br>";
//        exit();

        if (false === $this->get('security.context')->isGranted('ROLE_USER')) {
            return $this->render('OlegOrderformBundle:Security:login.html.twig'

            );
        }

        $em = $this->getDoctrine()->getManager();

        //echo " controller multy<br>";
        //exit();

        $entity  = new OrderInfo();

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
        if( 1 ) {

            //echo "id2=".$entity->getId()."<br>";
            //exit();

//            echo "<br>Before loop:<br>";
//            echo "<br>part count=".count($entity->getPart())."<br>";
//            foreach( $entity->getPart() as $part ) {
//                echo "<br>part id=".$part->getId();
//                echo "<br>part name=".$part->getPartname()->first();
//                echo "<br>part validity=".$part->getPartname()->first()->getValidity();
//            }


//            $patientname = $form['patient'][0]['mrn'][0]['field']->getData();
//            echo "patientname=".$patientname."<br>";
//
//            $accessionname = $form['patient'][0]['procedure'][0]['accession'][0]['accession'][0]['field']->getData();
//            echo "accessionname=".$accessionname."<br>";
//            print_r( $accessionname );
//            echo " accessionname count=".count($form['patient'][0]['procedure'][0]['accession'][0]['accession'])."<br>";
//
            $proceduretype = $form['patient'][0]['procedure'][0]['name'][0]['field']->getData();
            echo "proceduretype=".$proceduretype."<br>";
//
            $partname = $form['patient'][0]['procedure'][0]['accession'][0]['part'][0]['partname'][0]['field']->getData();
            echo "part partname=".$partname."<br>";
//            //print_r($partname);
//            echo " !!!partname count=".count($form['patient'][0]['procedure'][0]['accession'][0]['part'][0]['partname'])."<br>";

            $blockname = $form['patient'][0]['procedure'][0]['accession'][0]['part'][0]['block'][0]['blockname'][0]['field']->getData();
            echo "block blockname=".$blockname."<br>";
//
//            $description = $form['patient'][0]['procedure'][0]['accession'][0]['part'][0]['description'][0]['field']->getData();
//            echo "part description=".$description."<br>";
//
//            exit();

            $entity = $em->getRepository('OlegOrderformBundle:OrderInfo')->processEntity( $entity, $type );

//            echo "<br>After loop:<br>";
//            echo "patient count=".count($entity->getPatient());
//            foreach( $entity->getPatient() as $pat ) {
//                echo "<br>mrn=".$pat->getMrn();
//                echo "<br>name=".$pat->getName();
//                echo "<br>sex=".$pat->getSex();
//                //echo "<br>dob=".$pat->getDob();
//
//                foreach( $pat->getClinicalHistory() as $hist ) {
//                    echo "<br>hist id=".$hist->getId();
//                    echo "<br>hist text=".$hist->getClinicalHistory();
//                    echo "<br>hist Provider=".$hist->getProvider()[0];
//                }
//
//            }
//            exit();

//            echo "part count=".count($entity->getPart());
//            foreach( $entity->getPart() as $part ) {
//                echo "<br>id=".$part->getId();
//                echo "<br>name=".$part->getPartname()->first();
//                echo "<br>Diagnos=".$part->getDiagnos()->first();
//                //echo "<br>dob=".$pat->getDob();
//
//                foreach( $part->getDiagnos() as $diag ) {
//                    echo "<br>diag id=".$diag->getId();
//                    echo "<br>diag text=".$diag->getField();
//                    //echo "<br>hist creator=".$hist->getProvider()[0]->getUsername();
//                }
//
//            }
//            exit();

            if( isset($_POST['btnSave']) ) {
                $status = $em->getRepository('OlegOrderformBundle:Status')->findOneByName('Not Submitted');             
                $entity->setStatus($status);
            }

            //echo "entity=".$entity;
            //exit();

            //$em->persist($entity);
            //$em->flush();

            if( isset($_POST['btnSubmit']) ) {
                //email
                //$email = $this->get('security.context')->getToken()->getAttribute('email');
                $user = $this->get('security.context')->getToken()->getUser();
                $email = $user->getEmail();

                $emailUtil = new EmailUtil();
                $emailUtil->sendEmail( $email, $entity, null );

                return $this->render('OlegOrderformBundle:ScanOrder:thanks.html.twig', array(
                    'orderid' => $entity->getId(),
                ));
            }

            if (isset($_POST['btnSave'])) {
//                $response = $this->forward('OlegOrderformBundle:ScanOrder:multi', array(
//                    'id'  => $entity->getId(),
//                ));
                $this->showMultyAction($entity->getId(), "edit");
            }

        }

        return array(           
            'form'   => $form->createView(),
            'type' => 'new'
        );    
    }    
    
    /**
     * Displays a form to create a new OrderInfo + Scan entities.
     *
     * @Route("/single/new", name="single_new")
     * @Route("/research/new", name="res_new")
     * @Route("/educational/new", name="edu_new")
     * @Route("/clinical/new", name="clinical_new")
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

        //$username = $user->getUsername();
        //$email = $user->getEmail();

        $entity->addProvider($user);

        $patient = new Patient(true);
        $entity->addPatient($patient);

        $procedure = new Procedure(true);
        $patient->addProcedure($procedure);

        $accession = new Accession(true);
        $procedure->addAccession($accession);

        $part = new Part(true);
        $accession->addPart($part);

        $block = new Block(true);
        $part->addBlock($block);

        $slide = new Slide(true);
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
        //INNER JOIN orderinfo.procedure procedure
        $query = $em->createQuery('
            SELECT orderinfo
            FROM OlegOrderformBundle:OrderInfo orderinfo
            INNER JOIN orderinfo.patient patient
            INNER JOIN orderinfo.procedure procedure
            INNER JOIN orderinfo.accession accession
            INNER JOIN orderinfo.part part
            INNER JOIN orderinfo.block block
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

        //echo "route=".$routeName.", type=".$type."<br>";
        if( $type == "edit" || $routeName == "multy_edit") {
            $disable = false;
            $type = "edit";
        }

        //echo "show id=".$entity->getId()."<br>";
        //use always multy because we use nested forms to display single and multy slide orders
        $single_multy = $entity->getType();

        if( $single_multy == 'single' ) {
            $single_multy = 'multy';
        }

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

//    //test ajax json data controller
//    /**
//     * Displays a form to create a new OrderInfo + Scan entities.
//     * @Route("/getdata/{term}", name="getdata")
//     * @Method("POST")
//     * @Method("GET")
//     * @Template("OlegOrderformBundle:MultyScanOrder:new.html.twig")
//     */
//    public function getStainsAction($term) {
//
//        //$request = $this->get('request');
//        //$name=$request->request->get('formName');
//
//        $em = $this->getDoctrine()->getManager();
//
////        $entities = $em->getRepository('OlegOrderformBundle:StainList')->findAll();
////        $output = array();
////        foreach ($entities as $member) {
////            $output[] = array(
////                'id' => $member->getId(),
////                'text' => $member->getname(),
////            );
////        }
//
//        $query = $em->createQuery(
//            'SELECT stain.id as id, stain.name as text
//            FROM OlegOrderformBundle:StainList stain'
//        );
//        $output = $query->getResult();
//
//        $response = new Response();
//        $response->headers->set('Content-Type', 'application/json');
//        $response->setContent(json_encode($output));
//        return $response;
//
////        $arr = array('1','me', 'who');
////
////        $res = array("id"=>1, "text"=>$arr);
////
////        $return=$res1;
////
////        $return=json_encode($return);//jscon encode the array
////        return new Response($return,200,array('Content-Type'=>'application/json'));//make sure it has the correct content type
//    }

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
