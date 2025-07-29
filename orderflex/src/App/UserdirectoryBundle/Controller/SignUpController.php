<?php

namespace App\UserdirectoryBundle\Controller;



use App\UserdirectoryBundle\Entity\Institution; //process.py script: replaced namespace by ::class: added use line for classname=Institution
use App\UserdirectoryBundle\Entity\AdminComment;
use App\UserdirectoryBundle\Entity\AdministrativeTitle;
use App\UserdirectoryBundle\Entity\AppointmentTitle;
use App\UserdirectoryBundle\Entity\BoardCertification;
use App\UserdirectoryBundle\Entity\Book;
use App\UserdirectoryBundle\Entity\ConfidentialComment;
use App\UserdirectoryBundle\Entity\EmploymentStatus;
use App\UserdirectoryBundle\Entity\Grant;
use App\UserdirectoryBundle\Entity\Lecture;
use App\UserdirectoryBundle\Entity\MedicalTitle;
use App\UserdirectoryBundle\Entity\PrivateComment;
use App\UserdirectoryBundle\Entity\Publication;
use App\UserdirectoryBundle\Entity\PublicComment;
use App\UserdirectoryBundle\Entity\ResearchLab;
use App\UserdirectoryBundle\Entity\ResetPassword;
use App\UserdirectoryBundle\Entity\SignUp;
use App\UserdirectoryBundle\Entity\SiteList;
use App\UserdirectoryBundle\Entity\StateLicense;
use App\UserdirectoryBundle\Entity\Training;
use App\UserdirectoryBundle\Entity\User;
use App\UserdirectoryBundle\Entity\UserInfo;
use App\UserdirectoryBundle\Form\ResetPasswordType;
use App\UserdirectoryBundle\Form\UserSimpleType;
use App\UserdirectoryBundle\Captcha\ReCaptcha;
use App\UserdirectoryBundle\Util\UserUtil;
use App\UserdirectoryBundle\Controller\OrderAbstractController;


use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;


class SignUpController extends OrderAbstractController
{

    protected $router;
    protected $siteName;
    protected $siteNameShowuser;
    protected $siteNameStr;
    protected $pathHome;
    protected $minimumRoles;
    protected $roleAdmins;

    public function __construct() {
        $this->siteName = 'employees'; //controller is not setup yet, so we can't use $this->getParameter('employees.sitename');
        $this->siteNameShowuser = 'employees';
        $this->siteNameStr = 'Employee Directory';
        $this->pathHome = 'employees_home';
        $this->minimumRoles = array('ROLE_USERDIRECTORY_OBSERVER');
        $this->roleAdmins = array('ROLE_USERDIRECTORY_ADMIN');
    }

    /**
     * Lists all signUp entities.
     */
    #[Route(path: '/signup-list', name: 'employees_signup_index', methods: ['GET'])]
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:SignUp'] by [SignUp::class]
        $signUps = $em->getRepository(SignUp::class)->findAll();

