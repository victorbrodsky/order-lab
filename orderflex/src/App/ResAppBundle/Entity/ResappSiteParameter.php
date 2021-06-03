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


namespace App\ResAppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="resapp_siteparameter")
 */
class ResappSiteParameter {

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


    //TODO: add parameters Report path, 
//    /**
//     * Path to the local copy of the fellowship application form
//     * https://script.google.com/a/macros/pathologysystems.org/d/14jgVkEBCAFrwuW5Zqiq8jsw37rc4JieHkKrkYz1jyBp_DFFyTjRGKgHj/edit
//     *
//     * @ORM\Column(type="text", nullable=true)
//     */
//    private $codeGoogleFormResApp;

//    /**
//     * @ORM\Column(type="boolean", nullable=true)
//     */
//    private $allowPopulateResApp;

//    /**
//     * Automatically send invitation emails to upload recommendation letters
//     *
//     * @ORM\Column(type="boolean", nullable=true)
//     */
//    private $sendEmailUploadLetterResApp;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $confirmationSubjectResApp;

//    /**
//     * Recommendation Letter Salt to generate Recommendation Letter Salted Scrypt Hash ID
//     *
//     * @ORM\Column(type="text", nullable=true)
//     */
//    private $recLetterSaltResApp;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $confirmationBodyResApp;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $confirmationEmailResApp;

//    /**
//     * Client Email to get GoogleSrevice: i.e. '1040591934373-1sjcosdt66bmani0kdrr5qmc5fibmvk5@developer.gserviceaccount.com'
//     *
//     * @ORM\Column(type="text", nullable=true)
//     */
//    private $clientEmailResApp;

//    /**
//     * Path to p12 key file: i.e. /../Util/ResowshipApplication-f1d9f98353e5.p12
//     * E:\Program Files (x86)\pacsvendor\pacsname\htdocs\order\scanorder\Scanorders2\src\App\ResAppBundle\Util\FellowshipApplication-f1d9f98353e5.p12
//     *
//     * @ORM\Column(type="text", nullable=true)
//     */
//    private $p12KeyPathResApp;

//    /**
//     * https://www.googleapis.com/auth/drive https://spreadsheets.google.com/feeds
//     *
//     * @ORM\Column(type="text", nullable=true)
//     */
//    private $googleDriveApiUrlResApp;

//    /**
//     * Impersonate user Email: i.e. olegivanov@pathologysystems.org
//     *
//     * @ORM\Column(type="text", nullable=true)
//     */
//    private $userImpersonateEmailResApp;

//    /**
//     * Template Google Spreadsheet ID (1ITacytsUV2yChbfOSVjuBoW4aObSr_xBfpt6m_vab48)
//     *
//     * @ORM\Column(type="text", nullable=true)
//     */
//    private $templateIdResApp;

//    /**
//     * Backup Google Spreadsheet ID (19KlO1oCC88M436JzCa89xGO08MJ1txQNgLeJI0BpNGo)
//     *
//     * @ORM\Column(type="text", nullable=true)
//     */
//    private $backupFileIdResApp;

//    /**
//     * Application Google Drive Folder ID (0B2FwyaXvFk1efmc2VGVHUm5yYjJRWGFYYTF0Z2N6am9iUFVzcTc1OXdoWEl1Vmc0LWdZc0E)
//     * where the response spreadsheets (response forms) are saved
//     *
//     * @ORM\Column(type="text", nullable=true)
//     */
//    private $folderIdResApp;

//    /**
//     * Config.json file folder ID
//     *
//     * @ORM\Column(type="text", nullable=true)
//     */
//    private $configFileFolderIdResApp;

//    /**
//     * Backup Sheet Last Modified Date
//     *
//     * @ORM\Column(type="datetime", nullable=true)
//     */
//    private $backupUpdateDatetimeResApp;

    /**
     * Local Institution to which every imported application is set: Pathology Fellowship Programs (WCMC)
     * Used on populating applicant's spreadsheet in ResAppImportPopulateUtil->populateSpreadsheet() to get institution and set $fellowshipApplication->setInstitution($instPathologyFellowshipProgram);
     *
     * @ORM\Column(type="text", nullable=true)
     */
    private $localInstitutionResApp;

//    /**
//     * Modify the filename format generated by the Google recommendation letter upload form to include the “institution name” that is supplied in the URL
//     * Institution for which recommendation letters will be downloaded (fellowship identification string).
//     * Will be used to filter and only download files that have the matching institution string in the file name.
//     *
//     * @ORM\Column(type="text", nullable=true)
//     */
//    private $identificationUploadLetterResApp;

//    /**
//     * [ checkbox ] Delete successfully imported applications from Google Drive
//     *
//     * @ORM\Column(type="boolean", nullable=true)
//     */
//    private $deleteImportedAplicationsResApp;

//    /**
//     * checkbox for "Automatically delete downloaded applications that are older than [X] year(s)
//     * (set it at 2) [this is to delete old excel sheets that are downloaded from google drive.
//     * Make sure it is functional and Google/Excel sheets containing applications older than
//     * the amount of years set by this option is auto-deleted along with the linked downloaded documents.
//     *
//     * @ORM\Column(type="boolean", nullable=true)
//     */
//    private $deleteOldAplicationsResApp;

//    /**
//     * Used in checkbox for "Automatically delete downloaded applications that are older than [X] year(s)
//     *
//     * @ORM\Column(type="integer", nullable=true)
//     */
//    private $yearsOldAplicationsResApp;

    /**
     * Path to spreadsheets: i.e. Spreadsheets
     *
     * @ORM\Column(type="text", nullable=true)
     */
    private $spreadsheetsPathResApp;

