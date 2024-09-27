<?php
/**
 * Created by PhpStorm.
 * User: ch3
 * Date: 12/4/2017
 * Time: 3:12 PM
 */

namespace App\TranslationalResearchBundle\Entity;


use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;


//Project Goal is similar to Sub-Project.

#[ORM\Table(name: 'transres_projectgoal')]
#[ORM\Entity]
class ProjectGoal {

    //IDENTITY or AUTO: when creating and persist new object ID is created with AUTO.
    // However, the logic relies on ID = NULL when new object is persisted but the object does not yet exist.
    // Make a test "Testing IDENTITY comparing to AUTO":
    // IDENTITY => ID is not created
    // AUTO => ID is created

    // User Deprecated: Context: Loading metadata for class
    // App\UserdirectoryBundle\Entity\Document Problem:
    // Using the IDENTITY generator strategy with platform
    // "Doctrine\DBAL\Platforms\PostgreSQL100Platform" is
    // deprecated and will not be possible in Doctrine ORM 3.0.
    // Solution: Use the SEQUENCE generator strategy instead.
    // (ClassMetadataFactory.php:632 called by ClassMetadataFactory.php:150,
    // https://github.com/doctrine/orm/issues/8850, package doctrine/orm)
    
    //Probably fix: composer require doctrine/dbal:^4

    /**
     * @var integer
     */
    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    private $id;

    /**
     * @var \DateTime
     */
    #[ORM\Column(type: 'datetime', nullable: true)]
    private $createDate;

    #[ORM\ManyToOne(targetEntity: 'App\UserdirectoryBundle\Entity\User')]
    #[ORM\JoinColumn(referencedColumnName: 'id', nullable: true)]
    private $author;

    #[ORM\ManyToOne(targetEntity: 'App\UserdirectoryBundle\Entity\User')]
    #[ORM\JoinColumn(name: 'updateAuthor', referencedColumnName: 'id', nullable: true)]
    private $updateAuthor;

    /**
     * @var \DateTime
     */
    #[ORM\Column(type: 'datetime', nullable: true)]
    private $updatedate;

    /**
     * Indicates the order in the list
     */
    #[ORM\Column(type: 'integer', nullable: true)]
    private $orderinlist;

    /**
     * @var string
     */
    #[ORM\Column(type: 'text', nullable: true)]
    private $description;

    //Enable de-activation of each project goal
    // (we can not delete them since a work request may be associated with them),
    // but there must be a way to mark an project goal as “Inactive”
    // so that it stops showing up on the drop down list
    // of “Project View” page when this field is non-empty, and on the Work Request pages
    #[ORM\Column(type: 'string', nullable: true)]
    private $status;

    //ManyToOne is always the owning side of a bidirectional association.
    //The owning side has to have the inversedBy attribute
    #[ORM\ManyToOne(targetEntity: 'Project', inversedBy: 'projectGoals')]
    #[ORM\JoinColumn(name: 'project_id', referencedColumnName: 'id')]
    private $project;

    //Can have many Work Request, Work Request can have many Project Goals
    //The inverse side has to have the mappedBy.
    //TransResRequest is the owning side and it will be responsible to make changes to the ProjectGoal
    #[ORM\ManyToMany(targetEntity: TransResRequest::class, mappedBy: 'projectGoals')]
    private $workRequests;

//    #[ORM\OneToMany(targetEntity: 'TransResRequest', mappedBy: 'projectGoal', cascade: ['persist'])]
//    private $workRequests;

//    #[ORM\JoinTable(name: 'transres_price_workqueue')]
//    #[ORM\JoinColumn(name: 'price_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
//    #[ORM\InverseJoinColumn(name: 'workqueue_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
//    #[ORM\ManyToMany(targetEntity: 'App\TranslationalResearchBundle\Entity\WorkQueueList', cascade: ['persist', 'remove'])]
//    #[ORM\OrderBy(['createdate' => 'DESC'])]
//    private $workRequests;


    public function __construct($user=null) {
        $this->setAuthor($user);
        $this->setCreateDate(new \DateTime());

        $this->workRequests = new ArrayCollection();
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return \DateTime
     */
    public function getCreateDate()
    {
        return $this->createDate;
    }

    /**
     * @param \DateTime $createDate
     */
    public function setCreateDate($createDate)
    {
        $this->createDate = $createDate;
    }

    /**
     * @return mixed
     */
    public function getAuthor()
    {
        return $this->author;
    }

    /**
     * @param mixed $author
     */
    public function setAuthor($author)
    {
        $this->author = $author;
    }

    /**
     * @return mixed
     */
    public function getUpdateAuthor()
    {
        return $this->updateAuthor;
    }

    /**
     * @param mixed $updateAuthor
     */
    public function setUpdateAuthor($updateAuthor)
    {
        $this->updateAuthor = $updateAuthor;
    }

    #[ORM\PreUpdate]
    public function getUpdatedate()
    {
        return $this->updatedate;
    }

    /**
     * @param \DateTime $updatedate
     */
    public function setUpdatedate($updatedate)
    {
        $this->updatedate = $updatedate;
    }

    /**
     * @return mixed
     */
    public function getOrderinlist()
    {
        return $this->orderinlist;
    }

    /**
     * @param mixed $orderinlist
     */
    public function setOrderinlist($orderinlist)
    {
        $this->orderinlist = $orderinlist;
    }

    /**
     * @return mixed
     */
    public function getProject()
    {
        return $this->project;
    }

    /**
     * @param mixed $project
     */
    public function setProject($project)
    {
        $this->project = $project;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param string $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * @return mixed
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param mixed $status
     */
    public function setStatus($status)
    {
        $this->status = $status;
    }

    /**
     * @return mixed
     */
    public function getWorkRequests()
    {
        return $this->workRequests;
    }
    public function addWorkRequest($item)
    {
        if( $item && !$this->workRequests->contains($item) ) {
            $this->workRequests->add($item);
        }
        return $this;
    }
    public function removeWorkRequest($item)
    {
        $this->workRequests->removeElement($item);
    }



    public function __toString()
    {
        $projectId = NULL;
        $project = $this->getProject();
        if( $project ) {
            $projectId = $project->getId();
        }
        return "Project Goal: ID=".$this->getId().", projectId=".$projectId.", description=".$this->getDescription();
    }

}