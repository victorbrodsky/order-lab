<?php

//use to create some complex queries

namespace Oleg\OrderformBundle\Repository;

use Doctrine\ORM\EntityRepository;

class SlideRepository extends ArrayFieldAbstractRepository {
    
    //Make new - no requirements for uniqueness.
    public function processEntity( $entity, $orderinfo=null ) {
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

        return $slide;
    }

    //$parent is slide. Slide does not have children.
    public function replaceDuplicateEntities($parent,$orderinfo) {
        return $parent;
    }

}



?>
