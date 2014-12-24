<?php

namespace Oleg\UserdirectoryBundle\Entity;

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
     * @ORM\Column(name="siteName", type="string")
     */
    private $siteName;

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
     * @ORM\Column(type="string", nullable=true)
     */
    private $entityId;

    //user's institution, department, division, service at the moment of creation/update
//    /**
//     * @ORM\Column(type="array", nullable=true)
//     */
//    private $institutions = array();
//
//    /**
//     * @ORM\Column(type="array", nullable=true)
//     */
//    private $departments = array();
//
//    /**
//     * @ORM\Column(type="array", nullable=true)
//     */
//    private $divisions = array();
//
//    /**
//     * @ORM\Column(type="array", nullable=true)
//     */
//    private $services = array();

//    /**
//     * @ORM\ManyToOne(targetEntity="InstitutionTree", cascade={"persist"})
//     * @ORM\JoinColumn(name="institutionTree", referencedColumnName="id", nullable=true, onDelete="CASCADE")
//     **/

    /**
     * @ORM\OneToMany(targetEntity="InstitutionTree", mappedBy="logger", cascade={"persist","remove"})
     **/
    private $institutionTrees;



    public function __construct($siteName) {
        $this->siteName = $siteName;
        $this->institutionTrees = new ArrayCollection();
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
            //set user's institution, department, division, service
            //$this->setInstTree($user,"AdministrativeTitle");
            //$this->setInstTree($user,"AppointmentTitle");

            foreach( $user->getAdministrativeTitles() as $title ) {
                $tree = $this->setInstTree($title,"AdministrativeTitle");
                if( $tree ) {
                    $this->addInstitutionTree($tree);
                }
            }


            foreach( $user->getAppointmentTitles() as $title ) {
                $tree = $this->setInstTree($title,"AppointmentTitle");
                if( $tree ) {
                    $this->addInstitutionTree($tree);
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
    public function setInstTree($title,$type) {
        $instTreeEntity = null;

        $ins = $title->getInstitution();
        $dep = $title->getDepartment();
        $div = $title->getDivision();
        $ser = $title->getService();

        if( $ins || $dep || $div || $ser ) {
            $instTreeEntity = new InstitutionTree($type);
            if( $ins )
                $instTreeEntity->setInstitution($ins);
            if( $dep )
                $instTreeEntity->setDepartment($dep);
            if( $div )
                $instTreeEntity->setDivision($div);
            if( $ser )
                $instTreeEntity->setService($ser);
        }

        return $instTreeEntity;
    }

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



    /**
     * @return mixed
     */
    public function getInstitutionTrees()
    {
        return $this->institutionTrees;
    }
    public function addInstitutionTree($tree)
    {
        if( !$this->institutionTrees->contains($tree) ) {
            $this->institutionTrees->add($tree);
            $tree->setLogger($this);
        }

        return $this;
    }
    public function removeInstitutionTree($tree)
    {
        $this->institutionTrees->removeElement($tree);
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