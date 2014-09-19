<?php

namespace Oleg\UserdirectoryBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity
 * @ORM\Table(name="credentials")
 */
class Credentials extends BaseUserAttributes
{

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $employeeId;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $dob;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $nationalProviderIdentifier;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $numberCLIA;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $cliaExpirationDate;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $numberPFI;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $numberCOQ;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $coqExpirationDate;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $emergencyContactInfo;

    /**
     * @ORM\OneToOne(targetEntity="User", mappedBy="credentials")
     */
    private $user;

    ///// Collections //////
    /**
     * @ORM\OneToMany(targetEntity="StateLicense", mappedBy="credentials", cascade={"persist"})
     */
    private $stateLicense;

    /**
     * @ORM\OneToMany(targetEntity="BoardCertification", mappedBy="credentials", cascade={"persist"})
     */
    private $boardCertification;

    /**
     * @ORM\OneToMany(targetEntity="CodeNYPH", mappedBy="credentials", cascade={"persist"})
     */
    private $codeNYPH;



    public function __construct() {

        parent::__construct();

        $this->stateLicense = new ArrayCollection();
        $this->boardCertification = new ArrayCollection();
        $this->codeNYPH = new ArrayCollection();

        $this->setType(self::TYPE_RESTRICTED);

        //create new state License
        $this->addStateLicense( new StateLicense() );

        //create new board Certification
        $this->addBoardCertification( new BoardCertification() );

        //create new Code NYPH
        $this->addCodeNYPH( new CodeNYPH() );
    }

    /**
     * @param mixed $cliaExpirationDate
     */
    public function setCliaExpirationDate($cliaExpirationDate)
    {
        $this->cliaExpirationDate = $cliaExpirationDate;
    }

    /**
     * @return mixed
     */
    public function getCliaExpirationDate()
    {
        return $this->cliaExpirationDate;
    }

    /**
     * @param mixed $coqExpirationDate
     */
    public function setCoqExpirationDate($coqExpirationDate)
    {
        $this->coqExpirationDate = $coqExpirationDate;
    }

    /**
     * @return mixed
     */
    public function getCoqExpirationDate()
    {
        return $this->coqExpirationDate;
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
    public function getDob()
    {
        return $this->dob;
    }

    /**
     * @param mixed $employeeId
     */
    public function setEmployeeId($employeeId)
    {
        $this->employeeId = $employeeId;
    }

    /**
     * @return mixed
     */
    public function getEmployeeId()
    {
        return $this->employeeId;
    }

    /**
     * @param mixed $nationalProviderIdentifier
     */
    public function setNationalProviderIdentifier($nationalProviderIdentifier)
    {
        $this->nationalProviderIdentifier = $nationalProviderIdentifier;
    }

    /**
     * @return mixed
     */
    public function getNationalProviderIdentifier()
    {
        return $this->nationalProviderIdentifier;
    }

    /**
     * @param mixed $numberCLIA
     */
    public function setNumberCLIA($numberCLIA)
    {
        $this->numberCLIA = $numberCLIA;
    }

    /**
     * @return mixed
     */
    public function getNumberCLIA()
    {
        return $this->numberCLIA;
    }

    /**
     * @param mixed $numberCOQ
     */
    public function setNumberCOQ($numberCOQ)
    {
        $this->numberCOQ = $numberCOQ;
    }

    /**
     * @return mixed
     */
    public function getNumberCOQ()
    {
        return $this->numberCOQ;
    }

    /**
     * @param mixed $numberPFI
     */
    public function setNumberPFI($numberPFI)
    {
        $this->numberPFI = $numberPFI;
    }

    /**
     * @return mixed
     */
    public function getNumberPFI()
    {
        return $this->numberPFI;
    }

    /**
     * @return mixed
     */
    public function getStateLicense()
    {
        return $this->stateLicense;
    }

    public function addStateLicense( $stateLicense )
    {
        if( !$stateLicense )
            return;

        if( !$this->stateLicense->contains($stateLicense) ) {
            $stateLicense->setCredentials($this);
            $this->stateLicense->add($stateLicense);
        }

    }

    public function removeStateLicense($stateLicense)
    {
        $this->stateLicense->removeElement($stateLicense);
    }

    /**
     * @return mixed
     */
    public function getBoardCertification()
    {
        return $this->boardCertification;
    }

    public function addBoardCertification( $boardCertification )
    {
        if( !$boardCertification )
            return;

        if( !$this->boardCertification->contains($boardCertification) ) {
            $boardCertification->setCredentials($this);
            $this->boardCertification->add($boardCertification);
        }

    }

    public function removeBoardCertification($boardCertification)
    {
        $this->boardCertification->removeElement($boardCertification);
    }

    /**
     * @param mixed $emergencyContactInfo
     */
    public function setEmergencyContactInfo($emergencyContactInfo)
    {
        $this->emergencyContactInfo = $emergencyContactInfo;
    }

    /**
     * @return mixed
     */
    public function getEmergencyContactInfo()
    {
        return $this->emergencyContactInfo;
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
     * @return mixed
     */
    public function getCodeNYPH()
    {
        return $this->codeNYPH;
    }
    public function addCodeNYPH( $codeNYPH )
    {
        if( !$codeNYPH )
            return;

        if( !$this->codeNYPH->contains($codeNYPH) ) {
            $codeNYPH->setCredentials($this);
            $this->codeNYPH->add($codeNYPH);
        }

    }
    public function removeCodeNYPH($codeNYPH)
    {
        $this->codeNYPH->removeElement($codeNYPH);
    }

    public function __toString() {
        return "Credentials";
    }

}