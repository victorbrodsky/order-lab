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

namespace Oleg\OrderformBundle\Repository;

use Doctrine\ORM\EntityRepository;

use Oleg\OrderformBundle\Entity\Patient;
use Oleg\OrderformBundle\Security\Util\SecurityUtil;


class ArrayFieldAbstractRepository extends EntityRepository {

    protected $source;

    const STATUS_RESERVED = "reserved";
    const STATUS_VALID = "valid";
    const STATUS_INVALID = "invalid";
    const STATUS_ALIAS = "alias";

    public function __construct($em, $class)
    {
        parent::__construct($em, $class);

        $securityUtil = new SecurityUtil($em,null,null,null);
        $this->source = $securityUtil->getDefaultSourceSystem();
    }

    public function processEntity( $entity, $message, $original=null ) {

        if( !$entity ) {
            throw new \Exception('Provided entity for processing is null');
            //return $entity;
        }

        $em = $this->_em;
        $class = new \ReflectionClass($entity);
        $className = $class->getShortName();

        echo "<br>processEntity className=".$className.", keyFieldName=".$entity->obtainKeyFieldName()."<br>";
        echo $entity;
        echo $className.": original:".$original."<br>";

        //add this object to institution from message.
        //$addClassMethod = "add".$className;
        //$message->getInstitution()->$addClassMethod($entity);
        $entity->setInstitution($message->getInstitution());

        //set default source if empty
        if( !$entity->getSource() ) {
            $entity->setSource($this->source);
        }

        //check and remove duplication objects such as two Part 'A'.
        $entity = $em->getRepository('OlegOrderformBundle:'.$className)->replaceDuplicateEntities( $entity, $message );

        //Accession only: process conflict if exists for accession number. Replace conflicting accession number by a new generated number.
//        if( $className == 'Accession' ) {
//            $entity = $em->getRepository('OlegOrderformBundle:'.$className)->processDuplicationKeyField( $original, $message );
//        }


        $keys = $entity->obtainAllKeyfield();

        //echo "count keys=".count($keys)."<br>";
        //echo "key=".$keys->first()."<br>";

        if( count($keys) == 0 ) {
            $entity->createKeyField();  //this should never execute in normal situation. This happens when form submit with empty fields added by js
            //throw new \Exception( 'Key field does not exists for '.$className );
        } elseif( count($keys) > 1 ) {
            //throw new \Exception( 'This Object ' . $className . ' must have only one key field. Number of key field=' . count($keys) );
            //echo( 'This Object ' . $className . ' should have only one key field. Number of key field=' . count($keys) );

        }

        $key = $entity->obtainValidKeyField();

        if( !$key ) {
            //this can happen for 'deleted-by-amended-order' or 'invalid' keys => don't process this
            //echo 'Key field does not exists for '.$className."<br>";
            return $entity;
            //throw new \Exception( 'Key field does not exists for '.$className );
        }

        //echo "valid key=".$key.", status=".$key->getStatus()."<br>";

        //change keytype from Existing Auto-generated keytype to Auto-generated keytype.
        $entity = $this->changeKeytype($entity);

        if( $key == ""  ) { //$key == "" is the same as $key->getName().""
            echo "Case 1: Empty form object (all fields are empty): generate next available key and assign to this object <br>";

            $nextKey = $this->getNextNonProvided($entity,null,$message);

            //we should have only one key field. At this point block and part names might be empty, this can happen if Accession number was empty on the form
            $key->setField($nextKey);
            $key->setStatus(self::STATUS_VALID);
            $key->setProvider($message->getProvider());
            //echo "nextKey=".$nextKey."<br>";

        } else {

            //this is a main function to check uniqueness
            $found = $this->findUniqueByKey($entity);

            //TODO: if amend the use the same logic as for submit. Probably, restriction that only one valid field exist is not correct.
//            if( $message->getStatus() == 'Amended' ) {
//                //echo "amended order: found=".$found."<br>";
//                //$found = null;
//            } else {
//                //this is a main function to check uniqueness
//                //$found = $this->findUniqueByKey($entity);
//            }


            if( $found ) {
                echo "Case 2: object exists in DB (eneterd key is for existing object): Copy Children, Copy Fields <br>";

                //CopyChildren: copy form's object children to the found one.
                //testing:
//                foreach( $entity->getChildren() as $child ) {
//                    //echo "adding: ".$child."<br>";
//                    $found->addChildren( $child );
//                }

                if( $original ) {
                    $entity = $original;
                }

                return $this->setResult($found, $message, $entity);

            } else {
                echo "Case 3: object does not exist in DB (new key is eneterd) or it's an amend order <br>";
            }

        }

        return $this->setResult($entity, $message, $original);
    }


