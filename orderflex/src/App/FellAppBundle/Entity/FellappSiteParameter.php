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

/**
 * @ORM\Entity
 * @ORM\Table(name="fellapp_siteParameter")
 */
class FellappSiteParameter {

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * Subject of e-mail to the accepted applicant
     *
     * @ORM\Column(type="text", nullable=true)
     */
    private $acceptedEmailSubject;

    /**
     * Subject of e-mail to the accepted applicant
     *
     * @ORM\Column(type="text", nullable=true)
     */
    private $acceptedEmailBody;


    /**
     * Subject of e-mail to the rejected applicant
     *
     * @ORM\Column(type="text", nullable=true)
     */
    private $rejectedEmailSubject;

    /**
     * Subject of e-mail to the rejected applicant
     *
     * @ORM\Column(type="text", nullable=true)
     */
    private $rejectedEmailBody;

    /**
     * Application season start date (MM/DD). default: academicYearStart
     * 
     * @ORM\Column(type="date", nullable=true)
     */
    private $fellappAcademicYearStart;

    /**
     * Application season end date (MM/DD). default: academicYearEnd
     * 
     * @ORM\Column(type="date", nullable=true)
     */
    private $fellappAcademicYearEnd;
    
    /**
     * the web app url from deployment GAS, send by email in inviteSingleReferenceToSubmitLetter
     *
     * @ORM\Column(type="text", nullable=true)
     */
    private $fellappRecLetterUrl;

    /**
     * Email address for confirmation of application submission
     * 
     * @ORM\Column(type="text", nullable=true)
     */
    private $confirmationEmailFellApp;

    /**
     * Link to the Application Page (so the users can click and see how it looks)
     *
     * @ORM\Column(type="text", nullable=true)
     */
    private $applicationPageLinkFellApp;

    /**
     * Rename $p12KeyPathFellApp to $authPathFellApp
     * Full path to the credential authentication JSON file for Google
     * C:\Users\ch3\Documents\MyDocs\WCMC\ORDER\order-lab\orderflex\src\App\FellAppBundle\Util\quickstart-FellowshipAuth.json
     *
     * @ORM\Column(type="text", nullable=true)
     */
    private $authPathFellApp;

    /**
     * Google Drive API URL
     * https://www.googleapis.com/auth/drive https://spreadsheets.google.com/feeds
     *
     * @ORM\Column(type="text", nullable=true)
     */
    private $googleDriveApiUrlFellApp;

    /**
     * $localInstitutionFellApp
     * Local Institution to which every imported application is set: Pathology Fellowship Programs (WCMC)
     *
     * @ORM\ManyToOne(targetEntity="App\UserdirectoryBundle\Entity\Institution")
     */
    private $localInstitutionFellApp;


    
    public function __construct() {
        
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
    public function getAcceptedEmailSubject()
    {
        return $this->acceptedEmailSubject;
    }

    /**
     * @param mixed $acceptedEmailSubject
     */
    public function setAcceptedEmailSubject($acceptedEmailSubject)
    {
        $this->acceptedEmailSubject = $acceptedEmailSubject;
    }

    /**
     * @return mixed
     */
    public function getAcceptedEmailBody()
    {
        return $this->acceptedEmailBody;
    }

    /**
     * @param mixed $acceptedEmailBody
     */
    public function setAcceptedEmailBody($acceptedEmailBody)
    {
        $this->acceptedEmailBody = $acceptedEmailBody;
    }

    /**
     * @return mixed
     */
    public function getRejectedEmailSubject()
    {
        return $this->rejectedEmailSubject;
    }

    /**
     * @param mixed $rejectedEmailSubject
     */
    public function setRejectedEmailSubject($rejectedEmailSubject)
    {
        $this->rejectedEmailSubject = $rejectedEmailSubject;
    }

    /**
     * @return mixed
     */
    public function getRejectedEmailBody()
    {
        return $this->rejectedEmailBody;
    }

    /**
     * @param mixed $rejectedEmailBody
     */
    public function setRejectedEmailBody($rejectedEmailBody)
    {
        $this->rejectedEmailBody = $rejectedEmailBody;
    }

    /**
     * @return mixed
     */
    public function getFellappAcademicYearStart()
    {
        return $this->fellappAcademicYearStart;
    }

    /**
     * @param mixed $fellappAcademicYearStart
     */
    public function setFellappAcademicYearStart($fellappAcademicYearStart)
    {
        $this->fellappAcademicYearStart = $fellappAcademicYearStart;
    }

    /**
     * @return mixed
     */
    public function getFellappAcademicYearEnd()
    {
        return $this->fellappAcademicYearEnd;
    }

    /**
     * @param mixed $fellappAcademicYearEnd
     */
    public function setFellappAcademicYearEnd($fellappAcademicYearEnd)
    {
        $this->fellappAcademicYearEnd = $fellappAcademicYearEnd;
    }

    /**
     * @return mixed
     */
    public function getFellappRecLetterUrl()
    {
        return $this->fellappRecLetterUrl;
    }

    /**
     * @param mixed $fellappRecLetterUrl
     */
    public function setFellappRecLetterUrl($fellappRecLetterUrl)
    {
        $this->fellappRecLetterUrl = $fellappRecLetterUrl;
    }

    /**
     * @return mixed
     */
    public function getConfirmationEmailFellApp()
    {
        return $this->confirmationEmailFellApp;
    }

    /**
     * @param mixed $confirmationEmailFellApp
     */
    public function setConfirmationEmailFellApp($confirmationEmailFellApp)
    {
        $this->confirmationEmailFellApp = $confirmationEmailFellApp;
    }

    /**
     * @return mixed
     */
    public function getApplicationPageLinkFellApp()
    {
        return $this->applicationPageLinkFellApp;
    }

    /**
     * @param mixed $applicationPageLinkFellApp
     */
    public function setApplicationPageLinkFellApp($applicationPageLinkFellApp)
    {
        $this->applicationPageLinkFellApp = $applicationPageLinkFellApp;
    }

    /**
     * @return mixed
     */
    public function getAuthPathFellApp()
    {
        return $this->authPathFellApp;
    }

    /**
     * @param mixed $authPathFellApp
     */
    public function setAuthPathFellApp($authPathFellApp)
    {
        $this->authPathFellApp = $authPathFellApp;
    }

    /**
     * @return mixed
     */
    public function getGoogleDriveApiUrlFellApp()
    {
        return $this->googleDriveApiUrlFellApp;
    }

    /**
     * @param mixed $googleDriveApiUrlFellApp
     */
    public function setGoogleDriveApiUrlFellApp($googleDriveApiUrlFellApp)
    {
        $this->googleDriveApiUrlFellApp = $googleDriveApiUrlFellApp;
    }

    /**
     * @return mixed
     */
    public function getLocalInstitutionFellApp()
    {
        return $this->localInstitutionFellApp;
    }

    /**
     * @param mixed $localInstitutionFellApp
     */
    public function setLocalInstitutionFellApp($localInstitutionFellApp)
    {
        $this->localInstitutionFellApp = $localInstitutionFellApp;
    }

    

}

