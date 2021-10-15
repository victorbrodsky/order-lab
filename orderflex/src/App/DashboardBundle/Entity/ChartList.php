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



//“Chart ID” [free-text field only allowing integers]
//
//“Accessible to users with the following roles:” [multi-select with roles]
//
//“Deny access to users with the following roles:” [multi-select with roles]
//
//“Deny access to the following users:” [multi-select with all users]
//
//“Data can be downloaded by users with the following roles:” [multi-select with roles].
//
//“Associated with the following organizational groups”: [multi-select with the flat list of all organizational groups]
//
//“Associated Dashboard Topics”: [multi-select with the flat list of all “Dashboard Topics” above]
//
//Dashboard Visualization Method: [Single-select with a list of Dashboard Visualization Method items from the list manager]
//
//Display Chart Title: [free text one line field]
//
//Chart Type: [multi-select with the flat list of all “Dashboard Chart Types” above]
//
//X Axis Label Title (if any): [free text one line field]
//
//Default X axis units: [free text one line field]
//
//Y Axis Label Title (if any): [free text one line field]
//
//Default Y axis units: [free text one line field]
//
//Z Axis Label Title (if any): [free text one line field]
//
//Default Z axis units: [free text one line field]
//
//Data Set Legend Series Title(s): [free text one line field]
//
//Chart Comment: [free text one line field]
//
//Display data point quantity after chart title: [free text one line field]
//
//Display mean X axis value after X axis label title: [free text one line field]
//
//Display standard deviation for the X axis values after X axis label title: [free text one line field]
//
//Display mean Y axis value after Y axis label title: [free text one line field]
//
//Display standard deviation for the Y axis values after Y axis label title: [free text one line field]
//
//Display data point quantity in legend’s series title: [free text one line field]
//
//Display mean series value in legend’s series title: [free text one line field]
//
//Display standard deviation for the series values in legend’s series title: [free text one line field]
//
//“Default Image Width in Pixels:” [one line free text]
//
//”Default Image Height In Pixels:” [one line free text]
//
//“Hide Negative X Axis Values By Default”: [one line free text]
//
//“Hide Negative Y Axis Values By Default”: [one line free text]
//
//“Hide Negative Z Axis Values By Default”: [one line free text]
//
//“Hide Zero X Axis Values By Default”: [one line free text]
//
//“Hide Zero Y Axis Values By Default”: [one line free text]
//
//“Hide Zero Z Axis Values By Default”: [one line free text]
//
//“Applicable Dashboard Chart Filter Fields”: [multi-select with the flat list of all “Dashboard Chart Filter Fields” from step 4 above]
//
//“Display Order of Applicable Primary Dashboard Chart Filter Fields (for example, {“chartFilterID1”:”10”, “chartFilterID2”:”30”, “chartFilterID3”:”20”}):” [3-line free-text field]
//
//“Applicable Dashboard Data Table Column Titles”: [free text, three-line field]
//
//“Display Order of Data Table Titles (for example, {“DataTableColumnTitle01”:”10”, “DataTableColumnTitle02”:”30”, “DataTableColumnTitle03”:”20”}):” [3-line free-text field]
//
//“Display Order of Applicable Secondary Dashboard Chart Filter Fields (for example, {“chartFilterID1”:”10”, “chartFilterID2”:”30”, “chartFilterID3”:”20”}):” [3-line free-text field]
//
//“Default Values for Applicable Secondary Dashboard Chart Filter Fields (for example, {“chartFilterID1”:”01/2021”, “chartFilterID2”:”Female”, “chartFilterID3”:”AP, CP”}):” [3-line free-text field]
//
//“Additional Chart Settings:” [3-line free-text field]
//
//“Dashboard Data Source:” [single-select with Dashboard Data Source from step 6 above]
//
//“Dashboard Update Frequency:” [single-select with Dashboard Update Frequency from step 5 above]
//
//“Path to pre-generated default image for this chart:” [free text one line field]
//
//“Timestamp for the pre-generated default image for this chart:” [free text one line field]
//
//“Favorited by the following users”: [multi-select with all users]
//
//“Requested by:” [multi-select with all users]
//
//“Requested on:” [timestamp]
    

}
