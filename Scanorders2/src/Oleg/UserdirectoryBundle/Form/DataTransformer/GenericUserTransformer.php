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

namespace Oleg\UserdirectoryBundle\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Doctrine\Common\Persistence\ObjectManager;
use Oleg\UserdirectoryBundle\Entity\User;
use Oleg\UserdirectoryBundle\Security\Util\UserSecurityUtil;


class GenericUserTransformer implements DataTransformerInterface
{

    /**
     * @var ObjectManager
     */
    protected $em;
    protected $user;
    protected $bundleName;
    protected $className;
    protected $params;


    public function __construct(ObjectManager $em=null, $user=null, $className=null, $bundleName=null, $params=null)
    {
        $this->em = $em;
        $this->user = $user;
        $this->className = $className;
        $this->params = $params;

        if( $bundleName ) {
            $this->bundleName = $bundleName;
        } else {
            $this->bundleName = "UserdirectoryBundle";
        }

        if( !$this->className ) {
            throw $this->createNotFoundException('className is null');
        }
    }

    public function getThisEm() {
        return $this->em;
    }


    /**
     * Transforms an array of objects or name strings to ids.
     */
    public function transform( $entities )
    {
        //echo $entities->first()->getBlockPrefix()."<br>";
        //echo "!!!!!!!!!!!transform: entities=".$entities."<br>";
        //echo $this->className.": transform: count=".count($entities)."<br>";
        //var_dump($entities);

        $array = new \Doctrine\Common\Collections\ArrayCollection();

        if( !$entities || null === $entities->toArray() ) {
            //echo $this->className.": return empty array";
            //return $array;

            if( $this->params['multiple'] ) {
                return $array;
            } else {
                return null;
            }
        }

        if( count($entities) == 0 ) {
            return null;
        }

        if( count($entities) > 0 ) {
            $idArr = [];
            foreach( $entities as $entity ) {
                if( $entity ) {
                    //echo $entity;
                    $idArr[] = $entity->getId();
                }
            }

            //return array with primaryPrincipal as the first element
            //echo "idArr:<br>";
            //var_dump($idArr);
            //echo "return:".implode(",", $idArr)."<br>";

            return implode(",", $idArr);
        }

        //echo "return single id:".$entities->first()->getId()."<br>";
        return $entities->first()->getId();
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

        //echo "!!!!!!!!!!!data reverse transformer text=".$text."<br>";
        //exit();

        $newListArr = new \Doctrine\Common\Collections\ArrayCollection();

        if( !$text ) {
            //echo "return empty array <br>";
            //return $newListArr;

            if( $this->params['multiple'] ) {
                return $newListArr;
            } else {
                return null;
            }
        }

        if( $this->params['multiple'] ) {
            //echo "text array<br>";
            //exit();
            $textArr = explode(",", $text);
            foreach ($textArr as $entity) {
                $newListArr = $this->findEntity($newListArr, $entity);
            }

            //echo "reverseTransform: return count:".count($newListArr)."<br>";
            return $newListArr;
        } else {
            $entity = $this->em->getRepository('Oleg'.$this->bundleName.':'.$this->className)->findOneById($text);
            return $entity;
        }
    }

    public function findEntity( $newListArr, $entity ) {

        if( is_numeric ( $entity ) ) {    //number => most probably it is id

            //echo "principal=".$username." => numeric => most probably it is id<br>";

            $entity = $this->em->getRepository('Oleg'.$this->bundleName.':'.$this->className)->findOneById($entity);

            if( null === $entity ) {

                //$newList = $this->createNew($entity); //create a new record in db
                //$newListArr->add($newList);

                return $newListArr;

            } else {

                $newListArr->add($entity);

                return $newListArr;

            }

        } else {    //text => most probably it is new name or multiple ids

            //echo "principal=".$username." => text => most probably it is new name or multiple ids<br>";

            //$newList = $this->createNew($entity); //create a new record in db

//            if( $newList ) {
//                //echo "newList=".$newList."<br>";
//                $newListArr->add($newList);
//            }

            return $newListArr;

        }

    }



}
