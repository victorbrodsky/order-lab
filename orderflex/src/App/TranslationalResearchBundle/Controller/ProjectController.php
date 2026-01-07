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

namespace App\TranslationalResearchBundle\Controller;



use App\TranslationalResearchBundle\Entity\IrbApprovalTypeList; //process.py script: replaced namespace by ::class: added use line for classname=IrbApprovalTypeList


//use App\TranslationalResearchBundle\Entity\ProjectGoal;
use App\TranslationalResearchBundle\Entity\ProjectGoal;
use App\TranslationalResearchBundle\Entity\TransResRequest;
use App\TranslationalResearchBundle\Form\ProjectGoalsSectionType;
use App\TranslationalResearchBundle\Form\ProjectMisiType;
use App\UserdirectoryBundle\Entity\Document; //process.py script: replaced namespace by ::class: added use line for classname=Document


use App\UserdirectoryBundle\Entity\Institution; //process.py script: replaced namespace by ::class: added use line for classname=Institution


use App\OrderformBundle\Entity\MessageCategory; //process.py script: replaced namespace by ::class: added use line for classname=MessageCategory


use App\TranslationalResearchBundle\Entity\BusinessPurposeList; //process.py script: replaced namespace by ::class: added use line for classname=BusinessPurposeList
//use Graphp\GraphViz\GraphViz;
use App\UserdirectoryBundle\Entity\EventObjectTypeList;
use Doctrine\Common\Collections\ArrayCollection;
use App\TranslationalResearchBundle\Entity\Project;
use App\TranslationalResearchBundle\Form\FilterType;
use App\TranslationalResearchBundle\Form\ProjectStateType;
use App\TranslationalResearchBundle\Form\ProjectType;
use App\UserdirectoryBundle\Controller\OrderAbstractController;


use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Workflow\DefinitionBuilder;
use Symfony\Component\Workflow\Dumper\GraphvizDumper;
use Symfony\Component\Workflow\Transition;

///**
// * Project controller.
// *
// * @Route("project")
// */

class ProjectController extends OrderAbstractController
{

    #[Route(path: '/', name: 'translationalresearch_home', methods: ['GET'])]
    public function homeAction()
    {
        if( false === $this->isGranted('ROLE_TRANSRES_USER') ) {
            return $this->redirect( $this->generateUrl($this->getParameter('translationalresearch.sitename').'-nopermission') );
        }

        $transresUtil = $this->container->get('transres_util');
        if(
            $transresUtil->isAdminOrPrimaryReviewer() === false &&
            $this->isGranted('ROLE_TRANSRES_TECHNICIAN') === false
        ) {
            //exit('not admin');
            if( $this->isGranted('ROLE_TRANSRES_REQUESTER') ) {
                return $this->redirectToRoute('translationalresearch_my_project_index');
            }
        }

        return $this->redirectToRoute('translationalresearch_project_index');
    }

