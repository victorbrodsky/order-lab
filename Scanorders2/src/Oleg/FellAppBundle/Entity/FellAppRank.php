<?php

namespace Oleg\FellAppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

use Oleg\UserdirectoryBundle\Entity\ListAbstract;

/**
 * @ORM\Entity
 * @ORM\Table(name="fellapp_fellAppRank")
 */
class FellAppRank extends ListAbstract
{

    /**
     * @ORM\OneToMany(targetEntity="FellAppRank", mappedBy="original", cascade={"persist"})
     **/
    protected $synonyms;

    /**
     * @ORM\ManyToOne(targetEntity="FellAppRank", inversedBy="synonyms", cascade={"persist"})
     * @ORM\JoinColumn(name="original_id", referencedColumnName="id", nullable=true)
     **/
    protected $original;

    /**
     * @ORM\Column(name="value", type="decimal", precision=2, scale=1, nullable=true)
     */
    private $value;


    //name => 1 (Excellent)
    //value => 1

    //name => 1.5
    //value => 1.5


    /**
     * @param mixed $value
     */
    public function setValue($value)
    {
        $this->value = $value;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }




    public function __toString() {
        return $this->name;
    }







}