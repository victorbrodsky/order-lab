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

namespace App\VacReqBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use App\UserdirectoryBundle\Entity\ListAbstract;
use Symfony\Component\Validator\Constraints as Assert;


//This list store -/+ 20 years holidays

/**
 * @ORM\Entity
 * @ORM\Table(name="vacreq_holidayList")
 */
class VacReqHolidayList extends ListAbstract {

    /**
     * @ORM\OneToMany(targetEntity="VacReqHolidayList", mappedBy="original", cascade={"persist"})
     **/
    protected $synonyms;

    /**
     * @ORM\ManyToOne(targetEntity="VacReqHolidayList", inversedBy="synonyms", cascade={"persist"})
     * @ORM\JoinColumn(name="original_id", referencedColumnName="id", nullable=true)
     **/
    protected $original;

    /**
     * Holiday Name
     * @ORM\Column(type="string", nullable=true)
     */
    private $holidayName;

    /**
     * Holiday Date (2022-05-25)
     * @ORM\Column(type="date", nullable=true)
     */
    private $holidayDate;

    //“Country” attribute (set to [US] by default)
    /**
     * @ORM\ManyToOne(targetEntity="App\UserdirectoryBundle\Entity\Countries")
     * @ORM\JoinColumn(referencedColumnName="id", nullable=true)
     */
    private $country;

    //"Observed By" - showing all organizational groups in a Select2 drop down menu
    /**
     * @ORM\ManyToMany(targetEntity="App\UserdirectoryBundle\Entity\Institution", cascade={"persist"})
     * @ORM\JoinTable(name="vacreq_holiday_institution",
     *      joinColumns={@ORM\JoinColumn(name="holiday_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="institution_id", referencedColumnName="id")}
     *      )
     **/
    private $institutions;


    /**
     * Observed, used, active holiday
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $observed;



    function __construct($author=null) {
        parent::__construct($author);
        $this->institutions = new ArrayCollection();
    }


    
    /**
     * @return mixed
     */
    public function getHolidayDate()
    {
        return $this->holidayDate;
    }

    /**
     * @param mixed $holidayDate
     */
    public function setHolidayDate($holidayDate)
    {
        $this->holidayDate = $holidayDate;
    }

    /**
     * @return mixed
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * @param mixed $country
     */
    public function setCountry($country)
    {
        $this->country = $country;
    }

    /**
     * @return mixed
     */
    public function getInstitutions()
    {
        return $this->institutions;
    }

    /**
     * @param mixed $institution
     */
    public function addInstitution($institution)
    {
        if( !$this->institutions->contains($institution) ) {
            $this->institutions->add($institution);
        }
    }

    public function removeInstitution($institution)
    {
        $this->institutions->removeElement($institution);
    }

    public function setInstitutions($institutions)
    {
        if( $institutions && is_array($institutions) ) {
            foreach ($institutions as $institution) {
                $this->addInstitution($institution);
            }
        }
    }

    public function getInstitutionsStr()
    {
        //return $this->getInstitutions().toString();
        $res = "";
        $numItems = count($this->getInstitutions());
        $i = 0;
        foreach($this->getInstitutions() as $inst) {
            $res = $res . $inst->getName();
            if(++$i !== $numItems) {
                $res = $res . ", ";
            }
        }
        return $res;
    }

    public function clearInstitutions() {
        $this->institutions->clear();
    }

    /**
     * @return mixed
     */
    public function getHolidayName()
    {
        return $this->holidayName;
    }

    /**
     * @param mixed $holidayName
     */
    public function setHolidayName($holidayName)
    {
        $this->holidayName = $holidayName;
    }

    /**
     * @return mixed
     */
    public function getObserved()
    {
        return $this->observed;
    }

    /**
     * @param mixed $observed
     */
    public function setObserved($observed)
    {
        $this->observed = $observed;
    }

    
    public function getString() {
        $dateStr = "N/A";
        if( $this->getHolidayDate() ) {
            $dateStr = $this->getHolidayDate()->format('D, M d Y'); //format('d-m-Y');
        }

        $observedStr = "N/A";
        if( $this->getObserved() === true ) {
            $observedStr = "Yes";
        }
        if( $this->getObserved() === false ) {
            $observedStr = "No";
        }

        return "ID#".$this->getId() . " (" . $this->getName() . ")" .
        ": Date=" . $dateStr .
        "; Name=" . $this->getHolidayName() .
        "; Institution(s)=" . $this->getInstitutionsStr().
        "; Active=" . $observedStr;
    }

    public function getNameOrShortName() {
        if( $this->getShortname() ) {
            return $this->getShortname();
        }
        return $this->getName();
    }

    public function getHolidayNameOrShortName() {
        if( $this->getShortname() ) {
            return $this->getShortname();
        }
        return $this->getHolidayName();
    }

    public function getEntityHash() {
        $hash = hash("sha1",$this->getString());
        return $hash;
    }

}