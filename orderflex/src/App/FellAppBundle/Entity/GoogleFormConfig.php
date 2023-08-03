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

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Table(name: 'fellapp_googleFormConfig')]
#[ORM\Entity]
class GoogleFormConfig {
    
    /**
     * @var integer
     */
    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    private $id;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private $updateDate;

    #[ORM\ManyToOne(targetEntity: 'App\UserdirectoryBundle\Entity\User')]
    #[ORM\JoinColumn(referencedColumnName: 'id', nullable: true)]
    private $updatedBy;

    #[ORM\Column(type: 'boolean', nullable: true)]
    private $acceptingSubmission;

    #[ORM\JoinTable(name: 'fellapp_googleformconfig_fellowshipsubspecialty')]
    #[ORM\JoinColumn(name: 'googleformconfig_id', referencedColumnName: 'id')]
    #[ORM\InverseJoinColumn(name: 'fellowshipsubspecialty_id', referencedColumnName: 'id')]
    #[ORM\ManyToMany(targetEntity: 'App\UserdirectoryBundle\Entity\FellowshipSubspecialty', cascade: ['persist', 'remove'])]
    private $fellowshipSubspecialties;

    /**
     * text in the wells at the top of the application
     */
    #[ORM\Column(type: 'text', nullable: true)]
    private $applicationFormNote;

    /**
     * admin email (_adminemail)
     */
    #[ORM\Column(type: 'string', nullable: true)]
    private $adminEmail;

    /**
     * fellowship admin's email (_useremail)
     */
    #[ORM\Column(type: 'string', nullable: true)]
    private $fellappAdminEmail;

    /**
     * "exception" account that the application is still shown to for testing purposes even when turned off (i.e. olegivanov@pathologysystems.org)
     */
    #[ORM\Column(type: 'string', nullable: true)]
    private $exceptionAccount;

    /**
     * text shown when the application is submitted
     */
    #[ORM\Column(type: 'text', nullable: true)]
    private $submissionConfirmation;


    #[ORM\Column(type: 'boolean', nullable: true)]
    private $letterAcceptingSubmission;

    /**
     * message shown on Error.html for reference letter page when parameters are not specified
     */
    #[ORM\Column(type: 'text', nullable: true)]
    private $letterError;

    /**
     * "exception" account that the letter submission is still shown to for testing purposes even when turned off (i.e. olegivanov@pathologysystems.org)
     */
    #[ORM\Column(type: 'string', nullable: true)]
    private $letterExceptionAccount;

    #[ORM\JoinTable(name: 'fellapp_googleformconfig_visastatus')]
    #[ORM\JoinColumn(name: 'googleformconfig_id', referencedColumnName: 'id')]
    #[ORM\InverseJoinColumn(name: 'visastatus_id', referencedColumnName: 'id')]
    #[ORM\ManyToMany(targetEntity: 'App\FellAppBundle\Entity\VisaStatus', cascade: ['persist', 'remove'])]
    private $fellowshipVisaStatuses;

    /**
     * NYPH-CORNELL ONLY ACCEPTS/SPONSORS J-1 VISAS
     */
    #[ORM\Column(type: 'string', nullable: true)]
    private $visaNote;

    /**
     * In chronological order, list other educational experiences, jobs, military service or training that is not accounted for above.
     */
    #[ORM\Column(type: 'string', nullable: true)]
    private $otherExperienceNote;

    /**
     * Please indicate national board examination dates and results received.
     */
    #[ORM\Column(type: 'string', nullable: true)]
    private $nationalBoardNote;

    /**
     * Please list any states in which you hold a license to practice medicine. Please provide a license number. If an application is pending in a state, please write “pending.”
     */
    #[ORM\Column(type: 'string', nullable: true)]
    private $medicalLicenseNote;

    /**
     * Please indicate any areas of board certification.
     */
    #[ORM\Column(type: 'string', nullable: true)]
    private $boardCertificationNote;

    /**
     * Please list the individuals who will write your letters of recommendation. At least three are required.
     */
    #[ORM\Column(type: 'string', nullable: true)]
    private $referenceLetterNote;

