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

use App\UserdirectoryBundle\Entity\ListAbstract;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
//use Symfony\Component\Validator\Constraints as Assert;


//Similar to FellowshipSubspecialty, but used only on the /apply page
#[ORM\Table(name: 'fellapp_globalspecialty')]
#[ORM\Entity]
class GlobalFellowshipSpecialty extends ListAbstract
{

    #[ORM\OneToMany(targetEntity: 'GlobalFellowshipSpecialty', mappedBy: 'original', cascade: ['persist'])]
    protected $synonyms;

    #[ORM\ManyToOne(targetEntity: 'GlobalFellowshipSpecialty', inversedBy: 'synonyms', cascade: ['persist'])]
    #[ORM\JoinColumn(name: 'original_id', referencedColumnName: 'id', nullable: true)]
    protected $original;

    //One institution can have many fellowship specialty
    #[ORM\ManyToOne(targetEntity: 'App\UserdirectoryBundle\Entity\Institution')]
    #[ORM\JoinColumn(name: 'institution_id', referencedColumnName: 'id', nullable: true)]
    private $institution;

    /**
     * Application season start date
     */
    #[ORM\Column(type: 'date', nullable: true)]
    private $seasonYearStart;

    /**
     * Application season end date
     */
    #[ORM\Column(type: 'date', nullable: true)]
    private $seasonYearEnd;

    //API key expected in URL to enable remote connection: [l4kn5lk2nl23iron2i3n2l3inl23kn4o2i3j42fowiefw940]
    #[ORM\Column(type: 'string', nullable: true)]
    private $apiConnectionKey;

    //Key(s) for application import: [2lk24n2k3n4o95n4o86n4o2i3noifinof] (accept multiple new values via Select2)
    //    $entity->setApiImportKeys([
    //    '2lk24n2k3n4o95n4o86n4o2i3noifinof',
    //    'newkey1234567890',
    //    'anotherKey0987654321',
    //    ]);
    #[ORM\Column(type: 'json', nullable: true)]
    private array $apiImportKeys = [];


    /**
     * @param mixed $institution
     */
    public function setInstitution($institution)
    {
        $this->institution = $institution;
    }

    /**
     * @return mixed
     */
    public function getInstitution()
    {
        return $this->institution;
    }

    /**
     * @return mixed
     */
    public function getSeasonYearStart()
    {
        return $this->seasonYearStart;
    }

    /**
     * @param mixed $seasonYearStart
     */
    public function setSeasonYearStart($seasonYearStart)
    {
        $this->seasonYearStart = $seasonYearStart;
    }

    /**
     * @return mixed
     */
    public function getSeasonYearEnd()
    {
        return $this->seasonYearEnd;
    }

    /**
     * @param mixed $seasonYearEnd
     */
    public function setSeasonYearEnd($seasonYearEnd)
    {
        $this->seasonYearEnd = $seasonYearEnd;
    }

    /**
     * @return mixed
     */
    public function getApiConnectionKey()
    {
        return $this->apiConnectionKey;
    }

    /**
     * @param mixed $apiConnectionKey
     */
    public function setApiConnectionKey($apiConnectionKey)
    {
        $this->apiConnectionKey = $apiConnectionKey;
    }


    //$this->apiImportKeys = ['abc123', 'def456', 'ghi789'];
    public function getApiImportKeys(): array
    {
        return $this->apiImportKeys;
    }
    public function setApiImportKeys(array $apiImportKeys): self
    {
        $this->apiImportKeys = $apiImportKeys;
        return $this;
    }
    public function addApiImportKey(string $key): self
    {
        if (!in_array($key, $this->apiImportKeys, true)) {
            $this->apiImportKeys[] = $key;
        }
        return $this;
    }
    //$this->removeApiImportKey('def456');
    public function removeApiImportKey(string $key): self
    {
        $this->apiImportKeys = array_filter(
            $this->apiImportKeys,
            fn($existingKey) => $existingKey !== $key
        );
        return $this;
    }


}