    public function setResult( $entity, $message, $original=null ) {

        $em = $this->_em;
        $class = new \ReflectionClass($entity);
        $className = $class->getShortName();

        //Encounter only: add encounter's name, sex, age to the corresponding patient fields
        if( $className == 'Encounter' ) {
            if( $original ) {
                $formEntity = $original;
            } else {
                $formEntity = $entity;
            }
            $this->copyCommonFieldsToPatient( $formEntity, $message->getProvider() );
            $this->checkAgeConflict( $entity, $message, $original );
            //exit();
        }

        if( $className == 'Part' ) {

            if( $original ) {
                //echo " ############ original ############ <br>";
                $formEntity = $original;
            } else {
                //echo " ############ entity ############ <br>";
                $formEntity = $entity;
            }
//            echo "<br>paper count=".count($formEntity->getPaper())."<br>";
//            echo "paper name=".$formEntity->getPaper()->first()->getName()."<br>";
//            echo "doc count=".count($formEntity->obtainValidField('paper')->getDocuments())."<br>";
//            echo "doc=".$formEntity->obtainValidField('paper')->getDocuments()->first()->getFullDescriptionStr()."<br>";

            $validPaper = $formEntity->obtainValidField('paper');

            $validPaper = $em->getRepository('OlegUserdirectoryBundle:Document')->processDocuments( $validPaper );

            //add new paper to $entity
            if( $validPaper != null ) {
                $docCount = count($validPaper->getDocuments());
                if( $docCount > 0 ) {
                    $entity->addPaper($validPaper);
                } else {
                    $formEntity->removePaper($validPaper);
                }
            }

            //echo "papers count=".count($entity->getPaper())."<br>";
            //exit('exit part doc');
        }

        if( $className == 'Accession' ) {
            //process images
        }


        //Check if institution of message and entity are match.
        //Since we set institution of entity from message on the previous step, the institution of message and entity must be the same.
        //However, keep this check just in case we got a wrong entity from DB
        if( $entity->getInstitution()->getId() != $message->getInstitution()->getId() ) {
            //echo "inst are diff!";
            throw new \Exception( 'Institution of order form and found object are different '.$className );
        } else {
            //echo $className.": inst are the same!<br>";
        }

        //echo "Set Result for entity:".$entity;

        //set status 'valid'
        $entity->setStatus(self::STATUS_VALID);

        //Copy Fields
        $entity = $this->processFieldArrays($entity,$message,$original);

        //Clean empty array fields, which can be added by user dinamically, such as Part's "Differential Diagnoses" (DiffDisident) with empty input field
        //$entity->cleanEmptyArrayFields();
        $entity = $this->cleanAndProcessEmptyArrayFields($entity);

        if( !$entity->getId() || $entity->getId() == "" ) {
            //echo "set persist: persist ".$className."<br>";
            $em->persist($entity);
        } else {
            //echo "set persist: merge ".$className.", id=".$entity->getId()."<br>";
            //$em->merge($entity);
        }

        //set provider
        $entity->setProvider($message->getProvider());

        //add entity to message
        //$this->addEntityToMessage($entity,$message);
        $addClassMethod = "add".$className;
        $message->$addClassMethod($entity);

        //add this object to institution from message.
        //$message->getInstitution()->$addClassMethod($entity);
        $entity->setInstitution($message->getInstitution());

        echo "After processing:".$entity;

        ///////////////// process parent /////////////////
        //if( $parent ) {   //auto generated by form accession does not have parent
        if( !$entity instanceof Patient ) {

            $parent = $entity->getParent();
            //echo "Parent: ".$parent."<br>";

            //form's auto generated accession does not have parent
            if( $original && $parent == null ) {
                $parent = $original->getParent();
                //echo "originalParent: ".$parent."<br>";
                $parent->setOneChild($entity);
            }

            $originalParent = null;
            if( $original ) {
                $originalParent = $original->getParent();
                //echo "original parent=".$originalParent;
            }

            $parentClass = new \ReflectionClass($parent);
            $parentClassName = $parentClass->getShortName();
            $processedParent = $em->getRepository('OlegOrderformBundle:'.$parentClassName)->processEntity( $parent, $message, $originalParent );
            //echo "processed parent:".$processedParent;

            $entity->setParent($processedParent);
            //echo "processed entity:".$entity;
        }
        ///////////////// EOF process parent /////////////////

        echo "Finish Set Result for entity:".$entity;
        echo "children count=".count($entity->getChildren())."<br>";

        return $entity;
    }



    public function cleanAndProcessEmptyArrayFields($entity) {
        $entity->cleanEmptyArrayFields();
        return $entity;
    }

    public function postProcessing($message) {

        ///////////// post processing part /////////////
        $parts = $message->getPart();
        foreach( $parts as $part ) {
            $key = $part->obtainValidKeyField();
            if( !$key || $key == "" ) {
                //generate auto key
                $nextKey = $this->getNextNonProvided($part,null,$message);
                if( !$nextKey || $nextKey == '' ) {
                    throw new \Exception( 'Key field was not generated for Part, key='.$nextKey );
                }
                $key->setField($nextKey);
                $key->setStatus(self::STATUS_VALID);
                $key->setProvider($message->getProvider());
                //echo "nextKey=".$nextKey."<br>";
            }
        }
        ///////////// EOF post processing part /////////////

        ///////////// post processing block /////////////
        $blocks = $message->getBlock();
        foreach( $blocks as $block ) {
            $key = $block->obtainValidKeyField();
            if( !$key || $key == "" ) {
                //generate auto key
                $nextKey = $this->getNextNonProvided($block,null,$message);
                if( !$nextKey || $nextKey == '' ) {
                    throw new \Exception( 'Key field was not generated for Block, key='.$nextKey );
                }
                $key->setField($nextKey);
                $key->setStatus(self::STATUS_VALID);
                $key->setProvider($message->getProvider());
                //echo "nextKey=".$nextKey."<br>";
            }
        }
        ///////////// EOF post processing block /////////////

    }


//    //overrided by block and part repositories
//    public function attachToParent( $entity, $child ) {
//        //echo "start adding to message <br>";
//        if( $child ) {
//            $entity->addChildren($child);
//        }
//    }

