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

namespace Oleg\UserdirectoryBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity
 * @ORM\Table(name="user_objectTypeDropdown")
 */
class ObjectTypeDropdown extends ObjectTypeReceivingBase
{

    /**
     * @ORM\OneToMany(targetEntity="ObjectTypeDropdown", mappedBy="original")
     **/
    protected $synonyms;

    /**
     * @ORM\ManyToOne(targetEntity="ObjectTypeDropdown", inversedBy="synonyms")
     * @ORM\JoinColumn(name="original_id", referencedColumnName="id")
     **/
    protected $original;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $value;

    /**
     * @ORM\ManyToOne(targetEntity="FormNode", inversedBy="objectTypeDropdowns", cascade={"persist"})
     * @ORM\JoinColumn(name="formNode_id", referencedColumnName="id")
     */
    protected $formNode;

    /**
     * @var array
     * @ORM\Column(type="array", nullable=true)
     */
    private $idValues;



    public function __construct($creator=null)
    {
        parent::__construct($creator);
        $this->idValues = array();
    }


    /**
     * @return mixed
     */
    public function getIdValues()
    {
        return $this->idValues;
    }
    /**
     * @param mixed $values
     */
    public function setIdValues($values)
    {
//        if( $values && count($values) > 0 ) {
//            foreach( $values as $value ) {
//                $this->addIdValue($value);
//            }
//        } else {
//            $this->idValues = array();
//        }
        $this->idValues = $values;
    }
    public function addIdValue($value) {
        $this->idValues[] = $value;
        return $this;
    }

}