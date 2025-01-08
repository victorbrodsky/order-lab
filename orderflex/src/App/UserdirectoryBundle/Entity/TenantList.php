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

namespace App\UserdirectoryBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

#[ORM\Table(name: 'user_tenantlist')]
#[ORM\Entity]
class TenantList extends ListAbstract
{

    #[ORM\OneToMany(targetEntity: 'TenantList', mappedBy: 'original')]
    protected $synonyms;

    #[ORM\ManyToOne(targetEntity: 'TenantList', inversedBy: 'synonyms')]
    #[ORM\JoinColumn(name: 'original_id', referencedColumnName: 'id')]
    protected $original;


    #[ORM\ManyToOne(targetEntity: TenantManager::class, inversedBy: 'tenants')]
    #[ORM\JoinColumn(name: 'tenantmanager_id', referencedColumnName: 'id', onDelete: 'CASCADE', nullable: true)]
    private $tenantManager;

    //Name (that is what would be shown on the homepage list) - tenantId, i.e. 'tenantapp3'

    //tenant url (tenant name) (similar to HostedUserGroupList)
    //One url can belong to the multiple tenants, for example tenant1 and tenant2, when tenant 1 is active and tenent2 is inactive.
    //URL Slug (that will be used to construct the link to the tenant homepage - /c/wcm/pathology , etc)
    #[ORM\ManyToOne(targetEntity: TenantUrlList::class, inversedBy: 'tenants')]
    #[ORM\JoinColumn(name: 'tenanturl_id', referencedColumnName: 'id', nullable: true)]
    private $tenantUrl;
    //#[ORM\OneToOne(targetEntity: TenantUrlList::class, cascade: ['persist', 'remove'])]
    //private $tenantUrl;

    #[ORM\Column(type: 'string', nullable: true)]
    private $tenantPort;

    #[ORM\Column(type: 'string', nullable: true)]
    private $databaseHost;

    #[ORM\Column(type: 'string', nullable: true)]
    private $databasePort;

    #[ORM\Column(type: 'string', nullable: true)]
    private $databaseName;

    #[ORM\Column(type: 'string', nullable: true)]
    private $databaseUser;

    #[ORM\Column(type: 'string', nullable: true)]
    private $databasePassword;

//    //$systemDb (boolean) Use as a system DB to store multitenancy parameters
//    #[ORM\Column(type: 'boolean', nullable: true)]
//    private $systemDb;

    //Show on Homepage? (Yes/No, Boolean) if set to “No” do not show on the main homepage list
    #[ORM\Column(type: 'boolean', nullable: true)]
    private $showOnHomepage;

    //Active and accessible via Web GUI? (Yes/No, Boolean)
    #[ORM\Column(type: 'boolean', nullable: true)]
    private $enabled;


    //Parent (Hierarchical): Since the list of tenants is hierarchical it should stay hierarchical: the root tenant is /c , /c/wcm is an entry for the institution with parent of /c , /c/wcm/pathology and /c/wcm/psychiatry have /c/wcm as parent, etc
    //Database file name: ""
    //Path to the database file: ""

    //Platform Administrator Account User Name: "" (User object or string?)
    #[ORM\Column(type: 'string', nullable: true)]
    private $adminName;

    //Tenant Institution Title: [free text]
    #[ORM\Column(type: 'string', nullable: true)]
    private $institutionTitle;

    //Tenant Department Title: [free text]
    #[ORM\Column(type: 'string', nullable: true)]
    private $departmentTitle;

    //Billing Tenant Administrator Contact Name: [free text]
    #[ORM\Column(type: 'string', nullable: true)]
    private $billingAdminName;

    //Billing Tenant Administrator Contact Email: [free text]
    #[ORM\Column(type: 'string', nullable: true)]
    private $billingAdminEmail;

    //Operational Tenant Administrator Contact Name: [free text]
    #[ORM\Column(type: 'string', nullable: true)]
    private $operationalAdminName;

    //Operational Tenant Administrator Contact Email: [free text]
    #[ORM\Column(type: 'string', nullable: true)]
    private $operationalAdminEmail;

    //Not mapped: indicates if the tenant in DB matches with tenant data from the filesystem on the server
    private $matchSystem;

    //Primary tenant: show homepage differently
    //1) haproxy.cfg file must be modified to use '/' with backend of the particular tenant:
    // acl homepagemanager_url path_beg -i /
    // use_backend tenantapp1_backend if homepagemanager_url
    //2)restart haproxy: sudo systemctl restart haproxy
    #[ORM\Column(type: 'boolean', nullable: true)]
    private $primaryTenant;




    public function __construct($author=null) {
        parent::__construct($author);
    }




    /**
     * @return mixed
     */
    public function getTenantManager()
    {
        return $this->tenantManager;
    }

    /**
     * @param mixed $tenantManager
     */
    public function setTenantManager($tenantManager)
    {
        $this->tenantManager = $tenantManager;
    }

    /**
     * @return mixed
     */
    public function getTenantUrl()
    {
        return $this->tenantUrl;
    }

    /**
     * @param mixed $tenantUrl
     */
    public function setTenantUrl($tenantUrl)
    {
        $this->tenantUrl = $tenantUrl;
    }