    //find similar child and return the first one
    //return false if no similar children are found
    //Overwritten in EncounterRepository
    public function findSimilarChild($parent,$newChild) {

        //echo "Find similar child in parent:".$parent;
        //echo "Find child:".$newChild;

        $children = $parent->getChildren();

        //echo "<br>";
        //echo $newChild;
        //echo "newChild key=".$newChild->obtainValidKeyfield()."<br>";
        if( $newChild->obtainValidKeyfield()."" == "" ) {   //no name is provided, so can't compare => does not exist
            //echo "false: no name <br>";
            return false;
        }

        if( !$children || count($children) == 0 ) { //no children => does not exist
            //echo "false: no children <br>";
            return false;
        }

        foreach( $children as $child ) {
            //echo $child;

            if( $child === $newChild ) {
                //echo "the same child: continue<br>";
                return false;
            }

            if( $this->entityEqualByComplexKey($child, $newChild) ) {
                return $child;
            }

        }//foreach

        return false;
    }
    public function entityEqualByComplexKey($entity1, $entity2) {

        $key1 = $entity1->obtainValidKeyfield();
        $key2 = $entity2->obtainValidKeyfield();

        //echo $key1."?a=".$key2."<br>";

        //check 1: compare keys
        if( $this->keysEqual($key1,$key2) ) { //keys are the same

            //check 2: compare parent's keys
            $parent = $entity1->getParent();
            if( $parent ) {
                $parKey = $parent->obtainValidKeyfield();

                $newParent = $entity2->getParent();
                if( $newParent ) {
                    $newparKey = $newParent->obtainValidKeyfield();
                } else {
                    $newparKey = null;
                }

                //echo $parKey."?b=".$newparKey."<br>";

                if( $this->keysEqual($parKey,$newparKey) ) {
                    //echo "return found similar child: keys are the same <br>";
                    //echo $child;
                    return true;
                }
            } else {
                //parent does not exist, but keys are equal => return found similar child
                return true;
            }//if parent

        }//if keys equal

        return false;
    }
    public function keysEqual($key1, $key2) {
        //check 1: compare keys
        if( $key1."" == $key2."" ) {   //key values are the same
            //compare keytype if exists
            if( $key1 && method_exists($key1,'getKeytype') ) {
                $keytype1 = $key1->getKeytype()->getId();
                $keytype2 = $key2->getKeytype()->getId();
                if( $keytype1 == $keytype2 ) {
                    return true;
                } else {
                    return false;
                }
            } else {
                return true;
            }
        } else {
            return false;
        }
    }

    //TODO: not used!
    //if child has message without id => child belongs to this message
    public function isEntityBelongsToMessage_NEW( $entity, $message ) {

        //echo "check if belongs to message. entity: ".$entity;

        $orders = $entity->getMessage();

        //echo "orders=".count($orders)."<br>";

        //1) if object (ie part) does not have message => does not belong => return true
        if( count($orders) == 0 ) {
            //echo "yes!!! no orders <br>";
            return true;
        }

        //2a) if at least one order of this object does not have id => new order => this order => return true
        foreach( $orders as $order ) {
            //echo "order id=".$order->getId()."<br>";
            if( $order->getId() && $order->getId() != '' ) {
                //previous order
                //echo "object has message with ID <br>";
            } else {
                //echo "order no ID !!!!!!!!!!!!!!!!!!!!!!!!!!!!<br>";
                return true;
            }
        }
    }


    //TODO: not used!
    public function isEntityBelongsToMessage( $entity, $message ) {

        //echo "check if belongs to message. entity: ".$entity;

        //this condition will not work on postgresql because id is preset for not existing entity
        if( $message->getId() && $message->getId() != '' ) {
//            if( $entity->getMessage()->first()->getId() == $message->getId() ) {
//                return true;
//            } else {
//                return false;
//            }
            //echo "no: message has id <br>";
            return false;
        } else {

            $orders = $entity->getMessage();
            //echo "orders=".count($orders)."<br>";

            //1) if object (ie part) does not have message => does not belong => return true
            if( count($orders) == 0 ) {
                //echo "yes!!! no orders <br>";
                return true;
            }

            //2a) if at least one order of this object does not have id => new order => this order => return true
//            foreach( $orders as $order ) {
//                if( $order->getId() && $order->getId() != '' ) {
//                    //previous order
//                    //echo "object has message with ID <br>";
//                } else {
//                    return true;
//                }
//            }

            //2) if object (ie part) does not have id then this is a new object which belongs to this new message
            if( $entity->getId() && $entity->getId() != '' ) {
                //echo "no: entity has id :".$entity;
                return false;
            } else {
                //echo "yes !!!<br>";
                return true;
            }

        }

    }

    //TODO: not used!
    public function removeThisAndAllParentsFromMessage( $entity, $message ) {
        $className = new \ReflectionClass($entity);
        $shortClassName = $className->getShortName();
        $removeClassMethod = "remove".$shortClassName;    //"removePatient"
        //echo 'removing '.$shortClassName."<br>";

        $message->$removeClassMethod($entity);
        $parent = $entity->getParent();
        if( $parent ) {
            $this->removeThisAndAllParentsFromMessage( $parent, $message );
        }
    }

    //overwrited by accession only => conflicting accession number replaced by a new generated one
    public function processDuplicationKeyField($entity,$message) {
        return $entity; //override it for accession only
    }

    public function changeKeytype($entity) {
        return $entity; //override it for patient and accession only
    }


