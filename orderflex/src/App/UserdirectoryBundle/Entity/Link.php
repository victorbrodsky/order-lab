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

namespace App\UserdirectoryBundle\Entity;


use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;


#[ORM\Table(name: 'user_link')]
#[ORM\Entity]
class Link extends ListAbstract
{

    #[ORM\OneToMany(targetEntity: 'Link', mappedBy: 'original', cascade: ['persist'])]
    protected $synonyms;

    #[ORM\ManyToOne(targetEntity: 'Link', inversedBy: 'synonyms', cascade: ['persist'])]
    #[ORM\JoinColumn(name: 'original_id', referencedColumnName: 'id', nullable: true)]
    protected $original;



    #[ORM\ManyToOne(targetEntity: 'LinkTypeList')]
    #[ORM\JoinColumn(referencedColumnName: 'id', nullable: true)]
    private $linktype;

    #[ORM\Column(type: 'string', nullable: true)]
    private $link;







    /**
     * @param mixed $link
     */
    public function setLink($link)
    {
        $this->link = $link;
    }

    /**
     * @return mixed
     */
    public function getLink()
    {
        return $this->link;
    }

    /**
     * @param mixed $linktype
     */
    public function setLinktype($linktype)
    {
        $this->linktype = $linktype;
    }

    /**
     * @return mixed
     */
    public function getLinktype()
    {
        return $this->linktype;
    }




}