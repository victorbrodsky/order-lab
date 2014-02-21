<?php

namespace Oleg\OrderformBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity(repositoryClass="Oleg\OrderformBundle\Repository\UserRequestRepository")
 * @ORM\Table(name="accountrequest")
 * @ORM\HasLifecycleCallbacks
 */
class UserRequest
{
    
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $cwid;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $name;
    
    /**
     * @ORM\Column(type="string")
     * @Assert\NotBlank
     */
    protected $email;
    
    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $phone;
    
    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $job;
    
    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $organization;
    
    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $department;
    
    /**
     * @ORM\Column(type="text", nullable=true)
     */
    protected $request;

//    /**
//     * @ORM\Column(type="string", nullable=true)
//     */
//    protected $pathologyService;
    /**
     * @ORM\ManyToMany(targetEntity="PathServiceList")
     * @ORM\JoinTable(name="accountrequest_pathservice",
     *      joinColumns={@ORM\JoinColumn(name="user_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="pathservice_id", referencedColumnName="id")}
     * )
     */
    protected $pathologyServices;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $primaryPathologyService;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $status;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime")
     */
    protected $creationdate;

    function __construct()
    {
        $this->pathologyServices = new ArrayCollection();
    }

    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set cwid
     *
     * @param string $cwid
     * @return Request
     */
    public function setCwid($cwid)
    {
        $this->cwid = $cwid;
    
        return $this;
    }

    /**
     * Get cwid
     *
     * @return string 
     */
    public function getCwid()
    {
        return $this->cwid;
    }

    /**
     * Set request
     *
     * @param string $request
     * @return Request
     */
    public function setRequest($request)
    {
        $this->request = $request;
    
        return $this;
    }

    /**
     * Get request
     *
     * @return string 
     */
    public function getRequest()
    {
        return $this->request;
    }

//    /**
//     * @param mixed $pathologyService
//     */
//    public function setPathologyService($pathologyService)
//    {
//        $this->pathologyService = $pathologyService;
//    }
//
//    /**
//     * @return mixed
//     */
//    public function getPathologyService()
//    {
//        return $this->pathologyService;
//    }

    public function getName() {
        return $this->name;
    }

    public function getEmail() {
        return $this->email;
    }

    public function getPhone() {
        return $this->phone;
    }

    public function getJob() {
        return $this->job;
    }

    public function getOrganization() {
        return $this->organization;
    }

    public function getDepartment() {
        return $this->department;
    }

    public function setName($name) {
        $this->name = $name;
    }

    public function setEmail($email) {
        $this->email = $email;
    }

    public function setPhone($phone) {
        $this->phone = $phone;
    }

    public function setJob($job) {
        $this->job = $job;
    }

    public function setOrganization($organization) {
        $this->organization = $organization;
    }

    public function setDepartment($department) {
        $this->department = $department;
    }
    public function getStatus() {
        return $this->status;
    }

    public function setStatus($status) {
        $this->status = $status;
    }

    /**
     * @ORM\PrePersist
     */
    public function setCreationdate()
    {
        $this->creationdate = new \DateTime();
    }

    public function getCreationdate()
    {
        return $this->creationdate;
    }


    public function addPathologyServices(\Oleg\OrderformBundle\Entity\PathServiceList $pathologyServices)
    {
        if( !$this->pathologyServices->contains($pathologyServices) ) {
            $this->pathologyServices[] = $pathologyServices;
        }

        return $this;
    }

    public function removePathologyServices(\Oleg\OrderformBundle\Entity\PathServiceList $pathologyServices)
    {
        $this->pathologyServices->removeElement($pathologyServices);
    }

    /**
     * @param mixed $pathologyServices
     */
    public function setPathologyServices($pathologyServices)
    {
        if( $pathologyServices->first() ) {
            $this->primaryPathologyService = $pathologyServices->first()->getId();
        } else {
            $this->primaryPathologyService = NULL;
        }
        $this->pathologyServices = $pathologyServices;
    }

    /**
     * @return mixed
     */
    public function getPathologyServices()
    {
        //return $this->pathologyServices;

        $resArr = new ArrayCollection();
        foreach( $this->pathologyServices as $service ) {
            if( $service->getId()."" == $this->getPrimaryPathologyService()."" ) {
                //$resArr->removeElement($service);
                //$resArr->first();
                if( count($this->pathologyServices) > 1 ) {
                    $firstEl = $resArr->get(0);
                    $resArr->set(0,$service);
                    $resArr->add($firstEl);
                } else {
                    $resArr->add($service);
                }
            } else {
                $resArr->add($service);
            }
        }
        return $resArr;
    }

    /**
     * @param mixed $primaryPathologyService
     */
    public function setPrimaryPathologyService($primaryPathologyService)
    {
        $this->primaryPathologyService = $primaryPathologyService;
    }

    /**
     * @return mixed
     */
    public function getPrimaryPathologyService()
    {
        return $this->primaryPathologyService;
    }

}