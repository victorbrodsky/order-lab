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

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="scan_encounterPatfirstname")
 */
class EncounterPatfirstname extends EncounterArrayFieldAbstract
{

    /**
     * @ORM\ManyToOne(targetEntity="Encounter", inversedBy="patfirstname", cascade={"persist"})
     * @ORM\JoinColumn(name="encounter_id", referencedColumnName="id", onDelete="CASCADE", nullable=true)
     */
    protected $encounter;

    /**
     * Last Name
     * @ORM\Column(type="string", nullable=true)
     */
    protected $field;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $alias;





    /**
     * @param mixed $alias
     */
    public function setAlias($alias)
    {
        $this->alias = $alias;
    }

    /**
     * @return mixed
     */
    public function getAlias()
    {
        return $this->alias;
    }


    /**
     * convert the string to "Sentence Case"
     * @return mixed
     */
    public function getField()
    {
        return $this->capitalizeIfNotAllCapital($this->field);
    }



}