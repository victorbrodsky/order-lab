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
    /**
     * @var ObjectManager
     */
    private $em;
    private $user;

    /**
     * @param ObjectManager $om
     */
    public function __construct(EntityManagerInterface $em=null, $user=null)
    {
        $this->em = $em;
        $this->user = $user;
    }

    /**
     * Transforms an object to a string.
     */
    public function transform($date)
    {
        //echo "string data transformer: ".$date."<br>";
        if (null === $date) {
            //echo "return empty <br>";
            return "";
        }

        //exit('111');
        //return $date;

        $transformer = new DateTimeToStringTransformer(null,null,'d/m');
        $dateStr = $transformer->transform($date);

        //echo "return dateStr:".$dateStr." <br>";
        //exit('111');

        return $dateStr;
    }

    /**
     * Transforms a string (number) to an object.
     */
    public function reverseTransform($text)
    {
        //echo "data reverseTransform text=".$text."<br>";
        //exit();

        if( !$text ) {
            return null;
        }

        //convert mm/yyyy to dd/mm/yyyy accepted by symfony
        $datetime = $text;

        $textArr = explode("/",$datetime);
        $day = $textArr[0];
        $month = $textArr[1];
        //$year = $textArr[2];
        $year = intval(date("Y"));

        if( !$day || !$month ) {
            throw new \TransformationFailedException( 'Month or year are empty: day=' . $day . ', month=' . $month );
        }

        //construct date as mm/dd/yyy
        $datetime = $month . "/" . $day . "/" . $year;

        $date = new \DateTime($datetime);

        //echo "date=".$date."<br>";

        return $date;
    }

}