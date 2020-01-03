<?php
/**
 * Copyright (c) 2017 Cornell University
 *
 *  Licensed under the Apache License, Version 2.0 (the "License");
 *  you may not use this file except in compliance with the License.
 *  You may obtain a copy of the License at
 *
 *  http://www.apache.org/licenses/LICENSE-2.0
 *
 *  Unless required by applicable law or agreed to in writing, software
 *  distributed under the License is distributed on an "AS IS" BASIS,
 *  WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 *  See the License for the specific language governing permissions and
 *  limitations under the License.
 */

/**
 * Created by JetBrains PhpStorm.
 * User: oli2002
 * Date: 4/18/14
 * Time: 1:07 PM
 * To change this template use File | Settings | File Templates.
 */

namespace App\UserdirectoryBundle\Entity;


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

    /**
     * @ORM\ManyToOne(targetEntity="LocaleList")
     **/
    private $locale;

    /**
     * Exclude from Employee Directory search results
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $excludeFromSearch;

    /**
     * Hide this profile
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $hide;

    /**
     * Only show this profile to members of the following institution(s)
     * @ORM\ManyToMany(targetEntity="Institution")
     * @ORM\JoinTable(name="user_preferences_institutions",
     *      joinColumns={@ORM\JoinColumn(name="preferences_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="institution_id", referencedColumnName="id")}
     *      )
     */
    private $showToInstitutions;

    /**
     * Only show this profile to users with the following roles
     * @var array
     * @ORM\Column(type="array", nullable=true)
     */
    protected $showToRoles = array();

    /**
     * Do not send a notification email if listed as an "attending" in a Call Log Book Entry
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $noAttendingEmail;

    /**
     * @ORM\ManyToOne(targetEntity="LifeFormList")
     **/
    private $lifeForm;

//    /**
//     * @ORM\Column(type="boolean", nullable=true)
//     */
//    protected $tooltip;

    public function __construct() {
        $this->languages = new ArrayCollection();
        $this->showToInstitutions = new ArrayCollection();
        $this->roles = array();
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

    /**
     * @param mixed $locale
     */
    public function setLocale($locale)
    {
        $this->locale = $locale;
    }

    /**
     * @return mixed
     */
    public function getLocale()
    {
        return $this->locale;
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

    public function addShowToInstitution($item)
    {
        if( $item && !$this->showToInstitutions->contains($item) ) {
            $this->showToInstitutions->add($item);
        }
        return $this;
    }
    public function removeShowToInstitution($item)
    {
        $this->showToInstitutions->removeElement($item);
    }
    public function getShowToInstitutions()
    {
        return $this->showToInstitutions;
    }

    /**
     * @return mixed
     */
    public function getShowToRoles()
    {
        return $this->showToRoles;
    }
    public function addShowToRole($role) {
        $role = strtoupper($role);
        if( !in_array($role, $this->showToRoles, true) ) {
            $this->showToRoles[] = $role;
        }
    }
    public function removeShowToRole($role)
    {
        if (false !== $key = array_search(strtoupper($role), $this->showToRoles, true)) {
            unset($this->showToRoles[$key]);
            $this->showToRoles = array_values($this->showToRoles);
        }

        return $this;
    }
    public function setShowToRoles($roles) {
        $this->showToRoles = array();
        foreach( $roles as $role ) {
            $this->addShowToRole($role."");
        }
    }

    /**
     * @param mixed $hide
     */
    public function setHide($hide)
    {
        $this->hide = $hide;
    }

    /**
     * @return mixed
     */
    public function getHide()
    {
        return $this->hide;
    }

    /**
     * @param mixed $excludeFromSearch
     */
    public function setExcludeFromSearch($excludeFromSearch)
    {
        $this->excludeFromSearch = $excludeFromSearch;
    }

    /**
     * @return mixed
     */
    public function getExcludeFromSearch()
    {
        return $this->excludeFromSearch;
    }

    /**
     * @return mixed
     */
    public function getNoAttendingEmail()
    {
        return $this->noAttendingEmail;
    }

    /**
     * @param mixed $noAttendingEmail
     */
    public function setNoAttendingEmail($noAttendingEmail)
    {
        $this->noAttendingEmail = $noAttendingEmail;
    }

    /**
     * @return mixed
     */
    public function getLifeForm()
    {
        return $this->lifeForm;
    }

    /**
     * @param mixed $lifeForm
     */
    public function setLifeForm($lifeForm)
    {
        $this->lifeForm = $lifeForm;
    }




    public function __toString() {
        $res = "UserPreferences";
        if( $this->getId() ) {
            $res = $res . " ID:" . $this->getId();
        }
        return $res;
    }

}