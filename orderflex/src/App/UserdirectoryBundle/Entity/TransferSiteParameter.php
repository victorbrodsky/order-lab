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
use Symfony\Component\Validator\Constraints as Assert;

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

#[ORM\Table(name: 'user_transfersiteparameter')]
#[ORM\Entity]
class TransferSiteParameter
{

    /**
     * @var integer
     */
    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    private $id;
    
//    //Add a new platform list manager list titled “Data types for interface transfer”
//    //with one value on the list (for now) titled “Antibody List”.
//    #[ORM\OneToMany(targetEntity: 'App\UserdirectoryBundle\Entity\TransferDataType')]
//    private $transferDataTypes;

    


    public function __construct() {
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }



    public function __toString() {
        return "Transfer Site Parameter";
    }

}