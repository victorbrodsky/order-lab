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


/**
 * Common list controller
 * @Route("/admin/list")
 */
class ListController extends Controller
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
    public function indexAction()
    {
        $request = $this->container->get('request');
        return getList($request);
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

        $request = $this->container->get('request');
        $routeName = $request->get('_route');
        //exit("routeName=".$routeName); //mrntype

        $pieces = explode("_", $routeName);
        $pathbase = $pieces[0];

        $mapper= $this->classListMapper($pathbase);

        $entityClass = "Oleg\\OrderformBundle\\Entity\\".$mapper['className'];

        $entity = new $entityClass();

//        $user = $this->get('security.context')->getToken()->getUser();
//        $entity->setUpdatedby($user);
//        $entity->setUpdatedon(new \DateTime());

        $form = $this->createCreateForm($entity,$pathbase,'new');
        $form->handleRequest($request);

//        $errorHelper = new ErrorHelper();
//        $errors = $errorHelper->getErrorMessages($form);
//        echo "<br>form errors:<br>";
//        print_r($errors);
//        exit();
//        var_dump($form->getErrorsAsString());
//        echo "<br>";
//        var_dump($form->getErrors());

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();

            //the date from the form does not contain time, so set createdate with date and time.
            $entity->setCreatedate(new \DateTime());

            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl($pathbase.'_show', array('id' => $entity->getId())));
        }

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
            'displayName' => $mapper['displayName'],
            'pathbase' => $pathbase
        );
    }

    /**
    * Creates a form to create an entity.
    * @param $entity The entity
    * @return \Symfony\Component\Form\Form The form
    */
    private function createCreateForm($entity,$pathbase,$cicle=null)
    {
        $class = new \ReflectionClass($entity);
        $className = $class->getShortName();

        $options = array();

        $options['className'] = $className;

        if( method_exists($entity,'getOriginal') ) {
            $options['original'] = true;
        }

        if( method_exists($entity,'getSynonyms') ) {
            $options['synonyms'] = true;
        }

        if( $cicle ) {
            $options['cicle'] = $cicle;
        }

        //use $timezone = $user->getTimezone(); ?
        $user = $this->get('security.context')->getToken()->getUser();
        $options['user'] = $user;

        $newForm = new GenericListType($options);

        $form = $this->createForm($newForm, $entity, array(
            'action' => $this->generateUrl($pathbase.'_create'),
            'method' => 'POST',
        ));

        $form->add('submit', 'submit', array('label' => 'Create','attr'=>array('class'=>'btn btn-warning')));

        return $form;
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
    public function newAction()
    {

        $request = $this->container->get('request');
        $routeName = $request->get('_route');
        $pieces = explode("_", $routeName);
        $pathbase = $pieces[0];
        //echo "type=".$type."<br>";

        $mapper= $this->classListMapper($pathbase);

        $entityClass = "Oleg\\OrderformBundle\\Entity\\".$mapper['className'];

        $entity = new $entityClass();

        $user = $this->get('security.context')->getToken()->getUser();
        $entity->setCreatedate(new \DateTime());
        $entity->setType('user-added');
        $entity->setCreator($user);

        //$entity->setUpdatedby($user);
        //$entity->setUpdatedon(new \DateTime());

        //get max orderinlist + 10
        $em = $this->getDoctrine()->getManager();
        $query = $em->createQuery('SELECT MAX(c.orderinlist) as maxorderinlist FROM OlegOrderformBundle:'.$mapper['className'].' c');
        $nextorder = $query->getSingleResult()['maxorderinlist']+10;
        $entity->setOrderinlist($nextorder);

        $form   = $this->createCreateForm($entity,$pathbase,'new');

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
            'displayName' => $mapper['displayName'],
            'pathbase' => $pathbase
        );
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
    public function showAction($id)
    {

        $request = $this->container->get('request');
        $routeName = $request->get('_route');
        $pieces = explode("_", $routeName);
        $pathbase = $pieces[0];
        //echo "pathbase=".$pathbase."<br>";

        $em = $this->getDoctrine()->getManager();

        $mapper= $this->classListMapper($pathbase);

        $entity = $em->getRepository('OlegOrderformBundle:'.$mapper['className'])->find($id);
        $form = $this->createEditForm($entity,$pathbase,'edit',true);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find '.$mapper['className'].' entity.');
        }

        $deleteForm = $this->createDeleteForm($id,$pathbase);

        return array(
            'entity'      => $entity,
            'edit_form' => $form->createView(),
            'delete_form' => $deleteForm->createView(),
            'displayName' => $mapper['displayName'],
            'pathbase' => $pathbase
        );
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
    public function editAction($id)
    {

        $request = $this->container->get('request');
        $routeName = $request->get('_route');
        $pieces = explode("_", $routeName);
        $pathbase = $pieces[0];

        $em = $this->getDoctrine()->getManager();

        $mapper= $this->classListMapper($pathbase);

        $entity = $em->getRepository('OlegOrderformBundle:'.$mapper['className'])->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find '.$mapper['className'].' entity.');
        }

        $editForm = $this->createEditForm($entity,$pathbase,'edit');
        $deleteForm = $this->createDeleteForm($id,$pathbase);

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            //'delete_form' => $deleteForm->createView(),
            'displayName' => $mapper['displayName'],
            'pathbase' => $pathbase
        );
    }

    /**
    * Creates a form to edit an entity.
    * @param $entity The entity
    * @return \Symfony\Component\Form\Form The form
    */
    private function createEditForm($entity,$pathbase,$cicle,$disabled=false)
    {

        $class = new \ReflectionClass($entity);
        $className = $class->getShortName();

        $options = array();

        $options['className'] = $className;

        $options['id'] = $entity->getId();

        if( method_exists($entity,'getOriginal') ) {
            $options['original'] = true;
        }

        if( method_exists($entity,'getSynonyms') ) {
            $options['synonyms'] = true;
        }

        if( $cicle ) {
            $options['cicle'] = $cicle;
        }

        //use $timezone = $user->getTimezone(); ?
        $user = $this->get('security.context')->getToken()->getUser();
        $options['user'] = $user;

        $newForm = new GenericListType($options);

        $form = $this->createForm($newForm, $entity, array(
            'action' => $this->generateUrl($pathbase.'_show', array('id' => $entity->getId())),
            'method' => 'PUT',
            'disabled' => $disabled
        ));

        if( !$disabled ) {
            $form->add('submit', 'submit', array('label' => 'Update', 'attr'=>array('class'=>'btn btn-warning')));
        }

        return $form;
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

        //$request = $this->container->get('request');
        $routeName = $request->get('_route');
        $pieces = explode("_", $routeName);
        $pathbase = $pieces[0];

        $em = $this->getDoctrine()->getManager();

        $mapper= $this->classListMapper($pathbase);

        $entity = $em->getRepository('OlegOrderformBundle:'.$mapper['className'])->find($id);

        //save array of synonyms
        if( method_exists($entity,'getSynonyms') && $entity->getSynonyms() ) {
            $beforeformSynonyms = clone $entity->getSynonyms();
        }

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find '.$mapper['className'].' entity.');
        }

        $deleteForm = $this->createDeleteForm($id,$pathbase);
        $editForm = $this->createEditForm($entity,$pathbase,'edit');
        $editForm->handleRequest($request);

