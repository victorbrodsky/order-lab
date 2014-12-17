<?php

namespace Oleg\UserdirectoryBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 * @ORM\Table(name="user_researchLabPI")
 */
class ResearchLabPI
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
     * @ORM\ManyToOne(targetEntity="ResearchLab", inversedBy="pis")
     * @ORM\JoinColumn(name="researchLab_id", referencedColumnName="id", onDelete="CASCADE")
     **/
    private $researchLab;

    /**
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumn(name="fosuser_id", referencedColumnName="id")
     **/
    private $pi;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $createdate;


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
     * @param mixed $pi
     */
    public function setPi($pi)
    {
        $this->pi = $pi;
    }

    /**
     * @return mixed
     */
    public function getPi()
    {
        return $this->pi;
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
        return "Research Lab pi: id=".$this->id.", text=".$this->pi."<br>";
    }

}