<?php
/**
 * Created by PhpStorm.
 * User: ch3
 * Date: 12/4/2017
 * Time: 3:12 PM
 */

namespace Oleg\TranslationalResearchBundle\Entity;


use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;


/**
 * @ORM\Entity
 * @ORM\Table(name="transres_product")
 */
class Product {

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $createDate;

    /**
     * @ORM\ManyToOne(targetEntity="Oleg\UserdirectoryBundle\Entity\User")
     * @ORM\JoinColumn(referencedColumnName="id", nullable=true)
     */
    private $submitter;

    /**
     * @ORM\ManyToOne(targetEntity="TransResRequest", inversedBy="products")
     * @ORM\JoinColumn(name="transresRequest_id", referencedColumnName="id")
     */
    private $transresRequest;

    /**
     * @ORM\ManyToOne(targetEntity="RequestCategoryTypeList")
     * @ORM\JoinColumn(name="category", referencedColumnName="id", nullable=true)
     */
    private $category;

    /**
     * Requested
     * @ORM\Column(type="integer", nullable=true)
     */
    private $requested;

    /**
     * Completed
     * @ORM\Column(type="integer", nullable=true)
     */
    private $completed;

    /**
     * @var string
     * @ORM\Column(type="text", nullable=true)
     */
    private $comment;

    /**
     * Note (TRP tech)
     *
     * @var string
     * @ORM\Column(type="text", nullable=true)
     */
    private $note;


    public function __construct($user=null) {
        $this->setSubmitter($user);
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
    public function getSubmitter()
    {
        return $this->submitter;
    }

    /**
     * @param mixed $submitter
     */
    public function setSubmitter($submitter)
    {
        $this->submitter = $submitter;
    }

    /**
     * @return mixed
     */
    public function getTransresRequest()
    {
        return $this->transresRequest;
    }

    /**
     * @param mixed $transresRequest
     */
    public function setTransresRequest($transresRequest)
    {
        $this->transresRequest = $transresRequest;
    }

    /**
     * @return mixed
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * @param mixed $category
     */
    public function setCategory($category)
    {
        $this->category = $category;
    }

    /**
     * @return mixed
     */
    public function getRequested()
    {
        return $this->requested;
    }

    /**
     * @param mixed $requested
     */
    public function setRequested($requested)
    {
        $this->requested = $requested;
    }

    /**
     * @return mixed
     */
    public function getCompleted()
    {
        return $this->completed;
    }

    /**
     * @param mixed $completed
     */
    public function setCompleted($completed)
    {
        $this->completed = $completed;
    }

    /**
     * @return string
     */
    public function getComment()
    {
        return $this->comment;
    }

    /**
     * @param string $comment
     */
    public function setComment($comment)
    {
        $this->comment = $comment;
    }

    /**
     * @return mixed
     */
    public function getNote()
    {
        return $this->note;
    }

    /**
     * @param mixed $note
     */
    public function setNote($note)
    {
        $this->note = $note;
    }

    public function getQuantity() {
        $quantity = $this->getCompleted();
        if( !$quantity ) {
            $quantity = $this->getRequested();
        }
        return $quantity;
    }

}