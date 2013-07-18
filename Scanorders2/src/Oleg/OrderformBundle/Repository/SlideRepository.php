<?php

//use to create some complex queries

namespace Oleg\OrderformBundle\Repository;

use Doctrine\ORM\EntityRepository;

class SlideRepository extends EntityRepository {
    
    //TODO: remove it. It's a simple example
    public function findAllOrderedByName() {
        //$em = $this->getDoctrine()->getManager();

        //$entities = $em->getRepository('OlegOrderformBundle:Slide')->findAll();

        $entities = $this->findAll();
        
        return $entities;
    }
    
    public function createSlide( $entity ) {
        
        $em = $this->getDoctrine()->getManager();
        $em->persist($entity);
        $em->flush();
            
    }
    
}

?>
