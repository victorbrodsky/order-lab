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

namespace App\OrderformBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use App\UserdirectoryBundle\Entity\DocumentContainer;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="scan_calllogEntryMessage")
 */
class CalllogEntryMessage extends OrderBase {

    /**
     * @ORM\OneToOne(targetEntity="Message", mappedBy="calllogEntryMessage")
     **/
    protected $message;


    //Patient List
    /**
     * Return order if not completed by deadline
     *
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $addPatientToList;

//    /**
//     * @ORM\ManyToOne(targetEntity="PatientListHierarchy")
//     **/
//    private $patientList;
    /**
     * @ORM\ManyToMany(targetEntity="PatientListHierarchy", inversedBy="calllogEntryMessages" )
     * @ORM\JoinTable(name="scan_calllogEntryMessage_patientList")
     **/
    private $patientLists;

    /**
     * Call Log Entry Tags
     * @ORM\ManyToMany(targetEntity="CalllogEntryTagsList", inversedBy="calllogEntryMessages" )
     * @ORM\JoinTable(name="scan_calllogEntryMessage_entryTag")
     **/
    private $entryTags;

    /**
     * Amount of Time Spent in Minutes
     * @ORM\Column(type="integer", nullable=true)
     */
    private $timeSpentMinutes;

//    /**
//     * Linked Object ID. Used to make a link to other lists in the list manager.
//     * @ORM\Column(type="string", nullable=true)
//     */
//    private $entityId;
//    /**
//     * Used to make a link to other lists in the list manager.
//     * i.e. "App\OrderformBundle\Entity"
//     * @ORM\Column(type="string", nullable=true)
//     */
//    private $entityNamespace;
//    /**
//     * Used to make a link to other lists in the list manager.
//     * i.e. "Patient"
//     * @ORM\Column(type="string", nullable=true)
//     */
//    private $entityName;

    //Within each message object, add the following variables for backup
    /**
     * Patient Last Name Backup
     * @ORM\Column(type="string", nullable=true)
     */
    private $patientLastNameBackup;

    /**
     * Patient First Name Backup
     * @ORM\Column(type="string", nullable=true)
     */
    private $patientFirstNameBackup;

    /**
     * Patient Middle Name Backup
     * @ORM\Column(type="string", nullable=true)
     */
    private $patientMiddleNameBackup;

    /**
     * Patient Date of Birth Backup
     * @ORM\Column(type="date", nullable=true)
     */
    private $patientDOBBackup;

    /**
     * Patient MRN Type Backup
     * @ORM\ManyToOne(targetEntity="MrnType", cascade={"persist"})
     */
    private $patientMRNTypeBackup;

    /**
     * Patient MRN Backup
     * @ORM\Column(type="string", nullable=true)
     */
    private $patientMRNBackup;

    /**
     * Encounter Number Type Backup
     * @ORM\ManyToOne(targetEntity="EncounterType", cascade={"persist"})
     */
    private $encounterTypeBackup;

    /**
     * Encounter Number Backup
     * @ORM\Column(type="string", nullable=true)
     */
    private $encounterNumberBackup;

    /**
     * Encounter Date Backup
     * @ORM\OneToOne(targetEntity="EncounterDate", cascade={"persist","remove"})
     */
    private $encounterDateBackup;

    //All Message related fields are already exists in Message objects
//    /**
//     * Message Type Backup
//     * @ORM\ManyToOne(targetEntity="MessageCategory", cascade={"persist"})
//     */
//    private $messageCategoryBackup;
//    /**
//     * Message Version Backup
//     * @ORM\Column(type="integer", nullable=true)
//     */
//    private $messageVersionBackup;
    //Form Type Backup
    //Form Version Backup
    
    /**
     * @ORM\ManyToMany(targetEntity="App\UserdirectoryBundle\Entity\Document", cascade={"persist","remove"})
     * @ORM\JoinTable(name="calllog_calllogentrymessage_document",
     *      joinColumns={@ORM\JoinColumn(name="message_id", referencedColumnName="id", onDelete="CASCADE")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="document_id", referencedColumnName="id", onDelete="CASCADE", unique=true)}
     *      )
     * @ORM\OrderBy({"createdate" = "ASC"})
     **/
    private $documents;

