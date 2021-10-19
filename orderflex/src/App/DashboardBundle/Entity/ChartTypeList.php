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

namespace App\DashboardBundle\Entity;

use App\UserdirectoryBundle\Entity\BaseCompositeNode;
use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;


//Dashboard Chart Type

/**
 * Use Composite pattern:
 * The composite pattern describes that a group of objects is to be treated in the same
 * way as a single instance of an object. The intent of a composite is to "compose" objects into tree structures
 * to represent part-whole hierarchies. Implementing the composite pattern lets clients treat individual objects
 * and compositions uniformly.
 * Use Doctrine Extension Tree for tree manipulation.
 *
 * @Gedmo\Tree(type="nested")
 * @ORM\Entity(repositoryClass="App\UserdirectoryBundle\Repository\TreeRepository")
 * @ORM\Table(
 *  name="dashboard_charttypelist",
 *  indexes={
 *      @ORM\Index( name="charttypelist_name_idx", columns={"name"} ),
 *  }
 * )
 */
class ChartTypeList extends BaseCompositeNode
{

    /**
     * @ORM\OneToMany(targetEntity="ChartTypeList", mappedBy="original")
     **/
    protected $synonyms;

    /**
     * @ORM\ManyToOne(targetEntity="ChartTypeList", inversedBy="synonyms")
     * @ORM\JoinColumn(name="original_id", referencedColumnName="id")
     **/
    protected $original;

    /**
     * @Gedmo\TreeParent
     * @ORM\ManyToOne(targetEntity="ChartTypeList", inversedBy="children")
     * @ORM\JoinColumn(name="parent_id", referencedColumnName="id")
     **/
    protected $parent;

    /**
     * @ORM\OneToMany(targetEntity="ChartTypeList", mappedBy="parent", cascade={"persist","remove"})
     * @ORM\OrderBy({"lft" = "ASC"})
     **/
    protected $children;

//    /**
//     * Is it Institution?
//     *
//     * Organizational Group Types - mapper between the level number and level title.
//     * level int in OrganizationalGroupType corresponds to this level integer: 1-Institution, 2-Department, 3-Division, 4-Service
//     * For example, OrganizationalGroupType with level=1, set this level to 1.
//     * Default types have a positive level numbers, all other types have negative level numbers.
//     *
//     * @ORM\ManyToOne(targetEntity="OrganizationalGroupType", cascade={"persist"})
//     */
//    private $organizationalGroupType;



    public function __construct($author=null) {
        parent::__construct($author);
    }


//    public function __toString()
//    {
//        if( $this->getAbbreviation() && $this->getAbbreviation() != "" ) {
//            return $this->getAbbreviation()."";
//        }
//
//        return $this->getName()."";
//    }

    public function getClassName()
    {
        return "ChartTypeList";
    }

}