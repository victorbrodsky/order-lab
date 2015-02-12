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
     * @ORM\ManyToMany(targetEntity="Document")
     * @ORM\JoinTable(name="user_documentcontainer_document",
     *      joinColumns={@ORM\JoinColumn(name="accessionlaborder_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="document_id", referencedColumnName="id")}
     *      )
     **/
    private $documents;

    //Image Title: [plain text]
    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $title;

    //Image Comment(s): [plain text]
    /**
     * @ORM\OneToMany(targetEntity="DocumentComment", mappedBy="documentContainer", cascade={"persist"})
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


    public function __construct() {
        $this->documents = new ArrayCollection();
        $this->imageComments = new ArrayCollection();
    }




    public function getDocuments()
    {
        return $this->documents;
    }
    public function addDocument($document)
    {
        if( $document && !$this->documents->contains($document) ) {
            $this->documents->add($document);
        }
        return $this;
    }
    public function removeDocument($document)
    {
        $this->documents->removeElement($document);
    }


    public function getImageComments()
    {
        return $this->imageComments;
    }
    public function addImageComment($imageComment)
    {
        if( $imageComment && !$this->imageComments->contains($imageComment) ) {
            $this->imageComments->add($imageComment);
        }
        return $this;
    }
    public function removeImageComment($imageComment)
    {
        $this->imageComments->removeElement($imageComment);
    }

    /**
     * @param mixed $comments
     */
    public function setComments($comments)
    {
        $this->comments = $comments;
    }

    /**
     * @return mixed
     */
    public function getComments()
    {
        return $this->comments;
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




}