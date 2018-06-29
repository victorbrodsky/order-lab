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

namespace Oleg\UserdirectoryBundle\Controller;

use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Form\Extension\Core\DataTransformer\DateTimeToStringTransformer;

use Oleg\UserdirectoryBundle\Entity\UserRequest;
use Oleg\UserdirectoryBundle\Form\UserRequestType;
use Oleg\UserdirectoryBundle\Form\UserRequestApproveType;

/**
 * UserRequest controller.
 */
class UserRequestController extends Controller
{

    protected $router;
    protected $siteName;
    protected $siteNameShowuser;
    protected $siteNameStr;
    protected $roleEditor;

    public function __construct() {
        $this->siteName = 'employees'; //controller is not setup yet, so we can't use $this->container->getParameter('employees.sitename');
        $this->siteNameShowuser = 'employees';
        $this->siteNameStr = 'Employee Directory';
        $this->roleEditor = 'ROLE_USERDIRECTORY_EDITOR';
    }

    /**
     * Lists all UserRequest entities.
     *
     * @Route("/account-requests", name="employees_accountrequest")
     * @Method("GET")
     * @Template("OlegUserdirectoryBundle:UserRequest:index.html.twig")
     */
    public function indexAction( Request $request )
    {
        if (false === $this->get('security.authorization_checker')->isGranted($this->roleEditor)) {
            return $this->redirect( $this->generateUrl($this->siteName.'-nopermission') );
        }
        
        $em = $this->getDoctrine()->getManager();

        //$entities = $em->getRepository('OlegUserdirectoryBundle:UserRequest')->findAll();

        $repository = $this->getDoctrine()->getRepository('OlegUserdirectoryBundle:UserRequest');
        $dql =  $repository->createQueryBuilder("accreq");
        $dql->select('accreq');
        $dql->leftJoin("accreq.systemAccountRequest", "systemAccountRequest");

        $dql->where("accreq.siteName = :siteName");
		
		$postData = $request->query->all();
		
		if( !isset($postData['sort']) ) { 
			$dql->orderBy("accreq.creationdate","DESC");
		}
		
		//pass sorting parameters directly to query; Somehow, knp_paginator stoped correctly create pagination according to sorting parameters       
//		if( isset($postData['sort']) ) {
//            $dql = $dql . " ORDER BY $postData[sort] $postData[direction]";
//        }

        $limit = 30;
        $query = $em->createQuery($dql);
        $query->setParameter("siteName",$this->siteName);

        //echo "siteName=".$this->siteName."<br>";
        //echo "query=".$query->getSql()."<br>";

        $paginator  = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $query,
            $request->query->get('page', 1), /*page number*/
            $limit, /*limit per page*/
            array('wrap-queries' => true)
        );

        $forms = array();
        foreach( $pagination as $req ) {
            if( $req->getStatus() == 'active') {
                $disable = false;
            } else {
                $disable = true;
            }

            $params = $this->getParams($this->siteName);

            $forms[] = $this->createForm(UserRequestApproveType::class,$req,array('disabled'=>$disable,'form_custom_value'=>$params))->createView();
        }

