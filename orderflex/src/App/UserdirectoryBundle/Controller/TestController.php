<?php
/**
 * Created by PhpStorm.
 * User: ch3
 * Date: 2/21/2020
 * Time: 7:59 AM
 */

namespace App\UserdirectoryBundle\Controller;

use App\CallLogBundle\Util\CallLogUtil;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
//use Symfony\Component\Routing\Annotation\Template;
//use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;


// 127.0.0.1/order/index_dev.php/directory/test/container/test/
/**
 * @Route("/test")
 */
class TestController extends AbstractController
//class TestController extends TestBaseController
{

    /**
     * @Route("/container/test/", name="user_test_container")
     * @Template("AppUserdirectoryBundle/Testing/testing.html.twig")
     */
    public function testContainerAction( CallLogUtil $calllogUtil ) {
        if( false === $this->get('security.authorization_checker')->isGranted('ROLE_PLATFORM_DEPUTY_ADMIN') ) {
            return $this->redirect($this->generateUrl('employees-nopermission'));
        }

        $msg = "Container Test";
        $em = $this->getDoctrine()->getManager();
        //$calllogUtil = $this->get('calllog_util');
        $user = $this->get('security.token_storage')->getToken()->getUser();

        $user = $em->getRepository('AppUserdirectoryBundle:User')->find($user->getId());
        $msg = $msg . "; user=$user";

        $id = $calllogUtil->getNextEncounterGeneratedId();
        $msg = $msg . "; id=$id";

        $this->get('session')->getFlashBag()->add(
            'notice',
            $msg
        );

        return array(
            'user' => $user,
            'title' => "TestController"
        );
    }

}