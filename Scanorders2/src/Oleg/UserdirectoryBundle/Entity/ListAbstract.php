<?php
/**
 * Created by JetBrains PhpStorm.
 * User: oli2002
 * Date: 10/24/13
 * Time: 12:14 PM
 * To change this template use File | Settings | File Templates.
 */

namespace Oleg\UserdirectoryBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

use Symfony\Component\Validator\Constraints as Assert;


/**
 * @ORM\MappedSuperclass
 * @ORM\HasLifecycleCallbacks
 */
abstract class ListAbstract
{

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(name="name", type="string")
     * @Assert\NotBlank(message = "This value should not be blank.")
     */
    protected $name;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $abbreviation;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $shortname;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    protected $description;

    /**
     * @ORM\Column(type="string")
     * @Assert\NotBlank
     */
    protected $type;

    /**
     * @ORM\ManyToOne(targetEntity="Oleg\UserdirectoryBundle\Entity\User")
     * @ORM\JoinColumn(name="creator_id", referencedColumnName="id")
     * @Assert\NotBlank
     */
    protected $creator;

    /**
     * @var \DateTime
     * @ORM\Column(name="createdate", type="datetime")
     * @Assert\NotBlank
     */
    protected $createdate;

    /**
     * @ORM\ManyToOne(targetEntity="Oleg\UserdirectoryBundle\Entity\User")
     * @ORM\JoinColumn(name="updatedby_id", referencedColumnName="id",nullable=true)
     */
    protected $updatedby;

    /**
     * @var \DateTime
     * @ORM\Column(name="updatedon", type="datetime", nullable=true)
     */
    protected $updatedon;

    /**
     * Indicates the order in the list
     * @ORM\Column(type="integer")
     * @Assert\NotBlank
     */
    protected $orderinlist;

    /**
     * @var array
     * @ORM\Column(type="array", nullable=true)
     */
    protected $updateAuthorRoles = array();


    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $fulltitle;

    /**
     * Link to List ID
     * @ORM\Column(type="string", nullable=true)
     */
    protected $linkToListId;

    /**
     * for all items/rows "Object Type"="Dropdown Menu Value"
     * Platform List Manager List where all items should have "Object Type"="Form Field - Dropdown Menu"
     * @ORM\ManyToOne(targetEntity="Oleg\UserdirectoryBundle\Entity\ObjectTypeList")
     * @ORM\JoinColumn(name="objectType_id", referencedColumnName="id",nullable=true)
     */
    protected $objectType;

    /**
     * Linked Object ID
     * @ORM\Column(type="string", nullable=true)
     */
    protected $linkToObjectId;

    /**
     * @ORM\ManyToOne(targetEntity="Oleg\UserdirectoryBundle\Entity\ItemTypeList")
     * @ORM\JoinColumn(name="itemType_id", referencedColumnName="id",nullable=true)
     */
    protected $itemType;


//    /**
//     * @ORM\Column(type="string", nullable=true)
//     */
//    protected $fulltitleunique;
//
//    /**
//     * @ORM\Column(type="string", nullable=true)
//     */
//    protected $fulltitlemedium;
//
//    /**
//     * @ORM\Column(type="string", nullable=true)
//     */
//    protected $fulltitleshort;




    public function __construct( $creator = null ) {

        $this->synonyms = new ArrayCollection();

        //set mandatory list attributes
        $this->setName("");
        $this->setType('user-added');
        $this->setCreatedate(new \DateTime());
        $this->setOrderinlist(-1);

        if( $creator ) {
            $this->setCreator($creator);
        }

    }


    public function addSynonym($synonym)
    {
        if( !$this->synonyms->contains($synonym) ) {
            $this->synonyms->add($synonym);
            $synonym->setOriginal($this);
        }
        return $this;
    }

    public function removeSynonym($synonyms)
    {
        $this->synonyms->removeElement($synonyms);
    }

    /**
     * Get synonyms
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getSynonyms()
    {
        return $this->synonyms;
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




    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set id
     *
     * @param integer $id
     * @return List
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Set name
     *
     * @param string $name
     * @return List
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name."";
    }

    /**
     * @param mixed $abbreviation
     */
    public function setAbbreviation($abbreviation)
    {
        $this->abbreviation = $abbreviation;
    }

    /**
     * @return mixed
     */
    public function getAbbreviation()
    {
        return $this->abbreviation;
    }

    /**
     * @param mixed $shortname
     */
    public function setShortname($shortname)
    {
        $this->shortname = $shortname;
    }

    /**
     * @return mixed
     */
    public function getShortname()
    {
        return $this->shortname;
    }

    /**
     * @param mixed $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * @return mixed
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set type
     *
     * @param string $type
     * @return List
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set createdate
     *
     * @param \DateTime $createdate
     * @return List
     */
    public function setCreatedate($createdate)
    {
        $this->createdate = $createdate;

        return $this;
    }

    /**
     * Get createdate
     *
     * @return \DateTime
     */
    public function getCreatedate()
    {
        return $this->createdate;
    }

    /**
     * Set creator
     *
     * @param \Oleg\UserdirectoryBundle\Entity\User $creator
     * @return List
     */
    public function setCreator(\Oleg\UserdirectoryBundle\Entity\User $creator=null)
    {
        $this->creator = $creator;

        return $this;
    }

    /**
     * Get creator
     *
     * @return \Oleg\UserdirectoryBundle\Entity\User $creator
     */
    public function getCreator()
    {
        return $this->creator;
    }

