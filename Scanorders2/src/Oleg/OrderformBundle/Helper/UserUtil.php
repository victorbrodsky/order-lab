<?php
/**
 * Created by JetBrains PhpStorm.
 * User: oli2002
 * Date: 10/4/13
 * Time: 12:56 PM
 * To change this template use File | Settings | File Templates.
 */

namespace Oleg\OrderformBundle\Helper;

use Oleg\OrderformBundle\Entity\User;
use Doctrine\Common\Collections\ArrayCollection;
use Oleg\OrderformBundle\Entity\PathServiceList;
use Oleg\OrderformBundle\Entity\Logger;
use Oleg\OrderformBundle\Security\Util\AperioUtil;

class UserUtil {

    public function generateUsersExcel( $em, $default_time_zone ) {
        $inputFileName = __DIR__ . '/../Helper/users.xlsx';

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

        ////////////// add system user /////////////////
        $adminemail = $this->getSiteSetting($em,'siteEmail');
        $user = new User();
        $user->setEmail($adminemail);
        $user->setEmailCanonical($adminemail);
        $user->setUsername('system');
        $user->setUsernameCanonical('system');
        $user->setPassword("");
        $user->setCreatedby('system');
        $user->addRole('ROLE_SCANORDER_PROCESSOR');
        $user->getPreferences()->setTimezone($default_time_zone);
        $user->setEnabled(true);
        $user->setLocked(true); //system is locked, so no one can logged in with this account
        $user->setExpired(false);
        $found_user = $em->getRepository('OlegOrderformBundle:User')->findOneByUsername('system');
        if( !$found_user ) {
            $em->persist($user);
            $em->flush();
            $count++;
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
            $pathlogyServices = explode("/",$rowData[0][2]);

            //echo "<br>pathservices=".$rowData[0][2]." == ";
            //print_r($pathlogyServices);

            //create system user
            $user = new User();
            $user->setEmail($email);
            $user->setEmailCanonical($email);
            $user->setUsername($username);
            $user->setUsernameCanonical($username);
            $user->setFirstName($firstName);
            $user->setLastName($lastName);
            $user->setDisplayName($firstName." ".$lastName);
            $user->setPhone($phone);
            $user->setFax($fax);
            $user->setTitle($title);
            $user->setOffice($office);
            $user->setPassword("");
            $user->setCreatedby('excel');
            $user->getPreferences()->setTimezone($default_time_zone);

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

            foreach( $pathlogyServices as $pathlogyService ) {

                $pathlogyService = trim($pathlogyService);

                if( $pathlogyService != "" ) {
                    //echo " (".$pathlogyService.") ";
                    $pathlogyServiceEntity  = $em->getRepository('OlegOrderformBundle:PathServiceList')->findOneByName($pathlogyService);

                    if( $pathlogyServiceEntity ) {
                        //$pathlogyServiceEntities[] = $pathlogyServiceEntity;
                    } else {
                        $pathlogyServiceEntity = new PathServiceList();
                        $pathlogyServiceEntity->setCreator( $username );
                        $pathlogyServiceEntity->setCreatedate( new \DateTime() );
                        $pathlogyServiceEntity->setName( $pathlogyService );
                        $pathlogyServiceEntity->setType('default');
                        $em->persist($pathlogyServiceEntity);
                        $em->flush();
                    }
                    $user->addPathologyServices($pathlogyServiceEntity);
                } //if

            } //foreach

            $user->setEnabled(true);
            $user->setLocked(false);
            $user->setExpired(false);

            $found_user = $em->getRepository('OlegOrderformBundle:User')->findOneByUsername($username);
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

    public function generateUserPathServices( $user ) {
        $choicesServ = array(
            "My Orders"=>"My Orders",
            "Where I am the Submitter"=>"Where I am the Submitter",
            "Where I am the Ordering Provider"=>"Where I am the Ordering Provider",
            "Where I am the Course Director"=>"Where I am the Course Director",
            "Where I am the Principal Investigator"=>"Where I am the Principal Investigator",
            "Where I am the Amendment Author"=>"Where I am the Amendment Author"
        );
        if( is_object($user) && $user instanceof User ) {
            $services = $user->getPathologyServices();
            foreach( $services as $service ) {
                $choicesServ[$service->getId()] = "All ".$service->getName()." Orders";
            }
        }

        return $choicesServ;
    }

    //user has permission to view other's valid field if he/she is submitter or pathology member and not external submitter and not ROLE_SCANORDER_PROCESSOR => S+PRE+PFE+PFA * !ES * !P == !S*!PRE*!PFE*!PFA + ES
    public function hasPermission( $security_content ) {

        if( true === $security_content->isGranted('ROLE_SCANORDER_PROCESSOR') ) {
            return true;
        }

        if( true === $security_content->isGranted('ROLE_SCANORDER_EXTERNAL_SUBMITTER') ) {
            return false;
        }

        if(
            true === $security_content->isGranted('ROLE_SCANORDER_SUBMITTER') ||
            true === $security_content->isGranted('ROLE_SCANORDER_PATHOLOGY_RESIDENT') ||
            true === $security_content->isGranted('ROLE_SCANORDER_PATHOLOGY_FELLOW') ||
            true === $security_content->isGranted('ROLE_SCANORDER_PATHOLOGY_FACULTY')
        ) {
            return true;
        } else {
            return false;
        }
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

    public function getSiteSetting($em,$setting) {

        $params = $em->getRepository('OlegOrderformBundle:SiteParameters')->findAll();

        if( !$params ) {
            //throw new \Exception( 'Parameter object is not found' );
        }

        if( count($params) != 1 ) {
            throw new \Exception( 'Must have only one parameter object. Found '.count($params).' object(s)' );
        }

        $param = $params[0];

        $getSettingMethod = "get".$setting;
        $res = $param->$getSettingMethod();

        return $res;
    }

}