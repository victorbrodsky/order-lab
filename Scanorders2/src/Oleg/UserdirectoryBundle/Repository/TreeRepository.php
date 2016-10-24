<?php


namespace Oleg\UserdirectoryBundle\Repository;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityRepository;
use Gedmo\Tree\Entity\Repository\NestedTreeRepository;
use Oleg\UserdirectoryBundle\Entity\Institution;
use Oleg\UserdirectoryBundle\Form\DataTransformer\GenericTreeTransformer;

class TreeRepository extends NestedTreeRepository {

    //check if node belongs to the parentNode tree. For example, 1wcmc6->2path5->3inf4 => if inf.lft > wcmc.lft AND inf.rgt < wcmc.rgt => return true.
    public function isNodeUnderParentnode( $parentNode, $node ) {

        //echo "node=".$node."<br>";

        if( !$parentNode || !$node ) {
            return false;
        }

        //the node is the parentNode
        if( $parentNode->getId() == $node->getId() ) {
            //echo "parentNode:".$parentNode."(".$parentNode->getId().") and node:".$node."(".$node->getId().") are the same <br>";
            return true;
        }

        if( $node->getRoot() == $parentNode->getRoot() && $node->getLft() > $parentNode->getLft() && $node->getRgt() < $parentNode->getRgt() ) {
            //echo "parentNode:".$parentNode."(".$parentNode->getId().") has the node:".$node."(".$node->getId().") <br>";
            return true;
        }

        return false;
    }

    public function isNodeUnderParentnodes( $parentNodes, $node ) {
        foreach( $parentNodes as $parentNode ) {
            if( $this->isNodeUnderParentnode($parentNode, $node) ) {
                return true;
            }
        }

        return false;
    }

//    public function isParentNodeUnderNodes( $parentNode, $nodes ) {
//        foreach( $nodes as $node ) {
//            if( $this->isNodeUnderParentnode($parentNode, $node) ) {
//                return true;
//            }
//        }
//
//        return false;
//    }

//    public function selectStrNodesUnderParentNode( Institution $parentNode, Institution $node ) {
//
//        $criteriastr = "";
//        $criteriastr .= $node->getRoot() . " = " . $parentNode->getRoot();
//        $criteriastr .= " AND ";
//        $criteriastr .= $node->getLft() . " >= " . $parentNode->getLft();
//        $criteriastr .= " AND ";
//        $criteriastr .= $node->getRgt() . " =< " . $parentNode->getRgt();
//        //$criteriastr .= " OR ";
//        $criteriastr .= $node->getId() . " = " . $parentNode->getId();
//
//        $criteriastr = "(".$criteriastr.")";
//
//        return $criteriastr;
//    }

    public function selectNodesUnderParentNode( Institution $parentNode, $field, $default=true ) {

        if( $default ) {
            $comparatorLft = "<";
            $comparatorRgt = ">";
        } else {
            $comparatorLft = ">";
            $comparatorRgt = "<";
        }

        $criteriastr = "";
        $criteriastr .= $field.".root = " . $parentNode->getRoot();
        $criteriastr .= " AND ";
        $criteriastr .= $field.".lft $comparatorLft " . $parentNode->getLft(); //Default: lft < getLft
        $criteriastr .= " AND ";
        $criteriastr .= $field.".rgt $comparatorRgt " . $parentNode->getRgt(); //Default: rgt > getRgt
        $criteriastr .= " OR ";
        $criteriastr .= $field.".id = " . $parentNode->getId();

        $criteriastr = "(".$criteriastr.")";

        return $criteriastr;
    }

