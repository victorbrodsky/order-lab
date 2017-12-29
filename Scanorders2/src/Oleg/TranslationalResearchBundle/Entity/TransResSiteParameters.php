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

namespace Oleg\UserdirectoryBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

use Symfony\Component\Form\Extension\Core\DataTransformer\DateTimeToStringTransformer;

/**
 * @ORM\Entity
 * @ORM\Table(name="transres_siteParameters")
 */
class TransResSiteParameters {

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * Hematopathology or AP/CP
     *
     * @ORM\ManyToOne(targetEntity="Oleg\TranslationalResearchBundle\Entity\SpecialtyList", cascade={"persist"})
     */
    private $projectSpecialty;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $transresFromHeader;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $transresFooter;

    /**
     * @ORM\OneToOne(targetEntity="Document")
     * @ORM\JoinColumn(name="transresLogo_id", referencedColumnName="id")
     **/
    private $transresLogo;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $transresNotificationEmail;





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
    public function getProjectSpecialty()
    {
        return $this->projectSpecialty;
    }

    /**
     * @param mixed $projectSpecialty
     */
    public function setProjectSpecialty($projectSpecialty)
    {
        $this->projectSpecialty = $projectSpecialty;
    }

    /**
     * @return mixed
     */
    public function getTransresFromHeader()
    {
        return $this->transresFromHeader;
    }

    /**
     * @param mixed $transresFromHeader
     */
    public function setTransresFromHeader($transresFromHeader)
    {
        $this->transresFromHeader = $transresFromHeader;
    }

    /**
     * @return mixed
     */
    public function getTransresFooter()
    {
        return $this->transresFooter;
    }

    /**
     * @param mixed $transresFooter
     */
    public function setTransresFooter($transresFooter)
    {
        $this->transresFooter = $transresFooter;
    }

    /**
     * @return mixed
     */
    public function getTransresLogo()
    {
        return $this->transresLogo;
    }

    /**
     * @param mixed $transresLogo
     */
    public function setTransresLogo($transresLogo)
    {
        $this->transresLogo = $transresLogo;
    }

    /**
     * @return mixed
     */
    public function getTransresNotificationEmail()
    {
        return $this->transresNotificationEmail;
    }

    /**
     * @param mixed $transresNotificationEmail
     */
    public function setTransresNotificationEmail($transresNotificationEmail)
    {
        $this->transresNotificationEmail = $transresNotificationEmail;
    }







}