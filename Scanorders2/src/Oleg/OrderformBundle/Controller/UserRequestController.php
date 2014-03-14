<?php

namespace Oleg\OrderformBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Oleg\OrderformBundle\Entity\UserRequest;
use Oleg\OrderformBundle\Form\UserRequestType;
use Oleg\OrderformBundle\Helper\EmailUtil;
use Symfony\Component\Form\Extension\Core\DataTransformer\DateTimeToStringTransformer;

//@Route("/userrequest")
/**
 * UserRequest controller.
 */
class UserRequestController extends Controller
{

    /**
     * Lists all UserRequest entities.
     *
     * @Route("/account-requests", name="accountrequest")
     * @Method("GET")
     * @Template()
     */
    public function indexAction()
    {
        if (false === $this->get('security.context')->isGranted('ROLE_PROCESSOR')) {
            return $this->redirect( $this->generateUrl('logout') );
        }
        
        $em = $this->getDoctrine()->getManager();

        //$entities = $em->getRepository('OlegOrderformBundle:UserRequest')->findAll();

        $repository = $this->getDoctrine()->getRepository('OlegOrderformBundle:UserRequest');
        $dql =  $repository->createQueryBuilder("accreq");
        $dql->select('accreq');
        $dql->leftJoin("accreq.pathologyServices", "pathologyServices");
        $dql->orderBy("accreq.creationdate","DESC");

        $limit = 30;
        $query = $em->createQuery($dql);
        $paginator  = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $query,
            $this->get('request')->query->get('page', 1), /*page number*/
            $limit/*limit per page*/
        );

