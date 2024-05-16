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


//12 - Add a new platform list manager list titled “Data types for interface transfer”
// with one value on the list (for now) titled “Antibody List”.
// - Add a second new platform list manager list titled “Interface transfer statuses”
// with values of “Ready”, “Completed”, “Failed”.
// - Add a third new platform list manager list titled “Interface sources and destinations”
// with two values of “view.online” and “view.med.cornell.edu”.

// - In “Data Type for Transfer” show a multi-select Select2 field with values from
// “Data types for interface transfer” list in list manager.
// - In “Status” column show a single-select select2 menu with values from
// the “Interface transfer statuses” list in list manager.
// - In “Source” and “Destination” show a multi-select select2 menu with values from
// “Interface Sources and Destinations” platform list manager list.

//Cron job is running on host (intranet, internal wcm server) or remote (internet, outside server)? Who will initiate the transfer?
//If host is initiating the transfer: Observer pattern or
// Synchronous / Asynchronous, Client/Server / Peer-to-Peer
//https://stackoverflow.com/questions/413086/client-server-synchronization-pattern-algorithm
//https://www.geeksforgeeks.org/software-design-patterns/
//https://www.geeksforgeeks.org/observer-pattern-set-1-introduction/

#[ORM\Table(name: 'user_interfacetransferlist')]
#[ORM\Entity]
class InterfaceTransferList extends ListAbstract
{

    #[ORM\OneToMany(targetEntity: 'InterfaceTransferList', mappedBy: 'original')]
    protected $synonyms;

    #[ORM\ManyToOne(targetEntity: 'InterfaceTransferList', inversedBy: 'synonyms')]
    #[ORM\JoinColumn(name: 'original_id', referencedColumnName: 'id')]
    protected $original;

    //Data types for interface transfer => Name of this list

    //Status list: “Ready”, “Completed”, “Failed”
    #[ORM\ManyToOne(targetEntity: 'App\UserdirectoryBundle\Entity\TransferStatusList')]
    #[ORM\JoinColumn(name: 'transferstatus_id', referencedColumnName: 'id')]
    private $transferStatus;

    //Interface sources and destinations
    #[ORM\Column(type: 'string', nullable: true)]
    private $transferSource;

    #[ORM\Column(type: 'string', nullable: true)]
    private $transferDestination;


    
    /**
     * @return mixed
     */
    public function getTransferStatus()
    {
        return $this->transferStatus;
    }

    /**
     * @param mixed $transferStatus
     */
    public function setTransferStatus($transferStatus)
    {
        $this->transferStatus = $transferStatus;
    }

    /**
     * @return mixed
     */
    public function getTransferSource()
    {
        return $this->transferSource;
    }

    /**
     * @param mixed $transferSource
     */
    public function setTransferSource($transferSource)
    {
        $this->transferSource = $transferSource;
    }

    /**
     * @return mixed
     */
    public function getTransferDestination()
    {
        return $this->transferDestination;
    }

    /**
     * @param mixed $transferDestination
     */
    public function setTransferDestination($transferDestination)
    {
        $this->transferDestination = $transferDestination;
    }


}
