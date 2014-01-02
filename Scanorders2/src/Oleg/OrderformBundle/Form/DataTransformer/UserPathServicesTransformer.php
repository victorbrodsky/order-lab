<?php
/**
 * Created by JetBrains PhpStorm.
 * User: oli2002
 * Date: 9/12/13
 * Time: 3:47 PM
 * To change this template use File | Settings | File Templates.
 */

namespace Oleg\OrderformBundle\Form\DataTransformer;

//use Symfony\Component\Form\DataTransformerInterface;
//use Symfony\Component\Form\Exception\TransformationFailedException;
//use Doctrine\Common\Persistence\ObjectManager;
use Oleg\OrderformBundle\Form\DataTransformer\PathServiceTransformer;

//used by user type
class UserPathServicesTransformer extends PathServiceTransformer
{

    public function transform( $entities )
    {
//        echo $entities->first()->getName();
        $array = new \Doctrine\Common\Collections\ArrayCollection();

        if( null === $entities->toArray() ) {
            return $array;
        }

        if( count($entities) == 0 ) {
            return null;
        }

        return $entities->first()->getId();
    }


    public function reverseTransform($text)
    {

        //echo "data transformer text=".$text."<br>";
        //exit();

        if (!$text) {
            return null;
        }

        $newListArr = new \Doctrine\Common\Collections\ArrayCollection();

        if( is_numeric ( $text ) ) {    //number => most probably it is id

            $entity = $this->getThisEm()->getRepository('OlegOrderformBundle:PathServiceList')->findOneById($text);

            if( null === $entity ) {

                $newList = $this->createNew($text); //create a new record in db

                $newListArr->add($newList);

                return $newListArr;

            } else {

                $newListArr->add($entity);

                return $newListArr;

            }

        } else {    //text => most probably it is new name

            $newList = $this->createNew($text); //create a new record in db

            $newListArr->add($newList);

            return $newListArr;

        }

    }

}