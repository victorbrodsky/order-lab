<?php

namespace Oleg\UserdirectoryBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="Oleg\UserdirectoryBundle\Repository\ResearchLabRepository")
 * @ORM\Table(name="user_researchLab")
 */
class ResearchLab extends ListAbstract  //extends BaseUserAttributes
{

    /**
     * @ORM\OneToMany(targetEntity="BuildingList", mappedBy="original", cascade={"persist"})
     **/
    protected $synonyms;

    /**
     * @ORM\ManyToOne(targetEntity="BuildingList", inversedBy="synonyms", cascade={"persist"})
     * @ORM\JoinColumn(name="original_id", referencedColumnName="id", nullable=true)
     **/
    protected $original;

    /**
     * @ORM\ManyToMany(targetEntity="User", inversedBy="researchLabs")
     * @ORM\JoinTable(name="user_researchlab_user",
     *      joinColumns={@ORM\JoinColumn(name="researchlab_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="user_id", referencedColumnName="id")}
     * )
     **/
    private $user;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $foundedDate;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $dissolvedDate;

    /**
     * @ORM\ManyToOne(targetEntity="Location")
     * @ORM\JoinColumn(name="location", referencedColumnName="id", nullable=true)
     **/
    private $location;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $weblink;

    /**
     * @ORM\OneToMany(targetEntity="ResearchLabComment", mappedBy="researchLab", cascade={"persist","remove"})
     **/
    private $comments;
    private $commentDummy;

    /**
     * @ORM\OneToMany(targetEntity="ResearchLabPI", mappedBy="researchLab", cascade={"persist","remove"})
     **/
    private $pis;
    private $piDummy;


    public function __construct($creator=null) {

        $this->comments = new ArrayCollection();
        $this->pis = new ArrayCollection();

        $this->user = new ArrayCollection();
        $this->synonyms = new ArrayCollection();

        //set mandatory list attributes
        $this->setName("");
        $this->setType('user-added');
        $this->setCreatedate(new \DateTime());
        $this->setOrderinlist(-1);

        if( $creator ) {
            $this->setCreator($creator);
        }

//        //add comment
//        $comment = new ResearchLabComment();
//        $this->addComment($comment);
//
//        //add pi
//        $pi = new ResearchLabPI();
//        $this->addPi($pi);
    }


    public function getComments()
    {
        return $this->comments;
    }
    public function addComment($comment)
    {
        if( !$this->comments->contains($comment) ) {
            $this->comments->add($comment);
            $comment->setResearchLab($this);
        }
        return $this;
    }
    public function removeComment($comment)
    {
        $this->comments->removeElement($comment);
    }

    public function getPis()
    {
        return $this->pis;
    }
    public function addPi($pi)
    {
        if( !$this->pis->contains($pi) ) {
            $this->pis->add($pi);
            $pi->setResearchLab($this);
        }
        return $this;
    }
    public function removePi($pi)
    {
        $this->pis->removeElement($pi);
    }

    /**
     * @param mixed $dissolvedDate
     */
    public function setDissolvedDate($dissolvedDate)
    {
        $this->dissolvedDate = $dissolvedDate;
    }

    /**
     * @return mixed
     */
    public function getDissolvedDate()
    {
        return $this->dissolvedDate;
    }

    /**
     * @param mixed $foundedDate
     */
    public function setFoundedDate($foundedDate)
    {
        $this->foundedDate = $foundedDate;
    }

    /**
     * @return mixed
     */
    public function getFoundedDate()
    {
        return $this->foundedDate;
    }

    /**
     * @param mixed $location
     */
    public function setLocation($location)
    {
        $this->location = $location;
    }

    /**
     * @return mixed
     */
    public function getLocation()
    {
        return $this->location;
    }



    public function addUser($user)
    {
        if( !$this->user->contains($user) ) {
            $this->user->add($user);
            $user->addResearchLab($this);
        }

        return $this;
    }
    public function removeUser($user)
    {
        $this->user->removeElement($user);
    }
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param mixed $weblink
     */
    public function setWeblink($weblink)
    {
        $this->weblink = $weblink;
    }

    /**
     * @return mixed
     */
    public function getWeblink()
    {
        return $this->weblink;
    }





    /**
     * @param mixed $commentDummy
     */
    public function setCommentDummy($commentDummy)
    {
        $this->commentDummy = $commentDummy;
    }

    /**
     * @return mixed
     */
    public function getCommentDummy()
    {
        return $this->commentDummy;
    }

    /**
     * @param mixed $piDummy
     */
    public function setPiDummy($piDummy)
    {
        $this->piDummy = $piDummy;
    }

    /**
     * @return mixed
     */
    public function getPiDummy()
    {
        return $this->piDummy;
    }

    public function setComment($text,$user)
    {
        if( $text && $text != "" ) {
            $comment = new ResearchLabComment();
            $comment->setComment($text);
            $comment->setAuthor($user);
            $this->addComment($comment);
        }
    }
    public function setPiUser($user)
    {
        if( $user ) {
            $pi = new ResearchLabPI();
            $pi->setPi($user);
            $this->addPi($pi);
        }
    }


    public function removeDependents($user) {
        //remove user's comments
        foreach( $this->getComments() as $comment ) {
            if( $comment->getAuthor()->getId() == $user->getId() ) {
                $this->removeComment($comment);
            }
        }
        //remove user's pi
        foreach( $this->getPis() as $pi ) {
            if( $pi->getPi()->getId() == $user->getId() ) {
                $this->removePi($pi);
            }
        }
    }

    //interface function
    public function getAuthor()
    {
        return $this->getCreator();
    }
    public function setAuthor($author)
    {
        return $this->setCreator($author);
    }
    public function getUpdateAuthor()
    {
        return $this->getUpdatedby();
    }
    public function setUpdateAuthor($author)
    {
        return $this->setUpdatedby($author);
    }


//    public function __toString() {
//        //return "Research Lab"." name=".$this->getName().", id=".$this->getId();
//        return $this->getName();
//    }


}