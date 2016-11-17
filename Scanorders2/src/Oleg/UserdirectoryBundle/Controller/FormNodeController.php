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

        $em = $this->getDoctrine()->getManager();

        $entityNamespace = $request->query->get('entityNamespace');
        $entityName = $request->query->get('entityName');
        $entityId = $request->query->get('entityId');

        //echo "entityNamespace=".$entityNamespace."<br>";
        //echo "entityName=".$entityName."<br>";
        //echo "entityId=".$entityId."<br>";

        if( !$entityNamespace || !$entityName || !$entityId ) {
            //echo "no entity namespace and name";
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

        $formNodeHolderEntity = $em->getRepository($entityFullName)->find($entityId);
        if( !$formNodeHolderEntity ) {
            throw new \Exception( 'Entity not found: entityFullName='.$entityFullName.'; entityId='.$entityId );
        }

        $formNodeHolderId = $formNodeHolderEntity->getId();
        $resArr = array();

        foreach( $formNodeHolderEntity->getFormNodes() as $formNode ) {

            if( $formNode && $formNodeId = $formNode->getId() ) {
                $formNodeId = $formNode->getId();
            } else {
                continue;
            }


            //check if the node has a parent form node type of Section and visible. The node will be placed by JS inside this section
//            $parentFormNode = $formNode->getParent();
//            if( $parentFormNode && $parentFormNode->getVisible() && $parentFormNode->getId() ) {
//                $parentFormNodeObjectType = $parentFormNode->getObjectType();
//                if( $parentFormNodeObjectType ) {
//                    //echo "parentObjectTypeName=".$parentFormNodeObjectType->getName()."<br>";
//                    if( $parentFormNodeObjectType->getName() == "Form Group" ||
//                        $parentFormNodeObjectType->getName() == "Form" ||
//                        $parentFormNodeObjectType->getName() == "Form Section"
//                    ) {
//                        $formNodeArr = array(
//                            'formNode' => $parentFormNode,
//                            'formNodeHolderEntity' => $formNodeHolderEntity,
//                            'cycle' => 'edit',
//                        );
//
//                        $template = $this->render('OlegUserdirectoryBundle:FormNode:formnode_fields.html.twig', $formNodeArr)->getContent();
//
//                        $res = array(
//                            'formNodeHtml' => $template,
//                            'formNodeHolderId' => $formNodeHolderId,
//                            'formNodeId' => $parentFormNode->getId(),
//                        );
//
//                        $resArr[] = $res;
//                    }
//                }
//            } //parent
            $resArr = $this->createParentFormSectionTemplateRecursively( $formNodeHolderEntity, $formNode, $resArr );

            if( count($resArr) > 0 ) {
                $parentFormNodeId = $resArr[0]['parentFormNodeId'];
            } else {
                $parentFormNodeId = null;
            }

            $formNodeArr = array(
                'formNode' => $formNode,
                'formNodeHolderEntity' => $formNodeHolderEntity,
                'cycle' => 'edit',
            );

            $template = $this->render('OlegUserdirectoryBundle:FormNode:formnode_fields.html.twig', $formNodeArr)->getContent();

//            $formNodeId = null;
//            $idBreadcrumbsArr = array();
//            //TODO: rewrite according to multiple formNodes in one formNodeHolder
//            if ($formNode) {
//                $formNodeId = $formNode->getId();
//
//                //check if form node should be attached to the parent form node (if this formnode un-visible and objecttype "Form Section")
//                $parentFormNode = $formNode->getParent();
//                //echo "parentFormNode=".$parentFormNode->getName()."<br>";
//                if (!$formNode->getVisible() && $parentFormNode) {
//                    $parentFormNodeObjectType = $parentFormNode->getObjectType();
//                    if ($parentFormNodeObjectType) {
//                        //echo "parentObjectTypeName=".$parentFormNodeObjectType->getName()."<br>";
//                        if ($parentFormNodeObjectType->getName() == "Form Section") {
//                            $idBreadcrumbsArr = $formNodeHolderEntity->getIdBreadcrumbs();
//                            $idBreadcrumbsArr = array_reverse($idBreadcrumbsArr);
//                            //print_r($idBreadcrumbsArr);
//                        }
//                    }
//                }
//            }

//        $parentFormnodeHolderId = null;
//        $parent = $formNodeHolderEntity->getParent();
//        if( $parent ) {
//            $parentFormNode = $parent->getFormNode();
//            if( $parentFormNode ) {
//                $objectTypeName = $parentFormNode->getObjectType()->getName();
//                //echo "getObjectType=".$objectTypeName."<br>";
//                if( $objectTypeName == "Form Section" || $objectTypeName == "Form" ) {
//                    $parentFormnodeHolderId = $formNodeHolderEntity->getParent()->getFormNode()->getId();
//                }
//            }
//        }
            //echo "parentFormnodeHolderId=".$parentFormnodeHolderId."<br>";

            $res = array(
                'formNodeHolderId' => $formNodeHolderId,
                'parentFormNodeId' => $parentFormNodeId,
                'formNodeId' => $formNodeId,
                'formNodeHtml' => $template,
                //'parentFormnodeHolderId' => $parentFormnodeHolderId, //parent messageCategory Id
                //'idBreadcrumbsArr' => $idBreadcrumbsArr    //implode("=>",$idBreadcrumbsArr)
            );

            $resArr[] = $res;
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
        $parentFormNode = $this->getParentFormNodeSection($formNode);

        if( $parentFormNode ) {

            $formNodeArr = array(
                'formNode' => $parentFormNode,
                'formNodeHolderEntity' => $formNodeHolderEntity,
                'cycle' => 'edit',
            );

            $template = $this->render('OlegUserdirectoryBundle:FormNode:formnode_fields.html.twig', $formNodeArr)->getContent();

            $grandParentFormNode = $this->getParentFormNodeSection($parentFormNode);
            if( $grandParentFormNode ) {
                $grandParentFormNodeId = $grandParentFormNode->getId();
            } else {
                $grandParentFormNodeId = null;
            }

            $res = array(
                'formNodeHolderId' => $formNodeHolderId,
                'parentFormNodeId' => $grandParentFormNodeId,
                'formNodeId' => $parentFormNode->getId(),
                'formNodeHtml' => $template,
            );

            $resArr[] = $res;

            return $this->createParentFormSectionTemplateRecursively( $formNodeHolderEntity, $parentFormNode, $resArr );

        } else {
            return $resArr;
        }

        return $resArr;
    }

    public function getParentFormNodeSection( $formNode ) {

        $parentFormNode = $formNode->getParent();
        if( $parentFormNode && $parentFormNode->getVisible() && $parentFormNode->getId() ) {

            $parentFormNodeObjectType = $parentFormNode->getObjectType();
            if ($parentFormNodeObjectType) {
                //echo "parentObjectTypeName=".$parentFormNodeObjectType->getName()."<br>";
                if ($parentFormNodeObjectType->getName() == "Form Group" ||
                    $parentFormNodeObjectType->getName() == "Form" ||
                    $parentFormNodeObjectType->getName() == "Form Section"
                ) {
                    return $parentFormNode;
                }
            }
        }

        return null;
    }

}