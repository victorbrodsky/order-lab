<?php

namespace Oleg\UserdirectoryBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

//TODO: turn it to BaseCompositeNode
/**
 * @ORM\Entity
 * @ORM\Table(name="user_residencySpecialty")
 */
class ResidencySpecialty extends ListAbstract
{

    /**
     * @ORM\OneToMany(targetEntity="ResidencySpecialty", mappedBy="original")
     **/
    protected $synonyms;

    /**
     * @ORM\ManyToOne(targetEntity="ResidencySpecialty", inversedBy="synonyms")
     * @ORM\JoinColumn(name="original_id", referencedColumnName="id", nullable=true)
     **/
    protected $original;



    //fellowshipSubspecialty - children
    /**
     * @ORM\OneToMany(targetEntity="FellowshipSubspecialty", mappedBy="parent", cascade={"persist"})
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

    //mapper functions to deal with tree logic
    public function addFellowshipSubspecialty($child) {
        $this->addChild($child);
    }
    public function removeFellowshipSubspecialty($child) {
        $this->removeChild($child);
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
        return "ResidencySpecialty";
    }



}