<?php

namespace Oleg\OrderformBundle\Controller;

use Doctrine\Common\Collections\ArrayCollection;
use Oleg\OrderformBundle\Entity\AccessRequest;
use Oleg\OrderformBundle\Resources\config\Constant;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Form\Extension\Core\DataTransformer\DateTimeToStringTransformer;

use Oleg\OrderformBundle\Entity\UserRequest;
use Oleg\OrderformBundle\Form\UserRequestType;
use Oleg\OrderformBundle\Form\UserRequestApproveType;
use Oleg\OrderformBundle\Helper\EmailUtil;
//use Oleg\UserdirectoryBundle\Util\UserUtil;
//use Oleg\OrderformBundle\Helper\ErrorHelper;

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
        if (false === $this->get('security.context')->isGranted('ROLE_SCANORDER_PROCESSOR')) {
            return $this->redirect( $this->generateUrl('scan-order-nopermission') );
        }
        
        $em = $this->getDoctrine()->getManager();

        //$entities = $em->getRepository('OlegOrderformBundle:UserRequest')->findAll();

        $repository = $this->getDoctrine()->getRepository('OlegOrderformBundle:UserRequest');
        $dql =  $repository->createQueryBuilder("accreq");
        $dql->select('accreq');
        //$dql->leftJoin("accreq.division", "division");
        $dql->orderBy("accreq.creationdate","DESC");

        $limit = 30;
        $query = $em->createQuery($dql);
        $paginator  = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $query,
            $this->get('request')->query->get('page', 1), /*page number*/
            $limit/*limit per page*/
        );

        $forms = array();
        foreach( $pagination as $req ) {
            if( $req->getStatus() == 'active') {
                $disable = false;
            } else {
                $disable = true;
            }
            $forms[] = $this->createForm(new UserRequestApproveType(), $req, array('disabled' => $disable) )->createView();
        }

        return array(
            'entities' => $pagination,
            'forms' => $forms
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

        $params = $this->getParams();

        $form = $this->createForm(new UserRequestType($params), $entity);

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

            return $this->redirect( $this->generateUrl('login') );
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

        $params = $this->getParams();

        $form   = $this->createForm(new UserRequestType($params), $entity);

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
        if (false === $this->get('security.context')->isGranted('ROLE_SCANORDER_PROCESSOR')) {
            return $this->redirect( $this->generateUrl('scan-order-nopermission') );
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
        if (false === $this->get('security.context')->isGranted('ROLE_SCANORDER_PROCESSOR')) {
            return $this->redirect( $this->generateUrl('scan-order-nopermission') );
        }

        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('OlegOrderformBundle:UserRequest')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find UserRequest entity.');
        }

        $params = $this->getParams();

        $editForm = $this->createForm(new UserRequestType($params), $entity);

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

        if (false === $this->get('security.context')->isGranted('ROLE_SCANORDER_PROCESSOR')) {
            return $this->redirect( $this->generateUrl('scan-order-nopermission') );
        }

        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('OlegOrderformBundle:UserRequest')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find UserRequest entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        $params = $this->getParams();

        $editForm = $this->createForm(new UserRequestType($params), $entity);

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

        if (false === $this->get('security.context')->isGranted('ROLE_SCANORDER_PROCESSOR')) {
            return $this->redirect( $this->generateUrl('scan-order-nopermission') );
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
        
        if (false === $this->get('security.context')->isGranted('ROLE_SCANORDER_PROCESSOR')) {
            return $this->redirect( $this->generateUrl('scan-order-nopermission') );
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

    /**
     * Update (Approve) a new UserRequest entity.
     *
     * @Route("/account-requests-approve", name="accountrequest_approve")
     * @Method("POST")
     * @Template("OlegOrderformBundle:UserRequest:index.html.twig")
     */
    public function approveUserAccountRequestAction(Request $request)
    {
        //exit("approve User Account Request");

        $entity  = new UserRequest();

        $form = $this->createForm(new UserRequestApproveType(), $entity);
        $form->handleRequest($request);

        if( $entity->getId() && $entity->getId() != "" && $entity->getUsername() && $entity->getUsername() != "" && count($entity->getInstitution()) != 0 ) {

            //echo "form valid!";
            //exit();
            $em = $this->getDoctrine()->getManager();

            $entityDb = $em->getRepository('OlegOrderformBundle:UserRequest')->findOneById($entity->getId());
            if (!$entityDb) {
                throw $this->createNotFoundException('Unable to find UserRequest entity with ID:'.$entity->getId());
            }

            $entityDb->setStatus('approved');
            $entityDb->setUsername($entity->getUsername());
            $entityDb->setInstitution($entity->getInstitution());

            $em->persist($entityDb);
            $em->flush();

            $this->get('session')->getFlashBag()->add(
                'notice',
                "User with username ".$entityDb->getUsername()." has been successfully approved. You can lock this user or change institutions later on using the user's profile."
            );

        } else {

            $failedArr = array();

            if( $entity->getUsername() && $entity->getUsername() == "" ) {
                $failedArr[] = "username is empty";
            }

            if( count($entity->getInstitution()) == 0 ) {
                $failedArr[] = "Institution list is empty";
            }

            $this->get('session')->getFlashBag()->add(
                'notice',
                "Approve user with username ".$entity->getUsername()." failed."." ".implode(",", $failedArr)
            );

        }

        return $this->redirect($this->generateUrl('accountrequest'));
    }

    public function getParams() {

        $params = array();

        $em = $this->getDoctrine()->getManager();

        //departments
        $department = $em->getRepository('OlegUserdirectoryBundle:Department')->findOneByName('Pathology and Laboratory Medicine');
        $departments = new ArrayCollection();
        $departments->add($department);

        //echo "dep=".$department->getName()."<br>";

        //divisions
        $divisions = $department->getDivisions();

        //services
        $services = new ArrayCollection();
        foreach( $divisions as $division ) {

            foreach( $division->getServices() as $service ) {
                $services->add($service);
            }

        }
        //$services = $em->getRepository('OlegUserdirectoryBundle:Service')->findByDepartment($department);

        //foreach( $departments as $dep ) {
        //    echo "dep=".$dep->getName()."<br>";
        //}
        //exit('exit');

        $params['departments'] = $departments;
        $params['services'] = $services;

        return $params;
    }





    /////////////// Access Request ////////////////////

    /**
     * @Route("/access-requests/{id}", name="access_request_new", requirements={"id" = "\d+"})
     * @Method("GET")
     * @Template("OlegOrderformBundle:UserRequest:access_request.html.twig")
     */
    public function accessRequestCreateAction($id)
    {

        if( false === $this->get('security.context')->isGranted('ROLE_SCANORDER_UNAPPROVED_SUBMITTER') &&
            false === $this->get('security.context')->isGranted('ROLE_SCANORDER_BANNED')
        ) {
            return $this->redirect( $this->generateUrl('scan-order-nopermission') );
        }

        $em = $this->getDoctrine()->getManager();

        $user = $em->getRepository('OlegUserdirectoryBundle:User')->find($id);

        if (!$user) {
            throw $this->createNotFoundException('Unable to find User.');
        }

        $secUtil = $this->get('order_security_utility');
        $userAccessReq = $secUtil->getUserAccessRequest($user,Constant::SITE_NAME);

        if( $userAccessReq && $userAccessReq->getStatus() == AccessRequest::STATUS_ACTIVE ) {

            $transformer = new DateTimeToStringTransformer(null,null,'m/d/Y');
            $dateStr = $transformer->transform($userAccessReq->getCreatedate());

            $text = "You have requested access on " . $dateStr . ". Your request has not been approved yet. Please contact the system administrator by emailing ".$this->container->getParameter('default_system_email')." if you have any questions.";

            $this->get('security.context')->setToken(null);
            //$this->get('request')->getSession()->invalidate();

            return $this->render('OlegOrderformBundle:UserRequest:request_confirmation.html.twig',array('text'=>$text));
        }

        if( $userAccessReq && $userAccessReq->getStatus() == AccessRequest::STATUS_DECLINED ) {

            $transformer = new DateTimeToStringTransformer(null,null,'m/d/Y');
            $dateStr = $transformer->transform($userAccessReq->getCreatedate());
            $text = 'You have requested access on '.$dateStr.'. Your request has been declined. Please contact the system administrator by emailing '.$this->container->getParameter('default_system_email').' if you have any questions.';

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

        if (false === $this->get('security.context')->isGranted('ROLE_SCANORDER_UNAPPROVED_SUBMITTER')) {
            return $this->redirect( $this->generateUrl('scan-order-nopermission') );
        }

        $em = $this->getDoctrine()->getManager();

        $user = $em->getRepository('OlegUserdirectoryBundle:User')->find($id);

        if (!$user) {
            throw $this->createNotFoundException('Unable to find User.');
        }

        //$user->setAppliedforaccess('active');
        //$user->setAppliedforaccessdate( new \DateTime() );

        $secUtil = $this->get('order_security_utility');
        $userAccessReq = $secUtil->getUserAccessRequest($user,Constant::SITE_NAME);

        if( $userAccessReq ) {
            //throw $this->createNotFoundException('AccessRequest is already created for this user');
            $transformer = new DateTimeToStringTransformer(null,null,'m/d/Y');
            $dateStr = $transformer->transform($userAccessReq->getCreatedate());

            $text = "You have requested access on " . $dateStr . ". " .
                    "The status of your request is " . $userAccessReq->getStatusStr() . "." .
                    "Please contact the system administrator by emailing ".$this->container->getParameter('default_system_email')." if you have any questions.";

            $this->get('security.context')->setToken(null);
            //$this->get('request')->getSession()->invalidate();

            return $this->render('OlegOrderformBundle:UserRequest:request_confirmation.html.twig',array('text'=>$text));
        }

        //Create a new active AccessRequest
        $accReq = new AccessRequest();
        $accReq->setStatus(AccessRequest::STATUS_ACTIVE);
        $accReq->setUser($user);

        $em->persist($accReq);
        $em->flush();

        $email = $user->getEmail();
        $emailUtil = new EmailUtil();

        $text =
            "Thank You For Access Request !\r\n"
            . "Confirmation Email was sent to " . $email . "\r\n";

        $emailUtil->sendEmail( $email, $em, null, $text, null );

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
        if (false === $this->get('security.context')->isGranted('ROLE_SCANORDER_PROCESSOR')) {
            return $this->redirect( $this->generateUrl('scan-order-nopermission') );
        }

        $em = $this->getDoctrine()->getManager();

        $roles = $em->getRepository('OlegUserdirectoryBundle:Roles')->findAll();
        $rolesArr = array();
        if( $this->get('security.context')->isGranted('ROLE_SCANORDER_ADMIN') ) {
            foreach( $roles as $role ) {
                $rolesArr[$role->getName()] = $role->getAlias();
            }
        }

        $repository = $this->getDoctrine()->getRepository('OlegOrderformBundle:AccessRequest');
        $dql =  $repository->createQueryBuilder("accreq");
        $dql->select('accreq');
        $dql->innerJoin('accreq.user','user');
        //$dql->where("accreq.status = ".AccessRequest::STATUS_ACTIVE." OR accreq.status = ".AccessRequest::STATUS_DECLINED." OR accreq.status = ".AccessRequest::STATUS_APPROVED);
        $dql->orderBy("accreq.status","DESC");

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

        if (false === $this->get('security.context')->isGranted('ROLE_SCANORDER_PROCESSOR')) {
            return $this->redirect( $this->generateUrl('scan-order-nopermission') );
        }

        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('OlegUserdirectoryBundle:User')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find User entity.');
        }

        //$entity->setAppliedforaccess($status);
        $accReq = $em->getRepository('OlegOrderformBundle:AccessRequest')->findOneByUser($id);

        if( $status == "approved" && $role == "submitter" ) {
            $entity->setRoles(array());
            $entity->addRole('ROLE_SCANORDER_SUBMITTER');
            $entity->addRole('ROLE_SCANORDER_ORDERING_PROVIDER');
            $accReq->setStatus(AccessRequest::STATUS_APPROVED);
        }

        if( $status == "declined" ) {
            //$roles[] = "ROLE_SCANORDER_BANNED";
            //$entity->setRoles($roles);
            $entity->setRoles(array());
            $entity->addRole('ROLE_SCANORDER_BANNED');
            $accReq->setStatus(AccessRequest::STATUS_DECLINED);
        }

        if( $status == "active" ) {
            $entity->setRoles(array());
            $entity->addRole('ROLE_SCANORDER_UNAPPROVED_SUBMITTER');
            $accReq->setStatus(AccessRequest::STATUS_ACTIVE);
        }

        $em->persist($entity);
        $em->flush();

        return $this->redirect($this->generateUrl('accessrequest_list'));
    }
    
}
