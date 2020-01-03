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

namespace App\UserdirectoryBundle\Repository;

use Doctrine\ORM\EntityRepository;
use App\OrderformBundle\Entity\Research;
use App\UserdirectoryBundle\Entity\ResearchLab;


class ResearchLabRepository extends EntityRepository {


    public function processResearchLab( $user ) {

        $em = $this->_em;

        $labs = $user->getResearchLabs();
        //echo "labs count=".count($labs)."<br>";

        foreach( $labs as $lab ) {

            //echo "<br> lab name=".$lab->getName().", id=".$lab->getId()."<br>";

            if( !($lab && $lab->getName() && $lab->getName() != "") ) {
                $user->removeResearchLab($lab);
                //echo "lab has no name => continue to the next research lab object <br>";
                continue;
            }

            //$formLabId = $lab->getId();

            //get lab from DB if exists
            $labDb = $em->getRepository('AppUserdirectoryBundle:ResearchLab')->findOneByName($lab->getName()."");

            if( $labDb ) {

                //echo "found lab in DB name=".$lab->getName().", id=".$lab->getId()."<br>";

                //merge db and form entity
                $labDb->setPiDummy($lab->getPiDummy());
                $labDb->setCommentDummy($lab->getCommentDummy());

                $user->removeResearchLab($lab);
                $user->addResearchLab($labDb);

                //$em->detach($lab);
                //$em->detach($labDb);

                $labFinal = $labDb;

                //echo "lab dummy: id=".$lab->getId().", pi=".$lab->getPiDummy().", comment=".$lab->getCommentDummy()."<br>";
            } else {
                $labFinal = $lab;
            }

            //set pi
            //echo "pis1=".count($labFinal->getPis())."<br>";

            //check if pi=$user for this lab already exists
            if( $user->getId() ) {
                $piDb = $em->getRepository('AppUserdirectoryBundle:ResearchLabPI')->findOneBy( array( 'pi'=>$user, 'researchLab'=>$labFinal->getId() ) );
            } else {
                $piDb = null;
            }
            
            if( $labFinal->getPiDummy() && $labFinal->getPiDummy() == true ) {
                //echo "lab pi=".$labFinal->getPiDummy()."<br>";

                if( $piDb ) {
                    //echo "exist pi=".$piDb->getPi()."<br>";
                    $piDb->setPi($user);
                } else {
                    //echo "does not exist pi <br>";
                    $labFinal->setPiUser($user);
                }

            } else {

                if( $piDb ) {
                    $labFinal->removePi($piDb);
                    $em->remove($piDb);
                }

            }

            //echo "pis2=".count($labFinal->getPis())."<br>";

            //set comment
            //echo "comments 1=".count($labFinal->getComments())."<br>";

            //check if comment authored by $user for this lab already exists
            if( $user->getId() ) {
                $commentDb = $em->getRepository('AppUserdirectoryBundle:ResearchLabComment')->findOneBy( array( 'author' => $user, 'researchLab'=>$labFinal->getId() ) );
            } else {
                $commentDb = null;
            }
            
            if( $labFinal->getCommentDummy() && $labFinal->getCommentDummy() != "" ) {
                //echo "lab comment=".$labFinal->getCommentDummy()."<br>";

                if( $commentDb ) {
                    //echo "exist comment=".$commentDb->getComment()."<br>";
                    $commentDb->setComment($labFinal->getCommentDummy());
                } else {
                    //echo "does not exist comment <br>";
                    $labFinal->setComment($labFinal->getCommentDummy(),$user);
                }

            } else {
                if( $commentDb ) {
                    $labFinal->removeComment($commentDb);
                    $em->remove($commentDb);
                }
            }
            //echo "comments 2=".count($labFinal->getComments())."<br>";

            //foreach( $labFinal->getComments() as $comment ) {
            //    echo $comment;
            //}

        } //foreach

        //echo "labs final count=".count($user->getResearchLabs())."<br>";
        //$labOrig = $em->getRepository('AppUserdirectoryBundle:ResearchLab')->find(21);
        //echo "original form labOrig id=21: name=".$labOrig->getName().", id=".$labOrig->getId()."<br>";

        //exit('process lab');

        return $user;
    }




    //remove pi and comment for Research Lab
    public function removeDependents($subjectUser,$lab) {

        //echo "remove user=".$subjectUser.", lab=".$lab->getId()."<br>";

        if( !($lab instanceof ResearchLab) ) {
            //echo 'not research lab object <br>';
            return;
        }

        $em = $this->_em;

        $commentDb = $em->getRepository('AppUserdirectoryBundle:ResearchLabComment')->findOneBy( array( 'author' => $subjectUser->getId(), 'researchLab'=>$lab->getId() ) );
        if( $commentDb ) {
            //echo "remove comment=".$commentDb."<br>";
            $em->remove($commentDb);
            $em->flush();
        }

        $piDb = $em->getRepository('AppUserdirectoryBundle:ResearchLabPI')->findOneBy( array( 'pi'=>$subjectUser->getId(), 'researchLab'=>$lab->getId() ) );
        if( $piDb ) {
            //echo "remove pi=".$piDb."<br>";
            $em->remove($piDb);
            $em->flush();
        }

        //exit('remove lab');

    }

}

