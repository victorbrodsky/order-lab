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
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 * @ORM\Table(name="user_logger")
 */
class Logger
{

    /**
     * @var integer
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(name="siteName", type="string", nullable=true)
     */
    private $siteName;
    /**
     * @ORM\ManyToOne(targetEntity="SiteList")
     * @ORM\JoinColumn(name="site_id", referencedColumnName="id", nullable=true)
     */
    private $site;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $creationdate;

    /**
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=true)
     */
    private $user;

    /**
     * @var array
     * @ORM\Column(type="array", nullable=true)
     */
    private $roles = array();

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $username;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $ip;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $useragent;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $width;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $height;

    /**
     * @ORM\ManyToOne(targetEntity="EventTypeList")
     * @ORM\JoinColumn(name="eventType_id", referencedColumnName="id", nullable=true)
     **/
    private $eventType;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $event;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $serverresponse;



    //Fields specifying a subject entity
    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $entityNamespace;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $entityName;

    /**
     * @ORM\ManyToOne(targetEntity="EventObjectTypeList")
     * @ORM\JoinColumn(name="objectType_id", referencedColumnName="id", nullable=true)
     **/
    private $objectType;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $entityId;



    //user's institution, department, division, service at the moment of creation/update
    /**
     * @ORM\ManyToMany(targetEntity="Institution")
     * @ORM\JoinTable(name="user_logger_institutions",
     *      joinColumns={@ORM\JoinColumn(name="logger_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="institution_id", referencedColumnName="id")}
     *      )
     **/
    private $institutionTrees;



    public function __construct($site) {

        $this->site = $site;
        if( $site ) {
            $this->siteName = $site->getAbbreviation();
        }

        $this->institutionTrees = new ArrayCollection();

        //make sure timezone set to UTC
        date_default_timezone_set('UTC');
    }


    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $siteName
     */
    public function setSiteName($siteName)
    {
        $this->siteName = $siteName;
    }
    /**
     * @return mixed
     */
    public function getSiteName()
    {
        return $this->siteName;
    }
    /**
     * @return mixed
     */
    public function getSite()
    {
        return $this->site;
    }
    /**
     * @param mixed $site
     */
    public function setSite($site)
    {
        $this->site = $site;
    }


    /**
     * @ORM\PrePersist
     */
    public function setCreationdate()
    {
        $this->creationdate = new \DateTime();
    }

    public function getCreationdate()
    {
        return $this->creationdate;
    }

    public function setUser($user)
    {
        $this->user = $user;

        if( $user ) {
            //set title's institution, department, division, service
            foreach( $user->getAdministrativeTitles() as $title ) {
                //$tree = $this->setInstTree($title,"AdministrativeTitle");
                if( $title->getInstitution() ) {
                    $this->addInstitutionTree($title->getInstitution());
                }
            }

            foreach( $user->getAppointmentTitles() as $title ) {
                if( $title->getInstitution() ) {
                    $this->addInstitutionTree($title->getInstitution());
                }
            }

            foreach( $user->getMedicalTitles() as $title ) {
                if( $title->getInstitution() ) {
                    $this->addInstitutionTree($title->getInstitution());
                }
            }
        }

        return $this;
    }

    public function getUser()
    {
        return $this->user;
    }

    //set inst tree
//    public function setInstTree($title,$type) {
//        $instTreeEntity = null;
//
//        $ins = $title->getInstitution();
//
//        if( $ins  ) {
//            $instTreeEntity = new InstitutionTree($type);
//            if( $ins )
//                $instTreeEntity->setInstitution($ins);
//        }
//
//        return $instTreeEntity;
//    }

    /**
     * @param mixed $event
     */
    public function setEvent($event)
    {
        $this->event = $event;
    }

    /**
     * @return mixed
     */
    public function getEvent()
    {
        return $this->event;
    }

    /**
     * @param mixed $eventType
     */
    public function setEventType($eventType)
    {
        $this->eventType = $eventType;
    }

    /**
     * @return mixed
     */
    public function getEventType()
    {
        return $this->eventType;
    }

    /**
     * @param mixed $height
     */
    public function setHeight($height)
    {
        $this->height = $height;
    }

    /**
     * @return mixed
     */
    public function getHeight()
    {
        return $this->height;
    }

    /**
     * @param mixed $ip
     */
    public function setIp($ip)
    {
        $this->ip = $ip;
    }

    /**
     * @return mixed
     */
    public function getIp()
    {
        return $this->ip;
    }

    /**
     * @param array $roles
     */
    public function setRoles($roles)
    {
        if( $roles ) {
            foreach( $roles as $role ) {
                $this->addRole($role."");
            }
        }

    }

