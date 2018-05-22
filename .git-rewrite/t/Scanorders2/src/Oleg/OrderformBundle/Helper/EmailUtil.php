<?php
namespace Oleg\OrderformBundle\Helper;

/**
 * Description of EmailUtil
 *
 * @author Cina
 */
class EmailUtil {
    
    public function sendEmail( $email, $entity, $text = null, $conflict=null ) {

        if( !$email || $email == "" ) {
            return false;
        }

        ini_set( 'sendmail_from', "slidescan@med.cornell.edu" ); //My usual e-mail address
        ini_set( "SMTP", "smtp.med.cornell.edu" );  //My usual sender
        //ini_set( 'smtp_port', 25 );

        if( $text ) {
            $message = $text;
        } else {
            $thanks_txt =
                "Thank You For Your Order !\r\n"
                . "Order " . $entity->getId() . " Successfully Submitted.\r\n"
                . "Confirmation Email was sent to " . $email . "\r\n";
            $message = $thanks_txt;
        }

        if( $conflict ) {
            $message = $message."\r\n\r\n".$conflict;
        }

        // In case any of our lines are larger than 70 characters, we should use wordwrap()
        $message = wordwrap($message, 70, "\r\n");
        // Send
        mail($email, 'Scan Order Confirmation', $message);

        return true;
    }
    
}

?>
