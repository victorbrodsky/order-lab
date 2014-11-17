<?php

namespace Oleg\UserdirectoryBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;


/**
 * @ORM\Entity
 * @ORM\Table(name="user_buildingList")
 */
class BuildingList extends ListAbstract
{

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $abbreviation;

    /**
     * @ORM\OneToOne(targetEntity="GeoLocation", mappedBy="building", cascade={"persist"})
     **/
    protected $geoLocation;


    /**
     * @ORM\OneToMany(targetEntity="BuildingList", mappedBy="original", cascade={"persist"})
     **/
    protected $synonyms;

    /**
     * @ORM\ManyToOne(targetEntity="BuildingList", inversedBy="synonyms", cascade={"persist"})
     * @ORM\JoinColumn(name="original_id", referencedColumnName="id", nullable=true)
     **/
    protected $original;




    /**
     * @param mixed $abbreviation
     */
    public function setAbbreviation($abbreviation)
    {
        $this->abbreviation = $abbreviation;
    }

    /**
     * @return mixed
     */
    public function getAbbreviation()
    {
        return $this->abbreviation;
    }

    /**
     * @param mixed $geographicLocation
     */
    public function setGeographicLocation($geographicLocation)
    {
        $this->geographicLocation = $geographicLocation;
    }

    /**
     * @return mixed
     */
    public function getGeographicLocation()
    {
        return $this->geographicLocation;
    }





    public function __toString() {

        if( $this->getAbbreviation() ) {
            return $this->getAbbreviation() . " - " . $this->getName();
        } else {
            return $this->getName();
        }
    }


}