<?php

namespace Oleg\OrderformBundle\Repository;

use Doctrine\ORM\EntityRepository;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Doctrine\ORM\Mapping\ClassMetadata;

use Oleg\OrderformBundle\Entity\PatientMrn;
use Oleg\OrderformBundle\Entity\AccessionAccession;

class ArrayFieldAbstractRepository extends EntityRepository {

    private $log;

    const STATUS_RESERVED = "reserved";
    const STATUS_VALID = "valid";

    public function __construct($em, $class)
    {
        parent::__construct($em, $class);
        $this->log = new Logger('FieldAbstractRep');
        $this->log->pushHandler(new StreamHandler('./Scanorder.log', Logger::WARNING));

    }

    public function processEntity( $entity, $orderinfo ) {

        $class = new \ReflectionClass($entity);
        $className = $class->getShortName();
        $fieldName = $entity->obtainKeyFieldName();

        echo "<br>processEntity className=".$className.", fieldName=".$fieldName."<br>";

        //check and remove duplication objects such as two Part 'A'. We don't need this if we have JS form check(?)
        //$em = $this->_em;
        //$entity = $em->getRepository('OlegOrderformBundle:'.$childName)->removeDuplicateEntities( $entity );

        $keys = $entity->obtainAllKeyfield();

        echo "count keys=".count($keys)."<br>";
        echo "key=".$keys->first()."<br>";

        if( count($keys) == 0 ) {
            $keys = $entity->createKeyField();
        } elseif( count($keys) > 1 ) {
            throw new \Exception( 'This Object ' . $className . ' must have only one key field. Number of key field=' . count($keys) );
        }

        if( $keys->first() == ""  ) {
            echo "Case 1: Empty form object (all fields are empty): generate next available key and assign to this object <br>";

            $nextKey = $this->getNextNonProvided($entity);  //"NO".strtoupper($fieldName)."PROVIDED", $className, $fieldName);

            //we should have only one key field !!!
            $keys->first()->setField($nextKey);
            $keys->first()->setValidity(1);
            $keys->first()->setProvider($orderinfo->getProvider()->first());

            return $this->setResult($entity, $orderinfo);

        } else {

            //this is a main function to check uniqueness
            $found = $this->findUniqueByKey($entity);

            if( $found ) {
                echo "Case 2: object exists in DB (eneterd key is for existing object): CopyChildren, CopyFields <br>";
                //CopyChildren
                foreach( $entity->getChildren() as $child ) {
                    //echo "adding: ".$child."<br>";
                    $found->addChildren( $child );
                }


                return $this->setResult($found, $orderinfo, $entity);

            } else {
                echo "Case 3: object does not exist in DB (new key is eneterd) <br>";

                return $this->setResult($entity, $orderinfo);
            }

        }

    }

    public function setResult( $entity, $orderinfo, $original=null ) {

        $em = $this->_em;
        $em->persist($entity);
        $children = $entity->getChildren();

        //set status 'valid'
        $entity->setStatus(self::STATUS_VALID);

        //CopyFields
        $entity = $this->processFieldArrays($entity,$orderinfo,$original);

        echo "count of children=".count($children)."<br>";

        foreach( $children as $child ) {
            $childClass = new \ReflectionClass($child);
            $childClassName = $childClass->getShortName();
            echo "childClassName=".$childClassName."<br>";
            $child = $em->getRepository('OlegOrderformBundle:'.$childClassName)->processEntity( $child, $orderinfo );
            $addClassMethod = "add".$childClassName;
            $orderinfo->$addClassMethod($child);
        }
        echo "finish: entity status=".$entity->getStatus()."<br>";

//        $em->merge($entity);

        return $entity;
    }



    public function createKeyField( $entity, $className, $fieldName ) {
        $fieldValue = $this->getNextNonProvided($entity);
        //echo "fieldValue=".$fieldValue."<br>";
        $fieldEntityName = ucfirst($className).ucfirst($fieldName);
        $fieldClass = "Oleg\\OrderformBundle\\Entity\\".$fieldEntityName;
        $clearFieldMethod = "clear".ucfirst($fieldName);
        $addFieldMethod = "add".ucfirst($fieldName);
        $field = new $fieldClass(1);
        $field->setField($fieldValue);
        $entity->$clearFieldMethod();
        $entity->$addFieldMethod($field);
        return $entity;
    }

