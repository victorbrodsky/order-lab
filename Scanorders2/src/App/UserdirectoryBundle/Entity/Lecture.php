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

namespace Oleg\UserdirectoryBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity
 * @ORM\Table(name="user_lecture")
 */
class Lecture extends BaseUserAttributes
{

    /**
     * @ORM\ManyToOne(targetEntity="User", inversedBy="lectures")
     * @ORM\JoinColumn(name="fosuser", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $user;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $lectureDate;

    /**
     * @ORM\ManyToOne(targetEntity="ImportanceList")
     * @ORM\JoinColumn(name="importance_id", referencedColumnName="id", nullable=true)
     */
    private $importance;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $title;

    /**
     * @ORM\ManyToOne(targetEntity="OrganizationList")
     * @ORM\JoinColumn(name="organization_id", referencedColumnName="id", nullable=true)
     */
    private $organization;

    /**
     * @ORM\ManyToOne(targetEntity="CityList")
     * @ORM\JoinColumn(name="city_id", referencedColumnName="id", nullable=true)
     */
    private $city;

    /**
     * @ORM\ManyToOne(targetEntity="States")
     * @ORM\JoinColumn(name="state_id", referencedColumnName="id", nullable=true)
     **/
    private $state;

    /**
     * @ORM\ManyToOne(targetEntity="Countries")
     * @ORM\JoinColumn(name="country_id", referencedColumnName="id", nullable=true)
     **/
    private $country;


    /**
     * @param mixed $importance
     */
    public function setImportance($importance)
    {
        $this->importance = $importance;
    }

    /**
     * @return mixed
     */
    public function getImportance()
    {
        return $this->importance;
    }

    /**
     * @param \DateTime $lectureDate
     */
    public function setLectureDate($lectureDate)
    {
        $this->lectureDate = $lectureDate;
    }

    /**
     * @return \DateTime
     */
    public function getLectureDate()
    {
        return $this->lectureDate;
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
     * @param mixed $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * @return mixed
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param mixed $city
     */
    public function setCity($city)
    {
        $this->city = $city;
    }

    /**
     * @return mixed
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * @param mixed $country
     */
    public function setCountry($country)
    {
        $this->country = $country;
    }

    /**
     * @return mixed
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * @param mixed $organization
     */
    public function setOrganization($organization)
    {
        $this->organization = $organization;
    }

    /**
     * @return mixed
     */
    public function getOrganization()
    {
        return $this->organization;
    }

    /**
     * @param mixed $state
     */
    public function setState($state)
    {
        $this->state = $state;
    }

    /**
     * @return mixed
     */
    public function getState()
    {
        return $this->state;
    }






    public function __toString() {
        return "Lecture";
    }

}