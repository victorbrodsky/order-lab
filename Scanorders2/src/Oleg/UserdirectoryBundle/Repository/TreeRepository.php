<?php


namespace Oleg\UserdirectoryBundle\Repository;

use Doctrine\ORM\EntityRepository;

use Oleg\UserdirectoryBundle\Form\DataTransformer\GenericTreeTransformer;

class TreeRepository extends EntityRepository {


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

        //echo "parent=".$parent.", id=".$parent->getId()."<br>";

        //check by name and parent
        if( $parent->getId() ) {
            $foundChild = $em->getRepository('OlegUserdirectoryBundle:'.$className)->findOneBy(array('name'=>$name,'parent'=>$parent));
        } else {
            $foundChild = null;
        }

        if( !$foundChild ) {

            //echo "create new !!!!!!!!!!!!!!!!!!!!! <br>";
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

