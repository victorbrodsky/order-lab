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

    //tenant url (tenant name) (similar to HostedUserGroupList)
    //#[ORM\ManyToOne(targetEntity: TenantUrlList::class, inversedBy: 'tenants')]
    //#[ORM\JoinColumn(name: 'tenanturl_id', referencedColumnName: 'id', nullable: true)]
    //private $tenantUrl;
    #[ORM\OneToOne(targetEntity: TenantUrlList::class, cascade: ['persist', 'remove'])]
    private $tenantUrl;


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

    //$systemDb (boolean) Use as a system DB to store multitenancy parameters
    #[ORM\Column(type: 'boolean', nullable: true)]
    private $systemDb;

    #[ORM\Column(type: 'boolean', nullable: true)]
    private $enabled;


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

    /**
     * @return mixed
     */
    public function getSystemDb()
    {
        return $this->systemDb;
    }

    /**
     * @param mixed $systemDb
     */
    public function setSystemDb($systemDb)
    {
        $this->systemDb = $systemDb;
    }

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




}