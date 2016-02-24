<?php

namespace Oleg\UserdirectoryBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

use Oleg\UserdirectoryBundle\Entity\ListAbstract;

/**
 * @ORM\Entity
 * @ORM\Table(name="user_permissionObjectList")
 */
class PermissionObjectList extends ListAbstract {

    /**
     * @ORM\OneToMany(targetEntity="PermissionObjectList", mappedBy="original", cascade={"persist"})
     **/
    protected $synonyms;

    /**
     * @ORM\ManyToOne(targetEntity="PermissionObjectList", inversedBy="synonyms", cascade={"persist"})
     * @ORM\JoinColumn(name="original_id", referencedColumnName="id", nullable=true)
     **/
    protected $original;



    /**
     * @ORM\ManyToMany(targetEntity="SiteList", cascade={"persist"})
     * @ORM\JoinTable(name="user_permissionObjectList_sites",
     *      joinColumns={@ORM\JoinColumn(name="permissionObjectList_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="site_id", referencedColumnName="id")}
     *      )
     **/
    private $sites;



    public function __construct() {

        parent::__construct();

        $this->sites = new ArrayCollection();

    }




    public function getSites()
    {
        return $this->sites;
    }
    public function addSite($item)
    {
        if( $item && !$this->sites->contains($item) ) {
            $this->sites->add($item);
        }
    }
    public function removeSite($item)
    {
        $this->sites->removeElement($item);
    }


    public function hasSite( $sitename ) {
        foreach( $this->getSites() as $site ) {
            if( $site->getName()."" == $sitename || $site->getAbbreviation()."" == $sitename ) {
                return true;
            }
        }
        return false;
    }


}