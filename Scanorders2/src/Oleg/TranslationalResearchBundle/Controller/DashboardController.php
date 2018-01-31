<?php

namespace Oleg\TranslationalResearchBundle\Controller;

use Oleg\TranslationalResearchBundle\Form\FilterDashboardType;
use Oleg\UserdirectoryBundle\Util\LargeFileDownloader;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;


/**
 * @Route("dashboard")
 */
class DashboardController extends Controller
{

    /**
     * @Route("/pi-level/", name="translationalresearch_dashboard_pilevel")
     * @Route("/funded-level/", name="translationalresearch_dashboard_fundedlevel")
     * @Template("OlegTranslationalResearchBundle:Dashboard:pilevel.html.twig")
     */
    public function piLevelAction( Request $request ) {

        if( false === $this->get('security.authorization_checker')->isGranted('ROLE_TRANSRES_USER') ) {
            return $this->redirect( $this->generateUrl($this->container->getParameter('translationalresearch.sitename').'-nopermission') );
        }

        $transResFormNodeUtil = $this->container->get('transres_formnode_util');
        $routeName = $request->get('_route');
        $infos = array();

        //////////// Filter ////////////
        $params = array();
        $filterform = $this->createForm(FilterDashboardType::class, null,array(
            'method' => 'GET',
            'form_custom_value'=>$params
        ));
        $filterform->handleRequest($request);
        //////////// EOF Filter ////////////

        $layoutArray = array(
            'height' => 800,
            'width' =>  800
        );

//            var data = [{
//                values: [19, 26, 55],
//                labels: ['Residential', 'Non-Residential', 'Utility'],
//                type: 'pie'
//            }];
        $dataArray = array();
        $chartDataArray = array();
        $labels = array();
        $values = array();
        $type = 'pie';

        //$labels = array('Residential', 'Non-Residential', 'Utility');
        //$values = array(19, 26, 55);

        $projects = $this->getProjectPis($filterform);

        //Projects per PIs
        if( $routeName == "translationalresearch_dashboard_pilevel" ) {
            $title = "Dashboard: Projects per PI";
            $nameValueArr = array();
            foreach ($projects as $project) {
                $pis = $project->getPrincipalInvestigators();
                foreach ($pis as $pi) {
                    $userName = $pi->getUsernameOptimal();
                    if (isset($nameValueArr[$userName])) {
                        $count = $nameValueArr[$userName] + 1;
                    } else {
                        $count = 1;
                    }
                    $nameValueArr[$userName] = $count;
                }
            }

            foreach ($nameValueArr as $name => $value) {
                $labels[] = $name;
                $values[] = $value;
            }
        }

        if( $routeName == "translationalresearch_dashboard_fundedlevel" ) {
            $title = "Dashboard: Funded vs Un-Funded Projects";
            $nameValueArr = array();
            $fundedCount = 0;
            $unfundedCount = 0;
            foreach ($projects as $project) {
                $fundingNumber = $transResFormNodeUtil->getProjectFormNodeFieldByName($project,"If funded, please provide account number");
                if( $fundingNumber ) {
                    $fundedCount++;
                } else {
                    $unfundedCount++;
                }
            }
            //echo "fundedCount=".$fundedCount."<br>";
            //echo "unfundedCount=".$unfundedCount."<br>";

            $labels = array('Number of Funded Projects','Number of Un-Funded Projects');
            $values = array($fundedCount,$unfundedCount);
        }

        $chartDataArray['values'] = $values;
        $chartDataArray['labels'] = $labels;
        $chartDataArray['type'] = $type;
        $dataArray[] = $chartDataArray;

        return array(
            'infos' => $infos,
            'title' => $title,
            'filterform' => $filterform->createView(),
            'dataArray' => $dataArray,
            'layoutArray' => $layoutArray
        );
    }


//    /**
//     * @Route("/funded-level/", name="translationalresearch_dashboard_fundedlevel")
//     * @Template("OlegTranslationalResearchBundle:Dashboard:pilevel.html.twig")
//     */
//    public function fundedLevelAction( Request $request ) {
//
//        if( false === $this->get('security.authorization_checker')->isGranted('ROLE_TRANSRES_USER') ) {
//            return $this->redirect( $this->generateUrl($this->container->getParameter('translationalresearch.sitename').'-nopermission') );
//        }
//
//        $title = "Dashboard for Funded Project Level";
//        $infos = array();
//
//        //////////// Filter ////////////
//        $params = array();
//        $filterform = $this->createForm(FilterDashboardType::class, null,array(
//            'method' => 'GET',
//            'form_custom_value'=>$params
//        ));
//        $filterform->handleRequest($request);
//        //////////// EOF Filter ////////////
//
//        $params = array();
//        $filterform = $this->createForm(FilterDashboardType::class, null,array(
//            'method' => 'GET',
//            'form_custom_value'=>$params
//        ));
//
//        $filterform->handleRequest($request);
//
//        return array(
//            'infos' => $infos,
//            'title' => $title,
//        );
//    }


    public function getProjectPis($filterform) {
        $em = $this->getDoctrine()->getManager();

        $repository = $em->getRepository('OlegTranslationalResearchBundle:Project');
        $dql =  $repository->createQueryBuilder("project");
        $dql->select('project');

        //$dql->where("project.state=:state");
        //$projects = $repository->findAll();
        //$query = $dql->getQuery();
        //echo "projectId=".$project->getId()."<br>";
        //echo "reviewId=".$reviewId."<br>";

        $startDate = $filterform['startDate']->getData();
        $endDate = $filterform['endDate']->getData();
        $dqlParameters = array();

        if( $startDate ) {
            //echo "startDate=" . $startDate->format('Y-m-d H:i:s') . "<br>";
            $dql->andWhere('project.createDate >= :startDate');
            $dqlParameters['startDate'] = $startDate->format('Y-m-d H:i:s');
        }
        if( $endDate ) {
            $endDate->modify('+1 day');
            $dql->andWhere('project.createDate <= :endDate');
            $dqlParameters['endDate'] = $endDate->format('Y-m-d H:i:s');
        }

        $query = $em->createQuery($dql);

        $query->setParameters($dqlParameters);
        //echo "query=".$query->getSql()."<br>";

        $projects = $query->getResult();

        return $projects;
    }

}
