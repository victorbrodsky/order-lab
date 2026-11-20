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

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Table(name: 'wikiportal_page')]
#[ORM\UniqueConstraint(name: 'wikiportal_page_slug_parent_unique', columns: ['slug', 'parent_id'])]
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

    /**
     * Self-referencing parent. NULL = top-level page (the single parentless
     * page flagged isHomepage renders at '/')
     *
     * @var WikiPage
     */
    #[ORM\ManyToOne(targetEntity: WikiPage::class, inversedBy: 'children')]
    #[ORM\JoinColumn(name: 'parent_id', referencedColumnName: 'id', nullable: true, onDelete: 'CASCADE')]
    private $parent;

    /**
     * @var Collection|WikiPage[]
     */
    #[ORM\OneToMany(targetEntity: WikiPage::class, mappedBy: 'parent')]
    #[ORM\OrderBy(['sortOrder' => 'ASC', 'title' => 'ASC'])]
    private $children;

    /**
     * Denormalized path built from the ancestor slugs (e.g. 'home/docs/setup').
     * Unique, used for lookup by /page/{path} route
     *
     * @var string
     */
    #[ORM\Column(type: 'string', unique: true, nullable: true)]
    private $fullPath;

    /**
     * The single home page rendered at '/wiki-portal/'
     *
     * @var boolean
     */
    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private $isHomepage = false;

    /**
     * Ordering among siblings
     *
     * @var integer
     */
    #[ORM\Column(type: 'integer', nullable: true)]
    private $sortOrder;

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
        $this->children = new ArrayCollection();
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

    public function getParent()
    {
        return $this->parent;
    }

    public function setParent($parent)
    {
        $this->parent = $parent;
        return $this;
    }

    public function getChildren()
    {
        return $this->children;
    }

    public function addChild(WikiPage $child)
    {
        if (!$this->children->contains($child)) {
            $this->children->add($child);
            $child->setParent($this);
        }
        return $this;
    }

    public function removeChild(WikiPage $child)
    {
        if ($this->children->removeElement($child)) {
            if ($child->getParent() === $this) {
                $child->setParent(null);
            }
        }
        return $this;
    }

    public function getFullPath()
    {
        return $this->fullPath;
    }

    public function setFullPath($fullPath)
    {
        $this->fullPath = $fullPath;
        return $this;
    }

    public function getIsHomepage()
    {
        return $this->isHomepage;
    }

    public function setIsHomepage($isHomepage)
    {
        $this->isHomepage = $isHomepage;
        return $this;
    }

    public function getSortOrder()
    {
        return $this->sortOrder;
    }

    public function setSortOrder($sortOrder)
    {
        $this->sortOrder = $sortOrder;
        return $this;
    }

    /**
     * Ordered ancestor chain (root first, self last) for breadcrumbs
     */
    public function getAncestors()
    {
        $ancestors = array();
        $node = $this;
        while ($node) {
            array_unshift($ancestors, $node);
            $node = $node->getParent();
        }
        return $ancestors;
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
