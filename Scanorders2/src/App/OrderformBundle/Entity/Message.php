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
use App\UserdirectoryBundle\Entity\GeneralEntity;
use App\UserdirectoryBundle\Entity\UserWrapper;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Validator\Constraints as Assert;

use Doctrine\Common\Collections\ArrayCollection;

/**
 * Message, Holder of different orders (i.e. scanorder, laborder)
 *
 * @ORM\Entity(repositoryClass="App\OrderformBundle\Repository\MessageRepository")
 * @ORM\Table(name="scan_message",
 *  indexes={
 *      @ORM\Index( name="oid_idx", columns={"oid"} )
 *  }
 * )
 * @ORM\HasLifecycleCallbacks
 */
class Message {

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /////////////////    HIERARCHY OBJECTS    //////////////////////
    /**
     * @ORM\ManyToMany(targetEntity="Patient", inversedBy="message" )
     * @ORM\JoinTable(name="scan_message_patient")
     **/
    private $patient;

    /**
     * @ORM\ManyToMany(targetEntity="Encounter", inversedBy="message")
     * @ORM\JoinTable(name="scan_message_encounter")
     **/
    private $encounter;

    /**
     * @ORM\ManyToMany(targetEntity="Procedure", inversedBy="message")
     * @ORM\JoinTable(name="scan_message_procedure")
     **/
    private $procedure;

    /**
     * @ORM\ManyToMany(targetEntity="Accession", inversedBy="message")
     * @ORM\JoinTable(name="scan_message_accession")
     **/
    private $accession;

    /**
     * @ORM\ManyToMany(targetEntity="Part", inversedBy="message")
     * @ORM\JoinTable(name="scan_message_part")
     **/
    private $part;

    /**
     * @ORM\ManyToMany(targetEntity="Block", inversedBy="message")
     * @ORM\JoinTable(name="scan_message_block")
     **/
    private $block;

    /**
     * @ORM\ManyToMany(targetEntity="Slide", inversedBy="message")
     * @ORM\JoinTable(name="scan_message_slide")
     **/
    private $slide;

    /**
     * @ORM\ManyToMany(targetEntity="Imaging", inversedBy="message")
     * @ORM\JoinTable(name="scan_message_imaging")
     **/
    private $imaging;
    /////////////////   EOF  HIERARCHY OBJECTS    //////////////////////


    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $idnumber;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $comment;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="orderdate", type="datetime", nullable=true)
     *
     */
    private $orderdate;

    /**
     * MessageCategory with subcategory (parent-children hierarchy)
     *
     * @ORM\ManyToOne(targetEntity="MessageCategory", cascade={"persist"})
     */
    private $messageCategory;

    /**
     * Form nodes fields cache (snapshot) used in spreadsheet
     * @ORM\Column(type="text", nullable=true)
     */
    private $formnodesCache;

    /**
     * Patient's name cache
     * @ORM\Column(type="string", nullable=true)
     */
    private $patientNameCache;

    /**
     * Patient's mrn cache
     * @ORM\Column(type="string", nullable=true)
     */
    private $patientMrnCache;

//    /**
//     * Form nodes fields cache (snapshot) used in list and view as table
//     * @ORM\Column(type="text", nullable=true)
//     */
//    private $formnodesCacheTable;

    /**
     * Name of the form: Message Type name at the time of message submission
     * @ORM\Column(type="string", nullable=true)
     */
    private $messageTitle;

    /**
     * @ORM\ManyToOne(targetEntity="App\UserdirectoryBundle\Entity\User")
     */
    private $provider;

    /**
     * @ORM\ManyToMany(targetEntity="App\UserdirectoryBundle\Entity\UserWrapper", cascade={"persist","remove"})
     * @ORM\JoinTable(name="scan_message_user",
     *      joinColumns={@ORM\JoinColumn(name="message_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="user_id", referencedColumnName="id")}
     *      )
     **/
    private $proxyuser;

    /**
     * @ORM\ManyToMany(targetEntity="App\UserdirectoryBundle\Entity\UserWrapper", cascade={"persist","remove"})
     * @ORM\JoinTable(name="scan_message_orderRecipient",
     *      joinColumns={@ORM\JoinColumn(name="message_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="orderRecipient_id", referencedColumnName="id")}
     *      )
     **/
    private $orderRecipients;

    /**
     * @ORM\ManyToMany(targetEntity="App\UserdirectoryBundle\Entity\UserWrapper", cascade={"persist","remove"})
     * @ORM\JoinTable(name="scan_message_reportRecipient",
     *      joinColumns={@ORM\JoinColumn(name="message_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="reportRecipient_id", referencedColumnName="id")}
     *      )
     **/
    private $reportRecipients;

    /**
     * @ORM\ManyToMany(targetEntity="App\UserdirectoryBundle\Entity\InstitutionWrapper", cascade={"persist","remove"})
     * @ORM\JoinTable(name="scan_message_organizationRecipient",
     *      joinColumns={@ORM\JoinColumn(name="message_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="organizationRecipient_id", referencedColumnName="id")}
     *      )
     **/
    private $organizationRecipients;

    /**
     * Institutional PHI Scope: users with the same Institutional PHI Scope can view the data of this order
     *
     * @ORM\ManyToOne(targetEntity="App\UserdirectoryBundle\Entity\Institution")
     */
    private $institution;

    /**
     * @ORM\ManyToOne(targetEntity="Status")
     */
    private $status;

    /**
     * @ORM\ManyToOne(targetEntity="MessageStatusList")
     */
    private $messageStatus;

    /**
     * Message Status Prior to Deletion
     * @ORM\ManyToOne(targetEntity="MessageStatusList")
     */
    private $messageStatusPrior;

    /**
     * @ORM\OneToOne(targetEntity="App\UserdirectoryBundle\Entity\ModifierInfo", cascade={"persist","remove"})
     */
    private $signeeInfo;

    /**
     * @ORM\ManyToMany(targetEntity="App\UserdirectoryBundle\Entity\ModifierInfo", cascade={"persist","remove"})
     * @ORM\JoinTable(name="scan_message_editors",
     *      joinColumns={@ORM\JoinColumn(name="message_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="editorInfo_id", referencedColumnName="id", unique=true)}
     * )
     * @ORM\OrderBy({"modifiedOn" = "ASC"})
     **/
    private $editorInfos;

    /**
     * Equipment associated with this order (object)
     *
     * @ORM\ManyToOne(targetEntity="App\UserdirectoryBundle\Entity\Equipment")
     */
    private $equipment;

    /**
     * Purpose of Order (string)
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $purpose;

    /**
     * Order priority: routine, stat
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $priority;

    /**
     * Order completetion deadline
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $deadline;

    /**
     * Return order if not completed by deadline
     *
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $returnoption;


    /**
     * oid - id of the original order. Required for amend logic.
     * When Amend order, switch orders to keep the original id and at newly created order set oid of the original order
     * @var string
     * @ORM\Column(type="integer", nullable=true)
     */
    private $oid;

    /**
     * Account of the order
     *
     * @ORM\ManyToOne(targetEntity="Account", inversedBy="message", cascade={"persist"})
     * @ORM\JoinColumn(name="account_id", referencedColumnName="id", nullable=true)
     */
    private $account;

    /**
     * @ORM\OneToOne(
     *      targetEntity="Educational",
     *      inversedBy="message",
     *      cascade={"persist"}
     * )
     * @ORM\JoinColumn(
     *      name="educational_id",
     *      referencedColumnName="id"
     * )
     */
    private $educational;

    /**
     * @ORM\OneToOne(
     *      targetEntity="Research",
     *      inversedBy="message",
     *      cascade={"persist"}
     * )
     * @ORM\JoinColumn(
     *      name="research_id",
     *      referencedColumnName="id"
     * )
     */
    private $research;



