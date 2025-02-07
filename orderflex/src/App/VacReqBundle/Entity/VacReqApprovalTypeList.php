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


//"Time Away Approval Group Type" with 2 values: Faculty, Fellows
#[ORM\Table(name: 'vacreq_approvaltypelist')]
#[ORM\Entity]
#[ORM\HasLifecycleCallbacks]
class VacReqApprovalTypeList extends ListAbstract {

    #[ORM\OneToMany(targetEntity: 'VacReqApprovalTypeList', mappedBy: 'original', cascade: ['persist'])]
    protected $synonyms;

    #[ORM\ManyToOne(targetEntity: 'VacReqApprovalTypeList', inversedBy: 'synonyms', cascade: ['persist'])]
    #[ORM\JoinColumn(name: 'original_id', referencedColumnName: 'id', nullable: true)]
    protected $original;

    //Add a reference to the “Time Away Approval Group Type” for each approver group vacation site
    // and display this value in a select2 drop down menu under the approval group.
    //associated with vacation group (insitution): one VacReqApprovalTypeList can have many Institution
    //Institution n-----1 VacReqApprovalTypeList
    //when add/edit group, choose institution and select VacReqApprovalTypeList which will link to this institution
    //1) Institution has ManyToOne to VacReqApprovalTypeList: Institution->getVacReqApprovalTypeList
    // => vac days accrued per month, max vac days, allow carry over
    //Easy, but in this case Institution is UserDirectoryBundle will have a reference to VacReqBundle
    //2) VacReqApprovalTypeList has OneToMany or ManyToMany (unique) to Institution: institution => getApprovalType(institution)
    //    /**
    //     * @ORM\ManyToOne(targetEntity="App\UserdirectoryBundle\Entity\Institution")
    //     * @ORM\JoinColumn(referencedColumnName="id", nullable=true)
    //     */
    //    private $institution;
    //    //Add unique=true if institution allows to have only one approvaltype
    //    /**
    //     * TO REMOVE
    //     *
    //     * @ORM\ManyToMany(targetEntity="App\UserdirectoryBundle\Entity\Institution", cascade={"persist"})
    //     * @ORM\JoinTable(name="vacreq_approvaltypes_institutions",
    //     *      joinColumns={@ORM\JoinColumn(name="approvaltype_id", referencedColumnName="id")},
    //     *      inverseJoinColumns={@ORM\JoinColumn(name="institution_id", referencedColumnName="id")}
    //     *      )
    //     **/
    //    private $institutions;
    /**
     * Instead of creat relation to institution here, create relation to the group settings which has relation to institution
     **/
    #[ORM\ManyToMany(targetEntity: 'VacReqSettings', mappedBy: 'approvalTypes')]
    private $vacreqSettings;


    ////////// Moved from VacReqSiteParameter ///////////
    /**
     * Moved from VacReqSiteParameter
     * Vacation days accrued per month (faculty = 2, fellows = 1.666666667)
     */
    #[ORM\Column(type: 'decimal', precision: 15, scale: 9, nullable: true)]
    private $vacationAccruedDaysPerMonth;

    /**
     * Moved from VacReqSiteParameter
     * Maximum number vacation days per year (usually 12*2=24). (faculty, fellows)
     */
    #[ORM\Column(type: 'integer', nullable: true)]
    private $maxVacationDays;

    /**
     * Moved from VacReqSiteParameter
     * Maximum number of carry over vacation days per year (faculty, fellows)
     */
    #[ORM\Column(type: 'integer', nullable: true)]
    private $maxCarryOverVacationDays;

    /**
     * Moved from VacReqSiteParameter
     * Note for vacation days (faculty, fellows)
     */
    #[ORM\Column(type: 'text', nullable: true)]
    private $noteForVacationDays;

    /**
     * Moved from VacReqSiteParameter
     * Note for carry over vacation days (faculty, fellows)
     */
    #[ORM\Column(type: 'text', nullable: true)]
    private $noteForCarryOverDays;
    ////////// EOF Moved from VacReqSiteParameter ///////////
    /**
     * Allow to request carry over of unused vacation days to the following year (faculty, fellows)
     */
    #[ORM\Column(type: 'boolean', nullable: true)]
    private $allowCarryOver;





    public function __construct() {
        //$this->institutions = new ArrayCollection();
        $this->vacreqSettings = new ArrayCollection();
    }



//    //institutions
//    public function getInstitutions()
//    {
//        return $this->institutions;
//    }
//    public function addInstitution($item)
//    {
//        if( $item && !$this->institutions->contains($item) ) {
//            $this->institutions->add($item);
//        }
//    }
//    public function removeInstitution($item)
//    {
//        $this->institutions->removeElement($item);
//    }
//    public function clearInstitutions() {
//        $this->institutions->clear();
//    }

    //vacreqSettings
    public function getVacreqSettings()
    {
        return $this->vacreqSettings;
    }
    public function addVacreqSetting($item)
    {
        if( $item && !$this->vacreqSettings->contains($item) ) {
            $this->vacreqSettings->add($item);
            $item->addApprovalType($this);
        }
    }
    public function removeVacreqSetting($item)
    {
        $this->vacreqSettings->removeElement($item);
        $item->removeApprovalType($this);
    }
    public function clearVacreqSettings() {
        $this->vacreqSettings->clear();
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

    public function getVacationAccruedDaysPerMonthStr()
    {
        $vacationAccruedDaysPerMonth = $this->vacationAccruedDaysPerMonth;
        if( $vacationAccruedDaysPerMonth ) {
            //$accDays = round($accDays);
            $vacationAccruedDaysPerMonth = number_format((float)$vacationAccruedDaysPerMonth, 2, '.', '');
        }
        return $vacationAccruedDaysPerMonth;
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
    public function getAllowCarryOver()
    {
        return $this->allowCarryOver;
    }

    /**
     * @param mixed $allowCarryOver
     */
    public function setAllowCarryOver($allowCarryOver)
    {
        $this->allowCarryOver = $allowCarryOver;
    }


    public function __toString()
    {
        return $this->getName();
    }


}