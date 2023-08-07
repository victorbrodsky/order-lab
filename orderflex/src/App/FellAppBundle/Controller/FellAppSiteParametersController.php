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

namespace App\FellAppBundle\Controller;

use App\FellAppBundle\Entity\FellappSiteParameter;
use App\FellAppBundle\Form\FellappSiteParameterType;
use App\UserdirectoryBundle\Controller\SiteParametersController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use App\UserdirectoryBundle\Controller\OrderAbstractController;


use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Component\Routing\Annotation\Route;

use App\UserdirectoryBundle\Entity\SiteParameters;
use App\UserdirectoryBundle\Form\SiteParametersType;
use App\UserdirectoryBundle\Util\UserUtil;

use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

/**
 * SiteParameters controller.
 */
#[Route(path: '/settings')]
class FellAppSiteParametersController extends SiteParametersController
{

    /**
     * Lists all SiteParameters entities.
     */
    #[Route(path: '/site-settings/', name: 'fellapp_sitesettings_siteparameters', methods: ['GET'])]
    #[Template('AppUserdirectoryBundle/SiteParameters/site-index.html.twig')]
    public function indexSiteSettingsAction(Request $request)
    {
        if( false === $this->isGranted('ROLE_FELLAPP_ADMIN') ) {
            return $this->redirect( $this->generateUrl('fellapp-nopermission') );
        }
        return $this->indexParameters($request);
    }

    /**
     * Lists all SiteParameters entities.
     */
    #[Route(path: '/', name: 'fellapp_siteparameters', methods: ['GET'])]
    #[Template('AppUserdirectoryBundle/SiteParameters/index.html.twig')]
    public function indexAction(Request $request)
    {
        if( false === $this->isGranted('ROLE_FELLAPP_ADMIN') ) {
            return $this->redirect( $this->generateUrl('fellapp-nopermission') );
        }
        return $this->indexParameters($request);
    }

    /**
     * Displays a form to edit an existing SiteParameters entity.
     */
    #[Route(path: '/{id}/edit', name: 'fellapp_siteparameters_edit', methods: ['GET'])]
    #[Template('AppUserdirectoryBundle/SiteParameters/edit.html.twig')]
    public function editAction(Request $request,$id)
    {
        return $this->editParameters($request,$id);
    }

    /**
     * Edits an existing SiteParameters entity.
     */
    #[Route(path: '/{id}', name: 'fellapp_siteparameters_update', methods: ['PUT'])]
    #[Template('AppUserdirectoryBundle/SiteParameters/edit.html.twig')]
    public function updateAction(Request $request, $id)
    {
        return $this->updateParameters($request, $id);
    }


    /**
     * FellAppSiteParameter
     */
    #[Route(path: '/specific-site-parameters/edit-page/', name: 'fellapp_siteparameters_edit_specific_site_parameters', methods: ['GET', 'POST'])]
    #[Template('AppFellAppBundle/SiteParameter/edit.html.twig')]
    public function fellappSiteParameterEditAction( Request $request ) {

        if( false === $this->isGranted('ROLE_FELLAPP_ADMIN') ) {
            return $this->redirect( $this->generateUrl('fellapp-nopermission') );
        }

        $cycle = "edit";

        $fellappSiteParameter = $this->getOrCreateNewFellAppParameters($cycle);

        $form = $this->createFellAppSiteParameterForm($fellappSiteParameter,$cycle);
        $form->handleRequest($request);

        if( $form->isSubmitted() && $form->isValid() ) {
            $em = $this->getDoctrine()->getManager();

            //set end default date as "Start" date - 1 day
            //exit('end date');
            if( $fellappSiteParameter->getFellappAcademicYearEnd() === NULL ) {
                $startDate = $fellappSiteParameter->getFellappAcademicYearStart();
                if( $startDate ) {
                    //"Start" date - 1 day
                    $thisEndDate = clone $startDate;
                    $thisEndDate->modify('-1 day');
                    $fellappSiteParameter->setFellappAcademicYearEnd($thisEndDate);
                }
//                else {
//                    $currentYear = intval(date("Y"));
//                    $june30 = new \DateTime($currentYear."-06-30");
//                    //echo "set start date=".$june30->format('yyyy-mm-dd')."<br>";
//                    $fellappSiteParameter->setFellappAcademicYearEnd($june30);
//                }
            }

            //exit('submit');
            $em->persist($fellappSiteParameter);
            $em->flush();

            return $this->redirect($this->generateUrl('fellapp_siteparameters'));
        }

        return array(
            'entity' => $fellappSiteParameter,
            'form'   => $form->createView(),
            'cycle' => $cycle,
            'title' => "Update Fellowship Specific Site Parameters"
        );
    }

