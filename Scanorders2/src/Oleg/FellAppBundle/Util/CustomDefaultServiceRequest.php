<?php
/**
 * Created by PhpStorm.
 * User: ch3
 * Date: 3/22/2016
 * Time: 3:05 PM
 */

namespace Oleg\FellAppBundle\Util;


use Google\Spreadsheet\DefaultServiceRequest;

//This class is needed just to set CURLOPT_SSL_VERIFYPEER to false in Google\Spreadsheet\DefaultServiceRequest

class CustomDefaultServiceRequest extends DefaultServiceRequest
{

    /**
     * Overwrite: Initialize the curl session
     *
     * @param string $url
     * @param array  $requestHeaders
     *
     * @return resource
     */
    protected function initRequest($url, $requestHeaders = array())
    {
        $curlParams = array (
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_FAILONERROR => false,
            CURLOPT_SSL_VERIFYPEER => false,    //true,
            CURLOPT_VERBOSE => false,
        );

        if(substr($url, 0, 4) !== 'http') {
            $url = $this->serviceUrl . $url;
        }

        $ch = curl_init();
        curl_setopt_array($ch, $curlParams);
        curl_setopt($ch, CURLOPT_URL, $url);

        $headers = array();
        if (count($this->getHeaders()) > 0) {
            foreach ($this->getHeaders() as $k => $v) {
                $headers[] = "$k: $v";
            }
        }
        $headers[] = "Authorization: " . $this->tokenType . " " . $this->accessToken;
        $headers = array_merge($headers, $requestHeaders);

        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_USERAGENT, $this->getUserAgent());
        return $ch;
    }

}