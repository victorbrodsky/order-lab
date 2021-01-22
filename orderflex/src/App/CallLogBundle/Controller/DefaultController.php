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

namespace App\CallLogBundle\Controller;

use App\CallLogBundle\Form\CalllogMessageCacheType;
use App\OrderformBundle\Entity\Message;
use App\OrderformBundle\Entity\MessageTagsList;
use App\OrderformBundle\Entity\PatientMrn;
use App\UserdirectoryBundle\Entity\ObjectTypeText;
use App\UserdirectoryBundle\Controller\OrderAbstractController;
//use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
//use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;


class DefaultController extends OrderAbstractController
{

    /**
     * @Route("/about", name="calllog_about_page")
     * @Template("AppUserdirectoryBundle/Default/about.html.twig")
     */
    public function aboutAction(Request $request)
    {
        return array('sitename' => $this->getParameter('calllog.sitename'));
    }



//    /**
//     * Alerts
//     * @Route("/alerts/", name="calllog_alerts")
//     * @Template("AppCallLogBundle/Default/under_construction.html.twig")
//     */
//    public function alertsAction(Request $request)
//    {
//        return;
//    }


    /**
     * Resources
     * @Route("/resources/", name="calllog_resources")
     * @Template("AppCallLogBundle/CallLog/resources.html.twig")
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
        $entities = $em->getRepository('AppUserdirectoryBundle:SiteParameters')->findAll();

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
//     * @Route("/check-encounter-location/", name="calllog_check_encounter_location", methods={"POST"}, options={"expose"=true})
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
        exit("This is one time run method to assign the calllog roles");

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
                $role = $em->getRepository('AppUserdirectoryBundle:Roles')->findOneByName($roleStr);
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
        $repository = $em->getRepository('AppUserdirectoryBundle:User');
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
        $user = $em->getRepository('AppUserdirectoryBundle:User')->findOneByUsername( $cwid."_@_". $usernamePrefix);

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

        $repository = $em->getRepository('AppOrderformBundle:Message');

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
            $userSecUtil->createUserEditEvent($this->getParameter('calllog.sitename'), $msgLog, $user, null, $request, $eventType);
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
     * @Template("AppCallLogBundle/CallLog/update-cache-manually.html.twig")
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
            $userSecUtil->createUserEditEvent($this->getParameter('calllog.sitename'), $msg, $user, $message, $request, $eventType);

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
     * 127.0.0.1/order/call-log-book/update-text-html
     *
     * @Route("/update-text-html", name="calllog_update_text_html")
     */
    public function updateTextHtmlAction(Request $request)
    {
        if (false === $this->get('security.authorization_checker')->isGranted('ROLE_PLATFORM_DEPUTY_ADMIN')) {
            return $this->redirect($this->generateUrl('employees-nopermission'));
        }

        $calllogUtil = $this->get('calllog_util');
        $res = $calllogUtil->updateTextHtml();

        exit("EOF updateTextHtmlAction. Res=".$res);

//        //$em = $this->getDoctrine()->getManager();
//        //$userSecUtil = $this->get('user_security_utility');
//        //$user = $this->get('security.token_storage')->getToken()->getUser();
//
//        //$objectTypeText = $formNodeUtil->getObjectTypeByName('Form Field - Free Text, HTML');
//
//        $historySourceFormNode = $this->getSourceFormNodeByName("History/Findings");
//        if( !$historySourceFormNode ) {
//            exit("Error: no source form node History/Findings");
//        }
//        $impressionSourceFormNode = $this->getSourceFormNodeByName("Impression/Outcome");
//        if( !$impressionSourceFormNode ) {
//            exit("Error: no source form node Impression/Outcome");
//        }
//
//        $historyDestinationFormNode = $this->getDestinationFormNodeByName("History/Findings HTML");
//        if( !$historyDestinationFormNode ) {
//            exit("Error: no destination form node History/Findings HTML");
//        }
//        $historyDestinationFormNodeId = $historyDestinationFormNode->getId();
//
//        $impressionDestinationFormNode = $this->getDestinationFormNodeByName("Impression/Outcome HTML");
//        if( !$impressionDestinationFormNode ) {
//            exit("Error: no destination form node Impression/Outcome HTML");
//        }
//        $impressionDestinationFormNodeId = $impressionDestinationFormNode->getId();
//
//        //$formNodeHtml = $em->getRepository('AppUserdirectoryBundle:ObjectTypeText')->findAll();
//
//        //$sourceTextObjects = $em->getRepository('AppUserdirectoryBundle:FormNode')->findOneByName("History/Findings");
//        $repository = $em->getRepository('AppUserdirectoryBundle:ObjectTypeText');
//        $dql = $repository->createQueryBuilder("list");;
//        $dql->select('list');
//        $dql->leftJoin("list.formNode", "formNode");
//        //$dql->leftJoin("list.objectType", "objectType");
//        //$dql->leftJoin("list.parent", "parent");
//        //$dql->leftJoin("parent.parent", "grandParent");
//        //$dql->where("list.level = 4 AND objectType.id = ".$objectTypeText->getId()." AND parent.level = 3 AND grandParent.name = 'Pathology Call Log Entry'");
//        //$dql->andWhere("list.name = 'History/Findings' OR list.name = 'Impression/Outcome'");
//        $dql->where("formNode.id = " . $historySourceFormNode->getId() . " OR formNode.id = " . $impressionSourceFormNode->getId());
//        //$dql->orderBy('list.arraySectionIndex','DESC');
//        //$dql->addOrderBy('list.orderinlist', 'ASC');
//        $query = $em->createQuery($dql);
//
//        $sourceTextObjects = $query->getResult();
//        echo "Searching text objects by formnode ID ".$historySourceFormNode->getId()." and ".$impressionSourceFormNode->getId()."<br>";
//        echo "SourceTextObjects count=".count($sourceTextObjects)."<br>";
//
//       //$iterableResult = $query->iterate();
//       // echo "iterableResult count=".count($iterableResult)."<br>";
//
//
//        $totalCounter = 0;
//        $processedCounter = 0;
//
//        $batchSize = 20;
//        $i = 0;
//
//        foreach($sourceTextObjects as $textObject) {
//        //foreach($iterableResult as $row) {
//            //$textObject = $row[0];
//
//
//            //check if parent is section (level = 3)
////            if( $textObject->getParent() && $textObject->getParent()->getLevel() == 3 ) {
////                //ok
////            } else {
////                echo "Skip this textObject: ".$textObject."<br>";
////                continue;
////            }
//
//            //create a new ObjectTypeText Html
//            //echo "Copy this textObject: ".$textObject."<br>";
//
//            $totalCounter++;
//
//            $creator = $textObject->getCreator();
//            $createDate = $textObject->getCreatedate();
//
//            $updatedby = $textObject->getUpdatedby();
//            $updatedon = $textObject->getUpdatedon();
//
//            $name = $textObject->getName();
//            $abbreviation = $textObject->getAbbreviation();
//            $shortName = $textObject->getShortname();
//            $description = $textObject->getDescription();
//            $type = $textObject->getType();
//
//            $updateAuthorRoles = $textObject->getUpdateAuthorRoles();
//            $fulltitle = $textObject->getFulltitle();
//            $linkToListId = $textObject->getLinkToListId();
//
//            $version = $textObject->getVersion();
//
//            $formValue = $textObject->getValue();
//            $formNode = $textObject->getFormNode();
//
//            $entityNamespace = $textObject->getEntityNamespace();
//            $entityName = $textObject->getEntityName();
//            $entityId = $textObject->getEntityId();
//
//            $arraySectionId = $textObject->getArraySectionId();
//            $arraySectionIndex = $textObject->getArraySectionIndex();
//
//            $existingHtmlText = $this->findExistingTextHtmlByName($formNode,$formValue,$historyDestinationFormNodeId,$impressionDestinationFormNodeId,$entityNamespace,$entityName,$entityId);
//            if( $existingHtmlText ) {
//                echo $totalCounter.": Skipped (".$formNode->getName()."): Text HTML already exists value=[$formValue], existingHtml=[$existingHtmlText]<br>";
//                continue;
//            }
//
//            //Create new text object
//            $textHtmlObject = new ObjectTypeText();
//
//            $count = null;
//            $userSecUtil->setDefaultList($textHtmlObject,$count,$creator,$name);
//
//            //Set list parameters
//            $textHtmlObject->setCreatedate($createDate);
//            $textHtmlObject->setUpdatedby($updatedby);
//            $textHtmlObject->setUpdatedon($updatedon);
//            $textHtmlObject->setAbbreviation($abbreviation);
//            $textHtmlObject->setShortname($shortName);
//            $textHtmlObject->setDescription($description);
//            $textHtmlObject->setType($type);
//            $textHtmlObject->setFulltitle($fulltitle);
//
//            $textHtmlObject->setFulltitle($fulltitle);
//            $textHtmlObject->setLinkToListId($linkToListId);
//            $textHtmlObject->setVersion($version);
//            $textHtmlObject->setFulltitle($fulltitle);
//            $textHtmlObject->setFulltitle($fulltitle);
//            $textHtmlObject->setUpdateAuthorRoles($updateAuthorRoles);
//
//            //Set ObjectTypeReceivingBase parameters
//            $textHtmlObject->setArraySectionId($arraySectionId);
//            $textHtmlObject->setArraySectionIndex($arraySectionIndex);
//
//            //3) set message by entityName to the created list
//            //$textHtmlObject->setObject($holderEntity);
//            $textHtmlObject->setEntityNamespace($entityNamespace);
//            $textHtmlObject->setEntityName($entityName);
//            $textHtmlObject->setEntityId($entityId);
//
//            $textHtmlObject->setValue($formValue);
//
//            //4) set formnode to the list ("History/Findings" -> )
//            //$textHtmlObject->setFormNode($formNodeHtml);
//
//            if( $formNode->getName() == 'History/Findings' ) {
//                if( $historyDestinationFormNode ) {
//                    $textHtmlObject->setFormNode($historyDestinationFormNode);
//                    $msgLog = $processedCounter.": ".$entityId."(".$entityName."): Copy History/Findings html text [$formValue] to formnode [$historyDestinationFormNode]";
//                } else {
//                    echo $totalCounter.": Skip historyDestinationFormNodeByName not found <br>";
//                    continue;
//                }
//            }
//            if( $formNode->getName() == 'Impression/Outcome' ) {
//                if( $impressionDestinationFormNode ) {
//                    $textHtmlObject->setFormNode($impressionDestinationFormNode);
//                    $msgLog = $processedCounter.": ".$entityId."(".$entityName."): Copy Impression/Outcome html text [$formValue] to formnode [$impressionDestinationFormNode]";
//                } else {
//                    echo $totalCounter.": Skip impressionDestinationFormNodeByName not found <br>";
//                    continue;
//                }
//            }
//
//            //echo "textHtmlObject: Namespace=" . $textHtmlObject->getEntityNamespace() . ", Name=" . $textHtmlObject->getEntityName() . ", Value=" . $textHtmlObject->getValue() . "<br>";
//            $processedCounter++;
//
//            //$testing = true;
//            $testing = false;
//            if( !$testing ) {
//
//                //$updateCache = false;
//                $updateCache = true;
//                if( $updateCache ) {
//                    $message = null;
//                    if ($entityId) {
//                        $message = $em->getRepository('AppOrderformBundle:Message')->find($entityId);
//                        if (!$message) {
//                            throw new \Exception("Message is not found by id " . $entityId);
//                        }
//                        //Save fields as cache in the field $formnodesCache ($holderEntity->setFormnodesCache($text))
//                        $testing = false;
//                        $formNodeUtil->updateFieldsCache($message, $testing);
//                    }
//                }
//
//                $em->persist($textHtmlObject);
//                //$em->flush();
//                //$em->clear();
//
//                if (($i % $batchSize) === 0) {
//                    $em->flush(); // Executes all updates.
//                    //$em->clear(); // Detaches all objects from Doctrine!
//                }
//                ++$i;
//
//                //EventLog
//                //$eventType = "Call Log Book Entry Updated";
//                //$userSecUtil->createUserEditEvent($this->getParameter('calllog.sitename'), $msgLog, $user, $message, $request, $eventType);
//            }
//
//            echo $msgLog . "<br>";
//
//            if( $processedCounter > 100 ) {
//                $em->flush(); //testing
//                $em->clear();
//                exit("Break processing $totalCounter text objects");
//            }
//
//        }//foreach
//
//        $em->flush();
//        $em->clear();
//
//        exit("Processed $processedCounter text objects");
    }
//    public function findExistingTextHtmlByName($formNode,$formValue,$historyDestinationFormNodeId,$impressionDestinationFormNodeId,$entityNamespace,$entityName,$entityId) {
//
//        //return false; //testing
//
//        $em = $this->getDoctrine()->getManager();
//
//        $repository = $em->getRepository('AppUserdirectoryBundle:ObjectTypeText');
//        $dql = $repository->createQueryBuilder("list");;
//        $dql->select('list');
//        $dql->leftJoin("list.formNode", "formNode");
//
//        if( $formNode->getName() == 'History/Findings' ) {
//            $dql->where("formNode.id = " . $historyDestinationFormNodeId);
//        }
//        if( $formNode->getName() == 'Impression/Outcome' ) {
//            $dql->where("formNode.id = " . $impressionDestinationFormNodeId);
//        }
//
//        //$dql->andWhere("list.value = '$formValue'");
//        $dql->andWhere("list.value IS NOT NULL");
//
//        $dql->andWhere("list.entityNamespace = '$entityNamespace' AND list.entityName = '$entityName' AND list.entityId = '$entityId'");
//
//        $query = $em->createQuery($dql);
//        $destinationTextObjects = $query->getResult();
//        //echo "Existing destinationTextObjects count=".count($destinationTextObjects)."<br>";
//
//        //exit("eof");
//
//        if( count($destinationTextObjects) > 0 ) {
//            return $destinationTextObjects[0]->getValue();
//        }
//
//        return false;
//    }
//    //$name - "History/Findings", "Impression/Outcome"
//    public function getSourceFormNodeByName($name) {
//        $em = $this->getDoctrine()->getManager();
//        $formNodeUtil = $this->get('user_formnode_utility');
//
//        $objectTypeText = $formNodeUtil->getObjectTypeByName('Form Field - Free Text');
//
//        $repository = $em->getRepository('AppUserdirectoryBundle:FormNode');
//        $dql = $repository->createQueryBuilder("list");;
//        $dql->select('list');
//        $dql->leftJoin("list.objectType", "objectType");
//        $dql->leftJoin("list.parent", "parent");
//        $dql->leftJoin("parent.parent", "grandParent");
//        $dql->where("list.level = 4 AND objectType.id = ".$objectTypeText->getId()." AND parent.level = 3 AND grandParent.name = 'Pathology Call Log Entry'");
//        $dql->andWhere("list.name = '".$name."'");
//        $query = $em->createQuery($dql);
//        $sourceTextObjects = $query->getResult();
//        echo "sourceTextObjects count=".count($sourceTextObjects)."<br>";
//
//        if( count($sourceTextObjects) == 1 ) {
//            return $sourceTextObjects[0];
//        }
//
//        return NULL;
//    }
//    //$name - "History/Findings HTML", "Impression/Outcome HTML"
//    public function getDestinationFormNodeByName($name) {
//        $em = $this->getDoctrine()->getManager();
//        $formNodeUtil = $this->get('user_formnode_utility');
//
//        $objectTypeText = $formNodeUtil->getObjectTypeByName('Form Field - Free Text, HTML');
//
//        $repository = $em->getRepository('AppUserdirectoryBundle:FormNode');
//        $dql = $repository->createQueryBuilder("list");;
//        $dql->select('list');
//        $dql->leftJoin("list.objectType", "objectType");
//        $dql->leftJoin("list.parent", "parent");
//        $dql->leftJoin("parent.parent", "grandParent");
//        //$dql->where('list.level = 4 AND objectType.id = '.$objectTypeText->getId().' AND parent.level = 3');
//        $dql->where("list.level = 4 AND objectType.id = ".$objectTypeText->getId()." AND parent.level = 3 AND grandParent.name = 'Pathology Call Log Entry'");
//        $dql->andWhere("list.name = '".$name."'");
//        $query = $em->createQuery($dql);
//        $destinationTextObjects = $query->getResult();
//
//        if( count($destinationTextObjects) == 1 ) {
//            return $destinationTextObjects[0];
//        }
//
//        return NULL;
//    }


