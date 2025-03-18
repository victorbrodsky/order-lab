<?php
/**
 * Copyright (c) 2017 Cornell University
 *
 *  Licensed under the Apache License, Version 2.0 (the "License");
 *  you may not use this file except in compliance with the License.
 *  You may obtain a copy of the License at
 *
 *  http://www.apache.org/licenses/LICENSE-2.0
 *
 *  Unless required by applicable law or agreed to in writing, software
 *  distributed under the License is distributed on an "AS IS" BASIS,
 *  WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 *  See the License for the specific language governing permissions and
 *  limitations under the License.
 */

namespace App\UserdirectoryBundle\Entity;

use App\UserdirectoryBundle\User\Model\UserBase;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping\AttributeOverrides;
use Doctrine\ORM\Mapping\AttributeOverride;
use Doctrine\ORM\Mapping\Column;

use App\UserdirectoryBundle\Repository\UserRepository;

//use FOS\UserBundle\Model\User as BaseUser;
//Use FOSUser bundle: https://github.com/FriendsOfSymfony/FOSUserBundle/blob/master/Resources/doc/index.md
//User is a reserved keyword in SQL so you cannot use it as table name
//Generate unique username (post): primaryPublicUserId + "_" + keytype + "_" _ id
//[Doctrine\DBAL\DBALException]
// An exception occurred while executing 'ALTER TABLE user_fosuser ALTER COLUMN username NVARCHAR(180) NOT NULL':
// SQLSTATE[42000]: [Microsoft][ODBC Driver 11 for SQL Server][SQL Server]The index 'username_idx' is dependent on column 'username'.
//Caused by FriendsOfSymfony/FOSUserBundle forked from KnpLabs/KnpUserBundle commit 6fefb7212dfc629ebc74af67d75a62d274a44c95
//Make username and email length compatible with actual database limitations regarding key and index length
//So use "friendsofsymfony/user-bundle": "v2.0.0-alpha3"
// *      @ORM\Index( name="username_idx", columns={"username"} )

///**
// * @ORM\Entity(repositoryClass="App\UserdirectoryBundle\Repository\UserRepository")
// * @UniqueEntity(
// *     fields={"keytype", "primaryPublicUserId"},
// *     errorPath="primaryPublicUserId",
// *     message="Can not create a new user: the combination of the Primary Public User ID Type and Primary Public User ID is already in use."
// * )
// * @ORM\HasLifecycleCallbacks
// * @ORM\AttributeOverrides({
// *      @ORM\AttributeOverride(name="email",
// *          column=@ORM\Column(
// *              name     = "email",
// *              type     = "string",
// *              unique   = false,
// *              nullable = true
// *          )
// *      ),
// *      @ORM\AttributeOverride(name="emailCanonical",
// *          column=@ORM\Column(
// *              name     = "email_canonical",
// *              type     = "string",
// *              unique   = false,
// *              nullable = true
// *          )
// *      )
// * })
// */

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[UniqueEntity(
    fields: ['keytype', 'primaryPublicUserId'],
    errorPath: 'primaryPublicUserId',
    message: 'Can not create a new user: the combination of the Primary Public User ID Type and Primary Public User ID is already in use.',
)]
#[ORM\HasLifecycleCallbacks]
#[ORM\Table(name: 'user_fosuser')]
#[ORM\Index(name: 'keytype_idx', columns: ['keytype'])]
#[ORM\Index(name: 'primaryPublicUserId_idx', columns: ['primaryPublicUserId'])]
#[AttributeOverrides([
    new AttributeOverride(
        name: "email",
        column: new Column(name: "email", type: "string", unique: false, nullable: true)
    ),
    new AttributeOverride(
        name: "emailCanonical",
        column: new Column(name: "email_canonical", type: "string", unique: false, nullable: true)
    )]
)]
class User extends UserBase {

    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    protected $id;

//    /**
//     * @ORM\Column(name="email", type="string", unique=false, nullable=true)
//     */
//    protected $email;
//
//    /**
//     * @ORM\Column(name="email_canonical", type="string", unique=false, nullable=true)
//     */
//    protected $emailCanonical;

    /**
     * Ldap Object Distinguished Name
     * @var string $dn
     */
    private $dn;

    /**
     * Primary Public User ID Type
     */
    #[ORM\ManyToOne(targetEntity: 'UsernameType', inversedBy: 'users')]
    #[ORM\JoinColumn(name: 'keytype', referencedColumnName: 'id', nullable: true)]
    protected $keytype;

    #[ORM\Column(name: 'primaryPublicUserId', type: 'string')]
    private $primaryPublicUserId;

    /**
     * includes 7+2 fields: suffix, firstName, middleName, lastName, displayName, initials, preferredPhone, email/emailCanonical (used instead of extended user's email)
     *
     * //OneToMany(targetEntity="UserInfo", mappedBy="user", cascade={"persist","remove"},
     *  indexBy="firstName,middleName,lastName,displayName,email"
     * )
     */
    #[ORM\OneToMany(targetEntity: 'UserInfo', mappedBy: 'user', cascade: ['persist', 'remove'])]
    #[ORM\OrderBy(['displayName' => 'ASC'])]
    private $infos;

    /**
     * system, excel, manual, ldap
     */
    #[ORM\Column(name: 'createdby', type: 'string', nullable: true)]
    private $createdby;

    /**
     * otherUserParam: used for creation a new user on the fly and pass additional parameters, for example, transres project's specialty Hema or APCP
     */
    #[ORM\Column(type: 'string', nullable: true)]
    private $otherUserParam;

    #[ORM\OneToOne(targetEntity: 'UserPreferences', inversedBy: 'user', cascade: ['persist', 'remove'])]
    private $preferences;

    #[ORM\OneToOne(targetEntity: 'Credentials', inversedBy: 'user', cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(name: 'credentials_id', referencedColumnName: 'id', onDelete: 'CASCADE', nullable: true)]
    private $credentials;

    #[ORM\OneToMany(targetEntity: 'Training', mappedBy: 'user', cascade: ['persist', 'remove'])]
    #[ORM\OrderBy(['completionDate' => 'DESC', 'orderinlist' => 'ASC'])]
    private $trainings;

    #[ORM\OneToMany(targetEntity: 'App\FellAppBundle\Entity\FellowshipApplication', mappedBy: 'user', cascade: ['persist', 'remove'])]
    #[ORM\OrderBy(['orderinlist' => 'ASC'])]
    private $fellowshipApplications;

    #[ORM\OneToMany(targetEntity: 'App\ResAppBundle\Entity\ResidencyApplication', mappedBy: 'user', cascade: ['persist', 'remove'])]
    #[ORM\OrderBy(['orderinlist' => 'ASC'])]
    private $residencyApplications;

    #[ORM\OneToMany(targetEntity: 'Location', mappedBy: 'user', cascade: ['persist', 'remove'])]
    private $locations;

    #[ORM\OneToMany(targetEntity: 'AdministrativeTitle', mappedBy: 'user', cascade: ['persist', 'remove'])]
    #[ORM\OrderBy(['orderinlist' => 'ASC', 'priority' => 'ASC', 'endDate' => 'ASC'])]
    private $administrativeTitles;

    #[ORM\OneToMany(targetEntity: 'AppointmentTitle', mappedBy: 'user', cascade: ['persist', 'remove'])]
    #[ORM\OrderBy(['orderinlist' => 'ASC', 'priority' => 'ASC', 'endDate' => 'ASC'])]
    private $appointmentTitles;

    #[ORM\OneToMany(targetEntity: 'MedicalTitle', mappedBy: 'user', cascade: ['persist', 'remove'])]
    #[ORM\OrderBy(['orderinlist' => 'ASC', 'priority' => 'ASC', 'endDate' => 'ASC'])]
    private $medicalTitles;

    #[ORM\OneToMany(targetEntity: 'EmploymentStatus', mappedBy: 'user', cascade: ['persist', 'remove'])]
    #[ORM\OrderBy(['terminationDate' => 'ASC'])]
    private $employmentStatus;

    #[ORM\JoinTable(name: 'user_users_researchlabs')]
    #[ORM\ManyToMany(targetEntity: 'ResearchLab', inversedBy: 'user', cascade: ['persist'])]
    private $researchLabs;

    #[ORM\JoinTable(name: 'user_users_grants')]
    #[ORM\ManyToMany(targetEntity: 'Grant', inversedBy: 'user', cascade: ['persist'])]
    private $grants;

    #[ORM\JoinTable(name: 'user_users_publications')]
    #[ORM\ManyToMany(targetEntity: 'Publication', inversedBy: 'users', cascade: ['persist', 'remove'])]
    #[ORM\OrderBy(['updatedate' => 'DESC', 'publicationDate' => 'DESC'])]
    private $publications;

    #[ORM\JoinTable(name: 'user_users_books')]
    #[ORM\ManyToMany(targetEntity: 'Book', inversedBy: 'users', cascade: ['persist', 'remove'])]
    #[ORM\OrderBy(['updatedate' => 'DESC', 'publicationDate' => 'DESC'])]
    private $books;

    #[ORM\OneToMany(targetEntity: 'Lecture', mappedBy: 'user', cascade: ['persist', 'remove'])]
    #[ORM\OrderBy(['lectureDate' => 'DESC'])]
    private $lectures;

    #[ORM\OneToMany(targetEntity: 'PrivateComment', mappedBy: 'user', cascade: ['persist', 'remove'])]
    #[ORM\OrderBy(['commentTypeStr' => 'ASC', 'updatedate' => 'DESC', 'orderinlist' => 'ASC'])]
    private $privateComments;

    #[ORM\OneToMany(targetEntity: 'PublicComment', mappedBy: 'user', cascade: ['persist', 'remove'])]
    #[ORM\OrderBy(['commentTypeStr' => 'ASC', 'updatedate' => 'DESC', 'orderinlist' => 'ASC'])]
    private $publicComments;

    #[ORM\OneToMany(targetEntity: 'ConfidentialComment', mappedBy: 'user', cascade: ['persist', 'remove'])]
    #[ORM\OrderBy(['commentTypeStr' => 'ASC', 'updatedate' => 'DESC', 'orderinlist' => 'ASC'])]
    private $confidentialComments;

    #[ORM\OneToMany(targetEntity: 'AdminComment', mappedBy: 'user', cascade: ['persist', 'remove'])]
    #[ORM\OrderBy(['commentTypeStr' => 'ASC', 'updatedate' => 'DESC', 'orderinlist' => 'ASC'])]
    private $adminComments;


