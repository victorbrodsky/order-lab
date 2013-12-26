<?php

namespace Oleg\OrderformBundle\Repository;

use Doctrine\ORM\EntityRepository;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;
//use Doctrine\ORM\Mapping\ClassMetadata;

//use Oleg\OrderformBundle\Entity\PatientMrn;
//use Oleg\OrderformBundle\Entity\AccessionAccession;

class ArrayFieldAbstractRepository extends EntityRepository {

    private $log;

    const STATUS_RESERVED = "reserved";
    const STATUS_VALID = "valid";
    const STATUS_INVALID = "invalid";

    public function __construct($em, $class)
    {
        parent::__construct($em, $class);
        $this->log = new Logger('FieldAbstractRep');
        $this->log->pushHandler(new StreamHandler('./Scanorder.log', Logger::WARNING));

    }

    public function processEntity( $entity, $orderinfo ) {

        if( !$entity ) {
            return $entity;
        }

        $class = new \ReflectionClass($entity);
        $className = $class->getShortName();
        echo "<br>processEntity className=".$className.", keyFieldName=".$entity->obtainKeyFieldName()."<br>";
        //echo $entity;

        //check and remove duplication objects such as two Part 'A'. We don't need this if we have JS form check(?)
        //$entity = $em->getRepository('OlegOrderformBundle:'.$childName)->removeDuplicateEntities( $entity );

        $em = $this->_em;
        $entity = $em->getRepository('OlegOrderformBundle:'.$className)->processDuplicationKeyField($entity,$orderinfo);

        $keys = $entity->obtainAllKeyfield();

        //echo "count keys=".count($keys)."<br>";
        //echo "key=".$keys->first()."<br>";

        if( count($keys) == 0 ) {
            $entity->createKeyField();  //this should never execute in normal situation
        } elseif( count($keys) > 1 ) {
            //throw new \Exception( 'This Object ' . $className . ' must have only one key field. Number of key field=' . count($keys) );
            //echo( 'This Object ' . $className . ' should have only one key field. Number of key field=' . count($keys) );

        }

        $key = $entity->obtainValidKeyField();
        //echo "valid key=".$key.", status=".$key->getStatus()."<br>";

        if( $key == ""  ) {
            //echo "Case 1: Empty form object (all fields are empty): generate next available key and assign to this object <br>";

            $nextKey = $this->getNextNonProvided($entity,null,$orderinfo);  //"NO".strtoupper($fieldName)."PROVIDED", $className, $fieldName);

            //we should have only one key field !!!
            $key->setField($nextKey);
            $key->setStatus(self::STATUS_VALID);
            $key->setProvider($orderinfo->getProvider()->first());

        } else {

            if( $orderinfo->getCicle() == 'amend' ) {
                $found = null;
            } else {
                //this is a main function to check uniqueness
                $found = $this->findUniqueByKey($entity);
            }


            if( $found ) {
                //echo "Case 2: object exists in DB (eneterd key is for existing object): CopyChildren, CopyFields <br>";
                //CopyChildren
                foreach( $entity->getChildren() as $child ) {
                    //echo "adding: ".$child."<br>";
                    $found->addChildren( $child );
                }
                return $this->setResult($found, $orderinfo, $entity);

                //$entity->setId( $found->getId() );
                //return $this->setResult($entity, $orderinfo);

            } else {
                echo "Case 3: object does not exist in DB (new key is eneterd) <br>";
            }

        }

        return $this->setResult($entity, $orderinfo);

    }

