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

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="fellapp_googleFormConfig")
 */
class GoogleFormConfig {
    
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $updateDate;

    /**
     * @ORM\ManyToOne(targetEntity="Oleg\UserdirectoryBundle\Entity\User")
     * @ORM\JoinColumn(referencedColumnName="id", nullable=true)
     */
    private $updatedBy;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $acceptingSubmission;

    /**
     * @ORM\ManyToMany(targetEntity="Oleg\UserdirectoryBundle\Entity\FellowshipSubspecialty", cascade={"persist","remove"})
     * @ORM\JoinTable(name="fellapp_googleformconfig_fellowshipsubspecialty",
     *      joinColumns={@ORM\JoinColumn(name="googleformconfig_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="fellowshipsubspecialty_id", referencedColumnName="id")}
     * )
     **/
    private $fellowshipSubspecialties;

    /**
     * text in the wells at the top of the application
     *
     * @ORM\Column(type="text", nullable=true)
     */
    private $applicationFormNote;

    /**
     * system admin email
     *
     * @ORM\Column(type="text", nullable=true)
     */
    private $systemEmail;

    /**
     * fellowship admin's email
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $adminEmail;

    
    public function __construct() {
        $this->fellowshipSubspecialties = new ArrayCollection();
    }
    
    
    public function getId() {
        return $this->id;
    }

    public function getFellowshipSubspecialties()
    {
        return $this->fellowshipSubspecialties;
    }
    public function addFellowshipSubspecialty($item)
    {
        if( $item && !$this->fellowshipSubspecialties->contains($item) ) {
            $this->fellowshipSubspecialties->add($item);
        }

    }
    public function removeFellowshipSubspecialty($item)
    {
        $this->fellowshipSubspecialties->removeElement($item);
    }

    /**
     * @return mixed
     */
    public function getUpdateDate()
    {
        return $this->updateDate;
    }

    /**
     * @param mixed $updateDate
     */
    public function setUpdateDate($updateDate)
    {
        $this->updateDate = $updateDate;
    }

    /**
     * @return mixed
     */
    public function getUpdatedBy()
    {
        return $this->updatedBy;
    }

    /**
     * @param mixed $updatedBy
     */
    public function setUpdatedBy($updatedBy)
    {
        $this->updatedBy = $updatedBy;
    }

    /**
     * @return mixed
     */
    public function getAcceptingSubmission()
    {
        return $this->acceptingSubmission;
    }

    /**
     * @param mixed $acceptingSubmission
     */
    public function setAcceptingSubmission($acceptingSubmission)
    {
        $this->acceptingSubmission = $acceptingSubmission;
    }

    /**
     * @return mixed
     */
    public function getApplicationFormNote()
    {
        return $this->applicationFormNote;
    }

    /**
     * @param mixed $applicationFormNote
     */
    public function setApplicationFormNote($applicationFormNote)
    {
        $this->applicationFormNote = $applicationFormNote;
    }

    /**
     * @return mixed
     */
    public function getSystemEmail()
    {
        return $this->systemEmail;
    }

    /**
     * @param mixed $systemEmail
     */
    public function setSystemEmail($systemEmail)
    {
        $this->systemEmail = $systemEmail;
    }

    /**
     * @return mixed
     */
    public function getAdminEmail()
    {
        return $this->adminEmail;
    }

    /**
     * @param mixed $adminEmail
     */
    public function setAdminEmail($adminEmail)
    {
        $this->adminEmail = $adminEmail;
    }




    public function __toString() {
        return "Google Form Config AcceptingSubmission=".$this->getAcceptingSubmission()."<br>";
    }
    
    
}

?>
