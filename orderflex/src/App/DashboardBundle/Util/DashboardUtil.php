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

        echo "topics=".count($topics)."<br>";

        $parent = NULL;
        $elements1 = array();
        foreach($topics as $topic) {
            echo "topic=$topic <br>";
            if( $topic->getLevel() == 0 ) {
                $parent = $topic."";
            }
            if( $topic->getLevel() == 1 ) {
                $elements1[] = $topic . "";
            }
        }

        $filterTopics = array();

        $filterTypes[$parent] = $elements1;
        //dump($filterTopics);
        //exit();

        return $filterTopics;

        $transresUtil = $this->container->get('transres_util');

        //get all enabled project specialties
        $specialties = $this->em->getRepository('AppTranslationalResearchBundle:SpecialtyList')->findBy(
            array(
                'type' => array("default","user-added")
            ),
            array('orderinlist' => 'ASC')
        );

        $allowSpecialties = array();
        foreach($specialties as $projectSpecialty) {
            $allowSpecialties[] = $projectSpecialty->getUppercaseFullName();
        }

        $filterTypes = array();

        //My work requests
        $elements1 = array(
            'My Submitted Requests',
            "My Draft Requests",
            'Submitted Requests for My Projects',
            'Draft Requests for My Projects',
            //'Requests I Completed',
            //'[[hr]]'
        );
        if( $this->secAuth->isGranted('ROLE_TRANSRES_TECHNICIAN') || $transresUtil->isAdminOrPrimaryReviewer() ) {
            $elements1[] = 'Requests I Completed';
        }
        $filterTypes['My work requests'] = $elements1;

//        $filterTypes[] = '[[hr]]';

        if( $transresUtil->isAdminOrPrimaryReviewerOrExecutive() === false && $this->secAuth->isGranted('ROLE_TRANSRES_TECHNICIAN') === false ) {
            return $filterTypes;
        }

        //All by type
        $elements2 = array('All Requests');
        foreach($allowSpecialties as $allowSpecialty) {
            $elements2[] = "All $allowSpecialty Requests";
        }
        $elements2[] = 'All Requests (including Drafts)';
        $filterTypes['All by type'] = $elements2;
//        $filterTypes[] = '[[hr]]';

        //Pending by type
        $elements3 = array('All Pending Requests');
        //$filterTypes['Pending by type'] = 'All Pending Requests';
        foreach($allowSpecialties as $allowSpecialty) {
            $elements3[] = "All $allowSpecialty Pending Requests";
        }
        $filterTypes['Pending by type'] = $elements3;
//        $filterTypes[] = '[[hr]]';

        //Active by type
        $elements4 = array('All Active Requests');
        //$filterTypes['Active by type'] = 'All Active Requests';
        foreach($allowSpecialties as $allowSpecialty) {
            $elements4[] = "All $allowSpecialty Active Requests";
        }
        $filterTypes['Active by type'] = $elements4;
//        $filterTypes[] = '[[hr]]';

        //Completed by type
        $elements5 = array('All Completed Requests');
//        $filterTypes['Completed by type'] = 'All Completed Requests';
        foreach($allowSpecialties as $allowSpecialty) {
            $elements5[] = "All $allowSpecialty Completed Requests";
        }
        $filterTypes['Completed by type'] = $elements5;
//        $filterTypes[] = '[[hr]]';

        //Completed & notified by type
        $elements6 = array('All Completed and Notified Requests');
        //$filterTypes['Completed & notified by type'] = 'All Completed and Notified Requests';
        foreach($allowSpecialties as $allowSpecialty) {
            $elements6[] = "All $allowSpecialty Completed and Notified Requests";
        }
        $filterTypes['Completed & notified by type'] = $elements6;
//        $filterTypes[] = '[[hr]]';

        //By queues
        //add all the Work Queues as named in the list manager + appended “ Work Queue”
        //(So there should be two new links: “CTP Lab Work Queue” and “MISI Lab Work Queue”
        $workQueues = $transresUtil->getWorkQueues();
        $elements7 = array('All Requests with Work Queues');
        //$filterTypes['By queues'] = 'All Requests with Work Queues';
        foreach($workQueues as $workQueue) {
            $elements7[] = $workQueue->getName()." Work Queue";
        }
        $filterTypes['By queues'] = $elements7;

        return $filterTypes;
    }

}

