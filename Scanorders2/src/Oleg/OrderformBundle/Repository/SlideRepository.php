<?php

//use to create some complex queries

namespace Oleg\OrderformBundle\Repository;

use Doctrine\ORM\EntityRepository;

class SlideRepository extends ArrayFieldAbstractRepository {

    //Make new - no requirements for uniqueness.
    public function processEntity( $entity, $orderinfo=null, $original=null ) {

        //set default source if empty
        if( !$entity->getSource() ) {
            $entity->setSource($this->source);
        }

        return $this->setResult( $entity, $orderinfo, null );
    }
    
    public function setResult( $slide, $orderinfo, $original=null ) {

        //echo $slide;

        //Clean empty array fields
        $slide->cleanEmptyArrayFields();

        $em = $this->_em;
        $em->persist($slide);

        if( $orderinfo == null ) {
            return $slide;
        }

        if( !$slide->getProvider() ) {
            //echo "set slide provider=".$orderinfo->getProvider()."<br>";
            $slide->setProvider($orderinfo->getProvider());
        }

        $slide = $em->getRepository('OlegOrderformBundle:Slide')->processFieldArrays($slide,$orderinfo,$original);

        $scans = $slide->getScan();
        foreach( $scans as $scan ) {
            $scan->setProvider($orderinfo->getProvider());
            $scan = $em->getRepository('OlegOrderformBundle:Slide')->processFieldArrays($scan,$orderinfo,$original);
        } //scan

        $stains = $slide->getStain();
        foreach( $stains as $stain ) {
            $stain->setProvider($orderinfo->getProvider());
            $stain = $em->getRepository('OlegOrderformBundle:Slide')->processFieldArrays($stain,$orderinfo,$original);
        } //stain

        unset($original);

        //this does not work on postgresql because id is set before creating a new element in DB (before flush)
        if( !$slide->getId() || $slide->getId() == "" ) {
            $orderinfo->addSlide($slide);
        }

        //add educational and research
        //********** take care of educational and research director and principal investigator ***********//
        if( $orderinfo->getEducational() && !$orderinfo->getEducational()->isEmpty() ) {
            $orderinfo->getEducational()->addSlide($slide);
        }

        if( $orderinfo->getResearch() && !$orderinfo->getResearch()->isEmpty() ) {
            $orderinfo->getResearch()->addSlide($slide);
        }
        //********** end of educational and research processing ***********//

        //add this slide to institution from orderinfo.
        //$orderinfo->getInstitution()->addSlide($slide);
        $slide->setInstitution($orderinfo->getInstitution());

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
        $processedParent = $em->getRepository('OlegOrderformBundle:'.$className)->processEntity($parent, $orderinfo, null);
        $slide->setParent($processedParent);
        ////////////// EOF process parent //////////////

        return $slide;
    }

    //$parent is slide. Slide does not have children.
    public function replaceDuplicateEntities($parent,$orderinfo) {
        return $parent;
    }

}



?>
