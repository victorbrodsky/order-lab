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
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity
 * @ORM\Table(name="user_languageList")
 */
class LanguageList extends ListAbstract
{

    /**
     * @ORM\OneToMany(targetEntity="LanguageList", mappedBy="original")
     **/
    protected $synonyms;

    /**
     * @ORM\ManyToOne(targetEntity="LanguageList", inversedBy="synonyms")
     * @ORM\JoinColumn(name="original_id", referencedColumnName="id")
     **/
    protected $original;

    /**
     * @ORM\ManyToMany(targetEntity="UserPreferences", mappedBy="languages")
     **/
    protected $userpreferences;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $nativeName;


    public function __construct() {
        parent::__construct();
        $this->userpreferences = new ArrayCollection();
    }


    public function addUserpreference($item)
    {
        if( $item && !$this->userpreferences->contains($item) ) {
            $this->userpreferences->add($item);
        }
        return $this;
    }
    public function removeUserpreference($item)
    {
        $this->userpreferences->removeElement($item);
    }
    public function getUserpreferences()
    {
        return $this->userpreferences;
    }

    /**
     * @param mixed $nativeName
     */
    public function setNativeName($nativeName)
    {
        $this->nativeName = $nativeName;
    }

    /**
     * @return mixed
     */
    public function getNativeName()
    {
        return $this->nativeName;
    }

    public function createFullTitle()
    {
        $fullTitle = "";

        if( $this->getName() ) {
            $fullTitle = $this->getName();
        }

        if( $this->getNativeName() ) {
            if( $fullTitle != "" ) {
                $fullTitle = $fullTitle . " - " .  $this->getNativeName();
            } else {
                $fullTitle = $this->getNativeName();
            }
        }

        $this->setFulltitle($fullTitle);

        return $fullTitle;
    }


}