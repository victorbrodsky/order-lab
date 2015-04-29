<?php

namespace Oleg\UserdirectoryBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity
 * @ORM\Table(name="user_localeList")
 */
class LocaleList extends ListAbstract
{

    /**
     * @ORM\OneToMany(targetEntity="LocaleList", mappedBy="original")
     **/
    protected $synonyms;

    /**
     * @ORM\ManyToOne(targetEntity="LocaleList", inversedBy="synonyms")
     * @ORM\JoinColumn(name="original_id", referencedColumnName="id")
     **/
    protected $original;



    public function createFullTitle()
    {
        $fullTitle = "";

        if( $this->getName() ) {
            $fullTitle = $this->getName();
        }

        if( $this->getDescription() ) {
            if( $fullTitle != "" ) {
                $fullTitle = $fullTitle . " - " .  $this->getDescription();
            } else {
                $fullTitle = $this->getDescription();
            }
        }

        $this->setFulltitle($fullTitle);

        return $fullTitle;
    }

}