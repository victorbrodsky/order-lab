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

namespace Oleg\UserdirectoryBundle\Controller;


use Oleg\UserdirectoryBundle\Entity\Book;
use Oleg\UserdirectoryBundle\Entity\Lecture;
use Oleg\UserdirectoryBundle\Entity\PerSiteSettings;
use Oleg\UserdirectoryBundle\Entity\Publication;
//use Symfony\Component\Translation\Translator;
//use Symfony\Component\Translation\Loader\ArrayLoader;
use Oleg\UserdirectoryBundle\Entity\UserInfo;
use Oleg\UserdirectoryBundle\Form\LabelType;
use Oleg\UserdirectoryBundle\Form\UserSimpleType;
use Oleg\UserdirectoryBundle\Security\Authentication\AuthUtil;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Form\Extension\Core\DataTransformer\DateTimeToStringTransformer;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Config\Definition\Exception\ForbiddenOverwriteException;

use Doctrine\Common\Collections\ArrayCollection;

use FOS\UserBundle\FOSUserEvents;
use FOS\UserBundle\Event\FormEvent;
use FOS\UserBundle\Event\GetResponseUserEvent;
use FOS\UserBundle\Event\UserEvent;

use Oleg\UserdirectoryBundle\Entity\User;
use Oleg\UserdirectoryBundle\Util\UserUtil;
use Oleg\UserdirectoryBundle\Form\UserType;
use Oleg\UserdirectoryBundle\Entity\AdministrativeTitle;
use Oleg\UserdirectoryBundle\Entity\AppointmentTitle;
use Oleg\UserdirectoryBundle\Entity\MedicalTitle;
use Oleg\UserdirectoryBundle\Entity\StateLicense;
use Oleg\UserdirectoryBundle\Entity\BoardCertification;
use Oleg\UserdirectoryBundle\Entity\EmploymentStatus;
use Oleg\UserdirectoryBundle\Entity\AdminComment;
use Oleg\UserdirectoryBundle\Entity\Identifier;
use Oleg\UserdirectoryBundle\Entity\PrivateComment;
use Oleg\UserdirectoryBundle\Entity\PublicComment;
use Oleg\UserdirectoryBundle\Entity\AccessRequest;
use Oleg\UserdirectoryBundle\Entity\BaseUserAttributes;
use Oleg\UserdirectoryBundle\Entity\ConfidentialComment;
use Oleg\UserdirectoryBundle\Entity\ResearchLab;
use Oleg\UserdirectoryBundle\Entity\Document;
use Oleg\UserdirectoryBundle\Entity\Location;
use Oleg\UserdirectoryBundle\Entity\Training;
use Oleg\UserdirectoryBundle\Form\DataTransformer\GenericTreeTransformer;
use Oleg\UserdirectoryBundle\Util\CropAvatar;
use Oleg\UserdirectoryBundle\Entity\Grant;


class UserController extends Controller
{

    /**
     * @Route("/about", name="employees_about_page")
     * @Template("OlegUserdirectoryBundle:Default:about.html.twig")
     */
    public function aboutAction( Request $request ) {

        //$userServiceUtil = $this->get('user_service_utility');
        //$ver = $userServiceUtil->getGitVersionDate();
        //echo "ver=".$ver."<br>";
        //print_r($ver);
        //exit('1');

        return array('sitename'=>$this->container->getParameter('employees.sitename'));
    }



//    /**
//     * The same boss
//     *
//     * @Route("/users-by-ids", name="employees_users-by-ids")
//     */
//    public function getUsersListAction(Request $request, $idsArr) {
//
//        //user search
//        $params = array('time'=>'current_only','objectname'=>'usersbyids','objectid'=>$idsArr,'excludeCurrentUser'=>true);
//        $res = $this->indexUser($request,$params);
//        $pagination = $res['entities'];
//
//        return $this->render('OlegUserdirectoryBundle::Admin/users-content.html.twig',
//            array(
//                'entities' => $pagination,
//                'sitename' => $this->container->getParameter('employees.sitename')
//            )
//        );
//    }


    /**
     * The same services
     *
     * @Route("/my-objects", name="employees_my_objects")
     */
    public function myObjectsAction(Request $request) {        
        
        $tablename = $request->get('tablename');
        $objectid = $request->get('id');
        $objectname = $request->get('name');
        $postData = $request->get('postData');
        $subjectUserId = $request->get('subjectUserId');

        //echo "tablename=".$tablename."<br>";

        //user search
        $params = array('time'=>'current_only','objectname'=>$tablename,'objectid'=>$objectid,'excludeCurrentUser'=>false,'subjectUserId'=>$subjectUserId);
        $res = $this->indexUser($request,$params); //use function getTheSameObject
        $pagination = $res['entities'];

        //echo "pagination count=".count($pagination)."<br>";

        if( count($pagination) == 0 ) {
            $response = new Response();
            $response->headers->set('Content-Type', 'application/json');
            $response->setContent(null);
            return $response;
        }

        $render = $this->render('OlegUserdirectoryBundle::Admin/users-content.html.twig',
            array(
                'entities' => $pagination,
                'sitename' => $this->container->getParameter('employees.sitename'),
                'postData' => $postData
            )
        );
        return $render;

//        $params = array(
//            'entities' => $pagination,
//            'sitename' => $this->container->getParameter('employees.sitename'),
//            'postData' => $postData
//        );
//
//        $res = array( 'params' => $render, 'count' => count($pagination) );
//
//        $response = new Response();
//        $response->setContent($res);
//
//        return $response;
    }


    /**
     * In the "List Current" menu, add the top choice called "Common Locations". CLicking it should list all "orphan" locations that are not attached to any users.
     *
     * @Route("/common-locations", name="employees_list_common_locations")
     * @Template("OlegUserdirectoryBundle:Location:common-locations.html.twig")
     */
    public function listCommonLocationsAction(Request $request) {

        $filter = trim( $request->get('filter') );

        //location search
        $userUtil = new UserUtil();
        $locations = $userUtil->indexLocation($filter, $request, $this->container, $this->getDoctrine());

        return array(
            'locations' => $locations,
            'filter' => $filter
        );
    }


    /**
     * Search for the users with the same object. For example, the same institution, service, room, academic title, appointment title
     *
     * @Route("/search-users", name="employees_search_same_object")
     */
    public function searchSameObjectAction(Request $request) {

        $em = $this->getDoctrine()->getManager();

        $tablename = $request->get('tablename');
        $objectid = $request->get('id');
        $objectname = $request->get('name');

        //user search
        $params = array('time'=>'current_only','objectname'=>$tablename,'objectid'=>$objectid);
        $res = $this->indexUser($request,$params);
        $pagination = $res['entities'];

        $title = "Current employees: ".$tablename." ".$objectname;

        if( strtolower($tablename) == "room" ) {
            $title = "Current employees in ".$tablename." ".$objectname;
        }

        if( strtolower($tablename) == "administrativetitle" ) {
            $title = 'Current employees with the administrative title of "'.$objectname.'"';
        }

        if( strtolower($tablename) == "appointmenttitle" ) {
            $title = 'Current employees with the academic title of "'.$objectname.'"';
        }

        if( strtolower($tablename) == "medicaltitle" ) {
            $title = 'Current employees with the medical title of "'.$objectname.'"';
        }

//        if( $tablename == "service" ) {
//            $title = 'Current employees of the '.$objectname.' service';
//        }

        if( strtolower($tablename) == "institution" ) {
            $title = 'Current employees of the '.$objectname;
        }

//        if( $tablename == "division" ) {
//            $title = 'Current employees of the '.$objectname.' division';
//        }
//
//        if( $tablename == "department" ) {
//            $title = 'Current employees of the '.$objectname.' department';
//        }

        if( $tablename == "Institution" ) {
            $node = $em->getRepository('OlegUserdirectoryBundle:Institution')->find($objectid);
            if( $node ) {
                $title = $title . " (".$node->getRootName($node).")";
            }
        }

        return $this->render(
            'OlegUserdirectoryBundle:Default:home.html.twig',
            array(
                'accessreqs' => null,
                'locations' => null,
                'entities' => $pagination,
                'roles' => null,
                'search' => null,
                'sameusers' => $title,  //"all current employees of " . $objectname . " " . $tablename,
                'postData' => $request->query->all()
            )
        );



    }


    /**
     * Show home page
     *
     * @Route("/", name="employees_home")
     * @Template("OlegUserdirectoryBundle:Default:home.html.twig")
     */
    public function indexAction( Request $request ) {

        if(
            false == $this->get('security.authorization_checker')->isGranted('ROLE_USER') ||              // authenticated (might be anonymous)
            false == $this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')    // authenticated (NON anonymous)
        ){
            return $this->redirect( $this->generateUrl('login') );
        }

        //$form = $this->createForm(new SearchType(),null);
        //$form->submit($request);  //use bind instead of handleRequest. handleRequest does not get filter data
        //$search = $form->get('search')->getData();

        //check for active access requests
        $accessreqs = $this->getActiveAccessReq();

        $search = trim( $request->get('search') );
        $userid = trim( $request->get('userid') );
        $all = trim($request->get('all'));

        //echo "all=".$all."<br>";

//        $page = $request->get('page');
//        if( !$page && $page == "" ) {
//            $page = 1;
//        }

        //echo "search=".$search."<br>";

//        $translator = new Translator('fr_FR');
//        $translator->addLoader('array', new ArrayLoader());
//        $translator->addResource('array', array(
//            'Symfony is great!' => 'J\'aime Symfony!',
//        ), 'fr_FR');
//        echo $translator->trans('Symfony is great!');
        //echo "translated=".$translated."<br>";

        $locations = null;
        $pagination = null;
        $roles = null;

        if( $search != "" || $userid != "" ) {

            //location search
            $userUtil = new UserUtil();
            $locations = $userUtil->indexLocation($search, $request, $this->container, $this->getDoctrine());

            //user search
            $params = array('time'=>'current_only','search'=>$search,'userid'=>$userid,'all'=>$all);
            $res = $this->indexUser($request,$params);
            $pagination = $res['entities'];
            $roles = $res['roles'];
        }

        return array(
            'accessreqs' => count($accessreqs),
            'locations' => $locations,
            'entities' => $pagination,
            'roles' => $roles,
            'search' => $search,
            'all' => $all,
            'postData' => $request->query->all()
        );
    }

    //check for active access requests
    public function getActiveAccessReq() {
        if( !$this->get('security.authorization_checker')->isGranted('ROLE_USERDIRECTORY_ADMIN') ) {
            return null;
        }
        $userSecUtil = $this->get('user_security_utility');
        $accessreqs = $userSecUtil->getUserAccessRequestsByStatus($this->container->getParameter('employees.sitename'),AccessRequest::STATUS_ACTIVE);
        return $accessreqs;
    }







    /**
     * @Route("/users", name="employees_listusers")
     * @Route("/users/previous", name="employees_listusers_previous")
     * @Method("GET")
     * @Template("OlegUserdirectoryBundle:Admin:users.html.twig")
     */
    public function indexUserAction(Request $request)
    {
        if( false === $this->get('security.authorization_checker')->isGranted('ROLE_USERDIRECTORY_OBSERVER') ) {
            return $this->redirect($this->generateUrl('employees-nopermission'));
        }

        $filter = trim( $request->get('filter') );

        $prefix =  "";
        $time = 'current_only';
        $routeName = $request->get('_route');
        if( $routeName == "employees_listusers_previous" ) {
            $time = 'past_only';
            $prefix =  "Previous ";
        }

        $params = array('filter'=>$filter,'time'=>$time,'limitFlag'=>100);
        $res = $this->indexUser($request,$params);

        if( $filter == "" ) {
            if( $routeName == "employees_listusers_previous" ) {
                $filter = "All Previous Employees";
            } else {
                $filter = "All Current Employees";
            }
        } else {
            $filter = $prefix . $filter;
        }

        $res['filter'] = $filter;

        return $res;
    }

    //$time: 'current_only' - search only current, 'past_only' - search only past, 'all' - search current and past (no filter)
    //public function indexUser( $filter=null, $time='all', $limitFlag=true, $search=null, $userid=null ) {
    public function indexUser( $request, $params ) {

        $filter = ( array_key_exists('filter', $params) ? $params['filter'] : null);
        $time = ( array_key_exists('time', $params) ? $params['time'] : 'all');
        $limitFlag = ( array_key_exists('limitFlag', $params) ? $params['limitFlag'] : null);
        $search = ( array_key_exists('search', $params) ? $params['search'] : null);
        $userid = ( array_key_exists('userid', $params) ? $params['userid'] : null);
        $all = ( array_key_exists('all', $params) ? $params['all'] : null);
//        $myteam = ( array_key_exists('myteam', $params) ? $params['myteam'] : null);
//        $myboss = ( array_key_exists('myboss', $params) ? $params['myboss'] : null);
//        $myservice = ( array_key_exists('myservice', $params) ? $params['myservice'] : null);
        $objectname = ( array_key_exists('objectname', $params) ? $params['objectname'] : null);
        $objectid = ( array_key_exists('objectid', $params) ? $params['objectid'] : null);
        $excludeCurrentUser = ( array_key_exists('excludeCurrentUser', $params) ? $params['excludeCurrentUser'] : null);
        $subjectUserId = ( array_key_exists('subjectUserId', $params) ? $params['subjectUserId'] : null);

        //echo "filter=".$filter."<br>";
        //echo "search=".$search."<br>";
        //echo "all=".$all."<br>";

        //$request = $this->get('request');
        $postData = $request->query->all();

        $sort = null;
        if( isset($postData['sort']) ) {
            //check for location sort
            //if( strpos($postData['sort'],'location.') === false && strpos($postData['sort'],'heads.') === false ) {
            if( strpos($postData['sort'],'location.') === false && strpos($postData['sort'],'administrativeTitle') === false ) {
                $sort = $postData['sort'];
            }
        }

        $repository = $this->getDoctrine()->getRepository('OlegUserdirectoryBundle:User');
        $dql =  $repository->createQueryBuilder("user");
        $dql->select('user');

        $dql->leftJoin("user.infos", "infos");
        $dql->leftJoin("user.preferences", "preferences");
        $dql->leftJoin("user.employmentStatus", "employmentStatus");
        $dql->leftJoin("employmentStatus.employmentType", "employmentType");

        $dql->leftJoin("user.administrativeTitles", "administrativeTitles");
        $dql->leftJoin("administrativeTitles.name", "administrativeName");
        $dql->leftJoin("administrativeTitles.institution", "administrativeInstitution");
        //$dql->leftJoin("administrativeTitles.department", "administrativeDepartment");
        //$dql->leftJoin("administrativeTitles.division", "administrativeDivision");
        //$dql->leftJoin("administrativeTitles.service", "administrativeService");

        $dql->leftJoin("user.appointmentTitles", "appointmentTitles");
        $dql->leftJoin("appointmentTitles.name", "appointmentName");
        $dql->leftJoin("appointmentTitles.institution", "appointmentInstitution");
        $dql->leftJoin("appointmentTitles.positions", "appointmentTitlesPositions");
        //$dql->leftJoin("appointmentTitles.department", "appointmentDepartment");
        //$dql->leftJoin("appointmentTitles.division", "appointmentDivision");
        //$dql->leftJoin("appointmentTitles.service", "appointmentService");

        $dql->leftJoin("user.medicalTitles", "medicalTitles");
        $dql->leftJoin("medicalTitles.name", "medicalName");
        $dql->leftJoin("medicalTitles.institution", "medicalInstitution");
        //$dql->leftJoin("medicalTitles.department", "medicalDepartment");
        //$dql->leftJoin("medicalTitles.division", "medicalDivision");
        //$dql->leftJoin("medicalTitles.service", "medicalService");

        $dql->leftJoin("user.locations", "locations");
        $dql->leftJoin("locations.room", "locationroom");
        $dql->leftJoin("locations.assistant", "assistant");
        $dql->leftJoin("assistant.infos", "assistantinfos");

        $dql->leftJoin("user.credentials", "credentials");

        $dql->leftJoin("user.researchLabs", "researchLabs");
        $dql->leftJoin("researchLabs.pis", "researchLabsPis");

        //$dql->leftJoin("user.institutions", "institutions");
        //$dql->where("user.appliedforaccess = 'active'");

        if(1) { //TODO: this cause in php 5.4: "Notice: String offset cast occurred" in in vendor\doctrine\dbal\lib\Doctrine\DBAL\Platforms\SQLServerPlatform.php at line 1232:  if ($query[$currentPosition] === '(') {
            if ($sort == null) {
                if ($time == 'current_only') {
                    $dql->orderBy("infos.lastName", "ASC");
                    $dql->addOrderBy("administrativeInstitution.name", "ASC");
                    //$dql->addOrderBy("administrativeService.name","ASC");
                    //$dql->addOrderBy("appointmentService.name","ASC");
                    //$dql->addOrderBy("medicalService.name","ASC");
                } else if ($time == 'past_only') {
                    $dql->orderBy("employmentStatus.terminationDate", "DESC");
                    $dql->addOrderBy("infos.lastName", "ASC");
                } else {
                    $dql->orderBy("infos.lastName", "ASC");
                }
            }
        }

        if( $userid ) {

            $totalcriteriastr = "user.id =".$userid;

        } else {

            $totalcriteriastr = null;
            $criteriastr = "";

            //filter
            $criteriastr = $this->getCriteriaStrByFilter( $dql, $filter, $criteriastr );
            //echo "filter=".$criteriastr."<br>";

            //search
            $criteriastr = $this->getCriteriaStrBySearch( $dql, $search, $criteriastr );
            //echo "search=".$criteriastr."<br>";

            //myteam
            //$criteriastr = $this->getMyTeam( $dql, $myteam, $myboss, $criteriastr );

            //same object
            $criteriastr = $this->getTheSameObject( $dql, $subjectUserId, $objectname, $objectid, $excludeCurrentUser, $criteriastr );
            //echo "criteriastr=".$criteriastr."<br>";

            //time
            //echo "all=".$all."<br>";
            if( !$all ) {
                //echo "all=".$all."<br>";
                $userutil = new UserUtil();
                $criteriastr = $userutil->getCriteriaStrByTime($dql, $time, null, $criteriastr);
                //echo "criteriastr=" . $criteriastr . "<br>";

                //filter out system user
                $totalcriteriastr = "user.keytype IS NOT NULL AND user.primaryPublicUserId != 'system'";

                //filter out Pathology Fellowship Applicants
                $totalcriteriastr = $totalcriteriastr . " AND (employmentType.name != 'Pathology Fellowship Applicant' OR employmentType.id IS NULL)";

                //filter out users with excludeFromSearch set to true
                if (false === $this->get('security.authorization_checker')->isGranted('ROLE_PLATFORM_DEPUTY_ADMIN')) {
                    $totalcriteriastr = $totalcriteriastr . " AND (preferences.excludeFromSearch IS NULL OR preferences.excludeFromSearch = FALSE)";
                }
            }

            if( $criteriastr ) {
                if( $totalcriteriastr ) {
                    $totalcriteriastr = $totalcriteriastr . " AND (".$criteriastr.")";
                } else {
                    $totalcriteriastr = $criteriastr;
                }

            } else {

            }

//            if( $criteriastr != "" ) {
//                $totalcriteriastr = "(" . $timecriteriastr . ") AND " .  $criteriastr;
//            } else {
//                $totalcriteriastr = $timecriteriastr;
//            }

        }
        
        //echo "totalcriteriastr=".$totalcriteriastr."<br>";

        $dql->where($totalcriteriastr);

        //pass sorting parameters directly to query; Somehow, knp_paginator stoped correctly create pagination according to sorting parameters
//        if( $sort ) {
//            $dql = $dql . " ORDER BY $postData[sort] $postData[direction]";
//        }

        //echo "dql=".$dql."<br>";

        $em = $this->getDoctrine()->getManager();
        $query = $em->createQuery($dql);    //->setParameter('now', date("Y-m-d", time()));

        //$limitFlag = null;//testing
        if( $limitFlag ) {
            //echo "use paginator limitFlag=$limitFlag<br>";
            $limit = $limitFlag; //1000;
            $paginator  = $this->get('knp_paginator');
            $pagination = $paginator->paginate(
                $query,
                $request->query->get('page', 1), /*page number*/
                $limit, /*limit per page*/
                array('wrap-queries'=>true) //don't need it with "doctrine/orm": "v2.4.8"
            );
        } else {
            //echo "dont use paginator <br>";
            $pagination = $query->getResult();
        }

        //exit("count=".count($pagination));//testing
        $rolesArr = $this->getUserRoles(); //index user (search result list)

        return array(
            'entities' => $pagination,
            'roles' => $rolesArr //it's used to get user role alias using {{ attribute(roles, role) }} , where role is a current user role name
        );
    }



    public function getCriteriaStrBySearch( $dql, $search, $inputCriteriastr ) {

        $criteriastr = "";

        if( !$search || $search == "" ) {
            return $inputCriteriastr;
        }

        //last name
        $criteriastr .= "infos.lastName LIKE '%".$search."%' OR ";
        //$criteriastr .= "user.lastName='".$search."' OR ";

        //first name
        $criteriastr .= "infos.firstName LIKE '%".$search."%' OR ";
        //$criteriastr .= "user.firstName='".$search."' OR ";

        //Middle Name
        $criteriastr .= "infos.middleName LIKE '%".$search."%' OR ";
        //$criteriastr .= "user.middleName='".$search."' OR ";

        //Preferred Full Name for Display
        $criteriastr .= "infos.displayName LIKE '%".$search."%' OR ";

        //Abbreviated Name/Initials field
        //$criteriastr .= "user.initials LIKE '%".$search."%' OR ";
        $criteriastr .= "infos.initials='".$search."' OR ";

        //preferred email
        $criteriastr .= "infos.email LIKE '%".$search."%' OR ";
        //$criteriastr .= "user.email='".$search."' OR ";

        //email in locations
        $criteriastr .= "locations.email LIKE '%".$search."%' OR ";
        //$criteriastr .= "locations.email='".$search."' OR ";

        //User ID/CWID
        $criteriastr .= "user.primaryPublicUserId LIKE '%".$search."%' OR ";
        //$criteriastr .= "user.primaryPublicUserId='".$search."' OR ";

        //Username
        $criteriastr .= "user.username LIKE '%".$search."%' OR ";


        //////////////////// administrative title
        //institution
        $criteriastr .= "administrativeInstitution.name LIKE '%".$search."%' OR ";
        //department
        //$criteriastr .= "administrativeDepartment.name LIKE '%".$search."%' OR ";
        //division
        //$criteriastr .= "administrativeDivision.name LIKE '%".$search."%' OR ";
        //service
        //$criteriastr .= "administrativeService.name LIKE '%".$search."%' OR ";
        $criteriastr .= "administrativeName.name LIKE '%".$search."%' OR ";


        //////////////////// academic appointment title
        //institution
        $criteriastr .= "appointmentInstitution.name LIKE '%".$search."%' OR ";
        //department
        //$criteriastr .= "appointmentDepartment.name LIKE '%".$search."%' OR ";
        //division
        //$criteriastr .= "appointmentDivision.name LIKE '%".$search."%' OR ";
        //service
        //$criteriastr .= "appointmentService.name LIKE '%".$search."%' OR ";
        $criteriastr .= "appointmentName.name LIKE '%".$search."%' OR ";


        //////////////////// medical appointment title
        //institution
        $criteriastr .= "medicalInstitution.name LIKE '%".$search."%' OR ";
        //department
        //$criteriastr .= "medicalDepartment.name LIKE '%".$search."%' OR ";
        //division
        //$criteriastr .= "medicalDivision.name LIKE '%".$search."%' OR ";
        //service
        //$criteriastr .= "medicalService.name LIKE '%".$search."%' OR ";
        $criteriastr .= "medicalName.name LIKE '%".$search."%' OR ";


        //Associated NYPH Code in Locations
        //$criteriastr .= "locations.associatedCode LIKE '%".$search."%' OR ";
        $criteriastr .= "locations.associatedCode='".$search."' OR ";

        if( $this->get('security.authorization_checker')->isGranted('ROLE_PLATFORM_DEPUTY_ADMIN') ) {
            //WCMC Employee Identification Number (EIN)
            //NPI
            $dql->leftJoin("credentials.identifiers", "identifiers");
            //$criteriastr .= "identifiers.field LIKE '%".$search."%' OR ";
            $criteriastr .= "identifiers.field='".$search."' OR ";

            //NYPH Code
            $dql->leftJoin("credentials.codeNYPH", "codeNYPH");
            //$criteriastr .= "codeNYPH.field LIKE '%".$search."%' OR ";
            $criteriastr .= "codeNYPH.field='".$search."' OR ";

            //License Number

            //Specialty (in Board Certifications)
            $dql->leftJoin("credentials.boardCertification", "boardCertification");
            $dql->leftJoin("boardCertification.specialty", "specialty");
            $criteriastr .= "specialty.name LIKE '%".$search."%' OR ";
        }

        //Position Type
        //$dql->leftJoin("appointmentTitles.positions", "appointmentTitlesPositions");
        $criteriastr .= " appointmentTitlesPositions.name LIKE '%".$search."%' ";

        //Specialties
        $dql->leftJoin("medicalTitles.specialties", "medicalSpecialties");
        $criteriastr .= " OR medicalSpecialties.name LIKE '%".$search."%' ";


        if( $criteriastr != "" ) {
            $criteriastr = " (" . $criteriastr . ")";
        }

        if( $inputCriteriastr && $inputCriteriastr != "" ) {
            if( $criteriastr != "" ) {
                $inputCriteriastr = $inputCriteriastr . " AND (" . $criteriastr . ")";
            }
        } else {
            $inputCriteriastr = $criteriastr;
        }

        return $inputCriteriastr;
    }

