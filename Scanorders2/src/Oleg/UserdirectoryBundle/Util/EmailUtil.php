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

        $this->container->get('mailer')->send($message);

        return true;
    }

    public function checkEmails($emails) {
        if( strpos($emails, ',') !== false ) {
            $emails = explode(',', $emails);
        }
        return $emails;
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
