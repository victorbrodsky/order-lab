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
 * @ORM\Table(name="scan_encounterAttendingPhysician")
 */
class EncounterAttendingPhysician extends EncounterArrayFieldAbstract
{

    /**
     * @ORM\ManyToOne(targetEntity="Encounter", inversedBy="attendingPhysicians", cascade={"persist"})
     * @ORM\JoinColumn(name="encounter_id", referencedColumnName="id", onDelete="CASCADE", nullable=true)
     */
    protected $encounter;


    /**
     * @ORM\ManyToOne(targetEntity="Oleg\UserdirectoryBundle\Entity\UserWrapper", cascade={"persist","remove"})
     * @ORM\JoinColumn(name="attendingPhysician", referencedColumnName="id")
     */
    protected $field;

    /**
     * @ORM\ManyToOne(targetEntity="Oleg\UserdirectoryBundle\Entity\HealthcareProviderSpecialtiesList", cascade={"persist","remove"})
     * @ORM\JoinColumn(name="attendingPhysicianSpecialty", referencedColumnName="id")
     */
    private $attendingPhysicianSpecialty;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $attendingPhysicianPhone;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $attendingPhysicianEmail;







    /**
     * @return mixed
     */
    public function getAttendingPhysicianSpecialty()
    {
        return $this->attendingPhysicianSpecialty;
    }

    /**
     * @param mixed $attendingPhysicianSpecialty
     */
    public function setAttendingPhysicianSpecialty($attendingPhysicianSpecialty)
    {
        $this->attendingPhysicianSpecialty = $attendingPhysicianSpecialty;
    }

    /**
     * @return mixed
     */
    public function getAttendingPhysicianPhone()
    {
        return $this->attendingPhysicianPhone;
    }

    /**
     * @param mixed $attendingPhysicianPhone
     */
    public function setAttendingPhysicianPhone($attendingPhysicianPhone)
    {
        $this->attendingPhysicianPhone = $attendingPhysicianPhone;
    }

    /**
     * @return mixed
     */
    public function getAttendingPhysicianEmail()
    {
        return $this->attendingPhysicianEmail;
    }

    /**
     * @param mixed $attendingPhysicianEmail
     */
    public function setAttendingPhysicianEmail($attendingPhysicianEmail)
    {
        $this->attendingPhysicianEmail = $attendingPhysicianEmail;
    }


    public function getEmail() {
        $email = null;
        $userWrapper = $this->getField();
        if( $userWrapper ) {
            if( $userWrapper->getUser() ) {
                $email = $userWrapper->getUser()->getSingleEmail();
            }

            if( !$email ) {
                $email = $userWrapper->getUserWrapperEmail();
            }
        }

        if( !$email ) {
            $email = $this->getAttendingPhysicianEmail();
        }

        return $email;
    }

}