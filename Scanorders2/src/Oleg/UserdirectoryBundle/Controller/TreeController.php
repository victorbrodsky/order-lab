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
     * Get tree structure by parent
     *
     * @Route("/common/composition-tree/", name="employees_get_composition_tree", options={"expose"=true})
     * @Method({"GET", "POST"})
     */
    public function getTreeByParentAction(Request $request) {

        $userid = trim( $request->get('userid') );
        $opt = trim( $request->get('opt') );
        $thisid = trim( $request->get('thisid') );
        $pid = trim( $request->get('id') );
        $className = trim( $request->get('classname') );
        $bundleName = trim( $request->get('bundlename') );
        //$level = trim( $request->get('pid') );
        //echo "pid=".$pid."<br>";
        //echo "level=".$level."<br>";

        $combobox = false;
        //$userpositions = false;

        if (strpos($opt,'combobox') !== false) {
            $combobox = true;
        }

//        if (strpos($opt,'userpositions') !== false) {
//            $userpositions = true;
//        }

        if( $thisid == 'null' ) {
            $thisid = null;
        }

        if( $pid == 'null' ) {
            $pid = null;
        }

        if( !$pid && $thisid == 0 ) {
            //echo "get root if thisid is 0 <br>";
            $pid = '0';
        }

        $em = $this->getDoctrine()->getManager();

        $mapper = $this->classMapper($bundleName,$className);
        $treeRepository = $em->getRepository($mapper['prefix'].$mapper['bundleName'].':'.$mapper['className']);

        $dql =  $treeRepository->createQueryBuilder("list");
        $dql->orderBy("list.lft","ASC");

        $where = "(list.type = :typedef OR list.type = :typeadd)";
        $params = array('typedef' => 'default','typeadd' => 'user-added');

        $addwhere = false;

        if( !$addwhere && $pid != null && is_numeric($pid) && $pid == 0 ) {
            //children: where parent is NULL => root
            //echo "by root pid=".$pid."<br>";
            $dql->leftJoin("list.parent", "parent");
            $where = $where . " AND parent.id is NULL";
            $addwhere = true;
        }

        if( !$addwhere && $pid && ($pid == '#' || !is_numeric($pid)) ) {
            //echo "by root pid=".$pid."<br>";
            //root: the same as $pid == 0
            //$where = $where . " AND list.level = :level";
            //$params['level'] = 0;
            $dql->leftJoin("list.parent", "parent");
            $where = $where . " AND parent.id is NULL";
            $addwhere = true;
        }

        if( !$addwhere && $pid && $pid != 0 && $pid != '#' && is_numeric($pid) ) {
            //children: where parent = $pid
            //echo "by pid=".$pid."<br>";
            $dql->leftJoin("list.parent", "parent");
            $where = $where . " AND parent.id = :id";
            $params['id'] = $pid;
            $addwhere = true;
        }

        if( !$addwhere && $pid == null && $thisid && $thisid != 0 && is_numeric($thisid) ) {
            //siblings
            //echo "by sibling id=".$thisid."<br>";
            $thisNode = $treeRepository->find($thisid);
            $parent = $thisNode->getParent();

            if( $parent ) {
                $pid = $parent->getId();
                $dql->leftJoin("list.parent", "parent");
                $where = $where . " AND parent.id = :id";
                $params['id'] = $pid;
            } else {
                $dql->leftJoin("list.parent", "parent");
                $where = $where . " AND parent.id is NULL";
            }
            $addwhere = true;
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

        $output = array();
        foreach( $entities as $entity ) {

            if( !$entity->getOrganizationalGroupType() ) {
                //echo "entity without org group = ".$entity->getId()."<br>";
                continue;
            }

            $levelTitle = $entity->getOrganizationalGroupType()->getName()."";

            if( $combobox ) {
                $text = $entity->getName()."";
            } else {
                $text = $entity->getName()." [" . $levelTitle . "]";
            }

            $element = array(
                'id' => $entity->getId(),
                'pid' => ($entity->getParent() ? $entity->getParent()->getId() : 0),
                //'text' => 'id:'.$entity->getId()." (".$entity->getLft()." ".$entity->getName()." ".$entity->getRgt().")",
                //'text' => $entity->getName()." [" . $levelTitle . "]",
                'text' => $text,
                'level' => $entity->getLevel(),
                'type' => $levelTitle,          //set js icon by level title
                'leveltitle' => $levelTitle,
                //'children' => ( count($entity->getChildren()) > 0 ? true : false)
            );

            if( $combobox ) {
                //for combobox

//                if( $userpositions ) {
//                    //find user positions by $userid and nodeid
//                    $nodeUserPositions = $em->getRepository('OlegUserdirectoryBundle:UserPosition')->findBy(
//                        array(
//                            'user' => $userid,
//                            'institution' => $entity->getId()
//                        )
//                    );
//
//                    $positiontypes = array();
//                    foreach( $nodeUserPositions as $nodeUserPosition ) {
//                        foreach( $nodeUserPosition->getPositionTypes() as $posType ) {
//                            $positiontypes[] = $posType->getId();
//                        }
//                    }
//
//                    $element['positiontypes'] = implode(",", $positiontypes);
//                }

            } else {
                //for jstree
                $element['children'] = ( count($entity->getChildren()) > 0 ? true : false);
            }

            $output[] = $element;
        }

        //construct an empty element for combobox to allow to enter a new node
        if( $combobox && count($output) == 0 ) {
            if( $pid != 0 && $pid != '#' && is_numeric($pid) ) {

                $parent = $treeRepository->find($pid);
                if( $parent ) {
                    $childLevel = intval($parent->getLevel()) + 1;
                } else {
                    $childLevel = 0;
                }

                $organizationalGroupTypes = $em->getRepository($mapper['prefix'].$mapper['bundleName'].':'.$mapper['organizationalGroupType'])->findBy(
                    array(
                        "level" => $childLevel,
                        "type" => array('default','user-added')
                    )
                );

                if( count($organizationalGroupTypes) > 0 ) {
                    $organizationalGroupType = $organizationalGroupTypes[0];
                }

                if( count($organizationalGroupTypes) == 0 ) {
                    $organizationalGroupType = null;
                }

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

            }//if
        }//if

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
        $bundleName = trim( $request->get('bundlename') );
        //echo "nodeid=".$nodeid."<br>";
        //echo "pid=".$pid."<br>";
        //echo "action=".$action."<br>";
        //echo "className=".$className."<br>";

        $combobox = false;
        //$userpositions = false;

        if (strpos($opt,'combobox') !== false) {
            $combobox = true;
        }

//        if (strpos($opt,'userpositions') !== false) {
//            $userpositions = true;
//        }

        $em = $this->getDoctrine()->getManager();

        $output = 'Failed: not supported action ' . $action;

        $mapper = $this->classMapper($bundleName,$className);

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

                if( $parent ) {
                    $parentLevel = $parent->getLevel();
                    $childLevel = intval($parentLevel) + 1;
                } else {
                    $childLevel = 0;
                }
                $organizationalGroupType = $em->getRepository($mapper['prefix'].$mapper['bundleName'].':'.$mapper['organizationalGroupType'])->findOneByLevel($childLevel);

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

                $fullClassName = "Oleg\\".$mapper['bundleName']."\\Entity\\".$mapper['className'];
                $node = new $fullClassName();
                $userutil = new UserUtil();
                $userutil->setDefaultList($node,$orderinlist,$username,$nodetext);
                $node->setOrganizationalGroupType($organizationalGroupType);
                if( $combobox ) {
                    $node->setType('user-added');
                }

                if( $parent ) {
                    $treeRepository->persistAsLastChildOf($node,$parent);
                } else {
                    $treeRepository->persistAsLastChild($node);
                }

                //$em->persist($node);
                $em->flush();

            }

            $output = $node->getId();

        } //create_node

        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');
        $response->setContent(json_encode($output));
        return $response;
    }



    public function classMapper($bundleName,$className) {

        $prefix = "Oleg";
        //$bundleName = "UserdirectoryBundle";
        $organizationalGroupType = "OrganizationalGroupType";

        switch( $className ) {
            case "Institution":
                $organizationalGroupType = "OrganizationalGroupType";
                break;
            case "CommentTypeList":
                $organizationalGroupType = "CommentGroupType";
                break;
            default:
                //$className = null;
        }

        $res = array(
            'prefix' => $prefix,
            'className' => $className,
            'bundleName' => $bundleName,
            'organizationalGroupType' => $organizationalGroupType
        );

        return $res;
    }

}
