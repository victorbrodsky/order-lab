<?php

namespace Oleg\UserdirectoryBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="user_adminComment")
 */
class AdminComment extends BaseComment
{

    /**
     * @ORM\ManyToOne(targetEntity="Credentials", inversedBy="adminComments")
     * @ORM\JoinColumn(name="credentials_id", referencedColumnName="id", onDelete="CASCADE", nullable=true)
     */
    protected $credentials;

    public function __construct($author=null) {
        parent::__construct($author);

        $this->setType(self::TYPE_RESTRICTED);
    }

    /**
     * @param mixed $credentials
     */
    public function setCredentials($credentials)
    {
        $this->credentials = $credentials;
    }

    /**
     * @return mixed
     */
    public function getCredentials()
    {
        return $this->credentials;
    }



    public function __toString() {
        return "Administrative Comment";
    }


}