    /**
     * 127.0.0.1/order/call-log-book/update-entry-tags
     *
     * @Route("/update-entry-tags", name="calllog_update_entry_tags")
     */
    public function updateEntryTagAction(Request $request)
    {
        exit("Permitted only once");

        if (false === $this->get('security.authorization_checker')->isGranted('ROLE_PLATFORM_DEPUTY_ADMIN')) {
            return $this->redirect($this->generateUrl('employees-nopermission'));
        }

        $calllogUtil = $this->get('calllog_util');
        $res = null;

        //Copy entry tags from CalllogEntryMessage->entryTags => Message->entryTags

        $em = $this->getDoctrine()->getManager();
        $repository = $em->getRepository('AppOrderformBundle:CalllogEntryMessage');
        $dql =  $repository->createQueryBuilder("calllogMessage");
        $dql->select('calllogMessage');
        $dql->leftJoin("calllogMessage.entryTags", "entryTag");
        $dql->where("entryTag IS NOT NULL");

        $query = $em->createQuery($dql);

        $calllogMessages = $query->getResult();
        echo "calllogMessages=".count($calllogMessages)."<br>";

        foreach($calllogMessages as $calllogMessage) {
            $message = $calllogMessage->getMessage();
            $tags = $calllogMessage->getEntryTags();
            echo "######## Messag=".$message->getId()."########<br>";
            foreach($tags as $tag) {
                echo $message->getId().": tags=" . $tag . "<br>";

                $messageEntryTag = $em->getRepository("AppOrderformBundle:MessageTagsList")->findOneByName($tag->getName());
                echo $message->getId().": messageEntryTag=" . $messageEntryTag . "<br>";

                $message->addEntryTag($messageEntryTag);

                $em->flush();
            }
        }


        exit("EOF updateEntryTagAction. Res=" . $res);


    }

