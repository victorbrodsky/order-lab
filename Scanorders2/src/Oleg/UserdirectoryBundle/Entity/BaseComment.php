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
     * @ORM\ManyToOne(targetEntity="CommentTypeList")
     **/
    private $commentType;

    /**
     * @ORM\ManyToOne(targetEntity="CommentSubTypeList")
     **/
    private $commentSubType;



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

    /**
     * @return mixed
     */
    public function getCommentType()
    {
        return $this->commentType;
    }





}