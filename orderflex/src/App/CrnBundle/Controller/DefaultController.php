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

namespace App\CrnBundle\Controller;

use App\CrnBundle\Form\CrnMessageCacheType;
use App\OrderformBundle\Entity\Message;
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
     * @Route("/about", name="crn_about_page")
     * @Template("AppUserdirectoryBundle/Default/about.html.twig")
     */
    public function aboutAction(Request $request)
    {

        //$userUtil = $this->container->get('user_utility');
        //$scheme = $userUtil->getScheme();
        //exit("scheme=$scheme");

        return array('sitename' => $this->getParameter('crn.sitename'));
    }



//    /**
//     * Alerts
//     * @Route("/alerts/", name="crn_alerts")
//     * @Template("AppCrnBundle/Default/under_construction.html.twig")
//     */
//    public function alertsAction(Request $request)
//    {
//        return;
//    }


    /**
     * Resources
     * @Route("/resources/", name="crn_resources")
     * @Template("AppCrnBundle/Crn/resources.html.twig")
     */
    public function resourcesAction(Request $request)
    {
        if( false === $this->isGranted('ROLE_CRN_USER') ) {
            return $this->redirect( $this->generateUrl('crn-nopermission') );
        }

        //return $this->redirectToRoute('user_admin_index');

        //testing
        //metaphone (if enabled)
        //$userServiceUtil = $this->container->get('user_service_utility');
        //$userServiceUtil->metaphoneTest();

//        $msg = "Notify Test!!!";
//        $this->addFlash(
//            'notice',
//            $msg
//        );
//            $this->addFlash(
//                'pnotify',
//                $msg
//            );

        $em = $this->getDoctrine()->getManager();
        $entities = $em->getRepository('AppCrnBundle:CrnSiteParameter')->findAll();

        if( count($entities) != 1 ) {
            throw new \Exception( 'Must have only one parameter object. Found '.count($entities).'object(s)' );
        }

        $entity = $entities[0];

        $resourcesText = $entity->getCrnResource();

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
//     * @Route("/check-encounter-location/", name="crn_check_encounter_location", methods={"POST"}, options={"expose"=true})
//     */
//    public function checkLocationAction(Request $request)
//    {
//        exit("Not used");
//    }


    /**
     * http://localhost/order/crn/assign-crn-users
     * This is one time run method to assign the crn roles
     * @Route("/assign-crn-users", name="crn_assign-crn-users")
     */
    public function assignUsersAction(Request $request)
    {
        if( false === $this->isGranted('ROLE_PLATFORM_DEPUTY_ADMIN') ) {
            return $this->redirect($this->generateUrl('employees-nopermission'));
        }

        $em = $this->getDoctrine()->getManager();

        $user = $this->getUser();

        $inputFileName = __DIR__ . '/../../../../../importUserLists/Crn_Users.xlsx';

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

            $attendingUserStr = trim((string)$rowData[0][0]);
            $attendingUserCwid = trim((string)$rowData[0][1]);
            //echo "attendingUserStr=".$attendingUserStr."<br>";
            //echo "attendingUserCwid=".$attendingUserCwid."<br>";
            $attendingCount = $this->assignRoleToUser($attendingUserStr,$attendingUserCwid,"ROLE_CRN_PATHOLOGY_ATTENDING",$attendingCount);

            $residentUserStr = trim((string)$rowData[0][2]);
            $residentUserCwid = trim((string)$rowData[0][3]);
            //echo "residentUserStr=".$residentUserStr."<br>";
            //echo "residentUserCwid=".$residentUserCwid."<br>";
            $residentCount = $this->assignRoleToUser($residentUserStr,$residentUserCwid,"ROLE_CRN_PATHOLOGY_RESIDENT",$residentCount);

            $fellowUserStr = trim((string)$rowData[0][4]);
            $fellowUserCwid = trim((string)$rowData[0][5]);
            //echo "fellowUserStr=".$fellowUserStr."<br>";
            //echo "fellowUserCwid=".$fellowUserCwid."<br>";
            $fellowCount = $this->assignRoleToUser($fellowUserStr,$fellowUserCwid,"ROLE_CRN_PATHOLOGY_FELLOW",$fellowCount);

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
     * http://localhost/order/crn/update-cache-values-now
     * method to populate/update all Critical Result Notification Entry cache in XML format
     * @Route("/update-cache-values-now/", name="crn_update_cache_values_now")
     */
    public function populateEntryCacheAction(Request $request)
    {
        if( false === $this->isGranted('ROLE_PLATFORM_DEPUTY_ADMIN') ) {
            return $this->redirect($this->generateUrl('employees-nopermission'));
        }

        //exit("This is a one time run method");

        $formNodeUtil = $this->container->get('user_formnode_utility');
        $userSecUtil = $this->container->get('user_security_utility');

        $user = $this->getUser();
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

        $msg = "Critical Result Notification cache has been updated for " . count($messages) . " Critical Result Notification Entries";

        //Event Log
        if( count($messages) ) {
            $eventType = "Critical Result Notification Cache Updated";
            $msgLog = $msg . ":<br>" . implode(", ",$messageIds);
            $userSecUtil->createUserEditEvent($this->getParameter('crn.sitename'), $msgLog, $user, null, $request, $eventType);
        }

        $this->addFlash(
            'pnotify',
            $msg
        );

        return $this->redirect( $this->generateUrl('crn_home') );
        //exit($msg);
    }


    /**
     * @Route("/update-cache-manually/{id}", name="crn_update_cache_manually")
     * @Template("AppCrnBundle/Crn/update-cache-manually.html.twig")
     */
    public function updateCacheManuallyAction(Request $request, Message $message)
    {
        if( false === $this->isGranted('ROLE_CRN_ADMIN') ) {
            return $this->redirect($this->generateUrl('employees-nopermission'));
        }

        $userSecUtil = $this->container->get('user_security_utility');
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();

        $params = array();

        $form = $this->createForm(
            CrnMessageCacheType::class,
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

            $msg = "Critical Result Notification cache has been manually updated for " . $message->getOid();

            $this->addFlash(
                'pnotify',
                $msg
            );

            //Event Log
            $eventType = "Critical Result Notification Cache Updated Manually";
            $userSecUtil->createUserEditEvent($this->getParameter('crn.sitename'), $msg, $user, $message, $request, $eventType);

            return $this->redirect($this->generateUrl('crn_crnentry_view', array(
                'messageOid' => $message->getOid(),
                'messageVersion' => $message->getVersion()
            )));
        }

        return array(
            'form' => $form->createView(),
            'message' => $message,
            'title' => "Update Cache Manually for Critical Result Notification Entry ID " . $message->getOid()
        );
    }


    /**
     * 127.0.0.1/order/crn/update-text-html
     *
     * @Route("/update-text-html", name="crn_update_text_html")
     */
    public function updateTextHtmlAction(Request $request)
    {
        if (false === $this->isGranted('ROLE_PLATFORM_DEPUTY_ADMIN')) {
            return $this->redirect($this->generateUrl('employees-nopermission'));
        }

        $crnUtil = $this->container->get('crn_util');
        $res = $crnUtil->updateTextHtml();

        exit("EOF updateTextHtmlAction. Res=".$res);

//        //$em = $this->getDoctrine()->getManager();
//        //$userSecUtil = $this->container->get('user_security_utility');
//        //$user = $this->getUser();
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
//        //$dql->where("list.level = 4 AND objectType.id = ".$objectTypeText->getId()." AND parent.level = 3 AND grandParent.name = 'Pathology Critical Result Notification Entry'");
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
//                //$eventType = "Critical Result Notification Entry Updated";
//                //$userSecUtil->createUserEditEvent($this->getParameter('crn.sitename'), $msgLog, $user, $message, $request, $eventType);
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
//        $formNodeUtil = $this->container->get('user_formnode_utility');
//
//        $objectTypeText = $formNodeUtil->getObjectTypeByName('Form Field - Free Text');
//
//        $repository = $em->getRepository('AppUserdirectoryBundle:FormNode');
//        $dql = $repository->createQueryBuilder("list");;
//        $dql->select('list');
//        $dql->leftJoin("list.objectType", "objectType");
//        $dql->leftJoin("list.parent", "parent");
//        $dql->leftJoin("parent.parent", "grandParent");
//        $dql->where("list.level = 4 AND objectType.id = ".$objectTypeText->getId()." AND parent.level = 3 AND grandParent.name = 'Pathology Critical Result Notification Entry'");
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
//        $formNodeUtil = $this->container->get('user_formnode_utility');
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
//        $dql->where("list.level = 4 AND objectType.id = ".$objectTypeText->getId()." AND parent.level = 3 AND grandParent.name = 'Pathology Critical Result Notification Entry'");
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

}
