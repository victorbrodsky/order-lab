<?php
/**
 * Copyright (c) 2017 Cornell University
 *
 *  Licensed under the Apache License, Version 2.0 (the "License");
 *  you may not use this file except in compliance with the License.
 *  You may obtain a copy of the License at
 *
 *  http://www.apache.org/licenses/LICENSE-2.0
 *
 *  Unless required by applicable law or agreed to in writing, software
 *  distributed under the License is distributed on an "AS IS" BASIS,
 *  WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 *  See the License for the specific language governing permissions and
 *  limitations under the License.
 *
 *  Created by Oleg Ivanov
 */

namespace App\UserdirectoryBundle\Controller;

use App\TranslationalResearchBundle\Entity\Project;
use App\UserdirectoryBundle\Entity\InterfaceTransferList;
use App\UserdirectoryBundle\Entity\TransferData;
use App\UserdirectoryBundle\Entity\TransferStatusList;
use App\UserdirectoryBundle\Util\SyncBackupUtil;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\UserdirectoryBundle\Controller\OrderAbstractController;


class InterfaceController extends OrderAbstractController
{
    
    #[Route(path: '/interface-log', name: 'employees_logger_interface', methods: ['GET'])]
    #[Template('AppUserdirectoryBundle/Logger/index.html.twig')]
    public function interfaceLoggerAction(Request $request)
    {
        exit("interface-log is under Construction");

        if (false === $this->isGranted('ROLE_PLATFORM_DEPUTY_ADMIN')) {
            return $this->redirect($this->generateUrl($this->getParameter('employees.sitename') . '-nopermission'));
        }

        $params = array(
            'sitename'=>$this->getParameter('employees.sitename')
        );
        return $this->listLogger($params,$request);
    }



    #[Route(path: '/interface-test', name: 'employees_interface_test', methods: ['GET'])]
    #[Template('AppUserdirectoryBundle/Logger/index.html.twig')]
    public function interfaceManagerAction(Request $request)
    {
        if (false === $this->isGranted('ROLE_PLATFORM_DEPUTY_ADMIN')) {
            return $this->redirect($this->generateUrl($this->getParameter('employees.sitename') . '-nopermission'));
        }

        //exit("Under Construction: interface-manager");

        $interfaceTransferUtil = $this->container->get('interface_transfer_utility');
        $em = $this->getDoctrine()->getManager();

        $transfers = $em->getRepository(InterfaceTransferList::class)->findAll();

        $transfer = NULL;
        if( count($transfers) > 0 ) {
            $transfer = $transfers[0];
        }

        //$res = $interfaceTransferUtil->classListMapper($transfer);
        //dump($res);
        //exit('111');
        
        $interfaceTransferUtil->testTransferFile($transfer);

        exit();
    }

    #[Route(path: '/transfer-manager', name: 'employees_interface_manager', methods: ['GET'])]
    #[Template('AppUserdirectoryBundle/TransferInterface/manager.html.twig')]
    public function transferInterfaceManagerAction(Request $request, SyncBackupUtil $syncBackupUtil)
    {
        if(
            false === $this->isGranted('ROLE_PLATFORM_DEPUTY_ADMIN') &&
            false === $this->isGranted('ROLE_TRANSRES_ADMIN')
        ) {
            return $this->redirect($this->generateUrl($this->getParameter('employees.sitename') . '-nopermission'));
        }

        $interfaceTransferUtil = $this->container->get('interface_transfer_utility');

        //Testing
        //$files = $syncBackupUtil->downloadBackupFilesFromPublic();

        $title = "Transfer Interface Manager";

        //List of items to transfer from TransferData
        $transferDatas = $interfaceTransferUtil->getTransfers('Ready',true,$request);

        return array(
            'title' => $title,
            'entities' => $transferDatas
        );
    }