    /**
     * 127.0.0.1/order/call-log-book/update-patient-mrn
     *
     * @Route("/update-patient-mrn", name="calllog_update_patient_mrn")
     */
    public function updatePatientMrnAction(Request $request)
    {
        exit("updatePatientMrnAction. Execute only once.");

        if (false === $this->get('security.authorization_checker')->isGranted('ROLE_PLATFORM_DEPUTY_ADMIN')) {
            return $this->redirect($this->generateUrl('employees-nopermission'));
        }

        //$calllogUtil = $this->get('calllog_util');
        $res = null;

        //Copy entry tags from CalllogEntryMessage->entryTags => Message->entryTags

        $em = $this->getDoctrine()->getManager();

        $oldMrnType = $em->getRepository('AppOrderformBundle:MrnType')->findOneByName("New York Hospital MRN");
        if( !$oldMrnType ) {
            exit("oldMrnType not found");
        }

        $newMrnType = $em->getRepository('AppOrderformBundle:MrnType')->findOneByName("NYH EMPI");
        if( !$newMrnType ) {
            exit("newMrnType not found");
        }

//        $repository = $em->getRepository('AppOrderformBundle:Patient');
//        $dql = $repository->createQueryBuilder("patient");
//        $dql->leftJoin("patient.mrn", "mrn");
//        $dql->leftJoin("patient.dob", "dob");
//        $dql->leftJoin("patient.lastname", "lastname");
//        $dql->leftJoin("patient.firstname", "firstname");
//        //$dql->leftJoin("patient.encounter", "encounter");
//        //$dql->leftJoin("encounter.patlastname", "encounterLastname");
//        //$dql->leftJoin("encounter.patfirstname", "encounterFirstname");
//
//        $dql->andWhere("mrn.keytype = :keytype");
//        $parameters['keytype'] = $mrntype->getId();
//
//        $dql->andWhere("mrn.field = :mrn");
//        $parameters['mrn'] = $mrn;
//
//        $query = $em->createQuery($dql);
//        $query->setParameters($parameters);
//        $patients = $query->getResult();
//        echo "patients=".count($patients)."<br>";

        //$inputFileName = __DIR__ . '/../Util/Cities.xlsx';
        //$inputFileName = "C:\Users\ch3\Documents\MyDocs\WCMC\CallLog";

        $projectRoot = $this->get('kernel')->getProjectDir(); //C:\Users\ch3\Documents\MyDocs\WCMC\ORDER\order-lab\orderflex
        //echo "projectRoot=$projectRoot<br>";
        //exit($projectRoot);
        $parentRoot = str_replace('order-lab','',$projectRoot);
        $parentRoot = str_replace('orderflex','',$parentRoot);
        $parentRoot = str_replace(DIRECTORY_SEPARATOR.DIRECTORY_SEPARATOR,'',$parentRoot);
        //echo "parentRoot=$parentRoot<br>";
        $filename = "updateData.xlsx";
        //$filename = "updateDataDev.xlsx";
        //$filename = "updateDataTest.csv";
        $inputFileName = $parentRoot.DIRECTORY_SEPARATOR."temp".DIRECTORY_SEPARATOR.$filename;
        //$path = "C:\\Users\\ch3\\Documents\\MyDocs\\WCMC\\ORDER\\temp\\eras.pdf";
        echo "inputFileName=$inputFileName<br>";


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

        //$batchSize = 20;
        $count = 0;

        //for each row in excel
        for( $row = 2; $row <= $highestRow; $row++ ) {

            $res = NULL;

            if( $row > 550 ) {
                break;
            }

            //  Read a row of data into an array
            $rowData = $sheet->rangeToArray(
                'A' . $row . ':' . $highestColumn . $row,
                NULL,
                TRUE,
                FALSE
            );

//            $newMrnNumber = trim($rowData[0][1]);
//            if( !$newMrnNumber ) {
//                echo "New MRN is empty => exit <br>";
//                break;
//            }

            //echo $row.": ";
            //var_dump($rowData);
            //echo "<br>";

//            $oldMrnValue = trim($rowData[0][0]);
//            $newMrnValue = trim($rowData[0][1]);
//
//            $lastName = trim($rowData[0][2]);
//            $firstName = trim($rowData[0][3]);
//            $dob = trim($rowData[0][4]);

            //$res = $this->findAndupdateSinglePatient($oldMrnValue,$oldMrnType,$newMrnValue,$newMrnType);
            $res = $this->findAndUpdateSinglePatient($rowData,$oldMrnType,$newMrnType,$row);

            if( $res ) {
                $count++;

                //$mrnEntity = $res->obtainValidField('mrn')->obtainOptimalName();
                //exit("Updated patient with ID=".$res->getId().", MRN=".$mrnEntity);
            }
        }

        exit("EOF updatePatientMrnAction. updated patients=" . $count);
    }
    public function findAndUpdateSinglePatient( $rowData, $oldMrnType, $newMrnType, $count ) { //$oldMrnValue, $oldMrnType, $newMrnValue, $newMrnType ) {
        $logger = $this->container->get('logger');
        $em = $this->getDoctrine()->getManager();
        $user = $this->get('security.token_storage')->getToken()->getUser();
        $invalidStatus = 'invalid';

        $oldMrnNumber = trim($rowData[0][0]);
        $newMrnNumber = trim($rowData[0][1]);

        $lastName = trim($rowData[0][2]);
        $firstName = trim($rowData[0][3]);
        $dob = trim($rowData[0][4]);

        if ( filter_var($oldMrnNumber, FILTER_VALIDATE_INT) === false ) {
            //echo "$oldMrnNumber is not an integer <br>";
            return NULL;
        }

        //echo "MRN=".$oldMrnNumber.": ";
        //print_r($rowData);
        //dump($rowData);

        $repository = $em->getRepository('AppOrderformBundle:Patient');
        $dql = $repository->createQueryBuilder("patient");
        $dql->leftJoin("patient.mrn", "mrn");
        $dql->leftJoin("patient.dob", "dob");
        $dql->leftJoin("patient.lastname", "lastname");
        $dql->leftJoin("patient.firstname", "firstname");
        //$dql->leftJoin("patient.encounter", "encounter");
        //$dql->leftJoin("encounter.patlastname", "encounterLastname");
        //$dql->leftJoin("encounter.patfirstname", "encounterFirstname");

        $dql->where("mrn.field = :mrn");
        $parameters['mrn'] = $oldMrnNumber;

        $dql->andWhere("mrn.keytype = :keytype");
        $parameters['keytype'] = $oldMrnType->getId();

        if( $lastName ) {
            $dql->andWhere("LOWER(lastname.field) = LOWER(:lastname)");
            $parameters['lastname'] = $lastName;
        }

        if( $firstName ) {
            $dql->andWhere("LOWER(firstname.field) = LOWER(:firstname)");
            $parameters['firstname'] = $firstName;
        }

        $query = $em->createQuery($dql);

        $query->setParameters($parameters);

        $patients = $query->getResult();

        //echo $oldMrnNumber.": patients=".count($patients)."<br>";

        if( count($patients) == 1 ) {
            $patient = $patients[0];
        } else {
            //exit("Error: found patients=".count($patients)." by mrnValue=".$oldMrnNumber);
            $logger->warning("Error: found patients=".count($patients)." by mrnValue=".$oldMrnNumber."; lastname=".$lastName."; firstname=".$firstName);
            exit("Error: found patients=".count($patients)." by mrnValue=".$oldMrnNumber."; lastname=".$lastName."; firstname=".$firstName);
        }

        echo $count.": Ready to update MRN: [$oldMrnNumber($oldMrnType)] => [$newMrnNumber($newMrnType)] <br>";

        $update = true;
        //$update = false; //testing
        /////////// update MRN ///////////
        if( $update && $newMrnType && $newMrnNumber ) {

            //Check if mrn number and type already exists
            $patientDb = $this->findPatientByMrn($newMrnNumber,$newMrnType);
            if( $patientDb ) {
                $logger->warning("Error: Patient with $newMrnNumber $newMrnType already exists");
                return NULL;
                //exit("Error: Patient with $newMrnNumber $newMrnType already exists");
            }

            //Check if existing valid mrn number and type are the same as new mrn number and type
            $existingMrnNumber = null;
            $existingMrnTypeId = null;
            $mrnEntity = $patient->obtainValidField('mrn');
            if( $mrnEntity ) {
                $existingMrnNumber = $mrnEntity->getField();
                if( $mrnEntity->getKeytype() ) {
                    $existingMrnTypeId = $mrnEntity->getKeytype()->getId();
                }
            }
            if( $existingMrnNumber && $existingMrnTypeId ) {
                if( $existingMrnNumber == $newMrnNumber && $existingMrnTypeId == $newMrnType->getId() ) {
                    $updatemsg = "MRN already exists: ".$newMrnNumber." ".$newMrnType;
                    echo $updatemsg."<br>";
                    $logger->notice($updatemsg);
                    //exit("MRN already exists: ".$newMrnNumber." ".$newMrnType);
                    return NULL;
                }
            } else {
                $logger->warning("Existing valid MRN does not exist");
                exit("Existing valid MRN does not exist");
            }

            //Create new valid MRN entity
            $updatemsg = "Update MRN: [$oldMrnNumber($oldMrnType)] => [$newMrnNumber($newMrnType)]";
            echo $updatemsg."<br>";
            $logger->notice($updatemsg);
            //echo "create new mrn <br>";
            //1) set all existing MRN to invalid
            $patient->setStatusAllFields($patient->getMrn(), $invalidStatus);
            //2) create new valid MRN
            $newMrnObject = new PatientMrn('valid',$user,null);
            if( $newMrnType ) {
                $newMrnObject->setKeytype($newMrnType);
            }
            if( $newMrnNumber ) {
                $newMrnObject->setField($newMrnNumber);
            }
            $patient->addMrn($newMrnObject);

            $em->flush();
            
        } else {
            $patient = NULL;
        }
        /////////// EOF update MRN ///////////


        return $patient;
    }
    public function findPatientByMrn( $mrnNumber, $nmrnType ) { //$oldMrnValue, $oldMrnType, $newMrnValue, $newMrnType ) {
        $em = $this->getDoctrine()->getManager();
        //$user = $this->get('security.token_storage')->getToken()->getUser();
        //$invalidStatus = 'invalid';
        $patient = NULL;

        if ( filter_var($mrnNumber, FILTER_VALIDATE_INT) === false ) {
            exit("$mrnNumber is not an integer");
        }

        $repository = $em->getRepository('AppOrderformBundle:Patient');
        $dql = $repository->createQueryBuilder("patient");
        $dql->leftJoin("patient.mrn", "mrn");
        $dql->leftJoin("patient.dob", "dob");
        //$dql->leftJoin("patient.lastname", "lastname");
        //$dql->leftJoin("patient.firstname", "firstname");
        //$dql->leftJoin("patient.encounter", "encounter");
        //$dql->leftJoin("encounter.patlastname", "encounterLastname");
        //$dql->leftJoin("encounter.patfirstname", "encounterFirstname");

        $dql->where("mrn.field = :mrn");
        $parameters['mrn'] = $mrnNumber;

        $dql->andWhere("mrn.keytype = :keytype");
        $parameters['keytype'] = $nmrnType->getId();

        $query = $em->createQuery($dql);

        $query->setParameters($parameters);

        $patients = $query->getResult();

        //echo $mrnNumber.": patients=".count($patients)."<br>";

        if( count($patients) > 1 ) {
            $patient = $patients[0];
        } else {
            //exit("Error: found patients=".count($patients)." by mrnValue=".$mrnNumber);
        }

        return $patient;
    }


