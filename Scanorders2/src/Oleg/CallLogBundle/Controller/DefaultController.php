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

namespace Oleg\CallLogBundle\Controller;

use Oleg\CallLogBundle\Form\CalllogMessageCacheType;
use Oleg\OrderformBundle\Entity\Message;
use Oleg\UserdirectoryBundle\Entity\ObjectTypeText;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;


class DefaultController extends Controller
{

    /**
     * @Route("/about", name="calllog_about_page")
     * @Template("OlegUserdirectoryBundle:Default:about.html.twig")
     */
    public function aboutAction(Request $request)
    {
        return array('sitename' => $this->container->getParameter('calllog.sitename'));
    }



//    /**
//     * Alerts
//     * @Route("/alerts/", name="calllog_alerts")
//     * @Template("OlegCallLogBundle:Default:under_construction.html.twig")
//     */
//    public function alertsAction(Request $request)
//    {
//        return;
//    }


    /**
     * Resources
     * @Route("/resources/", name="calllog_resources")
     * @Template("OlegCallLogBundle:CallLog:resources.html.twig")
     */
    public function resourcesAction(Request $request)
    {
        if( false === $this->get('security.authorization_checker')->isGranted('ROLE_CALLLOG_USER') ) {
            return $this->redirect( $this->generateUrl('calllog-nopermission') );
        }

        //return $this->redirectToRoute('user_admin_index');

        //testing
        //metaphone (if enabled)
        //$userServiceUtil = $this->get('user_service_utility');
        //$userServiceUtil->metaphoneTest();

//        $msg = "Notify Test!!!";
//        $this->get('session')->getFlashBag()->add(
//            'notice',
//            $msg
//        );
//            $this->get('session')->getFlashBag()->add(
//                'pnotify',
//                $msg
//            );

        $em = $this->getDoctrine()->getManager();
        $entities = $em->getRepository('OlegUserdirectoryBundle:SiteParameters')->findAll();

        if( count($entities) != 1 ) {
            throw new \Exception( 'Must have only one parameter object. Found '.count($entities).'object(s)' );
        }

        $entity = $entities[0];

        $resourcesText = $entity->getCalllogResources();

        return array(
            //'entity' => $entity,
            //'form' => $form->createView(),
            //'cycle' => $cycle,
            'title' => "Resources",
            'resourcesText' => $resourcesText
        );
    }


//    /**
//     * Resources
//     * @Route("/check-encounter-location/", name="calllog_check_encounter_location", options={"expose"=true})
//     * @Method("POST")
//     */
//    public function checkLocationAction(Request $request)
//    {
//        exit("Not used");
//    }


