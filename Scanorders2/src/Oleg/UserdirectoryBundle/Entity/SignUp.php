<?php
/**
 * Created by PhpStorm.
 * User: ch3
 * Date: 4/3/2018
 * Time: 11:37 AM
 */

namespace Oleg\UserdirectoryBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="user_signup")
 * @ORM\HasLifecycleCallbacks
 */
class SignUp {

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="Oleg\UserdirectoryBundle\Entity\User")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     */
    private $user;

    /**
     * @ORM\ManyToOne(targetEntity="SiteList")
     * @ORM\JoinColumn(name="site_id", referencedColumnName="id", nullable=true)
     */
    private $site;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $createdate;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $updatedate;

    /**
     * @ORM\ManyToOne(targetEntity="Oleg\UserdirectoryBundle\Entity\User")
     * @ORM\JoinColumn(name="updatedby_id", referencedColumnName="id",nullable=true)
     */
    private $updatedby;

    ////////////// sign up details //////////////
    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $userName;

    /**
     * @ORM\Column(type="string", nullable=true)
     * @Assert\NotBlank(
     *     message = "The email value should not be blank."
     * )
     * @Assert\Email(
     *     message = "The email '{{ value }}' is not a valid email.",
     *     checkMX = true
     * )
     */
    private $email;
    ////////////// EOF sign up details //////////////


