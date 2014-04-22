<?php

namespace Oleg\OrderformBundle\Repository;

use Doctrine\ORM\EntityRepository;


class ResearchRepository extends EntityRepository {

    public function processEntity( $orderinfo, $user ) {

        $research = $orderinfo->getResearch();

        if( $research->isEmpty() ) {
            $orderinfo->setResearch(NULL);
            return $orderinfo;
        }

        //process Project Title
        $projectTitle = $this->convertStrToObject( $research->getProjectTitleStr(), 'ProjectTitleList', $user );
        $research->setProjectTitle($projectTitle);
        echo "projectTitle name=".$projectTitle->getName()."<br>";

        //process Set Title
        $setTitle = $this->convertStrToObject( $research->getSetTitleStr(), 'SetTitleList', $user, 'projectTitle', $projectTitle->getId() );
        $research->setSetTitle($setTitle);
        echo "SetTitleList name=".$projectTitle->getName().", id=".$projectTitle->getId()."<br>";

        //process principals and primary principal
        $this->processPrincipals( $research, $projectTitle );
        exit();

        //set this new SetTitle to Research and ProjectTitle objects
        $projectTitle->addSetTitle($setTitle);

        //exit('educ rep');
        return $orderinfo;
    }





    //inputs: source $research, destination ProjectTitle
    public function processPrincipals( $research, $foundprojectTitle ) {

        $principalWrappers = $research->getPrincipalWrappers();

        foreach( $principalWrappers as $principalWrapper ) {

            $principalstr = $principalWrapper->getPrincipalStr();
            //echo "principalstr=".$principalstr."<br>";
            $foundPrincipal = $this->_em->getRepository('OlegOrderformBundle:PIList')->findOneByName($principalstr);

            if( !$foundPrincipal ) {
                throw new \Exception( 'Principal was not found with name '.$principalstr );
            }

            $foundprojectTitle->addPrincipal($foundPrincipal);
            $foundPrincipal->addProjectTitle($foundprojectTitle);

            //set primaryPrincipal as a first principal
            if( $principalWrappers->first() ) {
                $foundprojectTitle->setPrimaryPrincipal( $principalWrappers->first()->getPrincipal()->getId() );
            } else {
                $foundprojectTitle->setPrimaryPrincipal( NULL );
            }

        }//foreach

    }



    //TODO: move below function to Abstract ListAbstractRepository

    //inputs: name, class name, user, parent field name, parent
    //output: new list entity (i.e. ProjectTitleList or SetTitleList)
    public function convertStrToObject( $name, $className, $user, $parentFieldName = null, $parentId=null ) {

        $criterions = array( 'name' => $name );

        echo "use parentId=".$parentId.", fieldname=".$parentFieldName."<br>";
        if( $parentFieldName ) {
            if( !$parentId ) {
                $parentId = -1; //if parentId is not set yet (object does not exists), force not found to create a new entity
            }
            echo "use parentId=".$parentId."<br>";
            $criterions[$parentFieldName] = $parentId;
        }

        $entity = $this->_em->getRepository('OlegOrderformBundle:'.$className)->findOneBy( $criterions );

        if( !$entity ) {
            echo $className.': not found <br>';
            //create a new setTitle
            $entity = $this->createNewListEntity($className,$name,$user);
        } else {
            echo $className.': found <br>';
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
