<?php

namespace Oleg\UserdirectoryBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;


/**
 * @ORM\Entity
 * @ORM\Table(name="specialty")
 */
class Specialty extends ListAbstract
{

    /**
     * @ORM\OneToMany(targetEntity="Specialty", mappedBy="original", cascade={"persist"})
     **/
    protected $synonyms;

    /**
     * @ORM\ManyToOne(targetEntity="Specialty", inversedBy="synonyms", cascade={"persist"})
     * @ORM\JoinColumn(name="original_id", referencedColumnName="id", nullable=true)
     **/
    protected $original;



    public function __construct() {
        $this->synonyms = new ArrayCollection();
    }



    /**
     * Add synonyms
     *
     * @param \Oleg\UserdirectoryBundle\Entity\Specialty $synonyms
     * @return Specialty
     */
    public function addSynonym(\Oleg\UserdirectoryBundle\Entity\Specialty $synonyms)
    {
        $this->synonyms->add($synonyms);

        return $this;
    }

    /**
     * Remove synonyms
     *
     * @param \Oleg\UserdirectoryBundle\Entity\Specialty $synonyms
     */
    public function removeSynonym(\Oleg\UserdirectoryBundle\Entity\Specialty $synonyms)
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


}