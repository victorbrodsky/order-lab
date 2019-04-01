<?php
/**
 * Created by PhpStorm.
 * User: ch3
 * Date: 4/1/2019
 * Time: 11:31 AM
 */

namespace Oleg\FellAppBundle\Util;


class RecLetterUtil {

    protected $em;
    protected $container;
    protected $uploadDir;

    public function __construct( $em, $container ) {
        $this->em = $em;
        $this->container = $container;
        $this->uploadDir = 'Uploaded';
    }

    //Recommendation Letter Salted Script Hash ID
    public function generateRecLetterId( $fellapp, $reference, $request ) {

        $userSecUtil = $this->container->get('user_security_utility');

        $str = "pepperstr";

        $salt = $userSecUtil->getSiteSettingParameter('recLetterSaltFellApp');
        if( !$salt ) {
            $salt = 'pepper';
        }

        //Generate "Recommendation Letter Salted Scrypt Hash ID":
        // Live Server URL from Site Settings +
        // Organizational Group of the received application +
        // Fellowship Type of the Application +
        // Application ID +
        // Application Timestamp +
        // Reference ID +
        // Reference Email +
        // "Recommendation Letter Salt"

        $url = $request->getSchemeAndHttpHost();

        //use if (hash_equals($knownString, $userInput)) to compare two hash (or php password_verify)
        //$hash = md5($str);
        //$hash = sha1($str);
        $hash = hash("sha1",$str); //sha1
        //$hash = password_hash($str,PASSWORD_DEFAULT);

        echo "Hash=".$hash."<br>";

        return $hash;
    }


}