//    ////////////// more user details //////////////
//    /**
//     * @ORM\Column(type="string", nullable=true)
//     */
//    private $firstName;
//
//    /**
//     * @ORM\Column(type="string", nullable=true)
//     */
//    private $lastName;
//
//    /**
//     * @ORM\Column(type="string", nullable=true)
//     */
//    private $phone;
//
//    /**
//     * @ORM\ManyToOne(targetEntity="Institution")
//     */
//    private $institution;
//
//    /**
//     * @ORM\ManyToOne(targetEntity="AdminTitleList")
//     **/
//    private $administrativeTitle;
//    ////////////// EOF more user details //////////////


    ////////////// registration parameters //////////////
    /**
     * The salt to use for hashing.
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $salt;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $hashPassword;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $registrationLinkID;

    /**
     * Requested, Activation Email Sent, Activated
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $registrationStatus;

    /**
     * @ORM\Column(type="integer")
     */
    private $emailSentCounter;

    /**
     * Last Url, to use later when activate an account
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $lastUrl;
    ////////////// EOF registration parameters //////////////


    ////////////// tech parameters //////////////
    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $ip;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $useragent;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $width;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $height;
    ////////////// EOF tech parameters //////////////


    public function __construct() {
        $this->setEmailSentCounter(0);
        $this->setRegistrationStatus("Requested");
        $this->setCreatedate( new \DateTime() );
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     */
    public function setId($id)
    {
        $this->id = $id;
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
     * @return mixed
     */
    public function getSite()
    {
        return $this->site;
    }

    /**
     * @param mixed $site
     */
    public function setSite($site)
    {
        $this->site = $site;
    }

    /**
     * @return mixed
     */
    public function getCreatedate()
    {
        return $this->createdate;
    }

    /**
     * @param mixed $createdate
     */
    public function setCreatedate($createdate)
    {
        $this->createdate = $createdate;
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
    public function getUpdatedby()
    {
        return $this->updatedby;
    }

    /**
     * @param mixed $updatedby
     */
    public function setUpdatedby($updatedby)
    {
        $this->updatedby = $updatedby;
    }

    /**
     * @return mixed
     */
    public function getUserName()
    {
        return $this->userName;
    }

    /**
     * @param mixed $userName
     */
    public function setUserName($userName)
    {
        $this->userName = $userName;
    }

    /**
     * @return mixed
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param mixed $email
     */
    public function setEmail($email)
    {
        $this->email = $email;
    }



//    /**
//     * @return mixed
//     */
//    public function getFirstName()
//    {
//        return $this->firstName;
//    }
//
//    /**
//     * @param mixed $firstName
//     */
//    public function setFirstName($firstName)
//    {
//        $this->firstName = $firstName;
//    }
//
//    /**
//     * @return mixed
//     */
//    public function getLastName()
//    {
//        return $this->lastName;
//    }
//
//    /**
//     * @param mixed $lastName
//     */
//    public function setLastName($lastName)
//    {
//        $this->lastName = $lastName;
//    }
//
//    /**
//     * @return mixed
//     */
//    public function getPhone()
//    {
//        return $this->phone;
//    }
//
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
//    public function getInstitution()
//    {
//        return $this->institution;
//    }
//
//    /**
//     * @param mixed $institution
//     */
//    public function setInstitution($institution)
//    {
//        $this->institution = $institution;
//    }
//
//    /**
//     * @return mixed
//     */
//    public function getAdministrativeTitle()
//    {
//        return $this->administrativeTitle;
//    }
//
//    /**
//     * @param mixed $administrativeTitle
//     */
//    public function setAdministrativeTitle($administrativeTitle)
//    {
//        $this->administrativeTitle = $administrativeTitle;
//    }

    /**
     * @return mixed
     */
    public function getSalt()
    {
        return $this->salt;
    }

    /**
     * @param mixed $salt
     */
    public function setSalt($salt)
    {
        $this->salt = $salt;
    }

    /**
     * @return mixed
     */
    public function getHashPassword()
    {
        return $this->hashPassword;
    }

    /**
     * @param mixed $hashPassword
     */
    public function setHashPassword($hashPassword)
    {
        $this->hashPassword = $hashPassword;
    }

    /**
     * @return mixed
     */
    public function getRegistrationLinkID()
    {
        return $this->registrationLinkID;
    }

    /**
     * @param mixed $registrationLinkID
     */
    public function setRegistrationLinkID($registrationLinkID)
    {
        $this->registrationLinkID = $registrationLinkID;
    }

    /**
     * @return mixed
     */
    public function getRegistrationStatus()
    {
        return $this->registrationStatus;
    }

    /**
     * @param mixed $registrationStatus
     */
    public function setRegistrationStatus($registrationStatus)
    {
        $this->registrationStatus = $registrationStatus;
    }

    /**
     * @return mixed
     */
    public function getIp()
    {
        return $this->ip;
    }

    /**
     * @param mixed $ip
     */
    public function setIp($ip)
    {
        $this->ip = $ip;
    }

    /**
     * @return mixed
     */
    public function getUseragent()
    {
        return $this->useragent;
    }

    /**
     * @param mixed $useragent
     */
    public function setUseragent($useragent)
    {
        $this->useragent = $useragent;
    }

    /**
     * @return mixed
     */
    public function getWidth()
    {
        return $this->width;
    }

    /**
     * @param mixed $width
     */
    public function setWidth($width)
    {
        $this->width = $width;
    }

    /**
     * @return mixed
     */
    public function getHeight()
    {
        return $this->height;
    }

    /**
     * @param mixed $height
     */
    public function setHeight($height)
    {
        $this->height = $height;
    }

    /**
     * @return mixed
     */
    public function getLastUrl()
    {
        return $this->lastUrl;
    }

    /**
     * @param mixed $lastUrl
     */
    public function setLastUrl($lastUrl)
    {
        $this->lastUrl = $lastUrl;
    }

    

    /**
     * @return mixed
     */
    public function getEmailSentCounter()
    {
        return $this->emailSentCounter;
    }

    /**
     * @param mixed $emailSentCounter
     */
    public function setEmailSentCounter($emailSentCounter)
    {
        $this->emailSentCounter = $emailSentCounter;
    }

    public function incrementEmailSentCounter() {
        $counter = $this->getEmailSentCounter();
        $counter = $counter + 1;
        $this->setEmailSentCounter($counter);
    }

    public function __toString() {
        $userStr = null;
        if( $this->getUser() ) {
            $userStr = "user=".$this->getUser().", ";
        }
        return "Sign Up Request: ".$userStr."ID=".$this->getId().", site=".$this->getSite().", username=".$this->getUserName().", email=".$this->getEmail().", emailSentCounter=".$this->getEmailSentCounter();
    }

}