    /**
     * Google integration
     */
    #[Route(path: '/google-integration', name: 'fellapp_google_integration', methods: ['GET'])]
    #[Template('AppFellAppBundle/SiteParameter/google-integration.html.twig')]
    public function fellappIntegrationShowAction( Request $request ) {

        if( false === $this->isGranted('ROLE_FELLAPP_ADMIN') ) {
            return $this->redirect( $this->generateUrl('fellapp-nopermission') );
        }

        $cycle = "show";

//        $dir = $this->container->get('kernel')->getProjectDir() . DIRECTORY_SEPARATOR .
//            "src" . DIRECTORY_SEPARATOR . "App" .
//            DIRECTORY_SEPARATOR . "FellAppBundle" . DIRECTORY_SEPARATOR . "Util" .
//            DIRECTORY_SEPARATOR . "GoogleForm"
//        ;

        //$manualUrl = $dir . DIRECTORY_SEPARATOR . "Readme.docx";
        //echo "manualUrl=$manualUrl <br>";

        $filename = "README.pdf";
        $bundleFileName = "orderassets\\AppFellAppBundle\\docs\\".$filename;
        
        return array(
            'manualUrl' => $bundleFileName,
            'cycle' => $cycle,
            'title' => "Google Integration Manual"
        );
    }

