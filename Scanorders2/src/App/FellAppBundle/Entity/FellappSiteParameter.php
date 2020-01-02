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


namespace Oleg\FellAppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="fellapp_siteParameter")
 */
class FellappSiteParameter {

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * Subject of e-mail to the accepted applicant
     *
     * @ORM\Column(type="text", nullable=true)
     */
    private $acceptedEmailSubject;

    /**
     * Subject of e-mail to the accepted applicant
     *
     * @ORM\Column(type="text", nullable=true)
     */
    private $acceptedEmailBody;


    /**
     * Subject of e-mail to the rejected applicant
     *
     * @ORM\Column(type="text", nullable=true)
     */
    private $rejectedEmailSubject;

    /**
     * Subject of e-mail to the rejected applicant
     *
     * @ORM\Column(type="text", nullable=true)
     */
    private $rejectedEmailBody;


    
    public function __construct() {

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
    public function getAcceptedEmailSubject()
    {
        return $this->acceptedEmailSubject;
    }

    /**
     * @param mixed $acceptedEmailSubject
     */
    public function setAcceptedEmailSubject($acceptedEmailSubject)
    {
        $this->acceptedEmailSubject = $acceptedEmailSubject;
    }

    /**
     * @return mixed
     */
    public function getAcceptedEmailBody()
    {
        return $this->acceptedEmailBody;
    }

    /**
     * @param mixed $acceptedEmailBody
     */
    public function setAcceptedEmailBody($acceptedEmailBody)
    {
        $this->acceptedEmailBody = $acceptedEmailBody;
    }

    /**
     * @return mixed
     */
    public function getRejectedEmailSubject()
    {
        return $this->rejectedEmailSubject;
    }

    /**
     * @param mixed $rejectedEmailSubject
     */
    public function setRejectedEmailSubject($rejectedEmailSubject)
    {
        $this->rejectedEmailSubject = $rejectedEmailSubject;
    }

    /**
     * @return mixed
     */
    public function getRejectedEmailBody()
    {
        return $this->rejectedEmailBody;
    }

    /**
     * @param mixed $rejectedEmailBody
     */
    public function setRejectedEmailBody($rejectedEmailBody)
    {
        $this->rejectedEmailBody = $rejectedEmailBody;
    }


}

