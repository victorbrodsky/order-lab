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
 * Created by JetBrains PhpStorm.
 * User: oli2002
 * Date: 10/24/13
 * Time: 12:14 PM
 * To change this template use File | Settings | File Templates.
 */

namespace App\UserdirectoryBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

use Symfony\Component\Validator\Constraints as Assert;


/**
 * @ORM\MappedSuperclass
 * @ORM\HasLifecycleCallbacks
 */
abstract class ListAbstract
{

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(name="name", type="string")
     * @Assert\NotBlank(message = "This value should not be blank.")
     */
    protected $name;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $abbreviation;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $shortname;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    protected $description;

    /**
     * @ORM\Column(type="string")
     * @Assert\NotBlank
     */
    protected $type;

    /**
     * @ORM\ManyToOne(targetEntity="App\UserdirectoryBundle\Entity\User")
     * @ORM\JoinColumn(name="creator_id", referencedColumnName="id")
     * @Assert\NotBlank
     */
    protected $creator;

    /**
     * @var \DateTime
     * @ORM\Column(name="createdate", type="datetime", nullable=true)
     * @Assert\NotBlank
     */
    protected $createdate;

    /**
     * @ORM\ManyToOne(targetEntity="App\UserdirectoryBundle\Entity\User")
     * @ORM\JoinColumn(name="updatedby_id", referencedColumnName="id",nullable=true)
     */
    protected $updatedby;

    /**
     * @var \DateTime
     * @ORM\Column(name="updatedon", type="datetime", nullable=true)
     */
    protected $updatedon;

    /**
     * Indicates the order in the list
     * @ORM\Column(type="integer")
     * @Assert\NotBlank
     */
    protected $orderinlist;

    /**
     * @var array
     * @ORM\Column(type="array", nullable=true)
     */
    protected $updateAuthorRoles = array();


    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $fulltitle;

    /**
     * Link to List ID. Specifically only used to link the record with the table "PlatformListManagerRootList"
     * @ORM\Column(type="string", nullable=true)
     */
    protected $linkToListId;

    /**
     * for all items/rows "Object Type"="Dropdown Menu Value"
     * Platform List Manager List where all items should have "Object Type"="Form Field - Dropdown Menu"
     * For not form nodes, object type can be "User"
     * @ORM\ManyToOne(targetEntity="App\UserdirectoryBundle\Entity\ObjectTypeList")
     * @ORM\JoinColumn(name="objectType_id", referencedColumnName="id",nullable=true)
     */
    protected $objectType;

    //Used to make a link to other lists in the list manager.
    /**
     * Linked Object ID. Used to make a link to other lists in the list manager.
     * @ORM\Column(type="string", nullable=true)
     */
    protected $entityId;
    /**
     * Used to make a link to other lists in the list manager.
     * i.e. "App\OrderformBundle\Entity"
     * @ORM\Column(type="string", nullable=true)
     */
    protected $entityNamespace;
    /**
     * Used to make a link to other lists in the list manager.
     * i.e. "Patient"
     * @ORM\Column(type="string", nullable=true)
     */
    protected $entityName;

    /**
     * 
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $version;

//    /**
//     * @ORM\Column(type="string", nullable=true)
//     */
//    protected $fulltitleunique;
//
//    /**
//     * @ORM\Column(type="string", nullable=true)
//     */
//    protected $fulltitlemedium;
//
//    /**
//     * @ORM\Column(type="string", nullable=true)
//     */
//    protected $fulltitleshort;




    public function __construct( $creator = null ) {
        $this->synonyms = new ArrayCollection();

        //set mandatory list attributes
        $this->setName("");
        $this->setType('user-added');
        $this->setCreatedate(new \DateTime());
        $this->setOrderinlist(-1);
        $this->setVersion(1);

        if( $creator ) {
            $this->setCreator($creator);
        }

    }


    public function addSynonym($synonym)
    {
        if( !$this->synonyms->contains($synonym) ) {
            $this->synonyms->add($synonym);
            $synonym->setOriginal($this);
        }
        return $this;
    }

    public function removeSynonym($synonyms)
    {
        $this->synonyms->removeElement($synonyms);
    }

    /**
     * Get synonyms
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getSynonyms()
    {
        return $this->synonyms;
    }

    /**
     * @param mixed $original
     */
    public function setOriginal($original)
    {
        $this->original = $original;
    }