    /**
     * @ORM\ManyToOne(targetEntity="CalllogAttachmentTypeList")
     * @ORM\JoinColumn(name="calllogattachmenttype_id", referencedColumnName="id", nullable=true)
     */
    private $calllogAttachmentType;

    /**
     * Tasks for Call Log
     *
     * @ORM\OneToMany(targetEntity="CalllogTask", mappedBy="calllogEntryMessage", cascade={"persist"})
     **/
    private $calllogTasks;


    public function __construct() {

        $this->patientLists = new ArrayCollection();
        $this->entryTags = new ArrayCollection();
        $this->documents = new ArrayCollection();
        $this->calllogTasks = new ArrayCollection();

    }


    /**
     * @return mixed
     */
    public function getAddPatientToList()
    {
        return $this->addPatientToList;
    }

    /**
     * @param mixed $addPatientToList
     */
    public function setAddPatientToList($addPatientToList)
    {
        $this->addPatientToList = $addPatientToList;
    }



    public function addPatientList($item)
    {
        if( $item && !$this->patientLists->contains($item) ) {
            $this->patientLists->add($item);
        }
        return $this;
    }
    public function removePatientList($item)
    {
        $this->patientLists->removeElement($item);
    }
    public function getPatientLists()
    {
        return $this->patientLists;
    }

    public function addEntryTag($item)
    {
        if( $item && !$this->entryTags->contains($item) ) {
            $this->entryTags->add($item);
        }
        return $this;
    }
    public function removeEntryTag($item)
    {
        $this->entryTags->removeElement($item);
    }
    public function getEntryTags()
    {
        return $this->entryTags;
    }


    /**
     * @return mixed
     */
    public function getPatientLastNameBackup()
    {
        return $this->patientLastNameBackup;
    }

    /**
     * @param mixed $patientLastNameBackup
     */
    public function setPatientLastNameBackup($patientLastNameBackup)
    {
        $this->patientLastNameBackup = $patientLastNameBackup;
    }

    /**
     * @return mixed
     */
    public function getPatientFirstNameBackup()
    {
        return $this->patientFirstNameBackup;
    }

    /**
     * @param mixed $patientFirstNameBackup
     */
    public function setPatientFirstNameBackup($patientFirstNameBackup)
    {
        $this->patientFirstNameBackup = $patientFirstNameBackup;
    }

    /**
     * @return mixed
     */
    public function getPatientMiddleNameBackup()
    {
        return $this->patientMiddleNameBackup;
    }

    /**
     * @param mixed $patientMiddleNameBackup
     */
    public function setPatientMiddleNameBackup($patientMiddleNameBackup)
    {
        $this->patientMiddleNameBackup = $patientMiddleNameBackup;
    }

    /**
     * @return mixed
     */
    public function getPatientDOBBackup()
    {
        return $this->patientDOBBackup;
    }

    /**
     * @param mixed $patientDOBBackup
     */
    public function setPatientDOBBackup($patientDOBBackup)
    {
        $this->patientDOBBackup = $patientDOBBackup;
    }

    /**
     * @return mixed
     */
    public function getPatientMRNTypeBackup()
    {
        return $this->patientMRNTypeBackup;
    }

    /**
     * @param mixed $patientMRNTypeBackup
     */
    public function setPatientMRNTypeBackup(MrnType $patientMRNTypeBackup)
    {
        $this->patientMRNTypeBackup = $patientMRNTypeBackup;
    }

    /**
     * @return mixed
     */
    public function getPatientMRNBackup()
    {
        return $this->patientMRNBackup;
    }

    /**
     * @param mixed $patientMRNBackup
     */
    public function setPatientMRNBackup($patientMRNBackup)
    {
        $this->patientMRNBackup = $patientMRNBackup;
    }

