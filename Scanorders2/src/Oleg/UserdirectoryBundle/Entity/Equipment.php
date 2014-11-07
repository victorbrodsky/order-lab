<?php

namespace Oleg\UserdirectoryBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

use Oleg\UserdirectoryBundle\Entity\ListAbstract;

/**
 * @ORM\Entity
 * @ORM\Table(name="user_equipment")
 */
class Equipment extends ListAbstract
{

    /**
     * @ORM\OneToMany(targetEntity="Equipment", mappedBy="original")
     **/
    protected $synonyms;

    /**
     * @ORM\ManyToOne(targetEntity="Equipment", inversedBy="synonyms")
     * @ORM\JoinColumn(name="original_id", referencedColumnName="id")
     **/
    protected $original;

    /**
     * @ORM\ManyToOne(targetEntity="EquipmentType", inversedBy="equipments", cascade={"persist"})
     * @ORM\JoinColumn(name="keytype_id", referencedColumnName="id", nullable=true)
     */
    protected $keytype;


    public function __construct() {
        $this->synonyms = new ArrayCollection();
    }

    /**
     * Add synonyms
     *
     * @param \Oleg\UserdirectoryBundle\Entity\Equipment $synonyms
     * @return Equipment
     */
    public function addSynonym(\Oleg\UserdirectoryBundle\Entity\Equipment $synonyms)
    {
        if( !$this->synonyms->contains($synonyms) ) {
            $this->synonyms->add($synonyms);
        }
    
        return $this;
    }

    /**
     * Remove synonyms
     *
     * @param \Oleg\UserdirectoryBundle\Entity\Equipment $synonyms
     */
    public function removeSynonym(\Oleg\UserdirectoryBundle\Entity\Equipment $synonyms)
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
     * @param \Oleg\UserdirectoryBundle\Entity\Equipment $original
     * @return Equipment
     */
    public function setOriginal(\Oleg\UserdirectoryBundle\Entity\Equipment $original = null)
    {
        $this->original = $original;
    
        return $this;
    }

    /**
     * Get original
     *
     * @return \Oleg\UserdirectoryBundle\Entity\Equipment
     */
    public function getOriginal()
    {
        return $this->original;
    }

    /**
     * @param mixed $keytype
     */
    public function setKeytype($keytype)
    {
        $this->keytype = $keytype;
    }

    /**
     * @return mixed
     */
    public function getKeytype()
    {
        return $this->keytype;
    }




}