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
     * @ORM\OneToOne(targetEntity="GeoLocation", mappedBy="building",cascade={"persist"})
     **/
    protected $geoLocation;

    /**
     * @ORM\ManyToOne(targetEntity="Institution")
     */
    private $institution;


    /**
     * @ORM\OneToMany(targetEntity="BuildingList", mappedBy="original", cascade={"persist"})
     **/
    protected $synonyms;

    /**
     * @ORM\ManyToOne(targetEntity="BuildingList", inversedBy="synonyms", cascade={"persist"})
     * @ORM\JoinColumn(name="original_id", referencedColumnName="id", nullable=true)
     **/
    protected $original;



    public function __construct($creator=null) {
        $this->synonyms = new ArrayCollection();

        //set mandatory list attributes
        $this->setName("");
        $this->setType('user-added');
        $this->setCreatedate(new \DateTime());
        $this->setOrderinlist(-1);

        if( $creator ) {
            $this->setCreator($creator);
        }
    }


    /**
     * Set name
     *
     * @param string $name
     * @return List
     */
    public function setName($name)
    {
        if( $name == null ) {
            $name = "";
        }

        $this->name = $name;

        return $this;
    }


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
     * @param mixed $geoLocation
     */
    public function setGeoLocation($geoLocation)
    {
        $this->geoLocation = $geoLocation;
        $geoLocation->setBuilding($this);
    }

    /**
     * @return mixed
     */
    public function getGeoLocation()
    {
        return $this->geoLocation;
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


    //WCMC - Weill Cornell Medical College / 1300 York Ave / Abbreviation = C
    public function __toString() {

        $instName = "";
        if( $this->getInstitution() ) {
            if( $this->getInstitution()->getAbbreviation() ) {
                $instName = $this->getInstitution()->getAbbreviation()."";
            } else {
                $instName = $this->getInstitution()->getName()."";
            }
        }

        $geoName = "";
        if( $this->getGeoLocation() != "" ) {
            $geoName = $this->getGeoLocation()."";
        }

        $name = "";
        if( $instName != "" ) {
            $name = $instName . " - ";
        }

        if( $this->getName() != "" ) {
            $name = $name . $this->getName() . " / ";
        }

        if( $geoName != "" ) {
            $name = $name . $geoName;
        }

        if( $this->getAbbreviation() && $this->getAbbreviation() != "" ) {
            $name = $name . " / Abbreviation = " . $this->getAbbreviation()."";
        }

        return $name;
    }


}