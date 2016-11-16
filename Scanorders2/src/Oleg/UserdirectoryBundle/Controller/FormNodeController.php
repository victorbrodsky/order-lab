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


        $formNodeArr = array(
            'formNodeHolderEntity' => $formNodeHolderEntity,
            'cycle' => 'edit',
        );

        $template = $this->render('OlegUserdirectoryBundle:FormNode:formnode_fields.html.twig',$formNodeArr)->getContent();

        $formNodeId = null;
        $idBreadcrumbsArr = array();
        if( $formNodeHolderEntity->getFormNode() ) {
            $formNodeId = $formNodeHolderEntity->getFormNode()->getId();

            //check if form node should be attached to the parent form node
            $parentFormNode = $formNodeHolderEntity->getFormNode()->getParent();
            //echo "parentFormNode=".$parentFormNode->getName()."<br>";
            if( $parentFormNode ) {
                $parentFormNodeObjectType = $parentFormNode->getObjectType();
                if( $parentFormNodeObjectType ) {
                    //echo "parentObjectTypeName=".$parentFormNodeObjectType->getName()."<br>";
                    if( $parentFormNodeObjectType->getName() == "Form Section" ) {
                        $idBreadcrumbsArr = $formNodeHolderEntity->getIdBreadcrumbs();
                        $idBreadcrumbsArr = array_reverse($idBreadcrumbsArr);
                        //print_r($idBreadcrumbsArr);
                    }
                }
            }
        }

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
            'formNodeHtml' => $template,
            'formNodeId' => $formNodeId,
            //'parentFormnodeHolderId' => $parentFormnodeHolderId, //parent messageCategory Id
            'idBreadcrumbsArr' => $idBreadcrumbsArr    //implode("=>",$idBreadcrumbsArr)
        );

        $json = json_encode($res);
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


}