    /**
     * Lists all project entities.
     *
     *
     *
     *
     *
     */
    #[Route(path: '/projects/', name: 'translationalresearch_project_index', methods: ['GET'])]
    #[Route(path: '/my-projects/', name: 'translationalresearch_my_project_index', methods: ['GET'])]
    #[Route(path: '/projects-where-i-am-the-requester/', name: 'translationalresearch_my_request_project_index', methods: ['GET'])]
    #[Route(path: '/draft-projects-where-i-am-the-requester/', name: 'translationalresearch_my_request_project_draft_index', methods: ['GET'])]
    #[Route(path: '/projects-i-have-reviewed/', name: 'translationalresearch_my_reviewed_project_index', methods: ['GET'])]
    #[Route(path: '/projects-pending-my-review/', name: 'translationalresearch_my_pending_review_project_index', methods: ['GET'])]
    #[Route(path: '/projects-pending-review/', name: 'translationalresearch_pending_review_project_index', methods: ['GET'])]
    #[Route(path: '/projects-awaiting-additional-info-to-be-reviewed/', name: 'translationalresearch_my_missinginfo_review_project_index', methods: ['GET'])]
    #[Route(path: '/active-project-requests-with-expired-approval/', name: 'translationalresearch_active_expired_project_index', methods: ['GET'])]
    #[Route(path: '/active-project-requests-with-approval-expiring-soon/', name: 'translationalresearch_active_expired_soon_project_index', methods: ['GET'])]
    #[Route(path: '/active-project-requests-non-funded-over-budget/', name: 'translationalresearch_active_non_funded_over_budget_project_index', methods: ['GET'])]
    #[Route(path: '/approved-project-requests-funded/', name: 'translationalresearch_approved_funded_project_index', methods: ['GET'])]
    #[Route(path: '/approved-project-requests-non-funded/', name: 'translationalresearch_approved_non_funded_project_index', methods: ['GET'])]
    #[Template('AppTranslationalResearchBundle/Project/index.html.twig')]
    public function indexAction(Request $request)
    {

        //TODO: check performance   scan_perSiteSettings?
        if( false === $this->isGranted('ROLE_TRANSRES_USER') ) {
            return $this->redirect( $this->generateUrl($this->getParameter('translationalresearch.sitename').'-nopermission') );
        }

        //test: only 12 queries vs ~800 queries in regular run
//        return array(
//            'filterError' => true,
//            'title' => "Test Performance",
//        );

        $transresUtil = $this->container->get('transres_util');
        //$transResFormNodeUtil = $this->container->get('transres_formnode_util');
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $routeName = $request->get('_route');
        $title = "Project Requests";

        //testing
//        $roles = $user->getRoles();
//        foreach( $roles as $role ) {
//            echo "role=$role <br>";
//        }
//        if( $this->isGranted("ROLE_TRANSRES_REQUESTER_COVID19") ) {
//            echo "covid role is OK <br>";
//        }
//        if( $this->isGranted("ROLE_TRANSRES_REQUESTER_APCP") ) {
//            echo "apcp role is OK <br>";
//        }

        if( $routeName == "translationalresearch_project_index" ) {
            if( 
                //$transresUtil->isAdminOrPrimaryReviewer() === false &&
                $transresUtil->isAdminOrPrimaryReviewerOrExecutive() === false &&
                $this->isGranted('ROLE_TRANSRES_TECHNICIAN') === false 
            ) {
                if ($this->isGranted('ROLE_TRANSRES_REQUESTER')) {
                    return $this->redirectToRoute('translationalresearch_my_project_index');
                }
            }
        }

        //get allowed and denied projectSpecialties
        $projectSpecialtyAllowedRes = $transresUtil->getAllowedProjectSpecialty($user);
        $projectSpecialtyAllowedArr = $projectSpecialtyAllowedRes['projectSpecialtyAllowedArr'];
        $projectSpecialtyDeniedArr = $projectSpecialtyAllowedRes['projectSpecialtyDeniedArr'];

//        if( $routeName == "translationalresearch_my_pending_review_project_index" ) {
//            return $this->redirectToRoute(
//                'translationalresearch_my_review_project_index',
//                array(
//                    'filter[state][0]' => 'irb_review',
//                    'filter[state][1]' => 'admin_review',
//                    'filter[state][2]' => 'committee_review',
//                    'filter[state][3]' => 'final_review',
//                    'filter[preroute]' => 'translationalresearch_my_pending_review_project_index'
//                )
//            );
//        }

        if( $routeName == "translationalresearch_pending_review_project_index" ) {

            $indexParams = array(
                'filter[state][0]' => 'irb_review',
                'filter[state][1]' => 'irb_missinginfo',
                'filter[state][2]' => 'admin_review',
                'filter[state][3]' => 'admin_missinginfo',
                'filter[state][4]' => 'committee_review',
                'filter[state][5]' => 'final_review',
                'title' => "Project Requests Pending Review",
            );

            //projectSpecialty filter[projectSpecialty][]=2
            foreach($projectSpecialtyAllowedArr as $projectSpecialtyAllowed) {
                $indexParams['filter[projectSpecialty]'][] = $projectSpecialtyAllowed->getId();
            }

            return $this->redirectToRoute(
                'translationalresearch_project_index',
                $indexParams
            );
        }

        //$projects = $em->getRepository('AppTranslationalResearchBundle:Project')->findAll();

        //process.py script: replaced namespace by ::class: ['AppTranslationalResearchBundle:Project'] by [Project::class]
        $repository = $em->getRepository(Project::class);
        $dql =  $repository->createQueryBuilder("project");
        $dql->select('project');

        $dql->leftJoin('project.submitter','submitter');

        $dql->leftJoin('project.principalInvestigators','principalInvestigators');
        $dql->leftJoin('principalInvestigators.infos','principalInvestigatorsInfos');

        $dql->leftJoin('project.principalIrbInvestigator','principalIrbInvestigator');

        $dql->leftJoin('project.irbReviews','irbReviews');
        $dql->leftJoin('irbReviews.reviewer','irbReviewer');
        $dql->leftJoin('irbReviews.reviewerDelegate','irbReviewerDelegate');

        $dql->leftJoin('project.adminReviews','adminReviews');
        $dql->leftJoin('adminReviews.reviewer','adminReviewer');
        $dql->leftJoin('adminReviews.reviewerDelegate','adminReviewerDelegate');

        $dql->leftJoin('project.committeeReviews','committeeReviews');
        $dql->leftJoin('committeeReviews.reviewer','committeeReviewer');
        $dql->leftJoin('committeeReviews.reviewerDelegate','committeeReviewerDelegate');

        $dql->leftJoin('project.finalReviews','finalReviews');
        $dql->leftJoin('finalReviews.reviewer','finalReviewer');
        $dql->leftJoin('finalReviews.reviewerDelegate','finalReviewerDelegate');

        $dql->leftJoin('project.submitInvestigators','submitInvestigators');
        $dql->leftJoin('project.coInvestigators','coInvestigators');
        $dql->leftJoin('project.pathologists','pathologists');
        $dql->leftJoin('project.billingContact','billingContact');
        $dql->leftJoin('project.contacts','contacts');

        $dql->leftJoin('project.projectType','projectType');

        $advancedFilter = 0;
        $formnode = false;
        $dqlParameters = array();

        $humanName = $transresUtil->getHumanName();

//        //get allowed and denied projectSpecialties
//        $projectSpecialtyAllowedRes = $transresUtil->getAllowedProjectSpecialty($user);
//        $projectSpecialtyAllowedArr = $projectSpecialtyAllowedRes['projectSpecialtyAllowedArr'];
//        $projectSpecialtyDeniedArr = $projectSpecialtyAllowedRes['projectSpecialtyDeniedArr'];

        //////// create filter //////////
        //$filterError = true;
        //$transresUsers = array($user);
        $transresUsers = $transresUtil->getAppropriatedUsers();
        //echo "transresUsers count=".count($transresUsers)."<br>";

        $stateChoiceArr = $transresUtil->getStateChoisesArr();
        $stateChoiceArr["All except Drafts"] = "All-except-Drafts";
        $stateChoiceArr["All except Drafts and Canceled"] = "All-except-Drafts-and-Canceled";
        //$stateChoiceArr["Closed"] = "Closed";
        //$stateChoiceArr["Canceled"] = "Canceled";
        //$defaultStatesArr = $transresUtil->getDefaultStatesArr();
        $transresPricesList = $transresUtil->getPricesList();

        $expectedExpirationDateChoices = $transresUtil->getExpectedExpirationDateChoices();

        $requesterGroup = $transresUtil->getProjectRequesterGroupChoices();

        $trpAdminOrTech = false;
        if(
            $this->isGranted('ROLE_TRANSRES_ADMIN') ||
            $this->isGranted('ROLE_TRANSRES_TECHNICIAN')
        ) {
            $trpAdminOrTech = true;
        }
        
        $params = array(
            //'SecurityAuthChecker' => $this->container->get('security.authorization_checker'),
            'trpAdminOrTech' => $trpAdminOrTech,
            'transresUsers' => $transresUsers,
            'stateChoiceArr' => $stateChoiceArr,
            //'defaultStatesArr' => $defaultStatesArr,
            'projectSpecialtyAllowedArr' => $projectSpecialtyAllowedArr,
            'defaultStatesArr' => array("All-except-Drafts-and-Canceled"),
            //'defaultStatesArr' => array("All-except-Drafts","Canceled","Closed"),
            'toImplicitExpDate' => null,
            'fromImplicitExpDate' => null,
            'humanName' => $transresUtil->getHumanName(),
            'humanAnimalNameBracket' => $transresUtil->getHumanAnimalName("brackets"),
            'humanAnimalNameSlash' => $transresUtil->getHumanAnimalName("slash"),
            'transresPricesList' => $transresPricesList,
            'expectedExpirationDateChoices' => $expectedExpirationDateChoices,
            'requesterGroup' => $requesterGroup,
            'overBudget' => 'all',
            'fundingType' => null
        );

        if( $routeName == "translationalresearch_my_request_project_draft_index" ) {
            $params['defaultStatesArr'] = array('draft');
        }

        if( $routeName == "translationalresearch_active_expired_project_index" ) {
            $params['defaultStatesArr'] = array(
                'irb_review','irb_missinginfo',
                'admin_review','admin_missinginfo',
                'committee_review','final_review','final_approved'
            );
            $params['toImplicitExpDate'] =new \DateTime();
            $title = "Active Project Requests with Expired $humanName";
        }
        if( $routeName == "translationalresearch_active_expired_soon_project_index" ) {
            $params['defaultStatesArr'] = array(
                'irb_review','irb_missinginfo',
                'admin_review','admin_missinginfo',
                'committee_review','final_review','final_approved'
            );
            $today = new \DateTime();
            $params['fromImplicitExpDate'] = new \DateTime();
            $params['toImplicitExpDate'] = $today->modify('+3 months');
            $title = "Active Project Requests with $humanName Expiring Soon";
        }
        if( $routeName == "translationalresearch_active_non_funded_over_budget_project_index" ) {
            //?filter[projectSpecialty][]=2&filter[projectSpecialty][]=1&filter[projectSpecialty][]=5&
            //filter[projectSpecialty][]=4&filter[searchId]=&filter[state][]=irb_review&filter[state][]=irb_missinginfo&
            //filter[state][]=admin_review&filter[state][]=admin_missinginfo&filter[state][]=committee_review&
            //filter[state][]=final_review&filter[state][]=final_approved&
            //filter[startDate]=&filter[endDate]=&
            //filter[searchIrbNumber]=&filter[searchTitle]=&filter[submitter]=&filter[fundingNumber]=&
            //filter[fundingType]=&filter[searchProjectType]=&filter[exportId]=&
            //filter[humanTissue]=&filter[exemptIrbApproval]=&
            //filter[fromExpectedCompletionDate]=&filter[toExpectedCompletionDate]=&
            //filter[briefDescription]=&filter[fromImplicitExpDate]=&
            //filter[toImplicitExpDate]=&filter[priceList]=all&
            //filter[overBudget]=over-budget-with-no-budget

            //do not show all project that have “No Budget Limit” checked
            //list all projects that are also active and non-funded,
            // and that have the “Remaining Budget” amount less than zero
            //AND all projects that have unspent amount of “No Info” (Approved Project Budget is NULL)

            //echo "routeName=$routeName <br>";
            $params['defaultStatesArr'] = array(
                'irb_review','irb_missinginfo',
                'admin_review','admin_missinginfo',
                'committee_review','final_review','final_approved'
            );
            $params['overBudget'] = 'over-budget-with-no-budget'; //'Over Budget or With No Budget';
            $params['fundingType'] = "Non-Funded";
            $title = "Active Non-Funded Projects Over Budget or With No Budget";
        }
        if( $routeName == "translationalresearch_approved_funded_project_index" ) {
            //echo "routeName=$routeName <br>";
            $params['defaultStatesArr'] = array(
                'final_approved'
            );
            $params['fundingType'] = "Funded";
            $title = "Approved Funded Projects";
        }
        if( $routeName == "translationalresearch_approved_non_funded_project_index" ) {
            //echo "routeName=$routeName <br>";
            $params['defaultStatesArr'] = array(
                'final_approved'
            );
            $params['fundingType'] = "Non-Funded";
            $title = "Approved Non-Funded Projects";
        }

        $filterform = $this->createForm(FilterType::class, null,array(
            'method' => 'GET',
            'form_custom_value'=>$params
        ));

        if( count($projectSpecialtyAllowedArr) == 0 ) { //testing getTransResAdminEmails
            $sysAdminEmailArr = $transresUtil->getTransResAdminEmails(null,true,true); //indexAction
            $errorMsg = "You don't have any allowed project specialty in your profile.".
                        "<br>Please contact the system admin(s):".
                        "<br>".implode(", ",$sysAdminEmailArr);
            //no allowed specialty
            return array(
                'filterError' => true,
                //'allProjects' => array(),
                'title' => $errorMsg,
                //'filterform' => $filterform->createView(),
                //'eventObjectTypeId' => null,
                //'advancedFilter' => $advancedFilter
            );
        }

        $filterform->handleRequest($request);

        if(1) { //testing
            $principalInvestigators = $filterform['principalInvestigators']->getData();
            $associatedUsers = $filterform['associatedUsers']->getData();
            $submitter = $filterform['submitter']->getData();
            $reviewers = $filterform['reviewers']->getData();
        } else {
            $principalInvestigators = null;
            $associatedUsers = null;
            $submitter = null;
            $reviewers = null;
        }

        $projectSpecialties = $filterform['projectSpecialty']->getData();
        $states = $filterform['state']->getData();
        $startDate = $filterform['startDate']->getData();
        $endDate = $filterform['endDate']->getData();
        $searchId = $filterform['searchId']->getData();
        $searchTitle = $filterform['searchTitle']->getData();
        $searchIrbNumber = $filterform['searchIrbNumber']->getData();
        $fundingNumber = $filterform['fundingNumber']->getData();
        $fundingType = $filterform['fundingType']->getData();
        $searchProjectType = $filterform['searchProjectType']->getData();
        $exportId = $filterform['exportId']->getData();
        $humanTissue = $filterform['humanTissue']->getData();
        $exemptIrbApproval = $filterform['exemptIrbApproval']->getData();
        $fromExpectedCompletionDate = $filterform['fromExpectedCompletionDate']->getData();
        $toExpectedCompletionDate = $filterform['toExpectedCompletionDate']->getData();
        $fromImplicitExpDate = $filterform['fromImplicitExpDate']->getData();
        $toImplicitExpDate = $filterform['toImplicitExpDate']->getData();
        $briefDescription = $filterform['briefDescription']->getData();
        $expectedExpirationDateChoices = $filterform['expectedExpirationDateChoices']->getData();
        $requesterGroup = $filterform['requesterGroup']->getData();

        $priceList = NULL;
        if( isset($filterform['priceList']) ) {
            $priceList = $filterform['priceList']->getData();
        }

        $overBudget = NULL;
        if( isset($filterform['overBudget']) ) {
            $overBudget = $filterform['overBudget']->getData();
        }

        //$showMatchingAndTotal = $filterform['showMatchingAndTotal']->getData();
//        $archived = $filterform['completed']->getData();
//        $complete = $filterform['review']->getData();
//        $interviewee = $filterform['missinginfo']->getData();
//        $active = $filterform['approved']->getData();
//        $reject = $filterform['closed']->getData();
        if( isset($filterform['preroute']) ) {
            $preroute = $filterform['preroute']->getData();
        }

        $filterTitle = trim((string)$request->get('title'));

        if( $filterTitle ) {
            $title = $filterTitle;
        }
        //////// EOF create filter //////////

        //force to set project specialty filter for non-admin users
        if( $transresUtil->isAdminOrPrimaryReviewer() === false ) {
            //1) check if $projectSpecialties is not set => set $projectSpecialties as $projectSpecialtyAllowedArr
            if (count($projectSpecialties) == 0) {
                $projectSpecialtyReturn = $transresUtil->getReturnIndexSpecialtyArray($projectSpecialtyAllowedArr);
                return $this->redirectToRoute(
                    $routeName,
                    $projectSpecialtyReturn
                );
            } else {
                //2) construct $tempAllowedProjectSpecialties containing only allowed specialty from the $projectSpecialties
                $tempAllowedProjectSpecialties = new ArrayCollection();
                foreach ($projectSpecialties as $projectSpecialty) {
                    if (!$projectSpecialtyDeniedArr->contains($projectSpecialty)) {
                        $tempAllowedProjectSpecialties->add($projectSpecialty);
                    }
                }

                //to prevent redirection loop, check if $projectSpecialties is different than $tempAllowedProjectSpecialties
                $diff = $transresUtil->getObjectDiff($projectSpecialties, $tempAllowedProjectSpecialties->toArray());

                if (count($diff) > 0) {
                    $projectSpecialtyReturn = $transresUtil->getReturnIndexSpecialtyArray($tempAllowedProjectSpecialties);
                    return $this->redirectToRoute(
                        $routeName,
                        $projectSpecialtyReturn
                    );
                }
            }
        }//if not admin


        //////////////////// Start Filter ////////////////////

        //Non admin, Primary Reviewers, Technicians and Executive can see all projects.
        // All other users can view only their projects (where they are requesters: PI, Pathologists Involved, Co-Investigators, Contacts, Billing Contacts)
//        if(
//            $transresUtil->isAdminOrPrimaryReviewer() === false &&
//            $this->isGranted('ROLE_TRANSRES_TECHNICIAN') === false &&
//            $this->isGranted('ROLE_TRANSRES_EXECUTIVE_HEMATOPATHOLOGY') === false &&
//            $this->isGranted('ROLE_TRANSRES_EXECUTIVE_APCP') === false
//        ) {
//
//        }

    //$testingDisable = 0;
    //if( $testingDisable ) {

        /////////////////////// Limit access for non-admin, non-reviewer, non-executive users /////////////////////////////
        //Non admin, Primary Reviewers, Reviewers and Executive can see all projects.
        // All other users can view only their projects
        // (where they are requesters: PI, Pathologists Involved, Co-Investigators, Contacts, Billing Contacts or reviewers)
        if (
            $transresUtil->isAdminOrPrimaryReviewerOrExecutive() //index
            || $this->isGranted('ROLE_TRANSRES_TECHNICIAN')
            || $transresUtil->hasReviewerRoles()
        ) {
            $showOnlyMyProjects = false;
        } else {
            //this will hide projects "Project Requests Pending My Review" for added reviewers directly to the project without reviewer role
            $showOnlyMyProjects = true;
        }
        //echo "showOnlyMyProjects=$showOnlyMyProjects <br>";
        /////////////////////// EOF Limit access for non-admin, non-reviewer, non-executive users /////////////////////////////

        if ($projectSpecialties && count($projectSpecialties) > 0) {
            //echo "testing where projectSpecialty <br>";
            $dql->leftJoin('project.projectSpecialty', 'projectSpecialty');
            $projectSpecialtyIdsArr = array();
            foreach ($projectSpecialties as $projectSpecialty) {
                $projectSpecialtyIdsArr[] = $projectSpecialty->getId();
            }
            $dql->andWhere("projectSpecialty.id IN (:projectSpecialtyIdsArr)");
            $dqlParameters["projectSpecialtyIdsArr"] = $projectSpecialtyIdsArr;
        }

        //echo "count states=".count($states)."<br>";
        if ($states && count($states) > 0) {
            //echo "testing where state <br>";
            //All except Drafts
            $allExceptDraft = "";
            if (in_array("All-except-Drafts", $states)) {
                $allExceptDraft = " OR project.state != 'draft' OR project.state IS NULL";
            }
            if (in_array("All-except-Drafts-and-Canceled", $states)) {
                $allExceptDraft = " OR (project.state != 'draft' AND project.state != 'canceled') OR project.state IS NULL";
            }
            $dql->andWhere("project.state IN (:states)" . $allExceptDraft);
            $dqlParameters["states"] = $states;

            if (count($states) == 1 && $states[0] == "All-except-Drafts-and-Canceled") {
                //as regular filter
            } else {
                $advancedFilter++;
            }
        }

        if ($searchId) {
            $dql->andWhere("(LOWER(project.oid) LIKE LOWER(:oid) OR LOWER(project.globalId) LIKE LOWER(:globalId) OR LOWER(project.sourceId) LIKE LOWER(:sourceId))");
            $dqlParameters["oid"] = "%" . $searchId . "%";
            $dqlParameters["sourceId"] = "%" . $searchId . "%";

            //For global id, remove project sepcialty string such as APCP or MISI from searchId: APCP3366 => 3366
            $searchIdDigits = preg_replace('/[^0-9.]+/', '', $searchId);
            $dqlParameters["globalId"] = "%" . $searchIdDigits . "%";
        }

        if ($exportId) {
            //echo "testing where exportId <br>";
            //$dql->andWhere("LOWER(project.exportId) LIKE LOWER(:exportId)");
            $dql->andWhere("CAST(project.exportId as VARCHAR) LIKE LOWER(:exportId)");
            $dqlParameters["exportId"] = "%" . $exportId . "%";
            $advancedFilter++;
        }

        if ($briefDescription) {
            $dql->andWhere("LOWER(project.description) LIKE LOWER(:description)");
            $dqlParameters["description"] = "%" . $briefDescription . "%";
            $advancedFilter++;
        }

        if ($priceList) {
            if ($priceList != 'all') {
                $dql->leftJoin('project.priceList', 'priceList');
                if( $priceList == 'external' ) {
                //if ($priceList == 'default') {
                    $dql->andWhere("priceList.id IS NULL");
                } else {
                    $dql->andWhere("priceList.id = :priceListId");
                    $dqlParameters["priceListId"] = $priceList;
                }
                $advancedFilter++;
            }
        }

        //TODO: use approvedProjectBudget and $grandTotal (Total) from work requests ($invoice->getTotal(), $grandTotal = $total + $subsidy;)
        //1 way) How to: Add overBudget to the project entity. Update overBudget when invoice is created/updated
        //2 way) Add "Total" (GrandTotal) with subsidy (positive or negative),
        //Update Total when create/edit invoice, change invoice status (Canceled) and change Work Request status (draft, canceled)
        //Then make a query to get approvedProjectBudget-Total
        //3 way) use DoctrineListener, when invoice or work request is updated, update project's total
        //echo "overBudget=$overBudget<br>";
        if ($overBudget) {
            //echo "testing where over-budget <br>";
            if ($overBudget != 'all') {

                if ($overBudget == 'over-budget-with-no-budget') {
                    //do not show all project that have “No Budget Limit” checked
                    //list all projects that are also active and non-funded,
                    // and that have the “Remaining Budget” amount less than zero
                    //AND all projects that have unspent amount of “No Info” (Approved Project Budget is NULL)
//                    $dql->andWhere(
//                        "
//                        project.approvedProjectBudget IS NULL OR
//                        (project.approvedProjectBudget IS NOT NULL AND project.grandTotal IS NOT NULL AND project.grandTotal > project.approvedProjectBudget)
//                        OR
//                        (project.totalCost IS NOT NULL AND project.grandTotal IS NOT NULL AND project.grandTotal > CAST(project.totalCost as NUMERIC))
//                        "
//                    );
                    //populate approvedProjectBudget according to the totalCost
                    //$dql->andWhere("project.approvedProjectBudget IS NULL OR (project.total IS NOT NULL AND project.total > project.approvedProjectBudget)");
                    //$dql->andWhere("(project.grandTotal IS NOT NULL AND project.grandTotal > project.approvedProjectBudget)");
//                    $dql->andWhere("
//                        project.approvedProjectBudget IS NULL
//                        OR
//                        (project.approvedProjectBudget IS NOT NULL AND project.noBudgetLimit = FALSE AND project.total > project.approvedProjectBudget)
//                    ");

                    $dql->andWhere(
                        "project.noBudgetLimit = FALSE" .
                        " AND" .
                        //" project.funded != TRUE"
                        //." AND".
                        " (" .
                        "project.approvedProjectBudget IS NULL" .
                        " OR" .
                        " (project.total IS NOT NULL AND project.approvedProjectBudget IS NOT NULL AND project.total > project.approvedProjectBudget)" .
                        ")"
                    );
                }

                if ($overBudget == 'over-budget') {
                    //$dql->andWhere("project.approvedProjectBudget IS NOT NULL OR project.grandTotal > project.approvedProjectBudget");
                    //$dql->andWhere("project.grandTotal > CAST(project.approvedProjectBudget AS integer)");

//                    $dql->andWhere("
//                        project.approvedProjectBudget IS NOT NULL AND project.noBudgetLimit = FALSE AND project.total > project.approvedProjectBudget
//                    ");

//                    $dql->andWhere("
//                        project.total IS NOT NULL AND project.approvedProjectBudget IS NOT NULL AND project.noBudgetLimit = FALSE AND project.total > project.approvedProjectBudget
//                    ");

                    $dql->andWhere(
                        "project.noBudgetLimit = FALSE" .
                        " AND" .
                        " (" .
                        " (project.total IS NOT NULL AND project.approvedProjectBudget IS NOT NULL AND project.total > project.approvedProjectBudget)" .
                        ")"
                    );
                }

                if ($overBudget == 'with-no-budget') {
                    //$dql->andWhere("project.approvedProjectBudget IS NULL OR project.noBudgetLimit = FALSE");
                    $dql->andWhere("project.approvedProjectBudget IS NULL");
                }

                $advancedFilter++;
            }
        }

        if( $expectedExpirationDateChoices ) {

            $expectedExpirationDateChoices = strtolower($expectedExpirationDateChoices);
            $expectedExpirationDateProcessed = false;

            if ($expectedExpirationDateChoices == strtolower('All')) {
                //do nothing
                $expectedExpirationDateProcessed = true;
            }

            //only for non-funded projects. clear for all funded projects.
            // However we have a separate filter for funded/non-funded projects.
            // Therefore, should we include "project.funded != TRUE" condition here?

            $now = new \DateTime();
            $nowStr = $now->format('Y-m-d H:i:s');

            if( $expectedExpirationDateChoices == strtolower('Expired') ) {
                $dql->andWhere('(:nowDatetime > project.expectedExpirationDate AND project.funded != TRUE)');
                $dqlParameters['nowDatetime'] = $nowStr;
                $expectedExpirationDateProcessed = true;
            }

            if( $expectedExpirationDateChoices == strtolower('Expiring') ) {
                //([expiration date - “Default number of months in advance…” (var from site settings)] older than today)
                $projectExprDurationEmail = $transresUtil->getProjectExprDurationEmail();
                if( $projectExprDurationEmail ) {
                    $advanceDate = $now->modify("+" . $projectExprDurationEmail . " months");
                    $dql->andWhere('(project.expectedExpirationDate BETWEEN :nowDatetime and :advanceDate AND project.funded != TRUE)');
                    $dqlParameters['nowDatetime'] = $nowStr;
                    $dqlParameters['advanceDate'] = $advanceDate->format('Y-m-d H:i:s');
                }
                $expectedExpirationDateProcessed = true;
            }

            if( $expectedExpirationDateChoices == strtolower('Current/Non-expired') ) {
                $dql->andWhere('project.expectedExpirationDate > :nowDatetime AND project.funded != TRUE');
                $dqlParameters['nowDatetime'] = $nowStr;
                $expectedExpirationDateProcessed = true;
            }

            if( $expectedExpirationDateProcessed == false ) {
                $this->addFlash(
                    'warning',
                    "Expiration filter '$expectedExpirationDateChoices' not found"
                );
            }

            $advancedFilter++;
        }

        if( $requesterGroup ) {
            if( $requesterGroup == 'Any' ) {
                //filter nothing
            }
            elseif( $requesterGroup == 'None' ) {
                $dql->andWhere('project.requesterGroup IS NULL');
            }
            else {
                $dql->andWhere('project.requesterGroup = :requesterGroup');
                $dqlParameters['requesterGroup'] = $requesterGroup;
            }

        }

        //////////////// get Projects IDs with the form node filter ////////////////
        if ($formnode) {
            //echo "testing where formnode <br>";
            if ($searchProjectType) {
                $projectTypeIds = $transresUtil->getProjectIdsFormNodeByFieldName($searchProjectType->getId(), "Project Type");
                $dql->andWhere("project.id IN (:projectTypeIds)");
                $dqlParameters["projectTypeIds"] = $projectTypeIds;
                $advancedFilter++;
            }
            if ($searchTitle) {
                $titleIds = $transresUtil->getProjectIdsFormNodeByFieldName($searchTitle, "Title");
                //$dql->andWhere("project.id IN (".implode(",",$titleIds).")");
                $dql->andWhere("project.id IN (:titleIds)");
                $dqlParameters["titleIds"] = $titleIds;
                $advancedFilter++;
            }
            if ($searchIrbNumber) {
                $irbnumberIds = $transresUtil->getProjectIdsFormNodeByFieldName($searchIrbNumber, "$humanName Number");
                //$dql->andWhere("project.id IN (".implode(",",$irbnumberIds).")");
                $dql->andWhere("project.id IN (:irbnumberIds)");
                $dqlParameters["irbnumberIds"] = $irbnumberIds;
                $advancedFilter++;
            }
            if ($fundingNumber) {
                $fundingNumberIds = $transresUtil->getProjectIdsFormNodeByFieldName($fundingNumber, "If funded, please provide account number");
                //$dql->andWhere("project.id IN (".implode(",",$fundingNumberIds).")");
                $dql->andWhere("project.id IN (:fundingNumberIds)");
                $dqlParameters["fundingNumberIds"] = $fundingNumberIds;
                $advancedFilter++;
            }
            if ($fundingType) {
                //echo "fundingType=" . $fundingType . "<br>";
                $compareType = NULL;
                if ($fundingType == "Funded") {
                    $compareType = 1;
                }
                if ($fundingType == "Non-Funded") {
                    $compareType = 0;
                }
                if (isset($compareType)) {
                    //$transResFormNodeUtil->getProjectFormNodeFieldByName(project,"Funded");
                    $fundedIds = $transresUtil->getProjectIdsFormNodeByFieldName($compareType, "Funded");
                    //echo "fundingNumberIds:" . implode(",", $fundingNumberIds) . "<br>";
                    $dql->andWhere("project.id IN (:fundedIds)");
                    $dqlParameters["fundedIds"] = $fundedIds;
                }
                $advancedFilter++;
            }
        }
        //////////////// EOF get Projects IDs with the form node filter ////////////////

        if ($searchProjectType) {
            $dql->andWhere("projectType.id = :projectTypeId");
            $dqlParameters["projectTypeId"] = $searchProjectType->getId();
            $advancedFilter++;
        }
        //echo "searchTitle=$searchTitle <br>";
        if ($searchTitle) {
            //exit('111');
            $dql->andWhere("LOWER(project.title) LIKE LOWER(:title)");
            $dqlParameters["title"] = "%" . $searchTitle . "%";
            $advancedFilter++;
        }
        if ($searchIrbNumber) {
            $dql->andWhere("LOWER(project.irbNumber) LIKE LOWER(:irbNumber) OR LOWER(project.iacucNumber) LIKE LOWER(:irbNumber)");
            $dqlParameters["irbNumber"] = "%" . $searchIrbNumber . "%";
            $advancedFilter++;
        }
        if ($fundingNumber) {
            $dql->andWhere("LOWER(project.fundedAccountNumber) LIKE LOWER(:fundedAccountNumber)");
            $dqlParameters["fundedAccountNumber"] = "%" . $fundingNumber . "%";
            $advancedFilter++;
        }
        if ($fundingType) {
            //echo "fundingType=" . $fundingType . "<br>";
            if ($fundingType == "Funded") {
                $dql->andWhere("project.funded = true");
            }
            if ($fundingType == "Non-Funded") {
                $dql->andWhere("project.funded = false OR project.funded IS NULL");
            }
            $advancedFilter++;
        }

        if ($humanTissue) {
            //echo "fundingType=" . $humanTissue . "<br>";
            if ($humanTissue == "Involves Human Tissue") {
                $dql->andWhere("project.involveHumanTissue = 'Yes'");
            }
            if ($humanTissue == "Does Not Involve Human Tissue") {
                $dql->andWhere("project.involveHumanTissue = 'No' OR project.involveHumanTissue IS NULL");
            }
            $advancedFilter++;
        }
        if ($exemptIrbApproval) {
            echo "exemptIrbApproval=" . $exemptIrbApproval . "<br>";
            $dql->leftJoin('project.exemptIrbApproval', 'exemptIrbApproval');
            if ($exemptIrbApproval == "exempt-from-irb-approval") {
                //$dql->andWhere("project.exemptIrbApproval = true OR project.exemptIrbApproval IS NULL");
                $dql->andWhere("exemptIrbApproval.name = 'Exempt' OR project.exemptIrbApproval IS NULL");
            }
            if ($exemptIrbApproval == "not-exempt-from-irb-approval") {
                //$dql->andWhere("project.exemptIrbApproval = false");
                $dql->andWhere("exemptIrbApproval.name = 'Not Exempt'");
            }
            $advancedFilter++;
        }


        if ($principalInvestigators && count($principalInvestigators) > 0) {
            //echo "testing where principalInvestigators <br>";
            $dql->andWhere("principalInvestigators.id IN (:principalInvestigators) OR principalIrbInvestigator.id IN (:principalInvestigators)");
            $principalInvestigatorsIdsArr = array();
            foreach ($principalInvestigators as $principalInvestigator) {
                $principalInvestigatorsIdsArr[] = $principalInvestigator->getId();
            }
            $dqlParameters["principalInvestigators"] = $principalInvestigatorsIdsArr; //implode(",",$principalInvestigatorsIdsArr);
        }

        //$associatedUsers
        if( $associatedUsers && count($associatedUsers) > 0 ) {

            $showAssCriterion =
            "principalInvestigators.id IN (:assUserIds) OR ".
            "principalIrbInvestigator.id IN (:assUserIds) OR ".
            "submitInvestigators.id IN (:assUserIds) OR ".
            "coInvestigators.id IN (:assUserIds) OR ".
            "pathologists.id IN (:assUserIds) OR ".
            "contacts.id IN (:assUserIds) OR ".
            "billingContact.id IN (:assUserIds) OR ".
            "submitter.id IN (:assUserIds)";

            $dql->andWhere($showAssCriterion);
            $dqlParameters["assUserIds"] = $associatedUsers;

            $advancedFilter++;
        }

        if ($submitter) {
            //echo "submitter=".$submitter->getId()."<br>";
            $dql->andWhere("submitter.id = :submitterId");
            $dqlParameters["submitterId"] = $submitter->getId();
            $advancedFilter++;
        }

        if ($reviewers && count($reviewers) > 0) {
            //echo "testing where reviewerIds <br>";
            //"principalInvestigators.id IN (:principalInvestigators)"
            $reviewersCriterion =
                "irbReviewer.id IN (:reviewerIds) OR " .
                "irbReviewerDelegate.id IN (:reviewerIds) OR " .

                "adminReviewer.id IN (:reviewerIds) OR " .
                "adminReviewerDelegate.id IN (:reviewerIds) OR " .

                "committeeReviewer.id IN (:reviewerIds) OR " .
                "committeeReviewerDelegate.id IN (:reviewerIds) OR " .

                "finalReviewer.id IN (:reviewerIds) OR " .
                "finalReviewerDelegate.id IN (:reviewerIds)";
            $dql->andWhere($reviewersCriterion);

            $reviewersIdsArr = array();
            foreach ($reviewers as $reviewer) {
                $reviewersIdsArr[] = $reviewer->getId();
            }

            $dqlParameters["reviewerIds"] = $reviewersIdsArr; //implode(",",$principalInvestigatorsIdsArr);
            $advancedFilter++;
        }

        if ($startDate) {
            //echo "startDate=" . $startDate->format('Y-m-d H:i:s') . "<br>";
            $dql->andWhere('project.createDate >= :startDate');
            $dqlParameters['startDate'] = $startDate->format('Y-m-d H:i:s');
            $advancedFilter++;
        }
        if ($endDate) {
            $endDate->modify('+1 day');
            //echo "endDate=" . $endDate->format('Y-m-d H:i:s') . "<br>";
            $dql->andWhere('project.createDate <= :endDate');
            $dqlParameters['endDate'] = $endDate->format('Y-m-d H:i:s');
            $advancedFilter++;
        }

        if ($fromExpectedCompletionDate) {
            $dql->andWhere('project.expectedCompletionDate >= :fromExpectedCompletionDate');
            $dqlParameters['fromExpectedCompletionDate'] = $fromExpectedCompletionDate->format('Y-m-d H:i:s');
            $advancedFilter++;
        }
        if ($toExpectedCompletionDate) {
            $toExpectedCompletionDate->modify('+1 day');
            $dql->andWhere('project.expectedCompletionDate <= :toExpectedCompletionDate');
            $dqlParameters['toExpectedCompletionDate'] = $toExpectedCompletionDate->format('Y-m-d H:i:s');
            $advancedFilter++;
        }

        if ($fromImplicitExpDate) {
            $dql->andWhere('project.implicitExpirationDate >= :fromImplicitExpirationDate');
            $dqlParameters['fromImplicitExpirationDate'] = $fromImplicitExpDate->format('Y-m-d H:i:s');
            $advancedFilter++;
        }
        if ($toImplicitExpDate) {
            $toImplicitExpDate->modify('+1 day');
            $dql->andWhere('project.implicitExpirationDate <= :toImplicitExpirationDate');
            $dqlParameters['toImplicitExpirationDate'] = $toImplicitExpDate->format('Y-m-d H:i:s');
            $advancedFilter++;
        }

        //In the project list, show Draft projects only to the project's requester and to admins
        if ($transresUtil->isAdminOrPrimaryReviewer() === false) {
            //echo "testing where project.state != 'draft' <br>";
            //show projects where Iam requester
            $showOnlyMyProjectsCriterion = $this->getProjectWhereIamRequester();

            //OR show projects with state!= draft
            $allowedStatesCriterion = "project.state != 'draft'";

            $limitedProjectsCriterion = "(" . $showOnlyMyProjectsCriterion . ")" . " OR " . $allowedStatesCriterion;

            $dql->andWhere($limitedProjectsCriterion);
            $dqlParameters["userId"] = $user->getId();
        }

//        /////////////////////// Limit access for non-admin, non-reviewer, non-executive users /////////////////////////////
//        //Non admin, Primary Reviewers, Reviewers and Executive can see all projects.
//        // All other users can view only their projects
//        // (where they are requesters: PI, Pathologists Involved, Co-Investigators, Contacts, Billing Contacts or reviewers)
//        if (
//            $transresUtil->isAdminOrPrimaryReviewerOrExecutive() //index
//            || $this->isGranted('ROLE_TRANSRES_TECHNICIAN')
//            || $transresUtil->hasReviewerRoles()
//        ) {
//            $showOnlyMyProjects = false;
//        } else {
//            //this will hide projects "Project Requests Pending My Review" for added reviewers directly to the project without reviewer role
//            $showOnlyMyProjects = true;
//        }
//        //echo "showOnlyMyProjects=$showOnlyMyProjects <br>";
//
//        if ($showOnlyMyProjects || $routeName == "translationalresearch_my_project_index") {
//            $showOnlyMyProjectsCriterion = $this->getProjectWhereIamRequester();
//            $dql->andWhere($showOnlyMyProjectsCriterion);
//            $dqlParameters["userId"] = $user->getId();
//            $title = "My Project Requests, where I am a Requester";
//        }
//        /////////////////////// EOF Limit access for non-admin, non-reviewer, non-executive users /////////////////////////////

        if ($routeName == "translationalresearch_my_request_project_index") {
            $myRequestProjectsCriterion = "submitter.id = :userId";
            $dql->andWhere($myRequestProjectsCriterion);

            $dqlParameters["userId"] = $user->getId();
            $title = "Projects I Personally Requested"; //"My Project Requests, Where I am a Requester";
        }

        if ($routeName == "translationalresearch_my_request_project_draft_index") {
            $myRequestProjectsCriterion = $this->getProjectWhereIamRequester();
            $dql->andWhere($myRequestProjectsCriterion . " AND project.state != 'draft'");

            $dqlParameters["userId"] = $user->getId();
            $title = "Draft Projects, where I am a Requester"; //"My Project Requests, Where I am a Requester";
        }

        //Projects I have reviewed (History):
        // only shows projects previously reviewed by the reviewer, not any of the projects currently pending this user's review
        if( $routeName == "translationalresearch_my_reviewed_project_index" ) {
            //echo "testing where translationalresearch_my_reviewed_project_index <br>";
            //$myReviewProjectsCriterion = $this->getProjectWhereIamReviewer();

            //rely on decision: problem that imported projects do not have decision set
//            $myReviewedProjectsCriterion =
//                "( (irbReviewer.id = :userId OR irbReviewerDelegate.id = :userId) AND (irbReviews.decision='approved' OR irbReviews.decision='rejected') )".
//                " OR ".
//                "((adminReviewer.id = :userId OR adminReviewerDelegate.id = :userId) AND (adminReviews.decision='approved' OR adminReviews.decision='rejected') )".
//                " OR ".
//                "((committeeReviewer.id = :userId OR committeeReviewerDelegate.id = :userId) AND (committeeReviews.decision='approved' OR committeeReviews.decision='rejected') )".
//                " OR ".
//                "((finalReviewer.id = :userId OR finalReviewerDelegate.id = :userId) AND (finalReviews.decision='approved' OR finalReviews.decision='rejected') )"
//            ;

            //rely on state after reviewer's state
            $myReviewedProjectsCriterion =
                "( (irbReviewer.id = :userId OR irbReviewerDelegate.id = :userId) AND (".
                "project.state='irb_rejected' OR project.state='admin_review' OR ".
                "project.state='admin_rejected' OR project.state='committee_review' OR ".
                "project.state='committee_rejected' OR project.state='final_review' OR ".
                "project.state='final_review' OR project.state='final_approved' OR ".
                "project.state='final_rejected' OR project.state='closed'".
                "))".
                " OR ".
                "((adminReviewer.id = :userId OR adminReviewerDelegate.id = :userId) AND (".
                "project.state='admin_rejected' OR project.state='committee_review' OR ".
                "project.state='committee_rejected' OR project.state='final_review' OR ".
                "project.state='final_review' OR project.state='final_approved' OR ".
                "project.state='final_rejected' OR project.state='closed'".
                "))".
                " OR ".
                "((committeeReviewer.id = :userId OR committeeReviewerDelegate.id = :userId) AND (".
                "project.state='committee_rejected' OR project.state='final_review' OR ".
                "project.state='final_review' OR project.state='final_approved' OR ".
                "project.state='final_rejected' OR project.state='closed'".
                "))".
                " OR ".
                "((finalReviewer.id = :userId OR finalReviewerDelegate.id = :userId) AND (".
                "project.state='final_review' OR project.state='final_approved' OR ".
                "project.state='final_rejected' OR project.state='closed'".
                "))"
            ;

            $dql->andWhere($myReviewedProjectsCriterion);

            $dqlParameters["userId"] = $user->getId();
            $title = "Projects I Have Reviewed (History)";

            $showOnlyMyProjects = false;
        }

        if( $routeName == "translationalresearch_my_pending_review_project_index" ) {
            //echo "testing where translationalresearch_my_pending_review_project_index <br>";
            //Pending my review: I'm a reviewer and project's review where I'm a reviewer has decision = NULL ("Pending Review")
            //TODO: should filter current state, and corresponding current state review:decision is NULL (pending) and reviewer is logged in user?
//            $myPendingProjectsCriterion =
//                "((irbReviewer.id = :userId OR irbReviewerDelegate.id = :userId) AND irbReviews.decision IS NULL)".
//                " OR ".
//                "((adminReviewer.id = :userId OR adminReviewerDelegate.id = :userId) AND adminReviews.decision IS NULL)".
//                " OR ".
//                "((committeeReviewer.id = :userId OR committeeReviewerDelegate.id = :userId) AND committeeReviews.decision IS NULL)".
//                " OR ".
//                "((finalReviewer.id = :userId OR finalReviewerDelegate.id = :userId) AND finalReviews.decision IS NULL)"
//                ." AND project.state LIKE '%_review'"
//                //." AND project.state LIKE 'irb_review'"
//                //." AND (project.state=irbReviews.status OR project.state=adminReviews.status OR project.state=committeeReviews.status OR project.state=finalReviews.status)"
//            ;
//            $myPendingProjectsCriterion =
//                "((irbReviewer.id = :userId OR irbReviewerDelegate.id = :userId) AND project.state=irbReviews.status)".
//                " OR ".
//                "((adminReviewer.id = :userId OR adminReviewerDelegate.id = :userId) AND project.state=adminReviews.status)".
//                " OR ".
//                "((committeeReviewer.id = :userId OR committeeReviewerDelegate.id = :userId) AND project.state=committeeReviews.status)".
//                " OR ".
//                "((finalReviewer.id = :userId OR finalReviewerDelegate.id = :userId) AND project.state=finalReviews.status)"
//                ." AND project.state LIKE '%_review'"
//                //." AND (project.state=irbReviews.status OR project.state=adminReviews.status OR project.state=committeeReviews.status OR project.state=finalReviews.status)"
//            ;
            //echo "routeName=$routeName <br>";
            $myPendingProjectsCriterion =
                "((irbReviewer.id = :userId OR irbReviewerDelegate.id = :userId) AND project.state='irb_review')".
                " OR ".
                "((adminReviewer.id = :userId OR adminReviewerDelegate.id = :userId) AND project.state='admin_review')".
                " OR ".
                "((committeeReviewer.id = :userId OR committeeReviewerDelegate.id = :userId) AND project.state='committee_review')".
                " OR ".
                "((finalReviewer.id = :userId OR finalReviewerDelegate.id = :userId) AND project.state='final_review')"
                ." AND project.state LIKE '%_review'"
                //." AND (project.state=irbReviews.status OR project.state=adminReviews.status OR project.state=committeeReviews.status OR project.state=finalReviews.status)"
            ;

            //testing
            //TODO: why added reviewer does not see the project on the project to review page
            //$myPendingProjectsCriterion =
            //    "((adminReviewer.id = :userId OR adminReviewerDelegate.id = :userId) AND project.state='admin_review')"
            //;

            $dql->andWhere($myPendingProjectsCriterion);
            $dqlParameters["userId"] = $user->getId();

            $title = "Project Requests Pending My Review";

            $showOnlyMyProjects = false;
        }

//        if( $routeName == "translationalresearch_pending_review_project_index" ) {
//            //echo "testing where translationalresearch_pending_review_project_index <br>";
//            //echo "routeName=$routeName <br>";
//            //all statuses not equal to Closed/Canceled/Draft/Approved
//            $pendingProjectsCriterion =
//                "project.state LIKE '%_review' OR project.state LIKE '%_missinginfo'"
//            ;
//
//            $dql->andWhere($pendingProjectsCriterion);
//
//            $title = "Project Requests Pending Review";
//
//            $showOnlyMyProjects = false;
//        }

        if( $routeName == "translationalresearch_my_missinginfo_review_project_index" ) {
            //echo "testing where translationalresearch_my_missinginfo_review_project_index <br>";
            $myPendingProjectsCriterion =
                "((irbReviewer.id = :userId OR irbReviewerDelegate.id = :userId) AND project.state='irb_missinginfo')".
                " OR ".
                "((adminReviewer.id = :userId OR adminReviewerDelegate.id = :userId) AND project.state='admin_missinginfo')"
            ;

            $dql->andWhere($myPendingProjectsCriterion);

            $dqlParameters["userId"] = $user->getId();
            $title = "Projects Awaiting Additional Info To Be Reviewed";

            $showOnlyMyProjects = false;
        }

        //set title
        if( $routeName == "translationalresearch_active_expired_project_index" ) {
            $title = "Active Projects with Expired $humanName";
        }
        if( $routeName == "translationalresearch_active_expired_soon_project_index" ) {
            $title = "Active Projects with $humanName Expiring Soon";
        }
        if( $routeName == "translationalresearch_active_non_funded_over_budget_project_index" ) {
            $title = "Active Non-Funded Projects Over Budget or With No Budget";
        }
        if( $routeName == "translationalresearch_approved_funded_project_index" ) {
            $title = "Approved Funded Projects";
        }
        if( $routeName == "translationalresearch_approved_non_funded_project_index" ) {
            $title = "Approved Non-Funded Projects";
        }

        /////////////////////// Limit access for non-admin, non-reviewer, non-executive users /////////////////////////////
//        //Non admin, Primary Reviewers, Reviewers and Executive can see all projects.
//        // All other users can view only their projects
//        // (where they are requesters: PI, Pathologists Involved, Co-Investigators, Contacts, Billing Contacts or reviewers)
//        if (
//            $transresUtil->isAdminOrPrimaryReviewerOrExecutive() //index
//            || $this->isGranted('ROLE_TRANSRES_TECHNICIAN')
//            || $transresUtil->hasReviewerRoles()
//        ) {
//            $showOnlyMyProjects = false;
//        } else {
//            //this will hide projects "Project Requests Pending My Review" for added reviewers directly to the project without reviewer role
//            $showOnlyMyProjects = true;
//        }
//        //echo "showOnlyMyProjects=$showOnlyMyProjects <br>";

        if( $showOnlyMyProjects || $routeName == "translationalresearch_my_project_index" ) {
            $showOnlyMyProjectsCriterion = $this->getProjectWhereIamRequester();
            $dql->andWhere($showOnlyMyProjectsCriterion);
            $dqlParameters["userId"] = $user->getId();
            if( $routeName == "translationalresearch_my_project_index" ) {
                $title = "My Project Requests, where I am a Requester";
            }
        }
        //////////////////// EOF Start Filter ////////////////////

        $dql->orderBy('project.createDate', 'DESC');

        //echo "showMatchingAndTotal=".$showMatchingAndTotal."<br>";
//        if( $showMatchingAndTotal == "WithTotal" ) {
//            $withMatching = true; //slower 7.5 sec
//            $advancedFilter++;
//        } else {
//            $withMatching = false; //twice faster 3.5 sec
//        }

        $limit = 10;
        $query = $dql->getQuery(); //$query = $em->createQuery($dql);

//        if( $withMatching ) {
//            $query2 = $em->createQuery($dql);
//        }

        if( count($dqlParameters) > 0 ) {
            $query->setParameters($dqlParameters);

//            if( $withMatching ) {
//                $query2->setParameters($dqlParameters);
//            }
        }

        //echo "query=".$query->getSql()."<br>";

        $paginationParams = array(
            'defaultSortFieldName' => 'project.id',
            'defaultSortDirection' => 'DESC',
            'wrap-queries' => true
        );

        $paginator  = $this->container->get('knp_paginator');
        $projects = $paginator->paginate(
            $query,
            $request->query->get('page', 1),   /*page number*/
            $limit,                            /*limit per page*/
            $paginationParams
        );

        $allProjectIdsArr = array();
        //if( $withMatching ) {
            //$allProjects = $query2->getResult();
            //$allGlobalProjects = $em->getRepository('AppTranslationalResearchBundle:Project')->findAll();
            //$title = $title . " (Matching " . count($allProjects) . ", Total " . count($allGlobalProjects) . ")";
            $allProjectIdsArr = $transresUtil->getProjectIdsArrByDqlParameters($dql,$dqlParameters);
            $allGlobalProjects = $transresUtil->getTotalProjectCount();
            $title = $title . " (Matching " . count($allProjectIdsArr) . ", Total " . $allGlobalProjects . ")";
        //}
//        if( count($allProjects) > 0 ) {
//            //$allProjects = $projects->getTotalItemCount();
//            $pageNumber = $projects->getCurrentPageNumber();
//            $items = $projects->getItems();
//            $startPageItems = (intval($pageNumber) - 1) * intval($limit) + 1;
//            $endPageItems = intval($startPageItems) + count($items) - 1;
//            //echo "pageNumber=$pageNumber; items=".count($items)."; startPageItems=".$startPageItems."; endPageItems=".$endPageItems."<br>";
//            $title = $title . " (" . $startPageItems . " of " . $endPageItems . ", Total " . count($allProjects) . ")";
//        } else {
//            $title = $title . " (Total " . count($allProjects) . ")";
//        }

        //return array('filterError' => true,'title' => "Test Performance",); //test 18(295ms) queries vs 800(431ms)

        $eventObjectType = $em->getRepository(EventObjectTypeList::class)->findOneByName("Project");
        if( $eventObjectType ) {
            $eventObjectTypeId = $eventObjectType->getId();
        } else {
            $eventObjectTypeId = null;
        }

//        return array(
//            'filterError' => true,
//            'title' => "Test Performance",
//            'filterform' => $filterform->createView()
//        ); //test 18 queries vs 800

        //echo "before ... <br>";
        return array(
            //'projectsTableDisable' => true, //testing
            //'filterDisable' => true, //testing
            //'filterError' => true, //testing
            'projects' => $projects,
            'allProjectIdsArr' => $allProjectIdsArr,
            'title' => $title,
            'filterform' => $filterform->createView(),
            'eventObjectTypeId' => $eventObjectTypeId,
            'advancedFilter' => $advancedFilter
        );
    }