    #[Route(path: '/start-transfer', name: 'employees_start_transfer', methods: ['GET'])]
    #[Template('AppUserdirectoryBundle/TransferInterface/manager.html.twig')]
    public function startTransferAction(Request $request)
    {
        if(
            false === $this->isGranted('ROLE_PLATFORM_DEPUTY_ADMIN') &&
            false === $this->isGranted('ROLE_TRANSRES_ADMIN')
        ) {
            return $this->redirect($this->generateUrl($this->getParameter('employees.sitename') . '-nopermission'));
        }

        $interfaceTransferUtil = $this->container->get('interface_transfer_utility');

        //List of items to transfer from TransferData
        $transferDatas = $interfaceTransferUtil->sendTransfer();

        $request->getSession()->getFlashBag()->add(
            'notice',
            $transferDatas
        );

        return $this->redirect($this->generateUrl('employees_interface_manager'));
    }

    #[Route(path: '/transfer-interface/receive-transfer', name: 'employees_transfer_interface_receive_transfer', methods: ['POST'])]
    public function receiveTransferAction(Request $request)
    {
        //if (false === $this->isGranted('ROLE_PLATFORM_DEPUTY_ADMIN')) {
        //    return $this->redirect($this->generateUrl($this->getParameter('employees.sitename') . '-nopermission'));
        //}
        //exit('receive!!!');

        $logger = $this->container->get('logger');
        $post_data = json_decode($request->getContent(), true);
        $logger->notice('receiveTransferAction: post_data count='.count($post_data));

        //https://stackoverflow.com/questions/58709888/php-curl-how-to-safely-send-data-to-another-server-using-curl
        //$secretKey = $interfaceTransfer->getSshPassword(); //use SshPassword for now
        //$secretKey = $_ENV['APP_SECRET']; //get .env parameter
        $userSecUtil = $this->container->get('user_security_utility');
        $secretKey = $userSecUtil->getSiteSettingParameter('secretKey');

        $checksum = NULL;
        $input = array();
        foreach ($post_data as $key => $value) {
            if ($key === 'hash') {     // Checksum value is separate from all other fields and shouldn't be included in the hash
                $checksum = $value;
            } else {
                $input[$key] = $value;
            }
        }

        $valid = NULL;
        $hash = hash('sha512', $secretKey . serialize($input));
        if ($hash === $checksum) {
            $valid = true;
        } else {
            $valid = false;
        }

        $transferResult = NULL;
        if( $valid ) {
            $logger->notice('receiveTransferAction: checksum valid');
            $interfaceTransferUtil = $this->container->get('interface_transfer_utility');
            $transferResult = $interfaceTransferUtil->receiveTransfer($input);
        }

        //$post_str = implode(',', $input);
        //$logger->notice('receiveTransferAction: input='.$post_str);
        //$res = "OK; ".$post_str . "; VALID=$valid"; //"OK";

        $res = array(
            "checksum" => $checksum,
            "valid" => $valid,
            "transferResult" => $transferResult
        );

        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');
        $response->setContent(json_encode($res));
        return $response;
    }

