<?php
/**
 * Created by PhpStorm.
 * User: DevServer
 * Date: 9/22/15
 * Time: 12:34 PM
 */

namespace Oleg\FellAppBundle\Entity;


use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;



/**
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 * @ORM\Table(name="fellapp_rank")
 */
class Rank {

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;


    /**
     * @ORM\OneToOne(targetEntity="FellowshipApplication", mappedBy="rank")
     */
    private $fellapp;


    /**
     * @ORM\Column(name="rank", type="integer", nullable=true)
     */
    private $rank;


    /**
     * @ORM\ManyToOne(targetEntity="Oleg\UserdirectoryBundle\Entity\User")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=true)
     */
    private $user;

    /**
     * @var array
     * @ORM\Column(type="array", nullable=true)
     */
    private $userroles = array();

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $creationdate;

    /**
     * @ORM\ManyToOne(targetEntity="Oleg\UserdirectoryBundle\Entity\User")
     * @ORM\JoinColumn(name="updateuser_id", referencedColumnName="id", nullable=true)
     */
    private $updateuser;

    /**
     * @var array
     * @ORM\Column(type="array", nullable=true)
     */
    private $updateuserroles = array();

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $updatedate;







    public function __construct() {
        $this->creationdate = new \DateTime();

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
    public function getFellapp()
    {
        return $this->fellapp;
    }

    /**
     * @param mixed $fellapp
     */
    public function setFellapp($fellapp)
    {
        $this->fellapp = $fellapp;
    }

    /**
     * @return mixed
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param mixed $user
     */
    public function setUser($user)
    {
        $this->user = $user;
    }

    /**
     * @return array
     */
    public function getUserroles()
    {
        return $this->userroles;
    }
    /**
     * @param array $userroles
     */
    public function setUserroles($userroles)
    {
        if( $userroles ) {
            foreach( $userroles as $role ) {
                $this->addUserrole($role."");
            }
        }
    }
    public function addUserrole($role) {
        $this->roles[] = $role;
        return $this;
    }

    /**
     * @return array
     */
    public function getUpdateuserroles()
    {
        return $this->updateuserroles;
    }
    /**
     * @param array $updateuserroles
     */
    public function setUpdateuserroles($updateuserroles)
    {
        if( $updateuserroles ) {
            foreach( $updateuserroles as $role ) {
                $this->addUpdateuserrole($role."");
            }
        }
    }
    public function addUpdateuserrole($role) {
        $this->updateuserroles[] = $role;
        return $this;
    }


    /**
     * @return \DateTime
     */
    public function getCreationdate()
    {
        return $this->creationdate;
    }

    /**
     * @param \DateTime $creationdate
     */
    public function setCreationdate($creationdate)
    {
        $this->creationdate = $creationdate;
    }

    /**
     * @return mixed
     */
    public function getUpdateuser()
    {
        return $this->updateuser;
    }

    /**
     * @param mixed $updateuser
     */
    public function setUpdateuser($updateuser)
    {
        $this->updateuser = $updateuser;
    }

    /**
     * @return \DateTime
     */
    public function getUpdatedate()
    {
        return $this->updatedate;
    }

    /**
     * @param \DateTime $updatedate
     * @ORM\PreUpdate
     */
    public function setUpdatedate()
    {
        $this->updatedate = new \DateTime();
    }

    /**
     * @return mixed
     */
    public function getRank()
    {
        return $this->rank;
    }

    /**
     * @param mixed $rank
     */
    public function setRank($rank)
    {
        $this->rank = $rank;
    }



} 