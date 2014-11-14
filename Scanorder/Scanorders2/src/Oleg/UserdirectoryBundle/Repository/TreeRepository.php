<?php


namespace Oleg\UserdirectoryBundle\Repository;

use Doctrine\ORM\EntityRepository;

use Oleg\UserdirectoryBundle\Form\DataTransformer\GenericTreeTransformer;

class TreeRepository extends EntityRepository {



    public function findCategoryByNameAndParentId($category,$parent) {

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

        //echo "parent=".$parent.", id=".$parent->getId()."<br>";
        //echo "child=".$child.", id=".$child->getId()."<br>";

        //find child in DB by name and parent
        $foundChild = $this->findCategoryByNameAndParentId($child,$parent);

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

            $parent->$addMethod($foundChild);

            //overwrite entity
            $entity->$setMethod($foundChild);

            //echo "final category: name=".$foundChild->getName().", id=".$foundChild->getId().", parentId=".$foundChild->getParent()->getId()."<br>";

            return $foundChild;
        }

    }

}

