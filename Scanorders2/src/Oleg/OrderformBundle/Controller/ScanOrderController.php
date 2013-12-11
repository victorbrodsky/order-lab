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
use Oleg\OrderformBundle\Entity\Procedure;
use Oleg\OrderformBundle\Form\ProcedureType;
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
use Oleg\OrderformBundle\Entity\PatientClinicalHistory;
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

        //by user
        $user = $this->get('security.context')->getToken()->getUser();

        //create filters
        $form = $this->createForm(new FilterType( $this->getFilter(), $user ), null);
        $form->bind($request);

        $repository = $this->getDoctrine()->getRepository('OlegOrderformBundle:OrderInfo');
        $dql =  $repository->createQueryBuilder("orderinfo");
        //$dql->addSelect('orderinfo');
        //$dql->addSelect('COUNT(slides) as slidecount');
        //$dql->addGroupBy('orderinfo');
        $dql->select('orderinfo, COUNT(slides) as slidecount');
        $dql->groupBy('orderinfo');
        $dql->addGroupBy('orderinfo');
        $dql->addGroupBy('status.name');

        $dql->innerJoin("orderinfo.slide", "slides");

        $dql->innerJoin("orderinfo.provider", "provider");

        $search = $form->get('search')->getData();
        $filter = $form->get('filter')->getData();
        $service = $form->get('service')->getData();

        //service
        //echo "<br>service=".$service;
        //exit();

        $criteriastr = "";


        //***************** Pathology Service filetr ***************************//
        $showprovider = 'false';

        //service filter is existing pathology service in DB
        if( is_numeric($service)  ) {

            $userService = $user->getPathologyServices();

            if( !$userService ) {
                $this->get('session')->getFlashBag()->add(
                    'notice',
                    'You are not assign to any pathology service; All orders are shown.'
                );
            }

            $pathService = $em->getRepository('OlegOrderformBundle:PathServiceList')->find($service);

            if( $userService && $userService != ''  ) {
                if( $criteriastr != "" ) {
                    $criteriastr .= " AND ";
                }
                $criteriastr .= " orderinfo.pathologyService=".$pathService->getId();
            }
            $showprovider = 'true';
        } else {
            //this implemented below in "User filter"
        }
        //***************** END of Pathology Service filetr ***************************//


        //***************** Status filetr ***************************//
        $dql->innerJoin("orderinfo.status", "status");
        //echo "status filter = ".$filter."<br>";
        if( $filter && is_numeric($filter) && $filter > 0 ) {
            if( $criteriastr != "" ) {
                $criteriastr .= " AND ";
            }
            $criteriastr .= " status.id=" . $filter;
        }

        //filter special cases
        if( $filter && is_string($filter) && $filter != "All" ) {

            //echo "filter=".$filter;
            if( $criteriastr != "" ) {
                $criteriastr .= " AND ";
            }

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
        //***************** END of Status filetr ***************************//


        //***************** Search filetr ***************************//
        if( $search && $search != "" ) {
            if( $criteriastr != "" ) {
                $criteriastr .= " AND ";
            }
            $dql->innerJoin("orderinfo.accession", "accessionobj");
            $dql->innerJoin("accessionobj.accession", "accession");
            $criteriastr .= "accession.field LIKE '%" . $search . "%'";
            
        }
        //***************** END of Search filetr ***************************//


        //***************** User filter ***************************//
        $dql->innerJoin("orderinfo.proxyuser", "proxyuser");
        //show only my order if i'm not an admin and Pathology Services are not choosen
        if( false === $this->get('security.context')->isGranted('ROLE_ADMIN') && $service == 0 ) {
            //echo " role_user ";
            if( $criteriastr != "" ) {
                $criteriastr .= " AND ";
            }
            $criteriastr .= "( provider.id=".$user->getId();

            //***************** Proxy User Orders *************************//
            $criteriastr .= " OR proxyuser.id=".$user->getId()." )";
            //***************** END of Proxy User Orders *************************//
        }

        if( $service == "My Orders" ) {
            //show only my order if i'm not an admin and Pathology Services are not choosen
            //Orders I Personally Placed and Proxy Orders Placed For Me
            if( false === $this->get('security.context')->isGranted('ROLE_ADMIN') && $service == 0 ) {
                //echo " role_user ";
                if( $criteriastr != "" ) {
                    $criteriastr .= " AND ";
                }
                $criteriastr .= "( provider.id=".$user->getId();

                //***************** Proxy User Orders *************************//
                $criteriastr .= " OR proxyuser.id=".$user->getId()." )";
                //***************** END of Proxy User Orders *************************//
            }
        }
        if( $service == "Orders I Personally Placed" ) {
            if( false === $this->get('security.context')->isGranted('ROLE_ADMIN') && $service == 0 ) {
                //echo " role_user ";
                if( $criteriastr != "" ) {
                    $criteriastr .= " AND ";
                }
                $criteriastr .= "provider.id=".$user->getId();
            }
        }
        if( $service == "Proxy Orders Placed For Me" ) {
            if( false === $this->get('security.context')->isGranted('ROLE_ADMIN') && $service == 0 ) {
                //echo " role_user ";
                if( $criteriastr != "" ) {
                    $criteriastr .= " AND ";
                }
                //***************** Proxy User Orders *************************//
                $criteriastr .= "proxyuser.id=".$user->getId();
                //***************** END of Proxy User Orders *************************//
            }
        }
        //***************** END of User filetr ***************************//


        //echo "<br>criteriastr=".$criteriastr."<br>";
        
        if( $criteriastr != "" ) {
            //TODO: use ->setParameter(1, $caravan);
            $dql->where($criteriastr);
        }

        $params = $this->getRequest()->query->all();
        $sort = $this->getRequest()->query->get('sort');
        if( $params == null || count($params) == 0 ) {
            $dql->orderBy("orderinfo.id","DESC");
        }
        if( $sort != 'orderinfo.id' ) {
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
     * Displays a form to create a new OrderInfo + Scan entities.
     *
     * @Route("/TODEL", name="scanorder_new_TODEL")
     * @Method("GET")
     * @Template("OlegOrderformBundle:ScanOrder:new.html.twig")
     */
    public function newAction()
    {
        exit("single order controller???");
        if (false === $this->get('security.context')->isGranted('ROLE_USER')) {
            return $this->render('OlegOrderformBundle:Security:login.html.twig');
        }

        $em = $this->getDoctrine()->getManager();

        $entity = new OrderInfo();
        $user = $this->get('security.context')->getToken()->getUser();

        $username = $user->getUsername();

        $entity->addProvider($user);

        //get pathology service for this user by email
        $service = $user->getPathologyServices();

        $params = array('type'=>'single', 'cicle'=>'new', 'service'=>$service, 'user'=>$username, 'em'=>$em);
        $form   = $this->createForm( new OrderInfoType($params, $entity), $entity );

        $patient = new Patient(true);
//        $clinicalHistory = new ClinicalHistory();
//        $patient->addClinicalHistory($clinicalHistory);
        $form_patient   = $this->createForm(new PatientType($params), $patient);

        $procedure = new Procedure(true);

        $form_procedure = $this->createForm(new ProcedureType($params), $procedure);

        //$paper = new Document();
        //$form_paper = $this->createForm(new DocumentType(), $paper);

        $accession = new Accession(true);
        $form_accession   = $this->createForm(new AccessionType($params), $accession);

        $part = new Part(true);
        //$diffDiagnoses = new DiffDiagnoses();
        //$part->addDiffDiagnoses($diffDiagnoses);
        //$file = new Document();
        //$part->addPaper($file);
        //$part = $em->getRepository('OlegOrderformBundle:Part')->presetEntity( $part );
        $form_part   = $this->createForm(new PartType($params), $part);

        $block = new Block(true);
        $form_block   = $this->createForm(new BlockType($params), $block);

        $slide = new Slide(true);

        //$specialStains = new SpecialStains();
        //$relevantScans = new RelevantScans();
        //$slide->addRelevantScan($relevantScans);
        //$slide->addSpecialStain($specialStains);

        $form_slide   = $this->createForm(new SlideType($params), $slide);

//        $scan = new Scan();
//        $form_scan   = $this->createForm(new ScanType(), $scan);

//        $stain = new Stain();
//        $form_stain   = $this->createForm(new StainType($params), $stain);

        return array(
            'form' => $form->createView(),
            'form_patient' => $form_patient->createView(),
            'form_procedure' => $form_procedure->createView(),
            'form_accession' => $form_accession->createView(),
            'form_part' => $form_part->createView(),
            'form_block' => $form_block->createView(),
            'form_slide' => $form_slide->createView(),
//            'form_scan' => $form_scan->createView(),
//            'form_stain' => $form_stain->createView(),
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
            "All" => "All Statuses",
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