    #[ORM\OneToOne(targetEntity: 'Document')]
    #[ORM\JoinColumn(name: 'avatar_id', referencedColumnName: 'id')]
    private $avatar;

    #[ORM\OneToMany(targetEntity: 'Permission', mappedBy: 'user', cascade: ['persist', 'remove'])]
    private $permissions;

    #[ORM\OneToOne(targetEntity: 'PerSiteSettings', mappedBy: 'user', cascade: ['persist', 'remove'])]
    private $perSiteSettings;

    #[ORM\Column(name: 'testingAccount', type: 'boolean', nullable: true)]
    private $testingAccount;

    #[ORM\ManyToOne(targetEntity: 'User')]
    #[ORM\JoinColumn(referencedColumnName: 'id', nullable: true)]
    private $author;

    /**
     * @var \DateTime
     */
    #[ORM\Column(type: 'datetime', nullable: true)]
    private $createDate;

    #[ORM\Column(type: 'integer', nullable: true)]
    private $failedAttemptCounter;

//    /**
//     *  Send email notifications to
//     */
//    #[ORM\ManyToOne(targetEntity: 'User')]
//    #[ORM\JoinColumn(referencedColumnName: 'id', nullable: true)]
//    private $notificationEmailUser;

    //TODO: add multiple email addresses to notify or replace $notificationEmailUser to support many users
    #[ORM\JoinTable(name: 'user_users_notifyusers')]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id')]
    #[ORM\InverseJoinColumn(name: 'notifyuser_id', referencedColumnName: 'id')]
    #[ORM\ManyToMany(targetEntity: 'User')]
    private $notifyUsers;

    /**
     * @var \DateTime
     */
    #[ORM\Column(name: 'lastActivity', type: 'datetime', nullable: true)]
    private $lastActivity;

    /**
     * Last visited url, updated by keepalive on the web page => /common/setserveractive
     */
    #[ORM\Column(type: 'text', nullable: true)]
    private $lastLoggedUrl;

    #[ORM\Column(name: 'activeAD', type: 'boolean', nullable: true)]
    private $activeAD;

    /**
     * @var \DateTime
     */
    #[ORM\Column(name: 'lastAdCheck', type: 'datetime', nullable: true)]
    private $lastAdCheck;


    function __construct( $addobjects=true )
    {
        parent::__construct();

        $this->infos = new ArrayCollection();
        $this->locations = new ArrayCollection();
        $this->administrativeTitles = new ArrayCollection();
        $this->appointmentTitles = new ArrayCollection();
        $this->medicalTitles = new ArrayCollection();
        $this->employmentStatus = new ArrayCollection();
        $this->researchLabs = new ArrayCollection();
        $this->grants = new ArrayCollection();
        $this->trainings = new ArrayCollection();
        $this->fellowshipApplications = new ArrayCollection();
        $this->residencyApplications = new ArrayCollection();
        $this->publications = new ArrayCollection();
        $this->books = new ArrayCollection();
        $this->lectures = new ArrayCollection();
        $this->permissions = new ArrayCollection();

        $this->privateComments = new ArrayCollection();
        $this->publicComments = new ArrayCollection();
        $this->confidentialComments = new ArrayCollection();
        $this->adminComments = new ArrayCollection();

        $this->notifyUsers = new ArrayCollection();

        //create user info
        $userInfo = new UserInfo();
        $this->addInfo($userInfo);

        //create preferences
        $userPref = new UserPreferences();
        //$userPref->setTooltip(true);
        $userPref->setTimezone('America/New_York');
        $userPref->setUser($this);
        $this->setPreferences($userPref);

        //create credentials
        $this->setCredentials(new Credentials($this,$addobjects));

        //create PerSiteSettings
        $perSiteSettings = new PerSiteSettings();
        $this->setPerSiteSettings($perSiteSettings);

        //set unlocked, enabled
        $this->setLocked(false);
        $this->setEnabled(true);

        $this->setActiveAD(false);

        $this->setCreateDate(new \DateTime());

        //parent::__construct();
    }



    /**
     * @param string $dn
     */
    public function setDn($dn): void
    {
        $this->dn = $dn;
    }

    /**
     * @return string
     */
    public function getDn(): ?string
    {
        return $this->dn;

    }

    public function setIdNull(): void
    {
        $this->id = null;
    }

    /**
     * @return mixed
     */
    public function getAuthor(): mixed
    {
        return $this->author;
    }

    /**
     * @param mixed $author
     */
    public function setAuthor($author): void
    {
        $this->author = $author;
    }

    /**
     * @return \DateTime
     */
    public function getCreateDate(): ?\DateTime
    {
        return $this->createDate;
    }

    /**
     * @param \DateTime $createDate
     */
    public function setCreateDate($createDate): void
    {
        $this->createDate = $createDate;
    }

    /**
     * @param mixed $avatar
     */
    public function setAvatar($avatar): void
    {
        //1) clear old avatar image if exists
        if( $this->getAvatar() ) {
            $this->getAvatar()->clearUseObject();
        }

        //2) set avatar
        $this->avatar = $avatar;
        if( $avatar ) {
            $avatar->createUseObject($this);
        }
    }

    /**
     * @return mixed
     */
    public function getAvatar(): mixed
    {
        return $this->avatar;
    }

    /**
     * @param mixed $keytype
     */
    public function setKeytype($keytype): void
    {
        $this->keytype = $keytype;
    }

    /**
     * @return mixed
     */
    public function getKeytype(): mixed
    {
        return $this->keytype;
    }

    /**
     * @param mixed $primaryPublicUserId
     */
    public function setPrimaryPublicUserId($primaryPublicUserId): void
    {
        if( $primaryPublicUserId ) {
            //$primaryPublicUserId = trim((string)$primaryPublicUserId);
            //$primaryPublicUserId = strtolower($primaryPublicUserId);
            $primaryPublicUserId = $this->canonicalize($primaryPublicUserId);
        }
        $this->primaryPublicUserId = $primaryPublicUserId;
    }

    /**
     * @return mixed
     */
    public function getPrimaryPublicUserId(): ?string
    {
        return $this->primaryPublicUserId;
    }

//    //password can not be NULL
//    public function setPassword($password) {
//        if( $password == NULL ) {
//            $this->password = "";
//        } else {
//            $this->password = $password;
//        }
//    }

    /**
     * @param mixed $title
     */
    public function setTitle($title): void
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
     * @return mixed
     */
    public function getTestingAccount(): bool
    {
        return $this->testingAccount;
    }

    /**
     * @param mixed $testingAccount
     */
    public function setTestingAccount($testingAccount): void
    {
        $this->testingAccount = $testingAccount;
    }

    /**
     * @param mixed $createdby
     */
    public function setCreatedby($createdby = 'ldap'): void
    {
        $this->createdby = $createdby;
    }

    /**
     * @return mixed
     */
    public function getCreatedby(): ?string
    {
        return $this->createdby;
    }

    /**
     * @return mixed
     */
    public function getOtherUserParam(): ?string
    {
        return $this->otherUserParam;
    }

    /**
     * @param mixed $otherUserParam
     */
    public function setOtherUserParam($otherUserParam): void
    {
        $this->otherUserParam = $otherUserParam;
    }

    //
    public function getMainLocation(): mixed
    {

        $loc = $this->getLocations()->get(0);

        if( $loc && $loc->hasLocationTypeName("Employee Office") ) {
            return $loc;
        }

        foreach( $this->getLocations() as $loc ) {
            if( $loc && $loc->hasLocationTypeName("Employee Office") ) {
                return $loc;
            }
            if( $loc && $loc->getName() == "Main Office" ) {
                return $loc;
            }
        }

        return null;
    }

    public function getHomeLocation(): mixed
    {

        $loc = $this->getLocations()->get(1);

        if( $loc && $loc->hasLocationTypeName("Employee Home") ) {
            return $loc;
        }

        foreach( $this->getLocations() as $loc ) {
            if( $loc && $loc->hasLocationTypeName("Employee Home") ) {
                return $loc;
            }
            if( $loc && $loc->getName() == "Home" ) {
                return $loc;
            }
        }

        return null;
    }


    public function hasRole($role): bool
    {
        return in_array(strtoupper($role), $this->roles, true);
    }

    public function hasPartialRole($partialRoleStr)
    {
        foreach( $this->getRoles() as $role ) {
            if( strpos((string)$role, $partialRoleStr) !== false ) {
                return true;
            }
        }
        return false;
    }

    /**
     * @param mixed $preferences
     */
    public function setPreferences($preferences): void
    {
        $this->preferences = $preferences;
    }

    /**
     * @return mixed
     */
    public function getPreferences(): mixed
    {
        return $this->preferences;
    }

    /**
     * Get id
     *
     * @return integer 
     */
    public function getId(): ?int
    {
        return $this->id;
    }


    public function addTraining($training): self
    {
        if( $training && !$this->trainings->contains($training) ) {
            $this->trainings->add($training);
            $training->setUser($this);
        }

        return $this;
    }
    public function removeTraining($training): void
    {
        $this->trainings->removeElement($training);
    }
    public function getTrainings(): mixed
    {
        return $this->trainings;
    }

    public function addFellowshipApplication($application): self
    {
        if( $application && !$this->fellowshipApplications->contains($application) ) {
            $this->fellowshipApplications->add($application);
            $application->setUser($this);
        }

        return $this;
    }
    public function removeFellowshipApplication($application): void
    {
        $this->fellowshipApplications->removeElement($application);
    }
    public function getFellowshipApplications(): mixed
    {
        return $this->fellowshipApplications;
    }

    public function addResidencyApplication($application): self
    {
        if( $application && !$this->residencyApplications->contains($application) ) {
            $this->residencyApplications->add($application);
            $application->setUser($this);
        }

        return $this;
    }
    public function removeResidencyApplication($application): void
    {
        $this->residencyApplications->removeElement($application);
    }
    public function getResidencyApplications(): mixed
    {
        return $this->residencyApplications;
    }


    public function addLocation($location): self
    {
        if( $location && !$this->locations->contains($location) ) {
            $this->locations->add($location);
            $location->setUser($this);
        }
    
        return $this;
    }
    public function removeLocation($locations): void
    {
        $this->locations->removeElement($locations);
    }
    public function getLocations(): mixed
    {
        return $this->locations;
    }

