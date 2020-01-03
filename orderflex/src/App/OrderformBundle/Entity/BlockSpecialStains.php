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

//use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity
 * @ORM\Table(name="scan_blockSpecialStains")
 */
class BlockSpecialStains extends BlockArrayFieldAbstract
{

//    /**
//     * @ORM\ManyToOne(targetEntity="Slide", inversedBy="specialStains")
//     * @ORM\JoinColumn(name="slide_id", referencedColumnName="id", nullable=true)
//     */
//    protected $slide;

    /**
     * @ORM\ManyToOne(targetEntity="Block", inversedBy="specialStains")
     * @ORM\JoinColumn(name="block_id", referencedColumnName="id", nullable=true, onDelete="CASCADE")
     */
    protected $block;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    protected $field;

    /**
     * @ORM\ManyToOne(targetEntity="StainList", cascade={"persist"})
     * @ORM\JoinColumn(name="stainlist_id", referencedColumnName="id", nullable=true)
     */
    protected $staintype;




    /**
     * @param mixed $staintype
     */
    public function setStaintype($staintype)
    {
        $this->staintype = $staintype;
    }

    /**
     * @return mixed
     */
    public function getStaintype()
    {
        return $this->staintype;
    }



}