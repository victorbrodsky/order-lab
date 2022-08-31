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

use App\OrderformBundle\Entity\PatientArrayFieldAbstract;


/**
 * @ORM\Entity
 * @ORM\Table(name="scan_patientDeceased")
 */
class PatientDeceased extends PatientArrayFieldAbstract
{

    /**
     * @ORM\ManyToOne(targetEntity="Patient", inversedBy="deceased")
     * @ORM\JoinColumn(name="patient_id", referencedColumnName="id", nullable=true)
     */
    protected $patient;


    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $deceased;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $deathdate;

    /**
     * @ORM\Column(type="time", nullable=true)
     */
    private $deathtime;

    /**
     * Dummy field required by abstract
     * @ORM\Column(type="string", nullable=true)
     */
    protected $field;



    /**
     * @param mixed $deathdate
     */
    public function setDeathdate($deathdate)
    {
        $this->deathdate = $deathdate;
    }

    /**
     * @return mixed
     */
    public function getDeathdate()
    {
        return $this->deathdate;
    }

    /**
     * @param mixed $deathtime
     */
    public function setDeathtime($deathtime)
    {
        $this->deathtime = $deathtime;
    }

    /**
     * @return mixed
     */
    public function getDeathtime()
    {
        return $this->deathtime;
    }

    /**
     * @param mixed $deceased
     */
    public function setDeceased($deceased)
    {
        $this->deceased = $deceased;
    }

    /**
     * @return mixed
     */
    public function getDeceased()
    {
        return $this->deceased;
    }

    public function __toString() {
        $deceased = "alive";
        if( $this->getDeceased() ) {
            $deceased = "deceased";
        }
        return $deceased;
        //return $this->field."";
    }

}