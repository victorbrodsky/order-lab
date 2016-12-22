<?php
/**
 * Created by PhpStorm.
 * User: ch3
 * Date: 11/10/2016
 * Time: 4:03 PM
 */

namespace Oleg\UserdirectoryBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;


class FormNodeController extends Controller {

    private $single = 'single';

    //private $testing = true;
    private $testing = false;

    /**
     * Second part of the user view profile
     *
     * @Route("/formnode-fields/", name="employees_formnode_fields", options={"expose"=true})
     * @Method({"GET", "POST"})
     * @Template("OlegUserdirectoryBundle:FormNode:formnode_fields.html.twig")
     */
    public function getFormNodeFieldsAction( Request $request )
    {
        if( false === $this->get('security.context')->isGranted('ROLE_USER') ) {
            return $this->redirect( $this->generateUrl('employees-nopermission') );
        }

        $formNodeUtil = $this->get('user_formnode_utility');
        $em = $this->getDoctrine()->getManager();

        $cycle = $request->query->get('cycle');

        //formnode's holder (MessageCategory)
        $holderNamespace = $request->query->get('holderNamespace');
        $holderName = $request->query->get('holderName'); //MessageCategory
        $holderId = $request->query->get('holderId');

        //receiving list's entityName (Message)
        $entityNamespace = $request->query->get('entityNamespace'); //"Oleg\\OrderformBundle\\Entity"
        $entityName = $request->query->get('entityName'); //"Message";
        $entityId = $request->query->get('entityId'); //"Message ID";

        //add to url: &testing=true
        $testing = $request->query->get('testing');
        if( $testing ) {
            $this->testing = true;
        }

        //echo "entityNamespace=".$entityNamespace."<br>";
        //echo "entityName=".$entityName."<br>";
        //echo "entityId=".$entityId."<br>";

        if( !$holderNamespace || !$holderName || !$holderId ) {
            //echo "no holder namespace and name";
            return null;
        }

        //Oleg\UserdirectoryBundle\Entity:ObjectTypeText
        //"OlegUserdirectoryBundle:ObjectTypeText"
        $holderNamespaceArr = explode("\\",$holderNamespace);
        if( count($holderNamespaceArr) > 2 ) {
            $holderNamespaceShort = $holderNamespaceArr[0] . $holderNamespaceArr[1];
            $holderFullName = $holderNamespaceShort . ":" . $holderName;
        } else {
            throw new \Exception( 'Corresponding value list namespace is invalid: '.$holderNamespace );
        }

        $formNodeHolderEntity = $em->getRepository($holderFullName)->find($holderId);
        if( !$formNodeHolderEntity ) {
            throw new \Exception( 'Entity not found: holderFullName='.$holderFullName.'; holderId='.$holderId );
        }

        $formNodeHolderId = $formNodeHolderEntity->getId();
        $resArr = array();

        //$formNodes = $formNodeHolderEntity->getFormNodes();
        //get only 'real' fields as $formNodes
        $formNodes = $formNodeUtil->getAllRealFormNodes($formNodeHolderEntity);

        foreach( $formNodes as $formNode ) {

            if( $formNode && $formNodeId = $formNode->getId() ) {
                $formNodeId = $formNode->getId();
            } else {
                continue;
            }

            if( $this->isFormNodeInArray($formNode,$resArr) ) {
                continue;
            }

            if( $this->testing ) {
                echo "<br>Check formNode: holder=" . $formNodeHolderEntity->getName() . "; formnode=" . $formNode->getName() . "; objecttype=" . $formNode->getObjectTypeName() . ":". $formNode->getObjectTypeId() . "<br>";
            }
            $parentFormNode = $this->getParentFormNodeSection($formNodeHolderEntity,$formNode);
            if( $parentFormNode ) {
                $parentFormNodeId = $parentFormNode->getId();
            } else {
                $parentFormNodeId = null;
            }

            $arraySectionCount = null;

            if( $parentFormNodeId ) {
                //check parent nested sections
                $resArr = $this->createParentFormSectionTemplateRecursively($formNodeHolderEntity, $formNode, $resArr);

                //$arraySectionCount = $resArr['formNodeArraySectionCount'];
                $arraySectionCount = $formNodeUtil->getArraySectionCountRecursive($parentFormNode,$arraySectionCount,$this->testing);

                if( $this->testing ) {
                    echo "final arraySectionCount=" . $arraySectionCount . "<br>";
                }
            }

            //find FormNode value by entityNamespace, entityName, entityId
            $formNodeValue = null;
            if( $entityId ) {
                $mapper = array(
                    'entityNamespace' => $entityNamespace,
                    'entityName' => $entityName, //"Message"
                    'entityId' => $entityId,
                );
                $formNodeValue = $formNodeUtil->getFormNodeValueByFormnodeAndReceivingmapper($formNode,$mapper);
            }
            //echo "formNode=".$formNode->getId()."<br>";
            //echo "formNodeValue=".$formNodeValue."<br>";
            if( $this->testing ) {
                echo "formNodeValue for formNode=".$formNode->getId().":<br>";
                print "<pre>";
                print_r($formNodeValue);
                print "</pre>EOF formNodeValues<br>";
            }

            if( is_array($formNodeValue) ) {

                /////////////// TODO: create additional sections ///////////////
                foreach( $formNodeValue as $formNodeValueArr ) {
                    $formNodeValue = $formNodeValueArr['formNodeValue'];
                    $arraySectionCount = $formNodeValueArr['arraySectionIndex'];

//                    if( $arraySectionCount ) {
//                        $formNodeId = $formNodeId."_".$arraySectionCount;
//                        if( $parentFormNodeId ) {
//                            $parentFormNodeId = $parentFormNodeId."_".$arraySectionCount;
//                        }
//                    }
                    $formNodeId = $formNodeUtil->getFormNodeId($formNodeId,$arraySectionCount);
                    if( $parentFormNodeId ) {
                        $parentFormNodeId = $formNodeUtil->getFormNodeId($parentFormNodeId,$arraySectionCount);
                    }

                    $formNodeArr = array(
                        'formNode' => $formNode,
                        'formNodeId' => $formNodeId,
                        'formNodeHolderEntity' => $formNodeHolderEntity,
                        'cycle' => $cycle,
                        'formNodeValue' => $formNodeValue,
                        'single' => $this->single,
                        'arraySectionCount' => $arraySectionCount
                    );

                    $template = $this->render('OlegUserdirectoryBundle:FormNode:formnode_fields.html.twig', $formNodeArr)->getContent();

                    $res = array(
                        'formNodeHolderId' => $formNodeHolderId,
                        'parentFormNodeId' => $parentFormNodeId,
                        'formNodeId' => $formNodeId,
                        'simpleFormNode' => true,
                        'formNodeObjectType' => $formNode->getObjectType() . "",
                        'formNodeValue' => $formNodeValue,
                        'formNodeHtml' => $template,
                        'arraySectionCount' => $arraySectionCount
                        //'parentFormnodeHolderId' => $parentFormnodeHolderId, //parent messageCategory Id
                        //'idBreadcrumbsArr' => $idBreadcrumbsArr    //implode("=>",$idBreadcrumbsArr)
                    );

                    $resArr[] = $res;
                }
                /////////////// EOF create additional sections ///////////////

            } else {

                //$formNodeId = $formNode->getId();

                $formNodeArr = array(
                    'formNode' => $formNode,
                    'formNodeId' => $formNodeId,
                    'formNodeHolderEntity' => $formNodeHolderEntity,
                    'cycle' => $cycle,
                    'formNodeValue' => $formNodeValue,
                    'single' => $this->single,
                    'arraySectionCount' => $arraySectionCount
                );

                $template = $this->render('OlegUserdirectoryBundle:FormNode:formnode_fields.html.twig', $formNodeArr)->getContent();

                $res = array(
                    'formNodeHolderId' => $formNodeHolderId,
                    'parentFormNodeId' => $parentFormNodeId,
                    'formNodeId' => $formNodeId,
                    'simpleFormNode' => true,
                    'formNodeObjectType' => $formNode->getObjectType() . "",
                    'formNodeValue' => $formNodeValue,
                    'formNodeHtml' => $template,
                    'arraySectionCount' => null
                    //'parentFormnodeHolderId' => $parentFormnodeHolderId, //parent messageCategory Id
                    //'idBreadcrumbsArr' => $idBreadcrumbsArr    //implode("=>",$idBreadcrumbsArr)
                );

                $resArr[] = $res;
            }
        }//foreach

        if( $this->testing ) {
            print "<pre>";
            print_r($resArr);
            print "</pre>";
            exit('testing');
        }

        $json = json_encode($resArr);
        $response = new Response($json);
        $response->headers->set('Content-Type', 'application/json');
        return $response;


//        $template = "OK";
        //$showUserArr = $this->showUser($userid,$this->container->getParameter('employees.sitename'),false);

        //$template = $this->render('OlegUserdirectoryBundle:Profile:edit_user_only.html.twig',$showUserArr)->getContent();

//        $json = json_encode($template);
//        $response = new Response($json);
//        $response->headers->set('Content-Type', 'application/json');
//        return $response;
    }


