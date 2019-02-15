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
     * http://localhost/order/call-log-book/populate-entry-cache/
     * This is one time run method to populate call log entry cache in XML format
     * @Route("/populate-entry-cache/", name="calllog_populate_entry_cache")
     */
    public function populateEntryCacheAction(Request $request)
    {
        if( false === $this->get('security.authorization_checker')->isGranted('ROLE_PLATFORM_DEPUTY_ADMIN') ) {
            return $this->redirect($this->generateUrl('employees-nopermission'));
        }

        $formNodeUtil = $this->get('user_formnode_utility');
        $em = $this->getDoctrine()->getManager();

        $repository = $em->getRepository('OlegOrderformBundle:Message');

        $dql =  $repository->createQueryBuilder("message");
        $dql->select('message');
        $dql->where("message.formnodesCache IS NULL");

        $query = $em->createQuery($dql);

        $messages = $query->getResult();
        echo "Messages count=".count($messages)."<br>";

        foreach( $messages as $message ) {
            $res = $formNodeUtil->updateFieldsCache($message);

            if( !$res) {
                exit("Error updating cache");
            }

            $message = NULL;
            $em->clear($message);
        }

        exit("End of updating cache");
    }

}
