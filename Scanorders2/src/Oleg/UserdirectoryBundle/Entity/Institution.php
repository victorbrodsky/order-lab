<?php

namespace Oleg\UserdirectoryBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="institution")
 */
class Institution extends ListAbstract
{
    /**
     * @ORM\OneToMany(targetEntity="Institution", mappedBy="original")
     **/
    protected $synonyms;

    /**
     * @ORM\ManyToOne(targetEntity="Institution", inversedBy="synonyms")
     * @ORM\JoinColumn(name="original_id", referencedColumnName="id")
     **/
    protected $original;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $abbreviation;

    /**
     * @ORM\OneToMany(targetEntity="Department", mappedBy="institution", cascade={"persist"})
     */
    protected $departments;

//    /**
//     * @ORM\OneToMany(targetEntity="OrderInfo", mappedBy="institution")
//     */
//    protected $orderinfos;

//    /**
//     * @ORM\OneToMany(targetEntity="Patient", mappedBy="institution")
//     */
//    protected $patients;

//    /**
//     * @ORM\OneToMany(targetEntity="Procedure", mappedBy="institution")
//     */
//    protected $procedures;
//
//    /**
//     * @ORM\OneToMany(targetEntity="Accession", mappedBy="institution")
//     */
//    protected $accessions;
//
//    /**
//     * @ORM\OneToMany(targetEntity="Part", mappedBy="institution")
//     */
//    protected $parts;
//
//    /**
//     * @ORM\OneToMany(targetEntity="Block", mappedBy="institution")
//     */
//    protected $blocks;
//
//    /**
//     * @ORM\OneToMany(targetEntity="Slide", mappedBy="institution")
//     */
//    protected $slides;

    /**
     * @ORM\ManyToMany(targetEntity="User", mappedBy="institution")
     **/
    protected $users;


    public function __construct() {
        $this->synonyms = new ArrayCollection();
        $this->departments = new ArrayCollection();
        $this->users = new ArrayCollection();

//        $this->orderinfos = new ArrayCollection();
//        $this->patients = new ArrayCollection();
//        $this->procedures = new ArrayCollection();
//        $this->accessions = new ArrayCollection();
//        $this->parts = new ArrayCollection();
//        $this->blocks = new ArrayCollection();
//        $this->slides = new ArrayCollection();

    }

    /**
     * Add synonyms
     *
     * @param \Oleg\UserdirectoryBundle\Entity\Institution $synonyms
     * @return Institution
     */
    public function addSynonym(\Oleg\UserdirectoryBundle\Entity\Institution $synonyms)
    {
        $this->synonyms[] = $synonyms;
    
        return $this;
    }

    /**
     * Remove synonyms
     *
     * @param \Oleg\UserdirectoryBundle\Entity\Institution $synonyms
     */
    public function removeSynonym(\Oleg\UserdirectoryBundle\Entity\Institution $synonyms)
    {
        $this->synonyms->removeElement($synonyms);
    }

    /**
     * Get synonyms
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getSynonyms()
    {
        return $this->synonyms;
    }

    /**
     * Set original
     *
     * @param \Oleg\UserdirectoryBundle\Entity\Institution $original
     * @return Institution
     */
    public function setOriginal(\Oleg\UserdirectoryBundle\Entity\Institution $original = null)
    {
        $this->original = $original;
    
        return $this;
    }

