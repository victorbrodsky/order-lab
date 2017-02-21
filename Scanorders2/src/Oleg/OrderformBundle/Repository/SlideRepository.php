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

//use to create some complex queries

namespace Oleg\OrderformBundle\Repository;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityRepository;


class SlideRepository extends ArrayFieldAbstractRepository {

    //Make new - no requirements for uniqueness.
    public function processEntity( $entity, $message=null, $original=null ) {

        //set default source if empty
        if( !$entity->getSource() ) {
            $entity->setSource($this->source);
        }

        return $this->setResult( $entity, $message, null );
    }
    
    public function setResult( $slide, $message, $original=null ) {

        //echo $slide;

        //Clean empty array fields
        $slide->cleanEmptyArrayFields();

        $em = $this->_em;
        $em->persist($slide);

        if( $message == null ) {
            return $slide;
        }

        if( !$slide->getProvider() ) {
            //echo "set slide provider=".$message->getProvider()."<br>";
            $slide->setProvider($message->getProvider());
        }

        $slide = $em->getRepository('OlegOrderformBundle:Slide')->processFieldArrays($slide,$message,$original);

        $scans = $slide->getScan();
        foreach( $scans as $scan ) {
            $scan->setProvider($message->getProvider());
            $scan = $em->getRepository('OlegOrderformBundle:Slide')->processFieldArrays($scan,$message,$original);
            $scan->setInstitution($message->getInstitution());
            $message->addImaging($scan);
        } //scan

        $stains = $slide->getStain();
        foreach( $stains as $stain ) {
            $stain->setProvider($message->getProvider());
            $stain = $em->getRepository('OlegOrderformBundle:Slide')->processFieldArrays($stain,$message,$original);
        } //stain

        unset($original);

        //this does not work on postgresql because id is set before creating a new element in DB (before flush)
        if( !$slide->getId() || $slide->getId() == "" ) {
            $message->addSlide($slide);
        }

        //add educational and research
        //********** take care of educational and research director and principal investigator ***********//
        if( $message->getEducational() && !$message->getEducational()->isEmpty() ) {
            $message->getEducational()->addSlide($slide);
        }

        if( $message->getResearch() && !$message->getResearch()->isEmpty() ) {
            $message->getResearch()->addSlide($slide);
        }
        //********** end of educational and research processing ***********//

        //add this slide to institution from message.
        //$message->getInstitution()->addSlide($slide);
        $slide->setInstitution($message->getInstitution());

        ////////////// process parent //////////////
        //1) reattach slide to part if it is Cytopathology
        if( $slide->getSlidetype() == "Cytopathology" ) {
            //echo "Cytopathology => attach slide to part<br>";
            $block = $slide->getBlock();
            $part = $block->getPart();
            $slide->setParent($part);
            $part->addSlide($slide);
            $block->removeSlide($slide);
            //remove block from part, if block does not have any slides
            if( count($block->getSlide()) == 0 ) {
                $part->removeBlock($block);
            }
        }
        //2) process parent
        $parent = $slide->getParent();
        $class = new \ReflectionClass($parent);
        $className = $class->getShortName();
        $processedParent = $em->getRepository('OlegOrderformBundle:'.$className)->processEntity($parent, $message, null);
        $slide->setParent($processedParent);
        ////////////// EOF process parent //////////////

        return $slide;
    }


    public function findSlidesByInstAccessionPartBlock($institution,$accessionTypeStr,$accessionStr,$partStr,$blockStr) {

        $slides = new ArrayCollection();

        $block = $this->_em->getRepository('OlegOrderformBundle:Block')->findOneByInstAccessionPartBlock($institution,$accessionTypeStr,$accessionStr,$partStr,$blockStr);

        if( $block ) {
            foreach( $block->getSlide() as $slide ) {
                if( !$slides->contains($slide) ) {
                    $slides->add($slide);
                }
            }
        }

        //echo "bloc's slide count=".count($slides)."<br>";

        $part = $this->_em->getRepository('OlegOrderformBundle:Part')->findOneByInstAccessionPart($institution,$accessionTypeStr,$accessionStr,$partStr);

        if( $part ) {
            foreach( $part->getSlide() as $slide ) {
                if( !$slides->contains($slide) ) {
                    $slides->add($slide);
                }
            }
        }

        //echo "part's slide count=".count($slides)."<br>";

        return $slides;
    }

    public function findSlidesByInstAccession($institution,$accessionTypeStr,$accessionStr) {

        $slides = new ArrayCollection();

        $accessiontype = $this->_em->getRepository('OlegOrderformBundle:AccessionType')->findOneByName($accessionTypeStr);

        $extra = array();
        $extra["keytype"] = $accessiontype->getId();

        $institutions = array();
        $institutions[] = $institution;
        $validity = array(self::STATUS_VALID,self::STATUS_RESERVED);
        $single = true;

        $accession = $this->_em->getRepository('OlegOrderformBundle:Accession')->findOneByIdJoinedToField(
            $institutions,
            $accessionStr,
            "Accession",
            "accession",
            $validity,
            $single,
            $extra
        );

        if( $accession ) {

            foreach( $accession->getPart() as $part ) {

                //get part's slides
                foreach( $part->getSlide() as $slide ) {
                    if( !$slides->contains($slide) ) {
                        $slides->add($slide);
                    }
                }

                //get block's slides
                foreach( $part->getBlock() as $block ) {
                    foreach( $block->getSlide() as $slide ) {
                        if( !$slides->contains($slide) ) {
                            $slides->add($slide);
                        }
                    }
                }

            }

        }

        return $slides;
    }

    //$parent is slide. Slide does not have children.
    public function replaceDuplicateEntities($parent,$message) {
        return $parent;
    }

}



?>
