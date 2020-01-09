<?php

namespace App\TranslationalResearchBundle\Controller;

use App\TranslationalResearchBundle\Entity\TransResSiteParameters;

use App\TranslationalResearchBundle\Form\SiteParameterType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

/**
 * SiteParameters controller.
 *
 * @Route("site-parameters")
 */
class TransResSiteParametersController extends Controller
{

//    /**
//     * Lists all SiteParameters entities.
//     *
//     * @Route("/list/{specialtyStr}", name="translationalresearch_standalone_siteparameters_index")
//     * @Template("AppTranslationalResearchBundle/SiteParameters/index.html.twig")
//     * @Method("GET")
//     */
//    public function indexAction(Request $request, $specialtyStr)
//    {
//        if( false === $this->get('security.authorization_checker')->isGranted('ROLE_TRANSRES_ADMIN') ) {
//            return $this->redirect( $this->generateUrl($this->container->getParameter('translationalresearch.sitename').'-nopermission') );
//        }
//
//
//
//        return array(
//            //
//        );
//    }

//    /**
//     * Creates a new SiteParameters entity.
//     *
//     * @Route("/new/{specialtyStr}", name="translationalresearch_standalone_siteparameters_new")
//     * @Template("AppTranslationalResearchBundle/SiteParameters/new.html.twig")
//     * @Method({"GET", "POST"})
//     */
//    public function newAction(Request $request, $specialtyStr)
//    {
//        if( false === $this->get('security.authorization_checker')->isGranted('ROLE_TRANSRES_ADMIN') ) {
//            return $this->redirect( $this->generateUrl($this->container->getParameter('translationalresearch.sitename').'-nopermission') );
//        }
//
//        //$em = $this->getDoctrine()->getManager();
//        //$transresUtil = $this->get('transres_util');
//        $transresRequestUtil = $this->get('transres_request_util');
//        $user = $this->get('security.token_storage')->getToken()->getUser();
//        //$user = null; //testing
//        $cycle = "new";
//
//        $invoice = $transresRequestUtil->createNewInvoice($transresRequest,$user);
//
//        $form = $this->createSiteParameterForm($invoice,$cycle,$transresRequest);
//
//        $form->handleRequest($request);
//
//        if ($form->isSubmitted() && $form->isValid()) {
//            //exit('new');
//
//            $msg = $transresRequestUtil->createSubmitNewInvoice($transresRequest,$invoice,$form);
//
//            if( $form->getClickedButton() && 'saveAndSend' === $form->getClickedButton()->getName() ) {
//                //TODO: generate and send PDF
//            }
//
//            //$msg = "New Invoice has been successfully created for the request ID ".$transresRequest->getOid();
//
//            $this->get('session')->getFlashBag()->add(
//                'notice',
//                $msg
//            );
//
//            return $this->redirectToRoute('translationalresearch_invoice_show', array('oid' => $invoice->getOid()));
//        }

//        return array(
//            'transresRequest' => $transresRequest,
//            'invoice' => $invoice,
//            'form' => $form->createView(),
//            'title' => "New Invoice for the Request ID ".$transresRequest->getOid(),
//            'cycle' => $cycle
//        );
//    }

    /**
     * Finds and displays site parameters entity.
     *
     * @Route("/show/{specialtyStr}", name="translationalresearch_standalone_siteparameters_show")
     * @Template("AppTranslationalResearchBundle/SiteParameters/new.html.twig")
     * @Method("GET")
     */
    public function showAction(Request $request, $specialtyStr)
    {
        if( false === $this->get('security.authorization_checker')->isGranted('ROLE_TRANSRES_ADMIN') ) {
            return $this->redirect( $this->generateUrl($this->container->getParameter('translationalresearch.sitename').'-nopermission') );
        }

        $transresRequestUtil = $this->get('transres_request_util');
        $cycle = "show";

        $siteParameter = $transresRequestUtil->findCreateSiteParameterEntity($specialtyStr);
        if( !$siteParameter ) {
            throw new \Exception("SiteParameter is not found by specialty '" . $specialtyStr . "'");
        }
        //echo "siteParameter=".$siteParameter."<br>";
        //exit();

        $form = $this->createSiteParameterForm($siteParameter,$cycle);

        return array(
            'siteParameter' => $siteParameter,
            'form' => $form->createView(),
            'cycle' => $cycle,
            'title' => $siteParameter,
        );
    }

    /**
     * Finds and displays site parameters entity.
     *
     * @Route("/show-content/{specialtyStr}", name="translationalresearch_standalone_siteparameters_show_content")
     * @Template("AppTranslationalResearchBundle/SiteParameters/show-content.html.twig")
     * @Method("GET")
     */
    public function showContentAction(Request $request, $specialtyStr)
    {
        if( false === $this->get('security.authorization_checker')->isGranted('ROLE_TRANSRES_ADMIN') ) {
            return $this->redirect( $this->generateUrl($this->container->getParameter('translationalresearch.sitename').'-nopermission') );
        }

        $transresRequestUtil = $this->get('transres_request_util');
        $cycle = "show";

        $siteParameter = $transresRequestUtil->findCreateSiteParameterEntity($specialtyStr);
        if( !$siteParameter ) {
            throw new \Exception("SiteParameter is not found by specialty '" . $specialtyStr . "'");
        }

        $form = $this->createSiteParameterForm($siteParameter,$cycle);

        return array(
            'siteParameter' => $siteParameter,
            'form' => $form->createView(),
            'cycle' => $cycle,
            'title' => $siteParameter,
            'specialtyStr' => $specialtyStr
        );
    }

