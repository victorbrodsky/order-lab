<?php
/**
 * Created by PhpStorm.
 * User: ch3
 * Date: 12/4/2017
 * Time: 3:12 PM
 */

namespace App\TranslationalResearchBundle\Entity;


use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;



#[ORM\Table(name: 'transres_projectgoal')]
#[ORM\Entity]
class ProjectGoal {

    //? [ORM\GeneratedValue(strategy: 'AUTO')]
    //User Deprecated: Context: Loading metadata for class
    // App\UserdirectoryBundle\Entity\Document Problem:
    // Using the IDENTITY generator strategy with platform
    // "Doctrine\DBAL\Platforms\PostgreSQL100Platform" is
    // deprecated and will not be possible in Doctrine ORM 3.0.
    // Solution: Use the SEQUENCE generator strategy instead.
    // (ClassMetadataFactory.php:632 called by ClassMetadataFactory.php:150,
    // https://github.com/doctrine/orm/issues/8850, package doctrine/orm)

    //[ORM\GeneratedValue(strategy: 'IDENTITY')]

    /**
     * @var integer
     */
    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
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

    #[ORM\ManyToOne(targetEntity: 'Project', inversedBy: 'projectGoals')]
    #[ORM\JoinColumn(name: 'project_id', referencedColumnName: 'id')]
    private $project;

    /**
     * @var string
     */
    #[ORM\Column(type: 'text', nullable: true)]
    private $description;


    public function __construct($user=null) {
        $this->setAuthor($user);
        $this->setCreateDate(new \DateTime());
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