    public function getProjectWhereIamRequester() {
        $showOnlyMyProjectsCriterion =
            "principalInvestigators.id = :userId OR ".
            "principalIrbInvestigator.id = :userId OR ".
            "submitInvestigators.id = :userId OR ".
            "coInvestigators.id = :userId OR ".
            "pathologists.id = :userId OR ".
            "contacts.id = :userId OR ".
            "billingContact.id = :userId OR ".
            "submitter.id = :userId"
        ;
        return $showOnlyMyProjectsCriterion;
    }
    public function getProjectWhereIamReviewer() {
        $criterion =
            "irbReviewer.id = :userId OR ".
            "irbReviewerDelegate.id = :userId OR ".

            "adminReviewer.id = :userId OR ".
            "adminReviewerDelegate.id = :userId OR ".

            "committeeReviewer.id = :userId OR ".
            "committeeReviewerDelegate.id = :userId OR ".

            "finalReviewer.id = :userId OR ".
            "finalReviewerDelegate.id = :userId"
        ;

        return $criterion;
    }


    /**
     * Select new project specialty
     */
    #[Route(path: '/project/select-new-project-type', name: 'translationalresearch_project_new_selector', methods: ['GET', 'POST'])]
    #[Template('AppTranslationalResearchBundle/Project/new-project-selector.html.twig')]
    public function newProjectSelectorAction(Request $request)
    {
        $transresUtil = $this->container->get('transres_util');
        $userTenantUtil = $this->container->get('user_tenant_utility');

        $specialties = $transresUtil->getTransResProjectSpecialties(false);

        $collDivs = $transresUtil->getTransResCollaborationDivs();

        $collDivsFiltered = array();

        //Remove specialties with enableNewProjectOnSelector is false
        $specialtiesFiltered = array();
        foreach($specialties as $specialty) {
            //$fieldName, $project=null, $projectSpecialty=null
            if( $transresUtil->getTransresSiteProjectParameter('enableNewProjectOnSelector',null,$specialty) === true ) {
                $specialtiesFiltered[] = $specialty;
            }

            $specialtyAbbr = $specialty->getAbbreviation();
            $specialtyAbbr = strtolower($specialtyAbbr);
            $collDivsFiltered[$specialtyAbbr] = null;

            foreach($collDivs as $collDiv) {
                $collDivUrlSlug = $collDiv->getUrlSlug();
                //echo "specialtyAbbr=$specialtyAbbr, collDivUrlSlug=$collDivUrlSlug <br>";
                if( $specialtyAbbr && $collDivUrlSlug ) {
                    $collDivUrlSlug = strtolower($collDivUrlSlug);
                    //echo "specialtyAbbr=[$specialtyAbbr], collDivUrlSlug=[$collDivUrlSlug] <br>";
                    if( $specialtyAbbr == $collDivUrlSlug ) {
                        //echo "!!! Match $specialtyAbbr=>$collDivUrlSlug<br>";
                        $collDivsFiltered[$specialtyAbbr] = $collDivUrlSlug; //use urlSlug
                    }
                }
            }
        }

        //dump($collDivsFiltered);exit('111');
        $reverse = false;
        if( $userTenantUtil->isHubServer() ) {
            $reverse = true;
        }
        $requesterGroups = $transresUtil->getTransResRequesterGroups($reverse);

        //check if user does not have ROLE_TRANSRES_REQUESTER and specialty role
        //$transresUtil->addMinimumRolesToCreateProject();

        //add a support email address on the bottom of this page to contact TRP support if having technical issues
        $trpAdminEmail = $transresUtil->getTransresSiteProjectParameter('fromEmail',null);
        if( !$trpAdminEmail ) {
            $userSecUtil = $this->container->get('user_security_utility');
            $trpAdminEmail = $userSecUtil->getSiteSettingParameter('siteEmail');

            $trpAdminEmail = '<a href="mailto:'.$trpAdminEmail.'">'.$trpAdminEmail.'</a>';
        }
        $supportNote = NULL;
        if( $trpAdminEmail ) {
            $supportNote = "If you encounter any technical issues, please email TRP support $trpAdminEmail";
        }

        return array(
            'specialties' => $specialtiesFiltered,
            'collDivsFiltered' => $collDivsFiltered,
            'requesterGroups' => $requesterGroups,
            'title' => "New Project Request",
            'supportNote' => $supportNote
        );
    }

