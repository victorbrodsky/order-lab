<?php

namespace Oleg\CallLogBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

use Oleg\UserdirectoryBundle\Entity\ListAbstract;

/**
 * @ORM\Entity
 * @ORM\Table(name="calllog_pathologyCallComplexPatients")
 */
class PathologyCallComplexPatients extends ListAbstract
{

    /**
     * @ORM\OneToMany(targetEntity="PathologyCallComplexPatients", mappedBy="original")
     **/
    protected $synonyms;

    /**
     * @ORM\ManyToOne(targetEntity="PathologyCallComplexPatients", inversedBy="synonyms")
     * @ORM\JoinColumn(name="original_id", referencedColumnName="id")
     **/
    protected $original;



//    /**
//     * @ORM\ManyToOne(targetEntity="PatientListHierarchy", inversedBy="patientLists")
//     **/
//    private $patientListHierarchy;

//    /**
//     *
//     * @ORM\ManyToMany(targetEntity="Patient")
//     * @ORM\JoinTable(name="scan_patientList_patient",
//     *      joinColumns={@ORM\JoinColumn(name="patientList_id", referencedColumnName="id")},
//     *      inverseJoinColumns={@ORM\JoinColumn(name="patient_id", referencedColumnName="id")}
//     *      )
//     */
//    private $patients;



//    public function __construct( $creator = null ) {
//        parent::__construct($creator);
//
//        //$this->patients = new ArrayCollection();
//    }


//    public function getPatients()
//    {
//        return $this->patients;
//    }
//    public function addPatient($item)
//    {
//        if( $item && !$this->patients->contains($item) ) {
//            $this->patients->add($item);
//        }
//        return $this;
//    }
//    public function removePatient($item)
//    {
//        $this->patients->removeElement($item);
//    }



//    /**
//     * @return mixed
//     */
//    public function getPatientListHierarchy()
//    {
//        return $this->patientListHierarchy;
//    }
//
//    /**
//     * @param mixed $patientListHierarchy
//     */
//    public function setPatientListHierarchy($patientListHierarchy)
//    {
//        $this->patientListHierarchy = $patientListHierarchy;
//    }


}