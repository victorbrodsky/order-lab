<?php
/**
 * Copyright (c) 2017 Cornell University
 *
 *  Licensed under the Apache License, Version 2.0 (the "License");
 *  you may not use this file except in compliance with the License.
 *  You may obtain a copy of the License at
 *
 *  http://www.apache.org/licenses/LICENSE-2.0
 *
 *  Unless required by applicable law or agreed to in writing, software
 *  distributed under the License is distributed on an "AS IS" BASIS,
 *  WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 *  See the License for the specific language governing permissions and
 *  limitations under the License.
 */

namespace App\OrderformBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Form\Extension\Core\DataTransformer\DateTimeToStringTransformer;

use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity
 * @ORM\Table(name="scan_slideReturnRequest")
 */
class SlideReturnRequest {

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\OneToOne(targetEntity="Message", mappedBy="slideReturnRequest", cascade={"persist"})
     **/
    private $message;



    /**
     * Additional status variable: Status object to string (active, declined, approved)
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $status;

//    /**
//     * @ORM\ManyToOne(targetEntity="App\UserdirectoryBundle\Entity\Location")
//     * @ORM\JoinColumn(name="returnSlide", referencedColumnName="id", nullable=true)
//     * @Assert\NotBlank
//     **/
//    private $returnSlide;

    /**
     * @var string
     * @ORM\Column(name="urgency", type="string", nullable=true)
     * @Assert\NotBlank
     */
    private $urgency;

//    /**
//     * @ORM\ManyToMany(targetEntity="Slide")
//     * @ORM\JoinTable(name="scan_returnrequest_slide",
//     *      joinColumns={@ORM\JoinColumn(name="slideReturnRequest", referencedColumnName="id")},
//     *      inverseJoinColumns={@ORM\JoinColumn(name="slide", referencedColumnName="id")}
//     * )
//     */
//    private $slide;

//    /**
//     * @ORM\ManyToOne(targetEntity="Message")
//     * @ORM\JoinColumn(name="message", referencedColumnName="id", nullable=true)
//     */
//    protected $message;

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
        //$this->slide = new ArrayCollection();
        $this->slidetext = new ArrayCollection();
    }





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

    public function getStatus() {
        return $this->status;
    }

    public function setStatus($status) {
        $this->status = $status;
    }



//    /**
//     * Add slide
//     *
//     * @param \App\OrderformBundle\Entity\Slide $slide
//     * @return SlideReturnRequest
//     */
//    public function addSlide(\App\OrderformBundle\Entity\Slide $slide)
//    {
//        if( !$this->slide->contains($slide) ) {
//            $this->slide->add($slide);
//        }
//        return $this;
//    }
//
//    /**
//     * Remove slide
//     *
//     * @param \App\OrderformBundle\Entity\Slide $slide
//     */
//    public function removeSlide(\App\OrderformBundle\Entity\Slide $slide)
//    {
//        $this->slide->removeElement($slide);
//    }
//
//    /**
//     * Get slide
//     *
//     * @return SlideReturnRequest
//     */
//    public function getSlide()
//    {
//        return $this->slide;
//    }
//
//    /**
//     * Get slide
//     * @param Doctrine\Common\Collections\Collection
//     * @return SlideReturnRequest
//     */
//    public function setSlide( $slides)
//    {
//        $this->slide = $slides;
//        return $this;
//    }

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
     * @param mixed $message
     */
    public function setMessage($message)
    {
        $this->message = $message;
        $message->setSlideReturnRequest($this);
    }

    /**
     * @return mixed
     */
    public function getMessage()
    {
        return $this->message;
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
        foreach( $this->getMessage()->getSlide() as $slide ) {

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

    public function __toString() {
        $res = "SlideReturnRequest";
        if( $this->getId() ) {
            $res = $res . " with ID=" . $this->getId();
        }
        return $res;
    }

}