    #[Route(path: '/transfer-interface/get-app-path', name: 'employees_transfer_interface_get_app_path', methods: ['POST'])]
    public function getAppPathAction(Request $request)
    {
        //$logger = $this->container->get('logger');
        $post_data = json_decode($request->getContent(), true);
        //$logger->notice('getAppPathAction: post_data count='.count($post_data));

        //https://stackoverflow.com/questions/58709888/php-curl-how-to-safely-send-data-to-another-server-using-curl
        //$secretKey = $interfaceTransfer->getSshPassword(); //use SshPassword for now
        //$secretKey = $_ENV['APP_SECRET']; //get .env parameter
        $userSecUtil = $this->container->get('user_security_utility');
        $secretKey = $userSecUtil->getSiteSettingParameter('secretKey');

        $checksum = NULL;
        $input = array();
        foreach ($post_data as $key => $value) {
            if ($key === 'hash') {     // Checksum value is separate from all other fields and shouldn't be included in the hash
                $checksum = $value;
            } else {
                $input[$key] = $value;
            }
        }

        $valid = NULL;
        //$logger->notice("serialize(jsonFile)=".serialize($input));
        $hash = hash('sha512', $secretKey . serialize($input));
        //$logger->notice("receiveTransferAction: hash=[$hash], checksum=[$checksum]");
        if ($hash === $checksum) {
            $valid = true;
        } else {
            $valid = false;
        }

        $transferResult = NULL;
        if( $valid ) {
            //$logger->notice('receiveTransferAction: checksum valid');
            $interfaceTransferUtil = $this->container->get('interface_transfer_utility');
            $transferResult = $interfaceTransferUtil->receiveTransfer($input);
        }

        //$post_str = implode(',', $input);
        //$logger->notice('receiveTransferAction: input='.$post_str);
        //$res = "OK; ".$post_str . "; VALID=$valid"; //"OK";

        $projectRoot = $this->container->get('kernel')->getProjectDir(); //C:\Users\ch3\Documents\MyDocs\WCMC\ORDER\order-lab\orderflex
        //$logger->notice('getAppPathAction: projectRoot='.$projectRoot);

        $res = array(
            "checksum" => $checksum,
            "valid" => $valid,
            "transferResult" => $transferResult,
            "apppath" => $projectRoot
        );

        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');
        $response->setContent(json_encode($res));
        return $response;
    }

    #[Route(path: '/transfer-interface/get-backup-path', name: 'employees_transfer_interface_get_backup_path', methods: ['POST'])]
    public function getBackupPathAction(Request $request)
    {
        //$logger = $this->container->get('logger');
        $post_data = json_decode($request->getContent(), true);
        //$logger->notice('getAppPathAction: post_data count='.count($post_data));

        //https://stackoverflow.com/questions/58709888/php-curl-how-to-safely-send-data-to-another-server-using-curl
        //$secretKey = $interfaceTransfer->getSshPassword(); //use SshPassword for now
        //$secretKey = $_ENV['APP_SECRET']; //get .env parameter
        $userSecUtil = $this->container->get('user_security_utility');
        $secretKey = $userSecUtil->getSiteSettingParameter('secretKey');

        $networkDrivePath = $userSecUtil->getSiteSettingParameter('networkDrivePath');
        
        $checksum = NULL;
        $input = array();
        foreach ($post_data as $key => $value) {
            if ($key === 'hash') {     // Checksum value is separate from all other fields and shouldn't be included in the hash
                $checksum = $value;
            } else {
                $input[$key] = $value;
            }
        }

        $valid = NULL;
        //$logger->notice("serialize(jsonFile)=".serialize($input));
        $hash = hash('sha512', $secretKey . serialize($input));
        //$logger->notice("receiveTransferAction: hash=[$hash], checksum=[$checksum]");
        if ($hash === $checksum) {
            $valid = true;
        } else {
            $valid = false;
        }

        //$post_str = implode(',', $input);
        //$logger->notice('receiveTransferAction: input='.$post_str);
        //$res = "OK; ".$post_str . "; VALID=$valid"; //"OK";

        $res = array(
            "checksum" => $checksum,
            "valid" => $valid,
            //"transferResult" => $transferResult,
            "backuppath" => $networkDrivePath
        );

        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');
        $response->setContent(json_encode($res));
        return $response;
    }

    /////////////////////////////////////
    ///////// Project transfer //////////
    /////////////////////////////////////

