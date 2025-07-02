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

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\ArrayCollection;

#[ORM\Table(name: 'user_accountrequest')]
#[ORM\Entity(repositoryClass: 'App\UserdirectoryBundle\Repository\UserRequestRepository')]
#[ORM\HasLifecycleCallbacks]
class UserRequest
{
    
    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    private $id;

    #[ORM\Column(type: 'string', nullable: true)]
    private $cwid;

    #[ORM\Column(type: 'string', nullable: true)]
    private $username;

    #[ORM\Column(type: 'string', nullable: true)]
    private $hascwid;

    /**
     * Last Name
     */
    #[ORM\Column(type: 'string', nullable: true)]
    private $name;

    /**
     * First Name
     */
    #[ORM\Column(type: 'string', nullable: true)]
    private $firstName;

    #[ORM\Column(type: 'string')]
    #[Assert\NotBlank(message: 'The email value should not be blank.')]
    #[Assert\Email(message: "The email '{{ value }}' is not a valid email.")]
    private $email;
    
    #[ORM\Column(type: 'string', nullable: true)]
    private $phone;

    ///////////////// mobilePhone /////////////////////
    #[ORM\Column(type: 'string', nullable: true)]
    private $mobilePhone;

    #[ORM\Column(type: 'string', nullable: true)]
    private $mobilePhoneVerifyCode;

    /**
     * mobilePhoneVerifyCode generation Date. Used for expiration date.
     */
    #[ORM\Column(type: 'datetime', nullable: true)]
    private $mobilePhoneVerifyCodeDate;

    /**
     * Is the mobile phone number verified?
     */
    #[ORM\Column(type: 'boolean', nullable: true)]
    private $mobilePhoneVerified;
    ///////////////// EOF mobilePhone ///////////////////////
    #[ORM\Column(type: 'string', nullable: true)]
    private $job;

    /**
     * request permittedInstitutionalPHIScope
     */
    #[ORM\JoinTable(name: 'user_accountrequest_institution')]
    #[ORM\JoinColumn(name: 'request_id', referencedColumnName: 'id')]
    #[ORM\InverseJoinColumn(name: 'institution_id', referencedColumnName: 'id')]
    #[ORM\ManyToMany(targetEntity: 'App\UserdirectoryBundle\Entity\Institution')]
    private $requestedInstitutionalPHIScope;

    /**
     * requested Institution (ScanOrders Institution Scope)
     **/
    #[ORM\ManyToOne(targetEntity: 'App\UserdirectoryBundle\Entity\Institution')]
    #[ORM\JoinColumn(name: 'institution_id', referencedColumnName: 'id')]
    private $requestedScanOrderInstitutionScope;

    //Free text field accepts new values.
    //The new value does not have to be recorded back into
    // the Organizational Group hierarchy,
    // but do save it with the rest of
    // the “Request account” variables submitted
    // on this page as a free text typed value
    #[ORM\Column(type: 'string', nullable: true)]
    private $requestedInstitutionScope;
    
    #[ORM\Column(type: 'text', nullable: true)]
    private $request;

    #[ORM\Column(type: 'text', nullable: true)]
    private $similaruser;

    #[ORM\Column(type: 'string', nullable: true)]
    private $primaryService;

    #[ORM\Column(type: 'string', nullable: true)]
    private $status;

    /**
     * @var \DateTime
     */
    #[ORM\Column(type: 'datetime', nullable: true)]
    private $creationdate;

    /**
     * @var \DateTime
     */
    #[ORM\Column(type: 'datetime', nullable: true)]
    private $actiondate;

    #[ORM\Column(type: 'string', nullable: true)]
    private $referencename;

    #[ORM\Column(type: 'string', nullable: true)]
    private $referenceemail;

    #[ORM\Column(type: 'string', nullable: true)]
    private $referencephone;

