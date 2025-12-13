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
 */

namespace App\UserdirectoryBundle\Controller;



use App\FellAppBundle\Entity\FellowshipApplication;
use App\UserdirectoryBundle\Controller\OrderAbstractController;

use App\UserdirectoryBundle\Entity\Document;
use App\UserdirectoryBundle\Entity\User;
use App\UserdirectoryBundle\Form\DataTransformer\GenericTreeTransformer;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Panther\Client;
use Symfony\Component\Routing\Annotation\Route;

use Symfony\Component\BrowserKit\HttpBrowser;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;


class DemoDataController extends OrderAbstractController
{

    private $baseUrl = 'https://view.online/c/demo-institution/demo-department';
    //private $baseUrl = 'http://localhost';

    // http://127.0.0.1/directory/api/upload-file
    #[Route(path: '/api/upload-file/', name: 'employees_api_upload_file', methods: ['GET','POST'])]
    public function apiUploadFile(Request $request) {
        //exit("Under construction");
        $logger = $this->container->get('logger');
        $logger->info("apiUploadFile: Starting file upload");
        
        // Get the uploaded file
        //$filepath = $request->files->get('filepath');
        //if (!$filepath) {
        //    return new JsonResponse(['error' => 'No file uploaded'], 400);
        //}

        //dd($request->getContent());
        //dd($request->request->all());

        // Get fellowship application ID
        $fellappId = $request->request->get('fellapp_id');
        if (!$fellappId) {
            return new JsonResponse([
                'status' => 'error',
                'error' => 'fellappId is required'
            ], 400);
        }
        $documentType = $request->request->get('documenttype');
        if (!$fellappId) {
            return new JsonResponse([
                'status' => 'error',
                'error' => 'documenttype is required'
            ], 400);
        }
        $filepath = $request->request->get('filepath');
        if (!$filepath) {
            return new JsonResponse([
                'status' => 'error',
                'error' => 'filepath is required'
            ], 400);
        }
        // src/App/FellAppBundle/Util/{file_name}
        $relativePath = $request->request->get('relative_path');
        if (!$relativePath) {
            return new JsonResponse([
                'status' => 'error',
                'error' => 'relative_path is required'
            ], 400);
        }
        $fileName = $request->request->get('file_name');
        if (!$fileName) {
            return new JsonResponse([
                'status' => 'error',
                'error' => 'file_name is required'
            ], 400);
        }
        $sitename = $request->request->get('sitename');
        if (!$sitename) {
            return new JsonResponse([
                'status' => 'error',
                'error' => 'sitename is required'
            ], 400);
        }

        // Get document type (default to 'Other' if not specified)
        //$documentType = $request->request->get('documenttype', 'Other');
        //$sitename = $request->request->get('sitename', 'fellapp');

        $projectRoot = $this->container->get('kernel')->getProjectDir(); //C:\Users\ch3\Documents\MyDocs\WCMC\ORDER\order-lab\orderflex
        $filepath = $projectRoot . DIRECTORY_SEPARATOR . $relativePath;

        $inputParameters = "apiUploadFile: projectRoot=$projectRoot, fellappId=$fellappId, documentType=$documentType, filepath=$filepath, $relativePath=relativePath, sitename=$sitename";

        $logger->info($inputParameters);
//        return new JsonResponse([
//            'status' => 'status ok: inputParameters='.$inputParameters,
//            'error' => null
//        ], 200);

        $em = $this->getDoctrine()->getManager();

        try {
            // Get the fellowship application
            //$fellowshipApplication = $em->getRepository('AppFellappBundle:FellowshipApplication')->find($fellappId);
            $fellowshipApplication = $em->getRepository(FellowshipApplication::class)->find($fellappId);
            if (!$fellowshipApplication) {
                return new JsonResponse([
                    'status' => 'error',
                    'error' => 'Fellowship application not found'
                ], 404);
            }

            // Get the user (system user if not authenticated)
            $user = $this->getUser();
            if (!$user) {
                $userSecUtil = $this->container->get('user_security_utility');
                $user = $userSecUtil->findSystemUser();
                if (!$user) {
                    return new JsonResponse([
                        'status' => 'error',
                        'error' => 'User not found and could not get system user'
                    ], 500);
                }
            }

            $currentDatetime = new \DateTime();
            $currentDatetimeTimestamp = $currentDatetime->getTimestamp();
            $fileExt = pathinfo($filepath, PATHINFO_EXTENSION);
            $fileExtStr = "";
            if( $fileExt ) {
                $fileExtStr = ".".$fileExt;
            }

            $fileUniqueName = $currentDatetimeTimestamp.'ID'.$fileName.$fileExtStr;  //.'_title='.$fileTitle;

            $filesize = null;
            if (file_exists($filepath)) {
                $sizeInBytes = filesize($filepath);
                echo "File size: " . $sizeInBytes . " bytes\n";
            } else {
                echo "File does not exist. projectRoot=$projectRoot, filepath=$filepath";
                return new JsonResponse([
                    'status' => 'error',
                    'error' => "File does not exist. projectRoot=$projectRoot, filepath=$filepath"
                ], 500);
            }

            // Create a new Document entity
            $document = new Document($user);
            $document->setUniqueid($fileUniqueName);
            $document->setUniquename($fileUniqueName);
            $document->setUploadDirectory($filepath);
            $document->setSize($filesize);
            
            // Set document type if provided
            if ($documentType) {
                $transformer = new GenericTreeTransformer($em, $user, "DocumentTypeList", "UserdirectoryBundle");
                $documentTypeObject = $transformer->reverseTransform($documentType);
                if ($documentTypeObject) {
                    $document->setType($documentTypeObject);
                }
            }

            // Move the file to the upload directory
            $uploadDirectory = $this->getParameter('kernel.project_dir') . '/public/uploaded/fellapp/documents/';
            if (!file_exists($uploadDirectory)) {
                mkdir($uploadDirectory, 0777, true);
            }
            
            // Set the upload directory in the document
            $document->setUploadDirectory('/uploaded/fellapp/documents/');

            // Generate thumbnails if it's an image
            //$fileExtension = strtolower(pathinfo($document->getCleanOriginalname(), PATHINFO_EXTENSION));
            if (in_array($fileExtStr, ['jpg', 'jpeg', 'png', 'gif'])) {
                $userServiceUtil = $this->container->get('user_service_utility');
                $userServiceUtil->generateTwoThumbnails($document);
            }

            // Add the document to the fellowship application
            $fellowshipApplication->addDocument($document);

            // Save everything
            $em->persist($document);
            $em->persist($fellowshipApplication);
            $em->flush();

            // Log the upload event
            $userSecUtil = $this->container->get('user_security_utility');
            $eventDescription = "Document $filepath has been added to the fellowship application ID $fellappId by " . $user;
            $userSecUtil->createUserEditEvent($sitename, $eventDescription, $user, $document, $request, $documentType . ' Uploaded');

            return new JsonResponse([
                'status' => 'success',
                'documentId' => $document->getId(),
                'documentName' => $document->getCleanOriginalname(),
                'documentPath' => $document->getRelativeUploadFullPath(),
                'message' => $inputParameters
            ], 200);

        } catch (\Exception $e) {
            $logger->error("apiUploadFile error: " . $e->getMessage());
            return new JsonResponse([
                'status' => 'error',
                'error' => 'Failed to upload file: ' . $e->getMessage()
            ], 500);
        }
        return new JsonResponse([
            'status' => 'success',
            'message' => $inputParameters
        ], 200);
    }

