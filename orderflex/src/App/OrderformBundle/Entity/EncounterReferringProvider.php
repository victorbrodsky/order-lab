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
 * @ORM\Table(name="scan_encounterReferringProvider")
 */
class EncounterReferringProvider extends EncounterArrayFieldAbstract
{

    /**
     * @ORM\ManyToOne(targetEntity="Encounter", inversedBy="referringProviders", cascade={"persist"})
     * @ORM\JoinColumn(name="encounter_id", referencedColumnName="id", onDelete="CASCADE", nullable=true)
     */
    protected $encounter;


    /**
     * @ORM\ManyToOne(targetEntity="App\UserdirectoryBundle\Entity\UserWrapper", cascade={"persist","remove"})
     * @ORM\JoinColumn(name="referringProvider", referencedColumnName="id")
     */
    protected $field;

    /**
     * @ORM\ManyToOne(targetEntity="App\UserdirectoryBundle\Entity\HealthcareProviderSpecialtiesList", cascade={"persist","remove"})
     * @ORM\JoinColumn(name="referringProviderSpecialty", referencedColumnName="id")
     */
    private $referringProviderSpecialty;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $referringProviderPhone;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $referringProviderEmail;

    /**
     * Initial Communication: Inbound, Outbound
     *
     * @ORM\ManyToOne(targetEntity="App\UserdirectoryBundle\Entity\HealthcareProviderCommunicationList", cascade={"persist","remove"})
     * @ORM\JoinColumn(name="referringProviderCommunication", referencedColumnName="id")
     */
    private $referringProviderCommunication;


    /**
     * @return mixed
     */
    public function getReferringProviderSpecialty()
    {
        return $this->referringProviderSpecialty;
    }

    /**
     * @param mixed $referringProviderSpecialty
     */
    public function setReferringProviderSpecialty($referringProviderSpecialty)
    {
        $this->referringProviderSpecialty = $referringProviderSpecialty;
    }

    /**
     * @return mixed
     */
    public function getReferringProviderCommunication()
    {
        return $this->referringProviderCommunication;
    }

    /**
     * @param mixed $referringProviderCommunication
     */
    public function setReferringProviderCommunication($referringProviderCommunication)
    {
        $this->referringProviderCommunication = $referringProviderCommunication;
    }

    /**
     * @return mixed
     */
    public function getReferringProviderPhone()
    {
        return $this->referringProviderPhone;
    }

    /**
     * @param mixed $referringProviderPhone
     */
    public function setReferringProviderPhone($referringProviderPhone)
    {
        $this->referringProviderPhone = $referringProviderPhone;
    }

    /**
     * @return mixed
     */
    public function getReferringProviderEmail()
    {
        return $this->referringProviderEmail;
    }

    /**
     * @param mixed $referringProviderEmail
     */
    public function setReferringProviderEmail($referringProviderEmail)
    {
        $this->referringProviderEmail = $referringProviderEmail;
    }

    public function obtainLinkedUser() {
        $userWrapper = $this->getField();
        if( $userWrapper && $userWrapper->getUser() ) {
            return $userWrapper->getUser();
        }
        return null;
    }


}