<?php

namespace Oleg\UserdirectoryBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity
 * @ORM\Table(name="user_fellowshipApplication")
 */
class FellowshipApplication extends BaseUserAttributes
{

    /**
     * @ORM\ManyToOne(targetEntity="User", inversedBy="fellowshipApplications")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=true)
     */
    private $user;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $startDate;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $endDate;

    /**
     * @ORM\ManyToOne(targetEntity="FellowshipSubspecialty",cascade={"persist"})
     */
    private $fellowshipSubspecialty;

    /**
     * This should be the link to WCMC's "Department of Pathology and Laboratory Medicine"
     *
     * @ORM\ManyToOne(targetEntity="Institution",cascade={"persist"})
     */
    private $institution;

    /**
     * @ORM\ManyToMany(targetEntity="Document")
     * @ORM\JoinTable(name="user_fellApp_coverLetter",
     *      joinColumns={@ORM\JoinColumn(name="fellApp_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="coverLetter_id", referencedColumnName="id", unique=true)}
     *      )
     **/
    private $coverLetters;


    //Reprimands
    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $reprimand;

    /**
     * @ORM\ManyToMany(targetEntity="Document")
     * @ORM\JoinTable(name="user_fellApp_reprimandDocument",
     *      joinColumns={@ORM\JoinColumn(name="fellApp_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="reprimandDocument_id", referencedColumnName="id", unique=true)}
     *      )
     **/
    private $reprimandDocuments;

    //Lawsuits
    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $lawsuit;

    /**
     * @ORM\ManyToMany(targetEntity="Document")
     * @ORM\JoinTable(name="user_fellApp_lawsuitDocument",
     *      joinColumns={@ORM\JoinColumn(name="fellApp_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="lawsuitDocument_id", referencedColumnName="id", unique=true)}
     *      )
     **/
    private $lawsuitDocuments;

    /**
     * Accession Number
     * @ORM\OneToMany(targetEntity="Reference", mappedBy="fellapp", cascade={"persist"})
     */
    private $references;


    public function __construct($author=null) {
        parent::__construct($author);

        $this->coverLetters = new ArrayCollection();
        $this->reprimandDocuments = new ArrayCollection();
        $this->lawsuitDocuments = new ArrayCollection();
        $this->references = new ArrayCollection();
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
     * @param mixed $fellowshipSubspecialty
     */
    public function setFellowshipSubspecialty($fellowshipSubspecialty)
    {
        $this->fellowshipSubspecialty = $fellowshipSubspecialty;
    }

    /**
     * @return mixed
     */
    public function getFellowshipSubspecialty()
    {
        return $this->fellowshipSubspecialty;
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



    public function addCoverLetter($item)
    {
        if( $item && !$this->coverLetters->contains($item) ) {
            $this->coverLetters->add($item);
        }
        return $this;
    }
    public function removeCoverLetter($item)
    {
        $this->coverLetters->removeElement($item);
    }
    public function getCoverLetters()
    {
        return $this->coverLetters;
    }

    public function addReprimandDocument($item)
    {
        if( $item && !$this->reprimandDocuments->contains($item) ) {
            $this->reprimandDocuments->add($item);
        }
        return $this;
    }
    public function removeReprimandDocument($item)
    {
        $this->reprimandDocuments->removeElement($item);
    }
    public function getReprimandDocuments()
    {
        return $this->reprimandDocuments;
    }

    public function addLawsuitDocument($item)
    {
        if( $item && !$this->lawsuitDocuments->contains($item) ) {
            $this->lawsuitDocuments->add($item);
        }
        return $this;
    }
    public function removeLawsuitDocument($item)
    {
        $this->lawsuitDocuments->removeElement($item);
    }
    public function getLawsuitDocuments()
    {
        return $this->lawsuitDocuments;
    }

    /**
     * @param mixed $lawsuit
     */
    public function setLawsuit($lawsuit)
    {
        $this->lawsuit = $lawsuit;
    }

    /**
     * @return mixed
     */
    public function getLawsuit()
    {
        return $this->lawsuit;
    }

    /**
     * @param mixed $reprimand
     */
    public function setReprimand($reprimand)
    {
        $this->reprimand = $reprimand;
    }

    /**
     * @return mixed
     */
    public function getReprimand()
    {
        return $this->reprimand;
    }

    public function addReference($item)
    {
        if( $item && !$this->references->contains($item) ) {
            $this->references->add($item);
        }
        return $this;
    }
    public function removeReference($item)
    {
        $this->references->removeElement($item);
    }
    public function getReferences()
    {
        return $this->references;
    }



//    //create attachmentDocument holder with one Document if not exists
//    public function createCoverLetter() {
//        if( count($this->getCoverLetters()) == 0 ) {
//            $this->addCoverLetter( new Document() );
//        }
//    }



    public function __toString() {
        return "FellowshipApplication";
    }

}