    //process single array of fields (i.e. ClinicalHistory Array of Fields)
    public function processFieldArrays( $entity, $message=null, $original=null, $status=null ) {

        if( $message ) {
            $provider = $message->getProvider(); //assume message has only one provider.
            //echo "provider=".$provider."<br>";
        }

        //default source
        $securityUtil = new SecurityUtil($this->_em,null,null,null);
        $source = $securityUtil->getDefaultSourceSystem();

        $class = new \ReflectionClass($entity);
        $className = $class->getShortName();
        //echo "Process Array Fields: className=".$className."<br>";

        //make sure the message is set for the key element.
        if( is_subclass_of($entity, 'Oleg\OrderformBundle\Entity\ObjectAbstract') ) {
            //echo "is_subclass_of: className=".$className."<br>";
            $key = $entity->obtainValidKeyfield();
            if( $key && !$key->getMessage() ) {
                $key->setMessage($message);
                //echo "key=".$key.", id=".$key->getId()."<br>";
                //echo "set field's message=".$key->getMessage()."<br>";
            } else {
                //echo "Don't add message to field=".$key."!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!<br>";
            }
        }

        $class_methods = $class->getMethods(\ReflectionMethod::IS_PUBLIC);
        foreach( $class_methods as $method_name ) {

            $methodShortName = $method_name->getShortName();    //getMrn

            if( strpos($methodShortName,'get') !== false && strpos($methodShortName,'__') === false ) {    //filter in only "get" methods

                //echo "methodShortName=".$methodShortName."<br>";

                //get array of fields (i.e. getMrn, getClinicalHistory ... )
                if( $original ) {
                    $fields = $original->$methodShortName();
                } else {
                    $fields = $entity->$methodShortName();
                }
                //echo $methodShortName." count=".count($fields)."<br>";

                if( is_object($fields) || is_array($fields) ) {

                    //echo ( $methodShortName." is object !!! <br>" );

                    //$validitySet = false;   //indicate that validity has not been set in this field array

                    foreach( $fields as $field ) {  //original fields from submitted form

                        $parentname = get_parent_class($field);
                        $basename = get_parent_class($parentname);

                        //echo ( "0 field=".$field.", basename=".$basename."<br>" );

                        if( is_object($field) && $basename == 'App\OrderformBundle\Entity\ArrayFieldAbstract' ) {

                            $class = new \ReflectionClass($field);
                            $parent = $class->getParentClass();

                            //echo "<br>Method:".$methodShortName.", field=".$field.", fieldId=".$field->getId().", status=".$field->getStatus()."<br>";

                            if( $parent ) {

                                //assign message to the field if message is null
                                if( !$field->getMessage() ) {
                                    //echo "set message to field=".$field."<br>";
                                    $field->setMessage($message);
                                    //echo "field's message=".$field->getMessage()."<br>";
                                } else {
                                    //echo "Don't add message to field=".$field."!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!<br>";
                                }

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

                                //create an array, consisting of the field class names, for exceptional fields. These fields are always created as valid.
                                $exceptionArr = array( 'PatientClinicalHistory', 'PartDiffDisident', 'RelevantScans', 'BlockSpecialStains');

                                //############# set provider to the fields from submitted form #############//
                                //echo( $methodShortName.": field provider=".$field->getProvider()." <br>" );
                                if( !$field->getProvider() || $field->getProvider() == "" ) {
                                    //echo( "add provider <br>" );
                                    $field->setProvider($provider); //set provider
                                    //echo( "after added provider=".$field->getProvider()." <br>" );
                                }
                                //############# EOF set provider to the fields from submitted form #############//

                                //############# set default source if empty #############//
                                if( !$field->getSource() ) {
                                    $field->setSource($source);
                                }
                                //############# EOF set default source if empty #############//


                                //############# set validity to the fields from submitted form #############//
                                $validIsSet = $this->validFieldIsSet($entity->$methodShortName(),$exceptionArr);
                                //echo "validIsSet=".$validIsSet."<br>";

                                if( !$validIsSet ) {  //set valid if none of the filed has valid status already
                                    //echo "Status:".$field->getStatus()."; Set status to ".self::STATUS_VALID." to field=".$field." !!!<br>";
                                    $field->setStatus(self::STATUS_VALID);
                                } else {
                                    if( !$field->getStatus() || $field->getStatus() == "" )  {   //set if status is not set yet
                                        //echo "Status:".$field->getStatus()."; Set status to ".self::STATUS_INVALID." to field=".$field." !!!<br>";
                                        $field->setStatus(self::STATUS_INVALID);
                                    } else {
                                        //echo "Status:".$field->getStatus()."; Do not change status of field=".$field." !!!<br>";
                                    }
                                }
                                //############# EOF set validity to the fields from submitted form #############//


                                //############# copy processed field from submitted object (original) to found entity in DB #############//
                                //echo "original=".$original."<br>";
                                if( $original ) {
                                        //echo "entity:".$entity;
                                        //echo "original:".$original;
                                        //echo "field=".$field."<br>";
                                    $methodBaseName = str_replace("get", "", $methodShortName);
                                    $entity = $this->copyField( $entity, $message, $field, $className, $methodBaseName, $exceptionArr );
                                }
                                //############# EOF copy processed field from submitted object (original) to found entity in DB #############//

                            } //if parent

                        } //if object && is_subclass_of

                    } //foreach

                } //if object
                //echo "<br>";
            }
        }

        //echo "after set fields:".$entity;

        return $entity;
    }

