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
    protected $author;

    #[ORM\Column(type: 'datetime', nullable: true)]
    protected $createdate;

    #[ORM\ManyToOne(targetEntity: AuthServerNetworkList::class, inversedBy: 'hostedGroupHolders')]
    #[ORM\JoinColumn(name: 'servernetwork_id', referencedColumnName: 'id', onDelete: 'CASCADE', nullable: true)]
    private $serverNetwork;

    #[ORM\JoinTable(name: 'user_hostedgroupholder_hostedusergroup')]
    #[ORM\ManyToMany(targetEntity: HostedUserGroupList::class, mappedBy: 'hostedGroupHolders', cascade: ['persist'])]
    private $hostedUserGroups;

    //For this group holder add server parameters, footer parameters, etc


    public function __construct( $author=null ) {
        $this->setAuthor($author);
        $this->setCreatedate(new \DateTime());

        $this->hostedUserGroups = new ArrayCollection();
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
    public function getHostedUserGroups()
    {
        return $this->hostedUserGroups;
    }

    public function addHostedUserGroup( $item )
    {
        if( !$this->hostedUserGroups->contains($item) ) {
            $this->hostedUserGroups->add($item);
            $item->addHostedGroupHolder($this);
        }

        return $this;
    }
    public function removeHostedUserGroup($item)
    {
        if( $this->hostedUserGroups->contains($item) ) {
            $this->hostedUserGroups->removeElement($item);
            $item->removeHostedGroupHolder($this);
        }

        return $this;
    }




//    public function getDocumentContainers()
//    {
//        return $this->documentContainers;
//    }
//    public function addDocumentContainer($item)
//    {
//        if( $item && !$this->documentContainers->contains($item) ) {
//            $this->documentContainers->add($item);
//            $item->setAttachmentContainer($this);
//        }
//    }
//    public function removeDocumentContainer($item)
//    {
//        $this->documentContainers->removeElement($item);
//    }
//
//    public function isEmpty() {
//        foreach( $this->getDocumentContainers() as $documentContainer ) {
//            if( count($documentContainer->getDocuments()) > 0 ) {
//                return false;
//            }
//        }
//        return true;
//    }

    public function __toString() {
        return "HostedGroupHolder:"."hostedUserGroups=".count($this->getHostedUserGroups())."<br>";
    }

} 