    /**
     * I hereby certify that all of the information on this application is accurate, complete,
     * and current to the best of my knowledge, and that this application is being made for
     * serious consideration of training in the Pathology Fellowship indicated.
     * I understand that accepting more than one fellowship position constitutes a violation
     * of professional ethics and may result in the forfeiture of all positions.
     */
    #[ORM\Column(type: 'text', nullable: true)]
    private $signatureStatement;

    //////////// Recommendation Letter script parameters /////////////////////
    /**
     * recSpreadsheetFolderId - Google folder ID for recommendation letters Spreadsheet
     */
    #[ORM\Column(type: 'text', nullable: true)]
    private $recSpreadsheetFolderId;

    /**
     * recUploadsFolderId - Google folder ID for recommendation letters upload
     */
    #[ORM\Column(type: 'text', nullable: true)]
    private $recUploadsFolderId;

    /**
     * recTemplateFileId - Google file ID for the Spreadsheet Template for the recommendation letter
     */
    #[ORM\Column(type: 'text', nullable: true)]
    private $recTemplateFileId;

    /**
     * recBackupTemplateFileId - Google file ID for the Backup Spreadsheet Template for the recommendation letter
     */
    #[ORM\Column(type: 'text', nullable: true)]
    private $recBackupTemplateFileId;
    //////////// EOF Recommendation Letter script parameters /////////////////////
    //////////// Fellowship Application script parameters /////////////////////
    /**
     * felSpreadsheetFolderId - Google folder ID for fellowship application Spreadsheet
     */
    #[ORM\Column(type: 'text', nullable: true)]
    private $felSpreadsheetFolderId;

    /**
     * felUploadsFolderId - Google folder ID for fellowship application upload
     */
    #[ORM\Column(type: 'text', nullable: true)]
    private $felUploadsFolderId;

    /**
     * felTemplateFileId - Google file ID for the Spreadsheet Template for the fellowship application
     */
    #[ORM\Column(type: 'text', nullable: true)]
    private $felTemplateFileId;

    /**
     * felBackupTemplateFileId - Google file ID for the Backup Spreadsheet Template for the fellowship application
     */
    #[ORM\Column(type: 'text', nullable: true)]
    private $felBackupTemplateFileId;
    //////////// EOF Fellowship Application script parameters /////////////////////


    

    public function __construct() {
        $this->fellowshipSubspecialties = new ArrayCollection();
        $this->fellowshipVisaStatuses = new ArrayCollection();
    }






    
    public function getId() {
        return $this->id;
    }

    public function getFellowshipSubspecialties()
    {
        return $this->fellowshipSubspecialties;
    }
    public function addFellowshipSubspecialty($item)
    {
        if( $item && !$this->fellowshipSubspecialties->contains($item) ) {
            $this->fellowshipSubspecialties->add($item);
        }

    }
    public function removeFellowshipSubspecialty($item)
    {
        $this->fellowshipSubspecialties->removeElement($item);
    }

    public function getFellowshipVisaStatuses()
    {
        return $this->fellowshipVisaStatuses;
    }
    public function addFellowshipVisaStatus($item)
    {
        if( $item && !$this->fellowshipVisaStatuses->contains($item) ) {
            $this->fellowshipVisaStatuses->add($item);
        }

    }
    public function removeFellowshipVisaStatus($item)
    {
        $this->fellowshipVisaStatuses->removeElement($item);
    }

    /**
     * @return mixed
     */
    public function getUpdateDate()
    {
        return $this->updateDate;
    }

    /**
     * @param mixed $updateDate
     */
    public function setUpdateDate($updateDate)
    {
        $this->updateDate = $updateDate;
    }

    /**
     * @return mixed
     */
    public function getUpdatedBy()
    {
        return $this->updatedBy;
    }

    /**
     * @param mixed $updatedBy
     */
    public function setUpdatedBy($updatedBy)
    {
        $this->updatedBy = $updatedBy;
    }

