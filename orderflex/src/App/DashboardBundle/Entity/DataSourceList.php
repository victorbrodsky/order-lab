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
 * Dashboard Data Source
 *
 * @ORM\Entity
 * @ORM\Table(name="dashboard_datasourcelist")
 */
class DataSourceList extends ListAbstract
{

    /**
     * @ORM\OneToMany(targetEntity="DataSourceList", mappedBy="original", cascade={"persist"})
     **/
    protected $synonyms;

    /**
     * @ORM\ManyToOne(targetEntity="DataSourceList", inversedBy="synonyms", cascade={"persist"})
     * @ORM\JoinColumn(name="original_id", referencedColumnName="id", nullable=true)
     **/
    protected $original;



    /**
     * a) Update Frequency
     *
     * @ORM\ManyToOne(targetEntity="UpdateFrequencyList")
     * @ORM\JoinColumn(name="updateFrequency_id", referencedColumnName="id", nullable=true)
     */
    private $updateFrequency;

    //We should have a single, centralize access control in ChartList
    //These access/deny are specified in the ChartList
//    /**
//     * b) "Accessible to users with the following roles:" [multi-select with roles]
//     *
//     * @ORM\ManyToMany(targetEntity="Roles", cascade={"persist"})
//     * @ORM\JoinTable(name="dashboard_datasource_accessrole",
//     *      joinColumns={@ORM\JoinColumn(name="datasource_id", referencedColumnName="id")},
//     *      inverseJoinColumns={@ORM\JoinColumn(name="role_id", referencedColumnName="id")}
//     *      )
//     **/
//    private $accessRoles;
//
//    /**
//     * c) “Deny access to users with the following roles:” [multi-select with roles]
//     *
//     * @ORM\ManyToMany(targetEntity="Roles", cascade={"persist"})
//     * @ORM\JoinTable(name="dashboard_datasource_denyrole",
//     *      joinColumns={@ORM\JoinColumn(name="datasource_id", referencedColumnName="id")},
//     *      inverseJoinColumns={@ORM\JoinColumn(name="role_id", referencedColumnName="id")}
//     *      )
//     **/
//    private $denyRoles;
//
//    /**
//     * d) “Deny access to the following users:” [multi-select with all users]
//     *
//     * @ORM\ManyToMany(targetEntity="User", cascade={"persist"})
//     * @ORM\JoinTable(name="dashboard_datasource_denyuser",
//     *      joinColumns={@ORM\JoinColumn(name="datasource_id", referencedColumnName="id")},
//     *      inverseJoinColumns={@ORM\JoinColumn(name="user_id", referencedColumnName="id")}
//     *      )
//     **/
//    private $denyUsers;
//
//    /**
//     * e) “Data can be downloaded by users with the following roles:” [multi-select with roles].
//     *
//     * @ORM\ManyToMany(targetEntity="Roles", cascade={"persist"})
//     * @ORM\JoinTable(name="dashboard_datasource_downloadrole",
//     *      joinColumns={@ORM\JoinColumn(name="datasource_id", referencedColumnName="id")},
//     *      inverseJoinColumns={@ORM\JoinColumn(name="role_id", referencedColumnName="id")}
//     *      )
//     **/
//    private $downloadRoles;

}
