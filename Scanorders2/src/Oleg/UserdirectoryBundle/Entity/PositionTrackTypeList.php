<?php

namespace Oleg\UserdirectoryBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity
 * @ORM\Table(name="user_positionTrackTypeList")
 */
class PositionTrackTypeList extends ListAbstract
{

    /**
     * @ORM\OneToMany(targetEntity="PositionTrackTypeList", mappedBy="original")
     **/
    protected $synonyms;

    /**
     * @ORM\ManyToOne(targetEntity="PositionTrackTypeList", inversedBy="synonyms")
     * @ORM\JoinColumn(name="original_id", referencedColumnName="id")
     **/
    protected $original;



    /**
     * @ORM\ManyToMany(targetEntity="AppointmentTitle", mappedBy="positions")
     **/
    private $appointmentTitles;


    public function __construct($creator=null) {
        $this->appointmentTitles = new ArrayCollection();
    }



    public function addAppointmentTitle($item)
    {
        if( $item && !$this->appointmentTitles->contains($item) ) {
            $this->appointmentTitles->add($item);
        }
        return $this;
    }
    public function removeAppointmentTitle($item)
    {
        $this->appointmentTitles->removeElement($item);
    }
    public function getAppointmentTitles()
    {
        return $this->appointmentTitles;
    }

}