    //process single array of fields (i.e. ClinicalHistory Array of Fields)
    public function processFieldArrays( $entity, $orderinfo, $original=null ) {

        //$entity->setStatus(self::STATUS_VALID);

        $provider = $orderinfo->getProvider()->first(); //assume orderinfo has only one provider.
        //echo "provider=".$provider."<br>";

        //$class_methods = get_class_methods($dest);
        $class = new \ReflectionClass($entity);
        $className = $class->getShortName();
        //echo "className=".$className."<br>";
        //$parent = $class->getParentClass();

        //$log->addInfo('Foo');
        //$log->addError('Bar');

        $class_methods = $class->getMethods(\ReflectionMethod::IS_PUBLIC);
        foreach( $class_methods as $method_name ) {

            $methodShortName = $method_name->getShortName();    //getMrn

            if( strpos($methodShortName,'get') !== false ) {    //&& $methodShortName != 'getId' ) { //filter in only "get" methods

                $this->log->addInfo( " method=".$methodShortName."=>" );
                if( $original ) {
                    $fields = $original->$methodShortName();
                } else {
                    $fields = $entity->$methodShortName();
                }
                //echo $methodShortName." count=".count($fields)."<br>";

                if( is_object($fields) || is_array($fields) ) {

                    //echo ( $methodShortName." is object !!! <br>" );

                    $validitySet = false;   //indicate that validity has not been set in this field array

                    foreach( $fields as $field ) {  //original fields from submitted form

                        //echo ( "0 field=".$field."<br>" );

                        if( is_object($field) ) {

                            $fieldReflection = new \ReflectionClass($field);
                            if( $fieldReflection->hasMethod('getProvider') ) {

                                $class = new \ReflectionClass($field);
                                $parent = $class->getParentClass();

                                //echo ( "1 field=".$field."<br>" );

                                if( $parent && $field->getField() && $field->getField() != "" ) {     //filter in all objects with parent class. assume it is "PatientArrayFieldAbstract"

                                    $this->log->addInfo( "###parent exists=".$parent->getName().", method=".$methodShortName.", id=".$field->getId()."<br>" );
                                    $this->log->addInfo( "field id=".$field->getId()."<br>" );

                                    //############# set provider to the fields from submitted form
                                    if( !$field->getProvider() || $field->getProvider() == "" ) {
                                        //echo( "add provider <br>" );
                                        $field->setProvider($provider); //set provider
                                        //echo( "after added provider=".$field->getProvider()." <br>" );
                                    }

                                    //############# set validity to the fields from submitted form
                                    if( !$validitySet ) {
                                        $this->log->addInfo( "methodShortName=".$methodShortName."<br>" );
                                        if( !$entity->getId() || !$this->hasValidity($entity->$methodShortName()) ) { //set validity for the first added field
                                            //echo "Set validity to 1 <br>";
                                            $field->setValidity(1);
                                        }
                                        $validitySet = true;    //indicate that validity is already has been set in this field array
                                    }

                                    //############# copy processed field from submitted object (original) to found entity in DB
                                    if( $original ) {
                                        $this->log->addInfo( "original yes: field=".$field."<br>" );
                                        $methodBaseName = str_replace("get", "", $methodShortName);
                                        $entity = $this->copyField( $entity, $field, $className, $methodBaseName );

                                    }
                                }

                                //echo " end mrn provider=".$entity->getMrn()->first()->getProvider().", count=".count($entity->getMrn());
                                //echo "end name provider=".$entity->getName()->first()->getProvider().", count=".count($entity->getname())." <br>";
                                //echo " end provider=".$field->getProvider()." <br><br>";

                            }

                        } //if object

                    } //foreach

                } //if object
                //echo "<br>";
            }
        }

        return $entity;
    }

    //replace field entity if not existed from source object to destination object
    public function copyField( $entity, $field, $className, $methodName ) {
        $em = $this->_em;
        echo "class=".$className.$methodName.", id=".$field->getId().", field=".$field."<br>";
        $found = $em->getRepository('OlegOrderformBundle:'.$className.$methodName)->findOneById($field->getId());

        if( !$found ) {
            echo( "### ".$methodName." not found !!!!!! => add <br>" );
            $methodName = "add".$methodName;
            $entity->$methodName( $field );
        } else {
            echo( "### ".$methodName." entity is found in DB, validity=".$field->getValidity()."<br>" );
//            $found->setProvider($field->getProvider());
//            if( $field->getValidity() && $field->getValidity() != 0 ) {
//                $found->setValidity($field->getValidity());
//            }
            //echo "validity=".$found->getValidity()."<br>";
        }

        return $entity;
    }

