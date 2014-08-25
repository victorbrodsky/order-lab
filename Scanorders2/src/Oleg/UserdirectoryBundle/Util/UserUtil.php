<?php
/**
 * Created by JetBrains PhpStorm.
 * User: oli2002
 * Date: 10/4/13
 * Time: 12:56 PM
 * To change this template use File | Settings | File Templates.
 */

namespace Oleg\UserdirectoryBundle\Util;

use Doctrine\Common\Collections\ArrayCollection;

use Oleg\UserdirectoryBundle\Entity\User;
use Oleg\UserdirectoryBundle\Entity\AdministrativeTitle;
use Oleg\UserdirectoryBundle\Entity\Logger;
use Oleg\OrderformBundle\Security\Util\AperioUtil;

class UserUtil {

    public function generateUsersExcel( $em, $default_time_zone ) {
        $inputFileName = __DIR__ . '/../Util/users.xlsx';

        try {
            $inputFileType = \PHPExcel_IOFactory::identify($inputFileName);
            $objReader = \PHPExcel_IOFactory::createReader($inputFileType);
            $objPHPExcel = $objReader->load($inputFileName);
        } catch(Exception $e) {
            die('Error loading file "'.pathinfo($inputFileName,PATHINFO_BASENAME).'": '.$e->getMessage());
        }

        //$sheetData = $objPHPExcel->getActiveSheet()->toArray(null,true,true,true);
        //var_dump($sheetData);

        $count = 0;
        $serviceCount = 0;

        ////////////// add system user /////////////////
        $adminemail = $this->getSiteSetting($em,'siteEmail');
        $systemuser = new User();
        $systemuser->setEmail($adminemail);
        $systemuser->setEmailCanonical($adminemail);
        $systemuser->setUsername('system');
        $systemuser->setUsernameCanonical('system');
        $systemuser->setPassword("");
        $systemuser->setCreatedby('system');
        $systemuser->addRole('ROLE_SCANORDER_PROCESSOR');
        $systemuser->getPreferences()->setTimezone($default_time_zone);
        $systemuser->setEnabled(true);
        $systemuser->setLocked(true); //system is locked, so no one can logged in with this account
        $systemuser->setExpired(false);
        $found_user = $em->getRepository('OlegUserdirectoryBundle:User')->findOneByUsername('system');
        if( !$found_user ) {
            $em->persist($systemuser);
            $em->flush();
            $count++;
        } else {
            $systemuser = $found_user;
        }
        ////////////// end of add system user /////////////////

        $sheet = $objPHPExcel->getSheet(0);
        $highestRow = $sheet->getHighestRow();
        $highestColumn = $sheet->getHighestColumn();

        //for each user in excel
        for ($row = 2; $row <= $highestRow; $row++){
            //  Read a row of data into an array
            $rowData = $sheet->rangeToArray('A' . $row . ':' . $highestColumn . $row,
                NULL,
                TRUE,
                FALSE);

            //  Insert row data array into the database
//            echo $row.": ";
//            var_dump($rowData);
//            echo "<br>";

            $email = $rowData[0][11];
            list($username, $extra) = explode("@", $email);
            $phone = $rowData[0][8];
            $fax = $rowData[0][12];
            $firstName = $rowData[0][6];
            $lastName = $rowData[0][5];
            $title = $rowData[0][7];
            $office = $rowData[0][10];
            $services = explode("/",$rowData[0][2]);

            //echo "<br>divisions=".$rowData[0][2]." == ";
            //print_r($services);

            //create excel user
            $user = new User();
            $user->setEmail($email);
            $user->setEmailCanonical($email);
            $user->setUsername($username);
            $user->setUsernameCanonical($username);
            $user->setFirstName($firstName);
            $user->setLastName($lastName);
            $user->setDisplayName($firstName." ".$lastName);
            //$user->setPhone($phone);
            //$user->setFax($fax);
            //$user->setTitle($title);
            //$user->setOffice($office);
            $user->setPassword("");
            $user->setCreatedby('excel');
            $user->getPreferences()->setTimezone($default_time_zone);

            //phone, fax, office are stored in Location object
            //$mainLocation  = $em->getRepository('OlegUserdirectoryBundle:Location')->findOneByName('Main Office');
            $mainLocation  = $user->getMainLocation();
            $mainLocation->setPhone($phone);
            $mainLocation->setFax($fax);
            $mainLocation->setRoom($office);

            //title is stored in Administrative Title
            $administrativeTitle = new AdministrativeTitle();
            $administrativeTitle->setName($title);
            $user->addAdministrativeTitle($administrativeTitle);

            //add Roles
            $user->addRole('ROLE_SCANORDER_SUBMITTER');

            //************** get Aperio group roles and ROLE_SCANORDER_ORDERING_PROVIDER for this user **************//
            $aperioUtil = new AperioUtil();

            $userid = $aperioUtil->getUserIdByUserName($username);

            $aperioRoles = $aperioUtil->getUserGroupMembership($userid);

            $stats = $aperioUtil->setUserPathologyRolesByAperioRoles( $user, $aperioRoles );

            //echo "username=(".$username.")\n";
            //echo "userid=(".$userid.")\n";
            //if( $username == 'oli2002' ) {
                //print_r($aperioRoles);
                //exit('aperio util');
            //}
            //************** end of  Aperio group roles **************//


            if( $username == "oli2002" || $username == "vib9020" ) {
                $user->addRole('ROLE_SCANORDER_ADMIN');
            }

//            ////////// when user created by excel file, assign permittedInstitutionalPHIScope to its PerSiteSettings //////////
//            if( count($user->getPerSiteSettings()) == 0 ) {
//                $params = $em->getRepository('OlegOrderformBundle:SiteParameters')->findAll();
//                if( count($params) > 0 ) { //if zero found => initial admin login after DB clean
//                    if( count($params) != 1 ) {
//                        throw new \Exception( 'Must have only one parameter object. Found '.count($params).' object(s)' );
//                    }
//                    $param = $params[0];
//                    $institution = $param->getAutoAssignInstitution();
//                    if( $institution ) {
//                        $perSiteSettings = new PerSiteSettings();
//                        $perSiteSettings->setSiteName('scanorder');
//                        $perSiteSettings->setAuthor($systemuser);
//                        $perSiteSettings->addPermittedInstitutionalPHIScope($institution);
//                        $user->addPerSiteSettings($perSiteSettings);
//                    }
//                }
//
//
//            }
//            ////////// EOF assign Institution //////////

            foreach( $services as $service ) {

                $service = trim($service);

                if( $service != "" ) {
                    //echo " (".$service.") ";
                    $serviceEntity  = $em->getRepository('OlegUserdirectoryBundle:Service')->findOneByName($service);

                    if( $serviceEntity ) {
                        $administrativeTitle->setService($serviceEntity);
                        $division = $serviceEntity->getDivision();
                        $administrativeTitle->setDivision($division);
                        $department = $division->getDepartment();
                        $administrativeTitle->setDepartment($department);
                        $institution = $department->getInstitution();
                        $administrativeTitle->setInstitution($institution);
                    } else {
                        //Don't create service if it is not found in the service list
//                        $serviceEntity = new \Oleg\UserdirectoryBundle\Entity\Service();
//                        $serviceEntity->setOrderinlist( $serviceCount );
//                        $serviceEntity->setCreator( $systemuser );
//                        $serviceEntity->setCreatedate( new \DateTime() );
//                        $serviceEntity->setName( trim($service) );
//                        $serviceEntity->setType('default');
//                        $em->persist($serviceEntity);
//                        $em->flush();
//                        $serviceCount = $serviceCount + 10;
                    }
                } //if

            } //foreach

            $user->setEnabled(true);
            $user->setLocked(false);
            $user->setExpired(false);

            $found_user = $em->getRepository('OlegUserdirectoryBundle:User')->findOneByUsername($username);
            if( $found_user ) {
                //
            } else {
                //echo $username." not found ";
                $em->persist($user);
                $em->flush();
                $count++;
            }

        }//for each user

        //exit();
        return $count;
    }

