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
 * @ORM\MappedSuperclass
 */
class ObjectTypeReceivingBase extends ListAbstract
{

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $arraySectionIndex;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $arraySectionId;





    public function __construct( $creator = null ) {
        parent::__construct($creator);
    }



    /**
     * @return mixed
     */
    public function getFormNode()
    {
        return $this->formNode;
    }

    /**
     * @param mixed $formNode
     */
    public function setFormNode($formNode)
    {
        $this->formNode = $formNode;
    }


    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param mixed $value
     */
    public function setValue($value)
    {
        $this->value = $value;
    }

    /**
     * @return mixed
     */
    public function getArraySectionIndex()
    {
        return $this->arraySectionIndex;
    }

    /**
     * @param mixed $arraySectionIndex
     */
    public function setArraySectionIndex($arraySectionIndex)
    {
        $this->arraySectionIndex = $arraySectionIndex;
    }

    /**
     * @return mixed
     */
    public function getArraySectionId()
    {
        return $this->arraySectionId;
    }

    /**
     * @param mixed $arraySectionId
     */
    public function setArraySectionId($arraySectionId)
    {
        $this->arraySectionId = $arraySectionId;
    }




}