    //add field entity if not existed from source object to destination object (from origin to entity)
    //field id is null if check button is not pressed, in this case all fields are gray
    //if entity is found in DB, then all fields have ID, if not then this function is not executed, because process FieldArrays has original=null
    public function copyField( $entity, $message, $field, $className, $methodName, $exceptionArr ) {
        $em = $this->_em;
        //echo "copy Field: class=".$className.$methodName.", id=".$field->getId().", field=".$field."<br>";
        //echo $entity;

        $addMethodName = "add".$methodName; //i.e. addMrn

        $getMethod = "get".$methodName;
        $fields = $entity->$getMethod();

        //echo "this fields count=".count($fields)."<br>";

        $validField = $this->validFieldIsSet( $fields, $exceptionArr );
        //echo "?valid field= ".$validField."<br>";

        //if similar field is already set and provided field is empty => don't add provided field
        if( !$field || trim($field) == "" ) {
            if( $validField && $validField->getProvider()->getId() == $field->getProvider()->getId() ) {
                //echo $methodName.": field is empty and non empty valid field exists => don't add provided field => return!!!<br>";
                return $entity;
            } else {
                //echo $methodName.": add provided field <br>";
            }
        }

        //if valid field exists and it is empty and form field is not empty =>
        //option1: overwrite the valid field with form field value
        //option2: make the valid empty existing field as invalid, add a new not empty form field as valid,
        //echo "valid field=".$validField.", field=".$field."<br>";
        if( $validField && $validField->getField() == "" && $field->getField() != "" ) {

            //option1
            //$validField->setField($field->getField());

            //option2
            $validField->setStatus(self::STATUS_INVALID);

            $field->setStatus(self::STATUS_VALID);
            $entity->$addMethodName( $field );

            //echo( "### ".$methodName." add field as new valid field, change valid field to invalid<br>" );
            return $entity;
        }

        //if field has id, check if the value is not the same. If the values are different, then create a new valid field and make status of DB existed field as invalid
        if( $field->getId() && $field->getId() != "" ) {
            $found = $em->getRepository('OlegOrderformBundle:'.$className.$methodName)->findOneById($field->getId());
            //echo "found field=".$found." compare to field=".$field."<br>";
            if( $found && $found->getField() != $field->getField() ) {
                //echo "different with found by id=".$field->getId()."<br>";
                //create a new $className.$methodName object
                $fieldClassName = "Oleg\\OrderformBundle\\Entity\\".$className.$methodName;
                $status = self::STATUS_VALID;
                $provider = $field->getProvider();
                //echo "provider=".$provider."<br>";
                $newField = new $fieldClassName($status,$provider);
                $newField->setField($field->getField());
                $newField->setMessage($message);
                $entity->$addMethodName( $newField );

                if( $validField ) {
                    $validField->setStatus(self::STATUS_INVALID);
                }

                return $entity;
            }
        }

        //add only if the field array does not already contain this valid field (by field name)
        foreach( $fields as $thisField ) {

            //echo "thisField=".$thisField."<br>";
            //echo "authors field: ".$field->getProvider()."<br>";
            //echo "author thisField: ".$thisField->getProvider()."<br>";
            //echo $methodName.": compare: (".$thisField.") ?= (".$field.") , status=".$thisField->getStatus()." => ";
            //echo "author thisField: ".$thisField->getProvider()->getId() . " ";
            //echo "authors field: ".$field->getProvider()->getId()." => ";
            //echo "message field: ".$field->getMessage()."<br>";
            //echo "author thisField: ".$thisField->getMessage()."<br>";

            if(
                $thisField."" == $field."" &&
                $thisField->getStatus() == self::STATUS_VALID &&
                $thisField->getProvider()->getId() == $field->getProvider()->getId()
            ) {
                //echo "found valid field by field name => don't add field!!! <br>";
                return $entity;
            } else {

                //don't add if it is a key field (only one for submitter and external submitter)
                //echo "is key?:".strtolower($entity->obtainKeyFieldName()) ."==". strtolower($methodName)." => ";
                if( strtolower($entity->obtainKeyFieldName()) == strtolower($methodName) ) {
                    //echo "exception don't add key!!! <br>";
                    return $entity;
                }

                //echo "add field!!! <br>";
            }
        }

        //echo $className.$methodName.": find field =".$field.", id=".$field->getId()."<br>";
        //adding field
        $found = $em->getRepository('OlegOrderformBundle:'.$className.$methodName)->findOneById($field->getId());
        //echo "found id=".$found."<br>";

        if( !$found ) {
            //echo( "### ".$methodName." not found !!!!!! => add <br>" );

            //change status of the field to invalid if valid field is already exists for this entity
            if( $validField ) {

                //echo "fields status=".$field->getStatus()."<br>";

                //requirement: last added field is invalid if not alias
                if( $field->getStatus() != self::STATUS_ALIAS ) {
                    $field->setStatus(self::STATUS_INVALID);
                }

                //requirement: last added field is valid, change status of previous valid field to invalid
                //$field->setStatus(self::STATUS_VALID);
                //$validField->setStatus(self::STATUS_INVALID);
            }

            $entity->$addMethodName( $field );
        } else {
            //echo( "@@@ ".$methodName." is found !!!!!! => don't add <br>" );
        }

        return $entity;
    }

    public function validFieldIsSet( $fields, $exceptionArr=null ) {

        //echo "validFieldIsSet fields count=".count($fields)."<br>";

        if( count($fields) == 0 ) {
            return false;
        }

        //exception for array fields such as Part Differential Diagnosis field. Always added as valid
        $class = new \ReflectionClass($fields->first());
        $className = $class->getShortName();

        if( $exceptionArr && in_array($className, $exceptionArr) ) {
            //echo $className.": skip!!! <br>";
            return false;
        }

        foreach( $fields as $thisField ) {
            //echo "field=".$thisField.", fieldId=".$thisField->getId()."<br>";
            //TODO: should we check if the field is not empty?
            //if( $thisField->getStatus() == self::STATUS_VALID && $thisField != "" ) {
            if( $thisField->getStatus() == self::STATUS_VALID ) {
                //echo "found valid field by field name => don't add field <br>";
                return $thisField;
            }
        }
        return false;
    }

