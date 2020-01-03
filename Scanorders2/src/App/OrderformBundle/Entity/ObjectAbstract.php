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
 * Date: 10/24/13
 * Time: 12:14 PM
 * To change this template use File | Settings | File Templates.
 */

namespace App\OrderformBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use App\UserdirectoryBundle\Entity\GeoLocation;
use App\UserdirectoryBundle\Entity\Location;
use App\UserdirectoryBundle\Entity\Spot;
use App\UserdirectoryBundle\Entity\Tracker;
use Symfony\Component\Form\Extension\Core\DataTransformer\DateTimeToStringTransformer;


/**
 * @ORM\MappedSuperclass
 * @ORM\HasLifecycleCallbacks
 */
abstract class ObjectAbstract
{

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * status: use to indicate if the entity with this key is reserved only but not submitted
     * @ORM\Column(type="string", nullable=true)
     */
    protected $status;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $creationdate;

    /**
     * @ORM\ManyToOne(targetEntity="App\UserdirectoryBundle\Entity\User")
     * @ORM\JoinColumn(name="provider", referencedColumnName="id")
     */
    protected $provider;

    /**
     * @ORM\ManyToOne(targetEntity="App\UserdirectoryBundle\Entity\SourceSystemList")
     * @ORM\JoinColumn(name="source_id", referencedColumnName="id", nullable=true)
     */
    protected $source;

    /**
     * @ORM\ManyToOne(targetEntity="App\UserdirectoryBundle\Entity\Institution")
     * @ORM\JoinColumn(name="institution_id", referencedColumnName="id")
     */
    protected $institution;

//    /**
//     * DocumentContainer can have many Documents; each Document has document type (DocumentTypeList)
//     * @ORM\OneToOne(targetEntity="App\UserdirectoryBundle\Entity\DocumentContainer", cascade={"persist","remove"})
//     **/
//    protected $documentContainer;

    /**
     * Attachment can have many DocumentContainers; each DocumentContainers can have many Documents; each DocumentContainers has document type (DocumentTypeList)
     * @ORM\OneToOne(targetEntity="App\UserdirectoryBundle\Entity\AttachmentContainer", cascade={"persist","remove"})
     **/
    protected $attachmentContainer;

    /**
     * @ORM\OneToOne(targetEntity="App\UserdirectoryBundle\Entity\Tracker", cascade={"persist","remove"})
     **/
    protected $tracker;


    protected $changeObjectArr = array();
    protected $tempSource;
    protected $tempUser;


    public function __construct( $status='invalid', $provider=null, $source = null ) {
        $this->status = $status;
        $this->source = $source;
        $this->provider = $provider;
        $this->message = new ArrayCollection();
    }


    public function __clone() {
        if( $this->getId() ) {
            $this->setId(null);
            $this->message = new ArrayCollection();
            $this->makeDependClone();
        }
    }
    
    public function cloneChildren($message) {
        // Get current collection
        $children = $this->getChildren();

        if( !$children ) return;
        
        $cloneChildren = new ArrayCollection();
        
        foreach( $children as $child ) {
            //echo "1 clone Children: ".$child;
            $message->removeDepend($child);
            $cloneChild = clone $child;
            //$cloneChild->removeMessage($message);
            $cloneChild->cloneChildren($message);
            $cloneChildren->add($cloneChild);
            $cloneChild->setParent($this);
            //$message->removeDepend($cloneChild);

            //$cloneChild->addMessage($message);
            $message->addDepend($cloneChild);
            //echo "2 cloned Children: ".$cloneChild;
        }

        $this->setChildren($cloneChildren);

    }