    public function addAdministrativeTitle($administrativeTitle): self
    {
        if( $administrativeTitle && !$this->administrativeTitles->contains($administrativeTitle) ) {
            $this->administrativeTitles->add($administrativeTitle);
            $administrativeTitle->setUser($this);

            //$administrativeTitle->setHeads();
        }
    
        return $this;
    }
    public function removeAdministrativeTitle($administrativeTitle): void
    {
        //$administrativeTitle->unsetHeads();
        $this->administrativeTitles->removeElement($administrativeTitle);
    }
    public function getAdministrativeTitles()
    {
        return $this->administrativeTitles;
    }

    public function addAppointmentTitle($appointmentTitle): self
    {
        if( $appointmentTitle && !$this->appointmentTitles->contains($appointmentTitle) ) {
            $this->appointmentTitles->add($appointmentTitle);
            $appointmentTitle->setUser($this);
        }
    
        return $this;
    }
    public function removeAppointmentTitle($appointmentTitle): void
    {
        $this->appointmentTitles->removeElement($appointmentTitle);
    }
    public function getAppointmentTitles(): mixed
    {
        return $this->appointmentTitles;
    }

    public function addMedicalTitle($medicalTitle): self
    {
        if( $medicalTitle && !$this->medicalTitles->contains($medicalTitle) ) {
            $this->medicalTitles->add($medicalTitle);
            $medicalTitle->setUser($this);
        }
        return $this;
    }
    public function removeMedicalTitle($medicalTitle): void
    {
        $this->medicalTitles->removeElement($medicalTitle);
    }
    public function getMedicalTitles(): mixed
    {
        return $this->medicalTitles;
    }


    /**
     * @param mixed $credentials
     */
    public function setCredentials($credentials): void
    {
        $this->credentials = $credentials;
    }

    /**
     * @return mixed
     */
    public function getCredentials(): mixed
    {
        return $this->credentials;
    }

    public function addEmploymentStatus($employmentStatus): self
    {
        if( $employmentStatus ) {
            if( !$this->employmentStatus->contains($employmentStatus) ) {
                $this->employmentStatus->add($employmentStatus);
                $employmentStatus->setUser($this);
            }
        }

        return $this;
    }
    public function addEmploymentStatu($employmentStatus): self
    {
        $this->addEmploymentStatus($employmentStatus);
        return $this;
    }

    public function removeEmploymentStatus($employmentStatus): void
    {
        $this->employmentStatus->removeElement($employmentStatus);
    }
    public function removeEmploymentStatu($employmentStatus): void
    {
        $this->removeEmploymentStatus($employmentStatus);
    }

    /**
     * Get employmentStatus
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getEmploymentStatus(): mixed
    {
        return $this->employmentStatus;
    }



    /**
     * @return mixed
     */
    public function getPrivateComments(): mixed
    {
        return $this->privateComments;
    }
    public function addPrivateComment( $comment ): ?self
    {
        if( !$comment )
            return null;

        if( !$this->privateComments->contains($comment) ) {
            $comment->setUser($this);
            $this->privateComments->add($comment);
        }
        return $this;
    }
    public function removePrivateComment($comment): void
    {
        $this->privateComments->removeElement($comment);
    }


    /**
     * @return mixed
     */
    public function getPublicComments(): mixed
    {
        return $this->publicComments;
    }
    public function addPublicComment( $comment ): ?self
    {
        if( !$comment )
            return null;

        if( !$this->publicComments->contains($comment) ) {
            $comment->setUser($this);
            $this->publicComments->add($comment);
        }
        return $this;
    }
    public function removePublicComment($comment): void
    {
        $this->publicComments->removeElement($comment);
    }


    /**
     * @return mixed
     */
    public function getAdminComments(): mixed
    {
        return $this->adminComments;
    }
    public function addAdminComment( $comment ): ?self
    {
        if( !$comment ) {
            return null;
        }

        if( !$this->adminComments->contains($comment) ) {
            $comment->setUser($this);
            $this->adminComments->add($comment);
        }

        return $this;
    }
    public function removeAdminComment($comment): void
    {
        $this->adminComments->removeElement($comment);
    }


    /**
     * @return mixed
     */
    public function getConfidentialComments(): mixed
    {
        return $this->confidentialComments;
    }
    public function addConfidentialComment( $comment ): ?self
    {
        if( !$comment )
            return null;

        if( !$this->confidentialComments->contains($comment) ) {
            $comment->setUser($this);
            $this->confidentialComments->add($comment);
        }

        return $this;
    }
    public function removeConfidentialComment($comment): void
    {
        $this->confidentialComments->removeElement($comment);
    }



    public function addResearchLab($researchLab): self
    {
        if( $researchLab && !$this->researchLabs->contains($researchLab) ) {
            $this->researchLabs->add($researchLab);
            //$researchLab->addUser($this);
        }

        return $this;
    }
    public function removeResearchLab($researchLab): void
    {
        $this->researchLabs->removeElement($researchLab);
        //$researchLab->removeUser($this);
    }
    public function getResearchLabs()
    {
        return $this->researchLabs;
    }


    public function addGrant($item): self
    {
        if( $item && !$this->grants->contains($item) ) {
            $this->grants->add($item);
            $item->addUser($this);
        }
        return $this;
    }
    public function removeGrant($item): void
    {
        $this->grants->removeElement($item);
        $item->removeUser($this);
    }
    public function getGrants(): mixed
    {
        return $this->grants;
    }


    public function addPublication($item): self
    {
        if( $item && !$this->publications->contains($item) ) {
            $this->publications->add($item);
            //$item->addUser($this);
        }
        return $this;
    }
    public function removePublication($item): void
    {
        $this->publications->removeElement($item);
        //$item->removeUser($this);
    }
    public function getPublications(): mixed
    {
        return $this->publications;
    }

    public function addBook($item): self
    {
        if( $item && !$this->books->contains($item) ) {
            $this->books->add($item);
        }
        return $this;
    }
    public function removeBook($item): void
    {
        $this->books->removeElement($item);
    }
    public function getBooks(): mixed
    {
        return $this->books;
    }


    public function addLecture($item): self
    {
        if( $item && !$this->lectures->contains($item) ) {
            $this->lectures->add($item);
            $item->setUser($this);
        }
        return $this;
    }
    public function removeLecture($item): void
    {
        $this->lectures->removeElement($item);
    }
    public function getLectures(): mixed
    {
        return $this->lectures;
    }

    public function getPermissions(): mixed
    {
        return $this->permissions;
    }
    public function addPermission($item): void
    {
        if( $item && !$this->permissions->contains($item) ) {
            $this->permissions->add($item);
            $item->setUser($this);
        }
    }
    public function removePermission($item): void
    {
        $this->permissions->removeElement($item);
    }

    /**
     * @return mixed
     */
    public function getFailedAttemptCounter(): ?int
    {
        return $this->failedAttemptCounter;
    }
    /**
     * @param mixed $failedAttemptCounter
     */
    public function setFailedAttemptCounter($failedAttemptCounter): void
    {
        $this->failedAttemptCounter = $failedAttemptCounter;
    }
    public function incrementFailedAttemptCounter(): void
    {
        $counter = $this->getFailedAttemptCounter();
        if( $counter === null ) {
            $counter = 0;
        }
        $counter = $counter + 1;
        $this->setFailedAttemptCounter($counter);
        //$this->setLastFailedAttemptDate(new \DateTime());
    }
    public function resetFailedAttemptCounter(): void
    {
        if( $this->getFailedAttemptCounter() ) {
            $this->setFailedAttemptCounter(0);
            //$this->setLastFailedAttemptDate(null);
        }
    }

//    /**
//     * @return mixed
//     */
//    public function getNotificationEmailUser(): mixed
//    {
//        return $this->notificationEmailUser;
//    }
//
//    /**
//     * @param mixed $notificationEmailUser
//     */
//    public function setNotificationEmailUser($notificationEmailUser): void
//    {
//        $this->notificationEmailUser = $notificationEmailUser;
//    }

    public function getNotifyUsers(): mixed
    {
        return $this->notifyUsers;
    }
    public function addNotifyUser( $item ): ?self
    {
        if( !$item ) {
            return null;
        }

        if( !$this->notifyUsers->contains($item) ) {
            $this->notifyUsers->add($item);
        }

        return $this;
    }
    public function removeNotifyUser($item): void
    {
        $this->notifyUsers->removeElement($item);
    }

    /**
     * @return \DateTime|null
     */
    public function getLastActivity(): ?\DateTime
    {
        return $this->lastActivity;
    }

    /**
     * {@inheritdoc}
     */
    public function setLastActivity(\DateTime $time = null): self
    {
        $this->lastActivity = $time;

        return $this;
    }


    public function getLastLoggedUrl()
    {
        return $this->lastLoggedUrl;
    }

