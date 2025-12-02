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



use App\OrderformBundle\Entity\AccessionListHierarchy;
use App\OrderformBundle\Entity\MessageCategory;
use App\OrderformBundle\Entity\PatientListHierarchy;
use App\UserdirectoryBundle\Entity\BaseCompositeNode;
use App\UserdirectoryBundle\Entity\CommentTypeList;
use App\UserdirectoryBundle\Entity\FellowshipSubspecialty;
use App\UserdirectoryBundle\Entity\FormNode;
use App\UserdirectoryBundle\Entity\Institution;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

use App\UserdirectoryBundle\Entity\User;
use App\UserdirectoryBundle\Util\UserSecurityUtil;
use Symfony\Component\Form\Extension\Core\DataTransformer\DateTimeToStringTransformer;

class GenericTreeTransformer implements DataTransformerInterface
{
    protected $em;
    protected $user;
    protected $className;
    protected $bundleName;
    protected $fullClassName;
    protected $params;
    
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

        if( !$this->className ) {
            throw new \Exception('className is null');
        }

        $this->fullClassName = "App\\".$this->bundleName."\\Entity\\".$this->className;
    }

    public function getThisEm() {
        return $this->em;
    }


    /**
     * Transforms an object or name string to id.
     *
     * @param  mixed $entity
     * @return mixed
     */
    public function transform($entity): mixed
    {

        //echo "transform: entity=".$entity."<br>";

        if( null === $entity || $entity == "" ) {
            return "";
        }

        //echo "data transformer entity=".$entity."<br>";
        //echo "data transformer entity id=".$entity->getId()."<br>";

        if( is_int($entity) ) {
            //echo "transform by id=".$entity." !!!<br>";
            //$entity = $this->em->getRepository('App'.$this->bundleName.':'.$this->className)->findOneById($entity);
            $entity = $this->em->getRepository($this->fullClassName)->findOneById($entity);
            //echo "findOneById entity=".$entity."<br>";
        }
//        else {
//            //echo "transform by name=".$entity." ????????????????<br>";
//            $entity = $this->em->getRepository('App'.$this->bundleName.':'.$this->className)->findOneByName($entity);
//        }

        if( null === $entity ) {
            return "";
        }

        //return $entity->getId();

        //echo "count=".count($entity)."<br>";
        //echo "$entity=".$entity."<br>";

        return $entity->getId();
    }

    /**
     * Transforms a string (number) to an object.
     *
     * @param  mixed $text
     *
     * @return mixed
     *
     * @throws TransformationFailedException if object (stain) is not found.
     */
    public function reverseTransform( $text ): mixed
    {

        //echo "data reverse transformer text=".$text."<br>";
        //exit();

        if( !$text ) {
            //exit('text is null');
            //echo "data transformer text is null <br>";
            return null;
        }

        if( is_numeric ( $text ) ) {    //number => most probably it is id

            //echo 'text is id <br>';

            //$entity = $this->em->getRepository('App'.$this->bundleName.':'.$this->className)->findOneById($text);
            $entity = $this->em->getRepository($this->fullClassName)->findOneById($text);

            if( null === $entity ) {

                //exit('create new???');
                return $this->createNew($text); //create a new record in db

            } else {

                //echo "found:".$entity->getBlockPrefix()."<br>";
                //exit('use found object <br>');
                return $entity; //use found object

            }

        } else {    //text => most probably it is new name

            //exit('text is a new record name='.$text);
            //echo "text is a new record name=".$text."<br>";
            return $this->createNew($text); //create a new record in db

        }

    }

    public function createNew($name) {

        //echo $this->className.": enter create new name=".$name."<br>";
        //exit('create new !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!');

        if( !$name || $name == "" ) {
            //exit('child name is NULL');
            return null;
        }
        
        //trancate length to 255 char
        $origName = null;
        if( strlen((string)$name) > 255 ) {
            $origName = $name;
            $name = substr((string)$name,0,252).'...';         
        }

        //check if it is already exists in db
        //echo "className=".$this->className."<br>";
//        if( $this->bundleName == "UserdirectoryBundle" && $this->className == "User" ) {
//            //User does not have field "name"
//            $entity = null;
//        } else {
//            $entity = $this->em->getRepository('App'.$this->bundleName.':'.$this->className)->findOneByName($name."");
//        }
//        if( null === $entity ) {
//            $entity = $this->em->getRepository('App'.$this->bundleName.':'.$this->className)->findOneByAbbreviation($name."");
//        }
        $entity = $this->findEntityByString($name."");
        
        if( null === $entity ) {

            //echo "create new with name=".$name."<br>";
            //echo "user=".$this->user."<br>"; //user must be an object (exist in DB)
            if( !$this->user instanceof User ) {
                //user = system user
                $userSecUtil = new UserSecurityUtil($this->em);
                $this->user = $userSecUtil->findSystemUser();
            }

            $newEntity = $this->createNewEntity($name."",$this->className,$this->user);
            
            if( $origName ) {
                $newEntity->setDescription($origName);
            }

            if( method_exists($newEntity,'getParent') && !($newEntity instanceof BaseCompositeNode) && !($newEntity instanceof FellowshipSubspecialty) ) {
                //don't flush this entity because it has parent and parent can not be set here
                //Exception: FellowshipSubspecialty it has parent but it can be created a usual way
                //echo "this entity has parent => don't create <br>";
                //echo "name=".$newEntity->getBlockPrefix()."<br>";
                //$this->em->persist($newEntity);
                return $newEntity;
            }

            //echo $this->className.": persist and flush !!!!!!!!!!!!!!!! <br>";
            //exit('1');

            $this->em->persist($newEntity);
            //$this->em->flush($newEntity);
            $this->em->flush();

            return $newEntity;
        } else {
            //echo "entity is found in DB:".$entity."<br>";
            return $entity;
        }

    }


    public function createNewEntity($name,$className,$creator) {

        if( !$name || $name == "" ) {
            return null;
        }

        $fullClassName = "App\\".$this->bundleName."\\Entity\\".$className;
        $newEntity = new $fullClassName();

        //add default type
        $userSecUtil = new UserSecurityUtil($this->em);
        $newEntity = $userSecUtil->addDefaultType($newEntity,$this->params);

        $newEntity = $this->populateEntity($newEntity);

        $newEntity->setName($name."");
        $newEntity->setCreator($creator);

        return $newEntity;
    }

    public function populateEntity($entity) {
        //exit('1');

        $entity->setCreatedate(new \DateTime());
        $entity->setType('user-added');

        $fullClassName = new \ReflectionClass($entity);
        $className = $fullClassName->getShortName();

        //get max orderinlist
        //$query = $this->em->createQuery('SELECT MAX(c.orderinlist) as maxorderinlist FROM App'.$this->bundleName.':'.$className.' c');
        $classPathName = "App\\".$this->bundleName."\\Entity\\".$className;
        $query = $this->em->createQuery('SELECT MAX(c.orderinlist) as maxorderinlist FROM '.$classPathName.' c');
        $nextorder = $query->getSingleResult()['maxorderinlist']+10;
        $entity->setOrderinlist($nextorder);

        //set OrganizationalGroupType
        if( method_exists($entity,'setOrganizationalGroupType') ) {
            if( $entity instanceof Institution ) {
                $mapper = array(
                    'prefix' => "App",
                    'organizationalGroupType' => "OrganizationalGroupType",
                    'bundleName' => "UserdirectoryBundle",
                );
            }

            if( $entity instanceof CommentTypeList ) {
                $mapper = array(
                    'prefix' => "App",
                    'organizationalGroupType' => "CommentGroupType",
                    'bundleName' => "UserdirectoryBundle",
                );
            }

            if( $entity instanceof MessageCategory ) {
                $mapper = array(
                    'prefix' => "App",
                    'organizationalGroupType' => "MessageTypeClassifiers",
                    'bundleName' => "OrderformBundle",
                );
            }

            if( $entity instanceof PatientListHierarchy ) {
                $mapper = array(
                    'prefix' => "App",
                    'organizationalGroupType' => "PatientListHierarchyGroupType",
                    'bundleName' => "OrderformBundle",
                );
            }

            if( $entity instanceof AccessionListHierarchy ) {
                $mapper = array(
                    'prefix' => "App",
                    'organizationalGroupType' => "AccessionListHierarchyGroupType",
                    'bundleName' => "OrderformBundle",
                );
            }

            if( $entity instanceof FormNode ) {
                $mapper = array(
                    'prefix' => "App",
                    'organizationalGroupType' => NULL,
                    'bundleName' => "OrderformBundle",
                );
            }

            $mapper['entityNamespace'] = $mapper['prefix']."\\".$mapper['bundleName']."\\Entity";

            //$organizationalGroupTypes = $this->em->getRepository($mapper['prefix'].$mapper['bundleName'].':'.$mapper['organizationalGroupType'])->findBy(
            $organizationalGroupTypes = $this->em->getRepository($mapper['entityNamespace'].'\\'.$mapper['organizationalGroupType'])->findBy(
                array(
                    "level" => 0,
                    "type" => array('default','user-added')
                )
            );

            if( count($organizationalGroupTypes) > 0 ) {
                $organizationalGroupType = $organizationalGroupTypes[0];
            }

            if( count($organizationalGroupTypes) == 0 ) {
                $organizationalGroupType = null;
            }

            if( $organizationalGroupType ) {
                $entity->setOrganizationalGroupType($organizationalGroupType);
            }

        }

        //add type for institution: Medical and Educational
        if( method_exists($entity,'addType') && $className == 'Institution' ) {
            //$institutionMedicalType = $this->em->getRepository($mapper['prefix'].$mapper['bundleName'].':'.'InstitutionType')->findOneByName('Medical');
            $institutionMedicalType = $this->em->getRepository($mapper['entityNamespace'].'\\'.'InstitutionType')->findOneByName('Medical');
            $entity->addType($institutionMedicalType);
            //$institutionEducationalType = $this->em->getRepository($mapper['prefix'].$mapper['bundleName'].':'.'InstitutionType')->findOneByName('Educational');
            $institutionEducationalType = $this->em->getRepository($mapper['entityNamespace'].'\\'.'InstitutionType')->findOneByName('Educational');
            $entity->addType($institutionEducationalType);
        }

        return $entity;
    }


    public function findEntityByString($string) {

        if( $this->bundleName == "UserdirectoryBundle" && $this->className == "User" ) {
            //User does not have field "name"
            $entity = null;
        } else {
            //$entity = $this->em->getRepository('App'.$this->bundleName.':'.$this->className)->findOneByName($string."");
            $entity = $this->em->getRepository($this->fullClassName)->findOneByName($string."");
        }

        if( null === $entity ) {
            //$entity = $this->em->getRepository('App'.$this->bundleName.':'.$this->className)->findOneByAbbreviation($string."");
            $entity = $this->em->getRepository($this->fullClassName)->findOneByAbbreviation($string."");
        }

        return $entity;
    }
    public function findEntityById($id) {
        //$entity = $this->em->getRepository('App'.$this->bundleName.':'.$this->className)->find($id);
        $entity = $this->em->getRepository($this->fullClassName)->find($id);
        return $entity;
    }
}