    //check if an institution node under permitted institutions and collaboration institutions
//    public function isNodeUnderPermittedInstitutions( $node, $permittedInstitutions, $collaborationTypesStrArr=array("Union") ) {
//
//        $res = false;
//
//        $repository = $this->_em->getRepository('OlegUserdirectoryBundle:Collaboration');
//        $dql = $repository->createQueryBuilder("collaboration");
//        $dql->select("collaboration");
//        $dql->leftJoin("collaboration.institutions","institutions");
//
//        //$criteriastr = "";
//        $criteriastr = "collaboration.type != 'disabled' AND collaboration.type != 'draft'";
//
//        //permitted institutions
//        foreach( $permittedInstitutions as $permittedInstitution ) {
//            if( $criteriastr != "" ) {
//                $criteriastr = $criteriastr . " OR ";
//            }
//            $criteriastr .= $this->selectStrNodesUnderParentNode( $permittedInstitution, $node );
//        }
//
//        //echo "criteriastr=".$criteriastr."<br>";
//
//        $dql->where($criteriastr);
//        $query = $this->_em->createQuery($dql);
//        $collaborations = $query->getResult();
//
//        //echo "single query count(collaborations)=".count($collaborations)."<br>";
//        //exit();
//
//        if( count($collaborations) > 0 ) {
//            $res = true;
//        }
//
//        return $res;
//    }

//    //check collaboration with given node
//    //$collaborationTypesStrArr: array("Union","Intersection"), if null - ignore collaborations
//    public function findCollaborationsByNode_old( $node, $collaborationTypesStrArr=array("Union") ) {
//
//        if( !$collaborationTypesStrArr ) {
//            $msg = "Collaboration is ignored. Collaboration type is null.";
//            //exit($msg);
//            throw new \Exception($msg);
//            return array();
//        }
//        if( count($collaborationTypesStrArr) == 0 ) {
//            $msg = "Collaboration is ignored. Collaboration type array is null count=".count($collaborationTypesStrArr);
//            //exit($msg);
//            throw new \Exception($msg);
//            return array();
//        }
//
//        //get collaborations with type $collaborationTypesStrArr
//        $collaborations = new ArrayCollection();
//
//        foreach( $node->getCollaborationInstitutions() as $collaboration ) {
//
//            $collaborationObjType = $node->getCollaborationType()."";
//
//            if( $collaborationObjType && in_array($collaborationObjType, $collaborationTypesStrArr) ) {
//                if( $collaboration && !$collaborations->contains($collaboration)  ) {
//                    $collaborations->add($collaboration);
//                }
//            }
//
//        }
//
//        //echo "count(collaborations)=".count($collaborations)."<br>";
//
//        return $collaborations;
//    }

    //check collaboration with given node: select all institutions where the node is indicated as collaboration institution.
    //$collaborationTypesStrArr: array("Union","Intersection"), if null - ignore collaborations
    public function findCollaborationsByNode( $node, $collaborationTypesStrArr=array("Union") ) {

        if( !$collaborationTypesStrArr ) {
            $msg = "Collaboration is ignored. Collaboration type array is null.";
            //exit($msg);
            throw new \Exception($msg);
            return array();
        }
        if( count($collaborationTypesStrArr) == 0 ) {
            $msg = "Collaboration is ignored. Collaboration type array has no elements: ".print_r($collaborationTypesStrArr);
            //exit($msg);
            throw new \Exception($msg);
            return array();
        }

        $repository = $this->_em->getRepository('OlegUserdirectoryBundle:Institution');
        $dql = $repository->createQueryBuilder("institution");
        $dql->select("institution");
        $dql->leftJoin("institution.collaborationInstitutions","collaborationInstitutions");
        $dql->leftJoin("institution.collaborationType","collaborationType");

        ///// replaced by getCriterionStrForCollaborationsByNode /////
        $criteriastr = "institution.type != 'disabled' AND institution.type != 'draft'"; //->setParameters( array('disabletype'=>'disabled','drafttype'=>'draft')

        $criteriastr = $criteriastr . " AND " . $this->selectNodesUnderParentNode( $node, "collaborationInstitutions" );

        if( $collaborationTypesStrArr && count($collaborationTypesStrArr) > 0 ) {
            $collaborationTypeCriterionArr = array();
            foreach( $collaborationTypesStrArr as $collaborationTypesStr ) {
                $collaborationTypeCriterionArr[] = "collaborationType.name = '" . $collaborationTypesStr . "'";
            }

            $criteriastr .= " AND " . implode( " OR ", $collaborationTypeCriterionArr );
        }
        ///// EOF replaced by getCriterionStrForCollaborationsByNode /////

        //echo "criteriastr=".$criteriastr."<br>";

        $dql->where($criteriastr);
        $query = $this->_em->createQuery($dql);
        $collaborations = $query->getResult();

        //TODO: add collaboration institutions if node is collaboration type and has collaboration institutions
        //i.e. "WCMC_NYP Collaboration" node has "Collaboration" type and has two collaboration institutions - WCMC and NYP
        //collaborations
        foreach( $node->getCollaborationInstitutions() as $collaborationNode ) {
            if( !in_array($collaborationNode, $collaborations) ) {
                //echo "collaborationNode=".$collaborationNode."<br>";
                $collaborations[] = $collaborationNode;
            }
        }


        //echo "count(collaborations)=".count($collaborations)."<br>";

        return $collaborations;
    }

