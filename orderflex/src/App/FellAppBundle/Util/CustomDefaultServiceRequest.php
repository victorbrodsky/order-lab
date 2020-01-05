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
 * Created by PhpStorm.
 * User: ch3
 * Date: 3/22/2016
 * Time: 3:05 PM
 */

namespace App\FellAppBundle\Util;


use Google\Spreadsheet\DefaultServiceRequest;

//This class is needed just to set CURLOPT_SSL_VERIFYPEER to false in Google\Spreadsheet\DefaultServiceRequest

class CustomDefaultServiceRequest extends DefaultServiceRequest
{

    /**
     * Initializes the service request object.
     *
     * @param string $accessToken
     * @param string $tokenType
     */
    public function __construct(string $accessToken, string $tokenType = "OAuth")
    {
        parent::__construct($accessToken,$tokenType);
    }
//
//    public function setAccessRequest(string $accessToken)
//    {
//        $this->accessToken = $accessToken;
//    }

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