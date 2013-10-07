<?php

namespace Oleg\OrderformBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
//use Symfony\Component\Security\Core\Exception\AccessDeniedException;

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
use Oleg\OrderformBundle\Form\ScanType;
use Oleg\OrderformBundle\Entity\Stain;
use Oleg\OrderformBundle\Form\StainType;
use Oleg\OrderformBundle\Form\FilterType;
use Oleg\OrderformBundle\Entity\Document;
use Oleg\OrderformBundle\Entity\DiffDiagnoses;
use Oleg\OrderformBundle\Entity\ClinicalHistory;
use Oleg\OrderformBundle\Entity\RelevantScans;
use Oleg\OrderformBundle\Entity\SpecialStains;
use Oleg\OrderformBundle\Form\DocumentType;

use Oleg\OrderformBundle\Helper\ErrorHelper;
use Oleg\OrderformBundle\Helper\FormHelper;
use Oleg\OrderformBundle\Helper\EmailUtil;

//ScanOrder joins OrderInfo + Scan
/**
 * OrderInfo controller.
 *
 * @Route("/")
 */
class ScanOrderController extends Controller {

    /**
     * Lists all OrderInfo entities.
     *
     * @Route("/index", name="index")
     * @Method("GET")
     * @Template()
     */
    public function indexAction( Request $request ) {
        
        if (false === $this->get('security.context')->isGranted('ROLE_USER')) {
            //throw new AccessDeniedException();
            return $this->render('OlegOrderformBundle:Security:login.html.twig');
        }
        
        $em = $this->getDoctrine()->getManager();

        //$statuses = $em->getRepository('OlegOrderformBundle:Status')->findAll();
        //$data = $qb->getArrayResult();
        //$statuses = $query_status->getResult();

//        $em = $this->getDoctrine()->getManager();
//        $query_status = $em->createQuery('SELECT s.name FROM OlegOrderformBundle:Status s');    //->setParameter('price', '19.99');
//        $statusesArr = $query_status->getResult();
//        $statuses = $this->array_column($statusesArr, 'name');
//        print_r($statuses);

        //create filters
        $form = $this->createForm(new FilterType( $this->getFilter() ), null);
        $form->bind($request);

        $repository = $this->getDoctrine()->getRepository('OlegOrderformBundle:OrderInfo');

        //by user
        $user = $this->get('security.context')->getToken()->getUser();
          
        $criteria = array();
        if( false === $this->get('security.context')->isGranted('ROLE_ADMIN') ) {    
            $criteria['provider'] = $user;
        }            
        
        $search = $form->get('search')->getData();
        $filter = $form->get('filter')->getData();
        $service = $form->get('service')->getData();

        //service
        //echo "filter=".$filter;
        //exit();

        $showprovider = 'false';
        if( $service && $service == 1  ) {
            $helper = new FormHelper();
            //$email = $this->get('security.context')->getToken()->getAttribute('email');
            $email = $user->getEmail();
            $userService = $helper->getUserPathology($email);

            if( !$userService ) {
                $this->get('session')->getFlashBag()->add(
                    'notice',
                    'You are not assign to any pathology service; All orders are shown.'
                );
            }

            //get id
            $query = $em->createQuery(
                'SELECT p FROM OlegOrderformBundle:PathServiceList p WHERE p.name LIKE :name'
            )->setParameter('name', '%'.$userService.'%');
            $pathService = $query->getResult();

            if( $userService && $userService != ''  ) {
                $criteria['pathologyService']= $pathService[0]->getId();
            }
            $showprovider = 'true';
        }

        $orderby = "";

        $params = $this->getRequest()->query->all();
        $sort = $this->getRequest()->query->get('sort');

        if( $params == null || count($params) == 0 ) {
            $orderby = " ORDER BY orderinfo.id DESC";
        }

        if( $sort != 'orderinfo.id' ) {
            $orderby = " ORDER BY orderinfo.id DESC";
        }

        $criteriastr = "";
        //print_r($criteria);

        $count = 0;
        foreach( $criteria as $key => $value ){
            $criteriastr .= "orderinfo." . $key . "='" . $value . "'";
            if( count($criteria) > $count+1 ) {
                $criteriastr .= " AND ";
            }
            $count++;
        }

        $dql =  $repository->createQueryBuilder("orderinfo");
        $dql->innerJoin("orderinfo.status", "status");

        //filter DB
        if( $filter && $filter > 0 ) {
//            $dql->innerJoin("orderinfo.status", "status");
            $criteriastr .= " status.id=" . $filter;
        }

        //filter special cases
        if( $filter && is_string($filter) ) {

//            $dql->innerJoin("orderinfo.status", "status");

            switch( $filter ) {

                case "All Filled":
                    $criteriastr .= " status.name LIKE '%Filled%'";
                    break;
                case "All Filled and Returned":
                    $criteriastr .= " status.name LIKE '%Filled%' AND status.name LIKE '%Returned%'";
                    break;
                case "All Filled and Not Returned":
                    $criteriastr .= " status.name LIKE '%Filled%' AND status.name NOT LIKE '%Returned%'";
                    break;
                case "All Not Filled":
                    $criteriastr .= " status.name NOT LIKE '%Filled%'";
                    break;
                case "All On Hold":
                    $criteriastr .= " status.name LIKE '%On Hold%'";
                    break;
                default:
                    ;
            }

        }

        $criteriafull = "";
        if( $search && $search != "" ) {
            $dql->innerJoin("orderinfo.accession", "accession");           
            $criteriafull = "accession.accession LIKE '%" . $search . "%'";    
            
        }              
        
        if( $criteriastr != "" ) {
            if( $criteriafull != "" ) {
                $criteriafull .= " AND ";
            }
            $criteriafull .= $criteriastr;           
        } 
        
        if( $criteriafull != "" ) {
            $dql->where($criteriafull);
        }
        
        if( $orderby != "" ) {
            $dql->orderBy("orderinfo.id","DESC");
        }

//        $dql->orderBy("status.name","DESC");
        
        //echo "dql=".$dql;
        
        $limit = 15;
        $query = $em->createQuery($dql);
        $paginator  = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $query,
            $this->get('request')->query->get('page', 1), /*page number*/
            $limit/*limit per page*/
        );

