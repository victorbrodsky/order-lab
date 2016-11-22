<?php
namespace Oleg\UserdirectoryBundle\Util;

use Symfony\Component\HttpFoundation\Response;
use Oleg\UserdirectoryBundle\Entity\FormNode;
use Oleg\UserdirectoryBundle\Entity\ObjectTypeText;


/**
 * Description of FormNodeUtil
 *
 * @author Cina
 */
class FormNodeUtil
{

    protected $em;
    protected $sc;
    protected $container;

    public function __construct($em, $sc, $container)
    {
        $this->em = $em;
        $this->sc = $sc;
        $this->container = $container;
    }

    public function processFormNodes($request, $formNodeHolder, $holderEntity)
    {
        if( !$formNodeHolder ) {
            return;
        }

        $formNodes = $formNodeHolder->getFormNodes();
        if( !$formNodes ) {
            return;
        }

//        $rootFormNode = $formNode->getRootName($formNode);
//        if( !$rootFormNode ) {
//            exit("No Root of the node ".$formNode."<br>");
//            return;
//        }

        $data = $request->request->all();

        //print "<pre>";
        //print_r($data);
        //print "</pre>";
        //$unmappedField = $data["formnode-4"];
        //echo "<br>unmappedField=" . $unmappedField . "<br>";
        //$unmappedField = $data["formnode-6"];
        //echo "<br>unmappedField=" . $unmappedField . "<br>";
        //echo "<br><br>";

        //process by form root's children nodes
        //$this->processFormNodeRecursively($data,$rootFormNode,$holderEntity);

        //process by data partial key name" "formnode-4" => "formnode-"
        $this->processFormNodesFromDataKeys($data,$holderEntity);
    }

    //process by data partial key name" "formnode-4" => "formnode-"
    public function processFormNodesFromDataKeys($data,$holderEntity) {
        foreach( $data as $key=>$value ){
            //if( "show_me_" == substr($key,0,8) ) {
            if( strpos($key, 'formnode-') !== false ) {
                $formNodeId = str_replace('formnode-','',$key);
                // do whatever you need to with $formNodeId...
                $thisFormNode = $this->em->getRepository("OlegUserdirectoryBundle:FormNode")->find($formNodeId);
                if( !$thisFormNode ) {
                    //exit("No Root of the node id=".$formNodeId."<br>");
                    continue;
                }
                $this->processFormNodeByType($data,$thisFormNode,$holderEntity);
            }
        }
    }

    //NOT USED
    public function processFormNodeRecursively( $data, $formNode, $holderEntity ) {

        echo "formNode=".$formNode."<br>";
        $children = $formNode->getChildren();
        if( $children ) {

            foreach( $children as $childFormNode ) {
                $this->processFormNodeByType($data,$childFormNode,$holderEntity);
                $this->processFormNodeRecursively($data,$childFormNode,$holderEntity);
            }

        } else {
            $this->processFormNodeByType($data,$formNode,$holderEntity);
        }

    }

    public function processFormNodeByType( $data, $formNode, $holderEntity ) {
        if( !$this->hasValue($formNode) ) {
            //exit("No Value of the node=".$formNode."<br>");
            return;
        }

        $key = "formnode-".$formNode->getId();
        $formValue = $data[$key];
        echo $key.": formValue=" . $formValue . "<br>";

        //1) create a new list element
        $newListElement = $this->createNewList($formNode,$formValue);
        //echo "newListElement=".$newListElement."<br>";
        if( !$newListElement ) {
            //exit("No newListElement created: formNode=".$formNode."; formValue=".$formValue."<br>");
            return;
        }

        //2) add value to the created list
        if( $formValue ) {
            $newListElement->setValue($formValue);
        }

        //3) set message by entityName to the created list
        $newListElement->setObject($holderEntity);

        //testing
        if( 0 ) {
            $class = new \ReflectionClass($newListElement);
            $className = $class->getShortName();
            $classNamespace = $class->getNamespaceName();
            echo "newListElement list: classNamespace=" . $classNamespace . ", className=" . $className . ", Value=" . $newListElement->getValue() . "<br>";
            //echo "newListElement list: Namespace=" . $newListElement->getEntityNamespace() . ", Name=" . $newListElement->getEntityName() . ", Value=" . $newListElement->getValue() . "<br>";
        }
        //exit("processFormNodeByType; formValue=".$formValue);

        $this->em->persist($newListElement);
        //$this->em->flush($newListElement); //testing
    }


    public function hasValue( $formNode ) {

        $formNodeTypeName = $formNode->getObjectType()->getName()."";
        //echo "formNodeType=" . $formNodeType . "<br>";

        if( $formNodeTypeName == "Form Group" ) {
            return false;
        }
        if( $formNodeTypeName == "Form" ) {
            return false;
        }
        if( $formNodeTypeName == "Form Section" ) {
            return false;
        }

        $formNodeType = $formNode->getObjectType();
        $entityNamespace = $formNodeType->getEntityNamespace();
        $entityName = $formNodeType->getEntityName();
        if( !$entityNamespace || !$entityName ) {
            return false;
        }

        return true;
    }

    public function createNewList( $formNode, $formValue=null ) {
        $userSecUtil = $this->container->get('user_security_utility');
        $formNodeObjectType = $formNode->getObjectType();
        $entityNamespace = $formNodeObjectType->getEntityNamespace();
        $entityName = $formNodeObjectType->getEntityName();

        if( !$entityNamespace || !$entityName ) {
            return null;
        }

        //Oleg\UserdirectoryBundle\Entity:ObjectTypeText
        //"OlegUserdirectoryBundle:ObjectTypeText"
//        $entityNamespaceArr = explode("\\",$entityNamespace);
//        if( count($entityNamespaceArr) > 2 ) {
//            $entityNamespaceShort = $entityNamespaceArr[0] . $entityNamespaceArr[1];
//            $entityFullName = $entityNamespaceShort . ":" . $entityName;
//        } else {
//            throw new \Exception( 'Corresponding value list namespace is invalid: '.$entityNamespace );
//        }

        $listClassName = $entityNamespace."\\".$entityName;
        $newListElement = new $listClassName();
        //$newListElement = new ObjectTypeText();
        $creator = $this->sc->getToken()->getUser();
        $name = "";
        $count = null;
        $userSecUtil->setDefaultList($newListElement,$count,$creator,$name);

        return $newListElement;
    }

//    public function getListByType( $formNode ) {
//
//        $list = null;
//        $newListElement = null;
//
//        $formNodeType = $formNode->getObjectType();
//        //echo "formNodeType=" . $formNodeType . "<br>";
//
//        if( $formNodeType->getName()."" == "Form Field - Free Text" ) {
//            $list = $formNode->getObjectTypeText();
//            $newListElement = new ObjectTypeText();
//            $creator = $this->sc->getToken()->getUser();
//            $name = "";
//            $count = null;
//            $entityFullName = "OlegUserdirectoryBundle:ObjectTypeText";
//            $this->setDefaultList($newListElement,$count,$creator,$name,$entityFullName);
//            $this->em->persist($newListElement);
//        }
//
//        $res = array(
//            'list' => $list,
//            'newList' => $newListElement
//        );
//
//        return $res;
//    }

//    public function setDefaultList( $entity, $count, $user, $name=null, $entityFullName ) {
//
//        if( !$count ) {
//            $count = $this->getMaxId($entityFullName);
//            //echo "count=".$count."<br>";
//        }
//
//        $entity->setOrderinlist( $count );
//        $entity->setCreator( $user );
//        $entity->setCreatedate( new \DateTime() );
//        $entity->setType('user-added');
//        if( $name ) {
//            $entity->setName( trim($name) );
//        }
//        return $entity;
//    }
//
//    public function getMaxId( $entityFullName ) {
//        //echo "entityFullName=" . $entityFullName . "<br>";
//        $repository = $this->em->getRepository($entityFullName);
//        $dql =  $repository->createQueryBuilder("u");
//        $dql->select('MAX(u.id) as idMax');
//        //$dql->setMaxResults(1);
//        $res = $dql->getQuery()->getSingleResult();
//        $maxId = $res['idMax'];
//        if( !$maxId ) {
//            $maxId = 0;
//        }
//
//        return $maxId;
//    }