    //clone dependents (i.e. blockname, specialStains ... )
    public function cloneDepend($depends,$parent=null) {

        //$class = new \ReflectionClass($depends->first());
        //$className = $class->getShortName();
        //echo "cloneDepend ".$className."<br>";

        $dependClone = new ArrayCollection();
        foreach( $depends as $depend ) {
            //echo "id=".$depend->getId();
            $thisclone = clone $depend;
            //echo ": id=".$thisclone->getId();
            if( $parent ) {
                $thisclone->setParent($parent);
            }
            $dependClone->add($thisclone);
            //echo " => id=".$thisclone->getId()."<br>";
        }
        return $dependClone;
    }

    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    public function getId() {
        return $this->id;
    }


    /**
     * @ORM\PrePersist
     */
    public function setCreationdate()
    {
        $this->creationdate = new \DateTime();
    }

    /**
     * @return \DateTime
     */
    public function getCreationdate()
    {
        return $this->creationdate;
    }

    /**
     * @param mixed $status
     */
    public function setStatus($status)
    {
        $this->status = $status;
    }

    /**
     * @return mixed
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Add message
     *
     * @param \App\OrderformBundle\Entity\Message $message
     */
    public function addMessage(\App\OrderformBundle\Entity\Message $message=null)
    {
        //echo "ObjectAbstract add message=".$message."<br>";
        if( !$this->message->contains($message) ) {
            $this->message->add($message);
        }
    }

    /**
     * Remove message
     *
     * @param \App\OrderformBundle\Entity\Message $message
     */
    public function removeMessage(\App\OrderformBundle\Entity\Message $message)
    {
        $this->message->removeElement($message);
    }

    /**
     * Get message
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getMessage()
    {
        return $this->message;
    }

    public function clearMessage()
    {
        return $this->message->clear();
    }

    public function setProvider($provider)
    {
        $this->provider = $provider;

        return $this;
    }

    public function getProvider()
    {
        return $this->provider;
    }

    /**
     * @param mixed $source
     */
    public function setSource($source)
    {
        $this->source = $source;
    }

    /**
     * @return mixed
     */
    public function getSource()
    {
        return $this->source;
    }

    /**
     * @param mixed $institution
     */
    public function setInstitution($institution)
    {
        $this->institution = $institution;
    }

    /**
     * @return mixed
     */
    public function getInstitution()
    {
        return $this->institution;
    }

    /**
     * @param mixed $attachmentContainer
     */
    public function setAttachmentContainer($attachmentContainer)
    {
        $this->attachmentContainer = $attachmentContainer;
    }

    /**
     * @return mixed
     */
    public function getAttachmentContainer()
    {
        return $this->attachmentContainer;
    }

    /**
     * @param mixed $tracker
     */
    public function setTracker($tracker)
    {
        $this->tracker = $tracker;
    }

    /**
     * @return mixed
     */
    public function getTracker()
    {
        return $this->tracker;
    }

    /**
     * @return mixed
     */
    public function getTempSource()
    {
        return $this->tempSource;
    }

    /**
     * @param mixed $tempSource
     */
    public function setTempSource($tempSource)
    {
        $this->tempSource = $tempSource;
    }

    /**
     * @return mixed
     */
    public function getTempUser()
    {
        return $this->tempUser;
    }

    /**
     * @param mixed $tempUser
     */
    public function setTempUser($tempUser)
    {
        $this->tempUser = $tempUser;
    }





//    /**
//     * @param mixed $documentContainer
//     */
//    public function setDocumentContainer($documentContainer)
//    {
//        $this->documentContainer = $documentContainer;
//    }
//
//    /**
//     * @return mixed
//     */
//    public function getDocumentContainer()
//    {
//        return $this->documentContainer;
//    }






    //children methods

    public function setOneChild($child) {

        $this->getChildren()->clear();
        $this->addChildren($child);
        return $this;
    }

    public function cleanEmptyArrayFields() {
        //
    }

    public function obtainValidChild()
    {
        if( !$this->getChildren() ) {
            return null;
        }

        $validChild = null;
        $count = 0;
        //echo "number of children=: ".count($this->getChildren())."<br>";
        foreach( $this->getChildren() as $child) {
            //echo "get valid: ".$child."<br>";
            if( $child->getStatus()."" == "valid" ) {
                $validChild = $child;
                $count++;
            }
        }
//        if( $count > 1 ) {
//            throw $this->createNotFoundException( 'This Object must have only one valid child. Number of valid children=' . $count );
//        }
        return $validChild;
    }

