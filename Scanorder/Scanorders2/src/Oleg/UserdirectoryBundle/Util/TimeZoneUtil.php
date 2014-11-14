<?php
/**
 * Created by JetBrains PhpStorm.
 * User: oli2002
 * Date: 3/19/14
 * Time: 3:02 PM
 * To change this template use File | Settings | File Templates.
 */

namespace Oleg\UserdirectoryBundle\Util;


//Note: Timezone for twig is set in Oleg\UserdirectoryBundle\Services/TwigDateRequestListener

class TimeZoneUtil {

    /**
     * Modified Timezones list with GMT offset: http://www.pontikis.net/tip/?id=24
     * @return array
     * @link http://stackoverflow.com/a/9328760
     */
    public function tz_list() {
        $zones_array = array();
        $timestamp = time();
        foreach(timezone_identifiers_list() as $key => $zone) {
            date_default_timezone_set($zone);
            //$zones_array[$key]['zone'] = $zone;
            //$zones_array[$key]['diff_from_GMT'] = 'UTC/GMT ' . date('P', $timestamp);
            $zones_array[$zone] = '(UTC/GMT ' . date('P', $timestamp) . ') '. $zone;
        }
        return $zones_array;
    }

}