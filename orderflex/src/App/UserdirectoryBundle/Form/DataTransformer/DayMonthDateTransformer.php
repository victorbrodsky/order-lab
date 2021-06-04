<?php
/**
 * Copyright (c) 2017 Cornell University
 *
 *  Licensed under the Apache License, Version 2.0 (the "License");
 *  you may not use this file except in compliance with the License.
 *  You may obtain a copy of the License at
 *
 *  http://www.apache.org/licenses/LICENSE-2.0
 *
 *  Unless required by applicable law or agreed to in writing, software
 *  distributed under the License is distributed on an "AS IS" BASIS,
 *  WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 *  See the License for the specific language governing permissions and
 *  limitations under the License.
 */

/**
 * Created by JetBrains PhpStorm.
 * User: oli2002
 * Date: 9/12/13
 * Time: 3:47 PM
 * To change this template use File | Settings | File Templates.
 */

namespace App\UserdirectoryBundle\Form\DataTransformer;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Form\Extension\Core\DataTransformer\DateTimeToStringTransformer;

class DayMonthDateTransformer implements DataTransformerInterface
{
//    /**
//     * @var ObjectManager
//     */
//    private $em;
//    private $user;
//
//    /**
//     * @param ObjectManager $om
//     */
//    public function __construct(EntityManagerInterface $em=null, $user=null)
//    {
//        $this->em = $em;
//        $this->user = $user;
//    }

    //https://stackoverflow.com/questions/40463364/disable-days-and-month-from-symfony-datetypeclass

    /**
     * {@inheritdoc}
     */
    public function transform($value)
    {

        //dump($value);
        //exit();

        return $value;
    }

//    /**
//     * Transforms a string (number) to an object.
//     * {@inheritdoc}
//     */
//    public function reverseTransform($text)
//    {
//        //echo "data reverseTransform text<br>";
//        //echo "data reverseTransform text=".$text."<br>";
//
////        array:3 [
////          "year" => null
////          "month" => null
////          "day" => null
////        ]
//        //dump($text);
//        //exit();
//
//        if( !$text ) {
//            return null;
//        }
//
//        //convert mm/yyyy to dd/mm/yyyy accepted by symfony
//        $datetime = $text;
//
//        $textArr = explode("/",$datetime);
//        $day = $textArr[0];
//        $month = $textArr[1];
//        //$year = $textArr[2];
//        $year = intval(date("Y"));
//
//        if( !$day || !$month ) {
//            throw new \TransformationFailedException( 'Month or year are empty: day=' . $day . ', month=' . $month );
//        }
//
//        //construct date as mm/dd/yyy
//        $datetime = $month . "/" . $day . "/" . $year;
//
//        $date = new \DateTime($datetime);
//
//        //echo "date=".$date."<br>";
//
//        return $date;
//    }


    /**
     * Transforms a string (number) to an object.
     * {@inheritdoc}
     */
    public function reverseTransform($value)
    {
        if (!is_array($value)) {
            return $value;
        }

        //dump($value);
        //exit('111');
//        array:3 [
//          "year" => null
//          "month" => 7
//          "day" => 1
//        ]

        $day = $value["day"];
        $month = $value["month"];
        $year = $value["year"];

        if( empty($day) && empty($month) ) {
            $year = $value["year"] = NULL;
            return $value;
        }

        if( empty($year) ) {
            $year = intval(date("Y"));
            $value["year"] = $year;
        }

        if( !$year ) {
            $year = intval(date("Y"));
            $value["year"] = $year;
        }

        //check maximum number of days for mont, year
        $maxdays = cal_days_in_month(CAL_GREGORIAN, $month, $year);
        if( intval($day) > $maxdays ) {
            $day = $maxdays;
            $value["day"] = $day;
        }

//        echo "year=".$year."<br>";

//        dump($value);
        //exit('111');

//        if (empty($value['year'])) {
//            exit('111');
//        }

//        $datetime = "6" . "/" . "31" . "/" . "2021";
//        $date = new \DateTime($datetime);
//        echo "date=".$date->format("m/d/Y")."<br>";
//        exit('111');
//
        return $value;

//        //construct date as mm/dd/yyy
//        $datetime = $month . "/" . $day . "/" . $year;
//
//        $date = new \DateTime($datetime);
//        echo "date=".$date->format("m/d/Y")."<br>";
//
//        if( intval($day) > 30 ) {
//            echo "day=".$day."<br>";
//            echo "date=".$date->format("m/d/Y")."<br>";
//            exit('111');
//        }
//
//        return $date;
    }

}