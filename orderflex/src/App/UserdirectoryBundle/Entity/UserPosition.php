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
 * Date: 4/22/14
 * Time: 10:19 AM
 * To change this template use File | Settings | File Templates.
 */

namespace App\UserdirectoryBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity
 * @ORM\Table(name="user_userPosition")
 */
class UserPosition {

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

//    /**
//     * @ORM\ManyToOne(targetEntity="AdministrativeTitle", inversedBy="userPositions", cascade={"persist"})
//     * @ORM\JoinColumn(name="administrativeTitle_id", referencedColumnName="id", onDelete="CASCADE", nullable=true)
//     */
//    private $administrativeTitle;

//    /**
//     * //Position Type: Head, Manager, Primary Contact, Transcriptionist
//     * @ORM\ManyToMany(targetEntity="PositionTypeList", inversedBy="userPositions")
//     * @ORM\JoinTable(name="user_userPositions_positionTypes")
//     **/
//    private $positionTypes;
    /**
     * //Position Type: Head, Manager, Primary Contact, Transcriptionist
     * @ORM\ManyToMany(targetEntity="PositionTypeList", inversedBy="userPositions")
     * @ORM\JoinTable(name="user_userPositions_positionTypes")
     **/
    private $positionTypes;



    public function __construct() {
        $this->positionTypes = new ArrayCollection();
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
    public function getId()
    {
        return $this->id;
    }

//    /**
//     * @param mixed $administrativeTitle
//     */
//    public function setAdministrativeTitle($administrativeTitle)
//    {
//        $this->administrativeTitle = $administrativeTitle;
//    }
//
//    /**
//     * @return mixed
//     */
//    public function getAdministrativeTitle()
//    {
//        return $this->administrativeTitle;
//    }


    public function addPositionType($item)
    {
        if( !$this->positionTypes->contains($item) ) {
            $this->positionTypes->add($item);
        }
        return $this;
    }
    public function removePositionType($item)
    {
        $this->positionTypes->removeElement($item);
    }
    public function getPositionTypes()
    {
        return $this->positionTypes;
    }
    public function clearPositionTypes()
    {
        foreach( $this->getPositionTypes() as $posType )
        {
            $this->removePositionType($posType);
        }
        return $this;
    }


    public function __toString() {
        return $this->getFullName();
    }

    public function getFullName() {
        $fullName = "ID:".$this->getId()." ";

        //Administrative Title
        if( $this->getAdministrativeTitle() ) {
            $fullName = $fullName . ", Administrative Title:" . $this->getAdministrativeTitle();
        }

        //positions
        $positions = $this->getPositionTypes();
        $positionsArr = array();
        foreach( $positions as $position ) {
            $positionsArr[] = $position;
        }

        if( count($positions) > 0 ) {
            if( $fullName ) {
                $fullName = $fullName . ", Positions:" .implode(",", $positionsArr)."";
            } else {
                $fullName = " Positions:" . implode(",", $positionsArr)."";
            }
        }

        return $fullName;
    }


}