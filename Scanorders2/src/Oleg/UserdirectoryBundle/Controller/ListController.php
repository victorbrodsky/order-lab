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
     * @Route("/roles/", name="role-list")
     * @Route("/institutions/", name="institutions-list")
     * @Route("/departments/", name="departments-list")
     * @Route("/states/", name="states-list")
     * @Route("/board-certifications/", name="boardcertifications-list")
     * @Route("/employment_terminations/", name="employmentterminations-list")
     * @Route("/event-log-event-types/", name="loggereventtypes-list")
     * @Route("/primary-public-userid-types/", name="usernametypes-list")
     * @Route("/identifier-types/", name="identifiers-list")
     * @Route("/residency-tracks/", name="residencytracks-list")
     * @Route("/fellowship-types/", name="fellowshiptypes-list")
     * @Route("/research-lab-titles/", name="researchlabtitles-list")
     * @Method("GET")
     * @Template("OlegUserdirectoryBundle:ListForm:index.html.twig")
     */
    public function indexAction(Request $request)
    {
        return $this->getList($request);
    }
    public function getList($request) {
        $type = $request->get('_route');

        //get object name: stain-list => stain
        $pieces = explode("-", $type);
        $pathbase = $pieces[0];

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

        if( method_exists($entityClass,'getSynonyms') ) {
            //echo "synonyms exists! <br>";
            $dql->leftJoin("ent.synonyms", "synonyms");
            $dql->addGroupBy('synonyms.name');
            $dql->leftJoin("ent.original", "original");
            $dql->addGroupBy('original.name');
        } else {
            //echo "no synonyms! <br>";
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
     * @Route("/roles/", name="role_create")
     * @Route("/institutions/", name="institutions_create")
     * @Route("/departments/", name="departments_create")
     * @Route("/states/", name="states_create")
     * @Route("/board-certifications/", name="boardcertifications_create")
     * @Route("/employment_terminations/", name="employmentterminations_create")
     * @Route("/event-log-event-types/", name="loggereventtypes_create")
     * @Route("/primary-public-userid-types/", name="usernametypes_create")
     * @Route("/identifier-types/", name="identifiers_create")
     * @Route("/residency-tracks/", name="residencytracks_create")
     * @Route("/fellowship-types/", name="fellowshiptypes_create")
     * @Route("/research-lab-titles/", name="researchlabtitles_create")
     * @Method("POST")
     * @Template("OlegUserdirectoryBundle:ListForm:new.html.twig")
     */
    public function createAction(Request $request)
    {
        return $this->createList($request);
    }
    public function createList($request) {
        $routeName = $request->get('_route');
        //exit("routeName=".$routeName); //mrntype

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
    private function createCreateForm($entity,$mapper,$pathbase,$cicle=null)
    {
        $options = array();

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

        $newForm = new GenericListType($options, $mapper);

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
     * @Route("/roles/new", name="role_new")
     * @Route("/departments/new", name="departments_new")
     * @Route("/institutions/new", name="institutions_new")
     * @Route("/states/new", name="states_new")
     * @Route("/board-certifications/new", name="boardcertifications_new")
     * @Route("/employment_terminations/new", name="employmentterminations_new")
     * @Route("/event-log-event-types/new", name="loggereventtypes_new")
     * @Route("/primary-public-userid-types/new", name="usernametypes_new")
     * @Route("/identifier-types/new", name="identifiers_new")
     * @Route("/residency-tracks/new", name="residencytracks_new")
     * @Route("/fellowship-types/new", name="fellowshiptypes_new")
     * @Route("/research-lab-titles/new", name="researchlabtitles_new")
     * @Method("GET")
     * @Template("OlegUserdirectoryBundle:ListForm:new.html.twig")
     */
    public function newAction(Request $request)
    {
        return $this->newList($request);
    }
    public function newList($request) {
        $routeName = $request->get('_route');
        $pieces = explode("_", $routeName);
        $pathbase = $pieces[0];
        //echo "type=".$type."<br>";

        $mapper= $this->classListMapper($pathbase);

        $entityClass = $mapper['fullClassName'];    //"Oleg\\OrderformBundle\\Entity\\".$mapper['className'];

        $entity = new $entityClass();

        $user = $this->get('security.context')->getToken()->getUser();
        $entity->setCreatedate(new \DateTime());
        $entity->setType('user-added');
        $entity->setCreator($user);

        //$entity->setUpdatedby($user);
        //$entity->setUpdatedon(new \DateTime());

        //get max orderinlist + 10
        $em = $this->getDoctrine()->getManager();
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
     * @Route("/roles/{id}", name="role_show")
     * @Route("/departments/{id}", name="departments_show")
     * @Route("/institutions/{id}", name="institutions_show")
     * @Route("/states/{id}", name="states_show")
     * @Route("/board-certifications/{id}", name="boardcertifications_show")
     * @Route("/employment_terminations/{id}", name="employmentterminations_show")
     * @Route("/event-log-event-types/{id}", name="loggereventtypes_show")
     * @Route("/primary-public-userid-types/{id}", name="usernametypes_show")
     * @Route("/identifier-types/{id}", name="identifiers_show")
     * @Route("/residency-tracks/{id}", name="residencytracks_show")
     * @Route("/fellowship-types/{id}", name="fellowshiptypes_show")
     * @Route("/research-lab-titles/{id}", name="researchlabtitles_show")
     * @Method("GET")
     * @Template("OlegUserdirectoryBundle:ListForm:show.html.twig")
     */
    public function showAction(Request $request,$id)
    {
        return $this->showList($request,$id);
    }
    public function showList($request,$id) {
        $routeName = $request->get('_route');
        $pieces = explode("_", $routeName);
        $pathbase = $pieces[0];
        //echo "pathbase=".$pathbase."<br>";

        $em = $this->getDoctrine()->getManager();

        $mapper= $this->classListMapper($pathbase);

        $entity = $em->getRepository($mapper['bundleName'].':'.$mapper['className'])->find($id);
        $form = $this->createEditForm($entity,$mapper,$pathbase,'edit',true);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find '.$mapper['fullClassName'].' entity.');
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
     * @Route("/roles/{id}/edit", name="role_edit")
     * @Route("/departments/{id}/edit", name="departments_edit")
     * @Route("/institutions/{id}/edit", name="institutions_edit")
     * @Route("/states/{id}/edit", name="states_edit")
     * @Route("/board-certifications/{id}/edit", name="boardcertifications_edit")
     * @Route("/employment_terminations/{id}/edit", name="employmentterminations_edit")
     * @Route("/event-log-event-types/{id}/edit", name="loggereventtypes_edit")
     * @Route("/primary-public-userid-types/{id}/edit", name="usernametypes_edit")
     * @Route("/identifier-types/{id}/edit", name="identifiers_edit")
     * @Route("/residency-tracks/{id}/edit", name="residencytracks_edit")
     * @Route("/fellowship-types/{id}/edit", name="fellowshiptypes_edit")
     * @Route("/research-lab-titles/{id}/edit", name="researchlabtitles_edit")
     * @Method("GET")
     * @Template("OlegUserdirectoryBundle:ListForm:edit.html.twig")
     */
    public function editAction(Request $request,$id)
    {
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
    private function createEditForm($entity,$mapper,$pathbase,$cicle,$disabled=false)
    {

        $options = array();

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
     * @Route("/roles/{id}", name="role_update")
     * @Route("/departments/{id}", name="departments_update")
     * @Route("/institutions/{id}", name="institutions_update")
     * @Route("/states/{id}", name="states_update")
     * @Route("/board-certifications/{id}", name="boardcertifications_update")
     * @Route("/employment_terminations/{id}", name="employmentterminations_update")
     * @Route("/event-log-event-types/{id}", name="loggereventtypes_update")
     * @Route("/primary-public-userid-types/{id}", name="usernametypes_update")
     * @Route("/identifier-types/{id}", name="identifiers_update")
     * @Route("/residency-tracks/{id}", name="residencytracks_update")
     * @Route("/fellowship-types/{id}", name="fellowshiptypes_update")
     * @Route("/research-lab-titles/{id}", name="researchlabtitles_update")
     * @Method("PUT")
     * @Template("OlegUserdirectoryBundle:ListForm:edit.html.twig")
     */
    public function updateAction(Request $request, $id)
    {
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
        if( method_exists($entity,'getSynonyms') && $entity->getSynonyms() ) {
            $beforeformSynonyms = clone $entity->getSynonyms();
        }

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find '.$mapper['fullClassName'].' entity.');
        }

        $deleteForm = $this->createDeleteForm($id,$pathbase);
        $editForm = $this->createEditForm($entity,$mapper,$pathbase,'edit');
        $editForm->handleRequest($request);

        if( $editForm->isValid() ) {

            //make sure to keep creator and creation date from original entity, according to the requirements (Issue#250):
            //For "Creation Date", "Creator" these variables should not be modifiable via the form even if the user unlocks these fields in the browser.
            $originalEntity = $em->getRepository($mapper['bundleName'].':'.$mapper['className'])->find($id);
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

        case "role":
            $className = "roles";
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
        case "states":
            $className = "States";
            $displayName = "States";
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
        case "researchlabtitles":
            $className = "ResearchLabTitleList";
            $displayName = "Research Lab Titles";
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

        return $res;
    }

    /////////////////// DELETE IS NOT USED /////////////////////////
    /**
     * Deletes a entity.
     *
     * @Route("/roles/{id}", name="role_delete")
     * @Route("/departments/{id}", name="departments_delete")
     * @Route("/institutions/{id}", name="institutions_delete")
     * @Route("/states/{id}", name="states_delete")
     * @Route("/board-certifications/{id}", name="boardcertifications_delete")
     * @Route("/employment_terminations/{id}", name="employmentterminations_delete")
     * @Route("/event-log-event-types/{id}", name="loggereventtypes_delete")
     * @Route("/primary-public-userid-types/{id}", name="usernametypes_delete")
     * @Route("/identifier-types/{id}", name="identifiers_delete")
     * @Route("/residency-tracks/{id}", name="residencytracks_delete")
     * @Route("/fellowship-types/{id}", name="fellowshiptypes_delete")
     * @Route("/research-lab-titles/{id}", name="researchlabtitles_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, $id)
    {
        return $this->deleteList($request, $id);
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
