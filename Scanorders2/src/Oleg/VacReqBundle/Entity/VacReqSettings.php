<?php

/**
 * Created by PhpStorm.
 * User: ch3
 * Date: 4/11/2016
 * Time: 11:35 AM
 */

namespace Oleg\VacReqBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;


/**
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 * @ORM\Table(name="vacreq_settings")
 */
class VacReqSettings
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
     * @ORM\ManyToOne(targetEntity="Oleg\UserdirectoryBundle\Entity\Institution")
     */
    private $institution;


    /**
     * @ORM\ManyToMany(targetEntity="Oleg\UserdirectoryBundle\Entity\User", cascade={"persist"})
     * @ORM\JoinTable(name="vacreq_settings_user",
     *      joinColumns={@ORM\JoinColumn(name="settings_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="emailuser_id", referencedColumnName="id")}
     *      )
     **/
    private $emailUsers;




    public function __construct($institution) {
        $this->emailUsers = new ArrayCollection();
        $this->setInstitution($institution);
    }




    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }


    /**
     * @return mixed
     */
    public function getInstitution()
    {
        return $this->institution;
    }

    /**
     * @param mixed $institution
     */
    public function setInstitution($institution)
    {
        $this->institution = $institution;
    }


    public function getEmailUsers()
    {
        return $this->emailUsers;
    }
    public function addEmailUser($item)
    {
        if( $item && !$this->emailUsers->contains($item) ) {
            $this->emailUsers->add($item);
        }
        return $this;
    }
    public function removeEmailUser($item)
    {
        $this->emailUsers->removeElement($item);
    }


    public function __toString()
    {
        return "VacReqSettings: institutionId=".$this->getId()." => count emailUsers=".count($this->getEmailUsers())."<br>";
    }
}