<?php

namespace Oleg\OrderformBundle\Entity;

use FOS\UserBundle\Model\User as BaseUser;
use Doctrine\ORM\Mapping as ORM;

//use FR3D\LdapBundle\Model\LdapUserInterface;

/**
 * use FOSUser bundle: https://github.com/FriendsOfSymfony/FOSUserBundle/blob/master/Resources/doc/index.md
 * User is a reserved keyword in SQL so you cannot use it as table name
 * @ORM\Entity
 * @ORM\Table(name="fos_user")
 */
class User extends BaseUser
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    public function __construct()
    {
        parent::__construct();
        // your own logic
    }
    
    /**
     * Ldap Object Distinguished Name
     * @var string $dn
     */
//    private $dn;
    
    /**
     * {@inheritDoc}
     */
//    public function setDn($dn)
//    {
//        $this->dn = $dn;
//    }

    /**
     * {@inheritDoc}
     */
//    public function getDn()
//    {
//        return $this->dn;
//    }
}

?>
