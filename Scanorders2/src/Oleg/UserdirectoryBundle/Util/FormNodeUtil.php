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

        $rootFormNode = $formNodeHolder->getFormNode();
        if( !$rootFormNode ) {
            return;
        }

        $data = $request->request->all();
        //print "<pre>";
        //print_r($data);
        //print "</pre>";
        //$unmappedField = $data["formnode-4"];
        //echo "<br>unmappedField=" . $unmappedField . "<br>";
        //echo "<br><br>";

        $this->processFormNodeRecursively($data,$rootFormNode,$holderEntity);
    }
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
        //echo $key.": formValue=" . $formValue . "<br>";

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
        $this->em->flush($newList);
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
        echo "rootNode=".$rootNode."<br>";

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

    public function setFormNodeToMessageCategory($messageCategoryName,$formNode) {
        //attach this formnode to the MessageCategory "Transfusion Medicine"
        $em = $this->em;
        $messageCategory = $em->getRepository('OlegOrderformBundle:MessageCategory')->findOneByName($messageCategoryName);
        if( !$messageCategory ) {
            exit("Message category not found by name=".$messageCategoryName);
        }
        if( !$messageCategory->getFormNode() ) {
            $messageCategory->setFormNode($formNode);
            $em->persist($messageCategory);
            $em->persist($formNode);
            $em->flush();
            echo "Set ".$formNode." to ".$messageCategory."<br>";
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
        );
        $PathologyCallLogEntry = $this->createFormNode($formParams);

        //History/Findings (Section)
        $formParams = array(
            'parent' => $PathologyCallLogEntry,
            'name' => "History/Findings",
            'objectType' => $objectTypeSection,
            'showLabel' => true
        );
        $historySection = $this->createFormNode($formParams);

        //History/Findings Text
        $formParams = array(
            'parent' => $historySection,
            'name' => "History/Findings Text",
            'placeholder' => "History/Findings Text",
            'objectType' => $objectTypeText,
            'showLabel' => false
        );
        $historyText = $this->createFormNode($formParams);

        //Impression/Outcome (Section)
        $formParams = array(
            'parent' => $PathologyCallLogEntry,
            'name' => "Impression/Outcome",
            'objectType' => $objectTypeSection,
            'showLabel' => true
        );
        $impressionSection = $this->createFormNode($formParams);

        //Impression/Outcome Text
        $formParams = array(
            'parent' => $impressionSection,
            'name' => "Impression/Outcome Text",
            'placeholder' => "Impression/Outcome Text",
            'objectType' => $objectTypeText,
            'showLabel' => false
        );
        $impressionText = $this->createFormNode($formParams);

        //attach this formnode to the MessageCategory "Transfusion Medicine"
        $this->setFormNodeToMessageCategory($messageCategoryName,$PathologyCallLogEntry);

        return $PathologyCallLogEntry;
    }

    public function createTransfusionMedicine($parent) {

        $objectTypeForm = $this->getObjectTypeByName('Form');
        $objectTypeSection = $this->getObjectTypeByName('Form Section');
        //$objectTypeText = $this->getObjectTypeByName('Form Field - Free Text');
        $objectTypeString = $this->getObjectTypeByName('Form Field - Free Text, Single Line');

        $messageCategoryName = "Transfusion Medicine";

        //Transfusion Medicine (Form)
        $formParams = array(
            'parent' => $parent,
            'name' => $messageCategoryName,
            'objectType' => $objectTypeForm,
            'showLabel' => false,
        );
        $transfusionMedicine = $this->createFormNode($formParams);

        ////////////// Laboratory Values [Form Section] //////////////////
        $formParams = array(
            'parent' => $transfusionMedicine,
            'name' => "Laboratory Values",
            'objectType' => $objectTypeSection,
            'showLabel' => true,
        );
        $laboratoryValues = $this->createFormNode($formParams);

            //Hemoglobin: [Form Field - Free Text, Single Line]
            $formParams = array(
                'parent' => $laboratoryValues,
                'name' => "Hemoglobin",
                'objectType' => $objectTypeString,
                'showLabel' => true
            );
            $Hemoglobin = $this->createFormNode($formParams);

            //Platelets: [Form Field - Free Text, Single Line]
            $formParams = array(
                'parent' => $laboratoryValues,
                'name' => "Platelets",
                'objectType' => $objectTypeString,
                'showLabel' => true
            );
            $Platelets = $this->createFormNode($formParams);
        ////////////// EOF Laboratory Values [Form Section] //////////////////

        //attach this formnode to the MessageCategory "Transfusion Medicine"
        $this->setFormNodeToMessageCategory($messageCategoryName,$transfusionMedicine);
    }





}