    //$node - institution of the search entity
    //$field = "institutions"
    //$collaborationTypesStrArr: array("Union","Intersection","Untrusted Intersection"). If null => ignore collaboration
    public function getCriterionStrForCollaborationsByNode( $node, $field, $collaborationTypesStrArr=array("Union"), $instDefault=true, $collDefault=true ) {

        //institutional scope
        $addedNodes = array();

        $addedNodes[] = $node->getId();
        $institutionalCriteriaStr = $this->selectNodesUnderParentNode( $node, $field, $instDefault );

        //collaborations
        $collaborations = $this->findCollaborationsByNode( $node, $collaborationTypesStrArr );
        $collaborationCriterionArr = array();
        foreach( $collaborations as $collaboration ) {
            foreach( $collaboration->getCollaborationInstitutions() as $collaborationNode ) {
                if( !in_array($collaborationNode->getId(), $addedNodes) ) {
                    //echo "collaborationNode=".$collaborationNode."<br>";
                    $collaborationCriterionArr[] = $this->selectNodesUnderParentNode( $collaborationNode, $field, $collDefault );
                }
            }
        }

        $collaborationCriteriaStr = "";
        if( count($collaborationCriterionArr) > 0 ) {
            $collaborationCriteriaStr = " OR " . implode(" OR ",$collaborationCriterionArr);
        }

        $criteriastr = $institutionalCriteriaStr . $collaborationCriteriaStr;

        return $criteriastr;
    }



    public function findNodeByNameAndRoot($rootNodeId,$nameStr,$mapper=null) {

        $node = null;

        if( !$mapper ) {
            $mapper = array(
                'prefix' => "Oleg",
                'className' => "Institution",
                'bundleName' => "UserdirectoryBundle"
            );
        }

        $treeRepository = $this->_em->getRepository($mapper['prefix'].$mapper['bundleName'].':'.$mapper['className']);
        $dql =  $treeRepository->createQueryBuilder("list");
        $dql->select('list');
        $dql->where("list.name = :nameStr AND list.root=:rootNodeId");
        $dql->orderBy("list.level","ASC"); //higher level first, so in case of similar division and service name, the division will be returned.

        $query = $this->_em->createQuery($dql);
        $query->setParameters( array("nameStr"=>$nameStr,"rootNodeId"=>$rootNodeId) );

        $nodes = $query->getResult();
        //echo "nodes count=".count($nodes)."<br>";

        if( count($nodes) > 0 ) {
            $node = $nodes[0];
        }

        return $node;
    }

    public function findChildAtPosition($parent,$position) {
        //$children = $this->children($parent);
        $children = $parent->getChildren();
        if( $children && count($children) > 0 ) {
            $child = $children->get($position);
        } else {
            $child = null;
        }
        return $child;
    }

    public function findByChildnameAndParent($childName,$parent,$mapper) {

        if( !$childName || !$parent ) {
            //exit('Logical Error: category and/or parent is null');
            throw new \Exception('Logical Error: child name and/or parent is null');
        }

        $foundChildEntity = null;

//        $fullClassName = new \ReflectionClass($parent);
//        $className = $fullClassName->getShortName();
//        //echo "<br><br>find Category className=".$className."<br>";

        //echo "childName=(".$childName.")<br>";
        //echo "rep=".$mapper['prefix'].$mapper['bundleName'].':'.$mapper['className']."<br>";

        $treeRepository = $this->_em->getRepository($mapper['prefix'].$mapper['bundleName'].':'.$mapper['className']);
        $dql =  $treeRepository->createQueryBuilder("list");
        $dql->select('list');
        $dql->leftJoin("list.parent","parent");
        $dql->where('parent.id = :parentid AND list.name = :childname');
        //$dql->where("parent.id = ".$parent->getId()." AND list.name = '".$childName."'");

        $query = $this->_em->createQuery($dql);

        $params = array('parentid' => $parent->getId(), 'childname' => $childName);

        $query->setParameters($params);

        $results = $query->getResult();

       if( count($results) > 0 ) {
           $foundChildEntity = $results[0];
       }


        //echo "foundChildEntity=".$foundChildEntity."<br>";
        //exit('tree rep');

        return $foundChildEntity;
    }


