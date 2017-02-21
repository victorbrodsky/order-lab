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
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;

use Oleg\OrderformBundle\Entity\PatientArrayFieldAbstract;


/**
 * @ORM\Entity
 * @ORM\Table(name="scan_patientType")
 */
class PatientType extends PatientArrayFieldAbstract
{

    /**
     * @ORM\ManyToOne(targetEntity="Patient", inversedBy="type")
     * @ORM\JoinColumn(name="patient_id", referencedColumnName="id", nullable=true)
     */
    protected $patient;


    /**
     * @ORM\ManyToOne(targetEntity="PatientTypeList")
     * @ORM\JoinColumn(name="patientTypeList_id", referencedColumnName="id", nullable=true)
     */
    protected $field;

    //The values of the PatientType hierarchy should be attached to Systems (multiple systems). Many PatientTypes to Many Systems.
    /**
     * @ORM\ManyToMany(targetEntity="Oleg\UserdirectoryBundle\Entity\SourceSystemList")
     * @ORM\JoinTable(name="scan_patientType_system",
     *      joinColumns={@ORM\JoinColumn(name="patientType_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="system_id", referencedColumnName="id")}
     *      )
     **/
    private $sources;



    public function __construct( $status = 'valid', $provider = null, $source = null ) {
        parent::__construct($status,$provider,$source);
        $this->sources = new ArrayCollection();
    }



    public function getSources()
    {
        return $this->sources;
    }
    public function addSource($item)
    {
        if( $item && !$this->sources->contains($item) ) {
            $this->sources->add($item);
        }
        return $this;
    }
    public function removeSource($item)
    {
        $this->sources->removeElement($item);
    }

}