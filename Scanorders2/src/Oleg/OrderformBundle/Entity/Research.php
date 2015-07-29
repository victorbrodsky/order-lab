<?php
/**
 * Created by JetBrains PhpStorm.
 * User: oli2002
 * Date: 4/22/14
 * Time: 9:30 AM
 * To change this template use File | Settings | File Templates.
 */

namespace Oleg\OrderformBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\ArrayCollection;

//* @ORM\Table(name="scan_research",
// *  indexes={
//    *      @ORM\Index( name="projectTitleStr_idx", columns={"projectTitleStr"} ),
// *      @ORM\Index( name="setTitleStr_idx", columns={"setTitleStr"} )
// *  }

/**
 * @ORM\Entity(repositoryClass="Oleg\OrderformBundle\Repository\ResearchRepository")
 * @ORM\Table(name="scan_research")
 */
class Research
{

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\OneToOne(targetEntity="Message", mappedBy="research")
     */
    private $message;

    /**
     * @ORM\OneToMany(targetEntity="Slide", mappedBy="research")
     */
    private $slides;

    //principal as entered by a user. Use a wrapper because research can have multiple PIs
//    /**
//     * Keep info as principal name as entered by a user and id to a principal
//     * @ORM\OneToMany(targetEntity="PrincipalWrapper", mappedBy="research", cascade={"persist"})
//     * @ORM\JoinColumn(name="principal_id", referencedColumnName="id", nullable=true)
//     */
//    private $principalWrappers;
    //principal as entered by a user. Use a wrapper because research can have multiple PIs
//    /**
//     * Keep info as principal name as entered by a user and id to a principal
//     * @ORM\OneToMany(targetEntity="Oleg\UserdirectoryBundle\Entity\UserWrapper", cascade={"persist"})
//     * @ORM\JoinColumn(name="principal_id", referencedColumnName="id", nullable=true)
//     */
//    private $principalWrappers;
    /**
     * @ORM\ManyToMany(targetEntity="Oleg\UserdirectoryBundle\Entity\UserWrapper", cascade={"persist","remove"})
     * @ORM\JoinTable(name="scan_research_userWrapper",
     *      joinColumns={@ORM\JoinColumn(name="research_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="userWrapper_id", referencedColumnName="id")}
     *      )
     **/
    private $userWrappers;

    /**
     * primarySet - name of the primary PI. Indicates if the primaryPrincipal was set by this order
     * @ORM\Column(type="string", nullable=true)
     */
    private $primarySet;


    /**
     * @ORM\ManyToOne(targetEntity="ProjectTitleTree", inversedBy="researches", cascade={"persist"})
     */
    private $projectTitle;

//    /**
//     * @ORM\ManyToMany(targetEntity="PIList", inversedBy="projectTitles", cascade={"persist"})
//     * @ORM\JoinTable(name="scan_projectTitles_principals")
//     **/
//    private $principals;

//    /**
//     * @ORM\Column(type="string", nullable=true)
//     */
//    private $primaryPrincipal;
    /**
     * @ORM\ManyToOne(targetEntity="Oleg\UserdirectoryBundle\Entity\UserWrapper",cascade={"persist"})
     */
    private $primaryPrincipal;



    //project title as entered by a user
//    /**
//     * @ORM\Column(type="string", nullable=true)
//     */
//    protected $projectTitleStr;
//
//    /**
//     * @ORM\ManyToOne(targetEntity="ProjectTitleList", cascade={"persist"})
//     * @ORM\JoinColumn(name="projectTitle_id", referencedColumnName="id", nullable=true)
//     */
//    protected $projectTitle;
//
//    //principal as entered by a user
//    /**
//     * @ORM\Column(type="string", nullable=true)
//     */
//    protected $setTitleStr;




