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

/**
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 * @ORM\Table(name="user_researchLabPI")
 */
class ResearchLabPI
{

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="ResearchLab", inversedBy="pis")
     * @ORM\JoinColumn(name="researchLab_id", referencedColumnName="id", onDelete="CASCADE")
     **/
    private $researchLab;

    /**
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumn(name="fosuser_id", referencedColumnName="id")
     **/
    private $pi;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $createdate;


    /**
     * @ORM\PrePersist
     */
    public function setCreatedate()
    {
        $this->createdate = new \DateTime();
    }

    /**
     * @return \DateTime
     */
    public function getCreatedate()
    {
        return $this->createdate;
    }

    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $pi
     */
    public function setPi($pi)
    {
        $this->pi = $pi;
    }

    /**
     * @return mixed
     */
    public function getPi()
    {
        return $this->pi;
    }

    /**
     * @param mixed $researchLab
     */
    public function setResearchLab($researchLab)
    {
        $this->researchLab = $researchLab;
    }

    /**
     * @return mixed
     */
    public function getResearchLab()
    {
        return $this->researchLab;
    }

    public function __toString() {
        return "Research Lab pi: id=".$this->id.", text=".$this->pi."<br>";
    }

}