    //$fieldName: search field name by $fieldStr (i.e.: search for S11-12 in accession)
    //$validity - status of the object specified by $className
    //$institutions - Order institutional scope
    public function findOneByIdJoinedToField( $institutions, $fieldStr, $className, $fieldName, $validities=null, $single=true, $extra=null )
    {
        //echo "fieldStr=(".$fieldStr.")<br> ";
        //echo " validity=".$validity."<br>";

        if( !$fieldStr || $fieldStr == "" ) {
            return null;
        }

        if( $validities != null && is_array($validities) == false ) {
            throw new \Exception( 'Validity is provided, but not as array; validities=' . $validities );
        }

        if( $extra && count($extra) > 0 ) {
            if ( strval($extra["keytype"]) != strval(intval($extra["keytype"])) ) {
                //echo "Your variable is not an integer";
                throw new \Exception("keytype is not an integer: ".$extra["keytype"]);
            }
        }

        //add validity conditions
        $validityStr = "";
        if( $validities && is_array($validities) && count($validities)>0 ) {
            //$validityStr = " AND cfield.status='".self::STATUS_VALID."' AND (";
            $validityStr = " AND (";
            $count = 1;
            foreach( $validities as $validity ) {
                $validityStr .= "c.status='".$validity."'";
                if( $count < count($validities) ) {
                    $validityStr .= " OR ";
                }
                $count++;
            }
            $validityStr .= ")";
        }
        //echo "validityStr=".$validityStr." <br> ";

        $extraStr = "";
        if( $extra && count($extra) > 0 ) {
            if( $className == "Patient" || $className == "Accession" ) {
                $extraStr = " AND cfield.keytype = ".$extra["keytype"];
            }
        }

        //echo "extraStr=".$extraStr." ,onlyValid=".$onlyValid." <br> ";

        //add institution conditions: institutions are order ids
        //the object must be under provided institutional scope or under collaboration scope
        $addedInst = array();
        $instStr = "";
        if( $institutions && is_array($institutions) && count($institutions)>0 ) {
            $instStr = " AND (";
            $count = 1;
            foreach( $institutions as $inst ) {
                //echo "inst=".$inst." => ";
                if( !in_array($inst, $addedInst) ) {
                    //echo "not in array<br>";
                    //$instStr .= "c.institution=".$inst."";
                    $permittedInstitution = $this->_em->getRepository('OlegUserdirectoryBundle:Institution')->find($inst);
                    $instStrNew = $this->_em->getRepository('OlegUserdirectoryBundle:Institution')->
                        getCriterionStrForCollaborationsByNode($permittedInstitution,"institution",array("Union","Intersection"));
                    //echo "instStrNew=".$instStrNew."<br>";
                    $instStr .= $instStrNew;
                    if( $count < count($institutions) ) {
                        $instStr .= " OR ";
                    }
                    $addedInst[] = $inst;
                    $count++;
                } else {
                    //echo "in array<br>";
                }
            }
            $instStr .= ")";
        }
        //echo "instStr=".$instStr." ==> <br>";

        $dql = 'SELECT c FROM OlegOrderformBundle:'.$className.' c
                JOIN c.'.$fieldName.' cfield
                JOIN c.institution institution
                WHERE cfield.field = :field'.$validityStr.$extraStr.$instStr;

        $query = $this->getEntityManager()
            ->createQuery($dql)->setParameter('field', $fieldStr."");

        //echo "<br>dql=".$dql." ==> <br>";
        //echo "field=".$fieldStr." <br> ";

        try {

            $entities = $query->getResult();

            if( $single ) {
                //echo "count=".count($entities)."<br>";
                if( count($entities) == 1 ) {
                    //echo $entities[0];
                    //return single entity
                    return $entities[0];
                } else
                if( count($entities) > 0 ) {
                    //throw new \Exception( 'More than one entity found, but single entity is expected for ' . $className. ' with key='. $fieldStr. ', type=' . $extraStr );
                    foreach( $entities as $entity ) {
                        if( $entity->obtainValidKeyfield() ) {
                            //we should return a single result, but we got multiple entity, so return the first valid key one.
                            return $entity;
                        }
                    }
                    //no valid entity found, so return null
                    return null;
                } else {
                    //count == 0 => no entity found, so return null
                    return null;
                }

            } else {
                return $entities;
            }

        } catch (\Doctrine\ORM\NoResultException $e) {
            //echo "find return null<br>";
            return null;
        }
    }

    //find and delete all objects where $fieldName = $fieldStr
    public function deleteIfReserved( $institutions, $fieldStr, $className, $fieldName, $extra=null ) {

        //echo "fieldStr=(".$fieldStr.") ";
        //echo "keytype=(".$extra['keytype'].") ";
        $entities = $this->findOneByIdJoinedToField( $institutions, $fieldStr, $className, $fieldName, array(self::STATUS_RESERVED), false, $extra );
        //echo "found entities = ". count($entities). " ";

        if( !$entities ) {
            return 0;
        }

        $removed = 0;
        foreach( $entities as $entity ) {

            //check if it has children with reserved status
            $count = 0;
            foreach( $entity->getChildren() as $child ) {
                //echo 'status='.$child->getStatus()." ";
                if( $child->getStatus() == self::STATUS_RESERVED ) {
                    $count++;
                }
            }

            //don't delete if this entity has reserved children
            if( $count > 0 ) {
                return -1;
            }

            $em = $this->_em;
            $em->remove($entity);
            $em->flush();
            $removed++;
        }
        return $removed;
    }

    //It is used only when user generate Patient, Accession, Part or Block by pressing "check" button on the form
    //$className: i.e. Patient
    //$fieldName: i.e. mrn
    public function createElement( $institution, $status, $provider, $className, $fieldName, $parent = null, $fieldValue = null, $extra = null, $withfields = false, $flush=true ) {

        //echo "Create Element: className=".$className."<br>";

        if( !$provider ) {
            throw new \Exception('Provider is not provided for creation of element '.$className);
        }

        if( !$institution ) {
            throw new \Exception('Institution is not provided for creation of element '.$className);
        }

        if( $extra && count($extra) > 0 ) {
            if ( strval($extra["keytype"]) != strval(intval($extra["keytype"])) ) {
                //echo "Your variable is not an integer";
                throw new \Exception("keytype is not an integer: ".$extra["keytype"]);
            }
        }

        if( !$status ) {
            $status = self::STATUS_RESERVED;
        }

        $em = $this->_em;

        $entityClass = "Oleg\\OrderformBundle\\Entity\\".$className;
        $entity = new $entityClass($withfields,'valid',$provider);

        $inst = $em->getRepository('OlegUserdirectoryBundle:Institution')->findOneById($institution);
        $entity->setInstitution($inst);
        $institutions = array();
        $institutions[] = $institution;

        $validity = array(self::STATUS_VALID);

        if( !$fieldValue ) {
            $fieldValue = $this->getNextNonProvided($entity,$extra,null);
        }
        //echo "\nfieldValue=".$fieldValue."<br>";
        //echo "extra accession=".$extra['accession']."<br>";
        //echo "extra keytype=".$extra['keytype']."<br>";
        //echo "extra partname=".$extra['partname']."<br>";

        //before create: check if entity with valid key does not exists in DB
        //TODO: If someone generated this name already (very low probability), so regenerate key field name (?)
        $entitiesFound = $this->findOneByIdJoinedToField( $institutions, $fieldValue, $className, $fieldName, $validity, false, $extra );
        //echo "Entities Found count=".count($entitiesFound)."<br>";

        if( count($entitiesFound) == 1 ) {
            return $entitiesFound[0];
        }
        if( count($entitiesFound) > 1 ) {
            foreach( $entitiesFound as $entityFound  ) {
                if( $entityFound->getStatus() == self::STATUS_RESERVED || $entityFound->getStatus() == self::STATUS_VALID ) {
                    return $entityFound;
                }
            }
            return $entitiesFound->first();
        }

        //$fieldEntityName = ucfirst($className).ucfirst($fieldName);
        //echo "fieldEntityName=".$fieldEntityName." ";
//        $fieldClass = "Oleg\\OrderformBundle\\Entity\\".$fieldEntityName;
//        $field = new $fieldClass();
//        $field->setField($fieldValue);

//        if( $provider ) {
//            $field->setProvider($provider);
//            $entity->setProvider($provider);
//        }

        $fields = $entity->obtainKeyField();
        if( count($fields) > 1 ) {
            throw new \Exception('Newly created element has more than one key field. Number of key fields='.count($fields));
        }

        if( count($fields) == 0 ) {
            $entity->createKeyField();
            //throw new \Exception('Newly created element does not have key field. Number of key fields='.count($fields));
        }

        $fields = $entity->obtainKeyField();

        if( count($fields) != 1 ) {
            throw new \Exception('Newly created element must have only one key field. Number of key fields='.count($fields));
        }

        $field = $fields->first();
        $field->setField($fieldValue);

        //set keyfield status to valid
        $field->setStatus(self::STATUS_VALID);

        $field->setProvider($provider);

        if( $field && method_exists($field,'setExtra') ) {
            //find keytype with provided extra (keytype id) from DB
            //echo "extra exists for field=".$field."# ";
            $extraEntity = $this->getExtraEntityById($extra);
            $field->setExtra($extraEntity);
        }

        if ($field && method_exists($field, 'setOriginal')) {
            //strip zeros and record original
            $originalKey = $field->getField();
            $field->setOriginal($originalKey);
            $stripedKey = ltrim($originalKey, '0');
            $field->setField($stripedKey);
        }

//        $keyAddMethod = "add".ucfirst($fieldName);
//        //echo "keyAddMethod=".$keyAddMethod."<br> ";
//
//        $entity->$keyAddMethod($field);

        //echo "set status=".$status."<br>";

        $entity->setStatus($status);

        $em->persist($entity);

        if( $parent ) {
            //echo "#########set Parent = ".$fieldName."<br>\n";
            $em->persist($parent);
            $entity->setParent($parent);
        } else {
            //echo "Parent is not set<br>";
        }

        //echo $entity;
        //exit();

        if( $flush ) {
            $em->flush();
        }       

        return $entity;
    }

