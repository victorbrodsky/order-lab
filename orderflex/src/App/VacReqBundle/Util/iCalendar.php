<?php
/**
 * Created by PhpStorm.
 * User: ch3
 * Date: 1/13/2023
 * Time: 1:46 PM
 */

namespace App\VacReqBundle\Util;


class iCalendar
{
    var $file_text;
    var $cal;
    var $event_count;
    var $todo_count;
    var $last_key;

    function read_file($file) {
        $this->file = $file;
        $file_text = join("", file($file)); //load file
        $file_text = preg_replace("/[\r\n]{1,} ([:;])/", "\\1", $file_text);
        return $file_text; // return all text
    }

    function get_event_count() {
        return $this->event_count;
    }

    function get_todo_count() {
        return $this->todo_count;
    }

    function parse($uri) {
        $this->cal = array(); // new empty array

        $this->event_count = -1;
        $this->file_text = $this->read_file($uri);

        $this->file_text = preg_split("[\n]", $this->file_text);
        if (!stristr($this->file_text[0], 'BEGIN:VCALENDAR'))
            return 'error not VCALENDAR';

        foreach ($this->file_text as $text) {

            $text = trim($text);
            if (!empty($text)) {
                list($key, $value) = $this->retun_key_value($text);

                switch ($text) {
                    case "BEGIN:VTODO":
                        $this->todo_count = $this->todo_count + 1;
                        $type = "VTODO";
                        break;

                    case "BEGIN:VEVENT":
                        $this->event_count = $this->event_count + 1;
                        $type = "VEVENT";
                        break;

                    case "BEGIN:VCALENDAR":
                    case "BEGIN:DAYLIGHT":
                    case "BEGIN:VTIMEZONE":
                    case "BEGIN:STANDARD":
                        $type = $value;
                        break;

                    case "END:VTODO":
                    case "END:VEVENT":

                    case "END:VCALENDAR":
                    case "END:DAYLIGHT":
                    case "END:VTIMEZONE":
                    case "END:STANDARD":
                        $type = "VCALENDAR";
                        break;

                    default:
                        $this->add_to_array($type, $key, $value);
                        break;
                }
            }
        }
        return $this->cal;
    }


    function add_to_array($type, $key, $value) {
        if ($key == false) {
            $key = $this->last_key;
            switch ($type) {
                case 'VEVENT': $value = $this->cal[$type][$this->event_count][$key] . $value;
                    break;
                case 'VTODO': $value = $this->cal[$type][$this->todo_count][$key] . $value;
                    break;
            }
        }

        if (($key == "DTSTAMP") or ($key == "LAST-MODIFIED") or ($key == "CREATED"))
            $value = $this->ical_date_to_unix($value);
        if ($key == "RRULE")
            $value = $this->ical_rrule($value);
        if (stristr($key, "DTSTART") or stristr($key, "DTEND")){
            $my_arr=explode("T",$value);
            $cdate=$my_arr[0]." ".$my_arr[1];
            //$cdate = $value;
            list($key, $cdate) = $this->ical_dt_date($key, $cdate);
        }

        switch ($type) {
            case "VTODO":
                $this->cal[$type][$this->todo_count][$key] = $value;
                break;

            case "VEVENT":
                $this->cal[$type][$this->event_count][$key] = $value;
                break;

            default:
                $this->cal[$type][$key] = $value;
                break;
        }
        $this->last_key = $key;
    }

    function retun_key_value($text) {
        preg_match("/([^:]+)[:]([\w\W]+)/", $text, $matches);

        if (empty($matches)) {
            return array(false, $text);
        } else {
            $matches = array_splice($matches, 1, 2);
            return $matches;
        }
    }

    function ical_rrule($value) {
        $rrule = explode(';', $value);
        foreach ($rrule as $line) {
            $rcontent = explode('=', $line);
            $result[$rcontent[0]] = $rcontent[1];
        }
        return $result;
    }

    function ical_date_to_unix($ical_date) {
        $ical_date = str_replace('T', '', $ical_date);
        $ical_date = str_replace('Z', '', $ical_date);

        preg_match('([0-9]{4})([0-9]{2})([0-9]{2})([0-9]{0,2})([0-9]{0,2})([0-9]{0,2})', $ical_date, $date);

        if ($date[1] <= 1970) {
            $date[1] = 1971;
        }
        return mktime($date[4], $date[5], $date[6], $date[2], $date[3], $date[1]);
    }

    function ical_dt_date($key, $value) {
        $value = $this->ical_date_to_unix($value);

        $temp = explode(";", $key);

        if (empty($temp[1])) { // neni TZID
            $data = str_replace('T', '', $data);
            return array($key, $value);
        }

        $key = $temp[0];
        $temp = explode("=", $temp[1]);
        $return_value[$temp[0]] = $temp[1];
        $return_value['unixtime'] = $value;

        return array($key, $return_value);
    }

    function get_sort_event_list() {
        $temp = $this->get_event_list();
        if (!empty($temp)) {
            usort($temp, array(&$this, "ical_dtstart_compare"));
            return $temp;
        } else {
            return false;
        }
    }

    function ical_dtstart_compare($a, $b) {
        return strnatcasecmp($a['DTSTART']['unixtime'], $b['DTSTART']['unixtime']);
    }

    function get_event_list() {
        return $this->cal['VEVENT'];
    }

    function get_todo_list() {
        return $this->cal['VTODO'];
    }

    function get_calender_data() {
        return $this->cal['VCALENDAR'];
    }

    function get_all_data() {
        return $this->cal;
    }
}