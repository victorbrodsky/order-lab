<?php

namespace Oleg\UserdirectoryBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity
 * @ORM\Table(name="user_location")
 */
class Location extends BaseLocation
{

    /**
     * @ORM\OneToMany(targetEntity="Location", mappedBy="original")
     **/
    protected $synonyms;

    /**
     * @ORM\ManyToOne(targetEntity="Location", inversedBy="synonyms")
     * @ORM\JoinColumn(name="original_id", referencedColumnName="id")
     **/
    protected $original;

    /**
     * @ORM\ManyToOne(targetEntity="BuildingList", cascade={"persist"})
     * @ORM\JoinColumn(name="building", referencedColumnName="id")
     */
    private $building;

    /**
     * Associated NYPH Code
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $associatedCode;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $associatedClia;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $associatedCliaExpDate;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $associatedPfi;

    /**
     * @ORM\ManyToMany(targetEntity="User")
     * @ORM\JoinTable(name="user_location_assistant",
     *      joinColumns={@ORM\JoinColumn(name="location_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="assistant_id", referencedColumnName="id")}
     * )
     **/
    private $assistant;

    /**
     * @ORM\ManyToOne(targetEntity="User", inversedBy="locations")
     * @ORM\JoinColumn(name="fosuser", referencedColumnName="id", nullable=true, onDelete="CASCADE")
     */
    private $user;

    /**
     * @ORM\ManyToOne(targetEntity="Institution",cascade={"persist"})
     */
    private $institution;

    /**
     * @ORM\ManyToOne(targetEntity="Department",cascade={"persist"})
     */
    private $department;

    /**
     * @ORM\ManyToOne(targetEntity="Division",cascade={"persist"})
     */
    private $division;

    /**
     * @ORM\ManyToOne(targetEntity="Service",cascade={"persist"})
     */
    private $service;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $ic;

    /**
     * @ORM\ManyToOne(targetEntity="LocationPrivacyList", inversedBy="locations")
     * @ORM\JoinColumn(name="privacy_id", referencedColumnName="id")
     **/
    private $privacy;


    public function __construct($creator=null) {

        parent::__construct($creator);

        $this->synonyms = new ArrayCollection();
        $this->assistant = new ArrayCollection();
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
     * @param mixed $department
     */
    public function setDepartment($department)
    {
        $this->department = $department;
    }

    /**
     * @return mixed
     */
    public function getDepartment()
    {
        return $this->department;
    }

    /**
     * @param mixed $division
     */
    public function setDivision($division)
    {
        $this->division = $division;
    }

    /**
     * @return mixed
     */
    public function getDivision()
    {
        return $this->division;
    }

    /**
     * @param mixed $service
     */
    public function setService($service)
    {
        $this->service = $service;
    }

    /**
     * @return mixed
     */
    public function getService()
    {
        return $this->service;
    }

    /**
     * Add assistant
     *
     * @param \Oleg\OrderformBundle\Entity\User $assistant
     * @return User
     */
    public function addAssistant($assistant)
    {
        if( !$this->assistant->contains($assistant) ) {
            $this->assistant->add($assistant);
        }

        return $this;
    }
    /**
     * Remove assistant
     *
     * @param \Oleg\OrderformBundle\Entity\User $assistant
     */
    public function removeAssistant($assistant)
    {
        $this->assistant->removeElement($assistant);
    }

    /**
     * Get assistant
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getAssistant()
    {
        return $this->assistant;
    }

    /**
     * @param mixed $associatedCode
     */
    public function setAssociatedCode($associatedCode)
    {
        $this->associatedCode = $associatedCode;
    }

    /**
     * @return mixed
     */
    public function getAssociatedCode()
    {
        return $this->associatedCode;
    }

    /**
     * @param mixed $associatedClia
     */
    public function setAssociatedClia($associatedClia)
    {
        $this->associatedClia = $associatedClia;
    }

    /**
     * @return mixed
     */
    public function getAssociatedClia()
    {
        return $this->associatedClia;
    }

    /**
     * @param mixed $associatedCliaExpDate
     */
    public function setAssociatedCliaExpDate($associatedCliaExpDate)
    {
        $this->associatedCliaExpDate = $associatedCliaExpDate;
    }

    /**
     * @return mixed
     */
    public function getAssociatedCliaExpDate()
    {
        return $this->associatedCliaExpDate;
    }

    /**
     * @param mixed $associatedPfi
     */
    public function setAssociatedPfi($associatedPfi)
    {
        $this->associatedPfi = $associatedPfi;
    }

    /**
     * @return mixed
     */
    public function getAssociatedPfi()
    {
        return $this->associatedPfi;
    }

    /**
     * @param mixed $building
     */
    public function setBuilding($building)
    {
        $this->building = $building;
    }

    /**
     * @return mixed
     */
    public function getBuilding()
    {
        return $this->building;
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
     * @param mixed $ic
     */
    public function setIc($ic)
    {
        $this->ic = $ic;
    }

    /**
     * @return mixed
     */
    public function getIc()
    {
        return $this->ic;
    }

    /**
     * @param mixed $privacy
     */
    public function setPrivacy($privacy)
    {
        $this->privacy = $privacy;
    }

    /**
     * @return mixed
     */
    public function getPrivacy()
    {
        return $this->privacy;
    }


    public function __toString() {

        return $this->getNameFull();
    }

    public function getNameFull() {

        $name = "";

        if( $this->user ) {

            $name = $name . $this->user->getUsernameOptimal() . "'s ";

        }

        $name = $name . $this->name;

        $detailsArr = array();

        if( $this->getRoom() ) {
            $detailsArr[] = $this->getRoom();
        }

        if( $this->getSuit() ) {
            $detailsArr[] = $this->getSuit();
        }

        if( $this->getBuilding() ) {
            $detailsArr[] = $this->getBuilding()."";
        }

        if( $this->getInstitution() && $this->getBuilding() == null ) {
            $detailsArr[] = $this->getInstitution()->getName()."";
        }

        if( $this->getMailbox() ) {
            $detailsArr[] = $this->getMailbox();
        }

        //print_r($detailsArr);
        //exit();

        if( count($detailsArr) > 0 ) {
            $name = $name . " (" . implode(", ",$detailsArr) . ")";
        }

        return $name;
    }


}