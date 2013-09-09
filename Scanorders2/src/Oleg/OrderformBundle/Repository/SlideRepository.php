<?php

//use to create some complex queries

namespace Oleg\OrderformBundle\Repository;

use Doctrine\ORM\EntityRepository;

class SlideRepository extends EntityRepository {
    
    //Make new - no requirements for uniqueness.
    public function processEntity( $entity, $orderinfo=null ) {
          
        //create new           
//        $em = $this->_em;
//        $em->persist($entity);
        
        return $this->setResult( $entity, $orderinfo );
    }
    
    public function setResult( $slide, $orderinfo=null ) {
        
        $em = $this->_em;
        $em->persist($slide);

        if( $orderinfo == null ) {
            return $slide;
        }
        
        $scans = $slide->getScan();
        foreach( $scans as $scan ) {          
            if( !$scan->getId() ) {
                $slide->removeScan( $scan );
                $scan = $em->getRepository('OlegOrderformBundle:Scan')->processEntity( $scan );
                $slide->addScan($scan);
                $orderinfo->addScan($scan);
            } else {
                continue;
            }
        } //scan

        $stains = $slide->getStain();
        foreach( $stains as $stain ) {
            if( !$stain->getId() ) {
                $slide->removeStain( $stain );
                $stain = $em->getRepository('OlegOrderformBundle:Stain')->processEntity( $stain );
                $slide->addStain($stain);
                $orderinfo->addStain($stain);
            } else {
                continue;
            }
        } //stain
               
        //$em->flush($slide);
        
        return $slide;
    }
    
}

?>
