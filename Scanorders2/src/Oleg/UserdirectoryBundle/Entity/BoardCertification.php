<?php

namespace Oleg\UserdirectoryBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="user_boardCertification")
 */
class BoardCertification
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
     * @ORM\ManyToOne(targetEntity="BoardCertifiedSpecialties")
     * @ORM\JoinColumn(name="boardSpecialty_id", referencedColumnName="id")
     **/
    private $specialty;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $issueDate;

    /**
     * Expiration Date
     *
     * @ORM\Column(type="date", nullable=true)
     */
    private $expirationDate;

    /**
     * Recertification Date
     *
     * @ORM\Column(type="date", nullable=true)
     */
    private $recertificationDate;


    /**
     * @ORM\ManyToOne(targetEntity="Credentials", inversedBy="boardCertification")
     * @ORM\JoinColumn(name="credentials_id", referencedColumnName="id", onDelete="CASCADE", nullable=true)
     */
    private $credentials;


    /**
     * @ORM\ManyToOne(targetEntity="CertifyingBoardOrganization")
     **/
    private $certifyingBoardOrganization;


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
     * @param mixed $expirationDate
     */
    public function setExpirationDate($expirationDate)
    {
        $this->expirationDate = $expirationDate;
    }

    /**
     * @return mixed
     */
    public function getExpirationDate()
    {
        return $this->expirationDate;
    }

    /**
     * @param mixed $issueDate
     */
    public function setIssueDate($issueDate)
    {
        $this->issueDate = $issueDate;
    }

    /**
     * @return mixed
     */
    public function getIssueDate()
    {
        return $this->issueDate;
    }

    /**
     * @param mixed $specialty
     */
    public function setSpecialty($specialty)
    {
        $this->specialty = $specialty;
    }

    /**
     * @return mixed
     */
    public function getSpecialty()
    {
        return $this->specialty;
    }

    /**
     * @param mixed $certifyingBoardOrganization
     */
    public function setCertifyingBoardOrganization($certifyingBoardOrganization)
    {
        $this->certifyingBoardOrganization = $certifyingBoardOrganization;
    }

    /**
     * @return mixed
     */
    public function getCertifyingBoardOrganization()
    {
        return $this->certifyingBoardOrganization;
    }

    /**
     * @param mixed $recertificationDate
     */
    public function setRecertificationDate($recertificationDate)
    {
        $this->recertificationDate = $recertificationDate;
    }

    /**
     * @return mixed
     */
    public function getRecertificationDate()
    {
        return $this->recertificationDate;
    }



    public function __toString() {
        return "Board Certification";
    }

}