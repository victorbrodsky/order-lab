<?php

namespace Oleg\UserdirectoryBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="user_researchLab")
 */
class ResearchLab extends BaseUserAttributes
{

    /**
     * @ORM\ManyToOne(targetEntity="User", inversedBy="researchLabs")
     * @ORM\JoinColumn(name="fosuser", referencedColumnName="id", onDelete="CASCADE")
     */
    private $user;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $foundedDate;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $dissolvedDate;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $comment;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $location;

    /**
     * @ORM\ManyToOne(targetEntity="ResearchLabTitleList")
     * @ORM\JoinColumn(name="state_id", referencedColumnName="id", nullable=true)
     **/
    private $researchLabTitle;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    protected $researchPI;

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
     * @param mixed $dissolvedDate
     */
    public function setDissolvedDate($dissolvedDate)
    {
        $this->dissolvedDate = $dissolvedDate;
    }

    /**
     * @return mixed
     */
    public function getDissolvedDate()
    {
        return $this->dissolvedDate;
    }

    /**
     * @param mixed $foundedDate
     */
    public function setFoundedDate($foundedDate)
    {
        $this->foundedDate = $foundedDate;
    }

    /**
     * @return mixed
     */
    public function getFoundedDate()
    {
        return $this->foundedDate;
    }

    /**
     * @param mixed $location
     */
    public function setLocation($location)
    {
        $this->location = $location;
    }

    /**
     * @return mixed
     */
    public function getLocation()
    {
        return $this->location;
    }

    /**
     * @param mixed $user
     */
    public function setUser($user)
    {
        $this->user = $user;
    }

    /**
     * @return mixed
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param mixed $researchPI
     */
    public function setResearchPI($researchPI)
    {
        $this->researchPI = $researchPI;
    }

    /**
     * @return mixed
     */
    public function getResearchPI()
    {
        return $this->researchPI;
    }

    /**
     * @param mixed $researchLabTitle
     */
    public function setResearchLabTitle($researchLabTitle)
    {
        $this->researchLabTitle = $researchLabTitle;
    }

    /**
     * @return mixed
     */
    public function getResearchLabTitle()
    {
        return $this->researchLabTitle;
    }






    public function __toString() {
        return "Research Lab";
    }


}