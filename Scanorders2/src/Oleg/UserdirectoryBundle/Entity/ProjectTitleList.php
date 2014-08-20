<?php

namespace Oleg\UserdirectoryBundle\Entity;

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

    //list of set titles belongs to this project title.
    /**
     * @ORM\OneToMany(targetEntity="SetTitleList", mappedBy="projectTitle", cascade={"persist"})
     */
    protected $setTitles;

    /**
     * @ORM\ManyToMany(targetEntity="PIList", inversedBy="projectTitles", cascade={"persist"})
     * @ORM\JoinTable(name="projectTitles_principals")
     **/
    protected $principals;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $primaryPrincipal;


    public function __construct() {
        $this->setTitles = new ArrayCollection();
        $this->synonyms = new ArrayCollection();
        $this->principals = new ArrayCollection();
    }


    /**
     * Add synonyms
     *
     * @param ProjectTitleList $synonyms
     * @return ProjectTitleList
     */
    public function addSynonym(ProjectTitleList $synonyms)
    {
        if( !$this->synonyms->contains($synonyms) ) {
            $this->synonyms->add($synonyms);
        }

        return $this;
    }

    /**
     * Remove synonyms
     *
     * @param ProjectTitleList $synonyms
     */
    public function removeSynonym(ProjectTitleList $synonyms)
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


    public function addSetTitle(SetTitleList $setTitle = null)
    {
        if( $setTitle && !$this->setTitles->contains($setTitle) ) {
            $this->setTitles->add($setTitle);
            $setTitle->setProjectTitle($this);
        }

        return $this;
    }

    public function removeSetTitle(SetTitleList $setTitle)
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

//    public function setSetTitles( $settitle )
//    {
//        if( $settitle ) {
//            $this->addSetTitles($settitle);
//            //$settitle->setProjectTitle($this);
//        } else {
//            $this->setTitles = new ArrayCollection();
//        }
//        return $this;
//    }

    /**
     * Add principal
     *
     * @param PIList $principal
     * @return ProjectTitleList
     */
    public function addPrincipal(PIList $principal)
    {
        if( !$this->principals->contains($principal) ) {
            $this->principals->add($principal);
        }

        return $this;
    }

    /**
     * Remove principal
     *
     * @param PIList $principal
     */
    public function removePrincipal(PIList $principal)
    {
        $this->principals->removeElement($principal);
    }

    /**
     * Get principals
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getPrincipals() {

        $resArr = new ArrayCollection();
        foreach( $this->principals as $principal ) {

            //echo $principal->getId() . "?=" . $this->getPrimaryPrincipal()."<br>";
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

        return $resArr;
    }

    //$principal might be empty or PIList
    public function setPrincipals( $principals )
    {
        //echo "principals=".$principals;
        //echo "<br>set principals: count=".count($principals)."<br>";

        //set primary PI
        if( $principals->first() ) {
            $this->primaryPrincipal = $principals->first()->getId();
        } else {
            $this->primaryPrincipal = NULL;
        }

        $this->principals = $principals;

        //echo "<br>count principals=".count($this->getPrincipals())."<br>";
        //echo "primary principal=".$this->primaryPrincipal."<br>";
        //exit();

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


}
