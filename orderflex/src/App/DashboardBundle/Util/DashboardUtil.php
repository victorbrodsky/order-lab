<?php
/**
 * Created by PhpStorm.
 * User: oli2002 Oleg Ivanov
 * Date: 10/7/2021
 * Time: 12:25 PM
 */

namespace App\DashboardBundle\Util;



use App\UserdirectoryBundle\Entity\Logger; //process.py script: replaced namespace by ::class: added use line for classname=Logger
use App\TranslationalResearchBundle\Entity\Project; //process.py script: replaced namespace by ::class: added use line for classname=Project
use App\TranslationalResearchBundle\Entity\TransResRequest; //process.py script: replaced namespace by ::class: added use line for classname=TransResRequest
use App\TranslationalResearchBundle\Entity\Invoice; //process.py script: replaced namespace by ::class: added use line for classname=Invoice
use App\DashboardBundle\Entity\ChartList; //process.py script: replaced namespace by ::class: added use line for classname=ChartList
use App\DashboardBundle\Entity\TopicList; //process.py script: replaced namespace by ::class: added use line for classname=TopicList
use App\UserdirectoryBundle\Entity\Institution; //process.py script: replaced namespace by ::class: added use line for classname=Institution
use App\TranslationalResearchBundle\Entity\SpecialtyList; //process.py script: replaced namespace by ::class: added use line for classname=SpecialtyList
use App\UserdirectoryBundle\Entity\EventTypeList; //process.py script: replaced namespace by ::class: added use line for classname=EventTypeList
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Bundle\SecurityBundle\Security;


class DashboardUtil
{

    protected $container;
    protected $em;
    //protected $secTokenStorage;
    //protected $secAuth;
    protected $security;
    protected $session;

    private $width = 1200;
    private $height = 600;
    private $otherId = "All other [[otherStr]] combined";
    private $otherSearchStr = "All other ";
    //private $quantityLimit = 10;

    //private $lightFilter = true;

    public function __construct(
        EntityManagerInterface $em,
        ContainerInterface $container,
        Security $security,
        Session $session
    )
    {
        $this->container = $container;
        $this->em = $em;
        //$this->secAuth = $container->get('security.authorization_checker'); //$this->secAuth->isGranted("ROLE_USER")
        //$this->secToken = $container->get('security.token_storage')->getToken(); //$user = $this->secToken->getUser();
        //$this->secTokenStorage = $container->get('security.token_storage'); //$user = $this->secTokenStorage->getToken()->getUser();
        $this->security = $security;
        $this->session = $session;
    }

    public function getChartViewCount($startDate,$endDate,$chart) {

        if( !$chart) {
            return 0;
        }

        $dqlParameters = array();

        //get the date from event log
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:Logger'] by [Logger::class]
        $repository = $this->em->getRepository(Logger::class);
        $dql = $repository->createQueryBuilder("logger");

        $dql->select("logger");

        $dql->innerJoin('logger.user', 'user');
        $dql->innerJoin('logger.eventType', 'eventType');

        //$dql->where("logger.siteName = 'translationalresearch'");
        //$dql->where("logger.siteName = '".$site."'");

        $dql->andWhere("eventType.name = :eventTypeName");
        $dqlParameters['eventTypeName'] = "Dashboard Chart Viewed";

        $dql->andWhere("logger.entityName = :entityName");
        $dqlParameters['entityName'] = "ChartList";

        $dql->andWhere("logger.entityId = :entityId");
        $dqlParameters['entityId'] = $chart->getId();

        //$dql->andWhere("logger.creationdate > :startDate AND logger.creationdate < :endDate");
        $dql->andWhere('logger.creationdate >= :startDate');
        //$startDate->modify('-1 day');
        $dqlParameters['startDate'] = $startDate->format('Y-m-d H:i:s');

        $dql->andWhere('logger.creationdate <= :endDate');
        //$endDate->modify('+1 day');
        $dqlParameters['endDate'] = $endDate->format('Y-m-d H:i:s');

        //$dql->orderBy("logger.id","DESC");
        $query = $dql->getQuery();

        $query->setParameters($dqlParameters);

        $loggers = $query->getResult();

        //echo "loggers=".count($loggers)."<br>";
        //exit();

        $count = count($loggers);
        $count = intval($count);

        return $count;
    }
    public function getViewedCharts($startDate,$endDate) {
        $dqlParameters = array();

        //get the date from event log
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:Logger'] by [Logger::class]
        $repository = $this->em->getRepository(Logger::class);
        $dql = $repository->createQueryBuilder("logger");

        $dql->select("DISTINCT(logger.entityId) as chartId");

        $dql->innerJoin('logger.eventType', 'eventType');

        $dql->andWhere("eventType.name = :eventTypeName");
        $dqlParameters['eventTypeName'] = "Dashboard Chart Viewed";

        $dql->andWhere("logger.entityName = :entityName");
        $dqlParameters['entityName'] = "ChartList";

        //$dql->andWhere("logger.creationdate > :startDate AND logger.creationdate < :endDate");
        $dql->andWhere('logger.creationdate >= :startDate');
        $dqlParameters['startDate'] = $startDate->format('Y-m-d H:i:s');

        $dql->andWhere('logger.creationdate <= :endDate');
        $dqlParameters['endDate'] = $endDate->format('Y-m-d H:i:s');

        $dql->orderBy("logger.entityId","DESC");
        $query = $dql->getQuery();

        $query->setParameters($dqlParameters);

        $chartIds = $query->getResult();

        //echo "loggers=".count($chartIds)."<br>";

        $charts = array();
        foreach($chartIds as $chartId) {
        //process.py script: replaced namespace by ::class: ['AppDashboardBundle:ChartList'] by [ChartList::class]
            $charts[] = $this->em->getRepository(ChartList::class)->find($chartId['chartId']);
        }

        return $charts;
    }

    //get topics
    public function getFilterTopics() {
        //process.py script: replaced namespace by ::class: ['AppDashboardBundle:TopicList'] by [TopicList::class]
        $root = $this->em->getRepository(TopicList::class)->findOneByName("All Charts");
        if( !$root ) {
        //process.py script: replaced namespace by ::class: ['AppDashboardBundle:TopicList'] by [TopicList::class]
            $root = $this->em->getRepository(TopicList::class)->findOneByLevel(0);
        }

        //show only public topics
        $public = false;
        if( !$this->security->isGranted('ROLE_DASHBOARD_USER') ) {
            $public = true;
        }

        //$filterTopics = $root->getFullTreeAsEntity(array(),array("default","user-added"));
        $filterTopics = $root->getFullTreeAsEntity(array(),array(),$public);
        
        return $filterTopics;
    }

    public function printTreeSelectList() {
        //process.py script: replaced namespace by ::class: ['AppDashboardBundle:TopicList'] by [TopicList::class]
        $root = $this->em->getRepository(TopicList::class)->findOneByName("All Charts");
        if( !$root ) {
        //process.py script: replaced namespace by ::class: ['AppDashboardBundle:TopicList'] by [TopicList::class]
            $root = $this->em->getRepository(TopicList::class)->findOneByLevel(0);
        }

        $topicsArr = array();
        $filterTopics = $root->getFlatTree();
        foreach($filterTopics as $filterTopic) {
            // "add ID=".$filterTopic->getId().", value=".$filterTopic->getOneTreeNameWithoutRoot(" > ")."<br>";
            $topicsArr[$filterTopic->getId()] = $filterTopic->getOneTreeNameWithoutRoot(" > "); //getTreeName("=>");
        }

        return $topicsArr;
    }

    public function getFilterServices() {
        //get all services presented in the dashboard charts
        //process.py script: replaced namespace by ::class: ['AppDashboardBundle:ChartList'] by [ChartList::class]
        $repository = $this->em->getRepository(ChartList::class);
        $dql =  $repository->createQueryBuilder("list");
        $dql->select('list');
        $dql->leftJoin("list.institutions", "institutions");
        $dql->where("list.type = :typedef OR list.type = :typeadd");
        $dql->andWhere("institutions IS NOT NULL");

        $dql->orderBy("list.orderinlist","ASC");

        $parameters = array(
            'typedef' => 'default',
            'typeadd' => 'user-added'
        );

        $query = $dql->getQuery();

        $query->setParameters($parameters);

        $charts = $query->getResult();

        $institutions = array();

        foreach($charts as $chart) {
            //echo "#### chart=".$chart."<br>";
            $chartInstitutions = $chart->getInstitutions();
            foreach($chartInstitutions as $chartInstitution) {
                //echo "chartInstitution=".$chartInstitution."<br>";
                $institutions[$chartInstitution->getId()] = $chartInstitution->getNodeNameWithRoot(); //getNodeNameWithParent();
            }
        }

        //dump($institutions);
        //exit('111');

        return $institutions;
    }

    public function getFilterTypes() {
        //get all chart types (Line, Pie, Bar ...) presented in the dashboard charts
        //process.py script: replaced namespace by ::class: ['AppDashboardBundle:ChartList'] by [ChartList::class]
        $repository = $this->em->getRepository(ChartList::class);
        $dql =  $repository->createQueryBuilder("list");
        $dql->select('list');
        $dql->leftJoin("list.chartTypes", "chartTypes");
        $dql->where("list.type = :typedef OR list.type = :typeadd");
        $dql->andWhere("chartTypes IS NOT NULL");

        $dql->orderBy("list.orderinlist","ASC");

        $parameters = array(
            'typedef' => 'default',
            'typeadd' => 'user-added'
        );

        $query = $dql->getQuery();

        $query->setParameters($parameters);

        $charts = $query->getResult();

        $chartTypes = array();

        foreach($charts as $chart) {
            $types = $chart->getChartTypes();
            foreach($types as $type) {
                $chartTypes[$type->getId()] = $type->getName();
            }
        }

        return $chartTypes;
    }

    public function getFilterFavorites_orig() {
        $charts = $this->getFavorites();

        $chartArr = array();
        $chartIdArr = array();

        foreach($charts as $chart) {
            //$chartArr[$chart->getName()] = $chart->getAbbreviation();
            $chartArr[$chart->getId()] = $chart->getName();
            $chartIdArr[] = $chart->getId();
        }

        //array merge
        if( count($chartIdArr) > 1 ) {
            $chartAllArr = array();
            $chartIds = implode('-',$chartIdArr);
            $chartAllArr['all-favorites-'.$chartIds] = 'All';
            //$chartAllArr[$chartIds] = 'All';
            $chartArr = $chartAllArr + $chartArr;
        }

        return $chartArr;
    }
    public function getFilterFavorites() {
        $charts = $this->getFavorites();

        $chartArr = array();
        $chartIdArr = array();

        if( count($charts) > 0 ) {
            $chartArr['all'] = 'All';
        }

        foreach($charts as $chart) {
            //$chartArr[$chart->getName()] = $chart->getAbbreviation();
            $chartArr[$chart->getId()] = $chart->getName();
            $chartIdArr[] = $chart->getId();
        }

        return $chartArr;
    }

    public function getFavorites($user=null) {

        if( !$this->security->isGranted('ROLE_DASHBOARD_USER') ) {
            return array();
        }

        if( !$user ) {
            $user = $this->security->getUser();
        }

        //get charts with this user in favoriteUsers
        //process.py script: replaced namespace by ::class: ['AppDashboardBundle:ChartList'] by [ChartList::class]
        $repository = $this->em->getRepository(ChartList::class);
        $dql =  $repository->createQueryBuilder("list");
        $dql->select('list');

        $dql->leftJoin("list.favoriteUsers", "favoriteUsers");

        $dql->where("list.type = :typedef OR list.type = :typeadd");
        $dql->andWhere("favoriteUsers.id = :userId");

        $parameters = array(
            'typedef' => 'default',
            'typeadd' => 'user-added',
            'userId' => $user->getId()
        );

        $dql->orderBy("list.orderinlist","ASC");

        $query = $dql->getQuery();

        $query->setParameters($parameters);

        $charts = $query->getResult();

        return $charts;
    }

    public function getChartByPartialName( $partialName ) {
        //process.py script: replaced namespace by ::class: ['AppDashboardBundle:ChartList'] by [ChartList::class]
        $repository = $this->em->getRepository(ChartList::class);
        $dql =  $repository->createQueryBuilder("list");
        $dql->select('list');

        $dql->where("list.type = :typedef OR list.type = :typeadd");

        $parameters = array(
            'typedef' => 'default',
            'typeadd' => 'user-added'
        );

        $dql->orderBy("list.orderinlist","ASC");
        //$dql->orderBy("list.id","ASC");

        $dql->andWhere("list.name LIKE :partialName");
        $parameters['partialName'] = "%".$partialName."%";

        $query = $dql->getQuery();

        $query->setParameters($parameters);

        $query->setMaxResults(1);

        $chart = $query->getResult();

        return $chart[0];
    }

    public function getPublicChartTypes( $asObject=false ) {
        //process.py script: replaced namespace by ::class: ['AppDashboardBundle:ChartList'] by [ChartList::class]
        $repository = $this->em->getRepository(ChartList::class);
        $dql =  $repository->createQueryBuilder("list");
        $dql->select('list');

        $dql->leftJoin('list.topics','topics');

        $dql->where("list.type = :typedef OR list.type = :typeadd");

        $dql->andWhere("topics.publicAccess = TRUE");

        $parameters = array(
            'typedef' => 'default',
            'typeadd' => 'user-added'
        );

        $dql->orderBy("list.orderinlist","ASC");

        $query = $dql->getQuery();

        $query->setParameters($parameters);

        $charts = $query->getResult();

        if( $asObject ) {
            return $charts;
        }

        $chartArr = array();

        foreach($charts as $chart) {
            $chartArr[$chart->getName()] = $chart->getAbbreviation();
        }

        return $chartArr;
    }

    public function isChartPublic( $chart ) {
        //process.py script: replaced namespace by ::class: ['AppDashboardBundle:ChartList'] by [ChartList::class]
        $repository = $this->em->getRepository(ChartList::class);
        $dql =  $repository->createQueryBuilder("list");
        $dql->select('list');

        $dql->leftJoin('list.topics','topics');

        $dql->where("list.type = :typedef OR list.type = :typeadd");

        $dql->andWhere("topics.publicAccess = TRUE");

        $dql->andWhere("list.id = :chartId");

        $parameters = array(
            'typedef' => 'default',
            'typeadd' => 'user-added',
            'chartId' => $chart->getId()
        );

        $dql->orderBy("list.orderinlist","ASC");

        $query = $dql->getQuery();

        $query->setParameters($parameters);

        $charts = $query->getResult();

        if( count($charts) > 0 ) {
            return true;
        }

        return false;
    }

    public function getChartTypes( $asObject=false ) {
        //get chart types from DB ChartList
//        $charts = $this->em->getRepository('AppDashboardBundle:ChartList')->findBy(
//            array(
//                'type' => array("default","user-added")
//            ),
//            array('orderinlist' => 'ASC')
//        );

        //process.py script: replaced namespace by ::class: ['AppDashboardBundle:ChartList'] by [ChartList::class]
        $repository = $this->em->getRepository(ChartList::class);
        $dql =  $repository->createQueryBuilder("list");
        $dql->select('list');

        $dql->where("list.type = :typedef OR list.type = :typeadd");

        $parameters = array(
            'typedef' => 'default',
            'typeadd' => 'user-added'
        );

        $dql->orderBy("list.orderinlist","ASC");
        //$dql->orderBy("list.id","ASC");

        $query = $dql->getQuery();

        $query->setParameters($parameters);

        $charts = $query->getResult();

        if( $asObject ) {
            return $charts;
        }

        $chartArr = array();

        foreach($charts as $chart) {
            $chartArr[$chart->getName()] = $chart->getAbbreviation();
        }

        if( count($chartArr) == 0 ) {
            $chartArr = $this->getChartTypesInit();
        }

        return $chartArr;
    }

    public function getChartTypesInit() {

        //Add project specialty types according to the enabled specialties.
        //Replace [[projectSpecialties]] by $transresUtil->getAllowedProjectSpecialty($user)
        $projectSpecialtiesStr = "AP/CP, Hematopathology, MISI and COVID-19";
        if( 1 ) {
            $transresUtil = $this->container->get('transres_util');
            $projectSpecialtyAllowedArr = $transresUtil->getTransResProjectSpecialties();
            $projectSpecialtiesStr = "";
            foreach($projectSpecialtyAllowedArr as $projectSpecialty) {
                $projectSpecialtiesStr != "" && $projectSpecialtiesStr .= ", ";
                $projectSpecialtiesStr .= $projectSpecialty->getUppercaseFullName();
            }
        }

        $chartTypes = array(
            //PI/Project statistics
            "1. Principal Investigators by Affiliation (Linked)" =>                   "pi-by-affiliation",
            "2. Total Number of Projects per PI (Top) (linked)" =>                    "projects-per-pi",
            "3. Total Number of Funded Projects per PI (Top) (linked)" =>             "funded-projects-per-pi",
            "4. Total Number of Non-Funded Projects per PI (Top) (linked)" =>         "nonfunded-projects-per-pi",
            //Pathologists Involved and number of projects
            "5. Total Number of Projects per Pathologist Involved (Top)" =>             "projects-per-pathologist-involved",
            "6. Total Number of Funded Projects per Pathologist Involved (Top)" =>      "funded-projects-per-pathologist-involved",
            "7. Total Number of Non-Funded Projects per Pathologist Involved (Top)" =>  "nonfunded-projects-per-pathologist-involved",
            //Work request statistics
            "8. Total Number of Work Requests by Funding Source (linked)" =>     "requests-by-funding-source",
            "9. Projects with Most Work Requests (Top) (linked)" =>              "requests-per-project",
            "10. Funded Projects with Most Work Requests (Top) (linked)" =>      "requests-per-funded-projects",
            "11. Non-Funded Projects with Most Work Requests (Top) (linked)" =>  "requests-per-nonfunded-projects",
            //   Products/Services
            "12. Service Productivity by Products/Services (Top)" =>     "service-productivity-by-service",
            "13. Service Productivity for Funded Projects (Top)" =>      "service-productivity-by-service-per-funded-projects",
            "14. Service Productivity for Non-Funded Projects (Top)" =>  "service-productivity-by-service-per-nonfunded-projects",
            "15. Service Productivity: Items for Funded vs Non-Funded Projects" => "service-productivity-by-service-compare-funded-vs-nonfunded-projects",
            //Productivity statistics based on work requests
            "16. Total Fees of Items Ordered for Funded vs Non-Funded Projects" => "fees-by-requests",
            "17. Funded Projects with the Highest Total Fees (Top)" =>          "fees-by-requests-per-funded-projects",
            "18. Non-Funded Projects with the Highest Total Fees (Top)" =>      "fees-by-requests-per-nonfunded-projects",
            "19. Total Fees per Investigator (Top)" =>                          "fees-by-investigators",
            "20. Total Fees per Investigator for Funded Projects (Top)" =>      "fees-by-investigators-per-funded-projects",
            "21. Total Fees per Investigator for Non-Funded Projects (Top)"=>   "fees-by-investigators-per-nonfunded-projects",
            //Financial statistics based on invoices
            "22. Paid Invoices by Month" =>                                 "fees-by-invoices-paid-per-month",
            "23. Generated Invoices for Funded Projects" =>                 "fees-by-invoices-per-funded-projects",
            "24. Generated Invoices for Non-Funded Projects (Top)" =>       "fees-by-invoices-per-nonfunded-projects",
            "25. Total Invoiced Amounts by PI (Top)" =>                     "fees-by-invoices-per-pi",
            //Pathologists Involved and number of projects
            "26. Total Invoiced Amounts for Projects per Pathologist Involved (Top)" =>             "fees-by-invoices-per-projects-per-pathologist-involved",
            "27. Total Invoiced Amounts for Funded Projects per Pathologist Involved (Top)" =>      "fees-by-invoices-per-funded-projects-per-pathologist-involved",
            "28. Total Invoiced Amounts for Non-Funded Projects per Pathologist Involved (Top)" =>  "fees-by-invoices-per-nonfunded-projects-per-pathologist-involved",
            "29. Total Fees per Involved Pathologist for Non-Funded Projects (Top)" =>              "fees-per-nonfunded-projects-per-pathologist-involved",

            "30. Total Number of Projects per Type (linked)" =>         "projects-per-type",
            "31. Total Number of Work Requests per Business Purpose" => "requests-per-business-purpose",

            "32. Turn-around Statistics: Average number of days to complete a Work Request (based on 'Completed and Notified' requests)" => "turn-around-statistics-days-complete-request",
            "33. Turn-around Statistics: Number of days to complete each Work Request (based on 'Completed and Notified' requests) (linked)" => "turn-around-statistics-days-complete-per-request",
            "34. Turn-around Statistics: Number of days to complete each Work Request by products/services (based on 'Completed and Notified' requests)" => "turn-around-statistics-days-complete-per-request-with-product",
            "35. Turn-around Statistics: Average number of days for each project request approval phase (linked)" => "turn-around-statistics-days-project-state",
            "36. Turn-around Statistics: Number of days for each project request’s approval phase (linked)" => "turn-around-statistics-days-per-project-state",
            "37. Turn-around Statistics: Average number of days for invoices to be paid (based on fully and partially paid invoices) (linked)" => "turn-around-statistics-days-paid-invoice",
            "38. Turn-around Statistics: Number of days each paid and partially paid invoice took to get paid (linked)" => "turn-around-statistics-days-per-paid-invoice",
            "39. Turn-around Statistics: Top PIs with most delayed unpaid invoices (linked)" => "turn-around-statistics-pis-with-delayed-unpaid-invoices",
            "40. Turn-around Statistics: Top PIs with highest total amounts in unpaid, overdue invoices (linked)" => "turn-around-statistics-pis-with-highest-total-unpaid-invoices",
            "41. Turn-around Statistics: Top PIs by index (delay in months * invoiced amount) for unpaid, overdue invoices (linked)" => "turn-around-statistics-pis-combining-total-delayed-unpaid-invoices",

            "42. Total Number of Individual PIs involved in $projectSpecialtiesStr Projects (linked)" => "compare-projectspecialty-pis",
            "43. Total Number of $projectSpecialtiesStr Project Requests (linked)" => "compare-projectspecialty-projects",
            "44. Total Number of $projectSpecialtiesStr Project Requests By Month (linked)" => "compare-projectspecialty-projects-stack",
            //"44original. Total Number of AP/CP, Hematopathology and COVID-19 Project Requests By Month (linked)"=>"compare-projectspecialty-projects-stack_original",
            "45. Total Number of $projectSpecialtiesStr Work Requests By Month (linked)" => "compare-projectspecialty-requests",
            //"45new. Total Number of AP/CP, Hematopathology and COVID-19 Work Requests By Month (linked)" => "compare-projectspecialty-requests_new",
            //"45original. original Total Number of AP/CP, Hematopathology and COVID-19 Work Requests By Month (linked)" => "compare-projectspecialty-requests_original",
            "46. Total Number of $projectSpecialtiesStr Invoices By Month (linked)" => "compare-projectspecialty-invoices",
            //"46original. Total Number of AP/CP, Hematopathology and COVID-19 Invoices By Month (linked)"=>"compare-projectspecialty-invoices_original",

            "47. Total Fees per Project Request Type (linked)" => "projects-fees-per-type",
            "48. Total Fees per Project Request Type of Funded Projects (linked)" => "projects-funded-fees-per-type",
            "49. Total Fees per Project Request Type of Non-Funded Projects (linked)" => "projects-unfunded-fees-per-type",

            "50. Total Fees per Work Request Business Purpose" => "requests-fees-per-business-purpose",
            "51. Total Fees per Work Request Business Purpose for Funded Projects" => "requests-funded-fees-per-business-purpose",
            "52. Total Fees per Work Request Business Purpose for Non-Funded Projects" => "requests-unfunded-fees-per-business-purpose",

            "53. Turn-around Statistics: Number of Days each 'Completed and Notified' Work Request took with the Name of who marked it as completed" => "turn-around-statistics-days-complete-per-request-with-user",
            "54. Turn-around Statistics: Top most delinquent invoices (linked)" => "turn-around-statistics-delayed-unpaid-invoices-by-days",

            "55. Number of reminder emails sent per month (linked)" => "reminder-emails-per-month",

            "56. Number of successful log in events for the TRP site per month" => "successful-logins-trp",
            "57. Number of successful log in events per site per month" => "successful-logins-site",
            "58. Number of unique users in a given month who successfully log in, per site" => "successful-unique-logins-site-month",
            "59. Number of unique users in a given week who successfully log in, per site" => "successful-unique-logins-site-week",

            //"60. PIs with most projects" => "pis-with-most-projects",
            //"61. PIs with highest expenditures" => "pis-with-highest-expenditures",

            "60. Number of fellowship applicants by year" => "fellapp-number-applicant-by-year",
            //"61-OLD. Average sum of the USMLE and COMLEX scores for fellowship applicant by year" => "fellapp-average-usmle-scores-by-year-OLD",
            "61. Average USMLE and COMLEX scores for fellowship applicants by year" => "fellapp-average-usmle-scores-by-year",

            "62. New and Edited Call Log Entries Per Week" => "new-and-edited-calllog-entries-per-day",
            "63. Patients with Call Log Entries Per Week" => "patients-calllog-per-day",

            "64. Total amount of paid/due for issued invoices per month" => "total-amount-paid-unpaid-invoices-per-month",
            "65. Total amount of paid/due for issued invoices per fiscal year" => "total-amount-paid-unpaid-invoices-per-year",

            "66. Chart viewing stats per month" => "chart-view-stat",

            "67. Scheduled residency and fellowship interviews by interviewer" => "fellapp-resapp-interviews",
            "69. Scheduled residency interviews by interviewer" => "resapp-interviews",
            "70. Scheduled fellowship interviews by interviewer" => "fellapp-interviews",

            "68. Total candidate interview feedback comments provided via the system, by interviewer" => "fellapp-resapp-interviews-feedback",
            "71. Residency interview feedback comments provided via the system, by interviewer" => "resapp-interviews-feedback",
            "72. Fellowship interview feedback comments provided via the system, by interviewer" => "fellapp-interviews-feedback",
            "73. Country of origin for the fellowship applicants" => "fellapp-country-origin",

            "" => "",
            "" => "",
        );
        return $chartTypes;
    }
    public function getChartTypeByValue($value) {
        $key = array_search($value, $this->getChartTypes());
        return $key;
    }

    //get charts by topic and all children topic's charts
    public function getChartsByTopic_ORIG( $topic, $children=false ) {
        //echo "getChartsByTopic: topic=".$topic."<br>";
        //exit("topic=".$topic);

        //process.py script: replaced namespace by ::class: ['AppDashboardBundle:ChartList'] by [ChartList::class]
        $repository = $this->em->getRepository(ChartList::class);
        $dql =  $repository->createQueryBuilder("list");
        $dql->leftJoin('list.topics','topics');

        $dql->where("list.type = :typedef OR list.type = :typeadd");

        //$dql->andWhere("topics = :topicId");
        $dql->andWhere("topics = :topicId");

//        if( $public == true ) {
//            $dql->andWhere("topics.publicAccess = TRUE");
//        }

        $dql->orderBy("list.orderinlist","ASC");

        $parameters = array(
            'typedef' => 'default',
            'typeadd' => 'user-added',
            "topicId" => $topic->getId()
        );

        $query = $dql->getQuery();

        $query->setParameters($parameters);

        $charts = $query->getResult();
        //echo "charts count=".count($charts)."<br>";

        return $charts;
    }
    //get charts by topic and all children topic's charts
    public function getChartsByTopic( $topic, $children=false ) {
        //echo "getChartsByTopic: topic=".$topic."<br>";

        $topicsArr = array();

        if( $children ) {
            $topicNodes = $topic->printTreeSelectList(array(), 'getId');
            //dump($topicNodes);exit('111');
            foreach ($topicNodes as $topicNodeId => $topicName) {
                $topicsArr[] = $topicNodeId;
            }
        } else {
            $topicsArr[] = $topic->getId();
        }
        //dump($topicsArr);exit('111');
        
        //process.py script: replaced namespace by ::class: ['AppDashboardBundle:ChartList'] by [ChartList::class]
        $repository = $this->em->getRepository(ChartList::class);
        $dql =  $repository->createQueryBuilder("list");
        $dql->leftJoin('list.topics','topics');

        $dql->where("list.type = :typedef OR list.type = :typeadd");

        //$dql->andWhere("topics = :topicId");
        $dql->andWhere("topics IN (:topicIdsArr)");

//        if( $public == true ) {
//            $dql->andWhere("topics.publicAccess = TRUE");
//        }

        $dql->orderBy("list.orderinlist","ASC");

        $parameters = array(
            'typedef' => 'default',
            'typeadd' => 'user-added',
            "topicIdsArr" => $topicsArr
        );

        $query = $dql->getQuery();

        $query->setParameters($parameters);

        $charts = $query->getResult();
        //dump($charts);
        //echo "charts count=".count($charts)."<br>";

        return $charts;
    }

    public function getChartsByInstitution( $institution ) {

        //echo "getChartsByInstitution: institution=".$institution."<br>";
        //exit("institution=".$institution);

        //process.py script: replaced namespace by ::class: ['AppDashboardBundle:ChartList'] by [ChartList::class]
        $repository = $this->em->getRepository(ChartList::class);
        $dql =  $repository->createQueryBuilder("list");
        $dql->leftJoin('list.institutions','institutions');

        $dql->where("list.type = :typedef OR list.type = :typeadd");
        $dql->andWhere("institutions.id = :institutionId");

        $dql->orderBy("list.orderinlist","ASC");

        $parameters = array(
            'typedef' => 'default',
            'typeadd' => 'user-added',
            "institutionId" => $institution->getId()
        );

        $query = $dql->getQuery();

        $query->setParameters($parameters);

        $charts = $query->getResult();
        //echo "charts count=".count($charts)."<br>";

        return $charts;
    }

    public function getChartsByChartType( $chartType, $single=false ) {

        //echo "getChartsByChartType: chartType=".$chartType."<br>";
        //exit("chartType=".$chartType);

        //process.py script: replaced namespace by ::class: ['AppDashboardBundle:ChartList'] by [ChartList::class]
        $repository = $this->em->getRepository(ChartList::class);
        $dql =  $repository->createQueryBuilder("list");
        $dql->leftJoin('list.chartTypes','chartTypes');

        $dql->where("list.type = :typedef OR list.type = :typeadd");
        $dql->andWhere("chartTypes.id = :chartTypeId");

        $dql->orderBy("list.orderinlist","ASC");

        $parameters = array(
            'typedef' => 'default',
            'typeadd' => 'user-added',
            "chartTypeId" => $chartType->getId()
        );

        $query = $dql->getQuery();

        $query->setParameters($parameters);

        $charts = $query->getResult();
        //echo "charts count=".count($charts)."<br>";

        if( $single ) {
            if( count($charts) > 0 ) {
                return $charts[0];
            }
        }

        return $charts;
    }

    /////////////////////// methods ////////////////////////////
    public function isUserBelongsToInstitution($user, $parentInstitution) {
        if( !$parentInstitution ) {
            return false;
        }

        //get all user's institutions
        $institutions = $user->getInstitutions();

        foreach($institutions as $institution) {
            //echo $user.": parentNode:".$parentInstitution."(".$parentInstitution->getId().") and node:".$institution."(".$institution->getId().") are the same? <br>";
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:Institution'] by [Institution::class]
            if( $this->em->getRepository(Institution::class)->isNodeUnderParentnode($parentInstitution,$institution) ) {
                //echo $user.": isUserBelongsToInstitution Yes! <br>";
                return true;
            }
        }

        return false;
    }

    public function getNumberFormat($number,$digits=null) {
        //$number = 123456789;
        return number_format($number,$digits);
    }

    public function getOtherStr( $showLimited, $otherPrefix ) {
        if( $showLimited ) {
            return false; //show top ten only without others
        }
        return $otherPrefix;
    }

    //select top 10 ($limit), BUT make sure the other PIs are still shown as "Other"
    public function getTopArray($piProjectCountArr, $showOthers=false, $limit=10, $descriptionArr=array(), $maxLen=50) {
        arsort($piProjectCountArr);

        //$limit = 10;
        //$limit = $this->quantityLimit;
        //$limit = 3;

        if( $limit == "Show all" ) {
            $limit = null;
        }

        //$showOthers = true;
        //$otherId = "All other $showOthers combined";
        $otherId = str_replace("[[otherStr]]",$showOthers,$this->otherId);

        $otherIndexArr = array();
        $totalOtherValue = 0;
        $count = 1;
        $piProjectCountTopArr = array();
        foreach($piProjectCountArr as $username=>$value) {
            //$value = $this->getNumberFormat($value);
            //echo $username.": ".$count."<br>";
            if( $count <= $limit || !$limit ) {
                if( $value && $value != 0 ) {
                    //echo "add value=".$value."<br>";
                    $piProjectCountTopArr[$username] = $value;
                }
            } else {
                if( $showOthers !== false ) { //show others
                    //echo "show Others <br>";
                    if( is_array($value) ) {
                        //echo "1valueArr=".$value['value']."<br>";
                        $value = $value['value'];
                    } else {
                        //echo "1value=".$value."<br>";
                        //$value = $piProjectCountTopArr[$otherId] + $value;
                    }
                    //echo "Original value=".$value."<br>";
                    $totalOtherValue = $totalOtherValue + $value;
                    if (isset($piProjectCountTopArr[$otherId])) {
                        $value = $piProjectCountTopArr[$otherId] + intval($value);
//                        if( is_array($value) ) {
//                            echo "1valueArr=".$value['value']."<br>";
//                            $value = intval($piProjectCountTopArr[$otherId]) + intval($value['value']);
//                        } else {
//                            echo "1value=".$value."<br>";
//                            $value = $piProjectCountTopArr[$otherId] + $value;
//                        }
                    } else {
                        //$value = 1;
                    }
                    //echo "res value=".$value."<br>";

                    $piProjectCountTopArr[$otherId] = $value;

                    //add value to description array with index "other"
                    $otherIndexArr[$username] = $piProjectCountTopArr[$otherId];

                    //echo "add value=".$value."<br>";
                    //if( is_array($value) ) {
                    //$totalValue = $totalValue + $value['value'];
                    //echo "2valueArr=".$value['value']."<br>";
                    //} else {
                    //echo "2value=".$value."<br>";
                    //}

                }//if show others
            }
            $count++;
        }

        if( $maxLen ) {
            $piProjectCountTopShortArr = array();
            foreach($piProjectCountTopArr as $index=>$value) {
                $index = $this->tokenTruncate($index,$maxLen);

                $descr = array();
                foreach($descriptionArr as $descriptionSingleArr) {
                    $descrPrefix = $descriptionSingleArr['descrPrefix'];
                    $descrPostfix = $descriptionSingleArr['descrPostfix'];
                    $valuePrefix = $descriptionSingleArr['valuePrefix'];
                    $valuePostfix = $descriptionSingleArr['valuePostfix'];
                    $descrColor = $descriptionSingleArr['descrColor'];
                    $descrType = $descriptionSingleArr['descrType'];
                    $descrValueArr = $descriptionSingleArr['descrValueArr'];

                    if( array_key_exists($index,$descrValueArr) ) {
                        $descrValue = $descrValueArr[$index];
                    }

                    if( $index == $otherId ) {
                        $descrValue = 0;
                        //$valueTotal = 0;
                        foreach($otherIndexArr as $username=>$thisValue) {
                            if( $thisValue && array_key_exists($username,$descrValueArr) ) {
                                $descrValue = $descrValue + $descrValueArr[$username];

//                                if( is_array($value) ) {
//                                    $valueTotal = $valueTotal + $value['value'];
//                                } else {
//                                    $valueTotal = $valueTotal + $value;
//                                }
                            }
                        }
                        //echo "descrValue=$descrValue <br>";
                        //echo "valueTotal=$valueTotal <br>";
                        //echo "totalOtherValue=$totalOtherValue <br>";

                        if( is_array($value) ) {
                            $value['value'] = $totalOtherValue; //$valueTotal;
                        } else {
                            $value = $totalOtherValue; //$valueTotal;
                        }
                    }//$index == $otherId

                    if( $descrType == "money" ) {
                        $descrValue = $this->getNumberFormat($descrValue);
                    }
                    if( $descrValue ) {
                        if( $descrColor ) {
                            $descr[] = '<span style="color:'.$descrColor.'">' . $descrPrefix . $descrValue . $descrPostfix . '</span>';
                        } else {
                            $descr[] = $descrPrefix . $descrValue;
                        }
                    }
                }//foreach

                if( count($descr) > 0 ) {
                    if( is_array($value) ) {
                        $valueLabel = $value['value'];
                    } else {
                        $valueLabel = $value;
                    }
                    if( strpos((string)$valuePrefix,'$') !== false ) {
                        $valueLabel = $this->getNumberFormat($valueLabel);
                    } else {
                        $valueLabel = $valueLabel;
                    }

                    //$valueLabel = $valueLabel . ": " . $value; //testing

                    $index = $index . " " . $valuePrefix . $valueLabel . $valuePostfix . " (" . implode(", ",$descr) . ")";
                }

                $piProjectCountTopShortArr[$index] = $value;
            }//foreach

//            echo "<pre>";
//            print_r($piProjectCountTopShortArr);
//            echo "</pre>";
//            exit('111');

            return $piProjectCountTopShortArr;
        }//if

        return $piProjectCountTopArr;
    }
    public function getTopMultiArray($piProjectCountArr, $showOthers=false, $limit=10, $descriptionArr=array(), $maxLen=50) {
        //arsort($piProjectCountArr);
        usort($piProjectCountArr, function($a, $b) {
            return $b['value'] - $a['value'];
        });

//        echo "<pre>";
//        print_r($piProjectCountArr);
//        echo "</pre>";

        //$limit = 10;
        //$limit = 3;
        //$showOthers = true;
        //if( !$showOthers ) {
        //“Show only the top 10” - if it is checked, show only the top ten projects, if it is not checked, show the top 100
        //$limit = 20;
        //}

        if( $limit == "Show all" ) {
            $limit = null;
        }

        //$otherId = "All other $showOthers combined";
        $otherId = str_replace("[[otherStr]]",$showOthers,$this->otherId);

        $otherObjectids = array();

        $count = 1;
        $piProjectCountTopArr = array();
        foreach($piProjectCountArr as $id=>$arr) {
            $value = $arr['value'];
            $label = $arr['label'];
            $objectid = $arr['objectid'];
            $pi = $arr['pi'];

            $showPath = null;
            $link = null;
            if( isset($arr['show-path']) ) {
                $showPath = $arr['show-path'];
            }
            if( isset($arr['link']) ) {
                $link = $arr['link'];
            }

            //echo "value=".$value."<br>";
            //echo $username.": ".$count."<br>";
            if( $value && $value != 0 ) {
                if ($count <= $limit || !$limit) {
                    $piProjectCountTopArr[$id]['value'] = $value;
                    $piProjectCountTopArr[$id]['label'] = $label;
                    $piProjectCountTopArr[$id]['show-path'] = $showPath;
                    $piProjectCountTopArr[$id]['link'] = $link;
                    $piProjectCountTopArr[$id]['objectid'] = $objectid;
                    $piProjectCountTopArr[$id]['pi'] = $pi;
                } else {
                    if( $showOthers !== false ) {
                        //echo "show Others <br>";
                        if (isset($piProjectCountTopArr[$otherId]) && isset($piProjectCountTopArr[$otherId]['value'])) {
                            $thisValue = $piProjectCountTopArr[$otherId]['value'] + $value;
                        } else {
                            $thisValue = $value;
                        }
                        //echo $label.": ".$value."=>".$thisValue."<br>";
                        $piProjectCountTopArr[$otherId]['value'] = $thisValue;
                        $piProjectCountTopArr[$otherId]['label'] = $otherId;
                        $piProjectCountTopArr[$otherId]['show-path'] = $showPath;
                        $piProjectCountTopArr[$otherId]['link'] = $link;
                        $piProjectCountTopArr[$otherId]['objectid'] = null;
                        $piProjectCountTopArr[$otherId]['pi'] = $pi;
                        $otherObjectids[] = $objectid;
                    }
                }
            }
            $count++;
        }

        if( $showOthers ) {
            $piProjectCountTopArr[$otherId]['objectid'] = $otherObjectids;
        }

        if( $maxLen ) {
            $piProjectCountTopShortArr = array();
            foreach($piProjectCountTopArr as $id=>$arr) {

                $value = null;
                if( array_key_exists("value",$arr) ) {
                    $value = $arr['value'];
                } else {
                    continue;
                }

//                $label = null;
//                if( array_key_exists("label",$arr) ) {
//                    $label = $arr['label'];
//                }
//
//                $showPath = null;
//                if( array_key_exists("show-path",$arr) ) {
//                    $showPath = $arr['show-path'];
//                }

                $label = $arr['label'];
                $showPath = $arr['show-path'];
                $link = $arr['link'];
                $pi = $arr['pi'];
                $objectid = $arr['objectid'];
                //echo "objectid=".$objectid."<br>";
                $label = $this->tokenTruncate($label,$maxLen);
                $piProjectCountTopShortArr[$id]['value'] = $value;
                $piProjectCountTopShortArr[$id]['label'] = $label;
                $piProjectCountTopShortArr[$id]['show-path'] = $showPath;
                $piProjectCountTopShortArr[$id]['link'] = $link;
                $piProjectCountTopShortArr[$id]['objectid'] = $objectid;
                $piProjectCountTopShortArr[$id]['pi'] = $pi;
            }
            return $piProjectCountTopShortArr;
        }

        return $piProjectCountTopArr;
    }
    public function tokenTruncate($string, $your_desired_width) {
        $parts = preg_split('/([\s\n\r]+)/', $string, -1, PREG_SPLIT_DELIM_CAPTURE);
        $parts_count = count($parts);

        $postfix = null;
        $length = 0;
        $last_part = 0;
        for (; $last_part < $parts_count; ++$last_part) {
            $length += strlen((string)$parts[$last_part]);
            if ($length > $your_desired_width) {
                $postfix = "...";
                break;
            }
        }

        $res = implode(array_slice($parts, 0, $last_part));
        $res = trim((string)$res) . $postfix;
        //$res = $res . $postfix;
        //echo "res=[".$res."]<br>";

        return $res;    //implode(array_slice($parts, 0, $last_part)).$postfix;
    }

    //NOT USED
    public function addValueToOther($arrTop,$prefix=": $",$arrayValueKey='value') {
        $newArrTop = array();
        foreach($arrTop as $label => $value) {
            if( strpos((string)$label, $this->otherSearchStr) !== false ) {
                if( is_array($value) ) {
                    print_r($value);
                    //exit('111');
                    $value = $value[$arrayValueKey];
                    //echo "value(other array)=$value <br>";
                    $label = $label . $prefix . $this->getNumberFormat($value);
                } else {
                    //echo "value(other regular)=$value <br>";
                    $label = $label . $prefix . $this->getNumberFormat($value);
                }
                //$label = $label . $prefix . $value;
                $newArrTop[$label] = $value;
            } else {
//                if( is_array($value) ) {
//                    echo "value(regular)=$value[$arrayValueKey] <br>";
//                } else {
//                    echo "value(regular)=$value <br>";
//                }
                $newArrTop[$label] = $value;
            }
        }//foreach
        exit('111');
        return $newArrTop;
    }

    public function adjustBrightness($hex, $steps) {
        // Steps should be between -255 and 255. Negative = darker, positive = lighter
        $steps = max(-255, min(255, $steps));

        // Normalize into a six character long hex string
        $hex = str_replace('#', '', $hex);
        if (strlen((string)$hex) == 3) {
            $hex = str_repeat(substr((string)$hex,0,1), 2).str_repeat(substr((string)$hex,1,1), 2).str_repeat(substr((string)$hex,2,1), 2);
        }

        // Split into three parts: R, G and B
        $color_parts = str_split($hex, 2);
        $return = '#';

        foreach ($color_parts as $color) {
            $color   = hexdec($color); // Convert to decimal
            $color   = max(0,min(255,$color + $steps)); // Adjust color
            $return .= str_pad(dechex($color), 2, '0', STR_PAD_LEFT); // Make two char hex code
        }

        return $return;
    }

    public function getChart( $dataArr,
                              $title,
                              $type='pie',
                              $layoutArray=null,
                              $valuePrefixLabel=null,
                              $valuePostfixLabel=null,
                              $descriptionArr=null,
                              $hoverinfo=null
    ) {

        if( count($dataArr) == 0 ) {
            return array();
        }

        $labels = array();
        $values = array();
        $links = array();
        //$text = array();

        if( !$layoutArray ) {
            $layoutArray = array(
                'height' => $this->height,
                'width' => $this->width,
            );
        }

        if( $title ) {
            $layoutArray['title'] = $title;
        }

        foreach( $dataArr as $label => $valueData ) {
            $origLabel = $label;
            if( is_array($valueData) ) {
                $value = $valueData["value"];
                $link = $valueData["link"];
            } else {
                $value = $valueData;
                $link = null;
            }
            //value
            if ($type == "bar" || ($value && $value != 0)) {
                if( ($valuePrefixLabel || $valuePostfixLabel) && $value ) {
                    if (strpos((string)$valuePrefixLabel, '$') !== false) {
                        $label = $label . " " . $valuePrefixLabel . $this->getNumberFormat($value) . $valuePostfixLabel;
                    } else {
                        $label = $label . " " . $valuePrefixLabel . $value . $valuePostfixLabel;
                    }
                    //echo "value=$value<br>";
                }

                if( $descriptionArr ) {
                    if( isset($descriptionArr[$origLabel]) ) {
                        $label = $label . $descriptionArr[$origLabel];
                    }
                }

                $labels[] = $label;
                $values[] = $value;
                //$text[] = $value;
                if( $link ) {
                    $links[] = $link;
                }
            }
        }

        if( count($values) == 0 ) {
            return array();
            //return array('error'=>"No data found corresponding to this chart parameters");
        }

        $xAxis = "labels";
        $yAxis = "values";
        if( $type == "bar" || $type == "stack" ) {
            $xAxis = "x";
            $yAxis = "y";
        }

        $chartDataArray = array();
        $chartDataArray[$xAxis] = $labels;
        $chartDataArray[$yAxis] = $values;
        $chartDataArray['type'] = $type;
        $chartDataArray["links"] = $links;

        //color array for bars
//        if( $type == "bar" || $type == "stack" ) {
//            //$chartDataArray['marker']['color'] = array('rgb(142,124,195)','red','green');
//            $colors = array();
//            $initColor = "#3366CC";
//            $step = 100/count($values);
//            $count = 0;
//            foreach($values as $value) {
//                if($value) {
//                    $colors[] = $this->adjustBrightness($initColor,$count);
//                    $count = $count + 10;;
//                } else {
//                    $colors[] = 'white';
//                }
//            }
//
//            $chartDataArray['marker'] = array('color'=>$colors);    //['color'] = array('rgb(142,124,195)','red','green');
//        }

        //$chartDataArray["text"] = "111";
        $chartDataArray["textinfo"] = "value+percent";
        //hoverinfo: label+text+value+percent
        $chartDataArray["outsidetextfont"] = array('size'=>1,'color'=>'white');
        $chartDataArray['direction'] = 'clockwise';
        //$chartDataArray["hoverinfo"] = "percent+label";
        $chartDataArray["hoverinfo"] = $hoverinfo;

        $dataArray[] = $chartDataArray;

        //$chartsArray['layout'] = $layoutArray;
        //$chartsArray['data'] = $dataArray;

//        echo "<pre>";
//        print_r($dataArray);
//        echo "</pre>";

        $chartsArray = array(
            'layout' => $layoutArray,
            'data' => $dataArray
        );

        return $chartsArray;
    }
    public function getChartByMultiArray( $dataArr, $filterArr, $title, $type='pie', $layoutArray=null, $valuePrefixLabel=null, $hoverinfo=null ) {

        if( count($dataArr) == 0 ) {
            return array();
        }

        $startDate = $filterArr['startDate'];
        $endDate = $filterArr['endDate'];
        $projectSpecialtyObjects = $filterArr['projectSpecialtyObjects'];
        $funded = $filterArr['funded'];

//        $projectId = null;
//        if( isset($filterArr['funded']) ) {
//            $projectId = $filterArr['projectId'];
//        }

        if( $startDate ) {
            $startDateStr = $startDate->format('m/d/Y');
        }
        if( $endDate ) {
            $endDateStr = $endDate->format('m/d/Y');
        }

//        echo "<pre>";
//        print_r($dataArr);
//        echo "</pre>";

        $labels = array();
        $values = array();
        //$text = array();

        if( !$layoutArray ) {
            $layoutArray = array(
                'height' => $this->height,
                'width' => $this->width,
            );
        }

        if( $title ) {
            $layoutArray['title'] = $title;
        }

        foreach( $dataArr as $id=>$arr ) {
            $value = $arr['value'];
            $label = $arr['label'];
            $objectid = $arr['objectid'];
            $pi = $arr['pi'];

            $showPath = null;
            $link = null;
            if( isset($arr['show-path']) ) {
                $showPath = $arr['show-path'];
            }
            if( isset($arr['link']) ) {
                $link = $arr['link'];
            }

            if( $type == "bar" || ($value && $value != 0) ) {
                if( $valuePrefixLabel && $value ) {
                    if( strpos((string)$valuePrefixLabel,'$') !== false ) {
                        $label = $label . " " . $valuePrefixLabel . $this->getNumberFormat($value);
                    } else {
                        $label = $label . " " . $valuePrefixLabel . $value;
                    }
                }

                if( $showPath == 'project' ) {

                    $linkFilterArr = array(
                        'filter[state][0]' => 'final_approved',
                        'filter[state][1]' => 'closed',
                        'filter[startDate]' => $startDateStr,
                        'filter[endDate]' => $endDateStr,
                        //'filter[]' => $projectSpecialtyObjects
                    );

                    if( $funded === true ) {
                        $linkFilterArr['filter[fundingType]'] = 'Funded';
                    }
                    if( $funded === false ) {
                        $linkFilterArr['filter[fundingType]'] = 'Non-Funded';
                    }

                    if( count($projectSpecialtyObjects) > 0 ) {
                        $projectSpecialtyObject = $projectSpecialtyObjects[0];
                        $linkFilterArr['filter[projectSpecialty][]'] = $projectSpecialtyObject->getId();
                    }

                    if( strpos((string)$id, $this->otherSearchStr) !== false && is_array($objectid) ) {
                        $userIndex = 0;
                        foreach($objectid as $thisObjectid) {
                            $linkFilterArr['filter[principalInvestigators]['.$userIndex.']'] = $thisObjectid;
                            $userIndex++;
                        }
                    } else {
                        $linkFilterArr['filter[principalInvestigators][]'] = $objectid;
                    }

                    $link = $this->container->get('router')->generate(
                        'translationalresearch_project_index',
                        $linkFilterArr,
                        UrlGeneratorInterface::ABSOLUTE_URL
                    );
                    //$linkLabel = "link";
                    //$label = '<font color="red">'.$label.'</font>';
                    //$label = '<a target="_blank" href="'.$link.'">'.$label.'</a>';
                    //$label = $label . " " . $link;
                }

                if( $showPath == 'request' ) {

                    $linkFilterArr = array(
                        //'filter[project]' => $objectid,
                        'filter[projectSearch]' => $objectid, //TODO: optimization search
                        'filter[progressState][0]' => 'active',
                        'filter[progressState][1]' => 'completed',
                        'filter[progressState][2]' => 'completedNotified',
                        'filter[progressState][3]' => 'pendingInvestigatorInput',
                        'filter[progressState][4]' => 'pendingHistology',
                        'filter[progressState][5]' => 'pendingImmunohistochemistry',
                        'filter[progressState][6]' => 'pendingMolecular',
                        'filter[progressState][7]' => 'pendingCaseRetrieval',
                        'filter[progressState][8]' => 'pendingTissueMicroArray',
                        'filter[progressState][9]' => 'pendingSlideScanning',
                        'filter[startDate]' => $startDateStr,
                        'filter[endDate]' => $endDateStr
                    );

                    if( $funded === true ) {
                        $linkFilterArr['filter[fundingType]'] = 'Funded';
                    }
                    if( $funded === false ) {
                        $linkFilterArr['filter[fundingType]'] = 'Non-Funded';
                    }

                    if( count($projectSpecialtyObjects) > 0 ) {
                        $projectSpecialtyObject = $projectSpecialtyObjects[0];
                        $linkFilterArr['filter[projectSpecialty][]'] = $projectSpecialtyObject->getId();
                    }

                    if( strpos((string)$id, $this->otherSearchStr) !== false ) {
                        $linkFilterArr = null;
                    } else {
                        if( is_array($pi) ) {
                            $userIndex = 0;
                            foreach($pi as $thisPi) {
                                $linkFilterArr['filter[principalInvestigators]['.$userIndex.']'] = $thisPi;
                                $userIndex++;
                            }
                        } else {
                            $linkFilterArr['filter[principalInvestigators][]'] = $pi;
                        }
                    }

                    if( $linkFilterArr ) {
                        //echo "### $label<br>";
                        $link = $this->container->get('router')->generate(
                            'translationalresearch_request_index_filter',
                            $linkFilterArr,
                            UrlGeneratorInterface::ABSOLUTE_URL
                        );
                    }
                    //$linkLabel = "link";
                    //$label = '<font color="red">'.$label.'</font>';
                    //$label = '<a target="_blank" href="'.$link.'">'.$label.'</a>';
                    //$label = $label . " " . $link;
                }

                if( $showPath == 'project-type' ) {
                    $linkFilterArr = array(
                        'filter[state][0]' => 'final_approved',
                        'filter[state][1]' => 'closed',
                        'filter[startDate]' => $startDateStr,
                        'filter[endDate]' => $endDateStr,
                        //'filter[]' => $projectSpecialtyObjects,
                        'filter[searchProjectType]' => $objectid
                    );

                    if( count($projectSpecialtyObjects) > 0 ) {
                        $projectSpecialtyObject = $projectSpecialtyObjects[0];
                        $linkFilterArr['filter[projectSpecialty][]'] = $projectSpecialtyObject->getId();
                    }

                    $link = $this->container->get('router')->generate(
                        'translationalresearch_project_index',
                        $linkFilterArr,
                        UrlGeneratorInterface::ABSOLUTE_URL
                    );
                }

                $labels[] = $label;
                $values[] = $value;
                $links[] = $link;
                //$text[] = $value;
            }//if bar or value

        }//foreach

        if( count($values) == 0 ) {
            return array();
            //return array('error'=>"No data found corresponding to this chart parameters");
        }

        $xAxis = "labels";
        $yAxis = "values";
        if( $type == "bar" || $type == "stack" ) {
            $xAxis = "x";
            $yAxis = "y";
        }

        $chartDataArray = array();
        $chartDataArray[$xAxis] = $labels;
        $chartDataArray[$yAxis] = $values;
        $chartDataArray['type'] = $type;

        $chartDataArray["links"] = $links;
        //$chartDataArray["text"] = "111";
        $chartDataArray["textinfo"] = "value+percent";
        //hoverinfo: label+text+value+percent
        $chartDataArray["outsidetextfont"] = array('size'=>1,'color'=>'white');
        $chartDataArray['direction'] = 'clockwise';
        //$chartDataArray["hoverinfo"] = "percent+label";
        $chartDataArray["hoverinfo"] = $hoverinfo;

        $dataArray[] = $chartDataArray;

        //$chartsArray['layout'] = $layoutArray;
        //$chartsArray['data'] = $dataArray;

//        echo "<pre>";
//        print_r($dataArray);
//        echo "</pre>";

        $chartsArray = array(
            'layout' => $layoutArray,
            'data' => $dataArray
        );

        return $chartsArray;
    }

    public function getStackedChart( $combinedDataArr, $title, $type="stack", $layoutArray=null, $hoverinfo=null ) {

        if( count($combinedDataArr) == 0 ) {
            //exit('no data');
            return array();
        }

        if( !$layoutArray ) {
            $layoutArray = array(
                'height' => $this->height,
                'width' => $this->width,
                'margin' => array('b' => 200),
//            'yaxis' => array(
//                'automargin' => true
//            ),
//            'xaxis' => array(
//                'automargin' => true,
//            ),
//                'yaxis' => array(
//                    'tickformat' => "d" //"digit"
//                ),
            );
        }

        $layoutArray['title'] = $title;
        $layoutArray['barmode'] = 'stack';

        $stackDataArray = array();
        $allValues = array();
        //$stackDataSumArray = array();
        $xAxis = "x";
        $yAxis = "y";

        foreach($combinedDataArr as $name=>$dataArr) {
            $chartDataArray = array();
            $labels = array();
            $values = array();
            $links = array();
            foreach ($dataArr as $label => $valueData) {

                if( is_array($valueData) ) {
                    $value = $valueData["value"];
                    $link = $valueData["link"];
                } else {
                    $value = $valueData;
                    $link = null;
                }

                $labels[] = $label;
                $values[] = $value;
                $links[] = $link;

                $allValues[] = $value;

//                if( isset($stackDataSumArray[$label]) ) {
//                    $sumValue = $stackDataSumArray[$label] + $value;
//                } else {
//                    $sumValue = $value;
//                }
//                $stackDataSumArray[$label] = $sumValue;
            }

            //if( count($values) == 0 ) {
            //    continue;
            //}

            $chartDataArray[$xAxis] = $labels;
            $chartDataArray[$yAxis] = $values;
            $chartDataArray['name'] = $name;
            $chartDataArray['type'] = 'bar';
            $chartDataArray['links'] = $links;

            if( $hoverinfo ) {
                $chartDataArray["hoverinfo"] = $hoverinfo; //"x+y"; //"x+y"; //"x+y+name";
                //$chartDataArray["textinfo"] = "value"; //"label"; //"value+percent";
                //$chartDataArray["hovertemplate"] = "%{y}";
                //$chartDataArray["texttemplate"] = "%{y}";
            }

            $stackDataArray[] = $chartDataArray;
        }//foreach

        //if( count($values) == 0 ) {
        if( count($stackDataArray) == 0 || count($allValues) == 0 ) {
            return array();
            //return array('error'=>"No data found corresponding to this chart parameters");
        }

        $chartsArray = array(
            'layout' => $layoutArray,
            'data' => $stackDataArray
        );
        //dump($chartsArray);exit('111');

        return $chartsArray;
    }

    //https://plotly.com/javascript/violin/
    //https://raw.githubusercontent.com/plotly/datasets/master/violin_data.csv
    //$usmleSingleYearArr - usmle averages for single year
    //$comlexSingleYearArr - comlex averages for single year
    //$totalUsmleArr[$startDateLabel] = $usmleSingleYearArr;
    //$totalComlexArr[$startDateLabel] = $comlexSingleYearArr;
    //$combinedData['USMLE'] = $totalUsmleArr;
    //$combinedData['COMLEX'] = $totalComlexArr;
    public function getViolinChart( $combinedDataArr, $title, $type="violin", $layoutArray=null, $hoverinfo=null ) {
        if( count($combinedDataArr) == 0 ) {
            return array();
        }

        if( !$layoutArray ) {
            $layoutArray = array(
                'height' => $this->height,
                'width' => $this->width,
                'margin' => array('b' => 200),
                'yaxis' => array(
                    'zeroline' => false
                ),
                'violinmode' => 'group'
            );
        }

        $layoutArray['title'] = $title;

        $violinDataArray = array();
        //$stackDataSumArray = array();
        $xAxis = "x";
        $yAxis = "y";

        //total_bill,   tip,    sex,    smoker, day,    time,   size
        //15.42,        1.57,   Male,   No,     Sun,    Dinner, 2
        foreach($combinedDataArr as $name=>$dataArr) {
            $chartDataArray = array();
            //$labels = array('Sun', 'Mon', 'Tue', 'Sun', 'Mon', 'Tue', 'Mon', 'Tue', 'Mon');
            //$values = array("16.99", "10.34", "21.01", "23.68", "24.59", "25.29", "8.77", "26.88", "15.04");
            $labels = $dataArr['labels'];
            $values = $dataArr['values'];

            $chartDataArray[$xAxis] = $labels;
            $chartDataArray[$yAxis] = $values;
            $chartDataArray['name'] = $name;
            $chartDataArray['type'] = $type;
            $chartDataArray['legendgroup'] = $name;
            $chartDataArray['scalegroup'] = $name;
            $chartDataArray['box'] = array('visible' => true);
            $chartDataArray['meanline'] = array('visible' => true);
            $violinDataArray[] = $chartDataArray;
        }//foreach

        if( count($violinDataArray) == 0 ) {
            return array();
            //return array('error'=>"No data found corresponding to this chart parameters");
        }

        $chartsArray = array(
            'layout' => $layoutArray,
            'data' => $violinDataArray
        );

        //dump($chartsArray);
        //exit(111);

        return $chartsArray;
    }

    public function attachSecondValueToFirstLabel($firstArr,$secondArr,$prefix) {
        $resArr = array();
        foreach($firstArr as $index=>$value) {
            //$index = $index . " " . $prefix . $secondArr[$index];
            if( isset($secondArr[$index]) ) {
                if (strpos((string)$prefix, '$') !== false) {
                    //echo "index=$index, prefix=" . $prefix . "<br>"; //testing
                    //echo "secondArr[index]=" . $secondArr[$index] . "<br>";
                    $index = $index . " " . $prefix . $this->getNumberFormat($secondArr[$index]);
                } else {
                    $index = $index . " " . $prefix . $secondArr[$index];
                }
            }
            $resArr[$index] = $value;
        }
        return $resArr;
    }

    public function getProjectsByFilter($startDate, $endDate, $projectSpecialties, $states=null, $addOneEndDay=true) {

        //process.py script: replaced namespace by ::class: ['AppTranslationalResearchBundle:Project'] by [Project::class]
        $repository = $this->em->getRepository(Project::class);
        $dql =  $repository->createQueryBuilder("project");
        $dql->select('project');

        if( !$states ) {
            $dql->where("project.state = 'final_approved' OR project.state = 'closed'");
        } else {
            //$dql->where("request.progressState = '".$state."'");
            foreach($states as $state) {
                $stateArr[] = "project.state = '".$state."'";
            }
            if( count($stateArr) > 0 ) {
                $dql->where("(".implode(" OR ",$stateArr).")");
            }
        }

        $dqlParameters = array();

        if( $startDate ) {
            //echo "startDate=" . $startDate->format('Y-m-d H:i:s') . "<br>";
            $dql->andWhere('project.createDate >= :startDate');
            $dqlParameters['startDate'] = $startDate->format('Y-m-d'); //H:i:s
            //$dqlParameters['startDate'] = $startDate;
        }
        if( $endDate ) {
            if( $addOneEndDay ) {
                $endDate->modify('+1 day');
            }
            //echo "endDate=" . $endDate->format('Y-m-d H:i:s') . "<br>";
            $dql->andWhere('project.createDate <= :endDate');
            $dqlParameters['endDate'] = $endDate->format('Y-m-d'); //H:i:s
            //$dqlParameters['endDate'] = $endDate;
        }

        if( $projectSpecialties && count($projectSpecialties) > 0 ) {
            $dql->leftJoin('project.projectSpecialty','projectSpecialty');
            $projectSpecialtyIdsArr = array();
            $projectSpecialtyNamesArr = array();
            foreach($projectSpecialties as $projectSpecialty) {
                //echo "projectSpecialty=$projectSpecialty<br>";
                $projectSpecialtyIdsArr[] = $projectSpecialty->getId();
                $projectSpecialtyNamesArr[] = $projectSpecialty."";
            }
            $dql->andWhere("projectSpecialty.id IN (:projectSpecialtyIdsArr)");
            $dqlParameters["projectSpecialtyIdsArr"] = $projectSpecialtyIdsArr;
        }

        $query = $dql->getQuery();

        $query->setParameters($dqlParameters);
        //echo "query=".$query->getSql()."<br>";

        $projects = $query->getResult();

        //echo implode(",",$projectSpecialtyNamesArr)." Projects=".count($projects)." (".$startDate->format('d-M-Y')." - ".$endDate->format('d-M-Y').")<br>";

        return $projects;
    }

    public function getRequestsByFilter($startDate, $endDate, $projectSpecialties, $addOneEndDay=true) {
        //process.py script: replaced namespace by ::class: ['AppTranslationalResearchBundle:TransResRequest'] by [TransResRequest::class]
        $repository = $this->em->getRepository(TransResRequest::class);
        $dql =  $repository->createQueryBuilder("request");
        $dql->select('request');

        //Exclude Work requests with status=Canceled and Draft
        $dql->where("request.progressState != 'draft' AND request.progressState != 'canceled'");

        $dqlParameters = array();

        if( $startDate ) {
            //echo "startDate=" . $startDate->format('Y-m-d H:i:s') . "<br>";
            $dql->andWhere('request.createDate >= :startDate');
            $dqlParameters['startDate'] = $startDate->format('Y-m-d'); //H:i:s
        }
        if( $endDate ) {
            //$addOneEndDay=true;
            $thisEndDate = clone $endDate;
            if( $addOneEndDay ) {
                $thisEndDate->modify('+1 day');
                //$endDate->modify('+1 day');
            }
            //echo "endDate=" . $endDate->format('Y-m-d H:i:s') . "<br>";
            $dql->andWhere('request.createDate <= :endDate');
            //$dqlParameters['endDate'] = $endDate->format('Y-m-d'); //H:i:s
            $dqlParameters['endDate'] = $thisEndDate->format('Y-m-d'); //H:i:s
        }

        if( $projectSpecialties && count($projectSpecialties) > 0 ) {
            $dql->leftJoin('request.project','project');
            $dql->leftJoin('project.projectSpecialty','projectSpecialty');
            $projectSpecialtyIdsArr = array();
            $projectSpecialtyNamesArr = array();
            foreach($projectSpecialties as $projectSpecialty) {
                //echo "projectSpecialty=$projectSpecialty<br>";
                $projectSpecialtyIdsArr[] = $projectSpecialty->getId();
                $projectSpecialtyNamesArr[] = $projectSpecialty."";
            }
            $dql->andWhere("projectSpecialty.id IN (:projectSpecialtyIdsArr)");
            $dqlParameters["projectSpecialtyIdsArr"] = $projectSpecialtyIdsArr;
        }

        $query = $dql->getQuery();

        $query->setParameters($dqlParameters);
        //echo "query=".$query->getSql()."<br>";

        $projects = $query->getResult();

        //echo implode(",",$projectSpecialtyNamesArr)." Projects=".count($projects)." (".$startDate->format('d-M-Y')." - ".$endDate->format('d-M-Y').")<br>";

        return $projects;
    }
    public function getRequestsByAdvanceFilter($startDate, $endDate, $projectSpecialties, $productservice, $states=null, $addOneEndDay=true) {
        $em = $this->em;
        //$transresUtil = $this->container->get('transres_util');

        //process.py script: replaced namespace by ::class: ['AppTranslationalResearchBundle:TransResRequest'] by [TransResRequest::class]
        $repository = $em->getRepository(TransResRequest::class);
        $dql =  $repository->createQueryBuilder("request");
        $dql->select('request');

        //Exclude Work requests with status=Canceled and Draft
        if( !$states ) {
            $dql->where("request.progressState != 'draft' AND request.progressState != 'canceled'");
        } else {
            //$dql->where("request.progressState = '".$state."'");
            foreach($states as $state) {
                $stateArr[] = "request.progressState = '".$state."'";
            }
            if( count($stateArr) > 0 ) {
                $dql->where("(".implode(" OR ",$stateArr).")");
            }
        }

        $dqlParameters = array();

        if( $startDate ) {
            //echo "startDate=" . $startDate->format('Y-m-d H:i:s') . "<br>";
            $dql->andWhere('request.createDate >= :startDate');
            $dqlParameters['startDate'] = $startDate->format('Y-m-d'); //H:i:s
        }
        if( $endDate ) {
            if( $addOneEndDay ) {
                $endDate->modify('+1 day');
            }
            //echo "endDate=" . $endDate->format('Y-m-d H:i:s') . "<br>";
            $dql->andWhere('request.createDate <= :endDate');
            $dqlParameters['endDate'] = $endDate->format('Y-m-d'); //H:i:s
        }

        if( $projectSpecialties && count($projectSpecialties) > 0 ) {
            $dql->leftJoin('request.project','project');
            $dql->leftJoin('project.projectSpecialty','projectSpecialty');
            $projectSpecialtyIdsArr = array();
            $projectSpecialtyNamesArr = array();
            foreach($projectSpecialties as $projectSpecialty) {
                //echo "projectSpecialty=$projectSpecialty<br>";
                $projectSpecialtyIdsArr[] = $projectSpecialty->getId();
                $projectSpecialtyNamesArr[] = $projectSpecialty."";
            }
            $dql->andWhere("projectSpecialty.id IN (:projectSpecialtyIdsArr)");
            $dqlParameters["projectSpecialtyIdsArr"] = $projectSpecialtyIdsArr;
        }

        if( $productservice ) {
            $dql->leftJoin('request.products','products');
            $dql->leftJoin('products.category','category');
            $dql->andWhere("category.id = :categoryId");
            $dqlParameters["categoryId"] = $productservice; //->getId();
        }

        $query = $dql->getQuery();

        $query->setParameters($dqlParameters);
        //echo "query=".$query->getSql()."<br>";

        $projects = $query->getResult();

        //echo implode(",",$projectSpecialtyNamesArr)." Projects=".count($projects)." (".$startDate->format('d-M-Y')." - ".$endDate->format('d-M-Y').")<br>";

        return $projects;
    }

    public function getInvoicesByFilter($startDate, $endDate, $projectSpecialties, $states=null, $overdue=false, $addOneEndDay=true, $compareType='last invoice generation date',$filterRequest=true) {
        //process.py script: replaced namespace by ::class: ['AppTranslationalResearchBundle:Invoice'] by [Invoice::class]
        $repository = $this->em->getRepository(Invoice::class);
        $dql =  $repository->createQueryBuilder("invoice");
        $dql->select('invoice');
        $dql->leftJoin('invoice.transresRequest','request');

        //Exclude Work requests with status=Canceled and Draft
        //$dql->where("request.progressState != 'draft' AND request.progressState != 'canceled' AND invoice.latestVersion = TRUE AND invoice.status != 'canceled'");
        if( !$states ) {
            //Exclude Work requests with status=Canceled and Draft
            $dql->where("request.progressState != 'draft' AND request.progressState != 'canceled' AND invoice.latestVersion = TRUE AND invoice.status != 'canceled'");
        } else {
            foreach($states as $state) {
                $stateArr[] = "invoice.status = '".$state."'";
            }
            if( count($stateArr) > 0 ) {
                if( $filterRequest ) {
                    $dql->where("request.progressState != 'draft' AND request.progressState != 'canceled' AND invoice.latestVersion = TRUE AND (".implode(" OR ",$stateArr).")");
                } else {
                    $dql->where("invoice.latestVersion = TRUE AND (".implode(" OR ",$stateArr).")");
                }
            }
        }

        $dqlParameters = array();

        if( $startDate ) {
            //$startDateCriterion = 'request.createDate >= :startDate';
            if( $compareType == 'work request submission date' ) {
                $startDateCriterion = 'request.createDate >= :startDate';
            } elseif( $compareType == 'last invoice generation date' ) {
                $startDateCriterion = 'invoice.createDate >= :startDate';
            } elseif( $compareType == "date when status changed to paid in full" ) {
                $startDateCriterion = 'invoice.paidDate >= :startDate';
            } else {
                $startDateCriterion = 'request.createDate >= :startDate';
            }
            //echo "startDateCriterion=$startDateCriterion <br>";
            $dql->andWhere($startDateCriterion);
            $dqlParameters['startDate'] = $startDate->format('Y-m-d'); //H:i:s
        }
        if( $endDate ) {
            $thisEndDate = clone $endDate;
            if( $addOneEndDay ) {
                $thisEndDate->modify('+1 day');
            }
            //echo "endDate=" . $endDate->format('Y-m-d H:i:s') . "<br>";
            $dql->andWhere('request.createDate <= :endDate');

            //$endDateCriterion = 'request.createDate <= :endDate';
            if( $compareType == 'work request submission date' ) {
                $endDateCriterion = 'request.createDate <= :endDate';
            } elseif( $compareType == 'last invoice generation date' ) {
                $endDateCriterion = 'invoice.createDate <= :endDate';
            } elseif( $compareType == "date when status changed to paid in full" ) {
                $endDateCriterion = 'invoice.paidDate <= :endDate';
            } else {
                $endDateCriterion = 'request.createDate <= :endDate';
            }
            //echo "endDateCriterion=$endDateCriterion <br>";
            $dql->andWhere($endDateCriterion);

            $dqlParameters['endDate'] = $thisEndDate->format('Y-m-d'); //H:i:s
        }

        if( $projectSpecialties && count($projectSpecialties) > 0 ) {
            $dql->leftJoin('request.project','project');
            $dql->leftJoin('project.projectSpecialty','projectSpecialty');
            $projectSpecialtyIdsArr = array();
            $projectSpecialtyNamesArr = array();
            foreach($projectSpecialties as $projectSpecialty) {
                //echo "projectSpecialty=$projectSpecialty<br>";
                $projectSpecialtyIdsArr[] = $projectSpecialty->getId();
                $projectSpecialtyNamesArr[] = $projectSpecialty."";
            }
            $dql->andWhere("projectSpecialty.id IN (:projectSpecialtyIdsArr)");
            $dqlParameters["projectSpecialtyIdsArr"] = $projectSpecialtyIdsArr;
        }

        if( $overdue ) {
            $todayDate = new \DateTime();
            //$todayDate->modify('+1 day'); //make sure it's overdue (not considering hours and time zone difference)
            $dql->andWhere("invoice.dueDate IS NOT NULL AND :todayDate > invoice.dueDate");
            $dqlParameters["todayDate"] = $todayDate->format('Y-m-d');
        }

        $query = $dql->getQuery();

        $query->setParameters($dqlParameters);
        //echo "query=".$query->getSql()."<br>";

        $invoices = $query->getResult();

        //echo implode(",",$projectSpecialtyNamesArr)." Invoices=".count($invoices)." (".$startDate->format('d-M-Y')." - ".$endDate->format('d-M-Y').")<br>";

        return $invoices;
    }

    public function getChartNameWithTop($chartName,$quantityLimit) {
//        if( !$quantityLimit ) {
//            $quantityLimit = 10;
//        }
        if(0) {
            if (strpos((string)$chartName, "Top 10") !== false) {
                $chartNameModified = str_replace("Top 10", "Top " . $quantityLimit, $chartName);
            }
            if (strpos((string)$chartName, "Top 25") !== false) {
                $chartNameModified = str_replace("Top 25", "Top " . $quantityLimit, $chartName);
            }
            if (strpos((string)$chartName, "Top 35") !== false) {
                $chartNameModified = str_replace("Top 35", "Top " . $quantityLimit, $chartName);
            }
            if (strpos((string)$chartName, "Top 50") !== false) {
                $chartNameModified = str_replace("Top 50", "Top " . $quantityLimit, $chartName);
            }
        }
        $chartNameModified = null;
        if( $quantityLimit == "Show all" ) {
            $quantityLimitStr = "".$quantityLimit."";
        } else {
            $quantityLimitStr = "Top ".$quantityLimit."";
        }
        if (strpos((string)$chartName, "(Top)") !== false) {
            $chartNameModified = str_replace("(Top)","(".$quantityLimitStr.")",$chartName);
        }
        if (strpos((string)$chartName, "Top ") !== false) {
            $chartNameModified = str_replace("Top ","".$quantityLimitStr." ",$chartName);
        }

        if( !$chartNameModified ) {
            $chartNameModified = $chartName;
        }

        return $chartNameModified;
    }

    public function getTitleWithTotal($chartName,$total,$prefix=null,$postfix="total") {
        //$postfix = "total quantity";
        //$postfix = "total";
        //if( $prefix ) {
        //    $postfix = "total";
        //}
        return $chartName . " - " . $prefix . $total . " " . $postfix;
    }

    public function getTotalSegmentCount($arr) {
        //print_r($arr);
        $titleCount = 0;
        foreach($arr as $id=>$thisArr) {
            if( is_array($thisArr) ) {
                $titleCount = $titleCount + $thisArr['value'];
            } else {
                if( is_integer($thisArr) ) {
                    $titleCount = $titleCount + intval($thisArr);
                } else {
                    //???
                    $titleCount = $titleCount + intval($thisArr);
                }
            }
        }
        return $titleCount;
    }

    public function getDiffDaysByProjectState($project,$state) {
        $transresUtil = $this->container->get('transres_util');
        $reviews = $transresUtil->getReviewsByProjectAndState($project,$state);

        //get earliest create date and latest update date
        $startDate = null; //get enter state date
        $endDate = null; //get exit state date
        foreach($reviews as $review) {
            //phase start (enter) date
            $enterDate = $this->getPreviousStateEnterDate($project,$state);
            if( !$enterDate ) {
                $enterDate = $review->getCreatedate();
            }
            if( $startDate ) {
                if( $enterDate < $startDate ) {
                    $startDate = $enterDate;
                }
            } else {
                $startDate = $enterDate;
            }

            //phase end (exit) date
            if( $project->getApprovalDate() && $state == "final_review" ) {
                $endDate = $project->getApprovalDate();
                //echo "1 $state: ".$endDate->format("Y-m-d")."<br>";
                if( $endDate ) {
                    //echo "$state: ".$endDate->format("Y-m-d")."<br>";
                    continue;
                }
            }

            if( $state == "committee_review" ) {
                if( $review->getPrimaryReview() ) {
                    if ($endDate) {
                        if ($review->getUpdatedate() > $endDate) {
                            $endDate = $review->getUpdatedate();
                        }
                    } else {
                        $endDate = $review->getUpdatedate();
                    }
                }
            } else {
                if ($endDate) {
                    if ($review->getUpdatedate() > $endDate) {
                        $endDate = $review->getUpdatedate();
                    }
                } else {
                    $endDate = $review->getUpdatedate();
                }
            }
        }//foreach review

        if( !$startDate ) {
            $startDate = $project->getCreateDate();
        }

//        if( !$endDate && $state == "final_review" ) {
//            //echo "final state=".$state."<br>";
//            $endDate = $project->getApprovalDate();
//        }
//        if( $project->getApprovalDate() && $state == "final_review" ) {
//            $endDate = $project->getApprovalDate();
//        }

        if( !$endDate ) {
            //echo "***state=".$state."<br>";
            $endDate = $project->getUpdatedate();
        } else {
            //echo "###<br>";
        }

        if( $endDate < $startDate ) {
            $endDate = $startDate;
        }

        if( $startDate && $endDate ) {
            //ok
        } else {
            return null;
        }

        //echo $startDate->format("Y-m-d")." => ".$endDate->format("Y-m-d")." (".$state.")<br>";

//        //Number of days to go from review's createdate to review's updatedate
//        $dDiff = $startDate->diff($endDate);
//        //echo $dDiff->format('%R'); // use for point out relation: smaller/greater
//        $days = $dDiff->days;
//        //echo $state.": days=".$days."<br>";
//        $days = intval($days);
//
//        //show minimum 1 day
//        if( !$days || $days == 0 ) {
//            $days = 1;
//        }

        $days = $this->calculateDays($startDate,$endDate);

        return $days;
    }
    public function getStateExitDate($project,$state) {
        $transresUtil = $this->container->get('transres_util');
        $reviews = $transresUtil->getReviewsByProjectAndState($project,$state);

        //get latest update date
        $exitDate = null; //get exit state date
        foreach($reviews as $review) {
            if( $exitDate ) {
                if( $review->getUpdatedate() > $exitDate ) {
                    $exitDate = $review->getUpdatedate();
                }
            } else {
                $exitDate = $review->getUpdatedate();
            }
        }
        return $exitDate;
    }
//    public function getReviewExitDate($project,$state) {
//        $transresUtil = $this->container->get('transres_util');
//        $reviews = $transresUtil->getReviewsByProjectAndState($project,$state);
//
//        //get earliest create date and latest update date
//        $exitDate = null; //get exit state date
//        foreach($reviews as $review) {
//            if( $exitDate ) {
//                if( $review->getUpdatedate() > $exitDate ) {
//                    $exitDate = $review->getUpdatedate();
//                }
//            } else {
//                $exitDate = $review->getUpdatedate();
//            }
//        }
//        return $exitDate;
//    }
    public function getPreviousStateEnterDate($project,$state) {
        $date = null;
        if( $state == "irb_review" ) {
            $date = $project->getStartReviewDate();
            if( !$date ) {
                $date = $project->getCreateDate();
                //$date = $this->getStateEnterDate($project,"irb_review");
            }
        }
        if( $state == "admin_review" ) {
            $date = $this->getStateExitDate($project,"irb_review");
        }
        if( $state == "committee_review" ) {
            $date = $this->getStateExitDate($project,"admin_review");
        }
        if( $state == "final_review" ) {
            $date = $this->getStateExitDate($project,"committee_review");
        }
        return $date;
    }
    public function getStateTitleWithAverageDays($irbTitle,$projectPhaseArr) {
        $irbCount = count($projectPhaseArr);
        if( $irbCount > 0 ) {
            $irbDays = 0;
            foreach ($projectPhaseArr as $index => $valueData) {
                if( is_array($valueData) ) {
                    $days = $valueData['value'];
                } else {
                    $days = $valueData;
                }
                $irbDays = $irbDays + $days;
            }
            $irbTitle = $irbTitle . " (Average " . round($irbDays/$irbCount) . " days)";
        }
        return $irbTitle;
    }
    public function getInvoiceIssuedDate($invoice) {
        //continue;
        //$issued = $invoice->getCreateDate();

        $request = $invoice->getTransresRequest();

        //get the date from event log
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:Logger'] by [Logger::class]
        $repository = $this->em->getRepository(Logger::class);
        $dql = $repository->createQueryBuilder("logger");
        //$dql->innerJoin('logger.eventType', 'eventType');
        //$dql->leftJoin('logger.objectType', 'objectType');
        //$dql->leftJoin('logger.site', 'site');

        //$dql->where("logger.siteName = 'translationalresearch' AND logger.entityName = 'Invoice' AND logger.entityId = ".$invoice->getId());
        //$dql->where("logger.entityName = 'Invoice' AND logger.entityId = ".$invoice->getId());

        //Work Request ID APCP843-REQ16216 billing state has been changed to Invoiced, triggered by invoice status change to Unpaid/Issued
        $dql->where("logger.entityName = 'TransResRequest' AND logger.entityId = '".$request->getId()."'");

        //$dql->andWhere("logger.event LIKE '%"."status changed to '/Unpaid/Issued"."%'"); //status changed to 'Unpaid/Issued'
        //$dql->andWhere("logger.event LIKE :eventStr OR logger.event LIKE :eventStr2");
        $dql->andWhere("logger.event LIKE :eventStr");

        $dql->orderBy("logger.id","DESC");
        $query = $dql->getQuery();

        //$search = "status changed to 'Unpaid/Issued'";
        $search = "invoice status change to Unpaid/Issued";

        //$search = "Unpaid/Issued";
        //$search = "";
        //$search = "status changed to ";
        //$search2 = "status changed to 'Unpaid/Issued'";
        $query->setParameters(
            array(
                'eventStr' => '%'.$search.'%',
                //'eventStr2' => '%'.$search2.'%'
            )
        );

        $loggers = $query->getResult();

        //try to use "Invoice PDF Issued" event "Invoice APCP668-REQ14079-V1 PDF has been sent by email ..."
        if( count($loggers) == 0 ) {
            $dql2 = $repository->createQueryBuilder("logger");
            $dql2->where("logger.entityName = 'Invoice' AND logger.entityId = '".$invoice->getId()."'");
            $dql2->andWhere("logger.event LIKE :eventStr");

            $dql2->orderBy("logger.id","DESC");
            //$query2 = $this->em->createQuery($dql2);
            $query2 = $dql2->getQuery();

            $search2 = "Invoice ".$invoice->getOid()." PDF has been sent by email";
            $query2->setParameters(
                array(
                    'eventStr' => '%'.$search2.'%',
                )
            );

            $loggers = $query2->getResult();
        }

        //echo $invoice->getOid().": loggers count=".count($loggers)."<br>";
        //foreach($loggers as $logger) {
        //    echo "logger.id=".$logger->getId()."; TransResRequest id=".$request->getId()."<br>";
        //}

        if( count($loggers) > 0 ) {
            $logger = $loggers[0];
            //echo "@@@ logger.id=".$logger->getId()."; TransResRequest id=".$request->getId()."<br>";
            $issued = $logger->getCreationdate();
        } else {
            $issued = null;
        }

        return $issued;
    }

    public function getProjectRequestInvoiceChart($apcpProjects,$resStatArr,$startDateLabel) {
        $transresRequestUtil = $this->container->get('transres_request_util');
        //get requests, invoices

        $invoiceCount = 0;
        $requestCount = 0;
        foreach($apcpProjects as $project) {
            foreach($project->getRequests() as $request) {
                //$requestArr[] = $request;
                $requestCount++;
                $latestInvoice = $transresRequestUtil->getLatestInvoice($request);
                if( $latestInvoice ) {
                    $invoiceCount++;
                }
            }
        }
        //echo "invoiceCount=$invoiceCount<br>";

        $resStatArr['projects'][$startDateLabel] = count($apcpProjects);
        $resStatArr['requests'][$startDateLabel] = $requestCount;
        $resStatArr['invoices'][$startDateLabel] = $invoiceCount;

        return $resStatArr;
    }

    public function calculateDays($startDate,$endDate) {
        //1) calculate days
        $dDiff = $startDate->diff($endDate);
        //echo $dDiff->format('%R'); // use for point out relation: smaller/greater
        $days = $dDiff->days;
        //echo "days=".$days."<br>";
        $days = intval($days);

        if( !$days || $days == 0 ) {
            $days = 1;
        }

        return $days;
    }

    //function for "42. Total Number of Individual PIs involved in AP/CP and Hematopathology Projects" => "compare-projectspecialty-pis",
    public function trpPisSingleSpecialty($pisDataArr,$specialtyObject,$startDate,$startDateStr,$endDate,$endDateStr) {
        $projects = $this->getProjectsByFilter($startDate, $endDate, array($specialtyObject));

        $pisArr = array();
        foreach ($projects as $project) {
            foreach ($project->getAllPrincipalInvestigators() as $pi) {
                $pisArr[] = $pi->getId();
            }
        }

        $pisArr = array_unique($pisArr);

        //APCP
        //array(value,link)
        $linkFilterArr = array(
            'filter[state][0]' => 'final_approved',
            'filter[state][1]' => 'closed',
            'filter[startDate]' => $startDateStr,
            'filter[endDate]' => $endDateStr,
            //'filter[]' => $projectSpecialtyObjects,
            'filter[searchProjectType]' => null,
            'filter[projectSpecialty][]' => $specialtyObject->getId(),
            //'filter[principalInvestigators][]' => implode(",",$apcpPisArr)
        );
        $index = 0;
        foreach ($pisArr as $piId) {
            $filterIndex = "filter[principalInvestigators][" . $index . "]";
            //echo "filterIndex=".$filterIndex."<br>";
            $linkFilterArr[$filterIndex] = $piId;
            $index++;
        }
        $link = $this->container->get('router')->generate(
            'translationalresearch_project_index',
            $linkFilterArr,
            UrlGeneratorInterface::ABSOLUTE_URL
        );

        $specialtyName = $specialtyObject->getName();

        $pisDataArr[$specialtyName.' PIs'] = array('value' => count($pisArr), 'link' => $link);

        return $pisDataArr;
    }
    public function trpProjectsSingleSpecialty($dataArr,$specialtyObject,$startDate,$startDateStr,$endDate,$endDateStr) {
        $projects = $this->getProjectsByFilter($startDate,$endDate,array($specialtyObject));

        //APCP
        $linkFilterArr = array(
            'filter[state][0]' => 'final_approved',
            'filter[state][1]' => 'closed',
            'filter[startDate]' => $startDateStr,
            'filter[endDate]' => $endDateStr,
            'filter[searchProjectType]' => null,
            'filter[projectSpecialty][]' => $specialtyObject->getId(),
        );
        $link = $this->container->get('router')->generate(
            'translationalresearch_project_index',
            $linkFilterArr,
            UrlGeneratorInterface::ABSOLUTE_URL
        );
        $specialtyName = $specialtyObject->getName();
        $dataArr[$specialtyName.' Project Requests'] = array('value'=>count($projects),'link'=>$link);

        return $dataArr;
    }
    /////////////////////// EOF methods ////////////////////////

//    public function isViewPermitted($chart, $withFlash=true) {
//        if( $this->secAuth->isGranted('read', $chart) === true ) {
//
//            if( $withFlash ) {
//                $error = "You do not have access to this chart '" . $chart->getName() . "'. Please request access by contacting your site administrator.";
//                $this->get('session')->getFlashBag()->add(
//                    'warning',
//                    $error
//                );
//            }
//
//            return true;
//        }
//
//        //exit('Not permitted');
//        return false;
//    }

    public function getRequestParameters($request) {
        $parametersArr= array();

        $processed = false;

        if( $request->isMethod('GET') ) {
            //echo "request is GET <br>";
            $startDate = $request->query->get('startDate');
            $endDate = $request->query->get('endDate');
            $projectSpecialty = $request->query->get('projectSpecialty');
            $showLimited = $request->query->get('showLimited');
            $chartType = $request->query->get('chartType');
            $productservice = $request->query->get('productservice');
            $quantityLimit = $request->query->get('quantityLimit');
            $processed = true;
        }
        elseif( $request->isMethod('POST') ) {
            //echo "request is POST <br>";
            //dump($request);

            //use $request->getContent() for axios with header: application/x-www-form-urlencoded
            $content = $request->getContent();
            if( $content ) {
                //dump($content);
                //exit('111');
                $postData = json_decode($content, true);
                //$postData = json_decode($content);
                //dump($postData);
                //exit('111');

                $startDate = $postData['startDate'];
                $endDate = $postData['endDate'];
                $projectSpecialty = $postData['projectSpecialty'];
                $showLimited = $postData['showLimited'];
                $chartType = $postData['chartType'];
                $quantityLimit = $postData['quantityLimit'];

                $productservice = NULL;
                if( array_key_exists('productservice', $postData) ) {
                    $productservice = $postData['productservice'];
                }
                $processed = true;
            } else {
                $startDate = $request->get('startDate');
                $endDate = $request->get('endDate');
                $projectSpecialty = $request->get('projectSpecialty');
                $showLimited = $request->get('showLimited');
                $chartType = $request->get('chartType');
                $productservice = $request->get('productservice');
                $quantityLimit = $request->get('quantityLimit');
                $processed = true;
            }
        } else {
            //exit('Logical error: request is not GET or POST');
        }

        if( $processed ) {
            //echo "1chartType=$chartType <br>";
            $parametersArr['startDate'] = $startDate;
            $parametersArr['endDate'] = $endDate;
            $parametersArr['projectSpecialty'] = $projectSpecialty;
            $parametersArr['showLimited'] = $showLimited;
            $parametersArr['chartType'] = $chartType;
            $parametersArr['productservice'] = $productservice;
            $parametersArr['quantityLimit'] = $quantityLimit;
        }

        //echo "chartType=".$chartType."<br>";
        //exit('111');

        return $parametersArr;
    }

    public function getSessionFlashBag( $asString=true, $clear=true ) {
        //dump($this->session->getFlashBag());
        //exit('111');

        $notices = $this->session->getFlashBag()->get('notice', []);
        $warnings = $this->session->getFlashBag()->get('warning', []);
        $errors = $this->session->getFlashBag()->get('error', []);
        $permissionWarning = $this->session->getFlashBag()->get('permissionwarning', []);

        if( $clear ) {
            $errors = $this->session->getFlashBag()->clear();
        }

        if( $asString ) {

            $resString = NULL;

            if( count($permissionWarning) > 0 ) {
                if( $resString ) {
                    $resString = $resString . "; ";
                }
                $resString = $resString . "Permission Warning: " . implode(', ', $permissionWarning);
            }

            if( count($notices) > 0 ) {
                if( $resString ) {
                    $resString = $resString . "; ";
                }
                $resString = $resString . "Notices: " . implode(', ', $notices);
            }

            if( count($warnings) > 0 ) {
                if( $resString ) {
                    $resString = $resString . "; ";
                }
                $resString = $resString . "Warnings: " . implode(', ', $warnings);
            }

            if( count($errors) > 0 ) {
                if( $resString ) {
                    $resString = $resString . "; ";
                }
                $resString = $resString . "Errors: " . implode(', ', $errors);
            }

            return $resString;
        }

        $flashBag = array(
            'notice' => $notices,
            'warning' => $warnings,
            'error' => $errors,
        );

        return $flashBag;
    }
    public function getPermissionErrorSession( $chart, $clear=true ) {
        //Use session to get error attribute
        $sessionAttribute = NULL;

        $session = $this->session;
        //dump($session);

        if( $session ) {
            if( $clear ) {
                //remove(): Deletes an attribute by name and returns its value.
                $sessionAttribute = $session->remove('permission-error-' . $chart->getId());
            } else {
                $sessionAttribute = $session->get('permission-error-' . $chart->getId());
            }

            //echo "sessionAttribute=$sessionAttribute <br>";
            //$error = $sessionAttribute;
        }

        //$session = $this->session;
        //dump($session);

        return $sessionAttribute;
    }

    //////////// Main function to get chart data called by controller singleChartAction ("/single-chart/") ////////////
    public function getDashboardChart( $request, $parametersArr=NULL, $eventlog=true ) {

        //ini_set('memory_limit', '30000M');
        ini_set('max_execution_time', 1200); //1200 sec = 20 min; //600 seconds = 10 minutes; it will set back to original value after execution of this script

        if( $request ) {

//            if( $request->isMethod('GET') ) {
//                echo "request is GET <br>";
//                $startDate = $request->query->get('startDate');
//                $endDate = $request->query->get('endDate');
//                $projectSpecialty = $request->query->get('projectSpecialty');
//                $showLimited = $request->query->get('showLimited');
//                $chartType = $request->query->get('chartType');
//                $productservice = $request->query->get('productservice');
//                $quantityLimit = $request->query->get('quantityLimit');
//            }
//            elseif( $request->isMethod('POST') ) {
//                echo "request is POST <br>";
//                $startDate = $request->query->get('startDate');
//                $endDate = $request->query->get('endDate');
//                $projectSpecialty = $request->query->get('projectSpecialty');
//                $showLimited = $request->query->get('showLimited');
//                $chartType = $request->get('chartType');
//                $productservice = $request->query->get('productservice');
//                $quantityLimit = $request->query->get('quantityLimit');
//            } else {
//                exit('Logical error: request is not GET or POST');
//            }

            //echo "get params from request<br>";
            $parametersArr = $this->getRequestParameters($request);
        }
//        else {
//
//            $startDate = $parametersArr['startDate'];
//            $endDate = $parametersArr['endDate'];
//            $projectSpecialty = $parametersArr['projectSpecialty'];
//            $showLimited = $parametersArr['showLimited'];
//            $chartType = $parametersArr['chartType'];
//            $productservice = $parametersArr['productservice'];
//            $quantityLimit = $parametersArr['quantityLimit'];
//        }

        if( $parametersArr ) {
            $startDate = $parametersArr['startDate'];
            $endDate = $parametersArr['endDate'];
            $projectSpecialty = $parametersArr['projectSpecialty'];
            $showLimited = $parametersArr['showLimited'];
            $chartType = $parametersArr['chartType'];
            $productservice = $parametersArr['productservice'];
            $quantityLimit = $parametersArr['quantityLimit'];
        } else {
            $error = "Logical error: missing parameters";
            $chartsArray['warning'] = false;
            $chartsArray['error'] = $error;
            return $chartsArray;
        }

        //echo "quantityLimit=$quantityLimit<br>";
        //echo "showLimited=$showLimited<br>";
//        if( $quantityLimit ) {
//            $this->quantityLimit = $quantityLimit;
//        }

        //dump($request->query);
        //echo "startDate=".$startDate."<br>";
        //echo "endDate=".$endDate."<br>";
        //echo "projectSpecialty=".$projectSpecialty."<br>";
        //echo "chartType=$chartType <br>";
        //exit('111');
        
        //testing
        if(0) {
            $error = "Test error [$chartType]";
            $chartsArray['warning'] = false;
            $chartsArray['error'] = $error;
            return $chartsArray;
        }

        $userSecUtil = $this->container->get('user_security_utility');
        $user = $this->security->getUser();

        //process.py script: replaced namespace by ::class: ['AppDashboardBundle:ChartList'] by [ChartList::class]
        $chartObject = $this->em->getRepository(ChartList::class)->findOneByAbbreviation($chartType);
        //$chartObject = $this->getChartsByChartType($chartType,true);
        if( !$chartObject ) {
            $error = "Logical error: chart not found by abbreviation [$chartType]";
            $chartsArray['warning'] = false;
            $chartsArray['error'] = $error;
            return $chartsArray;
        }

        //if( $this->security->isGranted('read', $chartObject) === false ) {
            //exit('getDashboardChart: no read permission');
        //}

        //check !isChartPublic and read permission
        if( $this->isChartPublic($chartObject) == false && $this->security->isGranted('read', $chartObject) === false ) {
            //get admin email
            //$userSecUtil = $this->container->get('user_security_utility');
            $adminemail = $userSecUtil->getSiteSettingParameter('siteEmail');
            $error = "You do not have access to this chart '".$chartObject->getName().
                "'. Please request access by contacting your site administrator $adminemail.";

            //$flashBagStr = $this->getSessionFlashBag();
            $permissionErrorStr = $this->getPermissionErrorSession($chartObject);
            if( $permissionErrorStr ) {
                $error = $error . " " . $permissionErrorStr;
            }

            //EventLog
            if( $eventlog ) {
                $eventType = "Dashboard Chart Access Not Permitted";
                $sitename = $this->container->getParameter('dashboard.sitename');
                $event = "User " . $user . " does not have access to the dashboard chart with ID " . $chartObject->getId() . " '" . $chartObject->getName();
                //createUserEditEvent($sitename,$event,$user,$subjectEntities,$request,$action='Unknown Event')
                $userSecUtil->createUserEditEvent($sitename, $event, $user, $chartObject, $request, $eventType);
            }

            $chartsArray['warning'] = false;
            $chartsArray['error'] = $error;
            return $chartsArray;
        }

        //EventLog
        if( $eventlog ) {
            $eventType = "Dashboard Chart Viewed";
            $sitename = $this->container->getParameter('dashboard.sitename');
            $event = "Dashboard chart with ID " . $chartObject->getId() . " '" . $chartObject->getName() . "' viewed by " . $user;
            //createUserEditEvent($sitename,$event,$user,$subjectEntities,$request,$action='Unknown Event')
            $userSecUtil->createUserEditEvent($sitename, $event, $user, $chartObject, $request, $eventType);
        }

        $now = new \DateTime('now');

        if( !$endDate ) {
            //set to today
            $endDate = $now->format('m/d/Y');
        }

        if( !$startDate ) {
            //set to 1900
            //$startDate = "01/01/1900"; //10/31/2017 to DateTime
            $startDate = $now->modify('-1 year')->format('m/d/Y');
        }

        //echo "start=".$startDate."<br>";
        //echo "end=".$endDate."<br>";

        if( $startDate ) {
            $startDate = date_create_from_format('m/d/Y', $startDate); //10/31/2017 to DateTime
        }
        if( $endDate ) {
            $endDate = date_create_from_format('m/d/Y', $endDate); //10/31/2017 to DateTime
        }

        if( $startDate ) {
            $startDateStr = $startDate->format('m/d/Y');
        }
        if( $endDate ) {
            $endDateStr = $endDate->format('m/d/Y');
        }
        //echo "start=".$startDate->format('m/d/Y')."<br>";
        //echo "end=".$endDate->format('m/d/Y')."<br>";

        $projectSpecialtyObjects = array();

        if( !$projectSpecialty ) {
            $projectSpecialty = NULL;
        }

        //echo "projectSpecialty=".$projectSpecialty."<br>";
        if( $projectSpecialty != 0 ) {
            //echo "projectSpecialty=".$projectSpecialty."<br>";
        //process.py script: replaced namespace by ::class: ['AppTranslationalResearchBundle:SpecialtyList'] by [SpecialtyList::class]
            $projectSpecialtyObject = $this->em->getRepository(SpecialtyList::class)->find($projectSpecialty);
            $projectSpecialtyObjects[] = $projectSpecialtyObject;
        }
        //exit('1');

        $filterArr = array(
            'startDate'=>$startDate,
            'endDate'=>$endDate,
            'projectSpecialtyObjects' => $projectSpecialtyObjects,
            'showLimited' => $showLimited,
            'quantityLimit'=>$quantityLimit,
            'funded' => null
        );

        $layoutArray = array(
            'height' => $this->height,
            'width' => $this->width,
        );

        //echo "startDate=".$startDate."<br>";

        $titleCount = 0;
        $chartName = $this->getChartTypeByValue($chartType);

//        //TODO: add project specialty types according to the enabled specialties.
//        // Replace [[projectSpecialties]] by $transresUtil->getAllowedProjectSpecialty($user)
//        if( strpos((string)$chartName, "[[projectSpecialties]]") !== false ) {
//            $transresUtil = $this->container->get('transres_util');
//            $projectSpecialtyAllowedRes = $transresUtil->getAllowedProjectSpecialty();
//            $projectSpecialtyAllowedArr = $projectSpecialtyAllowedRes['projectSpecialtyAllowedArr'];
//            //$projectSpecialtyDeniedArr = $projectSpecialtyAllowedRes['projectSpecialtyDeniedArr'];
//            $projectSpecialtyAllowedStr = implode(", ", $projectSpecialtyAllowedArr);
//            $chartName = str_replace("[[projectSpecialties]]", $projectSpecialtyAllowedStr);
//        }

        $chartName = $this->getChartNameWithTop($chartName,$quantityLimit);

        //testing: add favorite icon
//        $favoriteDivId = 1;
//        $chartId = 1;
//        $favoriteIcon =
//            '<div><span id="'.$favoriteDivId.'" ' .
//            'class="favorite-icon glyphicon glyphicon-heart-empty" ' .
//            'style="color:orangered; float:right;" ' .
//            'onClick="favoriteChart(this,'.$chartId.');"></span></div>';
//        $chartName = $favoriteIcon . " " . $chartName;

        $chartsArray = null;
        $warningNoData = null;

        //1. Principal Investigators by Affiliation (Linked)
        if( $chartType == "pi-by-affiliation" ) {

            //$userSecUtil = $this->container->get('user_security_utility');
            //$piWcmPathologyCounter = 0;
            //$piWcmCounter = 0;
            //$piOtherCounter = 0;
            $departmentAbbreviation = "Department";
            $institutionAbbreviation = "Institution";
            $institution = null;
            $department = $userSecUtil->getSiteSettingParameter('transresDashboardInstitution');
            if( $department ) {
                $departmentAbbreviation = $department."";
                $institution = $department->getParent();
                if( $institution ) {
                    $institutionAbbreviation = $institution."";
                }
            }

            $projectsPerPi1 = array();
            $projectsPerPi2 = array();
            $projectsPerPi3 = array();
            $totalProjects = 0;
            $projectsCount1 = 0;
            $projectsCount2 = 0;
            $projectsCount3 = 0;

            $projects = $this->getProjectsByFilter($startDate,$endDate,$projectSpecialtyObjects);
            //echo "Projects count=".count($projects)."<br>";

            foreach($projects as $project) {
                //$pis = $project->getPrincipalInvestigators();
                $pis = $project->getAllPrincipalInvestigators();
                $count1 = false;
                $count2 = false;
                $count3 = false;

                foreach ($pis as $pi) {
                    //1. Principle Investigators by Affiliation
                    if( $this->isUserBelongsToInstitution($pi,$department) ) {
                        //WCM Pathology Faculty - WCM Department of Pathology and Laboratory Medicine in any Title’s department field
                        //$piWcmPathologyCounter++;
                        $count1 = true;
                        $projectsPerPi1[] = $pi->getId();
                        //$totalProjects++;
                        //echo $totalProjects."(WCM Pathology Faculty): PI=$pi; Project ID=".$project->getId()."<br>";
                    }
                    elseif ( $this->isUserBelongsToInstitution($pi,$institution) ) {
                        //WCM Other Departmental Faculty - WCM institution
                        //Non-WCM Pathology faculty PIs
                        //$piWcmCounter++;
                        $count2 = true;
                        $projectsPerPi2[] = $pi->getId();
                        //$totalProjects++;
                        //echo $totalProjects."(Non-WCM Pathology faculty): PI=$pi; Project ID=".$project->getId()."<br>";
                    } else {
                        //Other Institutions
                        //$piOtherCounter++;
                        $count3 = true;
                        $projectsPerPi3[] = $pi->getId();
                    }

                }//foreach pi

                if( $count1 ) {
                    $projectsCount1++;
                }
                if( $count2 ) {
                    $projectsCount2++;
                }
                if( $count3 ) {
                    $projectsCount3++;
                }

            }//foreach project
            //exit('111');

            $dataArray = array();
            $chartDataArray = array();
            $type = 'pie';

            $links = array();

            //////////// WCM Pathology Faculty ////////////
            $projectsPerPi1 = array_unique($projectsPerPi1);
            $piWcmPathologyCounter = count($projectsPerPi1);

            $linkFilterArr = array(
                'filter[state][0]' => 'final_approved',
                'filter[state][1]' => 'closed',
                'filter[startDate]' => $startDateStr,
                'filter[endDate]' => $endDateStr,
                //'filter[]' => $projectSpecialtyObjects
            );
            $userIndex = 0;
            foreach($projectsPerPi1 as $thisPi) {
                $linkFilterArr['filter[principalInvestigators]['.$userIndex.']'] = $thisPi;
                $userIndex++;
            }
            $link = $this->container->get('router')->generate(
                'translationalresearch_project_index',
                $linkFilterArr,
                UrlGeneratorInterface::ABSOLUTE_URL
            );
            $links[] = $link;
            //////////// EOF WCM Pathology Faculty ////////////

            //////////// Non-WCM Pathology faculty PIs ////////////
            $projectsPerPi2 = array_unique($projectsPerPi2);
            $piWcmCounter = count($projectsPerPi2);

            $linkFilterArr = array(
                'filter[state][0]' => 'final_approved',
                'filter[state][1]' => 'closed',
                'filter[startDate]' => $startDateStr,
                'filter[endDate]' => $endDateStr,
                //'filter[]' => $projectSpecialtyObjects
            );
            $userIndex = 0;
            foreach($projectsPerPi2 as $thisPi) {
                $linkFilterArr['filter[principalInvestigators]['.$userIndex.']'] = $thisPi;
                $userIndex++;
            }
            $link = $this->container->get('router')->generate(
                'translationalresearch_project_index',
                $linkFilterArr,
                UrlGeneratorInterface::ABSOLUTE_URL
            );
            $links[] = $link;
            //////////// EOF Non-WCM Pathology faculty PIs ////////////

            //////////// Other Institutions ////////////
            $projectsPerPi3 = array_unique($projectsPerPi3);
            $piOtherCounter = count($projectsPerPi3);

            $linkFilterArr = array(
                'filter[state][0]' => 'final_approved',
                'filter[state][1]' => 'closed',
                'filter[startDate]' => $startDateStr,
                'filter[endDate]' => $endDateStr,
                //'filter[]' => $projectSpecialtyObjects
            );
            $userIndex = 0;
            foreach($projectsPerPi3 as $thisPi) {
                $linkFilterArr['filter[principalInvestigators]['.$userIndex.']'] = $thisPi;
                $userIndex++;
            }
            $link = $this->container->get('router')->generate(
                'translationalresearch_project_index',
                $linkFilterArr,
                UrlGeneratorInterface::ABSOLUTE_URL
            );
            $links[] = $link;
            //////////// EOF Other Institutions ///////////////

            $titleTotal = $piWcmPathologyCounter + $piWcmCounter + $piOtherCounter;
            $chartName = $this->getTitleWithTotal($chartName,$titleTotal,null,"PIs total");

            $layoutArray = array(
                'height' => $this->height,
                'width' =>  $this->width,
                'title' => $chartName //"1. Principle Investigators by Affiliation"
            );

            //$institutionAbbreviation = "WCM";
            //$departmentAbbreviation = "Pathology";
            //$piWcmPathologyCounter = 2;
            //$piWcmCounter = 5;

            $instName = $institutionAbbreviation . " " . $departmentAbbreviation;

            //WCMC pathology faculty PIs with 134 projects: 26
            //Non-WCMC pathology faculty PIs with 211 projects: 37
            $labels = array(
                "$instName faculty PIs with ".$projectsCount1." projects: ".$piWcmPathologyCounter,
                //"$institutionAbbreviation Other Departmental faculty PIs with ".$projectsCount2." projects: ".$piWcmCounter,
                "Non-".$instName." faculty PIs with ".$projectsCount2." projects: ".$piWcmCounter,
                "Other Institutions' faculty PIs with ".$projectsCount3." projects: ".$piOtherCounter
            );

            $values = array($piWcmPathologyCounter,$piWcmCounter,$piOtherCounter);
            //$values = array($piWcmPathologyCounter,$piWcmCounter);

            $chartDataArray['values'] = $values;
            $chartDataArray['labels'] = $labels;
            $chartDataArray['type'] = $type;
            $chartDataArray["textinfo"] = "value+percent";
            //$chartDataArray["textinfo"] = "value";
            $chartDataArray["outsidetextfont"] = array('size'=>1,'color'=>'white');
            $chartDataArray['direction'] = 'clockwise';
            $chartDataArray["hoverinfo"] = "percent+label";

            //links
            $chartDataArray["links"] = $links;

            $dataArray[] = $chartDataArray;

            $chartsArray = array(
                'layout' => $layoutArray,
                'data' => $dataArray
            );
        }

        //2. Total number of projects (XXX) per PI (Top 5/10) (APPROVED & CLOSED)
        //project's list might show the different matching projects, because in the filter principalInvestigators
        // are filtered by $dql->andWhere("principalInvestigators.id IN (:principalInvestigators) OR principalIrbInvestigator.id IN (:principalInvestigators)");
        if( $chartType == "projects-per-pi" ) {

            $piProjectCountArr = array();

            $projects = $this->getProjectsByFilter($startDate,$endDate,$projectSpecialtyObjects);
            //echo "projects=".count($projects)."<br>";

            foreach($projects as $project) {
                //$pis = $project->getPrincipalInvestigators();
                $pis = $project->getAllPrincipalInvestigators();
                foreach ($pis as $pi) {
                    $userName = $pi->getUsernameOptimal();
                    $userId = $pi->getId();

                    //2. Total number of projects (XXX) per PI (Top 5/10) (APPROVED & CLOSED)
                    if( isset($piProjectCountArr[$userId]) && isset($piProjectCountArr[$userId]['value']) ) {
                        $count = $piProjectCountArr[$userId]['value'] + 1;
                    } else {
                        $count = 1;
                    }
                    $piProjectCountArr[$userId]['value'] = $count;
                    $piProjectCountArr[$userId]['label'] = $userName;
                    $piProjectCountArr[$userId]['objectid'] = $userId;
                    $piProjectCountArr[$userId]['pi'] = $userId;
                    $piProjectCountArr[$userId]['show-path'] = "project";
                }

                $titleCount++;
            }
            //exit('111');

            $showOther = $this->getOtherStr($showLimited,"PIs");
            $piProjectCountTopArr = $this->getTopMultiArray($piProjectCountArr,$showOther,$quantityLimit); // getTopMultiArray(
            $filterArr['funded'] = null;

            //135 project requests (129 unique) total
            $totalSegmentCount = $this->getTotalSegmentCount($piProjectCountTopArr);

            //$chartName = $chartName . " - " . $totalCount . " total";
            $chartName = $this->getTitleWithTotal($chartName,$totalSegmentCount,null,"project requests ($titleCount unique) total");

            //Projects per PI
            //                                           $dataArr,              $title,                                $type='pie', $layoutArray=null, $valuePrefixLabel=null
            $chartsArray = $this->getChartByMultiArray( $piProjectCountTopArr, $filterArr, $chartName,"pie",null," : ","percent+label");
        }
        ///////////////// EOF 2. Total number of projects (XXX) per PI (Top 5/10) (APPROVED & CLOSED) /////////////////

        // 3. Total number of Funded Projects per PI (Top 10)
        if( $chartType == "funded-projects-per-pi" ) {
            $piFundedProjectCountArr = array();

            $projects = $this->getProjectsByFilter($startDate,$endDate,$projectSpecialtyObjects);

            foreach($projects as $project) {
                $fundingNumber = $project->getFunded();

                if( $fundingNumber ) {

                    //$pis = $project->getPrincipalInvestigators();
                    $pis = $project->getAllPrincipalInvestigators();
                    foreach ($pis as $pi) {
                        $userName = $pi->getUsernameOptimal();
                        $userId = $pi->getId();

                        if (isset($piFundedProjectCountArr[$userId]) && isset($piFundedProjectCountArr[$userId]['value'])) {
                            $count = $piFundedProjectCountArr[$userId]['value'] + 1;
                        } else {
                            $count = 1;
                        }
                        $piFundedProjectCountArr[$userId]['value'] = $count;
                        $piFundedProjectCountArr[$userId]['label'] = $userName;
                        $piFundedProjectCountArr[$userId]['objectid'] = $userId;
                        $piFundedProjectCountArr[$userId]['pi'] = $userId;
                        $piFundedProjectCountArr[$userId]['show-path'] = "project";

                        //$titleCount = $titleCount + $count;

                    }//foreach $pis

                    $titleCount++;
                }//if

            }//foreach $projects

            $showOther = $this->getOtherStr($showLimited,"PIs");
            $piFundedProjectCountTopArr = $this->getTopMultiArray($piFundedProjectCountArr,$showOther,$quantityLimit);

            $totalSegmentCount = $this->getTotalSegmentCount($piFundedProjectCountTopArr);
            $chartName = $this->getTitleWithTotal($chartName,$totalSegmentCount,null,"project requests ($titleCount unique) total");

            $filterArr['funded'] = true;
            $chartsArray = $this->getChartByMultiArray( $piFundedProjectCountTopArr, $filterArr, $chartName,"pie",null," : ","percent+label");

        }
        ///////////////// EOF 3. Total number of Funded Projects per PI (Top 10) /////////////////

        //4. Total number of Non-Funded Projects per PI (Top 10)
        if( $chartType == "nonfunded-projects-per-pi" ) {
            $piUnFundedProjectCountArr = array();

            $projects = $this->getProjectsByFilter($startDate,$endDate,$projectSpecialtyObjects);

            foreach($projects as $project) {
                $fundingNumber = $project->getFunded();

                if( $fundingNumber ) {
                    //do nothing
                } else {

                    //$pis = $project->getPrincipalInvestigators();
                    $pis = $project->getAllPrincipalInvestigators();
                    foreach ($pis as $pi) {
                        $userName = $pi->getUsernameOptimal();
                        $userId = $pi->getId();

                        if (isset($piUnFundedProjectCountArr[$userId]) && isset($piUnFundedProjectCountArr[$userId]['value'])) {
                            $count = $piUnFundedProjectCountArr[$userId]['value'] + 1;
                        } else {
                            $count = 1;
                        }
                        $piUnFundedProjectCountArr[$userId]['value'] = $count;
                        $piUnFundedProjectCountArr[$userId]['label'] = $userName;
                        $piUnFundedProjectCountArr[$userId]['objectid'] = $userId;
                        $piUnFundedProjectCountArr[$userId]['pi'] = $userId;
                        $piUnFundedProjectCountArr[$userId]['show-path'] = "project";

                        //$titleCount = $titleCount + $count;
                    }//foreach $pis

                    $titleCount++;
                }
            }//foreach $projects

            $showOther = $this->getOtherStr($showLimited,"PIs");
            $piUnFundedProjectCountTopArr = $this->getTopMultiArray($piUnFundedProjectCountArr,$showOther,$quantityLimit);

            $totalSegmentCount = $this->getTotalSegmentCount($piUnFundedProjectCountTopArr);
            $chartName = $this->getTitleWithTotal($chartName,$totalSegmentCount,null,"project requests ($titleCount unique) total");

            $filterArr['funded'] = false;
            $chartsArray = $this->getChartByMultiArray( $piUnFundedProjectCountTopArr, $filterArr, $chartName,"pie",null," : ","percent+label");
        }
        ///////////////// EOF 4. Total number of Non-Funded Projects per PI (Top 10) /////////////////

        //5. Total Number of Projects per Pathologist Involved (Top 10)
        if( $chartType == "projects-per-pathologist-involved" ) {
            $pathologistProjectCountArr = array();
            //$pathologistProjectCountMultiArr = array();

            $projects = $this->getProjectsByFilter($startDate,$endDate,$projectSpecialtyObjects);

            foreach($projects as $project) {
                $pathologists = $project->getPathologists();
                foreach ($pathologists as $pathologist) {
                    $userName = $pathologist->getUsernameOptimal();
//                    $userId = $pathologist->getId();
//                    //2. Total number of projects (XXX) per PI (Top 5/10) (APPROVED & CLOSED)
//                    if( isset($pathologistProjectCountMultiArr[$userId]) && isset($pathologistProjectCountMultiArr[$userId]['value']) ) {
//                        $count = $pathologistProjectCountMultiArr[$userId]['value'] + 1;
//                    } else {
//                        $count = 1;
//                    }
//                    $pathologistProjectCountMultiArr[$userId]['value'] = $count;
//                    $pathologistProjectCountMultiArr[$userId]['label'] = $userName;
//                    $pathologistProjectCountMultiArr[$userId]['objectid'] = $userId;
//                    $pathologistProjectCountMultiArr[$userId]['pi'] = $userId;
//                    //$pathologistProjectCountMultiArr[$userId]['show-path'] = "project";

                    if (isset($pathologistProjectCountArr[$userName])) {
                        $count = $pathologistProjectCountArr[$userName] + 1;
                    } else {
                        $count = 1;
                    }
                    $pathologistProjectCountArr[$userName] = $count;

                    //$titleCount = $titleCount + $count;
                }

                $titleCount++;
            }

//            $showOther = $this->getOtherStr($showLimited,"Pathologist Involved");
//            $piProjectCountMultiTopArr = $this->getTopMultiArray($pathologistProjectCountMultiArr,$showOther); // getTopMultiArray(
//            $filterArr['funded'] = null;
//            $chartsArray = $this->getChartByMultiArray( $piProjectCountMultiTopArr, $filterArr, "2a. Total number of projects per Pathologist Involved (Top 10)","pie",null," : ");

            $showOther = $this->getOtherStr($showLimited,"pathologists involved");
            $pathologistProjectCountTopArr = $this->getTopArray($pathologistProjectCountArr,$showOther,$quantityLimit);

            $totalSegmentCount = $this->getTotalSegmentCount($pathologistProjectCountTopArr);
            $chartName = $this->getTitleWithTotal($chartName,$totalSegmentCount,null,"project requests ($titleCount unique) total");

            $chartsArray = $this->getChart($pathologistProjectCountTopArr,$chartName,'pie',$layoutArray," : ",null,null,"percent+label");

        }
        ///////////////// EOF 2a. Total number of projects per Pathologist Involved (Top 10) /////////////////
        // 6. Total number of Funded Projects per Pathologist Involved (Top 10)
        if( $chartType == "funded-projects-per-pathologist-involved" ) {
            $pathologistFundedProjectCountArr = array();

            $projects = $this->getProjectsByFilter($startDate,$endDate,$projectSpecialtyObjects);

            foreach($projects as $project) {
                $fundingNumber = $project->getFunded();
                if( $fundingNumber ) {

                    $pathologists = $project->getPathologists();
                    foreach ($pathologists as $pathologist) {
                        $userName = $pathologist->getUsernameOptimal();
                        if (isset($pathologistFundedProjectCountArr[$userName])) {
                            $count = $pathologistFundedProjectCountArr[$userName] + 1;
                        } else {
                            $count = 1;
                        }
                        $pathologistFundedProjectCountArr[$userName] = $count;
                    }//foreach $pathologists

                    $titleCount++;
                }
            }//foreach $projects

            $showOther = $this->getOtherStr($showLimited,"pathologists involved");
            $pathologistFundedProjectCountTopArr = $this->getTopArray($pathologistFundedProjectCountArr,$showOther,$quantityLimit);

            $totalSegmentCount = $this->getTotalSegmentCount($pathologistFundedProjectCountTopArr);
            $chartName = $this->getTitleWithTotal($chartName,$totalSegmentCount,null,"project requests ($titleCount unique) total");

            $filterArr['funded'] = true;
            $chartsArray = $this->getChart($pathologistFundedProjectCountTopArr, $chartName,"pie",$layoutArray," : ",null,null,"percent+label");
        }
        ///////////////// EOF 3a. Total number of Funded Projects per Pathologist Involved (Top 10) /////////////////
        // 7. Total number of Non-Funded Projects per Pathologist Involved (Top 10)
        if( $chartType == "nonfunded-projects-per-pathologist-involved" ) {
            $pathologistNonFundedProjectCountArr = array();

            $projects = $this->getProjectsByFilter($startDate,$endDate,$projectSpecialtyObjects);

            foreach($projects as $project) {
                $fundingNumber = $project->getFunded();
                if( $fundingNumber ) {
                    //do nothing
                } else {

                    $pathologists = $project->getPathologists();
                    foreach ($pathologists as $pathologist) {
                        $userName = $pathologist->getUsernameOptimal();

                        if (isset($pathologistNonFundedProjectCountArr[$userName])) {
                            $count = $pathologistNonFundedProjectCountArr[$userName] + 1;
                        } else {
                            $count = 1;
                        }
                        $pathologistNonFundedProjectCountArr[$userName] = $count;

                        $titleCount = $titleCount + $count;
                    }//foreach $pathologists

                    $titleCount++;
                }
            }//foreach $projects

            $showOther = $this->getOtherStr($showLimited,"pathologists involved");
            $pathologistNonFundedProjectCountTopArr = $this->getTopArray($pathologistNonFundedProjectCountArr,$showOther,$quantityLimit);

            $totalSegmentCount = $this->getTotalSegmentCount($pathologistNonFundedProjectCountTopArr);
            $chartName = $this->getTitleWithTotal($chartName,$totalSegmentCount,null,"project requests ($titleCount unique) total");

            //$filterArr['funded'] = true;
            $chartsArray = $this->getChart($pathologistNonFundedProjectCountTopArr, $chartName,"pie",$layoutArray," : ",null,null,"percent+label");
        }
        ///////////////// EOF 4a. Total number of Non-Funded Projects per Pathologist Involved (Top 10) /////////////////


        //Work request statistics
        //8. Total Number of Work Requests by Funding Source
        if( $chartType == "requests-by-funding-source" ) {

            $fundedRequestCount = 0;
            $notFundedRequestCount = 0;

            $fundedProjectArr = array();
            $unfundedProjectArr = array();

            $testArr = array();
            $testing = false;

            $requests = $this->getRequestsByFilter($startDate,$endDate,$projectSpecialtyObjects);
            foreach($requests as $transRequest) {
                $project = $transRequest->getProject();
                $projectId = $project->getId();
                $fundedAccountNumber = $transRequest->getFundedAccountNumber();
                $fundedAccountNumber = trim((string)$fundedAccountNumber);

                if($testing) {
                    //echo $transRequest->getOid().": fundedAccountNumber=[$fundedAccountNumber] <br>";
                    $testArr[$transRequest->getOid()]++;
                    //if( $fundedAccountNumber && strval($fundedAccountNumber) !== strval(intval($fundedAccountNumber)) ) {
                    if ($fundedAccountNumber && filter_var($fundedAccountNumber, FILTER_VALIDATE_INT) === false) {
                        echo $transRequest->getOid() . ": NOT INTEGER: [$fundedAccountNumber] <br>";
                    }
                }

                if( $fundedAccountNumber ) {
                    $fundedRequestCount++;
                    $fundedProjectArr[$projectId] = 1;
                } else {
                    $notFundedRequestCount++;
                    $unfundedProjectArr[$projectId] = 1;
                }
            }//foreach

            if($testing) {
                foreach ($testArr as $reqId => $reqCount) {
                    //echo $reqId." count=".$reqCount."<br>";
                    if ($reqCount != 1) {
                        echo $reqId . " !!!count=" . $reqCount . "<br>";
                    }
                }
                //print_r($testArr);
                exit("fundedRequestCount=$fundedRequestCount; notFundedRequestCount=$notFundedRequestCount");
            }

            $chartName = $this->getTitleWithTotal($chartName,$fundedRequestCount+$notFundedRequestCount,null,"work requests total");

            $dataArray = array();
            $chartDataArray = array();
            $type = 'pie';

            $layoutArray = array(
                'height' => $this->height,
                'width' =>  $this->width,
                'title' => $chartName //"5. Total Number of Work Requests by Funding Source"
            );

            $fundedProjectCount = 0;
            foreach($fundedProjectArr as $projectCount) {
                $fundedProjectCount = $fundedProjectCount + $projectCount;
            }

            $unfundedProjectCount = 0;
            foreach($unfundedProjectArr as $projectCount) {
                $unfundedProjectCount = $unfundedProjectCount + $projectCount;
            }

            //Work Requests for 154 Funded Projects: 1298
            $fundedLabel = "Work Requests for $fundedProjectCount Funded Projects"." : ".$fundedRequestCount;
            //Work Requests for 12 Non-Funded Projects: 445
            $unfundedLabel = "Work Requests for $unfundedProjectCount Non-Funded Projects"." : ".$notFundedRequestCount;

            $labels = array($fundedLabel,$unfundedLabel);
            $values = array($fundedRequestCount,$notFundedRequestCount);

            $links = array();
            //////////// Funded ////////////
            $linkFilterArr = array(
                'filter[progressState][0]' => 'active',
                'filter[progressState][1]' => 'completed',
                'filter[progressState][2]' => 'completedNotified',
                'filter[progressState][3]' => 'pendingInvestigatorInput',
                'filter[progressState][4]' => 'pendingHistology',
                'filter[progressState][5]' => 'pendingImmunohistochemistry',
                'filter[progressState][6]' => 'pendingMolecular',
                'filter[progressState][7]' => 'pendingCaseRetrieval',
                'filter[progressState][8]' => 'pendingTissueMicroArray',
                'filter[progressState][9]' => 'pendingSlideScanning',
                'filter[startDate]' => $startDateStr,
                'filter[endDate]' => $endDateStr,
                'filter[fundingType]' => 'Funded'
            );

            $link = $this->container->get('router')->generate(
                'translationalresearch_request_index_filter',
                $linkFilterArr,
                UrlGeneratorInterface::ABSOLUTE_URL
            );
            $links[] = $link;
            //////////// EOF Funded ////////////
            //////////// Non-Funded ////////////
            $linkFilterArr = array(
                'filter[progressState][0]' => 'active',
                'filter[progressState][1]' => 'completed',
                'filter[progressState][2]' => 'completedNotified',
                'filter[progressState][3]' => 'pendingInvestigatorInput',
                'filter[progressState][4]' => 'pendingHistology',
                'filter[progressState][5]' => 'pendingImmunohistochemistry',
                'filter[progressState][6]' => 'pendingMolecular',
                'filter[progressState][7]' => 'pendingCaseRetrieval',
                'filter[progressState][8]' => 'pendingTissueMicroArray',
                'filter[progressState][9]' => 'pendingSlideScanning',
                'filter[startDate]' => $startDateStr,
                'filter[endDate]' => $endDateStr,
                'filter[fundingType]' => 'Non-Funded'
            );

            $link = $this->container->get('router')->generate(
                'translationalresearch_request_index_filter',
                $linkFilterArr,
                UrlGeneratorInterface::ABSOLUTE_URL
            );
            $links[] = $link;
            //////////// EOF Non-Funded ////////////

            $chartDataArray['values'] = $values;
            $chartDataArray['labels'] = $labels;
            $chartDataArray['type'] = $type;
            $chartDataArray["textinfo"] = "value+percent";
            $chartDataArray["outsidetextfont"] = array('size'=>1,'color'=>'white');
            $chartDataArray['direction'] = 'clockwise';
            $chartDataArray["hoverinfo"] = "percent+label";
            $chartDataArray["links"] = $links;

            $dataArray[] = $chartDataArray;

            $chartsArray = array(
                'layout' => $layoutArray,
                'data' => $dataArray
            );
        }

        //9. Projects with Most Work Requests (Top 10)
        if( $chartType == "requests-per-project" ) {
            $requestPerProjectArr = array();

            $requests = $this->getRequestsByFilter($startDate,$endDate,$projectSpecialtyObjects);
            foreach($requests as $transRequest) {
                $project = $transRequest->getProject();
                $projectId = $project->getId();
                $piIdArr = array();

                //in the legend after the project ID show the name of the PI: projectID by username: XXXX
                $projectIndex = $project->getOid(false);
                $piArr = array();
                foreach($transRequest->getPrincipalInvestigators() as $pi) {
                    $piArr[] = $pi->getUsernameOptimal();
                }
                if( count($piArr) > 0 ) {
                    $projectIndex = $projectIndex . " by " . implode(", ",$piArr);
                }

                if( isset($requestPerProjectArr[$projectId]) && isset($requestPerProjectArr[$projectId]['value']) ) {
                    $count = $requestPerProjectArr[$projectId]['value'] + 1;
                } else {
                    $count = 1;
                }
                $requestPerProjectArr[$projectId]['value'] = $count;
                $requestPerProjectArr[$projectId]['label'] = $projectIndex;
                $requestPerProjectArr[$projectId]['objectid'] = $projectId;
                $requestPerProjectArr[$projectId]['pi'] = $piIdArr;
                $requestPerProjectArr[$projectId]['show-path'] = "request";

                $titleCount++;
            }//foreach

            $layoutArray = array(
                'height' => $this->height,
                'width' => $this->width,
            );

            $chartName = $this->getTitleWithTotal($chartName,$titleCount,null,"work requests total");
            $showOther = $this->getOtherStr($showLimited,"projects");
            $requestPerProjectTopArr = $this->getTopMultiArray($requestPerProjectArr,$showOther,$quantityLimit);
            $filterArr['funded'] = null;
            $chartsArray = $this->getChartByMultiArray($requestPerProjectTopArr, $filterArr, $chartName,"pie",$layoutArray," : ","percent+label");
        }

        //10. Funded Projects with Most Work Requests (Top 10)
        if( $chartType == "requests-per-funded-projects" ) {
            $fundedRequestPerProjectArr = array();

            $requests = $this->getRequestsByFilter($startDate,$endDate,$projectSpecialtyObjects);
            foreach($requests as $transRequest) {

                if( $transRequest->getFundedAccountNumber() ) {
                    $project = $transRequest->getProject();
                    $projectId = $project->getId();
                    $piIdArr = array();

                    //in the legend after the project ID show the name of the PI: projectID by username: XXXX
                    $projectIndex = $project->getOid(false);
                    $piArr = array();
                    foreach($transRequest->getPrincipalInvestigators() as $pi) {
                        $piArr[] = $pi->getUsernameOptimal();
                    }
                    if( count($piArr) > 0 ) {
                        $projectIndex = $projectIndex . " by " . implode(", ",$piArr);
                    }

                    if( isset($fundedRequestPerProjectArr[$projectId]) && isset($fundedRequestPerProjectArr[$projectId]['value']) ) {
                        $count = $fundedRequestPerProjectArr[$projectId]['value'] + 1;
                    } else {
                        $count = 1;
                    }
                    $fundedRequestPerProjectArr[$projectId]['value'] = $count;
                    $fundedRequestPerProjectArr[$projectId]['label'] = $projectIndex;
                    $fundedRequestPerProjectArr[$projectId]['objectid'] = $projectId;
                    $fundedRequestPerProjectArr[$projectId]['pi'] = $piIdArr;
                    $fundedRequestPerProjectArr[$projectId]['show-path'] = "request";
                    $titleCount++;
                }
            }

            $chartName = $this->getTitleWithTotal($chartName,$titleCount,null,"work requests total");
            $showOther = $this->getOtherStr($showLimited,"projects");
            $fundedRequestPerProjectTopArr = $this->getTopMultiArray($fundedRequestPerProjectArr,$showOther,$quantityLimit);
            $filterArr['funded'] = true;
            $chartsArray = $this->getChartByMultiArray( $fundedRequestPerProjectTopArr, $filterArr, $chartName,"pie",$layoutArray," : ","percent+label");
        }

        //11. Non-Funded Projects with Most Work Requests (Top 10)
        if( $chartType == "requests-per-nonfunded-projects" ) {
            $unFundedRequestPerProjectArr = array();

            $requests = $this->getRequestsByFilter($startDate,$endDate,$projectSpecialtyObjects);
            foreach($requests as $transRequest) {
                if( $transRequest->getFundedAccountNumber() ) {
                    //do nothing
                } else {
                    $project = $transRequest->getProject();
                    $projectId = $project->getId();
                    $piIdArr = array();

                    //in the legend after the project ID show the name of the PI: projectID by username: XXXX
                    $projectIndex = $project->getOid(false);
                    $piArr = array();
                    foreach($transRequest->getPrincipalInvestigators() as $pi) {
                        $piArr[] = $pi->getUsernameOptimal();
                    }
                    if( count($piArr) > 0 ) {
                        $projectIndex = $projectIndex . " by " . implode(", ",$piArr);
                    }

                    if( isset($unFundedRequestPerProjectArr[$projectId]) && isset($unFundedRequestPerProjectArr[$projectId]['value']) ) {
                        $count = $unFundedRequestPerProjectArr[$projectId]['value'] + 1;
                    } else {
                        $count = 1;
                    }
                    $unFundedRequestPerProjectArr[$projectId]['value'] = $count;
                    $unFundedRequestPerProjectArr[$projectId]['label'] = $projectIndex;
                    $unFundedRequestPerProjectArr[$projectId]['objectid'] = $projectId;
                    $unFundedRequestPerProjectArr[$projectId]['pi'] = $piIdArr;
                    $unFundedRequestPerProjectArr[$projectId]['show-path'] = "request";
                    $titleCount++;
                }
            }

            $chartName = $this->getTitleWithTotal($chartName,$titleCount,null,"work requests total");
            $showOther = $this->getOtherStr($showLimited,"projects");
            $unFundedRequestPerProjectTopArr = $this->getTopMultiArray($unFundedRequestPerProjectArr,$showOther,$quantityLimit);
            $filterArr['funded'] = false;
            $chartsArray = $this->getChartByMultiArray( $unFundedRequestPerProjectTopArr, $filterArr, $chartName,"pie",$layoutArray," : ","percent+label");
        }

        //Work request statistics: Products/Services
        //12. Service Productivity by Products/Services (Top 35)
        if( $chartType == "service-productivity-by-service" ) {
            $quantityCountByCategoryArr = array();

            $requests = $this->getRequestsByFilter($startDate,$endDate,$projectSpecialtyObjects);
            foreach($requests as $transRequest) {
                foreach($transRequest->getProducts() as $product) {
                    $category = $product->getCategory();
                    if( $category ) {
                        $categoryIndex = $category->getProductIdAndName();
                        $productQuantity = $product->getQuantity();
                        //9. TRP Service Productivity by Category Types (Top 10)
                        if (isset($quantityCountByCategoryArr[$categoryIndex])) {
                            $count = $quantityCountByCategoryArr[$categoryIndex] + $productQuantity;
                        } else {
                            $count = $productQuantity;
                        }
                        $quantityCountByCategoryArr[$categoryIndex] = $count;
                        /////////////
                        $titleCount = $titleCount + $productQuantity;
                    }
                }
            }//foreach $requests

            $chartName = $this->getTitleWithTotal($chartName,$titleCount,null,"items total");
            $showOther = $this->getOtherStr($showLimited,"items");
            //                                              $piProjectCountArr, $showOthers=false, $descriptionArr=array(), $maxLen=50, $limit=10
            $quantityCountByCategoryTopArr = $this->getTopArray(
                $quantityCountByCategoryArr,    //$dataCountArr
                $showOther,                     //$showOthers
                $quantityLimit
            //array(),                        //$descriptionArr=array()
            //50                              //$maxLen=50
            //35                              //$limit
            );
            $layoutArray = array(
                'height' => $this->height,
                'width' => $this->width,
            );
            $chartsArray = $this->getChart($quantityCountByCategoryTopArr, $chartName,'pie',$layoutArray," : ",null,null,"percent+label");
        }

        //13. Service Productivity for Funded Projects (Top 25)
        if( $chartType == "service-productivity-by-service-per-funded-projects" ) {
            $fundedQuantityCountByCategoryArr = array();

            $requests = $this->getRequestsByFilter($startDate,$endDate,$projectSpecialtyObjects);
            foreach($requests as $transRequest) {
                foreach($transRequest->getProducts() as $product) {
                    $category = $product->getCategory();
                    if( $category ) {
                        $categoryIndex = $category->getProductIdAndName();
                        $productQuantity = $product->getQuantity();
                        //10. Service Productivity for Funded Projects (Top 25)
                        if( $transRequest->getFundedAccountNumber() ) {
                            //10. Service Productivity for Funded Projects (Top 25)
                            if (isset($fundedQuantityCountByCategoryArr[$categoryIndex])) {
                                $count = $fundedQuantityCountByCategoryArr[$categoryIndex] + $productQuantity;
                            } else {
                                $count = $productQuantity;
                            }
                            $fundedQuantityCountByCategoryArr[$categoryIndex] = $count;
                            $titleCount = $titleCount + $productQuantity;
                        }
                    }
                }
            }//foreach $requests

            $chartName = $this->getTitleWithTotal($chartName,$titleCount,null,"items total");
            $showOther = $this->getOtherStr($showLimited,"items");
            $fundedQuantityCountByCategoryTopArr = $this->getTopArray(
                $fundedQuantityCountByCategoryArr,
                $showOther,
                $quantityLimit
            //array(),                        //$descriptionArr=array()
            //50,                             //$maxLen=50
            //25                              //$limit
            );
            $chartsArray = $this->getChart($fundedQuantityCountByCategoryTopArr, $chartName,'pie',$layoutArray," : ",null,null,"percent+label");
        }

        //14. Service Productivity for Non-Funded Projects (Top 10)
        if( $chartType == "service-productivity-by-service-per-nonfunded-projects" ) {
            $unFundedQuantityCountByCategoryArr = array();

            $requests = $this->getRequestsByFilter($startDate,$endDate,$projectSpecialtyObjects);
            foreach($requests as $transRequest) {
                foreach($transRequest->getProducts() as $product) {
                    $category = $product->getCategory();
                    if( $category ) {
                        $categoryIndex = $category->getProductIdAndName();
                        $productQuantity = $product->getQuantity();
                        //10. TRP Service Productivity for Funded Projects (Top 10)
                        if( $transRequest->getFundedAccountNumber() ) {
                            //do nothing
                        } else {
                            //11. Service Productivity for Non-Funded Projects (Top 10)
                            if (isset($unFundedQuantityCountByCategoryArr[$categoryIndex])) {
                                $count = $unFundedQuantityCountByCategoryArr[$categoryIndex] + $productQuantity;
                            } else {
                                $count = $productQuantity;
                            }
                            $unFundedQuantityCountByCategoryArr[$categoryIndex] = $count;
                            $titleCount = $titleCount + $productQuantity;
                        }
                    }
                }
            }//foreach $requests

            $chartName = $this->getTitleWithTotal($chartName,$titleCount,null,"items total");
            $showOther = $this->getOtherStr($showLimited,"items");
            $unFundedQuantityCountByCategoryTopArr = $this->getTopArray(
                $unFundedQuantityCountByCategoryArr,
                $showOther,
                $quantityLimit
            //array(),                        //$descriptionArr=array()
            //50,                             //$maxLen=50
            //25                              //$limit
            );
            $chartsArray = $this->getChart($unFundedQuantityCountByCategoryTopArr, $chartName,'pie',$layoutArray," : ",null,null,"percent+label");
        }

        //"15. Service Productivity: Items for Funded vs Non-Funded Projects" => "service-productivity-by-service-compare-funded-vs-nonfunded-projects"
        if( $chartType == "service-productivity-by-service-compare-funded-vs-nonfunded-projects" ) {
            $fundedQuantityCountByCategoryArr = array();
            $unFundedQuantityCountByCategoryArr = array();
            $stackDataSumArray = array();

            $requests = $this->getRequestsByFilter($startDate,$endDate,$projectSpecialtyObjects);
            foreach($requests as $transRequest) {
                foreach($transRequest->getProducts() as $product) {
                    $category = $product->getCategory();
                    if( $category ) {
                        $categoryIndex = $category->getProductIdAndName();
                        $productQuantity = $product->getQuantity();
                        if( $transRequest->getFundedAccountNumber() ) {
                            //10. TRP Service Productivity for Funded Projects (Top 10)
                            if (isset($fundedQuantityCountByCategoryArr[$categoryIndex])) {
                                $count = $fundedQuantityCountByCategoryArr[$categoryIndex] + $productQuantity;
                            } else {
                                $count = $productQuantity;
                            }
                            $fundedQuantityCountByCategoryArr[$categoryIndex] = $count;
                        } else {
                            //11. Service Productivity for Non-Funded Projects (Top 10)
                            if (isset($unFundedQuantityCountByCategoryArr[$categoryIndex])) {
                                $count = $unFundedQuantityCountByCategoryArr[$categoryIndex] + $productQuantity;
                            } else {
                                $count = $productQuantity;
                            }
                            $unFundedQuantityCountByCategoryArr[$categoryIndex] = $count;
                        }
                        $titleCount = $titleCount + $productQuantity;

                        if( isset($stackDataSumArray[$categoryIndex]) ) {
                            $sum = $stackDataSumArray[$categoryIndex] + $productQuantity;
                        } else {
                            $sum = $productQuantity;
                        }
                        $stackDataSumArray[$categoryIndex] = $sum;
                    }
                }
            }//foreach $requests

            //sort by value in key=>value
            arsort($stackDataSumArray);
            $fundedSortedArr = array();
            $unfundedSortedArr = array();
            foreach($stackDataSumArray as $categoryIndex=>$count) {
                //echo $categoryIndex."=".$count."<br>";

                if( array_key_exists($categoryIndex,$fundedQuantityCountByCategoryArr) ) {
                    $fundedSortedArr[$categoryIndex] = $fundedQuantityCountByCategoryArr[$categoryIndex];
                }

                if( array_key_exists($categoryIndex,$unFundedQuantityCountByCategoryArr) ) {
                    $unfundedSortedArr[$categoryIndex] = $unFundedQuantityCountByCategoryArr[$categoryIndex];
                }
            }

            $chartName = $this->getTitleWithTotal($chartName,$titleCount,null,"items total");
            //$showOther = $this->getOtherStr($showLimited,"projects");
            //$fundedQuantityCountByCategoryTopArr = $this->getTopArray($fundedQuantityCountByCategoryArr,$showOther);
            //$unFundedQuantityCountByCategoryTopArr = $this->getTopArray($unFundedQuantityCountByCategoryArr,$showOther);

            //increase vertical
            $layoutArray = array(
                'height' => $this->height*2,
                'width' => $this->width,
                'title' => $chartName,
                'margin' => array('b' => 800)
            );

            $combinedTrpData = array();
            $combinedTrpData['Funded'] = $fundedSortedArr; //$fundedQuantityCountByCategoryArr;
            $combinedTrpData['Not-Funded'] = $unfundedSortedArr; //$unFundedQuantityCountByCategoryArr;
            $chartsArray = $this->getStackedChart($combinedTrpData, $chartName, "stack", $layoutArray);
        }

        //16. Total Fees of Items Ordered for Funded vs Non-Funded Projects
        if( $chartType == "fees-by-requests" ) {
            $transresRequestUtil = $this->container->get('transres_request_util');
            $subtotalFees = 0;
            $fundedTotalFees = 0;
            $unFundedTotalFees = 0;

            $requests = $this->getRequestsByFilter($startDate,$endDate,$projectSpecialtyObjects);
            foreach($requests as $transRequest) {

                $subtotalFee = intval($transresRequestUtil->getTransResRequestSubTotal($transRequest));
                $subtotalFees = $subtotalFees + $subtotalFee;

                if( $transRequest->getFundedAccountNumber() ) {
                    $fundedTotalFees = $fundedTotalFees + $subtotalFee;
                } else {
                    $unFundedTotalFees = $unFundedTotalFees + $subtotalFee;
                }

                $titleCount++;
            }//foreach $requests

            //12. Total Fees of Items Ordered for Funded vs Non-Funded Projects (Total $)
            $dataArray = array();
            $chartDataArray = array();
            $type = 'pie';
            $subtotalFees = $this->getNumberFormat($subtotalFees);

            $chartName = $chartName. " (Total $".$subtotalFees.")";

            $layoutArray = array(
                'height' => $this->height,
                'width' =>  $this->width,
                'title' => $chartName
            );

            $fundedTotalFeesLabel = $this->getNumberFormat($fundedTotalFees);
            $unFundedTotalFeesLabel = $this->getNumberFormat($unFundedTotalFees);

            $labels = array('Funded : $'.$fundedTotalFeesLabel,'Non-Funded : $'.$unFundedTotalFeesLabel);
            $values = array($fundedTotalFees,$unFundedTotalFees);

            $chartDataArray['values'] = $values;
            $chartDataArray['labels'] = $labels;
            $chartDataArray['type'] = $type;
            $chartDataArray["textinfo"] = "value+percent";
            $chartDataArray["outsidetextfont"] = array('size'=>1,'color'=>'white');
            $chartDataArray['direction'] = 'clockwise';
            $chartDataArray["hoverinfo"] = "percent+label";
            $dataArray[] = $chartDataArray;

            $chartsArray = array(
                'layout' => $layoutArray,
                'data' => $dataArray
            );
            /////////////////////

        }

        //17. Funded Projects with the Highest Total Fees (Top 10)
        if( $chartType == "fees-by-requests-per-funded-projects" ) {
            $transresRequestUtil = $this->container->get('transres_request_util');
            $fundedTotalFeesByRequestArr = array();

            $requests = $this->getRequestsByFilter($startDate,$endDate,$projectSpecialtyObjects);
            foreach($requests as $transRequest) {

                $project = $transRequest->getProject();
                $projectIndex = $project->getOid(false);
                //$pis = $project->getPrincipalInvestigators();
                $pis = $project->getAllPrincipalInvestigators();
                $piInfoArr = array();
                foreach( $pis as $pi ) {
                    if( $pi ) {
                        $piInfoArr[] = $pi->getUsernameOptimal();
                    }
                }
                if( count($piInfoArr) > 0 ) {
                    $projectIndex = $projectIndex . " (" . implode(", ",$piInfoArr) . ")";
                }

                $subtotalFee = intval($transresRequestUtil->getTransResRequestSubTotal($transRequest));
                //$subtotalFees = $subtotalFees + $subtotalFee;

                //17. Funded Projects with the Highest Total Fees (Top 10)
                if( $transRequest->getFundedAccountNumber() ) {
                    if (isset($fundedTotalFeesByRequestArr[$projectIndex])) {
                        $totalFee = $fundedTotalFeesByRequestArr[$projectIndex] + $subtotalFee;
                    } else {
                        $totalFee = $subtotalFee;
                    }
                    //$totalFee = $this->getNumberFormat($totalFee);
                    $fundedTotalFeesByRequestArr[$projectIndex] = $totalFee;

                    $titleCount = $titleCount + $subtotalFee;
                }

            }//foreach $requests

            $chartName = $this->getTitleWithTotal($chartName,$this->getNumberFormat($titleCount),"$");
            $showOther = $this->getOtherStr($showLimited,"projects");
            $fundedTotalFeesByRequestTopArr = $this->getTopArray($fundedTotalFeesByRequestArr,$showOther,$quantityLimit);
            $chartsArray = $this->getChart($fundedTotalFeesByRequestTopArr, $chartName,'pie',$layoutArray," : $",null,null,"percent+label");
        }

        //18. Non-Funded Projects with the Highest Total Fees (Top 10)
        if( $chartType == "fees-by-requests-per-nonfunded-projects" ) {
            $transresRequestUtil = $this->container->get('transres_request_util');
            $unFundedTotalFeesByRequestArr = array();

            $requests = $this->getRequestsByFilter($startDate,$endDate,$projectSpecialtyObjects);

            foreach($requests as $transRequest) {

                $project = $transRequest->getProject();
                $projectIndex = $project->getOid(false);
                //$pis = $project->getPrincipalInvestigators();
                $pis = $project->getAllPrincipalInvestigators();
                $piInfoArr = array();
                foreach( $pis as $pi ) {
                    if( $pi ) {
                        $piInfoArr[] = $pi->getUsernameOptimal();
                    }
                }
                if( count($piInfoArr) > 0 ) {
                    $projectIndex = $projectIndex . " (" . implode(", ",$piInfoArr) . ")";
                }

                $subtotalFee = intval($transresRequestUtil->getTransResRequestSubTotal($transRequest));
                //echo "projectIndex=[".$project->getOid()."] <br>";
                //$subtotalFees = $subtotalFees + $subtotalFee;

                if( $transRequest->getFundedAccountNumber() ) {
                    //do nothing
                } else {
                    if (isset($unFundedTotalFeesByRequestArr[$projectIndex])) {
                        $totalFee = $unFundedTotalFeesByRequestArr[$projectIndex] + $subtotalFee;
                    } else {
                        $totalFee = $subtotalFee;
                    }
                    //$totalFee = $this->getNumberFormat($totalFee);
                    $unFundedTotalFeesByRequestArr[$projectIndex] = $totalFee;

                    $titleCount = $titleCount + $subtotalFee;
                }

            }//foreach $requests

            $chartName = $this->getTitleWithTotal($chartName,$this->getNumberFormat($titleCount),"$");
            $showOther = $this->getOtherStr($showLimited,"projects");
            $unFundedTotalFeesByRequestTopArr = $this->getTopArray($unFundedTotalFeesByRequestArr,$showOther,$quantityLimit);
            $chartsArray = $this->getChart($unFundedTotalFeesByRequestTopArr, $chartName,'pie',$layoutArray," : $",null,null,"percent+label");
        }

        //19. Total Fees per Investigator (Top 10)
        if( $chartType == "fees-by-investigators" ) {
            $transresRequestUtil = $this->container->get('transres_request_util');
            $totalFeesByInvestigatorArr = array();

            $requests = $this->getRequestsByFilter($startDate,$endDate,$projectSpecialtyObjects);
            foreach($requests as $transRequest) {

                $pis = $transRequest->getPrincipalInvestigators();
                if( count($pis) > 0 ) {
                    $pi = $pis[0];
                    $investigatorIndex = $pi->getUsernameOptimal();
                }

                $subtotalFee = intval($transresRequestUtil->getTransResRequestSubTotal($transRequest));
                //$subtotalFees = $subtotalFees + $subtotalFee;

                //15. Total Fees per Investigator (Top 10)
                if (isset($totalFeesByInvestigatorArr[$investigatorIndex])) {
                    $totalFee = $totalFeesByInvestigatorArr[$investigatorIndex] + $subtotalFee;
                } else {
                    $totalFee = $subtotalFee;
                }
                //$totalFee = $this->getNumberFormat($totalFee);
                $totalFeesByInvestigatorArr[$investigatorIndex] = $totalFee;
                /////////////////////////////

                $titleCount = $titleCount + $subtotalFee;

            }//foreach $requests

            $chartName = $this->getTitleWithTotal($chartName,$this->getNumberFormat($titleCount),"$");
            $showOther = $this->getOtherStr($showLimited,"Investigators");
            $totalFeesByInvestigatorTopArr = $this->getTopArray($totalFeesByInvestigatorArr,$showOther,$quantityLimit);
            $chartsArray = $this->getChart($totalFeesByInvestigatorTopArr, $chartName,'pie',$layoutArray," : $",null,null,"percent+label");
        }

        //20. Total Fees per Investigator (Funded) (Top 10)
        if( $chartType == "fees-by-investigators-per-funded-projects" ) {
            $transresRequestUtil = $this->container->get('transres_request_util');
            $fundedTotalFeesByInvestigatorArr = array();

            $requests = $this->getRequestsByFilter($startDate,$endDate,$projectSpecialtyObjects);
            foreach($requests as $transRequest) {

                $pis = $transRequest->getPrincipalInvestigators();
                if( count($pis) > 0 ) {
                    $pi = $pis[0];
                    $investigatorIndex = $pi->getUsernameOptimal();
                }

                $subtotalFee = intval($transresRequestUtil->getTransResRequestSubTotal($transRequest));
                //$subtotalFees = $subtotalFees + $subtotalFee;

                if( $transRequest->getFundedAccountNumber() ) {
                    if (isset($fundedTotalFeesByInvestigatorArr[$investigatorIndex])) {
                        $totalFee = $fundedTotalFeesByInvestigatorArr[$investigatorIndex] + $subtotalFee;
                    } else {
                        $totalFee = $subtotalFee;
                    }
                    //$totalFee = $this->getNumberFormat($totalFee);
                    $fundedTotalFeesByInvestigatorArr[$investigatorIndex] = $totalFee;

                    $titleCount = $titleCount + $subtotalFee;
                }

            }//foreach $requests

            $chartName = $this->getTitleWithTotal($chartName,$this->getNumberFormat($titleCount),"$");
            $showOther = $this->getOtherStr($showLimited,"Investigators");
            $fundedTotalFeesByInvestigatorTopArr = $this->getTopArray($fundedTotalFeesByInvestigatorArr,$showOther,$quantityLimit);
            $chartsArray = $this->getChart($fundedTotalFeesByInvestigatorTopArr, $chartName,'pie',$layoutArray," : $",null,null,"percent+label");
        }

        //21. Total Fees per Investigator (Non-Funded) (Top 10)
        if( $chartType == "fees-by-investigators-per-nonfunded-projects" ) {
            $transresRequestUtil = $this->container->get('transres_request_util');
            $unFundedTotalFeesByInvestigatorArr = array();

            $requests = $this->getRequestsByFilter($startDate,$endDate,$projectSpecialtyObjects);
            foreach($requests as $transRequest) {

                $pis = $transRequest->getPrincipalInvestigators();
                if( count($pis) > 0 ) {
                    $pi = $pis[0];
                    $investigatorIndex = $pi->getUsernameOptimal();
                }

                $subtotalFee = intval($transresRequestUtil->getTransResRequestSubTotal($transRequest));
                //$subtotalFees = $subtotalFees + $subtotalFee;

                if( $transRequest->getFundedAccountNumber() ) {
                    //do nothing
                } else {
                    //17. Total Fees per Investigator (non-Funded) (Top 10)
                    if (isset($unFundedTotalFeesByInvestigatorArr[$investigatorIndex])) {
                        $totalFee = $unFundedTotalFeesByInvestigatorArr[$investigatorIndex] + $subtotalFee;
                    } else {
                        $totalFee = $subtotalFee;
                    }
                    //$totalFee = $this->getNumberFormat($totalFee);
                    $unFundedTotalFeesByInvestigatorArr[$investigatorIndex] = $totalFee;

                    $titleCount = $titleCount + $subtotalFee;
                }

            }//foreach $requests

            $chartName = $this->getTitleWithTotal($chartName,$this->getNumberFormat($titleCount),"$");
            $showOther = $this->getOtherStr($showLimited,"Investigators");
            $unFundedTotalFeesByInvestigatorTopArr = $this->getTopArray($unFundedTotalFeesByInvestigatorArr,$showOther,$quantityLimit); //21vs25
            $chartsArray = $this->getChart($unFundedTotalFeesByInvestigatorTopArr, $chartName,'pie',$layoutArray," : $",null,null,"percent+label");
        }

        //"22. Paid Invoices by Month" =>                              "fees-by-invoices-paid-per-month",
        if( $chartType == "fees-by-invoices-paid-per-month" ) {

            $paidArr = array();
            $descriptionArr = array();

            $totalPaidInvoiceFee = 0;

            $invoiceStates = array("Paid in Full","Paid Partially");
            $compareType = "date when status changed to paid in full";

            //$startDate->modify( 'first day of last month' );
            $startDate->modify( 'first day of this month' );
            do {
                $startDateLabel = $startDate->format('M-Y');
                //echo "startDateLabel=".$startDateLabel."<br>";
                $thisEndDate = clone $startDate;
                //$thisEndDate->modify( 'first day of next month' );
                $thisEndDate->modify('last day of this month');
                //echo "StartDate=".$startDate->format("d-M-Y")."; EndDate=".$thisEndDate->format("d-M-Y").": ";

                $invoices = $this->getInvoicesByFilter($startDate,$thisEndDate,$projectSpecialtyObjects,$invoiceStates,false,true,$compareType);
                //get invoices by paidDate

                $invoiceIds = array();

                $startDate->modify( 'first day of next month' );

                foreach( $invoices as $invoice ) {

                    $paidThisInvoiceFee = intval($invoice->getPaid());


                    if( isset($paidArr[$startDateLabel]) ) {
                        $paidDateInvoiceFee = $paidArr[$startDateLabel] + $paidThisInvoiceFee;
                    } else {
                        $paidDateInvoiceFee = $paidThisInvoiceFee;
                    }

                    $paidArr[$startDateLabel] = $paidDateInvoiceFee;
                    //echo $startDateLabel.": paidThisInvoiceFee=".$paidThisInvoiceFee."<br>";
                    //echo intval($invoice->getPaid())."<br>";

                    $totalPaidInvoiceFee = $totalPaidInvoiceFee + $paidThisInvoiceFee;

                    $invoiceIds[] = $invoice->getId();
                }

                $addInfoStr = "";
                //$addInfoStr = ", IDS=".implode("; ",$invoiceIds);

                $descriptionArr[$startDateLabel] = " (" . count($invoices) . " invoices".$addInfoStr.")";

            } while( $startDate < $endDate );

            //echo "totalPaidInvoiceFee=".$totalPaidInvoiceFee."<br>"; //7591754 7.591.754
            //exit('111');

            $chartName = $this->getTitleWithTotal($chartName,$this->getNumberFormat($totalPaidInvoiceFee),"$","Total");

            //increase vertical
            $layoutArray = array(
                'height' => $this->height*1.3,
                'width' => $this->width,
                'title' => $chartName,
                'margin' => array('b' => 300)
            );

            //$dataArr, $title, $type='pie', $layoutArray=null, $valuePrefixLabel=null, $valuePostfixLabel=null, $descriptionArr=array()
            $chartsArray = $this->getChart($paidArr,$chartName,'bar',$layoutArray,"$",null,$descriptionArr,"percent+label");
        }

        //23. Generated Invoices by Status for Funded Projects
        if( $chartType == "fees-by-invoices-per-funded-projects" ) {

            $paidInvoices = 0;
            $unpaidInvoices = 0;
            $totalInvoices = 0;
            $totalFundedPaidFees = 0;
            $totalFundedDueFees = 0;
            $totalThisInvoiceVerificationFees = 0;

            $invoices = $this->getInvoicesByFilter($startDate, $endDate, $projectSpecialtyObjects);
            foreach( $invoices as $invoice ) {

                $transRequest = $invoice->getTransresRequest();
                $paidThisInvoiceFee = intval($invoice->getPaid());
                $dueThisInvoiceFee = intval($invoice->getDue());

                //18. Generated Invoices by Status from Funded Projects (Total invoiced $152K)
                if ($transRequest->getFundedAccountNumber()) {
                    if( $invoice->getStatus() == "Paid in Full" || $invoice->getStatus() == "Paid Partially" ) {
                        $paidInvoices++;
                    }
                    if( $invoice->getStatus() == "Unpaid/Issued" ) {
                        $unpaidInvoices++;
                    }
                    $totalInvoices++;
                    $totalFundedPaidFees = $totalFundedPaidFees + $paidThisInvoiceFee;
                    $totalFundedDueFees = $totalFundedDueFees + $dueThisInvoiceFee;
                    $totalThisInvoiceVerificationFees = $totalThisInvoiceVerificationFees + ($paidThisInvoiceFee + $dueThisInvoiceFee);
                }
                //////////////////////////////////////////////

            }//foreach invoices

            //18. Generated Invoices by Status from Funded Projects (Total invoiced $152K)
            //22. Generated Invoices by Status for Funded Projects
            $dataArray = array();
            $chartDataArray = array();
            $type = 'pie';

            $layoutArray = array(
                'height' => $this->height,
                'width' =>  $this->width,
                //'title' => "18. Generated Invoices from Funded Projects (Total invoiced $".$totalThisInvoiceVerificationFees."; Total invoices: ".$totalInvoices.", 'Paid in Full' invoices: ".$paidInvoices.")"
                'title' => $chartName." (Total invoiced $".$this->getNumberFormat($totalThisInvoiceVerificationFees)
                    ."; Total invoices: ".$totalInvoices.", 'Paid in Full' invoices: ".$paidInvoices.")"
            );

            $labels = array(
                $paidInvoices.' Paid Invoices'.' : $'.$this->getNumberFormat($totalFundedPaidFees),                 //78 Paid Invoices: $xx
                $unpaidInvoices.' Unpaid (Due) Invoices'.' : $'.$this->getNumberFormat($totalFundedDueFees)  //154 Unpaid (Due) Invoices: $xx
            );
            $values = array($totalFundedPaidFees,$totalFundedDueFees);

            $chartDataArray['values'] = $values;
            $chartDataArray['labels'] = $labels;
            $chartDataArray['type'] = $type;
            $chartDataArray["textinfo"] = "value+percent";
            $chartDataArray["outsidetextfont"] = array('size'=>1,'color'=>'white');
            $chartDataArray['marker'] = array('colors' => array("rgb(44, 160, 44)", "rgb(214, 39, 40)") );
            $chartDataArray['direction'] = 'clockwise';
            $chartDataArray["hoverinfo"] = "percent+label";
            $dataArray[] = $chartDataArray;

            $chartsArray = array(
                'layout' => $layoutArray,
                'data' => $dataArray
            );
            /////////////////////////////
        }

        //"24. Generated Invoices by Status for Non-Funded Projects (Top 10)" =>  "fees-by-invoices-per-nonfunded-projects"
        if( $chartType == "fees-by-invoices-per-nonfunded-projects" ) {
            $invoicesByProjectArr = array();
            $invoicesFeesByProjectArr = array();
            $totalInvoices = 0;

            $invoices = $this->getInvoicesByFilter($startDate, $endDate, $projectSpecialtyObjects);
            foreach( $invoices as $invoice ) {

                $transRequest = $invoice->getTransresRequest();
                $project = $transRequest->getProject();
                $projectIndex = $project->getOid(false);
                //$pis = $project->getPrincipalInvestigators();
                $pis = $project->getAllPrincipalInvestigators();
                $piInfoArr = array();
                foreach( $pis as $pi ) {
                    if( $pi ) {
                        $piInfoArr[] = $pi->getUsernameOptimal();
                    }
                }
                if( count($piInfoArr) > 0 ) {
                    $projectIndex = $projectIndex . " (" . implode(", ",$piInfoArr) . ")";
                }

                $totalThisInvoiceFee = intval($invoice->getTotal());

                //Generated Invoices by Status for Non-Funded Projects
                if( $transRequest->getFundedAccountNumber() ) {
                    //do nothing
                } else {
                    //Generated Invoices by Status per Funded Project (Top 10)
                    if (isset($invoicesByProjectArr[$projectIndex])) {
                        $count = $invoicesByProjectArr[$projectIndex] + 1;
                    } else {
                        $count = 1;
                    }
                    $invoicesByProjectArr[$projectIndex] = $count;
                    //fees
                    if (isset($invoicesFeesByProjectArr[$projectIndex])) {
                        $totalFee = $invoicesFeesByProjectArr[$projectIndex] + $totalThisInvoiceFee;
                    } else {
                        $totalFee = $totalThisInvoiceFee;
                    }
                    //$totalFee = 123456;
                    $invoicesFeesByProjectArr[$projectIndex] = $totalFee;

                    $titleCount = $titleCount + $totalThisInvoiceFee;
                    $totalInvoices++;
                }

            }//foreach invoices

            //invoice vs invoices
            $invoiceStr = "invoice";
            if( $totalInvoices > 1 ) {
                $invoiceStr = "invoices";
            }

            $chartName = $this->getTitleWithTotal($chartName,$this->getNumberFormat($titleCount),"$");
            //Generated Invoices by Status per Funded Project (Top 10)
            $showOther = $this->getOtherStr($showLimited,"projects");
            $invoicesByProjectTopArr = $this->getTopArray($invoicesByProjectArr,$showOther,$quantityLimit);
            $invoicesFeesByProjectTopArr = $this->getTopArray($invoicesFeesByProjectArr,$showOther,$quantityLimit);
            //merge two to attach fees to label
            $invoicesByProjectTopArr = $this->attachSecondValueToFirstLabel($invoicesByProjectTopArr,$invoicesFeesByProjectTopArr," : $");
            $chartsArray = $this->getChart($invoicesByProjectTopArr,$chartName." (".$totalInvoices." ".$invoiceStr.")",'pie',$layoutArray,null,null,null,"percent+label");

            if( is_array($chartsArray) && count($chartsArray) == 0 ) {
                $warningNoData = "There are no invoices associated with un-funded project requests during the selected time frame.".
                    "<br>Chart '$chartName' has not been generated.";
            }
        }

        //25. Total Invoiced Amounts by PI (Top 10)
        if( $chartType == "fees-by-invoices-per-pi" ) {
            $invoicesFeesByPiArr = array();
            $invoicePaidFeeArr = array();
            $invoiceDueFeeArr = array();
            $totalInvoices = 0;

            $invoices = $this->getInvoicesByFilter($startDate, $endDate, $projectSpecialtyObjects);
            foreach( $invoices as $invoice ) {

                $transRequest = $invoice->getTransresRequest();

                if( $invoice ) {
                    $investigator = $invoice->getPrincipalInvestigator();
                    if ($investigator) {
                        $investigatorIndex = $investigator->getUsernameOptimal();
                    } else {
                        $submitter = $invoice->getSubmitter();
                        $investigatorIndex = $submitter->getUsernameOptimal();
                    }
                } else {
                    $pis = $transRequest->getPrincipalInvestigators();
                    if( count($pis) > 0 ) {
                        $pi = $pis[0];
                        $investigatorIndex = $pi->getUsernameOptimal();
                    }
                }

                $totalThisInvoiceFee = intval($invoice->getTotal());
                $paidThisInvoiceFee = intval($invoice->getPaid());
                $dueThisInvoiceFee = intval($invoice->getDue());

                //24. Generated Invoices by Status per PI (Top 10)
                //if( $transRequest->getFundedAccountNumber() ) { //TODO: why funded?
                //Total fees
                if (isset($invoicesFeesByPiArr[$investigatorIndex])) {
                    $totalFee = $invoicesFeesByPiArr[$investigatorIndex] + $totalThisInvoiceFee;
                } else {
                    $totalFee = $totalThisInvoiceFee;
                }
                //$totalFee = 123456;
                $invoicesFeesByPiArr[$investigatorIndex] = $totalFee;

                //paid
                if (isset($invoicePaidFeeArr[$investigatorIndex])) {
                    $totalFee = $invoicePaidFeeArr[$investigatorIndex] + $paidThisInvoiceFee;
                } else {
                    $totalFee = $paidThisInvoiceFee;
                }
                $invoicePaidFeeArr[$investigatorIndex] = $totalFee;

                //unpaid
                if (isset($invoiceDueFeeArr[$investigatorIndex])) {
                    $totalFee = $invoiceDueFeeArr[$investigatorIndex] + $dueThisInvoiceFee;
                } else {
                    $totalFee = $dueThisInvoiceFee;
                }
                $invoiceDueFeeArr[$investigatorIndex] = $totalFee;

                $titleCount = $titleCount + $totalThisInvoiceFee;
                $totalInvoices++;
                //}

            }//foreach invoices

            $chartName = $this->getTitleWithTotal($chartName,$this->getNumberFormat($titleCount),"$");
            $chartName = $chartName." (".$totalInvoices." invoices)";

            //24. Generated Invoices by Status per PI (Top 10)
            $descriptionArr = array(
                array(
                    'descrPrefix'   => "paid $",
                    'descrPostfix'  => null,
                    'valuePrefix'   => " : $",
                    'valuePostfix'  => null,
                    'descrColor'    => "limegreen",
                    'descrType'     => "money",
                    'descrValueArr' => $invoicePaidFeeArr
                ),
                array(
                    'descrPrefix'   => "due $",
                    'descrPostfix'  => null,
                    'valuePrefix'   => " : $",
                    'valuePostfix'  => null,
                    'descrColor'    => "red",
                    'descrType'     => "money",
                    'descrValueArr' => $invoiceDueFeeArr
                ),
            );

            $showOther = $this->getOtherStr($showLimited,"PIs"); //21vs25
            $invoicesFeesByPiArrTop = $this->getTopArray($invoicesFeesByPiArr,$showOther,$quantityLimit,$descriptionArr);

            //attach value to other
            //$invoicesFeesByPiArrTop = $this->addValueToOther($invoicesFeesByPiArrTop);

            $chartsArray = $this->getChart($invoicesFeesByPiArrTop,$chartName,'pie',$layoutArray,null,null,null,"percent+label");

            //$dataArr, $title, $type='pie', $layoutArray=null, $valuePrefixLabel=null, $valuePostfixLabel=null, $descriptionArr=null, $hoverinfo=null
            //$chartsArray = $this->getChart($invoicesFeesByPiArrTop,$chartName,'pie',$layoutArray," : $",null,null,"percent+label","for-other");
        }

        //"26. Total Invoiced Amounts of Projects per Pathologist Involved (Top 10)" =>             "fees-by-invoices-per-projects-per-pathologist-involved",
        if( $chartType == "fees-by-invoices-per-projects-per-pathologist-involved" ) {
            $invoicesFeesByPathologistArr = array();
            $invoicePaidFeeArr = array();
            $invoiceDueFeeArr = array();
            $totalInvoices = 0;

            //get latest invoices Excluding Work requests with status=Canceled and Draft
            $invoices = $this->getInvoicesByFilter($startDate, $endDate, $projectSpecialtyObjects);
            foreach( $invoices as $invoice ) {

                $totalThisInvoiceFee = intval($invoice->getTotal());
                $paidThisInvoiceFee = intval($invoice->getPaid());
                $dueThisInvoiceFee = intval($invoice->getDue());

                $transRequest = $invoice->getTransresRequest();
                $project = $transRequest->getProject();
                $pathologists = $project->getPathologists();

                foreach($pathologists as $pathologist) {
                    $pathologistIndex = $pathologist->getUsernameOptimal();

                    //Total fees
                    if (isset($invoicesFeesByPathologistArr[$pathologistIndex])) {
                        $totalFee = $invoicesFeesByPathologistArr[$pathologistIndex] + $totalThisInvoiceFee;
                    } else {
                        $totalFee = $totalThisInvoiceFee;
                    }
                    $invoicesFeesByPathologistArr[$pathologistIndex] = $totalFee;

                    //paid
                    if (isset($invoicePaidFeeArr[$pathologistIndex])) {
                        $totalFee = $invoicePaidFeeArr[$pathologistIndex] + $paidThisInvoiceFee;
                    } else {
                        $totalFee = $paidThisInvoiceFee;
                    }
                    $invoicePaidFeeArr[$pathologistIndex] = $totalFee;

                    //unpaid
                    if (isset($invoiceDueFeeArr[$pathologistIndex])) {
                        $totalFee = $invoiceDueFeeArr[$pathologistIndex] + $dueThisInvoiceFee;
                    } else {
                        $totalFee = $dueThisInvoiceFee;
                    }
                    $invoiceDueFeeArr[$pathologistIndex] = $totalFee;

                    $titleCount = $titleCount + $totalThisInvoiceFee;
                }

                $totalInvoices++;

            }//foreach invoices

            $chartName = $this->getTitleWithTotal($chartName,$this->getNumberFormat($titleCount),"$");
            $chartName = $chartName." (".$totalInvoices." invoices)";

            $descriptionArr = array(
                array(
                    'descrPrefix'   => "paid $",
                    'descrPostfix'  => null,
                    'valuePrefix'   => " : $",
                    'valuePostfix'  => null,
                    'descrColor'    => "limegreen",
                    'descrType'     => "money",
                    'descrValueArr' => $invoicePaidFeeArr
                ),
                array(
                    'descrPrefix'   => "due $",
                    'descrPostfix'  => null,
                    'valuePrefix'   => " : $",
                    'valuePostfix'  => null,
                    'descrColor'    => "red",
                    'descrType'     => "money",
                    'descrValueArr' => $invoiceDueFeeArr
                ),
            );

            $showOther = $this->getOtherStr($showLimited,"pathologists involved");
            $invoicesFeesByPathologistArrTop = $this->getTopArray($invoicesFeesByPathologistArr,$showOther,$quantityLimit,$descriptionArr);

            //attach value to other
            //$invoicesFeesByPathologistArrTop = $this->addValueToOther($invoicesFeesByPathologistArrTop);

            $chartsArray = $this->getChart($invoicesFeesByPathologistArrTop, $chartName,'pie',$layoutArray,null,null,null,"percent+label");
        }

        //"27. Total Invoiced Amounts for Funded Projects per Pathologist Involved (Top 10)" =>      "fees-by-invoices-per-funded-projects-per-pathologist-involved"
        if( $chartType == "fees-by-invoices-per-funded-projects-per-pathologist-involved" ) {
            $invoicesFeesByPathologistArr = array();
            $invoicePaidFeeArr = array();
            $invoiceDueFeeArr = array();
            $totalInvoices = 0;

            //get latest invoices Excluding Work requests with status=Canceled and Draft
            $invoices = $this->getInvoicesByFilter($startDate, $endDate, $projectSpecialtyObjects);
            foreach( $invoices as $invoice ) {

                $totalThisInvoiceFee = intval($invoice->getTotal());
                $paidThisInvoiceFee = intval($invoice->getPaid());
                $dueThisInvoiceFee = intval($invoice->getDue());

                $transRequest = $invoice->getTransresRequest();
                $project = $transRequest->getProject();
                $pathologists = $project->getPathologists();

                if ($transRequest->getFundedAccountNumber()) {
                    foreach ($pathologists as $pathologist) {
                        $pathologistIndex = $pathologist->getUsernameOptimal();

                        //Total fees
                        if (isset($invoicesFeesByPathologistArr[$pathologistIndex])) {
                            $totalFee = $invoicesFeesByPathologistArr[$pathologistIndex] + $totalThisInvoiceFee;
                        } else {
                            $totalFee = $totalThisInvoiceFee;
                        }
                        $invoicesFeesByPathologistArr[$pathologistIndex] = $totalFee;

                        //paid
                        if (isset($invoicePaidFeeArr[$pathologistIndex])) {
                            $totalFee = $invoicePaidFeeArr[$pathologistIndex] + $paidThisInvoiceFee;
                        } else {
                            $totalFee = $paidThisInvoiceFee;
                        }
                        $invoicePaidFeeArr[$pathologistIndex] = $totalFee;

                        //unpaid
                        if (isset($invoiceDueFeeArr[$pathologistIndex])) {
                            $totalFee = $invoiceDueFeeArr[$pathologistIndex] + $dueThisInvoiceFee;
                        } else {
                            $totalFee = $dueThisInvoiceFee;
                        }
                        $invoiceDueFeeArr[$pathologistIndex] = $totalFee;

                        $titleCount = $titleCount + $totalThisInvoiceFee;
                    }
                    $totalInvoices++;
                }

            }//foreach invoices

            $chartName = $this->getTitleWithTotal($chartName,$this->getNumberFormat($titleCount),"$");
            $chartName = $chartName." (".$totalInvoices." invoices)";

            $descriptionArr = array(
                array(
                    'descrPrefix'   => "paid $",
                    'descrPostfix'  => null,
                    'valuePrefix'   => " : $",
                    'valuePostfix'  => null,
                    'descrColor'    => "limegreen",
                    'descrType'     => "money",
                    'descrValueArr' => $invoicePaidFeeArr
                ),
                array(
                    'descrPrefix'   => "due $",
                    'descrPostfix'  => null,
                    'valuePrefix'   => " : $",
                    'valuePostfix'  => null,
                    'descrColor'    => "red",
                    'descrType'     => "money",
                    'descrValueArr' => $invoiceDueFeeArr
                ),
            );

            $showOther = $this->getOtherStr($showLimited,"pathologists involved");
            $invoicesFeesByPathologistArrTop = $this->getTopArray($invoicesFeesByPathologistArr,$showOther,$quantityLimit,$descriptionArr);

            //attach value to other
            //$invoicesFeesByPathologistArrTop = $this->addValueToOther($invoicesFeesByPathologistArrTop);

            $chartsArray = $this->getChart($invoicesFeesByPathologistArrTop, $chartName,'pie',$layoutArray,null,null,null,"percent+label");
        }
        ///////////////// EOF "26. Total Invoiced Amounts of Funded Projects per Pathologist Involved (Top 10)" /////////////////

        //"28. Total Invoiced Amounts for Non-Funded Projects per Pathologist Involved (Top 10)" =>  "fees-by-invoices-per-nonfunded-projects-per-pathologist-involved"
        if( $chartType == "fees-by-invoices-per-nonfunded-projects-per-pathologist-involved" ) {
            $invoicesFeesByPathologistArr = array();
            $invoicePaidFeeArr = array();
            $invoiceDueFeeArr = array();
            $totalInvoices = 0;

            //get latest invoices Excluding Work requests with status=Canceled and Draft
            $invoices = $this->getInvoicesByFilter($startDate, $endDate, $projectSpecialtyObjects);
            foreach( $invoices as $invoice ) {

                $totalThisInvoiceFee = intval($invoice->getTotal());
                $paidThisInvoiceFee = intval($invoice->getPaid());
                $dueThisInvoiceFee = intval($invoice->getDue());

                $transRequest = $invoice->getTransresRequest();
                $project = $transRequest->getProject();
                $pathologists = $project->getPathologists();

                if( $transRequest->getFundedAccountNumber() ) {
                    //do nothing
                } else {
                    foreach($pathologists as $pathologist) {
                        $pathologistIndex = $pathologist->getUsernameOptimal();

                        //Total fees
                        if (isset($invoicesFeesByPathologistArr[$pathologistIndex])) {
                            $totalFee = $invoicesFeesByPathologistArr[$pathologistIndex] + $totalThisInvoiceFee;
                        } else {
                            $totalFee = $totalThisInvoiceFee;
                        }
                        $invoicesFeesByPathologistArr[$pathologistIndex] = $totalFee;

                        //paid
                        if (isset($invoicePaidFeeArr[$pathologistIndex])) {
                            $totalFee = $invoicePaidFeeArr[$pathologistIndex] + $paidThisInvoiceFee;
                        } else {
                            $totalFee = $paidThisInvoiceFee;
                        }
                        $invoicePaidFeeArr[$pathologistIndex] = $totalFee;

                        //unpaid
                        if (isset($invoiceDueFeeArr[$pathologistIndex])) {
                            $totalFee = $invoiceDueFeeArr[$pathologistIndex] + $dueThisInvoiceFee;
                        } else {
                            $totalFee = $dueThisInvoiceFee;
                        }
                        $invoiceDueFeeArr[$pathologistIndex] = $totalFee;

                        $titleCount = $titleCount + $totalThisInvoiceFee;
                    }
                    $totalInvoices++;
                }

            }//foreach invoices

            $chartName = $this->getTitleWithTotal($chartName,$this->getNumberFormat($titleCount),"$");

            $descriptionArr = array(
                array(
                    'descrPrefix'   => "paid $",
                    'descrPostfix'  => null,
                    'valuePrefix'   => " : $",
                    'valuePostfix'  => null,
                    'descrColor'    => "limegreen",
                    'descrType'     => "money",
                    'descrValueArr' => $invoicePaidFeeArr
                ),
                array(
                    'descrPrefix'   => "due $",
                    'descrPostfix'  => null,
                    'valuePrefix'   => " : $",
                    'valuePostfix'  => null,
                    'descrColor'    => "red",
                    'descrType'     => "money",
                    'descrValueArr' => $invoiceDueFeeArr
                ),
            );

            if( $totalInvoices > 1 ) {
                $totalInvoicesStr = "invoices";
            } else {
                $totalInvoicesStr = "invoice";
            }

            $showOther = $this->getOtherStr($showLimited,"pathologists involved");
            $invoicesFeesByPathologistArrTop = $this->getTopArray($invoicesFeesByPathologistArr,$showOther,$quantityLimit,$descriptionArr);
            $chartsArray = $this->getChart($invoicesFeesByPathologistArrTop, $chartName." (".$totalInvoices." $totalInvoicesStr)",'pie',$layoutArray,null,null,null,"percent+label");

            if( is_array($chartsArray) && count($chartsArray) == 0 ) {
                $warningNoData = "There are no invoices associated with un-funded project requests that specify an involved pathologist during the selected time frame.".
                    "<br>Chart '$chartName' has not been generated.";
            }
        }

        //"29. Total Fees per Involved Pathologist for Non-Funded Projects (Top 10)" =>  "fees-per-nonfunded-projects-per-pathologist-involved",
        if( $chartType == "fees-per-nonfunded-projects-per-pathologist-involved" ) {
            $transresRequestUtil = $this->container->get('transres_request_util');
            $invoicesFeesByPathologistArr = array();
            $requests = $this->getRequestsByFilter($startDate,$endDate,$projectSpecialtyObjects);
            foreach($requests as $transRequest) {

                if( $transRequest->getFundedAccountNumber() ) {
                    //do nothing
                } else {

                    $subtotalFee = intval($transresRequestUtil->getTransResRequestSubTotal($transRequest));

                    $project = $transRequest->getProject();
                    $pathologists = $project->getPathologists();

                    foreach ($pathologists as $pathologist) {
                        $pathologistIndex = $pathologist->getUsernameOptimal();

                        //Total fees
                        if (isset($invoicesFeesByPathologistArr[$pathologistIndex])) {
                            $totalFee = $invoicesFeesByPathologistArr[$pathologistIndex] + $subtotalFee;
                        } else {
                            $totalFee = $subtotalFee;
                        }
                        $invoicesFeesByPathologistArr[$pathologistIndex] = $totalFee;

                        $titleCount = $titleCount + $subtotalFee;
                    }
                }//if

            }//foreach $requests

            $chartName = $this->getTitleWithTotal($chartName,$this->getNumberFormat($titleCount),"$");

            $showOther = $this->getOtherStr($showLimited,"pathologists involved");
            $invoicesFeesByPathologistArrTop = $this->getTopArray($invoicesFeesByPathologistArr,$showOther,$quantityLimit);
            $chartsArray = $this->getChart($invoicesFeesByPathologistArrTop, $chartName,'pie',$layoutArray,": $");
        }
        ///////////// EOF "23. Total Invoiced Amounts of Non-Funded Projects per Pathologist Involved (Top 10)" /////////////

        //"30. Total Number of Projects per Type" => "projects-per-type"
        if( $chartType == "projects-per-type" ) {
            $projectTypeArr = array();

            $projects = $this->getProjectsByFilter($startDate,$endDate,$projectSpecialtyObjects);
            foreach($projects as $project) {
                $projectType = $project->getProjectType();
                if( $projectType ) {
                    $projectId = $projectType->getId();
                    $projectName = $projectType->getName();
                } else {
                    $projectId = "No Type";
                    $projectName = "No Type";;
                }

                if( isset($projectTypeArr[$projectId]) && isset($projectTypeArr[$projectId]['value']) ) {
                    $count = $projectTypeArr[$projectId]['value'] + 1;
                } else {
                    $count = 1;
                }
                $projectTypeArr[$projectId]['value'] = $count;
                $projectTypeArr[$projectId]['label'] = $projectName;
                $projectTypeArr[$projectId]['objectid'] = $projectId;
                $projectTypeArr[$projectId]['pi'] = null;
                $projectTypeArr[$projectId]['show-path'] = "project-type";

                $titleCount++;
            }

            $chartName = $this->getTitleWithTotal($chartName,$titleCount,null,"projects total");

            $showOther = $this->getOtherStr($showLimited,"Project Types");
            $projectTypeArrTop = $this->getTopMultiArray($projectTypeArr,$showOther,$quantityLimit);
            $chartsArray = $this->getChartByMultiArray( $projectTypeArrTop, $filterArr, $chartName,"pie",null," : ","percent+label");
        }


        //"31. Total Number of Requests per Business Purpose" => "requests-per-business-purpose"
        if( $chartType == "requests-per-business-purpose" ) {
            $requestBusinessPurposeArr = array();

            $requests = $this->getRequestsByFilter($startDate,$endDate,$projectSpecialtyObjects);
            foreach($requests as $transRequest) {

                $businessPurposes = $transRequest->getBusinessPurposes();

                foreach($businessPurposes as $businessPurpose) {
                    $businessPurposeName = $businessPurpose->getName();
                    if (isset($requestBusinessPurposeArr[$businessPurposeName])) {
                        $count = $requestBusinessPurposeArr[$businessPurposeName] + 1;
                    } else {
                        $count = 1;
                    }
                    $requestBusinessPurposeArr[$businessPurposeName] = $count;

                    $titleCount++;
                }
            }

            $chartName = $this->getTitleWithTotal($chartName,$titleCount,null,"work requests total");

            $showOther = $this->getOtherStr($showLimited,"Business Purposes");
            $requestBusinessPurposeArrTop = $this->getTopArray($requestBusinessPurposeArr,$showOther,$quantityLimit);
            $chartsArray = $this->getChart($requestBusinessPurposeArrTop, $chartName,'pie',$layoutArray," : ",null,null,"percent+label");
        }

        //"32. Turn-around Statistics: Average number of days to complete a Work Request (based on Completed and Notified requests)" => "turn-around-statistics-days-complete-request"
        if( $chartType == "turn-around-statistics-days-complete-request" ) {
            $averageDays = array();

            //$statuses = array("completed","completedNotified");
            $statuses = array("completedNotified");

            $globalCount = 0;
            $globalDays = 0;

            //$startDate->modify( 'first day of last month' );
            $startDate->modify( 'first day of this month' );
            do {
                $startDateLabel = $startDate->format('M-Y');
                $thisEndDate = clone $startDate;
                //$thisEndDate->modify( 'first day of next month' );
                $thisEndDate->modify('last day of this month');
                //echo "StartDate=".$startDate->format("d-M-Y")."; EndDate=".$thisEndDate->format("d-M-Y").": ";

                $transRequests = $this->getRequestsByAdvanceFilter($startDate,$thisEndDate,$projectSpecialtyObjects,$productservice,$statuses);

                $startDate->modify( 'first day of next month' );

                //echo "<br>";
                //echo "transRequests=".count($transRequests)." (".$startDateLabel.")<br>";

                $daysTotal = 0;
                $count = 0;

                foreach($transRequests as $transRequest) {

                    //Number of days to go from Submitted to Completed
                    $submitted = $transRequest->getCreateDate();

                    //$updated = $transRequest->getUpdateDate(); //assumption: the update date for completed requests is the same as $completedDate
                    $completed = $transRequest->getCompletedDate();
                    if( !$completed ) {
                        continue;
                    }

//                    $dDiff = $submitted->diff($completed);
//                    //echo $dDiff->format('%R'); // use for point out relation: smaller/greater
//                    $days = $dDiff->days;
//                    //echo "days=".$days."<br>";
//                    $days = intval($days);

                    $days = $this->calculateDays($submitted,$completed);

                    if( $days > 0 ) {
                        $daysTotal = $daysTotal + intval($days);
                        $count++;
                    }
                }

                //$transRequestsCount = count($transRequests);
                //if( $transRequestsCount ) {
                //$startDateLabel = $startDateLabel . " (" . $transRequestsCount . " requests)";
                //}
                $startDateLabel = $startDateLabel . " (" . count($transRequests) . " requests)";

                if( $count > 0 ) {
                    $avgDaysInt = round($daysTotal/$count);
                    $averageDays[$startDateLabel] = $avgDaysInt;

                    $globalCount++;
                    $globalDays = $globalDays + $avgDaysInt;

                } else {
                    $averageDays[$startDateLabel] = null;
                }


            } while( $startDate < $endDate );

//            if( $category ) {
//                //$categoryName = $this->tokenTruncate($category->getProductIdAndName(),50);
//                $categoryName = $category->getProductId();
//                $categoryStr = " (".$categoryName.")";
//            } else {
//                $categoryStr = null;
//            }
//            $chartName = $chartName.$categoryStr;

            //average days: number of days (5.9) to complete
            if( $globalCount ) {
                $avgDaysInt = round($globalDays / $globalCount);
                $avgDaysIntStr = "number of days ($avgDaysInt) to complete";
                $chartName = str_replace("number of days to complete",$avgDaysIntStr,$chartName);
            }

            $chartsArray = $this->getChart($averageDays,$chartName,'bar',$layoutArray);
        }

        //"33. Turn-around Statistics: Number of days to complete each Work Request (based on 'Completed and Notified' requests)" => "turn-around-statistics-days-complete-per-request",
        if( $chartType == "turn-around-statistics-days-complete-per-request" ) {
            $averageDays = array();

            $thisEndDate = clone $startDate;
            //$thisEndDate->modify( 'first day of next month' );
            $thisEndDate->modify('last day of this month');

            //$statuses = array("completed","completedNotified");
            $statuses = array("completedNotified");
            $transRequests = $this->getRequestsByAdvanceFilter($startDate,$thisEndDate,$projectSpecialtyObjects,$productservice,$statuses);

            $daysTotal = 0;
            //$count = 0;

            foreach($transRequests as $transRequest) {

                //Number of days to go from Submitted to Completed
                $submitted = $transRequest->getCreateDate();

                $completed = $transRequest->getCompletedDate();
                if( !$completed ) {
                    continue;
                }

//                $dDiff = $submitted->diff($completed);
//                //echo $dDiff->format('%R'); // use for point out relation: smaller/greater
//                $days = $dDiff->days;
//                //echo "days=".$days."<br>";
//                $days = intval($days);
                $days = $this->calculateDays($submitted,$completed);

                if( $days > 0 ) {
                    $daysTotal = $daysTotal + intval($days);
                    //$count++;
                }

                $index = $transRequest->getOid();

                $link = $this->container->get('router')->generate(
                    'translationalresearch_request_show',
                    array("id"=>$transRequest->getId()),
                    UrlGeneratorInterface::ABSOLUTE_URL
                );

                if( isset($averageDays[$index]) ) {
                    //$averageDays[$index] = $averageDays[$index] + $days;
                    //$existingArr = $averageDays[$index];
                    //$existingDays = $existingArr["value"];
                    $existingDays = $averageDays[$index]["value"];
                    $days = $days + $existingDays;
                    //$averageDays[$index] = array("value"=>$days,"link"=>$link);
                }
                //minimum 1 day
                if( !$days || $days == 0 ) {
                    $days = 1;
                }
                //$averageDays[$index] = $days;
                $averageDays[$index] = array("value"=>$days,"link"=>$link);

                //$averageDays[$startDateLabel] = array("value"=>$avgDaysInt,"link"=>$link);
                //$averageDays[$startDateLabel] = $avgDaysInt;

            }//foreach

            $layoutArray = array(
                'height' => $this->height,
                'width' =>  $this->width,
                'title' => $chartName,
                'margin' => array('b'=>200)
            );

            $chartsArray = $this->getChart($averageDays, $chartName,'bar',$layoutArray);
        }

        //"34. Turn-around Statistics: Number of days to complete each Work Request by products/services (based on 'Completed and Notified' requests)" => "turn-around-statistics-days-complete-per-request-with-product",
        if( $chartType == "turn-around-statistics-days-complete-per-request-with-product" ) {
            $averageDays = array();

            $thisEndDate = clone $startDate;
            $thisEndDate->modify('last day of this month');

            $statuses = array("completedNotified");
            $transRequests = $this->getRequestsByAdvanceFilter($startDate, $thisEndDate, $projectSpecialtyObjects, $productservice, $statuses);

            $requestCategoryWeightQuantityArr = array();

            foreach ($transRequests as $transRequest) {

                //Number of days to go from Submitted to Completed
                $submitted = $transRequest->getCreateDate();

                $completed = $transRequest->getCompletedDate();
                if (!$completed) {
                    continue;
                }

                //1) calculate days
//                $dDiff = $submitted->diff($completed);
//                //echo $dDiff->format('%R'); // use for point out relation: smaller/greater
//                $days = $dDiff->days;
//                //echo "days=".$days."<br>";
//                $days = intval($days);
                $days = $this->calculateDays($submitted,$completed);

                $index = $transRequest->getOid();

                //2) calculate weight
                $totalQuantity = 0;
                foreach ($transRequest->getProducts() as $product) {
                    $quantity = $product->getQuantity();
                    $totalQuantity = $totalQuantity + intval($quantity);
                }
                if( $totalQuantity ) {
                    $weight = $days / $totalQuantity;
                } else {
                    $weight = 1;
                }

//                $link = $this->container->get('router')->generate(
//                    'translationalresearch_request_show',
//                    array("id"=>$transRequest->getId()),
//                    UrlGeneratorInterface::ABSOLUTE_URL
//                );

                //3) convert quantity as weighted days
                foreach ($transRequest->getProducts() as $product) {
                    $quantity = $product->getQuantity();
                    $category = $product->getCategory();
                    if( $category ) {
                        $categoryIndex = $category->getShortInfo();
                        $weightedQuantity = $weight * $quantity;
                        //minimum 1 day
                        if( !$weightedQuantity || $weightedQuantity == 0 ) {
                            $weightedQuantity = 1;
                        }
                        $requestCategoryWeightQuantityArr[$categoryIndex][$index] = $weightedQuantity;
                        //$requestCategoryWeightQuantityArr[$categoryIndex][$index] = array("value"=>$weightedQuantity,"link"=>$link);
                    }
                }

            }//foreach

            $combinedTrpData = array();
            foreach($requestCategoryWeightQuantityArr as $categoryIndex=>$arr) {
                $combinedTrpData[$categoryIndex] = $arr;
            }

            //$projectIrbPhaseArr[$index] = $days;
            //$combinedTrpData = array();
            //$combinedTrpData['IRB Review'] = $projectIrbPhaseArr;
            //$chartsArray = $this->getStackedChart($combinedTrpData, $chartName, "stack");

            $layoutArray = array(
                'height' => $this->height,
                'width' => $this->width*1.2,
                'title' => $chartName,
                'margin' => array('b' => 200)
            );

            //exit("Exit");
            $chartsArray = $this->getStackedChart($combinedTrpData, $chartName, "stack");
        }

        //"35. Turn-around Statistics: Average number of days for each project request approval phase (linked)" => "turn-around-statistics-days-project-state"
        if( $chartType == "turn-around-statistics-days-project-state" ) {
            $transresUtil = $this->container->get('transres_util');

            $reviewStates = array("irb_review","admin_review","committee_review","final_review","irb_missinginfo");

            $projectStates = null;
            //$projectStates = array('final_approved','closed','final_rejected');

            //init array
            $averageDays = array();
            foreach($reviewStates as $state) {
                $stateLabel = $transresUtil->getStateLabelByName($state);
                $averageDays[$stateLabel] = 0;
            }

            $projects = $this->getProjectsByFilter($startDate, $endDate, $projectSpecialtyObjects, $projectStates);
            //echo "### $state projects count=".count($projects)."<br>";

            $totalDaysCount = 0;
            $totalDays = 0;
            $countArr = array();

            foreach ($projects as $project) {
                //echo "<br>############ ".$project->getOid()." ############ <br>";

                foreach($reviewStates as $state) {

                    $stateLabel = $transresUtil->getStateLabelByName($state);

                    $days = $this->getDiffDaysByProjectState($project, $state);
                    if ($days > 0) {
                        if( isset($averageDays[$stateLabel]) ) {
                            $averageDays[$stateLabel] = $averageDays[$stateLabel] + $days;
                        } else {
                            $averageDays[$stateLabel] = $days;
                        }

                        if( isset($countArr[$stateLabel]) ) {
                            $countArr[$stateLabel] = $countArr[$stateLabel] + 1;
                        } else {
                            $countArr[$stateLabel] = 1;
                        }

                        $totalDays = $totalDays + $days;
                        $totalDaysCount++;
                    }

                }//foreach state

            }//foreach project

            //exit("exit: $chartName");

            //links the entire graph it to the single filtered list of ALL 128 project requests, filtered by current status = “Approved” or “Closed”
            $linkFilterArr = array(
                'filter[state][0]' => 'final_approved',
                'filter[state][1]' => 'closed',
                'filter[startDate]' => $startDateStr,
                'filter[endDate]' => $endDateStr,
            );
            $link = $this->container->get('router')->generate(
                'translationalresearch_project_index',
                $linkFilterArr,
                UrlGeneratorInterface::ABSOLUTE_URL
            );


            $averageDaysNew = array();
            foreach($averageDays as $stateLabel=>$days) {
                if( isset($countArr[$stateLabel]) == false ) {
                    continue;
                }
                $count = $countArr[$stateLabel];
                if( $count > 0 ) {
                    //$stateLabel = $stateLabel . " (" . $count . " projects)";
                    $avgDaysInt = round($days / $count);
                    //$averageDaysNew[$stateLabel] = $avgDaysInt;
                    $daysArr = array("value"=>$avgDaysInt, "link"=>$link);
                    $averageDaysNew[$stateLabel] = $daysArr;
                }
            }

            //Calculate the total by adding average IRB Review days + Average Admin Review days + Average Committee Review days +
            // Average Final Review days and show it in the title after the word days: “Average number of days (33) for each…”
            if( $totalDaysCount > 0 ) {
                $averageDays = round($totalDays / $totalDaysCount);
                if( $averageDays > 0 ) {
                    $chartName = str_replace("Average number of days","Average number of days ($averageDays)",$chartName);
                }
            }

            $chartName = $chartName . " (based on " . count($projects) . " approved or closed projects)";

            $chartsArray = $this->getChart($averageDaysNew, $chartName,'bar',$layoutArray); // getChart
        }

        //"36. Turn-around Statistics: Number of days for each project request’s approval phase" => "turn-around-statistics-days-per-project-state"
        if( $chartType == "turn-around-statistics-days-per-project-state" ) {

            $projectIrbPhaseArr = array();
            $projectAdminPhaseArr = array();
            $projectCommitteePhaseArr = array();
            $projectFinalPhaseArr = array();

            $reviewStates = array("irb_review","admin_review","committee_review","final_review");

            //'final_approved' OR project.state = 'closed OR 'final_rejected'
            $projectStates = null;
            //$projectStates = array('final_approved','closed','final_rejected');
            $projects = $this->getProjectsByFilter($startDate, $endDate, $projectSpecialtyObjects, $projectStates);
            //echo "### $state projects count=".count($projects)."<br>";

            foreach ($projects as $project) {

                $index = $project->getOid();

                $link = $this->container->get('router')->generate(
                    'translationalresearch_project_show',
                    array("id"=>$project->getId()),
                    UrlGeneratorInterface::ABSOLUTE_URL
                );

                foreach($reviewStates as $state) {

                    $days = $this->getDiffDaysByProjectState($project, $state);

                    if ($days > 0) {
                        if ($state == "irb_review") {
                            //$projectIrbPhaseArr[$index] = $days;
                            $projectIrbPhaseArr[$index] = array("value"=>$days,"link"=>$link);
                        }
                        if ($state == "admin_review") {
                            //$projectAdminPhaseArr[$index] = $days;
                            $projectAdminPhaseArr[$index] = array("value"=>$days,"link"=>$link);
                        }
                        if ($state == "committee_review") {
                            //$projectCommitteePhaseArr[$index] = $days;
                            $projectCommitteePhaseArr[$index] = array("value"=>$days,"link"=>$link);
                        }
                        if ($state == "final_review") {
                            //$projectFinalPhaseArr[$index] = $days;
                            $projectFinalPhaseArr[$index] = array("value"=>$days,"link"=>$link);
                        }
                    }

                }//foreach state

            }//foreach project

            $chartName = $chartName . " (based on " . count($projects) . " approved or closed projects)";

            $irbTitle = $this->getStateTitleWithAverageDays('IRB Review',$projectIrbPhaseArr);
            $adminTitle = $this->getStateTitleWithAverageDays('Admin Review',$projectAdminPhaseArr);
            $committeeTitle = $this->getStateTitleWithAverageDays('Committee Review',$projectCommitteePhaseArr);
            $finalTitle = $this->getStateTitleWithAverageDays('Final Review',$projectFinalPhaseArr);

            $combinedTrpData = array();
            $combinedTrpData[$irbTitle] = $projectIrbPhaseArr;
            $combinedTrpData[$adminTitle] = $projectAdminPhaseArr;
            $combinedTrpData[$committeeTitle] = $projectCommitteePhaseArr;
            $combinedTrpData[$finalTitle] = $projectFinalPhaseArr;

            $chartsArray = $this->getStackedChart($combinedTrpData, $chartName, "stack");
        }

        //third bar graph showing how many days on average it took for Invoices to go from “Issued” to “Paid”
        //"37. Turn-around Statistics: Average number of days for invoices to be paid" =>                 "turn-around-statistics-days-paid-invoice"
        if( $chartType == "turn-around-statistics-days-paid-invoice" ) {
            $averageDays = array();

            $invoiceStates = array("Paid in Full","Paid Partially");

            //$startDate->modify( 'first day of last month' );
            $startDate->modify( 'first day of this month' );
            do {
                $startDateLabel = $startDate->format('M-Y');
                $thisEndDate = clone $startDate;
                //$thisEndDate->modify( 'first day of next month' );
                $thisEndDate->modify('last day of this month');
                //echo "StartDate=".$startDate->format("d-M-Y")."; EndDate=".$thisEndDate->format("d-M-Y").": ";

                //$startDate, $endDate, $projectSpecialties, $states=null, $overdue=false, $addOneEndDay=true, $compareType='last invoice generation date',$filterRequest=true
                $compareType = 'last invoice generation date';
                $overdue = false;
                //$addOneEndDay = true;
                $addOneEndDay = false;
                //$filterRequest = false;
                $filterRequest = true; //this option is not set in the invoice list and the result is different after the clicking the link
                // getInvoicesByFilter(
                $invoices = $this->getInvoicesByFilter($startDate,$thisEndDate,$projectSpecialtyObjects,$invoiceStates,$overdue,$addOneEndDay,$compareType,$filterRequest);

                //link each bar to the filtered list of invoices for the corresponding month and with status “fully paid” or “partially paid”
                //$dates = $datesArr[$date];
                $linkFilterArr = array(
                    'filter[status][0]' => "Paid in Full",
                    'filter[status][1]' => "Paid Partially",
                    'filter[startCreateDate]' => $startDate->format('m/d/Y'),
                    'filter[endCreateDate]' => $thisEndDate->format('m/d/Y'),
                    'filter[version]' => "Latest"
                );
                $link = $this->container->get('router')->generate(
                    'translationalresearch_invoice_index_filter',
                    $linkFilterArr,
                    UrlGeneratorInterface::ABSOLUTE_URL
                );

                $startDate->modify( 'first day of next month' );
                //$datesArr[$startDateLabel] = array('startDate'=>$startDate->format('m/d/Y'),'endDate'=>$thisEndDate->format('m/d/Y'));

                //echo "<br>";
                //echo "invoices=".count($invoices)." (".$startDateLabel.")<br>";

                $daysTotal = 0;
                $count = 0;

                foreach($invoices as $invoice) {
                    //echo "invoice=".$invoice->getOid()."<br>";
                    //Number of days to go from Submitted to Completed
                    $issued = $invoice->getIssuedDate(); //“Issued”
                    if( 0 && !$issued ) {
                        continue;
                        //all issued dates are pre-populated by http://127.0.0.1/order/translational-research/dashboard/graphs/populate-dates
                        $issued = $this->getInvoiceIssuedDate($invoice);
                    }
                    if( !$issued ) {
                        //exit('no issue date');
                        continue;
                        $issued = $invoice->getCreateDate();
                    }
                    $paid = $invoice->getPaidDate(); //“Paid”
                    if( !$paid ) {
                        //continue;
                        $paid = $invoice->getUpdateDate(); //“Paid”
                    }

//                    $dDiff = $issued->diff($paid);
//                    //echo $dDiff->format('%R'); // use for point out relation: smaller/greater
//                    $days = $dDiff->days;
//                    //echo "days=".$days."<br>";
//                    $days = intval($days);
                    $days = $this->calculateDays($issued,$paid);

                    if( $days > 0 ) {
                        $daysTotal = $daysTotal + intval($days);
                        $count++;
                    }
                }

                $startDateLabel = $startDateLabel . " (" . count($invoices) . " invoices)";

                if( $count > 0 ) {
                    $avgDaysInt = round($daysTotal/$count);
                    //$averageDays[$startDateLabel] = $avgDaysInt;
                    $averageDays[$startDateLabel] = array('value'=>$avgDaysInt,'link'=>$link);
                } else {
                    //$averageDays[$startDateLabel] = null;
                    $averageDays[$startDateLabel] = array('value'=>0,'link'=>$link);
                }

            } while( $startDate < $endDate );

            $layoutArray = array(
                'height' => $this->height*1.3,
                'width' => $this->width,
                'title' => $chartName,
                'margin' => array('b' => 300)
            );

            $chartsArray = $this->getChart($averageDays, $chartName,'bar',$layoutArray);
        }

        //"38. Turn-around Statistics: Number of days each paid and partially paid invoice took to get paid" => "turn-around-statistics-days-per-paid-invoice",
        if( $chartType == "turn-around-statistics-days-per-paid-invoice" ) {
            $invoiceStates = array("Paid in Full","Paid Partially");
            $invoices = $this->getInvoicesByFilter($startDate, $endDate, $projectSpecialtyObjects, $invoiceStates);
            //echo "### $state invoices count=".count($invoices)."<br>";

            $countArr = array();

            foreach($invoices as $invoice) {
                //echo "invoice=".$invoice->getOid()."<br>";
                //Number of days to go from Submitted to Completed
                $issued = $invoice->getIssuedDate(); //“Issued”
                if( !$issued ) {
                    //exit('no issue date');
                    continue;
                    //$issued = $invoice->getCreateDate();
                }
                $paid = $invoice->getPaidDate(); //“Paid”
                if( !$paid ) {
                    //continue;
                    $paid = $invoice->getUpdateDate(); //“Paid”
                }

//                $dDiff = $issued->diff($paid);
//                //echo $dDiff->format('%R'); // use for point out relation: smaller/greater
//                $days = $dDiff->days;
//                //echo "days=".$days."<br>";
//                $days = intval($days);
                $days = $this->calculateDays($issued,$paid);

                if( $days > 0 ) {
                    $invoiceIndex = $invoice->getOid();
                    //$countArr[$invoiceIndex] = $days;

                    $link = $this->container->get('router')->generate(
                        'translationalresearch_invoice_show',
                        array("oid"=>$invoice->getId()),
                        UrlGeneratorInterface::ABSOLUTE_URL
                    );

                    $countArr[$invoiceIndex] = array("value"=>$days,"link"=>$link);
                }
            }

            //$chartName = $chartName . " (based on " . count($projects) . " approved or closed projects)";

            $layoutArray = array(
                'height' => $this->height,
                'width' =>  $this->width,
                'title' => $chartName,
                'margin' => array('b'=>200)
            );

            $chartsArray = $this->getChart($countArr, $chartName,'bar',$layoutArray);
        }

        //"39. Turn-around Statistics: Top 10 PIs with most delayed unpaid invoices" => "turn-around-statistics-pis-with-delayed-unpaid-invoices",
        if( $chartType == "turn-around-statistics-pis-with-delayed-unpaid-invoices" ) {
            $transresRequestUtil = $this->container->get('transres_request_util');

            $pisUnpaidInvoicesArr = array();
            $invoiceDueArr = array();

            //get unpaid and delayd invoices
            $invoiceStates = array("Unpaid/Issued");
            //$invoiceStates = array("Unpaid/Issued","Pending","Paid in Full"); //testing
            $invoices = $this->getInvoicesByFilter($startDate,$endDate,$projectSpecialtyObjects,$invoiceStates,true);
            //$invoices = $transresRequestUtil->getOverdueInvoices();

            foreach($invoices as $invoice) {
                $pi = $invoice->getPrincipalInvestigator();
                if( $pi ) {
                    $piIndex = $pi->getUsernameOptimal();
                    if( isset($pisUnpaidInvoicesArr[$piIndex]) ) {
                        $count = $pisUnpaidInvoicesArr[$piIndex]['value'] + 1;
                    } else {
                        $count = 1;
                    }

                    //$due = intval($invoice->getDue());

                    //$pisUnpaidInvoicesArr[$piIndex] = $count;
                    $todayDate = new \DateTime();
                    $linkFilterArr = array(
                        'filter[status][0]' => "Unpaid/Issued",
                        'filter[startCreateDate]' => $startDateStr,
                        'filter[endCreateDate]' => $endDateStr,
                        'filter[endDate]' => $todayDate->format('m/d/Y'),
                        'filter[version]' => "Latest",
                        'filter[principalInvestigator]' => $pi->getId()
                    );
                    $link = $this->container->get('router')->generate(
                        'translationalresearch_invoice_index_filter',
                        $linkFilterArr,
                        UrlGeneratorInterface::ABSOLUTE_URL
                    );
                    //$pisUnpaidInvoicesArr[$piIndex] = array('value'=>$count,'link'=>$link, "due"=>$due);

                    $due = intval($invoice->getDue());
                    if( isset($invoiceDueArr[$piIndex]) ) {
                        $due = $invoiceDueArr[$piIndex] + $due;
                    }
                    $invoiceDueArr[$piIndex] = $due;

                    $pisUnpaidInvoicesArr[$piIndex] = array('value'=>$count,'link'=>$link, "due"=>$invoiceDueArr[$piIndex]);

                    $titleCount++;
                }
            }//foreach

            //$titleCount = $titleCount . " (invoices ".count($invoices).")";

            $descriptionArr = array(
                array(
                    'descrPrefix'   => "due $",
                    'descrPostfix'  => null,
                    'valuePrefix'   => ": (",
                    'valuePostfix'  => " invoices)",
                    'descrColor'    => "red",
                    'descrType'     => "money",
                    'descrValueArr' => $invoiceDueArr
                ),
            );

            $chartName = $this->getTitleWithTotal($chartName,$titleCount,null,"invoices total");
            $showOther = $this->getOtherStr($showLimited,"PIs");
            $pisUnpaidInvoicesArrTop = $this->getTopArray($pisUnpaidInvoicesArr,$showOther,$quantityLimit,$descriptionArr);

            //attach value to other
            //$pisUnpaidInvoicesArrTop = $this->addValueToOther($pisUnpaidInvoicesArrTop,": $","due"); //replaced by modified getTopArray

            $chartsArray = $this->getChart($pisUnpaidInvoicesArrTop, $chartName,'pie',$layoutArray,null,null,null,"percent+label");
        }

        //"40. Turn-around Statistics: Top PIs with highest total amounts in unpaid, overdue invoices (linked)" => "turn-around-statistics-pis-with-highest-total-unpaid-invoices",
        if( $chartType == "turn-around-statistics-pis-with-highest-total-unpaid-invoices" ) {
            $transresRequestUtil = $this->container->get('transres_request_util');

            $pisUnpaidInvoicesTotalArr = array();
            $totalUnpaid = 0;

            //$invoices = $transresRequestUtil->getOverdueInvoices();
            $invoiceStates = array("Unpaid/Issued");
            $invoices = $this->getInvoicesByFilter($startDate,$endDate,$projectSpecialtyObjects,$invoiceStates,true);

            foreach($invoices as $invoice) {
                $pi = $invoice->getPrincipalInvestigator();
                if( $pi ) {
                    $piIndex = $pi->getUsernameOptimal(); // . " (".$invoice->getOid().")";
                    //$pisUnpaidInvoicesTotalArr[$piIndex] = $invoice->getTotal();
                    $total = $invoice->getTotal();
                    $totalUnpaid = $totalUnpaid + intval($total);

                    if (isset($pisUnpaidInvoicesTotalArr[$piIndex])) {
                        //$count = $pisUnpaidInvoicesArr[$piIndex] + 1;
                        $total = $pisUnpaidInvoicesTotalArr[$piIndex]['value'] + $total;
                    }
                    //$pisUnpaidInvoicesTotalArr[$piIndex] = $total;
                    $todayDate = new \DateTime();
                    $linkFilterArr = array(
                        'filter[status][0]' => "Unpaid/Issued",
                        'filter[startCreateDate]' => $startDateStr,
                        'filter[endCreateDate]' => $endDateStr,
                        'filter[endDate]' => $todayDate->format('m/d/Y'),
                        'filter[version]' => "Latest",
                        'filter[principalInvestigator]' => $pi->getId()
                    );
                    $link = $this->container->get('router')->generate(
                        'translationalresearch_invoice_index_filter',
                        $linkFilterArr,
                        UrlGeneratorInterface::ABSOLUTE_URL
                    );
                    $pisUnpaidInvoicesTotalArr[$piIndex] = array('value'=>$total,'link'=>$link);

                    $titleCount++;
                }
            }//foreach

            //$titleCount = $titleCount . " (invoices ".count($invoices).")";

            //$chartName = $this->getTitleWithTotal($chartName,$titleCount);
            $chartName = $chartName . " (" . $titleCount . " invoices for a total $" . $this->getNumberFormat($totalUnpaid) . ")";

            $showOther = $this->getOtherStr($showLimited,"PIs");
            $pisUnpaidInvoicesTotalArrTop = $this->getTopArray($pisUnpaidInvoicesTotalArr,$showOther,$quantityLimit);
            $chartsArray = $this->getChart($pisUnpaidInvoicesTotalArrTop, $chartName,'pie',$layoutArray," : $",null,null,"percent+label");
        }

        //"41. Turn-around Statistics: Top 10 PIs combining amounts and delay duration for unpaid, overdue invoices" => "turn-around-statistics-pis-combining-total-delayed-unpaid-invoices",
        if( $chartType == "turn-around-statistics-pis-combining-total-delayed-unpaid-invoices" ) {
            $transresRequestUtil = $this->container->get('transres_request_util');

            $pisCombinedArr = array();
            $pisTotalUnpaidArr = array();
            $pisDaysArr = array();
            $pisCountArr = array();
            $pisIdArr = array();

            $totalUnpaid = 0;
            $totalCombined = 0;

            //$invoices = $transresRequestUtil->getOverdueInvoices();
            $invoiceStates = array("Unpaid/Issued");
            $invoices = $this->getInvoicesByFilter($startDate,$endDate,$projectSpecialtyObjects,$invoiceStates,true);

            foreach($invoices as $invoice) {
                $pi = $invoice->getPrincipalInvestigator();
                if( $pi ) {
                    $piIndex = $pi->getUsernameOptimal();
                    $total = $invoice->getTotal();
                    $total = intval($total);
                    $totalUnpaid = $totalUnpaid + $total;

                    //get number of due days (months)
                    $nowDate = new \DateTime();
                    $dueDate = $invoice->getDueDate();
                    if( !$dueDate ) {
                        continue; //ignore invoices without duedate
                    }
                    $diff = $nowDate->diff($dueDate);
                    $months = (($diff->format('%y') * 12) + $diff->format('%m'));  //full months difference;
                    $days = $diff->days;
                    //echo "days=".$days."<br>";
                    $dueTimeNumber = intval($months);
                    //if months is less than 1, use 1
                    //if( !$dueTimeNumber || $dueTimeNumber <= 0 ) {
                    //    $dueTimeNumber = 1;
                    //}

                    //multiply invoice amount by the number of associated months it has remained unpaid
                    //for example - $100 unpaid invoice from 5 months ago => 5 x $100 + $600 invoice x 3 months ago = $2300 for this PI
                    $combined = $total * $dueTimeNumber;
                    $totalCombined = $totalCombined + $combined;

                    //combined index
                    if (isset($pisCombinedArr[$piIndex])) {
                        //$count = $pisUnpaidInvoicesArr[$piIndex] + 1;
                        $combined = $pisCombinedArr[$piIndex] + $combined;
                    }
                    $pisCombinedArr[$piIndex] = $combined;

                    //total owed
                    if (isset($pisTotalUnpaidArr[$piIndex])) {
                        $total = $pisTotalUnpaidArr[$piIndex] + $total;
                    }
                    $pisTotalUnpaidArr[$piIndex] = $total;

                    //median number of days invoice has been unpaid
                    if (isset($pisDaysArr[$piIndex])) {
                        $days = $pisDaysArr[$piIndex] + $days;
                    }
                    $pisDaysArr[$piIndex] = $days;

                    //count for this PI
                    if (isset($pisCountArr[$piIndex])) {
                        $count = $pisCountArr[$piIndex] + 1;
                    } else {
                        $count = 1;
                    }
                    $pisCountArr[$piIndex] = $count;

                    $pisIdArr[$piIndex] = $pi->getId();

                    $titleCount++;
                }
            }//foreach

            //$titleCount = $titleCount . " (invoices ".count($invoices).")";

            //in the legend titles list PI name : total owed : median number of days invoice has been unpaid
            $pisCombinedArrNew = array();
            foreach($pisCombinedArr as $index => $combined) {
                //total
                $total = $pisTotalUnpaidArr[$index];
                //$total = $this->getNumberFormat($total);

                //days
                $days = $pisDaysArr[$index];
                $count = $pisCountArr[$index];
                if( $count ) {
                    $days = round($days / $count);
                } else {
                    $days = "unknown";
                }

                //new index (legend)
                $newIndex = $index . " ($" . $this->getNumberFormat($total) . " total owed, " . $days . " average number of days invoices have remained unpaid)";

                //$pisCombinedArrNew[$newIndex] = $combined;
                $todayDate = new \DateTime();
                $linkFilterArr = array(
                    'filter[status][0]' => "Unpaid/Issued",
                    'filter[startCreateDate]' => $startDateStr,
                    'filter[endCreateDate]' => $endDateStr,
                    'filter[endDate]' => $todayDate->format('m/d/Y'),
                    'filter[version]' => "Latest",
                    'filter[principalInvestigator]' => $pisIdArr[$index]
                );
                $link = $this->container->get('router')->generate(
                    'translationalresearch_invoice_index_filter',
                    $linkFilterArr,
                    UrlGeneratorInterface::ABSOLUTE_URL
                );
                $pisCombinedArrNew[$newIndex] = array('value'=>$combined,'link'=>$link);
            }

            //$chartName = $this->getTitleWithTotal($chartName,$titleCount);
            $chartName = $chartName . " (" . $titleCount . " invoices with a total index of " . $this->getNumberFormat($totalCombined) . ")";

            $layoutArray['width'] = $layoutArray['width'] * 1.3; //1400;

            $showOther = $this->getOtherStr($showLimited,"PIs");
            //getTopArray($piProjectCountArr, $showOthers=false, $descriptionArr=array(), $maxLen=50)
            $pisCombinedArrTop = $this->getTopArray($pisCombinedArrNew,$showOther,$quantityLimit,array(),150);
            $chartsArray = $this->getChart($pisCombinedArrTop, $chartName,'pie',$layoutArray," : $",null,null,"percent+label");
        }

//        //"42. Total Number of Individual PIs involved in AP/CP and Hematopathology Projects" => "compare-projectspecialty-pis",
//        if( $chartType == "compare-projectspecialty-pis_original" ) {
//            $transresUtil = $this->container->get('transres_util');
//            $specialtyApcpObject = $transresUtil->getTrpSpecialtyObjects("ap-cp");
//            $specialtyHemaObject = $transresUtil->getTrpSpecialtyObjects("hematopathology");
//            $specialtyCovidObject = $transresUtil->getTrpSpecialtyObjects("covid19");
//
//            //$startDate,$endDate,$projectSpecialties,$states
//            $apcpProjects = $this->getProjectsByFilter($startDate,$endDate,array($specialtyApcpObject));
//            $hemaProjects = $this->getProjectsByFilter($startDate,$endDate,array($specialtyHemaObject));
//            $covidProjects = $this->getProjectsByFilter($startDate,$endDate,array($specialtyCovidObject));
//
//            $apcpPisArr = array();
//            $hemaPisArr = array();
//            $covidPisArr = array();
//            foreach($apcpProjects as $project) {
//                foreach($project->getAllPrincipalInvestigators() as $pi) {
//                    $apcpPisArr[] = $pi->getId();
//                }
//            }
//            foreach($hemaProjects as $project) {
//                foreach($project->getAllPrincipalInvestigators() as $pi) {
//                    $hemaPisArr[] = $pi->getId();
//                }
//            }
//            foreach($covidProjects as $project) {
//                foreach($project->getAllPrincipalInvestigators() as $pi) {
//                    $covidPisArr[] = $pi->getId();
//                }
//            }
//
//            $apcpPisArr = array_unique($apcpPisArr);
//            $hemaPisArr = array_unique($hemaPisArr);
//            $covidPisArr = array_unique($covidPisArr);
//
//            $pisDataArr = array();
//
//            //APCP
//            //array(value,link)
//            $linkFilterArr = array(
//                'filter[state][0]' => 'final_approved',
//                'filter[state][1]' => 'closed',
//                'filter[startDate]' => $startDateStr,
//                'filter[endDate]' => $endDateStr,
//                //'filter[]' => $projectSpecialtyObjects,
//                'filter[searchProjectType]' => null,
//                'filter[projectSpecialty][]' => $specialtyApcpObject->getId(),
//                //'filter[principalInvestigators][]' => implode(",",$apcpPisArr)
//            );
//            $index = 0;
//            foreach($apcpPisArr as $piId) {
//                $filterIndex = "filter[principalInvestigators][".$index."]";
//                //echo "filterIndex=".$filterIndex."<br>";
//                $linkFilterArr[$filterIndex] = $piId;
//                $index++;
//            }
//            $link = $this->container->get('router')->generate(
//                'translationalresearch_project_index',
//                $linkFilterArr,
//                UrlGeneratorInterface::ABSOLUTE_URL
//            );
//            //$pisDataArr['AP/CP PIs'] = count($apcpPisArr);
//            $pisDataArr['AP/CP PIs'] = array('value'=>count($apcpPisArr),'link'=>$link);
//
//            //Hema
//            $linkFilterArr = array(
//                'filter[state][0]' => 'final_approved',
//                'filter[state][1]' => 'closed',
//                'filter[startDate]' => $startDateStr,
//                'filter[endDate]' => $endDateStr,
//                //'filter[]' => $projectSpecialtyObjects,
//                'filter[searchProjectType]' => null,
//                'filter[projectSpecialty][]' => $specialtyHemaObject->getId()
//            );
//            $index = 0;
//            foreach($hemaPisArr as $piId) {
//                $filterIndex = "filter[principalInvestigators][".$index."]";
//                $linkFilterArr[$filterIndex] = $piId;
//                $index++;
//            }
//            $link = $this->container->get('router')->generate(
//                'translationalresearch_project_index',
//                $linkFilterArr,
//                UrlGeneratorInterface::ABSOLUTE_URL
//            );
//            //$pisDataArr['Hematopathology PIs'] = count($hemaPisArr);
//            $pisDataArr['Hematopathology PIs'] = array('value'=>count($hemaPisArr),'link'=>$link);
//
//            //COVID
//            $linkFilterArr = array(
//                'filter[state][0]' => 'final_approved',
//                'filter[state][1]' => 'closed',
//                'filter[startDate]' => $startDateStr,
//                'filter[endDate]' => $endDateStr,
//                //'filter[]' => $projectSpecialtyObjects,
//                'filter[searchProjectType]' => null,
//                'filter[projectSpecialty][]' => $specialtyCovidObject->getId()
//            );
//            $index = 0;
//            foreach($covidPisArr as $piId) {
//                $filterIndex = "filter[principalInvestigators][".$index."]";
//                $linkFilterArr[$filterIndex] = $piId;
//                $index++;
//            }
//            $link = $this->container->get('router')->generate(
//                'translationalresearch_project_index',
//                $linkFilterArr,
//                UrlGeneratorInterface::ABSOLUTE_URL
//            );
//            $pisDataArr['COVID-19 PIs'] = array('value'=>count($covidPisArr),'link'=>$link);
//
//            $chartsArray = $this->getChart($pisDataArr, $chartName,'pie',$layoutArray,null,null,null,"percent+label");
//        }
        //New function with dynamic specialty
        //"42. Total Number of Individual PIs involved in AP/CP and Hematopathology Projects" => "compare-projectspecialty-pis",
        if( $chartType == "compare-projectspecialty-pis" ) {
            $transresUtil = $this->container->get('transres_util');
            $specialtyObjects = $transresUtil->getTrpSpecialtyObjects();

            $pisDataArr = array();

            foreach( $specialtyObjects as $specialtyObject ) {
                $pisDataArr = $this->trpPisSingleSpecialty($pisDataArr,$specialtyObject,$startDate,$startDateStr,$endDate,$endDateStr);
            }

            $chartsArray = $this->getChart($pisDataArr, $chartName,'pie',$layoutArray,null,null,null,"percent+label");
        }

//        //"43. Total Number of AP/CP and Hematopathology Project Requests" => "compare-projectspecialty-projects",
//        if( $chartType == "compare-projectspecialty-projects" ) {
//            $transresUtil = $this->container->get('transres_util');
//            $specialtyApcpObject = $transresUtil->getSpecialtyObject("ap-cp");
//            $specialtyHemaObject = $transresUtil->getSpecialtyObject("hematopathology");
//            $specialtyCovidObject = $transresUtil->getSpecialtyObject("covid19");
//
//            //$startDate,$endDate,$projectSpecialties,$states
//            $apcpProjects = $this->getProjectsByFilter($startDate,$endDate,array($specialtyApcpObject));
//            $hemaProjects = $this->getProjectsByFilter($startDate,$endDate,array($specialtyHemaObject));
//            $covidProjects = $this->getProjectsByFilter($startDate,$endDate,array($specialtyCovidObject));
//
//            $projectsDataArr = array();
//
//            //APCP
//            $linkFilterArr = array(
//                'filter[state][0]' => 'final_approved',
//                'filter[state][1]' => 'closed',
//                'filter[startDate]' => $startDateStr,
//                'filter[endDate]' => $endDateStr,
//                'filter[searchProjectType]' => null,
//                'filter[projectSpecialty][]' => $specialtyApcpObject->getId(),
//            );
//            $link = $this->container->get('router')->generate(
//                'translationalresearch_project_index',
//                $linkFilterArr,
//                UrlGeneratorInterface::ABSOLUTE_URL
//            );
//            //$projectsDataArr['AP/CP Project Requests'] = count($apcpProjects);
//            $projectsDataArr['AP/CP Project Requests'] = array('value'=>count($apcpProjects),'link'=>$link);
//
//            //Hema
//            $linkFilterArr = array(
//                'filter[state][0]' => 'final_approved',
//                'filter[state][1]' => 'closed',
//                'filter[startDate]' => $startDateStr,
//                'filter[endDate]' => $endDateStr,
//                'filter[searchProjectType]' => null,
//                'filter[projectSpecialty][]' => $specialtyHemaObject->getId(),
//            );
//            $link = $this->container->get('router')->generate(
//                'translationalresearch_project_index',
//                $linkFilterArr,
//                UrlGeneratorInterface::ABSOLUTE_URL
//            );
//            //$projectsDataArr['Hematopathology Project Requests'] = count($hemaProjects);
//            $projectsDataArr['Hematopathology Project Requests'] = array('value'=>count($hemaProjects),'link'=>$link);
//
//            //Covid
//            $linkFilterArr = array(
//                'filter[state][0]' => 'final_approved',
//                'filter[state][1]' => 'closed',
//                'filter[startDate]' => $startDateStr,
//                'filter[endDate]' => $endDateStr,
//                'filter[searchProjectType]' => null,
//                'filter[projectSpecialty][]' => $specialtyCovidObject->getId(),
//            );
//            $link = $this->container->get('router')->generate(
//                'translationalresearch_project_index',
//                $linkFilterArr,
//                UrlGeneratorInterface::ABSOLUTE_URL
//            );
//            //$projectsDataArr['Covidtopathology Project Requests'] = count($covidProjects);
//            $projectsDataArr['COVID-19 Project Requests'] = array('value'=>count($covidProjects),'link'=>$link);
//
//            $chartsArray = $this->getChart($projectsDataArr, $chartName,'pie',$layoutArray,null,null,null,"percent+label");
//        }
        //"43. Total Number of AP/CP and Hematopathology Project Requests" => "compare-projectspecialty-projects",
        if( $chartType == "compare-projectspecialty-projects" ) {
            $transresUtil = $this->container->get('transres_util');
            $specialtyObjects = $transresUtil->getTrpSpecialtyObjects();

            $projectsDataArr = array();

            foreach( $specialtyObjects as $specialtyObject ) {
                $projectsDataArr = $this->trpProjectsSingleSpecialty($projectsDataArr,$specialtyObject,$startDate,$startDateStr,$endDate,$endDateStr);
            }

            $chartsArray = $this->getChart($projectsDataArr, $chartName,'pie',$layoutArray,null,null,null,"percent+label");
        }

        //"44. Total Number of AP/CP and Hematopathology Project Requests By Month" => "compare-projectspecialty-projects-stack",
        if( $chartType == "compare-projectspecialty-projects-stack_original" ) {
            $transresUtil = $this->container->get('transres_util');
            $specialtyApcpObject = $transresUtil->getSpecialtyObject("ap-cp");
            $specialtyHemaObject = $transresUtil->getSpecialtyObject("hematopathology");
            $specialtyCovidObject = $transresUtil->getSpecialtyObject("covid19");

            $apcpResultStatArr = array();
            $hemaResultStatArr = array();
            $covidResultStatArr = array();
            $datesArr = array();

            //get startDate and add 1 month until the date is less than endDate
            //$startDate->modify( 'first day of last month' );
            $startDate->modify( 'first day of this month' );
            do {
                $startDateLabel = $startDate->format('M-Y');
                $thisEndDate = clone $startDate;
                //$thisEndDate->modify( 'first day of next month' );
                $thisEndDate->modify('last day of this month');
                $datesArr[$startDateLabel] = array('startDate'=>$startDate->format('m/d/Y'),'endDate'=>$thisEndDate->format('m/d/Y'));
                //echo "StartDate=".$startDate->format("d-M-Y")."; EndDate=".$thisEndDate->format("d-M-Y")."<br>";
                //$startDate,$endDate,$projectSpecialties,$states,$addOneEndDay
                $apcpProjects = $this->getProjectsByFilter($startDate,$thisEndDate,array($specialtyApcpObject),null,false);
                $hemaProjects = $this->getProjectsByFilter($startDate,$thisEndDate,array($specialtyHemaObject),null,false);
                $covidProjects = $this->getProjectsByFilter($startDate,$thisEndDate,array($specialtyCovidObject),null,false);
                $startDate->modify( 'first day of next month' );

                $apcpResultStatArr[$startDateLabel] = count($apcpProjects);
                $hemaResultStatArr[$startDateLabel] = count($hemaProjects);
                $covidResultStatArr[$startDateLabel] = count($covidProjects);
            } while( $startDate < $endDate );

            //AP/CP
            $apcpProjectsData = array();
            foreach($apcpResultStatArr as $date=>$value ) {
                //$apcpProjectsData[$date] = $value;
                $dates = $datesArr[$date];
                $linkFilterArr = array(
                    'filter[state][0]' => 'final_approved',
                    'filter[state][1]' => 'closed',
                    'filter[startDate]' => $dates['startDate'],
                    'filter[endDate]' => $dates['endDate'],
                    'filter[searchProjectType]' => null,
                    //'filter[projectSpecialty][]' => $specialtyHemaObject->getId(),
                );
                $link = $this->container->get('router')->generate(
                    'translationalresearch_project_index',
                    $linkFilterArr,
                    UrlGeneratorInterface::ABSOLUTE_URL
                );
                $apcpProjectsData[$date] = array('value'=>$value,'link'=>$link);
            }

            //Hema
            $hemaProjectsData = array();
            foreach($hemaResultStatArr as $date=>$value ) {
                //$hemaProjectsData[$date] = $value;
                $dates = $datesArr[$date];
                $linkFilterArr = array(
                    'filter[state][0]' => 'final_approved',
                    'filter[state][1]' => 'closed',
                    'filter[startDate]' => $dates['startDate'],
                    'filter[endDate]' => $dates['endDate'],
                    'filter[searchProjectType]' => null,
                    //'filter[projectSpecialty][]' => $specialtyHemaObject->getId(),
                );
                $link = $this->container->get('router')->generate(
                    'translationalresearch_project_index',
                    $linkFilterArr,
                    UrlGeneratorInterface::ABSOLUTE_URL
                );
                $hemaProjectsData[$date] = array('value'=>$value,'link'=>$link);
            }

            //Covid
            $covidProjectsData = array();
            foreach($covidResultStatArr as $date=>$value ) {
                //$covidProjectsData[$date] = $value;
                $dates = $datesArr[$date];
                $linkFilterArr = array(
                    'filter[state][0]' => 'final_approved',
                    'filter[state][1]' => 'closed',
                    'filter[startDate]' => $dates['startDate'],
                    'filter[endDate]' => $dates['endDate'],
                    'filter[searchProjectType]' => null,
                    //'filter[projectSpecialty][]' => $specialtyCovidObject->getId(),
                );
                $link = $this->container->get('router')->generate(
                    'translationalresearch_project_index',
                    $linkFilterArr,
                    UrlGeneratorInterface::ABSOLUTE_URL
                );
                $covidProjectsData[$date] = array('value'=>$value,'link'=>$link);
            }

            //Projects
            $combinedProjectsData = array();
            $combinedProjectsData['AP/CP'] = $apcpProjectsData;
            $combinedProjectsData['Hematopathology'] = $hemaProjectsData;
            $combinedProjectsData['COVID-19'] = $covidProjectsData;

            $chartsArray = $this->getStackedChart($combinedProjectsData, $chartName, "stack");
        }
        //"44. Total Number of AP/CP and Hematopathology Project Requests By Month" => "compare-projectspecialty-projects-stack",
        if( $chartType == "compare-projectspecialty-projects-stack" ) {
            $transresUtil = $this->container->get('transres_util');
            $specialtyObjects = $transresUtil->getTrpSpecialtyObjects();

            $datesArr = array();
            $specialtyResultStatArr = array();

            //get startDate and add 1 month until the date is less than endDate
            //$startDate->modify( 'first day of last month' );
            $startDate->modify( 'first day of this month' );
            do {
                $startDateLabel = $startDate->format('M-Y');
                $thisEndDate = clone $startDate;
                //$thisEndDate->modify( 'first day of next month' );
                $thisEndDate->modify('last day of this month');
                $datesArr[$startDateLabel] = array('startDate'=>$startDate->format('m/d/Y'),'endDate'=>$thisEndDate->format('m/d/Y'));
                //echo "StartDate=".$startDate->format("d-M-Y")."; EndDate=".$thisEndDate->format("d-M-Y")."<br>";
                foreach($specialtyObjects as $specialtyObject) {
                    //                                            $startDate,$endDate,$projectSpecialties,$states,$addOneEndDay
                    $specialtyProjects = $this->getProjectsByFilter($startDate,$thisEndDate,array($specialtyObject),null,false);
                    $specialtyResultStatArr[$specialtyObject->getId()][$startDateLabel] = count($specialtyProjects);
                }
                $startDate->modify( 'first day of next month' );

            } while( $startDate < $endDate );

            $combinedProjectsData = array();

            foreach($specialtyObjects as $specialtyObject) {
                $specialtyProjectsData = array();
                foreach($specialtyResultStatArr[$specialtyObject->getId()] as $date=>$value ) {
                    $dates = $datesArr[$date];
                    $linkFilterArr = array(
                        'filter[state][0]' => 'final_approved',
                        'filter[state][1]' => 'closed',
                        'filter[startDate]' => $dates['startDate'],
                        'filter[endDate]' => $dates['endDate'],
                        'filter[searchProjectType]' => null,
                        //'filter[projectSpecialty][]' => $specialtyHemaObject->getId(),
                    );
                    $link = $this->container->get('router')->generate(
                        'translationalresearch_project_index',
                        $linkFilterArr,
                        UrlGeneratorInterface::ABSOLUTE_URL
                    );
                    $specialtyProjectsData[$date] = array('value'=>$value,'link'=>$link);
                }

                $combinedProjectsData[$specialtyObject->getName()] = $specialtyProjectsData;
            }

            $chartsArray = $this->getStackedChart($combinedProjectsData, $chartName, "stack");
        }

        //"45. Total Number of AP/CP and Hematopathology Work Requests By Month" => "compare-projectspecialty-requests",
        if( $chartType == "compare-projectspecialty-requests_original" ) {
            $transresUtil = $this->container->get('transres_util');
            $specialtyApcpObject = $transresUtil->getSpecialtyObject("ap-cp");
            $specialtyHemaObject = $transresUtil->getSpecialtyObject("hematopathology");
            $specialtyCovidObject = $transresUtil->getSpecialtyObject("covid19");

            $apcpResultStatArr = array();
            $hemaResultStatArr = array();
            $covidResultStatArr = array();
            $datesArr = array();

            //get startDate and add 1 month until the date is less than endDate
            //$startDate->modify( 'first day of last month' );
            $startDate->modify( 'first day of this month' );
            do {
                $startDateLabel = $startDate->format('M-Y');
                $thisEndDate = clone $startDate;
                //$thisEndDate->modify( 'first day of next month' );
                $thisEndDate->modify('last day of this month');
                $datesArr[$startDateLabel] = array('startDate'=>$startDate->format('m/d/Y'),'endDate'=>$thisEndDate->format('m/d/Y'));
                //echo "StartDate=".$startDate->format("d-M-Y")."; EndDate=".$thisEndDate->format("d-M-Y")."<br>";
                //$startDate,$endDate,$projectSpecialties,$states,$addOneEndDay

                $apcpRequests = $this->getRequestsByFilter($startDate,$thisEndDate,array($specialtyApcpObject));
                $hemaRequests = $this->getRequestsByFilter($startDate,$thisEndDate,array($specialtyHemaObject));
                $covidRequests = $this->getRequestsByFilter($startDate,$thisEndDate,array($specialtyCovidObject));

                $startDate->modify( 'first day of next month' );

                $apcpResultStatArr[$startDateLabel] = count($apcpRequests);
                $hemaResultStatArr[$startDateLabel] = count($hemaRequests);
                $covidResultStatArr[$startDateLabel] = count($covidRequests);

            } while( $startDate < $endDate );

            //AP/CP
            $apcpRequestsData = array();
            foreach($apcpResultStatArr as $date=>$value ) {
                $dates = $datesArr[$date];
                $linkFilterArr = array(
                    'filter[progressState][0]' => 'active',
                    'filter[progressState][1]' => 'completed',
                    'filter[progressState][2]' => 'completedNotified',
                    'filter[progressState][3]' => 'pendingInvestigatorInput',
                    'filter[progressState][4]' => 'pendingHistology',
                    'filter[progressState][5]' => 'pendingImmunohistochemistry',
                    'filter[progressState][6]' => 'pendingMolecular',
                    'filter[progressState][7]' => 'pendingCaseRetrieval',
                    'filter[progressState][8]' => 'pendingTissueMicroArray',
                    'filter[progressState][9]' => 'pendingSlideScanning',
                    'filter[startDate]' => $dates['startDate'],
                    'filter[endDate]' => $dates['endDate'],
                    'filter[searchProjectType]' => null,
                );
                $link = $this->container->get('router')->generate(
                    'translationalresearch_request_index_filter',
                    $linkFilterArr,
                    UrlGeneratorInterface::ABSOLUTE_URL
                );
                $apcpRequestsData[$date] = array('value'=>$value,'link'=>$link);
            }

            //Hema
            $hemaRequestsData = array();
            foreach($hemaResultStatArr as $date=>$value ) {
                $dates = $datesArr[$date];
                $linkFilterArr = array(
                    'filter[progressState][0]' => 'active',
                    'filter[progressState][1]' => 'completed',
                    'filter[progressState][2]' => 'completedNotified',
                    'filter[progressState][3]' => 'pendingInvestigatorInput',
                    'filter[progressState][4]' => 'pendingHistology',
                    'filter[progressState][5]' => 'pendingImmunohistochemistry',
                    'filter[progressState][6]' => 'pendingMolecular',
                    'filter[progressState][7]' => 'pendingCaseRetrieval',
                    'filter[progressState][8]' => 'pendingTissueMicroArray',
                    'filter[progressState][9]' => 'pendingSlideScanning',
                    'filter[startDate]' => $dates['startDate'],
                    'filter[endDate]' => $dates['endDate'],
                    'filter[searchProjectType]' => null,
                );
                $link = $this->container->get('router')->generate(
                    'translationalresearch_request_index_filter',
                    $linkFilterArr,
                    UrlGeneratorInterface::ABSOLUTE_URL
                );
                $hemaRequestsData[$date] = array('value'=>$value,'link'=>$link);
            }

            //Covid
            $covidRequestsData = array();
            foreach($covidResultStatArr as $date=>$value ) {
                $dates = $datesArr[$date];
                $linkFilterArr = array(
                    'filter[progressState][0]' => 'active',
                    'filter[progressState][1]' => 'completed',
                    'filter[progressState][2]' => 'completedNotified',
                    'filter[progressState][3]' => 'pendingInvestigatorInput',
                    'filter[progressState][4]' => 'pendingHistology',
                    'filter[progressState][5]' => 'pendingImmunohistochemistry',
                    'filter[progressState][6]' => 'pendingMolecular',
                    'filter[progressState][7]' => 'pendingCaseRetrieval',
                    'filter[progressState][8]' => 'pendingTissueMicroArray',
                    'filter[progressState][9]' => 'pendingSlideScanning',
                    'filter[startDate]' => $dates['startDate'],
                    'filter[endDate]' => $dates['endDate'],
                    'filter[searchProjectType]' => null,
                );
                $link = $this->container->get('router')->generate(
                    'translationalresearch_request_index_filter',
                    $linkFilterArr,
                    UrlGeneratorInterface::ABSOLUTE_URL
                );
                $covidRequestsData[$date] = array('value'=>$value,'link'=>$link);
            }

            //Requests
            $combinedRequestsData = array();
            $combinedRequestsData['AP/CP'] = $apcpRequestsData;
            $combinedRequestsData['Hematopathology'] = $hemaRequestsData;
            $combinedRequestsData['COVID-19'] = $covidRequestsData;
            $chartsArray = $this->getStackedChart($combinedRequestsData, $chartName, "stack");
        }
        //"45. Total Number of AP/CP and Hematopathology Work Requests By Month" => "compare-projectspecialty-requests",
        if( $chartType == "compare-projectspecialty-requests" ) {
            $transresUtil = $this->container->get('transres_util');

            $specialtyObjects = $transresUtil->getTrpSpecialtyObjects();
            $datesArr = array();
            $specialtyResultStatArr = array();

            //get startDate and add 1 month until the date is less than endDate
            //$startDate->modify( 'first day of last month' );
            $startDate->modify( 'first day of this month' );
            do {
                $startDateLabel = $startDate->format('M-Y');

                foreach($specialtyObjects as $specialtyObject) {
                    $thisEndDate = clone $startDate;
                    //$thisEndDate->modify( 'first day of next month' );
                    $thisEndDate->modify('last day of this month');
                    $datesArr[$startDateLabel] = array('startDate'=>$startDate->format('m/d/Y'),'endDate'=>$thisEndDate->format('m/d/Y'));
                    //echo "StartDate=".$startDate->format("d-M-Y")."; EndDate=".$thisEndDate->format("d-M-Y")."<br>";

                    $specialtyRequests = $this->getRequestsByFilter($startDate,$thisEndDate,array($specialtyObject));
                    $specialtyResultStatArr[$specialtyObject->getId()][$startDateLabel] = count($specialtyRequests);
                }

                $startDate->modify( 'first day of next month' );

            } while( $startDate < $endDate );

            $combinedRequestsData = array();

            foreach($specialtyObjects as $specialtyObject) {
                $specialtyRequestsData = array();
                foreach($specialtyResultStatArr[$specialtyObject->getId()] as $date=>$value ) {
                    $dates = $datesArr[$date];
                    $linkFilterArr = array(
                        'filter[progressState][0]' => 'active',
                        'filter[progressState][1]' => 'completed',
                        'filter[progressState][2]' => 'completedNotified',
                        'filter[progressState][3]' => 'pendingInvestigatorInput',
                        'filter[progressState][4]' => 'pendingHistology',
                        'filter[progressState][5]' => 'pendingImmunohistochemistry',
                        'filter[progressState][6]' => 'pendingMolecular',
                        'filter[progressState][7]' => 'pendingCaseRetrieval',
                        'filter[progressState][8]' => 'pendingTissueMicroArray',
                        'filter[progressState][9]' => 'pendingSlideScanning',
                        'filter[startDate]' => $dates['startDate'],
                        'filter[endDate]' => $dates['endDate'],
                        //'filter[projectSpecialty][]' => $specialtyObject->getId(),
                        'filter[searchProjectType]' => null,
                    );
                    $link = $this->container->get('router')->generate(
                        'translationalresearch_request_index_filter',
                        $linkFilterArr,
                        UrlGeneratorInterface::ABSOLUTE_URL
                    );

                    $specialtyRequestsData[$date] = array('value'=>$value,'link'=>$link);
                }

                $combinedRequestsData[$specialtyObject->getName()] = $specialtyRequestsData;
            }

            $chartsArray = $this->getStackedChart($combinedRequestsData, $chartName, "stack");
        }

        //"46. Total Number of AP/CP and Hematopathology Invoices By Month" => "compare-projectspecialty-invoices",
        if( $chartType == "compare-projectspecialty-invoices_original" ) {
            $transresUtil = $this->container->get('transres_util');
            $specialtyApcpObject = $transresUtil->getSpecialtyObject("ap-cp");
            $specialtyHemaObject = $transresUtil->getSpecialtyObject("hematopathology");
            $specialtyCovidObject = $transresUtil->getSpecialtyObject("covid19");

            $apcpResultStatArr = array();
            $hemaResultStatArr = array();
            $covidResultStatArr = array();
            $datesArr = array();

            //get startDate and add 1 month until the date is less than endDate
            //$startDate->modify( 'first day of last month' );
            $startDate->modify( 'first day of this month' );
            do {
                $startDateLabel = $startDate->format('M-Y');
                $thisEndDate = clone $startDate;
                //$thisEndDate->modify( 'first day of next month' );
                $thisEndDate->modify('last day of this month');
                $datesArr[$startDateLabel] = array('startDate'=>$startDate->format('m/d/Y'),'endDate'=>$thisEndDate->format('m/d/Y'));
                //echo "StartDate=".$startDate->format("d-M-Y")."; EndDate=".$thisEndDate->format("d-M-Y")."<br>";
                //$startDate,$endDate,$projectSpecialties,$states,$addOneEndDay

                $apcpInvoices = $this->getInvoicesByFilter($startDate,$thisEndDate, array($specialtyApcpObject));
                $hemaInvoices = $this->getInvoicesByFilter($startDate,$thisEndDate, array($specialtyHemaObject));
                $covidInvoices = $this->getInvoicesByFilter($startDate,$thisEndDate, array($specialtyCovidObject));

                $startDate->modify( 'first day of next month' );

                //$apcpResultStatArr = $this->getProjectRequestInvoiceChart($apcpProjects,$apcpResultStatArr,$startDateLabel);
                //$hemaResultStatArr = $this->getProjectRequestInvoiceChart($hemaProjects,$hemaResultStatArr,$startDateLabel);
                $apcpResultStatArr[$startDateLabel] = count($apcpInvoices);
                $hemaResultStatArr[$startDateLabel] = count($hemaInvoices);
                $covidResultStatArr[$startDateLabel] = count($covidInvoices);

            } while( $startDate < $endDate );

            //AP/CP
            $apcpInvoicesData = array();
            foreach($apcpResultStatArr as $date=>$value ) {
                //$apcpInvoicesData[$date] = $value;
                $dates = $datesArr[$date];
                $linkFilterArr = array(
                    'filter[status][0]' => "Unpaid/Issued",
                    'filter[status][1]' => "Paid in Full",
                    'filter[status][2]' => "Paid Partially",
                    'filter[status][3]' => 'Refunded Fully',
                    'filter[status][4]' => 'Refunded Partially',
                    'filter[status][5]' => 'Pending',
                    'filter[startCreateDate]' => $dates['startDate'], //dueDate, therefore we can not filter invoices list
                    'filter[endCreateDate]' => $dates['endDate'],
                    'filter[version]' => "Latest",
                    //'filter[idSearch]' => $specialtyApcpObject->getUppercaseShortName()
                );
                $link = $this->container->get('router')->generate(
                    'translationalresearch_invoice_index_filter',
                    $linkFilterArr,
                    UrlGeneratorInterface::ABSOLUTE_URL
                );
                $apcpInvoicesData[$date] = array('value'=>$value,'link'=>$link);
            }

            //Hema
            $hemaInvoicesData = array();
            foreach($hemaResultStatArr as $date=>$value ) {
                //$hemaInvoicesData[$date] = $value;
                $dates = $datesArr[$date];
                $linkFilterArr = array(
                    'filter[status][0]' => "Unpaid/Issued",
                    'filter[status][1]' => "Paid in Full",
                    'filter[status][2]' => "Paid Partially",
                    'filter[status][3]' => 'Refunded Fully',
                    'filter[status][4]' => 'Refunded Partially',
                    'filter[status][5]' => 'Pending',
                    'filter[startCreateDate]' => $dates['startDate'],
                    'filter[endCreateDate]' => $dates['endDate'],
                    'filter[version]' => "Latest",
                    //'filter[idSearch]' => $specialtyHemaObject->getUppercaseShortName()
                );
                $link = $this->container->get('router')->generate(
                    'translationalresearch_invoice_index_filter',
                    $linkFilterArr,
                    UrlGeneratorInterface::ABSOLUTE_URL
                );
                $hemaInvoicesData[$date] = array('value'=>$value,'link'=>$link);
            }

            //Covid
            $covidInvoicesData = array();
            foreach($covidResultStatArr as $date=>$value ) {
                //$covidInvoicesData[$date] = $value;
                $dates = $datesArr[$date];
                $linkFilterArr = array(
                    'filter[status][0]' => "Unpaid/Issued",
                    'filter[status][1]' => "Paid in Full",
                    'filter[status][2]' => "Paid Partially",
                    'filter[status][3]' => 'Refunded Fully',
                    'filter[status][4]' => 'Refunded Partially',
                    'filter[status][5]' => 'Pending',
                    'filter[startCreateDate]' => $dates['startDate'],
                    'filter[endCreateDate]' => $dates['endDate'],
                    'filter[version]' => "Latest",
                    //'filter[idSearch]' => $specialtyCovidObject->getUppercaseShortName()
                );
                $link = $this->container->get('router')->generate(
                    'translationalresearch_invoice_index_filter',
                    $linkFilterArr,
                    UrlGeneratorInterface::ABSOLUTE_URL
                );
                $covidInvoicesData[$date] = array('value'=>$value,'link'=>$link);
            }

            //Invoices
            $combinedInvoicesData = array();
            $combinedInvoicesData['AP/CP'] = $apcpInvoicesData;
            $combinedInvoicesData['Hematopathology'] = $hemaInvoicesData;
            $combinedInvoicesData['COVID-19'] = $covidInvoicesData;
            $chartsArray = $this->getStackedChart($combinedInvoicesData, $chartName, "stack"); //" getStackedChart("
        }
        //"46. Total Number of AP/CP and Hematopathology Invoices By Month" => "compare-projectspecialty-invoices",
        if( $chartType == "compare-projectspecialty-invoices" ) {
            $transresUtil = $this->container->get('transres_util');

            $specialtyObjects = $transresUtil->getTrpSpecialtyObjects();

            $datesArr = array();
            $specialtyResultStatArr = array();

            //get startDate and add 1 month until the date is less than endDate
            //$startDate->modify( 'first day of last month' );
            $startDate->modify( 'first day of this month' );
            do {
                $startDateLabel = $startDate->format('M-Y');

                foreach($specialtyObjects as $specialtyObject) {
                    $thisEndDate = clone $startDate;
                    //$thisEndDate->modify( 'first day of next month' );
                    $thisEndDate->modify('last day of this month');
                    $datesArr[$startDateLabel] = array('startDate'=>$startDate->format('m/d/Y'),'endDate'=>$thisEndDate->format('m/d/Y'));
                    //echo "StartDate=".$startDate->format("d-M-Y")."; EndDate=".$thisEndDate->format("d-M-Y")."<br>";
                    //$startDate,$endDate,$projectSpecialties,$states,$addOneEndDay

                    //                                            $startDate,$endDate,$projectSpecialties,$states,$addOneEndDay
                    $specialtyInvoices = $this->getInvoicesByFilter($startDate,$thisEndDate, array($specialtyObject));
                    $specialtyResultStatArr[$specialtyObject->getId()][$startDateLabel] = count($specialtyInvoices);
                }

                $startDate->modify( 'first day of next month' );

                //$apcpResultStatArr[$startDateLabel] = count($apcpInvoices);
                //$hemaResultStatArr[$startDateLabel] = count($hemaInvoices);
                //$covidResultStatArr[$startDateLabel] = count($covidInvoices);

            } while( $startDate < $endDate );
            //exit(111);

            $combinedInvoicesData = array();

            foreach($specialtyObjects as $specialtyObject) {
                $specialtyInvoicesData = array();
                $linkFilterArr = array();
                foreach($specialtyResultStatArr[$specialtyObject->getId()] as $date=>$value ) {
                    $dates = $datesArr[$date];
                    $linkFilterArr = array(
                        'filter[status][0]' => "Unpaid/Issued",
                        'filter[status][1]' => "Paid in Full",
                        'filter[status][2]' => "Paid Partially",
                        'filter[status][3]' => 'Refunded Fully',
                        'filter[status][4]' => 'Refunded Partially',
                        'filter[status][5]' => 'Pending',
                        'filter[startCreateDate]' => $dates['startDate'], //dueDate, therefore we can not filter invoices list
                        'filter[endCreateDate]' => $dates['endDate'],
                        'filter[version]' => "Latest",
                        //'filter[idSearch]' => $specialtyObject->getUppercaseShortName()
                    );
                    $link = $this->container->get('router')->generate(
                        'translationalresearch_invoice_index_filter',
                        $linkFilterArr,
                        UrlGeneratorInterface::ABSOLUTE_URL
                    );
                    $specialtyInvoicesData[$date] = array('value'=>$value,'link'=>$link);
                }

                $combinedInvoicesData[$specialtyObject->getName()] = $specialtyInvoicesData;
            }

            $chartsArray = $this->getStackedChart($combinedInvoicesData, $chartName, "stack"); //" getStackedChart("
        }

        //"47. Total Fees per Project Request Type" => "projects-fees-per-type",
        if( $chartType == "projects-fees-per-type" ) {
            $transresUtil = $this->container->get('transres_util');
            $projectTypeArr = array();
            $totalFees = 0;
            $projectCount = 0;

            $projects = $this->getProjectsByFilter($startDate,$endDate,$projectSpecialtyObjects);
            foreach($projects as $project) {
                $projectType = $project->getProjectType();
                if( $projectType ) {
                    $projectTypeId = $projectType->getId();
                    $projectTypeName = $projectType->getName();
                } else {
                    $projectTypeId = "No Type";
                    $projectTypeName = "No Type";;
                }

                $invoicesInfos = $transresUtil->getInvoicesInfosByProject($project);
                $totalFee = $invoicesInfos['total'];
                //count only projects with fees => link will give different number of projects
                if( !$totalFee || $totalFee == 0 ) {
                    continue;
                }

                $projectCount++;
                $totalFees = $totalFees + $totalFee;

                if( isset($projectTypeArr[$projectTypeId]) && isset($projectTypeArr[$projectTypeId]['value']) ) {
                    $totalFee = $projectTypeArr[$projectTypeId]['value'] + $totalFee;
                }
                $projectTypeArr[$projectTypeId]['value'] = $totalFee;
                $projectTypeArr[$projectTypeId]['objectid'] = $projectTypeId;
                $projectTypeArr[$projectTypeId]['pi'] = null;

                if( isset($projectTypeArr[$projectTypeId]) && isset($projectTypeArr[$projectTypeId]['projectTypeCount']) ) {
                    $projectTypeCount = $projectTypeArr[$projectTypeId]['projectTypeCount'] + 1;
                } else {
                    $projectTypeCount = 1;
                }
                $projectTypeArr[$projectTypeId]['projectTypeCount'] = $projectTypeCount;
                //echo $projectCount.": ".$projectTypeName . " [".$projectTypeId."]=".$projectTypeArr[$projectTypeId]['projectTypeCount']."<br>";

                $projectTypeArr[$projectTypeId]['label'] = $projectTypeName . " (".$projectTypeArr[$projectTypeId]['projectTypeCount']." projects)";
                //$projectTypeArr[$projectTypeId]['show-path'] = null; //"project-type";

                //link
                $linkFilterArr = array(
                    'filter[state][0]' => 'final_approved',
                    'filter[state][1]' => 'closed',
                    'filter[startDate]' => $startDateStr,
                    'filter[endDate]' => $endDateStr,
                    'filter[searchProjectType]' => $projectTypeId
                    //'filter[searchProjectType]' => $project->getId()
                );
                $count = 0;
                foreach($projectSpecialtyObjects as $projectSpecialtyObject) {
                    $linkFilterArr["filter[searchProjectType][".$count."]"] = $projectSpecialtyObject->getId();
                    $count++;
                }
                $link = $this->container->get('router')->generate(
                    'translationalresearch_project_index',
                    $linkFilterArr,
                    UrlGeneratorInterface::ABSOLUTE_URL
                );
                $projectTypeArr[$projectTypeId]['link'] = $link;
            }//foreach

//            echo "<pre>";
//            print_r($projectTypeArr);
//            echo "</pre>";
//            exit('111');

            //do not filter by top
            $quantityLimit = "Show all";

            //$chartName,$total,$prefix=null,$postfix="total"
            $postfix = "total for ".$projectCount." projects";
            $chartName = $this->getTitleWithTotal($chartName,$this->getNumberFormat($totalFees),"$",$postfix);
            $showOther = $this->getOtherStr($showLimited,"Project Types");
            $projectTypeArrTop = $this->getTopMultiArray($projectTypeArr,$showOther,$quantityLimit,array(),100); // function getTopMultiArray(

//            $layoutArray = array(
//                'height' => $this->height,
//                'width' => $this->width*1.2,
//                'title' => $chartName,
//                'margin' => array('c' => 400)
//            );

            $chartsArray = $this->getChartByMultiArray( $projectTypeArrTop, $filterArr, $chartName,"pie",null," : $","percent+label"); //function getChartByMultiArray(
            //$chartsArray = $this->getChart($projectTypeArrTop, $chartName,'pie',$layoutArray," : $");
        }

        //"48. Total Fees per Project Request Type of Funded Projects (Top 10) (linked)" => "projects-funded-fees-per-type",
        if( $chartType == "projects-funded-fees-per-type" ) {
            $transresUtil = $this->container->get('transres_util');
            $projectTypeArr = array();
            $totalFees = 0;
            $projectCount = 0;

            $projects = $this->getProjectsByFilter($startDate,$endDate,$projectSpecialtyObjects);
            foreach($projects as $project) {

                if( !$project->getFunded() ) {
                    continue;
                }

                $projectType = $project->getProjectType();
                if( $projectType ) {
                    $projectTypeId = $projectType->getId();
                    $projectTypeName = $projectType->getName();
                } else {
                    $projectTypeId = "No Type";
                    $projectTypeName = "No Type";;
                }

                $invoicesInfos = $transresUtil->getInvoicesInfosByProject($project);
                $totalFee = $invoicesInfos['total'];
                if( !$totalFee || $totalFee == 0 ) {
                    continue;
                }

                $projectCount++;
                $totalFees = $totalFees + $totalFee;

                if( isset($projectTypeArr[$projectTypeId]) && isset($projectTypeArr[$projectTypeId]['value']) ) {
                    $totalFee = $projectTypeArr[$projectTypeId]['value'] + $totalFee;
                }
                $projectTypeArr[$projectTypeId]['value'] = $totalFee;
                $projectTypeArr[$projectTypeId]['objectid'] = $projectTypeId;
                $projectTypeArr[$projectTypeId]['pi'] = null;
                //$projectTypeArr[$projectTypeId]['show-path'] = "project-type";

                if( isset($projectTypeArr[$projectTypeId]) && isset($projectTypeArr[$projectTypeId]['projectTypeCount']) ) {
                    $projectTypeCount = $projectTypeArr[$projectTypeId]['projectTypeCount'] + 1;
                } else {
                    $projectTypeCount = 1;
                }
                $projectTypeArr[$projectTypeId]['projectTypeCount'] = $projectTypeCount;

                //$projectTypeArr[$projectTypeId]['label'] = $projectTypeName;
                $projectTypeArr[$projectTypeId]['label'] = $projectTypeName . " (".$projectTypeArr[$projectTypeId]['projectTypeCount']." projects)";

                //link
                $linkFilterArr = array(
                    'filter[state][0]' => 'final_approved',
                    'filter[state][1]' => 'closed',
                    'filter[startDate]' => $startDateStr,
                    'filter[endDate]' => $endDateStr,
                    'filter[searchProjectType]' => $projectTypeId
                );
                $count = 0;
                foreach($projectSpecialtyObjects as $projectSpecialtyObject) {
                    $linkFilterArr["filter[searchProjectType][".$count."]"] = $projectSpecialtyObject->getId();
                    $count++;
                }
                $link = $this->container->get('router')->generate(
                    'translationalresearch_project_index',
                    $linkFilterArr,
                    UrlGeneratorInterface::ABSOLUTE_URL
                );
                $projectTypeArr[$projectTypeId]['link'] = $link;
            }

            //do not filter by top
            $quantityLimit = "Show all";

            $postfix = "total for ".$projectCount." projects";
            $chartName = $this->getTitleWithTotal($chartName,$this->getNumberFormat($totalFees),"$",$postfix);
            $showOther = $this->getOtherStr($showLimited,"Project Types");
            $projectTypeArrTop = $this->getTopMultiArray($projectTypeArr,$showOther,$quantityLimit,array(),100);

            $chartsArray = $this->getChartByMultiArray( $projectTypeArrTop, $filterArr, $chartName,"pie",null," : $","percent+label");
        }

        //"49. Total Fees per Project Request Type of Non-Funded Projects (linked)" => "projects-unfunded-fees-per-type",
        if( $chartType == "projects-unfunded-fees-per-type" ) {
            $transresUtil = $this->container->get('transres_util');
            $projectTypeArr = array();
            $totalFees = 0;
            $projectCount = 0;

            $projects = $this->getProjectsByFilter($startDate,$endDate,$projectSpecialtyObjects);
            foreach($projects as $project) {

                if( $project->getFunded() ) {
                    continue;
                }

                $projectType = $project->getProjectType();
                if( $projectType ) {
                    $projectTypeId = $projectType->getId();
                    $projectTypeName = $projectType->getName();
                } else {
                    $projectTypeId = "No Type";
                    $projectTypeName = "No Type";;
                }

                $invoicesInfos = $transresUtil->getInvoicesInfosByProject($project);
                $totalFee = $invoicesInfos['total'];
                if( !$totalFee || $totalFee == 0 ) {
                    continue;
                }

                $projectCount++;
                $totalFees = $totalFees + $totalFee;

                if( isset($projectTypeArr[$projectTypeId]) && isset($projectTypeArr[$projectTypeId]['value']) ) {
                    $totalFee = $projectTypeArr[$projectTypeId]['value'] + $totalFee;
                }
                $projectTypeArr[$projectTypeId]['value'] = $totalFee;
                $projectTypeArr[$projectTypeId]['objectid'] = $projectTypeId;
                $projectTypeArr[$projectTypeId]['pi'] = null;
                //$projectTypeArr[$projectTypeId]['show-path'] = "project-type";

                if( isset($projectTypeArr[$projectTypeId]) && isset($projectTypeArr[$projectTypeId]['projectTypeCount']) ) {
                    $projectTypeCount = $projectTypeArr[$projectTypeId]['projectTypeCount'] + 1;
                } else {
                    $projectTypeCount = 1;
                }
                $projectTypeArr[$projectTypeId]['projectTypeCount'] = $projectTypeCount;

                //$projectTypeArr[$projectTypeId]['label'] = $projectTypeName;
                $projectTypeArr[$projectTypeId]['label'] = $projectTypeName . " (".$projectTypeArr[$projectTypeId]['projectTypeCount']." projects)";

                //link
                $linkFilterArr = array(
                    'filter[state][0]' => 'final_approved',
                    'filter[state][1]' => 'closed',
                    'filter[startDate]' => $startDateStr,
                    'filter[endDate]' => $endDateStr,
                    'filter[searchProjectType]' => $projectTypeId
                );
                $count = 0;
                foreach($projectSpecialtyObjects as $projectSpecialtyObject) {
                    $linkFilterArr["filter[searchProjectType][".$count."]"] = $projectSpecialtyObject->getId();
                    $count++;
                }
                $link = $this->container->get('router')->generate(
                    'translationalresearch_project_index',
                    $linkFilterArr,
                    UrlGeneratorInterface::ABSOLUTE_URL
                );
                $projectTypeArr[$projectTypeId]['link'] = $link;
            }

            //do not filter by top
            $quantityLimit = "Show all";

            $postfix = "total for ".$projectCount." projects";
            $chartName = $this->getTitleWithTotal($chartName,$this->getNumberFormat($totalFees),"$",$postfix);
            $showOther = $this->getOtherStr($showLimited,"Project Types");
            $projectTypeArrTop = $this->getTopMultiArray($projectTypeArr,$showOther,$quantityLimit,array(),100);

            $chartsArray = $this->getChartByMultiArray( $projectTypeArrTop, $filterArr, $chartName,"pie",null," : $","percent+label");
        }

        //"50. Total Fees per Work Request Business Purpose" => "requests-fees-per-business-purpose",
        if( $chartType == "requests-fees-per-business-purpose" ) {
            $transresRequestUtil = $this->container->get('transres_request_util');
            $requestBusinessPurposeArr = array();
            $totalFees = 0;
            $projectCount = 0;
            $projectBusinessCount = array();
            //$testing = true;
            $testing = false;

            $requests = $this->getRequestsByFilter($startDate,$endDate,$projectSpecialtyObjects);
            foreach($requests as $thisTransRequest) {

                $fee = $transresRequestUtil->getTransResRequestSubTotal($thisTransRequest);
                if( !$fee || $fee == 0 ) {
                    continue;
                }

                $projectCount++;
                $totalFees = $totalFees + $fee;

                if($testing) {
                    echo "<br>thisTransRequest=" . $thisTransRequest->getOid() . "; fee=" . $fee . "<br>";
                }

                $businessPurposes = $thisTransRequest->getBusinessPurposes();

                if( count($businessPurposes) == 0 ) {
                    //$totalFees = $totalFees + $fee;
                    if( isset($requestBusinessPurposeArr["No Business Purpose"]) ) {
                        $fee = $requestBusinessPurposeArr["No Business Purpose"] + $fee;
                    }
                    $requestBusinessPurposeArr["No Business Purpose"] = $fee;

                    if( isset($projectBusinessCount["No Business Purpose"]) && isset($projectBusinessCount["No Business Purpose"]['projectTypeCount']) ) {
                        $projectTypeCount = $projectBusinessCount["No Business Purpose"]['projectTypeCount'] + 1;
                    } else {
                        $projectTypeCount = 1;
                    }
                    $projectBusinessCount["No Business Purpose"]['projectTypeCount'] = $projectTypeCount;
                    //$projectBusinessCount["No Business Purpose"][$thisTransRequest->getId()] = 1;
                }

                foreach($businessPurposes as $businessPurpose) {
                    $businessPurposeName = $businessPurpose->getName();
                    $thisFee = $fee;
                    if( isset($requestBusinessPurposeArr[$businessPurposeName]) ) {
                        $thisFee = $requestBusinessPurposeArr[$businessPurposeName] + $thisFee;
                    }
                    $requestBusinessPurposeArr[$businessPurposeName] = $thisFee;
                    if($testing) {
                        echo "businessPurposeName=".$businessPurposeName."; fee=".$thisFee."<br>";
                    }

                    if( isset($projectBusinessCount[$businessPurposeName]) && isset($projectBusinessCount[$businessPurposeName]['projectTypeCount']) ) {
                        $projectTypeCount = $projectBusinessCount[$businessPurposeName]['projectTypeCount'] + 1;
                    } else {
                        $projectTypeCount = 1;
                    }
                    $projectBusinessCount[$businessPurposeName]['projectTypeCount'] = $projectTypeCount;
                    //$projectBusinessCount[$businessPurposeName][$thisTransRequest->getId()] = 1;
                }
            }

            //Note: One request can have multiple buisness purposes => total in the title can be less than the sum of all work requests for each business purpose.
            $requestBusinessPurposeNewArr = array();
            foreach($requestBusinessPurposeArr as $businessPurposeName=>$value) {
                $newLabel = $businessPurposeName . " (".$projectBusinessCount[$businessPurposeName]['projectTypeCount']." work requests)";
                $requestBusinessPurposeNewArr[$newLabel] = $value;
            }

            //do not filter by top
            $quantityLimit = "Show all";

            $postfix = "total for ".$projectCount." work requests";
            $totalFees = $this->getNumberFormat($totalFees);
            $chartName = $this->getTitleWithTotal($chartName,$totalFees,"$",$postfix);
            $showOther = $this->getOtherStr($showLimited,"Business Purposes");
            $requestBusinessPurposeArrTop = $this->getTopArray($requestBusinessPurposeNewArr,$showOther,$quantityLimit,array(),100);
            $chartsArray = $this->getChart($requestBusinessPurposeArrTop, $chartName,'pie',$layoutArray," : $",null,null,"percent+label");

            if($testing) {
                echo "<br>";
                print_r($requestBusinessPurposeNewArr);
                echo "<br>totalFees=".$totalFees."<br>";
                exit();
            }
        }

        //"51. Total Fees per Work Request Business Purpose for Funded Projects" => "requests-funded-fees-per-business-purpose",
        if( $chartType == "requests-funded-fees-per-business-purpose" ) {
            $transresRequestUtil = $this->container->get('transres_request_util');
            $requestBusinessPurposeArr = array();
            $totalFees = 0;
            $projectCount = 0;
            $projectBusinessCount = array();

            $requests = $this->getRequestsByFilter($startDate,$endDate,$projectSpecialtyObjects);
            foreach($requests as $transRequest) {

                if( !$transRequest->getProject()->getFunded() ) {
                    continue;
                }

                $fee = $transresRequestUtil->getTransResRequestSubTotal($transRequest);
                if( !$fee || $fee == 0 ) {
                    continue;
                }

                $projectCount++;
                $totalFees = $totalFees + $fee;

                $businessPurposes = $transRequest->getBusinessPurposes();

                if( count($businessPurposes) == 0 ) {
                    if( isset($requestBusinessPurposeArr["No Business Purpose"]) ) {
                        $fee = $requestBusinessPurposeArr["No Business Purpose"] + $fee;
                    }
                    $requestBusinessPurposeArr["No Business Purpose"] = $fee;

                    if( isset($projectBusinessCount["No Business Purpose"]) && isset($projectBusinessCount["No Business Purpose"]['projectTypeCount']) ) {
                        $projectTypeCount = $projectBusinessCount["No Business Purpose"]['projectTypeCount'] + 1;
                    } else {
                        $projectTypeCount = 1;
                    }
                    $projectBusinessCount["No Business Purpose"]['projectTypeCount'] = $projectTypeCount;
                }

                foreach($businessPurposes as $businessPurpose) {
                    $businessPurposeName = $businessPurpose->getName();

                    if( isset($requestBusinessPurposeArr[$businessPurposeName]) ) {
                        $fee = $requestBusinessPurposeArr[$businessPurposeName] + $fee;
                    }
                    $requestBusinessPurposeArr[$businessPurposeName] = $fee;

                    if( isset($projectBusinessCount[$businessPurposeName]) && isset($projectBusinessCount[$businessPurposeName]['projectTypeCount']) ) {
                        $projectTypeCount = $projectBusinessCount[$businessPurposeName]['projectTypeCount'] + 1;
                    } else {
                        $projectTypeCount = 1;
                    }
                    $projectBusinessCount[$businessPurposeName]['projectTypeCount'] = $projectTypeCount;
                }
            }

            //Note: One request can have multiple buisness purposes => total in the title can be less than the sum of all work requests for each business purpose.
            $requestBusinessPurposeNewArr = array();
            foreach($requestBusinessPurposeArr as $businessPurposeName=>$value) {
                if( !$projectBusinessCount[$businessPurposeName]['projectTypeCount'] ) {
                    $projectBusinessCount[$businessPurposeName]['projectTypeCount'] = 1;
                }
                $newLabel = $businessPurposeName . " (".$projectBusinessCount[$businessPurposeName]['projectTypeCount']." work requests)";
                $requestBusinessPurposeNewArr[$newLabel] = $value;
            }

            //do not filter by top
            $quantityLimit = "Show all";
            $postfix = "total for ".$projectCount." work requests";

            $chartName = $this->getTitleWithTotal($chartName,$this->getNumberFormat($totalFees),"$",$postfix);
            $showOther = $this->getOtherStr($showLimited,"Business Purposes");
            $requestBusinessPurposeArrTop = $this->getTopArray($requestBusinessPurposeNewArr,$showOther,$quantityLimit,array(),100);
            $chartsArray = $this->getChart($requestBusinessPurposeArrTop, $chartName,'pie',$layoutArray," : $",null,null,"percent+label");
        }

        //"52. Total Fees per Work Request Business Purpose for Non-Funded Projects" => "requests-unfunded-fees-per-business-purpose",
        if( $chartType == "requests-unfunded-fees-per-business-purpose" ) {
            $transresRequestUtil = $this->container->get('transres_request_util');
            $requestBusinessPurposeArr = array();
            $totalFees = 0;
            $projectCount = 0;
            $projectBusinessCount = array();

            $requests = $this->getRequestsByFilter($startDate,$endDate,$projectSpecialtyObjects);
            foreach($requests as $transRequest) {

                if( $transRequest->getProject()->getFunded() ) {
                    continue;
                }

                $fee = $transresRequestUtil->getTransResRequestSubTotal($transRequest);
                if( !$fee || $fee == 0 ) {
                    continue;
                }

                $projectCount++;
                $totalFees = $totalFees + $fee;

                $businessPurposes = $transRequest->getBusinessPurposes();

                if( count($businessPurposes) == 0 ) {
                    if( isset($requestBusinessPurposeArr["No Business Purpose"]) ) {
                        $fee = $requestBusinessPurposeArr["No Business Purpose"] + $fee;
                    }
                    $requestBusinessPurposeArr["No Business Purpose"] = $fee;

                    if( isset($projectBusinessCount["No Business Purpose"]) && isset($projectBusinessCount["No Business Purpose"]['projectTypeCount']) ) {
                        $projectTypeCount = $projectBusinessCount["No Business Purpose"]['projectTypeCount'] + 1;
                    } else {
                        $projectTypeCount = 1;
                    }
                    $projectBusinessCount["No Business Purpose"]['projectTypeCount'] = $projectTypeCount;
                }

                foreach($businessPurposes as $businessPurpose) {
                    $businessPurposeName = $businessPurpose->getName();

                    if( isset($requestBusinessPurposeArr[$businessPurposeName]) ) {
                        $fee = $requestBusinessPurposeArr[$businessPurposeName] + $fee;
                    }
                    $requestBusinessPurposeArr[$businessPurposeName] = $fee;

                    if( isset($projectBusinessCount[$businessPurposeName]) && isset($projectBusinessCount[$businessPurposeName]['projectTypeCount']) ) {
                        $projectTypeCount = $projectBusinessCount[$businessPurposeName]['projectTypeCount'] + 1;
                    } else {
                        $projectTypeCount = 1;
                    }
                    $projectBusinessCount[$businessPurposeName]['projectTypeCount'] = $projectTypeCount;
                }
            }

            //Note: One request can have multiple buisness purposes => total in the title can be less than the sum of all work requests for each business purpose.
            $requestBusinessPurposeNewArr = array();
            foreach($requestBusinessPurposeArr as $businessPurposeName=>$value) {
                if( !$projectBusinessCount[$businessPurposeName]['projectTypeCount'] ) {
                    $projectBusinessCount[$businessPurposeName]['projectTypeCount'] = 1;
                }
                $newLabel = $businessPurposeName . " (".$projectBusinessCount[$businessPurposeName]['projectTypeCount']." work requests)";
                $requestBusinessPurposeNewArr[$newLabel] = $value;
            }

            //do not filter by top
            $quantityLimit = "Show all";
            $postfix = "total for ".$projectCount." work requests";

            $chartName = $this->getTitleWithTotal($chartName,$this->getNumberFormat($totalFees),"$",$postfix);
            $showOther = $this->getOtherStr($showLimited,"Business Purposes");
            $requestBusinessPurposeArrTop = $this->getTopArray($requestBusinessPurposeNewArr,$showOther,$quantityLimit,array(),100);
            $chartsArray = $this->getChart($requestBusinessPurposeArrTop, $chartName,'pie',$layoutArray," : $",null,null,"percent+label");
        }

        //"53. Turn-around Statistics: Number of Days each “Completed and Notified” Work Request took with the Name of who marked it as completed" => "turn-around-statistics-days-complete-per-request-with-product-by-user",
        if( $chartType == "turn-around-statistics-days-complete-per-request-with-user" ) {
            $averageDays = array();

            $thisEndDate = clone $startDate;
            $thisEndDate->modify('last day of this month');

            $statuses = array("completedNotified");
            $transRequests = $this->getRequestsByAdvanceFilter($startDate, $thisEndDate, $projectSpecialtyObjects, $productservice, $statuses);

            foreach ($transRequests as $transRequest) {

                //Number of days to go from Submitted to Completed
                $submitted = $transRequest->getCreateDate();

                $completed = $transRequest->getCompletedDate();
                if (!$completed) {
                    continue;
                }

//                //1) calculate days
//                $dDiff = $submitted->diff($completed);
//                //echo $dDiff->format('%R'); // use for point out relation: smaller/greater
//                $days = $dDiff->days;
//                //echo "days=".$days."<br>";
//                $days = intval($days);
                $days = $this->calculateDays($submitted,$completed);

                if( !$days || $days == 0 ) {
                    $days = 1;
                }

                $index = $transRequest->getOid();
                $completedUser = $transRequest->getCompletedBy();
                if( !$completedUser ) {
                    $completedUser = $transRequest->getUpdateUser();
                }
                if( $completedUser ) {
                    $index = $index . ", " . $completedUser->getUsernameOptimal();
                }

//                if( isset($averageDays[$index]) ) {
//                    $days = $averageDays[$index] + $days;
//                }
//                $averageDays[$index] = $days;

                $link = $this->container->get('router')->generate(
                    'translationalresearch_request_show',
                    array("id"=>$transRequest->getId()),
                    UrlGeneratorInterface::ABSOLUTE_URL
                );

                if( isset($averageDays[$index]) && isset($averageDays[$index]['value']) ) {
                    $days = $averageDays[$index]['value'] + $days;
                }
                $averageDays[$index] = array("value"=>$days,"link"=>$link);

            }//foreach

            $layoutArray = array(
                'height' => $this->height*1.5,
                'width' => $this->width,
                'title' => $chartName,
                'margin' => array('b' => 400)
            );

            //exit("Exit");

            $valuePrefixLabel = "(";
            $valuePostfixLabel = " days)";
            $chartsArray = $this->getChart($averageDays, $chartName,'bar',$layoutArray,$valuePrefixLabel,$valuePostfixLabel); // getChart(
        }

        //"54. Turn-around Statistics: Top 50 most delinquent invoices" => "turn-around-statistics-delayed-unpaid-invoices-by-days",
        if( $chartType == "turn-around-statistics-delayed-unpaid-invoices-by-days" ) {
            $transresRequestUtil = $this->container->get('transres_request_util');

            $invoiceDueDaysArr = array();
            //$invoiceDueArr = array();

            //get unpaid and delayd invoices
            //$invoices = $transresRequestUtil->getOverdueInvoices();
            $invoiceStates = array("Unpaid/Issued");
            $invoices = $this->getInvoicesByFilter($startDate,$endDate,$projectSpecialtyObjects,$invoiceStates,true);

            foreach($invoices as $invoice) {

                $nowDate = new \DateTime();
                $dueDate = $invoice->getDueDate();
                if( !$dueDate ) {
                    continue; //ignore invoices without duedate
                }

                $days = $this->calculateDays($dueDate,$nowDate);

                //APCP843-REQ16111-V1 to PIFirstName PILastName X days ago on MM/DD/YY for $XXX.XX - (123) 444-5555
                $index = $invoice->getOid();
                $pi = $invoice->getPrincipalInvestigator();
                if( $pi ) {
                    $index = $index . " to " . $pi->getUsernameOptimal();
                }
                $index = $index . " " . $days . " days ago";

//                $issuedDate = $invoice->getIssuedDate();
//                if( $issuedDate ) {
//                    $index = $index . " on " . $issuedDate->format("m/d/Y");
//                } else {
//                    $index = $index . " due on " . $dueDate->format("m/d/Y");
//                }
                $index = $index . " due on " . $dueDate->format("m/d/Y");

                $due = $invoice->getDue();
                if( $due ) {
                    $index = $index . " for $" . $this->getNumberFormat($due);
                }
                $phone = $pi->getSinglePhone();
                if( $phone ) {
                    $index = $index . " - " . $phone;
                }

                //$invoiceDueDaysArr[$index] = $days;
                $invoiceShowUrl = $transresRequestUtil->getInvoiceShowUrl($invoice,false,$invoice->getOid(),true);
                $invoiceDueDaysArr[$index] = array('value'=>$days,'link'=>$invoiceShowUrl);

                //$invoiceDueArr[$index] = $invoice->getDue();

            }//foreach

            //$titleCount = $titleCount . " (invoices ".count($invoices).")";

            $layoutArray = array(
                'height' => $this->height*1.5,
                'width' => $this->width,
                'title' => $chartName,
                'margin' => array('b' => 700)
            );

//            $descriptionArr = array(
//                array(
//                    'descrPrefix'   => "due $",
//                    'descrPostfix'  => null,
//                    'valuePrefix'   => ": (",
//                    'valuePostfix'  => " overdue days)",
//                    'descrColor'    => "red",
//                    'descrType'     => "money",
//                    'descrValueArr' => $invoiceDueArr
//                ),
//            );
            $descriptionArr = array();

            //$chartName = $this->getTitleWithTotal($chartName,count($invoices));
            //109 unpaid invoices in total
            $chartName = $chartName . " - " . count($invoices) . " unpaid invoices in total";

            //$showOther = $this->getOtherStr($showLimited,"Invoices");
            //$limit=50
            $invoiceDueDaysArrTop = $this->getTopArray($invoiceDueDaysArr,false,$quantityLimit,$descriptionArr,$maxLen=100);
            arsort($invoiceDueDaysArrTop);
            $chartsArray = $this->getChart($invoiceDueDaysArrTop, $chartName,'bar',$layoutArray);
        }

        //"55. Number of reminder emails sent per month (linked)" => "reminder-emails-per-month",
        if( $chartType == "reminder-emails-per-month" ) {
            $transresUtil = $this->container->get('transres_util');

            if( count($projectSpecialtyObjects) > 0 ) {
                $projectSpecialtyObject = $projectSpecialtyObjects[0];
            } else {
                $projectSpecialtyObject = null;
            }

            //////// Construct link ////////
            $ProjectReminderEventTypeId = null;
            $RequestReminderEventTypeId = null;
            $InvoiceReminderEventTypeId = null;
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:EventTypeList'] by [EventTypeList::class]
            $ProjectReminderEventType = $this->em->getRepository(EventTypeList::class)->findOneByName("Project Reminder Email");
            if( $ProjectReminderEventType ) {
                $ProjectReminderEventTypeId = $ProjectReminderEventType->getId();
            }
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:EventTypeList'] by [EventTypeList::class]
            $RequestReminderEventType = $this->em->getRepository(EventTypeList::class)->findOneByName("Work Request Reminder Email");
            if( $RequestReminderEventType ) {
                $RequestReminderEventTypeId = $RequestReminderEventType->getId();
            }
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:EventTypeList'] by [EventTypeList::class]
            $InvoiceReminderEventType = $this->em->getRepository(EventTypeList::class)->findOneByName("Unpaid Invoice Reminder Email");
            if( $InvoiceReminderEventType ) {
                $InvoiceReminderEventTypeId = $InvoiceReminderEventType->getId();
            }

            $linkFilterArr = array(
                //'filter[startdate]' => $startDateStr,
                //'filter[enddate]' => $endDateStr,
                'filter[eventType][0]' => $ProjectReminderEventTypeId,
                'filter[eventType][1]' => $RequestReminderEventTypeId,
                'filter[eventType][2]' => $InvoiceReminderEventTypeId
            );

//            $link = $this->container->get('router')->generate(
//                'translationalresearch_logger',
//                $linkFilterArr,
//                UrlGeneratorInterface::ABSOLUTE_URL
//            );
            //////// EOF Construct link ////////

            $unpaidInvoicesArr = array();
            $delayedProjectsArr = array();
            $delayedRequestsArr = array();
            $delayedCompletedRequestsArr = array();
            $delayedCompletedNoInvoiceRequestsArr = array();
            $datesArr = array();

            $delayedProjectsCount = 0;
            $delayedRequestsCount = 0;
            $delayedCompletedRequestsCount = 0;
            $delayedCompletedNoInvoiceRequestsCount = 0;
            $unpaidInvoicesCount = 0;

            $pendingStates = array(
                'active',
                'pendingInvestigatorInput',
                'pendingHistology',
                'pendingImmunohistochemistry',
                'pendingMolecular',
                'pendingCaseRetrieval',
                'pendingTissueMicroArray',
                'pendingSlideScanning'
            );
            $completedStates = array(
                'completed'
            );
            $completedNoInvoiceStates = array(
                'completedNotified'
            );

            //get startDate and add 1 month until the date is less than endDate
            //$startDate->modify( 'first day of last month' );
            $startDate->modify( 'first day of this month' );
            do {
                $startDateLabel = $startDate->format('M-Y');
                $thisEndDate = clone $startDate;
                //$thisEndDate->modify( 'first day of next month' );
                $thisEndDate->modify('last day of this month');
                $datesArr[$startDateLabel] = array('startDate'=>$startDate->format('m/d/Y'),'endDate'=>$thisEndDate->format('m/d/Y'));
                //echo "StartDate=".$startDate->format("d-M-Y")."; EndDate=".$thisEndDate->format("d-M-Y")."<br>";

                $unpaidInvoicesRemindersCount = $transresUtil->getUnpaidInvoiceRemindersCount($startDate,$thisEndDate,$projectSpecialtyObjects);
                $delayedProjectRemindersCount = $transresUtil->getDelayedProjectRemindersCount($startDate,$thisEndDate,$projectSpecialtyObjects);
                $delayedRequestRemindersCount = $transresUtil->getDelayedRequestRemindersCount($startDate,$thisEndDate,$projectSpecialtyObjects,$pendingStates);
                $delayedCompletedRequestRemindersCount = $transresUtil->getDelayedRequestRemindersCount($startDate,$thisEndDate,$projectSpecialtyObjects,$completedStates);
                $delayedCompletedNoInvoiceRequestRemindersCount = $transresUtil->getDelayedRequestRemindersCount($startDate,$thisEndDate,$projectSpecialtyObjects,$completedNoInvoiceStates);

                $startDate->modify( 'first day of next month' );

                $unpaidInvoicesArr[$startDateLabel] = $unpaidInvoicesRemindersCount;
                $unpaidInvoicesCount += $unpaidInvoicesRemindersCount;

                $delayedProjectsArr[$startDateLabel] = $delayedProjectRemindersCount;
                $delayedProjectsCount += $delayedProjectRemindersCount;

                $delayedRequestsArr[$startDateLabel] = $delayedRequestRemindersCount;
                $delayedRequestsCount += $delayedRequestRemindersCount;

                $delayedCompletedRequestsArr[$startDateLabel] = $delayedCompletedRequestRemindersCount;
                $delayedCompletedRequestsCount += $delayedCompletedRequestRemindersCount;

                $delayedCompletedNoInvoiceRequestsArr[$startDateLabel] = $delayedCompletedNoInvoiceRequestRemindersCount;
                $delayedCompletedNoInvoiceRequestsCount += $delayedCompletedNoInvoiceRequestRemindersCount;

            } while( $startDate < $endDate );


            //Reminders
            $combinedData = array();
            //$combinedData['Unpaid Invoices'] = $unpaidInvoicesArr;
            $delayedInvoicesData = array();
            foreach($unpaidInvoicesArr as $date=>$value ) {

                $dates = $datesArr[$date];
                $linkFilterArr['filter[startdate]'] = $dates['startDate'];
                $linkFilterArr['filter[enddate]'] = $dates['endDate'];
                $link = $this->container->get('router')->generate(
                    'translationalresearch_logger',
                    $linkFilterArr,
                    UrlGeneratorInterface::ABSOLUTE_URL
                );

                $delayedInvoicesData[$date] = array('value'=>$value,'link'=>$link);
            }
            $combinedData["$unpaidInvoicesCount reminder emails for unpaid Invoices"] = $delayedInvoicesData;

            //$combinedData['Delayed Project Requests'] = $delayedProjectsArr;
            //Use IRB review delayed days for all states
            $modifiedState = "irbreview";
            $projectReminderDelayField = 'projectReminderDelay'.$modifiedState;
            $reminderDelay = $transresUtil->getTransresSiteProjectParameter($projectReminderDelayField, null, $projectSpecialtyObject);
            if (!$reminderDelay) {
                $reminderDelay = 14; //default 14 days
            }
            //$combinedData["Project requests taking longer than $reminderDelay days to review"] = $delayedProjectsArr;
            //show event log
            $delayedProjectsData = array();
            foreach($delayedProjectsArr as $date=>$value ) {

                $dates = $datesArr[$date];
                $linkFilterArr['filter[startdate]'] = $dates['startDate'];
                $linkFilterArr['filter[enddate]'] = $dates['endDate'];
                $link = $this->container->get('router')->generate(
                    'translationalresearch_logger',
                    $linkFilterArr,
                    UrlGeneratorInterface::ABSOLUTE_URL
                );

                $delayedProjectsData[$date] = array('value'=>$value,'link'=>$link);
            }
            $combinedData["$delayedProjectsCount reminder emails for project requests taking longer than $reminderDelay days to review"] = $delayedProjectsData;

            //$combinedData['Delayed Pending Work Request'] = $delayedRequestsArr;
            $reminderDelay = $transresUtil->getTransresSiteProjectParameter("pendingRequestReminderDelay", null, $projectSpecialtyObject);
            if (!$reminderDelay) {
                $reminderDelay = 28; //default 28 days
            }
            //$combinedData["Work requests taking longer than $reminderDelay days to complete"] = $delayedRequestsArr;
            $delayedRequestsData = array();
            foreach($delayedRequestsArr as $date=>$value ) {

                $dates = $datesArr[$date];
                $linkFilterArr['filter[startdate]'] = $dates['startDate'];
                $linkFilterArr['filter[enddate]'] = $dates['endDate'];
                $link = $this->container->get('router')->generate(
                    'translationalresearch_logger',
                    $linkFilterArr,
                    UrlGeneratorInterface::ABSOLUTE_URL
                );

                $delayedRequestsData[$date] = array('value'=>$value,'link'=>$link);
            }
            $combinedData["$delayedRequestsCount reminder emails for work requests taking longer than $reminderDelay days to complete"] = $delayedRequestsData;

            //$combinedData['Delayed Completed Work Request'] = $delayedCompletedRequestsArr;
            $reminderDelay = $transresUtil->getTransresSiteProjectParameter("completedRequestReminderDelay", null, $projectSpecialtyObject);
            if (!$reminderDelay) {
                $reminderDelay = 4; //default 4 days
            }
            //$combinedData["Work requests completed for over $reminderDelay days in need of submitter notifications"] = $delayedCompletedRequestsArr;
            $delayedCompletedRequestsData = array();
            foreach($delayedCompletedRequestsArr as $date=>$value ) {

                $dates = $datesArr[$date];
                $linkFilterArr['filter[startdate]'] = $dates['startDate'];
                $linkFilterArr['filter[enddate]'] = $dates['endDate'];
                $link = $this->container->get('router')->generate(
                    'translationalresearch_logger',
                    $linkFilterArr,
                    UrlGeneratorInterface::ABSOLUTE_URL
                );

                $delayedCompletedRequestsData[$date] = array('value'=>$value,'link'=>$link);
            }
            $combinedData["$delayedCompletedRequestsCount reminder emails for work requests completed for over $reminderDelay days in need of submitter notifications"] = $delayedCompletedRequestsData;

            //$combinedData['Delayed Completed and Notified Work Request without Invoices'] = $delayedCompletedNoInvoiceRequestsArr;
            $reminderDelay = $transresUtil->getTransresSiteProjectParameter("completedNoInvoiceRequestReminderDelay", null, $projectSpecialtyObject);
            if (!$reminderDelay) {
                $reminderDelay = 7; //default 7 days
            }
            //$combinedData["Work requests completed for over $reminderDelay days without invoices"] = $delayedCompletedNoInvoiceRequestsArr;
            $delayedCompletedNoInvoiceRequestsData = array();
            foreach($delayedCompletedNoInvoiceRequestsArr as $date=>$value ) {

                $dates = $datesArr[$date];
                $linkFilterArr['filter[startdate]'] = $dates['startDate'];
                $linkFilterArr['filter[enddate]'] = $dates['endDate'];
                $link = $this->container->get('router')->generate(
                    'translationalresearch_logger',
                    $linkFilterArr,
                    UrlGeneratorInterface::ABSOLUTE_URL
                );

                $delayedCompletedNoInvoiceRequestsData[$date] = array('value'=>$value,'link'=>$link);
            }
            $combinedData["$delayedCompletedNoInvoiceRequestsCount reminder emails for work requests completed for over $reminderDelay days without invoices"] = $delayedCompletedNoInvoiceRequestsData;

            //Total emails
            $totalEmails = $delayedProjectsCount + $delayedRequestsCount + $delayedCompletedRequestsCount + $delayedCompletedNoInvoiceRequestsCount + $unpaidInvoicesCount;
            if( $totalEmails ) {
                $chartName = $chartName . " ($totalEmails total)";
            }

            //TODO: increase width of the chart
            $layoutArray = array(
                'height' => $this->height,
                'width' => $this->width*1.2,
                'title' => $chartName,
                //'margin' => array('b' => 700)
            );

            $chartsArray = $this->getStackedChart($combinedData, $chartName, "stack", $layoutArray); //" function getStackedChart("
        }

        //"56. Number of successful log in events for the TRP site per month" => "successful-logins-trp",
        if( $chartType == "successful-logins-trp" ) {
            $transresUtil = $this->container->get('transres_util');

            $loginsArr = array();
            $totalLoginCount = 0;

            //$startDate->modify( 'first day of last month' );
            $startDate->modify( 'first day of this month' );
            do {
                $startDateLabel = $startDate->format('M-Y');
                $thisEndDate = clone $startDate;
                //$thisEndDate->modify( 'first day of next month' );
                $thisEndDate->modify('last day of this month');
                $datesArr[$startDateLabel] = array('startDate'=>$startDate->format('m/d/Y'),'endDate'=>$thisEndDate->format('m/d/Y'));
                //echo "StartDate=".$startDate->format("d-M-Y")."; EndDate=".$thisEndDate->format("d-M-Y")."<br>";

                $loginCount = $transresUtil->getLoginCount($startDate,$thisEndDate,'translationalresearch');
                $totalLoginCount += $loginCount;

                $startDate->modify( 'first day of next month' );

                $loginsArr[$startDateLabel] = $loginCount;


            } while( $startDate < $endDate );

            $combinedData["TRP Login"] = $loginsArr;

            $chartName = $chartName . " (" . $totalLoginCount . " Total)";

            $chartsArray = $this->getStackedChart($combinedData, $chartName, "stack");
        }

        //"57. Number of successful log in events per site" => "successful-logins-site"
        if( $chartType == "successful-logins-site" ) {
            $transresUtil = $this->container->get('transres_util');

            $loginsEmployeesArr = array();
            $loginsTranslationalresearchArr = array();
            $loginsFellappArr = array();
            $loginsVacreqArr = array();
            $loginsCalllogArr = array();
            $loginsCrnArr = array();
            $loginsResappArr = array();
            //$loginsScanArr = array();

            $totalLoginCount = 0;
            $loginCountCalllog = 0;
            $loginCountVacreq = 0;
            $loginCountFellapp = 0;
            $loginCountEmpl = 0;
            $loginCountTrp = 0;
            $loginCountCrn = 0;
            $loginCountResapp = 0;

            //$startDate->modify( 'first day of last month' );
            $startDate->modify( 'first day of this month' );
            do {
                $startDateLabel = $startDate->format('M-Y');
                $thisEndDate = clone $startDate;
                //$thisEndDate->modify( 'first day of next month' );
                $thisEndDate->modify('last day of this month');
                $datesArr[$startDateLabel] = array('startDate'=>$startDate->format('m/d/Y'),'endDate'=>$thisEndDate->format('m/d/Y'));
                //echo "StartDate=".$startDate->format("d-M-Y")."; EndDate=".$thisEndDate->format("d-M-Y")."<br>";

                $loginEmployeesCount = $transresUtil->getLoginCount($startDate,$thisEndDate,'employees');
                $loginsEmployeesArr[$startDateLabel] = $loginEmployeesCount;
                $totalLoginCount += $loginEmployeesCount;
                $loginCountEmpl = $loginCountEmpl + $loginEmployeesCount;

                $loginTranslationalresearchCount = $transresUtil->getLoginCount($startDate,$thisEndDate,'translationalresearch');
                $loginsTranslationalresearchArr[$startDateLabel] = $loginTranslationalresearchCount;
                $totalLoginCount += $loginTranslationalresearchCount;
                $loginCountTrp = $loginCountTrp + $loginTranslationalresearchCount;

                $loginFellappCount = $transresUtil->getLoginCount($startDate,$thisEndDate,'fellapp');
                $loginsFellappArr[$startDateLabel] = $loginFellappCount;
                $totalLoginCount += $loginFellappCount;
                $loginCountFellapp = $loginCountFellapp + $loginFellappCount;

                $loginVacreqCount = $transresUtil->getLoginCount($startDate,$thisEndDate,'vacreq');
                $loginsVacreqArr[$startDateLabel] = $loginVacreqCount;
                $totalLoginCount += $loginVacreqCount;
                $loginCountVacreq = $loginCountVacreq + $loginVacreqCount;

                $loginCalllogCount = $transresUtil->getLoginCount($startDate,$thisEndDate,'calllog');
                $loginsCalllogArr[$startDateLabel] = $loginCalllogCount;
                $totalLoginCount += $loginCalllogCount;
                $loginCountCalllog = $loginCountCalllog + $loginCalllogCount;

                $loginCrnCount = $transresUtil->getLoginCount($startDate,$thisEndDate,'crn');
                $loginsCrnArr[$startDateLabel] = $loginCrnCount;
                $totalLoginCount += $loginCrnCount;
                $loginCountCrn = $loginCountCrn + $loginCrnCount;

                $loginResappCount = $transresUtil->getLoginCount($startDate,$thisEndDate,'resapp');
                $loginsResappArr[$startDateLabel] = $loginResappCount;
                $totalLoginCount += $loginResappCount;
                $loginCountResapp = $loginCountResapp + $loginResappCount;

                //$loginScanCount = $transresUtil->getLoginCount($startDate,$thisEndDate,'scan');
                //$loginsScanArr[$startDateLabel] = $loginScanCount;
                //$totalLoginCount += $loginScanCount;

                $startDate->modify( 'first day of next month' );



            } while( $startDate < $endDate );

            $combinedData["Translational Research log in events ($loginCountTrp)"] = $loginsTranslationalresearchArr;
            $combinedData["Employee Directory log in events ($loginCountEmpl)"] = $loginsEmployeesArr;
            $combinedData["Fellowship Applications log in events ($loginCountFellapp)"] = $loginsFellappArr;
            $combinedData["Vacation Request log in events ($loginCountVacreq)"] = $loginsVacreqArr;
            $combinedData["Call Log Book log in events ($loginCountCalllog)"] = $loginsCalllogArr;
            $combinedData["Critical Result Notification log in events ($loginCountCrn)"] = $loginsCrnArr;
            $combinedData["Residency Applications log in events ($loginCountResapp)"] = $loginsResappArr;
            //$combinedData["Glass Slide Scan Orders Logins"] = $loginsScanArr;

            $chartName = $chartName . " (" . $totalLoginCount . " Total)";

            $chartsArray = $this->getStackedChart($combinedData, $chartName, "stack");

            //dump($loginsEmployeesArr);
            //dump($loginsResappArr);
            //dump($chartsArray);
        }

        //"58. Number of successful log in events per user" => "successful-logins-user"
//        if( $chartType == "successful-logins-user" ) {
//            $transresUtil = $this->container->get('transres_util');
//
//            $unique = true;
//            //$unique = false;
//
//            //$loginsArr = array();
//            $loginsUserArr = array();
//            $userArr = array();
//
//            //$totalLoginCount = 0;
//
//            $startDate->modify( 'first day of last month' );
//            do {
//                $startDateLabel = $startDate->format('M-Y');
//                $thisEndDate = clone $startDate;
//                $thisEndDate->modify( 'first day of next month' );
//                $datesArr[$startDateLabel] = array('startDate'=>$startDate->format('m/d/Y'),'endDate'=>$thisEndDate->format('m/d/Y'));
//                //echo "StartDate=".$startDate->format("d-M-Y")."; EndDate=".$thisEndDate->format("d-M-Y")."<br>";
//
//                $loginsArr = $transresUtil->getLoginsUniqueUser($startDate,$thisEndDate,$unique);
//
//                foreach($loginsArr as $loginUser) {
//                    $loginUserId = $loginUser['id'];
//                    if( isset($userArr[$loginUserId]) ) {
//                        $userTitle = $userArr[$loginUserId];
//                    } else {
//                        $user = $this->em->getRepository('AppUserdirectoryBundle:User')->find($loginUserId);
//                        $userTitle = $user->getUsernameOptimal();
//                        $userArr[$loginUserId] = $userTitle;
//                    }
//                    //$user = $loginUser['user'];
//                    //echo "user=".$user."<br>";
//                    $loginsUserArr[$userTitle][$startDateLabel]++;
//                    //$loginsUserArr[$startDateLabel][$login->getUser()->getUsernameOptimal()]++;
//
//                    //$totalLoginCount++;
//                }
//
//                //$loginsUserArr[$user->getUsernameOptimal()]++;
//
//                $startDate->modify( 'first day of next month' );
//
//            } while( $startDate < $endDate );
//
//            foreach($loginsUserArr as $startDateLabel=>$userDataArr) {
//                $combinedData[$startDateLabel] = $userDataArr;
//            }
//
//            //$combinedData["Translational Research Logins"] = $loginsTranslationalresearchArr;
//
//            //$chartName = $chartName . " (" . $totalLoginCount . " Total)";
//
//            $chartsArray = $this->getStackedChart($combinedData, $chartName, "stack");
//        }
        //"58. Number of unique users in a given month who successful log in, per site" => "successful-unique-logins-site-month"
        if( $chartType == "successful-unique-logins-site-month" ) {
            $transresUtil = $this->container->get('transres_util');

            //single bar for a given week would be divided by sub-site and each bar segment should show the total number of unique user logins

            $loginsEmployeesArr = array();
            $loginsTranslationalresearchArr = array();
            $loginsFellappArr = array();
            $loginsVacreqArr = array();
            $loginsCalllogArr = array();
            $loginsCrnArr = array();
            $loginsResappArr = array();
            //$loginsScanArr = array();

            $totalLoginCount = 0;
            $loginCountCalllog = 0;
            $loginCountVacreq = 0;
            $loginCountFellapp = 0;
            $loginCountEmpl = 0;
            $loginCountTrp = 0;
            $loginCountCrn = 0;
            $loginCountResapp = 0;
            $counter = 0;

            //echo "1 StartDate=".$startDate->format('d-M-Y')."<br>";

            //$startDate->modify( 'first day of last month' );
            $startDate->modify( 'first day of this month' );

            //$endDate->modify( 'last day of this month' );

            $interval = new \DateInterval('P1M');
            $dateRange = new \DatePeriod($startDate, $interval, $endDate);

            //echo "2 StartDate=".$startDate->format('d-M-Y')."<br>";

            //unique users for all sites. Not equal sum of the unique users per each site.
            //$loginTotalUniqueCount = $transresUtil->getLoginCount($startDate,$endDate,null,true);

            foreach( $dateRange as $startDate ) {
                //+6 days
                $thisEndDate = clone $startDate;
                //$thisEndDate->add(new \DateInterval('P6D'));
                $thisEndDate->add(new \DateInterval('P1M'));

                $startDateLabel = $startDate->format('d-M-Y');

                $datesArr[$startDateLabel] = array('startDate'=>$startDate->format('m/d/Y'),'endDate'=>$thisEndDate->format('m/d/Y'));
                //echo "StartDate=".$startDate->format("d-M-Y")."; EndDate=".$thisEndDate->format("d-M-Y")."<br>";

                $loginEmployeesCount = $transresUtil->getLoginCount($startDate,$thisEndDate,'employees',true);
                $loginsEmployeesArr[$startDateLabel] = $loginEmployeesCount;
                //$totalLoginCount += $loginEmployeesCount;
                $loginCountEmpl = $loginCountEmpl + $loginEmployeesCount;

                $loginTranslationalresearchCount = $transresUtil->getLoginCount($startDate,$thisEndDate,'translationalresearch',true);
                $loginsTranslationalresearchArr[$startDateLabel] = $loginTranslationalresearchCount;
                //$totalLoginCount += $loginTranslationalresearchCount;
                $loginCountTrp = $loginCountTrp + $loginTranslationalresearchCount;

                $loginFellappCount = $transresUtil->getLoginCount($startDate,$thisEndDate,'fellapp',true);
                $loginsFellappArr[$startDateLabel] = $loginFellappCount;
                //$totalLoginCount += $loginFellappCount;
                $loginCountFellapp = $loginCountFellapp + $loginFellappCount;

                $loginVacreqCount = $transresUtil->getLoginCount($startDate,$thisEndDate,'vacreq',true);
                $loginsVacreqArr[$startDateLabel] = $loginVacreqCount;
                //$totalLoginCount += $loginVacreqCount;
                $loginCountVacreq = $loginCountVacreq + $loginVacreqCount;

                $loginCalllogCount = $transresUtil->getLoginCount($startDate,$thisEndDate,'calllog',true);
                $loginsCalllogArr[$startDateLabel] = $loginCalllogCount;
                //$totalLoginCount += $loginCalllogCount;
                $loginCountCalllog = $loginCountCalllog + $loginCalllogCount;

                $loginResappCount = $transresUtil->getLoginCount($startDate,$thisEndDate,'resapp',true);
                $loginsResappArr[$startDateLabel] = $loginResappCount;
                $loginCountResapp = $loginCountResapp + $loginResappCount;

                $loginCrnCount = $transresUtil->getLoginCount($startDate,$thisEndDate,'crn',true);
                $loginsCrnArr[$startDateLabel] = $loginCrnCount;
                $loginCountCrn = $loginCountCrn + $loginCrnCount;

                $counter++;
            }

            //Average
            $loginCountAvgTrp = 0;
            $loginCountAvgEmpl = 0;
            $loginCountAvgFellapp = 0;
            $loginCountAvgVacreq = 0;
            $loginCountAvgCalllog = 0;
            $loginCountAvgCrn = 0;
            $loginCountAvgResapp = 0;
            if( $counter > 0 ) {
                $loginCountAvgTrp = round($loginCountTrp/$counter,2);
                $loginCountAvgEmpl = round($loginCountEmpl/$counter,2);
                $loginCountAvgFellapp = round($loginCountFellapp/$counter,2);
                $loginCountAvgVacreq = round($loginCountVacreq/$counter,2);
                $loginCountAvgCalllog = round($loginCountCalllog/$counter,2);
                $loginCountAvgCrn = round($loginCountCrn/$counter,2);
                $loginCountAvgResapp = round($loginCountResapp/$counter,2);
            }

            //($loginCountTrp on average per week; 2 total)
            $combinedData["Translational Research Users ($loginCountAvgTrp on average per month; $loginCountTrp total)"] = $loginsTranslationalresearchArr;
            $combinedData["Employee Directory Users ($loginCountAvgEmpl on average per month; $loginCountEmpl total)"] = $loginsEmployeesArr;
            $combinedData["Fellowship Applications Users ($loginCountAvgFellapp on average per month; $loginCountFellapp total)"] = $loginsFellappArr;
            $combinedData["Vacation Request Users ($loginCountAvgVacreq on average per month; $loginCountVacreq total)"] = $loginsVacreqArr;
            $combinedData["Call Log Book Users ($loginCountAvgCalllog on average per month; $loginCountCalllog total)"] = $loginsCalllogArr;
            $combinedData["Critical Result Notification Users ($loginCountAvgCrn on average per month; $loginCountCrn total)"] = $loginsCrnArr;
            $combinedData["Residency Applications Users ($loginCountAvgResapp on average per month; $loginCountResapp total)"] = $loginsResappArr;
            //$combinedData["Glass Slide Scan Orders Logins"] = $loginsScanArr;

            $totalLoginCount = $loginCountTrp+$loginCountEmpl+$loginCountFellapp+$loginCountVacreq+$loginCountCalllog+$loginCountCrn+$loginCountResapp;

            //$chartName = $chartName . " (" . $totalLoginCount . " Total)";
            //$chartName = $chartName . " (" . $loginTotalUniqueCount . " Total)";
            $chartName = $chartName . " (" . $totalLoginCount . " Total)";

            $chartsArray = $this->getStackedChart($combinedData, $chartName, "stack");
        }

        //"59. Number of unique users in a given week who successful log in, per site" => "successful-unique-logins-site-week",
        if( $chartType == "successful-unique-logins-site-week" ) {
            $transresUtil = $this->container->get('transres_util');

            //single bar for a given week would be divided by sub-site and each bar segment should show the total number of unique user logins

            $loginsEmployeesArr = array();
            $loginsTranslationalresearchArr = array();
            $loginsFellappArr = array();
            $loginsVacreqArr = array();
            $loginsCalllogArr = array();
            //$loginsScanArr = array();
            $loginsCrnArr = array();
            $loginsResappArr = array();

            $totalLoginCount = 0;
            $loginCountCalllog = 0;
            $loginCountVacreq = 0;
            $loginCountFellapp = 0;
            $loginCountEmpl = 0;
            $loginCountTrp = 0;
            $loginCountCrn = 0;
            $loginCountResapp = 0;
            $counter = 0;

            //$startDate->modify( 'first day of last month' );
            $startDate->modify( 'first day of this month' );

            //$endDate->modify( 'last day of this month' );

            $interval = new \DateInterval('P1W');
            $dateRange = new \DatePeriod($startDate, $interval, $endDate);

            //unique users for all sites. Not equal sum of the unique users per each site.
            //$loginTotalUniqueCount = $transresUtil->getLoginCount($startDate,$endDate,null,true);

            foreach( $dateRange as $startDate ) {
                //+6 days
                $thisEndDate = clone $startDate;
                $thisEndDate->add(new \DateInterval('P6D'));

                $startDateLabel = $startDate->format('d-M-Y');

                $datesArr[$startDateLabel] = array('startDate'=>$startDate->format('m/d/Y'),'endDate'=>$thisEndDate->format('m/d/Y'));
                //echo "StartDate=".$startDate->format("d-M-Y")."; EndDate=".$thisEndDate->format("d-M-Y")."<br>";

                $loginEmployeesCount = $transresUtil->getLoginCount($startDate,$thisEndDate,'employees',true);
                $loginsEmployeesArr[$startDateLabel] = $loginEmployeesCount;
                //$totalLoginCount += $loginEmployeesCount;
                $loginCountEmpl = $loginCountEmpl + $loginEmployeesCount;

                $loginTranslationalresearchCount = $transresUtil->getLoginCount($startDate,$thisEndDate,'translationalresearch',true);
                $loginsTranslationalresearchArr[$startDateLabel] = $loginTranslationalresearchCount;
                //$totalLoginCount += $loginTranslationalresearchCount;
                $loginCountTrp = $loginCountTrp + $loginTranslationalresearchCount;

                $loginFellappCount = $transresUtil->getLoginCount($startDate,$thisEndDate,'fellapp',true);
                $loginsFellappArr[$startDateLabel] = $loginFellappCount;
                //$totalLoginCount += $loginFellappCount;
                $loginCountFellapp = $loginCountFellapp + $loginFellappCount;

                $loginVacreqCount = $transresUtil->getLoginCount($startDate,$thisEndDate,'vacreq',true);
                $loginsVacreqArr[$startDateLabel] = $loginVacreqCount;
                //$totalLoginCount += $loginVacreqCount;
                $loginCountVacreq = $loginCountVacreq + $loginVacreqCount;

                $loginCalllogCount = $transresUtil->getLoginCount($startDate,$thisEndDate,'calllog',true);
                $loginsCalllogArr[$startDateLabel] = $loginCalllogCount;
                //$totalLoginCount += $loginCalllogCount;
                $loginCountCalllog = $loginCountCalllog + $loginCalllogCount;

                $loginCrnCount = $transresUtil->getLoginCount($startDate,$thisEndDate,'crn',true);
                $loginsCrnArr[$startDateLabel] = $loginCrnCount;
                $loginCountCrn = $loginCountCrn + $loginCrnCount;

                $loginResappCount = $transresUtil->getLoginCount($startDate,$thisEndDate,'resapp',true);
                $loginsResappArr[$startDateLabel] = $loginResappCount;
                $loginCountResapp = $loginCountResapp + $loginResappCount;

                $counter++;
            }

            //Average
            $loginCountAvgTrp = 0;
            $loginCountAvgEmpl = 0;
            $loginCountAvgFellapp = 0;
            $loginCountAvgVacreq = 0;
            $loginCountAvgCalllog = 0;
            $loginCountAvgCrn = 0;
            $loginCountAvgResapp = 0;
            if( $counter > 0 ) {
                $loginCountAvgTrp = round($loginCountTrp/$counter,2);
                $loginCountAvgEmpl = round($loginCountEmpl/$counter,2);
                $loginCountAvgFellapp = round($loginCountFellapp/$counter,2);
                $loginCountAvgVacreq = round($loginCountVacreq/$counter,2);
                $loginCountAvgCalllog = round($loginCountCalllog/$counter,2);
                $loginCountAvgCrn = round($loginCountCrn/$counter,2);
                $loginCountAvgResapp = round($loginCountResapp/$counter,2);
            }

            //($loginCountTrp on average per week; 2 total)
            $combinedData["Translational Research Users ($loginCountAvgTrp on average per week; $loginCountTrp total)"] = $loginsTranslationalresearchArr;
            $combinedData["Employee Directory Users ($loginCountAvgEmpl on average per week; $loginCountEmpl total)"] = $loginsEmployeesArr;
            $combinedData["Fellowship Applications Users ($loginCountAvgFellapp on average per week; $loginCountFellapp total)"] = $loginsFellappArr;
            $combinedData["Vacation Request Users ($loginCountAvgVacreq on average per week; $loginCountVacreq total)"] = $loginsVacreqArr;
            $combinedData["Call Log Book Users ($loginCountAvgCalllog on average per week; $loginCountCalllog total)"] = $loginsCalllogArr;
            $combinedData["Critical Result Notification Users ($loginCountAvgCrn on average per week; $loginCountCrn total)"] = $loginsCrnArr;
            $combinedData["Residency Applications Users ($loginCountAvgResapp on average per week; $loginCountResapp total)"] = $loginsResappArr;
            //$combinedData["Glass Slide Scan Orders Logins"] = $loginsScanArr;

            $totalLoginCount = $loginCountTrp+$loginCountEmpl+$loginCountFellapp+$loginCountVacreq+$loginCountCalllog+$loginCountCrn+$loginCountResapp;

//            $chartName = $chartName . " (" . $totalLoginCount . " Total)";
            //$chartName = $chartName . " (" . $loginTotalUniqueCount . " Total)";
            $chartName = $chartName . " (" . $totalLoginCount . " Total)";

            $chartsArray = $this->getStackedChart($combinedData, $chartName, "stack");
        }

        //"60. PIs with most projects" => "pis-with-most-projects"
        //for 60 total their project requests in any status except Draft or Canceled
        if( $chartType == "pis-with-most-projects" ) {

        }

        //"61. PIs with highest expenditures" => "pis-with-highest-expenditures"
        //for 61 total their paid invoices with status "Paid" for the time period in the filter well
        if( $chartType == "pis-with-highest-expenditures" ) {

        }

        //"60. Number of fellowship applicant by year" => "fellapp-number-applicant-by-year",
        if( $chartType == "fellapp-number-applicant-by-year" ) {
            $fellappUtil = $this->container->get('fellapp_util');

            //$perYear = false;
            //$perMonth = false;
            //$perYear = true;
            //$perMonth = true;

            //TODO: use shifted year: current year + 2 years
            //TODO: academic year or calendar year range?
            $startDate->modify('-4 year');
            $endDate->modify('+1 year');
            $startYear = $startDate->format('Y');
            $endYear = $endDate->format('Y');
            $endYearInt = intval($endYear);

            //$startEndDates = $fellappUtil->getAcademicYearStartEndDates($startYear,true,+2);
            $startEndDates = $fellappUtil->getAcademicYearStartEndDates($startYear,true);
            $academicStartDate = $startEndDates['startDate'];
            $academicEndDate = $startEndDates['endDate'];

            $totalCount = 0;

            do {
                $startDateLabel = $academicStartDate->format('Y');
                $startDateInt = intval($startDateLabel);

                //echo "startDateLabel=".$startDateLabel."<br>";
                //$thisEndDate = clone $startDate;
                //$thisEndDate->modify( 'first day of next month' );
//                if( $perYear ) {
//                    $thisEndDate->modify('last day of december this year');
//                }
//                if( $perMonth ) {
//                    $thisEndDate->modify('last day of this month');
//                }
                //echo "StartDate=".$startDate->format("d-M-Y")."; thisEndDate=".$thisEndDate->format("d-M-Y").": <br>";

                $fellapps = $fellappUtil->getFellAppByStatusAndYear(null,null,$startDateLabel);

                $academicStartDate->modify('+ 1 year');

//                if( $perYear ) {
//                    $startDate->modify('first day of january next year');
//                }
//                if( $perMonth ) {
//                    $startDate->modify('first day of next month');
//                }

                $fellappsCount = count($fellapps);
                $totalCount = $totalCount + $fellappsCount;

                $fellappArr[$startDateLabel] = $fellappsCount;

                //$descriptionArr[$startDateLabel] = " (" . count($invoices) . " invoices)";

            } while( $startDateInt < $endYearInt );

            //echo "totalPaidInvoiceFee=".$totalPaidInvoiceFee."; totalDueInvoiceFee=".$totalDueInvoiceFee."; totalInvoiceFee=".$totalInvoiceFee."<br>"; //7591754 7.591.754
            //exit('111');

            //$chartName = $this->getTitleWithTotal($chartName,$this->getNumberFormat($totalInvoiceFee),"$","Total");

            //increase vertical
            //tickformat: https://github.com/d3/d3-format/blob/main/README.md#locale_format
            $layoutArray = array(
                'height' => $this->height,//*1.3,
                'width' => $this->width,
                'title' => $chartName,
                'margin' => array('b'=>200),
                'xaxis' => array(
                    'tickformat' =>  "d",
                ),
                //'showlegend' => false
            );
            //$layoutArray = NULL;//array();

            $chartName = $chartName . " (" . $totalCount . " applications in total)";

            //stacked char
            //$combinedData = array();
            //$combinedData[] = $fellappArr;
            //$chartsArray = $this->getStackedChart($combinedData, $chartName, "stack", $layoutArray); //public function getStackedChart

            //bar chart
            $chartsArray = $this->getChart($fellappArr, $chartName,'bar',$layoutArray);
        }

        //"61-simple. Average sum of the USMLE scores for fellowship applicant by year" => "fellapp-average-usmle-scores-by-year",
        if( $chartType == "fellapp-average-usmle-scores-by-year-SIMPLE" ) {
            $fellappUtil = $this->container->get('fellapp_util');

            //$perYear = false;
            //$perMonth = false;

            //$perYear = true;
            //$perMonth = false;

            //echo "startDate=".$startDate->format('d-m-Y').", endDate=".$endDate->format('d-m-Y')."<br>";

            //TODO: use shifted year: current year + 2 years
            //TODO: academic year or calendar year range?
            //$startYear = $fellappUtil->getDefaultAcademicStartYear();
            $startDate->modify('-4 year');
            $endDate->modify('+1 year');
            $startYear = $startDate->format('Y');
            $endYear = $endDate->format('Y');
            $endYearInt = intval($endYear);

            //$startEndDates = $fellappUtil->getAcademicYearStartEndDates($startYear,true,+2);
            $startEndDates = $fellappUtil->getAcademicYearStartEndDates($startYear,true);
            $academicStartDate = $startEndDates['startDate'];
            $academicEndDate = $startEndDates['endDate'];
            //echo "academicStartDate=".$academicStartDate->format('d-m-Y').", academicEndDate=".$academicEndDate->format('d-m-Y')."<br>";
            //exit('111');

//            if( $perYear ) {
//                $startDate->modify('first day of january this year');
//            }
//            if( $perMonth ) {
//                $startDate->modify('first day of this month');
//            }
            
            $totalCount = 0;

            do {
                $startDateLabel = $academicStartDate->format('Y');
                $startDateInt = intval($startDateLabel);
                //echo "startDateLabel=".$startDateLabel."<br>";
                //$thisEndDate = clone $startDate;
                //$thisEndDate->modify( 'first day of next month' );
                //echo "StartDate=".$academicStartDate->format("d-M-Y")."; endYearInt=".$endYearInt.": <br>";

                $fellapps = $fellappUtil->getFellAppByStatusAndYear(null,null,$startDateLabel);

                $academicStartDate->modify('+ 1 year');

                $scoreAvg = 0;
                $scoreCounter = 0;
                $scoreSumTotal = 0;
                foreach($fellapps as $fellapp) {
                    $scoreSum = $fellapp->getUsmleAverage();
                    //echo $fellapp->getId().": scoreSum=$scoreSum <br>";
                    if( $scoreSum ) {
                        //echo $fellapp->getId().": scoreSum=$scoreSum <br>";
                        $scoreSumTotal = $scoreSumTotal + $scoreSum;
                        $scoreCounter++;
                    }
                }
                if( $scoreCounter > 0 ) {
                    //echo "$scoreSumTotal / $scoreCounter <br>";
                    $scoreAvg = round($scoreSumTotal / $scoreCounter);
                    //$scoreAvg = ($scoreSumTotal / $scoreCounter);
                }

                $fellappsCount = count($fellapps);
                $totalCount = $totalCount + $fellappsCount;

                $fellappArr[$startDateLabel] = $scoreAvg;

                //$descriptionArr[$startDateLabel] = " (" . count($invoices) . " invoices)";

            } while( $startDateInt < $endYearInt );

            //echo "totalPaidInvoiceFee=".$totalPaidInvoiceFee."; totalDueInvoiceFee=".$totalDueInvoiceFee."; totalInvoiceFee=".$totalInvoiceFee."<br>"; //7591754 7.591.754
            //exit('111');

            //$chartName = $this->getTitleWithTotal($chartName,$this->getNumberFormat($totalInvoiceFee),"$","Total");

            //increase vertical
            //tickformat: https://github.com/d3/d3-format/blob/main/README.md#locale_format
            $layoutArray = array(
                'height' => $this->height,//*1.3,
                'width' => $this->width,
                'title' => $chartName,
                'margin' => array('b'=>200),
                'xaxis' => array(
                    'tickformat' =>  "d",
                ),
                //'showlegend' => false
            );
            //$layoutArray = NULL;//array();

            $chartName = $chartName . " (" . $totalCount . " applications in total; USMLE Step 1,2, and 3)";
            //exit('$chartName='.$chartName);

            //stacked char
            //$combinedData = array();
            //$combinedData[] = $fellappArr;
            //$chartsArray = $this->getStackedChart($combinedData, $chartName, "stack", $layoutArray); //public function getStackedChart

            //bar chart
            $chartsArray = $this->getChart($fellappArr, $chartName,'bar',$layoutArray);

            //violin

        }
        //"61-OLD. Average sum of the USMLE scores for fellowship applicant by year" => "fellapp-average-usmle-scores-by-year-OLD",
        if( $chartType == "fellapp-average-usmle-scores-by-year-OLD" ) {
            $fellappUtil = $this->container->get('fellapp_util');

            //echo "startDate=".$startDate->format('d-m-Y').", endDate=".$endDate->format('d-m-Y')."<br>";

            //TODO: use shifted year: current year + 2 years
            //TODO: academic year or calendar year range?
            //$startYear = $fellappUtil->getDefaultAcademicStartYear();
            $startDate->modify('-4 year');
            $endDate->modify('+1 year');
            $startYear = $startDate->format('Y');
            $endYear = $endDate->format('Y');
            $endYearInt = intval($endYear);

            //$startEndDates = $fellappUtil->getAcademicYearStartEndDates($startYear,true,+2);
            $startEndDates = $fellappUtil->getAcademicYearStartEndDates($startYear,true);
            $academicStartDate = $startEndDates['startDate'];
            $academicEndDate = $startEndDates['endDate'];
            //echo "academicStartDate=".$academicStartDate->format('d-m-Y').", academicEndDate=".$academicEndDate->format('d-m-Y')."<br>";
            //exit('111');

            $usmleYearArr = array();
            $comlexYearArr = array();

            $totalScoreArr = array();
            $totalUsmleArr = array();
            $totalComlexArr = array();

//            $usmle1 = array();
//            $usmle2 = array();
//            $usmle3 = array();
//            $comlex1 = array();
//            $comlex2 = array();
//            $comlex3 = array();

//            $usmleCounter1 = 0;
//            $usmleCounter2 = 0;
//            $usmleCounter3 = 0;
//            $comlexCounter1 = 0;
//            $comlexCounter2 = 0;
//            $comlexCounter3 = 0;

            $usmleValueArr = array();
            $comlexValueArr = array();

            $totalCount = 0;

            do {
                $startDateLabel = $academicStartDate->format('Y');
                $startDateInt = intval($startDateLabel);
                //echo "startDateLabel=".$startDateLabel."<br>";
                //echo "StartDate=".$academicStartDate->format("d-M-Y")."; endYearInt=".$endYearInt.": <br>";

                $fellapps = $fellappUtil->getFellAppByStatusAndYear(null,null,$startDateLabel);

                $academicStartDate->modify('+ 1 year');

                //$usmleSingleYearArr = array();
                //$comlexSingleYearArr = array();

                foreach($fellapps as $fellapp) {

                    $usmleScoreAvg = 0;
                    $usmleScoreCounter = 0;
                    $usmleScoreSumTotal = 0;

                    $comlexScoreAvg = 0;
                    $comlexScoreCounter = 0;
                    $comlexScoreSumTotal = 0;

                    $usmleArr = $fellapp->getAllUsmleArr();
                    if( count($usmleArr) > 0 ) {
                        if( $usmleArr[1] !== NULL ) {
                            $usmleScoreSumTotal = $usmleScoreSumTotal + $usmleArr[1];
                            $usmleScoreCounter++;
                        }
                        if( $usmleArr[2] !== NULL ) {
                            $usmleScoreSumTotal = $usmleScoreSumTotal + $usmleArr[2];
                            $usmleScoreCounter++;
                        }
                        if( $usmleArr[3] !== NULL ) {
                            $usmleScoreSumTotal = $usmleScoreSumTotal + $usmleArr[3];
                            $usmleScoreCounter++;
                        }
                    }

                    if( $usmleScoreCounter > 0 ) {
                        $usmleScoreAvg = round($usmleScoreSumTotal / $usmleScoreCounter);
                        $usmleValueArr[] = $usmleScoreAvg;
                        $usmleYearArr[] = $startDateLabel;
                    }

                    $comlexArr = $fellapp->getAllComlexArr();
                    if( count($comlexArr) > 0 ) {
                        if( $comlexArr[1] !== NULL ) {
                            $comlexScoreSumTotal = $comlexScoreSumTotal + $comlexArr[1];
                            $comlexScoreCounter++;
                        }
                        if( $comlexArr[2] !== NULL ) {
                            $comlexScoreSumTotal = $comlexScoreSumTotal + $comlexArr[2];
                            $comlexScoreCounter++;
                        }
                        if( $comlexArr[3] !== NULL ) {
                            $comlexScoreSumTotal = $comlexScoreSumTotal + $comlexArr[3];
                            $comlexScoreCounter++;
                        }
                    }

                    if( $comlexScoreCounter > 0 ) {
                        //echo "$usmleScoreSumTotal / $usmleScoreCounter <br>";
                        $comlexScoreAvg = round($comlexScoreSumTotal / $comlexScoreCounter);
                        $comlexValueArr[] = $comlexScoreAvg;
                        $comlexYearArr[] = $startDateLabel;
                    }

                }//foreach fellapp

                $fellappsCount = count($fellapps);
                $totalCount = $totalCount + $fellappsCount;

            } while( $startDateInt < $endYearInt );

            $chartName = $chartName . " (" . $totalCount . " applications in total)";
            //exit('$chartName='.$chartName);

            $combinedData = array();
            $combinedData['USMLE']['labels'] = $usmleYearArr;
            $combinedData['USMLE']['values'] = $usmleValueArr;

            $combinedData['COMLEX']['labels'] = $comlexYearArr;
            $combinedData['COMLEX']['values'] = $comlexValueArr;

            //violin
            //$combinedDataArr, $title, $type="violin", $layoutArray=null, $hoverinfo=null
            $chartsArray = $this->getViolinChart($combinedData,$chartName,'violin');
        }
        //"61. Average sum of the USMLE scores for fellowship applicant by year" => "fellapp-average-usmle-scores-by-year",
        if( $chartType == "fellapp-average-usmle-scores-by-year" ) {
            $fellappUtil = $this->container->get('fellapp_util');

            //echo "startDate=".$startDate->format('d-m-Y').", endDate=".$endDate->format('d-m-Y')."<br>";

            //Show the last 10 years (in 2022): 2017, 2018, 2019, 2020, 2021, 2022, 2023, 2024
            //TODO: use shifted year: current year + 2 years
            //TODO: academic year or calendar year range?
            //$startYear = $fellappUtil->getDefaultAcademicStartYear();
            $startDate->modify('-6 year');
            $endDate->modify('+2 year');
            $startYear = $startDate->format('Y');
            $endYear = $endDate->format('Y');
            $endYearInt = intval($endYear);

            //$startEndDates = $fellappUtil->getAcademicYearStartEndDates($startYear,true,+2);
            $startEndDates = $fellappUtil->getAcademicYearStartEndDates($startYear,true);
            $academicStartDate = $startEndDates['startDate'];
            $academicEndDate = $startEndDates['endDate'];
            //echo "academicStartDate=".$academicStartDate->format('d-m-Y').", academicEndDate=".$academicEndDate->format('d-m-Y')."<br>";
            //exit('111');

            $usmleYearArr = array();
            $comlexYearArr = array();

            $usmle1YearArr = array();
            $usmle2YearArr = array();
            $usmle3YearArr = array();
            $comlex1YearArr = array();
            $comlex2YearArr = array();
            $comlex3YearArr = array();


            $totalScoreArr = array();
            $totalUsmleArr = array();
            $totalComlexArr = array();

            $usmle1ValueArr = array();
            $usmle2ValueArr = array();
            $usmle3ValueArr = array();

            $comlexValueArr = array();

            $totalCount = 0;

            do {
                $startDateLabel = $academicStartDate->format('Y');
                $startDateInt = intval($startDateLabel);
                //echo "startDateLabel=".$startDateLabel."<br>";
                //echo "StartDate=".$academicStartDate->format("d-M-Y")."; endYearInt=".$endYearInt.": <br>";

                $fellapps = $fellappUtil->getFellAppByStatusAndYear(null,null,$startDateLabel);
                //echo "startDateLabel=".$startDateLabel.", fellapps=".count($fellapps)."<br>";

                $academicStartDate->modify('+ 1 year');

                //$usmleSingleYearArr = array();
                //$comlexSingleYearArr = array();

                //TODO: make sure the value corresponds to year
                foreach($fellapps as $fellapp) {

                    //Usmle
                    $usmle1ScoreAvg = 0;
                    $usmle1ScoreCounter = 0;
                    $usmle1ScoreSumTotal = 0;

                    $usmle2ScoreAvg = 0;
                    $usmle2ScoreCounter = 0;
                    $usmle2ScoreSumTotal = 0;

                    $usmle3ScoreAvg = 0;
                    $usmle3ScoreCounter = 0;
                    $usmle3ScoreSumTotal = 0;

                    //Comlex
                    $comlex1ScoreAvg = 0;
                    $comlex1ScoreCounter = 0;
                    $comlex1ScoreSumTotal = 0;

                    $comlex2ScoreAvg = 0;
                    $comlex2ScoreCounter = 0;
                    $comlex2ScoreSumTotal = 0;

                    $comlex3ScoreAvg = 0;
                    $comlex3ScoreCounter = 0;
                    $comlex3ScoreSumTotal = 0;

                    $usmleArr = $fellapp->getAllUsmleArr();
                    if( count($usmleArr) > 0 ) {
                        //USMLE Step 1 can be numerical, pass or fail
                        $usmleStep1 = $usmleArr[1]; //Range: from 1 to 300
                        if( $usmleStep1 !== NULL && is_numeric($usmleStep1) && $usmleStep1 < 500 ) {
//                            if( $usmleStep1 > 1000 ) {
//                                exit('big $usmleStep1='.$usmleStep1.', fellapp ID='.$fellapp->getId());
//                            }
                            $usmle1ScoreSumTotal = $usmle1ScoreSumTotal + $usmleStep1;
                            $usmle1ScoreCounter++;
                        }
                        if( $usmleArr[2] !== NULL ) {
                            $usmle2ScoreSumTotal = $usmle2ScoreSumTotal + $usmleArr[2];
                            $usmle2ScoreCounter++;
                        }
                        if( $usmleArr[3] !== NULL ) {
                            $usmle3ScoreSumTotal = $usmle3ScoreSumTotal + $usmleArr[3];
                            $usmle3ScoreCounter++;
                        }
                    }

                    if( $usmle1ScoreCounter > 0 ) {
                        $usmle1ScoreAvg = round($usmle1ScoreSumTotal / $usmle1ScoreCounter);
                        $usmle1ValueArr[] = $usmle1ScoreAvg;
                        $usmle1YearArr[] = $startDateLabel;
                    }
                    if( $usmle2ScoreCounter > 0 ) {
                        $usmle2ScoreAvg = round($usmle2ScoreSumTotal / $usmle2ScoreCounter);
                        $usmle2ValueArr[] = $usmle2ScoreAvg;
                        $usmle2YearArr[] = $startDateLabel;
                    }
                    if( $usmle3ScoreCounter > 0 ) {
                        $usmle3ScoreAvg = round($usmle3ScoreSumTotal / $usmle3ScoreCounter);
                        $usmle3ValueArr[] = $usmle3ScoreAvg;
                        $usmle3YearArr[] = $startDateLabel;
                    }

                    $comlexArr = $fellapp->getAllComlexArr();
                    if( count($comlexArr) > 0 ) {
                        //Comlex Level 1 can be numerical, pass or fail
                        $comlexStep1 = $comlexArr[1]; //Range: from 9 to 999
                        if( $comlexStep1 !== NULL && is_numeric($comlexStep1) && $comlexStep1 < 1000 ) {
                            $comlex1ScoreSumTotal = $comlex1ScoreSumTotal + $comlexStep1;
                            $comlex1ScoreCounter++;
                        }
                        if( $comlexArr[2] !== NULL ) {
                            //echo "Dashboard comlex2=".$comlexArr[2]."<br>"; //comlex2=461/79
                            $comlex2ScoreSumTotal = $comlex2ScoreSumTotal + $comlexArr[2];
                            $comlex2ScoreCounter++;
                        }
                        if( $comlexArr[3] !== NULL ) {
                            $comlex3ScoreSumTotal = $comlex3ScoreSumTotal + $comlexArr[3];
                            $comlex3ScoreCounter++;
                        }
                    }

                    if( $comlex1ScoreCounter > 0 ) {
                        $comlex1ScoreAvg = round($comlex1ScoreSumTotal / $comlex1ScoreCounter);
                        $comlex1ValueArr[] = $comlex1ScoreAvg;
                        $comlex1YearArr[] = $startDateLabel;
                    }
                    if( $comlex2ScoreCounter > 0 ) {
                        $comlex2ScoreAvg = round($comlex2ScoreSumTotal / $comlex2ScoreCounter);
                        $comlex2ValueArr[] = $comlex2ScoreAvg;
                        $comlex2YearArr[] = $startDateLabel;
                    }
                    if( $comlex3ScoreCounter > 0 ) {
                        $comlex3ScoreAvg = round($comlex3ScoreSumTotal / $comlex3ScoreCounter);
                        $comlex3ValueArr[] = $comlex3ScoreAvg;
                        $comlex3YearArr[] = $startDateLabel;
                    }

                }//foreach fellapp

                $fellappsCount = count($fellapps);
                $totalCount = $totalCount + $fellappsCount;

            } while( $startDateInt < $endYearInt );

            $chartName = $chartName . " (" . $totalCount . " applications in total)";

            //dump($usmleYearArr);
            //dump($usmle1ValueArr);
            //exit('$chartName='.$chartName);

            $combinedData = array();

            ////////// USMLE //////////
            $combinedData['USMLE1']['labels'] = $usmle1YearArr;
            $combinedData['USMLE1']['values'] = $usmle1ValueArr;

            $combinedData['USMLE2']['labels'] = $usmle2YearArr;
            $combinedData['USMLE2']['values'] = $usmle2ValueArr;

            $combinedData['USMLE3']['labels'] = $usmle3YearArr;
            $combinedData['USMLE3']['values'] = $usmle3ValueArr;

            ////////// COMLEX //////////
            $combinedData['COMLEX1']['labels'] = $comlex1YearArr;
            $combinedData['COMLEX1']['values'] = $comlex1ValueArr;

            $combinedData['COMLEX2']['labels'] = $comlex2YearArr;
            $combinedData['COMLEX2']['values'] = $comlex2ValueArr;

            $combinedData['COMLEX3']['labels'] = $comlex3YearArr;
            $combinedData['COMLEX3']['values'] = $comlex3ValueArr;

            //dump($combinedData);
            //exit('$chartName='.$chartName);

            //violin
            //$combinedDataArr, $title, $type="violin", $layoutArray=null, $hoverinfo=null
            $chartsArray = $this->getViolinChart($combinedData,$chartName,'violin');
        }

        //"62. New and Edited Call Log Entries Per Day" => "new-and-edited-calllog-entries-per-day",
        if( $chartType == "new-and-edited-calllog-entries-per-day" ) {
            //Dates on the X axis
            //Quantity (1,2,3,4) on the Y axis
            //Legend:
            //New Call Log Entries
            //Edited Call Log Entries
            //$transresUtil = $this->container->get('transres_util');
            $calllogUtil = $this->container->get('calllog_util');

            $newTypeArr = array("New Call Log Book Entry Submitted");
            $editedTypeArr = array("Call Log Book Entry Edited","Call Log Book Entry Amended");

            $newArr = array();
            $editedArr = array();

            $newCount = 0;
            $editedCount = 0;
            $counter = 0;

            //$startDate->modify( 'first day of last month' );
            $startDate->modify( 'first day of this month' );

            //$totalCalllogEntriesCount = $transresUtil->getTotalUniqueCalllogEntriesCount($startDate,$endDate,$editedTypeArr);
            $totalCalllogEntriesCount = $calllogUtil->getTotalUniqueCalllogEntriesCount($startDate,$endDate,true);

            $weekly = true;  //weekly
            //$weekly = false; //daily

            if( $weekly ) {
                $interval = new \DateInterval('P1W'); //P1D P1W
            } else {
                $interval = new \DateInterval('P1D'); //P1D P1W
            }

            $dateRange = new \DatePeriod($startDate, $interval, $endDate);

            foreach( $dateRange as $startDate ) {
                //+6 days
                $thisEndDate = clone $startDate;

                if( $weekly ) {
                    $thisEndDate->add(new \DateInterval('P6D'));
                } else {
                    $thisEndDate->add(new \DateInterval('P1D'));
                }

                $startDateLabel = $startDate->format('d-M-Y');

                $datesArr[$startDateLabel] = array('startDate'=>$startDate->format('m/d/Y'),'endDate'=>$thisEndDate->format('m/d/Y'));
                //echo "StartDate=".$startDate->format("d-M-Y H:i:s")."; EndDate=".$thisEndDate->format("d-M-Y H:i:s")."<br>";

                $newCalllogEntriesCount = $calllogUtil->getCalllogEntriesCount($startDate,$thisEndDate,$newTypeArr);
                $newArr[$startDateLabel] = $newCalllogEntriesCount;
                $newCount = $newCount + $newCalllogEntriesCount;

                $editedCalllogEntriesCount = $calllogUtil->getCalllogEntriesCount($startDate,$thisEndDate,$editedTypeArr);
                //$editedCalllogEntriesCount = $transresUtil->getTotalUniqueCalllogEntriesCount($startDate,$thisEndDate);
                $editedArr[$startDateLabel] = $editedCalllogEntriesCount;
                $editedCount = $editedCount + $editedCalllogEntriesCount;

                $counter++;
            }

            $calllogTotalCount = $newCount + $editedCount;

            //($loginCountTrp on average per week; 2 total)
            $combinedData["New Call Log Entries ($newCount total)"] = $newArr;

            //$combinedData["Edited Call Log Entries ($editedCount total)"] = $editedArr;
            //convert that "edited" legend only from saying "Edited Call Log Entries (2503 total)" to
            // "X entries edited Y times in total" $totalCalllogEntriesCount
            $combinedData["$totalCalllogEntriesCount entries edited $editedCount times in total"] = $editedArr;

            $chartName = $chartName . " (" . $calllogTotalCount . " Total)";

            $chartsArray = $this->getStackedChart($combinedData, $chartName, "stack");
        }

        //"63. Patients with Call Log Entries Per Day" => "patients-calllog-per-day",
        if( $chartType == "patients-calllog-per-day" ) {
            //Title: Patients with Call Log Entries Per Day
            //Dates on the X axis
            //Quantity (1,2,3,4) on the Y axis
            //Legend: Patients with new call log entries
            //The second graph would show the number of patients who got a new Call Log entry per day.

            //$transresUtil = $this->container->get('transres_util');
            $calllogUtil = $this->container->get('calllog_util');

            $newTypeArr = array("New Call Log Book Entry Submitted");
            $editedTypeArr = array("Call Log Book Entry Edited","Call Log Book Entry Amended");

            $patientsArr = array();

            $patientCount = 0;
            $counter = 0;

            //$startDate->modify( 'first day of last month' );
            $startDate->modify( 'first day of this month' );

            $weekly = true;  //weekly
            //$weekly = false; //daily

            if( $weekly ) {
                $interval = new \DateInterval('P1W'); //P1D P1W
            } else {
                $interval = new \DateInterval('P1D'); //P1D P1W
            }

            $dateRange = new \DatePeriod($startDate, $interval, $endDate);

            foreach( $dateRange as $startDate ) {
                //+6 days
                $thisEndDate = clone $startDate;
                //$thisEndDate->add(new \DateInterval('P6D'));
                if( $weekly ) {
                    $thisEndDate->add(new \DateInterval('P6D'));
                } else {
                    $thisEndDate->add(new \DateInterval('P1D'));
                }

                $startDateLabel = $startDate->format('d-M-Y');

                $datesArr[$startDateLabel] = array('startDate'=>$startDate->format('m/d/Y'),'endDate'=>$thisEndDate->format('m/d/Y'));
                //echo "StartDate=".$startDate->format("d-M-Y")."; EndDate=".$thisEndDate->format("d-M-Y")."<br>";

                $calllogPatientEntriesCount = $calllogUtil->getCalllogPatientEntriesCount($startDate,$thisEndDate,$newTypeArr);
                $patientsArr[$startDateLabel] = $calllogPatientEntriesCount;
                $patientCount = $patientCount + $calllogPatientEntriesCount;

                $counter++;
            }

            //($loginCountTrp on average per week; 2 total)
            $combinedData["Number of Patients who got a new Call Log entry"] = $patientsArr;
            //$combinedData["Number of Patients who got a new Call Log entry2"] = $patientsArr;

            $chartName = $chartName . " (" . $patientCount . " Total)";

            $chartsArray = $this->getStackedChart($combinedData, $chartName, "stack");
        }

        //"64. Total amount of paid/due for issued invoices per month" => "total-amount-paid-unpaid-invoices-per-month"
        //similar to 22. Paid Invoices by Month
        //Similar to 46. stack
        if( $chartType == "total-amount-paid-unpaid-invoices-per-month" ) {
            $paidArr = array();
            $dueArr = array();
            //$descriptionArr = array();

            $perYear = false;
            $perMonth = false;

            //$perYear = true;
            $perMonth = true;

            $totalInvoicesCount = 0;
            $totalPaidInvoiceFee = 0;
            $totalDueInvoiceFee = 0;
            $totalInvoiceFee = 0;

            //$invoiceStates = array("Paid in Full","Paid Partially","Unpaid/Issued");
            $invoiceStates = array(
                "Paid in Full",
                "Paid Partially",
                "Unpaid/Issued",
                "Pending",
                "Refunded Fully",
                "Refunded Partially"
            );
            //$compareType = "date when status changed to paid in full";
            $compareType = "last invoice generation date";

            //$startDate->modify( 'first day of last month' );
            if( $perYear ) {
                $startDate->modify('first day of january this year');
            }
            if( $perMonth ) {
                $startDate->modify('first day of this month');
            }

            //fiscal year => take all available years as $startDate

            do {
                $startDateLabel = $startDate->format('M-Y');
                //echo "startDateLabel=".$startDateLabel."<br>";
                $thisEndDate = clone $startDate;
                //$thisEndDate->modify( 'first day of next month' );
                if( $perYear ) {
                    $thisEndDate->modify('last day of december this year');
                }
                if( $perMonth ) {
                    $thisEndDate->modify('last day of this month');
                }
                //echo "StartDate=".$startDate->format("d-M-Y")."; thisEndDate=".$thisEndDate->format("d-M-Y").": <br>";

                $invoices = $this->getInvoicesByFilter($startDate,$thisEndDate,$projectSpecialtyObjects,$invoiceStates,false,true,$compareType);
                //get invoices by paidDate

                if( $perYear ) {
                    $startDate->modify('first day of january next year');
                }
                if( $perMonth ) {
                    $startDate->modify('first day of next month');
                }

                $thisDateTotalPaid = 0.00;
                $thisDateTotalDue = 0.00;

                foreach( $invoices as $invoice ) {

                    $paid = intval($invoice->getPaid());
                    $due = intval($invoice->getDue());
                    $total = intval($invoice->getTotal());

                    $thisDateTotalPaid = $thisDateTotalPaid + $paid;
                    $thisDateTotalDue = $thisDateTotalDue + $due;

                    $totalPaidInvoiceFee = $totalPaidInvoiceFee + $paid;
                    $totalDueInvoiceFee = $totalDueInvoiceFee + $due;
                    $totalInvoiceFee = $totalInvoiceFee + $total;
                }

                $totalInvoicesCount = $totalInvoicesCount + count($invoices);
                //$descriptionArr[$startDateLabel] = " (" . count($invoices) . " invoices)";

                $paidArr[$startDateLabel] = $this->getNumberFormat($thisDateTotalPaid);
                $dueArr[$startDateLabel] = $this->getNumberFormat($thisDateTotalDue);

            } while( $startDate < $endDate );

            //echo "totalPaidInvoiceFee=".$totalPaidInvoiceFee."; totalDueInvoiceFee=".$totalDueInvoiceFee."; totalInvoiceFee=".$totalInvoiceFee."<br>"; //7591754 7.591.754
            //exit('111');

            $chartName = $this->getTitleWithTotal($chartName,$this->getNumberFormat($totalInvoiceFee),"$","Total");

            //increase vertical
            $layoutArray = array(
                'height' => $this->height*1.3,
                'width' => $this->width,
                'title' => $chartName,
                'margin' => array('b' => 300),
                'yaxis' => array(
                    'tickformat' => "$"."n" //"digit"
                ),
            );

            $totalPaidInvoiceFee = $this->getNumberFormat($totalPaidInvoiceFee);
            $totalDueInvoiceFee = $this->getNumberFormat($totalDueInvoiceFee);

            $combinedData["Paid $".$totalPaidInvoiceFee] = $paidArr;
            $combinedData["Due $".$totalDueInvoiceFee] = $dueArr;

            $chartName = $chartName . ", " . $totalInvoicesCount . " total invoices" .
                " (" . "Paid $". $totalPaidInvoiceFee . ", Due $".$totalDueInvoiceFee.")";

            $chartsArray = $this->getStackedChart($combinedData, $chartName, "stack",$layoutArray,"x+y");

            //dump($chartsArray);
            //exit('111');
        }

        //"65. Total amount of paid/due for issued invoices per fiscal year" => "total-amount-paid-unpaid-invoices-per-year" per fiscal year
        if( $chartType == "total-amount-paid-unpaid-invoices-per-year" ) {
            $paidArr = array();
            $dueArr = array();
            //$descriptionArr = array();

            $perYear = false;
            $perMonth = false;

            $perYear = true;
            //$perMonth = true;

            $totalInvoicesCount = 0;
            $totalPaidInvoiceFee = 0;
            $totalDueInvoiceFee = 0;
            $totalInvoiceFee = 0;

            $invoiceStates = array(
                "Paid in Full",
                "Paid Partially",
                "Unpaid/Issued",
                "Pending",
                "Refunded Fully",
                "Refunded Partially"
            );
            //$compareType = "date when status changed to paid in full";
            $compareType = "last invoice generation date";

            //ignore filter start date and set it to previous 10 years
            //$now = new \DateTime('now');
            //$startDate = $now->modify('-10 years');

            //$startDate->modify( 'first day of last month' );
            if( $perYear ) {
                $startDate->modify('first day of january this year');
            }
            if( $perMonth ) {
                $startDate->modify('first day of this month');
            }

            //fiscal year => take all available years as $startDate

            do {
                //$startDateLabel = $startDate->format('M-Y');
                $startDateLabel = $startDate->format('Y');
                //echo "startDateLabel=".$startDateLabel."<br>";
                $thisEndDate = clone $startDate;
                //$thisEndDate->modify( 'first day of next month' );
                if( $perYear ) {
                    $thisEndDate->modify('last day of december this year');
                }
                if( $perMonth ) {
                    $thisEndDate->modify('last day of this month');
                }
                //echo "StartDate=".$startDate->format("d-M-Y")."; thisEndDate=".$thisEndDate->format("d-M-Y").": <br>";

                $invoices = $this->getInvoicesByFilter($startDate,$thisEndDate,$projectSpecialtyObjects,$invoiceStates,false,true,$compareType);
                //get invoices by paidDate

                if( $perYear ) {
                    $startDate->modify('first day of january next year');
                }
                if( $perMonth ) {
                    $startDate->modify('first day of next month');
                }

                $thisDateTotalPaid = 0.00;
                $thisDateTotalDue = 0.00;

                //Possible to optimise and replace loop by sql sum
                foreach( $invoices as $invoice ) {
                    $paid = floatval($invoice->getPaid());
                    $due = floatval($invoice->getDue());
                    $total = floatval($invoice->getTotal());

                    $thisDateTotalPaid = $thisDateTotalPaid + $paid;
                    $thisDateTotalDue = $thisDateTotalDue + $due;

                    $totalPaidInvoiceFee = $totalPaidInvoiceFee + $paid;
                    $totalDueInvoiceFee = $totalDueInvoiceFee + $due;
                    $totalInvoiceFee = $totalInvoiceFee + $total;
                } //foreach invoice in date range

                //if( $thisDateTotalPaid || $thisDateTotalDue ) {
                    $paidArr[$startDateLabel] = $this->getNumberFormat($thisDateTotalPaid);
                    $dueArr[$startDateLabel] = $this->getNumberFormat($thisDateTotalDue);
                    $totalInvoicesCount = $totalInvoicesCount + count($invoices);
                //}

                //$descriptionArr[$startDateLabel] = " (" . count($invoices) . " invoices)";

            } while( $startDate < $endDate );

            //echo "totalPaidInvoiceFee=".$totalPaidInvoiceFee."; totalDueInvoiceFee=".$totalDueInvoiceFee."; totalInvoiceFee=".$totalInvoiceFee."<br>"; //7591754 7.591.754
            //exit('111');

            $chartName = $this->getTitleWithTotal($chartName,$this->getNumberFormat($totalInvoiceFee),"$","Total");

            //increase vertical
            //tickformat: https://github.com/d3/d3-format/blob/main/README.md#locale_format
            $layoutArray = array(
                'height' => $this->height*1.3,
                'width' => $this->width,
                'title' => $chartName,
                //'margin' => array('b' => 300),
                'yaxis' => array(
                    'tickformat' => "$"."n", //"digit"
                    //'showticklabels' => false,
                    //'tickvals' => null
                ),
                'xaxis' => array(
                    'tickformat' =>  "d",
                ),
                //'showlegend' => false
            );

            $totalPaidInvoiceFee = $this->getNumberFormat($totalPaidInvoiceFee);
            $totalDueInvoiceFee = $this->getNumberFormat($totalDueInvoiceFee);

            $combinedData = array();
            $combinedData["Paid $".$totalPaidInvoiceFee] = $paidArr;
            $combinedData["Due $".$totalDueInvoiceFee] = $dueArr;

            $chartName = $chartName . ", " . $totalInvoicesCount . " total invoices" .
                " (" . "Paid $". $totalPaidInvoiceFee . ", Due $".$totalDueInvoiceFee.")";

            $chartsArray = $this->getStackedChart($combinedData, $chartName, "stack", $layoutArray, "x+y"); //public function getStackedChart

            //dump($chartsArray);
            //exit('111');
        }

        //"66. Chart viewing stats per month" => "chart-view-stat",
        if( $chartType == "chart-view-stat" ) {
            //Stacked Bar chart:
            //Shows how many times each dashboard chart was accessed (on the Y axis) per month (on the X axis).
            //Since there are so many charts, the legend might be good to show under the chart.

            //$loginsResappArr = array();
            //$totalLoginCount = 0;
            //$loginCountResapp = 0;

            $totalViewCount = 0;
            //$charts = $this->getChartTypes(true);

            $startDate->modify( 'first day of this month' );
            $endDate->modify('last day of this month');
            //echo "StartDate=".$startDate->format("d-M-Y")."; EndDate=".$endDate->format("d-M-Y")."<br>";
            $charts = $this->getViewedCharts($startDate,$endDate);

//            //testing
//            if(0) {
//                $charts = array();
//                $exceptions = array(
//                    1, 2, 3, 4, 5, 6, 7, 8, 9, 10,
//                    11, 12, 13, 14, 15, 16, 17, 18, 19, 20,
//                );
//                for ($ii = 40; $ii <= 66; $ii++) {
//                    if (in_array($ii, $exceptions)) {
//                        continue;
//                    }
//                    $charts[] = $this->getChartByPartialName("$ii.");
//                }
//            }//testing

            $viewByChartArr = array();
            $chartViewCountArr = array();
            foreach($charts as $chart) {
                $chartViewCountArr[$chart->getId()] = 0;
            }

            //$startDate->modify( 'first day of last month' );
            //$startDate->modify( 'first day of this month' );
            do {
                $startDateLabel = $startDate->format('M-Y');
                $thisEndDate = clone $startDate;
                //$thisEndDate->modify( 'first day of next month' );
                $thisEndDate->modify('last day of this month');
                //$datesArr[$startDateLabel] = array('startDate'=>$startDate->format('m/d/Y'),'endDate'=>$thisEndDate->format('m/d/Y'));
                //echo "StartDate=".$startDate->format("d-M-Y")."; EndDate=".$thisEndDate->format("d-M-Y")."<br>";

//                $loginResappCount = $transresUtil->getLoginCount($startDate,$thisEndDate,'resapp');
//                $loginsResappArr[$startDateLabel] = $loginResappCount;
//                $totalLoginCount += $loginResappCount;
//                $loginCountResapp = $loginCountResapp + $loginResappCount;

                foreach($charts as $chart) {
                    //$chartId = $chart->getId();
                    //echo "chartId=$chartId <br>";
                    //$viewByChartArr[$chart->getName()] = array();
                    $thisViewCount = $this->getChartViewCount($startDate,$thisEndDate,$chart);
//                    if( $chart->getName() == "66. Chart viewing stats per month" ) {
//                        echo $chart . ": " . $startDate->format('d-M-Y') . "; " . $thisEndDate->format('d-M-Y') . "=>" . $thisViewCount . "<br>";
//                    }
                    $totalViewCount = $totalViewCount + $thisViewCount;
                    $chartViewCountArr[$chart->getId()] += $thisViewCount;
                    //$chartViewCountArr[$chart->getId()] = $chartViewCountArr[$chart->getId()] + $thisViewCount;
                    //$viewByChartArr[$startDateLabel] = $thisViewCount; //array($chart->getName(),$thisViewCount);
                    $viewByChartArr[$chart->getId()][$startDateLabel] = $thisViewCount;
                }//foreach chart

                $startDate->modify( 'first day of next month' );
            } while( $startDate < $endDate );

            //exit('111');

            //$combinedData["Residency Applications log in events ($loginCountResapp)"] = $loginsResappArr;
            $combinedData = array();
            foreach($charts as $chart) {
                $thisChartViewCounter = $chartViewCountArr[$chart->getId()];
                $combinedData[$chart->getName()." (".$thisChartViewCounter." total views)"] = $viewByChartArr[$chart->getId()];
            }

            $chartName = $chartName . " (" . count($charts) . " charts viewed, " . $totalViewCount . " total views)";

            $layoutArray = array(
                'height' => $this->height,
                'width' => $this->width,
                //'showlegend' => true,
                'margin' => array(
                    'b' => 200,
                    //'l' => 500,
                    //'r' => 100,
                    //'t' => 500,
                    //'pad' => 1
                ),
                'legend' => array(
                    'orientation'=>"h"
                ),
                'yaxis' => array(
                    'tickformat' => "d", //"digit", //"digit"
                    //'showticklabels' => true,
                    //'tickvals' => null,
                    'automargin' => true,
                    //'titlefont' => array('size'=>30)
                ),
                'xaxis' => array(
                    'tickformat' =>  "d",
                    'automargin' => true,
                )
            );

            $chartsArray = $this->getStackedChart($combinedData, $chartName, "stack", $layoutArray);
        }

        //"67. Scheduled residency and fellowship interviews by interviewer" => "fellapp-resapp-interviews",
        //"69. Scheduled residency interviews by interviewer" => "resapp-interviews",
        //"70. Scheduled fellowship interviews by interviewer" => "fellapp-interviews",
        //"68. Total candidate interview feedback comments provided via the system, by interviewer" => "fellapp-resapp-interviews-feedback",
        //"71. Residency interview feedback comments provided via the system, by interviewer" => "resapp-interviews-feedback",
        //"72. Fellowship interview feedback comments provided via the system, by interviewer" => "fellapp-interviews-feedback",
        if(
            $chartType == "fellapp-resapp-interviews" ||            //fell+resapp interview
            $chartType == "fellapp-interviews" ||                   //fell interview
            $chartType == "resapp-interviews" ||                    //resapp interview
            $chartType == "fellapp-resapp-interviews-feedback" ||  //fell+resapp feedback
            $chartType == "fellapp-interviews-feedback" ||         //fell feedback
            $chartType == "resapp-interviews-feedback"             //resapp feedback
        ) {
            //Scheduled residency and fellowship interviews by interviewer ([total scheduled interviews])
            //Y axis: Number of assigned interviews
            //X axis: Interviewer name

            //Residency and fellowship Interviews for which feedback was provided, by interviewer ([total feedback comments])
            //Y axis: Number of interviewees for whom feedback was submitted
            //X axis: Interviewer name

            //The default time range would be the "current year", but would be adjustable via
            // "From Date" and "To Date" fields (so you could get the counts for any 1 or more years).

            $resappUtil = $this->container->get('resapp_util');
            $fellappUtil = $this->container->get('fellapp_util');

            $totalInterviewsArr = array();

            $grandTotalFellappInterviewsCount = 0;
            $interviewedFellappCount = 0;
            $totalFellappInterviewsCount = 0;

            $grandTotalResappInterviewsCount = 0;
            $interviewedResappCount = 0;
            $totalResappInterviewsCount = 0;

            //$yearShift = 2; //+2
            //$startDate->modify('+'.$yearShift.' year');
            //$endDate->modify('+'.$yearShift.' year');

            //$year can be multiple dates "2019,2020,2021..."
            $startYear = $startDate->format('Y');
            $endYear = $endDate->format('Y');
            //echo "1startYear=".$startYear.", endYear=".$endYear."<br>";

            if( (int)$startYear > (int)$endYear ) {
                //echo "flip<br>";
                $tempYear = $startYear;
                $startYear = $endYear;
                $endYear = $tempYear;
            }
            //echo "2startYear=".$startYear.", endYear=".$endYear."<br>";

            $yearRange = '';

            foreach(range($startYear, $endYear) as $thisYear) {
                $yearRangeArr[] = $thisYear;
            }

            if( count($yearRangeArr) > 0 ) {
                $yearRange = implode(",",$yearRangeArr);
            } else {
                $yearRange = $startYear;
            }

            /////////////// fellowship applications //////////////////
            $fellapps = array();
            if(
                $chartType == "fellapp-resapp-interviews" ||            //fell+resapp interview
                $chartType == "fellapp-interviews" ||                   //fell interview
                $chartType == "fellapp-resapp-interviews-feedback" ||  //fell+resapp feedback
                $chartType == "fellapp-interviews-feedback"            //fell feedback
            ) {
                                    //$status,$fellSubspecArg,$year=null,$interviewer=null
                $fellapps = $fellappUtil->getFellAppByStatusAndYear(null,null,$yearRange);
                //echo "yearRange=$yearRange, fellapps=".count($fellapps)."<br>";
                //exit('111');

                foreach($fellapps as $fellapp) {

                    if( $fellappUtil->isFellAppInterviewed($fellapp) ) {
                        $interviewedFellappCount++;
                    }

                    foreach($fellapp->getInterviews() as $interview) {

                        $grandTotalFellappInterviewsCount++;

                        //if( $fellappUtil->isFellAppInterviewed($fellapp) ) {
                        //    $interviewedFellappCount++;
                        //}

                        if(
                            (
                                $chartType == "fellapp-resapp-interviews-feedback" ||
                                $chartType == "fellapp-interviews-feedback"
                            ) &&
                            $interview->isEmpty() === true
                        )
                        {
                            continue; //skip for feedback if feedback is empty
                        }

                        $interviewer = $interview->getInterviewer();
                        if( $interviewer ) {
                            $interviewerIndex = $interviewer->getDisplayName(); //getUsernameOptimal();
                            if( array_key_exists($interviewerIndex, $totalInterviewsArr) ) {
                                $currentCount = $totalInterviewsArr[$interviewerIndex];
                            } else {
                                $currentCount = 0;
                            }
                            $currentCount++;
                            $totalInterviewsArr[$interviewerIndex] = $currentCount;
                            $totalFellappInterviewsCount++;
                        }
                    }//foreach $interview
                }//foreach $fellapp
            }//if fellapp
            /////////////// EOF fellowship applications //////////////////

            /////////////// residency applications //////////////////
            $resapps = array();
            if(
                $chartType == "fellapp-resapp-interviews" ||            //fell+resapp interview
                $chartType == "resapp-interviews" ||                    //resapp interview
                $chartType == "fellapp-resapp-interviews-feedback" ||  //fell+resapp feedback
                $chartType == "resapp-interviews-feedback"             //resapp feedback
            ) {
                $resapps = $resappUtil->getResAppByStatusAndYear(null,null,$yearRange);
                //echo "yearRange=$yearRange, resapps=".count($resapps)."<br>";
                //exit('111');

                foreach($resapps as $resapp) {

                    if( $resappUtil->isResAppInterviewed($resapp) ) {
                        $interviewedResappCount++;
                    }

                    foreach($resapp->getInterviews() as $interview) {

                        $grandTotalResappInterviewsCount++;

                        //if( $resappUtil->isResAppInterviewed($resapp) ) {
                        //    $interviewedResappCount++;
                        //}

                        if(
                            (
                                $chartType == "fellapp-resapp-interviews-feedback" ||
                                $chartType == "resapp-interviews-feedback"
                            ) &&
                            $interview->isEmpty() === true )
                        {
                            continue; //skip for feedback if feedback is empty
                        }

                        $interviewer = $interview->getInterviewer();
                        if( $interviewer ) {
                            $interviewerIndex = $interviewer->getDisplayName(); //getUsernameOptimal();
                            if( array_key_exists($interviewerIndex, $totalInterviewsArr) ) {
                                $currentCount = $totalInterviewsArr[$interviewerIndex];
                            } else {
                                $currentCount = 0;
                            }
                            $currentCount++;
                            $totalInterviewsArr[$interviewerIndex] = $currentCount;
                            $totalResappInterviewsCount++;
                        }
                    }//foreach $interview
                }//foreach $resapp
            }//if resapp
            /////////////// EOF residency applications //////////////////

            $totalInterviewsCount = $totalFellappInterviewsCount + $totalResappInterviewsCount;
            $interviewedAppCount = $interviewedFellappCount + $interviewedResappCount;

            $fellappCount = count($fellapps);
            $resappCount = count($resapps);
            $totalAppCount = $fellappCount + $resappCount;

            $notInterviewedAppCount = $totalAppCount - $interviewedAppCount;

            if( strpos((string)$yearRange, ",") !== false ) {
                $yearRangeStr = "$startYear-$endYear";
            } else {
                $yearRangeStr = $yearRange;
            }

            //In the X axis labels for each chart, please show the quantity and % after the name in this format:
            //User MD, (64; 20.3%)
            //$totalInterviewsArrNew = array();
            foreach($totalInterviewsArr as $interviewerIndex=>$interviewerCount) {
                $interviewerPercent = 100*($interviewerCount/$totalInterviewsCount);
                $interviewerPercent = number_format($interviewerPercent, 2, '.', '');
                $interviewerNewIndex = $interviewerIndex." (".$interviewerCount."; ".$interviewerPercent."%)";
                $totalInterviewsArr[$interviewerNewIndex] = $totalInterviewsArr[$interviewerIndex];
                unset($totalInterviewsArr[$interviewerIndex]);
            }

            //$addTitle = " " . $totalInterviewsCount . " in total for $yearRangeStr";
            //$addTitle = $addTitle . " (" . $totalAppCount . " applications in total)";

            //67. Scheduled residency and fellowship interviews by interviewer
            // – 908 interviews for 250 applications (X not interviewed) in total for 2021-2022
            $addTitle = "$totalInterviewsCount interviews for $totalAppCount".
                " applications ($notInterviewedAppCount not interviewed) in total for $yearRangeStr";

            //$addTitle = $addTitle.", fellapps=".count($fellapps).", resapps=".count($resapps); //.", yearRange=".$yearRange;

            if( $chartType == "fellapp-resapp-interviews" ) {
                //67. Scheduled residency and fellowship interviews by interviewer
                // – 908 interviews for 250 applications (X not interviewed) in total for 2021-2022
                $addTitle = "$totalInterviewsCount interviews for $totalAppCount".
                " applications ($notInterviewedAppCount not interviewed) in total for $yearRangeStr";
            }

            if( $chartType == "fellapp-resapp-interviews-feedback" ) {
                //68. Total candidate interview feedback comments provided via the system, by interviewer –
                // 506 feedback comments for 250 applications (X not interviewed) for 2021-2022
                $addTitle = "$totalInterviewsCount feedback comments for $totalAppCount".
                    " applications ($notInterviewedAppCount not interviewed) in total for $yearRangeStr";
            }

            if( $chartType == "resapp-interviews" ) {
                //69. Scheduled residency interviews by interviewer –
                // 504 interviews for 72 applications (X not interviewed) in total for 2021-2022
                $addTitle = "$totalInterviewsCount interviews for $totalAppCount".
                    " applications ($notInterviewedAppCount not interviewed) in total for $yearRangeStr";
            }

            if( $chartType == "fellapp-interviews" ) {
                //70. Scheduled fellowship interviews by interviewer –
                // 363 interviews for 178 applications (X not interviewed) in total for 2021-2022
                $addTitle = "$totalInterviewsCount interviews for $totalAppCount".
                    " applications ($notInterviewedAppCount not interviewed) in total for $yearRangeStr";
            }

            if( $chartType == "resapp-interviews-feedback" ) {
                //71. Residency interview feedback comments provided via the system, by interviewer –
                // 545 feedback comments for 72 applications (X not interviewed) in total for 2021-2022
                $addTitle = "$totalInterviewsCount feedback comments for $totalAppCount".
                    " applications ($notInterviewedAppCount not interviewed) in total for $yearRangeStr";
            }

            if( $chartType == "fellapp-interviews-feedback" ) {
                //72. Fellowship interview feedback comments provided via the system, by interviewer –
                // 191 feedback comments for 178 applications (X not interviewed) in total for 2021-2022
                $addTitle = "$totalInterviewsCount feedback comments for $totalAppCount".
                    " applications ($notInterviewedAppCount not interviewed) in total for $yearRangeStr";
            }

            $titleDivider = "<br>";

            //$chartName = $this->getTitleWithTotal($chartName,$addTitle,"");
            $chartName = $chartName . $titleDivider . " - " . $addTitle;

            $layoutArray = array(
                'height' => $this->height*1.3,
                'width' => $this->width,
                'title' => $chartName,
                'margin' => array('b'=>200),
                'xaxis' => array(
                    'tickformat' =>  "d",
                ),
                'hovermode' => "y"
            );
            //$layoutArray = NULL;//array();

            array_multisort($totalInterviewsArr, SORT_DESC);

            //dump($totalInterviewsArr);
            //exit('111');

            //bar chart
            $chartsArray = $this->getChart($totalInterviewsArr, $chartName,'bar',$layoutArray);
        }

        //NOT USED
        //"67. Scheduled residency and fellowship interviews by interviewer (stacked)" => "fellapp-resapp-interviews-stacked",
        if( $chartType == "fellapp-resapp-interviews-stacked" ) {
            //Scheduled residency and fellowship interviews by interviewer ([total scheduled interviews])
            //Y axis: Number of assigned interviews
            //X axis: Interviewer name
            //The default time range would be the "current year", but would be adjustable via
            // "From Date" and "To Date" fields (so you could get the counts for any 1 or more years).

            $resappUtil = $this->container->get('resapp_util');
            $fellappUtil = $this->container->get('fellapp_util');

            $totalFellappInterviewsCount = 0;
            $totalFellappInterviewsArr = array();

            $totalResappInterviewsCount = 0;
            $totalResappInterviewsArr = array();

            //$yearShift = 2; //+2
            //$startDate->modify('+'.$yearShift.' year');
            //$endDate->modify('+'.$yearShift.' year');

            //$year can be multiple dates "2019,2020,2021..."
            $startYear = $startDate->format('Y');
            $endYear = $endDate->format('Y');
            //echo "1startYear=".$startYear.", endYear=".$endYear."<br>";

            if( (int)$startYear > (int)$endYear ) {
                //echo "flip<br>";
                $tempYear = $startYear;
                $startYear = $endYear;
                $endYear = $tempYear;
            }
            //echo "2startYear=".$startYear.", endYear=".$endYear."<br>";

            $yearRange = '';

            foreach(range($startYear, $endYear) as $thisYear) {
                $yearRangeArr[] = $thisYear;
            }

            if( count($yearRangeArr) > 0 ) {
                $yearRange = implode(",",$yearRangeArr);
            } else {
                $yearRange = $startYear;
            }

            //$status,$fellSubspecArg,$year=null,$interviewer=null
            $fellapps = $fellappUtil->getFellAppByStatusAndYear(null,null,$yearRange);
            //echo "yearRange=$yearRange, fellapps=".count($fellapps)."<br>";
            //exit('111');

            foreach($fellapps as $fellapp) {
                foreach($fellapp->getInterviews() as $interview) {
                    $interviewer = $interview->getInterviewer();
                    if( $interviewer ) {
                        $interviewerIndex = $interviewer->getUsernameOptimal();
                        if( array_key_exists($interviewerIndex, $totalFellappInterviewsArr) ) {
                            $currentCount = $totalFellappInterviewsArr[$interviewerIndex];
                        } else {
                            $currentCount = 0;
                        }
                        $currentCount++;
                        $totalFellappInterviewsArr[$interviewerIndex] = $currentCount;
                        $totalFellappInterviewsCount++;
                    }
                }//foreach $interview
            }//foreach $fellapp


            $resapps = $resappUtil->getResAppByStatusAndYear(null,null,$yearRange);
            //echo "yearRange=$yearRange, resapps=".count($resapps)."<br>";
            //exit('111');

            foreach($resapps as $resapp) {
                foreach($resapp->getInterviews() as $interview) {
                    $interviewer = $interview->getInterviewer();
                    if( $interviewer ) {
                        $interviewerIndex = $interviewer->getUsernameOptimal();
                        if( array_key_exists($interviewerIndex, $totalResappInterviewsArr) ) {
                            $currentCount = $totalResappInterviewsArr[$interviewerIndex];
                        } else {
                            $currentCount = 0;
                        }
                        $currentCount++;
                        $totalResappInterviewsArr[$interviewerIndex] = $currentCount;
                        $totalResappInterviewsCount++;
                    }
                }//foreach $interview
            }//foreach $resapp

            $totalInterviewsCount = $totalFellappInterviewsCount + $totalResappInterviewsCount;

            $fellappCount = count($fellapps);
            $resappCount = count($resapps);

            $addTitle = " - " . $totalInterviewsCount . " in total for $startYear-$endYear";
            $addTitle = $addTitle . " (" . $fellappCount + $resappCount . " applications in total)";
            //$addTitle = $addTitle.", fellapps=".count($fellapps).", resapps=".count($resapps); //.", yearRange=".$yearRange;

            //$chartName = $this->getTitleWithTotal($chartName,$addTitle,"");
            $chartName = $chartName . " " . $addTitle;

            //$showOther = $this->getOtherStr($showLimited,"Interviewers");
            //$totalInterviewsTopArr = $this->getTopArray($totalInterviewsArr,$showOther,$quantityLimit);
            //$chartsArray = $this->getChart($totalInterviewsTopArr, $chartName,'pie',$layoutArray,"",null,null,"percent+value+label");


            //increase vertical
            //tickformat: https://github.com/d3/d3-format/blob/main/README.md#locale_format
            $layoutArray = array(
                'height' => $this->height,//*1.3,
                'width' => $this->width,
                'title' => $chartName,
                'margin' => array('b'=>200),
                'xaxis' => array(
                    'tickformat' =>  "d",
                ),
                //'showlegend' => false
            );
            //$layoutArray = NULL;//array();

            //$chartName = $chartName . " (" . $totalCount . " applications in total)";

            //stacked char
            //$combinedData = array();
            //$combinedData[] = $fellappArr;
            //$chartsArray = $this->getStackedChart($combinedData, $chartName, "stack", $layoutArray); //public function getStackedChart

            //array_multisort($totalInterviewsArr, SORT_DESC);
            arsort($totalFellappInterviewsArr,SORT_NUMERIC);
            arsort($totalResappInterviewsArr,SORT_NUMERIC);

            //dump($totalFellappInterviewsArr);
            //dump($totalResappInterviewsArr);
            //exit('111');

            if( 1 ) {
                //stacked chart
                $combinedData = array();
                $combinedData['Fellowship - ' . $totalFellappInterviewsCount . ' interviewers'] = $totalFellappInterviewsArr;
                $combinedData['Residency - ' . $totalResappInterviewsCount . ' interviewers'] = $totalResappInterviewsArr;
                $chartsArray = $this->getStackedChart($combinedData, $chartName, "stack", $layoutArray);
            } else {
                //bar chart
                $chartsArray = $this->getChart($totalInterviewsArr, $chartName,'bar',$layoutArray);
            }
        }
        //NOT USED
        //"68. Residency and fellowship Interviews for which feedback was provided, by interviewer (stacked)" => "fellapp-resapp-interviews-feedback-stacked",
        if( $chartType == "fellapp-resapp-interviews-feedback-stacked" ) {
            //Residency and fellowship Interviews for which feedback was provided, by interviewer ([total feedback comments])
            //Y axis: Number of interviewees for whom feedback was submitted
            //X axis: Interviewer name

            $resappUtil = $this->container->get('resapp_util');
            $fellappUtil = $this->container->get('fellapp_util');

            $totalFellappInterviewsCount = 0;
            $totalFellappInterviewsArr = array();

            $totalResappInterviewsCount = 0;
            $totalResappInterviewsArr = array();

            //$yearShift = 2; //+2
            //$startDate->modify('+'.$yearShift.' year');
            //$endDate->modify('+'.$yearShift.' year');

            //$year can be multiple dates "2019,2020,2021..."
            $startYear = $startDate->format('Y');
            $endYear = $endDate->format('Y');
            //echo "1startYear=".$startYear.", endYear=".$endYear."<br>";

            if( (int)$startYear > (int)$endYear ) {
                //echo "flip<br>";
                $tempYear = $startYear;
                $startYear = $endYear;
                $endYear = $tempYear;
            }
            //echo "2startYear=".$startYear.", endYear=".$endYear."<br>";

            $yearRange = '';

            foreach(range($startYear, $endYear) as $thisYear) {
                $yearRangeArr[] = $thisYear;
            }

            if( count($yearRangeArr) > 0 ) {
                $yearRange = implode(",",$yearRangeArr);
            } else {
                $yearRange = $startYear;
            }

            //$status,$fellSubspecArg,$year=null,$interviewer=null
            $fellapps = $fellappUtil->getFellAppByStatusAndYear(null,null,$yearRange);
            //echo "yearRange=$yearRange, fellapps=".count($fellapps)."<br>";
            //exit('111');

            foreach($fellapps as $fellapp) {
                foreach($fellapp->getInterviews() as $interview) {
                    $interviewer = $interview->getInterviewer();
                    if( $interviewer ) {
                        $interviewerIndex = $interviewer->getUsernameOptimal();
                        if( array_key_exists($interviewerIndex, $totalFellappInterviewsArr) ) {
                            $currentCount = $totalFellappInterviewsArr[$interviewerIndex];
                        } else {
                            $currentCount = 0;
                        }
                        $currentCount++;
                        $totalFellappInterviewsArr[$interviewerIndex] = $currentCount;
                        $totalFellappInterviewsCount++;
                    }
                }//foreach $interview
            }//foreach $fellapp


            $resapps = $resappUtil->getResAppByStatusAndYear(null,null,$yearRange);
            //echo "yearRange=$yearRange, resapps=".count($resapps)."<br>";
            //exit('111');

            foreach($resapps as $resapp) {
                foreach($resapp->getInterviews() as $interview) {
                    if( $interview->isEmpty() === false ) {
                        $interviewer = $interview->getInterviewer();
                        if ($interviewer) {
                            $interviewerIndex = $interviewer->getUsernameOptimal();
                            if (array_key_exists($interviewerIndex, $totalResappInterviewsArr)) {
                                $currentCount = $totalResappInterviewsArr[$interviewerIndex];
                            } else {
                                $currentCount = 0;
                            }
                            $currentCount++;
                            $totalResappInterviewsArr[$interviewerIndex] = $currentCount;
                            $totalResappInterviewsCount++;
                        }
                    }
                }//foreach $interview
            }//foreach $resapp

            $totalInterviewsCount = $totalFellappInterviewsCount + $totalResappInterviewsCount;

            $fellappCount = count($fellapps);
            $resappCount = count($resapps);

            $addTitle = " - " . $totalInterviewsCount . " feedbacks in total for $startYear-$endYear";
            $addTitle = $addTitle . " (" . $fellappCount + $resappCount . " applications in total)";
            //$addTitle = $addTitle.", fellapps=".count($fellapps).", resapps=".count($resapps); //.", yearRange=".$yearRange;

            //$chartName = $this->getTitleWithTotal($chartName,$addTitle,"");
            $chartName = $chartName . " " . $addTitle;

            //increase vertical
            //tickformat: https://github.com/d3/d3-format/blob/main/README.md#locale_format
            $layoutArray = array(
                'height' => $this->height,//*1.3,
                'width' => $this->width,
                'title' => $chartName,
                'margin' => array('b'=>200),
                'xaxis' => array(
                    'tickformat' =>  "d",
                ),
                //'showlegend' => false
            );

            //array_multisort($totalInterviewsArr, SORT_DESC);
            arsort($totalFellappInterviewsArr,SORT_NUMERIC);
            arsort($totalResappInterviewsArr,SORT_NUMERIC);

            //dump($totalFellappInterviewsArr);
            //dump($totalResappInterviewsArr);
            //exit('111');

            $combinedData = array();
            $combinedData['Fellowship - '.$totalFellappInterviewsCount.' interviewers'] = $totalFellappInterviewsArr;
            $combinedData['Residency - '.$totalResappInterviewsCount.' interviewers'] = $totalResappInterviewsArr;

            $chartsArray = $this->getStackedChart($combinedData, $chartName, "stack", $layoutArray);
        }

        //country of origin of people that have applied to our program and then sorted by fellowship (similar to 66.)
        //"73. Country of origin for the fellowship applicants" => "fellapp-country-origin",
        if( $chartType == "fellapp-country-origin" ) {
            $fellappUtil = $this->container->get('fellapp_util');

            //$year can be multiple dates "2019,2020,2021..."
            $startYear = $startDate->format('Y');
            $endYear = $endDate->format('Y');
            //echo "1startYear=".$startYear.", endYear=".$endYear."<br>";

            if ((int)$startYear > (int)$endYear) {
                //echo "flip<br>";
                $tempYear = $startYear;
                $startYear = $endYear;
                $endYear = $tempYear;
            }
            //echo "2startYear=".$startYear.", endYear=".$endYear."<br>";

            $yearRange = '';

            foreach (range($startYear, $endYear) as $thisYear) {
                $yearRangeArr[] = $thisYear;
            }

            if (count($yearRangeArr) > 0) {
                $yearRange = implode(",", $yearRangeArr);
            } else {
                $yearRange = $startYear;
            }

            $fellapps = $fellappUtil->getFellAppByStatusAndYear(null,null,$yearRange);
            //echo "yearRange=$yearRange, fellapps=".count($fellapps)."<br>";
            //exit('111');

            $fellappDataArr = array();
            $countryCountArr = array();
            $countryArr = array();
            $fellSpecialtyArr = array();
            $fellSpecialtyName = NULL;

            foreach($fellapps as $fellapp) {
                //get fellowship specialty
                $fellSpecialtyName = NULL;
                $fellSpecialty = $fellapp->getFellowshipSubspecialty();
                if( $fellSpecialty ) {
                    $fellSpecialtyName = $fellSpecialty->getName();
                }
                if( !$fellSpecialtyName ) {
                    continue;
                }

                if( !isset($fellSpecialtyArr) ) {
                    $fellSpecialtyArr[$fellSpecialtyName] = 1;
                }

                //get citizenship App\UserdirectoryBundle\Entity\Citizenship
                $citizenshipName = NULL;
                $citizenships = $fellapp->getCitizenships();
                foreach($citizenships as $citizenship) {
                    if ($citizenship) {
                        $country = $citizenship->getCountry();
                        if ($country) {
                            $citizenshipName = $country->getName();
                        }
                    }
                    //echo "citizenshipName=$citizenshipName <br>";

                    if( $citizenshipName ) {
                        $citizenshipName = ucfirst(strtolower($citizenshipName));;
                        //$citizenshipName = strtolower($citizenshipName);

                        if (!isset($countryArr[$citizenshipName])) {
                            $countryArr[$citizenshipName] = 1;
                        }

                        $countCountry = 0;
                        if (isset($fellappDataArr[$fellSpecialtyName][$citizenshipName])) {
                            $countCountry = $fellappDataArr[$fellSpecialtyName][$citizenshipName];
                        }
                        $fellappDataArr[$fellSpecialtyName][$citizenshipName] = $countCountry + 1;
                    }

//                    if (isset($countryCountArr[$fellSpecialtyName])) {
//                        $fellappArr = $fellappDataArr[$fellSpecialtyName];
//                    }
//                    $countryCountArr[$fellSpecialtyName] = array($citizenshipName=>$countCountry); //[$citizenshipName] = $countCountry + 1;
                }
            }

            //dump($countryArr);
            //dump($fellappDataArr);
            //exit('111');

//            $combinedData = array();
//            foreach($countryArr as $citizenshipName) {
//                $thisArr = array();
//                foreach($fellSpecialtyArr as $fellSpecialtyName) {
//                    $thisArr[$fellSpecialtyName] = $fellappDataArr[$fellSpecialtyName][$citizenshipName];
//                }
//                //$thisArr['1'] = 1;
//                //$thisArr['2'] = 2;
//                //$thisArr['3'] = 3;
//                $combinedData[$citizenshipName] = $thisArr; //$fellappDataArr[$citizenshipName];
//                //$combinedData["Ex1"] = $ex1Arr;
//            }

//            $ex1Arr['1'] = 3;
//            $ex1Arr['2'] = 4;
//            $ex2Arr['3'] = 5;
//            $ex2Arr['4'] = 6;
//            $combinedData = array();
//            $combinedData["Ex1"] = $ex1Arr;
//            $combinedData["Ex2"] = $ex2Arr;

            //dump($combinedData);
            //exit('111');

            $chartName = $chartName;

            //73.
            //1) X - fellap types, menu on right - countries
            //2) X - countries, menu on right - fellap types
            $chartsArray = $this->getStackedChart($fellappDataArr, $chartName, "stack", $layoutArray);
        }


        if( $chartType == "" ) {

        }
        if( $chartType == "" ) {

        }


        //$chartsArray = array(); //testing
        //$chartsArray = null; //testing

        if( !is_array($chartsArray) ) {
            //echo "null <br>";
            $chartKey = $this->getChartTypeByValue($chartType);
            $chartsArray['error'] = "Chart type '$chartKey' is not valid";
            $chartsArray['warning'] = false;
            return $chartsArray;
        }

        if( is_array($chartsArray) && count($chartsArray) == 0 ) {
            //echo "count is 0 <br>";
            if( !$warningNoData ) {
                $chartKey = $this->getChartTypeByValue($chartType);
                $warningNoData = "Chart data is not found for '$chartKey'";
            }
            $chartsArray['warning'] = $warningNoData;   //"Chart data is not found for '$chartKey'";
            $chartsArray['error'] = false;
            return $chartsArray;
        }

        //dump($chartsArray);

        //chart is ok: add $chartObject->getId() to $chartsArray
        if( $chartObject ) {
            //$chartsArray = array(
            //    'layout' => $layoutArray,
            //    'data' => $dataArray
            //);
            //$layoutArray = $chartsArray['layout'];
            //$dataArray = $chartsArray['data'];

            //$dataArray[] = $chartDataArray;
            //$chartDataArray = $dataArray[0];
            //$chartDataArray['id'] = $chartObject->getId();
            
            //add favorite flag
            $user = $this->security->getUser();
            //$chartDataArray['favorite'] = $chartObject->isFavorite($user);
            
            //overwrite $chartsArray['data']
            //$dataArray = array();
            //$dataArray[] = $chartDataArray;
            //$chartsArray['data'] = $dataArray;

//            $chartsArray = array(
//                'chartId' => $chartObject->getId(),
//                'favorite' => $chartObject->isFavorite($user),
//                'layout' => $layoutArray,
//                'data' => $dataArray
//            );

            $user = $this->security->getUser();
            $chartsArray['chartId'] = $chartObject->getId();
            $chartsArray['favorite'] = $chartObject->isFavorite($user);
        }

        //dump($chartsArray);

        return $chartsArray;
    }




}

