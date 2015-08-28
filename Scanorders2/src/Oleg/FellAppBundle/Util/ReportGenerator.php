<?php
/**
 * Created by PhpStorm.
 * User: DevServer
 * Date: 8/28/15
 * Time: 8:47 AM
 */

namespace Oleg\FellAppBundle\Util;


use Clegginabox\PDFMerger\PDFMerger;
use Doctrine\ORM\EntityNotFoundException;
use Oleg\FellAppBundle\Controller\FellAppController;
use Oleg\FellAppBundle\Form\FellowshipApplicationType;
use Oleg\UserdirectoryBundle\Entity\Document;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Bundle\FrameworkBundle\Tests\Functional\WebTestCase;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;


class ReportGenerator {


    protected $em;
    protected $sc;
    protected $container;
    protected $templating;
    protected $uploadDir;


    public function __construct( $em, $sc, $container, $templating ) {
        $this->em = $em;
        $this->sc = $sc;
        $this->container = $container;
        $this->templating = $templating;

        //fellapp.uploadpath = fellapp
        $this->uploadDir = 'Uploaded/'.$this->container->getParameter('fellapp.uploadpath');
    }


    public function addFellAppReportToQueue( $id, $asap=false ) {

        //TODO: implement queuing
        if(0) {
            $manager = ReportGeneratorManager::getInstance($this->container);
            $manager->addToQueue($id,$asap);
            return;
        }

        return $this->generateFellAppReport( $id );
    }


