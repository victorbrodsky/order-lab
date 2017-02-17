<?php

namespace Oleg\UserdirectoryBundle\Controller;


use Oleg\FellAppBundle\Entity\FellAppRank;
use Oleg\FellAppBundle\Entity\FellAppStatus;
use Oleg\FellAppBundle\Entity\LanguageProficiency;
use Oleg\UserdirectoryBundle\Entity\AuthorshipRoles;
use Oleg\UserdirectoryBundle\Entity\BloodProductTransfusedList;
use Oleg\UserdirectoryBundle\Entity\BloodTypeList;
use Oleg\UserdirectoryBundle\Entity\CCIPlateletTypeTransfusedList;
use Oleg\UserdirectoryBundle\Entity\CCIUnitPlateletCountDefaultValueList;
use Oleg\UserdirectoryBundle\Entity\CertifyingBoardOrganization;
use Oleg\UserdirectoryBundle\Entity\CityList;
use Oleg\UserdirectoryBundle\Entity\ClericalErrorList;
use Oleg\UserdirectoryBundle\Entity\Collaboration;
use Oleg\UserdirectoryBundle\Entity\CollaborationTypeList;
use Oleg\UserdirectoryBundle\Entity\CommentGroupType;
use Oleg\UserdirectoryBundle\Entity\ComplexPlateletSummaryAntibodiesList;
use Oleg\UserdirectoryBundle\Entity\FormNode;
use Oleg\UserdirectoryBundle\Entity\HealthcareProviderSpecialtiesList;
use Oleg\UserdirectoryBundle\Entity\ImportanceList;
use Oleg\UserdirectoryBundle\Entity\LabResultFlagList;
use Oleg\UserdirectoryBundle\Entity\LabResultNameList;
use Oleg\UserdirectoryBundle\Entity\LabResultUnitsMeasureList;
use Oleg\UserdirectoryBundle\Entity\LifeFormList;
use Oleg\UserdirectoryBundle\Entity\ListAbstract;
use Oleg\UserdirectoryBundle\Entity\MedicalLicenseStatus;
use Oleg\UserdirectoryBundle\Entity\EventObjectTypeList;
use Oleg\UserdirectoryBundle\Entity\MonthsList;
use Oleg\UserdirectoryBundle\Entity\ObjectTypeList;
use Oleg\UserdirectoryBundle\Entity\OrganizationalGroupDefault;
use Oleg\UserdirectoryBundle\Entity\OrganizationalGroupType;
use Oleg\UserdirectoryBundle\Entity\LinkTypeList;
use Oleg\UserdirectoryBundle\Entity\LocaleList;
use Oleg\UserdirectoryBundle\Entity\PathologyResultSignatoriesList;
use Oleg\UserdirectoryBundle\Entity\Permission;
use Oleg\UserdirectoryBundle\Entity\PermissionActionList;
use Oleg\UserdirectoryBundle\Entity\PermissionList;
use Oleg\UserdirectoryBundle\Entity\PermissionObjectList;
use Oleg\UserdirectoryBundle\Entity\PlateletTransfusionProductReceivingList;
use Oleg\UserdirectoryBundle\Entity\PlatformListManagerRootList;
use Oleg\UserdirectoryBundle\Entity\PositionTrackTypeList;
use Oleg\UserdirectoryBundle\Entity\PositionTypeList;
use Oleg\UserdirectoryBundle\Entity\SexList;
use Oleg\UserdirectoryBundle\Entity\SiteList;
use Oleg\UserdirectoryBundle\Entity\SpotPurpose;
use Oleg\UserdirectoryBundle\Entity\TitlePositionType;
use Oleg\UserdirectoryBundle\Entity\TrainingTypeList;
use Oleg\UserdirectoryBundle\Entity\TransfusionAntibodyScreenResultsList;
use Oleg\UserdirectoryBundle\Entity\TransfusionCrossmatchResultsList;
use Oleg\UserdirectoryBundle\Entity\TransfusionDATResultsList;
use Oleg\UserdirectoryBundle\Entity\TransfusionHemolysisCheckResultsList;
use Oleg\UserdirectoryBundle\Entity\TransfusionProductStatusList;
use Oleg\UserdirectoryBundle\Entity\TransfusionReactionTypeList;
use Oleg\UserdirectoryBundle\Entity\WeekDaysList;
use Oleg\UserdirectoryBundle\Form\DataTransformer\SingleUserWrapperTransformer;
use Oleg\UserdirectoryBundle\Form\HierarchyFilterType;
use Oleg\VacReqBundle\Entity\VacReqRequestTypeList;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Intl\Intl;

use Oleg\UserdirectoryBundle\Entity\PerSiteSettings;
use Oleg\UserdirectoryBundle\Entity\AdministrativeTitle;
use Oleg\UserdirectoryBundle\Entity\BuildingList;
use Oleg\UserdirectoryBundle\Entity\CompletionReasonList;
use Oleg\UserdirectoryBundle\Entity\DocumentTypeList;
use Oleg\UserdirectoryBundle\Entity\EmploymentType;
use Oleg\UserdirectoryBundle\Entity\FellowshipSubspecialty;
use Oleg\UserdirectoryBundle\Entity\FellowshipTitleList;
use Oleg\UserdirectoryBundle\Entity\GeoLocation;
use Oleg\UserdirectoryBundle\Entity\HonorTrainingList;
use Oleg\UserdirectoryBundle\Entity\InstitutionType;
use Oleg\UserdirectoryBundle\Entity\Location;
use Oleg\UserdirectoryBundle\Entity\MedicalSpecialties;
use Oleg\UserdirectoryBundle\Entity\MedicalTitleList;
use Oleg\UserdirectoryBundle\Entity\ResearchLab;
use Oleg\UserdirectoryBundle\Entity\ResidencySpecialty;
use Oleg\UserdirectoryBundle\Entity\SourceOrganization;
use Oleg\UserdirectoryBundle\Entity\SourceSystemList;
use Oleg\UserdirectoryBundle\Entity\TrainingDegreeList;
use Oleg\UserdirectoryBundle\Entity\User;

use Oleg\UserdirectoryBundle\Entity\SiteParameters;
use Oleg\UserdirectoryBundle\Util\UserUtil;
use Oleg\UserdirectoryBundle\Entity\Roles;
use Oleg\UserdirectoryBundle\Entity\Institution;
use Oleg\UserdirectoryBundle\Entity\Department;
use Oleg\UserdirectoryBundle\Entity\Division;
use Oleg\UserdirectoryBundle\Entity\Service;
use Oleg\UserdirectoryBundle\Entity\States;
use Oleg\UserdirectoryBundle\Entity\BoardCertifiedSpecialties;
use Oleg\UserdirectoryBundle\Entity\EmploymentTerminationType;
use Oleg\UserdirectoryBundle\Entity\EventTypeList;
use Oleg\UserdirectoryBundle\Entity\IdentifierTypeList;
use Oleg\UserdirectoryBundle\Entity\FellowshipTypeList;
use Oleg\UserdirectoryBundle\Entity\ResidencyTrackList;
use Oleg\UserdirectoryBundle\Entity\LocationTypeList;
use Oleg\UserdirectoryBundle\Entity\Countries;
use Oleg\UserdirectoryBundle\Entity\Equipment;
use Oleg\UserdirectoryBundle\Entity\EquipmentType;
use Oleg\UserdirectoryBundle\Entity\LocationPrivacyList;
use Oleg\UserdirectoryBundle\Entity\RoleAttributeList;
use Oleg\UserdirectoryBundle\Entity\LanguageList;
use Symfony\Component\Intl\Locale\Locale;


//Notes:
//To turn off foreign key constraint globally: "SET GLOBAL FOREIGN_KEY_CHECKS=0;"

/**
 * @Route("/admin")
 */
class AdminController extends Controller
{
    /**
     * Admin Page
     *
     * @Route("/lists/", name="user_admin_index")
     * @Method("GET")
     * @Template("OlegUserdirectoryBundle:Admin:index.html.twig")
     */
    public function indexAction()
    {

        $environment = 'dev'; //default

        $em = $this->getDoctrine()->getManager();
        $params = $roles = $em->getRepository('OlegUserdirectoryBundle:SiteParameters')->findAll();

        if( count($params) > 1 ) {
            throw new \Exception( 'Must have only one parameter object. Found '.count($params).'object(s)' );
        }

        if( count($params) == 1 ) {
            $param = $params[0];
            $environment = $param->getEnvironment();
        }

        return $this->render('OlegUserdirectoryBundle:Admin:index.html.twig', array('environment'=>$environment));
    }

    /**
     * Admin Page
     *
     * @Route("/hierarchies/", name="user_admin_hierarchy_index")
     * @Method("GET")
     * @Template("OlegUserdirectoryBundle:Admin:hierarchy-index.html.twig")
     */
    public function indexHierarchyAction()
    {

        $environment = 'dev'; //default

        $em = $this->getDoctrine()->getManager();
        $params = $roles = $em->getRepository('OlegUserdirectoryBundle:SiteParameters')->findAll();

        if( count($params) > 1 ) {
            throw new \Exception( 'Must have only one parameter object. Found '.count($params).'object(s)' );
        }

        if( count($params) == 1 ) {
            $param = $params[0];
            $environment = $param->getEnvironment();
        }

        $filters = $this->getDefaultHierarchyFilter();

        return $this->render('OlegUserdirectoryBundle:Admin:hierarchy-index.html.twig', array('environment'=>$environment,'filters'=>$filters));
    }
    public function getDefaultHierarchyFilter() {
        $filterStr = array();
        //add a filter that checks if the site is "live" and hides this node in the live environment
        $userSecUtil = $this->container->get('user_security_utility');
        $environment = $userSecUtil->getSiteSettingParameter('environment');
        if( $environment == "live" ) { //live
            $filterStr['filter[types][0]'] = 'default';
            $filterStr['filter[types][1]'] = 'user-added';
        }
        //print_r($filterStr);
        return $filterStr;
    }


    /**
     * Populate DB
     *
     * @Route("/populate-all-lists-with-default-values", name="user_generate_all")
     * @Method("GET")
     * @Template("OlegUserdirectoryBundle:Admin:index.html.twig")
     */
    public function generateAllAction()
    {
        $userutil = new UserUtil();
        $user = $this->get('security.context')->getToken()->getUser();

        //$max_exec_time = ini_get('max_execution_time');
        ini_set('max_execution_time', 1800); //1800 seconds = 30 minutes; it will set back to original value after execution of this script

        //$default_time_zone = $this->container->getParameter('default_time_zone');

        //$count_EventTypeListSync = $this->syncEventTypeListDb(); //must be the first to update already existing objects. Can run on empty DB

        //testing
        //$count_setObjectTypeForAllLists = $this->setObjectTypeForAllLists();
        //$this->generateLabResultNames();
        //$this->generateLocationsFromExcel();

        $count_sitenameList = $this->generateSitenameList();

        $count_institutiontypes = $this->generateInstitutionTypes();                                //must be first
        $count_OrganizationalGroupType = $this->generateOrganizationalGroupType();                  //must be first
        $count_institution = $this->generateInstitutions();                                         //must be first
        $count_auxinstitution = $this->generateAuxiliaryInstitutions();
        $count_appTitlePositions = $this->generateAppTitlePositions();

        $count_CommentGroupType = $this->generateCommentGroupType();

        $count_siteParameters = $this->generateSiteParameters();    //can be run only after institution generation

        $count_roles = $this->generateRoles();
        $count_employmentTypes = $this->generateEmploymentTypes();
        $count_states = $this->generateStates();
        //$count_countryList = $this->generateCountryList();
        $count_languages = $this->generateLanguages();
        $count_locales = $this->generateLocales();
        $count_locationTypeList = $this->generateLocationTypeList();
        $count_locprivacy = $this->generateLocationPrivacy();
        $count_generateDefaultOrgGroupSiteParameters = $this->generateDefaultOrgGroupSiteParameters();

        $count_terminationTypes = $this->generateTerminationTypes();
        $count_eventTypeList = $this->generateEventTypeList();
        $count_usernameTypeList = $userutil->generateUsernameTypes($this->getDoctrine()->getManager(),$user);
        $count_identifierTypeList = $this->generateIdentifierTypeList();
        $count_fellowshipTypeList = $this->generateFellowshipTypeList();
        $count_residencyTrackList = $this->generateResidencyTrackList();

        $count_medicalTitleList = $this->generateMedicalTitleList();
        $count_medicalSpecialties = $this->generateMedicalSpecialties();

        $count_equipmentType = $this->generateEquipmentType();
        $count_equipment = $this->generateEquipment();

        $count_buildings = $this->generateBuildings();
        $count_locations = $this->generateLocations();

        $count_SpotPurpose = $this->generateSpotPurpose();

        $count_reslabs = $this->generateResLabs();

        //TODO: rewrite using DB not Aperio's SOAP
        $userGenerator = $this->container->get('user_generator');
        //$count_users = $userGenerator->generateUsersExcel();
        //$count_users = 0;

        $count_testusers = $this->generateTestUsers();

        $count_boardSpecialties = $this->generateBoardSpecialties();

        $count_sourcesystems = $this->generateSourceSystems();

        $count_documenttypes = $this->generateDocumentTypes();
        $count_generateLinkTypes = $this->generateLinkTypes();

        //training
        $count_completionReasons = $this->generateCompletionReasons();
        $count_trainingDegrees = $this->generateTrainingDegrees();
        //$count_majorTrainings = $this->generateMajorTrainings();
        //$count_minorTrainings = $this->generateMinorTrainings();
        $count_HonorTrainings = $this->generateHonorTrainings();
        $count_FellowshipTitles = $this->generateFellowshipTitles();
        $count_residencySpecialties = $this->generateResidencySpecialties();

        $count_sourceOrganizations = $this->generatesourceOrganizations();
        $count_generateImportances = $this->generateImportances();
        $count_generateAuthorshipRoles = $this->generateAuthorshipRoles();

        $count_sex = $this->generateSex();

        $count_PositionTypeList = $this->generatePositionTypeList();

        $count_generateMedicalLicenseStatus = $this->generateMedicalLicenseStatus();

        $count_generateCertifyingBoardOrganization = $this->generateCertifyingBoardOrganization();
        $count_TrainingTypeList = $this->generateTrainingTypeList();

        $count_FellAppStatus = $this->generateFellAppStatus();
        $count_FellAppRank = $this->generateFellAppRank();
        $count_LanguageProficiency = $this->generateLanguageProficiency();

        $collaborationtypes = $this->generateCollaborationtypes();
        $count_Permissions = $this->generatePermissions();
        $count_PermissionObjects = $this->generatePermissionObjects();
        $count_PermissionActions = $this->generatePermissionActions();

        $count_ObjectTypeActions = $this->generateObjectTypeActions();

        $count_EventObjectTypeList = $this->generateEventObjectTypeList();
        $count_VacReqRequestTypeList = $this->generateVacReqRequestTypeList();

        $adminRes = $this->generateAdministratorAction();

        $count_HealthcareProviderSpecialtiesList = $this->generateHealthcareProviderSpecialtiesList();

        $count_setObjectTypeForAllLists = $this->setObjectTypeForAllLists();

        $count_BloodProductTransfused = $this->generateBloodProductTransfused();
        $count_TransfusionReactionType = $this->generateTransfusionReactionType();
        $count_BloodTypeList = $this->generateBloodTypeList();
        $count_TransfusionAntibodyScreenResultsList = $this->generateTransfusionAntibodyScreenResultsList();
        $count_TransfusionDATResultsList = $this->generateTransfusionDATResultsList();
        $count_TransfusionCrossmatchResultsList = $this->generateTransfusionCrossmatchResultsList();
        $count_TransfusionHemolysisCheckResultsList = $this->generateTransfusionHemolysisCheckResultsList();
        $count_ComplexPlateletSummaryAntibodiesList = $this->generateComplexPlateletSummaryAntibodiesList();
        $count_CCIUnitPlateletCountDefaultValueList = $this->generateCCIUnitPlateletCountDefaultValueList();
        $count_CCIPlateletTypeTransfusedList = $this->generateCCIPlateletTypeTransfusedList();
        $count_PlateletTransfusionProductReceivingList = $this->generatePlateletTransfusionProductReceivingList();
        $count_TransfusionProductStatusList = $this->generateTransfusionProductStatusList();
        $count_generateWeekDaysList = $this->generateWeekDaysList();
        $count_generateMonthsList = $this->generateMonthsList();
        $count_generateClericalErrorList = $this->generateClericalErrorList();
        $count_generateLabResultNames = $this->generateLabResultNames();
        $count_generateLabResultUnitsMeasureList = $this->generateLabResultUnitsMeasureList();
        $count_generateLabResultFlagList = $this->generateLabResultFlagList();
        $count_generatePathologyResultSignatoriesList = $this->generatePathologyResultSignatoriesList();
        $count_setFormNodeVersion = $this->setFormNodeVersion();
        $count_generateLifeForm = $this->generateLifeForm();

        $count_generatePlatformListManagerList = $this->generatePlatformListManagerList();



        $this->get('session')->getFlashBag()->add(
            'notice',
            'Generated Tables: '.
            'Sitenames='.$count_sitenameList.', '.
            'Source Systems='.$count_sourcesystems.', '.
            'Roles='.$count_roles.', '.
            'Site Settings='.$count_siteParameters.', '.
            'generateDefaultOrgGroupSiteParameters='.$count_generateDefaultOrgGroupSiteParameters.', '.
            'Institution Types='.$count_institutiontypes.', '.
            'Organizational Group Types='.$count_OrganizationalGroupType.', '.
            'Institutions='.$count_institution.', '.
            'Auxiliary Institutions='.$count_auxinstitution.', '.
            'Appointment Title Positions='.$count_appTitlePositions.', '.
            //'Users='.$count_users.', '.
            'Test Users='.$count_testusers.', '.
            'Board Specialties='.$count_boardSpecialties.', '.
            'Employment Types='.$count_employmentTypes.', '.
            'Employment Types of Termination='.$count_terminationTypes.', '.
            'Event Log Types='.$count_eventTypeList.', '.
            'Username Types='.$count_usernameTypeList.', '.
            'Identifier Types='.$count_identifierTypeList.', '.
            'Residency Tracks='.$count_residencyTrackList.', '.
            'Fellowship Types='.$count_fellowshipTypeList.', '.
            'Medical Titles='.$count_medicalTitleList.', '.
            'Medical Specialties='.$count_medicalSpecialties.', '.
            'Equipment Types='.$count_equipmentType.', '.
            'Equipment='.$count_equipment.', '.
            'Location Types='.$count_locationTypeList.', '.
            'Location Privacy='.$count_locprivacy.', '.
            'States='.$count_states.', '.
            //'Countries='.$count_countryList.', '.
            'Languages='.$count_languages.', '.
            'Locales='.$count_locales.', '.
            'Locations='.$count_locations.', '.
            'Buildings='.$count_buildings.', '.
            'Reaserch Labs='.$count_reslabs.', '.
            'Completion Reasons='.$count_completionReasons.', '.
            'Training Degrees='.$count_trainingDegrees.', '.
            'Residency Specialties='.$count_residencySpecialties.', '.
            //'Major Trainings ='.$count_majorTrainings.', '.
            //'Minor Trainings ='.$count_minorTrainings.', '.
            'Honor Trainings='.$count_HonorTrainings.', '.
            'Fellowship Titles='.$count_FellowshipTitles.', '.
            'Document Types='.$count_documenttypes.', '.
            'Source Organizations='.$count_sourceOrganizations.', '.
            'Importances='.$count_generateImportances.', '.
            'AuthorshipRoles='.$count_generateAuthorshipRoles.', '.
            'LinkTypes='.$count_generateLinkTypes.', '.
            'Sex='.$count_sex.', '.
            'Position Types='.$count_PositionTypeList.', '.
            'Comment Group Types='.$count_CommentGroupType.', '.
            'Spot Purposes='.$count_SpotPurpose.', '.
            'Medical License Statuses='.$count_generateMedicalLicenseStatus.', '.
            'Certifying Board Organizations='.$count_generateCertifyingBoardOrganization.', '.
            'Training Types='.$count_TrainingTypeList.', '.
            'FellApp Statuses='.$count_FellAppStatus.', '.
            'FellApp Ranks='.$count_FellAppRank.', '.
            'Language Proficiency='.$count_LanguageProficiency.', '.
            'Permissions ='.$count_Permissions.', '.
            'PermissionObjects ='.$count_PermissionObjects.', '.
            'PermissionActions ='.$count_PermissionActions.', '.
            'ObjectTypeActions='.$count_ObjectTypeActions.', '.
            'setObjectTypeForAllLists='.$count_setObjectTypeForAllLists.', '.
            'Collaboration Types='.$collaborationtypes.', '.
            'EventObjectTypeList count='.$count_EventObjectTypeList.', '.
            'VacReqRequestTypeList count='.$count_VacReqRequestTypeList.', '.
            'Administrator generation='.$adminRes.', '.
            'HealthcareProviderSpecialtiesList='.$count_HealthcareProviderSpecialtiesList.', '.
            'BloodProductTransfused='.$count_BloodProductTransfused.', '.
            'TransfusionReactionType='.$count_TransfusionReactionType.', '.
            'BloodTypeList='.$count_BloodTypeList.', '.
            'TransfusionAntibodyScreenResultsList='.$count_TransfusionAntibodyScreenResultsList.', '.
            'TransfusionDATResultsList='.$count_TransfusionDATResultsList.', '.
            'TransfusionCrossmatchResultsList='.$count_TransfusionCrossmatchResultsList.', '.
            'TransfusionHemolysisCheckResultsList='.$count_TransfusionHemolysisCheckResultsList.', '.
            'ComplexPlateletSummaryAntibodiesList='.$count_ComplexPlateletSummaryAntibodiesList.', '.
            'CCIUnitPlateletCountDefaultValueList='.$count_CCIUnitPlateletCountDefaultValueList.', '.
            'CCIPlateletTypeTransfusedList='.$count_CCIPlateletTypeTransfusedList.', '.
            'PlateletTransfusionProductReceivingList='.$count_PlateletTransfusionProductReceivingList.', '.
            'TransfusionProductStatusList='.$count_TransfusionProductStatusList.', '.
            'WeekDaysList='.$count_generateWeekDaysList.', '.
            'MonthsList='.$count_generateMonthsList.', '.
            'ClericalErrorList='.$count_generateClericalErrorList.', '.
            'LabResultNames='.$count_generateLabResultNames.', '.
            'LabResultUnitsMeasures='.$count_generateLabResultUnitsMeasureList.', '.
            'LabResultFlagList='.$count_generateLabResultFlagList.', '.
            'PathologyResultSignatoriesList='.$count_generatePathologyResultSignatoriesList.', '.
            'FormNodeVersion='.$count_setFormNodeVersion.', '.
            'LifeForms='.$count_generateLifeForm.', '.
            'PlatformListManagerList='.$count_generatePlatformListManagerList.', '.

            ' (Note: -1 means that this table is already exists)'
        );

        //ini_set('max_execution_time', $max_exec_time); //set back to the original value

        return $this->redirect($this->generateUrl('user_admin_index'));
    }


    /**
     * @Route("/populate-residency-specialties-with-default-values", name="generate_residencyspecialties")
     * @Method("GET")
     * @Template()
     */
    public function generateResidencySpecialtiesAction()
    {

        $count = $this->generateResidencySpecialties();
        if( $count >= 0 ) {

            $this->get('session')->getFlashBag()->add(
                'notice',
                'Created '.$count. ' Residency Specialties'
            );

            return $this->redirect($this->generateUrl('user_admin_index'));

        } else {

            $this->get('session')->getFlashBag()->add(
                'notice',
                'This table is already exists!'
            );

            return $this->redirect($this->generateUrl('user_admin_index'));
        }

    }


    /**
     * @Route("/populate-country-city-list-with-default-values", name="generate_country_city")
     * @Method("GET")
     * @Template()
     */
    public function generateProcedureAction()
    {

        $max_exec_time = ini_get('max_execution_time');
        ini_set('max_execution_time', 900); //900 seconds = 15 minutes

        $count = $this->generateCountryList();

        $countryCount = $count['country'];
        $cityCount = $count['city'];

        $this->get('session')->getFlashBag()->add(
            'notice',
            'Added '.$countryCount.' countries and '.$cityCount.' cities'
        );

        ini_set('max_execution_time', $max_exec_time); //set back to the original value

        return $this->redirect($this->generateUrl('user_admin_index'));
    }



//////////////////////////////////////////////////////////////////////////////

