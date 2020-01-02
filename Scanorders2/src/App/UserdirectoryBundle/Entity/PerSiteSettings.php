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

namespace Oleg\UserdirectoryBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

use Doctrine\Common\Collections\ArrayCollection;

use Oleg\UserdirectoryBundle\Entity\BaseUserAttributes;

/**
 * @ORM\Entity
 * @ORM\Table(
 *  name="scan_perSiteSettings",
 *  indexes={
 *      @ORM\Index( name="fosuser_idx", columns={"fosuser"} ),
 *  }
 * )
 */
class PerSiteSettings extends BaseUserAttributes
{

    /**
     * @ORM\ManyToMany(targetEntity="Institution")
     * @ORM\JoinTable(name="scan_perSiteSettings_institution",
     *      joinColumns={@ORM\JoinColumn(name="perSiteSettings_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="institution_id", referencedColumnName="id")}
     *      )
     **/
    private $permittedInstitutionalPHIScope;


    /**
     * Service scope: service means one of the node of the institutional tree.
     * This scope allows to view only an existing order by url
     *
     * @ORM\ManyToMany(targetEntity="Institution")
     * @ORM\JoinTable(name="scan_perSiteSettings_service",
     *      joinColumns={@ORM\JoinColumn(name="perSiteSettings_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="service_id", referencedColumnName="id")}
     *      )
     **/
    private $scanOrdersServicesScope;

    /**
     * This scope allows to view, cancel or amend an existing order by order url.
     *
     * @ORM\ManyToMany(targetEntity="Institution")
     * @ORM\JoinTable(name="scan_chiefServices_service",
     *      joinColumns={@ORM\JoinColumn(name="perSiteSettings_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="service_id", referencedColumnName="id")}
     *      )
     **/
    private $chiefServices;

    /**
     * @ORM\ManyToOne(targetEntity="Institution")
     * @ORM\JoinColumn(name="institution_id", referencedColumnName="id")
     **/
    private $defaultInstitution;



//    /**
//     * defaultInstitution (ScanOrders Institution Scope)
//     *
//     * @ORM\ManyToOne(targetEntity="Oleg\UserdirectoryBundle\Entity\Institution")
//     * @ORM\JoinColumn(name="institution_id", referencedColumnName="id")
//     **/
//    private $scanOrderInstitutionsScope;

//    /**
//     * @ORM\ManyToOne(targetEntity="Oleg\UserdirectoryBundle\Entity\Department")
//     * @ORM\JoinColumn(name="department_id", referencedColumnName="id")
//     **/
//    private $defaultDepartment;
//
//    /**
//     * @ORM\ManyToOne(targetEntity="Oleg\UserdirectoryBundle\Entity\Division")
//     * @ORM\JoinColumn(name="division_id", referencedColumnName="id")
//     **/
//    private $defaultDivision;
//
//    /**
//     * @ORM\ManyToOne(targetEntity="Oleg\UserdirectoryBundle\Entity\Service")
//     * @ORM\JoinColumn(name="service_id", referencedColumnName="id")
//     **/
//    private $defaultService;

    /**
     * @ORM\OneToOne(targetEntity="User", inversedBy="perSiteSettings")
     * @ORM\JoinColumn(name="fosuser", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $user;


    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    protected $tooltip;


    /**
     * Organizational Group for new user's default values in Employee Directory
     * @ORM\ManyToOne(targetEntity="Institution")
     */
    private $organizationalGroupDefault;



    public function __construct() {

        parent::__construct();

        $this->permittedInstitutionalPHIScope = new ArrayCollection();
        $this->scanOrdersServicesScope = new ArrayCollection();
        $this->chiefServices = new ArrayCollection();
        $this->setType(self::TYPE_RESTRICTED);
        $this->tooltip = true;

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
//     * @param mixed $defaultService
//     */
//    public function setDefaultService($defaultService)
//    {
//        $this->defaultService = $defaultService;
//    }
//
//    /**
//     * @return mixed
//     */
//    public function getDefaultService()
//    {
//        return $this->defaultService;
//    }
//
//    /**
//     * @param mixed $defaultDepartment
//     */
//    public function setDefaultDepartment($defaultDepartment)
//    {
//        $this->defaultDepartment = $defaultDepartment;
//    }
//
//    /**
//     * @return mixed
//     */
//    public function getDefaultDepartment()
//    {
//        return $this->defaultDepartment;
//    }
//
//    /**
//     * @param mixed $defaultDivision
//     */
//    public function setDefaultDivision($defaultDivision)
//    {
//        $this->defaultDivision = $defaultDivision;
//    }
//
//    /**
//     * @return mixed
//     */
//    public function getDefaultDivision()
//    {
//        return $this->defaultDivision;
//    }
//    /**
//     * @param mixed $defaultInstitution
//     */
//    public function setDefaultInstitution($defaultInstitution)
//    {
//        $this->defaultInstitution = $defaultInstitution;
//    }
//
//    /**
//     * @return mixed
//     */
//    public function getDefaultInstitution()
//    {
//        return $this->defaultInstitution;
//    }




    //permittedInstitutionalPHIScope
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



    //ScanOrdersServicesScope
    public function getScanOrdersServicesScope()
    {
        return $this->scanOrdersServicesScope;
    }

    public function addScanOrdersServicesScope( $scanOrdersServicesScope )
    {
        if( !$this->scanOrdersServicesScope->contains($scanOrdersServicesScope) ) {
            $this->scanOrdersServicesScope->add($scanOrdersServicesScope);
        }

    }

    public function removeScanOrdersServicesScope($scanOrdersServicesScope)
    {
        $this->scanOrdersServicesScope->removeElement($scanOrdersServicesScope);
    }

    //getScanOrderInstitutionScope
    public function getScanOrderInstitutionScope()
    {
        return $this->getScanOrdersServicesScope();
    }


    //chiefServices
    public function getChiefServices()
    {
        return $this->chiefServices;
    }

    public function addChiefService( $chiefService )
    {
        if( !$this->chiefServices->contains($chiefService) ) {
            $this->chiefServices->add($chiefService);
        }

    }

    public function removeChiefService($chiefService)
    {
        $this->chiefServices->removeElement($chiefService);
    }

    /**
     * @param mixed $tooltip
     */
    public function setTooltip($tooltip)
    {
        $this->tooltip = $tooltip;
    }

    /**
     * @return mixed
     */
    public function getTooltip()
    {
        return $this->tooltip;
    }

    /**
     * @param mixed $defaultInstitution
     */
    public function setDefaultInstitution($defaultInstitution)
    {
        $this->defaultInstitution = $defaultInstitution;
    }

    /**
     * @return mixed
     */
    public function getDefaultInstitution()
    {
        return $this->defaultInstitution;
    }

    /**
     * @return mixed
     */
    public function getOrganizationalGroupDefault()
    {
        return $this->organizationalGroupDefault;
    }

    /**
     * @param mixed $organizationalGroupDefault
     */
    public function setOrganizationalGroupDefault($organizationalGroupDefault)
    {
        $this->organizationalGroupDefault = $organizationalGroupDefault;
    }




}