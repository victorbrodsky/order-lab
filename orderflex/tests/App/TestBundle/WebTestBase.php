<?php

/**
 * Created by PhpStorm.
 * User: App Ivanov
 * Date: 12/12/2019
 * Time: 8:23 AM
 */

namespace Tests\App\TestBundle;
//namespace App\CallLogBundle\Controller;

//use PHPUnit\Framework\TestCase;
//use App\UserdirectoryBundle\Util\UserSecurityUtil;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockFileSessionStorage;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

//use Doctrine\ORM\EntityManagerInterface;
//use Symfony\Component\DependencyInjection\ContainerInterface;
//use Symfony\Component\Security\Core\Security;
//use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
//use Symfony\Component\HttpFoundation\RequestStack;

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

//fresh install
//"autoload-dev": {
//    "psr-4": {
//        "App\\Tests\\": "tests/"
//        }
//    },

//Test specific ftest file: php bin/phpunit tests/App/TestBundle/TrpTest.php

//To specify http channel run it as: HTTP=1 ./vendor/bin/phpunit -d memory_limit=-1 --stop-on-failure (dev)
//To specify https channel (default) run it as: ./vendor/bin/phpunit (test,live)

class WebTestBase extends WebTestCase
{

    /**
     * @var \Doctrine\ORM\EntityManager
     */
    protected $em;
    protected $testContainer;
    protected $client = null;
    protected $user = null;
    protected $environment = null;

    public function getParam() {
//        global $argv, $argc;
//        $this->assertGreaterThan(2, $argc, 'No environment name passed');
//        if (strpos($argv[1], '---') !== false) {
//            $this->environment = $argv[1];
//        }
        $this->environment = getenv('TESTENV');
        //To test without data run: TESTENV=nodata ./bin/simple-phpunit

//        if( $this->environment == "nodata" ) {
//            echo "Run without data consistency check";
//        }
    }

    protected function setUp(): void {

        //$kernel = self::bootKernel();

        //$this->testContainer = self::$container;
        $this->testContainer = $this->client->getContainer();
        //$this->testContainer = $kernel->getContainer();

        $this->getTestClient();

        //$this->em = self::$container->get('doctrine.orm.entity_manager');
        //$this->em = $this->getService('doctrine.orm.entity_manager');
        //$this->em = $this->getService('user_security_utility');
        $this->em = $this->testContainer->get('doctrine.orm.entity_manager');

        $this->user = $this->getUser();

        $this->getParam();
        //exit("environment=".$this->environment);

        parent::setUp();
    }

    /**
     * {@inheritDoc}
     */
    protected function tearDown(): void
    {
        parent::tearDown();

        if($this->em) {
            $this->em->close();
            $this->em = null; // avoid memory leaks
        }

        $this->testContainer = null; // avoid memory leaks

        $this->client = null;

        $this->user = null;
    }

//    public function logIn_OLD() {
//
//        $session = $this->client->getContainer()->get('session');
//        //$session = $this->requestStack->getSession();
//        //$session = $this->client->getContainer()->get('request_stack')->getSession();
//        //$session = $this->client->getContainer()->get(RequestStack::class)->getSession();
//        //$session = new Session(new MockFileSessionStorage());
//        //$session = new Session(new MockFileSessionStorage());
//        //$session = new Session();
//        //$session->start();
//
//        $session = $this->client->getContainer()->get('session.factory')->createSession();
//
//        //$firewallName = 'external_ldap_firewall';
//        // if you don't define multiple connected firewalls, the context defaults to the firewall name
//        // See https://symfony.com/doc/current/reference/configuration/security.html#firewall-context
//        $firewallContext = 'scan_auth';
//        $firewallName = 'ldap_fellapp_firewall';
//
//        $systemUser = $this->getUser();
//        //$systemUser = $this->em->getRepository('AppUserdirectoryBundle:User')->findOneByUsername('administrator');
//
//        $token = new UsernamePasswordToken($systemUser, $firewallName, $systemUser->getRoles());
//
//        //$this->testContainer->get('security.token_storage')->setToken($token);
//        $this->client->getContainer()->get('security.token_storage')->setToken($token);
//        //$this->tokenStorage->setToken($token);
//
//        $session->set('_security_'.$firewallContext, serialize($token));
//        $session->save();
//
//        $cookie = new Cookie($session->getName(), $session->getId());
//        $this->client->getCookieJar()->set($cookie);
//    }

    public function logIn() {
        $systemUser = $this->getUser();

        $firewallContext = 'scan_auth';

        // simulate $testUser being logged in
        $this->client->loginUser($systemUser,$firewallContext);
    }

    public function getTestClient(array $options = array(), array $server = array()) {
        //TODO: detect if HTTP or HTTPS used by url

        //To specify http channel run it as: HTTP=1 ./bin/phpunit
        //To specify https channel (default) run it as: ./bin/phpunit
        $channel = getenv('HTTP');
        //echo "channel=[".$httpsChannel."]<br>";
        if( $channel ) {
            //echo "HTTP";
            $httpsChannel = false;
        } else {
            //echo "HTTPS";
            $httpsChannel = true;
        }

//        $client = static::createClient();
//        $userUtil = $client->getContainer()->get('user_utility');
//        $scheme = $userUtil->getScheme();
//        //exit("scheme=$scheme");
//        if( $scheme ) {
//            if( strtolower($scheme) == 'http' ) {
//                //echo "HTTP";
//                $httpsChannel = false;
//            } else {
//                //echo "HTTPS";
//                $httpsChannel = true;
//            }
//        }

        $client = static::createClient([], [
            'HTTP_HOST' => '127.0.0.1',
            'HTTP_USER_AGENT' => 'MySuperBrowser/1.0',
            'HTTPS' => $httpsChannel
        ]);

        $this->client = $client;

        //Alternative of setting HTTPS: When running on https this will follow redirect from http://127.0.0.1 to https://127.0.0.1
        //$this->client->followRedirects();
    }

    public function getUser()
    {
        $userSecUtil = $this->testContainer->get('user_security_utility');
        $systemUser = $userSecUtil->findSystemUser();

        if( !$systemUser ) {
            $systemUser = $this->em->getRepository('AppUserdirectoryBundle:User')->findOneByPrimaryPublicUserId('Administrator');
        }

        if( !$systemUser ) {
            $systemUser = $this->em->getRepository('AppUserdirectoryBundle:User')->findOneByPrimaryPublicUserId('administrator');
        }

        return $systemUser;
    }



}