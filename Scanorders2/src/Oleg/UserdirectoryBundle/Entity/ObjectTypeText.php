<?php

namespace Oleg\UserdirectoryBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity
 * @ORM\Table(name="user_objectTypeText")
 */
class ObjectTypeText extends ObjectTypeReceivingBase
{

    /**
     * @ORM\OneToMany(targetEntity="ObjectTypeText", mappedBy="original")
     **/
    protected $synonyms;

    /**
     * @ORM\ManyToOne(targetEntity="ObjectTypeText", inversedBy="synonyms")
     * @ORM\JoinColumn(name="original_id", referencedColumnName="id")
     **/
    protected $original;


//    /**
//     * @ORM\OneToMany(targetEntity="FormNode", mappedBy="objectTypeText")
//     */
//    private $formNodes;
//    /**
//     * @ORM\ManyToOne(targetEntity="ObjectTypeText", inversedBy="formNodes", cascade={"persist"})
//     * @ORM\JoinColumn(name="objectTypeText_id", referencedColumnName="id")
//     */
//    private $objectTypeText;
    /**
     * @ORM\ManyToOne(targetEntity="FormNode", inversedBy="objectTypeTexts", cascade={"persist"})
     * @ORM\JoinColumn(name="formNode_id", referencedColumnName="id")
     */
    protected $formNode;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    protected $value;






}