    //check the last NOMRNPROVIDED MRN in DB and construct next available MRN
    //$name: NOMRNPROVIDED
    //$className: i.e. Patient
    //$fieldName: i.e. mrn
    public function getNextNonProvided( $entity, $extra=null, $message=null, $prefixname=null ) { //$name, $className, $fieldName ) {

        $class = new \ReflectionClass($entity);
        $className = $class->getShortName();
        $fieldName = $entity->obtainKeyFieldName();
        //$name = "NO".strtoupper($className)."IDPROVIDED";

        if( $extra && count($extra) > 0 ) {
            if ( strval($extra["keytype"]) != strval(intval($extra["keytype"])) ) {
                //echo "Your variable is not an integer";
                throw new \Exception("keytype is not an integer: ".$extra["keytype"]);
            }
        }

        if( !$prefixname ) {
            $prefixname = $entity->obtainNoprovidedKeyPrefix();
        }

        //get extra key by $extra optional parameter or get it from entity
        $extraStr = "";
        if( $extra && count($extra) > 0 ) {
            if( $className == "Patient" || $className == "Accession" ) {
                $extraStr = " cfield.keytype = '".$extra["keytype"]."' AND ";
            }
        } else {
            $validKeyField = $entity->obtainValidKeyfield();
            //get extra field key such as Patient's keytype
            if( $validKeyField && method_exists($validKeyField,'obtainExtraKey') ) {
                $extra = $validKeyField->obtainExtraKey();
                $keytype = $extra["keytype"];
                //echo "keytype=".$keytype."<br>";
                $extraStr = " cfield.keytype = ".$keytype." AND ";
            }
        }

        //echo $message;
        //echo $entity;
        //echo "prefixname=".$prefixname.", fieldName=".$fieldName.", className=".$className."<br>";
        //echo "extraStr=".$extraStr.",<br>";

        //institution
        if( !$entity->getInstitution() && $message && $message->getInstitution() ) {
            $entity->setInstitution( $message->getInstitution() );
        }

        //TODO: change institution hierarchy and add collaboration
        //$inst = " AND c.institution=".$entity->getInstitution()->getId();
        $inst = " AND (" .
                $this->_em->getRepository('OlegUserdirectoryBundle:Institution')->
                    getCriterionStrForCollaborationsByNode($entity->getInstitution(),"institution",array("Union","Intersection")) .
                ")";

        //Here and in Block and Part repository: watch for string max: '9' is greater than '10'? However, max works correctly.
        $queryStr = 'SELECT MAX(cfield.field) as max'.$fieldName.' FROM OlegOrderformBundle:'.$className.
                    ' c'.
                    ' JOIN c.'.$fieldName.' cfield'.
                    ' JOIN c.institution institution'.
                    ' WHERE '.$extraStr.'cfield.field LIKE :field'.$inst;

        //echo "queryStr=".$queryStr."<br>";

        $query = $this->getEntityManager()
            ->createQuery($queryStr)->setParameter('field', '%'.$prefixname.'%');

        //echo "query=".$query->getSql()."<br>";

        $lastField = $query->getSingleResult();
        $index = 'max'.$fieldName;
        $lastFieldStr = $lastField[$index];

        //echo "lastFieldStr=".$lastFieldStr."<br>";
        //$fieldIndexArr = explode("-",$lastFieldStr);
        //echo "count=".count($fieldIndexArr)."<br>";

        $maxKey = $this->getNextByMax($lastFieldStr, $prefixname);

        //check if the valid bigger key was already assigned to the element of the same class attached to this order
        if( $message ) {
            $getSameEntity = "get".$className;
            foreach( $message->$getSameEntity() as $same ) {
                if( $same->getStatus() == self::STATUS_VALID ) {
                    $key = $same->obtainValidKeyfield()->getField()."";
                    $newBiggerKey = $this->getBiggerKey($maxKey,$key,$prefixname);
                    if( $newBiggerKey != -1 ) {
                        $maxKey = $newBiggerKey;
                    }
                }
            }
        }

        /////// check if maxKey does not exists ///////
        $queryCheckStr =
            'SELECT cfield.field FROM OlegOrderformBundle:'.$className.
            ' c'.
            ' JOIN c.'.$fieldName.' cfield'.
            ' JOIN c.institution institution'.
            ' WHERE '.$extraStr.'cfield.field = :field'.$inst;
        $checkQuery = $this->getEntityManager()
            ->createQuery($queryCheckStr)->setParameter('field', $maxKey);
        //echo "query=".$checkQuery->getSql()."<br>";
        $fields = $checkQuery->getResult();
        //echo "fields=".count($fields)."<br>";
        if( count($fields) > 0 ) {
            $errorMsg = 'Can not find max key. Founded max key '.$maxKey." is already exists ".count($fields)." time(s).";
            //exit($errorMsg);
            throw new \Exception($errorMsg);
        }
        /////// EOF check if maxKey does not exists ///////

        //echo "maxKey=".$maxKey."<br>";
        //exit();
        //if( $className == 'Encounter') {
        //    exit();
        //}
        //return $this->getNextByMax($lastFieldStr, $prefixname);
        return $maxKey;
    }

