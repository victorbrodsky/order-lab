<?php

namespace Oleg\UserdirectoryBundle\Controller;

use Oleg\UserdirectoryBundle\Entity\BuildingList;
use Oleg\UserdirectoryBundle\Entity\GeoLocation;
use Oleg\UserdirectoryBundle\Entity\ResearchLabTitleList;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;

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
     * Populate DB
     *
     * @Route("/genall", name="user_generate_all")
     * @Method("GET")
     * @Template("OlegUserdirectoryBundle:Admin:index.html.twig")
     */
    public function generateAllAction()
    {
        $userutil = new UserUtil();
        $user = $this->get('security.context')->getToken()->getUser();

        $max_exec_time = ini_get('max_execution_time');
        ini_set('max_execution_time', 300); //300 seconds = 5 minutes

        $default_time_zone = $this->container->getParameter('default_time_zone');

        $count_institution = $this->generateInstitutions();         //must be first

        $count_siteParameters = $this->generateSiteParameters();    //can be run only after institution generation

        $count_roles = $this->generateRoles();
        $count_terminationTypes = $this->generateTerminationTypes();
        $count_eventTypeList = $this->generateEventTypeList();
        $count_usernameTypeList = $userutil->generateUsernameTypes($this->getDoctrine()->getManager(),$user);
        $count_identifierTypeList = $this->generateIdentifierTypeList();
        $count_fellowshipTypeList = $this->generateFellowshipTypeList();
        $count_residencyTrackList = $this->generateResidencyTrackList();
        $count_locationTypeList = $this->generateLocationTypeList();
        $count_equipmentType = $this->generateEquipmentType();
        $count_equipment = $this->generateEquipment();
        $count_locprivacy = $this->generateLocationPrivacy();
        $count_reslabtitles = $this->generateResLabTitles();
        $count_buildings = $this->generateBuildings();

        $count_users = $userutil->generateUsersExcel($this->getDoctrine()->getManager(),$this->container);

        $count_states = $this->generateStates();
        $count_countryList = $this->generateCountryList();

        $count_boardSpecialties = $this->generateBoardSpecialties();

        $this->get('session')->getFlashBag()->add(
            'notice',
            'Generated Tables: '.
            'Roles='.$count_roles.', '.
            'Site Settings='.$count_siteParameters.', '.
            'Institutions='.$count_institution.', '.
            'Users='.$count_users.', '.
            'States='.$count_states.', '.
            'Countries='.$count_countryList.', '.
            'Board Specialties='.$count_boardSpecialties.', '.
            'Employment Types of Termination='.$count_terminationTypes.', '.
            'Event Log Types ='.$count_eventTypeList.', '.
            'Username Types ='.$count_usernameTypeList.', '.
            'Identifier Types ='.$count_identifierTypeList.', '.
            'Residency Tracks ='.$count_residencyTrackList.', '.
            'Fellowship Types ='.$count_fellowshipTypeList.', '.
            'Equipment Types ='.$count_equipmentType.', '.
            'Equipment ='.$count_equipment.', '.
            'Location Types ='.$count_locationTypeList.', '.
            'Location Privacy ='.$count_locprivacy.', '.
            'Buildings ='.$count_buildings.', '.
            'Reaserch Lab Titles='.$count_reslabtitles.' '.
            ' (Note: -1 means that this table is already exists)'
        );


        ini_set('max_execution_time', $max_exec_time); //set back to the original value

        return $this->redirect($this->generateUrl('user_admin_index'));
    }




