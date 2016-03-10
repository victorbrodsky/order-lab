<?php
namespace Oleg\UserdirectoryBundle\Util;

use Oleg\UserdirectoryBundle\Util\UserUtil;

/**
 * Description of EmailUtil
 *
 * @author Cina
 */
class EmailUtil {

    protected $em;
    protected $sc;
    protected $container;

    public function __construct( $em, $sc, $container ) {
        $this->em = $em;
        $this->sc = $sc;
        $this->container = $container;
    }

    //$emails: single or array of emails
    //$ccs: single or array of emails
    public function sendEmail( $emails, $subject, $message, $ccs=null, $fromEmail=null ) {

        if( $this->hasConnection() == false ) {
            //exit('no connection');
            return false;
        }
        //exit('yes connection');

        if( !$emails || $emails == "" ) {
            return false;
        }

        if( !$message || $message == "" ) {
            return false;
        }

        if( !$fromEmail ) {
            $userutil = new UserUtil();
            $fromEmail = $userutil->getSiteSetting($this->em,'siteEmail');
        }

        $emails = $this->checkEmails($emails);
        $ccs = $this->checkEmails($ccs);

        $message = \Swift_Message::newInstance()
            ->setSubject($subject)
            ->setFrom($fromEmail)
            ->setTo($emails)
            ->setBody(
                $message,
                'text/plain'
        );

        if( $ccs ) {
            $message->setCc($ccs);
        }

            /*
             * If you also want to include a plaintext version of the message
            ->addPart(
                $this->renderView(
                    'Emails/registration.txt.twig',
                    array('name' => $name)
                ),
                'text/plain'
            )
            */

        return $this->container->get('mailer')->send($message);
    }

    public function checkEmails($emails) {
        if( strpos($emails, ',') !== false ) {
            $emails = explode(',', $emails);
        }
        return $emails;
    }


    public function hasConnection() {
        $result = false;

        $userutil = new UserUtil();
        $smtp = $userutil->getSiteSetting($this->em,'smtpServerAddress');
        //echo "smtp=" . $smtp . "<br>";

        $fp = fsockopen($smtp, 25, $errno, $errstr, 5) ;

        if (!$fp) {
            //echo "SendEmail server:$smtp; ERROR:$errno - $errstr<br />\n";
            $logger = $this->container->get('logger');
            $logger->error("SendEmail server=$smtp; ERROR:$errno - $errstr");
        } else {
            //fwrite($fp, "\n");
            //echo fread($fp, 26);
            //fclose($fp);
        }

        return $result;
    }


    ///////////////// NOT USED: using original php mail  /////////////////
    public function sendEmail_orig( $email, $subject, $message, $em, $ccs=null, $fromEmail=null ) {

        if( !$email || $email == "" ) {
            return false;
        }

        if( !$message || $message == "" ) {
            return false;
        }

        $this->initEmail($em,$fromEmail);

        $headers = null;
        if( $ccs ) {
            $headers = 'Cc: ' . $ccs . "\r\n";
        }

        //echo "email=".$email."<br>";
        //echo "headers=".$headers."<br>";
        //exit('1');

        // Send
        mail($email, $subject, $message, $headers);

        return true;
    }

    public function initEmail($em,$fromEmail=null) {
        $userutil = new UserUtil();
        $smtp = $userutil->getSiteSetting($em,'smtpServerAddress');

        if( !$fromEmail ) {
            $fromEmail = $userutil->getSiteSetting($em,'siteEmail');
        }

        //exit("smtp=".$smtp);

        ini_set( 'sendmail_from', $fromEmail );
        ini_set( "SMTP", $smtp );
    }
    ///////////////// EOF NOT USED: using original php mail  /////////////////
    
}

?>