    //create recursively $formNodeArr containing
    public function createParentFormSectionTemplateRecursively( $formNodeHolderEntity, $formNode, $resArr ) {

        $formNodeHolderId = $formNodeHolderEntity->getId();
        if( !$formNodeHolderId ) {
            return $resArr;
        }

        //check if the node has a parent form node type of Section and visible. The node will be placed by JS inside this section
        $parentFormNode = $this->getParentFormNodeSection($formNodeHolderEntity,$formNode);

        if( $parentFormNode ) {

            if( $this->isFormNodeInArray($parentFormNode,$resArr) ) {
                return $resArr;
            }

            $arraySectionCount = null;
            $formNodeUtil = $this->get('user_formnode_utility');
            $arraySectionCount = $formNodeUtil->getArraySectionCountRecursive($parentFormNode,$arraySectionCount,$this->testing);

//            if( $arraySectionCount ) {
//                $parentFormNodeId = $parentFormNode->getId()."_".$arraySectionCount;
//            } else {
//                $parentFormNodeId = $parentFormNode->getId();
//            }
            $parentFormNodeId = $formNodeUtil->getFormNodeId($parentFormNode->getId(),$arraySectionCount);

            $formNodeArr = array(
                'formNode' => $parentFormNode,
                'formNodeId' => $parentFormNodeId,
                'formNodeHolderEntity' => $formNodeHolderEntity,
                'cycle' => 'edit',
                'formNodeValue' => null,
                'single' => $this->single,
                'arraySectionCount' => $arraySectionCount
            );

            $template = $this->render('OlegUserdirectoryBundle:FormNode:formnode_fields.html.twig', $formNodeArr)->getContent();

            $grandParentFormNode = $this->getParentFormNodeSection($formNodeHolderEntity,$parentFormNode);
            if( $grandParentFormNode ) {
                $grandParentFormNodeId = $grandParentFormNode->getId();
            } else {
                $grandParentFormNodeId = null;
            }

            $res = array(
                'formNodeHolderId' => $formNodeHolderId,
                'parentFormNodeId' => $grandParentFormNodeId,
                'formNodeId' => $parentFormNodeId,  //$parentFormNode->getId(),
                'formNodeValue' => null,
                'formNodeHtml' => $template,
                'simpleFormNode' => false,
                'arraySectionCount' => $arraySectionCount
            );

            $resArr[] = $res;

            return $this->createParentFormSectionTemplateRecursively( $formNodeHolderEntity, $parentFormNode, $resArr );

        } else {
            return $resArr;
        }

        return $resArr;
    }

