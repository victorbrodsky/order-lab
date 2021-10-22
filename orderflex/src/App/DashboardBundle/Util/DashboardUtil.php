<?php
/**
 * Created by PhpStorm.
 * User: oli2002 Oleg Ivanov
 * Date: 10/7/2021
 * Time: 12:25 PM
 */

namespace App\DashboardBundle\Util;


use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;


class DashboardUtil
{

    protected $container;
    protected $em;
    protected $secTokenStorage;
    protected $secAuth;

    private $width = 1200;
    private $height = 600;
    private $otherId = "All other [[otherStr]] combined";
    private $otherSearchStr = "All other ";
    //private $quantityLimit = 10;

    private $lightFilter = true;

    public function __construct(EntityManagerInterface $em, ContainerInterface $container)
    {
        $this->container = $container;
        $this->em = $em;
        $this->secAuth = $container->get('security.authorization_checker'); //$this->secAuth->isGranted("ROLE_USER")
        //$this->secToken = $container->get('security.token_storage')->getToken(); //$user = $this->secToken->getUser();
        $this->secTokenStorage = $container->get('security.token_storage'); //$user = $this->secTokenStorage->getToken()->getUser();
    }


    //get topics
    public function getFilterTopics() {

        //echo "111 <br>";

        //get all enabled dashboard topics
        $topics = $this->em->getRepository('AppDashboardBundle:TopicList')->findBy(
            array(
                'type' => array("default","user-added")
            ),
            array('orderinlist' => 'ASC')
        );

//        echo "topics=".count($topics)."<br>";

        $parent = NULL;
        $elements = array();
        foreach($topics as $topic) {
            //echo "topic=$topic <br>";
            //echo "level=".$topic->getLevel()."<br>";
            if( $topic->getLevel() == 0 ) {
                $parent = $topic."";
            }
            if( $topic->getLevel() == 1 ) {
                $elements[$topic->getId()] = $topic . "";
            }
        }

        $filterTopics = array();

        $filterTopics[$parent] = $elements;
        //dump($filterTopics);
        //exit();

        return $filterTopics;
    }

    public function getChartTypes() {

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
            "1. Principle Investigators by Affiliation (linked)" =>                   "pi-by-affiliation",
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

            //"60. Number of fellowship applicant by year" => "fellapp-number-applicant-by-year",
            //"61. Average sum of the USMLE scores for fellowship applicant by year" => "fellapp-average-usmle-scores-by-year",

            "62. New and Edited Call Log Entries Per Week" => "new-and-edited-calllog-entries-per-day",
            "63. Patients with Call Log Entries Per Week" => "patients-calllog-per-day",
            "" => "",
            "" => "",
            "" => "",
        );
        return $chartTypes;
    }
    public function getChartTypeByValue($value) {
        //$this->getChartTypes();
        $key = array_search($value, $this->getChartTypes());
        return $key;
    }

    public function getChartsByTopic( $topic ) {

        echo "getChartsByTopic: topic=".$topic."<br>";
        //exit("topic=".$topic);

        //$em = $this->getDoctrine()->getManager();

        //$chartsArray = array();

        $repository = $this->em->getRepository('AppDashboardBundle:ChartList');
        $dql =  $repository->createQueryBuilder("list");
        $dql->leftJoin('list.topics','topics');

        //$dql->where($selectWhere);
        $dql->andWhere("topics = :topicId");

        $parameters = array("topicId"=>$topic->getId());

        $query = $dql->getQuery();

        $query->setParameters($parameters);

        $charts = $query->getResult();
        echo "charts count=".count($charts)."<br>";


        return $charts;
    }

}

