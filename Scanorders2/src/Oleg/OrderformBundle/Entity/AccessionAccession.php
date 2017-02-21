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

namespace Oleg\OrderformBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

use Oleg\OrderformBundle\Entity\AccessionArrayFieldAbstract;

/**
 * @ORM\Entity
 * @ORM\Table(name="scan_accessionaccession",
 *  indexes={
 *      @ORM\Index( name="accession_field_idx", columns={"field"} ),
 *      @ORM\Index( name="accession_keytype_idx", columns={"keytype_id"} )
 *  },
 *  uniqueConstraints={@ORM\UniqueConstraint(name="accession_unique", columns={"accession_id", "field", "keytype_id"})}
 * )
 */
class AccessionAccession extends AccessionArrayFieldAbstract
{

    /**
     * @ORM\ManyToOne(targetEntity="Accession", inversedBy="accession", cascade={"persist"})
     * @ORM\JoinColumn(name="accession_id", referencedColumnName="id", onDelete="CASCADE", nullable=true)
     */
    protected $accession;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $field;

    /**
     * original accession # enetered by user
     * @ORM\Column(type="string", nullable=true)
     */
    protected $original;

    /**
     * @ORM\ManyToOne(targetEntity="AccessionType", inversedBy="accessionaccession", cascade={"persist"})
     * @ORM\JoinColumn(name="keytype_id", referencedColumnName="id", nullable=true)
     */
    protected $keytype;

    /**
     * @param mixed $keytype
     */
    public function setKeytype($keytype)
    {
        $this->keytype = $keytype;
    }

    /**
     * @return mixed
     */
    public function getKeytype()
    {
        return $this->keytype;
    }

    /**
     * @param mixed $original
     */
    public function setOriginal($original)
    {
        $this->original = $original;
    }

    /**
     * @return mixed
     */
    public function getOriginal()
    {
        return $this->original;
    }


    public function obtainOptimalName() {
        if( $this->getKeytype() ) {
            $keyStr = $this->getKeytype()->getOptimalName() . ": " . $this->getField();
        } else {
            $keyStr = $this->getField();
        }

        return $keyStr;
    }

    public function obtainExtraKey()
    {
        $extra = array();
        $keytypeId = 1;
        if( $this->getKeytype() ) {
            $keytypeId = $this->getKeytype()->getId();
        }
        $extra['keytype'] = $keytypeId;//$this->getKeytype()->getId();
        return $extra;
    }

    public function setExtra($extraEntity)
    {
        $this->setKeytype($extraEntity);
    }

}