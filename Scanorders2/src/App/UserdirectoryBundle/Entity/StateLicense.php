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
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="user_stateLicense")
 */
class StateLicense
{

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="States")
     * @ORM\JoinColumn(name="state_id", referencedColumnName="id", nullable=true)
     **/
    private $state;

    /**
     * @ORM\ManyToOne(targetEntity="Countries")
     * @ORM\JoinColumn(name="country", referencedColumnName="id", nullable=true)
     **/
    private $country;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $licenseNumber;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $licenseIssuedDate;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $licenseExpirationDate;


    /**
     * @ORM\ManyToOne(targetEntity="Credentials", inversedBy="stateLicense")
     * @ORM\JoinColumn(name="credentials_id", referencedColumnName="id", onDelete="CASCADE", nullable=true)
     */
    private $credentials;

    /**
     * @ORM\ManyToOne(targetEntity="MedicalLicenseStatus")
     * @ORM\JoinColumn(name="active", referencedColumnName="id", nullable=true)
     **/
    private $active;

    //Relevant Documents: [use the Dropzone upload box, allow 20 documents]
    /**
     * Attachment can have many DocumentContainers; each DocumentContainers can have many Documents; each DocumentContainers has document type (DocumentTypeList)
     * @ORM\OneToOne(targetEntity="AttachmentContainer", cascade={"persist","remove"})
     **/
    private $attachmentContainer;



    public function __construct() {
        //add one document
        $this->createAttachmentDocument();
    }



    /**
     * @param mixed $credentials
     */
    public function setCredentials($credentials)
    {
        $this->credentials = $credentials;
    }

    /**
     * @return mixed
     */
    public function getCredentials()
    {
        return $this->credentials;
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
     * @param mixed $licenseExpirationDate
     */
    public function setLicenseExpirationDate($licenseExpirationDate)
    {
        $this->licenseExpirationDate = $licenseExpirationDate;
    }

    /**
     * @return mixed
     */
    public function getLicenseExpirationDate()
    {
        return $this->licenseExpirationDate;
    }

    /**
     * @param mixed $licenseNumber
     */
    public function setLicenseNumber($licenseNumber)
    {
        $this->licenseNumber = $licenseNumber;
    }

    /**
     * @return mixed
     */
    public function getLicenseNumber()
    {
        return $this->licenseNumber;
    }

    /**
     * @param mixed $state
     */
    public function setState($state)
    {
        $this->state = $state;
    }

    /**
     * @return mixed
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * @param mixed $country
     */
    public function setCountry($country)
    {
        $this->country = $country;
    }

    /**
     * @return mixed
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * @param mixed $active
     */
    public function setActive($active)
    {
        $this->active = $active;
    }

    /**
     * @return mixed
     */
    public function getActive()
    {
        return $this->active;
    }

    /**
     * @param mixed $licenseIssuedDate
     */
    public function setLicenseIssuedDate($licenseIssuedDate)
    {
        $this->licenseIssuedDate = $licenseIssuedDate;
    }

    /**
     * @return mixed
     */
    public function getLicenseIssuedDate()
    {
        return $this->licenseIssuedDate;
    }

    /**
     * @return mixed
     */
    public function getAttachmentContainer()
    {
        return $this->attachmentContainer;
    }

    /**
     * @param mixed $attachmentContainer
     */
    public function setAttachmentContainer($attachmentContainer)
    {
        $this->attachmentContainer = $attachmentContainer;
    }

    //create attachmentDocument holder with one DocumentContainer if not exists
    public function createAttachmentDocument() {
        //add one document
        //echo "state license: add attachment doc <br>";
        $attachmentContainer = $this->getAttachmentContainer();
        if( !$attachmentContainer ) {
            $attachmentContainer = new AttachmentContainer();
            $this->setAttachmentContainer($attachmentContainer);
        }
        if( count($attachmentContainer->getDocumentContainers()) == 0 ) {
            $attachmentContainer->addDocumentContainer( new DocumentContainer() );
        }
    }


    public function __toString() {
        return "State License";
    }


}