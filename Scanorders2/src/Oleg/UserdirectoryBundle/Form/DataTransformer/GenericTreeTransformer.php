<?php
/**
 * Created by JetBrains PhpStorm.
 * User: oli2002
 * Date: 9/12/13
 * Time: 3:47 PM
 * To change this template use File | Settings | File Templates.
 */

namespace Oleg\UserdirectoryBundle\Form\DataTransformer;



use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Doctrine\Common\Persistence\ObjectManager;

use Oleg\UserdirectoryBundle\Entity\User;
use Oleg\UserdirectoryBundle\Security\Util\UserSecurityUtil;

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
    public function __construct(ObjectManager $em=null, $user=null, $className=null)
    {
        $this->em = $em;
        $this->user = $user;
        $this->className = $className;
    }

    public function getThisEm() {
        return $this->em;
    }


    /**
     * Transforms an object or name string to id.
     *
     * @param  Issue|null $issue
     * @return string
     */
    public function transform($entity)
    {

        if( null === $entity || $entity == "" ) {
            return "";
        }

        //echo "data transformer entity=".$entity."<br>";
        //echo "data transformer entity id=".$entity->getId()."<br>";

        if( is_int($entity) ) {
            //echo "transform by id=".$entity." !!!<br>";
            $entity = $this->em->getRepository('OlegUserdirectoryBundle:'.$this->className)->findOneById($entity);
            //echo "findOneById entity=".$entity."<br>";
        }
//        else {
//            //echo "transform by name=".$entity." ????????????????<br>";
//            $entity = $this->em->getRepository('OlegUserdirectoryBundle:'.$this->className)->findOneByName($entity);
//        }

        if( null === $entity ) {
            return "";
        }

        //return $entity->getId();

        //echo "count=".count($entity)."<br>";

        return $entity->getId();
    }

    /**
     * Transforms a string (number) to an object.
     *
     * @param  string $number
     *
     * @return Stain|null
     *
     * @throws TransformationFailedException if object (stain) is not found.
     */
    public function reverseTransform($text)
    {

        //echo "data reverse transformer text=".$text."<br>";
        //exit();

        if( !$text ) {
            //exit('text is null');
            //echo "data transformer text is null <br>";
            return null;
        }

        if( is_numeric ( $text ) ) {    //number => most probably it is id

            //echo 'text is id <br>';

            $entity = $this->em->getRepository('OlegUserdirectoryBundle:'.$this->className)->findOneById($text);

            if( null === $entity ) {

                //exit('create new???');
                return $this->createNew($text); //create a new record in db

            } else {

                //echo "found:".$entity->getName()."<br>";
                //exit('use found object <br>');
                return $entity; //use found object

            }

        } else {    //text => most probably it is new name

            //exit('text is a new record name');
            //echo "text is a new record name=".$text."<br>";
            return $this->createNew($text); //create a new record in db

        }

    }

    public function createNew($name) {

        //echo "enter create new name=".$name."<br>";
        //exit('create new !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!');

        //check if it is already exists in db
        $entity = $this->em->getRepository('OlegUserdirectoryBundle:'.$this->className)->findOneByName($name."");
        
        if( null === $entity ) {

            //echo "create new with name=".$name."<br>";
            //echo "user=".$this->user."<br>"; //user must be an object (exist in DB)
            if( !$this->user instanceof User ) {
                //user = system user
                $userSecUtil = new UserSecurityUtil($this->em,null,null);
                $this->user = $userSecUtil->findSystemUser();
            }

            $newEntity = $this->createNewEntity($name."",$this->className,$this->user);

            if( method_exists($newEntity,'getParent')  ) {
                //don't flush this entity because it has parent and parent can not be set here
                //echo "this entity has parent => don't create <br>";
                //echo "name=".$newEntity->getName()."<br>";
                //$this->em->persist($newEntity);
                return $newEntity;
            }

            //echo "persist and flush !!!!!!!!!!!!!!!! <br>";
            $this->em->persist($newEntity);
            $this->em->flush($newEntity);

            return $newEntity;
        } else {

            return $entity;
        }

    }


    public function createNewEntity($name,$className,$creator) {
        $fullClassName = "Oleg\\UserdirectoryBundle\\Entity\\".$className;
        $newEntity = new $fullClassName();

        $newEntity = $this->populateEntity($newEntity);

        $newEntity->setName($name."");
        $newEntity->setCreator($creator);

        return $newEntity;
    }

    public function populateEntity($entity) {
        //exit('1');

        $entity->setCreatedate(new \DateTime());
        $entity->setType('user-added');

        $fullClassName = new \ReflectionClass($entity);
        $className = $fullClassName->getShortName();

        //get max orderinlist
        $query = $this->em->createQuery('SELECT MAX(c.orderinlist) as maxorderinlist FROM OlegUserdirectoryBundle:'.$className.' c');
        $nextorder = $query->getSingleResult()['maxorderinlist']+10;
        $entity->setOrderinlist($nextorder);

        return $entity;
    }

}