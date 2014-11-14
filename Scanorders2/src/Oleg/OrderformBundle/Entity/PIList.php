<?php

namespace Oleg\OrderformBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

use Oleg\UserdirectoryBundle\Entity\ListAbstract;
use Oleg\UserdirectoryBundle\Entity\User;

/**
 * @ORM\Entity
 * @ORM\Table(name="scan_piList")
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
     * Set principal
     *
     * @param User $principal
     * @return PIList
     */
    public function setPrincipal(User $principal = null)
    {
        $this->principal = $principal;
    
        return $this;
    }

    /**
     * Get principal
     *
     * @return User
     */
    public function getPrincipal()
    {
        return $this->principal;
    }

    public function setUserObjectLink(User $user = null) {
        $this->setPrincipal($user);
    }
    public function getUserObjectLink() {
        return $this->getPrincipal();
    }

    /**
     * Add projectTitles
     *
     * @param ProjectTitleList $research
     * @return PIList
     */
    public function addProjectTitle(ProjectTitleList $research)
    {
        if( !$this->projectTitles->contains($research) ) {
            $this->projectTitles->add($research);
        }

        return $this;
    }

    /**
     * Remove researches
     *
     * @param ProjectTitleList $research
     */
    public function removeProjectTitle(ProjectTitleList $research)
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