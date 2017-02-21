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

namespace Oleg\OrderformBundle\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Doctrine\Common\Persistence\ObjectManager;
use Oleg\OrderformBundle\Entity\StainList;

class StainTransformer implements DataTransformerInterface
{
    /**
     * @var ObjectManager
     */
    private $em;
    private $user;

    /**
     * @param ObjectManager $om
     */
    public function __construct(ObjectManager $em=null, $user=null)
    {
        $this->em = $em;
        $this->user = $user;
    }

    /**
     * Transforms an object (issue) to a string (number).
     *
     * @param  Issue|null $issue
     * @return string
     */
    public function transform($stain)
    {

        if (null === $stain) {
            return "";
        }

        //echo "data transformer stain=".$stain."<br>";
        //echo "data transformer stain id=".$stain->getId()."<br>";

        if( is_int($stain) ) {
            //echo "transform stain by id=".$stain->getId()."<br>";
            $stain = $this->em->getRepository('OlegOrderformBundle:StainList')->findOneById($stain);
            //echo "findOneById stain=".$stain."<br>";
        }
        
        if( null === $stain ) {
            return "";
        }

        return $stain->getId();
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

        //echo "data transformer text=".$text."<br>";
        //exit();

        if (!$text) {
            //echo "return null".$text."<br>";
            return null;
        }

        if( is_numeric ( $text ) ) {    //number => most probably it is id

            $entity = $this->em->getRepository('OlegOrderformBundle:StainList')->findOneById($text);

            if( null === $entity ) {

                return $this->createNewStain($text); //create a new record in db

            } else {

                return $entity; //use found object

            }

        } else {    //text => most probably it is new name

            //echo "text => most probably it is new name=".$text."<br>";
            return $this->createNewStain($text); //create a new record in db

        }

    }

    public function createNewStain($name) {

        //check if it is already exists in db
        $entity = $this->em->getRepository('OlegOrderformBundle:StainList')->findOneByName($name);
        //echo "db entity=".$entity."<br>";
        if( null === $entity ) {

            $stain = new StainList();
            $stain->setName($name);
            $stain->setCreatedate(new \DateTime());
            $stain->setType('user-added');
            $stain->setCreator($this->user);

            //get max orderinlist
            $query = $this->em->createQuery('SELECT MAX(c.orderinlist) as maxorderinlist FROM OlegOrderformBundle:StainList c');           
            $nextorder = $query->getSingleResult()['maxorderinlist']+10;          
            $stain->setOrderinlist($nextorder);
            
            $this->em->persist($stain);
            $this->em->flush($stain);

            return $stain;
        } else {
            //echo "return db entity=".$entity."<br>";
            return $entity;
        }

    }


}