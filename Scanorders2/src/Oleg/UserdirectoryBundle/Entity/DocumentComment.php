<?php

namespace Oleg\UserdirectoryBundle\Entity;


use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;


/**
 * @ORM\Entity
 * @ORM\Table(name="user_documentComment")
 */
class DocumentComment extends BaseUserAttributes {

//    /**
//     * @var integer
//     *
//     * @ORM\Column(name="id", type="integer")
//     * @ORM\Id
//     * @ORM\GeneratedValue(strategy="AUTO")
//     */
//    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="DocumentContainer", inversedBy="comments")
     */
    private $documentContainer;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $comment;



//    /**
//     * @param int $id
//     */
//    public function setId($id)
//    {
//        $this->id = $id;
//    }
//
//    /**
//     * @return int
//     */
//    public function getId()
//    {
//        return $this->id;
//    }

    /**
     * @param mixed $documentContainer
     */
    public function setDocumentContainer($documentContainer)
    {
        $this->documentContainer = $documentContainer;
    }

    /**
     * @return mixed
     */
    public function getDocumentContainer()
    {
        return $this->documentContainer;
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




}