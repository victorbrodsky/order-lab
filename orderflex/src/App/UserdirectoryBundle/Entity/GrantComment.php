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

namespace App\UserdirectoryBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Table(name: 'user_grantComment')]
#[ORM\Entity]
#[ORM\HasLifecycleCallbacks]
class GrantComment
{

    /**
     * @var integer
     */
    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    private $id;

    #[ORM\ManyToOne(targetEntity: 'Grant', inversedBy: 'comments')]
    #[ORM\JoinColumn(name: 'grant_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private $grant;

    #[ORM\ManyToOne(targetEntity: 'User')]
    #[ORM\JoinColumn(name: 'author_id', referencedColumnName: 'id')]
    private $author;

    /**
     * @var \DateTime
     */
    #[ORM\Column(type: 'datetime', nullable: true)]
    private $createdate;

    #[ORM\Column(type: 'text', nullable: true)]
    private $comment;




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

    #[ORM\PrePersist]
    public function setCreatedate()
    {
        $this->createdate = new \DateTime();;
    }

    /**
     * @return \DateTime
     */
    public function getCreatedate()
    {
        return $this->createdate;
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
     * @param mixed $grant
     */
    public function setGrant($grant)
    {
        $this->grant = $grant;
    }

    /**
     * @return mixed
     */
    public function getGrant()
    {
        return $this->grant;
    }




    public function __toString() {
        return "Grant comment: id=".$this->id.", text=".$this->comment.", grant name=".$this->getGrant()->getName().", grant id=".$this->getGrant()->getId()."<br>";
    }


}