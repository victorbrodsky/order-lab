<?php

/**
 * Created by PhpStorm.
 * User: Oleg Ivanov
 * Date: 12/12/2019
 * Time: 8:23 AM
 */

namespace Tests\Oleg\TranslationalResearchBundle\Util;

use PHPUnit\Framework\TestCase;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;


class OlegWebTestBase extends WebTestCase
{

    /**
     * @var \Doctrine\ORM\EntityManager
     */
    protected $em;
    protected $container;
    protected $client = null;
    protected $user = null;

    protected function setUp()
    {
//        $kernel = self::bootKernel();
//
//        $this->container = $kernel->getContainer();
//
//        $this->em = $this->container
//            ->get('doctrine')
//            ->getManager();
//
//        $this->client = static::createClient([], [
//            'HTTP_HOST'       => '127.0.0.1',
//            'HTTP_USER_AGENT' => 'MySuperBrowser/1.0',
//        ]);

        $this->client = static::createClient([], [
            'HTTP_HOST'       => '127.0.0.1',
            'HTTP_USER_AGENT' => 'MySuperBrowser/1.0',
        ]);

        $this->container = $this->client->getContainer();

        $this->em = $this->container->get('doctrine.orm.entity_manager');

        $this->user = $this->getUser();
    }

    /**
     * {@inheritDoc}
     */
    protected function tearDown()
    {
        parent::tearDown();

        if($this->em) {
            $this->em->close();
            $this->em = null; // avoid memory leaks
        }

        $this->container = null; // avoid memory leaks

        $this->client = null;

        $this->user = null;
    }

    public function logIn() {

        $session = $this->client->getContainer()->get('session');

        //$firewallName = 'external_ldap_firewall';
        // if you don't define multiple connected firewalls, the context defaults to the firewall name
        // See https://symfony.com/doc/current/reference/configuration/security.html#firewall-context
        $firewallContext = 'scan_auth';
        $firewallName = 'ldap_fellapp_firewall';

        $systemUser = $this->getUser();
        //$systemUser = $this->em->getRepository('OlegUserdirectoryBundle:User')->findOneByUsername('administrator');

        $token = new UsernamePasswordToken($systemUser, null, $firewallName, $systemUser->getRoles());
        $this->container->get('security.token_storage')->setToken($token);

        $session->set('_security_'.$firewallContext, serialize($token));
    }

    public function getUser()
    {
        $userSecUtil = $this->container->get('user_security_utility');
        $systemUser = $userSecUtil->findSystemUser();

        if( !$systemUser ) {
            $systemUser = $this->em->getRepository('OlegUserdirectoryBundle:User')->findOneByPrimaryPublicUserId('Administrator');
        }

        if( !$systemUser ) {
            $systemUser = $this->em->getRepository('OlegUserdirectoryBundle:User')->findOneByPrimaryPublicUserId('administrator');
        }

        return $systemUser;
    }



}