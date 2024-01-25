<?php
/**
 * Created by PhpStorm.
 * User: ch3
 * Date: 1/25/2024
 * Time: 12:35 PM
 */

namespace App\Routing\Controller;

use App\UserdirectoryBundle\Controller\OrderAbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bridge\Twig\Attribute\Template;


class SystemTenancyController extends OrderAbstractController
{

    #[Route(path: '/', name: 'system-home')]
    #[Template('AppRouting/home/system-home.html.twig')]
    public function systemHomeAction(Request $request)
    {
        //exit("systemHomeAction");

        $title = "System";

        return array(
            'title' => $title
        );
    }

    #[Route(path: '/manager/', name: 'system-manager')]
    #[Route(path: '/manager/https', name: 'system-manager-https')]
    public function systemManagerAction(Request $request)
    {
        exit("systemManagerAction");
        $logger = $this->container->get('logger');
        $em = $this->getDoctrine()->getManager();
        $users = $roles = $em->getRepository(User::class)->findAll();
        $logger->notice('firstTimeLoginGenerationAction: users='.count($users));

        if (count($users) == 0) {

            //1) get systemuser
            //$userSecUtil = new UserSecurityUtil($em, null);
            $userSecUtil = $this->container->get('user_security_utility');
            $systemuser = $userSecUtil->findSystemUser();

            //$this->generateSitenameList($systemuser);

            if (!$systemuser) {

                $logger->notice('Start generate system user');
                $default_time_zone = null;
                $usernamePrefix = "local-user";

                //$userUtil = new UserUtil();
                $userUtil = $this->container->get('user_utility');
                $userUtil->generateUsernameTypes(null, false);
                //$userkeytype = $em->getRepository('AppUserdirectoryBundle:UsernameType')->findOneByAbbreviation("local-user");

                $this->generateSitenameList(null);

                $userSecUtil = $this->container->get('user_security_utility');
                $userkeytype = $userSecUtil->getUsernameType($usernamePrefix);

                //echo "userkeytype=".$userkeytype."; ID=".$userkeytype->getId()."<br>";

                $systemuser = $userUtil->createSystemUser($userkeytype,$default_time_zone);

                //echo "0 systemuser=".$systemuser."; username=".$systemuser->getUsername()."; ID=".$systemuser->getId()."<br>";

                //set unique username
                //$usernameUnique = $systemuser->createUniqueUsername();
                //$systemuser->setUsername($usernameUnique);
                //$systemuser->setUsernameCanonical($usernameUnique);

                //exit("1 systemuser=".$systemuser."; username=".$systemuser->getUsername()."; ID=".$systemuser->getId()."<br>");

                //$systemuser->setUsername("system_@_local-user");
                //$systemuser->setUsernameCanonical("system_@_local-user");

                //$encoder = $this->container->get('security.password_encoder');
                //$encoded = $encoder->encodePassword($systemuser, "systemuserpass");
                $authUtil = $this->container->get('authenticator_utility');
                $encoded = $authUtil->getEncodedPassword($systemuser,"systemuserpass");

                $systemuser->setPassword($encoded);
                $systemuser->setLocked(false);

                $em->persist($systemuser);
                $em->flush();

                $logger->notice('Finished generate system user: '.$systemuser);
                //exit("system user created");
            }

            $adminRes = $this->generateAdministratorAction(true);
            $logger->notice('Finished generate AdministratorAction. adminRes='.$adminRes);
            //exit($adminRes);

            //TODO: $channel
            //if( $channel && $channel == "https" ) {
            if( $request->get('_route') == "first-time-login-generation-init-https" ) {
                //set channel in SiteParameters to https
                $entities = $em->getRepository(SiteParameters::class)->findAll();
                if (count($entities) != 1) {
                    $userServiceUtil = $this->container->get('user_service_utility');
                    $userServiceUtil->generateSiteParameters();
                    $entities = $em->getRepository(SiteParameters::class)->findAll();
                }
                if (count($entities) != 1) {
                    exit('Must have only one parameter object. Found ' . count($entities) . ' object(s)');
                    //throw new \Exception( 'Must have only one parameter object. Found '.count($entities).' object(s)' );
                }
                $entity = $entities[0];
                $entity->setConnectionChannel("https");
                //$em->flush($entity);
                $em->flush();
            }

            $logger->notice('Start updateApplication');
            $updateres = $this->updateApplication();

            $adminRes = $adminRes . " <br> " .$updateres;

            $logger->notice('Finished initialization. adminRes='.$adminRes);

        } else {
            //$adminRes = 'Admin user already exists';
            //$adminRes = "System has been initialized successfully.";
            $adminRes = 'Admin user has been successfully created.';
            //exit('users already exists');
            $logger->notice('Finished initialization. users already exists');
        }


        $this->addFlash(
            'notice',
            $adminRes
        );

//        //make sure sitesettings is initialized
//        $siteParams = $em->getRepository(SiteParameters::class)->findAll();
//        if( count($siteParams) != 1 ) {
//            $userServiceUtil = $this->container->get('user_service_utility');
//            $userServiceUtil->generateSiteParameters();
//        }

        return $this->redirect($this->generateUrl('employees_home'));
    }

}