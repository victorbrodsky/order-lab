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

namespace App\UserdirectoryBundle\Controller;



use App\UserdirectoryBundle\Entity\SourceSystemList;
use App\UserdirectoryBundle\Entity\UsernameType; //process.py script: replaced namespace by ::class: added use line for classname=UsernameType


use App\UserdirectoryBundle\Entity\Institution; //process.py script: replaced namespace by ::class: added use line for classname=Institution
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use App\UserdirectoryBundle\Controller\OrderAbstractController;


use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Form\Extension\Core\DataTransformer\DateTimeToStringTransformer;

use App\UserdirectoryBundle\Entity\UserRequest;
use App\UserdirectoryBundle\Form\UserRequestType;
use App\UserdirectoryBundle\Form\UserRequestApproveType;

/**
 * UserRequest controller.
 */
class UserRequestController extends OrderAbstractController
{

    protected $router;
    protected $siteName;
    protected $siteNameShowuser;
    protected $siteNameStr;
    protected $roleEditor;

    public function __construct() {
        $this->siteName = 'employees'; //controller is not setup yet, so we can't use $this->getParameter('employees.sitename');
        $this->siteNameShowuser = 'employees';
        $this->siteNameStr = 'Employee Directory';
        $this->roleEditor = 'ROLE_USERDIRECTORY_EDITOR';
    }

    /**
     * Lists all UserRequest entities.
     */
    #[Route(path: '/account-requests', name: 'employees_accountrequest', methods: ['GET'])]
    #[Template('AppUserdirectoryBundle/UserRequest/index.html.twig')]
    public function indexAction( Request $request )
    {
        if (false === $this->isGranted($this->roleEditor)) {
            return $this->redirect( $this->generateUrl($this->siteName.'-nopermission') );
        }
        
        $em = $this->getDoctrine()->getManager();

        //$entities = $em->getRepository('AppUserdirectoryBundle:UserRequest')->findAll();

        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:UserRequest'] by [UserRequest::class]
        $repository = $this->getDoctrine()->getRepository(UserRequest::class);
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
        $query = $dql->getQuery(); //$query = $em->createQuery($dql);
        $query->setParameter("siteName",$this->siteName);

        //echo "siteName=".$this->siteName."<br>";
        //echo "query=".$query->getSql()."<br>";

        $paginator  = $this->container->get('knp_paginator');
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

        //Use new free text field requestedInstitutionScope. Populate it with the original requestedScanOrderInstitutionScope
        $orgGroupsSelect2 = array();
        foreach( $params['requestedScanOrderInstitutionScope'] as $orgGroup) {
            $orgGroupsSelect2[] = array('id' => $orgGroup->getId(), 'text' => $orgGroup->getNodeNameWithRoot());
        }
        //$orgGroupsSelect2[] = array('id' => 'test2', 'text' => 'test2');

        return array(
            'entities' => $pagination,
            'forms' => $forms,
            'sitename' => $this->siteName,
            'orggroups' => $orgGroupsSelect2
        );
    }

