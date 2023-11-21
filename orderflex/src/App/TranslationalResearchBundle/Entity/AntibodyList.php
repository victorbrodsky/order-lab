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

namespace App\TranslationalResearchBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

use App\UserdirectoryBundle\Entity\ListAbstract;

#[ORM\Table(name: 'transres_antibodyList')]
#[ORM\Entity]
class AntibodyList extends ListAbstract
{
    #[ORM\OneToMany(targetEntity: 'AntibodyList', mappedBy: 'original', cascade: ['persist'])]
    protected $synonyms;

    #[ORM\ManyToOne(targetEntity: 'AntibodyList', inversedBy: 'synonyms', cascade: ['persist'])]
    #[ORM\JoinColumn(name: 'original_id', referencedColumnName: 'id', nullable: true)]
    protected $original;

    #[ORM\Column(type: 'string', nullable: true)]
    protected $type;

    /**
     * Indicates the order in the list
     */
    #[ORM\Column(type: 'integer', nullable: true)]
    protected $orderinlist;

//`category` varchar(32) NOT NULL,
    //`name` varchar(255) NOT NULL,
    //`altname` varchar(255) DEFAULT NULL,
    //`company` varchar(255) NOT NULL,
    //`catalog` varchar(255) NOT NULL,
    //`lot` varchar(255) NOT NULL,
    //`igconcentration` varchar(255) NOT NULL,
    //`clone` varchar(255) NOT NULL,
    //`host` varchar(255) NOT NULL,
    //`reactivity` varchar(255) NOT NULL,
    //`control` varchar(255) NOT NULL,
    //`protocol` varchar(255) NOT NULL,
    //`retrieval` varchar(255) NOT NULL,
    //`dilution` varchar(255) NOT NULL,
    //`storage` varchar(255) NOT NULL,
    //`comment` varchar(6255) NOT NULL,
    //`datasheet` varchar(6255) NOT NULL,
    //`pdf` varchar(255) NOT NULL,
    /**
     * @var string
     */
    #[ORM\Column(type: 'string', nullable: true)]
    private $category;

    /**
     * @var string
     */
    #[ORM\Column(type: 'string', nullable: true)]
    private $altname;

    /**
     * @var string
     */
    #[ORM\Column(type: 'string', nullable: true)]
    private $company;

    /**
     * @var string
     */
    #[ORM\Column(type: 'string', nullable: true)]
    private $catalog;

    /**
     * @var string
     */
    #[ORM\Column(type: 'string', nullable: true)]
    private $lot;

    /**
     * @var string
     */
    #[ORM\Column(type: 'string', nullable: true)]
    private $igconcentration;

    /**
     * @var string
     */
    #[ORM\Column(type: 'string', nullable: true)]
    private $clone;

    /**
     * @var string
     */
    #[ORM\Column(type: 'string', nullable: true)]
    private $host;

    /**
     * @var string
     */
    #[ORM\Column(type: 'string', nullable: true)]
    private $reactivity;

    /**
     * @var string
     */
    #[ORM\Column(type: 'string', nullable: true)]
    private $control;

    /**
     * @var string
     */
    #[ORM\Column(type: 'string', nullable: true)]
    private $protocol;

    /**
     * @var string
     */
    #[ORM\Column(type: 'string', nullable: true)]
    private $retrieval;

    /**
     * @var string
     */
    #[ORM\Column(type: 'string', nullable: true)]
    private $dilution;

    /**
     * @var string
     */
    #[ORM\Column(type: 'string', nullable: true)]
    private $storage;

    /**
     * @var string
     */
    #[ORM\Column(type: 'text', nullable: true)]
    private $comment;

    /**
     * @var string
     */
    #[ORM\Column(type: 'text', nullable: true)]
    private $datasheet;

    /**
     * @var string
     */
    #[ORM\Column(type: 'string', nullable: true)]
    private $pdf;

    #[ORM\JoinTable(name: 'transres_antibody_document')]
    #[ORM\JoinColumn(name: 'request_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    #[ORM\InverseJoinColumn(name: 'document_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    #[ORM\ManyToMany(targetEntity: 'App\UserdirectoryBundle\Entity\Document', cascade: ['persist', 'remove'])]
    #[ORM\OrderBy(['createdate' => 'DESC'])]
    private $documents;

    /**
     * @var string
     */
    #[ORM\Column(type: 'text', nullable: true)]
    private $comment1;

    /**
     * @var string
     */
    #[ORM\Column(type: 'text', nullable: true)]
    private $comment2;

