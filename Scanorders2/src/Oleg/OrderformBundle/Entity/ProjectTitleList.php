<?php

namespace Oleg\OrderformBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="ProjectTitleList")
 */
class ProjectTitleList extends ListAbstract
{

    /**
     * @ORM\OneToMany(targetEntity="ProjectTitleList", mappedBy="original", cascade={"persist"})
     **/
    protected $synonyms;

    /**
     * @ORM\ManyToOne(targetEntity="ProjectTitleList", inversedBy="synonyms", cascade={"persist"})
     * @ORM\JoinColumn(name="original_id", referencedColumnName="id", nullable=true)
     **/
    protected $original;

    /**
     * @ORM\OneToMany(targetEntity="Research", mappedBy="projectTitle")
     */
    protected $research;

    //list of set titles belongs to this project title.
    /**
     * @ORM\OneToMany(targetEntity="SetTitleList", mappedBy="projectTitle", cascade={"persist"})
     */
    protected $setTitles;

    /**
     * @ORM\ManyToMany(targetEntity="PIList", inversedBy="projects")
     * @ORM\JoinTable(name="projects_principals")
     **/
    protected $principals;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $primaryPrincipal;


    public function __construct() {
        $this->research = new ArrayCollection();
        $this->setTitles = new ArrayCollection();
        $this->synonyms = new ArrayCollection();
        $this->principals = new ArrayCollection();
    }

    public function addResearch(\Oleg\OrderformBundle\Entity\Research $research)
    {
        if( !$this->research->contains($research) ) {
            $this->research->add($research);
        }
        return $this;
    }

    public function removeResearch(\Oleg\OrderformBundle\Entity\Research $research)
    {
        $this->research->removeElement($research);
    }

    public function getResearch()
    {
        return $this->research;
    }

    /**
     * Add synonyms
     *
     * @param \Oleg\OrderformBundle\Entity\ProjectTitleList $synonyms
     * @return ProjectTitleList
     */
    public function addSynonym(\Oleg\OrderformBundle\Entity\ProjectTitleList $synonyms)
    {
        if( !$this->synonyms->contains($synonyms) ) {
            $this->synonyms->add($synonyms);
        }

        return $this;
    }

    /**
     * Remove synonyms
     *
     * @param \Oleg\OrderformBundle\Entity\ProjectTitleList $synonyms
     */
    public function removeSynonym(\Oleg\OrderformBundle\Entity\ProjectTitleList $synonyms)
    {
        $this->synonyms->removeElement($synonyms);
    }

    /**
     * Get synonyms
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getSynonyms()
    {
        return $this->synonyms;
    }

    /**
     * @param mixed $original
     */
    public function setOriginal($original)
    {
        $this->original = $original;
    }

    /**
     * @return mixed
     */
    public function getOriginal()
    {
        return $this->original;
    }


    public function addSetTitles(\Oleg\OrderformBundle\Entity\SetTitleList $setTitle)
    {
        if( !$this->setTitles->contains($setTitle) ) {
            $this->setTitles->add($setTitle);
            //$setTitle->setProjectTitle($this);
        }
        return $this;
    }

    public function removeSetTitles(\Oleg\OrderformBundle\Entity\SetTitleList $setTitle)
    {
        $this->setTitles->removeElement($setTitle);
    }

    /**
     * @return mixed
     */
    public function getSetTitles()
    {
        return $this->setTitles;
    }

    public function setSetTitles( $settitle )
    {
        if( $settitle ) {
            $this->addSetTitles($settitle);
            //$settitle->setProjectTitle($this);
        } else {
            $this->setTitles = new ArrayCollection();
        }
        return $this;
    }

    /**
     * Add principals
     *
     * @param \Oleg\OrderformBundle\Entity\PIList $principals
     * @return ProjectTitleList
     */
    public function addPrincipals(\Oleg\OrderformBundle\Entity\PIList $principals)
    {
        if( !$this->principals->contains($principals) ) {
            $this->principals->add($principals);
        }
    
        return $this;
    }

    /**
     * Remove principals
     *
     * @param \Oleg\OrderformBundle\Entity\PIList $principals
     */
    public function removePrincipals(\Oleg\OrderformBundle\Entity\PIList $principals)
    {
        $this->principals->removeElement($principals);
    }

    /**
     * Get principals
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
//    public function getPrincipals()
//    {
//        return $this->principals;
//    }
    public function getPrincipals()
    {

        $resArr = new ArrayCollection();
        foreach( $this->principals as $principal ) {

            if( $principal->getId()."" == $this->getPrimaryPrincipal()."" ) {  //this principal is a primary one => put as the first element

                $firstEl = $resArr->first();
                if( count($this->principals) > 1 && $firstEl ) {

                    $resArr->set(0,$principal); //set( mixed $key, mixed $value ) Adds/sets an element in the collection at the index / with the specified key.
                    $resArr->add($firstEl);
                } else {
                    $resArr->add($principal);
                }
            } else {    //this principal is not a primary one
                $resArr->add($principal);
            }
        }

//        foreach( $resArr as $res ) {
//            echo $res."|";
//        }
//        echo "<br>count=".count($resArr)."<br>";

        return $resArr;
    }

    /**
     * @return mixed
     */
//    public function setPrincipals( $principals )
//    {
//        echo "count=".count($principals)."<br>";
//        //var_dump($principals);
//        //echo "<br>";
//        //exit();
//        $count = 0;
//        foreach( $principals as $principal ) {
//
//            if( $principal instanceof \Oleg\OrderformBundle\Entity\PIList ) {
//                //PIList object
//                $this->addPrincipal( $principal );
//            } else {
//                //string
//                $pi = new PIList();
//                $pi->setName($principal);
//                $this->addPrincipal( $pi );
//            }
//
//        }
//        return $this;
//    }

    //$principal might be empty or PIList
    public function setPrincipals( $principals )
    {
        //echo "principals=".$principals;
        echo "<br>setPrincipals: count=".count($principals)."<br>";

        foreach( $principals as $principal ) {

            echo "name=".$principal."<br>";
            if( $principal ) {
                //echo ": count=".count($principal)."<br>";
                foreach( $principal as $princ ) {
                    //echo "princ=".$princ."<br>";
                    $this->addPrincipals($princ);
                }
                if( count($principal) > 0 ) {
                    $this->primaryPrincipal = $principal->first()->getId();
                    //echo "set primary principal=".$this->primaryPrincipal."<br>";
                } else {
                    $this->primaryPrincipal = NULL;
                    //echo "set primary principal NULL<br>";
                }
            }
        }

        //if( is_object($principals) && $principals instanceof Doctrine\Common\Collections\ArrayCollection ) {
        //if( method_exists($principals,'first') ) {

        echo "<br>count principals=".count($this->getPrincipals())."<br>";
        echo "primary principal=".$this->primaryPrincipal."<br>";
        //exit();

//        echo "first=".$principals->first();
//
//        if( count($principals) > 0 ) {
//            if( count($principals) == 1 ) {
//                $this->primaryPrincipal = $principals->first()->getId();
//            } else {
//                $this->primaryPrincipal = $principals->first()->getId();
//            }
//        } else {
//            $this->primaryPrincipal = NULL;
//        }

//        if( $principals->first() ) {
//            $this->primaryPrincipal = $principals->first()->getId();
//        } else {
//            $this->primaryPrincipal = NULL;
//        }
//        $this->principals = $principals;
        return $this;
    }

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



//    public function isEmtpy()
//    {
//        if( $this->name == '' ) {
//            return false;
//        } else {
//            return true;
//        }
//    }


}