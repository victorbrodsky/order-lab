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
use Doctrine\Common\Collections\ArrayCollection;

#[ORM\Table(name: 'vacreq_siteparameter')]
#[ORM\Entity]
class VacReqSiteParameter
{

    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    private $id;

    #[ORM\Column(type: 'date', nullable: true)]
    private $academicYearStart;

    #[ORM\Column(type: 'date', nullable: true)]
    private $academicYearEnd;

    #[ORM\Column(type: 'text', nullable: true)]
    private $holidaysUrl;

    ////////// TODO: Moved to the VacReqApprovalTypeList (can be deleted) //////////////
    /**
     * Moved to the VacReqApprovalTypeList - Done
     */
    #[ORM\Column(type: 'integer', nullable: true)]
    private $vacationAccruedDaysPerMonth;

    /**
     * Moved to the VacReqApprovalTypeList - Done
     *
     * Maximum number vacation days per year (usually 12*2=24).
     * This should not be used for now, because we rely on the vacationAccruedDaysPerMonth.
     */
    #[ORM\Column(type: 'integer', nullable: true)]
    private $maxVacationDays;

    /**
     * Moved to the VacReqApprovalTypeList - Done
     *
     * Maximum number carry over vacation days per year (usually 15 carry over days)
     */
    #[ORM\Column(type: 'integer', nullable: true)]
    private $maxCarryOverVacationDays;

    /**
     * Moved to the VacReqApprovalTypeList - Done ?
     */
    #[ORM\Column(type: 'text', nullable: true)]
    private $noteForVacationDays;

    /**
     * Moved to the VacReqApprovalTypeList - Done ?
     */
    #[ORM\Column(type: 'text', nullable: true)]
    private $noteForCarryOverDays;
    ////////// EOF Moved to the VacReqApprovalTypeList //////////////
    /**
     * field titled “Floating Day Link Name” with a default value of “Floating Day”
     */
    #[ORM\Column(type: 'text', nullable: true)]
    private $floatingDayName;

    //text field titled “Floating Day Note” with a default value of
    // “The Juneteenth Holiday may be used as a floating holiday only
    // if you have an NYPH appointment. You can request a floating holiday however,
    // it must be used in the same fiscal year ending June 30, 2022.
    // It cannot be carried over.”
    /**
     * field titled “Floating Day Note” with a default value of "The Juneteenth Holiday may be used as a floating holiday only..."
     */
    #[ORM\Column(type: 'text', nullable: true)]
    private $floatingDayNote;

    /**
     * checkbox field titled “Restrict Floating Date Range” and set the value to “checked” by default
     */
    #[ORM\Column(type: 'boolean', nullable: true)]
    private $floatingRestrictDateRange;

    /**
     * Enable Floating Day Requests: [Yes/No]
     */
    #[ORM\Column(type: 'boolean', nullable: true)]
    private $enableFloatingDay;

    //URL for US Holiday dates in iCal format:
    /**
     * URL for US Holiday dates in iCal format
     */
    #[ORM\Column(type: 'text', nullable: true)]
    private $holidayDatesUrl;

    //new field titled “Instance maintained for the following institution”:
    // [Select2 with organizational groups pulled from the Platform List Manager List]”.
    // Set this value to 'Weill Cornell Medicine', 'Brooklyn Methodist', 'NYP Lower Manhattan Hospital Laboratory'
    #[ORM\JoinTable(name: 'vacreq_siteparameter_institution')]
    #[ORM\JoinColumn(name: 'siteparameter_id', referencedColumnName: 'id')]
    #[ORM\InverseJoinColumn(name: 'institution_id', referencedColumnName: 'id')]
    #[ORM\ManyToMany(targetEntity: 'App\UserdirectoryBundle\Entity\Institution', cascade: ['persist'])]
    private $institutions;

    //International Travel Registry
    #[ORM\Column(type: 'text', nullable: true)]
    private $intTravelNote;

    /**
     * Show "Travel intake form for Spend Control Committee approval" section
     */
    #[ORM\Column(type: 'boolean', nullable: true)]
    private $enableTravelIntakeForm;

    //Title of travel intake form accordion: [Travel intake form for Spend Control Committee approval]
    //Complete the Travel Intake form for Spend Control Committee Approval
    #[ORM\Column(type: 'text', nullable: true)]
    private $titleTravelIntakeForm;