    //return the key field with validity 1 or return a single existing key field
    public function obtainValidKeyfield()
    {
        if( !$this->obtainKeyField() ) {
            return null;
        }

        if( count($this->obtainKeyField()) == 1 ) {
            return $this->obtainKeyField()->first();
        }

        $validField = null;
        $count = 0;
        $names = "";
        //echo "number of children=: ".count($this->getChildren())."<br>";
        foreach( $this->obtainKeyField() as $field) {
            //echo "get field: status=".$field->getStatus().", field=".$field."<br>";
            if( $field->getStatus() == "valid" ) {
                //echo "child is valid!<br>";
                $validField = $field;
                $count++;
                $names = $names . $field . " ";
            }
        }

        //echo "count=".$count."<br>";

        if( $count > 1 ) {
            $class = new \ReflectionClass($field);
            $className = $class->getShortName();
            throw new \Exception( 'This Object must have only one valid child. Number of valid children=' . $count . ", className=".$className.", names=".$names);
        }

        return $validField;
    }

    public function obtainAllKeyfield() {
        return $this->obtainKeyField();
    }

    public function setStatusAllKeyfield($status) {
        foreach( $this->obtainKeyField() as $child) {
            $child->setStatus($status);
        }
    }

    public function obtainArrayFieldNames() {
        return null;
    }

    //For external submitter users: filter fields by author and keep only latest created fields
    //Since we don't remove external user role from logic, then this function will return unfilter object all the time
    public function filterArrayFields( $user, $strict = false ) {

        //filter only if the user has role external submitter
        if( !$user->hasRole('ROLE_SCANORDER_EXTERNAL_SUBMITTER') ) {
            return $this;
        }

        $fields = $this->getArrayFields();

        foreach( $fields as $field ) {
            $getMethod = "get".$field;
            $removeMethod = "remove".$field;
            //echo "get Method=".$getMethod." ";

            //don't remove key fields
            if( strtolower($this->obtainKeyFieldName()) == strtolower($field) && !$strict ) {  //&& !$strict
                continue;
            }

            $latestEntity = null;
            foreach( $this->$getMethod() as $entity ) {

                if( $entity->getProvider()->getId() != $user->getId() ) {

                    $this->$removeMethod($entity);

                } else {

                    //don't remove array fields
                    if( $this->obtainArrayFieldNames() && in_array($field,$this->obtainArrayFieldNames()) ) {
                        continue;
                    }

                    //get the latest entity
                    if( !$latestEntity || $entity->getCreationdate() > $latestEntity->getCreationdate() ) {
                        $this->$removeMethod($latestEntity);
                        $latestEntity = $entity;
                    }

                } //if
            } //foreach

        }

        return $this;
    }


    //get number of existing fields.
    //strict=false => don't count key fields
    //strict=true => count all, including key fields
    public function obtainExistingFields( $strict = false ) {

        $count = 0;

        $fields = $this->getArrayFields();

        foreach( $fields as $field ) {
            $getMethod = "get".$field;

            //don't remove key fields
            if( strtolower($this->obtainKeyFieldName()) == strtolower($field) && !$strict ) {
                //echo $field.": DON'T count key fields <br>";
                continue;
            }

            if( count($this->$getMethod()) > 0 ) {
                //echo "not empty field=".$field."; ";
                $count++;
            }

        }

        //echo "count existing field=".$count."<br> ";

        return $count;
    }

    //get only one field
    public function obtainValidField( $fieldname, $orderid=null ) {
        return $this->obtainStatusField( $fieldname, 'valid', $orderid );
    }

