<?php
/**
 * Created by PhpStorm.
 * User: ch3
 * Date: 2/21/2020
 * Time: 7:59 AM
 */

namespace App\UserdirectoryBundle\Controller;

use App\CallLogBundle\Util\CallLogUtil;
use App\UserdirectoryBundle\Controller\OrderAbstractController;
use App\UserdirectoryBundle\Entity\User;
use Symfony\Component\Routing\Annotation\Route;
//use Symfony\Component\Routing\Annotation\Template;

use Symfony\Bridge\Twig\Attribute\Template;


// 127.0.0.1/order/index_dev.php/directory/test/container/testbase/
#[Route(path: '/test')]
class TestBaseController extends OrderAbstractController
{

    #[Route(path: '/container/testbase/', name: 'user_testbase_container')]
    #[Template('AppUserdirectoryBundle/Testing/testing.html.twig')]
    public function testbaseContainerAction() {
    //public function testContainerAction( CallLogUtil $calllogUtil ) {
        if( false === $this->isGranted('ROLE_PLATFORM_DEPUTY_ADMIN') ) {
            return $this->redirect($this->generateUrl('employees-nopermission'));
        }

        $msg = "Container Test";
        $em = $this->getDoctrine()->getManager();
        //$calllogUtil = $this->container->get('calllog_util');
        $user = $this->getUser();

        $user = $em->getRepository(User::class)->find($user->getId());
        $msg = $msg . "; user=$user";

        //$id = $calllogUtil->getNextEncounterGeneratedId();
        //$msg = $msg . "; id=$id";

        //$this->container ContainerInterface $container
        //https://github.com/symfony/symfony/blob/5.0/src/Symfony/Bundle/FrameworkBundle/Controller/AbstractController.php
        $calllogUtilDirect = $this->container->get('calllog_util');
        $id = $calllogUtilDirect->getNextEncounterGeneratedId();
        $msg = $msg . "; id=$id";

        $this->addFlash(
            'notice',
            $msg
        );

        return array(
            'user' => $user,
            'title' => "TestBaseController"
        );
    }


}