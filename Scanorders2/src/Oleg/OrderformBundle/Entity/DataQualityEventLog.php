<?php
/**
 * Copyright (c) 2017 Cornell University
 *
 *  Licensed under the Apache License, Version 2.0 (the "License");
 *  you may not use this file except in compliance with the License.
 *  You may obtain a copy of the License at
 *
 *  http://www.apache.org/licenses/LICENSE-2.0
 *
 *  Unless required by applicable law or agreed to in writing, software
 *  distributed under the License is distributed on an "AS IS" BASIS,
 *  WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 *  See the License for the specific language governing permissions and
 *  limitations under the License.
 */

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