        return array(
            'entities' => $pagination,
            'forms' => $forms,
            'sitename' => $this->siteName
        );
    }

    /**
     * Creates a new UserRequest entity.
     *
     * @Route("/account-requests/new", name="employees_accountrequest_create")
     * @Method("POST")
     * @Template("OlegUserdirectoryBundle:UserRequest:account_request.html.twig")
     */
    public function createAction(Request $request)
    {
        //exit("createAction");

        $entity  = new UserRequest();

        $params = $this->getParams($this->siteName);

        $form = $this->createForm(UserRequestType::class,$entity,array('form_custom_value'=>$params));

        $form->handleRequest($request);

        if( !$entity->getRequestedScanOrderInstitutionScope() ) {
            $error = new FormError("Organizational Group is empty");
            $form->get('requestedScanOrderInstitutionScope')->addError($error);
        }

        if( !$entity->getName() ) {
            $error = new FormError("Last Name is empty");
            $form->get('name')->addError($error);
        }

        if( !$entity->getFirstName() ) {
            $error = new FormError("First Name is empty");
            $form->get('firstName')->addError($error);
        }

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

            return $this->redirect( $this->generateUrl($this->siteName.'_login') );
        }

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
            'sitename' => $this->siteName,
            'title' => "Account Request for ".$this->siteNameStr
        );
    }

    /**
     * Displays a form to create a new UserRequest entity.
     *
     * @Route("/account-requests/new", name="employees_accountrequest_new")
     * @Method("GET")
     * @Template("OlegUserdirectoryBundle:UserRequest:account_request.html.twig")
     */
    public function newAction()
    {
        $entity = new UserRequest();
        $entity->setSiteName($this->siteName);

        $em = $this->getDoctrine()->getManager();
        $usernametypes = $em->getRepository('OlegUserdirectoryBundle:UsernameType')->findBy(
            array(
                'type' => array('default', 'user-added'),
                'abbreviation' => array('wcmc-cwid','local-user')
            ),
            array('orderinlist' => 'ASC')
        );

        $params = $this->getParams($this->siteName);

        $form = $this->createForm(UserRequestType::class,$entity,array('form_custom_value'=>$params));

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
            'usernametypes' => $usernametypes,
            'sitename' => $this->siteName,
            'title' => "Account Request for ".$this->siteNameStr
            //'security' => 'false'
        );
    }

    /**
     * Finds and displays a UserRequest entity.
     *
     * @Route("/account-requests/{id}", name="employees_accountrequest_show", requirements={"id" = "\d+"})
     * @Method("GET")
     * @Template()
     */
    public function showAction($id)
    {
        if (false === $this->get('security.authorization_checker')->isGranted($this->roleEditor)) {
            return $this->redirect( $this->generateUrl($this->siteName.'-nopermission') );
        }

        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('OlegUserdirectoryBundle:UserRequest')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find UserRequest entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),
            'sitename' => $this->siteName
        );
    }


    
    
    /**
     * @Route("/account-requests/{id}/{status}/status", name="employees_accountrequest_status", requirements={"id" = "\d+"})
     * @Method("GET")
     * @Template("OlegUserdirectoryBundle:UserRequest:index.html.twig")
     */
    public function statusAction($id, $status)
    {
        
        if (false === $this->get('security.authorization_checker')->isGranted($this->roleEditor)) {
            return $this->redirect( $this->generateUrl($this->siteName.'-nopermission') );
        }
        
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('OlegUserdirectoryBundle:UserRequest')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find UserRequest entity.');
        }
      
        $entity->setStatus($status);
        $em->persist($entity);
        $em->flush();

        return $this->redirect($this->generateUrl($this->siteName.'_accountrequest'));
    }

    /**
     * Update (Approve) a new UserRequest entity.
     *
     * @Route("/account-requests-approve", name="employees_accountrequest_approve")
     * @Method("POST")
     * @Template("OlegUserdirectoryBundle:UserRequest:index.html.twig")
     */
    public function approveUserAccountRequestAction(Request $request)
    {

        if (false === $this->get('security.authorization_checker')->isGranted($this->roleEditor)) {
            return $this->redirect( $this->generateUrl($this->siteName.'-nopermission') );
        }

        $em = $this->getDoctrine()->getManager();
        $entity  = new UserRequest();

        $params = $this->getParams($this->siteName);

        $form = $this->createForm(UserRequestApproveType::class,$entity,array('form_custom_value'=>$params));
        $form->handleRequest($request);

        if( $entity->getSiteName() == "scan" ) {
            //Case 1: scan order with pacsvendor user
            if ($entity->getId() &&
                $entity->getId() != "" &&
                $entity->getUsername() &&
                $entity->getUsername() != "" &&
                //count($entity->getRequestedInstitutionalPHIScope()) != 0 &&
                $entity->getRequestedScanOrderInstitutionScope() &&
                $entity->getUsername()
            ) {

//            echo "form valid!";
//            echo "getRequestedScanOrderInstitutionScope=".$entity->getRequestedScanOrderInstitutionScope();
//            exit();

                $entityDb = $em->getRepository('OlegUserdirectoryBundle:UserRequest')->findOneById($entity->getId());
                if (!$entityDb) {
                    throw $this->createNotFoundException('Unable to find UserRequest entity with ID:' . $entity->getId());
                }

                $entityDb->setStatus('approved');
                $entityDb->setUsername($entity->getUsername());
                //$entityDb->setRequestedInstitutionalPHIScope($entity->getRequestedInstitutionalPHIScope());
                $entityDb->setRequestedScanOrderInstitutionScope($entity->getRequestedScanOrderInstitutionScope());

                $em->persist($entityDb);
                $em->flush();

                $this->get('session')->getFlashBag()->add(
                    'notice',
                    "User with username " . $entityDb->getUsername() . " has been successfully approved. ".
                    "You must manually create this user in the system. ".
                    "You can lock this user or change institutions later on using the user's profile."
                );

            } else {

                $failedArr = array();

                if ($entity->getUsername() && $entity->getUsername() == "") {
                    $failedArr[] = "username is empty";
                }

//            if( count($entity->getRequestedInstitutionalPHIScope()) == 0 ) {
//                $failedArr[] = "Institution list is empty";
//            }

                if (!$entity->getRequestedScanOrderInstitutionScope()) {
                    $failedArr[] = "organizational group is empty";
                }

                $this->get('session')->getFlashBag()->add(
                    'notice',
                    "Approve a user with username " . $entity->getUsername() . " failed." . " " . implode(",", $failedArr)
                );

            }
        } else {
            //Case 2: all other sites, except scan
            if ($entity->getId() &&
                $entity->getId() != "" &&
                $entity->getRequestedScanOrderInstitutionScope() &&
                count($entity->getRoles()) > 0
            ) {
                //exit('Approve ');

                $entityDb = $em->getRepository('OlegUserdirectoryBundle:UserRequest')->findOneById($entity->getId());
                if (!$entityDb) {
                    throw $this->createNotFoundException('Unable to find UserRequest entity with ID:' . $entity->getId());
                }

                $entityDb->setStatus('approved');
                if( $entity->getUsername() ) {
                    $entityDb->setUsername($entity->getUsername());
                }

                $entityDb->setRequestedScanOrderInstitutionScope($entity->getRequestedScanOrderInstitutionScope());

                if( count($entity->getRoles()) > 0 ) {
                    $entityDb->setRoles( $entity->getRoles() );
                }

                $em->persist($entityDb);
                $em->flush();

                $this->get('session')->getFlashBag()->add(
                    'notice',
                    "User with username " . $entityDb->getUsername() . " has been successfully approved. ".
                    "You must manually create this user in the system. ".
                    "You can lock this user or change institutions later on using the user's profile."
                );

            } else {
                $failedArr = array();

                if( count($entity->getRoles()) == 0 ) {
                    $failedArr[] = "Roles are not assigned";
                }

                if (!$entity->getRequestedScanOrderInstitutionScope()) {
                    $failedArr[] = "organizational group is empty";
                }

                $this->get('session')->getFlashBag()->add(
                    'notice',
                    "Approve a user with username " . $entity->getUsername() . " failed." . " " . implode(",", $failedArr)
                );
            }
        }


        return $this->redirect($this->generateUrl($this->siteName.'_accountrequest'));
    }

    public function getParams( $sitename ) {

        $userSecUtil = $this->container->get('user_security_utility');
        //$user = $this->get('security.context')->getToken()->getUser();
        
        $params = array();

        $em = $this->getDoctrine()->getManager();
        $params['em'] = $em;

        //departments
        $department = $userSecUtil->getAutoAssignInstitution();
        if( !$department ) {
            $department = $em->getRepository('OlegUserdirectoryBundle:Institution')->findOneByName('Pathology and Laboratory Medicine');
        }

        $params['institution'] = $department;
        $params['sitename'] = $sitename;

        //Institution
        //$requestedScanOrderInstitutionScope = $em->getRepository('OlegUserdirectoryBundle:Institution')->findBy(array('level'=>0));
        $repository = $em->getRepository('OlegUserdirectoryBundle:Institution');
        $dql =  $repository->createQueryBuilder("institution");
        $dql->select('institution');
        $dql->leftJoin("institution.types", "types");

        $dql->where("institution.type = 'default' OR institution.type = 'user-added'");
        $dql->andWhere("types.name IS NULL OR types.name != 'Collaboration'");

        $query = $em->createQuery($dql);
        $requestedScanOrderInstitutionScope = $query->getResult();

        //$params['requestedInstitutionalPHIScope'] = $requestedInstitutionalPHIScope;
        $params['requestedScanOrderInstitutionScope'] = $requestedScanOrderInstitutionScope;

        //Roles
        $securityUtil = $this->get('order_security_utility');
        $rolesArr = $securityUtil->getSiteRolesKeyValue($this->siteName);
        $params['roles'] = $rolesArr;

        return $params;
    }