        //check for active user requests
        $reqs = array();
        if( $this->get('security.context')->isGranted('ROLE_ADMIN') ) {                     
            $reqs = $em->getRepository('OlegOrderformBundle:UserRequest')->findByStatus("active");
        }
        
        return array(
            'form' => $form->createView(),
            'showprovider' => $showprovider,
            'pagination' => $pagination,
            'userreqs' => $reqs
        );
    }
    
    /**
     * Creates a new OrderInfo entity.
     *
     * @Route("/", name="singleorder_create")
     * @Method("POST")
     * @Template("OlegOrderformBundle:ScanOrder:new.html.twig")
     */
    public function createAction(Request $request)
    {
        if (false === $this->get('security.context')->isGranted('ROLE_USER')) {
            return $this->render('OlegOrderformBundle:Security:login.html.twig');
        }

        $params = array('type'=>'single', 'cicle'=>'create', 'service'=>null);

//        echo "controller create=";
//        print_r($params);
//        echo "<br>";

        //echo "scanorder createAction";
        $entity  = new OrderInfo();
        $form = $this->createForm(new OrderInfoType($params,$entity), $entity);
        $form->bind($request);
              
        $patient = new Patient();      
        $form_patient = $this->createForm(new PatientType($params), $patient);
        $form_patient->bind($request);

        $procedure = new Specimen();
        $form_procedure = $this->createForm(new SpecimenType($params), $procedure);
        $form_procedure->bind($request);

//        $files = $this->getRequest()->files;
//        print_r($files);
//        $paper = $procedure->getPaper();
//        echo "PAPER=".$paper."<br>";
//        $paper = new Document();
//        $form_paper = $this->createForm(new DocumentType(), $paper);
//        $form_paper->bind($request);
        
        $accession = new Accession();
        $form_accession = $this->createForm(new AccessionType($params), $accession);
        $form_accession->bind($request);
        
        $part = new Part();
        $form_part = $this->createForm(new PartType($params), $part);
        $form_part->bind($request);
        
        $block = new Block();
        $form_block = $this->createForm(new BlockType($params), $block);
        $form_block->bind($request);

        $slide = new Slide();
        $form_slide = $this->createForm(new SlideType($params), $slide);
        $form_slide->bind($request);

        $scan = new Scan();
        $form_scan = $this->createForm(new ScanType(), $scan);
        $form_scan->bind($request);

        $stain = new Stain();
        $form_stain = $this->createForm(new StainType($params), $stain);
        $form_stain->bind($request);


        if(0) {
            $errorHelper = new ErrorHelper();
            $errors = $errorHelper->getErrorMessages($form);
            echo "<br>form errors:<br>";
            print_r($errors); 
            $errors = $errorHelper->getErrorMessages($form_patient);
            echo "<br>patient errors:<br>";
            print_r($errors); 
            $errors = $errorHelper->getErrorMessages($form_procedure);
            echo "<br>procedure errors:<br>";
            print_r($errors); 
            $errors = $errorHelper->getErrorMessages($form_accession);
            echo "<br>accession errors:<br>";
            print_r($errors);
            $errors = $errorHelper->getErrorMessages($form_part);
            echo "<br>part errors:<br>";
            print_r($errors);
            $errors = $errorHelper->getErrorMessages($form_block);
            echo "<br>block errors:<br>";
            print_r($errors);
            $errors = $errorHelper->getErrorMessages($form_slide);
            echo "<br>slide errors:<br>";
            print_r($errors);
            $errors = $errorHelper->getErrorMessages($form_scan);
            echo "<br>scan errors:<br>";
            print_r($errors);
            $errors = $errorHelper->getErrorMessages($form_stain);
            echo "<br>stain errors:<br>";
            print_r($errors);

        }
//            
//        echo "<br>stain type=".$slide->getStain()->getName()."<br>";
//        echo "scan mag=".$slide->getScan()->getMag()."<br>";        
        
        
        if( $form->isValid() && 
            $form_procedure->isValid() &&
            $form_accession->isValid() &&
            $form_part->isValid() &&    
            $form_block->isValid() &&
            $form_slide->isValid() &&
            $form_scan->isValid() &&
            $form_stain->isValid()
        ) {
            $em = $this->getDoctrine()->getManager();

            //echo $paper;
            //$paper->upload();
            //$em->persist($paper);
            //$em->flush();
            //exit();
                        
            $entity = $em->getRepository('OlegOrderformBundle:OrderInfo')->processEntity( $entity, "single" );
            
            //procedure/specimen: none
            //$procedure->addProcedure($accession);
            $patient = $em->getRepository('OlegOrderformBundle:Patient')->processEntity( $patient );                       
            $entity->addPatient($patient);
            //$em->persist($entity);          
            //$em->flush();
            
            $procedure = $em->getRepository('OlegOrderformBundle:Specimen')->processEntity( $procedure, $patient, array($accession) );
            $patient->addSpecimen($procedure);
            $entity->addSpecimen($procedure);

            //$procedure->setPaper($paper);
            //$em->persist($patient); 
            //$em->persist($procedure);
            //$em->flush();

            $accession = $em->getRepository('OlegOrderformBundle:Accession')->processEntity( $accession );
            $procedure->addAccession($accession);
            $entity->addAccession($accession);
            //$em->persist($accession);          
            //$em->flush();
            
            $part = $em->getRepository('OlegOrderformBundle:Part')->processEntity( $part, $accession );
            $accession->addPart($part);
            $entity->addPart($part);
            //$em->persist($part);          
            //$em->flush();
            
            $block = $em->getRepository('OlegOrderformBundle:Block')->processEntity( $block, $part );
            $part->addBlock($block);
            $entity->addBlock($block);
            //$em->persist($block);          
            //$em->flush();
            
            $slide = $em->getRepository('OlegOrderformBundle:Slide')->processEntity( $slide );
            //$em->getRepository('OlegOrderformBundle:Stain')->processEntity( $slide->getStain() );
            //$em->getRepository('OlegOrderformBundle:Scan')->processEntity( $slide->getScan() );
            //$accession->addSlide($slide); 
            //$part->addSlide($slide);
            $block->addSlide($slide);  
            $entity->addSlide($slide);

            $scan = $em->getRepository('OlegOrderformBundle:Scan')->processEntity( $scan );
            $slide->addScan($scan);
            $entity->addScan($scan);

            $stain = $em->getRepository('OlegOrderformBundle:Stain')->processEntity( $stain );
            $slide->addStain($stain);
            $entity->addStain($stain);

            $name = $form_stain["name"]->getData();
            
//            echo "stain name=".$name."<br>";
//            print_r($request);
//            echo $stain;
            
//            echo $entity;
//            echo $procedure;
//            echo "orderinfo proc count=".count($procedure->getOrderInfo())."<br>";
//            echo "proc count=".count($entity->getSpecimen())."<br>";
//            echo "orderinfo part count=".count($part->getOrderInfo())."<br>";
//            echo "part count=".count($entity->getPart())."<br>";
//            exit();

            $em->persist($entity);
            $em->flush();

            //$email = $this->get('security.context')->getToken()->getAttribute('email');
            $user = $this->get('security.context')->getToken()->getUser();
            $email = $user->getEmail();

            $emailUtil = new EmailUtil();
            $emailUtil->sendEmail( $email, $entity, null );
                      
            return $this->render('OlegOrderformBundle:ScanOrder:thanks.html.twig', array(
                'orderid' => $entity->getId(),
            ));
        }

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
            'form_patient'   => $form_patient->createView(),
            'form_procedure'   => $form_procedure->createView(),
            'form_accession'   => $form_accession->createView(),
            'form_part'   => $form_part->createView(),
            'form_block'   => $form_block->createView(),
            'form_slide'   => $form_slide->createView(),
            'form_stain'   => $form_stain->createView(),
            'form_scan'   => $form_scan->createView(),
        );
    }
    
    /**
     * Displays a form to create a new OrderInfo + Scan entities.
     *
     * @Route("/", name="scanorder_new")
     * @Method("GET")
     * @Template("OlegOrderformBundle:ScanOrder:new.html.twig")
     */
    public function newAction()
    {            
        
        if (false === $this->get('security.context')->isGranted('ROLE_USER')) {
            return $this->render('OlegOrderformBundle:Security:login.html.twig');
        }

        $em = $this->getDoctrine()->getManager();

        $entity = new OrderInfo();
        $user = $this->get('security.context')->getToken()->getUser();

        $username = $user->getUsername();
        $email = $user->getEmail();

        $provider = $username;
//        if( $user->getDisplayName() ) {
//            $provider = $username." - ".$user->getDisplayName();
//        }
        $entity->setProvider($provider);

        //get pathology service for this user by email
        $helper = new FormHelper();
        $service = $helper->getUserPathology($email);
        //$pathService = $em->getRepository('OlegOrderformBundle:PathServiceList')->findOneByName( $service );
        //$entity->setPathologyService($pathService);

        $params = array('type'=>'single', 'cicle'=>'new', 'service'=>$service, 'user'=>$username, 'em'=>$em);
        $form   = $this->createForm( new OrderInfoType($params, $entity), $entity );

        $patient = new Patient();
        $clinicalHistory = new ClinicalHistory();
        $patient->addClinicalHistory($clinicalHistory);
        $form_patient   = $this->createForm(new PatientType($params), $patient);

        $procedure = new Specimen();

        $form_procedure = $this->createForm(new SpecimenType($params), $procedure);

        //$paper = new Document();
        //$form_paper = $this->createForm(new DocumentType(), $paper);

        $accession = new Accession();
        $form_accession   = $this->createForm(new AccessionType($params), $accession);
         
        $part = new Part();
        $diffDiagnoses = new DiffDiagnoses();
        $part->addDiffDiagnoses($diffDiagnoses);
        $file = new Document();
        $part->addPaper($file);
        //$part = $em->getRepository('OlegOrderformBundle:Part')->presetEntity( $part );
        $form_part   = $this->createForm(new PartType($params), $part);
            
        $block = new Block();      
        $form_block   = $this->createForm(new BlockType($params), $block);
        
        $slide = new Slide();

        $specialStains = new SpecialStains();
        $relevantScans = new RelevantScans();
        $slide->addRelevantScan($relevantScans);
        $slide->addSpecialStain($specialStains);

        $form_slide   = $this->createForm(new SlideType($params), $slide);

        $scan = new Scan();
        $form_scan   = $this->createForm(new ScanType(), $scan);

        $stain = new Stain();
        $form_stain   = $this->createForm(new StainType($params), $stain);
        
        return array(          
            'form' => $form->createView(),
            'form_patient' => $form_patient->createView(),
            'form_procedure' => $form_procedure->createView(),
            'form_accession' => $form_accession->createView(),
            'form_part' => $form_part->createView(),
            'form_block' => $form_block->createView(),
            'form_slide' => $form_slide->createView(),
            'form_scan' => $form_scan->createView(),
            'form_stain' => $form_stain->createView(),
            //'form_paper' => $form_paper->createView()
        );
    }

    /**
     * Finds and displays a OrderInfo entity.
     *
     * @Route("/{id}", name="scanorder_show", requirements={"id" = "\d+"})
     * @Method("GET")
     * @Template("OlegOrderformBundle:ScanOrder:show.html.twig")
     */
    public function showAction($id)
    {
        
        if (false === $this->get('security.context')->isGranted('ROLE_USER')) {
            return $this->render('OlegOrderformBundle:Security:login.html.twig');
        }
        
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('OlegOrderformBundle:OrderInfo')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find OrderInfo entity.');
        }

        $showForm = $this->createForm(new OrderInfoType(null,$entity), $entity, array('disabled' => true));
        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            'edit_form'   => $showForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Displays a form to edit an existing OrderInfo entity.
     *
     * @Route("/{id}/edit", name="scanorder_edit", requirements={"id" = "\d+"})
     * @Method("GET")
     * @Template()
     */
    public function editAction($id)
    {
        
        if (false === $this->get('security.context')->isGranted('ROLE_USER')) {
            return $this->render('OlegOrderformBundle:Security:login.html.twig');
        }
        
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('OlegOrderformBundle:OrderInfo')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find OrderInfo entity.');
        }

        $editForm = $this->createForm(new OrderInfoType(null,$entity), $entity);
        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Edits an existing OrderInfo entity.
     *
     * @Route("/{id}", name="scanorder_update", requirements={"id" = "\d+"})
     * @Method("PUT")
     * @Template("OlegOrderformBundle:OrderInfo:edit.html.twig")
     */
    public function updateAction(Request $request, $id)
    {
        
        if (false === $this->get('security.context')->isGranted('ROLE_USER')) {
            return $this->render('OlegOrderformBundle:Security:login.html.twig');
        }
        
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('OlegOrderformBundle:OrderInfo')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find OrderInfo entity.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createForm(new OrderInfoType(null,$entity), $entity);
        $editForm->bind($request);

        if ($editForm->isValid()) {
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('scanorder_edit', array('id' => $id)));
        }

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }
    /**
     * Deletes a OrderInfo entity.
     *
     * @Route("/{id}", name="scanorder_delete", requirements={"id" = "\d+"})
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, $id)
    {
        
        if (false === $this->get('security.context')->isGranted('ROLE_USER')) {
            return $this->render('OlegOrderformBundle:Security:login.html.twig');
        }
        
        $form = $this->createDeleteForm($id);
        $form->bind($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('OlegOrderformBundle:OrderInfo')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find OrderInfo entity.');
            }
            
//            $scan_entities = $em->getRepository('OlegOrderformBundle:Scan')->
//                    findBy(array('scanorder_id'=>$id));
            
//            $scan_entities = $em->getRepository('OlegOrderformBundle:Scan')->findBy(
//                array('scanorder' => $id)            
//            );
            $entity->removeAllChildren();          
            
            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('scanorder'));
    }
    
    /**
     * @Route("/{id}/{status}/status", name="scanorder_status", requirements={"id" = "\d+"})
     * @Method("GET")
     * @Template()
     */
    public function statusAction($id, $status)
    {
        
        if (false === $this->get('security.context')->isGranted('ROLE_USER')) {
            return $this->render('OlegOrderformBundle:Security:login.html.twig');
        }
        
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('OlegOrderformBundle:OrderInfo')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find OrderInfo entity.');
        }

        //check if user permission
        
        //$editForm = $this->createForm(new OrderInfoType(), $entity);
        //$deleteForm = $this->createDeleteForm($id);
        
        //$entity->setStatus($status);
        //$status = $em->getRepository('OlegOrderformBundle:Status')->setStatus($status);
        $status_entity = $em->getRepository('OlegOrderformBundle:Status')->findOneByAction($status);

        if( $status_entity ) {

            $entity->setStatus($status_entity);
            $em->persist($entity);
            $em->flush();

            $this->get('session')->getFlashBag()->add(
                'notice',
                'Status of Order #'.$id.' has been changed to "'.$status.'"'
            );

        } else {

            $this->get('session')->getFlashBag()->add(
                'notice',
                'Status: "'.$status.'" is not found'
            );

        }

        return $this->redirect($this->generateUrl('index'));
    }

    /**
     * Creates a form to delete a OrderInfo entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id)
    {
        return $this->createFormBuilder(array('id' => $id))
            ->add('id', 'hidden')
            ->getForm()
        ;
    }
    
    
    /**   
     * @Route("/thanks", name="thanks")
     * 
     * @Template("OlegOrderformBundle:ScanOrder:thanks.html.twig")
     */
    public function thanksAction( $orderid = '' )
    {    
        
        return $this->render('OlegOrderformBundle:ScanOrder:thanks.html.twig',
            array(
                'orderid' => $orderid            
            ));
    }

    public function getFilter() {
        $em = $this->getDoctrine()->getManager();
        $statuses = $em->getRepository('OlegOrderformBundle:Status')->findAll();

        //add special cases
        $specials = array(
            "All" => "All",
            "All Filled" => "All Filled",
            "All Filled and Returned" => "All Filled and Returned",
            "All Filled and Not Returned" => "All Filled and Not Returned",
            "All Not Filled" => "All Not Filled",
            "All On Hold" => "All On Hold"
        );

        $filterType = array();
        foreach( $specials as $key => $value ) {
            $filterType[$key] = $value;
        }

        //add statuses
        foreach( $statuses as $status ) {
            //echo "type: id=".$status->getId().", name=".$status->getName()."<br>";
            $filterType[$status->getId()] = $status->getName();
        }

        return $filterType;
    }

}
