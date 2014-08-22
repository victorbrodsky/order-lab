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
     * @ORM\Column(name="preferredPhone", type="string", nullable=true)
     */
    private $preferredPhone;

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

//    /**
//     * @ORM\OneToMany(targetEntity="PerSiteSettings", mappedBy="author", cascade={"persist"})
//     */
//    private $perSiteSettings;

    /**
     * @ORM\OneToMany(targetEntity="Location", mappedBy="user", cascade={"persist"})
     */
    private $locations;

    /**
     * @ORM\OneToMany(targetEntity="AdministrativeTitle", mappedBy="user", cascade={"persist"})
     */
    private $administrativeTitles;

    /**
     * @ORM\OneToMany(targetEntity="AppointmentTitle", mappedBy="user", cascade={"persist"})
     */
    private $appointmentTitles;


//    /**
//     * Keep reference to institution for performance gain?
//     *
//     * @ORM\ManyToMany(targetEntity="Institution", inversedBy="users")
//     * @ORM\JoinTable(name="fos_user_institution",
//     *      joinColumns={@ORM\JoinColumn(name="user_id", referencedColumnName="id")},
//     *      inverseJoinColumns={@ORM\JoinColumn(name="institution_id", referencedColumnName="id")}
//     * )
//     */
//    private $institution;

//    /**
//     * @ORM\ManyToMany(targetEntity="Department", inversedBy="users")
//     * @ORM\JoinTable(name="fos_user_department",
//     *      joinColumns={@ORM\JoinColumn(name="user_id", referencedColumnName="id")},
//     *      inverseJoinColumns={@ORM\JoinColumn(name="department_id", referencedColumnName="id")}
//     * )
//     */
//    private $department;
//
//    /**
//     * @ORM\ManyToMany(targetEntity="Division", inversedBy="users")
//     * @ORM\JoinTable(name="fos_user_division",
//     *      joinColumns={@ORM\JoinColumn(name="user_id", referencedColumnName="id")},
//     *      inverseJoinColumns={@ORM\JoinColumn(name="division_id", referencedColumnName="id")}
//     * )
//     */
//    private $division;
//
//    /**
//     * @ORM\ManyToMany(targetEntity="Service", inversedBy="users")
//     * @ORM\JoinTable(name="fos_user_service",
//     *      joinColumns={@ORM\JoinColumn(name="user_id", referencedColumnName="id")},
//     *      inverseJoinColumns={@ORM\JoinColumn(name="service_id", referencedColumnName="id")}
//     * )
//     */
//    private $service;

//    /**
//     * @ORM\Column(type="string", nullable=true)
//     */
//    private $primaryDivision;   //$primaryPathologyService;


    function __construct()
    {
        //$this->perSiteSettings = new ArrayCollection();
        $this->locations = new ArrayCollection();
        $this->administrativeTitles = new ArrayCollection();
        $this->appointmentTitles = new ArrayCollection();

//        $this->institution = new ArrayCollection();
//        $this->department = new ArrayCollection();
//        $this->division = new ArrayCollection();
//        $this->service = new ArrayCollection();

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
        //$AdministrativeTitle = new AdministrativeTitle();
        //$this->addAdministrativeTitle($AdministrativeTitle);

        parent::__construct();
    }

//    /**
//     * @param mixed $division
//     */
//    public function setDivision($division)
//    {
//        if( $division->first() ) {
//            $this->primaryDivision = $division->first()->getId();
//        } else {
//            $this->primaryDivision = NULL;
//        }
//        $this->division = $division;
//    }
//
//
//    /**
//     * @return mixed
//     */
//    public function getDivision()
//    {
//        //return $this->division;
//
//        $resArr = new ArrayCollection();
//        foreach( $this->division as $service ) {
//            //echo "service=".$service."<br>";
//            if( $service->getId()."" == $this->getPrimaryDivision()."" ) {  //this service is a primary path service => put as the first element
//                //$resArr->removeElement($service);
//                //$resArr->first();
//                //$firstEl = $resArr->get(0);
//                $firstEl = $resArr->first();
//                if( count($this->division) > 1 && $firstEl ) {
//                    //echo "firstEl=".$firstEl."<br>";
//                    $resArr->set(0,$service); //set( mixed $key, mixed $value ) Adds/sets an element in the collection at the index / with the specified key.
//                    $resArr->add($firstEl);
//                } else {
//                    $resArr->add($service);
//                }
//            } else {    //this service is not a primary path service
//                $resArr->add($service);
//            }
//        }
//
//        return $resArr;
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
        if( count($this->getAdministrativeTitles()) == 0 ) {
            $administrativeTitle = new AdministrativeTitle();
            $administrativeTitle->setName($title);
            $this->addAdministrativeTitle($administrativeTitle);
        } else {
            $this->getAdministrativeTitles()->first()->setName($title);
        }

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

    /**
     * @param mixed $preferredPhone
     */
    public function setPreferredPhone($preferredPhone)
    {
        $this->preferredPhone = $preferredPhone;
    }

    /**
     * @return mixed
     */
    public function getPreferredPhone()
    {
        return $this->preferredPhone;
    }