        return array(
            'entities' => $pagination
        );
    }

    /**
     * Creates a new UserRequest entity.
     *
     * @Route("/create/", name="accountrequest_create")
     * @Method("POST")
     * @Template("OlegOrderformBundle:UserRequest:account_request.html.twig")
     */
    public function createAction(Request $request)
    {
        //exit("createAction");

        $entity  = new UserRequest();
        $form = $this->createForm(new UserRequestType(), $entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
            //echo "form valid!";
            $em = $this->getDoctrine()->getManager();

            $entity->setStatus('active');

            $em->persist($entity);
            $em->flush();

            $this->get('session')->getFlashBag()->add(
                'notice',
                'Thank You! You have successfully submitted an account request. If you have provided your email or phone number we will let you know once your request is reviewed.'
            );

            return $this->redirect( $this->generateUrl('logout') );
        }

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * Displays a form to create a new UserRequest entity.
     *
     * @Route("/account-requests/new", name="accountrequest_new")
     * @Method("GET")
     * @Template("OlegOrderformBundle:UserRequest:account_request.html.twig")
     */
    public function newAction()
    {
        $entity = new UserRequest();
        $form   = $this->createForm(new UserRequestType(), $entity);

        return array(
            'entity' => $entity,
            'form'   => $form->createView()
            //'security' => 'false'
        );
    }

    /**
     * Finds and displays a UserRequest entity.
     *
     * @Route("/account-requests/{id}", name="accountrequest_show", requirements={"id" = "\d+"})
     * @Method("GET")
     * @Template()
     */
    public function showAction($id)
    {
        if (false === $this->get('security.context')->isGranted('ROLE_PROCESSOR')) {
            return $this->redirect( $this->generateUrl('logout') );
        }

        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('OlegOrderformBundle:UserRequest')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find UserRequest entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Displays a form to edit an existing UserRequest entity.
     *
     * @Route("/account-requests/{id}/edit", name="accountrequest_edit", requirements={"id" = "\d+"})
     * @Method("GET")
     * @Template()
     */
    public function editAction($id)
    {
        if (false === $this->get('security.context')->isGranted('ROLE_PROCESSOR')) {
            return $this->redirect( $this->generateUrl('logout') );
        }

        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('OlegOrderformBundle:UserRequest')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find UserRequest entity.');
        }

        $editForm = $this->createForm(new UserRequestType(), $entity);
        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Edits an existing UserRequest entity.
     *
     * @Route("/account-requests/{id}", name="accountrequest_update", requirements={"id" = "\d+"})
     * @Method("PUT")
     * @Template("OlegOrderformBundle:UserRequest:edit.html.twig")
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('OlegOrderformBundle:UserRequest')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find UserRequest entity.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createForm(new UserRequestType(), $entity);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {

            $em->persist($entity);
            $em->flush();

            return $this->redirect( $this->generateUrl('multy_new') );

            return $this->redirect($this->generateUrl('accountrequest_edit', array('id' => $id)));
        }

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }
    /**
     * Deletes a UserRequest entity.
     *
     * @Route("/account-requests/{id}", name="accountrequest_delete", requirements={"id" = "\d+"})
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, $id)
    {

        if (false === $this->get('security.context')->isGranted('ROLE_PROCESSOR')) {
            return $this->redirect( $this->generateUrl('logout') );
        }

        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('OlegOrderformBundle:UserRequest')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find UserRequest entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('accountrequest'));
    }

    /**
     * Creates a form to delete a UserRequest entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id)
    {
        return $this->createFormBuilder(array('id' => $id))
            ->add('id', 'hidden')
            ->getForm()
        ;
    }
    
    
    /**
     * @Route("/account-requests/{id}/{status}/status", name="accountrequest_status", requirements={"id" = "\d+"})
     * @Method("GET")
     * @Template("OlegOrderformBundle:UserRequest:index.html.twig")
     */
    public function statusAction($id, $status)
    {
        
        if (false === $this->get('security.context')->isGranted('ROLE_PROCESSOR')) {
            return $this->redirect( $this->generateUrl('logout') );
        }
        
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('OlegOrderformBundle:UserRequest')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find UserRequest entity.');
        }
      
        $entity->setStatus($status);
        $em->persist($entity);
        $em->flush();
        
        
        return $this->redirect($this->generateUrl('accountrequest'));
            
    }





    /////////////// Access Request ////////////////////

    /**
     * @Route("/access-requests/{id}", name="access_request_new", requirements={"id" = "\d+"})
     * @Method("GET")
     * @Template("OlegOrderformBundle:UserRequest:access_request.html.twig")
     */
    public function accessRequestCreateAction($id)
    {

        if( false === $this->get('security.context')->isGranted('ROLE_UNAPPROVED_SUBMITTER') AND
            false === $this->get('security.context')->isGranted('ROLE_BANNED')
        ) {
            return $this->redirect( $this->generateUrl('logout') );
        }

        $em = $this->getDoctrine()->getManager();

        $user = $em->getRepository('OlegOrderformBundle:User')->find($id);

        if (!$user) {
            throw $this->createNotFoundException('Unable to find User.');
            //return $this->redirect( $this->generateUrl('logout') );
        }

        if( $user->getAppliedforaccess() && $user->getAppliedforaccess() == "active" ) {

            $transformer = new DateTimeToStringTransformer(null,null,'m/d/Y');
            $dateStr = $transformer->transform($user->getAppliedforaccessdate());

            $text = "You have requested access on " . $dateStr . ". Your request has not been approved yet. Please contact the system administrator by emailing scanorder@med.cornell.edu if you have any questions.";

            $this->get('security.context')->setToken(null);
            //$this->get('request')->getSession()->invalidate();

            return $this->render('OlegOrderformBundle:UserRequest:request_confirmation.html.twig',array('text'=>$text));
        }

        if( $user->getAppliedforaccess() && $user->getAppliedforaccess() == "declined" ) {

            $transformer = new DateTimeToStringTransformer(null,null,'m/d/Y');
            $dateStr = $transformer->transform($user->getAppliedforaccessdate());
            $text = 'You have requested access on '.$dateStr.'. Your request has been declined. Please contact the system administrator by emailing scanorder@med.cornell.edu if you have any questions.';

            $this->get('security.context')->setToken(null);
            //$this->get('request')->getSession()->invalidate();

            return $this->render('OlegOrderformBundle:UserRequest:request_confirmation.html.twig',array('text'=>$text));
        }

        //echo "userid=".$id."<br>";
        //exit();

        return array(
            'userid' => $id,
        );

    }

    /**
     * @Route("/access-requests/{id}", name="access_request_create", requirements={"id" = "\d+"})
     * @Method("POST")
     * @Template("OlegOrderformBundle:UserRequest:access_request.html.twig")
     */
    public function accessRequestAction($id)
    {

        if (false === $this->get('security.context')->isGranted('ROLE_UNAPPROVED_SUBMITTER')) {
            return $this->redirect( $this->generateUrl('logout') );
        }

        $em = $this->getDoctrine()->getManager();

        $user = $em->getRepository('OlegOrderformBundle:User')->find($id);

        if (!$user) {
            throw $this->createNotFoundException('Unable to find User.');
        }

        $user->setAppliedforaccess('active');
        $user->setAppliedforaccessdate( new \DateTime() );

        $em->persist($user);
        $em->flush();


        $user = $this->get('security.context')->getToken()->getUser();
        $email = $user->getEmail();
        $emailUtil = new EmailUtil();

        $text =
            "Thank You For Access Request !\r\n"
            . "Confirmation Email was sent to " . $email . "\r\n";

        $emailUtil->sendEmail( $email, null, $text, null );

        $emailStr = "";
        if( $email && $email != "" ) {
            $emailStr = "\r\nConfirmation email was sent to ".$email;
        }

        $text = 'Your access request was successfully submitted and and will be reviewed.'.$emailStr;


//        $this->get('session')->getFlashBag()->add(
//            'notice',
//            $text
//        );

        $this->get('security.context')->setToken(null);
        //$this->get('request')->getSession()->invalidate();

        return $this->render('OlegOrderformBundle:UserRequest:request_confirmation.html.twig',array('text'=>$text));

    }


    /**
     * Lists all Access Request.
     *
     * @Route("/access-requests", name="accessrequest_list")
     * @Method("GET")
     * @Template("OlegOrderformBundle:UserRequest:access_request_list.html.twig")
     */
    public function accessRequestIndexAction()
    {
        if (false === $this->get('security.context')->isGranted('ROLE_PROCESSOR')) {
            return $this->redirect( $this->generateUrl('logout') );
        }

        $em = $this->getDoctrine()->getManager();

        //$entities = $em->getRepository('OlegOrderformBundle:User')->findByAppliedforaccess('active');

        $roles = $em->getRepository('OlegOrderformBundle:Roles')->findAll();
        $rolesArr = array();
        if( $this->get('security.context')->isGranted('ROLE_ADMIN') ) {
            foreach( $roles as $role ) {
                $rolesArr[$role->getName()] = $role->getAlias();
            }
        }

        $repository = $this->getDoctrine()->getRepository('OlegOrderformBundle:User');
        $dql =  $repository->createQueryBuilder("accreq");
        $dql->select('accreq');
        $dql->leftJoin("accreq.pathologyServices", "pathologyServices");
        $dql->where("accreq.appliedforaccess = 'active' OR accreq.appliedforaccess = 'declined' OR accreq.appliedforaccess = 'approved'");
        $dql->orderBy("accreq.appliedforaccess","DESC");
        //$dql->orderBy("pathologyServices.name","DESC");   //test many-to-many sorting

        $limit = 30;
        $query = $em->createQuery($dql);
        $paginator  = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $query,
            $this->get('request')->query->get('page', 1), /*page number*/
            $limit/*limit per page*/
        );

        return array(
            'entities' => $pagination,
            'roles' => $rolesArr
        );

    }


    /**
     * @Route("/access-requests/{id}/{status}/{role}/status", name="accessrequest_change", requirements={"id" = "\d+"})
     * @Method("GET")
     * @Template()
     */
    public function accessRequestChangeAction($id, $status, $role)
    {

        if (false === $this->get('security.context')->isGranted('ROLE_PROCESSOR')) {
            return $this->redirect( $this->generateUrl('logout') );
        }

        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('OlegOrderformBundle:User')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find User entity.');
        }

        $entity->setAppliedforaccess($status);

        if( $status == "approved" && $role == "external" ) {
            //$entity->removeRole('ROLE_UNAPPROVED_SUBMITTER');
            $entity->setRoles(array());
            $entity->addRole('ROLE_EXTERNAL_SUBMITTER');
            $entity->addRole('ROLE_EXTERNAL_ORDERING_PROVIDER');
            //$entity->setLocked(false);
        }

        if( $status == "approved" && $role == "submitter" ) {
            //$entity->removeRole('ROLE_UNAPPROVED_SUBMITTER');
            $entity->setRoles(array());
            $entity->addRole('ROLE_SUBMITTER');
            $entity->addRole('ROLE_ORDERING_PROVIDER');
            //$entity->setLocked(false);
        }

        if( $status == "declined" ) {
            //$roles[] = "ROLE_BANNED";
            //$entity->setRoles($roles);
            $entity->setRoles(array());
            $entity->addRole('ROLE_BANNED');
        }

        if( $status == "active" ) {
            //$roles[] = "ROLE_UNAPPROVED_SUBMITTER";
            //$entity->setRoles($roles);
            $entity->setRoles(array());
            $entity->addRole('ROLE_UNAPPROVED_SUBMITTER');
        }

        $em->persist($entity);
        $em->flush();

        return $this->redirect($this->generateUrl('accessrequest_list'));
    }
    
}