    /**
     * http://localhost/order/call-log-book/assign-calllog-users
     * This is one time run method to assign the calllog roles
     * @Route("/assign-calllog-users", name="calllog_assign-calllog-users")
     */
    public function assignUsersAction(Request $request)
    {
        if( false === $this->get('security.authorization_checker')->isGranted('ROLE_PLATFORM_DEPUTY_ADMIN') ) {
            return $this->redirect($this->generateUrl('employees-nopermission'));
        }

        $em = $this->getDoctrine()->getManager();

        $user = $this->get('security.token_storage')->getToken()->getUser();

        $inputFileName = __DIR__ . '/../../../../../importUserLists/Calllog_Users.xlsx';

        try {
            $inputFileType = \PhpOffice\PhpSpreadsheet\IOFactory::identify($inputFileName);
            $objReader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader($inputFileType);
            $objPHPExcel = $objReader->load($inputFileName);
        } catch(Exception $e) {
            die('Error loading file "'.pathinfo($inputFileName,PATHINFO_BASENAME).'": '.$e->getMessage());
        }

        $sheet = $objPHPExcel->getSheet(0);
        $highestRow = $sheet->getHighestRow();
        $highestColumn = $sheet->getHighestColumn();

        $attendingCount = 0;
        $residentCount = 0;
        $fellowCount = 0;

        //for each row in excel
        for( $row = 2; $row <= $highestRow; $row++ ) {

            //  Read a row of data into an array
            $rowData = $sheet->rangeToArray(
                'A' . $row . ':' . $highestColumn . $row,
                NULL,
                TRUE,
                FALSE
            );

            //echo $row.": ";
            //var_dump($rowData);
            //echo "<br>";

            $attendingUserStr = trim($rowData[0][0]);
            $attendingUserCwid = trim($rowData[0][1]);
            //echo "attendingUserStr=".$attendingUserStr."<br>";
            //echo "attendingUserCwid=".$attendingUserCwid."<br>";
            $attendingCount = $this->assignRoleToUser($attendingUserStr,$attendingUserCwid,"ROLE_CALLLOG_PATHOLOGY_ATTENDING",$attendingCount);

            $residentUserStr = trim($rowData[0][2]);
            $residentUserCwid = trim($rowData[0][3]);
            //echo "residentUserStr=".$residentUserStr."<br>";
            //echo "residentUserCwid=".$residentUserCwid."<br>";
            $residentCount = $this->assignRoleToUser($residentUserStr,$residentUserCwid,"ROLE_CALLLOG_PATHOLOGY_RESIDENT",$residentCount);

            $fellowUserStr = trim($rowData[0][4]);
            $fellowUserCwid = trim($rowData[0][5]);
            //echo "fellowUserStr=".$fellowUserStr."<br>";
            //echo "fellowUserCwid=".$fellowUserCwid."<br>";
            $fellowCount = $this->assignRoleToUser($fellowUserStr,$fellowUserCwid,"ROLE_CALLLOG_PATHOLOGY_FELLOW",$fellowCount);

            //exit("end of row $row");
        } //for loop

        exit("attendingCount=".$attendingCount."; residentCount=".$residentCount."; fellowCount=".$fellowCount);
    }

    public function assignRoleToUser( $userStr, $cwid, $roleStr, $count ) {
        if( $userStr ) {
            $attendingUser = $this->getUserByStrOrCwid($userStr,$cwid);
            //echo $roleStr.": ".$attendingUser;
            if( !$attendingUser ) {
                //echo " NOT FOUND!!!<br>";
                echo "User not found by [$userStr] [$cwid] [$roleStr]<br>";
                return $count;
            } else {
                //echo "<br>";
            }

            if( $attendingUser ) {
                $em = $this->getDoctrine()->getManager();
                $role = $em->getRepository('OlegUserdirectoryBundle:Roles')->findOneByName($roleStr);
                if ($role) {
                    if (!$attendingUser->hasRole($roleStr)) {
                        $attendingUser->addRole($roleStr);
                        //save
                        $em->flush($attendingUser);
                        echo "Role $roleStr has been assigned to user " . $attendingUser . "<br>";
                        $count++;
                    } else {
                        //echo "###Role $roleStr already exists in user ".$attendingUser."<br>";
                    }
                } else {
                    exit("Role not found by name $roleStr");
                }
            }
        }
        return $count;
    }
    public function getUserByStrOrCwid( $userStr, $cwid ) {
        //echo "Trying to find by [$userStr] [$cwid]: ";
        $user = $this->getUserByDisplayName($userStr);
        if( $user ) {
            return $user;
        } else {
            $user = $this->getUserByCwid($cwid);
            if( $user ) {
                return $user;
            }
        }
        //echo "!!! User not found by [$userStr] [$cwid] <br>";
        return null;
    }
    public function getUserByDisplayName( $userStr ) {
        $em = $this->getDoctrine()->getManager();
        $repository = $em->getRepository('OlegUserdirectoryBundle:User');
        $dql =  $repository->createQueryBuilder("user");
        $dql->select('user');
        $dql->leftJoin("user.infos", "infos");
        $dql->where("infos.displayName = :displayName");

        $query = $em->createQuery($dql);
        $query->setParameter('displayName', $userStr);

        $users = $query->getResult();
        if( count($users) != 1 ) {
            //echo "No single user found by [$userStr] <br>";
            return null;
        }

        return $users[0];
    }
    public function getUserByCwid( $cwid ) {
        //echo "Trying to find by cwid [$cwid] <br>";
        $usernamePrefix = 'ldap-user';
        $em = $this->getDoctrine()->getManager();
        $user = $em->getRepository('OlegUserdirectoryBundle:User')->findOneByUsername( $cwid."_@_". $usernamePrefix);

        return $user;
    }


