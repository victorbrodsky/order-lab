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
 * Created by PhpStorm.
 * User: ch3
 * Date: 11/2/2016
 * Time: 3:39 PM
 */

namespace App\UserdirectoryBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="user_organizationalGroupDefault")
 */
class OrganizationalGroupDefault
{

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="SiteParameters", inversedBy="organizationalGroupDefaults")
     */
    private $siteParameter;

    /**
     * @ORM\ManyToOne(targetEntity="Institution")
     */
    private $institution;

    /**
     * Primary Public User ID Type
     *
     * @ORM\ManyToOne(targetEntity="UsernameType")
     * @ORM\JoinColumn(referencedColumnName="id", nullable=true)
     */
    private $primaryPublicUserIdType;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $email;

    /**
     * @var array
     * @ORM\Column(type="array", nullable=true)
     */
    private $roles = array();

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $timezone;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $tooltip;

    /**
     * Only show this profile to members of the following institution(s)
     * @ORM\ManyToMany(targetEntity="Institution")
     * @ORM\JoinTable(name="user_organizationalGroupDefault_showToInstitution",
     *      joinColumns={@ORM\JoinColumn(name="organizationalGroupDefault_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="showToInstitution_id", referencedColumnName="id")}
     *      )
     */
    private $showToInstitutions;

    /**
     * @ORM\ManyToOne(targetEntity="Institution")
     */
    private $defaultInstitution;

    /**
     * @ORM\ManyToMany(targetEntity="Institution")
     * @ORM\JoinTable(name="user_organizationalGroupDefault_permittedInstitutionalPHIScope",
     *      joinColumns={@ORM\JoinColumn(name="permittedInstitutionalPHIScope_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="institution_id", referencedColumnName="id")}
     *      )
     **/
    private $permittedInstitutionalPHIScope;

    /**
     * @ORM\ManyToOne(targetEntity="EmploymentType")
     * @ORM\JoinColumn(name="employmentType_id", referencedColumnName="id", nullable=true)
     **/
    private $employmentType;

    /**
     * @ORM\ManyToOne(targetEntity="LocaleList")
     **/
    private $locale;

    /**
     * @ORM\ManyToMany(targetEntity="LanguageList", inversedBy="userpreferences")
     * @ORM\JoinTable(name="user_organizationalGroupDefault_language")
     **/
    private $languages;

    //Administrative Title Institution or Collaboration: Weill Cornell Medical College
    //Administrative Title Department: Department of Pathology and Laboratory Medicine
    /**
     * @ORM\ManyToOne(targetEntity="Institution")
     */
    private $administrativeTitleInstitution;

    //Academic Appointment Title Institution or Collaboration: Weill Cornell Medical College
    //Academic Appointment Department: Department of Pathology and Laboratory Medicine
    /**
     * @ORM\ManyToOne(targetEntity="Institution")
     */
    private $academicTitleInstitution;

    //Medical Appointment Title Institution or Collaboration: Weill Cornell Medical College
    //Medical Appointment Title Department: Department of Pathology and Laboratory Medicine
    /**
     * @ORM\ManyToOne(targetEntity="Institution")
     */
    private $medicalTitleInstitution;

    //Location Type: Employee Office
    /**
     * @ORM\ManyToMany(targetEntity="LocationTypeList", inversedBy="locations", cascade={"persist"})
     * @ORM\JoinTable(name="user_organizationalGroupDefault_locationtype")
     **/
    private $locationTypes;

    //Location Institution or Collaboration: Weill Cornell Medical College
    //Location Department: Department of Pathology and Laboratory Medicine
    /**
     * @ORM\ManyToOne(targetEntity="Institution")
     */
    private $locationInstitution;

    //Location City: New York
    /**
     * @ORM\ManyToOne(targetEntity="CityList")
     * @ORM\JoinColumn(referencedColumnName="id", nullable=true)
     */
    private $city;

    //Location State: New York (NY)
    /**
     * @ORM\ManyToOne(targetEntity="States")
     * @ORM\JoinColumn(referencedColumnName="id", nullable=true)
     **/
    private $state;

    //Location Zip Code: 10065
    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $zip;

    //Location Country: United States
    /**
     * @ORM\ManyToOne(targetEntity="Countries")
     * @ORM\JoinColumn(referencedColumnName="id", nullable=true)
     **/
    private $country;

    //Medical License Country: United States
    /**
     * @ORM\ManyToOne(targetEntity="Countries")
     * @ORM\JoinColumn(referencedColumnName="id", nullable=true)
     **/
    private $medicalLicenseCountry;

    //Medical License State: New York (NY)
    /**
     * @ORM\ManyToOne(targetEntity="States")
     * @ORM\JoinColumn(referencedColumnName="id", nullable=true)
     **/
    private $medicalLicenseState;

    /**
     * Employment Period(s) [visible only to Editors and Administrators]: Organizational Group for Employment Period
     *
     * @ORM\ManyToOne(targetEntity="Institution")
     */
    private $employmentInstitution;



    public function __construct() {
        $this->languages = new ArrayCollection();
        $this->showToInstitutions = new ArrayCollection();
        $this->permittedInstitutionalPHIScope = new ArrayCollection();
        $this->languages = new ArrayCollection();
        $this->roles = array();
        $this->locationTypes = new ArrayCollection();
    }




    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
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
    public function getSiteParameter()
    {
        return $this->siteParameter;
    }

    /**
     * @param mixed $siteParameter
     */
    public function setSiteParameter($siteParameter)
    {
        $this->siteParameter = $siteParameter;
    }

    /**
     * @return mixed
     */
    public function getInstitution()
    {
        return $this->institution;
    }

    /**
     * @param mixed $institution
     */
    public function setInstitution($institution)
    {
        $this->institution = $institution;
    }

    /**
     * @return mixed
     */
    public function getPrimaryPublicUserIdType()
    {
        return $this->primaryPublicUserIdType;
    }

    /**
     * @param mixed $primaryPublicUserIdType
     */
    public function setPrimaryPublicUserIdType($primaryPublicUserIdType)
    {
        $this->primaryPublicUserIdType = $primaryPublicUserIdType;
    }

    /**
     * @return mixed
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param mixed $email
     */
    public function setEmail($email)
    {
        $this->email = $email;
    }

    /**
     * @return mixed
     */
    public function getTimezone()
    {
        return $this->timezone;
    }

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
    public function getTooltip()
    {
        return $this->tooltip;
    }

    /**
     * @param mixed $tooltip
     */
    public function setTooltip($tooltip)
    {
        $this->tooltip = $tooltip;
    }


    public function getPermittedInstitutionalPHIScope()
    {
        return $this->permittedInstitutionalPHIScope;
    }
    public function addPermittedInstitutionalPHIScope( $permittedInstitutionalPHIScope )
    {
        if( !$this->permittedInstitutionalPHIScope->contains($permittedInstitutionalPHIScope) ) {
            $this->permittedInstitutionalPHIScope->add($permittedInstitutionalPHIScope);
        }

    }
    public function removePermittedInstitutionalPHIScope($permittedInstitutionalPHIScope)
    {
        $this->permittedInstitutionalPHIScope->removeElement($permittedInstitutionalPHIScope);
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
    public function getDefaultInstitution()
    {
        return $this->defaultInstitution;
    }

    /**
     * @param mixed $defaultInstitution
     */
    public function setDefaultInstitution($defaultInstitution)
    {
        $this->defaultInstitution = $defaultInstitution;
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

    /**
     * @return mixed
     */
    public function getEmploymentType()
    {
        return $this->employmentType;
    }

    /**
     * @param mixed $employmentType
     */
    public function setEmploymentType($employmentType)
    {
        $this->employmentType = $employmentType;
    }

    /**
     * @return mixed
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * @param mixed $locale
     */
    public function setLocale($locale)
    {
        $this->locale = $locale;
    }

    public function getLocationTypes()
    {
        return $this->locationTypes;
    }
    public function addLocationType($type)
    {
        if( $type && !$this->locationTypes->contains($type) ) {
            $this->locationTypes->add($type);
        }

        return $this;
    }
    public function removeLocationType($type)
    {
        $this->locationTypes->removeElement($type);
    }

    /**
     * @return mixed
     */
    public function getAdministrativeTitleInstitution()
    {
        return $this->administrativeTitleInstitution;
    }

    /**
     * @param mixed $administrativeTitleInstitution
     */
    public function setAdministrativeTitleInstitution($administrativeTitleInstitution)
    {
        $this->administrativeTitleInstitution = $administrativeTitleInstitution;
    }

    /**
     * @return mixed
     */
    public function getAcademicTitleInstitution()
    {
        return $this->academicTitleInstitution;
    }

    /**
     * @param mixed $academicTitleInstitution
     */
    public function setAcademicTitleInstitution($academicTitleInstitution)
    {
        $this->academicTitleInstitution = $academicTitleInstitution;
    }

    /**
     * @return mixed
     */
    public function getMedicalTitleInstitution()
    {
        return $this->medicalTitleInstitution;
    }

    /**
     * @param mixed $medicalTitleInstitution
     */
    public function setMedicalTitleInstitution($medicalTitleInstitution)
    {
        $this->medicalTitleInstitution = $medicalTitleInstitution;
    }

    /**
     * @return mixed
     */
    public function getLocationInstitution()
    {
        return $this->locationInstitution;
    }

    /**
     * @param mixed $locationInstitution
     */
    public function setLocationInstitution($locationInstitution)
    {
        $this->locationInstitution = $locationInstitution;
    }

    /**
     * @return mixed
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * @param mixed $city
     */
    public function setCity($city)
    {
        $this->city = $city;
    }

    /**
     * @return mixed
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * @param mixed $state
     */
    public function setState($state)
    {
        $this->state = $state;
    }

    /**
     * @return mixed
     */
    public function getZip()
    {
        return $this->zip;
    }

    /**
     * @param mixed $zip
     */
    public function setZip($zip)
    {
        $this->zip = $zip;
    }

    /**
     * @return mixed
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * @param mixed $country
     */
    public function setCountry($country)
    {
        $this->country = $country;
    }

    /**
     * @return mixed
     */
    public function getMedicalLicenseCountry()
    {
        return $this->medicalLicenseCountry;
    }

    /**
     * @param mixed $medicalLicenseCountry
     */
    public function setMedicalLicenseCountry($medicalLicenseCountry)
    {
        $this->medicalLicenseCountry = $medicalLicenseCountry;
    }

    /**
     * @return mixed
     */
    public function getMedicalLicenseState()
    {
        return $this->medicalLicenseState;
    }

    /**
     * @param mixed $medicalLicenseState
     */
    public function setMedicalLicenseState($medicalLicenseState)
    {
        $this->medicalLicenseState = $medicalLicenseState;
    }

    /**
     * @return mixed
     */
    public function getEmploymentInstitution()
    {
        return $this->employmentInstitution;
    }

    /**
     * @param mixed $employmentInstitution
     */
    public function setEmploymentInstitution($employmentInstitution)
    {
        $this->employmentInstitution = $employmentInstitution;
    }



    /**
     * @param array $roles
     */
    public function setRoles(array $roles)
    {
        $this->roles = array();

        foreach ($roles as $role) {
            $this->addRole($role);
        }

        return $this;
    }
    /**
     * @return array
     */
    public function getRoles()
    {
        return $this->roles;
    }
    public function addRole($role) {
        $this->roles[] = $role."";
        return $this;
    }
    public function removeRole($role)
    {
        if (false !== $key = array_search(strtoupper($role), $this->roles, true)) {
            unset($this->roles[$key]);
            $this->roles = array_values($this->roles);
        }

        return $this;
    }
    public function hasRole($role)
    {
        return in_array(strtoupper($role), $this->roles, true);
    }

    

}