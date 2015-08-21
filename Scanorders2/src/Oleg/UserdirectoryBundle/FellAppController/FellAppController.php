<?php

namespace Oleg\UserdirectoryBundle\FellAppController;


use Oleg\OrderformBundle\Helper\ErrorHelper;
use Oleg\UserdirectoryBundle\Entity\BoardCertification;
use Oleg\UserdirectoryBundle\Entity\Citizenship;
use Oleg\UserdirectoryBundle\Entity\Countries;
use Oleg\UserdirectoryBundle\Entity\CurriculumVitae;
use Oleg\UserdirectoryBundle\Entity\Document;
use Oleg\UserdirectoryBundle\Entity\EmploymentStatus;
use Oleg\UserdirectoryBundle\Entity\Examination;
use Oleg\UserdirectoryBundle\Entity\FellowshipApplication;
use Oleg\UserdirectoryBundle\Entity\FellowshipSubspecialty;
use Oleg\UserdirectoryBundle\Entity\GeoLocation;
use Oleg\UserdirectoryBundle\Entity\Location;
use Oleg\UserdirectoryBundle\Entity\Reference;
use Oleg\UserdirectoryBundle\Entity\StateLicense;
use Oleg\UserdirectoryBundle\Entity\Training;
use Oleg\UserdirectoryBundle\Entity\User;
use Oleg\UserdirectoryBundle\Form\DataTransformer\GenericManytomanyTransformer;
use Oleg\UserdirectoryBundle\Form\DataTransformer\GenericTreeTransformer;
use Oleg\UserdirectoryBundle\Form\FellAppFilterType;
use Oleg\UserdirectoryBundle\Form\FellowshipApplicationType;
use Oleg\UserdirectoryBundle\Util\UserUtil;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Form\Extension\Core\DataTransformer\DateTimeToStringTransformer;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;



class FellAppController extends Controller {

    /**
     * Show home page
     *
     * @Route("/", name="fellapp_home")
     * @Template("OlegUserdirectoryBundle:FellApp:home.html.twig")
     */
    public function indexAction(Request $request) {

        if(
            false == $this->get('security.context')->isGranted('ROLE_USER') ||              // authenticated (might be anonymous)
            false == $this->get('security.context')->isGranted('IS_AUTHENTICATED_FULLY')    // authenticated (NON anonymous)
        ){
            return $this->redirect( $this->generateUrl('login') );
        }

        //echo "fellapp home <br>";

        $em = $this->getDoctrine()->getManager();

        //create fellapp filter
        $params = array( 'fellTypes' => $this->getFellowshipTypesWithSpecials() );
        $filterform = $this->createForm(new FellAppFilterType($params), null);

        $filterform->bind($request);  //use bind instead of handleRequest. handleRequest does not get filter data

        $search = $filterform['search']->getData();
        $filter = $filterform['filter']->getData();
        $startDate = $filterform['startDate']->getData();
        $hidden = $filterform['hidden']->getData();
        $archived = $filterform['archived']->getData();
        //$page = $request->get('page');
        //echo "<br>startDate=".$startDate."<br>";
        //echo "<br>filter=".$filter."<br>";
        //echo "<br>search=".$search."<br>";

        //$fellApps = $em->getRepository('OlegUserdirectoryBundle:FellowshipApplication')->findAll();
        $repository = $this->getDoctrine()->getRepository('OlegUserdirectoryBundle:FellowshipApplication');
        $dql =  $repository->createQueryBuilder("fellapp");
        $dql->select('fellapp');
        //$dql->groupBy('fellapp');
        $dql->orderBy("fellapp.timestamp","DESC");
        $dql->leftJoin("fellapp.fellowshipSubspecialty", "fellowshipSubspecialty");
        $dql->leftJoin("fellapp.user", "applicant");
        $dql->leftJoin("applicant.infos", "applicantinfos");
        $dql->leftJoin("applicant.credentials", "credentials");
        $dql->leftJoin("credentials.examinations", "examinations");

        if( $search ) {
            $dql->leftJoin("applicant.infos", "userinfos");
            $dql->andWhere("userinfos.firstName LIKE '%".$search."%' OR userinfos.lastName LIKE '%".$search."%'");
        }

        if( $filter && $filter != "ALL" ) {
            $dql->andWhere("fellowshipSubspecialty.id = ".$filter);
        }

        if( !$hidden ) {
            $dql->andWhere("fellapp.applicationStatus != 'hide'");
        }

        if( !$archived ) {
            $dql->andWhere("fellapp.applicationStatus != 'archive'");
        }

        if( $startDate ) {
            //$transformer = new DateTimeToStringTransformer(null,null,'Y-m-d');
            //$dateStr = $transformer->transform($startDate);
            //$dql->andWhere("fellapp.startDate >= '".$startDate."'");
            //$dql->andWhere("year(fellapp.startDate) = '".$startDate->format('Y')."'");
            $bottomDate = "01-01-".$startDate->format('Y');
            $topDate = "12-31-".$startDate->format('Y');
            $dql->andWhere("fellapp.startDate BETWEEN '" . $bottomDate . "'" . " AND " . "'" . $topDate . "'" );
        }


        //echo "dql=".$dql."<br>";

        $limit = 10;
        $query = $em->createQuery($dql);
        $paginator  = $this->get('knp_paginator');
        $fellApps = $paginator->paginate(
            $query,
            $this->get('request')->query->get('page', 1), /*page number*/
            //$request->query->getInt('page', 1),
            $limit      /*limit per page*/
        );


        $em = $this->getDoctrine()->getManager();
        $eventtype = $em->getRepository('OlegUserdirectoryBundle:EventTypeList')->findOneByName("Import of Fellowship Applications");
        $lastImportTimestamps = $this->getDoctrine()->getRepository('OlegUserdirectoryBundle:Logger')->findBy(array('eventType'=>$eventtype),array('creationdate'=>'DESC'),1);
        if( count($lastImportTimestamps) != 1 ) {
            $lastImportTimestamp = null;
        } else {
            $lastImportTimestamp = $lastImportTimestamps[0]->getCreationdate();
        }

        return array(
            'entities' => $fellApps,
            'pathbase' => 'fellapp',
            'lastImportTimestamp' => $lastImportTimestamp,
            'fellappfilter' => $filterform->createView(),
            'startDate' => $startDate
        );
    }