//    public function getParams_Old() {
//
//        $params = array();
//
//        $em = $this->getDoctrine()->getManager();
//
//        //departments
//        $department = $em->getRepository('OlegUserdirectoryBundle:Department')->findOneByName('Pathology and Laboratory Medicine');
//        $departments = new ArrayCollection();
//        $departments->add($department);
//
//        //echo "dep=".$department->getName()."<br>";
//
//        //divisions
//        $divisions = $department->getDivisions();
//
//        //services
//        $services = new ArrayCollection();
//        foreach( $divisions as $division ) {
//
//            foreach( $division->getServices() as $service ) {
//                $services->add($service);
//            }
//
//        }
//        //$services = $em->getRepository('OlegUserdirectoryBundle:Service')->findByDepartment($department);
//
//        //foreach( $departments as $dep ) {
//        //    echo "dep=".$dep->getName()."<br>";
//        //}
//        //exit('exit');
//
//        $params['departments'] = $departments;
//        $params['services'] = $services;
//
//        return $params;
//    }










    ///// NOT USED: edit, delete account request ///////

    /**
     * Displays a form to edit an existing UserRequest entity.
     *
     * @Route("/account-requests/{id}/edit", name="employees_accountrequest_edit", requirements={"id" = "\d+"})
     * @Method("GET")
     * @Template()
     */
    public function editAction($id)
    {
        if (false === $this->get('security.authorization_checker')->isGranted($this->roleEditor)) {
            return $this->redirect( $this->generateUrl($this->siteName.'-nopermission') );
        }

        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('OlegUserdirectoryBundle:UserRequest')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find UserRequest entity.');
        }

        $params = $this->getParams();

        $editForm = $this->createForm(UserRequestType::class,$entity,array('form_custom_value'=>$params));

        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
            'sitename' => $this->siteName
        );
    }

    /**
     * Edits an existing UserRequest entity.
     *
     * @Route("/account-requests/{id}", name="employees_accountrequest_update", requirements={"id" = "\d+"})
     * @Method("PUT")
     * @Template("OlegUserdirectoryBundle:UserRequest:edit.html.twig")
     */
    public function updateAction(Request $request, $id)
    {

        if (false === $this->get('security.authorization_checker')->isGranted($this->roleEditor)) {
            return $this->redirect( $this->generateUrl($this->siteName.'-nopermission') );
        }

        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('OlegUserdirectoryBundle:UserRequest')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find UserRequest entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        $params = $this->getParams();

        $editForm = $this->createForm(UserRequestType::class,$entity,array('form_custom_value'=>$params));

        $editForm->handleRequest($request);

        if ($editForm->isValid()) {

            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl($this->siteName.'_accountrequest'));
            //return $this->redirect( $this->generateUrl('multy_new') );
            //return $this->redirect($this->generateUrl($this->siteName.'_accountrequest_edit', array('id' => $id)));
        }

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
            'sitename' => $this->siteName
        );
    }
    /**
     * Deletes a UserRequest entity.
     *
     * @Route("/account-requests/{id}", name="employees_accountrequest_delete", requirements={"id" = "\d+"})
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, $id)
    {

        if (false === $this->get('security.authorization_checker')->isGranted($this->roleEditor)) {
            return $this->redirect( $this->generateUrl($this->siteName.'-nopermission') );
        }

        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('OlegUserdirectoryBundle:UserRequest')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find UserRequest entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl($this->siteName.'_accountrequest'));
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
            ->add('id', HiddenType::class)
            ->getForm()
            ;
    }
    
}
