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
 * Created by PhpStorm.
 * User: ch3
 * Date: 11/10/2016
 * Time: 4:03 PM
 */

namespace App\UserdirectoryBundle\Controller;



use App\OrderformBundle\Entity\MessageCategory;
use App\UserdirectoryBundle\Entity\FormNode; //process.py script: replaced namespace by ::class: added use line for classname=FormNode
use App\UserdirectoryBundle\Controller\OrderAbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;


use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Component\Routing\Annotation\Route;


class FormNodeController extends OrderAbstractController {

    private $single = 'single';

    //private $testing = true;
    private $testing = false;

    /**
     * Second part of the user view profile
     */
    #[Route(path: '/formnode-fields/', name: 'employees_formnode_fields', methods: ['GET', 'POST'], options: ['expose' => true])]
    #[Template('AppUserdirectoryBundle/FormNode/formnode_fields.html.twig')]
    public function getFormNodeFieldsAction( Request $request )
    {
        if( false === $this->isGranted('ROLE_USER') ) {
            return $this->redirect( $this->generateUrl('employees-nopermission') );
        }

        $formNodeUtil = $this->container->get('user_formnode_utility');
        $em = $this->getDoctrine()->getManager();

        $cycle = $request->query->get('cycle');

        //formnode's holder (MessageCategory)
        $holderNamespace = $request->query->get('holderNamespace');
        $holderName = $request->query->get('holderName'); //MessageCategory
        $holderId = $request->query->get('holderId');

        //receiving list's entityName (Message)
        $entityNamespace = $request->query->get('entityNamespace'); //"App\\OrderformBundle\\Entity"
        $entityName = $request->query->get('entityName'); //"Message";
        $entityId = $request->query->get('entityId'); //"Message ID";

        //add to url: &testing=true
        $testing = $request->query->get('testing');
        if( $testing ) {
            $this->testing = true;
        }

        //$logger = $this->container->get('logger');
        //$logger->notice("getFormNodeFieldsAction: holderNamespace=$holderNamespace, holderName=$holderName, holderId=$holderId");
        //$logger->notice("getFormNodeFieldsAction: entityNamespace=$entityNamespace, entityName=$entityName, entityId=$entityId");

        //echo "entityNamespace=".$entityNamespace."<br>";
        //echo "entityName=".$entityName."<br>";
        //echo "entityId=".$entityId."<br>";

        if( !$holderNamespace || !$holderName || !$holderId ) {
            //echo "no holder namespace and name";
            return null;
        }

        //App\UserdirectoryBundle\Entity:ObjectTypeText
        //"AppUserdirectoryBundle:ObjectTypeText"
//        $holderNamespaceArr = explode("\\",$holderNamespace);
//        if( count($holderNamespaceArr) > 2 ) {
//            $holderNamespaceShort = $holderNamespaceArr[0] . $holderNamespaceArr[1];
//            $holderFullName = $holderNamespaceShort . ":" . $holderName;
//        } else {
//            throw new \Exception( 'Corresponding value list namespace is invalid: '.$holderNamespace );
//        }

        $holderFullName = $holderNamespace . "\\" . $holderName;
        $formNodeHolderEntity = $em->getRepository($holderFullName)->find($holderId);
        if( !$formNodeHolderEntity ) {
            throw new \Exception( 'Entity not found: holderFullName='.$holderFullName.'; holderId='.$holderId );
        }
        //$logger->notice("getFormNodeFieldsAction: holderFullName=$holderFullName: formNodeHolderEntity ID=".$formNodeHolderEntity->getId());
        //$logger->notice("getFormNodeFieldsAction: formNodeHolderEntity->getName()=".$formNodeHolderEntity->getName().", formNodeHolderEntity->getId()=".$formNodeHolderEntity->getId());

        $formNodeHolderId = $formNodeHolderEntity->getId();
        $resArr = array();

        if( $testing ) {
            echo "cycle=" . $cycle . "<br>";
        }

        //$formNodes = $formNodeHolderEntity->getFormNodes();
        //get only 'real' fields as $formNodes
        $formNodes = $formNodeUtil->getAllRealFormNodes($formNodeHolderEntity,$cycle);

        //reverse array to show the fields backwards for show and edit, otherwise the order of submitted form fields is reversed.
        //if( $cycle != "new" ) {
            //test by link (Test: MessageCategory&holderId=70):
            // http://localhost/order/directory/formnode-fields/?holderNamespace=App\OrderformBundle\Entity&holderName=MessageCategory&holderId=70&entityNamespace=App\OrderformBundle\Entity&entityName=Message&entityId=222&cycle=show&testing=true
            //One way to solve it: for show and edit - start calling "formnode-fields" from top to bottom. On show page, this done in opposite way - from bottom to top.
            //for show use reverse array (don't use it for top to bottom combobox  processing)
            //$formNodes = array_reverse($formNodes);
        //}

        foreach( $formNodes as $formNode ) {

            if( $this->testing ) {
                echo "<br>###################### ".$formNode->getId()." ################<br>";
                echo "############# formNode: holder=" . $formNodeHolderEntity->getName() . "; formnode=" . $formNode->getName() . "; objecttype=" . $formNode->getObjectTypeName() . ":". $formNode->getObjectTypeId() . "<br>";
            }
            //$logger->notice("getFormNodeFieldsAction: formNode->getName()=".$formNode->getName().", formNode->getId()=".$formNode->getId().", formNode->getObjectTypeId()=".$formNode->getObjectTypeId());

            if( $formNode && $formNode->getId() ) {
                $formNodeId = $formNode->getId();
            } else {
                continue;
            }

            if( $this->isFormNodeInArray($formNodeId,$resArr) ) {
                continue;
            }

            if( $this->testing ) {
                echo "<br>Check formNode: holder=" . $formNodeHolderEntity->getName() . "; formnode=" . $formNode->getName() . "; objecttype=" . $formNode->getObjectTypeName() . ":". $formNode->getObjectTypeId() . "<br>";
            }

//            $parentFormNode = $this->getParentFormNodeSection($formNodeHolderEntity,$formNode);
//            if( $parentFormNode ) {
//                $parentFormNodeId = $parentFormNode->getId();
//            } else {
//                $parentFormNodeId = null;
//            }

            $parentFormNodeId = null;
            $arraySectionCount = null;
            $parentFormNode = $formNode->getParent();

//            if( $parentFormNode ) {
//
////                //get array section count i.e. 0-1
////                $arraySectionCount = $formNodeUtil->getArraySectionCount($parentFormNode,$arraySectionCount,$this->testing);
//
////                //insert parent nested sections to resulting from node array
////                $resArr = $this->createParentFormSectionTemplateRecursively($formNodeHolderEntity, $formNode, $resArr);
//
//                //get common (merged) parent section
//                $parentFormNode = $this->getParentFormNodeSection($formNodeHolderEntity,$formNode);
//
//                //$lastResArr = $resArr[count($resArr)-1];
//                //$parentFormNodeId = $lastResArr['formNodeId'];
//
//                if( $parentFormNode ) {
//                    $parentFormNodeId = $parentFormNode->getId();
//                }
//            }

            //find FormNode value by entityNamespace, entityName, entityId
            $formNodeValue = null;
            $receivingEntity = null;
            if( $entityId ) {
                $mapper = array(
                    'entityNamespace' => $entityNamespace,
                    'entityName' => $entityName, //"Message"
                    'entityId' => $entityId,
                );
                $complexRes = $formNodeUtil->getFormNodeValueByFormnodeAndReceivingmapper($formNode,$mapper,false,$cycle);
                if( $complexRes ) {
                    $formNodeValue = $complexRes['formNodeValue'];
                    $receivingEntity = $complexRes['receivingEntity'];
                }
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

                /////////////// TODO: create additional sections when show submitted entry ///////////////
                foreach( $formNodeValue as $formNodeValueArr ) {
                    $formNodeValue = $formNodeValueArr['formNodeValue'];
                    $arraySectionCount = $formNodeValueArr['arraySectionIndex']; //in DB arraySectionCount named as arraySectionIndex

                    if( $this->testing ) {
                        echo "ArraySection arraySectionCount=" . $arraySectionCount . "<br>";
                    }

//                    if( $arraySectionCount ) {
//                        $formNodeId = $formNodeId.'_'.$arraySectionCount;
//                        if( $parentFormNodeId ) {
//                            $parentFormNodeId = $parentFormNodeId.'_'.$arraySectionCount;
//                        }
//                    }
                    //$formNodeId = $formNodeUtil->getFormNodeIdWithSectionCount($formNodeId,$arraySectionCount);
//                    if( $parentFormNodeId ) {
//                        $newParentFormNodeId = $formNodeUtil->getFormNodeIdWithSectionCount($parentFormNodeId,$arraySectionCount);
//                    }

                    //append prefix to clean array section count: 0_0 => prefix_0_0_prefix
                    $arraySectionCount = $formNodeUtil->gePrefixedtArraySectionCount($arraySectionCount);

                    //insert parent nested sections to resulting from node array
                    $resArr = $this->createParentFormSectionTemplateRecursively($formNodeHolderEntity, $formNode, $resArr, $arraySectionCount, $cycle);

                    //get common (merged) parent section
                    $parentFormNode = $this->getParentFormNodeSection($formNodeHolderEntity,$formNode);

                    if( $parentFormNode ) {
                        $parentFormNodeId = $parentFormNode->getId();
                    }

                    //process userWrapper case
                    $formNodeValue = $formNodeUtil->processFormNodeValue($formNode,$receivingEntity,$formNodeValue);

                    $formNodeArr = array(
                        'formNode' => $formNode,
                        'formNodeId' => $formNodeId,
                        'formNodeHolderEntity' => $formNodeHolderEntity,
                        'receivingEntity' => $receivingEntity,
                        'cycle' => $cycle,
                        'formNodeValue' => $formNodeValue,
                        'single' => $this->single,
                        'arraySectionCount' => $arraySectionCount,
                        //'arraySectionIndex' => null
                    );

                    $template = $this->render('AppUserdirectoryBundle/FormNode/formnode_fields.html.twig', $formNodeArr)->getContent();

                    //form form node array element
                    if( $parentFormNodeId ) {
                        $newParentFormNodeId = $formNodeUtil->getFormNodeIdWithSectionCount($parentFormNodeId,$arraySectionCount);
                    }

                    $res = array(
                        'formNodeHolderId' => $formNodeHolderId,
                        'parentFormNodeId' => $newParentFormNodeId,
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

                //////////////// Regular form node /////////////////////

                if( $parentFormNode ) {

                    //get array section count i.e. 0-1
                    $arraySectionCount = $formNodeUtil->getArraySectionCount($parentFormNode,$arraySectionCount,$this->testing);
                    if( $this->testing ) {
                        echo "Regular arraySectionCount=" . $arraySectionCount . "<br>";
                    }

                    //insert parent nested sections to resulting from node array
                    $resArr = $this->createParentFormSectionTemplateRecursively($formNodeHolderEntity, $formNode, $resArr, $arraySectionCount, $cycle);

                    //get common (merged) parent section
                    $parentFormNode = $this->getParentFormNodeSection($formNodeHolderEntity,$formNode);

                    if( $parentFormNode ) {
                        $parentFormNodeId = $parentFormNode->getId();
                    }
                }

                //process userWrapper case
                $formNodeValue = $formNodeUtil->processFormNodeValue($formNode,$receivingEntity,$formNodeValue);

                $formNodeArr = array(
                    'formNode' => $formNode,
                    'formNodeId' => $formNodeId,
                    'formNodeHolderEntity' => $formNodeHolderEntity,
                    'receivingEntity' => $receivingEntity,
                    'cycle' => $cycle,
                    'formNodeValue' => $formNodeValue,
                    'single' => $this->single,
                    'arraySectionCount' => $arraySectionCount,
                    //'arraySectionIndex' => null
                );

                $template = $this->render('AppUserdirectoryBundle/FormNode/formnode_fields.html.twig', $formNodeArr)->getContent();

                //form form node array element
                if( $parentFormNodeId ) {
                    $newParentFormNodeId = $formNodeUtil->getFormNodeIdWithSectionCount($parentFormNodeId,$arraySectionCount);
                }

                $res = array(
                    'formNodeHolderId' => $formNodeHolderId,
                    'parentFormNodeId' => $newParentFormNodeId,
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
                //////////////// EOF Regular form node /////////////////////
            }//if

        }//foreach

        if( $this->testing ) {
            if(0) {
                print "<pre>";
                print_r($resArr);
                print "</pre>";
            } else {
                foreach( $resArr as $res ) {
                    $res['formNodeHtml'] = 'html';
                    print "<pre>";
                    print_r($res);
                    print "</pre>";
                }
            }
            exit('testing');
        }

        $json = json_encode($resArr);
        $response = new Response($json);
        $response->headers->set('Content-Type', 'application/json');
        return $response;


//        $template = "OK";
        //$showUserArr = $this->showUser($userid,$this->getParameter('employees.sitename'),false);

        //$template = $this->render('AppUserdirectoryBundle/Profile/edit_user_only.html.twig',$showUserArr)->getContent();

//        $json = json_encode($template);
//        $response = new Response($json);
//        $response->headers->set('Content-Type', 'application/json');
//        return $response;
    }

    /**
     * Used by fellowship application Screening Questions
     * This if function gets the html for all form nodes
     * by getRecursionAllFormNodes without using
     * $holderForms = $formNodeHolderEntity->getFormNodes();
     */
    #[Route(path: '/formnode-fields-from-parent/', name: 'employees_formnode_fields_from_parent', methods: ['GET', 'POST'], options: ['expose' => true])]
    #[Template('AppUserdirectoryBundle/FormNode/formnode_fields.html.twig')]
    public function getFormNodesFieldsFromParentsAction( Request $request )
    {
        //exit("getFormNodesFieldsFromParentsAction start");
        //if( false === $this->isGranted('ROLE_USER') ) {
        //    return $this->redirect( $this->generateUrl('employees-nopermission') );
        //}

        $formNodeUtil = $this->container->get('user_formnode_utility');
        $em = $this->getDoctrine()->getManager();

        $cycle = $request->query->get('cycle');
        if( false === $this->isGranted('ROLE_USER') && $cycle != 'new' ) {
            return $this->redirect( $this->generateUrl('employees-nopermission') );
        }

        //formnode's holder (MessageCategory)
        $holderNamespace = $request->query->get('holderNamespace');
        $holderName = $request->query->get('holderName');
        $holderId = $request->query->get('holderId');

        //receiving list's entityName (Message)
        $entityNamespace = $request->query->get('entityNamespace'); //"App\\FellAppBundle\\Entity"
        $entityName = $request->query->get('entityName'); //"FellowshipApplication";
        $entityId = $request->query->get('entityId'); //"FellowshipApplication ID";

        //add to url: &testing=true
        $testing = $request->query->get('testing');
        if( $testing ) {
            $this->testing = true;
        }

        $logger = $this->container->get('logger');
        $logger->notice("getFormNodesFieldsFromParentsAction: holderNamespace=$holderNamespace, holderName=$holderName, holderId=$holderId");

        //echo "entityNamespace=".$entityNamespace."<br>";
        //echo "entityName=".$entityName."<br>";
        //echo "entityId=".$entityId."<br>";

        if( !$holderNamespace || !$holderName || !$holderId ) {
            //echo "no holder namespace and name";
            return null;
        }

        $holderFullName = $holderNamespace . "\\" . $holderName;
        $formNodeHolderEntity = $em->getRepository($holderFullName)->find($holderId);
        if( !$formNodeHolderEntity ) {
            throw new \Exception( 'Entity not found: holderFullName='.$holderFullName.'; holderId='.$holderId );
        }
        $logger->notice("getFormNodesFieldsFromParentsAction: holderFullName=$holderFullName: formNodeHolderEntity ID=".$formNodeHolderEntity->getId());
        $logger->notice("getFormNodesFieldsFromParentsAction: formNodeHolderEntity->getName()=".$formNodeHolderEntity->getName().", formNodeHolderEntity->getId()=".$formNodeHolderEntity->getId());

        $formNodeHolderId = $formNodeHolderEntity->getId();
        $resArr = array();

        if( $testing ) {
            echo "cycle=" . $cycle . "<br>";
        }

        //Testing: create dummy MessageCategory
        //"Fellowship Screening Questions"
        //TODO: pass parent $formNode to this function
        $formNode = $em->getRepository(FormNode::class)->findOneByName("Fellowship Screening Questions Form");
        if( !$formNode ) {
            exit('FormNode not found by "Fellowship Screening Questions"');
        }
        //echo "formNode=".$formNode->getId()."<br>";
        //$formNodeHolderEntity = new MessageCategory();
        //$formNodeHolderEntity->addFormNode($formNode);
        //$holderForms = array($formNode);
        //$formNodes = array();
        //assume only one form attached to the message category holder
        $formNodes = $formNodeUtil->getRecursionAllFormNodes($formNode,$formNodes=array(),'real',$cycle);
        //dump($formNodes);
        //exit('getFormNodesFieldsFromParentsAction');

        //$formNodes = $formNodeHolderEntity->getFormNodes();
        //get only 'real' fields as $formNodes
        //$formNodes = $formNodeUtil->getAllRealFormNodes($formNodeHolderEntity,$cycle);

        //reverse array to show the fields backwards for show and edit, otherwise the order of submitted form fields is reversed.
        //if( $cycle != "new" ) {
        //test by link (Test: MessageCategory&holderId=70):
        // http://localhost/order/directory/formnode-fields/?holderNamespace=App\OrderformBundle\Entity&holderName=MessageCategory&holderId=70&entityNamespace=App\OrderformBundle\Entity&entityName=Message&entityId=222&cycle=show&testing=true
        //One way to solve it: for show and edit - start calling "formnode-fields" from top to bottom. On show page, this done in opposite way - from bottom to top.
        //for show use reverse array (don't use it for top to bottom combobox  processing)
        //$formNodes = array_reverse($formNodes);
        //}

        foreach( $formNodes as $formNode ) {

            if( $this->testing ) {
                echo "<br>###################### ".$formNode->getId()." ################<br>";
                echo "############# formNode: holder=" . $formNodeHolderEntity->getName() . "; formnode=" . $formNode->getName() . "; objecttype=" . $formNode->getObjectTypeName() . ":". $formNode->getObjectTypeId() . "<br>";
            }
            $logger->notice("getFormNodeFieldsAction: formNode->getName()=".$formNode->getName().", formNode->getId()=".$formNode->getId().", formNode->getObjectTypeId()=".$formNode->getObjectTypeId());

            if( $formNode && $formNode->getId() ) {
                $formNodeId = $formNode->getId();
            } else {
                continue;
            }

            if( $this->isFormNodeInArray($formNodeId,$resArr) ) {
                continue;
            }

            if( $this->testing ) {
                echo "<br>Check formNode: holder=" . $formNodeHolderEntity->getName() . "; formnode=" . $formNode->getName() . "; objecttype=" . $formNode->getObjectTypeName() . ":". $formNode->getObjectTypeId() . "<br>";
            }

//            $parentFormNode = $this->getParentFormNodeSection($formNodeHolderEntity,$formNode);
//            if( $parentFormNode ) {
//                $parentFormNodeId = $parentFormNode->getId();
//            } else {
//                $parentFormNodeId = null;
//            }

            $parentFormNodeId = null;
            $arraySectionCount = null;
            $parentFormNode = $formNode->getParent();

//            if( $parentFormNode ) {
//
////                //get array section count i.e. 0-1
////                $arraySectionCount = $formNodeUtil->getArraySectionCount($parentFormNode,$arraySectionCount,$this->testing);
//
////                //insert parent nested sections to resulting from node array
////                $resArr = $this->createParentFormSectionTemplateRecursively($formNodeHolderEntity, $formNode, $resArr);
//
//                //get common (merged) parent section
//                $parentFormNode = $this->getParentFormNodeSection($formNodeHolderEntity,$formNode);
//
//                //$lastResArr = $resArr[count($resArr)-1];
//                //$parentFormNodeId = $lastResArr['formNodeId'];
//
//                if( $parentFormNode ) {
//                    $parentFormNodeId = $parentFormNode->getId();
//                }
//            }

            //find FormNode value by entityNamespace, entityName, entityId
            $formNodeValue = null;
            $receivingEntity = null;
            if( $entityId ) {
                $mapper = array(
                    'entityNamespace' => $entityNamespace,
                    'entityName' => $entityName, //"Message"
                    'entityId' => $entityId,
                );
                $complexRes = $formNodeUtil->getFormNodeValueByFormnodeAndReceivingmapper($formNode,$mapper,false,$cycle);
                if( $complexRes ) {
                    $formNodeValue = $complexRes['formNodeValue'];
                    $receivingEntity = $complexRes['receivingEntity'];
                }
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

                /////////////// TODO: create additional sections when show submitted entry ///////////////
                foreach( $formNodeValue as $formNodeValueArr ) {
                    $formNodeValue = $formNodeValueArr['formNodeValue'];
                    $arraySectionCount = $formNodeValueArr['arraySectionIndex']; //in DB arraySectionCount named as arraySectionIndex

                    if( $this->testing ) {
                        echo "ArraySection arraySectionCount=" . $arraySectionCount . "<br>";
                    }

//                    if( $arraySectionCount ) {
//                        $formNodeId = $formNodeId.'_'.$arraySectionCount;
//                        if( $parentFormNodeId ) {
//                            $parentFormNodeId = $parentFormNodeId.'_'.$arraySectionCount;
//                        }
//                    }
                    //$formNodeId = $formNodeUtil->getFormNodeIdWithSectionCount($formNodeId,$arraySectionCount);
//                    if( $parentFormNodeId ) {
//                        $newParentFormNodeId = $formNodeUtil->getFormNodeIdWithSectionCount($parentFormNodeId,$arraySectionCount);
//                    }

                    //append prefix to clean array section count: 0_0 => prefix_0_0_prefix
                    $arraySectionCount = $formNodeUtil->gePrefixedtArraySectionCount($arraySectionCount);

                    //insert parent nested sections to resulting from node array
                    $resArr = $this->createParentFormSectionTemplateRecursively($formNodeHolderEntity, $formNode, $resArr, $arraySectionCount, $cycle);

                    //get common (merged) parent section
                    $parentFormNode = $this->getParentFormNodeSection($formNodeHolderEntity,$formNode);

                    if( $parentFormNode ) {
                        $parentFormNodeId = $parentFormNode->getId();
                    }

                    //process userWrapper case
                    $formNodeValue = $formNodeUtil->processFormNodeValue($formNode,$receivingEntity,$formNodeValue);

                    $formNodeArr = array(
                        'formNode' => $formNode,
                        'formNodeId' => $formNodeId,
                        'formNodeHolderEntity' => $formNodeHolderEntity,
                        'receivingEntity' => $receivingEntity,
                        'cycle' => $cycle,
                        'formNodeValue' => $formNodeValue,
                        'single' => $this->single,
                        'arraySectionCount' => $arraySectionCount,
                        //'arraySectionIndex' => null
                    );

                    $template = $this->render('AppUserdirectoryBundle/FormNode/formnode_fields.html.twig', $formNodeArr)->getContent();

                    //form form node array element
                    $newParentFormNodeId = null;
                    if( $parentFormNodeId ) {
                        $newParentFormNodeId = $formNodeUtil->getFormNodeIdWithSectionCount($parentFormNodeId,$arraySectionCount);
                    }

                    $res = array(
                        'formNodeHolderId' => $formNodeHolderId,
                        'parentFormNodeId' => $newParentFormNodeId,
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

                //////////////// Regular form node /////////////////////

                if( $parentFormNode ) {

                    //get array section count i.e. 0-1
                    $arraySectionCount = $formNodeUtil->getArraySectionCount($parentFormNode,$arraySectionCount,$this->testing);
                    if( $this->testing ) {
                        echo "Regular arraySectionCount=" . $arraySectionCount . "<br>";
                    }

                    //insert parent nested sections to resulting from node array
                    $resArr = $this->createParentFormSectionTemplateRecursively($formNodeHolderEntity, $formNode, $resArr, $arraySectionCount, $cycle);

                    //get common (merged) parent section
                    $parentFormNode = $this->getParentFormNodeSection($formNodeHolderEntity,$formNode);

                    if( $parentFormNode ) {
                        $parentFormNodeId = $parentFormNode->getId();
                    }
                }

                //process userWrapper case
                $formNodeValue = $formNodeUtil->processFormNodeValue($formNode,$receivingEntity,$formNodeValue);

                $formNodeArr = array(
                    'formNode' => $formNode,
                    'formNodeId' => $formNodeId,
                    'formNodeHolderEntity' => $formNodeHolderEntity,
                    'receivingEntity' => $receivingEntity,
                    'cycle' => $cycle,
                    'formNodeValue' => $formNodeValue,
                    'single' => $this->single,
                    'arraySectionCount' => $arraySectionCount,
                    //'arraySectionIndex' => null
                );

                $template = $this->render('AppUserdirectoryBundle/FormNode/formnode_fields.html.twig', $formNodeArr)->getContent();

                //form form node array element
                $newParentFormNodeId = null;
                if( $parentFormNodeId ) {
                    $newParentFormNodeId = $formNodeUtil->getFormNodeIdWithSectionCount($parentFormNodeId,$arraySectionCount);
                }

                $res = array(
                    'formNodeHolderId' => $formNodeHolderId,
                    'parentFormNodeId' => $newParentFormNodeId,
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
                //////////////// EOF Regular form node /////////////////////
            }//if

        }//foreach

        if( $this->testing ) {
            if(0) {
                print "<pre>";
                print_r($resArr);
                print "</pre>";
            } else {
                foreach( $resArr as $res ) {
                    $res['formNodeHtml'] = 'html';
                    print "<pre>";
                    print_r($res);
                    print "</pre>";
                }
            }
            exit('testing');
        }

        $json = json_encode($resArr);
        $response = new Response($json);
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    //create recursively $formNodeArr containing
    public function createParentFormSectionTemplateRecursively( $formNodeHolderEntity, $formNode, $resArr, $arraySectionCount, $cycle ) {

        $formNodeHolderId = $formNodeHolderEntity->getId();
        if( !$formNodeHolderId ) {
            return $resArr;
        }

        $formNodeUtil = $this->container->get('user_formnode_utility');

        //check if the node has a parent form node type of Section and visible. The node will be placed by JS inside this section
        $parentFormNode = $this->getParentFormNodeSection($formNodeHolderEntity,$formNode);

        if( $parentFormNode ) {

            $parentFormNodeId = $formNodeUtil->getFormNodeIdWithSectionCount($parentFormNode->getId(),$arraySectionCount);

            if( $this->isFormNodeInArray($parentFormNodeId,$resArr) ) {
                return $resArr;
            }

            //only for array sections: get index of this array section from the top "Form". Use $arraySectionCount: count number of indexes (0=>1, 0-1=>2)
            //$arraySectionIndex = $formNodeUtil->getArraySectionIndexByHolderTreeRecursion($formNodeHolderEntity,$parentFormNode);
//            if( $arraySectionCount !== "" && $arraySectionCount !== null ) {
//                $arraySectionCountArr = explode('-',$arraySectionCount);
//                $arraySectionIndex = count($arraySectionCountArr);
//                if( $arraySectionIndex > 0 ) {
//                    $arraySectionIndex = $arraySectionIndex - 1;
//                } else {
//                    $arraySectionIndex = 0;
//                }
//            }

            if( $this->testing ) {
                echo "<br>######## Add Parent: ".$parentFormNode->getId()." #######<br>";
                //echo "Regular arraySectionCount=" . $arraySectionCount . "<br>";
            }

            //$arraySectionCount = null;
            //$formNodeUtil = $this->container->get('user_formnode_utility');
            //$arraySectionCount = $formNodeUtil->getArraySectionCount($parentFormNode,$arraySectionCount,$this->testing);

//            if( $arraySectionCount ) {
//                $parentFormNodeId = $parentFormNode->getId().'_'.$arraySectionCount;
//            } else {
//                $parentFormNodeId = $parentFormNode->getId();
//            }
            //$parentFormNodeId = $formNodeUtil->getFormNodeIdWithSectionCount($parentFormNode->getId(),$arraySectionCount);

            $formNodeArr = array(
                'formNode' => $parentFormNode,
                'formNodeId' => $parentFormNodeId,
                'formNodeHolderEntity' => $formNodeHolderEntity,
                'receivingEntity' => null,
                'cycle' => $cycle,  //'edit',
                'formNodeValue' => null,
                'single' => $this->single,
                'arraySectionCount' => $arraySectionCount,
                //'arraySectionIndex' => $arraySectionIndex
            );

            $template = $this->render('AppUserdirectoryBundle/FormNode/formnode_fields.html.twig', $formNodeArr)->getContent();

            $parentArraySectionCount = 0;
            $grandParentFormNode = $this->getParentFormNodeSection($formNodeHolderEntity,$parentFormNode);
            if( $grandParentFormNode ) {

                if( $formNodeUtil->isUnderArraySectionRecursion($grandParentFormNode,$this->testing) ) {
                    $cleanArraySectionCount = $formNodeUtil->getCleanedArraySection($arraySectionCount);
                    $arraySectionCountArr = explode('-', $cleanArraySectionCount);
                    //echo "arraySectionCountArr count=".count($arraySectionCountArr)."<br>";
                    array_pop($arraySectionCountArr);
                    $parentArraySectionCount = implode('-', $arraySectionCountArr);
                    $parentArraySectionCount = $formNodeUtil->gePrefixedtArraySectionCount($parentArraySectionCount);
                    $grandParentFormNodeId = $formNodeUtil->getFormNodeIdWithSectionCount($grandParentFormNode->getId(), $parentArraySectionCount);
                } else {
                    $grandParentFormNodeId = $grandParentFormNode->getId();
                }
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
                'arraySectionCount' => $arraySectionCount,
                //'arraySectionIndex' => $arraySectionIndex
            );

            $resArr[] = $res;

            return $this->createParentFormSectionTemplateRecursively( $formNodeHolderEntity, $parentFormNode, $resArr, $parentArraySectionCount, $cycle );

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
        $formNodeUtil = $this->container->get('user_formnode_utility');
        return $formNodeUtil->getParentFormNodeSection($formNodeHolderEntity,$formNode);

//        $parentFormNode = $formNode->getParent();
//
//        if( $parentFormNode && $parentFormNode->getId() && $formNodeUtil->isValidFormSection($parentFormNode) ) {
//            $parentFormNodeName = $parentFormNode->getName();
//            $objectTypeName = $parentFormNode->getObjectTypeName();
//            $objectTypeId = $parentFormNode->getObjectTypeId();
//            $thisTesting = $this->testing;
//            $thisTesting = false;
//            $topParentFormSection = $formNodeUtil->getTopFormSectionByHolderTreeRecursion($formNodeHolderEntity, $parentFormNodeName, $objectTypeId, $thisTesting);
//            if ($topParentFormSection) {
//                if ($this->testing) {
//                    echo '### topParentFormSection=' . $topParentFormSection . "; formnode=" . $formNode->getName() . " ($parentFormNodeName, $objectTypeName:$objectTypeId)" . "<br>";
//                }
//                return $topParentFormSection;
//            }
//        }

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


    public function isFormNodeInArray( $formNodeId, $resArr ) {
        foreach( $resArr as $res ) {
            if( $res['formNodeId'] == $formNodeId ) {
                return true;
            }
        }
        return false;
    }



    /**
     * Use: https://github.com/Atlantic18/DoctrineExtensions/blob/v2.4.x/doc/tree.md
     */
    #[Route(path: '/form-node-tree-test/', name: 'employees_form-node-tree-test', methods: ['GET'])]
    public function formNodeTestAction(Request $request)
    {
        if( false === $this->isGranted('ROLE_PLATFORM_DEPUTY_ADMIN') ) {
            return $this->redirect( $this->generateUrl($this->getParameter('employees.sitename').'-nopermission') );
        }

        $em = $this->getDoctrine()->getManager();

        $mapper = array(
            'prefix' => "App",
            'className' => "FormNode",
            'bundleName' => "UserdirectoryBundle",
            'fullClassName' => "App\\UserdirectoryBundle\\Entity\\FormNode",
            'entityNamespace' => "App\\UserdirectoryBundle\\Entity"
        );

        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:FormNode'] by [FormNode::class]
        $repo = $em->getRepository(FormNode::class);

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