    //TODO: implement this! administrativeDepartment.name?
    public function getCriteriaStrByFilter( $dql, $filter, $inputCriteriastr ) {

        $userSecUtil = $this->container->get('user_security_utility');
        $em = $this->getDoctrine()->getManager();

        $criteriastr = "";

        $mapper = array(
            'prefix' => 'Oleg',
            'bundleName' => 'UserdirectoryBundle',
            'className' => 'Institution'
        );

        //$wcmcpathology
        //$wcmc = $em->getRepository('OlegUserdirectoryBundle:Institution')->findOneByAbbreviation("WCMC");
        //navbarFilterInstitution1
        $wcmc = $userSecUtil->getSiteSettingParameter('navbarFilterInstitution1');
        if( $wcmc ) {
            $wcmcpathology = $em->getRepository('OlegUserdirectoryBundle:Institution')->findByChildnameAndParent(
                "Pathology and Laboratory Medicine",
                $wcmc,
                $mapper
            );
            $inst1 = $wcmc->getAbbreviation();
        }

        //$nyppathology
        //$nyp = $em->getRepository('OlegUserdirectoryBundle:Institution')->findOneByAbbreviation("NYP");
        $nyp = $userSecUtil->getSiteSettingParameter('navbarFilterInstitution2');
        if( $nyp ) {
            $nyppathology = $em->getRepository('OlegUserdirectoryBundle:Institution')->findByChildnameAndParent(
                "Pathology and Laboratory Medicine",
                $nyp,
                $mapper
            );
            $inst2 = $nyp->getAbbreviation();
        }

        //$curdate = date("Y-m-d", time());

        //Pending Administrative Review
        if( $filter && $filter == "Pending Administrative Review" ) {
            $pendingStatus = BaseUserAttributes::STATUS_UNVERIFIED;
            $criteriastr .= "(".
                "administrativeTitles.status = ".$pendingStatus.
                " OR appointmentTitles.status = ".$pendingStatus.
                " OR medicalTitles.status = ".$pendingStatus.
                //" OR locations.status = ".$pendingStatus.
            ")";
        }

        //With Administrative Title
        if( $filter && $filter == "With Administrative Title" ) {
            $dql->leftJoin("administrativeTitles.userPositions", "administrativePositions");
            $verifiedStatus = BaseUserAttributes::STATUS_VERIFIED;
            $criteriastr .= "(".
                "administrativeTitles.id IS NOT NULL".
                " AND administrativeTitles.status = ".$verifiedStatus.
                //only "Head of Institution", "Head of Department", "Head of Division"
                " AND (".
                    "administrativePositions.name = 'Head of Institution'".
                    " OR ".
                    "administrativeName.name LIKE '%Chairman%'".
                    //"administrativePositions.name = 'Head of Department' OR ".
                    //"administrativePositions.name = 'Head of Division'".
                ")".
            ")";
        }

        //Administration Division
        if( $filter && $filter == "Administration Division" ) {
            $dql->leftJoin("administrativeTitles.userPositions", "administrativePositions");
            $verifiedStatus = BaseUserAttributes::STATUS_VERIFIED;
            $criteriastr .= "(".
                "administrativeTitles.id IS NOT NULL".
                " AND administrativeTitles.status = ".$verifiedStatus.
                " AND administrativeInstitution.name = "."'Administration'".
                ")";
        }

        //WCM Pathology Employees Download Faculty
        if( $filter && $inst1 && $filter == $inst1." Pathology Employees Download Faculty" ) {
            $criteriastr .= "(".$this->getCriteriaForAllWcmcPath($criteriastr,$wcmcpathology).")";
            //No "Postdoctoral Associate"
            $criteriastr .= " AND ";
            //$criteriastr .= "(administrativeName.name != 'Postdoctoral Associate' AND appointmentName.name != 'Postdoctoral Associate' AND medicalName.name != 'Postdoctoral Associate')";
            //$criteriastr .= "(medicalName.name != 'Postdoctoral Associate')";
            //$criteriastr .= "(medicalName.name IS NULL OR medicalName.name != 'Postdoctoral Associate')";
            $criteriastr .= "(appointmentName.name IS NOT NULL OR (medicalName.name IS NOT NULL AND medicalName.name != 'Postdoctoral Associate'))";
            //$criteriastr .= "(medicalName.name != 'Postdoctoral Associate' AND (appointmentName.name IS NOT NULL OR medicalName.name IS NOT NULL))";
        }


        //WCM + Pathology
        if( $filter && $inst1 && $filter == $inst1." Pathology Employees" ) {
//            $criteriastr .= "(".
//                "administrativeInstitution.name = 'Weill Cornell Medical College'".
//                " OR appointmentInstitution.name = 'Weill Cornell Medical College'".
//                " OR medicalInstitution.name = 'Weill Cornell Medical College'".
//            ")";
//            $criteriastr .= $this->getCriteriaForAllChildrenUnderNode("administrativeInstitution", $criteriastr,$wcmcpathology);
//            $criteriastr .= " OR ";
//            $criteriastr .= $this->getCriteriaForAllChildrenUnderNode("appointmentInstitution", $criteriastr,$wcmcpathology);
//            $criteriastr .= " OR ";
//            $criteriastr .= $this->getCriteriaForAllChildrenUnderNode("medicalInstitution", $criteriastr,$wcmcpathology);
            $criteriastr .= $this->getCriteriaForAllWcmcPath($criteriastr,$wcmcpathology);
//            $criteriastr .= " AND ";
//            $criteriastr .= "(".
//                "administrativeInstitution.name = 'Pathology and Laboratory Medicine'".
//                " OR appointmentInstitution.name = 'Pathology and Laboratory Medicine'".
//                " OR medicalInstitution.name = 'Pathology and Laboratory Medicine'".
//            ")";
        }

        //Academic Appointment Title exists + Clinical Faculty + Research Faculty
        if( $filter && $inst1 && $filter == $inst1." Pathology Faculty" ) {
            $criteriastr .= $this->getCriteriaForAllChildrenUnderNode("appointmentInstitution", $criteriastr,$wcmcpathology);
            $criteriastr .= " AND ";
            $criteriastr .= "(appointmentTitlesPositions.name = 'Clinical Faculty' OR appointmentTitlesPositions.name = 'Research Faculty')";
        }

        //Academic Appointment Title exists + Clinical Faculty
        if( $filter && $inst1 && $filter == $inst1." Pathology Clinical Faculty" ) {
            $criteriastr .= $this->getCriteriaForAllChildrenUnderNode("appointmentInstitution", $criteriastr,$wcmcpathology);
            $criteriastr .= " AND ";
            $criteriastr .= "(appointmentTitlesPositions.name = 'Clinical Faculty')";
        }

        //list all people with MD, MBBS, and DO degrees (using all current synonym links) and only with Administrative or Academic title in institution "WCMC" and department of "Pathology"
        if( $filter && $inst1 && $filter == $inst1." Pathology Physicians" ) {
            $dql->leftJoin("user.trainings", "trainings");
            $dql->leftJoin("trainings.degree", "degree");
            $dql->leftJoin("degree.original", "original");
            //$criteriastr .= "(administrativeInstitution.name = 'Weill Cornell Medical College' OR appointmentInstitution.name = 'Weill Cornell Medical College' OR medicalInstitution.name = 'Weill Cornell Medical College')";
            //$criteriastr .= $this->getCriteriaForAllWcmcPath($criteriastr,$wcmcpathology);
            //$criteriastr .= " AND ";
            //$criteriastr .= "(administrativeInstitution.name = 'Pathology and Laboratory Medicine' OR appointmentInstitution.name = 'Pathology and Laboratory Medicine' OR medicalInstitution.name = 'Pathology and Laboratory Medicine')";
            $criteriastr .= "(".$this->getCriteriaForAllWcmcPath($criteriastr,$wcmcpathology).")";
            $criteriastr .= " AND ";
            $criteriastr .= "(original.name = 'MD' OR degree.name = 'MD')";
        }

        //Academic Appointment Title exists + Research Faculty
        if( $filter && $inst1 && $filter == $inst1." Pathology Research Faculty" ) {
//            $criteriastr .= "(appointmentInstitution.name = 'Weill Cornell Medical College')";
//            $criteriastr .= " AND ";
//            $criteriastr .= "(appointmentInstitution.name = 'Pathology and Laboratory Medicine')";
            $criteriastr .= $this->getCriteriaForAllChildrenUnderNode("appointmentInstitution", $criteriastr,$wcmcpathology);
            $criteriastr .= " AND ";
            $criteriastr .= "(appointmentTitlesPositions.name = 'Research Faculty')";
        }

        //Academic Appointment Title not exists + Admin Title exists
        if( $filter && $inst1 && $filter == $inst1." Pathology Staff" ) {
            //echo "wcm filter=".$filter."<br>";
            $criteriastr .= "(appointmentInstitution.id IS NULL)";
            $criteriastr .= " AND ";
//            $criteriastr .= "(administrativeInstitution.name = 'Weill Cornell Medical College')";
//            $criteriastr .= " AND ";
//            $criteriastr .= "(administrativeInstitution.name = 'Pathology and Laboratory Medicine')";
            $criteriastr .= $this->getCriteriaForAllChildrenUnderNode("administrativeInstitution", $criteriastr,$wcmcpathology);
        }

        //Academic Appointment Title not exists + Admin Title exists
        if( $filter && $inst2 && $filter == $inst2." Pathology Staff" ) {
            //echo "nyp filter=".$filter."<br>";
            //$criteriastr .= "("; 
            $criteriastr .= "(appointmentInstitution.id IS NULL)";
            $criteriastr .= " AND ";
            $criteriastr .= $this->getCriteriaForAllChildrenUnderNode("administrativeInstitution", $criteriastr,$nyppathology);
            //$criteriastr .= ")"; 
        }

        //Academic Appointment Title exists + division=Anatomic Pathology
        if( $filter && $inst1 && $filter == $inst1." Anatomic Pathology Faculty" ) {
            $wcmcAnatomicPathology = $em->getRepository('OlegUserdirectoryBundle:Institution')->findByChildnameAndParent(
                "Anatomic Pathology",
                $wcmcpathology,
                $mapper
            );
            $criteriastr .= $this->getCriteriaForAllChildrenUnderNode("appointmentInstitution", $criteriastr,$wcmcAnatomicPathology);
            //$criteriastr .= " AND ";
            //$criteriastr .= "(appointmentInstitution.name = 'Anatomic Pathology')";
        }

        //Academic Appointment Title exists + division=Laboratory Medicine
        if( $filter && $inst1 && $filter == $inst1." Laboratory Medicine Faculty" ) {
            $wcmcLaboratoryMedicinePathology = $em->getRepository('OlegUserdirectoryBundle:Institution')->findByChildnameAndParent(
                "Laboratory Medicine",
                $wcmcpathology,
                $mapper
            );
            $criteriastr .= $this->getCriteriaForAllChildrenUnderNode("appointmentInstitution", $criteriastr,$wcmcLaboratoryMedicinePathology);
            //$criteriastr .= " AND ";
            //$criteriastr .= "(appointmentInstitution.name = 'Laboratory Medicine')";
        }

        //As Faculty + Residents == Academic Appointment Title exists + position=Fellow
        if( $filter && $inst1 && $inst2 && $filter == "$inst1 or $inst2 Pathology Fellows" ) {
            //$criteriastr .= $this->getCriteriaForAllChildrenUnderNode("appointmentInstitution", $criteriastr,$wcmc);
            $criteriastr .= $this->getCriteriaForWcmcNypPathology("appointmentInstitution",$criteriastr,$wcmcpathology,$nyppathology);
            $criteriastr .= " AND ";
            $criteriastr .= "(appointmentTitlesPositions.name = 'Fellow')";
        }

        //Similar to "WCM or NYP Pathology Fellows", except it should list all employees who have Academic Appointment Title > "Position Track Type(s):" dropdown set to
        // "Postdoc" or "Research fellow" or "Research Associate"
        // AND associated institution for that Academic Appointment Title set to Weill Cornell Medical College ($wcmc).
        if( $filter && $inst1 && $filter == "$inst1 Non-academic Faculty" ) {
            $criteriastr .= $this->getCriteriaForAllChildrenUnderNode("appointmentInstitution", $criteriastr,$wcmc);
            $criteriastr .= " AND ";
            $criteriastr .= "(appointmentTitlesPositions.name = 'Postdoc' OR appointmentTitlesPositions.name = 'Research Fellow' OR appointmentTitlesPositions.name = 'Research Associate')";
        }

        //As Faculty + Residents == Academic Appointment Title exists + position=Resident
        if( $filter && $inst1 && $inst2 && $filter == "$inst1 or $inst2 Pathology Residents" ) {
            $criteriastr .= $this->getCriteriaForWcmcNypPathology("appointmentInstitution",$criteriastr,$wcmcpathology,$nyppathology);
            $criteriastr .= " AND ";
            $criteriastr .= "(appointmentTitlesPositions.name = 'Resident')"; //OR administrativeName.name = 'Resident' OR medicalName.name = 'Resident')";
        }

        //the same as "WCM Pathology Residents" except they have "AP/CP" in their "Residency Type" field.
        if( $filter && $inst1 && $inst2 && $filter == "$inst1 or $inst2 AP/CP Residents" ) {
            $criteriastr .= $this->getCriteriaForWcmcNypPathology("appointmentInstitution",$criteriastr,$wcmcpathology,$nyppathology);
            $criteriastr .= " AND ";
            $criteriastr .= "(appointmentTitlesPositions.name = 'Resident')";
            $dql->leftJoin("appointmentTitles.residencyTrack", "residencyTrack");
            $criteriastr .= " AND ";
            $criteriastr .= "(residencyTrack.name = 'AP/CP')";
        }

        //the same as "WCM Pathology Residents" except they have "AP" or "AP/CP" in their "Residency Type" field.
        if( $filter && $inst1 && $inst2 && $filter == "$inst1 or $inst2 AP Residents" ) {
            //$criteriastr .= $this->getCriteriaForAllChildrenUnderNode("appointmentInstitution", $criteriastr,$wcmcpathology);
            $criteriastr .= $this->getCriteriaForWcmcNypPathology("appointmentInstitution",$criteriastr,$wcmcpathology,$nyppathology);
            $criteriastr .= " AND ";
            $criteriastr .= "(appointmentTitlesPositions.name = 'Resident')";
            $dql->leftJoin("appointmentTitles.residencyTrack", "residencyTrack");
            $criteriastr .= " AND ";
            $criteriastr .= "(residencyTrack.name = 'AP' OR residencyTrack.name = 'AP/CP')";
        }

        //the same as "WCM Pathology Residents" except they have "AP" in their "Residency Type" field.
        if( $filter && $inst1 && $inst2 && $filter == "$inst1 or $inst2 AP Only Residents" ) {
            //$criteriastr .= $this->getCriteriaForAllChildrenUnderNode("appointmentInstitution", $criteriastr,$wcmcpathology);
            $criteriastr .= $this->getCriteriaForWcmcNypPathology("appointmentInstitution",$criteriastr,$wcmcpathology,$nyppathology);
            $criteriastr .= " AND ";
            $criteriastr .= "(appointmentTitlesPositions.name = 'Resident')";
            $dql->leftJoin("appointmentTitles.residencyTrack", "residencyTrack");
            $criteriastr .= " AND ";
            $criteriastr .= "(residencyTrack.name = 'AP')";
        }

        //the same as "WCM Pathology Residents" except they have "CP" or "AP/CP" in their "Residency Type" field.
        if( $filter && $inst1 && $inst2 && $filter == "$inst1 or $inst2 CP Residents" ) {
            //$criteriastr .= $this->getCriteriaForAllChildrenUnderNode("appointmentInstitution", $criteriastr,$wcmcpathology);
            $criteriastr .= $this->getCriteriaForWcmcNypPathology("appointmentInstitution",$criteriastr,$wcmcpathology,$nyppathology);
            $criteriastr .= " AND ";
            $criteriastr .= "(appointmentTitlesPositions.name = 'Resident')";
            $dql->leftJoin("appointmentTitles.residencyTrack", "residencyTrack");
            $criteriastr .= " AND ";
            $criteriastr .= "(residencyTrack.name = 'CP' OR residencyTrack.name = 'AP/CP')";
        }

        //the same as "WCM Pathology Residents" except they have "CP" in their "Residency Type" field.
        if( $filter && $inst1 && $inst2 && $filter == "$inst1 or $inst2 CP Only Residents" ) {
            $criteriastr .= $this->getCriteriaForWcmcNypPathology("appointmentInstitution",$criteriastr,$wcmcpathology,$nyppathology);
            $criteriastr .= " AND ";
            $criteriastr .= "(appointmentTitlesPositions.name = 'Resident')";
            $dql->leftJoin("appointmentTitles.residencyTrack", "residencyTrack");
            $criteriastr .= " AND ";
            $criteriastr .= "(residencyTrack.name = 'CP')";
        }

        // the same as "WCM Pathology Faculty" except they have at least one non-empty "Research Lab Title:" + a checkmark in
        //"Principal Investigator of this Lab:" with an empty or future "Dissolved on: [Date]" for Current / past or empty or future "Dissolved on: [Date]" for Previous
        if( $filter && $inst1 && $filter == "$inst1 Pathology Principal Investigators of Research Labs" ) {
            $criteriastr .= $this->getCriteriaForAllChildrenUnderNode("appointmentInstitution", $criteriastr,$wcmcpathology);
            $criteriastr .= " AND ";
            $criteriastr .= "(appointmentTitlesPositions.name = 'Clinical Faculty' OR appointmentTitlesPositions.name = 'Research Faculty')";

            //have Research Lab
            $criteriastr .= " AND ";
            $criteriastr .= "(researchLabs.id IS NOT NULL)";
            
            //a checkmark in "Principal Investigator of this Lab:" researchLabsPis.pi = this user
            $criteriastr .= " AND ";
            $criteriastr .= "(researchLabsPis.pi = user)";
        }

        // "WCM Pathology Faculty in Research Labs" - the same as "WCM Pathology Faculty"
        //except they have at least one non-empty "Research Lab Title:" with an empty or future "Dissolved on: [Date]" for Current / past or empty or future "Dissolved on: [Date]" for Previous
        if( $filter && $inst1 && $filter == "$inst1 Pathology Faculty in Research Labs" ) {
            $criteriastr .= $this->getCriteriaForAllChildrenUnderNode("appointmentInstitution", $criteriastr,$wcmcpathology);
            $criteriastr .= " AND ";
            $criteriastr .= "(appointmentTitlesPositions.name = 'Clinical Faculty' OR appointmentTitlesPositions.name = 'Research Faculty')";
            
            //have Research Lab
            $criteriastr .= " AND ";
            $criteriastr .= "(researchLabs.id IS NOT NULL)";
        }


        // "WCM or NYP Pathology Staff in Research Labs" - the same as "WCM Pathology Staff" OR "NYP Pathology Staff"
        //except they have at least one non-empty "Research Lab Title:" with an empty or future "Dissolved on: [Date]" for Current / past or empty or future "Dissolved on: [Date]" for Previous
        if( $filter && $inst1 && $inst2 && $filter == "$inst1 or $inst2 Pathology Staff in Research Labs" ) {
            //echo "wcm or nyp filter=".$filter."<br>";
            $criteriastr .= "(appointmentInstitution.id IS NULL)";
            $criteriastr .= " AND ";
            //$criteriastr .= "administrativeInstitution.name = 'Weill Cornell Medical College' AND administrativeInstitution.name = 'Pathology and Laboratory Medicine'";
            $criteriastr .= $this->getCriteriaForAllChildrenUnderNode("administrativeInstitution", $criteriastr,$wcmcpathology);
            $criteriastr .= " OR ";
            //$criteriastr .= "administrativeInstitution.name = 'New York Hospital' AND administrativeInstitution.name = 'Pathology'";
            $criteriastr .= $this->getCriteriaForAllChildrenUnderNode("administrativeInstitution", $criteriastr,$nyppathology);
            $criteriastr .= "";
            
            //have Research Lab
            $criteriastr .= " AND ";
            $criteriastr .= "(researchLabs.id IS NOT NULL)";
        }


        if( $filter && $filter != "" && $criteriastr == "" ) {
            $criteriastr = "1 = 0";
            $this->get('session')->getFlashBag()->add(
                'notice',
                "Filter not found: ".$filter
            );
        }

        if( $inputCriteriastr && $inputCriteriastr != "" ) {
            if( $criteriastr != "" ) {
                $inputCriteriastr = $inputCriteriastr . " AND (" . $criteriastr . ")";
            }
        } else {
            $inputCriteriastr = $criteriastr;
        }

        return $inputCriteriastr;
    }


    public function getCriteriaForAllChildrenUnderNode( $fieldstr, $criteriastr, $node, $withbrakets=true ) {
        if( !$node ) {
            //echo "Return: node=".$node."<br>";
            return $criteriastr;
            //new \Exception('Tree node does not exists');
        }
        
        if( $withbrakets ) {
            $criteriastr .= " ( ";
        }
        
        $criteriastr .= $fieldstr.".root = " . $node->getRoot();
        $criteriastr .= " AND ";
        $criteriastr .= $fieldstr.".lft > " . $node->getLft();
        $criteriastr .= " AND ";
        $criteriastr .= $fieldstr.".rgt < " . $node->getRgt();
        $criteriastr .= " OR ";
        $criteriastr .= $fieldstr.".id = " . $node->getId();
        
        if( $withbrakets ) {
            $criteriastr .= " ) ";
        }
        
        return $criteriastr;
    }

    public function getCriteriaForAllWcmcPath( $criteriastr, $wcmcpathology ) {
        $criteriastr .= $this->getCriteriaForAllChildrenUnderNode("administrativeInstitution", $criteriastr,$wcmcpathology);
        $criteriastr .= " OR ";
        $criteriastr .= $this->getCriteriaForAllChildrenUnderNode("appointmentInstitution", $criteriastr,$wcmcpathology);
        $criteriastr .= " OR ";
        $criteriastr .= $this->getCriteriaForAllChildrenUnderNode("medicalInstitution", $criteriastr,$wcmcpathology);
        return $criteriastr;
    }
    
