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

    const TYPE_PUBLIC = 0;
    const TYPE_PRIVATE = 1;
    const TYPE_RESTRICTED = 2;

    /**
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumn(name="author", referencedColumnName="id")
     */
    protected $author;

    /**
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumn(name="updateAuthor", referencedColumnName="id", nullable=true)
     */
    protected $updateAuthor;

    /**
     * @var array
     * @ORM\Column(type="array", nullable=true)
     */
    protected $updateAuthorRoles = array();

    /**
     * type: public, valid, restricted
     * @ORM\Column(type="integer", options={"default"=0})
     */
    protected $type;

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


    public function __construct() {
        $this->setType(self::TYPE_PUBLIC);
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


}