    //$prefixname: NOMRNPROVIDED or MERGE-ID
    public function getNextByMax( $lastFieldStr, $prefixname ) {

//        //get fieldIndex as 0000000000002: It's not working for prefixname with "-", so replace it with the second method below
//        $fieldIndexArr = explode("-",$lastFieldStr);
//        echo "count=".count($fieldIndexArr)."<br>";
//        if( count($fieldIndexArr) > 1 ) {
//            $fieldIndex = $fieldIndexArr[1];
//        } else {
//            $fieldIndex = 0;
//        }

        //get fieldIndex as 0000000000002
        if( $lastFieldStr ) {
            $fieldIndex = str_replace($prefixname."-","",$lastFieldStr);
        } else {
            $fieldIndex = 0;
            //echo "fieldIndex assigned 0<br>";
        }

        //echo "fieldIndex=".$fieldIndex."<br>";

        //$fieldIndex = ltrim($fieldIndex,'0') + 1;
        $fieldIndex = ltrim($fieldIndex,'0');
        $fieldIndex = (int)$fieldIndex + 1;
        $paddedfield = str_pad($fieldIndex,13,'0',STR_PAD_LEFT);
        //echo "paddedfield=".$paddedfield."<br>";
        //exit();
        return $prefixname.'-'.$paddedfield;
    }

    //compare two keys:
    //$keyDb: generated from DB: NOMRNPROVIDED-0000000000001
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

        //get extra field key such as Patient's keytype
        if( method_exists($validKeyField,'obtainExtraKey') ) {
            $extra = $validKeyField->obtainExtraKey();
        } else {
            $extra = null;
        }

        if( $entity->obtainValidKeyfield() ) {
            $em = $this->_em;
            $validity = array(self::STATUS_VALID,self::STATUS_RESERVED); //false; //accept reserved also
            $institutions = array($entity->getInstitution()->getId());
            $newEntity = $em->getRepository('OlegOrderformBundle:'.$className)->findOneByIdJoinedToField($institutions, $validKeyField->getField()."",$className,$fieldName,$validity,true, $extra);
        } else {
            //echo "This entity does not have a valid key field<br>";
            $newEntity = null;
        }

        return $newEntity;
    }


    //replace child if duplicated
    //This abstract method will execute only for Message, Accession and Part.
    //Encounter and Procedure will be processed by Patient repository's overwrite method
    //Block is not processed, because block never remove slides
    public function replaceDuplicateEntities( $parent, $message ) {

        //echo "abstract replace duplicate parent=".$parent;
        //echo "abstract replace duplicate message=".$message;

        if( $parent === $message ) {
            $children = $message->getChildren();
        } else {
            $children = $parent->getChildren();
        }

        if( !$children ) {
            return $parent;
        }

        if( count($children) <= 1 ) {
            //echo "return on children <= 1<br>";
            return $parent;
        }

        $count = 0;
        foreach( $children as $child ) {
            //echo $count.": Testing child=".$child."<br>";

            //don't remove slides
            $class = new \ReflectionClass($child);
            $className = $class->getShortName();
            if( $className == "Slide" ) {
                continue;
            }

            $sameChild = $this->findSimilarChild($parent,$child);

            if( $sameChild ) {
                //echo "Found similar child=".$child."<br>";

                $thisChildren = $child->getChildren();
                foreach( $thisChildren as $thisChild ) {
                    $sameChild->addChildren($thisChild);
                }

                //Copy Fields
                //echo "<br>######################################## Process similar fields ########################################<br>";
                $sameChild = $this->processFieldArrays($sameChild,$message,$child);
                //echo "######################################## EOF Process similar fields ########################################<br>";

                $parent->removeChildren($child);
                $child->setParent(null);
                $child->clearMessage();

                //make $message->removeAccession($child);
                $class = new \ReflectionClass($child);
                $className = $class->getShortName();
                $removeEntity = "remove".$className;
                $message->$removeEntity($child);

                //clean child
                //echo 'mem: ' . (memory_get_usage()/1024/1024) . "<br />\n";
                $em = $this->_em;
                $em->detach($child);
                unset($child);
                gc_collect_cycles();
                //exit();

            }
            $count++;
        }

        //testing
//        $children = $parent->getChildren();
//        foreach( $children as $child ) {
//            //echo "Res child=".$child."<br>";
//        }

        return $parent;
    }

    public function printTree( $entity ) {

        echo "print Tree: " . $entity;
        //echo "print provider count: " . count($entity->getProvider()).", id=".$entity->getProvider()->getId()."<br>";

        foreach( $entity->getChildren() as $child ) {
            if( count( $child->getChildren() ) == 0 ) {
                echo "print Tree node: " . $child;
                //echo "print node provider count: " . count($child->getProvider()).", id=".$child->getProvider()->getId()."<br>";
                echo "----------<br>";
            } else {
                $this->printTree($child);
            }
        }

    }


}