    /**
     * Select new project specialty
     * "/project/new/{specialtyStr}/{requesterGroup}"
     */
    #[Route(path: '/project/new/{specialtyStr}', name: 'translationalresearch_project_new', methods: ['GET', 'POST'])]
    #[Template('AppTranslationalResearchBundle/Project/new.html.twig')]
    public function newProjectAction(Request $request, $specialtyStr=null)
    {

        if( !$specialtyStr ) {
            return $this->redirect($this->generateUrl('translationalresearch_project_new_selector'));
        }

        //$specialtyStr = $request->query->get('specialty');
        $requesterGroupStr = $request->query->get('requester-group');
        $collDivStr = $request->query->get('collaborating-division');

        //echo "specialtyStr=$specialtyStr, requesterGroupStr=$requesterGroupStr, collDivStr=$collDivStr <br>";
        //exit('111');

//        if( !$requesterGroupStr ) {
//            return $this->redirect($this->generateUrl('translationalresearch_project_new_selector'));
//        }

        $transresPermissionUtil = $this->container->get('transres_permission_util');
        $transresUtil = $this->container->get('transres_util');
        $user = $this->getUser();
        $em = $this->getDoctrine()->getManager();

        //testing
//        $roles = $user->getRoles();
//        foreach( $roles as $role ) {
//            echo "role=$role <br>";
//        }
//        if( $this->isGranted("ROLE_TRANSRES_REQUESTER_COVID19") ) {
//            echo "covid role is OK <br>";
//        }
//        if( $this->isGranted("ROLE_TRANSRES_REQUESTER_APCP") ) {
//            echo "apcp role is OK <br>";
//        }

        //$specialty is a url prefix (i.e. "new-ap-cp-project")
        $specialty = $transresUtil->getSpecialtyObject($specialtyStr);

        if( false === $transresPermissionUtil->hasProjectPermission('create',null,$specialty) ) {
            //exit('NOT GRANTED: new project '.$specialtyStr);
            return $this->redirect($this->generateUrl('translationalresearch-nopermission'));
        }

        //check enableNewProjectAccessPage
        if( $transresUtil->getTransresSiteProjectParameter('enableNewProjectAccessPage',null,$specialty) !== true ) {
            //exit('disabled specialty='.$specialtyStr);
            $adminEmailsStr = $transresUtil->getAdminEmailsStrBySpecialty(null,$specialty,false); //new project
            //exit('adminEmailsStr='.$adminEmailsStr);
            $additionalMessage = "This project request type is currently not active. ".
                "Please select a different project request type or contact $adminEmailsStr";
            //exit('additionalMessage='.$additionalMessage);
            return $this->redirect($this->generateUrl('translationalresearch-nopermission',array('additionalMessage'=>$additionalMessage)));
        }

        //check if user does not have ROLE_TRANSRES_REQUESTER and specialty role
        $transresUtil->addMinimumRolesToCreateProject($specialty);

        $formnode = false;
        $cycle = "new";

        if( $transresUtil->isUserAllowedSpecialtyObject($specialty) === false ) {
            $this->addFlash(
                'warning',
                "You don't have a permission to access the $specialty project specialty"
            );
            //exit('NO SPECIALTY: new project '.$specialtyStr);
            return $this->redirect($this->generateUrl('translationalresearch-nopermission'));
        }

        $testing = false;
        //$testing = true;

        $project = $this->createProjectEntity($user,null);

        $project->setProjectSpecialty($specialty);

        //Set requester group
        if( $requesterGroupStr ) {
            $requesterGroupObject = $transresUtil->getRequesterGroupObject($requesterGroupStr);
            if( $requesterGroupObject ) {
                $project->setRequesterGroup($requesterGroupObject);
            }
        }

        if( $collDivStr ) {
            $collDivObject = $transresUtil->getCollaborationDivObject($collDivStr);
            if( $collDivObject ) {
                $project->addCollDiv($collDivObject);

                //On CSP set the radio button for the “Will this project involve human tissue?” to “No” by default on load
                //echo "collDivStr=".strtolower($collDivStr)."<br>";
                if( strtolower($collDivStr) == "csp" ) {
                    //involveHumanTissue
                    //echo "set involveHumanTissue to No<br>";
                    $project->setInvolveHumanTissue("No");
                }
            }
        }

        //set default exempt
        //process.py script: replaced namespace by ::class: ['AppTranslationalResearchBundle:IrbApprovalTypeList'] by [IrbApprovalTypeList::class]
        $exemptIrbApproval = $em->getRepository(IrbApprovalTypeList::class)->findOneByName("Not Exempt");
        $project->setExemptIrbApproval($exemptIrbApproval);
        //process.py script: replaced namespace by ::class: ['AppTranslationalResearchBundle:IrbApprovalTypeList'] by [IrbApprovalTypeList::class]
        $exemptIACUCApproval = $em->getRepository(IrbApprovalTypeList::class)->findOneByName("Exempt");
        $project->setExemptIACUCApproval($exemptIACUCApproval);

        //new: add all default reviewers
        $transresUtil->addDefaultStateReviewers($project);

        if( $cycle == 'new' && $project->getProjectSpecialtyStr() == 'MISI' ) {
            //exit('new MISI');
            $project->setFunded(true);
            $exemptIrbApproval = $em->getRepository(IrbApprovalTypeList::class)->findOneByName("Exempt");
            $project->setExemptIrbApproval($exemptIrbApproval);
            $project->setInvolveHumanTissue('No'); //involveHumanTissue (Not working?)
        }

        $form = $this->createProjectForm($project,$cycle,$request); //new

        $messageTypeId = true;//testing
        $formnodetrigger = 1;
        if( $messageTypeId ) {
            $formnodetrigger = 0; //build formnodes from top to bottom
        }

        //top message category id
        $formnodeTopHolderId = null;
        //$categoryStr = "Pathology Call Log Entry";
        //$messageCategory = $em->getRepository('AppOrderformBundle:MessageCategory')->findOneByName($categoryStr);
        $messageCategory = $project->getMessageCategory();
        if( $messageCategory ) {
            $formnodeTopHolderId = $messageCategory->getId();
        }

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            //$services = $project->getTissueProcessingServices();
            //echo "services=".count($services)."<br>";
            //exit("Project submitted");

            $startProjectReview = false;

            //exit("clickedButton=".$form->getClickedButton()->getName());
            //echo "clickedButton=".$form->getClickedButton()->getName()."<br>";

            //new
            if ($form->getClickedButton() && 'saveAsDraft' === $form->getClickedButton()->getName()) {
                //Save Project as Draft => state='draft'
                $project->setState('draft');
            }

            //new
            if ($form->getClickedButton() && 'submitIrbReview' === $form->getClickedButton()->getName()) {
                //Submit to IRB review
                $project->setState('irb_review');
                $startProjectReview = true;
            }

            $transresUtil->assignMinimumProjectRoles($project); //new

            $project->autoPopulateApprovedProjectBudget(); //new
            $project->calculateAndSetImplicitExpirationDate();
            $project->processShowHideFields();

            //set ExpectedExprDate only when project is final approved
            //$transresUtil->calculateAndSetProjectExpectedExprDate($project); //new

            $em->getRepository(Document::class)->processDocuments($project,"document");
            $em->getRepository(Document::class)->processDocuments($project,"irbApprovalLetter");
            $em->getRepository(Document::class)->processDocuments($project,"humanTissueForm");

            if( !$testing ) {
                $em->persist($project);
                $em->flush();

                $project->generateOid();
                $em->flush();
            }

            //generate project PDF
            if( !$testing ) {
                $transresPdfUtil = $this->container->get('transres_pdf_generator');
                $transresPdfUtil->generateAndSaveProjectPdf($project,$user,$request); //new
                $em->flush();
            }

            //process form nodes
            if( $formnode ) {
                $formNodeUtil = $this->container->get('user_formnode_utility');
                $formNodeUtil->processFormNodes($request,$project->getMessageCategory(),$project,$testing); //testing
                $transresUtil->copyFormNodeFieldsToProject($project);
            }

            //Draft message:
            //Your project request draft has been saved and assigned ID [id].
            // In order to initiate the review of your project request,
            // please make sure to complete your submission once your draft is ready.
            // Project requests with a “draft” status will not be reviewed until they are finalized and submitted.
            $emailSubject = "Your project request draft has been saved and assigned ID ".$project->getOid();
            $msg = "Your project request draft has been saved and assigned ID ".$project->getOid().".".
                " In order to initiate the review of your project request,".
                " please make sure to complete your submission once your draft is ready.".
                " Project requests with a 'draft' status will not be reviewed until they are finalized and submitted.";
            if( $startProjectReview ) {
                //$msg = "Project request ".$project->getOid()." has been successfully created and sent to the status '$label'";
                //Thank you for your submission! Your project request has been assigned an ID
                // of "[ID]" and will be reviewed. You should receive notifications of approval
                // status updates by email. You can also log back in to this site to review
                // the status of your project request, submit your subsequent work requests
                // (upon project request approval), and see your associated invoices (if any) as well.
                $emailSubject = "Your project request has been received and assigned the following ID: ".$project->getOid();
//                $msg = "Thank you for your submission! Your project request has been received and assigned the following ID of ".$project->getOid().
//                    " and will be reviewed.".
//                    " You should receive notifications of approval status updates by email.".
//                    " You can also log back in to this site to review the status of your project request, ".
//                    "submit your subsequent work requests (upon project request approval), and see your associated invoices (if any) as well.";
                //Thank you for your submission! Your project request has been received and assigned the ID of APCP28.
                // It will be reviewed and you should receive notifications regarding its approval status by email.
                // You can also log back into this web site to review the status of your project request,
                // submit your subsequent work requests (upon project request approval),
                // and see your associated invoices (if any) as well.
//                $msg = "Thank you for your submission! Your project request '".$project->getTitle()."' has been received and assigned the following ID of ".$project->getOid().".".
//                    " It will be reviewed and you should receive notifications regarding its approval status by email.".
//                    " You can also log back into the Translational Research web site to review the status of your project request, ".
//                    "submit your subsequent work requests (upon project request approval), and see your associated invoices (if any) as well.";
                //You can also log back into the website for the [Center for Translational Pathology] to review
                $msg = "Thank you for your submission! Your project request '".$project->getTitle()."' has been received and assigned the following ID of ".$project->getOid().".".
                    " It will be reviewed and you should receive notifications regarding its approval status by email.".
                    " You can also log back into the website for the ".$transresUtil->getBusinessEntityName()." to review the status of your project request, ".
                    "submit your subsequent work requests (upon project request approval), and see your associated invoices (if any) as well.";

            }

            if( $testing ) {
                exit('form is submitted and finished, msg='.$msg);
            }

            $this->addFlash(
                'notice',
                $msg
            );

            if( $startProjectReview ) {
                ///////////// send confirmation email to submitter and contact only ///////////////
                $break = "<br>";
                //get project url
                $projectUrl = $transresUtil->getProjectShowUrl($project);
                $emailBody = $msg . $break.$break. "To view this project request, please visit the link below:".$break.$projectUrl;
                //$transresUtil->sendNotificationEmails($project,null,$emailSubject,$emailBody,$testing);

                $emailUtil = $this->container->get('user_mailer_utility');
                $senderEmail = $transresUtil->getTransresSiteProjectParameter('fromEmail',$project);
                $requesterEmails = $transresUtil->getRequesterMiniEmails($project);
                $adminsCcs = $transresUtil->getTransResAdminEmails($project,true,true); //new project after save
                //                    $emails, $subject, $message, $ccs=null, $fromEmail=null
                $emailUtil->sendEmail($requesterEmails,$emailSubject,$emailBody,$adminsCcs,$senderEmail);
                ///////////// EOF send confirmation email to submitter and contact only ///////////////

                $emailResult = $transresUtil->sendTransitionEmail($project,"draft",$testing);
                $msg = $msg . "<br><br>" . $emailResult;

                //Send Notification emails for projects involving Computational Pathology or a request for a bioinformatician
                if( $project->sendComputationalEmail() ) {
                    $compEmailRes = $transresUtil->sendComputationalEmail($project); //new page
                    $msg = $msg . "<br><br>" . $compEmailRes;
                    $this->addFlash(
                        'notice',
                        "Notification emails for projects involving Computational Pathology".
                        " or a request for a bioinformatician".
                        " have been sent: " . $compEmailRes
                    );
                }
            }

            $eventType = "Project Created";
            $transresUtil->setEventLog($project,$eventType,$msg,$testing);

            return $this->redirectToRoute('translationalresearch_project_show', array('id' => $project->getId()));
        }

