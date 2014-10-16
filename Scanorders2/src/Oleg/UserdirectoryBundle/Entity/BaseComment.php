<?php
/**
 * Created by JetBrains PhpStorm.
 * User: oli2002
 * Date: 10/24/13
 * Time: 12:14 PM
 * To change this template use File | Settings | File Templates.
 */

namespace Oleg\UserdirectoryBundle\Entity;

use Doctrine\ORM\Mapping as ORM;


/**
 * @ORM\MappedSuperclass
 */
abstract class BaseComment extends BaseUserAttributes {


    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $comment;

    /**
     * @ORM\ManyToOne(targetEntity="CommentTypeList", cascade={"persist"})
     **/
    private $commentType;

    /**
     * @ORM\ManyToOne(targetEntity="CommentSubTypeList", cascade={"persist"})
     **/
    private $commentSubType;


    public function __construct($author=null) {
        parent::__construct($author);
    }



    /**
     * @param mixed $comment
     */
    public function setComment($comment)
    {
        $this->comment = $comment;
    }

    /**
     * @return mixed
     */
    public function getComment()
    {
        return $this->comment;
    }

    /**
     * @param mixed $commentSubType
     */
    public function setCommentSubType($commentSubType)
    {
        $this->commentSubType = $commentSubType;
    }
    public function setCommentSubTypeList($commentSubType)
    {
        $this->setCommentSubType($commentSubType);
    }

    /**
     * @return mixed
     */
    public function getCommentSubType()
    {
        return $this->commentSubType;
    }

    /**
     * @param mixed $commentType
     */
    public function setCommentType($commentType)
    {
        $this->commentType = $commentType;
    }
    public function setCommentTypeList($commentType)
    {
        $this->setCommentType($commentType);
    }

    /**
     * @return mixed
     */
    public function getCommentType()
    {
        return $this->commentType;
    }


    /**
     * Add document
     *
     * @param \Oleg\OrderformBundle\Entity\Document $document
     * @return Part
     */
    public function addDocument($document)
    {
        if( $document == null ) {
            $document = new Document();
        }

        if( !$this->documents->contains($document) ) {
            $this->documents->add($document);
            $document->setPart($this);
        }

        return $this;
    }
    /**
     * Remove document
     *
     * @param \Oleg\OrderformBundle\Entity\Document $document
     */
    public function removeDocument($document)
    {
        $this->documents->removeElement($document);
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
        //$this->documents = new ArrayCollection();
        return $this->documents = $documents;
    }

}