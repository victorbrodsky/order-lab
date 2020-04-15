<?php
/**
 * Copyright (c) 2017 Cornell University
 *
 *  Licensed under the Apache License, Version 2.0 (the "License");
 *  you may not use this file except in compliance with the License.
 *  You may obtain a copy of the License at
 *
 *  http://www.apache.org/licenses/LICENSE-2.0
 *
 *  Unless required by applicable law or agreed to in writing, software
 *  distributed under the License is distributed on an "AS IS" BASIS,
 *  WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 *  See the License for the specific language governing permissions and
 *  limitations under the License.
 */

namespace App\UserdirectoryBundle\Controller;


use App\UserdirectoryBundle\Form\HierarchyFilterType;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use App\UserdirectoryBundle\Controller\OrderAbstractController;
//use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
//use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Form\Extension\Core\DataTransformer\DateTimeToStringTransformer;

use App\UserdirectoryBundle\Util\UserUtil;
use App\OrderformBundle\Entity\Patient;
use App\OrderformBundle\Entity\PatientMrn;


/**
 * @Route("/tree-util")
 */
class TreeController extends OrderAbstractController {


    /**
     * Get tree structure by parent
     *
     * @Route("/common/composition-tree/", name="employees_get_composition_tree", methods={"GET","POST"}, options={"expose"=true})
     */
    public function getTreeByParentAction(Request $request) {
       
        
        $userid = trim( $request->get('userid') );
        $opt = trim( $request->get('opt') );
        $thisid = trim( $request->get('thisid') );
        $pid = trim( $request->get('id') );
        $className = trim( $request->get('classname') );
        $bundleName = trim( $request->get('bundlename') );
        $type = trim( $request->get('type') ); //user-added or default or undefined
        $entityIds = trim( $request->get('entityIds') );
        $orderformtype = trim( $request->get('orderformtype') ); //similar to sitename (calllog, crn, single, multi, transres ...)
        //$level = trim( $request->get('pid') );
        //echo "pid=".$pid."<br>";
        //echo "level=".$level."<br>";
        //echo "userid=".$userid."<br>";
        //echo "entityIds=".$entityIds."<br>";

        //get filter params
        //$filter = trim( $request->get('types') );
        $filterform = $this->createForm(HierarchyFilterType::class,null,array('form_custom_value'=>null));

        $formname = $filterform->getName();
        $formData = $request->query->get($formname);

        $typesFilter = NULL;
        if( is_array($formData) ) {
            if( array_key_exists('types',$formData) ) {
                $typesFilter = $formData['types'];
            }
        }
        //print_r($typesFilter);

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

        if( $userid == 'null' ) {
            $userid = null;
        }

        if( !$pid && $thisid == 0 ) {
            //echo "get root if thisid is 0 <br>";
            $pid = '0';
        }

        $em = $this->getDoctrine()->getManager();

        $mapper = $this->classMapper($bundleName,$className);
        $treeRepository = $em->getRepository($mapper['prefix'].$mapper['bundleName'].':'.$mapper['className']);

        $dql =  $treeRepository->createQueryBuilder("list");
        //$dql->orderBy("list.lft","ASC");
        $dql->orderBy("list.orderinlist","ASC");

        //init where and params
        $where = "";
        $params = array();


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
            if ( strval($pid) == strval(intval($pid)) ) {
                //children: where parent = $pid
                //echo "by pid=".$pid."<br>";
                $dql->leftJoin("list.parent", "parent");
                //$where = $where . " AND parent.id = :id";
                $where = $this->addToWhere($where, "parent.id = :id");
                $params['id'] = $pid;
                $addwhere = true;
            }
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

//        if( $combobox ) {
//            $where = "(list.type = :typedef OR list.type = :typeadd)";
//            $params = array('typedef' => 'default','typeadd' => 'user-added');
//        }

        //process ajax type
        //echo "type=".$type."<br>";
        if( $type ) {
            //http://stackoverflow.com/questions/5929036/how-to-use-where-in-with-doctrine-2
            //This works per default with ->setParameter('ids', $ids) but not with ->setParameters('ids' => $ids)
            $typeArr = explode(",",$type);
            $typesStr = '(list.type IN (:types))'; //'.implode(',',$typesArr).'
            $params['types'] = $typeArr;

            if( $userid ) {
                if ($thisid) {
                    $where = $this->addToWhere($where, "(list.id=:thisid OR $typesStr OR ( list.type = 'user-added' AND list.creator = :user))");
                    $params['thisid'] = $thisid;
                } else {
                    $where = $this->addToWhere($where, "($typesStr OR ( list.type = 'user-added' AND list.creator = :user))");
                }
                $params['user'] = $userid;
                //$params['type'] = $type;
            } else {
                if ($thisid) {
                    $where = $this->addToWhere($where, "(list.id=:thisid OR $typesStr)");
                    $params['thisid'] = $thisid;
                } else {
                    $where = $this->addToWhere($where, "($typesStr)");
                }
                //$params['type'] = $type;
            }
        }

        //$query->where($where)->setParameters($params);
        $dql->where($where);

        if( $typesFilter ) {
//            foreach( $typesFilter as $type ) {
//                $typesFilterArr[] = "'".$type."'";
//            }
//            $dql->andWhere('list.type IN ('.implode(',',$typesFilterArr).')');
            $dql->andWhere('list.type IN (:filterTypes)');
            $params['filterTypes'] = $typesFilter;
        }

        //$entityIds
        if( $entityIds ) {
            $dql->andWhere('list.id IN (:entityIds)');
            $entityIdsArr = explode(",",$entityIds);
            $params['entityIds'] = $entityIdsArr;
        }

        if( $orderformtype ) {
            if( $orderformtype == 'calllog' ) {
                $dql->andWhere('list.name != :exceptName');
                $params['exceptName'] = "Critical Result Notification Entry";
            }
            if( $orderformtype == 'crn' ) {
                $dql->andWhere('list.name != :exceptName');
                $params['exceptName'] = "Pathology Call Log Entry";
            }
        }

        $query = $em->createQuery($dql);
        //$query->setParameters($params);
        foreach( $params as $key=>$value) {
            $query->setParameter($key, $value);
            //if( $key == 'types' ) {
            //    $query->setParameter($key, $value, \Doctrine\DBAL\Connection::PARAM_STR_ARRAY);
            //} else {
            //    $query->setParameter($key, $value);
            //}
        }

        //echo "dql=".$dql." <br>";

        $entities = $query->getResult();

        //testing
//        echo "count=".count($entities)."<br>";
//        foreach( $entities as $entity ) {
//            echo "entity=".$entity->getName()." [ID#".$entity->getId()."]<br>";
//        }
//        exit('exit employees_get_composition_tree');

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

            if( $entity && method_exists($entity, 'hasInstitutionType') && $entity->hasInstitutionType("Collaboration") ) {
                $levelTitle = "Collaboration";  // . " (Level " . $entity->getLevel() . ")";
            } else {

                if( $mapper && array_key_exists('organizationalGroupType', $mapper) && $mapper['organizationalGroupType'] && $entity->getOrganizationalGroupType() ) {
                    $levelTitle = $entity->getOrganizationalGroupType()->getName() . "";
                } else {
                    //$levelTitle = $treeRepository->getDefaultLevelLabel($mapper, $entity->getLevel());
                    $levelTitle = $treeRepository->getLevelLabels($entity,$mapper);
                }
            }

            if( !$levelTitles ) {
                $levelTitles = $levelTitle;
            }

            if( $combobox ) {
                $text = $entity->getName()."";
            } else {
                $text = $entity->getName()." [" . $levelTitle . "]";
            }

            if( $mapper['className'] == "FormNode" ) {
                $text = $text . " " . "v." . $entity->getVersion();
                $text = $text . " (" . "ID:" . $entity->getId() . "; order: " . $entity->getOrderinlist() . ")";
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
                'leveltitle' => $levelTitles,   //we might have different level names                //$levelTitle,
                'disabled' => $optionDisabled,
            );


            if( $combobox ) {
                //for combobox
            } else {
                //for jstree
                $addNodes = array();
                if( $addNodeRepository ) {
                    $addNodes = $addNodeRepository->findAllByInstitutionNodeAsUserArray($entity->getId(),true);
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

            $output[] = $element;
        }//foreach


        //additional nodes ie. users
        if( $addNodeRepository ) {
            if( $pid && $pid != 0 && $pid != '#' && is_numeric($pid) ) {
                $addNodes = $addNodeRepository->findAllByInstitutionNodeAsUserArray($pid,true);
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

                if( $parent && method_exists($parent,'hasInstitutionType')  && $parent->hasInstitutionType("Collaboration") ) {
                    //$collaborationLevel = $parent->getLevel() + 1;
                    $defaultOrganizationalGroupType = "Collaboration";// . " (Level " . $collaborationLevel . ")";
                }

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
     * @Route("/tree/action", name="employees_tree_edit_node", methods={"POST"}, options={"expose"=true})
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

        if( $nodeid ) {
            //postgres does not accept empty id
            $node = $treeRepository->find($nodeid);
        }

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
                        //echo "logical error! ";
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

                $username = $this->get('security.token_storage')->getToken()->getUser();
                $parent = $treeRepository->find($pid);

                if( $parent ) {
                    $parentLevel = $parent->getLevel();
                    $childLevel = intval($parentLevel) + 1;
                } else {
                    $childLevel = 0;
                }

                if( $mapper['organizationalGroupType'] ) {
                    $organizationalGroupType = $em->getRepository($mapper['prefix'] . $mapper['bundleName'] . ':' . $mapper['organizationalGroupType'])->findOneByLevel($childLevel);
                } else {
                    $organizationalGroupType = NULL;
                }

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

                $fullClassName = "App\\".$mapper['bundleName']."\\Entity\\".$mapper['className'];
                $node = new $fullClassName();
                $userSecUtil = $this->get('user_security_utility');
                $userSecUtil->setDefaultList($node,$orderinlist,$username,$nodetext);

                if( $organizationalGroupType ) {
                    $node->setOrganizationalGroupType($organizationalGroupType);
                }

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


    /**
     * @Route("/tree/create-root/", name="employees_tree_create_root", methods={"GET","POST"}, options={"expose"=true})
     */
    public function createTopLevelRoot(Request $request) {
//        if( false === $this->get('security.authorization_checker')->isGranted('ROLE_USERDIRECTORY_EDITOR') ) {
//            return $this->redirect( $this->generateUrl($this->getParameter('employees.sitename').'-order-nopermission') );
//        }

        //$cycle = "new";
        $user = $this->get('security.token_storage')->getToken()->getUser();

        $data = $request->request->all();
        print_r($data);

        $className = $data['entityName'];
        $bundleName = $data['bundleName'];
        $routename = $data['routename'];
        $sitename = $data['sitename'];
        $rootNodeName = $data['rootNodeName'];

        $em = $this->getDoctrine()->getManager();

        $mapper = $this->classMapper($bundleName,$className);
        //$treeRepository = $em->getRepository($mapper['prefix'].$mapper['bundleName'].':'.$mapper['className']);

        $fullClassName = "App\\".$mapper['bundleName']."\\Entity\\".$mapper['className'];
        $root = new $fullClassName();
        $userSecUtil = $this->get('user_security_utility');
        $userSecUtil->setDefaultList($root,1,$user,$rootNodeName);


        if( $mapper['organizationalGroupType'] ) {
            $organizationalGroupType = $em->getRepository($mapper['prefix'] . $mapper['bundleName'] . ':' . $mapper['organizationalGroupType'])->findOneByLevel(0);
        } else {
            $organizationalGroupType = NULL;
        }

        if( $organizationalGroupType ) {
            $root->setOrganizationalGroupType($organizationalGroupType);
        }

        //add type for institution: Medical and Educational
        if( method_exists($root,'addType') && $mapper['className'] == 'Institution' ) {
            $institutionMedicalType = $em->getRepository($mapper['prefix'].$mapper['bundleName'].':'.'InstitutionType')->findOneByName('Medical');
            $root->addType($institutionMedicalType);
            $institutionEducationalType = $em->getRepository($mapper['prefix'].$mapper['bundleName'].':'.'InstitutionType')->findOneByName('Educational');
            $root->addType($institutionEducationalType);
        }

        $root->setType('user-added');

        //exit("createTopLevelRoot");

        $em->persist($root);
        $em->flush();

        return $this->redirectToRoute($routename);
        
//        $form = $this->createForm('CompositeTreeRootType', array(
//            'disabled' => $disabled,
//            'form_custom_value' => $params
//        ));
//
//        $form->handleRequest($request);
//
//        if ($form->isSubmitted() && $form->isValid()) {
//
//            $transresUtil->processDefaultReviewersRole($defaultReviewer,$originalReviewer,$originalReviewerDelegate);
//
//            $this->getDoctrine()->getManager()->flush();
//
//            //Event Log
//            $eventType = "Default Reviewer Updated";
//            $reviewersArr = $transresUtil->getCurrentReviewersEmails($defaultReviewer,false);
//            $reviewer = $reviewersArr['reviewer'];
//            $reviewerDelegate = $reviewersArr['reviewerDelegate'];
//            $stateStr = $defaultReviewer->getState();
//            //get state string: irb_review=>IRB Review
//            $stateLabel = $transresUtil->getStateSimpleLabelByName($stateStr);
//            $specialtyStr = $defaultReviewer->getProjectSpecialty();
//            $msg = "Default Reviewer Object ($stateLabel, $specialtyStr) has been updated:"; //with reviewer=".$reviewer . " ; reviewerDelegate=".$reviewerDelegate;
//            $msg = $msg . "<br>Original reviewer=".$originalReviewer.";<br> New reviewer=".$reviewer;
//            $msg = $msg . "<br>Original reviewerDelegate=".$originalReviewerDelegate.";<br> New reviewerDelegate=".$reviewerDelegate;
//            $transresUtil->setEventLog($defaultReviewer,$eventType,$msg);
//
//            return $this->redirectToRoute('translationalresearch_default-reviewer_show', array('id' => $defaultReviewer->getId()));
//        }
//
//        return array(
//            'cycle' => $cycle,
//            'defaultReviewer' => $defaultReviewer,
//            'specialty' => $defaultReviewer->getProjectSpecialty(),
//            'form' => $form->createView(),
//            'title' => "Default Reviewer for ".$specialtyStr." ".$stateLabel,
//            'delete_form' => $deleteForm->createView(),
//        );

    }


    public function classMapper($bundleName,$className) {

        $prefix = "App";
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
            case "MessageCategory":
                $organizationalGroupType = "MessageTypeClassifiers";
                break;
            case "PatientListHierarchy":
                $organizationalGroupType = "PatientListHierarchyGroupType";
                break;
            case "AccessionListHierarchy":
                $organizationalGroupType = "AccessionListHierarchyGroupType";
                break;
            case "FormNode":
                $organizationalGroupType = NULL;
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