        $templateParams = array(
            'project' => $project,
            'form' => $form->createView(),
            'cycle' => $cycle,
            'title' => $specialty->getName()." Project Request",
            'formnodetrigger' => $formnodetrigger,
            'formnodeTopHolderId' => $formnodeTopHolderId
        );

        if( $cycle == 'new' && $project->getProjectSpecialtyStr() == 'MISI' ) {
            return $this->render('AppTranslationalResearchBundle/Project/new-misi.html.twig', $templateParams);
        }

        return $templateParams;
//        return array(
//            'project' => $project,
//            'form' => $form->createView(),
//            'cycle' => $cycle,
//            'title' => $specialty->getName()." Project Request",
//            'formnodetrigger' => $formnodetrigger,
//            'formnodeTopHolderId' => $formnodeTopHolderId
//        );
    }


    /**
     * Get Project Edit page
     * Originally edit form generates a new entity Project with new id and same oid.
     */
    #[Route(path: '/project/edit/{id}', name: 'translationalresearch_project_edit', methods: ['GET', 'POST'])]
    #[Template('AppTranslationalResearchBundle/Project/edit.html.twig')]
    public function editAction(Request $request, Project $project)
    {
        $transresPermissionUtil = $this->container->get('transres_permission_util');

        if( false === $transresPermissionUtil->hasProjectPermission("update",$project) ) {
            return $this->redirect($this->generateUrl('translationalresearch-nopermission'));
        }

        $transresUtil = $this->container->get('transres_util');

        //$userSecUtil = $this->container->get('user_security_utility');
        $user = $this->getUser();
        $em = $this->getDoctrine()->getManager();

        $formnode = false;
        $cycle = "edit";
        $formtype = "translationalresearch-project";

        $class = new \ReflectionClass($project);
        $className = $class->getShortName();          //ObjectTypeText
        $classNamespace = $class->getNamespaceName(); //App\UserdirectoryBundle\Entity

        $testing = false;
        //$testing = true;

        $project = $this->createProjectEntity($user,$project);

        ///////////// get originals /////////////
        $originalProjectSpecialty = $project->getProjectSpecialty();
        $originalApprovedProjectBudget = $project->getApprovedProjectBudget();
        $originalNoBudgetLimit = $project->getNoBudgetLimit();
        //$originalState = $project->getState();
        //$originalExpDate = $project->getExpectedExpirationDate();

        //IRB Reviews
        $originalIrbReviews = new ArrayCollection();
        foreach ($project->getIrbReviews() as $review) {
            $originalIrbReviews->add($review);
        }
        //Admin Reviews
        $originalAdminReviews = new ArrayCollection();
        foreach ($project->getAdminReviews() as $review) {
            $originalAdminReviews->add($review);
        }
        //Committee Reviews
        $originalCommitteeReviews = new ArrayCollection();
        foreach ($project->getCommitteeReviews() as $review) {
            $originalCommitteeReviews->add($review);
        }
        //Final Reviews
        $originalFinalReviews = new ArrayCollection();
        foreach ($project->getFinalReviews() as $review) {
            $originalFinalReviews->add($review);
        }
        //Project Goals
        $originalProjectGoals = new ArrayCollection();
        foreach ($project->getProjectGoals() as $projectGoal) {
            $originalProjectGoals->add($projectGoal);
        }
        ///////////// EOF get originals /////////////

        $form = $this->createProjectForm($project,$cycle,$request); //edit

        $messageTypeId = true;//testing
        $formnodetrigger = 1;
        if( $messageTypeId ) {
            $formnodetrigger = 0; //build formnodes from top to bottom
        }

        //top message category id
        $formnodeTopHolderId = null;
        //$categoryStr = "Pathology Call Log Entry";
        $messageCategory = $project->getMessageCategory();
        if( $messageCategory ) {
            $formnodeTopHolderId = $messageCategory->getId();
        }

        $form->handleRequest($request);

//        //"closed" -> Any except "canceled" => check exp date (do it in the JS transresValidateProjectForm)
//        $currentState = $project->getState();
//        $currentExpDate = $project->getExpectedExpirationDate();
//        if( $originalState != $currentState ) {
//            if( $originalExpDate == $currentExpDate ) {
//                if ($originalState == "closed" && $currentState != "canceled") {
//                    //if $currentExpDate is equal or older than (today’s date + 7 days)
//                    $plusSevenDaysDate = new \DateTime('+ 7 days');
//                    if( $currentExpDate > $plusSevenDaysDate ) {
//                        //Please update the expected expiration date to a future date.
//                        $form->get('expectedExpirationDate')->addError(new FormError('Please update the expected expiration date to a future date.'));
//                    }
//                }
//            }
//        }

        if ($form->isSubmitted() && $form->isValid()) {

            //exit("Project update submitted"); //testing

            $project->setUpdateUser($user);
            $project->setUpdateDate();
            $project->calculateAndSetImplicitExpirationDate(); //edit
            $project->processShowHideFields();

            $startProjectReview = false;

            $originalStateStr = $project->getState();
            $originalStateLabel = $transresUtil->getStateLabelByName($originalStateStr);

            //if project state changed from 'closed' => generate change state request
            //show reactivation confirmation modal before project edit submit => change status back to the original state

            $msg = "Project request " . $project->getOid() . " has been successfully updated";

            //////////// remove the relationship between the review and the project ////////////
            $transresUtil->removeReviewsFromProject($project, $originalIrbReviews, "IrbReview");
            $transresUtil->removeReviewsFromProject($project, $originalAdminReviews, "AdminReview");
            $transresUtil->removeReviewsFromProject($project, $originalCommitteeReviews, "CommitteeReview");
            $transresUtil->removeReviewsFromProject($project, $originalFinalReviews, "FinalReview");
            $transresUtil->removeReviewsFromProject($project, $originalProjectGoals, "ProjectGoal");
            //////////// EOF remove the relationship between the review and the project ////////////

            //assign authors to the project goals
            $transresUtil->processProjectGoals($project);

//            $currentReviews = $project->getIrbReviews();
//            foreach( $currentReviews as $currentReview ) {
//                echo "review=".$currentReview."<br>";
//                echo "review=".$currentReview->getId()."<br>";
//            }
            //exit("After review");

            //exit("clickedButton=".$form->getClickedButton()->getName());

            //exit('before set state to irb_review');
            if ($form->getClickedButton() && 'submitIrbReview' === $form->getClickedButton()->getName()) {
                //Submit to IRB review
                if ($project->getState() == 'draft') {
                    $project->setState('irb_review');
                    $startProjectReview = true;

                    $label = $transresUtil->getStateLabelByName($project->getState());
                    $msg = "Project request " . $project->getOid() . " has been successfully updated and the status has been changed from '$originalStateLabel' to '$label'";
                    //$msg = $msg . " by " . $user->getUsernameOptimal();
                }
            }

            $transresUtil->assignMinimumProjectRoles($project); //edit

            //$project->autoPopulateApprovedProjectBudget(); //edit

            $em->getRepository(Document::class)->processDocuments($project, "document");
            $em->getRepository(Document::class)->processDocuments($project, "irbApprovalLetter");
            $em->getRepository(Document::class)->processDocuments($project, "humanTissueForm");

            //Change review's decision according to the state (if state has been changed manually)
            $eventResetMsg = null;
//            if( $originalState != $project->getState() ) {
//                $eventResetMsg = $transresUtil->resetReviewDecision($project);
//            }

            if (!$testing) {
                $em->persist($project);
                $em->flush();
            }

            $transresUtil->sendProjectApprovedBudgetUpdateEmail($project,$originalApprovedProjectBudget);
            $transresUtil->sendProjectNoBudgetUpdateEmail($project,$originalNoBudgetLimit);
            
            //if specialty is changed
            if( $originalProjectSpecialty->getId() != $project->getProjectSpecialty()->getId() ) {

                $transresPdfUtil = $this->container->get('transres_pdf_generator');

                //eventlog
//                $eventType = "Project Updated";
//                $msgSpecialtyChanged = "Project specialty changed to ".$project->getProjectSpecialty();
//                $transresUtil->setEventLog($project,$eventType,$msgSpecialtyChanged,$testing);

                $project->generateOid();

                //regenerate request Oid
                foreach($project->getRequests() as $transresRequest) {
                    $transresRequest->generateOid();

                    //regenerate invoice Oid
                    foreach($transresRequest->getInvoices() as $invoice){
                        $invoice->generateOid($transresRequest);
                    }
                }

                //eventlog
                $eventType = "Project Updated";
                $msgSpecialtyChanged = "Project specialty changed to ".$project->getProjectSpecialty().
                    " All associated work request's and invoice's IDs have been updated. All latest Invoice PDFs have been regenerated.";
                $transresUtil->setEventLog($project,$eventType,$msgSpecialtyChanged,$testing);

                $em->flush();
                //exit("Changed specialty");

                //regenarate latest invoice PDF
                foreach($project->getRequests() as $transresRequest) {
                    foreach($transresRequest->getInvoices() as $invoice){
                        //regenarate latest invoice PDF
                        if( $invoice->getLatestVersion() ) {
                            //echo "regenaret Invoice PDF for " . $invoice->getOid()."<br>";
                            $transresPdfUtil->generateInvoicePdf($invoice,$user,$request);
                        }
                    }
                }
            }

            //generate project PDF
            if( !$testing ) {
                $transresPdfUtil = $this->container->get('transres_pdf_generator');
                $transresPdfUtil->generateAndSaveProjectPdf($project,$user,$request); //edit
                $em->flush();
            }

            //process form nodes
            if( $formnode ) {
                $formNodeUtil = $this->container->get('user_formnode_utility');
                $formNodeUtil->processFormNodes($request,$project->getMessageCategory(),$project,$testing); //testing
                //update project's irbExpirationDate and fundedAccountNumber
                $transresUtil->copyFormNodeFieldsToProject($project);
            }

            $msg = $msg . " by ".$project->getUpdateUser()->getUsernameOptimal().".";

            $label = $transresUtil->getStateLabelByName($project->getState());
            $msg = $msg . " The project's current status is '".$label."'.";

            if( $testing ) {
                echo "<br>Enf of form submit<br>";
                echo "Clicked button=".$form->getClickedButton()->getName()."<br>";
                exit('Form is submitted and finished, msg='.$msg);
            }

            //echo "cliked btn=".$form->getClickedButton()->getName()."<br>";
            //exit('resubmit');
            if ($form->getClickedButton() && 'reSubmitReview' === $form->getClickedButton()->getName()) {
                //eventlog
                $eventType = "Project Updated";
                $transresUtil->setEventLog($project,$eventType,$msg.$eventResetMsg,$testing);

                //add resubmit comment
                $reSubmitReviewComment = $form->get('reSubmitReviewComment')->getData();
                if( $reSubmitReviewComment ) {
                    $transresUtil->addCommentToCurrentProjectState($project,$reSubmitReviewComment);
                }
                //exit("Resubmit; comment=".$reSubmitReviewComment);

                //redirect to review action or to the original project resubmit page
                //1) get a single reviewer:
                //$review = $transresUtil->getSingleReviewByProject($project);
                //However, we might have a multiple reviewers => just get the first one.
                //We need one single reviewer in order to use 'translationalresearch_transition_action_by_review' workflow
                //logic to transit the project from '_missinginfo' to the '_review' stage.
                //Therefore, just get a first reviewer and transit the project to '_review' stage
                $review = NULL;
                $reviews = $transresUtil->getReviewsByProjectAndState($project,$project->getState());
                if( count($reviews) > 0 ) {
                    $review = $reviews[0];
                }

                $transitionName = $transresUtil->getSingleTransitionNameByProject($project);
                //echo "review=$review, transitionName=$transitionName <br>";
                //exit("111");
                if( $review && $transitionName ) {
                    //http://127.0.0.1/order/translational-research/project-review-transition/irb_review_resubmit/3371/1218
                    //@Route("/project-review-transition/{transitionName}/{id}/{reviewId}", name="translationalresearch_transition_action_by_review")
                    //exit("redirect1 to translationalresearch_transition_action_by_review");
                    return $this->redirectToRoute('translationalresearch_transition_action_by_review',
                        array(
                            'transitionName' => $transitionName,
                            'id' => $project->getId(),
                            'reviewId' => $review->getId()
                        )
                    );
                } else {
                    //exit("redirect2 to translationalresearch_project_resubmit");
                    return $this->redirectToRoute('translationalresearch_project_resubmit', array('id' => $project->getId()));
                }
            }
            //exit("exit111");

            $this->addFlash(
                'notice',
                $msg
            );

            if( $startProjectReview ) {
                ///////////// send confirmation email to submitter and contact only ///////////////
                $break = "<br>";
                //get project url
                $projectUrl = $transresUtil->getProjectShowUrl($project);
                $emailBody = $msg . $break.$break. "To view this project request, please visit the link below:".$break.$projectUrl;
                //$transresUtil->sendNotificationEmails($project,null,$msg,$emailBody,$testing);

                $emailUtil = $this->container->get('user_mailer_utility');
                $senderEmail = $transresUtil->getTransresSiteProjectParameter('fromEmail',$project);
                $requesterEmails = $transresUtil->getRequesterMiniEmails($project);
                $adminsCcs = $transresUtil->getTransResAdminEmails($project,true,true); //after project edited
                //                    $emails, $subject, $message, $ccs=null, $fromEmail=null
                $emailUtil->sendEmail($requesterEmails,$msg,$emailBody,$adminsCcs,$senderEmail);
                ///////////// EOF send confirmation email to submitter and contact only ///////////////

                $emailResult = $transresUtil->sendTransitionEmail($project,"draft",$testing);
                $msg = $msg . "<br><br>" . $emailResult;

                //Send Notification emails for projects involving Computational Pathology or a request for a bioinformatician
                if( $project->sendComputationalEmail() ) {
                    $compEmailRes = $transresUtil->sendComputationalEmail($project); //edit page
                    $msg = $msg . "<br><br>" . $compEmailRes;
                    $this->addFlash(
                        'notice',
                        "Notification emails for projects involving Computational Pathology".
                        " or a request for a bioinformatician".
                        " have been sent: " . $compEmailRes
                    );
                }
            }

            //eventlog
            $eventType = "Project Updated";
            $transresUtil->setEventLog($project,$eventType,$msg.$eventResetMsg,$testing);

            return $this->redirectToRoute('translationalresearch_project_show', array('id' => $project->getId()));
        }//submit form

        $eventType = "Project Viewed";

        $msg = "Project request ".$project->getOid() ." has been viewed on the edit page.";
        $label = $transresUtil->getStateLabelByName($project->getState());
        $msg = $msg . " The project's current status is '".$label."'.";

        $transresUtil->setEventLog($project,$eventType,$msg,$testing);

        //append “ Approved Budget: $xx.xx” at the end of the title again,
        //only for users listed as PIs or Billing contacts or
        //Site Admin/Executive Committee/Platform Admin/Deputy Platform Admin) and
        //ONLY for projects with status = Final Approved or Closed
//        $approvedProjectBudgetInfo = "";
//        if( $transresUtil->isAdminPiBillingAndApprovedClosed($project) ) {
//            $approvedProjectBudget = $project->getApprovedProjectBudget();
//            if( $approvedProjectBudget ) {
//                //$approvedProjectBudget = $project->toMoney($approvedProjectBudget);
//                $approvedProjectBudget = $transresUtil->dollarSignValue($approvedProjectBudget);
//                $approvedProjectBudgetInfo = " (Approved Budget: $approvedProjectBudget)"; //edit page
//            }
//        }
        $approvedProjectBudgetInfo = "";
        if( $transresUtil->isAdminPrimaryRevExecutiveOrRequester($project) ) {

            $projectBudgetInfo = array();

            $approvedProjectBudget = $project->getApprovedProjectBudget();
            if( $approvedProjectBudget ) {
                //$approvedProjectBudget = $project->toMoney($approvedProjectBudget);
                $approvedProjectBudget = $transresUtil->dollarSignValue($approvedProjectBudget);
                //$approvedProjectBudgetInfo = " (Approved Budget: $approvedProjectBudget)"; //show page
                $projectBudgetInfo[] = "Approved Budget: $approvedProjectBudget";
            }
            $remainingProjectBudget = $project->getRemainingBudget();
            if( $remainingProjectBudget ) {
                $remainingProjectBudget = $transresUtil->dollarSignValue($remainingProjectBudget);
                $projectBudgetInfo[] = "Remaining Budget: $remainingProjectBudget"; //show page
            }
            $totalProjectBudget = $project->getTotal();
            if( $totalProjectBudget ) {
                $totalProjectBudget = $transresUtil->dollarSignValue($totalProjectBudget);
                $projectBudgetInfo[] = "Total Value: $totalProjectBudget"; //show page
            }

            if( count($projectBudgetInfo) > 0 ) {
                $approvedProjectBudgetInfo = implode(", ",$projectBudgetInfo);
            }
        }

        return array(
            'project' => $project,
            'edit_form' => $form->createView(),
            'cycle' => $cycle,
            'formtype' => $formtype,
            'title' => "Edit ".$project->getProjectInfoName(), //.$approvedProjectBudgetInfo, //edit
            'approvedProjectBudgetInfo' => $approvedProjectBudgetInfo,
            'triggerSearch' => 0,
            'formnodetrigger' => $formnodetrigger,
            'formnodeTopHolderId' => $formnodeTopHolderId,
            'entityNamespace' => $classNamespace,
            'entityName' => $className,
            'entityId' => $project->getId(),
            'sitename' => $this->getParameter('translationalresearch.sitename'),
        );
    }


    /**
     * Finds and displays a project entity.
     */
    #[Route(path: '/project/show/{id}', name: 'translationalresearch_project_show', methods: ['GET'])]
    #[Template('AppTranslationalResearchBundle/Project/show.html.twig')]
    public function showAction(Request $request, Project $project, $cycle="show")
    {

//        //Testing
//        if( $this->isGranted('ROLE_TRANSRES_ADMIN') ) {
//            echo "showAction: YES ROLE_TRANSRES_ADMIN <br>";
//        }
//        //exit('test role');

        $transresPermissionUtil = $this->container->get('transres_permission_util');

        if( false === $transresPermissionUtil->hasProjectPermission("view",$project) ) {
            return $this->redirect($this->generateUrl('translationalresearch-nopermission'));
        }

        $transresUtil = $this->container->get('transres_util');
        $em = $this->getDoctrine()->getManager();

        //Testing
        //generate project PDF
        //$transresPdfUtil = $this->container->get('transres_pdf_generator');
        //$user = $this->getUser();
        //$transresPdfUtil->generateAndSaveProjectPdf($project,$user,$request); //update_project_nobudgetlimit
        //Send Notification emails for projects involving Computational Pathology or a request for a bioinformatician
//        if( $project->sendComputationalEmail() ) {
//            $compEmailRes = $transresUtil->sendComputationalEmail($project);
//            $this->addFlash(
//                'notice',
//                "Notification emails for projects involving Computational Pathology".
//                " or a request for a bioinformatician".
//                " have been sent: " . $compEmailRes
//            );
//        }
//        exit('showAction: $project->sendComputationalEmail()');


        //$cycle = "show";

        $form = $this->createProjectForm($project,$cycle,$request); //show

        //$deleteForm = $this->createDeleteForm($project);

        //create a review form (for example, IrbReview form if logged in user is a reviewer or reviewer delegate)
        //1) if project is in the review state: irb_review, admin_review, committee_review or final_review
        //2) if the current user is added to this project as the reviewer for the state above
        //$reviewFormViews = $transresUtil->getReviewForm($project,$user);

        $eventType = "Project Viewed";

        $msg = "Project request ".$project->getOid() ." has been viewed on the show page.";
        $label = $transresUtil->getStateLabelByName($project->getState());
        $msg = $msg . " The project's current status is ".$label.".";

        $transresUtil->setEventLog($project,$eventType,$msg);

        $eventObjectType = $em->getRepository(EventObjectTypeList::class)->findOneByName("Project");
        if( $eventObjectType ) {
            $eventObjectTypeId = $eventObjectType->getId();
        } else {
            $eventObjectTypeId = null;
        }

        //append “ Approved Budget: $xx.xx” at the end of the title again,
        //only for users listed as PIs or Billing contacts or
        //Site Admin/Executive Committee/Platform Admin/Deputy Platform Admin) and
        //ONLY for projects with status = Final Approved or Closed
        $approvedProjectBudgetInfo = "";
        if( $transresUtil->isAdminPrimaryRevExecutiveOrRequester($project) ) {

            $projectBudgetInfo = array();

            $approvedProjectBudget = $project->getApprovedProjectBudget();
            if( $approvedProjectBudget ) {
                //$approvedProjectBudget = $project->toMoney($approvedProjectBudget);
                $approvedProjectBudget = $transresUtil->dollarSignValue($approvedProjectBudget);
                //$approvedProjectBudgetInfo = " (Approved Budget: $approvedProjectBudget)"; //show page
                $projectBudgetInfo[] = "Approved Budget: $approvedProjectBudget";
            }
            $remainingProjectBudget = $project->getRemainingBudget();
            if( $remainingProjectBudget ) {
                $remainingProjectBudget = $transresUtil->dollarSignValue($remainingProjectBudget);
                $projectBudgetInfo[] = "Remaining Budget: $remainingProjectBudget"; //show page
            }
            $totalProjectBudget = $project->getTotal();
            if( $totalProjectBudget ) {
                $totalProjectBudget = $transresUtil->dollarSignValue($totalProjectBudget);
                $projectBudgetInfo[] = "Total Value: $totalProjectBudget"; //show page
            }

            if( count($projectBudgetInfo) > 0 ) {
                $approvedProjectBudgetInfo = implode(", ",$projectBudgetInfo);
            }
        }

        return array(
            'project' => $project,
            'form' => $form->createView(),
            'cycle' => $cycle,
            'title' => $project->getProjectInfoName(),// .$approvedProjectBudgetInfo, //show: "Project request ".$project->getOid(),
            //'delete_form' => $deleteForm->createView(),
            'eventObjectTypeId' => $eventObjectTypeId,
            'approvedProjectBudgetInfo' => $approvedProjectBudgetInfo
            //'review_forms' => $reviewFormViews
        );
    }

    /**
     * Finds and displays a project entity on a simple html page
     * via ajax when project is changed on the new work request page.
     */
    #[Route(path: '/project/show-simple/{id}', name: 'translationalresearch_project_show_simple', methods: ['GET'], options: ['expose' => true])]
    #[Template('AppTranslationalResearchBundle/Project/show-simple.html.twig')]
    public function includeProjectDetailsAction(Request $request, Project $project)
    {

        ////////////////// rendering using the original project show ////////////////
        return $this->showAction($request,$project);
        ////////////////// EOF rendering using the original project show ////////////////

        ////////////////// custom rendering ////////////////
        $transresPermissionUtil = $this->container->get('transres_permission_util');

        if( false === $transresPermissionUtil->hasProjectPermission("view",$project) ) {
            return $this->redirect($this->generateUrl('translationalresearch-nopermission'));
        }

        $transresUtil = $this->container->get('transres_util');

        $cycle = "show";

        $form = $this->createProjectForm($project,$cycle,$request); //show show-simple

        //append “ Approved Budget: $xx.xx” at the end of the title again,
        //only for users listed as PIs or Billing contacts or
        //Site Admin/Executive Committee/Platform Admin/Deputy Platform Admin) and
        //ONLY for projects with status = Final Approved or Closed
//        $approvedProjectBudgetInfo = "";
//        if( $transresUtil->isAdminPrimaryRevExecutiveOrRequester($project) ) {
//            $approvedProjectBudget = $project->getApprovedProjectBudget();
//            if( $approvedProjectBudget ) {
//                //$approvedProjectBudget = $project->toMoney($approvedProjectBudget);
//                $approvedProjectBudget = $transresUtil->dollarSignValue($approvedProjectBudget);
//                $approvedProjectBudgetInfo = " (Approved Budget: $approvedProjectBudget)"; //show simple
//            }
//        }

        $approvedProjectBudgetInfo = "";
        if( $transresUtil->isAdminPrimaryRevExecutiveOrRequester($project) ) {

            $projectBudgetInfo = array();

            $approvedProjectBudget = $project->getApprovedProjectBudget();
            if( $approvedProjectBudget ) {
                $approvedProjectBudget = $transresUtil->dollarSignValue($approvedProjectBudget);
                $projectBudgetInfo[] = "Approved Budget: $approvedProjectBudget";
            }
            $remainingProjectBudget = $project->getRemainingBudget();
            if( $remainingProjectBudget ) {
                $remainingProjectBudget = $transresUtil->dollarSignValue($remainingProjectBudget);
                $projectBudgetInfo[] = "Remaining Budget: $remainingProjectBudget"; //show page
            }
            $totalProjectBudget = $project->getTotal();
            if( $totalProjectBudget ) {
                $totalProjectBudget = $transresUtil->dollarSignValue($totalProjectBudget);
                $projectBudgetInfo[] = "Total Value: $totalProjectBudget"; //show page
            }

            if( count($projectBudgetInfo) > 0 ) {
                $approvedProjectBudgetInfo = " (".implode(", ",$projectBudgetInfo).")";
            }
        }

        $resArr = array(
            'project' => $project,
            'form' => $form->createView(),
            'cycle' => $cycle,
            'title' => $project->getProjectInfoName().$approvedProjectBudgetInfo, //show: "Project request ".$project->getOid(),
            'messageToUsers' => null
        );

        return $this->render("AppTranslationalResearchBundle/Project/show-simple.html.twig",
            $resArr
        );
        ////////////////// EOF custom rendering ////////////////
    }

    //Show Project Goals on the Work Request page
    //Show this field on “Work Request View” page to all users only if this field is non-empty
    //Show this field on “Work Request Edit” page to users with TRP roles other than “basic TRP submitter”, even if it is empty on this Edit page
    #[Route(path: '/project/goals/{id}/{workrequestid}/{cycle}', name: 'translationalresearch_project_goals', methods: ['GET'], options: ['expose' => true])]
    #[Template('AppTranslationalResearchBundle/Project/goals.html.twig')]
    public function projectGoalsAction(Request $request, Project $project, $cycle, $workrequestid=NULL )
    {
//        $transresPermissionUtil = $this->container->get('transres_permission_util');
//        if( false === $transresPermissionUtil->hasProjectPermission("edit",$project) ) {
//            return $this->redirect($this->generateUrl('translationalresearch-nopermission'));
//        }

        //$cycle = "edit";
        //exit('$cycle='.$cycle);

        $transresUtil = $this->container->get('transres_util');
        if( $cycle == 'edit' ) {
            if( false === $transresUtil->isAdvancedUser($project) ) {
                //exit('111');
                //Don't show project goals on the edit page if user has "basic TRP submitter" role
                return array(
                    'title' => "Project Goals",
                    'project' => $project,
                    'cycle' => $cycle,
                    'form' => null,
                );
            }
        }

        //Show this field on “Work Request View” page to all users only if this field is non-empty.
        if( $cycle == 'show' ) {
            if( count($project->getProjectGoals()) == 0 ) {
                //Don't show project goals on the view page if empty
                return array(
                    'title' => "Project Goals",
                    'project' => $project,
                    'cycle' => $cycle,
                    'form' => null,
                );
            }
        }

        $disabled = true;
        if( $cycle != 'show' ) {
            $disabled = false;
        }

        $params = array(
            'cycle' => $cycle,
            'showProjectGoalStatus' => false, //true //Show project goal status and orderinlist on the Work Request
            'prototype_name' => '__reqprojectgoals__'
        );

        $form = $this->createForm(ProjectGoalsSectionType::class, $project, array(
            'form_custom_value' => $params,
            'disabled' => $disabled,
        ));

        return array(
            'title' => "Project Goals",
            'project' => $project,
            'workRequestId' => $workrequestid,
            'cycle' => $cycle,
            'formProjectGoal' => $form->createView(),
        );
    }

    #[Route(path: '/add-project-goals', name: 'translationalresearch_add_project_goals_ajax', methods: ['GET', 'POST'], options: ['expose' => true])]
    public function addProjectGoalsAjaxAction(Request $request) {

        $transresUtil = $this->container->get('transres_util');
        //$transresRequestUtil = $this->container->get('transres_request_util');
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();

        //$projectId = $request->query->get('projectId');
        //$productArr = $request->query->get('productArr');

        $projectId = $request->get('projectId');
        $workrequestId = $request->get('workrequestId');
        $projectGoals = $request->get('projectGoals');

        //dump($projectGoals);
        //exit('111');

        $workRequest = NULL;
        if( $workrequestId ) {
            $workRequest = $em->getRepository(TransResRequest::class)->find($workrequestId);
        }

        //$messageArr = array();
        $resultArr = array();

        if( $projectGoals && $projectId ) {

            foreach($projectGoals as $projectGoalData) {
                $projectGoalId = $projectGoalData['id'];
                $projectGoalEntityId = $projectGoalData['projectGoalEntityId']; //new if projectGoalEntityId is empty
                $description = $projectGoalData['description'];
                $associated = $projectGoalData['associated']; //TODO: associated does not pass correctly (always false)
                $description = $transresUtil->tokenTruncate($description, 100);
                //exit('associated='.$associated.", description=".$description);
                if( !$description ) {
                    $message = "Project goal is empty, this project goal has been removed.";
                    $resultArr[] = array(
                        'error' => 1,
                        'id' => $projectGoalId,
                        'projectGoalEntityId' => null,
                        'message' => $message
                    );
                    continue;
                }

                //TODO: check if projectGoalEntityId is not null => update associated value with this $workRequest
                //Otherwise => create new
                if( $workRequest && $projectGoalEntityId ) {
                    //Project Goal already exists => update only associated flag
                    $message = null;
                    $projectGoal = $em->getRepository(ProjectGoal::class)->find($projectGoalEntityId);
                    if ($projectGoal) {
                        if ($associated == 1) {
                            //add association
                            //check if does not associated
                            if (!$workRequest->getProjectGoals()->contains($projectGoal)) {
                                $workRequest->addProjectGoal($projectGoal);
                                //$message = "Project goal ID $projectGoal '$description' has been associated with this Work Request.";
                                $message = "The association has been created for project goal ID ". $projectGoal->getId()." '$description'";
                            }
                        } else {
                            //exit('remove WorkRequest');
                            //remove association
                            //check if already associated
                            if ($workRequest->getProjectGoals()->contains($projectGoal)) {
                                $workRequest->removeProjectGoal($projectGoal);
                                $projectGoal->removeWorkRequest($workRequest);
                                $message = "The association has been removed for project goal ID ". $projectGoal->getId()." '$description'";
                            }
                        }
                        $em->flush();
                    }// if $projectGoal
                    if ($message) {
                        $resultArr[] = array(
                            'error' => 0,
                            'id' => $projectGoalId,
                            'projectGoalEntityId' => $projectGoalEntityId,
                            'workRequestId' => $workRequest->getId(),
                            'message' => $message
                        );
                    }
                    continue;
                }

                //find if the project goal exists
                //$projectGoalEntity = $em->getRepository(ProjectGoal::class)->findOneByDescription('Pathology and Laboratory Medicine');
                $projectGoalEntities = $transresUtil->findProjectGoals($projectId,$description);
                if( $projectGoalEntities && count($projectGoalEntities) > 0 ) {
                    $message = "Project goal '$description' already exists, this project goal has been removed.";
                    if( count($projectGoalEntities) == 1 ) {
                        $projectGoalEntity = $projectGoalEntities[0];
                        $status = $projectGoalEntity->getStatus();
                        $message = "Project goal '$description' already exists with status '".ucfirst($status)."'; this project goal has been removed.";
                    }
                    $resultArr[] = array(
                        'error' => 1,
                        'id' => $projectGoalId,
                        'projectGoalEntityId' => null,
                        'message' => $message
                    );
                    continue;
                } else {
                    $project = $em->getRepository(Project::class)->find($projectId);
                    if( $project ) {
                        $projectGoalEntity = new ProjectGoal($user);
                        $projectGoalEntity->setProject($project);
                        $projectGoalEntity->setDescription($description);

                        $message = "Project goal '$description' has been successfully added.";

                        if ($associated == 1) {
                            if ($workRequest) {
                                $workRequest->addProjectGoal($projectGoalEntity);
                                $message = "Project goal '$description' has been successfully added and associated with this work request.";
                            }
                        }

                        if( $projectGoalEntity->getStatus() === NULL ) {
                            $projectGoalEntity->setStatus('enable');
                        }

                        //set orderinlist, can be edited orderinlist on the project edit page
                        //get the latest orderinlist from the project's project goals list
                        $orderinlist = $transresUtil->findNextProjectGoalOrderinlist($projectId);
                        if( $orderinlist ) {
                            $projectGoalEntity->setOrderinlist($orderinlist);
                        }

                        $em->persist($projectGoalEntity);
                        $em->flush();
                        //$messageArr[] = "Project goal '$description' has been successfully added.";
                        //$resultArr[$projectGoalId] = $projectGoalEntity->getId();
                        $resultArr[] = array(
                            'error' => 0,
                            'id' => $projectGoalId,
                            'projectGoalEntityId' => $projectGoalEntity->getId(),
                            'message' => $message
                        );
                    } else {
                        //$messageArr[] = "Project with ID '$projectId' does not exist.";
                        //$resultArr[$projectGoalId] = null;
                        $resultArr[] = array(
                            'error' => 1,
                            'id' => $projectGoalId,
                            'projectGoalEntityId' => null,
                            'message' => "Project with ID '$projectId' does not exist."
                        );
                        continue;
                    }
                }
            }//foreach
        }

//        $message = "";
//        if( count($messageArr) > 0 ) {
//            $message = implode('<br>',$messageArr);
//        }

        //testing
//        $output[] = array(
//            'error' => NULL,
//            'projectId' => $projectId,
//            'projectGoals' => implode(',',$projectGoals),
//            //'message' => $message,
//            'result' => $resultArr
//        );

        //$output = $remainingBudget;

        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');
        $response->setContent(json_encode($resultArr));
        return $response;
    }

    /**
     * Get existing project goals for this project
     */
    #[Route(path: '/project/project-goals/ajax', name: 'translationalresearch_project_get_project_goals_ajax', methods: ['GET'], options: ['expose' => true])]
    public function getProjectGoalsAction( Request $request )
    {
        if (false == $this->isGranted('ROLE_TRANSRES_USER')) {
            return $this->redirect($this->generateUrl('translationalresearch-nopermission'));
        }
        $em = $this->getDoctrine()->getManager();

        $transresUtil = $this->container->get('transres_util');

        $projectId = $request->get('projectId');

        if( !$projectId ) {
            $output = array(
                'error' => "No project id provided"
            );
            $response = new Response();
            $response->headers->set('Content-Type', 'application/json');
            $response->setContent(json_encode($output));
            return $response;
        }

        if( $projectId  ) {
            $project = $em->getRepository(Project::class)->find($projectId);
        }

        if( !$project ) {
            $output = array(
                'error' => "No project found by the project id $projectId"
            );
            $response = new Response();
            $response->headers->set('Content-Type', 'application/json');
            $response->setContent(json_encode($output));
            return $response;
        }

        if( $transresUtil->isUserAllowedSpecialtyObject($project->getProjectSpecialty()) === false ) {
            $this->addFlash(
                'warning',
                "You don't have a permission to access the ".$project->getProjectSpecialty()." project specialty"
            );
            return $this->redirect($this->generateUrl('translationalresearch-nopermission'));
        }
        
        $projectGoals = $transresUtil->findProjectGoals($project->getId());
        $existingProjectGoals = array();
        foreach($projectGoals as $existingProjectGoal) {
            $description = $transresUtil->tokenTruncate($existingProjectGoal->getDescription(), 100);
            //$existingProjectGoals[$description] = $existingProjectGoal->getId();
            $existingProjectGoals[] = array(
                'id' => $existingProjectGoal->getId(),
                'text' => $description
            );
        }

        $output = array(
            'error' => null,
            "existingProjectGoals" => $existingProjectGoals,
        );

        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');
        $response->setContent(json_encode($output));
        return $response;
    }

    /**
     * Finds and displays a review form for this project entity.
     */
    #[Route(path: '/project/review/{id}', name: 'translationalresearch_project_review', methods: ['GET'])]
    #[Template('AppTranslationalResearchBundle/Project/review.html.twig')]
    public function reviewAction(Request $request, Project $project)
    {
        $transresUtil = $this->container->get('transres_util');
        $transresPermissionUtil = $this->container->get('transres_permission_util');

        if( $transresPermissionUtil->hasProjectPermission("review",$project) ) {
            //ok
            //echo "ok review permission <br>";
        } else {
            //echo "no review permission <br>";
            //show no access page with view link for allowed users
            if( $transresPermissionUtil->hasProjectPermission("view",$project) ) {
                $projectUrl = $this->container->get('router')->generate(
                    'translationalresearch_project_show',
                    array(
                        'id' => $project->getId(),
                    ),
                    UrlGeneratorInterface::ABSOLUTE_URL
                );
                $projectLink = "<a href=" . $projectUrl . ">" . "View Project Request Details with ID " . $project->getOid() . "</a>";

                return $this->redirect($this->generateUrl('translationalresearch-nopermission',array('additionalMessage'=>$projectLink)));
            }

            return $this->redirect($this->generateUrl('translationalresearch-nopermission'));
        }

        //$cycle = "show";
        $cycle = "review";

        $form = $this->createProjectForm($project,$cycle,$request); //show review

        //$cycle = "review";

        //$deleteForm = $this->createDeleteForm($project);

        //create a review form (for example, IrbReview form if logged in user is a reviewer or reviewer delegate)
        //1) if project is in the review state: irb_review, admin_review, committee_review or final_review
        //2) if the current user is added to this project as the reviewer for the state above
        //$reviewFormViews = $transresUtil->getReviewForm($project,$user);

        $eventType = "Project Viewed";
        $msg = "Project request ".$project->getOid() ." has been viewed on the review page.";
        $transresUtil->setEventLog($project,$eventType,$msg);

        return array(
            'project' => $project,
            'form' => $form->createView(),
            'cycle' => $cycle,
            'title' => "Review ".$project->getProjectInfoName(),
            //'delete_form' => $deleteForm->createView(),
            //'review_forms' => $reviewFormViews
        );

//        return array(
//            'project' => $project,
//            'cycle' => 'show',
//            'delete_form' => $deleteForm->createView(),
//            'title' => "Project request ".$project->getId()
//        );
    }

    /**
     * Finds and displays a resubmit form for this project entity.
     */
    #[Route(path: '/project/resubmit/{id}', name: 'translationalresearch_project_resubmit', methods: ['GET'])]
    #[Template('AppTranslationalResearchBundle/Project/review.html.twig')]
    public function resubmitAction(Request $request, Project $project)
    {
        $transresUtil = $this->container->get('transres_util');

        if(
            $transresUtil->isAdminOrPrimaryReviewer($project) ||
            $transresUtil->isProjectStateRequesterResubmit($project)
        ) {
            //ok
        } else {
            return $this->redirect($this->generateUrl('translationalresearch-nopermission'));
        }

        if( $transresUtil->isUserAllowedSpecialtyObject($project->getProjectSpecialty()) === false ) {
            $this->addFlash(
                'warning',
                "You don't have a permission to access the ".$project->getProjectSpecialty()." project specialty"
            );
            return $this->redirect($this->generateUrl('translationalresearch-nopermission'));
        }

        $cycle = "review";
        //$cycle = "resubmit";

        $form = $this->createProjectForm($project,$cycle,$request); //show resubmit

        return array(
            'project' => $project,
            'form' => $form->createView(),
            'cycle' => $cycle,
            'title' => "Resubmit ".$project->getProjectInfoName(),
        );
    }

    public function createProjectEntity($user,$project=null) {

        $userSecUtil = $this->container->get('user_security_utility');
        $formnode = false;
        $em = $this->getDoctrine()->getManager();

        if( !$project ) {
            $project = new Project($user);
            $project->setVersion(1);
        }

        if( !$project->getInstitution() ) {
            $autoAssignInstitution = $userSecUtil->getAutoAssignInstitution();
            if( !$autoAssignInstitution ) {
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:Institution'] by [Institution::class]
                $autoAssignInstitution = $em->getRepository(Institution::class)->findOneByName('Pathology and Laboratory Medicine');
            }
            $project->setInstitution($autoAssignInstitution);
        }

        //set order category
        if( $formnode && !$project->getMessageCategory() ) {
            $categoryStr = "HemePath Translational Research Project";  //"Pathology Call Log Entry";
            //$categoryStr = "Nesting Test"; //testing
        //process.py script: replaced namespace by ::class: ['AppOrderformBundle:MessageCategory'] by [MessageCategory::class]
            $messageCategory = $em->getRepository(MessageCategory::class)->findOneByName($categoryStr);

            if (!$messageCategory) {
                throw new \Exception("Message category is not found by name '" . $categoryStr . "'");
            }
            $project->setMessageCategory($messageCategory);
        }

        //set project price list
//        if( !$project->getPriceList() ) {
//            $priceListName = "External Pricing";
//            $priceList = $em->getRepository('AppTranslationalResearchBundle:PriceTypeList')->findOneByName($priceListName);
//            if ($priceList) {
//                $project->setPriceList($priceList);
//            }
//        }

        return $project;
    }

    public function createProjectForm( Project $project, $cycle, $request )
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $transresUtil = $this->container->get('transres_util');
        $routeName = $request->get('_route');

        //set project price list if not set
        //if( $cycle == "new" ) {
