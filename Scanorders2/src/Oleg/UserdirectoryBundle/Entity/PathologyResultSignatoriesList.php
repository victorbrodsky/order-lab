<?php

namespace Oleg\UserdirectoryBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity
 * @ORM\Table(name="user_pathologyResultSignatoriesList")
 */
class PathologyResultSignatoriesList extends ListAbstract
{

    /**
     * @ORM\OneToMany(targetEntity="PathologyResultSignatoriesList", mappedBy="original")
     **/
    protected $synonyms;

    /**
     * @ORM\ManyToOne(targetEntity="PathologyResultSignatoriesList", inversedBy="synonyms")
     * @ORM\JoinColumn(name="original_id", referencedColumnName="id")
     **/
    protected $original;


    //user ID set by setObject($user)

//    /**
//     * @ORM\ManyToMany(targetEntity="Oleg\UserdirectoryBundle\Entity\UserWrapper", cascade={"persist","remove"})
//     * @ORM\JoinTable(name="scan_pathologyResultSignatory_userWrapper",
//     *      joinColumns={@ORM\JoinColumn(name="pathologyResultSignatory_id", referencedColumnName="id")},
//     *      inverseJoinColumns={@ORM\JoinColumn(name="userWrapper_id", referencedColumnName="id")}
//     *      )
//     **/
//    private $userWrappers;



    public function __construct($creator=null) {
        parent::__construct($creator);

//        $this->userWrappers = new ArrayCollection();
    }



//    public function getUserWrappers()
//    {
//        return $this->userWrappers;
//    }
//    public function addUserWrapper($item)
//    {
//        if( $item && !$this->userWrappers->contains($item) ) {
//            $this->userWrappers->add($item);
//        }
//        return $this;
//    }
//    public function removeUserWrapper($item)
//    {
//        $this->userWrappers->removeElement($item);
//    }

//    public function addUserWrapperAsUser($user)
//    {
//        if( $user ) {
//            $userWrapper = new UserWrapper();
//            $userWrapper->setUser($user);
//            $this->addUserWrapper($userWrapper);
//        }
//        return $this;
//    }
//    public function getUserWrapperAsUser()
//    {
//        $userWrapper = null;
//        $userWrapper = $this->getUserWrappers()->first();
//        if( $userWrapper ) {
//            $userWrapper = $userWrapper->getUser();
//        }
//        return $userWrapper;
//    }


}