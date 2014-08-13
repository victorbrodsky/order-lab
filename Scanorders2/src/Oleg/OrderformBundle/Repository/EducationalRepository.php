<?php

namespace Oleg\OrderformBundle\Repository;

//use Doctrine\ORM\EntityRepository;


class EducationalRepository extends ListAbstractRepository {

    public function processEntity( $orderinfo, $user ) {

        $educational = $orderinfo->getEducational();

        if( !$educational || $educational->isEmpty() ) {
            $orderinfo->setEducational(NULL);
            return $orderinfo;
        }

        //process Course Title
        $courseTitle = $this->convertStrToObject( $educational->getCourseTitleStr(), 'CourseTitleList', $user );
        $educational->setCourseTitle($courseTitle);
        //echo "CourseTitle name=".$courseTitle->getName()."<br>";

        //echo "LessonTitleStr=".$educational->getLessonTitleStr()."<br>";

        //process Set Title
        $lessonTitle = $this->convertStrToObject( $educational->getLessonTitleStr(), 'LessonTitleList', $user, 'courseTitle', $courseTitle->getId() );

        //process principals and primary principal
        $this->processDirectors( $educational, $courseTitle );
        //exit();

        //set this new LessonTitle to Educational and CourseTitle objects
        $courseTitle->addLessonTitle($lessonTitle);

        return $orderinfo;
    }


    //inputs: source $Educational, destination CourseTitle
    public function processDirectors( $educational, $foundcourseTitle ) {

        $directorWrappers = $educational->getDirectorWrappers();

        foreach( $directorWrappers as $directorWrapper ) {

            $directorstr = $directorWrapper->getDirectorStr();
            //echo "directorstr=".$directorstr."<br>";
            $foundDirector = $this->_em->getRepository('OlegOrderformBundle:DirectorList')->findOneByName($directorstr);

            if( !$foundDirector ) {
                throw new \Exception( 'Director was not found with name '.$directorstr );
            }

            $foundcourseTitle->addDirector($foundDirector);
            $foundDirector->addCourse($foundcourseTitle);

            //set primaryDirector as a first Director
            if( $directorWrappers->first() ) {
                if( !$foundcourseTitle->getPrimaryDirector() ) {
                    $foundcourseTitle->setPrimaryDirector( $directorWrappers->first()->getDirector()->getId() );
                    $educational->setPrimarySet( $directorWrappers->first()->getDirector()->getName() );
                }
            } else {
                $foundcourseTitle->setPrimaryDirector( NULL );
            }

        }//foreach

    }

}
