<?php

namespace Oleg\OrderformBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

use Oleg\UserdirectoryBundle\Entity\ListAbstract;

/**
 * @ORM\Entity
 * @ORM\Table(name="scan_labTest")
 */
class LabTest extends ListAbstract {

    /**
     * @ORM\OneToMany(targetEntity="LabTest", mappedBy="original", cascade={"persist"})
     **/
    protected $synonyms;

    /**
     * @ORM\ManyToOne(targetEntity="LabTest", inversedBy="synonyms", cascade={"persist"})
     * @ORM\JoinColumn(name="original_id", referencedColumnName="id", nullable=true)
     **/
    protected $original;




    //"Laboratory Test ID Type" field (just like MRN Type; Select2)
    /**
     * @ORM\ManyToOne(targetEntity="LabTestType", cascade={"persist"})
     * @ORM\JoinColumn(referencedColumnName="id", nullable=true)
     */
    private $labTestType;

    //"Laboratory Test ID" field (just like MRN)
    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $labTestId;

//    /**
//     * "Laboratory Test Title"
//     * @ORM\Column(type="string", nullable=true)
//     */
//    private $labTestTitle;
    /**
     * "Laboratory Test Title"
     * @ORM\ManyToOne(targetEntity="LabTestTitle", cascade={"persist"})
     * @ORM\JoinColumn(referencedColumnName="id", nullable=true)
     */
    private $labTestTitle;



    /**
     * @param mixed $labTestId
     */
    public function setLabTestId($labTestId)
    {
        $this->labTestId = $labTestId;
    }

    /**
     * @return mixed
     */
    public function getLabTestId()
    {
        return $this->labTestId;
    }

    /**
     * @param mixed $labTestTitle
     */
    public function setLabTestTitle($labTestTitle)
    {
        $this->labTestTitle = $labTestTitle;
    }

    /**
     * @return mixed
     */
    public function getLabTestTitle()
    {
        return $this->labTestTitle;
    }

    /**
     * @param mixed $labTestType
     */
    public function setLabTestType($labTestType)
    {
        $this->labTestType = $labTestType;
    }

    /**
     * @return mixed
     */
    public function getLabTestType()
    {
        return $this->labTestType;
    }



    public function __toString() {
        $res = "";

        if( $this->getLabTestTitle() ) {
            $res = $res . $this->getLabTestTitle() . " ";
        }

        if( $this->labTestType() ) {
            $res = $res . $this->labTestType() . " ";
        }

        if( $this->getLabTestId() ) {
            $res = $res . $this->getLabTestId() . " ";
        }

        return $res;
    }

}