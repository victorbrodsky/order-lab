<?php


namespace Oleg\UserdirectoryBundle\Repository;

use Doctrine\ORM\EntityRepository;

use Oleg\UserdirectoryBundle\Form\DataTransformer\GenericTreeTransformer;

class TreeRepository extends EntityRepository {

    //check if given category exists in DB
    public function processListElement($author,$holder,$category,$parentCategory=null) {

        $em = $this->_em;

        $fullClassName = new \ReflectionClass($category);
        $className = $fullClassName->getShortName();
        echo "<br><br>Process Category className=".$className."<br>";
        //$removeMethod = "remove".$className;
        //$addMethod = "add".$className;
        $setMethod = "set".$className;

        //set original subtype from type to comment
        $formSubCategory = null;
        $children = $category->getChildren();
        if( count($children) > 0 ) {
            $formSubCategory = $category->getChildren()->first();
            echo "formSubCategory=".$formSubCategory."<br>";
            //$holder->setCommentSubType($formSubCategory);
        }

        $name = $category->getName();

        $searchArr = array('name'=>$name);
        if( $parentCategory && $parentCategory->getId() ) {
            $searchArr['parent'] = $parentCategory->getId();
        }

        echo "serach array:<br>";
        print_r($searchArr);
        echo "<br>";

        $foundCategory = $em->getRepository('OlegUserdirectoryBundle:'.$className)->findOneBy($searchArr);

        echo "foundCategory=".$foundCategory."<br>";

        if( $foundCategory ) {

            echo "Found: Category=".$foundCategory."<br>";

            $children = $category->getChildren();
            foreach( $children as $child ) {

                echo "child id=".$child->getId()."<br>";

                if( $child->getId() && $child->getId() != "" ) {
                    continue; //skip for existing child in DB
                }

                $category->removeChild($child);
                $child->setParent(null);

                //process children
                $child = $em->getRepository('OlegUserdirectoryBundle:CommentTypeList')->processListElement($author,$holder,$child,$foundCategory);
                $em->persist($child);

                $foundCategory->addChild($child);

                if( method_exists($formSubCategory,'getParent')  ) {
                    if( $formSubCategory && $formSubCategory->getName() == $child->getName() && $formSubCategory->getParent()->getName() == $child->getParent()->getName() ) {
                        $fullChildClassName = new \ReflectionClass($category);
                        $childClassName = $fullChildClassName->getShortName();
                        $setChildMethod = "set".$childClassName;
                        echo "setChildMethod=".$setChildMethod."<br>";
                        $holder->$setChildMethod($child);
                    }
                }

            }

            //$holder->$removeMethod($category);
            $holder->$setMethod($foundCategory);

            return $foundCategory;

        } else {

            echo "Category is not found=".$category."<br>";

            //set parent if applicable
            if( $parentCategory && method_exists($category,'getParent') ) {
                echo "set parent=".$parentCategory."<br>";
                $parentCategory->addChild($category);
            }

            //$category->setType('user-added');
            $treeTransf = new GenericTreeTransformer($em);
            $category = $treeTransf->populateEntity($category);

            $category->setCreator($author);

            echo "cat type=".$category->getType()."<br>";
            echo "cat creator=".$category->getCreator()."<br>";
            //exit();

            $em->persist($category);

            //overwrite entity
            $fullChildClassName = new \ReflectionClass($category);
            $childClassName = $fullChildClassName->getShortName();
            $setChildMethod = "set".$childClassName;
            echo "setChildMethod=".$setChildMethod."<br>";
            $holder->$setChildMethod($category);

            return $category;

        }

    }















    //utility function for tree parent-child relationship
    public function checkAndSetParent($user,$entity,$parent,$child) {

        if( !$parent || !$child ) {
            return $child;
        }

        if( method_exists($child,'getParent') == false ) {
            return $child;
        }

        //echo 'check <br>';

        $fullClassName = new \ReflectionClass($child);
        $className = $fullClassName->getShortName();
        //echo "className=".$className."<br>";
        //$removeMethod = "remove".$className;
        $addMethod = "add".$className;
        $setMethod = "set".$className;

        //don't overwrite parent
//        if( $child->getParent() && $child->getId() ) {
//            //echo 'child already has parent <br>';
//            return $child;
//        }


        $em = $this->_em;

        $name = $child->getName();

        echo "parent=".$parent.", id=".$parent->getId()."<br>";
        echo "child=".$child.", id=".$child->getId()."<br>";

        //find child in DB by id or name and parent
        if( $parent->getId() ) {
            $foundChild = $em->getRepository('OlegUserdirectoryBundle:'.$className)->findOneBy(array('name'=>$name,'parent'=>$parent));
        } else if( $child->getId() && $child->getId() != "" ) {
            $foundChild = $child;
        } else {
            $foundChild = null;
        }

        //echo "foundChild=".$foundChild.", id=".$foundChild->getId()." <br>";

        //exit();

        if( !$foundChild ) {

            echo "create new !!!!!!!!!!!!!!!!!!!!! <br>";
            $treeTransf = new GenericTreeTransformer($em,$user);
            $newChild = $treeTransf->createNewEntity($name,$className,$user);
            $em->persist($newChild);
            //$em->flush($newChild);
            $parent->$addMethod($newChild);

            //overwrite entity
            $entity->$setMethod($newChild);

            return $newChild;

        } else {

            $em->persist($foundChild);
            $parent->$addMethod($foundChild);

            //overwrite entity
            $entity->$setMethod($foundChild);
            //$em->flush($entity);

            return $foundChild;

        }

    }

}

