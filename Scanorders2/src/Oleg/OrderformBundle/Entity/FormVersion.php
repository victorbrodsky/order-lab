<?php

namespace Oleg\OrderformBundle\Entity;

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