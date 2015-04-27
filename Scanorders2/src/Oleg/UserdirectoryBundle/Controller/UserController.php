<?php

namespace Oleg\UserdirectoryBundle\Controller;



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
        return array();
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
//        $res = $this->indexUser( $params );
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

        //user search
        $params = array('time'=>'current_only','objectname'=>$tablename,'objectid'=>$objectid,'excludeCurrentUser'=>false);
        $res = $this->indexUser( $params ); //use function getTheSameObject
        $pagination = $res['entities'];

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

        $tablename = $request->get('tablename');
        $objectid = $request->get('id');
        $objectname = $request->get('name');

        //user search
        $params = array('time'=>'current_only','objectname'=>$tablename,'objectid'=>$objectid);
        $res = $this->indexUser( $params );
        $pagination = $res['entities'];

        $title = "Current employees: ".$tablename." ".$objectname;

        if( $tablename == "room" ) {
            $title = "Current employees in ".$tablename." ".$objectname;
        }

        if( $tablename == "administrativeTitles" ) {
            $title = 'Current employees with the administrative title of "'.$objectname.'"';
        }

        if( $tablename == "appointmentTitles" ) {
            $title = 'Current employees with the academic title of "'.$objectname.'"';
        }

        if( $tablename == "medicalTitles" ) {
            $title = 'Current employees with the medical title of "'.$objectname.'"';
        }

        if( $tablename == "service" ) {
            $title = 'Current employees of the '.$objectname.' service';
        }

        if( $tablename == "institution" ) {
            $title = 'Current employees of the '.$objectname;
        }

        if( $tablename == "division" ) {
            $title = 'Current employees of the '.$objectname.' division';
        }

        if( $tablename == "department" ) {
            $title = 'Current employees of the '.$objectname.' department';
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
            false == $this->get('security.context')->isGranted('ROLE_USER') ||              // authenticated (might be anonymous)
            false == $this->get('security.context')->isGranted('IS_AUTHENTICATED_FULLY')    // authenticated (NON anonymous)
        ){
            return $this->redirect( $this->generateUrl('login') );
        }

        //$form = $this->createForm(new SearchType(),null);

        //$form->bind($request);  //use bind instead of handleRequest. handleRequest does not get filter data
        //$search = $form->get('search')->getData();

        //check for active access requests
        $accessreqs = $this->getActiveAccessReq();

        $search = trim( $request->get('search') );
        $userid = trim( $request->get('userid') );

