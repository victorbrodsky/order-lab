<?php
/**
 * Created by JetBrains PhpStorm.
 * User: oli2002
 * Date: 4/18/14
 * Time: 1:07 PM
 * To change this template use File | Settings | File Templates.
 */

namespace Oleg\UserdirectoryBundle\Entity;


use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="user_preferences")
 */
class UserPreferences {

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\OneToOne(targetEntity="User", mappedBy="preferences")
     */
    private $user;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $timezone;

    /**
     * @ORM\ManyToMany(targetEntity="LanguageList", inversedBy="userpreferences")
     * @ORM\JoinTable(name="user_userpreferences_languages")
     **/
    private $languages;

//    /**
//     * @ORM\Column(type="boolean", nullable=true)
//     */
//    protected $tooltip;

    public function __construct() {
        $this->languages = new ArrayCollection();
    }

    /**
     * @param mixed $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $user
     */
    public function setUser($user)
    {
        $this->user = $user;
    }

    /**
     * @return mixed
     */
    public function getUser()
    {
        return $this->user;
    }

//    /**
//     * @param mixed $tooltip
//     */
//    public function setTooltip($tooltip)
//    {
//        $this->tooltip = $tooltip;
//    }
//
//    /**
//     * @return mixed
//     */
//    public function getTooltip()
//    {
//        return $this->tooltip;
//    }

    /**
     * @param mixed $timezone
     */
    public function setTimezone($timezone)
    {
        $this->timezone = $timezone;
    }

    /**
     * @return mixed
     */
    public function getTimezone()
    {
        return $this->timezone;
    }

    public function addLanguage($item)
    {
        if( $item && !$this->languages->contains($item) ) {
            $this->languages->add($item);
        }
        return $this;
    }
    public function removeLanguage($item)
    {
        $this->languages->removeElement($item);
    }
    public function getLanguages()
    {
        return $this->languages;
    }

}