    /**
     * Get original
     *
     * @return \Oleg\UserdirectoryBundle\Entity\Institution
     */
    public function getOriginal()
    {
        return $this->original;
    }

//    /**
//     * Add orderinfos
//     *
//     * @param \Oleg\UserdirectoryBundle\Entity\OrderInfo $orderinfo
//     * @return Institution
//     */
//    public function addOrderInfo(\Oleg\UserdirectoryBundle\Entity\OrderInfo $orderinfo)
//    {
//        //echo "Institution addOrderinfo=".$orderinfo."<br>";
//        if( !$this->orderinfos->contains($orderinfo) ) {
//            $orderinfo->setInstitution($this);
//            $this->orderinfos->add($orderinfo);
//        }
//    }

//    /**
//     * Remove orderinfos
//     *
//     * @param \Oleg\UserdirectoryBundle\Entity\OrderInfo $orderinfos
//     */
//    public function removeOrderInfo(\Oleg\UserdirectoryBundle\Entity\OrderInfo $orderinfo)
//    {
//        $this->orderinfos->removeElement($orderinfo);
//    }
//
//    /**
//     * Get orderinfos
//     *
//     * @return \Doctrine\Common\Collections\Collection
//     */
//    public function getOrderInfos()
//    {
//        return $this->orderinfos;
//    }
//
//
//    /**
//     * Add patient
//     *
//     * @param \Oleg\UserdirectoryBundle\Entity\Patient $patient
//     * @return Institution
//     */
//    public function addPatient(\Oleg\UserdirectoryBundle\Entity\Patient $patient)
//    {
//        //echo "Institution addOrderinfo=".$orderinfo."<br>";
//        if( !$this->patients->contains($patient) ) {
//            $patient->setInstitution($this);
//            $this->patients->add($patient);
//        }
//    }
//    /**
//     * Remove order
//     *
//     * @param \Oleg\UserdirectoryBundle\Entity\Patient $order
//     */
//    public function removePatient(\Oleg\UserdirectoryBundle\Entity\Patient $patient)
//    {
//        $this->patients->removeElement($patient);
//    }
//    /**
//     * Get order
//     *
//     * @return \Doctrine\Common\Collections\Collection
//     */
//    public function getPatients()
//    {
//        return $this->patients;
//    }
//
//
//    /**
//     * Add procedure
//     *
//     * @param \Oleg\UserdirectoryBundle\Entity\Procedure $procedure
//     * @return Institution
//     */
//    public function addProcedure(\Oleg\UserdirectoryBundle\Entity\Procedure $procedure)
//    {
//        if( !$this->procedures->contains($procedure) ) {
//            $procedure->setInstitution($this);
//            $this->procedures->add($procedure);
//        }
//    }
//    /**
//     * Remove procedure
//     *
//     * @param \Oleg\UserdirectoryBundle\Entity\Procedure $procedure
//     */
//    public function removeProcedure(\Oleg\UserdirectoryBundle\Entity\Procedure $procedure)
//    {
//        $this->procedures->removeElement($procedure);
//    }
//    /**
//     * Get order
//     *
//     * @return \Doctrine\Common\Collections\Collection
//     */
//    public function getProcedures()
//    {
//        return $this->procedures;
//    }
//
//
//
//    /**
//     * Add accession
//     *
//     * @param \Oleg\UserdirectoryBundle\Entity\Accession $accession
//     * @return Institution
//     */
//    public function addAccession(\Oleg\UserdirectoryBundle\Entity\Accession $accession)
//    {
//        if( !$this->accessions->contains($accession) ) {
//            $accession->setInstitution($this);
//            $this->accessions->add($accession);
//        }
//    }
//    /**
//     * Remove accession
//     *
//     * @param \Oleg\UserdirectoryBundle\Entity\Accession $accession
//     */
//    public function removeAccession(\Oleg\UserdirectoryBundle\Entity\Accession $accession)
//    {
//        $this->accessions->removeElement($accession);
//    }
//    /**
//     * Get order
//     *
//     * @return \Doctrine\Common\Collections\Collection
//     */
//    public function getAccessions()
//    {
//        return $this->accessions;
//    }
//
//
//
//    /**
//     * Add part
//     *
//     * @param \Oleg\UserdirectoryBundle\Entity\Part $part
//     * @return Institution
//     */
//    public function addPart(\Oleg\UserdirectoryBundle\Entity\Part $part)
//    {
//        if( !$this->parts->contains($part) ) {
//            $part->setInstitution($this);
//            $this->parts->add($part);
//        }
//    }
//    /**
//     * Remove part
//     *
//     * @param \Oleg\UserdirectoryBundle\Entity\Part $part
//     */
//    public function removePart(\Oleg\UserdirectoryBundle\Entity\Part $part)
//    {
//        $this->parts->removeElement($part);
//    }
//    /**
//     * Get order
//     *
//     * @return \Doctrine\Common\Collections\Collection
//     */
//    public function getParts()
//    {
//        return $this->parts;
//    }
//
//
//    /**
//     * Add block
//     *
//     * @param \Oleg\UserdirectoryBundle\Entity\Block $block
//     * @return Institution
//     */
//    public function addBlock(\Oleg\UserdirectoryBundle\Entity\Block $block)
//    {
//        if( !$this->blocks->contains($block) ) {
//            $block->setInstitution($this);
//            $this->blocks->add($block);
//        }
//    }
//    /**
//     * Remove block
//     *
//     * @param \Oleg\UserdirectoryBundle\Entity\Block $block
//     */
//    public function removeBlock(\Oleg\UserdirectoryBundle\Entity\Block $block)
//    {
//        $this->blocks->removeElement($block);
//    }
//    /**
//     * Get order
//     *
//     * @return \Doctrine\Common\Collections\Collection
//     */
//    public function getBlocks()
//    {
//        return $this->blocks;
//    }
//
//
//
//    /**
//     * Add slide
//     *
//     * @param \Oleg\UserdirectoryBundle\Entity\Slide $slide
//     * @return Institution
//     */
//    public function addSlide(\Oleg\UserdirectoryBundle\Entity\Slide $slide)
//    {
//        if( !$this->slides->contains($slide) ) {
//            $slide->setInstitution($this);
//            $this->slides->add($slide);
//        }
//    }
//    /**
//     * Remove slide
//     *
//     * @param \Oleg\UserdirectoryBundle\Entity\Slide $slide
//     */
//    public function removeSlide(\Oleg\UserdirectoryBundle\Entity\Slide $slide)
//    {
//        $this->slides->removeElement($slide);
//    }
//    /**
//     * Get order
//     *
//     * @return \Doctrine\Common\Collections\Collection
//     */
//    public function getSlides()
//    {
//        return $this->slides;
//    }


