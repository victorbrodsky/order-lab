<?php


namespace Oleg\UserdirectoryBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Oleg\UserdirectoryBundle\Entity\Grant;


class GrantRepository extends EntityRepository {


    public function processGrant( $user ) {

        $em = $this->_em;

        $grants = $user->getGrants();

        foreach( $grants as $grant ) {


            if( !($grant && $grant->getName() && $grant->getName() != "") ) {
                $user->removeGrant($grant);
                continue;
            }

            //get grant from DB if exists
            $grantDb = $em->getRepository('OlegUserdirectoryBundle:Grant')->findOneByName($grant->getGrantTitle()."");

            if( $grantDb ) {

                echo "found grant in DB name=".$grant->getName().", id=".$grant->getId()."<br>";

                //merge db and form entity
                $grantDb->setEffortDummy($grant->getEffortDummy());
                $grantDb->setCommentDummy($grant->getCommentDummy());

                $user->removeGrant($grant);
                $user->addGrant($grantDb);

                $grantFinal = $grantDb;

                //echo "lab dummy: id=".$lab->getId().", pi=".$lab->getPiDummy().", comment=".$lab->getCommentDummy()."<br>";
            } else {
                $grantFinal = $grant;
            }


            //check if effort already exists
            $grantEffortDb = $em->getRepository('OlegUserdirectoryBundle:GrantEffort')->findOneBy( array( 'author'=>$user, 'grant'=>$grantFinal->getId() ) );

            if( $grantFinal->getEffortDummy() ) {
                //echo "lab pi=".$labFinal->getPiDummy()."<br>";

                if( $grantEffortDb ) {
                    //echo "exist pi=".$piDb->getPi()."<br>";
                    $grantEffortDb->setAuthor($user);
                } else {
                    //echo "does not exist pi <br>";
                    $grantFinal->setEffort($grantEffortDb,$user);
                }

            } else {

                if( $grantEffortDb ) {
                    $grantFinal->removeEffort($grantEffortDb);
                    $em->remove($grantEffortDb);
                }

            }


            //check if comment authored by $user for this lab already exists
            $commentDb = $em->getRepository('OlegUserdirectoryBundle:ResearchLabComment')->findOneBy( array( 'author' => $user, 'grant'=>$grantFinal->getId() ) );

            if( $grantFinal->getCommentDummy() && $grantFinal->getCommentDummy() != '' ) {

                if( $commentDb ) {
                    //echo "exist comment=".$commentDb->getComment()."<br>";
                    $commentDb->setComment($grantFinal->getCommentDummy());
                } else {
                    //echo "does not exist comment <br>";
                    $grantFinal->setComment($grantFinal->getCommentDummy(),$user);
                }

            } else {
                if( $commentDb ) {
                    $grantFinal->removeComment($commentDb);
                    $em->remove($commentDb);
                }
            }
            //echo "comments 2=".count($labFinal->getComments())."<br>";

            //foreach( $labFinal->getComments() as $comment ) {
            //    echo $comment;
            //}

        } //foreach

        //echo "labs final count=".count($user->getResearchLabs())."<br>";
        //$labOrig = $em->getRepository('OlegUserdirectoryBundle:ResearchLab')->find(21);
        //echo "original form labOrig id=21: name=".$labOrig->getName().", id=".$labOrig->getId()."<br>";

        //exit('process lab');

        return $user;
    }




    //remove effort and comment for Grant
    public function removeDependents($subjectUser,$grant) {

        //echo "remove user=".$subjectUser.", lab=".$lab->getId()."<br>";

        if( !($grant instanceof Grant) ) {
            //echo 'not grant object <br>';
            return;
        }

        $em = $this->_em;

        $commentDb = $em->getRepository('OlegUserdirectoryBundle:GrantComment')->findOneBy( array( 'author' => $subjectUser->getId(), 'grant'=>$grant->getId() ) );
        if( $commentDb ) {
            echo "remove comment=".$commentDb."<br>";
            $em->remove($commentDb);
            $em->flush();
        }

        $effortDb = $em->getRepository('OlegUserdirectoryBundle:GrantEffort')->findOneBy( array( 'author'=>$subjectUser->getId(), 'grant'=>$grant->getId() ) );
        if( $effortDb ) {
            echo "remove pi=".$effortDb."<br>";
            $em->remove($effortDb);
            $em->flush();
        }

        //exit('remove lab');

    }

}

