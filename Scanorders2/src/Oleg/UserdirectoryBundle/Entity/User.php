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
 * @ORM\Entity(repositoryClass="Oleg\UserdirectoryBundle\Repository\UserRepository")
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

    /**
     * @ORM\OneToOne(targetEntity="Document")
     * @ORM\JoinColumn(name="avatar_id", referencedColumnName="id")
     **/
    private $avatar;





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
     * @param mixed $avatar
     */
    public function setAvatar($avatar)
    {
        $this->avatar = $avatar;
    }

    /**
     * @return mixed
     */
    public function getAvatar()
    {
        return $this->avatar;
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

    public function addLocation($location)
    {
        if( $location && !$this->locations->contains($location) ) {
            $this->locations->add($location);
            $location->setUser($this);
        }
    
        return $this;
    }
    public function removeLocation($locations)
    {
        $this->locations->removeElement($locations);
    }
    public function getLocations()
    {
        return $this->locations;
    }

    public function addAdministrativeTitle($administrativeTitle)
    {
        if( $administrativeTitle && !$this->administrativeTitles->contains($administrativeTitle) ) {
            $this->administrativeTitles->add($administrativeTitle);
            $administrativeTitle->setUser($this);

            $administrativeTitle->setHeads();
        }
    
        return $this;
    }
    public function removeAdministrativeTitle($administrativeTitle)
    {
        $administrativeTitle->unsetHeads();
        $this->administrativeTitles->removeElement($administrativeTitle);
    }
    public function getAdministrativeTitles()
    {
        return $this->administrativeTitles;
    }

    public function addAppointmentTitle($appointmentTitle)
    {
        if( $appointmentTitle && !$this->appointmentTitles->contains($appointmentTitle) ) {
            $this->appointmentTitles->add($appointmentTitle);
            $appointmentTitle->setUser($this);
        }
    
        return $this;
    }
    public function removeAppointmentTitle($appointmentTitle)
    {
        $this->appointmentTitles->removeElement($appointmentTitle);
    }
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
        if( $employmentStatus ) {
            if( !$this->employmentStatus->contains($employmentStatus) ) {
                $this->employmentStatus->add($employmentStatus);
                $employmentStatus->setUser($this);
            }
        }

        return $this;
    }
    public function addEmploymentStatu($employmentStatus) {
        $this->addEmploymentStatus($employmentStatus);
        return $this;
    }

    public function removeEmploymentStatus($employmentStatus)
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



    public function addResearchLab($researchLab)
    {
        if( $researchLab && !$this->researchLabs->contains($researchLab) ) {
            $this->researchLabs->add($researchLab);
            $researchLab->addUser($this);
        }

        return $this;
    }
    public function removeResearchLab($researchLab)
    {
        $this->researchLabs->removeElement($researchLab);
        $researchLab->removeUser($this);
    }
    public function getResearchLabs()
    {
        return $this->researchLabs;
    }




    public function __toString() {
        return $this->getUserNameStr();
    }


    //If the person is a head of a Department, list all people who belong in that department.
    public function getDepartments($head=false,$type=null) {
        $departments = new ArrayCollection();
        if( $type == null or $type == "AdministrativeTitle" ) {
            foreach( $this->getAdministrativeTitles() as $adminTitles ) {
                if( $adminTitles->getDepartment() && $adminTitles->getDepartment()->getId() && $adminTitles->getDepartment()->getName() != "" ) {
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
        }
        if( $type == null or $type == "AppointmentTitle" ) {
            if( $head == false ) {
                foreach( $this->getAppointmentTitles() as $appTitles ) {
                    if( $appTitles->getDepartment() && $appTitles->getDepartment()->getId() && $appTitles->getDepartment()->getName() != "" ) {
                        if( !$departments->contains($appTitles->getDepartment()) ) {
                            $departments->add($appTitles->getDepartment());
                        }
                    }
                }
            }
        }
        return $departments;
    }

    //If the person is a head of a Division, list all people who belong in that division.
    //$emptyService=true => divisions with empty service
    public function getDivisions($head=false,$emptyService=false,$type=null) {

        $divisions = new ArrayCollection();

        if( $type == null or $type == "AdministrativeTitle" ) {
            foreach( $this->getAdministrativeTitles() as $adminTitles ) {
                if( $adminTitles->getDivision() && $adminTitles->getDivision()->getId() && $adminTitles->getDivision()->getName() != "" ) {
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
        }

        if( $type == null or $type == "AppointmentTitle" ) {
            foreach( $this->getAppointmentTitles() as $appTitles ) {
                if( $appTitles->getDivision() && $appTitles->getDivision()->getId() && $appTitles->getDivision()->getName() != "" ) {
                    if( $emptyService && $appTitles->getDivision() && count($appTitles->getDivision()->getServices()) == 0 ) {
                        $head = false;
                    }
                    if( $head == true ) {
                        if( !$divisions->contains($appTitles->getDivision()) ) {
                            $divisions->add($appTitles->getDivision());
                        }
                    } else {
                        if( !$divisions->contains($appTitles->getDivision()) ) {
                            $divisions->add($appTitles->getDivision());
                        }
                    }
                }
            }
        }

        return $divisions;
    }

    //get all services from administrative and appointment titles.
    public function getServices($type=null) {
        $services = new ArrayCollection();
        if( $type == null or $type == "AdministrativeTitle" ) {
            foreach( $this->getAdministrativeTitles() as $adminTitles ) {
                if( $adminTitles->getService() && $adminTitles->getService()->getId() && $adminTitles->getService()->getName() != "" )
                    $services->add($adminTitles->getService());
            }
        }
        if( $type == null or $type == "AppointmentTitle" ) {
            foreach( $this->getAppointmentTitles() as $appTitles ) {
                if( $appTitles->getService() && $appTitles->getService()->getId() && $appTitles->getService()->getName() != "" )
                    $services->add($appTitles->getService());
            }
        }
        return $services;
    }

    //get all institutions from administrative and appointment titles.
    public function getInstitutions($type=null) {
        $institutions = new ArrayCollection();
        if( $type == null or $type == "AdministrativeTitle" ) {
            foreach( $this->getAdministrativeTitles() as $adminTitles ) {
                if( $adminTitles->getInstitution() && $adminTitles->getInstitution()->getId() && $adminTitles->getInstitution()->getName() != "" )
                    $institutions->add($adminTitles->getInstitution());
            }
        }
        if( $type == null or $type == "AppointmentTitle" ) {
            foreach( $this->getAppointmentTitles() as $appTitles ) {
                if( $appTitles->getInstitution() && $appTitles->getInstitution()->getId() && $appTitles->getInstitution()->getName() != "" )
                    $institutions->add($appTitles->getInstitution());
            }
        }
        //echo "inst count=".count($institutions)."<br>";
        return $institutions;
    }

    //TODO: check performance of foreach. It might be replaced by direct DB query
    public function getBosses() {
        $res = array();
        //$bosses = new ArrayCollection();
        foreach( $this->getAdministrativeTitles() as $adminTitles ) {
            $bosses = new ArrayCollection();
            foreach( $adminTitles->getBoss() as $boss ) {
                $bosses->add($boss);
                //$res[$adminTitles->getId()][] = $boss;
            }
            $res[$adminTitles->getId()]['bosses'] = $bosses;
            $res[$adminTitles->getId()]['titleobject'] = $adminTitles;
        }
        //return $bosses;
        return $res;
    }

    //returns: [Medical Director of Informatics] Victor Brodsky
    public function getTitleAndNameByTitle( $admintitle ) {
        return "[" . $admintitle->getName() . "] " . $this->getUsernameOptimal();
    }

    //Testing
    public function getMemoryUsage() {
        //return round(memory_get_usage() / 1024);
        return memory_get_usage();
    }

    //TODO: check performance of foreach. It might be replaced by direct DB query
    public function getAssistants() {
        $assistants = new ArrayCollection();
        $ids = array();
        foreach( $this->getLocations() as $location ) {
            foreach( $location->getAssistant() as $assistant ) {
                $assistants->add($assistant);
                $ids[] = $assistant->getId();
            }
        }

        $res = array();
        $res['entities'] = $assistants;
        $res['ids'] = $ids;
        //print_r($ids);

        return $res;
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

    //Preferred: (646) 555-5555
    //Main Office Line: (212) 444-4444
    //Main Office Mobile: (123) 333-3333
    public function getAllPhones() {
        $phonesArr = array();
        //get all locations phones
        if( $this->getPreferredPhone() ) {
            $phone = array();
            $phone['prefix'] = "Preferred";
            $phone['phone'] = $this->getPreferredPhone();
            $phonesArr[] = $phone;
        }

        foreach( $this->getLocations() as $location ) {
            if( $location->getLocationType() && $location->getLocationType()->getName() != "Employee Home" ) {
                if( $location->getPhone() ) {
                    $phone = array();
                    $phone['prefix'] = $location->getName()." Line";
                    $phone['phone'] = $location->getPhone();
                    $phonesArr[] = $phone;
                }
                if( $location->getMobile() ) {
                    $phone = array();
                    $phone['prefix'] = $location->getName()." Mobile";
                    $phone['phone'] = $location->getMobile();
                    $phonesArr[] = $phone;
                }
                if( $location->getPager() ) {
                    $phone = array();
                    $phone['prefix'] = $location->getName()." Pager";
                    $phone['phone'] = $location->getPager();
                    $phonesArr[] = $phone;
                }
//                if( $location->getIc() )
//                    $phonesArr[] = $location->getName()." Intercom: ".$location->getIc();
            }
        }

        return $phonesArr;
    }

    public function getAllEmail() {
        $emailArr = array();
        //get all locations phones
        if( $this->getEmail() ) {
            $email = array();
            $email['prefix'] = "Preferred";
            $email['email'] = $this->getEmail();
            $emailArr[] = $email;
        }

        foreach( $this->getLocations() as $location ) {
            if( $location->getLocationType() && $location->getLocationType()->getName() != "Employee Home" ) {
                if( $location->getEmail() ) {
                    $email = array();
                    $email['prefix'] = $location->getName();
                    $email['email'] = $this->getEmail();
                    $emailArr[] = $email;
                }
            }
        }

        return $emailArr;
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