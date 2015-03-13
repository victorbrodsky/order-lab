<?php

namespace Oleg\UserdirectoryBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Form\Extension\Core\DataTransformer\DateTimeToStringTransformer;

use Oleg\UserdirectoryBundle\Form\GenericListType;
use Oleg\UserdirectoryBundle\Util\ErrorHelper;


/**
 * Common list controller
 * @Route("/admin/list")
 */
class ListController extends Controller
{

    /**
     * Lists all entities.
     *
     * @Route("/sourcesystems/", name="sourcesystems-list")
     * @Route("/roles/", name="role-list")
     * @Route("/institutions/", name="institutions-list")
     * @Route("/departments/", name="departments-list")
     * @Route("/divisions/", name="divisions-list")
     * @Route("/services/", name="services-list")
     * @Route("/states/", name="states-list")
     * @Route("/countries/", name="countries-list")
     * @Route("/board-certifications/", name="boardcertifications-list")
     * @Route("/employment_terminations/", name="employmentterminations-list")
     * @Route("/event-log-event-types/", name="loggereventtypes-list")
     * @Route("/primary-public-userid-types/", name="usernametypes-list")
     * @Route("/identifier-types/", name="identifiers-list")
     * @Route("/residency-tracks/", name="residencytracks-list")
     * @Route("/fellowship-types/", name="fellowshiptypes-list")
//     * @Route("/research-labs/", name="researchlabs-list")
     * @Route("/location-types/", name="locationtypes-list")
     * @Route("/equipment/", name="equipments-list")
     * @Route("/equipment-types/", name="equipmenttypes-list")
     * @Route("/location-privacy-types/", name="locationprivacy-list")
     * @Route("/role-attributes/", name="roleattributes-list")
     * @Route("/buidlings/", name="buildings-list")
     * @Route("/rooms/", name="rooms-list")
     * @Route("/suites/", name="suites-list")
     * @Route("/floors/", name="floors-list")
     * @Route("/mailboxes/", name="mailboxes-list")
     * @Route("/efforts/", name="efforts-list")
     * @Route("/admintitles/", name="admintitles-list")
     * @Route("/apptitles/", name="apptitles-list")
     * @Route("/completionreasons/", name="completionreasons-list")
     * @Route("/trainingdegrees/", name="trainingdegrees-list")
     * @Route("/trainingmajors/", name="trainingmajors-list")
     * @Route("/trainingminors/", name="trainingminors-list")
     * @Route("/traininghonors/", name="traininghonors-list")
     * @Route("/fellowshiptitles/", name="fellowshiptitles-list")
     * @Route("/residencyspecialty/", name="residencyspecialtys-list")
     * @Route("/fellowshipsubspecialty/", name="fellowshipsubspecialtys-list")
     * @Route("/institutiontypes/", name="institutiontypes-list")
     * @Route("/documenttypes/", name="documenttypes-list")
     *
     * @Method("GET")
     * @Template("OlegUserdirectoryBundle:ListForm:index.html.twig")
     */
    public function indexAction(Request $request)
    {
        if( false === $this->get('security.context')->isGranted('ROLE_USERDIRECTORY_OBSERVER') ) {
            return $this->redirect( $this->generateUrl($this->container->getParameter('employees.sitename').'-order-nopermission') );
        }

        return $this->getList($request);
    }
    public function getList($request) {

        $type = $request->get('_route');

        //get object name: stain-list => stain
        $pieces = explode("-", $type);
        $pathbase = $pieces[0];
        //echo "pathbase=".$pathbase."<br>";

        $mapper= $this->classListMapper($pathbase);

        $repository = $this->getDoctrine()->getRepository($mapper['bundleName'].':'.$mapper['className']);
        $dql =  $repository->createQueryBuilder("ent");
        $dql->select('ent');
        $dql->groupBy('ent');

        $dql->leftJoin("ent.creator", "creator");
        $dql->leftJoin("ent.updatedby", "updatedby");

        $dql->addGroupBy('creator.username');
        $dql->addGroupBy('updatedby.username');

        $entityClass = $mapper['fullClassName'];   //"Oleg\\OrderformBundle\\Entity\\".$mapper['className'];

        //synonyms and original
        $dql->leftJoin("ent.synonyms", "synonyms");
        $dql->addGroupBy('synonyms.name');
        $dql->leftJoin("ent.original", "original");
        $dql->addGroupBy('original.name');


//        if( method_exists($entityClass,'getResearchlab') ) {
//            $dql->leftJoin("ent.researchlab", "researchlab");
//            $dql->leftJoin("researchlab.user", "user");
//            $dql->addSelect('COUNT(user) AS HIDDEN usercount');
//        }

        if( method_exists($entityClass,'getParent') ) {
            $dql->leftJoin("ent.parent", "parent");
            $dql->addGroupBy('parent.name');
        }

        if( method_exists($entityClass,'getRoles') ) {
            $dql->leftJoin("ent.roles", "roles");
            $dql->addGroupBy('roles.name');
        }

        if( method_exists($entityClass,'getAttributes') ) {
            $dql->leftJoin("ent.attributes", "attributes");
            $dql->addGroupBy('attributes');
        }

        //$dql->orderBy("ent.createdate","DESC");
		
		//pass sorting parameters directly to query; Somehow, knp_paginator stoped correctly create pagination according to sorting parameters       		
		$postData = $request->query->all();
		if( isset($postData['sort']) ) {    			
            $dql = $dql . " ORDER BY $postData[sort] $postData[direction]";
        }

        //echo "dql=".$dql."<br>";

        $em = $this->getDoctrine()->getManager();
        $limit = 30;
        $query = $em->createQuery($dql);
        $paginator  = $this->get('knp_paginator');
        $entities = $paginator->paginate(
            $query,
            $this->get('request')->query->get('page', 1), /*page number*/
            $limit/*limit per page*/
        );

        return array(
            'entities' => $entities,
            'displayName' => $mapper['displayName'],
            'pathbase' => $pathbase
        );
    }

