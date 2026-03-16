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

namespace App\FellAppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Table(name: 'fellapp_hubConfig')]
#[ORM\Entity]
class HubConfig {
    
    /**
     * @var integer
     */
    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    private $id;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private $updateDate;

    #[ORM\ManyToOne(targetEntity: 'App\UserdirectoryBundle\Entity\User')]
    #[ORM\JoinColumn(referencedColumnName: 'id', nullable: true)]
    private $updatedBy;

    //#[ORM\Column(type: 'boolean', nullable: true)]
    //private $acceptingSubmission;


    //#[ORM\Column(type: 'string', nullable: true)]
    //private $boardCertificationNote;


    //(1) "URL of the fellowship application page hosted by Google" -
    // set it by default to the value "https://wcmc.pathologysystems.org/fellowship-application"

    //(2) "URL of the fellowship application page hosted by the public tandem hub server tenant instance" -
    // set it by default to the value "https://view.online/fellowship-applications/apply"
    #[ORM\Column(type: 'string', nullable: true)]
    private $hubFellappFormUrl;

    //(3) "URL of the API endpoint hosted by Google to download fellowship applications" -
    // set it by default to the value "[]" (whatever is the URL site uses to reach out to Google API to send the request to download the fellowship applications)

    //(4) "URL of the API endpoint hosted by the public tandem hub server tenant instance" -
    // set it by default to the value "https://view.online/fellowship-applications/download-application-data"
    #[ORM\Column(type: 'string', nullable: true)]
    private $hubServerApiUrl;

    //(5) "URL of the recommendation letter upload page hosted by Google (to append hash ID)" -
    // set it by default to the value "[]" (whatever is the URL the WCM site uses to first append the unique HASH value + Applicant data and then email to the recommendation letter writers)

    //(6) "URL of the recommendation letter upload page hosted by the public tandem hub server tenant instance (to append hash ID)" -
    // set it by default to the value "https://view.online/fellowship-applications/submit-a-letter-of-recommendation"
    #[ORM\Column(type: 'string', nullable: true)]
    private $hubRecletterFormUrl;
    


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

    /**
     * @return mixed
     */
    public function getUpdateDate()
    {
        return $this->updateDate;
    }

    /**
     * @param mixed $updateDate
     */
    public function setUpdateDate($updateDate)
    {
        $this->updateDate = $updateDate;
    }

    /**
     * @return mixed
     */
    public function getUpdatedBy()
    {
        return $this->updatedBy;
    }

    /**
     * @param mixed $updatedBy
     */
    public function setUpdatedBy($updatedBy)
    {
        $this->updatedBy = $updatedBy;
    }

    /**
     * @return mixed
     */
    public function getHubFellappFormUrl()
    {
        return $this->hubFellappFormUrl;
    }

    /**
     * @param mixed $hubFellappFormUrl
     */
    public function setHubFellappFormUrl($hubFellappFormUrl)
    {
        $this->hubFellappFormUrl = $hubFellappFormUrl;
    }

    /**
     * @return mixed
     */
    public function getHubServerApiUrl()
    {
        return $this->hubServerApiUrl;
    }

    /**
     * @param mixed $hubServerApiUrl
     */
    public function setHubServerApiUrl($hubServerApiUrl)
    {
        $this->hubServerApiUrl = $hubServerApiUrl;
    }

    /**
     * @return mixed
     */
    public function getHubRecletterFormUrl()
    {
        return $this->hubRecletterFormUrl;
    }

    /**
     * @param mixed $hubRecletterFormUrl
     */
    public function setHubRecletterFormUrl($hubRecletterFormUrl)
    {
        $this->hubRecletterFormUrl = $hubRecletterFormUrl;
    }



    public function __toString() {
        return "Hub Config getHubFellappFormUrl=".$this->getHubFellappFormUrl()."<br>";
    }
    
    
}

?>
