<?php

namespace Oleg\OrderformBundle\Entity;

use FOS\UserBundle\Model\User as BaseUser;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping\AttributeOverrides;
use Doctrine\ORM\Mapping\AttributeOverride;

//use FR3D\LdapBundle\Model\LdapUserInterface;

//TODO: fix: Invalid field override named 'email' for class 'Oleg\OrderformBundle\Entity\User'.
/**
 * use FOSUser bundle: https://github.com/FriendsOfSymfony/FOSUserBundle/blob/master/Resources/doc/index.md
 * User is a reserved keyword in SQL so you cannot use it as table name
 * @ORM\Entity
 * @ORM\Table(name="fos_user")
 * @ORM\AttributeOverrides({
 *      @ORM\AttributeOverride(name="email", column=@ORM\Column(type="string", name="email", length=255, unique=false, nullable=true)),
 *      @ORM\AttributeOverride(name="emailCanonical", column=@ORM\Column(type="string", name="email_canonical", length=255, unique=false, nullable=true))
 * })
 */
class User extends BaseUser
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

//    /**
//     * @ORM\ManyToMany(targetEntity="Group")
//     * @ORM\JoinTable(name="fos_user_user_group",
//     *      joinColumns={@ORM\JoinColumn(name="user_id", referencedColumnName="id")},
//     *      inverseJoinColumns={@ORM\JoinColumn(name="group_id", referencedColumnName="id")}
//     * )
//     */
//    protected $groups;

    /**
     * @ORM\ManyToMany(targetEntity="PathServiceList")
     * @ORM\JoinTable(name="fos_user_pathservice",
     *      joinColumns={@ORM\JoinColumn(name="user_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="pathservice_id", referencedColumnName="id")}
     * )
     */
    protected $pathologyServices;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $primaryPathologyService;

    /**
     * @ORM\Column(name="phone", type="string", nullable=true)
     */
    protected $phone;

    /**
     * @ORM\Column(name="firstName", type="string", nullable=true)
     */
    protected $firstName;

    /**
     * @ORM\Column(name="lastName", type="string", nullable=true)
     */
    protected $lastName;

    /**
     * @ORM\Column(name="title", type="string", nullable=true)
     */
    protected $title;

    /**
     * @ORM\Column(name="displayName", type="string", nullable=true)
     */
    protected $displayName;

    /**
     * @ORM\Column(name="fax", type="string", nullable=true)
     */
    protected $fax;

    /**
     * @ORM\Column(name="office", type="string", nullable=true)
     */
    protected $office;

    /**
     * @ORM\Column(name="createdby", type="string", nullable=true)
     */
    protected $createdby;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $appliedforaccess;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $appliedforaccessdate;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $timezone;

//    /**
//     * @var array
//     * @ORM\Column(type="array", nullable=true)
//     */
//    private $chiefservices;
    /**
     * @ORM\ManyToMany(targetEntity="PathServiceList")
     * @ORM\JoinTable(name="fos_user_chiefservice",
     *      joinColumns={@ORM\JoinColumn(name="user_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="pathservice_id", referencedColumnName="id")}
     * )
     */
    protected $chiefservices;

    function __construct()
    {
        $this->pathologyServices = new ArrayCollection();
        $this->chiefservices = new ArrayCollection();   //array();
        parent::__construct();
    }

    /**
     * @param mixed $pathologyServices
     */
    public function setPathologyServices($pathologyServices)
    {
        if( $pathologyServices->first() ) {
            $this->primaryPathologyService = $pathologyServices->first()->getId();
        } else {
            $this->primaryPathologyService = NULL;
        }
        $this->pathologyServices = $pathologyServices;
    }

    /**
     * @return mixed
     */
