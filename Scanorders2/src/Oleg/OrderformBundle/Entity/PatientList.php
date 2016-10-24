<?php

namespace Oleg\OrderformBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

use Oleg\UserdirectoryBundle\Entity\ListAbstract;

/**
 * @ORM\Entity
 * @ORM\Table(name="scan_patientList")
 */
class PatientList extends ListAbstract
{

    /**
     * @ORM\OneToMany(targetEntity="PatientList", mappedBy="original")
     **/
    protected $synonyms;

    /**
     * @ORM\ManyToOne(targetEntity="PatientList", inversedBy="synonyms")
     * @ORM\JoinColumn(name="original_id", referencedColumnName="id")
     **/
    protected $original;



    /**
     * @ORM\ManyToMany(targetEntity="PatientListHierarchy", inversedBy="patientLists")
     **/
    private $patientListHierarchy;

    /**
     * @ORM\ManyToOne(targetEntity="Patient")
     */
    private $patients;



    public function __construct( $creator = null ) {
        parent::__construct($creator);

        $this->patients = new ArrayCollection();
    }


    public function getPatient()
    {
        return $this->patients;
    }
    public function addPatient($item)
    {
        if( $item && !$this->patients->contains($item) ) {
            $this->patients->add($item);
        }
        return $this;
    }
    public function removePatient($item)
    {
        $this->patients->removeElement($item);
    }



    /**
     * @return mixed
     */
    public function getPatientListHierarchy()
    {
        return $this->patientListHierarchy;
    }

    /**
     * @param mixed $patientListHierarchy
     */
    public function setPatientListHierarchy($patientListHierarchy)
    {
        $this->patientListHierarchy = $patientListHierarchy;
    }


}