    public function getFellowshipTypesWithSpecials() {
        $em = $this->getDoctrine()->getManager();

        //get list of fellowship type with extra "ALL"
        $repository = $this->getDoctrine()->getRepository('OlegUserdirectoryBundle:FellowshipSubspecialty');
        $dql = $repository->createQueryBuilder('list')
            //->select("list.id as id, list.name as text")
            ->where("list.type = :typedef OR list.type = :typeadd")
            ->orderBy("list.orderinlist","ASC");
        $query = $em->createQuery($dql);
        $query->setParameters( array(
            'typedef' => 'default',
            'typeadd' => 'user-added',
        ));
        $fellTypes = $query->getResult();

        //add special cases
        $specials = array(
            "ALL" => "ALL",
        );

        $filterType = array();
        foreach( $specials as $key => $value ) {
            $filterType[$key] = $value;
        }

        //add statuses
        foreach( $fellTypes as $type ) {
            //echo "type: id=".$status->getId().", name=".$status->getName()."<br>";
            $filterType[$type->getId()] = $type->getName();
        }

        return $filterType;
    }


    /**
     * @Route("/show/{id}", name="fellapp_show")
     * @Route("/edit/{id}", name="fellapp_edit")
     *
     * @Template("OlegUserdirectoryBundle:FellApp:new.html.twig")
     */
    public function showAction(Request $request, $id) {

        if(
            false == $this->get('security.context')->isGranted('ROLE_USER') ||              // authenticated (might be anonymous)
            false == $this->get('security.context')->isGranted('IS_AUTHENTICATED_FULLY')    // authenticated (NON anonymous)
        ){
            return $this->redirect( $this->generateUrl('login') );
        }

        //echo "fellapp home <br>";

        $user = $this->get('security.context')->getToken()->getUser();
        $em = $this->getDoctrine()->getManager();

        //$fellApps = $em->getRepository('OlegUserdirectoryBundle:FellowshipApplication')->findAll();
        $entity = $this->getDoctrine()->getRepository('OlegUserdirectoryBundle:FellowshipApplication')->find($id);

        if( !$entity ) {
            throw $this->createNotFoundException('Unable to find entity by id='.$id);
        }


        $routeName = $request->get('_route');

        if( $routeName == "fellapp_show" ) {
            $cycle = 'show';
            $disabled = true;
            $method = "GET";
            $action = $this->generateUrl('fellapp_edit', array('id' => $entity->getId()));
        }

        if( $routeName == "fellapp_edit" ) {
            $cycle = 'edit';
            $disabled = false;
            $method = "PUT";
            $action = $this->generateUrl('fellapp_update', array('id' => $entity->getId()));
        }

        $params = array(
            'cycle' => $cycle,
            'sc' => $this->get('security.context'),
            'em' => $em,
            'user' => $entity->getUser(),
            'cloneuser' => null,
            'roles' => $user->getRoles()
        );

        $form = $this->createForm(
            new FellowshipApplicationType($params),
            $entity,
            array(
                'disabled' => $disabled,
                'method' => $method,
                'action' => $action
            )
        );


        return array(
            'form' => $form->createView(),
            'entity' => $entity,
            'pathbase' => 'fellapp',
            'cycle' => $cycle,
            'sitename' => $this->container->getParameter('fellapp.sitename')
        );
    }