    /**
     * @var integer
     */
    #[ORM\Column(name: 'exportId', type: 'integer', nullable: true)]
    private $exportId;


    /**
     * Inventory Stock
     */
    #[ORM\Column(type: 'integer', nullable: true)]
    private $inventory;

    /**
     * Unit Price
     */
    #[ORM\Column(type: 'decimal', precision: 15, scale: 2, nullable: true)]
    private $unitPrice;

    /**
     * Tissue Type
     *
     * @var string
     */
    #[ORM\Column(type: 'string', nullable: true)]
    private $tissueType;

    /**
     * Similar to http://store.ihcworld.com/abi-1-ihc-antibody/
     */
    #[ORM\OneToMany(targetEntity: 'VisualInfo', mappedBy: 'antibody', cascade: ['persist', 'remove'])]
    #[ORM\OrderBy(['orderinlist' => 'ASC', 'updatedate' => 'DESC'])]
    private $visualInfos;

    //Populate and replace $category by $categoryTags
    #[ORM\JoinTable(name: 'transres_antibody_categorytag')]
    #[ORM\ManyToMany(targetEntity: AntibodyCategoryTagList::class, inversedBy: 'antibodies')]
    private $categoryTags;

    #[ORM\Column(type: 'boolean', nullable: true)]
    private $openToPublic;

    /////// “Associated Antibodies” multi-select Select2 ///////
    // https://www.doctrine-project.org/projects/doctrine-orm/en/2.16/reference/association-mapping.html#many-to-many-self-referencing
    // https://stackoverflow.com/questions/21244816/doctrines-many-to-many-self-referencing-and-reciprocity
//    /**
//     * Many Antibodies have Many Antibodies. (associatesWithMe, similar to friendsWithMe)
//     */
//    #[ManyToMany(targetEntity: AntibodyList::class, mappedBy: 'myAssociates')]
//    private $associates;
//
//    /**
//     * Many Antibodies have many Antibodies (similar to myFriends).
//     */
//    #[JoinTable(name: 'transres_antibody_associate')]
//    #[JoinColumn(name: 'antibody_id', referencedColumnName: 'id')]
//    #[InverseJoinColumn(name: 'associate_id', referencedColumnName: 'id')]
//    #[ManyToMany(targetEntity: AntibodyList::class, inversedBy: 'associates')]
//    private $myAssociates;

    //, cascade: ['persist']
    #[ORM\JoinTable(name: 'transres_antibody_associate')]
    #[ORM\JoinColumn(name: 'antibody_id', referencedColumnName: 'id')]
    #[ORM\InverseJoinColumn(name: 'associate_id', referencedColumnName: 'id')]
    #[ORM\ManyToMany(targetEntity: AntibodyList::class)]
    private $associates;
    /////// EOF “Associated Antibodies” multi-select Select2 ///////




    public function __construct($author=null) {

        parent::__construct($author);

        $this->documents = new ArrayCollection();
        $this->visualInfos = new ArrayCollection();
        $this->categoryTags = new ArrayCollection();

        $this->associates = new ArrayCollection();
        //$this->myAssociates = new ArrayCollection();
    }


    /**
     * @return mixed
     */
    public function getDocuments()
    {
        return $this->documents;
    }
    public function addDocument($item)
    {
        if( $item && !$this->documents->contains($item) ) {
            $this->documents->add($item);
            $item->createUseObject($this);
        }
        return $this;
    }
    public function removeDocument($item)
    {
        $this->documents->removeElement($item);
        $item->clearUseObject();
    }

    /**
     * @return string
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * @param string $category
     */
    public function setCategory($category)
    {
        $this->category = $category;
    }

    /**
     * @return string
     */
    public function getAltname()
    {
        return $this->altname;
    }

    /**
     * @param string $altname
     */
    public function setAltname($altname)
    {
        $this->altname = $altname;
    }

    /**
     * @return string
     */
    public function getCompany()
    {
        return $this->company;
    }

    /**
     * @param string $company
     */
    public function setCompany($company)
    {
        $this->company = $company;
    }

    /**
     * @return string
     */
    public function getCatalog()
    {
        return $this->catalog;
    }

    /**
     * @param string $catalog
     */
    public function setCatalog($catalog)
    {
        $this->catalog = $catalog;
    }

    /**
     * @return string
     */
    public function getLot()
    {
        return $this->lot;
    }

    /**
     * @param string $lot
     */
    public function setLot($lot)
    {
        $this->lot = $lot;
    }

    /**
     * @return string
     */
    public function getIgconcentration()
    {
        return $this->igconcentration;
    }