        return $this->render('AppUserdirectoryBundle/SignUp/index.html.twig', array(
            'signUps' => $signUps,
            'title' => "Sign Up for ".$this->siteNameStr,
            'sitenamefull' => $this->siteNameStr,
            'sitename' => $this->siteName
        ));
    }

    /**
     * http://localhost/order/directory/sign-up
     * Creates a new signUp entity.
     */
    #[Route(path: '/sign-up', name: 'employees_signup_new', methods: ['GET', 'POST'])]
    public function newSignUpAction(Request $request)
    {

        $userSecUtil = $this->container->get('user_security_utility');
        $userServiceUtil = $this->container->get('user_service_utility');
        $systemEmail = $userSecUtil->getSiteSettingParameter('siteEmail');
        $em = $this->getDoctrine()->getManager();
        $signUp = new Signup();
        $form = $this->createForm('App\UserdirectoryBundle\Form\SignUpType', $signUp);
        $form->handleRequest($request);

        $password = $signUp->getHashPassword();
        //echo "password=$password<br>";

        if( $form->isSubmitted() ) {

            $passwordErrorCount = 0;
            if( !$password ) {
                $passwordErrorCount++;
            } else {
                //length
                if( strlen((string)$password) < '8' || strlen((string)$password) > '25' ) {
                    $passwordErrorCount++;
                }
                if( preg_match('/[A-Za-z]/', $password) && preg_match('/[0-9]/', $password) ) {
                    //echo 'Contains at least one letter and one number';
                } else {
                    //echo "No letter or number <br>";
                    $passwordErrorCount++;
                }
            }
            if( $passwordErrorCount > 0 ) {
                //echo "email error: $passwordErrorCount<br>";
                $passwordError = "Please make sure your password is between 8 and 25 characters and ".
                    "contains at least one letter and at least one number.";
                $form->get('hashPassword')->addError(new FormError($passwordError));
            }

            $usernameErrorCount = 0;
            if( !$signUp->getUserName() ) {
                $usernameErrorCount++;
            } else {
                if( strlen((string)$signUp->getUserName()) < '4' || strlen((string)$signUp->getUserName()) > '25' ) {
                    $usernameErrorCount++;
                }
                if( str_contains($signUp->getUserName(), ' ') ) {
                    $usernameErrorCount++;
                }
            }
            if( $usernameErrorCount > 0 ) {
                $usernameError = "Please make sure your user name contains at least 4 and at most 25 characters and does not contain empty spaces.";
                $form->get('userName')->addError(new FormError($usernameError));
            }

            if( !$signUp->getEmail() ) {
                //$form->get('email')->addError(new FormError('The email value should not be blank.')); validation is done in DB level
            } else {
                $emailHasError = false;
                //echo "email=".$signUp->getEmail()."<br>";
                //If the entered email address ends in “@med.cornell.edu” or “@nyp.org”
                if( strpos((string)$signUp->getEmail(), "@med.cornell.edu") !== false || strpos((string)$signUp->getEmail(), "@nyp.org") !== false ) {
                    $cwid = "CWID";
                    $emailArr = explode("@",$signUp->getEmail());
                    if( count($emailArr)>0 ) {
                        $cwid = $emailArr[0];
                    }
                    $emailError = "Since you entered an institutional e-mail address, you do not need to sign up for an account. ".
                                  "You can use your $cwid account and the associated password to log in.";
                    $form->get('email')->addError(new FormError($emailError));
                    $emailHasError = true;
                }

                //check if still active request and email or username existed in SignUp DB
                if( !$emailHasError ) {
                    $signUpDbs = $em->getRepository(SignUp::class)->findByEmail($signUp->getEmail());
                    if (count($signUpDbs) > 0) {
                        $signUpDb = $signUpDbs[0];
                        if ($signUpDb->getRegistrationStatus() == "Activation Email Sent") {
                            $createdDate = $signUpDb->getCreatedate();
                            //echo "createDate=".$createdDate->format("d-m-Y H:i:s")."<br>";
                            $now = new \DateTime();
                            //echo "now=".$now->format("d-m-Y H:i:s")."<br>";
                            $interval = $now->diff($createdDate);
                            $hours = $interval->h + ($interval->days * 24);
                            //echo "hours=".$hours."<br>";
                            if ($hours <= 24) {
                                //allow only 3 emails during 24 hours
                                if ($signUpDb->getEmailSentCounter() < 3) {
                                    $this->sendEmailWithActivationLink($signUpDb, $request);
                                    $confirmation =
                                        "An email was re-sent to the email address you provided " . $signUpDb->getEmail() .
                                        " with an activation link.<br>" .
                                        "Please click the link emailed to you to activate your account.";
                                    return $this->render('AppUserdirectoryBundle/SignUp/confirmation.html.twig',
                                        array(
                                            'title' => "Registration Confirmation",
                                            'messageSuccess' => $confirmation)
                                    );
                                } else {
                                    //exit('registration with this email is still active');
                                    $emailError = "A new account for this email address has been requested more than once in the past 24 hours." .
                                        " You should have received an email with an activation link to follow in order to complete your registration." .
                                        " If you do not see such an email message, please try signing up tomorrow.";
                                    $form->get('email')->addError(new FormError($emailError));
                                    $emailHasError = true;
                                }

                            }
                        }
                    }
                }

                //check if user with provided email existed in SignUp DB
                if( !$emailHasError ) {
                    $userDbs = $em->getRepository(User::class)->findUserByUserInfoEmail($signUp->getEmail());
                    //echo "usersDb=".count($userDbs)."<br>";
                    if (count($userDbs) > 0) {
                        $userDb = $userDbs[0];
                        if ($userDb->getLocked()) {
                            $emailError = "Your account is currently locked and you will not be able to log in until" .
                                " you contact the system administrator at " . $systemEmail . ".";
                        } else {
                            $orderUrl = $this->container->get('router')->generate(
                                $this->pathHome,
                                array(),
                                UrlGeneratorInterface::ABSOLUTE_URL
                            );
                            $orderUrl = ' <a href="' . $orderUrl . '">log in</a> ';

                            $resetUrl = $this->container->get('router')->generate(
                                $this->siteName . "_forgot_password",
                                array(),
                                UrlGeneratorInterface::ABSOLUTE_URL
                            );
                            $resetUrl = '<a href="' . $resetUrl . '">visit this page to receive an email with the password reset link</a>';

                            $emailError = "The email you entered has already been associated with an existing user account." .
                                " Either enter a different email address to continue signing up for a new account," .
                                " or " . $orderUrl . " using the user name and password of the existing account." .
                                " If you do not recall the user name and password associated with the existing account," .
                                " " . $resetUrl . ".";
                        }
                        $form->get('email')->addError(new FormError($emailError));
                        $emailHasError = true;
                    }
                }

            }

            if( $signUp->getUserName() && $usernameErrorCount == 0 ) {
                //When the user clicks “Sign Up”, search for matching existing user names
                // in the user table; if the user name is taken, show a red well stating
                // “This user name appears to be taken. Please choose another one.”
                $userDb = $em->getRepository(User::class)->findOneByPrimaryPublicUserId($signUp->getUserName());
                if( $userDb ) {
                    $form->get('userName')->addError(new FormError('This user name appears to be taken. Please choose another one.'));
                }
            }

            if( $userSecUtil->getSiteSettingParameter('captchaEnabled') === true ) {
                $captchaRes = $request->request->get('g-recaptcha-response');
                if( !$this->captchaValidate($request,$captchaRes) ) {
                    $form->get('recaptcha')->addError(new FormError('Captcha is required'));
                }
            }

        }//if submitted

        if( $form->isSubmitted() && $form->isValid() ) {

            //1)hash password
            //$salt = uniqid(mt_rand(), true);
            $salt = rtrim(str_replace('+', '.', base64_encode(random_bytes(32))), '=');
            //echo "salt=$salt<br>";
            $dummyUser = new User();
            $dummyUser->setSalt($salt);

            //$encoder = $this->container->get('security.password_encoder');
            //$encoded = $encoder->encodePassword($dummyUser,$password);
            $authUtil = $this->container->get('authenticator_utility');
            $encoded = $authUtil->getEncodedPassword($dummyUser,$password);
            //echo "encoded=$encoded<br>";
            $signUp->setSalt($salt);
            $signUp->setHashPassword($encoded);
            unset($dummyUser);

            //2) Generate unique REGISTRATION-LINK-ID
            $text = $signUp->getEmail().$signUp->getUserName().$signUp->getSalt();
            $registrationLinkId = $userServiceUtil->getUniqueRegistrationLinkId("SignUp",$text); //$signUp->getEmail());
            $signUp->setRegistrationLinkID($registrationLinkId);

            //sitename
            $siteObject = $em->getRepository(SiteList::class)->findOneByAbbreviation($this->siteName);
            if( $siteObject ) {
                $signUp->setSite($siteObject);
            }

            if( $request ) {
                $signUp->setUseragent($_SERVER['HTTP_USER_AGENT']);
                $signUp->setIp($request->getClientIp());
                $signUp->setWidth($request->get('display_width'));
                $signUp->setHeight($request->get('display_height'));
            }

            $firewallName = "ldap_".$this->siteName."_firewall";
            $indexLastRoute = '_security.'.$firewallName.'.target_path';
            $lastRoute = $request->getSession()->get($indexLastRoute);
            //echo "lastRoute=".$lastRoute."<br>";
            if( $lastRoute ) {
                $signUp->setLastUrl($lastRoute);
            }

            //exit('flush');
            $em->persist($signUp);
            //$em->flush($signUp);
            $em->flush();

            //Email
            $this->sendEmailWithActivationLink($signUp,$request);

            //Confirmation
            $confirmation = "Thank You for signing up!<br>
                An email was sent to the email address you provided ".$signUp->getEmail()." with a registration link.<br>
                Please click the link emailed to you to activate your account.";

            $confirmation = $confirmation .
                "<br><br>If you do not receive the confirmation message within".
                " a few minutes of signing up, please check your spam or junk mail ".
                "folder just in case the confirmation email got delivered there ".
                "instead of your inbox. If so, select the confirmation message ".
                "and click Not Junk/Spam, which will allow future messages to get through.";

            //return $this->redirectToRoute('employees_signup_show', array('id' => $signUp->getId()));
            return $this->render('AppUserdirectoryBundle/SignUp/confirmation.html.twig',
                array(
                    'title'=>"Registration Confirmation",
                    'messageSuccess'=>$confirmation)
            );
        }
        //exit('new');

        if( $userSecUtil->getSiteSettingParameter('captchaEnabled') === true ) {
            $captchaSiteKey = $userSecUtil->getSiteSettingParameter('captchaSiteKey');
        } else {
            $captchaSiteKey = null;
        }
        //echo "captchaSiteKey=[".$captchaSiteKey."]<br>";

        return $this->render('AppUserdirectoryBundle/SignUp/new.html.twig', array(
            'signUp' => $signUp,
            'form' => $form->createView(),
            'title' => "Sign Up for ".$this->siteNameStr,
            'sitenamefull' => $this->siteNameStr,
            'sitename' => $this->siteName,
            'captchaSiteKey' => $captchaSiteKey
        ));
    }
    public function sendEmailWithActivationLink($signUp,$request) {
        $emailUtil = $this->container->get('user_mailer_utility');
        $em = $this->getDoctrine()->getManager();
        $userSecUtil = $this->container->get('user_security_utility');

        $systemEmail = $userSecUtil->getSiteSettingParameter('siteEmail');
        $newline = "<br>";
        $subject = $this->siteNameStr." Registration";

        $orderUrl = $this->container->get('router')->generate(
            $this->pathHome,
            array(),
            UrlGeneratorInterface::ABSOLUTE_URL
        );

        //$activationUrl = ""; //http://URL/order/activate-account/REGISTRATION-LINK-ID
        $activationUrl = $this->container->get('router')->generate(
            $this->siteName.'_activate_account',
            array(
                'registrationLinkID'=>$signUp->getRegistrationLinkID()
            ),
            UrlGeneratorInterface::ABSOLUTE_URL
        );
        $activationUrl = '<a href="'.$activationUrl.'">'.$activationUrl.'</a>';

        $body =
            "Thank You for registering at ".$orderUrl." !".
            $newline.$newline."Please visit the following link to activate your account or copy/paste it into your browser’s address bar:".
            $newline.$activationUrl.
            $newline.$newline."If you encounter any issues, please email our system administrator at $systemEmail.";
        ;

        //                    $emails, $subject, $message, $ccs=null, $fromEmail=null
        $emailUtil->sendEmail($signUp->getEmail(), $subject, $body);

        //change status
        $signUp->setRegistrationStatus("Activation Email Sent");

        //update email counter
        $signUp->incrementEmailSentCounter();

        //$em->persist($signUp);
        //$em->flush($signUp);
        $em->flush();

        $signUpUser = $signUp->getUser();

        //Event Log
        $systemuser = $userSecUtil->findSystemUser();
        $event = "New user registration has been created:<br>".$signUp;
        $event = $event . "<br>Email Subject: " . $subject;
        $event = $event . "<br>Email Body:<br>" . $body;
        $userSecUtil->createUserEditEvent($this->siteName,$event,$systemuser,$signUpUser,$request,'Activation Email Sent');

        $res = array('subject'=>$subject,'body'=>$body);
        return $res;
    }

    //get success response from recaptcha and return it to controller
    //https://www.google.com/recaptcha/admin#site/341068506
    function captchaValidate($request,$recaptcha) {

        //return false; //Fatal error: Cannot declare class App\UserdirectoryBundle\Util\ReCaptchaResponse, because the name is already in use

        //dump($recaptcha);

        //$dir = $this->getParameter('kernel.root_dir'); //app
        //echo "dir=".$dir."<br>";
        //current dir is "C:\bla\bla\ORDER\scanorder\Scanorders2\app"
        //require_once "../src/App/UserdirectoryBundle/Util/RecaptchaLib.php";
        //include_once "../src/App/UserdirectoryBundle/Util/RecaptchaLib.php";

        $userSecUtil = $this->container->get('user_security_utility');
        $captchaSecretKey = $userSecUtil->getSiteSettingParameter('captchaSecretKey');

        $userIp = $request->getClientIp();

        $response = null;

        // check secret key
        $reCaptcha = new ReCaptcha($captchaSecretKey);

        $response = $reCaptcha->verifyResponse(
            //$_SERVER["REMOTE_ADDR"],
            $userIp,
            $recaptcha
        );

//        $response = $reCaptcha->verifyResponse(
//            $request,
//            $_SERVER["REMOTE_ADDR"],
//            $recaptcha,
//            $captchaSecretKey
//        );

        if( $response != null && $response->success ) {
            return true;
        }
        return false;
    }

    #[Route(path: '/activate-account/{registrationLinkID}', name: 'employees_activate_account', methods: ['GET', 'POST'])]
    public function activateAccountAction(Request $request, TokenStorageInterface $tokenStorage, $registrationLinkID)
    {
        //exit('1');
        $emailUtil = $this->container->get('user_mailer_utility');
        $userSecUtil = $this->container->get('user_security_utility');
        $em = $this->getDoctrine()->getManager();

        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:SignUp'] by [SignUp::class]
        $signUp = $em->getRepository(SignUp::class)->findOneByRegistrationLinkID($registrationLinkID);
        if( !$signUp ) {
            $confirmation = "This activation link is invalid. Please make sure you have copied it from your email message correctly.";
            return $this->render('AppUserdirectoryBundle/SignUp/confirmation.html.twig',
                array(
                    'title'=>"Invalid Activation Link",
                    'messageDanger'=>$confirmation
                )
            );
        }

        //If the activation link is visited more than 48 hours after the timestamp in the timestamp column,
        // show the following message on the page: “This activation link has expired. Please <sign up> again.”
        $createdDate = $signUp->getCreatedate();
        //echo "createDate=".$createdDate->format("d-m-Y H:i:s")."<br>";
        $now = new \DateTime();
        //echo "now=".$now->format("d-m-Y H:i:s")."<br>";
        $interval = $now->diff($createdDate);
        $hours = $interval->h + ($interval->days*24);
        //echo "hours=$hours <br>";
        if( $hours > 48 ) {
            $signupUrl = $this->container->get('router')->generate(
                $this->siteName."_signup_new",
                array(),
                UrlGeneratorInterface::ABSOLUTE_URL
            );
            $signupUrl = ' <a href="'.$signupUrl.'">sign up</a> ';
            $confirmation = "This activation link has expired. Please ".$signupUrl." again.";
            return $this->render('AppUserdirectoryBundle/SignUp/confirmation.html.twig',
                array(
                    'title'=>"Invalid Activation Link",
                    'messageDanger'=>$confirmation
                )
            );
        }

        //If the “Registration Status” of the Registration Link ID equals “Activated”,
        // show the following message: “This activation link has already been used. Please <log in> using your account.”
        if( $signUp->getRegistrationStatus() == "Activated" ) {
            $orderUrl = $this->container->get('router')->generate(
                $this->pathHome,
                array(),
                UrlGeneratorInterface::ABSOLUTE_URL
            );
            $orderUrl = ' <a href="'.$orderUrl.'">log in</a> ';
            $confirmation = "This activation link has already been used. Please ".$orderUrl." using your account.";
            return $this->render('AppUserdirectoryBundle/SignUp/confirmation.html.twig',
                array(
                    'title'=>"Invalid Activation Link",
                    'messageDanger'=>$confirmation
                )
            );
        }
        //exit('ok');

//        $rolesArr = array();
//        $params = array(
//            'cycle' => 'edit',
//            //'user' => $user,
//            'roles' => $rolesArr,
//            //'container' => $this->container,
//            'em' => $em
//        );
//        $form = $this->createForm('App\UserdirectoryBundle\Form\SignUpConfirmationType', $signUp, array(
//            'form_custom_value'=>$params
//        ));


        //1) only if not created yet: search by $signUp->getUserName() and if $signUp->getUser() is NULL
        $user = $em->getRepository(User::class)->findOneByPrimaryPublicUserId($signUp->getUserName());

        if( !$user ) {
            //check by email
            $emailCanonical = $this->canonicalize($signUp->getEmail());
            $user = $em->getRepository(User::class)->findOneByEmailCanonical($emailCanonical);
        }

        if( !$user ) {
            $emailCanonical = $this->canonicalize($signUp->getEmail());
            $users = $em->getRepository(User::class)->findUserByUserInfoEmail($emailCanonical);
            if (count($users) > 0) {
                $user = $users[0];
            }
        }

        //if( !$user ) { //&& !$signUp->getUser()
        if( $user || $signUp->getUser() ) {
            //user exists: don't create a new user
        } else {
            ///////////// create a new user ///////////////
            $publicUserId = $signUp->getUserName();
            $username = $publicUserId . "_@_" . "local-user";

            $user = $userSecUtil->constractNewUser($username); //publicUserId_@_ldap-user

            //create user info
            if( count($user->getInfos() ) == 0 ) {
                $userInfo = new UserInfo();
                $user->addInfo($userInfo);
            }

            //add site specific creation string
            //$createdBy = "Manually by Translational Research User";
            //$createdBy = "manual";
            $createdBy = "selfsignup-" . $this->siteName;
            $user->setCreatedby($createdBy);

            //$user->setOtherUserParam($otherUserParam);

            $user->setLocked(true);

            //add site minimum role
            $siteObject = $em->getRepository(SiteList::class)->findOneByAbbreviation($this->siteName);
            $lowestRoles = $siteObject->getLowestRoles();
            if( count($lowestRoles) == 0 ) {
                $lowestRoles = $this->minimumRoles;
            }
            foreach($lowestRoles as $role) {
                $user->addRole($role);
            }
            /////// add TRP minimum role ////////
            $trpSiteObject = $em->getRepository(SiteList::class)->findOneByAbbreviation('translationalresearch');
            $trpLowestRoles = $trpSiteObject->getLowestRoles();
            //if( count($trpLowestRoles) == 0 ) {
            //    $lowestRoles = $this->minimumRoles;
            //}
            foreach($trpLowestRoles as $role) {
                $user->addRole($role);
            }
            /////// EOF add TRP minimum role ////////

            //set passwordhash
            if ($signUp->getHashPassword()) {
                $user->setPassword($signUp->getHashPassword());
            }

            //set salt
            if ($signUp->getSalt()) {
                $user->setSalt($signUp->getSalt());
            }

            //set user info
            $userEmail = $signUp->getEmail();
            if ($userEmail) {
                $user->setEmail($userEmail);
                //$user->setEmailCanonical($userEmail);
            }

            //set administrativeTitles
            if (count($user->getAdministrativeTitles()) == 0) {
                $user->addAdministrativeTitle(new AdministrativeTitle($user));
            }

            //set user in SignUp
            $signUp->setUser($user);

            //Update the registration status” column in the “sign up list” table to “Activated”
            //$signUp->setRegistrationStatus("Activated");

            //delete registration link field in DB?

            //exit('before new user');
            $em->persist($signUp);
            $em->persist($user);
            $em->flush();
            ///////////// EOF create a new user ///////////////

            //$user = $em->getRepository(User::class)->find($user->getId());
            //$signUp = $em->getRepository('AppUserdirectoryBundle:SignUp')->findOneByRegistrationLinkID($registrationLinkID);

//            ////////////////////// auth /////////////////////////
//            // Authenticating user
//            $token = new UsernamePasswordToken($user, null, 'ldap_employees_firewall', $user->getRoles());
//            $this->container->get('security.token_storage')->setToken($token);
//            //For Symfony <= 2.3
//            //$this->container->get('security.context')->setToken($token);
//            $this->container->get('session')->set('_security_secured_area', serialize($token));
//            ////////////////////// EOF auth /////////////////////////
        }//if user is not created yet

        //$userAuth = $this->getUser();
        $userAuth = $this->getUser();
        if( $userAuth instanceof User) {
            //echo "already auth <br>";
        } else {
            ////////////////////// auth /////////////////////////
            // Authenticating user
            $token = new UsernamePasswordToken($user, 'ldap_employees_firewall', $user->getRoles());
            //$this->container->get('security.token_storage')->setToken($token);
            $tokenStorage->setToken($token);
            //For Symfony <= 2.3
            //$this->container->get('security.context')->setToken($token);
            //$this->container->get('session')->set('_security_secured_area', serialize($token));
            $request->getSession()->set('_security_secured_area', serialize($token));
            ////////////////////// EOF auth /////////////////////////
        }

        //$cycle = 'create';
        $cycle = 'edit';
        $rolesArr = array();
        $params = array(
            'cycle' => $cycle,
            //'cycle' => 'edit',
            'user' => $user,
            'cloneuser' => null,
            'roles' => $rolesArr,
            'container' => $this->container,
            'em' => $em,
            'hidePrimaryPublicUserId' => true,
            'activateBtn' => true
        );
        $form = $this->createForm(UserSimpleType::class, $user, array(
            'disabled' => false,
            //'action' => $this->generateUrl( $this->getParameter('employees.sitename').'_create_user' ),
            //'method' => 'POST',
            'form_custom_value' => $params,
        ));

        $form->handleRequest($request);

        if( $form->isSubmitted() && $form->isValid() ) {
            //Update the registration status” column in the “sign up list” table to “Activated”
            $signUp->setRegistrationStatus("Activated");

            //Set the account as “unlocked” and log in the user + send them to the “Employee Directory” homepage.
            $user->setLocked(false);

            //testing
//            foreach($user->getAdministrativeTitles() as $adminTitle) {
//                echo "Title=" . $adminTitle->getName() . "; Inst=" . $adminTitle->getInstitution() . "<br>";
//            }
//            foreach($user->getInfos() as $info) {
//                echo "email=" . $info->getEmail() . "; phone=" . $info->getPreferredPhone() . "<br>";
//            }

            //exit('1 before activate new user='.$user);
            $em->flush();

            //Event Log
            //$author = $this->getUser();
            $systemuser = $userSecUtil->findSystemUser();
            $event = "Successful Account Activation:<br>".$signUp;
            $userSecUtil = $this->container->get('user_security_utility');
            $userSecUtil->createUserEditEvent($this->siteName,$event,$systemuser,$user,$request,'Successful Account Activation');

//            $this->addFlash(
//                'notice',
//                "Your account has been successfully activated."
//            );

            //////////////// send email to admin //////////////////////
            $newline = "<br>";
            $emails = $userSecUtil->getUserEmailsByRole($this->siteName,"Administrator");
            $ccEmails = $userSecUtil->getUserEmailsByRole($this->siteName,"Platform Administrator");
            $adminEmails = $userSecUtil->getUserEmailsByRole($this->siteName,null,$this->roleAdmins);
            if( $ccEmails ) {
                $emails = array_merge($emails, $ccEmails);
            }
            if( $adminEmails ) {
                $emails = array_merge($emails, $adminEmails);
            }
            $emails = array_unique($emails);
            //echo "user emails=".implode(";",$emails)."<br>";
            $subject = "New ".$signUp->getUser()." account added on the ".$this->siteNameStr." site";

            $userDetalsArr = $signUp->getUser()->getDetailsArr();
            $titleArr = array();
            $titleArr[] = $userDetalsArr["title"];
            $titleArr[] = $userDetalsArr["institution"];

            $body =
                "New user has been created and given access to the ".$this->siteNameStr." site:".
                $newline.$signUp->getUser().", ".implode(", ",$titleArr);
            ;
            //                    $emails, $subject, $message, $ccs=null, $fromEmail=null
            $emailUtil->sendEmail($emails, $subject, $body); //testing
//            $this->addFlash(
//                'notice',
//                "subject=".$subject . "; body=" . $body
//            );
            //////////////// EOF send email to admin //////////////////////

            //TODO: Logout user after creating new user
            $this->addFlash(
                'notice',
                "Your account has been successfully activated. Please re-login."
            );
            return $this->redirect($this->generateUrl($this->siteName . '_logout'));

//            //redirect to intended Url
//            $redirectUrl = $signUp->getLastUrl();
//            if( $redirectUrl ) {
//                return $this->redirect($redirectUrl);
//            } else {
//                //send them to the homepage.
//                return $this->redirectToRoute($this->pathHome);
//            }
        }
        //exit('new');

        return $this->render('AppUserdirectoryBundle/SignUp/activation.html.twig', array(
            'signUp' => $signUp,
            'user' => $user,
            'cycle' => $cycle,
            'form' => $form->createView(),
            'title' => "Activate Account for ".$this->siteNameStr,
            'sitenamefull' => $this->siteNameStr,
            'sitename' => $this->siteName
        ));



    }

    /**
     * Finds and displays a signUp entity.
     */
    #[Route(path: '/signup-show/{id}', name: 'employees_signup_show', methods: ['GET'])]
    public function showAction(SignUp $signUp)
    {
        $deleteForm = $this->createDeleteForm($signUp);

        $form = $this->createForm('App\UserdirectoryBundle\Form\SignUpType', $signUp, array(
            'disabled' => true
        ));
        //$form->handleRequest($request);

        return $this->render('AppUserdirectoryBundle/SignUp/new.html.twig', array(
            'signUp' => $signUp,
            'title' => "Sign Up for ".$this->siteNameStr,
            'sitenamefull' => $this->siteNameStr,
            'sitename' => $this->siteName,
            'form' => $form->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing signUp entity.
     */
    #[Route(path: '/signup-edit/{id}', name: 'employees_signup_edit', methods: ['GET', 'POST'])]
    public function editAction(Request $request, SignUp $signUp)
    {
        $deleteForm = $this->createDeleteForm($signUp);
        $form = $this->createForm('App\UserdirectoryBundle\Form\SignUpType', $signUp, array(
            'disabled' => true
        ));
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute($this->siteName.'_signup_edit', array('id' => $signUp->getId()));
        }

        return $this->render('AppUserdirectoryBundle/SignUp/new.html.twig', array(
            'signUp' => $signUp,
            'title' => "Sign Up for ".$this->siteNameStr,
            'sitenamefull' => $this->siteNameStr,
            'sitename' => $this->siteName,
            'form' => $form->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Deletes a signUp entity.
     */
    #[Route(path: '/signup-delete/{id}', name: 'employees_signup_delete', methods: ['DELETE'])]
    public function deleteAction(Request $request, SignUp $signUp)
    {
        $form = $this->createDeleteForm($signUp);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($signUp);
            $em->flush();
        }

        return $this->redirectToRoute($this->siteName.'_signup_index');
    }

    /**
     * Creates a form to delete a signUp entity.
     *
     * @param SignUp $signUp The signUp entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(SignUp $signUp)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl($this->siteName.'_signup_delete', array('id' => $signUp->getId())))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }



    //create empty collections
    public function addEmptyCollections($entity,$user=null) {

        $userSecUtil = $this->container->get('user_security_utility');
        $em = $this->getDoctrine()->getManager();

        if( !$user ) {
            $user = $this->getUser();
        }

        if( count($entity->getAdministrativeTitles()) == 0 ) {
            $entity->addAdministrativeTitle(new AdministrativeTitle($user));
        }

        if( count($entity->getAppointmentTitles()) == 0 ) {
            $entity->addAppointmentTitle(new AppointmentTitle($user));
            //echo "app added, type=".$appointmentTitle->getType()."<br>";
        }

        if( count($entity->getMedicalTitles()) == 0 ) {
            $entity->addMedicalTitle(new MedicalTitle($user));
        }

        //state license
        $stateLicenses = $entity->getCredentials()->getStateLicense();
        if( count($stateLicenses) == 0 ) {
            $entity->getCredentials()->addStateLicense( new StateLicense() );
        }
        //make sure state license has attachmentContainer
        foreach( $stateLicenses as $stateLicense ) {
            $stateLicense->createAttachmentDocument();
        }

        //board certification
        $boardCertifications = $entity->getCredentials()->getBoardCertification();
        if( count($boardCertifications) == 0 ) {
            $entity->getCredentials()->addBoardCertification( new BoardCertification() );
        }
        //make sure board certification has attachmentContainer
        foreach( $boardCertifications as $boardCertification ) {
            $boardCertification->createAttachmentDocument();
        }

        if( count($entity->getEmploymentStatus()) == 0 ) {
            $entity->addEmploymentStatus(new EmploymentStatus($user));
        }

        $pathology = $userSecUtil->getAutoAssignInstitution();
        if( !$pathology ) {
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:Institution'] by [Institution::class]
            $wcmc = $em->getRepository(Institution::class)->findOneByAbbreviation("WCM");
            if (!$wcmc) {
                //exit('No Institution: "WCM"');
                throw $this->createNotFoundException('No Institution: "WCM"');
            }
            $mapper = array(
                'prefix' => 'App',
                'bundleName' => 'UserdirectoryBundle',
                'className' => 'Institution',
                'fullClassName' => "App\\UserdirectoryBundle\\Entity\\Institution",
                'entityNamespace' => "App\\UserdirectoryBundle\\Entity"
            );
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:Institution'] by [Institution::class]
            $pathology = $em->getRepository(Institution::class)->findByChildnameAndParent(
                "Pathology and Laboratory Medicine",
                $wcmc,
                $mapper
            );
        }
        if( !$pathology ) {
            //exit('No Institution: "Pathology and Laboratory Medicine"');
            throw $this->createNotFoundException('No Institution: "Pathology and Laboratory Medicine"');
        }

        //check if Institution is assign
        foreach( $entity->getEmploymentStatus() as $employmentStatus ) {
            $employmentStatus->createAttachmentDocument();
            //echo "employ inst=".$employmentStatus->getInstitution()."<br>";
            if( !$employmentStatus->getInstitution() ) {

                $employmentStatus->setInstitution($pathology);
            }
        }

        //create new comments
        if( count($entity->getPublicComments()) == 0 ) {
            $entity->addPublicComment( new PublicComment($user) );
        }
        if( $this->isGranted('ROLE_PLATFORM_DEPUTY_ADMIN') || $this->isGranted('ROLE_USERDIRECTORY_EDITOR') ||
            $entity->getId() && $entity->getId() == $user->getId()
        ) {
            if( count($entity->getPrivateComments()) == 0 ) {
                $entity->addPrivateComment( new PrivateComment($user) );
            }
        }
        if( $this->isGranted('ROLE_PLATFORM_DEPUTY_ADMIN') || $this->isGranted('ROLE_USERDIRECTORY_EDITOR') ) {
            if( count($entity->getAdminComments()) == 0 ) {
                $entity->addAdminComment( new AdminComment($user) );
            }
        }
        if( $this->isGranted('ROLE_PLATFORM_DEPUTY_ADMIN') || $this->isGranted('ROLE_USERDIRECTORY_EDITOR') ) {
            if( count($entity->getConfidentialComments()) == 0 ) {
                $entity->addConfidentialComment( new ConfidentialComment($user) );
            }
        }

        if( count($entity->getResearchLabs()) == 0 ) {
            $entity->addResearchLab(new ResearchLab($user));
        }

        if( count($entity->getGrants()) == 0 ) {
            $entity->addGrant(new Grant($user));
        }
        //check if has attachemntDocument and at least one DocumentContainers
        foreach( $entity->getGrants() as $grant ) {
            $grant->createAttachmentDocument();
        }

        if( count($entity->getTrainings()) == 0 ) {
            $entity->addTraining(new Training($user));
        }

        if( count($entity->getPublications()) == 0 ) {
            $entity->addPublication(new Publication($user));
        }

        if( count($entity->getBooks()) == 0 ) {
            $entity->addBook(new Book($user));
        }

        if( count($entity->getLectures()) == 0 ) {
            $entity->addLecture(new Lecture($user));
        }

        //Identifier EIN
//        if( count($entity->getCredentials()->getIdentifiers()) == 0 ) {
//            $entity->getCredentials()->addIdentifier( new Identifier() );
//        }

        //make sure coqAttachmentContainer, cliaAttachmentContainer exists
        $entity->getCredentials()->createAttachmentDocument();

    }












    /**
     * http://localhost/order/directory/forgot-password
     */
    #[Route(path: '/forgot-password', name: 'employees_forgot_password', methods: ['GET', 'POST'])]
    public function forgotPasswordAction(Request $request)
    {
        //exit('Not Implemented Yet');
        $userSecUtil = $this->container->get('user_security_utility');
        $userServiceUtil = $this->container->get('user_service_utility');
        $systemEmail = $userSecUtil->getSiteSettingParameter('siteEmail');
        $em = $this->getDoctrine()->getManager();

        $userDb = null;
        $resetPassword = new ResetPassword();
        $form = $this->createForm('App\UserdirectoryBundle\Form\ForgotPasswordType', $resetPassword);
        $form->handleRequest($request);

        if( $form->isSubmitted() ) {

            if( !$resetPassword->getEmail() ) {
                //$form->get('email')->addError(new FormError('The email value should not be blank.'));
            } else {
                //If the entered email address ends in “@med.cornell.edu” or “@nyp.org”
                if( strpos((string)$resetPassword->getEmail(), "@med.cornell.edu") !== false || strpos((string)$resetPassword->getEmail(), "@nyp.org") !== false ) {
                    $cwid = "CWID";
                    $emailArr = explode("@",$resetPassword->getEmail());
                    if( count($emailArr)>0 ) {
                        $cwid = $emailArr[0];
                    }
//                    $enterpriseManagementUrl = ' <a href="https://its.weill.cornell.edu/services/accounts-and-access/password-management">enterprise password management</a> ';
//                    $emailError = "The password for your $cwid can only be changed or reset by visiting the
//                        $enterpriseManagementUrl page or by calling the help desk at ‭1 (212) 746-4878‬.";
                    //SiteSettings: get from site settings "Notice for attempting to reset password for an LDAP-authenticated account."
                    $emailError = $userSecUtil->getSiteSettingParameter('noticeAttemptingPasswordResetLDAP');
                    $emailError = str_replace("[[CWID]]",$cwid,$emailError);
                    $form->get('email')->addError(new FormError($emailError));
                }

                //check if still active request and email or username existed in ResetPassword DB
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:ResetPassword'] by [ResetPassword::class]
                $resetPasswordDbs = $em->getRepository(ResetPassword::class)->findByEmail($resetPassword->getEmail());
                if( count($resetPasswordDbs) > 0 ) {
                    $resetPasswordDb = $resetPasswordDbs[0];
                    if ($resetPasswordDb->getRegistrationStatus() == "Password Reset Email Sent") {
                        $createdDate = $resetPasswordDb->getCreatedate();
                        //echo "createDate=".$createdDate->format("d-m-Y H:i:s")."<br>";
                        $now = new \DateTime();
                        //echo "now=".$now->format("d-m-Y H:i:s")."<br>";
                        $interval = $now->diff($createdDate);
                        $hours = $interval->h + ($interval->days * 24);
                        //echo "hours=".$hours."<br>";
                        if( $hours <= 24 ) {
                            //allow only 3 emails during 24 hours
                            if( $resetPasswordDb->getEmailSentCounter() < 3 ) {
                                $this->sendEmailWithResetLink($resetPasswordDb,$request);
                                $confirmation =
                                    "An email was re-sent to the email address you provided ".$resetPasswordDb->getEmail().
                                    " with a password reset link.<br>".
                                    "Please click the link emailed to you to reset your password.";
                                return $this->render('AppUserdirectoryBundle/SignUp/confirmation.html.twig',
                                    array(
                                        'title'=>"Password Reset Confirmation",
                                        'messageSuccess'=>$confirmation)
                                );
                            } else {
                                //exit('password reset with this email is still active');
                                $emailError = "The password reset has been requested more than once for this account in the last 24 hours.".
                                    " You should have received an email with with a link to follow in order to reset your password.".
                                    " If you do not see such an email message, please try resetting this password tomorrow.";
                                $form->get('email')->addError(new FormError($emailError));
                            }

                        }
                    }
                }

                //check if user with provided email existed in resetPassword DB
                $text = $resetPassword->getEmail();
                $user = $resetPassword->getUser();
                if( $user ) {
                    $text = $text . $user->getPrimaryPublicUserId();
                }
                $userDbs = $em->getRepository(User::class)->findUserByUserInfoEmail($text);
                //echo "usersDb=".count($userDbs)."<br>";
                if( count($userDbs) > 0 ) {
                    $userDb = $userDbs[0];
                    if( $userDb->getLocked() ) {
                        $emailError = "Your account is currently locked and you will not be able to log in until".
                            " you contact the system administrator at ".$systemEmail.".";
                        $form->get('email')->addError(new FormError($emailError));
                    }
                }
                if( count($userDbs) == 0 ) {
//                    $signupUrl = $this->container->get('router')->generate(
//                        $this->siteName."_signup_new",
//                        array(),
//                        UrlGeneratorInterface::ABSOLUTE_URL
//                    );
//                    $signupUrl = ' <a href="'.$signupUrl.'">sign up for a new account</a> ';
                    //$emailError = "The email address you have entered is not associated with any active accounts.".
                    //    " Please $signupUrl or enter a different email address above.";
                    $emailError = $this->confirmationForgotPassword();
                    $form->get('email')->addError(new FormError($emailError));
                }
            }
        }//if submitted

        if( $form->isSubmitted() && $form->isValid() ) {

            //1) Generate unique REGISTRATION-LINK-ID
            $resetPasswordLinkID = $userServiceUtil->getUniqueRegistrationLinkId("ResetPassword",$resetPassword->getEmail());
            $resetPassword->setRegistrationLinkID($resetPasswordLinkID);

            //sitename
            $siteObject = $em->getRepository(SiteList::class)->findOneByAbbreviation($this->siteName);
            if( $siteObject ) {
                $resetPassword->setSite($siteObject);
            }

            if( $request ) {
                $resetPassword->setUseragent($_SERVER['HTTP_USER_AGENT']);
                $resetPassword->setIp($request->getClientIp());
                $resetPassword->setWidth($request->get('display_width'));
                $resetPassword->setHeight($request->get('display_height'));
            }

            //exit('flush');
            $em->persist($resetPassword);
            //$em->flush($resetPassword);
            $em->flush();

            //Email
            $this->sendEmailWithResetLink($resetPassword,$request);
//            $subject = $resSent['subject'];
//            $body = $resSent['body'];
//
//            //Event Log
//            //$author = $this->getUser();
//            $systemuser = $userSecUtil->findSystemUser();
//            $event = "ORDER Password reset link has been used for ".$this->siteNameStr." for:<br>".$resetPassword;
//            $event = $event . "<br>Email Subject: " . $subject;
//            $event = $event . "<br>Email Body:<br>" . $body;
//            $userSecUtil->createUserEditEvent($this->siteName,$event,$systemuser,$resetPassword,$request,'Password Reset Created');

            //Confirmation
            //$confirmation =
            //    "An email was sent to the email address you provided ".$resetPassword->getEmail()." with a password reset link.<br>
            //    Please click the link emailed to you to reset your password.";
            $confirmation = $this->confirmationForgotPassword();
            return $this->render('AppUserdirectoryBundle/SignUp/confirmation.html.twig',
                array(
                    'title'=>"Password Reset Confirmation",
                    'messageSuccess'=>$confirmation)
            );
        }
        //exit('new');

        return $this->render('AppUserdirectoryBundle/SignUp/forgot-password.html.twig', array(
            'resetPassword' => $resetPassword,
            'form' => $form->createView(),
            'title' => "ORDER Password Reset",
            'sitenamefull' => $this->siteNameStr,
            'sitename' => $this->siteName
        ));
    }
    public function sendEmailWithResetLink($resetPassword,$request) {
        $emailUtil = $this->container->get('user_mailer_utility');
        $em = $this->getDoctrine()->getManager();
        $userSecUtil = $this->container->get('user_security_utility');

        $systemEmail = $userSecUtil->getSiteSettingParameter('siteEmail');

        //Email
        $newline = "<br>";
        $subject = "ORDER Password reset link";

        //$activationUrl = ""; //http://URL/order/activate-account/REGISTRATION-LINK-ID
        $resetPasswordUrl = $this->container->get('router')->generate(
            $this->siteName.'_reset_password',
            array(
                'resetPasswordLinkID'=>$resetPassword->getRegistrationLinkID()
            ),
            UrlGeneratorInterface::ABSOLUTE_URL
        );

        $body =
            "Please visit the following link to reset your password or copy/paste it into your browser’s address bar:".
            $newline.$resetPasswordUrl.
            $newline."If you encounter any issues, please email our system administrator at $systemEmail.";
        ;

        //                    $emails, $subject, $message, $ccs=null, $fromEmail=null
        $emailUtil->sendEmail($resetPassword->getEmail(), $subject, $body);

        //change status
        $resetPassword->setRegistrationStatus("Password Reset Email Sent");

        //update email counter
        $resetPassword->incrementEmailSentCounter();

        //$em->persist($signUp);
        //$em->flush($resetPassword);
        $em->flush();

        $signUpUser = $resetPassword->getUser();

        //Event Log
        //$author = $this->getUser();
        $systemuser = $userSecUtil->findSystemUser();
        $event = "ORDER Password reset link has been used for ".$this->siteNameStr." for:<br>".$resetPassword;
        $event = $event . "<br>Email Subject: " . $subject;
        $event = $event . "<br>Email Body:<br>" . $body;
        $userSecUtil->createUserEditEvent($this->siteName,$event,$systemuser,$signUpUser,$request,'Password Reset Email Sent');

        $res = array('subject'=>$subject,'body'=>$body);
        return $res;
    }
    public function confirmationForgotPassword() {
        $signupUrl = $this->container->get('router')->generate(
            $this->siteName."_signup_new",
            array(),
            UrlGeneratorInterface::ABSOLUTE_URL
        );
        $signupUrl = ' <a href="'.$signupUrl.'">sign up for a new account</a> ';
        $confirmation = "If you have provided a valid email address associated with a user of this site,".
            " you should receive an email message with a link to a page where you can reset the password.".
            " Please note that it might take up to 15 minutes to receive this email.".
            " If you have not received such an email, you can request another or ".$signupUrl.".";

        return $confirmation;
    }

    /**
     * http://localhost/order/directory/reset-password/d902aea8d5bdc7141
     */
    #[Route(path: '/reset-password/{resetPasswordLinkID}', name: 'employees_reset_password', methods: ['GET', 'POST'])]
    public function resetPasswordAction(Request $request, $resetPasswordLinkID)
    {
        //exit("reset password by resetPasswordLinkID=".$resetPasswordLinkID);

        //$emailUtil = $this->container->get('user_mailer_utility');
        //$userServiceUtil = $this->container->get('user_service_utility');
        $userSecUtil = $this->container->get('user_security_utility');
        $em = $this->getDoctrine()->getManager();

        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:ResetPassword'] by [ResetPassword::class]
        $resetPassword = $em->getRepository(ResetPassword::class)->findOneByRegistrationLinkID($resetPasswordLinkID);
        if( !$resetPassword ) {
            $confirmation = "This reset password link is invalid. Please make sure you have copied it from your email message correctly.";
            //exit($confirmation);
            return $this->render('AppUserdirectoryBundle/SignUp/confirmation.html.twig',
                array(
                    'title'=>"Invalid Reset Password Link",
                    'messageDanger'=>$confirmation
                )
            );
        }

        //If the activation link is visited more than 48 hours after the timestamp in the timestamp column,
        // show the following message on the page: “This activation link has expired. Please <sign up> again.”
        $createdDate = $resetPassword->getCreatedate();
        //echo "createDate=".$createdDate->format("d-m-Y H:i:s")."<br>";
        $now = new \DateTime();
        //echo "now=".$now->format("d-m-Y H:i:s")."<br>";
        $interval = $now->diff($createdDate);
        $hours = $interval->h + ($interval->days*24);
        //echo "hours=$hours <br>";
        if( $hours > 48 ) {
            $resetPasswordUrl = $this->container->get('router')->generate(
                $this->siteName."_forgot_password",
                array(),
                UrlGeneratorInterface::ABSOLUTE_URL
            );
            $resetPasswordUrl = ' <a href="'.$resetPasswordUrl.'">reset password</a> ';
            $confirmation = "This reset password link has expired. Please ".$resetPasswordUrl." again.";
            //exit($confirmation);
            return $this->render('AppUserdirectoryBundle/SignUp/confirmation.html.twig',
                array(
                    'title'=>"Invalid Reset Password Link",
                    'messageDanger'=>$confirmation
                )
            );
        }

        //If the “Registration Status” of the Registration Link ID equals “Reset”,
        // show the following message: “This activation link has already been used. Please <log in> using your account.”
        if( $resetPassword->getRegistrationStatus() == "Reset" ) {
            $orderUrl = $this->container->get('router')->generate(
                $this->pathHome,
                array(),
                UrlGeneratorInterface::ABSOLUTE_URL
            );
            $orderUrl = ' <a href="'.$orderUrl.'">log in</a> ';
            $confirmation = "This password reset link has already been used.".
                " Please ".$orderUrl." using your new password.";
            //exit($confirmation);
            return $this->render('AppUserdirectoryBundle/SignUp/confirmation.html.twig',
                array(
                    'title'=>"Invalid Reset Password Link",
                    'messageDanger'=>$confirmation
                )
            );
        }
        //exit('ok');s

        //1) only if not created yet: search by $signUp->getUserName() and if $signUp->getUser() is NULL
        $users = $em->getRepository(User::class)->findUserByUserInfoEmail($resetPassword->getEmail());
        if( count($users) == 0 ) {
            $confirmation = "The email address you have entered is not associated with any active accounts."; //keep this for reset password by linkID
            //exit($confirmation);
            return $this->render('AppUserdirectoryBundle/SignUp/confirmation.html.twig',
                array(
                    'title'=>"Invalid Reset Password Link",
                    'messageDanger'=>$confirmation
                )
            );
        }//if user is not created yet

        $user = null;
        if( count($users) > 0 ) {
            $user = $users[0];
        } else {
            $confirmation = "Logical Error: User not found by email ".$resetPassword->getEmail();
            //exit($confirmation);
            return $this->render('AppUserdirectoryBundle/SignUp/confirmation.html.twig',
                array(
                    'title'=>"Invalid Reset Password Link",
                    'messageDanger'=>$confirmation
                )
            );
        }

//        $userAuth = $this->getUser();
//        if( $userAuth instanceof User) {
//            //already authenticated
//            //exit('already auth');
//        } else {
//            //exit('before auth');
//            ////////////////////// auth /////////////////////////
//            $token = new UsernamePasswordToken($user, null, 'ldap_employees_firewall', $user->getRoles());
//            $this->container->get('security.token_storage')->setToken($token);
//            $this->container->get('session')->set('_security_secured_area', serialize($token));
//            ////////////////////// EOF auth /////////////////////////
//        }

        //$cycle = 'create';
        $cycle = 'edit';
        $params = array(
            'cycle' => $cycle,
            'user' => $user,
        );
        $form = $this->createForm(ResetPasswordType::class, $user, array(
            'disabled' => false,
            'form_custom_value' => $params,
        ));

        $form->handleRequest($request);

        $password = $user->getPassword();
        //echo "password=".$password."<br>";

        if( $form->isSubmitted() ) {

            $passwordErrorCount = 0;
            if( !$password ) {
                $passwordErrorCount++;
            } else {
                //length
                if( strlen((string)$password) < '8' || strlen((string)$password) > '25' ) {
                    $passwordErrorCount++;
                }
                if( preg_match('/[A-Za-z]/', $password) && preg_match('/[0-9]/', $password) ) {
                    //echo 'Contains at least one letter and one number';
                } else {
                    //echo "No letter or number <br>";
                    $passwordErrorCount++;
                }
            }
            if( $passwordErrorCount > 0 ) {
                //echo "email error: $passwordErrorCount<br>";
                $passwordError = "Please make sure your password is between 8 and 25 characters and ".
                    "contains at least one letter and at least one number.";
                //$form->get('password')->addError(new FormError($passwordError));
                $form->get('password')->get('first')->addError(new FormError($passwordError));
            }
            //exit('1');
        }//if submitted


        if( $form->isSubmitted() && $form->isValid() ) {

            //Update the registration status” column in the “sign up list” table to “Reset”
            $resetPassword->setRegistrationStatus("Reset");

            //get password hash
            //$encoder = $this->container->get('security.password_encoder');
            //$encodedPassword = $encoder->encodePassword($user,$password);
            $authUtil = $this->container->get('authenticator_utility');
            $encodedPassword = $authUtil->getEncodedPassword($user,$password);
            //echo "encodedPassword=".$encodedPassword."<br>";
            $user->setPassword($encodedPassword);

            //set user in SignUp
            $resetPassword->setUser($user);

            //exit('reset flush');
            //$em->flush($resetPassword);
            //$em->flush($user);
            $em->flush();

            //Event Log
            //$author = $this->getUser();
            $systemuser = $userSecUtil->findSystemUser();
            $event = "Successful Password Reset:<br>".$resetPassword;
            $userSecUtil = $this->container->get('user_security_utility');
            $userSecUtil->createUserEditEvent($this->siteName,$event,$systemuser,$user,$request,'Successful Password Reset');

            $this->addFlash(
                'notice',
                "Your password has been successfully reset."
            );

            //send them to the “Employee Directory” homepage.
            return $this->redirectToRoute($this->siteName . '_login');
        }
        //exit('reset form');

        return $this->render('AppUserdirectoryBundle/SignUp/reset-password-action.html.twig', array(
            'resetPassword' => $resetPassword,
            'user' => $user,
            'cycle' => $cycle,
            'form' => $form->createView(),
            'title' => "Reset Password",
            'sitenamefull' => $this->siteNameStr,
            'sitename' => $this->siteName
        ));
    }

    public function canonicalize($string)
    {
        if (null === $string) {
            return null;
        }

        $encoding = mb_detect_encoding($string);
        $result = $encoding
            ? mb_convert_case($string, MB_CASE_LOWER, $encoding)
            : mb_convert_case($string, MB_CASE_LOWER);

        return $result;
    }

}
