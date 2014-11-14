<?php

namespace Oleg\OrderformBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

use Oleg\UserdirectoryBundle\Entity\ListAbstract;

/**
 * @ORM\Entity
 * @ORM\Table(name="scan_accessiontype")
 */
class AccessionType extends ListAbstract
{

    /**
     * @ORM\OneToMany(targetEntity="AccessionType", mappedBy="original", cascade={"persist"})
     **/
    protected $synonyms;

    /**
     * @ORM\ManyToOne(targetEntity="AccessionType", inversedBy="synonyms", cascade={"persist"})
     * @ORM\JoinColumn(name="original_id", referencedColumnName="id", nullable=true)
     **/
    protected $original;

    /**
     * @ORM\OneToMany(targetEntity="AccessionAccession", mappedBy="keytype")
     */
    protected $accessionaccession;


    public function __construct() {
        $this->synonyms = new ArrayCollection();
        $this->accessionaccession = new ArrayCollection();
    }



    public function addAccessionaccession(\Oleg\OrderformBundle\Entity\AccessionAccession $accessionaccession)
    {
        if( !$this->accessionaccession->contains($accessionaccession) ) {
            $this->accessionaccession->add($accessionaccession);
        }
        return $this;
    }

    public function removeAccessionaccession(\Oleg\OrderformBundle\Entity\AccessionAccession $accessionaccession)
    {
        $this->accessionaccession->removeElement($accessionaccession);
    }

    public function getAccessionaccession()
    {
        return $this->accessionaccession;
    }


}