    #[Route(path: '/install-gas/', name: 'fellapp_install_gas', methods: ['GET', 'POST'])]
    #[Template('AppFellAppBundle/SiteParameter/install-gas.html.twig')]
    public function runInstallGasAction(Request $request)
    {

        if (!$this->isGranted('ROLE_PLATFORM_DEPUTY_ADMIN')) {
            return $this->redirect($this->generateUrl('fellapp-nopermission'));
        }

        //$user = $this->getUser();
        //$toEmail = $user->getSingleEmail();

        if( isset($_POST['params']) ) {
            $params = $_POST['params'];
        }

        if( isset($params) ) {
            $projectDir = $this->container->get('kernel')->getProjectDir();
            //exit("projectDir=".$projectDir);

            $path = $projectDir . "/../" . DIRECTORY_SEPARATOR .
                "utils" . DIRECTORY_SEPARATOR . "google-integration";

            $path = realpath($path);
            echo "path=".$path."<br>";

            $logFile = $path . DIRECTORY_SEPARATOR . "pythonexeclog.log";

            if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
                $python = "python";
                //$python = $path.DIRECTORY_SEPARATOR."venv".DIRECTORY_SEPARATOR."Scripts".DIRECTORY_SEPARATOR."python.exe";
                $clasppath = "C:/Users/ch3/AppData/Roaming/npm/clasp";
            } else {
                $python = "python3";
                //$python = $path.DIRECTORY_SEPARATOR."venv".DIRECTORY_SEPARATOR."bin".DIRECTORY_SEPARATOR."python3";
                $clasppath = "clasp";
            }

            //$command = $python ." " . "'" . $path . DIRECTORY_SEPARATOR . "fellapp.py" . "'";
            $command = $python . " " . $path . DIRECTORY_SEPARATOR . "fellappsimple.py";

            $command = $command . " --clasp " . $clasppath;
            $command = $command . " --title " . $params;
            echo "command=".$command."<br>";

            //clasp login
            //https://accounts.google.com/o/oauth2/v2/auth/oauthchooseaccount?access_type=offline&scope=https%3A%2F%2F
            //www.googleapis.com%2Fauth%2Fscript.deployments%20https%3A%2F%2F
            //www.googleapis.com%2Fauth%2Fscript.projects%20https%3A%2F%2F
            //www.googleapis.com%2Fauth%2Fscript.webapp.deploy%20https%3A%2F%2F
            //www.googleapis.com%2Fauth%2Fdrive.metadata.readonly%20https%3A%2F%2F
            //www.googleapis.com%2Fauth%2Fdrive.file%20https%3A%2F%2F
            //www.googleapis.com%2Fauth%2Fservice.management%20https%3A%2F%2F
            //www.googleapis.com%2Fauth%2Flogging.read%20https%3A%2F%2F
            //www.googleapis.com%2Fauth%2Fuserinfo.email%20https%3A%2F%2F
            //www.googleapis.com%2Fauth%2Fuserinfo.profile%20https%3A%2F%2F
            //www.googleapis.com%2Fauth%2Fcloud-platform&response_type=code&
            //client_id=1072944905499-vm2v2i5dvn0a0d2o4ca36i1vge8cvbn0.apps.googleusercontent.com&
            //redirect_uri=http%3A%2F%2Flocalhost%3A57375&service=lso&o2v=2&flowName=GeneralOAuthFlow
            //https://console.cloud.google.com/
            //https://stackoverflow.com/questions/60838607/clasp-local-login

            //$prefixFellApp = "FellApp"; //MyScriptFellApp
            //$prefixRecLet = "RecLet"; //MyScriptRecLet

            //$scriptFellApp = $params . $prefixFellApp;
            //$scriptRecLet = $params . $prefixRecLet;

            //$dir = $path . DIRECTORY_SEPARATOR . "scripts";
            //$dirFellApp = $dir . DIRECTORY_SEPARATOR . "" . $scriptFellApp;
            //$dirRecLet = $dir . DIRECTORY_SEPARATOR . "" . $scriptRecLet;

            //$commandFellApp = $command . " --dir " . $dirFellApp . " --title " . $scriptFellApp;
            //$commandRecLet = $command . " --dir " . $dirRecLet . " --title " . $scriptRecLet;

            //$commandFellApp = $clasppath . " -v"; //testing
            //$commandFellApp = $python . " -V";
            //$commandFellApp = $clasppath . " login";
            //$res = exec($commandFellApp);
            //dump($res);
            //exit('111');

            //$command = $command . " " . $params;
            //$command = $python. " -V";
            //$command = $command . " > " .$logFile;
            //echo "commandFellApp=".$commandFellApp."<br>";
            //echo "commandRecLet=".$commandRecLet."<br>";
            //exit('111');

            //$userServiceUtil = $this->container->get('user_service_utility');

            //$commandFellApp = explode(" ",$commandFellApp);
            //$commandRecLet = explode(" ",$commandRecLet);

            $commandArr = explode(" ",$command);

            $logDir = $path.DIRECTORY_SEPARATOR."scripts";
            $envArr = array('HTTP' => 1);
            $execTime = 30; //3 min
            //ini_set('max_execution_time', $execTime);
            $process = new Process($commandArr,$logDir,$envArr,null,$execTime);

            try {
                $process->mustRun();
                $buffer = $process->getOutput();
                $buffer = '<code><pre>'.$buffer.'</pre></code>';
                dump($buffer);
                exit("OK");
            } catch (ProcessFailedException $exception) {
                $buffer = $exception->getMessage();
                dump($buffer);
                exit("Error");
            }

            //dump($res);
            //exit("res=");
            //$res = null;

            //$resFellApp = $this->runProcess($commandFellApp,$logFile);
            //$resRecLet = $this->runProcess($commandRecLet,$logFile);
            //exit("resFellApp=".$resFellApp."; resRecLet=".$resRecLet);

            //Flash
            $this->addFlash(
                'notice',
                'Executed command ' . $commandArr." with result:" . $buffer
            );

            return $this->redirectToRoute('fellapp_install_gas');
        }

        //exit("email res=".$emailRes);