    public function setResult( $entity, $orderinfo, $original=null ) {

        $class = new \ReflectionClass($entity);
        $className = $class->getShortName();

        $em = $this->_em;

        //echo "Set Result for entity:".$entity;

        $children = $entity->getChildren();

        //set status 'valid'
        $entity->setStatus(self::STATUS_VALID);

        //CopyFields
        $entity = $this->processFieldArrays($entity,$orderinfo,$original);

        //echo "After process fields:".$entity;

        //echo "count of children=".count($children)."<br>";

        foreach( $children as $child ) {

            $childClass = new \ReflectionClass($child);
            $childClassName = $childClass->getShortName();
            //echo $className.": childClassName=".$childClassName."<br>";

            $entity->removeChildren($child);
            $child = $em->getRepository('OlegOrderformBundle:'.$childClassName)->processEntity( $child, $orderinfo );

            //add children and orderinfo
            $em->getRepository('OlegOrderformBundle:'.$className)->attachToParentAndOrderinfo( $entity, $child, $orderinfo );

        }

        if( !$entity->getId() || $entity->getId() == "" ) {
            //echo "persist ".$className."<br>";
            $em->persist($entity);
        } else {
            //echo "merge ".$className."<br>";
            //$em->merge($entity);
        }

        //set provider
        $entity->setProvider($orderinfo->getProvider()->first());

        //echo "Finish Set Result for entity:".$entity;

        return $entity;
    }

    public function attachToParentAndOrderinfo( $entity, $child, $orderinfo ) {
        echo "start adding to orderinfo <br>";
        if( $child ) {
            $entity->addChildren($child);

            //add orderinfo if oid is not set yet => new orderinfo. If oid is set then it is un-canceled order and it already has links to its children objects(patients, parts, blocks, slides ...)
            if( $orderinfo->getOid() == null ) {
                //link entity with orderinfo
                $childClass = new \ReflectionClass($child);
                $childClassName = $childClass->getShortName();
                $addClassMethod = "add".$childClassName;    //"addPatient"
                $orderinfo->$addClassMethod($child);
                echo "add orderinfo for ".$childClassName."<br>";
            }

        }
    }

    public function processDuplicationKeyField($entity,$orderinfo) {
        return $entity; //override it for accession only
    }