    /**
     * @Route("/update/{id}", name="fellapp_update")
     * @Method("PUT")
     * @Template("OlegUserdirectoryBundle:FellApp:new.html.twig")
     */
    public function editAction(Request $request, $id) {

        if(
            false == $this->get('security.context')->isGranted('ROLE_USER') ||              // authenticated (might be anonymous)
            false == $this->get('security.context')->isGranted('IS_AUTHENTICATED_FULLY')    // authenticated (NON anonymous)
        ){
            return $this->redirect( $this->generateUrl('login') );
        }

        echo "update <br>";
        //exit('update');

        $entity = $this->getDoctrine()->getRepository('OlegUserdirectoryBundle:FellowshipApplication')->find($id);

        if( !$entity ) {
            throw $this->createNotFoundException('Unable to find entity by id='.$id);
        }

        $cycle = 'edit';
        $user = $this->get('security.context')->getToken()->getUser();

        $params = array(
            'cycle' => $cycle,
            'sc' => $this->get('security.context'),
            'em' => $this->getDoctrine()->getManager(),
            'user' => $entity->getUser(),
            'cloneuser' => null,
            'roles' => $user->getRoles()
        );

        $form = $this->createForm( new FellowshipApplicationType($params), $entity );

        $form->handleRequest($request);

        if( !$form->isSubmitted() ) {
            echo "form is not submitted<br>";
            $form->submit($request);
        }


//        if ($form->isDisabled()) {
//            echo "form is disabled<br>";
//        }
//        if (count($form->getErrors(true)) > 0) {
//            echo "form has errors<br>";
//        }
//        echo "errors:<br>";
//        $string = (string) $form->getErrors(true);
//        echo "string errors=".$string."<br>";
//        echo "getErrors count=".count($form->getErrors())."<br>";
//        echo "getErrorsAsString()=".$form->getErrorsAsString()."<br>";
        //print_r($form->getErrors());
//        echo "<br>string errors:<br>";
//        print_r($form->getErrorsAsString());
//        echo "<br>";
//        exit();

        if(0) {
            $errorHelper = new ErrorHelper();
            $errors = $errorHelper->getErrorMessages($form);
            echo "<br>form errors:<br>";
            print_r($errors);
        }

        if( $form->isValid() ) {

            //exit('form valid');

            $this->processDocuments($entity);

            //set update author application
            $em = $this->getDoctrine()->getManager();
            $userUtil = new UserUtil();
            $sc = $this->get('security.context');
            $userUtil->setUpdateInfo($entity,$em,$sc);

            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();

            //set logger for update
            $userSecUtil = $this->container->get('user_security_utility');
            $systemUser = $userSecUtil->findSystemUser();
            $event = "Fellowship Application with ID " . $id . " has been updated by " . $user;
            $userSecUtil->createUserEditEvent($this->container->getParameter('fellapp.sitename'),$event,$systemUser,$entity,$request,'Fellowship Application Updated');

            return $this->redirect($this->generateUrl('fellapp_show',array('id' => $entity->getId())));
        }

        //echo 'form invalid <br>';
        //exit('form invalid');

        return array(
            'form' => $form->createView(),
            'entity' => $entity,
            'pathbase' => 'fellapp',
            'cycle' => $cycle
        );


    }

