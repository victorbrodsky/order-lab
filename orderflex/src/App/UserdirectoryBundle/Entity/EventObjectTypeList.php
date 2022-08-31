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

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;


/**
 * @ORM\Entity()
 * @ORM\Table(name="user_eventObjectTypeList")
 */
class EventObjectTypeList extends ListAbstract
{

    /**
     * @ORM\OneToMany(targetEntity="EventObjectTypeList", mappedBy="original", cascade={"persist"})
     **/
    protected $synonyms;

    /**
     * @ORM\ManyToOne(targetEntity="EventObjectTypeList", inversedBy="synonyms", cascade={"persist"})
     * @ORM\JoinColumn(name="original_id", referencedColumnName="id", nullable=true)
     **/
    protected $original;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $url;

    /**
     * @ORM\ManyToMany(targetEntity="SiteList")
     * @ORM\JoinTable(name="user_eventObjectType_site",
     *      joinColumns={@ORM\JoinColumn(name="eventObjectType_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="site_id", referencedColumnName="id")}
     *      )
     */
    private $exclusivelySites;



    public function __construct() {
        parent::__construct();

        $this->exclusivelySites = new ArrayCollection();
    }



    /**
     * @return mixed
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @param mixed $url
     */
    public function setUrl($url)
    {
        $this->url = $url;
    }



    public function getExclusivelySites()
    {
        return $this->exclusivelySites;
    }
    public function addExclusivelySite($item)
    {
        if( $item && !$this->exclusivelySites->contains($item) ) {
            $this->exclusivelySites->add($item);
            return true;
        }
        return false;
    }
    public function removeExclusivelySite($item)
    {
        $this->exclusivelySites->removeElement($item);
    }

}