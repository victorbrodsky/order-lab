<?php
/**
 * Created by JetBrains PhpStorm.
 * User: oli2002
 * Date: 9/10/13
 * Time: 5:46 PM
 * To change this template use File | Settings | File Templates.
 */

namespace Oleg\OrderformBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Form\Extension\Core\DataTransformer\DateTimeToStringTransformer;

/**
 * @ORM\Entity
 * @ORM\Table(name="scan_accessionDate")
 */
class AccessionAccessionDate extends AccessionArrayFieldAbstract
{
    /**
     * @ORM\ManyToOne(targetEntity="Accession", inversedBy="accessionDate", cascade={"persist"})
     * @ORM\JoinColumn(name="accession_id", referencedColumnName="id", onDelete="CASCADE", nullable=true)
     */
    protected $accession;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $field;

    /**
     * @ORM\Column(type="time", nullable=true)
     */
    private $time;


    public function __toString() {
        $transformer = new DateTimeToStringTransformer(null,null,'m/d/Y');
        return $dateStr = $transformer->transform($this->field);
    }




    /**
     * @param mixed $time
     */
    public function setTime($time)
    {
        $this->time = $time;
    }

    /**
     * @return mixed
     */
    public function getTime()
    {
        return $this->time;
    }




}