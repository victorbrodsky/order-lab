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


namespace App\DashboardBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="dashboard_siteparameter")
 */
class DashboardSiteParameter {

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    
    /**
     * Default Dashboard Chart
     *
     * @ORM\ManyToMany(targetEntity="ChartList", cascade={"persist"})
     * @ORM\JoinTable(name="dashboard_siteparameter_chart",
     *      joinColumns={@ORM\JoinColumn(name="siteparameter_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="chart_id", referencedColumnName="id")}
     *      )
     */
    private $charts;

    /**
     * Default Dashboard Topic
     * 
     * @ORM\ManyToOne(targetEntity="TopicList")
     * @ORM\JoinColumn(referencedColumnName="id", nullable=true)
     */
    private $topic;
    


    public function __construct() {
        $this->charts = new ArrayCollection();
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getTopic()
    {
        return $this->topic;
    }

    /**
     * @param mixed $topic
     */
    public function setTopic($topic)
    {
        $this->topic = $topic;
    }

    /**
     * @return mixed
     */
    public function getCharts()
    {
        return $this->charts;
    }
    public function addChart($item)
    {
        if( $item && !$this->charts->contains($item) ) {
            $this->charts->add($item);
            return $this;
        }
        return NULL;
    }
    public function removeChart($item)
    {
        $this->charts->removeElement($item);
    }
    



    



}

