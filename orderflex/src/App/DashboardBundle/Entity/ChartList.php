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

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

use App\UserdirectoryBundle\Entity\ListAbstract;

/**
 * Dashboard Charts
 *
 * @ORM\Entity
 * @ORM\Table(name="dashboard_chartlist")
 */
class ChartList extends ListAbstract
{

    /**
     * @ORM\OneToMany(targetEntity="ChartList", mappedBy="original", cascade={"persist"})
     **/
    protected $synonyms;

    /**
     * @ORM\ManyToOne(targetEntity="ChartList", inversedBy="synonyms", cascade={"persist"})
     * @ORM\JoinColumn(name="original_id", referencedColumnName="id", nullable=true)
     **/
    protected $original;
    
    //TODO: don't need OID, we have auto generated ID
//    /**
//     * “Chart ID” [free-text field only allowing integers]
//     * 
//     * @var string
//     * @ORM\Column(type="string", nullable=true)
//     */
//    private $oid;

    ///////////////////// Access Control ////////////////////
    //We should have a single, centralize access control in ChartList
    //“Accessible to users with the following roles:” [multi-select with roles]
    //“Deny access to users with the following roles:” [multi-select with roles]
    //“Deny access to the following users:” [multi-select with all users]
    //“Data can be downloaded by users with the following roles:” [multi-select with roles].
    /**
     * "Accessible to users with the following roles:" [multi-select with roles]
     *
     * @ORM\ManyToMany(targetEntity="Roles", cascade={"persist"})
     * @ORM\JoinTable(name="dashboard_chart_accessrole",
     *      joinColumns={@ORM\JoinColumn(name="chart_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="role_id", referencedColumnName="id")}
     *      )
     **/
    private $accessRoles;

    /**
     * "Deny access to users with the following roles:" [multi-select with roles]
     *
     * @ORM\ManyToMany(targetEntity="Roles", cascade={"persist"})
     * @ORM\JoinTable(name="dashboard_chart_denyrole",
     *      joinColumns={@ORM\JoinColumn(name="chart_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="role_id", referencedColumnName="id")}
     *      )
     **/
    private $denyRoles;

    /**
     * "Deny access to the following users:" [multi-select with all users]
     *
     * @ORM\ManyToMany(targetEntity="User", cascade={"persist"})
     * @ORM\JoinTable(name="dashboard_chart_denyuser",
     *      joinColumns={@ORM\JoinColumn(name="chart_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="user_id", referencedColumnName="id")}
     *      )
     **/
    private $denyUsers;

    /**
     * "Data can be downloaded by users with the following roles:" [multi-select with roles].
     *
     * @ORM\ManyToMany(targetEntity="Roles", cascade={"persist"})
     * @ORM\JoinTable(name="dashboard_chart_downloadrole",
     *      joinColumns={@ORM\JoinColumn(name="chart_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="role_id", referencedColumnName="id")}
     *      )
     **/
    private $downloadRoles;
    ///////////////////// EOF Access Control ////////////////////

    //Width and Height are repeating in TopicList
    //“Default Image Width in Pixels:” [one line free text]
    //”Default Image Height In Pixels:” [one line free text]
    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $width;
    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $height;

    /**
     * Display Chart Title: [free text one line field]
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $chartTitle;

    //“Associated with the following organizational groups”: [multi-select with the flat list of all organizational groups] - Institution hierarchy
//    /**
//     * Organizational Group Types - mapper between the level number and level title.
//     * level int in OrganizationalGroupType corresponds to this level integer: 1-Institution, 2-Department, 3-Division, 4-Service
//     * For example, OrganizationalGroupType with level=1, set this level to 1.
//     * Default types have a positive level numbers, all other types have negative level numbers.
//     *
//     * @ORM\ManyToOne(targetEntity="OrganizationalGroupType", cascade={"persist"})
//     */
//    private $organizationalGroupType;
//    /**
//     * Organizational Group
//     *
//     * @ORM\ManyToOne(targetEntity="App\UserdirectoryBundle\Entity\Institution")
//     * @ORM\JoinColumn(name="institution_id", referencedColumnName="id", nullable=true)
//     **/
//    private $organizationalGroup;
    /**
     * "Associated with the following organizational groups": [multi-select with the flat list of all organizational groups] - Institution hierarchy
     *
     * @ORM\ManyToMany(targetEntity="App\UserdirectoryBundle\Entity\Institution", cascade={"persist"})
     * @ORM\JoinTable(name="dashboard_chart_institution",
     *      joinColumns={@ORM\JoinColumn(name="chart_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="institution_id", referencedColumnName="id")}
     *      )
     */
    private $institutions;

