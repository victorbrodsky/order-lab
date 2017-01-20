<?php

namespace Oleg\OrderformBundle\Entity;

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
     * @ORM\ManyToOne(targetEntity="Oleg\UserdirectoryBundle\Entity\UserWrapper", cascade={"persist","remove"})
     * @ORM\JoinColumn(name="referringProvider", referencedColumnName="id")
     */
    protected $field;

    /**
     * @ORM\ManyToOne(targetEntity="Oleg\UserdirectoryBundle\Entity\HealthcareProviderSpecialtiesList", cascade={"persist","remove"})
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
        if( $userWrapper->getUser() ) {
            return $userWrapper->getUser();
        }
        return null;
    }


}