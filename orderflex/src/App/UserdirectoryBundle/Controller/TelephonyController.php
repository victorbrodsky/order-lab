<?php

namespace App\UserdirectoryBundle\Controller;

use App\UserdirectoryBundle\Controller\OrderAbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Twilio\Rest\Client;

class TelephonyController extends OrderAbstractController {

    protected $siteName;

    public function __construct() {
        $this->siteName = 'employees'; //controller is not setup yet, so we can't use $this->getParameter('employees.sitename');
    }

    /**
     * @Route("/verify-mobile-phone/{userId}/{phoneNumber}", name="employees_verify_mobile_phone", methods={"GET"})
     */
    public function verifyMobileAction(Request $request, $userId, $phoneNumber)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->get('security.token_storage')->getToken()->getUser();

        //$text = "verificationcode";
        //$text = random_int(1, 6);

        //$userid
        $subjectUser = $em->getRepository('AppUserdirectoryBundle:User')->find($userId);
        if( !$subjectUser ) {
            throw new \Exception( 'User not found by id ' . $userId );
        }

        //if not admin, only logged in user can verify its own mobile phone number
        if( false === $this->get('security.authorization_checker')->isGranted('ROLE_PLATFORM_DEPUTY_ADMIN') ) {
            if( $userId != $user->getId() ) {
                return $this->redirect($this->generateUrl('employees-nopermission'));
            }
        }

        $verifyCode = $this->assignVerificationCode($subjectUser,$phoneNumber);

        $text = "Mobile phone number verification code $verifyCode";

        $message = $this->sendText($phoneNumber,$text);

        dump($message);

        exit('EOF verifyMobileAction');

//        return $this->render('AppUserdirectoryBundle/SignUp/index.html.twig', array(
//            'signUps' => $signUps,
//            'title' => "Sign Up for ".$this->siteNameStr,
//            'sitenamefull' => $this->siteNameStr,
//            'sitename' => $this->siteName
//        ));
    }

    /**
     * @Route("/verify-mobile-phone-ajax/{userId}/{phoneNumber}", name="employees_verify_mobile_phone_ajax", methods={"GET"})
     */
    public function verifyMobileAjaxAction(Request $request, $userId, $phoneNumber)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->get('security.token_storage')->getToken()->getUser();

        //$text = "verificationcode";
        //$text = random_int(1, 6);

        //$userid
        $subjectUser = $em->getRepository('AppUserdirectoryBundle:User')->find($userId);
        if( !$subjectUser ) {
            throw new \Exception( 'User not found by id ' . $userId );
        }

        //if not admin, only logged in user can verify its own mobile phone number
        if( false === $this->get('security.authorization_checker')->isGranted('ROLE_PLATFORM_DEPUTY_ADMIN') ) {
            if( $userId != $user->getId() ) {
                return $this->redirect($this->generateUrl('employees-nopermission'));
            }
        }

        $verifyCode = $this->assignVerificationCode($subjectUser,$phoneNumber);

        $text = "Mobile phone number verification code $verifyCode";

        $message = $this->sendText($phoneNumber,$text);

        dump($message);

        exit('EOF verifyMobileAction');

//        return $this->render('AppUserdirectoryBundle/SignUp/index.html.twig', array(
//            'signUps' => $signUps,
//            'title' => "Sign Up for ".$this->siteNameStr,
//            'sitenamefull' => $this->siteNameStr,
//            'sitename' => $this->siteName
//        ));
    }

    public function assignVerificationCode($user,$phoneNumber) {
        $em = $this->getDoctrine()->getManager();
        $text = random_int(1, 6);
        
        $userInfo = $user->getUserInfoByPreferredMobilePhone($phoneNumber);
        
        if( $userInfo ) {
            $userInfo->setMobilePhoneVerifyCode($text);
            $userInfo->setPreferredMobilePhoneVerified(false);
            $em->flush();
        }

        return $text;
    }

    public function sendText( $phoneNumber, $textToSend ) {
        // Find your Account Sid and Auth Token at twilio.com/console
        // DANGER! This is insecure. See http://twil.io/secure
        //$sid    = "ACXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX";
        //$token  = "your_auth_token";
        //$twilio = new Client($sid, $token);
//        $message = $twilio->messages
//            ->create("+1xxx", // to
//                [
//                    "body" => "This is the ship that made the Kessel Run in fourteen parsecs?",
//                    "from" => "+1xxx"
//                ]
//            );

        $userSecUtil = $this->get('user_security_utility');

        $twilioSid = $userSecUtil->getSiteSettingParameter('twilioSid','Telephony');
        $twilioApiKey = $userSecUtil->getSiteSettingParameter('twilioApiKey','Telephony');
        $fromPhoneNumber = $userSecUtil->getSiteSettingParameter('fromPhoneNumber','Telephony');

        //$twilioSid = "xxxxx";
        //$twilioApiKey = "xxxxx";
        //$fromPhoneNumber = "xxxxx";

        $twilio = new Client($twilioSid, $twilioApiKey);

        $message = $twilio->messages
            ->create($phoneNumber, // to
                [
                    "body" => $textToSend,      //"This is the test telephony message",
                    "from" => $fromPhoneNumber //"+11234567890"
                ]
            );

        print($message->sid);

        return $message;
    }

    //twilioSid, twilioApiKey
    public function getTelephonyParameters( $parameterName ) {
        $em = $this->getDoctrine()->getManager();
        $entities = $em->getRepository('AppUserdirectoryBundle:SiteParameters')->findAll();
        if( count($entities) != 1 ) {
            throw new \Exception( 'Must have only one site parameter object. Found '.count($entities).'object(s)' );
        }
        $siteParameters = $entities[0];

        $telephonySiteParameter = $siteParameters->getTelephonySiteParameter();

        //create one TelephonySiteParameter
        if( !$telephonySiteParameter ) {
            throw new \Exception( 'TelephonySiteParameter does not exists. Found ' );
        }

        $getMethod = 'get'.$parameterName;

        if( !$telephonySiteParameter->$getMethod() ) {
            //echo "return: no documents<br>";
            return null;
        }

        $parameter = $telephonySiteParameter->$getMethod();

        return $parameter;
    }

}