    public function getCriteriaForWcmcNypPathology( $fieldstr, $criteriastr, $wcmcpathology, $nyppathology ) {
                    
        if( !$wcmcpathology || !$nyppathology ) {
            return $criteriastr;           
        }
        
        $criteriastr .= "(";
        
        //$criteriastr .= $this->getCriteriaForAllChildrenUnderNode("appointmentInstitution", $criteriastr,$wcmcpathology);    
        $criteriastr .= "(";
        $criteriastr .= $fieldstr.".root = " . $wcmcpathology->getRoot();
        $criteriastr .= " AND ";
        $criteriastr .= $fieldstr.".lft > " . $wcmcpathology->getLft();
        $criteriastr .= " AND ";
        $criteriastr .= $fieldstr.".rgt < " . $wcmcpathology->getRgt();
        $criteriastr .= " OR ";
        $criteriastr .= $fieldstr.".id = " . $wcmcpathology->getId();
        $criteriastr .= ")";
        
        $criteriastr .= " OR ";
        
        //$criteriastr .= $this->getCriteriaForAllChildrenUnderNode("appointmentInstitution", $criteriastr,$nyppathology);
        $criteriastr .= "(";
        $criteriastr .= $fieldstr.".root = " . $nyppathology->getRoot();
        $criteriastr .= " AND ";
        $criteriastr .= $fieldstr.".lft > " . $nyppathology->getLft();
        $criteriastr .= " AND ";
        $criteriastr .= $fieldstr.".rgt < " . $nyppathology->getRgt();
        $criteriastr .= " OR ";
        $criteriastr .= $fieldstr.".id = " . $nyppathology->getId();
        $criteriastr .= ")";
        
        $criteriastr .= ")";
        
        return $criteriastr;
    }

//    public function getMyTeam( $dql, $myteam, $myboss, $inputCriteriastr ) {
//
//        $user = $this->get('security.token_storage')->getToken()->getUser();
//
//        $criteriastr = "";
//
//        //Me Boss: list names of users who have me listed as their boss in their profile and link each name to the user's profile
//        if( $myteam && $myteam == "myreports" ) {
//            $dql->leftJoin("administrativeTitles.boss", "boss");
//            $criteriastr = "user.id != " . $user->getId() . " AND " . "boss.id = " . $user->getId();
//        }
//
//        //The Same Boss: list names of users who have the same boss as me in their profile
//        if( $myteam && $myteam == "mygroups" ) {
//            if( $myboss ) {
//                $dql->leftJoin("administrativeTitles.boss", "boss");
//                $criteriastr = "boss.id = " . $myboss . " AND user.id != " . $user->getId();
//            }
//        }
//
////        //users with this service
////        if( $myteam && $myteam == "myservices" ) {
////            if( $myservice ) {
////                $criteriastr = "(administrativeService.id = " . $myservice . " OR " . "appointmentService.id = " . $myservice . ") AND " . "user.id != " . $user->getId();
////            }
////        }
//
//
//        if( $inputCriteriastr && $inputCriteriastr != "" ) {
//            if( $criteriastr != "" ) {
//                $inputCriteriastr = $inputCriteriastr . " AND (" . $criteriastr . ")";
//            }
//        } else {
//            $inputCriteriastr = $criteriastr;
//        }
//
//        //echo "inputCriteriastr=".$inputCriteriastr."<br>";
//
//        return $inputCriteriastr;
//    }


    public function getTheSameObject( $dql, $subjectUserId, $objectname, $objectid, $excludeCurrentUser, $inputCriteriastr ) {

        //echo "objectname=".$objectname.", objectid=".$objectid."<br>";
        //exit();
        
        $em = $this->getDoctrine()->getManager();

        if( $subjectUserId ) {
            $user = $em->getRepository('OlegUserdirectoryBundle:User')->find($subjectUserId);
        } else {
            $user = $this->get('security.token_storage')->getToken()->getUser();
        }

        $criteriastr = "";

        if( $objectname && strtolower($objectname) == "institution" ) {
            if( !$objectid || $objectid != "" ) {
//                $criteriastr .= "administrativeInstitution.id = " . $objectid;
//                $criteriastr .= " OR ";
//                $criteriastr .= "appointmentInstitution.id = " . $objectid;
//                $criteriastr .= " OR ";
//                $criteriastr .= "medicalInstitution.id = " . $objectid;
              
                $node = $em->getRepository('OlegUserdirectoryBundle:Institution')->find($objectid);

                //administrativeInstitution
                $criteriastr .= " ( ";
                $criteriastr .= "administrativeInstitution.lft > " . $node->getLft();
                $criteriastr .= " AND ";
                $criteriastr .= "administrativeInstitution.rgt < " . $node->getRgt();
                $criteriastr .= " OR ";
                $criteriastr .= "administrativeInstitution.id = " . $objectid;
                $criteriastr .= " ) ";

                $criteriastr .= " OR ";

                //appointmentInstitution
                $criteriastr .= " ( ";
                $criteriastr .= "appointmentInstitution.lft > " . $node->getLft();
                $criteriastr .= " AND ";
                $criteriastr .= "appointmentInstitution.rgt < " . $node->getRgt();
                $criteriastr .= " OR ";
                $criteriastr .= "appointmentInstitution.id = " . $objectid;
                $criteriastr .= " ) ";

                $criteriastr .= " OR ";

                //medicalInstitution
                $criteriastr .= " ( ";
                $criteriastr .= "medicalInstitution.lft > " . $node->getLft();
                $criteriastr .= " AND ";
                $criteriastr .= "medicalInstitution.rgt < " . $node->getRgt();
                $criteriastr .= " OR ";
                $criteriastr .= "medicalInstitution.id = " . $objectid;
                $criteriastr .= " ) ";

            } else {
                $criteriastr = "1=0";
            }
        }

//        if( $objectname && $objectname == "service" ) {
//            if( !$objectid || $objectid != "" ) {
//                $criteriastr .= "administrativeInstitution.id = " . $objectid;
//                $criteriastr .= " OR ";
//                $criteriastr .= "appointmentInstitution.id = " . $objectid;
//                $criteriastr .= " OR ";
//                $criteriastr .= "medicalInstitution.id = " . $objectid;
//            } else {
//                $criteriastr = "1=0";
//            }
//        }

        if( $objectname && strtolower($objectname) == "administrativetitle" ) {
            if( !$objectid || $objectid != "" ) {
                $criteriastr .= "administrativeTitles.name = '" . $objectid . "'";
            } else {
                $criteriastr = "1=0";
            }
        }

        if( $objectname && strtolower($objectname) == "appointmenttitle" ) {
            if( !$objectid || $objectid != "" ) {
                $criteriastr .= "appointmentTitles.name = '" . $objectid . "'";
            } else {
                $criteriastr = "1=0";
            }
        }

        if( $objectname && strtolower($objectname) == "medicaltitle" ) {
            if( !$objectid || $objectid != "" ) {
                $criteriastr .= "medicalTitles.name = '" . $objectid . "'";
            } else {
                $criteriastr = "1=0";
            }
        }

        if( $objectname && strtolower($objectname) == "room" ) {
            if( !$objectid || $objectid != "" ) {
                $criteriastr .= "locations.room = '" . $objectid . "'";
            } else {
                $criteriastr = "1=0";
            }
        }

//        if( $objectname && $objectname == "department" ) {
//            if( !$objectid || $objectid != "" ) {
//                $criteriastr .= "administrativeInstitution.id = " . $objectid;
//                $criteriastr .= " OR ";
//                $criteriastr .= "appointmentInstitution.id = " . $objectid;
//                $criteriastr .= " OR ";
//                $criteriastr .= "medicalInstitution.id = " . $objectid;
//            } else {
//                $criteriastr = "1=0";
//            }
//        }
//
//        if( $objectname && $objectname == "division" ) {
//            if( !$objectid || $objectid != "" ) {
//                $criteriastr .= "administrativeDivision.id = " . $objectid;
//                $criteriastr .= " OR ";
//                $criteriastr .= "appointmentInstitution.id = " . $objectid;
//                $criteriastr .= " OR ";
//                $criteriastr .= "medicalDivision.id = " . $objectid;
//            } else {
//                $criteriastr = "1=0";
//            }
//        }

        if( $objectname && $objectname == "myboss" ) {
            if( !$objectid || $objectid != "" ) {
                $dql->leftJoin("administrativeTitles.boss", "boss");
                $criteriastr = "boss.id = " . $objectid;
            } else {
                $criteriastr = "1=0";
            }
        }

        if( $objectname && $objectname == "myreports" ) {
            $dql->leftJoin("administrativeTitles.boss", "boss");
            $criteriastr = "boss.id = " . $user->getId();
        }

        if( $objectname && $objectname == "researchlabs" ) {
            if( !$objectid || $objectid != "" ) {
                $criteriastr = "researchLabs.id = " . $objectid;
            } else {
                $criteriastr = "1=0";
            }
        }

        if( $objectname && $objectname == "assistances" ) {
            //get user's assistants
            $assistantsRes = $user->getAssistants();
            $assistants = $assistantsRes['ids'];
            if( count($assistants) > 0 ) {
                $assistantsStr = implode(",", $assistants);
                $criteriastr = "user.id IN (" . $assistantsStr . ")";
            } else {
                $criteriastr = "1=0";
            }
        }

        if( $objectname && $objectname == "mybosses" ) {
            if( count($objectid) > 0 ) {
                $bossesStr = implode(",", $objectid);
                $criteriastr = "user.id IN (" . $bossesStr . ")";
            } else {
                $criteriastr = "1=0";
            }
        }

        //exclude current user
        if( $excludeCurrentUser ) {
            if( $criteriastr != "" ) {
                $criteriastr = "user.id != " . $user->getId() . " AND (" . $criteriastr . ")";
            } else {
                $criteriastr = "user.id != " . $user->getId();
            }
        }

        //echo "criteriastr=".$criteriastr."<br>";

        if( $inputCriteriastr && $inputCriteriastr != "" ) {
            if( $criteriastr != "" ) {
                $inputCriteriastr = $inputCriteriastr . " AND (" . $criteriastr . ")";
            }
        } else {
            $inputCriteriastr = $criteriastr;
        }

        //echo "inputCriteriastr=".$inputCriteriastr."<br>";
        //exit();

        return $inputCriteriastr;
    }



    public function pendingAdminReviewAction() {
        
        //testing
        //$response = new Response();
        //$response->setContent(null);
        //return $response;
        
        $pending = null;

        if( false === $this->get('security.authorization_checker')->isGranted('ROLE_USERDIRECTORY_EDITOR') ) {
            $response = new Response();
            $response->setContent($pending);
            return $response;
        }

        $limitFlag = false;

        //$filter=null, $time='all', $limitFlag=true, $search=null, $userid=null
//        $params = array('filter'=>'Pending Administrative Review','time'=>'current_only','limitFlag'=>$limitFlag);
//        $res = $this->indexUser($request,$params);
//        $pendingOld = count($res['entities']);
//        echo "pendingOld=".$pendingOld."<br>";
        
        
        $pendingStatus = BaseUserAttributes::STATUS_UNVERIFIED;
        $criteriastr = "(".
            "administrativeTitles.status = ".$pendingStatus.
            " OR appointmentTitles.status = ".$pendingStatus.
            " OR medicalTitles.status = ".$pendingStatus.
            //" OR locations.status = ".$pendingStatus.
            ")";
        
        //current_only
        $curdate = date("Y-m-d", time());
        $criteriastr .= " AND (";
        $criteriastr .= "employmentStatus.id IS NULL";
        $criteriastr .= " OR ";     
        $criteriastr .= "employmentStatus.terminationDate IS NULL OR employmentStatus.terminationDate > '".$curdate."'";
        $criteriastr .= ")";
           
        //filter out system user
        $totalcriteriastr = "user.keytype IS NOT NULL AND user.primaryPublicUserId != 'system'";

        //filter out Pathology Fellowship Applicants
        $totalcriteriastr = $totalcriteriastr . " AND (employmentType.name != 'Pathology Fellowship Applicant' OR employmentType.id IS NULL)";

        if( $criteriastr ) {
            $totalcriteriastr = $totalcriteriastr . " AND (".$criteriastr.")";
        } 
        
        //$totalcriteriastr = "user.keytype IS NOT NULL AND user.primaryPublicUserId != 'system' AND (employmentType.name != 'Pathology Fellowship Applicant' OR employmentType.id IS NULL) AND (((administrativeTitles.status = 0 OR appointmentTitles.status = 0 OR medicalTitles.status = 0 OR locations.status = 0)) AND (((employmentStatus.id IS NULL) OR employmentStatus.terminationDate IS NULL OR employmentStatus.terminationDate > '2015-11-05')))";
        
        $em = $this->getDoctrine()->getManager();  
        $repository = $this->getDoctrine()->getRepository('OlegUserdirectoryBundle:User');
        $dql = $repository->createQueryBuilder('user');
        $dql->select('COUNT(DISTINCT user.id)');
        //$dql->select('COUNT(user.id)');
        
        $dql->leftJoin("user.administrativeTitles", "administrativeTitles");
        $dql->leftJoin("user.appointmentTitles", "appointmentTitles");
        $dql->leftJoin("user.medicalTitles", "medicalTitles");
        $dql->leftJoin("user.locations", "locations");
        $dql->leftJoin("user.employmentStatus", "employmentStatus");
        $dql->leftJoin("employmentStatus.employmentType", "employmentType");   
        //$dql->orderBy('user.id');

        
//        $qb = $em->createQueryBuilder();
//        $qb->select($qb->expr()->countDistinct('user.id'));
//        $qb->from('OlegUserdirectoryBundle:User','user');
//        $qb->where($totalcriteriastr);
//        //$qb->groupBy('user');
//        $qb->leftJoin("user.administrativeTitles", "administrativeTitles");
//        $qb->leftJoin("user.appointmentTitles", "appointmentTitles");
//        $qb->leftJoin("user.medicalTitles", "medicalTitles");
//        $qb->leftJoin("user.locations", "locations");
//        $qb->leftJoin("user.employmentStatus", "employmentStatus");
//        $qb->leftJoin("employmentStatus.employmentType", "employmentType");
//        $count = $qb->getQuery()->getSingleScalarResult();
//        echo "count=".$count."<br>";
        //print_r($count);
        
        //echo "totalcriteriastr=".$totalcriteriastr."<br>";
        
        $dql->where($totalcriteriastr);
        $query = $em->createQuery($dql);      

        $pending = $query->getSingleScalarResult();       
        //$pending = $query->getResult(\Doctrine\ORM\Query::HYDRATE_SINGLE_SCALAR);
        
        //echo "pending=".$pending."<br>";
        
        $response = new Response();
        $response->setContent($pending);

        return $response;
    }





    ////////////////////// Create New User //////////////////////
    /**
     * @Route("/user/new", name="employees_new_user")
     * @Route("/user/new/clone/{id}", name="employees_new_user_clone", requirements={"id" = "\d+"})
     * @Method("GET")
     * @Template("OlegUserdirectoryBundle:Profile:edit_user.html.twig")
     */
    public function newUserAction(Request $request,$id=null)
    {

        if( false === $this->get('security.authorization_checker')->isGranted('ROLE_USERDIRECTORY_EDITOR') ) {
            return $this->redirect( $this->generateUrl('employees-nopermission') );
        }

        $em = $this->getDoctrine()->getManager();

        $userGenerator = $this->container->get('user_generator');

        //echo "user id=".$id."<br>";
        //exit();

        $userManager = $this->container->get('fos_user.user_manager');
        $user = $userManager->createUser();

        $creator = $this->get('security.token_storage')->getToken()->getUser();
        $user = $userGenerator->addDefaultLocations($user,$creator);

        $userSecUtil = $this->get('user_security_utility');
        $userkeytype = $userSecUtil->getDefaultUsernameType();
        $user->setKeytype($userkeytype);

        $user->setPassword("");

        //set optional user-type and user-name
        $userType = $request->query->get('user-type');
        if( $userType ) {
            $keytypeObj = $em->getRepository('OlegUserdirectoryBundle:UsernameType')->find($userType);
            $user->setKeytype($keytypeObj);
        }

        $userName = $request->query->get('user-name');
        if( $userName ) {
            $user->setPrimaryPublicUserId($userName);
        }

        //Only show this profile to members of the following institution(s): default preset choices WCMC, NYP
        $wcmc = $em->getRepository('OlegUserdirectoryBundle:Institution')->findOneByAbbreviation("WCMC");
        $nyp = $em->getRepository('OlegUserdirectoryBundle:Institution')->findOneByAbbreviation("NYP");
        //echo "add inst:".$wcmc."; ".$nyp."<br>";
        $user->getPreferences()->addShowToInstitution($wcmc);
        $user->getPreferences()->addShowToInstitution($nyp);

        //set empty collections
        $this->addEmptyCollections($user);

        //clone user
        $subjectUser = null;
        if( $id && $id != "" ) {
            $subjectUser = $em->getRepository('OlegUserdirectoryBundle:User')->find($id);
            $userUtil = new UserUtil();
            $user = $userUtil->makeUserClone($subjectUser,$user);
        } else {
            //organizationalGroupDefault - match it to the organizational group selected in the "Defaults for an Organizational Group" in Site Settings,
            // then load the corresponding default values into the page on initial load
            $userUtil = new UserUtil();
            $user = $userUtil->populateDefaultUserFields($creator,$user,$em);
        }

        //add EIN identifier to credentials
        $identEin = new Identifier();
        $identKeytypeEin = $em->getRepository('OlegUserdirectoryBundle:IdentifierTypeList')->findOneByName("Employee Identification Number (EIN)");
        if( $identKeytypeEin ) {
            $identEin->setKeytype($identKeytypeEin);
        }
        $user->getCredentials()->addIdentifier($identEin);

        //add NPI identifier to credentials
        $identNpi = new Identifier();
        $identKeytypeNpi = $em->getRepository('OlegUserdirectoryBundle:IdentifierTypeList')->findOneByName("National Provider Identifier (NPI)");
        if( $identKeytypeNpi ) {
            $identNpi->setKeytype($identKeytypeNpi);
        }
        $user->getCredentials()->addIdentifier($identNpi);

        //Roles
        $rolesArr = $this->getUserRoles(); //new user form

        $params = array(
            'cycle' => 'create',
            'user' => $user,
            'cloneuser' => $subjectUser,
            'roles' => $rolesArr,
            'container' => $this->container,
            'em' => $em
        );

//        $form = $this->createForm(new UserType($params), $user, array(
//            'disabled' => false,
//            'action' => $this->generateUrl( $this->container->getParameter('employees.sitename').'_create_user' ),
//            'method' => 'POST',
//        ));
        $form = $this->createForm(UserType::class, $user, array(
            'disabled' => false,
            'action' => $this->generateUrl( $this->container->getParameter('employees.sitename').'_create_user' ),
            'method' => 'POST',
            'form_custom_value' => $params,
        ));

        //return $this->container->get('templating')->renderResponse('FOSUserBundle:Profile:show.html.'.$this->container->getParameter('fos_user.template.engine'), array('user' => $user));
        return array(
            'entity' => $user,
            'form' => $form->createView(),
            'cycle' => 'create_user',
            'user_id' => '',
            'sitename' => $this->container->getParameter('employees.sitename'),
            'userclone' => $subjectUser,
            'postData' => $request->query->all(),
            'title' => 'Create New User'
        );

    }

