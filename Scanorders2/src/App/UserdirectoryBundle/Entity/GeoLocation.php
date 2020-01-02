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
 * @ORM\Table(name="user_geoLocation")
 */
class GeoLocation
{

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $street1;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $street2;

    /**
     * @ORM\ManyToOne(targetEntity="CityList")
     * @ORM\JoinColumn(name="city", referencedColumnName="id", nullable=true)
     */
    private $city;

    /**
     * @ORM\ManyToOne(targetEntity="States")
     * @ORM\JoinColumn(name="state", referencedColumnName="id", nullable=true)
     **/
    private $state;

    /**
     * @ORM\ManyToOne(targetEntity="Countries")
     * @ORM\JoinColumn(name="country", referencedColumnName="id", nullable=true)
     **/
    private $country;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $county;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $zip;


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
     * @param mixed $county
     */
    public function setCounty($county)
    {
        $this->county = $county;
    }

    /**
     * @return mixed
     */
    public function getCounty()
    {
        return $this->county;
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

    /**
     * @param mixed $street1
     */
    public function setStreet1($street1)
    {
        $this->street1 = $street1;
    }

    /**
     * @return mixed
     */
    public function getStreet1()
    {
        return $this->street1;
    }

    /**
     * @param mixed $street2
     */
    public function setStreet2($street2)
    {
        $this->street2 = $street2;
    }

    /**
     * @return mixed
     */
    public function getStreet2()
    {
        return $this->street2;
    }

    /**
     * @param mixed $zip
     */
    public function setZip($zip)
    {
        $this->zip = $zip;
    }

    /**
     * @return mixed
     */
    public function getZip()
    {
        return $this->zip;
    }


    //City, Country Abbreviation such as USA (Full name of country if abbreviation not available)
    public function getFullGeoLocation( $delimeter = ", " ) {
        //echo "delimeter=[$delimeter]<br>";
        $resArr = array();

        if( $this->getStreet1() && $this->getStreet2() ) {
            $resArr[] =  $this->getStreet1() . ", " .  $this->getStreet2();
        }

        if( $this->getStreet1() ) {
            $resArr[] = $this->getStreet1() . "";
        }

        if( $this->getStreet2() ) {
            $resArr[] = $this->getStreet2() . "";
        }

        $cityStr = null;
        if( $this->getCity() ) {
            $cityStr = $this->getCity()->getOptimalName() . "";
        }

        $stateStr = null;
        if( $this->getState() ) {
            $stateStr = $this->getState()->getOptimalName() . "";
        }

        if( $cityStr && $stateStr ) {
            $resArr[] = $cityStr . ", " . $stateStr;
        } else {
            if( $cityStr ) {
                $resArr[] = $cityStr . "";
            }
            if( $stateStr ) {
                $resArr[] = $stateStr . "";
            }
        }

        if( $this->getZip() ) {
            $resArr[] = $this->getZip() . "";
        }

        if( $this->getCountry() ) {
            $resArr[] = $this->getCountry()->getOptimalName() . "";
        }

        return implode($delimeter,$resArr);
    }

    public function __toString() {

        if( $this->getStreet1() && $this->getStreet2() ) {
            return $this->getStreet1() . ", " .  $this->getStreet2();
        }

        if( $this->getStreet1() ) {
            return $this->getStreet1() . "";
        }

        if( $this->getStreet2() ) {
            return $this->getStreet2() . "";
        }

        return "";
    }



}