    /**
     * @param mixed $orderinlist
     */
    public function setOrderinlist($orderinlist)
    {
        $this->orderinlist = $orderinlist;
    }

    /**
     * @return mixed
     */
    public function getOrderinlist()
    {
        return $this->orderinlist;
    }

    /**
     * @return mixed
     */
    public function getLinkToListId()
    {
        return $this->linkToListId;
    }

    /**
     * @param mixed $linkToListId
     */
    public function setLinkToListId($linkToListId)
    {
        $this->linkToListId = $linkToListId;
    }

    /**
     * @return mixed
     */
    public function getObjectType()
    {
        return $this->objectType;
    }

    /**
     * @param mixed $objectType
     */
    public function setObjectType($objectType)
    {
        $this->objectType = $objectType;
    }

    /**
     * @return mixed
     */
    public function getLinkToObjectId()
    {
        return $this->linkToObjectId;
    }

    /**
     * @param mixed $linkToObjectId
     */
    public function setLinkToObjectId($linkToObjectId)
    {
        $this->linkToObjectId = $linkToObjectId;
    }

    /**
     * @return mixed
     */
    public function getItemType()
    {
        return $this->itemType;
    }

    /**
     * @param mixed $itemType
     */
    public function setItemType($itemType)
    {
        $this->itemType = $itemType;
    }








    /////////////// full titles ////////////////////
    /**
     * @param mixed $fulltitle
     */
    public function setFulltitle($fulltitle)
    {
        $this->fulltitle = $fulltitle;
    }

    /**
     * @return mixed
     */
    public function getFulltitle()
    {
        return $this->fulltitle;
    }

//    /**
//     * @param mixed $fulltitlemedium
//     */
//    public function setFulltitlemedium($fulltitlemedium)
//    {
//        $this->fulltitlemedium = $fulltitlemedium;
//    }
//
//    /**
//     * @return mixed
//     */
//    public function getFulltitlemedium()
//    {
//        return $this->fulltitlemedium;
//    }
//
//    /**
//     * @param mixed $fulltitleshort
//     */
//    public function setFulltitleshort($fulltitleshort)
//    {
//        $this->fulltitleshort = $fulltitleshort;
//    }
//
//    /**
//     * @return mixed
//     */
//    public function getFulltitleshort()
//    {
//        return $this->fulltitleshort;
//    }
//
//    /**
//     * @param mixed $fulltitleunique
//     */
//    public function setFulltitleunique($fulltitleunique)
//    {
//        $this->fulltitleunique = $fulltitleunique;
//    }
//
//    /**
//     * @return mixed
//     */
//    public function getFulltitleunique()
//    {
//        return $this->fulltitleunique;
//    }
    /////////////// EOF full titles ////////////////////


    public function __toString()
    {
        $name = $this->name."";
//        if( $this->shortname && $this->shortname != "" ) {
//            $name = $this->shortname."";
//        }
        return $name;
    }

    //For search
    public function getOptimalName()
    {
        if( $this->abbreviation && $this->abbreviation != "" ) {
            return $this->abbreviation."";
        }

        if( $this->shortname && $this->shortname != "" ) {
            return $this->shortname."";
        }

        if( $this->name && $this->name != "" ) {
            return $this->name."";
        }
    }

    /**
     * @param mixed $updatedby
     */
    public function setUpdatedby($user)
    {
        //if( $user ) {
            $this->updatedby = $user;
        //}
    }

    /**
     * @return mixed
     */
    public function getUpdatedby()
    {
        return $this->updatedby;
    }

    /**
     * @ORM\PreUpdate
     */
    public function setUpdatedon()
    {
        $this->updatedon = new \DateTime();
    }

    /**
     * @return \DateTime
     */
    public function getUpdatedon()
    {
        return $this->updatedon;
    }

    public function isEmpty() {
        if( $this->name == '' ) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @return mixed
     */
    public function getUpdateAuthorRoles()
    {
        return $this->updateAuthorRoles;
    }


    public function setUpdateAuthorRoles($roles) {
        foreach( $roles as $role ) {
            $this->addUpdateAuthorRole($role."");
        }
    }

    public function addUpdateAuthorRole($role) {
        $role = strtoupper($role);
        if( !in_array($role, $this->updateAuthorRoles, true) ) {
            $this->updateAuthorRoles[] = $role;
        }
    }

    public function removeDependents($user) {
        return;
    }


    /**
     * @ORM\PrePersist
     * @ORM\PreUpdate
     */
    public function onCreateUpdate() {
        $this->createFullTitle();
    }

    public function createFullTitle()
    {
        $fullTitle = "";

        if( $this->getAbbreviation() ) {
            $fullTitle = $this->getAbbreviation();
        }

        if( $this->getName() ) {
            if( $fullTitle != "" ) {
                $fullTitle = $fullTitle . " - " .  $this->getName();
            } else {
                $fullTitle = $this->getName();
            }
        }

        $this->setFulltitle($fullTitle);

        return $fullTitle;
    }

    //for entity with synonyms
//    public function setSynonyms($synonyms = null) {
//        echo "set synonym=".$synonyms."<br>";
//        exit();
//        $newsynonyms = new ArrayCollection();
//        if( $synonyms ) {
//            $newsynonyms->add($synonyms);
//            $this->synonyms = $newsynonyms;
//        } else {
//            $this->synonyms = $newsynonyms;
//        }
//        return $this;
//    }


}