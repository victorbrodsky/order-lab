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
use Oleg\OrderformBundle\Entity\PathServiceList;

class PathServicesTransformer implements DataTransformerInterface
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
     * @param  Issue|null $issue
     * @return string
     */
    public function transform($entity)
    {
        $array = new \Doctrine\Common\Collections\ArrayCollection();

        if( null === $entity->toArray() ) {
            return $array;
        }
        return $array;
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

        echo "data transformer text=".$text."<br>";
        //exit();

        if (!$text) {
            return null;
        }

        if( is_numeric ( $text ) ) {    //number => most probably it is id of existing Pathology Service

            $entity = $this->em->getRepository('OlegOrderformBundle:PathServiceList')->findOneById($text);

            if( null === $entity ) {

                return $this->createNew($text); //create a new record in db

            } else {

                return array($entity); //use found object

            }

        } else {    //text => most probably it is new name

            return $this->createNew($text); //create a new record in db

        }

    }

    public function createNew($names) {

//        echo "names=".$names."<br>";
//        print_r($names);
//        exit();

        $entities = array();

        foreach( explode(",", $names) as $name ) {

            $name = trim($name);
            //echo "name=".$name."<br>";

            //check if it is already exists in db
            if (is_numeric($name)) {
                $entity = $this->em->getRepository('OlegOrderformBundle:PathServiceList')->find($name); //id is given; entity exists in db
            } else {
                $entity = $this->em->getRepository('OlegOrderformBundle:PathServiceList')->findOneByName($name);    //new name is given; entity does not exist in db
            }

            if( null === $entity ) {
                $entities[] = $this->createPathService($this->em,$name,$this->user);
            } else {
                $entities[] = $entity;
            }

        }

        //print_r($entities);
        //exit();

        return $entities;

    }

    public function createPathService($em,$name,$user) {
        $newEntity = new PathServiceList();
        $newEntity->setName($name);
        $newEntity->setCreatedate(new \DateTime());
        $newEntity->setType('user-added');
        $newEntity->setCreator($user);
        $em->persist($newEntity);
        $em->flush($newEntity);
        return $newEntity;
    }


}