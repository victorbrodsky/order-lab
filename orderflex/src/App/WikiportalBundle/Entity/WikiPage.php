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

namespace App\WikiportalBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Table(name: 'wikiportal_page')]
#[ORM\Entity]
#[ORM\HasLifecycleCallbacks]
class WikiPage
{
    /**
     * @var integer
     */
    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'SEQUENCE')]
    private $id;

    /**
     * @var string
     */
    #[ORM\Column(type: 'string', nullable: false)]
    #[Assert\NotBlank]
    private $title;

    /**
     * URL-friendly unique identifier
     *
     * @var string
     */
    #[ORM\Column(type: 'string', unique: true, nullable: false)]
    #[Assert\NotBlank]
    private $slug;

    /**
     * Page content as HTML produced by the Tiptap editor
     *
     * @var string
     */
    #[ORM\Column(type: 'text', nullable: true)]
    private $body;

    #[ORM\ManyToOne(targetEntity: 'App\UserdirectoryBundle\Entity\User')]
    #[ORM\JoinColumn(referencedColumnName: 'id', nullable: true)]
    private $creator;

    #[ORM\ManyToOne(targetEntity: 'App\UserdirectoryBundle\Entity\User')]
    #[ORM\JoinColumn(referencedColumnName: 'id', nullable: true)]
    private $updateuser;

    /**
     * @var \DateTime
     */
    #[ORM\Column(type: 'datetime', nullable: true)]
    private $createdate;

    /**
     * @var \DateTime
     */
    #[ORM\Column(type: 'datetime', nullable: true)]
    private $updatedate;

    public function __construct($creator = null)
    {
        $this->creator = $creator;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function setTitle($title)
    {
        $this->title = $title;
        return $this;
    }

    public function getSlug()
    {
        return $this->slug;
    }

    public function setSlug($slug)
    {
        $this->slug = $slug;
        return $this;
    }

    public function getBody()
    {
        return $this->body;
    }

    public function setBody($body)
    {
        $this->body = $body;
        return $this;
    }

    public function getCreator()
    {
        return $this->creator;
    }

    public function setCreator($creator)
    {
        $this->creator = $creator;
        return $this;
    }

    public function getUpdateuser()
    {
        return $this->updateuser;
    }

    public function setUpdateuser($updateuser)
    {
        $this->updateuser = $updateuser;
        return $this;
    }

    public function getCreatedate()
    {
        return $this->createdate;
    }

    public function setCreatedate($createdate)
    {
        $this->createdate = $createdate;
        return $this;
    }

    public function getUpdatedate()
    {
        return $this->updatedate;
    }

    public function setUpdatedate($updatedate)
    {
        $this->updatedate = $updatedate;
        return $this;
    }

    #[ORM\PrePersist]
    public function setCreatedateValue()
    {
        if (!$this->getCreatedate()) {
            $this->setCreatedate(new \DateTime());
        }
        $this->setUpdatedate(new \DateTime());
    }

    #[ORM\PreUpdate]
    public function setUpdatedateValue()
    {
        $this->setUpdatedate(new \DateTime());
    }

    public function __toString()
    {
        return "Wiki Page: id=" . $this->id . ", title=" . $this->title;
    }
}
