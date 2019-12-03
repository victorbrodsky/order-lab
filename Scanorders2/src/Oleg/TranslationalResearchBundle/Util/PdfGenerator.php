<?php
/**
 * Created by PhpStorm.
 * User: ch3
 * Date: 12/21/2017
 * Time: 3:02 PM
 */

namespace Oleg\TranslationalResearchBundle\Util;


use Oleg\UserdirectoryBundle\Entity\Document;
use Oleg\UserdirectoryBundle\Form\DataTransformer\GenericTreeTransformer;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class PdfGenerator
{

    protected $container;
    protected $em;
    protected $secTokenStorage;
    protected $secAuth;

    protected $uploadDir;

    public function __construct( $em, $container ) {
        $this->container = $container;
        $this->em = $em;
        $this->secAuth = $container->get('security.authorization_checker'); //$this->secAuth->isGranted("ROLE_USER")
        $this->secTokenStorage = $container->get('security.token_storage'); //$user = $this->secTokenStorage->getToken()->getUser();

        $this->uploadDir = 'Uploaded';
    }


    public function generateInvoicePdf( $invoice, $authorUser, $request=null ) {

        ini_set('max_execution_time', 300); //300 seconds = 5 minutes
        $logger = $this->container->get('logger');

        $userSecUtil = $this->container->get('user_security_utility');

        if( !$request ) {
            $request = $this->container->get('request_stack')->getCurrentRequest();
        }

        if( !$authorUser ) {
            $authorUser = $userSecUtil->findSystemUser();
        }

        //generate file name. use PI in the pdf file name as per Ning, Jeff request.
        $fileFullReportUniqueName = $this->constructUniqueFileName($invoice,"Invoice",$invoice->getPrincipalInvestigator());
        $logger->notice("Start to generate PDF invoice ID=".$invoice->getOid()."; filename=".$fileFullReportUniqueName);

        //check and create Report and temp folders
        $reportsUploadPath = "transres/InvoicePDF";  //$userSecUtil->getSiteSettingParameter('reportsUploadPathFellApp');
        if( !$reportsUploadPath ) {
            $reportsUploadPath = "InvoicePDF";
            $logger->warning('InvoicePDFUploadPath is not defined in Site Parameters. Use default "'.$reportsUploadPath.'" folder.');
        }
        $uploadReportPath = $this->uploadDir.'/'.$reportsUploadPath;

        $reportPath = $this->container->get('kernel')->getRootDir() . '/../web/' . $uploadReportPath;
        //echo "reportPath=".$reportPath."<br>";
        //$reportPath = realpath($reportPath);
        //echo "reportPath=".$reportPath."<br>";

        if( !file_exists($reportPath) ) {
            mkdir($reportPath, 0700, true);
            chmod($reportPath, 0700);
        }

        //$outdir = $reportPath.'/temp_'.$invoice->getOid().'/';
        //$outdir = $reportPath.'/'.$invoice->getOid().'/';
        $outdir = $reportPath.'/';

        //echo "before generateApplicationPdf id=".$id."; outdir=".$outdir."<br>";
        //0) generate application pdf
        //$applicationFilePath = $outdir . "application_ID" . $invoice->getOid() . ".pdf";
        $applicationFilePath = $outdir . $fileFullReportUniqueName;

        $this->generatePdf($invoice,$applicationFilePath); //this does not work with https
        //$logger->notice("Successfully Generated Application PDF from HTML for ID=".$id."; file=".$applicationFilePath);

        //$pdfPath = "translationalresearch_invoice_download";
        //$pdfPathParametersArr = array('id' => $invoice->getId());
        //$this->generatePdfPhantomjs($pdfPath,$pdfPathParametersArr,$applicationFilePath,$request);

        //$filenamePdf = $reportPath . '/' . $fileFullReportUniqueName;

        //4) add PDF to invoice DB
        $filesize = filesize($applicationFilePath);
        $documentPdf = $this->createInvoicePdfDB($invoice,"document",$authorUser,$fileFullReportUniqueName,$uploadReportPath,$filesize,'Invoice PDF');
        if( $documentPdf ) {
            $documentPdfId = $documentPdf->getId();
        } else {
            $documentPdfId = null;
        }

        $event = "PDF for Invoice with ID ".$invoice->getOid()." has been successfully created " . $fileFullReportUniqueName . " (PDF document ID".$documentPdfId.")";
        //echo $event."<br>";
        //$logger->notice($event);

        $userSecUtil->createUserEditEvent($this->container->getParameter('translationalresearch.sitename'),$event,$authorUser,$invoice,null,'Invoice PDF Created');

        //delete application temp folder
        //$this->deleteDir($outdir);

        $res = array(
            'filename' => $fileFullReportUniqueName,
            'pdf' => $applicationFilePath,
            'size' => $filesize
        );

        $logger->notice($event);

        return $res;
    }

    protected function constructUniqueFileName($entity,$filenameStr,$subjectUser=null) {

        $logger = $this->container->get('logger');
        $user = $this->secTokenStorage->getToken()->getUser();

        $currentDate = new \DateTime();

        //$userServiceUtil = $this->container->get('user_service_utility');
        //$currentDate = $userServiceUtil->convertToUserTimezone($currentDate,$user);

        if( !$subjectUser ) {
            $subjectUser = $entity->getSubmitter();
        }

        //use only last name as per Bing, Jeff request
        $submitterName = $subjectUser->getLastName();
        if( !$submitterName ) {
            $submitterName = $subjectUser->getUsernameShortest();
        }

        $submitterName = str_replace(" ","-",$submitterName);
        $submitterName = str_replace(".","-",$submitterName);
        $submitterName = str_replace("(","-",$submitterName);
        $submitterName = str_replace(")","-",$submitterName);
        if( $submitterName ) {
            $submitterName = "-" . $submitterName;
        }

        //$serverTimezone = date_default_timezone_get(); //server timezone

        //h-i-s-a
        $filename =
            $filenameStr.
            "-".$entity->getOId().
            //"-".$subjectUser->getLastNameUppercase().
            //"-".$subjectUser->getFirstNameUppercase().
            $submitterName.
            //"-generated-on-".$currentDate->format('m-d-Y').'-at-'.$currentDate->format('H-i-s').'_'.$serverTimezone.
            "-".$currentDate->format('m-d-Y'). //use only date without time
            ".pdf";

        //replace all white spaces to _
        $filename = str_replace(" ","_",$filename);
        $filename = str_replace("/","_",$filename);
        $filename = str_replace("--","-",$filename);

        return $filename;
    }

    //TODO: test it for https
    //use KnpSnappyBundle to convert html to pdf
    //http://wkhtmltopdf.org must be installed on server
    public function generatePdf($invoice,$applicationOutputFilePath) {
        $logger = $this->container->get('logger');
        $logger->notice("Trying to generate PDF in ".$applicationOutputFilePath);
        $userSecUtil = $this->container->get('user_security_utility');

        if( file_exists($applicationOutputFilePath) ) {
            //return;
            $logger->notice("generatePdf: unlink file already exists path=" . $applicationOutputFilePath );
            unlink($applicationOutputFilePath);
        }

        ini_set('max_execution_time', 300); //300 sec

        //testing
        //$wkhtmltopdfpath = $this->container->getParameter('wkhtmltopdfpath');
        //echo "wkhtmltopdfpath=$wkhtmltopdfpath<br>";
        //$default_system_email = $this->container->getParameter('default_system_email');
        //echo "default_system_email=$default_system_email<br>";

        $connectionChannel = $userSecUtil->getSiteSettingParameter('connectionChannel');
        if( !$connectionChannel ) {
            $connectionChannel = 'http';
        }
        //exit("connectionChannel=".$connectionChannel);
        //$connectionChannel = 'http';

        $router = $this->container->get('router');

        $replaceContext = false;
        //$replaceContext = true;
        if( $replaceContext ) {
            //generate application URL
            $context = $router->getContext();

            //http://192.168.37.128/order/app_dev.php/translational-research/download-invoice-pdf/49
            $originalHost = $context->getHost();
            $originalScheme = $context->getScheme();
            $originalBaseUrl = $context->getBaseUrl();

            $context->setHost('localhost');
            //$context->setHost('collage.med.cornell.edu');
            $context->setScheme($connectionChannel);
            $context->setBaseUrl('/order');
        }

        //exit("oid=".$invoice->getOid());

        //invoice download
        $pageUrl = $router->generate('translationalresearch_invoice_download',
            array(
                'id' => $invoice->getId()
            ),
            UrlGeneratorInterface::ABSOLUTE_URL
        ); //this does not work from console: 'order' is missing

        //$logger->notice("### pageUrl=".$pageUrl);
        //echo "pageurl=". $pageUrl . "<br>";
        //exit();

        //$application =
        //TODO: test it for https. Possible replace by:
        //$output = $this->get('knp_snappy.pdf')->getOutputFromHtml($html, array(
        //'cookie' => array(
        //    'PHPSESSID' => $PHPSESSID
        //)));
        //$session->getName() => $session->getId()

        //take care of authentication
        $session = $this->container->get('session');
        $session->save();
        session_write_close();
        $PHPSESSID = $session->getId();

        $this->container->get('knp_snappy.pdf')->generate(
            $pageUrl,
            $applicationOutputFilePath,
            array(
                'cookie' => array(
                    'PHPSESSID' => $PHPSESSID
                )
            )
        );

        if( $replaceContext ) {
            //set back to original context
            $context->setHost($originalHost);
            $context->setScheme($originalScheme);
            $context->setBaseUrl($originalBaseUrl);
        }

        //echo "generated ok! <br>";
    }

    //create invoice report in DB
    protected function createInvoicePdfDB($holderEntity,$holderMethodSingularStr,$author,$uniqueTitle,$path,$filesize,$documentType) {

        $logger = $this->container->get('logger');

        $object = new Document($author);

        $object->setUniqueid($uniqueTitle);
        $object->setCleanOriginalname($uniqueTitle);
        $object->setTitle($uniqueTitle);
        $object->setUniquename($uniqueTitle);

        $object->setUploadDirectory($path);
        $object->setSize($filesize);

        $transformer = new GenericTreeTransformer($this->em, $author, "DocumentTypeList", "UserdirectoryBundle");
        $documentType = trim($documentType);
        $documentTypeObject = $transformer->reverseTransform($documentType);
        if( $documentTypeObject ) {
            $object->setType($documentTypeObject);
        }

        //constructs methods: "getReports", "removeReport", "addReport"
        $getMethod = "get".$holderMethodSingularStr."s";
        $removeMethod = "remove".$holderMethodSingularStr;
        $addMethod = "add".$holderMethodSingularStr;

        //do not remove documents Application PDF
        //move all reports to OldReports
        if( $holderMethodSingularStr == "report" ) {
            foreach ($holderEntity->getReports() as $report) {
                $holderEntity->removeReport($report);
                $holderEntity->addOldReport($report);
            }
        }

        //add report
        $holderEntity->$addMethod($object);

        $this->em->persist($holderEntity);
        $this->em->persist($object);
        $this->em->flush();

        $logger->notice("Document created with ID=".$object->getId()." for ".get_class($holderEntity)." ID=".$holderEntity->getId() . "; documentType=".$documentType);

        return $object;
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





    public function generatePackingSlipPdf($transresRequest,$authorUser,$request) {
        $logger = $this->container->get('logger');
        $userSecUtil = $this->container->get('user_security_utility');

        if( !$transresRequest ) {
            return null;
        }

        //generate file name
        $fileFullReportUniqueName = $this->constructUniqueFileName($transresRequest,"PackingSlip-PDF");
        $logger->notice("Start to generate Packing Slip PDF ID=".$transresRequest->getOid()."; filename=".$fileFullReportUniqueName);

        //check and create Report and temp folders (transresuploadpath)
        $reportsUploadPath = "transres/PackingSlipPDF";  //$userSecUtil->getSiteSettingParameter('reportsUploadPathFellApp');
        if( !$reportsUploadPath ) {
            $reportsUploadPath = "PackingSlipPDF";
            $logger->warning('PackingSlipPDF UploadPath is not defined in Site Parameters. Use default "'.$reportsUploadPath.'" folder.');
        }
        $uploadReportPath = $this->uploadDir.'/'.$reportsUploadPath;

        $reportPath = $this->container->get('kernel')->getRootDir() . '/../web/' . $uploadReportPath;
        //echo "reportPath=".$reportPath."<br>";
        //$reportPath = realpath($reportPath);
        //echo "reportPath=".$reportPath."<br>";

        if( !file_exists($reportPath) ) {
            mkdir($reportPath, 0700, true);
            chmod($reportPath, 0700);
        }

        //$outdir = $reportPath.'/temp_'.$invoice->getOid().'/';
        //$outdir = $reportPath.'/'.$invoice->getOid().'/';
        $outdir = $reportPath.'/';

        //echo "before generateApplicationPdf id=".$id."; outdir=".$outdir."<br>";
        //0) generate application pdf
        //$applicationFilePath = $outdir . "application_ID" . $invoice->getOid() . ".pdf";
        $applicationFilePath = $outdir . $fileFullReportUniqueName;

        //$useKnpSnappy = true;
        $useKnpSnappy = false;
        if( $useKnpSnappy ) {
            $this->generatePdfPackingSlip($transresRequest,$fileFullReportUniqueName,$applicationFilePath);
            //$this->generatePdfPhantomjsPackingSlip($transresRequest,$applicationFilePath,$request);
        } else {
            //packing slip url
            $pdfPath = "translationalresearch_packing_slip_download";
            $pdfPathParametersArr = array('id' => $transresRequest->getId());
            $this->generatePdfPhantomjs($pdfPath, $pdfPathParametersArr, $applicationFilePath, $request);
        }

        $filesize = filesize($applicationFilePath);
        echo "filesize=".$filesize."<br>";

        if( !$filesize ) {
            $logger->warning('PackingSlipPDF failed. filesize=['.$filesize.']; applicationFilePath='.$applicationFilePath);
            throw new \Exception('PackingSlipPDF failed. filesize=['.$filesize.']; applicationFilePath='.$applicationFilePath);
        }

        //add PDF to invoice DB
        //$filesize = filesize($applicationFilePath);
        $documentPdf = $this->createInvoicePdfDB($transresRequest,"packingSlipPdf",$authorUser,$fileFullReportUniqueName,$uploadReportPath,$filesize,'Packing Slip PDF');
        if( $documentPdf ) {
            $documentPdfId = $documentPdf->getId();
        } else {
            $documentPdfId = null;
        }

        $event = "Packing Slip PDF for Work Request with ID ".$transresRequest->getOid()." has been successfully created " . $fileFullReportUniqueName . " (PDF document ID".$documentPdfId.")";
        //echo $event."<br>";
        //$logger->notice($event);

        $userSecUtil->createUserEditEvent($this->container->getParameter('translationalresearch.sitename'),$event,$authorUser,$transresRequest,null,'Packing Slip PDF Created');

        //delete application temp folder
        //$this->deleteDir($outdir);

        $res = array(
            'filename' => $fileFullReportUniqueName,
            'pdf' => $applicationFilePath,
            'size' => $filesize
        );

        $logger->notice($event);

        return $res;

        //exit('exit generatePackingSlipPdf');
    }

    //NOT USED
    //Do not use KnpSnappyBundle to convert html to pdf for packing slip
    //http://wkhtmltopdf.org must be installed on server
    public function generatePdfPackingSlip($transresRequest,$fileFullReportUniqueName,$applicationOutputFilePath) {
        $logger = $this->container->get('logger');
        $logger->notice("Trying to generate PDF in ".$applicationOutputFilePath);
        $userSecUtil = $this->container->get('user_security_utility');
        
        if( file_exists($applicationOutputFilePath) ) {
            //return;
            $logger->notice("generatePdf: unlink file already exists path=" . $applicationOutputFilePath );
            unlink($applicationOutputFilePath);
        }

        ini_set('max_execution_time', 300); //300 sec

        //testing
        //$wkhtmltopdfpath = $this->container->getParameter('wkhtmltopdfpath');
        //echo "wkhtmltopdfpath=$wkhtmltopdfpath<br>";
        //$default_system_email = $this->container->getParameter('default_system_email');
        //echo "default_system_email=$default_system_email<br>";

        $connectionChannel = $userSecUtil->getSiteSettingParameter('connectionChannel');
        if( !$connectionChannel ) {
            $connectionChannel = 'http';
        }

        //generate application URL
        $router = $this->container->get('router');

        //change context only if not localhost or 127.0.0.1
        $replaceContext = false;
        //$replaceContext = true;
        if($replaceContext) {
            $context = $router->getContext();
            //http://192.168.37.128/order/app_dev.php/translational-research/download-invoice-pdf/49
            $context->setHost('localhost');
            $context->setScheme($connectionChannel);
            $context->setBaseUrl('/order');
        }

        //invoice download
        $pageUrl = $router->generate('translationalresearch_packing_slip_download',
            array(
                'id' => $transresRequest->getId()
            ),
            UrlGeneratorInterface::ABSOLUTE_URL
        ); //this does not work from console: 'order' is missing

        //$logger->notice("### pageUrl=".$pageUrl);
        //echo "pageurl=". $pageUrl . "<br>";
        //exit();

        //take care of authentication
        $session = $this->container->get('session');
        $session->save();
        session_write_close();
        $PHPSESSID = $session->getId();

        $this->container->get('knp_snappy.pdf')->generate(
            $pageUrl,
            $applicationOutputFilePath,
            array(
                'cookie' => array(
                    'PHPSESSID' => $PHPSESSID
                )
            )
        );

//        $this->container->get('knp_snappy.image')->generate(
//            $pageUrl,
//            $applicationOutputFilePath
//        );

        //echo "generated ok! <br>";
    }


    //use Phantomjs to convert html to pdf
    public function generatePdfPhantomjs($pdfPath,$pdfPathParametersArr,$applicationOutputFilePath,$request) {
        $logger = $this->container->get('logger');
        $userServiceUtil = $this->container->get('user_service_utility');
        $userSecUtil = $this->container->get('user_security_utility');

        $logger->notice("Trying to generate PDF by Phantomjs in ".$applicationOutputFilePath);
        if( file_exists($applicationOutputFilePath) ) {
            //return;
            $logger->notice("Phantomjs: unlink file already exists path=" . $applicationOutputFilePath );
            unlink($applicationOutputFilePath);
        }

        ini_set('max_execution_time', 300); //300 sec

        //testing
        //$wkhtmltopdfpath = $this->container->getParameter('wkhtmltopdfpath');
        //echo "wkhtmltopdfpath=$wkhtmltopdfpath<br>";
        //$default_system_email = $this->container->getParameter('default_system_email');
        //echo "default_system_email=$default_system_email<br>";

        $connectionChannel = $userSecUtil->getSiteSettingParameter('connectionChannel');
        if( !$connectionChannel ) {
            $connectionChannel = 'http';
        }

        //generate application URL
        $router = $this->container->get('router');
        $context = null;

        if( $request ) {
            $replaceContext = true;
            //$replaceContext = false;
            $schemeAndHttpHost = $request->getSchemeAndHttpHost();
            //exit("schemeAndHttpHost=$schemeAndHttpHost");
            if ($replaceContext && strpos($schemeAndHttpHost, "localhost") === false && strpos($schemeAndHttpHost, "127.0.0.1") === false) {
                //exit('use localhost');
                $context = $router->getContext();

                $originalHost = $context->getHost();
                $originalScheme = $context->getScheme();
                $originalBaseUrl = $context->getBaseUrl();

                //http://192.168.37.128/order/app_dev.php/translational-research/download-invoice-pdf/49
                $context->setHost('localhost');
                //$context->setHost('127.0.0.1');
                $context->setScheme($connectionChannel);
                $context->setBaseUrl('/order');
            }
        }

        //packing slip url
//        $pageUrl = $router->generate('translationalresearch_packing_slip_download',
//            array(
//                'id' => $transresRequest->getId()
//            ),
//            UrlGeneratorInterface::ABSOLUTE_URL
//        ); //this does not work from console: 'order' is missing
        $pageUrl = $router->generate($pdfPath,
            $pdfPathParametersArr,
            UrlGeneratorInterface::ABSOLUTE_URL
        ); //this does not work from console: 'order' is missing
        //echo "pageUrl=$pageUrl <br>";

        //set back to original context
        if( $context ) {
            $context->setHost($originalHost);
            $context->setScheme($originalScheme);
            $context->setBaseUrl($originalBaseUrl);
        }

        //$pageUrl = "http://localhost/order/translational-research/work-request/download-packing-slip-pdf/14078";

        //$ bin/phantomjs.exe examples/rasterize.js 'http://localhost/order/translational-research/work-request/download-packing-slip-pdf/3' result.pdf
        //$cmd = '"' . $libreOfficeConvertToPDFPathFellApp . DIRECTORY_SEPARATOR . $libreOfficeConvertToPDFFilenameFellApp .
        //    '" ' . $libreOfficeConvertToPDFArgumentsdFellApp . ' "' . $outdir . '"';

        if( $userServiceUtil->isWinOs() ) {
            $phantomjs = $userSecUtil->getSiteSettingParameter('phantomjs');
            if (!$phantomjs) {
                throw new \InvalidArgumentException('phantomjs is not defined in Site Parameters.');
            }

            $rasterize = $userSecUtil->getSiteSettingParameter('rasterize');
            if (!$rasterize) {
                throw new \InvalidArgumentException('rasterize is not defined in Site Parameters.');
            }
        } else {
            $phantomjs = $userSecUtil->getSiteSettingParameter('phantomjsLinux');
            if (!$phantomjs) {
                throw new \InvalidArgumentException('phantomjsLinux is not defined in Site Parameters.');
            }

            $rasterize = $userSecUtil->getSiteSettingParameter('rasterizeLinux');
            if (!$rasterize) {
                throw new \InvalidArgumentException('rasterizeLinux is not defined in Site Parameters.');
            }
        }

//        $cmd =
//            '"C:/Users/ch3/Desktop/php/phantomjs-2.1.1-windows/phantomjs-2.1.1-windows/bin/phantomjs.exe"' .
//            ' "C:/Users/ch3/Desktop/php/phantomjs-2.1.1-windows/phantomjs-2.1.1-windows/examples/rasterize.js"' .
//            ' ' . $pageUrl .
//            ' ' . $applicationOutputFilePath
//            ;

        if( $userServiceUtil->isWinOs() ) {
            $phantomjs = '"' . $phantomjs . '"';
            $rasterize = '"' . $rasterize . '"';
            $applicationOutputFilePath = '"' . $applicationOutputFilePath . '"';
        }

        $parameters = "--disk-cache=true";
        if( $connectionChannel == 'https' ) {
            $parameters = $parameters . " --ignore-ssl-errors=true";
        }

        $cmd = $phantomjs . ' ' . $parameters . ' ' . $rasterize . ' ' . $pageUrl . ' ' . $applicationOutputFilePath . ' "A4"';
        //$cmd = $phantomjs . ' ' . $rasterize . ' ' . $pageUrl . ' ' . $applicationOutputFilePath . ' "A4"';
        $logger->notice("Phantomjs cmd=[".$cmd."]");
        //echo "phantomjs cmd=".$cmd."<br>";
        //exit('111');

        //$shellout = shell_exec( $cmd );
        $shellout = exec( $cmd );

//        if( $shellout ) {
//            //echo "shellout=".$shellout."<br>";
//            //$logger->notice("Phantomjs converted output file=" . $applicationOutputFilePath);
//        } else {
//            $logger->error("Phantomjs failed to convert output file=" . $applicationOutputFilePath);
//        }

        //echo "generated ok! <br>";
    }

}