    //generate Fellowship Application Report
    public function generateFellAppReport( $id ) {
        ini_set('max_execution_time', 300); //300 seconds = 5 minutes

//        $params = $this->getShowParameters($id,'fellapp_download');
//        $html = $this->renderView('OlegFellAppBundle:Form:download.html.twig',$params);
//        $this->html2pdf($html);
//        return;

        $entity = $this->em->getRepository('OlegFellAppBundle:FellowshipApplication')->find($id);

        if( !$entity ) {
            throw new EntityNotFoundException('Unable to find Fellowship Application by id='.$id);
        }

        //generate file name: LastName_FirstName_FellowshipType_StartYear.pdf
        $subjectUser = $entity->getUser();
        $filename =
            "ID".$id.
            "_".$subjectUser->getLastNameUppercase().
            "_".$subjectUser->getFirstNameUppercase().
            "_".$entity->getFellowshipSubspecialty()->getName().
            "_".$entity->getStartDate()->format('Y').
            ".pdf";

        //cv
        $recentDocumentCv = $entity->getRecentCv();
        $abspathCv = $recentDocumentCv->getFileSystemPath();
        //echo "abspathCv=".$abspathCv."<br>";

        //cover letter
        $recentCoverLetter = $entity->getRecentCoverLetter();
        $abspathCoverLetter = $recentCoverLetter->getFileSystemPath();

        //scores
        $scores = $entity->getRecentExaminationScores();

        //Reprimand
        $reprimand = $entity->getRecentReprimand();

        //Legal Explanation
        $legalExplanation = $entity->getRecentLegalExplanation();

        //fellapp.uploadpath = fellapp
        //$rootDir = $this->get('kernel')->getRootDir();
        //$rootDirClean = str_replace("app","",$rootDir);
        $uploadReportPath = 'Uploaded/' . $this->container->getParameter('fellapp.uploadpath').'/Reports';
        $reportPath = $this->container->get('kernel')->getRootDir() . '/../web/' . $uploadReportPath.'/';

        if( !file_exists($uploadReportPath) ) {
            mkdir($uploadReportPath, 0700, true);
            chmod($uploadReportPath, 0700);
        }

//        $testFlag = false;
//        if($testFlag) {
//            //$applicationPath = "C:\\Program Files (x86)\\Aperio\\Spectrum\\htdocs\\order\\scanorder\\Scanorders2\\web\\Uploaded\\fellapp\\Reports\\".$filename;
//            $applicationFilePath = $reportPath . "application_ID" . $id . ".pdf";
//            $this->generateApplicationPdf($id,$applicationFilePath);
//
//            $filenameMerged = $reportPath . "report_ID" . $id . ".pdf";
//            $filesArr = array($applicationFilePath,$abspathCv,$abspathCoverLetter);
//            foreach( $scores as $score ) {
//                $filesArr[] = $score->getFileSystemPath();
//            }
//            $this->mergeByPDFMerger($filesArr,$filenameMerged );
//        }


        $outdir = $reportPath.'temp_'.$id.'/';


        //0) generate application pdf
        $applicationFilePath = $outdir . "application_ID" . $id . ".pdf";
        $this->generateApplicationPdf($id,$applicationFilePath);

        //1) get all upload documents
        $filePathsArr = array($applicationFilePath,$abspathCv,$abspathCoverLetter);

        foreach( $scores as $score ) {
            $filePathsArr[] = $score->getFileSystemPath();
        }

        if( $reprimand ) {
            $filePathsArr[] = $reprimand;
        }

        if( $legalExplanation ) {
            $filePathsArr[] = $legalExplanation;
        }

        //2) convert all uploads to pdf using LibreOffice
        $fileNamesArr = $this->convertToPdf( $filePathsArr, $outdir );

        $uniqueid = "report_ID" . $id;
        $fileUniqueName = $uniqueid . ".pdf";
        $filenameMerged = $reportPath . $fileUniqueName;
        $this->mergeByPDFMerger($fileNamesArr,$filenameMerged );

        $logger = $this->container->get('logger');
        $logger->notice("download Application report pdf ok; path=" . $filenameMerged );

        $createFlag = true;
        if( count($entity->getReports()) > 0 ) {
            $createFlag = false;
        }

        //3) add the report to application report DB
        $author = $this->sc->getToken()->getUser();
        $filesize = filesize($filenameMerged);
        if(1) { //testing: do not save to DB
        $this->createFellAppReportDB($entity,$author,$uniqueid,$filename,$fileUniqueName,$uploadReportPath,$filesize);
        }

        //log event
        $userSecUtil = $this->container->get('user_security_utility');
        if( $createFlag ) {
            $actionStr = "created";
        } else {
            $actionStr = "updated";
        }
        $event = "Report for Fellowship Application with ID".$id." has been successfully ".$actionStr." " . $filename;
        $userSecUtil->createUserEditEvent($this->container->getParameter('fellapp.sitename'),$event,$author,$entity,null,'Fellowship Application Updated');


        //delete application temp folder
        $this->deleteDir($outdir);

        $res = array(
            'filename' => $filename,
            'report' => $filenameMerged,
            'size' => $filesize
        );

        return $res;
    }

    //use KnpSnappyBundle to convert html to pdf
    //http://wkhtmltopdf.org must be installed on server
    public function generateApplicationPdf($applicationId,$applicationOutputFilePath) {
        if( file_exists($applicationOutputFilePath) ) {
            $logger = $this->container->get('logger');
            $logger->warning("generateApplicationPdf: file already exists path=" . $applicationOutputFilePath );
            return;
        }

        ini_set('max_execution_time', 300); //300 sec

    if(1) {
        //generate application URL
        $router = $this->container->get('router');

        //$url = $router->generate('fellapp_download',array('id' => $applicationId)); //fellowship-applications/show/43
        //$pageUrl = "http://localhost/order".$url;

        $pageUrl = $router->generate('fellapp_download',array('id' => $applicationId),true); //this does not work from console: 'order' is missing
        //echo "pageurl=". $pageUrl . "<br>";

        //save session
        $session = $this->container->get('session');
        $session->save();
        session_write_close();

        //$application =
        $this->container->get('knp_snappy.pdf')->generate(
            $pageUrl,
            $applicationOutputFilePath,
            array('cookie' => array($session->getName() => $session->getId()))
        );
    }//if

        if(0) {
            $this->logIn();
            $this->generateFromTwig($applicationId,$applicationOutputFilePath);
            //exit('exit 1');
        }//if
    }

