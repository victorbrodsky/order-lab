<?php
/**
 * This is a PHP library that handles calling reCAPTCHA.
 *    - Documentation and latest version
 *          https://developers.google.com/recaptcha/docs/php
 *    - Get a reCAPTCHA API Key
 *          https://www.google.com/recaptcha/admin/create
 *    - Discussion group
 *          http://groups.google.com/group/recaptcha
 *
 * @copyright Copyright (c) 2014, Google Inc.
 * @link      http://www.google.com/recaptcha
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */
/**
 * A ReCaptchaResponse is returned from checkAnswer().
 */


//How to use
//Create site key and secret key:
//1) https://www.google.com/recaptcha/admin/site/730004528/setup
//2) Enter label: for example view.online
//3) Choose V2
//4) Add domain: for example view.online
//5) Click Submit
//6) In the site settings: enter generated site key and secret key from
//Example use in web: C:\Users\cinav\Documents\WCMC\ORDER\order-lab\orderflex\templates\AppUserdirectoryBundle\SignUp\new.html.twig

namespace App\UserdirectoryBundle\Captcha;



use Symfony\Component\HttpClient\HttpClient;

class ReCaptcha
{
    private static string $_signupUrl = "https://www.google.com/recaptcha/admin";
    private static string $_siteVerifyUrl = "https://www.google.com/recaptcha/api/siteverify?";
    private string $_secret;
    private static string $_version = "php_1.0";

    public function __construct( string $secret )
    {
//        if ($secret == null || $secret == "") {
//            die("To use reCAPTCHA you must get an API key from <a href='"
//                . self::$_signupUrl . "'>" . self::$_signupUrl . "</a>");
//        }
        if (empty($secret)) {
            throw new \InvalidArgumentException(
                "Missing reCAPTCHA API key. You can get one at: " . self::$_signupUrl
            );
        }
        $this->_secret = $secret;

        $this->_secret=$secret;
    }


    /**
     * Constructor.
     *
     * @param string $secret shared secret between site and ReCAPTCHA server.
     */
    function ReCaptcha($secret)
    {
        if ($secret == null || $secret == "") {
            die("To use reCAPTCHA you must get an API key from <a href='"
                . self::$_signupUrl . "'>" . self::$_signupUrl . "</a>");
        }
        $this->_secret=$secret;

        //echo 'init: $this->_secret='.$this->_secret."<br>";
    }

    /**
     * Encodes the given data into a query string format.
     *
     * @param array $data array of string elements to be encoded.
     *
     * @return string - encoded request.
     */
    private function _encodeQS($data)
    {
        $req = "";
        foreach ($data as $key => $value) {
            $req .= $key . '=' . urlencode(stripslashes($value)) . '&';
        }
        // Cut the last '&'
        $req=substr((string)$req, 0, strlen((string)$req)-1);
        return $req;
    }
    /**
     * Submits an HTTP GET to a reCAPTCHA server.
     *
     * @param string $path url path to recaptcha server.
     * @param array  $data array of parameters to be sent.
     *
     * @return array response
     */
    private function _submitHTTPGet($path, $data)
    {
        $req = $this->_encodeQS($data);
        $response = file_get_contents($path . $req);
        return $response;
    }
    /**
     * Calls the reCAPTCHA siteverify API to verify whether the user passes
     * CAPTCHA test.
     *
     * @param string $remoteIp   IP address of end user.
     * @param string $response   response string from recaptcha verification.
     *
     * @return ReCaptchaResponse
     */
    public function verifyResponse($remoteIp, $response)
    {
        //echo '$remoteIp='.$remoteIp."<br>";

        $recaptchaResponse = new ReCaptchaResponse();
        // Discard empty solution submissions
        if ($response == null || strlen((string)$response) == 0) {
            //$recaptchaResponse = new ReCaptchaResponse();
            $recaptchaResponse->success = false;
            $recaptchaResponse->errorCodes = 'missing-input';
            return $recaptchaResponse;
        }
        $getResponse = $this->_submitHttpGet(
            self::$_siteVerifyUrl,
            array (
                'secret' => $this->_secret,
                'remoteip' => $remoteIp,
                'v' => self::$_version,
                'response' => $response
            )
        );
        $answers = json_decode($getResponse, true);
        //dump($answers);
        //exit('captcha');

        //$recaptchaResponse = new ReCaptchaResponse();
        if (trim((string)$answers['success']) == true) {
            $recaptchaResponse->success = true;
        } else {
            $recaptchaResponse->success = false;
            $recaptchaResponse->errorCodes = $answers['error-codes'];
        }
        return $recaptchaResponse;
    }

    public function verifyResponse_new($request, $remoteIp, $recaptchaResponse, $captchaSecretKey) {

        echo '$remoteIp='.$remoteIp."<br>";
        //echo '$recaptchaResponse='.$recaptchaResponse."<br>";

        $userIp = $request->getClientIp();
        echo '$userIp='.$userIp."<br>";

        //$userSecUtil = $this->container->get('user_security_utility');
        //$captchaSecretKey = $userSecUtil->getSiteSettingParameter('captchaSecretKey');

        $secret = $captchaSecretKey;
        echo '$secret='.$secret."<br>";


        $client = HttpClient::create();
        $response = $client->request('POST', 'https://www.google.com/recaptcha/api/siteverify', [
            'body' => [
                'secret' => $secret,
                'response' => $recaptchaResponse,
                'remoteip' => $userIp
            ]
        ]);
        $responseData = $response->toArray();
        dump($responseData);


        $verifyUrl = 'https://www.google.com/recaptcha/api/siteverify';
        //$response = file_get_contents($verifyUrl . '?secret=' . urlencode($this->_secret) . '&response=' . urlencode($recaptchaResponse) . '&remoteip=' . urlencode($userIp));
        $fullVerifyUrl = $verifyUrl . '?secret=' . urlencode($secret) . '&response=' . urlencode($recaptchaResponse) . '&remoteip=' . urlencode($userIp);
        echo '$fullVerifyUrl='.$fullVerifyUrl."<br>";
        $response = file_get_contents($fullVerifyUrl);
        $responseData = json_decode($response);
        dump($responseData);
        exit('captcha');

        $recaptchaResponse = new ReCaptchaResponse();
        if ($responseData->success) {
            //CAPTCHA passed - proceed with form logic
            $recaptchaResponse->success = true;
        } else {
            //CAPTCHA failed - handle error
            $recaptchaResponse->success = false;
            $recaptchaResponse->errorCodes = $responseData->errorCodes; //['error-codes'];
        }

    }
}
?>


