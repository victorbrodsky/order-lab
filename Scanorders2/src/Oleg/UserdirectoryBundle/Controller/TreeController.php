<?php

namespace Oleg\UserdirectoryBundle\Controller;


use Oleg\UserdirectoryBundle\Entity\Institution;
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
     * Get tree structure by parent
     *
     * @Route("/common/composition-tree/", name="employees_get_composition_tree", options={"expose"=true})
     * @Method({"GET", "POST"})
     */
    public function getTreeByParentAction(Request $request) {

        $opt = trim( $request->get('opt') );
        $thisid = trim( $request->get('thisid') );
        $pid = trim( $request->get('id') );
        $className = trim( $request->get('classname') );
        //$level = trim( $request->get('pid') );
        //echo "pid=".$pid."<br>";
        //echo "level=".$level."<br>";

        if( $thisid == 'null' ) {
            $thisid = null;
        }

        if( $pid == 'null' ) {
            $pid = null;
        }

        $em = $this->getDoctrine()->getManager();

        //$repository = $this->getDoctrine()->getRepository('OlegUserdirectoryBundle:Institution');
        $mapper = $this->classMapper($className);
        $treeRepository = $em->getRepository($mapper['prefix'].$mapper['bundleName'].':'.$mapper['className']);

        $dql =  $treeRepository->createQueryBuilder("list");
        $dql->orderBy("list.lft","ASC");

        $where = "(list.type = :typedef OR list.type = :typeadd)";
        $params = array('typedef' => 'default','typeadd' => 'user-added');


        if( $pid && is_numeric($pid) && $pid == 0 ) {
            //children: where parent = $pid
            $dql->leftJoin("list.parent", "parent");
            $where = $where . " AND parent.id is NULL";
        }

        if( $pid && ($pid == '#' || !is_numeric($pid)) ) {
            //echo "by root pid=".$pid."<br>";
            //root: the same as $pid == 0
            //$where = $where . " AND list.level = :level";
            //$params['level'] = 0;
            $dql->leftJoin("list.parent", "parent");
            $where = $where . " AND parent.id is NULL";
        }

        if( $pid && $pid != 0 && $pid != '#' && is_numeric($pid) ) {
            //children: where parent = $pid
            //echo "by pid=".$pid."<br>";
            $dql->leftJoin("list.parent", "parent");
            $where = $where . " AND parent.id = :id";
            $params['id'] = $pid;
        }

        if( $pid == null && $thisid && $thisid != 0 && is_numeric($thisid) ) {
            //siblings
            //echo "by sibling id=".$thisid."<br>";
            $thisNode = $treeRepository->find($thisid);
            $parent = $thisNode->getParent();
            if( $parent ) {
                $pid = $parent->getId();
                $dql->leftJoin("list.parent", "parent");
                $where = $where . " AND parent.id = :id";
                $params['id'] = $pid;
            }
        }

//        if( $level && is_numeric($level) ) {
//            //root
//            $where = $where . " AND list.level = :level";
//            $params['level'] = $level;
//        }

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
            $levelTitle = $entity->getOrganizationalGroupType()->getName()."";
            $element = array(
                'id' => $entity->getId(),
                'pid' => ($entity->getParent() ? $entity->getParent()->getId() : 0),
                //'text' => 'id:'.$entity->getId()." (".$entity->getLft()." ".$entity->getName()." ".$entity->getRgt().")",
                'text' => $entity->getName()." [" . $levelTitle . "]",
                'level' => $entity->getLevel(),
                'type' => $levelTitle,          //set js icon by level title
                'leveltitle' => $levelTitle,
                //'children' => ( count($entity->getChildren()) > 0 ? true : false)
            );

            if( $opt != 'combobox' ) {
                $element['children'] = ( count($entity->getChildren()) > 0 ? true : false);
            }

            $output[] = $element;
        }

        //construct an empty element for combobox to allow to enter a new node
        if( $opt == 'combobox' && count($output) == 0 ) {
            if( $pid != 0 && $pid != '#' && is_numeric($pid) ) {
                $parent = $treeRepository->find($pid);
                $childLevel = intval($parent->getLevel()) + 1;
                $organizationalGroupType = $em->getRepository('OlegUserdirectoryBundle:OrganizationalGroupType')->findOneByLevel($childLevel);
                if( $organizationalGroupType ) {
                    $levelTitle = $organizationalGroupType->getName();
                    $element = array(
                        'id' => null,
                        'pid' => $pid,
                        'text' => "",
                        'level' => $childLevel,
                        'type' => $levelTitle,          //set js icon by level title
                        'leveltitle' => $levelTitle,
                    );
                    $output[] = $element;
                }
            }
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

        $opt = trim( $request->get('opt') );

        $pid = trim( $request->get('pid') );
        $position = trim( $request->get('position') );

        $oldpid = trim( $request->get('oldpid') );
        $oldposition = trim( $request->get('oldposition') );

        $nodeid = trim( $request->get('nodeid') );
        $nodetext = trim( $request->get('nodetext') );

        $action = trim( $request->get('action') );
        //$action = 'none'; //testing
        $className = trim( $request->get('classname') );
        //echo "nodeid=".$nodeid."<br>";
        //echo "pid=".$pid."<br>";
        //echo "action=".$action."<br>";
        //echo "className=".$className."<br>";

        $em = $this->getDoctrine()->getManager();

        $output = 'Failed: not supported action ' . $action;

        $mapper = $this->classMapper($className);

        $treeRepository = $em->getRepository($mapper['prefix'].$mapper['bundleName'].':'.$mapper['className']);

        $node = $treeRepository->find($nodeid);

        if( $node && $action == 'rename_node' ) {
            if( $node->getName()."" != $nodetext ) {
                $node->setName($nodetext);
                $em->flush($node);
                $output = "ok";
            }
        } //rename_node

        if( $node && $action == 'move_node' ) {

            //if( $oldpid != $node->getParent()->getId() ) {
                //logic error if not the same
                //throw new \Exception( 'Logic error: js old pid=' . $oldpid . ' is not the same as node pid=' .$node->getParent()->getId() );
            //}

            $parent = $treeRepository->find($pid);

            if( $parent ) {

                if( $position == 0 ) {
                    $treeRepository->persistAsFirstChildOf($node, $parent);
                } else
                if( intval($position)+1 == $treeRepository->childCount($parent,true) ) {
                    $treeRepository->persistAsLastChildOf($node, $parent);
                } else {
                    $currentSibling = $treeRepository->findChildAtPosition($parent,$position);
                    if( $currentSibling ) {
                        $treeRepository->persistAsPrevSiblingOf($node, $currentSibling);
                        //$node->setParent($parent);
                    } else {
                        echo "logical error! ";
                        $treeRepository->persistAsFirstChildOf($node, $parent);
                    }
                }

                $em->flush($node);
                $output = "ok";

            } else {
                $output = "Failed: parent node is not found";
            }

        } //move_node


        if( $action == 'create_node' ) {

            //check if already exists in DB by $nodetext and $pid
            $nodes = $treeRepository->findBy(array('parent'=>$pid,'name'=>$nodetext));

            if( $nodes && count($nodes) > 0 ) {
                $node = $nodes[0];
            } else {
                $node = null;
            }

            if( !$node ) {

                $username = $this->get('security.context')->getToken()->getUser();
                $parent = $treeRepository->find($pid);
                $parentLevel = $parent->getLevel();
                $childLevel = intval($parentLevel) + 1;
                $organizationalGroupType = $em->getRepository('OlegUserdirectoryBundle:OrganizationalGroupType')->findOneByLevel($childLevel);

                //////////// get max ordeinlist ////////////////////
                $query = $treeRepository->createQueryBuilder('s');
                $query->select('s, MAX(s.orderinlist) AS max_orderinlist');
                $query->groupBy('s');
                $query->setMaxResults(1);
                $query->orderBy('max_orderinlist', 'DESC');
                $results = $query->getQuery()->getResult();
                if( $results && count($results) > 0 ) {
                    $orderinlist = $results[0]['max_orderinlist'];
                    $orderinlist = intval($orderinlist) + 10;
                } else {
                    $orderinlist = 10;
                }
                //////////// EOF get max ordeinlist ////////////////////

                $node = new Institution();
                $userutil = new UserUtil();
                $userutil->setDefaultList($node,$orderinlist,$username,$nodetext);
                $node->setOrganizationalGroupType($organizationalGroupType);
                if( $opt == 'combobox' ) {
                    $node->setType('user-added');
                }
                $treeRepository->persistAsLastChildOf($node,$parent);

                $em->persist($node);
                $em->flush();

            }

            $output = $node->getId();

        } //create_node

        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');
        $response->setContent(json_encode($output));
        return $response;
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
