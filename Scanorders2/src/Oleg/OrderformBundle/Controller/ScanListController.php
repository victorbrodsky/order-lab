<?php

namespace Oleg\OrderformBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Form\Extension\Core\DataTransformer\DateTimeToStringTransformer;

use Oleg\OrderformBundle\Form\GenericListType;
use Oleg\OrderformBundle\Helper\ErrorHelper;

use Oleg\UserdirectoryBundle\Controller\ListController;

/**
 * Common list controller
 * @Route("/admin/list")
 */
class ScanListController extends ListController
{

    /**
     * Lists all entities.
     *
     * @Route("/mrn-types/", name="mrntype-list")
     * @Route("/accession-types/", name="accessiontype-list")
     * @Route("/encounter-types/", name="encountertype-list")
     * @Route("/stains/", name="stain-list")
     * @Route("/organs/", name="organ-list")
     * @Route("/procedures/", name="procedure-list")
     * @Route("/pathology-services/", name="pathservice-list")
     * @Route("/slide-types/", name="slidetype-list")
     * @Route("/form-types/", name="formtype-list")
     * @Route("/statuses/", name="status-list")
     * @Route("/return-slide-to-options/", name="returnslideto-list")
     * @Route("/slide-delivery-options/", name="slidedelivery-list")
     * @Route("/region-to-scan-options/", name="regiontoscan-list")
     * @Route("/scan-order-processor-comments/", name="processorcomment-list")
     * @Route("/accounts/", name="accounts-list")
     * @Route("/urgency/", name="urgency-list")
     * @Route("/scanners/", name="scanners-list")
     * @Method("GET")
     * @Template("OlegOrderformBundle:ListForm:index.html.twig")
     */
    public function indexAction(Request $request)
    {
        return $this->getList($request);
    }

    /**
     * Creates a new entity.
     *
     * @Route("/mrn-types/", name="mrntype_create")
     * @Route("/accession-types/", name="accessiontype_create")
     * @Route("/encounter-types/", name="encountertype_create")
     * @Route("/stains/", name="stain_create")
     * @Route("/organs/", name="organ_create")
     * @Route("/procedures/", name="procedure_create")
     * @Route("/pathology-services/", name="pathservice_create")
     * @Route("/slide-types/", name="slidetype_create")
     * @Route("/form-types/", name="formtype_create")
     * @Route("/statuses/", name="status_create")
     * @Route("/return-slide-to-options/", name="returnslideto_create")
     * @Route("/slide-delivery-options/", name="slidedelivery_create")
     * @Route("/region-to-scan-options/", name="regiontoscan_create")
     * @Route("/scan-order-processor-comments/", name="processorcomment_create")
     * @Route("/accounts/", name="accounts_create")
     * @Route("/urgency/", name="urgency_create")
     * @Route("/scanners/", name="scanners_create")
     * @Method("POST")
     * @Template("OlegOrderformBundle:ListForm:new.html.twig")
     */
    public function createAction(Request $request)
    {
        return $this->createList($request);
    }

    /**
     * Displays a form to create a new entity.
     *
     * @Route("/mrn-types/new", name="mrntype_new")
     * @Route("/accession-types/new", name="accessiontype_new")
     * @Route("/encounter-types/new", name="encountertype_new")
     * @Route("/stains/new", name="stain_new")
     * @Route("/organs/new", name="organ_new")
     * @Route("/procedures/new", name="procedure_new")
     * @Route("/pathology-services/new", name="pathservice_new")
     * @Route("/slide-types/new", name="slidetype_new")
     * @Route("/form-types/new", name="formtype_new")
     * @Route("/statuses/new", name="status_new")
     * @Route("/return-slide-to-options/new", name="returnslideto_new")
     * @Route("/slide-delivery-options/new", name="slidedelivery_new")
     * @Route("/region-to-scan-options/new", name="regiontoscan_new")
     * @Route("/scan-order-processor-comments/new", name="processorcomment_new")
     * @Route("/accounts/new", name="accounts_new")
     * @Route("/urgency/new", name="urgency_new")
     * @Route("/scanners/new", name="scanners_new")
     * @Method("GET")
     * @Template("OlegOrderformBundle:ListForm:new.html.twig")
     */
    public function newAction(Request $request)
    {
        return $this->newList($request);
    }

