<?php

namespace Oleg\OrderformBundle\Repository;

use Doctrine\ORM\EntityRepository;


class ResearchRepository extends ListAbstractRepository {

    public function processEntity( $orderinfo, $user ) {

        $research = $orderinfo->getResearch();

        if( $research->isEmpty() ) {
            $orderinfo->setResearch(NULL);
            return $orderinfo;
        }

        //process Project Title
        $projectTitle = $this->convertStrToObject( $research->getProjectTitleStr(), 'ProjectTitleList', $user );
        $research->setProjectTitle($projectTitle);
        //echo "projectTitle name=".$projectTitle->getName()."<br>";

        echo "SetTitleStr=".$research->getSetTitleStr()."<br>";

        //process Set Title
        $setTitle = $this->convertStrToObject( $research->getSetTitleStr(), 'SetTitleList', $user, 'projectTitle', $projectTitle->getId() );
        $research->setSetTitle($setTitle);

        //process principals and primary principal
        $this->processPrincipals( $research, $projectTitle );
        //exit();

        //set this new SetTitle to Research and ProjectTitle objects
        $projectTitle->addSetTitle($setTitle);

//        foreach( $projectTitle->getSetTitles() as $settitle ) {
//            echo "SetTitleList name=".$settitle->getName().", id=".$settitle->getId()."<br>";
//        }
//        echo "SetTitleStr=".$orderinfo->getResearch()->getSetTitleStr()."<br>";

        //exit('res');
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
                if( !$foundprojectTitle->getPrimaryPrincipal() ) {
                    $foundprojectTitle->setPrimaryPrincipal( $principalWrappers->first()->getPrincipal()->getId() );
                }
            } else {
                $foundprojectTitle->setPrimaryPrincipal( NULL );
            }

        }//foreach

    }


}
