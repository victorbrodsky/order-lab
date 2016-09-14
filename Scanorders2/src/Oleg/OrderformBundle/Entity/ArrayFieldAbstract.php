<?php
/**
 * Created by JetBrains PhpStorm.
 * User: oli2002
 * Date: 10/24/13
 * Time: 12:14 PM
 * To change this template use File | Settings | File Templates.
 */

namespace Oleg\OrderformBundle\Entity;

use Doctrine\ORM\Mapping as ORM;


/**
 * @ORM\MappedSuperclass
 * @ORM\HasLifecycleCallbacks
 */
abstract class ArrayFieldAbstract {

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

     /**
     * @ORM\ManyToOne(targetEntity="Oleg\UserdirectoryBundle\Entity\User")
     * @ORM\JoinColumn(name="provider", referencedColumnName="id")
     */
    protected $provider;

    /**
     * status: valid, invalid, alias
     * @ORM\Column(type="string", nullable=true)
     */
    protected $status;

    //default: 'scanorder'. Other values (old): "import_from_Epic", "import_from_CoPath"
    /**
     * @ORM\ManyToOne(targetEntity="Oleg\UserdirectoryBundle\Entity\SourceSystemList")
     * @ORM\JoinColumn(name="source_id", referencedColumnName="id", nullable=true)
     */
    protected $source;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $creationdate;

    /**
     * @ORM\ManyToOne(targetEntity="Message", cascade={"persist"})
     * @ORM\JoinColumn(name="message", referencedColumnName="id", nullable=true)
     */
    protected $message;


    /**
     * @ORM\OneToOne(targetEntity="DataQualityEventLog")
     * @ORM\JoinColumn(name="dqeventlog", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $dqeventlog;

    private $className;

    public function __construct( $status = 'valid', $provider = null, $source = null )
    {
        $this->status = $status;
        $this->provider = $provider;
        $this->source = $source;

        $class = new \ReflectionClass($this);
        $this->className = $class->getShortName();
    }

    public function __clone() {
        if( $this->getId() ) {
            //echo "field ".$this->getId()." set id to null <br>";
            $this->setId(null);
        }
    }

    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    public function getId() {
        return $this->id;
    }

    /**
     * @ORM\PrePersist
     */
    public function setCreationdate()
    {
        $this->creationdate = new \DateTime();
    }

    /**
     * @return \DateTime
     */
    public function getCreationdate()
    {
        return $this->creationdate;
    }

    public function setProvider($provider)
    {
        $this->provider = $provider;

        return $this;
    }

    public function getProvider()
    {
        return $this->provider;
    }

    /**
     * @param mixed $status
     */
    public function setStatus($status)
    {
        $this->setFieldChangeArray("status",$this->status,$status);
        $this->status = $status;
    }

    /**
     * @return mixed
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param mixed $source
     */
    public function setSource($source)
    {
        $this->source = $source;
    }

    /**
     * @return mixed
     */
    public function getSource()
    {
        return $this->source;
    }

    /**
     * @param mixed $message
     */
    public function setMessage($message)
    {
        $this->message = $message;
    }

    /**
     * @return mixed
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @param \Oleg\OrderformBundle\Entity\DataQualityEventLog $dqeventlog
     */
    public function setDqeventlog(DataQualityEventLog $dqeventlog)
    {
        $this->dqeventlog = $dqeventlog;
    }

    /**
     * @return mixed
     */
    public function getDqeventlog()
    {
        return $this->dqeventlog;
    }


    public function setFieldChangeArray($fieldName,$oldValue,$newValue) {
        if( $oldValue != $newValue ) {
            //echo "2 setStatus old=".$this->status."; new=".$status."<br>";
            if( $this->className ) {
                $className = $this->className;
            } else {
                $class = new \ReflectionClass($this);
                $className = $class->getShortName();
            }
            //echo $className.": parent id=".$this->getParent()->getId()."<br>";

            if( $this->getParent() ) {
                $changeObjectArr = $this->getParent()->obtainChangeObjectArr();

                $changeObjectArr[$className][$this->getId()][$fieldName]['old'] = $oldValue;
                $changeObjectArr[$className][$this->getId()][$fieldName]['new'] = $newValue;

                $this->getParent()->setChangeObjectArr($changeObjectArr);
            }
        }
    }


    public function __toString() {
        return $this->field."";
    }

}