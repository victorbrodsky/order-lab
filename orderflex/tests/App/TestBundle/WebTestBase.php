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


//    public function getInit() : void
//    {
//        $this->getTestClient();
//
//        $this->testContainer = self::$container;
//        //$this->container = $this->client->getContainer();
//
//        $this->em = self::$container->get('doctrine.orm.entity_manager');
//        //$this->em = $this->getService('doctrine.orm.entity_manager');
//        //$this->em = $this->getService('user_security_utility');
//
//        $this->user = $this->getUser();
//
//        $this->getParam();
//    }

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

        $this->getTestClient();

        $this->testContainer = self::$container;
        //$this->container = $this->client->getContainer();

        $this->em = self::$container->get('doctrine.orm.entity_manager');
        //$this->em = $this->getService('doctrine.orm.entity_manager');
        //$this->em = $this->getService('user_security_utility');

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

    public function logIn() {

        $session = $this->client->getContainer()->get('session');

        //$firewallName = 'external_ldap_firewall';
        // if you don't define multiple connected firewalls, the context defaults to the firewall name
        // See https://symfony.com/doc/current/reference/configuration/security.html#firewall-context
        $firewallContext = 'scan_auth';
        $firewallName = 'ldap_fellapp_firewall';

        $systemUser = $this->getUser();
        //$systemUser = $this->em->getRepository('AppUserdirectoryBundle:User')->findOneByUsername('administrator');
        
        $token = new UsernamePasswordToken($systemUser, $firewallName, $systemUser->getRoles());
        $this->testContainer->get('security.token_storage')->setToken($token);

        $session->set('_security_'.$firewallContext, serialize($token));
        $session->save();

        $cookie = new Cookie($session->getName(), $session->getId());
        $this->client->getCookieJar()->set($cookie);
    }

    public function getTestClient(array $options = array(), array $server = array()) {

        //$client = static::createClient();
        //$this->client = $client;
        //return $client;

        //Set HTTPS if required
//        $client = static::createClient([], [
//            'HTTP_HOST'       => '127.0.0.1',
//            'HTTP_USER_AGENT' => 'MySuperBrowser/1.0',
//        ]);

        //$kernel = static::bootKernel($options);
        //$client = $kernel->getContainer()->get('test.client');

        //$userSecUtil = $this->testContainer->get('user_security_utility');
        //$userSecUtil = $this->getService('user_security_utility');

//        $connectionChannel = NULL;
//        //$connectionChannel = $userSecUtil->getSiteSettingParameter('connectionChannel');
        //$httpsChannel = false;
        //$httpsChannel = true;
//        if( $connectionChannel == 'https' ) {
//            $httpsChannel = true;
//        }

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

        $client = static::createClient([], [
            'HTTP_HOST' => '127.0.0.1',
            'HTTP_USER_AGENT' => 'MySuperBrowser/1.0',
            'HTTPS' => $httpsChannel
        ]);

        $this->client = $client;

        //Alternative of setting HTTPS: When running on https this will follow redirect from http://127.0.0.1 to https://127.0.0.1
        //$this->client->followRedirects();
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