    /**
     * @return mixed
     */
    public function getEncounterTypeBackup()
    {
        return $this->encounterTypeBackup;
    }

    /**
     * @param mixed $encounterTypeBackup
     */
    public function setEncounterTypeBackup(EncounterType $encounterTypeBackup)
    {
        $this->encounterTypeBackup = $encounterTypeBackup;
    }

    /**
     * @return mixed
     */
    public function getEncounterNumberBackup()
    {
        return $this->encounterNumberBackup;
    }

    /**
     * @param mixed $encounterNumberBackup
     */
    public function setEncounterNumberBackup($encounterNumberBackup)
    {
        $this->encounterNumberBackup = $encounterNumberBackup;
    }

    /**
     * @return mixed
     */
    public function getEncounterDateBackup()
    {
        return $this->encounterDateBackup;
    }

    /**
     * @param mixed $encounterDateBackup
     */
    public function setEncounterDateBackup($encounterDateBackup)
    {
        $this->encounterDateBackup = $encounterDateBackup;
    }

    /**
     * @return mixed
     */
    public function getTimeSpentMinutes()
    {
        return $this->timeSpentMinutes;
    }

    /**
     * @param mixed $timeSpentMinutes
     */
    public function setTimeSpentMinutes($timeSpentMinutes)
    {
        $this->timeSpentMinutes = $timeSpentMinutes;
    }

    public function addDocument($item)
    {
        if( $item && !$this->documents->contains($item) ) {
            $this->documents->add($item);
            $item->createUseObject($this);
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
    public function getCalllogAttachmentType()
    {
        return $this->calllogAttachmentType;
    }

    /**
     * @param mixed $calllogAttachmentType
     */
    public function setCalllogAttachmentType($calllogAttachmentType)
    {
        $this->calllogAttachmentType = $calllogAttachmentType;
    }

    public function addCalllogTask($item)
    {
        if( $item && !$this->calllogTasks->contains($item) ) {
            $item->setCalllogEntryMessage($this);
            $this->calllogTasks->add($item);
        }
        return $this;
    }
    public function removeCalllogTask($item)
    {
        $this->calllogTasks->removeElement($item);
    }
    public function getCalllogTasks()
    {
        return $this->calllogTasks;
    }


    
//    /**
//     * @return mixed
//     */
//    public function getEntityId()
//    {
//        return $this->entityId;
//    }
//
//    /**
//     * @param mixed $entityId
//     */
//    public function setEntityId($entityId)
//    {
//        $this->entityId = $entityId;
//    }
//
//    /**
//     * @return mixed
//     */
//    public function getEntityNamespace()
//    {
//        return $this->entityNamespace;
//    }
//
//    /**
//     * @param mixed $entityNamespace
//     */
//    public function setEntityNamespace($entityNamespace)
//    {
//        $this->entityNamespace = $entityNamespace;
//    }
//
//    /**
//     * @return mixed
//     */
//    public function getEntityName()
//    {
//        return $this->entityName;
//    }
//
//    /**
//     * @param mixed $entityName
//     */
//    public function setEntityName($entityName)
//    {
//        $this->entityName = $entityName;
//    }
//
//    public function setObject($object) {
//
//        $class = new \ReflectionClass($object);
//        $className = $class->getShortName();
//        $classNamespace = $class->getNamespaceName();
//
//        if( $className && !$this->getEntityName() ) {
//            $this->setEntityName($className);
//        }
//
//        if( $classNamespace && !$this->getEntityNamespace() ) {
//            $this->setEntityNamespace($classNamespace);
//        }
//
//        if( !$this->getEntityId() && $object->getId() ) {
//            //echo "setEntityId=".$object->getId()."<br>";
//            $this->setEntityId($object->getId());
//        }
//    }




    public function __toString() {
        $res = "Call Log Entry Message";
        if( $this->getId() ) {
            $res = $res . " with ID=" . $this->getId();
        }
        return $res;
    }

}