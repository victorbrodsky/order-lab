<?php

namespace Oleg\OrderformBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
//use Doctrine\Common\Collections\ArrayCollection;

use Oleg\OrderformBundle\Entity\ArrayFieldAbstract;


/**
 * @ORM\Entity(repositoryClass="Oleg\OrderformBundle\Repository\ClinicalHistoryRepository")
 * @ORM\Table(name="clinicalHistory")
 */
class ClinicalHistory extends ArrayFieldAbstract
{
    
//    /**
//     * @ORM\Id
//     * @ORM\Column(type="integer")
//     * @ORM\GeneratedValue(strategy="AUTO")
//     */
//    protected $id;

    /**
     * @ORM\Column(type="text", length=10000)
     */
    protected $clinicalHistory;

    /**
     * @ORM\ManyToOne(targetEntity="Patient", inversedBy="clinicalHistory")
     * @ORM\JoinColumn(name="patient_id", referencedColumnName="id", nullable=true)
     */
    protected $patient;

//    /**
//     * validity - valid or not valid
//     * @ORM\Column(type="string", nullable=true, length=100)
//     */
//    protected $validity;
//
//    /**
//     * @var \DateTime
//     * @ORM\Column(type="datetime", nullable=true)
//     *
//     */
//    protected $creationdate;

//    /**
//     * @ORM\ManyToOne(targetEntity="User", cascade={"persist"})
//     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
//     */
//    protected $creator;
//    /**
//     * @ORM\ManyToMany(targetEntity="User", cascade={"persist"})
//     * @ORM\JoinTable(name="provider_clinicalhist",
//     *      joinColumns={@ORM\JoinColumn(name="clinicalhist_id", referencedColumnName="id")},
//     *      inverseJoinColumns={@ORM\JoinColumn(name="provider_id", referencedColumnName="id")}
//     * )
//     */
//    private $provider;

//    public function __construct()
//    {
//        $this->provider = new ArrayCollection();
//    }

//    public function setId($id)
//    {
//        //echo "setId=".$id."<br>";
//        $this->id = $id;
//        return $this;
//    }
//
//    public function getId() {
//        return $this->id;
//    }

    /**
     * Set clinicalHistory
     *
     * @param string $clinicalHistory
     * @return ClinicalHistory
     */
    public function setClinicalHistory($clinicalHistory)
    {
        $this->clinicalHistory = $clinicalHistory;
    
        return $this;
    }

    /**
     * Get clinicalHistory
     *
     * @return string 
     */
    public function getClinicalHistory()
    {
        return $this->clinicalHistory;
    }

//    /**
//     * Set validity
//     *
//     * @param string $validity
//     * @return ClinicalHistory
//     */
//    public function setValidity($validity)
//    {
//        $this->validity = $validity;
//
//        return $this;
//    }
//
//    /**
//     * Get validity
//     *
//     * @return string
//     */
//    public function getValidity()
//    {
//        return $this->validity;
//    }

    /**
     * Set patient
     *
     * @param \Oleg\OrderformBundle\Entity\Patient $patient
     * @return ClinicalHistory
     */
    public function setPatient(\Oleg\OrderformBundle\Entity\Patient $patient = null)
    {
        $this->patient = $patient;
    
        return $this;
    }

    /**
     * Get patient
     *
     * @return \Oleg\OrderformBundle\Entity\Patient 
     */
    public function getPatient()
    {
        return $this->patient;
    }

//    /**
//     * @ORM\PrePersist
//     */
//    public function setCreationdate()
//    {
//        $this->creationdate = new \DateTime();
//    }
//
//    /**
//     * @return \DateTime
//     */
//    public function getCreationdate()
//    {
//        return $this->creationdate;
//    }


//    public function setProvider($provider)
//    {
//        if ( is_array($provider) ) {
//            $this->provider = $provider;
//        } else {
//            $this->provider->clear();
//            $this->provider->add($provider);
//        }
//        return $this;
//    }
//
//    public function getProvider()
//    {
//        return $this->provider;
//    }
//
//    public function addProvider(\Oleg\OrderformBundle\Entity\User $provider)
//    {
//        if( !$this->provider->contains($provider) ) {
//            $this->provider[] = $provider;
//        }
//
//        return $this;
//    }
//
//    public function removeProvider(\Oleg\OrderformBundle\Entity\User $provider)
//    {
//        $this->provider->removeElement($provider);
//    }

}