    /**
     * Displays a form to edit an existing entity.
     *
     * @Route("/edit/{specialtyStr}", name="translationalresearch_standalone_siteparameters_edit")
     * @Template("AppTranslationalResearchBundle/SiteParameters/new.html.twig")
     * @Method({"GET", "POST"})
     */
    public function editAction(Request $request, $specialtyStr)
    {

        if( false === $this->get('security.authorization_checker')->isGranted('ROLE_TRANSRES_ADMIN') ) {
            return $this->redirect( $this->generateUrl($this->container->getParameter('translationalresearch.sitename').'-nopermission') );
        }

        $cycle = "edit";
        $user = $this->get('security.token_storage')->getToken()->getUser();
        $transresUtil = $this->get('transres_util');
        $transresRequestUtil = $this->get('transres_request_util');
        $em = $this->getDoctrine()->getManager();

        $siteParameter = $transresRequestUtil->findCreateSiteParameterEntity($specialtyStr);
        if( !$siteParameter ) {
            throw new \Exception("SiteParameter is not found by specialty '" . $specialtyStr . "'");
        }

        $form = $this->createSiteParameterForm($siteParameter,$cycle);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            //update user
            $siteParameter->setUpdateUser($user);

            //process document
            //$em->getRepository('AppUserdirectoryBundle:Document')->processSingleDocument($form,$siteParameter,"transresLogo");
            $em->getRepository('AppUserdirectoryBundle:Document')->processDocuments($siteParameter,"transresLogo");

            $em->getRepository('AppUserdirectoryBundle:Document')->processDocuments($siteParameter,"transresPackingSlipLogo");

            $em->flush();

            $msg = $siteParameter." have been updated.";

            $this->get('session')->getFlashBag()->add(
                'notice',
                $msg
            );

            $eventType = "SiteParameters Updated";
            //$eventType = "Site Settings Parameter Updated";
            $msg = $siteParameter." have been updated.";
            $transresUtil->setEventLog($siteParameter,$eventType,$msg);

            return $this->redirectToRoute('translationalresearch_standalone_siteparameters_show', array(
                'specialtyStr' => $siteParameter->getProjectSpecialty()->getAbbreviation()
            ));
        }

        return array(
            'siteParameter' => $siteParameter,
            'form' => $form->createView(),
            'cycle' => $cycle,
            'title' => $siteParameter,
        );
    }


    public function createSiteParameterForm( $siteParameter, $cycle ) {

        $em = $this->getDoctrine()->getManager();
        $transresUtil = $this->get('transres_util');
        $user = $this->get('security.token_storage')->getToken()->getUser();
        
        $params = array(
            'cycle' => $cycle,
            'em' => $em,
            'user' => $user,
            'humanName' => $transresUtil->getHumanName()
        );

        if( $cycle == "new" ) {
            $disabled = false;
        }

        if( $cycle == "show" ) {
            $disabled = true;
        }

        if( $cycle == "edit" ) {
            $disabled = false;
        }

        $form = $this->createForm(SiteParameterType::class, $siteParameter, array(
            'form_custom_value' => $params,
            'disabled' => $disabled,
        ));

        return $form;
    }

//    public function findCreateSiteParameterEntity($specialtyStr) {
//        $em = $this->getDoctrine()->getManager();
//        $user = $this->get('security.token_storage')->getToken()->getUser();
//
//        //$entity = $em->getRepository('AppTranslationalResearchBundle:TransResSiteParameters')->findOneByOid($specialtyStr);
//
//        $repository = $em->getRepository('AppTranslationalResearchBundle:TransResSiteParameters');
//        $dql = $repository->createQueryBuilder("siteParameter");
//        $dql->select('siteParameter');
//        $dql->leftJoin('siteParameter.projectSpecialty','projectSpecialty');
//
//        $dqlParameters = array();
//
//        $dql->where("projectSpecialty.abbreviation = :specialtyStr");
//
//        $dqlParameters["specialtyStr"] = $specialtyStr;
//
//        $query = $em->createQuery($dql);
//
//        if( count($dqlParameters) > 0 ) {
//            $query->setParameters($dqlParameters);
//        }
//
//        $entities = $query->getResult();
//        //echo "projectSpecialty count=".count($entities)."<br>";
//
//        if( count($entities) > 0 ) {
//            return $entities[0];
//        }
//
//        //Create New
//        $specialty = $em->getRepository('AppTranslationalResearchBundle:SpecialtyList')->findOneByAbbreviation($specialtyStr);
//        if( !$specialty ) {
//            throw new \Exception("SpecialtyList is not found by specialty abbreviation '" . $specialtyStr . "'");
//        } else {
//            $entity = new TransResSiteParameters($user);
//
//            $entity->setProjectSpecialty($specialty);
//
//            $em->persist($entity);
//            $em->flush($entity);
//
//            return $entity;
//        }
//
//        return null;
//    }

}
