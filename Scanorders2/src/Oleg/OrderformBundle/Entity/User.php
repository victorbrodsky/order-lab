<?php

namespace Oleg\OrderformBundle\Entity;

use FOS\UserBundle\Model\User as BaseUser;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping\AttributeOverrides;
use Doctrine\ORM\Mapping\AttributeOverride;

//Use FOSUser bundle: https://github.com/FriendsOfSymfony/FOSUserBundle/blob/master/Resources/doc/index.md
//User is a reserved keyword in SQL so you cannot use it as table name

/**
 * @ORM\Entity
 * @ORM\Table(name="fos_user",
 *  indexes={
 *      @ORM\Index( name="username_idx", columns={"username"} ),
 *      @ORM\Index( name="displayName_idx", columns={"displayName"} )
 *  }
 * )
 * @ORM\AttributeOverrides({ @ORM\AttributeOverride( name="email", column=@ORM\Column(type="string", name="email", unique=false, nullable=true) ), @ORM\AttributeOverride( name="emailCanonical", column=@ORM\Column(type="string", name="email_canonical", unique=false, nullable=true) )
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
     * @ORM\OneToOne(targetEntity="UserPreferences", inversedBy="user", cascade={"persist"})
     */
    protected $preferences;

    /**
     * @ORM\ManyToMany(targetEntity="PathServiceList")
     * @ORM\JoinTable(name="fos_user_chiefservice",
     *      joinColumns={@ORM\JoinColumn(name="user_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="pathservice_id", referencedColumnName="id")}
     * )
     */
    protected $chiefservices;

    /**
     * @ORM\ManyToMany(targetEntity="Institution")
     * @ORM\JoinTable(name="fos_user_institution",
     *      joinColumns={@ORM\JoinColumn(name="user_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="institution_id", referencedColumnName="id")}
     * )
     */
    protected $institution;


    function __construct()
    {
        $this->pathologyServices = new ArrayCollection();
        $this->chiefservices = new ArrayCollection();
        $this->institution = new ArrayCollection();
        $this->setPreferences(new UserPreferences());
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
    public function getPathologyServices()
    {
        //return $this->pathologyServices;

        $resArr = new ArrayCollection();
        foreach( $this->pathologyServices as $service ) {
            //echo "service=".$service."<br>";
            if( $service->getId()."" == $this->getPrimaryPathologyService()."" ) {  //this service is a primary path service => put as the first element
                //$resArr->removeElement($service);
                //$resArr->first();
                //$firstEl = $resArr->get(0);
                $firstEl = $resArr->first();
                if( count($this->pathologyServices) > 1 && $firstEl ) {
                    //echo "firstEl=".$firstEl."<br>";
                    $resArr->set(0,$service); //set( mixed $key, mixed $value ) Adds/sets an element in the collection at the index / with the specified key.
                    $resArr->add($firstEl);
                } else {
                    $resArr->add($service);
                }
            } else {    //this service is not a primary path service
                $resArr->add($service);
            }
        }

//        foreach( $resArr as $res ) {
//            echo $res."|";
//        }
//        echo "<br>count=".count($resArr)."<br>";

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

    //chief services
    /**
     * @param mixed $chiefservices
     */
    public function setChiefservices($chiefservices)
    {
        $this->chiefservices = $chiefservices;

        //add service chiefs to services
        foreach( $chiefservices as $service ) {
            $this->addPathologyServices($service);
        }

    }

    /**
     * @return mixed
     */
    public function getChiefservices()
    {
        return $this->chiefservices;
    }

    public function addChiefservices(\Oleg\OrderformBundle\Entity\PathServiceList $chiefservice)
    {
        if( !$this->chiefservices->contains($chiefservice) ) {
            $this->chiefservices[] = $chiefservice;
        }

        //add service chiefs to services
        $this->addPathologyServices($chiefservice);

        return $this;
    }

    public function removeChiefservices(\Oleg\OrderformBundle\Entity\PathServiceList $chiefservices)
    {
        $this->chiefservices->removeElement($chiefservices);
    }

    /**
     * @param mixed $preferences
     */
    public function setPreferences($preferences)
    {
        $this->preferences = $preferences;
    }

    /**
     * @return mixed
     */
    public function getPreferences()
    {
        return $this->preferences;
    }

    /**
     * @return mixed
     */
    public function getInstitution()
    {
        return $this->institution;
    }

    public function addInstitution(\Oleg\OrderformBundle\Entity\Institution $institution)
    {
        if( !$this->institution->contains($institution) ) {
            $this->institution->add($institution);
        }
        return $this;
    }

    public function removeInstitution(\Oleg\OrderformBundle\Entity\Institution $institution)
    {
        $this->institution->removeElement($institution);
    }

    public function setInstitution( $institutions )
    {
        $this->institution->clear();
        foreach( $institutions as $institution ) {
            $this->addInstitution($institution);
        }
        return $this->institution;
    }

}

?>
