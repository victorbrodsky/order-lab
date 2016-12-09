<?php

namespace Oleg\UserdirectoryBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity
 * @ORM\Table(name="user_objectTypeDateTime")
 */
class ObjectTypeDateTime extends ListAbstract
{

    /**
     * @ORM\OneToMany(targetEntity="ObjectTypeDateTime", mappedBy="original")
     **/
    protected $synonyms;

    /**
     * @ORM\ManyToOne(targetEntity="ObjectTypeDateTime", inversedBy="synonyms")
     * @ORM\JoinColumn(name="original_id", referencedColumnName="id")
     **/
    protected $original;

    /**
     * @ORM\ManyToOne(targetEntity="FormNode", inversedBy="objectTypeDateTimes", cascade={"persist"})
     * @ORM\JoinColumn(name="formNode_id", referencedColumnName="id")
     */
    private $formNode;



    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $value;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $datetimeValue;

    /**
     * @ORM\Column(type="time", nullable=true)
     */
    private $timeValue;



    public function __construct( $creator = null ) {
        parent::__construct($creator);
    }



    /**
     * @return mixed
     */
    public function getFormNode()
    {
        return $this->formNode;
    }

    /**
     * @param mixed $formNode
     */
    public function setFormNode($formNode)
    {
        $this->formNode = $formNode;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param mixed $value
     */
    public function setValue($value)
    {
//        if( is_object($value) ) {
//            //
//        } else {
//            if( $timezoneStr ) {
//                $value = new \DateTime($value, new \DateTimeZone($timezoneStr)); //'Pacific/Nauru'
//            } else {
//                $value = new \DateTime($value);
//            }
//        }
        $this->value = $value;
    }

    /**
     * @return mixed
     */
    public function getDatetimeValue()
    {
        return $this->datetimeValue;
    }

    /**
     * @param mixed $datetimeValue
     */
    public function setDatetimeValue($datetimeValue)
    {
        $this->datetimeValue = $datetimeValue;
    }

    /**
     * @return mixed
     */
    public function getTimeValue()
    {
        return $this->timeValue;
    }

    /**
     * @param mixed $timeValue
     */
    public function setTimeValue($timeValue)
    {
        $this->timeValue = $timeValue;
    }


    public function setTimeValueHourMinute($hour,$minute,$second=null)
    {
        //echo "hour=".$hour."; minute=".$minute."<br>";
        $datetimeValue = new \DateTime();
        $datetimeValue->setTime($hour, $minute, $second);
        $this->setTimeValue($datetimeValue);
        //echo "time=".$this->getTimeValue()->format('h:i:s')."<br>";
    }

}