    public function hasValidity( $fields ) {
        foreach( $fields as $field ) {
            //echo "Validity=".$field->getValidity().", field=".$field->getField()."<br>";
            if( $field->getValidity() == 1 ) {
                return true;
            }
        }
        return false;
    }

    public function findOneByIdJoinedToField( $fieldStr, $className, $fieldName, $validity=null, $single=true, $extra=null )
    {
        //echo "fieldStr=(".$fieldStr.")<br>";

        $onlyValid = "";
        if( $validity ) {
            //echo " check validity ";
            $onlyValid = " AND cfield.validity=1";
        }

        $extraStr = "";
        if( $extra ) {
            if( $className == "Patient" ) {
                $extraStr = " AND cfield.mrntype = ".$extra;
            }
        }

        $query = $this->getEntityManager()
            ->createQuery('
        SELECT c, cfield FROM OlegOrderformBundle:'.$className.' c
        JOIN c.'.$fieldName.' cfield
        WHERE cfield.field = :field'.$onlyValid.$extraStr
            )->setParameter('field', $fieldStr."");

        try {
            if( $single ) {
                //echo "find return single<br>";
                return $query->getSingleResult();
            } else {
                //echo "find return<br>";
                return $query->getResult();
            }

        } catch (\Doctrine\ORM\NoResultException $e) {
            //echo "find return null<br>";
            return null;
        }
    }

    public function deleteIfReserved( $fieldStr, $className, $fieldName, $extra = null ) {
        //echo "fieldStr=".$fieldStr." ";
        $entities = $this->findOneByIdJoinedToField($fieldStr, $className, $fieldName, null, false, $extra);
        if( $entities ) {
            foreach( $entities as $entity ) {
                if( $entity->getStatus() == self::STATUS_RESERVED ) {
                    //echo "id=".$entity->getId()." ";
                    $em = $this->_em;

                    //delete created parents
                    if( $className == "Part" ) {
                        $accession = $entity->getAccession();
                        if( $accession->getStatus() == self::STATUS_RESERVED ) {                          
                            $em->remove($accession);
                        }
                    }
                    if( $className == "Block" ) {
                        $part = $entity->getPart();
                        $accession = $part->getAccession();
                        if( $accession->getStatus() == self::STATUS_RESERVED ) {                          
                            $em->remove($accession);
                        }                      
                        if( $part->getStatus() == self::STATUS_RESERVED ) {                          
                            $em->remove($part);
                        }
                    }

                    $em->remove($entity);
                    $em->flush();
                    return true;
                }
            }
        }
        return false;
    }

    //$className: Patient
    //$fieldName: mrn
    public function createElement( $status = null, $provider = null, $className, $fieldName, $parent = null, $fieldValue = null, $extra = null ) {
        if( !$status ) {
            $status = self::STATUS_RESERVED;
        }
        $em = $this->_em;

        $entityClass = "Oleg\\OrderformBundle\\Entity\\".$className;
        $entity = new $entityClass();

        if( !$fieldValue ) {
            $fieldValue = $this->getNextNonProvided($entity,$extra);
        }
        //echo "fieldValue=".$fieldValue;

        $fieldEntityName = ucfirst($className).ucfirst($fieldName);
        $fieldClass = "Oleg\\OrderformBundle\\Entity\\".$fieldEntityName;
        $field = new $fieldClass();

        $field->setField($fieldValue);

        if( $provider ) {
            $field->setProvider($provider);
        }

        $field->setValidity(1);

        //if( $className == "Patient" ) {
        if( $field && method_exists($field,'setExtra') ) {
            //find mrnType with provided extra (mrntype id) from DB
            $extraEntity = $this->getExtraEntityById($extra);
            $field->setExtra($extraEntity);
        }

        $keyAddMethod = "add".ucfirst($fieldName);
        $entity->$keyAddMethod($field);

        $entity->setStatus($status);

        $em->persist($entity);

        if( $parent ) {
            //echo "set Parent = ".$fieldName."<br>";
            $em->persist($parent);
            $entity->setParent($parent);
        } else {
            //echo "Parent is not set<br>";
        }

        //exit();
        $em->flush();
        //echo "Created=".$fieldEntityName."<br>";

        return $entity;
    }

