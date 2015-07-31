<?php

namespace Oleg\OrderformBundle\Repository;

//use Doctrine\ORM\EntityRepository;

use Oleg\UserdirectoryBundle\Repository\ListAbstractRepository;

class EducationalRepository extends ListAbstractRepository {


    public function processEntity( $message, $user ) {

        $educational = $message->getEducational();

        //echo "educational=".$educational."<br>";

        if( !$educational || $educational->isEmpty() ) {
            $message->setEducational(NULL);
            //echo "educational is empty<br>";
            //exit();
            return $message;
        }

        foreach( $educational->getUserWrappers() as $userWrapper ) {
            //echo "courseTitle=".$educational->getCourseTitle()."<br>";
            if( $educational->getCourseTitle() ) {
                $educational->getCourseTitle()->addUserWrapper($userWrapper);
            }
        }

        //exit();
        return $message;
    }

}
