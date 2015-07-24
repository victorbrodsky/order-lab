<?php

namespace Oleg\UserdirectoryBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * "Organizational Group Types" with a url of /list/organizational-group-types
 * @ORM\Entity
 * @ORM\Table(name="user_commentGroupType")
 */
class CommentGroupType extends ListAbstract
{

    /**
     * @ORM\OneToMany(targetEntity="CommentGroupType", mappedBy="original")
     **/
    protected $synonyms;

    /**
     * @ORM\ManyToOne(targetEntity="CommentGroupType", inversedBy="synonyms")
     * @ORM\JoinColumn(name="original_id", referencedColumnName="id")
     **/
    protected $original;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $level;

    //name is the level title: Comment Category, Comment Name



    /**
     * @param mixed $level
     */
    public function setLevel($level)
    {
        $this->level = $level;
    }

    /**
     * @return mixed
     */
    public function getLevel()
    {
        return $this->level;
    }




}