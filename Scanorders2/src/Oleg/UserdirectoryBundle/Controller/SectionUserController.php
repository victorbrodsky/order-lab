<?php
/**
 * Created by PhpStorm.
 * User: ch3
 * Date: 11/1/2016
 * Time: 10:32 AM
 */

namespace Oleg\UserdirectoryBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class SectionUserController extends UserController
{


    /**
     * @Route("/my-team-ajax/", name="employees_my_team")
     * @Template("OlegUserdirectoryBundle:SectionUser:my-team.html.twig")
     * @Method({"GET", "POST"})
     */
    public function myTeamAction( Request $request ) {

        if( false === $this->get('security.context')->isGranted('ROLE_USERDIRECTORY_OBSERVER') ) {
            return $this->redirect($this->generateUrl('employees-nopermission'));
        }

        $em = $this->getDoctrine()->getManager();

        $userid = $request->query->get('userid');
        $teamType = $request->query->get('teamType');
        //echo "userid=".$userid."<br>";

        $user = $em->getRepository('OlegUserdirectoryBundle:User')->find($userid);

        return array(
            'user' => $user,
            'cycle' => 'show_user',
            'teamType' => $teamType,
            'postData' => '',
            'sitename' => $this->container->getParameter('employees.sitename'),
        );

//        $showUserArr = $this->showUser($userid,$this->container->getParameter('employees.sitename'),false);
//
//        $template = $this->render('OlegUserdirectoryBundle:Profile:edit_user_only.html.twig',$showUserArr)->getContent();
//
//        $json = json_encode($template);
//        $response = new Response($json);
//        $response->headers->set('Content-Type', 'application/json');
//        return $response;
    }


}