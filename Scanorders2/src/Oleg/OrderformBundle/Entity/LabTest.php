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