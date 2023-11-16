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

#[ORM\Table(name: 'transres_visualinfo')]
#[ORM\Entity]
class VisualInfo {

    /**
     * @var integer
     */
    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    protected $id;

    #[ORM\ManyToOne(targetEntity: 'App\UserdirectoryBundle\Entity\User')]
    #[ORM\JoinColumn(name: 'author', referencedColumnName: 'id')]
    protected $author;

    #[ORM\ManyToOne(targetEntity: 'App\UserdirectoryBundle\Entity\User')]
    #[ORM\JoinColumn(name: 'updateAuthor', referencedColumnName: 'id', nullable: true)]
    protected $updateAuthor;

    /**
     * @var \DateTime
     */
    #[ORM\Column(type: 'datetime', nullable: true)]
    protected $createdate;

    /**
     * @var \DateTime
     */
    #[ORM\Column(type: 'datetime', nullable: true)]
    protected $updatedate;


    /**
     * Indicates the order in the list
     */
    #[ORM\Column(type: 'integer', nullable: true)]
    protected $orderinlist;

    #[ORM\ManyToOne(targetEntity: 'AntibodyList', inversedBy: 'visualInfos')]
    #[ORM\JoinColumn(name: 'antibodylist_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    protected $antibody;

    
    #[ORM\JoinTable(name: 'transres_visualinfo_document')]
    #[ORM\JoinColumn(name: 'visualinfo_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    #[ORM\InverseJoinColumn(name: 'document_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    #[ORM\ManyToMany(targetEntity: 'App\UserdirectoryBundle\Entity\Document', cascade: ['persist', 'remove'])]
    #[ORM\OrderBy(['createdate' => 'DESC'])]
    protected $documents;

    /**
     * Comment for document
     *
     * @var string
     */
    #[ORM\Column(type: 'text', nullable: true)]
    private $comment;

    /**
     * Catalog
     * catalog (preconfigured, not editable) can be established based on the antibody ID (existing) and the # of panel is accruing.
     *
     * @var string
     */
    #[ORM\Column(type: 'string', nullable: true)]
    private $catalog;

    //Add: Region of Interest Image(s) [Up to 10 images, up to 10MB each]
    //Add: Whole Slide Image(s) [Up to 2 images, up to 2GB each]
    //This is why we need three separate fields.
    // The only alternative would be to allow the user to specify
    // the uploaded file type (document, region of interest,
    // whole slide image) in a user-friendly way and then show
    // the upload type next to the uploaded file
    //#[ORM\ManyToOne(targetEntity: 'VisualInfoUploadTypeList')]
    //#[ORM\JoinColumn(name: 'uploadedtype', referencedColumnName: 'id', nullable: true)]
    //private $uploadedType;

    //uploadedType: document, region of interest, whole slide image
    #[ORM\Column(type: 'string', nullable: true)]
    private $uploadedType;




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

    #[ORM\PreUpdate]
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

    /**
     * @return mixed
     */
    public function getUploadedType()
    {
        return $this->uploadedType;
    }

    /**
     * @param mixed $uploadedType
     */
    public function setUploadedType($uploadedType)
    {
        $this->uploadedType = $uploadedType;
    }


    public function isEmpty() {
        if( $this->getComment() ) {
            //echo "comment=[".$this->getComment()."]<br>";
            return false;
        }
        if( $this->getCatalog() ) {
            //echo "catalog=[".$this->getCatalog()."]<br>";
            return false;
        }
        if( count($this->getDocuments()) != 0 ) {
            //echo "documents=[".count($this->getDocuments())."]<br>";
            return false;
        }
        return true;
    }


    public function __toString() {
        return "Visual Info";
    }


}