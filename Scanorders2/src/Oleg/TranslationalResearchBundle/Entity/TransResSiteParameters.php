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

namespace Oleg\TranslationalResearchBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

use Symfony\Component\Form\Extension\Core\DataTransformer\DateTimeToStringTransformer;

/**
 * @ORM\Entity
 * @ORM\Table(name="transres_siteParameters", uniqueConstraints={@ORM\UniqueConstraint(name="siteParameters_unique", columns={"projectSpecialty_id"})})
 * @ORM\HasLifecycleCallbacks
 */
class TransResSiteParameters {

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="Oleg\UserdirectoryBundle\Entity\User")
     * @ORM\JoinColumn(referencedColumnName="id", nullable=true)
     */
    private $creator;

    /**
     * @ORM\ManyToOne(targetEntity="Oleg\UserdirectoryBundle\Entity\User")
     * @ORM\JoinColumn(name="updateUser", referencedColumnName="id", nullable=true)
     */
    private $updateUser;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $createDate;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $updateDate;

    /**
     * Hematopathology or AP/CP
     *
     * @ORM\ManyToOne(targetEntity="Oleg\TranslationalResearchBundle\Entity\SpecialtyList", cascade={"persist"})
     * @ORM\JoinColumn(name="projectSpecialty_id", referencedColumnName="id", nullable=false)
     */
    private $projectSpecialty;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $transresFromHeader;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $transresFooter;

    /**
     * Single document implementation:
     * 1) add interface method removeDocument
     * 2) modify setter method (i.e. setTransresLogo): add $transresLogo->createUseObject($this);
     * 3) add in setHolderDocumentsDql: case "OlegTranslationalResearchBundle:TransResSiteParameters" => "comment.transresLogo";
     *
     * @ORM\OneToOne(targetEntity="Oleg\UserdirectoryBundle\Entity\Document", cascade={"persist","remove"})
     * @ORM\JoinColumn(name="transresLogo_id", referencedColumnName="id", nullable=true)
     **/
    private $transresLogo;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $transresNotificationEmail;



    public function __construct($user=null) {
        $this->setCreator($user);
        $this->setCreateDate(new \DateTime());
    }



    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
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
    public function getCreator()
    {
        return $this->creator;
    }

    /**
     * @param mixed $creator
     */
    public function setCreator($creator)
    {
        $this->creator = $creator;
    }

    /**
     * @return mixed
     */
    public function getUpdateUser()
    {
        return $this->updateUser;
    }

    /**
     * @param mixed $updateUser
     */
    public function setUpdateUser($updateUser)
    {
        $this->updateUser = $updateUser;
    }

    /**
     * @return mixed
     */
    public function getCreateDate()
    {
        return $this->createDate;
    }

    /**
     * @param mixed $createDate
     */
    public function setCreateDate($createDate)
    {
        $this->createDate = $createDate;
    }

    /**
     * @return \DateTime
     */
    public function getUpdateDate()
    {
        return $this->updateDate;
    }

    /**
     * @ORM\PreUpdate
     */
    public function setUpdateDate()
    {
        $this->updateDate = new \DateTime();
    }


    /**
     * @return mixed
     */
    public function getProjectSpecialty()
    {
        return $this->projectSpecialty;
    }

    /**
     * @param mixed $projectSpecialty
     */
    public function setProjectSpecialty($projectSpecialty)
    {
        $this->projectSpecialty = $projectSpecialty;
    }

    /**
     * @return mixed
     */
    public function getTransresFromHeader()
    {
        return $this->transresFromHeader;
    }

    /**
     * @param mixed $transresFromHeader
     */
    public function setTransresFromHeader($transresFromHeader)
    {
        $this->transresFromHeader = $transresFromHeader;
    }

    /**
     * @return mixed
     */
    public function getTransresFooter()
    {
        return $this->transresFooter;
    }

    /**
     * @param mixed $transresFooter
     */
    public function setTransresFooter($transresFooter)
    {
        $this->transresFooter = $transresFooter;
    }

    /**
     * @return mixed
     */
    public function getTransresLogo()
    {
        return $this->transresLogo;
    }

    /**
     * @param mixed $transresLogo
     */
    public function setTransresLogo($transresLogo)
    {
        if( $transresLogo ) {
            $transresLogo->createUseObject($this);
        }

        $this->transresLogo = $transresLogo;
    }
    //interface method to remove document
    public function removeDocument($document) {
        $document->clearUseObject();
        $this->setTransresLogo(null);
    }

    /**
     * @return mixed
     */
    public function getTransresNotificationEmail()
    {
        return $this->transresNotificationEmail;
    }

    /**
     * @param mixed $transresNotificationEmail
     */
    public function setTransresNotificationEmail($transresNotificationEmail)
    {
        $this->transresNotificationEmail = $transresNotificationEmail;
    }


    public function __toString(){
        //return "Site Parameters ID ".$this->getId()." for ".$this->getProjectSpecialty();
        return "Site Parameters for ".$this->getProjectSpecialty()->getName();
    }




}