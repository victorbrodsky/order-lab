<?php
namespace Oleg\UserdirectoryBundle\Util;

use Oleg\UserdirectoryBundle\Util\UserUtil;

/**
 * Description of EmailUtil
 *
 * @author Cina
 */
class EmailUtil {


    public function sendEmail( $email, $subject, $message, $em, $ccs=null, $adminemail=null ) {

        if( !$email || $email == "" ) {
            return false;
        }

        if( !$message || $message == "" ) {
            return false;
        }

        $this->initEmail($em,$adminemail);

        $headers = null;
        if( $ccs ) {
            $headers = 'Cc: ' . $ccs . "\r\n";
        }

        // Send
        mail($email, $subject, $message, $headers);

        return true;
    }

    public function initEmail($em,$adminemail=null) {
        $userutil = new UserUtil();
        $smtp = $userutil->getSiteSetting($em,'smtpServerAddress');

        if( !$adminemail ) {
            $adminemail = $userutil->getSiteSetting($em,'siteEmail');
        }

        //exit("smtp=".$smtp);

        ini_set( 'sendmail_from', $adminemail );
        ini_set( "SMTP", $smtp );
    }
    
}

?>
