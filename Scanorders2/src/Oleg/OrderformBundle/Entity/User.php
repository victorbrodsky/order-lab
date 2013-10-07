<?php

namespace Oleg\OrderformBundle\Entity;

use FOS\UserBundle\Model\User as BaseUser;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping\AttributeOverrides;
use Doctrine\ORM\Mapping\AttributeOverride;

//use FR3D\LdapBundle\Model\LdapUserInterface;

/**
 * use FOSUser bundle: https://github.com/FriendsOfSymfony/FOSUserBundle/blob/master/Resources/doc/index.md
 * User is a reserved keyword in SQL so you cannot use it as table name
 * @ORM\Entity
 * @ORM\Table(name="fos_user")
 * @ORM\AttributeOverrides({
 *      @ORM\AttributeOverride(name="email", column=@ORM\Column(type="string", name="email", length=255, unique=false, nullable=true)),
 *      @ORM\AttributeOverride(name="emailCanonical", column=@ORM\Column(type="string", name="email_canonical", length=255, unique=false, nullable=true))
 * })
 */
class User extends BaseUser
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

//    /**
//     * @ORM\ManyToMany(targetEntity="Group")
//     * @ORM\JoinTable(name="fos_user_user_group",
//     *      joinColumns={@ORM\JoinColumn(name="user_id", referencedColumnName="id")},
//     *      inverseJoinColumns={@ORM\JoinColumn(name="group_id", referencedColumnName="id")}
//     * )
//     */
//    protected $groups;

    /**
     * @ORM\ManyToMany(targetEntity="PathServiceList")
     * @ORM\JoinTable(name="fos_user_pathservice",
     *      joinColumns={@ORM\JoinColumn(name="user_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="pathservice_id", referencedColumnName="id")}
     * )
     */
    protected $pathologyServices;

//    /**
//     * @ORM\Column(name="defaultPathService", type="string", nullable=true)
//     */
//    protected $defaultPathService;

    /**
     * @ORM\Column(name="phone", type="string", nullable=true)
     */
    protected $phone;

    /**
     * @ORM\Column(name="firstName", type="string", nullable=true)
     */
    protected $firstName;

    /**
     * @ORM\Column(name="lastName", type="string", nullable=true)
     */
    protected $lastName;

    /**
     * @ORM\Column(name="title", type="string", nullable=true)
     */
    protected $title;

    /**
     * @ORM\Column(name="displayName", type="string", nullable=true)
     */
    protected $displayName;

    /**
     * @ORM\Column(name="fax", type="string", nullable=true)
     */
    protected $fax;

    /**
     * @ORM\Column(name="office", type="string", nullable=true)
     */
    protected $office;

    function __construct()
    {
        $this->pathologyServices = new ArrayCollection();
        //$this->defaultPathService = 0;
        parent::__construct();
    }

    /**
     * @param mixed $pathologyServices
     */
    public function setPathologyServices($pathologyServices)
    {
        $this->pathologyServices = $pathologyServices;
    }

    /**
     * @return mixed
     */
    public function getPathologyServices()
    {
        return $this->pathologyServices;
    }

    /**
     * @param mixed $phone
     */
    public function setPhone($phone)
    {
        $this->phone = $phone;
    }

    /**
     * @return mixed
     */
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * @param mixed $firstName
     */
    public function setFirstName($firstName)
    {
        $this->firstName = $firstName;
    }

    /**
     * @return mixed
     */
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * @param mixed $lastName
     */
    public function setLastName($lastName)
    {
        $this->lastName = $lastName;
    }

    /**
     * @return mixed
     */
    public function getLastName()
    {
        return $this->lastName;
    }

    /**
     * @param mixed $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * @return mixed
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param mixed $displayName
     */
    public function setDisplayName($displayName)
    {
        $this->displayName = $displayName;
    }

    /**
     * @return mixed
     */
    public function getDisplayName()
    {
        return $this->displayName;
    }

    /**
     * @param mixed $fax
     */
    public function setFax($fax)
    {
        $this->fax = $fax;
    }

    /**
     * @return mixed
     */
    public function getFax()
    {
        return $this->fax;
    }

    /**
     * @param mixed $office
     */
    public function setOffice($office)
    {
        $this->office = $office;
    }

    /**
     * @return mixed
     */
    public function getOffice()
    {
        return $this->office;
    }

    public function addPathologyServices(\Oleg\OrderformBundle\Entity\PathServiceList $pathologyServices)
    {
        if( !$this->pathologyServices->contains($pathologyServices) ) {
            $this->pathologyServices[] = $pathologyServices;
        }

        return $this;
    }

    public function removePathologyServices(\Oleg\OrderformBundle\Entity\PathServiceList $pathologyServices)
    {
        $this->pathologyServices->removeElement($pathologyServices);
    }

//    /**
//     * @param mixed $defaultPathService
//     */
//    public function setDefaultPathService($defaultPathService)
//    {
//        $this->defaultPathService = $defaultPathService;
//    }
//
//    /**
//     * @return mixed
//     */
//    public function getDefaultPathService()
//    {
//        return $this->defaultPathService;
//    }


//    public function __toString() {
//        return "User: ".$this->username.", email=".$this->email.", PathServiceList count=".count($this->pathologyServices)."<br>";
//    }

}

?>
