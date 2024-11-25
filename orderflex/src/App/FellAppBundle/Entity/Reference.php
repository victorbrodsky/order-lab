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

namespace App\FellAppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\ArrayCollection;

#[ORM\Table(name: 'fellapp_reference')]
#[ORM\Entity]
class Reference
{

    /**
     * @var integer
     */
    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    private $id;

    #[ORM\ManyToOne(targetEntity: 'FellowshipApplication', inversedBy: 'references', cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(name: 'fellapp_id', referencedColumnName: 'id', onDelete: 'CASCADE', nullable: true)]
    private $fellapp;

    #[ORM\Column(type: 'date', nullable: true)]
    private $creationDate;

    #[ORM\ManyToOne(targetEntity: 'App\UserdirectoryBundle\Entity\User')]
    #[ORM\JoinColumn(referencedColumnName: 'id', nullable: true)]
    private $createdBy;

    /**
     * Last Name
     */
    #[ORM\Column(type: 'string', nullable: true)]
    private $name;

    #[ORM\Column(type: 'string', nullable: true)]
    private $firstName;

    #[ORM\Column(type: 'string', nullable: true)]
    private $degree;

    #[ORM\Column(type: 'string', nullable: true)]
    private $title;

    #[ORM\ManyToOne(targetEntity: 'App\UserdirectoryBundle\Entity\Institution')]
    private $institution;

    #[ORM\OneToOne(targetEntity: 'App\UserdirectoryBundle\Entity\GeoLocation', cascade: ['persist', 'remove'])]
    private $geoLocation;

    /**
     * Reference Letters
     **/
    #[ORM\JoinTable(name: 'fellapp_reference_document')]
    #[ORM\JoinColumn(name: 'reference_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    #[ORM\InverseJoinColumn(name: 'document_id', referencedColumnName: 'id', onDelete: 'CASCADE', unique: true)]
    #[ORM\ManyToMany(targetEntity: 'App\UserdirectoryBundle\Entity\Document', cascade: ['persist', 'remove'])]
    #[ORM\OrderBy(['createdate' => 'ASC'])]
    private $documents;

    #[ORM\Column(type: 'string', nullable: true)]
    private $email;

    #[ORM\Column(type: 'string', nullable: true)]
    private $phone;

    #[ORM\Column(type: 'string', nullable: true)]
    private $recLetterHashId;

    #[ORM\Column(type: 'boolean', nullable: true)]
    private $recLetterReceived;

    #[ORM\Column(type: 'integer', nullable: true)]
    private $invitationSentEmailCounter;


    public function __construct($author=null) {

        $this->documents = new ArrayCollection();

        $this->setCreatedBy($author);
        $this->setCreationDate(new \DateTime());
    }


    /**
     * @param mixed $createdBy
     */
    public function setCreatedBy($createdBy)
    {
        $this->createdBy = $createdBy;
    }

    /**
     * @return mixed
     */
    public function getCreatedBy()
    {
        return $this->createdBy;
    }

    /**
     * @param mixed $creationDate
     */
    public function setCreationDate($creationDate)
    {
        $this->creationDate = $creationDate;
    }

    /**
     * @return mixed
     */
    public function getCreationDate()
    {
        return $this->creationDate;
    }

    /**
     * @param mixed $geoLocation
     */
    public function setGeoLocation($geoLocation)
    {
        $this->geoLocation = $geoLocation;
    }

    /**
     * @return mixed
     */
    public function getGeoLocation()
    {
        return $this->geoLocation;
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
     * @param mixed $institution
     */
    public function setInstitution($institution)
    {
        $this->institution = $institution;
    }

    /**
     * @return mixed
     */
    public function getInstitution()
    {
        return $this->institution;
    }

    /**
     * @param mixed $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return mixed
     */
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * @param mixed $firstName
     */
    public function setFirstName($firstName)
    {
        $this->firstName = $firstName;
    }

    /**
     * @return mixed
     */
    public function getDegree()
    {
        return $this->degree;
    }

    /**
     * @param mixed $degree
     */
    public function setDegree($degree)
    {
        $this->degree = $degree;
    }

    /**
     * @param mixed $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * @return mixed
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param mixed $fellapp
     */
    public function setFellapp($fellapp)
    {
        $this->fellapp = $fellapp;
    }

    /**
     * @return mixed
     */
    public function getFellapp()
    {
        return $this->fellapp;
    }


    public function addDocument($item)
    {
        if( $item && !$this->documents->contains($item) ) {
            $this->documents->add($item);
            $item->createUseObject($this);

            //set recLetterReceived true
            $this->setRecLetterReceived(true);
        }
        return $this;
    }
    public function removeDocument($item)
    {
        $this->documents->removeElement($item);
        $item->clearUseObject();
    }
    public function getDocuments()
    {
        return $this->documents;
    }

    /**
     * @return mixed
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param mixed $email
     */
    public function setEmail($email)
    {
        $this->email = $email;
    }

    /**
     * @return mixed
     */
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * @param mixed $phone
     */
    public function setPhone($phone)
    {
        $this->phone = $phone;
    }

    /**
     * @return mixed
     */
    public function getRecLetterHashId()
    {
        return $this->recLetterHashId;
    }

    /**
     * @param mixed $recLetterHashId
     */
    public function setRecLetterHashId($recLetterHashId)
    {
        $this->recLetterHashId = $recLetterHashId;
    }

    /**
     * @return mixed
     */
    public function getRecLetterReceived()
    {
        return $this->recLetterReceived;
    }

    /**
     * @param mixed $recLetterReceived
     */
    public function setRecLetterReceived($recLetterReceived)
    {
        $this->recLetterReceived = $recLetterReceived;
    }

    /**
     * @return mixed
     */
    public function getInvitationSentEmailCounter()
    {
        return $this->invitationSentEmailCounter;
    }

    /**
     * @param mixed $invitationSentEmailCounter
     */
    public function setInvitationSentEmailCounter($invitationSentEmailCounter)
    {
        $this->invitationSentEmailCounter = $invitationSentEmailCounter;
    }

    public function autoSetRecLetterReceived()
    {
        if( count($this->getDocuments()) > 0 ) {
            $this->setRecLetterReceived(true);
        }
    }

    public function getRecentReferenceLetter() {
        if( count($this->getDocuments()) > 0 ) {
            return $this->getDocuments()->last();
        } else {
            return null;
        }
    }

    public function hasReferenceLetter()
    {
        if( $this->getRecLetterReceived() ) {
           return true;
        }
        if( count($this->getDocuments()) > 0 ) {
            return true;
        }
        return false;
    }


    public function getFullName() {
        $nameArr = array();
        $firstName = $this->getFirstName();
        if( $firstName ) {
            $firstName = $this->capitalizeIfNotAllCapital($firstName);
            $nameArr[] = trim((string)$firstName);
        }
        $lastName = $this->getName();
        if( $lastName ) {
            $lastName = $this->capitalizeIfNotAllCapital($lastName);
            $nameArr[] = trim((string)$lastName);
        }

        return implode(" ",$nameArr);
    }

    public function capitalizeIfNotAllCapital($s) {
        if( !$s ) {
            return $s;
        }
        $convert = false;
        //check if all UPPER
        if( strtoupper($s) == $s ) {
            $convert = true;
        }
        //check if all lower
        if( strtolower($s) == $s ) {
            $convert = true;
        }
        if( $convert ) {
            return ucwords( strtolower($s) );
        }
        return $s;
    }

}