    public function findCategoryByChildAndParent($category,$parent) {

        if( !$category || !$parent ) {
            //exit('Logical Error: category and/or parent is null');
            throw new \Exception('Logical Error: category and/or parent is null');
        }

        $name = $category->getName();
        $searchArr = array('name'=>$name);

        $fullClassName = new \ReflectionClass($category);
        $className = $fullClassName->getShortName();
        //echo "<br><br>find Category className=".$className."<br>";


        if( method_exists($category,'getParent')  ) {

            //echo "parent name=".$parent->getName().", id=".$parent->getId()."<br>";
            if( $parent->getName() && $parent->getName() != "" && !$parent->getId() ) {
                //echo "parent does not exist in DB => this category does not exist in DB => return null<br>";
                return null;
            }

            if( $parent && $parent->getId() && $parent->getId() != "" ) {
                $searchArr['parent'] = $parent->getId();
            }

        }

        //echo "search array:<br>";
        //print_r($searchArr);
        //echo "<br>";

        $foundCategory = $this->_em->getRepository('OlegUserdirectoryBundle:'.$className)->findOneBy($searchArr);

        return $foundCategory;
    }







    //utility function for tree parent-child relationship
    public function checkAndSetParent($author,$entity,$parent,$child) {

        //echo "child=".$child."<br>";
        if( !$child ) {
            //exit('Logical error: child does not exist');
            //throw new \Exception( 'Logical error: child does not exist');
            return $child;
        }

        if( !$parent ) {
            //exit('Logical error: parent does not exist');
            //throw new \Exception( 'Logical error: parent does not exist');
            return $child;
        }

        $fullClassName = new \ReflectionClass($child);
        $className = $fullClassName->getShortName();
        //echo "<br><br>Processing: className=".$className."<br>";
        //$removeMethod = "remove".$className;
        $addMethod = "add".$className;
        $setMethod = "set".$className;

        if( !$parent->getId() ) {
            //exit('Logical error: parent do not exist in DB, parent id is null');
            throw new \Exception( 'Logical error: parent do not exist in DB, parent id is null');
        }

        if( method_exists($child,'getParent') == false ) {
            //exit('Logical error: child does not have parent method');
            throw new \Exception('Logical error: child does not have parent method');
            //return $child;
        }

        //echo  "category: name=".$child->getName().", id=".$child->getId().", parentId=".$child->getParent()->getId()."<br>";
        //echo  "parent: name=".$parent->getName().", id=".$parent->getId()."<br>";

        if( $child && $child->getId() ) {
            //echo "don't process because category exists in DB, id=".$child->getId()." <br>";

            if( $child->getParent() && $child->getParent()->getId() ) {


                //check if parent is the same
                if( $parent->getId() == $child->getParent()->getId() ) {
                    //echo  "category and parent exist in DB: name=".$child->getName().", id=".$child->getId().", child parent id=".$child->getParent()->getId().", orig parent id=".$parent->getId()."<br>";
                    return $child;
                } else {
                    //echo  "category and exists in DB, but parents are different => new category: name=".$child->getName().", id=".$child->getId().", child parent id=".$child->getParent()->getId().", orig parent id=".$parent->getId()."<br>";
                }


            } else {
                //echo  "category exist in DB: name=".$child->getName().", id=".$child->getId()."<br>";
                //return $child;
                //exit('Logical error: child exists in DB but does not have parent');
                throw new \Exception( 'Logical error: child exists in DB but does not have parent');
            }
        }

        ////////////////// By this point we sure that child is valid //////////////////

        //echo 'check <br>';

        $em = $this->_em;

        $name = $child->getName();

        if( !$name || $name == '' ) {
            //exit('child name is NULL');
            return $child;
        }

        //echo "parent=".$parent.", id=".$parent->getId()."<br>";
        //echo "child=".$child.", id=".$child->getId()."<br>";

        //find child in DB by name and parent
        $foundChild = $this->findCategoryByChildAndParent($child,$parent);

        //echo "foundChild=".$foundChild."<br>";
        //exit();

        if( !$foundChild ) {

            //echo "Case 1: Not found in DB => create new <br>";
            $treeTransf = new GenericTreeTransformer($em,$author);
            $newChild = $treeTransf->createNewEntity($name,$className,$author);
            $em->persist($newChild);
            $parent->$addMethod($newChild);

            //overwrite entity
            $entity->$setMethod($newChild);

            //echo "final category to create: name=".$newChild->getName().", id=".$newChild->getId().", parentId=".$newChild->getParent()->getId()."<br>";
            //exit();

            $this->_em->persist($newChild);
            $this->_em->flush($newChild);

            return $newChild;

        } else {

            //echo "Case 2: Found in DB<br>";
            //exit();

            $parent->$addMethod($foundChild);

            //overwrite entity
            $entity->$setMethod($foundChild);

            //echo "final category: name=".$foundChild->getName().", id=".$foundChild->getId().", parentId=".$foundChild->getParent()->getId()."<br>";

            return $foundChild;
        }

    }



//    public function getLevelLabels( $nodes ) {
//        $labelsStr = "";
//        $labels = array();
//
//        foreach( $nodes as $node ) {
//            $nodeLabel = $node->getOrganizationalGroupType()->getName()."";
//            if( $node && !in_array($nodeLabel,$labels) ) {
//                $labels[] = $nodeLabel;
//            }
//        }
//
//        if( count($labels) > 0 ){
//            $labelsStr = implode(",", $labels);
//        }
//
//        return $labelsStr;
//    }