//////////////////////////////////////////////////////////////////////////////

    public function setDefaultList( $entity, $count, $user, $name=null ) {
//        $entity->setOrderinlist( $count );
//        $entity->setCreator( $user );
//        $entity->setCreatedate( new \DateTime() );
//        $entity->setType('default');
//        if( $name ) {
//            $entity->setName( trim($name) );
//        }
//        return $entity;
        $userutil = new UserUtil();
        return $userutil->setDefaultList( $entity, $count, $user, $name );
    }

   

    public function generateRoles() {

        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('OlegUserdirectoryBundle:Roles')->findAll();
        if( $entities ) {
            //return -1;
        }

        //Note: fos user has role ROLE_SCANORDER_SUPER_ADMIN

        $types = array(

            //////////// general roles are set by security.yml only ////////////

            //general super admin role for all sites
            "ROLE_ADMIN" => array("OrderPlatform Administrator","Full access for all sites"),
            //"ROLE_BANNED" => "Banned user for all sites",                 //general super admin role for all sites
            //"ROLE_UNAPPROVED" => "Unapproved User",                       //general unapproved user

            //////////// Scanorder roles ////////////
            "ROLE_SCANORDER_ADMIN" => array("ScanOrder Administrator","Full access for Scan Order site"),
            "ROLE_SCANORDER_PROCESSOR" => array("ScanOrder Processor","Allow to view all orders and change scan order status"),

            "ROLE_SCANORDER_DIVISION_CHIEF" => array("ScanOrder Division Chief","Allow to view and amend all orders for this division(institution)"),  //view or modify all orders of the same division(institution)
            "ROLE_SCANORDER_SERVICE_CHIEF" => array("ScanOrder Service Chief","Allow to view and amend all orders for this service"),    //view or modify all orders of the same service

            "ROLE_SCANORDER_DATA_QUALITY_ASSURANCE_SPECIALIST" => array("ScanOrder Data Quality Assurance Specialist","Allow to make data quality modification"),

            //"ROLE_USER" => "User", //this role must be always assigned to the authenticated user. Required by fos user bundle.

            "ROLE_SCANORDER_SUBMITTER" => array("ScanOrder Submitter","Allow submit new orders, amend own order"),
            "ROLE_SCANORDER_ORDERING_PROVIDER" => array("ScanOrder Ordering Provider","Allow submit new orders, amend own order"),

            "ROLE_SCANORDER_PATHOLOGY_FELLOW" => array("ScanOrder Pathology Fellow",""),
            "ROLE_SCANORDER_PATHOLOGY_FACULTY" => array("ScanOrder Pathology Faculty",""),

            "ROLE_SCANORDER_COURSE_DIRECTOR" => array("ScanOrder Course Director","Allow to be a Course Director in Educational orders"),
            "ROLE_SCANORDER_PRINCIPAL_INVESTIGATOR" => array("ScanOrder Principal Investigator","Allow to be a Principal Investigator in Research orders"),

            "ROLE_SCANORDER_UNAPPROVED_SUBMITTER" => array("ScanOrder Unapproved Submitter","Does not allow to visit Scan Order site"),
            "ROLE_SCANORDER_BANNED" => array("ScanOrder Banned User","Does not allow to visit Scan Order site"),

            "ROLE_SCANORDER_ONCALL_TRAINEE" => array("OrderPlatform On Call Trainee","Allow to see the phone numbers & email of Home location"),
            "ROLE_SCANORDER_ONCALL_ATTENDING" => array("OrderPlatform On Call Attending","Allow to see the phone numbers & email of Home location"),

            //////////// EmployeeDirectory roles ////////////
            "ROLE_USERDIRECTORY_OBSERVER" => array("EmployeeDirectory Observer","Allow to view all employees"),
            "ROLE_USERDIRECTORY_EDITOR" => array("EmployeeDirectory Editor","Allow to edit all employees"),
            "ROLE_USERDIRECTORY_ADMIN" => array("EmployeeDirectory Administrator","Full access for Employee Directory site"),
            "ROLE_USERDIRECTORY_BANNED" => array("EmployeeDirectory Banned User","Does not allow to visit Employee Directory site"),
            "ROLE_USERDIRECTORY_UNAPPROVED" => array("EmployeeDirectory Unapproved User","Does not allow to visit Employee Directory site"),

        );

        $username = $this->get('security.context')->getToken()->getUser();

        $count = 1;
        foreach( $types as $role => $aliasDescription ) {

            $alias = $aliasDescription[0];
            $description = $aliasDescription[1];

            $entity = $em->getRepository('OlegUserdirectoryBundle:Roles')->findOneByName(trim($role));

            if( !$entity ) {
                $entity = new Roles();
                $this->setDefaultList($entity,$count,$username,null);
            }

            $entity->setName( trim($role) );
            $entity->setAlias( trim($alias) );
            $entity->setDescription( trim($description) );

            //set attributes for ROLE_SCANORDER_ONCALL_TRAINEE
            if( $role == "ROLE_SCANORDER_ONCALL_TRAINEE" ) {
                $attr = new RoleAttributeList();
                $this->setDefaultList($attr,1,$username,"Call Pager");
                $attr->setValue("(111) 111-1111");
                $entity->addAttribute($attr);
            }
            //set attributes for ROLE_SCANORDER_ONCALL_ATTENDING
            if( $role == "ROLE_SCANORDER_ONCALL_ATTENDING" ) {
                $attr = new RoleAttributeList();
                $this->setDefaultList($attr,10,$username,"Call Pager");
                $attr->setValue("(222) 222-2222");
                $entity->addAttribute($attr);
            }

            $em->persist($entity);
            $em->flush();

            $count = $count + 10;

        } //foreach

        return round($count/10);
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
            "siteEmail" => "oli2002@med.cornell.edu", //"slidescan@med.cornell.edu",

            "smtpServerAddress" => "smtp.med.cornell.edu",

            "aDLDAPServerAddress" => "a.wcmc-ad.net",
            "aDLDAPServerOu" => "a.wcmc-ad.net",
            "aDLDAPServerAccountUserName" => "svc_aperio_spectrum@a.wcmc-ad.net",
            "aDLDAPServerAccountPassword" => "Aperi0,123",

            "dbServerAddress" => "127.0.0.1",
            "dbServerPort" => "null",
            "dbServerAccountUserName" => "symfony2",
            "dbServerAccountPassword" => "symfony2",
            "dbDatabaseName" => "ScanOrder",

            "institutionurl" => "http://weill.cornell.edu",
            "institutionname" => "Weill Cornell Medical College",
            "departmenturl" => "http://www.cornellpathology.com",
            "departmentname" => "Pathology and Laboratory Medicine Department",

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
            "employeesuploadpath" => "pathology-employees/Documents",
            "scanuploadpath" => "scan-order/Documents"

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

        $em->persist($params);
        $em->flush();

        return round($count/10);
    }


    //https://bitbucket.org/weillcornellpathology/scanorder/issue/221/multiple-office-locations-and-phone
    public function generateInstitutions() {

        $username = $this->get('security.context')->getToken()->getUser();

        $em = $this->getDoctrine()->getManager();
        $entities = $em->getRepository('OlegUserdirectoryBundle:Institution')->findAll();

        if( $entities ) {
            return -1;
        }

        $wcmcDep = array(
            'Anesthesiology',
            'Biochemistry',
            'Feil Family Brain and Mind Research Institute',
            'Cardiothoracic Surgery' => array(
                'Thoracic Surgery'
            ),
            'Cell and Developmental Biology' => null,
            'Dermatology' => null,
            'Genetic Medicine' => null,
            'Healthcare Policy and Research' => array(
                'Biostatistics and Epidemiology',
                'Comparative Effectiveness and Outcomes Research',
                'Health Informatics',
                'Health Policy and Economics',
                'Health Systems Innovation and Implementation Science'
            ),
            'Weill Department of Medicine' => array(
                'Cardiology',
                'Clinical Epidemiology and Evaluative Sciences Research',
                'Clinical Pharmacology'                                                     //continue  dep
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
                'Pathology Informatics'
            ),
            'Pediatrics' => array(
                'Cardiology',
                'Child Development',
                'Child Neurology'                                                           //continue
            ),
            'Pharmacology' => null,
            'Physiology and Biophysics' => null,
            'Psychiatry' => array(
                'Sackler Institute for Developmental Psychobiology'
            ),
            'Primary Care' => null,
            'Radiology' => null,
            'Radiation Oncology' => null,
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
            'Pathology' => null,
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
            'Weill Cornell Medical College'=>$wcmc,
            "New York Hospital"=>$nyh,
            "Weill Cornell Medical College Qatar"=>$wcmcq,
            "Memorial Sloan Kettering Cancer Center"=>$msk,
            "Hospital for Special Surgery"=>$hss
        );


        $instCount = 1;
        foreach( $institutions as $institutionname=>$infos ) {
            $institution = new Institution();
            $this->setDefaultList($institution,$instCount,$username,$institutionname);
            $institution->setAbbreviation( trim($infos['abbreviation']) );

            if( $infos['departments'] && is_array($infos['departments'])  ) {
                $depCount = 0;
                foreach( $infos['departments'] as $departmentname=>$divisions ) {
                    $department = new Department();
                    if( is_numeric($departmentname) ){
                        $departmentname = $infos['departments'][$departmentname];
                    }
                    //echo "departmentname=".$departmentname."<br>";
                    $this->setDefaultList($department,$depCount,$username,$departmentname);

                    if( $divisions && is_array($divisions) ) {
                        $divCount = 0;
                        foreach( $divisions as $divisionname=>$services ) {
                            $division = new Division();
                            if( is_numeric($divisionname) ){
                                $divisionname = $divisions[$divisionname];
                            }
                            $this->setDefaultList($division,$divCount,$username,$divisionname);


                            if( $services && is_array($services) ) {
                                $serCount = 0;
                                foreach( $services as $servicename ) {
                                    $service = new Service();
                                    if( is_numeric($servicename) ){
                                        $servicename = $services[$servicename];
                                    }
                                    $this->setDefaultList($service,$serCount,$username,$servicename);

                                    $division->addService($service);
                                    $serCount = $serCount + 10;
                                }
                            }//services


                            $department->addDivision($division);
                            $divCount = $divCount + 10;
                        }
                    }//divisions

                    $institution->addDepartment($department);
                    $depCount = $depCount + 10;

                }
            }//departmets

            $em->persist($institution);
            $em->flush();
            $instCount = $instCount + 10;
        } //foreach

        return round($instCount/10);
    }


    public function generateStates() {

        $em = $this->getDoctrine()->getManager();
        $entities = $em->getRepository('OlegUserdirectoryBundle:States')->findAll();

        if( $entities ) {
            return -1;
        }

        $states = array('AL'=>"Alabama",
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
            'WY'=>"Wyoming");


        $username = $this->get('security.context')->getToken()->getUser();

        $count = 1;
        foreach( $states as $key => $value ) {

            $entity = new States();
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
        $entities = $em->getRepository('OlegUserdirectoryBundle:Countries')->findAll();

        if( $entities ) {
            return -1;
        }

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

        $count = 1;
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

        $count = 1;
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

        $count = 1;
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
        $entities = $em->getRepository('OlegUserdirectoryBundle:EventTypeList')->findAll();

        if( $entities ) {
            return -1;
        }

        $elements = array(
            'Login Page Visit',
            'Successful Login',
            'Bad Credentials',
            'Unsuccessful Login Attempt',
            'Unapproved User Login Attempt',
            'Banned User Login Attempt',
            'User Created',
            'User Updated',
            'Search'
        );

        $username = $this->get('security.context')->getToken()->getUser();

        $count = 1;
        foreach( $elements as $value ) {

            $entity = new EventTypeList();
            $this->setDefaultList($entity,$count,$username,null);
            $entity->setName( trim($value) );

            $em->persist($entity);
            $em->flush();

            $count = $count + 10;

        } //foreach

        return round($count/10);
    }


    public function generateIdentifierTypeList() {
        $em = $this->getDoctrine()->getManager();
        $entities = $em->getRepository('OlegUserdirectoryBundle:IdentifierTypeList')->findAll();

        if( $entities ) {
            return -1;
        }

        $elements = array(
            'WCMC Employee Identification Number (EIN)',
            'National Provider Identifier (NPI)'
        );

        $username = $this->get('security.context')->getToken()->getUser();

        $count = 1;
        foreach( $elements as $value ) {

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

        $count = 1;
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

        $count = 1;
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
            'Patient Contact Information',
            'Medical Office',
            'Inpatient Room'
        );

        $username = $this->get('security.context')->getToken()->getUser();

        $count = 1;
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
        $entities = $em->getRepository('OlegUserdirectoryBundle:EquipmentType')->findAll();

        if( $entities ) {
            return -1;
        }

        $types = array(
            'Whole Slide Scanner'
        );

        $count = 1;
        foreach( $types as $type ) {

            $listEntity = new EquipmentType();
            $this->setDefaultList($listEntity,$count,$username,$type);

            $em->persist($listEntity);
            $em->flush();

            $count = $count + 10;
        }

        return $count;
    }

    public function generateEquipment() {

        $username = $this->get('security.context')->getToken()->getUser();

        $em = $this->getDoctrine()->getManager();
        $entities = $em->getRepository('OlegUserdirectoryBundle:Equipment')->findAll();

        if( $entities ) {
            return -1;
        }

        $types = array(
            'Aperio ScanScope AT'
        );

        $count = 1;
        foreach( $types as $type ) {

            $listEntity = new Equipment();
            $this->setDefaultList($listEntity,$count,$username,$type);

            $keytype = $em->getRepository('OlegUserdirectoryBundle:EquipmentType')->findOneByName('Whole Slide Scanner');

            if( !$keytype ) {
               //exit('equipment keytype is null');
               throw new \Exception( 'Equipment keytype is null' );
            }

            $keytype->addEquipment($listEntity);

            $em->persist($listEntity);
            $em->flush();

            $count = $count + 10;
        }

        return $count;
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

        $count = 1;
        foreach( $types as $type ) {

            $listEntity = new LocationPrivacyList();
            $this->setDefaultList($listEntity,$count,$username,$type);

            $em->persist($listEntity);
            $em->flush();

            $count = $count + 10;
        }

        return $count;
    }


    public function generateResLabTitles() {

        $username = $this->get('security.context')->getToken()->getUser();

        $em = $this->getDoctrine()->getManager();
        $entities = $em->getRepository('OlegUserdirectoryBundle:ResearchLabTitleList')->findAll();

        if( $entities ) {
            return -1;
        }

        $types = array(
            "Laboratory of Prostate Cancer Research Group",
            "Proteolytic Oncogenesis",
            "Macrophages and Tissue Remodeling",
            "Molecular Gynecologic Pathology",
            "Cancer Biology",
            "Laboratory of Cell Metabolism",
            "Viral Oncogenesis",
            "Center for Vascular Biology",
            "Cell Cycle",
            "Laboratory of Stem Cell Aging and Cancer",
            "Molecular Pathology",
            "Skeletal Biology"
        );

        $count = 1;
        foreach( $types as $type ) {

            $listEntity = new ResearchLabTitleList();
            $this->setDefaultList($listEntity,$count,$username,$type);

            $em->persist($listEntity);
            $em->flush();

            $count = $count + 10;
        }

        return $count;
    }


    public function generateBuildings() {

        $username = $this->get('security.context')->getToken()->getUser();

        $em = $this->getDoctrine()->getManager();
        $entities = $em->getRepository('OlegUserdirectoryBundle:BuildingList')->findAll();

        if( $entities ) {
            return -1;
        }

        $buildings = array(
            "Weill Cornell Medical College" => array('street1'=>'1300 York Ave','abbr'=>'C','inst'=>'WCMC'),
            "Belfer Research Building" => array('street1'=>'413 East 69th Street','abbr'=>null,'inst'=>'WCMC'),
            "Helmsley Medical Tower" => array('street1'=>'1320 York Ave','abbr'=>null,'inst'=>'WCMC'),
            "Weill Greenberg Center" => array('street1'=>'1305 York Ave','abbr'=>null,'inst'=>'WCMC'),
            "Olin Hall" => array('street1'=>'445 East 69th Street','abbr'=>null,'inst'=>'WCMC'),
            "" => array('street1'=>'575 Lexington Ave','abbr'=>null,'inst'=>'WCMC'),                        //WCMC - 575 Lexington Ave
            "" => array('street1'=>'402 East 67th Street','abbr'=>null,'inst'=>'WCMC'),                     //WCMC - 402 East 67th Street
            "" => array('street1'=>'425 East 61st Street','abbr'=>null,'inst'=>'WCMC'),                     //WCMC - 425 East 61st Street
            "Starr Pavilion" => array('street1'=>'520 East 70th Street','abbr'=>'ST','inst'=>'NYP'),
            "J Corridor" => array('street1'=>'525 East 68th Street','abbr'=>'J','inst'=>'NYP'),
            "L Corridor" => array('street1'=>'525 East 68th Street','abbr'=>'L','inst'=>'NYP'),
            "K Wing" => array('street1'=>'525 East 68th Street','abbr'=>'K','inst'=>'NYP'),
            "F Wing, Floors 2-9" => array('street1'=>'525 East 68th Street','abbr'=>'F','inst'=>'NYP'),
            "Baker Pavilion - F Wing" => array('street1'=>'525 East 68th Street','abbr'=>'P','inst'=>'NYP'),
            "Payson Pavilion" => array('street1'=>'425 East 61st Street','abbr'=>null,'inst'=>'NYP'),
            "Whitney Pavilion" => array('street1'=>'525 East 68th Street','abbr'=>'W','inst'=>'NYP'),
            "M Wing" => array('street1'=>'530 East 70th Street','abbr'=>'M','inst'=>'NYP'),
            "N Wing" => array('street1'=>'530 East 70th Street','abbr'=>'N','inst'=>'NYP'),
            "Weill Cornell Medical Assoc. Eastside" => array('street1'=>'201 East 80th Street','abbr'=>null,'inst'=>'NYP'),
            "Weill Cornell Medical Assoc. Westside" => array('street1'=>'12 West 72nd Street','abbr'=>null,'inst'=>'NYP'),
            "Iris Cantor Women’s Health Center" => array('street1'=>'425 East 61st Street','abbr'=>null,'inst'=>'NYP'),
            "Weill Cornell Imaging at NewYork-Presbyterian" => array('street1'=>'416 East 55th Street','abbr'=>null,'inst'=>'NYP'),    //NYP - Weill Cornell Imaging at NewYork-Presbyterian / 416 East 55th Street
            "Weill Cornell Imaging at NewYork-Presbyterian, 9th Floor" => array('street1'=>'425 East 61st Street','abbr'=>null,'inst'=>'NYP'),    //NYP - Weill Cornell Imaging at NewYork-Presbyterian / 425 East 61st Street, 9th Floor
            "Weill Cornell Imaging at NewYork-Presbyterian, lobby level" => array('street1'=>'520 East 70th Street','abbr'=>null,'inst'=>'NYP'),    //NYP - Weill Cornell Imaging at NewYork-Presbyterian / 520 East 70th Street, lobby level
            "Weill Cornell Imaging at NewYork-Presbyterian, 3rd Floor" => array('street1'=>'1305 York Avenue','abbr'=>null,'inst'=>'NYP'),    //NYP - Weill Cornell Imaging at NewYork-Presbyterian / 1305 York Avenue, 3rd Floor
            "Oxford Medical Offices" => array('street1'=>'428 East 72nd Street','abbr'=>null,'inst'=>'NYP'),
            "Stich Building" => array('street1'=>'1315 York Ave','abbr'=>null,'inst'=>'NYP'),
            "Kips Bay Medical Offices" => array('street1'=>'411 East 69th Street','abbr'=>null,'inst'=>'NYP'),
            "Phipps House Medical Offices" => array('street1'=>'449 East 68th Street','abbr'=>null,'inst'=>'NYP'),
            ""  => array('street1'=>'333 East 38th Street','abbr'=>null,'inst'=>'NYP')  //NYP - 333 East 38th Street

        );

        $city = "New York";
        $state = $em->getRepository('OlegUserdirectoryBundle:Institution')->findOneByName("New York");
        $country = $em->getRepository('OlegUserdirectoryBundle:Institution')->findOneByName("United States");

        $count = 1;
        foreach( $buildings as $building => $attr ) {

            $listEntity = new BuildingList();
            $this->setDefaultList($listEntity,$count,$username,$building);

            //add buildings attributes
            $street1 = $attr['street1'];
            $buildingAbbr = $attr['abbr'];

            $geo = new GeoLocation();
            $geo->setStreet1($street1);
            $geo->setCity($city);
            $geo->setState($state);
            $geo->setCountry($country);

            $listEntity->setGeoLocation($geo);
            $listEntity->setAbbreviation($buildingAbbr);

            $instAbbr = $attr['inst'];
            $inst = $em->getRepository('OlegUserdirectoryBundle:Institution')->findOneByAbbreviation($instAbbr);
            if( $inst ) {
                $listEntity->setInstitution($inst);
            }

            $em->persist($listEntity);
            $em->flush();

            $count = $count + 10;
        }

        return $count;
    }

}
