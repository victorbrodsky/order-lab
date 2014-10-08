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
abstract class BaseUserAttributes {

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
     * @ORM\ManyToOne(targetEntity="Oleg\UserdirectoryBundle\Entity\User")
     * @ORM\JoinColumn(name="updateAuthor", referencedColumnName="id", nullable=true)
     */
    protected $updateAuthor;

    /**
     * @var array
     * @ORM\Column(type="array", nullable=true)
     */
    protected $updateAuthorRoles = array();

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
     * @var \DateTime
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $pgystart;

    /**
     * @var \DateTime
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $pgylevel;


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
    public function getOrderinlist()
    {
        return $this->orderinlist;
    }

    /**
     * @param \DateTime $pgylevel
     */
    public function setPgylevel($pgylevel)
    {
        $this->pgylevel = $pgylevel;
    }

    /**
     * @return \DateTime
     */
    public function getPgylevel()
    {
        return $this->pgylevel;
    }

    /**
     * @param \DateTime $pgystart
     */
    public function setPgystart($pgystart)
    {
        $this->pgystart = $pgystart;
    }

    /**
     * @return \DateTime
     */
    public function getPgystart()
    {
        return $this->pgystart;
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
    public function getUpdateAuthor()
    {
        return $this->updateAuthor;
    }

    /**
     * @ORM\PreUpdate
     */
    public function setUpdatedate()
    {
        $this->updatedate = new \DateTime();
    }

    /**
     * @return \DateTime
     */
    public function getUpdatedate()
    {
        return $this->updatedate;
    }

    /**
     * @return mixed
     */
    public function getUpdateAuthorRoles()
    {
        return $this->updateAuthorRoles;
    }


    public function setUpdateAuthorRoles($roles) {
        foreach( $roles as $role ) {
            $this->addUpdateAuthorRole($role."");
        }
    }

    public function addUpdateAuthorRole($role) {
        $role = strtoupper($role);
        if( !in_array($role, $this->updateAuthorRoles, true) ) {
            $this->updateAuthorRoles[] = $role;
        }
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