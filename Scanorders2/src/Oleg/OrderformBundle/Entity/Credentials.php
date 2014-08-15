<?php

namespace Oleg\OrderformBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="credentials")
 */
class Credentials extends BaseUserAttributes
{

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $employeeId;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $dob;

    /**
     * @ORM\ManyToMany(targetEntity="CodeNYPH")
     * @ORM\JoinTable(name="credentials_codeNYPH",
     *      joinColumns={@ORM\JoinColumn(name="credentials_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="codeNYPH_id", referencedColumnName="id")}
     *      )
     **/
    private $codeNYPH;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $ntionalProviderIdentifier;

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
     * @ORM\OneToMany(targetEntity="StateLicense", mappedBy="credentials", cascade={"persist"})
     */
    private $stateLicense;


    public function __construct() {
        parent::__construct();
        $this->stateLicense = new ArrayCollection();
        $this->setType(self::TYPE_RESTRICTED);
    }


    //overwrite set type: this object is restricted => can not change type
    public function setType($type)
    {
        if( $this->getType() == self::TYPE_RESTRICTED ) {
            throw new \Exception( 'Can not change type for restricted entity' );
        }
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
     * @param mixed $codeNYPH
     */
    public function setCodeNYPH($codeNYPH)
    {
        $this->codeNYPH = $codeNYPH;
    }

    /**
     * @return mixed
     */
    public function getCodeNYPH()
    {
        return $this->codeNYPH;
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
     * @param mixed $ntionalProviderIdentifier
     */
    public function setNtionalProviderIdentifier($ntionalProviderIdentifier)
    {
        $this->ntionalProviderIdentifier = $ntionalProviderIdentifier;
    }

    /**
     * @return mixed
     */
    public function getNtionalProviderIdentifier()
    {
        return $this->ntionalProviderIdentifier;
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
        if( !$this->stateLicense->contains($stateLicense) ) {
            $stateLicense->setCredentials($this);
            $this->stateLicense->add($stateLicense);
        }

    }

    public function removeStateLicense($stateLicense)
    {
        $this->stateLicense->removeElement($stateLicense);
    }




}