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

/**
 * Created by PhpStorm.
 * User: DevServer
 * Date: 1/26/15
 * Time: 1:35 PM
 */

namespace App\UserdirectoryBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="user_userInfo",
 * indexes={
 *      @ORM\Index( name="middleName_idx", columns={"middleName"} ),
 *      @ORM\Index( name="firstName_idx", columns={"firstName"} ),
 *      @ORM\Index( name="lastName_idx", columns={"lastName"} ),
 *      @ORM\Index( name="displayName_idx", columns={"displayName"} ),
 *      @ORM\Index( name="email_idx", columns={"email"} )
 *  }
 * )
 */
class UserInfo extends BaseUserAttributes {

    /**
     * @ORM\ManyToOne(targetEntity="User", inversedBy="infos")
     */
    private $user;

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
     * @ORM\Column(name="preferredMobilePhone", type="string", nullable=true)
     */
    private $preferredMobilePhone;

    /**
     * @ORM\Column(name="mobilePhoneVerifyCode", type="string", nullable=true)
     */
    private $mobilePhoneVerifyCode;

    /**
     * mobilePhoneVerifyCode generation Date. Used for expiration date.
     *
     * @ORM\Column(name="mobilePhoneVerifyCodeDate", type="datetime", nullable=true)
     */
    private $mobilePhoneVerifyCodeDate;

    /**
     * Is the mobile phone number verified?
     *
     * @ORM\Column(name="preferredMobilePhoneVerified", type="boolean", nullable=true)
     */
    private $preferredMobilePhoneVerified;

    /**
     * @ORM\Column(name="email", type="string", nullable=true)
     */
    private $email;

    /**
     * @ORM\Column(name="emailCanonical", type="string", nullable=true)
     */
    private $emailCanonical;

    /**
     * @ORM\Column(name="initials", type="string", nullable=true)
     */
    private $initials;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $salutation;



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
     * @return mixed
     */
    public function getPreferredMobilePhone()
    {
        return $this->preferredMobilePhone;
    }

    /**
     * @param mixed $preferredMobilePhone
     */
    public function setPreferredMobilePhone($preferredMobilePhone)
    {
        if( $this->preferredMobilePhone != $preferredMobilePhone ) {
            //$this->setPreferredMobilePhoneVerified(false);
            //$this->setMobilePhoneVerifyCode(NULL);
            //$this->setMobilePhoneVerifyCodeDate(NULL);
            $this->setUnVerified();
        }

        $this->preferredMobilePhone = $preferredMobilePhone;
    }

    /**
     * @return mixed
     */
    public function getPreferredMobilePhoneVerified()
    {
        return $this->preferredMobilePhoneVerified;
    }

    /**
     * @param mixed $preferredMobilePhoneVerified
     */
    public function setPreferredMobilePhoneVerified($preferredMobilePhoneVerified)
    {
        $this->preferredMobilePhoneVerified = $preferredMobilePhoneVerified;
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

    public function verifyCode($verificationCode) {
        $userVerificationCode = $this->getMobilePhoneVerifyCode();
        $phoneNumber = $this->getPreferredMobilePhone();
        $notExpired = $this->verificationCodeIsNotExpired();
        if( $notExpired && $phoneNumber && $userVerificationCode && $verificationCode && $userVerificationCode == $verificationCode ) {
            //OK
//            $this->setMobilePhoneVerifyCode(NULL);
//            $this->setMobilePhoneVerifyCodeDate(NULL);
//            $this->setPreferredMobilePhoneVerified(true);
            $this->setVerified();

            return $phoneNumber;
        }

        return false;
    }
    public function setVerified() {
        $this->setMobilePhoneVerifyCode(NULL);
        $this->setMobilePhoneVerifyCodeDate(NULL);
        $this->setPreferredMobilePhoneVerified(true);
    }
    public function setUnVerified() {
        $this->setMobilePhoneVerifyCode(NULL);
        $this->setMobilePhoneVerifyCodeDate(NULL);
        $this->setPreferredMobilePhoneVerified(false);
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
     * @param mixed $email
     */
    public function setEmail($email)
    {
        $this->email = $email;

        if( $email ) {
            $this->setEmailCanonical($email);
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
     * @param mixed $emailCanonical
     */
    public function setEmailCanonical($emailCanonical)
    {
        $this->emailCanonical = $emailCanonical;
    }

    /**
     * @return mixed
     */
    public function getEmailCanonical()
    {
        return $this->emailCanonical;
    }

    /**
     * @param mixed $salutation
     */
    public function setSalutation($salutation)
    {
        $this->salutation = $salutation;
    }

    /**
     * @return mixed
     */
    public function getSalutation()
    {
        return $this->salutation;
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



    public function __toString() {
        return "UserInfo";
    }

} 