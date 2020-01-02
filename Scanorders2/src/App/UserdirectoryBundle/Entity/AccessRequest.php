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

namespace Oleg\UserdirectoryBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity()
 * @ORM\HasLifecycleCallbacks
 * @ORM\Table(
 *  name="user_accessrequest",
 *  indexes={
 *      @ORM\Index( name="user_idx", columns={"user_id"} ),
 *      @ORM\Index( name="status_idx", columns={"status"} ),
 *      @ORM\Index( name="siteName_idx", columns={"siteName"} )
 *  }
 * )
 */
class AccessRequest
{

    const STATUS_ACTIVE = 0;
    const STATUS_DECLINED = 1;
    const STATUS_APPROVED = 2;

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
     * @ORM\Column(name="siteName", type="string")
     */
    private $siteName;

    /**
     * @ORM\Column(name="status", type="integer", options={"default" = 0})
     */
    private $status;

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

    /**
     * @var array
     * @ORM\Column(type="array", nullable=true)
     */
    private $updateAuthorRoles = array();

    /////////////////////// access request details //////////////////////////
    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $firstName;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $lastName;

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

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $phone;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $job;

    /**
     * Organizational Group
     *
     * @ORM\ManyToOne(targetEntity="Institution")
     * @ORM\JoinColumn(name="institution_id", referencedColumnName="id", nullable=true)
     **/
    private $organizationalGroup;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $reason;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $similaruser;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $referencename;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $referenceemail;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $referencephone;



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
     * @ORM\PreUpdate
     */
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
     * @ORM\PrePersist
     */
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

}