    public function setDefaultList( $entity, $count, $user, $name=null ) {
        //$userutil = new UserUtil();
        //return $userutil->setDefaultList( $entity, $count, $user, $name );

        $userSecUtil = $this->get('user_security_utility');
        $entity = $userSecUtil->setDefaultList( $entity, $count, $user, $name );
        $entity->setType('default');
        return $entity;
    }

   
    //Generate or Update roles
    public function generateRoles() {

        $em = $this->getDoctrine()->getManager();

        //generate role can update the role too
//        $entities = $em->getRepository('OlegUserdirectoryBundle:Roles')->findAll();
//        if( $entities ) {
//            //return -1;
//        }

        //Note: fos user has role ROLE_SCANORDER_SUPER_ADMIN

        $types = array(

            //////////// general roles are set by security.yml only ////////////

            //general super admin role for all sites
            "ROLE_PLATFORM_ADMIN" => array(
                "Platform Administrator",
                "Full access for all sites",
                100
            ),
            "ROLE_PLATFORM_DEPUTY_ADMIN" => array(
                "Deputy Platform Administrator",
                'The same as "Platform Administrator" role can do except assign or remove "Platform Administrator" or "Deputy Platform Administrator" roles',
                100
            ),
            //"ROLE_BANNED" => "Banned user for all sites",                 //general super admin role for all sites
            //"ROLE_UNAPPROVED" => "Unapproved User",                       //general unapproved user

            //ROLE_TESTER
            "ROLE_TESTER" => array(
                "Tester",
                "Allow using testing server without redirection",
                1
            ),

            //////////// Scanorder roles ////////////
            "ROLE_SCANORDER_ADMIN" => array(
                "ScanOrder Administrator",
                "Full access for Scan Order site",
                90
            ),
            "ROLE_SCANORDER_PROCESSOR" => array(
                "ScanOrder Processor",
                "Allow to view all orders and change scan order status",
                50
            ),

            "ROLE_SCANORDER_DATA_QUALITY_ASSURANCE_SPECIALIST" => array(
                "ScanOrder Data Quality Assurance Specialist",
                "Allow to make data quality modification",
                50
            ),

            "ROLE_SCANORDER_DIVISION_CHIEF" => array(
                "ScanOrder Division Chief",
                "Allow to view and amend all orders for this division(institution)",
                40
            ),  //view or modify all orders of the same division(institution)
            "ROLE_SCANORDER_SERVICE_CHIEF" => array(
                "ScanOrder Service Chief",
                "Allow to view and amend all orders for this service",
                30
            ),


            //"ROLE_USER" => "User", //this role must be always assigned to the authenticated user. Required by fos user bundle.

            "ROLE_SCANORDER_SUBMITTER" => array(
                "ScanOrder Submitter",
                "Allow submit new orders, amend own order",
                10
            ),
            "ROLE_SCANORDER_ORDERING_PROVIDER" => array(
                "ScanOrder Ordering Provider",
                "Allow submit new orders, amend own order",
                10
            ),

            "ROLE_SCANORDER_PATHOLOGY_FELLOW" => array(
                "ScanOrder Pathology Fellow",
                "",
                10
            ),
            "ROLE_SCANORDER_PATHOLOGY_FACULTY" => array(
                "ScanOrder Pathology Faculty",
                "",
                10
            ),

            "ROLE_SCANORDER_COURSE_DIRECTOR" => array(
                "ScanOrder Course Director",
                "Allow to be a Course Director in Educational orders",
                10
            ),
            "ROLE_SCANORDER_PRINCIPAL_INVESTIGATOR" => array(
                "ScanOrder Principal Investigator",
                "Allow to be a Principal Investigator in Research orders",
                10
            ),

            "ROLE_SCANORDER_ONCALL_TRAINEE" => array(
                "OrderPlatform On Call Trainee",
                "Allow to see the phone numbers & email of Home location",
                10
            ),
            "ROLE_SCANORDER_ONCALL_ATTENDING" => array(
                "OrderPlatform On Call Attending",
                "Allow to see the phone numbers & email of Home location",
                10
            ),

            "ROLE_PLATFORM_DEMO" => array(
                "Platform Demo",
                "The same as ROLE_PLATFORM_DEPUTY_ADMIN but names are replaced by Demo Applicant for Fellowship Application Site",
                5
            ),

            "ROLE_SCANORDER_UNAPPROVED" => array(
                "ScanOrder Unapproved User",
                "Does not allow to visit Scan Order site",
                0
            ),
            "ROLE_SCANORDER_BANNED" => array(
                "ScanOrder Banned User",
                "Does not allow to visit Scan Order site",
                -1
            ),


            //////////// EmployeeDirectory roles ////////////
            "ROLE_USERDIRECTORY_ADMIN" => array(
                "EmployeeDirectory Administrator",
                "Full access for Employee Directory site",
                90
            ),
            "ROLE_USERDIRECTORY_EDITOR" => array(
                "EmployeeDirectory Editor",
                "Allow to edit all employees; Can not change roles for users, but can grant access via access requests",
                50
            ),
            "ROLE_USERDIRECTORY_OBSERVER" => array(
                "EmployeeDirectory Observer",
                "Allow to view all employees",
                10
            ),
            "ROLE_USERDIRECTORY_BANNED" => array(
                "EmployeeDirectory Banned User",
                "Does not allow to visit Employee Directory site",
                -1
            ),
            "ROLE_USERDIRECTORY_UNAPPROVED" => array(
                "EmployeeDirectory Unapproved User",
                "Does not allow to visit Employee Directory site",
                0
            ),


            //////////// FellApp roles ////////////
            "ROLE_FELLAPP_ADMIN" => array(
                "Fellowship Applications Administrator",
                "Full access for Fellowship Applications site",
                90
            ),
            //Directors (7 types)
//            "ROLE_FELLAPP_DIRECTOR" => array(
//                "Fellowship Program General Director Role",
//                "Access to Fellowship Application type as Director (edit application,upload new documents)",
//                50
//            ),
            "ROLE_FELLAPP_DIRECTOR_WCMC_BREASTPATHOLOGY" => array(
                "Fellowship Program Director WCMC Breast Pathology",
                "Access to specific Fellowship Application type as Director",
                50
            ),
            "ROLE_FELLAPP_DIRECTOR_WCMC_CYTOPATHOLOGY" => array(
                "Fellowship Program Director WCMC Cytopathology",
                "Access to specific Fellowship Application type as Director",
                50
            ),
            "ROLE_FELLAPP_DIRECTOR_WCMC_GYNECOLOGICPATHOLOGY" => array(
                "Fellowship Program Director WCMC Gynecologic Pathology",
                "Access to specific Fellowship Application type as Director",
                50
            ),
            "ROLE_FELLAPP_DIRECTOR_WCMC_GASTROINTESTINALPATHOLOGY" => array(
                "Fellowship Program Director WCMC Gastrointestinal Pathology",
                "Access to specific Fellowship Application type as Director",
                50
            ),
            "ROLE_FELLAPP_DIRECTOR_WCMC_GENITOURINARYPATHOLOGY" => array(
                "Fellowship Program Director WCMC Genitourinary Pathology",
                "Access to specific Fellowship Application type as Director",
                50
            ),
            "ROLE_FELLAPP_DIRECTOR_WCMC_HEMATOPATHOLOGY" => array(
                "Fellowship Program Director WCMC Hematopathology",
                "Access to specific Fellowship Application type as Director",
                50
            ),
            "ROLE_FELLAPP_DIRECTOR_WCMC_MOLECULARGENETICPATHOLOGY" => array(
                "Fellowship Program Director WCMC Molecular Genetic Pathology",
                "Access to specific Fellowship Application type as Director",
                50
            ),
            //Program-Coordinator (7 types)
//            "ROLE_FELLAPP_COORDINATOR" => array(
//                "Fellowship Program General Coordinator Role",
//                "Access to Fellowship Application type as Coordinator (edit application,upload new documents)",
//                40
//            ),
            "ROLE_FELLAPP_COORDINATOR_WCMC_BREASTPATHOLOGY" => array(
                "Fellowship Program Coordinator WCMC Breast Pathology",
                "Access to specific Fellowship Application type as Coordinator",
                40
            ),
            "ROLE_FELLAPP_COORDINATOR_WCMC_CYTOPATHOLOGY" => array(
                "Fellowship Program Coordinator WCMC Cytopathology",
                "Access to specific Fellowship Application type as Coordinator",
                40
            ),
            "ROLE_FELLAPP_COORDINATOR_WCMC_GYNECOLOGICPATHOLOGY" => array(
                "Fellowship Program Coordinator WCMC Gynecologic Pathology",
                "Access to specific Fellowship Application type as Coordinator",
                40
            ),
            "ROLE_FELLAPP_COORDINATOR_WCMC_GASTROINTESTINALPATHOLOGY" => array(
                "Fellowship Program Coordinator WCMC Gastrointestinal Pathology",
                "Access to specific Fellowship Application type as Coordinator",
                40
            ),
            "ROLE_FELLAPP_COORDINATOR_WCMC_GENITOURINARYPATHOLOGY" => array(
                "Fellowship Program Coordinator WCMC Genitourinary Pathology",
                "Access to specific Fellowship Application type as Coordinator",
                40
            ),
            "ROLE_FELLAPP_COORDINATOR_WCMC_HEMATOPATHOLOGY" => array(
                "Fellowship Program Coordinator WCMC Hematopathology",
                "Access to specific Fellowship Application type as Coordinator",
                40
            ),
            "ROLE_FELLAPP_COORDINATOR_WCMC_MOLECULARGENETICPATHOLOGY" => array(
                "Fellowship Program Coordinator WCMC Molecular Genetic Pathology",
                "Access to specific Fellowship Application type as Coordinator",
                40
            ),
            //Fellowship Interviewer
//            "ROLE_FELLAPP_INTERVIEWER" => array(
//                "Fellowship Program General Interviewer Role",
//                "Access to Fellowship Application type as Interviewer (able to view, create and update the interview form)",
//                30
//            ),
            "ROLE_FELLAPP_INTERVIEWER_WCMC_BREASTPATHOLOGY" => array(
                "Fellowship Program Interviewer WCMC Breast Pathology",
                "Access to specific Fellowship Application type as Interviewer",
                30
            ),
            "ROLE_FELLAPP_INTERVIEWER_WCMC_CYTOPATHOLOGY" => array(
                "Fellowship Program Interviewer WCMC Cytopathology",
                "Access to specific Fellowship Application type as Interviewer",
                30
            ),
            "ROLE_FELLAPP_INTERVIEWER_WCMC_GYNECOLOGICPATHOLOGY" => array(
                "Fellowship Program Interviewer WCMC Gynecologic Pathology",
                "Access to specific Fellowship Application type as Interviewer",
                30
            ),
            "ROLE_FELLAPP_INTERVIEWER_WCMC_GASTROINTESTINALPATHOLOGY" => array(
                "Fellowship Program Interviewer WCMC Gastrointestinal Pathology",
                "Access to specific Fellowship Application type as Interviewer",
                30
            ),
            "ROLE_FELLAPP_INTERVIEWER_WCMC_GENITOURINARYPATHOLOGY" => array(
                "Fellowship Program Interviewer WCMC Genitourinary Pathology",
                "Access to specific Fellowship Application type as Interviewer",
                30
            ),
            "ROLE_FELLAPP_INTERVIEWER_WCMC_HEMATOPATHOLOGY" => array(
                "Fellowship Program Interviewer WCMC Hematopathology",
                "Access to specific Fellowship Application type as Interviewer",
                30
            ),
            "ROLE_FELLAPP_INTERVIEWER_WCMC_MOLECULARGENETICPATHOLOGY" => array(
                "Fellowship Program Interviewer WCMC Molecular Genetic Pathology",
                "Access to specific Fellowship Application type as Interviewer",
                30
            ),

            //Fellowship Observer
            "ROLE_FELLAPP_OBSERVER" => array(
                "Fellowship Program Observer",
                "Access to Fellowship Application as Observer (able to view a particular (assigned) application)",
                10
            ),

//            "ROLE_FELLAPP_USER" => array(
//                "Fellowship Applications User",
//                "Allow to view the Fellowship Applications site",
//                5
//            ),
            "ROLE_FELLAPP_BANNED" => array(
                "Fellowship Applications Banned User",
                "Does not allow to visit Fellowship Applications site",
                -1
            ),
            "ROLE_FELLAPP_UNAPPROVED" => array(
                "Fellowship Applications Unapproved User",
                "Does not allow to visit Fellowship Applications site",
                0
            ),

            //////////// Deidentifier roles ////////////
            "ROLE_DEIDENTIFICATOR_ADMIN" => array(
                "Deidentifier Administrator",
                "Full access for Deidentifier site",
                90
            ),

            "ROLE_DEIDENTIFICATOR_WCMC_NYP_HONEST_BROKER" => array(
                "WCMC-NYP Deidentifier Honest Broker",
                "Can search and generate",
                50
            ),
            "ROLE_DEIDENTIFICATOR_WCMC_NYP_ENQUIRER" => array(
                "WCMC-NYP Deidentifier Enquirer",
                "Can search, but not generate",
                40
            ),
            "ROLE_DEIDENTIFICATOR_WCMC_NYP_GENERATOR" => array(
                "WCMC-NYP Deidentifier Generator",
                "Can generate, but not search",
                10
            ),

            "ROLE_DEIDENTIFICATOR_BANNED" => array(
                "Deidentifier Banned User",
                "Does not allow to visit Deidentifier site",
                -1
            ),
            "ROLE_DEIDENTIFICATOR_UNAPPROVED" => array(
                "Deidentifier Unapproved User",
                "Does not allow to visit Deidentifier site",
                0
            ),


            //////////// VACREQ roles ////////////
            "ROLE_VACREQ_ADMIN" => array(
                "Vacation Request Administrator",
                "Full access for Vacation Request site",
                90
            ),

//            "ROLE_VACREQ_APPROVER" => array(
//                "Vacation Request Approver",
//                "Can search and approve vacation requests",
//                50
//            ),
            "ROLE_VACREQ_APPROVER_EXECUTIVE" => array(
                "Vacation Request Approver for the Executive Committee",
                "Can search and approve vacation requests",
                50
            ),
            "ROLE_VACREQ_APPROVER_CLINICALPATHOLOGY" => array(
                "Vacation Request Approver for the Clinical Pathology",
                "Can search and approve vacation requests for specified service",
                50
            ),
            "ROLE_VACREQ_APPROVER_EXPERIMENTALPATHOLOGY" => array(
                "Vacation Request Approver for the Experimental Pathology",
                "Can search and approve vacation requests for specified service",
                50
            ),
            "ROLE_VACREQ_APPROVER_VASCULARBIOLOGY" => array(
                "Vacation Request Approver for the Vascular Biology",
                "Can search and approve vacation requests for specified service",
                50
            ),
            "ROLE_VACREQ_APPROVER_HEMATOPATHOLOGY" => array(
                "Vacation Request Approver for the Hematopathology",
                "Can search and approve vacation requests for specified service",
                50
            ),
            "ROLE_VACREQ_APPROVER_SURGICALPATHOLOGY" => array(
                "Vacation Request Approver for the Surgical Pathology",
                "Can search and approve vacation requests for specified service",
                50
            ),
            "ROLE_VACREQ_APPROVER_CYTOPATHOLOGY" => array(
                "Vacation Request Approver for the Cytopathology",
                "Can search and approve vacation requests for specified service",
                50
            ),
            "ROLE_VACREQ_APPROVER_DERMATOPATHOLOGY" => array(
                "Vacation Request Approver for the Dermatopathology",
                "Can search and approve vacation requests for specified service",
                50
            ),

            "ROLE_VACREQ_SUPERVISOR_WCMC_PATHOLOGY" => array(
                "Vacation Request Supervisor - WCMC Pathology Department",
                "Can search and approve carry over requests for Department of Pathology and Laboratory Medicine(WCMC)",
                40
            ),

            "ROLE_VACREQ_OBSERVER_WCMC_PATHOLOGY" => array(
                "Vacation Request Observer for WCMC Department of Pathology and Laboratory Medicine",
                "This role should allow the user to log into the Vacation Request site and,
                if this is the only role the user has on the Vacation Request Site,
                be instantly redirected to the Away Calendar page.
                No access should be provided to the Homepage, Incoming Requests, Group Management, My Group, etc.",
                40
            ),

//            "ROLE_VACREQ_SUBMITTER" => array(
//                "Vacation Request Submitter",
//                "Can submit a vacation request",
//                30
//            ),
            "ROLE_VACREQ_SUBMITTER_EXECUTIVE" => array(
                "Vacation Request Submitter for the Executive Committee",
                "Can search and create vacation requests",
                30
            ),
            "ROLE_VACREQ_SUBMITTER_CLINICALPATHOLOGY" => array(
                "Vacation Request Submitter for the Clinical Pathology",
                "Can search and create vacation requests for specified service",
                30
            ),
            "ROLE_VACREQ_SUBMITTER_EXPERIMENTALPATHOLOGY" => array(
                "Vacation Request Submitter for the Experimental Pathology",
                "Can search and create vacation requests for specified service",
                30
            ),
            "ROLE_VACREQ_SUBMITTER_VASCULARBIOLOGY" => array(
                "Vacation Request Submitter for the Vascular Biology",
                "Can search and create vacation requests for specified service",
                30
            ),
            "ROLE_VACREQ_SUBMITTER_HEMATOPATHOLOGY" => array(
                "Vacation Request Submitter for the Hematopathology",
                "Can search and create vacation requests for specified service",
                30
            ),
            "ROLE_VACREQ_SUBMITTER_SURGICALPATHOLOGY" => array(
                "Vacation Request Submitter for the Surgical Pathology",
                "Can search and create vacation requests for specified service",
                30
            ),
            "ROLE_VACREQ_SUBMITTER_CYTOPATHOLOGY" => array(
                "Vacation Request Submitter for the Cytopathology",
                "Can search and create vacation requests for specified service",
                30
            ),
            "ROLE_VACREQ_SUBMITTER_DERMATOPATHOLOGY" => array(
                "Vacation Request Submitter for the Dermatopathology",
                "Can search and create vacation requests for specified service",
                30
            ),

            "ROLE_VACREQ_BANNED" => array(
                "Vacation Request Banned User",
                "Does not allow to visit Vacation Request site",
                -1
            ),
            "ROLE_VACREQ_UNAPPROVED" => array(
                "Vacation Request Unapproved User",
                "Does not allow to visit Vacation Request site",
                0
            ),

            //////////// CALLLOG roles ////////////
            "ROLE_CALLLOG_ADMIN" => array(
                "Call Logbook Administrator",
                "Full access for  Call Logbook site",
                90
            ),

            "ROLE_CALLLOG_DATA_QUALITY" => array(
                "Data Quality Manager for WCMC-NYP",
                "Merge or un-merge patient records",
                60
            ),

            "ROLE_CALLLOG_PATHOLOGY_RESIDENT" => array(
                "Pathology Resident",
                "",
                50
            ),
            "ROLE_CALLLOG_PATHOLOGY_FELLOW" => array(
                "Pathology Fellow",
                "",
                50
            ),
            "ROLE_CALLLOG_PATHOLOGY_ATTENDING" => array(
                "Pathology Attending",
                "",
                50
            ),

            "ROLE_CALLLOG_USER" => array(
                "Call Logbook User",
                "Can create, edit and read call book entries",
                30
            ),

        );

        $username = $this->get('security.context')->getToken()->getUser();

        $count = 10;
        foreach( $types as $role => $aliasDescription ) {

            $alias = $aliasDescription[0];
            $description = $aliasDescription[1];
            $level = $aliasDescription[2];

            $entity = $em->getRepository('OlegUserdirectoryBundle:Roles')->findOneByName(trim($role));

            if( $entity ) {
                if( !$entity->getLevel() ) {
                    $entity->setLevel($level);
                    $em->persist($entity);
                    $em->flush();
                }
                continue;
            }

            if( !$entity ) {
                $entity = new Roles();
                $this->setDefaultList($entity,$count,$username,null);
            }

            $entity->setName( trim($role) );
            $entity->setAlias( trim($alias) );
            $entity->setDescription( trim($description) );
            $entity->setLevel($level);

            $attrName = "Call Pager";

            //set attributes for ROLE_SCANORDER_ONCALL_TRAINEE
            if( $role == "ROLE_SCANORDER_ONCALL_TRAINEE" ) {
                $attrValue = "(111) 111-1111";
                $attrs = $em->getRepository('OlegUserdirectoryBundle:RoleAttributeList')->findBy(array("name"=>$attrName,"value"=>$attrValue));
                if( count($attrs) == 0 ) {
                    $attr = new RoleAttributeList();
                    $this->setDefaultList($attr,1,$username,$attrName);
                    $attr->setValue($attrValue);
                    $entity->addAttribute($attr);
                }
            }
            //set attributes for ROLE_SCANORDER_ONCALL_ATTENDING
            if( $role == "ROLE_SCANORDER_ONCALL_ATTENDING" ) {
                $attrValue = "(222) 222-2222";
                $attrs = $em->getRepository('OlegUserdirectoryBundle:RoleAttributeList')->findBy(array("name"=>$attrName,"value"=>$attrValue));
                if( count($attrs) == 0 ) {
                    $attr = new RoleAttributeList();
                    $this->setDefaultList($attr,10,$username,$attrName);
                    $attr->setValue($attrValue);
                    $entity->addAttribute($attr);
                }
            }

            //set institution and Fellowship Subspecialty types to role
            $this->setInstitutionFellowship($entity,$role);

            $em->persist($entity);
            $em->flush();

            $count = $count + 10;

        } //foreach

        return round($count/10);
    }

    //entity - role object
    //role - role string
    public function setInstitutionFellowship($entity,$role) {

        if( strpos($role,'_WCMC_') === false ) {
            return;
        }

        $em = $this->getDoctrine()->getManager();

        $wcmc = $em->getRepository('OlegUserdirectoryBundle:Institution')->findOneByAbbreviation("WCMC");
        $entity->setInstitution($wcmc);

        if( strpos($role,'BREASTPATHOLOGY') !== false ) {
            $BREASTPATHOLOGY = $em->getRepository('OlegUserdirectoryBundle:FellowshipSubspecialty')->findOneByName("Breast Pathology");
            $entity->setFellowshipSubspecialty($BREASTPATHOLOGY);
        }

        if( strpos($role,'CYTOPATHOLOGY') !== false ) {
            $CYTOPATHOLOGY = $em->getRepository('OlegUserdirectoryBundle:FellowshipSubspecialty')->findOneByName("Cytopathology");
            $entity->setFellowshipSubspecialty($CYTOPATHOLOGY);
        }

        if( strpos($role,'GYNECOLOGICPATHOLOGY') !== false ) {
            $GYNECOLOGICPATHOLOGY = $em->getRepository('OlegUserdirectoryBundle:FellowshipSubspecialty')->findOneByName("Gynecologic Pathology");
            $entity->setFellowshipSubspecialty($GYNECOLOGICPATHOLOGY);
        }

        if( strpos($role,'GASTROINTESTINALPATHOLOGY') !== false ) {
            $GASTROINTESTINALPATHOLOGY = $em->getRepository('OlegUserdirectoryBundle:FellowshipSubspecialty')->findOneByName("Gastrointestinal Pathology");
            $entity->setFellowshipSubspecialty($GASTROINTESTINALPATHOLOGY);
        }

        if( strpos($role,'GENITOURINARYPATHOLOGY') !== false ) {
            $GENITOURINARYPATHOLOGY = $em->getRepository('OlegUserdirectoryBundle:FellowshipSubspecialty')->findOneByName("Genitourinary Pathology");
            $entity->setFellowshipSubspecialty($GENITOURINARYPATHOLOGY);
        }

        if( strpos($role,'HEMATOPATHOLOGY') !== false ) {
            $HEMATOPATHOLOGY = $em->getRepository('OlegUserdirectoryBundle:FellowshipSubspecialty')->findOneByName("Hematopathology");
            $entity->setFellowshipSubspecialty($HEMATOPATHOLOGY);
        }

        if( strpos($role,'MOLECULARGENETICPATHOLOGY') !== false ) {
            $MOLECULARGENETICPATHOLOGY = $em->getRepository('OlegUserdirectoryBundle:FellowshipSubspecialty')->findOneByName("Molecular Genetic Pathology");
            $entity->setFellowshipSubspecialty($MOLECULARGENETICPATHOLOGY);
        }

    }

    //entity - role object
    public function setInstitutionVacReqRole($entity) {

        //role - role string
        $role = $entity->getName()."";

        if( strpos($role,'_VACREQ_') === false ) {
            return;
        }

        $em = $this->getDoctrine()->getManager();
        $wcmc = $em->getRepository('OlegUserdirectoryBundle:Institution')->findOneByAbbreviation("WCMC");

        //should be 8:

        //create "Executive Committee" in the Pathology Department and name the type of that group "Committee":
        //Create organizational group "Committee" with default level -2, because all other levels are taken by regular tree elements
        //-2 is mirroring of the same level 2 - "Division". This solution should work and don not cause any errors.
        //Other solution is to remove restriction for level uniqueness in the organizational group object. But, how it will affect the logic?
        //EXECUTIVE: Executive Committee
        $this->vacreqRoleSetSingleUserInstitution($entity,"EXECUTIVE",$wcmc,"Executive Committee","cwid");

        //CLINICALPATHOLOGY: Laboratory Medicine
        $this->vacreqRoleSetSingleUserInstitution($entity,"CLINICALPATHOLOGY",$wcmc,"Laboratory Medicine","cwid");

        //EXPERIMENTALPATHOLOGY (Barry Sleckman): Experimental Pathology (create new under WCMC => Pathology and Laboratory Medicine)
        //bas2022@med.cornell.edu
        // +1 212 746 4842
        //Pathology and Laboratory Medicine (WCMC)
        $this->vacreqRoleSetSingleUserInstitution($entity,"EXPERIMENTALPATHOLOGY",$wcmc,"Experimental Pathology","cwid");

        //VASCULARBIOLOGY : "Vascular Biology" (in NYP onlys. Create a new under WCMC => Pathology and Laboratory Medicine => Research)
        $this->vacreqRoleSetSingleUserInstitution($entity,"VASCULARBIOLOGY",$wcmc,"Vascular Biology","cwid");

        //HEMATOPATHOLOGY : "Hematopathology" - use division, not service
        $this->vacreqRoleSetSingleUserInstitution($entity,"HEMATOPATHOLOGY",$wcmc,"Hematopathology","cwid");

        //SURGICALPATHOLOGY : Anatomic Pathology
        $this->vacreqRoleSetSingleUserInstitution($entity,"SURGICALPATHOLOGY",$wcmc,"Anatomic Pathology","cwid");

        //CYTOPATHOLOGY : Cytopathology
        $this->vacreqRoleSetSingleUserInstitution($entity,"CYTOPATHOLOGY",$wcmc,"Cytopathology","cwid");

        //DERMATOPATHOLOGY : Dermatopathology
        $this->vacreqRoleSetSingleUserInstitution($entity,"DERMATOPATHOLOGY",$wcmc,"Dermatopathology","cwid");

        //SUPERVISOR : Pathology and Laboratory Medicine
        $this->vacreqRoleSetSingleUserInstitution($entity,"SUPERVISOR",$wcmc,"Pathology and Laboratory Medicine","cwid");

        return 0;
    }
    //Assign Institution to a Role Object
    //$VacReqGroupStr: "DERMATOPATHOLOGY" string
    //$root: $wcmc object
    //$instName: "Dermatology" institution name string
    public function vacreqRoleSetSingleUserInstitution($entity,$VacReqGroupStr,$root,$instName,$cwid) {
        $em = $this->getDoctrine()->getManager();
        $role = $entity->getName()."";
        //echo "role=".$role."<br>";
        //DERMATOPATHOLOGY: ?
        if( strpos($role,$VacReqGroupStr) !== false ) {
            $groupObject = $em->getRepository('OlegUserdirectoryBundle:Institution')->findNodeByNameAndRoot($root->getId(),$instName);
            if( !$groupObject ) {
                exit($root.": no child found with name=".$instName);
            }
            $entity->setInstitution($groupObject);

            //assign approver APPROVER
            //echo "cwid=".$cwid."<br>";
            if( $cwid && strpos($role,"ROLE_VACREQ_APPROVER") !== false ) {
                $approver = $em->getRepository('OlegUserdirectoryBundle:User')->findOneByPrimaryPublicUserId($cwid);
                //echo "approver=".$approver."<br>";
                if( $approver ) {
                    $approver->addRole($entity);
                    $em->flush($approver);
                    //echo "user found by cwid=".$cwid."<br>";
                } else {
                    //exit("user not found by cwid=".$cwid);
                }
            }

            //assign SUPERVISOR
            if( $cwid && strpos($role,"ROLE_VACREQ_SUPERVISOR") !== false ) {
                $supervisor = $em->getRepository('OlegUserdirectoryBundle:User')->findOneByPrimaryPublicUserId($cwid);
                //echo "supervisor=".$supervisor."<br>";
                if( $supervisor ) {
                    $supervisor->addRole($entity);
                    $em->flush($supervisor);
                    //echo "user found by cwid=".$cwid."<br>";
                } else {
                    //exit("user not found by cwid=".$cwid);
                }
            }

            //Don't use this method: Use Role's Institution to link a role and VacReqRequest's Institution
            //Create and add appropriate permission to this role:
            //Permission Holder: "" - Permission: "Submit a Vacation Request", Institution(s): ""
        }
    }

    public function generateSiteParameters() {

        $em = $this->getDoctrine()->getManager();
        $entities = $em->getRepository('OlegUserdirectoryBundle:SiteParameters')->findAll();

        if( $entities ) {
            return -1;
        }

        $types = array(
            "maxIdleTime" => "30",
            "environment" => "dev",
            "siteEmail" => "", //"slidescan@med.cornell.edu",

            "smtpServerAddress" => "",

            "aDLDAPServerAddress" => "",
            "aDLDAPServerPort" => "",
            "aDLDAPServerOu" => "",    //used for DC
            "aDLDAPServerAccountUserName" => "",
            "aDLDAPServerAccountPassword" => "",
            "ldapExePath" => "../src/Oleg/UserdirectoryBundle/Util/",
            "ldapExeFilename" => "LdapSaslCustom.exe",

            "dbServerAddress" => "127.0.0.1",
            "dbServerPort" => "null",
            "dbServerAccountUserName" => "",
            "dbServerAccountPassword" => "",
            "dbDatabaseName" => "",

            "aperioeSlideManagerDBServerAddress" => "127.0.0.1",
            "aperioeSlideManagerDBServerPort" => "null",
            "aperioeSlideManagerDBUserName" => "",
            "aperioeSlideManagerDBPassword" => "",
            "aperioeSlideManagerDBName" => "",

            "institutionurl" => "http://www.cornell.edu/",
            "institutionname" => "Cornell University",
            "subinstitutionurl" => "http://weill.cornell.edu",
            "subinstitutionname" => "Weill Cornell Medicine",
            "departmenturl" => "http://www.cornellpathology.com",
            "departmentname" => "Pathology and Laboratory Medicine Department",

            ///////////////////// FELLAPP /////////////////////
            "codeGoogleFormFellApp" => "",
            "confirmationEmailFellApp" => "",
            "confirmationSubjectFellApp" => "Your WCMC/NYP fellowship application has been succesfully received",
            "confirmationBodyFellApp" => "Thank You for submitting the fellowship application to Weill Cornell Medical College/NewYork Presbyterian Hospital. ".
                                         "Once we receive the associated recommendation letters, your application will be reviewed and considered. ". 
                                         "If You have any questions, please do not hesitate to contact me by phone or via email. ".
                                         "Sincerely, Jessica Misner Fellowship Program Coordinator Weill Cornell Medicine Pathology and Laboratory Medicine 1300 York Avenue, Room C-302 T 212.746.6464 F 212.746.8192",
            "clientEmailFellApp" => '',
            "p12KeyPathFellApp" => 'E:\Program Files (x86)\Aperio\Spectrum\htdocs\order\scanorder\Scanorders2\src\Oleg\FellAppBundle\Util',
            "googleDriveApiUrlFellApp" => "https://www.googleapis.com/auth/drive https://spreadsheets.google.com/feeds",
            "userImpersonateEmailFellApp" => "olegivanov@pathologysystems.org",
            "templateIdFellApp" => "",
            "backupFileIdFellApp" => "",
            "folderIdFellApp" => "",
            "localInstitutionFellApp" => "Pathology Fellowship Programs (WCMC)",
            "deleteImportedAplicationsFellApp" => false,
            "deleteOldAplicationsFellApp" => false,
            "yearsOldAplicationsFellApp" => 2,
            "spreadsheetsPathFellApp" => "fellapp/Spreadsheets",
            "applicantsUploadPathFellApp" => "fellapp/FellowshipApplicantUploads",
            "reportsUploadPathFellApp" => "fellapp/Reports",
            "applicationPageLinkFellApp" => "http://wcmc.pathologysystems.org/fellowship-application",
            "libreOfficeConvertToPDFPathFellApp" => 'C:\Program Files (x86)\LibreOffice 5\program',
            "libreOfficeConvertToPDFFilenameFellApp" => "soffice",
            "libreOfficeConvertToPDFArgumentsdFellApp" => "--headless -convert-to pdf -outdir",
            "pdftkPathFellApp" => 'C:\Program Files (x86)\Aperio\Spectrum\htdocs\order\scanorder\Scanorders2\vendor\olegutil\PDFTKBuilderPortable\App\pdftkbuilder',
            "pdftkFilenameFellApp" => "pdftk",
            "pdftkArgumentsFellApp" => "###inputFiles### cat output ###outputFile### dont_ask",
            "gsPathFellApp" => "C:\Program Files (x86)\Aperio\Spectrum\htdocs\order\scanorder\Scanorders2\vendor\olegutil\Ghostscript\bin",
            "gsFilenameFellApp"=>"gswin64c.exe",
            "gsArgumentsFellApp"=>"-q -dNOPAUSE -dBATCH -sDEVICE=pdfwrite -sOutputFile= ###outputFile###  -c .setpdfwrite -f ###inputFiles###",
            ///////////////////// EOF FELLAPP /////////////////////

            "maintenance" => false,
            //"maintenanceenddate" => null,
            "maintenancelogoutmsg" =>   'The scheduled maintenance of this software has begun.'.
                                        ' The administrators are planning to return this site to a fully functional state on or before [[datetime]].'.
                                        'If you were in the middle of entering order information, it was saved as an "Unsubmitted" order '.
                                        'and you should be able to submit that order after the maintenance is complete.',
            "maintenanceloginmsg" =>    'The scheduled maintenance of this software has begun. The administrators are planning to return this site to a fully '.
                                        'functional state on or before [[datetime]]. If you were in the middle of entering order information, '.
                                        'it was saved as an "Unsubmitted" order and you should be able to submit that order after the maintenance is complete.',

            //uploads
            "avataruploadpath" => "directory/avatars",
            "employeesuploadpath" => "directory/documents",
            "scanuploadpath" => "scan-order/documents",
            "fellappuploadpath" => "fellapp/documents",
            "vacrequploadpath" => "directory/vacreq",

            "mainHomeTitle" => "Welcome to the O R D E R platform!",
            "listManagerTitle" => "List Manager",
            "eventLogTitle" => "Event Log",
            "siteSettingsTitle" => "Site Settings",
            "contentAboutPage" => '
                <p>
                    This site is built on the platform titled "O R D E R" (as in the opposite of disorder).
                </p>

                <p>
                    Designers: Victor Brodsky, Oleg Ivanov
                </p>

                <p>
                    Developer: Oleg Ivanov
                </p>

                <p>
                    Quality Assurance Testers: Oleg Ivanov, Steven Bowe, Emilio Madrigal
                </p>

                <p>
                    We are continuing to improve this software. If you have a suggestion or believe you have encountered an issue, please don\'t hesitate to email
                <a href="mailto:slidescan@med.cornell.edu" target="_top">slidescan@med.cornell.edu</a> and attach relevant screenshots.
                </p>

                <br>

                <p>
                O R D E R is made possible by:
                </p>

                <br>

                <p>

                        <ul>


                    <li>
                        <a href="http://php.net">PHP</a>
                    </li>

                    <br>

                    <li>
                        <a href="http://symfony.com">Symfony</a>
                    </li>

                    <br>

                    <li>
                        <a href="http://doctrine-project.org">Doctrine</a>
                    </li>

                    <br>                  
					
					<li>
                        <a href="https://msdn.microsoft.com/en-us/library/aa366156.aspx">MSDN library: ldap_bind_s</a>
                    </li>

                    <br>

                    <li>
                        <a href="https://github.com/symfony/SwiftmailerBundle">SwiftmailerBundle</a>
                    </li>

                    <br>

                    <li>
                        <a href="https://github.com/symfony/AsseticBundle">AsseticBundle</a>
                    </li>

                    <br>

                    <li>
                        <a href="https://github.com/FriendsOfSymfony/FOSUserBundle">FOSUserBundle</a>
                    </li>

                    <br>

                    <li>
                        <a href="http://phpexcel.codeplex.com/">PHP Excel</a>
                    </li>

                    <br>

                    <li>

                        <a href="https://github.com/1up-lab/OneupUploaderBundle">OneupUploaderBundle</a>
                    </li>

                    <br>

                    <li>
                        <a href="http://www.dropzonejs.com/">Dropzone JS</a>
                    </li>

                    <br>

                    <li>
                        <a href="http://www.jstree.com/">jsTree</a>
                    </li>

                    <br>

                    <li>
                        <a href="https://github.com/KnpLabs/KnpPaginatorBundle">KnpPaginatorBundle</a>
                    </li>

                    <br>

                    <li>
                        <a href="http://twig.sensiolabs.org/doc/advanced.html">Twig</a>
                    </li>

                    <br>

                    <li>
                        <a href="http://getbootstrap.com/">Bootstrap</a>
                    </li>

                    <br>

                    <li>
                        <a href="https://github.com/kriskowal/q">JS promises Q</a>
                    </li>

                    <br>

                    <li>
                        <a href="http://jquery.com">jQuery</a>
                    </li>

                    <br>

                    <li>
                        <a href="http://jqueryui.com/">jQuery UI</a>
                    </li>

                    <br>

                    <li>
                        <a href="https://github.com/RobinHerbots/jquery.inputmask">jQuery Inputmask</a>
                    </li>

                    <br>

                    <li>
                        <a href="http://ivaynberg.github.io/select2/">Select2</a>
                    </li>

                    <br>

                    <li>
                        <a href="http://www.eyecon.ro/bootstrap-datepicker/">Bootstrap Datepicker</a>
                    </li>

                    <br>

                    <li>
                        <a href="http://www.malot.fr/bootstrap-datetimepicker/demo.php">Bootstrap DateTime Picker</a>
                    </li>

                    <br>

                    <li>
                        <a href="https://github.com/twitter/typeahead.js/">Typeahead with Bloodhound</a>
                    </li>

                    <br>

                    <li>
                        <a href="http://fengyuanchen.github.io/cropper/">Image Cropper</a>
                    </li>

                    <br>

                    <li>
                        <a href="http://handsontable.com/">Handsontable</a>
                    </li>

                    <br>

                    <li>
                        <a href="https://github.com/KnpLabs/KnpSnappyBundle">KnpSnappyBundle with wkhtmltopdf</a>
                    </li>

                     <br>

                    <li>
                        <a href="https://www.libreoffice.org/">LibreOffice</a>
                    </li>

                    <br>

                    <li>
                        <a href="https://github.com/myokyawhtun/PDFMerger">PDFMerger</a>
                    </li>

                    <br>

                    <li>
                        <a href="https://www.pdflabs.com/tools/pdftk-the-pdf-toolkit/">PDFTK</a>
                    </li>

                    <br>

                    <li>
                        <a href="http://www.ghostscript.com/">Ghostscript</a>
                    </li>

                    <br>

                    <li>
                        <a href="https://github.com/bermi/password-generator">Password Generator</a>
                    </li>

                    <br>

                    <li>
                        <a href="https://github.com/andreausu/UsuScryptPasswordEncoderBundle">Password Encoder</a>
                    </li>

                    <br>

                    <li>
                        <a href="https://github.com/adesigns/calendar-bundle">jQuery FullCalendar bundle</a>
                    </li>

                    <li>
                        <a href="https://sciactive.com/pnotify/">PNotify JavaScript notifications</a>
                    </li>

                </ul>
                </p>
            '
            //"underLoginMsgUser" => "",
            //"underLoginMsgScan => ""

        );

        $params = new SiteParameters();

        $count = 0;
        foreach( $types as $key => $value ) {
            $method = "set".$key;
            $params->$method( $value );
            $count = $count + 10;
        }

        //assign Institution
        $institutionName = 'Weill Cornell Medical College';
        $institution = $em->getRepository('OlegUserdirectoryBundle:Institution')->findOneByName($institutionName);
        if( !$institution ) {
            throw new \Exception( 'Institution was not found for name='.$institutionName );
        }
        $params->setAutoAssignInstitution($institution);

        //set AllowPopulateFellApp to false
        $params->setAllowPopulateFellApp(false);

        $em->persist($params);
        $em->flush();

        return round($count/10);
    }

