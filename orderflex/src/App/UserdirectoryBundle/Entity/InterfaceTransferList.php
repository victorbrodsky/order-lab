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

//The host server (intranet) will send data to the remote (public) server.
//First, create immediate upload the data on change.
// Example: https://stackoverflow.com/questions/58709888/php-curl-how-to-safely-send-data-to-another-server-using-curl
//Second, it is possible to create a table in DB storing ID's of the updates antibodies.
// Then, the cron job will upload these antibodies to the remote server.

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

    #[ORM\Column(type: 'string', nullable: true)]
    private $sshUsername;

    //Password or key
    #[ORM\Column(type: 'text', nullable: true)]
    private $sshPassword;

    //Absolute path to the remote server certificate for curl:
    //https://unitstep.net/blog/2009/05/05/using-curl-in-php-to-access-https-ssltls-protected-sites/
    //1) visit remote site view.online
    //2) view certificate => export/download as pem or crt
    //3) use this remote certificate in CURLOPT_CAINFO (get from $remoteCertificate)
    #[ORM\Column(type: 'text', nullable: true)]
    private $remoteCertificate;




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

    /**
     * @return mixed
     */
    public function getSshUsername()
    {
        return $this->sshUsername;
    }

    /**
     * @param mixed $sshUsername
     */
    public function setSshUsername($sshUsername)
    {
        $this->sshUsername = $sshUsername;
    }

    /**
     * @return mixed
     */
    public function getSshPassword()
    {
        return $this->sshPassword;
    }

    /**
     * @param mixed $sshPassword
     */
    public function setSshPassword($sshPassword)
    {
        $this->sshPassword = $sshPassword;
    }

    /**
     * @return mixed
     */
    public function getRemoteCertificate()
    {
        return $this->remoteCertificate;
    }

    /**
     * @param mixed $remoteCertificate
     */
    public function setRemoteCertificate($remoteCertificate)
    {
        $this->remoteCertificate = $remoteCertificate;
    }

    //Since $strServer might be 'view.online/c/wcm/pathology' for multitenancy, get only view.online
    public function getTransferSourceBase()
    {
        $serverName = $this->getTransferSource();
        if( !$serverName ) {
            return NULL;
        }

        $exploded_server = explode('/', $serverName);
        if( count($exploded_server) > 0 ) {
            $serverName = $exploded_server[0];
        }
        return $serverName;
    }

}
