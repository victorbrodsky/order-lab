<?php

namespace Oleg\OrderformBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

use Oleg\UserdirectoryBundle\Entity\ListAbstract;

/**
 * @ORM\Entity
 * @ORM\Table(name="scan_encounterList",
 *  indexes={
 *      @ORM\Index( name="encounter_name_idx", columns={"name"} )
 *  }
 * )
 */
class EncounterList extends ListAbstract
{
    /**
     * @ORM\OneToMany(targetEntity="EncounterList", mappedBy="original", cascade={"persist"})
     **/
    protected $synonyms;

    /**
     * @ORM\ManyToOne(targetEntity="EncounterList", inversedBy="synonyms", cascade={"persist"})
     * @ORM\JoinColumn(name="original_id", referencedColumnName="id", nullable=true)
     **/
    protected $original;

    /**
     * @ORM\OneToMany(targetEntity="EncounterName", mappedBy="field")
     */
    protected $encountername;


    public function __construct() {
        $this->synonyms = new ArrayCollection();
        $this->encountername = new ArrayCollection();
    }   

    /**
     * Add EncounterName
     *
     * @param \Oleg\OrderformBundle\Entity\EncounterName $encountername
     * @return EncounterList
     */
    public function addEncountername(\Oleg\OrderformBundle\Entity\EncounterName $encountername)
    {
        if( $encountername && !$this->encountername->contains($encountername) ) {
            $this->encountername->add($encountername);
            $encountername->setField($this);
        }
    
        return $this;
    }

    /**
     * Remove encountername
     *
     * @param \Oleg\OrderformBundle\Entity\EncounterName $encountername
     */
    public function removeEncounterName(\Oleg\OrderformBundle\Entity\EncounterName $encountername)
    {
        $this->encountername->removeElement($encountername);
    }

    /**
     * Get encountername
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getEncountername()
    {
        return $this->encountername;
    }


}