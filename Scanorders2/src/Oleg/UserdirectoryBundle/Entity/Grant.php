<?php

namespace Oleg\UserdirectoryBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 * @ORM\Table(name="user_grant")
 */
class Grant extends ListAbstract
{

    /**
     * @ORM\OneToMany(targetEntity="Grant", mappedBy="original", cascade={"persist"})
     **/
    protected $synonyms;

    /**
     * @ORM\ManyToOne(targetEntity="Grant", inversedBy="synonyms", cascade={"persist"})
     * @ORM\JoinColumn(name="original_id", referencedColumnName="id", nullable=true)
     **/
    protected $original;



    /**
     * @ORM\ManyToMany(targetEntity="User", inversedBy="grants")
     * @ORM\JoinTable(name="user_grant_user",
     *      joinColumns={@ORM\JoinColumn(name="grant_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="user_id", referencedColumnName="id")}
     * )
     **/
    private $user;



    /**
     * @ORM\ManyToOne(targetEntity="SourceOrganization")
     * @ORM\JoinColumn(name="sourceOrganization_id", referencedColumnName="id", nullable=true)
     */
    private $sourceOrganization;

    /**
     * @ORM\ManyToOne(targetEntity="GrantTitle")
     * @ORM\JoinColumn(name="grantTitle_id", referencedColumnName="id", nullable=true)
     */
    private $grantTitle;

    //Relevant Documents: [use the Dropzone upload box, allow 20 documents]
    /**
     * Attachment can have many DocumentContainers; each DocumentContainers can have many Documents; each DocumentContainers has document type (DocumentTypeList)
     * ORM\OneToOne(targetEntity="Oleg\UserdirectoryBundle\Entity\AttachmentContainer", cascade={"persist","remove"})
     **/
    private $attachmentContainer;

    //Link to a page with more information:
    /**
     * @ORM\ManyToOne(targetEntity="GrantLink")
     * @ORM\JoinColumn(name="grantLink_id", referencedColumnName="id", nullable=true)
     */
    private $grantLink;

    /**
     * @ORM\ManyToOne(targetEntity="EffortList",cascade={"persist"})
     **/
    protected $effort;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $grantid;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $startDate;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $endDate;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $comment;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $amount;




    public function __construct($creator=null) {

        parent::__construct();

        $this->user = new ArrayCollection();
        $this->synonyms = new ArrayCollection();

        if( $creator ) {
            $this->setCreator($creator);
        }

    }




    public function addUser($user)
    {
        if( !$this->user->contains($user) ) {
            $this->user->add($user);
            $user->addGrant($this);
        }

        return $this;
    }
    public function removeUser($user)
    {
        $this->user->removeElement($user);
    }
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param mixed $amount
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;
    }

    /**
     * @return mixed
     */
    public function getAmount()
    {
        return $this->amount;
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
     * @param mixed $endDate
     */
    public function setEndDate($endDate)
    {
        $this->endDate = $endDate;
    }

    /**
     * @return mixed
     */
    public function getEndDate()
    {
        return $this->endDate;
    }

    /**
     * @param mixed $grantLink
     */
    public function setGrantLink($grantLink)
    {
        $this->grantLink = $grantLink;
    }

    /**
     * @return mixed
     */
    public function getGrantLink()
    {
        return $this->grantLink;
    }

    /**
     * @param mixed $grantTitle
     */
    public function setGrantTitle($grantTitle)
    {
        $this->grantTitle = $grantTitle;
    }

    /**
     * @return mixed
     */
    public function getGrantTitle()
    {
        return $this->grantTitle;
    }

    /**
     * @param mixed $grantid
     */
    public function setGrantid($grantid)
    {
        $this->grantid = $grantid;
    }

    /**
     * @return mixed
     */
    public function getGrantid()
    {
        return $this->grantid;
    }

    /**
     * @param mixed $sourceOrganization
     */
    public function setSourceOrganization($sourceOrganization)
    {
        $this->sourceOrganization = $sourceOrganization;
    }

    /**
     * @return mixed
     */
    public function getSourceOrganization()
    {
        return $this->sourceOrganization;
    }

    /**
     * @param mixed $startDate
     */
    public function setStartDate($startDate)
    {
        $this->startDate = $startDate;
    }

    /**
     * @return mixed
     */
    public function getStartDate()
    {
        return $this->startDate;
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
    public function getEffort()
    {
        return $this->effort;
    }




}