    //process single array of fields (i.e. ClinicalHistory Array of Fields)
    public function processFieldArrays( $entity, $orderinfo=null, $original=null, $status=null ) {

        if( $orderinfo ) {
            $provider = $orderinfo->getProvider()->first(); //assume orderinfo has only one provider.
            //echo "provider=".$provider."<br>";
        }

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

            if( strpos($methodShortName,'get') !== false ) {    //filter in only "get" methods

                //echo "methodShortName=".$methodShortName."<br>";

                $this->log->addInfo( " method=".$methodShortName."=>" );

                //get array of fields (i.e. getMrn, getClinicalHistory ... )
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

                        $parentname = get_parent_class($field);
                        $basename = get_parent_class($parentname);

                        //echo ( "0 field=".$field.", basename=".$basename."<br>" );

                        if( is_object($field) && $basename == 'Oleg\OrderformBundle\Entity\ArrayFieldAbstract' ) {


                                $class = new \ReflectionClass($field);
                                $parent = $class->getParentClass();

                                //echo "Method:".$methodShortName.", field=".$field.", fieldId=".$field->getId().", status=".$field->getStatus()."<br>";

//                                if( $parent && $field->getField() && $field->getField() != "" ) {     //filter in all objects with parent class. assume it is "PatientArrayFieldAbstract"
                            if( $parent ) {

                                $this->log->addInfo( "###parent exists=".$parent->getName().", method=".$methodShortName.", id=".$field->getId()."<br>" );
                                $this->log->addInfo( "field id=".$field->getId()."<br>" );

                                //Change status only and continue to the next field
                                if( $status ) {
                                    //echo "2 change status to (".$status.") <br>";
                                    $field->setStatus($status);
                                    //set ID to null if status is valid (un-cancel procedure)
                                    //if( $status == 'valid' ) {
                                        //$field->setId(null);
                                        //$em = $this->_em;
                                        //$em->detach($field);
                                        //$em->persist($field);
                                    //}
                                    continue;
                                }

                                //############# set provider to the fields from submitted form
                                if( !$field->getProvider() || $field->getProvider() == "" ) {
                                    //echo( "add provider <br>" );
                                    $field->setProvider($provider); //set provider
                                    //echo( "after added provider=".$field->getProvider()." <br>" );
                                }

                                //############# set validity to the fields from submitted form
                                if( !$validitySet ) {
                                    //echo( "methodShortName=".$methodShortName."<br>" );
                                    if( !$this->validFieldIsSet($fields) ) {  //set valid if none of the filed has valid status already
                                        //echo "Set status to ".self::STATUS_VALID." to field".$field."<br>";
                                        $field->setStatus(self::STATUS_VALID);
                                    } else {
                                        //echo "Do not set status to ".self::STATUS_VALID." to field=".$field."<br>";
                                    }
                                    $validitySet = true;    //indicate that status is already has been set in this field array
                                }
                                //echo "field status =".$field->getStatus()."<br>";

                                //############# copy processed field from submitted object (original) to found entity in DB
                                if( $original ) {
                                        //echo "entity:".$entity;
                                        //echo "original:".$original;
                                        //echo "field=".$field."<br>";
                                    $this->log->addInfo( "original yes: field=".$field."<br>" );
                                    $methodBaseName = str_replace("get", "", $methodShortName);
                                    $entity = $this->copyField( $entity, $field, $className, $methodBaseName, $fields );
                                }
                            }

                        } //if object && is_subclass_of

                    } //foreach

                } //if object
                //echo "<br>";
            }
        }

        return $entity;
    }

    //replace field entity if not existed from source object to destination object
    //field id is null if check button is not pressed, in this case all fields are gray
    //if entity is found in DB, then all fields have ID, if not then this function is not executed, because processFieldArrays has original=null
    public function copyField( $entity, $field, $className, $methodName, $fields ) {
        $em = $this->_em;
        //echo "copyField!!!: class=".$className.$methodName.", id=".$field->getId().", field=".$field."<br>";
        //echo "this fields count=".count($fields)."<br>";

        //if similar field is already set and provided field is empty => don't add provided field
        if( !$field || trim($field) == "" ) {
            if( $this->validFieldIsSet( $fields ) ) {
                //echo $methodName.": field is empty and non empty valid field exists => don't add provided field => return<br>";
                return $entity;
            }
        }

        //add only if the field array does not already contain this valid field (by field name)
        foreach( $fields as $thisField ) {
            if( $thisField."" == $field."" && $thisField->getStatus() == self::STATUS_VALID ) {
                //echo "found valid field by field name => don't add field <br>";
                return $entity;
            }
        }

        $found = $em->getRepository('OlegOrderformBundle:'.$className.$methodName)->findOneById($field->getId());

        if( !$found ) {
            //echo( "### ".$methodName." not found !!!!!! => add <br>" );
            $addMethodName = "add".$methodName; //i.e. addMrn
            $entity->$addMethodName( $field );
        } else {
            //
        }

        return $entity;
    }

    public function validFieldIsSet( $fields ) {

        //exception for Part Differential Diagnosis field. Always added as valid
        $class = new \ReflectionClass($fields->first());
        $className = $class->getShortName();
        //echo "className=".$className."<br>";
        if( $className == 'PartDiffDisident' ) {
            return false;
        }

        foreach( $fields as $thisField ) {
            if( $thisField->getStatus() == self::STATUS_VALID ) {
                //echo "found valid field by field name => don't add field <br>";
                return true;
            }
        }
        return false;
    }

    public function findOneByIdJoinedToField( $fieldStr, $className, $fieldName, $validity=null, $single=true, $extra=null )
    {
        //echo "fieldStr=(".$fieldStr.")<br> ";
        //echo " validity=".$validity." ";

        $onlyValid = "";
        if( $validity ) {
            //echo " check validity ";
            if( $validity != "" && $validity !=  1 ) {
                //echo "validity == string1 ";
            } else if( $validity ==  1 ) {
                //echo "validity == true ";
                $validity = self::STATUS_VALID;
            } else {
                //echo "else-validity == string ";
            }
            $onlyValid = " AND c.status='".$validity."' AND cfield.status='".self::STATUS_VALID."'";
        }

        $extraStr = "";
        if( $extra && count($extra) > 0 ) {
            if( $className == "Patient" ) {
                $extraStr = " AND cfield.mrntype = ".$extra["mrntype"];
            }
        }

        //echo "extraStr=".$extraStr." ,onlyValid=".$onlyValid." ";

        $query = $this->getEntityManager()
            ->createQuery('
        SELECT c FROM OlegOrderformBundle:'.$className.' c
        JOIN c.'.$fieldName.' cfield
        WHERE cfield.field = :field'.$onlyValid.$extraStr
            )->setParameter('field', $fieldStr."");

        try {
            if( $single ) {
                //echo "find return single<br>";
                return $query->getSingleResult();
            } else {
                //echo "find multi return<br>";
                return $query->getResult();
            }

        } catch (\Doctrine\ORM\NoResultException $e) {
            //echo "find return null<br>";
            return null;
        }
    }

    public function deleteIfReserved( $fieldStr, $className, $fieldName, $extra = null ) {

        //echo "fieldStr=".$fieldStr." ";
        $entities = $this->findOneByIdJoinedToField($fieldStr, $className, $fieldName, self::STATUS_RESERVED, false, $extra );
        //echo "found entities = ". count($entities). " ";

        if( !$entities ) {
            return 0;
        }

        $removed = 0;
        foreach( $entities as $entity ) {
            $em = $this->_em;
            $em->remove($entity);
            $em->flush();
            $removed++;
        }
        return $removed;
    }

    //$className: Patient
    //$fieldName: mrn
    public function createElement( $status = null, $provider = null, $className, $fieldName, $parent = null, $fieldValue = null, $extra = null, $flush=true ) {
        if( !$status ) {
            $status = self::STATUS_RESERVED;
        }
        $em = $this->_em;

        $entityClass = "Oleg\\OrderformBundle\\Entity\\".$className;
        $entity = new $entityClass();

        if( !$fieldValue ) {
            $fieldValue = $this->getNextNonProvided($entity,$extra,null);
        }
        //echo "fieldValue=".$fieldValue;

        //before create: check if entity with key does not exists in DB
//        $entityFound = $this->findOneByIdJoinedToField($fieldValue, $className, $fieldName, self::STATUS_RESERVED, true, $extra );
//        if( $entityFound ) {
//            return $entityFound;
//        }

        $fieldEntityName = ucfirst($className).ucfirst($fieldName);
        $fieldClass = "Oleg\\OrderformBundle\\Entity\\".$fieldEntityName;
        $field = new $fieldClass();

        $field->setField($fieldValue);

        if( $provider ) {
            $field->setProvider($provider);
        }

        $field->setStatus(self::STATUS_VALID);

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
        if( $flush ) {
            $em->flush();
        }
        //echo "Created=".$fieldEntityName."<br>";

        return $entity;
    }

    //check the last NOMRNPROVIDED MRN in DB and construct next available MRN
    //$name: NOMRNPROVIDED
    //$className: i.e. Patient
    //$fieldName: i.e. mrn
    public function getNextNonProvided( $entity, $extra=null, $orderinfo=null ) { //$name, $className, $fieldName ) {

        $class = new \ReflectionClass($entity);
        $className = $class->getShortName();
        $fieldName = $entity->obtainKeyFieldName();
        $name = "NO".strtoupper($fieldName)."PROVIDED";

        //get extra key by $extra optional parameter or get it from entity
        $extraStr = "";
        if( $extra && count($extra) > 0 ) {
            if( $className == "Patient" ) {
                $extraStr = " cfield.mrntype = '".$extra["mrntype"]."' AND ";
            }
        } else {
            $validKeyField = $entity->obtainValidKeyfield();
            //get extra field key such as Patient's mrntype
            if( $validKeyField && method_exists($validKeyField,'obtainExtraKey') ) {
                $extra = $validKeyField->obtainExtraKey();
                $mrntype = $extra["mrntype"];
                $extraStr = " cfield.mrntype = ".$mrntype." AND ";
            }
        }

        $query = $this->getEntityManager()
        ->createQuery('
        SELECT MAX(cfield.field) as max'.$fieldName.' FROM OlegOrderformBundle:'.$className.' c
        JOIN c.'.$fieldName.' cfield
        WHERE '.$extraStr.'cfield.field LIKE :field'
        )->setParameter('field', '%'.$name.'%');
        
        $lastField = $query->getSingleResult();
        $index = 'max'.$fieldName;
        $lastFieldStr = $lastField[$index];
        //echo "lastFieldStr=".$lastFieldStr."<br>";
        //$fieldIndexArr = explode("-",$lastFieldStr);
        //echo "count=".count($fieldIndexArr)."<br>";

        $maxKey = $this->getNextByMax($lastFieldStr, $name);

        //check if the valid bigger key was already assigned to the element of the same class attached to this order
        if( $orderinfo ) {
            $getSameEntity = "get".$className;
            foreach( $orderinfo->$getSameEntity() as $same ) {
                if( $same->getStatus() == self::STATUS_VALID ) {
                    $key = $same->obtainValidKeyfield()->getField()."";
                    $newBiggerKey = $this->getBiggerKey($maxKey,$key,$name);
                    if( $newBiggerKey != -1 ) {
                        $maxKey = $newBiggerKey;
                    }
                }
            }
        }
        //echo "maxKey=".$maxKey."<br>";
        //return $this->getNextByMax($lastFieldStr, $name);
        return $maxKey;
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

    //compare two keys:
    //$keyDb: generated from DB: NOMRNPROVIDED-0000000001
    //$key  : key founded in the entity
    public function getBiggerKey( $keyDb, $key, $name ) {

        //echo "get bigger key:".$keyDb." == ".$key.", name=".$name."<br>";

        if( strpos($keyDb,$name) === false || strpos($key,$name) === false ) {
            //echo "keys are not generated<br>";
            return -1;
        }

        $fieldIndexArrDb = explode("-",$keyDb);
        $intKeyDb = intval($fieldIndexArrDb[1]);

        $fieldIndexArr = explode("-",$key);
        $intKey = intval($fieldIndexArr[1]);

        if( $intKeyDb == $intKey ) {
            //echo "keys equal<br>";
            return $this->getNextByMax( $keyDb, $name );
        } else
        if( $intKeyDb > $intKey ) {
            //echo "DBkey > key <br>";
            return $keyDb;
        } else
        if( $intKeyDb < $intKey ) {
            //echo "DBkey < key <br>";
            return $this->getNextByMax( $key, $name ); //increment key by one
        } else {
            throw new \Exception('Can not compare keys:'.$intKeyDb." and ".$intKey);
        }

    }

    //check if the entity with its field is existed in DB
    //$className: class name i.e. "Patient"
    //$fieldName: key field name i.e. "mrn"
    //return: null - not existed, entity object if existed
    public function findUniqueByKey( $entity ) {

        //echo "find Unique By Key: Abstract: ".$entity;

        if( !$entity ) {
            //echo "entity is null <br>";
            return null;
        }

        $class = new \ReflectionClass($entity);
        $className = $class->getShortName();
        $fieldName = $entity->obtainKeyFieldName();

        $validKeyField = $entity->obtainValidKeyfield();

        //get extra field key such as Patient's mrntype
        if( method_exists($validKeyField,'obtainExtraKey') ) {
            $extra = $validKeyField->obtainExtraKey();
        } else {
            $extra = null;
        }

        if( $entity->obtainValidKeyfield() ) {
            $em = $this->_em;
            $newEntity = $em->getRepository('OlegOrderformBundle:'.$className)->findOneByIdJoinedToField($validKeyField->getField()."",$className,$fieldName,true,true, $extra);
        } else {
            //echo "This entity does not have a valid key field<br>";
            $newEntity = null;
        }

        return $newEntity;
    }

    public function printTree( $entity ) {

        echo "print Tree: " . $entity;

        foreach( $entity->getChildren() as $child ) {
            if( count( $child->getChildren() ) == 0 ) {
                echo "print Tree node: " . $child;
                echo "----------<br>";
            } else {
                $this->printTree($child);
            }
        }

    }


}
