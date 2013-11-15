<?php

namespace Oleg\OrderformBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="diseasetype")
 */
class DiseaseType
{
    
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="string", nullable=true, length=100)
     */
    protected $diseaseType;

    /**
     * @ORM\Column(type="string", nullable=true, length=100)
     */
    protected $origin;

    /**
     * @ORM\ManyToOne(targetEntity="OrganList", inversedBy="partprimary", cascade={"persist"})
     * @ORM\JoinColumn(name="primaryorgan_id", referencedColumnName="id", nullable=true)
     */
    protected $primaryOrgan;

    /**
     * @param mixed $diseaseType
     */
    public function setDiseaseType($diseaseType)
    {
        $this->diseaseType = $diseaseType;
    }

    /**
     * @return mixed
     */
    public function getDiseaseType()
    {
        return $this->diseaseType;
    }

    /**
     * @param mixed $origin
     */
    public function setOrigin($origin)
    {
        $this->origin = $origin;
    }

    /**
     * @return mixed
     */
    public function getOrigin()
    {
        return $this->origin;
    }

    /**
     * @param mixed $primaryOrgan
     */
    public function setPrimaryOrgan($primaryOrgan)
    {
        $this->primaryOrgan = $primaryOrgan;
    }

    /**
     * @return mixed
     */
    public function getPrimaryOrgan()
    {
        return $this->primaryOrgan;
    }

    /**
     * @param mixed $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }


}