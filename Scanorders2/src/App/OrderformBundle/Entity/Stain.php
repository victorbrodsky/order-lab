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

//(repositoryClass="App\OrderformBundle\Repository\StainRepository")
/**
 * @ORM\Entity
 * @ORM\Table(name="scan_stain")
 */
class Stain extends SlideArrayFieldAbstract
{

    /**
     * @ORM\ManyToOne(targetEntity="Slide", inversedBy="stain")
     * @ORM\JoinColumn(name="slide", referencedColumnName="id")
     */
    protected $slide;

    /**
     * @ORM\ManyToOne(targetEntity="StainList", cascade={"persist"})
     * @ORM\JoinColumn(name="stainlist_id", referencedColumnName="id", nullable=true)
     */
    protected $field;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $stainer;
    
    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $date;



    /**
     * Set stainer
     *
     * @param string $stainer
     * @return Stain
     */
    public function setStainer($stainer)
    {
        $this->stainer = $stainer;
    
        return $this;
    }

    /**
     * Get stainer
     *
     * @return string 
     */
    public function getStainer()
    {
        return $this->stainer;
    }

    /**
     * Set date
     *
     * @param \DateTime $date
     * @return Stain
     */
    public function setDate($date)
    {
        $this->date = $date;
    
        return $this;
    }

    /**
     * Get date
     *
     * @return \DateTime 
     */
    public function getDate()
    {
        return $this->date;
    }

    public function setProvider($provider)
    {
        if( $provider ) {
            $this->provider = $provider;
        } else {
            $this->provider = $this->getSlide()->getProvider();
        }

        return $this;
    }

}