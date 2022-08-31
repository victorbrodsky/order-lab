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

namespace App\OrderformBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

use App\UserdirectoryBundle\Entity\ListAbstract;

/**
 * @ORM\Entity
 * @ORM\Table(name="scan_encounterList",
 *  indexes={
 *      @ORM\Index( name="encounter_name_idx", columns={"name"} )
 *  }
 * )
 */
class EncounterList extends ListAbstract
{
    /**
     * @ORM\OneToMany(targetEntity="EncounterList", mappedBy="original", cascade={"persist"})
     **/
    protected $synonyms;

    /**
     * @ORM\ManyToOne(targetEntity="EncounterList", inversedBy="synonyms", cascade={"persist"})
     * @ORM\JoinColumn(name="original_id", referencedColumnName="id", nullable=true)
     **/
    protected $original;

    /**
     * @ORM\OneToMany(targetEntity="EncounterName", mappedBy="field")
     */
    protected $encountername;


    public function __construct() {
        $this->synonyms = new ArrayCollection();
        $this->encountername = new ArrayCollection();
    }   

    /**
     * Add EncounterName
     *
     * @param \App\OrderformBundle\Entity\EncounterName $encountername
     * @return EncounterList
     */
    public function addEncountername(\App\OrderformBundle\Entity\EncounterName $encountername)
    {
        if( $encountername && !$this->encountername->contains($encountername) ) {
            $this->encountername->add($encountername);
            $encountername->setField($this);
        }
    
        return $this;
    }

    /**
     * Remove encountername
     *
     * @param \App\OrderformBundle\Entity\EncounterName $encountername
     */
    public function removeEncounterName(\App\OrderformBundle\Entity\EncounterName $encountername)
    {
        $this->encountername->removeElement($encountername);
    }

    /**
     * Get encountername
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getEncountername()
    {
        return $this->encountername;
    }


}