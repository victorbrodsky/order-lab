<?php

namespace Oleg\UserdirectoryBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity
 * @ORM\Table(name="user_institutiontype")
 */
class InstitutionType extends ListAbstract
{

    /**
     * @ORM\OneToMany(targetEntity="InstitutionType", mappedBy="original")
     **/
    protected $synonyms;

    /**
     * @ORM\ManyToOne(targetEntity="InstitutionType", inversedBy="synonyms")
     * @ORM\JoinColumn(name="original_id", referencedColumnName="id")
     **/
    protected $original;


    /**
     * @ORM\ManyToMany(targetEntity="Institution", mappedBy="types")
     **/
    private $institutions;



    public function __construct() {
        $this->synonyms = new ArrayCollection();
        $this->institutions = new ArrayCollection();
    }



    public function addInstitution($institution)
    {
        if( $institution && !$this->institutions->contains($institution) ) {
            $this->institutions->add($institution);
        }

        return $this;
    }
    public function removeInstitution($institution)
    {
        $this->institutions->removeElement($institution);
    }
    public function getInstitutions()
    {
        return $this->institutions;
    }

}