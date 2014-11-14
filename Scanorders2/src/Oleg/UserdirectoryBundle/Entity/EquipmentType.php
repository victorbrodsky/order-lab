<?php

namespace Oleg\UserdirectoryBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;


/**
 * @ORM\Entity
 * @ORM\Table(name="user_equipmentType")
 */
class EquipmentType extends ListAbstract
{

    /**
     * @ORM\OneToMany(targetEntity="EquipmentType", mappedBy="original", cascade={"persist"})
     **/
    protected $synonyms;

    /**
     * @ORM\ManyToOne(targetEntity="EquipmentType", inversedBy="synonyms", cascade={"persist"})
     * @ORM\JoinColumn(name="original_id", referencedColumnName="id", nullable=true)
     **/
    protected $original;


    /**
     * @ORM\OneToMany(targetEntity="Equipment", mappedBy="keytype")
     */
    protected $equipments;



    public function __construct() {
        $this->synonyms = new ArrayCollection();
        $this->equipments = new ArrayCollection();
    }


    



    public function addEquipment(\Oleg\UserdirectoryBundle\Entity\Equipment $equipment)
    {
        if( !$this->equipments->contains($equipment) ) {
            $this->equipments->add($equipment);
            $equipment->setKeytype($this);
        }
        return $this;
    }

    public function removeEquipment(\Oleg\UserdirectoryBundle\Entity\Equipment $equipment)
    {
        $this->equipments->removeElement($equipment);
    }

    public function getEquipments()
    {
        return $this->equipments;
    }

    public function getChildren() {
        return $this->getEquipments();
    }

}