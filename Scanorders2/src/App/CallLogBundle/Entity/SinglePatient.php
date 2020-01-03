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
 * Date: 8/24/15
 * Time: 11:08 AM
 */

namespace App\CallLogBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

//Not mapped patient object

class SinglePatient {

//    /**
//     * @ORM\ManyToOne(targetEntity="App\OrderformBundle\Entity\MrnType")
//     * @ORM\JoinColumn(name="keytype_id", referencedColumnName="id", nullable=true)
//     */
//    private $keytype;
    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $keytype;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $mrn;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $lastname;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $firstname;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $middlename;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $suffix;

    /**
     * @ORM\ManyToOne(targetEntity="App\UserdirectoryBundle\Entity\SexList")
     * @ORM\JoinColumn(name="sex_id", referencedColumnName="id", nullable=true)
     */
    private $gender;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    protected $dob;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $phone;
    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $email;


    /**
     * @return mixed
     */
    public function getKeytype()
    {
        return $this->keytype;
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
    public function getMrn()
    {
        return $this->mrn;
    }

    /**
     * @param mixed $mrn
     */
    public function setMrn($mrn)
    {
        $this->mrn = $mrn;
    }

    /**
     * @return mixed
     */
    public function getLastname()
    {
        return $this->lastname;
    }

    /**
     * @param mixed $lastname
     */
    public function setLastname($lastname)
    {
        $this->lastname = $lastname;
    }

    /**
     * @return mixed
     */
    public function getFirstname()
    {
        return $this->firstname;
    }

    /**
     * @param mixed $firstname
     */
    public function setFirstname($firstname)
    {
        $this->firstname = $firstname;
    }

    /**
     * @return mixed
     */
    public function getMiddlename()
    {
        return $this->middlename;
    }

    /**
     * @param mixed $middlename
     */
    public function setMiddlename($middlename)
    {
        $this->middlename = $middlename;
    }

    /**
     * @return mixed
     */
    public function getSuffix()
    {
        return $this->suffix;
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
    public function getGender()
    {
        return $this->gender;
    }

    /**
     * @param mixed $gender
     */
    public function setGender($gender)
    {
        $this->gender = $gender;
    }

    /**
     * @return mixed
     */
    public function getDob()
    {
        return $this->dob;
    }

    /**
     * @param mixed $dob
     */
    public function setDob($dob)
    {
        $this->dob = $dob;
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




} 