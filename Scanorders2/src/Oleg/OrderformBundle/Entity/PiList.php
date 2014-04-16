<?php

namespace Oleg\OrderformBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="PIList")
 */
class PIList extends ListAbstract
{

    /**
     * @ORM\OneToMany(targetEntity="PIList", mappedBy="original", cascade={"persist"})
     **/
    protected $synonyms;

    /**
     * @ORM\ManyToOne(targetEntity="PIList", inversedBy="synonyms", cascade={"persist"})
     * @ORM\JoinColumn(name="original_id", referencedColumnName="id", nullable=true)
     **/
    protected $original;

    /**
     * @ORM\ManyToOne(targetEntity="User", cascade={"persist"})
     * @ORM\JoinColumn(name="principal_id", referencedColumnName="id")
     */
    protected $principal;

    /**
     * @ORM\ManyToMany(targetEntity="ProjectTitleList", mappedBy="principals")
     **/
    private $projects;

    //use name as $principalstr


    public function __construct() {
        $this->projects = new ArrayCollection();
        $this->synonyms = new ArrayCollection();
    }

    /**
     * Add synonyms
     *
     * @param \Oleg\OrderformBundle\Entity\PIList $synonyms
     * @return PIList
     */
    public function addSynonym(\Oleg\OrderformBundle\Entity\PIList $synonyms)
    {
        if( !$this->synonyms->contains($synonyms) ) {
            $this->synonyms->add($synonyms);
        }

        return $this;
    }

    /**
     * Remove synonyms
     *
     * @param \Oleg\OrderformBundle\Entity\PIList $synonyms
     */
    public function removeSynonym(\Oleg\OrderformBundle\Entity\PIList $synonyms)
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

    /**
     * Set principal
     *
     * @param \Oleg\OrderformBundle\Entity\User $principal
     * @return PIList
     */
    public function setPrincipal(\Oleg\OrderformBundle\Entity\User $principal = null)
    {
        $this->principal = $principal;
    
        return $this;
    }

    /**
     * Get principal
     *
     * @return \Oleg\OrderformBundle\Entity\User 
     */
    public function getPrincipal()
    {
        return $this->principal;
    }

    /**
     * Add projects
     *
     * @param \Oleg\OrderformBundle\Entity\ProjectTitleList $projects
     * @return PIList
     */
    public function addProject(\Oleg\OrderformBundle\Entity\ProjectTitleList $projects)
    {
        if( !$this->projects->contains($projects) ) {
            $this->projects->add($projects);
        }
    
        return $this;
    }

    /**
     * Remove projects
     *
     * @param \Oleg\OrderformBundle\Entity\ProjectTitleList $projects
     */
    public function removeProject(\Oleg\OrderformBundle\Entity\ProjectTitleList $projects)
    {
        $this->projects->removeElement($projects);
    }

    /**
     * Get projects
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getProjects()
    {
        return $this->projects;
    }

}