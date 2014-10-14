<?php


namespace Oleg\UserdirectoryBundle\Repository;

use Doctrine\ORM\EntityRepository;

use Oleg\UserdirectoryBundle\Form\DataTransformer\GenericTreeTransformer;

class TreeRepository extends EntityRepository {



    //check if given category exists in DB (down to up processing)
    //return category with all its parents
    public function processCategoryElement($author,$holder,$category) {

        $fullCategoryClassName = new \ReflectionClass($category);
        $categoryClassName = $fullCategoryClassName->getShortName();
        $setCategoryMethod = "set".$categoryClassName;
        echo "<br><br>Process category=".$categoryClassName."<br>";

        if( $category == null ) {
            echo "don't process because subtype is null <br>";
            return $category;
        }

        if( $category && $category->getId() ) {
            echo "don't process because category exists in DB, id=".$category->getId()." <br>";

            echo  "category: name=".$category->getName().", id=".$category->getId().", parentId=".$category->getParent()->getId()."<br>";

            //overwrite entity
            $holder->$setCategoryMethod($category);

            return $category;
        }

        $foundCategory = $this->findCategoryByNameAndParentId($category);

        if( $foundCategory ) {

            echo "Case1: found in DB: ".$foundCategory->getName().", id=".$foundCategory->getId()."<br>";

            //set parent
            //$parent = $foundCategory->getParent();
            //$parent->addChild($foundCategory);
            if( !$foundCategory->getParent() ) {
                exit('Logical Error: parent does not exists!!!');
            }

            //overwrite entity
            $holder->$setCategoryMethod($foundCategory);

            return $foundCategory;

        } else {

            echo "Case2: Not found in DB: ".$category->getName().", id=".$category->getId()."<br>";

            //create category
            //create new category with existing parent
            $treeTransf = new GenericTreeTransformer($this->_em);
            $category = $treeTransf->populateEntity($category);
            $category->setCreator($author);
            $category->setName($category->getName()."");

            //set parent
            if( method_exists($category,'getParent')  ) {

                echo "Case2 A: Parent method exists<br>";

                $parent = $category->getParent();

                if( $parent && $parent->getId() ) {

                    //parent exists in DB
                    echo "Case2 A a: Parent method exists in DB, name=".$parent->getName().",id=".$parent->getId()."<br>";

                } else {

                    echo "Case2 A b: Parent method not exists in DB, name=".$parent->getName().",id=".$parent->getId()."<br>";

                    //find parent
                    $parent = $this->findCategoryByNameAndParentId($parent);
                    //echo "find? parent=".$parent->getName().",id=".$parent->getId()."<br>";
                    if( !$parent ) {
                        $parent = $this->processCategoryElement($author,$holder,$category);
                    }

                }

                echo "Parent, name=".$parent->getName().",id=".$parent->getId()."<br>";

                $parent->addChild($category);

            } else {

                echo "Case2 B: Parent method not exists<br>";
                $parent = $this->processCategoryElement($author,$holder,$category);
                $parent->addChild($category);

            }

            echo "final category: name=".$category->getName().", id=".$category->getId().", parentId=".$category->getParent()->getId()."<br>";
            //exit();

            $this->_em->persist($category);
            $this->_em->flush($category);

            //overwrite entity
            $holder->$setCategoryMethod($category);

            return $category;

        }


        throw new \Exception( 'Logical error: no return');
    }


    public function findCategoryByNameAndParentId($category,$parent=null) {

        if( $category->getId() && $category->getId() != NULL ) {
            return $category;
        }

        $name = $category->getName();
        $searchArr = array('name'=>$name);

        $fullClassName = new \ReflectionClass($category);
        $className = $fullClassName->getShortName();
        //echo "<br><br>find Category className=".$className."<br>";


        if( method_exists($category,'getParent')  ) {

            if( $parent == null ) {
                $parent = $category->getParent();
            }

            echo "parent name=".$parent->getName().", id=".$parent->getId()."<br>";
            if( $parent->getName() && $parent->getName() != "" && !$parent->getId() ) {
                echo "parent does not exist in DB => this category does not exist in DB => return null<br>";
                return null;
            }

            if( $parent && $parent->getId() && $parent->getId() != "" ) {
                $searchArr['parent'] = $parent->getId();
            }

        }

        echo "serach array:<br>";
        print_r($searchArr);
        echo "<br>";

        $foundCategory = $this->_em->getRepository('OlegUserdirectoryBundle:'.$className)->findOneBy($searchArr);

        return $foundCategory;
    }












