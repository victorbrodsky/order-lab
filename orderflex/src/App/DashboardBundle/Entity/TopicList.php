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

//252(3) - "Dashboard Topic" (same as Organizational Groups)

/**
 *
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
 *  name="dashboard_topiclist",
 *  indexes={
 *      @ORM\Index( name="topiclist_name_idx", columns={"name"} ),
 *  }
 * )
 */
class TopicList extends BaseCompositeNode
{

    /**
     * @ORM\OneToMany(targetEntity="TopicList", mappedBy="original")
     **/
    protected $synonyms;

    /**
     * @ORM\ManyToOne(targetEntity="TopicList", inversedBy="synonyms")
     * @ORM\JoinColumn(name="original_id", referencedColumnName="id")
     **/
    protected $original;

    /**
     * @Gedmo\TreeParent
     * @ORM\ManyToOne(targetEntity="TopicList", inversedBy="children")
     * @ORM\JoinColumn(name="parent_id", referencedColumnName="id")
     **/
    protected $parent;

    /**
     * @ORM\OneToMany(targetEntity="TopicList", mappedBy="parent", cascade={"persist","remove"})
     * @ORM\OrderBy({"lft" = "ASC"})
     **/
    protected $children;

    ///////////// Attributes //////////////////
    //TODO: we have auto generated ID
//    /**
//     * “Dashboard Chart Topic ID” [free-text field only allowing integers]
//     *
//     * @var string
//     * @ORM\Column(type="string", nullable=true)
//     */
//    private $oid;

    /**
     * “Associated Dashboard Charts”: [multi-select with the flat list of all “Dashboard Charts” created in step 7 below]
     *
     * @ORM\ManyToMany(targetEntity="ChartList", mappedBy="topics")
     **/
    private $charts;

    //“Applicable Dashboard Chart Filter Fields:”: [multi-select with all “Dashboard Chart Filter Fields” created in step 4 below]
    //TODO: how filter fields will be implemented?

    //TODO: implement favorite DB and logic: favorite topics, charts
    //“Favorited by the following users”: [multi-select with all users]

    //////////////// TO BE IMPLEMENT LATER //////////////////////
    //TODO: It's already exists in ChartList?
//    /**
//     * “Associated with the following organizational groups”: [multi-select with the flat list of all organizational groups]
//     * Organizational Group
//     *
//     * @ORM\ManyToOne(targetEntity="App\UserdirectoryBundle\Entity\Institution")
//     * @ORM\JoinColumn(name="institution_id", referencedColumnName="id", nullable=true)
//     **/
//    private $organizationalGroup;
//
//    //“Default Image Width in Pixels:” [one line free text]
//    /**
//     * @ORM\Column(type="string", nullable=true)
//     */
//    private $width;
//
//    //”Default Image Height In Pixels:” [one line free text]
//    /**
//     * @ORM\Column(type="string", nullable=true)
//     */
//    private $height;

    //"Hide ..." - is boolean?
    //“Hide Negative X Axis Values By Default”: [one line free text]
    //“Hide Negative Y Axis Values By Default”: [one line free text]
    //“Hide Negative Z Axis Values By Default”: [one line free text]
    //“Hide Zero X Axis Values By Default”: [one line free text]
    //“Hide Zero Y Axis Values By Default”: [one line free text]
    //“Hide Zero Z Axis Values By Default”: [one line free text]

    //We should have a single, centralize access control in ChartList
    //“Accessible to users with the following roles:” [multi-select with roles]
    //“Deny access to users with the following roles:” [multi-select with roles]
    //“Deny access to the following users:” [multi-select with all users]
    //“Data can be downloaded by users with the following roles:” [multi-select with roles].

    //“Display Order of Associated Dashboards (for example, {“chartID1”:”10”, “chartID2”:”30”, “chartID3”:”20”}):” [3-line free-text field]
    //Not clear: if this is an order of the chart in $charts above, then it's better to place the $charts in the wrapper with one $chart and $order
    //If order can be the same for all charts: then use orderinlist of each chart in ChartList
    //Below are kind of json fields?
    //“Display Order of Applicable Primary Dashboard Chart Filter Fields (for example, {“chartFilterID1”:”10”, “chartFilterID2”:”30”, “chartFilterID3”:”20”}):” [3-line free-text field]
    //“Display Order of Applicable Secondary Dashboard Chart Filter Fields (for example, {“chartFilterID1”:”10”, “chartFilterID2”:”30”, “chartFilterID3”:”20”}):” [3-line free-text field]
    //“Default Values for Applicable Secondary Dashboard Chart Filter Fields (for example, {“chartFilterID1”:”01/2021”, “chartFilterID2”:”Female”, “chartFilterID3”:”AP, CP”}):” [3-line free-text field]
    //“Applicable Dashboard Data Table Column Titles”: [free text, three-line field]
    //“Display Order of Data Table Titles (for example, {“DataTableColumnTitle01”:”10”, “DataTableColumnTitle02”:”30”, “DataTableColumnTitle03”:”20”}):” [3-line free-text field]
    //“Additional Topic Settings:” [3-line free-text field]

    //“Requested by:” [multi-select with all users]
    //“Requested on:” [timestamp]
    //////////////// EOF TO BE IMPLEMENT LATER //////////////////////

    ///////////// EOF Attributes //////////////////


    public function __construct($author=null) {
        parent::__construct($author);

        $this->charts = new ArrayCollection();
    }


    public function addChart($item)
    {
        if( $item && !$this->charts->contains($item) ) {
            $this->charts->add($item);
            $item->addTopic($this);
        }
        return $this;
    }
    public function removeChart($item)
    {
        $this->charts->removeElement($item);
        $item->removeTopic($this);
    }
    public function getCharts()
    {
        return $this->charts;
    }

//    /**
//     * @return string
//     */
//    public function getOid()
//    {
//        return $this->oid;
//    }
//    /**
//     * @param string $oid
//     */
//    public function setOid($oid)
//    {
//        $this->oid = $oid;
//    }


    //is used to construct parent's show path the same as in ListController.php
    public function getClassName()
    {
        //return "TopicList";
        return "charttopic";
    }

}