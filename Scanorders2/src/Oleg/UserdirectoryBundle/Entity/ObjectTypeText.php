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
 * @ORM\Table(name="user_objectTypeText")
 */
class ObjectTypeText extends ObjectTypeReceivingBase
{

    /**
     * @ORM\OneToMany(targetEntity="ObjectTypeText", mappedBy="original")
     **/
    protected $synonyms;

    /**
     * @ORM\ManyToOne(targetEntity="ObjectTypeText", inversedBy="synonyms")
     * @ORM\JoinColumn(name="original_id", referencedColumnName="id")
     **/
    protected $original;


//    /**
//     * @ORM\OneToMany(targetEntity="FormNode", mappedBy="objectTypeText")
//     */
//    private $formNodes;
//    /**
//     * @ORM\ManyToOne(targetEntity="ObjectTypeText", inversedBy="formNodes", cascade={"persist"})
//     * @ORM\JoinColumn(name="objectTypeText_id", referencedColumnName="id")
//     */
//    private $objectTypeText;
    /**
     * @ORM\ManyToOne(targetEntity="FormNode", inversedBy="objectTypeTexts", cascade={"persist"})
     * @ORM\JoinColumn(name="formNode_id", referencedColumnName="id")
     */
    protected $formNode;

    /**
     * Plain text
     * 
     * @ORM\Column(type="text", nullable=true)
     */
    protected $value;


    /**
     * Reach html text (WYSIWYG)
     * 
     * @ORM\Column(type="text", nullable=true)
     */
    protected $valueHtml;




    /**
     * @return mixed
     */
    public function getValueHtml()
    {
        return $this->valueHtml;
    }

    /**
     * @param mixed $valueHtml
     */
    public function setValueHtml($valueHtml)
    {
        $this->valueHtml = $valueHtml;
    }


}