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

namespace Oleg\OrderformBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Oleg\UserdirectoryBundle\Entity\DocumentContainer;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="scan_calllogEntryMessage")
 */
class CalllogEntryMessage extends OrderBase {

    /**
     * @ORM\OneToOne(targetEntity="Message", mappedBy="calllogEntryMessage")
     **/
    protected $message;


    //Patient List
    /**
     * Return order if not completed by deadline
     *
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $addPatientToList;

    /**
     * @ORM\ManyToOne(targetEntity="PatientListHierarchy")
     **/
    private $patientList;

//    /**
//     * Linked Object ID. Used to make a link to other lists in the list manager.
//     * @ORM\Column(type="string", nullable=true)
//     */
//    private $entityId;
//    /**
//     * Used to make a link to other lists in the list manager.
//     * i.e. "Oleg\OrderformBundle\Entity"
//     * @ORM\Column(type="string", nullable=true)
//     */
//    private $entityNamespace;
//    /**
//     * Used to make a link to other lists in the list manager.
//     * i.e. "Patient"
//     * @ORM\Column(type="string", nullable=true)
//     */
//    private $entityName;





    /**
     * @return mixed
     */
    public function getAddPatientToList()
    {
        return $this->addPatientToList;
    }

    /**
     * @param mixed $addPatientToList
     */
    public function setAddPatientToList($addPatientToList)
    {
        $this->addPatientToList = $addPatientToList;
    }



    /**
     * @return mixed
     */
    public function getPatientList()
    {
        return $this->patientList;
    }

    /**
     * @param mixed $patientList
     */
    public function setPatientList($patientList)
    {
        $this->patientList = $patientList;
    }




//    /**
//     * @return mixed
//     */
//    public function getEntityId()
//    {
//        return $this->entityId;
//    }
//
//    /**
//     * @param mixed $entityId
//     */
//    public function setEntityId($entityId)
//    {
//        $this->entityId = $entityId;
//    }
//
//    /**
//     * @return mixed
//     */
//    public function getEntityNamespace()
//    {
//        return $this->entityNamespace;
//    }
//
//    /**
//     * @param mixed $entityNamespace
//     */
//    public function setEntityNamespace($entityNamespace)
//    {
//        $this->entityNamespace = $entityNamespace;
//    }
//
//    /**
//     * @return mixed
//     */
//    public function getEntityName()
//    {
//        return $this->entityName;
//    }
//
//    /**
//     * @param mixed $entityName
//     */
//    public function setEntityName($entityName)
//    {
//        $this->entityName = $entityName;
//    }
//
//    public function setObject($object) {
//
//        $class = new \ReflectionClass($object);
//        $className = $class->getShortName();
//        $classNamespace = $class->getNamespaceName();
//
//        if( $className && !$this->getEntityName() ) {
//            $this->setEntityName($className);
//        }
//
//        if( $classNamespace && !$this->getEntityNamespace() ) {
//            $this->setEntityNamespace($classNamespace);
//        }
//
//        if( !$this->getEntityId() && $object->getId() ) {
//            //echo "setEntityId=".$object->getId()."<br>";
//            $this->setEntityId($object->getId());
//        }
//    }




    public function __toString() {
        $res = "Call Log Entry Message";
        if( $this->getId() ) {
            $res = $res . " with ID=" . $this->getId();
        }
        return $res;
    }

}