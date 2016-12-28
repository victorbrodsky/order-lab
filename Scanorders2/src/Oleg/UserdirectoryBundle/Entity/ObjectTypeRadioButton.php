<?php

namespace Oleg\UserdirectoryBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity
 * @ORM\Table(name="user_objectTypeRadioButton")
 */
class ObjectTypeRadioButton extends ObjectTypeReceivingBase
{

    /**
     * @ORM\OneToMany(targetEntity="ObjectTypeRadioButton", mappedBy="original")
     **/
    protected $synonyms;

    /**
     * @ORM\ManyToOne(targetEntity="ObjectTypeRadioButton", inversedBy="synonyms")
     * @ORM\JoinColumn(name="original_id", referencedColumnName="id")
     **/
    protected $original;


    /**
     * @ORM\ManyToOne(targetEntity="FormNode", inversedBy="objectTypeRadioButtons", cascade={"persist"})
     * @ORM\JoinColumn(name="formNode_id", referencedColumnName="id")
     */
    protected $formNode;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    protected $value;






}