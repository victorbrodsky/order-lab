<?php
/**
 * Created by PhpStorm.
 * User: ch3
 * Date: 6/1/2022
 * Time: 1:20 PM
 */

namespace Tests\App\TestBundle;


use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class SimpleKernelTest extends KernelTestCase
{

    public function testUtil()
    {
        // (1) boot the Symfony kernel
        self::bootKernel();

        // (2) use static::getContainer() to access the service container
        $container = static::getContainer();

        // (3) run some service & test the result
        //$newsletterGenerator = $container->get(PdfUtil::class);
        $vacreqUtil = $container->get('vacreq_util');

        $floatingDay = "06/29/2019";
        $expectedRes = "2018-2019";

        $floatingDayDate = \DateTime::createfromformat('m/d/Y',$floatingDay);

        $yearRangeStr = $vacreqUtil->getAcademicYearBySingleDate($floatingDayDate);

        $this->assertTrue($yearRangeStr == $expectedRes);
    }

}