//        $page = $request->get('page');
//        if( !$page && $page == "" ) {
//            $page = 1;
//        }

        //echo "search=".$search."<br>";

        $locations = null;
        $pagination = null;
        $roles = null;

        if( $search != "" || $userid != "" ) {

            //location search
            $userUtil = new UserUtil();
            $locations = $userUtil->indexLocation($search, $request, $this->container, $this->getDoctrine());

            //user search
            $params = array('time'=>'current_only','search'=>$search,'userid'=>$userid);
            $res = $this->indexUser($params);
            $pagination = $res['entities'];
            $roles = $res['roles'];
        }

        return array(
            'accessreqs' => count($accessreqs),
            'locations' => $locations,
            'entities' => $pagination,
            'roles' => $roles,
            'search' => $search,
            'postData' => $request->query->all()
        );
    }

    //check for active access requests
    public function getActiveAccessReq() {
        if( !$this->get('security.context')->isGranted('ROLE_USERDIRECTORY_ADMIN') ) {
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
        if( false === $this->get('security.context')->isGranted('ROLE_USERDIRECTORY_OBSERVER') ) {
            return $this->redirect($this->generateUrl('employees-order-nopermission'));
        }

        $filter = trim( $request->get('filter') );

        $time = 'current_only';
        $routeName = $request->get('_route');
        if( $routeName == "employees_listusers_previous" ) {
            $time = 'past_only';
        }

        $params = array('filter'=>$filter,'time'=>$time);
        $res = $this->indexUser($params);

        if( $filter == "" ) {
            if( $routeName == "employees_listusers_previous" ) {
                $filter = "All Previous Employees";
            } else {
                $filter = "All Current Employees";
            }
        }

        $res['filter'] = $filter;

        return $res;
    }

    //$time: 'current_only' - search only current, 'past_only' - search only past, 'all' - search current and past (no filter)
    //public function indexUser( $filter=null, $time='all', $limitFlag=true, $search=null, $userid=null ) {
    public function indexUser( $params ) {

        $filter = ( array_key_exists('filter', $params) ? $params['filter'] : null);
        $time = ( array_key_exists('time', $params) ? $params['time'] : 'all');
        $limitFlag = ( array_key_exists('limitFlag', $params) ? $params['limitFlag'] : true);
        $search = ( array_key_exists('search', $params) ? $params['search'] : null);
        $userid = ( array_key_exists('userid', $params) ? $params['userid'] : null);
//        $myteam = ( array_key_exists('myteam', $params) ? $params['myteam'] : null);
//        $myboss = ( array_key_exists('myboss', $params) ? $params['myboss'] : null);
//        $myservice = ( array_key_exists('myservice', $params) ? $params['myservice'] : null);
        $objectname = ( array_key_exists('objectname', $params) ? $params['objectname'] : null);
        $objectid = ( array_key_exists('objectid', $params) ? $params['objectid'] : null);
        $excludeCurrentUser = ( array_key_exists('excludeCurrentUser', $params) ? $params['excludeCurrentUser'] : null);

        //echo "filter=".$filter."<br>";
        //echo "search=".$search."<br>";

        $request = $this->get('request');
        $postData = $request->query->all();

        $sort = null;
        if( isset($postData['sort']) ) {
            //check for location sort
            if( strpos($postData['sort'],'location.') === false && strpos($postData['sort'],'heads.') === false ) {
                $sort = $postData['sort'];
            }
        }

        $rolesArr = $this->getUserRoles();

        $repository = $this->getDoctrine()->getRepository('OlegUserdirectoryBundle:User');
        $dql =  $repository->createQueryBuilder("user");
        $dql->select('user');

        $dql->leftJoin("user.infos", "infos");
        $dql->leftJoin("user.employmentStatus", "employmentStatus");

        $dql->leftJoin("user.administrativeTitles", "administrativeTitles");
        $dql->leftJoin("administrativeTitles.name", "administrativeName");
        $dql->leftJoin("administrativeTitles.institution", "administrativeInstitution");
        $dql->leftJoin("administrativeTitles.department", "administrativeDepartment");
        $dql->leftJoin("administrativeTitles.division", "administrativeDivision");
        $dql->leftJoin("administrativeTitles.service", "administrativeService");

        $dql->leftJoin("user.appointmentTitles", "appointmentTitles");
        $dql->leftJoin("appointmentTitles.name", "appointmentName");
        $dql->leftJoin("appointmentTitles.institution", "appointmentInstitution");
        $dql->leftJoin("appointmentTitles.department", "appointmentDepartment");
        $dql->leftJoin("appointmentTitles.division", "appointmentDivision");
        $dql->leftJoin("appointmentTitles.service", "appointmentService");

        $dql->leftJoin("user.medicalTitles", "medicalTitles");
        $dql->leftJoin("medicalTitles.name", "medicalName");
        $dql->leftJoin("medicalTitles.institution", "medicalInstitution");
        $dql->leftJoin("medicalTitles.department", "medicalDepartment");
        $dql->leftJoin("medicalTitles.division", "medicalDivision");
        $dql->leftJoin("medicalTitles.service", "medicalService");

        $dql->leftJoin("user.locations", "locations");
        $dql->leftJoin("locations.room", "locationroom");
        $dql->leftJoin("locations.assistant", "assistant");
        $dql->leftJoin("assistant.infos", "assistantinfos");

        $dql->leftJoin("user.credentials", "credentials");

        $dql->leftJoin("user.researchLabs", "researchLabs");

        //$dql->leftJoin("user.institutions", "institutions");
        //$dql->where("user.appliedforaccess = 'active'");

        if( $sort == null ) {
            if( $time == 'current_only' ) {
                $dql->orderBy("infos.lastName","ASC");
                $dql->addOrderBy("administrativeInstitution.name","ASC");
                $dql->addOrderBy("administrativeService.name","ASC");
                $dql->addOrderBy("appointmentService.name","ASC");
                $dql->addOrderBy("medicalService.name","ASC");
            } else if( $time == 'past_only' ) {
                $dql->orderBy("employmentStatus.terminationDate","DESC");
                $dql->addOrderBy("infos.lastName","ASC");
            } else {
                $dql->orderBy("infos.lastName","ASC");
            }
        }


        if( $userid ) {

            $totalcriteriastr = "user.id =".$userid;

        } else {

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
            $criteriastr = $this->getTheSameObject( $dql, $objectname, $objectid, $excludeCurrentUser, $criteriastr );

            //time
            $userutil = new UserUtil();
            $criteriastr = $userutil->getCriteriaStrByTime( $dql, $time, null, $criteriastr );

            //filter out system user
            $totalcriteriastr = "user.keytype IS NOT NULL AND user.primaryPublicUserId != 'system'";

            if( $criteriastr ) {
                $totalcriteriastr = $totalcriteriastr . " AND (".$criteriastr.")";
            } else {

            }

//            if( $criteriastr != "" ) {
//                $totalcriteriastr = "(" . $timecriteriastr . ") AND " .  $criteriastr;
//            } else {
//                $totalcriteriastr = $timecriteriastr;
//            }

        }

        $dql->where($totalcriteriastr);

        //pass sorting parameters directly to query; Somehow, knp_paginator stoped correctly create pagination according to sorting parameters
        if( $sort ) {
            $dql = $dql . " ORDER BY $postData[sort] $postData[direction]";
        }

        //echo "dql=".$dql."<br>";

        $em = $this->getDoctrine()->getManager();
        $query = $em->createQuery($dql);    //->setParameter('now', date("Y-m-d", time()));

        if( $limitFlag ) {
            $limit = 1000;
            $paginator  = $this->get('knp_paginator');
            $pagination = $paginator->paginate(
                $query,
                $this->get('request')->query->get('page', 1), /*page number*/
                $limit/*limit per page*/
            );
        } else {
            $pagination = $query->getResult();
        }

        return array(
            'entities' => $pagination,
            'roles' => $rolesArr
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
        $criteriastr .= "administrativeDepartment.name LIKE '%".$search."%' OR ";
        //division
        $criteriastr .= "administrativeDivision.name LIKE '%".$search."%' OR ";
        //service
        $criteriastr .= "administrativeService.name LIKE '%".$search."%' OR ";
        $criteriastr .= "administrativeName.name LIKE '%".$search."%' OR ";


        //////////////////// academic appointment title
        //institution
        $criteriastr .= "appointmentInstitution.name LIKE '%".$search."%' OR ";
        //department
        $criteriastr .= "appointmentDepartment.name LIKE '%".$search."%' OR ";
        //division
        $criteriastr .= "appointmentDivision.name LIKE '%".$search."%' OR ";
        //service
        $criteriastr .= "appointmentService.name LIKE '%".$search."%' OR ";
        $criteriastr .= "appointmentName.name LIKE '%".$search."%' OR ";


        //////////////////// medical appointment title
        //institution
        $criteriastr .= "medicalInstitution.name LIKE '%".$search."%' OR ";
        //department
        $criteriastr .= "medicalDepartment.name LIKE '%".$search."%' OR ";
        //division
        $criteriastr .= "medicalDivision.name LIKE '%".$search."%' OR ";
        //service
        $criteriastr .= "medicalService.name LIKE '%".$search."%' OR ";
        $criteriastr .= "medicalName.name LIKE '%".$search."%' OR ";


        //Associated NYPH Code in Locations
        //$criteriastr .= "locations.associatedCode LIKE '%".$search."%' OR ";
        $criteriastr .= "locations.associatedCode='".$search."' OR ";

        if( $this->get('security.context')->isGranted('ROLE_PLATFORM_DEPUTY_ADMIN') ) {
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
        $criteriastr .= " appointmentTitles.position LIKE '%".$search."%' ";

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


    public function getCriteriaStrByFilter( $dql, $filter, $inputCriteriastr ) {

        $criteriastr = "";

        //$curdate = date("Y-m-d", time());

        //Pending Administrative Review
        if( $filter && $filter == "Pending Administrative Review" ) {
            $pendingStatus = BaseUserAttributes::STATUS_UNVERIFIED;
            $criteriastr .= "(".
                "administrativeTitles.status = ".$pendingStatus.
                " OR appointmentTitles.status = ".$pendingStatus.
                " OR medicalTitles.status = ".$pendingStatus.
                " OR locations.status = ".$pendingStatus.
            ")";
        }

        //WCMC + Pathology
        if( $filter && $filter == "WCMC Pathology Employees" ) {
            $criteriastr .= "(".
                "administrativeInstitution.name = 'Weill Cornell Medical College'".
                " OR appointmentInstitution.name = 'Weill Cornell Medical College'".
                " OR medicalInstitution.name = 'Weill Cornell Medical College'".
            ")";
            $criteriastr .= " AND ";
            $criteriastr .= "(".
                "administrativeDepartment.name = 'Pathology and Laboratory Medicine'".
                " OR appointmentDepartment.name = 'Pathology and Laboratory Medicine'".
                " OR medicalDepartment.name = 'Pathology and Laboratory Medicine'".
            ")";
        }

        //Academic Appointment Title exists + Clinical Faculty + Research Faculty
        if( $filter && $filter == "WCMC Pathology Faculty" ) {
            $criteriastr .= "(appointmentInstitution.name = 'Weill Cornell Medical College')";
            $criteriastr .= " AND ";
            $criteriastr .= "(appointmentDepartment.name = 'Pathology and Laboratory Medicine')";
            $criteriastr .= " AND ";
            $criteriastr .= "(appointmentTitles.position = 'Clinical Faculty' OR appointmentTitles.position = 'Research Faculty')";
        }

        //Academic Appointment Title exists + Clinical Faculty
        if( $filter && $filter == "WCMC Pathology Clinical Faculty" ) {
            $criteriastr .= "(appointmentInstitution.name = 'Weill Cornell Medical College')";
            $criteriastr .= " AND ";
            $criteriastr .= "(appointmentDepartment.name = 'Pathology and Laboratory Medicine')";
            $criteriastr .= " AND ";
            $criteriastr .= "(appointmentTitles.position = 'Clinical Faculty')";
        }

        //list all people with MD, MBBS, and DO degrees (using all current synonym links) and only with Administrative or Academic title in institution "WCMC" and department of "Pathology"
        if( $filter && $filter == "WCMC Pathology Physicians" ) {
            $dql->leftJoin("user.trainings", "trainings");
            $dql->leftJoin("trainings.degree", "degree");
            $dql->leftJoin("degree.original", "original");
            $criteriastr .= "(administrativeInstitution.name = 'Weill Cornell Medical College' OR appointmentInstitution.name = 'Weill Cornell Medical College' OR medicalInstitution.name = 'Weill Cornell Medical College')";
            $criteriastr .= " AND ";
            $criteriastr .= "(administrativeDepartment.name = 'Pathology and Laboratory Medicine' OR appointmentDepartment.name = 'Pathology and Laboratory Medicine' OR medicalDepartment.name = 'Pathology and Laboratory Medicine')";
            $criteriastr .= " AND ";
            $criteriastr .= "(original.name = 'MD')";
        }

        //Academic Appointment Title exists + Research Faculty
        if( $filter && $filter == "WCMC Pathology Research Faculty" ) {
            $criteriastr .= "(appointmentInstitution.name = 'Weill Cornell Medical College')";
            $criteriastr .= " AND ";
            $criteriastr .= "(appointmentDepartment.name = 'Pathology and Laboratory Medicine')";
            $criteriastr .= " AND ";
            $criteriastr .= "(appointmentTitles.position = 'Research Faculty')";
        }

        //Academic Appointment Title not exists + Admin Title exists
        if( $filter && $filter == "WCMC Pathology Staff" ) {
            $criteriastr .= "(appointmentInstitution IS NULL AND appointmentDepartment IS NULL)";
            $criteriastr .= " AND ";
            $criteriastr .= "(administrativeInstitution.name = 'Weill Cornell Medical College')";
            $criteriastr .= " AND ";
            $criteriastr .= "(administrativeDepartment.name = 'Pathology and Laboratory Medicine')";
        }

        //Academic Appointment Title not exists + Admin Title exists
        if( $filter && $filter == "NYP Pathology Staff" ) {
            $criteriastr .= "(appointmentInstitution IS NULL AND appointmentDepartment IS NULL)";
            $criteriastr .= " AND ";
            $criteriastr .= "(administrativeInstitution.name = 'New York Hospital')";
            $criteriastr .= " AND ";
            $criteriastr .= "(administrativeDepartment.name = 'Pathology')";
        }

        //Academic Appointment Title exists + division=Anatomic Pathology
        if( $filter && $filter == "WCMC Anatomic Pathology Faculty" ) {
            $criteriastr .= "(appointmentInstitution.name = 'Weill Cornell Medical College')";
            $criteriastr .= " AND ";
            $criteriastr .= "(appointmentDepartment.name = 'Pathology and Laboratory Medicine')";
            $criteriastr .= " AND ";
            $criteriastr .= "(appointmentDivision.name = 'Anatomic Pathology')";
        }

        //Academic Appointment Title exists + division=Laboratory Medicine
        if( $filter && $filter == "WCMC Laboratory Medicine Faculty" ) {
            $criteriastr .= "(appointmentInstitution.name = 'Weill Cornell Medical College')";
            $criteriastr .= " AND ";
            $criteriastr .= "(appointmentDepartment.name = 'Pathology and Laboratory Medicine')";
            $criteriastr .= " AND ";
            $criteriastr .= "(appointmentDivision.name = 'Laboratory Medicine')";
        }

        //As Faculty + Residents == Academic Appointment Title exists + position=Fellow
        if( $filter && $filter == "WCMC Pathology Fellows" ) {
            $criteriastr .= "(appointmentInstitution.name = 'Weill Cornell Medical College')";
            $criteriastr .= " AND ";
            $criteriastr .= "(appointmentDepartment.name = 'Pathology and Laboratory Medicine')";
            $criteriastr .= " AND ";
            $criteriastr .= "(appointmentTitles.position = 'Fellow')";
        }

        //As Faculty + Residents == Academic Appointment Title exists + position=Resident
        if( $filter && $filter == "WCMC Pathology Residents" ) {
            $criteriastr .= "(appointmentInstitution.name = 'Weill Cornell Medical College')";
            $criteriastr .= " AND ";
            $criteriastr .= "(appointmentDepartment.name = 'Pathology and Laboratory Medicine')";
            $criteriastr .= " AND ";
            $criteriastr .= "(appointmentTitles.position = 'Resident')";
        }

        //the same as "WCMC Pathology Residents" except they have "AP/CP" in their "Residency Type" field.
        if( $filter && $filter == "WCMC AP/CP Residents" ) {
            $criteriastr .= "(appointmentInstitution.name = 'Weill Cornell Medical College')";
            $criteriastr .= " AND ";
            $criteriastr .= "(appointmentDepartment.name = 'Pathology and Laboratory Medicine')";
            $criteriastr .= " AND ";
            $criteriastr .= "(appointmentTitles.position = 'Resident')";
            $dql->leftJoin("appointmentTitles.residencyTrack", "residencyTrack");
            $criteriastr .= " AND ";
            $criteriastr .= "(residencyTrack.name = 'AP/CP')";
        }

        //the same as "WCMC Pathology Residents" except they have "AP" or "AP/CP" in their "Residency Type" field.
        if( $filter && $filter == "WCMC AP Residents" ) {
            $criteriastr .= "(appointmentInstitution.name = 'Weill Cornell Medical College')";
            $criteriastr .= " AND ";
            $criteriastr .= "(appointmentDepartment.name = 'Pathology and Laboratory Medicine')";
            $criteriastr .= " AND ";
            $criteriastr .= "(appointmentTitles.position = 'Resident')";
            $dql->leftJoin("appointmentTitles.residencyTrack", "residencyTrack");
            $criteriastr .= " AND ";
            $criteriastr .= "(residencyTrack.name = 'AP' OR residencyTrack.name = 'AP/CP')";
        }

        //the same as "WCMC Pathology Residents" except they have "AP" in their "Residency Type" field.
        if( $filter && $filter == "WCMC AP Only Residents" ) {
            $criteriastr .= "(appointmentInstitution.name = 'Weill Cornell Medical College')";
            $criteriastr .= " AND ";
            $criteriastr .= "(appointmentDepartment.name = 'Pathology and Laboratory Medicine')";
            $criteriastr .= " AND ";
            $criteriastr .= "(appointmentTitles.position = 'Resident')";
            $dql->leftJoin("appointmentTitles.residencyTrack", "residencyTrack");
            $criteriastr .= " AND ";
            $criteriastr .= "(residencyTrack.name = 'AP')";
        }

        //the same as "WCMC Pathology Residents" except they have "CP" or "AP/CP" in their "Residency Type" field.
        if( $filter && $filter == "WCMC CP Residents" ) {
            $criteriastr .= "(appointmentInstitution.name = 'Weill Cornell Medical College')";
            $criteriastr .= " AND ";
            $criteriastr .= "(appointmentDepartment.name = 'Pathology and Laboratory Medicine')";
            $criteriastr .= " AND ";
            $criteriastr .= "(appointmentTitles.position = 'Resident')";
            $dql->leftJoin("appointmentTitles.residencyTrack", "residencyTrack");
            $criteriastr .= " AND ";
            $criteriastr .= "(residencyTrack.name = 'CP' OR residencyTrack.name = 'AP/CP')";
        }

        //the same as "WCMC Pathology Residents" except they have "CP" in their "Residency Type" field.
        if( $filter && $filter == "WCMC CP Only Residents" ) {
            $criteriastr .= "(appointmentInstitution.name = 'Weill Cornell Medical College')";
            $criteriastr .= " AND ";
            $criteriastr .= "(appointmentDepartment.name = 'Pathology and Laboratory Medicine')";
            $criteriastr .= " AND ";
            $criteriastr .= "(appointmentTitles.position = 'Resident')";
            $dql->leftJoin("appointmentTitles.residencyTrack", "residencyTrack");
            $criteriastr .= " AND ";
            $criteriastr .= "(residencyTrack.name = 'CP')";
        }

        // the same as "WCMC Pathology Faculty" except they have at least one non-empty "Research Lab Title:" + a checkmark in
        //"Principal Investigator of this Lab:" with an empty or future "Dissolved on: [Date]" for Current / past or empty or future "Dissolved on: [Date]" for Previous
        if( $filter && $filter == "WCMC Pathology Principal Investigators of Research Labs" ) {
            $criteriastr .= "(appointmentInstitution.name = 'Weill Cornell Medical College')";
            $criteriastr .= " AND ";
            $criteriastr .= "(appointmentDepartment.name = 'Pathology and Laboratory Medicine')";
            $criteriastr .= " AND ";
            $criteriastr .= "(appointmentTitles.position = 'Clinical Faculty' OR appointmentTitles.position = 'Research Faculty')";

            //a checkmark in "Principal Investigator of this Lab:"
            $criteriastr .= " AND ";
            $criteriastr .= "(researchLabs.researchPI = 1)";
        }

        // "WCMC Pathology Faculty in Research Labs" - the same as "WCMC Pathology Faculty"
        //except they have at least one non-empty "Research Lab Title:" with an empty or future "Dissolved on: [Date]" for Current / past or empty or future "Dissolved on: [Date]" for Previous
        if( $filter && $filter == "WCMC Pathology Faculty in Research Labs" ) {
            $criteriastr .= "(appointmentInstitution.name = 'Weill Cornell Medical College')";
            $criteriastr .= " AND ";
            $criteriastr .= "(appointmentDepartment.name = 'Pathology and Laboratory Medicine')";
            $criteriastr .= " AND ";
            $criteriastr .= "(appointmentTitles.position = 'Clinical Faculty' OR appointmentTitles.position = 'Research Faculty')";
        }


        // "WCMC or NYP Pathology Staff in Research Labs" - the same as "WCMC Pathology Staff" OR "NYP Pathology Staff"
        //except they have at least one non-empty "Research Lab Title:" with an empty or future "Dissolved on: [Date]" for Current / past or empty or future "Dissolved on: [Date]" for Previous
        if( $filter && $filter == "WCMC or NYP Pathology Staff in Research Labs" ) {
            $criteriastr .= "(appointmentInstitution IS NULL AND appointmentDepartment IS NULL)";
            $criteriastr .= " AND (";
            $criteriastr .= "administrativeInstitution.name = 'Weill Cornell Medical College' AND administrativeDepartment.name = 'Pathology and Laboratory Medicine'";
            $criteriastr .= " OR ";
            $criteriastr .= "administrativeInstitution.name = 'New York Hospital' AND administrativeDepartment.name = 'Pathology'";
            $criteriastr .= ") ";
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


//    public function getMyTeam( $dql, $myteam, $myboss, $inputCriteriastr ) {
//
//        $user = $this->get('security.context')->getToken()->getUser();
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


    public function getTheSameObject( $dql, $objectname, $objectid, $excludeCurrentUser, $inputCriteriastr ) {

        //echo "objectname=".$objectname.", objectid=".$objectid."<br>";
        //exit();

        $user = $this->get('security.context')->getToken()->getUser();

        $criteriastr = "";

        if( $objectname && $objectname == "institution" ) {
            if( !$objectid || $objectid != "" ) {
                $criteriastr .= "administrativeInstitution.id = " . $objectid;
                $criteriastr .= " OR ";
                $criteriastr .= "appointmentInstitution.id = " . $objectid;
            } else {
                $criteriastr = "1=0";
            }
        }

        if( $objectname && $objectname == "service" ) {
            if( !$objectid || $objectid != "" ) {
                $criteriastr .= "administrativeService.id = " . $objectid;
                $criteriastr .= " OR ";
                $criteriastr .= "appointmentService.id = " . $objectid;
                $criteriastr .= " OR ";
                $criteriastr .= "medicalService.id = " . $objectid;
            } else {
                $criteriastr = "1=0";
            }
        }

        if( $objectname && $objectname == "administrativeTitle" ) {
            if( !$objectid || $objectid != "" ) {
                $criteriastr .= "administrativeTitles.name = '" . $objectid . "'";
            } else {
                $criteriastr = "1=0";
            }
        }

        if( $objectname && $objectname == "appointmentTitle" ) {
            if( !$objectid || $objectid != "" ) {
                $criteriastr .= "appointmentTitles.name = '" . $objectid . "'";
            } else {
                $criteriastr = "1=0";
            }
        }

        if( $objectname && $objectname == "medicalTitle" ) {
            if( !$objectid || $objectid != "" ) {
                $criteriastr .= "medicalTitles.name = '" . $objectid . "'";
            } else {
                $criteriastr = "1=0";
            }
        }

        if( $objectname && $objectname == "room" ) {
            if( !$objectid || $objectid != "" ) {
                $criteriastr .= "locations.room = '" . $objectid . "'";
            } else {
                $criteriastr = "1=0";
            }
        }

        if( $objectname && $objectname == "department" ) {
            if( !$objectid || $objectid != "" ) {
                $criteriastr .= "administrativeDepartment.id = " . $objectid;
                $criteriastr .= " OR ";
                $criteriastr .= "appointmentDepartment.id = " . $objectid;
                $criteriastr .= " OR ";
                $criteriastr .= "medicalDepartment.id = " . $objectid;
            } else {
                $criteriastr = "1=0";
            }
        }

        if( $objectname && $objectname == "division" ) {
            if( !$objectid || $objectid != "" ) {
                $criteriastr .= "administrativeDivision.id = " . $objectid;
                $criteriastr .= " OR ";
                $criteriastr .= "appointmentDivision.id = " . $objectid;
                $criteriastr .= " OR ";
                $criteriastr .= "medicalDivision.id = " . $objectid;
            } else {
                $criteriastr = "1=0";
            }
        }

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
        $pending = null;

        if( false === $this->get('security.context')->isGranted('ROLE_USERDIRECTORY_EDITOR') ) {
            $response = new Response();
            $response->setContent($pending);
            return $response;
        }

        $limitFlag = false;

        //$filter=null, $time='all', $limitFlag=true, $search=null, $userid=null
        $params = array('filter'=>'Pending Administrative Review','time'=>'current_only','limitFlag'=>$limitFlag);
        $res = $this->indexUser( $params );

        $pending = count($res['entities']);

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

        if( false === $this->get('security.context')->isGranted('ROLE_USERDIRECTORY_EDITOR') ) {
            return $this->redirect( $this->generateUrl('employees-order-nopermission') );
        }

        $em = $this->getDoctrine()->getManager();

        //echo "user id=".$id."<br>";
        //exit();

        $userManager = $this->container->get('fos_user.user_manager');
        $user = $userManager->createUser();

        $creator = $this->get('security.context')->getToken()->getUser();
        $userUtil = new UserUtil();
        $user = $userUtil->addDefaultLocations($user,$creator,$em,$this->container);

        $userSecUtil = $this->get('user_security_utility');
        $userkeytype = $userSecUtil->getDefaultUsernameType();
        $user->setKeytype($userkeytype);

        //clone user
        $subjectUser = null;
        if( $id && $id != "" ) {
            $subjectUser = $em->getRepository('OlegUserdirectoryBundle:User')->find($id);
            $userUtil = new UserUtil();
            $user = $userUtil->makeUserClone($subjectUser,$user);
        }

        $this->addEmptyCollections($user);

        //add EIN identifier to credentials
        $identEin = new Identifier();
        $identKeytypeEin = $em->getRepository('OlegUserdirectoryBundle:IdentifierTypeList')->findOneByName("WCMC Employee Identification Number (EIN)");
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
        $rolesArr = $this->getUserRoles();

        $params = array(
            'cycle' => 'create',
            'user' => $user,
            'cloneuser' => $subjectUser,
            'roles' => $rolesArr,
            'sc' => $this->get('security.context'),
            'em' => $em
        );

        $form = $this->createForm(new UserType($params), $user, array(
            'disabled' => false,
            'action' => $this->generateUrl( $this->container->getParameter('employees.sitename').'_create_user' ),
            'method' => 'POST',
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
     * @Route("/user/new", name="employees_create_user")
     * @Method("POST")
     * @Template("OlegUserdirectoryBundle:Profile:register.html.twig")
     */
    public function createUserAction( Request $request )
    {
        return $this->createUser($request);
    }
    public function createUser($request) {

        if( false === $this->get('security.context')->isGranted('ROLE_USERDIRECTORY_EDITOR') ) {
            return $this->redirect( $this->generateUrl('employees-order-nopermission') );
        }

        $em = $this->getDoctrine()->getManager();

        //$user = new User();
        $userManager = $this->container->get('fos_user.user_manager');
        $user = $userManager->createUser();

        $user->setPassword("");
        $user->setCreatedby('manual');

        //$this->addEmptyCollections($user);

        //Roles
        $rolesArr = $this->getUserRoles();

        $params = array(
            'cycle' => 'create',
            'user' => $user,
            'cloneuser' => null,
            'roles' => $rolesArr,
            'sc' => $this->get('security.context'),
            'em' => $em
        );

        $form = $this->createForm(new UserType($params), $user, array('disabled' => false));

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

//        echo "loc errors:<br>";
//        print_r($form->getErrors());
//        echo "<br>loc string errors:<br>";
//        print_r($form->getErrorsAsString());
//        echo "<br>";
        //exit();

        if( $form->isValid() ) {

            $user->setEnabled(true);
            $user->setCreatedby('manual');

            //set unique username
            $user->setUniqueUsername();

            //set parents for institution tree for Administrative and Academical Titles
            $this->setParentsForInstitutionTree($user);

            //set parents for institution tree for Administrative and Academical Titles
            $this->setParentsForCommentTypeTree($user);

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
            $userAdmin = $this->get('security.context')->getToken()->getUser();
            $event = "User ".$user." has been created by ".$userAdmin."<br>";
            $userSecUtil = $this->get('user_security_utility');
            $userSecUtil->createUserEditEvent($this->container->getParameter('employees.sitename'),$event,$userAdmin,$user,$request,'User Created');

            return $this->redirect($this->generateUrl($this->container->getParameter('employees.sitename').'_showuser',array('id' => $user->getId())));
        }

        return array(
            'entity' => $user,
            'form' => $form->createView(),
            'cycle' => 'create_user',
            'user_id' => '',
            'sitename' => $this->container->getParameter('employees.sitename')
        );
    }

    protected function getEngine()
    {
        return $this->container->getParameter('fos_user.template.engine');
    }
    ////////////////////// EOF Create New User //////////////////////





    /**
     * @Route("/user/show/ee", name="employees_showuser_notstrict")
     * @Route("/user/{id}", name="employees_showuser", requirements={"id" = "\d+"})
     * @Method("GET")
     * @Template("OlegUserdirectoryBundle:Profile:edit_user.html.twig")
     */
    public function showUserAction($id)
    {
        //$secUtil = $this->get('user_security_utility');
        if( false === $this->get('security.context')->isGranted('ROLE_USER') ) { //!$secUtil->isCurrentUser($id) &&
            return $this->redirect( $this->generateUrl('employees-order-nopermission') );
        }

        return $this->showUser($id,$this->container->getParameter('employees.sitename'));
    }
    public function showUser($id, $sitename=null) {

        $request = $this->container->get('request');
        $em = $this->getDoctrine()->getManager();

        //echo "id=".$id."<br>";

        if( $id == 0 || $id == '' || $id == '' ) {
            $entity = new User();
        } else {
            $entity = $em->getRepository('OlegUserdirectoryBundle:User')->find($id);
        }

        if( !$entity ) {
            throw $this->createNotFoundException('Unable to find User entity.');
        }

        $this->addEmptyCollections($entity);

        $this->addHookFields($entity);

        //Roles
        $rolesArr = $this->getUserRoles();

        $params = array(
            'cycle' => 'show',
            'user' => $entity,
            'cloneuser' => null,
            'roles' => $rolesArr,
            'sc' => $this->get('security.context'),
            'em' => $em
        );

        $form = $this->createForm(new UserType($params), $entity, array('disabled' => true));

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

        //return $this->container->get('templating')->renderResponse('FOSUserBundle:Profile:show.html.'.$this->container->getParameter('fos_user.template.engine'), array('user' => $user));
        return array(
            'entity' => $entity,
            'form' => $form->createView(),
            'cycle' => 'show_user',
            'user_id' => $id,
            'sitename' => $sitename,
            'roleobjects' => $roleobjects,
            'postData' => $request->query->all(),
            'title' => 'Employee Profile ' . $entity->getUsernameOptimal()
        );
    }

    /**
     * @Route("/edit-user-profile/{id}", name="employees_user_edit", requirements={"id" = "\d+"})
     * @Method("GET")
     * @Template("OlegUserdirectoryBundle:Profile:edit_user.html.twig")
     */
    public function editUserAction($id)
    {
        $secUtil = $this->get('user_security_utility');
        if( !$secUtil->isCurrentUser($id) && false === $this->get('security.context')->isGranted('ROLE_USERDIRECTORY_EDITOR') ) {
            return $this->redirect( $this->generateUrl('employees-order-nopermission') );
        }

        return $this->editUser($id, $this->container->getParameter('employees.sitename'));
    }

    public function editUser($id,$sitename=null) {

        $request = $this->container->get('request');

        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('OlegUserdirectoryBundle:User')->find($id);

        if( !$entity ) {
            throw $this->createNotFoundException('Unable to find User entity.');
        }

        $this->addEmptyCollections($entity);

        $this->addHookFields($entity);

        //Roles
        $rolesArr = $this->getUserRoles();

        $params = array(
            'cycle' => 'edit',
            'user' => $entity,
            'cloneuser' => null,
            'roles' => $rolesArr,
            'sc' => $this->get('security.context'),
            'em' => $em
        );

        $form = $this->createForm(new UserType($params), $entity, array(
            'action' => $this->generateUrl($sitename.'_user_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));
//        $form->add('submit', 'submit', array('label' => 'Update','attr' => array('class' => 'btn btn-warning')));

        return array(
            'entity' => $entity,
            'form' => $form->createView(),
            'cycle' => 'edit_user',
            'user_id' => $id,
            'sitename' => $sitename,
            'postData' => $request->query->all(),
            'title' => 'Edit Employee Profile for ' . $entity->getUsernameOptimal()
        );
    }

    //create empty collections
    public function addEmptyCollections($entity) {

        $user = $this->get('security.context')->getToken()->getUser();

        if( count($entity->getAdministrativeTitles()) == 0 ) {
            $administrativeTitle = new AdministrativeTitle($user);
            $entity->addAdministrativeTitle($administrativeTitle);
        }

        if( count($entity->getAppointmentTitles()) == 0 ) {
            $appointmentTitle = new AppointmentTitle($user);
            $entity->addAppointmentTitle($appointmentTitle);
            //echo "app added, type=".$appointmentTitle->getType()."<br>";
        }

        if( count($entity->getMedicalTitles()) == 0 ) {
            $medicalTitle = new MedicalTitle($user);
            $entity->addMedicalTitle($medicalTitle);
        }

        if( count($entity->getCredentials()->getStateLicense()) == 0 ) {
            $entity->getCredentials()->addStateLicense( new StateLicense() );
        }

        if( count($entity->getCredentials()->getBoardCertification()) == 0 ) {
            $entity->getCredentials()->addBoardCertification( new BoardCertification() );
        }

        if( count($entity->getEmploymentStatus()) == 0 ) {
            $employmentStatus = new EmploymentStatus($user);
            $entity->addEmploymentStatus($employmentStatus);
        }

        //create new comments
        if( count($entity->getPublicComments()) == 0 ) {
            $entity->addPublicComment( new PublicComment($user) );
        }
        if( $this->get('security.context')->isGranted('ROLE_PLATFORM_DEPUTY_ADMIN') || $this->get('security.context')->isGranted('ROLE_USERDIRECTORY_EDITOR') ||
            $entity->getId() && $entity->getId() == $user->getId()
        ) {
            if( count($entity->getPrivateComments()) == 0 ) {
                $entity->addPrivateComment( new PrivateComment($user) );
            }
        }
        if( $this->get('security.context')->isGranted('ROLE_PLATFORM_DEPUTY_ADMIN') || $this->get('security.context')->isGranted('ROLE_USERDIRECTORY_EDITOR') ) {
            if( count($entity->getAdminComments()) == 0 ) {
                $entity->addAdminComment( new AdminComment($user) );
            }
        }
        if( $this->get('security.context')->isGranted('ROLE_PLATFORM_DEPUTY_ADMIN') || $this->get('security.context')->isGranted('ROLE_USERDIRECTORY_EDITOR') ) {
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

        if( count($entity->getTrainings()) == 0 ) {
            $entity->addTraining(new Training($user));
        }

        //Identifier EIN
//        if( count($entity->getCredentials()->getIdentifiers()) == 0 ) {
//            $entity->getCredentials()->addIdentifier( new Identifier() );
//        }

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
        if( !$secUtil->isCurrentUser($id) && false === $this->get('security.context')->isGranted('ROLE_USERDIRECTORY_EDITOR') ) {
            return $this->redirect( $this->generateUrl('employees-order-nopermission') );
        }

        return $this->updateUser( $request, $id, $this->container->getParameter('employees.sitename') );
    }
    public function updateUser(Request $request, $id, $sitename)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('OlegUserdirectoryBundle:User')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find User entity.');
        }

        //$this->addEmptyCollections($entity);

        //$this->addHookFields($entity);

//        $entity->setPreferredPhone('111222333');
//        $uow = $em->getUnitOfWork();
//        $uow->computeChangeSets(); // do not compute changes if inside a listener
//        $changeset = $uow->getEntityChangeSet($entity);
//        print_r($changeset);
        //exit('edit user');

        //$oldEntity = clone $entity;
        //$oldUserArr = get_object_vars($oldEntity);

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

        //echo "count=".count($originalAdminTitles)."<br>";
        //exit();

        //Roles
        $rolesArr = $this->getUserRoles();

        $params = array(
            'cycle' => 'edit',
            'user' => $entity,
            'cloneuser' => null,
            'roles' => $rolesArr,
            'sc' => $this->get('security.context'),
            'em' => $em
        );

        $form = $this->createForm(new UserType($params), $entity, array(
            'action' => $this->generateUrl($sitename.'_user_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));
        //$form->add('submit', 'submit', array('label' => 'Update'));

        $form->handleRequest($request);


//        if( $form->isValid() ) {
//            echo "form is valid <br>";
//        } else {
//            echo "form has error <br>";
//        }



        if( $form->isValid() ) {

            //echo "form is valid<br>";
            //exit();

            //check if changed roles are "Platform Administrator" or "Deputy Platform Administrator"
            $currRoles = $entity->getRoles();
            $resultRoles = $this->array_diff_assoc_true($currRoles,$originalRoles);

            //check 1: if the roles are changed by non admin user
            if( count($resultRoles) > 0 ) {
                if( false === $this->get('security.context')->isGranted('ROLE_PLATFORM_DEPUTY_ADMIN') &&
                    false === $this->get('security.context')->isGranted('ROLE_USERDIRECTORY_ADMIN') &&
                    false === $this->get('security.context')->isGranted('ROLE_SCANORDER_ADMIN')
                ) {
                    $this->setSessionForbiddenNote("Change Role(s) ".join(",",$resultRoles));
                    //throw new ForbiddenOverwriteException("You do not have permission to perform this operation: Change Role ".$role);
                    return $this->redirect( $this->generateUrl($sitename.'_user_edit',array('id'=>$id)) );
                }
            }
            //check 2: if the roles "Platform Administrator" or "Deputy Platform Administrator" are changed by non super admin user
            foreach( $resultRoles as $role ) {
                if( $role == "ROLE_PLATFORM_DEPUTY_ADMIN" || $role == "ROLE_PLATFORM_ADMIN" ) {
                    if( false === $this->get('security.context')->isGranted('ROLE_PLATFORM_ADMIN') ) {
                        $this->setSessionForbiddenNote("Change Role ".$role);
                        //throw new ForbiddenOverwriteException("You do not have permission to perform this operation: Change Role ".$role);
                        return $this->redirect( $this->generateUrl($sitename.'_user_edit',array('id'=>$id)) );
                    }
                }
            }

            //exit('before processing');

            //set parents for institution tree for Administrative and Academical Titles
            $this->setParentsForInstitutionTree($entity);

            //set parents for institution tree for Administrative and Academical Titles
            $this->setParentsForCommentTypeTree($entity);

            //set parents for residencySpecialty tree for Trainings
            $this->setParentsForresidencySpecialtyTree($entity);

            //set avatar
            $this->processSetAvatar($entity);

            //process research labs
            $entity = $em->getRepository('OlegUserdirectoryBundle:ResearchLab')->processResearchLab( $entity );

            //process grants
            $em->getRepository('OlegUserdirectoryBundle:Grant')->processGrant($entity);

            //process employmentstatus attachments
            $this->processEmploymentStatus($entity);

            //set update info for user
            $this->updateInfo($entity);

            /////////////// Add event log on edit (edit or add collection) ///////////////
            /////////////// Must run before removeCollection() function which flash DB. When DB is flashed getEntityChangeSet() will not work ///////////////
            $changedInfoArr = $this->setEventLogChanges($entity);

            /////////////// Process Removed Collections ///////////////
            $removedCollections = array();

            $removedInfo = $this->removeCollection($originalAdminTitles,$entity->getAdministrativeTitles());
            if( $removedInfo ) {
                $removedCollections[] = $removedInfo;
            }

            $removedInfo = $this->removeCollection($originalAppTitles,$entity->getAppointmentTitles());
            if( $removedInfo ) {
                $removedCollections[] = $removedInfo;
            }

            $removedInfo = $this->removeCollection($originalMedicalTitles,$entity->getMedicalTitles());
            if( $removedInfo ) {
                $removedCollections[] = $removedInfo;
            }

            $removedInfo = $this->removeCollection($originalLocations,$entity->getLocations());
            if( $removedInfo ) {
                $removedCollections[] = $removedInfo;
            }

            $removedInfo = $this->removeCollection($originalTrainings,$entity->getTrainings());
            if( $removedInfo ) {
                $removedCollections[] = $removedInfo;
            }

            //check for removed collection for Credentials: identifiers, stateLicense, boardCertification, codeNYPH
            $removedInfo = $this->removeCollection($originalIdentifiers,$entity->getCredentials()->getIdentifiers());
            if( $removedInfo ) {
                $removedCollections[] = $removedInfo;
            }

            $removedInfo = $this->removeCollection($originalStateLicense,$entity->getCredentials()->getStateLicense());
            if( $removedInfo ) {
                $removedCollections[] = $removedInfo;
            }

            $removedInfo = $this->removeCollection($originalBoardCertification,$entity->getCredentials()->getBoardCertification());
            if( $removedInfo ) {
                $removedCollections[] = $removedInfo;
            }

            $removedInfo = $this->removeCollection($originalCodeNYPH,$entity->getCredentials()->getCodeNYPH());
            if( $removedInfo ) {
                $removedCollections[] = $removedInfo;
            }
            //eof removed collection for Credentials

            $removedEmplStatus = $this->removeCollection($originalEmplStatus,$entity->getEmploymentStatus());
            if( $removedEmplStatus ) {
                $removedCollections[] = $removedEmplStatus;
            }

            $removedInfo = $this->removeCollection($originalPublicComments,$entity->getPublicComments());
            if( $removedInfo ) {
                $removedCollections[] = $removedInfo;
            }
            $removedInfo = $this->removeCollection($originalPrivateComments,$entity->getPrivateComments());
            if( $removedInfo ) {
                $removedCollections[] = $removedInfo;
            }
            $removedInfo = $this->removeCollection($originalAdminComments,$entity->getAdminComments());
            if( $removedInfo ) {
                $removedCollections[] = $removedInfo;
            }
            $removedInfo = $this->removeCollection($originalConfidentialComments,$entity->getConfidentialComments());
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
            /////////////// EOF Process Removed Collections ///////////////

            //set Edit event log for removed collection and changed fields or added collection
            if( count($changedInfoArr) > 0 || count($removedCollections) > 0 ) {
                $user = $this->get('security.context')->getToken()->getUser();
                $event = "User information of ".$entity." has been changed by ".$user.":"."<br>";
                $event = $event . implode("<br>", $changedInfoArr);
                $event = $event . "<br>" . implode("<br>", $removedCollections);
                $userSecUtil = $this->get('user_security_utility');
                $userSecUtil->createUserEditEvent($sitename,$event,$user,$entity,$request);
            }

            //echo "user=".$entity."<br>";

            echo "employmentStatus=".$entity->getEmploymentStatus()->first()."<br>";

            //exit('user exit');

            //$em->persist($entity);
            $em->flush($entity);

            //delete old avatar document from DB
            $this->processDeleteOldAvatar($entity,$oldAvatarId);

            //redirect only if this was called by the same controller class
            if( $sitename == $this->container->getParameter('employees.sitename') ) {
                return $this->redirect($this->generateUrl($sitename.'_showuser', array('id' => $id)));
            }
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
        //$user = $this->get('security.context')->getToken()->getUser();

        $em = $this->getDoctrine()->getManager();
        $sc = $this->get('security.context');
        $userUtil = new UserUtil();

        //Administartive and Appointment Titles and Comments update info set when parent are processed
        //So, set author info for the rest: EmploymentStatus, Location, Credentials, ResearchLab
        foreach( $subjectUser->getEmploymentStatus() as $entity ) {
            $userUtil->setUpdateInfo($entity,$em,$sc);
        }

        foreach( $subjectUser->getLocations() as $entity ) {
            $userUtil->setUpdateInfo($entity,$em,$sc);
            $userUtil->setUpdateInfo($entity->getBuilding(),$em,$sc);
        }

        //credentials
        $userUtil->setUpdateInfo($subjectUser->getCredentials(),$em,$sc);

        foreach( $subjectUser->getResearchLabs() as $entity ) {
            $userUtil->setUpdateInfo($entity,$em,$sc);
        }

        foreach( $subjectUser->getGrants() as $entity ) {
            $userUtil->setUpdateInfo($entity,$em,$sc);
        }

    }


//    public function createUserEditEvent($sitename,$event,$user,$subjectEntity,$request,$action='User Updated') {
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
        $sc = $this->get('security.context');
        $userUtil = new UserUtil();

        $educationalType = null;

        foreach( $entity->getTrainings() as $training) {
            $userUtil->processResidencySpecialtyTree($training,$em,$sc);

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

    //Process all holder containing institutional tree
    public function setParentsForInstitutionTree($entity) {

        $em = $this->getDoctrine()->getManager();
        $sc = $this->get('security.context');
        $userUtil = new UserUtil();

        foreach( $entity->getAdministrativeTitles() as $title) {
            $userUtil->processInstTree($title,$em,$sc);
        }
        foreach( $entity->getAppointmentTitles() as $title) {
            $userUtil->processInstTree($title,$em,$sc);
        }
        foreach( $entity->getMedicalTitles() as $title) {
            $userUtil->processInstTree($title,$em,$sc);
        }
        foreach( $entity->getLocations() as $location) {
            $userUtil->processInstTree($location,$em,$sc);
        }
    }

    public function setParentsForCommentTypeTree($entity) {

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

        ///////////////////////// process documents /////////////////////////
        $comment = $em->getRepository('OlegUserdirectoryBundle:Document')->processDocuments( $comment );

        if( $comment == null ) {
            return;
        }

        //set author if not set
        $userUtil = new UserUtil();
        $sc = $this->get('security.context');
        $userUtil->setUpdateInfo($comment,$em,$sc);

        //echo "<br>Comment text=".$comment->getComment()."<br>";

        //if comment text is empty => remove from user
        if( $comment->getComment() == "" && count($comment->getDocuments()) == 0 ) {

            $user = $comment->getUser();

            $comment->setCommentType(null);
            $comment->setCommentSubType(null);

            $fullClassName = new \ReflectionClass($comment);
            $className = $fullClassName->getShortName();

            $removeMethod = "remove".$className;
            //echo "removeMethod=".$removeMethod."<br>";

            $user->$removeMethod($comment);

            return;
        }

        $type = $comment->getCommentType();
        $subtype = $comment->getCommentSubType();

        $user = $this->get('security.context')->getToken()->getUser();
        $author = $em->getRepository('OlegUserdirectoryBundle:User')->find($user->getId());

        $subtype = $em->getRepository('OlegUserdirectoryBundle:CommentSubTypeList')->checkAndSetParent($author,$comment,$type,$subtype);
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
                    //$userUtil->setUpdateInfo($documentContainer,$em,$sc);
                }

            }
        }

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
                if( $title->getRemovable() == false ) {
                    continue;
                }
            }

            //echo "title=".$title.", id=".$title->getId()."<br>";
            $em->persist($title);

            if( false === $currentArr->contains($title) ) {
                $removeArr[] = "<strong>"."Removed: ".$title." ".$this->getEntityId($title)."</strong>";
                //echo "before delete <br>";
                if( is_subclass_of($title, 'Oleg\UserdirectoryBundle\Entity\ListAbstract') === false ) {
                    //echo "delete object entirely <br>";
                    // delete object entirely
                    $em->remove($title);
                    $em->flush();
                } else {
                    //echo 'no delete from DB because list <br>';
                    if( $subjectUser ) {
                        $title->removeUser($subjectUser);

                        if( $title instanceof ResearchLab ) {
                            //remove dependents: remove comments and id from lab
                            $em->getRepository('OlegUserdirectoryBundle:ResearchLab')->removeDependents( $subjectUser, $title );
                        }

                        if( $title instanceof Grant ) {
                            //remove dependents: remove documents
                            $em->getRepository('OlegUserdirectoryBundle:Grant')->removeDependents( $subjectUser, $title );
                        }

                        //TODO: remove documents from comments?
                    }
                }
            } else {
                //echo "no delete <br>";
            }

        } //foreach

        //exit('done remove collection');

        return implode("<br>", $removeArr);
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

        if( false === $this->get('security.context')->isGranted('ROLE_USERDIRECTORY_ADMIN') ) {
            $this->get('session')->getFlashBag()->add(
                'notice',
                'You do not have permission to visit this page'
            );
            return $this->redirect($this->generateUrl('employees-order-nopermission'));
        }

        $default_time_zone = $this->container->getParameter('default_time_zone');

        $userutil = new UserUtil();
        $usersCount = $userutil->generateUsersExcel($this->getDoctrine()->getManager(),$this->container);

        //exit();
        return $this->redirect($this->generateUrl('employees_listusers'));
    }

    public function getUserRoles() {
        $rolesArr = array();
        $em = $this->getDoctrine()->getManager();
        $roles = $em->getRepository('OlegUserdirectoryBundle:Roles')->findBy(array(), array('orderinlist' => 'ASC'));  //findAll();
        foreach( $roles as $role ) {
            $rolesArr[$role->getName()] = $role->getAlias();
        }
        return $rolesArr;
    }


    /**
     * @Route("/lockunlock/change/{id}/{status}", name="employees_lockunlock_change", requirements={"id" = "\d+"})
     * @Method("GET")
     * @Template()
     */
    public function lockUnlockChangeAction($id, $status) {

        if (false === $this->get('security.context')->isGranted('ROLE_USERDIRECTORY_EDITOR')) {
            return $this->redirect( $this->generateUrl('employees-order-nopermission') );
        }

        $this->lockUnlock($id, $status, $this->container->getParameter('employees.sitename'));

        return $this->redirect($this->generateUrl($this->container->getParameter('employees.sitename').'_listusers'));
    }

    public function lockUnlock($id, $status, $sitename) {

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
        $request = $this->container->get('request');
        $userAdmin = $this->get('security.context')->getToken()->getUser();
        $event = "User information of ".$user." has been changed by ".$userAdmin.":"."<br>";
        $event = $event . "User status changed to ".$status;
        $userSecUtil = $this->get('user_security_utility');
        $userSecUtil->createUserEditEvent($sitename,$event,$userAdmin,$user,$request);

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

        return $eventArr;

    }

    public function addChangesToEventLog( $eventArr, $changeset, $text="" ) {

        $changeArr = array();

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
                    $oldValue = implode(",",$oldValue);
                }
                if( is_array($newValue) ) {
                    $newValue = implode(",",$newValue);
                }

                $event = "<strong>".$field.$text."</strong>".": "."old value=".$oldValue.", new value=".$newValue;
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
        if( $entity->getId() ) {
            return "ID=".$entity->getId();
        }

        return "New";
    }

    public function setSessionForbiddenNote($msg) {
        $this->get('session')->getFlashBag()->add(
            'notice',
            "You do not have permission to perform this operation: ".$msg
        );
    }

    function array_diff_assoc_true($array1, $array2)
    {
        $res = array_merge( array_diff_assoc($array1,$array2), array_diff_assoc($array2,$array1) );
        return array_unique($res);
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
        if( !$secUtil->isCurrentUser($userid) && false === $this->get('security.context')->isGranted('ROLE_USERDIRECTORY_EDITOR') ) {
            return $this->redirect( $this->generateUrl('employees-order-nopermission') );
        }

        $uploadPath = "Uploaded/".$this->container->getParameter('employees.avataruploadpath');

        //$baseUrl = $this->container->get('router')->getContext()->getBaseUrl();
        //echo "baseUrl=".$baseUrl." ";

        //class CropAvatar.php new ($src, $data, $file)
        $crop = new CropAvatar($src, $data, $file, $uploadPath);

        $avatarid = NULL;
        $avatarpath = NULL;

        if( !$crop->getMsg() && $crop->getResult() ) {

            //(x86)\Aperio\Spectrum\htdocs\order\scanorder\Scanorders2\src\Oleg\UserdirectoryBundle\Util/../../../../web/Uploaded/directory/Avatars/avatar/20150106205815.jpeg
            $fullnameArr = explode("/", $crop->getResult());
            $uniquefilename = $fullnameArr[count($fullnameArr)-1];
            //echo "uniquefilename=".$uniquefilename." ";

            $size = filesize($crop->getResult());
            //echo "size=".$size." ";

            $uploadDir = $uploadPath . "/" .$crop->getAvatarPostfix();

            $em = $this->getDoctrine()->getManager();

            //document's creator
            $user = $this->get('security.context')->getToken()->getUser();

            $object = new Document($user);
            $object->setOriginalname(NULL);
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

}