//    public function getDivision()
//    {
//        return $this->division;
//    }
//
//    public function addDivision(\Oleg\UserdirectoryBundle\Entity\Division $division)
//    {
//        if( !$this->division->contains($division) ) {
//            $this->division->add($division);
//        }
//
//        return $this;
//    }
//
//    public function removeDivision(\Oleg\UserdirectoryBundle\Entity\Division $division)
//    {
//        $this->division->removeElement($division);
//    }

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

//    /**
//     * @return mixed
//     */
//    public function getInstitution()
//    {
//        return $this->institution;
//    }
//
//
//    public function addInstitution(\Oleg\UserdirectoryBundle\Entity\Institution $institution)
//    {
//        if( !$this->institution->contains($institution) ) {
//            $this->institution->add($institution);
//        }
//        return $this;
//    }
//
//    public function removeInstitution(\Oleg\UserdirectoryBundle\Entity\Institution $institution)
//    {
//        $this->institution->removeElement($institution);
//    }
//
//    public function setInstitution( $institutions )
//    {
//        //echo "set institutionsCount=".count($institutions)."<br>";
//        $this->institution->clear();
//        foreach( $institutions as $institution ) {
//            $this->addInstitution($institution);
//        }
//    }


//    /**
//     * @return mixed
//     */
//    public function getDivision()
//    {
//        return $this->division;
//    }
//
//
//    public function addDivision(\Oleg\UserdirectoryBundle\Entity\Division $division)
//    {
//        if( !$this->division->contains($division) ) {
//            $this->division->add($division);
//        }
//        return $this;
//    }
//
//    public function removeDivision(\Oleg\UserdirectoryBundle\Entity\Division $division)
//    {
//        $this->division->removeElement($division);
//    }
//
//    public function setDivision( $division )
//    {
//        //echo "set institutionsCount=".count($institutions)."<br>";
//        $this->division->clear();
//        foreach( $division as $singledivision ) {
//            $this->addDivision($singledivision);
//        }
//    }


//    /**
//     * @return mixed
//     */
//    public function getService()
//    {
//        return $this->service;
//    }
//
//
//    public function addService(\Oleg\UserdirectoryBundle\Entity\Service $service)
//    {
//        if( !$this->service->contains($service) ) {
//            $this->service->add($service);
//        }
//        return $this;
//    }
//
//    public function removeService(\Oleg\UserdirectoryBundle\Entity\Service $service)
//    {
//        $this->service->removeElement($service);
//    }


    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }


//    /**
//     * Add perSiteSettings
//     *
//     * @param \Oleg\UserdirectoryBundle\Entity\perSiteSettings $perSiteSettings
//     * @return User
//     */
//    public function addPerSiteSettings(\Oleg\UserdirectoryBundle\Entity\PerSiteSettings $perSiteSettings)
//    {
//        //$this->locations[] = $location;
//        if( !$this->perSiteSettings->contains($perSiteSettings) ) {
//            $this->perSiteSettings->add($perSiteSettings);
//            $perSiteSettings->setAuthor($this);
//        }
//
//        return $this;
//    }
//    public function addPerSiteSetting(\Oleg\UserdirectoryBundle\Entity\PerSiteSettings $perSiteSettings) {
//        return $this->addPerSiteSettings($perSiteSettings);
//    }
//
//    /**
//     * Remove locations
//     *
//     * @param \Oleg\UserdirectoryBundle\Entity\PerSiteSettings $locations
//     */
//    public function removePerSiteSettings(\Oleg\UserdirectoryBundle\Entity\PerSiteSettings $perSiteSettings)
//    {
//        $this->perSiteSettings->removeElement($perSiteSettings);
//    }
//    public function removePerSiteSetting(\Oleg\UserdirectoryBundle\Entity\PerSiteSettings $perSiteSettings)
//    {
//        $this->removePerSiteSettings($perSiteSettings);
//    }
//
//    /**
//     * Get locations
//     *
//     * @return \Doctrine\Common\Collections\Collection
//     */
//    public function getPerSiteSettings()
//    {
//        return $this->perSiteSettings;
//    }


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
     * Add administrativeTitles
     *
     * @param \Oleg\UserdirectoryBundle\Entity\AdministrativeTitle $administrativeTitle
     * @return User
     */
    public function addAdministrativeTitle(\Oleg\UserdirectoryBundle\Entity\AdministrativeTitle $administrativeTitle)
    {
        if( !$this->administrativeTitles->contains($administrativeTitle) ) {
            $this->administrativeTitles->add($administrativeTitle);
            $administrativeTitle->setUser($this);
        }
    
        return $this;
    }

    /**
     * Remove administrativeTitles
     *
     * @param \Oleg\UserdirectoryBundle\Entity\AdministrativeTitle $administrativeTitles
     */
    public function removeAdministrativeTitle(\Oleg\UserdirectoryBundle\Entity\AdministrativeTitle $administrativeTitles)
    {
        $this->administrativeTitles->removeElement($administrativeTitles);
    }

    /**
     * Get administrativeTitles
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getAdministrativeTitles()
    {
        return $this->administrativeTitles;
    }

    /**
     * Add appointmentTitles
     *
     * @param \Oleg\UserdirectoryBundle\Entity\AppointmentTitle $appointmentTitles
     * @return User
     */
    public function addAppointmentTitle(\Oleg\UserdirectoryBundle\Entity\AppointmentTitle $appointmentTitles)
    {
        if( !$this->appointmentTitles->contains($appointmentTitles) ) {
            $this->appointmentTitles->add($appointmentTitles);
            $appointmentTitles->setUser($this);
        }
    
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


    //get all services from administrative and appointment titles.
    public function getServices() {
        $services = new ArrayCollection();
        foreach( $this->getAdministrativeTitles() as $adminTitles ) {
            if( $adminTitles->getService() && $adminTitles->getService()->getName() != "" )
                $services->add($adminTitles->getService());
        }
        foreach( $this->getAppointmentTitles() as $appTitles ) {
            if( $appTitles->getService() && $appTitles->getService()->getName() != "" )
                $services->add($appTitles->getService());
        }
        return $services;
    }

    //get all institutions from administrative and appointment titles.
    public function getInstitutions() {
        $institutions = new ArrayCollection();
        foreach( $this->getAdministrativeTitles() as $adminTitles ) {
            if( $adminTitles->getInstitution() && $adminTitles->getInstitution()->getName() != "" )
                $institutions->add($adminTitles->getInstitution());
        }
        foreach( $this->getAppointmentTitles() as $appTitles ) {
            if( $appTitles->getInstitution() && $appTitles->getInstitution()->getName() != "" )
                $institutions->add($appTitles->getInstitution());
        }
        //echo "inst count=".count($institutions)."<br>";
        return $institutions;
    }

//    public function getPerSiteSettingsValue($sitename,$getMethod) {
//        foreach( $this->getPerSiteSettings() as $site ) {
//            if( $site->getSiteName() == $sitename ) {
//                if( method_exists($site,$getMethod) ) {
//                    return $site->$getMethod();
//                }
//            }
//        }
//        return null;
//    }


//    /**
//     * Add department
//     *
//     * @param \Oleg\UserdirectoryBundle\Entity\Department $department
//     * @return User
//     */
//    public function addDepartment(\Oleg\UserdirectoryBundle\Entity\Department $department)
//    {
//        $this->department[] = $department;
//
//        return $this;
//    }
//
//    /**
//     * Remove department
//     *
//     * @param \Oleg\UserdirectoryBundle\Entity\Department $department
//     */
//    public function removeDepartment(\Oleg\UserdirectoryBundle\Entity\Department $department)
//    {
//        $this->department->removeElement($department);
//    }
//
//    /**
//     * Get department
//     *
//     * @return \Doctrine\Common\Collections\Collection
//     */
//    public function getDepartment()
//    {
//        return $this->department;
//    }

}