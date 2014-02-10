<?php

namespace Oleg\OrderformBundle\Entity;

use Doctrine\ORM\Mapping as ORM;


/**
 * @ORM\Entity
 * @ORM\Table(name="siteParameters")
 */
class SiteParameters {

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="string")
     */
    protected $maxIdleTime;

    /**
     * @param mixed $maxIdleTime
     */
    public function setMaxIdleTime($maxIdleTime)
    {
        $this->maxIdleTime = $maxIdleTime;
    }

    /**
     * @return mixed
     */
    public function getMaxIdleTime()
    {
        return $this->maxIdleTime;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }


}