    //get only one field with $status belongs to order with id $orderid
    //if status is null, get the first field belongs to the given order id
    public function obtainStatusField( $fieldname, $status, $orderid=null ) {

        $res = null;

        $resArr = $this->obtainStatusFieldArray($fieldname, $status, $orderid);

        if( count($resArr) == 1 ) {
            $res = $resArr[0];
        }

        //if multiple found, get the latest one (with the latest timestamp getCreationdate)
        if( count($resArr) > 1 ) {
            $latestField = null;
            foreach( $resArr as $field ) {
                //echo $fieldname.":".$field->getId()."field=".$field."<br>";
                if( !$field->getField() ) {
                    continue; //ignore empty value
                }
                if( !$latestField ) {
                    $latestField = $field;
                    continue;
                }
                if( $field->getCreationdate() > $latestField->getCreationdate() ) {
                    $latestField = $field;
                }
            }
            //echo $fieldname.":".$latestField->getId()."res=".$latestField."<br>";
            $res = $latestField;
        }

        //echo $fieldname.":".$res->getId()."res=".$res."<br>";
        return $res;
    }

    //get array of fields with $status belongs to order with id $orderid
    public function obtainStatusFieldArray( $fieldname, $status, $orderid=null ) {

        $res = array();
        $getMethod = "get".$fieldname;

        foreach( $this->$getMethod() as $entity ) {

            if( $status == null ) {

                if( $orderid != null ) {
                    //echo "field order id=".$entity->getMessage()->getOid()." =? ".$orderid."<br>";
                    if( $entity->getMessage()->getOid() == $orderid ) {
                        $res[] = $entity;
                    }
                } else {
                    $res[] = $entity;
                }

            } else {

                //echo $entity->getStatus()."?=".$status."<br>";
                if( $entity->getStatus() == $status ) {

                    //if orderid is given, then return the first $status field with provided orderid
                    if( $orderid != null ) {
                        if( $entity->getMessage()->getOid() == $orderid ) {
                            $res[] = $entity;
                        }
                    } else {
                        $res[] = $entity;
                    }

                }//if

            } //else

        } //foreach

        //echo $fieldname.": res count=".count($res)."<br>";
        return $res;
    }

    //get only one field
    public function obtainStatusFieldArrayOrAll( $fieldname, $status, $orderid=null ) {
        $resArr = $this->obtainStatusFieldArray($fieldname,$status,$orderid);
        if( count($resArr) > 0 ) {
            return $resArr;
        } else {
            return $this->obtainStatusFieldArray($fieldname,null,$orderid);
        }
    }

    //$fields: array of fields that should be filter out
    public function obtainOneValidObject($fields,$asarray=false) {

        $res = array();

        foreach( $fields as $field ) {

            if( is_array($field) ) {
                if( $asarray == false ) {
                    throw new \Exception('This method does not accept complex fields for filtering');
                }
                $key = $field[0];
                unset($field[0]);
                $oneField = $this->obtainValidField($key);
                foreach( $field as $simpleField ) {
                    $getMethod = "get".$simpleField;
                    $res[$this->getId()][$key][$simpleField] = $oneField->$getMethod()."";
                    echo "<br>";
                }
            } else {
                $oneField = $this->obtainValidField($field);
                if( $asarray ) {
                    $res[$this->getId()][$field] = $oneField."";
                } else {
                    $this->$field->clear();
                    $addMethod = "add".$field;
                    $this->$addMethod($oneField);
                }
            }




        }

        return $res;
    }

    public function obtainFullObjectName() {
        //return $this->obtainClassName() . " ID=" . $this->getId();
        return "";
    }

    public function obtainClassName() {
        $class = new \ReflectionClass($this);
        $className = $class->getShortName();
        return $className;
    }

    public function obtainFullClassName() {
        $class = new \ReflectionClass($this);
        $className = $class->getShortName();
        $classNamespace = $class->getNamespaceName();
        return $classNamespace."\\".$className;
    }

