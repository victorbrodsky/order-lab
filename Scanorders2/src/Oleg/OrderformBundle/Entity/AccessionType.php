<?php

namespace Oleg\OrderformBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="accessiontype")
 */
class AccessionType extends ListAbstract
{

    /**
     * @ORM\OneToMany(targetEntity="AccessionAccession", mappedBy="accessiontype")
     */
    protected $accessionaccession;


    public function __construct() {
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