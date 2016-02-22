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

    public function sendEmail( $email, $subject, $message, $ccs=null, $fromEmail=null ) {

        if( !$fromEmail ) {
            $userutil = new UserUtil();
            $fromEmail = $userutil->getSiteSetting($this->em,'siteEmail');
        }

        $message = \Swift_Message::newInstance()
            ->setSubject($subject)
            ->setFrom($fromEmail)
            ->setTo($email)
            ->setBody(
                $message,
                'text/plain'
        );

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
    
}

?>
