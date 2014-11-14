<?php

namespace Oleg\UserdirectoryBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity
 * @ORM\Table(name="user_adminComment")
 */
class AdminComment extends BaseComment
{

    /**
     * @ORM\ManyToOne(targetEntity="User", inversedBy="adminComments")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $user;

    /**
     * @ORM\ManyToMany(targetEntity="Document")
     * @ORM\JoinTable(name="user_admincomm_document",
     *      joinColumns={@ORM\JoinColumn(name="comm_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="document_id", referencedColumnName="id", unique=true)}
     *      )
     **/
    protected $documents;


    public function __construct($author=null) {
        parent::__construct($author);

        $this->setType(self::TYPE_RESTRICTED);

        $this->documents = new ArrayCollection();
    }


    /**
     * @param mixed $user
     */
    public function setUser($user)
    {
        $this->user = $user;
    }

    /**
     * @return mixed
     */
    public function getUser()
    {
        return $this->user;
    }



    public function __toString() {
        return "Administrative Comment";
    }


}