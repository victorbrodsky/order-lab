<?php

namespace Oleg\UserdirectoryBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity()
 * @ORM\Table(name="user_locationPrivacyList")
 */
class LocationPrivacyList extends ListAbstract
{


    /**
     * @ORM\OneToMany(targetEntity="LocationPrivacyList", mappedBy="original")
     **/
    protected $synonyms;

    /**
     * @ORM\ManyToOne(targetEntity="LocationPrivacyList", inversedBy="synonyms")
     * @ORM\JoinColumn(name="original_id", referencedColumnName="id")
     **/
    protected $original;

    /**
     * @ORM\OneToMany(targetEntity="Location", mappedBy="privacy")
     */
    protected $locations;



    public function __construct() {
        $this->synonyms = new ArrayCollection();
        $this->locations = new ArrayCollection();
    }


    


    public function addLocation(Location $location)
    {
        if( !$this->locations->contains($location) ) {
            $location->setPrivacy($this);
            $this->locations->add($location);
        }
    }
    public function removeLocation(Location $location)
    {
        $this->locations->removeElement($location);
    }
    public function getLocations()
    {
        return $this->locations;
    }


}