    //“Associated Dashboard Topics”: [multi-select with the flat list of all “Dashboard Topics” above]
    /**
     * “Associated Dashboard Topics”: [multi-select with the flat list of all “Dashboard Topics” above]
     *
     * @ORM\ManyToMany(targetEntity="TopicList", inversedBy="charts")
     * @ORM\JoinTable(name="dashboard_chart_topic")
     **/
    private $topics;

    /**
     * Dashboard Visualization Method: [Single-select with a list of Dashboard Visualization Method items from the list manager]
     *
     * @ORM\ManyToOne(targetEntity="VisualizationList)
     * @ORM\JoinColumn(referencedColumnName="id", nullable=true)
     */
    private $visualization;

    /**
     * Chart Type: [multi-select with the flat list of all “Dashboard Chart Types” above]
     *
     * @ORM\ManyToMany(targetEntity="ChartTypeList", cascade={"persist"})
     * @ORM\JoinTable(name="dashboard_chart_type",
     *      joinColumns={@ORM\JoinColumn(name="chart_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="type_id", referencedColumnName="id")}
     *      )
     */
    private $chartTypes;

    /**
     * “Dashboard Data Source:” [single-select with Dashboard Data Source from step 6 above]
     *
     * @ORM\ManyToOne(targetEntity="DataSourceList)
     * @ORM\JoinColumn(referencedColumnName="id", nullable=true)
     */
    private $dataSource;

    /**
     * “Dashboard Update Frequency:” [single-select with Dashboard Update Frequency from step 5 above]
     *
     * @ORM\ManyToOne(targetEntity="UpdateFrequencyList)
     * @ORM\JoinColumn(referencedColumnName="id", nullable=true)
     */
    private $updateFrequency;

    //“Applicable Dashboard Chart Filter Fields”: [multi-select with the flat list of all “Dashboard Chart Filter Fields” from step 4 above]
    //TODO: how filter fields will be implemented?

    //TODO: implement favorite DB and logic: favorite topics, charts
    //“Favorited by the following users”: [multi-select with all users]

    //////////////// TO BE IMPLEMENT LATER //////////////////////
    //X Axis Label Title (if any): [free text one line field]
    //Default X axis units: [free text one line field]
    //Y Axis Label Title (if any): [free text one line field]
    //Default Y axis units: [free text one line field]
    //Z Axis Label Title (if any): [free text one line field]
    //Default Z axis units: [free text one line field]

    //Data Set Legend Series Title(s): [free text one line field]
    //Chart Comment: [free text one line field]
    //Display data point quantity after chart title: [free text one line field]
    //Display mean X axis value after X axis label title: [free text one line field]
    //Display standard deviation for the X axis values after X axis label title: [free text one line field]
    //Display mean Y axis value after Y axis label title: [free text one line field]
    //Display standard deviation for the Y axis values after Y axis label title: [free text one line field]
    //Display data point quantity in legend’s series title: [free text one line field]
    //Display mean series value in legend’s series title: [free text one line field]
    //Display standard deviation for the series values in legend’s series title: [free text one line field]

    //"Hide ..." - is boolean?
    //“Hide Negative X Axis Values By Default”: [one line free text]
    //“Hide Negative Y Axis Values By Default”: [one line free text]
    //“Hide Negative Z Axis Values By Default”: [one line free text]
    //“Hide Zero X Axis Values By Default”: [one line free text]
    //“Hide Zero Y Axis Values By Default”: [one line free text]
    //“Hide Zero Z Axis Values By Default”: [one line free text]

    //Below are kind of json fields?
    //“Display Order of Applicable Primary Dashboard Chart Filter Fields (for example, {“chartFilterID1”:”10”, “chartFilterID2”:”30”, “chartFilterID3”:”20”}):” [3-line free-text field]
    //“Applicable Dashboard Data Table Column Titles”: [free text, three-line field]
    //“Display Order of Data Table Titles (for example, {“DataTableColumnTitle01”:”10”, “DataTableColumnTitle02”:”30”, “DataTableColumnTitle03”:”20”}):” [3-line free-text field]
    //“Display Order of Applicable Secondary Dashboard Chart Filter Fields (for example, {“chartFilterID1”:”10”, “chartFilterID2”:”30”, “chartFilterID3”:”20”}):” [3-line free-text field]
    //“Default Values for Applicable Secondary Dashboard Chart Filter Fields (for example, {“chartFilterID1”:”01/2021”, “chartFilterID2”:”Female”, “chartFilterID3”:”AP, CP”}):” [3-line free-text field]
    //“Additional Chart Settings:” [3-line free-text field]

