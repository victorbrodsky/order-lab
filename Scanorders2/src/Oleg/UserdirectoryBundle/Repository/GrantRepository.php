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

namespace Oleg\UserdirectoryBundle\Repository;


use Doctrine\ORM\EntityRepository;


class GrantRepository extends EntityRepository {


    public function processGrant( $user ) {

        $em = $this->_em;

        $grants = $user->getGrants();

        foreach( $grants as $grant ) {

            if( $grant->isEmpty() ) {
                $user->removeGrant($grant);
                //echo "Remove empty Grant: ".$grant."<br>";
                continue;
            }

            //echo "Process Grant: ".$grant."<br>";

            //get grant from DB if exists
            $grantDb = $em->getRepository('OlegUserdirectoryBundle:Grant')->findOneByName($grant->getName());

            //echo "grantDb: ".$grantDb."<br>";
            //exit('1');

            if( $grantDb ) {

                //echo "found grant in DB by name=".$grant->getName().", id=".$grant->getId()."<br>";

                //merge db and form entity
                $grantDb->setEffortDummy($grant->getEffortDummy());
                $grantDb->setCommentDummy($grant->getCommentDummy());

                $user->removeGrant($grant);
                $user->addGrant($grantDb);

                $grantFinal = $grantDb;

                //echo "grant dummy: id=".$grant->getId().", pi=".$grant->getPiDummy().", comment=".$grant->getCommentDummy()."<br>";
            } else {
                $grantFinal = $grant;
            }

            //echo "grantFinal: ".$grantFinal."<br>";

            //check if effort already exists
            if( $user->getId() ) {
                $grantEffortDb = $em->getRepository('OlegUserdirectoryBundle:GrantEffort')->findOneBy( array( 'author'=>$user, 'grant'=>$grantFinal->getId() ) );
            } else {
                $grantEffortDb = null;
            }
            
            if( $grantFinal->getEffortDummy() ) {
                //echo "grant effort=".$grantFinal->getEffortDummy()."<br>";

                if( $grantEffortDb ) {
                    //echo "exist effort=".$grantEffortDb->getEffort()."<br>";
                    $grantEffortDb->setAuthor($user);
                } else {
                    //echo "does not exist effort <br>";
                    $grantFinal->setEffort($grantFinal->getEffortDummy(),$user);
                }

            } else {
                //echo "no dummy effort=".$grantFinal->getEffortDummy()."<br>";

                if( $grantEffortDb ) {
                    $grantFinal->removeEffort($grantEffortDb);
                    $em->remove($grantEffortDb);
                }

            }


            //check if comment authored by $user for this grant already exists
            if( $user->getId() ) {
                $commentDb = $em->getRepository('OlegUserdirectoryBundle:GrantComment')->findOneBy( array( 'author' => $user, 'grant'=>$grantFinal->getId() ) );
            } else {
                $commentDb = null;
            } 
                
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
            //echo "comments 2=".count($grantFinal->getComments())."<br>";

            //foreach( $grantFinal->getComments() as $comment ) {
            //    //echo $comment;
            //}


            //process attachment documents
            if( $grantFinal->getAttachmentContainer() ) {
                foreach( $grantFinal->getAttachmentContainer()->getDocumentContainers() as $documentContainer) {
                    //echo "Doc Container ID=".$documentContainer->getId()."<br>";
                    $res = $em->getRepository('OlegUserdirectoryBundle:Document')->processDocuments( $documentContainer, null, null, $grantFinal );
                    if( $res === null ) {
                        //if res is null (no documents and attachmentContainer is empty), check for empty again.
                        if( $grant->isEmpty() ) {
                            $user->removeGrant($grant);
                            //echo "Remove empty Grant: ".$grant."<br>";
                            continue;
                        }
                    }
                }
            }

            //echo "after document processing: grant=".$grantFinal."<br>";

        } //foreach grant

        //echo "###grants final count=".count($user->getGrants())."<br>";
        //echo "effort count=".count($user->getGrants()->first()->getEfforts())."<br>";
        //exit('process grant');

        return $user;
    }




    //remove effort and comment for Grant
    public function removeDependents($subjectUser,$grant) {

        //echo "remove user=".$subjectUser.", grant=".$grant->getId()."<br>";
        //exit('1');

        if( !($grant instanceof Grant) ) {
            //echo 'not grant object <br>';
            return;
        }

        $em = $this->_em;

        $commentDb = $em->getRepository('OlegUserdirectoryBundle:GrantComment')->findOneBy( array( 'author' => $subjectUser->getId(), 'grant'=>$grant->getId() ) );
        if( $commentDb ) {
            //echo "remove comment=".$commentDb."<br>";
            $em->remove($commentDb);
            $em->flush();
        }

        $effortDb = $em->getRepository('OlegUserdirectoryBundle:GrantEffort')->findOneBy( array( 'author'=>$subjectUser->getId(), 'grant'=>$grant->getId() ) );
        if( $effortDb ) {
            //echo "remove effort=".$effortDb."<br>";
            $em->remove($effortDb);
            $em->flush();
        }

        //remove documents
        if( 0 ) {   //document belongs to grant, not to a user! => don't remove documents here!
            if( $grant->getAttachmentContainer() ) {

                foreach( $grant->getAttachmentContainer()->getDocumentContainers() as $documentContainer) {
                    //$em->getRepository('OlegUserdirectoryBundle:Document')->processDocuments( $documentContainer );

                    foreach( $documentContainer->getDocuments() as $document ) {

                        if( $document && $document->getId() ) {
                            $documentPath = $document->getServerPath();
                            $documentContainer->removeDocument($document);

                            //remove file from folder
                            if( is_file($documentPath) ) {
                                unlink($documentPath);
                            }

                            $em->remove($document);
                        }

                    } //foreach document

                    $grant->getAttachmentContainer()->removeDocumentContainer($documentContainer);
                    $em->remove($documentContainer);

                } //foreach documentContainer

                $em->remove($grant->getAttachmentContainer());

                $grant->setAttachmentContainer(null);

                $em->flush();

            }
        } //if

        //exit('remove grant');

    }

}

