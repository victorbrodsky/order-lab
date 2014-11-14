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
 * @ORM\Table(name="scan_directorWrapper")
 */
class DirectorWrapper {

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $directorStr;

    /**
     * @ORM\ManyToOne(targetEntity="DirectorList")
     * @ORM\JoinColumn(name="director_id", referencedColumnName="id", nullable=true)
     */
    protected $director;

    /**
     * @ORM\ManyToOne(targetEntity="Educational", inversedBy="directorWrappers", cascade={"persist"})
     **/
    protected $educational;


    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $director
     */
    public function setDirector($director)
    {
        $this->director = $director;
    }

    /**
     * @return mixed
     */
    public function getDirector()
    {
        return $this->director;
    }

    /**
     * @param mixed $directorStr
     */
    public function setDirectorStr($directorStr)
    {
        $this->directorStr = $directorStr;
    }

    /**
     * @return mixed
     */
    public function getDirectorStr()
    {
        return $this->directorStr;
    }

    /**
     * @param mixed $educational
     */
    public function setEducational($educational)
    {
        $this->educational = $educational;
    }

    /**
     * @return mixed
     */
    public function getEducational()
    {
        return $this->educational;
    }

    public function __toString(){
        return "DirectorWrapper: id=".$this->getDirector()->getId().", directorStr=".$this->directorStr."<br>";
    }

    public function getCourseInfo() {
        $info = $this->getEducational()->getCourseTitleStr();
        return $info;
    }

}