//    /**
//     * History of the order
//     *
//     * @ORM\ManyToMany(targetEntity="History")
//     * @ORM\JoinTable(name="scan_message_history",
//     *      joinColumns={@ORM\JoinColumn(name="message_id", referencedColumnName="id")},
//     *      inverseJoinColumns={@ORM\JoinColumn(name="history_id", referencedColumnName="id", unique=true)}
//     *      )
//     **/
//    private $history;


    /**
     * Conflicting accession number is replaced, so keep the reference to dataqualitymrnacc object in the message (unlike to dataqualityage)
     * Any message (order) has referenece to patient and accession, hence can create conflict
     *
     * @ORM\OneToMany(targetEntity="DataQualityMrnAcc", mappedBy="message", cascade={"persist"})
     */
    private $dataqualitymrnacc;

    /**
     * @ORM\OneToMany(targetEntity="History", mappedBy="message", cascade={"persist"})
     */
    private $history;

//    /**
//     * Tracking: can be many
//     * One-To-Many unidirectional with Join table
//     *
//     * @ORM\ManyToMany(targetEntity="Tracking")
//     * @ORM\JoinTable(name="scan_message_tracking",
//     *      joinColumns={@ORM\JoinColumn(name="message_id", referencedColumnName="id")},
//     *      inverseJoinColumns={@ORM\JoinColumn(name="tracking_id", referencedColumnName="id", unique=true)}
//     *      )
//     **/
//    private $tracking;





    /**
     * One-To-Many Unidirectional
     *
     * @ORM\ManyToMany(targetEntity="App\UserdirectoryBundle\Entity\GeneralEntity", cascade={"persist","remove"})
     * @ORM\JoinTable(name="scan_message_input",
     *      joinColumns={@ORM\JoinColumn(name="message_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="input_id", referencedColumnName="id", unique=true)}
     *      )
     */
    private $inputs;

    /**
     * One-To-Many Unidirectional
     *
     * @ORM\ManyToMany(targetEntity="App\UserdirectoryBundle\Entity\GeneralEntity", cascade={"persist","remove"})
     * @ORM\JoinTable(name="scan_message_output",
     *      joinColumns={@ORM\JoinColumn(name="message_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="output_id", referencedColumnName="id", unique=true)}
     *      )
     */
    private $outputs;


    /**
     * @ORM\ManyToMany(targetEntity="Message", mappedBy="associations")
     **/
    private $backAssociations;

    /**
     * @ORM\ManyToMany(targetEntity="Message", inversedBy="backAssociations", cascade={"persist","remove"})
     * @ORM\JoinTable(name="scan_message_associations",
     *      joinColumns={@ORM\JoinColumn(name="message_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="association_id", referencedColumnName="id")}
     *      )
     **/
    private $associations;


    /**
     * Sources: can be many
     * Source: Location, System, comment(string)
     * One-To-Many unidirectional with Join table
     *
     * @ORM\ManyToMany(targetEntity="Endpoint", cascade={"persist","remove"})
     * @ORM\JoinTable(name="scan_message_source",
     *      joinColumns={@ORM\JoinColumn(name="message_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="source_id", referencedColumnName="id", unique=true)}
     *      )
     **/
    private $sources;

    /**
     * Destinations: can be many
     * One-To-Many unidirectional with Join table
     *
     * @ORM\ManyToMany(targetEntity="Endpoint", cascade={"persist","remove"})
     * @ORM\JoinTable(name="scan_message_destination",
     *      joinColumns={@ORM\JoinColumn(name="message_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="destination_id", referencedColumnName="id", unique=true)}
     *      )
     **/
    private $destinations;


    /**
     * Attachment can have many DocumentContainers; each DocumentContainers can have many Documents; each DocumentContainers has document type (DocumentTypeList)
     * @ORM\OneToOne(targetEntity="App\UserdirectoryBundle\Entity\AttachmentContainer", cascade={"persist","remove"})
     **/
    private $attachmentContainer;

    /**
     * @ORM\OneToMany(targetEntity="ExternalId", mappedBy="message", cascade={"persist","remove"})
     */
    private $externalIds;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $version;

    /**
     * @ORM\ManyToOne(targetEntity="AmendmentReasonList")
     */
    private $amendmentReason;

    /**
     * @ORM\OneToMany(targetEntity="FormVersion", mappedBy="message", cascade={"persist","remove"})
     */
    private $formVersions;

    ////////////////////////// Specific Messages //////////////////////////

    /**
     * @ORM\OneToOne(targetEntity="SlideReturnRequest", inversedBy="message", cascade={"persist","remove"})
     **/
    private $slideReturnRequest;

    /**
     * @ORM\OneToOne(targetEntity="ScanOrder", inversedBy="message", cascade={"persist","remove"})
     **/
    private $scanorder;

    /**
     * @ORM\OneToOne(targetEntity="ProcedureOrder", inversedBy="message", cascade={"persist","remove"})
     **/
    private $procedureorder;

    /**
     * @ORM\OneToOne(targetEntity="LabOrder", inversedBy="message", cascade={"persist","remove"})
     */
    private $laborder;

    /**
     * @ORM\OneToOne(targetEntity="BlockOrder", inversedBy="message", cascade={"persist","remove"})
     */
    private $blockorder;

    /**
     * @ORM\OneToOne(targetEntity="SlideOrder", inversedBy="message", cascade={"persist","remove"})
     */
    private $slideorder;

    /**
     * @ORM\OneToOne(targetEntity="StainOrder", inversedBy="message", cascade={"persist","remove"})
     */
    private $stainorder;

    /**
     * Image Analysis Order
     * @ORM\OneToOne(targetEntity="ImageAnalysisOrder", inversedBy="message", cascade={"persist","remove"})
     */
    private $imageAnalysisOrder;

    /**
     * @ORM\OneToOne(targetEntity="Report", inversedBy="message", cascade={"persist","remove"})
     */
    private $report;

    /**
     * @ORM\OneToOne(targetEntity="ReportBlock", inversedBy="message", cascade={"persist","remove"})
     */
    private $reportBlock;

    /**
     * Pathology Call Log Entry (similar to the LabOrder)
     * @ORM\OneToOne(targetEntity="CalllogEntryMessage", inversedBy="message", cascade={"persist","remove"})
     */
    private $calllogEntryMessage;

    ////////////////////////// EOF Specific Messages //////////////////////////




    /**
     * Constructor
     */
    public function __construct()
    {
        $this->patient = new ArrayCollection();
        $this->encounter = new ArrayCollection();
        $this->procedure = new ArrayCollection();
        $this->accession = new ArrayCollection();
        $this->part = new ArrayCollection();      
        $this->block = new ArrayCollection();
        $this->slide = new ArrayCollection();
        $this->imaging = new ArrayCollection();

        $this->proxyuser = new ArrayCollection();
        $this->orderRecipients = new ArrayCollection();
        $this->reportRecipients = new ArrayCollection();
        $this->organizationRecipients = new ArrayCollection();

        $this->dataqualitymrnacc = new ArrayCollection();
        $this->history = new ArrayCollection();
        //$this->tracking = new ArrayCollection();

        //TODO: test cloning
        $this->sources = new ArrayCollection();
        $this->destinations = new ArrayCollection();
        $this->externalIds = new ArrayCollection();
        $this->formVersions = new ArrayCollection();

        //links
        //TODO: test cloning
        $this->inputs = new ArrayCollection();
        $this->outputs = new ArrayCollection();
        $this->associations = new ArrayCollection();
        $this->backAssociations = new ArrayCollection();

        $this->editorInfos = new ArrayCollection();

        $this->setOrderdate();

        //Initialize specific orders
//        if( !$this->getScanorder() ) {
//            $this->setScanorder(new ScanOrder());
//        }
//        if( !$this->getLaborder() ) {
//            $this->setLaborder(new LabOrder());
//        }
//        $this->setScanorder(null);
//        $this->setLaborder(null);
//        $this->setSlideReturnRequest(null);

    }

    public function __clone() {
        //throw new \Exception('Cloning message');

        if( $this->getId() ) {
            $this->setId(null);

            $children = $this->getPatient(); // Get current collection

            if( !$children ) return;

            $this->patient = new ArrayCollection();
            $this->encounter = new ArrayCollection();
            $this->procedure = new ArrayCollection();
            $this->accession = new ArrayCollection();
            $this->part = new ArrayCollection();
            $this->block = new ArrayCollection();
            $this->slide = new ArrayCollection();
            $this->imaging = new ArrayCollection();

            //links
            $this->inputs = new ArrayCollection();
            $this->outputs = new ArrayCollection();
            $this->associations = new ArrayCollection();
            $this->backAssociations = new ArrayCollection();

            $this->sources = new ArrayCollection();
            $this->destinations = new ArrayCollection();
            $this->externalIds = new ArrayCollection();
            $this->formVersions = new ArrayCollection();

            //$this->editorInfos = new ArrayCollection();

            //
            $provider = $this->getProvider();
            //$proxyuser = $this->getProxyuser();
            $dataqualitiesmrnacc = $this->getDataqualityMrnAcc();
            $histories = $this->getHistory();
            //$trackings = $this->getTracking();

            //$this->setProvider( new ArrayCollection() );
            //$this->proxyuser = new ArrayCollection();
            $this->dataqualitymrnacc = new ArrayCollection();
            $this->history = new ArrayCollection();
            //$this->tracking = new ArrayCollection();

            $this->setProvider( $provider );
            //$this->setProxyuser( $proxyuser );

            foreach( $dataqualitiesmrnacc as $dataqualitymrnacc ) {
                $this->addDataqualityMrnAcc($dataqualitymrnacc);
            }

            foreach( $histories as $history ) {
                $this->addHistory($history);
            }

//            foreach( $trackings as $tracking ) {
//                $this->addTracking($tracking);
//            }

            foreach( $children as $child ) {
                //echo "1 clone Children: ".$child;
                $this->removeDepend($child);

                $cloneChild = clone $child;

                //$cloneChild->removeMessage($this);

                $cloneChild->cloneChildren($this);

                //$this->patient->add($cloneChild);
                $this->addDepend($cloneChild);  //$message->addPatient();
                //$cloneChild->addMessage($this);
                //echo "2 cloned Children: ".$cloneChild;
            }


            //Specific Orders
//            $scanorder = $this->getScanorder();
//            $this->setScanorder($scanorder);
              
        }

    }

    public function removeDepend( $depend ) {
        $class = new \ReflectionClass($depend);
        $className = $class->getShortName();    //Part
        $removeMethod = "remove".$className;
        $this->$removeMethod($depend);
    }

    public function addDepend( $depend ) {
        $class = new \ReflectionClass($depend);
        $className = $class->getShortName();    //Part
        $addMethod = "add".$className;
        $this->$addMethod($depend);
    }


    /**
     * @param mixed $idnumber
     */
    public function setIdnumber($idnumber)
    {
        $this->idnumber = $idnumber;
    }

    /**
     * @return mixed
     */
    public function getIdnumber()
    {
        return $this->idnumber;
    }

    /**
     * @param mixed $comment
     */
    public function setComment($comment)
    {
        $this->comment = $comment;
    }

    /**
     * @return mixed
     */
    public function getComment()
    {
        return $this->comment;
    }

    /**
     * @param mixed $deadline
     */
    public function setDeadline($deadline)
    {
        $this->deadline = $deadline;
    }

    /**
     * @return mixed
     */
    public function getDeadline()
    {
        return $this->deadline;
    }

    /**
     * @param mixed $equipment
     */
    public function setEquipment($equipment)
    {
        $this->equipment = $equipment;
    }

    /**
     * @return mixed
     */
    public function getEquipment()
    {
        return $this->equipment;
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


//    /**
//     * @ORM\PrePersist
//     */
    public function setOrderdate($date=null) {
        //exit('exit setOrderdate');
        if( $date ) {
            //echo "date provided <br>";
            $this->orderdate = $date;
        } else {
            //echo "date now <br>";
            $this->orderdate = new \DateTime();
        }
        //exit('exit setOrderdate for message ID='.$this->getId());
    }

    /**
     * @return \DateTime
     */
    public function getOrderdate()
    {
        return $this->orderdate;
    }

    /**
     * @param mixed $priority
     */
    public function setPriority($priority)
    {
        $this->priority = $priority;
    }

    /**
     * @return mixed
     */
    public function getPriority()
    {
        return $this->priority;
    }

    /**
     * @param mixed $provider
     */
    public function setProvider($provider)
    {
        $this->provider = $provider;
    }

    /**
     * @return mixed
     */
    public function getProvider()
    {
        return $this->provider;
    }

    public function getProxyuser()
    {
        return $this->proxyuser;
    }
    public function addProxyuser($item)
    {
        if( $item && !$this->proxyuser->contains($item) ) {
            $this->proxyuser->add($item);
        }
        return $this;
    }
    public function removeProxyuser($item)
    {
        $this->proxyuser->removeElement($item);
    }
    public function addProxyuserAsUser($user)
    {
        if( $user ) {
            $userWrapper = new UserWrapper();
            $userWrapper->setUser($user);
            $this->addProxyuser($userWrapper);
        }
        return $this;
    }
    public function getProxyuserAsUser()
    {
        $proxyuser = null;
        $proxyuserWrapper = $this->getProxyuser()->first();
        if( $proxyuserWrapper ) {
            $proxyuser = $proxyuserWrapper->getUser();
        }
        return $proxyuser;
    }


    public function getOrderRecipients()
    {
        return $this->orderRecipients;
    }
    public function addOrderRecipient($item)
    {
        if( $item && !$this->orderRecipients->contains($item) ) {
            $this->orderRecipients->add($item);
        }
        return $this;
    }
    public function removeOrderRecipient($item)
    {
        $this->orderRecipients->removeElement($item);
    }

    public function getReportRecipients()
    {
        return $this->reportRecipients;
    }
    public function addReportRecipient($item)
    {
        if( $item && !$this->reportRecipients->contains($item) ) {
            $this->reportRecipients->add($item);
        }
        return $this;
    }
    public function removeReportRecipient($item)
    {
        $this->reportRecipients->removeElement($item);
    }

    public function getOrganizationRecipients()
    {
        return $this->organizationRecipients;
    }
    public function addOrganizationRecipient($item)
    {
        if( $item && !$this->organizationRecipients->contains($item) ) {
            $this->organizationRecipients->add($item);
        }
        return $this;
    }
    public function removeOrganizationRecipient($item)
    {
        $this->organizationRecipients->removeElement($item);
    }


    /**
     * @param mixed $purpose
     */
    public function setPurpose($purpose)
    {
        $this->purpose = $purpose;
    }

    /**
     * @return mixed
     */
    public function getPurpose()
    {
        return $this->purpose;
    }

    /**
     * @param mixed $returnoption
     */
    public function setReturnoption($returnoption)
    {
        $this->returnoption = $returnoption;
    }

    /**
     * @return mixed
     */
    public function getReturnoption()
    {
        return $this->returnoption;
    }

    /**
     * @param mixed $status
     */
    public function setStatus($status)
    {
        $this->status = $status;
    }

    /**
     * @return mixed
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @return mixed
     */
    public function getMessageStatus()
    {
        return $this->messageStatus;
    }

    /**
     * @param mixed $messageStatus
     */
    public function setMessageStatus($messageStatus)
    {
        $this->messageStatus = $messageStatus;
    }

    /**
     * @return mixed
     */
    public function getMessageStatusPrior()
    {
        return $this->messageStatusPrior;
    }

    /**
     * @param mixed $messageStatusPrior
     */
    public function setMessageStatusPrior($messageStatusPrior)
    {
        $this->messageStatusPrior = $messageStatusPrior;
    }

    /**
     * @param mixed $messageCategory
     */
    public function setMessageCategory($messageCategory)
    {
        $this->messageCategory = $messageCategory;
    }

    /**
     * @return mixed
     */
    public function getMessageCategory()
    {
        return $this->messageCategory;
    }

    /**
     * @return mixed
     */
    public function getFormnodesCache()
    {
        return $this->formnodesCache;
    }

    /**
     * @param mixed $formnodesCache
     */
    public function setFormnodesCache($formnodesCache)
    {
        $this->formnodesCache = $formnodesCache;
    }

    /**
     * @return mixed
     */
    public function getPatientNameCache()
    {
        return $this->patientNameCache;
    }

    /**
     * @param mixed $patientNameCache
     */
    public function setPatientNameCache($patientNameCache)
    {
        $this->patientNameCache = $patientNameCache;
    }

    /**
     * @return mixed
     */
    public function getPatientMrnCache()
    {
        return $this->patientMrnCache;
    }

    /**
     * @param mixed $patientMrnCache
     */
    public function setPatientMrnCache($patientMrnCache)
    {
        $this->patientMrnCache = $patientMrnCache;
    }

    /**
     * @return mixed
     */
    public function getMessageTitle()
    {
        return $this->messageTitle;
    }

    /**
     * @param mixed $messageTitle
     */
    public function setMessageTitle($messageTitle)
    {
        $this->messageTitle = $messageTitle;
    }
    //show the name of the form (from the form hierarchy) that was used to generate this submitted message.
    // Make sure to save this form ID of the form linked from the Message Type at the time of message submission
    public function getMessageTitleStr()
    {
        $title = "";
        if( $this->getMessageCategory() ) {
            $title = $this->getMessageCategory()->getNodeNameWithParent() . " (ID " . $this->getMessageCategory()->getId() . ")";
        }

        return $title;
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



    public function getDataqualityMrnAcc()
    {
        return $this->dataqualitymrnacc;
    }

    public function setDataqualityMrnAcc($dataqualitiesmrnacc)
    {
        if( $dataqualitiesmrnacc == null ) {
            $dataqualitiesmrnacc = new ArrayCollection();
        }
        $this->dataqualitymrnacc = $dataqualitiesmrnacc;
    }

    public function addDataqualityMrnAcc($dataqualitymrnacc)
    {
        if( !$this->dataqualitymrnacc->contains($dataqualitymrnacc) ) {
            $this->dataqualitymrnacc->add($dataqualitymrnacc);
        }
    }

    public function removeDataqualityMrnAcc($dataqualitymrnacc)
    {
        $this->dataqualitymrnacc->removeElement($dataqualitymrnacc);
    }

    public function getHistory()
    {
        return $this->history;
    }
    public function addHistory($history)
    {
        if( !$this->history->contains($history) ) {
            $this->history->add($history);
        }
    }
    public function removeHistory($history)
    {
        $this->history->removeElement($history);
    }

//    public function getTracking()
//    {
//        return $this->tracking;
//    }
//    public function addTracking($tracking)
//    {
//        if( $tracking && !$this->tracking->contains($tracking) ) {
//            $this->tracking->add($tracking);
//        }
//        return $this;
//    }
//    public function removeTracking($tracking)
//    {
//        $this->tracking->removeElement($tracking);
//    }

    public function getSources()
    {
        return $this->sources;
    }
    public function addSource($item)
    {
        if( $item && !$this->sources->contains($item) ) {
            $this->sources->add($item);
        }
        return $this;
    }
    public function removeSource($item)
    {
        $this->sources->removeElement($item);
    }

    public function getDestinations()
    {
        return $this->destinations;
    }
    public function addDestination($item)
    {
        if( $item && !$this->destinations->contains($item) ) {
            $this->destinations->add($item);
        }
        return $this;
    }
    public function removeDestination($item)
    {
        $this->destinations->removeElement($item);
    }

    /**
     * @param mixed $account
     */
    public function setAccount($account)
    {
        $this->account = $account;
    }

    /**
     * @return mixed
     */
    public function getAccount()
    {
        return $this->account;
    }

//    /**
//     * @param mixed $purpose
//     */
//    public function setPurpose($purpose)
//    {
//        $this->purpose = $purpose;
//    }
//
//    /**
//     * @return mixed
//     */
//    public function getPurpose()
//    {
//        return $this->purpose;
//    }



    /**
     * @param string $oid
     */
    public function setOid($oid=null)
    {
        if( $oid == null ) {
            $oid = $this->getId();
        }
        $this->oid = $oid;
    }

    /**
     * @return string
     */
    public function getOid()
    {
        return $this->oid;
//        if( $this->oid ) {
//            return $this->oid;
//        } else {
//            return $this->id;
//        }
    }


    /**
     * @param mixed $educational
     */
    public function setEducational($educational)
    {
        $this->educational = $educational;
    }

    /**
     * @return mixed
     */
    public function getEducational()
    {
        return $this->educational;
    }

    /**
     * @param mixed $research
     */
    public function setResearch($research)
    {
        $this->research = $research;
    }

    /**
     * @return mixed
     */
    public function getResearch()
    {
        return $this->research;
    }

    public function addExternalId($item)
    {
        if( $item && !$this->externalIds->contains($item) ) {
            $this->externalIds->add($item);
            $item->setMessage($this);
        }
        return $this;
    }
    public function removeExternalId($item)
    {
        $this->externalIds->removeElement($item);
    }
    /**
     * @return mixed
     */
    public function getExternalIds()
    {
        return $this->externalIds;
    }

    public function addFormVersion($item)
    {
        if( $item && !$this->formVersions->contains($item) ) {
            $this->formVersions->add($item);
            $item->setMessage($this);
        }
        return $this;
    }
    public function removeFormVersion($item)
    {
        $this->formVersions->removeElement($item);
    }
    /**
     * @return mixed
     */
    public function getFormVersions()
    {
        return $this->formVersions;
    }

    /**
     * @return mixed
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * @param mixed $version
     */
    public function setVersion($version)
    {
        $this->version = $version;
    }

    /**
     * @return mixed
     */
    public function getAmendmentReason()
    {
        return $this->amendmentReason;
    }

    /**
     * @param mixed $amendmentReason
     */
    public function setAmendmentReason($amendmentReason)
    {
        $this->amendmentReason = $amendmentReason;
    }

    /**
     * @return mixed
     */
    public function getSigneeInfo()
    {
        return $this->signeeInfo;
    }

    /**
     * @param mixed $signeeInfo
     */
    public function setSigneeInfo($signeeInfo)
    {
        $this->signeeInfo = $signeeInfo;
    }

    public function addEditorInfo($item)
    {
        if( $item && !$this->editorInfos->contains($item) ) {
            $this->editorInfos->add($item);
        }
        return $this;
    }
    public function removeEditorInfo($item)
    {
        $this->editorInfos->removeElement($item);
    }
    /**
     * @return mixed
     */
    public function getEditorInfos()
    {
        return $this->editorInfos;
    }


    ///////////////////// Hierarchy objects /////////////////////
    /**
     * Add patient
     *
     * @param \App\OrderformBundle\Entity\Patient $patient
     * @return Message
     */
    public function addPatient(\App\OrderformBundle\Entity\Patient $patient)
    {
        //echo "############### Message addPatient=".$patient."<br>";
        if( !$this->patient->contains($patient) ) {            
            $this->patient->add($patient);
        }               
    }

    /**
     * Remove patient
     *
     * @param \App\OrderformBundle\Entity\Patient $patient
     */
    public function removePatient(\App\OrderformBundle\Entity\Patient $patient)
    {      
        $this->patient->removeElement($patient);
    }

    /**
     * Get patient
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getPatient()
    {
        return $this->patient;
    }

    public function clearPatient()
    {
        foreach( $this->patient as $thispatient ) {
            $this->removePatient($thispatient);
        }
        //$this->patient->clear();
    }
    public function clearEncounter()
    {
        foreach( $this->encounter as $thisencounter ) {
            $this->removeEncounter($thisencounter);
        }
        //$this->patient->clear();
    }

    public function getValidEncounter() {
        foreach( $this->getEncounter() as $encounter  ) {
            if( $encounter->getStatus() == 'valid' ) {
                return $encounter;
            }
        }
        return null;
    }
    
    /**
     * Add slide
     *
     * @param \App\OrderformBundle\Entity\Slide $slide
     * @return Message
     */
    public function addSlide(\App\OrderformBundle\Entity\Slide $slide)
    {
        if( !$this->slide->contains($slide) ) {
            $this->slide->add($slide);
        }    
    }

    /**
     * Remove slide
     *
     * @param \App\OrderformBundle\Entity\Slide $slide
     */
    public function removeSlide(\App\OrderformBundle\Entity\Slide $slide)
    {
        $this->slide->removeElement($slide);
    }

    /**
     * Get slide
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getSlide()
    {
        return $this->slide;
    }

    public function addEncounter(\App\OrderformBundle\Entity\Encounter $encounter)
    {
        if( !$this->encounter->contains($encounter) ) {
            $this->encounter->add($encounter);
        }
    }
    public function removeEncounter(\App\OrderformBundle\Entity\Encounter $encounter)
    {
        $this->encounter->removeElement($encounter);
    }
    public function getEncounter()
    {
        return $this->encounter;
    }

    /**
     * Add procedure
     *
     * @param \App\OrderformBundle\Entity\procedure $procedure
     * @return Message
     */
    public function addProcedure(\App\OrderformBundle\Entity\Procedure $procedure)
    {         
        if( !$this->procedure->contains($procedure) ) {            
            $this->procedure->add($procedure);
        }   
    }

    /**
     * Remove procedure
     *
     * @param \App\OrderformBundle\Entity\Procedure $procedure
     */
    public function removeProcedure(\App\OrderformBundle\Entity\Procedure $procedure)
    {
        $this->procedure->removeElement($procedure);
    }

    /**
     * Get procedure
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getProcedure()
    {
        return $this->procedure;
    }

    /**
     * Add accession
     *
     * @param \App\OrderformBundle\Entity\Accession $accession
     * @return Message
     */
    public function addAccession(\App\OrderformBundle\Entity\Accession $accession)
    {
        if( !$this->accession->contains($accession) ) {            
            $this->accession->add($accession);
        }
    }

    /**
     * Remove accession
     *
     * @param \App\OrderformBundle\Entity\Accession $accession
     */
    public function removeAccession(\App\OrderformBundle\Entity\Accession $accession)
    {
        $this->accession->removeElement($accession);
    }

    /**
     * Get accession
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getAccession()
    {
        return $this->accession;
    }

    /**
     * Add part
     *
     * @param \App\OrderformBundle\Entity\Part $part
     * @return Message
     */
    public function addPart(\App\OrderformBundle\Entity\Part $part)
    {     
        if( !$this->part->contains($part) ) {            
            $this->part->add($part);
        }  
    }

    /**
     * Remove part
     *
     * @param \App\OrderformBundle\Entity\Part $part
     */
    public function removePart(\App\OrderformBundle\Entity\Part $part)
    {
        $this->part->removeElement($part);
    }

    /**
     * Get part
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getPart()
    {
        return $this->part;
    }

    /**
     * Add block
     *
     * @param \App\OrderformBundle\Entity\Block $block
     * @return Message
     */
    public function addBlock(\App\OrderformBundle\Entity\Block $block)
    {      
        if( !$this->block->contains($block) ) {            
            $this->block->add($block);
        }  
    }

    /**
     * Remove block
     *
     * @param \App\OrderformBundle\Entity\Block $block
     */
    public function removeBlock(\App\OrderformBundle\Entity\Block $block)
    {
        $this->block->removeElement($block);
    }

    /**
     * Get block
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getBlock()
    {
        return $this->block;
    }

    public function getImaging()
    {
        return $this->imaging;
    }
    public function addImaging($item)
    {
        if( $item && !$this->imaging->contains($item) ) {
            $this->imaging->add($item);
        }
    }
    public function removeImaging($item)
    {
        $this->imaging->removeElement($item);
    }
    ///////////////////// EOF Hierarchy objects /////////////////////


    //Links
    public function getInputs()
    {
        return $this->inputs;
    }
    public function addInput($input)
    {
        if( !$this->inputs->contains($input) ) {
            $this->inputs->add($input);
        }
    }
    public function removeInput($input)
    {
        $this->inputs->removeElement($input);
    }

    public function getOutputs()
    {
        return $this->outputs;
    }
    public function addOutput($output)
    {
        if( !$this->outputs->contains($output) ) {
            $this->outputs->add($output);
        }
    }
    public function removeOutput($output)
    {
        $this->outputs->removeElement($output);
    }

    public function getAssociations()
    {
        return $this->associations;
    }
    public function addAssociation($item)
    {
        if( !$this->associations->contains($item) ) {
            $this->associations->add($item);
            $item->addBackAssociation($this);
        }
    }
    public function removeAssociation($item)
    {
        $this->associations->removeElement($item);
    }

    public function getBackAssociations()
    {
        return $this->backAssociations;
    }
    public function addBackAssociation($item)
    {
        if( !$this->backAssociations->contains($item) ) {
            $this->backAssociations->add($item);
        }
    }
    public function removeBackAssociation($item)
    {
        $this->backAssociations->removeElement($item);
    }


//    /**
//     * @param mixed $scanner
//     */
//    public function setScanner($scanner)
//    {
//        $this->scanner = $scanner;
//    }
//
//    /**
//     * @return mixed
//     */
//    public function getScanner()
//    {
//        return $this->scanner;
//    }

    public function getFullName(){
        $fullName = "";

        if( $this->getMessageCategory() ) {
            $fullName = $fullName . $this->getMessageCategory()->getName();
        }

        $id = "";
        if( $this->getOid() ) {
            $id = $this->getOid();
        } else {
            if( $this->getId() ) {
                $id = $this->getId();
            }
        }

        $idStr = "";
        if( $id ) {
            $idStr = " ID:" . $id;
        }

        $fullName = $fullName . $idStr;

        //echo "fullName=".$fullName."<br>";

        return $fullName;
    }

    public function getMessageIdStr(){
        if( $this->getId() == $this->getOid() ) {
            return $this->getOid();
        } else {
            return $this->getOid()." (DB ID ".$this->getId().")";
        }
    }
    public function getMessageOidVersion(){
        $idStr = $this->getOid().".".$this->getVersion();
        //$idStr = $idStr . "[DB ID#".$this->getId()."]"; //testing
        return $idStr;
    }

    public function getUrl($generator,$urlname,$oid) {
        $url = null;
        if( $generator && $oid && $urlname ) {
            $url = $generator->generate($urlname, array('id' => $oid), UrlGeneratorInterface::ABSOLUTE_URL);
        }
        return $url;
    }

    public function __toString(){

//        $patient_info = "(";
//        $count = 0;
//        foreach( $this->patient as $patient ) {
//            //$patient_info .= 'id='.$patient->getId().", mrn=".$patient->getMrn(). "; ";
//            $patient_info .= $count.":" . $patient. "; ";
//            $count++;
//        }
//        $patient_info .= ")";

        $laborder = "no";
        if( $this->getLaborder() ) {
            $laborder = $this->getLaborder()->getId();
        }

        $report = "no";
        if( $this->getReport() ) {
            $report = $this->getReport()->getId();
        }

//        return "Message: id=".$this->id.", ".$this->educational.", ".$this->research.", patientCount=".count($this->patient).":".$patient_info.", slideCount=".count($this->slide)."<br>";
        return "Message: id=".$this->getId().", oid=".$this->oid.", status=".$this->getStatus().", category=".$this->getMessageCategory().
        ", institution=".$this->getInstitution().
        ", provider=".$this->getProvider().", providerName=".$this->getProvider()->getUsername().", providerId=".$this->getProvider()->getId().
        ", edu=".$this->educational.
        ", res=".$this->research.", patientCount=".count($this->patient).
        ", slideCount=".count($this->slide).
        ", laborder=".$laborder.
        ", report=".$report.
        "<br>";
    }


    public function addInputObject($object) {
        $input = new GeneralEntity();
        $input->setObject($object);
        if( !$this->getInputs()->contains($input) ) {
            $this->addInput($input);
        }
    }

    public function addOutputObject($object) {
        $output = new GeneralEntity();
        $output->setObject($object);
        if( !$this->getOutputs()->contains($output) ) {
            $this->addOutput($output);
        }
    }


    //TODO: testing
    public function getChildren() {
        return $this->getPatient();
    }
    public function addChildren($child) {
        $this->addPatient($child);
    }
    public function removeChildren($child) {
        $this->removePatient($child);
    }

    //FirstName LastName (MRN Type: MRN)
    public function getPatientNameMrnInfo() {
        $infoArr = array();
        foreach( $this->getPatient() as $patient ) {
            //echo "patient=".$patient->getId()."<br>";
            //$infoArr[] = $patient->obtainPatientInfoTitle();
            $infoArr[] = $patient->obtainPatientInfoSimple();
        }
        return implode("; ",$infoArr);
    }

    //[submitted timestamp in MM/DD/YYYY HH:MM 24HR format] by SubmitterFirstName SubmitterLastName, MD
    //NOT USED
    public function getSubmitterInfoSimpleDate() {
        $info = "Undefined Date";
        $infoDate = $this->getOrderSimpleDate();
        if( $infoDate ) {
            $info = $infoDate->format('m/d/Y') . " at " . $infoDate->format('H:i:s');
        }
        if( $this->getProvider() ) {
            $info = $info . " by ".$this->getProvider()->getUsernameOptimal();
        }
        return $info;
    }
    //NOT USED
    public function getOrderSimpleDate() {

        //Also, we can use OneToOne message.signeeInfo.modifiedOn date
        //if( $this->getSigneeInfo() && $this->getSigneeInfo()->getModifiedOn() ) {
        //    return $this->getSigneeInfo()->getModifiedOn();
        //}

        //Use this simple orderdate.
        return $this->getOrderdate();

        //Below logic is for testing when orderdate has been updated by error before this update bug fixed
        $date = null;

        //substitute message orderdate by encounter creationdate if order date more than encounter date
        $lastEncounterDate = null;
        if( count($this->getEncounter()) > 0 ) {
            $lastEncounterDate = $this->getEncounter()->last()->getCreationDate();
        }
        $orderdate = $this->getOrderdate();
        //echo "lastEncounterDate=".$lastEncounterDate->format('m/d/Y h:i')."; orderdate=".$orderdate->format('m/d/Y h:i')."<br>";
        if( $lastEncounterDate && $orderdate ) {
            if( $orderdate > $lastEncounterDate ) {
                $date = $lastEncounterDate;
            } else {
                $date = $orderdate;
            }
        }

        return $date;
    }

    public function getEncounterLocationInfos() {
        $infoArr = array();
        foreach($this->getEncounter() as $encounter) {
            $infoArr[] = $encounter->obtainLocationInfo();
        }
        return implode("; ",$infoArr);
    }

    public function getFormVersionsInfo() {
        $infoArr = array();
        foreach( $this->getFormVersions() as $formVersion ) {
            $infoArr[] = $formVersion->printShort();
        }
        return implode("; ",$infoArr);
    }

    /////////////////////// Getters and Setters of Specific Orders ///////////////////////

    /**
     * @param mixed $scanorder
     */
    public function setScanorder($scanorder)
    {
        $this->scanorder = $scanorder;
    }

    /**
     * @return mixed
     */
    public function getScanorder()
    {
        return $this->scanorder;
    }


    /**
     * @param mixed $slideReturnRequest
     */
    public function setSlideReturnRequest($slideReturnRequest)
    {
        $this->slideReturnRequest = $slideReturnRequest;
    }

    /**
     * @return mixed
     */
    public function getSlideReturnRequest()
    {
        return $this->slideReturnRequest;
    }


    /**
     * @param mixed $laborder
     */
    public function setLaborder($laborder)
    {
        $this->laborder = $laborder;
    }

    /**
     * @return mixed
     */
    public function getLaborder()
    {
        return $this->laborder;
    }

    /**
     * @param mixed $report
     */
    public function setReport($report)
    {
        $this->report = $report;
    }

    /**
     * @return mixed
     */
    public function getReport()
    {
        return $this->report;
    }

    /**
     * @param mixed $blockorder
     */
    public function setBlockorder($blockorder)
    {
        $this->blockorder = $blockorder;
    }

    /**
     * @return mixed
     */
    public function getBlockorder()
    {
        return $this->blockorder;
    }

    /**
     * @param mixed $slideorder
     */
    public function setSlideorder($slideorder)
    {
        $this->slideorder = $slideorder;
    }

    /**
     * @return mixed
     */
    public function getSlideorder()
    {
        return $this->slideorder;
    }

    /**
     * @param mixed $stainorder
     */
    public function setStainorder($stainorder)
    {
        $this->stainorder = $stainorder;
    }

    /**
     * @return mixed
     */
    public function getStainorder()
    {
        return $this->stainorder;
    }

    /**
     * @param mixed $imageAnalysisOrder
     */
    public function setImageAnalysisOrder($imageAnalysisOrder)
    {
        $this->imageAnalysisOrder = $imageAnalysisOrder;
    }

    /**
     * @return mixed
     */
    public function getImageAnalysisOrder()
    {
        return $this->imageAnalysisOrder;
    }

    /**
     * @param mixed $reportBlock
     */
    public function setReportBlock($reportBlock)
    {
        $this->reportBlock = $reportBlock;
    }

    /**
     * @return mixed
     */
    public function getReportBlock()
    {
        return $this->reportBlock;
    }

    /**
     * @param mixed $procedureorder
     */
    public function setProcedureorder($procedureorder)
    {
        $this->procedureorder = $procedureorder;
    }

    /**
     * @return mixed
     */
    public function getProcedureorder()
    {
        return $this->procedureorder;
    }

    /**
     * @return mixed
     */
    public function getCalllogEntryMessage()
    {
        return $this->calllogEntryMessage;
    }

    /**
     * @param mixed $calllogEntryMessage
     */
    public function setCalllogEntryMessage($calllogEntryMessage)
    {
        $this->calllogEntryMessage = $calllogEntryMessage;
    }


    /////////////////////// EOF Getters and Setters of Specific Orders ///////////////////////


}