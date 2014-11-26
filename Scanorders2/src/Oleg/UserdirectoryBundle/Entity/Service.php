<?php

namespace Oleg\UserdirectoryBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="user_service")
 */
class Service extends ListAbstract
{
    /**
     * @ORM\OneToMany(targetEntity="Division", mappedBy="original")
     **/
    protected $synonyms;

    /**
     * @ORM\ManyToOne(targetEntity="Division", inversedBy="synonyms")
     * @ORM\JoinColumn(name="original_id", referencedColumnName="id")
     **/
    protected $original;

    /**
     * Parent
     * @ORM\ManyToOne(targetEntity="Division", inversedBy="services")
     * @ORM\JoinColumn(name="division", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $parent;


    /**
     * @ORM\ManyToMany(targetEntity="User")
     * @ORM\JoinTable(name="user_service_head")
     **/
    private $heads;


    public function __construct() {
        $this->heads = new ArrayCollection();
        parent::__construct();
    }



    public function addHead($head)
    {
        if( !$this->heads->contains($head) ) {
            $this->heads->add($head);
        }
        return $this;
    }

    public function removeHead($head)
    {
        $this->heads->removeElement($head);
    }

    public function getHeads()
    {
        return $this->heads;
    }


    /**
     * @param mixed $parent
     */
    public function setParent($parent)
    {
        $this->parent = $parent;
    }

    /**
     * @return mixed
     */
    public function getParent()
    {
        return $this->parent;
    }

    public function getParentTree($html=false) {
        $division = $this->getParent();
        $department = $division->getParent();
        $inst = $department->getParent();
        $del = " / "; //" -> ";
        if( $html ) {
            $tree = "<strong>".$inst."</strong>".$del."<strong>".$department."</strong>".$del."<strong>".$division."</strong>".$del."<strong>".$this."</strong>";
        } else {
            $tree = $inst.$del.$department.$del.$division.$del.$this;
        }


        return $tree;
    }




    public function getParentName()
    {
        return "Division";
    }
    public function getClassName()
    {
        return "Service";
    }
}