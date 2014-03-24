<?php

namespace Oleg\OrderformBundle\Repository;

use Doctrine\ORM\EntityRepository;


class EducationalRepository extends EntityRepository {

    public function processEntity( $entity ) {

        $cwid = null;
        $user = null;
        $em = $this->_em;

        $str = $entity->getDirectorstr();
        //echo "str=".$str."<br>";

        if( is_int($str) ) {

            //here $str is the user id
            $user = $em->getRepository('OlegOrderformBundle:User')->findOneById($str);

        } else {

            //get cwid
            $strArr = explode(" ",$str);

            if( count($strArr) > 0 ) {
                $cwid = $strArr[0];
            }

            if( $cwid ) {
                //echo "cwid=".$cwid."<br>";
                $user = $em->getRepository('OlegOrderformBundle:User')->findOneByUsername($cwid);
            }

        }

        if( $user ) {
            //echo "user=".$user."<br>";
            $entity->setDirector($user);
        }

        //exit('educ rep');
        return $entity;
    }

}
