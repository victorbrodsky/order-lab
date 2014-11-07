<?php

namespace Oleg\OrderformBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="scan_dataquality_eventlog")
 */
class DataQualityEventLog
{


    /**
     * @var integer
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\OneToMany(targetEntity="DataQualityEvent", mappedBy="dqeventlog")
     **/
    private $dqevents;

    public function __construct() {
        $this->dqevents = new ArrayCollection();
    }



    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }


    /**
     * Add dqevent
     *
     * @param DataQualityEvent $dqevent
     * @return DataQualityEvent
     */
    public function addDqevent(DataQualityEvent $dqevent)
    {
        if( !$this->dqevents->contains($dqevent) ) {
            $this->dqevents->add($dqevent);
        }

        return $this;
    }

    /**
     * Remove dqevent
     *
     * @param DataQualityEvent $dqevent
     */
    public function removeDqevent(DataQualityEvent $dqevent)
    {
        $this->dqevents->removeElement($dqevent);
    }

    /**
     * Get dqevents
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getDqevents()
    {
        return $this->dqevents;
    }

}