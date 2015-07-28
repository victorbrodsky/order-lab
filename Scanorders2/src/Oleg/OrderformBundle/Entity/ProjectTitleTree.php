<?php

namespace Oleg\OrderformBundle\Entity;

use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Oleg\UserdirectoryBundle\Entity\BaseCompositeNode;
use Symfony\Component\Validator\Constraints as Assert;


/**
 * Use Composite pattern:
 * The composite pattern describes that a group of objects is to be treated in the same
 * way as a single instance of an object. The intent of a composite is to "compose" objects into tree structures
 * to represent part-whole hierarchies. Implementing the composite pattern lets clients treat individual objects
 * and compositions uniformly.
 * Use Doctrine Extension Tree for tree manipulation.
 *
 * @Gedmo\Tree(type="nested")
 * @ORM\Entity(repositoryClass="Oleg\UserdirectoryBundle\Repository\TreeRepository")
 * @ORM\Table(
 *  name="scan_projectTitleTree",
 *  indexes={
 *      @ORM\Index( name="name_idx", columns={"name"} ),
 *  }
 * )
 */
class ProjectTitleTree extends BaseCompositeNode {

    /**
     * @Gedmo\TreeParent
     * @ORM\ManyToOne(targetEntity="ProjectTitleTree", inversedBy="children")
     * @ORM\JoinColumn(name="parent_id", referencedColumnName="id")
     **/
    protected $parent;

    /**
     * @ORM\OneToMany(targetEntity="ProjectTitleTree", mappedBy="parent", cascade={"persist","remove"})
     * @ORM\OrderBy({"lft" = "ASC"})
     **/
    protected $children;

    /**
     * Organizational Group Types
     * level int in OrganizationalGroupType corresponds to this level integer: 1-Research Project Title, 2-Research Set Title
     * For example, OrganizationalGroupType with level=1, set this level to 1.
     *
     * @ORM\ManyToOne(targetEntity="ResearchGroupType", cascade={"persist"})
     */
    private $organizationalGroupType;

    /**
     * @ORM\OneToMany(targetEntity="ProjectTitleTree", mappedBy="original")
     **/
    protected $synonyms;

    /**
     * @ORM\ManyToOne(targetEntity="ProjectTitleTree", inversedBy="synonyms")
     * @ORM\JoinColumn(name="original_id", referencedColumnName="id")
     **/
    protected $original;



    /**
     * @ORM\ManyToMany(targetEntity="PIList", inversedBy="projectTitles", cascade={"persist"})
     * @ORM\JoinTable(name="scan_projectTitles_principals")
     **/
    protected $principals;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $primaryPrincipal;





    /**
     * @param mixed $organizationalGroupType
     */
    public function setOrganizationalGroupType($organizationalGroupType)
    {
        $this->organizationalGroupType = $organizationalGroupType;
        $this->setLevel($organizationalGroupType->getLevel());
    }

    /**
     * @return mixed
     */
    public function getOrganizationalGroupType()
    {
        return $this->organizationalGroupType;
    }


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
