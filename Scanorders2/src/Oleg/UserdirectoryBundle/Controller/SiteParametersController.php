<?php

namespace Oleg\UserdirectoryBundle\Controller;

use Doctrine\Common\Collections\ArrayCollection;
use Oleg\UserdirectoryBundle\Entity\OrganizationalGroupDefault;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Oleg\UserdirectoryBundle\Entity\SiteParameters;
use Oleg\UserdirectoryBundle\Form\SiteParametersType;
use Oleg\UserdirectoryBundle\Util\UserUtil;

/**
 * SiteParameters controller.
 *
 * @Route("/settings")
 */
class SiteParametersController extends Controller
{

    /**
     * Lists all SiteParameters entities.
     *
     * @Route("/", name="employees_siteparameters")
     * @Method("GET")
     * @Template("OlegUserdirectoryBundle:SiteParameters:index.html.twig")
     */
    public function indexAction(Request $request)
    {
        return $this->indexParameters($request);
    }

    public function indexParameters($request) {

        if( false === $this->get('security.context')->isGranted('ROLE_PLATFORM_DEPUTY_ADMIN') ) {
            return $this->redirect( $this->generateUrl('employees-nopermission') );
        }

        //testing email
        //$emailUtil = $this->container->get('user_mailer_utility');
        //$emailUtil->sendEmail( "oli2002@med.cornell.edu", "testing email", "testing email" );

        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('OlegUserdirectoryBundle:SiteParameters')->findAll();

        if( count($entities) != 1 ) {
            throw new \Exception( 'Must have only one parameter object. Found '.count($entities).'object(s)' );
        }

        $entity = $entities[0];

        $routeName = $request->get('_route');
        $routeArr = explode("_", $routeName);
        $sitename = $routeArr[0];

        $disabled = true;

        $passw = "*******";
        if( $entity->getAperioeSlideManagerDBPassword() != '' )
            $entity->setAperioeSlideManagerDBPassword($passw);

        if( $entity->getCoPathDBAccountPassword() != '' )
            $entity->setCoPathDBAccountPassword($passw);

        if( $entity->getADLDAPServerAccountPassword() != '' )
            $entity->setADLDAPServerAccountPassword($passw);

        if( $entity->getDbServerAccountPassword() != '' )
            $entity->setDbServerAccountPassword($passw);

        //testing
        //$organizationalGroupDefault = new OrganizationalGroupDefault();
        //$entity->addOrganizationalGroupDefault($organizationalGroupDefault);
//        foreach( $entity->getOrganizationalGroupDefaults() as $groupDefault ) {
//            echo "roles=".$groupDefault->getRoles()."<br>";
//            print_r($groupDefault->getRoles());
//        }

        //$sitename,SiteParameters $entity, $param=null, $disabled=false
        $editForm = $this->createEditForm($sitename,$entity,null,$disabled);

        $link = realpath($_SERVER['DOCUMENT_ROOT']).'\order\scanorder\Scanorders2\app\config\parameters.yml';
        //echo "link=".$link."<br>";

        //get absolute path prefix for Upload folder
        $rootDir = $this->container->get('kernel')->getRootDir(); //C:\Users\ch3\Documents\MyDocs\WCMC\ORDER\scanorder\Scanorders2\app
        $rootDir = str_replace('app','',$rootDir);
        $uploadPath = $rootDir . 'web\\';

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'cycle' => 'show',
            'link' => $link,
            'uploadPath' => $uploadPath,
            'sitename' => $sitename,
            'phphostname' => gethostname()
        );
    }

    /**
     * Displays a form to edit an existing SiteParameters entity.
     *
     * @Route("/{id}/edit", name="employees_siteparameters_edit")
     * @Method("GET")
     * @Template("OlegUserdirectoryBundle:SiteParameters:edit.html.twig")
     */
    public function editAction(Request $request,$id)
    {
        return $this->editParameters($request,$id);
    }

    public function editParameters(Request $request,$id)
    {
        if( false === $this->get('security.context')->isGranted('ROLE_PLATFORM_DEPUTY_ADMIN') ) {
            return $this->redirect( $this->generateUrl('employees-nopermission') );
        }

        $param = trim( $request->get('param') );

        $routeName = $request->get('_route');
        $routeArr = explode("_", $routeName);
        $sitename = $routeArr[0];

        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('OlegUserdirectoryBundle:SiteParameters')->find($id);

        if( !$entity ) {
            throw $this->createNotFoundException('Unable to find SiteParameters entity.');
        }

        $editForm = $this->createEditForm($sitename,$entity,$param,false);
        //$deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'cycle' => 'edit',
            'param' => $param,
            'sitename' => $sitename
            //'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Edits an existing SiteParameters entity.
     *
     * @Route("/{id}", name="employees_siteparameters_update")
     * @Method("PUT")
     * @Template("OlegUserdirectoryBundle:SiteParameters:edit.html.twig")
     */
    public function updateAction(Request $request, $id)
    {
        return $this->updateParameters($request, $id);
    }

    public function updateParameters(Request $request, $id)
    {

        if( false === $this->get('security.context')->isGranted('ROLE_PLATFORM_DEPUTY_ADMIN') ) {
            return $this->redirect( $this->generateUrl('employees-nopermission') );
        }

        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('OlegUserdirectoryBundle:SiteParameters')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find SiteParameters entity.');
        }

        $param = trim( $request->get('param') );
        //echo "param=".$param."<br>";

        $routeName = $request->get('_route');
        $routeArr = explode("_", $routeName);
        $sitename = $routeArr[0];

        $editForm = $this->createEditForm($sitename,$entity,$param,false);

        $editForm->handleRequest($request);

        if( $editForm->isValid() ) {
            $em->flush();

            return $this->redirect($this->generateUrl($sitename.'_siteparameters'));
        }

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'cycle' => 'edit',
            'param' => '',
            'sitename' => $sitename
        );
    }


    /**
     * Edits an existing SiteParameters entity.
     *
     * @Route("/organizational-group-default-management/{id}", name="employees_management_organizationalgroupdefault")
     * @Method({"GET","POST"})
     * @Template("OlegUserdirectoryBundle:SiteParameters:group-management-form.html.twig")
     */
    public function manageOrgGroupDefaultAction(Request $request, $id)
    {

        if( false === $this->get('security.context')->isGranted('ROLE_PLATFORM_DEPUTY_ADMIN') ) {
            return $this->redirect( $this->generateUrl('employees-nopermission') );
        }

        $em = $this->getDoctrine()->getManager();

        $routeName = $request->get('_route');
        $routeArr = explode("_", $routeName);
        $sitename = $routeArr[0];

        $entity = $em->getRepository('OlegUserdirectoryBundle:SiteParameters')->find($id);

        if( !$entity ) {
            throw $this->createNotFoundException('Unable to find SiteParameters entity.');
        }

        //testing
        if( count($entity->getOrganizationalGroupDefaults()) == 0 ) {
            $organizationalGroupDefault = new OrganizationalGroupDefault();
            $entity->addOrganizationalGroupDefault($organizationalGroupDefault);
        }

        $originalGroups = new ArrayCollection();
        // Create an ArrayCollection of the current Tag objects in the database
        foreach( $entity->getOrganizationalGroupDefaults() as $groupDefault ) {
            $originalGroups->add($groupDefault);
        }

        //Roles
        $rolesArr = $this->getUserRoles();

        $params = array(
            'sitename'=>$sitename,
            'cycle'=>"edit",
            'em'=>$em,
            'roles' => $rolesArr
        );

        $form = $this->createForm(new SiteParametersType($params), $entity, array(
            'action' => $this->generateUrl('employees_management_organizationalgroupdefault', array('id' => $entity->getId() )),
            'method' => 'POST',
            'disabled' => false,
        ));

        $form->add('save', 'submit', array(
            'label' => 'Update',
            'attr' => array('class'=>'btn btn-primary')
        ));

        $form->handleRequest($request);

        //check for empty target institution
        if( $form->isSubmitted() ) {

            $instArr = new ArrayCollection();
            foreach ($entity->getOrganizationalGroupDefaults() as $organizationalGroupDefault) {
                if (!$organizationalGroupDefault->getInstitution()) {
                    $form->addError(new FormError('Please select the Target Institution for all Organizational Group Management Sections'));
                    //$form['organizationalGroupDefaults']->addError(new FormError('Please select the Target Institution'));
                    //$entity->removeOrganizationalGroupDefault($organizationalGroupDefault);
                } else {
                    if( $instArr->contains($organizationalGroupDefault->getInstitution()) ) {
                        $form->addError(new FormError('Please make sure that the Target Institutions are Unique in all Organizational Group Management Sections'));
                    } else {
                        $instArr->add($organizationalGroupDefault->getInstitution());
                    }
                }
            }

        }

//        if( $form->isSubmitted() ) {
//            echo "form is submitted<br>";
//
//            if( $form->isValid() ) {
//                echo "form is valid<br>";
//            } else {
//                print_r((string) $form->getErrors());     // Main errors
//                print_r((string) $form->getErrors(true)); // Main and child errors
//                //exit('1');
//            }
//        }

        if( $form->isSubmitted() && $form->isValid() ) {

            //exit("form is valid");

            // remove the relationship between the tag and the Task
            foreach( $originalGroups as $originalGroup ) {
                $currentGroups = $entity->getOrganizationalGroupDefaults();
                if( false === $currentGroups->contains($originalGroup) ) {
                    // remove the Task from the Tag
                    $entity->removeOrganizationalGroupDefault($originalGroup);

                    // if it was a many-to-one relationship, remove the relationship like this
                    $originalGroup->setSiteParameter(null);

                    $em->persist($originalGroup);
                    // if you wanted to delete the Tag entirely, you can also do that
                    $em->remove($originalGroup);
                }
            }

            //testing
//            foreach( $entity->getOrganizationalGroupDefaults() as $group ) {
//                echo "primary Type=".$group->getPrimaryPublicUserIdType()."<br>";
//            }
            //exit('1');

            //$em->persist($entity);
            $em->flush();

            $this->get('session')->getFlashBag()->add(
                'notice',
                "Defaults for an Organizational Group have been updated."
            );

            return $this->redirect($this->generateUrl($sitename.'_siteparameters'));
        }

        return array(
            'entity'      => $entity,
            'form'   => $form->createView(),
            'cycle' => 'edit',
            'sitename' => $sitename
        );
    }



    /**
     * Creates a form to edit a SiteParameters entity.
     *
     * @param SiteParameters $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createEditForm( $sitename,SiteParameters $entity, $param=null, $disabled=false )
    {
        $em = $this->getDoctrine()->getManager();

        $cycle = 'show';

        if( !$disabled ) {
            $cycle = 'edit';
        }

        //Roles
        $rolesArr = $this->getUserRoles();

        $params = array(
            'sitename'=>$sitename,
            'cycle'=>$cycle,
            'em'=>$em,
            'param'=>$param,
            'roles'=>$rolesArr
        );

        $form = $this->createForm(new SiteParametersType($params), $entity, array(
            'action' => $this->generateUrl($sitename.'_siteparameters_update', array('id' => $entity->getId(), 'param' => $param )),
            'method' => 'PUT',
            'disabled' => $disabled
        ));

        if( $disabled === false ) {
            $form->add('submit', 'submit', array('label' => 'Update', 'attr'=>array('class'=>'btn btn-warning','style'=>'margin-top: 15px;')));
        }

        return $form;
    }

    public function getUserRoles() {
        $rolesArr = array();
        $em = $this->getDoctrine()->getManager();
        $roles = $em->getRepository('OlegUserdirectoryBundle:Roles')->findBy(
            array('type' => array('default','user-added')),
            array('orderinlist' => 'ASC')
        );  //findAll();
        foreach( $roles as $role ) {
            $rolesArr[$role->getName()] = $role->getAlias();
        }
        return $rolesArr;
    }



//    /**
//     * Displays a admin email.
//     *
//     * @Route("/scan-order/admin-email", name="scan-order-admin-email")
//     * @Method("GET")
//     * @Template("OlegOrderformBundle:History:index.html.twig")
//     */
//    public function getAdminEmailAction()
//    {
//
//        $userutil = new UserUtil();
//        $em = $this->getDoctrine()->getManager();
//        $adminemail = $userutil->getSiteSetting($em,'siteEmail');
//
//        $response = new Response();
//        $response->setContent($adminemail);
//
//        return $response;
//    }
//
    //    /**