    public function obtainNoprovidedKeyPrefix() {
        return $name = "NO".strtoupper($this->obtainClassName())."IDPROVIDED";
    }

    public function setStatusAllFields($fields,$status,$user=null) {
        foreach( $fields as $field ) {
            $field->setStatus($status);
            //$field->setUpdateAuthor($user);
        }
    }

    public function changeStatusAllFields($fields,$statusOld,$statusNew) {
        foreach( $fields as $field ) {
            if( $field->getStatus() == $statusOld ) {
                $field->setStatus($statusNew);
                //$field->setUpdateAuthor($user);
            }
        }
    }

    public function getHolderPatient() {
        $parent = $this->getParent();
        if( $parent ) {
            return $parent->getHolderPatient();
        } else {
            return $this;
        }
    }
    /**
     * @return array
     */
    public function obtainChangeObjectArr()
    {
        return $this->changeObjectArr;
    }
    /**
     * @param array $changeObjectArr
     */
    public function setChangeObjectArr($changeObjectArr)
    {
        $this->changeObjectArr = $changeObjectArr;
    }
    public function addChangeObjectArr($arr)
    {
        $originalArr = $this->obtainChangeObjectArr();
        //$mergedChangeObjectArr = array_merge($this->obtainChangeObjectArr(),$arr);
        //$mergedChangeObjectArr = array_merge_recursive($originalArr,$arr);
        $mergedChangeObjectArr = $this->array_merge_recursive_distinct($originalArr,$arr);
        //$mergedChangeObjectArr = $this->array_merge_recursive_distinct_changed($originalArr,$arr);
        //$mergedChangeObjectArr = $this->array_merge_recursive_new($originalArr,$arr);

        //$mergedChangeObjectArr = $this->array_merge_assoc($this->obtainChangeObjectArr(),$arr,true);
//        echo "merged:<br><pre>";
//        echo print_r($mergedChangeObjectArr);
//        echo "</pre>";
        $this->setChangeObjectArr($mergedChangeObjectArr);
    }


