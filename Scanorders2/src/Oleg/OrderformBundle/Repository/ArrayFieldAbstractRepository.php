<?php

namespace Oleg\OrderformBundle\Repository;

use Doctrine\ORM\EntityRepository;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Doctrine\ORM\Mapping\ClassMetadata;

class ArrayFieldAbstractRepository extends EntityRepository {

    private $log;

    public function __construct($em, $class)
    {
        parent::__construct($em, $class);
        $this->log = new Logger('FieldAbstractRep');
        $this->log->pushHandler(new StreamHandler('./Scanorder.log', Logger::WARNING));
    }

    //process single array of fields (i.e. ClinicalHistory Array of Fields)
    public function processFieldArrays($entity, $provider, $original=null) {

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
                //echo "count=".count($fields)."<br>";

                if( is_object($fields) ) {  //for every field in array (usually, only one item exists)

                    $validitySet = false;   //indicate that validity has not been set in this field array

                    foreach( $fields as $field ) {  //original fields from submitted form

                        if( is_object($field) ) {

                            $fieldReflection = new \ReflectionClass($field);
                            if( $fieldReflection->hasMethod('getProvider') ) {

                                $class = new \ReflectionClass($field);
                                $parent = $class->getParentClass();

                                $this->log->addInfo( "field=".$field."<br>" );

                                if( $parent && $field->getField() && $field->getField() != "" ) {     //filter in all objects with parent class. assume it is "PatientArrayFieldAbstract"

                                    $this->log->addInfo( "###parent exists=".$parent->getName().", method=".$methodShortName.", id=".$field->getId()."<br>" );
                                    $this->log->addInfo( "field id=".$field->getId()."<br>" );

                                    //set provider to the fields from submitted form
                                    if( !$field->getProvider() || $field->getProvider() == "" ) {
                                        $this->log->addInfo( "add provider <br>" );
                                        $field->setProvider($provider); //set provider
                                        $this->log->addInfo( "after provider=".$field->getProvider()." <br>" );
                                    }

                                    //set validity to the fields from submitted form
                                    if( !$validitySet ) {
                                        $this->log->addInfo( "methodShortName=".$methodShortName."<br>" );
                                        if( !$entity->getId() || !$this->hasValidity($entity->$methodShortName()) ) { //set validity for the first added field
                                            //echo "Set validity to 1 <br>";
                                            $field->setValidity(1);
                                        }
                                        $validitySet = true;    //indicate that validity is already has been set in this field array
                                    }

                                    //copy processed field from submitted object to found entity in DB
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

}