    /**
     * url: http://localhost/order/directory/user/new/simple-ajax-form/
     *
     * @Route("/user/new/simple-ajax-form/", name="employees_new_simple_user", options={"expose"=true})
     * @Method("GET")
     * @Template("OlegUserdirectoryBundle:Profile:new_simple_user.html.twig")
     */
    public function newSimpleUserAction(Request $request)
    {
        if( false === $this->get('security.authorization_checker')->isGranted('ROLE_USER') ) {
            return $this->redirect( $this->generateUrl('employees-nopermission') );
        }

        $em = $this->getDoctrine()->getManager();

        $comboboxValue = $request->get('comboboxValue');
        //echo "comboboxValue=$comboboxValue<br>";

        $comboboxValueArr = explode(" ",$comboboxValue);
        if( count($comboboxValueArr) > 1 ) {
            //1- If the entered string contains at least one space (John Smith),
            // take the word after the last space and populate it into the "Last Name" field of the modal (Smith).
            // Populate the "First Name" field of the modal with everything before the last space (John).
            //$firstName = $comboboxValueArr[0];
            $firstName = array_shift($comboboxValueArr);
            //$lastName = $comboboxValueArr[1];
            $lastName = implode(" ",$comboboxValueArr);
        } else {
            //2- If the string contains no spaces, populate the Last Name field of the modal with the entire string (JohnSmith)
            $firstName = null;
            $lastName = $comboboxValue;
        }

        $userGenerator = $this->container->get('user_generator');

        $userManager = $this->container->get('fos_user.user_manager');
        $user = $userManager->createUser();

        $creator = $this->get('security.token_storage')->getToken()->getUser();
        $user = $userGenerator->addDefaultLocations($user,$creator);

        $userSecUtil = $this->get('user_security_utility');
        $userkeytype = $userSecUtil->getDefaultUsernameType();
        $user->setKeytype($userkeytype);

        $user->setPassword("");

        //set optional user-type and user-name
        $userType = $request->query->get('user-type');
        if( $userType ) {
            $keytypeObj = $em->getRepository('OlegUserdirectoryBundle:UsernameType')->find($userType);
            $user->setKeytype($keytypeObj);
        }

        $userName = $request->query->get('user-name');
        if( $userName ) {
            $user->setPrimaryPublicUserId($userName);
        }

        //Only show this profile to members of the following institution(s): default preset choices WCMC, NYP
        $wcmc = $em->getRepository('OlegUserdirectoryBundle:Institution')->findOneByAbbreviation("WCMC");
        $nyp = $em->getRepository('OlegUserdirectoryBundle:Institution')->findOneByAbbreviation("NYP");
        //echo "add inst:".$wcmc."; ".$nyp."<br>";
        $user->getPreferences()->addShowToInstitution($wcmc);
        $user->getPreferences()->addShowToInstitution($nyp);

        //set empty collections
        $this->addEmptyCollections($user);

        //clone user
        $subjectUser = null;
        //organizationalGroupDefault - match it to the organizational group selected in the "Defaults for an Organizational Group" in Site Settings,
        // then load the corresponding default values into the page on initial load
        $userUtil = new UserUtil();
        $user = $userUtil->populateDefaultUserFields($creator,$user,$em);

        //Roles
        $rolesArr = $this->getUserRoles(); //new user form


        //create user info if not exists
        if( count($user->getInfos()) == 0 ) {
            //$userInfo = new UserInfo();
            //$user->addInfo($userInfo);
        }
        //set user info
        $user->setFirstName($firstName);
        $user->setLastName($lastName);

        $primaryPublicUserIdLabel = $userSecUtil->getSiteSettingParameter('noticeLdapName');

        $params = array(
            'cycle' => 'create',
            'user' => $user,
            'cloneuser' => $subjectUser,
            'roles' => $rolesArr,
            'container' => $this->container,
            'em' => $em,
            'primaryPublicUserIdLabel' => $primaryPublicUserIdLabel
        );

//        $form = $this->createForm(new UserType($params), $user, array(
//            'disabled' => false,
//            'action' => $this->generateUrl( $this->container->getParameter('employees.sitename').'_create_user' ),
//            'method' => 'POST',
//        ));
        $form = $this->createForm(UserSimpleType::class, $user, array(
            'disabled' => false,
            //'action' => $this->generateUrl( $this->container->getParameter('employees.sitename').'_create_user' ),
            //'method' => 'POST',
            'form_custom_value' => $params,
        ));

        //return $this->container->get('templating')->renderResponse('FOSUserBundle:Profile:show.html.'.$this->container->getParameter('fos_user.template.engine'), array('user' => $user));
        return array(
            'entity' => $user,
            'form' => $form->createView(),
            'cycle' => 'create_user',
            'user_id' => '',
            'sitename' => $this->container->getParameter('employees.sitename'),
            'userclone' => $subjectUser,
            'postData' => $request->query->all(),
            //'title' => 'Create New User'
        );

    }
    /**
     * @Route("/search-user-ldap-ajax/", name="employees_search_user_ldap_ajax", options={"expose"=true})
     * @Method("GET")
     */
    public function searchUserLdapAjaxAction(Request $request)
    {
        if (false === $this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            return $this->redirect($this->generateUrl('employees-nopermission'));
        }

        $em = $this->getDoctrine()->getManager();
        
        $resArr = array();
        $primaryPublicUserId = null;

        $searchvalue = $request->get('searchvalue');
        $inputType = $request->get('type');
        echo "searchvalue=$searchvalue <br>";
        echo "inputType=$inputType <br>";

        if( $inputType == "primaryPublicUserId" ) {
            $primaryPublicUserId = $searchvalue;
        }

        if( $inputType == "email" ) {
            $emailParts = explode("@",$searchvalue);
            if( count($emailParts) == 2 ) {
                $firstEmailPart = $emailParts[0];
                $secondEmailPart = $emailParts[1];
                $publicUserId = $firstEmailPart;
            }
            $primaryPublicUserId = $firstEmailPart;
        }

        if( !$primaryPublicUserId ) {
            exit('exit user search no primaryPublicUserId');
            $json = json_encode($resArr);
            $response = new Response($json);
            $response->headers->set('Content-Type', 'application/json');
            return $response;
        }

        $authUtil = new AuthUtil($this->container,$em);
        $searchRes = $authUtil->searchLdap($primaryPublicUserId);
        if( $searchRes == NULL || count($searchRes) == 0 ) {
            $msg = "LdapAuthentication: can not find user by publicUserId=".$publicUserId;
            echo "msg=$msg <br>";
            //create local user: oli2002c_@_local-user
            $username = $primaryPublicUserId . "_@_" . "local-user";
        } else {
            //create WCMC LDAP user: oli2002c_@_ldap-user
            echo "<pre>";
            print_r($searchRes);
            echo "</pre>";
            echo "LDAP user found<br>";
//            $username = $primaryPublicUserId . "_@_" . "ldap-user"; //"ldap-user" default username postfix
            //TODO: compare email domain for ldap-user or ldap2-user and use ldap according to the domain
//            $emailMapperPostfix1 = $userSecUtil->getSiteSettingParameter("ldapMapperEmail");
//            if( $emailMapperPostfix1 && $secondEmailPart == $emailMapperPostfix1 ) {
//                $username = $publicUserId . "_@_" . "ldap-user";
//            } else {
//                $emailMapperPostfix2 = $userSecUtil->getSiteSettingParameter("ldapMapperEmail2");
//                if( $emailMapperPostfix2 && $secondEmailPart == $emailMapperPostfix2 ) {
//                    $username = $publicUserId . "_@_" . "ldap2-user";
//                }
//            }

        }

        exit('exit user search');
        $json = json_encode($resArr);
        $response = new Response($json);
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }
    /**
     * @Route("/add-new-user-ajax/", name="employees_add_new_user_ajax", options={"expose"=true})
     * @Method("POST")
     */
    public function addNewUserAjaxAction(Request $request)
    {
        if (false === $this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            return $this->redirect($this->generateUrl('employees-nopermission'));
        }

        return $this->addNewUserAjax($request); //$this->container->getParameter('employees.sitename')
    }
    public function addNewUserAjax($request) {

        $testing = false;
        //$testing = true;

        $resArr = array(
            "flag" => "NOTOK",
            "error" => "Unknown Error"
        );

        $fieldId = $request->get('fieldId');
        $sitename = $request->get('sitename');
        $otherUserParam = $request->get('otherUserParam');
        $publicUserId = $request->get('cwid');
        $email = $request->get('email');
        $displayname = $request->get('displayname');
        $firstname = $request->get('firstname');
        $lastname = $request->get('lastname');
        $phone = $request->get('phone');
        $administrativetitle = $request->get('administrativetitle');
        $institution = $request->get('institution');

        //echo "publicUserId=$publicUserId<br>";

        $creator = $this->get('security.token_storage')->getToken()->getUser();
        $em = $this->getDoctrine()->getManager();
        $userSecUtil = $this->get('user_security_utility');
        $userServiceUtil = $this->get('user_service_utility');
        $username = null;
        $firstEmailPart = null;
        $secondEmailPart = null;

        $emailParts = explode("@",$email);
        if( count($emailParts) == 2 ) {
            $firstEmailPart = $emailParts[0];
            $secondEmailPart = $emailParts[1];
            $publicUserId = $firstEmailPart;
        }

        if( !$publicUserId ) {
            $publicUserId = $firstEmailPart;
        }

        if( !$displayname ) {
            if( $firstname && $lastname ) {
                $displayname = $firstname . " " . $lastname;
            }
            if( $firstname && !$lastname ) {
                $displayname = $firstname;
            }
            if( !$firstname && $lastname ) {
                $displayname = $lastname;
            }
        }

        //echo "publicUserId=$publicUserId<br>";

        if( $publicUserId ) {
            $user = $em->getRepository('OlegUserdirectoryBundle:User')->findOneByPrimaryPublicUserId($publicUserId);
            if (!$user) {
                $user = $em->getRepository('OlegUserdirectoryBundle:User')->findOneByEmailCanonical($email);

            }
            if (!$user) {
                $users = $em->getRepository('OlegUserdirectoryBundle:User')->findUserByUserInfoEmail($email);
                if (count($users) > 0) {
                    $user = $users[0];
                }
            }
        }

        if( $user ) {
            $thisUrl = $this->container->get('router')->generate(
                $sitename.'_showuser',
                array(
                    'id'=>$user->getId()
                ),
                UrlGeneratorInterface::ABSOLUTE_URL
            );
            $thisLink = '<a target="_blank" href='.$thisUrl.'>'.$user.'</a>';

            $fieldId = "'".$fieldId."'";
            $selectExistingPerson =
                '<a onclick="selectExistingUserComboboxes(this,'.$user->getId().','.$fieldId.');"'.
                ' href="javascript:;"'.
                '>click here to select this existing person</a>'
            ;

            $systemEmail = $userSecUtil->getSiteSettingParameter('siteEmail');
//            $systemAdministrator =
//                '<a onclick="selectExistingUser('.$user->getId().');" href='.
//                $thisUrl.'>contact the system administrator</a>'
//            ;
            $systemAdministrator = "contact the system administrator $systemEmail";

            //A person with the CWID of "xxx007" already has an account on this system and
            // should be listed in the dropdown menus of the Project Request page.
            // Please <click here to select this existing person>, change the CWID of the new,
            // different person you would like to add, or <contact the system administrator>
            // if you believe you discovered an issue.
            $resArr["error"] =
                "A person $thisLink already has an account on this system and" .
                " should be listed in the dropdown menus of the Project Request page." .
                "<br>Please $selectExistingPerson, change the user name of the new," .
                "<br>different person you would like to add, or $systemAdministrator" .
                " if you believe you discovered an issue."
            ;

            $json = json_encode($resArr);
            $response = new Response($json);
            $response->headers->set('Content-Type', 'application/json');
            return $response;
        }//if user

        //first search this user if exists in ldap directory
        $authUtil = new AuthUtil($this->container,$em);
        $searchRes = $authUtil->searchLdap($publicUserId);
        if( $searchRes == NULL || count($searchRes) == 0 ) {
            //$msg = "LdapAuthentication: can not find user by publicUserId=".$publicUserId;
            //echo "msg=$msg <br>";
            //create local user: oli2002c_@_local-user
            $username = $publicUserId . "_@_" . "local-user";
        } else {
            //create WCMC LDAP user: oli2002c_@_ldap-user
            //echo "create WCMC LDAP user<br>";
            $username = $publicUserId . "_@_" . "ldap-user"; //"ldap-user" default username postfix
            //TODO: compare email domain for ldap-user or ldap2-user and use ldap according to the domain
            $emailMapperPostfix1 = $userSecUtil->getSiteSettingParameter("ldapMapperEmail");
            if( $emailMapperPostfix1 && $secondEmailPart == $emailMapperPostfix1 ) {
                $username = $publicUserId . "_@_" . "ldap-user";
            } else {
                $emailMapperPostfix2 = $userSecUtil->getSiteSettingParameter("ldapMapperEmail2");
                if( $emailMapperPostfix2 && $secondEmailPart == $emailMapperPostfix2 ) {
                    $username = $publicUserId . "_@_" . "ldap2-user";
                }
            }

        }

        $user = $userSecUtil->constractNewUser($username); //publicUserId_@_ldap-user

        //add site specific creation string
        //$createdBy = "Manually by Translational Research User";
        //$createdBy = "manual";
        $createdBy = "manual-".$sitename;
        $user->setCreatedby($createdBy);

        $user->setOtherUserParam($otherUserParam);

        $user->setLocked(true);

        //add roles
        $user->addRole('ROLE_USERDIRECTORY_OBSERVER');
        //add site specific role
        //$user->addRole('ROLE_TRANSRES_USER');

        //create user info
        if( count($user->getInfos()) == 0 ) {
            $userInfo = new UserInfo();
            $user->addInfo($userInfo);
        }

        //set user info
        $user->setEmail($email);
        $user->setEmailCanonical($email);
        $user->setFirstName($firstname);
        $user->setLastName($lastname);
        $user->setDisplayName($displayname);
        $user->setPreferredPhone($phone);

        //set administrativeTitles
        if( count($user->getAdministrativeTitles()) == 0 ) {
            $user->addAdministrativeTitle(new AdministrativeTitle($user));
        }
        $adminTitles = $user->getAdministrativeTitles();
        if( count($adminTitles) > 0 ) {
            $adminTitle = $adminTitles[0];
            if( $administrativetitle ) {
                $administrativetitleObject = $userSecUtil->getObjectByNameTransformer($creator, $administrativetitle, "UserdirectoryBundle", "AdminTitleList");
                //echo "administrativetitleObject=".$administrativetitleObject->getId()."<br>";
                $adminTitle->setName($administrativetitleObject); //AdministrativeTitle
            }

            if( $institution ) {
                $institutionObject = $userSecUtil->getObjectByNameTransformer($creator, $institution, "UserdirectoryBundle", "Institution");
                //echo "institutionObject=".$institutionObject->getId()."<br>";
                $adminTitle->setInstitution($institutionObject); //Institution
            }
        }
        //echo "admins=".count($user->getAdministrativeTitles())."<br>";
        //exit();

        $this->processOtherUserParam($user,$otherUserParam);

        //$resArr['userId'] = $publicUserId;
        //$resArr['userName'] = "New User $publicUserId";

        //testing
//        $json = json_encode($resArr);
//        $response = new Response($json);
//        $response->headers->set('Content-Type', 'application/json');
//        return $response;
//        exit();

        if( !$testing ) {
            $em->persist($user);
            $em->flush();

            //record create user to Event Log
            $userUrl = $this->container->get('router')->generate(
                $sitename.'_showuser',
                array(
                    'id'=>$user->getId()
                ),
                UrlGeneratorInterface::ABSOLUTE_URL
            );
            $userUrl = '<a href="'.$userUrl.'">'.$userUrl.'</a>';
            
            $sitenameStr = $userServiceUtil->getSiteNameByAbbreviation($sitename);
            $event = "User account for " . $user . " has been created by " . $creator . " on the ".$sitenameStr." site.";
            $event = $event . "<br><br>" . "To review the details for this user account, please visit the link below:";
            $event = $event . "<br>" . $userUrl;

            $userSecUtil = $this->get('user_security_utility');
            $userSecUtil->createUserEditEvent(
                $sitename,
                $event,
                $creator,
                $user,
                $request,
                'New user record added'
            );

            //Email to Admin
            //$event = str_replace("<br>","\r\n",$event);
            $emailUtil = $this->get('user_mailer_utility');
            $adminEmails = $userSecUtil->getUserEmailsByRole($sitename,"Administrator");
            $ccEmails = $userSecUtil->getUserEmailsByRole($sitename,"Platform Administrator");
            $adminEmails = array_merge($adminEmails,$ccEmails);
            $adminEmails = array_unique($adminEmails);
            //                    $emails, $subject, $message, $ccs=null, $fromEmail=null
            $emailUtil->sendEmail($adminEmails, 'New user record added', $event);
        } else {
            //$resArr["flag"] = "NOTOK";
            //$resArr["error"] = "Testing Mode!";
        }

        $resArr["flag"] = "OK";
        $resArr["error"] = null;
        $resArr['userId'] = $user->getId();
        $resArr['userName'] = $user."";

        if( $testing ) {
            $resArr['userId'] = 111; //testing user id
            $resArr['userName'] = "New User $publicUserId"; //dummy username
        }

        $json = json_encode($resArr);
        $response = new Response($json);
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }
    public function processOtherUserParam($user,$otherUserParam) {
        return false;
    }


    /**
     * @Route("/user/new", name="employees_create_user")
     * @Method("POST")
     * @Template("OlegUserdirectoryBundle:Profile:edit_user.html.twig")
     */
    public function createUserAction( Request $request )
    {
        return $this->createUser($request);
    }
    public function createUser($request) {

        if( false === $this->get('security.authorization_checker')->isGranted('ROLE_USERDIRECTORY_EDITOR') ) {
            return $this->redirect( $this->generateUrl('employees-nopermission') );
        }

        $em = $this->getDoctrine()->getManager();
        $author = $this->get('security.token_storage')->getToken()->getUser();

        //$user = new User();
        $userManager = $this->container->get('fos_user.user_manager');
        $user = $userManager->createUser();

        $user->setCreatedby('manual');
        $user->setAuthor($author);

        //Roles
        $rolesArr = $this->getUserRoles(); //create user form

        $params = array(
            'cycle' => 'create',
            'user' => $user,
            'cloneuser' => null,
            'roles' => $rolesArr,
            'container' => $this->container,
            'em' => $em
        );

        //$form = $this->createForm(new UserType($params), $user, array('disabled' => false));
        $form = $this->createForm(UserType::class, $user, array(
            'disabled' => false,
            'form_custom_value' => $params,
        ));

        $form->handleRequest($request);

        if( $user->getLastName() == "" ) {
            $error = new FormError("Last Name is empty");
            $form->get('infos')->get('lastName')->addError($error);
        }

        if( $user->getFirstName() == "" ) {
            $error = new FormError("First Name is empty");
            $form->get('infos')->get('firstName')->addError($error);
        }

        if( $user->getKeytype() == "" ) {
            $error = new FormError("Primary Public User ID Type is empty");
            $form->get('keytype')->addError($error);
        }

        if( $user->getPrimaryPublicUserId() == "" ) {
            $error = new FormError("Primary Public User ID is empty");
            $form->get('primaryPublicUserId')->addError($error);
        }

//        echo "loc errors:<br>";or NYP 
//        print_r($form->getErrors());
//        echo "<br>loc string errors:<br>";
//        print_r($form->getErrorsAsString());
//        echo "<br>";
        //exit();

        if( $form->isValid() ) {

            //make sure perSiteSetting exists (if user created by simplyfy view users, persitesettings is NULL)
            if( !$user->getPerSiteSettings() ) {
                $perSiteSettings = new PerSiteSettings();
                $user->setPerSiteSettings($perSiteSettings);
            }

            $user->setEnabled(true);
            $user->setCreatedby('manual');

            //set unique username
            $user->setUniqueUsername();

            //password can not be NULL
            if( $user->getPassword() == NULL ) {
                $user->setPassword("");
            }

            //encrypt password
            $this->encryptPassword($user,$user->getPassword(),true); //createUser

            //set parents for institution tree for Administrative and Academical Titles
            $this->setDocumentForCommentType($user);

            //set parents for residencySpecialty tree for Trainings
            $this->setParentsForresidencySpecialtyTree($user);

            //set avatar
            $this->processSetAvatar($user);

            $user = $em->getRepository('OlegUserdirectoryBundle:ResearchLab')->processResearchLab( $user );

            //process grants
            $em->getRepository('OlegUserdirectoryBundle:Grant')->processGrant($user);

            //process employmentstatus attachments
            $this->processEmploymentStatus($user);

            $em->persist($user);
            $em->flush();

            //record create user to Event Log
            $userAdmin = $this->get('security.token_storage')->getToken()->getUser();
            $event = "User account for ".$user." has been created by ".$userAdmin."<br>";
            $userSecUtil = $this->get('user_security_utility');
            $userSecUtil->createUserEditEvent($this->container->getParameter('employees.sitename'),$event,$userAdmin,$user,$request,'New user record added');

            //check fellapp roles to sync with FellowshipSubspecialty
            $fellappUtil = $this->container->get('fellapp_util');
            foreach( $user->getRoles() as $role ) {
                if( $role ) {
                    //echo "check role=".$role."<br>";
                    $roleEntity = $em->getRepository('OlegUserdirectoryBundle:Roles')->findOneByName($role);
                    if( $roleEntity && $roleEntity->hasSite("fellapp") && $roleEntity->getFellowshipSubspecialty() ) {
                        $fellappUtil->synchroniseFellowshipSubspecialtyAndProfileRoles( array($roleEntity->getFellowshipSubspecialty()) );
                    }
                }
            }

            return $this->redirect($this->generateUrl($this->container->getParameter('employees.sitename').'_showuser',array('id' => $user->getId())));
        }

        return array(
            'entity' => $user,
            'form' => $form->createView(),
            'cycle' => 'create_user',
            'user_id' => '',
            'sitename' => $this->container->getParameter('employees.sitename'),
            'userclone' => null,
            'postData' => $request->query->all(),
            'title' => 'Create New User',
        );
    }

    protected function getEngine()
    {
        return $this->container->getParameter('fos_user.template.engine');
    }
    ////////////////////// EOF Create New User //////////////////////


    /**
     * Optimized show user
     * @Route("/user/{id}", name="employees_showuser", requirements={"id" = "\d+"}, options={"expose"=true})
     * @Method("GET")
     * @Template("OlegUserdirectoryBundle:Profile:show_user.html.twig")
     */
    public function showUserOptimizedAction( Request $request, $id )
    {

        return $this->showUserOptimized( $request, $id, $this->container->getParameter('employees.sitename') );

//        if( false === $this->get('security.authorization_checker')->isGranted('ROLE_USER') ) { //!$secUtil->isCurrentUser($id) &&
//            return $this->redirect( $this->generateUrl('employees-nopermission') );
//        }
//
//        $em = $this->getDoctrine()->getManager();
//
//        //$entity = $em->getRepository('OlegUserdirectoryBundle:User')->find($id,\Doctrine\ORM\Query::HYDRATE_ARRAY);
//        $entity = $em->getRepository('OlegUserdirectoryBundle:User')->find($id);
//
//        if( !$entity ) {
//            throw $this->createNotFoundException('Unable to find User entity.');
//        }
//
//        //check if this subject user is visible according to the subject user's preferences
//        $user = $this->get('security.token_storage')->getToken()->getUser();
//        $secUtil = $this->get('user_security_utility');
//        if( !$secUtil->isUserVisible($entity,$user) ) {
//            return $this->redirect( $this->generateUrl('employees-nopermission') );
//        }
//
//        //print_r($entity);
//        //echo "<br><br>";
//        //print_r($entity[0]['infos']);
//
//        //echo "displayName" . $entity[0]['infos'][0]['displayName'] . "<br>";
//
//        return array(
//            'sitename' => $sitename,
//            'entity' => $entity,
//            'cycle' => 'show_user',
//            'user_id' => $id,
//            'sitename' => $this->container->getParameter('employees.sitename'),
//            'title' => 'Employee Profile ' . $entity->getUsernameOptimal()
//            //'title' => 'Employee Profile ' . $entity['infos'][0]['displayName']
//            //'title' => 'Employee Profile ' . $entity['displayName23']
//        );
    }
    public function showUserOptimized( Request $request, $id, $sitename )
    {

//        if( false === $this->get('security.authorization_checker')->isGranted('ROLE_USER') ) {
//            return $this->redirect( $this->generateUrl('employees-nopermission') );
//        }
        if( false === $this->get('security.authorization_checker')->isGranted('ROLE_USER') ) {
            return $this->redirect( $this->generateUrl('employees-nopermission') );
        }

        $em = $this->getDoctrine()->getManager();

        //$entity = $em->getRepository('OlegUserdirectoryBundle:User')->find($id,\Doctrine\ORM\Query::HYDRATE_ARRAY);
        $entity = $em->getRepository('OlegUserdirectoryBundle:User')->find($id);

        if( !$entity ) {
            throw $this->createNotFoundException('Unable to find User entity.');
        }

        //check if this subject user is visible according to the subject user's preferences
        $user = $this->get('security.token_storage')->getToken()->getUser();
        $secUtil = $this->get('user_security_utility');
        if( !$secUtil->isUserVisible($entity,$user) ) {
            //exit('1 show User Optimized no permission');
            return $this->redirect( $this->generateUrl('employees-nopermission') );
        }

        //print_r($entity);
        //echo "<br><br>";
        //print_r($entity[0]['infos']);

        //echo "displayName" . $entity[0]['infos'][0]['displayName'] . "<br>";

        return array(
            'sitename' => $sitename,
            'entity' => $entity,
            'cycle' => 'show_user',
            'user_id' => $id,
            'title' => 'Employee Profile ' . $entity->getUsernameOptimal()
            //'sitename' => $this->container->getParameter('employees.sitename'),
            //'title' => 'Employee Profile ' . $entity['infos'][0]['displayName']
            //'title' => 'Employee Profile ' . $entity['displayName23']
        );
    }
    
    /**
     * This is testing custom hydration: not effective for a single entity
     * 
     * @Route("/user/optimized/customh/{id}", name="employees_showuser_optimized_customh", requirements={"id" = "\d+"}, options={"expose"=true})
     * @Method("GET")
     * @Template("OlegUserdirectoryBundle:Profile:show_user.html.twig")
     */
    public function showUserOptimizedCustomhAction(Request $request, $id)
    {
        //$secUtil = $this->get('user_security_utility');
        if( false === $this->get('security.authorization_checker')->isGranted('ROLE_USER') ) { //!$secUtil->isCurrentUser($id) &&
            return $this->redirect( $this->generateUrl('employees-nopermission') );
        }
        
        $em = $this->getDoctrine()->getManager();

        //testing
//        return array(
//            'title' => 'empty',
//            'sitename' => $this->container->getParameter('employees.sitename'),
//            'user_id' => 1           
//        );
        
        //$entity = $em->getRepository('OlegUserdirectoryBundle:User')->find($id,\Doctrine\ORM\Query::HYDRATE_ARRAY);
               
        
        $repository = $this->getDoctrine()->getRepository('OlegUserdirectoryBundle:User');
        $dql =  $repository->createQueryBuilder("user");
        $dql->select('user','infos','avatar');
        $dql->leftJoin("user.infos", "infos");
        $dql->leftJoin("user.avatar", "avatar");
        $dql->leftJoin("user.locations", "locations");
        $dql->where('user.id = '.$id);
        $query = $em->createQuery($dql);
        //$entity = $query->getArrayResult();
        //$entity = $query->getSingleResult(\Doctrine\ORM\Query::HYDRATE_ARRAY);
        //$entity = $query->getSingleResult();
        
        $entity = $query->getSingleResult('SimpleHydrator');
        
        //$entity = $em->getRepository('OlegUserdirectoryBundle:User')->find($id);
        
        if( !$entity ) {
            throw $this->createNotFoundException('Unable to find User entity.');
        }

        //print_r($entity);
        //echo "<br><br>";
        //print_r($entity[0]['infos']);
        
        //echo "displayName" . $entity[0]['infos'][0]['displayName'] . "<br>";
        
        $getUniquename = $entity['uniquename38'];
        $getAbsoluteUploadFullPath = null;
        
        $uploadDirectory = $entity['uploadDirectory39'];
        if( $getUniquename && $uploadDirectory ) {
            $scheme = $request->getScheme();
            $getAbsoluteUploadFullPath = $scheme."://" . $_SERVER['SERVER_NAME'] . "/order/" . $uploadDirectory.'/'.$getUniquename;
        }

        $getUsernameOptimal = $entity['displayName23'];
        
        $getHeadInfo = array();
        
        return array(
            'entity' => $entity,           
            'cycle' => 'show_user',
            'user_id' => $id,
            'sitename' => $this->container->getParameter('employees.sitename'),          
            //'title' => 'Employee Profile ' . $entity->getUsernameOptimal()
            //'title' => 'Employee Profile ' . $entity['infos'][0]['displayName'] 
            'title' => 'Employee Profile ' . $entity['displayName23'],           
            'customh' => true,
            'getOriginalname' => $getUniquename,
            'getAbsoluteUploadFullPath' => $getAbsoluteUploadFullPath,
            'getUsernameOptimal' => $getUsernameOptimal,
            'getHeadInfo' => $getHeadInfo
        );
    }
    

