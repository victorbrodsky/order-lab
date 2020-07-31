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

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="resapp_googleformconfig")
 */
class GoogleFormConfig {
    
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
 * @ORM\Column(type="datetime", nullable=true)
 */
    private $updateDate;

    /**
     * @ORM\ManyToOne(targetEntity="App\UserdirectoryBundle\Entity\User")
     * @ORM\JoinColumn(referencedColumnName="id", nullable=true)
     */
    private $updatedBy;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $acceptingSubmission;

    /**
     * @ORM\ManyToMany(targetEntity="App\UserdirectoryBundle\Entity\ResidencySpecialty", cascade={"persist","remove"})
     * @ORM\JoinTable(name="resapp_googleformconfig_residencyspecialty",
     *      joinColumns={@ORM\JoinColumn(name="googleformconfig_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="residencyspecialty_id", referencedColumnName="id")}
     * )
     **/
    private $residencySubspecialties;

    /**
     * text in the wells at the top of the application
     *
     * @ORM\Column(type="text", nullable=true)
     */
    private $applicationFormNote;

    /**
     * admin email (_adminemail)
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $adminEmail;

    /**
     * residency admin's email (_useremail)
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $resappAdminEmail;

    /**
     * "exception" account that the application is still shown to for testing purposes even when turned off (i.e. olegivanov@pathologysystems.org)
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $exceptionAccount;

    /**
     * text shown when the application is submitted
     *
     * @ORM\Column(type="text", nullable=true)
     */
    private $submissionConfirmation;


    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $letterAcceptingSubmission;

    /**
     * message shown on Error.html for reference letter page when parameters are not specified
     *
     * @ORM\Column(type="text", nullable=true)
     */
    private $letterError;

    /**
     * "exception" account that the letter submission is still shown to for testing purposes even when turned off (i.e. olegivanov@pathologysystems.org)
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $letterExceptionAccount;

    /**
     * @ORM\ManyToMany(targetEntity="App\ResAppBundle\Entity\VisaStatus", cascade={"persist","remove"})
     * @ORM\JoinTable(name="resapp_googleformconfig_visastatus",
     *      joinColumns={@ORM\JoinColumn(name="googleformconfig_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="visastatus_id", referencedColumnName="id")}
     * )
     **/
    private $residencyVisaStatuses;

    /**
     * NYPH-CORNELL ONLY ACCEPTS/SPONSORS J-1 VISAS
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $visaNote;

    /**
     * In chronological order, list other educational experiences, jobs, military service or training that is not accounted for above.
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $otherExperienceNote;

    /**
     * Please indicate national board examination dates and results received.
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $nationalBoardNote;

    /**
     * Please list any states in which you hold a license to practice medicine. Please provide a license number. If an application is pending in a state, please write “pending.”
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $medicalLicenseNote;

    /**
     * Please indicate any areas of board certification.
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $boardCertificationNote;

    /**
     * Please list the individuals who will write your letters of recommendation. At least three are required.
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $referenceLetterNote;

    /**
     * I hereby certify that all of the information on this application is accurate, complete,
     * and current to the best of my knowledge, and that this application is being made for
     * serious consideration of training in the Pathology Residency indicated.
     * I understand that accepting more than one residency position constitutes a violation
     * of professional ethics and may result in the forfeiture of all positions.
     *
     * @ORM\Column(type="text", nullable=true)
     */
    private $signatureStatement;



    public function __construct() {
        $this->residencySubspecialties = new ArrayCollection();
        $this->residencyVisaStatuses = new ArrayCollection();
    }






    
    public function getId() {
        return $this->id;
    }

    public function getResidencySubspecialties()
    {
        return $this->residencySubspecialties;
    }
    public function addResidencyTrack($item)
    {
        if( $item && !$this->residencySubspecialties->contains($item) ) {
            $this->residencySubspecialties->add($item);
        }

    }
    public function removeResidencyTrack($item)
    {
        $this->residencySubspecialties->removeElement($item);
    }

    public function getResidencyVisaStatuses()
    {
        return $this->residencyVisaStatuses;
    }
    public function addResidencyVisaStatus($item)
    {
        if( $item && !$this->residencyVisaStatuses->contains($item) ) {
            $this->residencyVisaStatuses->add($item);
        }

    }
    public function removeResidencyVisaStatus($item)
    {
        $this->residencyVisaStatuses->removeElement($item);
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
    public function getResappAdminEmail()
    {
        return $this->resappAdminEmail;
    }

    /**
     * @param mixed $resappAdminEmail
     */
    public function setResappAdminEmail($resappAdminEmail)
    {
        $this->resappAdminEmail = $resappAdminEmail;
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


    



    public function __toString() {
        return "Google Form Config AcceptingSubmission=".$this->getAcceptingSubmission()."<br>";
    }
    
    
}

?>
