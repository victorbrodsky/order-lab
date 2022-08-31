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

namespace App\OrderformBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;


/**
 * Procedure (use 'procedures', because 'procedure' causes problems (reserved?))
 * @ORM\Entity(repositoryClass="App\OrderformBundle\Repository\ProcedureRepository")
 * @ORM\Table(name="scan_procedure")
 */
class Procedure extends ObjectAbstract
{
    /**
     * @ORM\OneToMany(targetEntity="ProcedureName", mappedBy="procedure", cascade={"persist"})
     */
    protected $name;

    /**
     * Procedure Number
     * @ORM\OneToMany(targetEntity="ProcedureNumber", mappedBy="procedure", cascade={"persist"})
     */
    protected $number;
    
    /**
     * parent
     * @ORM\ManyToOne(targetEntity="Encounter", inversedBy="procedure")
     * @ORM\JoinColumn(name="encounter", referencedColumnName="id")
     */
    protected $encounter;
    
    /**
     * Procedure might have many Accession (children)
     * 
     * @ORM\OneToMany(targetEntity="Accession", mappedBy="procedure")
     */
    protected $accession;
    
    /**
     * @ORM\ManyToMany(targetEntity="Message", mappedBy="procedure")
     **/
    protected $message;



    ///////////////// additional extra fields not shown on scan order /////////////////
    /**
     * Procedure location
     * @ORM\OneToMany(targetEntity="ProcedureLocation", mappedBy="procedure", cascade={"persist"})
     */
    private $location;

//    /**
//     * Procedure order
//     * @ORM\OneToMany(targetEntity="ProcedureOrder", mappedBy="procedure", cascade={"persist"})
//     */
//    private $order;

    /**
     * @ORM\OneToMany(targetEntity="ProcedureDate", mappedBy="procedure", cascade={"persist"})
     */
    private $date;
    ///////////////// EOF additional extra fields not shown on scan order /////////////////


    public function __construct( $withfields=false, $status='invalid', $provider=null, $source=null ) {
        parent::__construct($status,$provider,$source);
        $this->accession = new ArrayCollection();

        //fields:
        $this->name = new ArrayCollection();
        $this->number = new ArrayCollection();

        //extra
        $this->location = new ArrayCollection();
        //$this->order = new ArrayCollection();
        $this->date = new ArrayCollection();

        if( $withfields ) {
            $this->addName( new ProcedureName($status,$provider,$source) );
            $this->addNumber( new ProcedureNumber($status,$provider,$source) );
            //$this->addDate( new ProcedureDate($status,$provider,$source) );

            //testing data structure
            //$this->addExtraFields($status,$provider,$source);
        }
    }

    public function makeDependClone() {
        $this->name = $this->cloneDepend($this->name,$this);
        $this->number = $this->cloneDepend($this->number,$this);

        //extra fields
        $this->location = $this->cloneDepend($this->location,$this);
        //$this->order = $this->cloneDepend($this->order,$this);
        $this->date = $this->cloneDepend($this->date,$this);
    }



    //Name
    public function getName() {
        return $this->name;
    }

    public function setName($name) {
        $this->name = $name;
    }

    public function addName($name)
    {
        if( $name == null ) {
            $name = new ProcedureName();
        }

        if( !$this->name->contains($name) ) {
            $name->setProcedure($this);
            $this->name->add($name);
            $this->setArrayFieldObjectChange('name','add',$name);
        }

        return $this;
    }

    public function removeName($name)
    {
        $this->name->removeElement($name);
        $this->setArrayFieldObjectChange('name','remove',$name);
    }

    public function clearName()
    {
        $this->name->clear();
    }

    //Number
    public function getNumber() {
        //echo "number count=".count($this->number)."<br>";
        return $this->number;
    }

    public function setNumber($number) {
        $this->number = $number;
    }

    public function addNumber($number)
    {
        //echo "number add: id=".$number->getId().", name=".$number->getField()."<br>";
        if( $number ) {
            if( !$this->number->contains($number) ) {
                $number->setProcedure($this);
                $this->number->add($number);
            }
        }

        return $this;
    }

    public function removeNumber($number)
    {
        $this->number->removeElement($number);
    }

    public function clearNumber()
    {
        $this->number->clear();
    }

