<?php
/**
 * Copyright (c) 2017 Cornell University
 *
 *  Licensed under the Apache License, Version 2.0 (the "License");
 *  you may not use this file except in compliance with the License.
 *  You may obtain a copy of the License at
 *
 *  http://www.apache.org/licenses/LICENSE-2.0
 *
 *  Unless required by applicable law or agreed to in writing, software
 *  distributed under the License is distributed on an "AS IS" BASIS,
 *  WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 *  See the License for the specific language governing permissions and
 *  limitations under the License.
 */

/**
 * Created by JetBrains PhpStorm.
 * User: oli2002
 * Date: 9/12/13
 * Time: 3:47 PM
 * To change this template use File | Settings | File Templates.
 */

namespace App\UserdirectoryBundle\Form\DataTransformer;

use App\UserdirectoryBundle\Entity\UserWrapper;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerInterface;

//NOTE: userWrapper used:
//CourseTitleTree, Educational
//ProjectTitleTree, Research
//in Message (as proxyuser)
//PathologyResultSignaturesList
//EncounterAttendingPhysician (as field)
//EncounterReferringProvider (as field)

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
    public function __construct( EntityManagerInterface $em, ContainerInterface $serviceContainer, $user=null, $className=null, $bundleName=null )
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
        //exit('transform');

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
//                //echo "userWrapper=".$entity->getId()."<br>";
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
        //exit('reverse');

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
    public function addSingleObject( $newListArr, $username, $usernameType='UserWrapper' ) {

        //echo "userwrapper: username=".$username."<br>";

        if( is_numeric ( $username ) ) {    //number => most probably it is id

            //echo "principal=".$username." => numeric => most probably it is a UserWrapper id<br>";

            if( $usernameType == 'UserWrapper' ) {
                $userWrapper = $this->em->getRepository('AppUserdirectoryBundle:UserWrapper')->find($username);
            } else {
                $userWrapper = null;
            }
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
        $user = $this->em->getRepository('AppUserdirectoryBundle:User')->find($userid);
        //echo "found user by id=".$userid."<br>";

        $userWrapper = $this->em->getRepository('AppUserdirectoryBundle:UserWrapper')->findSimilarEntity($user,null);
        //echo "found userWrapper by wrapper id=".$userWrapper."<br>";

        if( $userWrapper ) {
            if( $user && !$userWrapper->getUser() ) {
                $userWrapper->setUser($user);
            }
            return $userWrapper;
        }

        //create new UserWrapper
        //echo "create new UserWrapper: userid=".$userid."<br>";
        if( $user ) {
            $userWrapper = new UserWrapper($this->user);
            $userWrapper->setUser($user);

            $this->em->persist($userWrapper);
            $this->em->flush($userWrapper);
        }

        return $userWrapper;
    }

    public function createNewUserWrapperByUserStr( $userStr ) {

        $userWrapper = null;

        $userSecUtil = $this->serviceContainer->get('user_security_utility');

        //find user by user string
        $user = $userSecUtil->getUserByUserstr( $userStr );
        //echo "found user=".$user."<br>";

        $userWrapper = $this->em->getRepository('AppUserdirectoryBundle:UserWrapper')->findSimilarEntity($user,$userStr);
        //echo "found userWrapper=".$userWrapper."<br>";

        //exit('1');

        if( $userWrapper ) {
            if( $user && !$userWrapper->getUser() ) {
                $userWrapper->setUser($user);
            }
            return $userWrapper;
        }

        //echo "create new UserWrapper: userStr=".$userStr."<br>";
        if( $user ) {
            $userWrapper = new UserWrapper($this->user);
            $userWrapper->setUser($user);
            $userWrapper->setUserStr($userStr);
        } else {
            $userWrapper = new UserWrapper($this->user);
            $userWrapper->setUserStr($userStr);
        }

        $this->em->persist($userWrapper);
        $this->em->flush($userWrapper);

        return $userWrapper;
    }


}