    //[Route(path: '/reset-demo-data/', name: 'employees_reset_demo_data', methods: ['GET'])]
    #[Route(path: '/reset-demo-data-ajax/', name: 'employees_reset_demo_data_ajax', methods: ['POST'])]
    public function resetDemoDataAction(Request $request)
    {
        $userSecUtil = $this->container->get('user_security_utility');
        $environment = $userSecUtil->getSiteSettingParameter('environment');
        if( $environment == 'live' ) {
            //exit("Demo DB cannot be run in live environment");
            $this->addFlash(
                'pnotify-error',
                "Demo DB cannot be run in live environment"
            );
            return $this->redirect($this->generateUrl('employees_manual_backup_restore'));
            //return false;
        }

        if (!$this->isGranted('ROLE_PLATFORM_ADMIN')) {
            return $this->redirect($this->generateUrl('employees-nopermission'));
        }

        //Flash
        $this->addFlash(
            'notice',
            "Demo Data"
        );


        //networkDrivePath
        $userSecUtil = $this->container->get('user_security_utility');
        $networkDrivePath = $userSecUtil->getSiteSettingParameter('networkDrivePath');
        //echo "networkDrivePath=".$networkDrivePath."<br>";
        if( !$networkDrivePath ) {
            //exit("No networkDrivePath is defined");
            $this->addFlash(
                'pnotify-error',
                //'notice',
                "Cannot continue with Backup: No Network Drive Path is defined in the Site Settings"
            );
            return $this->redirect($this->generateUrl('employees_manual_backup_restore'));
        }

        if( $networkDrivePath ) {

            //create backup
            //$res = $this->creatingBackupSQLFull($networkDrivePath); //Use php based pg_dump
            // $res = $this->dbManagePython($networkDrivePath,'backup'); //Use python script pg_dump
            $userServiceUtil = $this->container->get('user_service_utility');
            $res = $userServiceUtil->dbManagePython($networkDrivePath,'backup'); //Working: Use python script pg_dump
            //exit($res);

            $resStatus = $res['status'];
            $resStr = $res['message'];

            if( $resStatus == 'OK' ) {
                $resStr = "Backup successfully created in folder $networkDrivePath";
                $this->addFlash(
                    'notice',
                    $resStr
                );

                //Event Log
                $user = $this->getUser();
                $sitename = $this->getParameter('employees.sitename');
                $userSecUtil->createUserEditEvent($sitename,$resStr,$user,null,$request,'Create Backup Database');

                ///// Run demo db generation /////



                ///// EOF Run demo db generation /////

//                $env = 'demo';
//                $backupFileName = '';
//                $output = $this->restoreDBWrapper($backupFileName,$env);
//                if( $output['status'] == 'OK' ) {
//                    $this->addFlash(
//                        'notice',
//                        "Demo DB restored: ".$output['message']
//                    );
//                } else {
//                    $this->addFlash(
//                        'notice',
//                        "Error Demo DB: ".$output['message']
//                    );
//                }

            } else {
                $this->addFlash(
                    'pnotify-error',
                    $resStr
                );
            }

        } else {
            $this->addFlash(
                'pnotify-error',
                "Error backup"
            );
        }

        return $this->redirect($this->generateUrl('employees_manual_backup_restore'));


        //return $this->redirectToRoute('employees_home');
    }

