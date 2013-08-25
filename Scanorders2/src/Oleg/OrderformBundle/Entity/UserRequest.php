<?php

namespace Oleg\OrderformBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="Oleg\OrderformBundle\Repository\UserRequestRepository")
 * @ORM\Table(name="userrequest")
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
     * @ORM\Column(type="string", nullable=true, length=10)
     */
    protected $cwid;

    /**
     * @ORM\Column(type="string", nullable=true, length=500)
     */
    protected $name;
    
    /**
     * @ORM\Column(type="string", length=200)
     * @Assert\NotBlank
     */
    protected $email;
    
    /**
     * @ORM\Column(type="string", nullable=true, length=20)
     */
    protected $phone;
    
    /**
     * @ORM\Column(type="string", nullable=true, length=200)
     */
    protected $job;
    
    /**
     * @ORM\Column(type="string", nullable=true, length=200)
     */
    protected $organization;
    
    /**
     * @ORM\Column(type="string", nullable=true, length=200)
     */
    protected $department;
    
    /**
     * @ORM\Column(type="string", nullable=true, length=1000)
     */
    protected $request;

    /**
     * @ORM\Column(type="string", nullable=true, length=1000)
     */
    protected $pathologyService;
    
    /**
     * @ORM\Column(type="string", nullable=true, length=10)
     */
    protected $status;
    
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

    /**
     * @param mixed $pathologyService
     */
    public function setPathologyService($pathologyService)
    {
        $this->pathologyService = $pathologyService;
    }

    /**
     * @return mixed
     */
    public function getPathologyService()
    {
        return $this->pathologyService;
    }
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


}