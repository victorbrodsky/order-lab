<?php

namespace Oleg\UserdirectoryBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;


/**
 * @ORM\Entity
 * @ORM\Table(name="user_locationTypeList")
 */
class LocationTypeList extends ListAbstract
{

    /**
     * @ORM\OneToMany(targetEntity="LocationTypeList", mappedBy="original", cascade={"persist"})
     **/
    protected $synonyms;

    /**
     * @ORM\ManyToOne(targetEntity="LocationTypeList", inversedBy="synonyms", cascade={"persist"})
     * @ORM\JoinColumn(name="original_id", referencedColumnName="id", nullable=true)
     **/
    protected $original;


    /**
     * @ORM\ManyToMany(targetEntity="Location", mappedBy="locationTypes")
     **/
    private $locations;


    public function __construct($creator=null) {

        $this->synonyms = new ArrayCollection();
        $this->assistant = new ArrayCollection();

        $this->locations = new ArrayCollection();
    }


    public function getLocations()
    {
        return $this->locations;
    }
    public function addLocation($location)
    {
        if( !$this->locations->contains($location) ) {
            $this->locations->add($location);
        }

        return $this;
    }
    public function removeLocation($location)
    {
        $this->locations->removeElement($location);
    }

}