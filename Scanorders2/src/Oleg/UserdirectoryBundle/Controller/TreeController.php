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
                'id' => $entity->getId(),
                'text' => 'id:'.$entity->getId().", pos:".$entity->getOrderInList().": ".$entity->getName()."",
                //'text' => $entity->getName()."",
                'level' => $entity->getLevel(),
                'leveltype' => $entity->getOrganizationalGroupType()->getName()."",
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
     * @Route("/tree/action", name="employees_tree_edit_node", options={"expose"=true})
     * @Method({"POST"})
     */
    public function setTreeAction(Request $request) {

        $pid = trim( $request->get('pid') );
        $oldpid = trim( $request->get('oldpid') );
        $id = trim( $request->get('id') );
        $action = trim( $request->get('action') );
        $position = trim( $request->get('position') );
        $className = trim( $request->get('entity') );
        //echo "id=".$id."<br>";
        //echo "pid=".$pid."<br>";
        //echo "action=".$action."<br>";
        //echo "className=".$className."<br>";

        $em = $this->getDoctrine()->getManager();

        $output = 'not supported action ' . $action;

        $mapper = $this->classMapper($className);

        $node = $em->getRepository($mapper['prefix'].$mapper['bundleName'].':'.$mapper['className'])->find($id);

        if( $node && $action == 'rename_node' ) {
            if( $node->getName()."" != $position ) {
                $node->setName($position);
                $em->flush($node);
                $output = "ok";
            }
        }

        if( $node && $action == 'move_node' ) {

            if( $oldpid != $node->getParent()->getId() ) {
                //logic error if not the same
                throw new \Exception( 'Logic error: js old pid=' . $oldpid . ' is not the same as node pid=' .$node->getParent()->getId() );
            }

            $parent = $em->getRepository($mapper['prefix'].$mapper['bundleName'].':'.$mapper['className'])->find($pid);

            //change position only
            if( $oldpid == $pid ) {
                $this->setPositionAction($parent,$node,$position);
            }

            //move node to another parent
            if( $node->getParent()->getId() != $pid ) {
                if( $parent ) {
                    $node->setParent($parent);
                    $em->flush($node);
                    $output = "ok";
                } else {
                    $output = "parent node is not found";
                }
            }
        }

        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');
        $response->setContent(json_encode($output));
        return $response;
    }

    public function setPositionAction($parent,$node,$position) {
        $node->setPosition($position);
        //change positions of other children
    }

    public function classMapper($name) {

        $prefix = "Oleg";
        $bundleName = "UserdirectoryBundle";

        switch( $name ) {
            case "Institution":
                $className = "Institution";
                break;
            default:
                $className = null;
        }

        $res = array(
            'prefix' => $prefix,
            'className' => $className,
            'bundleName' => $bundleName
        );

        return $res;
    }

}
