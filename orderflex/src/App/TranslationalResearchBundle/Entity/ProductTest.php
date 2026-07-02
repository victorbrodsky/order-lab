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


#[ORM\Table(name: 'transres_producttest')]
#[ORM\Entity]
class ProductTest {

    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    #[ORM\GeneratedValue(strategy: 'SEQUENCE')]
    #[ORM\SequenceGenerator(sequenceName: 'document_id_seq', allocationSize: 1)]
    private ?int $id = null;


    public function __construct($user=null) {

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

    public function __toString()
    {
        return "ProductTest ID=".$this->getId();
    }

}