    public function generateFormNode() {

        $em = $this->em;
        $username = $this->container->get('security.context')->getToken()->getUser();

        //root
        $categories = array(
            'Root Form' => array(),
        );
        $count = 10;
        $level = 0;
        $count = $this->addNestedsetNodeRecursevely(null,$categories,$level,$username,$count);
        $rootNode = $em->getRepository('OlegUserdirectoryBundle:FormNode')->findOneByName('Root Form');
        //echo "rootNode=".$rootNode."<br>";

//        $objectTypeForm = $this->getObjectTypeByName('Form');
//        $objectTypeSection = $this->getObjectTypeByName('Form Section');
//        $objectTypeText = $this->getObjectTypeByName('Form Field - Free Text');
//        $objectTypeString = $this->getObjectTypeByName('Form Field - Free Text, Single Line');

        // Pathology Call Log Entry
        $PathologyCallLogEntry = $this->createPathologyCallLogEntryFormNode($rootNode);
        //echo "PathologyCallLogEntry=".$PathologyCallLogEntry."<br>";

        // Transfusion Medicine
        $TransfusionMedicine = $this->createTransfusionMedicine($rootNode);
        //echo "TransfusionMedicine=".$TransfusionMedicine."<br>";


//        $PathologyCallLogEntry = array(
//            array('Pathology Call Log Entry' => 'Form') => array(
//                array(
//                    array('History/Findings'=>'Form Section') => array(
//                        array('History Text'=>'Form Field - Free Text')
//                    ),
//                    array('Impression/Outcome'=>'Form Section') => array(
//                        array('History Text'=>'Form Field - Free Text')
//                    )
//                ),
//            )
//        );

//        array(
//                    'Form', //objectType [0]
//                    'History/Findings' => array(
//                        'Form Section', //objectType [0]
//                        array('History Text'=>'Form Field - Free Text')
//                    )
//                ),
//            ),
//        );
//        $count = 10;
//        $level = 0;
//        $count = $this->addNestedsetNodeRecursevely(null,$categories,$level,$username,$count);



        //exit('EOF message category');

        return round($count/10);
    }


    public function createFormNode( $params ) {
        $em = $this->em;
        $userSecUtil = $this->container->get('user_security_utility');
        $username = $this->container->get('security.context')->getToken()->getUser();

        $objectType = $params['objectType'];
        $showLabel = $params['showLabel'];
        $name = $params['name'];
        $parent = $params['parent'];

        //placeholder
        if( array_key_exists('placeholder', $params) ) {
            $placeholder = $params['placeholder'];
        } else {
            $placeholder = null;
        }

        //visible
        if( array_key_exists('visible', $params) ) {
            $visible = $params['visible'];
        } else {
            $visible = true;
        }

        //classNamespace
        if( array_key_exists('classNamespace', $params) ) {
            $classNamespace = $params['classNamespace'];
        } else {
            $classNamespace = null;
        }

        //className
        if( array_key_exists('className', $params) ) {
            $className = $params['className'];
        } else {
            $className = null;
        }

        //classObject
        if( array_key_exists('classObject', $params) ) {
            $classObject = $params['classObject'];
        } else {
            $classObject = null;
        }

        //objectTypeList
//        if( array_key_exists('objectTypeList', $params) ) {
//            $objectTypeList = $params['objectTypeList'];
//        } else {
//            $objectTypeList = null;
//        }

        //find by name and by parent ($parent) if exists
        if( $parent ) {
            $mapper = array(
                'prefix' => "Oleg",
                'className' => "FormNode",
                'bundleName' => "UserdirectoryBundle"
            );
            $node = $em->getRepository('OlegUserdirectoryBundle:FormNode')->findByChildnameAndParent($name,$parent,$mapper);
        } else {
            $node = $em->getRepository('OlegUserdirectoryBundle:FormNode')->findOneByName($name);
        }

        if( !$node ) {
            $node = new FormNode();

            $userSecUtil->setDefaultList($node,null,$username,$name);

            //set level
            $parentLevel = intval($parent->getLevel());
            $level = $parentLevel + 1;
            $node->setLevel($level);

            //set objectType
            if( $objectType ) {
                if( !$node->getObjectType() ) {
                    $node->setObjectType($objectType);
                }
            }

            //set showLabel
            $node->setShowLabel($showLabel);

            //set placeholder
            if( $placeholder) {
                $node->setPlaceholder($placeholder);
            }

            //set visible
            $node->setVisible($visible);

            //set parent
            if( $parent ) {
                $em->persist($parent);
                $parent->addChild($node);
            }

            if( $classNamespace && $className ) {
                $node->setEntityNamespace($classNamespace);
                $node->setEntityName($className);
            }

            if( $classObject ) {
                $node->setObject($classObject);
            }

            echo "Created: ".$node->getName()."<br>";
            $em->persist($parent);
            $em->persist($node);
            $em->flush();

        } else {

            //disable all below updates when finished
            //return $node;

            $updated = false;
            echo "Existed: ".$node->getName()."<br>";
            echo "objectType=".$objectType->getName()."<br>";

            //set objectType
            if( $objectType ) {
                if( !$node->getObjectType() ) {
                    $node->setObjectType($objectType);
                    echo "update objectType=".$node->getObjectType()."<br>";
                    $updated = true;
                }
            }

            if( $classNamespace && $className ) {
                $node->setEntityNamespace($classNamespace);
                $node->setEntityName($className);
                echo "set className $classNamespace $className <br>";
                $updated = true;
            } else {
                $node->setEntityNamespace(null);
                $node->setEntityName(null);
                echo "set NULL EntityName <br>";
                $updated = true;
            }

            if( $classObject ) {
                echo "set  classObject=".$classObject." <br>";
                $node->setObject($classObject);
                $updated = true;
            }

            //pre-set
            if(0) {
                $node->setEntityNamespace(null);
                $node->setEntityName(null);
                $node->setEntityId(null);

                $node->setReceivedValueEntityNamespace(null);
                $node->setReceivedValueEntityName(null);
                $node->setReceivedValueEntityId(null);
                $updated = true;
            }

            if( $updated ) {
                echo "update node=".$node." <br>";
                $em->persist($node);
                $em->flush($node);
            }

        }//if !$node

        return $node;
    }

    public function addNestedsetNodeRecursevely($parentCategory,$categories,$level,$username,$count) {

        $userSecUtil = $this->container->get('user_security_utility');
        $em = $this->em;

        foreach( $categories as $category=>$subcategory ) {

            $name = $category;

            if( $subcategory && !is_array($subcategory) ) {
                $name = $subcategory;
            }

            //find by name and by parent ($parentCategory) if exists
            if( $parentCategory ) {
                $mapper = array(
                    'prefix' => "Oleg",
                    'className' => "FormNode",
                    'bundleName' => "UserdirectoryBundle"
                );
                $node = $em->getRepository('OlegUserdirectoryBundle:FormNode')->findByChildnameAndParent($name,$parentCategory,$mapper);
            } else {
                $node = $em->getRepository('OlegUserdirectoryBundle:FormNode')->findOneByName($name);
            }

            if( !$node ) {
                //make category
                $node = new FormNode();

                $userSecUtil->setDefaultList($node,$count,$username,$name);
                $node->setLevel($level);

//                //try to get default group by level
//                if( !$node->getOrganizationalGroupType() ) {
//                    if( $node->getLevel() ) {
//                        $messageTypeClassifier = $em->getRepository('OlegOrderformBundle:MessageTypeClassifiers')->findOneByLevel($node->getLevel());
//                        if ($messageTypeClassifier) {
//                            $node->setOrganizationalGroupType($messageTypeClassifier);
//                        }
//                    }
//                }

                $count = $count + 10;
            }

//            echo $level.": category=".$name.", count=".$count."<br>";
//            echo "subcategory:<br>";
//            print_r($subcategory);
//            echo "<br><br>";
//            echo "messageCategory=".$node->getName()."<br>";

            //add to parent
            if( $parentCategory ) {
                $em->persist($parentCategory);
                $parentCategory->addChild($node);
            }

            //$node->printTree();

            //make children
            if( $subcategory && is_array($subcategory) && count($subcategory) > 0 ) {
                $count = $this->addNestedsetNodeRecursevely($node,$subcategory,$level+1,$username,$count);
            }

            //testing
            if( 1 ) {
                $label = null;
                if ($node->getObjectType()) {
                    $label = $node->getObjectType()->getName();
                } else {
                    $label = null;
                }
                if ($node->getParent()) {
                    $parent = $node->getParent()->getName();
                } else {
                    $parent = null;
                }
                echo $node.": label=".$label."; level=".$node->getLevel()."; parent=".$parent."<br>";
            }

            $em->persist($node);
            $em->flush();
        }

        return $count;
    }
    public function getObjectTypeByName($objectTypeName) {
        $em = $this->em;
        $objectType = $em->getRepository('OlegUserdirectoryBundle:ObjectTypeList')->findOneByName($objectTypeName);
        if( !$objectType ) {
            throw new \Exception( "ObjectType not found by ".$objectTypeName );
        }
        return $objectType;
    }

