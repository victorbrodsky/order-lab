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

/**
 * Created by PhpStorm.
 * User: ch3
 * Date: 10/6/2017
 * Time: 1:46 PM
 */

namespace Oleg\UserdirectoryBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use FOS\CommentBundle\Entity\Comment as FosBaseComment;
use FOS\CommentBundle\Model\SignedCommentInterface;
use Symfony\Component\Security\Core\User\UserInterface;



/**
 * @ORM\Entity
 * @ORM\Table(name="user_fosComment")
 * @ORM\ChangeTrackingPolicy("DEFERRED_EXPLICIT")
 */
class FosComment extends FosBaseComment implements SignedCommentInterface
{

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * Thread of this comment
     *
     * @var Thread
     * @ORM\ManyToOne(targetEntity="Oleg\UserdirectoryBundle\Entity\FosThread")
     */
    protected $thread;

    /**
     * Author of the comment
     *
     * @var User
     * @ORM\ManyToOne(targetEntity="Oleg\UserdirectoryBundle\Entity\User")
     */
    protected $author;

    /**
     * @var string
     * @ORM\Column(type="string", nullable=true)
     */
    protected $authorType;

    /**
     * @var string
     * @ORM\Column(type="string", nullable=true)
     */
    protected $authorTypeDescription;

    /**
     * @var string
     * @ORM\Column(type="string", nullable=true)
     */
    protected $prefix;



    /**
     * @return string
     */
    public function getAuthorType()
    {
        return $this->authorType;
    }

    /**
     * @param string $authorType
     */
    public function setAuthorType($authorType)
    {
        $this->authorType = $authorType;
    }

    /**
     * @return string
     */
    public function getAuthorTypeDescription()
    {
        return $this->authorTypeDescription;
    }

    /**
     * @param string $authorTypeDescription
     */
    public function setAuthorTypeDescription($authorTypeDescription)
    {
        $this->authorTypeDescription = $authorTypeDescription;
    }



    public function setAuthor(UserInterface $author)
    {
        $this->author = $author;
    }

    public function getAuthor()
    {
        return $this->author;
    }

    public function getAuthorName()
    {
        if (null === $this->getAuthor()) {
            return 'Anonymous';
        }

        return $this->getAuthor()->getUsernameOptimal();  //getUsername();
    }

    public function getAuthorNameByLoggedUser($loggedUser)
    {
        if (null === $this->getAuthor()) {
            return 'Anonymous';
        }

        if( $loggedUser->getId() != $this->getAuthor()->getId() ) {
            return 'Anonymous';
        }

        if( $loggedUser->getId() == $this->getAuthor()->getId() ) {
            return $this->getAuthor()->getUsernameOptimal();  //getUsername();
        }

        return $this->getAuthor()->getUsernameOptimal();  //getUsername();
    }

    public function setBody($body)
    {
//        if( $this->getBody() ) {
//            $body = $this->getBody() ." ". $body;
//        }
        $this->body = $body;
    }
    /**
     * @return string
     */
    public function getBody()
    {
//        if( $this->getPrefix() ) {
//            return $this->getPrefix() . $this->body;
//        }
        return $this->body;
    }

    /**
     * @return string
     */
    public function getPrefix()
    {
        return $this->prefix;
    }

    /**
     * @param string $prefix
     */
    public function setPrefix($prefix)
    {
        $this->prefix = $prefix;
    }

    public function getCommentShort() {
        $createdStr = $this->getCreatedAt()->format("m/d/Y H:i");
        //$info = "Submitted by a ".$this->getAuthorTypeDescription()." on ".$createdStr.": '".$this->getBody()."'";
        $info = $this->getAuthorTypeDescription()." on ".$createdStr.": <b>".$this->getBody()."</b>";
        return $info;
    }


    public function __toString() {
        return "Comment '".$this->getBody()."' with threadID=" . $this->getThread()->getId() . "; commentID" . $this->getId() . "<br>";
    }
}