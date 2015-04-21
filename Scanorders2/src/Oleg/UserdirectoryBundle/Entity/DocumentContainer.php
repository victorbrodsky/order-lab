<?php
/**
 * Created by JetBrains PhpStorm.
 * User: oli2002
 * Date: 9/10/14
 * Time: 5:46 PM
 * To change this template use File | Settings | File Templates.
 */

namespace Oleg\UserdirectoryBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;


/**
 * @ORM\Entity
 * @ORM\Table(name="user_documentContainer")
 */
class DocumentContainer {


    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private  $id;

    /**
     * @ORM\ManyToOne(targetEntity="AttachmentContainer", inversedBy="documentContainers")
     * @ORM\JoinColumn(name="attachmentContainer_id", referencedColumnName="id", nullable=true)
     */
    private $attachmentContainer;

    /**
     * @ORM\ManyToMany(targetEntity="Document", cascade={"persist","remove"})
     * @ORM\JoinTable(name="user_documentcontainer_document",
     *      joinColumns={@ORM\JoinColumn(name="documentcontainer_id", referencedColumnName="id", onDelete="cascade")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="document_id", referencedColumnName="id", onDelete="cascade")}
     *      )
     **/
    private $documents;

    //Image Title: [plain text]
    /**
     * Document Container title
     * @ORM\Column(type="string", nullable=true)
     */
    private $title;

    //Image Comment(s): [plain text]
    /**
     * Document Container comment
     * @ORM\OneToMany(targetEntity="DocumentComment", mappedBy="documentContainer", cascade={"persist","remove"})
     */
    private $comments;

    //Image Device: [Select2, "Generic Desktop Scanner" are the only two values; link to Equipment table, filter by Type="Requisition Form Scanner"]
    /**
     * @ORM\ManyToOne(targetEntity="Equipment")
     */
    private $device;

    //Image Date & Time: [plain text]
    /**
     * @var \DateTime
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $datetime;

    //Image Scanned By: [Select2; one user from the list of users]
    /**
     * @ORM\ManyToOne(targetEntity="User")
     */
    private $provider;

    /**
     * @ORM\ManyToOne(targetEntity="DocumentTypeList")
     * @ORM\JoinColumn(name="type_id", referencedColumnName="id", nullable=true)
     */
    private $type;


    public function __construct() {
        $this->documents = new ArrayCollection();
        $this->comments = new ArrayCollection();

        //testing
        //echo "comments count=".count($this->getComments())."<br>";
        if( count($this->getComments()) == 0 ) {
            $newcomment = new DocumentComment();
            $this->addComment($newcomment);
        }
    }





    /**
     * @param mixed $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * @return mixed
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param mixed $attachmentContainer
     */
    public function setAttachmentContainer($attachmentContainer)
    {
        $this->attachmentContainer = $attachmentContainer;
    }

    /**
     * @return mixed
     */
    public function getAttachmentContainer()
    {
        return $this->attachmentContainer;
    }





    public function getDocuments()
    {
        return $this->documents;
    }
    public function addDocument($document)
    {
        //echo "add document=".$document;
        //exit('add');
        if( $document && !$this->documents->contains($document) ) {
            $this->documents->add($document);
        }
        return $this;
    }
    public function removeDocument($document)
    {
        $this->documents->removeElement($document);
    }


    public function getComments()
    {
        return $this->comments;
    }
    public function addComment($comment)
    {
        if( $comment && !$this->comments->contains($comment) ) {
            $this->comments->add($comment);
        }
        return $this;
    }
    public function removeComment($comment)
    {
        $this->comments->removeElement($comment);
    }


    /**
     * @param \DateTime $datetime
     */
    public function setDatetime($datetime)
    {
        $this->datetime = $datetime;
    }

    /**
     * @return \DateTime
     */
    public function getDatetime()
    {
        return $this->datetime;
    }

    /**
     * @param mixed $device
     */
    public function setDevice($device)
    {
        $this->device = $device;
    }

    /**
     * @return mixed
     */
    public function getDevice()
    {
        return $this->device;
    }

    /**
     * @param mixed $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $provider
     */
    public function setProvider($provider)
    {
        $this->provider = $provider;
    }

    /**
     * @return mixed
     */
    public function getProvider()
    {
        return $this->provider;
    }

    /**
     * @param mixed $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * @return mixed
     */
    public function getTitle()
    {
        return $this->title;
    }



    public function __toString() {
        return "DocumentContainer: "."comments count=".count($this->getComments())."<br>";
    }
}