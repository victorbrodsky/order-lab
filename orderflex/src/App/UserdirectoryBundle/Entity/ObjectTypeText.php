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
     * Copy of the value. If the value is html text, this $secondaryValue can store the plain text
     *
     * @ORM\Column(type="text", nullable=true)
     */
    protected $secondaryValue;



    /**
     * @return mixed
     */
    public function getSecondaryValue()
    {
        return $this->secondaryValue;
    }

    /**
     * @param mixed $secondaryValue
     */
    public function setSecondaryValue($secondaryValue)
    {
        $this->secondaryValue = $secondaryValue;
    }

    /**
     * @param mixed $value
     */
    public function setValue($value)
    {
        $this->value = $value;

        if ($value) {

            if(1) { //formnode might not be set when is used directly by symfony's form
                //make sure formNode is set in this text object
                $formNode = $this->getFormNode();
                //echo "formNode=$formNode <br>";
                if ($formNode) {
                    $objectType = $formNode->getObjectType();
                    //echo "objectType=$objectType <br>";
                    if ($objectType && $objectType->getName() == "Form Field - Free Text, HTML" ) {
                        $secondaryValue = $this->convertHtmlToPlainText($value);
                        if ($secondaryValue) {
                            $this->setSecondaryValue($secondaryValue);
                        }
                    }
                }
                //exit('111');
            } else {
                $secondaryValue = $this->convertHtmlToPlainText($value);
                if ($secondaryValue) {
                    $this->setSecondaryValue($secondaryValue);
                }
            }

        }

    }

    public function convertHtmlToPlainText($text) {
        if( $text ) {
            $text = strip_tags($text);
        }
        return $text;
    }


}