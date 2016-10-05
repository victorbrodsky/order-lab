<?php
/**
 * Created by JetBrains PhpStorm.
 * User: oli2002
 * Date: 9/12/13
 * Time: 3:47 PM
 * To change this template use File | Settings | File Templates.
 */

namespace Oleg\UserdirectoryBundle\Form\DataTransformer;

use Oleg\UserdirectoryBundle\Entity\UserWrapper;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Doctrine\Common\Persistence\ObjectManager;

//used by user type
class UserWrapperTransformer implements DataTransformerInterface
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
    public function __construct( ObjectManager $em, $serviceContainer, $user=null, $className=null, $bundleName=null )
    {
        $this->em = $em;
        $this->serviceContainer = $serviceContainer;
        $this->user = $user;

        if( $className ) {
            $this->className = $className;
        } else {
            $this->className = "UserWrapper";
        }

        if( $bundleName ) {
            $this->bundleName = $bundleName;
        } else {
            $this->bundleName = "UserdirectoryBundle";
        }
    }

    /**
     * Use for 'Show'
     *
     * Transforms an UserWrapper object to a string.
     *
     * @param  Issue|null $issue
     * @return string
     */
    public function transform( $entities )
    {
        //echo "first entity=".$entities->first()."<br>";
        //echo "transform: entities=".$entities."<br>";
        //echo $this->className.": transform: count=".count($entities)."<br>";
        //var_dump($entities);

        $array = new \Doctrine\Common\Collections\ArrayCollection();

        if( !$entities || null === $entities->toArray() ) {
            //echo "return empty array";
            return $array;
        }

        if( count($entities) == 0 ) {
            return null;
        }

        if( count($entities) > 0 ) {

//            foreach( $entities as $entity ) {
//                echo "userWrapper=".$entity->getId()."<br>";
//            }

            $idArr = [];
            foreach( $entities as $entity ) {
                if( $entity ) {
                    //echo "add userwrapper to show ".$entity->getEntity()."<br>";
                    //$idArr[] = $entity->getUserStr();
                    $idArr[] = $entity->getEntity();
                    //$idArr[] = $entity->getId();
                }
            }
            
            //return array with primaryPrincipal as the first element
            //echo "idArr:<br>";
            //var_dump($idArr);
            //echo "return:".implode(",", $idArr)."<br>";

            return implode(",", $idArr);
        }

        //echo "return ".$entities->first()->getId()."<br>";
        return $entities->first()->getEntity();
    }

    /**
     * Use for 'Submit'
     *
     * Transform a user (user id) or userstr (user string) to an UserWrapper object (i.e. user).
     *
     * @param  string $number
     *
     * @return $this->className|null
     *
     * @throws TransformationFailedException if object ($this->className) is not found.
     */
    public function reverseTransform($text)
    {

        //var_dump($text);
        //echo "<br>transformer: count=".count($text)."<br>";
        //echo "data transformer text=".$text."<br>";

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
                $newListArr = $this->addSingleObject( $newListArr, $principal );
            }

            //echo "reverseTransform: return count:".count($newListArr)."<br>";

        } else {

            $newListArr = $this->addSingleObject( $newListArr, $text );


        }

        //echo "newListArr count=".count($newListArr)."<br>";
        //exit('1');

        return $newListArr; //UserWrapper
    }

    //$username - user id or user as string
    //return array of UserWrapper
    public function addSingleObject( $newListArr, $username ) {

        //echo "username=".$username."<br>";

        if( is_numeric ( $username ) ) {    //number => most probably it is id

            //echo "principal=".$username." => numeric => most probably it is a UserWrapper id<br>";

            //$entity = $this->em->getRepository('Oleg'.$this->bundleName.':'.$this->className)->findOneById($username);
            //$entity = $this->em->getRepository('OlegUserdirectoryBundle:UserWrapper')->findOneById($username);

//            $query = $this->em->createQueryBuilder()
//                ->from('OlegUserdirectoryBundle:UserWrapper', 'list')
//                ->select("list")
//                //->select("list.id as id, infos.displayName as text")
//                //->leftJoin("list.user", "user")
//                ->where("list=:userWrapperId")
//                ->setParameters( array(
//                    'userWrapperId' => $username
//                ));
//            $userWrappers = $query->getQuery()->getResult();

            $userWrapper = $this->em->getRepository('OlegUserdirectoryBundle:UserWrapper')->find($username);
            //echo "userWrapper=".$userWrapper."<br>";

            //if( $userWrappers && count($userWrappers) > 0 ) {
            if( $userWrapper ) {

                $newListArr->add($userWrapper);
                //return $newListArr;

            } else {

                $newList = $this->createNewUserWrapperByUserId($username); //create a new UserWrapper record in db

                $newListArr->add($newList);
                //return $newListArr;
            }

        } else {    //text => most probably it is new name or multiple ids

            //echo "principal=".$username." => text => most probably it is new name or multiple ids<br>";

            $newList = $this->createNewUserWrapperByUserStr($username); //create a new record in db

            if( $newList ) {
                //echo "newList=".$newList."<br>";
                $newListArr->add($newList);
            }

            //return $newListArr;

        }

        return $newListArr;
    }

    public function createNewUserWrapperByUserId( $userid ) {

        $userWrapper = null;

        //find user by id
        $user = $this->em->getRepository('OlegUserdirectoryBundle:User')->find($userid);

        $userWrapper = $this->em->getRepository('OlegUserdirectoryBundle:UserWrapper')->findSimilarEntity($user,null);
        //echo "found userWrapper by wrapper id=".$userWrapper."<br>";

        if( $userWrapper ) {
            return $userWrapper;
        }

        if( $user ) {
            $userWrapper = new UserWrapper();
            $userWrapper->setUser($user);
        }

        return $userWrapper;
    }

    public function createNewUserWrapperByUserStr( $userStr ) {

        $userWrapper = null;

        $userSecUtil = $this->serviceContainer->get('user_security_utility');

        //find user by user string
        $user = $userSecUtil->getUserByUserstr( $userStr );
        //echo "found user=".$user."<br>";

        $userWrapper = $this->em->getRepository('OlegUserdirectoryBundle:UserWrapper')->findSimilarEntity($user,$userStr);
        //echo "found userWrapper=".$userWrapper->getId()."<br>";

        //exit('1');

        if( $userWrapper ) {
            return $userWrapper;
        }

        if( $user ) {

            $userWrapper = new UserWrapper();
            $userWrapper->setUser($user);
            $userWrapper->setUserStr($userStr);

        } else {

            $userWrapper = new UserWrapper();
            $userWrapper->setUserStr($userStr);

        }

        return $userWrapper;
    }


}