    /**
     * @return array
     */
    public function getRoles()
    {
        return $this->roles;
    }

    public function addRole($role) {
        $this->roles[] = $role;
        return $this;
    }

    public function hasRole($role)
    {
        return in_array(strtoupper($role), $this->roles, true);
    }

//    public function getSiteRoles($sitename) {
//
//        $roles = array();
//
//        if( $sitename == 'employees' ) {
//            $sitename = 'userdirectory';
//        }
//
//        foreach( $this->getRoles() as $role ) {
//            if( stristr($role, $sitename) ) {
//                $roles[] = $role;
//            }
//        }
//
//        return $roles;
//    }

    /**
     * @param mixed $useragent
     */
    public function setUseragent($useragent)
    {
        $this->useragent = $useragent;
    }

    /**
     * @return mixed
     */
    public function getUseragent()
    {
        return $this->useragent;
    }


    /**
     * @param mixed $username
     */
    public function setUsername($username)
    {
        $this->username = $username;
    }

    /**
     * @return mixed
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * @param mixed $width
     */
    public function setWidth($width)
    {
        $this->width = $width;
    }

    /**
     * @return mixed
     */
    public function getWidth()
    {
        return $this->width;
    }

    /**
     * @param mixed $serverresponse
     */
    public function setServerresponse($serverresponse)
    {
        $this->serverresponse = $serverresponse;
    }

    /**
     * @return mixed
     */
    public function getServerresponse()
    {
        return $this->serverresponse;
    }


    public function addEvent( $newEvent ) {

        $event = $this->getEvent();

        $event = $event . $newEvent;

        $this->setEvent( $event );
    }



    /**
     * @param mixed $entityNamespace
     */
    public function setEntityNamespace($entityNamespace)
    {
        //remove "Proxies\__CG__\" in Proxies\__CG__\App\UserdirectoryBundle\Entity
        $proxyStr = "Proxies\__CG__\\";
        //$proxyStr = "App\UserdirectoryBundle\\";
        //echo "proxyStr=".$proxyStr."<br>";
        if( strpos($entityNamespace, $proxyStr) !== false ) {
            //echo "remove=".$proxyStr."<br>";
            $entityNamespace = str_replace($proxyStr, "", $entityNamespace);
        }
        //exit("entityNamespace=".$entityNamespace);

        $this->entityNamespace = $entityNamespace;
    }

    /**
     * @return mixed
     */
    public function getEntityNamespace()
    {
        return $this->entityNamespace;
    }

    /**
     * @return mixed
     */
    public function getObjectType()
    {
        return $this->objectType;
    }

    /**
     * @param mixed $objectType
     */
    public function setObjectType($objectType)
    {
        $this->objectType = $objectType;
    }

    /**
     * @param mixed $entityId
     */
    public function setEntityId($entityId)
    {
        $this->entityId = $entityId;
    }

    /**
     * @return mixed
     */
    public function getEntityId()
    {
        return $this->entityId;
    }

    /**
     * @param mixed $entityName
     */
    public function setEntityName($entityName)
    {
        $this->entityName = $entityName;
    }

    /**
     * @return mixed
     */
    public function getEntityName()
    {
        return $this->entityName;
    }


    public function getInstitutionTrees()
    {
        return $this->institutionTrees;
    }
    public function addInstitutionTree($tree)
    {
        if( !$this->institutionTrees->contains($tree) ) {
            $this->institutionTrees->add($tree);
        }

        return $this;
    }
    public function removeInstitutionTree($tree)
    {
        $this->institutionTrees->removeElement($tree);
    }

    //$type is title type: AdministrativeTitle or AppointmentTitle
    public function getInstitutionTreesByType($type)
    {
        if( $type ) {
            $institutionTrees = new ArrayCollection();
            foreach( $this->getInstitutionTrees() as $tree ) {
                if( $tree->getType()."" == $type ) {
                    $institutionTrees->add($tree);
                }
            }
            return $institutionTrees;
        } else {
            return $this->getInstitutionTrees();
        }
    }



//    public function addInstitution($institution)
//    {
//        $this->institutions[] = $institution->getId();
//    }
//    public function getInstitutions()
//    {
//        return $this->institutions;
//    }
//
//    public function addDepartment($department)
//    {
//        $this->departments[] = $department->getId();
//    }
//    public function getDepartments()
//    {
//        return $this->departments;
//    }
//
//    public function addDivision($division)
//    {
//        $this->divisions[] = $division->getId();
//    }
//    public function getDivisions()
//    {
//        return $this->divisions;
//    }
//
//    public function addService($service)
//    {
//        $this->services[] = $service->getId();
//    }
//    public function getServices()
//    {
//        return $this->services;
//    }





}