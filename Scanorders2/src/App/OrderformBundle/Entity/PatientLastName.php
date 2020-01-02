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

use Oleg\OrderformBundle\Entity\PatientArrayFieldAbstract;


/**
 * @ORM\Entity
 * @ORM\Table(name="scan_patientlastname",
 *  indexes={
 *      @ORM\Index( name="patientlastname_field_idx", columns={"field"} )
 *  }
 * )
 */
class PatientLastName extends PatientArrayFieldAbstract
{

    /**
     * @ORM\ManyToOne(targetEntity="Patient", inversedBy="lastname")
     * @ORM\JoinColumn(name="patient_id", referencedColumnName="id", onDelete="CASCADE", nullable=true)
     */
    protected $patient;

    /**
     * Last Name
     * @ORM\Column(type="string", nullable=true)
     */
    protected $field;

    /**
     * Metaphone key string
     * @ORM\Column(type="string", nullable=true)
     */
    protected $fieldMetaphone;


    /**
     * convert the string to "Sentence Case"
     * @return mixed
     */
    public function getField()
    {
        return $this->capitalizeIfNotAllCapital($this->field);
    }

    //TODO: on setField() update $fieldMetaphone using getMetaphoneKey method
    //http://stackoverflow.com/questions/10330704/symfony-2-0-getting-service-inside-entity
    //http://stackoverflow.com/questions/25515452/sf2-using-a-service-inside-an-entity
    //Or use DoctrineListener with postPersist

    /**
     * @return mixed
     */
    public function getFieldMetaphone()
    {
        return $this->fieldMetaphone;
    }

    /**
     * @param mixed $fieldMetaphone
     */
    public function setFieldMetaphone($fieldMetaphone)
    {
        $this->fieldMetaphone = $fieldMetaphone;
    }


}