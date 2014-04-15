<?php

namespace Oleg\OrderformBundle\Repository;

use Doctrine\ORM\EntityRepository;


class EducationalRepository extends EntityRepository {

    public function processEntity( $orderinfo ) {

        $entity = $orderinfo->getEducational();

        if( $entity->isEmpty() ) {
            $orderinfo->setEducational(NULL);
            return $orderinfo;
        }

        $cwid = null;
        $user = null;
        $em = $this->_em;

        $courseTitleName = $entity->getCourseTitle()->getName();
        $foundCourseTitle = $em->getRepository('OlegOrderformBundle:CourseTitleList')->findOneByName($courseTitleName);

        if( $foundCourseTitle ) {

            $originalCourseTitle = $entity->getCourseTitle();
            $originalLessonTitles = $originalCourseTitle->getLessonTitles();

            foreach( $originalLessonTitles as $lessontitle ) {
                $foundCourseTitle->addLessonTitles($lessontitle);
                $lessontitle->setCourseTitle($foundCourseTitle);
            }

            $entity->setCourseTitle( $foundCourseTitle );

            $orderinfo->setEducational($entity);

            return $orderinfo;

        } else {

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
                $orderinfo->setEducational($entity);
            }
        }

        //exit('educ rep');
        return $orderinfo;
    }

}
