<?php
/**
 * Created by JetBrains PhpStorm.
 * User: oli2002
 * Date: 3/18/14
 * Time: 7:21 PM
 * To change this template use File | Settings | File Templates.
 */

namespace Oleg\OrderformBundle\Security\Util;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

class AperioUtil {

    private $ldap = true;
    private $test = false;

    public function aperioAuthenticateToken( TokenInterface $token, $serviceContainer ) {

        //echo "AperioAuthenticator: user name=".$token->getUsername().", Credentials=".$token->getCredentials()."<br>";
        //exit("using Aperio Authenticator: authenticate Token");

        $AuthResult = $this->AperioAuth( $token->getUsername(), $token->getCredentials() );

        //print_r($AuthResult);
        //exit();

        if( isset($AuthResult['UserId']) && $AuthResult['ReturnCode'] == 0 ) {
            //echo "<br>Aperio got UserId!<br>";

//            $user = $userProvider->findUser($token->getUsername());
            $userManager = $serviceContainer->get('fos_user.user_manager');
            $user = $userManager->findUserByUsername($token->getUsername());

            if( !$user ) {

                //echo "No user found. Create a new User<br>";

                $user = $userManager->createUser();

                $user->setUsername($token->getUsername());
                $user->setEmail($AuthResult['E_Mail']);
                $user->setEnabled(1);
                $user->setCreatedby('aperio');

                //set Roles: aperio users can submit order by default.
                if( $this->test ) {
                    $user->addRole('ROLE_UNAPPROVED_SUBMITTER');
                } else {
                    $user->addRole('ROLE_SUBMITTER');           //Submitter
                }

                //TDODD: Remove: for testing at home;
                if( !$this->ldap ) {
                    echo "Aperio Auth: Remove it !!!";
                    $user->setUsername("testuser4");
                    $user->addRole('ROLE_ADMIN');
                }

                if( $token->getUsername() == "oli2002" || $token->getUsername() == "vib9020" ) {
                    $user->addRole('ROLE_ADMIN');
                }

                $user->setPassword("");

                $userManager->updateUser($user);

            } //if user


            //get roles: Faculty, Residents, or Fellows
            $_SESSION ['AuthToken'] = $AuthResult['Token'];
            $userid = $AuthResult['UserId'];
            //$roles = $client->GetUserRoleRecordsById($userid); //ADB_GetUserRoles
            //$roles = ADB_GetCurrentUserRoles();
            $aperioRoles = ADB_GetUserRoles($userid); //ADB_GetUserRoles
            //$roles = GetUserRoles($userid); //ADB_GetUserRoles
            echo "roles:";
            print_r($aperioRoles);
            echo "end of roles<br><br>";

            //TODO: get exact role names as in 'c.med.cornell.edu' server
            $addOrderingProviderRole = false;
            foreach( $aperioRoles as $role ) {
                //echo "Role: Id = ".$role->Id.", Description=".$role->Description.", Name=".$role->Name."<br>";
                if( $role->Name == "Faculty" ) {
                    $user->addRole("ROLE_PATHOLOGY_FACULTY");
                    $addOrderingProviderRole = true;
                }
                if( $role->Name == "Fellows" ) {
                    $user->addRole("ROLE_PATHOLOGY_FELLOW");
                    $addOrderingProviderRole = true;
                }
                if( $role->Name == "Residents" ) {
                    $user->addRole("ROLE_PATHOLOGY_RESIDENT");
                    $addOrderingProviderRole = true;
                }
            }

            if( $addOrderingProviderRole ) {
                $user->addRole('ROLE_ORDERING_PROVIDER');   //Ordering Provider
                $user->addRole('ROLE_COURSE_DIRECTOR');
                $user->addRole('ROLE_PRINCIPAL_INVESTIGATOR');
            }

            //exit('AperioAuth');

            return $user;

        } else {
            throw new AuthenticationException('The Aperio authentication failed. Authentication Result:'.implode(";",$AuthResult));
        }

        throw new AuthenticationException('Aperio: Invalid username or password');
    }


    public function AperioAuth( $loginName, $password ) {

        //echo "Aperio Auth Changeit back !!!";
        //exit();
        //echo " skip login=".$loginName.", pass=". $password." <br>";

        if( $this->ldap ) {
            include_once '\Skeleton.php';
            include_once '\DatabaseRoutines.php';
            //include_once '\cDataClient.php';
            //include_once '\Roles.php';
            //include_once '\cFilter.php';
            //$DataServerURL = "http://127.0.0.1:86";
            $DataServerURL = GetDataServerURL();
            $client = new \Aperio_Aperio($DataServerURL);//,"","","","");
            $AuthResult = $client->Authenticate($loginName,$password);

            //check if auth is ok: define ('LOGON_FAILED', '-7004');           // UserName is incorrect
            echo "ReturnCode=".$AuthResult['ReturnCode']."<br>";
            if( $AuthResult['ReturnCode'] == '-7004' || !isset($AuthResult['UserId']) ) {
                echo "LOGON_FAILED!!! <br>";
                return $AuthResult;
            }

        } else {
            echo "Aperio Auth Changeit back !!!";
            $AuthResult = array(
                'UserId' => 11,
                'ReturnCode' => 0,
                'E_Mail' => 'email@dummy'
            );
            //$loginName = 'oli2002';
        }

        echo "<br>AuthResult:<br>";
        print_r($AuthResult);
        //exit('AperioAuth');

        return $AuthResult;
    }

}