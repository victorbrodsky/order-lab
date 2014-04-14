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
use Oleg\OrderformBundle\Entity\SetTitleList;

class SetTitleTransformer implements DataTransformerInterface
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

    public function getThisEm() {
        return $this->em;
    }

    /**
     * Transforms an object to a string.
     *
     * @param  Issue|null $issue
     * @return string
     */
    public function transform($type)
    {        

        if (null === $type) {
            return "";
        }

        $type = $type->first();

        echo "setTitles: data transformer type=".$type."<br>";

        if( is_int($type) ) {
            $type = $this->em->getRepository('OlegOrderformBundle:SetTitleList')->findOneById($type);
            //echo "findOneById type=".$type."<br>";
        } else {
            $type = $this->em->getRepository('OlegOrderformBundle:SetTitleList')->findOneByName($type);
        }
        
        if (null === $type) {
            return "";
        }

        return $type->getId();

    }

    /**
     * Transforms a string (number) to an object (i.e. stain).
     *
     * @param  string $number
     *
     * @return SetTitleList|null
     *
     * @throws TransformationFailedException if object (SetTitleList) is not found.
     */
    public function reverseTransform($text)
    {

        //echo "data transformer text=".$text."<br>";
        //exit();

        if (!$text) {
            return null;
        }

        if( is_numeric ( $text ) ) {    //number => most probably it is id

            $entity = $this->em->getRepository('OlegOrderformBundle:SetTitleList')->findOneById($text);

            if( null === $entity ) {

                return $this->createNew($text); //create a new record in db

            } else {

                return $entity; //use found object

            }

        } else {    //text => most probably it is new name

            return $this->createNew($text); //create a new record in db

        }


    }

    public function createNew($name) {

        //echo "mrn type name=".$name."<br>";

        //check if it is already exists in db
        $entity = $this->em->getRepository('OlegOrderformBundle:SetTitleList')->findOneByName($name);
        
        if( null === $entity ) {

            $newEntity = new SetTitleList();
            $newEntity->setName($name);
            $newEntity->setCreatedate(new \DateTime());
            $newEntity->setType('default');
            $newEntity->setCreator($this->user);
            
            //get max orderinlist
            $query = $this->em->createQuery('SELECT MAX(c.orderinlist) as maxorderinlist FROM OlegOrderformBundle:SetTitleList c');
            $nextorder = $query->getSingleResult()['maxorderinlist']+10;          
            $newEntity->setOrderinlist($nextorder);

            $this->em->persist($newEntity);
            $this->em->flush($newEntity);

            return $newEntity;
        } else {

            return $entity;
        }

    }


}