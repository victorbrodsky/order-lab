<?php

namespace Oleg\OrderformBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Oleg\OrderformBundle\Entity\OrderInfo;
use Oleg\OrderformBundle\Form\OrderInfoType;

use Oleg\OrderformBundle\Form\FilterType;
use Oleg\OrderformBundle\Entity\Document;
use Oleg\OrderformBundle\Helper\OrderUtil;


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

        //***************** Superseded filter ***************************//
        if( false === $this->get('security.context')->isGranted('ROLE_ADMIN') ) {
            //$superseded_status = $em->getRepository('OlegOrderformBundle:Status')->findOneByName('Superseded');
            $criteriastr .= " status.name != 'Superseded'";
        }
        //***************** END of Superseded filetr ***************************//


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
            $dql->orderBy("orderinfo.orderdate","DESC");
        }
        if( $sort != 'orderinfo.oid' ) {
            $dql->orderBy("orderinfo.orderdate","DESC");
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
    



//    //, requirements={"id" = "\d+"}
//    /**
//     * Finds and displays a OrderInfo entity.
//     *
//     * @Route("/{id}", name="scanorder_show")
//     * @Method("GET")
//     * @Template("OlegOrderformBundle:ScanOrder:show.html.twig")
//     */
//    public function showAction($id)
//    {
//
//        if (false === $this->get('security.context')->isGranted('ROLE_USER')) {
//            return $this->render('OlegOrderformBundle:Security:login.html.twig');
//        }
//
//        $em = $this->getDoctrine()->getManager();
//
//        $entity = $em->getRepository('OlegOrderformBundle:OrderInfo')->findByOid($id);
//
//        if (!$entity) {
//            throw $this->createNotFoundException('Unable to find OrderInfo entity.');
//        }
//
//        $showForm = $this->createForm(new OrderInfoType(null,$entity), $entity, array('disabled' => true));
//        $deleteForm = $this->createDeleteForm($id);
//
//        return array(
//            'entity'      => $entity,
//            'edit_form'   => $showForm->createView(),
//            'delete_form' => $deleteForm->createView(),
//        );
//    }

//    //requirements={"id" = "\d+"}
//    /**
//     * Displays a form to edit an existing OrderInfo entity.
//     *
//     * @Route("/{id}/edit", name="scanorder_edit")
//     * @Method("GET")
//     * @Template()
//     */
//    public function editAction($id)
//    {
//
//        if (false === $this->get('security.context')->isGranted('ROLE_USER')) {
//            return $this->render('OlegOrderformBundle:Security:login.html.twig');
//        }
//
//        $em = $this->getDoctrine()->getManager();
//
//        $entity = $em->getRepository('OlegOrderformBundle:OrderInfo')->findByOid($id);
//
//        if (!$entity) {
//            throw $this->createNotFoundException('Unable to find OrderInfo entity.');
//        }
//
//        $editForm = $this->createForm(new OrderInfoType(null,$entity), $entity);
//        $deleteForm = $this->createDeleteForm($id);
//
//        return array(
//            'entity'      => $entity,
//            'edit_form'   => $editForm->createView(),
//            'delete_form' => $deleteForm->createView(),
//        );
//    }

//    //requirements={"id" = "\d+"}
//    /**
//     * Edits an existing OrderInfo entity.
//     *
//     * @Route("/{id}", name="scanorder_update")
//     * @Method("PUT")
//     * @Template("OlegOrderformBundle:OrderInfo:edit.html.twig")
//     */
//    public function updateAction(Request $request, $id)
//    {
//
//        if (false === $this->get('security.context')->isGranted('ROLE_USER')) {
//            return $this->render('OlegOrderformBundle:Security:login.html.twig');
//        }
//
//        $em = $this->getDoctrine()->getManager();
//
//        $entity = $em->getRepository('OlegOrderformBundle:OrderInfo')->findByOid($id);
//
//        if (!$entity) {
//            throw $this->createNotFoundException('Unable to find OrderInfo entity.');
//        }
//
//        $deleteForm = $this->createDeleteForm($id);
//        $editForm = $this->createForm(new OrderInfoType(null,$entity), $entity);
//        $editForm->bind($request);
//
//        if ($editForm->isValid()) {
//            $em->persist($entity);
//            $em->flush();
//
//            return $this->redirect($this->generateUrl('scanorder_edit', array('id' => $id)));
//        }
//
//        return array(
//            'entity'      => $entity,
//            'edit_form'   => $editForm->createView(),
//            'delete_form' => $deleteForm->createView(),
//        );
//    }

    //requirements={"id" = "\d+"}
    /**
     * Deletes a OrderInfo entity.
     *
     * @Route("/{id}", name="scanorder_delete")
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
            $entity = $em->getRepository('OlegOrderformBundle:OrderInfo')->findOneByOid($id);

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

    //requirements={"id" = "\d+"}
    /**
     * @Route("/{id}/{status}/status", name="scanorder_status")
     * @Method("GET")
     * @Template()
     */
    public function statusAction($id, $status) {
        if (false === $this->get('security.context')->isGranted('ROLE_USER')) {
            return $this->render('OlegOrderformBundle:Security:login.html.twig');
        }
        
        $em = $this->getDoctrine()->getManager();

        $user = $this->get('security.context')->getToken()->getUser();

        $orderUtil = new OrderUtil($em);

        $res = $orderUtil->changeStatus($id, $status, $user);

        if( $res['result'] == 'conflict' ) {   //redirect to amend
            return $this->redirect( $this->generateUrl( 'order_amend', array('id' => $res['oid']) ) );
        }

        $this->get('session')->getFlashBag()->add('notice',$res['message']);

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
    public function thanksAction( $oid = '' )
    {    
        
        return $this->render('OlegOrderformBundle:ScanOrder:thanks.html.twig',
            array(
                'oid' => $oid
            ));
    }

    public function getFilter() {
        $em = $this->getDoctrine()->getManager();

        if( $this->get('security.context')->isGranted('ROLE_ADMIN') ) {
            $statuses = $em->getRepository('OlegOrderformBundle:Status')->findAll();
        } else {
            $repository = $this->getDoctrine()->getRepository('OlegOrderformBundle:Status');
            $dql = $repository->createQueryBuilder("status");
            //$dql->where('status.action IS NOT NULL');
            $dql->where("status.name != 'Superseded'");
            $statuses = $dql->getQuery()->getResult();
        }

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
