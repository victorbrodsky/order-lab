<?php
namespace Oleg\UserdirectoryBundle\Util;

use Oleg\UserdirectoryBundle\Util\UserUtil;

/**
 * Description of EmailUtil
 *
 * @author Cina
 */
class EmailUtil {


    public function sendEmail( $email, $subject, $message, $em, $ccs=null ) {

        if( !$email || $email == "" ) {
            return false;
        }

        if( !$message || $message == "" ) {
            return false;
        }

        $this->initEmail($em);

        $headers = null;
        if( $ccs ) {
            $headers = 'Cc: ' . $ccs . "\r\n";
        }

        // Send
        mail($email, $subject, $message, $headers);

        return true;
    }

    public function initEmail($em) {
        $userutil = new UserUtil();
        $adminemail = $userutil->getSiteSetting($em,'siteEmail');
        $smtp = $userutil->getSiteSetting($em,'smtpServerAddress');

        //exit("smtp=".$smtp);

        ini_set( 'sendmail_from', $adminemail );
        ini_set( "SMTP", $smtp );
    }
    
}

?>
