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

//used by user type
class UserListTransformer implements DataTransformerInterface
{

    /**
     * @var ObjectManager
     */
    private $em;
    private $serviceContainer;
    private $user;
    private $className;
    private $bundleName;

    /**
     * @param ObjectManager $om
     */
    public function __construct( ObjectManager $em=null, $serviceContainer=null, $user=null, $className=null, $bundleName=null )
    {
        $this->em = $em;
        $this->serviceContainer = $serviceContainer;
        $this->user = $user;
        $this->className = $className;

        if( $bundleName ) {
            $this->bundleName = $bundleName;
        } else {
            $this->bundleName = "OrderformBundle";
        }
    }

    /**
     * Use for 'Show'
     *
     * Transforms an object to a string.
     *
     * @param  Issue|null $issue
     * @return string
     */
    public function transform( $entities )
    {
        //echo $entities->first()->getName()."<br>";
        //echo "transform: entities=".$entities."<br>";
        //echo $this->className.": transform: count=".count($entities)."<br>";
        //var_dump($entities);

        $array = new \Doctrine\Common\Collections\ArrayCollection();

        if( !$entities || null === $entities->toArray() ) {
            //echo $this->className.": return empty array";
            return $array;
        }

        if( count($entities) == 0 ) {
            return null;
        }

        if( count($entities) > 0 ) {
            $idArr = [];
            foreach( $entities as $entity ) {
                if( $entity ) {
                    $idArr[] = $entity->getUserStr();
                }
            }
            
            //return array with primaryPrincipal as the first element
            //echo "idArr:<br>";
            //var_dump($idArr);
            //echo "return:".implode(",", $idArr)."<br>";
            
            return implode(",", $idArr);
        }

        return $entities->first()->getId();
    }

    /**
     * Use for 'Submit'
     *
     * Transforms a string (number) to an object (i.e. stain).
     *
     * @param  string $number
     *
     * @return $this->className|null
     *
     * @throws TransformationFailedException if object ($this->className) is not found.
     */
    public function reverseTransform($text)
    {

//        var_dump($text);
//        echo "<br>transformer: count=".count($text)."<br>";
//        echo "data transformer text=".$text."<br>";
//        if( $this->className == 'PIList') {
//            //exit();
//        }

        $newListArr = new \Doctrine\Common\Collections\ArrayCollection();

        if( !$text ) {
            //echo "return empty array <br>";
            return $newListArr;
        }

        //$newListArr = $this->addSingleService( $newListArr, $text );
        //return $newListArr;

        //TODO: this assumes that entered text does not have comma!
        if( strpos($text,',') !== false ) {

            //echo "text array<br>";
            //exit();
            $textArr = explode(",", $text);
            foreach( $textArr as $principal ) {
                //echo "principal text=".$principal."<br>";
                $newListArr = $this->addSingleUser( $newListArr, $principal );
            }

            //echo "reverseTransform: return count:".count($newListArr)."<br>";
            return $newListArr;

        } else {

            $newListArr = $this->addSingleUser( $newListArr, $text );
            return $newListArr;

        }

    }

    public function addSingleUser( $newListArr, $username ) {

        if( is_numeric ( $username ) ) {    //number => most probably it is id

            //echo "principal=".$username." => numeric => most probably it is id<br>";

            //entity is a DirectorList, PIList (or user wrapper?)
            $entity = $this->em->getRepository('Oleg'.$this->bundleName.':'.$this->className)->findOneById($username);

            if( null === $entity ) {

                $newList = $this->createNew($username); //create a new record in db

                $newListArr->add($newList);

                return $newListArr;

            } else {

                $newListArr->add($entity);

                return $newListArr;

            }

        } else {    //text => most probably it is new name or multiple ids

            //echo "principal=".$username." => text => most probably it is new name or multiple ids<br>";

            $newList = $this->createNew($username); //create a new record in db

            if( $newList ) {
                //echo "newList=".$newList."<br>";
                $newListArr->add($newList);
            }

            return $newListArr;

        }
    }

    //createNew DirectorList, PIList
    //name is entered by a user username
    public function createNew( $name ) {

        //echo "create: name=".$name."<br>";

        $userSecUtil = $this->serviceContainer->get('user_security_utility');

        //check if it is already exists in db
        $entity = $this->em->getRepository('Oleg'.$this->bundleName.':'.$this->className)->findOneByName($name);

        if( null === $entity ) {

            $user = $userSecUtil->getUserByUserstr( $name );

            $entityClass = "Oleg\\".$this->bundleName."\\Entity\\".$this->className;

            $newEntity = new $entityClass();
            $newEntity->setName($name);
            $newEntity->setCreatedate(new \DateTime());
            $newEntity->setType('user-added');
            $newEntity->setCreator($this->user);
            $newEntity->setUserObjectLink($user);

            //get max orderinlist
            $query = $this->em->createQuery('SELECT MAX(c.orderinlist) as maxorderinlist FROM Oleg'.$this->bundleName.':'.$this->className.' c');
            $nextorder = $query->getSingleResult()['maxorderinlist']+10;
            $newEntity->setOrderinlist($nextorder);

            $this->em->persist($newEntity);
            $this->em->flush($newEntity);

            return $newEntity;

        } else {

            //update if the user object is not set, maybe we have it now
            if( !$entity->getUserObjectLink() ) {
                $user = $userSecUtil->getUserByUserstr( $name );
                if( $user ) {
                    $entity->setUserObjectLink($user);
                    $this->em->persist($entity);
                    $this->em->flush($entity);
                }
            }

            return $entity;
        }

    }



}