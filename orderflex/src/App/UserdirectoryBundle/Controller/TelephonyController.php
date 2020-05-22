<?php

namespace App\UserdirectoryBundle\Controller;

use App\UserdirectoryBundle\Controller\OrderAbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
//use Twilio\Rest\Client;

class TelephonyController extends OrderAbstractController {

    protected $siteName;

    public function __construct() {
        $this->siteName = 'employees'; //controller is not setup yet, so we can't use $this->getParameter('employees.sitename');
    }

    /**
     * Get verification form
     *
     * @Route("/verify-mobile-phone/{phoneNumber}", name="employees_verify_mobile_phone", methods={"GET"})
     */
    public function verifyMobileAction(Request $request, $phoneNumber)
    {
        //$em = $this->getDoctrine()->getManager();

//        //if not admin, only logged in user can verify its own mobile phone number
//        //if( false === $this->get('security.authorization_checker')->isGranted('ROLE_PLATFORM_DEPUTY_ADMIN') ) {
//            if( $userId != $user->getId() ) {
//                return $this->redirect($this->generateUrl('employees-nopermission'));
//            }
//        //}

//        $form = $this->createTelephonyVerifyForm();
//        $form->handleRequest($request);
//
//        if ($form->isSubmitted() && $form->isValid() ) {
//
//        }

        $user = $this->get('security.token_storage')->getToken()->getUser();

        //$text = random_int(100000, 999999);
        //echo "text=$text <br>";

        //It's better to check if current user has a $phoneNumber
        $preferredMobilePhone = $user->getPreferredMobilePhone();

        if( $preferredMobilePhone != $phoneNumber ) {
            return $this->redirect($this->generateUrl('employees-nopermission'));
        }

        $userInfo = $user->getUserInfoByPreferredMobilePhone($phoneNumber);

        if( $userInfo ) {
            $mobilePhoneVerified = $userInfo->getPreferredMobilePhoneVerified();
        }
        
        if( !$mobilePhoneVerified ) {
            $mobilePhoneVerified = false;
        }

        return $this->render('AppUserdirectoryBundle/Telephony/verify-mobile-phone.html.twig', array(
            'sitename' => $this->siteName,
            'title' => "Mobile Phone Verification",
            //'form' => $form->createView(),
            'phoneNumber' => $phoneNumber,
            'mobilePhoneVerified' => $mobilePhoneVerified
        ));
    }

//    /**
//     * Get verification form
//     *
//     * @Route("/verify-mobile-phone-modal/{phoneNumber}", name="employees_verify_mobile_phone_modal", methods={"GET"})
//     */
//    public function verifyMobileModalAction(Request $request, $phoneNumber)
//    {
//
//        $user = $this->get('security.token_storage')->getToken()->getUser();
//
//        //$text = random_int(100000, 999999);
//        //echo "text=$text <br>";
//
//        //It's better to check if current user has a $phoneNumber
//        $preferredMobilePhone = $user->getPreferredMobilePhone();
//
//        if( $preferredMobilePhone != $phoneNumber ) {
//            return $this->redirect($this->generateUrl('employees-nopermission'));
//        }
//
//        $userInfo = $user->getUserInfoByPreferredMobilePhone($phoneNumber);
//
//        if( $userInfo ) {
//            $mobilePhoneVerified = $userInfo->getPreferredMobilePhoneVerified();
//        }
//
//        if( !$mobilePhoneVerified ) {
//            $mobilePhoneVerified = false;
//        }
//
//        return $this->render('AppUserdirectoryBundle/Telephony/verify-mobile-phone-modal.html.twig', array(
//            'sitename' => $this->siteName,
//            'title' => "Mobile Phone Verification",
//            //'form' => $form->createView(),
//            'phoneNumber' => $phoneNumber,
//            'mobilePhoneVerified' => $mobilePhoneVerified
//        ));
//    }

