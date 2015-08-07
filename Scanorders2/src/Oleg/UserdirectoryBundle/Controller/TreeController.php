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
        //echo "userid=".$userid."<br>";

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

        //init where and params
        $where = "";
        $params = array();

//        if( $combobox ) {
//            $where = "(list.type = :typedef OR list.type = :typeadd)";
//            $params = array('typedef' => 'default','typeadd' => 'user-added');
//        }

        $addwhere = false;

        if( !$addwhere && $pid != null && is_numeric($pid) && $pid == 0 ) {
            //children: where parent is NULL => root
            //echo "by root pid=".$pid."<br>";
            $dql->leftJoin("list.parent", "parent");
            //$where = $where . " AND parent.id is NULL";
            $where = $this->addToWhere($where,"parent.id is NULL");
            $addwhere = true;
        }

        if( !$addwhere && $pid && ($pid == '#' || !is_numeric($pid)) ) {
            //echo "by root pid=".$pid."<br>";
            //root: the same as $pid == 0
            //$where = $where . " AND list.level = :level";
            //$params['level'] = 0;
            $dql->leftJoin("list.parent", "parent");
            //$where = $where . " AND parent.id is NULL";
            $where = $this->addToWhere($where,"parent.id is NULL");
            $addwhere = true;
        }

        if( !$addwhere && $pid && $pid != 0 && $pid != '#' && is_numeric($pid) ) {
            //children: where parent = $pid
            //echo "by pid=".$pid."<br>";
            $dql->leftJoin("list.parent", "parent");
            //$where = $where . " AND parent.id = :id";
            $where = $this->addToWhere($where,"parent.id = :id");
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
                //$where = $where . " AND parent.id = :id";
                $where = $this->addToWhere($where,"parent.id = :id");
                $params['id'] = $pid;
            } else {
                $dql->leftJoin("list.parent", "parent");
                //$where = $where . " AND parent.id is NULL";
                $where = $this->addToWhere($where,"parent.id is NULL");
            }
            $addwhere = true;
        }

        //$query->where($where)->setParameters($params);
        $dql->where($where);

        $query = $em->createQuery($dql);
        $query->setParameters($params);
        //echo "dql=".$dql."<br>";

        $entities = $query->getResult();
        //echo "count=".count($entities)."<br>";

        $levelTitles = null;
        if( count($entities) > 0 ) {
            $levelTitles = $treeRepository->getLevelLabels($entities[0],$mapper);
        }

        //add additional node. For example, attach users to each institution node
        $addNodeRepository = null;
        if( !$combobox && $mapper['addNodeClassName'] ) {
            $addNodeRepository = $em->getRepository($mapper['prefix'].$mapper['bundleName'].':'.$mapper['addNodeClassName']);
        }

        $output = array();
        foreach( $entities as $entity ) {

            if( $entity->getOrganizationalGroupType() ) {
                $levelTitle = $entity->getOrganizationalGroupType()->getName()."";
            } else {
                $levelTitle = $treeRepository->getDefaultLevelLabel($mapper,$entity->getLevel());
            }

            if( !$levelTitles ) {
                $levelTitles = $levelTitle;
            }

            if( $combobox ) {
                $text = $entity->getName()."";
            } else {
                $text = $entity->getName()." [" . $levelTitle . "]";
            }

            //disabled or not
            if( $entity->getType()."" == 'default' || $entity->getType()."" == 'user-added' ) {
                $optionDisabled = false;
            } else {
                $optionDisabled = true;
            }

            $element = array(
                'id' => $entity->getId(),
                'pid' => ($entity->getParent() ? $entity->getParent()->getId() : 0),
                //'text' => 'id:'.$entity->getId()." (".$entity->getLft()." ".$entity->getName()." ".$entity->getRgt().")",
                //'text' => $entity->getName()." [" . $levelTitle . "]",
                'text' => $text,
                'level' => $entity->getLevel(),
                'type' => 'icon'.$entity->getLevel(),            //set js icon by level title
                'leveltitle' => $levelTitles,                   //$levelTitle,
                'disabled' => $optionDisabled,
            );


            if( $combobox ) {
                //for combobox
            } else {
                //for jstree
                $addNodes = array();
                if( $addNodeRepository ) {
                    $addNodes = $addNodeRepository->findAllByInstitutionNodeAsUserArray($entity->getId());
                }

                $children = false;
                if( count($entity->getChildren()) > 0 ) {
                    $children = true;
                } else {
                    if( count($addNodes) > 0 ) {
                        $children = $addNodes;
                    }
                }

                $element['children'] = $children;    //( count($entity->getChildren()) > 0 ? true : false);
            }

//            if( $addNodeRepository ) {
//                $element['state'] = array("opened"=>false,"selected"=>false);
//            }

            $output[] = $element;
        }//foreach


        //additional nodes ie. users
        if( $addNodeRepository ) {

            if( $pid && $pid != 0 && $pid != '#' && is_numeric($pid) ) {

                $addNodes = $addNodeRepository->findAllByInstitutionNodeAsUserArray($pid);

                if( count($addNodes) > 0 ) {
                    $output = array_merge($output, $addNodes);
                }

            }//if
        }//if


        //construct an empty element for combobox to allow to enter a new node
        if( $combobox && count($output) == 0 ) {
            if( $pid != 0 && $pid != '#' && is_numeric($pid) ) {

                $parent = $treeRepository->find($pid);
                if( $parent ) {
                    $childLevel = intval($parent->getLevel()) + 1;
                } else {
                    $childLevel = 0;
                }

                $defaultOrganizationalGroupType = $treeRepository->getDefaultLevelLabel($mapper,$childLevel);

                if( $defaultOrganizationalGroupType ) {
                    $element = array(
                        'id' => null,
                        'pid' => $pid,
                        'text' => "",
                        'level' => $childLevel,
                        'type' => 'icon'.$childLevel,
                        'leveltitle' => $defaultOrganizationalGroupType,
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

                //add type for institution: Medical and Educational
                if( method_exists($node,'addType') && $mapper['className'] == 'Institution' && $childLevel == 0 ) {
                    $institutionMedicalType = $em->getRepository($mapper['prefix'].$mapper['bundleName'].':'.'InstitutionType')->findOneByName('Medical');
                    $node->addType($institutionMedicalType);
                    $institutionEducationalType = $em->getRepository($mapper['prefix'].$mapper['bundleName'].':'.'InstitutionType')->findOneByName('Educational');
                    $node->addType($institutionEducationalType);
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
        $addNodeClassName = null;

        switch( $className ) {
            case "Institution":
                $organizationalGroupType = "OrganizationalGroupType";
                break;
            case "CommentTypeList":
                $organizationalGroupType = "CommentGroupType";
                break;
            case "ProjectTitleTree":
                $organizationalGroupType = "ResearchGroupType";
                break;
            case "CourseTitleTree":
                $organizationalGroupType = "CourseGroupType";
                break;
            case "Institution_User":
                $organizationalGroupType = "OrganizationalGroupType";
                $className = "Institution";
                $addNodeClassName = "User";
                break;
            default:
                //$className = null;
        }

        $res = array(
            'prefix' => $prefix,
            'className' => $className,
            'bundleName' => $bundleName,
            'organizationalGroupType' => $organizationalGroupType,
            'addNodeClassName' => $addNodeClassName
        );

        return $res;
    }

    public function addToWhere( $where, $addwhere ) {
        if( $where != "" ) {
            $where = $where . " AND " . $addwhere;
        } else {
            $where = $addwhere;
        }

        return $where;
    }

}
