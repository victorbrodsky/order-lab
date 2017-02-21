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
 * Created by JetBrains PhpStorm.
 * User: oli2002
 * Date: 9/10/13
 * Time: 5:46 PM
 * To change this template use File | Settings | File Templates.
 */

namespace Oleg\OrderformBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

use Oleg\UserdirectoryBundle\Entity\Document;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\HttpFoundation\File\UploadedFile;


/**
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 * @ORM\Table(name="scan_partPaper")
 */
class PartPaper extends PartArrayFieldAbstract
{

    /**
     * @ORM\ManyToOne(targetEntity="Part", inversedBy="paper", cascade={"persist"})
     * @ORM\JoinColumn(name="part_id", referencedColumnName="id", nullable=true, onDelete="CASCADE")
     */
    protected $part;

    /**
     * @ORM\ManyToMany(targetEntity="Oleg\UserdirectoryBundle\Entity\Document")
     * @ORM\JoinTable(name="scan_partpaper_document",
     *      joinColumns={@ORM\JoinColumn(name="partpaper_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="document_id", referencedColumnName="id")}
     *      )
     **/
    protected $documents;


    public function __construct( $status = 'valid', $provider = null, $source = null ) {
        parent::__construct($status,$provider,$source);
        $this->documents = new ArrayCollection();
    }


    /**
     * Add document
     *
     * @param \Oleg\OrderformBundle\Entity\Document $document
     * @return PartPaper
     */
    public function addDocument($document)
    {
        if( $document == null ) {
            $document = new Document($this->getProvider());
        }

        if( !$this->documents->contains($document) ) {
            $this->documents->add($document);
            $document->createUseObject($this);
        }

        return $this;
    }
    /**
     * Remove document
     *
     * @param \Oleg\OrderformBundle\Entity\Document $document
     */
    public function removeDocument($document)
    {
        $this->documents->removeElement($document);
        $document->clearUseObject();
    }

    /**
     * Get documents
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getDocuments()
    {
        return $this->documents;
    }

    public function setDocuments($documents)
    {
        $this->documents = new ArrayCollection();
        foreach( $documents as $document ) {
            $this->addDocument($document);
        }
    }

    public function __toString() {
        return "Paper: Documents count=".count($this->getDocuments());
    }


    /**
     * @param mixed $field
     */
    public function setField($field=null)
    {
        //
    }

    /**
     * @return mixed
     */
    public function getField()
    {
        return null;
    }



}