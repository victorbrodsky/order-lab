<?php

/**
 * Created by PhpStorm.
 * User: Oleg Ivanov
 * Date: 12/12/2019
 * Time: 8:23 AM
 */

namespace Tests\Oleg\TestBundle;

use PHPUnit\Framework\TestCase;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

//1) Make sure to include in composer.json "Tests\\": "tests/" under autoload:
//"autoload": {
//    "psr-4": {
//        "": "src/",
//            "Tests\\": "tests/"
//        },
//        "classmap": [
//        "app/AppKernel.php",
//        "app/AppCache.php"
//    ]
//},
//2) Run composer.phar dumpautoload

class WebTestBase extends WebTestCase
{

    /**
     * @var \Doctrine\ORM\EntityManager
     */
    protected $em;
    protected $container;
    protected $client = null;
    protected $user = null;


//    public function testGetLink($linkName,$expectedText) {
//        $this->logIn();
//
//        //echo "linkName=$linkName \n\r";
//        $crawler = $this->client->request('GET',$linkName);
//        //$crawler = $this->client->request('GET', '/translational-research/about');
//
//        //$content = $this->client->getResponse()->getContent();
//        //exit("content=$content");
//
//        $this->assertGreaterThan(
//            0,
//            $crawler->filter('html:contains('.$expectedText.')')->count()
//            //$crawler->filter('html:contains("Current Version")')->count()
//        );
//    }

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

        //$httpChanel = true;
        //$httpChanel = false;
//        $this->client = static::createClient([], [
//            'HTTP_HOST'       => '127.0.0.1',
//            'HTTP_USER_AGENT' => 'MySuperBrowser/1.0',
//            //'HTTPS' => $httpChanel
//        ]);
        //$this->client->followRedirects();
        $this->getClient();

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
        $session->save();

        $cookie = new Cookie($session->getName(), $session->getId());
        $this->client->getCookieJar()->set($cookie);
    }

    public function getClient() {
        //$httpChanel = true;
        //$httpChanel = false;
        $this->client = static::createClient([], [
            'HTTP_HOST' => '127.0.0.1',
            'HTTP_USER_AGENT' => 'MySuperBrowser/1.0',
            //'HTTPS' => $httpChanel
        ]);

        //When running on https this will follow redirect from http://127.0.0.1 to https://127.0.0.1
        $this->client->followRedirects();
    }

//    public function logIn_old()
//    {
//        $session = $this->client->getContainer()->get('session');
//
//        $firewallName = 'ldap_fellapp_firewall';
//        // if you don't define multiple connected firewalls, the context defaults to the firewall name
//        // See https://symfony.com/doc/current/reference/configuration/security.html#firewall-context
//        $firewallContext = 'scan_auth';
//
//        // you may need to use a different token class depending on your application.
//        // for example, when using Guard authentication you must instantiate PostAuthenticationGuardToken
//        $token = new UsernamePasswordToken('administrator', null, $firewallName, ['ROLE_PLATFORM_ADMIN']);
//        $session->set('_security_'.$firewallContext, serialize($token));
//        $session->save();
//
//        $cookie = new Cookie($session->getName(), $session->getId());
//        $this->client->getCookieJar()->set($cookie);
//    }

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