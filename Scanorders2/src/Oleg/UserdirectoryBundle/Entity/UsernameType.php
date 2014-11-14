<?php

namespace Oleg\UserdirectoryBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;


/**
 * @ORM\Entity
 * @ORM\Table(name="user_usernameType")
 */
class UsernameType extends ListAbstract
{

    /**
     * @ORM\OneToMany(targetEntity="EventTypeList", mappedBy="original", cascade={"persist"})
     **/
    protected $synonyms;

    /**
     * @ORM\ManyToOne(targetEntity="EventTypeList", inversedBy="synonyms", cascade={"persist"})
     * @ORM\JoinColumn(name="original_id", referencedColumnName="id", nullable=true)
     **/
    protected $original;

    /**
     * @ORM\OneToMany(targetEntity="User", mappedBy="keytype")
     */
    protected $users;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $abbreviation;


    public function __construct() {
        $this->synonyms = new ArrayCollection();
        $this->users = new ArrayCollection();
    }



    


    public function addUser(\Oleg\UserdirectoryBundle\Entity\User $user)
    {
        if( !$this->users->contains($user) ) {
            $this->users->add($user);
        }
        return $this;
    }

    public function removeUser(\Oleg\UserdirectoryBundle\Entity\User $user)
    {
        $this->users->removeElement($user);
    }

    public function getUsers()
    {
        return $this->users;
    }

    /**
     * @param mixed $abbreviation
     */
    public function setAbbreviation($abbreviation)
    {
        $this->abbreviation = $abbreviation;
    }

    /**
     * @return mixed
     */
    public function getAbbreviation()
    {
        return $this->abbreviation;
    }



}