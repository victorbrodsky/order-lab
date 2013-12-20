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
        
        $em = $this->_em;
        $em->persist($slide);

        if( $orderinfo == null ) {
            return $slide;
        }

        $slide->setProvider($orderinfo->getProvider()->first());

        $scans = $slide->getScan();
        foreach( $scans as $scan ) {
            $scan->setProvider($orderinfo->getProvider()->first());
        } //scan

        $stains = $slide->getStain();
        foreach( $stains as $stain ) {
            $scan->setProvider($orderinfo->getProvider()->first());
        } //stain
        
        return $slide;
    }
    
}

?>