    //http://www.php.net/manual/en/function.array-merge-recursive.php
    function array_merge_recursive_distinct_changed ( array &$array1, array &$array2 )
    {
        static $level=0;
        $merged = [];
        if (!empty($array2["mergeWithParent"]) || $level == 0) {
            $merged = $array1;
        }

        foreach ( $array2 as $key => &$value )
        {
            if (is_numeric($key)) {
                $merged [] = $value;
            } else {
                $merged[$key] = $value;
            }

            if ( is_array ( $value ) && isset ( $array1 [$key] ) && is_array ( $array1 [$key] )
            ) {
                $level++;
                $merged [$key] = $this->array_merge_recursive_distinct_changed($array1 [$key], $value);
                $level--;
            }
        }
        unset($merged["mergeWithParent"]);
        return $merged;
    }
    /**
     * array_merge_recursive does indeed merge arrays, but it converts values with duplicate
     * keys to arrays rather than overwriting the value in the first array with the duplicate
     * value in the second array, as array_merge does. I.e., with array_merge_recursive,
     * this happens (documented behavior):
     *
     * array_merge_recursive(array('key' => 'org value'), array('key' => 'new value'));
     *     => array('key' => array('org value', 'new value'));
     *
     * array_merge_recursive_distinct does not change the datatypes of the values in the arrays.
     * Matching keys' values in the second array overwrite those in the first array, as is the
     * case with array_merge, i.e.:
     *
     * array_merge_recursive_distinct(array('key' => 'org value'), array('key' => 'new value'));
     *     => array('key' => array('new value'));
     *
     * Parameters are passed by reference, though only for performance reasons. They're not
     * altered by this function.
     *
     * @param array $array1
     * @param array $array2
     * @return array
     * @author Daniel <daniel (at) danielsmedegaardbuus (dot) dk>
     * @author Gabriel Sobrinho <gabriel (dot) sobrinho (at) gmail (dot) com>
     */
    function array_merge_recursive_distinct ( array &$array1, array &$array2, $unique=false )
    {
        $merged = $array1;

        foreach ( $array2 as $key => &$value )
        {
            if ( is_array ( $value ) && isset ( $merged [$key] ) && is_array ( $merged [$key] ) )
            {
                $merged [$key] = $this->array_merge_recursive_distinct ( $merged [$key], $value );
            }
            else
            {
                $merged [$key] = $value;
            }
        }

        if ($unique) {
            $merged = array_unique($merged);
        }

        return $merged;
    }
    //http://stackoverflow.com/questions/6813884/array-merge-on-an-associative-array-in-php
    function array_merge_assoc($array1, $array2, $unique=false)
    {

        if(sizeof($array1)>sizeof($array2))
        {
            $size = sizeof($array1);
        }
        else
        {
            $a = $array1;
            $array1 = $array2;
            $array2 = $a;

            $size = sizeof($array1);
        }

        $keys2 = array_keys($array2);

        for($i = 0;$i<$size;$i++)
        {

            $thisIndex = $keys2[$i];
            if( !$thisIndex ) {
                $thisIndex = 0;
            }

            if( array_key_exists($thisIndex, $array1) ) {
                $thisArr1 = $array1[$thisIndex];
                //$array1[$keys2[$i]] = array_merge( $array1[$keys2[$i]], $array2[$keys2[$i]] );
            } else {
                $thisArr1 = $array1;
                //$array1[$keys2[$i]] = array_merge( $array1, $array2[$keys2[$i]] );
            }
            if( array_key_exists($thisIndex, $array2) ) {
                $thisArr2 = $array2[$thisIndex];
                //$array1[$keys2[$i]] = array_merge( $array1[$keys2[$i]], $array2[$keys2[$i]] );
            } else {
                $thisArr2 = $array2;
                //$array1[$keys2[$i]] = array_merge( $array1, $array2[$keys2[$i]] );
            }
            //$array1[$keys2[$i]] = $array1[$keys2[$i]] + $array2[$keys2[$i]];
            //$array1[$keys2[$i]] = array_merge( $array1[$keys2[$i]], $array2[$keys2[$i]] );

            $array1[$thisIndex] = array_merge( $thisArr1, $thisArr2 );
        }

        $array1 = array_filter($array1);

        if ($unique) {
            //$array1 = array_unique($array1);
        }

        return $array1;
    }

    public function setArrayFieldObjectChange($fieldName,$action,$addedObject) {
        //echo $this->getId().": setArrayFieldObjectChange $fieldName <br>";
        $holderPatient = $this->getHolderPatient();
        //$changeObjectArr = $holderPatient->obtainChangeObjectArr();
        $field = $addedObject->getField();
        if( $field instanceof \DateTime ) {
            $field = $addedObject->formatDataToString($field);
        }
        $addedObjectId = $addedObject->getId();
        if( !$addedObjectId ) {
            $addedObjectId = "0";
        }
        $changeObjectArr[$fieldName][$addedObjectId][$action]['field'] = $field."";
        $changeObjectArr[$fieldName][$addedObjectId][$action]['status'] = $addedObject->getStatus()."";
        $changeObjectArr[$fieldName][$addedObjectId][$action]['provider'] = $addedObject->getProvider()."";
        $holderPatient->addChangeObjectArr($changeObjectArr);

        if( $action == 'add' ) {
            //echo $fieldName.": Copy array from field; parent id=".$addedObject->getParent()->getId()."<br>";
            $changeFieldArr = $addedObject->getChangeFieldArr();
            $holderPatient->addChangeObjectArr($changeFieldArr);
//            $res = "<pre>";
//            $res .= $this->var_dump_ret($changeFieldArr);
//            $res .= "</pre>";
//            echo "Array from field:<br>".$res."<br>";
        }
    }
    public function obtainChangeObjectStr($asArr=false) {
        $changeArr = $this->obtainChangeObjectArr();

        if( $asArr ) {
            $res = "<pre>";
            $res .= $this->var_dump_ret($changeArr);
            $res .= "</pre>";
        } else {
            $res = json_encode($changeArr);
        }

        return $res;
    }
    function var_dump_ret($mixed = null) {
        ob_start();
        print_r($mixed);
        $content = ob_get_contents();
        ob_end_clean();
        return $content;
    }

