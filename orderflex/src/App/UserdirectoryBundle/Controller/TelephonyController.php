<?php

namespace App\UserdirectoryBundle\Controller;

use App\UserdirectoryBundle\Controller\OrderAbstractController;
use App\UserdirectoryBundle\Entity\AccessRequest;
use App\UserdirectoryBundle\Entity\UserRequest;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

//use Twilio\Rest\Client;

class TelephonyController extends OrderAbstractController {

    protected $siteName;

    public function __construct() {
        $this->siteName = 'employees'; //controller is not setup yet, so we can't use $this->getParameter('employees.sitename');
    }

    /**
     * Get verification form
     *
     * @Route("/verify-mobile-phone/{siteName}/{phoneNumber}", name="employees_verify_mobile_phone", methods={"GET"})
     */
    public function verifyMobilePhoneAction(Request $request, $siteName, $phoneNumber )
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
            'sitename' => $siteName,
            'title' => "Mobile Phone Verification",
            //'form' => $form->createView(),
            'phoneNumber' => $phoneNumber,
            'mobilePhoneVerified' => $mobilePhoneVerified
        ));
    }

    /**
     * Visiting this page should not require a log
     *
     * @Route("/verify-mobile-code/{verificationCode}", name="employees_verify_mobile_code", methods={"GET"})
     */
    public function verifyMobileCodeAction(Request $request, $verificationCode=null) {

        $em = $this->getDoctrine()->getManager();
        $userServiceUtil = $this->get('user_service_utility');
        //$user = $this->get('security.token_storage')->getToken()->getUser(); //user here is undefined

        //testing
        //$code = $userServiceUtil->generateVerificationCode();
        //exit("code=".$code);

        if( !$verificationCode ) {
            $verificationCode = $request->query->get('verify-code');
        }
        //exit("verificationCode=".$verificationCode);

        //0) find user by the $verificationCode
        $user = $userServiceUtil->getUserByVerificationCode($verificationCode);

        if( $user ) {
            //1) get userInfo
            $userInfo = $user->getUserInfo();

            //2) use $verificationCode to verify the verification code in userInfo, if equal then change => verified
            $phoneNumberVerified = $userInfo->verifyCode($verificationCode);
            if( $phoneNumberVerified ) {
                $userInfo->setVerified();
                if( $this->siteName == 'crn' ) {
                    $user->addRole("ROLE_CRN_RECIPIENT");
                }
                $em->flush();
                $this->get('session')->getFlashBag()->add(
                    'notice',
                    'Your mobile phone number has been successfully verified.'
                );

                //EventLog: $eventType, $event, $testing
                $eventMsg = 'Mobile phone number '.$phoneNumberVerified.' has been successfully verified by verifyMobileCodeAction';
                $userServiceUtil->setVerificationEventLog('Mobile Phone Verified', $eventMsg);
                
                return $this->redirect($this->generateUrl('main_common_home'));
            } else {
//                $this->get('session')->getFlashBag()->add(
//                    'warning',
//                    'Invalid verification code.'
//                );
                if( $userInfo->getPreferredMobilePhoneVerified() ) {
                    $resFailed = "Mobile phone number is already verified";
                } else {
                    $resFailed = "Verification failed";
                }
                $this->get('session')->getFlashBag()->add(
                    'warning',
                    $resFailed
                );
            }

            //2) use $verificationCode to verify the verification code in userInfo, if equal then change => verified
//            $userVerificationCode = $userInfo->getMobilePhoneVerifyCode();
//            $phoneNumber = $userInfo->getPreferredMobilePhone();
//            $notExpired = $userInfo->verificationCodeIsNotExpired();
//            if( $notExpired && $phoneNumber && $userVerificationCode && $verificationCode && $userVerificationCode == $verificationCode ) {
//                //OK
//                $userInfo->setMobilePhoneVerifyCode(NULL);
//                $userInfo->setMobilePhoneVerifyCodeDate(NULL);
//                $userInfo->setPreferredMobilePhoneVerified(true);
//
//                $em->flush();
//
//                $this->get('session')->getFlashBag()->add(
//                    'notice',
//                    'Your mobile phone number has been successfully verified.'
//                );
//
//                return $this->redirect($this->generateUrl('main_common_home'));
//            } else {
////                $this->get('session')->getFlashBag()->add(
////                    'warning',
////                    'Invalid verification code.'
////                );
//            }
        } else {
//            $this->get('session')->getFlashBag()->add(
//                'warning',
//                'Invalid verification code.'
//            );
        }

        //$phoneNumber = null;
        $mobilePhoneVerified = false;

        //testing
        //$user = $em->getRepository('AppUserdirectoryBundle:User')->find(4689);

        if( $user ) {
            //“visit your profile page to restart the verification process” is a link to user's profile page
            //visit your profile page to restart the verification process
            $profileLink = $this->container->get('router')->generate(
                'employees_showuser',
                array(
                    'id' => $user->getId(),
                ),
                UrlGeneratorInterface::ABSOLUTE_URL
            );
            $profileLink = "<a data-toggle='tooltip' title='Verification Link' href=" . $profileLink . ">visit your profile page to restart the verification process</a>";

            $userInfo = $user->getUserInfo();
            if( $userInfo ) {
                $mobilePhoneVerified = $userInfo->getPreferredMobilePhoneVerified();
                //$phoneNumber = $userInfo->getPreferredMobilePhone();
            }

            if( !$mobilePhoneVerified ) {
                $mobilePhoneVerified = false;
            }

        } else {
            $profileLink = "visit your profile page to restart the verification process";
        }

        //testing
        //$mobilePhoneVerified = false;

        $message = "The supplied verification code appears to be invalid."
        ." Please type the code in manually or $profileLink."
        ." If you have requested an account, you will be able to verify your mobile phone number once the account is created.";

        return $this->render('AppUserdirectoryBundle/Telephony/verify-mobile-code.html.twig', array(
            'sitename' => $this->siteName,
            'title' => "Mobile Phone Verification",
            'mobilePhoneVerified' => $mobilePhoneVerified,
            'verificationCode' => $verificationCode,
            'message' => $message
            //'form' => $form->createView(),

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
    public function verifyCodeAction(Request $request) {

        //testing
        //$lastRoute = $request->getSession()->get('originalRouteOnLogin');
        //return $this->redirect($lastRoute);
        //exit('$lastRoute='.$lastRoute);
        //$logger = $this->container->get('logger');

        try {
            $em = $this->getDoctrine()->getManager();
            $userServiceUtil = $this->get('user_service_utility');

            // Get data from session
            //$data = $this->get('session')->get('user');
            $verificationCode = $request->query->get('verify_code');
            $verificationCode = trim($verificationCode);

            $phoneNumber = $request->query->get('phoneNumber');
            $phoneNumber = trim($phoneNumber);

            $siteName = $request->query->get('siteName');
            $siteName = trim($siteName);

            if( !$siteName ) {
                $siteName = $this->siteName;
            }

            //echo 'verificationCode='.$verificationCode.", phoneNumber=".$phoneNumber.", siteName=".$siteName."<br>";

//            if( !$phoneNumber ) {
//                $user = $userServiceUtil->getUserByVerificationCode($verificationCode);
//            }

            if( $verificationCode && $phoneNumber ) {
                $user = $this->get('security.token_storage')->getToken()->getUser();

                $userInfo = $user->getUserInfoByPreferredMobilePhone($phoneNumber);

                if( $userInfo ) {

                    $phoneNumberVerified = $userInfo->verifyCode($verificationCode);
                    if( $phoneNumberVerified ) {
                        $userInfo->setVerified();
                        if( $siteName == 'crn' ) {
                            $user->addRole("ROLE_CRN_RECIPIENT");
                        }
                        $em->flush();
                        $this->get('session')->getFlashBag()->add(
                            'notice',
                            'Mobile phone number is verified!'
                        );

                        //EventLog: $eventType, $event, $testing
                        $eventMsg = 'Mobile phone number '.$phoneNumberVerified.' has been successfully verified by verifyCodeAction';
                        $userServiceUtil->setVerificationEventLog('Mobile Phone Verified', $eventMsg);

                        //redirect to the last root or home page
                        $lastRoute = $request->getSession()->get('originalRouteOnLogin');
                        //exit('$lastRoute='.$lastRoute);
                        if( $lastRoute ) {
                            //I should be redirected to the URL I was trying to visit after login.
                            $request->getSession()->set('originalRouteOnLogin',NULL);
                            return $this->redirect($lastRoute);
                        } else {
                            return $this->redirectToRoute($siteName.'_home');
                        }
                    } else { //if $phoneNumberVerified
                        //exit("Not equal verification code: verificationCode=[$verificationCode], userVerificationCode=[$userVerificationCode]");
                        if( $userInfo->getMobilePhoneVerified() ) {
                            $resFailed = "Mobile phone number is already verified";
                        } else {
                            $resFailed = "Verification failed";
                        }
                        //$logger->error("verifyCodeAction: ".$resFailed);
                        $this->get('session')->getFlashBag()->add(
                            'warning',
                            $resFailed
                        );
                    } //else $phoneNumberVerified

//                    $userVerificationCode = $userInfo->getMobilePhoneVerifyCode();
//                    $notExpired = $userInfo->verificationCodeIsNotExpired();
//                    if( $notExpired && $verificationCode && $userVerificationCode && $verificationCode == $userVerificationCode ) {
//                        $userInfo->setMobilePhoneVerifyCode(NULL);
//                        $userInfo->setMobilePhoneVerifyCodeDate(NULL);
//                        $userInfo->setPreferredMobilePhoneVerified(true);
//                        $em->flush();
//
//                        $this->get('session')->getFlashBag()->add(
//                            'notice',
//                            'Mobile phone number is verified!.'
//                        );
//
//                        //redirect to the last root or home page
//                        $lastRoute = $request->getSession()->get('originalRouteOnLogin');
//                        //exit('$lastRoute='.$lastRoute);
//                        if( $lastRoute ) {
//                            //I should be redirected to the URL I was trying to visit after login.
//                            $request->getSession()->set('originalRouteOnLogin',NULL);
//                            return $this->redirect($lastRoute);
//                        } else {
//                            return $this->redirectToRoute($siteName.'_home');
//                        }
//
//                    } else {
//                        //exit("Not equal verification code: verificationCode=[$verificationCode], userVerificationCode=[$userVerificationCode]");
//                        $this->get('session')->getFlashBag()->add(
//                            'warning',
//                            'Verification code does not match.'
//                        );
//                    }
                } else { //if $userInfo
                    //$logger->error("verifyCodeAction: userInfo not found by phoneNumber=".$phoneNumber); //TODO: check this error after phone is modified on the verification page
                } //else $userInfo
            } else {
                //$logger->error("verifyCodeAction: Logical error: invalid input parameters. phoneNumber=".$phoneNumber);
                //exit("Invalid input parameters: verificationCode=[$verificationCode], phoneNumber=[$phoneNumber]");
                $this->get('session')->getFlashBag()->add(
                    'warning',
                    'Logical error: invalid input parameters.'
                );
            } //else $userInfo

            //exit('Mobile phone number not verified.');

            //$logger->error("verifyCodeAction: Logical error: Unknown error. phoneNumber=".$phoneNumber);
            $this->get('session')->getFlashBag()->add(
                'warning',
                'Mobile phone number not verified.'
            );

            //exit("OK verificationCode=[$verificationCode]");
            return $this->redirectToRoute('employees_verify_mobile_phone',array('siteName'=>$siteName, 'phoneNumber'=>$phoneNumber));


        } catch (\Exception $exception) {
            //exit("Verification code is incorrect");
            $this->addFlash(
                'error',
                'Verification failed'
            );
            return $this->redirectToRoute('employees_verify_mobile_phone',array('siteName'=>$siteName, 'phoneNumber'=>$phoneNumber));
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
        $em = $this->getDoctrine()->getManager();
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
        //exit("phoneNumber=".$phoneNumber);

//        $userInfo = $user->getUserInfoByPreferredMobilePhone($phoneNumber);
//        if( $userInfo ) {
//            $userPreferredMobilePhone = $userInfo->getPreferredMobilePhone();
//            if( $phoneNumber && $userPreferredMobilePhone && $phoneNumber == $userPreferredMobilePhone ) {
//                //ok
//            } else {
//                return $this->redirect($this->generateUrl('employees-nopermission'));
//            }
//        }

        //if new phone number entered, the old verification is invalid => reassign the phone number and reset its properties (verification code and status)
        if( $userServiceUtil->userHasPhoneNumber($phoneNumber) === false ) {
            //return $this->redirect($this->generateUrl('employees-nopermission'));
            $userInfo = $user->getUserInfo();
            if( $userInfo ) {
                $userInfo->setPreferredMobilePhone($phoneNumber);
                $em->flush();
            }
        }

        $verifyCode = $userServiceUtil->assignVerificationCode($user,$phoneNumber);

        $text = "Mobile phone number verification code $verifyCode.";

        //https://view.med.cornell.edu/verify-mobile/XXXXXX
        $verificationUrl = $userServiceUtil->getVerificationUrl($verifyCode);
        $text = $text . " Please connect to VPN or the network and visit " .
                $verificationUrl . " to complete the verification process.";

        //exit($text); //testing

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

        if( $userServiceUtil->userHasPhoneNumber($phoneNumber) === false ) {
            $res = "User can not verify this phone number";
            $json = json_encode($res);
            $response = new Response($json);
            $response->headers->set('Content-Type', 'application/json');
            return $response;
        }

        if( $verificationCode && $phoneNumber ) {

            $userInfo = $user->getUserInfoByPreferredMobilePhone($phoneNumber);

            if ($userInfo) {

                $phoneNumberVerified = $userInfo->verifyCode($verificationCode);
                if( $phoneNumberVerified ) {
                    $userInfo->setVerified();
                    if( $this->siteName == 'crn' ) {
                        $user->addRole("ROLE_CRN_RECIPIENT");
                    }
                    $em->flush();
                    $res = "OK";

                    //EventLog: $eventType, $event, $testing
                    $eventMsg = 'Mobile phone number '.$phoneNumberVerified.' has been successfully verified by verifyCodeAjaxAction';
                    $userServiceUtil->setVerificationEventLog('Mobile Phone Verified', $eventMsg);
                } else {
                    //exit("Verification failed: phoneNumberVerified=[$phoneNumberVerified]");
                    if( $userInfo->getPreferredMobilePhoneVerified() ) {
                        $res = "Mobile phone number is already verified";
                    } else {
                        $res = "Verification failed";
                    }
                }

//                $userVerificationCode = $userInfo->getMobilePhoneVerifyCode();
//                $notExpired = $userInfo->verificationCodeIsNotExpired();
//                if( $notExpired && $verificationCode && $userVerificationCode && $verificationCode == $userVerificationCode ) {
//                    $userInfo->setMobilePhoneVerifyCode(NULL);
//                    $userInfo->setMobilePhoneVerifyCodeDate(NULL);
//                    $userInfo->setPreferredMobilePhoneVerified(true);
//                    $em->flush();
//                    $res = "OK";
//
//                    //EventLog
//
//
//                } else {
//                    //exit("Not equal verification code: verificationCode=[$verificationCode], userVerificationCode=[$userVerificationCode]");
//                    $res = "Verification code does not match";
//                }
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



    /**
     * Get verification form for Account Request
     *
     * @Route("/verify-mobile-phone/account-request/{sitename}/{objectName}/{id}", name="employees_verify_mobile_phone_account_request", methods={"GET"})
     */
    public function verifyAccountRequestMobilePhoneAction(Request $request, $sitename, $objectName, $id) {

        $em = $this->getDoctrine()->getManager();

        if( $objectName && $id ) {
            $requestObject = $em->getRepository('AppUserdirectoryBundle:'.$objectName)->find($id);
        } else {
            throw new \Exception("Logical Error: object name and id are not specified");
        }
        
        //exit('verifyAccountRequestMobilePhoneAction');
        //It's better to check if current user has a $phoneNumber
        $phoneNumber = $requestObject->getMobilePhone();

        $mobilePhoneVerified = $requestObject->getMobilePhoneVerified();
        if( !$mobilePhoneVerified ) {
            $mobilePhoneVerified = false;
        }

        return $this->render('AppUserdirectoryBundle/Telephony/verify-account-request-mobile-phone.html.twig', array(
            'sitename' => $sitename,
            'title' => "Mobile Phone Verification",
            'requestObject' => $requestObject,
            'phoneNumber' => $phoneNumber,
            'mobilePhoneVerified' => $mobilePhoneVerified,
            'objectName' => $objectName
        ));
    }
    /**
     * https://www.twilio.com/docs/sms/tutorials/how-to-send-sms-messages-php
     *
     * @Route("/verify-mobile-phone-account-request-ajax", name="employees_verify_mobile_phone_account_request_ajax", methods={"POST"}, options={"expose"=true})
     */
    public function verifyAccountRequestMobileAjaxAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $userServiceUtil = $this->get('user_service_utility');
        //$user = $this->get('security.token_storage')->getToken()->getUser();

        $userRequest = NULL;
        
        $phoneNumber = $request->get('phoneNumber');
        $userRequestId = $request->get('userRequestId');
        $objectName = $request->get('objectName');

        if( $userRequestId ) {
            //$userRequest = $em->getRepository('AppUserdirectoryBundle:UserRequest')->find($userRequestId);
            if( $objectName && $userRequestId ) {
                $requestObject = $em->getRepository('AppUserdirectoryBundle:'.$objectName)->find($userRequestId);
            } else {
                throw new \Exception("Logical Error: object name and id are not specified");
            }
        } else {
            $json = json_encode("Account Request is not found");
            $response = new Response($json);
            $response->headers->set('Content-Type', 'application/json');
            return $response;
        }

        //if new phone number entered, the old verification is invalid => reassign the phone number and reset its properties (verification code and status)
        if( $requestObject ) {
            $requestObjectMobilePhone = $requestObject->getMobilePhone();
            if( $requestObjectMobilePhone && $requestObjectMobilePhone != $phoneNumber ) {
                $requestObject->setMobilePhone($phoneNumber);
                $em->flush();
            }
        }

        $verifyCode = $userServiceUtil->assignAccountRequestVerificationCode($requestObject,$objectName,$phoneNumber);

        $text = "Mobile phone number verification code $verifyCode.";

        //https://view.med.cornell.edu/verify-mobile/XXXXXX
//        $verificationUrl = $userServiceUtil->getVerificationUrl($verifyCode);
//        $text = $text . " Please connect to VPN or the network and visit " .
//            $verificationUrl . " to complete the verification process.";
        
        $message = $userServiceUtil->sendText($phoneNumber,$text);
        
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
     * @Route("/verify-code-account-request-ajax", name="employees_verify_code_account_request_ajax", methods={"POST"}, options={"expose"=true})
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function verifyAccountRequestCodeAjaxAction(Request $request)
    {
        $userServiceUtil = $this->get('user_service_utility');
        $em = $this->getDoctrine()->getManager();

        $userRequest = NULL;

        // Get data
        //$verificationCode = $request->query->get('verificationCode');
        $userRequestId = $request->request->get('userRequestId');
        $userRequestId = trim($userRequestId);
        $objectName = $request->get('objectName');

        if( $userRequestId ) {
            //$userRequest = $em->getRepository('AppUserdirectoryBundle:UserRequest')->find($userRequestId);
            if( $objectName && $userRequestId ) {
                $requestObject = $em->getRepository('AppUserdirectoryBundle:'.$objectName)->find($userRequestId);
            } else {
                throw new \Exception("Logical Error: object name and id are not specified");
            }
        } else {
            $json = json_encode("Account Request is not found");
            $response = new Response($json);
            $response->headers->set('Content-Type', 'application/json');
            return $response;
        }

        //$phoneNumber = $request->query->get('phoneNumber');
        $verificationCode = $request->request->get('verificationCode');
        $verificationCode = trim($verificationCode);

        $res = "Phone number is not verified";

        if( $requestObject && $verificationCode ) {

            $phoneNumberVerified = $requestObject->verifyCode($verificationCode);
            if( $phoneNumberVerified ) {
                $requestObject->setVerified();
                if( $this->siteName == 'crn' ) {
                    $requestObject->addRole("ROLE_CRN_RECIPIENT");
                }
                $em->flush();
                $res = "OK";

                //EventLog: $eventType, $event, $testing
                $eventMsg = 'Mobile phone number '.$phoneNumberVerified.' has been successfully verified by verifyAccountRequestCodeAjaxAction';
                $userServiceUtil->setVerificationEventLog('Mobile Phone Verified', $eventMsg);
            } else {
                if( $requestObject->getMobilePhoneVerified() ) {
                    $res = "Mobile phone number is already verified";
                } else {
                    $res = "Verification failed";
                }
            }

//            $userVerificationCode = $userRequest->getMobilePhoneVerifyCode();
//            $notExpired = $userRequest->verificationCodeIsNotExpired();
//            if( $notExpired && $verificationCode && $userVerificationCode && $verificationCode == $userVerificationCode ) {
//                $userRequest->setMobilePhoneVerifyCode(null);
//                $userRequest->setMobilePhoneVerifyCodeDate(null);
//                $userRequest->setMobilePhoneVerified(true);
//                $em->flush();
//                $res = "OK";
//
//                //EventLog
//
//            } else {
//                //exit("Not equal verification code: verificationCode=[$verificationCode], userVerificationCode=[$userVerificationCode]");
//                $res = "Verification code does not match";
//            }
            
        } else {
            $res = "Invalid parameters";
            //exit("Invalid parameters");
        }

        $json = json_encode($res);
        $response = new Response($json);
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }
}
