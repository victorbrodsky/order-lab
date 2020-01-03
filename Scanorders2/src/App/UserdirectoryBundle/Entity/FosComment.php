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

namespace App\UserdirectoryBundle\Entity;

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
     * @ORM\ManyToOne(targetEntity="App\UserdirectoryBundle\Entity\FosThread")
     */
    protected $thread;

    /**
     * Author of the comment
     *
     * @var User
     * @ORM\ManyToOne(targetEntity="App\UserdirectoryBundle\Entity\User")
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

    //Fields specifying a subject entity
    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $entityNamespace;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $entityName;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $entityId;



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

    /**
     * @return mixed
     */
    public function getEntityNamespace()
    {
        return $this->entityNamespace;
    }

    /**
     * @param mixed $entityNamespace
     */
    public function setEntityNamespace($entityNamespace)
    {
        $this->entityNamespace = $entityNamespace;
    }

    /**
     * @return mixed
     */
    public function getEntityName()
    {
        return $this->entityName;
    }

    /**
     * @param mixed $entityName
     */
    public function setEntityName($entityName)
    {
        $this->entityName = $entityName;
    }

    /**
     * @return mixed
     */
    public function getEntityId()
    {
        return $this->entityId;
    }

    /**
     * @param mixed $entityId
     */
    public function setEntityId($entityId)
    {
        $this->entityId = $entityId;
    }

    public function setObject($object) {
        $class = new \ReflectionClass($object);
        $className = $class->getShortName();
        $classNamespace = $class->getNamespaceName();

        if( $className && !$this->getEntityName() ) {
            $this->setEntityName($className);
        }

        if( $classNamespace && !$this->getEntityNamespace() ) {
            $this->setEntityNamespace($classNamespace);
        }

        if( !$this->getEntityId() && $object->getId() ) {
            $this->setEntityId($object->getId());
        }
    }
    public function clearUseObject()
    {
        $this->setEntityName(NULL);
        $this->setEntityNamespace(NULL);
        $this->setEntityId(NULL);
    }

    //Old: Reviewer on 11/27/2018 16:11:
    //New: Reviewer (on 11/27/2018 at 4:11pm PST):
    public function getCommentShort() {
        //$createdStr = $this->getCreatedAt()->format("m/d/Y H:i");
        $createdDate = $this->getCreatedAt();
        $author = $this->getAuthor();
        if( $author ) {
            $createdDate = $this->convertFromUtcToUserTimezone($createdDate,$author);
        }
        $createdStr = $createdDate->format('m/d/Y') . " at " . $createdDate->format('h:ia T');
        //$info = "Submitted by a ".$this->getAuthorTypeDescription()." on ".$createdStr.": '".$this->getBody()."'";
        $info = $this->getAuthorTypeDescription()." (on ".$createdStr."): <b>".$this->getBody()."</b>";
        return $info;
    }
    public function convertFromUtcToUserTimezone($datetime,$user)
    {

        //$user_tz = 'America/New_York';
        $user_tz = null;
        $preferences = $user->getPreferences();
        if( $preferences ) {
            $user_tz = $preferences->getTimezone();
        }
        if( !$user_tz ) {
            return $datetime;
        }

        //echo "input datetime=".$datetime->format('Y-m-d H:i')."<br>";
        $datetimeUTC = new \DateTime($datetime->format('Y-m-d H:i'), new \DateTimeZone('UTC') );
        $datetimeTz = $datetimeUTC->setTimeZone(new \DateTimeZone($user_tz));

        //echo "output datetime=".$datetimeUTC->format('Y-m-d H:i')."<br>";

        return $datetimeTz;
    }


    public function __toString() {
        return "Comment '".$this->getBody()."' with threadID=" . $this->getThread()->getId() . "; commentID" . $this->getId() . "<br>";
    }
}