    public function generateFromTwig($applicationId,$applicationOutputFilePath) {
        $args = $this->getShowParameters($applicationId,'fellapp_download');
        $this->container->get('knp_snappy.pdf')->generateFromHtml(
            $this->renderView(
                'OlegFellAppBundle:Form:download.html.twig',
                $args
            ),
            $applicationOutputFilePath
        );
    }


//    public function getShowParameters($id,$routeName) {
//        $userSecUtil = $this->container->get('user_security_utility');
//        $user = $userSecUtil->findSystemUser();
//        $em = $this->em;
//
//        //$fellApps = $em->getRepository('OlegFellAppBundle:FellowshipApplication')->findAll();
//        $entity = $em->getRepository('OlegFellAppBundle:FellowshipApplication')->find($id);
//
//        if( !$entity ) {
//            throw new EntityNotFoundException('Unable to find Fellowship Application by id='.$id);
//        }
//
//        if( $routeName == "fellapp_download" ) {
//            $cycle = 'download';
//            $disabled = true;
//            $method = "GET";
//            $action = null;
//        }
//
//        $params = array(
//            'cycle' => $cycle,
//            'sc' => $this->sc,
//            'em' => $em,
//            'user' => $entity->getUser(),
//            'cloneuser' => null,
//            'roles' => $user->getRoles()
//        );
//
//        $formFactory = $this->container->get('form.factory');
//        $form = $formFactory->create(
//            new FellowshipApplicationType($params),
//            $entity,
//            array(
//                'disabled' => $disabled,
//                //'method' => $method,
//                //'action' => $action
//            )
//        );
//
//        return array(
//            'form' => $form->createView(),
//            'entity' => $entity,
//            'pathbase' => 'fellapp',
//            'cycle' => $cycle,
//            'sitename' => $this->container->getParameter('fellapp.sitename')
//        );
//    }

    private function logIn1()
    {
        $session = $this->container->get('session');

        $firewall = 'ldap_fellapp_firewall';
        $token = new UsernamePasswordToken('system', null, $firewall, array('ROLE_FELLAPP_ADMIN'));
        $session->set('_security_'.$firewall, serialize($token));
        $session->save();

        //$cookie = new Cookie($session->getName(), $session->getId());
        //$this->container->getCookieJar()->set($cookie);
    }
    private function logIn() {
        $firewall = 'ldap_fellapp_firewall';
        // create the authentication token
        $userSecUtil = $this->container->get('user_security_utility');
        $systemUser = $userSecUtil->findSystemUser();
        $token = new UsernamePasswordToken(
            $systemUser,
            null,
            $firewall,
            $systemUser->getRoles());

        // give it to the security context
        $this->container->get('security.context')->setToken($token);

        $session = $this->container->get('session');
        $session->set('_security_'.$firewall, serialize($token));
        //$session->save();

        //save session
        $session = $this->container->get('session');
//        $session->save();
//        session_write_close();
//        echo "session=".$session->getName() . ", id=". $session->getId() . "<br>";
    }


    protected function convertToPdf( $filePathsArr, $outdir ) {

        $fileNamesArr = array();

        //C:\Program Files (x86)\Aperio\Spectrum\htdocs\order\scanorder\Scanorders2\vendor\olegutil\LibreOfficePortable\App\libreoffice\program\soffice.exe
        //$cmd = '"C:\Program Files (x86)\LibreOffice 5\program\soffice" --headless -convert-to pdf -outdir "'.$outdir.'"';
        $cmd = '"'.$this->container->get('kernel')->getRootDir() . '\..\vendor\olegutil\LibreOfficePortable\App\libreoffice\program\soffice" --headless -convert-to pdf -outdir "' . $outdir . '"';
        //$cmd = '"../../../../../vendors/olegutil/LibreOfficePortable\App\libreoffice\program\soffice" --headless -convert-to pdf -outdir "' . $outdir . '"';

        //echo "cmd=" . $cmd . "<br>";

        foreach( $filePathsArr as $filePath ) {

            //$outFilename = $outdir . basename($filePath);
            $outFilename = $outdir . pathinfo($filePath, PATHINFO_FILENAME) . ".pdf";
            //echo "outFilename=".$outFilename."<br>";
            //exit('1');

            $fileNamesArr[] = $outFilename;

            //if( file_exists($filePath) ) {
            //echo "exists filePath=".$filePath."<br>";
            //continue;
            //}

            $cmd = $cmd .' "'.$filePath.'"';

            //$shellout = shell_exec( $cmd );
            $shellout = exec( $cmd );

            if( $shellout ) {
                //echo "shellout=".$shellout."<br>";
                $logger = $this->container->get('logger');
                $logger->debug("LibreOffice: " . $shellout);
            }

        }

        return $fileNamesArr;
    }

