<?php

namespace Oleg\OrderformBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;


/**
 * @ORM\Entity
 * @ORM\Table(name="scan_imageAnalysisOrder")
 */
class ImageAnalysisOrder extends OrderBase {

    /**
     * @ORM\OneToOne(targetEntity="Message", mappedBy="imageAnalysisOrder")
     **/
    protected $message;

    /**
     * Image Analysis Algorithm (http://indicalab.com/products/)
     *
     * @ORM\ManyToOne(targetEntity="ImageAnalysisAlgorithmList", cascade={"persist"})
     */
    private $imageAnalysisAlgorithm;



    /**
     * @param mixed $imageAnalysisAlgorithm
     */
    public function setImageAnalysisAlgorithm($imageAnalysisAlgorithm)
    {
        $this->imageAnalysisAlgorithm = $imageAnalysisAlgorithm;
    }

    /**
     * @return mixed
     */
    public function getImageAnalysisAlgorithm()
    {
        return $this->imageAnalysisAlgorithm;
    }



}