    /**
     * Creates a new entity.
     *
     * @Route("/sourcesystems/", name="sourcesystems_create")
     * @Route("/roles/", name="role_create")
     * @Route("/institutions/", name="institutions_create")
     * @Route("/departments/", name="departments_create")
     * @Route("/divisions/", name="divisions_create")
     * @Route("/services/", name="services_create")
     * @Route("/states/", name="states_create")
     * @Route("/countries/", name="countries_create")
     * @Route("/board-certifications/", name="boardcertifications_create")
     * @Route("/employment_terminations/", name="employmentterminations_create")
     * @Route("/event-log-event-types/", name="loggereventtypes_create")
     * @Route("/primary-public-userid-types/", name="usernametypes_create")
     * @Route("/identifier-types/", name="identifiers_create")
     * @Route("/residency-tracks/", name="residencytracks_create")
     * @Route("/fellowship-types/", name="fellowshiptypes_create")
//     * @Route("/research-labs/", name="researchlabs_create")
     * @Route("/location-types/", name="locationtypes_create")
     * @Route("/equipment/", name="equipments_create")
     * @Route("/equipment-types/", name="equipmenttypes_create")
     * @Route("/location-privacy-types/", name="locationprivacy_create")
     * @Route("/role-attributes/", name="roleattributes_create")
     * @Route("/buidlings/", name="buildings_create")
     * @Route("/rooms/", name="rooms_create")
     * @Route("/suites/", name="suites_create")
     * @Route("/floors/", name="floors_create")
     * @Route("/mailboxes/", name="mailboxes_create")
     * @Route("/efforts/", name="efforts_create")
     * @Route("/admintitles/", name="admintitles_create")
     * @Route("/apptitles/", name="apptitles_create")
     * @Route("/completionreasons/", name="completionreasons_create")
     * @Route("/trainingdegrees/", name="trainingdegrees_create")
     * @Route("/trainingmajors/", name="trainingmajors_create")
     * @Route("/trainingminors/", name="trainingminors_create")
     * @Route("/traininghonors/", name="traininghonors_create")
     * @Route("/fellowshiptitles/", name="fellowshiptitles_create")
     * @Route("/residencyspecialty/", name="residencyspecialtys_create")
     * @Route("/fellowshipsubspecialty/", name="fellowshipsubspecialtys_create")
     * @Route("/institutiontypes/", name="institutiontypes_create")
     * @Route("/documenttypes/", name="documenttypes_create")
     * @Method("POST")
     * @Template("OlegUserdirectoryBundle:ListForm:new.html.twig")
     */
    public function createAction(Request $request)
    {
        if( false === $this->get('security.context')->isGranted('ROLE_USERDIRECTORY_EDITOR') ) {
            return $this->redirect( $this->generateUrl($this->container->getParameter('employees.sitename').'-order-nopermission') );
        }

        return $this->createList($request);
    }
    public function createList($request) {
        $routeName = $request->get('_route');

        $pieces = explode("_", $routeName);
        $pathbase = $pieces[0];

        $mapper= $this->classListMapper($pathbase);

        $entityClass = $mapper['fullClassName'];    //"Oleg\\OrderformBundle\\Entity\\".$mapper['className'];

        $entity = new $entityClass();

        $form = $this->createCreateForm($entity,$mapper,$pathbase,'new');
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();

            //the date from the form does not contain time, so set createdate with date and time.
            $entity->setCreatedate(new \DateTime());

            $user = $this->get('security.context')->getToken()->getUser();
            $entity->setCreator($user);

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
    private function createCreateForm($entity,$mapper,$pathbase,$cycle=null)
    {
        $options = array();

        if( $cycle ) {
            $options['cycle'] = $cycle;
        }

        //use $timezone = $user->getTimezone(); ?
        $user = $this->get('security.context')->getToken()->getUser();
        $options['user'] = $user;

        $options['entity'] = $entity;

        $newForm = new GenericListType($options,$mapper);

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
     * @Route("/sourcesystems/new", name="sourcesystems_new")
     * @Route("/roles/new", name="role_new")
     * @Route("/departments/new", name="departments_new")
     * @Route("/divisions/new", name="divisions_new")
     * @Route("/services/new", name="services_new")
     * @Route("/institutions/new", name="institutions_new")
     * @Route("/states/new", name="states_new")
     * @Route("/countries/new", name="countries_new")
     * @Route("/board-certifications/new", name="boardcertifications_new")
     * @Route("/employment_terminations/new", name="employmentterminations_new")
     * @Route("/event-log-event-types/new", name="loggereventtypes_new")
     * @Route("/primary-public-userid-types/new", name="usernametypes_new")
     * @Route("/identifier-types/new", name="identifiers_new")
     * @Route("/residency-tracks/new", name="residencytracks_new")
     * @Route("/fellowship-types/new", name="fellowshiptypes_new")
//     * @Route("/research-labs/new", name="researchlabs_new")
     * @Route("/location-types/new", name="locationtypes_new")
     * @Route("/equipment/new", name="equipments_new")
     * @Route("/equipment-types/new", name="equipmenttypes_new")
     * @Route("/location-privacy-types/new", name="locationprivacy_new")
     * @Route("/role-attributes/new", name="roleattributes_new")
     * @Route("/buidlings/new", name="buildings_new")
     * @Route("/rooms/new", name="rooms_new")
     * @Route("/suites/new", name="suites_new")
     * @Route("/floors/new", name="floors_new")
     * @Route("/mailboxes/new", name="mailboxes_new")
     * @Route("/efforts/new", name="efforts_new")
     * @Route("/admintitles/new", name="admintitles_new")
     * @Route("/apptitles/new", name="apptitles_new")
     * @Route("/completionreasons/new", name="completionreasons_new")
     * @Route("/trainingdegrees/new", name="trainingdegrees_new")
     * @Route("/trainingmajors/new", name="trainingmajors_new")
     * @Route("/trainingminors/new", name="trainingminors_new")
     * @Route("/traininghonors/new", name="traininghonors_new")
     * @Route("/fellowshiptitles/new", name="fellowshiptitles_new")
     * @Route("/residencyspecialty/new", name="residencyspecialtys_new")
     * @Route("/fellowshipsubspecialty/new", name="fellowshipsubspecialtys_new")
     * @Route("/institutiontypes/new", name="institutiontypes_new")
     * @Route("/documenttypes/new", name="documenttypes_new")
     * @Method("GET")
     * @Template("OlegUserdirectoryBundle:ListForm:new.html.twig")
     */
    public function newAction(Request $request)
    {
        if( false === $this->get('security.context')->isGranted('ROLE_USERDIRECTORY_EDITOR') ) {
            return $this->redirect( $this->generateUrl($this->container->getParameter('employees.sitename').'-order-nopermission') );
        }

        return $this->newList($request);
    }
    public function newList($request,$pid=null) {
        $routeName = $request->get('_route');
        $pieces = explode("_", $routeName);
        $pathbase = $pieces[0];
        //echo "pathbase=".$pathbase."<br>";

        $em = $this->getDoctrine()->getManager();

        $mapper= $this->classListMapper($pathbase);

        $entityClass = $mapper['fullClassName'];    //"Oleg\\OrderformBundle\\Entity\\".$mapper['className'];

        $entity = new $entityClass();

        $user = $this->get('security.context')->getToken()->getUser();
        $entity->setCreatedate(new \DateTime());
        $entity->setType('user-added');
        $entity->setCreator($user);

        if( $pid ) {
            //echo "pid=".$pid."<br>";
            $parentNMapper = $this->getParentName($mapper['className']);
            $parent = $em->getRepository($parentNMapper['bundleName'].':'.$parentNMapper['className'])->find($pid);
            $entity->setParent($parent);
        }

        //get max orderinlist + 10
        $query = $em->createQuery('SELECT MAX(c.orderinlist) as maxorderinlist FROM '.$mapper['bundleName'].':'.$mapper['className'].' c');
        $nextorder = $query->getSingleResult()['maxorderinlist']+10;
        $entity->setOrderinlist($nextorder);

        $form   = $this->createCreateForm($entity,$mapper,$pathbase,'new');

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
     * @Route("/sourcesystems/{id}", name="sourcesystems_show")
     * @Route("/roles/{id}", name="role_show")
     * @Route("/departments/{id}", name="departments_show")
     * @Route("/divisions/{id}", name="divisions_show")
     * @Route("/services/{id}", name="services_show")
     * @Route("/institutions/{id}", name="institutions_show")
     * @Route("/states/{id}", name="states_show")
     * @Route("/countries/{id}", name="countries_show")
     * @Route("/board-certifications/{id}", name="boardcertifications_show")
     * @Route("/employment_terminations/{id}", name="employmentterminations_show")
     * @Route("/event-log-event-types/{id}", name="loggereventtypes_show")
     * @Route("/primary-public-userid-types/{id}", name="usernametypes_show")
     * @Route("/identifier-types/{id}", name="identifiers_show")
     * @Route("/residency-tracks/{id}", name="residencytracks_show")
     * @Route("/fellowship-types/{id}", name="fellowshiptypes_show")
//     * @Route("/research-labs/{id}", name="researchlabs_show")
     * @Route("/location-types/{id}", name="locationtypes_show")
     * @Route("/equipment/{id}", name="equipments_show")
     * @Route("/equipment-types/{id}", name="equipmenttypes_show")
     * @Route("/location-privacy-types/{id}", name="locationprivacy_show")
     * @Route("/role-attributes/{id}", name="roleattributes_show")
     * @Route("/buidlings/{id}", name="buildings_show")
     * @Route("/rooms/{id}", name="rooms_show")
     * @Route("/suites/{id}", name="suites_show")
     * @Route("/floors/{id}", name="floors_show")
     * @Route("/mailboxes/{id}", name="mailboxes_show")
     * @Route("/efforts/{id}", name="efforts_show")
     * @Route("/admintitles/{id}", name="admintitles_show")
     * @Route("/apptitles/{id}", name="apptitles_show")
     * @Route("/completionreasons/{id}", name="completionreasons_show")
     * @Route("/trainingdegrees/{id}", name="trainingdegrees_show")
     * @Route("/trainingmajors/{id}", name="trainingmajors_show")
     * @Route("/trainingminors/{id}", name="trainingminors_show")
     * @Route("/traininghonors/{id}", name="traininghonors_show")
     * @Route("/fellowshiptitles/{id}", name="fellowshiptitles_show")
     * @Route("/residencyspecialty/{id}", name="residencyspecialtys_show")
     * @Route("/fellowshipsubspecialty/{id}", name="fellowshipsubspecialtys_show")
     * @Route("/institutiontypes/{id}", name="institutiontypes_show")
     * @Route("/documenttypes/{id}", name="documenttypes_show")
     * @Method("GET")
     * @Template("OlegUserdirectoryBundle:ListForm:show.html.twig")
     */
    public function showAction(Request $request,$id)
    {

        if( false === $this->get('security.context')->isGranted('ROLE_USERDIRECTORY_OBSERVER') ) {
            return $this->redirect( $this->generateUrl($this->container->getParameter('employees.sitename').'-order-nopermission') );
        }

        return $this->showList($request,$id);
    }
    public function showList($request,$id) {
        $routeName = $request->get('_route');
        $pieces = explode("_", $routeName);
        $pathbase = $pieces[0];
        //echo "pathbase=".$pathbase."<br>";
        //exit('show');

        $em = $this->getDoctrine()->getManager();

        $mapper= $this->classListMapper($pathbase);

        $entity = $em->getRepository($mapper['bundleName'].':'.$mapper['className'])->find($id);
        //echo "entity=".$entity."<br>";
        $form = $this->createEditForm($entity,$mapper,$pathbase,'edit',true);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find '.$mapper['fullClassName'].' entity.');
        }

        //$deleteForm = $this->createDeleteForm($id,$pathbase);

        return array(
            'entity' => $entity,
            'edit_form' => $form->createView(),
            'delete_form' => null,  //$deleteForm->createView(),
            'displayName' => $mapper['displayName'],
            'pathbase' => $pathbase
        );
    }

    /**
     * Displays a form to edit an existing entity.
     *
     * @Route("/sourcesystems/{id}/edit", name="sourcesystems_edit")
     * @Route("/roles/{id}/edit", name="role_edit")
     * @Route("/departments/{id}/edit", name="departments_edit")
     * @Route("/divisions/{id}/edit", name="divisions_edit")
     * @Route("/services/{id}/edit", name="services_edit")
     * @Route("/institutions/{id}/edit", name="institutions_edit")
     * @Route("/states/{id}/edit", name="states_edit")
     * @Route("/countries/{id}/edit", name="countries_edit")
     * @Route("/board-certifications/{id}/edit", name="boardcertifications_edit")
     * @Route("/employment_terminations/{id}/edit", name="employmentterminations_edit")
     * @Route("/event-log-event-types/{id}/edit", name="loggereventtypes_edit")
     * @Route("/primary-public-userid-types/{id}/edit", name="usernametypes_edit")
     * @Route("/identifier-types/{id}/edit", name="identifiers_edit")
     * @Route("/residency-tracks/{id}/edit", name="residencytracks_edit")
     * @Route("/fellowship-types/{id}/edit", name="fellowshiptypes_edit")
//     * @Route("/research-labs/{id}/edit", name="researchlabs_edit")
     * @Route("/location-types/{id}/edit", name="locationtypes_edit")
     * @Route("/equipment/{id}/edit", name="equipments_edit")
     * @Route("/equipment-types/{id}/edit", name="equipmenttypes_edit")
     * @Route("/location-privacy-types/{id}/edit", name="locationprivacy_edit")
     * @Route("/role-attributes/{id}/edit", name="roleattributes_edit")
     * @Route("/buidlings/{id}/edit", name="buildings_edit")
     * @Route("/rooms/{id}/edit", name="rooms_edit")
     * @Route("/suites/{id}/edit", name="suites_edit")
     * @Route("/floors/{id}/edit", name="floors_edit")
     * @Route("/mailboxes/{id}/edit", name="mailboxes_edit")
     * @Route("/efforts/{id}/edit", name="efforts_edit")
     * @Route("/admintitles/{id}/edit", name="admintitles_edit")
     * @Route("/apptitles/{id}/edit", name="apptitles_edit")
     * @Route("/completionreasons/{id}/edit", name="completionreasons_edit")
     * @Route("/trainingdegrees/{id}/edit", name="trainingdegrees_edit")
     * @Route("/trainingmajors/{id}/edit", name="trainingmajors_edit")
     * @Route("/trainingminors/{id}/edit", name="trainingminors_edit")
     * @Route("/traininghonors/{id}/edit", name="traininghonors_edit")
     * @Route("/fellowshiptitles/{id}/edit", name="fellowshiptitles_edit")
     * @Route("/residencyspecialty/{id}/edit", name="residencyspecialtys_edit")
     * @Route("/fellowshipsubspecialty/{id}/edit", name="fellowshipsubspecialtys_edit")
     * @Route("/institutiontypes/{id}/edit", name="institutiontypes_edit")
     * @Route("/documenttypes/{id}/edit", name="documenttypes_edit")
     * @Method("GET")
     * @Template("OlegUserdirectoryBundle:ListForm:edit.html.twig")
     */
    public function editAction(Request $request,$id)
    {
        if( false === $this->get('security.context')->isGranted('ROLE_USERDIRECTORY_EDITOR') ) {
            return $this->redirect( $this->generateUrl($this->container->getParameter('employees.sitename').'-order-nopermission') );
        }

        return $this->editList($request,$id);
    }
    function editList($request,$id) {
        $routeName = $request->get('_route');
        $pieces = explode("_", $routeName);
        $pathbase = $pieces[0];

        $em = $this->getDoctrine()->getManager();

        $mapper= $this->classListMapper($pathbase);

        $entity = $em->getRepository($mapper['bundleName'].':'.$mapper['className'])->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find '.$mapper['fullClassName'].' entity.');
        }

        $editForm = $this->createEditForm($entity,$mapper,$pathbase,'edit');
        $deleteForm = $this->createDeleteForm($id,$pathbase);

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
            'displayName' => $mapper['displayName'],
            'pathbase' => $pathbase
        );
    }

