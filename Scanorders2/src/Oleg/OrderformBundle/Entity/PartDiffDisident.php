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

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="scan_partDiffDisident",
 *  indexes={
 *      @ORM\Index( name="partdiffdisident_field_idx", columns={"field"} )
 *  }
 * )
 */
class PartDiffDisident extends PartArrayFieldAbstract
{

    /**
     * @ORM\ManyToOne(targetEntity="Part", inversedBy="diffDisident")
     * @ORM\JoinColumn(name="part_id", referencedColumnName="id", nullable=true, onDelete="CASCADE")
     */
    protected $part;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $field;
}