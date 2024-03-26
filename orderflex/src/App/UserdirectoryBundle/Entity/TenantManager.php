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

//Ldap access request. Can be used for different sites with unique siteName

namespace App\UserdirectoryBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Table(name: 'user_tenantmanager')]
#[ORM\Entity]
class TenantManager
{

    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    private $id;

    #[ORM\ManyToOne(targetEntity: 'App\UserdirectoryBundle\Entity\User')]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id')]
    private $author;

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

    #[ORM\ManyToOne(targetEntity: 'App\UserdirectoryBundle\Entity\User')]
    #[ORM\JoinColumn(name: 'updatedby_id', referencedColumnName: 'id', nullable: true)]
    private $updatedby;


    // * Header Image : [DropZone field allowing upload of 1 image]
    #[ORM\JoinTable(name: 'user_tenantmanager_logo')]
    #[ORM\JoinColumn(name: 'tenantmanager_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    #[ORM\InverseJoinColumn(name: 'logo_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    #[ORM\ManyToMany(targetEntity: 'App\UserdirectoryBundle\Entity\Document', cascade: ['persist', 'remove'])]
    #[ORM\OrderBy(['createdate' => 'DESC'])]
    private $logos;

    // * ListOfHostedTenants as a List of hosted tenants, each one shown as a clickable link
    #[ORM\OneToMany(targetEntity: TenantList::class, mappedBy: 'tenantManager', cascade: ['persist', 'remove'])]
    private $tenants;


    // * Greeting Text : [free text form field, multi-line, accepts HTML, with default value:
    //  “Welcome to the View! The following organizations are hosted on this platform:”]
    #[ORM\Column(type: 'text', nullable: true)]
    private $greeting;

    // * Main text [free text form field, multi-line, accepts HTML, with default value: “Please log in to manage the tenants on this platform.”]
    #[ORM\Column(type: 'text', nullable: true)]
    private $maintext;

    // * Footer [free text form field, multi-line, accepts HTML, with default value: “[Home | <a href=”/about-us”>About Us</a> | Follow Us]”
    #[ORM\Column(type: 'text', nullable: true)]
    private $footer;


    public function __construct( $author=null ) {
        $this->setAuthor($author);
        $this->setCreatedate(new \DateTime());
        $this->tenants = new ArrayCollection();
        $this->logos = new ArrayCollection();
    }



    /**
     * @param mixed $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getAuthor()
    {
        return $this->author;
    }

    /**
     * @param mixed $author
     */
    public function setAuthor($author)
    {
        $this->author = $author;
    }

    /**
     * @param \DateTime $updatedate
     */
    #[ORM\PreUpdate]
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
     * @param \DateTime $createdate
     */
    #[ORM\PrePersist]
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
     * @param mixed $updatedby
     */
    public function setUpdatedby($updatedby)
    {
        $this->updatedby = $updatedby;
    }

    /**
     * @return mixed
     */
    public function getUpdatedby()
    {
        return $this->updatedby;
    }

    public function addLogo($item)
    {
        if( $item && !$this->logos->contains($item) ) {
            $this->logos->add($item);
            $item->createUseObject($this);
        }

        return $this;
    }
    public function removeLogo($item)
    {
        $this->logos->removeElement($item);
        $item->clearUseObject();
    }
    public function getLogos()
    {
        return $this->logos;
    }

    /**
     * @return mixed
     */
    public function getGreeting()
    {
        return $this->greeting;
    }

    /**
     * @param mixed $greeting
     */
    public function setGreeting($greeting)
    {
        $this->greeting = $greeting;
    }

    /**
     * @return mixed
     */
    public function getMaintext()
    {
        return $this->maintext;
    }

    /**
     * @param mixed $maintext
     */
    public function setMaintext($maintext)
    {
        $this->maintext = $maintext;
    }

    /**
     * @return mixed
     */
    public function getFooter()
    {
        return $this->footer;
    }

    /**
     * @param mixed $footer
     */
    public function setFooter($footer)
    {
        $this->footer = $footer;
    }

    public function getTenants()
    {
        return $this->tenants;
    }
    public function addTenant( $item )
    {
        if( !$this->tenants->contains($item) ) {
            $this->tenants->add($item);
            $item->setTenantManager($this);
        }

        return $this;
    }
    public function removeTenant($item)
    {
        if( $this->tenants->contains($item) ) {
            $this->tenants->removeElement($item);
            //$item->setTenantManager(null);
        }

        return $this;
    }


}