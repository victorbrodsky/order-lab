<?php

namespace Oleg\OrderformBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

use Oleg\UserdirectoryBundle\Entity\ListAbstract;

/**
 * @ORM\Entity
 * @ORM\Table(name="scan_organlist")
 */
class OrganList extends ListAbstract
{

    /**
     * @ORM\OneToMany(targetEntity="OrganList", mappedBy="original")
     **/
    protected $synonyms;

    /**
     * @ORM\ManyToOne(targetEntity="OrganList", inversedBy="synonyms")
     * @ORM\JoinColumn(name="original_id", referencedColumnName="id")
     **/
    protected $original;

    /**
     * @ORM\OneToMany(targetEntity="PartSourceOrgan", mappedBy="field")
     */
    protected $part;

//    /**
//     * @ORM\OneToMany(targetEntity="Part", mappedBy="primaryOrgan")
//     */
//    protected $partprimary;

    /**
     * @ORM\OneToMany(targetEntity="PartDiseaseType", mappedBy="primaryOrgan")
     */
    protected $partprimary;


    public function __construct() {
        $this->synonyms = new ArrayCollection();
        $this->part = new ArrayCollection();
    }


    /**
     * Add part
     *
     * @param \Oleg\OrderformBundle\Entity\OrganList $part
     * @return OrganList
     */
    public function addPart(\Oleg\OrderformBundle\Entity\OrganList $part)
    {
        $this->part[] = $part;
    
        return $this;
    }

    /**
     * Remove part
     *
     * @param \Oleg\OrderformBundle\Entity\OrganList $part
     */
    public function removePart(\Oleg\OrderformBundle\Entity\OrganList $part)
    {
        $this->part->removeElement($part);
    }

    /**
     * Get part
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getPart()
    {
        return $this->part;
    }
}