    //process upload documents: CurriculumVitae(documents), FellowshipApplication(coverLetters), Examination(scores), FellowshipApplication(lawsuitDocuments), FellowshipApplication(reprimandDocuments)
    public function processDocuments($application) {

        $em = $this->getDoctrine()->getManager();

        $userUtil = new UserUtil();
        $sc = $this->get('security.context');

        $subjectUser = $application->getUser();

        //CurriculumVitae
        foreach( $subjectUser->getCredentials()->getCvs() as $cv ) {
            $em->getRepository('OlegUserdirectoryBundle:Document')->processDocuments( $cv );
        }

        //FellowshipApplication(coverLetters)
        $em->getRepository('OlegUserdirectoryBundle:Document')->processDocuments( $application, 'coverLetter' );
        $em->getRepository('OlegUserdirectoryBundle:Document')->processDocuments( $application, 'lawsuitDocument');
        $em->getRepository('OlegUserdirectoryBundle:Document')->processDocuments( $application, 'reprimandDocument' );

        //Examination
        foreach( $subjectUser->getCredentials()->getExaminations() as $examination ) {
            $em->getRepository('OlegUserdirectoryBundle:Document')->processDocuments( $examination );
        }

    }


    /**
     * @Route("/status/{id}/{status}", name="fellapp_status")
     */
    public function statusAction($id,$status) {

        if(
            false == $this->get('security.context')->isGranted('ROLE_USER') ||              // authenticated (might be anonymous)
            false == $this->get('security.context')->isGranted('IS_AUTHENTICATED_FULLY')    // authenticated (NON anonymous)
        ){
            return $this->redirect( $this->generateUrl('login') );
        }

        echo "status <br>";

        $em = $this->getDoctrine()->getManager();

        //$fellApps = $em->getRepository('OlegUserdirectoryBundle:FellowshipApplication')->findAll();
        $entity = $this->getDoctrine()->getRepository('OlegUserdirectoryBundle:FellowshipApplication')->find($id);

        if( !$entity ) {
            throw $this->createNotFoundException('Unable to find entity by id='.$id);
        }


        $entity->setApplicationStatus($status);

        $em->persist($entity);
        $em->flush();

        return $this->redirect( $this->generateUrl('fellapp_home') );
    }


    /**
     * @Route("/resend-emails/{id}", name="fellapp_resendemails")
     */
    public function resendemailsAction(Request $request, $id) {

        if(
            false == $this->get('security.context')->isGranted('ROLE_USER') ||              // authenticated (might be anonymous)
            false == $this->get('security.context')->isGranted('IS_AUTHENTICATED_FULLY')    // authenticated (NON anonymous)
        ){
            return $this->redirect( $this->generateUrl('login') );
        }

        echo "resendemails <br>";

        $em = $this->getDoctrine()->getManager();

        $entity = $this->getDoctrine()->getRepository('OlegUserdirectoryBundle:FellowshipApplication')->find($id);

        if( !$entity ) {
            throw $this->createNotFoundException('Unable to find entity by id='.$id);
        }


        $userSecUtil = $this->container->get('user_security_utility');
        $systemUser = $userSecUtil->findSystemUser();
        $event = "Resend emails for fellowship application ID " . $id;
        $userSecUtil->createUserEditEvent($this->container->getParameter('fellapp.sitename'),$event,$systemUser,$entity,$request,'Fellowship Application Resend Emails');

        return $this->redirect( $this->generateUrl('fellapp_home') );
    }


