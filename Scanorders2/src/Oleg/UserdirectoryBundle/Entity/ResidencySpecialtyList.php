<?php

namespace Oleg\UserdirectoryBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;


/**
 * @ORM\Entity
 * @ORM\Table(name="user_residencySpecialtyList")
 */
class ResidencySpecialtyList extends ListAbstract
{

    /**
     * @ORM\OneToMany(targetEntity="ResidencySpecialtyList", mappedBy="original", cascade={"persist"})
     **/
    protected $synonyms;

    /**
     * @ORM\ManyToOne(targetEntity="ResidencySpecialtyList", inversedBy="synonyms", cascade={"persist"})
     * @ORM\JoinColumn(name="original_id", referencedColumnName="id", nullable=true)
     **/
    protected $original;



    //fellowshipSubspecialty - children
    /**
     * @ORM\OneToMany(targetEntity="FellowshipSubspecialtyList", mappedBy="parent", cascade={"persist"})
     */
    private $children;


    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $boardCertificateAvailable;



    public function __construct( $author = null ) {
        $this->children = new ArrayCollection();
        parent::__construct();
    }



    public function addChild($child)
    {
        if( $child && !$this->children->contains($child) ) {
            $this->children->add($child);
            $child->setParent($this);
        }

        return $this;
    }
    public function removeChild($child)
    {
        $this->children->removeElement($child);
    }
    public function getChildren()
    {
        return $this->children;
    }

    /**
     * @param mixed $boardCertificateAvailable
     */
    public function setBoardCertificateAvailable($boardCertificateAvailable)
    {
        $this->boardCertificateAvailable = $boardCertificateAvailable;
    }

    /**
     * @return mixed
     */
    public function getBoardCertificateAvailable()
    {
        return $this->boardCertificateAvailable;
    }

    public function getClassName()
    {
        return "ResidencySpecialtyList";
    }



}