    //Travel intake form PDF: [DropZone field where the new empty form can be uploaded so that its URL can be shown in the href field name link  above]
    #[ORM\JoinTable(name: 'vacreq_siteparameter_intakeform')]
    #[ORM\JoinColumn(name: 'siteparameter_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    #[ORM\InverseJoinColumn(name: 'document_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    #[ORM\ManyToMany(targetEntity: 'App\UserdirectoryBundle\Entity\Document', cascade: ['persist', 'remove'])]
    #[ORM\OrderBy(['createdate' => 'ASC'])]
    private $travelIntakeForms;



    public function __construct() {
        $this->institutions = new ArrayCollection();
        $this->travelIntakeForms = new ArrayCollection();
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

    /**
     * @return mixed
     */
    public function getFloatingDayName()
    {
        return $this->floatingDayName;
    }

    /**
     * @param mixed $floatingDayName
     */
    public function setFloatingDayName($floatingDayName)
    {
        $this->floatingDayName = $floatingDayName;
    }

    /**
     * @return mixed
     */
    public function getFloatingDayNote()
    {
        return $this->floatingDayNote;
    }

    /**
     * @param mixed $floatingDayNote
     */
    public function setFloatingDayNote($floatingDayNote)
    {
        $this->floatingDayNote = $floatingDayNote;
    }

    /**
     * @return mixed
     */
    public function getFloatingRestrictDateRange()
    {
        return $this->floatingRestrictDateRange;
    }

    /**
     * @param mixed $floatingRestrictDateRange
     */
    public function setFloatingRestrictDateRange($floatingRestrictDateRange)
    {
        $this->floatingRestrictDateRange = $floatingRestrictDateRange;
    }

    /**
     * @return mixed
     */
    public function getEnableFloatingDay()
    {
        return $this->enableFloatingDay;
    }

    /**
     * @param mixed $enableFloatingDay
     */
    public function setEnableFloatingDay($enableFloatingDay)
    {
        $this->enableFloatingDay = $enableFloatingDay;
    }
    

    /**
     * @return mixed
     */
    public function getHolidayDatesUrl()
    {
        return $this->holidayDatesUrl;
    }

    /**
     * @param mixed $holidayDatesUrl
     */
    public function setHolidayDatesUrl($holidayDatesUrl)
    {
        $this->holidayDatesUrl = $holidayDatesUrl;
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

    /**
     * @return mixed
     */
    public function getIntTravelNote()
    {
        return $this->intTravelNote;
    }

    /**
     * @param mixed $intTravelNote
     */
    public function setIntTravelNote($intTravelNote)
    {
        $this->intTravelNote = $intTravelNote;
    }

    /**
     * @return mixed
     */
    public function getEnableTravelIntakeForm()
    {
        return $this->enableTravelIntakeForm;
    }

    /**
     * @param mixed $enableTravelIntakeForm
     */
    public function setEnableTravelIntakeForm($enableTravelIntakeForm)
    {
        $this->enableTravelIntakeForm = $enableTravelIntakeForm;
    }

    /**
     * @return mixed
     */
    public function getTitleTravelIntakeForm()
    {
        return $this->titleTravelIntakeForm;
    }

    /**
     * @param mixed $titleTravelIntakeForm
     */
    public function setTitleTravelIntakeForm($titleTravelIntakeForm)
    {
        $this->titleTravelIntakeForm = $titleTravelIntakeForm;
    }

    public function addTravelIntakeForm($item)
    {
        if( $item && !$this->travelIntakeForms->contains($item) ) {
            $this->travelIntakeForms->add($item);
            $item->createUseObject($this);
        }
        return $this;
    }
    public function removeTravelIntakeForm($item)
    {
        $this->travelIntakeForms->removeElement($item);
        $item->clearUseObject();
    }
    public function getTravelIntakeForms()
    {
        return $this->travelIntakeForms;
    }
    public function getSingleTravelIntakeForm()
    {
        $docs = $this->getTravelIntakeForms();
        if( count($docs) > 0 ) {
            return $docs->last(); //ASC: the oldest ones come first and the most recent ones last
        }
        return null;
    }
    

}