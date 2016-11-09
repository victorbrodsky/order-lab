<?php
namespace Oleg\UserdirectoryBundle\Util;
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
        echo "<br><br>";

        //get formnode hierarchy
        //$children = $rootFormNode->getChildren();
        //echo "children count=" . count($children) . "<br>";

//        foreach( $children as $formNode ) {
//            //$formNodeType = $formNode->getObjectType()."";
//
//            if( !$this->hasValue($formNode) ) {
//                continue;
//            }
//
//            $key = "formnode-".$formNode->getId();
//            $formValue = $data[$key];
//            echo $key.": formValue=" . $formValue . "<br>";
//
//        }

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
        $formNodeType = $formNode->getObjectType();
        $entityNamespace = $formNodeType->getEntityNamespace();
        $entityName = $formNodeType->getEntityName();

        if( !$entityNamespace || !$entityName ) {
            return null;
        }

        //Oleg\UserdirectoryBundle\Entity:ObjectTypeText
        //"OlegUserdirectoryBundle:ObjectTypeText"
        $entityNamespaceArr = explode("\\",$entityNamespace);
        if( count($entityNamespaceArr) > 2 ) {
            $entityNamespaceShort = $entityNamespaceArr[0] . $entityNamespaceArr[1];
            $entityFullName = $entityNamespaceShort . ":" . $entityName;
        } else {
            throw new \Exception( 'Corresponding value list namespace is invalid: '.$entityNamespace );
        }

        $listClassName = $entityNamespace."\\".$entityName;
        $newList = new $listClassName();
        //$newList = new ObjectTypeText();
        $creator = $this->sc->getToken()->getUser();
        $name = "";
        $count = null;
        $this->setDefaultList($newList,$count,$creator,$name,$entityFullName);

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

    public function setDefaultList( $entity, $count, $user, $name=null, $entityFullName ) {

        if( !$count ) {
            $count = $this->getMaxId($entityFullName);
            //echo "count=".$count."<br>";
        }

        $entity->setOrderinlist( $count );
        $entity->setCreator( $user );
        $entity->setCreatedate( new \DateTime() );
        $entity->setType('user-added');
        if( $name ) {
            $entity->setName( trim($name) );
        }
        return $entity;
    }

    public function getMaxId( $entityFullName ) {
        //echo "entityFullName=" . $entityFullName . "<br>";
        $repository = $this->em->getRepository($entityFullName);
        $dql =  $repository->createQueryBuilder("u");
        $dql->select('MAX(u.id) as idMax');
        //$dql->setMaxResults(1);
        $res = $dql->getQuery()->getSingleResult();
        $maxId = $res['idMax'];
        if( !$maxId ) {
            $maxId = 0;
        }

        return $maxId;
    }
}

