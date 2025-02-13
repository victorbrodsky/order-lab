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

namespace App\UserdirectoryBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'user_employmentStatus')]
#[ORM\Entity]
class EmploymentStatus extends BaseUserAttributes
{

    #[ORM\ManyToOne(targetEntity: 'User', inversedBy: 'employmentStatus')]
    #[ORM\JoinColumn(name: 'fosuser', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private $user;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private $hireDate;

    //Employee Type
    #[ORM\ManyToOne(targetEntity: 'EmploymentType')]
    #[ORM\JoinColumn(name: 'employmentType_id', referencedColumnName: 'id', nullable: true)]
    private $employmentType;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private $terminationDate;

    #[ORM\ManyToOne(targetEntity: 'EmploymentTerminationType')]
    #[ORM\JoinColumn(name: 'state_id', referencedColumnName: 'id', nullable: true)]
    private $terminationType;

    #[ORM\Column(type: 'text', nullable: true)]
    private $terminationReason;

    #[ORM\Column(type: 'text', nullable: true)]
    private $jobDescriptionSummary;

    #[ORM\Column(type: 'text', nullable: true)]
    private $jobDescription;

    /**
     * Attachment can have many DocumentContainers; each DocumentContainers can have many Documents; each DocumentContainers has document type (DocumentTypeList)
     **/
    #[ORM\OneToOne(targetEntity: 'AttachmentContainer', cascade: ['persist', 'remove'])]
    private $attachmentContainer;

    #[ORM\ManyToOne(targetEntity: 'Institution')]
    private $institution;

    /////// Fields for vacreq calculation ///////
    //effort in %
    #[ORM\Column(type: 'integer', nullable: true)]
    private $effort;

    //ignore this employment period in vacreq days calculation
    #[ORM\Column(type: 'boolean', nullable: true)]
    private $ignore;

    //Time Away Approval Group Type (VacReqApprovalTypeList)
    #[ORM\ManyToOne(targetEntity: 'App\VacReqBundle\Entity\VacReqApprovalTypeList')]
    #[ORM\JoinColumn(referencedColumnName: 'id', nullable: true)]
    private $approvalGroupType;
    /////// EOF Fields for vacreq calculation ///////


    

    public function __construct($author=null) {
        parent::__construct($author);
        $this->setType(self::TYPE_PRIVATE);
        $this->setStatus(self::STATUS_VERIFIED);

        //add one document
        $this->createAttachmentDocument();
    }

    /**
     * @param mixed $hireDate
     */
    public function setHireDate($hireDate)
    {
        $this->hireDate = $hireDate;
    }

    /**
     * @return mixed
     */
    public function getHireDate()
    {
        return $this->hireDate;
    }

    /**
     * @param mixed $terminationDate
     */
    public function setTerminationDate($terminationDate)
    {
        $this->terminationDate = $terminationDate;
    }

    /**
     * @return mixed
     */
    public function getTerminationDate()
    {
        return $this->terminationDate;
    }

    /**
     * @param mixed $terminationReason
     */
    public function setTerminationReason($terminationReason)
    {
        $this->terminationReason = $terminationReason;
    }

    /**
     * @return mixed
     */
    public function getTerminationReason()
    {
        return $this->terminationReason;
    }

    /**
     * @param mixed $terminationType
     */
    public function setTerminationType($terminationType)
    {
        $this->terminationType = $terminationType;
    }

    /**
     * @return mixed
     */
    public function getTerminationType()
    {
        return $this->terminationType;
    }

    /**
     * @param mixed $user
     */
    public function setUser($user)
    {
        $this->user = $user;
    }

    /**
     * @return mixed
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param mixed $employmentType
     */
    public function setEmploymentType($employmentType)
    {
        $this->employmentType = $employmentType;
    }

    /**
     * @return mixed
     */
    public function getEmploymentType()
    {
        return $this->employmentType;
    }

    /**
     * @param mixed $attachmentContainer
     */
    public function setAttachmentContainer($attachmentContainer)
    {
        $this->attachmentContainer = $attachmentContainer;
    }

    /**
     * @return mixed
     */
    public function getAttachmentContainer()
    {
        return $this->attachmentContainer;
    }

    /**
     * @param mixed $jobDescription
     */
    public function setJobDescription($jobDescription)
    {
        $this->jobDescription = $jobDescription;
    }

    /**
     * @return mixed
     */
    public function getJobDescription()
    {
        return $this->jobDescription;
    }

    /**
     * @param mixed $jobDescriptionSummary
     */
    public function setJobDescriptionSummary($jobDescriptionSummary)
    {
        $this->jobDescriptionSummary = $jobDescriptionSummary;
    }

    /**
     * @return mixed
     */
    public function getJobDescriptionSummary()
    {
        return $this->jobDescriptionSummary;
    }

    /**
     * @return mixed
     */
    public function getInstitution()
    {
        return $this->institution;
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
    public function getEffort()
    {
        return $this->effort;
    }

    /**
     * @param mixed $effort
     */
    public function setEffort($effort)
    {
        $this->effort = $effort;
    }

    /**
     * @return mixed
     */
    public function getIgnore()
    {
        return $this->ignore;
    }

    /**
     * @param mixed $ignore
     */
    public function setIgnore($ignore)
    {
        $this->ignore = $ignore;
    }

    /**
     * @return mixed
     */
    public function getApprovalGroupType()
    {
        return $this->approvalGroupType;
    }

    /**
     * @param mixed $approvalGroupType
     */
    public function setApprovalGroupType($approvalGroupType)
    {
        $this->approvalGroupType = $approvalGroupType;
    }




    //create attachmentDocument holder with one DocumentContainer if not exists
    public function createAttachmentDocument() {
        //add one document
        $attachmentContainer = $this->getAttachmentContainer();
        //echo "attachmentContainer=".$attachmentContainer."<br>";
        if( !$attachmentContainer ) {
            $attachmentContainer = new AttachmentContainer();
            $this->setAttachmentContainer($attachmentContainer);
        }
        if( count($attachmentContainer->getDocumentContainers()) == 0 ) {
            $attachmentContainer->addDocumentContainer( new DocumentContainer() );
        }
    }


    public function __toString() {

        $documentContainersCount = 0;
        $documentsCount = 0;
        $attachmentContainer = $this->getAttachmentContainer();
        if( $attachmentContainer ) {
            foreach( $attachmentContainer->getDocumentContainers() as $documentContainer ) {
                $documentContainersCount++;
                $documentsCount = $documentsCount + count($documentContainer->getDocuments());
            }
        }

        return "Employment Status: id=".$this->getId().", documentContainersCount=".$documentContainersCount.", documentsCount=".$documentsCount;;
    }


}