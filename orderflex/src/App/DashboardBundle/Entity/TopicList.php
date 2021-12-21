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

    ///////////////////// Access Control ////////////////////
    //We should have a single, centralize access control in ChartList
    //“Accessible to users with the following roles:” [multi-select with roles]
    //“Deny access to users with the following roles:” [multi-select with roles]
    //“Deny access to the following users:” [multi-select with all users]
    //“Data can be downloaded by users with the following roles:” [multi-select with roles].
    /**
     * "Accessible to users with the following roles:" [multi-select with roles]
     *
     * @ORM\ManyToMany(targetEntity="App\UserdirectoryBundle\Entity\Roles", cascade={"persist"})
     * @ORM\JoinTable(name="dashboard_topic_accessrole",
     *      joinColumns={@ORM\JoinColumn(name="topic_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="role_id", referencedColumnName="id")}
     *      )
     **/
    private $accessRoles;

    /**
     * "Deny access to users with the following roles:" [multi-select with roles]
     *
     * @ORM\ManyToMany(targetEntity="App\UserdirectoryBundle\Entity\Roles", cascade={"persist"})
     * @ORM\JoinTable(name="dashboard_topic_denyrole",
     *      joinColumns={@ORM\JoinColumn(name="topic_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="role_id", referencedColumnName="id")}
     *      )
     **/
    private $denyRoles;

    /**
     * "Deny access to the following users:" [multi-select with all users]
     *
     * @ORM\ManyToMany(targetEntity="App\UserdirectoryBundle\Entity\User", cascade={"persist"})
     * @ORM\JoinTable(name="dashboard_topic_denyuser",
     *      joinColumns={@ORM\JoinColumn(name="topic_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="user_id", referencedColumnName="id")}
     *      )
     **/
    private $denyUsers;

    /**
     * "Data can be downloaded by users with the following roles:" [multi-select with roles].
     *
     * @ORM\ManyToMany(targetEntity="App\UserdirectoryBundle\Entity\Roles", cascade={"persist"})
     * @ORM\JoinTable(name="dashboard_topic_downloadrole",
     *      joinColumns={@ORM\JoinColumn(name="topic_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="role_id", referencedColumnName="id")}
     *      )
     **/
    private $downloadRoles;
    ///////////////////// EOF Access Control ////////////////////

    /**
     * “Favorited by the following users”: [multi-select with all users]
     *
     * @ORM\ManyToMany(targetEntity="App\UserdirectoryBundle\Entity\User", cascade={"persist"})
     * @ORM\JoinTable(name="dashboard_topic_favoriteuser",
     *      joinColumns={@ORM\JoinColumn(name="topic_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="user_id", referencedColumnName="id")}
     *      )
     **/
    private $favoriteUsers;

    /**
     * Requested by
     *
     * @ORM\ManyToOne(targetEntity="App\UserdirectoryBundle\Entity\User")
     * @ORM\JoinColumn(name="requester_id", referencedColumnName="id")
     */
    private $requester;

    /**
     * Requested on
     *
     * @var \DateTime
     * @ORM\Column(name="requesteddate", type="datetime", nullable=true)
     */
    private $requestedDate;

    /**
     * "Associated with the following organizational groups": [multi-select with the flat list of all organizational groups] - Institution hierarchy
     *
     * @ORM\ManyToMany(targetEntity="App\UserdirectoryBundle\Entity\Institution", cascade={"persist"})
     * @ORM\JoinTable(name="dashboard_topic_institution",
     *      joinColumns={@ORM\JoinColumn(name="topic_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="institution_id", referencedColumnName="id")}
     *      )
     */
    private $institutions;

    /**
     * Topic Comment
     *
     * @ORM\Column(type="text", nullable=true)
     */
    private $topicComment;


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

        $this->accessRoles = new ArrayCollection();
        $this->denyRoles = new ArrayCollection();
        $this->denyUsers = new ArrayCollection();
        $this->downloadRoles = new ArrayCollection();
        $this->institutions = new ArrayCollection();
        $this->favoriteUsers = new ArrayCollection();
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

    public function addAccessRole($item)
    {
        if( $item && !$this->accessRoles->contains($item) ) {
            $this->accessRoles->add($item);
            return $this;
        }
        return NULL;
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
            return $this;
        }
        return NULL;
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
            return $this;
        }
        return NULL;
    }
    public function removeInstitution($item)
    {
        $this->institutions->removeElement($item);
    }
    public function getInstitutions()
    {
        return $this->institutions;
    }
    public function addFavoriteUser($item)
    {
        if( $item && !$this->favoriteUsers->contains($item) ) {
            $this->favoriteUsers->add($item);
        }
        return $this;
    }
    public function removeFavoriteUser($item)
    {
        $this->favoriteUsers->removeElement($item);
    }
    public function getFavoriteUsers()
    {
        return $this->favoriteUsers;
    }


    //return 1 if favorite, 0 otherwise
    public function isFavorite($user) {
        if( $user && $this->getFavoriteUsers()->contains($user) ) {
            return true;
        }

        return false;
    }

    /**
     * @return mixed
     */
    public function getRequester()
    {
        return $this->requester;
    }

    /**
     * @param mixed $requester
     */
    public function setRequester($requester)
    {
        $this->requester = $requester;
    }

    /**
     * @return \DateTime
     */
    public function getRequestedDate()
    {
        return $this->requestedDate;
    }

    /**
     * @param \DateTime $requestedDate
     */
    public function setRequestedDate($requestedDate)
    {
        $this->requestedDate = $requestedDate;
    }

    /**
     * @return mixed
     */
    public function getTopicComment()
    {
        return $this->topicComment;
    }

    /**
     * @param mixed $topicComment
     */
    public function setTopicComment($topicComment)
    {
        $this->topicComment = $topicComment;
    }



    //is used to construct parent's show path the same as in ListController.php
    public function getClassName()
    {
        //return "TopicList";
        return "charttopic";
    }

}