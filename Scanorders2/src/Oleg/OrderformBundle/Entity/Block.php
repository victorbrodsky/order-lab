<?php

namespace Oleg\OrderformBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="Oleg\OrderformBundle\Repository\BlockRepository")
 * @ORM\Table(name="block")
 */
class Block
{
    
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;
    
    //Block belongs to exactly one Accession => Block has only one Accession
    /**
     * @ORM\ManyToOne(targetEntity="Accession", inversedBy="block")
     * @ORM\JoinColumn(name="accession_id", referencedColumnName="id")
     * @Assert\NotBlank
     */
    protected $accession;

    /**
     * Name is a letter (A,B ...)
     * @ORM\Column(type="string", length=1)
     * @Assert\NotBlank   
     */
    protected $name;   
   
    public function getId() {
        return $this->id;
    }

    public function getAccession() {
        return $this->accession;
    }

    public function getName() {
        return $this->name;
    }

    public function setId($id) {
        $this->id = $id;
    }

    public function setAccession($accession) {
        $this->accession = $accession;
    }

    public function setName($name) {
        $this->name = $name;
    }
    
}