<?php

/**
 * Created by PhpStorm.
 * User: ch3
 * Date: 12/10/2019
 * Time: 2:50 PM
 */

use PHPUnit\Framework\TestCase;

class TransResUtilTest extends TestCase
{

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
}