//    public function getPathologyServices()
//    {
//        return $this->pathologyServices;
//    }
    /**
     * @return mixed
     */
    public function getPathologyServices()
    {
        //return $this->pathologyServices;

        $resArr = new ArrayCollection();
        foreach( $this->pathologyServices as $service ) {
            if( $service->getId()."" == $this->getPrimaryPathologyService()."" ) {
                //$resArr->removeElement($service);
                //$resArr->first();
                if( count($this->pathologyServices) > 1 ) {
                    $firstEl = $resArr->get(0);
                    $resArr->set(0,$service);
                    $resArr->add($firstEl);
                } else {
                    $resArr->add($service);
                }
            } else {
                $resArr->add($service);
            }
        }

        return $resArr;
    }

    /**
     * @param mixed $phone
     */
    public function setPhone($phone)
    {
        $this->phone = $phone;
    }

    /**
     * @return mixed
     */
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * @param mixed $firstName
     */
    public function setFirstName($firstName)
    {
        $this->firstName = $firstName;
    }

    /**
     * @return mixed
     */
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * @param mixed $lastName
     */
    public function setLastName($lastName)
    {
        $this->lastName = $lastName;
    }

    /**
     * @return mixed
     */
    public function getLastName()
    {
        return $this->lastName;
    }

    /**
     * @param mixed $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * @return mixed
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param mixed $displayName
     */
    public function setDisplayName($displayName)
    {
        $this->displayName = $displayName;
    }

    /**
     * @return mixed
     */
    public function getDisplayName()
    {
        return $this->displayName;
    }

    /**
     * @param mixed $fax
     */
    public function setFax($fax)
    {
        $this->fax = $fax;
    }

    /**
     * @return mixed
     */
    public function getFax()
    {
        return $this->fax;
    }

    /**
     * @param mixed $office
     */
    public function setOffice($office)
    {
        $this->office = $office;
    }

    /**
     * @return mixed
     */
    public function getOffice()
    {
        return $this->office;
    }

    public function addPathologyServices(\Oleg\OrderformBundle\Entity\PathServiceList $pathologyServices)
    {
        if( !$this->pathologyServices->contains($pathologyServices) ) {
            $this->pathologyServices[] = $pathologyServices;
        }

        return $this;
    }

    public function removePathologyServices(\Oleg\OrderformBundle\Entity\PathServiceList $pathologyServices)
    {
        $this->pathologyServices->removeElement($pathologyServices);
    }

    /**
     * @param mixed $createdby
     */
    public function setCreatedby($createdby = 'ldap')
    {
        $this->createdby = $createdby;
    }

    /**
     * @return mixed
     */
    public function getCreatedby()
    {
        return $this->createdby;
    }

    public function __toString() {
//        return "User: ".$this->username.", email=".$this->email.", PathServiceList count=".count($this->pathologyServices)."<br>";
        if( $this->displayName && $this->displayName != "" ) {
            return $this->username." - ".$this->displayName;
        } else {
            return $this->username;
        }
    }

    public function hasRole($role)
    {
        return in_array(strtoupper($role), $this->roles, true);
    }

    /**
     * @param \DateTime $appliedforaccessdate
     */
    public function setAppliedforaccessdate($appliedforaccessdate)
    {
        $this->appliedforaccessdate = $appliedforaccessdate;
    }

    /**
     * @return \DateTime
     */
    public function getAppliedforaccessdate()
    {
        return $this->appliedforaccessdate;
    }

    /**
     * @param mixed $appliedforaccess
     */
    public function setAppliedforaccess($appliedforaccess)
    {
        $this->appliedforaccess = $appliedforaccess;
    }

    /**
     * @return mixed
     */
    public function getAppliedforaccess()
    {
        return $this->appliedforaccess;
    }

    /**
     * @param mixed $primaryPathologyService
     */
    public function setPrimaryPathologyService($primaryPathologyService)
    {
        $this->primaryPathologyService = $primaryPathologyService;
    }

    /**
     * @return mixed
     */
    public function getPrimaryPathologyService()
    {
        return $this->primaryPathologyService;
    }

    /**
     * @param mixed $timezone
     */
    public function setTimezone($timezone)
    {
        $this->timezone = $timezone;
    }

    /**
     * @return mixed
     */
    public function getTimezone()
    {
        return $this->timezone;
    }

    //chief services
    /**
     * @param mixed $chiefservices
     */
    public function setChiefservices($chiefservices)
    {
        $this->chiefservices = $chiefservices;
    }

    /**
     * @return mixed
     */
    public function getChiefservices()
    {
        return $this->chiefservices;
    }

    public function addChiefservices(\Oleg\OrderformBundle\Entity\PathServiceList $chiefservices)
    {
        if( !$this->chiefservices->contains($chiefservices) ) {
            $this->chiefservices[] = $chiefservices;
        }

        return $this;
    }

    public function removeChiefservices(\Oleg\OrderformBundle\Entity\PathServiceList $chiefservices)
    {
        $this->chiefservices->removeElement($chiefservices);
    }

}

?>
