<?php

namespace Oleg\UserdirectoryBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;


/**
 * @ORM\Entity
 * @ORM\Table(name="user_tracker")
 */
class Tracker {

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;


    /**
     * @ORM\OneToMany(targetEntity="Spot", mappedBy="message", cascade={"persist"})
     */
    private $spots;



    public function __construct()
    {
        $this->spots = new ArrayCollection();
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



    public function getSpots()
    {
        return $this->spots;
    }
    public function addSpot($item)
    {
        if( $item && !$this->spots->contains($item) ) {
            $this->spots->add($item);
        }
        return $this;
    }
    public function removeSpot($item)
    {
        $this->spots->removeElement($item);
    }

}