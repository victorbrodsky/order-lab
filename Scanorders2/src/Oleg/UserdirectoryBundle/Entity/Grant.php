<?php

namespace Oleg\UserdirectoryBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="Oleg\UserdirectoryBundle\Repository\GrantRepository")
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


    //Relevant Documents: [use the Dropzone upload box, allow 20 documents]
    /**
     * Attachment can have many DocumentContainers; each DocumentContainers can have many Documents; each DocumentContainers has document type (DocumentTypeList)
     * @ORM\OneToOne(targetEntity="AttachmentContainer", cascade={"persist","remove"})
     **/
    private $attachmentContainer;

    //Link to a page with more information:
    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $grantLink;

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
     * @ORM\Column(type="string", nullable=true)
     */
    private $amount;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $currentYearDirectCost;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $currentYearIndirectCost;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $totalCurrentYearCost;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $amountLabSpace;

    //User's fields
    /**
     * @ORM\OneToMany(targetEntity="GrantComment", mappedBy="grant", cascade={"persist","remove"})
     **/
    private $comments;
    private $commentDummy;

    /**
     * @ORM\OneToMany(targetEntity="GrantEffort", mappedBy="grant", cascade={"persist","remove"})
     **/
    private $efforts;
    private $effortDummy;



    public function __construct($creator=null) {

        parent::__construct();

        $this->user = new ArrayCollection();
        $this->comments = new ArrayCollection();
        $this->efforts = new ArrayCollection();

        //set mandatory list attributes
        $this->setName("");
        $this->setType('user-added');
        $this->setCreatedate(new \DateTime());
        $this->setOrderinlist(-1);
        if( $creator ) {
            $this->setCreator($creator);
        }

        //add one document
        $attachmentContainer = $this->getAttachmentContainer();
        if( !$attachmentContainer ) {
            $attachmentContainer = new AttachmentContainer();
            $this->setAttachmentContainer($attachmentContainer);
            if( count($attachmentContainer->getDocumentContainers()) == 0 ) {
                $attachmentContainer->addDocumentContainer( new DocumentContainer() );
            }
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

    public function addComment($item)
    {
        if( $item && !$this->comments->contains($item) ) {
            $this->comments->add($item);
            $item->setGrant($this);
        }

        return $this;
    }
    public function removeComment($item)
    {
        $this->comments->removeElement($item);
    }
    public function getComments()
    {
        return $this->comments;
    }

    public function addEffort($item)
    {
        if( $item && !$this->efforts->contains($item) ) {
            $this->efforts->add($item);
            $item->setGrant($this);
        }

        return $this;
    }
    public function removeEffort($item)
    {
        $this->efforts->removeElement($item);
    }
    public function getEfforts()
    {
        return $this->efforts;
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
     * @param mixed $amountLabSpace
     */
    public function setAmountLabSpace($amountLabSpace)
    {
        $this->amountLabSpace = $amountLabSpace;
    }

    /**
     * @return mixed
     */
    public function getAmountLabSpace()
    {
        return $this->amountLabSpace;
    }

    /**
     * @param mixed $currentYearDirectCost
     */
    public function setCurrentYearDirectCost($currentYearDirectCost)
    {
        $this->currentYearDirectCost = $currentYearDirectCost;
    }

    /**
     * @return mixed
     */
    public function getCurrentYearDirectCost()
    {
        return $this->currentYearDirectCost;
    }

    /**
     * @param mixed $currentYearIndirectCost
     */
    public function setCurrentYearIndirectCost($currentYearIndirectCost)
    {
        $this->currentYearIndirectCost = $currentYearIndirectCost;
    }

    /**
     * @return mixed
     */
    public function getCurrentYearIndirectCost()
    {
        return $this->currentYearIndirectCost;
    }

    /**
     * @param mixed $totalCurrentYearCost
     */
    public function setTotalCurrentYearCost($totalCurrentYearCost)
    {
        $this->totalCurrentYearCost = $totalCurrentYearCost;
    }

    /**
     * @return mixed
     */
    public function getTotalCurrentYearCost()
    {
        return $this->totalCurrentYearCost;
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
     * @param mixed $commentDummy
     */
    public function setCommentDummy($commentDummy)
    {
        $this->commentDummy = $commentDummy;
    }

    /**
     * @return mixed
     */
    public function getCommentDummy()
    {
        return $this->commentDummy;
    }

    /**
     * @param mixed $effortDummy
     */
    public function setEffortDummy($effortDummy)
    {
        $this->effortDummy = $effortDummy;
    }

    /**
     * @return mixed
     */
    public function getEffortDummy()
    {
        return $this->effortDummy;
    }







    //interface function
    public function getAuthor()
    {
        return $this->getCreator();
    }
    public function setAuthor($author)
    {
        return $this->setCreator($author);
    }
    public function getUpdateAuthor()
    {
        return $this->getUpdatedby();
    }
    public function setUpdateAuthor($author)
    {
        return $this->setUpdatedby($author);
    }





    //Util functions
    public function setComment($text,$user)
    {
        if( $text && $text != "" ) {
            $grantComment = new GrantComment();
            $grantComment->setComment($text);
            $grantComment->setAuthor($user);
            $this->addComment($grantComment);
        }
    }

    public function setEffort($effort,$user)
    {
        if( $effort ) {
            $grantEffort = new GrantEffort();
            $grantEffort->setEffort($effort);
            $grantEffort->setAuthor($user);
            $this->addEffort($grantEffort);
        }
    }


    public function __toString() {
        return $this->getName()."";
    }

}