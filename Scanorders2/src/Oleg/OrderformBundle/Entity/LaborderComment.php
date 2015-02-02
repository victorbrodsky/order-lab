<?php

namespace Oleg\OrderformBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

use Oleg\UserdirectoryBundle\Entity\ListAbstract;
use Oleg\UserdirectoryBundle\Entity\User;

/**
 * @ORM\Entity
 * @ORM\Table(name="scan_laborderComment")
 */
class LaborderComment
{

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="AccessionLaborder", inversedBy="imageComments")
     * @ORM\JoinColumn(name="laborder_id", referencedColumnName="id")
     */
    private $laborder;




    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $laborder
     */
    public function setLaborder($laborder)
    {
        $this->laborder = $laborder;
    }

    /**
     * @return mixed
     */
    public function getLaborder()
    {
        return $this->laborder;
    }


}