    public function setFormNodeToMessageCategory($messageCategoryName,$formNodes,$parentMessageCategoryName=null) {
        //attach this formnode to the MessageCategory "Transfusion Medicine"
        $em = $this->em;
        $messageCategory = $em->getRepository('OlegOrderformBundle:MessageCategory')->findOneByName($messageCategoryName);
        if( !$messageCategory ) {
            exit("Message category not found by name=".$messageCategoryName);
        }
        foreach( $formNodes as $formNode ) {
            //if( !$messageCategory->getFormNode() ) {
            if( $formNode && !$messageCategory->getFormNodes()->contains($formNode) ) {
                $messageCategory->addFormNode($formNode);
                $em->persist($messageCategory);
                //$em->persist($formNode);
                $em->flush();
                echo "Add " . $formNode . " to " . $messageCategory . "<br>";
            }
        }
    }



    public function createPathologyCallLogEntryFormNode($parent) {

        $objectTypeForm = $this->getObjectTypeByName('Form');
        $objectTypeSection = $this->getObjectTypeByName('Form Section');
        $objectTypeText = $this->getObjectTypeByName('Form Field - Free Text');
        echo "objectTypeForm=".$objectTypeForm."<br>";

        $messageCategoryName = "Pathology Call Log Entry";

        $formParams = array(
            'parent' => $parent,
            'name' => $messageCategoryName,
            'objectType' => $objectTypeForm,
            'showLabel' => false,
            'visible' => false
        );
        $PathologyCallLogEntry = $this->createFormNode($formParams);

        //History/Findings (Section)
        $formParams = array(
            'parent' => $PathologyCallLogEntry,
            'name' => "History/Findings",
            'objectType' => $objectTypeSection,
            'showLabel' => true,
            'visible' => true
        );
        $historySection = $this->createFormNode($formParams);

        //History/Findings Text
        $formParams = array(
            'parent' => $historySection,
            'name' => "History/Findings Text",
            'placeholder' => "History/Findings Text",
            'objectType' => $objectTypeText,
            'showLabel' => false,
            'visible' => true
        );
        $historyText = $this->createFormNode($formParams);

        //Impression/Outcome (Section)
        $formParams = array(
            'parent' => $PathologyCallLogEntry,
            'name' => "Impression/Outcome",
            'objectType' => $objectTypeSection,
            'showLabel' => true,
            'visible' => true
        );
        $impressionSection = $this->createFormNode($formParams);

        //Impression/Outcome Text
        $formParams = array(
            'parent' => $impressionSection,
            'name' => "Impression/Outcome Text",
            'placeholder' => "Impression/Outcome Text",
            'objectType' => $objectTypeText,
            'showLabel' => false,
            'visible' => true
        );
        $impressionText = $this->createFormNode($formParams);

        //attach this formnode to the MessageCategory "Transfusion Medicine"
        $this->setFormNodeToMessageCategory($messageCategoryName,array($historyText,$impressionText));

        return $PathologyCallLogEntry;
    }

