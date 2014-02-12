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

        $em = $this->_em;
        $em->persist($slide);

        if( $orderinfo == null ) {
            return $slide;
        }

        if( !$slide->getProvider() ) {
            //echo "set slide provider=".$orderinfo->getProvider()->first()."<br>";
            $slide->setProvider($orderinfo->getProvider()->first());
        }

        $slide = $em->getRepository('OlegOrderformBundle:Slide')->processFieldArrays($slide,$orderinfo,$original);

        $scans = $slide->getScan();
        foreach( $scans as $scan ) {
            $scan->setProvider($orderinfo->getProvider()->first());
            $scan = $em->getRepository('OlegOrderformBundle:Slide')->processFieldArrays($scan,$orderinfo,$original);
        } //scan

        $stains = $slide->getStain();
        foreach( $stains as $stain ) {
            $stain->setProvider($orderinfo->getProvider()->first());
            $stain = $em->getRepository('OlegOrderformBundle:Slide')->processFieldArrays($stain,$orderinfo,$original);
        } //stain

        //this does not work on postgresql because id is set before creating a new element in DB (before flush)
        if( !$slide->getId() || $slide->getId() == "" ) {
            $orderinfo->addSlide($slide);
        } else {
            //the potential parent of this slide is an empty branch for this orderinfo (it does not have a slide belonging to this order), so remove all parents from orderinfo
            //do this removal in part and block repo
        }

        return $slide;
    }
    
}

?>
