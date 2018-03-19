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

namespace Oleg\TranslationalResearchBundle\Controller;

//use Graphp\GraphViz\GraphViz;
use Doctrine\Common\Collections\ArrayCollection;
use Oleg\TranslationalResearchBundle\Entity\Project;
use Oleg\TranslationalResearchBundle\Form\FilterType;
use Oleg\TranslationalResearchBundle\Form\ProjectStateType;
use Oleg\TranslationalResearchBundle\Form\ProjectType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Workflow\DefinitionBuilder;
use Symfony\Component\Workflow\Dumper\GraphvizDumper;
use Symfony\Component\Workflow\Transition;

///**
// * Project controller.
// *
// * @Route("project")
// */

class ProjectController extends Controller
{

    /**
     * @Route("/", name="translationalresearch_home")
     * @Method("GET")
     */
    public function homeAction()
    {
        if( false === $this->get('security.authorization_checker')->isGranted('ROLE_TRANSRES_USER') ) {
            return $this->redirect( $this->generateUrl($this->container->getParameter('translationalresearch.sitename').'-nopermission') );
        }

        $transresUtil = $this->container->get('transres_util');
        if( $transresUtil->isAdminOrPrimaryReviewer() === false ) {
            if ($this->get('security.authorization_checker')->isGranted('ROLE_TRANSRES_REQUESTER')) {
                return $this->redirectToRoute('translationalresearch_my_project_index');
            }
        }

        return $this->redirectToRoute('translationalresearch_project_index');
    }

