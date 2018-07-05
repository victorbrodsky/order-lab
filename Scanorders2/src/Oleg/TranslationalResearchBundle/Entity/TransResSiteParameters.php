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
     * invoice header
     *
     * @ORM\Column(type="text", nullable=true)
     */
    private $transresFromHeader;

    /**
     * invoice footer
     *
     * @ORM\Column(type="text", nullable=true)
     */
    private $transresFooter;

    /**
     * Default Invoice Logos
     *
     * @ORM\ManyToMany(targetEntity="Oleg\UserdirectoryBundle\Entity\Document", cascade={"persist","remove"})
     * @ORM\JoinTable(name="transres_transResSiteParameters_transresLogo",
     *      joinColumns={@ORM\JoinColumn(name="transResSiteParameter_id", referencedColumnName="id", onDelete="CASCADE")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="transresLogo_id", referencedColumnName="id", onDelete="CASCADE")}
     *      )
     * @ORM\OrderBy({"createdate" = "DESC"})
     **/
    private $transresLogos;

    /**
     * Email body for notification email when Invoice PDF is sent to PI
     * @ORM\Column(type="text", nullable=true)
     */
    private $transresNotificationEmail;

    /**
     * Email subject for notification email when Invoice PDF is sent to PI
     * @ORM\Column(type="text", nullable=true)
     */
    private $transresNotificationEmailSubject;

    /**
     * Email body for notification email is being to send to the Request's PI when Request status is changed to "Completed and Notified"
     * @ORM\Column(type="text", nullable=true)
     */
    private $requestCompletedNotifiedEmail;

    /**
     * Email subject for notification email is being to send to to the Request's PI when Request status is changed to "Completed and Notified"
     * @ORM\Column(type="text", nullable=true)
     */
    private $requestCompletedNotifiedEmailSubject;

    /**
     * Invoice's invoiceSalesperson
     *
     * @ORM\ManyToOne(targetEntity="Oleg\UserdirectoryBundle\Entity\User")
     * @ORM\JoinColumn(name="invoiceSalesperson", referencedColumnName="id", nullable=true)
     */
    private $invoiceSalesperson;


    /**
     * Default Accession Type used in the System column in the Work Request handsontable
     *
     * @ORM\ManyToOne(targetEntity="Oleg\OrderformBundle\Entity\AccessionType")
     */
    private $accessionType;


    public function __construct($user=null) {
        $this->setCreator($user);
        $this->setCreateDate(new \DateTime());

        $this->transresLogos = new ArrayCollection();
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

    public function addTransresLogo($item)
    {
        if( $item && !$this->transresLogos->contains($item) ) {
            $this->transresLogos->add($item);
            $item->createUseObject($this);
        }
        return $this;
    }
    public function removeTransresLogo($item)
    {
        $this->transresLogos->removeElement($item);
        $item->clearUseObject();
    }
    public function getTransresLogos()
    {
        return $this->transresLogos;
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

    /**
     * @return mixed
     */
    public function getTransresNotificationEmailSubject()
    {
        return $this->transresNotificationEmailSubject;
    }

    /**
     * @param mixed $transresNotificationEmailSubject
     */
    public function setTransresNotificationEmailSubject($transresNotificationEmailSubject)
    {
        $this->transresNotificationEmailSubject = $transresNotificationEmailSubject;
    }

    /**
     * @return mixed
     */
    public function getRequestCompletedNotifiedEmail()
    {
        return $this->requestCompletedNotifiedEmail;
    }

    /**
     * @param mixed $requestCompletedNotifiedEmail
     */
    public function setRequestCompletedNotifiedEmail($requestCompletedNotifiedEmail)
    {
        $this->requestCompletedNotifiedEmail = $requestCompletedNotifiedEmail;
    }

    /**
     * @return mixed
     */
    public function getRequestCompletedNotifiedEmailSubject()
    {
        return $this->requestCompletedNotifiedEmailSubject;
    }

    /**
     * @param mixed $requestCompletedNotifiedEmailSubject
     */
    public function setRequestCompletedNotifiedEmailSubject($requestCompletedNotifiedEmailSubject)
    {
        $this->requestCompletedNotifiedEmailSubject = $requestCompletedNotifiedEmailSubject;
    }

    /**
     * @return mixed
     */
    public function getInvoiceSalesperson()
    {
        return $this->invoiceSalesperson;
    }

    /**
     * @param mixed $invoiceSalesperson
     */
    public function setInvoiceSalesperson($invoiceSalesperson)
    {
        $this->invoiceSalesperson = $invoiceSalesperson;
    }

    /**
     * @return mixed
     */
    public function getAccessionType()
    {
        return $this->accessionType;
    }

    /**
     * @param mixed $accessionType
     */
    public function setAccessionType($accessionType)
    {
        $this->accessionType = $accessionType;
    }
    
    


    public function __toString(){
        //return "Site Parameters ID ".$this->getId()." for ".$this->getProjectSpecialty();
        return "Site Parameters for ".$this->getProjectSpecialty()->getName();
    }




}