<?php
/**
 * Copyright (c) 2017 Cornell University
 *
 *  Licensed under the Apache License, Version 2.0 (the "License");
 *  you may not use this file except in compliance with the License.
 *  You may obtain a copy of the License at
 *
 *  http://www.apache.org/licenses/LICENSE-2.0
 *
 *  Unless required by applicable law or agreed to in writing, software
 *  distributed under the License is distributed on an "AS IS" BASIS,
 *  WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 *  See the License for the specific language governing permissions and
 *  limitations under the License.
 */

namespace Oleg\UserdirectoryBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="user_identifier")
 */
class Identifier
{

    const STATUS_UNVERIFIED = 0;    //unverified (not trusted)
    const STATUS_VERIFIED = 1;      //verified by admin

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="IdentifierTypeList")
     * @ORM\JoinColumn(name="keytype_id", referencedColumnName="id", nullable=true)
     **/
    private $keytype;

    /**
     * Note: this is a link to OrderformBundle bundle file App\OrderformBundle\Entity\MrnType.
     *
     * @ORM\ManyToOne(targetEntity="App\OrderformBundle\Entity\MrnType", cascade={"persist"})
     * @ORM\JoinColumn(name="keytypemrn_id", referencedColumnName="id", nullable=true)
     */
    protected $keytypemrn;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $field;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $link;

    /**
     * @ORM\ManyToOne(targetEntity="Credentials", inversedBy="identifiers")
     * @ORM\JoinColumn(name="credentials_id", referencedColumnName="id", onDelete="CASCADE", nullable=true)
     */
    private $credentials;

    /**
     * status: valid, invalid
     * @ORM\Column(type="integer", options={"default" = 0}, nullable=true)
     */
    private $status;

    /**
     * public, private
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $publiclyVisible;

    /**
     * Identifier enables system/service access
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $enableAccess;



    public function __construct() {
        $this->setStatus(self::STATUS_UNVERIFIED);
    }


    /**
     * @param mixed $keytypemrn
     */
    public function setKeytypemrn($keytypemrn)
    {
        $this->keytypemrn = $keytypemrn;
    }

    /**
     * @return mixed
     */
    public function getKeytypemrn()
    {
        return $this->keytypemrn;
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
     * @param mixed $field
     */
    public function setField($field)
    {
        $this->field = $field;
    }

    /**
     * @return mixed
     */
    public function getField()
    {
        return $this->field;
    }

    /**
     * @param mixed $keytype
     */
    public function setKeytype($keytype)
    {
        $this->keytype = $keytype;
    }

    /**
     * @return mixed
     */
    public function getKeytype()
    {
        return $this->keytype;
    }

    /**
     * @param mixed $link
     */
    public function setLink($link)
    {
        $this->link = $link;
    }

    /**
     * @return mixed
     */
    public function getLink()
    {
        return $this->link;
    }

    /**
     * @param mixed $publiclyVisible
     */
    public function setPubliclyVisible($publiclyVisible)
    {
        $this->publiclyVisible = $publiclyVisible;
    }

    /**
     * @return mixed
     */
    public function getPubliclyVisible()
    {
        return $this->publiclyVisible;
    }

    /**
     * @param mixed $enableAccess
     */
    public function setEnableAccess($enableAccess)
    {
        $this->enableAccess = $enableAccess;
    }

    /**
     * @return mixed
     */
    public function getEnableAccess()
    {
        return $this->enableAccess;
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


    public function __toString() {
        return "Identifier";
    }


}