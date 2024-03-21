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

/**
 * Created by PhpStorm.
 * User: DevServer
 * Date: 3/26/15
 * Time: 4:00 PM
 */

namespace App\UserdirectoryBundle\Entity;


use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;


#[ORM\Table(name: 'user_hostedgroupholder')]
#[ORM\Entity]
class HostedGroupHolder {


    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    private $id;

    #[ORM\ManyToOne(targetEntity: 'App\UserdirectoryBundle\Entity\User')]
    #[ORM\JoinColumn(name: 'author', referencedColumnName: 'id')]
    private $author;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private $createdate;

    #[ORM\Column(type: 'integer', nullable: true)]
    private $orderinlist;

    #[ORM\ManyToOne(targetEntity: AuthServerNetworkList::class, inversedBy: 'hostedGroupHolders')]
    #[ORM\JoinColumn(name: 'servernetwork_id', referencedColumnName: 'id', onDelete: 'CASCADE', nullable: true)]
    private $serverNetwork;

    //tenant url
    #[ORM\ManyToOne(targetEntity: HostedUserGroupList::class, inversedBy: 'hostedGroupHolders')]
    #[ORM\JoinColumn(name: 'hostedusergroup_id', referencedColumnName: 'id', nullable: true)]
    private $hostedUserGroup;

    //For this group holder add server parameters, footer parameters, etc
    //Add tenant's custom parameters such as page footer, list of accessible pages etc.
    //Homepage and About Us Page Content
    //For example, if Server Role and Network Access field is set to "Internet (Hub)", the home page will look different
    //the the home page for Internet (Solo)

//    database_host: localhost
//    database_port: 5432
//    database_name: ScanOrder
//    #database_name: Tenant2
//    database_user: symfony
//    database_password: symfony

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



    //If $serviceDb is true:
    //Footer parameters for home page


    public function __construct( $author=null ) {
        $this->setAuthor($author);
        $this->setCreatedate(new \DateTime());
        //$this->setOrderinlist(-1);
        $this->setEnabled(false);
    }



    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getAuthor()
    {
        return $this->author;
    }

    /**
     * @param mixed $author
     */
    public function setAuthor($author)
    {
        $this->author = $author;
    }

    /**
     * @return mixed
     */
    public function getCreatedate()
    {
        return $this->createdate;
    }

    /**
     * @param mixed $createdate
     */
    public function setCreatedate($createdate)
    {
        $this->createdate = $createdate;
    }

    /**
     * @return mixed
     */
    public function getServerNetwork()
    {
        return $this->serverNetwork;
    }

    /**
     * @param mixed $serverNetwork
     */
    public function setServerNetwork($serverNetwork)
    {
        $this->serverNetwork = $serverNetwork;
    }

    /**
     * @return mixed
     */
    public function getHostedUserGroup()
    {
        return $this->hostedUserGroup;
    }

    /**
     * @param mixed $hostedUserGroup
     */
    public function setHostedUserGroup($hostedUserGroup)
    {
        $this->hostedUserGroup = $hostedUserGroup;
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
    public function getOrderinlist()
    {
        return $this->orderinlist;
    }

    /**
     * @param mixed $orderinlist
     */
    public function setOrderinlist($orderinlist)
    {
        $this->orderinlist = $orderinlist;
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



    public function __toString() {
        return "HostedGroupHolder:"."hostedUserGroup=".$this->getHostedUserGroup()."<br>";
    }

} 