    /**
     * Creates a new UserRequest entity.
     */
    #[Route(path: '/account-requests/new', name: 'employees_accountrequest_create', methods: ['POST'])]
    #[Template('AppUserdirectoryBundle/UserRequest/account_request.html.twig')]
    public function createAction(Request $request)
    {
        //exit("createAction");
        $securityUtil = $this->container->get('user_security_utility');
        $emailUtil = $this->container->get('user_mailer_utility');

        $entity  = new UserRequest();
        $entity->setSiteName($this->siteName);

        $em = $this->getDoctrine()->getManager();
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:UsernameType'] by [UsernameType::class]
        $usernametypes = $em->getRepository(UsernameType::class)->findBy(
            array(
                'type' => array('default', 'user-added'),
                'abbreviation' => array('ldap-user','local-user')
            ),
            array('orderinlist' => 'ASC')
        );

        $params = $this->getParams($this->siteName, $request);

        $form = $this->createForm(UserRequestType::class,$entity,array('form_custom_value'=>$params)); //create POST

        $form->handleRequest($request);

//        if( !$entity->getRequestedScanOrderInstitutionScope() ) {
//            $error = new FormError("Organizational Group is empty");
//            $form->get('requestedScanOrderInstitutionScope')->addError($error);
//        }
        if( !$entity->getRequestedInstitutionScope() ) {
            $error = new FormError("Organizational Group is empty");
            $form->get('requestedInstitutionScope')->addError($error);
        }

        if( !$entity->getName() ) {
            $error = new FormError("Last Name is empty");
            $form->get('name')->addError($error);
        }

        if( !$entity->getFirstName() ) {
            $error = new FormError("First Name is empty");
            $form->get('firstName')->addError($error);
        }

        $requireVerifyMobilePhone = $securityUtil->isRequireVerifyMobilePhone($this->siteName);
        if( !$entity->getMobilePhone() && $requireVerifyMobilePhone ) {
            $error = new FormError("Primary Mobile Phone Number is empty");
            $form->get('mobilePhone')->addError($error);
        }

        if ($form->isValid()) {
            //echo "form valid!";
            $em = $this->getDoctrine()->getManager();

            $entity->setStatus('active');

            if( $entity->getMobilePhone() ) {
                $entity->setUnVerified();
            }

            $em->persist($entity);
            $em->flush(); //comment out for testing

            $this->addFlash(
                'notice',
                'Thank You! You have successfully submitted an account request. If you have provided your email or phone number we will let you know once your request is reviewed.'
            );

            //redirect to verify mobile phone number if isRequireVerifyMobilePhone
            if( $securityUtil->isRequireVerifyMobilePhone($this->siteName) ) {
                return $this->redirect($this->generateUrl('employees_verify_mobile_phone_account_request', 
                    array('sitename'=>$this->siteName,'id'=>$entity->getId(),'objectName'=>'UserRequest')
                ));
            }

            //Send email to admin
            $systemEmail = $securityUtil->getSiteSettingParameter('siteEmail');
            $subject = "New account request has been submitted for ".$this->siteName;
            $reason = $entity->getRequest();
            $emailBody = "New account request has been submitted for ".$this->siteName .
                " by " . $entity->getFirstName(). " " . $entity->getName() .
                ", email: " . $entity->getEmail() . ", reason: " . $reason
            ;
            $emailUtil->sendEmail( $systemEmail, $subject, $emailBody );


            return $this->redirect( $this->generateUrl($this->siteName.'_login') );
        }

        //Use new free text field requestedInstitutionScope. Populate it with the original requestedScanOrderInstitutionScope
        $orgGroupsSelect2 = array();
        foreach( $params['requestedScanOrderInstitutionScope'] as $orgGroup) {
            $orgGroupsSelect2[] = array('id' => $orgGroup->getId(), 'text' => $orgGroup->getNodeNameWithRoot());
        }

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
            'usernametypes' => $usernametypes,
            'sitename' => $this->siteName,
            'title' => "Account Request for ".$this->siteNameStr,
            'orggroups' => $orgGroupsSelect2
        );
    }

    /**
     * Displays a form to create a new UserRequest entity.
     * http://127.0.0.1/translational-research/account-requests/new
     */
    #[Route(path: '/account-requests/new', name: 'employees_accountrequest_new', methods: ['GET'])]
    #[Template('AppUserdirectoryBundle/UserRequest/account_request.html.twig')]
    public function newAction( Request $request )
    {
        $entity = new UserRequest();
        $entity->setSiteName($this->siteName);

        $em = $this->getDoctrine()->getManager();
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:UsernameType'] by [UsernameType::class]
        $usernametypes = $em->getRepository(UsernameType::class)->findBy(
            array(
                'type' => array('default', 'user-added'),
                'abbreviation' => array('ldap-user','local-user')
            ),
            array('orderinlist' => 'ASC')
        );
        
        $params = $this->getParams($this->siteName, $request);

        $form = $this->createForm(UserRequestType::class,$entity,array('form_custom_value'=>$params)); //create GET

        //convert to $orgGroupsSelect2
//        $choices = [
//            ['id' => 'apple', 'text' => 'Apple'],
//            ['id' => 'banana', 'text' => 'Banana'],
//        ];
        //dump($params['requestedScanOrderInstitutionScope']);
        //exit('111');
//        $orgGroupsSelect2 = [
//            ['id' => 'apple', 'text' => 'Apple'],
//            ['id' => 'banana', 'text' => 'Banana'],
//        ];
        //Use new free text field requestedInstitutionScope. Populate it with the original requestedScanOrderInstitutionScope
        $orgGroupsSelect2 = array();
        foreach( $params['requestedScanOrderInstitutionScope'] as $orgGroup) {
            $orgGroupsSelect2[] = array('id' => $orgGroup->getId(), 'text' => $orgGroup->getNodeNameWithRoot());
        }

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
            'usernametypes' => $usernametypes,
            'sitename' => $this->siteName,
            'title' => "Account Request for ".$this->siteNameStr,
            'orggroups' => $orgGroupsSelect2
            //'security' => 'false'
        );
    }

    /**
     * Finds and displays a UserRequest entity.
     */
    #[Route(path: '/account-requests/{id}', name: 'employees_accountrequest_show', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function showAction($id)
    {
        if (false === $this->isGranted($this->roleEditor)) {
            return $this->redirect( $this->generateUrl($this->siteName.'-nopermission') );
        }

        $em = $this->getDoctrine()->getManager();

        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:UserRequest'] by [UserRequest::class]
        $entity = $em->getRepository(UserRequest::class)->find($id);

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


    
    
    #[Route(path: '/account-requests/{id}/{status}/status', name: 'employees_accountrequest_status', methods: ['GET'], requirements: ['id' => '\d+'])]
    #[Template('AppUserdirectoryBundle/UserRequest/index.html.twig')]
    public function statusAction($id, $status)
    {
        
        if (false === $this->isGranted($this->roleEditor)) {
            return $this->redirect( $this->generateUrl($this->siteName.'-nopermission') );
        }
        
        $em = $this->getDoctrine()->getManager();

        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:UserRequest'] by [UserRequest::class]
        $entity = $em->getRepository(UserRequest::class)->find($id);

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
     */
    #[Route(path: '/account-requests-approve', name: 'employees_accountrequest_approve', methods: ['POST'])]
    #[Template('AppUserdirectoryBundle/UserRequest/index.html.twig')]
    public function approveUserAccountRequestAction(Request $request)
    {

        if (false === $this->isGranted($this->roleEditor)) {
            return $this->redirect( $this->generateUrl($this->siteName.'-nopermission') );
        }

        $em = $this->getDoctrine()->getManager();
        $entity = new UserRequest();

        $params = $this->getParams($this->siteName, $request);

        $form = $this->createForm(UserRequestApproveType::class,$entity,array('form_custom_value'=>$params));
        $form->handleRequest($request);

        if( $entity->getSiteName() == "scan" ) {
            //Case 1: scan order with pacsvendor user
            if ($entity->getId() &&
                $entity->getId() != "" &&
                $entity->getUsername() &&
                $entity->getUsername() != "" &&
                //count($entity->getRequestedInstitutionalPHIScope()) != 0 &&
                //$entity->getRequestedScanOrderInstitutionScope() &&
                $entity->getRequestedInstitutionScope() &&
                $entity->getUsername()
            ) {

//            echo "form valid!";
//            echo "getRequestedScanOrderInstitutionScope=".$entity->getRequestedScanOrderInstitutionScope();
//            exit();

                $entityDb = $em->getRepository(UserRequest::class)->findOneById($entity->getId());
                if (!$entityDb) {
                    throw $this->createNotFoundException('Unable to find UserRequest entity with ID:' . $entity->getId());
                }

                $entityDb->setStatus('approved');
                $entityDb->setUsername($entity->getUsername());
                //$entityDb->setRequestedInstitutionalPHIScope($entity->getRequestedInstitutionalPHIScope());
                //$entityDb->setRequestedScanOrderInstitutionScope($entity->getRequestedScanOrderInstitutionScope());
                $entityDb->setRequestedInstitutionScope($entity->getRequestedInstitutionScope());

                $em->persist($entityDb);
                $em->flush();

                $this->addFlash(
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

//                if (!$entity->getRequestedScanOrderInstitutionScope()) {
//                    $failedArr[] = "organizational group is empty";
//                }
                if (!$entity->getRequestedInstitutionScope()) {
                    $failedArr[] = "organizational group is empty";
                }

                $this->addFlash(
                    'notice',
                    "Approve a user with username " . $entity->getUsername() . " failed." . " " . implode(",", $failedArr)
                );

            }
        } else {
            //Case 2: all other sites, except scan
            if ($entity->getId() &&
                $entity->getId() != "" &&
                //$entity->getRequestedScanOrderInstitutionScope() &&
                $entity->getRequestedInstitutionScope() &&
                count($entity->getRoles()) > 0
            ) {
                //exit('Approve ');

        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:UserRequest'] by [UserRequest::class]
                $entityDb = $em->getRepository(UserRequest::class)->findOneById($entity->getId());
                if (!$entityDb) {
                    throw $this->createNotFoundException('Unable to find UserRequest entity with ID:' . $entity->getId());
                }

                $entityDb->setStatus('approved');
                if( $entity->getUsername() ) {
                    $entityDb->setUsername($entity->getUsername());
                }

                //$entityDb->setRequestedScanOrderInstitutionScope($entity->getRequestedScanOrderInstitutionScope());
                $entityDb->setRequestedInstitutionScope($entity->getRequestedInstitutionScope());

                if( count($entity->getRoles()) > 0 ) {
                    $entityDb->setRoles( $entity->getRoles() );
                }

                $em->persist($entityDb);
                $em->flush();

                $this->addFlash(
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

//                if (!$entity->getRequestedScanOrderInstitutionScope()) {
//                    $failedArr[] = "organizational group is empty";
//                }
                if (!$entity->setRequestedInstitutionScope()) {
                    $failedArr[] = "organizational group is empty";
                }

                $this->addFlash(
                    'notice',
                    "Approve a user with username " . $entity->getUsername() . " failed." . " " . implode(",", $failedArr)
                );
            }
        }


        return $this->redirect($this->generateUrl($this->siteName.'_accountrequest'));
    }

    public function getParams( $sitename, $request=NULL ) {

        $securityUtil = $this->container->get('user_security_utility');
        $userSecUtil = $this->container->get('user_security_utility');
        //$user = $this->container->get('security.context')->getToken()->getUser();
        
        $params = array();

        $em = $this->getDoctrine()->getManager();
        $params['em'] = $em;

        //departments
        $department = $userSecUtil->getAutoAssignInstitution();
        if( !$department ) {
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:Institution'] by [Institution::class]
            $department = $em->getRepository(Institution::class)->findOneByName('Pathology and Laboratory Medicine');
        }

        $params['institution'] = $department;
        $params['sitename'] = $sitename;
        //$params['request'] = $request;

        $systemRequested = $this->getSystemRequestedBySitename($sitename);
        $params['systemRequested'] = $systemRequested;

        //Institution
        //$requestedScanOrderInstitutionScope = $em->getRepository('AppUserdirectoryBundle:Institution')->findBy(array('level'=>0));
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:Institution'] by [Institution::class]
        $repository = $em->getRepository(Institution::class);
        $dql =  $repository->createQueryBuilder("institution");
        $dql->select('institution');
        $dql->leftJoin("institution.types", "types");

        $dql->where("institution.type = 'default' OR institution.type = 'user-added'");
        $dql->andWhere("types.name IS NULL OR types.name != 'Collaboration'");

        $query = $dql->getQuery(); //$query = $em->createQuery($dql);
        $requestedScanOrderInstitutionScope = $query->getResult();

        //$params['requestedInstitutionalPHIScope'] = $requestedInstitutionalPHIScope;
        $params['requestedScanOrderInstitutionScope'] = $requestedScanOrderInstitutionScope;

        //Roles
        $rolesArr = $securityUtil->getSiteRolesKeyValue($this->siteName);
        $params['roles'] = $rolesArr;

        //make mobile phone number required field
        $requireVerifyMobilePhone = $securityUtil->isRequireVerifyMobilePhone($this->siteName);
        $params['requireVerifyMobilePhone'] = $requireVerifyMobilePhone;

        return $params;
    }
//    public function getParams_Old() {
    //
    //        $params = array();
    //
    //        $em = $this->getDoctrine()->getManager();
    //
    //        //departments
    //        $department = $em->getRepository('AppUserdirectoryBundle:Department')->findOneByName('Pathology and Laboratory Medicine');
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
    //        //$services = $em->getRepository('AppUserdirectoryBundle:Service')->findByDepartment($department);
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
     */
    #[Route(path: '/account-requests/{id}/edit', name: 'employees_accountrequest_edit', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function editAction($id)
    {
        if (false === $this->isGranted($this->roleEditor)) {
            return $this->redirect( $this->generateUrl($this->siteName.'-nopermission') );
        }

        $em = $this->getDoctrine()->getManager();

        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:UserRequest'] by [UserRequest::class]
        $entity = $em->getRepository(UserRequest::class)->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find UserRequest entity.');
        }

        $params = $this->getParams();

        $editForm = $this->createForm(UserRequestType::class,$entity,array('form_custom_value'=>$params)); //edit GET

        $deleteForm = $this->createDeleteForm($id);

        //Use new free text field requestedInstitutionScope. Populate it with the original requestedScanOrderInstitutionScope
        $orgGroupsSelect2 = array();
        foreach( $params['requestedScanOrderInstitutionScope'] as $orgGroup) {
            $orgGroupsSelect2[] = array('id' => $orgGroup->getId(), 'text' => $orgGroup->getNodeNameWithRoot());
        }

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
            'sitename' => $this->siteName,
            'orggroups' => $orgGroupsSelect2
        );
    }

    /**
     * Edits an existing UserRequest entity.
     */
    #[Route(path: '/account-requests/{id}', name: 'employees_accountrequest_update', methods: ['PUT'], requirements: ['id' => '\d+'])]
    #[Template('AppUserdirectoryBundle/UserRequest/edit.html.twig')]
    public function updateAction(Request $request, $id)
    {

        if (false === $this->isGranted($this->roleEditor)) {
            return $this->redirect( $this->generateUrl($this->siteName.'-nopermission') );
        }

        $em = $this->getDoctrine()->getManager();

        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:UserRequest'] by [UserRequest::class]
        $entity = $em->getRepository(UserRequest::class)->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find UserRequest entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        $params = $this->getParams();

        $editForm = $this->createForm(UserRequestType::class,$entity,array('form_custom_value'=>$params)); //edit PUT

        $editForm->handleRequest($request);

        if ($editForm->isValid()) {

            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl($this->siteName.'_accountrequest'));
            //return $this->redirect( $this->generateUrl('multy_new') );
            //return $this->redirect($this->generateUrl($this->siteName.'_accountrequest_edit', array('id' => $id)));
        }

        //Use new free text field requestedInstitutionScope. Populate it with the original requestedScanOrderInstitutionScope
        $orgGroupsSelect2 = array();
        foreach( $params['requestedScanOrderInstitutionScope'] as $orgGroup) {
            $orgGroupsSelect2[] = array('id' => $orgGroup->getId(), 'text' => $orgGroup->getNodeNameWithRoot());
        }

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
            'sitename' => $this->siteName,
            'orggroups' => $orgGroupsSelect2
        );
    }
    /**
     * Deletes a UserRequest entity.
     */
    #[Route(path: '/account-requests/{id}', name: 'employees_accountrequest_delete', methods: ['DELETE'], requirements: ['id' => '\d+'])]
    public function deleteAction(Request $request, $id)
    {

        if (false === $this->isGranted($this->roleEditor)) {
            return $this->redirect( $this->generateUrl($this->siteName.'-nopermission') );
        }

        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:UserRequest'] by [UserRequest::class]
            $entity = $em->getRepository(UserRequest::class)->find($id);

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

    private function getSystemRequestedBySitename( $sitename ) {
        $systemAccountRequest = NULL;
        $name = NULL;

        switch( $sitename ) {
            case "employees":
                $name = "ORDER Employee Directory";
                break;
            case "calllog":
                $name = "ORDER Call Log Book";
                break;
            case "dashboard":
                $name = "ORDER Employee Directory";
                break;
            case "deidentifier":
                $name = "ORDER Deidentifier";
                break;
            case "fellapp":
                $name = "ORDER Fellowship Applications";
                break;
            case "scan":
                $name = "ORDER Scan Order";
                break;
            case "resapp":
                $name = "ORDER Employee Directory";
                break;
            case "translationalresearch":
                $name = "ORDER Translational Research";
                break;
            case "vacreq":
                $name = "ORDER Vacation Request";
                break;
            default:
                $name = "ORDER Employee Directory";
        }

        if( $name ) {
            $systemAccountRequest = $this->getDoctrine()->getManager()->getRepository(SourceSystemList::class)->findOneByName($name);
        }

        //echo '$systemRequested='.$systemAccountRequest.'<br>';

        return $systemAccountRequest;
    }

    
}
