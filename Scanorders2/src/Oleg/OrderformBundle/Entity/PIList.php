<?php

namespace Oleg\OrderformBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

use Oleg\UserdirectoryBundle\Entity\ListAbstract;

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
     * User object
     * @ORM\ManyToOne(targetEntity="Oleg\UserdirectoryBundle\Entity\User")
     * @ORM\JoinColumn(name="principal_id", referencedColumnName="id")
     */
    protected $principal;

    //use name as $principalstr

    /**
     * @ORM\ManyToMany(targetEntity="ProjectTitleList", mappedBy="principals", cascade={"persist"})
     **/
    private $projectTitles;


    public function __construct() {
        $this->synonyms = new ArrayCollection();
        $this->projectTitles = new ArrayCollection();
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
     * @param \Oleg\UserdirectoryBundle\Entity\User $principal
     * @return PIList
     */
    public function setPrincipal(\Oleg\UserdirectoryBundle\Entity\User $principal = null)
    {
        $this->principal = $principal;
    
        return $this;
    }

    /**
     * Get principal
     *
     * @return \Oleg\UserdirectoryBundle\Entity\User
     */
    public function getPrincipal()
    {
        return $this->principal;
    }

    public function setUserObjectLink(\Oleg\UserdirectoryBundle\Entity\User $user) {
        $this->setPrincipal($user);
    }
    public function getUserObjectLink() {
        return $this->getPrincipal();
    }

    /**
     * Add projectTitles
     *
     * @param \Oleg\OrderformBundle\Entity\Research $research
     * @return PIList
     */
    public function addProjectTitle(\Oleg\OrderformBundle\Entity\ProjectTitleList $research)
    {
        if( !$this->projectTitles->contains($research) ) {
            $this->projectTitles->add($research);
        }

        return $this;
    }

    /**
     * Remove researches
     *
     * @param \Oleg\OrderformBundle\Entity\Research $research
     */
    public function removeProjectTitle(\Oleg\OrderformBundle\Entity\ProjectTitleList $research)
    {
        $this->projectTitles->removeElement($research);
    }

    /**
     * Get researches
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getProjectTitles()
    {
        return $this->projectTitles;
    }

}