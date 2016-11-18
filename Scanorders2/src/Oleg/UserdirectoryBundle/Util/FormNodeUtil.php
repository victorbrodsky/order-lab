<?php
namespace Oleg\UserdirectoryBundle\Util;
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

        //echo "formNode=".$formNode."<br>";
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
            return;
        }

        $key = "formnode-".$formNode->getId();
        $formValue = $data[$key];
        echo $key.": formValue=" . $formValue . "<br>";

        //1) create a new list
        $newList = $this->createNewList($formNode,$formValue);

        //2) add value to the created list
        if( $formValue ) {
            $newList->setValue($formValue);
        }

        //3) set message by entityName to the created list
        $newList->setObject($holderEntity);

        //exit("processFormNodeByType; formValue=".$formValue);

        $this->em->persist($newList);
        $this->em->flush($newList); //testing
    }


    public function hasValue( $formNode ) {

//        $formNodeType = $formNode->getObjectType()->getName()."";
//        //echo "formNodeType=" . $formNodeType . "<br>";
//
//        if( $formNodeType == "Form Group" ) {
//            return false;
//        }
//        if( $formNodeType == "Form" ) {
//            return false;
//        }
//        if( $formNodeType == "Form Section" ) {
//            return false;
//        }

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
        $formNodeType = $formNode->getObjectType();
        $entityNamespace = $formNodeType->getEntityNamespace();
        $entityName = $formNodeType->getEntityName();

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
        $newList = new $listClassName();
        //$newList = new ObjectTypeText();
        $creator = $this->sc->getToken()->getUser();
        $name = "";
        $count = null;
        $userSecUtil->setDefaultList($newList,$count,$creator,$name);

        return $newList;
    }

//    public function getListByType( $formNode ) {
//
//        $list = null;
//        $newList = null;
//
//        $formNodeType = $formNode->getObjectType();
//        //echo "formNodeType=" . $formNodeType . "<br>";
//
//        if( $formNodeType->getName()."" == "Form Field - Free Text" ) {
//            $list = $formNode->getObjectTypeText();
//            $newList = new ObjectTypeText();
//            $creator = $this->sc->getToken()->getUser();
//            $name = "";
//            $count = null;
//            $entityFullName = "OlegUserdirectoryBundle:ObjectTypeText";
//            $this->setDefaultList($newList,$count,$creator,$name,$entityFullName);
//            $this->em->persist($newList);
//        }
//
//        $res = array(
//            'list' => $list,
//            'newList' => $newList
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

//            //set setObjectTypeText
//            if( $objectTypeList ) {
//                $node->setObjectTypeText($objectTypeList);
//            }

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

            echo "Created: ".$node->getName()."<br>";
            $em->persist($parent);
            $em->persist($node);
            $em->flush();
        } else {
            echo "Existed: ".$node->getName()."<br>";
            echo "objectType=".$objectType->getName()."<br>";
            //set objectType
            if( $objectType ) {
                echo "update object type <br>";
                if( !$node->getObjectType() ) {
                    $node->setObjectType($objectType);
                    $em->persist($node);
                    $em->flush();
                    echo "objectType=".$node->getObjectType()."<br>";
                }
            }

        }

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

    public function setFormNodeToMessageCategory($messageCategoryName,$formNodes) {
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
        //$objectTypeText = $this->getObjectTypeByName('Form Field - Free Text');
        $objectTypeString = $this->getObjectTypeByName('Form Field - Free Text, Single Line');
        $objectTypeDropdown = $this->getObjectTypeByName('Form Field - Dropdown Menu');

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
        $objectTypeDropdown = $this->getObjectTypeByName('Form Field - Dropdown Menu'); //,'ObjectTypeBloodProductTransfused');
        $formParams = array(
            'parent' => $miscellaneous,
            'name' => "Blood Product Transfused",
            'placeholder' => "Blood Product Transfused",
            'objectType' => $objectTypeDropdown,
            'showLabel' => true,
            'visible' => true
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
            'visible' => true
        );
        $TransfusionReactionType = $this->createFormNode($formParams);
        //attach this formnodes to the MessageCategory
        $this->setFormNodeToMessageCategory("Transfusion reaction",array($TransfusionReactionType));

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



}




