<?php

namespace Oleg\OrderformBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="boardCertification")
 */
class BoardCertification extends BaseTitle
{


    /**
     * @ORM\ManyToOne(targetEntity="Specialty", cascade={"persist"})
     * @ORM\JoinColumn(name="specialty_id", referencedColumnName="id", nullable=true)
     */
    private $specialty;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $issuedDate;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $recertificationDate;



    public function __construct() {
        parent::__construct();
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
     * @param mixed $issuedDate
     */
    public function setIssuedDate($issuedDate)
    {
        $this->issuedDate = $issuedDate;
    }

    /**
     * @return mixed
     */
    public function getIssuedDate()
    {
        return $this->issuedDate;
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





}