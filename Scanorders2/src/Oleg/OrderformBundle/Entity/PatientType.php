<?php

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
     * @ORM\ManyToOne(targetEntity="Patient", inversedBy="patientType")
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