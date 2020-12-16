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

namespace App\OrderformBundle\Form\DataTransformer;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Doctrine\Common\Persistence\ObjectManager;
use App\OrderformBundle\Entity\ProcedureList;

class ProcedureTransformer implements DataTransformerInterface
{
    /**
     * @var ObjectManager
     */
    private $em;
    private $user;

    /**
     * @param ObjectManager $om
     */
    public function __construct(EntityManagerInterface $em=null, $user=null)
    {
        $this->em = $em;
        $this->user = $user;
    }

    /**
     * Transforms an object to a string.
     *  Used to create form
     * @param  Issue|null $issue
     * @return string
     */
    public function transform( $input )
    {
        //echo "data transformer input=".$input."<br>";

        //if entity is string then find entity
//        if( !is_object($input) && is_string($input) ) {
//            $text = $input;
//            $entity = $this->em->getRepository('AppOrderformBundle:ProcedureList')->findOneByName($text);
//            //echo "string => get entity=".$entity."<br>";
//        } else {
//            $entity = $input;
//            //echo "entity =".$entity."<br>";
//        }

        $entity = $input;

        if( null === $entity ) {
            //echo "entity=null<br>";
            return "";
        }
        //echo "entity is not null<br>";

        //return $entity->getName();
        return $entity->getId();
    }

    /**
     * Transforms a string (number) to an object (i.e. stain).
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

        if (!$text) {
            return null;
        }

        if( is_numeric ( $text ) ) {    //number => most probably it is id

            $entity = $this->em->getRepository('AppOrderformBundle:ProcedureList')->findOneById($text);

            if( null === $entity ) {

                return $this->createNew($text); //create a new record in db

            } else {

                return $entity; //use found object

            }

        } else {    //text => most probably it is new name

            return $this->createNew($text); //create a new record in db

        }

    }

    public function createNew($name) {

        //check if it is already exists in db
        $entity = $this->em->getRepository('AppOrderformBundle:ProcedureList')->findOneByName($name);
        
        if( null === $entity ) {

            $newEntity = new ProcedureList();
            $newEntity->setName($name);
            $newEntity->setCreatedate(new \DateTime());
            $newEntity->setType('user-added');
            $newEntity->setCreator($this->user);

            //get max orderinlist
            $query = $this->em->createQuery('SELECT MAX(c.orderinlist) as maxorderinlist FROM AppOrderformBundle:ProcedureList c');           
            $nextorder = $query->getSingleResult()['maxorderinlist']+10;          
            $newEntity->setOrderinlist($nextorder);
            
            $this->em->persist($newEntity);
            $this->em->flush($newEntity);

            return $newEntity;
        } else {

            return $entity;
        }

    }


}