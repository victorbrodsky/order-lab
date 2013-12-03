<?php

//use to create some complex queries

namespace Oleg\OrderformBundle\Repository;

use Doctrine\ORM\EntityRepository;

class SlideRepository extends EntityRepository {
    
    //Make new - no requirements for uniqueness.
    public function processEntity( $entity, $orderinfo=null ) {
        return $this->setResult( $entity, $orderinfo );
    }
    
    public function setResult( $slide, $orderinfo=null ) {
        
        $em = $this->_em;
        $em->persist($slide);

        if( $orderinfo == null ) {
            return $slide;
        }

        //Note: stcan and stain are persisted by Slide entity annotations

//        $scans = $slide->getScan();
//        foreach( $scans as $scan ) {
//            $scan = $em->getRepository('OlegOrderformBundle:Scan')->processEntity( $scan );
//        }
        
        return $slide;
    }
    
//    public function notExists($entity) {
//        $id = $entity->getId();
//        if( !$id ) {
//            return true;
//        }
//        $em = $this->_em;
//        $found = $em->getRepository('OlegOrderformBundle:Slide')->findOneById($id);
//        if( null === $found ) {
//            return true;
//        } else {
//            return false;
//        }
//    }
    
}

?>
