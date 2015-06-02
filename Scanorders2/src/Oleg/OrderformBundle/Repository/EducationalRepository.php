<?php

namespace Oleg\OrderformBundle\Repository;

//use Doctrine\ORM\EntityRepository;

use Oleg\UserdirectoryBundle\Repository\ListAbstractRepository;

class EducationalRepository extends ListAbstractRepository {

    public function processEntity( $message, $user ) {

        $educational = $message->getEducational();

        if( !$educational || $educational->isEmpty() ) {
            $message->setEducational(NULL);
            return $message;
        }

        //process Course Title
        $objectParams = array(
            'className' => 'CourseTitleList',
            'fullClassName' => "Oleg\\OrderformBundle\\Entity\\"."CourseTitleList",
            'fullBundleName' => 'OlegOrderformBundle'
        );
        $courseTitle = $this->convertStrToObject( $educational->getCourseTitleStr(), $objectParams, $user );
        $educational->setCourseTitle($courseTitle);
        //echo "CourseTitle name=".$courseTitle->getName()."<br>";

        //echo "LessonTitleStr=".$educational->getLessonTitleStr()."<br>";

        //process Set Title
        $objectParams = array(
            'className' => 'LessonTitleList',
            'fullClassName' => "Oleg\\OrderformBundle\\Entity\\"."LessonTitleList",
            'fullBundleName' => 'OlegOrderformBundle'
        );
        $lessonTitle = $this->convertStrToObject( $educational->getLessonTitleStr(), $objectParams, $user, 'courseTitle', $courseTitle->getId() );

        //process principals and primary principal
        $this->processDirectors( $educational, $courseTitle );
        //exit();

        //set this new LessonTitle to Educational and CourseTitle objects
        $courseTitle->addLessonTitle($lessonTitle);

        return $message;
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