    //Run on internal (master). Get data from external (slave) to intranet (master)
    //Send request to the external asking to send back all new/updated projects
    #[Route(path: '/get-transfer', name: 'employees_get_transfer', methods: ['GET'])]
    #[Template('AppUserdirectoryBundle/TransferInterface/manager.html.twig')]
    public function getSlaveToMasterTransferAction(Request $request)
    {
        if(
            false === $this->isGranted('ROLE_PLATFORM_DEPUTY_ADMIN') &&
            false === $this->isGranted('ROLE_TRANSRES_ADMIN')
        ) {
            return $this->redirect($this->generateUrl($this->getParameter('employees.sitename') . '-nopermission'));
        }

        $interfaceTransferUtil = $this->container->get('interface_transfer_utility');

        //Testing
//        $transferableEntity = new Project();
//        $jsonObject = array(
//            'apppath' => '/usr/local/bin/order-lab-tenantapp1/orderflex',
//            'irbApprovalLetters' => array(
//                'originalname' => '668c329c96a32.pdf',
//                'uniqueid' => null,
//                'uploadDirectory' => "Uploaded/transres/documents",
//            ),
//        );
//        $interfaceTransferUtil->downloadFile($jsonObject, $transferableEntity, 'irbApprovalLetters');
        //$interfaceTransferUtil->downloadFile(null,null,null);
        //exit('111');

        //List of items to transfer from TransferData
        $transferDatas = $interfaceTransferUtil->getSlaveToMasterTransfer();

        $request->getSession()->getFlashBag()->add(
            'notice',
            $transferDatas
        );

        //dump($transferDatas);
        //exit('EOF getSlaveToMasterTransferAction');

        return $this->redirect($this->generateUrl('employees_interface_manager'));
    }

    //Run on external (slave) to send transferable objects in the response
    #[Route(path: '/transfer-interface/slave-to-master-transfer', name: 'employees_slave_to_master_transfer', methods: ['POST'])]
    public function sendSlaveToMasterTransferAction(Request $request)
    {
//        //exit('sendSlaveToMasterTransferAction');
//        //Testing
//        $res = array(
//            "checksum" => '123',
//            "valid" => true,
//            "transferResult" => 'OK'
//        );
//        $response = new Response();
//        $response->headers->set('Content-Type', 'application/json');
//        $response->setContent(json_encode($res));
//        return $response;

        $logger = $this->container->get('logger');
        $post_data = json_decode($request->getContent(), true);
        $logger->notice('sendSlaveToMasterTransferAction: post_data count='.count($post_data));

        //https://stackoverflow.com/questions/58709888/php-curl-how-to-safely-send-data-to-another-server-using-curl
        //$secretKey = $interfaceTransfer->getSshPassword(); //use SshPassword for now
        //$secretKey = $_ENV['APP_SECRET']; //get .env parameter
        $userSecUtil = $this->container->get('user_security_utility');
        $secretKey = $userSecUtil->getSiteSettingParameter('secretKey');

        $checksum = NULL;
        $input = array();
        foreach ($post_data as $key => $value) {
            if ($key === 'hash') {     // Checksum value is separate from all other fields and shouldn't be included in the hash
                $checksum = $value;
            } else {
                $input[$key] = $value;
            }
        }

        $valid = NULL;
        $hash = hash('sha512', $secretKey . serialize($input));
        if ($hash === $checksum) {
            $valid = true;
        } else {
            $valid = false;
        }

        $transferResult = NULL;
        if( $valid ) {
            $logger->notice('sendSlaveToMasterTransferAction: checksum valid');
            $interfaceTransferUtil = $this->container->get('interface_transfer_utility');
            $transferResult = $interfaceTransferUtil->sendSlavetoMasterTransfer($input);
        }

        //dump($transferResult);
        //exit('sendSlaveToMasterTransferAction');

        //$post_str = implode(',', $input);
        //$logger->notice('receiveTransferAction: input='.$post_str);
        //$res = "OK; ".$post_str . "; VALID=$valid"; //"OK";

        $res = array(
            "checksum" => $checksum,
            "valid" => $valid,
            "transferResult" => $transferResult
        );

        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');
        $response->setContent(json_encode($res));
        return $response;
    }

