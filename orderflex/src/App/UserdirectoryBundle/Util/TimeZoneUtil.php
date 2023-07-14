<?php
/**
 * Copyright (c) 2017 Cornell University
 *
 *  Licensed under the Apache License, Version 2.0 (the "License");
 *  you may not use this file except in compliance with the License.
 *  You may obtain a copy of the License at
 *
 *  http://www.apache.org/licenses/LICENSE-2.0
 *
 *  Unless required by applicable law or agreed to in writing, software
 *  distributed under the License is distributed on an "AS IS" BASIS,
 *  WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 *  See the License for the specific language governing permissions and
 *  limitations under the License.
 */

/**
 * Created by JetBrains PhpStorm.
 * User: oli2002
 * Date: 3/19/14
 * Time: 3:02 PM
 * To change this template use File | Settings | File Templates.
 */

namespace App\UserdirectoryBundle\Util;


//Note: Timezone for twig is set in App\UserdirectoryBundle\Services/TwigDateRequestListener

class TimeZoneUtil {

    /**
     * Modified Timezones list with GMT offset: http://www.pontikis.net/tip/?id=24
     * @return array
     * @link http://stackoverflow.com/a/9328760
     */
    public function tz_list( $asValueLabel=false ) {
        $zones_array = array();

        //$dateTimeZoneUTC = new \DateTimeZone("UTC");
        //$dateTimeUTC = new \DateTime("now", $dateTimeZoneUTC);

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

            if( intval($timeOffset) > 0 ) {
                $timeOffset = "+".$timeOffset;
            }

            //$timeOffset = gmdate("H:i", $timeOffset);

            //$this_tz_str = date_default_timezone_get();
            //$this_tz = new \DateTimeZone($zone);
            //$now = new \DateTime("now", $this_tz);
            //$timeOffset = $this_tz->getOffset($now) / 3600;

//            if( $zone == "America/New_York" ) {
//                echo $zone . ": timeOffset=" . $timeOffset . "<br>";
//            }

            if( $asValueLabel ) {
                $zones_array[$zone] = '(UTC/GMT ' . $timeOffset . ') ' . $zone;
            } else {
                $zones_array['(UTC/GMT ' . $timeOffset . ') ' . $zone] = $zone;
            }
        }
        return $zones_array;
    }

    //Replace twig's |ago filter from KnpTimeBundle bundle
    // https://stackoverflow.com/questions/44347922/return-the-difference-between-two-datetime-objects-using-knptimebundle
    //Removed from composer.json: "knplabs/knp-time-bundle": "^1.17",
    //Use in twig as {{ time_zone_util.ago(lastImportTimestamp) }}
    public function pluralize( $count, $text )
    {
        //return $count . ( ( $count == 1 ) ? ( " $text" ) : ( " ${text}s" ) );
        //Deprecated: Using ${var} in strings is deprecated, use {$var} instead in /usr/local/bin/order-lab/orderflex/src/App/UserdirectoryBundle/Util/TimeZoneUtil.php on line 90
        return $count . ( ( $count == 1 ) ? ( " $text" ) : ( " {$text}s" ) );
    }
    function ago( $datetime )
    {
        if( !$datetime ) {
            return "N/A"; //"Invalid import datetime";
        }

        $interval = date_create('now')->diff( $datetime );
        $suffix = ( $interval->invert ? ' ago' : '' );
        if ( $v = $interval->y >= 1 ) return $this->pluralize( $interval->y, 'year' ) . $suffix;
        if ( $v = $interval->m >= 1 ) return $this->pluralize( $interval->m, 'month' ) . $suffix;
        if ( $v = $interval->d >= 1 ) return $this->pluralize( $interval->d, 'day' ) . $suffix;
        if ( $v = $interval->h >= 1 ) return $this->pluralize( $interval->h, 'hour' ) . $suffix;
        if ( $v = $interval->i >= 1 ) return $this->pluralize( $interval->i, 'minute' ) . $suffix;
        return $this->pluralize( $interval->s, 'second' ) . $suffix;
    }

}