    public function createTransfusionMedicine($parent) {

        $objectTypeForm = $this->getObjectTypeByName('Form');
        $objectTypeSection = $this->getObjectTypeByName('Form Section');
        //$objectTypeFieldGroup = $this->getObjectTypeByName('Field Group');
        $objectTypeText = $this->getObjectTypeByName('Form Field - Free Text');
        $objectTypeString = $this->getObjectTypeByName('Form Field - Free Text, Single Line');
        $objectTypeDropdown = $this->getObjectTypeByName('Form Field - Dropdown Menu');
        $objectTypeDropdownValue = $this->getObjectTypeByName('Dropdown Menu Value');
        $objectTypeDate = $this->getObjectTypeByName('Form Field - Date');
        $objectTypeFullDate = $this->getObjectTypeByName('Form Field - Full Date');
        $objectTypeFullDateTime = $this->getObjectTypeByName('Form Field - Full Date and Time');

        $messageCategoryName = "Transfusion Medicine";

        //Transfusion Medicine (Form)
        $formParams = array(
            'parent' => $parent,
            'name' => $messageCategoryName,
            'objectType' => $objectTypeForm,
            'showLabel' => false,
            'visible' => false
        );
        $transfusionMedicine = $this->createFormNode($formParams);

        ////////////// Laboratory Values [Form Section] //////////////////
        $formParams = array(
            'parent' => $transfusionMedicine,
            'name' => "Laboratory Values",
            'objectType' => $objectTypeSection,
            'showLabel' => true,
            'visible' => true
        );
        $laboratoryValues = $this->createFormNode($formParams);

            //Hemoglobin: [Form Field - Free Text, Single Line]
            $formParams = array(
                'parent' => $laboratoryValues,
                'name' => "Hemoglobin",
                'objectType' => $objectTypeString,
                'showLabel' => true,
                'visible' => true
            );
            $HemoglobinString = $this->createFormNode($formParams);

            //Platelets: [Form Field - Free Text, Single Line]
            $formParams = array(
                'parent' => $laboratoryValues,
                'name' => "Platelets",
                'objectType' => $objectTypeString,
                'showLabel' => true,
                'visible' => true
            );
            $PlateletsString = $this->createFormNode($formParams);
        ////////////// EOF Laboratory Values [Form Section] //////////////////

        //attach this formnode to the MessageCategory "Transfusion Medicine"
        $this->setFormNodeToMessageCategory($messageCategoryName,array($HemoglobinString,$PlateletsString));


        //////////////////////////////////////////////////////
        //////// Transfusion Medicine -> First dose plasma [Message Category]
        //$formSectionArr = array();
        $messageCategoryName = "First dose plasma";

        //INR: [Form Field - Free Text, Single Line]
        $formParams = array(
            'parent' => $laboratoryValues,
            'name' => "INR",
            'placeholder' => "INR",
            'objectType' => $objectTypeString,
            'showLabel' => true,
            'visible' => true
        );
        //$formSectionArr[] = $formParams;
        $INRString = $this->createFormNode($formParams);

        //PT: [Form Field - Free Text, Single Line]
        $formParams = array(
            'parent' => $laboratoryValues,
            'name' => "PT",
            'placeholder' => "PT",
            'objectType' => $objectTypeString,
            'showLabel' => true,
            'visible' => true
        );
        //$formSectionArr[] = $formParams;
        $PTString = $this->createFormNode($formParams);

        //PTT: [Form Field - Free Text, Single Line]
        $formParams = array(
            'parent' => $laboratoryValues,
            'name' => "PTT",
            'placeholder' => "PTT",
            'objectType' => $objectTypeString,
            'showLabel' => true,
            'visible' => true
        );
        //$formSectionArr[] = $formParams;
        $PTTString = $this->createFormNode($formParams);

        //attach this formnodes to the MessageCategory
        $this->setFormNodeToMessageCategory($messageCategoryName,array($INRString,$PTString,$PTTString));

        //////////////////////////////////////////////////////
        //Transfusion Medicine -> First dose platelets [Message Category]
        $messageCategoryName = "First dose platelets";

        //Miscellaneous [Form Section]
        $formParams = array(
            'parent' => $transfusionMedicine,
            'name' => "Miscellaneous",
            'objectType' => $objectTypeSection,
            'showLabel' => true,
            'visible' => true
        );
        $miscellaneous = $this->createFormNode($formParams);

        //Medication: [Form Field - Free Text, Single Line]
        $formParams = array(
            'parent' => $miscellaneous,
            'name' => "Medication",
            'placeholder' => "Medication",
            'objectType' => $objectTypeString,
            'showLabel' => true,
            'visible' => true
        );
        $MedicationString = $this->createFormNode($formParams);

        //attach this formnodes to the MessageCategory
        $this->setFormNodeToMessageCategory($messageCategoryName,array($MedicationString));


        //////////////////////////////////////////////////////
        //Transfusion Medicine -> Third+ dose platelets [Message Category]
        $messageCategoryName = "Third+ dose platelets";

        //Laboratory Values [Form Section]
        //CCI: [Form Field - Free Text, Single Line]
        $formParams = array(
            'parent' => $laboratoryValues,
            'name' => "CCI",
            'placeholder' => "CCI",
            'objectType' => $objectTypeString,
            'showLabel' => true,
            'visible' => true
        );
        $CCIString = $this->createFormNode($formParams);

        //Miscellaneous [Form Section]
        //Platelet Goal: [Form Field - Free Text, Single Line]
        $formParams = array(
            'parent' => $miscellaneous,
            'name' => "Platelet Goal",
            'placeholder' => "Platelet Goal",
            'objectType' => $objectTypeString,
            'showLabel' => true,
            'visible' => true
        );
        $PlateletGoalString = $this->createFormNode($formParams);

        //attach this formnodes to the MessageCategory
        $this->setFormNodeToMessageCategory($messageCategoryName,array($CCIString,$PlateletGoalString));


        //////////////////////////////////////////////////////
        //Transfusion Medicine -> Cryoprecipitate [Message Category]
        $messageCategoryName = "Cryoprecipitate";
        //Laboratory Values [Form Section]
        //INR: [Form Field - Free Text, Single Line]
        $formParams = array(
            'parent' => $laboratoryValues,
            'name' => "INR",
            'placeholder' => "INR",
            'objectType' => $objectTypeString,
            'showLabel' => true,
            'visible' => true
        );
        $INRString = $this->createFormNode($formParams);

        //PT: [Form Field - Free Text, Single Line]
        $formParams = array(
            'parent' => $laboratoryValues,
            'name' => "PT",
            'placeholder' => "PT",
            'objectType' => $objectTypeString,
            'showLabel' => true,
            'visible' => true
        );
        $PTString = $this->createFormNode($formParams);

        //PTT: [Form Field - Free Text, Single Line]
        $formParams = array(
            'parent' => $laboratoryValues,
            'name' => "PTT",
            'placeholder' => "PTT",
            'objectType' => $objectTypeString,
            'showLabel' => true,
            'visible' => true
        );
        $PTTString = $this->createFormNode($formParams);

        //Fibrinogen: [Form Field - Free Text, Single Line]
        $formParams = array(
            'parent' => $laboratoryValues,
            'name' => "Fibrinogen",
            'placeholder' => "Fibrinogen",
            'objectType' => $objectTypeString,
            'showLabel' => true,
            'visible' => true
        );
        $FibrinogenString = $this->createFormNode($formParams);

        //attach this formnodes to the MessageCategory
        $this->setFormNodeToMessageCategory($messageCategoryName,array($INRString,$PTString,$PTTString,$FibrinogenString));


        //////////////////////////////////////////////////////
        //Transfusion Medicine -> MTP [Message Category]
        $messageCategoryName = "MTP";
        //Laboratory Values [Form Section]
        //INR: [Form Field - Free Text, Single Line]
        //PT: [Form Field - Free Text, Single Line]
        //PTT: [Form Field - Free Text, Single Line]
        //Fibrinogen: [Form Field - Free Text, Single Line]
        //attach this formnodes to the MessageCategory
        $this->setFormNodeToMessageCategory($messageCategoryName,array($INRString,$PTString,$PTTString,$FibrinogenString));

        //////////////////////////////////////////////////////
        //Transfusion Medicine -> Emergency release [Message Category]
        $messageCategoryName = "Emergency release";
        //Miscellaneous [Form Section]
        //Blood Type of Unit: [Form Field - Free Text, Single Line]
        $formParams = array(
            'parent' => $miscellaneous,
            'name' => "Blood Type of Unit",
            'placeholder' => "Blood Type of Unit",
            'objectType' => $objectTypeString,
            'showLabel' => true,
            'visible' => true
        );
        $BloodTypeofUnitString = $this->createFormNode($formParams);
        //Blood Type of Patient: [Form Field - Free Text, Single Line]
        $formParams = array(
            'parent' => $miscellaneous,
            'name' => "Blood Type of Patient",
            'placeholder' => "Blood Type of Patient",
            'objectType' => $objectTypeString,
            'showLabel' => true,
            'visible' => true
        );
        $BloodTypeofPatientString = $this->createFormNode($formParams);
        //attach this formnodes to the MessageCategory
        $this->setFormNodeToMessageCategory($messageCategoryName,array($BloodTypeofUnitString,$BloodTypeofPatientString));

        //////////////////////////////////////////////////////
        //Transfusion Medicine -> Payson transfusion [Message Category]
        //Transfusion Medicine -> Incompatible crossmatch [Message Category]
        //Miscellaneous [Form Section]
        //Blood Type of Unit: [Form Field - Free Text, Single Line]
        //Blood Type of Patient: [Form Field - Free Text, Single Line]
        //Antibodies: [Form Field - Free Text, Single Line]
        $formParams = array(
            'parent' => $miscellaneous,
            'name' => "Antibodies",
            'placeholder' => "Antibodies",
            'objectType' => $objectTypeString,
            'showLabel' => true,
            'visible' => true
        );
        $AntibodiesString = $this->createFormNode($formParams);
        //Phenotype: [Form Field - Free Text, Single Line]
        $formParams = array(
            'parent' => $miscellaneous,
            'name' => "Phenotype",
            'placeholder' => "Phenotype",
            'objectType' => $objectTypeString,
            'showLabel' => true,
            'visible' => true
        );
        $PhenotypeString = $this->createFormNode($formParams);
        //Incompatibility: [Form Field - Free Text, Single Line]
        $formParams = array(
            'parent' => $miscellaneous,
            'name' => "Incompatibility",
            'placeholder' => "Incompatibility",
            'objectType' => $objectTypeString,
            'showLabel' => true,
            'visible' => true
        );
        $IncompatibilityString = $this->createFormNode($formParams);
        //attach this formnodes to the MessageCategory
        $this->setFormNodeToMessageCategory("Payson transfusion",array($BloodTypeofUnitString,$BloodTypeofPatientString,$AntibodiesString,$PhenotypeString,$IncompatibilityString));
        //attach this formnodes to the MessageCategory
        $this->setFormNodeToMessageCategory("Incompatible crossmatch",array($BloodTypeofUnitString,$BloodTypeofPatientString,$AntibodiesString,$PhenotypeString,$IncompatibilityString));


        //////////////////////////////////////////////////////
        //Transfusion Medicine -> Transfusion reaction [Message Category]
        //Miscellaneous [Form Section]
        //Blood Product Transfused [Dropdown Menu Value List]
        //$objectTypeDropdown = $this->getObjectTypeByName('Form Field - Dropdown Menu'); //,'ObjectTypeBloodProductTransfused');
        $formParams = array(
            'parent' => $miscellaneous,
            'name' => "Blood Product Transfused",
            'placeholder' => "Blood Product Transfused",
            'objectType' => $objectTypeDropdown,
            'showLabel' => true,
            'visible' => true,
            'classNamespace' => "Oleg\\UserdirectoryBundle\\Entity",
            'className' => "BloodProductTransfusedList"
        );
        $BloodProductTransfused = $this->createFormNode($formParams);
        //attach this formnodes to the MessageCategory
        $this->setFormNodeToMessageCategory("Transfusion reaction",array($BloodProductTransfused));

        //Transfusion Reaction Type [Dropdown Menu Value List]
        $formParams = array(
            'parent' => $miscellaneous,
            'name' => "Transfusion Reaction Type",
            'placeholder' => "Transfusion Reaction Type",
            'objectType' => $objectTypeDropdown,
            'showLabel' => true,
            'visible' => true,
            'classNamespace' => "Oleg\\UserdirectoryBundle\\Entity",
            'className' => "TransfusionReactionTypeList"
        );
        $TransfusionReactionType = $this->createFormNode($formParams);
        //attach this formnodes to the MessageCategory
        $this->setFormNodeToMessageCategory("Transfusion reaction",array($TransfusionReactionType));

        //        Vitals [Form Section]
        $formParams = array(
            'parent' => $transfusionMedicine,
            'name' => "Vitals",
            'placeholder' => "",
            'objectType' => $objectTypeSection,
            'showLabel' => true,
            'visible' => true
        );
        $VitalsSection = $this->createFormNode($formParams);
        $VitalsArr = array();
        //    Pre-Temp: [Form Field - Free Text, Single Line]
        $formParams = array(
            'parent' => $VitalsSection,
            'name' => "Pre-Temp",
            'placeholder' => "Pre-Temp",
            'objectType' => $objectTypeString,
            'showLabel' => true,
            'visible' => true
        );
        $PreTemp = $this->createFormNode($formParams);
        $VitalsArr[] = $PreTemp;
        //    Pre-HR: [Form Field - Free Text, Single Line]
        $formParams = array(
            'parent' => $VitalsSection,
            'name' => "Pre-HR",
            'placeholder' => "Pre-HR",
            'objectType' => $objectTypeString,
            'showLabel' => true,
            'visible' => true
        );
        $PreHR = $this->createFormNode($formParams);
        $VitalsArr[] = $PreHR;
        //    Pre-RR: [Form Field - Free Text, Single Line]
        $formParams = array(
            'parent' => $VitalsSection,
            'name' => "Pre-RR",
            'placeholder' => "Pre-RR",
            'objectType' => $objectTypeString,
            'showLabel' => true,
            'visible' => true
        );
        $PreRR = $this->createFormNode($formParams);
        $VitalsArr[] = $PreRR;
        //    Pre-O2 sat: [Form Field - Free Text, Single Line]
        $formParams = array(
            'parent' => $VitalsSection,
            'name' => "Pre-O2",
            'placeholder' => "Pre-O2",
            'objectType' => $objectTypeString,
            'showLabel' => true,
            'visible' => true
        );
        $PreO2 = $this->createFormNode($formParams);
        $VitalsArr[] = $PreO2;
        //    Pre-BP: [Form Field - Free Text, Single Line]
        $formParams = array(
            'parent' => $VitalsSection,
            'name' => "Pre-BP",
            'placeholder' => "Pre-BP",
            'objectType' => $objectTypeString,
            'showLabel' => true,
            'visible' => true
        );
        $PreBP = $this->createFormNode($formParams);
        $VitalsArr[] = $PreBP;

        //    Post-Temp: [Form Field - Free Text, Single Line]
        $formParams = array(
            'parent' => $VitalsSection,
            'name' => "Post-Temp",
            'placeholder' => "Post-Temp",
            'objectType' => $objectTypeString,
            'showLabel' => true,
            'visible' => true
        );
        $PostTemp = $this->createFormNode($formParams);
        $VitalsArr[] = $PostTemp;
        //    Post-HR: [Form Field - Free Text, Single Line]
        $formParams = array(
            'parent' => $VitalsSection,
            'name' => "Post-HR",
            'placeholder' => "Post-HR",
            'objectType' => $objectTypeString,
            'showLabel' => true,
            'visible' => true
        );
        $PostHR = $this->createFormNode($formParams);
        $VitalsArr[] = $PostHR;
        //    Post-RR: [Form Field - Free Text, Single Line]
        $formParams = array(
            'parent' => $VitalsSection,
            'name' => "Post-RR",
            'placeholder' => "Post-RR",
            'objectType' => $objectTypeString,
            'showLabel' => true,
            'visible' => true
        );
        $PostRR = $this->createFormNode($formParams);
        $VitalsArr[] = $PostRR;
        //    Post-O2 sat: [Form Field - Free Text, Single Line]
        $formParams = array(
            'parent' => $VitalsSection,
            'name' => "Post-O2",
            'placeholder' => "Post-O2",
            'objectType' => $objectTypeString,
            'showLabel' => true,
            'visible' => true
        );
        $PostO2 = $this->createFormNode($formParams);
        $VitalsArr[] = $PostO2;
        //    Post-BP: [Form Field - Free Text, Single Line]
        $formParams = array(
            'parent' => $VitalsSection,
            'name' => "Post-BP",
            'placeholder' => "Post-BP",
            'objectType' => $objectTypeString,
            'showLabel' => true,
            'visible' => true
        );
        $PostBP = $this->createFormNode($formParams);
        $VitalsArr[] = $PostBP;
        //attach this formnodes to the MessageCategory
        $this->setFormNodeToMessageCategory("Transfusion reaction",$VitalsArr);

        //Transfusion Reaction Workup [Form Section]
        $formParams = array(
            'parent' => $transfusionMedicine,
            'name' => "Transfusion Reaction Workup",
            'placeholder' => "",
            'objectType' => $objectTypeSection,
            'showLabel' => true,
            'visible' => true
        );
        $TransfusionReactionWorkupSection = $this->createFormNode($formParams);
        $TransfusionReactionWorkupSectionArr = array();
        //    Transfusion Reaction Workup Description [Form Field - Free Text]
        $formParams = array(
            'parent' => $TransfusionReactionWorkupSection,
            'name' => "Transfusion Reaction Workup Description",
            'placeholder' => "Transfusion Reaction Workup Description",
            'objectType' => $objectTypeText,
            'showLabel' => true,
            'visible' => true
        );
        $TransfusionReactionWorkupDescription = $this->createFormNode($formParams);
        $TransfusionReactionWorkupSectionArr[] = $TransfusionReactionWorkupDescription;

        //    Clerical error: [Form Field - Dropdown Menu]
        $formParams = array(
            'parent' => $TransfusionReactionWorkupSection,
            'name' => "Clerical error",
            'placeholder' => "Clerical error",
            'objectType' => $objectTypeDropdown,
            'showLabel' => true,
            'visible' => true,
            //'classNamespace' => "Oleg\\UserdirectoryBundle\\Entity",
            //'className' => "ClericalErrorList"
        );
        $ClericalerrorDropdowm = $this->createFormNode($formParams);
        //attach this formnodes to the MessageCategory
        $this->setFormNodeToMessageCategory("Transfusion reaction",array($ClericalerrorDropdowm));
        //        Transfusion Reaction Clerical Error Type [Dropdown Menu Value List]
        //            Yes [Dropdown Menu Value]
        $formParams = array(
            'parent' => $ClericalerrorDropdowm,
            'name' => "Yes",
            'placeholder' => "Yes",
            'objectType' => $objectTypeDropdownValue,
            'showLabel' => true,
            'visible' => true,
        );
        $ClericalerrorDropdowmYes = $this->createFormNode($formParams);
        //            None [Dropdown Menu Value]
        $formParams = array(
            'parent' => $ClericalerrorDropdowm,
            'name' => "None",
            'placeholder' => "None",
            'objectType' => $objectTypeDropdownValue,
            'showLabel' => true,
            'visible' => true,
        );
        $ClericalerrorDropdowmNone = $this->createFormNode($formParams);

        //    Blood type of unit: [Form Field - Dropdown Menu]
        //        Blood Types [Dropdown Menu Value List]
        //            A+ [Dropdown Menu Value]
        //            A- [Dropdown Menu Value]
        //            B+ [Dropdown Menu Value]
        //            B- [Dropdown Menu Value]
        //            O+ [Dropdown Menu Value]
        //            O- [Dropdown Menu Value]
        $formParams = array(
            'parent' => $TransfusionReactionWorkupSection,
            'name' => "Blood type of unit",
            'placeholder' => "Blood type of unit",
            'objectType' => $objectTypeDropdown,
            'showLabel' => true,
            'visible' => true,
            'classNamespace' => "Oleg\\UserdirectoryBundle\\Entity",
            'className' => "BloodTypeList"
        );
        $BloodtypeofunitDropdowm = $this->createFormNode($formParams);
        //attach this formnodes to the MessageCategory
        $this->setFormNodeToMessageCategory("Transfusion reaction",array($BloodtypeofunitDropdowm));

        //    Blood type of pre-transfusion specimen: [Form Field - Dropdown Menu]
        //        Blood Types [Dropdown Menu Value List] SAME LIST AS ABOVE, DO NOT DUPLICATE, just link to it via Link to List ID
        $formParams = array(
            'parent' => $TransfusionReactionWorkupSection,
            'name' => "Blood type of pre-transfusion specimen",
            'placeholder' => "Blood type of pre-transfusion specimen",
            'objectType' => $objectTypeDropdown,
            'showLabel' => true,
            'visible' => true,
            'classNamespace' => "Oleg\\UserdirectoryBundle\\Entity",
            'className' => "BloodTypeList"
        );
        $BloodtypeofunitSpecimenDropdowm = $this->createFormNode($formParams);
        //attach this formnodes to the MessageCategory
        $this->setFormNodeToMessageCategory("Transfusion reaction",array($BloodtypeofunitSpecimenDropdowm));

        //    Blood type of post-transfusion specimen: [Form Field - Dropdown Menu]
        //        Blood Types [Dropdown Menu Value List] SAME LIST AS ABOVE, DO NOT DUPLICATE, just link to it via Link to List ID
        $formParams = array(
            'parent' => $TransfusionReactionWorkupSection,
            'name' => "Blood type of post-transfusion specimen",
            'placeholder' => "Blood type of post-transfusion specimen",
            'objectType' => $objectTypeDropdown,
            'showLabel' => true,
            'visible' => true,
            'classNamespace' => "Oleg\\UserdirectoryBundle\\Entity",
            'className' => "BloodTypeList"
        );
        $BloodtypeofunitPostTransfusionSpecimenDropdowm = $this->createFormNode($formParams);
        //attach this formnodes to the MessageCategory
        $this->setFormNodeToMessageCategory("Transfusion reaction",array($BloodtypeofunitPostTransfusionSpecimenDropdowm));

        //    Pre-transfusion antibody screen: [Form Field - Dropdown Menu]
        //        Transfusion antibody screen results [Dropdown Menu Value List]
        //            Positive [Dropdown Menu Value]
        //            Negative [Dropdown Menu Value]
        $formParams = array(
            'parent' => $TransfusionReactionWorkupSection,
            'name' => "Pre-transfusion antibody screen",
            'placeholder' => "Pre-transfusion antibody screen",
            'objectType' => $objectTypeDropdown,
            'showLabel' => true,
            'visible' => true,
            'classNamespace' => "Oleg\\UserdirectoryBundle\\Entity",
            'className' => "TransfusionAntibodyScreenResultsList"
        );
        $PretransfusionAntibodyScreenDropdowm = $this->createFormNode($formParams);
        //attach this formnodes to the MessageCategory
        $this->setFormNodeToMessageCategory("Transfusion reaction",array($PretransfusionAntibodyScreenDropdowm));

        //    Post-transfusion antibody screen: [Form Field - Dropdown Menu]
        $formParams = array(
            'parent' => $TransfusionReactionWorkupSection,
            'name' => "Post-transfusion antibody screen",
            'placeholder' => "Post-transfusion antibody screen",
            'objectType' => $objectTypeDropdown,
            'showLabel' => true,
            'visible' => true,
            'classNamespace' => "Oleg\\UserdirectoryBundle\\Entity",
            'className' => "TransfusionAntibodyScreenResultsList"
        );
        $PosttransfusionAntibodyScreenDropdowm = $this->createFormNode($formParams);
        //attach this formnodes to the MessageCategory
        $this->setFormNodeToMessageCategory("Transfusion reaction",array($PosttransfusionAntibodyScreenDropdowm));

        //    Pre-transfusion DAT: [Form Field - Dropdown Menu]
        //        Transfusion DAT results [Dropdown Menu Value List]
        //            Positive [Dropdown Menu Value]
        //            Negative [Dropdown Menu Value]
        $formParams = array(
            'parent' => $TransfusionReactionWorkupSection,
            'name' => "Pre-transfusion DAT",
            'placeholder' => "Pre-transfusion DAT",
            'objectType' => $objectTypeDropdown,
            'showLabel' => true,
            'visible' => true,
            'classNamespace' => "Oleg\\UserdirectoryBundle\\Entity",
            'className' => "TransfusionDATResultsList"
        );
        $TransfusionDATDropdowm = $this->createFormNode($formParams);
        //attach this formnodes to the MessageCategory
        $this->setFormNodeToMessageCategory("Transfusion reaction",array($TransfusionDATDropdowm));

        //    Post-transfusion DAT: [Form Field - Dropdown Menu]
        $formParams = array(
            'parent' => $TransfusionReactionWorkupSection,
            'name' => "Post-transfusion DAT",
            'placeholder' => "Post-transfusion DAT",
            'objectType' => $objectTypeDropdown,
            'showLabel' => true,
            'visible' => true,
            'classNamespace' => "Oleg\\UserdirectoryBundle\\Entity",
            'className' => "TransfusionDATResultsList"
        );
        $PostTransfusionDATDropdowm = $this->createFormNode($formParams);
        //attach this formnodes to the MessageCategory
        $this->setFormNodeToMessageCategory("Transfusion reaction",array($PostTransfusionDATDropdowm));

        //    Pre-transfusion crossmatch: [Form Field - Dropdown Menu]
        $formParams = array(
            'parent' => $TransfusionReactionWorkupSection,
            'name' => "Pre-transfusion crossmatch",
            'placeholder' => "Pre-transfusion crossmatch",
            'objectType' => $objectTypeDropdown,
            'showLabel' => true,
            'visible' => true,
            'classNamespace' => "Oleg\\UserdirectoryBundle\\Entity",
            'className' => "TransfusionCrossmatchResultsList"
        );
        $PreTransfusionCrossmatchDropdowm = $this->createFormNode($formParams);
        //attach this formnodes to the MessageCategory
        $this->setFormNodeToMessageCategory("Transfusion reaction",array($PreTransfusionCrossmatchDropdowm));

        //    Post-transfusion crossmatch: [Form Field - Dropdown Menu]
        $formParams = array(
            'parent' => $TransfusionReactionWorkupSection,
            'name' => "Post-transfusion crossmatch",
            'placeholder' => "Post-transfusion crossmatch",
            'objectType' => $objectTypeDropdown,
            'showLabel' => true,
            'visible' => true,
            'classNamespace' => "Oleg\\UserdirectoryBundle\\Entity",
            'className' => "TransfusionCrossmatchResultsList"
        );
        $PostTransfusionCrossmatchDropdowm = $this->createFormNode($formParams);
        //attach this formnodes to the MessageCategory
        $this->setFormNodeToMessageCategory("Transfusion reaction",array($PostTransfusionCrossmatchDropdowm));

        //    Pre-transfusion hemolysis check: [Form Field - Dropdown Menu]
        //        Transfusion hemolysis check results [Dropdown Menu Value List]
        //            Hemolysis [Dropdown Menu Value]
        //            No hemolysis [Dropdown Menu Value]
        $formParams = array(
            'parent' => $TransfusionReactionWorkupSection,
            'name' => "Pre-transfusion hemolysis check",
            'placeholder' => "Pre-transfusion hemolysis check",
            'objectType' => $objectTypeDropdown,
            'showLabel' => true,
            'visible' => true,
            'classNamespace' => "Oleg\\UserdirectoryBundle\\Entity",
            'className' => "TransfusionHemolysisCheckResultsList"
        );
        $PreTransfusionHemolysisCheckDropdowm = $this->createFormNode($formParams);
        //attach this formnodes to the MessageCategory
        $this->setFormNodeToMessageCategory("Transfusion reaction",array($PreTransfusionHemolysisCheckDropdowm));

        //    Post-transfusion hemolysis check: [Form Field - Dropdown Menu]
        //        Transfusion hemolysis check results [Dropdown Menu Value List] SAME LIST AS ABOVE, DO NOT DUPLICATE, just link to it via Link to List ID
        //            Hemolysis [Dropdown Menu Value]
        //            No hemolysis [Dropdown Menu Value]
        $formParams = array(
            'parent' => $TransfusionReactionWorkupSection,
            'name' => "Post-transfusion hemolysis check",
            'placeholder' => "Post-transfusion hemolysis check",
            'objectType' => $objectTypeDropdown,
            'showLabel' => true,
            'visible' => true,
            'classNamespace' => "Oleg\\UserdirectoryBundle\\Entity",
            'className' => "TransfusionHemolysisCheckResultsList"
        );
        $PostTransfusionHemolysisCheckDropdowm = $this->createFormNode($formParams);
        //attach this formnodes to the MessageCategory
        $this->setFormNodeToMessageCategory("Transfusion reaction",array($PostTransfusionHemolysisCheckDropdowm));

        //    Microbiology: [Form Field - Free Text, Single Line]
        $formParams = array(
            'parent' => $TransfusionReactionWorkupSection,
            'name' => "Microbiology",
            'placeholder' => "Microbiology",
            'objectType' => $objectTypeString,
            'showLabel' => true,
            'visible' => true
        );
        $Microbiology = $this->createFormNode($formParams);
        //attach this formnodes to the MessageCategory
        $this->setFormNodeToMessageCategory("Transfusion reaction",array($Microbiology));


        //Transfusion Medicine -> Complex platelet summary [Message Category]
        //Laboratory Values [Form Section]
        //    HLA A: [Form Field - Free Text, Single Line]
        $formParams = array(
            'parent' => $laboratoryValues,
            'name' => "HLA A",
            'placeholder' => "HLA A",
            'objectType' => $objectTypeString,
            'showLabel' => true,
            'visible' => true
        );
        $formNode = $this->createFormNode($formParams);
        $this->setFormNodeToMessageCategory("Complex platelet summary",array($formNode));
        //    HLA B: [Form Field - Free Text, Single Line]
        $formParams = array(
            'parent' => $laboratoryValues,
            'name' => "HLA B",
            'placeholder' => "HLA B",
            'objectType' => $objectTypeString,
            'showLabel' => true,
            'visible' => true
        );
        $formNode = $this->createFormNode($formParams);
        $this->setFormNodeToMessageCategory("Complex platelet summary",array($formNode));
        //    Rogosin PRA: [Form Field - Free Text, Single Line]
        $formParams = array(
            'parent' => $laboratoryValues,
            'name' => "Rogosin PRA",
            'placeholder' => "Rogosin PRA",
            'objectType' => $objectTypeString,
            'showLabel' => true,
            'visible' => true
        );
        $formNode = $this->createFormNode($formParams);
        $this->setFormNodeToMessageCategory("Complex platelet summary",array($formNode));
        //    Rogosin date: [Form Field - Full Date]
        $formParams = array(
            'parent' => $laboratoryValues,
            'name' => "Rogosin date",
            'placeholder' => "Rogosin date",
            'objectType' => $objectTypeFullDate,
            'showLabel' => true,
            'visible' => true
        );
        $formNode = $this->createFormNode($formParams);
        $this->setFormNodeToMessageCategory("Complex platelet summary",array($formNode));

        //    Antibodies [Form Field - Dropdown Menu]
        //        Complex platelet summary antibodies [Dropdown Menu Value List]
        //            HLA [Dropdown Menu Value]
        //            HPA [Dropdown Menu Value]
        //            None [Dropdown Menu Value]
        $formParams = array(
            'parent' => $laboratoryValues,
            'name' => "Antibodies",
            'placeholder' => "Antibodies",
            'objectType' => $objectTypeDropdown,
            'showLabel' => true,
            'visible' => true,
            'classNamespace' => "Oleg\\UserdirectoryBundle\\Entity",
            'className' => "ComplexPlateletSummaryAntibodiesList"
        );
        $PostTransfusionHemolysisCheckDropdowm = $this->createFormNode($formParams);
        //attach this formnodes to the MessageCategory
        $this->setFormNodeToMessageCategory("Complex platelet summary",array($PostTransfusionHemolysisCheckDropdowm));

        //    NYBC date: [Form Field - Full Date]
        $formParams = array(
            'parent' => $laboratoryValues,
            'name' => "NYBC date",
            'placeholder' => "NYBC date",
            'objectType' => $objectTypeFullDate,
            'showLabel' => true,
            'visible' => true
        );
        $formNode = $this->createFormNode($formParams);
        $this->setFormNodeToMessageCategory("Complex platelet summary",array($formNode));


        //        CCI (Corrected Count Increment) Calculations: [Form Section]
        $formParams = array(
            'parent' => $transfusionMedicine,
            'name' => "CCI (Corrected Count Increment) Calculations",
            'placeholder' => "",
            'objectType' => $objectTypeSection,
            'showLabel' => true,
            'visible' => true
        );
        $CCISection = $this->createFormNode($formParams);
        $this->setFormNodeToMessageCategory("Complex platelet summary",array($CCISection));

        //    BSA: [Form Field - Free Text, Single Line]
        $formParams = array(
            'parent' => $CCISection,
            'name' => "BSA",
            'placeholder' => "BSA",
            'objectType' => $objectTypeString,
            'showLabel' => true,
            'visible' => true
        );
        $formNode = $this->createFormNode($formParams);
        $this->setFormNodeToMessageCategory("Complex platelet summary",array($formNode));

        //    Unit Platelet Count [Form Field - Free Text, Single Line] : USE "Link To List ID" to link to a new list titled "CCI Unit Platelet Count Default Value" with one list item with "3" in the name column, load "3" via this link into this field on load. This mechanism will allow multiple possible default values for a given field depending on rules (once rules are implemented, until then your logic should grab the first value on the list).
        //        CCI Unit Platelet Count Default Value [Free Text Field Default Value List]
        //            3 [Free Text Field Default Value]
        $CCIUnitPlateletCountDefaultValueList = $this->em->getRepository("OlegUserdirectoryBundle:CCIUnitPlateletCountDefaultValueList")->findOneByName("3");
        $formParams = array(
            'parent' => $CCISection,
            'name' => "Unit Platelet Count",
            'placeholder' => "Unit Platelet Count",
            'objectType' => $objectTypeString,
            'showLabel' => true,
            'visible' => true,
            'classObject' => $CCIUnitPlateletCountDefaultValueList
        );
        $formNode = $this->createFormNode($formParams);
        $this->setFormNodeToMessageCategory("Complex platelet summary",array($formNode));

        //CCI (Corrected Count Increment) Instance: [Form Section] NESTED IN "CCI (Corrected Count Increment) Calculations: [Form Section]"
        $formParams = array(
            'parent' => $CCISection,
            'name' => "CCI (Corrected Count Increment) Instance",
            'placeholder' => "",
            'objectType' => $objectTypeSection,
            'showLabel' => true,
            'visible' => true
        );
        $CCIInstanceSection = $this->createFormNode($formParams);
        $this->setFormNodeToMessageCategory("Complex platelet summary",array($CCIInstanceSection));
        //    CCI date: [Form Field - Full Date and Time]
        $formParams = array(
            'parent' => $CCIInstanceSection,
            'name' => "CCI date",
            'placeholder' => "CCI date",
            'objectType' => $objectTypeFullDateTime,
            'showLabel' => true,
            'visible' => true
        );
        $formNode = $this->createFormNode($formParams);
        $this->setFormNodeToMessageCategory("Complex platelet summary",array($formNode));

        //    CCI Platelet Type Transfused [Form Field - Dropdown Menu]
        //        CCI Platelet Type Transfused [Dropdown Menu Value List]
        //            Regular Platelets [Dropdown Menu Value]
        //            Crossmatched [Dropdown Menu Value]
        //            HLA matched [Dropdown Menu Value]
        //            ABO matched [Dropdown Menu Value]
        $formParams = array(
            'parent' => $CCIInstanceSection,
            'name' => "CCI Platelet Type Transfused",
            'placeholder' => "CCI Platelet Type Transfused",
            'objectType' => $objectTypeDropdown,
            'showLabel' => true,
            'visible' => true,
            'classNamespace' => "Oleg\\UserdirectoryBundle\\Entity",
            'className' => "CCIPlateletTypeTransfusedList"
        );
        $formNode = $this->createFormNode($formParams);
        $this->setFormNodeToMessageCategory("Complex platelet summary",array($formNode));

        //    Pre Platelet Count 1: [Form Field - Free Text, Single Line]
        $formParams = array(
            'parent' => $CCIInstanceSection,
            'name' => "Pre Platelet Count 1",
            'placeholder' => "Pre Platelet Count 1",
            'objectType' => $objectTypeString,
            'showLabel' => true,
            'visible' => true
        );
        $formNode = $this->createFormNode($formParams);
        $this->setFormNodeToMessageCategory("Complex platelet summary",array($formNode));
        //    Post Platelet Count 2: [Form Field - Free Text, Single Line]
        $formParams = array(
            'parent' => $CCIInstanceSection,
            'name' => "Post Platelet Count 2",
            'placeholder' => "Post Platelet Count 2",
            'objectType' => $objectTypeString,
            'showLabel' => true,
            'visible' => true
        );
        $formNode = $this->createFormNode($formParams);
        $this->setFormNodeToMessageCategory("Complex platelet summary",array($formNode));
        //    CCI: [Form Field - Free Text, Single Line]
        $formParams = array(
            'parent' => $CCIInstanceSection,
            'name' => "CCI",
            'placeholder' => "CCI",
            'objectType' => $objectTypeString,
            'showLabel' => true,
            'visible' => true
        );
        $formNode = $this->createFormNode($formParams);
        $this->setFormNodeToMessageCategory("Complex platelet summary",array($formNode));


        //        Miscellaneous [Form Section]
        //    Product Currently Receiving: [Form Field - Dropdown Menu]
        //        Platelet Transfusion Product Receiving [Dropdown Menu Value List]
        //            HLA Platelets [Dropdown Menu Value]
        //            XM Platelets [Dropdown Menu Value]
        //            Regular Platelets [Dropdown Menu Value]
        //            Platelet Drip [Dropdown Menu Value]
        $formParams = array(
            'parent' => $miscellaneous,
            'name' => "Product Currently Receiving",
            'placeholder' => "Product Currently Receiving",
            'objectType' => $objectTypeDropdown,
            'showLabel' => true,
            'visible' => true,
            'classNamespace' => "Oleg\\UserdirectoryBundle\\Entity",
            'className' => "PlateletTransfusionProductReceivingList"
        );
        $formNode = $this->createFormNode($formParams);
        $this->setFormNodeToMessageCategory("Complex platelet summary",array($formNode));

        //    Product should be receiving: [Form Field - Dropdown Menu]
        //        Platelet Transfusion Product Receiving [Dropdown Menu Value List] SAME LIST AS ABOVE, DO NOT DUPLICATE, just link to it via Link to List ID
        $formParams = array(
            'parent' => $miscellaneous,
            'name' => "Product should be receiving",
            'placeholder' => "Product should be receiving",
            'objectType' => $objectTypeDropdown,
            'showLabel' => true,
            'visible' => true,
            'classNamespace' => "Oleg\\UserdirectoryBundle\\Entity",
            'className' => "PlateletTransfusionProductReceivingList"
        );
        $formNode = $this->createFormNode($formParams);
        $this->setFormNodeToMessageCategory("Complex platelet summary",array($formNode));

        //    Product Status: [Form Field - Dropdown Menu]
        //        Transfusion Product Status [Dropdown Menu Value List]
        //            Ordered [Dropdown Menu Value]
        //            Not Ordered [Dropdown Menu Value]
        //            Pending [Dropdown Menu Value]
        //            In-house [Dropdown Menu Value]
        $formParams = array(
            'parent' => $miscellaneous,
            'name' => "Product Status",
            'placeholder' => "Product Status",
            'objectType' => $objectTypeDropdown,
            'showLabel' => true,
            'visible' => true,
            'classNamespace' => "Oleg\\UserdirectoryBundle\\Entity",
            'className' => "TransfusionProductStatusList"
        );
        $formNode = $this->createFormNode($formParams);
        $this->setFormNodeToMessageCategory("Complex platelet summary",array($formNode));

        //    Expiration Date: [Form Field - Full Date]
        $formParams = array(
            'parent' => $miscellaneous,
            'name' => "Expiration Date",
            'placeholder' => "Expiration Date",
            'objectType' => $objectTypeFullDateTime,
            'showLabel' => true,
            'visible' => true
        );
        $formNode = $this->createFormNode($formParams);
        $this->setFormNodeToMessageCategory("Complex platelet summary",array($formNode));


        ////////////////////////////////////////////////////////
        //        Transfusion Medicine -> WinRho [Message Category]
        //Miscellaneous [Form Section]
        //    Weight: [Form Field - Free Text, Single Line]
        $formParams = array(
            'parent' => $miscellaneous,
            'name' => "Weight",
            'placeholder' => "Weight",
            'objectType' => $objectTypeString,
            'showLabel' => true,
            'visible' => true
        );
        $formNode = $this->createFormNode($formParams);
        $this->setFormNodeToMessageCategory("WinRho",array($formNode));
        //    Dosing: [Form Field - Free Text, Single Line]
        $formParams = array(
            'parent' => $miscellaneous,
            'name' => "Dosing",
            'placeholder' => "Dosing",
            'objectType' => $objectTypeString,
            'showLabel' => true,
            'visible' => true
        );
        $formNode = $this->createFormNode($formParams);
        $this->setFormNodeToMessageCategory("WinRho",array($formNode));
        //    IU: [Form Field - Free Text, Single Line]
        $formParams = array(
            'parent' => $miscellaneous,
            'name' => "IU",
            'placeholder' => "IU",
            'objectType' => $objectTypeString,
            'showLabel' => true,
            'visible' => true
        );
        $formNode = $this->createFormNode($formParams);
        $this->setFormNodeToMessageCategory("WinRho",array($formNode));


        //        Transfusion Medicine -> Special needs [Message Category]
        //Laboratory Values [Form Section]
        //    Relevant Laboratory Values: [Form Field - Free Text]
        $formParams = array(
            'parent' => $laboratoryValues,
            'name' => "Relevant Laboratory Values",
            'placeholder' => "Relevant Laboratory Values",
            'objectType' => $objectTypeText,
            'showLabel' => true,
            'visible' => true
        );
        $formNode = $this->createFormNode($formParams);
        $this->setFormNodeToMessageCategory("Special needs",array($formNode));

        //Transfusion Medicine -> Other [Message Category]
        //Laboratory Values [Form Section]
        //    Relevant Laboratory Values: [Form Field - Free Text]
        $formParams = array(
            'parent' => $laboratoryValues,
            'name' => "Relevant Laboratory Values",
            'placeholder' => "Relevant Laboratory Values",
            'objectType' => $objectTypeText,
            'showLabel' => true,
            'visible' => true
        );
        $formNode = $this->createFormNode($formParams);
        $this->setFormNodeToMessageCategory("Other",array($formNode),"Transfusion Medicine");

        //Microbiology [Message Category]
        //Laboratory Values [Form Section]
        //    Relevant Laboratory Values: [Form Field - Free Text]
        $formParams = array(
            'parent' => $laboratoryValues,
            'name' => "Relevant Laboratory Values",
            'placeholder' => "Relevant Laboratory Values",
            'objectType' => $objectTypeText,
            'showLabel' => true,
            'visible' => true
        );
        $formNode = $this->createFormNode($formParams);
        $this->setFormNodeToMessageCategory("Microbiology",array($formNode));

        //Coagulation [Message Category]
        //Laboratory Values [Form Section]
        //    Relevant Laboratory Values: [Form Field - Free Text]
        $formParams = array(
            'parent' => $laboratoryValues,
            'name' => "Relevant Laboratory Values",
            'placeholder' => "Relevant Laboratory Values",
            'objectType' => $objectTypeText,
            'showLabel' => true,
            'visible' => true
        );
        $formNode = $this->createFormNode($formParams);
        $this->setFormNodeToMessageCategory("Coagulation",array($formNode));

        //Hematology [Message Category]
        //Laboratory Values [Form Section]
        //    Relevant Laboratory Values: [Form Field - Free Text]
        $formParams = array(
            'parent' => $laboratoryValues,
            'name' => "Relevant Laboratory Values",
            'placeholder' => "Relevant Laboratory Values",
            'objectType' => $objectTypeText,
            'showLabel' => true,
            'visible' => true
        );
        $formNode = $this->createFormNode($formParams);
        $this->setFormNodeToMessageCategory("Hematology",array($formNode));

        //Chemistry [Message Category]
        //Laboratory Values [Form Section]
        //    Relevant Laboratory Values: [Form Field - Free Text]
        $formParams = array(
            'parent' => $laboratoryValues,
            'name' => "Relevant Laboratory Values",
            'placeholder' => "Relevant Laboratory Values",
            'objectType' => $objectTypeText,
            'showLabel' => true,
            'visible' => true
        );
        $formNode = $this->createFormNode($formParams);
        $this->setFormNodeToMessageCategory("Chemistry",array($formNode));

        //Cytogenetics [Message Category]
        //Laboratory Values [Form Section]
        //    Relevant Laboratory Values: [Form Field - Free Text]
        $formParams = array(
            'parent' => $laboratoryValues,
            'name' => "Relevant Laboratory Values",
            'placeholder' => "Relevant Laboratory Values",
            'objectType' => $objectTypeText,
            'showLabel' => true,
            'visible' => true
        );
        $formNode = $this->createFormNode($formParams);
        $this->setFormNodeToMessageCategory("Cytogenetics",array($formNode));

        //Molecular [Message Category]
        //Laboratory Values [Form Section]
        //    Relevant Laboratory Values: [Form Field - Free Text]
        $formParams = array(
            'parent' => $laboratoryValues,
            'name' => "Relevant Laboratory Values",
            'placeholder' => "Relevant Laboratory Values",
            'objectType' => $objectTypeText,
            'showLabel' => true,
            'visible' => true
        );
        $formNode = $this->createFormNode($formParams);
        $this->setFormNodeToMessageCategory("Molecular",array($formNode));

        //Other [Message Category]
        //Laboratory Values [Form Section]
        //    Relevant Laboratory Values: [Form Field - Free Text]
        $formParams = array(
            'parent' => $laboratoryValues,
            'name' => "Relevant Laboratory Values",
            'placeholder' => "Relevant Laboratory Values",
            'objectType' => $objectTypeText,
            'showLabel' => true,
            'visible' => true
        );
        //$formNode = $this->createFormNode($formParams);
        //$this->setFormNodeToMessageCategory("Other",array($formNode));

    }


//    public function createFormNodeAndLinkToMessageCategory( $formSectionArr, $messageCategoryName ) {
//
//        foreach( $formSectionArr as $formParams ) {
//
//            //placeholder
//            if( array_key_exists('fieldParent', $formParams) ) {
//                $fieldParent = $formParams['fieldParent'];
//            } else {
//                $fieldParent = false;
//            }
//
//            if( $fieldParent ) {
//                $fieldParentNode = $this->createFormNode($formParams);
//            } else {
//                $this->createFormNode($formParams);
//            }
//
//        }
//
//        //attach this formnode to the MessageCategory $messageCategoryName (i.e. "Transfusion Medicine")
//        $this->setFormNodeToMessageCategory($messageCategoryName,$fieldParentNode);
//    }