    /**
     * Add accession
     *
     * @param \App\OrderformBundle\Entity\Accession $accession
     * @return Procedure
     */
    public function addAccession(\App\OrderformBundle\Entity\Accession $accession)
    {
        if( !$this->accession->contains($accession) ) {
            $accession->setProcedure($this);
            $this->accession[] = $accession;
        }
    
        return $this;
    }
    /**
     * Remove accession
     *
     * @param \App\OrderformBundle\Entity\Accession $accession
     */
    public function removeAccession(\App\OrderformBundle\Entity\Accession $accession)
    {
        $this->accession->removeElement($accession);
    }
    /**
     * Get accession
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getAccession()
    {
        return $this->accession;
    }
    public function setAccession(\Doctrine\Common\Collections\ArrayCollection $accession)
    {
        $this->accession = $accession;
    }
    public function clearAccession(){
        $this->accession->clear();
    }

    /**
     * Set encounter
     *
     * @param \App\OrderformBundle\Entity\Encounter $encounter
     * @return Procedure
     */
    public function setEncounter(\App\OrderformBundle\Entity\Encounter $encounter = null)
    {
        $this->encounter = $encounter;
    
        return $this;
    }
    /**
     * Get encounter
     *
     * @return \App\OrderformBundle\Entity\Encounter
     */
    public function getEncounter()
    {
        return $this->encounter;
    }



    ///////////////////////// Extra fields /////////////////////////
    public function addExtraFields($status,$provider,$source) {
        $this->addLocation( new ProcedureLocation($status,$provider,$source) );
        //$this->addOrder( new ProcedureOrder($status,$provider,$source) );
        $this->addDate( new ProcedureDate($status,$provider,$source) );
    }

    public function getLocation()
    {
        return $this->location;
    }
    public function addLocation($location)
    {
        if( $location && !$this->location->contains($location) ) {
            $this->location->add($location);
            $location->setProcedure($this);
        }

        return $this;
    }
    public function removeLocation($location)
    {
        $this->location->removeElement($location);
    }

//    public function getOrder()
//    {
//        return $this->order;
//    }
//    public function addOrder($order)
//    {
//        if( $order && !$this->order->contains($order) ) {
//            $this->order->add($order);
//            $order->setProcedure($this);
//        }
//
//        return $this;
//    }
//    public function removeOrder($order)
//    {
//        $this->order->removeElement($order);
//    }


    /**
     * @return mixed
     */
    public function getDate()
    {
        return $this->date;
    }
    public function addDate($date)
    {
        if( $date && !$this->date->contains($date) ) {
            $this->date->add($date);
            $date->setProcedure($this);
        }

        return $this;
    }
    public function removeDate($date)
    {
        $this->date->removeElement($date);
    }
    ///////////////////////// EOF Extra fields /////////////////////////


    public function __toString() {

        $procNames = "";
        foreach( $this->getName() as $name ) {
            $procNames = $procNames . " name=". $name. " (provider=".$name->getProvider().", status=".$name->getStatus().") ";
        }

        return 'Procedure: id=' . $this->id .
            ", procedureNameCount=" . count($this->getName()) . " => Names=".$procNames.
            ", numberCount=" . count($this->number) .
            ": number->first=" . $this->number->first() .
            ", parentId=".$this->getParent()->getId().
            "; linked accessionCount=".count($this->accession).":".$this->accession->first();
    }


    //parent, children, key field methods
    public function setParent($parent)
    {
        $this->setEncounter($parent);
        return $this;
    }

    public function getParent()
    {
        return $this->getEncounter();
    }

    public function getChildren() {
        return $this->getAccession();
    }

    public function addChildren($child) {
        $this->addAccession($child);
    }

    public function removeChildren($child) {
        $this->removeAccession($child);
    }

    public function setChildren($children) {
        $this->setAccession($children);
    }
    
    //don't use 'get' because later repo functions relay on "get" keyword
    //object info for blue strip
    public function obtainFullObjectName() {

        $fullNameArr = array();

        //date
        $dateStr = "";
        $date = $this->obtainValidField('date');
        if( $date && $date != "" ) {
            $dateStr = $date." ";
        }

        //number
        $key = $this->obtainValidField('number');
        if( $key ) {
            if( $key->getKeytype() ) {
                $fullNameArr[] = $dateStr . $key->getKeytype()->getOptimalName() . ": " . $key->getField();
            } else {
                $fullNameArr[] = $dateStr . $key->getField();
            }
        }

        $fullName = implode(", ",$fullNameArr);

        return $fullName;
    }

    public function obtainKeyField() {
        return $this->getNumber();
    }

    public function obtainKeyFieldName() {
        return "number";
    }

    public function createKeyField() {
        $this->addNumber( new ProcedureNumber() );
        return $this->obtainKeyField();
    }

    public function getArrayFields() {
        $fieldsArr = array(
            'Number','Name',
            //extra fields
            'Location','Date'
        );
        return $fieldsArr;
    }

}