    public function setLoginAttempt( $request, $security_content, $em, $options ) {

        $user = null;
        $username = null;
        $roles = null;

        if( !array_key_exists('serverresponse', $options) ) {
            //$options['serverresponse'] = null;
            $options['serverresponse'] = http_response_code();
        }

        $token = $security_content->getToken();

        if( $token ) {
            $user = $security_content->getToken()->getUser();
            $username = $token->getUsername();
            //print_r($user);
            if( $user && is_object($user) ) {
                $roles = $user->getRoles();
            } else {
                $user = null;
            }
        } else {
            $username = $request->get('_username');
        }

        $logger = new Logger();
        $logger->setUser($user);
        $logger->setRoles($roles);
        $logger->setUsername($username);
        $logger->setIp($request->getClientIp());
        $logger->setUseragent($_SERVER['HTTP_USER_AGENT']);
        $logger->setWidth($request->get('display_width'));
        $logger->setHeight($request->get('display_height'));
        $logger->setEvent($options['event']);
        $logger->setServerresponse($options['serverresponse']);

        //exit();

        $em->persist($logger);
        $em->flush();

    }

    public function getMaxIdleTime($em) {

        $params = $em->getRepository('OlegOrderformBundle:SiteParameters')->findAll();

        if( !$params ) {
            //new DB does not have SiteParameters object
            return 1800; //30 min
            //throw new \Exception( 'Parameter object is not found' );
        }

        if( count($params) != 1 ) {
            throw new \Exception( 'Must have only one parameter object. Found '.count($params).'object(s)' );
        }

        $param = $params[0];
        $maxIdleTime = $param->getMaxIdleTime();

        //return time in seconds
        $maxIdleTime = $maxIdleTime * 60;

        return $maxIdleTime;
    }

    public function getMaxIdleTimeAndMaintenance($em) {

        $params = $em->getRepository('OlegOrderformBundle:SiteParameters')->findAll();

        if( !$params ) {
            //new DB does not have SiteParameters object
            $res = array(
                'maxIdleTime' => 1800,
                'maintenance' => false
            );
            return $res; //30 min
            //throw new \Exception( 'Parameter object is not found' );
        }

        if( count($params) != 1 ) {
            throw new \Exception( 'Must have only one parameter object. Found '.count($params).'object(s)' );
        }

        $param = $params[0];
        $maxIdleTime = $param->getMaxIdleTime();
        $maintenance = $param->getMaintenance();

        //return time in seconds
        $maxIdleTime = $maxIdleTime * 60;

        $res = array(
            'maxIdleTime' => $maxIdleTime,
            'maintenance' => $maintenance
        );

        return $res;
    }

    //return parameter specified by $setting. If the first time login when site parameter does not exist yet, return -1.
    public function getSiteSetting($em,$setting) {

        $params = $em->getRepository('OlegOrderformBundle:SiteParameters')->findAll();

//        if( !$params ) {
//            //throw new \Exception( 'Parameter object is not found' );
//        }

        if( count($params) == 0 ) {
            return -1;
        }

        if( count($params) > 1 ) {
            throw new \Exception( 'Must have only one parameter object. Found '.count($params).' object(s)' );
        }

        $param = $params[0];

        $getSettingMethod = "get".$setting;
        $res = $param->$getSettingMethod();

        return $res;
    }

}