    public function __construct() {
        $this->slides = new ArrayCollection();

        $this->userWrappers = new ArrayCollection();
        //$this->principals = new ArrayCollection();
    }

//    public function __clone() {
//        if ($this->id) {
//            $this->setId(null);
//        }
//    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
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

//    /**
//     * @param mixed $slide
//     */
//    public function setSlide($slide)
//    {
//        $this->slide = $slide;
//    }
//
//    /**
//     * @return mixed
//     */
//    public function getSlide()
//    {
//        return $this->slide;
//    }
    /**
     * @param \Oleg\OrderformBundle\Entity\Slide $slide
     * @return Block
     */
    public function addSlide(\Oleg\OrderformBundle\Entity\Slide $slide)
    {
        if( !$this->slides->contains($slide) ) {
            $slide->setResearch($this);
            $this->slides->add($slide);
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
        $this->slides->removeElement($slide);
    }

    /**
     * Get slide
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getSlides()
    {
        return $this->slides;
    }


    /**
     * @param mixed $projectTitle
     */
    public function setProjectTitle($projectTitle)
    {
        $this->projectTitle = $projectTitle;
    }

    /**
     * @return mixed
     */
    public function getProjectTitle()
    {
        return $this->projectTitle;
    }

    /**
     * @return mixed
     */
    public function getUserWrappers()
    {
        //entity is PrincipalWrapper class => order will show the same order as entered by a user
        return $this->userWrappers;

        //entity is PIList class => we can shows Primary PI as the first principal
//        if( $this->getProjectTitle() ) {
//            return $this->getProjectTitle()->getPrincipals(); //to keep order according to Primary PI
//        } else {
//            return $this->principalWrappers;
//        }

    }

    /**
     * Add userWrappers
     *
     * @param $userWrappers
     * @return Research
     */
    public function addUserWrapper($userWrapper)
    {
        if( !$this->userWrappers->contains($userWrapper) ) {
            $this->userWrappers->add($userWrapper);
        }

        return $this;
    }

    /**
     * Remove userWrappers
     *
     * @param userWrappers $userWrappers
     */
    public function removeUserWrapper($userWrappers)
    {
        $this->userWrappers->removeElement($userWrappers);
    }

//    /**
//     * @param mixed $projectTitleStr
//     */
//    public function setProjectTitleStr($projectTitleStr)
//    {
//        $this->projectTitleStr = $projectTitleStr;
//    }
//
//    /**
//     * @return mixed
//     */
//    public function getProjectTitleStr()
//    {
//        return $this->projectTitleStr."";
//    }
//
//    /**
//     * @param mixed $setTitleStr
//     */
//    public function setSetTitleStr($setTitleStr)
//    {
//        $this->setTitleStr = $setTitleStr;
//    }
//
//    /**
//     * @return mixed
//     */
//    public function getSetTitleStr()
//    {
//        return $this->setTitleStr."";
//    }

    /**
     * @param mixed $primarySet
     */
    public function setPrimarySet($primarySet)
    {
        $this->primarySet = $primarySet;
    }

    /**
     * @return mixed
     */
    public function getPrimarySet()
    {
        return $this->primarySet;
    }

//    /**
//     * Add principal
//     *
//     * @param PIList $principal
//     * @return ProjectTitleList
//     */
//    public function addPrincipal(PIList $principal)
//    {
//        if( $principal && !$this->principals->contains($principal) ) {
//            $this->principals->add($principal);
//        }
//
//        return $this;
//    }
//
//    /**
//     * Remove principal
//     *
//     * @param PIList $principal
//     */
//    public function removePrincipal(PIList $principal)
//    {
//        $this->principals->removeElement($principal);
//    }
//
//    /**
//     * Get principals
//     *
//     * @return \Doctrine\Common\Collections\Collection
//     */
//    public function getPrincipals() {
//
//        $resArr = new ArrayCollection();
//        foreach( $this->principals as $principal ) {
//
//            //echo $principal->getId() . "?=" . $this->getPrimaryPrincipal()."<br>";
//            if( $principal->getId()."" == $this->getPrimaryPrincipal()."" ) {  //this principal is a primary one => put as the first element
//
//                $firstEl = $resArr->first();
//                if( count($this->principals) > 1 && $firstEl ) {
//
//                    $resArr->set(0,$principal); //set( mixed $key, mixed $value ) Adds/sets an element in the collection at the index / with the specified key.
//                    $resArr->add($firstEl);
//                } else {
//                    $resArr->add($principal);
//                }
//            } else {    //this principal is not a primary one
//                $resArr->add($principal);
//            }
//        }
//
//        return $resArr;
//    }
//
//    //$principal might be empty or PIList
//    public function setPrincipals( $principals )
//    {
//        //echo "principals=".$principals;
//        //echo "<br>set principals: count=".count($principals)."<br>";
//
//        //set primary PI
//        if( $principals->first() ) {
//            $this->primaryPrincipal = $principals->first()->getId();
//        } else {
//            $this->primaryPrincipal = NULL;
//        }
//
//        $this->principals = $principals;
//
//        //echo "<br>count principals=".count($this->getPrincipals())."<br>";
//        //echo "primary principal=".$this->primaryPrincipal."<br>";
//        //exit();
//
//        return $this;
//    }

    /**
     * @param mixed $primaryPrincipal
     */
    public function setPrimaryPrincipal($primaryPrincipal)
    {
        $this->primaryPrincipal = $primaryPrincipal;
    }

    /**
     * @return mixed
     */
    public function getPrimaryPrincipal()
    {
        return $this->primaryPrincipal;
    }



    public function isEmpty()
    {
        //if( $this->getProjectTitleStr() == '' ) {
        if( $this->getProjectTitle()."" == "" ) {
            return true;
        } else {
            return false;
        }
    }


    public function __toString(){
        //return "Research: id=".$this->id.", project=".$this->projectTitle.", project type=".$this->getProjectTitle()->getType().", principal=".$this->principal.", countSetTitles=".count($this->projectTitle->getSetTitles())."<br>";
        return "Research: id=".$this->id.", project=".$this->projectTitle."<br>";
    }

}