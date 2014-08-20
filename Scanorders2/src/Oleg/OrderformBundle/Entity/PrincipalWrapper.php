<?php
/**
 * Created by JetBrains PhpStorm.
 * User: oli2002
 * Date: 4/22/14
 * Time: 10:19 AM
 * To change this template use File | Settings | File Templates.
 */

namespace Oleg\OrderformBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity
 */
class PrincipalWrapper {

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $principalStr;

    /**
     * @ORM\ManyToOne(targetEntity="Oleg\UserdirectoryBundle\Entity\PIList")
     * @ORM\JoinColumn(name="principal_id", referencedColumnName="id", nullable=true)
     */
    protected $principal;

    /**
     * @ORM\ManyToOne(targetEntity="Research", inversedBy="principalWrappers", cascade={"persist"})
     **/
    protected $research;


    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $principal
     */
    public function setPrincipal($principal)
    {
        $this->principal = $principal;
    }

    /**
     * @return mixed
     */
    public function getPrincipal()
    {
        return $this->principal;
    }

    /**
     * @param mixed $principalStr
     */
    public function setPrincipalStr($principalStr)
    {
        $this->principalStr = $principalStr;
    }

    /**
     * @return mixed
     */
    public function getPrincipalStr()
    {
        return $this->principalStr;
    }

    /**
     * @param mixed $research
     */
    public function setResearch($research)
    {
        $this->research = $research;
    }

    /**
     * @return mixed
     */
    public function getResearch()
    {
        return $this->research;
    }

    public function __toString(){
        return "PrincipalWrapper: id=".$this->getPrincipal()->getId().", principalStr=".$this->principalStr."<br>";
    }

}