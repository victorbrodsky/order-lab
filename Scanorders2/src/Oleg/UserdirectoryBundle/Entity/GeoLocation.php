<?php

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
    public function getFullGeoLocation() {

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

        if( $this->getCity() ) {
            $resArr[] = $this->getCity() . "";
        }

        if( $this->getCountry() ) {
            $resArr[] = $this->getCountry()->getOptimalName() . "";
        }

        return implode(", ",$resArr);
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