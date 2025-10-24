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

namespace App\FellAppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

use App\UserdirectoryBundle\Entity\ListAbstract;

#[ORM\Table(name: 'fellapp_fellimportkey')]
#[ORM\Entity]
class FellAppImportKey extends ListAbstract
{

    #[ORM\OneToMany(targetEntity: 'FellAppImportKey', mappedBy: 'original', cascade: ['persist'])]
    protected $synonyms;

    #[ORM\ManyToOne(targetEntity: 'FellAppImportKey', inversedBy: 'synonyms', cascade: ['persist'])]
    #[ORM\JoinColumn(name: 'original_id', referencedColumnName: 'id', nullable: true)]
    protected $original;

    #[ORM\ManyToOne(targetEntity: 'GlobalFellowshipSpecialty', inversedBy: 'apiImportKeys', cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(name: 'globalspecialty_id', referencedColumnName: 'id', onDelete: 'CASCADE', nullable: true)]
    private $globalspecialty;

    #[ORM\Column(type: 'string', length: 255)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    private string $value;


    /**
     * @param mixed $value
     */
    public function setValue($value)
    {
        $this->value = $value;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @return mixed
     */
    public function getGlobalspecialty()
    {
        return $this->globalspecialty;
    }

    /**
     * @param mixed $globalspecialty
     */
    public function setGlobalspecialty($globalspecialty)
    {
        $this->globalspecialty = $globalspecialty;
    }




    public function __toString() {
        return "name=".$this->name . "; value=" . $this->value;
    }







}