    /**
     * Finds and displays a entity.
     *
     * @Route("/mrn-types/{id}", name="mrntype_show")
     * @Route("/accession-types/{id}", name="accessiontype_show")
     * @Route("/encounter-types/{id}", name="encountertype_show")
     * @Route("/stains/{id}", name="stain_show")
     * @Route("/organs/{id}", name="organ_show")
     * @Route("/procedures/{id}", name="procedure_show")
     * @Route("/pathology-services/{id}", name="pathservice_show")
     * @Route("/slide-types/{id}", name="slidetype_show")
     * @Route("/form-types/{id}", name="formtype_show")
     * @Route("/statuses/{id}", name="status_show")
     * @Route("/return-slide-to-options/{id}", name="returnslideto_show")
     * @Route("/slide-delivery-options/{id}", name="slidedelivery_show")
     * @Route("/region-to-scan-options/{id}", name="regiontoscan_show")
     * @Route("/scan-order-processor-comments/{id}", name="processorcomment_show")
     * @Route("/accounts/{id}", name="accounts_show")
     * @Route("/urgency/{id}", name="urgency_show")
     * @Route("/scanners/{id}", name="scanners_show")
     * @Method("GET")
     * @Template("OlegOrderformBundle:ListForm:show.html.twig")
     */
    public function showAction(Request $request,$id)
    {
        return $this->showList($request,$id);
    }

    /**
     * Displays a form to edit an existing entity.
     *
     * @Route("/mrn-types/{id}/edit", name="mrntype_edit")
     * @Route("/accession-types/{id}/edit", name="accessiontype_edit")
     * @Route("/encounter-types/{id}/edit", name="encountertype_edit")
     * @Route("/stains/{id}/edit", name="stain_edit")
     * @Route("/organs/{id}/edit", name="organ_edit")
     * @Route("/procedures/{id}/edit", name="procedure_edit")
     * @Route("/pathology-services/{id}/edit", name="pathservice_edit")
     * @Route("/slide-types/{id}/edit", name="slidetype_edit")
     * @Route("/form-types/{id}/edit", name="formtype_edit")
     * @Route("/statuses/{id}/edit", name="status_edit")
     * @Route("/return-slide-to-options/{id}/edit", name="returnslideto_edit")
     * @Route("/slide-delivery-options/{id}/edit", name="slidedelivery_edit")
     * @Route("/region-to-scan-options/{id}/edit", name="regiontoscan_edit")
     * @Route("/scan-order-processor-comments/{id}/edit", name="processorcomment_edit")
     * @Route("/accounts/{id}/edit", name="accounts_edit")
     * @Route("/urgency/{id}/edit", name="urgency_edit")
     * @Route("/scanners/{id}/edit", name="scanners_edit")
     * @Method("GET")
     * @Template("OlegOrderformBundle:ListForm:edit.html.twig")
     */
    public function editAction(Request $request,$id)
    {
        return $this->editList($request,$id);
    }

    /**
     * Edits an existing entity.
     *
     * @Route("/mrn-types/{id}", name="mrntype_update")
     * @Route("/accession-types/{id}", name="accessiontype_update")
     * @Route("/encounter-types/{id}", name="encountertype_update")
     * @Route("/stains/{id}", name="stain_update")
     * @Route("/organs/{id}", name="organ_update")
     * @Route("/procedures/{id}", name="procedure_update")
     * @Route("/pathology-services/{id}", name="pathservice_update")
     * @Route("/slide-types/{id}", name="slidetype_update")
     * @Route("/form-types/{id}", name="formtype_update")
     * @Route("/statuses/{id}", name="status_update")
     * @Route("/return-slide-to-options/{id}", name="returnslideto_update")
     * @Route("/slide-delivery-options/{id}", name="slidedelivery_update")
     * @Route("/region-to-scan-options/{id}", name="regiontoscan_update")
     * @Route("/scan-order-processor-comments/{id}", name="processorcomment_update")
     * @Route("/accounts/{id}", name="accounts_update")
     * @Route("/urgency/{id}", name="urgency_update")
     * @Route("/scanners/{id}", name="scanners_update")
     * @Method("PUT")
     * @Template("OlegOrderformBundle:ListForm:edit.html.twig")
     */
    public function updateAction(Request $request, $id)
    {
        return $this->updateList($request, $id);
    }


