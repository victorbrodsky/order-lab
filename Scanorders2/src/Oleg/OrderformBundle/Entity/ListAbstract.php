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
     */
    protected $creator;


    /**
     * @var \DateTime
     * @ORM\Column(name="date", type="datetime")
     * @Assert\NotBlank
     */
    protected $createdate;

    /**
     * Indicates the order in the list
     * @ORM\Column(type="string")
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
     * @return OrganList
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
     * @return OrganList
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
     * @return OrganList
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
     * @return OrganList
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
        return $this->name;
    }

}