    /**
     * @return mixed
     */
    public function getTenantPort()
    {
        return $this->tenantPort;
    }

    /**
     * @param mixed $tenantPort
     */
    public function setTenantPort($tenantPort)
    {
        $this->tenantPort = $tenantPort;
    }

    

    /**
     * @return mixed
     */
    public function getDatabaseHost()
    {
        return $this->databaseHost;
    }

    /**
     * @param mixed $databaseHost
     */
    public function setDatabaseHost($databaseHost)
    {
        $this->databaseHost = $databaseHost;
    }

    /**
     * @return mixed
     */
    public function getDatabasePort()
    {
        return $this->databasePort;
    }

    /**
     * @param mixed $databasePort
     */
    public function setDatabasePort($databasePort)
    {
        $this->databasePort = $databasePort;
    }

    /**
     * @return mixed
     */
    public function getDatabaseName()
    {
        return $this->databaseName;
    }

    /**
     * @param mixed $databaseName
     */
    public function setDatabaseName($databaseName)
    {
        $this->databaseName = $databaseName;
    }

    /**
     * @return mixed
     */
    public function getDatabaseUser()
    {
        return $this->databaseUser;
    }

    /**
     * @param mixed $databaseUser
     */
    public function setDatabaseUser($databaseUser)
    {
        $this->databaseUser = $databaseUser;
    }

    /**
     * @return mixed
     */
    public function getDatabasePassword()
    {
        return $this->databasePassword;
    }

    /**
     * @param mixed $databasePassword
     */
    public function setDatabasePassword($databasePassword)
    {
        $this->databasePassword = $databasePassword;
    }

//    /**
//     * @return mixed
//     */
//    public function getSystemDb()
//    {
//        return $this->systemDb;
//    }
//
//    /**
//     * @param mixed $systemDb
//     */
//    public function setSystemDb($systemDb)
//    {
//        $this->systemDb = $systemDb;
//    }

    /**
     * @return mixed
     */
    public function getEnabled()
    {
        return $this->enabled;
    }

    /**
     * @param mixed $enabled
     */
    public function setEnabled($enabled)
    {
        $this->enabled = $enabled;
    }

    /**
     * @return mixed
     */
    public function getShowOnHomepage()
    {
        return $this->showOnHomepage;
    }

    /**
     * @param mixed $showOnHomepage
     */
    public function setShowOnHomepage($showOnHomepage)
    {
        $this->showOnHomepage = $showOnHomepage;
    }

    /**
     * @return mixed
     */
    public function getAdminName()
    {
        return $this->adminName;
    }

    /**
     * @param mixed $adminName
     */
    public function setAdminName($adminName)
    {
        $this->adminName = $adminName;
    }

    /**
     * @return mixed
     */
    public function getInstitutionTitle()
    {
        return $this->institutionTitle;
    }

    /**
     * @param mixed $institutionTitle
     */
    public function setInstitutionTitle($institutionTitle)
    {
        $this->institutionTitle = $institutionTitle;
    }

    /**
     * @return mixed
     */
    public function getDepartmentTitle()
    {
        return $this->departmentTitle;
    }

    /**
     * @param mixed $departmentTitle
     */
    public function setDepartmentTitle($departmentTitle)
    {
        $this->departmentTitle = $departmentTitle;
    }

    /**
     * @return mixed
     */
    public function getBillingAdminName()
    {
        return $this->billingAdminName;
    }

    /**
     * @param mixed $billingAdminName
     */
    public function setBillingAdminName($billingAdminName)
    {
        $this->billingAdminName = $billingAdminName;
    }

    /**
     * @return mixed
     */
    public function getBillingAdminEmail()
    {
        return $this->billingAdminEmail;
    }

    /**
     * @param mixed $billingAdminEmail
     */
    public function setBillingAdminEmail($billingAdminEmail)
    {
        $this->billingAdminEmail = $billingAdminEmail;
    }

    /**
     * @return mixed
     */
    public function getOperationalAdminName()
    {
        return $this->operationalAdminName;
    }

    /**
     * @param mixed $operationalAdminName
     */
    public function setOperationalAdminName($operationalAdminName)
    {
        $this->operationalAdminName = $operationalAdminName;
    }

    /**
     * @return mixed
     */
    public function getOperationalAdminEmail()
    {
        return $this->operationalAdminEmail;
    }

    /**
     * @param mixed $operationalAdminEmail
     */
    public function setOperationalAdminEmail($operationalAdminEmail)
    {
        $this->operationalAdminEmail = $operationalAdminEmail;
    }

    /**
     * @return mixed
     */
    public function getMatchSystem()
    {
        return $this->matchSystem;
    }

    /**
     * @param mixed $matchSystem
     */
    public function setMatchSystem($matchSystem)
    {
        $this->matchSystem = $matchSystem;
    }

    /**
     * @return mixed
     */
    public function getPrimaryTenant()
    {
        return $this->primaryTenant;
    }

    /**
     * @param mixed $primaryTenant
     */
    public function setPrimaryTenant($primaryTenant)
    {
        $this->primaryTenant = $primaryTenant;
    }






}