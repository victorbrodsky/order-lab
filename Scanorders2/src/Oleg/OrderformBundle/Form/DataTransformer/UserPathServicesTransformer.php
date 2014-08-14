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
        //echo $entities->first()->getName()."<br>";
        //echo "count=".count($entities)."<br>";

        $array = new \Doctrine\Common\Collections\ArrayCollection();

        if( !$entities || null === $entities->toArray() ) {
            return $array;
        }

        if( count($entities) == 0 ) {
            return null;
        }

        if( count($entities) > 1 ) {
            $idArr = [];
            foreach( $entities as $entity ) {
                if( $entity ) {
                    if( $entity->getId()."" == $this->user->getPrimaryDivision()."" ) {
                        array_unshift($idArr, $entity->getId()); //Prepend to the beginning of an array
                    } else {
                        $idArr[] = $entity->getId();
                    }
                }
            }
            
            //return array with primaryDivision as the first element
            return implode(",", $idArr);
        }

        return $entities->first()->getId();
    }


    public function reverseTransform($text)
    {

        //echo "data transformer text=".$text."<br>";
        //exit();

        if (!$text) {
            $newListArr = new \Doctrine\Common\Collections\ArrayCollection();
            return $newListArr;
        }

        $newListArr = new \Doctrine\Common\Collections\ArrayCollection();

        //TODO: this implies that pathology service does not have comma!
        if( strpos($text,',') !== false ) {

            //echo "text array<br>";
            //exit();
            $textArr = explode(",", $text);
            foreach( $textArr as $pathservice ) {
                $newListArr = $this->addSingleService( $newListArr, $pathservice );
            }
            return $newListArr;

        } else {

            $newListArr = $this->addSingleService( $newListArr, $text );
            return $newListArr;

        }

    }

    public function addSingleService( $newListArr, $service ) {

        if( is_numeric ( $service ) ) {    //number => most probably it is id

            //echo "service=".$service." => numeric => most probably it is id<br>";

            $entity = $this->getThisEm()->getRepository('OlegOrderformBundle:PathServiceList')->findOneById($service);

            if( null === $entity ) {

                $newList = $this->createNew($service); //create a new record in db

                $newListArr->add($newList);

                return $newListArr;

            } else {

                $newListArr->add($entity);

                return $newListArr;

            }

        } else {    //text => most probably it is new name or multiple ids

            //echo "service=".$service." => text => most probably it is new name or multiple ids<br>";

            $newList = $this->createNew($service); //create a new record in db

            if( $newList ) {
                //echo "newList="+$newList+"<br>";
                $newListArr->add($newList);
            }

            return $newListArr;

        }
    }

}