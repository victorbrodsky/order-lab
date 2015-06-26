<?php

namespace Oleg\UserdirectoryBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

//@ORM\Entity(repositoryClass="Oleg\UserdirectoryBundle\Repository\TreeRepository")

//TODO: turn it to BaseCompositeNode
/**
 *
 * @ORM\Entity(repositoryClass="Oleg\UserdirectoryBundle\Repository\TreeRepository")
 *
 * * @UniqueEntity(
 *     fields={"parent", "name"},
 *     errorPath="name",
 *     message="Can not create a new category: the combination of the parent id and name is already in use."
 * )
 *
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