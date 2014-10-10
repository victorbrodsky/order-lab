<?php

namespace Oleg\UserdirectoryBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity
 * @ORM\Table(name="user_commentSubTypeList")
 */
class CommentSubTypeList extends ListAbstract
{

    /**
     * @ORM\OneToMany(targetEntity="CommentSubTypeList", mappedBy="original")
     **/
    protected $synonyms;

    /**
     * @ORM\ManyToOne(targetEntity="CommentSubTypeList", inversedBy="synonyms")
     * @ORM\JoinColumn(name="original_id", referencedColumnName="id")
     **/
    protected $original;

    /**
     * @ORM\ManyToOne(targetEntity="CommentTypeList", inversedBy="commentSubTypes")
     * @ORM\JoinColumn(name="parent", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $parent;



    public function __construct() {
        $this->synonyms = new ArrayCollection();
    }


    /**
     * Add synonyms
     *
     * @param \Oleg\UserdirectoryBundle\Entity\CommentSubTypeList $synonyms
     * @return CommentSubTypeList
     */
    public function addSynonym(\Oleg\UserdirectoryBundle\Entity\CommentSubTypeList $synonyms)
    {
        if( !$this->synonyms->contains($synonyms) ) {
            $this->synonyms->add($synonyms);
        }

        return $this;
    }

    /**
     * Remove synonyms
     *
     * @param \Oleg\UserdirectoryBundle\Entity\CommentSubTypeList $synonyms
     */
    public function removeSynonym(\Oleg\UserdirectoryBundle\Entity\CommentSubTypeList $synonyms)
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

    /**
     * @param mixed $parent
     */
    public function setParent($parent)
    {
        $this->parent = $parent;
    }

    /**
     * @return mixed
     */
    public function getParent()
    {
        return $this->parent;
    }

    //no children
    public function getChildren() {
        return array();
    }

}