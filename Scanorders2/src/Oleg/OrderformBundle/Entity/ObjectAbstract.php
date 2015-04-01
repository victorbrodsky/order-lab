<?php
/**
 * Created by JetBrains PhpStorm.
 * User: oli2002
 * Date: 10/24/13
 * Time: 12:14 PM
 * To change this template use File | Settings | File Templates.
 */

namespace Oleg\OrderformBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;


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
     * @ORM\ManyToOne(targetEntity="Oleg\UserdirectoryBundle\Entity\User")
     * @ORM\JoinColumn(name="provider", referencedColumnName="id")
     */
    protected $provider;

    /**
     * @ORM\ManyToOne(targetEntity="Oleg\UserdirectoryBundle\Entity\SourceSystemList")
     * @ORM\JoinColumn(name="source_id", referencedColumnName="id", nullable=true)
     */
    protected $source;

    /**
     * @ORM\ManyToOne(targetEntity="Oleg\UserdirectoryBundle\Entity\Institution")
     * @ORM\JoinColumn(name="institution_id", referencedColumnName="id")
     */
    protected $institution;

//    /**
//     * DocumentContainer can have many Documents; each Document has document type (DocumentTypeList)
//     * ORM\OneToOne(targetEntity="Oleg\UserdirectoryBundle\Entity\DocumentContainer", cascade={"persist","remove"})
//     **/
//    protected $documentContainer;

    /**
     * Attachment can have many DocumentContainers; each DocumentContainers can have many Documents; each DocumentContainers has document type (DocumentTypeList)
     * ORM\OneToOne(targetEntity="Oleg\UserdirectoryBundle\Entity\AttachmentContainer", cascade={"persist","remove"})
     **/
    protected $attachmentContainer;



    public function __construct( $status='invalid', $provider=null, $source = null ) {
        $this->status = $status;
        $this->source = $source;
        $this->provider = $provider;
        $this->orderinfo = new ArrayCollection();
    }


    public function __clone() {
        if( $this->getId() ) {
            $this->setId(null);
            $this->orderinfo = new ArrayCollection();
            $this->makeDependClone();
        }
    }
    
    public function cloneChildren($orderinfo) {
        // Get current collection
        $children = $this->getChildren();

        if( !$children ) return;
        
        $cloneChildren = new ArrayCollection();
        
        foreach( $children as $child ) {
            //echo "1 clone Children: ".$child;
            $orderinfo->removeDepend($child);
            $cloneChild = clone $child;
            //$cloneChild->removeOrderinfo($orderinfo);
            $cloneChild->cloneChildren($orderinfo);
            $cloneChildren->add($cloneChild);
            $cloneChild->setParent($this);
            //$orderinfo->removeDepend($cloneChild);

            //$cloneChild->addOrderinfo($orderinfo);
            $orderinfo->addDepend($cloneChild);
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
     * Add orderinfo
     *
     * @param \Oleg\OrderformBundle\Entity\OrderInfo $orderinfo
     */
    public function addOrderinfo(\Oleg\OrderformBundle\Entity\OrderInfo $orderinfo=null)
    {
        //echo "ObjectAbstract add orderinfo=".$orderinfo."<br>";
        if( !$this->orderinfo->contains($orderinfo) ) {
            $this->orderinfo->add($orderinfo);
        }
    }

    /**
     * Remove orderinfo
     *
     * @param \Oleg\OrderformBundle\Entity\OrderInfo $orderinfo
     */
    public function removeOrderinfo(\Oleg\OrderformBundle\Entity\OrderInfo $orderinfo)
    {
        $this->orderinfo->removeElement($orderinfo);
    }

    /**
     * Get orderinfo
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getOrderinfo()
    {
        return $this->orderinfo;
    }

    public function clearOrderinfo()
    {
        return $this->orderinfo->clear();
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

        if( count($resArr) > 0 ) {
            $res = $resArr[0];
        }

        //echo "res=".$res."<br>";
        return $res;
    }

    //get array of fields with $status belongs to order with id $orderid
    public function obtainStatusFieldArray( $fieldname, $status, $orderid=null ) {

        $res = array();
        $getMethod = "get".$fieldname;

        foreach( $this->$getMethod() as $entity ) {

            if( $status == null ) {

                if( $orderid != null ) {
                    //echo "field order id=".$entity->getOrderinfo()->getOid()." =? ".$orderid."<br>";
                    if( $entity->getOrderinfo()->getOid() == $orderid ) {
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
                        if( $entity->getOrderinfo()->getOid() == $orderid ) {
                            $res[] = $entity;
                        }
                    } else {
                        $res[] = $entity;
                    }

                }//if

            } //else

        } //foreach

        //echo "res count=".count($res)."<br>";
        return $res;
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
        return $this->obtainClassName() . " ID=" . $this->getId();
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

//    //TODO: compare two orderinfo: dirty and from db?
//    //get children which belongs to provided orderinfo
//    public function countChildrenWithOrderinfo( $orderinfo ) {
//        $children = $this->getChildren();
//        $count = 0;
//        foreach( $children as $child ) {
//            if( $child->getOrderinfo() == $orderinfo ) {
//                $count ++;
//            }
//        }
//        return $count;
//    }

}