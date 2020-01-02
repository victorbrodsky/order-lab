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

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity
 * @ORM\Table(name="user_objectTypeList")
 */
class ObjectTypeList extends ListAbstract
{

    /**
     * @ORM\OneToMany(targetEntity="ObjectTypeList", mappedBy="original")
     **/
    protected $synonyms;

    /**
     * @ORM\ManyToOne(targetEntity="ObjectTypeList", inversedBy="synonyms")
     * @ORM\JoinColumn(name="original_id", referencedColumnName="id")
     **/
    protected $original;


    //use receivedValueEntityNamespace, receivedValueEntityName and receivedValueEntityId to link this object type to
    // a specific object type implementation (i.e. ObjectTypeFormText), where the values will be stored.
    //Received Form Field Value Entity.
    /**
     * i.e. "Oleg\OlegUserdirectoryBundle\Entity"
     * @ORM\Column(type="string", nullable=true)
     */
    private $receivedValueEntityNamespace;
    /**
     * i.e. "Patient"
     * @ORM\Column(type="string", nullable=true)
     */
    private $receivedValueEntityName;
    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $receivedValueEntityId;






    /**
     * @return mixed
     */
    public function getReceivedValueEntityNamespace()
    {
        return $this->receivedValueEntityNamespace;
    }

    /**
     * @param mixed $receivedValueEntityNamespace
     */
    public function setReceivedValueEntityNamespace($receivedValueEntityNamespace)
    {
        $this->receivedValueEntityNamespace = $receivedValueEntityNamespace;
    }

    /**
     * @return mixed
     */
    public function getReceivedValueEntityName()
    {
        return $this->receivedValueEntityName;
    }

    /**
     * @param mixed $receivedValueEntityName
     */
    public function setReceivedValueEntityName($receivedValueEntityName)
    {
        $this->receivedValueEntityName = $receivedValueEntityName;
    }

    /**
     * @return mixed
     */
    public function getReceivedValueEntityId()
    {
        return $this->receivedValueEntityId;
    }

    /**
     * @param mixed $receivedValueEntityId
     */
    public function setReceivedValueEntityId($receivedValueEntityId)
    {
        $this->receivedValueEntityId = $receivedValueEntityId;
    }



    public function setReceivedValueEntity($object) {
        $class = new \ReflectionClass($object);
        $className = $class->getShortName();
        $classNamespace = $class->getNamespaceName();

        if( $className && !$this->getReceivedValueEntityName() ) {
            $this->setReceivedValueEntityName($className);
        }

        if( $classNamespace && !$this->getReceivedValueEntityNamespace() ) {
            $this->setReceivedValueEntityNamespace($classNamespace);
        }

        if( !$this->getReceivedValueEntityId() && $object->getId() ) {
            $this->setReceivedValueEntityId($object->getId());
        }
    }


//    /**
//     * @param mixed $entityNamespace
//     */
//    public function setEntityNamespace($entityNamespace)
//    {
//        //remove "Proxies\__CG__\" if $entityNamespace="Proxies\__CG__\Oleg\UserdirectoryBundle\Entity"
//        $proxyStr = "Proxies\__CG__\\";
//        //$proxyStr = "Oleg\UserdirectoryBundle\\";
//        //echo "proxyStr=".$proxyStr."<br>";
//        if( strpos($entityNamespace, $proxyStr) !== false ) {
//            //echo "remove=".$proxyStr."<br>";
//            $entityNamespace = str_replace($proxyStr, "", $entityNamespace);
//        }
//        //exit("entityNamespace=".$entityNamespace);
//
//        $this->entityNamespace = $entityNamespace;
//    }





}