    /**
     * @return mixed
     */
    public function getAcceptingSubmission()
    {
        return $this->acceptingSubmission;
    }

    /**
     * @param mixed $acceptingSubmission
     */
    public function setAcceptingSubmission($acceptingSubmission)
    {
        $this->acceptingSubmission = $acceptingSubmission;
    }

    /**
     * @return mixed
     */
    public function getApplicationFormNote()
    {
        return $this->applicationFormNote;
    }

    /**
     * @param mixed $applicationFormNote
     */
    public function setApplicationFormNote($applicationFormNote)
    {
        $this->applicationFormNote = $applicationFormNote;
    }

    /**
     * @return mixed
     */
    public function getAdminEmail()
    {
        return $this->adminEmail;
    }

    /**
     * @param mixed $adminEmail
     */
    public function setAdminEmail($adminEmail)
    {
        $this->adminEmail = $adminEmail;
    }

    /**
     * @return mixed
     */
    public function getFellappAdminEmail()
    {
        return $this->fellappAdminEmail;
    }

    /**
     * @param mixed $fellappAdminEmail
     */
    public function setFellappAdminEmail($fellappAdminEmail)
    {
        $this->fellappAdminEmail = $fellappAdminEmail;
    }

    /**
     * @return mixed
     */
    public function getExceptionAccount()
    {
        return $this->exceptionAccount;
    }

    /**
     * @param mixed $exceptionAccount
     */
    public function setExceptionAccount($exceptionAccount)
    {
        $this->exceptionAccount = $exceptionAccount;
    }

    /**
     * @return mixed
     */
    public function getSubmissionConfirmation()
    {
        return $this->submissionConfirmation;
    }

    /**
     * @param mixed $submissionConfirmation
     */
    public function setSubmissionConfirmation($submissionConfirmation)
    {
        $this->submissionConfirmation = $submissionConfirmation;
    }

    /**
     * @return mixed
     */
    public function getLetterAcceptingSubmission()
    {
        return $this->letterAcceptingSubmission;
    }

    /**
     * @param mixed $letterAcceptingSubmission
     */
    public function setLetterAcceptingSubmission($letterAcceptingSubmission)
    {
        $this->letterAcceptingSubmission = $letterAcceptingSubmission;
    }

    /**
     * @return mixed
     */
    public function getLetterError()
    {
        return $this->letterError;
    }

    /**
     * @param mixed $letterError
     */
    public function setLetterError($letterError)
    {
        $this->letterError = $letterError;
    }

    /**
     * @return mixed
     */
    public function getLetterExceptionAccount()
    {
        return $this->letterExceptionAccount;
    }

    /**
     * @param mixed $letterExceptionAccount
     */
    public function setLetterExceptionAccount($letterExceptionAccount)
    {
        $this->letterExceptionAccount = $letterExceptionAccount;
    }

    /**
     * @return mixed
     */
    public function getVisaNote()
    {
        return $this->visaNote;
    }
    /**
     * @param mixed $visaNote
     */
    public function setVisaNote($visaNote)
    {
        $this->visaNote = $visaNote;
    }

    /**
     * @return mixed
     */
    public function getOtherExperienceNote()
    {
        return $this->otherExperienceNote;
    }
    /**
     * @param mixed $otherExperienceNote
     */
    public function setOtherExperienceNote($otherExperienceNote)
    {
        $this->otherExperienceNote = $otherExperienceNote;
    }

    /**
     * @return mixed
     */
    public function getNationalBoardNote()
    {
        return $this->nationalBoardNote;
    }
    /**
     * @param mixed $nationalBoardNote
     */
    public function setNationalBoardNote($nationalBoardNote)
    {
        $this->nationalBoardNote = $nationalBoardNote;
    }

    /**
     * @return mixed
     */
    public function getMedicalLicenseNote()
    {
        return $this->medicalLicenseNote;
    }
    /**
     * @param mixed $medicalLicenseNote
     */
    public function setMedicalLicenseNote($medicalLicenseNote)
    {
        $this->medicalLicenseNote = $medicalLicenseNote;
    }

