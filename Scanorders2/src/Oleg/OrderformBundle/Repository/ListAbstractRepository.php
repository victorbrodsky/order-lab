<?php
/**
 * Created by JetBrains PhpStorm.
 * User: oli2002
 * Date: 4/23/14
 * Time: 3:16 PM
 * To change this template use File | Settings | File Templates.
 */

namespace Oleg\OrderformBundle\Repository;

use Doctrine\ORM\EntityRepository;

class ListAbstractRepository extends EntityRepository {

    //inputs: name, class name, user, parent field name, parent
    //output: new list entity (i.e. ProjectTitleList or SetTitleList)
    public function convertStrToObject( $name, $className, $user, $parentFieldName = null, $parentId=null ) {

        if( !$name || $name == '' ) {
            return NULL;
        }

        $criterions = array( 'name' => $name );

        //echo "use parentId=".$parentId.", fieldname=".$parentFieldName."<br>";
        if( $parentFieldName ) {
            if( !$parentId ) {
                $parentId = -1; //if parentId is not set yet (object does not exists), force not found to create a new entity
            }
            //echo "use parentId=".$parentId."<br>";
            $criterions[$parentFieldName] = $parentId;
        }

        $entity = $this->_em->getRepository('OlegOrderformBundle:'.$className)->findOneBy( $criterions );

        if( !$entity ) {
            //echo $className.': not found <br>';
            //create a new setTitle
            $entity = $this->createNewListEntity($className,$name,$user);
        } else {
            //echo $className.': found <br>';
        }

        return $entity;

    }

    //create a new List Entity (i.e. setTitle)
    public function createNewListEntity( $className, $name, $user ) {

        //$className = "SetTitleList";
        $entityClass = "Oleg\\OrderformBundle\\Entity\\".$className;
        $newEntity = new $entityClass();
        $newEntity->setName($name);
        $newEntity->setCreatedate(new \DateTime());
        $newEntity->setType('default');
        $newEntity->setCreator($user);

        //get max orderinlist
        $query = $this->_em->createQuery('SELECT MAX(c.orderinlist) as maxorderinlist FROM OlegOrderformBundle:'.$className.' c');
        $nextorder = $query->getSingleResult()['maxorderinlist']+10;
        $newEntity->setOrderinlist($nextorder);

        return $newEntity;
    }

}