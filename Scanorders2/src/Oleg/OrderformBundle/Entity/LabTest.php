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

    //"Laboratory Test Title" is list name


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


    //interface function
    public function getAuthor()
    {
        return $this->getCreator();
    }
    public function setAuthor($author)
    {
        return $this->setCreator($author);
    }
    public function getUpdateAuthor()
    {
        return $this->getUpdatedby();
    }
    public function setUpdateAuthor($author)
    {
        return $this->setUpdatedby($author);
    }


    public function __toString() {
        $res = "";

        if( $this->getName() ) {
            $res = $res . $this->getName() . " ";
        }

        if( $this->getLabTestType() ) {
            $res = $res . $this->getLabTestType() . " ";
        }

        if( $this->getLabTestId() ) {
            $res = $res . $this->getLabTestId() . " ";
        }

        return $res;
    }

}