    //only "Form Section" and "Form Section Array" are visible by convention.
//    public function getParentFormNodeSectionOLD( $formNodeHolderEntity, $formNode ) {
//        $parentFormNode = $formNode->getParent();
//        if( $parentFormNode && $parentFormNode->isVisible() && $parentFormNode->getId() ) {
//
//            $parentFormNodeObjectType = $parentFormNode->getObjectType();
//            if ($parentFormNodeObjectType) {
//                //echo "parentObjectTypeName=".$parentFormNodeObjectType->getName()."<br>";
//                if(
//                    $parentFormNodeObjectType->getName() == "Form Section" ||
//                    $parentFormNodeObjectType->getName() == "Form Section Array"
//                ) {
//                    return $parentFormNode;
//                }
//            }
//        }
//
//        return null;
//    }
    //only "Form Section" and "Form Section Array" are visible by convention.
    //check all parents if they have a similar Form Section (the same name) and return the one on the top
    public function getParentFormNodeSection( $formNodeHolderEntity, $formNode ) {
        $formNodeUtil = $this->get('user_formnode_utility');
        $parentFormNode = $formNode->getParent();

        if( $parentFormNode && $parentFormNode->getId() && $formNodeUtil->isValidFormSection($parentFormNode) ) {
            $parentFormNodeName = $parentFormNode->getName();
            $objectTypeName = $parentFormNode->getObjectTypeName();
            $objectTypeId = $parentFormNode->getObjectTypeId();
            $topParentFormSection = $formNodeUtil->getTopFormSectionByHolderTreeRecursion($formNodeHolderEntity, $parentFormNodeName, $objectTypeId, $this->testing);
            if ($topParentFormSection) {
                if ($this->testing) {
                    echo '### topParentFormSection=' . $topParentFormSection . "; formnode=" . $formNode->getName() . " ($parentFormNodeName, $objectTypeName:$objectTypeId)" . "<br>";
                }
                return $topParentFormSection;
            }
        }

//        if( $parentFormNode && $parentFormNode->getId() && $formNodeUtil->isValidFormSection($parentFormNode) ) {
//            //echo $formNodeHolderEntity->getId().":parentFormNode=".$parentFormNode."<br><br>";
//            $formNodeName = $parentFormNode->getObjectTypeName();
//            $objectTypeId = $parentFormNode->getObjectTypeId();
//            $topParentFormSection = $formNodeUtil->getTopFormSectionByHolderTreeRecursion($formNodeHolderEntity->getParent(),$formNodeName,$objectTypeId);
//            if( $topParentFormSection ) {
//                echo $formNodeHolderEntity->getId().":topParentFormSection=".$topParentFormSection."<br><br>";
//                return $topParentFormSection;
//            } else {
//                echo $formNodeHolderEntity->getId().":parentFormNode=".$parentFormNode."<br><br>";
//                return $parentFormNode;
//            }
//        }

        return null;
    }


