<?php

namespace Oleg\UserdirectoryBundle\Controller;

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

        $editForm = $this->createEditForm($sitename,$entity,$disabled);

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
     * Creates a form to edit a SiteParameters entity.
     *
     * @param SiteParameters $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createEditForm($sitename,SiteParameters $entity, $param=null,$disabled=false)
    {

        $cycle = 'show';

        if( !$disabled ) {
            $cycle = 'edit';
        }
        $params = array('sitename'=>$sitename,'cycle'=>$cycle,'param'=>$param);

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
