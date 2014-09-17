<?php

namespace Oleg\UserdirectoryBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;


//TODO: optimise by removing foreach loops

/**
 * @Route("/util")
 */
class UtilController extends Controller {
      

//    /**
//     * @Route("/service", name="get-service")
//     * @Method("GET")
//     */
//    public function getServiceAction() {
//
//        $whereServicesList = "";
//
//        $em = $this->getDoctrine()->getManager();
//
//        $request = $this->get('request');
//        $opt = trim( $request->get('opt') );
//
//        $query = $em->createQueryBuilder()
//            ->from('OlegUserdirectoryBundle:Service', 'list')
//            ->select("list.id as id, list.name as text")
//            ->orderBy("list.orderinlist","ASC");
//
//        $user = $this->get('security.context')->getToken()->getUser();
//
//        if( $opt == 'default' ) {
//            if( $this->get('security.context')->isGranted('IS_AUTHENTICATED_FULLY') ) {
//                $query->where("list.type = 'default' OR ( list.type = 'user-added' AND list.creator = :user)")->setParameter('user',$user);
//            } else {
//                $query->where('list.type = :type ')->setParameter('type', 'default');
//            }
//        } else {
//            //find user's services to include them in the list
//            $user = $em->getRepository('OlegUserdirectoryBundle:User')->findOneById($opt);
//            $getServices = $user->getServices();	//TODO: user's or allowed services?
//
//            foreach( $getServices as $serviceId ) {
//                $whereServicesList = $whereServicesList . " OR list.id=".$serviceId->getId();
//            }
//            //$query->where('list.type = :type OR list.creator = :user_id ' . $whereServicesList)->setParameter('type', 'default')->setParameter('user_id', $opt);
//            $query->where("list.type = :type OR ( list.type = 'user-added' AND list.creator = :user_id) ".$whereServicesList)->setParameter('type', 'default')->setParameter('user_id', $opt);
//        }
//
//        $output = $query->getQuery()->getResult();
//
//        $response = new Response();
//        $response->headers->set('Content-Type', 'application/json');
//        $response->setContent(json_encode($output));
//        return $response;
//    }

    /**
     * @Route("/department", name="get-departments-by-parent")
     * @Route("/division", name="get-divisions-by-parent")
     * @Route("/service", name="get-services-by-parent")
     * @Method("GET")
     */
    public function getDepartmentAction() {

        $em = $this->getDoctrine()->getManager();

        $request = $this->get('request');
        $pid = trim( $request->get('pid') );

        $routeName = $request->get('_route');

        if( $routeName == "get-departments-by-parent") {
            $className = 'Department';
        }
        if( $routeName == "get-divisions-by-parent") {
            $className = 'Division';
        }
        if( $routeName == "get-services-by-parent") {
            $className = 'Service';
        }

        $query = $em->createQueryBuilder()
            ->from('OlegUserdirectoryBundle:'.$className, 'list')
            ->innerJoin("list.parent", "pid")
            ->select("list.id as id, list.name as text")
            ->orderBy("list.orderinlist","ASC");

        $user = $this->get('security.context')->getToken()->getUser();

        $query->where("pid = :pid AND (list.type = 'default' OR ( list.type = 'user-added' AND list.creator = :user))")->setParameters(array('user'=>$user,'pid'=>$pid));

        $output = $query->getQuery()->getResult();

        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');
        $response->setContent(json_encode($output));
        return $response;
    }

    /**
     * @Route("/institution", name="employees_get_institution")
     * @Method("GET")
     */
    public function getInstitutionAction() {

        $whereServicesList = "";

        $em = $this->getDoctrine()->getManager();

        $request = $this->get('request');
        $opt = trim( $request->get('opt') );

        $query = $em->createQueryBuilder()
            ->from('OlegUserdirectoryBundle:Institution', 'list')
            ->select("list.id as id, list.name as text")
            ->orderBy("list.orderinlist","ASC");

        $user = $this->get('security.context')->getToken()->getUser();

        $query->where("list.type = 'default' OR ( list.type = 'user-added' AND list.creator = :user)")->setParameter('user',$user);

        $output = $query->getQuery()->getResult();

        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');
        $response->setContent(json_encode($output));
        return $response;
    }

    //search if $needle exists in array $products
    public function in_complex_array($needle,$products,$indexstr='text') {
        foreach( $products as $product ) {
            if ( $product[$indexstr] === $needle ) {
                return true;
            }
        }
        return false;
    }

}
