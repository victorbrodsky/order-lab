<?php

namespace Oleg\UserdirectoryBundle\Controller;

use Oleg\UserdirectoryBundle\Form\LoggerFilterType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Oleg\UserdirectoryBundle\Entity\Logger;
use Oleg\UserdirectoryBundle\Form\LoggerType;

/**
 * Logger controller.
 *
 * @Route("/event-log")
 */
class LoggerController extends Controller
{

    /**
     * Lists audit log for a specific user
     *
     * @Route("/user/{id}", name="employees_logger_user_with_id")
     * @Route("/user", name="employees_logger_user")
     * @Method("GET")
     * @Template("OlegUserdirectoryBundle:Logger:logger_object.html.twig")
     */
    public function getAuditLogAction(Request $request)
    {       
        
        $postData = $request->get('postData');
        $userid = $request->get('id');
        $onlyheader = $request->get('onlyheader');

        //echo "postData=<br>";
        //print_r($postData);

        $entityName = 'User';

        $params = array(
            'sitename'=>$this->container->getParameter('employees.sitename'),
            'entityNamespace'=>'Oleg\UserdirectoryBundle\Entity',
            'entityName'=>$entityName,
            'entityId'=>$userid,
            'postData'=>$postData,
            'onlyheader'=>true
        );

        $logger =  $this->listLogger($params,$request);

        return $logger;
    }

    /**
     * @Route("/user/{id}/all", name="employees_logger_user_all")
     * @Method("GET")
     * @Template("OlegUserdirectoryBundle:Logger:index.html.twig")
     */
    public function getAuditLogAllAction(Request $request)
    {
        $postData = $request->get('postData');
        $userid = $request->get('id');
        //$onlyheader = $request->get('onlyheader');

        //echo "postData=<br>";
        //print_r($postData);

        $entityName = 'User';

        $params = array(
            'sitename'=>$this->container->getParameter('employees.sitename'),
            'entityNamespace'=>'Oleg\UserdirectoryBundle\Entity',
            'entityName'=>$entityName,
            'entityId'=>$userid,
            'postData'=>$postData,
            'onlyheader'=>false,
            'allsites'=>true
        );

        $logger =  $this->listLogger($params,$request);

        return $logger;
    }


    /**
     * Lists all Logger entities.
     *
     * @Route("/", name="employees_logger")
     * @Method("GET")
     * @Template("OlegUserdirectoryBundle:Logger:index.html.twig")
     */
    public function indexAction(Request $request)
    {
        $params = array(
            'sitename'=>$this->container->getParameter('employees.sitename')
        );
        return $this->listLogger($params,$request);
    }


    protected function listLogger( $params, $request ) {

        $sitename = ( array_key_exists('sitename', $params) ? $params['sitename'] : null);
        $allsites = ( array_key_exists('allsites', $params) ? $params['allsites'] : null);
        $entityNamespace = ( array_key_exists('entityNamespace', $params) ? $params['entityNamespace'] : null);
        $entityName = ( array_key_exists('entityName', $params) ? $params['entityName'] : null);
        $entityId = ( array_key_exists('entityId', $params) ? $params['entityId'] : null);
        $postData = ( array_key_exists('postData', $params) ? $params['postData'] : null);
        $onlyheader = ( array_key_exists('onlyheader', $params) ? $params['onlyheader'] : null);

        $em = $this->getDoctrine()->getManager();

        $roles = $em->getRepository('OlegUserdirectoryBundle:Roles')->findAll();
        $rolesArr = array();
        if( $this->get('security.context')->isGranted('ROLE_SCANORDER_ADMIN') ) {
            foreach( $roles as $role ) {
                $rolesArr[$role->getName()] = $role->getAlias();
            }
        }

        $repository = $this->getDoctrine()->getRepository('OlegUserdirectoryBundle:Logger');
        $dql =  $repository->createQueryBuilder("logger");
        $dql->select('logger');
        $dql->innerJoin('logger.eventType', 'eventType');

        if( $allsites == null || $allsites == false ) {
            $dql->where("logger.siteName = '".$sitename."'");
        }

        $filterform = $this->processLoggerFilter($dql,$request);

        $createLogger = null;
        $updateLogger = null;

        //get only specific object log
        if( $entityNamespace && $entityName && $entityId ) {
            //'Oleg\UserdirectoryBundle\Entity'
            //$namepartsArr = explode("\\", $entityNamespace);
            //$repName = $namepartsArr[0].$namepartsArr[1];
            //echo "entityNamespace=".$entityNamespace."<br>";
            //echo "0=".$namepartsArr[0]."<br>";
            //$subjectUser = $em->getRepository($repName.':'.$entityName)->find($entityId);

            $dql->andWhere('logger.entityNamespace = :entityNamespace');
            $dql->andWhere('logger.entityName = :entityName');
            $dql->andWhere('logger.entityId = :entityId');

            if( $onlyheader ) {

                /////////////// get created info ///////////////
                $dql2 = clone $dql;
                $dql2->andWhere("eventType.name = 'User Created'");
                $dql2->orderBy("logger.id","ASC");
                //echo "dql2=".$dql2."<br>";

                $query2 = $em->createQuery($dql2);
                $query2->setParameters( array( 'entityNamespace'=>$entityNamespace, 'entityName'=>$entityName, 'entityId'=>$entityId ) );
                $query2->setMaxResults(1);

                $loggers = $query2->getResult();
                //echo "logger count=".count($loggers)."<br>";
                if( count($loggers) > 0 ) {
                    $createLogger = $loggers[0];
                    //echo "logger id=".$createLogger->getId()."<br>";
                    //echo "logger eventType=".$createLogger->getEventType()->getName()."<br>";
                }

                /////////////// get updated info ///////////////
                $dql3 = clone $dql;
                $dql3->andWhere("eventType.name = 'User Updated'");
                $dql3->orderBy("logger.id","DESC");
                //echo "dql2=".$dql3."<br>";

                $query3 = $em->createQuery($dql3);
                $query3->setParameters( array( 'entityNamespace'=>$entityNamespace, 'entityName'=>$entityName, 'entityId'=>$entityId ) );
                $query3->setMaxResults(1);
                $loggers = $query3->getResult();
                //echo "logger count=".count($loggers)."<br>";
                if( count($loggers) > 0 ) {
                    $updateLogger = $loggers[0];
                }

                return array(
                    'roles' => $rolesArr,
                    'sitename' => $sitename,
                    'createLogger' => $createLogger,
                    'updateLogger' => $updateLogger
                );

            } //if onlyheader

        } //if entityNamespace entityName entityId

        if( $postData == null ) {
		    $request = $this->get('request');
		    $postData = $request->query->all();
        }

		if( !isset($postData['sort']) ) { 
			$dql->orderBy("logger.creationdate","DESC");
		}

		//pass sorting parameters directly to query; Somehow, knp_paginator stoped correctly create pagination according to sorting parameters       
//		if( isset($postData['sort']) ) {
//            $dql = $dql . " ORDER BY $postData[sort] $postData[direction]";
//        }
		
        $limit = 30;
        $query = $em->createQuery($dql);

        if( $entityNamespace && $entityName && $entityId ) {
            $query->setParameters( array( 'entityNamespace'=>$entityNamespace, 'entityName'=>$entityName, 'entityId'=>$entityId ) );
        }

        $paginator  = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $query,
            $this->get('request')->query->get('page', 1), /*page number*/
            $limit/*limit per page*/
        );