    /**
     * Path to upload applicants documents: i.e. ResidencyApplicantUploads (resapp/ResidencyApplicantUploads)
     *
     * @ORM\Column(type="text", nullable=true)
     */
    private $applicantsUploadPathResApp;


    /**
     * Path to upload applicants documents used in ReportGenerator: i.e. Reports
     *
     * @ORM\Column(type="text", nullable=true)
     */
    private $reportsUploadPathResApp;

//    /**
//     * Link to the outside Application Page (so the users can click and see how it looks)
//     *
//     * @ORM\Column(type="text", nullable=true)
//     */
//    private $applicationPageLinkResApp;

    /**
     * Data Extraction Anchors in json (getKeyFieldArr):
     * Preceding anchor, Subsequent anchor, Number of characters
     *
     * @ORM\Column(type="text", nullable=true)
     */
    private $dataExtractionAnchor;
    
    /**
     * Default Residency Track for Bulk Import
     *
     * @ORM\ManyToOne(targetEntity="App\UserdirectoryBundle\Entity\ResidencyTrackList", cascade={"persist"})
     */
    private $defaultResidencyTrack;

    /**
     * Application season start date (MM/DD). default: academicYearStart
     *
     * @ORM\Column(type="date", nullable=true)
     */
    private $resappAcademicYearStart;

    /**
     * Application season end date (MM/DD). default: academicYearEnd
     *
     * @ORM\Column(type="date", nullable=true)
     */
    private $resappAcademicYearEnd;



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
    public function getLocalInstitutionResApp()
    {
        return $this->localInstitutionResApp;
    }

    /**
     * @param mixed $localInstitutionResApp
     */
    public function setLocalInstitutionResApp($localInstitutionResApp)
    {
        $this->localInstitutionResApp = $localInstitutionResApp;
    }

    /**
     * @return mixed
     */
    public function getApplicantsUploadPathResApp()
    {
        return $this->applicantsUploadPathResApp;
    }

    /**
     * @param mixed $applicantsUploadPathResApp
     */
    public function setApplicantsUploadPathResApp($applicantsUploadPathResApp)
    {
        $this->applicantsUploadPathResApp = $applicantsUploadPathResApp;
    }

    /**
     * @return mixed
     */
    public function getReportsUploadPathResApp()
    {
        return $this->reportsUploadPathResApp;
    }

    /**
     * @param mixed $reportsUploadPathResApp
     */
    public function setReportsUploadPathResApp($reportsUploadPathResApp)
    {
        $this->reportsUploadPathResApp = $reportsUploadPathResApp;
    }

    /**
     * @return mixed
     */
    public function getSpreadsheetsPathResApp()
    {
        return $this->spreadsheetsPathResApp;
    }

    /**
     * @param mixed $spreadsheetsPathResApp
     */
    public function setSpreadsheetsPathResApp($spreadsheetsPathResApp)
    {
        $this->spreadsheetsPathResApp = $spreadsheetsPathResApp;
    }

    /**
     * @return mixed
     */
    public function getConfirmationSubjectResApp()
    {
        return $this->confirmationSubjectResApp;
    }

    /**
     * @param mixed $confirmationSubjectResApp
     */
    public function setConfirmationSubjectResApp($confirmationSubjectResApp)
    {
        $this->confirmationSubjectResApp = $confirmationSubjectResApp;
    }

    /**
     * @return mixed
     */
    public function getConfirmationBodyResApp()
    {
        return $this->confirmationBodyResApp;
    }

    /**
     * @param mixed $confirmationBodyResApp
     */
    public function setConfirmationBodyResApp($confirmationBodyResApp)
    {
        $this->confirmationBodyResApp = $confirmationBodyResApp;
    }

    /**
     * @return mixed
     */
    public function getConfirmationEmailResApp()
    {
        return $this->confirmationEmailResApp;
    }

    /**
     * @param mixed $confirmationEmailResApp
     */
    public function setConfirmationEmailResApp($confirmationEmailResApp)
    {
        $this->confirmationEmailResApp = $confirmationEmailResApp;
    }

    /**
     * @return mixed
     */
    public function getDataExtractionAnchor()
    {
        return $this->dataExtractionAnchor;
    }

    /**
     * @param mixed $dataExtractionAnchor
     */
    public function setDataExtractionAnchor($dataExtractionAnchor)
    {
        $this->dataExtractionAnchor = $dataExtractionAnchor;
    }

    /**
     * @return mixed
     */
    public function getDefaultResidencyTrack()
    {
        return $this->defaultResidencyTrack;
    }

    /**
     * @param mixed $defaultResidencyTrack
     */
    public function setDefaultResidencyTrack($defaultResidencyTrack)
    {
        $this->defaultResidencyTrack = $defaultResidencyTrack;
    }

    /**
     * @return mixed
     */
    public function getResappAcademicYearStart()
    {
        return $this->resappAcademicYearStart;
    }

    /**
     * @param mixed $resappAcademicYearStart
     */
    public function setResappAcademicYearStart($resappAcademicYearStart)
    {
        $this->resappAcademicYearStart = $resappAcademicYearStart;
    }

    /**
     * @return mixed
     */
    public function getResappAcademicYearEnd()
    {
        return $this->resappAcademicYearEnd;
    }

    /**
     * @param mixed $resappAcademicYearEnd
     */
    public function setResappAcademicYearEnd($resappAcademicYearEnd)
    {
        $this->resappAcademicYearEnd = $resappAcademicYearEnd;
    }



}