    public function isFormNodeInArray( $formNode, $resArr ) {
        foreach( $resArr as $res ) {
            if( $res['formNodeId'] == $formNode->getId() ) {
                return true;
            }
        }
        return false;
    }



    /**
     * Use: https://github.com/Atlantic18/DoctrineExtensions/blob/v2.4.x/doc/tree.md
     * @Route("/form-node-tree-test/", name="employees_form-node-tree-test")
     * @Method("GET")
     */
    public function formNodeTestAction(Request $request)
    {
        if( false === $this->get('security.context')->isGranted('ROLE_PLATFORM_DEPUTY_ADMIN') ) {
            return $this->redirect( $this->generateUrl($this->container->getParameter('employees.sitename').'-order-nopermission') );
        }

        $em = $this->getDoctrine()->getManager();

        $mapper = array(
            'prefix' => "Oleg",
            'className' => "FormNode",
            'bundleName' => "UserdirectoryBundle"
        );

        $repo = $em->getRepository('OlegUserdirectoryBundle:FormNode');

        //verify
        $verify = $repo->verify(); // can return TRUE if tree is valid, or array of errors found on tree
        if( $verify === TRUE ) {
            echo "FormNode tree is ok<br>";
        } else {
            echo "verify errors:<br>";
            print_r($verify);

            // if tree has errors it will try to fix all tree nodes
            if(0) {
                $repo->recover();
                $em->flush(); // important: flush recovered nodes
            }
        }

        // it will remove this node from tree and reparent all children
//        $disabledFormNode = null;
//        $disabledFormNodes = $repo->findBy(array('name'=>'Pathology Call Log Entry','type'=>'disabled'));
//        if( count($disabledFormNodes) == 1 ) {
//            $disabledFormNode = $disabledFormNodes[0];
//        }
//
//        if( !$disabledFormNode ) {
//            exit("disabledFormNode not found");
//        }
//
//        $repo->removeFromTree($disabledFormNode);
//        $em->clear(); // clear cached nodes

        if(0) {
            $id = "84";
            $removedCount = $repo->removeTreeNodeAndAllChildrenById($id, $mapper);
            echo "removedCount = $removedCount<br>";
        }

        if(0) {
            //fixed level: find all levels with -1
            $fixedCount = $repo->setLevelFromParentRecursively($mapper);
            echo "fixed levels count=".$fixedCount."<br>";
        }

        exit("<br><br>Form Node Tree testing");
    }


}