    /**
     * http://localhost/order/call-log-book/update-cache-values-now
     * method to populate/update all call log entry cache in XML format
     * @Route("/update-cache-values-now/", name="calllog_update_cache_values_now")
     */
    public function populateEntryCacheAction(Request $request)
    {
        if( false === $this->get('security.authorization_checker')->isGranted('ROLE_PLATFORM_DEPUTY_ADMIN') ) {
            return $this->redirect($this->generateUrl('employees-nopermission'));
        }

        //exit("This is a one time run method");

        $formNodeUtil = $this->get('user_formnode_utility');
        $userSecUtil = $this->get('user_security_utility');

        $user = $this->get('security.token_storage')->getToken()->getUser();
        $em = $this->getDoctrine()->getManager();

        $messageIds = array();

        $testing = false;
        //$testing = true;
        $forceUpdate = true;
        //$forceUpdate = false;

        $repository = $em->getRepository('OlegOrderformBundle:Message');

        $dql =  $repository->createQueryBuilder("message");
        $dql->select('message');

        if( !$forceUpdate ) {
            $dql->where("message.formnodesCache IS NULL");
        }

        //$dql->setMaxResults(100);

        $query = $em->createQuery($dql);

        $messages = $query->getResult();
        //echo "Messages to update count=".count($messages)."<br>";

        foreach( $messages as $message ) {

            //forceUpdate or message does not have formNodeCache
            if( $forceUpdate || !$message->getFormnodesCache() ) {
                $res = $formNodeUtil->updateFieldsCache($message, $testing);
            }

            if( !$res) {
                exit("Error updating cache");
            }

            $messageIds[] = $res;
        }

        $msg = "Call Log cache has been updated for " . count($messages) . " Call Log Entries";

        //Event Log
        if( count($messages) ) {
            $eventType = "Call Log Cache Updated";
            $msgLog = $msg . ":<br>" . implode(", ",$messageIds);
            $userSecUtil->createUserEditEvent($this->container->getParameter('calllog.sitename'), $msgLog, $user, null, $request, $eventType);
        }

        $this->get('session')->getFlashBag()->add(
            'pnotify',
            $msg
        );

        return $this->redirect( $this->generateUrl('calllog_home') );
        //exit($msg);
    }


    /**
     * @Route("/update-cache-manually/{id}", name="calllog_update_cache_manually")
     * @Template("OlegCallLogBundle:CallLog:update-cache-manually.html.twig")
     */
    public function updateCacheManuallyAction(Request $request, Message $message)
    {
        if( false === $this->get('security.authorization_checker')->isGranted('ROLE_CALLLOG_ADMIN') ) {
            return $this->redirect($this->generateUrl('employees-nopermission'));
        }

        $userSecUtil = $this->get('user_security_utility');
        $em = $this->getDoctrine()->getManager();
        $user = $this->get('security.token_storage')->getToken()->getUser();

        $params = array();

        $form = $this->createForm(
            CalllogMessageCacheType::class,
            $message,
            array(
                'form_custom_value' => $params,
                'form_custom_value_entity' => $message
            )
        );

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            // $entityManager->persist($task);
            $em->flush($message);

            $msg = "Call Log cache has been manually updated for " . $message->getOid();

            $this->get('session')->getFlashBag()->add(
                'pnotify',
                $msg
            );

            //Event Log
            $eventType = "Call Log Cache Updated Manually";
            $userSecUtil->createUserEditEvent($this->container->getParameter('calllog.sitename'), $msg, $user, $message, $request, $eventType);

            return $this->redirect($this->generateUrl('calllog_callentry_view', array(
                'messageOid' => $message->getOid(),
                'messageVersion' => $message->getVersion()
            )));
        }