    /**
     * @return mixed
     */
    public function getOriginal()
    {
        return $this->original;
    }




    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set id
     *
     * @param integer $id
     * @return List
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Set name
     *
     * @param string $name
     * @return List
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name."";
    }

    /**
     * @param mixed $abbreviation
     */
    public function setAbbreviation($abbreviation)
    {
        $this->abbreviation = $abbreviation;
    }

    /**
     * @return mixed
     */
    public function getAbbreviation()
    {
        return $this->abbreviation;
    }

    /**
     * @param mixed $shortname
     */
    public function setShortname($shortname)
    {
        $this->shortname = $shortname;
    }

    /**
     * @return mixed
     */
    public function getShortname()
    {
        return $this->shortname;
    }

    /**
     * @param mixed $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * @return mixed
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set type
     *
     * @param string $type
     * @return List
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set createdate
     *
     * @param \DateTime $createdate
     * @return List
     */
    public function setCreatedate($createdate)
    {
        if( $createdate ) {
            $this->createdate = $createdate;
        }

        return $this;
    }

    /**
     * Get createdate
     *
     * @return \DateTime
     */
    public function getCreatedate()
    {
        return $this->createdate;
    }

    /**
     * Set creator
     *
     * @param \App\UserdirectoryBundle\Entity\User $creator
     * @return List
     */
    public function setCreator(\App\UserdirectoryBundle\Entity\User $creator=null)
    {
        $this->creator = $creator;

        return $this;
    }

    /**
     * Get creator
     *
     * @return \App\UserdirectoryBundle\Entity\User $creator
     */
    public function getCreator()
    {
        return $this->creator;
    }

    /**
     * @param mixed $orderinlist
     */
    public function setOrderinlist($orderinlist)
    {
        $this->orderinlist = $orderinlist;
    }

    /**
     * @return mixed
     */
    public function getOrderinlist()
    {
        return $this->orderinlist;
    }

    /**
     * @return mixed
     */
    public function getLinkToListId()
    {
        return $this->linkToListId;
    }

    /**
     * @param mixed $linkToListId
     */
    public function setLinkToListId($linkToListId)
    {
        $this->linkToListId = $linkToListId;
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
     * @return mixed
     */
    public function getVersion()
    {
        if( $this->version === NULL ) {
            return 1;
        }

        return $this->version;
    }

    /**
     * @param mixed $version
     */
    public function setVersion($version)
    {
        $this->version = $version;
    }



    /////////////// Fields specifying a subject entity ///////////////
    /**
     * @return mixed
     */
    public function getEntityNamespace()
    {
        return $this->entityNamespace;
    }
    /**
     * @param mixed $entityNamespace
     */
    public function setEntityNamespace($entityNamespace)
    {
        //remove "Proxies\__CG__\" if $entityNamespace="Proxies\__CG__\App\UserdirectoryBundle\Entity"
        $proxyStr = "Proxies\__CG__\\";
        if( strpos($entityNamespace, $proxyStr) !== false ) {
            //echo "remove=".$proxyStr."<br>";
            $entityNamespace = str_replace($proxyStr, "", $entityNamespace);
        }
        //echo "entityNamespace=".$entityNamespace."<br>";

        $this->entityNamespace = $entityNamespace;
    }

    /**
     * @return mixed
     */
    public function getEntityName()
    {
        return $this->entityName;
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
    public function getEntityId()
    {
        return $this->entityId;
    }

    /**
     * @param mixed $entityId
     */
    public function setEntityId($entityId)
    {
        $this->entityId = $entityId;
    }
    ///////////////  EOF Fields specifying a subject entity ///////////////



    /////////////// full titles ////////////////////
    /**
     * @param mixed $fulltitle
     */
    public function setFulltitle($fulltitle)
    {
        $this->fulltitle = $fulltitle;
    }

    /**
     * @return mixed
     */
    public function getFulltitle()
    {
        return $this->fulltitle;
    }

//    /**
//     * @param mixed $fulltitlemedium
//     */
//    public function setFulltitlemedium($fulltitlemedium)
//    {
//        $this->fulltitlemedium = $fulltitlemedium;
//    }
//
//    /**
//     * @return mixed
//     */
//    public function getFulltitlemedium()
//    {
//        return $this->fulltitlemedium;
//    }
//
//    /**
//     * @param mixed $fulltitleshort
//     */
//    public function setFulltitleshort($fulltitleshort)
//    {
//        $this->fulltitleshort = $fulltitleshort;
//    }
//
//    /**
//     * @return mixed
//     */
//    public function getFulltitleshort()
//    {
//        return $this->fulltitleshort;
//    }
//
//    /**
//     * @param mixed $fulltitleunique
//     */
//    public function setFulltitleunique($fulltitleunique)
//    {
//        $this->fulltitleunique = $fulltitleunique;
//    }
//
//    /**
//     * @return mixed
//     */
//    public function getFulltitleunique()
//    {
//        return $this->fulltitleunique;
//    }
    /////////////// EOF full titles ////////////////////


    public function __toString()
    {
        $name = $this->name."";
//        if( $this->shortname && $this->shortname != "" ) {
//            $name = $this->shortname."";
//        }
        return $name;
    }

    //For search
    public function getOptimalName()
    {
        if( $this->abbreviation && $this->abbreviation != "" ) {
            return $this->abbreviation."";
        }

        if( $this->shortname && $this->shortname != "" ) {
            return $this->shortname."";
        }

        if( $this->name && $this->name != "" ) {
            return $this->name."";
        }
    }

    //Abbreviation (Name)
    public function getOptimalAbbreviationName()
    {
        $name = $this->getName();
        if( $this->getAbbreviation() && $this->getAbbreviation() != "" ) {
            $name = $this->getAbbreviation()." (" . $name . ")";
        }

        return $name;
    }

    //Name (Short Name, Abbreviation)
    //Name (Short Name, Abbreviation) IF ALL THREE ARE PRESENT
    //Name (Abbreviation) IF SHORT NAME IS MISSING
    //Name (Short Name) IF ABBREVIATION IS MISSING
    //Name IF BOTH SHORT NAME AND ABBREVIATION ARE MISSING
    public function getOptimalNameShortnameAbbreviation()
    {
        $name = $this->getName();

        $nameArr = array();
        if( $this->getShortname() && $this->getShortname() != "" ) {
            $nameArr[] = $this->getShortname();
        }

        if( $this->getAbbreviation() && $this->getAbbreviation() != "" ) {
            $nameArr[] = $this->getAbbreviation();
        }

        if( count($nameArr) > 0 ) {
            $name = $name." (" . implode(", ",$nameArr) . ")";
        }

        return $name;
    }

    /**
     * @param mixed $updatedby
     */
    public function setUpdatedby($user)
    {
        //if( $user ) {
            $this->updatedby = $user;
        //}
    }

    /**
     * @return mixed
     */
    public function getUpdatedby()
    {
        return $this->updatedby;
    }

    /**
     * @ORM\PreUpdate
     */
    public function setUpdatedon()
    {
        $this->updatedon = new \DateTime();
    }

    /**
     * @return \DateTime
     */
    public function getUpdatedon()
    {
        return $this->updatedon;
    }

    public function isEmpty() {
        if( $this->name == '' ) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @return mixed
     */
    public function getUpdateAuthorRoles()
    {
        return $this->updateAuthorRoles;
    }


    public function setUpdateAuthorRoles($roles) {
        foreach( $roles as $role ) {
            $this->addUpdateAuthorRole($role."");
        }
    }

    public function addUpdateAuthorRole($role) {
        $role = strtoupper($role);
        if( $this->updateAuthorRoles ) {
            if( !in_array($role, $this->updateAuthorRoles, true) ) {
                $this->updateAuthorRoles[] = $role;
            }
        } else {
            $this->updateAuthorRoles[] = $role;
        }
    }

    public function removeDependents($user) {
        return;
    }


    /**
     * @ORM\PrePersist
     * @ORM\PreUpdate
     */
    public function onCreateUpdate() {
        $this->createFullTitle();
    }

    public function createFullTitle()
    {
        $fullTitle = "";

        if( $this->getAbbreviation() ) {
            $fullTitle = $this->getAbbreviation();
        }

        if( $this->getName() ) {
            if( $fullTitle != "" ) {
                $fullTitle = $fullTitle . " - " .  $this->getName();
            } else {
                $fullTitle = $this->getName();
            }
        }

        $this->setFulltitle($fullTitle);

        return $fullTitle;
    }

    public function setObject($object) {

        $class = new \ReflectionClass($object);
        $className = $class->getShortName();
        $classNamespace = $class->getNamespaceName();

        if( $className && !$this->getEntityName() ) {
            $this->setEntityName($className);
        }

        if( $classNamespace && !$this->getEntityNamespace() ) {
            $this->setEntityNamespace($classNamespace);
        }

        if( !$this->getEntityId() && $object->getId() ) {
            //echo "setEntityId=".$object->getId()."<br>";
            $this->setEntityId($object->getId());
        }
    }

    public function clearObject() {
        $this->setEntityNamespace(null);
        $this->setEntityName(null);
        $this->setEntityId(null);
    }

    public function isVisible() {
        if( $this->getType() == 'disabled' || $this->getType() == 'draft' || $this->getType() == 'hidden' ) {
            return false;
        } else {
            return true;
        }
    }

    public function getObjectTypeName() {
        $objectType = $this->getObjectType();
        if( $objectType ) {
            return $objectType->getName()."";
        }
        return null;
    }
    public function getObjectTypeId() {
        $objectType = $this->getObjectType();
        if( $objectType ) {
            return $objectType->getId();
        }
        return null;
    }


    //for entity with synonyms
//    public function setSynonyms($synonyms = null) {
//        //echo "set synonym=".$synonyms."<br>";
//        exit();
//        $newsynonyms = new ArrayCollection();
//        if( $synonyms ) {
//            $newsynonyms->add($synonyms);
//            $this->synonyms = $newsynonyms;
//        } else {
//            $this->synonyms = $newsynonyms;
//        }
//        return $this;
//    }


    public function getEntityClassName() {
        //$className = get_class($this);
        //echo "className=".$className."<br>";
        //exit('1');
        //return get_class($this); //App\TranslationalResearchBundle\Entity\AntibodyList

        $reflect = new \ReflectionClass($this);
        return $reflect->getShortName();
    }

}