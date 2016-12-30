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
class SingleUserWrapperTransformer extends UserWrapperTransformer//implements DataTransformerInterface
{


    /**
     * Use for 'Show'
     *
     * Transforms an UserWrapper object to a string.
     *
     * @param  Issue|null $issue
     * @return string
     */
    public function transform( $entity )
    {
        //echo "first entity=".$entities->first()."<br>";
        //echo "transform: entity=".$entity."<br>";
        //echo $this->className.": transform: count=".count($entities)."<br>";
        //var_dump($entities);

        //$array = new \Doctrine\Common\Collections\ArrayCollection();

        if( !$entity ) {
            //echo "return empty <br>";
            return null;
        }

        //echo "return ".$entities->first()->getId()."<br>";
        return $entity->getEntity();
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
        //echo "data transformer single text=".$text."<br>";

        if( !$text ) {
            //echo "return empty <br>";
            return null;
        }

        //$newListArr = $this->addSingleService( $newListArr, $text );
        //return $newListArr;

        $transformedEntity = null;
        $newListArr = new \Doctrine\Common\Collections\ArrayCollection();

        //echo "single text no array<br>";
        $newListArr = $this->addSingleObject( $newListArr, $text );
        $transformedEntity = $newListArr->first();

//        echo "transformedEntity id=".$transformedEntity->getId()."<br>";
//        echo "transformedEntity name=".$transformedEntity->getName()."<br>";
//        echo "transformedEntity user=".$transformedEntity->getUser()."<br>";
//        exit('1');

        return $transformedEntity; //UserWrapper
    }


    public function reverseTransformByType($text,$usernameType='UserWrapper') {

        if( !$text ) {
            return null;
        }

        $transformedEntity = null;
        $newListArr = new \Doctrine\Common\Collections\ArrayCollection();

        $newListArr = $this->addSingleObject( $newListArr, $text, $usernameType );
        $transformedEntity = $newListArr->first();

        return $transformedEntity; //UserWrapper
    }


}