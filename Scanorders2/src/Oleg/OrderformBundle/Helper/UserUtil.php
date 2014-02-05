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

class UserUtil {

    public function generateUsersExcel($em) {
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

        $sheet = $objPHPExcel->getSheet(0);
        $highestRow = $sheet->getHighestRow();
        $highestColumn = $sheet->getHighestColumn();

        //for each user
        $count = 0;
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

            //add Roles
            //"ROLE_USER" => "Submitter" is added by default
            $user->addRole('ROLE_ORDERING_PROVIDER');

            //ROLES: Submitter - ROLE_USER, Processor - ROLE_ADMIN

            if( $username == "oli2002" || $username == "vib9020" ) {
                $user->addRole('ROLE_ADMIN');
            }

//            if( $username == "svc_aperio_spectrum" ) {
//                $user->addRole('ROLE_ADMIN');
//            }

//            $pathlogyServiceEntities = new ArrayCollection();
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
//                    if( !$user->getDefaultPathService() ) {
//                        $user->setDefaultPathService($pathlogyServiceEntity->getId());  //set the first pathology service as default one
//                    }
                }
            }
//            if( count($pathlogyServiceEntities) > 0 ) {
//                $user->setPathologyServices($pathlogyServiceEntities);
//            }

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
        $services = $user->getPathologyServices();
        $choicesServ = array( "My Orders"=>"My Orders", "Orders I Personally Placed"=>"Orders I Personally Placed", "Proxy Orders Placed For Me"=>"Proxy Orders Placed For Me");
        foreach( $services as $service ) {
            $choicesServ[$service->getId()] = "All ".$service->getName()." Orders";
        }
        //print_r($choicesServ);
        return $choicesServ;
    }

    //TODO: what to use ROLE_SUBMITTER or ROLE_PATHOLOGY_RESIDENT?
    public function hasPermission( $security_content ) {
        if(
            //$entity &&
            false === $security_content->isGranted('ROLE_SUBMITTER') &&
            false === $security_content->isGranted('ROLE_PATHOLOGY_RESIDENT') &&
            false === $security_content->isGranted('ROLE_PATHOLOGY_FELLOW') &&
            false === $security_content->isGranted('ROLE_PATHOLOGY_FACULTY')
        ) {
            return false;
//            $user = $security_content->getToken()->getUser();
//            if( $entity->getProvider()->getId() != $user->getId() ) {
//                return false;
//            }
        } else {
            return true;
        }
    }

}