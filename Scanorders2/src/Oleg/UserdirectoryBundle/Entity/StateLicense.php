<?php

namespace Oleg\UserdirectoryBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="user_stateLicense")
 */
class StateLicense
{

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="States")
     * @ORM\JoinColumn(name="state_id", referencedColumnName="id", nullable=true)
     **/
    private $state;

    /**
     * @ORM\ManyToOne(targetEntity="States")
     * @ORM\JoinColumn(name="country", referencedColumnName="id", nullable=true)
     **/
    private $country;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $licenseNumber;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $licenseExpirationDate;


    /**
     * @ORM\ManyToOne(targetEntity="Credentials", inversedBy="stateLicense")
     * @ORM\JoinColumn(name="credentials_id", referencedColumnName="id", onDelete="CASCADE", nullable=true)
     */
    protected $credentials;


    /**
     * @param mixed $credentials
     */
    public function setCredentials($credentials)
    {
        $this->credentials = $credentials;
    }

    /**
     * @return mixed
     */
    public function getCredentials()
    {
        return $this->credentials;
    }

    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $licenseExpirationDate
     */
    public function setLicenseExpirationDate($licenseExpirationDate)
    {
        $this->licenseExpirationDate = $licenseExpirationDate;
    }

    /**
     * @return mixed
     */
    public function getLicenseExpirationDate()
    {
        return $this->licenseExpirationDate;
    }

    /**
     * @param mixed $licenseNumber
     */
    public function setLicenseNumber($licenseNumber)
    {
        $this->licenseNumber = $licenseNumber;
    }

    /**
     * @return mixed
     */
    public function getLicenseNumber()
    {
        return $this->licenseNumber;
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




    public function __toString() {
        return "State License";
    }


}