//            if (!$project->getPriceList()) {
//                $priceListName = "External Pricing";
//                $priceList = $em->getRepository('AppTranslationalResearchBundle:PriceTypeList')->findOneByName($priceListName);
//                if ($priceList) {
//                    $project->setPriceList($priceList);
//                }
//            }
        //}

        $stateChoiceArr = $transresUtil->getStateChoisesArr();

        $otherUserParam = $project->getProjectSpecialty()->getAbbreviation();

        $institutionName = $transresUtil->getTransresSiteProjectParameter('institutionName',$project);

        $feeScheduleUrlArr = array();
        $projectSpecialty = $project->getProjectSpecialty();
        if( $projectSpecialty ) {
            $projectSpecialtyId = $projectSpecialty->getId();
            $feeScheduleUrlArr = array(
                'orderable-for-specialty[specialties][]' => $projectSpecialtyId
            );
        }

        $feeScheduleUrl = $this->container->get('router')->generate(
            'translationalresearchfeesschedule-list',
            //array(
            //    'orderable-for-specialty[specialties][]' => $project->getProjectSpecialty()->getId()
            //),
            $feeScheduleUrlArr,
            UrlGeneratorInterface::ABSOLUTE_URL
        );
        $feeScheduleLink = "<a target='_blank' data-toggle='tooltip' title='Products/Services (Fee Schedule) List' href=".
            $feeScheduleUrl.
            ">See fee schedule</a>";

        //On “New Project Request” page for MISI projects ONLY, after “See fee schedule” link, add
        // “ / See MISI Antibody Panel List” with the words “See MISI Antibody Panel List” linking to the PDF
        if( $cycle == 'new' && $project->getProjectSpecialtyStr() == 'MISI' ) {
//            $feeScheduleMisiLink = $this->container->get('router')->generate(
//                'translationalresearch_misi_antibody_panels',
//                array(),
//                UrlGeneratorInterface::ABSOLUTE_URL
//            );
            $feeScheduleMisiLink = $this->container->get('router')->generate(
                'translationalresearch_antibodies_group_by_panel',
                array('labs'=>'MISI'),
                UrlGeneratorInterface::ABSOLUTE_URL
            );
            $feeScheduleLink = $feeScheduleLink ." / ".
                "<a target='_blank' data-toggle='tooltip' title='MISI Antibody Panel List' href=".
                $feeScheduleMisiLink.
                ">See MISI Antibody Panel List</a>";
        }

