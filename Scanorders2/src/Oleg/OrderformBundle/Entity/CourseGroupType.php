<?php

namespace Oleg\OrderformBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Oleg\UserdirectoryBundle\Entity\ListAbstract;

/**
 * "Organizational Group Types" with a url of /list/organizational-group-types
 * @ORM\Entity
 * @ORM\Table(name="scan_courseGroupType")
 */
class CourseGroupType extends ListAbstract
{

    /**
     * @ORM\OneToMany(targetEntity="CourseGroupType", mappedBy="original")
     **/
    protected $synonyms;

    /**
     * @ORM\ManyToOne(targetEntity="CourseGroupType", inversedBy="synonyms")
     * @ORM\JoinColumn(name="original_id", referencedColumnName="id")
     **/
    protected $original;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $level;

    //name is the level title: Institution, Division, Department, Service



    /**
     * @param mixed $level
     */
    public function setLevel($level)
    {
        $this->level = $level;
    }

    /**
     * @return mixed
     */
    public function getLevel()
    {
        return $this->level;
    }




}