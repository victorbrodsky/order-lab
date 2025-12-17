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

/**
 * Created by PhpStorm.
 * User: oli2002
 * Date: 10/15/14
 * Time: 11:57 AM
 */

namespace App\UserdirectoryBundle\Controller;



use App\UserdirectoryBundle\Entity\Document; //process.py script: replaced namespace by ::class: added use line for classname=Document

use App\UserdirectoryBundle\Form\ImportUsersType;
use App\UserdirectoryBundle\Util\LargeFileDownloader;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use App\UserdirectoryBundle\Controller\OrderAbstractController;


use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;


class UploadController extends OrderAbstractController {

    //Method("DELETE") causes problem: no permission
    #[Route(path: '/file-delete', name: 'employees_file_delete', methods: ['GET', 'POST', 'DELETE'])]
    public function deleteFileAction(Request $request) {
        //exit('deleteFileAction employees exit');
        return $this->deleteFileMethod($request);
    }

    public function deleteFileMethod(Request $request) {
        //exit('deleteFileMethod exit');
        if( false == $this->isGranted('IS_AUTHENTICATED_FULLY') ){
            //exit('no permission');
            return $this->redirect( $this->generateUrl('employees-nopermission') );
        }

        $documentid = $request->get('documentid');
        $commentid = $request->get('commentid');
        $commentclass = $request->get('commenttype');    //comment class
        $sitename = $request->get('sitename');
        //echo "documentid=".$documentid."<br>";
        //echo "commentid=".$commentid."<br>";
        //echo "commentclass=".$commentclass."<br>";

        //$logger = $this->container->get('logger');
        //$logger->notice("deleteFileMethod: documentid=".$documentid);
        //$logger->notice("deleteFileMethod: commentclass=".$commentclass);

        //exit('my uploader');

        $userServiceUtil = $this->container->get('user_service_utility');
        //find document with id
        $em = $this->getDoctrine()->getManager();
        $document = NULL;

        if( $documentid ) {
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:Document'] by [Document::class]
            $document = $em->getRepository(Document::class)->find($documentid);
            //echo "document=".$document." => ";
            //$logger->notice("deleteFileMethod: documentDob=".$document);
        }

        $count = 0;

        if( $document ) {

            //document absolute path
            $documentPath = $document->getServerPath();
            //echo "documentPath=".$documentPath."<br>";
            //$logger->notice("documentPath=".$documentPath);

            //find object where document is belongs
            //$comment = $this->getDoctrine()->getRepository('AppUserdirectoryBundle:'.$commentclass)->findOneBy(array('id'=>$commentid,'documents'=>$document));

//            //set $commentid and $commentclass to the document entity name
//            //example: entityNamespace="App\TranslationalResearchBundle\Entity", entityName="TransResSiteParameters", entityId=111)
//            if( !$commentid or $commentid == 'undefined' ) {
//                $entityId = $document->getEntityId();           //TransResSiteParameters
//                $commentid = $entityId;
//            }
//            if( !$commentclass or $commentclass == 'undefined' ) {
//                $entityName = $document->getEntityName();           //TransResSiteParameters
//                $entityNamespace = $document->getEntityNamespace(); //App\TranslationalResearchBundle\Entity
//                if( $entityName && $entityNamespace ) {
//                    //AppTranslationalResearchBundle:TransResSiteParameters
//                    $entityNamespace = str_replace("\\","",$entityNamespace);
//                    $entityNamespace = str_replace("Entity","",$entityNamespace);
//                    $commentclass = $entityNamespace.":".$entityName;
//                    //$logger->notice("commentclass=".$commentclass);
//                }
//            }

            if( $commentid && $commentid != 'undefined' && $commentclass && $commentclass != 'undefined' ) {

                //convert "AppFellAppBundle:FellowshipApplication" to App\FellAppBundle\Entity\FellowshipApplication
                $classPath = $userServiceUtil->convertNamespaceToClasspath($commentclass);
                if( !$classPath ) {
                    throw new \Exception( 'Can not convert short namespace to class path, commentclass='.$commentclass );
                }

                $repository = $this->getDoctrine()->getRepository($classPath);
                $dql = $repository->createQueryBuilder("comment");
                $dql->select('comment');
                //$dql->innerJoin("comment.documents", "documents");
                $this->setHolderDocumentsDql($dql,$commentclass);
                $dql->where("documents = :document");
                //$query = $em->createQuery($dql)->setParameter("document",$document);
                $query = $dql->getQuery();
                $query->setParameter("document",$document);
                $comments = $query->getResult();

                //echo "comment count=".count($comments)." ";
                //$logger->notice("comment count=".count($comments));
                if( count($comments) > 1 ) {
                    throw new \Exception( 'More than one holder found, count='.count($comments) );
                }

                if( count($comments) > 0 ) {
                    $comment = $comments[0];
                    if( $comment->getId() == $commentid ) {
                        $comment->removeDocument($document);
                        $em->persist($comment);
                        $count++;

//                        //update report for fellapp
//                        $logger = $this->container->get('logger');
//                        $logger->notice("delete document from comment=".$comment);
//                        if( $comment instanceof FellowshipApplication ) {
//                            //update report
//                            $fellappUtil = $this->container->get('fellapp_util');
//                            $fellappUtil->addFellAppReportToQueue( $comment->getId() );
//                        }
                    }
                }

            } //if commentid && $commentclass

            //event log
            $user = $this->getUser();
            $eventDescription = "Document has been deleted from the server by " . $user;
            //get eventtype
            $documentType = $document->getType();
            if( $documentType ) {
                $eventtype = $documentType->getName() . " Deleted";
            } else {
                $eventtype = "Document Deleted";
            }
            $this->setDownloadEventLog($request,$document,$user,$sitename,$eventtype,$eventDescription);

            $count++;
            $em->remove($document);
            $em->flush();

            //remove file from folder
            if( is_file($documentPath) ) {
                unlink($documentPath);
            }

        }

        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');
        $response->setContent($count);
        return $response;
    }