    //TODO: change it to source-systems (SourceSystemList)
    //    /**
    //     * Systems for account requests: System for which the account is being requested
    //     * @ORM\ManyToOne(targetEntity="SystemAccountRequestType")
    //     **/
    //    private $systemAccountRequest;
    /**
     * Systems for account requests: System for which the account is being requested
     **/
    #[ORM\ManyToOne(targetEntity: 'SourceSystemList')]
    private $systemAccountRequest;

    #[ORM\Column(type: 'string', nullable: true)]
    private $siteName;

    /**
     * @var array
     */
    #[ORM\Column(type: 'array', nullable: true)]
    private $roles = array();



    function __construct()
    {
        //$this->services = new ArrayCollection();
        $this->requestedInstitutionalPHIScope = new ArrayCollection();
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

    public function setId($id)
    {
        $this->id = $id;
        return $id;
    }

    /**
     * Set cwid
     *
     * @param string $cwid
     */
    public function setCwid($cwid)
    {
        $this->cwid = $cwid;
    
        return $this;
    }

    /**
     * Get cwid
     *
     * @return string 
     */
    public function getCwid()
    {
        return $this->cwid;
    }

    /**
     * Set request
     *
     * @param string $request
     */
    public function setRequest($request)
    {
        $this->request = $request;
    
        return $this;
    }

    /**
     * Get request
     *
     * @return string 
     */
    public function getRequest()
    {
        return $this->request;
    }

    public function getName() {
        return $this->name;
    }

    public function getEmail() {
        return $this->email;
    }

    public function getPhone() {
        return $this->phone;
    }

    public function getJob() {
        return $this->job;
    }

    public function setName($name) {
        $this->name = $name;
    }

    public function setEmail($email) {
        $this->email = $email;
    }

    public function setPhone($phone) {
        $this->phone = $phone;
    }

    public function setJob($job) {
        $this->job = $job;
    }

    public function getStatus() {
        return $this->status;
    }

    public function setStatus($status) {
        $this->status = $status;
    }

    #[ORM\PrePersist]
    public function setCreationdate()
    {
        $this->creationdate = new \DateTime();
    }

    public function getCreationdate()
    {
        return $this->creationdate;
    }


//    public function addServices(\App\UserdirectoryBundle\Entity\Service $service)
//    {
//        if( !$this->services->contains($service) ) {
//            $this->services->add($service);
//        }
//
//        return $this;
//    }
//
//    public function removeServices(\App\UserdirectoryBundle\Entity\Service $service)
//    {
//        $this->services->removeElement($service);
//    }
//
//    /**
//     * @param mixed $services
//     */
//    public function setServices($services)
//    {
//        if( $services->first() ) {
//            $this->primaryService = $services->first()->getId();
//        } else {
//            $this->primaryService = NULL;
//        }
//        $this->services = $services;
//    }
//
//    /**
//     * @return mixed
//     */
//    public function getServices()
//    {
//
//        $resArr = new ArrayCollection();
//        foreach( $this->services as $service ) {
//            if( $service->getId()."" == $this->getPrimaryService()."" ) {
//                //$resArr->removeElement($service);
//                //$resArr->first();
//                if( count($this->services) > 1 ) {
//                    $firstEl = $resArr->get(0);
//                    $resArr->set(0,$service);
//                    $resArr->add($firstEl);
//                } else {
//                    $resArr->add($service);
//                }
//            } else {
//                $resArr->add($service);
//            }
//        }
//        return $resArr;
//    }

    /**
     * @param mixed $primaryService
     */
    public function setPrimaryService($primaryService)
    {
        $this->primaryService = $primaryService;
    }

    /**
     * @return mixed
     */
    public function getPrimaryService()
    {
        return $this->primaryService;
    }

    /**
     * @param mixed $referenceemail
     */
    public function setReferenceemail($referenceemail)
    {
        $this->referenceemail = $referenceemail;
    }

    /**
     * @return mixed
     */
    public function getReferenceemail()
    {
        return $this->referenceemail;
    }

    /**
     * @param mixed $referencename
     */
    public function setReferencename($referencename)
    {
        $this->referencename = $referencename;
    }

    /**
     * @return mixed
     */
    public function getReferencename()
    {
        return $this->referencename;
    }

    /**
     * @param mixed $referencephone
     */
    public function setReferencephone($referencephone)
    {
        $this->referencephone = $referencephone;
    }

    /**
     * @return mixed
     */
    public function getReferencephone()
    {
        return $this->referencephone;
    }

    /**
     * @param mixed $similaruser
     */
    public function setSimilaruser($similaruser)
    {
        $this->similaruser = $similaruser;
    }

    /**
     * @return mixed
     */
    public function getSimilaruser()
    {
        return $this->similaruser;
    }

    /**
     * @param mixed $hascwid
     */
    public function setHascwid($hascwid)
    {
        $this->hascwid = $hascwid;
    }

    /**
     * @return mixed
     */
    public function getHascwid()
    {
        return $this->hascwid;
    }


    /**
     * @return mixed
     */
    public function getRequestedInstitutionalPHIScope()
    {
        return $this->requestedInstitutionalPHIScope;
    }

    public function setRequestedInstitutionalPHIScope( $requestedInstitutionalPHIScope )
    {
        $this->requestedInstitutionalPHIScope->clear();
        foreach( $requestedInstitutionalPHIScope as $institution ) {
            $this->addRequestedInstitutionalPHIScope($institution);
        }
        return $this->requestedInstitutionalPHIScope;
    }

    public function addRequestedInstitutionalPHIScope(\App\UserdirectoryBundle\Entity\Institution $institution)
    {
        if( !$this->requestedInstitutionalPHIScope->contains($institution) ) {
            $this->requestedInstitutionalPHIScope->add($institution);
        }
        return $this;
    }

    public function removeRequestedInstitutionalPHIScope(\App\UserdirectoryBundle\Entity\Institution $institution)
    {
        $this->requestedInstitutionalPHIScope->removeElement($institution);
    }

    /**
     * @param mixed $requestedScanOrderInstitutionScope
     */
    public function setRequestedScanOrderInstitutionScope($requestedScanOrderInstitutionScope)
    {
        $this->requestedScanOrderInstitutionScope = $requestedScanOrderInstitutionScope;
    }

    /**
     * @return mixed
     */
    public function getRequestedScanOrderInstitutionScope()
    {
        return $this->requestedScanOrderInstitutionScope;
    }

    /**
     * @return mixed
     */
    public function getRequestedInstitutionScope()
    {
        return $this->requestedInstitutionScope;
    }

    /**
     * @param mixed $requestedInstitutionScope
     */
    public function setRequestedInstitutionScope($requestedInstitutionScope)
    {
        $this->requestedInstitutionScope = $requestedInstitutionScope;
    }



    /**
     * @param \DateTime $actiondate
     */
    #[ORM\PreUpdate]
    public function setActiondate()
    {
        $this->actiondate = new \DateTime();
    }

    /**
     * @return \DateTime
     */
    public function getActiondate()
    {
        return $this->actiondate;
    }

    /**
     * @param mixed $username
     */
    public function setUsername($username)
    {
        $this->username = $username;
    }

    /**
     * @return mixed
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * @return mixed
     */
    public function getFirstName()
    {
        return $this->firstName;
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
    public function getSystemAccountRequest()
    {
        return $this->systemAccountRequest;
    }

    /**
     * @param mixed $systemAccountRequest
     */
    public function setSystemAccountRequest($systemAccountRequest)
    {
        $this->systemAccountRequest = $systemAccountRequest;
    }


    /**
     * @param array $roles
     */
    public function setRoles($roles)
    {
        if( $roles ) {
            foreach( $roles as $role ) {
                $this->addRole($role."");
            }
        }

    }

    /**
     * @return array
     */
    public function getRoles()
    {
        return $this->roles;
    }

    public function addRole($role) {
        $this->roles[] = $role;
        return $this;
    }

    public function hasRole($role)
    {
        return in_array(strtoupper($role), $this->roles, true);
    }

    /**
     * @return mixed
     */
    public function getSiteName()
    {
        return $this->siteName;
    }

    /**
     * @param mixed $siteName
     */
    public function setSiteName($siteName)
    {
        $this->siteName = $siteName;
    }

    /**
     * @return mixed
     */
    public function getMobilePhone()
    {
        return $this->mobilePhone;
    }

    /**
     * @param mixed $mobilePhone
     */
    public function setMobilePhone($mobilePhone)
    {
        if( $mobilePhone ) {
            //strip '-' and ' '
            $mobilePhone = str_replace('-','',$mobilePhone);
            $mobilePhone = str_replace(' ','',$mobilePhone);
        }
        
        if( $this->mobilePhone != $mobilePhone ) {
//            $this->setMobilePhoneVerified(false);
//            $this->setMobilePhoneVerifyCode(NULL);
//            $this->setMobilePhoneVerifyCodeDate(NULL);
            $this->setUnVerified();
        }

        $this->mobilePhone = $mobilePhone;
    }

    /**
     * @return mixed
     */
    public function getMobilePhoneVerifyCode()
    {
        return $this->mobilePhoneVerifyCode;
    }

    /**
     * @param mixed $mobilePhoneVerifyCode
     */
    public function setMobilePhoneVerifyCode($mobilePhoneVerifyCode)
    {
        $this->mobilePhoneVerifyCode = $mobilePhoneVerifyCode;
    }

    /**
     * @return mixed
     */
    public function getMobilePhoneVerifyCodeDate()
    {
        return $this->mobilePhoneVerifyCodeDate;
    }

    /**
     * @param mixed $mobilePhoneVerifyCodeDate
     */
    public function setMobilePhoneVerifyCodeDate($mobilePhoneVerifyCodeDate)
    {
        $this->mobilePhoneVerifyCodeDate = $mobilePhoneVerifyCodeDate;
    }

    //Link methods for UserInfo
    public function getPreferredMobilePhoneVerified()
    {
        return $this->getMobilePhoneVerified();
    }
    public function getPreferredMobilePhone()
    {
        return $this->getMobilePhone();
    }

    public function verifyCode($verificationCode) {
        $userVerificationCode = $this->getMobilePhoneVerifyCode();
        $phoneNumber = $this->getMobilePhone();
        $notExpired = $this->verificationCodeIsNotExpired();
        if( $notExpired && $phoneNumber && $userVerificationCode && $verificationCode && $userVerificationCode == $verificationCode ) {
            //OK
//            $this->setMobilePhoneVerified(NULL);
//            $this->setMobilePhoneVerifyCodeDate(NULL);
//            $this->setMobilePhoneVerifyCode(true);
            $this->setVerified();

            return true;
        }

        return false;
    }
    public function setVerified() {
        $this->setMobilePhoneVerified(true);
        $this->setMobilePhoneVerifyCodeDate(NULL);
        $this->setMobilePhoneVerifyCode(NULL);
    }
    public function setUnVerified() {
        $this->setMobilePhoneVerified(false);
        $this->setMobilePhoneVerifyCodeDate(NULL);
        $this->setMobilePhoneVerifyCode(NULL);
    }
    public function verificationCodeIsNotExpired() {
        $expireDate = new \DateTime();
        $expireDate->modify("-2 day");
        $verificationCodeCreationDate = $this->getMobilePhoneVerifyCodeDate();
        if( !$verificationCodeCreationDate ) {
            return true;
        }

        if( $verificationCodeCreationDate >= $expireDate ) {
            return true;
        }

        return false;
    }

    /**
     * @return mixed
     */
    public function getMobilePhoneVerified()
    {
        return $this->mobilePhoneVerified;
    }

    /**
     * @param mixed $mobilePhoneVerified
     */
    public function setMobilePhoneVerified($mobilePhoneVerified)
    {
        $this->mobilePhoneVerified = $mobilePhoneVerified;
    }


    
}