    /**
     * @return mixed
     */
    public function getBoardCertificationNote()
    {
        return $this->boardCertificationNote;
    }
    /**
     * @param mixed $boardCertificationNote
     */
    public function setBoardCertificationNote($boardCertificationNote)
    {
        $this->boardCertificationNote = $boardCertificationNote;
    }

    /**
     * @return mixed
     */
    public function getReferenceLetterNote()
    {
        return $this->referenceLetterNote;
    }
    /**
     * @param mixed $referenceLetterNote
     */
    public function setReferenceLetterNote($referenceLetterNote)
    {
        $this->referenceLetterNote = $referenceLetterNote;
    }

    /**
     * @return mixed
     */
    public function getSignatureStatement()
    {
        return $this->signatureStatement;
    }
    /**
     * @param mixed $signatureStatement
     */
    public function setSignatureStatement($signatureStatement)
    {
        $this->signatureStatement = $signatureStatement;
    }

    /**
     * @return mixed
     */
    public function getRecSpreadsheetFolderId()
    {
        return $this->recSpreadsheetFolderId;
    }

    /**
     * @param mixed $recSpreadsheetFolderId
     */
    public function setRecSpreadsheetFolderId($recSpreadsheetFolderId)
    {
        $this->recSpreadsheetFolderId = $recSpreadsheetFolderId;
    }

    /**
     * @return mixed
     */
    public function getRecUploadsFolderId()
    {
        return $this->recUploadsFolderId;
    }

    /**
     * @param mixed $recUploadsFolderId
     */
    public function setRecUploadsFolderId($recUploadsFolderId)
    {
        $this->recUploadsFolderId = $recUploadsFolderId;
    }

    /**
     * @return mixed
     */
    public function getRecTemplateFileId()
    {
        return $this->recTemplateFileId;
    }

    /**
     * @param mixed $recTemplateFileId
     */
    public function setRecTemplateFileId($recTemplateFileId)
    {
        $this->recTemplateFileId = $recTemplateFileId;
    }

    /**
     * @return mixed
     */
    public function getRecBackupTemplateFileId()
    {
        return $this->recBackupTemplateFileId;
    }

    /**
     * @param mixed $recBackupTemplateFileId
     */
    public function setRecBackupTemplateFileId($recBackupTemplateFileId)
    {
        $this->recBackupTemplateFileId = $recBackupTemplateFileId;
    }

    /**
     * @return mixed
     */
    public function getFelSpreadsheetFolderId()
    {
        return $this->felSpreadsheetFolderId;
    }

    /**
     * @param mixed $felSpreadsheetFolderId
     */
    public function setFelSpreadsheetFolderId($felSpreadsheetFolderId)
    {
        $this->felSpreadsheetFolderId = $felSpreadsheetFolderId;
    }

    /**
     * @return mixed
     */
    public function getFelUploadsFolderId()
    {
        return $this->felUploadsFolderId;
    }

    /**
     * @param mixed $felUploadsFolderId
     */
    public function setFelUploadsFolderId($felUploadsFolderId)
    {
        $this->felUploadsFolderId = $felUploadsFolderId;
    }

    /**
     * @return mixed
     */
    public function getFelTemplateFileId()
    {
        return $this->felTemplateFileId;
    }

    /**
     * @param mixed $felTemplateFileId
     */
    public function setFelTemplateFileId($felTemplateFileId)
    {
        $this->felTemplateFileId = $felTemplateFileId;
    }

    /**
     * @return mixed
     */
    public function getFelBackupTemplateFileId()
    {
        return $this->felBackupTemplateFileId;
    }

    /**
     * @param mixed $felBackupTemplateFileId
     */
    public function setFelBackupTemplateFileId($felBackupTemplateFileId)
    {
        $this->felBackupTemplateFileId = $felBackupTemplateFileId;
    }


    



    public function __toString() {
        return "Google Form Config AcceptingSubmission=".$this->getAcceptingSubmission()."<br>";
    }
    
    
}

?>
