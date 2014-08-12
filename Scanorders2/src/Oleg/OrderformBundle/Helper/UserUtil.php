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

            ////////// assign default Institution //////////
            if( $user->getInstitution() == NULL || count($user->getInstitution()) == 0 ) {
                $params = $em->getRepository('OlegOrderformBundle:SiteParameters')->findAll();
                if( count($params) > 0 ) { //if zero found => initial admin login after DB clean
                    if( count($params) != 1 ) {
                        throw new \Exception( 'Must have only one parameter object. Found '.count($params).' object(s)' );
                    }
                    $param = $params[0];
                    $institution = $param->getAutoAssignInstitution();
                    if( $institution ) {
                        $user->addInstitution($institution);
                    }
                }
            }
            ////////// EOF assign Institution //////////

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

    //user has permission to perform the view/edit the valid field, created by someone else, if he/she is submitter or ROLE_SCANORDER_PROCESSOR or service chief or division chief
    public function hasUserPermission( $entity, $user ) {

        if( $entity == null ) {
            return true;
        }

        if( $user == null ) {
            return false;
        }


        ///////////////// 1) check if the user belongs to the same institution /////////////////
        $hasInst = false;
        foreach( $user->getInstitution() as $inst ) {
            //echo "compare: ".$inst->getId()."=?".$entity->getInstitution()->getId()."<br>";
            if( $inst->getId() == $entity->getInstitution()->getId() ) {
                $hasInst = true;
            }
        }

        if( $hasInst == false ) {
            return false;
        }
        ///////////////// EOF 1) /////////////////


        ///////////////// 2) check if the user is processor or service, division chief /////////////////
        if(
            $user->hasRole('ROLE_SCANORDER_ADMIN') ||
            $user->hasRole('ROLE_SCANORDER_PROCESSOR') ||
            $user->hasRole('ROLE_SCANORDER_DIVISION_CHIEF') ||
            $user->hasRole('ROLE_SCANORDER_SERVICE_CHIEF')
        ){
            return true;
        }
        ///////////////// EOF 2) /////////////////

        ///////////////// 3) submitters  /////////////////
        if( $user->hasRole('ROLE_SCANORDER_SUBMITTER') ) {
            return true;
        }
        ///////////////// EOF 3) /////////////////

        ///////////////// 4) pathology members  /////////////////
//        if(
//            true === $user->hasRole('ROLE_SCANORDER_PATHOLOGY_RESIDENT') ||
//            true === $user->hasRole('ROLE_SCANORDER_PATHOLOGY_FELLOW') ||
//            true === $user->hasRole('ROLE_SCANORDER_PATHOLOGY_FACULTY')
//        ) {
//            return true;
//        }
        ///////////////// EOF 4) /////////////////

        return false;

    }

    //wrapper for hasUserPermission
    public function hasPermission( $entity, $security_content ) {
        return $this->hasUserPermission($entity,$security_content->getToken()->getUser());
    }

    //check if the given user can perform given actions on the content of the given order
    public function isUserAllowOrderActions( $order, $user, $actions=null ) {

        if( !$this->hasUserPermission( $order, $user ) ) {
            return false;
        }

        //if actions are not specified => allow all actions
        if( $actions == null ) {
            return true;
        }

        //if actions is not array => return false
        if( !is_array($actions) ) {
            throw new \Exception('Actions must be an array');
            //return false;
        }

        //at this point, actions array has list of actions to performed by this user

        //processor and division chief can perform any actions
        if(
            $user->hasRole('ROLE_SCANORDER_ADMIN') ||
            $user->hasRole('ROLE_SCANORDER_PROCESSOR') ||
            $user->hasRole('ROLE_SCANORDER_DIVISION_CHIEF')
        ) {
            return true;
        }

        //submitter(owner) and ordering provider can perform any actions
        //echo $order->getProvider()->getId() . " ?= " . $user->getId() . "<br>";
        if( $order->getProvider()->getId() === $user->getId() || $order->getProxyuser()->getId() === $user->getId() ) {
            return true;
        }

        //order's service
        $service = $order->getPathologyService();

        //service chief can perform any actions
        $userChiefServices = $user->getChiefservices();
        if( $userChiefServices->contains($service) ) {
            return true;
        }

        //At this point we have only regular users

        //for each action
        foreach( $actions as $action ) {

            //echo "action=".$action."<br>";

            //status change can be done only by submitter(owner), ordering provider, or service chief: it would not get here, so not allowed
            if( $action == 'changestatus' ) {
                return false;
            }

            //amend can be done only by submitter(owner), ordering provider, or service chief: it would not get here, so not allowed
            if( $action == 'amend' ) {
                return false;
            }

            //edit can be done only by submitter(owner), ordering provider, or service chief: it would not get here, so not allowed
            if( $action == 'edit' ) {
                return false;
            }

            //show is allowed if the user belongs to the same service
            if( $action == 'show' ) {
                //echo "action: show <br>";
                $userServices = $user->getPathologyServices();
                if( $userServices->contains($service) ) {
                    return true;
                }
            }
        }

        //exit('is User Allow Order Actions: no permission');
        return false;
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
            return 1800; //30 min
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