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
use Symfony\Component\Routing\Annotation\Route;
//use Symfony\Component\Routing\Annotation\Template;
//use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;


// 127.0.0.1/order/index_dev.php/directory/test/container/test/
/**
 * @Route("/test")
 */
//class TestController extends OrderAbstractController
class TestController extends TestBaseController
{

    /**
     * @Route("/container/test/", name="user_test_container")
     * @Template("AppUserdirectoryBundle/Testing/testing.html.twig")
     */
    public function testContainerAction() {
    //public function testContainerAction( CallLogUtil $calllogUtil ) {
        if( false === $this->get('security.authorization_checker')->isGranted('ROLE_PLATFORM_DEPUTY_ADMIN') ) {
            return $this->redirect($this->generateUrl('employees-nopermission'));
        }

        $msg = "Container Test";
        $em = $this->getDoctrine()->getManager();
        //$calllogUtil = $this->get('calllog_util');
        $user = $this->get('security.token_storage')->getToken()->getUser();

        $user = $em->getRepository('AppUserdirectoryBundle:User')->find($user->getId());
        $msg = $msg . "; user=$user";

        //$id = $calllogUtil->getNextEncounterGeneratedId();
        //$msg = $msg . "; id=$id";

        //$this->container ContainerInterface $container
        //https://github.com/symfony/symfony/blob/5.0/src/Symfony/Bundle/FrameworkBundle/Controller/AbstractController.php
        //$calllogUtilDirect = $this->container->get('calllog_util');
        $calllogUtilDirect = $this->get('calllog_util');
        $id = $calllogUtilDirect->getNextEncounterGeneratedId();
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

//    //Check for auto-injection deprecation notice
//    //1) Create OrderAbstarctController extends OrderAbstractController
//    //2) Override getSubscribedServices adding util services
//    //3) replace in controller $this->get by $this->container->get
//    public static function getSubscribedServices()
//    {
//        return [
//            'router' => '?'.RouterInterface::class,
//            'request_stack' => '?'.RequestStack::class,
//            'http_kernel' => '?'.HttpKernelInterface::class,
//            'serializer' => '?'.SerializerInterface::class,
//            'session' => '?'.SessionInterface::class,
//            'security.authorization_checker' => '?'.AuthorizationCheckerInterface::class,
//            'twig' => '?'.Environment::class,
//            'doctrine' => '?'.ManagerRegistry::class,
//            'form.factory' => '?'.FormFactoryInterface::class,
//            'security.token_storage' => '?'.TokenStorageInterface::class,
//            'security.csrf.token_manager' => '?'.CsrfTokenManagerInterface::class,
//            'parameter_bag' => '?'.ContainerBagInterface::class,
//            'message_bus' => '?'.MessageBusInterface::class,
//            'messenger.default_bus' => '?'.MessageBusInterface::class,
//            'calllog_util' => '?'.CallLogUtil::class,
//        ];
//    }
}