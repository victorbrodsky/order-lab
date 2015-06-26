<?php

namespace Oleg\UserdirectoryBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

//TODO: turn it to BaseCompositeNode
/**
 * @ORM\Entity
 * @ORM\Table(name="user_fellowshipSubspecialty")
 */
class FellowshipSubspecialty extends ListAbstract
{

    /**
     * @ORM\OneToMany(targetEntity="FellowshipSubspecialty", mappedBy="original", cascade={"persist"})
     **/
    protected $synonyms;

    /**
     * @ORM\ManyToOne(targetEntity="FellowshipSubspecialty", inversedBy="synonyms", cascade={"persist"})
     * @ORM\JoinColumn(name="original_id", referencedColumnName="id", nullable=true)
     **/
    protected $original;




    //ResidencySpecialty - parent
    /**
     * @ORM\ManyToOne(targetEntity="ResidencySpecialty", inversedBy="children")
     * @ORM\JoinColumn(name="parent_id", referencedColumnName="id", nullable=true)
     **/
    protected $parent;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $boardCertificateAvailable;




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
        return "FellowshipSubspecialty";
    }


}