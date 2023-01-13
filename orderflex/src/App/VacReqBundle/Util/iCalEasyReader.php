<?php
/**
 * Created by PhpStorm.
 * User: ch3
 * Date: 1/13/2023
 * Time: 1:24 PM
 */

namespace App\VacReqBundle\Util;


//https://www.phpclasses.org/browse/file/63450.html
/**
 * iCalEasyReader is an easy to understood class, loads a "ics" format string and returns an array with the traditional iCal fields
 *
 * @category Parser
 * @author Matias Perrone <matias.perrone at gmail dot com>
 * @author Timo Henke <phpstuff@thenke.de> (Some ideas taken partially from iCalParse on http://www.phpclasses.org/)
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 * @version 1.4.1
 * @param string $data ics file string content
 * @param array|false $data $makeItEasy the idea is to convert this "keys" into the "values", converting the DATE and DATE-TIME values to the respective DateTime type of PHP, also all the keys are lowercased
 * @return array|false
 */
class iCalEasyReader
{
    private $ical = null;
    private $_lastitem = null;

    public function &load($data)
    {
        $this->ical = false;
        $regex_opt = 'mib';

        // Lines in the string
        $lines = mb_split( '[\r\n]+', $data );

        // Delete empty ones
        $last = count( $lines );
        for($i = 0; $i < $last; $i ++)
        {
            if (trim( $lines[$i] ) == '')
                unset( $lines[$i] );
        }
        $lines = array_values( $lines );

        // First and last items
        $first = 0;
        $last = count( $lines ) - 1;

        if (! ( mb_ereg_match( '^BEGIN:VCALENDAR', $lines[$first], $regex_opt ) and mb_ereg_match( '^END:VCALENDAR', $lines[$last], $regex_opt ) ))
        {
            $first = null;
            $last = null;
            foreach ( $lines as $i => &$line )
            {
                if (mb_ereg_match( '^BEGIN:VCALENDAR', $line, $regex_opt ))
                    $first = $i;

                if (mb_ereg_match( '^END:VCALENDAR', $line, $regex_opt ))
                {
                    $last = $i;
                    break;
                }
            }
        }

        // Procesing
        if (! is_null( $first ) and ! is_null( $last ))
        {
            $lines = array_slice( $lines, $first + 1, ( $last - $first - 1 ), true );

            $group = null;
            $parentgroup = null;
            $this->ical = [];
            $addTo = [];
            $addToElement = null;
            foreach ( $lines as $line )
            {
                $clave = null;
                $pattern = '^(BEGIN|END)\:(.+)$'; // (VALARM|VTODO|VJOURNAL|VEVENT|VFREEBUSY|VCALENDAR|DAYLIGHT|VTIMEZONE|STANDARD)
                mb_ereg_search_init( $line );
                $regs = mb_ereg_search_regs( $pattern, $regex_opt );
                if ($regs)
                {
                    // $regs
                    // 0 => BEGIN:VEVENT
                    // 1 => BEGIN
                    // 2 => VEVENT
                    switch ( $regs[1] )
                    {
                        case 'BEGIN' :
                            if (! is_null( $group ))
                                $parentgroup = $group;

                            $group = trim( $regs[2] );

                            // Adding new values to groups
                            if (is_null( $parentgroup ))
                            {
                                if (! array_key_exists( $group, $this->ical ))
                                    $this->ical[$group] = [null];
                                else
                                    $this->ical[$group][] = null;
                            }
                            else
                            {
                                if (! array_key_exists( $parentgroup, $this->ical ))
                                    $this->ical[$parentgroup] = [$group => [null]];

                                if (! array_key_exists( $group, $this->ical[$parentgroup] ))
                                    $this->ical[$parentgroup][$group] = [null];
                                else
                                    $this->ical[$parentgroup][$group][] = null;
                            }

                            break;
                        case 'END' :
                            if (is_null( $group ))
                                $parentgroup = null;

                            $group = null;
                            break;
                    }
                    continue;
                }

                if (! in_array( $line[0], [" ", "\t"] ))
                    $this->addItem( $line, $group, $parentgroup );
                else
                    $this->concatItem( $line );
            }
        }

        return $this->ical;
    }

    public function addType(&$value, $item)
    {
        $type = explode( '=', $item );

        if (count( $type ) > 1 and $type[0] == 'VALUE')
            $value['type'] = $type[1];
        else
            $value[$type[0]] = $type[1];

        return $value;
    }

    public function addItem($line, $group, $parentgroup)
    {
        $line = $this->transformLine( $line );
        $item = explode( ':', $line, 2 );
        // If $group is null is an independent value
        if (is_null( $group ))
        {
            $this->ical[$item[0]] = ( count( $item ) > 1 ? $item[1] : null );
            $this->_lastitem = &$this->ical[$item[0]];
        }
        // If $group is set then is an item of a group
        else
        {
            $subitem = explode( ';', $item[0], 2 );
            if (count( $subitem ) == 1)
                $value = ( count( $item ) > 1 ? $item[1] : null );
            else
            {
                $value = ['value' => $item[1]];
                $this->addType( $value, $subitem[1] );
            }

            // Multi value
            if (is_string( $value ))
            {
                $z = explode( ';', $value );
                if (count( $z ) > 1)
                {
                    $value = [];
                    foreach ( $z as &$v )
                    {
                        $t = explode( '=', $v );
                        $value[$t[0]] = $t[count( $t ) - 1];
                    }
                }
                unset( $z );
            }

            if (is_null( $parentgroup ))
            {
                $this->ical[$group][count( $this->ical[$group] ) - 1][$subitem[0]] = $value;
                $this->_lastitem = &$this->ical[$group][count( $this->ical[$group] ) - 1][$subitem[0]];
            }
            else
            {
                $this->ical[$parentgroup][$group][count( $this->ical[$parentgroup][$group] ) - 1][$subitem[0]] = $value;
                $this->_lastitem = &$this->ical[$parentgroup][$group][count( $this->ical[$parentgroup][$group] ) - 1][$subitem[0]];
            }
        }
    }

    public function concatItem($line)
    {
        $line = mb_substr( $line, 1 );
        if (is_array( $this->_lastitem ))
        {
            $line = $this->transformLine( $this->_lastitem['value'] . $line );
            $this->_lastitem['value'] = $line;
        }
        else
        {
            $line = $this->transformLine( $this->_lastitem . $line );
            $this->_lastitem = $line;
        }
    }

    public function transformLine($line)
    {
        $patterns = ['\\\\[n]', '\\\\[t]', '\\\\,', '\\\\;'];
        $replacements = ["\n", "\t", ",", ";"];

        return $this->mb_eregi_replace_all( $patterns, $replacements, $line );
    }

    public function mb_eregi_replace_all($pattern, $replacement, $string)
    {
        if (is_array( $pattern ) and is_array( $replacement ))
        {
            foreach ( $pattern as $i => $patron )
            {
                if (array_key_exists( $i, $replacement ))
                    $reemplazo = $replacement[$i];
                else
                    $reemplazo = '';

                $string = mb_eregi_replace( $patron, $reemplazo, $string );
            }
        }
        elseif (is_string( $pattern ) and is_string( $replacement ))
            $string = mb_eregi_replace( $pattern, $replacement, $string );

        return $string;
    }
}