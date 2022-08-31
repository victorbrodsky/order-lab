<?php
/**
 * Created by PhpStorm.
 * User: ch3
 * Date: 1/11/2020
 * Time: 8:52 AM
 */

namespace Tests\App\TestBundle;


use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class KernelTestBase extends KernelTestCase
{

    public $testContainer;
    public $entityManager;


//    public function getInit( EntityManagerInterface $em, ContainerInterface $container ) {
//        $this->entityManager = $em;
//        $this->testContainer = $container;
//    }

    protected function setUp()
    {
        //$this->getInit();

        $kernel = self::bootKernel();

        //$em = self::$kernel->getContainer()->get(ObjectManager::class);

//        $em = self::$kernel->getContainer()
//            ->get('doctrine')
//            ->getManager();

        $container = self::$container;
        $test = $container->get('user_service_utility');
        $realContainer = $container->get('test.service_container');
        $this->entityManager = $realContainer->get('doctrine.orm.entity_manager');
        //$this->entityManager = $realContainer->get('doctrine')->getManager();

//        $this->entityManager = $kernel->getContainer()
//            ->get('doctrine')
//            ->getManager();

        //$this->mycontainer = $kernel->getContainer();
    }

//    public function testSearchByName()
//    {
//        $product = $this->entityManager
//            ->getRepository(Product::class)
//            ->findOneBy(['name' => 'Priceless widget'])
//        ;
//
//        $this->assertSame(14.50, $product->getPrice());
//    }

    protected function tearDown()
    {
        parent::tearDown();

        // doing this is recommended to avoid memory leaks
        $this->entityManager->close();
        $this->entityManager = null;
    }

}