    public function setLastLoggedUrl($string)
    {
        $this->lastLoggedUrl = $string;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getActiveAD() {
        return $this->activeAD;
    }

    /**
     * @param mixed $activeAD
     */
    public function setActiveAD($activeAD) {
        $this->activeAD = $activeAD;
    }

    /**
     * @return mixed
     */
    public function getLastAdCheck()
    {
        return $this->lastAdCheck;
    }

    /**
     * @param mixed $lastAdCheck
     */
    public function setLastAdCheck($lastAdCheck)
    {
        if( $lastAdCheck ) {
            $lastAdCheck = new \DateTime();
        }
        $this->lastAdCheck = $lastAdCheck;
    }


//    /**
//     * @return \DateTime
//     */
//    public function getLastFailedAttemptDate()
//    {
//        return $this->lastFailedAttemptDate;
//    }
//    /**
//     * @param \DateTime $lastFailedAttemptDate
//     */
//    public function setLastFailedAttemptDate($lastFailedAttemptDate)
//    {
//        $this->lastFailedAttemptDate = $lastFailedAttemptDate;
//    }

    public function getUserIdentifier(): string
    {
        return $this->getUsername();
    }



    public function __toString(): string
    {
        //return $this->getUsername();
        //return $this->getDisplayOrFirstLastname();
        //return $this->getDisplayName();
        //return $this->getFirstName();

//        $displayName = null;
//        $infos = $this->getInfos();
//        if( $infos && count($infos) > 0 ) {
//            $displayName = $infos->first()->getDisplayName();
//        }
//        return $displayName;

        //testing
        //return $this->getPrimaryPublicUserId();                 //it takes ~20 sec, 3933 DB queries total, 3780 DB queries 'scan_perSiteSettings'
        //return (string) $this->getPrimaryUseridKeytypeStr();    //it takes ~20 sec, 3937 DB queries total, 3780 DB queries 'scan_perSiteSettings'

        return (string) $this->getUserNameStr();                //it takes ~30 sec, 7732 DB queries total, 3795 DB queries 'user_userInfo', 3780 DB queries 'scan_perSiteSettings'
    }





    public function setUsernameForce($username): self
    {
        if( $username ) {
            //$username = trim((string)$username);
            //$username = strtolower($username);
            $username = $this->canonicalize($username);
        }
        $this->username = $username;
        $this->setUsernameCanonicalForce($username);
        return $this;
    }
    //do not overwrite username when user id is set (user already exists in DB)
    public function setUsername($username): ?self
    {
        if( $this->getId() && $username != $this->getUsername() ) {
            //continue without error
            return null;
            //exit('Can not change username when user is in DB: username='.$username.', existing username='.$this->getUsername().', id='.$this->getId());
            //throw new \Exception( 'Can not change username when user is in DB: username='.$username.', existing username='.$this->getUsername().', id='.$this->getId() );
        }

        if( $username ) {
            //$username = trim((string)$username);
            //$username = strtolower($username);
            $username = $this->canonicalize($username);
        }

        $this->username = $username;
        $this->setUsernameCanonical($username);

        return $this;
    }
    public function setUsernameCanonicalForce($usernameCanonical): void
    {
        if( $usernameCanonical ) {
            //$usernameCanonical = trim((string)$usernameCanonical);
            //$usernameCanonical = strtolower($usernameCanonical);
            $usernameCanonical = $this->canonicalize($usernameCanonical);
        }
        $this->usernameCanonical = $usernameCanonical;
    }
    public function setUsernameCanonical($usernameCanonical): ?self
    {
        if( $this->getId() && $usernameCanonical != $this->getUsernameCanonical() ) {
            //continue without error
            return null;
            //exit('Can not change canonical username when user is in DB: username='.$usernameCanonical.', existing canonical username='.$this->getUsername().', id='.$this->getId());
            //throw new \Exception( 'Can not change canonical username when user is in DB: username='.$usernameCanonical.', existing canonical username='.$this->getUsername().', id='.$this->getId() );
        }

        $this->setUsernameCanonicalForce($usernameCanonical);

        return $this;
    }


    //Username utilities methods
    public function setUniqueUsername(): void
    {
        $this->setUsername($this->createUniqueUsername());
    }

    public function createUniqueUsername(): ?string
    {
        $uniqueUsername = $this->createUniqueUsernameByKeyKeytype($this->getKeytype(),$this->getPrimaryPublicUserId());
        if( $uniqueUsername ) {
            //$uniqueUsername = trim((string)$uniqueUsername);
            //$uniqueUsername = strtolower($uniqueUsername);
            $uniqueUsername = $this->canonicalize($uniqueUsername);
        }
        return $uniqueUsername;
    }

    public function createUniqueUsernameByKeyKeytype($keytype,$key): string
    {
        if( $key ) {
            //$key = trim((string)$key);
            //$key = strtolower($key);
            $key = $this->canonicalize($key);
        }

        $keytypeAbbreviation = 'local-user';
        if( $keytype ) {
            $keytypeAbbreviation = $keytype->getAbbreviation();
        }

        $username = $key."_@_".$keytypeAbbreviation;
        $usernamestr = preg_replace('/\s+/', '-', $username);   //replace all whitespaces by '-'
        return $usernamestr;
    }

    //Get CWID
    public function createCleanUsername($username): ?string
    {
        if( $username ) {
            $username = $this->canonicalize($username);
        }
        $usernameArr = explode("_@_",$username);
        return $usernameArr[0];
    }

    public function getUsernamePrefix($username=null): ?string
    {
        if( !$username ) {
            $username = $this->getUsername();
        }

        if( $username ) {
            //$username = trim((string)$username);
            //$username = strtolower($username);
            $username = $this->canonicalize($username);
        }

        $usernameArr = explode("_@_",$username);
        if( array_key_exists(1, $usernameArr) ) {
            $prefix = $usernameArr[1];
        } else {
            $prefix = "";
        }
        
        return $prefix;
    }

    public function usernameIsValid($username=null): bool
    {
        if( !$username ) {
            $username = $this->getUsername();
        }
        if( strpos((string)$username,"_@_") !== false ) {
            return true;
        }
        return false;
    }


    //[ORM\PreUpdate] - update
    //[ORM\PrePersist] - create
    //Use PreFlush - always check and set display name if empty on create or update user
    #[ORM\PreFlush]
    public function setDisplaynameIfEmpty(): void
    {
        $originalDisplayName = $this->getOriginalDisplayName();
        //echo "originalDisplayName=".$originalDisplayName."<br>";
        if( !$originalDisplayName || $originalDisplayName == "" ) {
            //$this->setDisplayname( $this->getUsernameOptimal() );
            $this->setDisplayname( $this->getDisplayName() );
        }
    }



    public function addInfo($info): self
    {
        if( $info && !$this->infos->contains($info) ) {
            $this->infos->add($info);
            $info->setUser($this);
        }

        return $this;
    }
    public function removeInfo($info): void
    {
        $this->infos->removeElement($info);
    }
    public function getInfos(): mixed
    {
        return $this->infos;
    }

    //for "friendsofsymfony/user-bundle" > "v2.0.0-alpha3" => locked field is removed
    public function setLocked( $value ): void
    {
        $this->setEnabled(!$value);
    }
    public function getLocked(): bool
    {
        return !$this->isEnabled();
    }
    public function isLocked(): bool
    {
        return !$this->isEnabled();
//        if( $this->isEnabled() ) {
//            return false;
//        } else {
//            return true;
//        }
    }
    public function setEnabled($boolean): self
    {
        $this->enabled = (bool) $boolean;

        if( $boolean ) {
            $this->resetFailedAttemptCounter();
        }

        return $this;
    }

    /////////////////////////// user's info mapper 7+2: //////////////////////////////////////
    /// suffix, firstName, middleName, lastName, displayName, initials, preferredPhone, preferredEmail/emailCanonical (used instead of extended user's email) ///
    /**
     * @param mixed $suffix
     */
    public function setSuffix($suffix): void
    {
        $infos = $this->getInfos();
        if( $infos && count($infos) > 0 ) {
            $infos->first()->setSuffix($suffix);
        }
    }

    /**
     * @return mixed
     */
    public function getSuffix(): ?string
    {
        $value = null;
        $infos = $this->getInfos();
        if( $infos && count($infos) > 0 ) {
            $value = $infos->first()->getSuffix();
        }
        return $value;
    }

    /**
     * @param mixed $firstName
     */
    public function setFirstName($firstName): void
    {
        $infos = $this->getInfos();
        if( $infos && count($infos) > 0 ) {
            $infos->first()->setFirstName($firstName);
        }
    }

    /**
     * @return mixed
     */
    public function getFirstName(): ?string
    {
        $value = null;
        $infos = $this->getInfos();
        if( $infos && count($infos) > 0 ) {
            $value = $infos->first()->getFirstName();
        }
        return $value;
    }

    /**
     * @param mixed $middleName
     */
    public function setMiddleName($middleName): void
    {
        $infos = $this->getInfos();
        if( $infos && count($infos) > 0 ) {
            $infos->first()->setMiddleName($middleName);
        }
    }

    /**
     * @return mixed
     */
    public function getMiddleName(): ?string
    {
        $value = null;
        $infos = $this->getInfos();
        if( $infos && count($infos) > 0 ) {
            $value = $infos->first()->getMiddleName();
        }
        return $value;
    }

    /**
     * @param mixed $lastName
     */
    public function setLastName($lastName): void
    {
        $infos = $this->getInfos();
        if( $infos && count($infos) > 0 ) {
            $infos->first()->setLastName($lastName);
        }
    }

    /**
     * @return mixed
     */
    public function getLastName(): ?string
    {
        $value = null;
        $infos = $this->getInfos();
        if( $infos && count($infos) > 0 ) {
            $value = $infos->first()->getLastName();
        }
//        if( $infos ) {
//            foreach ($infos as $info) {
//                $value = $info->getLastName();
//                return $value;
//            }
//        }
        return $value;
    }


    /**
     * @param mixed $firstName
     */
    public function setSalutation($salutation): void
    {
        $infos = $this->getInfos();
        if( $infos && count($infos) > 0 ) {
            $infos->first()->setSalutation($salutation);
        }
    }

    public function getSalutation(): ?string
    {
        $value = null;
        $infos = $this->getInfos();
        if( $infos && count($infos) > 0 ) {
            $value = $infos->first()->getSalutation();
        }
        return $value;
    }


    public function getLastNameUppercase(): ?string
    {
        return $this->capitalizeIfNotAllCapital($this->getLastName());
    }
    public function getFirstNameUppercase(): ?string
    {
        return $this->capitalizeIfNotAllCapital($this->getFirstName());
    }
//    function capitalizeIfNotAllCapitalOrig($s) {
//        //echo "1s=".$s."<br>";
//        if( strlen(preg_replace('![^A-Z]+!', '', $s)) == strlen((string)$s) ) {
//            $s = ucfirst(strtolower($s));
//        }
//        //echo "2s=".$s."<br>";
//        return ucwords($s);
//    }
    public function capitalizeIfNotAllCapital($s): mixed
    {
        if( !$s ) {
            return $s;
        }
        $convert = false;
        //check if all UPPER
        if( strtoupper($s) == $s ) {
            $convert = true;
        }
        //check if all lower
        if( strtolower($s) == $s ) {
            $convert = true;
        }
        if( $convert ) {
            return ucwords( strtolower($s) );
        }
        return $s;
    }
//    function isAllCapital($s) {
//        if( $this->count_capitals($s) == strlen((string)$s) ) {
//            return true;
//        }
//        return false;
//    }
//    function count_capitals($s) {
//        return strlen(preg_replace('![^A-Z]+!', '', $s));
//    }

    /**
     * @param mixed $displayName
     */
    public function setDisplayName($displayName): void
    {
        $infos = $this->getInfos();
        if( $infos && count($infos) > 0 ) {
            $infos->first()->setDisplayName($displayName);
        }
    }

    /**
     * @return mixed
     */
    public function getDisplayName(): ?string
    {
        $displayName = null;
        $infos = $this->getInfos();
        if( $infos && count($infos) > 0 ) {
            $displayName = $infos->first()->getDisplayName();
        }

        if( !$displayName ) {
            $displayName = $this->getFirstName() . " " . $this->getLastName();
        }

        if( !$displayName ){
            $displayName = $this->getPrimaryUseridKeytypeStr();
        }
        
        return $displayName."";
    }

    public function getOriginalDisplayName(): ?string
    {
        $displayName = null;
        $infos = $this->getInfos();
        if( $infos && count($infos) > 0 ) {
            $displayName = $infos->first()->getDisplayName();
        }
        return $displayName;
    }

    /**
     * @param mixed $preferredPhone
     */
    public function setPreferredPhone($preferredPhone): void
    {
        $infos = $this->getInfos();
        if( $infos && count($infos) > 0 ) {
            $infos->first()->setPreferredPhone($preferredPhone);
        }
    }
    /**
     * @return mixed
     */
    public function getPreferredPhone(): ?string
    {
        $value = null;
        $infos = $this->getInfos();
        if( $infos && count($infos) > 0 ) {
            $value = $infos->first()->getPreferredPhone();
        }
        return $value;
    }

    /**
     * @param mixed $preferredMobilePhone
     */
    public function setPreferredMobilePhone($preferredMobilePhone): void
    {
        $infos = $this->getInfos();
        if( $infos && count($infos) > 0 ) {
            $infos->first()->setPreferredMobilePhone($preferredMobilePhone);
        }
    }
    /**
     * @return mixed
     */
    public function getPreferredMobilePhone(): ?string
    {
        $value = null;
        $infos = $this->getInfos();
        if( $infos && count($infos) > 0 ) {
            $value = $infos->first()->getPreferredMobilePhone();
        }
        return $value;
    }

    /**
     * @return mixed
     */
    public function getUserInfoByPreferredMobilePhone($preferredMobilePhone): ?string
    {
        $preferredMobilePhone = str_replace("+","",$preferredMobilePhone);
        $infos = $this->getInfos();
        foreach($infos as $info) {
            $thisPreferredMobilePhone = $info->getPreferredMobilePhone();
            $thisPreferredMobilePhone = str_replace("+","",$thisPreferredMobilePhone);
            //echo "[$preferredMobilePhone] =? [$thisPreferredMobilePhone]<br>";
            //exit();
            if( $thisPreferredMobilePhone && $preferredMobilePhone && $thisPreferredMobilePhone == $preferredMobilePhone ) {
                //echo 'found userInfo='.$info->getId()."<br>";
                return $info;
            }
        }
        //echo 'not found userInfo<br>';
        return null;
    }
    public function getUserInfo(): mixed
    {
        $infos = $this->getInfos();
        if( $infos && count($infos) > 0 ) {
            return $infos->first();
        }
        return false;
    }

    /**
     * @param mixed $initials
     */
    public function setInitials($initials): void
    {
        $infos = $this->getInfos();
        if( $infos && count($infos) > 0 ) {
            $infos->first()->setInitials($initials);
        }
    }

    /**
     * @return mixed
     */
    public function getInitials(): ?string
    {
        $value = null;
        $infos = $this->getInfos();
        if( $infos && count($infos) > 0 ) {
            $value = $infos->first()->getInitials();
        }
        return $value;
    }

   //overwrite email
    public function setEmail($email): ?self
    {
        $infos = $this->getInfos();
        if( $infos && count($infos) > 0 ) {
            $infos->first()->setEmail($email);
        }

        return $this;
    }
    public function getEmail(): ?string
    {
        $value = null;
        $infos = $this->getInfos();
        if( $infos && count($infos) > 0 ) {
            $value = $infos->first()->getEmail();
        }
        return $value;
    }
    //overwrite canonical email
    public function setEmailCanonical($emailCanonical): self
    {
        $infos = $this->getInfos();
        if( $infos && count($infos) > 0 ) {
            if( $emailCanonical ) {
                //$emailCanonical = strtolower($emailCanonical);
                $emailCanonical = $this->canonicalize($emailCanonical);
            }
            $infos->first()->setEmailCanonical($emailCanonical);
        }

        return $this;
    }
    public function getEmailCanonical(): ?string
    {
        $value = null;
        $infos = $this->getInfos();
        if( $infos && count($infos) > 0 ) {
            $value = $infos->first()->getEmailCanonical();
        }
        return $value;
    }

    /**
     * @param mixed $perSiteSettings
     */
    public function setPerSiteSettings($perSiteSettings): void
    {
        $this->perSiteSettings = $perSiteSettings;
        $perSiteSettings->setUser($this);
    }

    /**
     * @return mixed
     */
    public function getPerSiteSettings(): mixed
    {
        return $this->perSiteSettings;
    }

    /////////////////////////////////////////////////////////////////






    //////////////////// util methods ////////////////////////
    
    public function getCleanUsername(): ?string
    {
        return $this->createCleanUsername( $this->getUsername() );
    }

    //show user's FirstName LastName - userName (userNameType)
    public function getUserNameStr( $showStatus=false ): ?string
    {
        //Add (No longer works)
        //echo "showStatus=$showStatus <br>";
        $statusStr = "";
        if( $showStatus ) {
            $statusStr = $this->getFullStatusStr();
        }

        $primaryUseridKeytypeStr = $this->getPrimaryUseridKeytypeStr();
        //$primaryUseridKeytypeStr = " 222 ";
        $displayName = $this->getDisplayName();

        if( !$displayName ) {
            $displayName = $this->getFirstName() . " " . $this->getLastName();
        }

        if( $displayName && $primaryUseridKeytypeStr ) {
            return $displayName . " - " . $primaryUseridKeytypeStr.$statusStr;
        }

        if( $primaryUseridKeytypeStr && !$displayName ){
            return $primaryUseridKeytypeStr."".$statusStr;
        }

        if( $displayName && !$primaryUseridKeytypeStr ){
            return $displayName."".$statusStr;
        }
    }

    //Get displayname or first + last name
    //Used in vacreq personAwayInfo
    public function getDisplayOrFirstLastname( $showStatus=false ): ?string
    {

        $displayName = $this->getDisplayName();

        if( !$displayName ) {
            $displayName = $this->getFirstName() . " " . $this->getLastName();

            //Add status (No longer works, Active in AD)
            if( $showStatus ) {
                $addInfo = $this->getFullStatusStr();
                $displayName = $displayName . $addInfo;
            }
        }

        if( !$displayName ){
            $displayName = $this->getUserNameStr($showStatus);
        }

        return $displayName;
    }


    public function getUserNameShortStr(): ?string
    {
        return $this->getPrimaryPublicUserId();
    }

    public function getPrimaryUseridKeytypeStr( string $delimeter = " " ) : ?string
    {
        //Exception in twig: UsernameType was already present for the same ID (example dev user id=12: $this->getKeytype() -> this error)
        if( $this->getKeytype() && $this->getKeytype()->getName() ) {
            return $this->getPrimaryPublicUserId().$delimeter."(".$this->getKeytype()->getName().")";
        } else {
            return $this->getPrimaryPublicUserId()."";
        }
    }


    public function getUsernameShortest(): ?string
    {
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
    //FirstName LastName, MD
    //if $inverted is true => LastName FirstName
    public function getUsernameOptimal( $inverted=false ): ?string
    {
        $degrees = array();
        $titles = array();

        //get appended degrees
        $trainings = $this->getTrainings();
        //echo "trainings=".count($trainings)."<br>";
        foreach($trainings as $training) {
            //echo "training=".$training."<br>";
            if ($training->getAppendDegreeToName() && $training->getDegree()) {
                $degrees[] = $training->getDegree();
            }
            if ($training->getAppendFellowshipTitleToName() && $training->getFellowshipTitle()) {
                if ($training->getFellowshipTitle()->getAbbreviation()) {
                    $titles[] = $training->getFellowshipTitle()->getAbbreviation();
                }
            }
        }

        $degreesStr = implode(", ", $degrees);
        $titlesStr = implode(", ", $titles);

        $degreesAndTitlesStr = $degreesStr;
        if( $titlesStr ) {
            $degreesAndTitlesStr = $degreesAndTitlesStr . ", " . $titlesStr;
        }

        if( $degreesAndTitlesStr ) {
            $degreesAndTitlesStr = ", " . $degreesAndTitlesStr;
        }


        if( $this->getDisplayName() ) {
            return $this->getDisplayName() . $degreesAndTitlesStr;
        }

        if( $this->getLastName() && $this->getFirstName() ) {
            if( $inverted ) {
                return $this->getLastName() . " " . $this->getFirstName() . $degreesAndTitlesStr;
            } else {
                return $this->getFirstName() . " " . $this->getLastName() . $degreesAndTitlesStr;
            }
        }

        if( $this->getLastName() ) {
            return $this->getLastName() . $degreesAndTitlesStr;
        }

        if( $this->getFirstName() ) {
            return $this->getFirstName() . $degreesAndTitlesStr;
        }

        if( $this->getPrimaryPublicUserId() ) {
            return $this->getPrimaryPublicUserId() . $degreesAndTitlesStr;
        }

        return $this->getId();
    }
    public function getOptimalAbbreviationName(): ?string
    {
        return $this->getUserNameStr();
    }

    public function getSingleLastName(): ?string
    {
        if( $this->getLastName() ) {
            return $this->getLastName();
        }

        if( $this->getDisplayName() ) {
            //Joe S. Doe => Doe
            $userName = $this->getDisplayName();
            $userNameArr = explode(" ", $userName);
            $userNameCount = count($userNameArr);
            $userFamilyname = $userNameArr[$userNameCount-1];
            return $userFamilyname;
        }

        return null;
    }
    public function getSingleFirstName(): ?string
    {
        if( $this->getFirstName() ) {
            return $this->getFirstName();
        }

        if( $this->getDisplayName() ) {
            //Joe S. Doe => Doe
            $userName = $this->getDisplayName();
            $userNameArr = explode(" ", $userName);
            $userNameCount = count($userNameArr);
            //$userFirstname = $userNameArr[$userNameCount-1];
            $userFirstnameArr = array();
            for( $i=0; $i<$userNameCount-2; $i++ ) {
                $userFirstnameArr[] = $userNameArr[$i];
            }
            $userFirstname = implode(" ",$userFirstnameArr);

            return $userFirstname;
        }

        return null;
    }
    public function getSingleSalutation(): ?string
    {
        if( $this->getSalutation() ) {
            return $this->getSalutation();
        }

        $degrees = array();
        //get appended degrees
        foreach( $this->getTrainings() as $training ) {
            if( $training->getAppendDegreeToName() && $training->getDegree() ) {
                $degrees[] = $training->getDegree();
            }
        }
        if( count($degrees) > 0 ) {
            $degreesStr = implode(", ", $degrees);
            return $degreesStr;
        }

        return null;
    }

    public function getSingleDegree(): ?string
    {
        
        $degrees = array();
        //get appended degrees
        foreach( $this->getTrainings() as $training ) {
            if( $training->getAppendDegreeToName() && $training->getDegree() ) {
                $degrees[] = $training->getDegree();
            }
        }
        if( count($degrees) > 0 ) {
            $degreesStr = implode(", ", $degrees);
            return $degreesStr;
        }

        return null;
    }


    //get all institutions from administrative and appointment titles.
    //status: 0-unverified, 1-verified
    public function getInstitutions($type=null,$status=null,$priority=null): mixed
    {
        $institutions = new ArrayCollection();
        if( $type == null || $type == "AdministrativeTitle" ) {
            foreach( $this->getAdministrativeTitles() as $adminTitles ) {
                if( $adminTitles->getInstitution() && $adminTitles->getInstitution()->getId() && $adminTitles->getInstitution()->getName() != "" )
                    //echo "AdministrativeTitle inst=".$adminTitles->getInstitution()."<br>";
                    if( !$institutions->contains($adminTitles->getInstitution()) ) {
                        if( $status == null || $adminTitles->getStatus() == $status ) {
                            if( $priority === null || $priority."" === $adminTitles->getPriority()."" ) {
                                $institutions->add($adminTitles->getInstitution());
                            }
                        }
                    }
            }
        }
        if( $type == null || $type == "AppointmentTitle" ) {
            foreach( $this->getAppointmentTitles() as $appTitles ) {
                if( $appTitles->getInstitution() && $appTitles->getInstitution()->getId() && $appTitles->getInstitution()->getName() != "" )
                    //echo "1 AppointmentTitle inst=".$appTitles->getInstitution()."<br>";
                    if( !$institutions->contains($appTitles->getInstitution()) ) {
                        //echo "2 AppointmentTitle inst=".$appTitles->getInstitution()."<br>";
                        if( $status == null || $appTitles->getStatus() == $status ) {
                            //echo "3 AppointmentTitle inst=".$appTitles->getInstitution()."<br>";
                            //echo "priority: ".$priority."" ."===". $appTitles->getPriority()."<br>";
                            if( $priority === null || $priority."" === $appTitles->getPriority()."" ) {
                                //echo "4 AppointmentTitle inst=".$appTitles->getInstitution()."<br>";
                                $institutions->add($appTitles->getInstitution());
                            }
                        }
                    }
            }
        }
        if( $type == null || $type == "MedicalTitle" ) {
            foreach( $this->getMedicalTitles() as $medicalTitles ) {
                if( $medicalTitles->getInstitution() && $medicalTitles->getInstitution()->getId() && $medicalTitles->getInstitution()->getName() != "" )
                    //echo "MedicalTitle inst=".$medicalTitles->getInstitution()."<br>";
                    if( !$institutions->contains($medicalTitles->getInstitution()) ) {
                        if( $status == null || $medicalTitles->getStatus() == $status ) {
                            if( $priority === null || $priority."" === $medicalTitles->getPriority()."" ) {
                                $institutions->add($medicalTitles->getInstitution());
                            }
                        }
                    }
            }
        }
        //echo "inst count=".count($institutions)."<br>";
//        foreach( $institutions as $institution ) {
//            echo "inst=".$institution->getId()."-".$institution->getName()."<br>";
//        }

        return $institutions;
    }

    //return array grouped by institution name:
    //instArr[instName][] = array('rootId'=>$rootId,'instId'=>$instId)
    //Cases:
    // Software Development (WCMC)
    // Pathology Informatics (NYP, WCMC)
    //Molecular Hematopathology (Hematopathology-WCMC, Molecular and Genomic Pathology-WCMC, Hematopathology-NYP, Molecular and Genomic Pathology-NYP)"
    public function getDeduplicatedInstitutions($priority=null): mixed
    {

        $instArr = array();

        $institutions = $this->getInstitutions(null,null,$priority);
        //echo "1 inst count=".count($institutions)."<br>";
        if( count($institutions) == 0 ) {
            $institutions = $this->getInstitutions();
            //echo "2 inst count=".count($institutions)."<br>";
        }

        foreach( $institutions as $institution ) {
            $instName = $institution->getName()."";
            //echo "instName=".$instName."<br>";

            //default uniqueName = "WCM"
            $uniqueName = $institution->getRootName($institution)."";

            $rootId = null;
            if( $institution->getRootName($institution) ) {
                $rootId = $institution->getRootName($institution)->getId();
            }

            $institution = array(
                'rootId'=>$rootId,
                'uniqueName'=>$uniqueName,
                'parentName'=>$institution->getParent()."",
                'instId'=>$institution->getId(),
                'instNameWithRoot'=>$institution->getNodeNameWithRoot(),
                'instName'=>$institution->getName().""
            );

            $instArr[$instName][] = $institution;
        }

//        echo '<pre>';
//        print_r($instArr);
//        echo  '</pre>';

        //constract a new correct array
        $instResArr = array();

        foreach ($instArr as $instKeyName => $instInfoArr) {
            foreach ($instInfoArr as $institution) {
                //check if name is already exists in instArr, but id different
                if( !$this->isSingleUniqueKeyMatchCombination($instInfoArr, 'uniqueName', $institution['uniqueName']) ) {
                    //echo "different inst with same name=".$institution['instId']."<br>";
                    //uniqueName = "Hematopathology-WCMC"
                    $institution['uniqueName'] = $institution['parentName']."-".$institution['uniqueName'];
                }
                $instResArr[$instKeyName][] = $institution;
            }
        }

//        echo '<pre>';
//        print_r($instResArr);
//        echo  '</pre>';

        return $instResArr;
    }
    public function isSingleUniqueKeyMatchCombination($array,$key1,$match1): bool
    {
        $count = 0;
        foreach( $array as $element ) {
            //echo $count.": compare: ".$element[$key1]."==".$match1."=>";
            if( $element[$key1] == $match1 ) {
                $count++;
                //echo "not unique! <br>";
                if( $count > 1 ) {
                    //echo "not unique! <br>";
                    return false;
                }
            } else {
                //echo "<br>";
            }
        }
        return true;
    }
//    public function isUniqueKeyMatchCombination($array,$key1,$match1,$key2,$match2) {
//        $count = 0;
//        foreach( $array as $element ) {
//            echo $count.": compare: ".$element[$key1]."==".$match1." && ".$element[$key2]."==".$match2."<br>";
//            if( $element[$key1] == $match1 && $element[$key2] == $match2 ) {
//                $count++;
//                if( $count > 1 ) {
//                    echo "not unique! <br>";
//                    return false;
//                }
//            }
//        }
//        return true;
//    }

    //TODO: check performance of foreach. It might be replaced by direct DB query
    public function getBosses(): array
    {
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

    //returns: [Medical Director of Informatics] FirstName LastName
    public function getTitleAndNameByTitle( $admintitle ): string
    {
        return "[" . $admintitle->getName() . "] " . $this->getUsernameOptimal();
    }

    //from any (first) "Administrative Title" if available, if not - from any/first title
    public function getDetailsArr(): array
    {

        $res = array("title"=>null,"institution"=>null);

//        $adminTitles = $this->getAdministrativeTitles();
//        if( count($adminTitles)>0 ) {
//            $title = $adminTitles->first();
//            if( !$res["title"] ) {
//                $res["title"] = $title->getName()."";
//            }
//            if( !$res["institution"] ) {
//                $res["institution"] = $title->getInstitution()."";
//            }
//        }
        $adminTitles = $this->getAdministrativeTitles();    //it was typo?: getAppointmentTitles();
        $res = $this->getDetailsArrFromTitles($adminTitles,$res);

        $appTitles = $this->getAppointmentTitles();
        $res = $this->getDetailsArrFromTitles($appTitles,$res);

        $medTitles = $this->getMedicalTitles();
        $res = $this->getDetailsArrFromTitles($medTitles,$res);

        return $res;
    }
    public function getDetailsArrFromTitles($titles,$res): mixed
    {
        if( count($titles)>0 ) {
            $title = $titles->first();
            if( !$res["title"] ) {
                $res["title"] = $title->getName()."";
            }
            if( !$res["institution"] ) {
                $res["institution"] = $title->getInstitution();
            }
        }
        return $res;
    }


    //Testing
    public function getMemoryUsage(): mixed
    {
        //return round(memory_get_usage() / 1024);
        return memory_get_usage();
    }

    //TODO: check performance of foreach. It might be replaced by direct DB query
    public function getAssistants(): array
    {
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

    public function getSiteRoles($sitename): array
    {

        $roles = array();

        if( $sitename == 'employees' ) {
            $sitename = 'userdirectory';
        }

        //return $this->getRoles();

        foreach( $this->getRoles() as $role ) {
            if( stristr($role, $sitename) ) {
                $roles[] = $role;
            }
        }

//        foreach( $this->getRoles() as $role ) {
//            $roleObject = $em->getRepository('AppUserdirectoryBundle:Roles')->findOneByName($role);
//            if( $roleObject && $roleObject->hasSite( $this->siteName ) ) {
//                $originalRoles[] = $role;
//            }
//        }

        return $roles;
    }

    //Preferred: (646) 555-5555
    //Main Office Line: (212) 444-4444
    //Main Office Mobile: (123) 333-3333
    public function getAllPhones(): array
    {
        $phonesArr = array();
        //get all locations phones
        foreach( $this->getLocations() as $location ) {
            if( count($location->getLocationTypes()) == 0 || !$location->hasLocationTypeName("Employee Home") ) {
                if( $location->getPhone() ) {
                    $phone = array();
                    $phone['prefix'] = $location->getName()." Line: ";
                    $phone['phone'] = $location->getPhone();
                    $phonesArr[] = $phone;
                }
                if( $location->getMobile() ) {
                    $phone = array();
                    $phone['prefix'] = $location->getName()." Mobile: ";
                    $phone['phone'] = $location->getMobile();
                    $phonesArr[] = $phone;
                }
                if( $location->getPager() ) {
                    $phone = array();
                    $phone['prefix'] = $location->getName()." Pager: ";
                    $phone['phone'] = $location->getPager();
                    $phonesArr[] = $phone;
                }
//                if( $location->getIc() )
//                    $phonesArr[] = $location->getName()." Intercom: ".$location->getIc();
            }
        }

        if( $this->getPreferredPhone() ) {
            $phone = array();
            if( count($phonesArr) > 0 ) {
                $phone['prefix'] = "Preferred: ";
            } else {
                $phone['prefix'] = "";
            }
            $phone['phone'] = $this->getPreferredPhone();
            $phonesArr[] = $phone;
        }
        
        //getPreferredMobilePhone
        if( $this->getPreferredMobilePhone() ) {
            $phone = array();
            if( count($phonesArr) > 0 ) {
                $phone['prefix'] = "Preferred Mobile: ";
            } else {
                $phone['prefix'] = "";
            }
            $phone['phone'] = $this->getPreferredMobilePhone();
            $phonesArr[] = $phone;
        }

        if( count($phonesArr) == 1 ) {
            $phonesArr[0]['prefix'] = "";
        }

        rsort($phonesArr);

        return $phonesArr;
    }

    public function getSinglePhoneAndPager(): array
    {
        $phone = $this->getPreferredPhone();
        $pager = null;
        foreach( $this->getLocations() as $location ) {
            if( count($location->getLocationTypes()) == 0 || !$location->hasLocationTypeName("Employee Home") ) {
                if( !$phone && $location->getPhone() ) {
                    $phone = $location->getPhone();
                }
                if( !$pager && $location->getPager() ) {
                    $pager = $location->getPager();
                }
                if( $phone && $pager ) {
                    break;
                }
            }
        }
        $phoneArr = array(
            'phone' => $phone,
            'pager' => $pager
        );
        return $phoneArr;
    }

    public function getSinglePhone(): ?string
    {
        $phoneArr = $this->getSinglePhoneAndPager();
        return $phoneArr['phone'];
    }

//    public function getSinglePager() {
//        foreach( $this->getLocations() as $location ) {
//            if( count($location->getLocationTypes()) == 0 || !$location->hasLocationTypeName("Employee Home") ) {
//                if( $location->getPager() ) {
//                    return $location->getPager();
//                }
//            }
//        }
//        return null;
//    }
    public function getAllFaxes(): ?string
    {
        $faxArr = array();
        //get all locations phones
        foreach ($this->getLocations() as $location) {
            if( $location->getFax() ) {
                $faxArr[] = $location->getFax();
            }
        }
        return implode(", ",$faxArr);
    }

    public function getAllEmail(): array
    {
        $emailArr = array();
        //echo "count loc=".count($this->getLocations())."<br>";
        //get all locations phones
        foreach( $this->getLocations() as $location ) {
            //echo "loc=".$location."<br>";
            if( count($location->getLocationTypes()) == 0 || $location->hasLocationTypeName("Employee Home") ) {
                //echo "email:".$location->getEmail()."<br>";
                if( $location->getEmail() ) {
                    $email = array();
                    $email['prefix'] = $location->getName().": ";
                    $email['email'] = $this->getEmail();
                    $emailArr[] = $email;
                }
            }
        }

        if( $this->getEmail() ) {
            $email = array();
            if( count($emailArr) > 0 ) {
                $email['prefix'] = "Preferred: ";
            } else {
                $email['prefix'] = "";
            }
            $email['email'] = $this->getEmail();
            $emailArr[] = $email;
        }

        if( count($emailArr) == 1 ) {
            $emailArr[0]['prefix'] = "";
        }

        rsort($emailArr);

        return $emailArr;
    }

//    //$critical=true - this email will be used in the critical emails send directly to the user.
//    //$critical=false - this email will not be sent to the user, but it will be sent to the notification user(s).
//    //TODO: add additional emails from "Only send notifications to the following email address(es)"
//    // if critical==true. Or add new method getUserEmails() and replace getSingleEmail(false) by getUserEmails()
//    // if thoses emails are used as receipients. If these emails somehow are used as a sender,
//    // then choose only the first one.
//    //$critical is not need it. Create new function getRecipientEmails() that will be used as a recepeint emails.
//    public function getSingleEmail_ORIG( $critical=true ): mixed
//    {
//        if( !$critical ) {
//            //if not critical, use "Send email notification to" field in the user profile
//            $notificationEmailUser = $this->getNotificationEmailUser();
//            if( $notificationEmailUser ) {
//                $notificationEmailUserSingleEmail = $notificationEmailUser->getSingleEmail();
//                return $notificationEmailUserSingleEmail;
//            }
//        }
//
//        if( $this->getEmail() ) {
//            return $this->getEmail();
//        }
//
//        foreach( $this->getLocations() as $location ) {
//            if( $location->getEmail() && $location->hasLocationTypeName("Employee Office") ) {
//                return $location->getEmail();
//            }
//        }
//
//        foreach( $this->getLocations() as $location ) {
//            if( $location->getEmail() ) {
//                return $location->getEmail();
//            }
//        }
//
//        return null;
//    }

    //$critical=true - this email will be used in the critical emails send directly to the user.
    //$critical=false - this email will not be sent to the user, but it will be sent to the notification user(s).
    // if thoses emails are used as receipients. If these emails somehow are used as a sender,
    // then choose only the first one.
    //$critical might not need it. Create new function getRecipientEmails() that will be used as a recepeint emails.
    //Return string of email(s)
    public function getSingleEmail( $critical=true, $delimeter=", " ): mixed
    {
        //$critical not used, it can be remove;
        //if( !$critical ) {
        //Always send email to notifyUsers instead of original email
        $notifyUsers = $this->getNotifyUsers();
        if( count($notifyUsers) > 0 ) {
            //if not critical, use "Send email notification to" field in the user profile
            $notifyUserEmails = array();
            foreach( $notifyUsers as $notifyUser) {
                $notifyUserEmails[] = $notifyUser->getSingleEmail();
            }

            if( count($notifyUserEmails) > 0 ) {
                $notifyUserEmailsStr = implode($delimeter,$notifyUserEmails);
                //echo "notifyUserEmailsStr=$notifyUserEmailsStr<br>";
                if( $notifyUserEmailsStr ) {
                    return $notifyUserEmailsStr;
                }
            }
        }

        if( $this->getEmail() ) {
            return $this->getEmail();
        }

        foreach( $this->getLocations() as $location ) {
            if( $location->getEmail() && $location->hasLocationTypeName("Employee Office") ) {
                return $location->getEmail();
            }
        }

        foreach( $this->getLocations() as $location ) {
            if( $location->getEmail() ) {
                return $location->getEmail();
            }
        }

        return null;
    }

    //Get "name <email>"
    public function getNameEmail(): string
    {
        $email = $this->getSingleEmail();
        $optimalUserName = $this->getUsernameOptimal();
        return $optimalUserName." <".$email.">";
    }

    public function isInUserArray( $users ): bool
    {
        if( !$users ) {
            return false;
        }
        foreach( $users as $user ) {
            //echo "user=".$user."<br>";
            if( $user->getId() == $this->getId() ) {
                return true;
            }
        }
        return false;
    }

    //return null if user is still employed
    //return str if user not longer working:
    //"No longer works at the [Institution] as of MM/DD/YYYY. (show the most recent "End of Employment Date")
    public function getEmploymentTerminatedStr( $withLockStatus=true ) {
        $res = "";
        $emplCount = 0;
        $resArr = array();

        foreach( $this->getEmploymentStatus() as $employmentStatus ) {

            $startDateStr = null;
            $endDateStr = null;
            $intstitution = $employmentStatus->getInstitution();

            if( $employmentStatus->getTerminationDate() ) {
                $endDateStr = $employmentStatus->getTerminationDate()->format("m/d/Y");
            }

            if( $employmentStatus->getHireDate() ) {
                $startDateStr = $employmentStatus->getHireDate()->format("m/d/Y");
            }

            //1) if start date AND Organizational Group is NULL
            if( !$startDateStr && $endDateStr && !$intstitution  ) {
                //Employed prior to 11/28/2022.
                $resArr[] = "Employed prior to $endDateStr";
            }

            //2) If “Start Date” is not NULL and “End Date” is not NULL, but Organizational Institution is NULL
            if( $startDateStr && $endDateStr && !$intstitution  ) {
                //Employed from 11/22/2022 to 11/28/2022.
                $resArr[] = "Employed from $startDateStr to $endDateStr";
            }

            //3) If “Start Date” is not NULL (and “End Date” is not NULL) and Organizational Institution is not NULL
            if( $startDateStr && $endDateStr && $intstitution  ) {
                //Employed by [Organizational Group] from 11/22/2022 to 11/28/2022.
                $resArr[] = "Employed by $intstitution from $startDateStr to $endDateStr";
            }

            //4) If “Start Date” is NULL (and “End Date” is not NULL) and Organizational Institution is not NULL
            if( !$startDateStr && $endDateStr && $intstitution  ) {
                //Employed by [Organizational Group] prior to 11/28/2022.
                $resArr[] = "Employed by $intstitution prior to $endDateStr";
            }

            $emplCount++;
        }

        if( count($resArr) > 0 ) {
            $res = implode("; ",$resArr);
        }

        if( $withLockStatus ) {
            if ($this->isLocked()) {
                if ($res) {
                    $res = $res . ".";
                }
                $res = $res . " Account is locked";
            }
        }

//        //get AD status
//        if( $res ) {
//            $res = $res . ";";
//        }
//        if( $this->getActiveAD() === true ) {
//            $res = $res . " Active in AD";
//        }
//        if( $this->getActiveAD() === false ) {
//            $res = $res . " Inactive in AD";
//        }
//        if( $this->getActiveAD() === null ) {
//            //$res = $res . " AD status unknown";
//            $res = $res . " Inactive in AD";
//        }

        return $res;
    }

    public function getAdStatusStr() {
        $res = "";
        //get AD status
//        if( $res ) {
//            $res = $res . ";";
//        }
        if( $this->getActiveAD() === true ) {
            $res = "Active in AD";
        }
        if( $this->getActiveAD() === false ) {
            $res = "Inactive in AD";
        }
        if( $this->getActiveAD() === null ) {
            $res = "Inactive in AD";
        }
        return $res;
    }


    public function getFullStatusStr( $short=true, $withBrackets=true, $withLockStatus=true ) {
        $addInfo = "";

        $terminatedStr = $this->getEmploymentTerminatedStr($withLockStatus);
        //echo "terminatedStr=$terminatedStr <br>";
        if( $terminatedStr ) {
            if( $short ) {
                $addInfo = "No longer works";
            } else {
                $addInfo = $terminatedStr;
            }
        }

        $adStatus = $this->getAdStatusStr();
        if( $adStatus ) {
            if( $addInfo ) {
                $addInfo = $addInfo . "; ";
            }
            $addInfo = $addInfo . $adStatus;
        }

        if( $withBrackets && $addInfo ) {
            $addInfo = " (" . $addInfo . ")";
        }

        return $addInfo;
    }

    public function getEmploymentStartEndDates( $asString=true, $format='m/d/Y' )
    {
        $resArr = array();
        $startDate = NULL;
        $endDate = NULL;

        //"terminationDate" = "ASC" - the earliest date is shown first, the latest date is shown last
//        $latestEmploymentStatus = NULL;
//        $employmentStatuses = $this->getEmploymentStatus();
//        if( count($employmentStatuses) > 0 ) {
//            $latestEmploymentStatus = $employmentStatuses->first();
//        }
        $latestEmploymentStatus = $this->getLatestEmploymentStatus();

        if( $latestEmploymentStatus ) {

            if( $latestEmploymentStatus->getHireDate() ) {
                $startDate = $latestEmploymentStatus->getHireDate();
                if( $startDate && $asString ) {
                    $startDate = $startDate->format($format);
                }
            }
            if( $latestEmploymentStatus->getTerminationDate() ) {
                $endDate = $latestEmploymentStatus->getTerminationDate();
                if( $endDate && $asString ) {
                    $endDate = $endDate->format($format);
                }
            }
        }

        //echo "startDate=".$startDate."<br>";
        $resArr['startDate'] = $startDate;
        $resArr['endDate'] = $endDate;

        return $resArr;
    }

    public function getLatestEmploymentStatus() {
        //"terminationDate" = "ASC" - the earliest date is shown first, the latest date is shown last
        $latestEmploymentStatus = NULL;
        $employmentStatuses = $this->getEmploymentStatus();
        if( count($employmentStatuses) > 0 ) {
            $latestEmploymentStatus = $employmentStatuses->first();
        }
        return $latestEmploymentStatus;
    }

    public function getAllEmploymentStartEndDates( $asString=true, $format='m/d/Y' ) {
        $employmentStatuses = array();
        foreach($this->getEmploymentStatus() as $employmentStatus) {
            if( $employmentStatus ) {
                if( $employmentStatus->getIgnore() !== TRUE ) {
                    $resArr = array();
                    $startDate = NULL;
                    $endDate = NULL;
                    if ($employmentStatus->getHireDate()) {
                        $startDate = $employmentStatus->getHireDate();
                        if ($startDate && $asString) {
                            $startDate = $startDate->format($format);
                        }
                    }
                    if ($employmentStatus->getTerminationDate()) {
                        $endDate = $employmentStatus->getTerminationDate();
                        if ($endDate && $asString) {
                            $endDate = $endDate->format($format);
                        }
                    }

                    $groupName = NULL;
                    $group = $employmentStatus->getapprovalGroupType();
                    if( $group ) {
                        $groupName = $group->getName();
                    }
                    //echo "startDate=".$startDate."<br>";
                    $resArr['startDate'] = $startDate;
                    $resArr['endDate'] = $endDate;
                    $resArr['ignore'] = $employmentStatus->getIgnore();
                    $resArr['effort'] = $employmentStatus->getEffort();
                    $resArr['group'] = $groupName;
                    $employmentStatuses[] = $resArr;
                }
            }
        }
        return $employmentStatuses;
    }

    public function getDegreesTitles() {
        $degrees = array();
        $titles = array();

        //get appended degrees
        $trainings = $this->getTrainings();
        //echo "trainings=".count($trainings)."<br>";
        foreach($trainings as $training) {
            //echo "training=".$training."<br>";
            if ($training->getAppendDegreeToName() && $training->getDegree()) {
                $degrees[] = $training->getDegree();
            }
            if ($training->getAppendFellowshipTitleToName() && $training->getFellowshipTitle()) {
                if ($training->getFellowshipTitle()->getAbbreviation()) {
                    $titles[] = $training->getFellowshipTitle()->getAbbreviation();
                }
            }
        }

        $degreesStr = implode(", ", $degrees);
        $titlesStr = implode(", ", $titles);
    
        return 
            array(
                'degree' => $degreesStr,
                'title' => $titlesStr,
            );
    }

    /////////////////////// NOT USED!!! Return: Chief, Eyebrow Pathology ///////////////////////
    //Group by institutions
    public function getHeadInfo(): array
    {
        $instArr = array();

        $instArr = $this->addTitleInfo($instArr,'administrativeTitle',$this->getAdministrativeTitles());

        $instArr = $this->addTitleInfo($instArr,'appointmentTitle',$this->getAppointmentTitles());

        $instArr = $this->addTitleInfo($instArr,'medicalTitle',$this->getMedicalTitles());

        return $instArr;
    }
    public function addTitleInfo($instArr,$tablename,$titles): mixed
    {
        foreach( $titles as $title ) {
            $elementInfo = null;
            if( $title->getName() ) {
                $name = $title->getName()->getName()."";

//                //add missing "Position Type" values to user's profiles
//                $positionTypes = $title->getUserPositions();
//                $positionTypesArr = array();
//                foreach( $positionTypes as $positionType ) {
//                    $positionTypesArr[] = $positionType->getName();
//                }
//                $positionTypesStr = implode("; ",$positionTypesArr);

                $titleId = null;
                if( $title->getName()->getId() ) {
                    $titleId = $title->getName()->getId();
                }
                $elementInfo = array(
                    'tablename'=>$tablename,
                    'id'=>$titleId,'name'=>$name,
                    //'positiontypes'=>$positionTypesStr
                );
            }

            //$headInfo[] = 'break-br';

            $instId = 0;
            $institution = null;
            if( $title->getInstitution() ) {
                $institution = $title->getInstitution();
                $instId = $title->getInstitution()->getId();
            }

            if( array_key_exists($instId,$instArr) ) {
                //echo $instId." => instId already exists<br>";
            } else {
                //echo $instId." => instId does not exists<br>";
                $instArr[$instId]['instInfo'] = $this->getHeadInstitutionInfoArr($institution);
            }
            $instArr[$instId]['titleInfo'][] = $elementInfo;
            $instArr[$instId]['old'] = 1;
        }//foreach titles

        return $instArr;
    }
    public function getHeadInstitutionInfoArr($institution): array
    {

        //echo "inst=".$institution."<br>";
        //echo "count=".count($headInfo)."<br>";
        $pid = null;

        $headInfo = array();

        //service
        if( $institution ) {

            $institutionThis = $institution;
            //echo "inst=".$institutionThis."<br>";

            $name = $institutionThis->getName()."";
            $titleId = null;
            if( $institutionThis->getId() ) {
                $titleId = $institutionThis->getId();
            }
            $pid = null;
            $parent = $institutionThis->getParent();
            if( $parent && $parent->getId() ) {
                $pid = $parent->getId();
            }
            $elementInfo = array('tablename'=>'Institution','id'=>$titleId,'pid'=>$pid,'name'=>$name);
            $headInfo[] = $elementInfo;

        }

        //division
        if( $institution && $institution->getParent() ) {

            $institutionThis = $institution->getParent();
            //echo "inst=".$institutionThis."<br>";

            $name = $institutionThis->getName()."";
            $titleId = null;
            if( $institutionThis->getId() ) {
                $titleId = $institutionThis->getId();
            }
            $pid = null;
            $parent = $institutionThis->getParent();
            if( $parent && $parent->getId() ) {
                $pid = $parent->getId();
            }
            $elementInfo = array('tablename'=>'Institution','id'=>$titleId,'pid'=>$pid,'name'=>$name);
            $headInfo[] = $elementInfo;

        }

        //department
        if( $institution && $institution->getParent() && $institution->getParent()->getParent() ) {

            $institutionThis = $institution->getParent()->getParent();
            //echo "inst=".$institutionThis."<br>";

            $name = $institutionThis->getName()."";
            $titleId = null;
            if( $institutionThis->getId() ) {
                $titleId = $institutionThis->getId();
            }
            $pid = null;
            $parent = $institutionThis->getParent();
            if( $parent && $parent->getId() ) {
                $pid = $parent->getId();
            }
            $elementInfo = array('tablename'=>'Institution','id'=>$titleId,'pid'=>$pid,'name'=>$name);
            $headInfo[] = $elementInfo;

        }

        //institution
        if( $institution && $institution->getParent() && $institution->getParent()->getParent() && $institution->getParent()->getParent()->getParent() ) {

            $institutionThis = $institution->getParent()->getParent()->getParent();
            //echo "inst=".$institutionThis."<br>";

            $name = $institutionThis->getName()."";
            $titleId = null;
            if( $institutionThis->getId() ) {
                $titleId = $institutionThis->getId();
            }
            $pid = null;
            $parent = $institutionThis->getParent();
            if( $parent && $parent->getId() ) {
                $pid = $parent->getId();
            }
            $elementInfo = array('tablename'=>'Institution','id'=>$titleId,'pid'=>$pid,'name'=>$name);
            $headInfo[] = $elementInfo;

            //$headInfo[] = 'break-hr';
        }

        //$headInfo[] = 'break-hr';

        return $headInfo;
    }

    public function getUniqueTitles( $titles ): mixed
    {
        $titlesArr = new ArrayCollection();
        $titleNameIdsArr = array();
        foreach( $titles as $title ) {
            if( $title->getName() ) {
                $nameId = $title->getName()->getId();
                if (!in_array($nameId, $titleNameIdsArr)) {
                    $titleNameIdsArr[] = $nameId;
                    $titlesArr->add($title);
                }
            }
        }
        return $titlesArr;
    }
    public function getUniqueTitlesStr( $titles, $delimeter="; " ): ?string
    {
        $titlesArr = $this->getUniqueTitles($titles);
        $titleNameArr = array();
        foreach( $titlesArr as $title ) {
            $titleNameArr[] = $title->getName();
        }
        return implode($delimeter,$titleNameArr);
    }
    /////////////////////// EOF Return: Chief, Eyebrow Pathology ///////////////////////

    //TODO: create dynamic roles as in http://php-and-symfony.matthiasnoback.nl/2012/07/symfony2-security-creating-dynamic-roles-using-roleinterface/
    //ROLE_DEIDENTIFICATOR_USE: if one of the user role has DEIDENTIFICATOR site then create this role. It will solve security.yml problem
    
}