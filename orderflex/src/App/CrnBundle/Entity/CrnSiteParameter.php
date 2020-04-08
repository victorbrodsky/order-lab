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

namespace App\CrnBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="crn_siteParameter")
 */
class CrnSiteParameter
{

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

//    /**
//     * @ORM\OneToOne(targetEntity="App\UserdirectoryBundle\Entity\SiteParameters", inversedBy="crnSiteParameter")
//     */
//    private $siteParameter;


//    /**
//     * @ORM\Column(type="text", nullable=true)
//     */
//    private $crnResources;

    /**
     * @ORM\ManyToOne(targetEntity="App\OrderformBundle\Entity\MrnType", cascade={"persist"})
     */
    private $keytypemrn;

    /**
     * @ORM\ManyToOne(targetEntity="App\OrderformBundle\Entity\MessageCategory", cascade={"persist"})
     */
    private $messageCategory;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $timezone;

    /**
     * @ORM\ManyToOne(targetEntity="App\UserdirectoryBundle\Entity\CityList")
     */
    private $city;

    /**
     * @ORM\ManyToOne(targetEntity="App\UserdirectoryBundle\Entity\States")
     **/
    private $state;

    /**
     * @ORM\ManyToOne(targetEntity="App\UserdirectoryBundle\Entity\Countries")
     **/
    private $country;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $county;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $zip;

    /**
     * @ORM\ManyToOne(targetEntity="App\OrderformBundle\Entity\PatientListHierarchy" )
     **/
    private $patientList;

//    /**
//     * @ORM\Column(type="string", nullable=true)
//     */
//    private $bodySearch1;
//
//    /**
//     * @ORM\Column(type="string", nullable=true)
//     */
//    private $bodySearch2;

    /**
     * Use cached values to display entry content preview in lists
     *
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $useCache;

    /**
     * @ORM\ManyToOne(targetEntity="App\UserdirectoryBundle\Entity\Institution")
     */
    private $institution;

    /**
     * enable/disable document upload section
     *
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $enableDocumentUpload;

    /**
     * @ORM\ManyToOne(targetEntity="App\OrderformBundle\Entity\AccessionType", cascade={"persist"})
     */
    private $defaultAccessionType;
    
    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $defaultAccessionPrefix;

    /**
     * Default Initial Communication
     *
     * @ORM\ManyToOne(targetEntity="App\UserdirectoryBundle\Entity\HealthcareProviderCommunicationList", cascade={"persist"})
     */
    private $defaultInitialCommunication;
    
    /**
     * Show Accession Number fields
     *
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $showAccession;

    /**
     * Show Accession Number on the Homepage
     *
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $showAccessionHome;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $crnResource;

    /**
     * @ORM\ManyToOne(targetEntity="App\OrderformBundle\Entity\MessageTagTypesList", cascade={"persist"})
     */
    private $defaultTagType;
    

    public function __construct() {
        $this->setEnableDocumentUpload(true);
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

//    /**
//     * @return mixed
//     */
//    public function getSiteParameter()
//    {
//        return $this->siteParameter;
//    }
//
//    /**
//     * @param mixed $siteParameter
//     */
//    public function setSiteParameter($siteParameter)
//    {
//        $this->siteParameter = $siteParameter;
//    }

    /**
     * @return mixed
     */
    public function getKeytypemrn()
    {
        return $this->keytypemrn;
    }

    /**
     * @param mixed $keytypemrn
     */
    public function setKeytypemrn($keytypemrn)
    {
        $this->keytypemrn = $keytypemrn;
    }

    /**
     * @return mixed
     */
    public function getMessageCategory()
    {
        return $this->messageCategory;
    }

    /**
     * @param mixed $messageCategory
     */
    public function setMessageCategory($messageCategory)
    {
        $this->messageCategory = $messageCategory;
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
    public function getCounty()
    {
        return $this->county;
    }

    /**
     * @param mixed $county
     */
    public function setCounty($county)
    {
        $this->county = $county;
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
    public function getPatientList()
    {
        return $this->patientList;
    }

    /**
     * @param mixed $patientList
     */
    public function setPatientList($patientList)
    {
        $this->patientList = $patientList;
    }

    /**
     * @return mixed
     */
    public function getUseCache()
    {
        return $this->useCache;
    }

    /**
     * @param mixed $useCache
     */
    public function setUseCache($useCache)
    {
        $this->useCache = $useCache;
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
    public function getEnableDocumentUpload()
    {
        return $this->enableDocumentUpload;
    }

    /**
     * @param mixed $enableDocumentUpload
     */
    public function setEnableDocumentUpload($enableDocumentUpload)
    {
        $this->enableDocumentUpload = $enableDocumentUpload;
    }

    /**
     * @return mixed
     */
    public function getDefaultAccessionType()
    {
        return $this->defaultAccessionType;
    }

    /**
     * @param mixed $defaultAccessionType
     */
    public function setDefaultAccessionType($defaultAccessionType)
    {
        $this->defaultAccessionType = $defaultAccessionType;
    }

    /**
     * @return mixed
     */
    public function getDefaultAccessionPrefix()
    {
        return $this->defaultAccessionPrefix;
    }

    /**
     * @param mixed $defaultAccessionPrefix
     */
    public function setDefaultAccessionPrefix($defaultAccessionPrefix)
    {
        $this->defaultAccessionPrefix = $defaultAccessionPrefix;
    }

    /**
     * @return mixed
     */
    public function getDefaultInitialCommunication()
    {
        return $this->defaultInitialCommunication;
    }

    /**
     * @param mixed $defaultInitialCommunication
     */
    public function setDefaultInitialCommunication($defaultInitialCommunication)
    {
        $this->defaultInitialCommunication = $defaultInitialCommunication;
    }

    /**
     * @return mixed
     */
    public function getShowAccession()
    {
        return $this->showAccession;
    }

    /**
     * @param mixed $showAccession
     */
    public function setShowAccession($showAccession)
    {
        $this->showAccession = $showAccession;
    }

    /**
     * @return mixed
     */
    public function getShowAccessionHome()
    {
        return $this->showAccessionHome;
    }

    /**
     * @param mixed $showAccessionHome
     */
    public function setShowAccessionHome($showAccessionHome)
    {
        $this->showAccessionHome = $showAccessionHome;
    }

    /**
     * @return mixed
     */
    public function getCrnResource()
    {
        return $this->crnResource;
    }

    /**
     * @param mixed $crnResource
     */
    public function setCrnResource($crnResource)
    {
        $this->crnResource = $crnResource;
    }

    /**
     * @return mixed
     */
    public function getDefaultTagType()
    {
        return $this->defaultTagType;
    }

    /**
     * @param mixed $defaultTagType
     */
    public function setDefaultTagType($defaultTagType)
    {
        $this->defaultTagType = $defaultTagType;
    }

    


    

}