    /**
     * Second part of the user view profile
     * 
     * @Route("/user/only/{id}", name="employees_showuser_only")
     * @Method("GET")
     * @Template("OlegUserdirectoryBundle:Profile:edit_user_only.html.twig")
     */
    public function showOnlyUserAction(Request $request, $id)
    {
        //$secUtil = $this->get('user_security_utility');
        if( false === $this->get('security.authorization_checker')->isGranted('ROLE_USER') ) { //!$secUtil->isCurrentUser($id) &&
            return $this->redirect( $this->generateUrl('employees-nopermission') );
        }

        //echo "id=".$id."<br>";
        $showUser = $this->showUser($request,$id,$this->container->getParameter('employees.sitename'),false);

        if( $showUser === false ) {
            return $this->redirect( $this->generateUrl('employees-nopermission') );
        }

        return $showUser;
    }

    /**
     * Second part of the user view profile
     *
     * @Route("/user/only-ajax/", name="employees_showuser_only_ajax", options={"expose"=true})
     * @Method({"GET", "POST"})
     */
    public function showOnlyAjaxUserAction(Request $request)
    {
        if( false === $this->get('security.authorization_checker')->isGranted('ROLE_USER') ) {
            return $this->redirect( $this->generateUrl('employees-nopermission') );
        }

        $userid = $request->query->get('userid');
        //echo "userid=".$userid."<br>";

        $showUserArr = $this->showUser($request,$userid,$this->container->getParameter('employees.sitename'),false);

        $template = $this->render('OlegUserdirectoryBundle:Profile:edit_user_only.html.twig',$showUserArr)->getContent();

        $json = json_encode($template);
        $response = new Response($json);
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    /**
     * route "employees_showuser_object" is the old user profile view (slow)
     * 
     * @Route("/user/show/{id}", name="employees_showuser_notstrict")
     * @Route("/user/object/{id}", name="employees_showuser_object", requirements={"id" = "\d+"}, options={"expose"=true})
     * @Method("GET")
     * @Template("OlegUserdirectoryBundle:Profile:edit_user.html.twig")
     */
    public function showUserAction(Request $request, $id)
    {
        //$secUtil = $this->get('user_security_utility');
        if( false === $this->get('security.authorization_checker')->isGranted('ROLE_USER') ) { //!$secUtil->isCurrentUser($id) &&
            return $this->redirect( $this->generateUrl('employees-nopermission') );
        }

        $showUser = $this->showUser($request,$id,$this->container->getParameter('employees.sitename'));

        if( $showUser === false ) {
            return $this->redirect( $this->generateUrl('employees-nopermission') );
        }

        return $showUser;
    }
    public function showUser($request, $id, $sitename=null, $fulluser=true) {

        ini_set('max_execution_time', 300); //300 seconds = 5 minutes
        ini_set('memory_limit', '3072M');

        //$request = $this->container->get('request');
        $em = $this->getDoctrine()->getManager();

        //echo "id=".$id."<br>";

        if( $id == 0 || $id == '' || $id == '' ) {
            $entity = new User();
        } else {
            $entity = $em->getRepository('OlegUserdirectoryBundle:User')->find($id);

            //check if this subject user is visible according to the subject user's preferences
            $user = $this->get('security.token_storage')->getToken()->getUser();
            $secUtil = $this->get('user_security_utility');
            if( !$secUtil->isUserVisible($entity,$user) ) {
                return false;
            }
        }

        if( !$entity ) {
            throw $this->createNotFoundException('Unable to find User entity.');
        }

        $this->addEmptyCollections($entity);

        $this->addHookFields($entity);

        //Roles
        $rolesArr = $this->getUserRoles(); //show user form

        $params = array(
            'cycle' => 'show',
            'user' => $entity,
            'cloneuser' => null,
            'roles' => $rolesArr,
            'container' => $this->container,
            'em' => $em
        );

        //$form = $this->createForm(new UserType($params), $entity, array('disabled' => true));
        $form = $this->createForm(UserType::class, $entity, array(
            'disabled' => true,
            'form_custom_value' => $params,
        ));

//        if (!is_object($user) || !$user instanceof UserInterface) {
//            throw new AccessDeniedException('This user does not have access to this section.');
//        }

        //get roles objects for this user
        $roleobjects = array();
        foreach( $entity->getRoles() as $role ) {
            $roleEntity = $em->getRepository('OlegUserdirectoryBundle:Roles')->findOneByName($role);
            if( $roleEntity ) {
                $roleobjects[] = $roleEntity;
            }
        }

//        exit('111');
//        echo "<pre>";
//        var_dump($request->query);
//        echo "</pre>";

        //return $this->container->get('templating')->renderResponse('FOSUserBundle:Profile:show.html.'.$this->container->getParameter('fos_user.template.engine'), array('user' => $user));
        return array(
            'entity' => $entity,
            'form' => $form->createView(),
            'cycle' => 'show_user',
            'user_id' => $id,
            'sitename' => $sitename,
            'roleobjects' => $roleobjects,
            'postData' => $request->query->all(),
            'title' => 'Employee Profile ' . $entity->getUsernameOptimal(),
            'fulluser' => $fulluser
        );
    }

    /**
     * @Route("/edit-user-profile/{id}", name="employees_user_edit", requirements={"id" = "\d+"})
     * @Method("GET")
     * @Template("OlegUserdirectoryBundle:Profile:edit_user.html.twig")
     */
    public function editUserAction(Request $request, $id)
    {
        $secUtil = $this->get('user_security_utility');
        if( !$secUtil->isCurrentUser($id) && false === $this->get('security.authorization_checker')->isGranted('ROLE_USERDIRECTORY_EDITOR') ) {
            return $this->redirect( $this->generateUrl('employees-nopermission') );
        }

        $editUser = $this->editUser($request,$id, $this->container->getParameter('employees.sitename'));

        if( $editUser === false ) {
            return $this->redirect( $this->generateUrl('employees-nopermission') );
        }

        return $editUser;
    }

    public function editUser( $request, $id, $sitename=null ) {

        ini_set('max_execution_time', 300); //300 seconds = 5 minutes
        ini_set('memory_limit', '3072M');

        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('OlegUserdirectoryBundle:User')->find($id);

        if( !$entity ) {
            throw $this->createNotFoundException('Unable to find User entity.');
        }

        //check if this subject user is visible according to the subject user's preferences
        $user = $this->get('security.token_storage')->getToken()->getUser();
        $secUtil = $this->get('user_security_utility');
        if( !$secUtil->isUserVisible($entity,$user) ) {
            return false;
        }

        $this->addEmptyCollections($entity);

        $this->addHookFields($entity);

        //Roles
        $rolesArr = $this->getUserRoles(); //edit user form

        $params = array(
            'cycle' => 'edit',
            'user' => $entity,
            'cloneuser' => null,
            'roles' => $rolesArr,
            'container' => $this->container,
            'em' => $em
        );

//        $form = $this->createForm(new UserType($params), $entity, array(
//            'action' => $this->generateUrl($sitename.'_user_update', array('id' => $entity->getId())),
//            'method' => 'PUT',
//        ));
        $form = $this->createForm(UserType::class, $entity, array(
            'action' => $this->generateUrl($sitename.'_user_update', array('id' => $entity->getId())),
            'method' => 'PUT',
            'form_custom_value' => $params,
        ));
//        $form->add('submit', 'submit', array('label' => 'Update','attr' => array('class' => 'btn btn-warning')));

        $pageTitle = 'Edit Employee Profile for ' . $entity->getUsernameOptimal();
        $termStr = $entity->getEmploymentTerminatedStr();
        if( $termStr ) {
            $pageTitle = $pageTitle . " (" . $termStr . ")";
        }

        return array(
            'entity' => $entity,
            'form' => $form->createView(),
            'cycle' => 'edit_user',
            'user_id' => $id,
            'sitename' => $sitename,
            'postData' => $request->query->all(),
            'title' => $pageTitle
        );
    }

    //create empty collections
    public function addEmptyCollections($entity,$user=null) {

        $userSecUtil = $this->container->get('user_security_utility');
        $em = $this->getDoctrine()->getManager();

        if( !$user ) {
            $user = $this->get('security.token_storage')->getToken()->getUser();
        }

        if( count($entity->getAdministrativeTitles()) == 0 ) {
            $entity->addAdministrativeTitle(new AdministrativeTitle($user));
        }

        if( count($entity->getAppointmentTitles()) == 0 ) {
            $entity->addAppointmentTitle(new AppointmentTitle($user));
            //echo "app added, type=".$appointmentTitle->getType()."<br>";
        }

        if( count($entity->getMedicalTitles()) == 0 ) {
            $entity->addMedicalTitle(new MedicalTitle($user));
        }

        //state license
        $stateLicenses = $entity->getCredentials()->getStateLicense();
        if( count($stateLicenses) == 0 ) {
            $entity->getCredentials()->addStateLicense( new StateLicense() );
        }
        //make sure state license has attachmentContainer
        foreach( $stateLicenses as $stateLicense ) {
            $stateLicense->createAttachmentDocument();
        }

        //board certification
        $boardCertifications = $entity->getCredentials()->getBoardCertification();
        if( count($boardCertifications) == 0 ) {
            $entity->getCredentials()->addBoardCertification( new BoardCertification() );
        }
        //make sure board certification has attachmentContainer
        foreach( $boardCertifications as $boardCertification ) {
            $boardCertification->createAttachmentDocument();
        }

        if( count($entity->getEmploymentStatus()) == 0 ) {
            $entity->addEmploymentStatus(new EmploymentStatus($user));
        }

        $pathology = $userSecUtil->getAutoAssignInstitution();
        if( !$pathology ) {
            $wcmc = $em->getRepository('OlegUserdirectoryBundle:Institution')->findOneByAbbreviation("WCMC");
            if( !$wcmc ) {
                //exit('No Institution: "WCMC"');
                throw $this->createNotFoundException('No Institution: "WCMC"');
            }
            $mapper = array(
                'prefix' => 'Oleg',
                'bundleName' => 'UserdirectoryBundle',
                'className' => 'Institution'
            );
            $pathology = $em->getRepository('OlegUserdirectoryBundle:Institution')->findByChildnameAndParent(
                "Pathology and Laboratory Medicine",
                $wcmc,
                $mapper
            );
            if( !$pathology ) {
                //exit('No Institution: "Pathology and Laboratory Medicine"');
                throw $this->createNotFoundException('No Institution: "Pathology and Laboratory Medicine"');
            }
        }

        //check if Institution is assign
        foreach( $entity->getEmploymentStatus() as $employmentStatus ) {
            $employmentStatus->createAttachmentDocument();
            //echo "employ inst=".$employmentStatus->getInstitution()."<br>";
            if( !$employmentStatus->getInstitution() ) {
                $employmentStatus->setInstitution($pathology);
            }
        }

        //create new comments
        if( count($entity->getPublicComments()) == 0 ) {
            $entity->addPublicComment( new PublicComment($user) );
        }
        if( $this->get('security.authorization_checker')->isGranted('ROLE_PLATFORM_DEPUTY_ADMIN') || $this->get('security.authorization_checker')->isGranted('ROLE_USERDIRECTORY_EDITOR') ||
            $entity->getId() && $entity->getId() == $user->getId()
        ) {
            if( count($entity->getPrivateComments()) == 0 ) {
                $entity->addPrivateComment( new PrivateComment($user) );
            }
        }
        if( $this->get('security.authorization_checker')->isGranted('ROLE_PLATFORM_DEPUTY_ADMIN') || $this->get('security.authorization_checker')->isGranted('ROLE_USERDIRECTORY_EDITOR') ) {
            if( count($entity->getAdminComments()) == 0 ) {
                $entity->addAdminComment( new AdminComment($user) );
            }
        }
        if( $this->get('security.authorization_checker')->isGranted('ROLE_PLATFORM_DEPUTY_ADMIN') || $this->get('security.authorization_checker')->isGranted('ROLE_USERDIRECTORY_EDITOR') ) {
            if( count($entity->getConfidentialComments()) == 0 ) {
                $entity->addConfidentialComment( new ConfidentialComment($user) );
            }
        }

        if( count($entity->getResearchLabs()) == 0 ) {
            $entity->addResearchLab(new ResearchLab($user));
        }

        if( count($entity->getGrants()) == 0 ) {
            $entity->addGrant(new Grant($user));
        }
        //check if has attachemntDocument and at least one DocumentContainers
        foreach( $entity->getGrants() as $grant ) {
            $grant->createAttachmentDocument();
        }

        if( count($entity->getTrainings()) == 0 ) {
            $entity->addTraining(new Training($user));
        }

        if( count($entity->getPublications()) == 0 ) {
            $entity->addPublication(new Publication($user));
        }

        if( count($entity->getBooks()) == 0 ) {
            $entity->addBook(new Book($user));
        }

        if( count($entity->getLectures()) == 0 ) {
            $entity->addLecture(new Lecture($user));
        }

        //Identifier EIN
//        if( count($entity->getCredentials()->getIdentifiers()) == 0 ) {
//            $entity->getCredentials()->addIdentifier( new Identifier() );
//        }

        //make sure coqAttachmentContainer, cliaAttachmentContainer exists
        $entity->getCredentials()->createAttachmentDocument();

    }



    public function addHookFields($user) {
        //empty
    }

    /**
     * @Route("/edit-user-profile/{id}", name="employees_user_update")
     * @Method("PUT")
     * @Template("OlegUserdirectoryBundle:Profile:edit_user.html.twig")
     */
    public function updateUserAction(Request $request, $id)
    {
        $secUtil = $this->get('user_security_utility');
        if( !$secUtil->isCurrentUser($id) && false === $this->get('security.authorization_checker')->isGranted('ROLE_USERDIRECTORY_EDITOR') ) {
            return $this->redirect( $this->generateUrl('employees-nopermission') );
        }

        return $this->updateUser( $request, $id, $this->container->getParameter('employees.sitename') );
    }
    public function updateUser(Request $request, $id, $sitename)
    {

        ini_set('max_execution_time', 300); //300 seconds = 5 minutes
        ini_set('memory_limit', '3072M');

        $em = $this->getDoctrine()->getManager();
        $logger = $this->container->get('logger');
        $loggedUser = $this->get('security.token_storage')->getToken()->getUser();

        $entity = $em->getRepository('OlegUserdirectoryBundle:User')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find User entity.');
        }

        $logger->notice($loggedUser.": trying to update user (ID=".$entity->getId().") ".$entity);

        //$this->addHookFields($entity);

//        $entity->setPreferredPhone('111222333');
//        $uow = $em->getUnitOfWork();
//        $uow->computeChangeSets(); // do not compute changes if inside a listener
//        $changeset = $uow->getEntityChangeSet($entity);
//        print_r($changeset);
        //exit('edit user');

        //$oldEntity = clone $entity;
        //$oldUserArr = get_object_vars($oldEntity);

        //echo "getPassword=".$entity->getPassword()."<br>";
        //echo "getPlainPassword=".$entity->getPlainPassword()."<br>";
        $originalPassword = $entity->getPassword();

        //Create original roles
        $originalRoles = array();
        foreach( $entity->getRoles() as $role) {
            $originalRoles[] = $role;
        }

        // Create an ArrayCollection of the current Tag objects in the database
        $originalAdminTitles = new ArrayCollection();
        foreach( $entity->getAdministrativeTitles() as $title) {
            $originalAdminTitles->add($title);
        }

        $originalAppTitles = new ArrayCollection();
        foreach( $entity->getAppointmentTitles() as $title) {
            $originalAppTitles->add($title);
        }

        $originalMedicalTitles = new ArrayCollection();
        foreach( $entity->getMedicalTitles() as $title) {
            $originalMedicalTitles->add($title);
        }

        $originalLocations = new ArrayCollection();
        foreach( $entity->getLocations() as $loc) {
            $originalLocations->add($loc);
        }

        $originalTrainings = new ArrayCollection();
        foreach( $entity->getTrainings() as $training) {
            $originalTrainings->add($training);
        }

        $originalPublications = new ArrayCollection();
        foreach( $entity->getPublications() as $publication) {
            $originalPublications->add($publication);
        }

        $originalBooks = new ArrayCollection();
        foreach( $entity->getBooks() as $book) {
            $originalBooks->add($book);
        }

        $originalLectures = new ArrayCollection();
        foreach( $entity->getLectures() as $lecture) {
            $originalLectures->add($lecture);
        }

        //Credentials collections
        $originalIdentifiers = new ArrayCollection();
        foreach( $entity->getCredentials()->getIdentifiers() as $subitem) {
            $originalIdentifiers->add($subitem);
        }

        $originalStateLicense = new ArrayCollection();
        foreach( $entity->getCredentials()->getStateLicense() as $subitem) {
            $originalStateLicense->add($subitem);
        }

        $originalBoardCertification = new ArrayCollection();
        foreach( $entity->getCredentials()->getBoardCertification() as $subitem) {
            $originalBoardCertification->add($subitem);
        }

        $originalCodeNYPH = new ArrayCollection();
        foreach( $entity->getCredentials()->getCodeNYPH() as $subitem) {
            $originalCodeNYPH->add($subitem);
        }
        //eof Credentials collections

        $originalEmplStatus = new ArrayCollection();
        foreach( $entity->getEmploymentStatus() as $item) {
            $originalEmplStatus->add($item);
        }

        $originalPublicComments = new ArrayCollection();
        foreach( $entity->getPublicComments() as $subitem) {
            $originalPublicComments->add($subitem);
        }
        $originalPrivateComments = new ArrayCollection();
        foreach( $entity->getPrivateComments() as $subitem) {
            $originalPrivateComments->add($subitem);
        }
        $originalAdminComments = new ArrayCollection();
        foreach( $entity->getAdminComments() as $subitem) {
            $originalAdminComments->add($subitem);
        }
        $originalConfidentialComments = new ArrayCollection();
        foreach( $entity->getConfidentialComments() as $subitem) {
            $originalConfidentialComments->add($subitem);
        }

        $originalResLabs = new ArrayCollection();
        foreach( $entity->getResearchLabs() as $lab) {
            $originalResLabs->add($lab);
        }

        $originalGrants = new ArrayCollection();
        foreach( $entity->getGrants() as $grant) {
            $originalGrants->add($grant);
        }

        if( $entity->getAvatar() ) {
            $oldAvatarId = $entity->getAvatar()->getId();
            //echo "0 oldAvatarId=".$oldAvatarId."<br>";
        } else {
            $oldAvatarId = NULL;
        }

        $originalShowToInstitutions = new ArrayCollection();
        foreach( $entity->getPreferences()->getShowToInstitutions() as $inst) {
            $originalShowToInstitutions->add($inst);
        }

        $originalPrimaryPublicUsername = $entity->getPrimaryPublicUserId();
        //echo "count=".count($originalAdminTitles)."<br>";
        //exit();

        $originalKeyType = $entity->getKeytype();

        $originalInsts = new ArrayCollection();
        $originalScanOrdersServicesScope = new ArrayCollection();
        $originalChiefServices = new ArrayCollection();
        if( $entity->getPerSiteSettings() ) {
            foreach ($entity->getPerSiteSettings()->getPermittedInstitutionalPHIScope() as $item) {
                $originalInsts->add($item);
                $logger->notice("Added to original inst=".$item);
            }
            foreach ($entity->getPerSiteSettings()->getScanOrdersServicesScope() as $item) {
                $originalScanOrdersServicesScope->add($item);
            }
            foreach ($entity->getPerSiteSettings()->getChiefServices() as $item) {
                $originalChiefServices->add($item);
            }
        }

        //Roles
        $rolesArr = $this->getUserRoles(); //update user form

        $params = array(
            'cycle' => 'edit',
            'user' => $entity,
            'cloneuser' => null,
            'roles' => $rolesArr,
            'container' => $this->container,
            'em' => $em
        );

//        $form = $this->createForm(new UserType($params), $entity, array(
//            'action' => $this->generateUrl($sitename.'_user_update', array('id' => $entity->getId())),
//            'method' => 'PUT',
//        ));
        $form = $this->createForm(UserType::class, $entity, array(
            'action' => $this->generateUrl($sitename.'_user_update', array('id' => $entity->getId())),
            'method' => 'PUT',
            'form_custom_value' => $params,
        ));
        //$form->add('submit', 'submit', array('label' => 'Update'));



//        $data = $request->request->all();
//
//        print("REQUEST DATA<br/>");
//        foreach ($data as $k => $d) {
//            print("$k: <pre>"); print_r($d); print("</pre>");
//        }
//
//        $children = $form->all();
//
//        print("<br/>FORM CHILDREN<br/>");
//        foreach ($children as $ch) {
//            print($ch->getName() . "<br/>");
//        }
//
//        $data = array_diff_key($data, $children);
//        //$data contains now extra fields
//
//        print("<br/>DIFF DATA<br/>");
//        foreach ($data as $k => $d) {
//            print("$k: <pre>"); print_r($d); print("</pre>");
//        }


        $form->handleRequest($request);


//        if( $form->isValid() ) {
//            echo "form is valid <br>";
//        } else {
//            echo "form has error <br>";
//        }
//        echo "<br>loc string errors:<br>";
//        print_r($form->getErrorsAsString());
//        echo "<br>";


        if( $form->isValid() ) {

            //echo "form is valid<br>";
            //exit();

            //make sure perSiteSetting exists (if user created by simplyfy view users, persitesettings is NULL)
            if( !$entity->getPerSiteSettings() ) {
                $perSiteSettings = new PerSiteSettings();
                $entity->setPerSiteSettings($perSiteSettings);
            }

            //check if changed roles are "Platform Administrator" or "Deputy Platform Administrator"
            $currRoles = $entity->getRoles();
            $resultRoles = $this->array_diff_assoc_true($currRoles,$originalRoles);
            $msg = "Change Role(s):<br> diffRoles=".join(", ",$resultRoles).
                ";<br> originalRoles=".join(", ",$originalRoles).
                ";<br> currentRoles =".join(", ",$currRoles);
            //testing
            //$this->setSessionForbiddenNote($msg);
            //echo "msg=".$msg."<br>";
            //exit('testing');

            //check 1: if the roles are changed by non admin user
            if( count($resultRoles) > 0 ) {
                if( false === $this->get('security.authorization_checker')->isGranted('ROLE_PLATFORM_DEPUTY_ADMIN') &&
                    false === $this->get('security.authorization_checker')->isGranted('ROLE_USERDIRECTORY_ADMIN') &&
                    false === $this->get('security.authorization_checker')->isGranted('ROLE_SCANORDER_ADMIN') &&
                    false === $this->get('security.authorization_checker')->isGranted('ROLE_DEIDENTIFICATOR_ADMIN')
                    //&& false === $this->get('security.authorization_checker')->isGranted('ROLE_USERDIRECTORY_EDITOR')
                ) {
                    $msg = "You do not have permission to perform this operation: ".$msg;
                    $logger->notice($msg);
                    $this->setSessionForbiddenNote($msg);
                    //throw new ForbiddenOverwriteException("You do not have permission to perform this operation: Change Role ".$role);
                    return $this->redirect( $this->generateUrl($sitename.'_user_edit',array('id'=>$id)) );
                }
            }
            //check 2: if the roles "Platform Administrator" or "Deputy Platform Administrator" are changed by non super admin user
            foreach( $resultRoles as $role ) {
                if( $role == "ROLE_PLATFORM_DEPUTY_ADMIN" || $role == "ROLE_PLATFORM_ADMIN" ) {
                    if( false === $this->get('security.authorization_checker')->isGranted('ROLE_PLATFORM_ADMIN') ) {
                        $this->setSessionForbiddenNote("Change Role ".$role);
                        //throw new ForbiddenOverwriteException("You do not have permission to perform this operation: Change Role ".$role);
                        return $this->redirect( $this->generateUrl($sitename.'_user_edit',array('id'=>$id)) );
                    }
                }
            }
            
            $currentPrimaryPublicUsername = $entity->getPrimaryPublicUserId();
            if( $currentPrimaryPublicUsername != $originalPrimaryPublicUsername ) {
                if( false === $this->get('security.authorization_checker')->isGranted('ROLE_PLATFORM_ADMIN') ) {
                    $this->setSessionForbiddenNote("You don't have permission to change Primary Public User ID");                   
                    return $this->redirect( $this->generateUrl($sitename.'_user_edit',array('id'=>$id)) );
                } else {
                    $uniqueUsername = $entity->createUniqueUsername();
                    $entity->setUsernameForce($uniqueUsername);
                }
            }

            //TODO: update username if keytype is changed
            $currentKeyType = $entity->getKeytype();
            if( $currentKeyType && $currentKeyType != $originalKeyType ) {
                $uniqueUsername = $entity->createUniqueUsername();
                $entity->setUsernameForce($uniqueUsername);
            }

            //check if insts were changed and user is not admin
            if( false === $this->get('security.authorization_checker')->isGranted('ROLE_PLATFORM_DEPUTY_ADMIN') &&
                false === $this->get('security.authorization_checker')->isGranted('ROLE_USERDIRECTORY_ADMIN') &&
                false === $this->get('security.authorization_checker')->isGranted('ROLE_SCANORDER_ADMIN') &&
                false === $this->get('security.authorization_checker')->isGranted('ROLE_DEIDENTIFICATOR_ADMIN')
                //&& false === $this->get('security.authorization_checker')->isGranted('ROLE_USERDIRECTORY_EDITOR')
            ) {
                $currentInsts = $entity->getPerSiteSettings()->getPermittedInstitutionalPHIScope();
                $logger->notice("compare: currentInsts=".count($currentInsts)." != originalInsts=".count($originalInsts));
                if( count($currentInsts) != count($originalInsts) ) {
                    $this->setSessionForbiddenNote("Change Institutions");
                    throw new ForbiddenOverwriteException("You do not have permission to perform this operation: Change institutions: original count=".count($originalInsts)."; new count=".count($currentInsts));
                    //return $this->redirect( $this->generateUrl('logout') );
                }
                foreach( $currentInsts as $inst ) {
                    $logger->notice("Check inst=".$inst);
                    if( !$originalInsts->contains($inst) ) {
                        $this->setSessionForbiddenNote("Change Institutions");
                        throw new ForbiddenOverwriteException("You do not have permission to perform this operation: Change Institutions: removed=".$inst);
                        //return $this->redirect( $this->generateUrl('logout') );
                    }
                }
            } else {
                $logger->notice("Logged in user is admin => don't check for institution change");
            }


            //exit('Testing: before processing');

            //set parents for institution tree for Administrative and Academical Titles
            //$this->setCompositeTreeNode($entity);

            //encrypt password
            $this->encryptPassword($entity,$originalPassword); //updateUser

            //set parents for institution tree for Administrative and Academical Titles
            $this->setDocumentForCommentType($entity);

            //set parents for residencySpecialty tree for Trainings
            $this->setParentsForresidencySpecialtyTree($entity);

            //set avatar
            $this->processSetAvatar($entity);

            //process research labs
            $entity = $em->getRepository('OlegUserdirectoryBundle:ResearchLab')->processResearchLab( $entity );

            //process grants
            $em->getRepository('OlegUserdirectoryBundle:Grant')->processGrant($entity); //update user

            //process employmentstatus attachments
            $this->processEmploymentStatus($entity);

            //process credentials attachments
            $this->processCredentials($entity);

            //process publications
            //$this->processPublications($entity);

            //process books
            //$this->processBooks($entity);

            //process userWrappers
            $this->processUserWrappers($entity,$request);

            //set update info for user
            $this->updateInfo($entity);

            /////////////// Add event log on edit (edit or add collection) ///////////////
            /////////////// Must run before removeCollection() function which flash DB. When DB is flashed getEntityChangeSet() will not work ///////////////
            $changedInfoArr = $this->setEventLogChanges($entity);

            /////////////// Process Removed Collections ///////////////
            $removedCollections = array();

            $removedInfo = $this->removeCollection($originalAdminTitles,$entity->getAdministrativeTitles(),$entity);
            if( $removedInfo ) {
                $removedCollections[] = $removedInfo;
            }

            $removedInfo = $this->removeCollection($originalAppTitles,$entity->getAppointmentTitles(),$entity);
            if( $removedInfo ) {
                $removedCollections[] = $removedInfo;
            }

            $removedInfo = $this->removeCollection($originalMedicalTitles,$entity->getMedicalTitles(),$entity);
            if( $removedInfo ) {
                $removedCollections[] = $removedInfo;
            }

            $removedInfo = $this->removeCollection($originalLocations,$entity->getLocations(),$entity);
            if( $removedInfo ) {
                $removedCollections[] = $removedInfo;
            }
            //exit('location');

            $removedInfo = $this->removeCollection($originalTrainings,$entity->getTrainings(),$entity);
            if( $removedInfo ) {
                $removedCollections[] = $removedInfo;
            }

            $removedInfo = $this->removeCollection($originalPublications,$entity->getPublications(),$entity);
            if( $removedInfo ) {
                $removedCollections[] = $removedInfo;
            }

            $removedInfo = $this->removeCollection($originalBooks,$entity->getBooks(),$entity);
            if( $removedInfo ) {
                $removedCollections[] = $removedInfo;
            }

            $removedInfo = $this->removeCollection($originalLectures,$entity->getLectures(),$entity);
            if( $removedInfo ) {
                $removedCollections[] = $removedInfo;
            }

            //check for removed collection for Credentials: identifiers, stateLicense, boardCertification, codeNYPH
            $removedInfo = $this->removeCollection($originalIdentifiers,$entity->getCredentials()->getIdentifiers(),$entity);
            if( $removedInfo ) {
                $removedCollections[] = $removedInfo;
            }

            $removedInfo = $this->removeCollection($originalStateLicense,$entity->getCredentials()->getStateLicense(),$entity);
            if( $removedInfo ) {
                $removedCollections[] = $removedInfo;
            }

            $removedInfo = $this->removeCollection($originalBoardCertification,$entity->getCredentials()->getBoardCertification(),$entity);
            if( $removedInfo ) {
                $removedCollections[] = $removedInfo;
            }

            $removedInfo = $this->removeCollection($originalCodeNYPH,$entity->getCredentials()->getCodeNYPH(),$entity);
            if( $removedInfo ) {
                $removedCollections[] = $removedInfo;
            }
            //eof removed collection for Credentials

            $removedEmplStatus = $this->removeCollection($originalEmplStatus,$entity->getEmploymentStatus(),$entity);
            if( $removedEmplStatus ) {
                $removedCollections[] = $removedEmplStatus;
            }

            $removedInfo = $this->removeCollection($originalPublicComments,$entity->getPublicComments(),$entity);
            if( $removedInfo ) {
                $removedCollections[] = $removedInfo;
            }
            $removedInfo = $this->removeCollection($originalPrivateComments,$entity->getPrivateComments(),$entity);
            if( $removedInfo ) {
                $removedCollections[] = $removedInfo;
            }
            $removedInfo = $this->removeCollection($originalAdminComments,$entity->getAdminComments(),$entity);
            if( $removedInfo ) {
                $removedCollections[] = $removedInfo;
            }
            $removedInfo = $this->removeCollection($originalConfidentialComments,$entity->getConfidentialComments(),$entity);
            if( $removedInfo ) {
                $removedCollections[] = $removedInfo;
            }

            $removedInfo = $this->removeCollection($originalResLabs,$entity->getResearchLabs(),$entity);
            if( $removedInfo ) {
                $removedCollections[] = $removedInfo;
            }

            $removedInfo = $this->removeCollection($originalGrants,$entity->getGrants(),$entity);
            if( $removedInfo ) {
                $removedCollections[] = $removedInfo;
            }

            $removedInfo = $this->recordToEvenLogDiffCollection($originalShowToInstitutions,$entity->getPreferences()->getShowToInstitutions(),"ShowToInstitutions");
            if( $removedInfo ) {
                $removedCollections[] = $removedInfo;
            }

            //PerSiteSettings
            $removedInfo = $this->recordToEvenLogDiffCollection($originalInsts,$entity->getPerSiteSettings()->getPermittedInstitutionalPHIScope(),"PermittedInstitutionalPHIScope");
            if( $removedInfo ) {
                $removedCollections[] = $removedInfo;
            }
            $removedInfo = $this->recordToEvenLogDiffCollection($originalScanOrdersServicesScope,$entity->getPerSiteSettings()->getScanOrdersServicesScope(),"ScanOrdersServicesScope");
            if( $removedInfo ) {
                $removedCollections[] = $removedInfo;
            }
            $removedInfo = $this->recordToEvenLogDiffCollection($originalChiefServices,$entity->getPerSiteSettings()->getChiefServices(),"ChiefServices");
            if( $removedInfo ) {
                $removedCollections[] = $removedInfo;
            }
            /////////////// EOF Process Removed Collections ///////////////

            //set Edit event log for removed collection and changed fields or added collection
            if( count($changedInfoArr) > 0 || count($removedCollections) > 0 ) {
                $user = $this->get('security.token_storage')->getToken()->getUser();
                $event = "User information of ".$entity." has been changed by ".$user.":"."<br>";
                $event = $event . implode("<br>", $changedInfoArr);
                $event = $event . "<br>" . implode("<br>", $removedCollections);
                $userSecUtil = $this->get('user_security_utility');
                $userSecUtil->createUserEditEvent($sitename,$event,$user,$entity,$request,'User record updated');
            }

            //echo "user=".$entity."<br>";

            //echo "employmentStatus=".$entity->getEmploymentStatus()->first()."<br>";

            //exit('user exit');

            //$em->persist($entity);
            $em->flush($entity);

            //delete old avatar document from DB
            $this->processDeleteOldAvatar($entity,$oldAvatarId);

            //check fellapp roles to sync with FellowshipSubspecialty
            $fellappUtil = $this->container->get('fellapp_util');
            foreach( $resultRoles as $role ) {
                if( $role ) {
                    //echo "check role=".$role."<br>";
                    $roleEntity = $em->getRepository('OlegUserdirectoryBundle:Roles')->findOneByName($role);
                    if( $roleEntity && $roleEntity->hasSite("fellapp") && $roleEntity->getFellowshipSubspecialty() ) {
                        $fellappUtil->synchroniseFellowshipSubspecialtyAndProfileRoles( array($roleEntity->getFellowshipSubspecialty()) );
                    }
                }
            }

            //redirect only if this was called by the same controller class
            //if( $sitename == $this->container->getParameter('employees.sitename') ) {
                return $this->redirect($this->generateUrl($sitename.'_showuser', array('id' => $id)));
            //}
        }

        //echo "form is not valid<br>";
        //exit();

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
            'cycle' => 'edit_user',
            'user_id' => $id,
            'sitename' => $sitename,
            'postData' => $request->query->all()
        );
    }

    public function updateInfo($subjectUser) {
        //$user = $this->get('security.token_storage')->getToken()->getUser();

        $em = $this->getDoctrine()->getManager();
        $userUtil = new UserUtil();

        //Administartive and Appointment Titles and Comments update info set when parent are processed
        //So, set author info for the rest: EmploymentStatus, Location, Credentials, ResearchLab
        foreach( $subjectUser->getEmploymentStatus() as $entity ) {
            $userUtil->setUpdateInfo($entity,$em,$this->get('security.token_storage'));
        }

        foreach( $subjectUser->getLocations() as $entity ) {
            $userUtil->setUpdateInfo($entity,$em,$this->get('security.token_storage'));
            $userUtil->setUpdateInfo($entity->getBuilding(),$em,$this->get('security.token_storage'));
        }

        //credentials
        $userUtil->setUpdateInfo($subjectUser->getCredentials(),$em,$this->get('security.token_storage'));

        foreach( $subjectUser->getResearchLabs() as $entity ) {
            $userUtil->setUpdateInfo($entity,$em,$this->get('security.token_storage'));
        }

        foreach( $subjectUser->getGrants() as $entity ) {
            $userUtil->setUpdateInfo($entity,$em,$this->get('security.token_storage'));
        }

    }


