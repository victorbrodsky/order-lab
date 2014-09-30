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
 * @ORM\HasLifecycleCallbacks
 */
abstract class BaseComment {

    const TYPE_PUBLIC = 0;          //access by anybody
    const TYPE_PRIVATE = 1;         //access by user
    const TYPE_RESTRICTED = 2;      //access by admin

    const STATUS_UNVERIFIED = 0;    //unverified (not trusted)
    const STATUS_VERIFIED = 1;      //verified by admin

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="Oleg\UserdirectoryBundle\Entity\User")
     * @ORM\JoinColumn(name="author", referencedColumnName="id")
     */
    protected $author;

    /**
     * type: public, private, restricted
     * @ORM\Column(type="integer", options={"default" = 0}, nullable=true)
     */
    protected $type;

    /**
     * status: valid, invalid
     * @ORM\Column(type="integer", options={"default" = 0}, nullable=true)
     */
    protected $status;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $createdate;

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


    public function __construct($author=null) {
        $this->setAuthor($author);
        $this->setType(self::TYPE_PUBLIC);
        $this->setStatus(self::STATUS_UNVERIFIED);
    }


    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
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
    public function getAuthor()
    {
        return $this->author;
    }

    /**
     * @ORM\PrePersist
     */
    public function setCreatedate()
    {
        $this->createdate = new \DateTime();
    }

    /**
     * @return \DateTime
     */
    public function getCreatedate()
    {
        return $this->createdate;
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



    /**
     * @param mixed $type
     */
    public function setType($type)
    {
        //echo "before set type=".$this->getType()."<br>";

        if( $type == $this->getType() ) {
            //echo "return set type=".$this->getType()."<br>";
            return;
        }

        if( $this->getType() == self::TYPE_RESTRICTED ) {
            throw new \Exception( 'Can not change type for restricted entity. type='.$type );
        } else {
            $this->type = $type;
        }
    }

    /**
     * @return mixed
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param mixed $status
     */
    public function setStatus($status)
    {
        $this->status = $status;
    }

    /**
     * @return mixed
     */
    public function getStatus()
    {
        return $this->status;
    }


    public function getStatusStr()
    {
        return $this->getStatusStrByStatus($this->getStatus());
    }

    public function getStatusStrByStatus($status)
    {
        $str = $status;

        if( $status == self::STATUS_UNVERIFIED )
            $str = "Pending Administrative Review";

        if( $status == self::STATUS_VERIFIED )
            $str = "Verified by Administration";

        return $str;
    }


}