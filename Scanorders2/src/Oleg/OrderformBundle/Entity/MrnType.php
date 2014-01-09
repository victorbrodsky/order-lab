<?php

namespace Oleg\OrderformBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="mrntype")
 */
class MrnType extends ListAbstract
{

    /**
     * @ORM\OneToMany(targetEntity="PatientMrn", mappedBy="mrntype")
     */
    protected $patientmrn;


    public function __construct() {
        $this->patientmrn = new ArrayCollection();
    }

    public function addPatientmrn(\Oleg\OrderformBundle\Entity\PatientMrn $patientmrn)
    {
        if( !$this->diseaseType->contains($patientmrn) ) {
            $this->patientmrn->add($patientmrn);
        }
        return $this;
    }

    public function removePatientmrn(\Oleg\OrderformBundle\Entity\PatientMrn $patientmrn)
    {
        $this->patientmrn->removeElement($patientmrn);
    }

    public function getPatientmrn()
    {
        return $this->patientmrn;
    }

}