    //“Path to pre-generated default image for this chart:” [free text one line field]
    //“Timestamp for the pre-generated default image for this chart:” [free text one line field]

    //“Requested by:” [multi-select with all users]
    //“Requested on:” [timestamp]
//////////////// EOF TO BE IMPLEMENT LATER //////////////////////


    public function __construct($author=null) {
        parent::__construct($author);

        $this->topics = new ArrayCollection();
        $this->accessRoles = new ArrayCollection();
        $this->denyRoles = new ArrayCollection();
        $this->denyUsers = new ArrayCollection();
        $this->downloadRoles = new ArrayCollection();
        $this->institutions = new ArrayCollection();
        $this->chartTypes = new ArrayCollection();
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

    public function addTopic($item)
    {
        if( $item && !$this->topics->contains($item) ) {
            $this->topics->add($item);
        }
        return $this;
    }
    public function removeTopic($item)
    {
        $this->topics->removeElement($item);
    }
    public function getTopics()
    {
        return $this->topics;
    }

    public function addAccessRole($item)
    {
        if( $item && !$this->accessRoles->contains($item) ) {
            $this->accessRoles->add($item);
        }
        return $this;
    }
    public function removeAccessRole($item)
    {
        $this->accessRoles->removeElement($item);
    }
    public function getAccessRoles()
    {
        return $this->accessRoles;
    }

    public function addDenyRole($item)
    {
        if( $item && !$this->denyRoles->contains($item) ) {
            $this->denyRoles->add($item);
        }
        return $this;
    }
    public function removeDenyRole($item)
    {
        $this->denyRoles->removeElement($item);
    }
    public function getDenyRoles()
    {
        return $this->denyRoles;
    }

    public function addDenyUser($item)
    {
        if( $item && !$this->denyUsers->contains($item) ) {
            $this->denyUsers->add($item);
        }
        return $this;
    }
    public function removeDenyUser($item)
    {
        $this->denyUsers->removeElement($item);
    }
    public function getDenyUsers()
    {
        return $this->denyUsers;
    }

    public function addDownloadRole($item)
    {
        if( $item && !$this->downloadRoles->contains($item) ) {
            $this->downloadRoles->add($item);
        }
        return $this;
    }
    public function removeDownloadRole($item)
    {
        $this->downloadRoles->removeElement($item);
    }
    public function getDownloadRoles()
    {
        return $this->downloadRoles;
    }

    public function addInstitution($item)
    {
        if( $item && !$this->institutions->contains($item) ) {
            $this->institutions->add($item);
        }
        return $this;
    }
    public function removeInstitution($item)
    {
        $this->institutions->removeElement($item);
    }
    public function getInstitutions()
    {
        return $this->institutions;
    }

    public function addChartType($item)
    {
        if( $item && !$this->chartTypes->contains($item) ) {
            $this->chartTypes->add($item);
        }
        return $this;
    }
    public function removeChartType($item)
    {
        $this->chartTypes->removeElement($item);
    }
    public function getChartTypes()
    {
        return $this->chartTypes;
    }

    /**
     * @return mixed
     */
    public function getWidth()
    {
        return $this->width;
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
    public function getHeight()
    {
        return $this->height;
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
    public function getVisualization()
    {
        return $this->visualization;
    }

    /**
     * @param mixed $visualization
     */
    public function setVisualization($visualization)
    {
        $this->visualization = $visualization;
    }

    /**
     * @return mixed
     */
    public function getChartTitle()
    {
        return $this->chartTitle;
    }

    /**
     * @param mixed $chartTitle
     */
    public function setChartTitle($chartTitle)
    {
        $this->chartTitle = $chartTitle;
    }

    /**
     * @return mixed
     */
    public function getDataSource()
    {
        return $this->dataSource;
    }

    /**
     * @param mixed $dataSource
     */
    public function setDataSource($dataSource)
    {
        $this->dataSource = $dataSource;
    }

    /**
     * @return mixed
     */
    public function getUpdateFrequency()
    {
        return $this->updateFrequency;
    }

    /**
     * @param mixed $updateFrequency
     */
    public function setUpdateFrequency($updateFrequency)
    {
        $this->updateFrequency = $updateFrequency;
    }



}
