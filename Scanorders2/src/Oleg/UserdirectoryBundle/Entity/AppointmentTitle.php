<?php

namespace Oleg\UserdirectoryBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="user_appointmentTitle")
 */
class AppointmentTitle extends BaseTitle
{

    /**
     * @ORM\ManyToOne(targetEntity="User", inversedBy="appointmentTitles")
     * @ORM\JoinColumn(name="fosuser", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $user;

    /**
     * @ORM\Column(name="position", type="string", nullable=true)
     */
    protected $position;

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
     * @param mixed $position
     */
    public function setPosition($position)
    {
        $this->position = $position;
    }

    /**
     * @return mixed
     */
    public function getPosition()
    {
        return $this->position;
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



    public function __toString() {
        return "Academic Appointment Title";
    }
}