<?php

namespace Oleg\OrderformBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Oleg\OrderformBundle\Entity\OrderInfo;
use Oleg\OrderformBundle\Form\OrderInfoType;
use Oleg\OrderformBundle\Entity\Patient;
use Oleg\OrderformBundle\Form\PatientType;
use Oleg\OrderformBundle\Entity\Specimen;
use Oleg\OrderformBundle\Form\SpecimenType;
use Oleg\OrderformBundle\Entity\Accession;
use Oleg\OrderformBundle\Form\AccessionType;
use Oleg\OrderformBundle\Entity\Part;
use Oleg\OrderformBundle\Form\PartType;
use Oleg\OrderformBundle\Entity\Block;
use Oleg\OrderformBundle\Form\BlockType;
use Oleg\OrderformBundle\Entity\Slide;
use Oleg\OrderformBundle\Form\SlideType;
use Oleg\OrderformBundle\Entity\Scan;
use Oleg\OrderformBundle\Entity\Stain;

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
     * @Route("/research/new", name="res_create")
     * @Route("/educational/new", name="edu_create")
     * @Route("/clinical/new", name="clinical_create")
     * @Method("POST")
     * @Template("OlegOrderformBundle:MultyScanOrder:new.html.twig")
     */
    public function multyCreateAction(Request $request)
    { 
        
        if (false === $this->get('security.context')->isGranted('ROLE_USER')) {
            return $this->render('OlegOrderformBundle:Security:login.html.twig'

            );
        }
        
        //echo " controller multy<br>";
        //exit();

        $entity  = new OrderInfo();

        $request = $this->container->get('request');
        $routeName = $request->get('_route');
        //echo "routeName=".$routeName;

        $type = "clinical";

        if( $routeName == "clinical_create") {
            $type = "clinical";
            //$entity->setEducational(null);
            //$entity->setResearch(null);
        }

        if( $routeName == "edu_create") {
            $type = "educational";
            //$entity->setResearch(null);
        }

        if( $routeName == "res_create") {
            $type = "research";
            //$entity->setEducational(null);
        }

        $form = $this->createForm(new OrderInfoType($type, null, $entity ), $entity);
        $form->bind($request);
        //$form->handleRequest($request);

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

//        if( $form->isValid() ) {
        if( 1 ) {

            //echo "id2=".$entity->getId()."<br>";
            //exit();
            $em = $this->getDoctrine()->getManager();                            
                       
            $entity = $em->getRepository('OlegOrderformBundle:OrderInfo')->processEntity( $entity, $type );

//            echo "<br>Before loop:<br>";
//            echo $entity;
            //exit();
            
            //Patient
            //$pat_count = 0;
            foreach( $entity->getPatient() as $patient ) {
                if( !$patient->getId() ) {
                    //echo " pat id null <br>";
                    $entity->removePatient( $patient );
                    $patient = $em->getRepository('OlegOrderformBundle:Patient')->processEntity( $patient );
                    $entity->addPatient($patient);
                } else {
                    continue;
                }
                //echo $pat_count++." !!!!!!!!!!patient = ". $patient. "<br>";
                
                //Procedure
                $specimen_count = 0;
                foreach( $patient->getSpecimen() as $specimen ) {
                    if( !$specimen->getId() ) {
                        //echo " specimen id null <br>";
                        $patient->removeSpecimen( $specimen );
                        $specimen = $em->getRepository('OlegOrderformBundle:Specimen')->processEntity( $specimen, $specimen->getAccession() );
                        $patient->addSpecimen($specimen);
                        $entity->addSpecimen($specimen);
                    } else {
                        continue;
                    }
                    //echo $specimen_count++." !!!!!!!!!!specimen = ". $specimen. "<br>";
                    
                    //Accession
                    //$acc_count = 0;
                    foreach( $specimen->getAccession() as $accession ) {
                        if( !$accession->getId() ) {
                            $specimen->removeAccession( $accession );
                            $accession = $em->getRepository('OlegOrderformBundle:Accession')->processEntity( $accession );
                            $specimen->addAccession($accession);
                            $entity->addAccession($accession);
                        } else {
                            continue;
                        }
                        //echo $acc_count++." !!!!!!!!!!acc = ". $accession. "<br>";

                        //Part
                        //$part_count = 0;
                        foreach( $accession->getPart() as $part ) {
                            if( !$part->getId() ) {
                                $accession->removePart( $part );
                                $part = $em->getRepository('OlegOrderformBundle:Part')->processEntity( $part, $accession );
                                $accession->addPart($part);
                                $entity->addPart($part);
                            } else {
                                continue;
                            }
                            //echo $part_count++." !!!!!!!!!!part = ". $part. "<br>";
                            //Block
                            //$count=0;
                            foreach( $part->getBlock() as $block ) {
                                if( !$block->getId() ) {
                                    //echo " !!!!!!!!!! block0 = ". $block. "<br>";
                                    $part->removeBlock( $block );
                                    $block = $em->getRepository('OlegOrderformBundle:Block')->processEntity( $block, $part );
                                    $part->addBlock($block);
                                    $entity->addBlock($block);
                                    //echo " !!!!!!!!!! block1 = ". $block. "<br>";
                                } else {
                                    continue;
                                }
                                //echo $count++." !!!!!!!!!!block = ". $block. "<br>";
                                //Slide
                                foreach( $block->getSlide() as $slide ) {
                                    //echo "!!!!!!!!!!slide = ". $slide. "<br>";
                                    if( !$slide->getId() ) {
                                        $block->removeSlide( $slide );
                                        $slide = $em->getRepository('OlegOrderformBundle:Slide')->processEntity( $slide );
                                        //$em->getRepository('OlegOrderformBundle:Stain')->processEntity( $slide->getStain() );
                                        //$em->getRepository('OlegOrderformBundle:Scan')->processEntity( $slide->getScan() );
                                        
                                        //$accession->addSlide($slide);  
                                        //$part->addSlide($slide);  
                                        $block->addSlide($slide);                                                                                                                             
                                        $entity->addSlide($slide);
                                    } else {
                                        continue;
                                    }

                                        //Scan
                                        foreach( $slide->getScan() as $scan ) {
                                            //echo "!!!!!!!!!!slide = ". $slide. "<br>";
                                            if( !$scan->getId() ) {
                                                $slide->removeScan( $scan );
                                                $scan = $em->getRepository('OlegOrderformBundle:Scan')->processEntity( $scan );
                                                $slide->addScan($scan);
                                                $entity->addScan($scan);
                                            } else {
                                                continue;
                                            }
                                        } //scan

                                        //Stain
                                        foreach( $slide->getStain() as $stain ) {
                                            //echo "!!!!!!!!!!slide = ". $slide. "<br>";
                                            if( !$stain->getId() ) {
                                                $slide->removeStain( $stain );
                                                $stain = $em->getRepository('OlegOrderformBundle:Stain')->processEntity( $stain );
                                                $slide->addStain($stain);
                                                $entity->addStain($stain);
                                            } else {
                                                continue;
                                            }
                                        } //stain

                                } //slide

                            } //block

                        } //part

                    } //accession

                } //procedure

            } //patient

            //echo "<br>End of loop<br>";
            //echo $entity;
            //exit();

            if (isset($_POST['btnSave'])) {
                //echo "save";
                $entity->setStatus('save');
            }

            //exit();

            $em->persist($entity);
            $em->flush();

            if (isset($_POST['btnSubmit'])) {
                //email
                $email = $this->get('security.context')->getToken()->getAttribute('email');

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

        $entity = new OrderInfo();
        $username = $this->get('security.context')->getToken()->getUser();
        $entity->setProvider($username);

        $patient = new Patient();
        $entity->addPatient($patient);

        //$patient2 = new Patient();
        //$entity->addPatient($patient2);

        $procedure = new Specimen();
        $patient->addSpeciman($procedure);

        //$procedure2 = new Specimen();
        //$patient->addSpeciman($procedure2);

        $accession = new Accession();
        $procedure->addAccession($accession);

        $part = new Part();
        $accession->addPart($part);

        $block = new Block();
        $part->addBlock($block);

        $slide = new Slide();
        $block->addSlide($slide);

        $scan = new Scan();
        $slide->addScan($scan);

        $stain = new Stain();
        $slide->addStain($stain);


        $request = $this->container->get('request');
        $routeName = $request->get('_route');
        //echo "routeName=".$routeName;
        $type = "clinical";
        if( $routeName == "edu_new") {
            //echo " add edu ";
            $type = "educational";
            $edu = new Educational();
            $entity->setEducational($edu);
        }

        if( $routeName == "res_new") {
            $type = "research";
            $res = new Research();
            $entity->setResearch($res);
        }

        //$slide2 = new Slide();
        //$block->addSlide($slide2);

        //get pathology service for this user by email
        $helper = new FormHelper();
        $email = $this->get('security.context')->getToken()->getAttribute('email');
        $service = $helper->getUserPathology($email);
//        if( $service ) {
//            $services = explode("/", $service);
//            $service = $services[0];
//        }
        $entity->setPathologyService($service);

        $form   = $this->createForm( new OrderInfoType(true, $service, $entity), $entity );
        
        return array(          
            'form' => $form->createView(),
            'type' => 'new',
            'multy' => $type
        );
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
        //INNER JOIN orderinfo.specimen specimen
        $query = $em->createQuery('
            SELECT orderinfo
            FROM OlegOrderformBundle:OrderInfo orderinfo
            INNER JOIN orderinfo.patient patient
            INNER JOIN orderinfo.specimen specimen
            INNER JOIN orderinfo.accession accession
            INNER JOIN orderinfo.part part
            INNER JOIN orderinfo.block block
            INNER JOIN orderinfo.slide slide
            WHERE orderinfo.id = :id
        ')->setParameter('id', $id);

        $entities = $query->getResult();

        //echo "<br>orderinfo count=".count( $entities )."<br>";

        if( count( $entities ) == 0 ) {
            throw $this->createNotFoundException('Unable to find entity.');
        } else {
            $entity = $entities[0];
        }

        //echo $entity;
        //echo $entity->getStatus();
        //echo "<br>specimen count=".count( $entity->getSpecimen() );

        //patient
        foreach( $entity->getPatient() as $patient ) {

//            echo "<br>patient order info count=".count( $patient->getOrderInfo() )."<br>";
            //check if patient has this orderinfo
            if( !$this->hasOrderInfo($patient,$id) ) {
                //echo "remove patient!!!! <br>";
                $entity->removePatient($patient);
                continue;
            }

//            echo "<br>patient has procedure count=".count( $patient->getSpecimen() )."<br>";

            //procdeure
            foreach( $patient->getSpecimen() as $specimen ) {

                if( !$this->hasOrderInfo($specimen,$id) ) {
                    $patient->removeSpecimen($specimen);
                    continue;
                }

                //accession
                foreach( $specimen->getAccession() as $accession ) {
                    if( !$this->hasOrderInfo($accession,$id) ) {
                        $specimen->removeAccession($accession);
                        continue;
                    }

                    //part
                    foreach( $accession->getPart() as $part ) {
                       if( !$this->hasOrderInfo($part,$id) ) {
                            $accession->removePart($part);
                            continue;
                        }

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

        //echo "<br>specimen count=".count( $entity->getSpecimen() );

        $disable = true;

        $request = $this->container->get('request');
        $routeName = $request->get('_route');

        //echo "route=".$routeName.", type=".$type."<br>";
        if( $type == "edit" || $routeName == "multy_edit") {
            $disable = false;
            $type = "edit";
        }

        //echo "show id=".$entity->getId()."<br>";
        $form   = $this->createForm( new OrderInfoType(true, null, $entity), $entity, array('disabled' => $disable) );

//        echo "type=".$entity->getType();
//        exit();

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

 
}