    /**
     * @Route("/remove/{id}", name="fellapp_remove")
     */
    public function removeAction($id) {

        if( false == $this->get('security.context')->isGranted('ROLE_PLATFORM_ADMIN') ){
            return $this->redirect( $this->generateUrl('fellapp-nopermission') );
        }

        echo "remove <br>";
        exit('not supported');

        $em = $this->getDoctrine()->getManager();

        $entity = $this->getDoctrine()->getRepository('OlegUserdirectoryBundle:FellowshipApplication')->find($id);

        if( !$entity ) {
            throw $this->createNotFoundException('Unable to find entity by id='.$id);
        }

        if(0) {
            $user = $entity->getUser();

            $entity->setApplicationStatus('archive');

            $user->setUsernameForce($user->getUsername()."-deleted");

            $user->setPrimaryPublicUserId($user->getPrimaryPublicUserId()."-deleted");

            $avatar = $user->getAvatar();
            $em->persist($avatar);
            $em->remove($avatar);
            $user->setAvatar(NULL);

            //$em->remove($entity);

            $em->flush();
        }

        return $this->redirect( $this->generateUrl('fellapp_home') );



        $credentials = $user->getCredentials();

        echo "training count=".count($user->getTrainings())."<br>";
        foreach( $user->getTrainings() as $training  ) {
            echo "remove training<br>";
            $user->remove($training);
            $em->remove($training);
            //$em->flush();
        }

        foreach( $entity->getCoverLetters() as $item ) {
            $entity->removeCoverLetter($item);
            $em->remove($item);
        }

        //$em->remove($credentials);
        //$em->flush();

        //exit('ok');

        //$em->remove($user);
        //$em->flush();

        $em->remove($entity);
        $em->flush();


        return $this->redirect( $this->generateUrl('fellapp_home') );
    }


    /**
     * Show home page
     *
     * @Route("/populate", name="fellapp_populate")
     */
    public function populateSpreadsheetAction(Request $request) {

        $fellappUtil = $this->container->get('fellapp_import');
        $populatedCount = $fellappUtil->populateFellApp();

        if( $populatedCount >= 0 ) {
            $event = "Populated ".$populatedCount." Fellowship Applicantions.";
            $flashType = 'notice';
        } else {
            $event = "Google API service failed!";
            $flashType = 'warning';
        }

        $this->get('session')->getFlashBag()->add(
            $flashType,
            $event
        );

        return $this->redirect( $this->generateUrl('fellapp_home') );
    }


    /**
     * Import spreadsheet to C:\Program Files (x86)\Aperio\Spectrum\htdocs\order\scanorder\Scanorders2\web\Uploaded\fellapp\Spreadsheets
     *
     * @Route("/import", name="fellapp_import")
     */
    public function importAction(Request $request) {

        $fellappUtil = $this->container->get('fellapp_import');
        $fileDb = $fellappUtil->importFellApp();

        if( $fileDb ) {
            $event = "Fellowship Application Spreadsheet file has been successful downloaded to the server with id=" . $fileDb->getId().", title=".$fileDb->getUniquename();
            $flashType = 'notice';
        } else {
            $event = "Fellowship Application Spreadsheet download failed!";
            $flashType = 'warning';
        }

        $this->get('session')->getFlashBag()->add(
            $flashType,
            $event
        );

        //exit('import event'.$event);

        return $this->redirect( $this->generateUrl('fellapp_home') );

//        //$excelFile = $this->printFile($service, $excelId);
//
//        //$response = $this->downloadFile($service, $excelFile, 'excel');
//
//        //echo "response=".$response."<br>";
//
//        exit(1);
//
//
////        $files = $service->files->listFiles();
////        echo "count files=".count($files)."<br>";
////        //echo "<pre>"; print_r($files);
////        foreach( $files as $item ) {
////            echo "title=".$item['title']."<br>";
////        }
//
//        //https://drive.google.com/open?id=0B2FwyaXvFk1edWdMdTlFTUt1aVU
//        $folderId = "0B2FwyaXvFk1edWdMdTlFTUt1aVU";
//        //https://drive.google.com/open?id=0B2FwyaXvFk1efmc2VGVHUm5yYjJRWGFYYTF0Z2N6am9iUFVzcTc1OXdoWEl1Vmc0LWdZc0E
//        //$folderId = "0B2FwyaXvFk1efmc2VGVHUm5yYjJRWGFYYTF0Z2N6am9iUFVzcTc1OXdoWEl1Vmc0LWdZc0E";
//        //$files = $this->printFilesInFolder($service, $folderId);
//
//
//        $photoId = "0B2FwyaXvFk1eRnJVS1N0MWhkc0E";
//        $file = $this->printFile($service, $photoId);
//        $response = $this->downloadFile($service, $file);
//        echo "response=".$response."<br>";
//
//        exit('1');
//
//        // Exchange authorization code for access token
//        //$accessToken = $client->authenticate($authCode);
//        //$client->setAccessToken($accessToken);
//
//        $fileId = "1DN1BEbONKNmFpHU6xBo69YSLjXCnhRy0IbyXrwMzEzc";
//
//        $file = $this->printFile($service, $fileId);
//
//        echo "after file <br>";
//
//        $response = $this->downloadFile($service,$file);
//
//        print_r($response);
//
//        echo "response=".$response."<br>";
//        //exit();
//        return $response;
//
//        return $this->redirect( $this->generateUrl('fellapp_home') );
    }