    public function generateDefaultOrgGroupSiteParameters() {
        $em = $this->getDoctrine()->getManager();
        $entities = $em->getRepository('OlegUserdirectoryBundle:SiteParameters')->findAll();

        if( count($entities) != 1 ) {
            throw new \Exception( 'Must have only one parameter object. Found '.count($entities).'object(s)' );
        }

        $entity = $entities[0];

        $nyp = $em->getRepository('OlegUserdirectoryBundle:Institution')->findOneByAbbreviation("NYP");
        if( !$nyp ) {
            exit('No Institution: "NYP"');
        }

        $wcmc = $em->getRepository('OlegUserdirectoryBundle:Institution')->findOneByAbbreviation("WCMC");
        if( !$wcmc ) {
            exit('No Institution: "WCMC"');
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
            exit('No Institution: "Pathology and Laboratory Medicine"');
        }

        $pathDefaultGroup = null;

        foreach( $entity->getOrganizationalGroupDefaults() as $groupDefault ) {
            if( $groupDefault->getInstitution() ) {
              if( $groupDefault->getInstitution()->getId() == $pathology->getId() ) {
                  $pathDefaultGroup = $groupDefault;
                  break;
              }
            }
        }

        if( $pathDefaultGroup ) {
            return 0;
        }

        $pathDefaultGroup = new OrganizationalGroupDefault();

        //target Institution
        $pathDefaultGroup->setInstitution($pathology);

        //primaryPublicUserIdType
        $primaryPublicUserIdType = $em->getRepository('OlegUserdirectoryBundle:UsernameType')->findOneByName("WCMC CWID");
        if( !$primaryPublicUserIdType ) {
            exit('No UsernameType: "WCMC CWID"');
        }
        $pathDefaultGroup->setPrimaryPublicUserIdType($primaryPublicUserIdType);

        //email
        $pathDefaultGroup->setEmail("@med.cornell.edu");

        //roles
        //ROLE_SCANORDER_SUBMITTER
        $role = $em->getRepository('OlegUserdirectoryBundle:Roles')->findOneByName("ROLE_SCANORDER_SUBMITTER");
        if( !$role ) {
            exit('No Role: "ROLE_SCANORDER_SUBMITTER"');
        }
        $pathDefaultGroup->addRole($role);
        //ROLE_USERDIRECTORY_OBSERVER
        $role = $em->getRepository('OlegUserdirectoryBundle:Roles')->findOneByName("ROLE_USERDIRECTORY_OBSERVER");
        if( !$role ) {
            exit('No Role: "ROLE_USERDIRECTORY_OBSERVER"');
        }
        $pathDefaultGroup->addRole($role);
        //ROLE_VACREQ_OBSERVER_WCMC_PATHOLOGY
        $role = $em->getRepository('OlegUserdirectoryBundle:Roles')->findOneByName("ROLE_VACREQ_OBSERVER_WCMC_PATHOLOGY");
        if( !$role ) {
            exit('No Role: "ROLE_VACREQ_OBSERVER_WCMC_PATHOLOGY"');
        }
        $pathDefaultGroup->addRole($role);

        //timezone
        //$timezone = new \DateTimeZone('America/New_York');
        $pathDefaultGroup->setTimezone('America/New_York');

        //tooltipe
        $pathDefaultGroup->setTooltip(true);

        //showToInstitutions: WCMC, NYP
        $pathDefaultGroup->addShowToInstitution($wcmc);
        $pathDefaultGroup->addShowToInstitution($nyp);

        //defaultInstitution
        $pathDefaultGroup->setDefaultInstitution($wcmc);

        //permittedInstitutionalPHIScope: WCMC
        $pathDefaultGroup->addPermittedInstitutionalPHIScope($wcmc);

        //employmentType
        $employmentType = $em->getRepository('OlegUserdirectoryBundle:EmploymentType')->findOneByName("Full Time");
        if( !$employmentType ) {
            exit('No object: "Full Time"');
        }
        $pathDefaultGroup->setEmploymentType($employmentType);

        //locale: en_US - English (United States)
        $locale = $em->getRepository('OlegUserdirectoryBundle:LocaleList')->findOneByName("en_US");
        if( !$locale ) {
            exit('No object: "en_US"');
        }
        $pathDefaultGroup->setLocale($locale);

        //languages
        $language = $em->getRepository('OlegUserdirectoryBundle:LanguageList')->findOneByName("American English");
        if( !$language ) {
            exit('No object: "American English"');
        }
        $pathDefaultGroup->addLanguage($language);

        //administrativeTitleInstitution
        $pathDefaultGroup->setAdministrativeTitleInstitution($pathology);

        //academicTitleInstitution
        $pathDefaultGroup->setAcademicTitleInstitution($pathology);

        //medicalTitleInstitution
        $pathDefaultGroup->setMedicalTitleInstitution($pathology);

        //locationTypes
        $locationType = $em->getRepository('OlegUserdirectoryBundle:LocationTypeList')->findOneByName("Employee Office");
        if( !$locationType ) {
            exit('No object: "Employee Office"');
        }
        $pathDefaultGroup->addLocationType($locationType);

        //locationInstitution
        $pathDefaultGroup->setLocationInstitution($wcmc);

        //city
        $city = $em->getRepository('OlegUserdirectoryBundle:CityList')->findOneByName("New York");
        if( !$city ) {
            exit('No object: "New York"');
        }
        $pathDefaultGroup->setCity($city);

        //state
        $state = $em->getRepository('OlegUserdirectoryBundle:States')->findOneByName("New York");
        if( !$state ) {
            exit('No object: "New York"');
        }
        $pathDefaultGroup->setState($state);

        //zip
        $pathDefaultGroup->setZip("10065");

        //country
        $country = $em->getRepository('OlegUserdirectoryBundle:Countries')->findOneByName("United States");
        if( !$country ) {
            exit('No object: "United States"');
        }
        $pathDefaultGroup->setCountry($country);

        //medicalLicenseCountry
        $pathDefaultGroup->setMedicalLicenseCountry($country);

        //medicalLicenseState
        $pathDefaultGroup->setMedicalLicenseState($state);


        $entity->addOrganizationalGroupDefault($pathDefaultGroup);

        $em->persist($pathDefaultGroup);
        $em->flush();

        return 1;
    }


    public function generateSitenameList() {

        $em = $this->getDoctrine()->getManager();

        $elements = array(
            'directory' => 'employees',
            'scan' => 'scan',
            'fellowship-applications' => 'fellapp',
            'deidentifier' => 'deidentifier',
            'vacation-request' => 'vacreq',
            'call-log-book' => 'calllog'
        );


        $username = $this->get('security.context')->getToken()->getUser();

        $count = 10;
        foreach( $elements as $name => $abbreviation ) {

            $entity = $em->getRepository('OlegUserdirectoryBundle:SiteList')->findOneByName($name);
            if( $entity ) {
                continue;
            }

            $entity = new SiteList();
            $this->setDefaultList($entity,$count,$username,$name);

            $entity->setAbbreviation($abbreviation);

            $em->persist($entity);
            $em->flush();

            $count = $count + 10;

        } //foreach

        return round($count/10);

    }

    public function generateInstitutionTypes() {

        $em = $this->getDoctrine()->getManager();

        $elements = array(
            'Medical',
            'Educational',
            'Collaboration',
            'Research Lab'
        );


        $username = $this->get('security.context')->getToken()->getUser();

        $count = 10;
        foreach( $elements as $name ) {

            $entity = $em->getRepository('OlegUserdirectoryBundle:InstitutionType')->findOneByName($name);
            if( $entity ) {
                continue;
            }

            $entity = new InstitutionType();
            $this->setDefaultList($entity,$count,$username,$name);

            $em->persist($entity);
            $em->flush();

            $count = $count + 10;

        } //foreach

        return round($count/10);

    }

    public function generateOrganizationalGroupType() {

        $em = $this->getDoctrine()->getManager();
        $entities = $em->getRepository('OlegUserdirectoryBundle:OrganizationalGroupType')->findAll();

        if( $entities ) {
            return -1;
        }

        $elements = array(
            'Institution' => 0, //positive level - default level's title
            'Department' => 1,
            'Division' => 2,
            'Service' => 3,
            'Committee' => -21, //negative level - all other title: Committee is under Department, so it's -2. Additional index -21 is just for indication that this level has another title "Research Lab"
            'Research Lab' => -22
        );

        $username = $this->get('security.context')->getToken()->getUser();

        $count = 10;
        foreach( $elements as $name=>$level ) {

            $entity = new OrganizationalGroupType();
            $this->setDefaultList($entity,$count,$username,$name);

            $entity->setLevel($level);

            $em->persist($entity);
            $em->flush();

            $count = $count + 10;

        } //foreach

        return round($count/10);

    }

    //https://bitbucket.org/weillcornellpathology/scanorder/issue/221/multiple-office-locations-and-phone
    public function generateInstitutions() {

        $username = $this->get('security.context')->getToken()->getUser();

        $em = $this->getDoctrine()->getManager();

//        $entities = $em->getRepository('OlegUserdirectoryBundle:Institution')->findAll();
//
//        if( $entities ) {
//            return -1;
//        }
        ///////////////test
        //$levelInstitution = $em->getRepository('OlegUserdirectoryBundle:OrganizationalGroupType')->findOneByName('Institution');
        //$levelDepartment = $em->getRepository('OlegUserdirectoryBundle:OrganizationalGroupType')->findOneByName('Department');
        //$levelDivision = $em->getRepository('OlegUserdirectoryBundle:OrganizationalGroupType')->findOneByName('Division');
        //$levelService = $em->getRepository('OlegUserdirectoryBundle:OrganizationalGroupType')->findOneByName('Service');
//
//        $treeCount = 10;
//        $inst = new Institution();
//        $this->setDefaultList($inst,$treeCount,$username,'WCMC');
//        $inst->setOrganizationalGroupType($levelInstitution);
//        $treeCount++;
//
//        $pathdep = new Institution();
//        $this->setDefaultList($pathdep,$treeCount,$username,'Pathology');
//        $pathdep->setOrganizationalGroupType($levelDepartment);
//        $treeCount = $treeCount + 10;
//        $inst->addChild($pathdep);
//
//        $Biochemistry = new Institution();
//        $this->setDefaultList($Biochemistry,$treeCount,$username,'Biochemistry');
//        $Biochemistry->setOrganizationalGroupType($levelDepartment);
//        $treeCount = $treeCount + 10;
//        $inst->addChild($Biochemistry);
//
//        $division = new Institution();
//        $this->setDefaultList($division,$treeCount,$username,'Informatics');
//        $division->setOrganizationalGroupType($levelDivision);
//        $treeCount = $treeCount + 10;
//        $pathdep->addChild($division);
//
//        $service = new Institution();
//        $this->setDefaultList($service,$treeCount,$username,'Software Development');
//        $service->setOrganizationalGroupType($levelService);
//        $treeCount = $treeCount + 10;
//        $division->addChild($service);
//
//        $em->persist($inst);
//        $em->flush();
//        $repo = $em->getRepository('OlegUserdirectoryBundle:Institution');
//        $inst = $repo->findOneByName('WCMC');
//        $Anesthesiology = new Institution();
//        $this->setDefaultList($Anesthesiology,60,$username,'Anesthesiology');
//        $Anesthesiology->setOrganizationalGroupType($levelDepartment);
//        $repo->persistAsFirstChildOf($Anesthesiology,$inst);
//        $em->flush();
        //$node = $repo->findOneByName('Pathology');
//        $repo->removeFromTree($node);
        //$repo->moveUp($node, true);
        //exit();
        //echo $node."<br>";
        //echo $node->getPath();
        //$arrayTree = $repo->childrenHierarchy();
//        $htmlTree = $repo->childrenHierarchy(
//            null, /* starting from root nodes */
//            false, /* true: load all children, false: only direct */
//            array(
//                'decorate' => true,
//                'representationField' => 'slug',
//                'html' => true
//            )
//        );
//        echo $htmlTree;
//
//        exit('eof test');
        /////////////////////

        $entities = $em->getRepository('OlegUserdirectoryBundle:Institution')->findAll();

        if( $entities ) {
            return -1;
        }

        $wcmcDep = array(
            'Anesthesiology',
            'Biochemistry',
            'Cardiothoracic Surgery' => array(
                'Thoracic Surgery'
            ),
            'Cell and Developmental Biology' => null,
            'Dermatology' => null,
            'Feil Family Brain and Mind Research Institute',
            'Genetic Medicine' => null,
            'Healthcare Policy and Research' => array(
                'Biostatistics and Epidemiology',
                'Comparative Effectiveness and Outcomes Research',
                'Health Informatics',
                'Health Policy and Economics',
                'Health Systems Innovation and Implementation Science'
            ),
            'Microbiology and Immunology' => null,
            'Neurological Surgery' => null,
            'Neurology' => array(
                "Alzheimer's Disease & Memory Disorders",
                "Diagnostic Testing - Evoked Potentials, EEG & EMG",
                "Doppler (Transcranial and Carotid Duplex) Ultrasound Studies"              //continue
            ),
            'Obstetrics and Gynecology' => array(
                'General Ob/Gyn',
                'Gynecology',
                'Gynecologic Oncology'                                                     //continue
            ),
            'Ophthalmology' => null,
            'Orthopaedic Surgery' => null,
            'Otolaryngology - Head and Neck Surgery' => null,
            'Pathology and Laboratory Medicine' => array(
                'shortname' => 'Pathology',
                //divisions
                'Anatomic Pathology' => array(
                    //services
                    'Autopsy Pathology',
                    'Breast Pathology',
                    'Cardiopulmonary Pathology',
                    'Cytopathology',
                    'Dermatopathology',
                    'Gastrointestinal and Liver Pathology',
                    'Genitourinary Pathology',
                    'Gynecologic Pathology',
                    'Head and Neck Pathology',
                    'Hematopathology',
                    'Neuropathology',
                    'Pediatric Pathology',
                    'Perinatal and Obstetric Pathology',
                    'Renal Pathology',
                    'Surgical Pathology'
                ),
                'Hematopathology' => array(
                    'Immunopathology',
                    'Molecular Hematopathology'
                ),
                'Weill Cornell Pathology Consultation Services' => array(
                    'Breast Pathology',
                    'Dermatopathology',
                    'Gastrointestinal and Liver Pathology',
                    'Genitourinary Pathology',
                    'Gynecologic Pathology',
                    'Hematopathology',
                    'Perinatal and Obstetrical Pathology',
                    'Renal Pathology'
                ),
                'Laboratory Medicine' => array(
                    'Clinical Chemistry',
                    'Cytogenetics',
                    'Routine and special coagulation',
                    'Endocrinology',
                    'Routine and special hematology',
                    'Immunochemistry',
                    'Serology',
                    'Immunohematology',
                    'Microbiology',
                    'Molecular diagnostics',
                    'Toxicology',
                    'Mycology',
                    'Therapeutic drug monitoring',
                    'Parasitology',
                    'Virology'
                ),
                'Pathology Informatics' => array(
                    'Scanning Service',
                ),
                'Pathology Fellowship Programs'
            ),
            'Pediatrics' => array(
                'Cardiology',
                'Child Development',
                'Child Neurology'                                                           //continue
            ),
            'Pharmacology' => null,
            'Physiology and Biophysics' => null,
            'Primary Care' => null,
            'Psychiatry' => array(
                'Sackler Institute for Developmental Psychobiology'
            ),
            'Radiation Oncology' => null,
            'Radiology' => null,
            'Rehabilitation Medicine' => null,
            'Reproductive Medicine' => array(
                'Center for Reproductive Medicine and Infertility (CRMI)',
                'Center for Male Reproductive Medicine and Microsurgery'
            ),
            'Surgery' => array(
                'Breast Surgery',
                'Burn, Critical Care and Trauma',
                'Colon & Rectal Surgery',                                                   //continue
            ),
            'Urology' => array(
                'Brady Urologic Health Center'
            ),
            'Weill Department of Medicine' => array(
                'Cardiology',
                'Clinical Epidemiology and Evaluative Sciences Research',
                'Clinical Pharmacology'                                                     //continue  dep
            ),
            'Other Centers' => array(
                'Ansary Stem Cell Institute',
                'Center for Complementary and Integrative Medicine',
                'Center for Healthcare Informatics and Policy'                              //continue
            )

        );
        $wcmc = array(
            'abbreviation'=>'WCMC',
            'departments'=>$wcmcDep
        );

        //http://nyp.org/services/index.html
        $nyhDep = array(
            'Allergy, Immunology and Pulmonology' => null,
            'Anesthesiology' => null,
            'Cancer (Oncology)' => null,
            'Cancer Screening and Awareness' => null,
            'Cardiology' => null,
			'Complementary, Alternative, and Integrative Medicine' => null,
            'Dermatology' => null,
            'Diabetes and Endocrinology' => null,
            'Digestive Diseases' => null,
            'Ear, Nose, and Throat (Otorhinolaryngology)' => null,
            'Geriatrics' => null,
            'Hematology (Blood Disorders)' => null,
            'Infectious Diseases/International Medicine' => null,
            'Internal Medicine' => null,
            'Nephrology (Kidney Disease)' => null,
            'Neurology and Neuroscience' => null,
            'Obstetrics and Gynecology' => null,
            'Ophthalmology' => null,
            'Pain Medicine' => null,
            'Pathology and Laboratory Medicine' => null,
            'Pediatrics' => null,
            'Preventive Medicine and Nutrition' => null,
            'Psychiatry and Mental Health' => null,
            'Radiation Oncology' => null,
            'Radiology' => null,
            'Rehabilitation Medicine' => null,
            'Rheumatology' => null,
            "Women's Health" => null
        );

        $nyh = array(
            'abbreviation'=>'NYP',
            'departments'=>$nyhDep
        );


        $wcmcq = array(
            'abbreviation'=>'WCMCQ',
            'departments'
        );

        $mskDep = array(
            'Anesthesiology and Critical Care Medicine' => null,
            'Laboratory Medicine' => null,
            'Medicine' => null
            //continue
        );
        $msk = array(
            'abbreviation'=>'MSK',
            'departments'=>$mskDep
        );

        $hssDep = array(
            'Orthopedic Surgery' => null,
            'Anesthesiology' => null,
            'Medicine' => null
            //continue
        );
        $hss = array(
            'abbreviation'=>'HSS',
            'departments'=>$hssDep
        );

        $institutions = array(
            "Weill Cornell Medical College"=>$wcmc,
            "New York-Presbyterian Hospital"=>$nyh,
            "Weill Cornell Medical College Qatar"=>$wcmcq,
            "Memorial Sloan Kettering Cancer Center"=>$msk,
            "Hospital for Special Surgery"=>$hss
        );


        $medicalType = $em->getRepository('OlegUserdirectoryBundle:InstitutionType')->findOneByName('Medical');

        $levelInstitution = $em->getRepository('OlegUserdirectoryBundle:OrganizationalGroupType')->findOneByName('Institution');
        $levelDepartment = $em->getRepository('OlegUserdirectoryBundle:OrganizationalGroupType')->findOneByName('Department');
        $levelDivision = $em->getRepository('OlegUserdirectoryBundle:OrganizationalGroupType')->findOneByName('Division');
        $levelService = $em->getRepository('OlegUserdirectoryBundle:OrganizationalGroupType')->findOneByName('Service');

        $treeCount = 10;

        foreach( $institutions as $institutionname=>$infos ) {
            $institution = new Institution();
            $this->setDefaultList($institution,$treeCount,$username,$institutionname);
            $treeCount = $treeCount + 10;
            $institution->setAbbreviation( trim($infos['abbreviation']) );

            $institution->addType($medicalType);
            $institution->setOrganizationalGroupType($levelInstitution);

            if( array_key_exists('departments', $infos) && $infos['departments'] && is_array($infos['departments'])  ) {

                foreach( $infos['departments'] as $departmentname=>$divisions ) {

                    $department = new Institution();

                    if( is_numeric($departmentname) ){
                        $departmentname = $infos['departments'][$departmentname];
                    }
                    //echo "departmentname=".$departmentname."<br>";
                    $this->setDefaultList($department,$treeCount,$username,$departmentname);
                    $treeCount = $treeCount + 10;
                    $department->setOrganizationalGroupType($levelDepartment);

                    if( $divisions && is_array($divisions) ) {

                        foreach( $divisions as $divisionname=>$services ) {

                            //shortname
                            if( $divisionname === 'shortname' && $services ) {
                                //echo "<br> services=".$services."<br>";
                                $department->setShortname($services);
                                continue;
                            }

                            $division = new Institution();
                            if( is_numeric($divisionname) ){
                                $divisionname = $divisions[$divisionname];
                            }
                            $this->setDefaultList($division,$treeCount,$username,$divisionname);
                            $treeCount = $treeCount + 10;
                            $division->setOrganizationalGroupType($levelDivision);

                            if( $services && is_array($services) ) {

                                foreach( $services as $servicename ) {
                                    $service = new Institution();
                                    if( is_numeric($servicename) ){
                                        $servicename = $services[$servicename];
                                    }
                                    $this->setDefaultList($service,$treeCount,$username,$servicename);
                                    $treeCount = $treeCount + 10;
                                    $service->setOrganizationalGroupType($levelService);

                                    $division->addChild($service);
                                }
                            }//services


                            $department->addChild($division);
                        }
                    }//divisions

                    $institution->addChild($department);
                }
            }//departmets

            $em->persist($institution);
            $em->flush();
        } //foreach

        return round($treeCount/10);
    }

    public function generateAuxiliaryInstitutions() {

        $em = $this->getDoctrine()->getManager();
        $username = $this->get('security.context')->getToken()->getUser();
        $count = 0;

        //echo 'generate Auxiliary Institutions <br>';

        //All Institutions
        //echo 'All Institutions <br>';
        $allInst = $em->getRepository('OlegUserdirectoryBundle:Institution')->findOneByAbbreviation("All Institutions");
        if( !$allInst ) {
            $allInst = new Institution();
            $this->setDefaultList($allInst,1,$username,"All Institutions");
            $allInst->setAbbreviation("All Institutions");
            $medicalType = $em->getRepository('OlegUserdirectoryBundle:InstitutionType')->findOneByName('Medical');
            $allInst->addType($medicalType);
            //$allInst->setOrganizationalGroupType($levelInstitution);

            $em->persist($allInst);
            $em->flush($allInst);
            $count++;
        }

        //All Collaborations
        $collaborationType = $em->getRepository('OlegUserdirectoryBundle:InstitutionType')->findOneByName('Collaboration');
        //echo 'All Collaborations <br>';
        $allCollaborationInst = $em->getRepository('OlegUserdirectoryBundle:Institution')->findOneByAbbreviation("All Collaborations");
        if( !$allCollaborationInst ) {
            $allCollaborationInst = new Institution();
            $this->setDefaultList($allCollaborationInst,2,$username,"All Collaborations");
            $allCollaborationInst->setAbbreviation("All Collaborations");
            $allCollaborationInst->addType($collaborationType);
            //$allCollaborationInst->setOrganizationalGroupType($levelInstitution);
            $em->persist($allCollaborationInst);
            $em->flush($allCollaborationInst);
            $count++;
        }

        //add 'WCMC-NYP Collaboration'
        $wcmcnypCollaborationInst = $em->getRepository('OlegUserdirectoryBundle:Institution')->findOneByName('WCMC-NYP Collaboration');
        if( !$wcmcnypCollaborationInst ) {
            $wcmcnypCollaborationInst = new Institution();
            $this->setDefaultList($wcmcnypCollaborationInst,3,$username,"WCMC-NYP Collaboration");
            $wcmcnypCollaborationInst->setAbbreviation("WCMC-NYP Collaboration");

            $wcmcnypCollaborationInst->addType($collaborationType);

            $wcmc = $em->getRepository('OlegUserdirectoryBundle:Institution')->findOneByAbbreviation("WCMC");
            $wcmcnypCollaborationInst->addCollaborationInstitution($wcmc);

            $nyp = $em->getRepository('OlegUserdirectoryBundle:Institution')->findOneByAbbreviation("NYP");
            $wcmcnypCollaborationInst->addCollaborationInstitution($nyp);

            $unionCollaborationType = $em->getRepository('OlegUserdirectoryBundle:CollaborationTypeList')->findOneByName("Union");
            $wcmcnypCollaborationInst->setCollaborationType($unionCollaborationType);

            $allCollaborationInst->addChild($wcmcnypCollaborationInst);

            $em->persist($allCollaborationInst);
            $em->persist($wcmcnypCollaborationInst);
            $em->flush();
            $count++;
        }

//            //add WCMC-NYP collaboration object to this "WCMC-NYP" institution above
//            $wcmcnypCollaboration = $em->getRepository('OlegUserdirectoryBundle:Collaboration')->findOneByName("WCMC-NYP");
//            if( !$wcmcnypCollaboration ) {
//                $wcmcnypCollaboration = new Collaboration();
//                $this->setDefaultList($wcmcnypCollaboration,10,$username,"WCMC-NYP");
//
//                //add institutions
//                //WCMC
//                $wcmc = $em->getRepository('OlegUserdirectoryBundle:Institution')->findOneByName("WCMC");
//                if( !$wcmc ) {
//                    exit('No Institution: "WCMC"');
//                }
//                $wcmcnypCollaboration->addInstitution($wcmc);
//                //NYP
//                $nyp = $em->getRepository('OlegUserdirectoryBundle:Institution')->findOneByName("NYP");
//                if( !$nyp ) {
//                    exit('No Institution: "NYP"');
//                }
//                $wcmcnypCollaboration->addInstitution($nyp);
//
//                //set type
//                $collaborationType = $em->getRepository('OlegUserdirectoryBundle:CollaborationTypeList')->findOneByName("Union");
//                if( !$collaborationType ) {
//                    exit('No CollaborationTypeList: "Union"');
//                }
//                $wcmcnypCollaboration->setCollaborationType($collaborationType);
//            }
//            $wcmcnypCollaborationInst->addCollaboration($wcmcnypCollaboration);
//
//            $em->persist($allCollaborationInst);
//            $em->flush();
//            $count++;


        return $count;
    }

    public function generateStates() {

        $em = $this->getDoctrine()->getManager();
        $entities = $em->getRepository('OlegUserdirectoryBundle:States')->findAll();

        if( $entities ) {
            return -1;
        }

        $states = array(
            'AL'=>"Alabama",
            'AK'=>"Alaska",
            'AZ'=>"Arizona",
            'AR'=>"Arkansas",
            'CA'=>"California",
            'CO'=>"Colorado",
            'CT'=>"Connecticut",
            'DE'=>"Delaware",
            'DC'=>"District Of Columbia",
            'FL'=>"Florida",
            'GA'=>"Georgia",
            'HI'=>"Hawaii",
            'ID'=>"Idaho",
            'IL'=>"Illinois",
            'IN'=>"Indiana",
            'IA'=>"Iowa",
            'KS'=>"Kansas",
            'KY'=>"Kentucky",
            'LA'=>"Louisiana",
            'ME'=>"Maine",
            'MD'=>"Maryland",
            'MA'=>"Massachusetts",
            'MI'=>"Michigan",
            'MN'=>"Minnesota",
            'MS'=>"Mississippi",
            'MO'=>"Missouri",
            'MT'=>"Montana",
            'NE'=>"Nebraska",
            'NV'=>"Nevada",
            'NH'=>"New Hampshire",
            'NJ'=>"New Jersey",
            'NM'=>"New Mexico",
            'NY'=>"New York",
            'NC'=>"North Carolina",
            'ND'=>"North Dakota",
            'OH'=>"Ohio",
            'OK'=>"Oklahoma",
            'OR'=>"Oregon",
            'PA'=>"Pennsylvania",
            'RI'=>"Rhode Island",
            'SC'=>"South Carolina",
            'SD'=>"South Dakota",
            'TN'=>"Tennessee",
            'TX'=>"Texas",
            'UT'=>"Utah",
            'VT'=>"Vermont",
            'VA'=>"Virginia",
            'WA'=>"Washington",
            'WV'=>"West Virginia",
            'WI'=>"Wisconsin",
            'WY'=>"Wyoming"
        );

        $username = $this->get('security.context')->getToken()->getUser();

        $count = 10;
        foreach( $states as $key => $value ) {

            $entity = new States();
            $this->setDefaultList($entity,$count,$username,null);
            $entity->setName( trim($value) );
            $entity->setAbbreviation( trim($key) );

            $em->persist($entity);
            $em->flush();

            $count = $count + 10;

        } //foreach

        return round($count/10);
    }