    /**
     * Location entries need to be re-linked to the one original Location ID (find by name "New York Presbyterian Hospital")
     * 127.0.0.1/order/call-log-book/relink-duplicate-location
     *
     * @Route("/relink-duplicate-location", name="calllog_relink_duplicate_location")
     */
    public function relinkLocationAction(Request $request)
    {
        //exit("Permitted only once");

        if (false === $this->get('security.authorization_checker')->isGranted('ROLE_PLATFORM_DEPUTY_ADMIN')) {
            return $this->redirect($this->generateUrl('employees-nopermission'));
        }

        $em = $this->getDoctrine()->getManager();

        $repository = $em->getRepository('AppUserdirectoryBundle:Location');
        $dql =  $repository->createQueryBuilder("location");
        $dql->select('location');

        $dql->where("location.name = 'New York Presbyterian Hospital'");

        $dql->orderBy("location.id", "DESC"); //last entered showed first

        $query = $em->createQuery($dql);

        $locations = $query->getResult();
        echo "locations=".count($locations)."<br>";

//        foreach($locations as $location) {
//            if( $location->getId() != $locationDefault->getId() ) {
//                //$location->
//            }
//        }

        $defaultLocations = array();
        $hashArr = array();
        $count = 0;
        foreach($locations as $location) {

            //echo "id=".$location->getId()." (".$location->getType()."): ";

            if( $location->getType() == "disabled" ) {
                continue;
            }

            echo "id=".$location->getId()." (".$location->getType()."): ";

            //$hash = $location->getHashName();
            $hash = $location->getStringify();

            if( isset($hashArr[$hash]) ) {
                $hashArr[$hash]++;
                if( $location->getType() != "disabled" ) {
                    $location->setType("disabled");
                    echo "disable this location=" . $location->getId();
                    $em->flush();
                    $count++;
                }
            } else {
                $defaultLocations[$hash] = $location;
                $hashArr[$hash] = 1;
                echo "!!! set as default location=".$location->getId();
            }

            echo "<br>";
        }

        foreach($defaultLocations as $hash=>$defaultLocation) {
            echo $hash.": defaultLocation=".$defaultLocation->getId()."<br>";
        }

//        foreach($hashArr as $hash => $hashCount) {
//            echo $hashCount.": hash=".$hash."<br>";
//        }

//        foreach($locations as $location) {
//
//            //$hash = $location->getHashName();
//            $hash = $location->getStringify();
//
//            $hashCount = $hashArr[$hash];
//            //echo $location->getId().": hashCount=".$hashCount."<br>";
//
//        }

        exit("EOF update locations. Disabled count=".$count);
    }

