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

/**
 * @ORM\Entity
 * @ORM\Table(name="scan_partDiseaseType",
 *  indexes={
 *      @ORM\Index( name="partdiseasetype_field_idx", columns={"field"} )
 *  }
 * )
 */
class PartDiseaseType extends PartArrayFieldAbstract
{

    /**
     * @ORM\ManyToOne(targetEntity="Part", inversedBy="diseaseType", cascade={"persist"})
     * @ORM\JoinColumn(name="part_id", referencedColumnName="id", onDelete="CASCADE", nullable=true)
     */
    protected $part;

    /**
     * //serve as "diseaseType"
     * @ORM\Column(type="string", nullable=true)
     */
    protected $field;

    /**
     * @ORM\ManyToMany(targetEntity="DiseaseTypeList", cascade={"persist","remove"})
     * @ORM\JoinTable(name="scan_diseaseType_diseaseTypeList",
     *      joinColumns={@ORM\JoinColumn(name="diseaseType_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="diseaseTypeList_id", referencedColumnName="id")}
     *      )
     **/
    private $diseaseTypes;

    /**
     * @ORM\ManyToMany(targetEntity="DiseaseOriginList", cascade={"persist","remove"})
     * @ORM\JoinTable(name="scan_diseaseOrigin_diseaseOriginList",
     *      joinColumns={@ORM\JoinColumn(name="diseaseType_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="diseaseTypeList_id", referencedColumnName="id")}
     *      )
     **/
    private $diseaseOrigins;

    /**
     * @ORM\ManyToOne(targetEntity="OrganList", inversedBy="partprimary", cascade={"persist"})
     * @ORM\JoinColumn(name="primaryorgan_id", referencedColumnName="id", nullable=true)
     */
    protected $primaryOrgan;


    public function __construct( $status = 'valid', $provider = null, $source = null ) {
        parent::__construct($status,$provider,$source);
        $this->diseaseTypes = new ArrayCollection();
        $this->diseaseOrigins = new ArrayCollection();
    }


    /**
     * @param mixed $primaryOrgan
     */
    public function setPrimaryOrgan($primaryOrgan)
    {
        $this->primaryOrgan = $primaryOrgan;
    }

    /**
     * @return mixed
     */
    public function getPrimaryOrgan()
    {
        return $this->primaryOrgan;
    }


    public function getDiseaseTypes()
    {
        return $this->diseaseTypes;
    }
    public function addDiseaseType($item)
    {
        if( $item && !$this->diseaseTypes->contains($item) ) {
            $this->diseaseTypes->add($item);
        }
        return $this;
    }
    public function removeDiseaseType($item)
    {
        $this->diseaseTypes->removeElement($item);
    }

    public function getDiseaseOrigins()
    {
        return $this->diseaseOrigins;
    }
    public function addDiseaseOrigin($item)
    {
        if( $item && !$this->diseaseOrigins->contains($item) ) {
            $this->diseaseOrigins->add($item);
        }
        return $this;
    }
    public function removeDiseaseOrigin($item)
    {
        $this->diseaseOrigins->removeElement($item);
    }


}