    public function generateCountryList_Old() {

        $em = $this->getDoctrine()->getManager();

//        $entities = $em->getRepository('OlegUserdirectoryBundle:Countries')->findAll();
//        if( $entities ) {
//            //return -1;
//        }

//        $elements = Intl::getRegionBundle()->getCountryNames();
//        print_r($elements);
//        exit();

        $elements = array(
            "Afghanistan", "Albania", "Algeria", "American Samoa", "Andorra", "Angola", "Anguilla", "Antarctica", "Antigua and Barbuda",
            "Argentina", "Armenia", "Aruba", "Australia", "Austria", "Azerbaijan", "Bahamas", "Bahrain", "Bangladesh", "Barbados", "Belarus",
            "Belgium", "Belize", "Benin", "Bermuda", "Bhutan", "Bolivia", "Bosnia and Herzegowina", "Botswana", "Bouvet Island", "Brazil",
            "British Indian Ocean Territory", "Brunei Darussalam", "Bulgaria", "Burkina Faso", "Burundi", "Cambodia", "Cameroon", "Canada",
            "Cape Verde", "Cayman Islands", "Central African Republic", "Chad", "Chile", "China", "Christmas Island", "Cocos (Keeling) Islands",
            "Colombia", "Comoros", "Congo", "Congo, the Democratic Republic of the", "Cook Islands", "Costa Rica", "Cote d'Ivoire",
            "Croatia (Hrvatska)", "Cuba", "Cyprus", "Czech Republic", "Denmark", "Djibouti", "Dominica", "Dominican Republic",
            "East Timor", "Ecuador", "Egypt", "El Salvador", "Equatorial Guinea", "Eritrea", "Estonia", "Ethiopia", "Falkland Islands (Malvinas)",
            "Faroe Islands", "Fiji", "Finland", "France", "France Metropolitan", "French Guiana", "French Polynesia", "French Southern Territories",
            "Gabon", "Gambia", "Georgia", "Germany", "Ghana", "Gibraltar", "Greece", "Greenland", "Grenada", "Guadeloupe", "Guam", "Guatemala",
            "Guinea", "Guinea-Bissau", "Guyana", "Haiti", "Heard and Mc Donald Islands", "Holy See (Vatican City State)", "Honduras", "Hong Kong",
            "Hungary", "Iceland", "India", "Indonesia", "Iran (Islamic Republic of)", "Iraq", "Ireland", "Israel", "Italy", "Jamaica", "Japan",
            "Jordan", "Kazakhstan", "Kenya", "Kiribati", "Korea, Democratic People's Republic of", "Korea, Republic of", "Kuwait", "Kyrgyzstan",
            "Lao, People's Democratic Republic", "Latvia", "Lebanon", "Lesotho", "Liberia", "Libyan Arab Jamahiriya", "Liechtenstein", "Lithuania",
            "Luxembourg", "Macau", "Macedonia, The Former Yugoslav Republic of", "Madagascar", "Malawi", "Malaysia", "Maldives", "Mali", "Malta",
            "Marshall Islands", "Martinique", "Mauritania", "Mauritius", "Mayotte", "Mexico", "Micronesia, Federated States of", "Moldova, Republic of",
            "Monaco", "Mongolia", "Montserrat", "Morocco", "Mozambique", "Myanmar", "Namibia", "Nauru", "Nepal", "Netherlands", "Netherlands Antilles",
            "New Caledonia", "New Zealand", "Nicaragua", "Niger", "Nigeria", "Niue", "Norfolk Island", "Northern Mariana Islands", "Norway", "Oman",
            "Pakistan", "Palau", "Panama", "Papua New Guinea", "Paraguay", "Peru", "Philippines", "Pitcairn", "Poland", "Portugal", "Puerto Rico",
            "Qatar", "Reunion", "Romania", "Russian Federation", "Rwanda", "Saint Kitts and Nevis", "Saint Lucia", "Saint Vincent and the Grenadines",
            "Samoa", "San Marino", "Sao Tome and Principe", "Saudi Arabia", "Senegal", "Seychelles", "Sierra Leone", "Singapore", "Slovakia (Slovak Republic)",
            "Slovenia", "Solomon Islands", "Somalia", "South Africa", "South Georgia and the South Sandwich Islands", "Spain", "Sri Lanka", "St. Helena",
            "St. Pierre and Miquelon", "Sudan", "Suriname", "Svalbard and Jan Mayen Islands", "Swaziland", "Sweden", "Switzerland", "Syrian Arab Republic",
            "Taiwan, Province of China", "Tajikistan", "Tanzania, United Republic of", "Thailand", "Togo", "Tokelau", "Tonga", "Trinidad and Tobago",
            "Tunisia", "Turkey", "Turkmenistan", "Turks and Caicos Islands", "Tuvalu", "Uganda", "Ukraine", "United Arab Emirates", "United Kingdom",
            "United States", "United States Minor Outlying Islands", "Uruguay", "Uzbekistan", "Vanuatu", "Venezuela", "Vietnam", "Virgin Islands (British)",
            "Virgin Islands (U.S.)", "Wallis and Futuna Islands", "Western Sahara", "Yemen", "Yugoslavia", "Zambia", "Zimbabwe"
        );



        $username = $this->get('security.context')->getToken()->getUser();

        $count = 10;
        foreach( $elements as $value ) {

            $entity = new Countries();
            $this->setDefaultList($entity,$count,$username,null);
            $entity->setName( trim($value) );

            $em->persist($entity);
            $em->flush();

            $count = $count + 10;

        } //foreach

        return round($count/10);

    }


    public function generateCountryList() {

        $em = $this->getDoctrine()->getManager();

        $user = $this->get('security.context')->getToken()->getUser();

        $inputFileName = __DIR__ . '/../Util/Cities.xlsx';

        try {
            $inputFileType = \PHPExcel_IOFactory::identify($inputFileName);
            $objReader = \PHPExcel_IOFactory::createReader($inputFileType);
            $objPHPExcel = $objReader->load($inputFileName);
        } catch(Exception $e) {
            die('Error loading file "'.pathinfo($inputFileName,PATHINFO_BASENAME).'": '.$e->getMessage());
        }

        $sheet = $objPHPExcel->getSheet(0);
        $highestRow = $sheet->getHighestRow();
        $highestColumn = $sheet->getHighestColumn();

        $countryCount = 1;
        $cityCount = 1;

        $batchSize = 20;

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

            //$countryPersisted = false;
            //$cityPersisted = false;

            $country = trim($rowData[0][0]);
            $city = trim($rowData[0][1]);

            //country
            //echo "country=".$country."<br>";
            $countryDb = $em->getRepository('OlegUserdirectoryBundle:Countries')->findOneByName($country);

            if( !$countryDb ) {
                //echo "add country=".$country."<br>";

                $newCountry = new Countries();
                $this->setDefaultList($newCountry,$countryCount,$user,$country);


                $em->persist($newCountry);
                $em->flush();
                //$countryPersisted = true;

                $countryCount = $countryCount + 10;
            }

            //city
            //echo "city=".$city."<br>";
            $cityDb = $em->getRepository('OlegUserdirectoryBundle:CityList')->findOneByName($city);

            if( !$cityDb ) {
                //echo "add city=".$city."<br>";

                $newCity = new CityList();
                $this->setDefaultList($newCity,$cityCount,$user,$city);

                $em->persist($newCity);
                //$cityPersisted = true;

                $cityCount = $cityCount + 10;
            }

            //if( $countryPersisted || $cityPersisted ) {
                if( ($row % $batchSize) === 0 ) {
                    $em->flush();
                    //$em->clear(); // Detaches all objects from Doctrine!
                }
            //}

        } //for loop

        $em->flush(); //Persist objects that did not make up an entire batch
        $em->clear();

        $countArr = array();
        $countArr['country'] = round($countryCount/10);
        $countArr['city'] = round($cityCount/10);

