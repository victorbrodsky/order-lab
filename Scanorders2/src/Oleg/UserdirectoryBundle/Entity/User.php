<?php

namespace Oleg\UserdirectoryBundle\Entity;

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
 * )
 *
 */
class User extends BaseUser
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(name="firstName", type="string", nullable=true)
     */
    private $firstName;

    /**
     * @ORM\Column(name="middleName", type="string", nullable=true)
     */
    private $middleName;

    /**
     * @ORM\Column(name="lastName", type="string", nullable=true)
     */
    private $lastName;

    /**
     * @ORM\Column(name="displayName", type="string", nullable=true)
     */
    private $displayName;

    /**
     * @ORM\Column(name="createdby", type="string", nullable=true)
     */
    private $createdby;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $appliedforaccess;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $appliedforaccessdate;

    /**
     * @ORM\OneToOne(targetEntity="UserPreferences", inversedBy="user", cascade={"persist"})
     */
    private $preferences;

    /**
     * @ORM\OneToMany(targetEntity="Location", mappedBy="user", cascade={"persist"})
     */
    private $locations;

    /**
     * @ORM\OneToMany(targetEntity="AdmnistrativeTitle", mappedBy="user", cascade={"persist"})
     */
    private $admnistrativeTitles;

    /**
     * @ORM\OneToMany(targetEntity="AppointmentTitle", mappedBy="user", cascade={"persist"})
     */
    private $appointmentTitles;


    /**
     * Each user must be linked with one or many Institutions. We can link a user with Division and then get Institution by looping trhough all users's divisions:
     * User->getDivisions() => foreach division: institution=division->getDepartment->getInstitution => institutions[] = institution
     * However, keep reference to institution for performance gain.
     *
     * @ORM\ManyToMany(targetEntity="Institution", inversedBy="users")
     * @ORM\JoinTable(name="fos_user_institution",
     *      joinColumns={@ORM\JoinColumn(name="user_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="institution_id", referencedColumnName="id")}
     * )
     */
    private $institution;

    /**
     * @ORM\ManyToMany(targetEntity="Department", inversedBy="users")
     * @ORM\JoinTable(name="fos_user_department",
     *      joinColumns={@ORM\JoinColumn(name="user_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="department_id", referencedColumnName="id")}
     * )
     */
    private $department;

    /**
     * @ORM\ManyToMany(targetEntity="Division", inversedBy="users")
     * @ORM\JoinTable(name="fos_user_division",
     *      joinColumns={@ORM\JoinColumn(name="user_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="division_id", referencedColumnName="id")}
     * )
     */
    private $division;

    /**
     * @ORM\ManyToMany(targetEntity="Service", inversedBy="users")
     * @ORM\JoinTable(name="fos_user_service",
     *      joinColumns={@ORM\JoinColumn(name="user_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="service_id", referencedColumnName="id")}
     * )
     */
    private $service;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $primaryDivision;   //$primaryPathologyService;

    /**
     * @ORM\ManyToMany(targetEntity="Division")
     * @ORM\JoinTable(name="fos_user_chiefdivision",
     *      joinColumns={@ORM\JoinColumn(name="user_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="pathservice_id", referencedColumnName="id")}
     * )
     */
    private $chiefDivisions;



    function __construct()
    {
        $this->locations = new ArrayCollection();
        $this->admnistrativeTitles = new ArrayCollection();
        $this->appointmentTitles = new ArrayCollection();

        $this->institution = new ArrayCollection();
        $this->department = new ArrayCollection();
        $this->division = new ArrayCollection();
        $this->service = new ArrayCollection();

        $this->chiefDivisions = new ArrayCollection();

        $this->setPreferences(new UserPreferences());

        //two default locations: "main office" and "home"
        $mainLocation = new Location();
        $homeLocation = new Location();
        $mainLocation->setName('Main Office');
        $homeLocation->setName('Home');
        $this->locations->set(0,$mainLocation);  //main has index 0
        $mainLocation->setUser($this);
        $this->locations->set(1,$homeLocation);  //home hsa index 1
        $homeLocation->setUser($this);

        //one default Admnistrative Title
        $AdmnistrativeTitle = new AdmnistrativeTitle();
        $this->addAdmnistrativeTitle($AdmnistrativeTitle);

        parent::__construct();
    }

    /**
     * @param mixed $division
     */
    public function setDivision($division)
    {
        if( $division->first() ) {
            $this->primaryDivision = $division->first()->getId();
        } else {
            $this->primaryDivision = NULL;
        }
        $this->division = $division;
    }


    /**
     * @return mixed
     */
    public function getDivision()
    {
        //return $this->division;

        $resArr = new ArrayCollection();
        foreach( $this->division as $service ) {
            //echo "service=".$service."<br>";
            if( $service->getId()."" == $this->getPrimaryDivision()."" ) {  //this service is a primary path service => put as the first element
                //$resArr->removeElement($service);
                //$resArr->first();
                //$firstEl = $resArr->get(0);
                $firstEl = $resArr->first();
                if( count($this->division) > 1 && $firstEl ) {
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

        return $resArr;
    }

//    /**
//     * @param mixed $phone
//     */
//    public function setPhone($phone)
//    {
//        $this->phone = $phone;
//    }
//
//    /**
//     * @return mixed
//     */
//    public function getPhone()
//    {
//        return $this->phone;
//    }

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
     * @param mixed $middleName
     */
    public function setMiddleName($middleName)
    {
        $this->middleName = $middleName;
    }

    /**
     * @return mixed
     */
    public function getMiddleName()
    {
        return $this->middleName;
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
        $this->getAdmnistrativeTitles()->first()->setName($title);
    }

//    /**
//     * @return mixed
//     */
//    public function getTitle()
//    {
//        return $this->title;
//    }

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

//    /**
//     * @param mixed $fax
//     */
//    public function setFax($fax)
//    {
//        $this->fax = $fax;
//    }
//
//    /**
//     * @return mixed
//     */
//    public function getFax()
//    {
//        return $this->fax;
//    }

//    /**
//     * @param mixed $office
//     */
//    public function setOffice($office)
//    {
//        $this->office = $office;
//    }
//
//    /**
//     * @return mixed
//     */
//    public function getOffice()
//    {
//        return $this->office;
//    }

    public function addDivision(\Oleg\UserdirectoryBundle\Entity\Division $division)
    {
        if( !$this->division->contains($division) ) {
            $this->division->add($division);
        }

        return $this;
    }

    public function removeDivision(\Oleg\UserdirectoryBundle\Entity\Division $division)
    {
        $this->division->removeElement($division);
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
//        return "User: ".$this->username.", email=".$this->email.", PathServiceList count=".count($this->division)."<br>";
        if( $this->displayName && $this->displayName != "" ) {
            return $this->username." - ".$this->displayName;
        } else {
            return $this->username;
        }
    }

    public function getMainLocation() {
        return $this->getLocations()->get(0);
    }

    public function getHomeLocation() {
        return $this->getLocations()->get(1);
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
     * @param mixed $primaryDivision
     */
    public function setPrimaryDivision($primaryDivision)
    {
        $this->primaryDivision = $primaryDivision;
    }

    /**
     * @return mixed
     */
    public function getPrimaryDivision()
    {
        return $this->primaryDivision;
    }



    //chief services
    /**
     * @param mixed $chiefDivisions
     */
    public function setChiefDivisions($chiefDivisions)
    {
        $this->chiefDivisions = $chiefDivisions;

        //add service chiefs to services
        foreach( $chiefDivisions as $division ) {
            $this->addDivision($division);
        }

    }

    /**
     * @return mixed
     */
    public function getChiefDivisions()
    {
        return $this->chiefDivisions;
    }

    public function addChiefDivisions(\Oleg\UserdirectoryBundle\Entity\Division $chiefDivisions)
    {
        if( !$this->chiefDivisions->contains($chiefDivisions) ) {
            $this->chiefDivisions[] = $chiefDivisions;
        }

        //add service chiefs to services
        $this->addDivision($chiefDivisions);

        return $this;
    }

    public function removeChiefDivisions(\Oleg\UserdirectoryBundle\Entity\Division $chiefDivisions)
    {
        $this->chiefDivisions->removeElement($chiefDivisions);
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


    public function addInstitution(\Oleg\UserdirectoryBundle\Entity\Institution $institution)
    {
        if( !$this->institution->contains($institution) ) {
            $this->institution->add($institution);
        }
        return $this;
    }

    public function removeInstitution(\Oleg\UserdirectoryBundle\Entity\Institution $institution)
    {
        $this->institution->removeElement($institution);
    }

    public function setInstitution( $institutions )
    {
        //echo "set institutionsCount=".count($institutions)."<br>";
        $this->institution->clear();
        foreach( $institutions as $institution ) {
            $this->addInstitution($institution);
        }
    }


    /**
     * @return mixed
     */
    public function getService()
    {
        return $this->service;
    }


    public function addService(\Oleg\UserdirectoryBundle\Entity\Service $service)
    {
        if( !$this->service->contains($service) ) {
            $this->service->add($service);
        }
        return $this;
    }

    public function removeService(\Oleg\UserdirectoryBundle\Entity\Service $service)
    {
        $this->service->removeElement($service);
    }


    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Add locations
     *
     * @param \Oleg\UserdirectoryBundle\Entity\Location $locations
     * @return User
     */
    public function addLocation(\Oleg\UserdirectoryBundle\Entity\Location $location)
    {
        //$this->locations[] = $location;
        if( !$this->locations->contains($location) ) {
            $this->locations->add($location);
            $location->setUser($this);
        }
    
        return $this;
    }

    /**
     * Remove locations
     *
     * @param \Oleg\UserdirectoryBundle\Entity\Location $locations
     */
    public function removeLocation(\Oleg\UserdirectoryBundle\Entity\Location $locations)
    {
        $this->locations->removeElement($locations);
    }

    /**
     * Get locations
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getLocations()
    {
        return $this->locations;
    }

    /**
     * Add admnistrativeTitles
     *
     * @param \Oleg\UserdirectoryBundle\Entity\AdmnistrativeTitle $admnistrativeTitle
     * @return User
     */
    public function addAdmnistrativeTitle(\Oleg\UserdirectoryBundle\Entity\AdmnistrativeTitle $admnistrativeTitle)
    {
        if( !$this->admnistrativeTitles->contains($admnistrativeTitle) ) {
            $this->admnistrativeTitles->add($admnistrativeTitle);
            $admnistrativeTitle->setUser($this);
        }
    
        return $this;
    }

    /**
     * Remove admnistrativeTitles
     *
     * @param \Oleg\UserdirectoryBundle\Entity\AdmnistrativeTitle $admnistrativeTitles
     */
    public function removeAdmnistrativeTitle(\Oleg\UserdirectoryBundle\Entity\AdmnistrativeTitle $admnistrativeTitles)
    {
        $this->admnistrativeTitles->removeElement($admnistrativeTitles);
    }

    /**
     * Get admnistrativeTitles
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getAdmnistrativeTitles()
    {
        return $this->admnistrativeTitles;
    }

    /**
     * Add appointmentTitles
     *
     * @param \Oleg\UserdirectoryBundle\Entity\AppointmentTitle $appointmentTitles
     * @return User
     */
    public function addAppointmentTitle(\Oleg\UserdirectoryBundle\Entity\AppointmentTitle $appointmentTitles)
    {
        $this->appointmentTitles[] = $appointmentTitles;
    
        return $this;
    }

    /**
     * Remove appointmentTitles
     *
     * @param \Oleg\UserdirectoryBundle\Entity\AppointmentTitle $appointmentTitles
     */
    public function removeAppointmentTitle(\Oleg\UserdirectoryBundle\Entity\AppointmentTitle $appointmentTitles)
    {
        $this->appointmentTitles->removeElement($appointmentTitles);
    }

    /**
     * Get appointmentTitles
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getAppointmentTitles()
    {
        return $this->appointmentTitles;
    }

    /**
     * Add department
     *
     * @param \Oleg\UserdirectoryBundle\Entity\Department $department
     * @return User
     */
    public function addDepartment(\Oleg\UserdirectoryBundle\Entity\Department $department)
    {
        $this->department[] = $department;
    
        return $this;
    }

    /**
     * Remove department
     *
     * @param \Oleg\UserdirectoryBundle\Entity\Department $department
     */
    public function removeDepartment(\Oleg\UserdirectoryBundle\Entity\Department $department)
    {
        $this->department->removeElement($department);
    }

    /**
     * Get department
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getDepartment()
    {
        return $this->department;
    }

}