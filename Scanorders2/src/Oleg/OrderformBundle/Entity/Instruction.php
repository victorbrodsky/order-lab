<?php
/**
 * Created by JetBrains PhpStorm.
 * User: oli2002
 * Date: 20/07/15
 * Time: 10:10 AM
 * To change this template use File | Settings | File Templates.
 */

namespace Oleg\OrderformBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

use Symfony\Component\Validator\Constraints as Assert;


/**
 * @ORM\Entity
 * @ORM\Table(name="scan_instruction")
 */
class Instruction
{

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="Oleg\UserdirectoryBundle\Entity\User")
     * @ORM\JoinColumn(name="creator_id", referencedColumnName="id")
     * @Assert\NotBlank
     */
    private $creator;

    /**
     * @var \DateTime
     * @ORM\Column(name="createdate", type="datetime")
     * @Assert\NotBlank
     */
    private $createdate;

    /**
     * @var array
     * @ORM\Column(type="array", nullable=true)
     */
    private $creatorRoles = array();

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $instruction;



    public function __construct( $creator = null ) {
        $this->setCreatedate(new \DateTime());

        if( $creator ) {
            $this->setCreator($creator);
        }
    }



    /**
     * @param \DateTime $createdate
     */
    public function setCreatedate($createdate)
    {
        $this->createdate = $createdate;
    }

    /**
     * @return \DateTime
     */
    public function getCreatedate()
    {
        return $this->createdate;
    }

    /**
     * @param mixed $creator
     */
    public function setCreator($creator)
    {
        $this->creator = $creator;

        if( count($this->getCreatorRoles()) == 0 ) {
            $this->setCreatorRoles($creator->getRoles());
        }
    }

    /**
     * @return mixed
     */
    public function getCreator()
    {
        return $this->creator;
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
     * @param mixed $instruction
     */
    public function setInstruction($instruction)
    {
        $this->instruction = $instruction;
    }

    /**
     * @return mixed
     */
    public function getInstruction()
    {
        return $this->instruction;
    }



    /**
     * @return mixed
     */
    public function getCreatorRoles()
    {
        return $this->creatorRoles;
    }

    public function setCreatorRoles($roles) {
        foreach( $roles as $role ) {
            $this->addCreatorRole($role."");
        }
    }

    public function addCreatorRole($role) {
        $role = strtoupper($role);
        if( !in_array($role, $this->creatorRoles, true) ) {
            $this->creatorRoles[] = $role;
        }
    }




}