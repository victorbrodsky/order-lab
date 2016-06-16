<?php

namespace Oleg\UserdirectoryBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

//* @ORM\Table(name="user_platformListManagerRootList",
// *     uniqueConstraints={@ORM\UniqueConstraint(name="list_unique", columns={"listId", "listName"})}
// * )


/**
 * @ORM\Entity
 * @ORM\Table(name="user_platformListManagerRootList")
 */
class PlatformListManagerRootList extends ListAbstract
{
    /**
     * @ORM\OneToMany(targetEntity="PlatformListManagerRootList", mappedBy="original")
     **/
    protected $synonyms;

    /**
     * @ORM\ManyToOne(targetEntity="PlatformListManagerRootList", inversedBy="synonyms")
     * @ORM\JoinColumn(name="original_id", referencedColumnName="id")
     **/
    protected $original;


    /**
     * @ORM\Column(type="string")
     */
    protected $listId;

    /**
     * @ORM\Column(type="string")
     */
    protected $listName;




    /**
     * @return mixed
     */
    public function getListId()
    {
        return $this->listId;
    }

    /**
     * @param mixed $listId
     */
    public function setListId($listId)
    {
        $this->listId = $listId;
    }

    /**
     * @return mixed
     */
    public function getListName()
    {
        return $this->listName;
    }

    /**
     * @param mixed $listName
     */
    public function setListName($listName)
    {
        $this->listName = $listName;
    }




}