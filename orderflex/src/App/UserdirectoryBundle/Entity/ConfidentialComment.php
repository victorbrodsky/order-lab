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

#[ORM\Table(name: 'user_confidentialComment')]
#[ORM\Entity]
class ConfidentialComment extends BaseComment
{

    #[ORM\ManyToOne(targetEntity: 'User', inversedBy: 'confidentialComments')]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    protected $user;

    #[ORM\JoinTable(name: 'user_confcomm_document')]
    #[ORM\JoinColumn(name: 'comm_id', referencedColumnName: 'id')]
    #[ORM\InverseJoinColumn(name: 'document_id', referencedColumnName: 'id', unique: true)]
    #[ORM\ManyToMany(targetEntity: 'Document')]
    protected $documents;

    public function __construct($author=null) {
        parent::__construct($author);

        $this->setType(self::TYPE_RESTRICTED);

        $this->documents = new ArrayCollection();
    }


    /**
     * @param mixed $user
     */
    public function setUser($user)
    {
        $this->user = $user;
    }

    /**
     * @return mixed
     */
    public function getUser()
    {
        return $this->user;
    }



    public function __toString() {
        return "Confidential Comment";
    }


}