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

/**
 * Created by PhpStorm.
 * User: oli2002
 * Date: 4/11/2016
 * Time: 11:35 AM
 */

namespace App\VacReqBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

//Link institutional group with parameters (email users, inform users (bosses), approval group types)


/**
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 * @ORM\Table(name="vacreq_settings")
 */
class VacReqSettings
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
     * @ORM\ManyToOne(targetEntity="App\UserdirectoryBundle\Entity\Institution")
     */
    private $institution;

    /**
     * @ORM\ManyToMany(targetEntity="App\UserdirectoryBundle\Entity\User", cascade={"persist"})
     * @ORM\JoinTable(name="vacreq_settings_user",
     *      joinColumns={@ORM\JoinColumn(name="settings_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="emailuser_id", referencedColumnName="id")}
     *      )
     **/
    private $emailUsers;

    /**
     * //Send a notification to the following individuals on service (for Fellows)
     * //https://stackoverflow.com/questions/7490488/convert-flat-array-to-a-delimited-string-to-be-saved-in-the-database
     * //https://stackoverflow.com/questions/49324327/how-not-to-allow-delete-options-in-select2
     * //On the group setting page, admin setup a list of default users (bosses of this fellows institutional group).
     * //On the new request page fellows can add any users in the system to this list, but can not remove the default bosses.
     *
     * @ORM\ManyToMany(targetEntity="App\UserdirectoryBundle\Entity\User", cascade={"persist"})
     * @ORM\JoinTable(name="vacreq_settings_informuser",
     *      joinColumns={@ORM\JoinColumn(name="settings_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="informuser_id", referencedColumnName="id")}
     *      )
     **/
    private $defaultInformUsers;

//    /**
//     * @ORM\ManyToMany(targetEntity="VacReqApprovalTypeList", cascade={"persist"})
//     * @ORM\JoinTable(name="vacreq_settings_approvaltype",
//     *      joinColumns={@ORM\JoinColumn(name="settings_id", referencedColumnName="id")},
//     *      inverseJoinColumns={@ORM\JoinColumn(name="approvaltype_id", referencedColumnName="id")}
//     *      )
//     **/
//    private $approvalTypes;
    /**
     * This owning side - repsonsible for this relation
     * mappedBy has to be specified on the inversed side of a (bidirectional) association
     * inversedBy has to be specified on the owning side of a (bidirectional) association
     * 
     * @ORM\ManyToMany(targetEntity="VacReqApprovalTypeList", inversedBy="vacreqSettings")
     * @ORM\JoinTable(name="vacreq_settings_approvaltypes")
     **/
    private $approvalTypes;




    public function __construct($institution) {
        $this->emailUsers = new ArrayCollection();
        $this->defaultInformUsers = new ArrayCollection();
        $this->approvalTypes = new ArrayCollection();
        $this->setInstitution($institution);
    }




    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }


    /**
     * @return mixed
     */
    public function getInstitution()
    {
        return $this->institution;
    }

    /**
     * @param mixed $institution
     */
    public function setInstitution($institution)
    {
        $this->institution = $institution;
    }


    public function getEmailUsers()
    {
        return $this->emailUsers;
    }
    public function addEmailUser($item)
    {
        if( $item && !$this->emailUsers->contains($item) ) {
            $this->emailUsers->add($item);
        }
        return $this;
    }
    public function removeEmailUser($item)
    {
        $this->emailUsers->removeElement($item);
    }


    public function getDefaultInformUsers()
    {
        return $this->defaultInformUsers;
    }
    public function addDefaultInformUser($item)
    {
        if( $item && !$this->defaultInformUsers->contains($item) ) {
            $this->defaultInformUsers->add($item);
        }
        return $this;
    }
    public function removeDefaultInformUser($item)
    {
        $this->defaultInformUsers->removeElement($item);
    }

    
    public function getApprovalTypes()
    {
        return $this->approvalTypes;
    }
    public function addApprovalType($item)
    {
        if( $item && !$this->approvalTypes->contains($item) ) {
            $this->approvalTypes->add($item);
        }
        return $this;
    }
    public function removeApprovalType($item)
    {
        $this->approvalTypes->removeElement($item);
    }
    public function clearApprovalTypes() {
        $this->approvalTypes->clear();
    }
    


    public function __toString()
    {
        return "VacReqSettings: institutionId=".$this->getId().": ".
        implode(",",$this->getApprovalTypes())."".
        " count emailUsers=".count($this->getEmailUsers()).
        "; count informUsers=".count($this->getInformUsers()).
        //"; count approvalTypes=".count($this->getApprovalTypes()).
        "<br>";
    }
}