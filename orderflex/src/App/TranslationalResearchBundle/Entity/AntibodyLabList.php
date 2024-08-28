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

namespace App\TranslationalResearchBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

use App\UserdirectoryBundle\Entity\ListAbstract;

#[ORM\Table(name: 'transres_antibodylablist')]
#[ORM\Entity]
class AntibodyLabList extends ListAbstract
{

    #[ORM\OneToMany(targetEntity: 'AntibodyLabList', mappedBy: 'original', cascade: ['persist'])]
    protected $synonyms;

    #[ORM\ManyToOne(targetEntity: 'AntibodyLabList', inversedBy: 'synonyms', cascade: ['persist'])]
    #[ORM\JoinColumn(name: 'original_id', referencedColumnName: 'id', nullable: true)]
    protected $original;

    #[ORM\ManyToMany(targetEntity: AntibodyList::class, mappedBy: 'antibodyLabs')]
    private $antibodies;


    
    public function __construct($author=null) {
        parent::__construct($author);
        $this->antibodies = new ArrayCollection();
    }

    /**
     * @return mixed
     */
    public function getAntibodies()
    {
        return $this->antibodies;
    }
    public function addAntibody( $item )
    {
        if( !$this->antibodies->contains($item) ) {
            $this->antibodies->add($item);
            $item->addAntibodyLab($this);
        }

        return $this;
    }
    public function removeAntibody($item)
    {
        if( $this->antibodies->contains($item) ) {
            $this->antibodies->removeElement($item);
            $item->removeAntibodyLab($this);
        }

        return $this;
    }
    
}