    //check the last NOMRNPROVIDED MRN in DB and construct next available MRN
    //$name: NOMRNPROVIDED
    //$className: i.e. Patient
    //$fieldName: i.e. mrn
    public function getNextNonProvided( $entity, $extra=null ) { //$name, $className, $fieldName ) {

        $class = new \ReflectionClass($entity);
        $className = $class->getShortName();
        $fieldName = $entity->obtainKeyFieldName();
        $name = "NO".strtoupper($fieldName)."PROVIDED";

        //get extra key by $extra optional parameter or get it from entity
        $extraStr = "";
        if( $extra ) {
            if( $className == "Patient" ) {
                $extraStr = " AND cfield.mrntype = ".$extra;
            }
        } else {
            $validKeyField = $entity->getValidKeyfield();
            //get extra field key such as Patient's mrntype
            if( $validKeyField && method_exists($validKeyField,'obtainExtraKey') ) {
                $extraStr = $validKeyField->obtainExtraKey();
            }
        }

        $query = $this->getEntityManager()
        ->createQuery('
        SELECT MAX(cfield.field) as max'.$fieldName.' FROM OlegOrderformBundle:'.$className.' c
        JOIN c.'.$fieldName.' cfield
        WHERE cfield.field LIKE :field'.$extraStr
        )->setParameter('field', '%'.$name.'%');
        
        $lastField = $query->getSingleResult();
        $index = 'max'.$fieldName;
        $lastFieldStr = $lastField[$index];
        //echo "lastFieldStr=".$lastFieldStr."<br>";
        $fieldIndexArr = explode("-",$lastFieldStr);
        //echo "count=".count($fieldIndexArr)."<br>";
        
        return $this->getNextByMax($lastFieldStr, $name);
    }
    
    public function getNextByMax( $lastFieldStr, $name ) {
        $fieldIndexArr = explode("-",$lastFieldStr);
        //echo "count=".count($fieldIndexArr)."<br>";
        if( count($fieldIndexArr) > 1 ) {
            $fieldIndex = $fieldIndexArr[1];
        } else {
            $fieldIndex = 0;
        }
        $fieldIndex = ltrim($fieldIndex,'0') + 1;
        $paddedfield = str_pad($fieldIndex,10,'0',STR_PAD_LEFT);
        //echo "paddedfield=".$paddedfield."<br>";
        //exit();
        return $name.'-'.$paddedfield;
    }

    //check if the entity with its field is existed in DB
    //$className: class name i.e. "Patient"
    //$fieldName: key field name i.e. "mrn"
    //return: null - not existed, entity object if existed
    public function findUniqueByKey( $entity ) {

        echo "find Unique By Key: Abstract: ".$entity;

        if( !$entity ) {
            //echo "entity is null <br>";
            return null;
        }

        $class = new \ReflectionClass($entity);
        $className = $class->getShortName();
        $fieldName = $entity->obtainKeyFieldName();

        $validKeyField = $entity->getValidKeyfield();

        //get extra field key such as Patient's mrntype
        if( method_exists($validKeyField,'obtainExtraKey') ) {
            $extra = $validKeyField->obtainExtraKey();
        } else {
            $extra = null;
        }

        if( $entity->getValidKeyfield() ) {
            $em = $this->_em;
            $newEntity = $em->getRepository('OlegOrderformBundle:'.$className)->findOneByIdJoinedToField($validKeyField->getField()."",$className,$fieldName,true, $extra);
        } else {
            echo "This entity does not have a valid key field<br>";
            $newEntity = null;
        }

        return $newEntity;
    }

//    //check entity by ID (need for postgresql; for mssql can check by if($entity->getId()) )
//    public function notExists($entity, $className) {
//
//        return true;
//
//        $id = $entity->getId();
//        if( !$id ) {
//            echo "notExists: ".$className.": no id =>".$entity." <br>";
//            return true;
//        }
//        echo "notExists: ".$className.": id=".$id." =>".$entity."<br>";
//        $em = $this->_em;
//        $found = $em->getRepository('OlegOrderformBundle:'.$className)->findOneById($id);
//        if( null === $found ) {
//            return true;
//        } else {
//            return false;
//        }
//    }

//    //get only valid field
//    public function getValidField( $fields ) {
//        foreach( $fields as $field ) {
//            //echo "get valid field=".$field.", validity=".$field->getValidity()."<br>";
//            if( $field->getValidity() && $field->getValidity() == 1 ) {
//                return $field;
//            }
//        }
//        return null;
//    }

}