        return $countArr;
    }


    public function generateLanguages() {

        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('OlegUserdirectoryBundle:LanguageList')->findAll();
        if( $entities ) {
            return -1;
        }

        //\Locale::setDefault('ru');
        $elements = Intl::getLanguageBundle()->getLanguageNames();
        //print_r($elements);
        //exit();

        $username = $this->get('security.context')->getToken()->getUser();

        $count = 10;
        foreach( $elements as $abbreviation=>$name ) {

            //$entity = $em->getRepository('OlegUserdirectoryBundle:LanguageList')->findOneByAbbreviation($abbreviation);

            //testing
//            if( $entity ) {
//                $em->remove($entity);
//                $em->flush();
//                echo "remove entity with ".$abbreviation."<br>";
//            }

            $entity = null;

            if( !$entity ) {
                $entity = new LanguageList();
                $this->setDefaultList($entity,$count,$username,null);
                $entity->setName( trim($name) );
                $entity->setAbbreviation( trim($abbreviation) );
            }

            \Locale::setDefault($abbreviation);
            $languageNativeName = Intl::getLanguageBundle()->getLanguageName($abbreviation);

            //uppercase the first letter
            $languageNativeName = mb_convert_case(mb_strtolower($languageNativeName), MB_CASE_TITLE, "UTF-8");

//            if( $abbreviation == 'ru' ) {
//                echo $abbreviation."=(".$languageNativeName.")<br>";
//                exit();
//            }

            $entity->setNativeName($languageNativeName);

            $em->persist($entity);
            $em->flush();

            $count = $count + 10;

        } //foreach
        //exit('1');

        \Locale::setDefault('en');

        return round($count/10);
    }


    public function generateLocales() {

        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('OlegUserdirectoryBundle:LocaleList')->findAll();
        if( $entities ) {
            return -1;
        }

        $elements = Intl::getLocaleBundle()->getLocaleNames();
        //print_r($elements);
        //exit();

        $username = $this->get('security.context')->getToken()->getUser();

        $count = 10;
        foreach( $elements as $locale=>$description ) {

//            $entities = $em->getRepository('OlegUserdirectoryBundle:LocaleList')->findByName($locale);
//            foreach( $entities as $entity ) {
//                $em->remove($entity);
//                $em->flush();
//                //echo "remove entity with ".$locale."<br>";
//            }

            $entity = null;
            if( !$entity ) {
                $entity = new LocaleList();
                $this->setDefaultList($entity,$count,$username,null);
            }

            $entity->setName( trim($locale) );
            $entity->setDescription( trim($description) );

            $em->persist($entity);
            $em->flush();

            $count = $count + 10;

        } //foreach
        //exit('1');

        return round($count/10);
    }


    public function generateBoardSpecialties() {

        $em = $this->getDoctrine()->getManager();
        $entities = $em->getRepository('OlegUserdirectoryBundle:BoardCertifiedSpecialties')->findAll();

        if( $entities ) {
            return -1;
        }

        $elements = array(
            'Anatomic Pathology',
            'Clinical Pathology',
            'Hematopathology',
            'Cytopathology',
            'Molecular Genetic Pathology',
            'Immunopathology',
            'Pediatric Pathology',
            'Neuropathology',
            'Dermatopathology',
            'Medical Microbiology',
            'Blood Banking/Transfusion Medicine',
            'Forensic Pathology',
            'Chemical Pathology'
        );


        $username = $this->get('security.context')->getToken()->getUser();

        $count = 10;
        foreach( $elements as $value ) {

            $entity = new BoardCertifiedSpecialties();
            $this->setDefaultList($entity,$count,$username,null);
            $entity->setName( trim($value) );

            $em->persist($entity);
            $em->flush();

            $count = $count + 10;

        } //foreach

        return round($count/10);

    }


    public function generateSourceSystems() {

        $em = $this->getDoctrine()->getManager();

        //$entities = $em->getRepository('OlegUserdirectoryBundle:SourceSystemList')->findAll();
        //if( $entities ) {
        //    return -1;
        //}

        $elements = array(
            'ORDER Scan Order', //used as default in getDefaultSourceSystem //'Scan Order',
            'WCMC Epic Practice Management',
            'WCMC Epic Ambulatory EMR',
            'NYH Paper Requisition',
            'Written or oral referral',
            'Aperio eSlide Manager on C.MED.CORNELL.EDU',
            'Indica HALO',
            'ORDER Deidentifier', //'Deidentifier',
            'ORDER Call Log Book'
        );


        $username = $this->get('security.context')->getToken()->getUser();

        $count = 10;
        foreach( $elements as $value ) {

            if( $em->getRepository('OlegUserdirectoryBundle:SourceSystemList')->findOneByName($value) ) {
                continue;
            }

            $entity = new SourceSystemList();
            $this->setDefaultList($entity,$count,$username,$value);

            $em->persist($entity);
            $em->flush();

            $count = $count + 10;

        } //foreach

        return round($count/10);

    }


    public function generateDocumentTypes() {

        $em = $this->getDoctrine()->getManager();

//        $entities = $em->getRepository('OlegUserdirectoryBundle:DocumentTypeList')->findAll();
//        if( $entities ) {
//            return -1;
//        }

        $elements = array(

            //'Generic Document',
            'Avatar Image',
            'Comment Document',
            'Autopsy Image',
            'Gross Image',
            'Part Document',
            'Block Image',
            'Microscopic Image',
            'Whole Slide Image',
            'Requisition Form Image',
            'Outside Report Reference Representation',
            'Grant Document',
            'Medical License Document',
            'Certificate of Qualification Document',

            'Fellowship Application Spreadsheet',
            'Fellowship Application Document',
            'Complete Fellowship Application PDF',
            'Old Complete Fellowship Application PDF',

            'Fellowship Photo',
            'Fellowship CV',
            'Fellowship Cover Letter',
            'Fellowship USMLE Scores',
            'Fellowship Interview Itinerary',
            'Fellowship Recommendation',
            'Fellowship Reprimand',
            'Fellowship Legal Suit',

        );


        $username = $this->get('security.context')->getToken()->getUser();

        $count = 10;
        foreach( $elements as $value ) {

            if( $em->getRepository('OlegUserdirectoryBundle:DocumentTypeList')->findOneByName($value) ) {
                continue;
            }

            $entity = new DocumentTypeList();
            $this->setDefaultList($entity,$count,$username,$value);

            $em->persist($entity);
            $em->flush();

            $count = $count + 10;

        } //foreach

        return round($count/10);

    }


    public function generateLinkTypes() {

        $em = $this->getDoctrine()->getManager();
        $entities = $em->getRepository('OlegUserdirectoryBundle:LinkTypeList')->findAll();

        if( $entities ) {
            return -1;
        }

        $elements = array(
            'Thumbnail',
            'Label',
            'Via WebScope',
            'Via ImageScope',
            'Download'
        );

        $username = $this->get('security.context')->getToken()->getUser();

        $count = 10;
        foreach( $elements as $value ) {

            $entity = new LinkTypeList();
            $this->setDefaultList($entity,$count,$username,$value);

            $em->persist($entity);
            $em->flush();

            $count = $count + 10;

        } //foreach

        return round($count/10);

    }

    public function generateEmploymentTypes() {

        $em = $this->getDoctrine()->getManager();
        $entities = $em->getRepository('OlegUserdirectoryBundle:EmploymentType')->findAll();

        if( $entities ) {
            return -1;
        }

        $elements = array(
            'Full Time',
            'Part Time',
            'Pathology Fellowship Applicant'
        );

        $username = $this->get('security.context')->getToken()->getUser();

        $count = 10;
        foreach( $elements as $value ) {

            $entity = new EmploymentType();
            $this->setDefaultList($entity,$count,$username,$value);

            $em->persist($entity);
            $em->flush();

            $count = $count + 10;

        } //foreach

        return round($count/10);

    }


    public function generateTerminationTypes() {

        $em = $this->getDoctrine()->getManager();
        $entities = $em->getRepository('OlegUserdirectoryBundle:EmploymentTerminationType')->findAll();

        if( $entities ) {
            return -1;
        }

        $elements = array(
            'Graduated',
            'Quit',
            'Retired',
            'Fired'
        );

        $username = $this->get('security.context')->getToken()->getUser();

        $count = 10;
        foreach( $elements as $value ) {

            $entity = new EmploymentTerminationType();
            $this->setDefaultList($entity,$count,$username,null);
            $entity->setName( trim($value) );

            $em->persist($entity);
            $em->flush();

            $count = $count + 10;

        } //foreach

        return round($count/10);

    }

    public function generateEventTypeList() {
        $em = $this->getDoctrine()->getManager();

        $elements = array(
            'Login Page Visit',
            'Successful Login',
            'Bad Credentials',
            'Unsuccessful Login Attempt',
            'Unapproved User Login Attempt',
            'Banned User Login Attempt',
            'New user record added',
            'User record updated',

            'Import of Fellowship Applications Spreadsheet',
            'Import of Fellowship Application data to DB',
            'Fellowship Application Created',
            'Fellowship Application Creation Failed',
            'Fellowship Application Updated',
            'Fellowship Application Rating Invitation Emails Resent',
            'Fellowship Application Page Viewed',

            'Complete Fellowship Application PDF Downloaded',
            'Fellowship Interview Itinerary Downloaded',
            'Fellowship CV Downloaded',
            'Fellowship Cover Letter Downloaded',
            'Fellowship USMLE Scores Downloaded',
            'Fellowship Recommendation Downloaded',

            'Complete Fellowship Application PDF Uploaded',
            'Fellowship Interview Itinerary Uploaded',
            'Fellowship CV Uploaded',
            'Fellowship Cover Letter Uploaded',
            'Fellowship USMLE Scores Uploaded',
            'Fellowship Recommendation Uploaded',

            'Fellowship Application Status changed to Active',
            'Fellowship Application Status changed to Archived',
            'Fellowship Application Status changed to Hidden',
            'Fellowship Application Status changed to Complete',
            'Fellowship Application Status changed to Interviewee',
            'Fellowship Application Status changed to Rejected',
            'Fellowship Application Status changed to On Hold',
            'Fellowship Interview Evaluation Updated',
            "Deleted Fellowship Application From Google Drive",
            "Failed Deleted Fellowship Application From Google Drive",

            'Role Permission Updated',

            'Generate Accession Deidentifier ID',
            'Search by Deidentifier ID conducted',

            'Warning',
            'Error'
        );

        $username = $this->get('security.context')->getToken()->getUser();

        $count = 10;
        foreach( $elements as $value ) {
            if( $em->getRepository('OlegUserdirectoryBundle:EventTypeList')->findOneByName($value) ) {
                continue;
            }
            //echo 'OlegUserdirectoryBundle:EventTypeList' . " name=" . $value . "<br>";
            $entity = new EventTypeList();
            $this->setDefaultList($entity,$count,$username,null);
            $entity->setName( trim($value) );

            $em->persist($entity);
            $em->flush();

            $count = $count + 10;
            //echo 'EOF OlegUserdirectoryBundle:EventTypeList' . " name=" . $value . "<br>";
        } //foreach

        return round($count/10);
    }


    public function generateIdentifierTypeList() {
        $em = $this->getDoctrine()->getManager();

//        $entities = $em->getRepository('OlegUserdirectoryBundle:IdentifierTypeList')->findAll();
//        if( $entities ) {
//            return -1;
//        }

        $elements = array(
            'WCMC Employee Identification Number (EIN)',
            'National Provider Identifier (NPI)',
            'MRN',
            'ORDER Local User',
            'NYP CWID',
            'WCMC CWID'
        );

        $username = $this->get('security.context')->getToken()->getUser();

        $count = 10;
        foreach( $elements as $value ) {

            if( $em->getRepository('OlegUserdirectoryBundle:IdentifierTypeList')->findOneByName($value) ) {
                continue;
            }

            $entity = new IdentifierTypeList();
            $this->setDefaultList($entity,$count,$username,null);
            $entity->setName( trim($value) );

            $em->persist($entity);
            $em->flush();

            $count = $count + 10;

        } //foreach

        return round($count/10);
    }


    public function generateFellowshipTypeList() {
        $em = $this->getDoctrine()->getManager();
        $entities = $em->getRepository('OlegUserdirectoryBundle:FellowshipTypeList')->findAll();

        if( $entities ) {
            return -1;
        }

        $elements = array(
            "Blood banking/Transfusion medicine",
            "Chemistry",
            "Dermatopathology",
            "Forensic pathology",
            "Genitourinary pathology",
            "Hematopathology",
            "Molecular genetic pathology",
            "Pathology informatics",
            "Pulmonary/Mediastinal pathology",
            "Soft tissue/Bone pathology",
            "Breast pathology",
            "Cytopathology",
            "Diagnostic immunology",
            "Gastrointestinal pathology",
            "Gynecologic pathology",
            "Medical microbiology",
            "Neuropathology",
            "Pediatric pathology",
            "Renal pathology",
            "Surgical/Oncologic pathology"
        );

        $username = $this->get('security.context')->getToken()->getUser();

        $count = 10;
        foreach( $elements as $value ) {

            $entity = new FellowshipTypeList();
            $this->setDefaultList($entity,$count,$username,null);
            $entity->setName( trim($value) );

            $em->persist($entity);
            $em->flush();

            $count = $count + 10;

        } //foreach

        return round($count/10);
    }

    public function generateResidencyTrackList() {
        $em = $this->getDoctrine()->getManager();
        $entities = $em->getRepository('OlegUserdirectoryBundle:ResidencyTrackList')->findAll();

        if( $entities ) {
            return -1;
        }

        $elements = array(
            'AP',
            'CP',
            'AP/CP'
        );

        $username = $this->get('security.context')->getToken()->getUser();

        $count = 10;
        foreach( $elements as $value ) {

            $entity = new ResidencyTrackList();
            $this->setDefaultList($entity,$count,$username,null);
            $entity->setName( trim($value) );

            $em->persist($entity);
            $em->flush();

            $count = $count + 10;

        } //foreach

        return round($count/10);
    }


    public function generateMedicalTitleList() {
        $em = $this->getDoctrine()->getManager();

        $elements = array(
            'Assistant Attending Pathologist',
            'Associate Attending Pathologist',
            'Attending Pathologist',
            'Resident',
            'Fellow'
        );

        $username = $this->get('security.context')->getToken()->getUser();

        $count = 10;
        foreach( $elements as $value ) {

            $value = trim($value);

            if( $em->getRepository('OlegUserdirectoryBundle:MedicalTitleList')->findOneByName($value) ) {
                continue;
            }

            $entity = new MedicalTitleList();
            $this->setDefaultList($entity,$count,$username,$value);

            $em->persist($entity);
            $em->flush();

            $count = $count + 10;

        } //foreach

        return round($count/10);
    }

    public function generateMedicalSpecialties() {
        $em = $this->getDoctrine()->getManager();

        $elements = array(
            'Autopsy Pathology',
            'Breast Pathology',
            'Cardiopulmonary Pathology',
            'Clinical Microbiology',
            'Cytogenetics',
            'Cytopathology',
            'Dermatopathology',
            'Gastrointestinal and Liver Pathology',
            'Genitourinary Pathology',
            'Gynecologic Pathology',
            'Head and Neck Pathology',
            'Hematopathology',
            'Immunopathology',
            'Molecular and Genomic Pathology',
            'Molecular Hematopathology',
            'Neuropathology',
            'Pathology Informatics',
            'Pediatric Pathology',
            'Perinatal and Obstetric Pathology',
            'Renal Pathology',
            'Surgical Pathology',
            'Transfusion Medicine'
        );

        $username = $this->get('security.context')->getToken()->getUser();

        $count = 10;
        foreach( $elements as $value ) {

            $value = trim($value);

            if( $em->getRepository('OlegUserdirectoryBundle:MedicalSpecialties')->findOneByName($value) ) {
                continue;
            }

            $entity = new MedicalSpecialties();
            $this->setDefaultList($entity,$count,$username,$value);

            $em->persist($entity);
            $em->flush();

            $count = $count + 10;

        } //foreach

        return round($count/10);
    }

    public function generateLocationTypeList() {
        $em = $this->getDoctrine()->getManager();
        $entities = $em->getRepository('OlegUserdirectoryBundle:LocationTypeList')->findAll();

        if( $entities ) {
            return -1;
        }

        $elements = array(
            'Employee Office',
            'Employee Desk',
            'Employee Cubicle',
            'Employee Suite',
            'Employee Mailbox',
            'Employee Home',
            'Conference Room',
            'Sign Out Room',
            'Clinical Laboratory',
            'Research Laboratory',
            'Medical Office',
            'Inpatient Room',
            "Patient's Primary Contact Information",
            "Patient's Contact Information",
            'Pick Up',
            'Accessioning',
            'Storage',
            'Filing Room',
            'Off Site Slide Storage',
            'Present Address',
            'Permanent Address',
            'Work Address',
            'Encounter Location'
        );

        $username = $this->get('security.context')->getToken()->getUser();

        $count = 10;
        foreach( $elements as $value ) {

            $entity = new LocationTypeList();
            $this->setDefaultList($entity,$count,$username,null);
            $entity->setName( trim($value) );

            $em->persist($entity);
            $em->flush();

            $count = $count + 10;

        } //foreach

        return round($count/10);
    }




    public function generateEquipmentType() {

        $username = $this->get('security.context')->getToken()->getUser();

        $em = $this->getDoctrine()->getManager();

//        $entities = $em->getRepository('OlegUserdirectoryBundle:EquipmentType')->findAll();
//
//        if( $entities ) {
//            return -1;
//        }

        $types = array(
            'Whole Slide Scanner',
            'Microtome',
            'Centrifuge',
            'Slide Stainer',
            'Microscope Camera',
            'Autopsy Camera',
            'Gross Image Camera',
            'Tissue Processor',
            'Xray Machine',
            'Block Imaging Camera',
            'Requisition Form Scanner'
        );

        $count = 10;
        foreach( $types as $type ) {

            if( $em->getRepository('OlegUserdirectoryBundle:EquipmentType')->findOneByName($type) ) {
                continue;
            }

            $listEntity = new EquipmentType();
            $this->setDefaultList($listEntity,$count,$username,$type);

            $em->persist($listEntity);
            $em->flush();

            $count = $count + 10;
        }

        return round($count/10);
    }

    public function generateEquipment() {

        $username = $this->get('security.context')->getToken()->getUser();

        $em = $this->getDoctrine()->getManager();

//        $entities = $em->getRepository('OlegUserdirectoryBundle:Equipment')->findAll();
//
//        if( $entities ) {
//            return -1;
//        }

        $types = array(
            'Aperio ScanScope AT' => 'Whole Slide Scanner',
            'Lumix LX5' => 'Autopsy Camera',
            'Canon 60D' => 'Autopsy Camera',
            'Milestone MacroPath D' => 'Gross Image Camera',
            'Block Processing Device' => 'Tissue Processor',
            'Faxitron' => 'Xray Machine',
            'Block Image Device' => 'Block Imaging Camera',
            'Microtome Device' => 'Microtome',
            'Microtome Device' => 'Centrifuge',
            'Slide Stainer Device' => 'Slide Stainer',
            'Olympus Camera' => 'Microscope Camera',
            'Generic Desktop Scanner' => 'Requisition Form Scanner'
        );

        $count = 10;
        foreach( $types as $device => $keytype ) {

            if( $em->getRepository('OlegUserdirectoryBundle:Equipment')->findOneByName($device) ) {
                continue;
            }

            $keytype = $em->getRepository('OlegUserdirectoryBundle:EquipmentType')->findOneByName($keytype);

            if( !$keytype ) {
                //continue;
                //exit('equipment keytype is null');
                throw new \Exception( 'Equipment keytype is null, name="' . $keytype .'"' );
            }

            $listEntity = new Equipment();
            $this->setDefaultList($listEntity,$count,$username,$device);

            $keytype->addEquipment($listEntity);

            $em->persist($listEntity);
            $em->flush();

            $count = $count + 10;
        }

        return round($count/10);
    }


    public function generateLocationPrivacy() {

        $username = $this->get('security.context')->getToken()->getUser();

        $em = $this->getDoctrine()->getManager();
        $entities = $em->getRepository('OlegUserdirectoryBundle:LocationPrivacyList')->findAll();

        if( $entities ) {
            return -1;
        }

        $types = array(
            "Administration; Those 'on call' can see these phone numbers & email",
            "Administration can see and edit this contact information",
            "Any approved user of Employee Directory can see these phone numbers and email",
            "Any approved user of Employee Directory can see this contact information if logged in",
            "Anyone can see this contact information"
        );

        $count = 10;
        foreach( $types as $type ) {

            $listEntity = new LocationPrivacyList();
            $this->setDefaultList($listEntity,$count,$username,$type);

            $em->persist($listEntity);
            $em->flush();

            $count = $count + 10;
        }

        return round($count/10);
    }


    public function generateResLabs_OLD() {

        $username = $this->get('security.context')->getToken()->getUser();

        $em = $this->getDoctrine()->getManager();
        $entities = $em->getRepository('OlegUserdirectoryBundle:ResearchLab')->findAll();

        if( $entities ) {
            return -1;
        }

        $types = array(
            "Laboratory of Prostate Cancer Research Group",
            "Proteolytic Oncogenesis",
            "Macrophages and Tissue Remodeling",
            "Antiphospholipid Syndrome",
            "Laboratory of Stem Cell Aging and Cancer",
            "Molecular Pathology",
            "Skeletal Biology",
            "Viral Oncogenesis",
            "Vascular Biology",
            "Cell Cycle",
            "Molecular Gynecologic Pathology",
            "Cancer Biology",
            "Cell Metabolism",
            "Oncogenic Transcription Factors in Prostate Cancer",
        );

        $count = 10;
        foreach( $types as $type ) {

            $listEntity = new ResearchLab();
            $this->setDefaultList($listEntity,$count,$username,$type);

            $em->persist($listEntity);
            $em->flush();

            $count = $count + 10;
        }

        return round($count/10);
    }
    //http://cornellpathology.com/research/laboratories
    //add new reseacrh lab institutions with "Research Lab" OrganizationalGroupType under "WCMC-Pathology"
    //add manually already existing lab's institutions:
    //"Skeletal Biology", "Dr. Inghirami's Lab", "Wayne Tam Lab"
    public function generateResLabs() {

        $username = $this->get('security.context')->getToken()->getUser();

        $em = $this->getDoctrine()->getManager();

        $researchLabOrgGroup = $em->getRepository('OlegUserdirectoryBundle:OrganizationalGroupType')->findOneByName("Research Lab");
        if( !$researchLabOrgGroup ) {
            exit('No OrganizationalGroupType: "Research Lab"');
        }
        //echo "researchLabOrgGroup=".$researchLabOrgGroup."<br>";

        $wcmc = $em->getRepository('OlegUserdirectoryBundle:Institution')->findOneByAbbreviation("WCMC");
        if( !$wcmc ) {
            exit('No Institution: "WCMC"');
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

        $medicalType = $em->getRepository('OlegUserdirectoryBundle:InstitutionType')->findOneByName('Medical');

        $labs = array(
            "Prostate Cancer Research Group" => "Laboratory of Prostate Cancer Research Group",
            "Viral Oncogenesis" => "Viral Oncogenesis",
            "Vascular Biology" => "Center for Vascular Biology",
            "Cell Cycle" => "Cell Cycle",
            "Epigenetics and Genomic Integrity" => "Laboratory of Epigenetics and Genomic Integrity",
            "Laboratory of Stem Cell Aging and Cancer" => "Laboratory of Stem Cell Aging and Cancer",
            "Molecular Pathology" => "Molecular Pathology",
            "Oncogenic Transcription Factors in Prostate Cancer" => "Oncogenic Transcription Factors in Prostate Cancer", //Oncogenic Transcription Factors in Prostate Cancer; Prostate Cancer Research Group

            "DNA Repair and Molecular Immunology" => "DNA Repair and Molecular Immunology Laboratory",
            "Proteolytic Oncogenesis" => "Proteolytic Oncogenesis",
            "Macrophages and Tissue Remodeling" => "Macrophages and Tissue Remodeling", //Macrophages and Tissue Remodeling; Vascular Biology
            "Antiphospholipid Syndrome" => "Antiphospholipid Syndrome (APS) Research Laboratory", //Dr. Rand's Lab
            "Molecular Gynecologic Pathology" => "Molecular Gynecologic Pathology",
            "Regulation of Bone Mass Laboratory" => "Regulation of Bone Mass Laboratory",
            "Cell Metabolism" => "Laboratory of Cell Metabolism",
            "Cancer Biology" => "Cancer Biology",

            "Skeletal Biology" => "Skeletal Biology",
            "Dr. Inghirami's Lab" => "Dr. Inghirami's Lab",
            "Wayne Tam Lab" => "Wayne Tam Lab"

//            "Viral Oncogenesis",
//            "Center for Vascular Biology",
//
//
//            "Proteolytic Oncogenesis",
//            "Macrophages and Tissue Remodeling",
//            "Antiphospholipid Syndrome",
//            "Laboratory of Stem Cell Aging and Cancer",
//            "Molecular Pathology",
//            "Skeletal Biology",
//            "Viral Oncogenesis",
//            "Vascular Biology",
//            "Cell Cycle",
//            "Molecular Gynecologic Pathology",
//            "Cancer Biology",
//            "Cell Metabolism",
//            "Oncogenic Transcription Factors in Prostate Cancer",
        );

        $count = 10;
        foreach( $labs as $labName => $pageName ) {

            //1) create a new Research Institution with "Research Lab" OrganizationalGroupType under "WCMC-Pathology"
            $researchInstitution = $em->getRepository('OlegUserdirectoryBundle:Institution')->findByChildnameAndParent(
                $labName,
                $pathology,
                $mapper
            );
            if( $researchInstitution ) {
                continue;
            }
            $researchInstitution = $em->getRepository('OlegUserdirectoryBundle:Institution')->findByChildnameAndParent(
                $pageName,
                $pathology,
                $mapper
            );
            if( $researchInstitution ) {
                continue;
            }

            $researchInstitution = new Institution();
            $this->setDefaultList($researchInstitution,1,$username,$labName);
            $researchInstitution->setOrganizationalGroupType($researchLabOrgGroup);
            $researchInstitution->addType($medicalType);
            $pathology->addChild($researchInstitution);
            //echo "researchInstitution=".$researchInstitution."(".$researchInstitution->getOrganizationalGroupType().")<br>";
            //exit('1');

            $em->persist($researchInstitution);
            $em->flush();
            //echo "added new Research Institution=".$labName."<br>";


            //2) create Research Lab object
            $researchLab = $em->getRepository('OlegUserdirectoryBundle:ResearchLab')->findOneByName($labName);
            if( $researchLab ) {
                if( !$researchLab->getInstitution() ) {
                    $researchLab->setInstitution($researchInstitution);
                    $em->persist($researchLab);
                    $em->flush();
                }
                continue;
            }
            $researchLab = $em->getRepository('OlegUserdirectoryBundle:ResearchLab')->findOneByName($pageName);
            if( $researchLab ) {
                if( !$researchLab->getInstitution() ) {
                    $researchLab->setInstitution($researchInstitution);
                    $em->persist($researchLab);
                    $em->flush();
                }
                continue;
            }

            $researchLab = new ResearchLab();
            $this->setDefaultList($researchLab, $count, $username, $labName);

            $researchLab->setInstitution($researchInstitution);

            $em->persist($researchLab);
            $em->flush();
            //echo "added new ResearchLab=".$labName."<br>";


            $count = $count + 10;

            //exit("finished adding ".$labName)."<br>";
        }

        return round($count/10);
    }


    public function generateBuildings() {

        $username = $this->get('security.context')->getToken()->getUser();

        $em = $this->getDoctrine()->getManager();
        $entities = $em->getRepository('OlegUserdirectoryBundle:BuildingList')->findAll();

        if( $entities ) {
            return -1;
        }

        $buildings = array(
            array('name'=>"Weill Cornell Medical College", 'street1'=>'1300 York Ave','abbr'=>'C','inst'=>'WCMC'),
            array('name'=>"Belfer Research Building", 'street1'=>'413 East 69th Street','abbr'=>null,'inst'=>'WCMC'),
            array('name'=>"Helmsley Medical Tower", 'street1'=>'1320 York Ave','abbr'=>null,'inst'=>'WCMC'),
            array('name'=>"Weill Greenberg Center",'street1'=>'1305 York Ave','abbr'=>null,'inst'=>'WCMC'),
            array('name'=>"Olin Hall",'street1'=>'445 East 69th Street','abbr'=>null,'inst'=>'WCMC'),
            array('name'=>"",'street1'=>'575 Lexington Ave','abbr'=>null,'inst'=>'WCMC'),                        //WCMC - 575 Lexington Ave
            array('name'=>"",'street1'=>'402 East 67th Street','abbr'=>null,'inst'=>'WCMC'),                     //WCMC - 402 East 67th Street
            array('name'=>"",'street1'=>'425 East 61st Street','abbr'=>null,'inst'=>'WCMC'),                     //WCMC - 425 East 61st Street
            array('name'=>"Starr Pavilion",'street1'=>'520 East 70th Street','abbr'=>'ST','inst'=>'NYP'),
            array('name'=>"J Corridor",'street1'=>'525 East 68th Street','abbr'=>'J','inst'=>'NYP'),
            array('name'=>"L Corridor",'street1'=>'525 East 68th Street','abbr'=>'L','inst'=>'NYP'),
            array('name'=>"K Wing",'street1'=>'525 East 68th Street','abbr'=>'K','inst'=>'NYP'),
            array('name'=>"F Wing, Floors 2-9",'street1'=>'525 East 68th Street','abbr'=>'F','inst'=>'NYP'),
            array('name'=>"Baker Pavilion - F Wing",'street1'=>'525 East 68th Street','abbr'=>'B','inst'=>'NYP'),
            array('name'=>"Payson Pavilion",'street1'=>'525 East 68th Street','abbr'=>'P','inst'=>'NYP'),
            array('name'=>"Whitney Pavilion",'street1'=>'525 East 68th Street','abbr'=>'W','inst'=>'NYP'),
            array('name'=>"M Wing",'street1'=>'530 East 70th Street','abbr'=>'M','inst'=>'NYP'),
            array('name'=>"N Wing",'street1'=>'530 East 70th Street','abbr'=>'N','inst'=>'NYP'),
            array('name'=>"Weill Cornell Medical Assoc. Eastside",'street1'=>'201 East 80th Street','abbr'=>null,'inst'=>'NYP'),
            array('name'=>"Weill Cornell Medical Assoc. Westside",'street1'=>'12 West 72nd Street','abbr'=>null,'inst'=>'NYP'),
            array('name'=>"Iris Cantor Women’s Health Center",'street1'=>'425 East 61st Street','abbr'=>null,'inst'=>'NYP'),
            array('name'=>"Weill Cornell Imaging at NewYork-Presbyterian",'street1'=>'416 East 55th Street','abbr'=>null,'inst'=>'NYP'),    //NYP - Weill Cornell Imaging at NewYork-Presbyterian / 416 East 55th Street
            array('name'=>"Weill Cornell Imaging at NewYork-Presbyterian, 9th Floor",'street1'=>'425 East 61st Street','abbr'=>null,'inst'=>'NYP'),    //NYP - Weill Cornell Imaging at NewYork-Presbyterian / 425 East 61st Street, 9th Floor
            array('name'=>"Weill Cornell Imaging at NewYork-Presbyterian, lobby level",'street1'=>'520 East 70th Street','abbr'=>null,'inst'=>'NYP'),    //NYP - Weill Cornell Imaging at NewYork-Presbyterian / 520 East 70th Street, lobby level
            array('name'=>"Weill Cornell Imaging at NewYork-Presbyterian, 3rd Floor",'street1'=>'1305 York Avenue','abbr'=>null,'inst'=>'NYP'),    //NYP - Weill Cornell Imaging at NewYork-Presbyterian / 1305 York Avenue, 3rd Floor
            array('name'=>"Oxford Medical Offices",'street1'=>'428 East 72nd Street','abbr'=>null,'inst'=>'NYP'),
            array('name'=>"Stich Building",'street1'=>'1315 York Ave','abbr'=>null,'inst'=>'NYP'),
            array('name'=>"Kips Bay Medical Offices",'street1'=>'411 East 69th Street','abbr'=>null,'inst'=>'NYP'),
            array('name'=>"Phipps House Medical Offices",'street1'=>'449 East 68th Street','abbr'=>null,'inst'=>'NYP'),
            array('name'=>"",'street1'=>'333 East 38th Street','abbr'=>null,'inst'=>'NYP'),  //NYP - 333 East 38th Street
            array('name'=>"Greenberg Pavilion",'street1'=>'525 East 68th Street','abbr'=>null,'inst'=>'NYP'),
        );

        $city = $em->getRepository('OlegUserdirectoryBundle:CityList')->findOneByName("New York");
        $state = $em->getRepository('OlegUserdirectoryBundle:States')->findOneByName("New York");
        $country = $em->getRepository('OlegUserdirectoryBundle:Countries')->findOneByName("United States");
        if( !$country ) {
            //exit('ERROR: country null');
            $errorMsg = 'Failed to create Building List. Country is not found by name=' . 'United States.'.
            'Please populate Country and City Lists first or create a country with name "United States"';
            //throw new \Exception( $errorMsg );
            return $errorMsg;
        }

        $count = 10;
        foreach( $buildings as $building ) {

            $name = $building['name'];

            if( !$name ) {
                continue;
            }

            if( $em->getRepository('OlegUserdirectoryBundle:BuildingList')->findOneByName($name) ) {
                continue;
            }

            $listEntity = new BuildingList();
            $this->setDefaultList($listEntity,$count,$username,$name);

            //add buildings attributes
            $street1 = $building['street1'];
            $buildingAbbr = $building['abbr'];

            $geo = new GeoLocation();
            $geo->setStreet1($street1);
            $geo->setCity($city);
            $geo->setState($state);
            $geo->setCountry($country);

            $listEntity->setGeoLocation($geo);
            $listEntity->setAbbreviation($buildingAbbr);

            $instAbbr = $building['inst'];
            $inst = $em->getRepository('OlegUserdirectoryBundle:Institution')->findOneByAbbreviation($instAbbr);
            if( $inst ) {
                $listEntity->addInstitution($inst);
            }

            //echo $count.": name=".$name.", street1=".$street1."<br>";

            $em->persist($listEntity);
            $em->flush();

            $count = $count + 10;
        }

        return round($count/10);
    }


    public function generateLocations() {

        $username = $this->get('security.context')->getToken()->getUser();

        $em = $this->getDoctrine()->getManager();

//        $entities = $em->getRepository('OlegUserdirectoryBundle:Location')->findAll();
//        if( $entities ) {
//            return -1;
//        }

        $locations = array(
            "Surgical Pathology Filing Room" => array('street1'=>'520 East 70th Street','phone'=>'222-0059','room'=>'ST-1012','inst'=>'NYP'),
        );

        $city = $em->getRepository('OlegUserdirectoryBundle:CityList')->findOneByName("New York");
        $state = $em->getRepository('OlegUserdirectoryBundle:States')->findOneByName("New York");
        $country = $em->getRepository('OlegUserdirectoryBundle:Countries')->findOneByName("United States");
        $locationType = $em->getRepository('OlegUserdirectoryBundle:LocationTypeList')->findOneByName("Filing Room");
        $locationPrivacy = $em->getRepository('OlegUserdirectoryBundle:LocationPrivacyList')->findOneByName("Anyone can see this contact information");
        $building = $em->getRepository('OlegUserdirectoryBundle:BuildingList')->findOneByName("Starr Pavilion");

        if( !$country ) {
            $errorMsg = 'Failed to create Building List. Country is not found by name=' . 'United States.'.
                'Please populate Country and City Lists first or create a country with name "United States"';
            //throw new \Exception( $errorMsg );
            return $errorMsg;
        }

        $count = 10;
        foreach( $locations as $location => $attr ) {

            if( $em->getRepository('OlegUserdirectoryBundle:Location')->findOneByName($location) ) {
                continue;
            }

            $listEntity = new Location();
            $this->setDefaultList($listEntity,$count,$username,$location);

            //add buildings attributes
            $street1 = $attr['street1'];
            $phone = $attr['phone'];
            $room = $attr['room'];
            $instAbbr = $attr['inst'];

            $inst = $em->getRepository('OlegUserdirectoryBundle:Institution')->findOneByAbbreviation($instAbbr);
            if( $inst ) {
                $listEntity->setInstitution($inst);
            }

            $geo = new GeoLocation();
            $geo->setStreet1($street1);
            $geo->setCity($city);
            $geo->setState($state);
            $geo->setCountry($country);

            $listEntity->setGeoLocation($geo);
            $listEntity->setPhone($phone);
            $listEntity->setRoom($room);
            $listEntity->setStatus($listEntity::STATUS_VERIFIED);
            $listEntity->addLocationType($locationType);
            $listEntity->setPrivacy($locationPrivacy);
            $listEntity->setBuilding($building);

            //set room object
            $userUtil = new UserUtil();
            $roomObj = $userUtil->getObjectByNameTransformer($room,$username,'RoomList',$em);
            $listEntity->setRoom($roomObj);

            $em->persist($listEntity);
            $em->flush();

            $count = $count + 10;
        }

        $countNew = $this->generateLocationsFromExcel();
        $count = $count + $countNew;

        return round($count/10);
    }

    public function generateLocationsFromExcel( $count=null ) {
        $username = $this->get('security.context')->getToken()->getUser();
        $em = $this->getDoctrine()->getManager();

//        $entities = $em->getRepository('OlegUserdirectoryBundle:Location')->findAll();
//        if( count($entities) > 3 ) {
//            return -1;
//        }

        $userSecUtil = $this->container->get('user_security_utility');

        $locationPrivacy = $em->getRepository('OlegUserdirectoryBundle:LocationPrivacyList')->findOneByName("Anyone can see this contact information");
        if( !$locationPrivacy ) {
            exit("Location privacy is not found by name "."'Anyone can see this contact information'");
        }

        $inputFileName = __DIR__ . '/../Util/Encounter Locations (Import Columns A through O)-2 - Fixed-Ready For Import.xlsx';

        //TODO: check if file exists before opening (for all excel files)
        try {
            $inputFileType = \PHPExcel_IOFactory::identify($inputFileName);
            $objReader = \PHPExcel_IOFactory::createReader($inputFileType);
            $objPHPExcel = $objReader->load($inputFileName);
        } catch( Exception $e ) {
            die('Error loading file "'.pathinfo($inputFileName,PATHINFO_BASENAME).'": '.$e->getMessage());
        }

        $sheet = $objPHPExcel->getSheet(0);
        $highestRow = $sheet->getHighestRow();
        $highestColumn = $sheet->getHighestColumn();

        for( $row = 2; $row <= $highestRow; $row++ ) {

            //Read a row of data into an array
            $rowData = $sheet->rangeToArray('A' . $row . ':' . $highestColumn . $row,
                NULL,
                TRUE,
                FALSE);

            $name = trim($rowData[0][0]);
            $locationTypeName = trim($rowData[0][1]);
            $locationPhone = trim($rowData[0][2]);
            $locationRoom = trim($rowData[0][3]);
            $locationSuite = trim($rowData[0][4]);
            $locationFloor = trim($rowData[0][5]);
            $locationFloorSide = trim($rowData[0][6]);
            $locationBuildingName = trim($rowData[0][7]);

//            print "<pre>";
//            print_r($rowData);
//            print "</pre>";
//            print "</pre>";
            //echo "name=$name, locationTypeName=$locationTypeName, locationPhone=$locationPhone, locationRoom=$locationRoom, locationSuite=$locationSuite, locationFloor=$locationFloor, locationFloorSide=$locationFloorSide, locationBuildingName=$locationBuildingName <br>";
            //exit();

            if( !$name ) {
                exit("Location name is empty");
            }

            $listEntity = $em->getRepository('OlegUserdirectoryBundle:Location')->findOneByName($name);
            if( $listEntity ) {
                continue;
            }

            $listEntity = new Location();
            $this->setDefaultList($listEntity,null,$username,$name);

            $listEntity->setStatus($listEntity::STATUS_VERIFIED);
            $listEntity->setPrivacy($locationPrivacy);

            if( $locationTypeName ) {
                $locationType = $em->getRepository('OlegUserdirectoryBundle:LocationTypeList')->findOneByName($locationTypeName);
                if (!$locationType) {
                    exit("No location found by name " . $locationTypeName);
                }
                $listEntity->addLocationType($locationType);
            }

            if( $locationPhone ) {
                $listEntity->setPhone($locationPhone);
            }

            if( $locationRoom ) {
                $room = $em->getRepository('OlegUserdirectoryBundle:RoomList')->findOneByName($locationRoom);
                if (!$room) {
                    exit("No room found by name " . $locationRoom);
                }
                $listEntity->setRoom($room);
            }

            if( $locationSuite ) {
                $suite = $em->getRepository('OlegUserdirectoryBundle:SuiteList')->findOneByName($locationSuite);
                if (!$suite) {
                    exit("No suite found by name " . $locationSuite);
                }
                $listEntity->setSuite($suite);
            }

            if( $locationFloor ) {
                $floor = $em->getRepository('OlegUserdirectoryBundle:FloorList')->findOneByName($locationFloor);
                if( !$floor ) {
                    //exit("No floor found by name " . $locationFloor);
                    $floor = $userSecUtil->getObjectByNameTransformer($username,$locationFloor,"UserdirectoryBundle","FloorList");
                    $em->persist($floor);
                    $em->flush($floor);
                }
                //$floor = $em->getRepository('OlegUserdirectoryBundle:FloorList')->findOneByName($locationFloor);
                if( !$floor ) {
                    exit("No floor found by name " . $locationFloor);
                }
                $listEntity->setFloor($floor);
            }

            if( $locationFloorSide ) {
                $listEntity->setFloorSide($locationFloorSide);
            }

            if( $locationBuildingName ) {
                if( $locationBuildingName == "Greenberg Pavillion" ) {
                    $locationBuildingName = "Greenberg Pavilion";
                }
                $building = $em->getRepository('OlegUserdirectoryBundle:BuildingList')->findOneByName($locationBuildingName);
                if (!$building) {
                    exit("No building type found by name " . $locationBuildingName);
                }
                $listEntity->setBuilding($building);
            }

            //exit('exit generateObjectTypeActions');
            $em->persist($listEntity);
            $em->flush();

            $count = $count + 10;
            //exit('1');
        }

        $em->clear();

        return round($count/10);
    }

    public function generateTestUsers() {

        $testusers = array(
            "testplatformadministrator" => array("ROLE_PLATFORM_ADMIN"),
            "testdeputyplatformadministrator" => array("ROLE_PLATFORM_DEPUTY_ADMIN"),

            "testscanadministrator" => array("ROLE_SCANORDER_ADMIN"),
            "testscanprocessor" => array("ROLE_SCANORDER_PROCESSOR"), //TODO: check auth logic: it ask for access request for scan site
            "testscansubmitter" => array("ROLE_SCANORDER_SUBMITTER"),

            "testuseradministrator" => array("ROLE_SCANORDER_SUBMITTER","ROLE_USERDIRECTORY_ADMIN"),
            "testusereditor" => array("ROLE_SCANORDER_SUBMITTER","ROLE_USERDIRECTORY_EDITOR"),  //TODO: check auth logic: it ask for access request for directory site
            "testuserobserver" => array("ROLE_SCANORDER_SUBMITTER","ROLE_USERDIRECTORY_OBSERVER")
        );

        $userSecUtil = $this->container->get('user_security_utility');
        $userGenerator = $this->container->get('user_generator');
        $userUtil = new UserUtil();
        $em = $this->getDoctrine()->getManager();
        $systemuser = $userUtil->createSystemUser($em,null,null);  //$this->get('security.context')->getToken()->getUser();
        $default_time_zone = $this->container->getParameter('default_time_zone');

//        $em = $this->getDoctrine()->getManager();
//        $entities = $em->getRepository('OlegUserdirectoryBundle:ResearchLab')->findAll();
//
//        if( $entities ) {
//            return -1;
//        }

        $count = 1;
        foreach( $testusers as $testusername => $roles ) {

            $user = new User();
            $userkeytype = $userSecUtil->getUsernameType("aperio");
            $user->setKeytype($userkeytype);
            $user->setPrimaryPublicUserId($testusername);

            //echo "username=".$user->getPrimaryPublicUserId()."<br>";
            $found_user = $em->getRepository('OlegUserdirectoryBundle:User')->findOneByPrimaryPublicUserId( $user->getPrimaryPublicUserId() );
            if( $found_user ) {
                //add scanorder Roles
                foreach( $roles as $role ) {
                    $found_user->addRole($role);
                }
                $em->flush();
                continue;
            }

            //set unique username
            $usernameUnique = $user->createUniqueUsername();
            $user->setUsername($usernameUnique);
            $user->setUsernameCanonical($usernameUnique);

            //$user->setEmail($email);
            //$user->setEmailCanonical($email);
            $user->setFirstName($testusername);
            $user->setLastName($testusername);
            $user->setDisplayName($testusername." ".$testusername);
            $user->setPassword("");
            $user->setCreatedby('system');
            $user->getPreferences()->setTimezone($default_time_zone);

            //add default locations
            $user = $userGenerator->addDefaultLocations($user,$systemuser);

            //phone, fax, office are stored in Location object
            $mainLocation = $user->getMainLocation();
            //$mainLocation->setPhone($phone);
            //$mainLocation->setFax($fax);

            //title is stored in Administrative Title
            $administrativeTitle = new AdministrativeTitle($systemuser);
            $user->addAdministrativeTitle($administrativeTitle);

            //add scanorder Roles
            foreach( $roles as $role ) {
                $user->addRole($role);
            }

            $user->setEnabled(true);
            $user->setLocked(false);
            $user->setExpired(false);

            //record user log create
            $event = "User ".$user." has been created by ".$systemuser."<br>";
            $userSecUtil->createUserEditEvent($this->container->getParameter('employees.sitename'),$event,$systemuser,$user,null,'New user record added');

            $em->persist($user);
            $em->flush();

            //**************** create PerSiteSettings for this user **************//
            //TODO: ideally, this should be located on scanorder site
            $perSiteSettings = new PerSiteSettings($systemuser);
            $perSiteSettings->setUser($user);
            $params = $em->getRepository('OlegUserdirectoryBundle:SiteParameters')->findAll();
            if( count($params) != 1 ) {
                throw new \Exception( 'Must have only one parameter object. Found '.count($params).' object(s)' );
            }
            $param = $params[0];
            $institution = $param->getAutoAssignInstitution();
            $perSiteSettings->addPermittedInstitutionalPHIScope($institution);
            $em->persist($perSiteSettings);
            $em->flush();
            //**************** EOF create PerSiteSettings for this user **************//

            $count++;
        }

        return $count;
    }



    public function generateCompletionReasons() {

        $username = $this->get('security.context')->getToken()->getUser();

        $em = $this->getDoctrine()->getManager();
        $entities = $em->getRepository('OlegUserdirectoryBundle:CompletionReasonList')->findAll();

        if( $entities ) {
            return -1;
        }

        $types = array(
            "Graduated",
            "Transferred"
        );

        $count = 10;
        foreach( $types as $type ) {

            $listEntity = new CompletionReasonList();
            $this->setDefaultList($listEntity,$count,$username,$type);

            $em->persist($listEntity);
            $em->flush();

            $count = $count + 10;
        }

        return round($count/10);
    }

    public function generateTrainingDegrees() {

        $username = $this->get('security.context')->getToken()->getUser();

        $em = $this->getDoctrine()->getManager();
        $entities = $em->getRepository('OlegUserdirectoryBundle:TrainingDegreeList')->findAll();

        if( $entities ) {
            return -1;
        }

        $types = array(
            "MD", "DO", "PhD", "JD", "MBA", "MHA", "MA", "MS", "BS", "BA", "MBBS", "MDCM", "MBChB", "BMed",
            "Dr.Med", "Dr.MuD", "Cand.med", "DMD", "BDent", "DDS", "BDS", "BDSc", "BChD", "CD", "Cand.Odont.",
            "Dr.Med.Dent.", "DNP", "DNAP", "DNS", "DNSc", "OTD", "DrOT", "MSOT", "MOT", "OD", "B.Optom", "BEd",
            "BME", "BSE", "BSocSc", "BSc", "BPharm", "BScPhm", "PharmB", "MPharm", "PharmD", "DPT", "DPhysio",
            "MPT", "BSPT", "MPAS", "MPS", "DPM", "DP", "BPod", "PodB", "PodD", "MPA", "MPS", "PsyD",
            "ClinPsyD", "EdS", "BSN", "DVM", "VMD", "BVS", "BVSc", "BVMS", "MLIS", "MLS", "MSLIS", "BSW"
        );

        $count = 10;
        foreach( $types as $type ) {

            $listEntity = new TrainingDegreeList();
            $this->setDefaultList($listEntity,$count,$username,$type);

            $listEntity->setAbbreviation($type);

            //set "MBBS" and "DO" to be synonyms of "MD" in the List Manager for Degrees
            if( $type == "DO" || $type == "MBBS" ) {
                $mdOriginal = $em->getRepository('OlegUserdirectoryBundle:TrainingDegreeList')->findOneByName("MD");
                $listEntity->setOriginal($mdOriginal);
            }

            $em->persist($listEntity);
            $em->flush();

            $count = $count + 10;
        }

        return round($count/10);
    }

    public function generateResidencySpecialties() {

        $username = $this->get('security.context')->getToken()->getUser();

        $em = $this->getDoctrine()->getManager();

//        $entities = $em->getRepository('OlegUserdirectoryBundle:ResidencySpecialty')->findAll();
//        if( $entities ) {
            //return -1;
//            $query = $em->createQuery('DELETE OlegUserdirectoryBundle:FellowshipSubspecialty c WHERE c.id > 0');
//            $query->execute();
//            $query = $em->createQuery('DELETE OlegUserdirectoryBundle:ResidencySpecialty c WHERE c.id > 0');
//            $query->execute();
//        }

        $inputFileName = __DIR__ . '/../Util/SpecialtiesResidenciesFellowshipsCertified.xlsx';

        try {
            $inputFileType = \PHPExcel_IOFactory::identify($inputFileName);
            $objReader = \PHPExcel_IOFactory::createReader($inputFileType);
            $objPHPExcel = $objReader->load($inputFileName);
        } catch(Exception $e) {
            die('Error loading file "'.pathinfo($inputFileName,PATHINFO_BASENAME).'": '.$e->getMessage());
        }

        $sheet = $objPHPExcel->getSheet(0);
        $highestRow = $sheet->getHighestRow();
        $highestColumn = $sheet->getHighestColumn();

        $lastResidencySpecialtyEntity = null;

        $count = 10;
        $subcount = 1;

        //for each row in excel
        for ($row = 2; $row <= $highestRow; $row++){
            //  Read a row of data into an array
            $rowData = $sheet->rangeToArray('A' . $row . ':' . $highestColumn . $row,
                NULL,
                TRUE,
                FALSE);

            //echo $row.": ";
            //var_dump($rowData);
            //echo "<br>";

            //ResidencySpecialty	FellowshipSubspecialty	BoardCertificationAvailable
            $residencySpecialty = $rowData[0][0];
            $fellowshipSubspecialty = $rowData[0][1];
            $boardCertificationAvailable = $rowData[0][2];
            //echo "residencySpecialty=".$residencySpecialty."<br>";
            //echo "fellowshipSubspecialty=".$fellowshipSubspecialty."<br>";
            //echo "boardCertificationAvailable=".$boardCertificationAvailable."<br>";

            $residencySpecialtyEntity = null;

            if( $residencySpecialty ) {

                $residencySpecialty = trim($residencySpecialty);
                //echo "residencySpecialty=".$residencySpecialty."<br>";

                $residencySpecialtyEntity = $em->getRepository('OlegUserdirectoryBundle:ResidencySpecialty')->findOneByName($residencySpecialty."");

                //if( $em->getRepository('OlegUserdirectoryBundle:ResidencySpecialty')->findOneByName($residencySpecialty."") ) {
                //    continue;
                //}

                if( !$residencySpecialtyEntity ) {
                    $residencySpecialtyEntity = new ResidencySpecialty();
                    $this->setDefaultList($residencySpecialtyEntity,$count,$username,$residencySpecialty);
                }

                if( $boardCertificationAvailable && $boardCertificationAvailable == "Yes" ) {
                    $residencySpecialtyEntity->setBoardCertificateAvailable(true);
                }

                $em->persist($residencySpecialtyEntity);
                $em->flush();

                $lastResidencySpecialtyEntity = $residencySpecialtyEntity;

                $count = $count + 10;
            }

            if( $fellowshipSubspecialty ) {

                $fellowshipSubspecialty = trim($fellowshipSubspecialty);
                //echo "fellowshipSubspecialty=".$fellowshipSubspecialty."<br>";
                $fellowshipSubspecialtyEntity = $em->getRepository('OlegUserdirectoryBundle:FellowshipSubspecialty')->findOneByName($fellowshipSubspecialty."");

                //if( $fellowshipSubspecialtyEntity ) {
                //    continue;
                //}

                if( !$fellowshipSubspecialtyEntity ) {
                    $fellowshipSubspecialtyEntity = new FellowshipSubspecialty();
                    $this->setDefaultList($fellowshipSubspecialtyEntity,$subcount,$username,$fellowshipSubspecialty);
                }


                if( $boardCertificationAvailable && $boardCertificationAvailable == "Yes" ) {
                    $fellowshipSubspecialtyEntity->setBoardCertificateAvailable(true);
                }

                if( $lastResidencySpecialtyEntity ) {
                    $lastResidencySpecialtyEntity->addChild($fellowshipSubspecialtyEntity);
                }

                $em->persist($lastResidencySpecialtyEntity);
                $em->persist($fellowshipSubspecialtyEntity);
                $em->flush();

                $subcount = $subcount + 10;
            }

        }

        $em->clear();

        return round($count/10);
    }


    public function generateHonorTrainings() {

        $username = $this->get('security.context')->getToken()->getUser();

        $em = $this->getDoctrine()->getManager();
        $entities = $em->getRepository('OlegUserdirectoryBundle:HonorTrainingList')->findAll();

        if( $entities ) {
            return -1;
        }

        $types = array(
            "Magna Cum Laude", "Summa Cum Laude", "Cum Laude", "AOA Member"
        );

        $count = 10;
        foreach( $types as $type ) {

            $listEntity = new HonorTrainingList();
            $this->setDefaultList($listEntity,$count,$username,$type);

            $em->persist($listEntity);
            $em->flush();

            $count = $count + 10;
        }

        return round($count/10);
    }

    //Professional Fellowship Title
    public function generateFellowshipTitles() {

        $username = $this->get('security.context')->getToken()->getUser();

        $em = $this->getDoctrine()->getManager();
        $entities = $em->getRepository('OlegUserdirectoryBundle:FellowshipTitleList')->findAll();

        if( $entities ) {
            return -1;
        }

        $types = array(
            "F.C.A.P." => "Fellow of the College of American Pathologists",
            "F.A.A.E.M." => "Fellow of the American Academy of Emergency Medicine",
            "F.A.A.F.P." => "Fellow of the American Academy of Family Physicians",
            "F.A.C.C." => "Fellow of the American College of Cardiologists",
            "F.A.C.E." => "Fellow of the American College of Endocrinology",
            "F.A.C.E.P." => "Fellow of the American College of Emergency Physicians",
            "F.A.C.G." => "Fellow of the American College of Gastroenterology",
            "F.A.C.F.A.S." => "Fellow of the American College of Foot and Ankle Surgeons",
            "F.A.C.O.G." => "Fellow of the American College of Obstetrics and Gynecologists",
            "F.A.C.O.S." => "Fellow of the American College of Osteopathic Surgeons",
            "F.A.C.P." => "Fellow of the American College of Physicians",
            "F.A.C.C.P." => "Fellow of the American College of Chest Physicians",
            "F.A.C.S." => "Fellow of the American College of Surgeons",
            "F.A.S.P.S." => "Fellow of the American Society of Podiatric Surgeons",
            "F.H.M." => "Fellow in Hospital Medicine",
            "F.I.C.S." => "Fellow of the International College of Surgeons",
            "F.S.C.A.I." => "Fellow of the Society for Cardiovascular Angiography and Interventions",
            "F.S.T.S." => "Fellow of the Society of Thoracic Surgeons"
        );

        $count = 10;
        foreach( $types as $abbr => $name ) {

            $listEntity = new FellowshipTitleList();
            $this->setDefaultList($listEntity,$count,$username,$name);

            $listEntity->setAbbreviation($abbr);

            $em->persist($listEntity);
            $em->flush();

            $count = $count + 10;
        }

        return round($count/10);
    }


    public function generatesourceOrganizations() {

        $username = $this->get('security.context')->getToken()->getUser();

        $em = $this->getDoctrine()->getManager();
        $entities = $em->getRepository('OlegUserdirectoryBundle:SourceOrganization')->findAll();

        if( $entities ) {
            return -1;
        }

        $types = array(
            "National Institutes of Health" => "NIH"
        );

        $count = 10;
        foreach( $types as $name => $abbreviation ) {

            $listEntity = new SourceOrganization();
            $this->setDefaultList($listEntity,$count,$username,$name);

            $listEntity->setAbbreviation($abbreviation);

            $em->persist($listEntity);
            $em->flush();

            $count = $count + 10;
        }

        return round($count/10);
    }


    public function generateImportances() {

        $username = $this->get('security.context')->getToken()->getUser();

        $em = $this->getDoctrine()->getManager();
        $entities = $em->getRepository('OlegUserdirectoryBundle:ImportanceList')->findAll();

        if( $entities ) {
            return -1;
        }

        $types = array(
            "#1 - First most important",
            "#2 - Second most important",
            "#3 - Third most important",
            "#4 - Fourth most important",
            "#5 - Fifth most important",
            "Other"
        );

        $count = 10;
        foreach( $types as $name ) {

            $listEntity = new ImportanceList();
            $this->setDefaultList($listEntity,$count,$username,$name);

            $em->persist($listEntity);
            $em->flush();

            $count = $count + 10;
        }

        return round($count/10);
    }


    public function generateAuthorshipRoles() {

        $username = $this->get('security.context')->getToken()->getUser();

        $em = $this->getDoctrine()->getManager();
        $entities = $em->getRepository('OlegUserdirectoryBundle:AuthorshipRoles')->findAll();

        if( $entities ) {
            return -1;
        }

        $types = array(
            "Editor",
            "Chapter Author"
        );

        $count = 10;
        foreach( $types as $name ) {

            $listEntity = new AuthorshipRoles();
            $this->setDefaultList($listEntity,$count,$username,$name);

            $em->persist($listEntity);
            $em->flush();

            $count = $count + 10;
        }

        return round($count/10);
    }


//    public function generateTitlePositionTypes() {
//
//        $username = $this->get('security.context')->getToken()->getUser();
//
//        $em = $this->getDoctrine()->getManager();
//        $entities = $em->getRepository('OlegUserdirectoryBundle:TitlePositionType')->findAll();
//
//        if( $entities ) {
//            return -1;
//        }
//
////        $types = array(
////            'Head',
////            'Manager',
////            'Primary Contact',
////            'Transcriptionist',
////        );
//
//        $types = array(
//            'Head of Institution',
//            'Head of Department',
//            'Head of Division',
//            'Head of Service',
//            'Manager of Institution',
//            'Manager of Department',
//            'Manager of Division',
//            'Manager of Service',
//            'Primary Contact of Institution',
//            'Primary Contact of Department',
//            'Primary Contact of Division',
//            'Primary Contact of Service',
//            'Transcriptionist for the Institution',
//            'Transcriptionist for the Department',
//            'Transcriptionist for the Division',
//            'Transcriptionist for the Service'
//        );
//
//        $count = 10;
//        foreach( $types as $name ) {
//
//            $listEntity = new TitlePositionType();
//            $this->setDefaultList($listEntity,$count,$username,$name);
//
//            $em->persist($listEntity);
//            $em->flush();
//
//            $count = $count + 10;
//        }
//
//        return round($count/10);
//    }


    public function generateSex() {

        $username = $this->get('security.context')->getToken()->getUser();

        $em = $this->getDoctrine()->getManager();
        $entities = $em->getRepository('OlegUserdirectoryBundle:SexList')->findAll();

        if( $entities ) {
            return -1;
        }

        //http://nces.ed.gov/ipeds/reic/definitions.asp
        $types = array(
            'Female' => 'F',
            'Male' => 'M',
            'Unspecified' => 'U'
        );

        $count = 10;
        foreach( $types as $type => $abbreviation ) {

            $listEntity = new SexList();
            $this->setDefaultList($listEntity,$count,$username,$type);
            $listEntity->setAbbreviation($abbreviation);

            $em->persist($listEntity);
            $em->flush();

            $count = $count + 10;
        }

        return round($count/10);
    }


    public function generatePositionTypeList() {

        $username = $this->get('security.context')->getToken()->getUser();

        $em = $this->getDoctrine()->getManager();
        $entities = $em->getRepository('OlegUserdirectoryBundle:PositionTypeList')->findAll();

        if( $entities ) {
            return -1;
        }

        //https://bitbucket.org/weillcornellpathology/scanorder/issue/438/change-institution-division-department
        $types = array(
            'Head of Institution',
            'Head of Department',
            'Head of Division',
            'Head of Service',

            'Manager of Institution',
            'Manager of Department',
            'Manager of Division',
            'Manager of Service',

            'Primary Contact of Institution',
            'Primary Contact of Department',
            'Primary Contact of Division',
            'Primary Contact of Service',

            'Transcriptionist of Institution',
            'Transcriptionist of Department',
            'Transcriptionist of Division',
            'Transcriptionist of Service',
        );

        $count = 10;
        foreach( $types as $type ) {

            $listEntity = new PositionTypeList();
            $this->setDefaultList($listEntity,$count,$username,$type);

            $em->persist($listEntity);
            $em->flush();

            $count = $count + 10;
        }

        return round($count/10);
    }

    public function generateCommentGroupType() {

        $em = $this->getDoctrine()->getManager();
        $entities = $em->getRepository('OlegUserdirectoryBundle:CommentGroupType')->findAll();

        if( $entities ) {
            return -1;
        }

        $elements = array(
            'Comment Category' => 0,
            'Comment Name' => 1,
        );

        $username = $this->get('security.context')->getToken()->getUser();

        $count = 10;
        foreach( $elements as $name=>$level ) {

            $entity = new CommentGroupType();
            $this->setDefaultList($entity,$count,$username,$name);

            $entity->setLevel($level);

            $em->persist($entity);
            $em->flush();

            $count = $count + 10;

        } //foreach

        return round($count/10);

    }

    public function generateSpotPurpose() {

        $em = $this->getDoctrine()->getManager();
        $entities = $em->getRepository('OlegUserdirectoryBundle:SpotPurpose')->findAll();

        if( $entities ) {
            return -1;
        }

        $elements = array(
            'Initial Patient Encounter - Address Entry',
//            'Encounter',
//            'Procedure',
//            'Accession',
//            'Part',
//            'Block',
//            'Slide'
        );

        $username = $this->get('security.context')->getToken()->getUser();

        $count = 10;
        foreach( $elements as $name ) {

            $entity = new SpotPurpose();
            $this->setDefaultList($entity,$count,$username,$name);

            $em->persist($entity);
            $em->flush();

            $count = $count + 10;

        } //foreach

        return round($count/10);

    }

    public function generateMedicalLicenseStatus() {

        $em = $this->getDoctrine()->getManager();
        $entities = $em->getRepository('OlegUserdirectoryBundle:MedicalLicenseStatus')->findAll();

        if( $entities ) {
            return -1;
        }

        $elements = array(
            'Yes',
            'No',
        );

        $username = $this->get('security.context')->getToken()->getUser();

        $count = 10;
        foreach( $elements as $name ) {

            $entity = new MedicalLicenseStatus();
            $this->setDefaultList($entity,$count,$username,$name);

            $em->persist($entity);
            $em->flush();

            $count = $count + 10;

        } //foreach

        return round($count/10);

    }


    public function generateCertifyingBoardOrganization() {

        $em = $this->getDoctrine()->getManager();
        $entities = $em->getRepository('OlegUserdirectoryBundle:CertifyingBoardOrganization')->findAll();

        if( $entities ) {
            return -1;
        }

        $elements = array(
            'American Board of Pathology',
        );

        $username = $this->get('security.context')->getToken()->getUser();

        $count = 10;
        foreach( $elements as $name ) {

            $entity = new CertifyingBoardOrganization();
            $this->setDefaultList($entity,$count,$username,$name);

            $em->persist($entity);
            $em->flush();

            $count = $count + 10;

        } //foreach

        return round($count/10);

    }

    public function generateTrainingTypeList() {

        $em = $this->getDoctrine()->getManager();

        //$entities = $em->getRepository('OlegUserdirectoryBundle:TrainingTypeList')->findAll();
        //if( $entities ) {
        //    return -1;
        //}

        $elements = array(
            'Undergraduate',
            'Graduate',
            'Medical',
            'Residency',
            'Post-Residency Fellowship',
            'GME',
            'Other'
        );

        $username = $this->get('security.context')->getToken()->getUser();

        $count = 10;
        foreach( $elements as $name ) {

            $entity = $em->getRepository('OlegUserdirectoryBundle:TrainingTypeList')->findOneByName($name);
            if( $entity ) {
                continue;
            }

            $entity = new TrainingTypeList();
            $this->setDefaultList($entity,$count,$username,$name);

            $em->persist($entity);
            $em->flush();

            $count = $count + 10;

        } //foreach

        return round($count/10);

    }

    public function generateAppTitlePositions() {

        $em = $this->getDoctrine()->getManager();
        $entities = $em->getRepository('OlegUserdirectoryBundle:PositionTrackTypeList')->findAll();

        if( $entities ) {
            return -1;
        }

        $elements = array(
            'Resident',
            'Fellow',
            'Clinical Faculty',
            'Research Faculty'
        );

        $username = $this->get('security.context')->getToken()->getUser();

        $count = 10;
        foreach( $elements as $name ) {

            $entity = new PositionTrackTypeList();
            $this->setDefaultList($entity,$count,$username,$name);

            $em->persist($entity);
            $em->flush();

            $count = $count + 10;

        } //foreach

        return round($count/10);

    }


    public function generateFellAppStatus() {

        $em = $this->getDoctrine()->getManager();

        //$entities = $em->getRepository('OlegFellAppBundle:FellAppStatus')->findAll();
        //if( $entities ) {
        //    return -1;
        //}

        $elements = array(
            'active'=>'Active',
            'complete'=>'Complete',
            'interviewee'=>'Interviewee',
            'onhold'=>'On Hold',
            'reject'=>'Rejected',
            //'hide'=>'Hidden',
            'priority'=>'Priority',
            'archive'=>'Archived',
        );

        $username = $this->get('security.context')->getToken()->getUser();

        $count = 10;
        foreach( $elements as $name=>$action ) {

            $listEntity = $em->getRepository('OlegFellAppBundle:FellAppStatus')->findOneByName($name);
            if( $listEntity ) {
                continue;
            }

            $entity = new FellAppStatus();
            $this->setDefaultList($entity,$count,$username,$name);
            $entity->setAction($action);

            $em->persist($entity);
            $em->flush();

            $count = $count + 10;

        } //foreach

        return round($count/10);

    }

    public function generateFellAppRank() {

        $em = $this->getDoctrine()->getManager();
        $entities = $em->getRepository('OlegFellAppBundle:FellAppRank')->findAll();

        if( $entities ) {
            return -1;
        }

        $elements = array(
            '1 (Excellent)'=>1,
            '1.5'=>1.5,
            '2 (Average)'=>2,
            '2.5'=>2.5,
            '3 (Below Average)'=>3
        );

        $username = $this->get('security.context')->getToken()->getUser();

        $count = 10;
        foreach( $elements as $name=>$value ) {

            $entity = new FellAppRank();
            $this->setDefaultList($entity,$count,$username,$name);
            $entity->setValue($value);

            $em->persist($entity);
            $em->flush();

            $count = $count + 10;

        } //foreach

        return round($count/10);

    }

    public function generateLanguageProficiency() {

        $em = $this->getDoctrine()->getManager();
        $entities = $em->getRepository('OlegFellAppBundle:LanguageProficiency')->findAll();

        if( $entities ) {
            return -1;
        }

        $elements = array(
            "Excellent",
            "Adequate",
            "Inadequate",
            "N/A"
        );

        $username = $this->get('security.context')->getToken()->getUser();

        $count = 10;
        foreach( $elements as $name ) {

            $entity = new LanguageProficiency();
            $this->setDefaultList($entity,$count,$username,$name);

            $em->persist($entity);
            $em->flush();

            $count = $count + 10;

        } //foreach

        return round($count/10);

    }

    public function generateVacReqRequestTypeList() {

        $username = $this->get('security.context')->getToken()->getUser();

        $em = $this->getDoctrine()->getManager();

        $types = array(
            "Business Travel / Vacation Request" => "business-vacation",
            "Carry Over Request" => "carryover",
        );

        $count = 10;
        foreach( $types as $name => $abbreviation ) {

            $listEntity = $em->getRepository('OlegVacReqBundle:VacReqRequestTypeList')->findOneByName($name);
            if( $listEntity ) {
                continue;
            }

            $listEntity = new VacReqRequestTypeList();
            $this->setDefaultList($listEntity,$count,$username,$name);

            $listEntity->setAbbreviation($abbreviation);

            $em->persist($listEntity);
            $em->flush();

            $count = $count + 10;
        }

        return round($count/10);
    }

    public function generateHealthcareProviderSpecialtiesList() {

        $username = $this->get('security.context')->getToken()->getUser();

        $em = $this->getDoctrine()->getManager();

        $types = array(
            "Blood Bank Personnel",
            "Microbiology Lab Personnel",
            "Cellular Therapy Lab Personnel",
            "Coagulation Lab Personnel",
            "Toxicology Lab Personnel",
            "Molecular Lab Personnel",
            "Cytogenetics Lab Personnel",
            "Central Lab Personnel",
            "OR nurse",
            "OR anesthesiologist",
            "OR surgeon",
            "Floor nurse",
            "Floor physician",
            "Floor PA",
            "Hospital administrator",
            "Infusion center nurse",
            "Infusion center physician",
            "Infusion center nurse practitioner",
            "Medical student",
            "ER physician",
            "ER nurse",
            "ER PA",
            "Interventional radiology physician",
            "Interventional radiology nurse",
            "Interventional radiology PA",
            "Endoscopy physician",
            "Endoscopy nurse",
            "Endoscopy PA"
        );

        $count = 10;
        foreach( $types as $name ) {

            $listEntity = $em->getRepository('OlegUserdirectoryBundle:HealthcareProviderSpecialtiesList')->findOneByName($name);
            if( $listEntity ) {
                continue;
            }

            $listEntity = new HealthcareProviderSpecialtiesList();
            $this->setDefaultList($listEntity,$count,$username,$name);

            $em->persist($listEntity);
            $em->flush();

            $count = $count + 10;
        }

        return round($count/10);
    }

    public function generateCollaborationtypes() {

        $em = $this->getDoctrine()->getManager();
        $entities = $em->getRepository('OlegUserdirectoryBundle:CollaborationTypeList')->findAll();

        if( $entities ) {
            return -1;
        }

//        "Menu Search Action" - "Search patients data and orders within collaboration institutions on the top search"
//        "Check Button Retrieve Action" - "Retrieve patients data within collaboration institutions on the check button click"
//        "View Action" - "View patient data on check button pressed in order form and view orders within collaboration institutions"
//        "Submit Action" - "Submit orders for collaboration institution"

        $elements = array(
            "Union" => 'Bidirectional collaboration: Users within this type of collaboration have full access to the patient data.'.
                       'Supported actions: "Menu Search Action", "Check Button Retrieve Action", "View Action", "Submit Action""',
            "Intersection" => 'Unidirectional trusted collaboration: Users withini this type of collaboration can view and submit new orders in the same way as bidirectional collaboration.'.
                              'Supported actions: "Check Button Retrieve Action", "View Action", "Submit Action"',
            "Untrusted Intersection" => 'Unidirectional untrusted: Supported actions: "Submit Action".'.
                                        'If the user enters an existing MRN number and click check button it will retrieve empty data, so the user can enter a new data which will marked as "invalid".'
        );

        $username = $this->get('security.context')->getToken()->getUser();

        $count = 10;
        foreach( $elements as $name => $description ) {

            $entity = new CollaborationTypeList();
            $this->setDefaultList($entity,$count,$username,$name);

            $entity->setDescription($description);

            $em->persist($entity);
            $em->flush();

            $count = $count + 10;

        } //foreach

        return round($count/10);

    }

    public function generatePermissions() {

        $username = $this->get('security.context')->getToken()->getUser();

        $em = $this->getDoctrine()->getManager();
//        $entities = $em->getRepository('OlegUserdirectoryBundle:PermissionList')->findAll();
//        if( $entities ) {
//            return -1;
//        }

        $types = array(
            "View Patient Data for a given patient", //(our "check" button AND our "Test Patient" view page)
            "Add Patient Data",
            "Modify Patient Data",
            "Delete Patient Data", //(or mark it inactive/invalid since we don't delete; this and 3 above are for Data Quality role)
            "Add a New Patient",
            "Add a New Encounter",
            "Add a New Procedure",
            "Add a New Accession",
            "Add a New Part",
            "Add a New Block",
            "Add a New Slide",
            "Add a New Image",
            "Submit Orders",
            "Sign Orders",      //(if it is a two-step process - submit into a queue then someone else signs)
            "Submit Results",
            "Sign Results",     //(if it is a two-step process - submit into a queue then someone else signs)
            "Change the status of an order",
            "Change a status of a result",
            "Browse/search incoming orders for a given organizational group",
            "Browse/search outgoing orders for a given organizational group",
            "Browse/search incoming results for a given organizational group",
            "Browse/search outgoing results for a given organizational group",
            "Browse/search patients that 'belong' to a given organizational group",
            "Browse/search accessions that 'belong' to a given organizational group",

            "Search by Deidentifier ID",
            "Generate new Deidentifier ID",

            "Submit an interview evaluation",
            "Create a New Fellowship Application",
            "Modify a Fellowship Application",
            "View a Fellowship Application",

            "Submit a Vacation Request",
            "Approve a Vacation Request",
            "Approve a Carry Over Request",

            "Create Call Log Entry",
            "Edit Call Log Entry",
            "Change Status of Call Log Entry",
            "Hide Call Log Entry",
            "Change Status of Patient to Complex",
            "Change Status of Patient away from Complex",
            "Create Patient Record",
            "Edit Patient Record",
            "Hide Patient Record",
            "Delete Patient Record",
            "Read Patient Record",
            "Merge Patient Record"
        );

        $count = 10;
        foreach( $types as $type ) {

            $listEntity = $em->getRepository('OlegUserdirectoryBundle:PermissionList')->findOneByName($type);
            if( $listEntity ) {
                continue;
            }

            $listEntity = new PermissionList();
            $this->setDefaultList($listEntity,$count,$username,$type);

            $em->persist($listEntity);
            $em->flush();

            $count = $count + 10;
        }

        return round($count/10);
    }

    public function generatePermissionObjects() {

        $username = $this->get('security.context')->getToken()->getUser();

        $em = $this->getDoctrine()->getManager();

//        $entities = $em->getRepository('OlegUserdirectoryBundle:PermissionObjectList')->findAll();
//        if( $entities ) {
//            return -1;
//        }

        $types = array(
            "Patient" => array("",array("scan")),
            "Patient Record" => array("",array("scan","calllog")),
            "Encounter" => array("",array("scan")),
            "Procedure" => array("",array("scan")),

            "Accession" => array("",array("scan","deidentifier")),

            "Part" => array("",array("scan")),
            "Block" => array("",array("scan")),
            "Slide" => array("",array("scan")),
            "Image" => array("Imaging",array("scan")),
            "Image Analysis" => array("",array("scan")),
            "Order" => array("Message",array("scan")),
            "Report" => array("",array("scan")),

            "Interview" => array("",array("fellapp")),
            "FellowshipApplication" => array("",array("fellapp")),

            "VacReqRequest" => array("",array("vacreq")), //"Business/Vacation Request"

            "Call Log Entry" => array("",array("calllog")),
            "Complex Patient" => array("",array("calllog")),


        );

        $count = 10;
        foreach( $types as $name => $abbreviationSiteArr ) {

            if( !$name || $name == "" ) {
                continue;
            }

            $listEntity = $em->getRepository('OlegUserdirectoryBundle:PermissionObjectList')->findOneByName($name);
            if( $listEntity ) {

                $abbreviation = $abbreviationSiteArr[0];
                if( $abbreviation && $abbreviation != "" ) {
                    if( !$listEntity->getAbbreviation() ) {
                        $listEntity->setAbbreviation($abbreviation);
                        $em->persist($listEntity);
                        $em->flush();
                    }
                }

                $sites = $abbreviationSiteArr[1];
                foreach( $sites as $site ) {
                    $siteObject = $em->getRepository('OlegUserdirectoryBundle:SiteList')->findOneByAbbreviation($site);
                    if( !$listEntity->getSites()->contains($siteObject) ) {
                        $listEntity->addSite($siteObject);
                        $em->persist($listEntity);
                        $em->flush();
                    }
                }

                continue;
            }

            //Create new
            $listEntity = new PermissionObjectList();
            $this->setDefaultList($listEntity,$count,$username,$name);

            $sites = $abbreviationSiteArr[1];
            foreach( $sites as $site ) {
                $siteObject = $em->getRepository('OlegUserdirectoryBundle:SiteList')->findOneByAbbreviation($site);
                if( !$listEntity->getSites()->contains($siteObject) ) {
                    $listEntity->addSite($siteObject);
                    //$em->persist($listEntity);
                    //$em->flush();
                }
            }

            $em->persist($listEntity);
            $em->flush();

            $count = $count + 10;
        }

        return round($count/10);
    }

    public function generatePermissionActions() {

        $username = $this->get('security.context')->getToken()->getUser();

        $em = $this->getDoctrine()->getManager();
//        $entities = $em->getRepository('OlegUserdirectoryBundle:PermissionActionList')->findAll();
//        if( $entities ) {
//            return -1;
//        }

        $types = array(
            "create",
            "read",
            "update",
            "delete",
            "changestatus",

            "changestatus-carryover",
            "view-away-calendar",

            "merge",
            "hide",
            "changestatus-to-complex",
            "changestatus-from-complex",

        );

        $count = 10;
        foreach( $types as $type ) {

            $listEntity = $em->getRepository('OlegUserdirectoryBundle:PermissionActionList')->findOneByName($type);
            if( $listEntity ) {
                continue;
            }

            $listEntity = new PermissionActionList();
            $this->setDefaultList($listEntity,$count,$username,$type);

            $em->persist($listEntity);
            $em->flush();

            $count = $count + 10;
        }

        return round($count/10);
    }

    public function generateObjectTypeActions() {

        $username = $this->get('security.context')->getToken()->getUser();

        $em = $this->getDoctrine()->getManager();
//        $entities = $em->getRepository('OlegUserdirectoryBundle:PermissionActionList')->findAll();
//        if( $entities ) {
//            return -1;
//        }

        $types = array(
            "Form Group",
            "Form",
            "Form Section",
            "Form Section Array",

            //String
            array(
                'name' => "Form Field - Free Text, Single Line",
                'receivedValueEntityNamespace' => 'Oleg\UserdirectoryBundle\Entity',
                'receivedValueEntityName' => 'ObjectTypeString'
            ),
            array(
                'name' => "Form Field - Free Text, Single Line, Locked, Calculated, Stored",
                'receivedValueEntityNamespace' => 'Oleg\UserdirectoryBundle\Entity',
                'receivedValueEntityName' => 'ObjectTypeString'
            ),
            array(
                'name' => "Form Field - Free Text, Single Line, Unlocked, Calculated, Stored",
                'receivedValueEntityNamespace' => 'Oleg\UserdirectoryBundle\Entity',
                'receivedValueEntityName' => 'ObjectTypeString'
            ),
            array(
                'name' => "Form Field - Free Text, Single Line, Numeric, Unsigned Positive Integer",
                'receivedValueEntityNamespace' => 'Oleg\UserdirectoryBundle\Entity',
                'receivedValueEntityName' => 'ObjectTypeString'
            ),
            array(
                'name' => "Form Field - Free Text, Single Line, Numeric, Signed Integer",
                'receivedValueEntityNamespace' => 'Oleg\UserdirectoryBundle\Entity',
                'receivedValueEntityName' => 'ObjectTypeString'
            ),
            array(
                'name' => "Form Field - Free Text, Single Line, Numeric, Signed Float",
                'receivedValueEntityNamespace' => 'Oleg\UserdirectoryBundle\Entity',
                'receivedValueEntityName' => 'ObjectTypeString'
            ),
            array(
                'name' => "Form Field - Free Text, Single Line, Locked, Calculated, Visual Aid",
                'receivedValueEntityNamespace' => 'Oleg\UserdirectoryBundle\Entity',
                'receivedValueEntityName' => 'ObjectTypeString'
            ),

            //Text
            array(
                'name' => "Form Field - Free Text",
                'receivedValueEntityNamespace' => 'Oleg\UserdirectoryBundle\Entity',
                'receivedValueEntityName' => 'ObjectTypeText'
            ),
            array(
                'name' => "Form Field - Free Text, RTF",
                'receivedValueEntityNamespace' => 'Oleg\UserdirectoryBundle\Entity',
                'receivedValueEntityName' => 'ObjectTypeText'
            ),
            array(
                'name' => "Form Field - Free Text, HTML",
                'receivedValueEntityNamespace' => 'Oleg\UserdirectoryBundle\Entity',
                'receivedValueEntityName' => 'ObjectTypeText'
            ),

            //Dates
            array(
                'name' => "Form Field - Full Date",
                'receivedValueEntityNamespace' => 'Oleg\UserdirectoryBundle\Entity',
                'receivedValueEntityName' => 'ObjectTypeDateTime'
            ),
            array(
                'name' => "Form Field - Time",
                'receivedValueEntityNamespace' => 'Oleg\UserdirectoryBundle\Entity',
                'receivedValueEntityName' => 'ObjectTypeDateTime'
            ),
            array(
                'name' => "Form Field - Full Date and Time",
                'receivedValueEntityNamespace' => 'Oleg\UserdirectoryBundle\Entity',
                'receivedValueEntityName' => 'ObjectTypeDateTime'
            ),
            array(
                'name' => "Form Field - Year",
                'receivedValueEntityNamespace' => 'Oleg\UserdirectoryBundle\Entity',
                'receivedValueEntityName' => 'ObjectTypeDateTime'
            ),
            array(
                'name' => "Form Field - Month",
                'receivedValueEntityNamespace' => 'Oleg\UserdirectoryBundle\Entity',
                'receivedValueEntityName' => 'ObjectTypeDateTime',
            ),
            array(
                'name' => "Form Field - Date",
                'receivedValueEntityNamespace' => 'Oleg\UserdirectoryBundle\Entity',
                'receivedValueEntityName' => 'ObjectTypeDateTime'
            ),
            array(
                'name' => "Form Field - Day of the Week",
                'receivedValueEntityNamespace' => 'Oleg\UserdirectoryBundle\Entity',
                'receivedValueEntityName' => 'ObjectTypeDateTime',
            ),
            array(
                'name' => "Form Field - Time, with Time Zone",
                'receivedValueEntityNamespace' => 'Oleg\UserdirectoryBundle\Entity',
                'receivedValueEntityName' => 'ObjectTypeDateTime'
            ),
            array(
                'name' => "Form Field - Full Date and Time, with Time Zone",
                'receivedValueEntityNamespace' => 'Oleg\UserdirectoryBundle\Entity',
                'receivedValueEntityName' => 'ObjectTypeDateTime'
            ),

            //Dropdown
            array(
                'name' => "Form Field - Dropdown Menu",
                'receivedValueEntityNamespace' => 'Oleg\UserdirectoryBundle\Entity',
                'receivedValueEntityName' => 'ObjectTypeDropdown'
            ),
            array(
                'name' => "Form Field - Dropdown Menu - Allow New Entries",
                'receivedValueEntityNamespace' => 'Oleg\UserdirectoryBundle\Entity',
                'receivedValueEntityName' => 'ObjectTypeDropdown'
            ),
            array(
                'name' => "Form Field - Dropdown Menu - Allow Multiple Selections - Allow New Entries",
                'receivedValueEntityNamespace' => 'Oleg\UserdirectoryBundle\Entity',
                'receivedValueEntityName' => 'ObjectTypeDropdown'
            ),
            array(
                'name' => "Form Field - Dropdown Menu - Allow Multiple Selections",
                'receivedValueEntityNamespace' => 'Oleg\UserdirectoryBundle\Entity',
                'receivedValueEntityName' => 'ObjectTypeDropdown'
            ),
            //"Dropdown Menu Value",

            //Patient
            array(
                'name' => "Linked Object - Patient",
                'receivedValueEntityNamespace' => 'Oleg\OrderformBundle\Entity',
                'receivedValueEntityName' => 'Patient'
            ),

            //Checkbox
            array(
                'name' => "Form Field - Checkbox",
                'receivedValueEntityNamespace' => 'Oleg\UserdirectoryBundle\Entity',
                'receivedValueEntityName' => 'ObjectTypeCheckbox'
            ),
            array(
                'name' => "Form Field - Checkboxes",
                'receivedValueEntityNamespace' => 'Oleg\UserdirectoryBundle\Entity',
                'receivedValueEntityName' => 'ObjectTypeCheckbox'
            ),

            //Radio
            array(
                'name' => "Form Field - Radio Button",
                'receivedValueEntityNamespace' => 'Oleg\UserdirectoryBundle\Entity',
                'receivedValueEntityName' => 'ObjectTypeRadioButton' //radio button is very similar to the dropdown menu
            ),

            //User
//            array(
//                'name' => "Linked Object - User",
//                'receivedValueEntityNamespace' => 'Oleg\OrderformBundle\Entity',
//                'receivedValueEntityName' => 'PathologyResultSignatories'
//            ),



        );

        $count = 10;
        foreach( $types as $type ) {

            if( is_array($type) ) {
                $name = $type['name'];
                $receivedValueEntityNamespace = $type['receivedValueEntityNamespace'];
                $receivedValueEntityName = $type['receivedValueEntityName'];
            } else {
                //echo "string ";
                $name = $type;
                $receivedValueEntityNamespace = NULL;
                $receivedValueEntityName = NULL;
            }

            //echo "name=".$name."<br>";

            $listEntity = $em->getRepository('OlegUserdirectoryBundle:ObjectTypeList')->findOneByName($name);
            if( $listEntity ) {
                $updated = false;
                if( !$listEntity->getReceivedValueEntityNamespace() && $receivedValueEntityNamespace ) {
                    $listEntity->setReceivedValueEntityNamespace($receivedValueEntityNamespace);
                    $updated = true;
                }
                if( !$listEntity->getReceivedValueEntityName() && $receivedValueEntityName ) {
                    $listEntity->setReceivedValueEntityName($receivedValueEntityName);
                    $updated = true;
                }

                if( $listEntity->getEntityNamespace() ) {
                    $listEntity->setEntityNamespace(NULL);
                    $updated = true;
                }
                if( $listEntity->getEntityName() ) {
                    $listEntity->setEntityName(NULL);
                    $updated = true;
                }

                if( $updated ) {
                    $em->persist($listEntity);
                    $em->flush();
                }
                continue;
            }

            $listEntity = new ObjectTypeList();
            $this->setDefaultList($listEntity,null,$username,$name);

            if( $receivedValueEntityNamespace ) {
                //echo "receivedValueEntityNamespace=".$receivedValueEntityNamespace."<br>";
                $listEntity->setReceivedValueEntityNamespace($receivedValueEntityNamespace);
            }
            if( $receivedValueEntityName ) {
                //echo "entityName=".$receivedValueEntityName."<br>";
                $listEntity->setReceivedValueEntityName($receivedValueEntityName);
            }

            //exit('exit generateObjectTypeActions');
            $em->persist($listEntity);
            $em->flush();

            $count = $count + 10;
        }

        return round($count/10);
    }
    //set "Object Type"="Dropdown Menu Value" (referring to this list) for all items/rows on all lists
    // except the root Platform List Manager List where all items should have "Object Type"="Form Field - Dropdown Menu"
    public function setObjectTypeForAllLists() {
        return -1;

        $children  = array();
        $classes = get_declared_classes();
        print_r($classes);
        foreach( $classes as $class ){
            //echo "0 class=".$class."<br>";
            if( $class instanceof ListAbstract ) {
            //if( is_subclass_of( $class, 'ListAbstract' ) ) {
                $children[] = $class;
                echo "ListAbstract class=".$class."<br>";
            }
        }

        //echo $this->get_extends_number('ListAbstract')."<br>";

        exit('exit');
    }


    public function generateEventObjectTypeList() {

        $username = $this->get('security.context')->getToken()->getUser();

        $em = $this->getDoctrine()->getManager();

        $repository = $this->getDoctrine()->getRepository('OlegUserdirectoryBundle:Logger');
        $query = $repository->createQueryBuilder('logger')
            ->select('logger.entityName')
            ->distinct()
            ->getQuery();
        $types = $query->getResult();

//        if( count($types) == 0 ) {
//            $types = array(
//                "User",
//                "SiteList",
//                "FellowshipApplication",
//                "Accession",
//                "AccessionAccession",
//                "Roles"
//            );
//        }

        $count = 10;
        foreach( $types as $type ) {

            //print_r($type);
            //$entityName = $type;    //$type['entityName'];
            $entityName = $type['entityName'];
            //echo "entityName=".$entityName."<br>";
            //exit('1');

            if( !$entityName ) {
                continue;
            }
            //echo "entityName=".$entityName."<br>";

            $listEntity = $em->getRepository('OlegUserdirectoryBundle:EventObjectTypeList')->findOneByName($entityName);
            if( $listEntity ) {
                continue;
            }

            $listEntity = new EventObjectTypeList();
            $this->setDefaultList($listEntity,$count,$username,$entityName);

            $em->persist($listEntity);
            $em->flush();

            $count = $count + 10;
        }

        return round($count/10);
    }

    /**
     * populate Platform List Manager Root List: url="order/directory/admin/list-manager-populate/"
     * @Route("/list-manager-populate/", name="user_populate_platform_list_manager")
     * @Method("GET")
     */
    public function generatePlatformListManagerList( Request $request=null ) {

        $username = $this->get('security.context')->getToken()->getUser();

        $em = $this->getDoctrine()->getManager();

        $types = array(
            "1"  => array('PlatformListManagerRootList','platformlistmanager-list'),
            "10" => array('SiteList','sites-list'),
            "20" => array('SourceSystemList','sourcesystems-list'),
            "30" => array('Roles','role-list'),
            "40" => array('Institution','institutions-list'),
            "50" => array('States','states-list'),
            "60" => array('Countries','countries-list'),
            "70" => array('BoardCertifiedSpecialties','boardcertifications-list'),
            "80" => array('EmploymentType','employmenttypes-list'),
            "90" => array('EmploymentTerminationType','employmentterminations-list'),

            "100" => array('EventTypeList','loggereventtypes-list'),
            "110" => array('UsernameType','usernametypes-list'),
            "120" => array('IdentifierTypeList','identifiers-list'),
            "130" => array('ResidencyTrackList','residencytracks-list'),
            "140" => array('FellowshipTypeList','fellowshiptypes-list'),
            "150" => array('LocationTypeList','locationtypes-list'),
            "160" => array('Equipment','equipments-list'),
            "170" => array('EquipmentType','equipmenttypes-list'),
            "180" => array('LocationPrivacyList','locationprivacy-list'),
            "190" => array('RoleAttributeList','roleattributes-list'),

            "200" => array('BuildingList','buildings-list'),
            "210" => array('RoomList','rooms-list'),
            "220" => array('SuiteList','suites-list'),
            "230" => array('FloorList','floors-list'),
            "240" => array('MailboxList','mailboxes-list'),
            "250" => array('EffortList','efforts-list'),
            "260" => array('AdminTitleList','admintitles-list'),
            "270" => array('AppTitleList','apptitles-list'),
            "280" => array('CompletionReasonList','completionreasons-list'),
            "290" => array('TrainingDegreeList','trainingdegrees-list'),

            "300" => array('MajorTrainingList','trainingmajors-list'),
            "310" => array('MinorTrainingList','trainingminors-list'),
            "320" => array('HonorTrainingList','traininghonors-list'),
            "330" => array('FellowshipTitleList','fellowshiptitles-list'),
            "340" => array('ResidencySpecialty','residencyspecialtys-list'),
            "350" => array('FellowshipSubspecialty','fellowshipsubspecialtys-list'),
            "360" => array('InstitutionType','institutiontypes-list'),
            "370" => array('DocumentTypeList','documenttypes-list'),
            "380" => array('MedicalTitleList','medicaltitles-list'),
            "390" => array('MedicalSpecialties','medicalspecialties-list'),

            "400" => array('SourceOrganization','sourceorganizations-list'),
            "410" => array('LanguageList','languages-list'),
            "420" => array('ImportanceList','importances-list'),
            "430" => array('LocaleList','locales-list'),
            "450" => array('AuthorshipRoles','authorshiproles-list'),
            "460" => array('OrganizationList','organizations-list'),
            "470" => array('CityList','cities-list'),
            "480" => array('LinkTypeList','linktypes-list'),
            "490" => array('SexList','sexes-list'),

            "500" => array('PositionTypeList','positiontypes-list'),
            "510" => array('OrganizationalGroupType','organizationalgrouptypes-list'),
            "520" => array('CommentTypeList','commenttypes-list'),
            "530" => array('CommentGroupType','commentgrouptypes-list'),
            "540" => array('UserWrapper','userwrappers-list'),
            "550" => array('SpotPurpose','spotpurposes-list'),
            "560" => array('MedicalLicenseStatus','medicalstatuses-list'),
            "570" => array('CertifyingBoardOrganization','certifyingboardorganizations-list'),
            "580" => array('TrainingTypeList','trainingtypes-list'),
            "590" => array('JobTitleList','joblists-list'),

            "600" => array('FellAppStatus','fellappstatuses-list','Fellowship Application Status'),
            "610" => array('FellAppRank','fellappranks-list','Fellowship Application Rank'),
            "620" => array('LanguageProficiency','fellapplanguageproficiency-list'),
            "630" => array('CollaborationTypeList','collaborationtypes-list'),
            "640" => array('PermissionList','permission-list'),
            "650" => array('PermissionObjectList','permissionobject-list'),
            "660" => array('PermissionActionList','permissionaction-list'),
            "670" => array('EventObjectTypeList','eventobjecttypes-list'),
            "680" => array('VacReqRequestTypeList','vacreqrequesttypes-list','Vacation Request Type'),

            //added on December 7 2016, after July 19th, 2016
            "690" => array('HealthcareProviderSpecialtiesList','healthcareproviderspecialty-list','Healthcare Provider Specialty'),
            "700" => array('MessageTypeClassifiers','messagetypeclassifiers-list'),
            "710" => array('AmendmentReasonList','amendmentreasons-list'),
            "720" => array('ObjectTypeList','objecttypes-list'),
            //"730" => array('PathologyCallComplexPatients','pathologycallcomplexpatients-list'),
            "730" => array('PatientListHierarchy','patientlisthierarchys-list'),
            "740" => array('PatientListHierarchyGroupType','patientlisthierarchygrouptype-list'),
            "750" => array('ObjectTypeText','objecttypetexts-list'),
            "760" => array('ObjectTypeString','objecttypestrings-list'),
            "770" => array('ObjectTypeDropdown','objecttypedropdowns-list'),
            "780" => array('BloodProductTransfusedList','bloodproducttransfusions-list'),
            "790" => array('TransfusionReactionTypeList','transfusionreactiontypes-list'),
            "800" => array('BloodTypeList','bloodtypes-list'),
            "810" => array('TransfusionAntibodyScreenResultsList','transfusionantibodyscreenresults-list'),
            "820" => array('TransfusionCrossmatchResultsList','transfusioncrossmatchresults-list'),
            "830" => array('TransfusionDATResultsList','transfusiondatresults-list','Transfusion DAT Results List'),
            "840" => array('TransfusionHemolysisCheckResultsList','transfusionhemolysischeckresults-list'),
            "850" => array('ObjectTypeDateTime','objecttypedatetimes-list'),
            "860" => array('ComplexPlateletSummaryAntibodiesList','complexplateletsummaryantibodies-list'),
            "870" => array('CCIUnitPlateletCountDefaultValueList','cciunitplateletcountdefaultvalues-list','CCI Unit Platelet Count Default Value List'),
            "880" => array('CCIPlateletTypeTransfusedList','cciplatelettypetransfuseds-list','CCI Platelet Type Transfused List'),
            "890" => array('PlateletTransfusionProductReceivingList','platelettransfusionproductreceivings-list'),
            "900" => array('TransfusionProductStatusList','transfusionproductstatus-list'),
            "910" => array('EncounterInfoTypeList','encounterinfotypes-list'),
            "920" => array('EncounterStatusList','encounterstatuses-list'),
            "930" => array('PatientRecordStatusList','patientrecordstatuses-list'),
            "940" => array('MessageStatusList','messagestatuses-list'),
            "950" => array('WeekDaysList','weekdays-list'),
            "960" => array('MonthsList','months-list'),
            "970" => array('FormNode','formnodes-list','Flat Form Tree'),
            "980" => array('ClericalErrorList','clericalerrors-list'),
            "990" => array('LabResultNameList','labresultnames-list','Lab Result Names'),
            "1000" => array('LabResultUnitsMeasureList','labresultunitsmeasures-list','Lab Result Units of Measure List'),
            "1010" => array('LabResultFlagList','labresultflags-list','Lab Result Flag List'),
            "1020" => array('PathologyResultSignatoriesList','pathologyresultsignatories-list','Pathology Result Signatories List'),
            "1030" => array('ObjectTypeCheckbox','objecttypecheckboxs-list','Object Type Checkbox'),
            "1040" => array('ObjectTypeRadioButton','objecttyperadiobuttons-list','Object Type Radio Button'),
            "1050" => array('Location','employees_locations_pathaction_list','Locations'),
            "1060" => array('LifeFormList','lifeforms-list','Life Form List'),
            //"1050" => array('','-list'),

        );

        $count = 10;
        foreach( $types as $listId => $listArr ) {

            $listName = $listArr[0];
            $listRootName = $listArr[1];

            if( count($listArr) == 3 ) {
                $nameClean = $listArr[2];
            } else {
                $nameClean = null;
            }

//            $listEntity = $em->getRepository('OlegUserdirectoryBundle:PlatformListManagerRootList')->findOneByListId($listId);
//            if( $listEntity ) {
//                echo 'exists listId='.$listId."<br>";
//                continue;
//            }

            $listEntity = $em->getRepository('OlegUserdirectoryBundle:PlatformListManagerRootList')->findOneByListRootName($listRootName);
            if( $listEntity ) {
                //exit('exists listRootName='.$listRootName);
                continue;
            }

            $listEntity = $em->getRepository('OlegUserdirectoryBundle:PlatformListManagerRootList')->findOneByListName($listName);
            if( $listEntity ) {
                //exit('exists listName='.$listName);
                continue;
            }

            echo "nameClean=$nameClean || listName=$listName, listRootName=$listRootName <br>";

            //construct the "Name" from the entity name:
            // "SourceSystemList" => "Source System", "BoardCertifiedSpecialties" => "Board Certified Specialty"
            if( !$nameClean ) {
                //1) split with upper case
                $nameArr = $this->splitAtUpperCase($listName);

                //2) remove the last element if == "List"
                $lastIndex = count($nameArr) - 1;
                if ($nameArr[$lastIndex] == "List") {
                    unset($nameArr[$lastIndex]);
                }

                //3) singularize
                $nameArrClean = array();
                foreach ($nameArr as $thisName) {
                    //Countries => Country
                    if (substr($thisName, -3) == "ies") {
                        //$len = strlen($thisName);
                        //$thisName = substr_replace("ies", "y", $len-3, $len);
                        $thisName = $this->str_lreplace("ies", "y", $thisName);
                    }

                    //Roles => Role
                    if (substr($thisName, -2) == "es") {
                        //$len = strlen($thisName);
                        //$thisName = substr_replace("es", "e", $len-2, $len);
                        $thisName = $this->str_lreplace("es", "e", $thisName);
                    }

                    $nameArrClean[] = $thisName;
                }

                $nameClean = implode(" ", $nameArrClean);
            }

            //echo "nameClean=$nameClean || listName=$listName, listRootName=$listRootName <br>";
            //echo "nameClean=$nameClean <br>";

            $listEntity = new PlatformListManagerRootList();
            $this->setDefaultList($listEntity,null,$username,$nameClean);

            //$listEntity->setLinkToListId($listId);
            $listEntity->setListName($listName);
            $listEntity->setListRootName($listRootName);

            $em->persist($listEntity);
            $em->flush($listEntity);

            //set linkToListId the same as ID
            if( $listEntity->getId() ) {
                $listEntity->setLinkToListId($listEntity->getId());
                $em->persist($listEntity);
                $em->flush($listEntity);
            }

            $count = $count + 10;
        }

        $res = 'Inserted PlatformListManagerRootList objects count='.round($count/10);

        $this->get('session')->getFlashBag()->add(
            'notice',
            $res
        );

        //exit($res);

        //if( $request->get('_route') == "user_populate_platform_list_manager" ) {
        if( $request ) {
            return $this->redirect($this->generateUrl('user_admin_index'));
        }

        return round($count/10);
    }
    function splitAtUpperCase($s) {
        return preg_split('/(?=[A-Z])/', $s, -1, PREG_SPLIT_NO_EMPTY);
    }
    function str_lreplace($search, $replace, $subject)
    {
        $pos = strrpos($subject, $search);

        if($pos !== false)
        {
            $subject = substr_replace($subject, $replace, $pos, strlen($search));
        }

        return $subject;
    }


    /**
     * @Route("/set-institution-employment-period/", name="user_institution_employment_period")
     * @Method("GET")
     */
    public function setInstitutionEmploymentPeriodAction()
    {

        if (false === $this->get('security.context')->isGranted('ROLE_PLATFORM_DEPUTY_ADMIN')) {
            return $this->redirect($this->generateUrl($this->container->getParameter('employees.sitename') . '-order-nopermission'));
        }

        $em = $this->getDoctrine()->getManager();

        $wcmc = $em->getRepository('OlegUserdirectoryBundle:Institution')->findOneByAbbreviation("WCMC");
        if( !$wcmc ) {
            exit('No Institution: "WCMC"');
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
            exit('No Institution: "Pathology and Laboratory Medicine"');
        }

        $query = $em->createQuery('UPDATE OlegUserdirectoryBundle:EmploymentStatus p SET p.institution = '.$pathology->getId().' WHERE p.institution IS NULL');
        $numUpdated = $query->execute();

        exit("set-institution-employment-period; numUpdated=".$numUpdated);
    }

    /**
     * For all users in the live C.MED system EXCEPT FELLOWSHIP APPLICANTS, set "Pathology and Laboratory Medicine"
     * @Route("/set-default-org-group/", name="user_set-default-org-group")
     * @Method("GET")
     */
    public function setDefaultOrgGroupAction()
    {

        if (false === $this->get('security.context')->isGranted('ROLE_PLATFORM_DEPUTY_ADMIN')) {
            return $this->redirect($this->generateUrl($this->container->getParameter('employees.sitename') . '-order-nopermission'));
        }

        $em = $this->getDoctrine()->getManager();

        $wcmc = $em->getRepository('OlegUserdirectoryBundle:Institution')->findOneByAbbreviation("WCMC");
        if( !$wcmc ) {
            exit('No Institution: "WCMC"');
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
            exit('No Institution: "Pathology and Laboratory Medicine"');
        }


//        $repository = $em->getRepository('OlegUserdirectoryBundle:User');
//        $dql =  $repository->createQueryBuilder("user");
//        $dql->select('user');
//        $dql->leftJoin("user.perSiteSettings", "perSiteSettings");
//        $dql->leftJoin("user.employmentStatus", "employmentStatus");
//        $dql->leftJoin("employmentStatus.employmentType", "employmentType");
//        $dql->where("perSiteSettings.organizationalGroupDefault IS NULL OR perSiteSettings IS NULL");
//        $dql->andWhere("employmentType.name != :employmentType OR employmentStatus IS NULL");
//
//        $query = $em->createQuery($dql);
//        $query->setParameter('employmentType', "Pathology Fellowship Applicant");
//
//        $users = $query->getResult();

        $users = $em->getRepository('OlegUserdirectoryBundle:User')->findAll();
        echo "user count=".count($users)."<br>";

        $totalCount = 0;
        $count = 0;
        foreach( $users as $user ) {
            echo "<br>".$totalCount.": user=".$user."<br>";

            $employmentStatuses = $user->getEmploymentStatus();
            if( count($employmentStatuses) > 0 ) {
                $employmentStatus = $employmentStatuses->first();
                if( $employmentStatus->getEmploymentType()."" == "Pathology Fellowship Applicant" ) {
                    echo "skip fellowship applicant <br>";
                    continue;
                }
            }

            $userSettings = $user->getPerSiteSettings();
            if( $userSettings ) {
                echo "userSetting=".$userSettings->getId()."; orgGroupDefault=".$userSettings->getOrganizationalGroupDefault()."<br>";
                if( !$userSettings->getOrganizationalGroupDefault() ) {
                    $userSettings->setOrganizationalGroupDefault($pathology);
                    $em->persist($userSettings);
                    $em->flush($userSettings);
                    $count++;
                } else {
                    //exit('OrganizationalGroupDefault='.$userSettings->getOrganizationalGroupDefault());
                }
            } else {
                $userSettings = new PerSiteSettings();
                $userSettings->setOrganizationalGroupDefault($pathology);
                $user->setPerSiteSettings($userSettings);
                $em->persist($userSettings);
                $em->flush($userSettings);
                $count++;
            }
            $totalCount++;
        }

        exit("<br><br>set-default-org-group; count=".$count);
    }

    /**
     * @Route("/convert-logger-site/", name="user_convert-logger-site")
     * @Method("GET")
     */
    public function convertLoggerSitenameToSiteObectAction() {

        if( false === $this->get('security.context')->isGranted('ROLE_PLATFORM_DEPUTY_ADMIN') ) {
            return $this->redirect( $this->generateUrl($this->container->getParameter('employees.sitename').'-order-nopermission') );
        }

        $em = $this->getDoctrine()->getManager();

        //$logger = $em->getRepository('OlegUserdirectoryBundle:Logger')->find(7789);
        //$loggers = array($logger);
        $loggers = $em->getRepository('OlegUserdirectoryBundle:Logger')->findAll();

        //map sitename to object
        $siteMap = array(
            'employees' => $this->getDoctrine()->getRepository('OlegUserdirectoryBundle:SiteList')->findOneByAbbreviation('employees'),
            'scan' => $this->getDoctrine()->getRepository('OlegUserdirectoryBundle:SiteList')->findOneByAbbreviation('scan'),
            'fellapp' => $this->getDoctrine()->getRepository('OlegUserdirectoryBundle:SiteList')->findOneByAbbreviation('fellapp'),
            'deidentifier' => $this->getDoctrine()->getRepository('OlegUserdirectoryBundle:SiteList')->findOneByAbbreviation('deidentifier'),
            'vacreq' => $this->getDoctrine()->getRepository('OlegUserdirectoryBundle:SiteList')->findOneByAbbreviation('vacreq'),
            'calllog' => $this->getDoctrine()->getRepository('OlegUserdirectoryBundle:SiteList')->findOneByAbbreviation('calllog'),
        );

        $count = 1;
        foreach( $loggers as $logger ) {

            $site = $logger->getSite();
            if( $site ) {
                continue;
            }

            $site = $siteMap[$logger->getSiteName()];
            if( $site ) {
                $logger->setSite($site);
                $em->flush($logger);

                $count++;
            }
        }

        exit('Inserted site objects to loggers count='.$count);

        $this->get('session')->getFlashBag()->add(
            'notice',
            'Inserted site objects to loggers count='.$count
        );

        return $this->redirect($this->generateUrl('user_admin_index'));
    }



    /**
     * @Route("/sync-db/", name="user_sync_db")
     * @Method("GET")
     */
    public function syncEventTypeListDbAction()
    {
        $count = $this->syncEventTypeListDb();
        $this->get('session')->getFlashBag()->add(
            'notice',
            'syncEventTypeListDb count='.$count
        );

        $count = $this->syncRolesDb();
        $this->get('session')->getFlashBag()->add(
            'notice',
            'syncRolesDb count='.$count
        );

        //List of Research Labs clean
        $count = $this->syncResearchLabsDb();
        $this->get('session')->getFlashBag()->add(
            'notice',
            'Research Labs clean count='.$count
        );

        return $this->redirect($this->generateUrl('user_admin_index'));
    }
    public function syncEventTypeListDb() {

        $count = 0;

        //User Created -> New user record added
        $count = $count + $this->singleSyncDb('OlegUserdirectoryBundle:EventTypeList',"User Created","New user record added");

        //User Updated -> User record updated
        $count = $count + $this->singleSyncDb('OlegUserdirectoryBundle:EventTypeList',"User Updated","User record updated");

        //Populate of Fellowship Applications -> Import of Fellowship Application data to DB
        $count = $count + $this->singleSyncDb('OlegUserdirectoryBundle:EventTypeList',"Populate of Fellowship Applications","Import of Fellowship Application data to DB");

        //Import of Fellowship Applications -> Import of Fellowship Applications Spreadsheet
        $count = $count + $this->singleSyncDb('OlegUserdirectoryBundle:EventTypeList',"Import of Fellowship Applications","Import of Fellowship Applications Spreadsheet");

        //Fellowship Application Resend Emails -> Fellowship Application Rating Invitation Emails Resent
        $count = $count + $this->singleSyncDb('OlegUserdirectoryBundle:EventTypeList',"Fellowship Application Resend Emails","Fellowship Application Rating Invitation Emails Resent");

        //Fellowship Applicant Page Viewed -> Fellowship Application Page Viewed
        $count = $count + $this->singleSyncDb('OlegUserdirectoryBundle:EventTypeList',"Fellowship Applicant Page Viewed","Fellowship Application Page Viewed");

        return $count;
    }
    public function singleSyncDb($repStr,$oldName,$newName) {
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository($repStr)->findOneByName($oldName);
        //echo $repStr . " oldName=" . $oldName . "<br>";
        if( $entity ) {
            $entity->setName($newName);
            //echo $repStr . " name=" . $newName . "<br>";
            $em->persist($entity);
            $em->flush();
            return 1;
        }
        return 0;
    }

    //add sitename to the existing roles using role name
    public function syncRolesDb() {

        $em = $this->getDoctrine()->getManager();
        $roles = $em->getRepository('OlegUserdirectoryBundle:Roles')->findAll();

        $count = 0;

        foreach( $roles as $role ) {

//            if( strpos($role, '_DEIDENTIFICATOR_') !== false ) {
//                $site = $em->getRepository('OlegUserdirectoryBundle:SiteList')->findOneByName('deidentifier');
//                if( $role->getSites() && !$role->getSites()->contains($site) ) {
//                    $role->addSite($site);
//                    $count++;
//                }
//            }
            $resCount = 0;

            $resCount = $resCount + $this->addSites( $role, '_DEIDENTIFICATOR_', 'deidentifier' );

            $resCount = $resCount + $this->addSites( $role, '_VACREQ_', 'vacation-request' );

            $resCount = $resCount + $this->addSites( $role, '_FELLAPP_', 'fellowship-applications' );

            $resCount = $resCount + $this->addSites( $role, '_SCANORDER_', 'scan' );

            $resCount = $resCount + $this->addSites( $role, '_USERDIRECTORY_', 'directory' );


            $resCount = $resCount + $this->addFellAppPermission( $role );

            $resCount = $resCount + $this->addVacReqPermission( $role );
            $resCount = $resCount + $this->setInstitutionVacReqRole($role); //set institution and Fellowship Subspecialty types to role


            //disable/remove already existing general roles
            if(
                $role == "ROLE_FELLAPP_USER"        ||
                $role == "ROLE_FELLAPP_INTERVIEWER" ||
                $role == "ROLE_FELLAPP_COORDINATOR" ||
                $role == "ROLE_FELLAPP_DIRECTOR"
            ) {
                //$role->setType('disabled');
                //remove role
                foreach( $role->getSites() as $site ) {
                    $role->removeSite($site);
                }
                $em->remove($role);
                $em->flush();
                $count++;
                //$resCount++;
                continue;
            }


            if( $resCount > 0 ) {
                $count++;
                $em->persist($role);
                $em->flush();
            }
        }

        //exit('resCount='.$resCount);
        return $count;
    }
    public function addSites( $role, $roleStr, $sitename ) {
        $count = 0;
        if( strpos($role, $roleStr) !== false ) {
            $em = $this->getDoctrine()->getManager();
            $site = $em->getRepository('OlegUserdirectoryBundle:SiteList')->findOneByName($sitename);
            if( $role->getSites() && !$role->getSites()->contains($site) ) {
                $role->addSite($site);
                $count++;
            }
        }
        return $count;
    }

    public function addFellAppPermission( $role ) {
        $count = 0;

        $userSecUtil = $this->container->get('user_security_utility');

        //ROLE_FELLAPP_INTERVIEWER: permission="Submit an interview evaluation", object="Interview", action="create"
        if( strpos($role, "ROLE_FELLAPP_INTERVIEWER") !== false ) {
            $count = $count + $userSecUtil->checkAndAddPermissionToRole($role,"Submit an interview evaluation","Interview","create");
        }

        //ROLE_FELLAPP_DIRECTOR:
        //ROLE_FELLAPP_COORDINATOR:
        // permission="Create a New Fellowship Application", object="FellowshipApplication", action="create"
        // permission="Modify a Fellowship Application", object="FellowshipApplication", action="update"
        if( strpos($role, "ROLE_FELLAPP_COORDINATOR") !== false || strpos($role, "ROLE_FELLAPP_DIRECTOR") !== false ) {
            $count = $count + $userSecUtil->checkAndAddPermissionToRole($role,"Create a New Fellowship Application","FellowshipApplication","create");
            $count = $count + $userSecUtil->checkAndAddPermissionToRole($role,"Modify a Fellowship Application","FellowshipApplication","update");
        }

        //ROLE_FELLAPP_OBSERVER: permission="View a Fellowship Application", object="FellowshipApplication", action="read"
        if( strpos($role, "ROLE_FELLAPP_OBSERVER") !== false ) {
            $count = $count + $userSecUtil->checkAndAddPermissionToRole($role,"View a Fellowship Application","FellowshipApplication","read");
        }

        return $count;
    }

    public function addVacReqPermission( $role ) {
        $count = 0;

        $userSecUtil = $this->container->get('user_security_utility');

        //ROLE_VACREQ_APPROVER: permission="Approve a Vacation Request", object="VacReqRequest", action="changestatus"
        if( strpos($role, "ROLE_VACREQ_APPROVER") !== false ) {
            //$count = $count + $userSecUtil->checkAndAddPermissionToRole($role,"Submit a Vacation Request","VacReqRequest","create");
            $count = $count + $userSecUtil->checkAndAddPermissionToRole($role,"Approve a Vacation Request","VacReqRequest","changestatus");
        }

        //ROLE_VACREQ_APPROVER: permission="Approve a Vacation Request", object="VacReqRequest", action="create"
        if( strpos($role, "ROLE_VACREQ_SUBMITTER") !== false ) {
            $count = $count + $userSecUtil->checkAndAddPermissionToRole($role,"Submit a Vacation Request","VacReqRequest","create");
        }

        //ROLE_VACREQ_SUPERVISOR: permission="Approve a Carry Over Request", object="VacReqRequest", action="changestatus-carryover"
        if( strpos($role, "ROLE_VACREQ_SUPERVISOR") !== false ) {
            $count = $count + $userSecUtil->checkAndAddPermissionToRole($role,"Submit a Vacation Request","VacReqRequest","create");
            $count = $count + $userSecUtil->checkAndAddPermissionToRole($role,"Approve a Vacation Request","VacReqRequest","changestatus");
            $count = $count + $userSecUtil->checkAndAddPermissionToRole($role,"Approve a Carry Over Request","VacReqRequest","changestatus-carryover");
        }

        return $count;
    }


    public function syncResearchLabsDb() {

        //check
        $em = $this->getDoctrine()->getManager();
        $researchLabs = $em->getRepository('OlegUserdirectoryBundle:ResearchLab')->findBy(array(),array('name'=>'asc','id'=>'asc'));
        //echo "researchLab count=".count($researchLabs)."<br>";

        $count = 0;
        $currentResLab = null;

        foreach( $researchLabs as $researchLab ) {

            //echo "researchLab:".$researchLab->getId()." => ".$researchLab->getName()."<br>";
            if( $currentResLab == null || $currentResLab->getName() != $researchLab->getName() ) {
                //if( $researchLab->getType() != "default" ) {
                //    exit("researchLab type is not default: type=".$researchLab->getType());
                //}
                $currentResLab = $researchLab;
                continue;
            }

            //1) re-assign all users from $rgiesearchLab to $currentResLab
            foreach( $researchLab->getUser() as $user ) {
                //remove
                $researchLab->removeUser($user);
                $user->removeResearchLab($researchLab);
                //add
                $user->addResearchLab($currentResLab);
            }

            //2) remove $researchLab if no user attached to it
            if( count($researchLab->getUser()) == 0 ) {
                $em->remove($researchLab);
                $em->flush();
                //echo $researchLab->getId().": researchLab removed <br>";
                $count++;
            } else {
                exit("There are still users attached to researchLab: user count=".count($researchLab->getUser()));
            }

        }//foreach

        //echo "removed count=".$count."<br>";
        //exit('1');
        return $count;
    }


    ////////////////// Employee Tree Util //////////////////////
    /**
     * @Route("/list/institutional-tree/", name="employees_tree_institutiontree_list")
     * @Route("/list/comment-tree/", name="employees_tree_commenttree_list")
     * @Route("/list/form-tree/", name="employees_tree_formnode_list")
     *
     * @Method("GET")
     */
    public function institutionTreeAction(Request $request)
    {
        if( false === $this->get('security.context')->isGranted('ROLE_USERDIRECTORY_OBSERVER') ) {
            return $this->redirect( $this->generateUrl($this->container->getParameter('employees.sitename').'-order-nopermission') );
        }

        return $this->compositeTree($request,$this->container->getParameter('employees.sitename'));
    }

    public function compositeTree(Request $request, $sitename)
    {

        $mapper = $this->getMapper($request->get('_route'));

        //show html tree
        if( 0 ) {
            $em = $this->getDoctrine()->getManager();
            $repo = $em->getRepository($mapper['bundlePreffix'].$mapper['bundleName'].':'.$mapper['className']);
            $htmlTree = $repo->childrenHierarchy(
                null, /* starting from root nodes */
                false, /* true: load all children, false: only direct */
                array(
                    'decorate' => true,
                    'representationField' => 'slug',
                    'html' => true
                )
            );
            echo $htmlTree;
        }

        //$filterList = array('default','user-added','disabled','draft');
        //$filters = trim( $request->get('filters') );
//        $filter = null;
//        if( $filters ) {
//            $filter = implode(",", $filters);
//        }
        //get filter types from request
        $filterform = $this->createForm(new HierarchyFilterType(), null);
        $formname = $filterform->getName();
        $formData = $request->query->get($formname);
        $types = $formData['types'];
        //print_r($types);
//        if( !$types ) {
//            $types = array(
//                "default"=>"default",
//                "user-added"=>"user-added",
//            );
//        }
        $params = array('types'=>$types);
        //create final filter form with data params
        $filterform = $this->createForm(new HierarchyFilterType($params), null);
        //$filterform->bind($request);
        //$filterform->handleRequest($request);
        //$types = $filterform['types']->getData();
        //$types = $filterform->get('types')->getData();
        //$data = $filterform->getData();
        //$types = $data['types'];
        //$types = $filterform->get('types');
        //var_dump($filterform->getData());die;
        //echo "types=".$types."<br>";
        //print_r($types);
        //die;
//        if( $types ) {
//            $dql->andWhere("ent.id LIKE :search OR ent.name LIKE :search OR ent.abbreviation LIKE :search OR ent.shortname LIKE :search OR ent.description LIKE :search");
//            $dqlParameters['search'] = '%'.$search.'%';
//        }

        return $this->render('OlegUserdirectoryBundle:Tree:composition-tree.html.twig',
            array(
                'title' => $mapper['title'],
                'bundleName' => $mapper['bundleName'],
                'entityName' => $mapper['className'],
                'nodeshowpath' => $mapper['nodeshowpath'],
                'sitename' => $sitename,
                'filterform' => $filterform->createView(),
                //'filters' => $filters,
                //'filterList' => $filterList,
                'routename' => $request->get('_route')
            )
        );
    }


    public function getMapper($routeName) {

        $bundlePreffix = "Oleg";
        $bundleName = "UserdirectoryBundle";
        $className = null;
        $title = null;
        $nodeshowpath = null;

        if( $routeName == "employees_tree_institutiontree_list" ) {
            $bundleName = "UserdirectoryBundle";
            $className = "Institution";
            $title = "Institutional Tree Management";
            $nodeshowpath = "institutions_show";
        }

        if( $routeName == "employees_tree_commenttree_list" ) {
            $bundleName = "UserdirectoryBundle";
            $className = "CommentTypeList";
            $title = "Comment Type Tree Management";
            $nodeshowpath = "commenttypes_show";
        }

        if( $routeName == "employees_tree_formnode_list" ) {
            $bundleName = "UserdirectoryBundle";
            $className = "FormNode";
            $title = "Form Tree Management";
            $nodeshowpath = "formnodes_show";
        }

        $mapper = array(
            'bundlePreffix' => $bundlePreffix,
            'bundleName' => $bundleName,
            'className' => $className,
            'title' => $title,
            'nodeshowpath' => $nodeshowpath
        );

        return $mapper;
    }

    public function generateAdministratorAction() {

        if( false === $this->get('security.context')->isGranted('ROLE_PLATFORM_DEPUTY_ADMIN') ) {
            return $this->redirect( $this->generateUrl('employees-nopermission') );
        }

        $em = $this->getDoctrine()->getManager();

        //$user = $this->get('security.context')->getToken()->getUser();
        $primaryPublicUserId = 'Administrator';
        //$primaryPublicUserId = 'Administrator1';

        $localUserType = $em->getRepository('OlegUserdirectoryBundle:UsernameType')->findOneByAbbreviation('local-user');

        $administrators = $em->getRepository('OlegUserdirectoryBundle:User')->findBy(
            array(
                'primaryPublicUserId' => $primaryPublicUserId,
                'keytype' => $localUserType->getId()
            )
        );

        if( count($administrators) > 1 ) {
            throw new \Exception( "Found multiple $primaryPublicUserId . Found ".count($primaryPublicUserId)."users" );
        }

        if( count($administrators) == 1 ) {
            $administrator = $administrators[0];
        }


        $encoder = $this->container->get('security.password_encoder');

        if( $administrator ) {

            $flush = false;
            $res = "$primaryPublicUserId user already exists.";

            $encodedPassword = $encoder->encodePassword($administrator, "1234567890");

            $bool = hash_equals($administrator->getPassword(), $encodedPassword);

            if( !$bool ) {
                $administrator->setPassword($encodedPassword);
                $flush = true;
                $res .= " Password updated.";
            }

            if( !$administrator->hasRole('ROLE_PLATFORM_ADMIN') ) {
                $administrator->addRole('ROLE_PLATFORM_ADMIN');
                $flush = true;
                $res .= " Role ROLE_PLATFORM_ADMIN added.";
            }

            if( $flush ) {
                $em->persist($administrator);
                $em->flush($administrator);
                //echo "flash ";
            } else {
                //echo "no flash ";
            }

        } else {

            $userSecUtil = $this->container->get('user_security_utility');
            $administrator = $userSecUtil->constractNewUser($primaryPublicUserId.'_@_local-user');

            $systemEmail = $userSecUtil->getSiteSettingParameter('siteEmail');
            $administrator->setEmail($systemEmail);
            $administrator->setEmailCanonical($systemEmail);
            $administrator->setCreatedby('system');
            $administrator->addRole('ROLE_PLATFORM_ADMIN');
            $administrator->setEnabled(true);
            $administrator->setLocked(true);
            $administrator->setExpired(false);

            $encodedPassword = $encoder->encodePassword($administrator, "1234567890");
            $administrator->setPassword($encodedPassword);

            $default_time_zone = $this->container->getParameter('default_time_zone');
            $administrator->getPreferences()->setTimezone($default_time_zone);

            $res = "New $primaryPublicUserId account has been created";

            $em->persist($administrator);
            $em->flush($administrator);
        }

        return $res;
    }

    /**
     * @Route("/list/generate-form-node-tree/", name="employees_generate_form_node_tree")
     * @Method("GET")
     */
    public function generateFormNodeAction(Request $request)
    {
        if( false === $this->get('security.context')->isGranted('ROLE_PLATFORM_DEPUTY_ADMIN') ) {
            return $this->redirect( $this->generateUrl($this->container->getParameter('employees.sitename').'-order-nopermission') );
        }

        $formNodeUtil = $this->get('user_formnode_utility');
        $count = $formNodeUtil->generateFormNode();

        exit("Form Node Tree generated: ".$count);
    }

    /**
     * @Route("/list/generate-test-form-node-tree/", name="employees_generate_test_form_node_tree")
     * @Method("GET")
     */
    public function generateTestFormNodeAction(Request $request)
    {
        if( false === $this->get('security.context')->isGranted('ROLE_PLATFORM_DEPUTY_ADMIN') ) {
            return $this->redirect( $this->generateUrl($this->container->getParameter('employees.sitename').'-order-nopermission') );
        }

        $formNodeUtil = $this->get('user_formnode_utility');
        $formNodeUtil->createTestFormNodes();

        exit("Test Form Node Tree generated");
    }

    //Blood Product Transfused
    public function generateBloodProductTransfused() {

        $username = $this->get('security.context')->getToken()->getUser();
        $em = $this->getDoctrine()->getManager();

        $types = array(
            "Red blood cells",
            "Platelets",
            "Plasma",
            "Cryoprecipitate",
            "Stem cells",
            "Other"
        );

        $count = 10;
        foreach( $types as $name ) {

            $listEntity = $em->getRepository('OlegUserdirectoryBundle:BloodProductTransfusedList')->findOneByName($name);
            if( $listEntity ) {
                continue;
            }

            $listEntity = new BloodProductTransfusedList();
            $this->setDefaultList($listEntity,$count,$username,$name);

            //exit('exit generateObjectTypeActions');
            $em->persist($listEntity);
            $em->flush();

            $count = $count + 10;
        }

        return round($count/10);
    }

    //ClericalErrorList
    public function generateClericalErrorList() {

        $username = $this->get('security.context')->getToken()->getUser();
        $em = $this->getDoctrine()->getManager();

        $types = array(
            "Yes",
            "None",
        );

        $count = 10;
        foreach( $types as $name ) {

            $listEntity = $em->getRepository('OlegUserdirectoryBundle:ClericalErrorList')->findOneByName($name);
            if( $listEntity ) {
                continue;
            }

            $listEntity = new ClericalErrorList();
            $this->setDefaultList($listEntity,$count,$username,$name);

            //exit('exit generateObjectTypeActions');
            $em->persist($listEntity);
            $em->flush();

            $count = $count + 10;
        }

        return round($count/10);
    }

    //LabResultNames
    public function generateLabResultNames() {
        $username = $this->get('security.context')->getToken()->getUser();
        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('OlegUserdirectoryBundle:LabResultNameList')->findAll();
        if( count($entities) > 3 ) {
            //return -1;
        }

        ini_set('max_execution_time', 3600);

        //$inputFileName = __DIR__ . '/../Util/Lab Result Names For Import.xlsx';
        //Lab Result Names For Import 2.xlsx
        $inputFileName = __DIR__ . '/../Util/Lab Result Names For Import.xlsx';

        try {
            $inputFileType = \PHPExcel_IOFactory::identify($inputFileName);
            $objReader = \PHPExcel_IOFactory::createReader($inputFileType);
            $objPHPExcel = $objReader->load($inputFileName);
        } catch( Exception $e ) {
            die('Error loading file "'.pathinfo($inputFileName,PATHINFO_BASENAME).'": '.$e->getMessage());
        }

        $sheet = $objPHPExcel->getSheet(0);
        $highestRow = $sheet->getHighestRow();
        $highestColumn = $sheet->getHighestColumn();

        $batchSize = 20;
        $loopCount = 0;
        $count = 10;
        for( $row = 2; $row <= $highestRow; $row++ ) {

            //Read a row of data into an array
            $rowData = $sheet->rangeToArray('A' . $row . ':' . $highestColumn . $row,
                NULL,
                TRUE,
                FALSE);

            $name = $rowData[0][0];
            $shortname = $rowData[0][1];
            $abbreviation = $rowData[0][2];

//            print "<pre>";
//            print_r($rowData);
//            print "</pre>";
//            print "</pre>";
            //echo "name=$name, shortname=$shortname, abbreviation=$abbreviation <br>";
            //exit('1');

            $listEntity = $em->getRepository('OlegUserdirectoryBundle:LabResultNameList')->findOneByName($name);
            if( $listEntity ) {
                continue;
            }

            $listEntity = new LabResultNameList();
            $this->setDefaultList($listEntity,null,$username,$name);

            if( $shortname ) {
                $listEntity->setShortname($shortname);
            }

            if( $abbreviation ) {
                $listEntity->setAbbreviation($abbreviation);
            }

            //exit('exit generateObjectTypeActions');
            $em->persist($listEntity);
            if( ($loopCount % $batchSize) === 0) {
                //$em->persist($listEntity);
                $em->flush();
                //$em->clear(); // Detaches all objects from Doctrine!
            }

            $count = $count + 10;
            $loopCount++;
        }

        $em->flush(); //Persist objects that did not make up an entire batch
        $em->clear();

        //exit('1');

        return round($count/10);
    }


    //LabResultUnitsMeasureList
    public function generateLabResultUnitsMeasureList() {

        $username = $this->get('security.context')->getToken()->getUser();
        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('OlegUserdirectoryBundle:LabResultUnitsMeasureList')->findAll();
        if( count($entities) > 3 ) {
            return -1;
        }

        $inputFileName = __DIR__ . '/../Util/Laboratory Units of Measure Compilation-1.xlsx';

        try {
            $inputFileType = \PHPExcel_IOFactory::identify($inputFileName);
            $objReader = \PHPExcel_IOFactory::createReader($inputFileType);
            $objPHPExcel = $objReader->load($inputFileName);
        } catch( Exception $e ) {
            die('Error loading file "'.pathinfo($inputFileName,PATHINFO_BASENAME).'": '.$e->getMessage());
        }

        $sheet = $objPHPExcel->getSheet(0);
        $highestRow = $sheet->getHighestRow();
        $highestColumn = $sheet->getHighestColumn();

        $types = array(
            //"Sample Lab Result Unit of Measure 01",
            //"Sample Lab Result Unit of Measure 02",
        );

        $count = 10;
        for( $row = 2; $row <= $highestRow; $row++ ) {

            //Read a row of data into an array
            $rowData = $sheet->rangeToArray('A' . $row . ':' . $highestColumn . $row,
                NULL,
                TRUE,
                FALSE);

            $name = $rowData[0][0];
            $abbreviation = $rowData[0][1];

//            print "<pre>";
//            print_r($rowData);
//            print "</pre>";
//            print "</pre>";
//            echo "name=$name, abbreviation=$abbreviation <br>";

            $listEntity = $em->getRepository('OlegUserdirectoryBundle:LabResultUnitsMeasureList')->findOneByName($name);
            if( $listEntity ) {
                continue;
            }

            $listEntity = new LabResultUnitsMeasureList();
            $this->setDefaultList($listEntity,null,$username,$name);

            $listEntity->setAbbreviation($abbreviation);

            //exit('exit generateObjectTypeActions');
            $em->persist($listEntity);
            $em->flush();

            $count = $count + 10;
            //exit('1');
        }

        $em->clear();

        return round($count/10);
    }

    //LabResultFlagList
    public function generateLabResultFlagList() {

        $username = $this->get('security.context')->getToken()->getUser();
        $em = $this->getDoctrine()->getManager();

        $types = array(
            "Abnormal (applies to non-numeric results)"=>"A",
            "Above absolute high-off instrument scale"=>">",
            "Above high normal"=>"H",
            "Above upper panic limits"=>"HH",
            "Below absolute low-off instrument scale"=>"<",
            "Below low normal"=>"L",
            "Below lower panic limits"=>"LL",
            "Better--use when direction not relevant"=>"B",
            "Intermediate. Indicates for microbiology susceptibilities only"=>"I",
            "Moderately susceptible. Indicates for microbiology susceptibilities only"=>"MS",
            "No range defined, or normal ranges don't apply"=>"null",
            "Normal (applies to non-numeric results)"=>"N",
            "Resistant. Indicates for microbiology susceptibilities only"=>"R",
            "Significant change down"=>"D",
            "Significant change up"=>"U"
        );

        $count = 10;
        foreach( $types as $name=>$abbreviation ) {

            $listEntity = $em->getRepository('OlegUserdirectoryBundle:LabResultFlagList')->findOneByName($name);
            if( $listEntity ) {
                continue;
            }

            $listEntity = new LabResultFlagList();
            $this->setDefaultList($listEntity,$count,$username,$name);

            $listEntity->setAbbreviation($abbreviation);

            //exit('exit generateObjectTypeActions');
            $em->persist($listEntity);
            $em->flush();

            $count = $count + 10;
        }

        return round($count/10);
    }

    //PathologyResultSignatoriesList
    public function generatePathologyResultSignatoriesList() {

        $username = $this->get('security.context')->getToken()->getUser();
        $em = $this->getDoctrine()->getManager();

        $types = array(

        );

        $count = 10;
        foreach( $types as $name ) {

            $listEntity = $em->getRepository('OlegUserdirectoryBundle:PathologyResultSignatoriesList')->findOneByName($name);
            if( $listEntity ) {
                continue;
            }

            $listEntity = new PathologyResultSignatoriesList();
            $this->setDefaultList($listEntity,$count,$username,$name);

            $em->persist($listEntity);
            $em->flush();

            $count = $count + 10;
        }

        return round($count/10);
    }

    //Transfusion Reaction Type
    public function generateTransfusionReactionType() {

        $username = $this->get('security.context')->getToken()->getUser();
        $em = $this->getDoctrine()->getManager();

        $types = array(
            "FNHTR",
            "Allergic",
            "Underlying condition",
            "No transfusion reaction",
            "Anaphylactic",
            "TACO",
            "TRALI",
            "Hemolytic",
            "Delayed serologic",
            "Hypotensive",
            "Bacterial",
            "Other",
        );

        $count = 10;
        foreach( $types as $name ) {

            $listEntity = $em->getRepository('OlegUserdirectoryBundle:TransfusionReactionTypeList')->findOneByName($name);
            if( $listEntity ) {
                continue;
            }

            $listEntity = new TransfusionReactionTypeList();
            $this->setDefaultList($listEntity,$count,$username,$name);

            //exit('exit generateObjectTypeActions');
            $em->persist($listEntity);
            $em->flush();

            $count = $count + 10;
        }

        return round($count/10);
    }

    //BloodTypeList
    public function generateBloodTypeList() {

        $username = $this->get('security.context')->getToken()->getUser();
        $em = $this->getDoctrine()->getManager();

        $types = array(
            "A+",
            "A-",
            "B+",
            "B-",
            "O+",
            "O-",
        );

        $count = 10;
        foreach( $types as $name ) {

            $listEntity = $em->getRepository('OlegUserdirectoryBundle:BloodTypeList')->findOneByName($name);
            if( $listEntity ) {
                continue;
            }

            $listEntity = new BloodTypeList();
            $this->setDefaultList($listEntity,$count,$username,$name);

            //exit('exit generateObjectTypeActions');
            $em->persist($listEntity);
            $em->flush();

            $count = $count + 10;
        }

        return round($count/10);
    }

    public function generateTransfusionAntibodyScreenResultsList() {

        $username = $this->get('security.context')->getToken()->getUser();
        $em = $this->getDoctrine()->getManager();

        $types = array(
            "Positive",
            "Negative",
        );

        $count = 10;
        foreach( $types as $name ) {

            $listEntity = $em->getRepository('OlegUserdirectoryBundle:TransfusionAntibodyScreenResultsList')->findOneByName($name);
            if( $listEntity ) {
                continue;
            }

            $listEntity = new TransfusionAntibodyScreenResultsList();
            $this->setDefaultList($listEntity,$count,$username,$name);

            //exit('exit generateObjectTypeActions');
            $em->persist($listEntity);
            $em->flush();

            $count = $count + 10;
        }

        return round($count/10);
    }

    public function generateTransfusionDATResultsList() {

        $username = $this->get('security.context')->getToken()->getUser();
        $em = $this->getDoctrine()->getManager();

        $types = array(
            "Positive",
            "Negative",
        );

        $count = 10;
        foreach( $types as $name ) {

            $listEntity = $em->getRepository('OlegUserdirectoryBundle:TransfusionDATResultsList')->findOneByName($name);
            if( $listEntity ) {
                continue;
            }

            $listEntity = new TransfusionDATResultsList();
            $this->setDefaultList($listEntity,$count,$username,$name);

            //exit('exit generateObjectTypeActions');
            $em->persist($listEntity);
            $em->flush();

            $count = $count + 10;
        }

        return round($count/10);
    }

    public function generateTransfusionCrossmatchResultsList() {

        $username = $this->get('security.context')->getToken()->getUser();
        $em = $this->getDoctrine()->getManager();

        $types = array(
            "Compatible",
            "Incompatible",
        );

        $count = 10;
        foreach( $types as $name ) {

            $listEntity = $em->getRepository('OlegUserdirectoryBundle:TransfusionCrossmatchResultsList')->findOneByName($name);
            if( $listEntity ) {
                continue;
            }

            $listEntity = new TransfusionCrossmatchResultsList();
            $this->setDefaultList($listEntity,$count,$username,$name);

            //exit('exit generateObjectTypeActions');
            $em->persist($listEntity);
            $em->flush();

            $count = $count + 10;
        }

        return round($count/10);
    }

    public function generateTransfusionHemolysisCheckResultsList() {

        $username = $this->get('security.context')->getToken()->getUser();
        $em = $this->getDoctrine()->getManager();

        $types = array(
            "Hemolysis",
            "No hemolysis",
        );

        $count = 10;
        foreach( $types as $name ) {

            $listEntity = $em->getRepository('OlegUserdirectoryBundle:TransfusionHemolysisCheckResultsList')->findOneByName($name);
            if( $listEntity ) {
                continue;
            }

            $listEntity = new TransfusionHemolysisCheckResultsList();
            $this->setDefaultList($listEntity,$count,$username,$name);

            //exit('exit generateObjectTypeActions');
            $em->persist($listEntity);
            $em->flush();

            $count = $count + 10;
        }

        return round($count/10);
    }

    public function generateComplexPlateletSummaryAntibodiesList() {

        $username = $this->get('security.context')->getToken()->getUser();
        $em = $this->getDoctrine()->getManager();

        $types = array(
            "HLA",
            "HPA",
            "None"
        );

        $count = 10;
        foreach( $types as $name ) {

            $listEntity = $em->getRepository('OlegUserdirectoryBundle:ComplexPlateletSummaryAntibodiesList')->findOneByName($name);
            if( $listEntity ) {
                continue;
            }

            $listEntity = new ComplexPlateletSummaryAntibodiesList();
            $this->setDefaultList($listEntity,$count,$username,$name);

            //exit('exit generateObjectTypeActions');
            $em->persist($listEntity);
            $em->flush();

            $count = $count + 10;
        }

        return round($count/10);
    }


    public function generateCCIUnitPlateletCountDefaultValueList() {

        $username = $this->get('security.context')->getToken()->getUser();
        $em = $this->getDoctrine()->getManager();

        $types = array(
            "3",
        );

        $count = 10;
        foreach( $types as $name ) {

            $listEntity = $em->getRepository('OlegUserdirectoryBundle:CCIUnitPlateletCountDefaultValueList')->findOneByName($name);
            if( $listEntity ) {
                continue;
            }

            $listEntity = new CCIUnitPlateletCountDefaultValueList();
            $this->setDefaultList($listEntity,$count,$username,$name);

            //exit('exit generateObjectTypeActions');
            $em->persist($listEntity);
            $em->flush();

            $count = $count + 10;
        }

        return round($count/10);
    }

    public function generateCCIPlateletTypeTransfusedList() {

        $username = $this->get('security.context')->getToken()->getUser();
        $em = $this->getDoctrine()->getManager();

        $types = array(
            "Regular Platelets",
            "Crossmatched",
            "HLA matched",
            "ABO matched",
        );

        $count = 10;
        foreach( $types as $name ) {

            $listEntity = $em->getRepository('OlegUserdirectoryBundle:CCIPlateletTypeTransfusedList')->findOneByName($name);
            if( $listEntity ) {
                continue;
            }

            $listEntity = new CCIPlateletTypeTransfusedList();
            $this->setDefaultList($listEntity,$count,$username,$name);

            //exit('exit generateObjectTypeActions');
            $em->persist($listEntity);
            $em->flush();

            $count = $count + 10;
        }

        return round($count/10);
    }

    public function generatePlateletTransfusionProductReceivingList() {

        $username = $this->get('security.context')->getToken()->getUser();
        $em = $this->getDoctrine()->getManager();

        $types = array(
            "HLA Platelets",
            "XM Platelets",
            "Regular Platelets",
            "Platelet Drip"
        );

        $count = 10;
        foreach( $types as $name ) {

            $listEntity = $em->getRepository('OlegUserdirectoryBundle:PlateletTransfusionProductReceivingList')->findOneByName($name);
            if( $listEntity ) {
                continue;
            }

            $listEntity = new PlateletTransfusionProductReceivingList();
            $this->setDefaultList($listEntity,$count,$username,$name);

            //exit('exit generateObjectTypeActions');
            $em->persist($listEntity);
            $em->flush();

            $count = $count + 10;
        }

        return round($count/10);
    }

    public function generateTransfusionProductStatusList() {

        $username = $this->get('security.context')->getToken()->getUser();
        $em = $this->getDoctrine()->getManager();

        $types = array(
            "Ordered",
            "Not Ordered",
            "Pending",
            "In-house"
        );

        $count = 10;
        foreach( $types as $name ) {

            $listEntity = $em->getRepository('OlegUserdirectoryBundle:TransfusionProductStatusList')->findOneByName($name);
            if( $listEntity ) {
                continue;
            }

            $listEntity = new TransfusionProductStatusList();
            $this->setDefaultList($listEntity,$count,$username,$name);

            //exit('exit generateObjectTypeActions');
            $em->persist($listEntity);
            $em->flush();

            $count = $count + 10;
        }

        return round($count/10);
    }


    public function generateWeekDaysList() {

        $username = $this->get('security.context')->getToken()->getUser();
        $em = $this->getDoctrine()->getManager();

        $types = array(
            "Monday",
            "Tuesday",
            "Wednesday",
            "Thursday",
            "Friday",
            "Saturday",
            "Sunday",
        );

        $count = 10;
        foreach( $types as $name ) {

            $listEntity = $em->getRepository('OlegUserdirectoryBundle:WeekDaysList')->findOneByName($name);
            if( $listEntity ) {
                continue;
            }

            $listEntity = new WeekDaysList();
            $this->setDefaultList($listEntity,$count,$username,$name);

            //exit('exit generateObjectTypeActions');
            $em->persist($listEntity);
            $em->flush();

            $count = $count + 10;
        }

        return round($count/10);
    }

    public function generateMonthsList() {

        $username = $this->get('security.context')->getToken()->getUser();
        $em = $this->getDoctrine()->getManager();

        $types = array(
            "January",
            "February",
            "March",
            "April",
            "May",
            "June",
            "July",
            "August",
            "September",
            "October",
            "November",
            "December",
        );

        $count = 10;
        foreach( $types as $name ) {

            $listEntity = $em->getRepository('OlegUserdirectoryBundle:MonthsList')->findOneByName($name);
            if( $listEntity ) {
                continue;
            }

            $listEntity = new MonthsList();
            $this->setDefaultList($listEntity,$count,$username,$name);

            //exit('exit generateObjectTypeActions');
            $em->persist($listEntity);
            $em->flush();

            $count = $count + 10;
        }

        return round($count/10);
    }


    public function generateLifeForm() {

        $username = $this->get('security.context')->getToken()->getUser();
        $em = $this->getDoctrine()->getManager();

        $types = array(
            "Homo Sapiens",
        );

        $count = 10;
        foreach( $types as $name ) {

            $listEntity = $em->getRepository('OlegUserdirectoryBundle:LifeFormList')->findOneByName($name);
            if( $listEntity ) {
                continue;
            }

            $listEntity = new LifeFormList();
            $this->setDefaultList($listEntity,$count,$username,$name);

            //exit('exit generateObjectTypeActions');
            $em->persist($listEntity);
            $em->flush();

            $count = $count + 10;
        }

        return round($count/10);
    }


    //add all MD users to "Pathology Result Signatories" (set the name of each list item to "FirstName LastName, MD" and set the "Object ID" to the corresponding user ID)
    /**
     * @Route("/list/add-mdusers-to-pathology-result-signatories/", name="employees_add-mdusers-to-pathology-result-signatories")
     * @Method("GET")
     */
    public function addMDUsersToPathologyResultSignatoriesList(Request $request)
    {
        if( false === $this->get('security.context')->isGranted('ROLE_PLATFORM_DEPUTY_ADMIN') ) {
            return $this->redirect( $this->generateUrl($this->container->getParameter('employees.sitename').'-order-nopermission') );
        }

        $em = $this->getDoctrine()->getManager();
        $creator = $this->get('security.context')->getToken()->getUser();
        $userSecUtil = $this->container->get('user_security_utility');

        //user_trainings_0_degree
        $repository = $em->getRepository('OlegUserdirectoryBundle:User');
        $dql =  $repository->createQueryBuilder("user");
        $dql->select('user');

        $dql->leftJoin("user.trainings", "trainings");
        $dql->leftJoin("trainings.degree", "degree");

        $dql->leftJoin("user.employmentStatus", "employmentStatus");
        $dql->leftJoin("employmentStatus.employmentType", "employmentType");


        $dql->where("degree.name = :degreeMd");
        $dql->andWhere("employmentType.name != :fellappType");

        $query = $em->createQuery($dql);

        $query->setParameters(array(
            'degreeMd' => 'MD',
            'fellappType' => "'Pathology Fellowship Applicant'"
        ));

        $users = $query->getResult();
        //$count = count($users);

        $count = 1;

        //add users to PathologyResultSignatoriesList
        foreach( $users as $user ) {
            //"FirstName LastName, MD"
            $name = $user->getUsernameOptimal();
            //echo "<br> $count User: ".$name."<br>";

            $listEntity = $em->getRepository('OlegUserdirectoryBundle:PathologyResultSignatoriesList')->findOneByName($name);
            if( $listEntity ) {
                continue;
            }

            $listEntity = new PathologyResultSignatoriesList();
            $this->setDefaultList($listEntity,null,$creator,$name);

            $listEntity->setObject($user);

            //set object type
            //$eventObjectType = $userSecUtil->getObjectByNameTransformer($creator,"User",'UserdirectoryBundle','EventObjectTypeList');
            //if( $eventObjectType ) {
            //    $listEntity->setObjectType($eventObjectType);
            //}

            echo "<br> $count: adding user: ".$name."<br>";

            $userWrapper = $listEntity->getUserWrapper();
            echo "userWrapper=".$userWrapper."<br>";
            if( !$userWrapper ) {
                echo "User wrapper is null <br>";
                $userWrapperTransformer = new SingleUserWrapperTransformer($em, $this->container, $creator, 'UserWrapper');
                $userWrapper = $userWrapperTransformer->createNewUserWrapperByUserId($user->getId());
                $listEntity->setUserWrapper($userWrapper);
            }

            $em->persist($listEntity);
            $em->flush();

            $count++;

            echo "Added user: ".$name."<br>";
            //exit('end');
        }

        $count = $count - 1;

        exit("<br>Added MD users: ".$count);
    }
    /**
     * Remove all "Pathology Fellowship Applicant" users from PathologyResultSignatoriesList
     *
     * @Route("/list/remove-fellapp-mdusers-to-pathology-result-signatories/", name="employees_remove-fellapp-mdusers-to-pathology-result-signatories")
     * @Method("GET")
     */
    public function removeFellappMDUsersToPathologyResultSignatoriesList(Request $request) {
        $em = $this->getDoctrine()->getManager();
        $pathologists = $em->getRepository('OlegUserdirectoryBundle:PathologyResultSignatoriesList')->findAll();
        $count = 0;

        foreach( $pathologists as $pathologist ) {

            $userWrapper = $pathologist->getUserWrapper();
            if( $userWrapper ) {

                $user = $userWrapper->getUser();
                if( $this->hasEmploymentType($user,"Pathology Fellowship Applicant") ) {

                    echo "remove user=".$user."<br>";
                    //$pathologist->set
                    //$em->remove($userWrapper);
                    $em->remove($pathologist);
                    $em->flush();
                    $count++;
                    continue;

                } else {
                    //echo "do not remove user=".$user."<br>";
                }//else

            }//if

        }//foreach

        exit("<br>Removed MD users: ".$count);
    }
    public function hasEmploymentType( $user, $employmentTypeStr ) {
        if( $user ) {
            $employmentStatuses = $user->getEmploymentStatus();
            foreach( $employmentStatuses as $employmentStatus ) {
                $employmentType = $employmentStatus->getEmploymentType();
                if( $employmentType && $employmentType->getName() == $employmentTypeStr ) {
                    return true;
                }
            }
        }
        return false;
    }


    public function setFormNodeVersion() {
        $em = $this->getDoctrine()->getManager();
        $query = $em->createQuery("UPDATE OlegUserdirectoryBundle:FormNode node SET node.version = '1' WHERE node.version = 'NULL' OR node.version IS NULL");
        $numUpdated = $query->execute();
        return "set formnode versions count ".$numUpdated;
    }
}
