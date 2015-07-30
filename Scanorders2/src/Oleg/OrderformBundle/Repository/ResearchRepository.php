<?php

namespace Oleg\OrderformBundle\Repository;

//use Doctrine\ORM\EntityRepository;

use Oleg\UserdirectoryBundle\Repository\ListAbstractRepository;

class ResearchRepository extends ListAbstractRepository {


    public function processEntity( $message, $user ) {

        $research = $message->getResearch();

        echo "research=".$research."<br>";
        //exit();

        if( !$research || $research->isEmpty() ) {
            $message->setResearch(NULL);
            echo "research is empty<br>";
            //exit();
            return $message;
        }

        foreach( $research->getUserWrappers() as $userWrapper ) {
            if( $research->getProjectTitle() ) {
                $research->getProjectTitle()->addUserWrapper($userWrapper);
            }
        }

        //$projectTitle = $research->getProjectTitle();
        //if( $projectTitle ) {
            //process principals and primary principal
            //$this->processPrincipals( $research, $projectTitle );
        //}

        //exit('res');
        return $message;
    }
//    public function processEntity_Old( $message, $user ) {
//
//        $research = $message->getResearch();
//
//        if( !$research || $research->isEmpty() ) {
//            $message->setResearch(NULL);
//            return $message;
//        }
//
//        //process Project Title
//        $objectParams = array(
//            'className' => 'ProjectTitleList',
//            'fullClassName' => "Oleg\\OrderformBundle\\Entity\\"."ProjectTitleList",
//            'fullBundleName' => 'OlegOrderformBundle'
//        );
//        $projectTitle = $this->convertStrToObject( $research->getProjectTitleStr(), $objectParams, $user );
//        $research->setProjectTitle($projectTitle);
//        //echo "projectTitle name=".$projectTitle->getName()."<br>";
//
//        //echo "SetTitleStr=".$research->getSetTitleStr()."<br>";
//
//        //process Set Title
//        $objectParams = array(
//            'className' => 'SetTitleList',
//            'fullClassName' => "Oleg\\OrderformBundle\\Entity\\"."SetTitleList",
//            'fullBundleName' => 'OlegOrderformBundle'
//        );
//        $setTitle = $this->convertStrToObject( $research->getSetTitleStr(), $objectParams, $user, 'projectTitle', $projectTitle->getId() );
//
//        //process principals and primary principal
//        $this->processPrincipals( $research, $projectTitle );
//        //exit();
//
//        //set this new SetTitle to Research and ProjectTitle objects
//        $projectTitle->addSetTitle($setTitle);
//
////        foreach( $projectTitle->getSetTitles() as $settitle ) {
////            echo "SetTitleList name=".$settitle->getName().", id=".$settitle->getId()."<br>";
////        }
////        echo "SetTitleStr=".$message->getResearch()->getSetTitleStr()."<br>";
//
//        //exit('res');
//        return $message;
//    }



//    //inputs: source $research, destination ProjectTitle
//    public function processPrincipals( $research, $foundprojectTitle ) {
//
//        $userWrappers = $research->getUserWrappers();
//
//        foreach( $userWrappers as $userWrapper ) {
//
//            $userstr = $userWrapper->getName()."";
//            //echo "userstr=".$userstr."<br>";
//            $foundPrincipal = $this->_em->getRepository('UserdirectoryBundle:UserWrapper')->findOneByName($userstr);
//
//            if( !$foundPrincipal ) {
//                throw new \Exception( 'Principal was not found with name '.$userstr );
//            }
//
//            $foundprojectTitle->addPrincipal($foundPrincipal);
//            $foundPrincipal->addProjectTitle($foundprojectTitle);
//
//            //set primaryPrincipal as a first principal
//            if( $userWrappers->first() ) {
//                if( !$foundprojectTitle->getPrimaryPrincipal() ) {
//                    $foundprojectTitle->setPrimaryPrincipal( $userWrappers->first()->getPrincipal()->getId() );
//                    $research->setPrimarySet( $userWrappers->first()->getPrincipal()->getName() );
//                }
//            } else {
//                $foundprojectTitle->setPrimaryPrincipal( NULL );
//            }
//
//        }//foreach
//
//    }


}