//    public function createUserEditEvent($sitename,$event,$user,$subjectEntity,$request,$action='User record updated') {
//        $userSecUtil = $this->get('user_security_utility');
//        $eventLog = $userSecUtil->constructEventLog($sitename,$user,$request);
//        $eventLog->setEvent($event);
//
//        //set Event Type
//        $em = $this->getDoctrine()->getManager();
//        $eventtype = $em->getRepository('OlegUserdirectoryBundle:EventTypeList')->findOneByName($action);
//        $eventLog->setEventType($eventtype);
//
//        //get classname, entity name and id of subject entity
//        $class = new \ReflectionClass($subjectEntity);
//        $className = $class->getShortName();
//        $classNamespace = $class->getNamespaceName();
//
//        //set classname, entity name and id of subject entity
//        $eventLog->setEntityNamespace($classNamespace);
//        $eventLog->setEntityName($className);
//        $eventLog->setEntityId($subjectEntity->getId());
//
//        $em = $this->getDoctrine()->getManager();
//        $em->persist($eventLog);
//        $em->flush();
//    }


    //Process all holder containing Residency Specialty tree
    public function setParentsForResidencySpecialtyTree($entity) {

        $em = $this->getDoctrine()->getManager();
        $secTokenStorage = $this->get('security.token_storage');
        $userUtil = new UserUtil();

        $educationalType = null;

        foreach( $entity->getTrainings() as $training) {
            $userUtil->processResidencySpecialtyTree($training,$em,$secTokenStorage);

            //set Educational type for training Institution
            $institution = $training->getInstitution();
            if( $institution && $educationalType == null ) {
                $educationalType = $em->getRepository('OlegUserdirectoryBundle:InstitutionType')->findOneByName("Educational");
            }
            if( $institution && $educationalType) {
                $institution->addType($educationalType);
            }
        }
    }


    public function setDocumentForCommentType($entity) {

        //exit('end all comments');
        //return;
        //echo "process comments <br>";

        //echo "public comments count=".count($entity->getPublicComments())."<br>";

        foreach( $entity->getPublicComments() as $comment) {
            $this->processCommentType($comment);
        }
        //exit('pc');

        foreach( $entity->getPrivateComments() as $comment) {
            $this->processCommentType($comment);
        }

        foreach( $entity->getAdminComments() as $comment) {
            $this->processCommentType($comment);
        }

        foreach( $entity->getConfidentialComments() as $comment) {
            $this->processCommentType($comment);
        }

        //exit('end all comments');

    }
    public function processCommentType($comment) {

        $em = $this->getDoctrine()->getManager();

        // process documents
        $em->getRepository('OlegUserdirectoryBundle:Document')->processDocuments( $comment );

        if( $comment == null ) {
            return;
        }

        //set author if not set
        $userUtil = new UserUtil();
        $userUtil->setUpdateInfo($comment,$em,$this->get('security.token_storage'));
    }


    //set documents for EmploymentStatus
    public function processEmploymentStatus($subjectUser) {

        $em = $this->getDoctrine()->getManager();

        foreach( $subjectUser->getEmploymentStatus() as $employmentStatus ) {

            foreach( $employmentStatus->getAttachmentContainer()->getDocumentContainers() as $documentContainer) {

                $documentContainer = $em->getRepository('OlegUserdirectoryBundle:Document')->processDocuments( $documentContainer );

                if( $documentContainer ) {
                    //$userUtil = new UserUtil();
                    //$sc = $this->get('security.context');
                    //$userUtil->setUpdateInfo($documentContainer,$em,$this->get('security.token_storage'));
                }

            }
        }

    }

    //set documents for Credentials's coqAttachmentContainer and StateLicense's attachmentContainer
    public function processCredentials($subjectUser) {

        $user = $this->get('security.token_storage')->getToken()->getUser();
        $em = $this->getDoctrine()->getManager();
        $userSecUtil = $this->get('user_security_utility');

        $credentials = $subjectUser->getCredentials();
        if( !$credentials ) {
            return;
        }

        //Credentials's coqAttachmentContainer
        $coqAttachmentContainer = $credentials->getCoqAttachmentContainer();
        if( !$coqAttachmentContainer ) {
            return;
        }
        $documentCoqType = $userSecUtil->getObjectByNameTransformer($user,"Certificate of Qualification Document",'UserdirectoryBundle','DocumentTypeList');
        foreach( $coqAttachmentContainer->getDocumentContainers() as $documentContainer) {
            $documentContainer = $em->getRepository('OlegUserdirectoryBundle:Document')->processDocuments( $documentContainer,null,$documentCoqType );
        }

        //Credentials's cliaAttachmentContainer
        $cliaAttachmentContainer = $credentials->getCliaAttachmentContainer();
        if( !$cliaAttachmentContainer ) {
            return;
        }
        $documentCliaType = $userSecUtil->getObjectByNameTransformer($user,"CLIA Document",'UserdirectoryBundle','DocumentTypeList');
        foreach( $cliaAttachmentContainer->getDocumentContainers() as $documentContainer) {
            $documentContainer = $em->getRepository('OlegUserdirectoryBundle:Document')->processDocuments( $documentContainer,null,$documentCliaType );
        }

        //StateLicense's attachmentContainer
        $documentCredType = $userSecUtil->getObjectByNameTransformer($user,"Medical License Document",'UserdirectoryBundle','DocumentTypeList');
        foreach( $credentials->getStateLicense() as $stateLicense) {
            $attachmentContainer = $stateLicense->getAttachmentContainer();
            if( $attachmentContainer ) {
                foreach( $attachmentContainer->getDocumentContainers() as $documentContainer ) {
                    $documentContainer = $em->getRepository('OlegUserdirectoryBundle:Document')->processDocuments($documentContainer,null,$documentCredType);
                }
            }
        }

        //BoardCertification's attachmentContainer
        $documentBoardcertType = $userSecUtil->getObjectByNameTransformer($user,"Board Certification Document",'UserdirectoryBundle','DocumentTypeList');
        foreach( $credentials->getBoardCertification() as $boardCertification ) {
            $attachmentContainer = $boardCertification->getAttachmentContainer();
            if( $attachmentContainer ) {
                foreach( $attachmentContainer->getDocumentContainers() as $documentContainer ) {
                    $documentContainer = $em->getRepository('OlegUserdirectoryBundle:Document')->processDocuments($documentContainer,null,$documentBoardcertType);
                }
            }
        }

    }

