<?php

namespace Oleg\OrderformBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="scan_accessionLaborder")
 */
class AccessionLaborder extends AccessionArrayFieldAbstract
{

    /**
     * @ORM\ManyToOne(targetEntity="Accession", inversedBy="laborder")
     * @ORM\JoinColumn(name="accession_id", referencedColumnName="id", nullable=true)
     */
    protected $accession;

    //Lab Order Source
    //$source field - already exists in base abstract class

    //Lab Order ID
    //$orderinfo - already exists in base abstract class

    //Requisition Form Image(s): [upload multiple JPEGs]
    /**
     * @ORM\ManyToMany(targetEntity="Oleg\UserdirectoryBundle\Entity\Document")
     * @ORM\JoinTable(name="scan_accessionlaborder_document",
     *      joinColumns={@ORM\JoinColumn(name="accessionlaborder_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="document_id", referencedColumnName="id")}
     *      )
     **/
    private $documents;

    //Requisition Form Image Title: [plain text]
    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $imageTitle;

    //Requisition Form Image Comment(s): [plain text]
    /**
     * @ORM\OneToMany(targetEntity="LaborderComment", mappedBy="laborder", cascade={"persist"})
     */
    private $imageComments;

    //Requisition Form Image Device: [Select2, "Generic Desktop Scanner" are the only two values; link to Equipment table, filter by Type="Requisition Form Scanner"]
    /**
     * @ORM\ManyToOne(targetEntity="Oleg\UserdirectoryBundle\Entity\Equipment")
     */
    private $imageDevice;

    //Requisition Form Image Date & Time: [plain text]
    /**
     * @var \DateTime
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $imageDatetime;

    //Requisition Form Image Scanned By: [Select2; one user from the list of users]
    /**
     * @ORM\ManyToOne(targetEntity="Oleg\UserdirectoryBundle\Entity\User")
     */
    private $imageProvider;

    //Attach "Progress & Comments" page to the Lab Order
    //TODO: History (Progress & Comments) object is linked to the order



    public function __construct( $status = 'valid', $provider = null, $source = null ) {
        parent::__construct($status,$provider,$source);

        $this->documents = new ArrayCollection();
        $this->imageComments = new ArrayCollection();
    }


    /**
     * @param \DateTime $imageDatetime
     */
    public function setImageDatetime($imageDatetime)
    {
        $this->imageDatetime = $imageDatetime;
    }

    /**
     * @return \DateTime
     */
    public function getImageDatetime()
    {
        return $this->imageDatetime;
    }

    /**
     * @param mixed $imageDevice
     */
    public function setImageDevice($imageDevice)
    {
        $this->imageDevice = $imageDevice;
    }

    /**
     * @return mixed
     */
    public function getImageDevice()
    {
        return $this->imageDevice;
    }

    /**
     * @param mixed $imageProvider
     */
    public function setImageProvider($imageProvider)
    {
        $this->imageProvider = $imageProvider;
    }

    /**
     * @return mixed
     */
    public function getImageProvider()
    {
        return $this->imageProvider;
    }

    /**
     * @param mixed $imageTitle
     */
    public function setImageTitle($imageTitle)
    {
        $this->imageTitle = $imageTitle;
    }

    /**
     * @return mixed
     */
    public function getImageTitle()
    {
        return $this->imageTitle;
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
    public function removeDocuments($document)
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


}