    //check if given category exists in DB
    public function processListElement_UpToDown($author,$holder,$category,$parentCategory=null) {

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
            //echo "formSubCategory=".$formSubCategory."<br>";
            //$holder->setCommentSubType($formSubCategory);
        }

        //GenericTreeTransformer return id of the object, therefore $category is id of the object
        //echo "category=".$category."<br>";
        $name = $category->getName();
        $id = $category->getId();
        echo "name=".$name.", id=".$id."<br>";

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

                //set parent
                if( method_exists($formSubCategory,'getParent')  ) {
                    if( $formSubCategory && $child && $formSubCategory->getName() == $child->getName() ) {
                        if( $formSubCategory->getParent() && $child->getParent() && $formSubCategory->getParent()->getName() == $child->getParent()->getName() ) {
                            $fullChildClassName = new \ReflectionClass($category);
                            $childClassName = $fullChildClassName->getShortName();
                            $setChildMethod = "set".$childClassName;
                            //echo "setChildMethod=".$setChildMethod."<br>";
                            $holder->$setChildMethod($child);
                        }
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
                //echo "set parent=".$parentCategory."<br>";
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
            $em->flush($category);

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

        //echo "child=".$child."<br>";
        if( !$child ) {
            return $child;
            //exit('Logical error: child does not exist');
            //throw new \Exception( 'Logical error: parent and child do not exist');
        }

        if( !$parent ) {
            //exit('Logical error: parent does not exist');
            throw new \Exception( 'Logical error: parent and child do not exist');
            //return $child;
        }

        $fullClassName = new \ReflectionClass($child);
        $className = $fullClassName->getShortName();
        echo "<br><br>Processing: className=".$className."<br>";
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

        if( $child && $child->getId() ) {
            echo "don't process because category exists in DB, id=".$child->getId()." <br>";

            if( $child->getParent() && $child->getParent()->getId() ) {
                //echo  "category and parent exist in DB: name=".$child->getName().", id=".$child->getId().", parentId=".$child->getParent()->getId()."<br>";
                return $child;
            } else {
                //echo  "category exist in DB: name=".$child->getName().", id=".$child->getId()."<br>";
                //return $child;
                //exit('Logical error: child exists in DB but does not have parent');
                throw new \Exception( 'Logical error: child exists in DB but does not have parent');
            }
        }

        ////////////////// By this point we sure that child is valid //////////////////

        //echo 'check <br>';



        //don't overwrite parent
//        if( $child->getParent() && $child->getId() ) {
//            //echo 'child already has parent <br>';
//            return $child;
//        }


        $em = $this->_em;

        $name = $child->getName();

        //echo "parent=".$parent.", id=".$parent->getId()."<br>";
        //echo "child=".$child.", id=".$child->getId()."<br>";

//        //find child in DB by name and parent
//        if( $parent->getId() ) {
//            $foundChild = $em->getRepository('OlegUserdirectoryBundle:'.$className)->findOneBy(array('name'=>$name,'parent'=>$parent));
//        } else if( $child->getId() && $child->getId() != "" ) {
//            $foundChild = $child;
//        } else {
//            $foundChild = null;
//        }

        //find child in DB by name and parent
        $foundChild = $this->findCategoryByNameAndParentId($child,$parent);

        echo "foundChild=".$foundChild."<br>";

        //exit();

        if( !$foundChild ) {

            echo "Case 1: Not found in DB => create new <br>";
            $treeTransf = new GenericTreeTransformer($em,$user);
            $newChild = $treeTransf->createNewEntity($name,$className,$user);
            $em->persist($newChild);
            //$em->flush($newChild);
            $parent->$addMethod($newChild);

            //overwrite entity
            $entity->$setMethod($newChild);

            echo "final category to create: name=".$newChild->getName().", id=".$newChild->getId().", parentId=".$newChild->getParent()->getId()."<br>";
            //exit();

            $this->_em->persist($newChild);
            $this->_em->flush($newChild);

            return $newChild;

        } else {

            echo "Case 2: Found in DB<br>";
            //$em->persist($foundChild);
            $parent->$addMethod($foundChild);

            //overwrite entity
            $entity->$setMethod($foundChild);
            //$em->flush($entity);

            echo "final category: name=".$foundChild->getName().", id=".$foundChild->getId().", parentId=".$foundChild->getParent()->getId()."<br>";

            return $foundChild;

        }

    }

}