    //NOT USED
    #[Route(path: '/demo-data-test/', name: 'employees_demo_data_test', methods: ['GET'])]
    public function testAction( Request $request, TokenStorageInterface $tokenStorage ) {

//        //authenticate systemuser
//        $logger = $this->container->get('logger');
//        $userSecUtil = $this->container->get('user_security_utility');
//        $firewall = 'ldap_fellapp_firewall';
//        $systemUser = $userSecUtil->findSystemUser();
//        if( $systemUser ) {
//            //$token = new UsernamePasswordToken($systemUser, null, $firewall, $systemUser->getRoles());
//            $token = new UsernamePasswordToken($systemUser, $firewall, $systemUser->getRoles());
//            //$this->container->get('security.token_storage')->setToken($token);
//            $tokenStorage->setToken($token);
//        }
//        $logger->notice("testAction: Logged in as systemUser=".$systemUser);
//        if( $this->isGranted('ROLE_PLATFORM_DEPUTY_ADMIN') ) { //ROLE_USER
//            $logger->notice("testAction: systemUser is ROLE_PLATFORM_DEPUTY_ADMIN");
//        }

        // makes a real request to an external site
        $browser = new HttpBrowser(HttpClient::create());
        //$crawler = $browser->request('GET', '/directory/user/new');
        $crawler = $browser->request('GET', $this->baseUrl.'/directory/login');

        //$content = $this->client->getResponse()->getContent();
        //dump($crawler);
        //exit("content");

        // select the form and fill in some values
        $form = $crawler->selectButton('Log In')->form();
        $form['_usernametype'] = 'local-user';
        $form['_display-username'] = 'administrator';
        $form['_password'] = 'demo';

        // submits the given form
        $crawler = $browser->submit($form);

        dump($crawler);
        exit('111');
    }

    //NOT USED
    #[Route(path: '/demo-data-panther/{password}', name: 'employees_demo_data_panther', methods: ['GET'])]
    public function testPantherAction( Request $request, TokenStorageInterface $tokenStorage, $password=null ) {
        //$client = Client::createChromeClient();
        // alternatively, create a Firefox client
        //$client = Client::createFirefoxClient();

        $demoDbUtil = $this->container->get('demodb_utility');

        $client = $demoDbUtil->loginAction($password);
        $client->takeScreenshot('demoDb/test_login.png');

        //$users = $demoDbUtil->createUsers($client);
        //$client->takeScreenshot('demoDb/test_createuser.png');

        $users = $demoDbUtil->getUsers(); //testing

        ///////////// TRP /////////////////
        if(0) {
            $projectIds = array(1);
            if (1) {
                $projectIds = $demoDbUtil->newTrpProjects($client, $users);
                if (count($projectIds) == 0) {
                    exit('Error generating new TRP project');
                }
            }

            if (1) {
                $demoDbUtil->approveTrpProjects($client, $projectIds);
            }

            $requestIds = array(18);
            if (1) {
                $requestIds = $demoDbUtil->newTrpWorkRequests($client, $projectIds);
            }

            $invoiceIds = $demoDbUtil->newTrpInvoices($client, $requestIds);
        }
        ///////////// EOF TRP /////////////////


        
        ///////////// Fellowship App /////////////////
        if( 0 ) {
            $fellappIds = $demoDbUtil->newFellApps($client, $users);
        }
        ///////////// EOF Fellowship App /////////////////

        ///////////// VacReq /////////////////
        if( 1 ) {
            $vacreqIds = $demoDbUtil->newVacReqs($client, $users);
        }
        ///////////// EOF VacReq /////////////////


        exit('eof panther');
    }
    

}
