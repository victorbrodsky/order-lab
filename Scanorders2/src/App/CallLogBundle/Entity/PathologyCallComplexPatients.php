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

namespace Oleg\CallLogBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

use Oleg\UserdirectoryBundle\Entity\ListAbstract;

/**
 * @ORM\Entity
 * @ORM\Table(name="calllog_pathologyCallComplexPatients")
 */
class PathologyCallComplexPatients extends ListAbstract
{

    /**
     * @ORM\OneToMany(targetEntity="PathologyCallComplexPatients", mappedBy="original")
     **/
    protected $synonyms;

    /**
     * @ORM\ManyToOne(targetEntity="PathologyCallComplexPatients", inversedBy="synonyms")
     * @ORM\JoinColumn(name="original_id", referencedColumnName="id")
     **/
    protected $original;


    /**
     * @ORM\ManyToOne(targetEntity="Oleg\OrderformBundle\Entity\Patient", cascade={"persist"})
     */
    private $patient;






    /**
     * @return mixed
     */
    public function getPatient()
    {
        return $this->patient;
    }

    /**
     * @param mixed $patient
     */
    public function setPatient($patient)
    {
        $this->patient = $patient;
    }


//    /**
//     * @ORM\ManyToOne(targetEntity="PatientListHierarchy", inversedBy="patientLists")
//     **/
//    private $patientListHierarchy;

//    /**
//     *
//     * @ORM\ManyToMany(targetEntity="Patient")
//     * @ORM\JoinTable(name="scan_patientList_patient",
//     *      joinColumns={@ORM\JoinColumn(name="patientList_id", referencedColumnName="id")},
//     *      inverseJoinColumns={@ORM\JoinColumn(name="patient_id", referencedColumnName="id")}
//     *      )
//     */
//    private $patients;



//    public function __construct( $creator = null ) {
//        parent::__construct($creator);
//
//        //$this->patients = new ArrayCollection();
//    }


//    public function getPatients()
//    {
//        return $this->patients;
//    }
//    public function addPatient($item)
//    {
//        if( $item && !$this->patients->contains($item) ) {
//            $this->patients->add($item);
//        }
//        return $this;
//    }
//    public function removePatient($item)
//    {
//        $this->patients->removeElement($item);
//    }



//    /**
//     * @return mixed
//     */
//    public function getPatientListHierarchy()
//    {
//        return $this->patientListHierarchy;
//    }
//
//    /**
//     * @param mixed $patientListHierarchy
//     */
//    public function setPatientListHierarchy($patientListHierarchy)
//    {
//        $this->patientListHierarchy = $patientListHierarchy;
//    }




}