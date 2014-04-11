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
        $this->patientmrn = new ArrayCollection();
    }

    public function addPatientmrn(\Oleg\OrderformBundle\Entity\PatientMrn $patientmrn)
    {
        if( !$this->patientmrn->contains($patientmrn) ) {
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

    /**
     * @param mixed $original
     */
    public function setOriginal($original)
    {
        $this->original = $original;
    }

    /**
     * @return mixed
     */
    public function getOriginal()
    {
        return $this->original;
    }

    /**
     * @param mixed $synonyms
     */
    public function setSynonyms($synonyms)
    {
        $this->synonyms = $synonyms;
    }

    /**
     * @return mixed
     */
    public function getSynonyms()
    {
        return $this->synonyms;
    }


}