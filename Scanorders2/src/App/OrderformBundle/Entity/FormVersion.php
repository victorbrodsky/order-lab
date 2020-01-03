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

namespace App\OrderformBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;

//UPDATE 'user_formNode' SET version='1' WHERE version IS NULL;

/**
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 * @ORM\Table(name="scan_formVersion")
 */
class FormVersion {


    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="Message", inversedBy="formVersions")
     * @ORM\JoinColumn(name="message_id", referencedColumnName="id", nullable=true)
     */
    private $message;


    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $formId;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $formTitle;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $formVersion;





    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @param mixed $message
     */
    public function setMessage($message)
    {
        $this->message = $message;
    }

    /**
     * @return mixed
     */
    public function getFormId()
    {
        return $this->formId;
    }

    /**
     * @param mixed $formId
     */
    public function setFormId($formId)
    {
        $this->formId = $formId;
    }

    /**
     * @return mixed
     */
    public function getFormTitle()
    {
        return $this->formTitle;
    }

    /**
     * @param mixed $formTitle
     */
    public function setFormTitle($formTitle)
    {
        $this->formTitle = $formTitle;
    }

    /**
     * @return mixed
     */
    public function getFormVersion()
    {
        return $this->formVersion;
    }

    /**
     * @param mixed $formVersion
     */
    public function setFormVersion($formVersion)
    {
        $this->formVersion = $formVersion;
    }


    public function setFormNode( $formNode ) {

        if( $formNode->getId() ) {
            $this->setFormId($formNode->getId());
        }

        if( $formNode->getName() ) {
            $this->setFormTitle($formNode->getName());
        }

        if( $formNode->getVersion() ) {
            $this->setFormVersion($formNode->getVersion());
        }

    }


    public function __toString()
    {
        $str = "";

        if( $this->getFormId() ) {
            $str = $str . " formId=" . $this->getFormId();
        }

        if( $this->getFormTitle() ) {
            $str = $str . " formTitle=" . $this->getFormTitle();
        }

        if( $this->getFormVersion() ) {
            $str = $str . " formVersion=" . $this->getFormVersion();
        }

        return $str;
    }

    public function printShort()
    {
        $str = "";

        if( $this->getFormTitle() ) {
            $str = $str . $this->getFormTitle();
        }

        if( $this->getFormId() ) {
            $str = $str . ", " . $this->getFormId();
        }

        if( $this->getFormVersion() ) {
            $str = $str . ", " . $this->getFormVersion();
        }

        return $str;
    }

}