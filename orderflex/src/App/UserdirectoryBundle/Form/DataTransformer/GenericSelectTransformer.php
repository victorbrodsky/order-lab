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

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Doctrine\Common\Persistence\ObjectManager;

class GenericSelectTransformer implements DataTransformerInterface
{
    /**
     * @var ObjectManager
     */
    private $em;
    private $user;
    protected $className;
    protected $bundleName;
    protected $params;

    /**
     * @param ObjectManager $om
     */
    public function __construct(EntityManagerInterface $em=null, $user=null, $className=null, $bundleName=null, $params=null)
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
    }

    /**
     * Transforms an object or name string to id.
     */
    public function transform($entity)
    {
        if( null === $entity || $entity == "" ) {
            return "";
        }

        //echo "data transformer entity=".$entity."<br>";
        //echo "data transformer entity id=".$entity->getId()."<br>";

        if( is_int($entity) ) {
            //echo "transform by name=".$entity." !!!<br>";
            $entity = $this->em->getRepository('App'.$this->bundleName.':'.$this->className)->findOneById($entity);
            //echo "findOneById entity=".$entity."<br>";
        }
        else {
            //echo "transform by name=".$entity." ????????????????<br>";
            //$entity = $this->em->getRepository('App'.$this->bundleName.':'.$this->className)->findOneByName($entity);
            $entity = $this->findEntityByString($entity);
        }

        if( null === $entity ) {
            return "";
        }

        //return $entity->getId();

        //echo "count=".count($entity)."<br>";

        return $entity->getId();
    }

    /**
     * Transforms a string (number) to an object.
     */
    public function reverseTransform($text)
    {
        //echo "data reverseTransform text=".$text."<br>";
        //exit();

        if (!$text) {
            return null;
        }

        if( is_numeric ( $text ) ) {    //number => most probably it is id
            //echo 'text is id <br>';
            $entity = $this->em->getRepository('App'.$this->bundleName.':'.$this->className)->findOneById($text);

            if( $entity ) {
                //return $entity->getBlockPrefix();
                return $entity;
            } else {
                return $this->findEntityByString($text);
            }
        } else {
            return $this->findEntityByString($text);
        }

        return $text;
    }


    public function findEntityByString($string) {
        $entity = $this->em->getRepository('App'.$this->bundleName.':'.$this->className)->findOneByName($string."");

        if( null === $entity ) {
            $entity = $this->em->getRepository('App'.$this->bundleName.':'.$this->className)->findOneByAbbreviation($string."");
        }

        return $entity;
    }
    public function findEntityById($id) {
        $entity = $this->em->getRepository('App'.$this->bundleName.':'.$this->className)->find($id);
        return $entity;
    }
}