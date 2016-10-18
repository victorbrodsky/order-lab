<?php

namespace Oleg\UserdirectoryBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

//use Oleg\UserdirectoryBundle\Entity\ListAbstract;

/**
 * @ORM\Entity
 * @ORM\Table(name="user_sexList")
 */
class SexList extends ListAbstract
{

    /**
     * @ORM\OneToMany(targetEntity="SexList", mappedBy="original", cascade={"persist"})
     **/
    protected $synonyms;

    /**
     * @ORM\ManyToOne(targetEntity="SexList", inversedBy="synonyms", cascade={"persist"})
     * @ORM\JoinColumn(name="original_id", referencedColumnName="id", nullable=true)
     **/
    protected $original;



    public function __toString()
    {
        $name = $this->name."";

        if( $this->abbreviation && $this->abbreviation != "" ) {
            $name = $this->abbreviation."";
        }

        return $name;
    }
}