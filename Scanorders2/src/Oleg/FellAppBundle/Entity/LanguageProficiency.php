<?php

namespace Oleg\FellAppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

use Oleg\UserdirectoryBundle\Entity\ListAbstract;

/**
 * @ORM\Entity
 * @ORM\Table(name="fellapp_languageProficiency")
 */
class LanguageProficiency extends ListAbstract
{

    /**
     * @ORM\OneToMany(targetEntity="LanguageProficiency", mappedBy="original", cascade={"persist"})
     **/
    protected $synonyms;

    /**
     * @ORM\ManyToOne(targetEntity="LanguageProficiency", inversedBy="synonyms", cascade={"persist"})
     * @ORM\JoinColumn(name="original_id", referencedColumnName="id", nullable=true)
     **/
    protected $original;



    public function __toString() {
        return $this->name;
    }


}