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

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

use App\UserdirectoryBundle\Entity\ListAbstract;

/**
 * Note: this file is used in App\UserdirectoryBundle\Entity\Identifier. Do not change!
 *
 * @ORM\Entity
 * @ORM\Table(name="scan_mrntype")
 */
class MrnType extends ListAbstract
{

    /**
     * @ORM\OneToMany(targetEntity="MrnType", mappedBy="original", cascade={"persist"})
     **/
    protected $synonyms;

    /**
     * @ORM\ManyToOne(targetEntity="MrnType", inversedBy="synonyms", cascade={"persist"})
     * @ORM\JoinColumn(name="original_id", referencedColumnName="id", nullable=true)
     **/
    protected $original;

    /**
     * @ORM\OneToMany(targetEntity="PatientMrn", mappedBy="keytype")
     */
    protected $patientmrn;


    public function __construct() {
		$this->synonyms = new ArrayCollection();
        $this->patientmrn = new ArrayCollection();
    }

    public function addPatientmrn(\App\OrderformBundle\Entity\PatientMrn $patientmrn)
    {
        if( !$this->patientmrn->contains($patientmrn) ) {
            $this->patientmrn->add($patientmrn);
        }
        return $this;
    }

    public function removePatientmrn(\App\OrderformBundle\Entity\PatientMrn $patientmrn)
    {
        $this->patientmrn->removeElement($patientmrn);
    }

    public function getPatientmrn()
    {
        return $this->patientmrn;
    }


    public function __toString()
    {
        $name = $this->name."";

        if( $this->abbreviation && $this->abbreviation != "" ) {
            $name = $this->abbreviation."";
        }

        return $name;
    }

}