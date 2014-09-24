<?php

namespace Oleg\UserdirectoryBundle\Entity;

use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping\AttributeOverrides;
use Doctrine\ORM\Mapping\AttributeOverride;

use FOS\UserBundle\Model\User as BaseUser;

//Use FOSUser bundle: https://github.com/FriendsOfSymfony/FOSUserBundle/blob/master/Resources/doc/index.md
//User is a reserved keyword in SQL so you cannot use it as table name

//TODO: unique username + usertype

/**
 * @ORM\Entity
 * @ORM\Table(name="user_fosuser",
 *  indexes={
 *      @ORM\Index( name="username_idx", columns={"username"} ),
 *      @ORM\Index( name="displayName_idx", columns={"displayName"} )
 *  }
 * )
 * @ORM\AttributeOverrides({
 *      @ORM\AttributeOverride( name="email", column=@ORM\Column(type="string", name="email", unique=false, nullable=true) ), @ORM\AttributeOverride( name="emailCanonical", column=@ORM\Column(type="string", name="email_canonical", unique=false, nullable=true) ),
 *      @ORM\AttributeOverride( name="username", column=@ORM\Column(type="string", name="username", unique=false) ), @ORM\AttributeOverride( name="usernameCanonical", column=@ORM\Column(type="string", name="username_canonical", unique=false) )
 * })
 * )
 *
 * @UniqueEntity({"username_canonical", "email_canonical"})
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
     * @ORM\OneToOne(targetEntity="UserPreferences", inversedBy="user", cascade={"persist"})
     */
    private $preferences;

    /**
     * @ORM\OneToOne(targetEntity="Credentials", inversedBy="user", cascade={"persist"})
     */
    private $credentials;

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

    /**
     * @ORM\OneToMany(targetEntity="EmploymentStatus", mappedBy="user", cascade={"persist"})
     */
    private $employmentStatus;


    function __construct()
    {
        $this->locations = new ArrayCollection();
        $this->administrativeTitles = new ArrayCollection();
        $this->appointmentTitles = new ArrayCollection();
        $this->employmentStatus = new ArrayCollection();

        //create preferences
        $userPref = new UserPreferences();
        $userPref->setTooltip(true);
        $userPref->setTimezone('America/New_York');
        $userPref->setUser($this);
        $this->setPreferences($userPref);

        //create credentials
        $this->setCredentials(new Credentials($this));

        //two default locations: "main office" and "home"
        $mainLocation = new Location($this);
        $homeLocation = new Location($this);
        $mainLocation->setName('Main Office');
        $homeLocation->setName('Home');
        $this->locations->set(0,$mainLocation);  //main has index 0
        $mainLocation->setUser($this);
        $mainLocation->setRemovable(false);
        $this->locations->set(1,$homeLocation);  //home hsa index 1
        $homeLocation->setUser($this);
        $homeLocation->setRemovable(false);

        parent::__construct();
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
            $administrativeTitle = new AdministrativeTitle($this);
            $administrativeTitle->setName($title);
            $this->addAdministrativeTitle($administrativeTitle);
        } else {
            $this->getAdministrativeTitles()->first()->setName($title);
        }

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

    /**
     * @param mixed $credentials
     */
    public function setCredentials($credentials)
    {
        $this->credentials = $credentials;
    }

    /**
     * @return mixed
     */
    public function getCredentials()
    {
        return $this->credentials;
    }

    /**
     * Add employmentStatus
     *
     * @param \Oleg\UserdirectoryBundle\Entity\EmploymentStatus $employmentStatus
     * @return User
     */
    public function addEmploymentStatus(\Oleg\UserdirectoryBundle\Entity\EmploymentStatus $employmentStatus)
    {
        if( !$this->employmentStatus->contains($employmentStatus) ) {
            $this->employmentStatus->add($employmentStatus);
            $employmentStatus->setUser($this);
        }

        return $this;
    }
    public function addEmploymentStatu($employmentStatus) {
        $this->addEmploymentStatus($employmentStatus);
        return $this;
    }

    /**
     * Remove employmentStatus
     *
     * @param \Oleg\UserdirectoryBundle\Entity\EmploymentStatus $employmentStatus
     */
    public function removeEmploymentStatus(\Oleg\UserdirectoryBundle\Entity\EmploymentStatus $employmentStatus)
    {
        $this->employmentStatus->removeElement($employmentStatus);
    }
    public function removeEmploymentStatu($employmentStatus)
    {
        $this->removeEmploymentStatus($employmentStatus);
    }

    /**
     * Get employmentStatus
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getEmploymentStatus()
    {
        return $this->employmentStatus;
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

    public function getSiteRoles($sitename) {

        $roles = array();

        if( $sitename == 'employees' ) {
            $sitename = 'userdirectory';
        }

        foreach( $this->getRoles() as $role ) {
            if( stristr($role, $sitename) ) {
                $roles[] = $role;
            }
        }

        return $roles;
    }

}