        return array(
            'loggerfilter' => $filterform->createView(),
            'pagination' => $pagination,
            'roles' => $rolesArr,
            'sitename' => $sitename,
            'createLogger' => $createLogger,
            'updateLogger' => $updateLogger
        );
    }




    public function processLoggerFilter( $dql, $request ) {

        $params = array();

        //Start Date, Start Time, End Date, End Time, User [Select2 dropdown), Event Type [Entity Updated], [Free Text Search value for Event column] [Filter Button]
        $filterform = $this->createForm(new LoggerFilterType($params), null);

        $filterform->bind($request);

        $creationdate = $filterform['creationdate']->getData();
        $search = $filterform['search']->getData();
        $user = $filterform['user']->getData();
        $eventType = $filterform['eventType']->getData();

        //echo "user=".$user."<br>";
        //echo "search=".$search."<br>";

        return $filterform;
    }






    //////////////// Currently not used ////////////////////
    /**
     * Creates a new Logger entity.
     *
     * @Route("/", name="employees_logger_create")
     * @Method("POST")
     * @Template("OlegUserdirectoryBundle:Logger:new.html.twig")
     */
    public function createAction(Request $request)
    {
        return $this->createLogger($request,$this->container->getParameter('employees.sitename'));
    }

    protected function createLogger(Request $request, $sitename) {
        $entity = new Logger($sitename);
        $form = $this->createCreateForm($entity, $sitename);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('logger_show', array('id' => $entity->getId())));
        }

        $this->get('session')->getFlashBag()->add(
            'notice',
            'Failed to create log'
        );

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
            'sitename' => $sitename
        );
    }

    /**
     * Creates a form to create a Logger entity.
     *
     * @param Logger $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    protected function createCreateForm(Logger $entity, $sitename)
    {
        $form = $this->createForm(new LoggerType(), $entity, array(
            'action' => $this->generateUrl($sitename.'_logger_create'),
            'method' => 'POST',
        ));

        $form->add('submit', 'submit', array('label' => 'Create'));

        return $form;
    }


    /**
     * Displays a form to create a new Logger entity.
     *
     * @Route("/new", name="logger_new")
     * @Method("GET")
     * @Template()
     */
    public function newAction()
    {
        $entity = new Logger($this->container->getParameter('employees.sitename'));
        $form   = $this->createCreateForm($entity,$this->container->getParameter('employees.sitename'));

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * Finds and displays a Logger entity.
     *
     * @Route("/{id}", name="logger_show")
     * @Method("GET")
     * @Template()
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('OlegUserdirectoryBundle:Logger')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Logger entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Displays a form to edit an existing Logger entity.
     *
     * @Route("/{id}/edit", name="logger_edit")
     * @Method("GET")
     * @Template()
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('OlegUserdirectoryBundle:Logger')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Logger entity.');
        }

        $editForm = $this->createEditForm($entity);
        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
    * Creates a form to edit a Logger entity.
    *
    * @param Logger $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createEditForm(Logger $entity)
    {
        $form = $this->createForm(new LoggerType(), $entity, array(
            'action' => $this->generateUrl('logger_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));

        $form->add('submit', 'submit', array('label' => 'Update'));

        return $form;
    }
    /**
     * Edits an existing Logger entity.
     *
     * @Route("/{id}", name="logger_update")
     * @Method("PUT")
     * @Template("OlegUserdirectoryBundle:Logger:edit.html.twig")
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('OlegUserdirectoryBundle:Logger')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Logger entity.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $em->flush();

            return $this->redirect($this->generateUrl('logger_edit', array('id' => $id)));
        }

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }
    /**
     * Deletes a Logger entity.
     *
     * @Route("/{id}", name="logger_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('OlegUserdirectoryBundle:Logger')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find Logger entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('logger'));
    }

    /**
     * Creates a form to delete a Logger entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('logger_delete', array('id' => $id)))
            ->setMethod('DELETE')
            ->add('submit', 'submit', array('label' => 'Delete'))
            ->getForm()
        ;
    }
}
