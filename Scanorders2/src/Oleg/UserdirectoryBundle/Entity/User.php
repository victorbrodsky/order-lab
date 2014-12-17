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
//Generate unique username (post): primaryPublicUserId + "_" + keytype + "_" _ id

/**
 * @ORM\Entity
 * @UniqueEntity(
 *     fields={"keytype", "primaryPublicUserId"},
 *     errorPath="primaryPublicUserId",
 *     message="Can not create a new user: the combination of the Primary Public User ID Type and Primary Public User ID is already in use."
 * )
 * @ORM\HasLifecycleCallbacks
 * @ORM\Table(name="user_fosuser",
 *  indexes={
 *      @ORM\Index( name="keytype_idx", columns={"keytype"} ),
 *      @ORM\Index( name="primaryPublicUserId_idx", columns={"primaryPublicUserId"} ),
 *      @ORM\Index( name="username_idx", columns={"username"} ),
 *      @ORM\Index( name="displayName_idx", columns={"displayName"} ),
 *      @ORM\Index( name="firstName_idx", columns={"firstName"} ),
 *      @ORM\Index( name="lastName_idx", columns={"lastName"} ),
 *      @ORM\Index( name="email_idx", columns={"email"} )
 *  }
 * )
 * @ORM\AttributeOverrides({ @ORM\AttributeOverride( name="email", column=@ORM\Column(type="string", name="email", unique=false, nullable=true) ), @ORM\AttributeOverride( name="emailCanonical", column=@ORM\Column(type="string", name="email_canonical", unique=false, nullable=true) ) })
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
     * Primary Public User ID Type
     *
     * @ORM\ManyToOne(targetEntity="UsernameType", inversedBy="users")
     * @ORM\JoinColumn(name="keytype", referencedColumnName="id", nullable=true)
     */
    protected $keytype;

    /**
     * @ORM\Column(name="primaryPublicUserId", type="string")
     */
    private $primaryPublicUserId;

    /**
     * @ORM\Column(name="suffix", type="string", nullable=true)
     */
    private $suffix;

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
     * @ORM\Column(name="initials", type="string", nullable=true)
     */
    private $initials;

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
     * @ORM\OrderBy({"orderinlist" = "ASC", "priority" = "ASC", "endDate" = "ASC"})
     */
    private $administrativeTitles;

    /**
     * @ORM\OneToMany(targetEntity="AppointmentTitle", mappedBy="user", cascade={"persist"})
     * @ORM\OrderBy({"orderinlist" = "ASC", "priority" = "ASC", "endDate" = "ASC"})
     */
    private $appointmentTitles;

    /**
     * @ORM\OneToMany(targetEntity="EmploymentStatus", mappedBy="user", cascade={"persist"})
     * @ORM\OrderBy({"terminationDate" = "ASC"})
     */
    private $employmentStatus;

    /**
     * @ORM\ManyToMany(targetEntity="ResearchLab", mappedBy="user", cascade={"persist"})
     **/
    private $researchLabs;

    /**
     * @ORM\OneToMany(targetEntity="PrivateComment", mappedBy="user", cascade={"persist"})
     */
    private $privateComments;

    /**
     * @ORM\OneToMany(targetEntity="PublicComment", mappedBy="user", cascade={"persist"})
     */
    private $publicComments;

    /**
     * @ORM\OneToMany(targetEntity="ConfidentialComment", mappedBy="user", cascade={"persist"})
     */
    private $confidentialComments;

    /**
     * @ORM\OneToMany(targetEntity="AdminComment", mappedBy="user", cascade={"persist"})
     */
    private $adminComments;




    function __construct()
    {
        $this->locations = new ArrayCollection();
        $this->administrativeTitles = new ArrayCollection();
        $this->appointmentTitles = new ArrayCollection();
        $this->employmentStatus = new ArrayCollection();
        $this->researchLabs = new ArrayCollection();

        $this->privateComments = new ArrayCollection();
        $this->publicComments = new ArrayCollection();
        $this->confidentialComments = new ArrayCollection();
        $this->adminComments = new ArrayCollection();

        //create preferences
        $userPref = new UserPreferences();
        //$userPref->setTooltip(true);
        $userPref->setTimezone('America/New_York');
        $userPref->setUser($this);
        $this->setPreferences($userPref);

        //create credentials
        $this->setCredentials(new Credentials($this));

        //set unlocked, enabled
        $this->setLocked(false);
        $this->setEnabled(true);

        parent::__construct();
    }


    public function setIdNull() {
        $this->id = null;
    }

    /**
     * @param mixed $keytype
     */
    public function setKeytype($keytype)
    {
        $this->keytype = $keytype;
    }

    /**
     * @return mixed
     */
    public function getKeytype()
    {
        return $this->keytype;
    }

    /**
     * @param mixed $primaryPublicUserId
     */
    public function setPrimaryPublicUserId($primaryPublicUserId)
    {
        $this->primaryPublicUserId = $primaryPublicUserId;
    }

    /**
     * @return mixed
     */
    public function getPrimaryPublicUserId()
    {
        return $this->primaryPublicUserId;
    }

    /**
     * @param mixed $suffix
     */
    public function setSuffix($suffix)
    {
        $this->suffix = $suffix;
    }

    /**
     * @return mixed
     */
    public function getSuffix()
    {
        return $this->suffix;
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
     * @param mixed $initials
     */
    public function setInitials($initials)
    {
        $this->initials = $initials;
    }

    /**
     * @return mixed
     */
    public function getInitials()
    {
        return $this->initials;
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

    //
    public function getMainLocation() {

        $loc = $this->getLocations()->get(0);

        if( $loc->getLocationType() && $loc->getLocationType()->getName() == "Employee Office" ) {
            return $loc;
        }

        foreach( $this->getLocations() as $loc ) {
            if( $loc->getLocationType() && $loc->getLocationType()->getName() == "Employee Office" ) {
                return $loc;
            }
            if( $loc->getName() == "Main Office" ) {
                return $loc;
            }
        }

        return null;
    }

    public function getHomeLocation() {

        $loc = $this->getLocations()->get(1);

        if( $loc->getLocationType()->getName() == "Employee Office" ) {
            return $loc;
        }

        foreach( $this->getLocations() as $loc ) {
            if( $loc->getLocationType()->getName() == "Employee Home" ) {
                return $loc;
            }
            if( $loc->getName() == "Home" ) {
                return $loc;
            }
        }

        return null;
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

            $administrativeTitle->setHeads();
        }
    
        return $this;
    }

    /**
     * Remove administrativeTitles
     *
     * @param \Oleg\UserdirectoryBundle\Entity\AdministrativeTitle $administrativeTitles
     */
    public function removeAdministrativeTitle(\Oleg\UserdirectoryBundle\Entity\AdministrativeTitle $administrativeTitle)
    {
        $administrativeTitle->unsetHeads();
        $this->administrativeTitles->removeElement($administrativeTitle);
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

    public function addEmploymentStatus($employmentStatus)
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



    /**
     * @return mixed
     */
    public function getPrivateComments()
    {
        return $this->privateComments;
    }
    public function addPrivateComment( $comment )
    {
        if( !$comment )
            return;

        if( !$this->privateComments->contains($comment) ) {
            $comment->setUser($this);
            $this->privateComments->add($comment);
        }
    }
    public function removePrivateComment($comment)
    {
        $this->privateComments->removeElement($comment);
    }


    /**
     * @return mixed
     */
    public function getPublicComments()
    {
        return $this->publicComments;
    }
    public function addPublicComment( $comment )
    {
        if( !$comment )
            return;

        if( !$this->publicComments->contains($comment) ) {
            $comment->setUser($this);
            $this->publicComments->add($comment);
        }
    }
    public function removePublicComment($comment)
    {
        $this->publicComments->removeElement($comment);
    }


    /**
     * @return mixed
     */
    public function getAdminComments()
    {
        return $this->adminComments;
    }
    public function addAdminComment( $comment )
    {
        if( !$comment )
            return;

        if( !$this->adminComments->contains($comment) ) {
            $comment->setUser($this);
            $this->adminComments->add($comment);
        }
    }
    public function removeAdminComment($comment)
    {
        $this->adminComments->removeElement($comment);
    }


    /**
     * @return mixed
     */
    public function getConfidentialComments()
    {
        return $this->confidentialComments;
    }
    public function addConfidentialComment( $comment )
    {
        if( !$comment )
            return;

        if( !$this->confidentialComments->contains($comment) ) {
            $comment->setUser($this);
            $this->confidentialComments->add($comment);
        }
    }
    public function removeConfidentialComment($comment)
    {
        $this->confidentialComments->removeElement($comment);
    }



    /**
     * Add researchLabs
     *
     * @param \Oleg\UserdirectoryBundle\Entity\ResearchLab $researchLabs
     * @return User
     */
    public function addResearchLab(\Oleg\UserdirectoryBundle\Entity\ResearchLab $researchLab)
    {
        if( !$this->researchLabs->contains($researchLab) ) {
            $this->researchLabs->add($researchLab);
            $researchLab->addUser($this);
        }

        return $this;
    }

    /**
     * Remove researchLab
     *
     * @param \Oleg\UserdirectoryBundle\Entity\ResearchLab $researchLab
     */
    public function removeResearchLab(\Oleg\UserdirectoryBundle\Entity\ResearchLab $researchLab)
    {
        $this->researchLabs->removeElement($researchLab);
    }

    /**
     * Get researchLabs
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getResearchLabs()
    {
        return $this->researchLabs;
    }




    public function __toString() {
        return $this->getUserNameStr();
    }


    //If the person is a head of a Department, list all people who belong in that department.
    public function getDepartments($head=false) {
        $departments = new ArrayCollection();
        foreach( $this->getAdministrativeTitles() as $adminTitles ) {
            if( $adminTitles->getDepartment() && $adminTitles->getDepartment()->getName() != "" ) {
                if( $head == true ) {
                    if( $adminTitles->getDepartment()->getHeads()->contains($this) ) {
                        if( !$departments->contains($adminTitles->getDepartment()) ) {
                            $departments->add($adminTitles->getDepartment());
                        }
                    }
                } else {
                    if( !$departments->contains($adminTitles->getDepartment()) ) {
                        $departments->add($adminTitles->getDepartment());
                    }
                }
            }
        }
        if( $head == false ) {
            foreach( $this->getAppointmentTitles() as $appTitles ) {
                if( $appTitles->getDepartment() && $appTitles->getDepartment()->getName() != "" ) {
                    if( !$departments->contains($appTitles->getDepartment()) ) {
                        $departments->add($appTitles->getDepartment());
                    }
                }
            }
        }
        return $departments;
    }

    //If the person is a head of a Division, list all people who belong in that division.
    //$emptyService=true => divisions with empty service
    public function getDivisions($head=false,$emptyService=false) {
        $divisions = new ArrayCollection();
        foreach( $this->getAdministrativeTitles() as $adminTitles ) {
            if( $adminTitles->getDivision() && $adminTitles->getDivision()->getName() != "" ) {
                //echo "division=".$adminTitles->getDivision()->getName()."<br>";
                if( $emptyService && $adminTitles->getDivision() && count($adminTitles->getDivision()->getServices()) == 0 ) {
                    //echo "set head true <br>";
                    $head = false;
                }
                if( $head == true ) {
                    if( $adminTitles->getDivision()->getHeads()->contains($this) ) {
                        if( !$divisions->contains($adminTitles->getDivision()) ) {
                            $divisions->add($adminTitles->getDivision());
                        }
                    }
                } else {
                    if( !$divisions->contains($adminTitles->getDivision()) ) {
                        $divisions->add($adminTitles->getDivision());
                    }
                }
            }
        }

        foreach( $this->getAppointmentTitles() as $appTitles ) {
            if( $emptyService && $appTitles->getDivision() && count($appTitles->getDivision()->getServices()) == 0 ) {
                $head = false;
            }
            if( $head == true ) {
                if( $appTitles->getDivision() && $appTitles->getDivision()->getName() != "" ) {
                    if( !$divisions->contains($appTitles->getDivision()) ) {
                        $divisions->add($appTitles->getDivision());
                    }
                }
            } else {
                if( !$divisions->contains($appTitles->getDivision()) ) {
                    $divisions->add($appTitles->getDivision());
                }
            }
        }
        return $divisions;
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

    public function getBosses() {
        $bosses = new ArrayCollection();
        foreach( $this->getAdministrativeTitles() as $adminTitles ) {
            foreach( $adminTitles->getBoss() as $boss ) {
                $bosses->add($boss);
            }
        }
        return $bosses;
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


    //do not overwrite username when user id is set (user already exists in DB)
    public function setUsernameCanonical($usernameCanonical)
    {
        if( $this->getId() && $usernameCanonical != $this->getUsernameCanonical() ) {
            //exit('Can not change canonical username when user is in DB: username='.$usernameCanonical.', id='.$this->getId());
            throw new \Exception( 'Can not change canonical username when user is in DB: new usernameCanonical='.$usernameCanonical.', old usernameCanonical'.$this->getUsernameCanonical().', id='.$this->getId() );
        }

        $this->usernameCanonical = $usernameCanonical;

        return $this;
    }


    //Username utilities methods
    public function setUniqueUsername() {
        $this->setUsername($this->createUniqueUsername());
    }

    public function createUniqueUsername() {
        return $this->createUniqueUsernameByKeyKeytype($this->getKeytype(),$this->getPrimaryPublicUserId());
    }

    public function createUniqueUsernameByKeyKeytype($keytype,$key) {
        $username = $key."_@_".$keytype->getAbbreviation();
        $usernamestr = preg_replace('/\s+/', '-', $username);   //replace all whitespaces by '-'
        return $usernamestr;
    }

    public function createCleanUsername($username) {
        $usernameArr = explode("_@_",$username);
        return $usernameArr[0];
    }

    public function getUsernamePrefix($username) {
        $usernameArr = explode("_@_",$username);
        return $usernameArr[1];
    }

    public function usernameIsValid($username) {
        if( strpos($username,"_@_") !== false ) {
            return true;
        }
        return false;
    }

    public function getCleanUsername() {
        return $this->createCleanUsername( $this->getUsername() );
    }

    public function getUserNameStr() {
        if( $this->getDisplayName() ) {
            return $this->getPrimaryUseridKeytypeStr()." - ".$this->getDisplayName();
        } else {
            return $this->getPrimaryUseridKeytypeStr();
        }
    }

    public function getUserNameShortStr() {
        return $this->getPrimaryPublicUserId();
    }

    public function getPrimaryUseridKeytypeStr() {
        if( $this->getKeytype() ) {
            return $this->getPrimaryPublicUserId()." (".$this->getKeytype()->getName().")";
        } else {
            return $this->getPrimaryPublicUserId();
        }
    }


    public function getUsernameShortest() {
        if( $this->getDisplayName() ) {
            return $this->getDisplayName();
        } else {
            return $this->getPrimaryPublicUserId();
        }
    }

    //the user has a Preferred Name, start with preferred name;
    //If the user has no preferred name, but does have a first and last name, concatenate them
    //and start with the first and last name combo; if the user has no first name, use just the last name;
    //if the user has no last name, use the first name; if the user has none of the three names, start with the User ID:
    public function getUsernameOptimal() {

        if( $this->getDisplayName() ) {
            return $this->getDisplayName();
        }

        if( $this->getLastName() && $this->getFirstName() ) {
            return $this->getLastName() . " " . $this->getFirstName();
        }

        if( $this->getLastName() ) {
            return $this->getLastName();
        }

        if( $this->getFirstName() ) {
            return $this->getFirstName();
        }

        if( $this->getPrimaryPublicUserId() ) {
            return $this->getPrimaryPublicUserId();
        }

        return $this->getId();
    }

    /**
     * @ORM\PrePersist
     */
    public function setDisplaynameIfEmpty()
    {
        if( !$this->getDisplayName() || $this->getDisplayName() == "" ) {
            $this->setDisplayname( $this->getUsernameOptimal() );
        }
    }

}