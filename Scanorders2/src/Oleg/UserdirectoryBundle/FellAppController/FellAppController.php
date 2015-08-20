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

        //create filters
        $filterform = $this->createForm(new FellAppFilterType(), null);

        $filterform->bind($request);  //use bind instead of handleRequest. handleRequest does not get filter data

        $search = $filterform['search']->getData();
        $filter = $filterform['filter']->getData();
        $startDate = $filterform['startDate']->getData();
        $page = $request->get('page');
        //echo "<br>startDate=".$startDate."<br>";
        //echo "<br>filter=".$filter->getId()."<br>";
        //echo "<br>search=".$search."<br>";

        //$fellApps = $em->getRepository('OlegUserdirectoryBundle:FellowshipApplication')->findAll();
        $repository = $this->getDoctrine()->getRepository('OlegUserdirectoryBundle:FellowshipApplication');
        $dql =  $repository->createQueryBuilder("fellapp");
        $dql->select('fellapp');
        //$dql->groupBy('fellapp');
        $dql->orderBy("fellapp.timestamp","DESC");
        $dql->leftJoin("fellapp.fellowshipSubspecialty", "fellowshipSubspecialty");
        $dql->leftJoin("fellapp.user", "applicant");
        $dql->leftJoin("applicant.credentials", "credentials");
        $dql->leftJoin("credentials.examinations", "examinations");

        if( $search ) {
            $dql->leftJoin("applicant.infos", "userinfos");
            $dql->andWhere("userinfos.firstName LIKE '%".$search."%' OR userinfos.lastName LIKE '%".$search."%'");
        }

        if( $filter ) {
            $dql->andWhere("fellowshipSubspecialty.id = ".$filter->getId());
        }

        if( $startDate ) {
            //$transformer = new DateTimeToStringTransformer(null,null,'Y-m-d');
            //$dateStr = $transformer->transform($startDate);
            //$dql->andWhere("fellapp.startDate >= '".$dateStr."'");
            $dql->andWhere("fellapp.startDate >= '".$startDate->format('Y-m-d')."'");
        }

        $limit = 100;
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
            'fellappfilter' => $filterform->createView()
        );
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

        $em = $this->getDoctrine()->getManager();

        $entity = $this->getDoctrine()->getRepository('OlegUserdirectoryBundle:FellowshipApplication')->find($id);

        if( !$entity ) {
            throw $this->createNotFoundException('Unable to find entity by id='.$id);
        }

        $user = $entity->getUser();

        $credentials = $user->getCredentials();

        echo "training count=".count($user->getTrainings())."<br>";
        foreach( $user->getTrainings() as $training  ) {
            echo "remove training<br>";
            $em->remove($training);
            $em->flush();
        }


        $em->remove($credentials);
        $em->flush();

        exit('ok');

        $em->remove($user);
        $em->flush();

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

        echo "fellapp populateSpreadsheet <br>";

        //get latest spreadsheet file from Uploaded/fellapp/Spreadsheets
        $em = $this->getDoctrine()->getManager();
        $fellappSpreadsheetType = $em->getRepository('OlegUserdirectoryBundle:DocumentTypeList')->findOneByName('Fellowship Application Spreadsheet');
        $documents = $em->getRepository('OlegUserdirectoryBundle:Document')->findBy(
            array('type' => $fellappSpreadsheetType),
            array('createdate'=>'desc'),
            1   //limit to one
        );

        if( count($documents) == 1 ) {
            $document = $documents[0];
        }

        $inputFileName = $document->getServerPath();    //'Uploaded/fellapp/Spreadsheets/Pathology Fellowships Application Form (Responses).xlsx';
        $populatedCount = $this->populateSpreadsheet($request,$inputFileName);

        $userSecUtil = $this->container->get('user_security_utility');
        $systemUser = $userSecUtil->findSystemUser();
        $event = "Populated fellowship applicantions " . $populatedCount;
        $userSecUtil->createUserEditEvent($this->container->getParameter('fellapp.sitename'),$event,$systemUser,null,$request,'Populate of Fellowship Applications');

        $this->get('session')->getFlashBag()->add(
            'notice',
            $event
        );

        return $this->redirect( $this->generateUrl('fellapp_home') );
    }


    public function populateSpreadsheet( $request, $inputFileName ) {

        echo "inputFileName=".$inputFileName."<br>";

        ini_set('max_execution_time', 3000); //30000 seconds = 50 minutes

        $service = $this->getGoogleService();
        if( !$service ) {
            $this->get('session')->getFlashBag()->add(
                'warning',
                "Google API service failed!"
            );
            $logger = $this->container->get('logger');
            $logger->warning("Google API service failed!");

            return $this->redirect( $this->generateUrl('fellapp_home') );
        }

        $uploadPath = 'Uploaded/fellapp/FellowshipApplicantUploads/';

        try {
            $inputFileType = \PHPExcel_IOFactory::identify($inputFileName);
            $objReader = \PHPExcel_IOFactory::createReader($inputFileType);
            $objPHPExcel = $objReader->load($inputFileName);
        } catch(Exception $e) {
            throw new IOException('Error loading file "'.pathinfo($inputFileName,PATHINFO_BASENAME).'": '.$e->getMessage());
        }

        //$sheetData = $objPHPExcel->getActiveSheet()->toArray(null,true,true,true);
        //var_dump($sheetData);

        $em = $this->getDoctrine()->getManager();
        $default_time_zone = $this->container->getParameter('default_time_zone');

        $userSecUtil = $this->container->get('user_security_utility');
        $userkeytype = $userSecUtil->getUsernameType('local-user');
        if( !$userkeytype ) {
            throw $this->createNotFoundException('Unable to find local user keytype');
        }

        $employmentType = $em->getRepository('OlegUserdirectoryBundle:EmploymentType')->findOneByName("Pathology Fellowship Applicant");
        if( !$employmentType ) {
            throw $this->createNotFoundException('Unable to find entity by name='."Pathology Fellowship Applicant");
        }
        $presentLocationType = $em->getRepository('OlegUserdirectoryBundle:LocationTypeList')->findOneByName("Present Address");
        if( !$presentLocationType ) {
            throw $this->createNotFoundException('Unable to find entity by name='."Present Address");
        }
        $permanentLocationType = $em->getRepository('OlegUserdirectoryBundle:LocationTypeList')->findOneByName("Permanent Address");
        if( !$permanentLocationType ) {
            throw $this->createNotFoundException('Unable to find entity by name='."Permanent Address");
        }
        $workLocationType = $em->getRepository('OlegUserdirectoryBundle:LocationTypeList')->findOneByName("Work Address");
        if( !$workLocationType ) {
            throw $this->createNotFoundException('Unable to find entity by name='."Work Address");
        }


        ////////////// add system user /////////////////
        $systemUser = $userSecUtil->findSystemUser();
        ////////////// end of add system user /////////////////

        $sheet = $objPHPExcel->getSheet(0);
        $highestRow = $sheet->getHighestRow();
        $highestColumn = $sheet->getHighestColumn();

        $headers = $rowData = $sheet->rangeToArray('A' . 1 . ':' . $highestColumn . 1,
            NULL,
            TRUE,
            FALSE);
        //print_r($headers);

        $count = 0;

        //for each user in excel
        for ($row = 3; $row <= $highestRow; $row++){

            $count++;

            //  Read a row of data into an array
            $rowData = $sheet->rangeToArray('A' . $row . ':' . $highestColumn . $row,
                NULL,
                TRUE,
                FALSE);

            //print_r($rowData);

            //$id = $rowData[0][0];
            $id = $this->getValueByHeaderName('ID',$rowData,$headers);
            echo "row=".$row.": id=".$id."<br>";

            //check if the user already exists in DB by $id
            $user = $em->getRepository('OlegUserdirectoryBundle:User')->findOneByPrimaryPublicUserId($id);
            if( $user ) {
                //skip this applicant because it's already exists in DB
                continue;
            }

            //create excel user
            $addobjects = false;
            $user = new User($addobjects);
            $user->setKeytype($userkeytype);
            $user->setPrimaryPublicUserId($id);

            //set unique username
            $usernameUnique = $user->createUniqueUsername();
            $user->setUsername($usernameUnique);
            $user->setUsernameCanonical($usernameUnique);

            $email = $this->getValueByHeaderName('email',$rowData,$headers);
            //echo "email=".$email."<br>";

            $lastName = $this->getValueByHeaderName('lastName',$rowData,$headers);
            $firstName = $this->getValueByHeaderName('firstName',$rowData,$headers);
            $middleName = $this->getValueByHeaderName('middleName',$rowData,$headers);
            $displayName = $firstName." ".$lastName;
            if( $middleName ) {
                $displayName = $firstName." ".$middleName." ".$lastName;
            }

            //create logger which must be deleted on successefull creation of application
            $eventAttempt = "Attempt of creating Fellowship Applicant ".$displayName." with unique ID=".$id;
            $eventLogAttempt =  $userSecUtil->createUserEditEvent($this->container->getParameter('fellapp.sitename'),$eventAttempt,$systemUser,null,$request,'Fellowship Application Creation Failed');

            $user->setEmail($email);
            $user->setEmailCanonical($email);
            $user->setFirstName($firstName);
            $user->setLastName($lastName);
            $user->setMiddleName($middleName);
            $user->setDisplayName($displayName);
            $user->setPassword("");
            $user->setCreatedby('googleapi');
            $user->getPreferences()->setTimezone($default_time_zone);
            $user->setLocked(true);
            //$em->persist($user);


            //Pathology Fellowship Applicant in EmploymentStatus
            $employmentStatus = new EmploymentStatus($systemUser);
            $employmentStatus->setEmploymentType($employmentType);
            $user->addEmploymentStatus($employmentStatus);

            $fellowshipApplication = new FellowshipApplication($systemUser);
            $fellowshipApplication->setApplicationStatus('active');
            $user->addFellowshipApplication($fellowshipApplication);

            //timestamp
            $fellowshipApplication->setTimestamp($this->transformDatestrToDate($this->getValueByHeaderName('timestamp',$rowData,$headers)));

            //fellowshipType
            $fellowshipType = $this->getValueByHeaderName('fellowshipType',$rowData,$headers);
            if( $fellowshipType ) {
                $transformer = new GenericTreeTransformer($em, $systemUser, 'FellowshipSubspecialty');
                $fellowshipTypeEntity = $transformer->reverseTransform($fellowshipType);
                $fellowshipApplication->setFellowshipSubspecialty($fellowshipTypeEntity);
            }

            //trainingPeriodStart
            $fellowshipApplication->setStartDate($this->transformDatestrToDate($this->getValueByHeaderName('trainingPeriodStart',$rowData,$headers)));

            //trainingPeriodEnd
            $fellowshipApplication->setEndDate($this->transformDatestrToDate($this->getValueByHeaderName('trainingPeriodEnd',$rowData,$headers)));

            //uploadedPhotoUrl
            $uploadedPhotoUrl = $this->getValueByHeaderName('uploadedPhotoUrl',$rowData,$headers);
            //echo "uploadedPhotoUrl=".$uploadedPhotoUrl."<br>";
            $uploadedPhotoId = $this->getFileIdByUrl( $uploadedPhotoUrl );
            if( $uploadedPhotoId ) {
                $uploadedPhotoDb = $this->downloadFileToServer($systemUser, $service, $uploadedPhotoId, null, $uploadPath);
                if( !$uploadedPhotoDb ) {
                    throw new IOException('Unable to download file to server: uploadedPhotoUrl='.$uploadedPhotoUrl.', fileDB='.$uploadedPhotoDb);
                }
                $user->setAvatar($uploadedPhotoDb); //set this file as Avatar
            }

            //uploadedCVUrl
            $uploadedCVUrl = $this->getValueByHeaderName('uploadedCVUrl',$rowData,$headers);
            $uploadedCVUrlId = $this->getFileIdByUrl( $uploadedCVUrl );
            if( $uploadedCVUrlId ) {
                $uploadedCVUrlDb = $this->downloadFileToServer($systemUser, $service, $uploadedCVUrlId, null, $uploadPath);
                if( !$uploadedCVUrlDb ) {
                    throw new IOException('Unable to download file to server: uploadedCVUrl='.$uploadedCVUrl.', fileDB='.$uploadedCVUrlDb);
                }
                $cv = new CurriculumVitae($systemUser);
                $cv->addDocument($uploadedCVUrlDb);
                $user->getCredentials()->addCv($cv);
            }

            //uploadedCoverLetterUrl
            $uploadedCoverLetterUrl = $this->getValueByHeaderName('uploadedCoverLetterUrl',$rowData,$headers);
            $uploadedCoverLetterUrlId = $this->getFileIdByUrl( $uploadedCoverLetterUrl );
            if( $uploadedCoverLetterUrlId ) {
                $uploadedCoverLetterUrlDb = $this->downloadFileToServer($systemUser, $service, $uploadedCoverLetterUrlId, null, $uploadPath);
                if( !$uploadedCoverLetterUrlDb ) {
                    throw new IOException('Unable to download file to server: uploadedCoverLetterUrl='.$uploadedCoverLetterUrl.', fileDB='.$uploadedCoverLetterUrlDb);
                }
                $fellowshipApplication->addCoverLetter($uploadedCoverLetterUrlDb);
            }

            $examination = new Examination($systemUser);
            $user->getCredentials()->addExamination($examination);
            //uploadedUSMLEScoresUrl
            $uploadedUSMLEScoresUrl = $this->getValueByHeaderName('uploadedUSMLEScoresUrl',$rowData,$headers);
            $uploadedUSMLEScoresUrlId = $this->getFileIdByUrl( $uploadedUSMLEScoresUrl );
            if( $uploadedUSMLEScoresUrlId ) {
                $uploadedUSMLEScoresUrlDb = $this->downloadFileToServer($systemUser, $service, $uploadedUSMLEScoresUrlId, null, $uploadPath);
                if( !$uploadedUSMLEScoresUrlDb ) {
                    throw new IOException('Unable to download file to server: uploadedUSMLEScoresUrl='.$uploadedUSMLEScoresUrl.', fileDB='.$uploadedUSMLEScoresUrlDb);
                }
                $examination->addScore($uploadedUSMLEScoresUrlDb);
            }

            //presentAddress
            $presentLocation = new Location($systemUser);
            $presentLocation->setName('Fellowship Applicant Present Address');
            $presentLocation->addLocationType($presentLocationType);
            $geoLocation = $this->createGeoLocation($em,$systemUser,'presentAddress',$rowData,$headers);
            if( $geoLocation ) {
                $presentLocation->setGeoLocation($geoLocation);
            }
            $user->addLocation($presentLocation);

            //telephoneHome
            //telephoneMobile
            //telephoneFax
            $presentLocation->setPhone($this->getValueByHeaderName('telephoneHome',$rowData,$headers));
            $presentLocation->setMobile($this->getValueByHeaderName('telephoneMobile',$rowData,$headers));
            $presentLocation->setFax($this->getValueByHeaderName('telephoneFax',$rowData,$headers));

            //permanentAddress
            $permanentLocation = new Location($systemUser);
            $permanentLocation->setName('Fellowship Applicant Permanent Address');
            $permanentLocation->addLocationType($permanentLocationType);
            $geoLocation = $this->createGeoLocation($em,$systemUser,'permanentAddress',$rowData,$headers);
            if( $geoLocation ) {
                $permanentLocation->setGeoLocation($geoLocation);
            }
            $user->addLocation($permanentLocation);

            //telephoneWork
            $telephoneWork = $this->getValueByHeaderName('telephoneWork',$rowData,$headers);
            if( $telephoneWork ) {
                $workLocation = new Location($systemUser);
                $workLocation->setName('Fellowship Applicant Work Address');
                $workLocation->addLocationType($workLocationType);
                $workLocation->setPhone($telephoneWork);
                $user->addLocation($workLocation);
            }


            $citizenship = new Citizenship($systemUser);
            $user->getCredentials()->addCitizenship($citizenship);
            //visaStatus
            $citizenship->setVisa($this->getValueByHeaderName('visaStatus',$rowData,$headers));
            //citizenshipCountry
            $citizenshipCountry = $this->getValueByHeaderName('citizenshipCountry',$rowData,$headers);
            if( $citizenshipCountry ) {
                $transformer = new GenericTreeTransformer($em, $systemUser, 'Countries');
                $citizenshipCountryEntity = $transformer->reverseTransform($citizenshipCountry);
                $citizenship->setCountry($citizenshipCountryEntity);
            }

            //undergraduate
            $this->createFellAppTraining($em,$user,$systemUser,"undergraduateSchool",$rowData,$headers,1);

            //graduate
            $this->createFellAppTraining($em,$user,$systemUser,"graduateSchool",$rowData,$headers,2);

            //medical
            $this->createFellAppTraining($em,$user,$systemUser,"medicalSchool",$rowData,$headers,3);

            //residency: residencyStart	residencyEnd	residencyName	residencyArea
            $this->createFellAppTraining($em,$user,$systemUser,"residency",$rowData,$headers,4);

            //gme1: gme1Start, gme1End, gme1Name, gme1Area => Major
            $this->createFellAppTraining($em,$user,$systemUser,"gme1",$rowData,$headers,5);

            //gme2: gme2Start, gme2End, gme2Name, gme2Area => Major
            $this->createFellAppTraining($em,$user,$systemUser,"gme2",$rowData,$headers,6);

            //otherExperience1Start	otherExperience1End	otherExperience1Name=>Major
            $this->createFellAppTraining($em,$user,$systemUser,"otherExperience1",$rowData,$headers,7);

            //otherExperience2Start	otherExperience2End	otherExperience2Name=>Major
            $this->createFellAppTraining($em,$user,$systemUser,"otherExperience2",$rowData,$headers,8);

            //otherExperience3Start	otherExperience3End	otherExperience3Name=>Major
            $this->createFellAppTraining($em,$user,$systemUser,"otherExperience3",$rowData,$headers,9);

            //USMLEStep1DatePassed	USMLEStep1Score
            $examination->setUSMLEStep1DatePassed($this->transformDatestrToDate($this->getValueByHeaderName('USMLEStep1DatePassed',$rowData,$headers)));
            $examination->setUSMLEStep1Score($this->getValueByHeaderName('USMLEStep1Score',$rowData,$headers));

            //USMLEStep2CKDatePassed	USMLEStep2CKScore	USMLEStep2CSDatePassed	USMLEStep2CSScore
            $examination->setUSMLEStep2CKDatePassed($this->transformDatestrToDate($this->getValueByHeaderName('USMLEStep2CKDatePassed',$rowData,$headers)));
            $examination->setUSMLEStep2CKScore($this->getValueByHeaderName('USMLEStep2CKScore',$rowData,$headers));
            $examination->setUSMLEStep2CSDatePassed($this->transformDatestrToDate($this->getValueByHeaderName('USMLEStep2CSDatePassed',$rowData,$headers)));
            $examination->setUSMLEStep2CSScore($this->getValueByHeaderName('USMLEStep2CSScore',$rowData,$headers));

            //USMLEStep3DatePassed	USMLEStep3Score
            $examination->setUSMLEStep3DatePassed($this->transformDatestrToDate($this->getValueByHeaderName('USMLEStep3DatePassed',$rowData,$headers)));
            $examination->setUSMLEStep3Score($this->getValueByHeaderName('USMLEStep3Score',$rowData,$headers));

            //ECFMGCertificate
            $ECFMGCertificateStr = $this->getValueByHeaderName('ECFMGCertificate',$rowData,$headers);
            $ECFMGCertificate = false;
            if( $ECFMGCertificateStr == 'Yes' ) {
                $ECFMGCertificate = true;
            }
            $examination->setECFMGCertificate($ECFMGCertificate);

            //ECFMGCertificateNumber	ECFMGCertificateDate
            $examination->setECFMGCertificateNumber($this->getValueByHeaderName('ECFMGCertificateNumber',$rowData,$headers));
            $examination->setECFMGCertificateDate($this->transformDatestrToDate($this->getValueByHeaderName('ECFMGCertificateDate',$rowData,$headers)));

            //COMLEXLevel1DatePassed	COMLEXLevel1Score	COMLEXLevel2DatePassed	COMLEXLevel2Score	COMLEXLevel3DatePassed	COMLEXLevel3Score
            $examination->setCOMLEXLevel1Score($this->getValueByHeaderName('COMLEXLevel1Score',$rowData,$headers));
            $examination->setCOMLEXLevel1DatePassed($this->transformDatestrToDate($this->getValueByHeaderName('COMLEXLevel1DatePassed',$rowData,$headers)));
            $examination->setCOMLEXLevel2Score($this->getValueByHeaderName('COMLEXLevel2Score',$rowData,$headers));
            $examination->setCOMLEXLevel2DatePassed($this->transformDatestrToDate($this->getValueByHeaderName('COMLEXLevel2DatePassed',$rowData,$headers)));
            $examination->setCOMLEXLevel3Score($this->getValueByHeaderName('COMLEXLevel3Score',$rowData,$headers));
            $examination->setCOMLEXLevel3DatePassed($this->transformDatestrToDate($this->getValueByHeaderName('COMLEXLevel3DatePassed',$rowData,$headers)));

            //medicalLicensure1Country	medicalLicensure1State	medicalLicensure1DateIssued	medicalLicensure1Number	medicalLicensure1Active
            $this->createFellAppMedicalLicense($em,$user,$systemUser,"medicalLicensure1",$rowData,$headers);

            //medicalLicensure2
            $this->createFellAppMedicalLicense($em,$user,$systemUser,"medicalLicensure2",$rowData,$headers);

            //suspendedLicensure
            $fellowshipApplication->setReprimand($this->getValueByHeaderName('suspendedLicensure',$rowData,$headers));
            //uploadedReprimandExplanationUrl
            $uploadedReprimandExplanationUrl = $this->getValueByHeaderName('uploadedReprimandExplanationUrl',$rowData,$headers);
            $uploadedReprimandExplanationUrlId = $this->getFileIdByUrl( $uploadedReprimandExplanationUrl );
            if( $uploadedReprimandExplanationUrlId ) {
                $uploadedReprimandExplanationUrlDb = $this->downloadFileToServer($systemUser, $service, $uploadedReprimandExplanationUrlId, null, $uploadPath);
                if( !$uploadedReprimandExplanationUrlDb ) {
                    throw new IOException('Unable to download file to server: uploadedReprimandExplanationUrl='.$uploadedReprimandExplanationUrl.', fileID='.$uploadedReprimandExplanationUrlDb->getId());
                }
                $fellowshipApplication->addReprimandDocument($uploadedReprimandExplanationUrlDb);
            }

            //legalSuit
            $fellowshipApplication->setLawsuit($this->getValueByHeaderName('legalSuit',$rowData,$headers));
            //uploadedLegalExplanationUrl
            $uploadedLegalExplanationUrl = $this->getValueByHeaderName('uploadedLegalExplanationUrl',$rowData,$headers);
            $uploadedLegalExplanationUrlId = $this->getFileIdByUrl( $uploadedLegalExplanationUrl );
            if( $uploadedLegalExplanationUrlId ) {
                $uploadedLegalExplanationUrlDb = $this->downloadFileToServer($systemUser, $service, $uploadedLegalExplanationUrlId, null, $uploadPath);
                if( !$uploadedLegalExplanationUrlDb ) {
                    throw new IOException('Unable to download file to server: uploadedLegalExplanationUrl='.$uploadedLegalExplanationUrl.', fileID='.$uploadedLegalExplanationUrlDb->getId());
                }
                $fellowshipApplication->addReprimandDocument($uploadedLegalExplanationUrlDb);
            }

            //boardCertification1Board	boardCertification1Area	boardCertification1Date
            $this->createFellAppBoardCertification($em,$user,$systemUser,"boardCertification1",$rowData,$headers);
            //boardCertification2
            $this->createFellAppBoardCertification($em,$user,$systemUser,"boardCertification2",$rowData,$headers);
            //boardCertification3
            $this->createFellAppBoardCertification($em,$user,$systemUser,"boardCertification3",$rowData,$headers);

            //recommendation1Name	recommendation1Title	recommendation1Institution	recommendation1AddressStreet1	recommendation1AddressStreet2	recommendation1AddressCity	recommendation1AddressState	recommendation1AddressZip	recommendation1AddressCountry
            $ref1 = $this->createFellAppReference($em,$systemUser,'recommendation1',$rowData,$headers);
            if( $ref1 ) {
                $fellowshipApplication->addReference($ref1);
            }
            $ref2 = $this->createFellAppReference($em,$systemUser,'recommendation2',$rowData,$headers);
            if( $ref2 ) {
                $fellowshipApplication->addReference($ref2);
            }
            $ref3 = $this->createFellAppReference($em,$systemUser,'recommendation3',$rowData,$headers);
            if( $ref3 ) {
                $fellowshipApplication->addReference($ref3);
            }
            $ref4 = $this->createFellAppReference($em,$systemUser,'recommendation4',$rowData,$headers);
            if( $ref4 ) {
                $fellowshipApplication->addReference($ref4);
            }

            //honors
            $fellowshipApplication->setHonors($this->getValueByHeaderName('honors',$rowData,$headers));
            //publications
            $fellowshipApplication->setPublications($this->getValueByHeaderName('publications',$rowData,$headers));
            //memberships
            $fellowshipApplication->setMemberships($this->getValueByHeaderName('memberships',$rowData,$headers));

            //signatureName
            $fellowshipApplication->setSignatureName($this->getValueByHeaderName('signatureName',$rowData,$headers));
            //signatureDate
            $signatureDate = $this->transformDatestrToDate($this->getValueByHeaderName('signatureDate',$rowData,$headers));
            $fellowshipApplication->setSignatureDate($signatureDate);

            if( !$fellowshipApplication->getSignatureName() ) {
                $event = "Error: Applicant signature is null after populating Fellowship Applicant " . $displayName . " with unique ID=".$id."; Application ID " . $fellowshipApplication->getId();
                $userSecUtil->createUserEditEvent($this->container->getParameter('fellapp.sitename'),$event,$systemUser,$fellowshipApplication,$request,'Fellowship Application Creation Failed');
                $logger = $this->container->get('logger');
                $logger->error($event);

                //send email
                $emailUtil = new EmailUtil();
                $userSecUtil = $this->get('user_security_utility');
                $emails = $userSecUtil->getUserEmailsByRole($this->container->getParameter('fellapp.sitename'),"Administrator");
                $headers = $userSecUtil->getUserEmailsByRole($this->container->getParameter('fellapp.sitename'),"Platform Administrator");
                if( !$emails ) {
                    $emails = $headers;
                    $headers = null;
                }
                $emailUtil->sendEmail( $emails, "Failed to create fellowship applicant with unique ID=".$id, $event, $em, $headers );
            }

            //exit('end applicant');

            $em->persist($user);
            $em->flush();

            //everything looks fine => remove creation attempt log
            $em->remove($eventLogAttempt);
            $em->flush();

            $event = "Populated fellowship applicant " . $displayName . "; Application ID " . $fellowshipApplication->getId();
            $userSecUtil->createUserEditEvent($this->container->getParameter('fellapp.sitename'),$event,$systemUser,$fellowshipApplication,$request,'Fellowship Application Created');

        } //for


        //echo "count=".$count."<br>";
        //exit('end populate');

        return $count;
    }

    public function createFellAppReference($em,$author,$typeStr,$rowData,$headers) {

        //recommendation1Name	recommendation1Title	recommendation1Institution	recommendation1AddressStreet1
        //recommendation1AddressStreet2	recommendation1AddressCity	recommendation1AddressState	recommendation1AddressZip	recommendation1AddressCountry

        $recommendationName = $this->getValueByHeaderName($typeStr."Name",$rowData,$headers);
        $recommendationTitle = $this->getValueByHeaderName($typeStr."Title",$rowData,$headers);

        //echo "recommendationName=".$recommendationName."<br>";
        //echo "recommendationTitle=".$recommendationTitle."<br>";

        if( !$recommendationName && !$recommendationTitle ) {
            //echo "no ref<br>";
            return null;
        }

        $reference = new Reference($author);

        //recommendation1Name
        $reference->setName($recommendationName);

        //recommendation1Title
        $reference->setTitle($recommendationTitle);

        $instStr = $this->getValueByHeaderName($typeStr."Institution",$rowData,$headers);
        if( $instStr ) {
            $params = array('type'=>'Educational');
            $transformer = new GenericTreeTransformer($em, $author, 'Institution', null, $params);
            $instEntity = $transformer->reverseTransform($instStr);
            $reference->setInstitution($instEntity);
        }

        $geoLocation = $this->createGeoLocation($em,$author,$typeStr."Address",$rowData,$headers);
        if( $geoLocation ) {
            $reference->setGeoLocation($geoLocation);
        }

        return $reference;
    }

    public function createGeoLocation($em,$author,$typeStr,$rowData,$headers) {

        $geoLocationStreet1 = $this->getValueByHeaderName($typeStr.'Street1',$rowData,$headers);
        $geoLocationStreet2 = $this->getValueByHeaderName($typeStr.'Street2',$rowData,$headers);
        //echo "geoLocationStreet1=".$geoLocationStreet1."<br>";
        //echo "geoLocationStreet2=".$geoLocationStreet2."<br>";

        if( !$geoLocationStreet1 && !$geoLocationStreet2 ) {
            //echo "no geoLocation<br>";
            return null;
        }

        $geoLocation = new GeoLocation();
        //popuilate geoLocation
        $geoLocation->setStreet1($this->getValueByHeaderName($typeStr.'Street1',$rowData,$headers));
        $geoLocation->setStreet2($this->getValueByHeaderName($typeStr.'Street2',$rowData,$headers));
        $geoLocation->setZip($this->getValueByHeaderName($typeStr.'Zip',$rowData,$headers));
        //presentAddressCity
        $presentAddressCity = $this->getValueByHeaderName($typeStr.'City',$rowData,$headers);
        if( $presentAddressCity ) {
            $transformer = new GenericTreeTransformer($em, $author, 'CityList');
            $presentAddressCityEntity = $transformer->reverseTransform($presentAddressCity);
            $geoLocation->setCity($presentAddressCityEntity);
        }
        //presentAddressState
        $presentAddressState = $this->getValueByHeaderName($typeStr.'State',$rowData,$headers);
        if( $presentAddressState ) {
            $transformer = new GenericTreeTransformer($em, $author, 'States');
            $presentAddressStateEntity = $transformer->reverseTransform($presentAddressState);
            $geoLocation->setState($presentAddressStateEntity);
        }
        //presentAddressCountry
        $presentAddressCountry = $this->getValueByHeaderName($typeStr.'Country',$rowData,$headers);
        if( $presentAddressCountry ) {
            $transformer = new GenericTreeTransformer($em, $author, 'Countries');
            $presentAddressCountryEntity = $transformer->reverseTransform($presentAddressCountry);
            $geoLocation->setCountry($presentAddressCountryEntity);
        }

        return $geoLocation;
    }

    public function transformDatestrToDate($datestr) {
        $date = null;

        if( !$datestr ) {
            return $date;
        }
        $datestr = trim($datestr);
        //echo "###datestr=".$datestr."<br>";

        if( strtotime($datestr) === false ) {
            // bad format
            $msg = 'transformDatestrToDate: Bad format of datetime string='.$datestr;
            //throw new \UnexpectedValueException($msg);
            $logger = $this->container->get('logger');
            $logger->error($msg);
            //exit('bad');
            return $date;
        }

//        if( !$this->valid_date($datestr) ) {
//            $msg = 'Date string is not valid'.$datestr;
//            throw new \UnexpectedValueException($msg);
//            $logger = $this->container->get('logger');
//            $logger->error($msg);
//        }

        try {
            $date = new \DateTime($datestr);
        } catch (Exception $e) {
            $msg = 'Failed to convert string'.$datestr.'to DateTime:'.$e->getMessage();
            //throw new \UnexpectedValueException($msg);
            $logger = $this->container->get('logger');
            $logger->error($msg);
        }

        return $date;
    }