//        $transformer = new DateTimeToStringTransformer(null,null,'m/d/Y h:m:s');
//        $dateStr = $transformer->transform($entity->getCreatedate());
//        echo "date=".$dateStr."<br>";
//        echo "creator=".$entity->getCreator()."<br>";
//
//        $errorHelper = new ErrorHelper();
//        $errors = $errorHelper->getErrorMessages($editForm);
//        echo "<br>form errors:<br>";
//        print_r($errors);
//        echo "<br><br>";
//        var_dump($editForm->getErrorsAsString());
//        echo "<br>";
//        var_dump($editForm->getErrors());
//        //exit();

        if( $editForm->isValid() ) {

            //make sure to keep creator and creation date from original entity, according to the requirements (Issue#250):
            //For "Creation Date", "Creator" these variables should not be modifiable via the form even if the user unlocks these fields in the browser.
            $originalEntity = $em->getRepository('OlegOrderformBundle:'.$mapper['className'])->find($id);
            $entity->setCreator($originalEntity->getCreator());
            $entity->setCreatedate($originalEntity->getCreatedate());

            $user = $this->get('security.context')->getToken()->getUser();
            $entity->setUpdatedby($user);
            $entity->setUpdatedon(new \DateTime());

            if( method_exists($entity,'getSynonyms') ) {
                //take care of self-referencing: remove
                if( count($beforeformSynonyms) > count($entity->getSynonyms()) ) {
                    foreach( $beforeformSynonyms as $syn ) {
                        $syn->setOriginal(NULL);
                    }
                }

                //take care of self-referencing: add
                foreach( $entity->getSynonyms() as $syn ) {
                    $syn->setOriginal($entity);
                }
            }

            $em->flush();

            return $this->redirect($this->generateUrl($pathbase.'_show', array('id' => $id)));
        }

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
            'displayName' => $mapper['displayName'],
            'pathbase' => $pathbase
        );
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
        case "pathservice":
            $className = "pathservicelist";
            $displayName = "Pathology Services";
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
        case "role":
            $className = "roles";
            $displayName = "Roles";
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
        case "researchprojecttitles":
            $className = "projecttitlelist";
            $displayName = "Project Titles";
            break;
        case "researchsettitles":
            $className = "settitlelist";
            $displayName = "Set Titles";
            break;
        case "educationallessontitles":
            $className = "LessonTitleList";
            $displayName = "Lesson Titles";
            break;
        case "principalinvestigators":
            $className = "PIList";
            $displayName = "Principal Investigators";
            break;
        case "coursedirectors":
            $className = "DirectorList";
            $displayName = "Course Directors";
            break;
        case "departments":
            $className = "Department";
            $displayName = "Departments";
            break;
        case "institutions":
            $className = "Institution";
            $displayName = "Institutions";
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

    /**
     * Creates a form to delete a entity by id.
     * @param mixed $id The entity id
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id,$pathbase)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl($pathbase.'_delete', array('id' => $id)))
            ->setMethod('DELETE')
            ->add('submit', 'submit', array('label' => 'Delete','attr'=>array('class'=>'btn btn-danger')))
            ->getForm()
        ;
    }
    /////////////////// DELETE IS NOT USED /////////////////////////

}
