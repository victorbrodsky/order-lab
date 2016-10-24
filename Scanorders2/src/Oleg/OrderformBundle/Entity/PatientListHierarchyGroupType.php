<?php

namespace Oleg\OrderformBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

use Oleg\UserdirectoryBundle\Entity\ListAbstract;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * "Message Type Classifiers" with a url of /list/message-type-classifiers
 * @ORM\Entity
 * @UniqueEntity(
 *     fields={"level"},
 *     errorPath="level",
 *     message="This Default Tree Level Association Type is already associated with another tree level. Please remove that association or enter a different tree level."
 * )
 * @ORM\Table(name="scan_patientListHierarchyGroupType")
 */
class PatientListHierarchyGroupType extends ListAbstract
{

    /**
     * @ORM\OneToMany(targetEntity="PatientListHierarchyGroupType", mappedBy="original")
     **/
    protected $synonyms;

    /**
     * @ORM\ManyToOne(targetEntity="PatientListHierarchyGroupType", inversedBy="synonyms")
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