//    function valid_date($date) {
//        return (preg_match("/^([0-9]{4})-([0-9]{2})-([0-9]{2})$/", $date));
//    }

    public function createFellAppBoardCertification($em,$user,$author,$typeStr,$rowData,$headers) {

        $boardCertificationIssueDate = $this->getValueByHeaderName($typeStr.'Date',$rowData,$headers);
        if( !$boardCertificationIssueDate ) {
            return null;
        }

        $boardCertification = new BoardCertification($author);
        $user->getCredentials()->addBoardCertification($boardCertification);

        //boardCertification1Board
        $boardCertificationBoard = $this->getValueByHeaderName($typeStr.'Board',$rowData,$headers);
        if( $boardCertificationBoard ) {
            $transformer = new GenericTreeTransformer($em, $author, 'CertifyingBoardOrganization');
            $CertifyingBoardOrganizationEntity = $transformer->reverseTransform($boardCertificationBoard);
            $boardCertification->setCertifyingBoardOrganization($CertifyingBoardOrganizationEntity);
        }

        //boardCertification1Area => BoardCertifiedSpecialties
        $boardCertificationArea = $this->getValueByHeaderName($typeStr.'Area',$rowData,$headers);
        if( $boardCertificationArea ) {
            $transformer = new GenericTreeTransformer($em, $author, 'BoardCertifiedSpecialties');
            $boardCertificationAreaEntity = $transformer->reverseTransform($boardCertificationArea);
            $boardCertification->setSpecialty($boardCertificationAreaEntity);
        }

        //boardCertification1Date
        $boardCertification->setIssueDate($this->transformDatestrToDate($boardCertificationIssueDate));

        return $boardCertification;
    }

    public function createFellAppMedicalLicense($em,$user,$author,$typeStr,$rowData,$headers) {

        //medicalLicensure1Country	medicalLicensure1State	medicalLicensure1DateIssued	medicalLicensure1Number	medicalLicensure1Active

        $licenseNumber = $this->getValueByHeaderName($typeStr.'Number',$rowData,$headers);
        $licenseIssuedDate = $this->getValueByHeaderName($typeStr.'DateIssued',$rowData,$headers);

        if( !$licenseNumber && !$licenseIssuedDate ) {
            return null;
        }

        $license = new StateLicense($author);
        $user->getCredentials()->addStateLicense($license);

        //medicalLicensure1DateIssued
        $license->setLicenseIssuedDate($this->transformDatestrToDate($licenseIssuedDate));

        //medicalLicensure1Active
        $medicalLicensureActive = $this->getValueByHeaderName($typeStr.'Active',$rowData,$headers);
        if( $medicalLicensureActive ) {
            $transformer = new GenericTreeTransformer($em, $author, 'MedicalLicenseStatus');
            $medicalLicensureActiveEntity = $transformer->reverseTransform($medicalLicensureActive);
            $license->setActive($medicalLicensureActiveEntity);
        }

        //medicalLicensure1Country
        $medicalLicensureCountry = $this->getValueByHeaderName($typeStr.'Country',$rowData,$headers);
        if( $medicalLicensureCountry ) {
            $transformer = new GenericTreeTransformer($em, $author, 'Countries');
            $medicalLicensureCountryEntity = $transformer->reverseTransform($medicalLicensureCountry);
            //echo "MedCountry=".$medicalLicensureCountryEntity.", ID+".$medicalLicensureCountryEntity->getId()."<br>";
            $license->setCountry($medicalLicensureCountryEntity);
        }

        //medicalLicensure1State
        $medicalLicensureState = $this->getValueByHeaderName($typeStr.'State',$rowData,$headers);
        if( $medicalLicensureState ) {
            $transformer = new GenericTreeTransformer($em, $author, 'States');
            $medicalLicensureStateEntity = $transformer->reverseTransform($medicalLicensureState);
            //echo "MedState=".$medicalLicensureStateEntity."<br>";
            $license->setState($medicalLicensureStateEntity);
        }

        //medicalLicensure1Number
        $license->setLicenseNumber($licenseNumber);

        return $license;
    }

    public function createFellAppTraining($em,$user,$author,$typeStr,$rowData,$headers,$orderinlist) {

        //Start
        $trainingStart = $this->getValueByHeaderName($typeStr.'Start',$rowData,$headers);
        //End
        $trainingEnd = $this->getValueByHeaderName($typeStr.'End',$rowData,$headers);

        if( !$trainingStart && !$trainingEnd ) {
            return null;
        }

        $training = new Training($author);
        $training->setOrderinlist($orderinlist);
        $user->addTraining($training);

        //set TrainingType
        if( $typeStr == 'undergraduateSchool' ) {
            $trainingType = $em->getRepository('OlegUserdirectoryBundle:TrainingTypeList')->findOneByName('Undergraduate');
            $training->setTrainingType($trainingType);
        }
        if( $typeStr == 'graduateSchool' ) {
            $trainingType = $em->getRepository('OlegUserdirectoryBundle:TrainingTypeList')->findOneByName('Graduate');
            $training->setTrainingType($trainingType);
        }
        if( strpos($typeStr,'medical') !== false ) {
            $trainingType = $em->getRepository('OlegUserdirectoryBundle:TrainingTypeList')->findOneByName('Medical');
            $training->setTrainingType($trainingType);
        }
        if( strpos($typeStr,'residency') !== false ) {
            $trainingType = $em->getRepository('OlegUserdirectoryBundle:TrainingTypeList')->findOneByName('Residency');
            $training->setTrainingType($trainingType);
        }
        if( strpos($typeStr,'gme') !== false ) {
            $trainingType = $em->getRepository('OlegUserdirectoryBundle:TrainingTypeList')->findOneByName('GME');
            $training->setTrainingType($trainingType);
        }
        if( strpos($typeStr,'other') !== false ) {
            $trainingType = $em->getRepository('OlegUserdirectoryBundle:TrainingTypeList')->findOneByName('Other');
            $training->setTrainingType($trainingType);
        }

        $majorMatchString = $typeStr.'Major';
        $nameMatchString = $typeStr.'Name';

        if( strpos($typeStr,'otherExperience') !== false ) {
            //otherExperience1Start	otherExperience1End	otherExperience1Name
            //exception for otherExperience: Name => Major
            $majorMatchString = $typeStr.'Name';
            $nameMatchString = null;
        }

        if( strpos($typeStr,'gme') !== false ) {
            //gme1Start	gme1End	gme1Name gme1Area
            //exception for Area: gmeArea => Major
            $majorMatchString = $typeStr.'Area';
        }

        if( strpos($typeStr,'residency') !== false ) {
            //residencyStart	residencyEnd	residencyName	residencyArea
            //residencyArea => ResidencySpecialty
            $residencyArea = $this->getValueByHeaderName('residencyArea',$rowData,$headers);
            $transformer = new GenericTreeTransformer($em, $author, 'ResidencySpecialty');
            $residencyAreaEntity = $transformer->reverseTransform($residencyArea);
            $training->setResidencySpecialty($residencyAreaEntity);
        }

        //Start
        $training->setStartDate($this->transformDatestrToDate($this->getValueByHeaderName($typeStr.'Start',$rowData,$headers)));

        //End
        $training->setCompletionDate($this->transformDatestrToDate($this->getValueByHeaderName($typeStr.'End',$rowData,$headers)));

        //Name
        $schoolName = $this->getValueByHeaderName($nameMatchString,$rowData,$headers);
        if( $schoolName ) {
            $params = array('type'=>'Educational');
            $transformer = new GenericTreeTransformer($em, $author, 'Institution', null, $params);
            $schoolNameEntity = $transformer->reverseTransform($schoolName);
            $training->setInstitution($schoolNameEntity);
        }

        //Major
        $schoolMajor = $this->getValueByHeaderName($majorMatchString,$rowData,$headers);
        if( $schoolMajor ) {
            $transformer = new GenericTreeTransformer($em, $author, 'MajorTrainingList');
            $schoolMajorEntity = $transformer->reverseTransform($schoolMajor);
            $training->addMajor($schoolMajorEntity);
        }

        //Degree
        $schoolDegree = $this->getValueByHeaderName($typeStr.'Degree',$rowData,$headers);
        if( $schoolDegree ) {
            $transformer = new GenericTreeTransformer($em, $author, 'TrainingDegreeList');
            $schoolDegreeEntity = $transformer->reverseTransform($schoolDegree);
            $training->setDegree($schoolDegreeEntity);
        }

        return $training;
    }


    public function getValueByHeaderName($header, $row, $headers) {

        $res = null;

        if( !$header ) {
            return $res;
        }

        //echo "header=".$header."<br>";
        //print_r($headers);
        //print_r($row[0]);

        $key = array_search($header, $headers[0]);
        //echo "key=".$key."<br>";

        if( $key === false ) {
            //echo "key is false !!!!!!!!!!<br>";
            return $res;
        }

        if( array_key_exists($key, $row[0]) ) {
            $res = $row[0][$key];
        }

        //echo "res=".$res."<br>";
        return $res;
    }

    //parse url and get file id
    public function getFileIdByUrl( $url ) {
        if( !$url ) {
            return null;
        }
        //https://drive.google.com/a/pathologysystems.org/file/d/0B2FwyaXvFk1eSDQ0MkJKSjhLN1U/view?usp=drivesdk
        $urlArr = explode("/d/", $url);
        $urlSecond = $urlArr[1];
        $urlSecondArr = explode("/", $urlSecond);
        $fileId = $urlSecondArr[0];
        return $fileId;
    }



    /**
     * Import spreadsheet to C:\Program Files (x86)\Aperio\Spectrum\htdocs\order\scanorder\Scanorders2\web\Uploaded\fellapp\Spreadsheets
     *
     * @Route("/import", name="fellapp_import")
     */
    public function importAction(Request $request) {

        //echo "fellapp import <br>";

        $service = $this->getGoogleService();
        if( !$service ) {
            $this->get('session')->getFlashBag()->add(
                'warning',
                "Google API service failed!"
            );
            $logger = $this->container->get('logger');
            $logger->warning("Google API service failed!");
        }

        if( $service ) {

            //https://drive.google.com/open?id=1DN1BEbONKNmFpHU6xBo69YSLjXCnhRy0IbyXrwMzEzc
            $excelId = "1DN1BEbONKNmFpHU6xBo69YSLjXCnhRy0IbyXrwMzEzc";

            //$user = $this->get('security.context')->getToken()->getUser();
            $userSecUtil = $this->container->get('user_security_utility');
            $systemUser = $userSecUtil->findSystemUser();

            $path = 'Uploaded/fellapp/Spreadsheets/';
            $fileDb = $this->downloadFileToServer($systemUser, $service, $excelId, 'excel', $path);

            if( $fileDb ) {
                $em = $this->getDoctrine()->getManager();
                $em->flush($fileDb);
                $event = "Fellowship Application Spreadsheet file has been successful downloaded to the server with id=" . $fileDb->getId().", title=".$fileDb->getUniquename();
                $this->get('session')->getFlashBag()->add(
                    'notice',
                    $event
                );
            } else {
                $event = "Fellowship Application Spreadsheet download failed!";
                $this->get('session')->getFlashBag()->add(
                    'warning',
                    $event
                );

                $logger = $this->container->get('logger');
                $logger->warning("Fellowship Application Spreadsheet download failed!");
            }

            $userSecUtil->createUserEditEvent($this->container->getParameter('fellapp.sitename'),$event,$systemUser,null,$request,'Import of Fellowship Applications');

        }

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






    public function getGoogleService() {
        $client_email = '1040591934373-1sjcosdt66bmani0kdrr5qmc5fibmvk5@developer.gserviceaccount.com';
        $pkey = __DIR__ . '/../Util/FellowshipApplication-f1d9f98353e5.p12';
        $user_to_impersonate = 'olegivanov@pathologysystems.org';
        $res = $this->authenticationP12Key($pkey,$client_email,$user_to_impersonate);
        return $res['service'];
    }

    //Using OAuth 2.0 for Server to Server Applications: using PKCS12 certificate file
    //https://developers.google.com/api-client-library/php/auth/service-accounts
    //1) Create a service account by Google Developers Console.
    //2) Delegate domain-wide authority to the service account.
    //3) Impersonate a user account.
    public function authenticationP12Key($pkey,$client_email,$user_to_impersonate) {
        $private_key = file_get_contents($pkey); //notasecret
        $scopes = array('https://www.googleapis.com/auth/drive');
        $credentials = new \Google_Auth_AssertionCredentials(
            $client_email,
            $scopes,
            $private_key,
            'notasecret',                                 // Default P12 password
            'http://oauth.net/grant_type/jwt/1.0/bearer', // Default grant type
            $user_to_impersonate
        );

        $client = new \Google_Client();
        $client->setAssertionCredentials($credentials);
        if ($client->getAuth()->isAccessTokenExpired()) {
            $client->getAuth()->refreshTokenWithAssertion();
        }

        $service = new \Google_Service_Drive($client);

        $res = array(
            'client' => $client,
            'credentials' => $credentials,
            'service' => $service
        );

        return $res;
    }

    public function downloadFileToServer($author, $service, $fileId, $type, $path) {
        $file = null;
        try {
            $file = $service->files->get($fileId);
        } catch (Exception $e) {
            throw new IOException('Google API: Unable to get file by file id='.$fileId.". An error occurred: " . $e->getMessage());
        }

        $em = $this->getDoctrine()->getManager();

        if( $file ) {

            //check if file already exists by file id
            $documentDb = $em->getRepository('OlegUserdirectoryBundle:Document')->findOneByUniqueid($file->getId());
            if( $documentDb && $type != 'excel' ) {
                //echo "already exists file ID=".$file->getId()."<br>";
                return $documentDb;
            }

            $response = $this->downloadFile($service, $file, $type);
            //echo "response=".$response."<br>";
            if( !$response ) {
                throw new IOException('Error file response is empty: file id='.$fileId);
            }

            //create unique file name
            $currentDatetime = new \DateTime();
            $currentDatetimeTimestamp = $currentDatetime->getTimestamp();

            //$fileTitle = trim($file->getTitle());
            //$fileTitle = str_replace(" ","",$fileTitle);
            //$fileTitle = str_replace("-","_",$fileTitle);
            //$fileTitle = 'testfile.jpg';
            $fileExt = pathinfo($file->getTitle(), PATHINFO_EXTENSION);

            $fileUniqueName = $currentDatetimeTimestamp.'_id='.$file->getId().".".$fileExt;  //.'_title='.$fileTitle;
            //echo "fileUniqueName=".$fileUniqueName."<br>";

            $filesize = $file->getFileSize();
            if( !$filesize ) {
                $filesize = mb_strlen($response) / 1024; //KBs,
            }

            $object = new Document($author);
            $object->setUniqueid($file->getId());
            $object->setOriginalname($file->getTitle());
            $object->setUniquename($fileUniqueName);
            $object->setUploadDirectory($path);
            $object->setSize($filesize);

            if( $type && $type == 'excel' ) {
                $fellappSpreadsheetType = $em->getRepository('OlegUserdirectoryBundle:DocumentTypeList')->findOneByName('Fellowship Application Spreadsheet');
            } else {
                $fellappSpreadsheetType = $em->getRepository('OlegUserdirectoryBundle:DocumentTypeList')->findOneByName('Fellowship Application Upload');
            }
            if( $fellappSpreadsheetType ) {
                $object->setType($fellappSpreadsheetType);
            }

            $em->persist($object);

            $fullpath = $this->get('kernel')->getRootDir() . '/../web/'.$path;
            $target_file = $fullpath . $fileUniqueName;

            //$target_file = $fullpath . 'uploadtestfile.jpg';
            //echo "target_file=".$target_file."<br>";
            if( !file_exists($fullpath) ) {
                // 0600 - Read and write for owner, nothing for everybody else
                mkdir($fullpath, 0600, true);
                chmod($fullpath, 0600);
            }

            file_put_contents($target_file, $response);

            return $object;
        }

        return null;
    }





    
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

//    function getFilesByPkey() {
//
//        $client_id = "1040591934373-1sjcosdt66bmani0kdrr5qmc5fibmvk5.apps.googleusercontent.com";
//        $client_email = '1040591934373-1sjcosdt66bmani0kdrr5qmc5fibmvk5@developer.gserviceaccount.com';
//
//        $pkey = __DIR__ . '/../Util/FellowshipApplication-f1d9f98353e5.p12';
//        $private_key = file_get_contents($pkey); //notasecret
//
//        $scopes = array('https://www.googleapis.com/auth/drive');   //array('https://www.googleapis.com/auth/sqlservice.admin');
//
////        $credentials = new \Google_Auth_AssertionCredentials(
////            $client_email,
////            $scopes,
////            $private_key
////        );
//
//        $user_to_impersonate = 'olegivanov@pathologysystems.org';
//        $credentials = new \Google_Auth_AssertionCredentials(
//            $client_email,
//            $scopes,
//            $private_key,
//            'notasecret',                                 // Default P12 password
//            'http://oauth.net/grant_type/jwt/1.0/bearer', // Default grant type
//            $user_to_impersonate
//        );
//
//        $client = new \Google_Client();
//        //$client->setAccessType('offline');
//        $client->setAssertionCredentials($credentials);
//        if ($client->getAuth()->isAccessTokenExpired()) {
//            $client->getAuth()->refreshTokenWithAssertion();
//        }
//
////        $sqladmin = new \Google_Service_SQLAdmin($client);
////        $response = $sqladmin->instances->listInstances('examinable-example-123')->getItems();
////        echo json_encode($response) . "\n";
//
//        $service = new \Google_Service_Drive($client);
//
//        $files = $service->files->listFiles();
//        echo "count files=".count($files)."<br>";
//        //echo "<pre>"; print_r($files);
//
//        foreach( $files as $item ) {
//            echo "title=".$item['title']."<br>";
//        }
//
//    }

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
     *
     * @param String credentials Json representation of the OAuth 2.0
     *     credentials.
     * @return Google_Service_Drive service object.
     */
    function buildService_ORIG($credentials) {
        $apiClient = new \Google_Client();
        $apiClient->setUseObjects(true);
        $apiClient->setAccessToken($credentials);
        return new \Google_Service_Drive($apiClient);
    }

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
     * Download a file's content.
     *
     * @param Google_Servie_Drive $service Drive API service instance.
     * @param Google_Servie_Drive_DriveFile $file Drive File instance.
     * @return String The file's content if successful, null otherwise.
     */
    function downloadFile($service, $file, $type=null) {
        if( $type && $type == 'excel' ) {
            $downloadUrl = $file->getExportLinks()['text/csv'];
        } else {
            $downloadUrl = $file->getDownloadUrl();
        }
        echo "downloadUrl=".$downloadUrl."<br>";
        if ($downloadUrl) {
            $request = new \Google_Http_Request($downloadUrl, 'GET', null, null);
            $httpRequest = $service->getClient()->getAuth()->authenticatedRequest($request);
            echo "res code=".$httpRequest->getResponseHttpCode()."<br>";
            if ($httpRequest->getResponseHttpCode() == 200) {
                return $httpRequest->getResponseBody();
            } else {
                // An error occurred.
                return null;
            }
        } else {
            // The file doesn't have any content stored on Drive.
            return null;
        }
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