//     * Creates a new SiteParameters entity.
//     *
//     * @Route("/", name="siteparameters_create")
//     * @Method("POST")
//     * @Template("OlegOrderformBundle:SiteParameters:new.html.twig")
//     */
//    public function createAction(Request $request)
//    {
//        $entity = new SiteParameters();
//        $form = $this->createCreateForm($entity);
//        $form->handleRequest($request);
//
//        if( $form->isValid() ) {
//            //echo "par not valid!";
//            //exit();
//            $em = $this->getDoctrine()->getManager();
//            $em->persist($entity);
//            $em->flush();
//
////            return $this->redirect($this->generateUrl('siteparameters_show', array('id' => $entity->getId())));
//            return $this->redirect($this->generateUrl('siteparameters'));
//        }
//
//        return array(
//            'entity' => $entity,
//            'form'   => $form->createView(),
//        );
//    }

//    /**
//    * Creates a form to create a SiteParameters entity.
//    *
//    * @param SiteParameters $entity The entity
//    *
//    * @return \Symfony\Component\Form\Form The form
//    */
//    private function createCreateForm(SiteParameters $entity)
//    {
//        $form = $this->createForm(new SiteParametersType(), $entity, array(
//            'action' => $this->generateUrl('siteparameters_create'),
//            'method' => 'POST',
//        ));
//
//        $form->add('submit', 'submit', array('label' => 'Create'));
//
//        return $form;
//    }