    //Run on external (slave)
    //send confirmationResponse back to source server to assign Global ID
    #[Route(path: '/transfer-interface/confirmation-master-to-slave', name: 'employees_confirmation_master_to_slave', methods: ['POST'])]
    public function confirmationMasterToSlaveAction(Request $request)
    {
        $logger = $this->container->get('logger');
        $logger->notice('confirmationMasterToSlaveAction: start');

        $post_data = json_decode($request->getContent(), true);
        $logger->notice('confirmationMasterToSlaveAction: post_data count='.count($post_data));

        $logger->notice("confirmationMasterToSlaveAction: ");

        //https://stackoverflow.com/questions/58709888/php-curl-how-to-safely-send-data-to-another-server-using-curl
        //$secretKey = $interfaceTransfer->getSshPassword(); //use SshPassword for now
        //$secretKey = $_ENV['APP_SECRET']; //get .env parameter
        $userSecUtil = $this->container->get('user_security_utility');
        $secretKey = $userSecUtil->getSiteSettingParameter('secretKey');

//        $checksum = NULL;
//        $input = array();
//        foreach ($post_data as $key => $value) {
//            if ($key === 'hash') {     // Checksum value is separate from all other fields and shouldn't be included in the hash
//                $checksum = $value;
//            } else {
//                $input[$key] = $value;
//            }
//        }

        $checksum = $post_data['hash'];
        $input = $post_data['confirmationResponse'];
        //$input[] = '1';

        $valid = NULL;
        $hash = hash('sha512', $secretKey . serialize($input));

        $logger->notice("confirmationMasterToSlaveAction: [$hash] =? [$checksum]");

        if ($hash === $checksum) {
            $valid = true;
        } else {
            $valid = false;
        }

        $transferResult = NULL;
        if( $valid ) {
            $logger->notice('confirmationMasterToSlaveAction: checksum valid');
            $interfaceTransferUtil = $this->container->get('interface_transfer_utility');
            $transferResult = $interfaceTransferUtil->receiveConfirmationOnSlave($input);
        } else {
            $logger->notice('confirmationMasterToSlaveAction: checksum is not valid');
        }

        //dump($transferResult);
        //exit('sendSlaveToMasterTransferAction');

        //$post_str = implode(',', $input);
        //$logger->notice('receiveTransferAction: input='.$post_str);
        //$res = "OK; ".$post_str . "; VALID=$valid"; //"OK";

        $res = array(
            "checksum" => $checksum,
            "valid" => $valid,
            "transferResult" => $transferResult
        );

        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');
        $response->setContent(json_encode($res));
        return $response;
    }

    #[Route(path: '/change-status-transfer/{id}/{status}', name: 'employees_change_status_transfer', methods: ['GET'])]
    public function changeStatusAction(Request $request, $id, $status)
    {
        if(
            false === $this->isGranted('ROLE_PLATFORM_DEPUTY_ADMIN') &&
            false === $this->isGranted('ROLE_TRANSRES_ADMIN')
        ) {
            return $this->redirect($this->generateUrl($this->getParameter('employees.sitename') . '-nopermission'));
        }

        $em = $this->getDoctrine()->getManager();

        //find TransferData by id
        $transferData = $em->getRepository(TransferData::class)->find($id);
        if( !$transferData ) {
            //exit('TransferData not found by id='.$id);
            $request->getSession()->getFlashBag()->add(
                'warning',
                'TransferData not found by id='.$id
            );
            return $this->redirect($this->generateUrl('employees_interface_manager'));
        }

        //find TransferStatusList by status
        $transferStatus = $em->getRepository(TransferStatusList::class)->findOneByName($status);
        if( !$transferStatus ) {
            //exit('TransferStatusList not found by name='.$status);
            $request->getSession()->getFlashBag()->add(
                'warning',
                'TransferStatusList not found by name='.$status
            );
            return $this->redirect($this->generateUrl('employees_interface_manager'));
        }

        if( $transferData && $transferStatus ) {
            $transferData->setTransferStatus($transferStatus);
            $em->flush();
        }

        $request->getSession()->getFlashBag()->add(
            'notice',
            "TransferData for " . $transferData->getClassName() . " has been set to " . $transferStatus
        );

        return $this->redirect($this->generateUrl('employees_interface_manager'));
    }
}
