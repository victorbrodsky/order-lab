<?php
/**
 * Created by JetBrains PhpStorm.
 * User: oli2002
 * Date: 4/22/14
 * Time: 10:19 AM
 * To change this template use File | Settings | File Templates.
 */

namespace Oleg\UserdirectoryBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity
 * @ORM\Table(name="user_userPosition")
 */
class UserPosition {

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="Institution", inversedBy="userPositions", cascade={"persist","remove"})
     * @ORM\JoinColumn(name="institution_id", referencedColumnName="id", onDelete="CASCADE", nullable=true)
     */
    protected $institution;

    /**
     * User object
     * @ORM\ManyToOne(targetEntity="User")
     */
    private $user;

    /**
     * //Position Type: Head, Manager, Primary Contact, Transcriptionist
     * @ORM\ManyToMany(targetEntity="PositionTypeList", inversedBy="userPositions")
     * @ORM\JoinTable(name="user_userPositions_positionTypes")
     **/
    private $positionTypes;



    public function __construct() {
        $this->positionTypes = new ArrayCollection();
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
     * @param mixed $institution
     */
    public function setInstitution($institution)
    {
        $this->institution = $institution;
    }

    /**
     * @return mixed
     */
    public function getInstitution()
    {
        return $this->institution;
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

    public function addPositionType($item)
    {
        if( !$this->positionTypes->contains($item) ) {
            $this->positionTypes->add($item);
        }
        return $this;
    }
    public function removePositionType($item)
    {
        $this->positionTypes->removeElement($item);
    }
    public function getPositionTypes()
    {
        return $this->positionTypes;
    }



    public function __toString() {
        return $this->getFullName();
    }

    public function getFullName() {
        $fullName = "";

        if( $this->getUser() ) {
            $fullName = $fullName . $this->getUser()."";
        }

        //institution
        if( $this->getInstitution() ) {
            $fullName = $fullName . ", Institution:" . $this->getInstitution(); //."(".$this->getInstitution()->getId().")";
        }

        //positions
        $positions = $this->getPositionTypes();
        $positionsArr = array();
        foreach( $positions as $position ) {
            $positionsArr[] = $position;
        }

        if( count($positions) > 0 ) {
            if( $fullName ) {
                $fullName = $fullName . ", Positions:" .implode(",", $positionsArr)."";
            } else {
                $fullName = " Positions:" . implode(",", $positionsArr)."";
            }
        }

        return $fullName;
    }


}