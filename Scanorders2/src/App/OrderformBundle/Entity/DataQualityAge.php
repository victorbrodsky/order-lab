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

namespace App\OrderformBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="scan_dataquality_age")
 */
class DataQualityAge extends DataQuality
{

    /**
     * @ORM\ManyToOne(targetEntity="Message")
     * @ORM\JoinColumn(name="message_id", referencedColumnName="id", onDelete="CASCADE", nullable=true)
     */
    protected $message;

    /**
     * @ORM\ManyToOne(targetEntity="Encounter")
     * @ORM\JoinColumn(name="encounter_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $encounter;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $encounterage;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $encounterdate;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $patientdob;




    /**
     * @param mixed $message
     */
    public function setMessage($message)
    {
        $this->message = $message;
    }

    /**
     * @return mixed
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @param mixed $encounter
     */
    public function setEncounter($encounter)
    {
        $this->encounter = $encounter;
    }

    /**
     * @return mixed
     */
    public function getEncounter()
    {
        return $this->encounter;
    }

    /**
     * @param mixed $encounterage
     */
    public function setEncounterage($encounterage)
    {
        $this->encounterage = $encounterage;
    }

    /**
     * @return mixed
     */
    public function getEncounterage()
    {
        return $this->encounterage;
    }

    /**
     * @param mixed $encounterdate
     */
    public function setEncounterdate($encounterdate)
    {
        $this->encounterdate = $encounterdate;
    }

    /**
     * @return mixed
     */
    public function getEncounterdate()
    {
        return $this->encounterdate;
    }

    /**
     * @param mixed $patientdob
     */
    public function setPatientdob($patientdob)
    {
        $this->patientdob = $patientdob;
    }

    /**
     * @return mixed
     */
    public function getPatientdob()
    {
        return $this->patientdob;
    }


}