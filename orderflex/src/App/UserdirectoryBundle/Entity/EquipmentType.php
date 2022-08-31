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

namespace App\UserdirectoryBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;


/**
 * @ORM\Entity
 * @ORM\Table(name="user_equipmentType")
 */
class EquipmentType extends ListAbstract
{

    /**
     * @ORM\OneToMany(targetEntity="EquipmentType", mappedBy="original", cascade={"persist"})
     **/
    protected $synonyms;

    /**
     * @ORM\ManyToOne(targetEntity="EquipmentType", inversedBy="synonyms", cascade={"persist"})
     * @ORM\JoinColumn(name="original_id", referencedColumnName="id", nullable=true)
     **/
    protected $original;


    /**
     * @ORM\OneToMany(targetEntity="Equipment", mappedBy="keytype")
     */
    protected $equipments;



    public function __construct() {
        $this->synonyms = new ArrayCollection();
        $this->equipments = new ArrayCollection();
    }


    



    public function addEquipment(\App\UserdirectoryBundle\Entity\Equipment $equipment)
    {
        if( !$this->equipments->contains($equipment) ) {
            $this->equipments->add($equipment);
            $equipment->setKeytype($this);
        }
        return $this;
    }

    public function removeEquipment(\App\UserdirectoryBundle\Entity\Equipment $equipment)
    {
        $this->equipments->removeElement($equipment);
    }

    public function getEquipments()
    {
        return $this->equipments;
    }

    public function getChildren() {
        return $this->getEquipments();
    }

}