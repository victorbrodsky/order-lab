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
     * @ORM\Column(type="string", nullable=true, length=1000)
     */
    protected $request;

    /**
     * @ORM\Column(type="string", nullable=true, length=1000)
     */
    protected $pathologyService;
    
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


}