    /**
     * Add user
     *
     * @param \Oleg\UserdirectoryBundle\Entity\User $user
     * @return Institution
     */
    public function addUser(\Oleg\UserdirectoryBundle\Entity\User $user)
    {
        if( !$this->users->contains($user) ) {
            $this->users->add($user);
        }
    }
    /**
     * Remove user
     *
     * @param \Oleg\UserdirectoryBundle\Entity\User $user
     */
    public function removeUser(\Oleg\UserdirectoryBundle\Entity\User $user)
    {
        $this->users->removeElement($user);
    }
    /**
     * Get order
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getUsers()
    {
        return $this->users;
    }



    /**
     * Add department
     *
     * @param \Oleg\UserdirectoryBundle\Entity\Department $department
     * @return Institution
     */
    public function addDepartment(\Oleg\UserdirectoryBundle\Entity\Department $department)
    {
        if( !$this->departments->contains($department) ) {
            $department->setInstitution($this);
            $this->departments->add($department);
        }
    }
    /**
     * Remove department
     *
     * @param \Oleg\UserdirectoryBundle\Entity\Department $department
     */
    public function removeDepartment(\Oleg\UserdirectoryBundle\Entity\Department $department)
    {
        $this->departments->removeElement($department);
    }
    /**
     * Get order
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getDepartments()
    {
        return $this->departments;
    }



    /**
     * @param mixed $abbreviation
     */
    public function setAbbreviation($abbreviation)
    {
        $this->abbreviation = $abbreviation;
    }

    /**
     * @return mixed
     */
    public function getAbbreviation()
    {
        return $this->abbreviation;
    }



}