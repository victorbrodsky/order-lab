<?php

namespace Oleg\UserdirectoryBundle\Controller;


use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Form\Extension\Core\DataTransformer\DateTimeToStringTransformer;

use Oleg\UserdirectoryBundle\Util\UserUtil;
use Oleg\OrderformBundle\Entity\Patient;
use Oleg\OrderformBundle\Entity\PatientMrn;


/**
 * @Route("/tree-util")
 */
class TreeController extends Controller {


    /**
     * @Route("/common/institution/", name="employees_get_institution_tree", options={"expose"=true})
     * @Method({"GET", "POST"})
     */
    public function getTreeAction(Request $request) {

        $id = trim( $request->get('id') );
        $level = trim( $request->get('level') );
        //echo "id=".$id."<br>";
        //echo "level=".$level."<br>";

        $em = $this->getDoctrine()->getManager();

        $repository = $this->getDoctrine()->getRepository('OlegUserdirectoryBundle:Institution');
        $dql =  $repository->createQueryBuilder("list");
        $dql->orderBy("list.orderinlist","ASC");

        $where = "(list.type = :typedef OR list.type = :typeadd)";
        $params = array('typedef' => 'default','typeadd' => 'user-added');

        if( $id != '#' && is_numeric($id) ) {
            //children: where parent = $id
            $dql->leftJoin("list.parent", "parent");
            $where = $where . " AND parent.id = :id";
            $params['id'] = $id;
        }

        if( $id == '#' || !is_numeric($id) ) {
            //root
            $where = $where . " AND list.level = :level";
            $params['level'] = 0;
        }

        if( $level && is_numeric($level) ) {
            //root
            $where = $where . " AND list.level = :level";
            $params['level'] = $level;
        }

        //$query->where($where)->setParameters($params);
        $dql->where($where);

        $query = $em->createQuery($dql);
        $query->setParameters($params);
        //echo "dql=".$dql."<br>";

        $entities = $query->getResult();
        //echo "count=".count($entities)."<br>";

//        $output = array(
//            'id' => 0,
//            'text' => "Institutions",
//        );

        $output = array();
        foreach( $entities as $entity ) {
            $element = array(
                'id'=>$entity->getId(),
                'text'=>$entity->getId().": ".$entity->getName()."",
                'children' => ( count($entity->getChildren()) > 0 ? true : false)
            );
            $output[] = $element;
        }
        //$output['children'] = array($children);

        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');
        $response->setContent(json_encode($output));
        return $response;
    }


    /**
     * @Route("/tree/rename", name="employees_tree_rename_node", options={"expose"=true})
     * @Method({"POST"})
     */
    public function setTreeAction(Request $request) {

        $id = trim( $request->get('id') );
        $level = trim( $request->get('level') );
        //echo "id=".$id."<br>";
        //echo "level=".$level."<br>";

        $em = $this->getDoctrine()->getManager();

        $repository = $this->getDoctrine()->getRepository('OlegUserdirectoryBundle:Institution');
        $dql =  $repository->createQueryBuilder("list");
        $dql->orderBy("list.orderinlist","ASC");

        $where = "(list.type = :typedef OR list.type = :typeadd)";
        $params = array('typedef' => 'default','typeadd' => 'user-added');

        if( $id != '#' && is_numeric($id) ) {
            //children: where parent = $id
            $dql->leftJoin("list.parent", "parent");
            $where = $where . " AND parent.id = :id";
            $params['id'] = $id;
        }

        if( $id == '#' || !is_numeric($id) ) {
            //root
            $where = $where . " AND list.level = :level";
            $params['level'] = 0;
        }

        if( $level && is_numeric($level) ) {
            //root
            $where = $where . " AND list.level = :level";
            $params['level'] = $level;
        }

        //$query->where($where)->setParameters($params);
        $dql->where($where);

        $query = $em->createQuery($dql);
        $query->setParameters($params);
        //echo "dql=".$dql."<br>";

        $entities = $query->getResult();
        //echo "count=".count($entities)."<br>";

//        $output = array(
//            'id' => 0,
//            'text' => "Institutions",
//        );

        $output = array();
        foreach( $entities as $entity ) {
            $element = array(
                'id'=>$entity->getId(),
                'text'=>$entity->getId().": ".$entity->getName()."",
                'children' => ( count($entity->getChildren()) > 0 ? true : false)
            );
            $output[] = $element;
        }
        //$output['children'] = array($children);

        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');
        $response->setContent(json_encode($output));
        return $response;
    }


}