    /**
     * @param string $igconcentration
     */
    public function setIgconcentration($igconcentration)
    {
        $this->igconcentration = $igconcentration;
    }

    /**
     * @return string
     */
    public function getClone()
    {
        return $this->clone;
    }

    /**
     * @param string $clone
     */
    public function setClone($clone)
    {
        $this->clone = $clone;
    }

    /**
     * @return string
     */
    public function getHost()
    {
        return $this->host;
    }

    /**
     * @param string $host
     */
    public function setHost($host)
    {
        $this->host = $host;
    }

    /**
     * @return string
     */
    public function getReactivity()
    {
        return $this->reactivity;
    }

    /**
     * @param string $reactivity
     */
    public function setReactivity($reactivity)
    {
        $this->reactivity = $reactivity;
    }

    /**
     * @return string
     */
    public function getControl()
    {
        return $this->control;
    }

    /**
     * @param string $control
     */
    public function setControl($control)
    {
        $this->control = $control;
    }

    /**
     * @return string
     */
    public function getProtocol()
    {
        return $this->protocol;
    }

    /**
     * @param string $protocol
     */
    public function setProtocol($protocol)
    {
        $this->protocol = $protocol;
    }

    /**
     * @return string
     */
    public function getRetrieval()
    {
        return $this->retrieval;
    }

    /**
     * @param string $retrieval
     */
    public function setRetrieval($retrieval)
    {
        $this->retrieval = $retrieval;
    }

    /**
     * @return string
     */
    public function getDilution()
    {
        return $this->dilution;
    }

    /**
     * @param string $dilution
     */
    public function setDilution($dilution)
    {
        $this->dilution = $dilution;
    }

    /**
     * @return string
     */
    public function getStorage()
    {
        return $this->storage;
    }

    /**
     * @param string $storage
     */
    public function setStorage($storage)
    {
        $this->storage = $storage;
    }

    /**
     * @return string
     */
    public function getComment()
    {
        return $this->comment;
    }

    /**
     * @param string $comment
     */
    public function setComment($comment)
    {
        $this->comment = $comment;
    }

    /**
     * @return string
     */
    public function getDatasheet()
    {
        return $this->datasheet;
    }

    /**
     * @param string $datasheet
     */
    public function setDatasheet($datasheet)
    {
        $this->datasheet = $datasheet;
    }

    /**
     * @return string
     */
    public function getPdf()
    {
        return $this->pdf;
    }

    /**
     * @param string $pdf
     */
    public function setPdf($pdf)
    {
        $this->pdf = $pdf;
    }

    /**
     * @return string
     */
    public function getComment1()
    {
        return $this->comment1;
    }

    /**
     * @param string $comment1
     */
    public function setComment1($comment1)
    {
        $this->comment1 = $comment1;
    }

    /**
     * @return string
     */
    public function getComment2()
    {
        return $this->comment2;
    }

    /**
     * @param string $comment2
     */
    public function setComment2($comment2)
    {
        $this->comment2 = $comment2;
    }

    /**
     * @return int
     */
    public function getExportId()
    {
        return $this->exportId;
    }

    /**
     * @param int $exportId
     */
    public function setExportId($exportId)
    {
        $this->exportId = $exportId;
    }

    /**
     * @return mixed
     */
    public function getVisualInfos()
    {
        return $this->visualInfos;
    }
    public function addVisualInfo( $item )
    {
        if( !$item )
            return;

        if( !$this->visualInfos->contains($item) ) {
            $item->setAntibody($this);
            $this->visualInfos->add($item);
        }
    }
    public function removeVisualInfo($item)
    {
        $this->visualInfos->removeElement($item);
    }

    /**
     * @return mixed
     */
    public function getInventory()
    {
        return $this->inventory;
    }

    /**
     * @param mixed $inventory
     */
    public function setInventory($inventory)
    {
        $this->inventory = $inventory;
    }

    /**
     * @return mixed
     */
    public function getUnitPrice()
    {
        return $this->unitPrice;
    }

    /**
     * @param mixed $unitPrice
     */
    public function setUnitPrice($unitPrice)
    {
        $this->unitPrice = $unitPrice;
    }

    /**
     * @return mixed
     */
    public function getTissueType()
    {
        return $this->tissueType;
    }

    /**
     * @param mixed $tissueType
     */
    public function setTissueType($tissueType)
    {
        $this->tissueType = $tissueType;
    }

    public function getCategoryTags()
    {
        return $this->categoryTags;
    }
    public function addCategoryTag( $item )
    {
        if( !$this->categoryTags->contains($item) ) {
            $this->categoryTags->add($item);
        }

        return $this;
    }
    public function removeCategoryTag($item)
    {
        if( $this->categoryTags->contains($item) ) {
            $this->categoryTags->removeElement($item);
        }

        return $this;
    }

