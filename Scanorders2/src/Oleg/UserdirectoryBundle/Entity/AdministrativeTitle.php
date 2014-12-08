<?php

namespace Oleg\UserdirectoryBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity
 * @ORM\Table(name="user_administrativeTitle")
 */
class AdministrativeTitle extends BaseTitle
{

    /**
     * @ORM\ManyToOne(targetEntity="AdminTitleList")
     * @ORM\JoinColumn(name="name", referencedColumnName="id", nullable=true)
     **/
    protected $name;

    /**
     * @ORM\ManyToOne(targetEntity="User", inversedBy="administrativeTitles")
     * @ORM\JoinColumn(name="fosuser", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $user;

    /**
     * @ORM\ManyToMany(targetEntity="User")
     * @ORM\JoinTable(name="user_administrative_boss",
     *      joinColumns={@ORM\JoinColumn(name="administrative_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="boss_id", referencedColumnName="id")}
     * )
     **/
    protected $boss;



    function __construct($author=null)
    {
        parent::__construct($author);

        $this->boss = new ArrayCollection();
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
     * Add boss
     *
     * @param \Oleg\OrderformBundle\Entity\User $boss
     * @return User
     */
    public function addBoss($boss)
    {
        if( !$this->boss->contains($boss) ) {
            $this->boss->add($boss);
        }

        return $this;
    }
    public function addBos($boss)
    {
        return $this->addBoss($boss);
    }
    /**
     * Remove boss
     *
     * @param \Oleg\OrderformBundle\Entity\User $boss
     */
    public function removeBoss($boss)
    {
        $this->boss->removeElement($boss);
    }
    public function removeBos($boss)
    {
        $this->removeBoss($boss);
    }

    /**
     * Get boss
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getBoss()
    {
        return $this->boss;
    }


    public function __toString() {
        return "Administrative Title";
    }


}