//    //convert mm/yyyy to DateTime format
//    public function processPublications($subjectUser) {
//
//        $em = $this->getDoctrine()->getManager();
//
//        foreach( $subjectUser->getPublications() as $item ) {
//
//            $mmyyyy = $item->getPublicationDate();
//            echo "mmyyyy=".$mmyyyy."<br>";
//            exit('1');
//        }
//
//    }
//    public function processBooks($subjectUser) {
//
//    }

    public function processUserWrappers( $user, $request ) {
        //get userWrapper IDs

        $em = $this->getDoctrine()->getManager();
        $data = $request->request->all();
        $userwrappers = $data['userwrappers'];

//        print "<pre>";
//        print_r($userwrappers);
//        print "</pre>";

        if( $userwrappers && count($userwrappers) > 0 ) {

            //1) get all wrappers with this user
            $userWrappers = $em->getRepository('OlegUserdirectoryBundle:UserWrapper')->findByUser($user->getId());

            //2) remove this user from all wrappers except in $userwrappers array.
            foreach( $userWrappers as $userWrapper ) {
                //echo $userWrapper->getId().": wrapper=".$userWrapper."<br>";
                if( !in_array($userWrapper->getId(),$userwrappers) ) {
                    //echo $userWrapper->getId().": remove user from this wrapper=".$userWrapper."<br>";
                    //remove user from this wrapper
                    $userWrapper->setUser(null);
                    $em->persist($userWrapper);
                    $em->flush($userWrapper);
                } else {
                    //echo $userWrapper->getId().": keep this wrapper=".$userWrapper."<br>";
                }
            }

            //3) add user to the wrappers in array $userwrappers
            foreach( $userwrappers as $userWrapperId ) {
                $userWrapper = $em->getRepository('OlegUserdirectoryBundle:UserWrapper')->find($userWrapperId);
                if( $userWrapper ) {
                    if( !$userWrapper->getUser() ) {
                        $userWrapper->setUser($user);
                        $em->persist($userWrapper);
                        $em->flush($userWrapper);
                    } else {
                        //wrapper already has a linked user
                    }
                }
            }

        }
        //exit('exit wrapper');
    }

    public function encryptPassword( $user, $originalPassword, $newUser=false ) {
        //return; //testing

        //echo "originalPassword=".$originalPassword."<br>";
        //echo "getPassword=".$user->getPassword()."<br>";
        //echo "getPlainPassword=".$user->getPlainPassword()."<br>";

//        $compare = true;
//        if( !$originalPassword ) {
//            $originalPassword = $user->getPassword(); //new user
//            $compare = false;
//            //return;
//        }

        if( !$originalPassword ) {
            //exit('no original password');
            return;
        }

        $encoder = $this->container->get('security.password_encoder');
        $encoded = $encoder->encodePassword($user, $user->getPassword());

        //password is the same as original one
        if( !$newUser && hash_equals($originalPassword, $user->getPassword()) ) {
            if( $this->isEncodedPassword($user->getPassword()) ) {
                //exit('password is already encoded and it is the same');
                return;
            }
        }

        //$encoder = $this->container->get('security.password_encoder');
        //$encoded = $encoder->encodePassword($user, $user->getPassword());

        //echo "compare: $originalPassword == $encoded <br>";
        $equals = hash_equals($originalPassword, $encoded);

        if( !$equals && $user->getPassword() != "" ) {
            // 3) Encode the password (you could also do this via Doctrine listener)
            //echo "new password<br>";
            //$password = $this->get('security.password_encoder')->encodePassword($user, $user->getPlainPassword());
            $user->setPassword($encoded);
        } else {
            //echo "old password<br>";
        }
        //exit();
    }
    function isEncodedPassword($password) {
        //return preg_match('/^[a-f0-9]{32}$/', $password);
        //check the length of the password
        if( strlen($password) >= 32 ) {
            return true;
        }
        return false;
    }

    //explicitly set a new avatar
    public function processSetAvatar($subjectUser) {

        if( $subjectUser->getAvatar() ) {
            $avatarid = $subjectUser->getAvatar()->getId();
            //echo "new avatarid=".$avatarid."<br>";
            //echo "new avatar size=".$subjectUser->getAvatar()->getSize()."<br>";

            if( $avatarid && $avatarid != "" ) {
                //echo "avatarid=".$avatarid."<br>";
                $em = $this->getDoctrine()->getManager();
                $avatar = $em->getRepository('OlegUserdirectoryBundle:Document')->find($avatarid);
                $subjectUser->setAvatar($avatar);
            } else {
                //echo "null avatarid=".$avatarid."<br>";
                $subjectUser->setAvatar(NULL);
            }

        }

    }

    //delete old avatar document from DB and avatar images from filesystem
    public function processDeleteOldAvatar($subjectUser,$oldAvatarId) {

        if( $oldAvatarId == NULL ) {
            return;
        }

        //don't try to delete if old and new avatar id are the same (avatar has not changed)
        if( $subjectUser->getAvatar()->getId() == $oldAvatarId ) {
            return;
        }

        $em = $this->getDoctrine()->getManager();
        $em->clear();

        //echo "1 oldAvatarId=".$oldAvatarId."<br>";
        $oldAvatar = $em->getRepository('OlegUserdirectoryBundle:Document')->find($oldAvatarId);

        if( $oldAvatar ) {

            //echo "old avatar id=".$oldAvatar->getId()."<br>";

            $oldImageAvatar = $oldAvatar->getAbsoluteUploadFullPath();
            //$oldImageUpload = str_replace($crop->getAvatarPostfix(),$crop->getUploadPostfix(),$oldImageAvatar);
            $oldImageUpload = str_replace('avatar','upload',$oldImageAvatar);

            $fs = new Filesystem();
            try {
                $fs->remove(array($oldImageAvatar));
            } catch (IOExceptionInterface $e) {
                echo "An error occurred while creating your directory at ".$e->getPath();
            }

            try {
                $fs->remove(array($oldImageUpload));
            } catch (IOExceptionInterface $e) {
                echo "An error occurred while creating your directory at ".$e->getPath();
            }

            //exit('delete old avatar');
            $em->remove($oldAvatar);
            $em->flush();
        }
    }


    public function removeCollection($originalArr,$currentArr,$subjectUser=null) {
        $em = $this->getDoctrine()->getManager();
        $removeArr = array();

        foreach( $originalArr as $title ) {

            //check if location is not home and main
            if( method_exists($title,'getRemovable') ) {
                //echo "remove location=".$title."<br>";
                if( $title->getRemovable() == false ) {
                    continue;
                }
            }

            //echo "title=".$title.", id=".$title->getId()."<br>";
            $em->persist($title);

            if( false === $currentArr->contains($title) ) {
                $removeArr[] = "<strong>"."Removed: ".$title." ".$this->getEntityId($title)."</strong>";
                //echo "before delete <br>";
//                if( is_subclass_of($title, 'Oleg\UserdirectoryBundle\Entity\ListAbstract') === false ) {
//                    //echo "delete object entirely <br>";
//                    // delete object entirely
//                    $em->remove($title);
//                    $em->flush();
//                } else {
                    //echo 'no delete from DB because list <br>';
                    //echo "subjectUser=".$subjectUser."<br>";
                    if( $subjectUser ) {

                        if( $title instanceof ResearchLab ) {
                            //remove dependents: remove comments and id from lab
                            $em->getRepository('OlegUserdirectoryBundle:ResearchLab')->removeDependents( $subjectUser, $title );
                        } elseif ( $title instanceof Grant ) {
                            //remove dependents: remove documents
                            $em->getRepository('OlegUserdirectoryBundle:Grant')->removeDependents( $subjectUser, $title );
                        } else {
                            if( method_exists($title,'removeUser') ) {
                                $title->removeUser($subjectUser);
                            }
                            if( method_exists($title,'setUser') ) {
                                $title->setUser($subjectUser);
                            }
                            //echo "delete object entirely <br>";
                            // delete object entirely
                            $em->remove($title);
                            $em->flush();
                        }

                        //TODO: remove documents from comments?
                    }
                //}
            } else {
                //echo "no delete <br>";
            }

        } //foreach

        //exit('done remove collection');

        return implode("<br>", $removeArr);
    }

    //record if different: old values, new values
    public function recordToEvenLogDiffCollection($originalArr,$currentArr,$text) {
        $removeArr = array();

        $original = $this->listToArray($originalArr);
        $new = $this->listToArray($currentArr);

        $diff = array_diff($original, $new);

        if( count($original) != count($new) || count($diff) != 0 ) {
            $removeArr[] = "<strong>"."Original ".$text.": ".implode(", ",$original)."</strong>";
            $removeArr[] = "<strong>"."New ".$text.": ".implode(", ",$new)."</strong>";
        }

        return implode("<br>", $removeArr);
    }
    public function listToArray($collection) {
        $resArr = array();
        foreach( $collection as $item ) {
            $resArr[] = $item."";
        }
        return $resArr;
    }


    /**
     * Generate users from excel
     *
     * @Route("/user/generate", name="generate_users")
     * @Method("GET")
     * @Template("OlegUserdirectoryBundle:Admin:users.html.twig")
     */
    public function generateUsersAction()
    {

        if( false === $this->get('security.authorization_checker')->isGranted('ROLE_USERDIRECTORY_ADMIN') ) {
            $this->get('session')->getFlashBag()->add(
                'notice',
                'You do not have permission to visit this page'
            );
            return $this->redirect($this->generateUrl('employees-nopermission'));
        }

        $inputFileName = __DIR__ . '/../../../../../importLists/ImportUsersTemplate.xlsx';

        $userGenerator = $this->container->get('user_generator');

        //list v1
        //$count_users = $userGenerator->generateUsersExcelV1();

        //list v2 provided by Jessica
        $count_users = $userGenerator->generateUsersExcelV2($inputFileName);

        if( $count_users > 0 ) {
            $msg = 'Imported ' . $count_users . ' new users from Excel.';
        } else {
            $msg = 'Imported new users from Excel failed.';
        }

        $this->get('session')->getFlashBag()->add(
            'notice',
            $msg
        );

        //exit();
        return $this->redirect($this->generateUrl('employees_listusers'));
    }

    public function getUserRoles( $asLabelValue=true ) {
        $rolesArr = array();
        $em = $this->getDoctrine()->getManager();
        $roles = $em->getRepository('OlegUserdirectoryBundle:Roles')->findBy(
            array('type' => array('default','user-added')),
            array('orderinlist' => 'ASC')
        );  //findAll();
        foreach( $roles as $role ) {
            if( $asLabelValue ) {
                $rolesArr[$role->getAlias()] = $role->getName();
            } else {
                $rolesArr[$role->getName()] = $role->getAlias();
            }
        }
        return $rolesArr;
    }


    /**
     * @Route("/lockunlock/change/{id}/{status}", name="employees_lockunlock_change", requirements={"id" = "\d+"})
     * @Method("GET")
     * @Template()
     */
    public function lockUnlockChangeAction(Request $request, $id, $status) {

        if (false === $this->get('security.authorization_checker')->isGranted('ROLE_USERDIRECTORY_EDITOR')) {
            return $this->redirect( $this->generateUrl('employees-nopermission') );
        }

        $this->lockUnlock($request, $id, $status, $this->container->getParameter('employees.sitename'));

        return $this->redirect($this->generateUrl($this->container->getParameter('employees.sitename').'_listusers'));
    }

    public function lockUnlock($request, $id, $status, $sitename) {

        $em = $this->getDoctrine()->getManager();

        $user = $em->getRepository('OlegUserdirectoryBundle:User')->find($id);

        if (!$user) {
            throw $this->createNotFoundException('Unable to find User entity.');
        }

        if( $status == "lock" ) {
            $user->setLocked(true);
        }

        if( $status == "unlock" ) {
            $user->setLocked(false);
        }

        //record edit user to Event Log
        $userAdmin = $this->get('security.token_storage')->getToken()->getUser();
        $event = "User information of ".$user." has been changed by ".$userAdmin.":"."<br>";
        $event = $event . "User status changed to ".$status;
        $userSecUtil = $this->get('user_security_utility');
        $userSecUtil->createUserEditEvent($sitename,$event,$userAdmin,$user,$request,'User record updated');

        $em->persist($user);
        $em->flush();

    }


    //User log should record all changes in user: subjectUser, Author, field, old value, new value.
    public function setEventLogChanges($subjectuser) {
        
        $em = $this->getDoctrine()->getManager();

        $uow = $em->getUnitOfWork();
        $uow->computeChangeSets(); // do not compute changes if inside a listener

        $eventArr = array();

        //log simple fields
        $changeset = $uow->getEntityChangeSet($subjectuser);
        $eventArr = $this->addChangesToEventLog( $eventArr, $changeset );

        //log preferences
        $changeset = $uow->getEntityChangeSet($subjectuser->getPreferences());
        $text = "("."Preferences ".$this->getEntityId($subjectuser->getPreferences()).")";
        $eventArr = $this->addChangesToEventLog( $eventArr, $changeset, $text );
        //preferences: languages
        foreach( $subjectuser->getPreferences()->getLanguages() as $subentity ) {
            $changeset = $uow->getEntityChangeSet($subentity);
            $text = "("."Language ".$this->getEntityId($subentity).")";
            $eventArr = $this->addChangesToEventLog( $eventArr, $changeset, $text );
        }
//        //preferences: showToInstitutions
//        foreach( $subjectuser->getPreferences()->getShowToInstitutions() as $subentity ) {
//            echo "inst=".$subentity."<br>";
//            $changeset = $uow->getEntityChangeSet($subentity);
//            $text = "("."Show To Institutions ".$this->getEntityId($subentity).")";
//            $eventArr = $this->addChangesToEventLog( $eventArr, $changeset, $text );
//        }
//        exit();
        //preferences: showToRoles
        if( $subjectuser->getPreferences()->getShowToRoles() && count($subjectuser->getPreferences()->getShowToRoles()) > 0 ) {
            foreach( $subjectuser->getPreferences()->getShowToRoles() as $subentity ) {
                $changeset = $uow->getEntityChangeSet($subentity);
                //echo "role=".$subentity."<br>";
                //exit();
                $text = "("."Show To Roles ".$subentity.")";
                $eventArr = $this->addChangesToEventLog( $eventArr, $changeset, $text );
            }
        }

        //log credentials
        $credentials = $subjectuser->getCredentials();
        $changeset = $uow->getEntityChangeSet($credentials);
        $text = "("."Credentials ".$this->getEntityId($credentials).")";
        $eventArr = $this->addChangesToEventLog( $eventArr, $changeset, $text );
        //credentials: codeNYPH
        foreach( $credentials->getCodeNYPH() as $subentity ) {
            $changeset = $uow->getEntityChangeSet($subentity);
            $text = "("."codeNYPH ".$this->getEntityId($subentity).")";
            $eventArr = $this->addChangesToEventLog( $eventArr, $changeset, $text );
        }
        //credentials: stateLicense
        foreach( $credentials->getStateLicense() as $subentity ) {
            $changeset = $uow->getEntityChangeSet($subentity);
            $text = "("."stateLicense ".$this->getEntityId($subentity).")";
            $eventArr = $this->addChangesToEventLog( $eventArr, $changeset, $text );
        }
        //credentials: boardCertification
        foreach( $credentials->getBoardCertification() as $subentity ) {
            $changeset = $uow->getEntityChangeSet($subentity);
            $text = "("."boardCertification ".$this->getEntityId($subentity).")";
            $eventArr = $this->addChangesToEventLog( $eventArr, $changeset, $text );
        }

        //publicComments
        foreach( $subjectuser->getPublicComments() as $subentity ) {
            $changeset = $uow->getEntityChangeSet($subentity);
            $text = "("."publicComments ".$this->getEntityId($subentity).")";
            $eventArr = $this->addChangesToEventLog( $eventArr, $changeset, $text );
        }
        //privateComments
        foreach( $subjectuser->getPrivateComments() as $subentity ) {
            $changeset = $uow->getEntityChangeSet($subentity);
            $text = "("."privateComments ".$this->getEntityId($subentity).")";
            $eventArr = $this->addChangesToEventLog( $eventArr, $changeset, $text );
        }
        //adminComments
        foreach( $subjectuser->getAdminComments() as $subentity ) {
            $changeset = $uow->getEntityChangeSet($subentity);
            $text = "("."adminComments ".$this->getEntityId($subentity).")";
            $eventArr = $this->addChangesToEventLog( $eventArr, $changeset, $text );
        }
        //confidentialComments
        foreach( $subjectuser->getConfidentialComments() as $subentity ) {
            $changeset = $uow->getEntityChangeSet($subentity);
            $text = "("."confidentialComments ".$this->getEntityId($subentity).")";
            $eventArr = $this->addChangesToEventLog( $eventArr, $changeset, $text );
        }

        //log Location(s)
        foreach( $subjectuser->getLocations() as $loc ) {
            $changeset = $uow->getEntityChangeSet($loc);
            $text = "("."Location ".$this->getEntityId($loc).")";
            $eventArr = $this->addChangesToEventLog( $eventArr, $changeset, $text );
        }

        //log Administrative Title(s)
        foreach( $subjectuser->getAdministrativeTitles() as $title ) {
            $changeset = $uow->getEntityChangeSet($title);
            $text = "("."Administrative Title ".$this->getEntityId($title).")";
            $eventArr = $this->addChangesToEventLog( $eventArr, $changeset, $text );
        }

        //log Academic Appointment Title(s)
        foreach( $subjectuser->getAppointmentTitles() as $title ) {
            $changeset = $uow->getEntityChangeSet($title);
            $text = "("."Academic Appointment Title ".$this->getEntityId($title).")";
            $eventArr = $this->addChangesToEventLog( $eventArr, $changeset, $text );
        }

        //log Medical Appointment Title(s)
        foreach( $subjectuser->getMedicalTitles() as $title ) {
            $changeset = $uow->getEntityChangeSet($title);
            $text = "("."Medical Appointment Title ".$this->getEntityId($title).")";
            $eventArr = $this->addChangesToEventLog( $eventArr, $changeset, $text );
        }

        //log Employment Status
        foreach( $subjectuser->getEmploymentStatus() as $item ) {
            $changeset = $uow->getEntityChangeSet($item);
            $text = "("."Employment Status ".$this->getEntityId($item).")";
            $eventArr = $this->addChangesToEventLog( $eventArr, $changeset, $text );
        }

        //log Research Labs
        foreach( $subjectuser->getResearchLabs() as $item ) {
            $changeset = $uow->getEntityChangeSet($item);
            $text = "("."Research Lab ".$this->getEntityId($item).")";
            $eventArr = $this->addChangesToEventLog( $eventArr, $changeset, $text );
        }

        //log Grants
        foreach( $subjectuser->getGrants() as $item ) {
            $changeset = $uow->getEntityChangeSet($item);
            $text = "("."Grant ".$this->getEntityId($item).")";
            $eventArr = $this->addChangesToEventLog( $eventArr, $changeset, $text );
        }

        //log Publications
        foreach( $subjectuser->getPublications() as $item ) {
            $changeset = $uow->getEntityChangeSet($item);
            $text = "("."Publication ".$this->getEntityId($item).")";
            $eventArr = $this->addChangesToEventLog( $eventArr, $changeset, $text );
        }

        //log Books
        foreach( $subjectuser->getBooks() as $item ) {
            $changeset = $uow->getEntityChangeSet($item);
            $text = "("."Book ".$this->getEntityId($item).")";
            $eventArr = $this->addChangesToEventLog( $eventArr, $changeset, $text );
        }

        //log Lectures
        foreach( $subjectuser->getLectures() as $item ) {
            $changeset = $uow->getEntityChangeSet($item);
            $text = "("."Lecture ".$this->getEntityId($item).")";
            $eventArr = $this->addChangesToEventLog( $eventArr, $changeset, $text );
        }

        //log Trainings (Educations)
        foreach( $subjectuser->getTrainings() as $item ) {
            $changeset = $uow->getEntityChangeSet($item);
            $text = "("."Training ".$this->getEntityId($item).")";
            $eventArr = $this->addChangesToEventLog( $eventArr, $changeset, $text );
        }

        return $eventArr;

    }

    public function addChangesToEventLog( $eventArr, $changeset, $text="" ) {

        $changeArr = array();

        //echo "count changeset=".count($changeset)."<br>";

        //process $changeset: author, subjectuser, oldvalue, newvalue
        foreach( $changeset as $key => $value ) {
            if( $value[0] != $value[1] ) {

                if( is_object($key) ) {
                    //if $key is object then skip it, because we don't want to have non-informative record such as: credentials(stateLicense New): old value=, new value=Credentials
                    continue;
                }

                $field = $key;

                $oldValue = $value[0];
                $newValue = $value[1];

                if( $oldValue instanceof \DateTime ) {
                    $oldValue = $this->convertDateTimeToStr($value[0]);
                }
                if( $newValue instanceof \DateTime ) {
                    $newValue = $this->convertDateTimeToStr($value[1]);
                }

                if( is_array($oldValue) ) {
                    $oldValue = implode(", ",$oldValue);
                }
                if( is_array($newValue) ) {
                    $newValue = implode(", ",$newValue);
                }

                $event = "<strong>".$field.$text."</strong>".": "."old value=".$oldValue.", new value=".$newValue;

                //don't record values for password
                if( $field == 'password' ) {
                    $event = "<strong>".$field.$text."</strong>";
                }

                //echo "event=".$event."<br>";
                //exit();

                $changeArr[] = $event;
            }
        }

        if( count($changeArr) > 0 ) {
            $eventArr[] = implode("<br>", $changeArr);
        }

        return $eventArr;

    }

    public function convertDateTimeToStr($datetime) {
        $transformer = new DateTimeToStringTransformer(null,null,'m/d/Y');
        $dateStr = $transformer->transform($datetime);
        return $dateStr;
    }

    public function getEntityId($entity) {
        //echo "entity=".$entity."<br>";
        if( $entity && $entity->getId() ) {
            return "ID=".$entity->getId();
        }

        return "New";
    }

    public function setSessionForbiddenNote($msg) {
        $this->get('session')->getFlashBag()->add(
            'notice',
            "You do not have permission to perform this operation: ".$msg
        );
//        $this->get('session')->getFlashBag()->add(
//            'pnotify-error',
//            "You do not have permission to perform this operation: ".$msg
//        );
    }

    function array_diff_assoc_true($array1, $array2)
    {
        //$diff1 = array_diff_assoc($array1,$array2);
        //$diff2 = array_diff_assoc($array2,$array1);
        $diff1 = array_diff($array1,$array2);
        $diff2 = array_diff($array2,$array1);

        //echo "diff1:<br>";
        //print_r($diff1);
        //echo "<br>diff2:<br>";
        //print_r($diff2);
        //echo "<br><br>";

        $res = array_merge( $diff1, $diff2 );
        $res = array_unique($res);

        //echo "res:<br>";
        //print_r($res);
        //echo "<br><br>";

        return $res;
    }






    /**
     * @Route("/user/save-avatar", name="employees_save_avatar")
     * @Method("POST")
     * @Template("OlegUserdirectoryBundle:Admin:users.html.twig")
     */
    public function saveAvatarAction(Request $request)
    {

//        $src = $_POST['avatar_src'];
//        $data = $_POST['avatar_data'];
//        $file = $_FILES['avatar_file'];
//        $userid = $_POST['avatar_userid'];

        $src = $request->get('avatar_src');
        $data = $request->get('avatar_data');
        $file = $_FILES['avatar_file']; //$request->get('avatar_file');
        $userid = $request->get('avatar_userid');

        //echo "src=".$src." <br>";
        //echo "data=".$data." <br>";
        //echo "file=".$file." <br>";
        //echo "userid1=".$userid." <br>";

        $secUtil = $this->get('user_security_utility');
        if( !$secUtil->isCurrentUser($userid) && false === $this->get('security.authorization_checker')->isGranted('ROLE_USERDIRECTORY_EDITOR') ) {
            echo "employees-nopermission<br>";
            return $this->redirect( $this->generateUrl('employees-nopermission') );
        }

        $uploadPath = "Uploaded/".$this->container->getParameter('employees.avataruploadpath');

        //$baseUrl = $this->container->get('router')->getContext()->getBaseUrl();
        //echo "baseUrl=".$baseUrl." ";

        //class CropAvatar.php new ($src, $data, $file)
        $crop = new CropAvatar($src, $data, $file, $uploadPath);

        $avatarid = NULL;
        $avatarpath = NULL;

        if( !$crop->getMsg() && $crop->getResult() ) {

            //(x86)\pacsvendor\pacsname\htdocs\order\scanorder\Scanorders2\src\Oleg\UserdirectoryBundle\Util/../../../../web/Uploaded/directory/Avatars/avatar/20150106205815.jpeg
            $fullnameArr = explode("/", $crop->getResult());
            $uniquefilename = $fullnameArr[count($fullnameArr)-1];
            //echo "uniquefilename=".$uniquefilename." ";

            $size = filesize($crop->getResult());
            //echo "size=".$size." ";

            $uploadDir = $uploadPath . "/" .$crop->getAvatarPostfix();

            $em = $this->getDoctrine()->getManager();

            //document's creator
            $user = $this->get('security.token_storage')->getToken()->getUser();

            $object = new Document($user);
            $object->setCleanOriginalname(NULL);
            $object->setUniquename($uniquefilename);
            $object->setUploadDirectory($uploadDir);
            $object->setSize($size);

            //document's type
            $documentType = $em->getRepository('OlegUserdirectoryBundle:DocumentTypeList')->findOneByName('Avatar Image');
            $object->setType($documentType);

            $em->persist($object);
            $em->flush($object);

            $avatarid = $object->getId();
            $avatarpath = $object->getAbsoluteUploadFullPath();
        }

        $responseArr = array(
            'state'  => 200,
            'message' => $crop -> getMsg(),
            'result' => $crop -> getResult(),
            'avatarid' => $avatarid,
            'avatarpath' => $avatarpath
        );

        //echo json_encode($responseArr);

        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');
        $response->setContent(json_encode($responseArr));
        return $response;


        //exit();
        //return $this->redirect($this->generateUrl('employees_listusers'));
        //return $this->redirect($this->generateUrl('employees_listusers'));
        //return $this->redirect($this->generateUrl('employees_showuser', array('id' => $id)));
    }

    /**
     * @Route("/user/impersonate/{id}", name="employees_user_impersonate")
     * @Method("GET")
     * @Template("OlegUserdirectoryBundle:Profile:show_user.html.twig")
     */
    public function impersonateUserAction(Request $request, $id)
    {
        if( false === $this->get('security.authorization_checker')->isGranted('ROLE_PLATFORM_DEPUTY_ADMIN') ) {
            return $this->redirect( $this->generateUrl('employees-nopermission') );
        }

        $em = $this->getDoctrine()->getManager();

        //get username
        $user = $em->getRepository('OlegUserdirectoryBundle:User')->find($id);
        $username = $user->getUsername();

        //http://example.com/somewhere?_switch_user=thomas
        $url = $this->generateUrl('employees_showuser', array('id' => $id));
        $url = $url . "?_switch_user=" . $username;
        return $this->redirect($url);
    }


    /**
     * @Route("/user/employment-terminate/{id}", name="employees_user_employment_terminate")
     * @Method("GET")
     * @Template("OlegUserdirectoryBundle:Profile:show_user.html.twig")
     */
    public function employmentTerminateAction(Request $request, $id)
    {
        if( false === $this->get('security.authorization_checker')->isGranted('ROLE_USERDIRECTORY_EDITOR') ) {
            return $this->redirect( $this->generateUrl('employees-nopermission') );
        }

        $em = $this->getDoctrine()->getManager();
        $userAdmin = $this->get('security.token_storage')->getToken()->getUser();

        //get username
        $subjectUser = $em->getRepository('OlegUserdirectoryBundle:User')->find($id);

        $todayDate = new \DateTime();
        //$todayDateStr = $todayDate->format("m/d/Y");

        $yestardayDate = new \DateTime();
        $yestardayDate = $yestardayDate->add(\DateInterval::createFromDateString('yesterday'));
        $yestardayDateStr = $yestardayDate->format("m/d/Y");

        $institutionArr = array();

        //make sure EmploymentStatus exists
        if( count($subjectUser->getEmploymentStatus()) == 0 ) {
            $subjectUser->addEmploymentStatus(new EmploymentStatus($userAdmin));
        }
//        //check if Institution is assign
//        foreach( $subjectUser->getEmploymentStatus() as $employmentStatus ) {
//            $employmentStatus->createAttachmentDocument();
//            //echo "employ inst=".$employmentStatus->getInstitution()."<br>";
//            if( !$employmentStatus->getInstitution() ) {
//                $wcmc = $em->getRepository('OlegUserdirectoryBundle:Institution')->findOneByAbbreviation("WCMC");
//                if( !$wcmc ) {
//                    //exit('No Institution: "WCMC"');
//                    throw $this->createNotFoundException('No Institution: "WCMC"');
//                }
//                $mapper = array(
//                    'prefix' => 'Oleg',
//                    'bundleName' => 'UserdirectoryBundle',
//                    'className' => 'Institution'
//                );
//                $pathology = $em->getRepository('OlegUserdirectoryBundle:Institution')->findByChildnameAndParent(
//                    "Pathology and Laboratory Medicine",
//                    $wcmc,
//                    $mapper
//                );
//                if( !$pathology ) {
//                    //exit('No Institution: "Pathology and Laboratory Medicine"');
//                    throw $this->createNotFoundException('No Institution: "Pathology and Laboratory Medicine"');
//                }
//                $employmentStatus->setInstitution($pathology);
//            }
//        }

        //A- Add yesterday's date into the "Employment Period(s) [visible only to Editors and Administrators]">"End of Employment Date:"
        // FOR EVERY EMPTY "End of Employment Date:" in that section if there is more than one since it is an array.
        foreach( $subjectUser->getEmploymentStatus() as $employmentStatus ) {
            if( !$employmentStatus->getTerminationDate() ) {
                $employmentStatus->setTerminationDate($yestardayDate);
                if( $employmentStatus->getInstitution() ) {
                    $institutionArr[] = $employmentStatus->getInstitution()."";
                }
            }
        }

        $institutionStr = implode(", ",$institutionArr);

        //B- In Global User Preferences, mark "Prevent user from logging in (lock):" as checked.
        $subjectUser->setLocked(true);

        $em->flush();

        //C- Add an Event to the Event Log (add an Event Type of "User marked as no longer employed")
        // with "FirstName LastName (CWID: xxx) marked as no longer employed by [Institution] as of MM/DD/YYYY
        // by FirstName LastName (CWID: xxx) and account locked" in the Event Description and properly
        // populate the user performing the change and the Object Type/ID of the user receiving the change.
        $event = $subjectUser->getUsernameOptimal()." marked as no longer employed by ".$institutionStr." as of ".$yestardayDateStr;
        $event .= " by ".$userAdmin->getUsernameOptimal()." and account locked";
        $userSecUtil = $this->get('user_security_utility');
        $userSecUtil->createUserEditEvent(
            $this->container->getParameter('employees.sitename'),
            $event,
            $userAdmin,
            $subjectUser,
            $request,
            'User Employment Terminated'
        );

        //D- Once successful, display a blue well at the top of the user's profile page saying
        // "Successfully marked user as no longer working at the [Institution] as of yesterday, MM/DD/YYYY."
        $eventSession = "Successfully marked ".$subjectUser->getUsernameOptimal()." as no longer working at the ".$institutionStr." as of yesterday, ".$yestardayDateStr.".";
        $this->get('session')->getFlashBag()->add(
            'notice',
            $eventSession
        );

        $sitename = "employees";
        return $this->redirect($this->generateUrl($sitename.'_showuser', array('id' => $id)));
    }


    /**
     * @Route("/download/printable-lab-directory", name="employees_userlist_download_excel")
     */
    public function downloadAction( Request $request )
    {

        if (
            false == $this->get('security.authorization_checker')->isGranted('ROLE_USER') ||              // authenticated (might be anonymous)
            false == $this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')    // authenticated (NON anonymous)
        ) {
            return $this->redirect($this->generateUrl('employees-nopermission'));
        }

        $userSecUtil = $this->container->get('user_security_utility');

        $em = $this->getDoctrine()->getManager();

        //testing
//        $em = $this->getDoctrine()->getManager();
//        $user = $em->getRepository('OlegUserdirectoryBundle:User')->find(158);//"Melissa"
//        echo "<br>live user=".$user.":<br>";
//        $instResArr = $user->getDeduplicatedInstitutions();
//        foreach( $instResArr as $instRes ) {
//            $instName = $instRes[0]['instName'];
//            $instName = strtoupper($instName);
//            echo "add instName=".$instName."<br>";
//        }
//        $user = $em->getRepository('OlegUserdirectoryBundle:User')->find(71);//"Melissa"
//        echo "<br>test user=".$user.":<br>";
//        $instResArr = $user->getDeduplicatedInstitutions();
//        foreach( $instResArr as $instRes ) {
//            $instName = $instRes[0]['instName'];
//            $instName = strtoupper($instName);
//            echo "add instName=".$instName."<br>";
//        }
//        if( $user ) {
//            $lastname = $user->getSingleLastName();
//            echo "lastname=[$lastname]<br>";
//            $firstname = $user->getSingleFirstName();
//            echo "firstname=[$firstname]<br>";
//        }
//        exit('testing');

        $inst1 = null;
        $inst2 = null;

        $mapper = array(
            'prefix' => 'Oleg',
            'bundleName' => 'UserdirectoryBundle',
            'className' => 'Institution'
        );

        //$wcmcpathology
        //$wcmc = $em->getRepository('OlegUserdirectoryBundle:Institution')->findOneByAbbreviation("WCMC");
        //navbarFilterInstitution1
        $wcmc = $userSecUtil->getSiteSettingParameter('navbarFilterInstitution1');
        if( $wcmc ) {
            $wcmcpathology = $em->getRepository('OlegUserdirectoryBundle:Institution')->findByChildnameAndParent(
                "Pathology and Laboratory Medicine",
                $wcmc,
                $mapper
            );
            $inst1 = $wcmc->getAbbreviation();
        }

        //$nyppathology
        //$nyp = $em->getRepository('OlegUserdirectoryBundle:Institution')->findOneByAbbreviation("NYP");
        $nyp = $userSecUtil->getSiteSettingParameter('navbarFilterInstitution2');
        if( $nyp ) {
            $nyppathology = $em->getRepository('OlegUserdirectoryBundle:Institution')->findByChildnameAndParent(
                "Pathology and Laboratory Medicine",
                $nyp,
                $mapper
            );
            $inst2 = $nyp->getAbbreviation();
        }

        //$fileName = WCM-Pathology-Phone-List-MM-DD-YYYY-HH-MM.xlsx
        $currentDate = date('m-d-Y-H-i');
        $fileName = $inst1."-Pathology-Phone-List-".$currentDate.".xlsx";
        //$fileName = "users_".$currentDate.".xls";
        $fileName = str_replace("  ", " ", $fileName);
        $fileName = str_replace(" ", "_", $fileName);

        //$sheet title
        //$sheetTitle = "DEPARTMENT OF PATHOLOGY AND LABORATORY MEDICINE FACULTY AND KEY PERSONNEL FY".date('y');
        //$sheetTitle = "DEPARTMENT OF PATHOLOGY AND LABORATORY MEDICINE FACULTY AND KEY PERSONNEL";

        //footer
        //$sheetFooter = "Rev ".date('m-d-Y')." at ".date('H:i');
        //$footer = $sheetTitle . " " . $footer;

        $userDownloadUtil = $this->container->get('user_download_utility');

        ////////////// WCM Pathology Employees //////////////
        $filter = "$inst1 Pathology Employees";
        $params = array('filter'=>$filter,'time'=>'current_only','limitFlag'=>null);
        $res = $this->indexUser($request,$params);
        $users = $res['entities'];
        //echo "count users=".count($users)."<br>";
        //exit("inst1=$inst1; inst2=$inst2");
        ////////////// EOF WCM Pathology Employees //////////////

        ////////////// WCM Pathology Employees Download Faculty //////////////
        $filterFaculty = "$inst1 Pathology Employees Download Faculty";
        $paramsFaculty = array('filter'=>$filterFaculty,'time'=>'current_only','limitFlag'=>null);
        $res = $this->indexUser($request,$paramsFaculty);
        $usersFaculty = $res['entities'];
        ////////////// EOF WCM Pathology Employees //////////////

        ////////////// With Administrative Title /////////////
        $filterAdmin = "With Administrative Title";
        $adminParams = array('filter'=>$filterAdmin,'time'=>'current_only','limitFlag'=>null);
        $resAdmin = $this->indexUser($request,$adminParams);
        $administrativeUsers = $resAdmin['entities'];
        ////////////// EOF With Administrative Title /////////////

        ////////////// Administrative /////////////
//        $filterAdminDivision = "Administration Division";
//        $adminDivisionParams = array('filter'=>$filterAdminDivision,'time'=>'current_only','limitFlag'=>null);
//        $resAdminDivision = $this->indexUser($request,$adminDivisionParams);
//        $administrationDivisionUsers = $resAdminDivision['entities'];
        ////////////// EOF Administrative /////////////

        ////////////// Locations /////////////
        //$userUtil = new UserUtil();
        //$filter = "WCM Pathology Department Common Location For Phone Directory";
        //$locations = $userUtil->indexLocation($filter, $request, $this->container, $this->getDoctrine());
        $locations = null;
        ////////////// EOF Locations /////////////

        $autoAssignInstitution = $userSecUtil->getAutoAssignInstitution();
        if( $autoAssignInstitution ) {
            $sheetTitle = $autoAssignInstitution->getName();
            if( $sheetTitle ) {
                $sheetTitle = strtoupper($sheetTitle);
                $sheetTitle = "DEPARTMENT OF " . $sheetTitle;
            }
        } else {
            $sheetTitle = "DEPARTMENT OF PATHOLOGY AND LABORATORY MEDICINE";
        }

        $excelBlob = null;

        ///////////////////// Faculty List /////////////////////
        $facultySections = array(
            " " => $usersFaculty,
            //"ADMINISTRATION" => $administrationDivisionUsers
        );
        if(1) {
            $sheetParams = array(
                'excelBlob' => $excelBlob,
                'sheetTitle' => $sheetTitle." \n FACULTY ADMINISTRATION TELEPHONE LIST \n FY" . date('y') . "\n\n",
                'sheetFooter' => "Rev ".date('m-d-Y')." at ".date('H:i'),
                'sheetName' => 'Faculty List',
                'sheetIndex' => 0,
                'sheetHeaders' => array('A1'=>'FACULTY','B1'=>'TITLE', 'C1'=>'EXTENSION', 'D1'=>'LOCATION','E1'=>'EMAIL'),
                'sheetFont' => 'Times New Roman',   //'Times New Roman',  //'Colibri',
                'sheetSize' => 10,
                'sheetZoom' => 100,
                'sheetHeaderSize' => 11,
                'withAssistants' => false
            );
            $excelBlob = $userDownloadUtil->createUserListExcel($facultySections, $sheetParams);
        }
        ///////////////////// EOF Faculty List /////////////////////


        ///////////////////// Housestaff - Residents //////////////////////////
        $filterHousestaffResidents = "$inst1 or $inst2 Pathology Residents";
        $housestaffResidentsParams = array('filter'=>$filterHousestaffResidents,'time'=>'current_only','limitFlag'=>null);
        $resHousestaffResidents = $this->indexUser($request,$housestaffResidentsParams);
        $housestaffResidents = $resHousestaffResidents['entities'];
        ///////////////////// EOF Housestaff //////////////////////////

        ///////////////////// Housestaff - Fellows //////////////////////////
        $filterHousestaffFellows = "$inst1 or $inst2 Pathology Fellows";
        $housestaffFellowsParams = array('filter'=>$filterHousestaffFellows,'time'=>'current_only','limitFlag'=>null);
        $resHousestaffFellows = $this->indexUser($request,$housestaffFellowsParams);
        $housestaffFellows = $resHousestaffFellows['entities'];
        ///////////////////// EOF Housestaff //////////////////////////


        ///////////////////// Department List /////////////////////
        $departmentSections = $userDownloadUtil->getSections($users,$administrativeUsers,$locations);

        if( count($housestaffResidents) > 0 ) {
            $departmentSections["HOUSESTAFF - RESIDENTS"] = $housestaffResidents;
        }
        if( count($housestaffFellows) > 0 ) {
            $departmentSections["HOUSESTAFF - FELLOWS"] = $housestaffFellows;
        }

//        echo '<br><br>sections:<pre>';
//        print_r($sections);
//        echo  '</pre>';
//        exit();

        $sheetParams = array(
            'excelBlob' => $excelBlob,
            'sheetTitle' => $sheetTitle." \n FACULTY AND KEY PERSONNEL FY".date('y')."\n\n",
            'sheetFooter' => "Rev ".date('m-d-Y')." at ".date('H:i'),
            'sheetName' => 'Department List',
            'sheetIndex' => 1,
            'sheetHeaders' => null, //array('A1'=>'FACULTY','B1'=>'TITLE', 'C1'=>'EXTENSION', 'D1'=>'LOCATION','E1'=>'EMAIL'),
            'sheetFont' => 'Colibri',   //'Times New Roman',  //'Colibri',
            'sheetSize' => 10,
            'sheetZoom' => 100,
            'sheetHeaderSize' => 11,
            'withAssistants' => true
        );
        $excelBlob = $userDownloadUtil->createUserListExcel($departmentSections,$sheetParams);
        ///////////////////// EOF Department List /////////////////////

        if(1) {
            $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($excelBlob, 'Xlsx');
            //ob_end_clean();
            //$writer->setIncludeCharts(true);
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="' . $fileName . '"');
            //header('Content-Disposition: attachment;filename="fileres.xlsx"');

            // Write file to the browser
            $writer->save('php://output');

            exit();
        }
    }

    /**
     * @Route("/label/user/preview/{id}", name="employees_user_label_preview")
     * @Method({"GET","POST"})
     * @Template("OlegUserdirectoryBundle:Labels:label_user_preview.html.twig")
     */
    public function averySingleUserPrintAction(Request $request, $id) {
        if( false === $this->get('security.authorization_checker')->isGranted('ROLE_USERDIRECTORY_EDITOR') ) {
            return $this->redirect( $this->generateUrl('employees-nopermission') );
        }

        $em = $this->getDoctrine()->getManager();
        $userDownloadUtil = $this->container->get('user_download_utility');

        //get user label
        $subjectUser = $em->getRepository('OlegUserdirectoryBundle:User')->find($id);
        $userElStr = $userDownloadUtil->getLabelSingleUser($subjectUser);

        $params = array('label'=>$userElStr,'singleUser'=>true);
        $form = $this->createForm(LabelType::class,null,array('form_custom_value'=>$params));

        $form->handleRequest($request);

        if( $form->isSubmitted() ) {
            $userlabel = $form['userlabel']->getData();
            //echo "userlabel=".$userlabel."<br>";

            $dotborders = $form['dotborders']->getData();
            $labelmax = $form['labelcount']->getData();
            $startcolumn = $form['startcolumn']->getData();
            $startrow = $form['startrow']->getData();
            //$endrow = $form['endrow']->getData();

            //return $this->redirect($this->generateUrl('employees_user_avery_5160', array('id'=>$id, 'userlabel'=>$userlabel)));

            $usersArr = array();

            $startIndex = 0;
            //$num = 30; //3 x 10

            $num = 30;//$endrow * 3; //30

            if( $labelmax == 0 ) {
                $labelmax = 30;
            }

            //$startrow
            //$startrow=1 => $currentLabelCount=0 (1-1)*3 = 0
            //$startrow=2 => $currentLabelCount=3 (2-1)*3 = 3
            //$startrow=3 => $currentLabelCount=6 (3-1)*3 = 6
            //$currentLabelCount = ($startrow-1)*3;

            //4 row; 2 column => (4-1)*3 + (2-1) = 10 empty labels in front
            $emptyLabelCount = ($startrow-1)*3 + ($startcolumn-1);

            $labelCount = 0;
            $labelUserCount = 0;
            for( $i=$startIndex; $i<$num; $i++ ) {
                //if( $labelUserCount < $labelmax && $labelCount >= $currentLabelCount ) {
                if( $labelUserCount < $labelmax && $labelCount >= $emptyLabelCount ) {
                    $usersArr[] = $userlabel;   //$userEl;
                    $labelUserCount++;
                } else {
                    $usersArr[] = null;
                }
                $labelCount++;
            }

            return $this->render('OlegUserdirectoryBundle:Labels:avery_5160.html.twig', array(
                'userlabels' => $usersArr,
                'labelperpage' => 30,    //30
                'dotborders' => $dotborders,
                'pagemargin' => ""   //"margin: 0.50in 0px 0px 0px;"
            ));
        }

        return array(
            'form' => $form->createView(),
            'labelpreview' => true,
            //'userEl' => $userEl,
            'title' => "User Label Print Management and Preview"
        );
    }
    /**
     * @Route("/label/users/preview/", name="employees_users_label_preview")
     * @Method({"GET","POST"})
     * @Template("OlegUserdirectoryBundle:Labels:label_user_preview.html.twig")
     */
    public function averyMultipleUsersPrintAction(Request $request) {
        if( false === $this->get('security.authorization_checker')->isGranted('ROLE_USERDIRECTORY_EDITOR') ) {
            return $this->redirect( $this->generateUrl('employees-nopermission') );
        }

        $em = $this->getDoctrine()->getManager();
        $userSecUtil = $this->get('user_security_utility');
        $userDownloadUtil = $this->container->get('user_download_utility');

        $inst1 = null;
        $inst2 = null;

        $mapper = array(
            'prefix' => 'Oleg',
            'bundleName' => 'UserdirectoryBundle',
            'className' => 'Institution'
        );

        //$wcmcpathology
        //$wcmc = $em->getRepository('OlegUserdirectoryBundle:Institution')->findOneByAbbreviation("WCMC");
        //navbarFilterInstitution1
        $wcmc = $userSecUtil->getSiteSettingParameter('navbarFilterInstitution1');
        if( $wcmc ) {
            $wcmcpathology = $em->getRepository('OlegUserdirectoryBundle:Institution')->findByChildnameAndParent(
                "Pathology and Laboratory Medicine",
                $wcmc,
                $mapper
            );
            $inst1 = $wcmc->getAbbreviation();
        }

        //$nyppathology
        //$nyp = $em->getRepository('OlegUserdirectoryBundle:Institution')->findOneByAbbreviation("NYP");
        $nyp = $userSecUtil->getSiteSettingParameter('navbarFilterInstitution2');
        if( $nyp ) {
            $nyppathology = $em->getRepository('OlegUserdirectoryBundle:Institution')->findByChildnameAndParent(
                "Pathology and Laboratory Medicine",
                $nyp,
                $mapper
            );
            $inst2 = $nyp->getAbbreviation();
        }

        //get users
        //$users = $em->getRepository('OlegUserdirectoryBundle:User')->findAll();
        ////////////// WCM Pathology Employees Download Faculty //////////////
        $filterFaculty = "$inst1 Pathology Employees Download Faculty";
        $paramsFaculty = array('filter'=>$filterFaculty,'time'=>'current_only','limitFlag'=>null);
        $res = $this->indexUser($request,$paramsFaculty);
        $facultyUsers = $res['entities'];
        //echo "facultyUsers count=".count($facultyUsers)."<br>";
        ////////////// EOF WCM Pathology Employees //////////////

        ////////////// WCM Pathology Employees //////////////
        $filter = "$inst1 Pathology Employees";
        $params = array('filter'=>$filter,'time'=>'current_only','limitFlag'=>null);
        $res = $this->indexUser($request,$params);
        $users = $res['entities'];
        ////////////// EOF WCM Pathology Employees //////////////

        $allusers = array_unique (array_merge ($facultyUsers, $users));

//        foreach( $users as $user ) {
//            echo $user->getId().": ".$user->getUsernameOptimal()."<br>";
//            break;
//        }

        $params = array('singleUser'=>false,'allusers'=>$allusers,'users'=>$facultyUsers);
        $form = $this->createForm(LabelType::class,null,array('form_custom_value'=>$params));

        $form->handleRequest($request);

        if( $form->isSubmitted() ) {
            //$userlabel = $form['userlabel']->getData();
            //echo "userlabel=".$userlabel."<br>";

            $dotborders = $form['dotborders']->getData();
            //$labelmax = $form['labelcount']->getData();
            $startcolumn = $form['startcolumn']->getData();
            $startrow = $form['startrow']->getData();
            //$endrow = $form['endrow']->getData();

            $selectUsers = $form['users']->getData();

            //re-save selected users as array
            $users = array();
            foreach( $selectUsers as $user ) {
                //echo $user->getId().": ".$user->getUsernameOptimal()."<br>";
                $users[] = $user;
            }
            //echo "#########<br>";

            //return $this->redirect($this->generateUrl('employees_user_avery_5160', array('id'=>$id, 'userlabel'=>$userlabel)));

            $num = count($users);

            //4 row; 2 column => (4-1)*3 + (2-1) = 10 empty labels in front
            $emptyLabelCount = ($startrow-1)*3 + ($startcolumn-1);
            $num = $num + $emptyLabelCount;

            $usersArr = array();
            $labelCount = 0;
            $labelUserCount = 0;
            for( $i=0; $i<$num; $i++ ) {
                if( $labelCount >= $emptyLabelCount ) {
                    //echo "labelUserCount=".$labelUserCount."<br>";
                    $user = $users[$labelUserCount];
                    //echo $user->getId().":: ".$user->getUsernameOptimal()."<br>";
                    $userlabel = $userDownloadUtil->getLabelSingleUser($user);
                    $usersArr[] = $userlabel;
                    $labelUserCount++;
                } else {
                    $usersArr[] = null;
                }
                $labelCount++;
            }

            return $this->render('OlegUserdirectoryBundle:Labels:avery_5160.html.twig', array(
                'userlabels' => $usersArr,
                'labelperpage' => 30,    //30
                'dotborders' => $dotborders,
                'pagemargin' => ""   //"margin: 0.50in 0px 0px 0px;"
            ));
        }

        return array(
            'form' => $form->createView(),
            'labelpreview' => false,
            //'userEl' => $userEl,
            'title' => "Users Label Print Management and Preview"
        );
    }