    public function classListMapper( $route ) {

        switch( $route ) {

        case "mrntype":
            $className = "mrntype";
            $displayName = "MRN Types";
            break;
        case "accessiontype":
            $className = "accessiontype";
            $displayName = "Accession Types";
            break;
        case "encountertype":
            $className = "encountertype";
            $displayName = "Encounter Types";
            break;
        case "stain":
            $className = "stainlist";
            $displayName = "Stains";
            break;
        case "organ":
            $className = "organlist";
            $displayName = "Organs";
            break;
        case "procedure":
            $className = "procedurelist";
            $displayName = "Procedures";
            break;
        case "slidetype":
            $className = "slidetype";
            $displayName = "Slide Types";
            break;
        case "formtype":
            $className = "formtype";
            $displayName = "Form Types";
            break;
        case "status":
            $className = "status";
            $displayName = "Statuses";
            break;
        case "returnslideto":
            $className = "returnslideto";
            $displayName = '"Return Slide To" Options';
            break;
        case "slidedelivery":
            $className = "slidedelivery";
            $displayName = "Slide Delivery Options";
            break;
        case "regiontoscan":
            $className = "regiontoscan";
            $displayName = '"Region To Scan" Options';
            break;
        case "processorcomment":
            $className = "processorcomments";
            $displayName = "Processor Comments";
            break;
        case "accounts":
            $className = "Account";
            $displayName = "Accounts";
            break;
        case "urgency":
            $className = "Urgency";
            $displayName = "Urgencies";
            break;
        case "scanners":
            $className = "ScannerList";
            $displayName = "Scanners";
            break;
        default:
            $className = null;
            $displayName = null;
        }

        //echo "className=".$className.", displayName=".$displayName."<br>";

        $res = array();
        $res['className'] = $className;
        $res['fullClassName'] = "Oleg\\OrderformBundle\\Entity\\".$className;
        $res['bundleName'] = "OlegOrderformBundle";
        $res['displayName'] = $displayName;

        return $res;
    }

    /////////////////// DELETE IS NOT USED /////////////////////////
    /**
     * Deletes a entity.
     *
     * @Route("/mrn-types/{id}", name="mrntype_delete")
     * @Route("/accession-types/{id}", name="accessiontype_delete")
     * @Route("/encounter-types/{id}", name="encountertype_delete")
     * @Route("/stains/{id}", name="stain_delete")
     * @Route("/organs/{id}", name="organ_delete")
     * @Route("/procedures/{id}", name="procedure_delete")
     * @Route("/pathology-services/{id}", name="pathservice_delete")
     * @Route("/slide-types/{id}", name="slidetype_delete")
     * @Route("/form-types/{id}", name="formtype_delete")
     * @Route("/statuses/{id}", name="status_delete")
     * @Route("/return-slide-to-options/{id}", name="returnslideto_delete")
     * @Route("/slide-delivery-options/{id}", name="slidedelivery_delete")
     * @Route("/region-to-scan-options/{id}", name="regiontoscan_delete")
     * @Route("/scan-order-processor-comments/{id}", name="processorcomment_delete")
     * @Route("/accounts/{id}", name="accounts_delete")
     * @Route("/urgency/{id}", name="urgency_delete")
     * @Route("/scanners/{id}", name="scanners_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, $id)
    {

        $routeName = $request->get('_route');
        $pieces = explode("_", $routeName);
        $pathbase = $pieces[0];

        $mapper= $this->classListMapper($pathbase);

        $form = $this->createDeleteForm($id,$pathbase);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('OlegOrderformBundle:'.$mapper['className'])->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find '.$mapper['className'].' entity.');
            }

            $em->remove($entity);
            $em->flush();
        } else {
            //
        }

        return $this->redirect($this->generateUrl($pathbase));
    }
    /////////////////// DELETE IS NOT USED /////////////////////////

}