//        $trpAdmin = false;
//        if( $this->isGranted('ROLE_TRANSRES_ADMIN') ) {
//            $trpAdmin = true;
//        }
        $trpAdmin = $transresUtil->isAdmin($project);
//        $trpTech = false;
//        if( $this->isGranted('ROLE_TRANSRES_TECHNICIAN') ) {
//            $trpTech = true;
//        }
        $trpTech = $transresUtil->isTech($project);

//        $trpCommitteeReviewer = false;
//        if( $this->isGranted('ROLE_TRANSRES_COMMITTEE_REVIEWER') ) {
//            $trpCommitteeReviewer = true;
//        }
        $trpCommitteeReviewer = $transresUtil->isComiteeReviewer($project);

        $trpAdvancedUser = $transresUtil->isAdvancedUser($project);

        $params = array(
            'cycle' => $cycle,
            'em' => $em,
            'user' => $user,
            'transresUtil' => $transresUtil,
            //'SecurityAuthChecker' => $this->container->get('security.authorization_checker'),
            'trpAdmin' => $trpAdmin,
            'trpTech' => $trpTech,
            'trpAdvancedUser' => $trpAdvancedUser,
            'trpCommitteeReviewer' => $trpCommitteeReviewer,
            'project' => $project,
            'otherUserParam' => $otherUserParam,
            'routeName' => $routeName,
            'disabledReviewerFields' => true,
            'disabledState' => true,
            'disabledReviewers' => true,
            'saveAsDraft' => false,
            //'saveAsComplete' => false,
            'updateProject' => false,
            'submitIrbReview' => false,
            'reSubmitReview' => false,
            'stateChoiceArr'=>$stateChoiceArr,
            'institutionName'=>$institutionName,
            'feeScheduleLink' => $feeScheduleLink,
            'showProjectGoalStatus' => true //Show project goal status and orderinlist on the project page
        );

        $params['admin'] = false;
        $params['showIrbReviewer'] = true;  //false; //TODO: change logic to show review result and comment, but hide reviewers
        $params['showAdminReviewer'] = true;
        $params['showCommitteeReviewer'] = true;
        $params['showFinalReviewer'] = true;

        //User can be admin for other project speciatly, but not for this one. Therefore: show only if reviewer
//        $params['showIrbReviewer'] = $transresUtil->isProjectReviewer($project);
//        $params['showAdminReviewer'] = $transresUtil->isProjectReviewer($project);
//        $params['showCommitteeReviewer'] = $transresUtil->isProjectReviewer($project);
//        $params['showFinalReviewer'] = $transresUtil->isProjectReviewer($project);

        //User can be admin for other project speciatly, but not for this one. Therefore: show only if platform admin, specialty admin, reviewer
        $params['showIrbReviewer'] = $transresUtil->isAdminOrPrimaryReviewerOrExecutive($project);
        $params['showAdminReviewer'] = $transresUtil->isAdminOrPrimaryReviewerOrExecutive($project);
        $params['showCommitteeReviewer'] = $transresUtil->isAdminOrPrimaryReviewerOrExecutive($project);
        $params['showFinalReviewer'] = $transresUtil->isAdminOrPrimaryReviewerOrExecutive($project);

        //Show reviews: If specialty admin or platform admin
        if( $this->isGranted('ROLE_PLATFORM_DEPUTY_ADMIN') ) {
            $params['showIrbReviewer'] = true;
            $params['showAdminReviewer'] = true;
            $params['showCommitteeReviewer'] = true;
            $params['showFinalReviewer'] = true;
        }

        if( $cycle == "pdf" ) {
            $params['showIrbReviewer'] = false;
            $params['showAdminReviewer'] = false;
            $params['showCommitteeReviewer'] = false;
            $params['showFinalReviewer'] = false;
        }

        $trpPrimaryReviewer = $transresUtil->isPrimaryReviewer($project);
        //if( $this->isGranted('ROLE_TRANSRES_ADMIN') || $this->isGranted('ROLE_TRANSRES_PRIMARY_REVIEWER') ) {
        if( $trpAdmin || $trpPrimaryReviewer ) {
            $params['admin'] = true;
//            $params['showIrbReviewer'] = true;
//            $params['showAdminReviewer'] = true;
//            $params['showCommitteeReviewer'] = true;
//            $params['showFinalReviewer'] = true;
            $params['disabledReviewerFields'] = false;
            $params['disabledState'] = false;
            $params['disabledReviewers'] = false;
        } else {
            //TODO: do not add reviewers
        }

        //testing
        //s$params['showIrbReviewer'] = false;
        //$params['showAdminReviewer'] = false;
        //$params['showCommitteeReviewer'] = false;
        //$params['showFinalReviewer'] = false;

        //show if owner