    public function getDropdownValue( $formNode ) {
        $em = $this->em;
        $output = array();

        $entityNamespace = $formNode->getEntityNamespace(); //"Oleg\OrderformBundle\Entity"
        $entityName = $formNode->getEntityName();           //"BloodProductTransfusedList"

        if( $entityNamespace && $entityName ) {

            $entityNamespaceArr = explode("\\",$entityNamespace);
            $bundleName = $entityNamespaceArr[0].$entityNamespaceArr[1];

            $query = $em->createQueryBuilder()->from($bundleName.':'.$entityName, 'list')
                ->select("list.id as id, list.name as text")
                ->orderBy("list.orderinlist","ASC");

            //$query->where("list.type = :typedef OR list.type = :typeadd")->setParameters(array('typedef' => 'default','typeadd' => 'user-added'));
            $query->where("list.type = :typedef OR list.type = :typeadd")->
                    setParameters(array('typedef' => 'default','typeadd' => 'user-added'));

            $output = $query->getQuery()->getResult();

        }

        $resArr = array();
        foreach( $output as $list ) {
            $resArr[] = array(
                'id' => $list['text'],
                'text' => $list['text']
            );
        }

        //get additional menu children "Dropdown Menu Value"
        foreach( $formNode->getChildren() as $dropdownValue ) {
            $resArr[] = array(
                'id' => $dropdownValue->getName()."",
                'text' => $dropdownValue->getName().""
            );
        }

        return $resArr;
    }

    public function getDefaultValue( $formNode ) {
        $em = $this->em;
        $entityNamespace = $formNode->getEntityNamespace(); //"Oleg\OrderformBundle\Entity"
        $entityName = $formNode->getEntityName();           //"CCIUnitPlateletCountDefaultValueList"
        $entityId = $formNode->getEntityId();

        if( $entityId ) {
            $entityNamespaceArr = explode("\\", $entityNamespace);
            $bundleName = $entityNamespaceArr[0] . $entityNamespaceArr[1];
            $defaultValueEntity = $em->getRepository($bundleName.':'.$entityName)->find($entityId);

            if( $defaultValueEntity ) {
                return $defaultValueEntity->getName() . "";
            }
        }

        return null;
    }

}