    /**
     * @return mixed
     */
    public function getOpenToPublic()
    {
        return $this->openToPublic;
    }

    /**
     * @param mixed $openToPublic
     */
    public function setOpenToPublic($openToPublic)
    {
        $this->openToPublic = $openToPublic;
    }

    /**
     * @return mixed
     */
    public function getAssociates()
    {
        return $this->associates;
    }
    public function setAssociates($items)
    {
        foreach( $items as $item ) {
            $this->addAssociates($item);
        }
    }
    public function addAssociate($item)
    {
        if( $item && !$this->associates->contains($item) ) {
            $this->associates->add($item);
            //exit("addAssociate");
            if( !$item->getAssociates()->contains($this) ) {
                $item->addAssociate($this);
            }
        }
        return $this;
    }
    public function removeAssociate($item)
    {
        $this->associates->removeElement($item);
        //exit("removeAssociate");
        //$item->removeAssociate($this);
        if( $item->getAssociates()->contains($this) ) {
            $item->removeAssociate($this);
        }
    }

//    /**
//     * @return mixed
//     */
//    public function getMyAssociates()
//    {
//        return $this->myAssociates;
//    }
//    public function addMyAssociate($item)
//    {
//        if( $item && !$this->myAssociates->contains($item) ) {
//            $this->myAssociates->add($item);
//            $item->addAssociate($this);
//        }
//        return $this;
//    }
//    public function removeMyAssociate($item)
//    {
//        $this->myAssociates->removeElement($item);
//        $item->removeAssociate($this);
//    }

    
    public function getAllComments($separator="\r\n") {
        $res = "";
        $comment = $this->getComment();
        $comment1 = $this->getComment1();
        $comment2 = $this->getComment2();
        if( $comment ) {
            $res = $comment;
        }
        if( $comment1 ) {
            $res = $res . $separator . "Additional Comment 1: " . $comment1;
        }
        if( $comment2 ) {
            $res = $res . $separator . "Additional Comment 2: " . $comment2;
        }
        return $res;
    }

//    public function __toString()
//    {
//        $company = $this->getCompany();
//        if( $company ) {
//            $company = " (".$this->getCompany().")";
//        }
//        return $this->getName().$company;
//    }
    //[Antibody ID]/[Category]: [Antibody Name] [Vendor]/[Category] ([Protocol]/[Antigen retrieval]/[Dilution])
    //[Antibody ID]/[Category]: [Antibody Name] [Vendor]/[Catalog]/[Clone] ([Protocol]/[Antigen retrieval]/[Dilution])
    //[Antibody ID]/[Category]: [Antibody Name] [(Alternative Name)] [Vendor]/[Catalog]/[Clone] ([Protocol]/[Antigen retrieval]/[Dilution])
    public function __toString()
    {
        $res = $this->getId();

        $category = $this->getCategory();
        if( $category ) {
            $res = $res . "/" . $category;
        }

        $res = $res . ":";

        //Antibody Name
        $name = $this->getName();
        if( $name ) {
            //$name = trim((string)$name);
            $res = $res . " " . $name;
        }

        //Antibody Name
        $altName = $this->getAltname();
        if( $altName ) {
            //$altName = trim((string)$altName);
            $res = $res . " (" . $altName . ")";
        }

        //Vendor/Category
        $vendor = $this->getCompany();
        if( $vendor ) {
            $res = $res . " " . $vendor;
        }

        //[Vendor]/[Catalog]/[Clone]
        $catalog = $this->getCatalog();
        if( $catalog ) {
            $res = $res . "/" . $catalog;
        }

        //[Vendor]/[Catalog]/[Clone]
        $clone = $this->getClone();
        if( $clone ) {
            $res = $res . "/" . $clone;
        }

        //Protocol
        $protocol = $this->getProtocol();
        if( $protocol ) {
            $res = $res . " " . $protocol;
        }
        //Antigen retrieval
        $retrieval = $this->getRetrieval();
        if( $retrieval ) {
            $res = $res . "/" . $retrieval;
        }
        //Dilution
        $dilution = $this->getDilution();
        if( $dilution ) {
            $res = $res . "/" . $dilution;
        }

        return $res;
    }

    public function listName() {
        //[ID] AntibodyTitle
        $res = $this->getId();

        $name = $this->getName();
        if( $name ) {
            $res = $res . " [" . $name . "]";
        }
        
        return $res;
    }

}