//    /**
//     * @Route("/account-creation/", name="employees_account_creation")
//     * @Template("OlegTranslationalResearchBundle:AccessRequest:account_confirmation.html.twig")
//     * @Method({"GET", "POST"})
//     */
//    public function accountConfirmationAction(Request $request)
//    {
//        //echo "user=".$user."; redirectPath=".$redirectPath."; specialty=".$specialty."<br>";
//        if( false === $this->get('security.authorization_checker')->isGranted('ROLE_TRANSRES_USER') ) {
//            return $this->redirect( $this->generateUrl($this->container->getParameter('translationalresearch.sitename').'-nopermission') );
//        }
//
//        $em = $this->getDoctrine()->getManager();
//        $user = $this->get('security.token_storage')->getToken()->getUser();
//        $sitename = $this->container->getParameter('translationalresearch.sitename');
//        $cycle = "new";
//
//        if( count($user->getAdministrativeTitles()) == 0 ) {
//            $user->addAdministrativeTitle(new AdministrativeTitle($user));
//        }
//        //echo "admins=".count($user->getAdministrativeTitles())."<br>";
//
//        $params = array(
//            'cycle' => $cycle,
//            'em' => $em,
//            'user' => $user,
//        );
//        $form = $this->createForm(AccountConfirmationType::class, $user, array(
//            'form_custom_value' => $params,
//        ));
//
//        $form->handleRequest($request);
//
//
//        if( $form->isSubmitted() && $form->isValid() ) {
//
//            //echo $user->getId().": Display Name=".$user->getEmail()."<br>";
//            //exit('accountConfirmationAction submit');
//
//            $em->flush();
//
//            return $this->redirect($this->generateUrl($sitename.'_showuser', array('id' => $id)));
//        }
//
//        return array(
//            'user' => $user,
//            'form' => $form->createView(),
//            'title' => "Profile Details for ".$user,
//            'cycle' => $cycle,
//            'sitename' => $sitename,
//        );
//    }
}
