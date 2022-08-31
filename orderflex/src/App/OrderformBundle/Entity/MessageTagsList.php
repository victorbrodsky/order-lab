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
 * @ORM\Table(name="scan_messagetagslist")
 */
class MessageTagsList extends ListAbstract {

    /**
     * @ORM\OneToMany(targetEntity="MessageTagsList", mappedBy="original", cascade={"persist"})
     **/
    protected $synonyms;

    /**
     * @ORM\ManyToOne(targetEntity="MessageTagsList", inversedBy="synonyms", cascade={"persist"})
     * @ORM\JoinColumn(name="original_id", referencedColumnName="id", nullable=true)
     **/
    protected $original;


    /**
     * @ORM\ManyToMany(targetEntity="MessageTagTypesList", inversedBy="messageTags" )
     * @ORM\JoinTable(name="scan_tag_type")
     **/
    private $tagTypes;
    

    public function __construct() {
        parent::__construct();
        $this->tagTypes = new ArrayCollection();
    }


    public function addTagType($item)
    {
        if( $item && !$this->tagTypes->contains($item) ) {
            $this->tagTypes->add($item);
        }
        return $this;
    }
    public function removeTagType($item)
    {
        $this->tagTypes->removeElement($item);
    }
    public function getTagTypes()
    {
        return $this->tagTypes;
    }
    
}