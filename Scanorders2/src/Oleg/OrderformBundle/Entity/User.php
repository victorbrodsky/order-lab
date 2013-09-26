<?php

namespace Oleg\OrderformBundle\Entity;

use FOS\UserBundle\Model\User as BaseUser;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping\AttributeOverrides;
use Doctrine\ORM\Mapping\AttributeOverride;

//use FR3D\LdapBundle\Model\LdapUserInterface;

/**
 * use FOSUser bundle: https://github.com/FriendsOfSymfony/FOSUserBundle/blob/master/Resources/doc/index.md
 * User is a reserved keyword in SQL so you cannot use it as table name
 * @ORM\Entity
 * @ORM\Table(name="fos_user")
 *
 * @AttributeOverrides({
 *     @AttributeOverride(name="emailCanonical",
 *         column=@ORM\Column(
 *             name="emailCanonical",
 *             type="string",
 *             length=255,
 *             nullable=true
 *         )
 *     ),
 *     @AttributeOverride(name="email",
 *         column=@ORM\Column(
 *             name="email",
 *             type="string",
 *             length=255,
 *             nullable=true
 *         )
 *     )
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

    /**
     * @ORM\Column(name="phone", type="string", nullable=true)
     */
    protected $phone;

    
//    /**
//     * Ldap Object Distinguished Name
//     * @var string $dn
//     * Ldap Object Distinguished Name
//     * @var string $dn
//     * @ORM\Column(name="dn", type="string")
//     * @Assert\NotBlank
//     */
//    protected $dn;

    public function __construct()
    {
        parent::__construct();
        // your own logic
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

    
//    /**
//     * {@inheritDoc}
//     */
//    public function setDn($dn)
//    {
//        $this->dn = $dn;
//    }
//
//    /**
//     * {@inheritDoc}
//     */
//    public function getDn()
//    {
//        return $this->dn;
//    }

//    /**
//     * @param mixed $phone
//     */
//    public function setPhone($phone)
//    {
//        $this->phone = $phone;
//    }
//
//    /**
//     * @return mixed
//     */
//    public function getPhone()
//    {
//        return $this->phone;
//    }



}

?>
