<?php

namespace Oleg\UserdirectoryBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity
 * @ORM\Table(name="user_languageList")
 */
class LanguageList extends ListAbstract
{

    /**
     * @ORM\OneToMany(targetEntity="LanguageList", mappedBy="original")
     **/
    protected $synonyms;

    /**
     * @ORM\ManyToOne(targetEntity="LanguageList", inversedBy="synonyms")
     * @ORM\JoinColumn(name="original_id", referencedColumnName="id")
     **/
    protected $original;

    /**
     * @ORM\ManyToMany(targetEntity="UserPreferences", mappedBy="languages")
     **/
    protected $userpreferences;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $nativeName;


    public function __construct() {
        parent::__construct();
        $this->userpreferences = new ArrayCollection();
    }


    public function addUserpreference($item)
    {
        if( $item && !$this->userpreferences->contains($item) ) {
            $this->userpreferences->add($item);
        }
        return $this;
    }
    public function removeUserpreference($item)
    {
        $this->userpreferences->removeElement($item);
    }
    public function getUserpreferences()
    {
        return $this->userpreferences;
    }

    /**
     * @param mixed $nativeName
     */
    public function setNativeName($nativeName)
    {
        $this->nativeName = $nativeName;
    }

    /**
     * @return mixed
     */
    public function getNativeName()
    {
        return $this->nativeName;
    }

    public function createFullTitle()
    {
        $fullTitle = "";

        if( $this->getName() ) {
            $fullTitle = $this->getName();
        }

        if( $this->getNativeName() ) {
            if( $fullTitle != "" ) {
                $fullTitle = $fullTitle . " - " .  $this->getNativeName();
            } else {
                $fullTitle = $this->getNativeName();
            }
        }

        $this->setFulltitle($fullTitle);

        return $fullTitle;
    }


}