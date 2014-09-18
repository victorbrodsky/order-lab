<?php
/**
 * Created by JetBrains PhpStorm.
 * User: oli2002
 * Date: 9/12/13
 * Time: 3:47 PM
 * To change this template use File | Settings | File Templates.
 */

namespace Oleg\OrderformBundle\Form\DataTransformer;

use Oleg\UserdirectoryBundle\Entity\User;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Doctrine\Common\Persistence\ObjectManager;

class GenericTreeTransformer implements DataTransformerInterface
{
    /**
     * @var ObjectManager
     */
    protected $em;
    protected $user;
    protected $className;

    /**
     * @param ObjectManager $om
     */
    public function __construct(ObjectManager $em=null, $user=null, $className)
    {
        $this->em = $em;
        $this->user = $user;
        $this->className = $className;
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
    public function transform($entity)
    {
        //echo "data transformer entity=".$entity."<br>";
        if( null === $entity ) {
            return "";
        }
        return $entity->getId();
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

            $entity = $this->em->getRepository('OlegUserdirectoryBundle:'.$this->className)->findOneById($text);

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

        //check if it is already exists in db
        $entity = $this->em->getRepository('OlegUserdirectoryBundle:'.$this->className)->findOneByName($name);
        
        if( null === $entity ) {

            //echo "create new<br>";
            //echo "user=".$this->user."<br>"; //user must be an object (exist in DB)
            if( !$this->user instanceof User ) {
                //user = system user
                $this->user = $this->em->getRepository('OlegUserdirectoryBundle:User')->findOneByUsername('system');
            }

            $fullClassName = "Oleg\\UserdirectoryBundle\\Entity\\".$this->className;
            $newEntity = new $fullClassName();
            $newEntity->setName($name);
            $newEntity->setCreatedate(new \DateTime());
            $newEntity->setType('user-added');
            $newEntity->setCreator($this->user);

            //get max orderinlist
            $query = $this->em->createQuery('SELECT MAX(c.orderinlist) as maxorderinlist FROM OlegUserdirectoryBundle:'.$this->className.' c');
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