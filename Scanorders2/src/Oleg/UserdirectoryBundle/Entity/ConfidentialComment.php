<?php

namespace Oleg\UserdirectoryBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="user_confidentialComment")
 */
class ConfidentialComment extends BaseComment
{

    /**
     * @ORM\ManyToOne(targetEntity="User", inversedBy="confidentialComments")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $user;

    public function __construct($author=null) {
        parent::__construct($author);

        $this->setType(self::TYPE_RESTRICTED);
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
        return "Confidential Comment";
    }


}