//        if( $transresUtil->isProjectReviewer($user,$project->getIrbReviews()) ) {
//            $params['showIrbReviewer'] = true;
//        }
//        if( $transresUtil->isProjectReviewer($user,$project->getAdminReviews()) ) {
//            $params['showAdminReviewer'] = true;
//        }
//        if( $transresUtil->isProjectReviewer($user,$project->getCommitteeReviews()) ) {
//            $params['showCommitteeReviewer'] = true;
//        }
//        if( $transresUtil->isProjectReviewer($user,$project->getFinalReviews()) ) {
//            $params['showFinalReviewer'] = true;
//        }

        //check if reviewer
//        $params['reviewer'] = false;
//        if(  ) {
//
//        }

        $disabled = false;

        if( $cycle == "new" ) {
            $disabled = false;

            if( $transresUtil->isRequesterOrAdmin($project) === true ) {
                $params['saveAsDraft'] = true;
                $params['submitIrbReview'] = true;
            }

//            if( $params['admin'] === false ) {
//                $params['showIrbReviewer'] = false;
//                $params['showAdminReviewer'] = false;
//                $params['showCommitteeReviewer'] = false;
//                $params['showFinalReviewer'] = false;
//            }
        }

        if( $cycle == "show" || $cycle == "review" || $cycle == "pdf" ) {
            $disabled = true;
        }

        if( $cycle == "resubmit" ) {
            $disabled = false;
        }

        if( $cycle == "edit" ) {
            $disabled = false;
            if( $project->getState() && $project->getState() == "draft" ) {
                if( $transresUtil->isRequesterOrAdmin($project) === true ) {
                    $params['submitIrbReview'] = true;
                    $params['updateProject'] = true;
                }
            }

            //allow edit if admin at any time
            if( $transresUtil->isAdminOrPrimaryReviewer($project) || $transresUtil->isProjectEditableByRequester($project) ) {
                $params['updateProject'] = true;
            }

            if( $project->getState() && strpos((string)$project->getState(),"_missinginfo") !== false ) {
                $params['reSubmitReview'] = true;
                //in the missing info stage, allow update project only by admin
                if( $transresUtil->isAdminOrPrimaryReviewer($project) ) {
                    $params['updateProject'] = true;
                } else {
                    $params['updateProject'] = false;
                }
            }
        }

        if( $cycle == "set-state" ) {
            $disabled = false;
        }

        //true or false. If true project will be shown with different tissue questions (for example, CP project)
        $specialProjectSpecialty = $transresUtil->specialProjectSpecialty($project);
        $params['specialProjectSpecialty'] = $specialProjectSpecialty;
        //true or false. If true collLabs checkboxes will be shown in project (for example, CP and AP/CP project)
        $specialExtraProjectSpecialty = $transresUtil->specialExtraProjectSpecialty($project);
        //echo "1specialExtraProjectSpecialty=".$specialExtraProjectSpecialty."<br>";
        $params['specialExtraProjectSpecialty'] = $specialExtraProjectSpecialty;

//        if( 0 && $project->getProjectSpecialty()->getAbbreviation() == 'misi' ) {
//            $form = $this->createForm(ProjectMisiType::class, $project, array(
//                'form_custom_value' => $params,
//                'disabled' => $disabled,
//            ));
//        } else {
//            $form = $this->createForm(ProjectType::class, $project, array(
//                'form_custom_value' => $params,
//                'disabled' => $disabled,
//            ));
//        }

        $form = $this->createForm(ProjectType::class, $project, array(
            'form_custom_value' => $params,
            'disabled' => $disabled,
        ));

        return $form;
    }

    /**
     * Creates a form to delete a project entity.
     *
     * @param Project $project The project entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(Project $project)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('translationalresearch_project_delete', array('id' => $project->getId())))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }

    /**
     * Finds and displays a resubmit form for this project entity.
     */
    #[Route(path: '/project/ajax/{id}', name: 'translationalresearch_get_project_ajax', methods: ['GET'], options: ['expose' => true])]
    #[Template('AppTranslationalResearchBundle/Project/review.html.twig')]
    public function getProjectAction(Request $request, Project $project)
    {
        if (false == $this->isGranted('ROLE_TRANSRES_USER')) {
            return $this->redirect($this->generateUrl('translationalresearch-nopermission'));
        }

        $em = $this->getDoctrine()->getManager();
        $transresUtil = $this->container->get('transres_util');
        $transresRequestUtil = $this->container->get('transres_request_util');

        if( $transresUtil->isUserAllowedSpecialtyObject($project->getProjectSpecialty()) === false ) {
            $this->addFlash(
                'warning',
                "You don't have a permission to access the ".$project->getProjectSpecialty()." project specialty"
            );
            return $this->redirect($this->generateUrl('translationalresearch-nopermission'));
        }

        $projectPisArr = array();
        foreach($project->getPrincipalInvestigators() as $pi) {
            $projectPisArr[] = $pi->getId();
        }

        $billingContactId = null;
        $billingContact = $project->getBillingContact();
        if( $billingContact ) {
            $billingContactId = $project->getBillingContact()->getId();
        }

        $implicitExpirationDate = null;
        if( $project->getImplicitExpirationDate() ) {
            $implicitExpirationDate = $project->getImplicitExpirationDate()->format("m/d/Y");
        }

        $projectFunded = $project->getFunded();
        $fundedStr = "Not-Funded";
        $projectFundedVal = null;
        if( $projectFunded ) {
            $fundedStr = "Funded";
            $projectFundedVal = 1;
        }

        //if project type = "USCAP Submission", set the default value for the Business Purpose of the new Work Request as "USCAP-related"
        $businessPurposesArr = array();
        if( $project->getProjectType() && $project->getProjectType()->getName() == "USCAP Submission" ) {
        //process.py script: replaced namespace by ::class: ['AppTranslationalResearchBundle:BusinessPurposeList'] by [BusinessPurposeList::class]
            $businessPurpose = $em->getRepository(BusinessPurposeList::class)->findOneByName("USCAP-related");
            //echo "businessPurpose=".$businessPurpose."<br>";
            if( $businessPurpose ) {
                $businessPurposesArr[] = $businessPurpose->getId();
            }
        }

        //get project's related "Product or Service" list
        // by getProductServiceByProjectSpecialty( $projectSpecialty, $project=null )
        $projectSpecialty = $project->getProjectSpecialty();
        $projectProducts = $transresRequestUtil->getSelectProductServiceByProjectSpecialty($projectSpecialty,$project);

        $projectRemainingBudgetNote = $transresUtil->getProjectRemainingBudgetNote($project);
        $remainingProjectBudgetValue = $project->getRemainingBudget();
        $remainingProjectBudget = $transresUtil->dollarSignValue($remainingProjectBudgetValue);
        $projectApprovedProjectBudget = $project->getApprovedProjectBudget();

        $messageToUsers = $transresUtil->getTrpMessageToUsers($project);

        $output = array(
            "projectId" => $project->getOid(),
            "fundedAccountNumber" => $project->getFundedAccountNumber(),
            "implicitExpirationDate" => $implicitExpirationDate,
            "principalInvestigators" => $projectPisArr,
            "contact" => $billingContactId, //BillingContact,
            "projectFundedVal" => $projectFundedVal,
            "fundedStr" => $fundedStr,
            "businessPurposes" => $businessPurposesArr,
            'projectProducts' => $projectProducts,
            'projectRemainingBudgetNote' => $projectRemainingBudgetNote,
            'remainingProjectBudgetValue' => $remainingProjectBudgetValue,
            'remainingProjectBudget' => $remainingProjectBudget,
            'projectApprovedProjectBudget' => $projectApprovedProjectBudget,
            'messageToUsers' => $messageToUsers
        );

        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');
        $response->setContent(json_encode($output));
        return $response;
    }

    /**
     * Download one single project
     */
    #[Route(path: '/download-projects-spreadsheet/{ids}/{limit}', methods: ['GET'], name: 'translationalresearch_download_projects_excel')]
    public function downloadApplicantListExcelAction(Request $request, $ids=null, $limit=null) {

        if (false == $this->isGranted('ROLE_TRANSRES_USER')) {
            return $this->redirect($this->generateUrl('translationalresearch-nopermission'));
        }

        //$limit = 2; //testing
        //exit("ids=".$ids);
        //exit("limit=".$limit);

        if( $ids ) {
            if( is_array($ids) && count($ids) == 0 ) {
                exit("No Projects to Export to spreadsheet");
            }
        }

        if( !$ids ) {
            exit("No Projects to Export to spreadsheet");
        }

        $transresUtil = $this->container->get('transres_util');

        //[YEAR] [WCMC (top level of actual institution)] [FELLOWSHIP-TYPE] Fellowship Candidate Data generated on [DATE] at [TIME] EST.xls
        //$fileName = "Projects ".date('m/d/Y H:i').".xlsx";
        $fileName = "Projects-".date('m-d-Y').".xlsx";
        //exit("filename=".$fileName);

        $projectIdsArr = explode(',', $ids);

        //testing
//        $transresRequestUtil = $this->container->get('transres_request_util');
//        $workRequests = $transresRequestUtil->getProjectMiniRequests(3370);
//        foreach($workRequests as $request) {
//            print_r($request);
//            $oid = $request['oid'];
//            exit('oid='.$oid);
//        }
//        exit('111');

        //Spout uses less memory
        $transresUtil->createProjectExcelSpout($projectIdsArr,$fileName,$limit);
        //header('Content-Disposition: attachment;filename="'.$fileName.'"');
        exit();

        //PhpOffice
        //TODO:
        //https://phpspreadsheet.readthedocs.io/en/develop/topics/memory_saving/
        // $cache = new MyCustomPsr16Implementation();
        //
        // composer require symfony/cache
        // use Symfony\Component\Cache\Simple\FilesystemCache;
        // $cache = new FilesystemCache();
        // \PhpOffice\PhpSpreadsheet\Settings::setCache($cache);

        $excelBlob = $transresUtil->createProjectListExcel($projectIdsArr,$limit);
        //exit("got excel blob");

        //$writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($excelBlob, 'Excel2007');
        $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($excelBlob, 'Xlsx');
        //ob_end_clean();
        //$writer->setIncludeCharts(true);

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="'.$fileName.'"');
        //header('Content-Disposition: attachment;filename="fileres.xlsx"');

        // Write file to the browser
        $writer->save('php://output');

        $excelBlob->disconnectWorksheets();
        unset($excelBlob);

        exit();
    }

    /**
     * Download multiple filtered projects
     */
    #[Route(path: '/download-projects-spreadsheet-post', methods: ['POST'], name: 'translationalresearch_download_projects_excel_post')]
    public function downloadApplicantListExcelPostAction(Request $request) {

        if (false == $this->isGranted('ROLE_TRANSRES_USER')) {
            return $this->redirect($this->generateUrl('translationalresearch-nopermission'));
        }

        //$ids = $request->query->get('projectids');
        $ids = $request->request->get('ids');
        //exit("ids=".$ids);

        $limit = null;
        //exit("ids=".$ids);
        //exit("limit=".$limit);

        if( $ids ) {
            if( is_array($ids) && count($ids) == 0 ) {
                exit("No Projects to Export to spreadsheet");
            }
        }

        if( !$ids ) {
            exit("No Projects to Export to spreadsheet");
        }

        $transresUtil = $this->container->get('transres_util');

        //[YEAR] [WCMC (top level of actual institution)] [FELLOWSHIP-TYPE] Fellowship Candidate Data generated on [DATE] at [TIME] EST.xls
        //$fileName = "Projects ".date('m/d/Y H:i').".xlsx";
        $fileName = "Projects-".date('m-d-Y').".xlsx";

        $projectIdsArr = explode(',', $ids);

        //Spout uses less memory
        $transresUtil->createProjectExcelSpout($projectIdsArr,$fileName,$limit);
        //header('Content-Disposition: attachment;filename="'.$fileName.'"');
        exit();
    }


    /**
     * Download one single project
     * Similarly as fellapp_download_interview_applicants_list_pdf
     */
    #[Route(path: '/download-projects-pdf/{id}', methods: ['GET'], name: 'translationalresearch_download_projects_pdf')]
    public function downloadProjectPdfAction(Request $request, $id=null) {

        if (false == $this->isGranted('ROLE_TRANSRES_USER')) {
            return $this->redirect($this->generateUrl('translationalresearch-nopermission'));
        }

        if( !$id ) {
            exit("Project id is null, no project to export to pdf");
        }

        $em = $this->getDoctrine()->getManager();
        //process.py script: replaced namespace by ::class: ['AppTranslationalResearchBundle:Project'] by [Project::class]
        $project = $em->getRepository(Project::class)->find($id);

        if( !$project ) {
            exit("Project not found by id $id");
        }

        //testing
        if(0) {
            $transresPdfUtil = $this->container->get('transres_pdf_generator');
            $pdfContent = $transresPdfUtil->exportProjectPdf($project, $request);
            $fileName = "test.pdf";
            return new Response(
                $pdfContent,
                200,
                array(
                    'Content-Type' => 'application/pdf',
                    'Content-Disposition' => 'attachment; filename="' . $fileName . '"'
                )
            );
        }

        $pdf = $project->getSingleProjectPdf();
        if( $pdf ) {
            $pdfPath = $pdf->getServerPath();
            if( file_exists($pdfPath) ) {
                return $this->redirect( $this->generateUrl('translationalresearch_file_download',array('id' => $pdf->getId())) );
            }
        }

        $transresPdfUtil = $this->container->get('transres_pdf_generator');
        $user = $this->getUser();
        $res = $transresPdfUtil->generateAndSaveProjectPdf($project,$user,$request);

        $filename = $res['filename'];
        $filsize = $res['size'];
        //echo "filsize=$filsize; filename=$filename <br>";

        if( $filename && $filsize ) {
            //exit("OK: filsize=$filsize; filename=$filename");
            $pdf = $project->getSingleProjectPdf();
            if( $pdf ) {
                return $this->redirect( $this->generateUrl('translationalresearch_file_download',array('id' => $pdf->getId())) );
            }
        }

        //exit("pdf no");
        $this->addFlash(
            'warning',
            "Logical error: project PDF not found"
        );

        return $this->redirectToRoute('translationalresearch_project_show', array('id' => $project->getId()));
    }

    /**
     * Finds and displays a project entity on a simple html page
     * via ajax when project is changed on the new work request page.
     */
    #[Route(path: '/project/show-simple-pdf/{id}', name: 'translationalresearch_project_show_simple_pdf', methods: ['GET'], options: ['expose' => true])]
    #[Template('AppTranslationalResearchBundle/Project/show-simple-pdf.html.twig')]
    public function showProjectPdfAction(Request $request, Project $project)
    {
        return $this->showAction($request, $project, "pdf");
    }

    /**
     * Force to update project PDF
     */
    #[Route(path: '/project/update-project-pdf/', name: 'translationalresearch_update_project_pdf', methods: ['GET', 'POST'], options: ['expose' => true])]
    #[Template('AppTranslationalResearchBundle/Project/show-simple-pdf.html.twig')]
    public function updateProjectPdfAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $transresUtil = $this->container->get('transres_util');

        $projectId = trim((string)$request->get('projectId') );
        //process.py script: replaced namespace by ::class: ['AppTranslationalResearchBundle:Project'] by [Project::class]
        $project = $em->getRepository(Project::class)->find($projectId);

        $permission = true;
        $res = "NotOK";

        if( $transresUtil->isAdminOrPrimaryReviewer($project) ) {
            //ok
        } else {
            //return $this->redirect($this->generateUrl('translationalresearch-nopermission'));
            $permission = false;
        }

        if( $transresUtil->isUserAllowedSpecialtyObject($project->getProjectSpecialty()) === false ) {
            $permission = false;
        }

        if( $permission == false ) {
            $response = new Response($res);
            return $response;
        }

        if( $project ) {

            //generate project PDF
            $transresPdfUtil = $this->container->get('transres_pdf_generator');
            $user = $this->getUser();
            $transresPdfUtil->generateAndSaveProjectPdf($project,$user,$request); //update_project_nobudgetlimit
            $em->flush();

            $logger = $this->container->get('logger');
            $logger->notice("translationalresearch_update_project_pdf updated PDF");

            $res = "Project " . $project->getOid() . " PDF has been updated";

            $this->addFlash(
                'notice',
                $res
            );
        } else {
            //$res = "Logical error: project not found by ID $projectId";
        }

        $response = new Response($res);
        return $response;
    }

}
