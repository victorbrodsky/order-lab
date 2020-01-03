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
 * Created by JetBrains PhpStorm.
 * User: oli2002
 * Date: 4/22/14
 * Time: 10:19 AM
 * To change this template use File | Settings | File Templates.
 */

namespace App\UserdirectoryBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity(repositoryClass="App\UserdirectoryBundle\Repository\UserWrapperRepository")
 * @ORM\Table(name="user_userWrapper")
 */
class UserWrapper extends ListAbstract {

    /**
     * @ORM\OneToMany(targetEntity="UserWrapper", mappedBy="original", cascade={"persist"})
     **/
    protected $synonyms;

    /**
     * @ORM\ManyToOne(targetEntity="UserWrapper", inversedBy="synonyms", cascade={"persist"})
     * @ORM\JoinColumn(name="original_id", referencedColumnName="id", nullable=true)
     **/
    protected $original;


//    /**
//     * @ORM\Id
//     * @ORM\Column(type="integer")
//     * @ORM\GeneratedValue(strategy="AUTO")
//     */
//    private $id;

//    /**
//     * must be synchronised with name in ListAbstract
//     *
//     * @ORM\Column(type="string", nullable=true)
//     */
//    private $userStr;
    //use name in ListAbstract as userStr

    /**
     * User object
     * @ORM\ManyToOne(targetEntity="User")
     */
    private $user;


    //Phone Number: [free text]
    /**
     * @ORM\Column(name="phone", type="string", nullable=true)
     */
    private $userWrapperPhone;

    //E-Mail: [free text]
    /**
     * @ORM\Column(name="email", type="string", nullable=true)
     */
    private $userWrapperEmail;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $userWrapperFirstName;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $userWrapperLastName;

    //Specialty: [link to the platform list manager's specialty list items here
    // http://collage.med.cornell.edu/order/directory/admin/list-manager/id/69 - allow more than one]
    /**
     * @ORM\ManyToOne(targetEntity="App\UserdirectoryBundle\Entity\HealthcareProviderSpecialtiesList", cascade={"persist","remove"})
     */
    private $userWrapperSpecialty;

    //Source Site: [ID/name of the O R D E R site used to create this particular instance of the user wrapper object;
    // for user wrappers created on the Call Log Book, this would have the ID of the Call Log Book]
    /**
     * @ORM\ManyToOne(targetEntity="SourceSystemList")
     */
    private $userWrapperSource;



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
     * @return mixed
     */
    public function getUserWrapperPhone()
    {
        return $this->userWrapperPhone;
    }

    /**
     * @param mixed $userWrapperPhone
     */
    public function setUserWrapperPhone($userWrapperPhone)
    {
        $this->userWrapperPhone = $userWrapperPhone;
    }

    /**
     * @return mixed
     */
    public function getUserWrapperEmail()
    {
        return $this->userWrapperEmail;
    }

    /**
     * @param mixed $userWrapperEmail
     */
    public function setUserWrapperEmail($userWrapperEmail)
    {
        $this->userWrapperEmail = $userWrapperEmail;
    }

    /**
     * @return mixed
     */
    public function getUserWrapperSpecialty()
    {
        return $this->userWrapperSpecialty;
    }

    /**
     * @param mixed $userWrapperSpecialty
     */
    public function setUserWrapperSpecialty($userWrapperSpecialty)
    {
        $this->userWrapperSpecialty = $userWrapperSpecialty;
    }

    /**
     * @return mixed
     */
    public function getUserWrapperSource()
    {
        return $this->userWrapperSource;
    }

    /**
     * @param mixed $userWrapperSource
     */
    public function setUserWrapperSource($userWrapperSource)
    {
        $this->userWrapperSource = $userWrapperSource;
    }

    /**
     * @return mixed
     */
    public function getUserWrapperFirstName()
    {
        return $this->userWrapperFirstName;
    }

    /**
     * @param mixed $userWrapperFirstName
     */
    public function setUserWrapperFirstName($userWrapperFirstName)
    {
        $this->userWrapperFirstName = $userWrapperFirstName;
    }

    /**
     * @return mixed
     */
    public function getUserWrapperLastName()
    {
        return $this->userWrapperLastName;
    }

    /**
     * @param mixed $userWrapperLastName
     */
    public function setUserWrapperLastName($userWrapperLastName)
    {
        $this->userWrapperLastName = $userWrapperLastName;
    }

    /**
     * @param mixed $userStr
     */
    public function setUserStr($userStr)
    {
        //$this->userStr = $userStr;
        $this->setName($userStr);
    }

    /**
     * @return mixed
     */
    public function getUserStr()
    {
        //return $this->userStr;
        return $this->getName();
    }

    public function __toString() {
        return $this->getFullName();
    }

    public function getFullName() {
        $fullName = "";

        if( $this->getUser() ) {
            $fullName = $fullName . $this->getUser()."";
            return $fullName;
        }

        if( $this->getName() ) {
            if( $fullName ) {
                $fullName = $fullName . " " .$this->getName()."";
            } else {
                $fullName = $this->getName()."";
            }
        }

        //echo "fullName=".$fullName."<br>";
        return $fullName;
    }

    public function getFullNameWithDetails() {
        $fullNameArr = array();

        //$fullNameArr[] = $this->getFullName();
        if( $this->getUser() ) {
            $fullNameArr[] = $this->getUser()."";
        }

        if( $this->getName() ) {
            $fullNameArr[] = $this->getName();
        }

        if( $this->getUserWrapperSource() ) {
            $fullNameArr[] = $this->getUserWrapperSource();
        }

        //echo "fullName=".$fullName."<br>";
        return implode("; ",$fullNameArr);
    }

    //get user id, wrapper id or user string
    //used for transformer
    public function getEntity() {

        $user = $this->getUser();
        if( $user ) {
            return $user;
        }

        if( $this->getId() ) {
            return $this->getId();
        }

        return $this->getFullName();
    }


}