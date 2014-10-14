<?php

namespace Oleg\UserdirectoryBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity()
 * @ORM\Table(name="user_commentTypeList")
 */
class CommentTypeList extends ListAbstract
{


    /**
     * @ORM\OneToMany(targetEntity="CommentTypeList", mappedBy="original")
     **/
    protected $synonyms;

    /**
     * @ORM\ManyToOne(targetEntity="CommentTypeList", inversedBy="synonyms")
     * @ORM\JoinColumn(name="original_id", referencedColumnName="id")
     **/
    protected $original;

    /**
     * @ORM\OneToMany(targetEntity="CommentSubTypeList", mappedBy="parent")
     */
    protected $commentSubTypes;



    public function __construct() {
        $this->synonyms = new ArrayCollection();
        $this->commentSubTypes = new ArrayCollection();
    }


    /**
     * Add synonyms
     *
     * @param \Oleg\UserdirectoryBundle\Entity\CommentTypeList $synonyms
     * @return CommentTypeList
     */
    public function addSynonym(\Oleg\UserdirectoryBundle\Entity\CommentTypeList $synonyms)
    {
        if( !$this->synonyms->contains($synonyms) ) {
            $this->synonyms->add($synonyms);
        }

        return $this;
    }

    /**
     * Remove synonyms
     *
     * @param \Oleg\UserdirectoryBundle\Entity\CommentTypeList $synonyms
     */
    public function removeSynonym(\Oleg\UserdirectoryBundle\Entity\CommentTypeList $synonyms)
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
     * Add commentSubTypeList
     *
     * @param \Oleg\UserdirectoryBundle\Entity\CommentSubTypeList $commentSubType
     * @return Institution
     */
    public function addCommentSubType(\Oleg\UserdirectoryBundle\Entity\CommentSubTypeList $commentSubType)
    {
        if( !$this->commentSubTypes->contains($commentSubType) ) {
            $commentSubType->setParent($this);
            $this->commentSubTypes->add($commentSubType);
        }
    }
    public function addCommentSubTypeList($commentSubType) {
        $this->addCommentSubType($commentSubType);
    }
    /**
     * Remove commentSubTypeList
     *
     * @param \Oleg\UserdirectoryBundle\Entity\CommentSubTypeList $commentSubType
     */
    public function removeCommentSubType(\Oleg\UserdirectoryBundle\Entity\CommentSubTypeList $commentSubType)
    {
        $this->commentSubTypes->removeElement($commentSubType);
    }
    /**
     * Get order
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getCommentSubTypes()
    {
        return $this->commentSubTypes;
    }

    public function getChildren()
    {
        return $this->getCommentSubTypes();
    }
    public function addChild($child)
    {
        return $this->addCommentSubType($child);
    }
    public function removeChild($child)
    {
        return $this->removeCommentSubType($child);
    }


}