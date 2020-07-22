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

namespace App\TranslationalResearchBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity
 * @ORM\Table(name="transres_visualinfo")
 */
class VisualInfo {

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\UserdirectoryBundle\Entity\User")
     * @ORM\JoinColumn(name="author", referencedColumnName="id")
     */
    protected $author;

    /**
     * @ORM\ManyToOne(targetEntity="App\UserdirectoryBundle\Entity\User")
     * @ORM\JoinColumn(name="updateAuthor", referencedColumnName="id", nullable=true)
     */
    protected $updateAuthor;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $createdate;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $updatedate;


    /**
     * Indicates the order in the list
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $orderinlist;

    /**
     * @ORM\ManyToOne(targetEntity="AntibodyList", inversedBy="visualInfos")
     * @ORM\JoinColumn(name="antibodylist_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $antibody;


//    /**
//     * @ORM\ManyToMany(targetEntity="App\UserdirectoryBundle\Entity\Document", cascade={"persist","remove"})
//     * @ORM\JoinTable(name="user_visualinfo_document",
//     *      joinColumns={@ORM\JoinColumn(name="visualinfo_id", referencedColumnName="id")},
//     *      inverseJoinColumns={@ORM\JoinColumn(name="document_id", referencedColumnName="id", unique=true, onDelete="CASCADE")}
//     *      )
//     * @ORM\OrderBy({"createdate" = "DESC"})
//     **/
//    protected $documents;
    /**
     * @ORM\ManyToMany(targetEntity="App\UserdirectoryBundle\Entity\Document")
     * @ORM\JoinTable(name="transres_visualinfo_document",
     *      joinColumns={@ORM\JoinColumn(name="visualinfo_id", referencedColumnName="id", onDelete="CASCADE")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="document_id", referencedColumnName="id", onDelete="CASCADE")}
     *      )
     * @ORM\OrderBy({"createdate" = "DESC"})
     **/
    protected $documents;

    /**
     * Comment for document
     *
     * @var string
     * @ORM\Column(type="text", nullable=true)
     */
    private $comment;

    /**
     * Catalog
     * catalog (preconfigured, not editable) can be established based on the antibody ID (existing) and the # of panel is accruing.
     *
     * @var string
     * @ORM\Column(type="string", nullable=true)
     */
    private $catalog;


    public function __construct( $author=null ) {
        $this->documents = new ArrayCollection();
        
        $this->setAuthor($author);
        $this->setCreatedate(new \DateTime());
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
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
    public function getAuthor()
    {
        return $this->author;
    }

    /**
     * @param mixed $author
     */
    public function setAuthor($author)
    {
        $this->author = $author;
    }

    /**
     * @return mixed
     */
    public function getUpdateAuthor()
    {
        return $this->updateAuthor;
    }

    /**
     * @param mixed $updateAuthor
     */
    public function setUpdateAuthor($updateAuthor)
    {
        $this->updateAuthor = $updateAuthor;
    }

    /**
     * @return mixed
     */
    public function getCreatedate()
    {
        return $this->createdate;
    }

    /**
     * @param mixed $createdate
     */
    public function setCreatedate($createdate)
    {
        $this->createdate = $createdate;
    }

    /**
     * @return mixed
     */
    public function getUpdatedate()
    {
        return $this->updatedate;
    }

    /**
     * @ORM\PreUpdate
     */
    public function setUpdatedate()
    {
        $this->updatedate = new \DateTime();
    }

    /**
     * @return mixed
     */
    public function getOrderinlist()
    {
        return $this->orderinlist;
    }

    /**
     * @param mixed $orderinlist
     */
    public function setOrderinlist($orderinlist)
    {
        $this->orderinlist = $orderinlist;
    }

    /**
     * @return mixed
     */
    public function getAntibody()
    {
        return $this->antibody;
    }

    /**
     * @param mixed $antibody
     */
    public function setAntibody($antibody)
    {
        $this->antibody = $antibody;
    }


    /**
     * Add document
     *
     * @param \App\UserdirectoryBundle\Entity\Document $document
     * @return Comment
     */
    public function addDocument($document)
    {
        if( $document && !$this->documents->contains($document) ) {
            $this->documents->add($document);
            $document->createUseObject($this);
        }

        return $this;
    }
    /**
     * Remove document
     *
     * @param \App\UserdirectoryBundle\Entity\Document $document
     */
    public function removeDocument($document)
    {
        $this->documents->removeElement($document);
        $document->clearUseObject();
    }
    /**
     * Get documents
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getDocuments()
    {
        return $this->documents;
    }

    public function setDocuments($documents)
    {
        $this->documents = new ArrayCollection();
        foreach( $documents as $document ) {
            $this->addDocument($document);
        }
    }

    /**
     * @return mixed
     */
    public function getComment()
    {
        return $this->comment;
    }

    /**
     * @param mixed $comment
     */
    public function setComment($comment)
    {
        $this->comment = $comment;
    }

    /**
     * @return string
     */
    public function getCatalog()
    {
        return $this->catalog;
    }

    /**
     * @param string $catalog
     */
    public function setCatalog($catalog)
    {
        $this->catalog = $catalog;
    }




    public function __toString() {
        return "Visual Info";
    }


}