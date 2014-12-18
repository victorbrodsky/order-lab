<?php

namespace Oleg\UserdirectoryBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 * @ORM\Table(name="user_researchLabComment")
 */
class ResearchLabComment
{

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="ResearchLab", inversedBy="comments")
     * @ORM\JoinColumn(name="researchLab_id", referencedColumnName="id", onDelete="CASCADE")
     **/
    private $researchLab;

    /**
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumn(name="author_id", referencedColumnName="id")
     **/
    private $author;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $createdate;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $comment;




    /**
     * @param mixed $comment
     */
    public function setComment($comment)
    {
        $this->comment = $comment;
    }

    /**
     * @return mixed
     */
    public function getComment()
    {
        return $this->comment;
    }

    /**
     * @param mixed $author
     */
    public function setAuthor($author)
    {
        $this->author = $author;
    }

    /**
     * @return mixed
     */
    public function getAuthor()
    {
        return $this->author;
    }

    /**
     * @ORM\PrePersist
     */
    public function setCreatedate()
    {
        $this->createdate = new \DateTime();;
    }

    /**
     * @return \DateTime
     */
    public function getCreatedate()
    {
        return $this->createdate;
    }

    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $researchLab
     */
    public function setResearchLab($researchLab)
    {
        $this->researchLab = $researchLab;
    }

    /**
     * @return mixed
     */
    public function getResearchLab()
    {
        return $this->researchLab;
    }


    public function __toString() {
        return "Research Lab comment: id=".$this->id.", text=".$this->comment.", res lab name=".$this->getResearchLab()->getName().", res lab id=".$this->getResearchLab()->getId()."<br>";
        //return "Research Lab comment";
    }


}