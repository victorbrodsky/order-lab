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
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
//use Symfony\Component\Routing\Annotation\Template;

use Symfony\Bridge\Twig\Attribute\Template;


// 127.0.0.1/order/index_dev.php/directory/test/container/test/
//class TestController extends OrderAbstractController
#[Route(path: '/test')]
class TestController extends TestBaseController
{

    #[Route(path: '/container/test/', name: 'user_test_container')]
    #[Template('AppUserdirectoryBundle/Testing/testing.html.twig')]
    public function testContainerAction() {
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
        //$calllogUtilDirect = $this->container->get('calllog_util');
        $calllogUtilDirect = $this->container->get('calllog_util');
        $id = $calllogUtilDirect->getNextEncounterGeneratedId();
        $msg = $msg . "; id=$id";

        $this->addFlash(
            'notice',
            $msg
        );

        return array(
            'user' => $user,
            'title' => "TestController"
        );
    }

    //https://view.online/c/test-institution/test-department/directory/test/test-certificate/
    #[Route(path: '/test-certificate/{domain}', name: 'user_test_certificate')]
    public function testSslCertificateAction(Request $request, $domain=NULL) {
        if( false === $this->isGranted('ROLE_PLATFORM_DEPUTY_ADMIN') ) {
            return $this->redirect($this->generateUrl('employees-nopermission'));
        }

        $userServiceUtil = $this->container->get('user_service_utility');

        //$res = $userServiceUtil->checkSslCertificate3($domain);
        //echo "<br><br>";

        //$res = $userServiceUtil->checkSslCertificate2($domain);
        //echo "<br><br>";

        if(!$domain) {
            $domain = 'view.online';
            echo "testSslCertificateAction: use the default domain=$domain <br>";
        }

        $res1 = $userServiceUtil->checkSslCertificate($domain);
        $daysRemaining = $res1['DaysRemaining'];
        $organization = $res1['Organization'];

        if(0) {
            $daysRemaining = 14;
            //$organization = "Let's Encrypt";
            $res2 = $userServiceUtil->updateSslCertificate(
                $domain,
                $daysRemaining,
                $organization
            );
        }

        exit("Test Certificate: res1=>{$daysRemaining}, {$organization}, res2={$res2}");
    }

}