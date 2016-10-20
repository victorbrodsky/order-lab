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

        $dateTimeZoneUTC = new \DateTimeZone("UTC");
        $dateTimeUTC = new \DateTime("now", $dateTimeZoneUTC);

        foreach(timezone_identifiers_list() as $key => $zone) {
            //date_default_timezone_set($zone);
            //$zones_array[$key]['zone'] = $zone;
            //$zones_array[$key]['diff_from_GMT'] = 'UTC/GMT ' . date('P', $timestamp);

            //$timestamp = time();
            //$timestamp = $dateTime->getTimestamp();
            //$timeOffset = $dateTime->date('P');
            //$timeOffset = date('P', $timestamp);

            $dateTimeZone = new \DateTimeZone($zone);
            $dateTime = new \DateTime("now", $dateTimeZone);
            $timeOffset = $dateTimeZone->getOffset($dateTime);

            $timeOffset = $timeOffset / 3600;
            //$timeOffset = gmdate("H:i", $timeOffset);

            //$this_tz_str = date_default_timezone_get();
            //$this_tz = new \DateTimeZone($zone);
            //$now = new \DateTime("now", $this_tz);
            //$timeOffset = $this_tz->getOffset($now) / 3600;

//            if( $zone == "America/New_York" ) {
//                echo $zone . ": timeOffset=" . $timeOffset . "<br>";
//            }

            $zones_array[$zone] = '(UTC/GMT ' . $timeOffset . ') '. $zone;
        }
        return $zones_array;
    }

}