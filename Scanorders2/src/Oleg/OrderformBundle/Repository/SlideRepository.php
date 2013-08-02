<?php

//use to create some complex queries

namespace Oleg\OrderformBundle\Repository;

use Doctrine\ORM\EntityRepository;

class SlideRepository extends EntityRepository {
    
    //Make new - no requirements for uniqueness.
    public function processEntity( $in_entity ) { 
          
        $in_entity->getScan()->setStatus("submitted");
        
        //create new           
        $em = $this->_em;
        $em->persist($in_entity);
        $em->flush();

        return $in_entity;
        
    }
    
}

?>