    /**
     * @Route("/verify/code", name="employees_verify_code")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function verifyCode(Request $request)
    {
        try {
            $em = $this->getDoctrine()->getManager();
            // Get data from session
            //$data = $this->get('session')->get('user');
            $verificationCode = $request->query->get('verify_code');
            $verificationCode = trim($verificationCode);

            $phoneNumber = $request->query->get('phoneNumber');
            $phoneNumber = trim($phoneNumber);

            if( $verificationCode && $phoneNumber ) {
                $user = $this->get('security.token_storage')->getToken()->getUser();

                $userInfo = $user->getUserInfoByPreferredMobilePhone($phoneNumber);

                if( $userInfo ) {
                    $userVerificationCode = $userInfo->getMobilePhoneVerifyCode();
                    if( $verificationCode && $userVerificationCode && $verificationCode == $userVerificationCode ) {
                        $userInfo->setMobilePhoneVerifyCode(null);
                        $userInfo->setPreferredMobilePhoneVerified(true);
                        $em->flush();

                        $this->get('session')->getFlashBag()->add(
                            'notice',
                            'Mobile phone number is verified!.'
                        );
                    } else {
                        //exit("Not equal verification code: verificationCode=[$verificationCode], userVerificationCode=[$userVerificationCode]");
                        $this->get('session')->getFlashBag()->add(
                            'warning',
                            'Verification code does not match.'
                        );
                    }
                }
            } else {
                //exit("Invalid input parameters: verificationCode=[$verificationCode], phoneNumber=[$phoneNumber]");
                $this->get('session')->getFlashBag()->add(
                    'warning',
                    'Logical error: invalid input parameters.'
                );
            }

            $this->get('session')->getFlashBag()->add(
                'warning',
                'Mobile phone number not verified!.'
            );

            //exit("OK verificationCode=[$verificationCode]");
            return $this->redirectToRoute('employees_verify_mobile_phone',array('phoneNumber'=>$phoneNumber));


        } catch (\Exception $exception) {
            //exit("Verification code is incorrect");
            $this->addFlash(
                'error',
                'Verification code is incorrect'
            );
            return $this->redirectToRoute('employees_verify_mobile_phone',array('phoneNumber'=>$phoneNumber));
        }
    }

//    public function createTelephonyVerifyForm() {
//        $params = array();
//
//        $form = $this->createForm(TelephonyVerifyMobilePhoneType::class, null, array('form_custom_value'=>$params));
//
//        return $form;
//    }

    //Route("/verify-mobile-phone-ajax/{phoneNumber}", name="employees_verify_mobile_phone_ajax", methods={"GET"})
    /**
     * https://www.twilio.com/docs/sms/tutorials/how-to-send-sms-messages-php
     *
     * @Route("/verify-mobile-phone-ajax", name="employees_verify_mobile_phone_ajax", methods={"POST"}, options={"expose"=true})
     */
    public function verifyMobileAjaxAction(Request $request)
    {
        //$em = $this->getDoctrine()->getManager();
        $userServiceUtil = $this->get('user_service_utility');
        $user = $this->get('security.token_storage')->getToken()->getUser();

        //Testing
//        $res = 'OK';
//        $json = json_encode($res);
//        $response = new Response($json);
//        $response->headers->set('Content-Type', 'application/json');
//        return $response;

        //$text = "verificationcode";
        //$text = random_int(1, 6);

        //$userid
//        $subjectUser = $em->getRepository('AppUserdirectoryBundle:User')->find($userId);
//        if( !$subjectUser ) {
//            throw new \Exception( 'User not found by id ' . $userId );
//        }

        //if not admin, only logged in user can verify its own mobile phone number
//        if( false === $this->get('security.authorization_checker')->isGranted('ROLE_PLATFORM_DEPUTY_ADMIN') ) {
//            if( $userId != $user->getId() ) {
//                return $this->redirect($this->generateUrl('employees-nopermission'));
//            }
//        }

        $phoneNumber = $request->get('phoneNumber');

//        $userInfo = $user->getUserInfoByPreferredMobilePhone($phoneNumber);
//        if( $userInfo ) {
//            $userPreferredMobilePhone = $userInfo->getPreferredMobilePhone();
//            if( $phoneNumber && $userPreferredMobilePhone && $phoneNumber == $userPreferredMobilePhone ) {
//                //ok
//            } else {
//                return $this->redirect($this->generateUrl('employees-nopermission'));
//            }
//        }

        if( $userServiceUtil->userCanVerifyPhoneNumber($phoneNumber) === false ) {
            return $this->redirect($this->generateUrl('employees-nopermission'));
        }

        $verifyCode = $userServiceUtil->assignVerificationCode($user,$phoneNumber);

        $text = "Mobile phone number verification code $verifyCode";

        $message = $userServiceUtil->sendText($phoneNumber,$text);

        //dump($message);
        //exit('EOF verifyMobileAction');

//        return $this->render('AppUserdirectoryBundle/SignUp/index.html.twig', array(
//            'signUps' => $signUps,
//            'title' => "Sign Up for ".$this->siteNameStr,
//            'sitenamefull' => $this->siteNameStr,
//            'sitename' => $this->siteName
//        ));

//        $errorMessage = $message['error_message'];
        //$messageProperties = $message['properties'];
        //$errorMessage = $messageProperties['errorMessage'];
        $errorMessage = $message->errorMessage;
        //$status = $message->status;
        if( $errorMessage ) {
            $res = $errorMessage;
        } else {
            $res = 'OK';
        }

        $json = json_encode($res);
        $response = new Response($json);
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    /**
     * @Route("/verify-code-ajax", name="employees_verify_code_ajax", methods={"POST"}, options={"expose"=true})
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function verifyCodeAjaxAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->get('security.token_storage')->getToken()->getUser();
        $userServiceUtil = $this->get('user_service_utility');

        // Get data
        //$verificationCode = $request->query->get('verificationCode');
        $verificationCode = $request->request->get('verificationCode');
        $verificationCode = trim($verificationCode);

        //$phoneNumber = $request->query->get('phoneNumber');
        $phoneNumber = $request->request->get('phoneNumber');
        $phoneNumber = trim($phoneNumber);

        //testing
//        $res = "phoneNumber=$phoneNumber, verificationCode=$verificationCode";
//        $json = json_encode($res);
//        $response = new Response($json);
//        $response->headers->set('Content-Type', 'application/json');
//        return $response;

        $res = "Phone number is not verified";

        if( $userServiceUtil->userCanVerifyPhoneNumber($phoneNumber) === false ) {
            $res = "User can not verify this phone number";
            $json = json_encode($res);
            $response = new Response($json);
            $response->headers->set('Content-Type', 'application/json');
            return $response;
        }

        if( $verificationCode && $phoneNumber ) {

            $userInfo = $user->getUserInfoByPreferredMobilePhone($phoneNumber);

            if ($userInfo) {
                $userVerificationCode = $userInfo->getMobilePhoneVerifyCode();
                if( $verificationCode && $userVerificationCode && $verificationCode == $userVerificationCode ) {
                    $userInfo->setMobilePhoneVerifyCode(null);
                    $userInfo->setPreferredMobilePhoneVerified(true);
                    $em->flush();
                    $res = "OK";
                } else {
                    //exit("Not equal verification code: verificationCode=[$verificationCode], userVerificationCode=[$userVerificationCode]");
                    $res = "Verification code does not match";
                }
            } else {
                //exit("userInfo not found");
                $res = "User Info is not found";
            }
        } else {
            $res = "Invalid parameters";
            //exit("Invalid parameters");
        }

        $json = json_encode($res);
        $response = new Response($json);
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

//    public function userCanVerifyPhoneNumber($phoneNumber) {
//        $user = $this->get('security.token_storage')->getToken()->getUser();
//        $userInfo = $user->getUserInfoByPreferredMobilePhone($phoneNumber);
//
//        if( $userInfo ) {
//            $userPreferredMobilePhone = $userInfo->getPreferredMobilePhone();
//            if( $phoneNumber && $userPreferredMobilePhone && $phoneNumber == $userPreferredMobilePhone ) {
//                return true;
//            }
//        }
//
//        return false;
//    }

//    public function assignVerificationCode($user,$phoneNumber) {
//        $em = $this->getDoctrine()->getManager();
//        $text = random_int(100000, 999999);
//
//        $userInfo = $user->getUserInfoByPreferredMobilePhone($phoneNumber);
//
//        if( $userInfo ) {
//            $userInfo->setMobilePhoneVerifyCode($text);
//            $userInfo->setPreferredMobilePhoneVerified(false);
//            $em->flush();
//        }
//
//        return $text;
//    }
//    public function sendText( $phoneNumber, $textToSend ) {
//        // Find your Account Sid and Auth Token at twilio.com/console
//        // DANGER! This is insecure. See http://twil.io/secure
//        //$sid    = "ACXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX";
//        //$token  = "your_auth_token";
//        //$twilio = new Client($sid, $token);
////        $message = $twilio->messages
////            ->create("+1xxx", // to
////                [
////                    "body" => "This is the ship that made the Kessel Run in fourteen parsecs?",
////                    "from" => "+1xxx"
////                ]
////            );
//
//        $userSecUtil = $this->get('user_security_utility');
//
//        $twilioSid = $userSecUtil->getSiteSettingParameter('twilioSid','Telephony');
//        $twilioApiKey = $userSecUtil->getSiteSettingParameter('twilioApiKey','Telephony');
//        $fromPhoneNumber = $userSecUtil->getSiteSettingParameter('fromPhoneNumber','Telephony');
//
//        //$twilioSid = "xxxxx";
//        //$twilioApiKey = "xxxxx";
//        //$fromPhoneNumber = "xxxxx";
//
//        $twilio = new Client($twilioSid, $twilioApiKey);
//
//        $message = $twilio->messages
//            ->create($phoneNumber, // to
//                [
//                    "body" => $textToSend,      //"This is the test telephony message",
//                    "from" => $fromPhoneNumber //"+11234567890"
//                ]
//            );
//
//        print($message->sid);
//
//        return $message;
//    }

//    //twilioSid, twilioApiKey
//    public function getTelephonyParameters( $parameterName ) {
//        $em = $this->getDoctrine()->getManager();
//        $entities = $em->getRepository('AppUserdirectoryBundle:SiteParameters')->findAll();
//        if( count($entities) != 1 ) {
//            throw new \Exception( 'Must have only one site parameter object. Found '.count($entities).'object(s)' );
//        }
//        $siteParameters = $entities[0];
//
//        $telephonySiteParameter = $siteParameters->getTelephonySiteParameter();
//
//        //create one TelephonySiteParameter
//        if( !$telephonySiteParameter ) {
//            throw new \Exception( 'TelephonySiteParameter does not exists. Found ' );
//        }
//
//        $getMethod = 'get'.$parameterName;
//
//        if( !$telephonySiteParameter->$getMethod() ) {
//            //echo "return: no documents<br>";
//            return null;
//        }
//
//        $parameter = $telephonySiteParameter->$getMethod();
//
//        return $parameter;
//    }

}
