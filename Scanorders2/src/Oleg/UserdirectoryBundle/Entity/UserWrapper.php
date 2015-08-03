<?php
/**
 * Created by JetBrains PhpStorm.
 * User: oli2002
 * Date: 4/22/14
 * Time: 10:19 AM
 * To change this template use File | Settings | File Templates.
 */

namespace Oleg\UserdirectoryBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity(repositoryClass="Oleg\UserdirectoryBundle\Repository\UserWrapperRepository")
 * @ORM\Table(name="user_userWrapper")
 */
class UserWrapper extends ListAbstract {

    /**
     * @ORM\OneToMany(targetEntity="UserWrapper", mappedBy="original", cascade={"persist"})
     **/
    protected $synonyms;

    /**
     * @ORM\ManyToOne(targetEntity="UserWrapper", inversedBy="synonyms", cascade={"persist"})
     * @ORM\JoinColumn(name="original_id", referencedColumnName="id", nullable=true)
     **/
    protected $original;


//    /**
//     * @ORM\Id
//     * @ORM\Column(type="integer")
//     * @ORM\GeneratedValue(strategy="AUTO")
//     */
//    private $id;

//    /**
//     * must be synchronised with name in ListAbstract
//     *
//     * @ORM\Column(type="string", nullable=true)
//     */
//    private $userStr;
    //use name in ListAbstract as userStr

    /**
     * User object
     * @ORM\ManyToOne(targetEntity="User")
     */
    private $user;





//    /**
//     * @param mixed $id
//     */
//    public function setId($id)
//    {
//        $this->id = $id;
//    }
//
//    /**
//     * @return mixed
//     */
//    public function getId()
//    {
//        return $this->id;
//    }

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

    /**
     * @param mixed $userStr
     */
    public function setUserStr($userStr)
    {
        //$this->userStr = $userStr;
        $this->setName($userStr);
    }

    /**
     * @return mixed
     */
    public function getUserStr()
    {
        //return $this->userStr;
        return $this->getName();
    }

    public function __toString() {
        return $this->getFullName();
    }

    public function getFullName() {
        $fullName = "";

        if( $this->getUser() ) {
            $fullName = $fullName . $this->getUser()."";
        }

        if( $this->getName() ) {
            if( $fullName ) {
                $fullName = $fullName . " " .$this->getName()."";
            } else {
                $fullName = $this->getName()."";
            }
        }

        return $fullName;
    }

    //get user id or user string
    //used for transformer
    public function getEntity() {

        if( $this->getId() ) {
            return $this->getId();
        }

        return $this->getFullName();

//        if( $this->getUser() && $this->getUser()->getId() ) {
//            //return $this->getUser()->getId();
//            return $this->getUser()."";
//        }
//        if( $this->getName() ) {
//            return $this->getName();
//        }
//        return null;
    }


}