    //TODO: try https://www.pdflabs.com/tools/pdftk-the-pdf-toolkit/
    //if file already exists then it is replaced with a new one
    protected function mergeByPDFMerger( $filesArr, $filenameMerged ) {

        $pdf = new PDFMerger();

        foreach( $filesArr as $file ) {
//            echo "add merge: filepath=(".$file.") => ";
            if( file_exists($file) ) {
                $pdf->addPDF($file, 'all');
            } else {
                $logger = $this->container->get('logger');
                $logger->warning("PDFMerger: pdf file does not exists path=" . $file );

                new \Exception("PDFMerger: pdf file does not exists path=" . $file);
            }
        }

        $pdf->merge('file', $filenameMerged);
    }

    protected static function deleteDir($dirPath) {
        if (! is_dir($dirPath)) {
            throw new \InvalidArgumentException("$dirPath must be a directory");
        }
        if (substr($dirPath, strlen($dirPath) - 1, 1) != '/') {
            $dirPath .= '/';
        }
        $files = glob($dirPath . '*', GLOB_MARK);
        foreach ($files as $file) {
            if (is_dir($file)) {
                self::deleteDir($file);
            } else {
                unlink($file);
            }
        }
        rmdir($dirPath);
    }



    //create fellapp report in DB
    //path = Uploaded/fellapp/Reports
    protected function createFellAppReportDB($holderEntity,$author,$uniqueid,$title,$fileUniqueName,$path,$filesize) {

        $object = new Document($author);
        $object->setUniqueid($uniqueid);
        $object->setOriginalname($title);
        $object->setTitle($title);
        $object->setUniquename($fileUniqueName);
        $object->setUploadDirectory($path);
        $object->setSize($filesize);

        $fellappReportType = $this->em->getRepository('OlegUserdirectoryBundle:DocumentTypeList')->findOneByName('Fellowship Application Report');

        if( $fellappReportType ) {
            $object->setType($fellappReportType);
        }

        //remove all reports
        foreach( $holderEntity->getReports() as $report ) {
            $holderEntity->removeReport($report);
            $this->em->remove($report);
        }

        //add report
        $holderEntity->addReport($object);

        $this->em->persist($holderEntity);
        $this->em->persist($object);
        $this->em->flush();

    }






//    protected function spraed($html) {
//        $pdfGenerator = $this->get('spraed.pdf.generator');
//
//        return new Response($pdfGenerator->generatePDF($html),
//            200,
//            array(
//                'Content-Type' => 'application/pdf',
//                'Content-Disposition' => 'inline; filename="out.pdf"'
//            )
//        );
//
//        exit;
//    }
//
//    protected function html2pdf($html) {
//
//        //$params = $this->getShowParameters($id,'fellapp_download');
//        //$html = $this->renderView('OlegFellAppBundle:Form:download.html.twig',$params);
//
//        try {
//
//            //$html2pdf = $this->get('html2pdf_factory')->create('P','A4','fr');
//            $html2pdf = $this->get('html2pdf_factory')->create();
//
//            $html2pdf->pdf->SetDisplayMode('real');
//            //$html2pdf->pdf->SetDisplayMode('fullpage');
//            $html2pdf->writeHTML($html);
//            $html2pdf->Output('examplepdf.pdf');
//
//            //return new Response();
//            exit;
//
//        } catch(HTML2PDF_exception $e) {
//            echo $e;
//            exit;
//        }
//    }


} 