<?php
/**
 * Created by JetBrains PhpStorm.
 * User: oli2002
 * Date: 10/24/13
 * Time: 12:14 PM
 * To change this template use File | Settings | File Templates.
 */

namespace Oleg\OrderformBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\ArrayCollection;

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
     * @ORM\Column(type="string")
     * @Assert\NotBlank
     */
    protected $name;

    /**
     * @ORM\Column(type="string")
     * @Assert\NotBlank
     */
    protected $type;

    /**
     * @ORM\ManyToOne(targetEntity="User", cascade={"persist"})
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
     * @ORM\ManyToOne(targetEntity="User", cascade={"persist"})
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
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
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
        return $this->name;
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
     * @param string $creator
     * @return List
     */
    public function setCreator($creator)
    {
        $this->creator = $creator;

        return $this;
    }

    /**
     * Get creator
     *
     * @return string
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


    public function __toString()
    {
        return $this->name."";
    }

    //@ORM\PrePersist
    /**
     * @param mixed $updatedby
     */
    public function setUpdatedby($user)
    {
        if( $user ) {
            $this->updatedby = $user;
        }
    }

    /**
     * @return mixed
     */
    public function getUpdatedby()
    {
        return $this->updatedby;
    }

    //@ORM\PrePersist
    /**
     * @param \DateTime $updatedon
     */
    public function setUpdatedon($updatedon)
    {
        $this->updatedon = $updatedon;
    }

    /**
     * @return \DateTime
     */
    public function getUpdatedon()
    {
        return $this->updatedon;
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