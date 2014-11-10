<?php

namespace Oleg\UserdirectoryBundle\Controller;


use Oleg\UserdirectoryBundle\Form\DataTransformer\GenericTreeTransformer;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
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



class UserController extends Controller
{

    /**
     * @Route("/about", name="employees_about_page")
     * @Template("OlegUserdirectoryBundle:Default:about.html.twig")
     */
    public function aboutAction( Request $request ) {
        return array();
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

        //echo "search=".$search."<br>";

        $pagination = null;
        $roles = null;

        if( $search != "" || $userid != "" ) {
            $res = $this->indexUser( null, 'current_only', true, $search, $userid );
            $pagination = $res['entities'];
            $roles = $res['roles'];
        }

        return array(
            'accessreqs' => count($accessreqs),
            'entities' => $pagination,
            'roles' => $roles,
            'search' => $search
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
     * @Route("/user-directory", name="employees_listusers")
     * @Route("/user-directory/previous", name="employees_listusers_previous")
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

        $res = $this->indexUser($filter,$time);
        $res['filter'] = $filter;

        return $res;
    }

    //$time: 'current_only' - search only current, 'past_only' - search only past, 'all' - search current and past (no filter)
    public function indexUser( $filter=null, $time='all', $limitFlag=true, $search=null, $userid=null ) {

        //$userManager = $this->container->get('fos_user.user_manager');
        //$users = $userManager->findUsers();

        $rolesArr = $this->getUserRoles();

        $repository = $this->getDoctrine()->getRepository('OlegUserdirectoryBundle:User');
        $dql =  $repository->createQueryBuilder("user");
        $dql->select('user');

        $dql->leftJoin("user.employmentStatus", "employmentStatus");

        $dql->leftJoin("user.administrativeTitles", "administrativeTitles");
        $dql->leftJoin("administrativeTitles.institution", "administrativeInstitution");
        $dql->leftJoin("administrativeTitles.department", "administrativeDepartment");
        $dql->leftJoin("administrativeTitles.division", "administrativeDivision");
        $dql->leftJoin("administrativeTitles.service", "administrativeService");

        $dql->leftJoin("user.appointmentTitles", "appointmentTitles");
        $dql->leftJoin("appointmentTitles.institution", "appointmentInstitution");
        $dql->leftJoin("appointmentTitles.department", "appointmentDepartment");
        $dql->leftJoin("appointmentTitles.division", "appointmentDivision");
        $dql->leftJoin("appointmentTitles.service", "appointmentService");

        $dql->leftJoin("user.locations", "locations");
        $dql->leftJoin("locations.assistant", "assistant");
        $dql->leftJoin("user.credentials", "credentials");

        //$dql->leftJoin("user.institutions", "institutions");
        //$dql->where("user.appliedforaccess = 'active'");

        $request = $this->get('request');
        $postData = $request->query->all();

        if( !isset($postData['sort']) ) {
            if( $time == 'current_only' ) {
                $dql->orderBy("user.lastName","ASC");
                $dql->addOrderBy("administrativeInstitution.name","ASC");
                $dql->addOrderBy("administrativeService.name","ASC");
                $dql->addOrderBy("appointmentService.name","ASC");
            } else if( $time == 'past_only' ) {
                $dql->orderBy("employmentStatus.terminationDate","DESC");
                $dql->addOrderBy("user.lastName","ASC");
            } else {
                $dql->orderBy("user.lastName","ASC");
            }
        }

        //employmentStatus
//        $timecriteriastr = "";
//        $curdate = date("Y-m-d", time());
//        echo "time=".$time."<br>";
//        if( $time == 'current_only' ) {
//            //Employment Status should have at least one group where Date of Termination is empty
//            $timecriteriastr .= "(employmentStatus IS NULL)";
//            $timecriteriastr .= " OR ";
//            $timecriteriastr .= "(employmentStatus.terminationDate IS NULL)";
//            $timecriteriastr .= " OR ";
//            $timecriteriastr .= "(employmentStatus.hireDate IS NOT NULL AND (employmentStatus.terminationDate IS NULL OR employmentStatus.terminationDate > '".$curdate."') )";
//        } else {
//            //Each group of fields in the employment status should have a non-empty Date of Termination.
//            //TODO: should the serach result display only users with all employment status have a non-empty Date of Termination?
//            $timecriteriastr .= "(employmentStatus IS NOT NULL)";
//            $timecriteriastr .= " AND ";
//            $timecriteriastr .= "(employmentStatus.hireDate IS NOT NULL AND employmentStatus.terminationDate IS NOT NULL AND employmentStatus.terminationDate < '".$curdate."')";
//            //$timecriteriastr .= " AND ";
//            //$timecriteriastr .= "(employmentStatus.hireDate IS NOT NULL AND employmentStatus.terminationDate IS NOT NULL)";
//        }


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
        if( isset($postData['sort']) ) {
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
        $criteriastr .= "user.lastName LIKE '%".$search."%' OR ";
        //$criteriastr .= "user.lastName='".$search."' OR ";

        //first name
        $criteriastr .= "user.firstName LIKE '%".$search."%' OR ";
        //$criteriastr .= "user.firstName='".$search."' OR ";

        //Middle Name
        $criteriastr .= "user.middleName LIKE '%".$search."%' OR ";
        //$criteriastr .= "user.middleName='".$search."' OR ";

        //Preferred Full Name for Display
        $criteriastr .= "user.displayName LIKE '%".$search."%' OR ";

        //Abbreviated Name/Initials field
        //$criteriastr .= "user.initials LIKE '%".$search."%' OR ";
        $criteriastr .= "user.initials='".$search."' OR ";

        //preferred email
        $criteriastr .= "user.email LIKE '%".$search."%' OR ";
        //$criteriastr .= "user.email='".$search."' OR ";

        //email in locations
        $criteriastr .= "locations.email LIKE '%".$search."%' OR ";
        //$criteriastr .= "locations.email='".$search."' OR ";

        //User ID/CWID
        //$criteriastr .= "user.primaryPublicUserId LIKE '%".$search."%' OR ";
        $criteriastr .= "user.primaryPublicUserId='".$search."' OR ";

        //administrative title
        //institution
        $criteriastr .= "administrativeInstitution.name LIKE '%".$search."%' OR ";

        //department
        $criteriastr .= "administrativeDepartment.name LIKE '%".$search."%' OR ";

        //division
        $criteriastr .= "administrativeDivision.name LIKE '%".$search."%' OR ";

        //service
        $criteriastr .= "administrativeService.name LIKE '%".$search."%' OR ";

        //academic appointment title
        //institution
        $criteriastr .= "appointmentInstitution.name LIKE '%".$search."%' OR ";

        //department
        $criteriastr .= "appointmentDepartment.name LIKE '%".$search."%' OR ";

        //division
        $criteriastr .= "appointmentDivision.name LIKE '%".$search."%' OR ";

        //service
        $criteriastr .= "appointmentService.name LIKE '%".$search."%' OR ";

        //Associated NYPH Code in Locations
        //$criteriastr .= "locations.associatedCode LIKE '%".$search."%' OR ";
        $criteriastr .= "locations.associatedCode='".$search."' OR ";

        if( $this->get('security.context')->isGranted('ROLE_ADMIN') ) {
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
        $criteriastr .= "appointmentTitles.position LIKE '%".$search."%'";


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

    private function getCleanCriteriaStr($criteriastr,$fieldName) {
        if( $criteriastr != "" ) {
            $criteriastr = " OR " . $criteriastr;
        }
        return $criteriastr;
    }

    public function getCriteriaStrByFilter( $dql, $filter, $inputCriteriastr ) {

        $criteriastr = "";

        //$curdate = date("Y-m-d", time());

        //Pending Administrative Review
        if( $filter && $filter == "Pending Administrative Review" ) {
            $pendingStatus = BaseUserAttributes::STATUS_UNVERIFIED;
            $criteriastr .= "(administrativeTitles.status = ".$pendingStatus." OR appointmentTitles.status = ".$pendingStatus." OR locations.status = ".$pendingStatus.")";
        }

        //WCMC + Pathology
        if( $filter && $filter == "All WCMC Pathology Employees" ) {
            $criteriastr .= "(administrativeInstitution.name = 'Weill Cornell Medical College' OR appointmentInstitution.name = 'Weill Cornell Medical College')";
            $criteriastr .= " AND ";
            $criteriastr .= "(administrativeDepartment.name = 'Pathology and Laboratory Medicine' OR appointmentDepartment.name = 'Pathology and Laboratory Medicine')";
        }

        //Academic Appointment Title exists + Clinical Faculty + Research Faculty
        if( $filter && $filter == "All WCMC Pathology Faculty" ) {
            $criteriastr .= "(appointmentInstitution.name = 'Weill Cornell Medical College')";
            $criteriastr .= " AND ";
            $criteriastr .= "(appointmentDepartment.name = 'Pathology and Laboratory Medicine')";
            $criteriastr .= " AND ";
            $criteriastr .= "(appointmentTitles.position = 'Clinical Faculty' OR appointmentTitles.position = 'Research Faculty')";
        }

        //Academic Appointment Title exists + Clinical Faculty
        if( $filter && $filter == "All WCMC Pathology Clinical Faculty" ) {
            $criteriastr .= "(appointmentInstitution.name = 'Weill Cornell Medical College')";
            $criteriastr .= " AND ";
            $criteriastr .= "(appointmentDepartment.name = 'Pathology and Laboratory Medicine')";
            $criteriastr .= " AND ";
            $criteriastr .= "(appointmentTitles.position = 'Clinical Faculty')";
        }

        //Academic Appointment Title exists + Research Faculty
        if( $filter && $filter == "All WCMC Pathology Research Faculty" ) {
            $criteriastr .= "(appointmentInstitution.name = 'Weill Cornell Medical College')";
            $criteriastr .= " AND ";
            $criteriastr .= "(appointmentDepartment.name = 'Pathology and Laboratory Medicine')";
            $criteriastr .= " AND ";
            $criteriastr .= "(appointmentTitles.position = 'Research Faculty')";
        }

        //Academic Appointment Title not exists + Admin Title exists
        if( $filter && $filter == "All WCMC Pathology Staff" ) {
            $criteriastr .= "(appointmentInstitution IS NULL AND appointmentDepartment IS NULL)";
            $criteriastr .= " AND ";
            $criteriastr .= "(administrativeInstitution.name = 'Weill Cornell Medical College')";
            $criteriastr .= " AND ";
            $criteriastr .= "(administrativeDepartment.name = 'Pathology and Laboratory Medicine')";
        }

        //Academic Appointment Title not exists + Admin Title exists
        if( $filter && $filter == "All NYP Pathology Staff" ) {
            $criteriastr .= "(appointmentInstitution IS NULL AND appointmentDepartment IS NULL)";
            $criteriastr .= " AND ";
            $criteriastr .= "(administrativeInstitution.name = 'New York Hospital')";
            $criteriastr .= " AND ";
            $criteriastr .= "(administrativeDepartment.name = 'Pathology')";
        }

        //Academic Appointment Title exists + division=Anatomic Pathology
        if( $filter && $filter == "All WCMC Anatomic Pathology Faculty" ) {
            $criteriastr .= "(appointmentInstitution.name = 'Weill Cornell Medical College')";
            $criteriastr .= " AND ";
            $criteriastr .= "(appointmentDepartment.name = 'Pathology and Laboratory Medicine')";
            $criteriastr .= " AND ";
            $criteriastr .= "(appointmentDivision.name = 'Anatomic Pathology')";
        }

        //Academic Appointment Title exists + division=Laboratory Medicine
        if( $filter && $filter == "All WCMC Laboratory Medicine Faculty" ) {
            $criteriastr .= "(appointmentInstitution.name = 'Weill Cornell Medical College')";
            $criteriastr .= " AND ";
            $criteriastr .= "(appointmentDepartment.name = 'Pathology and Laboratory Medicine')";
            $criteriastr .= " AND ";
            $criteriastr .= "(appointmentDivision.name = 'Laboratory Medicine')";
        }

        //As Faculty + Residents == Academic Appointment Title exists + position=Fellow
        if( $filter && $filter == "All WCMC Pathology Fellows" ) {
            $criteriastr .= "(appointmentInstitution.name = 'Weill Cornell Medical College')";
            $criteriastr .= " AND ";
            $criteriastr .= "(appointmentDepartment.name = 'Pathology and Laboratory Medicine')";
            $criteriastr .= " AND ";
            $criteriastr .= "(appointmentTitles.position = 'Fellow')";
        }

        //As Faculty + Residents == Academic Appointment Title exists + position=Resident
        if( $filter && $filter == "All WCMC Pathology Residents" ) {
            $criteriastr .= "(appointmentInstitution.name = 'Weill Cornell Medical College')";
            $criteriastr .= " AND ";
            $criteriastr .= "(appointmentDepartment.name = 'Pathology and Laboratory Medicine')";
            $criteriastr .= " AND ";
            $criteriastr .= "(appointmentTitles.position = 'Resident')";
        }

        //the same as "All WCMC Pathology Residents" except they have "AP/CP" in their "Residency Type" field.
        if( $filter && $filter == "All WCMC AP/CP Residents" ) {
            $criteriastr .= "(appointmentInstitution.name = 'Weill Cornell Medical College')";
            $criteriastr .= " AND ";
            $criteriastr .= "(appointmentDepartment.name = 'Pathology and Laboratory Medicine')";
            $criteriastr .= " AND ";
            $criteriastr .= "(appointmentTitles.position = 'Resident')";
            $dql->leftJoin("appointmentTitles.residencyTrack", "residencyTrack");
            $criteriastr .= " AND ";
            $criteriastr .= "(residencyTrack.name = 'AP/CP')";
        }

        //the same as "All WCMC Pathology Residents" except they have "AP" or "AP/CP" in their "Residency Type" field.
        if( $filter && $filter == "All WCMC AP Residents" ) {
            $criteriastr .= "(appointmentInstitution.name = 'Weill Cornell Medical College')";
            $criteriastr .= " AND ";
            $criteriastr .= "(appointmentDepartment.name = 'Pathology and Laboratory Medicine')";
            $criteriastr .= " AND ";
            $criteriastr .= "(appointmentTitles.position = 'Resident')";
            $dql->leftJoin("appointmentTitles.residencyTrack", "residencyTrack");
            $criteriastr .= " AND ";
            $criteriastr .= "(residencyTrack.name = 'AP' OR residencyTrack.name = 'AP/CP')";
        }

        //the same as "All WCMC Pathology Residents" except they have "AP" in their "Residency Type" field.
        if( $filter && $filter == "All WCMC AP Only Residents" ) {
            $criteriastr .= "(appointmentInstitution.name = 'Weill Cornell Medical College')";
            $criteriastr .= " AND ";
            $criteriastr .= "(appointmentDepartment.name = 'Pathology and Laboratory Medicine')";
            $criteriastr .= " AND ";
            $criteriastr .= "(appointmentTitles.position = 'Resident')";
            $dql->leftJoin("appointmentTitles.residencyTrack", "residencyTrack");
            $criteriastr .= " AND ";
            $criteriastr .= "(residencyTrack.name = 'AP')";
        }

        //the same as "All WCMC Pathology Residents" except they have "CP" or "AP/CP" in their "Residency Type" field.
        if( $filter && $filter == "All WCMC CP Residents" ) {
            $criteriastr .= "(appointmentInstitution.name = 'Weill Cornell Medical College')";
            $criteriastr .= " AND ";
            $criteriastr .= "(appointmentDepartment.name = 'Pathology and Laboratory Medicine')";
            $criteriastr .= " AND ";
            $criteriastr .= "(appointmentTitles.position = 'Resident')";
            $dql->leftJoin("appointmentTitles.residencyTrack", "residencyTrack");
            $criteriastr .= " AND ";
            $criteriastr .= "(residencyTrack.name = 'CP' OR residencyTrack.name = 'AP/CP')";
        }

        //the same as "All WCMC Pathology Residents" except they have "CP" in their "Residency Type" field.
        if( $filter && $filter == "All WCMC CP Only Residents" ) {
            $criteriastr .= "(appointmentInstitution.name = 'Weill Cornell Medical College')";
            $criteriastr .= " AND ";
            $criteriastr .= "(appointmentDepartment.name = 'Pathology and Laboratory Medicine')";
            $criteriastr .= " AND ";
            $criteriastr .= "(appointmentTitles.position = 'Resident')";
            $dql->leftJoin("appointmentTitles.residencyTrack", "residencyTrack");
            $criteriastr .= " AND ";
            $criteriastr .= "(residencyTrack.name = 'CP')";
        }

        // the same as "All WCMC Pathology Faculty" except they have at least one non-empty "Research Lab Title:" + a checkmark in
        //"Principal Investigator of this Lab:" with an empty or future "Dissolved on: [Date]" for Current / past or empty or future "Dissolved on: [Date]" for Previous
        if( $filter && $filter == "All WCMC Pathology Principal Investigators of Research Labs" ) {
            $criteriastr .= "(appointmentInstitution.name = 'Weill Cornell Medical College')";
            $criteriastr .= " AND ";
            $criteriastr .= "(appointmentDepartment.name = 'Pathology and Laboratory Medicine')";
            $criteriastr .= " AND ";
            $criteriastr .= "(appointmentTitles.position = 'Clinical Faculty' OR appointmentTitles.position = 'Research Faculty')";

            $dql->innerJoin("user.researchLabs", "researchLabs");

            //a checkmark in "Principal Investigator of this Lab:"
            $criteriastr .= " AND ";
            $criteriastr .= "(researchLabs.researchPI = 1)";

            //with an empty or future "Dissolved on: [Date]" for Current / past or empty or future "Dissolved on: [Date]" for Previous
//            $criteriastr .= " AND ";
//            $criteriastr .= "(researchLabs.dissolvedDate IS NULL OR researchLabs.dissolvedDate > '".$curdate."')";
        }

        // "All WCMC Pathology Faculty in Research Labs" - the same as "All WCMC Pathology Faculty"
        //except they have at least one non-empty "Research Lab Title:" with an empty or future "Dissolved on: [Date]" for Current / past or empty or future "Dissolved on: [Date]" for Previous
        if( $filter && $filter == "All WCMC Pathology Faculty in Research Labs" ) {
            $criteriastr .= "(appointmentInstitution.name = 'Weill Cornell Medical College')";
            $criteriastr .= " AND ";
            $criteriastr .= "(appointmentDepartment.name = 'Pathology and Laboratory Medicine')";
            $criteriastr .= " AND ";
            $criteriastr .= "(appointmentTitles.position = 'Clinical Faculty' OR appointmentTitles.position = 'Research Faculty')";

//            $dql->innerJoin("user.researchLabs", "researchLabs");
//            //with an empty or future "Dissolved on: [Date]" for Current / past or empty or future "Dissolved on: [Date]" for Previous
//            $criteriastr .= " AND ";
//            $criteriastr .= "(researchLabs.dissolvedDate IS NULL OR researchLabs.dissolvedDate > '".$curdate."')";
        }


        // "All WCMC or NYP Pathology Staff in Research Labs" - the same as "All WCMC Pathology Staff" OR "All NYP Pathology Staff"
        //except they have at least one non-empty "Research Lab Title:" with an empty or future "Dissolved on: [Date]" for Current / past or empty or future "Dissolved on: [Date]" for Previous
        if( $filter && $filter == "All WCMC or NYP Pathology Staff in Research Labs" ) {
            $criteriastr .= "(appointmentInstitution IS NULL AND appointmentDepartment IS NULL)";
            $criteriastr .= " AND (";
            $criteriastr .= "administrativeInstitution.name = 'Weill Cornell Medical College' AND administrativeDepartment.name = 'Pathology and Laboratory Medicine'";
            $criteriastr .= " OR ";
            $criteriastr .= "administrativeInstitution.name = 'New York Hospital' AND administrativeDepartment.name = 'Pathology'";
            $criteriastr .= ") ";

//            $dql->innerJoin("user.researchLabs", "researchLabs");
//            //with an empty or future "Dissolved on: [Date]" for Current / past or empty or future "Dissolved on: [Date]" for Previous
//            $criteriastr .= " AND ";
//            $criteriastr .= "(researchLabs.dissolvedDate IS NULL OR researchLabs.dissolvedDate > '".$curdate."')";
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

    public function pendingAdminReviewAction() {
        $pending = null;

        if( false === $this->get('security.context')->isGranted('ROLE_USERDIRECTORY_EDITOR') ) {
            $response = new Response();
            $response->setContent($pending);
            return $response;
        }

        $limitFlag = false;

        $res = $this->indexUser( 'Pending Administrative Review', 'current_only', $limitFlag );

        $pending = count($res['entities']);

        $response = new Response();
        $response->setContent($pending);

        return $response;
    }



    ////////////////////// Create New User //////////////////////
    /**
     * @Route("/users/new", name="employees_new_user")
     * @Method("GET")
     * @Template("OlegUserdirectoryBundle:Profile:edit_user.html.twig")
     */
    public function newUserAction(Request $request)
    {

        if( false === $this->get('security.context')->isGranted('ROLE_USERDIRECTORY_EDITOR') ) {
            return $this->redirect( $this->generateUrl('employees-order-nopermission') );
        }

        $em = $this->getDoctrine()->getManager();

        //$user = new User();
        $userManager = $this->container->get('fos_user.user_manager');
        $user = $userManager->createUser();

        $user->setEnabled(true);
        $user->setCreatedby('manual');

        $userSecUtil = $this->get('user_security_utility');
        $userkeytype = $userSecUtil->getDefaultUsernameType();
        $user->setKeytype($userkeytype);

        $this->addEmptyCollections($user);

        //add EIN identifier to credentials
        $identEin = new Identifier();
        $identKeytypeEin = $em->getRepository('OlegUserdirectoryBundle:IdentifierTypeList')->findOneByName("WCMC Employee Identification Number (EIN)");
        if( $identKeytypeEin ) {
            $identEin->setKeytype($identKeytypeEin);
        }
        $user->getCredentials()->addIdentifier($identEin);

        //add EIN identifier to credentials
        $identNpi = new Identifier();
        $identKeytypeNpi = $em->getRepository('OlegUserdirectoryBundle:IdentifierTypeList')->findOneByName("National Provider Identifier (NPI)");
        if( $identKeytypeNpi ) {
            $identNpi->setKeytype($identKeytypeNpi);
        }
        $user->getCredentials()->addIdentifier($identNpi);

        //Roles
        $rolesArr = $this->getUserRoles();

        $form = $this->createForm(new UserType('create',$user,$rolesArr,$this->get('security.context'),$em), $user, array('disabled' => false));

        //return $this->container->get('templating')->renderResponse('FOSUserBundle:Profile:show.html.'.$this->container->getParameter('fos_user.template.engine'), array('user' => $user));
        return array(
            'entity' => $user,
            'form' => $form->createView(),
            'cicle' => 'create_user',
            'user_id' => '',
            'sitename' => $this->container->getParameter('employees.sitename')
        );

    }


    /**
     * @Route("/users/new", name="employees_create_user")
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

        $this->addEmptyCollections($user);

        //Roles
        $rolesArr = $this->getUserRoles();

        $form = $this->createForm(new UserType('create',$user,$rolesArr,$this->get('security.context'),$em), $user, array('disabled' => false));

        $form->handleRequest($request);

        if( $user->getLastName() == "" ) {
            $error = new FormError("Last Name is empty");
            $form->get('lastName')->addError($error);
        }

        if( $user->getFirstName() == "" ) {
            $error = new FormError("First Name is empty");
            $form->get('firstName')->addError($error);
        }

        if( $user->getKeytype() == "" ) {
            $error = new FormError("Primary Public User ID Type is empty");
            $form->get('keytype')->addError($error);
        }

        if( $user->getPrimaryPublicUserId() == "" ) {
            $error = new FormError("Primary Public User ID is empty");
            $form->get('primaryPublicUserId')->addError($error);
        }

        if ($form->isValid()) {

            //set unique username
            $user->setUniqueUsername();

            $em->persist($user);
            $em->flush();

            return $this->redirect($this->generateUrl($this->container->getParameter('employees.sitename').'_showuser',array('id' => $user->getId())));
        }

        return array(
            'entity' => $user,
            'form' => $form->createView(),
            'cicle' => 'create_user',
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
     * @Route("/users/show/{id}", name="employees_showuser_notstrict")
     * @Route("/users/{id}", name="employees_showuser", requirements={"id" = "\d+"})
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

        $form = $this->createForm(new UserType('show',$entity,$rolesArr,$this->get('security.context'),$em), $entity, array('disabled' => true));

//        if (!is_object($user) || !$user instanceof UserInterface) {
//            throw new AccessDeniedException('This user does not have access to this section.');
//        }

        //return $this->container->get('templating')->renderResponse('FOSUserBundle:Profile:show.html.'.$this->container->getParameter('fos_user.template.engine'), array('user' => $user));
        return array(
            'entity' => $entity,
            'form' => $form->createView(),
            'cicle' => 'show_user',
            'user_id' => $id,
            'sitename' => $sitename
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

        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('OlegUserdirectoryBundle:User')->find($id);

        if( !$entity ) {
            throw $this->createNotFoundException('Unable to find User entity.');
        }

        $this->addEmptyCollections($entity);

        $this->addHookFields($entity);

        //Roles
        $rolesArr = $this->getUserRoles();

        $form = $this->createForm(new UserType('edit',$entity,$rolesArr,$this->get('security.context'),$em), $entity, array(
            'action' => $this->generateUrl($sitename.'_user_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));
//        $form->add('submit', 'submit', array('label' => 'Update','attr' => array('class' => 'btn btn-warning')));

        return array(
            'entity' => $entity,
            'form' => $form->createView(),
            'cicle' => 'edit_user',
            'user_id' => $id,
            'sitename' => $sitename
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
        if( $this->get('security.context')->isGranted('ROLE_ADMIN') || $this->get('security.context')->isGranted('ROLE_USERDIRECTORY_EDITOR') ||
            $entity->getId() && $entity->getId() == $user->getId()
        ) {
            if( count($entity->getPrivateComments()) == 0 ) {
                $entity->addPrivateComment( new PrivateComment($user) );
            }
        }
        if( $this->get('security.context')->isGranted('ROLE_ADMIN') || $this->get('security.context')->isGranted('ROLE_USERDIRECTORY_EDITOR') ) {
            if( count($entity->getAdminComments()) == 0 ) {
                $entity->addAdminComment( new AdminComment($user) );
            }
        }
        if( $this->get('security.context')->isGranted('ROLE_ADMIN') || $this->get('security.context')->isGranted('ROLE_USERDIRECTORY_EDITOR') ) {
            if( count($entity->getConfidentialComments()) == 0 ) {
                $entity->addConfidentialComment( new ConfidentialComment($user) );
            }
        }

        if( count($entity->getResearchLabs()) == 0 ) {
            $entity->addResearchLab(new ResearchLab($user));
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

        $this->addEmptyCollections($entity);

        $this->addHookFields($entity);

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

        $originalLocations = new ArrayCollection();
        foreach( $entity->getLocations() as $loc) {
            $originalLocations->add($loc);
        }

        //Credentials collections
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

        //echo "count=".count($originalAdminTitles)."<br>";
        //exit();

        //Roles
        $rolesArr = $this->getUserRoles();

        $form = $this->createForm(new UserType('edit',$entity,$rolesArr,$this->get('security.context'),$em), $entity, array(
            'action' => $this->generateUrl($sitename.'_user_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));
        //$form->add('submit', 'submit', array('label' => 'Update'));

        $form->handleRequest($request);

        //exit('after handle request');
        //print_r($form->getErrors());

        if( $form->isValid() ) {

            //echo "form is valid<br>";
            //exit();

            //check if roles were changed and user is not admin
            if( false === $this->get('security.context')->isGranted('ROLE_ADMIN') && false === $this->get('security.context')->isGranted('ROLE_USERDIRECTORY_EDITOR') ) {
                $currRoles = $entity->getRoles();
                if( count($originalRoles) != count($currRoles) ) {
                    $this->setSessionForbiddenNote("Change Roles");
                    throw new ForbiddenOverwriteException("You do not have permission to perform this operation: Change Roles");
                }
                foreach( $currRoles as $role ) {
                    if( !in_array($role, $originalRoles) ) {
                        $this->setSessionForbiddenNote("Change Roles");
                        throw new ForbiddenOverwriteException("You do not have permission to perform this operation: Change Roles");
                    }
                }
            }

            //exit('before processing');

            //set parents for institution tree for Administrative and Academical Titles
            $this->setParentsForInstitutionTree($entity);

            //set parents for institution tree for Administrative and Academical Titles
            $this->setParentsForCommentTypeTree($entity);

            //set parents for institution tree for Administrative and Academical Titles
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

            $removedInfo = $this->removeCollection($originalLocations,$entity->getLocations());
            if( $removedInfo ) {
                $removedCollections[] = $removedInfo;
            }

            //check for removed collection for Credentials: stateLicense, boardCertification, codeNYPH
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

            $removedInfo = $this->removeCollection($originalResLabs,$entity->getResearchLabs());
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
                $this->createUserEditEvent($sitename,$event,$user,$request);
            }

            //echo "user=".$entity."<br>";
            //exit();

            //$em->persist($entity);
            $em->flush($entity);

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
            'cicle' => 'edit_user',
            'user_id' => $id,
            'sitename' => $sitename
        );
    }

    public function updateInfo($subjectUser) {
        //$user = $this->get('security.context')->getToken()->getUser();

        //Administartive and Appointment Titles and Comments update info set when parent are processed
        //So, set author info for the rest: EmploymentStatus, Location, Credentials, ResearchLab
        foreach( $subjectUser->getEmploymentStatus() as $entity ) {
            $this->setUpdateInfo($entity);
        }

        foreach( $subjectUser->getLocations() as $entity ) {
            $this->setUpdateInfo($entity);
        }

        //credentials
        $this->setUpdateInfo($subjectUser->getCredentials());

        foreach( $subjectUser->getResearchLabs() as $entity ) {
            $this->setUpdateInfo($entity);
        }

    }
    public function setUpdateInfo($entity) {

        //echo "ent=".$entity."<br>";

        if( !$entity ) {
            return;
        }

        $user = $this->get('security.context')->getToken()->getUser();
        $em = $this->getDoctrine()->getManager();
        $author = $em->getRepository('OlegUserdirectoryBundle:User')->find($user->getId());

        //set author and roles if not set
        if( !$entity->getAuthor() ) {
            $entity->setAuthor($author);
        }
//        else {
//            $entity->setUpdateAuthor($user);
//            $entity->setUpdateAuthorRoles($user->getRoles());
//        }

        $entity->setUpdateAuthor($author);
        $entity->setUpdateAuthorRoles($author->getRoles());
    }

    public function createUserEditEvent($sitename,$event,$user,$request) {
        $userSecUtil = $this->get('user_security_utility');
        $eventLog = $userSecUtil->constructEventLog($sitename,$user,$request);
        $eventLog->setEvent($event);

        //set Event Type
        $em = $this->getDoctrine()->getManager();
        $eventtype = $em->getRepository('OlegUserdirectoryBundle:EventTypeList')->findOneByName('User Updated');
        $eventLog->setEventType($eventtype);

        $em = $this->getDoctrine()->getManager();
        $em->persist($eventLog);
        $em->flush();
    }


    public function setParentsForInstitutionTree($entity) {

        foreach( $entity->getAdministrativeTitles() as $title) {
            $this->processTitle($title);
        }

        foreach( $entity->getAppointmentTitles() as $title) {
            //echo "<br>################### AppTitle: ###################<br>";
            $this->processTitle($title);
        }

    }
    public function processTitle($title) {

        $institution = $title->getInstitution();
        $department = $title->getDepartment();
        $division = $title->getDivision();
        $service = $title->getService();

        $em = $this->getDoctrine()->getManager();
        $user = $this->get('security.context')->getToken()->getUser();

        $department = $em->getRepository('OlegUserdirectoryBundle:Institution')->checkAndSetParent($user,$title,$institution,$department);

        $division = $em->getRepository('OlegUserdirectoryBundle:Institution')->checkAndSetParent($user,$title,$department,$division);

        $service = $em->getRepository('OlegUserdirectoryBundle:Institution')->checkAndSetParent($user,$title,$division,$service);

        //set author if not set
        $this->setUpdateInfo($title);

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
        $this->setUpdateInfo($comment);

        echo "<br>Comment text=".$comment->getComment()."<br>";

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


    public function removeCollection($originalArr,$currentArr) {
        $em = $this->getDoctrine()->getManager();
        $removeArr = array();

        foreach( $originalArr as $title ) {

            //check if location is not home and main
            if( method_exists($title,'getRemovable') ) {
                if( $title->getRemovable() == false ) {
                    continue;
                }
            }

            //echo "title=".$title->getName().", id=".$title->getId()."<br>";
            $em->persist($title);
            if( false === $currentArr->contains($title) ) {
                $removeArr[] = "<strong>"."Removed: ".$title." ".$this->getEntityId($title)."</strong>";
                // if you wanted to delete the Tag entirely, you can also do that
                $em->remove($title);
                $em->flush();
            }
        }

        return implode("<br>", $removeArr);
    }




//    /**
//     * @Route("/new_user1", name="new_user1")
//     * @Method("GET")
//     * @Template("OlegUserdirectoryBundle:Profile:edit_user.html.twig")
//     */
//    public function registerAction(Request $request)
//    {
//        /** @var $formFactory \FOS\UserBundle\Form\Factory\FactoryInterface */
//        $formFactory = $this->container->get('fos_user.registration.form.factory');
//        /** @var $userManager \FOS\UserBundle\Model\UserManagerInterface */
//        $userManager = $this->container->get('fos_user.user_manager');
//        /** @var $dispatcher \Symfony\Component\EventDispatcher\EventDispatcherInterface */
//        $dispatcher = $this->container->get('event_dispatcher');
//
//        $user = $userManager->createUser();
//        $user->setEnabled(true);
//
//        $event = new GetResponseUserEvent($user, $request);
//        $dispatcher->dispatch(FOSUserEvents::REGISTRATION_INITIALIZE, $event);
//
//        if (null !== $event->getResponse()) {
//            return $event->getResponse();
//        }
//
//        $form = $formFactory->createForm();
//        $form->setData($user);
//
//        if ('POST' === $request->getMethod()) {
//            $form->bind($request);
//
//            if ($form->isValid()) {
//                $event = new FormEvent($form, $request);
//                $dispatcher->dispatch(FOSUserEvents::REGISTRATION_SUCCESS, $event);
//
//                $userManager->updateUser($user);
//
//                if (null === $response = $event->getResponse()) {
//                    $url = $this->container->get('router')->generate('fos_user_registration_confirmed');
//                    $response = new RedirectResponse($url);
//                }
//
//                $dispatcher->dispatch(FOSUserEvents::REGISTRATION_COMPLETED, new FilterUserResponseEvent($user, $request, $response));
//
//                return $response;
//            }
//        }
//
//        return $this->container->get('templating')->renderResponse('OlegUserdirectoryBundle:Profile:register.html.twig', array(
//            'form' => $form->createView(),
//        ));
//    }




    /**
     * Generate users from excel
     *
     * @Route("/users/generate", name="generate_users")
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
        $roles = $em->getRepository('OlegUserdirectoryBundle:Roles')->findAll();
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
        $this->createUserEditEvent($sitename,$event,$user,$request);

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

}