    /**
     * Lists all project entities.
     *
     * @Route("/projects/", name="translationalresearch_project_index")
     * @Route("/my-projects/", name="translationalresearch_my_project_index")
     * @Route("/projects-assigned-to-me-for-review/", name="translationalresearch_my_review_project_index")
     * @Route("/projects-pending-my-review/", name="translationalresearch_my_pending_review_project_index")
     * @Template("OlegTranslationalResearchBundle:Project:index.html.twig")
     * @Method("GET")
     */
    public function indexAction(Request $request)
    {

        if( false === $this->get('security.authorization_checker')->isGranted('ROLE_TRANSRES_USER') ) {
            return $this->redirect( $this->generateUrl($this->container->getParameter('translationalresearch.sitename').'-nopermission') );
        }

        $transresUtil = $this->container->get('transres_util');
        //$transResFormNodeUtil = $this->container->get('transres_formnode_util');
        $em = $this->getDoctrine()->getManager();
        $user = $this->get('security.token_storage')->getToken()->getUser();
        $routeName = $request->get('_route');
        $title = "Projects";

        if( $routeName == "translationalresearch_project_index" ) {
            if( $transresUtil->isAdminOrPrimaryReviewer() === false ) {
                if ($this->get('security.authorization_checker')->isGranted('ROLE_TRANSRES_REQUESTER')) {
                    return $this->redirectToRoute('translationalresearch_my_project_index');
                }
            }
        }

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

        //$projects = $em->getRepository('OlegTranslationalResearchBundle:Project')->findAll();

        $repository = $em->getRepository('OlegTranslationalResearchBundle:Project');
        $dql =  $repository->createQueryBuilder("project");
        $dql->select('project');

        $dql->leftJoin('project.submitter','submitter');
        $dql->leftJoin('project.principalInvestigators','principalInvestigators');
        $dql->leftJoin('principalInvestigators.infos','principalInvestigatorsInfos');

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

        $advancedFilter = 0;

        $dqlParameters = array();

        //get allowed and denied projectSpecialties
        $projectSpecialtyAllowedRes = $transresUtil->getAllowedProjectSpecialty($user);
        $projectSpecialtyAllowedArr = $projectSpecialtyAllowedRes['projectSpecialtyAllowedArr'];
        $projectSpecialtyDeniedArr = $projectSpecialtyAllowedRes['projectSpecialtyDeniedArr'];

        //////// create filter //////////
        //$filterError = true;
        $stateChoiceArr = $transresUtil->getStateChoisesArr();
        $params = array(
            'stateChoiceArr' => $stateChoiceArr,
            'projectSpecialtyAllowedArr' => $projectSpecialtyAllowedArr
        );
        $filterform = $this->createForm(FilterType::class, null,array(
            'method' => 'GET',
            'form_custom_value'=>$params
        ));

        if( count($projectSpecialtyAllowedArr) == 0 ) {
            $sysAdminEmailArr = $transresUtil->getTransResAdminEmails(true,true);
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

        $projectSpecialties = $filterform['projectSpecialty']->getData();
        $states = $filterform['state']->getData();
        $principalInvestigators = $filterform['principalInvestigators']->getData();
        $submitter = $filterform['submitter']->getData();
        $startDate = $filterform['startDate']->getData();
        $endDate = $filterform['endDate']->getData();
        $searchId = $filterform['searchId']->getData();
        $searchTitle = $filterform['searchTitle']->getData();
        $searchIrbNumber = $filterform['searchIrbNumber']->getData();
        $fundingNumber = $filterform['fundingNumber']->getData();
        $fundingType = $filterform['fundingType']->getData();
        $searchProjectType = $filterform['searchProjectType']->getData();
//        $archived = $filterform['completed']->getData();
//        $complete = $filterform['review']->getData();
//        $interviewee = $filterform['missinginfo']->getData();
//        $active = $filterform['approved']->getData();
//        $reject = $filterform['closed']->getData();
        if( isset($filterform['preroute']) ) {
            $preroute = $filterform['preroute']->getData();
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
        if(
            $transresUtil->isAdminOrPrimaryReviewer() === false &&
            $this->get('security.authorization_checker')->isGranted('ROLE_TRANSRES_TECHNICIAN') === false &&
            $this->get('security.authorization_checker')->isGranted('ROLE_TRANSRES_EXECUTIVE_HEMATOPATHOLOGY') === false &&
            $this->get('security.authorization_checker')->isGranted('ROLE_TRANSRES_EXECUTIVE_APCP') === false
        ) {

        }

        if( $projectSpecialties && count($projectSpecialties) > 0 ) {
            $dql->leftJoin('project.projectSpecialty','projectSpecialty');
            $projectSpecialtyIdsArr = array();
            foreach($projectSpecialties as $projectSpecialty) {
                $projectSpecialtyIdsArr[] = $projectSpecialty->getId();
            }
            $dql->andWhere("projectSpecialty.id IN (:projectSpecialtyIdsArr)");
            $dqlParameters["projectSpecialtyIdsArr"] = $projectSpecialtyIdsArr;
        }

        //echo "count states=".count($states)."<br>";
        if( $states && count($states) > 0 ) {
            $dql->andWhere("project.state IN (:states)");
            $dqlParameters["states"] = $states; //implode(",",$states);
            //$statesStr = "'".implode("','",$states)."'";
            //$dql->andWhere("project.state IN (".$statesStr.")");
            $advancedFilter++;
        }

        if( $searchId ) {
            //echo "searchId=$searchId<br>";
            $dql->andWhere("project.oid LIKE :oid");
            $dqlParameters["oid"] = "%".$searchId."%";
        }

        //////////////// get Projects IDs with the form node filter ////////////////
        if( $searchProjectType ) {
            $projectTypeIds = $transresUtil->getProjectIdsFormNodeByFieldName($searchProjectType->getId(),"Project Type");
            $dql->andWhere("project.id IN (:projectTypeIds)");
            $dqlParameters["projectTypeIds"] = $projectTypeIds;
            $advancedFilter++;
        }
        if( $searchTitle ) {
            $titleIds = $transresUtil->getProjectIdsFormNodeByFieldName($searchTitle,"Title");
            //$dql->andWhere("project.id IN (".implode(",",$titleIds).")");
            $dql->andWhere("project.id IN (:titleIds)");
            $dqlParameters["titleIds"] = $titleIds;
            $advancedFilter++;
        }
        if( $searchIrbNumber ) {
            $irbnumberIds = $transresUtil->getProjectIdsFormNodeByFieldName($searchIrbNumber,"IRB Number");
            //$dql->andWhere("project.id IN (".implode(",",$irbnumberIds).")");
            $dql->andWhere("project.id IN (:irbnumberIds)");
            $dqlParameters["irbnumberIds"] = $irbnumberIds;
            $advancedFilter++;
        }
        if( $fundingNumber ) {
            $fundingNumberIds = $transresUtil->getProjectIdsFormNodeByFieldName($fundingNumber,"If funded, please provide account number");
            //$dql->andWhere("project.id IN (".implode(",",$fundingNumberIds).")");
            $dql->andWhere("project.id IN (:fundingNumberIds)");
            $dqlParameters["fundingNumberIds"] = $fundingNumberIds;
            $advancedFilter++;
        }
        if( $fundingType ) {
            //echo "fundingType=" . $fundingType . "<br>";
            $compareType = NULL;
            if( $fundingType == "Funded" ) {
                $compareType = 1;
            }
            if( $fundingType == "Non-Funded" ) {
                $compareType = 0;
            }
            if( isset($compareType) ) {
                //$transResFormNodeUtil->getProjectFormNodeFieldByName(project,"Funded");
                $fundedIds = $transresUtil->getProjectIdsFormNodeByFieldName($compareType, "Funded");
                //echo "fundingNumberIds:" . implode(",", $fundingNumberIds) . "<br>";
                $dql->andWhere("project.id IN (:fundedIds)");
                $dqlParameters["fundedIds"] = $fundedIds;
            }
            $advancedFilter++;
        }
        //////////////// EOF get Projects IDs with the form node filter ////////////////

        if( $principalInvestigators && count($principalInvestigators)>0 ) {
            $dql->andWhere("principalInvestigators.id IN (:principalInvestigators)");
            $principalInvestigatorsIdsArr = array();
            foreach($principalInvestigators as $principalInvestigator) {
                $principalInvestigatorsIdsArr[] = $principalInvestigator->getId();
            }
            $dqlParameters["principalInvestigators"] = $principalInvestigatorsIdsArr; //implode(",",$principalInvestigatorsIdsArr);
        }

        if( $submitter ) {
            //echo "submitter=".$submitter->getId()."<br>";
            $dql->andWhere("submitter.id = :submitterId");
            $dqlParameters["submitterId"] = $submitter->getId();
            $advancedFilter++;
        }

        if( $startDate ) {
            //echo "startDate=" . $startDate->format('Y-m-d H:i:s') . "<br>";
            $dql->andWhere('project.createDate >= :startDate');
            $dqlParameters['startDate'] = $startDate->format('Y-m-d H:i:s');
            $advancedFilter++;
        }
        if( $endDate ) {
            $endDate->modify('+1 day');
            //echo "endDate=" . $endDate->format('Y-m-d H:i:s') . "<br>";
            $dql->andWhere('project.createDate <= :endDate');
            $dqlParameters['endDate'] = $endDate->format('Y-m-d H:i:s');
            $advancedFilter++;
        }


        //Non admin, Primary Reviewers and Executive can see all projects.
        // All other users can view only their projects
        // (where they are requesters: PI, Pathologists Involved, Co-Investigators, Contacts, Billing Contacts or reviewers)
        if( 
            $transresUtil->isAdminOrPrimaryReviewerOrExecutive() || 
            $this->get('security.authorization_checker')->isGranted('ROLE_TRANSRES_TECHNICIAN')
        ) {
            $showOnlyMyProjects = false;
        } else {
            $showOnlyMyProjects = true;
        }
        //echo "showOnlyMyProjects=$showOnlyMyProjects <br>";

        if( $showOnlyMyProjects || $routeName == "translationalresearch_my_project_index" ) {
            $dql->leftJoin('project.coInvestigators','coInvestigators');
            $dql->leftJoin('project.pathologists','pathologists');
            $dql->leftJoin('project.billingContact','billingContact');
            $dql->leftJoin('project.contacts','contacts');

            $showOnlyMyProjectsCriterion =
                "principalInvestigators.id = :userId OR ".
                "coInvestigators.id = :userId OR ".
                "pathologists.id = :userId OR ".
                "contacts.id = :userId OR ".
                "billingContact.id = :userId OR ".
                "submitter.id = :userId"
            ;

            $myReviewProjectsCriterion = $this->getProjectWhereIamReviewer();
            $showOnlyMyProjectsCriterion = $showOnlyMyProjectsCriterion . " OR " . $myReviewProjectsCriterion;

            $dql->andWhere($showOnlyMyProjectsCriterion);

            $dqlParameters["userId"] = $user->getId();
            $title = "My Projects, where I am a requester";
        }

        if( $routeName == "translationalresearch_my_review_project_index" ) {
            $myReviewProjectsCriterion = $this->getProjectWhereIamReviewer();
            $dql->andWhere($myReviewProjectsCriterion);

            $dqlParameters["userId"] = $user->getId();
            $title = "Projects Assigned to Me For Review";

//            if( $preroute == "translationalresearch_my_pending_review_project_index" ) {
//                $title = "Projects Pending My Review";
//            }
        }

        if( $routeName == "translationalresearch_my_pending_review_project_index" ) {
            //Pending my review: I'm a reviewer and project's review where I'm a reviewer has decision = NULL ("Pending Review")
            $myPendingProjectsCriterion =
                "((irbReviewer.id = :userId OR irbReviewerDelegate.id = :userId) AND irbReviews.decision IS NULL)".
                " OR ".
                "((adminReviewer.id = :userId OR adminReviewerDelegate.id = :userId) AND adminReviews.decision IS NULL)".
                " OR ".
                "((committeeReviewer.id = :userId OR committeeReviewerDelegate.id = :userId) AND committeeReviews.decision IS NULL)".
                " OR ".
                "((finalReviewer.id = :userId OR finalReviewerDelegate.id = :userId) AND finalReviews.decision IS NULL)"
            ;

            $dql->andWhere($myPendingProjectsCriterion);

            $dqlParameters["userId"] = $user->getId();
            $title = "Projects Pending My Review";
        }
        //////////////////// EOF Start Filter ////////////////////

        $dql->orderBy('project.id', 'DESC');

        $limit = 10;
        $query = $em->createQuery($dql);
        $query2 = $em->createQuery($dql);


        if( count($dqlParameters) > 0 ) {
            $query->setParameters($dqlParameters);
            $query2->setParameters($dqlParameters);
        }

        //echo "query=".$query->getSql()."<br>";

        $paginationParams = array(
            'defaultSortFieldName' => 'project.id',
            'defaultSortDirection' => 'DESC'
        );

        $paginator  = $this->get('knp_paginator');
        $projects = $paginator->paginate(
            $query,
            $request->query->get('page', 1),   /*page number*/
            $limit,                            /*limit per page*/
            $paginationParams
        );

        //$allProjects = array();
        $allProjects = $query2->getResult();
        if( count($allProjects) > 0 ) {
            //$allProjects = $projects->getTotalItemCount();
            $pageNumber = $projects->getCurrentPageNumber();
            $items = $projects->getItems();
            $startPageItems = (intval($pageNumber) - 1) * intval($limit) + 1;
            $endPageItems = intval($startPageItems) + count($items) - 1;
            //echo "pageNumber=$pageNumber; items=".count($items)."; startPageItems=".$startPageItems."; endPageItems=".$endPageItems."<br>";
            $title = $title . " (" . $startPageItems . " of " . $endPageItems . ", Total " . count($allProjects) . ")";
        } else {
            $title = $title . " (Total " . count($allProjects) . ")";
        }

        $eventObjectType = $em->getRepository('OlegUserdirectoryBundle:EventObjectTypeList')->findOneByName("Project");
        if( $eventObjectType ) {
            $eventObjectTypeId = $eventObjectType->getId();
        } else {
            $eventObjectTypeId = null;
        }

        return array(
            'projects' => $projects,
            'allProjects' => $allProjects,
            'title' => $title,
            'filterform' => $filterform->createView(),
            'eventObjectTypeId' => $eventObjectTypeId,
            'advancedFilter' => $advancedFilter
        );
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
     * Creates a new project entity in a simple way without formnode.
     *
     * @Route("/project/simple/new", name="translationalresearch_project_simple_new")
     * @Template("OlegTranslationalResearchBundle:Project:new.html.twig")
     * @Method({"GET", "POST"})
     */
    public function newSimpleAction(Request $request)
    {
        exit("This is a simple Project form not used. We use a formnode project fields instead.");

        if( false === $this->get('security.authorization_checker')->isGranted('ROLE_TRANSRES_USER') ) {
            return $this->redirect( $this->generateUrl($this->container->getParameter('translationalresearch.sitename').'-nopermission') );
        }

        $transresUtil = $this->container->get('transres_util');
        $user = $this->get('security.token_storage')->getToken()->getUser();
        $em = $this->getDoctrine()->getManager();
        $cycle = "new";

        $project = new Project($user);

//        $defaultReviewersAdded = false;
//        if(
//            $this->get('security.authorization_checker')->isGranted('ROLE_TRANSRES_ADMIN') ||
//            $this->get('security.authorization_checker')->isGranted('ROLE_TRANSRES_PRIMARY_REVIEWER') ||
//            $this->get('security.authorization_checker')->isGranted('ROLE_TRANSRES_PRIMARY_REVIEWER_DELEGATE')
//        ) {
//            //add all default reviewers
//            $transresUtil->addDefaultStateReviewers($project);
//            $defaultReviewersAdded = true;
//        }

        //new: add all default reviewers
        $transresUtil->addDefaultStateReviewers($project);

        $form = $this->createProjectForm($project,$cycle,$request); //simple new
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $em = $this->getDoctrine()->getManager();
            $em->persist($project);
            $em->flush();

            return $this->redirectToRoute('translationalresearch_project_show', array('id' => $project->getId()));
        }

        return array(
            'project' => $project,
            'form' => $form->createView(),
            'cycle' => $cycle,
            'title' => "Create Project"
        );
    }



    /**
     * Finds and displays a project entity.
     *
     * @Route("/project/show/{id}", name="translationalresearch_project_show")
     * @Template("OlegTranslationalResearchBundle:Project:show.html.twig")
     * @Method("GET")
     */
    public function showAction(Request $request, Project $project)
    {
        if( false === $this->get('security.authorization_checker')->isGranted('ROLE_TRANSRES_USER') ) {
            return $this->redirect( $this->generateUrl($this->container->getParameter('translationalresearch.sitename').'-nopermission') );
        }

        $transresUtil = $this->container->get('transres_util');
        $em = $this->getDoctrine()->getManager();
        //$user = $this->get('security.token_storage')->getToken()->getUser();

        if( $transresUtil->isUserAllowedSpecialtyObject($project->getProjectSpecialty()) === false ) {
            $this->get('session')->getFlashBag()->add(
                'warning',
                "You don't have a permission to access the ".$project->getProjectSpecialty()." project specialty"
            );
            return $this->redirect($this->generateUrl('translationalresearch-nopermission'));
        }

        $cycle = "show";

        $form = $this->createProjectForm($project,$cycle,$request); //show

        $deleteForm = $this->createDeleteForm($project);

        //create a review form (for example, IrbReview form if logged in user is a reviewer or reviewer delegate)
        //1) if project is in the review state: irb_review, admin_review, committee_review or final_review
        //2) if the current user is added to this project as the reviewer for the state above
        //$reviewFormViews = $transresUtil->getReviewForm($project,$user);

        $eventType = "Project Viewed";

        $msg = "Project ID ".$project->getOid() ." has been viewed on the show page.";
        $label = $transresUtil->getStateLabelByName($project->getState());
        $msg = $msg . " The project's current status is ".$label.".";

        $transresUtil->setEventLog($project,$eventType,$msg);

        $eventObjectType = $em->getRepository('OlegUserdirectoryBundle:EventObjectTypeList')->findOneByName("Project");
        if( $eventObjectType ) {
            $eventObjectTypeId = $eventObjectType->getId();
        } else {
            $eventObjectTypeId = null;
        }

        return array(
            'project' => $project,
            'form' => $form->createView(),
            'cycle' => $cycle,
            'title' => $project->getProjectInfoName(), //"Project ID ".$project->getOid(),
            'delete_form' => $deleteForm->createView(),
            'eventObjectTypeId' => $eventObjectTypeId,
            //'review_forms' => $reviewFormViews
        );

//        return array(
//            'project' => $project,
//            'cycle' => 'show',
//            'delete_form' => $deleteForm->createView(),
//            'title' => "Project ID ".$project->getId()
//        );
    }

    /**
     * Finds and displays a review form for this project entity.
     *
     * @Route("/project/review/{id}", name="translationalresearch_project_review")
     * @Template("OlegTranslationalResearchBundle:Project:review.html.twig")
     * @Method("GET")
     */
    public function reviewAction(Request $request, Project $project)
    {
        $transresUtil = $this->container->get('transres_util');

        if(
            $transresUtil->isAdminOrPrimaryReviewer() ||
            $transresUtil->isProjectReviewer($project)
        ) {
            //ok
        } else {
            return $this->redirect($this->generateUrl('translationalresearch-nopermission'));
        }

        if( $transresUtil->isUserAllowedSpecialtyObject($project->getProjectSpecialty()) === false ) {
            $this->get('session')->getFlashBag()->add(
                'warning',
                "You don't have a permission to access the ".$project->getProjectSpecialty()." project specialty"
            );
            return $this->redirect($this->generateUrl('translationalresearch-nopermission'));
        }

        //$transresUtil = $this->container->get('transres_util');
        //$em = $this->getDoctrine()->getManager();
        //$user = $this->get('security.token_storage')->getToken()->getUser();

        //$cycle = "show";
        $cycle = "review";

        $form = $this->createProjectForm($project,$cycle,$request); //show

        //$cycle = "review";

        $deleteForm = $this->createDeleteForm($project);

        //create a review form (for example, IrbReview form if logged in user is a reviewer or reviewer delegate)
        //1) if project is in the review state: irb_review, admin_review, committee_review or final_review
        //2) if the current user is added to this project as the reviewer for the state above
        //$reviewFormViews = $transresUtil->getReviewForm($project,$user);

        $eventType = "Project Viewed";
        $msg = "Project ID ".$project->getOid() ." has been viewed on the review page.";
        $transresUtil->setEventLog($project,$eventType,$msg);

        return array(
            'project' => $project,
            'form' => $form->createView(),
            'cycle' => $cycle,
            'title' => "Review ".$project->getProjectInfoName(),
            'delete_form' => $deleteForm->createView(),
            //'review_forms' => $reviewFormViews
        );

//        return array(
//            'project' => $project,
//            'cycle' => 'show',
//            'delete_form' => $deleteForm->createView(),
//            'title' => "Project ID ".$project->getId()
//        );
    }

    /**
     * Finds and displays a resubmit form for this project entity.
     *
     * @Route("/project/resubmit/{id}", name="translationalresearch_project_resubmit")
     * @Template("OlegTranslationalResearchBundle:Project:review.html.twig")
     * @Method("GET")
     */
    public function resubmitAction(Request $request, Project $project)
    {
        $transresUtil = $this->container->get('transres_util');

        if(
            $transresUtil->isAdminOrPrimaryReviewer() ||
            $transresUtil->isProjectStateRequesterResubmit($project)
        ) {
            //ok
        } else {
            return $this->redirect($this->generateUrl('translationalresearch-nopermission'));
        }

        if( $transresUtil->isUserAllowedSpecialtyObject($project->getProjectSpecialty()) === false ) {
            $this->get('session')->getFlashBag()->add(
                'warning',
                "You don't have a permission to access the ".$project->getProjectSpecialty()." project specialty"
            );
            return $this->redirect($this->generateUrl('translationalresearch-nopermission'));
        }

        //$cycle = "show";
        $cycle = "review";

        $form = $this->createProjectForm($project,$cycle,$request); //show

        return array(
            'project' => $project,
            'form' => $form->createView(),
            'cycle' => $cycle,
            'title' => "Resubmit ".$project->getProjectInfoName(),
        );
    }

    /**
     * Displays a form to edit an existing project entity.
     *
     * @Route("/project/{id}/simple/edit", name="translationalresearch_project_simple_edit")
     * @Template("OlegTranslationalResearchBundle:Project:edit.html.twig")
     * @Method({"GET", "POST"})
     */
    public function editAction(Request $request, Project $project)
    {
        $transresUtil = $this->container->get('transres_util');

        if(
            $transresUtil->isAdminOrPrimaryReviewer() ||
            $transresUtil->isProjectEditableByRequester($project)
        ) {
            //ok
        } else {
            return $this->redirect($this->generateUrl('translationalresearch-nopermission'));
        }

        $transresUtil = $this->container->get('transres_util');

        if(
            $transresUtil->isAdminOrPrimaryReviewer() ||
            $transresUtil->isProjectEditableByRequester($project)
        ) {
            //ok
        } else {
            return $this->redirect($this->generateUrl('translationalresearch-nopermission'));
        }

        //TODO: add the verification if the logged in user can edit the project:
        //1) requester => project is on the draft stage or in the reject stage
        //2) admin => any stage

        $user = $this->get('security.token_storage')->getToken()->getUser();
        $transresUtil = $this->container->get('transres_util');
        $em = $this->getDoctrine()->getManager();
        //$routeName = $request->get('_route');

        $cycle = "edit";

        //edit: add all default reviewers
        $transresUtil->addDefaultStateReviewers($project);

        ///////////// get originals /////////////
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
        ///////////// EOF get originals /////////////


        $deleteForm = $this->createDeleteForm($project);
        $editForm = $this->createProjectForm($project,$cycle,$request); //simple edit
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {

            $project->setUpdateUser($user);


            //////////// remove the relationship between the review and the project ////////////
//            //IRB Reviews
//            foreach ($originalIrbReviews as $review) {
//                if (false === $project->IrbReviews()->contains($review)) {
//                    // remove the Task from the Tag
//                    $project->IrbReviews()->removeElement($review);
//
//                    // if it was a many-to-one relationship, remove the relationship like this
//                    $review->setProject(null);
//
//                    $em->persist($review);
//
//                    // if you wanted to delete the Tag entirely, you can also do that
//                    $em->remove($review);
//                }
//            }
            $transresUtil->removeReviewsFromProject($project,$originalIrbReviews,$project->getIrbReviews());
            $transresUtil->removeReviewsFromProject($project,$originalAdminReviews,$project->getAdminReviews());
            $transresUtil->removeReviewsFromProject($project,$originalCommitteeReviews,$project->getCommitteeReviews());
            $transresUtil->removeReviewsFromProject($project,$originalFinalReviews,$project->getFinalReviews());
            //////////// EOF remove the relationship between the review and the project ////////////

            //testing
//            $irbs = $project->getIrbReviews();
//            echo "irbs count=".count($irbs)."<br>";
//            foreach($irbs as $irb) {
//                echo "irb=".$irb->getReviewer()."<br>";
//            }
            //exit('exit');

            $em->flush();

            return $this->redirectToRoute('translationalresearch_project_show', array('id' => $project->getId()));
        }

//        echo "irbReview count=".count($project->getIrbReviews())."<br>";
//        foreach($project->getIrbReviews() as $review){
//            echo "reviewer=".$review->getReviewer()->getUsernameOptimal()."<br>";
//        }

        return array(
            'project' => $project,
            'edit_form' => $editForm->createView(),
            'cycle' => $cycle,
            'delete_form' => $deleteForm->createView(),
            'title' => "Edit ".$project->getProjectInfoName()
        );
    }


    //     * @Route("/{id}/review", name="translationalresearch_project_review")



    /**
     * Deletes a project entity.
     *
     * @Route("/project/{id}", name="translationalresearch_project_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, Project $project)
    {
        if( false === $this->get('security.authorization_checker')->isGranted('ROLE_PLATFORM_DEPUTY_ADMIN') ) {
            return $this->redirect( $this->generateUrl($this->container->getParameter('translationalresearch.sitename').'-nopermission') );
        }

        $transresUtil = $this->container->get('transres_util');

        if( $transresUtil->isUserAllowedSpecialtyObject($project->getProjectSpecialty()) === false ) {
            $this->get('session')->getFlashBag()->add(
                'warning',
                "You don't have a permission to access the ".$project->getProjectSpecialty()." project specialty"
            );
            return $this->redirect($this->generateUrl('translationalresearch-nopermission'));
        }

        if(
            $transresUtil->isAdminOrPrimaryReviewer()
        ) {
            //ok
        } else {
            return $this->redirect($this->generateUrl('translationalresearch-nopermission'));
        }

        $form = $this->createDeleteForm($project);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($project);
            $em->flush();
        }

        return $this->redirectToRoute('translationalresearch_project_index');
    }

    public function createProjectForm( Project $project, $cycle, $request )
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->get('security.token_storage')->getToken()->getUser();
        $transresUtil = $this->container->get('transres_util');
        $routeName = $request->get('_route');

        $stateChoiceArr = $transresUtil->getStateChoisesArr();

        $params = array(
            'cycle' => $cycle,
            'em' => $em,
            'user' => $user,
            'transresUtil' => $transresUtil,
            'SecurityAuthChecker' => $this->get('security.authorization_checker'),
            'project' => $project,
            'otherUserParam' => $project->getProjectSpecialty()->getAbbreviation(),
            'routeName' => $routeName,
            'disabledReviewerFields' => true,
            'disabledState' => true,
            'disabledReviewers' => true,
            'saveAsDraft' => false,
            //'saveAsComplete' => false,
            'updateProject' => false,
            'submitIrbReview' => false,
            'stateChoiceArr'=>$stateChoiceArr
        );

        $params['admin'] = false;
        $params['showIrbReviewer'] = true;  //false; //TODO: change logic to show review result and comment, but hide reviewers
        $params['showAdminReviewer'] = true;
        $params['showCommitteeReviewer'] = true;
        $params['showFinalReviewer'] = true;
        if(
            $this->get('security.authorization_checker')->isGranted('ROLE_TRANSRES_ADMIN') ||
            $this->get('security.authorization_checker')->isGranted('ROLE_TRANSRES_PRIMARY_REVIEWER') ||
            $this->get('security.authorization_checker')->isGranted('ROLE_TRANSRES_PRIMARY_REVIEWER_DELEGATE')
        ) {
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

            if( $params['admin'] === false ) {
                $params['showIrbReviewer'] = false;
                $params['showAdminReviewer'] = false;
                $params['showCommitteeReviewer'] = false;
                $params['showFinalReviewer'] = false;
            }
        }

        if( $cycle == "show" || $cycle == "review" ) {
            $disabled = true;
        }

        if( $cycle == "edit" ) {
            $disabled = false;
//            if( $project->getState() && $project->getState() == "draft" ) {
//                if( $transresUtil->isRequesterOrAdmin($project) === true ) {
//                    $params['saveAsComplete'] = true;
//                }
//            }
            if( $project->getState() && $project->getState() == "draft" ) {
                if( $transresUtil->isRequesterOrAdmin($project) === true ) {
                    $params['submitIrbReview'] = true;
                    $params['updateProject'] = true;
                }
            }

            //allow edit if admin at any time
            if( $transresUtil->isAdminOrPrimaryReviewer() || $transresUtil->isProjectEditableByRequester($project) ) {
                $params['updateProject'] = true;
            }
        }

        if( $cycle == "set-state" ) {
            $disabled = false;
        }

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
     * @Route("/project/set-state/{id}", name="translationalresearch_project_set_state")
     * @Template("OlegTranslationalResearchBundle:Project:set-state.html.twig")
     * @Method({"GET","POST"})
     */
    public function setStateAction(Request $request, Project $project)
    {
        if( false === $this->get('security.authorization_checker')->isGranted('ROLE_PLATFORM_DEPUTY_ADMIN') ) {
            return $this->redirect( $this->generateUrl($this->container->getParameter('translationalresearch.sitename').'-nopermission') );
        }

        $transresUtil = $this->container->get('transres_util');

        if( $transresUtil->isUserAllowedSpecialtyObject($project->getProjectSpecialty()) === false ) {
            $this->get('session')->getFlashBag()->add(
                'warning',
                "You don't have a permission to access the ".$project->getProjectSpecialty()." project specialty"
            );
            return $this->redirect($this->generateUrl('translationalresearch-nopermission'));
        }

        $cycle = "set-state";

        //$form = $this->createProjectForm($project,$cycle,$request);

        $stateChoiceArr = $transresUtil->getStateChoisesArr();

        $params = array('stateChoiceArr'=>$stateChoiceArr);
        $form = $this->createForm(ProjectStateType::class, $project, array(
            'form_custom_value' => $params,
        ));

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $em = $this->getDoctrine()->getManager();
            $em->persist($project);
            $em->flush();

            return $this->redirectToRoute('translationalresearch_project_show', array('id' => $project->getId()));
        }

        return array(
            'project' => $project,
            'form' => $form->createView(),
            'cycle' => $cycle,
            'title' => "Set State for Project ID ".$project->getOid()
        );
    }

    /**
     * @Route("/project/thread-comments/{id}", name="translationalresearch_project_thread_comments")
     * @Template("OlegTranslationalResearchBundle:Project:thread-comments.html.twig")
     * @Method({"GET"})
     */
    public function threadCommentsAction(Request $request, $id)
    {
        if (false == $this->get('security.authorization_checker')->isGranted('ROLE_TRANSRES_USER')) {
            return $this->redirect($this->generateUrl('translationalresearch-nopermission'));
        }

        //$id = 'thread_id';
        $thread = $this->container->get('fos_comment.manager.thread')->findThreadById($id);
        if (null === $thread) {
            $thread = $this->container->get('fos_comment.manager.thread')->createThread();
            $thread->setId($id);
            $thread->setPermalink($request->getUri());

            // Add the thread
            $this->container->get('fos_comment.manager.thread')->saveThread($thread);
        }

        $comments = $this->container->get('fos_comment.manager.comment')->findCommentTreeByThread($thread);

        //return $comments;

        return array(
            'comments' => $comments,
            'thread' => $thread,
        );
    }

    /**
     * @Route("/project/thread-comments/show/{id}", name="translationalresearch_project_thread_comments_show")
     * @Template("OlegTranslationalResearchBundle:Project:thread-comments.html.twig")
     * @Method({"GET"})
     */
    public function threadCommentsShowAction(Request $request, $id)
    {
        //echo "comments id=".$id."<br>";
        //exit('1');

        if (false == $this->get('security.authorization_checker')->isGranted('ROLE_TRANSRES_USER')) {
            //exit("comments no permission");
            return $this->redirect($this->generateUrl('translationalresearch-nopermission'));
        }

        //$id = 'thread_id';
        $thread = $this->container->get('fos_comment.manager.thread')->findThreadById($id);
//        if (null === $thread) {
//            $thread = $this->container->get('fos_comment.manager.thread')->createThread();
//            $thread->setId($id);
//            $thread->setPermalink($request->getUri());
//
//            // Add the thread
//            $this->container->get('fos_comment.manager.thread')->saveThread($thread);
//        }

        if( $thread ) {
            $thread->setCommentable(false);
            $comments = $this->container->get('fos_comment.manager.comment')->findCommentTreeByThread($thread);
        } else {
            $comments = array();
        }

//        echo "comments count=".count($comments)."<br>";
//        if( count($comments) == 0 ) {
//            exit('stop');
//        }

        return array(
            'comments' => $comments,
            'thread' => $thread,
        );
    }

    /**
     * Finds and displays a resubmit form for this project entity.
     *
     * @Route("/project/ajax/{id}", name="translationalresearch_get_project_ajax", options={"expose"=true})
     * @Template("OlegTranslationalResearchBundle:Project:review.html.twig")
     * @Method("GET")
     */
    public function getProjectAction(Request $request, Project $project)
    {
        if (false == $this->get('security.authorization_checker')->isGranted('ROLE_TRANSRES_USER')) {
            return $this->redirect($this->generateUrl('translationalresearch-nopermission'));
        }

        $transresUtil = $this->container->get('transres_util');

        if( $transresUtil->isUserAllowedSpecialtyObject($project->getProjectSpecialty()) === false ) {
            $this->get('session')->getFlashBag()->add(
                'warning',
                "You don't have a permission to access the ".$project->getProjectSpecialty()." project specialty"
            );
            return $this->redirect($this->generateUrl('translationalresearch-nopermission'));
        }

        $output = array(
            "fundedAccountNumber" => $project->getFundedAccountNumber(),
            "irbExpirationDate" => $project->getIrbExpirationDate()->format("m/d/Y")
        );

        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');
        $response->setContent(json_encode($output));
        return $response;
    }

    /**
     * @Route("/download-projects-excel/{ids}", name="translationalresearch_download_projects_excel")
     */
    public function downloadApplicantListExcelAction(Request $request, $ids) {

        if (false == $this->get('security.authorization_checker')->isGranted('ROLE_TRANSRES_USER')) {
            return $this->redirect($this->generateUrl('translationalresearch-nopermission'));
        }

        //exit("ids=".$ids);

        if( count($ids) == 0 ) {
            exit("No Projects to Export to Excel");
        }

        //$em = $this->getDoctrine()->getManager();
        $transresUtil = $this->container->get('transres_util');

        //[YEAR] [WCMC (top level of actual institution)] [FELLOWSHIP-TYPE] Fellowship Candidate Data generated on [DATE] at [TIME] EST.xls
        $fileName = "Projects ".date('m/d/Y H:i').".xlsx";
        $fileName = str_replace("  ", " ", $fileName);
        $fileName = str_replace(" ", "-", $fileName);

        //$projects = $em->getRepository('OlegTranslationalResearchBundle:Project')->findAll();

        $projectIdsArr = explode(',', $ids);

        $excelBlob = $transresUtil->createProjectListExcel($projectIdsArr);

        $writer = \PHPExcel_IOFactory::createWriter($excelBlob, 'Excel2007');
        //ob_end_clean();
        //$writer->setIncludeCharts(true);

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');

        header('Content-Disposition: attachment;filename="'.$fileName.'"');
        //header('Content-Disposition: attachment;filename="fileres.xlsx"');

        // Write file to the browser
        $writer->save('php://output');

        exit();
    }

}
