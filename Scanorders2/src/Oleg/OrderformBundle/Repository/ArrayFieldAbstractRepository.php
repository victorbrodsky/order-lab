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

    //make sure the uniqueness entity. Make new or return id of existing.
    //$childName: i.e. "Procedure" for Patient
    public function processEntity( $entity, $orderinfo = null, $className, $fieldName, $childName, $parent = null ) {

        //check and remove duplication objects such as two Part 'A'. We don't need this if we have JS form check
        //$em = $this->_em;
        //$entity = $em->getRepository('OlegOrderformBundle:'.$childName)->removeDuplicateEntities( $entity );

        $found = $this->isExisted($entity,$className,$fieldName);

        $getChildMethod = "get".ucfirst($childName);
        $addChildMethod = "add".ucfirst($childName);
        $getFieldMethod = "get".ucfirst($fieldName);
        $clearFieldMethod = "clear".ucfirst($fieldName);

        if( $found ) {
            //case 1 - existed but empty with STATUS_RESERVED; User press check with empty MRN field => new MRN was generated
            //Case 2 - existed and STATUS_VALID; User entered existed MRN
            echo "********* ".$className." case 1 and 2: found in DB <br>";
            foreach( $entity->$getChildMethod() as $child ) {
                $found->$addChildMethod( $child );
            }
            return $this->setResult( $found, $orderinfo, $entity ); //provide found object, cause we need id
        } else {
            if( count($entity->$getFieldMethod()) > 0 ) {
                //Case 3 - User entered new KEY, not existed in DB
                echo "********* ".$className." case 3: not found, new key <br>";
                return $this->setResult( $entity, $orderinfo );
            } else {
                //Case 4 - KEY is not provided.
                echo "********* ".$className." case 4: not found, kye is empty <br>";
                if( $orderinfo ) {
                    $provider = $orderinfo->getProvider()->first();
                } else {
                    $provider = null;
                }

                //method1: create a new object
                //$newPatient = $this->createElement(self::STATUS_VALID,$provider,$className,$fieldName,$parent);
                //                foreach( $entity->$getChildMethod() as $child ) {
//                    $newPatient->$addChildMethod( $child );
//                }
//                return $this->setResult( $newPatient, $orderinfo, $entity );

                //method2: create a key field with next available key value and set this key field to form object (Advantage: no need to copy children)
                $fieldValue = $this->getNextNonProvided("NO".strtoupper($fieldName)."PROVIDED", $className, $fieldName);
                $field = $this->createKeyField( $fieldValue, $className, $fieldName );
                $entity->$clearFieldMethod();
                $entity->$addChildMethod($field);
                return $this->setResult( $entity, $orderinfo );

            }
        }

    }

    public function createKeyField( $fieldValue, $className, $fieldName ) {
        $fieldEntityName = ucfirst($className).ucfirst($fieldName);
        $fieldClass = "Oleg\\OrderformBundle\\Entity\\".$fieldEntityName;
        $field = new $fieldClass(1);
        $field->setField($fieldValue);
        return $field;
    }

    //process single array of fields (i.e. ClinicalHistory Array of Fields)
    public function processFieldArrays( $entity, $orderinfo, $original=null ) {

        $entity->setStatus(self::STATUS_VALID);

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
                                        //echo( "after provider=".$field->getProvider()." <br>" );
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

                                    //############# copy processed field from submitted object to found entity in DB
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
        //echo "class=".$className.$methodName.", id=".$field->getId().", field=".$field."<br>";
        $found = $em->getRepository('OlegOrderformBundle:'.$className.$methodName)->findOneById($field->getId());

        if( !$found ) {
            $this->log->addInfo( "### ".$methodName." not found !!!!!! => add <br>" );
            $methodName = "add".$methodName;
            $entity->$methodName( $field );
        } else {
            $this->log->addInfo( "### ".$methodName." entity is found in DB, validity=".$field->getValidity()."<br>" );
            $found->setProvider($field->getProvider());
            if( $field->getValidity() && $field->getValidity() != 0 ) {
                $found->setValidity($field->getValidity());
            }
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

    public function findOneByIdJoinedToField( $fieldStr, $className, $fieldName, $validity=null, $single=true )
    {
        //echo "fieldStr=".$fieldStr." ";

        $onlyValid = "";
        if( $validity ) {
            //echo " check validity ";
            $onlyValid = " AND cfield.validity=1";
        }

        $query = $this->getEntityManager()
            ->createQuery('
            SELECT c, cfield FROM OlegOrderformBundle:'.$className.' c
            JOIN c.'.$fieldName.' cfield
            WHERE cfield.field = :field'.$onlyValid
            )->setParameter('field', $fieldStr."");

        try {
            if( $single ) {
                return $query->getSingleResult();
            } else {
                return $query->getResult();
            }

        } catch (\Doctrine\ORM\NoResultException $e) {
            return null;
        }
    }

    public function deleteIfReserved( $fieldStr, $className, $fieldName ) {
        //echo "fieldStr=".$fieldStr." ";
        $entities = $this->findOneByIdJoinedToField($fieldStr, $className, $fieldName, null, false);
        if( $entities ) {
            foreach( $entities as $entity ) {
                if( $entity->getStatus() == self::STATUS_RESERVED ) {
                    //echo "id=".$entity->getId()." ";
                    $em = $this->_em;
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
    public function createElement( $status = null, $provider = null, $className, $fieldName, $parent = null, $fieldValue = null ) {
        if( !$status ) {
            $status = self::STATUS_RESERVED;
        }
        $em = $this->_em;

        if( !$fieldValue ) {
            $fieldValue = $this->getNextNonProvided("NO".strtoupper($fieldName)."PROVIDED", $className, $fieldName);
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

        $entityClass = "Oleg\\OrderformBundle\\Entity\\".$className;
        $entity = new $entityClass();
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
    public function getNextNonProvided( $name, $className, $fieldName ) {

//        $query = $this->getEntityManager()
//            ->createQuery('
//            SELECT MAX(pmrn.field) as maxmrn FROM OlegOrderformBundle:Patient p
//            JOIN p.mrn pmrn
//            WHERE pmrn.field LIKE :mrn'
//            )->setParameter('mrn', '%NOMRNPROVIDED%');
        $query = $this->getEntityManager()
            ->createQuery('
            SELECT MAX(cfield.field) as max'.$fieldName.' FROM OlegOrderformBundle:'.$className.' c
            JOIN c.'.$fieldName.' cfield
            WHERE cfield.field LIKE :field'
            )->setParameter('field', '%'.$name.'%');

        $lastField = $query->getSingleResult();
        $index = 'max'.$fieldName;
        $lastFieldStr = $lastField[$index];
        //echo "lastFieldStr=".$lastFieldStr."<br>";
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
    public function isExisted( $entity, $className, $fieldName ) {

        if( !$entity ) {
            //echo "entity is null <br>";
            return null;
        }

        $fieldMethod = "get".ucfirst($fieldName);

        //echo "entity field count=".count($entity->$fieldMethod())."<br>";

        if( $entity->$fieldMethod() == "" || $entity->$fieldMethod() == null ) {
            //echo "entity field get is null<br>";
            return null;
        }

        if( count($entity->$fieldMethod())>0 ) {
            $em = $this->_em;
            $newEntity = null;
            foreach( $entity->$fieldMethod() as $field ) {
                //echo "entity field=".$field->getField()."<br>";
                //$entity = $em->getRepository('OlegOrderformBundle:Patient')->findOneByIdJoinedToMrn( $field->getField() );
                $newEntity = $em->getRepository('OlegOrderformBundle:'.$className)->findOneByIdJoinedToField($field->getField(),$className,$fieldName,true);
                return $newEntity; //return first patient. In theory we should have only one KEY (i.e. mrn) in the submitting patient
            }
        } else {
            //echo "entity null <br>";
            $newEntity = null;
        }
        return $newEntity;
    }

    //check entity by ID (need for postgresql; for mssql can check by if($entity->getId()) )
    public function notExists($entity, $className) {
        $id = $entity->getId();
        if( !$id ) {
            return true;
        }
        $em = $this->_em;
        $found = $em->getRepository('OlegOrderformBundle:'.$className)->findOneById($id);
        if( null === $found ) {
            return true;
        } else {
            return false;
        }
    }

    //get only valid field
    public function getValidField( $fields ) {
        foreach( $fields as $field ) {
            //echo "get valid field=".$field.", validity=".$field->getValidity()."<br>";
            if( $field->getValidity() && $field->getValidity() == 1 ) {
                return $field;
            }
        }
        return null;
    }

}
