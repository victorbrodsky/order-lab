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
 * @ORM\Table(name="scan_messagetagtypeslist")
 */
class MessageTagTypesList extends ListAbstract {

    /**
     * @ORM\OneToMany(targetEntity="MessageTagTypesList", mappedBy="original", cascade={"persist"})
     **/
    protected $synonyms;

    /**
     * @ORM\ManyToOne(targetEntity="MessageTagTypesList", inversedBy="synonyms", cascade={"persist"})
     * @ORM\JoinColumn(name="original_id", referencedColumnName="id", nullable=true)
     **/
    protected $original;

    /**
     * @ORM\ManyToMany(targetEntity="MessageTagsList", mappedBy="tagTypes", cascade={"persist"})
     **/
    private $messageTags;
    

    public function __construct() {
        parent::__construct();

        $this->messageTags = new ArrayCollection();
    }


    public function addMessageTag($item)
    {
        if( $item && !$this->messageTags->contains($item) ) {
            $this->messageTags->add($item);
        }
        return $this;
    }
    public function removeMessageTag($item)
    {
        $this->messageTags->removeElement($item);
    }
    public function getMessageTags()
    {
        return $this->messageTags;
    }

}