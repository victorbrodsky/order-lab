<?php

namespace Oleg\UserdirectoryBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="user_researchLab")
 */
class ResearchLab extends BaseUserAttributes
{

    /**
     * @ORM\ManyToMany(targetEntity="User", inversedBy="researchLabs")
     * @ORM\JoinTable(name="user_researchlab_user",
     *      joinColumns={@ORM\JoinColumn(name="researchlab_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="user_id", referencedColumnName="id")}
     * )
     **/
    protected $user;

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

//    /**
//     * @ORM\Column(type="string", nullable=true)
//     */
//    private $location;
    /**
     * @ORM\ManyToOne(targetEntity="Location")
     * @ORM\JoinColumn(name="location", referencedColumnName="id", nullable=true)
     **/
    private $location;

    /**
     * @ORM\ManyToOne(targetEntity="ResearchLabTitleList", inversedBy="researchlab")
     * @ORM\JoinColumn(name="researchlabtitle_id", referencedColumnName="id", nullable=true)
     **/
    private $researchLabTitle;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    protected $researchPI;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $weblink;

    public function __construct($author=null) {
        parent::__construct($author);
        $this->user = new ArrayCollection();
    }

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
     * Add user
     *
     * @param \Oleg\OrderformBundle\Entity\User $user
     * @return User
     */
    public function addUser($user)
    {
        if( !$this->user->contains($user) ) {
            $this->user->add($user);
        }

        return $this;
    }
    /**
     * Remove user
     *
     * @param \Oleg\OrderformBundle\Entity\User $user
     */
    public function removeUser($user)
    {
        $this->user->removeElement($user);
    }

    /**
     * Get user
     *
     * @return \Doctrine\Common\Collections\Collection
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

    /**
     * @param mixed $weblink
     */
    public function setWeblink($weblink)
    {
        $this->weblink = $weblink;
    }

    /**
     * @return mixed
     */
    public function getWeblink()
    {
        return $this->weblink;
    }






    public function __toString() {
        return "Research Lab";
    }


}