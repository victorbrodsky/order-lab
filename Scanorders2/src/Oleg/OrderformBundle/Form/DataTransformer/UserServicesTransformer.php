<?php
/**
 * Created by JetBrains PhpStorm.
 * User: oli2002
 * Date: 9/12/13
 * Time: 3:47 PM
 * To change this template use File | Settings | File Templates.
 */

namespace Oleg\OrderformBundle\Form\DataTransformer;


//used by user type
class UserServicesTransformer extends ServiceTransformer
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

                    $securityUtil = $this->container->get('order_security_utility');
                    $defaultService = $securityUtil->getUserDefaultService();
                    if( $entity->getId() == $defaultService->getId() ) {
                        array_unshift($idArr, $entity->getId()); //Prepend to the beginning of an array
                    } else {
                        $idArr[] = $entity->getId();
                    }
                }
            }
            
            //return array with primaryService as the first element
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

        //TODO: this implies that service does not have comma!
        if( strpos($text,',') !== false ) {

            //echo "text array<br>";
            //exit();
            $textArr = explode(",", $text);
            foreach( $textArr as $service ) {
                $newListArr = $this->addSingleService( $newListArr, $service );
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

            $entity = $this->getThisEm()->getRepository('OlegUserdirectoryBundle:Service')->findOneById($service);

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