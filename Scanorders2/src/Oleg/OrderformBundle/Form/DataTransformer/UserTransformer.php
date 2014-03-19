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
use Oleg\OrderformBundle\Entity\User;

class UserTransformer implements DataTransformerInterface
{
    /**
     * @var ObjectManager
     */
    private $em;
    private $user;
    private $serviceContainer;
    private $classtype;

    /**
     * @param ObjectManager $om
     */
    public function __construct(ObjectManager $em=null, $user=null, $serviceContainer, $classtype )
    {
        $this->em = $em;
        $this->user = $user;
        $this->serviceContainer = $serviceContainer;
        $this->classtype = $classtype;
    }

    /**
     * Transforms an object (issue) to a string (number).
     *
     * @param  Issue|null $issue
     * @return string
     */
    public function transform($user)
    {

        if (null === $user) {
            return "";
        }

        //echo "data transformer user=".$user."<br>";

        if( is_int($user) ) {
            $user = $this->em->getRepository('OlegOrderformBundle:User')->findOneById($user);
            //echo "findOneById user=".$user."<br>";
        }

        return $user->getId();
    }

    /**
     * Transforms a string (number) to an object.
     * @throws TransformationFailedException if object (user) is not found.
     */
    public function reverseTransform($text)
    {

        //echo "data transformer text=".$text."<br>";
        //exit();

        if( !$text ) {
            return null;
        }

        if( is_numeric ( $text ) ) {    //number => most probably it is id

            $entity = $this->em->getRepository('OlegOrderformBundle:User')->findOneById($text);

            if( null === $entity ) {

                return $this->createNewUser($text); //create a new record in db

            } else {

                return $entity; //use found object

            }

        } else {    //text => most probably it is new name

            return $this->createNewUser($text); //create a new record in db

        }

    }

    public function createNewUser( $name ) {

        $name = trim($name);

        //record name as a string to separate field. Later on, admin will create and link this new user to the object
        $entity = $this->em->getRepository('OlegOrderformBundle:User')->findOneByUsername($name);
        if( null === $entity ) {

            $userManager = $this->serviceContainer->get('fos_user.user_manager');
            $user = $userManager->createUser();

            $clearName = preg_replace('/\s+/', '', $name);

            $user->setUsername($clearName);
            $user->setDisplayName($name);
            $user->setEmail('');
            $user->setEnabled(true);
            $user->setLocked(false);
            $user->setCreatedby('user-added');
            $user->setPlainPassword($clearName);

            //set Roles: aperio users can submit order by default.
            $user->addRole('ROLE_UNAPPROVED_SUBMITTER');

            if( $this->classtype == 'optionalUserEducational' ) {
                $user->addRole('ROLE_COURSE_DIRECTOR');
            }

            if( $this->classtype == 'optionalUserResearch' ) {
                $user->addRole('ROLE_PRINCIPAL_INVESTIGATOR');
            }

            return $user;
        } else {

            return $entity;
        }

    }


}