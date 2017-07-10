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

namespace Oleg\UserdirectoryBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity
 * @ORM\Table(name="user_objectTypeDateTime")
 */
class ObjectTypeDateTime extends ObjectTypeReceivingBase
{

    /**
     * @ORM\OneToMany(targetEntity="ObjectTypeDateTime", mappedBy="original")
     **/
    protected $synonyms;

    /**
     * @ORM\ManyToOne(targetEntity="ObjectTypeDateTime", inversedBy="synonyms")
     * @ORM\JoinColumn(name="original_id", referencedColumnName="id")
     **/
    protected $original;

    /**
     * @ORM\ManyToOne(targetEntity="FormNode", inversedBy="objectTypeDateTimes", cascade={"persist"})
     * @ORM\JoinColumn(name="formNode_id", referencedColumnName="id")
     */
    protected $formNode;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $value;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $datetimeValue;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $dateValue;

    /**
     * @ORM\Column(type="time", nullable=true)
     */
    private $timeValue;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $timezone;




    /**
     * @return mixed
     */
    public function getDatetimeValue()
    {
        return $this->datetimeValue;
    }

    /**
     * @param mixed $datetimeValue
     */
    public function setDatetimeValue($datetimeValue)
    {
        $this->datetimeValue = $datetimeValue;
    }

    /**
     * @return mixed
     */
    public function getTimeValue()
    {
        return $this->timeValue;
    }

    /**
     * @param mixed $timeValue
     */
    public function setTimeValue($timeValue)
    {
        $this->timeValue = $timeValue;
    }


    public function setTimeValueHourMinute($hour,$minute,$second=null)
    {
        //echo "hour=".$hour."; minute=".$minute."<br>";
        $datetimeValue = new \DateTime();

        $zero = 0; //"00"
        if( !$hour ) {
            $hour = $zero;
        }
        if( !$minute ) {
            $minute = $zero;
        }
        if( !$second ) {
            $second = $zero;
        }

        $datetimeValue->setTime($hour, $minute, $second);
        $this->setTimeValue($datetimeValue);
        //echo "time=".$this->getTimeValue()->format('h:i:s')."<br>";
    }

    public function setDateTimeValueDateHourMinute($timezone,$date,$hour,$minute,$second=null)
    {
        //echo "date=".$date."; hour=".$hour."; minute=".$minute."<br>";
        if( $date ) {
            //$date = 12/21/2016
            $datetimeValue = \DateTime::createFromFormat('m/d/Y', $date);
        } else {
            $datetimeValue = new \DateTime();
        }

        if( $timezone ) {
            //$datetimeValue->setTimezone(new \DateTimeZone($timezone)); //$timezone='Pacific/Chatham'
            $this->setTimezone($timezone);
        }

        $zero = 0; //"00"
        if( !$hour ) {
            $hour = $zero;
        }
        if( !$minute ) {
            $minute = $zero;
        }
        if( !$second ) {
            $second = $zero;
        }

        $datetimeValue->setTime($hour, $minute, $second);
        $this->setDatetimeValue($datetimeValue);
        //echo "time=".$this->getTimeValue()->format('h:i:s')."<br>";
    }



    /**
     * @return mixed
     */
    public function getDateValue()
    {
        return $this->dateValue;
    }

    /**
     * @param mixed $dateValue
     */
    public function setDateValue($dateValue)
    {
        $dateObjectValue = new \DateTime($dateValue);
        //$dateObjectValue->setDate($dateValue);
        $this->dateValue = $dateObjectValue;
    }

    /**
     * @return mixed
     */
    public function getTimezone()
    {
        return $this->timezone;
    }

    /**
     * @param mixed $timezone
     */
    public function setTimezone($timezone)
    {
        $this->timezone = $timezone;
    }




}