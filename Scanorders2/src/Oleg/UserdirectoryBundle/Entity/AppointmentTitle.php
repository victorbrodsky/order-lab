<?php

namespace Oleg\UserdirectoryBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="user_appointmentTitle")
 */
class AppointmentTitle extends BaseTitle
{

    /**
     * @ORM\ManyToOne(targetEntity="AppTitleList")
     * @ORM\JoinColumn(name="name", referencedColumnName="id", nullable=true)
     **/
    protected $name;

    /**
     * @ORM\ManyToOne(targetEntity="User", inversedBy="appointmentTitles")
     * @ORM\JoinColumn(name="fosuser", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $user;

//    /**
//     * @ORM\Column(name="position", type="string", nullable=true)
//     */
//    protected $position;
    /**
     * @ORM\ManyToMany(targetEntity="PositionTrackTypeList", inversedBy="appointmentTitles")
     * @ORM\JoinTable(name="user_appointmenttitle_position")
     **/
    private $positions;

    /**
     * @ORM\ManyToOne(targetEntity="ResidencyTrackList")
     * @ORM\JoinColumn(name="residencyTrack_id", referencedColumnName="id")
     **/
    private $residencyTrack;

    /**
     * @ORM\ManyToOne(targetEntity="FellowshipTypeList")
     * @ORM\JoinColumn(name="fellowshipType_id", referencedColumnName="id")
     **/
    private $fellowshipType;


    public function __construct($creator=null) {
        $this->positions = new ArrayCollection();
    }


    /**
     * @param mixed $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
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
     * @param mixed $fellowshipType
     */
    public function setFellowshipType($fellowshipType)
    {
        $this->fellowshipType = $fellowshipType;
    }

    /**
     * @return mixed
     */
    public function getFellowshipType()
    {
        return $this->fellowshipType;
    }

    /**
     * @param mixed $residencyTrack
     */
    public function setResidencyTrack($residencyTrack)
    {
        $this->residencyTrack = $residencyTrack;
    }

    /**
     * @return mixed
     */
    public function getResidencyTrack()
    {
        return $this->residencyTrack;
    }



    public function addPosition($item)
    {
        if( $item && !$this->positions->contains($item) ) {
            $this->positions->add($item);
        }
        return $this;
    }
    public function removePosition($item)
    {
        $this->positions->removeElement($item);
    }
    public function getPositions()
    {
        return $this->positions;
    }



    public function __toString() {
        return "Academic Appointment Title";
    }
}