<?php

namespace Oleg\UserdirectoryBundle\Controller;


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
        $count_usernameTypeList = $userutil->generateUsernameTypes($this->getDoctrine()->getManager(),$user); //$this->generateUsernameTypes();


        $count_users = $userutil->generateUsersExcel($this->getDoctrine()->getManager(),$default_time_zone);

        $count_states = $this->generateStates();

        $count_boardSpecialties = $this->generateBoardSpecialties();

        $this->get('session')->getFlashBag()->add(
            'notice',
            'Generated Tables: '.
            'Roles='.$count_roles.', '.
            'Site Settings='.$count_siteParameters.', '.
            'Institutions='.$count_institution.', '.
            'Users='.$count_users.', '.
            'States='.$count_states.', '.
            'Board Specialties='.$count_boardSpecialties.', '.
            'Employment Types of Termination='.$count_terminationTypes.', '.
            'Event Log Types ='.$count_eventTypeList.', '.
            'Username Types ='.$count_usernameTypeList.', '.
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
            return -1;
        }

        //Note: fos user has role ROLE_SCANORDER_SUPER_ADMIN

        $types = array(

            //////////// general roles are set by security.yml only ////////////
            "ROLE_ADMIN" => "OrderPlatform Administrator",                //general super admin role for all sites
            //"ROLE_BANNED" => "Banned user for all sites",                 //general super admin role for all sites
            //"ROLE_UNAPPROVED" => "Unapproved User",                       //general unapproved user

            //////////// Scanorder roles ////////////
            "ROLE_SCANORDER_ADMIN" => "ScanOrder Administrator",
            "ROLE_SCANORDER_PROCESSOR" => "ScanOrder Processor",

            "ROLE_SCANORDER_DIVISION_CHIEF" => "ScanOrder Division Chief",  //view or modify all orders of the same division(institution)
            "ROLE_SCANORDER_SERVICE_CHIEF" => "ScanOrder Service Chief",    //view or modify all orders of the same service

            "ROLE_SCANORDER_DATA_QUALITY_ASSURANCE_SPECIALIST" => "ScanOrder Data Quality Assurance Specialist",

            //"ROLE_USER" => "User", //this role must be always assigned to the authenticated user. Required by fos user bundle.

            "ROLE_SCANORDER_SUBMITTER" => "ScanOrder Submitter",
            "ROLE_SCANORDER_ORDERING_PROVIDER" => "ScanOrder Ordering Provider",

            "ROLE_SCANORDER_PATHOLOGY_RESIDENT" => "ScanOrder Pathology Resident",
            "ROLE_SCANORDER_PATHOLOGY_FELLOW" => "ScanOrder Pathology Fellow",
            "ROLE_SCANORDER_PATHOLOGY_FACULTY" => "ScanOrder Pathology Faculty",

            "ROLE_SCANORDER_COURSE_DIRECTOR" => "ScanOrder Course Director",
            "ROLE_SCANORDER_PRINCIPAL_INVESTIGATOR" => "ScanOrder Principal Investigator",

            "ROLE_SCANORDER_UNAPPROVED_SUBMITTER" => "ScanOrder Unapproved Submitter",
            "ROLE_SCANORDER_BANNED" => "ScanOrder Banned User",

            //////////// EmployeeDirectory roles ////////////
            "ROLE_USERDIRECTORY_OBSERVER" => "EmployeeDirectory Observer",
            "ROLE_USERDIRECTORY_EDITOR" => "EmployeeDirectory Editor",
            "ROLE_USERDIRECTORY_ADMIN" => "EmployeeDirectory Administrator",
            "ROLE_USERDIRECTORY_BANNED" => "EmployeeDirectory Banned User",
            "ROLE_USERDIRECTORY_UNAPPROVED" => "EmployeeDirectory Unapproved User",

        );

        $username = $this->get('security.context')->getToken()->getUser();

        $count = 1;
        foreach( $types as $role => $alias ) {

            $entity = new Roles();
            $this->setDefaultList($entity,$count,$username,null);
            $entity->setName( trim($role) );
            $entity->setAlias( trim($alias) );

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
                                        'The administrators are planning to return this site to a fully functional state on or before [June 10th, 2:00pm].'.
                                        'If you were in the middle of entering order information, it was saved as an "Unsubmitted" order '.
                                        'and you should be able to submit that order after the maintenance is complete.',
            "maintenanceloginmsg" =>    'The scheduled maintenance of this software has begun. The administrators are planning to return this site to a fully '.
                                        'functional state on or before [June 10th, 2:00pm]. If you were in the middle of entering order information, '.
                                        'it was saved as an "Unsubmitted" order and you should be able to submit that order after the maintenance is complete.'
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
            'abbreviation'=>'NYH',
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


    public function generateBoardSpecialties() {

        $em = $this->getDoctrine()->getManager();
        $entities = $em->getRepository('OlegUserdirectoryBundle:BoardCertifiedSpecialties')->findAll();

        if( $entities ) {
            return -1;
        }

        $elements = array(
            'AP',
            'CP',
            'Hematology',
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


//    public function generateUsernameTypes() {
//        $em = $this->getDoctrine()->getManager();
//        $entities = $em->getRepository('OlegUserdirectoryBundle:UsernameType')->findAll();
//
//        if( $entities ) {
//            return -1;
//        }
//
//        $elements = array(
//            'WCMC CWID',
//            'Autogenerated',
//            'Local User'
//        );
//
//        $username = $this->get('security.context')->getToken()->getUser();
//
//        $count = 1;
//        foreach( $elements as $value ) {
//
//            $entity = new UsernameType();
//            $this->setDefaultList($entity,$count,$username,null);
//            $entity->setName( trim($value) );
//
//            $em->persist($entity);
//            $em->flush();
//
//            $count = $count + 10;
//
//        } //foreach
//
//        return round($count/10);
//    }

}
