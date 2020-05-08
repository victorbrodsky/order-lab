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
 * Created by PhpStorm.
 * User: ch3
 * Date: 11/2/2016
 * Time: 3:39 PM
 */

namespace App\VacReqBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="vacreq_siteparameter")
 */
class VacReqSiteParameter
{

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $academicYearStart;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $academicYearEnd;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $holidaysUrl;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $vacationAccruedDaysPerMonth;

    /**
     * Maximum number vacation days per year (usually 12*2=24).
     * This should not be used for now, because we rely on the vacationAccruedDaysPerMonth.
     *
     * @ORM\Column(type="integer", nullable=true)
     */
    private $maxVacationDays;

    /**
     * Maximum number carry over vacation days per year (usually 15 carry over days)
     *
     * @ORM\Column(type="integer", nullable=true)
     */
    private $maxCarryOverVacationDays;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $noteForVacationDays;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $noteForCarryOverDays;





    public function __construct() {
    }



    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getAcademicYearStart()
    {
        return $this->academicYearStart;
    }

    /**
     * @param mixed $academicYearStart
     */
    public function setAcademicYearStart($academicYearStart)
    {
        $this->academicYearStart = $academicYearStart;
    }

    /**
     * @return mixed
     */
    public function getAcademicYearEnd()
    {
        return $this->academicYearEnd;
    }

    /**
     * @param mixed $academicYearEnd
     */
    public function setAcademicYearEnd($academicYearEnd)
    {
        $this->academicYearEnd = $academicYearEnd;
    }

    /**
     * @return mixed
     */
    public function getHolidaysUrl()
    {
        return $this->holidaysUrl;
    }

    /**
     * @param mixed $holidaysUrl
     */
    public function setHolidaysUrl($holidaysUrl)
    {
        $this->holidaysUrl = $holidaysUrl;
    }

    /**
     * @return mixed
     */
    public function getVacationAccruedDaysPerMonth()
    {
        return $this->vacationAccruedDaysPerMonth;
    }

    /**
     * @param mixed $vacationAccruedDaysPerMonth
     */
    public function setVacationAccruedDaysPerMonth($vacationAccruedDaysPerMonth)
    {
        $this->vacationAccruedDaysPerMonth = $vacationAccruedDaysPerMonth;
    }

    /**
     * @return mixed
     */
    public function getMaxCarryOverVacationDays()
    {
        return $this->maxCarryOverVacationDays;
    }

    /**
     * @param mixed $maxCarryOverVacationDays
     */
    public function setMaxCarryOverVacationDays($maxCarryOverVacationDays)
    {
        $this->maxCarryOverVacationDays = $maxCarryOverVacationDays;
    }

    /**
     * @return mixed
     */
    public function getNoteForVacationDays()
    {
        return $this->noteForVacationDays;
    }

    /**
     * @param mixed $noteForVacationDays
     */
    public function setNoteForVacationDays($noteForVacationDays)
    {
        $this->noteForVacationDays = $noteForVacationDays;
    }

    /**
     * @return mixed
     */
    public function getNoteForCarryOverDays()
    {
        return $this->noteForCarryOverDays;
    }

    /**
     * @param mixed $noteForCarryOverDays
     */
    public function setNoteForCarryOverDays($noteForCarryOverDays)
    {
        $this->noteForCarryOverDays = $noteForCarryOverDays;
    }

    /**
     * @return mixed
     */
    public function getMaxVacationDays()
    {
        return $this->maxVacationDays;
    }

    /**
     * @param mixed $maxVacationDays
     */
    public function setMaxVacationDays($maxVacationDays)
    {
        $this->maxVacationDays = $maxVacationDays;
    }


    

}