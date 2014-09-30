<?php

namespace Oleg\UserdirectoryBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="user_privateComment")
 */
class PrivateComment extends BaseComment
{

    /**
     * @ORM\ManyToOne(targetEntity="Credentials", inversedBy="privateComments")
     * @ORM\JoinColumn(name="credentials_id", referencedColumnName="id", onDelete="CASCADE", nullable=true)
     */
    protected $credentials;

    public function __construct($author=null) {
        parent::__construct($author);

        $this->setType(self::TYPE_PRIVATE);
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
        return "Private Comment";
    }


}