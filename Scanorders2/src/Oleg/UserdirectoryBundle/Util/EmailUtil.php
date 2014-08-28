<?php
namespace Oleg\UserdirectoryBundle\Util;

use Oleg\UserdirectoryBundle\Util\UserUtil;

/**
 * Description of EmailUtil
 *
 * @author Cina
 */
class EmailUtil {
    
    public function sendEmail( $email, $subject, $message ) {

        if( !$email || $email == "" ) {
            return false;
        }

        if( !$message || $message == "" ) {
            return false;
        }

        // Send
        mail($email, $subject, $message);

        return true;
    }

    public function initEmail($em) {
        $userutil = new UserUtil();
        $adminemail = $userutil->getSiteSetting($em,'siteEmail');
        $smtp = $userutil->getSiteSetting($em,'smtpServerAddress');

        ini_set( 'sendmail_from', $adminemail );
        ini_set( "SMTP", $smtp );
    }
    
}

?>
