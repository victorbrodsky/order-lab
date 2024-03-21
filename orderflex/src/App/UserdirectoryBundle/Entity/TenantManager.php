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

//Ldap access request. Can be used for different sites with unique siteName

namespace App\UserdirectoryBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Table(name: 'user_tenantmanager')]
#[ORM\Entity]
class TenantManager
{

    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    private $id;

    #[ORM\ManyToOne(targetEntity: 'App\UserdirectoryBundle\Entity\User')]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id')]
    private $user;

    /**
     * @var \DateTime
     */
    #[ORM\Column(type: 'datetime', nullable: true)]
    private $createdate;

    /**
     * @var \DateTime
     */
    #[ORM\Column(type: 'datetime', nullable: true)]
    private $updatedate;

    #[ORM\ManyToOne(targetEntity: 'App\UserdirectoryBundle\Entity\User')]
    #[ORM\JoinColumn(name: 'updatedby_id', referencedColumnName: 'id', nullable: true)]
    private $updatedby;

    //The homepage of the 'TenantManager' has:
    // * Header Image : [DropZone field allowing upload of 1 image]


    // * ListOfHostedTenants as a List of hosted tenants, each one shown as a clickable link
    #[ORM\OneToMany(targetEntity: TenantList::class, mappedBy: 'tenantManager', cascade: ['persist', 'remove'])]
    private $tenants;


    // * Greeting Text : [free text form field, multi-line, accepts HTML, with default value:
    //  “Welcome to the View! The following organizations are hosted on this platform:”]
    #[ORM\Column(type: 'text', nullable: true)]
    private $greeting;

    // * Main text [free text form field, multi-line, accepts HTML, with default value: “Please log in to manage the tenants on this platform.”]
    #[ORM\Column(type: 'text', nullable: true)]
    private $maintext;

    // * Footer [free text form field, multi-line, accepts HTML, with default value: “[Home | <a href=”/about-us”>About Us</a> | Follow Us]”
    #[ORM\Column(type: 'text', nullable: true)]
    private $footer;


    public function __construct() {
        $this->setStatus(self::STATUS_ACTIVE);
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
    public function getId()
    {
        return $this->id;
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
    public function getUser()
    {
        return $this->user;
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
    public function getSiteName()
    {
        return $this->siteName;
    }

    /**
     * @param \DateTime $updatedate
     */
    #[ORM\PreUpdate]
    public function setUpdatedate()
    {
        $this->updatedate = new \DateTime();
    }

    /**
     * @return \DateTime
     */
    public function getUpdatedate()
    {
        return $this->updatedate;
    }

    /**
     * @param \DateTime $createdate
     */
    #[ORM\PrePersist]
    public function setCreatedate()
    {
        $this->createdate = new \DateTime();
    }

    /**
     * @return \DateTime
     */
    public function getCreatedate()
    {
        return $this->createdate;
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
    public function getUpdatedby()
    {
        return $this->updatedby;
    }

    public function getUpdateAuthorRoles()
    {
        return $this->updateAuthorRoles;
    }


    public function setUpdateAuthorRoles($roles) {
        foreach( $roles as $role ) {
            $this->addUpdateAuthorRole($role."");
        }
    }

    public function addUpdateAuthorRole($role) {
        $role = strtoupper($role);
        if( !in_array($role, $this->updateAuthorRoles, true) ) {
            $this->updateAuthorRoles[] = $role;
        }
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
    public function getLastName()
    {
        return $this->lastName;
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
    public function getPhone()
    {
        return $this->phone;
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
    public function getJob()
    {
        return $this->job;
    }

    /**
     * @param mixed $job
     */
    public function setJob($job)
    {
        $this->job = $job;
    }

    /**
     * @return mixed
     */
    public function getOrganizationalGroup()
    {
        return $this->organizationalGroup;
    }

    /**
     * @param mixed $organizationalGroup
     */
    public function setOrganizationalGroup($organizationalGroup)
    {
        $this->organizationalGroup = $organizationalGroup;
    }

    /**
     * @return mixed
     */
    public function getReason()
    {
        return $this->reason;
    }

    /**
     * @param mixed $reason
     */
    public function setReason($reason)
    {
        $this->reason = $reason;
    }

    /**
     * @return mixed
     */
    public function getSimilaruser()
    {
        return $this->similaruser;
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
    public function getReferencename()
    {
        return $this->referencename;
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
    public function getReferenceemail()
    {
        return $this->referenceemail;
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
    public function getReferencephone()
    {
        return $this->referencephone;
    }

    /**
     * @param mixed $referencephone
     */
    public function setReferencephone($referencephone)
    {
        $this->referencephone = $referencephone;
    }



    /**
     * @param mixed $status
     */
    public function setStatus($status)
    {
        $this->status = $status;
    }

    /**
     * @return mixed
     */
    public function getStatus()
    {
        return $this->status;
    }

    public function getStatusStr()
    {
        $str = "";

        if( $this->getStatus() == self::STATUS_ACTIVE )
            $str = "Active";

        if( $this->getStatus() == self::STATUS_DECLINED )
            $str = "Declined";

        if( $this->getStatus() == self::STATUS_APPROVED )
            $str = "Approved";

        return $str;
    }

    /////////////// Mobile Phone /////////////
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

    public function updateUserMobilePhoneByAccessRequest() {
        $user = $this->getUser();
        $userInfo = $user->getUserInfo();
        $accessRequestMobilePhone = $this->getMobilePhone();
        if( $accessRequestMobilePhone != $userInfo->getPreferredMobilePhone() ) {
            $userInfo->setPreferredMobilePhone($accessRequestMobilePhone);
        }
        $accessRequestMobilePhoneVerifyCode = $this->getMobilePhoneVerifyCode();
        if( $accessRequestMobilePhoneVerifyCode != $userInfo->getMobilePhoneVerifyCode() ) {
            $userInfo->setMobilePhoneVerifyCode($accessRequestMobilePhoneVerifyCode);
        }
        $accessRequestMobilePhoneVerifyCodeDate = $this->getMobilePhoneVerifyCodeDate();
        if( $accessRequestMobilePhoneVerifyCodeDate != $userInfo->getMobilePhoneVerifyCodeDate() ) {
            $userInfo->setMobilePhoneVerifyCodeDate($accessRequestMobilePhoneVerifyCodeDate);
        }
        $accessRequestMobilePhoneVerified = $this->getMobilePhoneVerified();
        if( $accessRequestMobilePhoneVerified != $userInfo->getPreferredMobilePhoneVerified() ) {
            $userInfo->setPreferredMobilePhoneVerified($accessRequestMobilePhoneVerified);
        }
    }
    /////////////// EOF Mobile Phone /////////////


}