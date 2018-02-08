<?php
/**
 * Created by PhpStorm.
 * User: ch3
 * Date: 12/4/2017
 * Time: 3:12 PM
 */

namespace Oleg\TranslationalResearchBundle\Entity;


use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;


/**
 * @ORM\Entity
 * @ORM\Table(name="transres_dataResult")
 */
class DataResult {

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $createDate;

    /**
     * @ORM\ManyToOne(targetEntity="Oleg\UserdirectoryBundle\Entity\User")
     * @ORM\JoinColumn(referencedColumnName="id", nullable=true)
     */
    private $submitter;

    /**
     * @ORM\ManyToOne(targetEntity="TransResRequest", inversedBy="dataResults")
     * @ORM\JoinColumn(name="transresRequest_id", referencedColumnName="id")
     */
    private $transresRequest;

    /**
     * @var string
     * @ORM\Column(type="string", nullable=true)
     */
    private $system;

    /**
     * @var string
     * @ORM\Column(type="string", nullable=true)
     */
    private $accessionId;

    /**
     * @var string
     * @ORM\Column(type="string", nullable=true)
     */
    private $barcode;



    public function __construct($user=null) {
        $this->setSubmitter($user);
        $this->setCreateDate(new \DateTime());
    }


    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return \DateTime
     */
    public function getCreateDate()
    {
        return $this->createDate;
    }

    /**
     * @param \DateTime $createDate
     */
    public function setCreateDate($createDate)
    {
        $this->createDate = $createDate;
    }

    /**
     * @return mixed
     */
    public function getSubmitter()
    {
        return $this->submitter;
    }

    /**
     * @param mixed $submitter
     */
    public function setSubmitter($submitter)
    {
        $this->submitter = $submitter;
    }

    /**
     * @return mixed
     */
    public function getTransresRequest()
    {
        return $this->transresRequest;
    }

    /**
     * @param mixed $transresRequest
     */
    public function setTransresRequest($transresRequest)
    {
        $this->transresRequest = $transresRequest;
    }

    /**
     * @return string
     */
    public function getSystem()
    {
        return $this->system;
    }

    /**
     * @param string $system
     */
    public function setSystem($system)
    {
        $this->system = $system;
    }

    /**
     * @return string
     */
    public function getAccessionId()
    {
        return $this->accessionId;
    }

    /**
     * @param string $accessionId
     */
    public function setAccessionId($accessionId)
    {
        $this->accessionId = $accessionId;
    }

    /**
     * @return string
     */
    public function getBarcode()
    {
        return $this->barcode;
    }

    /**
     * @param string $barcode
     */
    public function setBarcode($barcode)
    {
        $this->barcode = $barcode;
    }

}