    public function addContactinfoByTypeAndName($user,$system,$locationType=null,$locationName=null,$spotEntity=null,$withdummyfields=false,$em=null,$removable=1) {
        $location = new Location($user);

        if( $locationType ) {
            $location->addLocationType($locationType);
        }

        $location->setName($locationName);
        $location->setStatus(1);
        $location->setRemovable($removable);

        $geoLocation = new GeoLocation();
        $location->setGeoLocation($geoLocation);

        if( $withdummyfields ) {
            //$location->setEmail("dummyemail@myemail.com");
            //$location->setPhone("(212) 123-4567");
            //$geoLocation->setStreet1("100");
            //$geoLocation->setStreet2("Broadway");
            $geoLocation->setZip("10065");
            $geoLocation->setCounty('New York County');

            if( $em ) {
                $city = $em->getRepository('AppUserdirectoryBundle:CityList')->findOneByName('New York');
                $geoLocation->setCity($city);

                $state = $em->getRepository('AppUserdirectoryBundle:States')->findOneByName('New York');
                $geoLocation->setState($state);

                $country = $em->getRepository('AppUserdirectoryBundle:Countries')->findOneByName('United States');
                $geoLocation->setCountry($country);
            }
        }

        $tracker = $this->getTracker();
        if( !$tracker) {
            $tracker = new Tracker();
            $this->setTracker($tracker);
        }

        if( !$spotEntity ) {
            $spotEntity = new Spot($user,$system);
        }
        $spotEntity->setCurrentLocation($location);
        $spotEntity->setCreation(new \DateTime());
        $spotEntity->setSpottedOn(new \DateTime());

        $tracker->addSpot($spotEntity);
    }


//    //replace contains in AddChild
//    public function childAlreadyExist( $newChild ) {
//
//        $children = $this->getChildren();
//
//        echo "<br>";
//        echo $newChild;
//        echo "newChild key=".$newChild->obtainValidKeyfield()."<br>";
//        if( $newChild->obtainValidKeyfield()."" == "" ) {   //no name is provided, so can't compare => does not exist
//            echo "false: no name <br>";
//            return false;
//        }
//
//        if( !$children || count($children) == 0 ) { //no children => does not exist
//            echo "false: no children <br>";
//            return false;
//        }
//
//        foreach( $children as $child ) {
//            echo $child;
//
//            echo $child->obtainValidKeyfield()."?a=".$newChild->obtainValidKeyfield()."<br>";
//
//            //check 1: compare keys
//            if( $child->obtainValidKeyfield()."" == $newChild->obtainValidKeyfield()."" ) {   //keys are the same
//
//                $parent = $child->getParent();
//                $parKey = $parent->obtainValidKeyfield();
//
//                $newParent = $newChild->getParent();
//                if( $newParent ) {
//                    $newparKey = $newParent->obtainValidKeyfield();
//                } else {
//                    $newparKey = null;
//                }
//
//                echo $parKey."?b=".$newparKey."<br>";
//
//                //check 2: compare parent's keys
//                if( $parKey."" == $newparKey."" ) {
//                    return true;
//                }
//
//            }
//
//        }
//
//        return false;
//    }

//    //TODO: compare two message: dirty and from db?
//    //get children which belongs to provided message
//    public function countChildrenWithMessage( $message ) {
//        $children = $this->getChildren();
//        $count = 0;
//        foreach( $children as $child ) {
//            if( $child->getMessage() == $message ) {
//                $count ++;
//            }
//        }
//        return $count;
//    }

}