//    /**
//     * Displays a form to create a new SiteParameters entity.
//     *
//     * @Route("/new", name="siteparameters_new")
//     * @Method("GET")
//     * @Template()
//     */
//    public function newAction()
//    {
//        $entity = new SiteParameters();
//        $form   = $this->createCreateForm($entity);
//
//        return array(
//            'entity' => $entity,
//            'form'   => $form->createView(),
//        );
//    }

//    /**
//     * Finds and displays a SiteParameters entity.
//     *
//     * @Route("/{id}", name="siteparameters_show")
//     * @Method("GET")
//     * @Template()
//     */
//    public function showAction($id)
//    {
//        $em = $this->getDoctrine()->getManager();
//
//        $entity = $em->getRepository('OlegOrderformBundle:SiteParameters')->find($id);
//
//        if (!$entity) {
//            throw $this->createNotFoundException('Unable to find SiteParameters entity.');
//        }
//
//        $deleteForm = $this->createDeleteForm($id);
//
//        return array(
//            'entity'      => $entity,
//            'delete_form' => $deleteForm->createView(),
//        );
//    }

//    /**
//     * Deletes a SiteParameters entity.
//     *
//     * @Route("/{id}", name="siteparameters_delete")
//     * @Method("DELETE")
//     */
//    public function deleteAction(Request $request, $id)
//    {
//        $form = $this->createDeleteForm($id);
//        $form->handleRequest($request);
//
//        if ($form->isValid()) {
//            $em = $this->getDoctrine()->getManager();
//            $entity = $em->getRepository('OlegOrderformBundle:SiteParameters')->find($id);
//
//            if (!$entity) {
//                throw $this->createNotFoundException('Unable to find SiteParameters entity.');
//            }
//
//            $em->remove($entity);
//            $em->flush();
//        }
//
//        return $this->redirect($this->generateUrl('siteparameters'));
//    }

//    /**
//     * Creates a form to delete a SiteParameters entity by id.
//     *
//     * @param mixed $id The entity id
//     *
//     * @return \Symfony\Component\Form\Form The form
//     */
//    private function createDeleteForm($id)
//    {
//        return $this->createFormBuilder()
//            ->setAction($this->generateUrl('siteparameters_delete', array('id' => $id)))
//            ->setMethod('DELETE')
//            ->add('submit', 'submit', array('label' => 'Delete'))
//            ->getForm()
//        ;
//    }

}