    //$mapper: if $mapper is null => institution
    public function getLevelLabels( $node=null, $mapper=null ) {
        return $this->getLevelLabelsInstitution($node,$mapper);
//        if( $node instanceof Institution ) {
//            return $this->getLevelLabelsInstitution($node,$mapper);
//        } else {
//            return $this->getLevelLabelsRegular($node,$mapper);
//        }
    }

    public function getLevelLabelsInstitution( $node=null, $mapper=null ) {

        $labelsStr = "";

        //get labels for all siblings of this node

        if( !$mapper ) {
            $mapper = array(
                'prefix' => "Oleg",
                'className' => "Institution",
                'bundleName' => "UserdirectoryBundle",
                'organizationalGroupType' => "OrganizationalGroupType"
            );
        }

        //check if types exists (types exists only for institution)
        $isInstitution = false;
        if( $mapper['className'] == "Institution" ) {
            $isInstitution = true;
        }

        //echo "<br>get labels for ".$mapper['className']."<br>";

        $treeRepository = $this->_em->getRepository($mapper['prefix'].$mapper['bundleName'].':'.$mapper['className']);
        $dql =  $treeRepository->createQueryBuilder("list");
        $dql->select('list');
        $dql->leftJoin("list.organizationalGroupType","organizationalGroupType");

        if( $isInstitution ) {
            $dql->leftJoin("list.types", "types");
        }

        $where = "(list.type = :typedef OR list.type = :typeadd)";
        $params = array('typedef' => 'default','typeadd' => 'user-added');

        if( $node ) {
            $parent = $node->getParent();
        } else {
            $parent = null;
        }

        if( $parent ) {
            $pid = $parent->getId();
            $dql->leftJoin("list.parent", "parent");
            $where = $where . " AND parent.id = :id";
            $params['id'] = $pid;
        } else {
            $dql->leftJoin("list.parent", "parent");
            $where = $where . " AND parent.id is NULL";
        }

        $dql->where($where);
        //echo "dql=".$dql."<br>";

        $query = $this->_em->createQuery($dql);
        $query->setParameters($params);

        $results = $query->getResult();

        $labelArr = array();
        foreach( $results as $result ) {
            $label = null;
            if( $isInstitution && $result->hasInstitutionType("Collaboration") ) {
                $label = "Collaboration";
            } else {
                if( $result->getOrganizationalGroupType() ) {
                    $label = $result->getOrganizationalGroupType()->getName() . "";
                }
            }
            //echo "loop label=".$label."<br>";
            if( $label && !in_array($label, $labelArr) ) {
                $labelArr[] = $label;
            }
        }

        $count = 0;

        //3 cases:
        //Department
        //Department or Group
        //Department, Group, or Collaboration
        foreach( $labelArr as $label ) {
            //echo "TreeRep: label=".$label."<br>";

            if( !$label ) {
                continue;
            }

            if( $count == 0 ) {
                $labelsStr = $label;
                $count++;
                continue;
            }

            if( count($labelArr) > $count + 1 ) {
                $labelsStr = $labelsStr . ", " . $label;
                //continue;
            }

            if( count($labelArr) == $count + 1 ) {
                if( count($labelArr) == 2 ) {
                    $labelsStr = $labelsStr . " or " . $label;
                } else {
                    $labelsStr = $labelsStr . ", or " . $label;
                }
                //continue;
            }

            $count++;
        }
        //echo "labelsStr=".$labelsStr."<br>";

        //if not found (no nodes exists), then get default label for 0 level
        if( !$labelsStr ) {
            $labelsStr = $this->getDefaultLevelLabel($mapper,0);
        }

        return $labelsStr;
    }

