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

use Oleg\UserdirectoryBundle\Entity\ListAbstract;

/**
 * @ORM\Entity
 * @ORM\Table(name="scan_organlist")
 */
class OrganList extends ListAbstract
{

    /**
     * @ORM\OneToMany(targetEntity="OrganList", mappedBy="original")
     **/
    protected $synonyms;

    /**
     * @ORM\ManyToOne(targetEntity="OrganList", inversedBy="synonyms")
     * @ORM\JoinColumn(name="original_id", referencedColumnName="id")
     **/
    protected $original;

    /**
     * @ORM\OneToMany(targetEntity="PartSourceOrgan", mappedBy="field")
     */
    protected $part;

//    /**
//     * @ORM\OneToMany(targetEntity="Part", mappedBy="primaryOrgan")
//     */
//    protected $partprimary;

    /**
     * @ORM\OneToMany(targetEntity="PartDiseaseType", mappedBy="primaryOrgan")
     */
    protected $partprimary;


    public function __construct() {
        $this->synonyms = new ArrayCollection();
        $this->part = new ArrayCollection();
    }


    /**
     * Add part
     *
     * @param \Oleg\OrderformBundle\Entity\OrganList $part
     * @return OrganList
     */
    public function addPart(\Oleg\OrderformBundle\Entity\OrganList $part)
    {
        $this->part[] = $part;
    
        return $this;
    }

    /**
     * Remove part
     *
     * @param \Oleg\OrderformBundle\Entity\OrganList $part
     */
    public function removePart(\Oleg\OrderformBundle\Entity\OrganList $part)
    {
        $this->part->removeElement($part);
    }

    /**
     * Get part
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getPart()
    {
        return $this->part;
    }
}