    /**
    * Creates a form to edit an entity.
    * @param $entity
    * @return \Symfony\Component\Form\Form The form
    */
    private function createEditForm($entity,$mapper,$pathbase,$cycle,$disabled=false)
    {

        $options = array();

        $options['id'] = $entity->getId();

        if( $cycle ) {
            $options['cycle'] = $cycle;
        }

        //use $timezone = $user->getTimezone(); ?
        $user = $this->get('security.context')->getToken()->getUser();
        $options['user'] = $user;

        $options['entity'] = $entity;

        $newForm = new GenericListType($options,$mapper);

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
     * @Route("/sourcesystems/{id}", name="sourcesystems_update")
     * @Route("/roles/{id}", name="role_update")
     * @Route("/departments/{id}", name="departments_update")
     * @Route("/divisions/{id}", name="divisions_update")
     * @Route("/services/{id}", name="services_update")
     * @Route("/institutions/{id}", name="institutions_update")
     * @Route("/states/{id}", name="states_update")
     * @Route("/countries/{id}", name="countries_update")
     * @Route("/board-certifications/{id}", name="boardcertifications_update")
     * @Route("/employment_terminations/{id}", name="employmentterminations_update")
     * @Route("/event-log-event-types/{id}", name="loggereventtypes_update")
     * @Route("/primary-public-userid-types/{id}", name="usernametypes_update")
     * @Route("/identifier-types/{id}", name="identifiers_update")
     * @Route("/residency-tracks/{id}", name="residencytracks_update")
     * @Route("/fellowship-types/{id}", name="fellowshiptypes_update")
//     * @Route("/research-labs/{id}", name="researchlabs_update")
     * @Route("/location-types/{id}", name="locationtypes_update")
     * @Route("/equipment/{id}", name="equipments_update")
     * @Route("/equipment-types/{id}", name="equipmenttypes_update")
     * @Route("/location-privacy-types/{id}", name="locationprivacy_update")
     * @Route("/role-attributes/{id}", name="roleattributes_update")
     * @Route("/buidlings/{id}", name="buildings_update")
     * @Route("/rooms/{id}", name="rooms_update")
     * @Route("/suites/{id}", name="suites_update")
     * @Route("/floors/{id}", name="floors_update")
     * @Route("/mailboxes/{id}", name="mailboxes_update")
     * @Route("/efforts/{id}", name="efforts_update")
     * @Route("/admintitles/{id}", name="admintitles_update")
     * @Route("/apptitles/{id}", name="apptitles_update")
     * @Route("/completionreasons/{id}", name="completionreasons_update")
     * @Route("/trainingdegrees/{id}", name="trainingdegrees_update")
     * @Route("/trainingmajors/{id}", name="trainingmajors_update")
     * @Route("/trainingminors/{id}", name="trainingminors_update")
     * @Route("/traininghonors/{id}", name="traininghonors_update")
     * @Route("/fellowshiptitles/{id}", name="fellowshiptitles_update")
     * @Route("/residencyspecialty/{id}", name="residencyspecialtys_update")
     * @Route("/fellowshipsubspecialty/{id}", name="fellowshipsubspecialtys_update")
     * @Route("/institutiontypes/{id}", name="institutiontypes_update")
     * @Route("/documenttypes/{id}", name="documenttypes_update")
     * @Method("PUT")
     * @Template("OlegUserdirectoryBundle:ListForm:edit.html.twig")
     */
    public function updateAction(Request $request, $id)
    {
        if( false === $this->get('security.context')->isGranted('ROLE_USERDIRECTORY_EDITOR') ) {
            return $this->redirect( $this->generateUrl($this->container->getParameter('employees.sitename').'-order-nopermission') );
        }

        return $this->updateList($request, $id);
    }
    public function updateList($request, $id) {
        $routeName = $request->get('_route');
        $pieces = explode("_", $routeName);
        $pathbase = $pieces[0];

        $em = $this->getDoctrine()->getManager();

        $mapper= $this->classListMapper($pathbase);

        $entity = $em->getRepository($mapper['bundleName'].':'.$mapper['className'])->find($id);

        //save array of synonyms
        $beforeformSynonyms = clone $entity->getSynonyms();

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find '.$mapper['fullClassName'].' entity.');
        }

        $deleteForm = $this->createDeleteForm($id,$pathbase);
        $editForm = $this->createEditForm($entity,$mapper,$pathbase,'edit_put_list');
        $editForm->handleRequest($request);

        if( $editForm->isValid() ) {

            //make sure to keep creator and creation date from original entity, according to the requirements (Issue#250):
            //For "Creation Date", "Creator" these variables should not be modifiable via the form even if the user unlocks these fields in the browser.
            $originalEntity = $em->getRepository($mapper['bundleName'].':'.$mapper['className'])->find($id);
            $entity->setCreator($originalEntity->getCreator());
            $entity->setCreatedate($originalEntity->getCreatedate());

            $user = $this->get('security.context')->getToken()->getUser();
            $entity->setUpdatedby($user);
            //$entity->setUpdatedon(new \DateTime());
            $entity->setUpdateAuthorRoles($user->getRoles());

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







    //////////////////////// Tree //////////////////////////////
    /**
     * @Route("/institutions-tree-management/", name="institutions-tree-management")
     * @Method("GET")
     * @Template("OlegUserdirectoryBundle:Tree:index.html.twig")
     */
    public function indexTreeAction(Request $request)
    {
        //exit('tree');
        return array(
            'name' => 'Institution'
        );
    }

    /**
     * Displays a form to create a new entity with parent.
     *
     * @Route("/department/new/parent/{pid}", name="departments_new_with_parent")
     * @Route("/division/new/parent/{pid}", name="divisions_new_with_parent")
     * @Route("/service/new/parent/{pid}", name="services_new_with_parent")
     * @Route("/service/new/parent/{pid}", name="services_new_with_parent")
     * @Method("GET")
     * @Template("OlegUserdirectoryBundle:ListForm:new.html.twig")
     */
    public function newNodeWithParentAction(Request $request,$pid)
    {
        return $this->newList($request,$pid);
    }

    public function getParentName( $className ) {

        //echo "className=".$className."<br>";

        switch( $className ) {
            case "Department":
                $parentClassName = "Institution";
                break;
            case "Division":
                $parentClassName = "Department";
                break;
            case "Service":
                $parentClassName = "Division";
                break;
            case "FellowshipSubspecialty":
                $parentClassName = "ResidencySpecialty";
                break;
            default:
                //$parentClassName = null;
                return null;
        }

        $res = array();
        $res['className'] = $parentClassName;
        $res['fullClassName'] = "Oleg\\UserdirectoryBundle\\Entity\\".$className;
        $res['bundleName'] = "OlegUserdirectoryBundle";

        return $res;
    }
    //////////////////////// EOF tree //////////////////////////////











    public function classListMapper( $route ) {

        switch( $route ) {

            case "sourcesystems":
                $className = "SourceSystemList";
                $displayName = "Source Systems";
                break;
            case "role":
                $className = "Roles";
                $displayName = "Roles";
                break;
            case "institutions":
                $className = "Institution";
                $displayName = "Institutions";
                break;
            case "departments":
                $className = "Department";
                $displayName = "Departments";
                break;
            case "divisions":
                $className = "Division";
                $displayName = "Divisions";
                break;
            case "services":
                $className = "Service";
                $displayName = "Services";
                break;
            case "states":
                $className = "States";
                $displayName = "States";
                break;
            case "countries":
                $className = "Countries";
                $displayName = "Countries";
                break;
            case "boardcertifications":
                $className = "BoardCertifiedSpecialties";
                $displayName = "Pathology Board Certified Specialties";
                break;
            case "employmentterminations":
                $className = "EmploymentTerminationType";
                $displayName = "Employment Types of Termination";
                break;
            case "loggereventtypes":
                $className = "EventTypeList";
                $displayName = "Event Log Types";
                break;
            case "usernametypes":
                $className = "UsernameType";
                $displayName = "Primary Public User ID Types";
                break;
            case "identifiers":
                $className = "IdentifierTypeList";
                $displayName = "Identifier Types";
                break;
            case "residencytracks":
                $className = "ResidencyTrackList";
                $displayName = "Residency Tracks";
                break;
            case "fellowshiptypes":
                $className = "FellowshipTypeList";
                $displayName = "Fellowship Types";
                break;
//            case "researchlabs":
//                $className = "ResearchLab";
//                $displayName = "Research Labs";
//                break;
            case "locationtypes":
                $className = "LocationTypeList";
                $displayName = "Location Types";
                break;
            case "equipments":
                $className = "Equipment";
                $displayName = "Equipment";
                break;
            case "equipmenttypes":
                $className = "EquipmentType";
                $displayName = "Equipment Types";
                break;
            case "locationprivacy":
                $className = "LocationPrivacyList";
                $displayName = "Location Privacy Types";
                break;
            case "roleattributes":
                $className = "RoleAttributeList";
                $displayName = "Role Attributes";
                break;
            case "buildings":
                $className = "BuildingList";
                $displayName = "Buildings";
                break;
            case "rooms":
                $className = "RoomList";
                $displayName = "Rooms";
                break;
            case "suites":
                $className = "SuiteList";
                $displayName = "Suites";
                break;
            case "floors":
                $className = "FloorList";
                $displayName = "Floors";
                break;
            case "mailboxes":
                $className = "MailboxList";
                $displayName = "Mailboxes";
                break;
            case "efforts":
                $className = "EffortList";
                $displayName = "Percent Efforts";
                break;
            case "admintitles":
                $className = "AdminTitleList";
                $displayName = "Administrative Titles";
                break;
            case "apptitles":
                $className = "AppTitleList";
                $displayName = "Academic Appointment Titles";
                break;
            case "completionreasons":
                $className = "CompletionReasonList";
                $displayName = "Completion Reasons";
                break;
            case "trainingdegrees":
                $className = "TrainingDegreeList";
                $displayName = "Training Degrees";
                break;
            case "trainingmajors":
                $className = "MajorTrainingList";
                $displayName = "Training Majors";
                break;
            case "trainingminors":
                $className = "MinorTrainingList";
                $displayName = "Training Minors";
                break;
            case "traininghonors":
                $className = "HonorTrainingList";
                $displayName = "Training Honors";
                break;
            case "fellowshiptitles":
                $className = "FellowshipTitleList";
                $displayName = "Professional Fellowship Titles";
                break;
            case "residencyspecialtys":
                $className = "ResidencySpecialty";
                $displayName = "Residency Specialties";
                break;
            case "fellowshipsubspecialtys":
                $className = "FellowshipSubspecialty";
                $displayName = "Fellowship Subspecialties";
                break;
            case "institutiontypes":
                $className = "InstitutionType";
                $displayName = "Institution Types";
                break;
            case "documenttypes":
                $className = "DocumentTypeList";
                $displayName = "Document Types";
                break;

            default:
                $className = null;
                $displayName = null;
        }

        //echo "className=".$className.", displayName=".$displayName."<br>";

        $res = array();
        $res['className'] = $className;
        $res['fullClassName'] = "Oleg\\UserdirectoryBundle\\Entity\\".$className;
        $res['bundleName'] = "OlegUserdirectoryBundle";
        $res['displayName'] = $displayName;

        //check parent name
        $parentMapper = $this->getParentName($className);
        if( $parentMapper ) {
            $res['parentClassName'] = $parentMapper['className'];
        }

        return $res;
    }



    /////////////////// DELETE IS NOT USED /////////////////////////
    /**
     * Deletes a entity.
     *
     * @Route("/sourcesystems/{id}", name="sourcesystems_delete")
     * @Route("/roles/{id}", name="role_delete")
     * @Route("/departments/{id}", name="departments_delete")
     * @Route("/divisions/{id}", name="divisions_delete")
     * @Route("/services/{id}", name="services_delete")
     * @Route("/institutions/{id}", name="institutions_delete")
     * @Route("/states/{id}", name="states_delete")
     * @Route("/countries/{id}", name="countries_delete")
     * @Route("/board-certifications/{id}", name="boardcertifications_delete")
     * @Route("/employment_terminations/{id}", name="employmentterminations_delete")
     * @Route("/event-log-event-types/{id}", name="loggereventtypes_delete")
     * @Route("/primary-public-userid-types/{id}", name="usernametypes_delete")
     * @Route("/identifier-types/{id}", name="identifiers_delete")
     * @Route("/residency-tracks/{id}", name="residencytracks_delete")
     * @Route("/fellowship-types/{id}", name="fellowshiptypes_delete")
//     * @Route("/research-labs/{id}", name="researchlabs_delete")
     * @Route("/location-types/{id}", name="locationtypes_delete")
     * @Route("/equipment/{id}", name="equipments_delete")
     * @Route("/equipment-types/{id}", name="equipmenttypes_delete")
     * @Route("/location-privacy-types/{id}", name="locationprivacy_delete")
     * @Route("/role-attributes/{id}", name="roleattributes_delete")
     * @Route("/buidlings/{id}", name="buildings_delete")
     * @Route("/rooms/{id}", name="rooms_delete")
     * @Route("/suites/{id}", name="suites_delete")
     * @Route("/floors/{id}", name="floors_delete")
     * @Route("/mailboxes/{id}", name="mailboxes_delete")
     * @Route("/efforts/{id}", name="efforts_delete")
     * @Route("/admintitles/{id}", name="admintitles_delete")
     * @Route("/apptitles/{id}", name="apptitles_delete")
     * @Route("/completionreasons/{id}", name="completionreasons_delete")
     * @Route("/trainingdegrees/{id}", name="trainingdegrees_delete")
     * @Route("/trainingmajors/{id}", name="trainingmajors_delete")
     * @Route("/trainingminors/{id}", name="trainingminors_delete")
     * @Route("/traininghonors/{id}", name="traininghonors_delete")
     * @Route("/fellowshiptitles/{id}", name="fellowshiptitles_delete")
     * @Route("/residencyspecialty/{id}", name="residencyspecialtys_delete")
     * @Route("/fellowshipsubspecialty/{id}", name="fellowshipsubspecialtys_delete")
     * @Route("/institutiontypes/{id}", name="institutiontypes_delete")
     * @Route("/documenttypes/{id}", name="documenttypes_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, $id)
    {

        return $this->redirect( $this->generateUrl($this->container->getParameter('employees.sitename').'-order-nopermission') );
        //return $this->deleteList($request, $id);
    }
    public function deleteList($request, $id) {

        $routeName = $request->get('_route');
        $pieces = explode("_", $routeName);
        $pathbase = $pieces[0];

        $mapper= $this->classListMapper($pathbase);

        $form = $this->createDeleteForm($id,$pathbase);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository($mapper['bundleName'].':'.$mapper['className'])->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find '.$mapper['fullClassName'].' entity.');
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
    protected function createDeleteForm($id,$pathbase)
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