        return array(
            'form' => $form->createView(),
            'message' => $message,
            'title' => "Update Cache Manually for Call Log Entry ID " . $message->getOid()
        );
    }


    /**
     * @Route("/update-text-html", name="calllog_update_text_html")
     */
    public function updateTextHtmlAction(Request $request)
    {
        if (false === $this->get('security.authorization_checker')->isGranted('ROLE_PLATFORM_DEPUTY_ADMIN')) {
            return $this->redirect($this->generateUrl('employees-nopermission'));
        }

        set_time_limit(600); //600 seconds => 10 mins

        $em = $this->getDoctrine()->getManager();
        $formNodeUtil = $this->get('user_formnode_utility');
        $userSecUtil = $this->get('user_security_utility');
        $user = $this->get('security.token_storage')->getToken()->getUser();

        //$objectTypeText = $formNodeUtil->getObjectTypeByName('Form Field - Free Text, HTML');

        $historySourceFormNode = $this->getSourceFormNodeByName("History/Findings");
        if( !$historySourceFormNode ) {
            exit("Error: no source form node History/Findings");
        }
        $impressionSourceFormNode = $this->getSourceFormNodeByName("Impression/Outcome");
        if( !$impressionSourceFormNode ) {
            exit("Error: no source form node Impression/Outcome");
        }

        $historyDestinationFormNode = $this->getDestinationFormNodeByName("History/Findings HTML");
        if( !$historyDestinationFormNode ) {
            exit("Error: no destination form node History/Findings HTML");
        }
        $historyDestinationFormNodeId = $historyDestinationFormNode->getId();

        $impressionDestinationFormNode = $this->getDestinationFormNodeByName("Impression/Outcome HTML");
        if( !$impressionDestinationFormNode ) {
            exit("Error: no destination form node Impression/Outcome HTML");
        }
        $impressionDestinationFormNodeId = $impressionDestinationFormNode->getId();

        //$formNodeHtml = $em->getRepository('OlegUserdirectoryBundle:ObjectTypeText')->findAll();

        //$sourceTextObjects = $em->getRepository('OlegUserdirectoryBundle:FormNode')->findOneByName("History/Findings");
        $repository = $em->getRepository('OlegUserdirectoryBundle:ObjectTypeText');
        $dql = $repository->createQueryBuilder("list");;
        $dql->select('list');
        $dql->leftJoin("list.formNode", "formNode");
        //$dql->leftJoin("list.objectType", "objectType");
        //$dql->leftJoin("list.parent", "parent");
        //$dql->leftJoin("parent.parent", "grandParent");
        //$dql->where("list.level = 4 AND objectType.id = ".$objectTypeText->getId()." AND parent.level = 3 AND grandParent.name = 'Pathology Call Log Entry'");
        //$dql->andWhere("list.name = 'History/Findings' OR list.name = 'Impression/Outcome'");
        $dql->where("formNode.id = " . $historySourceFormNode->getId() . " OR formNode.id = " . $impressionSourceFormNode->getId());
        //$dql->orderBy('list.arraySectionIndex','DESC');
        //$dql->addOrderBy('list.orderinlist', 'ASC');
        $query = $em->createQuery($dql);
        $sourceTextObjects = $query->getResult();
        echo "SourceTextObjects count=".count($sourceTextObjects)."<br>";

        $totalCounter = 0;
        $counter = 0;

        foreach($sourceTextObjects as $textObject) {

            //check if parent is section (level = 3)
//            if( $textObject->getParent() && $textObject->getParent()->getLevel() == 3 ) {
//                //ok
//            } else {
//                echo "Skip this textObject: ".$textObject."<br>";
//                continue;
//            }

            //create a new ObjectTypeText Html
            //echo "Copy this textObject: ".$textObject."<br>";

            $totalCounter++;

            $creator = $textObject->getCreator();
            $createDate = $textObject->getCreatedate();

            $updatedby = $textObject->getUpdatedby();
            $updatedon = $textObject->getUpdatedon();

            $name = $textObject->getName();
            $abbreviation = $textObject->getAbbreviation();
            $shortName = $textObject->getShortname();
            $description = $textObject->getDescription();
            $type = $textObject->getType();

            $updateAuthorRoles = $textObject->getUpdateAuthorRoles();
            $fulltitle = $textObject->getFulltitle();
            $linkToListId = $textObject->getLinkToListId();

            $version = $textObject->getVersion();

            $formValue = $textObject->getValue();
            $formNode = $textObject->getFormNode();

            $entityNamespace = $textObject->getEntityNamespace();
            $entityName = $textObject->getEntityName();
            $entityId = $textObject->getEntityId();

            $arraySectionId = $textObject->getArraySectionId();
            $arraySectionIndex = $textObject->getArraySectionIndex();

            $existingHtmlText = $this->findExistingTextHtmlByName($formNode,$formValue,$historyDestinationFormNodeId,$impressionDestinationFormNodeId,$entityNamespace,$entityName,$entityId);
            if( $existingHtmlText ) {
                echo $totalCounter.": Skipped (".$formNode->getName()."): Text HTML already exists value=[$formValue], existingHtml=[$existingHtmlText]<br>";
                continue;
            }

            //Create new text object
            $textHtmlObject = new ObjectTypeText();

            $count = null;
            $userSecUtil->setDefaultList($textHtmlObject,$count,$creator,$name);

            //Set list parameters
            $textHtmlObject->setCreatedate($createDate);
            $textHtmlObject->setUpdatedby($updatedby);
            $textHtmlObject->setUpdatedon($updatedon);
            $textHtmlObject->setAbbreviation($abbreviation);
            $textHtmlObject->setShortname($shortName);
            $textHtmlObject->setDescription($description);
            $textHtmlObject->setType($type);
            $textHtmlObject->setFulltitle($fulltitle);

            $textHtmlObject->setFulltitle($fulltitle);
            $textHtmlObject->setLinkToListId($linkToListId);
            $textHtmlObject->setVersion($version);
            $textHtmlObject->setFulltitle($fulltitle);
            $textHtmlObject->setFulltitle($fulltitle);
            $textHtmlObject->setUpdateAuthorRoles($updateAuthorRoles);

            //Set ObjectTypeReceivingBase parameters
            $textHtmlObject->setArraySectionId($arraySectionId);
            $textHtmlObject->setArraySectionIndex($arraySectionIndex);

            //3) set message by entityName to the created list
            //$textHtmlObject->setObject($holderEntity);
            $textHtmlObject->setEntityNamespace($entityNamespace);
            $textHtmlObject->setEntityName($entityName);
            $textHtmlObject->setEntityId($entityId);

            $textHtmlObject->setValue($formValue);

            //4) set formnode to the list ("History/Findings" -> )
            //$textHtmlObject->setFormNode($formNodeHtml);

            if( $formNode->getName() == 'History/Findings' ) {
                if( $historyDestinationFormNode ) {
                    $textHtmlObject->setFormNode($historyDestinationFormNode);
                    $msgLog = $counter.": ".$entityId."(".$entityName."): Copy History/Findings html text [$formValue] to formnode [$historyDestinationFormNode]";
                } else {
                    echo $totalCounter.": Skip historyDestinationFormNodeByName not found <br>";
                    continue;
                }
            }
            if( $formNode->getName() == 'Impression/Outcome' ) {
                if( $impressionDestinationFormNode ) {
                    $textHtmlObject->setFormNode($impressionDestinationFormNode);
                    $msgLog = $counter.": ".$entityId."(".$entityName."): Copy Impression/Outcome html text [$formValue] to formnode [$impressionDestinationFormNode]";
                } else {
                    echo $totalCounter.": Skip impressionDestinationFormNodeByName not found <br>";
                    continue;
                }
            }
            
            //echo "textHtmlObject: Namespace=" . $textHtmlObject->getEntityNamespace() . ", Name=" . $textHtmlObject->getEntityName() . ", Value=" . $textHtmlObject->getValue() . "<br>";
            $counter++;

            //$testing = true;
            $testing = false;
            if( !$testing ) {

                //$updateCache = false;
                $updateCache = true;
                if( $updateCache ) {
                    $message = null;
                    if ($entityId) {
                        $message = $em->getRepository('OlegOrderformBundle:Message')->find($entityId);
                        if (!$message) {
                            throw new \Exception("Message is not found by id " . $entityId);
                        }
                        //Save fields as cache in the field $formnodesCache ($holderEntity->setFormnodesCache($text))
                        $testing = false;
                        $formNodeUtil->updateFieldsCache($message, $testing);
                    }
                }

                $em->persist($textHtmlObject);
                $em->flush(); //testing

                //EventLog
                //$eventType = "Call Log Book Entry Updated";
                //$userSecUtil->createUserEditEvent($this->container->getParameter('calllog.sitename'), $msgLog, $user, $message, $request, $eventType);
            }

            echo $msgLog . "<br>";

            if( $totalCounter > 1000 ) {
                //exit("Break processing $totalCounter text objects");
            }

        }//foreach

        exit("Processed $counter text objects");
    }
    public function findExistingTextHtmlByName($formNode,$formValue,$historyDestinationFormNodeId,$impressionDestinationFormNodeId,$entityNamespace,$entityName,$entityId) {

        //return false; //testing

        $em = $this->getDoctrine()->getManager();

        $repository = $em->getRepository('OlegUserdirectoryBundle:ObjectTypeText');
        $dql = $repository->createQueryBuilder("list");;
        $dql->select('list');
        $dql->leftJoin("list.formNode", "formNode");

        if( $formNode->getName() == 'History/Findings' ) {
            $dql->where("formNode.id = " . $historyDestinationFormNodeId);
        }
        if( $formNode->getName() == 'Impression/Outcome' ) {
            $dql->where("formNode.id = " . $impressionDestinationFormNodeId);
        }

        //$dql->andWhere("list.value = '$formValue'");

        $dql->andWhere("list.entityNamespace = '$entityNamespace' AND list.entityName = '$entityName' AND list.entityId = '$entityId'");

        $query = $em->createQuery($dql);
        $destinationTextObjects = $query->getResult();
        //echo "Existing destinationTextObjects count=".count($destinationTextObjects)."<br>";

        //exit("eof");

        if( count($destinationTextObjects) > 0 ) {
            return $destinationTextObjects[0]->getValue();
        }

        return false;
    }
    //$name - "History/Findings", "Impression/Outcome"
    public function getSourceFormNodeByName($name) {
        $em = $this->getDoctrine()->getManager();
        $formNodeUtil = $this->get('user_formnode_utility');

        $objectTypeText = $formNodeUtil->getObjectTypeByName('Form Field - Free Text');

        $repository = $em->getRepository('OlegUserdirectoryBundle:FormNode');
        $dql = $repository->createQueryBuilder("list");;
        $dql->select('list');
        $dql->leftJoin("list.objectType", "objectType");
        $dql->leftJoin("list.parent", "parent");
        $dql->leftJoin("parent.parent", "grandParent");
        $dql->where("list.level = 4 AND objectType.id = ".$objectTypeText->getId()." AND parent.level = 3 AND grandParent.name = 'Pathology Call Log Entry'");
        $dql->andWhere("list.name = '".$name."'");
        $query = $em->createQuery($dql);
        $sourceTextObjects = $query->getResult();
        echo "sourceTextObjects count=".count($sourceTextObjects)."<br>";

        if( count($sourceTextObjects) == 1 ) {
            return $sourceTextObjects[0];
        }

        return NULL;
    }
    //$name - "History/Findings HTML", "Impression/Outcome HTML"
    public function getDestinationFormNodeByName($name) {
        $em = $this->getDoctrine()->getManager();
        $formNodeUtil = $this->get('user_formnode_utility');

        $objectTypeText = $formNodeUtil->getObjectTypeByName('Form Field - Free Text, HTML');

        $repository = $em->getRepository('OlegUserdirectoryBundle:FormNode');
        $dql = $repository->createQueryBuilder("list");;
        $dql->select('list');
        $dql->leftJoin("list.objectType", "objectType");
        $dql->leftJoin("list.parent", "parent");
        $dql->leftJoin("parent.parent", "grandParent");
        //$dql->where('list.level = 4 AND objectType.id = '.$objectTypeText->getId().' AND parent.level = 3');
        $dql->where("list.level = 4 AND objectType.id = ".$objectTypeText->getId()." AND parent.level = 3 AND grandParent.name = 'Pathology Call Log Entry'");
        $dql->andWhere("list.name = '".$name."'");
        $query = $em->createQuery($dql);
        $destinationTextObjects = $query->getResult();

        if( count($destinationTextObjects) == 1 ) {
            return $destinationTextObjects[0];
        }

        return NULL;
    }

}
