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

/**
 * Created by JetBrains PhpStorm.
 * User: oli2002
 * Date: 9/10/14
 * Time: 5:46 PM
 * To change this template use File | Settings | File Templates.
 */

namespace App\UserdirectoryBundle\Entity;

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

    /**
     * @ORM\Column(type="time", nullable=true)
     */
    private $time;

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

    /**
     * @ORM\ManyToMany(targetEntity="Link", cascade={"persist","remove"})
     * @ORM\JoinTable(name="user_documentcontainer_link",
     *      joinColumns={@ORM\JoinColumn(name="documentcontainer_id", referencedColumnName="id", onDelete="cascade")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="link_id", referencedColumnName="id", onDelete="cascade")}
     *      )
     **/
    private $links;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $imageId;

    /**
     * @ORM\ManyToOne(targetEntity="SourceSystemList")
     * @ORM\JoinColumn(name="source_id", referencedColumnName="id", nullable=true)
     */
    private $source;


    public function __construct($provider=null) {
        $this->documents = new ArrayCollection();
        $this->comments = new ArrayCollection();
        $this->links = new ArrayCollection();

        if( $provider ) {
            $this->setProvider($provider);
        }

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
            $document->createUseObject($this);
        }
        return $this;
    }
    public function removeDocument($document)
    {
        $this->documents->removeElement($document);
        $document->clearUseObject();
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
     * @param mixed $time
     */
    public function setTime($time)
    {
        $this->time = $time;
    }

    /**
     * @return mixed
     */
    public function getTime()
    {
        return $this->time;
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

    public function getLinks()
    {
        return $this->links;
    }
    public function addLink($item)
    {
        if( $item && !$this->links->contains($item) ) {
            $this->links->add($item);
        }
        return $this;
    }
    public function removeLink($item)
    {
        $this->links->removeElement($item);
    }

    /**
     * @param mixed $source
     */
    public function setSource($source)
    {
        $this->source = $source;
    }

    /**
     * @return mixed
     */
    public function getSource()
    {
        return $this->source;
    }

    /**
     * @param mixed $imageId
     */
    public function setImageId($imageId)
    {
        $this->imageId = $imageId;
    }

    /**
     * @return mixed
     */
    public function getImageId()
    {
        return $this->imageId;
    }



    public function __toString() {
        return "DocumentContainer: "."documents count=".count($this->getDocuments()).",comments count=".count($this->getComments())."<br>";
    }
}