    ///////////////////// un used methods //////////////////////////
    /**
     * Print files belonging to a folder.
     *
     * @param Google_Service_Drive $service Drive API service instance.
     * @param String $folderId ID of the folder to print files from.
     */
    function printFilesInFolder($service, $folderId) {
        $pageToken = NULL;

        do {
            try {
                $parameters = array();
                if ($pageToken) {
                    $parameters['pageToken'] = $pageToken;
                }
                $children = $service->children->listChildren($folderId, $parameters);
                echo "count=".count($children->getItems())."<br>";

                foreach ($children->getItems() as $child) {
                    //print 'File Id: ' . $child->getId()."<br>";
                    //print_r($child);
                    $this->printFile($service,$child->getId());
                }
                $pageToken = $children->getNextPageToken();
            } catch (Exception $e) {
                print "An error occurred: " . $e->getMessage();
                $pageToken = NULL;
            }
        } while ($pageToken);
    }

    function getFilesByAuthUrl() {
        $client_id = "1040591934373-hhm896qpgdaiiblaco9jdfvirkh5f65q.apps.googleusercontent.com";
        $client_secret = "RgXkEm2_1T8yKYa3Vw_tIhoO";
        $redirect_uri = 'urn:ietf:wg:oauth:2.0:oob';    //"http://localhost";

        $res = $this->buildService($client_id,$client_secret,$redirect_uri);

        $service = $res['service'];
        $client = $res['client'];

        $authUrl = $client->createAuthUrl();
        echo "authUrl=".$authUrl."<br>";

        // Exchange authorization code for access token
        $accessToken = $client->authenticate('4/OrVeRdkw9eByckCs7Gtn0B4eUwhERny8AqFOAwy29fY');
        $client->setAccessToken($accessToken);

        $files = $service->files->listFiles();
        echo "count files=".count($files)."<br>";
        echo "<pre>"; print_r($files);
    }

    /**
     * Build a Drive service object.
     */
    function buildService($client_id,$client_secret,$redirect_uri) {
        $client = new \Google_Client();
        $client->setClientId($client_id);
        $client->setClientSecret($client_secret);
        $client->setRedirectUri($redirect_uri);

        //$client->addScope("https://www.googleapis.com/auth/drive");
        $client->setScopes(array('https://www.googleapis.com/auth/drive'));
        $client->setAccessType('offline');

        $service = new \Google_Service_Drive($client);

        $res = array(
            'client' => $client,
            'service' => $service
        );
        return $res;
    }

    /**
     * Print a file's metadata.
     *
     * @param apiDriveService $service Drive API service instance.
     * @param string $fileId ID of the file to print metadata for.
     */
    function printFile($service, $fileId) {
        $file = null;
        try {
            $file = $service->files->get($fileId);

            print "Title: " . $file->getTitle()."<br>";
            print "ID: " . $file->getId()."<br>";
            print "Size: " . $file->getFileSize()."<br>";
            //print "URL: " . $file->getDownloadUrl()."<br>";
            print "Description: " . $file->getDescription()."<br>";
            print "MIME type: " . $file->getMimeType()."<br>"."<br>";

        } catch (Exception $e) {
            print "An error occurred: " . $e->getMessage();
        }
        return $file;
    }



    /**
     * Show home page
     *
     * @Route("/test", name="fellapp_test")
     * @Method("GET")
     */
    public function testAction() {

        //include_once "vendor/google/apiclient/examples/simple-query.php";
        include_once "vendor/google/apiclient/examples/user-example.php";
        //include_once "vendor/google/apiclient/examples/idtoken.php";



        return new Response("OK Test");
    }

}
