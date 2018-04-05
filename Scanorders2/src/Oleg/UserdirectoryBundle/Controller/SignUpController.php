<?php

namespace Oleg\UserdirectoryBundle\Controller;

use Oleg\UserdirectoryBundle\Entity\SignUp;
use Oleg\UserdirectoryBundle\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Signup controller.
 *
 * @Route("sign-up")
 */
class SignUpController extends Controller
{

    protected $router;
    protected $siteName;
    protected $siteNameShowuser;
    protected $siteNameStr;
    protected $pathHome;

    public function __construct() {
        $this->siteName = 'employees'; //controller is not setup yet, so we can't use $this->container->getParameter('employees.sitename');
        $this->siteNameShowuser = 'employees';
        $this->siteNameStr = 'Employee Directory';
        $this->pathHome = 'employees_home';
    }

    /**
     * Lists all signUp entities.
     *
     * @Route("/", name="employees_signup_index")
     * @Method("GET")
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $signUps = $em->getRepository('OlegUserdirectoryBundle:SignUp')->findAll();

        return $this->render('OlegUserdirectoryBundle:SignUp:index.html.twig', array(
            'signUps' => $signUps,
            'title' => "Sign Up for ".$this->siteNameStr,
            'sitenamefull' => $this->siteNameStr,
            'sitename' => $this->siteName
        ));
    }

    /**
     * http://localhost/order/directory/sign-up/new
     * Creates a new signUp entity.
     *
     * @Route("/new", name="employees_signup_new")
     * @Method({"GET", "POST"})
     */
    public function newSignUpAction(Request $request)
    {

        $userSecUtil = $this->get('user_security_utility');
        $userServiceUtil = $this->get('user_service_utility');
        $em = $this->getDoctrine()->getManager();
        $signUp = new Signup();
        $form = $this->createForm('Oleg\UserdirectoryBundle\Form\SignUpType', $signUp);
        $form->handleRequest($request);

        $password = $signUp->getHashPassword();
        echo "password=$password<br>";

        if( $form->isSubmitted() ) {

            $passwordErrorCount = 0;
            if( !$password ) {
                $passwordErrorCount++;
            } else {
                //length
                if( strlen($password) < '8' || strlen($password) > '25' ) {
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
                echo "email error: $passwordErrorCount<br>";
                $passwordError = "Please make sure your password is between 8 and 25 characters and ".
                    "contains at least one letter and at least one number.";
                $form->get('hashPassword')->addError(new FormError($passwordError));
            }

            $usernameErrorCount = 0;
            if( !$signUp->getUserName() ) {
                $usernameErrorCount++;
            } else {
                if( strlen($signUp->getUserName()) < '8' || strlen($signUp->getUserName()) > '25' ) {
                    $usernameErrorCount++;
                }
            }
            if( $usernameErrorCount > 0 ) {
                $usernameError = "Please make sure your user name contains at least 8 and at most 25 characters.";
                $form->get('userName')->addError(new FormError($usernameError));
            }

            if( !$signUp->getEmail() ) {
                //$form->get('email')->addError(new FormError('The email value should not be blank.'));
            } else {
                //If the entered email address ends in “@med.cornell.edu” or “@nyp.org”
                if( strpos($signUp->getEmail(), "@med.cornell.edu") !== false || strpos($signUp->getEmail(), "@nyp.org") !== false ) {
                    $cwid = "CWID";
                    $emailArr = explode("@",$signUp->getEmail());
                    if( count($emailArr)>0 ) {
                        $cwid = $emailArr[0];
                    }
                    $emailError = "Since you entered an institutional e-mail address, you do not need to sign up for an account. ".
                                  "You can use your $cwid and the associated password to log in.";
                    $form->get('email')->addError(new FormError($emailError));
                }

//                $userDb = $em->getRepository('OlegUserdirectoryBundle:User')->findOneByEmailCanonical($signUp->getEmail());
//                if( !$userDb ) {
//                    $form->get('email')->addError(new FormError('This user email appears to be taken. Please choose another one.'));
//                }
            }

            if( $signUp->getUserName() && $usernameErrorCount == 0 ) {
                //When the user clicks “Sign Up”, search for matching existing user names
                // in the user table; if the user name is taken, show a red well stating
                // “This user name appears to be taken. Please choose another one.”
                $userDb = $em->getRepository('OlegUserdirectoryBundle:User')->findOneByPrimaryPublicUserId($signUp->getUserName());
                if( $userDb ) {
                    $form->get('userName')->addError(new FormError('This user name appears to be taken. Please choose another one.'));
                }
            }
        }

        if( $form->isSubmitted() && $form->isValid() ) {

            //1)hash password
            //$salt = uniqid(mt_rand(), true);
            $salt = rtrim(str_replace('+', '.', base64_encode(random_bytes(32))), '=');
            echo "salt=$salt<br>";
            $encoder = $this->container->get('security.password_encoder');
            $dummyUser = new User();
            $dummyUser->setSalt($salt);
            $encoded = $encoder->encodePassword($dummyUser,$password);
            echo "encoded=$encoded<br>";
            $signUp->setSalt($salt);
            $signUp->setHashPassword($encoded);
            unset($dummyUser);

            //2) Generate unique REGISTRATION-LINK-ID
            $registrationLinkId = $userServiceUtil->getUniqueRegistrationLinkId($signUp->getEmail());
            $signUp->setRegistrationLinkID($registrationLinkId);

            //sitename
            $siteObject = $em->getRepository('OlegUserdirectoryBundle:SiteList')->findOneByAbbreviation($this->siteName);
            if( $siteObject ) {
                $signUp->setSite($siteObject);
            }

            if( $request ) {
                $signUp->setUseragent($_SERVER['HTTP_USER_AGENT']);
                $signUp->setIp($request->getClientIp());
                $signUp->setWidth($request->get('display_width'));
                $signUp->setHeight($request->get('display_height'));
            }

            //exit('flush');
            $em->persist($signUp);
            $em->flush($signUp);

            //Event Log
            //$author = $this->get('security.token_storage')->getToken()->getUser();
            $systemuser = $userSecUtil->findSystemUser();
            $event = "New user registration has been created:<br>".$signUp;
            $userSecUtil->createUserEditEvent($this->siteName,$event,$systemuser,$signUp,$request,'User SignUp Created');

            //Email
            $newline = "\r\n";
            $emailUtil = $this->container->get('user_mailer_utility');
            $subject = "ORDER Registration";

            //$orderUrl = ""; //[URL/order]
            $orderUrl = $this->container->get('router')->generate(
                'main_common_home',
                array(),
                UrlGeneratorInterface::ABSOLUTE_URL
            );

            //$activationUrl = ""; //http://URL/order/activate-account/REGISTRATION-LINK-ID
            $activationUrl = $this->container->get('router')->generate(
                $this->siteName.'_activate_account',
                array(
                    'registrationLinkID'=>$registrationLinkId
                ),
                UrlGeneratorInterface::ABSOLUTE_URL
            );
            $systemEmail = $userSecUtil->getSiteSettingParameter('siteEmail');

            $body =
                "Thank You for registering at ".$orderUrl."!".
                $newline."Please visit the following link to activate your account or copy/paste it into your browser’s address bar:".
                $newline.$activationUrl.
                $newline."If you encounter any issues, please email our $systemEmail.";
            ;

            //                    $emails, $subject, $message, $ccs=null, $fromEmail=null
            $emailUtil->sendEmail($signUp->getEmail(), $subject, $body);

            //change status
            $signUp->setRegistrationStatus("Activation Email Sent");
            //$em->persist($signUp);
            $em->flush($signUp);

            $confirmation = "Thank You for signing up!<br>
                An email was sent to the email address you provided ".$signUp->getEmail()." with a registration link.<br>
                Please click the link emailed to you to activate your account.";
//            $this->get('session')->getFlashBag()->add(
//                'notice',
//                $confirmation
//            );

            //return $this->redirectToRoute('employees_signup_show', array('id' => $signUp->getId()));
            return $this->render('OlegUserdirectoryBundle:SignUp:confirmation.html.twig',array('title'=>"Registration Confirmation",'message'=>$confirmation));
        }
        //exit('new');

        return $this->render('OlegUserdirectoryBundle:SignUp:new.html.twig', array(
            'signUp' => $signUp,
            'form' => $form->createView(),
            'title' => "Sign Up for ".$this->siteNameStr,
            'sitenamefull' => $this->siteNameStr,
            'sitename' => $this->siteName
        ));
    }

    /**
     * @Route("/activate-account/{registrationLinkID}", name="employees_activate_account")
     * @Method({"GET", "POST"})
     */
    public function activateAccountAction(Request $request, $registrationLinkID)
    {
        $userServiceUtil = $this->get('user_service_utility');
        $em = $this->getDoctrine()->getManager();

        $signUp = $em->getRepository('OlegUserdirectoryBundle:SignUp')->findOneByRegistrationLinkID($registrationLinkID);
        if( !$signUp ) {
            $confirmation = "This activation link is invalid. Please make sure you have copied it from your email message correctly.";
//            $this->get('session')->getFlashBag()->add(
//                'notice',
//                $confirmation
//            );
            return $this->render('OlegUserdirectoryBundle:SignUp:confirmation.html.twig',array('title'=>"Invalid Activation Link",'message'=>$confirmation));
        }

        //If the activation link is visited more than 48 hours after the timestamp in the timestamp column,
        // show the following message on the page: “This activation link has expired. Please <sign up> again.”

        //If the “Registration Status” of the Registration Link ID equals “Activated”,
        // show the following message: “This activation link has already been used. Please <log in> using your account.”

        $form = $this->createForm('Oleg\UserdirectoryBundle\Form\SignUpConfirmationType', $signUp);
        $form->handleRequest($request);

        if( $form->isSubmitted() && $form->isValid() ) {

            //Update the registration status” column in the “sign up list” table to “Activated”
            $signUp->setRegistrationStatus("Activated");

            //Set the account as “unlocked” and log in the user + send them to the “Employee Directory” homepage.


            exit('flush');
            $em->persist($signUp);
            $em->flush();

            //Event Log
            //$author = $this->get('security.token_storage')->getToken()->getUser();
            $author = null;
            $event = "Successful Account Activation:<br>".$signUp;
            $userSecUtil = $this->get('user_security_utility');
            $userSecUtil->createUserEditEvent($this->siteName,$event,$author,$signUp,$request,'Successful Account Activation');

            return $this->redirectToRoute($this->pathHome);
        }
        //exit('new');

        return $this->render('OlegUserdirectoryBundle:SignUp:activation.html.twig', array(
            'signUp' => $signUp,
            'form' => $form->createView(),
            'title' => "Activate Account for ".$this->siteNameStr,
            'sitenamefull' => $this->siteNameStr,
            'sitename' => $this->siteName
        ));



    }

    /**
     * Finds and displays a signUp entity.
     *
     * @Route("/{id}", name="employees_signup_show")
     * @Method("GET")
     */
    public function showAction(SignUp $signUp)
    {
        $deleteForm = $this->createDeleteForm($signUp);

        return $this->render('OlegUserdirectoryBundle:SignUp:new.html.twig', array(
            'signUp' => $signUp,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing signUp entity.
     *
     * @Route("/{id}/edit", name="employees_signup_edit")
     * @Method({"GET", "POST"})
     */
    public function editAction(Request $request, SignUp $signUp)
    {
        $deleteForm = $this->createDeleteForm($signUp);
        $editForm = $this->createForm('Oleg\UserdirectoryBundle\Form\SignUpType', $signUp);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('signup_edit', array('id' => $signUp->getId()));
        }

        return $this->render('OlegUserdirectoryBundle:SignUp:new.html.twig', array(
            'signUp' => $signUp,
            'title' => "Sign Up for ".$this->siteNameStr,
            'sitenamefull' => $this->siteNameStr,
            'sitename' => $this->siteName,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Deletes a signUp entity.
     *
     * @Route("/{id}", name="employees_signup_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, SignUp $signUp)
    {
        $form = $this->createDeleteForm($signUp);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($signUp);
            $em->flush();
        }

        return $this->redirectToRoute('signup_index');
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
            ->setAction($this->generateUrl('signup_delete', array('id' => $signUp->getId())))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }
}