        return array();
    }

    public function runProcess($command) {
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            echo 'This is a server using Windows! <br>';
            $windows = true;
            $linux = false;
        } else {
            echo 'This is a server not using Windows! Assume Linux <br>';
            $windows = false;
            $linux = true;
        }

        if( $linux ) {
            //$process = new Process($command);
            $process = Process::fromShellCommandline($command);
            $process->setTimeout(1800); //sec; 1800 sec => 30 min
            $process->run();
            if (!$process->isSuccessful()) {
                throw new ProcessFailedException($process);
            }
            $res = $process->getOutput();
        }

        if( $windows ) {
            $res = exec($command);
            //$res = shell_exec($command);
            //echo "res=".$res."<br>";
        }

        //chdir($old_path);

        return $res;
    }

    /**
     * FellAppSiteParameter Show
     */
    #[Route(path: '/specific-site-parameters/show/', name: 'fellapp_siteparameters_show_specific_site_parameters', methods: ['GET'])]
    #[Template('AppFellAppBundle/SiteParameter/show.html.twig')]
    public function fellappSiteParameterShowAction( Request $request ) {

        if( false === $this->isGranted('ROLE_FELLAPP_ADMIN') ) {
            return $this->redirect( $this->generateUrl('fellapp-nopermission') );
        }

        $cycle = "show";

        $fellappSiteParameter = $this->getOrCreateNewFellAppParameters($cycle);
        //echo "fellappSiteParameter=".$fellappSiteParameter->getId()."<br>";

        $form = $this->createFellAppSiteParameterForm($fellappSiteParameter,$cycle);
        
        return array(
            'entity' => $fellappSiteParameter,
            'form'   => $form->createView(),
            'cycle' => $cycle,
            'title' => "Fellowship Specific Site Parameters"
        );
    }

    public function createFellAppSiteParameterForm($entity, $cycle) {
        $user = $this->getUser();
        $em = $this->getDoctrine()->getManager();

        $disabled = false;
        if( $cycle == "show" ) {
            $disabled = true;
        }

        $params = array(
            'cycle' => $cycle,
            'user' => $user,
            'em' => $em,
            'container' => $this->container,
        );

        $form = $this->createForm(FellappSiteParameterType::class, $entity, array(
            'form_custom_value' => $params,
            'disabled' => $disabled
        ));

        return $form;
    }

    //Get or Create a new FellAppSiteParameter
    public function getOrCreateNewFellAppParameters( $cycle ) {
        $em = $this->getDoctrine()->getManager();
        $entities = $em->getRepository(SiteParameters::class)->findAll();
        if( count($entities) != 1 ) {
            throw new \Exception( 'Must have only one parameter object. Found '.count($entities).'object(s)' );
        }
        $siteParameters = $entities[0];

        $fellappSiteParameter = $siteParameters->getFellappSiteParameter();

        //create one FellAppSiteParameter
        if( !$fellappSiteParameter ) {
            //echo "FellAppSiteParameter null <br>";
            $fellappSiteParameter = new FellappSiteParameter();

            $siteParameters->setFellappSiteParameter($fellappSiteParameter);
            $em->flush();
        }

        if(0) {
            //set start default 1 July 2021 if NULL
            if ($fellappSiteParameter->getFellappAcademicYearStart() === NULL) {
                $currentYear = intval(date("Y"));
                $july1 = new \DateTime($currentYear . "-07-01");
                //echo "set start date=".$july1->format('yyyy-mm-dd')."<br>";
                $fellappSiteParameter->setFellappAcademicYearStart($july1);

            }
            //set end default 30 June 2021 if NULL
            if ($fellappSiteParameter->getFellappAcademicYearEnd() === NULL) {
                $startDate = $fellappSiteParameter->getFellappAcademicYearStart();
                if ($startDate) {
                    //"Start" date - 1 day
                    $thisEndDate = clone $startDate;
                    $thisEndDate->modify('-1 day');
                    $fellappSiteParameter->setFellappAcademicYearEnd($thisEndDate);
                } else {
                    $currentYear = intval(date("Y"));
                    $june30 = new \DateTime($currentYear . "-06-30");
                    //echo "set start date=".$june30->format('yyyy-mm-dd')."<br>";
                    $fellappSiteParameter->setFellappAcademicYearEnd($june30);
                }
            }
            //echo "set  date <br>";
        }

        return $fellappSiteParameter;
    }
    
}
