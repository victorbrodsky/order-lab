<?php

namespace Oleg\UserdirectoryBundle\Controller;



use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;


class DefaultController extends Controller
{

    /**
     * @Route("/thanks-for-downloading/{id}", name="common_thankfordownloading")
     * @Template("OlegUserdirectoryBundle:Default:thanksfordownloading.html.twig")
     * @Method("GET")
     */
    public function thankfordownloadingAction(Request $request, $id) {
        //return $this->redirect( $this->generateUrl('employees_file_download',array('id'=>$id)) );
        return array(
            'fileid' => $id
        );
    }

//    /**
//     * @Route("/", name="employees_home")
//     * @Template("OlegUserdirectoryBundle:Default:home.html.twig")
//     */
//    public function indexAction()
//    {
//
//        if(
//            false == $this->get('security.context')->isGranted('ROLE_USER') ||              // authenticated (might be anonymous)
//            false == $this->get('security.context')->isGranted('IS_AUTHENTICATED_FULLY')    // authenticated (NON anonymous)
//        ){
//            return $this->redirect( $this->generateUrl('login') );
//        }
//
//        //$form = $this->createForm(new SearchType(),null);
//
//        //$form->bind($request);  //use bind instead of handleRequest. handleRequest does not get filter data
//        //$search = $form->get('search')->getData();
//
//        //check for active access requests
//        $accessreqs = $this->getActiveAccessReq();
//
//
//        return array(
//            'accessreqs' => count($accessreqs)
//            //'form' => $form->createView(),
//        );
//    }
//
//    //check for active access requests
//    public function getActiveAccessReq() {
//        if( !$this->get('security.context')->isGranted('ROLE_USERDIRECTORY_ADMIN') ) {
//            return null;
//        }
//        $userSecUtil = $this->get('user_security_utility');
//        $accessreqs = $userSecUtil->getUserAccessRequestsByStatus($this->container->getParameter('employees.sitename'),AccessRequest::STATUS_ACTIVE);
//        return $accessreqs;
//    }


//    /**
//     * @Route("/admin", name="employees_admin")
//     * @Template("OlegUserdirectoryBundle:Default:index.html.twig")
//     */
//    public function adminAction()
//    {
//        $name = "This is an Employee Directory Admin Page!!!";
//        return array('name' => $name);
//    }
//
//
//    /**
//     * @Route("/hello/{name}", name="employees_hello")
//     * @Template()
//     */
//    public function helloAction($name)
//    {
//        return array('name' => $name);
//    }



}