    public function setHolderDocumentsDql($dql,$commentclass) {

        //Here use short namespace AppFellAppBundle:FellowshipApplication, as a legacy,  which is passed from js script user-fileuploads.js
        switch( $commentclass ) {
            case "AppFellAppBundle:FellowshipApplication":
                $str = "comment.coverLetters";
                break;
            case "AppUserdirectoryBundle:Examination":
                $str = "comment.scores";
                break;
//            case "AppTranslationalResearchBundle:TransResSiteParameters":
//                $str = "comment.transresLogo";
//                break;
            default:
                $str = "comment.documents";
        }

        //echo "dql str=".$str."<br>";

        $dql->innerJoin($str, "documents");
    }







    #[Route(path: '/file-download/{id}/{eventtype}', name: 'employees_file_download', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function downloadFileAction(Request $request, $id, $eventtype=null) {
        return $this->downloadFileMethod($request,$id,$this->getParameter('employees.sitename'),$eventtype);
    }

    public function downloadFileMethod($request,$id,$sitename=null,$eventtype=null) {

        if( false == $this->isGranted('IS_AUTHENTICATED_FULLY') ){
            return $this->redirect( $this->generateUrl('employees-nopermission') );
        }

        $userServiceUtil = $this->container->get('user_service_utility');
        $em = $this->getDoctrine()->getManager();
        //$logger = $this->container->get('logger');

        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:Document'] by [Document::class]
        $document = $em->getRepository(Document::class)->find($id);

        //exit("id=".$document->getId());
        //$response = new Response();

        if( $document ) {

            //event log
            $user = $this->getUser();
            $eventDescription = "Document has been downloaded by " . $user;
            $this->setDownloadEventLog($request, $document, $user, $sitename, $eventtype, $eventDescription);

            $originalname = $document->getOriginalnameClean();
            //$abspath = $document->getAbsoluteUploadFullPath();
            $abspath = $userServiceUtil->getDocumentAbsoluteUrl($document);
            $size = $document->getSize();
            if( $abspath || $originalname || $size ) {
                $downloader = new LargeFileDownloader($logger=$this->container->get('logger'));
                $downloader->downloadLargeFile($abspath, $originalname, $size);
            } else {
                exit ("File $originalname is not available");
            }
        } else {
            $user = $this->getUser();
            $eventDescription = "Document download failed by " . $user . ": Document not found by id $id";
            $this->setDownloadEventLog($request, $document, $user, $sitename, $eventtype, $eventDescription);
            echo $eventDescription.".<br> An error notification email has been sent to the system administrator.<br>";

            $userSecUtil = $this->container->get('user_security_utility');
            $userSecUtil->sendEmailToSystemEmail("Document not found by id $id for $sitename", $eventDescription);
            //$logger->error("Document not found by id $id");
            //throw new \Exception("User ".$user.": Document not found by id $id");
        }

        exit;
//            $referer = $request->headers->get('referer');
//            echo "referer=".$referer."<br>";
//            if( strpos((string)$referer, '/login') ) {
//                return $this->redirect( $this->generateUrl('main_common_home') );
//            } else {
//                exit;
//            }
//
//            if(0) {
//                $response->headers->set('Content-Type', 'application/unknown');
//                $response->headers->set('Content-Description', 'File Transfer');
//                $response->headers->set('Content-Disposition', 'attachment; filename="'.$originalname.'"');
//                $response->headers->set('Content-Length', $size);
//                $response->headers->set('Content-Transfer-Encoding', 'binary');
//                $response->setContent(file_get_contents($abspath));
//            }
//
//        } else {
//            $response->setContent('error');
//        }
//
//        return $response;
    }



    #[Route(path: '/file-view/{id}/{viewType}/{eventtype}', name: 'employees_file_view', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function viewFileAction( Request $request, $id, $eventtype=null, $viewType=null ) {
        return $this->viewFileMethod($request,$id,$this->getParameter('employees.sitename'),$eventtype,$viewType);
    }

    public function viewFileMethod($request,$id,$sitename=null,$eventtype=null,$viewType=null) {

        if( false == $this->isGranted('IS_AUTHENTICATED_FULLY') ){
            return $this->redirect( $this->generateUrl('employees-nopermission') );
        }

        $userServiceUtil = $this->container->get('user_service_utility');
        $em = $this->getDoctrine()->getManager();
        $document = $em->getRepository(Document::class)->find($id);

        $originalname = null;
        $size = null;
        $response = new Response();

        if( $document ) {

            //event log
            //if( $viewType != 'snapshot' ) {
            if( strpos((string)$viewType, 'snapshot') === false ) {
                $user = $this->getUser();
                $eventDescription = "Document has been viewed by " . $user;
                $this->setDownloadEventLog($request, $document, $user, $sitename, $eventtype, $eventDescription);
            }

            if( strpos((string)$viewType, 'snapshot') === false ) {
                $originalname = $document->getOriginalnameClean();
                //$abspath = $document->getAbsoluteUploadFullPath(); // http://view.online/c/wcm/pathology/Uploaded/directory/avatars/avatar/20240708194741.jpeg
                //$abspath = $document->getFullServerPath(); // /usr/local/***/Uploaded/directory/avatars/56fbf9e8867c3.jpg
                $size = $document->getSize();

                $abspath = $userServiceUtil->getDocumentAbsoluteUrl($document);

                //$filenameClean = str_replace("\\", "/", $abspath);
                //if( file_exists($filenameClean) === false ) {
                //    exit('File '.$filenameClean.' does not exist');
                //}
                //echo "not snapshot abspath=$abspath <br>";
                //exit('exit notsnapshot');
            } else {

                $viewTypeArr = explode("-", $viewType);
                if (count($viewTypeArr) > 1) {
                    $resize = $viewTypeArr[1];
                } else {
                    $resize = null;
                }
                //$resize = null; //testing: disable resize images
                //exit('$resize='.$resize);

                //TODO: resize thumbnails http://127.0.0.1/order/fellowship-applications/generate-thumbnails
                //get small thumbnail - i.e. used for the fellowship application list //small-18sec, original-25sec
                if( $resize == "small" ) {
                    $originalname = $document->getOriginalnameClean();
                    //$size = $document->getSize();
                    //$size = $document->getSizeBySize($resize);
                    //$abspath = $document->getAbsoluteUploadFullPath($resize,true);
                    $abspath = $document->getFileSystemPath($resize);
                    //$abspath = "http://127.0.0.1/order/Uploaded/fellapp/FellowshipApplicantUploads/small-1557157978ID1J9qjngqM1Bt_PZedHfJtX1S_sALg8YS-.jpg";
                    if( file_exists($abspath) ) {
                        //echo "The file $abspath exists <br>";
                        //$abspath = $document->getAbsoluteUploadFullPath($resize,true);
                        $abspath = $userServiceUtil->getDocumentAbsoluteUrl($document,$resize,true);
                    } else {
                        //echo "The file $abspath does not exists <br>";
                        //try to re-generate thumbnails for jpg and jpeg
                        if( strpos((string)$originalname, '.jpg') !== false || strpos((string)$originalname, '.jpeg') !== false ) {
                            $destRes = $userServiceUtil->generateTwoThumbnails($document);
                            if( $destRes ) {
                                $logger = $this->container->get('logger');
                                $logger->notice("Try to re-generate small thumbnail for $originalname. destRes=" . $destRes);
                            }
                        }

                        //$abspath = $document->getAbsoluteUploadFullPath($resize);
                        $abspath = $userServiceUtil->getDocumentAbsoluteUrl($document);
                    }
                    $size = $document->getSizeBySize($resize);
                    //exit('exit small: '.$abspath."; size=".$size);
                }
                //get small thumbnail - i.e. used for the fellowship application view
                elseif( $resize == "medium" ) {
                    $originalname = $document->getOriginalnameClean();
                    //$size = $document->getSize();
                    //$size = $document->getSizeBySize($resize);
                    //$abspath = $document->getAbsoluteUploadFullPath($resize,true);
                    //$abspath = $document->getFileSystemPath($resize);
                    $abspath = $userServiceUtil->getDocumentAbsoluteUrl($document);
                    if( file_exists($abspath) ) {
                        //echo "The file $abspath exists <br>";
                        //$abspath = $document->getAbsoluteUploadFullPath($resize,true);
                        $abspath = $userServiceUtil->getDocumentAbsoluteUrl($document,$resize,true);
                    } else {
                        //echo "The file $abspath does not exists <br>";
                        //try to re-generate thumbnails
                        if( strpos((string)$originalname, '.jpg') !== false || strpos((string)$originalname, '.jpeg') !== false ) {
                            $destRes = $userServiceUtil->generateTwoThumbnails($document);
                            if( $destRes ) {
                                $logger = $this->container->get('logger');
                                $logger->notice("Try to re-generate medium thumbnail for $originalname. destRes=" . $destRes);
                            }
                        }

                        //$abspath = $document->getAbsoluteUploadFullPath($resize);
                        $abspath = $userServiceUtil->getDocumentAbsoluteUrl($document,$resize);
                    }
                    $size = $document->getSizeBySize($resize);
                    //exit('exit medium: '.$abspath);
                } else {
                    //default
                    $originalname = $document->getOriginalnameClean();
                    //$abspath = $document->getAbsoluteUploadFullPath();
                    $abspath = $userServiceUtil->getDocumentAbsoluteUrl($document);
                    $size = $document->getSize();
                    //$logger = $this->container->get('logger');
                    //$logger->notice("viewFileMethod: originalname=".$originalname. ", abspath=" . $abspath. ", size=".$size);
                    //exit ("viewFileMethod: originalname=".$originalname. ", abspath=" . $abspath. ", size=".$size);
                    //echo "default abspath=$abspath <br>";
                }
            }

            //There is no small, medium size for PDF. PDF is not resize and always the same size.
            if( !$size ) {
                $size = $document->getSize();
            }

            //abspath=http://127.0.0.1/order/Uploaded/fellapp/FellowshipApplicantUploads/1557157978ID1J9qjngqM1Bt_PZedHfJtX1S_sALg8YS-.jpg
            //$abspath = "http://127.0.0.1/order/Uploaded/fellapp/FellowshipApplicantUploads/small-1557157978ID1J9qjngqM1Bt_PZedHfJtX1S_sALg8YS-.jpg";
            //echo "abspath=$abspath <br>";
            //$logger = $this->container->get('logger');
            //$logger->notice("viewFileMethod: originalname=".$originalname. ", abspath=" . $abspath. ", size=".$size);
            //exit ("viewFileMethod: originalname=".$originalname. ", abspath=" . $abspath. ", size=".$size);
            //exit(111);
            //$logger = $this->container->get('logger');
            //$logger->notice("abspath=$abspath");

//            $serverPath = $document->getFullServerPath();
//            $serverPath = "C:\Users\cinav\Documents\WCMC\ORDER\order-lab\orderflex\public\Uploaded\directory\avatars\avatar\\20150910195909.jpeg";
//            echo "serverPath=$serverPath <br>";
//            if( !file_exists($serverPath) ) {
//                exit ("File path $serverPath is not available");
//            } else {
//                echo "File path $serverPath is available <br>";
//            }

            if( $abspath || $originalname || $size ) {
                //echo "abspath=".$abspath."<br>";
                //echo "originalname=[".$originalname."]<br>";
                //echo "$abspath: size=".$size."<br>";
                //exit(111);
                $downloader = new LargeFileDownloader($logger=$this->container->get('logger'));
                ////$filepath, $filename=null, $size=null, $retbytes=true, $action="download", $viewType=null
                //$viewType = null; //viewType allow to resize file, but it does not work properly, so disable it by setting to null
                $downloader->downloadLargeFile($abspath, $originalname, $size, true, "view", $viewType);
            } else {
                exit ("File $originalname is not available");
            }

            exit;
        } else {
            $response->setContent('error');
        }

        return $response;
    }

    //make sure to update server DocumentTypeList and EventTypeList
    //"Complete Fellowship Application in PDF"=>"Complete Fellowship Application PDF"
    //"Old Complete Fellowship Application in PDF"=>"Old Complete Fellowship Application PDF"
    //"Fellowship Application Upload"=>"Fellowship Application Document"
    //EventTypeList: "Fellowship Application Upload Downloaded"=>"Fellowship Application Document Downloaded"
    public function setDownloadEventLog($request,$document,$user,$sitename,$eventtype,$eventDescription) {

        //try to get document type
        if( !$eventtype ) {
            if( $document && $document->getType() ) {
                $eventtype = $document->getType()->getName() . " Downloaded";
            } else {
                $eventtype = "Document Downloaded";
            }
        }

        //$logger = $this->container->get('logger');
        if( $eventtype && $sitename ) {
            //$logger->notice("document event log created: eventDescription=".$eventDescription);
            $userSecUtil = $this->container->get('user_security_utility');
            //$user = $this->getUser();
            //$eventDescription = "Document has been downloaded by " . $user;
            $userSecUtil->createUserEditEvent($sitename,$eventDescription,$user,$document,$request,$eventtype);
        } else {
            //$logger->notice("document event log not created!!!! : eventDescription=".$eventDescription);
        }
    }


    /**
     * Upload "Import Users" excel file for processing
     */
    #[Route(path: '/import-users/spreadsheet ', name: 'employees_import_users_excel', methods: ['GET', 'POST'])]
    #[Template('AppUserdirectoryBundle/Admin/import-users.html.twig')]
    public function importExcelUsersFileAction( Request $request )
    {

        if( false === $this->isGranted('ROLE_USERDIRECTORY_ADMIN') ) {
            $this->addFlash(
                'notice',
                'You do not have permission to import users.'
            );
            return $this->redirect($this->generateUrl('employees-nopermission'));
        }

        $form = $this->createForm(ImportUsersType::class,null);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            // $form->getData() holds the submitted values
            // but, the original `$task` variable has also been updated
            $inputFileName = $form['file']->getData();
            //exit('inputFileName='.$inputFileName);

            //$ext = $inputFileName->guessExtension();
            //echo "ext=".$ext."<br>";
            //exit('file');

            //$inputFileName = __DIR__ . '/../../../../../importLists/ImportUsersTemplate.xlsx';

            $userGenerator = $this->container->get('user_generator');

            //list v1
            //$count_users = $userGenerator->generateUsersExcelV1();

            //list v2 provided by Jessica
            $res = $userGenerator->generateUsersExcelV2($inputFileName);

            $this->addFlash(
                'notice',
                $res
            );

            //exit();
            //return $this->redirect($this->generateUrl('employees_listusers'));
        }



        //return $this->container->get('templating')->renderResponse('FOSUserBundle/Profile/show.html.'.$this->getParameter('fos_user.template.engine'), array('user' => $user));
        return array(
            'form' => $form->createView(),
            'sitename' => $this->getParameter('employees.sitename'),
            'title' => 'Import Users'
        );
    }

    #[Route(path: '/import-users/template/', name: 'employees_import_users_template_excel', methods: ['GET'])]
    public function importExcelUsersTemplateFileAction() {

        $rootDir = $this->container->get('kernel')->getRootDir();
        //echo "rootDir=".$rootDir."<br>";

        //C:/Users/ch3/Documents/MyDocs/WCMC/ORDER/scanorder/importLists/ImportUsersTemplate.xlsx
//        $templateLink = "file:///C:".DIRECTORY_SEPARATOR."Users".DIRECTORY_SEPARATOR."ch3".DIRECTORY_SEPARATOR."Documents".
//            DIRECTORY_SEPARATOR."MyDocs".DIRECTORY_SEPARATOR."WCM".DIRECTORY_SEPARATOR."ORDER".
//            DIRECTORY_SEPARATOR."scanorder".DIRECTORY_SEPARATOR."importLists".DIRECTORY_SEPARATOR."ImportUsersTemplate.xlsx";

        $templateLink = "file:///".$rootDir.DIRECTORY_SEPARATOR."..".DIRECTORY_SEPARATOR."..".DIRECTORY_SEPARATOR.
            "importLists".DIRECTORY_SEPARATOR."ImportUsersTemplate.xlsx";
        //echo "templateLink=".$templateLink."<br>";

        //$templateLink = "file:///C:/Users/ch3/Documents/MyDocs/WCMC/ORDER/scanorder/importLists/ImportUsersTemplate.xlsx";
        //echo "templateLink=".$templateLink."<br>";

        $response = new BinaryFileResponse($templateLink);
        $response->setContentDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT,'ImportUsersTemplate.xlsx');

        //$mimeType = 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';
        //$response->headers->set('Content-Type: '.$mimeType);

        return $response;
    }


} 