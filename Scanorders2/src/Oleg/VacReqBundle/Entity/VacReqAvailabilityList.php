<?php

namespace Oleg\VacReqBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Oleg\UserdirectoryBundle\Entity\ListAbstract;

/**
 * @ORM\Entity
 * @ORM\Table(name="vacreq_availabilityList")
 */
class VacReqAvailabilityList extends ListAbstract
{

    /**
     * @ORM\OneToMany(targetEntity="VacReqAvailabilityList", mappedBy="original")
     **/
    protected $synonyms;

    /**
     * @ORM\ManyToOne(targetEntity="VacReqAvailabilityList", inversedBy="synonyms")
     * @ORM\JoinColumn(name="original_id", referencedColumnName="id")
     **/
    protected $original;


    /**
     * @ORM\ManyToMany(targetEntity="VacReqRequest", mappedBy="availabilities")
     **/
    private $requests;




    public function __construct() {

        parent::__construct();

        $this->requests = new ArrayCollection();

    }





    public function getRequests()
    {
        return $this->requests;
    }
    public function addRequest($item)
    {
        if( !$this->requests->contains($item) ) {
            $this->requests->add($item);
        }
        return $this;
    }
    public function removeRequest($item)
    {
        $this->requests->removeElement($item);
    }



}