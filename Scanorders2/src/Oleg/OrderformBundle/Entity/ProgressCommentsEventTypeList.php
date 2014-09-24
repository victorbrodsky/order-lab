<?php

namespace Oleg\OrderformBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

use Oleg\UserdirectoryBundle\Entity\ListAbstract;

/**
 * @ORM\Entity
 * @ORM\Table(name="scan_progressCommentsEventTypeList")
 */
class ProgressCommentsEventTypeList extends ListAbstract
{

    /**
     * @ORM\OneToMany(targetEntity="ProgressCommentsEventTypeList", mappedBy="original", cascade={"persist"})
     **/
    protected $synonyms;

    /**
     * @ORM\ManyToOne(targetEntity="ProgressCommentsEventTypeList", inversedBy="synonyms", cascade={"persist"})
     * @ORM\JoinColumn(name="original_id", referencedColumnName="id", nullable=true)
     **/
    protected $original;



    public function __construct() {
        $this->synonyms = new ArrayCollection();
    }



    /**
     * Add synonyms
     *
     * @param \Oleg\OrderformBundle\Entity\ProgressCommentsEventTypeList $synonyms
     * @return ProgressCommentsEventTypeList
     */
    public function addSynonym(\Oleg\OrderformBundle\Entity\ProgressCommentsEventTypeList $synonyms)
    {
        $this->synonyms->add($synonyms);

        return $this;
    }

    /**
     * Remove synonyms
     *
     * @param \Oleg\OrderformBundle\Entity\ProgressCommentsEventTypeList $synonyms
     */
    public function removeSynonym(\Oleg\OrderformBundle\Entity\ProgressCommentsEventTypeList $synonyms)
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