<?php

namespace Oleg\OrderformBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Form\Extension\Core\DataTransformer\DateTimeToStringTransformer;

use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity
 * @ORM\Table(name="scan_slideReturnRequest")
 */
class SlideReturnRequest extends OrderAbstract {

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $status;

    /**
     * @var string
     * @ORM\Column(name="returnSlide", type="string", nullable=true)
     * @Assert\NotBlank
     */
    protected $returnSlide;

    /**
     * @var string
     * @ORM\Column(name="urgency", type="string", nullable=true)
     * @Assert\NotBlank
     */
    protected $urgency;

    /**
     * @ORM\ManyToMany(targetEntity="Slide")
     * @ORM\JoinTable(name="scan_returnrequest_slide",
     *      joinColumns={@ORM\JoinColumn(name="slideReturnRequest", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="slide", referencedColumnName="id")}
     * )
     */
    private $slide;

    /**
     * @ORM\ManyToOne(targetEntity="OrderInfo")
     * @ORM\JoinColumn(name="orderinfo", referencedColumnName="id", nullable=true)
     */
    protected $orderinfo;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $comment;

    /**
     * Return slide(s) by this date even if not scanned
     * @ORM\Column(name="returnoption", type="boolean", nullable=true)
     */
    private $returnoption;

    /**
     * @ORM\OneToMany(targetEntity="SlideText", mappedBy="slideReturnRequest", cascade={"persist"})
     */
    private $slidetext;


    /**
     * Constructor
     */
    public function __construct()
    {
        $this->slide = new ArrayCollection();
        $this->slidetext = new ArrayCollection();
    }

    /**
     * Set returnSlide
     *
     * @param string $returnSlide
     * @return SlideReturnRequest
     */
    public function setReturnSlide($returnSlide)
    {
        $this->returnSlide = $returnSlide;

        return $this;
    }

    /**
     * Get returnSlide
     *
     * @return string
     */
    public function getReturnSlide()
    {
        return $this->returnSlide;
    }

    public function getStatus() {
        return $this->status;
    }

    public function setStatus($status) {
        $this->status = $status;
    }



    /**
     * Add slide
     *
     * @param \Oleg\OrderformBundle\Entity\Slide $slide
     * @return SlideReturnRequest
     */
    public function addSlide(\Oleg\OrderformBundle\Entity\Slide $slide)
    {
        if( !$this->slide->contains($slide) ) {
            $this->slide->add($slide);
        }
        return $this;
    }

    /**
     * Remove slide
     *
     * @param \Oleg\OrderformBundle\Entity\Slide $slide
     */
    public function removeSlide(\Oleg\OrderformBundle\Entity\Slide $slide)
    {
        $this->slide->removeElement($slide);
    }

    /**
     * Get slide
     *
     * @return SlideReturnRequest
     */
    public function getSlide()
    {
        return $this->slide;
    }

    /**
     * Get slide
     * @param Doctrine\Common\Collections\Collection
     * @return SlideReturnRequest
     */
    public function setSlide( $slides)
    {
        $this->slide = $slides;
        return $this;
    }

    /**
     * @param string $urgency
     */
    public function setUrgency($urgency)
    {
        $this->urgency = $urgency;
    }

    /**
     * @return string
     */
    public function getUrgency()
    {
        return $this->urgency;
    }

    /**
     * @param mixed $orderinfo
     */
    public function setOrderinfo($orderinfo)
    {
        $this->orderinfo = $orderinfo;
    }

    /**
     * @return mixed
     */
    public function getOrderinfo()
    {
        return $this->orderinfo;
    }

    /**
     * @param mixed $comment
     */
    public function setComment($comment)
    {
        $this->comment = $comment;
    }

    /**
     * @return mixed
     */
    public function getComment()
    {
        return $this->comment;
    }


    public function addComment( $comment, $user )
    {
        // 06/15/2014 at 11:49pm
        $transformer = new DateTimeToStringTransformer(null,null,'m/d/Y \a\t G:i');
        $dateStr = $transformer->transform(new \DateTime());
        $commentFull = $user . " on " . $dateStr. ": " . $comment;

        $this->comment = $commentFull . "<br>" . $this->comment;
    }

    /**
     * @param mixed $returnoption
     */
    public function setReturnoption($returnoption)
    {
        $this->returnoption = $returnoption;
    }

    /**
     * @return mixed
     */
    public function getReturnoption()
    {
        return $this->returnoption;
    }

    public function getSlidetext()
    {
        return $this->slidetext;
    }

    public function addSlidetext($slidetext)
    {
        if( !$this->slidetext->contains($slidetext) ) {
            $slidetext->setSlideReturnRequest($this);
            $this->slidetext->add($slidetext);
        }

        return $this;
    }
    public function removeSlidetext($slidetext)
    {
        $this->slidetext->removeElement($slidetext);
    }



    public function getSlideDescription( $user ) {

        $description = array();
        foreach( $this->slide as $slide ) {

            $patient =  $slide->obtainPatient();//->filterArrayFields($user,true);
            $patientkey =  $patient->obtainValidKeyfield();
            $patientFullName = $patient->getFullPatientName();
            if( !$patientkey->getField() || $patientkey->getField() == "" ) {
                $patientMrn = "No MRN Provided";
            } else {
                $patientMrn = $patientkey->getField();
            }

            $accession =  $slide->obtainAccession();//->filterArrayFields($user,true);
            $accessionkey =  $accession->obtainValidKeyfield();

            $part =  $slide->obtainPart();//->filterArrayFields($user,true);
            $partkey =  $part->obtainValidKeyfield();

            $block =  $slide->obtainBlock();//->filterArrayFields($user,true);
            $blockDesc = "";
            if( $block ) {
                $blockkey =  $block->obtainValidKeyfield();
                $blockDesc = $blockkey->getField();
            }

            $stainArr = array();
            foreach( $slide->getStain() as $stain ) {
                $stainArr[] = $stain."";
            }
            $stainDesc = implode(",", $stainArr);

            $str = $accessionkey->getKeytype().": <b>".$accessionkey->getField()." ".$partkey->getField()." ".$blockDesc." ".$stainDesc."</b>".
                    " (".$patientkey->getKeytype().": ".$patientkey->getField().", ".$patientFullName.")";
            $description[] = $str;

        }

        return $description;
    }


    public function getSlideTextDescription( $user ) {

        $description = array();
        foreach( $this->slidetext as $slide ) {

            $patientFullName = $slide->getFullPatientName();

            if( !$slide->getMrn() || $slide->getMrn() == "" ) {
                $patientMrn = "No MRN Provided";
            } else {
                $patientMrn = $slide->getMrn();
            }

            $str = $slide->getAccessiontype().": <b>".$slide->getAccession()." ".$slide->getPart()." ".$slide->getBlock()." ".$slide->getStain()."</b>".
                " (".$slide->getMrntype().": ".$patientMrn.", ".$patientFullName.")";
            $description[] = $str;

        }

        return $description;
    }

}