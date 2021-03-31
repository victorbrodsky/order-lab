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

namespace App\TranslationalResearchBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

use App\UserdirectoryBundle\Entity\ListAbstract;

/**
 * Project Specialty List
 *
 * @ORM\Entity
 * @ORM\Table(name="transres_specialtyList")
 */
class SpecialtyList extends ListAbstract
{

    /**
     * @ORM\OneToMany(targetEntity="SpecialtyList", mappedBy="original", cascade={"persist"})
     **/
    protected $synonyms;

    /**
     * @ORM\ManyToOne(targetEntity="SpecialtyList", inversedBy="synonyms", cascade={"persist"})
     * @ORM\JoinColumn(name="original_id", referencedColumnName="id", nullable=true)
     **/
    protected $original;

    /**
     * @ORM\ManyToMany(targetEntity="RequestCategoryTypeList", mappedBy="projectSpecialties")
     */
    private $requestCategories;



    public function __construct($author=null) {

        parent::__construct($author);

        $this->requestCategories = new ArrayCollection();
    }



    /**
     * @return mixed
     */
    public function getRequestCategories()
    {
        return $this->requestCategories;
    }
    public function addRequestCategory( $item )
    {
        if( !$this->requestCategories->contains($item) ) {
            $this->requestCategories->add($item);
            $item->addProjectSpecialty($this);
        }

        return $this;
    }
    public function removeRequestCategory($item)
    {
        if( $this->requestCategories->contains($item) ) {
            $this->requestCategories->removeElement($item);
            $item->removeProjectSpecialty($this);
        }

        return $this;
    }


    public function getUppercaseName() {
        if( $this->getAbbreviation() == "hematopathology" ) {
            return "HEMATOPATHOLOGY";
        }
        if( $this->getAbbreviation() == "ap-cp" ) {
            return "APCP";
        }
        if( $this->getAbbreviation() == "covid19" ) {
            $name = "COVID19"; //clean name is used for roles "_COVID19"
            return $name;
        }
        if( $this->getAbbreviation() == "misi" ) {
            return "MISI";
        }
        //throw new \Exception("Unknown project specialty: ".$this->getAbbreviation());

        $abbreviation = $this->getAbbreviation();
        if( $abbreviation ) {
            $abbreviation = strtoupper($abbreviation);
        }

        return $abbreviation;
    }

    public function getUppercaseShortName() {
        if( $this->getAbbreviation() == "hematopathology" ) {
            return "HP";
        }
        if( $this->getAbbreviation() == "ap-cp" ) {
            return "APCP";
        }
        if( $this->getAbbreviation() == "covid19" ) {
            return "COVID-19";
        }
        if( $this->getAbbreviation() == "misi" ) {
            return "MISI";
        }

        //throw new \Exception("Unknown project specialty: ".$this->getAbbreviation());

        $abbreviation = $this->getAbbreviation();
        if( $abbreviation ) {
            $abbreviation = strtoupper($abbreviation);
        }

        return $abbreviation;
    }

    public function getUppercaseFullName() {
        if( $this->getAbbreviation() == "hematopathology" ) {
            return "Hematopathology";
        }
        if( $this->getAbbreviation() == "ap-cp" ) {
            return "AP/CP";
        }
        if( $this->getAbbreviation() == "covid19" ) {
            $name = "COVID-19";
            return $name;
        }
        if( $this->getAbbreviation() == "misi" ) {
            return "MISI";
        }

        //throw new \Exception("Unknown project specialty: ".$this->getAbbreviation());

        return $this->getAbbreviation();
    }

    //new-ap-cp-project
//    public function getNewProjectUrlPrefix() {
//        if( $this->getAbbreviation() ) {
//            $name = $this->getAbbreviation();
//        } else {
//            $name = $this->getName();
//        }
//        //return "new-".$name."-project"; //new-ap-cp-project
//        return $name."-project"; //ap-cp-project
//        if( $this->getAbbreviation() == "hemepath" ) {
//            return "hematopathology";
//        }
//        if( $this->getAbbreviation() == "ap-cp" ) {
//            return "ap-cp";
//        }
//    }
    //new-ap-cp-project => ap-cp
    //ap-cp-project => ap-cp
//    static public function getProjectAbbreviationFromUrlPrefix($urlPrefix) {
//        $urlPrefix = str_replace("new-","",$urlPrefix);
//        $urlPrefix = str_replace("-project","",$urlPrefix);
//        return $urlPrefix;
//    }

    //New AP/CP Project
//    public function getNewProjectName() {
////        if( $this->getName() ) {
////            $name = $this->getName();
////        } else {
////            $name = "UnknownSpecialty";
////        }
////        return "New ".$name." Project";
//        if( $this->getAbbreviation() == "hemepath" ) {
//            return "Hematopathology";
//        }
//        if( $this->getAbbreviation() == "ap-cp" ) {
//            return "AP/CP";
//        }
//    }

}