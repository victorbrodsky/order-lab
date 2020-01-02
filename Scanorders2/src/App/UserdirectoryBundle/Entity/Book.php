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

use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity
 * @ORM\Table(name="user_book")
 */
class Book extends BaseUserAttributes
{

    /**
     * @ORM\ManyToMany(targetEntity="User", mappedBy="books")
     **/
    private $users;


    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $citation;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $isbn;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $link;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $publicationDate;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $comment;

    /**
     * @ORM\ManyToOne(targetEntity="AuthorshipRoles")
     * @ORM\JoinColumn(referencedColumnName="id", nullable=true)
     */
    private $authorshipRole;



    public function __construct($author=null) {
        parent::__construct($author);
        $this->users = new ArrayCollection();
    }




    public function addUser($item)
    {
        if( $item && !$this->users->contains($item) ) {
            $this->users->add($item);
        }
        return $this;
    }
    public function removeUser($item)
    {
        $this->users->removeElement($item);
    }
    public function getUsers()
    {
        return $this->users;
    }

    /**
     * @param mixed $authorshipRole
     */
    public function setAuthorshipRole($authorshipRole)
    {
        $this->authorshipRole = $authorshipRole;
    }

    /**
     * @return mixed
     */
    public function getAuthorshipRole()
    {
        return $this->authorshipRole;
    }

    /**
     * @param mixed $citation
     */
    public function setCitation($citation)
    {
        $this->citation = $citation;
    }

    /**
     * @return mixed
     */
    public function getCitation()
    {
        return $this->citation;
    }

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
     * @param mixed $isbn
     */
    public function setIsbn($isbn)
    {
        $this->isbn = $isbn;
    }

    /**
     * @return mixed
     */
    public function getIsbn()
    {
        return $this->isbn;
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
     * @param \DateTime $publicationDate
     */
    public function setPublicationDate($publicationDate)
    {
        $this->publicationDate = $publicationDate;
    }

    /**
     * @return \DateTime
     */
    public function getPublicationDate()
    {
        return $this->publicationDate;
    }


    public function __toString() {
        return "Book";
    }

}