<?php

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


}