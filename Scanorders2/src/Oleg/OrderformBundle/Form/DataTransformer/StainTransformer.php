<?php
/**
 * Created by JetBrains PhpStorm.
 * User: oli2002
 * Date: 9/12/13
 * Time: 3:47 PM
 * To change this template use File | Settings | File Templates.
 */

namespace Oleg\OrderformBundle\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Doctrine\Common\Persistence\ObjectManager;
use Oleg\OrderformBundle\Entity\StainList;

class StainTransformer implements DataTransformerInterface
{
    /**
     * @var ObjectManager
     */
    private $em;
    private $user;

    /**
     * @param ObjectManager $om
     */
    public function __construct(ObjectManager $em=null, $user=null)
    {
        $this->em = $em;
        $this->user = $user;
    }

    /**
     * Transforms an object (issue) to a string (number).
     *
     * @param  Issue|null $issue
     * @return string
     */
    public function transform($stain)
    {

        if (null === $stain) {
            return "";
        }

        //echo "data transformer stain=".$stain."<br>";

        if( is_int($stain) ) {
            $stain = $this->em->getRepository('OlegOrderformBundle:StainList')->findOneById($stain);
            //echo "findOneById stain=".$stain."<br>";
        }

        return $stain->getId();
    }

    /**
     * Transforms a string (number) to an object (i.e. stain).
     *
     * @param  string $number
     *
     * @return Stain|null
     *
     * @throws TransformationFailedException if object (stain) is not found.
     */
    public function reverseTransform($text)
    {

        //echo "data transformer text=".$text."<br>";
        //exit();

        if (!$text) {
            return null;
        }

        if( is_numeric ( $text ) ) {    //number => most probably it is id

            $entity = $this->em->getRepository('OlegOrderformBundle:StainList')->findOneById($text);

            if( null === $entity ) {

                return $this->createNewStain($text); //create a new record in db

            } else {

                return $entity; //use found object

            }

        } else {    //text => most probably it is new name

            return $this->createNewStain($text); //create a new record in db

        }

    }

    public function createNewStain($name) {

        //check if it is already exists in db
        $entity = $this->em->getRepository('OlegOrderformBundle:StainList')->findOneByName($name);
        if( null === $entity ) {

            $stain = new StainList();
            $stain->setName($name);
            $stain->setCreatedate(new \DateTime());
            $stain->setType('user-added');
            $stain->setCreator($this->user);

            $this->em->persist($stain);
            $this->em->flush($stain);

            return $stain;
        } else {

            return $entity;
        }

    }


}