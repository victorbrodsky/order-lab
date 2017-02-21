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

namespace Oleg\OrderformBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="scan_encounterInpatientinfo")
 */
class EncounterInpatientinfo extends EncounterArrayFieldAbstract
{

    /**
     * @ORM\ManyToOne(targetEntity="Encounter", inversedBy="inpatientinfo")
     * @ORM\JoinColumn(name="encounter_id", referencedColumnName="id", nullable=true)
     */
    protected $encounter;


    //Inpatient Info Source: [Select2, pull from "Source Systems" list]
    //$source

    //Admission Date:
    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $admissiondate;

    //Admission Time:
    /**
     * @ORM\Column(type="time", nullable=true)
     */
    private $admissiontime;

    //Diagnosis on Admission:
    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $admissiondiagnosis;

    //Discharge Date:
    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $dischargedate;

    //Discharge Time:
    /**
     * @ORM\Column(type="time", nullable=true)
     */
    private $dischargetime;

    //Diagnosis on Discharge:
    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $dischargediagnosis;

    /**
     * Dummy field required by abstract
     * @ORM\Column(type="string", nullable=true)
     */
    protected $field;





    /**
     * @param mixed $admissiondate
     */
    public function setAdmissiondate($admissiondate)
    {
        $this->admissiondate = $admissiondate;
    }

    /**
     * @return mixed
     */
    public function getAdmissiondate()
    {
        return $this->admissiondate;
    }

    /**
     * @param mixed $admissiondiagnosis
     */
    public function setAdmissiondiagnosis($admissiondiagnosis)
    {
        $this->admissiondiagnosis = $admissiondiagnosis;
    }

    /**
     * @return mixed
     */
    public function getAdmissiondiagnosis()
    {
        return $this->admissiondiagnosis;
    }

    /**
     * @param mixed $admissiontime
     */
    public function setAdmissiontime($admissiontime)
    {
        $this->admissiontime = $admissiontime;
    }

    /**
     * @return mixed
     */
    public function getAdmissiontime()
    {
        return $this->admissiontime;
    }

    /**
     * @param mixed $dischargedate
     */
    public function setDischargedate($dischargedate)
    {
        $this->dischargedate = $dischargedate;
    }

    /**
     * @return mixed
     */
    public function getDischargedate()
    {
        return $this->dischargedate;
    }

    /**
     * @param mixed $dischargediagnosis
     */
    public function setDischargediagnosis($dischargediagnosis)
    {
        $this->dischargediagnosis = $dischargediagnosis;
    }

    /**
     * @return mixed
     */
    public function getDischargediagnosis()
    {
        return $this->dischargediagnosis;
    }

    /**
     * @param mixed $dischargetime
     */
    public function setDischargetime($dischargetime)
    {
        $this->dischargetime = $dischargetime;
    }

    /**
     * @return mixed
     */
    public function getDischargetime()
    {
        return $this->dischargetime;
    }



    public function __toString() {
        $diagnosisAdmission = $this->getAdmissiondiagnosis();
        $diagnosisDischarge = $this->getDischargediagnosis();
        return "Inaptient Info:".$diagnosisAdmission. " ".$diagnosisDischarge."<br>";
    }

}