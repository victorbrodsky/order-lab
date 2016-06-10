<?php

namespace Oleg\CallLogBundle\Util;

/**
 * Created by PhpStorm.
 * User: ch3
 * Date: 6/10/2016
 * Time: 3:04 PM
 */
class CallLogUtil
{

    protected $em;
    protected $sc;
    protected $container;

    public function __construct( $em, $sc, $container ) {
        $this->em = $em;
        $this->sc = $sc;
        $this->container = $container;
    }


}