<?php

/**
 * Created by PhpStorm.
 * User: ch3
 * Date: 12/10/2019
 * Time: 2:50 PM
 */

//./bin/simple-phpunit tests/Oleg/TranslationalResearchBundle/Util/TransResUtilTest.php

use PHPUnit\Framework\TestCase;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class TransResUtilTest extends KernelTestCase
{

    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $entityManager;
    private $container;

    protected function setUp()
    {
        $kernel = self::bootKernel();

        $this->container = $kernel->getContainer();

        $this->entityManager = $this->container
            ->get('doctrine')
            ->getManager();
    }

    public function testGeneratePackingSlipPdf() {

        //$transresRequestUtil = $this->container->get('transres_request_util');
        $transresPdfUtil = $this->container->get('transres_pdf_generator');

        $requestsId = 1;
        $transresRequest = $this->entityManager->getRepository('OlegTranslationalResearchBundle:TransResRequest')->find($requestsId);


        echo "transresRequest ID=".$transresRequest->getId().", OID=".$transresRequest->getOid()."\n";

        $oidTest = false;
        if (strpos($transresRequest->getOid(), '-REQ') !== false) {
            $oidTest = true;
        }
        $this->assertTrue($oidTest);


        //Generate Packing Slip
        $authorUser = null;
        $request = null;
        $res = $transresPdfUtil->generatePackingSlipPdf($transresRequest,$authorUser,$request);

        $filename = $res['filename'];
        //$pdf = $res['pdf'];
        $size = $res['size'];

        // assert that size is greater than zero
        $this->assertGreaterThan(100, $size);
    }

    public function testGetAvailableProjects() {
        //$transresRequestUtil = $this->container->get('transres_request_util');
        $transresUtil = $this->container->get('transres_util');
        $projects = $transresUtil->getAvailableRequesterOrReviewerProjects();

        $this->assertGreaterThan(1000, count($projects));

        $requests = $transresUtil->getTotalRequestCount();
        $this->assertGreaterThan(1000, count($requests));

    }

    public function testAdd()
    {
        $result = 30+12;

        // assert that your calculator added the numbers correctly!
        $this->assertEquals(42, $result);
    }


//    public function testComp()
//    {
//        $this->assertTrue(false);
//    }



    /**
     * {@inheritDoc}
     */
    protected function tearDown()
    {
        parent::tearDown();

        $this->entityManager->close();
        $this->entityManager = null; // avoid memory leaks

        $this->container = null; // avoid memory leaks
    }

}