    /**
     * Location entries need to be re-linked to the one original Location ID (find by name "New York Presbyterian Hospital")
     * 127.0.0.1/order/call-log-book/relink-duplicate-location
     *
     * @Route("/update-default-location", name="calllog_update_default_location")
     */
    public function updateDefaultLocationAction(Request $request)
    {
        //exit("Permitted only once");

        if (false === $this->get('security.authorization_checker')->isGranted('ROLE_PLATFORM_DEPUTY_ADMIN')) {
            return $this->redirect($this->generateUrl('employees-nopermission'));
        }

        $em = $this->getDoctrine()->getManager();

        //Step 0: find default encounters (default or user-added)
        $repository = $em->getRepository('AppUserdirectoryBundle:Location');
        $dql =  $repository->createQueryBuilder("location");
        $dql->select('location');
        $dql->leftJoin("location.locationTypes", "locationTypes");

        $dql->where("location.name = 'New York Presbyterian Hospital'");
        //$dql->andWhere("(location.type = 'default' OR location.type = 'user-added')");
        $dql->andWhere("location.type = 'disabled'");
        $dql->andWhere("locationTypes.name='Encounter Location'");

        $dql->orderBy("location.id", "DESC"); //last entered showed first

        $query = $em->createQuery($dql);

        $disabledLocations = $query->getResult();
        echo "disabled locations=".count($disabledLocations)."<br>";

        //Step 1: find default encounters (default or user-added)
        $repository = $em->getRepository('AppUserdirectoryBundle:Location');
        $dql =  $repository->createQueryBuilder("location");
        $dql->select('location');
        $dql->leftJoin("location.locationTypes", "locationTypes");
        $dql->where("location.name = 'New York Presbyterian Hospital'");
        $dql->andWhere("(location.type = 'default' OR location.type = 'user-added')");
        $dql->andWhere("locationTypes.name='Encounter Location'");
        //$dql->andWhere("location.type = 'disabled'");

        $dql->orderBy("location.id", "DESC"); //last entered showed first

        $query = $em->createQuery($dql);

        $locations = $query->getResult();
        echo "locations=".count($locations)."<br>";

        $defaultLocations = array();
        foreach($locations as $location) {

            //echo "id=".$location->getId()." (".$location->getType()."): ";

            $hash = $location->getStringify();
            $defaultLocations[$hash] = $location;

            //echo "<br>";
        }

        foreach($defaultLocations as $hash=>$defaultLocation) {
            echo $defaultLocation->getId().": ".$hash."<br>";
        }

//        foreach($hashArr as $hash => $hashCount) {
//            echo $hashCount.": hash=".$hash."<br>";
//        }

//        foreach($locations as $location) {
//
//            //$hash = $location->getHashName();
//            $hash = $location->getStringify();
//
//            $hashCount = $hashArr[$hash];
//            //echo $location->getId().": hashCount=".$hashCount."<br>";
//
//        }

        //Step 2: find all encounters with disabled location
        $repository = $em->getRepository('AppOrderformBundle:Encounter');
        $dql = $repository->createQueryBuilder("encounter");
        $dql->select('encounter');
        $dql->leftJoin("encounter.tracker","tracker");
        $dql->leftJoin("tracker.spots","spots");
        $dql->leftJoin("spots.currentLocation","currentLocation");
        $dql->leftJoin("currentLocation.locationTypes", "locationTypes");

        $dql->where("currentLocation.name = 'New York Presbyterian Hospital'");
        $dql->andWhere("currentLocation.type = 'disabled'");
        $dql->andWhere("locationTypes.name='Encounter Location'");

        $dql->orderBy("currentLocation.id", "DESC"); //last entered showed first

        $query = $em->createQuery($dql);

        $encounters = $query->getResult();
        echo "encounters=".count($encounters)."<br>";

        $messageArr = array();
        $thisLocationArr = array();
        $count = 0;
        foreach($encounters as $encounter) {

            foreach( $encounter->getTracker()->getSpots() as $spot ) {
                $messages = $encounter->getMessage();
                $message = $messages[0];
                $messageId = "UnknownMessageId";
                if( $message ) {
                    $messageId = $message->getId();
                }
                $encounterId = $encounter->getId();
                $messageArr[$messageId] = 1;
                $thisLocation = $spot->getCurrentLocation();
                $thisLocationArr[$thisLocation->getId()] = 1;
                $hash = $thisLocation->getStringify();
                //echo "hash=".$hash."<br>";

                if( isset($defaultLocations[$hash]) ) {
                    $defaultLocation = $defaultLocations[$hash];

                    if( $defaultLocation ) {
                        //echo $messageId.": defaultLocation=".$defaultLocation->getId()."<br>";
                        echo $thisLocation->getId()."($messageId $encounterId)=>".$defaultLocation->getId()."; ";
                        $spot->setCurrentLocation($defaultLocation);
                        //$em->flush();
                        $count++;
                    } else {
                        exit("1 Default location not found");
                    }
                } else {
                    exit("2 Default location not found");
                }

            }

        }

        echo "<br>messages=".count($messageArr)."<br>";
        echo "thisLocationArr=".count($thisLocationArr)."<br>";

        exit("EOF update default locations. Count=".$count);
    }

}
