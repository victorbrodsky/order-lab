<?php

/**
 * Created by PhpStorm.
 * User: ch3
 * Date: 1/21/2020
 * Time: 1:50 PM
 */

namespace App\DeidentifierBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

//Dummy class for GIT to create Entity folder. Empty folders are ignored by git.

///**
// * @ORM\Entity
// * @ORM\Table(name="deidentifier_dummy")
// */
class Dummy
{

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $field;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $field2;


    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getField()
    {
        return $this->field;
    }

    /**
     * @param mixed $field
     */
    public function setField($field)
    {
        $this->field = $field;
    }

    /**
     * @return mixed
     */
    public function getField2()
    {
        return $this->field2;
    }

    /**
     * @param mixed $field2
     */
    public function setField2($field2)
    {
        $this->field2 = $field2;
    }




}

?>