    public function getLevelLabelsRegular( $node=null, $mapper=null ) {

        $labelsStr = "";

        //get labels for all siblings of this node

        if( !$mapper ) {
            $mapper = array(
                'prefix' => "Oleg",
                'className' => "Institution",
                'bundleName' => "UserdirectoryBundle",
                'organizationalGroupType' => "OrganizationalGroupType"
            );
        }

        //echo "<br>get labels for ".$mapper['className']."<br>";

        $treeRepository = $this->_em->getRepository($mapper['prefix'].$mapper['bundleName'].':'.$mapper['className']);
        $dql =  $treeRepository->createQueryBuilder("list");
        $dql->select('DISTINCT(organizationalGroupType.name) AS levelLabel');
        $dql->leftJoin("list.organizationalGroupType","organizationalGroupType");

        $where = "(list.type = :typedef OR list.type = :typeadd)";
        $params = array('typedef' => 'default','typeadd' => 'user-added');

        if( $node ) {
            $parent = $node->getParent();
        } else {
            $parent = null;
        }

        if( $parent ) {
            $pid = $parent->getId();
            $dql->leftJoin("list.parent", "parent");
            $where = $where . " AND parent.id = :id";
            $params['id'] = $pid;
        } else {
            $dql->leftJoin("list.parent", "parent");
            $where = $where . " AND parent.id is NULL";
        }

        $dql->where($where);
        //echo "dql=".$dql."<br>";

        $query = $this->_em->createQuery($dql);
        $query->setParameters($params);

        $results = $query->getResult();

        $count = 0;

        //3 cases:
        //Department
        //Department or Group
        //Department, Group, or Collaboration
        foreach( $results as $result ) {
            $label = $result['levelLabel'];
            //echo "label=".$result['levelLabel']."<br>";

            if( !$label ) {
                continue;
            }

            if( $count == 0 ) {
                $labelsStr = $label;
                $count++;
                continue;
            }

            if( count($results) > $count + 1 ) {
                $labelsStr = $labelsStr . ", " . $label;
                //continue;
            }

            if( count($results) == $count + 1 ) {
                if( count($results) == 2 ) {
                    $labelsStr = $labelsStr . " or " . $label;
                } else {
                    $labelsStr = $labelsStr . ", or " . $label;
                }
                //continue;
            }

            $count++;
        }
        //echo "labelsStr=".$labelsStr."<br>";

        //if not found (no nodes exists), then get default label for 0 level
        if( !$labelsStr ) {
            $labelsStr = $this->getDefaultLevelLabel($mapper,0);
        }

        return $labelsStr;
    }


    public function getDefaultLevelLabel( $mapper, $level ) {

        $organizationalGroupType = $this->getDefaultLevelEntity( $mapper, $level );

        if( $organizationalGroupType ) {
            $levelTitle = $organizationalGroupType->getName()."";
        } else {
            $levelTitle = "Level ".$level;
        }

        return $levelTitle;
    }



    public function getDefaultLevelEntity( $mapper, $level ) {

        if( !array_key_exists('organizationalGroupType', $mapper) || !$mapper['organizationalGroupType'] ) {
            return null;
        }

        $organizationalGroupTypes = $this->_em->getRepository($mapper['prefix'].$mapper['bundleName'].':'.$mapper['organizationalGroupType'])->findBy(
            array(
                "level" => $level,
                "type" => array('default','user-added')
            )
        );

        if( count($organizationalGroupTypes) > 0 ) {
            $organizationalGroupType = $organizationalGroupTypes[0];
        }

        if( count($organizationalGroupTypes) == 0 ) {
            $organizationalGroupType = null;
        }

        return $organizationalGroupType;
    }


}

