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
use Symfony\Component\Form\Extension\Core\DataTransformer\DateTimeToStringTransformer;


/**
 * @ORM\Entity
 * @ORM\Table(name="scan_patientdob")
 */
class PatientDob extends PatientArrayFieldAbstract
{

    /**
     * @ORM\ManyToOne(targetEntity="Patient", inversedBy="dob")
     * @ORM\JoinColumn(name="patient_id", referencedColumnName="id", onDelete="CASCADE", nullable=true)
     */
    protected $patient;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    protected $field;



    public function setField($field=null)
    {
        //echo "<br>old data=".$this->formatDataToString($this->field)."<br>";
        //echo $this->getId().": data=".$this->formatDataToString($this->field)." => ".$this->formatDataToString($field)."<br>";
        //echo "parent=".$this->getPatient()."<br>";
        $this->setFieldChangeArray("field",$this->formatDataToString($this->field),$this->formatDataToString($field));
        $this->field = $field;
    }


    public function __toString() {
        return $this->formatDataToString($this->field);
    }

//    public function formatDataToString($data) {
//        if( $data ) {
//            $transformer = new DateTimeToStringTransformer(null, null, 'Y-m-d');
//            $dateStr = $transformer->transform($data);
//            return $dateStr;
//        } else {
//            return $data;
//        }
//    }

}