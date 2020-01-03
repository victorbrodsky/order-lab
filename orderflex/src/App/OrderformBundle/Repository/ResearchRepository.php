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

namespace App\OrderformBundle\Repository;

//use Doctrine\ORM\EntityRepository;

use App\UserdirectoryBundle\Repository\ListAbstractRepository;

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
//            'fullClassName' => "App\\OrderformBundle\\Entity\\"."ProjectTitleList",
//            'fullBundleName' => 'AppOrderformBundle'
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
//            'fullClassName' => "App\\OrderformBundle\\Entity\\"."SetTitleList",
//            'fullBundleName' => 'AppOrderformBundle'
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
