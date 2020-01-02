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

/**
 * Created by JetBrains PhpStorm.
 * User: oli2002
 * Date: 9/10/13
 * Time: 5:46 PM
 * To change this template use File | Settings | File Templates.
 */

namespace Oleg\OrderformBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="scan_slideText")
 */
class SlideText extends ArrayFieldAbstract
{
    /**
     * @ORM\ManyToOne(targetEntity="SlideReturnRequest", inversedBy="slidetext", cascade={"persist"})
     * @ORM\JoinColumn(name="slideReturnRequest_id", referencedColumnName="id", onDelete="CASCADE", nullable=true)
     */
    protected $slideReturnRequest;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $mrntype;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $mrn;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $patientlastname;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $patientfirstname;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $patientmiddlename;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $accessiontype;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $accession;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $part;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $block;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $stain;


    /**
     * Set slideReturnRequest
     *
     * @param \Oleg\OrderformBundle\Entity\SlideReturnRequest $slideReturnRequest
     * @return SlideReturnRequest Field
     */
    public function setSlideReturnRequest(\Oleg\OrderformBundle\Entity\SlideReturnRequest $slideReturnRequest = null)
    {
        $this->slideReturnRequest = $slideReturnRequest;

        return $this;
    }

    /**
     * Get slideReturnRequest
     *
     * @return \Oleg\OrderformBundle\Entity\SlideReturnRequest
     */
    public function getSlideReturnRequest()
    {
        return $this->slideReturnRequest;
    }

    /**
     * @param mixed $accession
     */
    public function setAccession($accession)
    {
        $this->accession = $accession;
    }

    /**
     * @return mixed
     */
    public function getAccession()
    {
        return $this->accession;
    }

    /**
     * @param mixed $accessiontype
     */
    public function setAccessiontype($accessiontype)
    {
        $this->accessiontype = $accessiontype;
    }

    /**
     * @return mixed
     */
    public function getAccessiontype()
    {
        return $this->accessiontype;
    }

    /**
     * @param mixed $block
     */
    public function setBlock($block)
    {
        $this->block = $block;
    }

    /**
     * @return mixed
     */
    public function getBlock()
    {
        return $this->block;
    }

    /**
     * @param mixed $mrn
     */
    public function setMrn($mrn)
    {
        $this->mrn = $mrn;
    }

    /**
     * @return mixed
     */
    public function getMrn()
    {
        return $this->mrn;
    }

    /**
     * @param mixed $mrntype
     */
    public function setMrntype($mrntype)
    {
        $this->mrntype = $mrntype;
    }

    /**
     * @return mixed
     */
    public function getMrntype()
    {
        return $this->mrntype;
    }

    /**
     * @param mixed $part
     */
    public function setPart($part)
    {
        $this->part = $part;
    }

    /**
     * @return mixed
     */
    public function getPart()
    {
        return $this->part;
    }

    /**
     * @param mixed $patientfirstname
     */
    public function setPatientfirstname($patientfirstname)
    {
        $this->patientfirstname = $patientfirstname;
    }

    /**
     * @return mixed
     */
    public function getPatientfirstname()
    {
        return $this->patientfirstname;
    }

    /**
     * @param mixed $patientlastname
     */
    public function setPatientlastname($patientlastname)
    {
        $this->patientlastname = $patientlastname;
    }

    /**
     * @return mixed
     */
    public function getPatientlastname()
    {
        return $this->patientlastname;
    }

    /**
     * @param mixed $patientmiddlename
     */
    public function setPatientmiddlename($patientmiddlename)
    {
        $this->patientmiddlename = $patientmiddlename;
    }

    /**
     * @return mixed
     */
    public function getPatientmiddlename()
    {
        return $this->patientmiddlename;
    }

    /**
     * @param mixed $stain
     */
    public function setStain($stain)
    {
        $this->stain = $stain;
    }

    /**
     * @return mixed
     */
    public function getStain()
    {
        return $this->stain;
    }

    public function getFullPatientName() {
        $patientFullName = "";

        if( $this->getPatientlastname() && $this->getPatientlastname() != "" ) {
            $patientFullName .= '<b>'.$this->getPatientlastname().'</b>';
        } else {
            $patientFullName .= "No Last Name Provided";
        }

        if( $this->getPatientfirstname() && $this->getPatientfirstname() != "" ) {
            if( $patientFullName != '' ) {
                $patientFullName .= ', ';
            }
            $patientFullName .= $this->getPatientfirstname();
        } else {
            if( $patientFullName != '' ) {
                $patientFullName .= ', ';
            }
            $patientFullName .= "No First Name Provided";
        }

        if( $this->getPatientmiddlename() && $this->getPatientmiddlename() != "" ) {
            if( $patientFullName != '' ) {
                $patientFullName .= ' ';
            }
            $patientFullName .= '<i>'.$this->getPatientmiddlename().'</i>';
        }

        return $patientFullName;
    }



}