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



use App\OrderformBundle\Entity\Patient; //process.py script: replaced namespace by ::class: added use line for classname=Patient
use App\TranslationalResearchBundle\Entity\AntibodyCategoryTagList;
use App\TranslationalResearchBundle\Entity\AntibodyLabList;
use App\TranslationalResearchBundle\Entity\AntibodyPanelList;
use App\UserdirectoryBundle\Entity\AuthServerNetworkList;
use App\UserdirectoryBundle\Entity\AuthPartnerServerList;
use App\UserdirectoryBundle\Entity\AuthUserGroupList;
use App\UserdirectoryBundle\Entity\HostedUserGroupList;
use App\UserdirectoryBundle\Entity\TenantUrlList;
use App\UserdirectoryBundle\Entity\TransferStatusList;
use App\UserdirectoryBundle\Entity\UsernameType; //process.py script: replaced namespace by ::class: added use line for classname=UsernameType
use App\UserdirectoryBundle\Entity\RoomList; //process.py script: replaced namespace by ::class: added use line for classname=RoomList
use App\UserdirectoryBundle\Entity\SuiteList; //process.py script: replaced namespace by ::class: added use line for classname=SuiteList
use App\UserdirectoryBundle\Entity\FloorList; //process.py script: replaced namespace by ::class: added use line for classname=FloorList
//use App\ResAppBundle\Entity\VisaStatus; //process.py script: replaced namespace by ::class: added use line for classname=VisaStatus
//use App\ResAppBundle\Entity\LanguageProficiency; //process.py script: replaced namespace by ::class: added use line for classname=LanguageProficiency
use App\UserdirectoryBundle\Entity\Logger; //process.py script: replaced namespace by ::class: added use line for classname=Logger

use App\DashboardBundle\Entity\ChartList;
use App\DashboardBundle\Entity\ChartTypeList;
use App\DashboardBundle\Entity\DataSourceList;
use App\DashboardBundle\Entity\FilterList;
use App\DashboardBundle\Entity\TopicList;
use App\DashboardBundle\Entity\UpdateFrequencyList;
use App\DashboardBundle\Entity\VisualizationList;
use App\DashboardBundle\Util\DashboardUtil;
use App\FellAppBundle\Entity\FellAppRank;
use App\FellAppBundle\Entity\FellAppStatus;
//use App\FellAppBundle\Entity\LanguageProficiency;
//use App\FellAppBundle\Entity\VisaStatus;
use App\OrderformBundle\Controller\ScanListController;
use App\ResAppBundle\Entity\ApplyingResidencyTrack;
use App\ResAppBundle\Entity\LearnAreaList;
use App\ResAppBundle\Entity\PostSophList;
use App\ResAppBundle\Entity\ResAppFitForProgram;
use App\ResAppBundle\Entity\ResAppRank;
use App\ResAppBundle\Entity\ResAppStatus;
use App\ResAppBundle\Entity\SpecificIndividualList;
use App\TranslationalResearchBundle\Entity\BusinessPurposeList;
use App\TranslationalResearchBundle\Entity\CollDivList;
use App\TranslationalResearchBundle\Entity\CollLabList;
use App\TranslationalResearchBundle\Entity\CompCategoryList;
use App\TranslationalResearchBundle\Entity\IrbApprovalTypeList;
use App\TranslationalResearchBundle\Entity\IrbStatusList;
use App\TranslationalResearchBundle\Entity\OrderableStatusList;
use App\TranslationalResearchBundle\Entity\OtherRequestedServiceList;
use App\TranslationalResearchBundle\Entity\PriceTypeList;
use App\TranslationalResearchBundle\Entity\ProjectTypeList;
use App\TranslationalResearchBundle\Entity\RequestCategoryTypeList;
use App\TranslationalResearchBundle\Entity\RequesterGroupList;
use App\TranslationalResearchBundle\Entity\SpecialtyList;
use App\TranslationalResearchBundle\Entity\TissueProcessingServiceList;
use App\TranslationalResearchBundle\Entity\WorkQueueList;
use App\UserdirectoryBundle\Entity\AdditionalCommunicationList;
use App\UserdirectoryBundle\Entity\AuthorshipRoles;
use App\UserdirectoryBundle\Entity\BloodProductTransfusedList;
use App\UserdirectoryBundle\Entity\BloodTypeList;
use App\UserdirectoryBundle\Entity\CCIPlateletTypeTransfusedList;
use App\UserdirectoryBundle\Entity\CCIUnitPlateletCountDefaultValueList;
use App\UserdirectoryBundle\Entity\CertifyingBoardOrganization;
use App\UserdirectoryBundle\Entity\CityList;
use App\UserdirectoryBundle\Entity\ClericalErrorList;
use App\UserdirectoryBundle\Entity\CollaborationTypeList;
use App\UserdirectoryBundle\Entity\CommentGroupType;
use App\UserdirectoryBundle\Entity\ComplexPlateletSummaryAntibodiesList;
use App\UserdirectoryBundle\Entity\FormNode;
use App\UserdirectoryBundle\Entity\HealthcareProviderCommunicationList;
use App\UserdirectoryBundle\Entity\HealthcareProviderSpecialtiesList;
use App\UserdirectoryBundle\Entity\ImportanceList;
use App\UserdirectoryBundle\Entity\LabResultFlagList;
use App\UserdirectoryBundle\Entity\LabResultNameList;
use App\UserdirectoryBundle\Entity\LabResultUnitsMeasureList;
use App\UserdirectoryBundle\Entity\LifeFormList;
use App\UserdirectoryBundle\Entity\ListAbstract;
use App\UserdirectoryBundle\Entity\MedicalLicenseStatus;
use App\UserdirectoryBundle\Entity\EventObjectTypeList;
use App\UserdirectoryBundle\Entity\MonthsList;
use App\UserdirectoryBundle\Entity\ObjectTypeList;
use App\UserdirectoryBundle\Entity\OrganizationalGroupDefault;
use App\UserdirectoryBundle\Entity\OrganizationalGroupType;
use App\UserdirectoryBundle\Entity\LinkTypeList;
use App\UserdirectoryBundle\Entity\LocaleList;
use App\UserdirectoryBundle\Entity\PathologyResultSignatoriesList;
use App\UserdirectoryBundle\Entity\Permission;
use App\UserdirectoryBundle\Entity\PermissionActionList;
use App\UserdirectoryBundle\Entity\PermissionList;
use App\UserdirectoryBundle\Entity\PermissionObjectList;
use App\UserdirectoryBundle\Entity\PlateletTransfusionProductReceivingList;
use App\UserdirectoryBundle\Entity\PlatformListManagerRootList;
use App\UserdirectoryBundle\Entity\PositionTrackTypeList;
use App\UserdirectoryBundle\Entity\PositionTypeList;
use App\UserdirectoryBundle\Entity\SexList;
use App\UserdirectoryBundle\Entity\SiteList;
use App\UserdirectoryBundle\Entity\SpotPurpose;
use App\UserdirectoryBundle\Entity\TitlePositionType;
use App\UserdirectoryBundle\Entity\TrainingTypeList;
use App\UserdirectoryBundle\Entity\TransfusionAntibodyScreenResultsList;
use App\UserdirectoryBundle\Entity\TransfusionCrossmatchResultsList;
use App\UserdirectoryBundle\Entity\TransfusionDATResultsList;
use App\UserdirectoryBundle\Entity\TransfusionHemolysisCheckResultsList;
use App\UserdirectoryBundle\Entity\TransfusionProductStatusList;
use App\UserdirectoryBundle\Entity\TransfusionReactionTypeList;
use App\UserdirectoryBundle\Entity\ViewModeList;
use App\UserdirectoryBundle\Entity\WeekDaysList;
use App\UserdirectoryBundle\Form\DataTransformer\SingleUserWrapperTransformer;
use App\UserdirectoryBundle\Form\HierarchyFilterType;
use App\UserdirectoryBundle\Util\UserSecurityUtil;
use App\VacReqBundle\Entity\VacReqApprovalTypeList;
use App\VacReqBundle\Entity\VacReqFloatingTextList;
use App\VacReqBundle\Entity\VacReqFloatingTypeList;
use App\VacReqBundle\Entity\VacReqRequestTypeList;
//use App\UserdirectoryBundle\Controller\OrderAbstractController;


use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Component\Intl\Languages;
use Symfony\Component\Intl\Locales;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\StreamedResponse;

use App\UserdirectoryBundle\Entity\PerSiteSettings;
use App\UserdirectoryBundle\Entity\AdministrativeTitle;
use App\UserdirectoryBundle\Entity\BuildingList;
use App\UserdirectoryBundle\Entity\CompletionReasonList;
use App\UserdirectoryBundle\Entity\DocumentTypeList;
use App\UserdirectoryBundle\Entity\EmploymentType;
use App\UserdirectoryBundle\Entity\FellowshipSubspecialty;
use App\UserdirectoryBundle\Entity\FellowshipTitleList;
use App\UserdirectoryBundle\Entity\GeoLocation;
use App\UserdirectoryBundle\Entity\HonorTrainingList;
use App\UserdirectoryBundle\Entity\InstitutionType;
use App\UserdirectoryBundle\Entity\Location;
use App\UserdirectoryBundle\Entity\MedicalSpecialties;
use App\UserdirectoryBundle\Entity\MedicalTitleList;
use App\UserdirectoryBundle\Entity\ResearchLab;
use App\UserdirectoryBundle\Entity\ResidencySpecialty;
use App\UserdirectoryBundle\Entity\SourceOrganization;
use App\UserdirectoryBundle\Entity\SourceSystemList;
use App\UserdirectoryBundle\Entity\TrainingDegreeList;
use App\UserdirectoryBundle\Entity\User;

use App\UserdirectoryBundle\Entity\SiteParameters;
use App\UserdirectoryBundle\Util\UserUtil;
use App\UserdirectoryBundle\Entity\Roles;
use App\UserdirectoryBundle\Entity\Institution;
//use App\UserdirectoryBundle\Entity\Department;
//use App\UserdirectoryBundle\Entity\Division;
//use App\UserdirectoryBundle\Entity\Service;
use App\UserdirectoryBundle\Entity\States;
use App\UserdirectoryBundle\Entity\BoardCertifiedSpecialties;
use App\UserdirectoryBundle\Entity\EmploymentTerminationType;
use App\UserdirectoryBundle\Entity\EventTypeList;
use App\UserdirectoryBundle\Entity\IdentifierTypeList;
use App\UserdirectoryBundle\Entity\FellowshipTypeList;
use App\UserdirectoryBundle\Entity\ResidencyTrackList;
use App\UserdirectoryBundle\Entity\LocationTypeList;
use App\UserdirectoryBundle\Entity\Countries;
use App\UserdirectoryBundle\Entity\Equipment;
use App\UserdirectoryBundle\Entity\EquipmentType;
use App\UserdirectoryBundle\Entity\LocationPrivacyList;
use App\UserdirectoryBundle\Entity\RoleAttributeList;
use App\UserdirectoryBundle\Entity\LanguageList;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;


//Notes:
//To turn off foreign key constraint globally: "SET GLOBAL FOREIGN_KEY_CHECKS=0;"
#[Route(path: '/admin')]
class AdminController extends OrderAbstractController
{

    /**
     * run: http://localhost/order/directory/admin/first-time-login-generation-init/
     * run: http://localhost/order/directory/admin/first-time-login-generation-init/https
     */
    #[Route(path: '/first-time-login-generation-init/', name: 'first-time-login-generation-init')]
    #[Route(path: '/first-time-login-generation-init/https', name: 'first-time-login-generation-init-https')]
    public function firstTimeLoginGenerationAction(Request $request)
    {
        $logger = $this->container->get('logger');
        $em = $this->getDoctrine()->getManager();
        $userServiceUtil = $this->container->get('user_service_utility');
        $users = $roles = $em->getRepository(User::class)->findAll();
        $logger->notice('firstTimeLoginGenerationAction: users='.count($users));

        if (count($users) == 0) {

            //1) get systemuser
            $userSecUtil = $this->container->get('user_security_utility');
            $systemuser = $userSecUtil->findSystemUser();

            if (!$systemuser) {

                $logger->notice('Start generate system user');
                $default_time_zone = null;
                $usernamePrefix = "local-user";

                //$userUtil = new UserUtil();
                $userUtil = $this->container->get('user_utility');
                $userUtil->generateUsernameTypes(null, false);
                //$userkeytype = $em->getRepository('AppUserdirectoryBundle:UsernameType')->findOneByAbbreviation("local-user");

                $this->generateSitenameList(null);

                //$userSecUtil = $this->container->get('user_security_utility');
                $userkeytype = $userSecUtil->getUsernameType($usernamePrefix);
                //echo "userkeytype=".$userkeytype."; ID=".$userkeytype->getId()."<br>";
                $systemuser = $userUtil->createSystemUser($userkeytype,$default_time_zone);
                $authUtil = $this->container->get('authenticator_utility');
                $encoded = $authUtil->getEncodedPassword($systemuser,"systemuserpass");

                $systemuser->setPassword($encoded);
                $systemuser->setLocked(false);

                $em->persist($systemuser);
                $em->flush();

                $logger->notice('Finished generate system user: '.$systemuser);
                //exit("system user created");
            }

            //generate or update default admin user
            $adminRes = $this->generateAdministratorAction(true);
            $logger->notice('Finished generate AdministratorAction. adminRes='.$adminRes);

            //generate multitenancy parameters (SERVER ROLE AND NETWORK ACCESS, etc)
            $count_generateAuthUserGroupList = $this->generateAuthUserGroupList();
            $count_generateAuthServerNetworkList = $this->generateAuthServerNetworkList();
            $count_generateAuthPartnerServerList = $this->generateAuthPartnerServerList();
            //$count_generateHostedUserGroupList = $this->generateHostedUserGroupList();
            $count_generateTenantUrlList = $this->generateTenantUrlList();
            $logger->notice('Finished generate multitenancy parameters. '.
                'generateAuthUserGroupList='.$count_generateAuthUserGroupList.
                '; generateAuthServerNetworkList='.$count_generateAuthServerNetworkList.
                '; generateAuthPartnerServerList='.$count_generateAuthPartnerServerList.
                //'; generateHostedUserGroupList='.$count_generateHostedUserGroupList
                '; generateTenantUrlList='.$count_generateTenantUrlList
            );

            if( $request->get('_route') == "first-time-login-generation-init-https" ) {
                //set channel in SiteParameters to https
//                $entities = $em->getRepository(SiteParameters::class)->findAll();
//                if (count($entities) != 1) {
//                    $userServiceUtil = $this->container->get('user_service_utility');
//                    $userServiceUtil->generateSiteParameters();
//                    $entities = $em->getRepository(SiteParameters::class)->findAll();
//                }
//                if (count($entities) != 1) {
//                    exit('Must have only one SiteParameters object. Found ' . count($entities) . ' object(s)');
//                    //throw new \Exception( 'Must have only one parameter object. Found '.count($entities).' object(s)' );
//                }
//                $entity = $entities[0];
                //getSingleSiteSettingsParam();
                //Get single or generate SettingParameter (Singleton)
                $entity = $userServiceUtil->getSingleSiteSettingParameter($createIfEmpty=false);
                $entity->setConnectionChannel("https");
                //$em->flush($entity);
                $em->flush();
            }

            $logger->notice('Start updateApplication (run deploy script)');
            $updateres = $this->updateApplication(); //run deploy script

            $adminRes = $adminRes . " <br> " .$updateres;

            $logger->notice('Finished initialization. adminRes='.$adminRes);

        } else {
            //$adminRes = 'Admin user already exists';
            //$adminRes = "System has been initialized successfully.";
            $adminRes = 'Admin user has been already created';
            //exit('users already exists');
            $logger->notice('Finished initialization. '.$adminRes);
        }


        $this->addFlash(
            'notice',
            $adminRes
        );

//        //make sure sitesettings is initialized
//        $siteParams = $em->getRepository(SiteParameters::class)->findAll();
//        if( count($siteParams) != 1 ) {
//            $userServiceUtil = $this->container->get('user_service_utility');
//            $userServiceUtil->generateSiteParameters();
//        }
        
        return $this->redirect($this->generateUrl('employees_home'));
    }

    #[Route(path: '/update-system-source-code/', name: 'user_update_system_source_code')]
    public function updateSourceCodeAction(Request $request) {
        if(
            false === $this->isGranted('ROLE_PLATFORM_ADMIN')
        ) {
            return $this->redirect($this->generateUrl('employees-nopermission'));
        }
        //exit('updateSourceCodeAction ok');

        //set_time_limit(0);
        //ini_set('memory_limit', '512M');

        $userServiceUtil = $this->container->get('user_service_utility');
        $userServiceUtil->runDeployScript(true,false,false);

        $updateres = "Source code and composer has been successfully updated";

        $this->addFlash(
            'pnotify',
            $updateres
        );

        return $this->redirect($this->generateUrl('employees_home'));
    }

    #[Route(path: '/update-system-source-composer/', name: 'user_update_system_source_composer')]
    public function updateComposerAction(Request $request) {
        if( false === $this->isGranted('ROLE_PLATFORM_ADMIN') ) {
            return $this->redirect($this->generateUrl('employees-nopermission'));
        }

        set_time_limit(0);
        ini_set('memory_limit', '512M');

        $userServiceUtil = $this->container->get('user_service_utility');
        $userServiceUtil->runDeployScript(false,true,false);

        $updateres = "Source code and composer has been successfully updated";

        $this->addFlash(
            'pnotify',
            $updateres
        );

        return $this->redirect($this->generateUrl('employees_home'));
    }

    #[Route(path: '/update-system-cache-assets/', name: 'user_update_system_cache_assets')]
    public function updateSystemAction(Request $request) {
        if( false === $this->isGranted('ROLE_PLATFORM_DEPUTY_ADMIN') ) {
            return $this->redirect($this->generateUrl('employees-nopermission'));
        }

        $updateres = $this->updateApplication();

//        $this->addFlash(
//            'notice',
//            $updateres
//        );
        $this->addFlash(
            'pnotify',
            $updateres
        );

        return $this->redirect($this->generateUrl('employees_home'));
    }

    public function updateApplication() {
        //set_time_limit(0);
        //ini_set('memory_limit', '512M');
        if( 1 ) {
            $userServiceUtil = $this->container->get('user_service_utility');
            $userServiceUtil->runDeployScript(false,false,true);
        } else {
            $this->clearCache();
            $this->installAssets();
            //exit('<br>exit update application');
        }

        $updateres = "Deploy script run successfully: Cache cleared, Assets dumped";

        return $updateres;
    }

    //user_update_migrate_db
    #[Route(path: '/update-migrate-db/', name: 'user_update_migrate_db')]
    public function updateMigrateDbAction(Request $request) {
        if( false === $this->isGranted('ROLE_PLATFORM_DEPUTY_ADMIN') ) {
            return $this->redirect($this->generateUrl('employees-nopermission'));
        }

        $userServiceUtil = $this->container->get('user_service_utility');
        $updateres = $userServiceUtil->updateMigrateDb();

        $this->addFlash(
            'pnotify',
            $updateres
        );

        return $this->redirect($this->generateUrl('employees_home'));
    }

//    public function runDeployScript_ORIG($update, $composer, $cache) {
//        if( false === $this->isGranted('ROLE_PLATFORM_DEPUTY_ADMIN') ) {
//            return $this->redirect($this->generateUrl('employees-nopermission'));
//        }
//
//        if( $update || $composer ) {
//            if (false === $this->isGranted('ROLE_PLATFORM_ADMIN')) {
//                return $this->redirect($this->generateUrl('employees-nopermission'));
//            }
//        }
//
//        //$this->container->compile();
//
//        $dirSep = DIRECTORY_SEPARATOR;
//
//        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
//            echo 'This is a server using Windows! <br>';
//            $windows = true;
//            $linux = false;
//        } else {
//            echo 'This is a server not using Windows! Assume Linux <br>';
//            $windows = false;
//            $linux = true;
//        }
//
//        $old_path = getcwd();
//        //echo "webPath=$old_path<br>";
//
//        $deploy_path = str_replace("public","",$old_path);
//        echo "deploy_path=$deploy_path<br>";
//        //exit('111');
//
//        if( is_dir($deploy_path) ) {
//            //echo "deploy path exists! <br>";
//        } else {
//            //echo "not deploy path exists: $deploy_path <br>";
//            exit('No deploy path exists in the filesystem; deploy_path=: '.$deploy_path);
//        }
//
//        //switch to deploy folder
//        echo chdir($deploy_path)."<br>";
//        echo "pwd=[".exec("pwd")."]<br>";
//        //exec("pwd");
//
//        // Everything for owner and for others
//        //chmod($old_path, 0777);
//
//        //$linux
//        if( $linux ) {
//            if( $cache ) {
//                //$this->runProcess("sudo chown -R www-data:www-data ".$old_path);
//                //$this->runProcess("php bin" . $dirSep . "console assets:install");
//                //$this->runProcess("php bin" . $dirSep . "console cache:clear --env=prod --no-debug");
//
//                $this->runProcess("bash deploy.sh");
//            }
//
//            if( $update ) {
//                //$this->runProcess("sudo chown -R www-data:www-data ".$old_path);
//                //$this->runProcess("cd /usr/local/bin/order-lab/");
//                //$this->runProcess("chmod 777");
//                $this->runProcess("git pull");
//            }
//
//            if( $composer ) {
//                $this->runProcess("export COMPOSER_HOME=/usr/local/bin/order-lab && /usr/local/bin/composer self-update");
//                $this->runProcess("export COMPOSER_HOME=/usr/local/bin/order-lab && /usr/local/bin/composer install");
//            }
//        }
//
//        //$windows
//        if( $windows ) {
//            if( $cache ) {
//                //echo "assets:install=" . exec("php bin" . $dirSep . "console assets:install") . "<br>";
//                //echo "cache:clear=" . exec("php bin" . $dirSep . "console cache:clear --env=prod --no-debug") . "<br>";
//                echo "windows deploy=" . exec("bash deploy.sh") . "<br>";
//
//                //remove var/cache/prod
//                //$cachePathOld = "var" . $dirSep . "cache" . $dirSep . "prod";
//                //$cachePathNew = "var" . $dirSep . "cache" . $dirSep . "pro_";
//                //echo "rm =" . exec("php var/console assets:install") . "<br>";
//
////                if (is_dir($cachePathOld)) {
////                    echo "cachePathOld exists! <br>";
////                } else {
////                    echo "cachePathOld not exists: $cachePathOld <br>";
////                    exit('error');
////                }
////                if (is_dir($cachePathNew)) {
////                    echo "cachePathNew exists! <br>";
////                } else {
////                    echo "cachePathNew not exists: $cachePathNew <br>";
////                    exit('error');
////                }
////                echo exec("rmdir " . $cachePathOld . " /S /Q") . "<br>";
////                echo exec("rename " . $cachePathNew . " " . $cachePathOld) . "<br>";
////                if (is_dir($cachePathNew)) {
////                    echo exec("rmdir " . $cachePathNew . " /S /Q") . "<br>";
////                }
//            }//cache
//
//            if( $update ) {
//                echo "git pull=" . exec("git pull") . "<br>";
//            }
//
//            if( $composer ) {
//                echo "composer.phar self-update=" . exec("composer.phar self-update") . "<br>";
//                echo "composer.phar install=" . exec("composer.phar install") . "<br>";
//            }
//        }
//
//        // Everything for owner, read and execute for others
//        //chmod($old_path, 0755);
//
//        //switch back to web folder
//        $output = chdir($old_path);
//        echo "<pre>$output</pre>";
//
//        return;
//        //exit('exit runDeployScript');
//    }
//    public function runProcess($script) {
//        //$process = new Process($script);
//        $process = Process::fromShellCommandline($script);
//        $process->setTimeout(1800); //sec; 1800 sec => 30 min
//        $process->run();
//        if (!$process->isSuccessful()) {
//            throw new ProcessFailedException($process);
//        }
//        echo $process->getOutput();
//    }
/////////////////////  NOT USED FOR DEPLOY ////////////////////////
    //    public function clearCache() {
    //        //echo exec('whoami') . "<br>";
    //
    //        $appPath = $this->getParameter('kernel.root_dir');
    //        echo "appPath=".$appPath."<br>";
    //
    //        $dirSep = DIRECTORY_SEPARATOR;
    //
    //        $cachePath = ''.$appPath. $dirSep .'cache';
    //
    //        if( is_dir($cachePath) ) {
    //            echo "dir! <br>";
    //        } else {
    //            echo "not dir! <br>";
    //            exit('not dir:'.$cachePath);
    //        }
    //
    //        echo exec("chmod -R 777 ".$cachePath)."<br>";
    //
    //        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
    //            echo 'This is a server using Windows!';
    //            //http://stackoverflow.com/questions/1965787/how-to-delete-files-subfolders-in-a-specific-directory-at-command-prompt-in-wind
    //            echo exec("rmdir ".$cachePath." /S /Q")."<br>";
    //        } else {
    //            echo 'This is a server not using Windows! Assume Linux';
    //            echo exec("rm -r ".$cachePath)."<br>";
    //        }
    //
    //
    //    }
    //    public function getCachePath() {
    //        $appPath = $this->getParameter('kernel.root_dir');
    //        echo "appPath=".$appPath."<br>";
    //
    //        $dirSep = DIRECTORY_SEPARATOR;
    //
    //        $cachePath = ''.$appPath. $dirSep .'cache';
    //
    //        if( is_dir($cachePath) ) {
    //            echo "dir! <br>";
    //        } else {
    //            echo "not dir! <br>";
    //            exit('not dir:'.$cachePath);
    //        }
    //
    //        return $cachePath;
    //    }
    //    public function clearCacheByService() {
    //
    //        //$fs = new Filesystem();
    //        //$fs->remove($this->getParameter('kernel.cache_dir'));
    //        //return;
    //
    //        $command = $this->container->get('user_cache_clear');
    //        $input = new ArgvInput(array('--env=' . $this->getParameter('kernel.environment')));
    //        $output = new ConsoleOutput();
    //        $command->run($input, $output);
    //        //exit($output);
    //    }
    //    public function installAssets() {
    //        $dirSep = DIRECTORY_SEPARATOR;
    //
    //        $appPath = $this->getParameter('kernel.root_dir');
    //        echo "appPath=".$appPath."<br>";
    //
    //        if( 1 ) {
    //            //$webPath = getcwd();
    //            //echo "webPath=$webPath<br>";
    //
    //            $console = $appPath . $dirSep . 'console';
    //            if( file_exists($console) ) {
    //                echo "console exists! <br>";
    //            } else {
    //                echo "not console exists: $console <br>";
    //                exit('error');
    //            }
    //
    //            echo exec("php " . $console . " assets:install " );
    //            echo exec("php " . $console . " assetic:dump --env=prod --no-debug " );
    //
    //            //echo exec("php " . $console . " assets:install");
    //            //echo exec("php " . $console . " assetic:dump");
    //
    //        } else {
    //
    //            //echo shell_exec("chmod -R 777 ".$webPath)."<br>";
    //
    //            //$path = ''.$appPath.'\\..\\'.'deploy_prod';
    //            $path = ''.$appPath."$dirSep..$dirSep".'deploy_prod';
    //
    //            echo "path=".$path."<br>";
    //            if( file_exists($path) ) {
    //                echo "path exists! <br>";
    //            } else {
    //                echo "path not exists: $path <br>";
    //                exit('error');
    //            }
    //
    //            echo exec("chmod -R 777 " . $path) . "<br>";
    //            echo exec("bash " . $path) . "<br>";
    //        }
    //
    //        //exit('exit install assests');
    //    }
    //    public function installAssetsByService() {
    //        $command = $this->container->get('user_install_assets');
    //        $input = new ArgvInput(
    //            array(
    //                '--env=' . $this->getParameter('kernel.environment'),
    //                //'--symlink',
    //                //'--relative'
    //            )
    //        );
    //        $output = new ConsoleOutput();
    //        $command->run($input, $output);
    //        exit($output);
    //    }
    //    //Testing method
    //    public function cccAction()
    //    {
    //        $kernel = $this->container->get('kernel');
    //        $application = new \Symfony\Bundle\FrameworkBundle\Console\Application($kernel);
    //        $application->setAutoExit(false);
    //        $options = array('command' => 'cache:clear',"--env" => 'prod', '--no-warmup' => true);
    //        $res = $application->run(new \Symfony\Component\Console\Input\ArrayInput($options));
    //        echo "res=".$res."<br>";
    //        return new Response();
    //    }
    //    //Testing method
    //    public function runCommand($command, $arguments = array())
    //    {
    //        $kernel = $this->container->get('kernel');
    //        $app = new Application($kernel);
    //
    //        $args = array_merge(array('command' => $command), $arguments);
    //
    //        $input = new ArrayInput($args);
    //        $output = new NullOutput();
    //
    //        return $app->doRun($input, $output);
    //    }
    /////////////////////  EOF NOT USED FOR DEPLOY ////////////////////////
    /**
     * Admin Page
     */
    #[Route(path: '/lists/', name: 'user_admin_index', methods: ['GET'])]
    #[Template('AppUserdirectoryBundle/Admin/index.html.twig')]
    public function indexAction()
    {

        $environment = 'dev'; //default

        $em = $this->getDoctrine()->getManager();
        $params = $roles = $em->getRepository(SiteParameters::class)->findAll();

        if( count($params) > 1 ) {
            throw new \Exception( 'Must have only one parameter object. Found '.count($params).'object(s)' );
        }

        if( count($params) == 1 ) {
            $param = $params[0];
            $environment = $param->getEnvironment();
        }

        return $this->render('AppUserdirectoryBundle/Admin/index.html.twig', array('environment'=>$environment));
    }

    /**
     * Admin Page
     */
    #[Route(path: '/hierarchies/', name: 'user_admin_hierarchy_index', methods: ['GET'])]
    #[Template('AppUserdirectoryBundle/Admin/hierarchy-index.html.twig')]
    public function indexHierarchyAction()
    {

        $environment = 'dev'; //default

        $em = $this->getDoctrine()->getManager();
        $params = $roles = $em->getRepository(SiteParameters::class)->findAll();

        if( count($params) > 1 ) {
            throw new \Exception( 'Must have only one parameter object. Found '.count($params).'object(s)' );
        }

        if( count($params) == 1 ) {
            $param = $params[0];
            $environment = $param->getEnvironment();
        }

        $filters = $this->getDefaultHierarchyFilter();

        return $this->render('AppUserdirectoryBundle/Admin/hierarchy-index.html.twig', array('environment'=>$environment,'filters'=>$filters));
    }
    public function getDefaultHierarchyFilter() {
        $filterStr = array();
        //add a filter that checks if the site is "live" and hides this node in the live environment
        $userSecUtil = $this->container->get('user_security_utility');
        $environment = $userSecUtil->getSiteSettingParameter('environment');
        if( $environment == 'live' ) { //live
            $filterStr['filter[types][0]'] = 'default';
            $filterStr['filter[types][1]'] = 'user-added';
        }
        //print_r($filterStr);
        return $filterStr;
    }

    /**
     * Populate DB
     */
    #[Route(path: '/populate-all-site-lists-with-default-values', name: 'user_generate_all_site', methods: ['GET'])]
    #[Template('AppUserdirectoryBundle/Admin/index.html.twig')]
    public function generateAllSiteAction(Request $request)
    {
        $logger = $this->container->get('logger');
        $logger->notice("generateAllSiteAction");

        $em = $this->getDoctrine()->getManager();

        //1)
        $count = $this->generateCountryList();
        $countryCount = $count['country'];
        $cityCount = $count['city'];
        $msg1 = 'Added '.$countryCount.' countries and '.$cityCount.' cities';
        $em->clear();

        //2)
        $msg2 = $this->generateAll($request);
        $em->clear();

        $this->addFlash(
            'notice',
            $msg1 . "<br><br><br>" .
            $msg2
        );

        //ini_set('max_execution_time', $max_exec_time); //set back to the original value

        //3)
        return $this->redirect($this->generateUrl('generate_all'));

        //return $this->redirect($this->generateUrl('user_admin_index'));
    }

    /**
     * Populate DB ( 3) Populate All Lists with Default Values (Part B) )
     */
    #[Route(path: '/populate-all-lists-with-default-values', name: 'user_generate_all', methods: ['GET'])]
    #[Template('AppUserdirectoryBundle/Admin/index.html.twig')]
    public function generateAllAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $logger = $this->container->get('logger');
        $logger->notice("generateAllAction");

        //testing
        if(0) {
            \Locale::setDefault('en');

            $elements = array();
            $language = Languages::getName('en');
            $elements['en'] = $language;
            $language = Languages::getName('en_US');
            $elements['en_US'] = $language;
            dump($elements);
            //exit('Exit Intl');

            $elements = array();
            $locale = Locales::getName('en');
            $elements['en'] = $locale;
            $locale = Locales::getName('en_US');
            $elements['en_US'] = $locale;
            dump($elements);

            exit("Intl test");
        }

        $msg = $this->generateAll($request);
        $em->clear();

        //convert flash array to a single message
        $flashBag = $request->getSession()->getFlashBag()->all();
        if( count($flashBag['notice']) > 5 ) {
            //dump($flashBag);
            //exit('111');
            $flashBagStr = implode("<br>", $flashBag['notice']);
            $this->addFlash(
                'notice',
                $flashBagStr
            );
        }

        $this->addFlash(
            'notice',
            $msg
        );

        //ini_set('max_execution_time', $max_exec_time); //set back to the original value

        //return $this->redirect($this->generateUrl('generate_all'));

        return $this->redirect($this->generateUrl('employees_siteparameters'));
    }

    public function generateAll($request) {

        $logger = $this->container->get('logger');
        $logger->notice("Start generateAll");

        //$userutil = new UserUtil();
        $userUtil = $this->container->get('user_utility');
        $user = $this->getUser();

        //ini_set('memory_limit', '3072M');
        //$max_exec_time = ini_get('max_execution_time');
        ini_set('max_execution_time', 1800); //1800 seconds = 30 minutes; it will set back to original value after execution of this script


        //testing
        //$userServiceUtil = $this->container->get('user_service_utility');
        //$userServiceUtil->createCronsLinux();
        //exit('eof createCronsLinux');
        //$count_generateChartsList = $this->generateChartsList();
        //$count_generateChartTopicList = $this->generateChartTopicList(); //hierarchy
        //exit('eof generate Charts test');

        //$default_time_zone = $this->getParameter('default_time_zone');

        //$count_EventTypeListSync = $this->syncEventTypeListDb(); //must be the first to update already existing objects. Can run on empty DB

        //testing
        //$count_setObjectTypeForAllLists = $this->setObjectTypeForAllLists();
        //$this->generateLabResultNames();
        //$this->generateLocationsFromExcel();
//        $adminRes = $this->generateAdministratorAction(); //testing this cause logout
//        exit('$adminRes='.$adminRes);
//        $logger->notice("Finished generate AdministratorAction");
//        return "Finished generateAdministratorAction";

        //testing
        //$count_generateHostedUserGroupList = $this->generateHostedUserGroupList();

        //$count_countryList = $this->generateCountryList();

        $count_sitenameList = $this->generateSitenameList($user);

        $count_institutiontypes = $this->generateInstitutionTypes();                                //must be first
        $count_OrganizationalGroupType = $this->generateOrganizationalGroupType();                  //must be first
        $count_institution = $this->generateInstitutions();                                         //must be first
        $count_auxinstitution = $this->generateAuxiliaryInstitutions();
        $count_appTitlePositions = $this->generateAppTitlePositions();

        $count_CommentGroupType = $this->generateCommentGroupType();

        //$count_siteParameters = $this->generateSiteParameters();    //can be run only after institution generation
        $userServiceUtil = $this->container->get('user_service_utility');
        $count_siteParameters = $userServiceUtil->generateSiteParameters();

        $count_Permissions = $this->generatePermissions();
        $count_PermissionObjects = $this->generatePermissionObjects();
        $count_PermissionActions = $this->generatePermissionActions();
        $logger->notice("Finished generatePermissionActions");

        $count_residencyTrackList = $this->generateResidencyTrackList(); //must be run before generateRoles
        $logger->notice("Finished generateResidencyTrackList");

        $count_roles = $this->generateRoles($request);
        $count_employmentTypes = $this->generateEmploymentTypes();
        $count_states = $this->generateStates();
        $logger->notice("Finished generateStates");

        $count_languages = $this->generateLanguages();
        $logger->notice("Finished generateLanguages");

        $count_locales = $this->generateLocales();
        $logger->notice("Finished generateLocales");

        $count_locationTypeList = $this->generateLocationTypeList();
        $logger->notice("Finished generateLocationTypeList");
        $count_locprivacy = $this->generateLocationPrivacy();
        $logger->notice("Finished generateLocationPrivacy");
        $count_generateDefaultOrgGroupSiteParameters = $this->generateDefaultOrgGroupSiteParameters();
        $logger->notice("Finished generateDefaultOrgGroupSiteParameters");

        $count_terminationTypes = $this->generateTerminationTypes();
        $count_eventTypeList = $this->generateEventTypeList();
        $count_usernameTypeList = $userUtil->generateUsernameTypes($user);
        $count_identifierTypeList = $this->generateIdentifierTypeList();
        $count_fellowshipTypeList = $this->generateFellowshipTypeList();
        //$count_residencyTrackList = $this->generateResidencyTrackList();
        //$logger->notice("Finished generateResidencyTrackList");

        $count_medicalTitleList = $this->generateMedicalTitleList();
        $count_medicalSpecialties = $this->generateMedicalSpecialties();
        $logger->notice("Finished generateMedicalSpecialties");

        $count_equipmentType = $this->generateEquipmentType();
        $count_equipment = $this->generateEquipment();
        $logger->notice("Finished generateEquipment");

        $count_buildings = $this->generateBuildings();
        $count_locations = $this->generateLocations();
        $logger->notice("Finished generateLocations");
        //return "Finished generateLocations";

        $count_SpotPurpose = $this->generateSpotPurpose();

        $count_reslabs = $this->generateResLabs();

        $count_testusers = $this->generateTestUsers();

        $count_boardSpecialties = $this->generateBoardSpecialties();

        $count_sourcesystems = $this->generateSourceSystems();

        $count_generateViewModeList = $this->generateViewModeList();

        $count_documenttypes = $this->generateDocumentTypes();
        $count_generateLinkTypes = $this->generateLinkTypes();
        $logger->notice("Finished generateLinkTypes");

        //training
        $count_completionReasons = $this->generateCompletionReasons();
        $count_trainingDegrees = $this->generateTrainingDegrees();
        //$count_majorTrainings = $this->generateMajorTrainings();
        //$count_minorTrainings = $this->generateMinorTrainings();
        $count_HonorTrainings = $this->generateHonorTrainings();
        $count_FellowshipTitles = $this->generateFellowshipTitles();
        $count_residencySpecialties = $this->generateResidencySpecialties();
        $logger->notice("Finished generateResidencySpecialties");
        //return "Finished generateResidencySpecialties";

        $count_fellowshipSubspecialties = $this->generateDefaultFellowshipSubspecialties();

        $count_sourceOrganizations = $this->generatesourceOrganizations();
        $count_generateImportances = $this->generateImportances();
        $count_generateAuthorshipRoles = $this->generateAuthorshipRoles();
        $logger->notice("Finished generateAuthorshipRoles");

        $count_sex = $this->generateSex();

        $count_PositionTypeList = $this->generatePositionTypeList();

        $count_generateMedicalLicenseStatus = $this->generateMedicalLicenseStatus();

        $count_generateCertifyingBoardOrganization = $this->generateCertifyingBoardOrganization();
        $count_TrainingTypeList = $this->generateTrainingTypeList();
        $logger->notice("Finished generateTrainingTypeList");
        //return "Finished generateTrainingTypeList";

        $count_FellAppStatus = $this->generateFellAppStatus();
        $count_FellAppRank = $this->generateFellAppRank();
        $count_FellAppVisaStatus = $this->generateFellAppVisaStatus();
        $count_LanguageProficiency = $this->generateLanguageProficiency();

        $count_ResAppStatus = $this->generateResAppStatus();
        $count_ResAppRank = $this->generateResAppRank();
        $count_ResAppVisaStatus = $this->generateResAppVisaStatus();
        $count_PostSophList = $this->generatePostSophList();
        $count_ResAppLanguageProficiency = $this->generateResAppLanguageProficiency();
        $count_ResAppFitForProgram = $this->generateResAppFitForProgram();

        $count_ResAppApplyingResidencyTrack = $this->generateResAppApplyingResidencyTrack();
        $count_ResAppLearnAreaList = $this->generateResAppLearnAreaList();
        $count_ResAppSpecificIndividualList = $this->generateResAppSpecificIndividualList();

        $logger->notice("Finished generateResAppSpecificIndividualList");
        //return "Finished generateResAppSpecificIndividualList";

        $collaborationtypes = $this->generateCollaborationtypes();
//        $count_Permissions = $this->generatePermissions();
//        $count_PermissionObjects = $this->generatePermissionObjects();
//        $count_PermissionActions = $this->generatePermissionActions();
//        $logger->notice("Finished generatePermissionActions");

        $count_ObjectTypeActions = $this->generateObjectTypeActions();

        $count_EventObjectTypeList = $this->generateEventObjectTypeList();
        $count_VacReqApprovalTypeList = $this->generateVacReqApprovalTypeList();
        $count_VacReqRequestTypeList = $this->generateVacReqRequestTypeList();
        $logger->notice("Finished generateVacReqRequestTypeList");
        $count_VacReqFloatingTextList = $this->generateVacReqFloatingTextList();
        $count_VacReqFloatingTypeList = $this->generateVacReqFloatingTypeList();
        //return "Finished generateVacReqFloatingTypeList";

        $adminRes = $this->generateAdministratorAction(); //testing this cause logout
        $logger->notice("Finished generate AdministratorAction");
        //return "Finished generateAdministratorAction";

        $count_HealthcareProviderSpecialtiesList = $this->generateHealthcareProviderSpecialtiesList();
        $count_HealthcareProviderCommunicationsList = $this->generateHealthcareProviderCommunicationsList();

        $count_setObjectTypeForAllLists = $this->setObjectTypeForAllLists();
        $logger->notice("Finished setObjectTypeForAllLists");
        //return "Finished setObjectTypeForAllLists";

        $count_BloodProductTransfused = $this->generateBloodProductTransfused();
        $count_TransfusionReactionType = $this->generateTransfusionReactionType();
        $count_BloodTypeList = $this->generateBloodTypeList();
        $count_AdditionalCommunicationList = $this->generateAdditionalCommunicationList();
        $count_TransfusionAntibodyScreenResultsList = $this->generateTransfusionAntibodyScreenResultsList();
        $count_TransfusionDATResultsList = $this->generateTransfusionDATResultsList();
        $count_TransfusionCrossmatchResultsList = $this->generateTransfusionCrossmatchResultsList();
        $count_TransfusionHemolysisCheckResultsList = $this->generateTransfusionHemolysisCheckResultsList();
        $count_ComplexPlateletSummaryAntibodiesList = $this->generateComplexPlateletSummaryAntibodiesList();
        $count_CCIUnitPlateletCountDefaultValueList = $this->generateCCIUnitPlateletCountDefaultValueList();
        $count_CCIPlateletTypeTransfusedList = $this->generateCCIPlateletTypeTransfusedList();
        $count_PlateletTransfusionProductReceivingList = $this->generatePlateletTransfusionProductReceivingList();
        $count_TransfusionProductStatusList = $this->generateTransfusionProductStatusList();
        $count_generateWeekDaysList = $this->generateWeekDaysList();
        $count_generateMonthsList = $this->generateMonthsList();
        $count_generateClericalErrorList = $this->generateClericalErrorList();
        $count_generateLabResultNames = $this->generateLabResultNames();
        $count_generateLabResultUnitsMeasureList = $this->generateLabResultUnitsMeasureList();
        $count_generateLabResultFlagList = $this->generateLabResultFlagList();
        $count_generatePathologyResultSignatoriesList = $this->generatePathologyResultSignatoriesList();
        $count_setFormNodeVersion = $this->setFormNodeVersion();
        $count_generateLifeForm = $this->generateLifeForm();
        $count_generateTransResProjectSpecialty = $this->generateTransResProjectSpecialty();
        $count_generateTransResPriceTypeList = $this->generateTransResPriceTypeList();
        $count_generateTransResProjectTypeList = $this->generateTransResProjectTypeList();
        $count_WorkQueueList = $this->generateWorkQueueList(); //after generateTransResProjectSpecialty()s
        $count_generateTransResRequestCategoryType = $this->generateTransResRequestCategoryType(); //after generateWorkQueueList
        $count_generateIrbApprovalTypeList = $this->generateIrbApprovalTypeList();
        $count_generateTissueProcessingServiceList = $this->generateTissueProcessingServiceList();
        $count_generateRestrictedServiceList = $this->generateRestrictedServiceList();
        //$count_generateCrnEntryTagsList = $this->generateCrnEntryTagsList();
        $count_BusinessPurposesList = $this->generateBusinessPurposes();
        $count_OrderableStatusList = $this->generateOrderableStatusList();
        $count_generateAntibodyCategoryTagList = $this->generateAntibodyCategoryTagList();
        $count_generateAntibodyLabList = $this->generateAntibodyLabList();
        $count_generateAntibodyPanelList = $this->generateAntibodyPanelList();
        $logger->notice("Finished AntibodyPanelList");
        //return "Finished generateBusinessPurposes";

        $count_generateCollLabList = $this->generateCollLabList();
        $count_generateCollDivList = $this->generateCollDivList();
        $count_generateIrbStatusList = $this->generateIrbStatusList();
        $count_generateRequesterGroupList = $this->generateRequesterGroupList();
        $count_generateCompCategoryList = $this->generateCompCategoryList();

        //Dashboards (7 lists)
        $count_generateDashboardRoles = $this->generateDashboardRoles();
        $count_generateChartsList = $this->generateChartsList();
        $count_generateChartFilterList = $this->generateChartFilterList();
        $count_generateChartDataSourceList = $this->generateChartDataSourceList();
        $count_generateChartUpdateFrequencyList = $this->generateChartUpdateFrequencyList();
        $count_generateChartVisualizationList = $this->generateChartVisualizationList();
        $count_generateChartTopicList = $this->generateChartTopicList(); //hierarchy
        $count_generateChartTypeList = $this->generateChartTypeList(); //hierarchy

        $count_generatePlatformListManagerList = $this->generatePlatformListManagerList(null,null);
        $logger->notice("Finished generatePlatformListManagerList");

        $count_populateClassUrl = $this->populateClassUrl();
        //$count_createAdminAntibodyList = $this->createAdminAntibodyList();
        $logger->notice("Finished populateClassUrl");

        $count_generateAuthUserGroupList = $this->generateAuthUserGroupList();
        $count_generateAuthServerNetworkList = $this->generateAuthServerNetworkList();
        $count_generateAuthPartnerServerList = $this->generateAuthPartnerServerList();
        //$count_generateHostedUserGroupList = $this->generateHostedUserGroupList();
        $count_generateTenantUrlList = $this->generateTenantUrlList();

        $count_generateTransferStatusList = $this->generateTransferStatusList();

        //exit('testing generateAll()');

        $msg =
            'Generated Tables: '.
            'Sitenames='.$count_sitenameList.', '.
            'Source Systems='.$count_sourcesystems.', '.
            'generateViewModeList='.$count_generateViewModeList.', '.
            'Roles='.$count_roles.', '.
            'Site Settings='.$count_siteParameters.', '.
            'generateDefaultOrgGroupSiteParameters='.$count_generateDefaultOrgGroupSiteParameters.', '.
            'Institution Types='.$count_institutiontypes.', '.
            'Organizational Group Types='.$count_OrganizationalGroupType.', '.
            'Institutions='.$count_institution.', '.
            'Auxiliary Institutions='.$count_auxinstitution.', '.
            'Appointment Title Positions='.$count_appTitlePositions.', '.
            //'Users='.$count_users.', '.
            'Test Users='.$count_testusers.', '.
            'Board Specialties='.$count_boardSpecialties.', '.
            'Employment Types='.$count_employmentTypes.', '.
            'Employment Types of Termination='.$count_terminationTypes.', '.
            'Event Log Types='.$count_eventTypeList.', '.
            'Username Types='.$count_usernameTypeList.', '.
            'Identifier Types='.$count_identifierTypeList.', '.
            'Residency Tracks='.$count_residencyTrackList.', '.
            'Fellowship Types='.$count_fellowshipTypeList.', '.
            'Medical Titles='.$count_medicalTitleList.', '.
            'Medical Specialties='.$count_medicalSpecialties.', '.
            'Equipment Types='.$count_equipmentType.', '.
            'Equipment='.$count_equipment.', '.
            'Location Types='.$count_locationTypeList.', '.
            'Location Privacy='.$count_locprivacy.', '.
            'States='.$count_states.', '.
            //'Countries='.$count_countryList.', '.
            'Languages='.$count_languages.', '.
            'Locales='.$count_locales.', '.
            'Locations='.$count_locations.', '.
            'Buildings='.$count_buildings.', '.
            'Reaserch Labs='.$count_reslabs.', '.
            'Completion Reasons='.$count_completionReasons.', '.
            'Training Degrees='.$count_trainingDegrees.', '.
            'Residency Specialties='.$count_residencySpecialties.', '.
            'Fellowship Subspecialties='.$count_fellowshipSubspecialties.', '.
            //'Major Trainings ='.$count_majorTrainings.', '.
            //'Minor Trainings ='.$count_minorTrainings.', '.
            'Honor Trainings='.$count_HonorTrainings.', '.
            'Fellowship Titles='.$count_FellowshipTitles.', '.
            'Document Types='.$count_documenttypes.', '.
            'Source Organizations='.$count_sourceOrganizations.', '.
            'Importances='.$count_generateImportances.', '.
            'AuthorshipRoles='.$count_generateAuthorshipRoles.', '.
            'LinkTypes='.$count_generateLinkTypes.', '.
            'Sex='.$count_sex.', '.
            'Position Types='.$count_PositionTypeList.', '.
            'Comment Group Types='.$count_CommentGroupType.', '.
            'Spot Purposes='.$count_SpotPurpose.', '.
            'Medical License Statuses='.$count_generateMedicalLicenseStatus.', '.
            'Certifying Board Organizations='.$count_generateCertifyingBoardOrganization.', '.
            'Training Types='.$count_TrainingTypeList.', '.
            'FellApp Statuses='.$count_FellAppStatus.', '.
            'FellApp Score='.$count_FellAppRank.', '.
            'FellAppVisaStatus='.$count_FellAppVisaStatus.', '.
            'FellApp Language Proficiency='.$count_LanguageProficiency.', '.

            'ResApp Statuses='.$count_ResAppStatus.', '.
            'ResApp Score='.$count_ResAppRank.', '.
            'ResAppVisaStatus='.$count_ResAppVisaStatus.', '.
            'PostSophList='.$count_PostSophList.', '.
            'count_ResAppFitForProgram='.$count_ResAppFitForProgram.', '.
            'ResAppLanguageProficiency='.$count_ResAppLanguageProficiency.', '.

            'ResAppApplyingResidencyTrack='.$count_ResAppApplyingResidencyTrack.', '.
            'ResAppLearnAreaList='.$count_ResAppLearnAreaList.', '.
            'ResAppSpecificIndividualList='.$count_ResAppSpecificIndividualList.', '.

            'Permissions ='.$count_Permissions.', '.
            'PermissionObjects ='.$count_PermissionObjects.', '.
            'PermissionActions ='.$count_PermissionActions.', '.
            'ObjectTypeActions='.$count_ObjectTypeActions.', '.
            'setObjectTypeForAllLists='.$count_setObjectTypeForAllLists.', '.
            'Collaboration Types='.$collaborationtypes.', '.
            'EventObjectTypeList count='.$count_EventObjectTypeList.', '.
            'VacReqApprovalTypeList count='.$count_VacReqApprovalTypeList.', '.
            'VacReqRequestTypeList count='.$count_VacReqRequestTypeList.', '.
            'VacReqFloatingTypeList count='.$count_VacReqFloatingTypeList.', '.
            'VacReqFloatingTextList count='.$count_VacReqFloatingTextList.', '.
            'Administrator generation='.$adminRes.', '.
            'HealthcareProviderSpecialtiesList='.$count_HealthcareProviderSpecialtiesList.', '.
            'HealthcareProviderCommunicationsList='.$count_HealthcareProviderCommunicationsList.', '.
            'BloodProductTransfused='.$count_BloodProductTransfused.', '.
            'TransfusionReactionType='.$count_TransfusionReactionType.', '.
            'BloodTypeList='.$count_BloodTypeList.', '.
            'AdditionalCommunicationList='.$count_AdditionalCommunicationList.', '.
            'TransfusionAntibodyScreenResultsList='.$count_TransfusionAntibodyScreenResultsList.', '.
            'TransfusionDATResultsList='.$count_TransfusionDATResultsList.', '.
            'TransfusionCrossmatchResultsList='.$count_TransfusionCrossmatchResultsList.', '.
            'TransfusionHemolysisCheckResultsList='.$count_TransfusionHemolysisCheckResultsList.', '.
            'ComplexPlateletSummaryAntibodiesList='.$count_ComplexPlateletSummaryAntibodiesList.', '.
            'CCIUnitPlateletCountDefaultValueList='.$count_CCIUnitPlateletCountDefaultValueList.', '.
            'CCIPlateletTypeTransfusedList='.$count_CCIPlateletTypeTransfusedList.', '.
            'PlateletTransfusionProductReceivingList='.$count_PlateletTransfusionProductReceivingList.', '.
            'TransfusionProductStatusList='.$count_TransfusionProductStatusList.', '.
            'WeekDaysList='.$count_generateWeekDaysList.', '.
            'MonthsList='.$count_generateMonthsList.', '.
            'ClericalErrorList='.$count_generateClericalErrorList.', '.
            'LabResultNames='.$count_generateLabResultNames.', '.
            'LabResultUnitsMeasures='.$count_generateLabResultUnitsMeasureList.', '.
            'LabResultFlagList='.$count_generateLabResultFlagList.', '.
            'PathologyResultSignatoriesList='.$count_generatePathologyResultSignatoriesList.', '.
            'FormNodeVersion='.$count_setFormNodeVersion.', '.
            'LifeForms='.$count_generateLifeForm.', '.
            'TransResProjectSpecialty='.$count_generateTransResProjectSpecialty.', '.
            'ProjectTypeList='.$count_generateTransResProjectTypeList.', '.
            'generateTransResPriceTypeList='.$count_generateTransResPriceTypeList.', '.
            'TransResRequestCategoryType='.$count_generateTransResRequestCategoryType.', '.
            'PlatformListManagerList='.$count_generatePlatformListManagerList.', '.
            'IrbApprovalTypeList='.$count_generateIrbApprovalTypeList.', '.
            'TissueProcessingServiceList='.$count_generateTissueProcessingServiceList.', '.
            'RestrictedServiceList='.$count_generateRestrictedServiceList.', '.
            'populateClassUrl='.$count_populateClassUrl.', '.
            //'CrnEntryTagsList='.$count_generateCrnEntryTagsList.', '.
            'businessPurposesList='.$count_BusinessPurposesList.', '.
            'WorkQueueList='.$count_WorkQueueList.', '.
            'OrderableStatusList='.$count_OrderableStatusList.', '.
            //'createAdminAntibodyList='.$count_createAdminAntibodyList.', '.

            'CollLabList='.$count_generateCollLabList.', '.
            'CollDivList='.$count_generateCollDivList.', '.
            'IrbStatusList='.$count_generateIrbStatusList.', '.
            'RequesterGroupList='.$count_generateRequesterGroupList.', '.
            'CompCategoryList='.$count_generateCompCategoryList.', '.

            'generateDashboardRoles='.$count_generateDashboardRoles.', '.
            'generateChartTypeList='.$count_generateChartTypeList.', '.
            'generateChartFilterList='.$count_generateChartFilterList.', '.
            'generateChartTopicList='.$count_generateChartTopicList.', '.
            'generateChartDataSourceList='.$count_generateChartDataSourceList.', '.
            'generateChartUpdateFrequencyList='.$count_generateChartUpdateFrequencyList.', '.
            'generateChartVisualizationList='.$count_generateChartVisualizationList.', '.
            'generateChartsList='.$count_generateChartsList.', '.

            'generateAuthUserGroupList='.$count_generateAuthUserGroupList.', '.
            'generateAuthServerNetworkList='.$count_generateAuthServerNetworkList.', '.
            'generateAuthPartnerServerList='.$count_generateAuthPartnerServerList.', '.
            //'generateHostedUserGroupList='.$count_generateHostedUserGroupList.', '.
            'generateTenantUrlList='.$count_generateTenantUrlList.', '.
            'generateAntibodyCategoryTagList='.$count_generateAntibodyCategoryTagList.', '.
            'generateAntibodyLabList='.$count_generateAntibodyLabList.', '.
            'generateAntibodyPanelList='.$count_generateAntibodyPanelList.', '.

            'generateTransferStatusList='.$count_generateTransferStatusList.', '.


            ' (Note: -1 means that this table is already exists)';

        $logger->notice("Finished generateAll");

//        if( $this->isWindows() ) {
//            //$emailUtil = $this->container->get('user_mailer_utility');
//            //$emailUtil->createEmailCronJob();
//            //$logger->notice("Created email cron job");
//        } else {
//            $userServiceUtil = $this->container->get('user_service_utility');
//            $userServiceUtil->createCronsLinux();
//        }
        //$userServiceUtil = $this->container->get('user_service_utility');
        //$userServiceUtil->createCrons();

        return $msg;
    }


    #[Route(path: '/populate-residency-specialties-with-default-values', name: 'generate_residencyspecialties', methods: ['GET'])]
    public function generateResidencySpecialtiesAction(Request $request)
    {

        $count = $this->generateResidencySpecialties();
        if( $count >= 0 ) {

            $this->addFlash(
                'notice',
                'Created '.$count. ' Residency Specialties'
            );

            return $this->redirect($this->generateUrl('employees_siteparameters'));

        } else {

            $this->addFlash(
                'notice',
                'This table is already exists!'
            );

            return $this->redirect($this->generateUrl('employees_siteparameters'));
        }

    }


    #[Route(path: '/populate-country-city-list-with-default-values', name: 'generate_country_city', methods: ['GET'])]
    public function generateProcedureAction(Request $request)
    {

        $max_exec_time = ini_get('max_execution_time');
        ini_set('max_execution_time', 900); //900 seconds = 15 minutes

        $count = $this->generateCountryList();

        $countryCount = $count['country'];
        $cityCount = $count['city'];

        $this->addFlash(
            'notice',
            'Added '.$countryCount.' countries and '.$cityCount.' cities'
        );

        ini_set('max_execution_time', $max_exec_time); //set back to the original value

        return $this->redirect($this->generateUrl('employees_siteparameters'));
    }



//////////////////////////////////////////////////////////////////////////////

    public function setDefaultList( $entity, $count, $user, $name=null ) {
        //$userutil = new UserUtil();
        //return $userutil->setDefaultList( $entity, $count, $user, $name );

        $userSecUtil = $this->container->get('user_security_utility');
        $entity = $userSecUtil->setDefaultList( $entity, $count, $user, $name );
        $entity->setType('default');
        return $entity;
    }

   
    //Generate or Update roles
    public function generateRoles(Request $request) {

        $em = $this->getDoctrine()->getManager();

        //generate role can update the role too
//        $entities = $em->getRepository(Roles::class)->findAll();
//        if( $entities ) {
//            //return -1;
//        }

        //Note: fos user has role ROLE_SCANORDER_SUPER_ADMIN

        $types = array(

            //////////// general roles are set by security.yml only ////////////

            //general super admin role for all sites
            "ROLE_PLATFORM_ADMIN" => array(
                "Platform Administrator",
                "Full access for all sites",
                100
            ),
            "ROLE_PLATFORM_DEPUTY_ADMIN" => array(
                "Deputy Platform Administrator",
                'The same as "Platform Administrator" role can do except assign or remove "Platform Administrator" or "Deputy Platform Administrator" roles',
                100
            ),
            //"ROLE_BANNED" => "Banned user for all sites",                 //general super admin role for all sites
            //"ROLE_UNAPPROVED" => "Unapproved User",                       //general unapproved user

            "ROLE_SUPER_ADMIN" => array(
                "Super User",
                "Access to the tenant manager",
                //"Access to the tenant manager and homepage manager",
                100
            ),
            "ROLE_SUPER_DEPUTY_ADMIN" => array(
                "Deputy Super User",
                'The same as "Super User" role',
                100
            ),

            //ROLE_TESTER
            "ROLE_TESTER" => array(
                "Tester",
                "Allow using testing server without redirection",
                1
            ),

            //////////// Scanorder roles ////////////
            "ROLE_SCANORDER_ADMIN" => array(
                "ScanOrder Administrator",
                "Full access for Scan Order site",
                90
            ),
            "ROLE_SCANORDER_PROCESSOR" => array(
                "ScanOrder Processor",
                "Allow to view all orders and change scan order status",
                50
            ),

            "ROLE_SCANORDER_DATA_QUALITY_ASSURANCE_SPECIALIST" => array(
                "ScanOrder Data Quality Assurance Specialist",
                "Allow to make data quality modification",
                50
            ),

            "ROLE_SCANORDER_DIVISION_CHIEF" => array(
                "ScanOrder Division Chief",
                "Allow to view and amend all orders for this division(institution)",
                40
            ),  //view or modify all orders of the same division(institution)
            "ROLE_SCANORDER_SERVICE_CHIEF" => array(
                "ScanOrder Service Chief",
                "Allow to view and amend all orders for this service",
                30
            ),


            //"ROLE_USER" => "User", //this role must be always assigned to the authenticated user. Required by fos user bundle.

            "ROLE_SCANORDER_SUBMITTER" => array(
                "ScanOrder Submitter",
                "Allow submit new orders, amend own order",
                10
            ),
            "ROLE_SCANORDER_ORDERING_PROVIDER" => array(
                "ScanOrder Ordering Provider",
                "Allow submit new orders, amend own order",
                10
            ),

            "ROLE_SCANORDER_PATHOLOGY_FELLOW" => array(
                "ScanOrder Pathology Fellow",
                "",
                10
            ),
            "ROLE_SCANORDER_PATHOLOGY_FACULTY" => array(
                "ScanOrder Pathology Faculty",
                "",
                10
            ),

            "ROLE_SCANORDER_COURSE_DIRECTOR" => array(
                "ScanOrder Course Director",
                "Allow to be a Course Director in Educational orders",
                10
            ),
            "ROLE_SCANORDER_PRINCIPAL_INVESTIGATOR" => array(
                "ScanOrder Principal Investigator",
                "Allow to be a Principal Investigator in Research orders",
                10
            ),

            "ROLE_SCANORDER_ONCALL_TRAINEE" => array(
                "OrderPlatform On Call Trainee",
                "Allow to see the phone numbers & email of Home location",
                10
            ),
            "ROLE_SCANORDER_ONCALL_ATTENDING" => array(
                "OrderPlatform On Call Attending",
                "Allow to see the phone numbers & email of Home location",
                10
            ),

            "ROLE_PLATFORM_DEMO" => array(
                "Platform Demo",
                "The same as ROLE_PLATFORM_DEPUTY_ADMIN but names are replaced by Demo Applicant for Fellowship Application Site",
                5
            ),

            "ROLE_SCANORDER_UNAPPROVED" => array(
                "ScanOrder Unapproved User",
                "Does not allow to visit Scan Order site",
                0
            ),
            "ROLE_SCANORDER_BANNED" => array(
                "ScanOrder Banned User",
                "Does not allow to visit Scan Order site",
                -1
            ),


            //////////// EmployeeDirectory roles ////////////
            "ROLE_USERDIRECTORY_ADMIN" => array(
                "EmployeeDirectory Administrator",
                "Full access for Employee Directory site",
                90
            ),
            "ROLE_USERDIRECTORY_EDITOR" => array(
                "EmployeeDirectory Editor",
                "Allow to edit all employees; Can not change roles for users, but can grant access via access requests",
                50
            ),
            "ROLE_USERDIRECTORY_SIMPLEVIEW" => array(
                "EmployeeDirectory Observer with a Simplified View",
                "Allow to view all employees with a simplified view",
                10
            ),
            "ROLE_USERDIRECTORY_OBSERVER" => array(
                "EmployeeDirectory Observer",
                "Allow to view all employees",
                10
            ),
            "ROLE_USERDIRECTORY_BANNED" => array(
                "EmployeeDirectory Banned User",
                "Does not allow to visit Employee Directory site",
                -1
            ),
            "ROLE_USERDIRECTORY_UNAPPROVED" => array(
                "EmployeeDirectory Unapproved User",
                "Does not allow to visit Employee Directory site",
                0
            ),


            //////////// FellApp roles ////////////
            "ROLE_FELLAPP_ADMIN" => array(
                "Fellowship Applications Administrator",
                "Full access for Fellowship Applications site",
                90
            ),
            //Directors (7 types)
//            "ROLE_FELLAPP_DIRECTOR" => array(
//                "Fellowship Program General Director Role",
//                "Access to Fellowship Application type as Director (edit application,upload new documents)",
//                50
//            ),
            "ROLE_FELLAPP_DIRECTOR_WCM_BREASTPATHOLOGY" => array(
                "Fellowship Program Director WCM Breast Pathology",
                "Access to specific Fellowship Application type as Director",
                50
            ),
            "ROLE_FELLAPP_DIRECTOR_WCM_CYTOPATHOLOGY" => array(
                "Fellowship Program Director WCM Cytopathology",
                "Access to specific Fellowship Application type as Director",
                50
            ),
            "ROLE_FELLAPP_DIRECTOR_WCM_GYNECOLOGICPATHOLOGY" => array(
                "Fellowship Program Director WCM Gynecologic Pathology",
                "Access to specific Fellowship Application type as Director",
                50
            ),
            "ROLE_FELLAPP_DIRECTOR_WCM_GASTROINTESTINALPATHOLOGY" => array(
                "Fellowship Program Director WCM Gastrointestinal Pathology",
                "Access to specific Fellowship Application type as Director",
                50
            ),
            "ROLE_FELLAPP_DIRECTOR_WCM_GENITOURINARYPATHOLOGY" => array(
                "Fellowship Program Director WCM Genitourinary Pathology",
                "Access to specific Fellowship Application type as Director",
                50
            ),
            "ROLE_FELLAPP_DIRECTOR_WCM_HEMATOPATHOLOGY" => array(
                "Fellowship Program Director WCM Hematopathology",
                "Access to specific Fellowship Application type as Director",
                50
            ),
            "ROLE_FELLAPP_DIRECTOR_WCM_MOLECULARGENETICPATHOLOGY" => array(
                "Fellowship Program Director WCM Molecular Genetic Pathology",
                "Access to specific Fellowship Application type as Director",
                50
            ),
            //Program-Coordinator (7 types)
//            "ROLE_FELLAPP_COORDINATOR" => array(
//                "Fellowship Program General Coordinator Role",
//                "Access to Fellowship Application type as Coordinator (edit application,upload new documents)",
//                40
//            ),
            "ROLE_FELLAPP_COORDINATOR_WCM_BREASTPATHOLOGY" => array(
                "Fellowship Program Coordinator WCM Breast Pathology",
                "Access to specific Fellowship Application type as Coordinator",
                40
            ),
            "ROLE_FELLAPP_COORDINATOR_WCM_CYTOPATHOLOGY" => array(
                "Fellowship Program Coordinator WCM Cytopathology",
                "Access to specific Fellowship Application type as Coordinator",
                40
            ),
            "ROLE_FELLAPP_COORDINATOR_WCM_GYNECOLOGICPATHOLOGY" => array(
                "Fellowship Program Coordinator WCM Gynecologic Pathology",
                "Access to specific Fellowship Application type as Coordinator",
                40
            ),
            "ROLE_FELLAPP_COORDINATOR_WCM_GASTROINTESTINALPATHOLOGY" => array(
                "Fellowship Program Coordinator WCM Gastrointestinal Pathology",
                "Access to specific Fellowship Application type as Coordinator",
                40
            ),
            "ROLE_FELLAPP_COORDINATOR_WCM_GENITOURINARYPATHOLOGY" => array(
                "Fellowship Program Coordinator WCM Genitourinary Pathology",
                "Access to specific Fellowship Application type as Coordinator",
                40
            ),
            "ROLE_FELLAPP_COORDINATOR_WCM_HEMATOPATHOLOGY" => array(
                "Fellowship Program Coordinator WCM Hematopathology",
                "Access to specific Fellowship Application type as Coordinator",
                40
            ),
            "ROLE_FELLAPP_COORDINATOR_WCM_MOLECULARGENETICPATHOLOGY" => array(
                "Fellowship Program Coordinator WCM Molecular Genetic Pathology",
                "Access to specific Fellowship Application type as Coordinator",
                40
            ),
            //Fellowship Interviewer
//            "ROLE_FELLAPP_INTERVIEWER" => array(
//                "Fellowship Program General Interviewer Role",
//                "Access to Fellowship Application type as Interviewer (able to view, create and update the interview form)",
//                30
//            ),
            "ROLE_FELLAPP_INTERVIEWER_WCM_BREASTPATHOLOGY" => array(
                "Fellowship Program Interviewer WCM Breast Pathology",
                "Access to specific Fellowship Application type as Interviewer",
                30
            ),
            "ROLE_FELLAPP_INTERVIEWER_WCM_CYTOPATHOLOGY" => array(
                "Fellowship Program Interviewer WCM Cytopathology",
                "Access to specific Fellowship Application type as Interviewer",
                30
            ),
            "ROLE_FELLAPP_INTERVIEWER_WCM_GYNECOLOGICPATHOLOGY" => array(
                "Fellowship Program Interviewer WCM Gynecologic Pathology",
                "Access to specific Fellowship Application type as Interviewer",
                30
            ),
            "ROLE_FELLAPP_INTERVIEWER_WCM_GASTROINTESTINALPATHOLOGY" => array(
                "Fellowship Program Interviewer WCM Gastrointestinal Pathology",
                "Access to specific Fellowship Application type as Interviewer",
                30
            ),
            "ROLE_FELLAPP_INTERVIEWER_WCM_GENITOURINARYPATHOLOGY" => array(
                "Fellowship Program Interviewer WCM Genitourinary Pathology",
                "Access to specific Fellowship Application type as Interviewer",
                30
            ),
            "ROLE_FELLAPP_INTERVIEWER_WCM_HEMATOPATHOLOGY" => array(
                "Fellowship Program Interviewer WCM Hematopathology",
                "Access to specific Fellowship Application type as Interviewer",
                30
            ),
            "ROLE_FELLAPP_INTERVIEWER_WCM_MOLECULARGENETICPATHOLOGY" => array(
                "Fellowship Program Interviewer WCM Molecular Genetic Pathology",
                "Access to specific Fellowship Application type as Interviewer",
                30
            ),

            //Fellowship Observer
            "ROLE_FELLAPP_OBSERVER" => array(
                "Fellowship Program Observer",
                "Access to Fellowship Application as Observer (able to view a particular (assigned) application)",
                10
            ),

//            "ROLE_FELLAPP_USER" => array(
//                "Fellowship Applications User",
//                "Allow to view the Fellowship Applications site",
//                5
//            ),
            "ROLE_FELLAPP_BANNED" => array(
                "Fellowship Applications Banned User",
                "Does not allow to visit Fellowship Applications site",
                -1
            ),
            "ROLE_FELLAPP_UNAPPROVED" => array(
                "Fellowship Applications Unapproved User",
                "Does not allow to visit Fellowship Applications site",
                0
            ),



            //////////// Residency roles ////////////
            "ROLE_RESAPP_ADMIN" => array(
                "Residency Applications Administrator",
                "Full access for Residency Applications site",
                90
            ),
            //Directors (3 types)
            "ROLE_RESAPP_DIRECTOR_WCM_AP" => array(
                "Residency Program Director WCM AP",
                "Access to specific Residency Application type as Director",
                50
            ),
            "ROLE_RESAPP_DIRECTOR_WCM_CP" => array(
                "Residency Program Director WCM CP",
                "Access to specific Residency Application type as Director",
                50
            ),
            "ROLE_RESAPP_DIRECTOR_WCM_APCP" => array(
                "Residency Program Director WCM AP/CP",
                "Access to specific Residency Application type as Director",
                50
            ),
            "ROLE_RESAPP_DIRECTOR_WCM_APEXP" => array(
                "Residency Program Director WCM AP/EXP",
                "Access to specific Residency Application type as Director",
                50
            ),
            "ROLE_RESAPP_DIRECTOR_WCM_CPEXP" => array(
                "Residency Program Director WCM CP/EXP",
                "Access to specific Residency Application type as Director",
                50
            ),
            //Program-Coordinator (3 types)
            "ROLE_RESAPP_COORDINATOR_WCM_AP" => array(
                "Residency Program Coordinator WCM AP",
                "Access to specific Residency Application type as Coordinator",
                40
            ),
            "ROLE_RESAPP_COORDINATOR_WCM_CP" => array(
                "Residency Program Coordinator WCM CP",
                "Access to specific Residency Application type as Coordinator",
                40
            ),
            "ROLE_RESAPP_COORDINATOR_WCM_APCP" => array(
                "Residency Program Coordinator WCM AP/CP",
                "Access to specific Residency Application type as Coordinator",
                40
            ),
            "ROLE_RESAPP_COORDINATOR_WCM_APEXP" => array(
                "Residency Program Coordinator WCM AP/EXP",
                "Access to specific Residency Application type as Coordinator",
                40
            ),
            "ROLE_RESAPP_COORDINATOR_WCM_CPEXP" => array(
                "Residency Program Coordinator WCM CP/EXP",
                "Access to specific Residency Application type as Coordinator",
                40
            ),
            
            //Residency Interviewer
            "ROLE_RESAPP_INTERVIEWER_WCM_AP" => array(
                "Residency Program Interviewer WCM AP",
                "Access to specific Residency Application type as Interviewer",
                30
            ),
            "ROLE_RESAPP_INTERVIEWER_WCM_CP" => array(
                "Residency Program Interviewer WCM CP",
                "Access to specific Residency Application type as Interviewer",
                30
            ),
            "ROLE_RESAPP_INTERVIEWER_WCM_APCP" => array(
                "Residency Program Interviewer WCM AP/CP",
                "Access to specific Residency Application type as Interviewer",
                30
            ),
            "ROLE_RESAPP_INTERVIEWER_WCM_APEXP" => array(
                "Residency Program Interviewer WCM AP/EXP",
                "Access to specific Residency Application type as Interviewer",
                30
            ),
            "ROLE_RESAPP_INTERVIEWER_WCM_CPEXP" => array(
                "Residency Program Interviewer WCM CP/EXP",
                "Access to specific Residency Application type as Interviewer",
                30
            ),

            //Residency Observer
            "ROLE_RESAPP_OBSERVER" => array(
                "Residency Program Observer",
                "Access to Residency Application as Observer (able to view a particular (assigned) application)",
                10
            ),

            "ROLE_RESAPP_BANNED" => array(
                "Residency Applications Banned User",
                "Does not allow to visit Residency Applications site",
                -1
            ),
            "ROLE_RESAPP_UNAPPROVED" => array(
                "Residency Applications Unapproved User",
                "Does not allow to visit Residency Applications site",
                0
            ),
            ///////////// EOF Residency roles ///////////////////



            //////////// Deidentifier roles ////////////
            "ROLE_DEIDENTIFICATOR_ADMIN" => array(
                "Deidentifier Administrator",
                "Full access for Deidentifier site",
                90
            ),

            "ROLE_DEIDENTIFICATOR_WCM_NYP_HONEST_BROKER" => array(
                "WCM-NYP Deidentifier Honest Broker",
                "Can search and generate",
                50
            ),
            "ROLE_DEIDENTIFICATOR_WCM_NYP_ENQUIRER" => array(
                "WCM-NYP Deidentifier Enquirer",
                "Can search, but not generate",
                40
            ),
            "ROLE_DEIDENTIFICATOR_WCM_NYP_GENERATOR" => array(
                "WCM-NYP Deidentifier Generator",
                "Can generate, but not search",
                10
            ),

            "ROLE_DEIDENTIFICATOR_BANNED" => array(
                "Deidentifier Banned User",
                "Does not allow to visit Deidentifier site",
                -1
            ),
            "ROLE_DEIDENTIFICATOR_UNAPPROVED" => array(
                "Deidentifier Unapproved User",
                "Does not allow to visit Deidentifier site",
                0
            ),


            //////////// VACREQ roles ////////////
            "ROLE_VACREQ_ADMIN" => array(
                "Vacation Request Administrator",
                "Full access for Vacation Request site",
                90
            ),

//            "ROLE_VACREQ_APPROVER" => array(
//                "Vacation Request Approver",
//                "Can search and approve vacation requests",
//                50
//            ),
            "ROLE_VACREQ_APPROVER_EXECUTIVE" => array(
                "Vacation Request Approver for the Executive Committee",
                "Can search and approve vacation requests",
                50
            ),
            "ROLE_VACREQ_APPROVER_CLINICALPATHOLOGY" => array(
                "Vacation Request Approver for the Clinical Pathology",
                "Can search and approve vacation requests for specified service",
                50
            ),
            "ROLE_VACREQ_APPROVER_EXPERIMENTALPATHOLOGY" => array(
                "Vacation Request Approver for the Cell and Cancer Pathobiology",
                "Can search and approve vacation requests for specified service",
                50
            ),
            "ROLE_VACREQ_APPROVER_VASCULARBIOLOGY" => array(
                "Vacation Request Approver for the Vascular Biology",
                "Can search and approve vacation requests for specified service",
                50
            ),
            "ROLE_VACREQ_APPROVER_HEMATOPATHOLOGY" => array(
                "Vacation Request Approver for the Hematopathology",
                "Can search and approve vacation requests for specified service",
                50
            ),
            "ROLE_VACREQ_APPROVER_SURGICALPATHOLOGY" => array(
                "Vacation Request Approver for the Surgical Pathology (Anatomic Pathology)",
                "Can search and approve vacation requests for specified service",
                50
            ),
            "ROLE_VACREQ_APPROVER_CYTOPATHOLOGY" => array(
                "Vacation Request Approver for the Cytopathology",
                "Can search and approve vacation requests for specified service",
                50
            ),
            "ROLE_VACREQ_APPROVER_DERMATOPATHOLOGY" => array(
                "Vacation Request Approver for the Dermatopathology",
                "Can search and approve vacation requests for specified service",
                50
            ),

            "ROLE_VACREQ_SUPERVISOR_WCM_PATHOLOGY" => array(
                "Vacation Request Supervisor - WCM Pathology Department",
                "Can search and approve carry over requests for Department of Pathology and Laboratory Medicine(WCM)",
                40
            ),

            "ROLE_VACREQ_OBSERVER_WCM_PATHOLOGY" => array(
                "Vacation Request Observer for WCM Department of Pathology and Laboratory Medicine",
                "This role should allow the user to log into the Vacation Request site and,
                if this is the only role the user has on the Vacation Request Site,
                be instantly redirected to the Away Calendar page.
                No access should be provided to the Homepage, Incoming Requests, Group Management, My Group, etc.",
                40
            ),

//            "ROLE_VACREQ_SUBMITTER" => array(
//                "Vacation Request Submitter",
//                "Can submit a vacation request",
//                30
//            ),
            "ROLE_VACREQ_SUBMITTER_EXECUTIVE" => array(
                "Vacation Request Submitter for the Executive Committee",
                "Can search and create vacation requests",
                30
            ),
            "ROLE_VACREQ_SUBMITTER_CLINICALPATHOLOGY" => array(
                "Vacation Request Submitter for the Clinical Pathology",
                "Can search and create vacation requests for specified service",
                30
            ),
            "ROLE_VACREQ_SUBMITTER_EXPERIMENTALPATHOLOGY" => array(
                "Vacation Request Submitter for the Cell and Cancer Pathobiology",
                "Can search and create vacation requests for specified service",
                30
            ),
            "ROLE_VACREQ_SUBMITTER_VASCULARBIOLOGY" => array(
                "Vacation Request Submitter for the Vascular Biology",
                "Can search and create vacation requests for specified service",
                30
            ),
            "ROLE_VACREQ_SUBMITTER_HEMATOPATHOLOGY" => array(
                "Vacation Request Submitter for the Hematopathology",
                "Can search and create vacation requests for specified service",
                30
            ),
            "ROLE_VACREQ_SUBMITTER_SURGICALPATHOLOGY" => array(
                "Vacation Request Submitter for the Surgical Pathology (Anatomic Pathology)",
                "Can search and create vacation requests for specified service",
                30
            ),
            "ROLE_VACREQ_SUBMITTER_CYTOPATHOLOGY" => array(
                "Vacation Request Submitter for the Cytopathology",
                "Can search and create vacation requests for specified service",
                30
            ),
            "ROLE_VACREQ_SUBMITTER_DERMATOPATHOLOGY" => array(
                "Vacation Request Submitter for the Dermatopathology",
                "Can search and create vacation requests for specified service",
                30
            ),

            "ROLE_VACREQ_BANNED" => array(
                "Vacation Request Banned User",
                "Does not allow to visit Vacation Request site",
                -1
            ),
            "ROLE_VACREQ_UNAPPROVED" => array(
                "Vacation Request Unapproved User",
                "Does not allow to visit Vacation Request site",
                0
            ),

            //////////// CALLLOG roles ////////////
            "ROLE_CALLLOG_ADMIN" => array(
                "Call Log Book Administrator",
                "Full access for Call Logbook site",
                90,
                "call-log-book"
            ),

            "ROLE_CALLLOG_DATA_QUALITY" => array(
                "Data Quality Manager for WCM-NYP",
                "Merge or un-merge patient records",
                60,
                "call-log-book"
            ),

            "ROLE_CALLLOG_PATHOLOGY_RESIDENT" => array(
                "Pathology Resident",
                "",
                50,
                "call-log-book"
            ),
            "ROLE_CALLLOG_PATHOLOGY_FELLOW" => array(
                "Pathology Fellow",
                "",
                50,
                "call-log-book"
            ),
            "ROLE_CALLLOG_PATHOLOGY_ATTENDING" => array(
                "Pathology Attending",
                "",
                50,
                "call-log-book"
            ),

            "ROLE_CALLLOG_USER" => array(
                "Call Log Book User",
                "Can create, edit and read call book entries",
                30,
                "call-log-book"
            ),


            //////////// CRN roles ////////////
            "ROLE_CRN_ADMIN" => array(
                "CRN Administrator",
                "Full access for Call Logbook site",
                90,
                "critical-result-notifications"
            ),

            "ROLE_CRN_DATA_QUALITY" => array(
                "CRN Data Quality Manager for WCM-NYP",
                "Merge or un-merge patient records",
                60,
                "critical-result-notifications"
            ),

            "ROLE_CRN_RECIPIENT" => array(
                "Critical Result Notification Recipient",
                "Receive text notifications on mobile phone",
                40,
                "critical-result-notifications"
            ),

            "ROLE_CRN_PATHOLOGY_RESIDENT" => array(
                "CRN Pathology Resident",
                "",
                50,
                "critical-result-notifications"
            ),
            "ROLE_CRN_PATHOLOGY_FELLOW" => array(
                "CRN Pathology Fellow",
                "",
                50,
                "critical-result-notifications"
            ),
            "ROLE_CRN_PATHOLOGY_ATTENDING" => array(
                "CRN Pathology Attending",
                "",
                50,
                "critical-result-notifications"
            ),
            "ROLE_CRN_USER" => array(
                "CRN User",
                "Can create, edit and read call book entries",
                30,
                "critical-result-notifications"
            ),

            "ROLE_CRN_PATHOLOGY_DERMATOPATHOLOGY_PRACTICE_SUPERVISOR" => array(
                "CRN Dermatopathology practice supervisor",
                "Full access for Call Logbook site",
                70,
                "critical-result-notifications"
            ),
            "ROLE_CRN_PATHOLOGY_DERMATOPAHOLOGY_ADMINISTRATIVE_ASSISTANT" => array(
                "CRN Dermatopathology administrative assistant",
                "Full access for Call Logbook site",
                70,
                "critical-result-notifications"
            ),
            "ROLE_CRN_PATHOLOGY_PRACTICE_SUPERVISOR" => array(
                "CRN Pathology supervisor",
                "Full access for Call Logbook site",
                70,
                "critical-result-notifications"
            ),
            "ROLE_CRN_PATHOLOGY_ADMINISTRATIVE_ASSISTANT" => array(
                "CRN Pathology administrative assistant",
                "Full access for Call Logbook site",
                70,
                "critical-result-notifications"
            ),

            //////////// EOF CRN roles ////////////

            //TRANSRES - similar to ROLE_FELLAPP_INTERVIEWER_WCM_HEMATOPATHOLOGY - _WCM_HEMEPATH and _WCM_APCP
            "ROLE_TRANSRES_ADMIN_APCP" => array(
                "Translational Research AP/CP Admin",
                "Full Access for Translational Research AP/CP site",
                90,
                "translational-research"
            ),
            "ROLE_TRANSRES_ADMIN_HEMATOPATHOLOGY" => array(
                "Translational Research Hematopathology Admin",
                "Full Access for Translational Research Hematopathology site",
                90,
                "translational-research"
            ),
            "ROLE_TRANSRES_ADMIN_COVID19" => array(
                "Translational Research COVID-19 Admin",
                "Full Access for Translational Research COVID-19 site",
                90,
                "translational-research"
            ),
            "ROLE_TRANSRES_ADMIN_MISI" => array(
                "Translational Research MISI Admin",
                "Full Access for Translational Research MISI site",
                90,
                "translational-research"
            ),

            "ROLE_TRANSRES_PROJECT_REACTIVATION_APPROVER" => array(
                "Translational Research Project Reactivation Approver",
                "Project reactivation approver (change status of the closed project)",
                90,
                "translational-research"
            ),

            "ROLE_TRANSRES_PRIMARY_REVIEWER_APCP" => array(
                "Translational Research AP/CP Final Reviewer",
                "Review for all states for AP/CP",
                80,
                "translational-research"
            ),
            "ROLE_TRANSRES_PRIMARY_REVIEWER_HEMATOPATHOLOGY" => array(
                "Translational Research Hematopathology Final Reviewer",
                "Review for all states for Hematopathology",
                80,
                "translational-research"
            ),
            "ROLE_TRANSRES_PRIMARY_REVIEWER_COVID19" => array(
                "Translational Research COVID-19 Final Reviewer",
                "Review for all states for COVID-19",
                80,
                "translational-research"
            ),
            "ROLE_TRANSRES_PRIMARY_REVIEWER_MISI" => array(
                "Translational Research MISI Final Reviewer",
                "Review for all states for MISI",
                80,
                "translational-research"
            ),

            "ROLE_TRANSRES_EXECUTIVE_APCP" => array(
                "Translational Research AP/CP Executive Committee",
                "Full View Access for AP/CP Translational Research site",
                70,
                "translational-research"
            ),
            "ROLE_TRANSRES_EXECUTIVE_HEMATOPATHOLOGY" => array(
                "Translational Research Hematopathology Executive Committee",
                "Full View Access for Hematopathology Translational Research site",
                70,
                "translational-research"
            ),
            "ROLE_TRANSRES_EXECUTIVE_COVID19" => array(
                "Translational Research COVID-19 Executive Committee",
                "Full View Access for COVID-19 Translational Research site",
                70,
                "translational-research"
            ),
            "ROLE_TRANSRES_EXECUTIVE_MISI" => array(
                "Translational Research MISI Executive Committee",
                "Full View Access for MISI Translational Research site",
                70,
                "translational-research"
            ),

            "ROLE_TRANSRES_IRB_REVIEWER_APCP" => array(
                "Translational Research AP/CP IRB Reviewer",
                "AP/CP IRB Review",
                50,
                "translational-research"
            ),
            "ROLE_TRANSRES_IRB_REVIEWER_HEMATOPATHOLOGY" => array(
                "Translational Research Hematopathology IRB Reviewer",
                "Hematopathology IRB Review",
                50,
                "translational-research"
            ),
            "ROLE_TRANSRES_IRB_REVIEWER_COVID19" => array(
                "Translational Research COVID-19 IRB Reviewer",
                "COVID-19 IRB Review",
                50,
                "translational-research"
            ),
            "ROLE_TRANSRES_IRB_REVIEWER_MISI" => array(
                "Translational Research MISI IRB Reviewer",
                "MISI IRB Review",
                50,
                "translational-research"
            ),

            "ROLE_TRANSRES_COMMITTEE_REVIEWER_APCP" => array(
                "Translational Research AP/CP Committee Reviewer",
                "AP/CP Committee Review",
                50,
                "translational-research"
            ),
            "ROLE_TRANSRES_COMMITTEE_REVIEWER_HEMATOPATHOLOGY" => array(
                "Translational Research Hematopathology Committee Reviewer",
                "Hematopathology Committee Review",
                50,
                "translational-research"
            ),
            "ROLE_TRANSRES_COMMITTEE_REVIEWER_COVID19" => array(
                "Translational Research COVID-19 Committee Reviewer",
                "COVID-19 Committee Review",
                50,
                "translational-research"
            ),
            "ROLE_TRANSRES_COMMITTEE_REVIEWER_MISI" => array(
                "Translational Research MISI Committee Reviewer",
                "MISI Committee Review",
                50,
                "translational-research"
            ),

            "ROLE_TRANSRES_PRIMARY_COMMITTEE_REVIEWER_APCP" => array(
                "Translational Research AP/CP Primary Committee Reviewer",
                "AP/CP Committee Review",
                50,
                "translational-research"
            ),
            "ROLE_TRANSRES_PRIMARY_COMMITTEE_REVIEWER_HEMATOPATHOLOGY" => array(
                "Translational Research Hematopathology Primary Committee Reviewer",
                "Hematopathology Committee Review",
                50,
                "translational-research"
            ),
            "ROLE_TRANSRES_PRIMARY_COMMITTEE_REVIEWER_COVID19" => array(
                "Translational Research COVID-19 Primary Committee Reviewer",
                "COVID-19 Committee Review",
                50,
                "translational-research"
            ),
            "ROLE_TRANSRES_PRIMARY_COMMITTEE_REVIEWER_MISI" => array(
                "Translational Research MISI Primary Committee Reviewer",
                "MISI Committee Review",
                50,
                "translational-research"
            ),

            "ROLE_TRANSRES_BILLING_ADMIN_APCP" => array(
                "Translational Research AP/CP Billing Administrator",
                "Create, View, Edit and Send an Invoice for Translational Research AP/CP Project",
                50,
                "translational-research"
            ),
            "ROLE_TRANSRES_BILLING_ADMIN_HEMATOPATHOLOGY" => array(
                "Translational Research Hematopathology Billing Administrator",
                "Create, View, Edit and Send an Invoice for Translational Research Hematopathology Project",
                50,
                "translational-research"
            ),
            "ROLE_TRANSRES_BILLING_ADMIN_COVID19" => array(
                "Translational Research COVID-19 Billing Administrator",
                "Create, View, Edit and Send an Invoice for Translational Research COVID-19 Project",
                50,
                "translational-research"
            ),
            "ROLE_TRANSRES_BILLING_ADMIN_MISI" => array(
                "Translational Research MISI Billing Administrator",
                "Create, View, Edit and Send an Invoice for Translational Research MISI Project",
                50,
                "translational-research"
            ),

            "ROLE_TRANSRES_BIOINFORMATICIAN" => array(
                "Translational Research Bioinformatician",
                "View Translational Research Project with departmental statistical or informatics support",
                30,
                "translational-research"
            ),

            "ROLE_TRANSRES_TECHNICIAN_APCP" => array(
                "Translational Research AP/CP Technician",
                "View and Edit a Translational Research AP/CP Request",
                50,
                "translational-research"
            ),
            "ROLE_TRANSRES_TECHNICIAN_HEMATOPATHOLOGY" => array(
                "Translational Research Hematopathology Technician",
                "View and Edit a Translational Research Hematopathology Request",
                50,
                "translational-research"
            ),
            "ROLE_TRANSRES_TECHNICIAN_COVID19" => array(
                "Translational Research COVID-19 Technician",
                "View and Edit a Translational Research COVID-19 Request",
                50,
                "translational-research"
            ),
            "ROLE_TRANSRES_TECHNICIAN_MISI" => array(
                "Translational Research MISI Technician",
                "View and Edit a Translational Research MISI Request",
                50,
                "translational-research"
            ),

            "ROLE_TRANSRES_REQUESTER_APCP" => array(
                "Translational Research AP/CP Project Requester",
                "Submit, View and Edit a Translational Research AP/CP Project",
                30,
                "translational-research"
            ),
            "ROLE_TRANSRES_REQUESTER_HEMATOPATHOLOGY" => array(
                "Translational Research Hematopathology Project Requester",
                "Submit, View and Edit a Translational Research Hematopathology Project",
                30,
                "translational-research"
            ),
            "ROLE_TRANSRES_REQUESTER_COVID19" => array(
                "Translational Research COVID-19 Project Requester",
                "Submit, View and Edit a Translational Research COVID-19 Project",
                30,
                "translational-research"
            ),
            "ROLE_TRANSRES_REQUESTER_MISI" => array(
                "Translational Research MISI Project Requester",
                "Submit, View and Edit a Translational Research MISI Project",
                30,
                "translational-research"
            ),

//            "ROLE_TRANSRES_HEMATOPATHOLOGY" => array(
//                "Translational Research Hematopathology User",
//                "Access to the Hematopathology Projects, Requests and Invoices",
//                50,
//                "translational-research"
//            ),
//            "ROLE_TRANSRES_APCP" => array(
//                "Translational Research AP/CP User",
//                "Access to the AP/CP Projects, Requests and Invoices",
//                50,
//                "translational-research"
//            ),

            "ROLE_DASHBOARD_ADMIN" => array(
                "Dashboards Administrator",
                "View all dashboards",
                90,
                "dashboard"
            ),

        );

        $username = $this->getUser();

        $count = 10;
        foreach( $types as $role => $aliasDescription ) {

            $alias = $aliasDescription[0];
            $description = $aliasDescription[1];
            $level = $aliasDescription[2];

            $entity = $em->getRepository(Roles::class)->findOneByName(trim((string)$role));

            if( $entity ) {
                if( !$entity->getLevel() ) {
                    $entity->setLevel($level);
                    $em->persist($entity);
                    $em->flush();
                }

                //testing
                //$this->setInstitutionResidency($entity, $role);

                //update residency track if not set
                $resResidencyTrack = $this->resetResidencyTrack($entity,$role);
                if( $resResidencyTrack ) {
                    $em->flush();
                    $this->addFlash(
                        'notice',
                        "Set residency track for $role"
                    );
                }

                //testing
//                if( isset($aliasDescription[3]) && $aliasDescription[3] == 'dashboard' ) {
//                    $this->addSites($entity,'_DASHBOARD_','dashboards');
//                    $em->flush();
//                    $this->addFlash(
//                        'notice',
//                        "Set dashboards site for $role"
//                    );
//                }

                continue; //temporary disable to override alias, description, level
            }

            if( !$entity ) {
                $entity = new Roles();
                $this->setDefaultList($entity,$count,$username,null);
            }

            $entity->setName( $role );
            $entity->setAlias( trim((string)$alias) );
            $entity->setDescription( trim((string)$description) );
            $entity->setLevel($level);

            //set sitename
            if( isset($aliasDescription[3]) ) {
                //the element exists in the array. write your code here.
                //i.e. $aliasDescription[3] === 'translational-research'
                //$input = array("a", "b", "c", "d", "e");
                //$output = array_slice($input, 0, 3);   // returns "a", "b", and "c"
                $roleParts = explode('_', $role); //ROLE TRANSRES IRB ...
                $rolePartSecondArr = array_slice($roleParts, 1, 1);
                $rolePartSecond = "_".$rolePartSecondArr[0]."_"; //_TRANSRES_
                //exit("rolePartSecond=".$rolePartSecond);
                $this->addSingleSite($entity,$rolePartSecond,$aliasDescription[3]);
            }

            $attrName = "Call Pager";

            //set attributes for ROLE_SCANORDER_ONCALL_TRAINEE
            if( $role == "ROLE_SCANORDER_ONCALL_TRAINEE" ) {
                $attrValue = "(111) 111-1111";
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:RoleAttributeList'] by [RoleAttributeList::class]
                $attrs = $em->getRepository(RoleAttributeList::class)->findBy(array("name"=>$attrName,"value"=>$attrValue));
                if( count($attrs) == 0 ) {
                    $attr = new RoleAttributeList();
                    $this->setDefaultList($attr,1,$username,$attrName);
                    $attr->setValue($attrValue);
                    $entity->addAttribute($attr);
                }
            }
            //set attributes for ROLE_SCANORDER_ONCALL_ATTENDING
            if( $role == "ROLE_SCANORDER_ONCALL_ATTENDING" ) {
                $attrValue = "(222) 222-2222";
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:RoleAttributeList'] by [RoleAttributeList::class]
                $attrs = $em->getRepository(RoleAttributeList::class)->findBy(array("name"=>$attrName,"value"=>$attrValue));
                if( count($attrs) == 0 ) {
                    $attr = new RoleAttributeList();
                    $this->setDefaultList($attr,10,$username,$attrName);
                    $attr->setValue($attrValue);
                    $entity->addAttribute($attr);
                }
            }

            if( $role == "ROLE_PLATFORM_ADMIN" || $role == "ROLE_PLATFORM_DEPUTY_ADMIN" ) {
                $nameAbbreviationSites = $this->getSiteList();
                foreach( $nameAbbreviationSites as $name=>$abbreviation ) {
                    $siteObject = $em->getRepository(SiteList::class)->findOneByAbbreviation($abbreviation);
                    if( !$entity->getSites()->contains($siteObject) ) {
                        $entity->addSite($siteObject);
                    }
                }
            }

            //set institution and Fellowship Subspecialty types to role
            $this->setInstitutionFellowship($entity,$role);

            //set institution and Residency Specialty types to role
            $this->setInstitutionResidency($entity,$role);

            $em->persist($entity);
            $em->flush();

            $count = $count + 10;

        } //foreach

        //exit("EOF generate Roles");

        return round($count/10);
    }

    //entity - role object
    //role - role string
    public function setInstitutionFellowship($entity,$role) {

        if( strpos((string)$role,'_WCM_') === false ) {
            return;
        }

        if( strpos((string)$role,'_FELLAPP_') === false ) {
            return;
        }

        $em = $this->getDoctrine()->getManager();

//        $siteObject = $em->getRepository(SiteParameters::class)->findOneByAbbreviation("fellapp");
//        if( $siteObject ) {
//            if( !$entity->getSites()->contains($siteObject) ) {
//                $entity->addSite($siteObject);
//            }
//        }
        
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:Institution'] by [Institution::class]
        $wcmc = $em->getRepository(Institution::class)->findOneByAbbreviation("WCM");
        $entity->setInstitution($wcmc);

        if( strpos((string)$role,'BREASTPATHOLOGY') !== false ) {
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:FellowshipSubspecialty'] by [FellowshipSubspecialty::class]
            $BREASTPATHOLOGY = $em->getRepository(FellowshipSubspecialty::class)->findOneByName("Breast Pathology");
            $entity->setFellowshipSubspecialty($BREASTPATHOLOGY);
            $this->addSingleSiteToEntity($entity,"fellapp");
        }

        if( strpos((string)$role,'CYTOPATHOLOGY') !== false ) {
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:FellowshipSubspecialty'] by [FellowshipSubspecialty::class]
            $CYTOPATHOLOGY = $em->getRepository(FellowshipSubspecialty::class)->findOneByName("Cytopathology");
            $entity->setFellowshipSubspecialty($CYTOPATHOLOGY);
            $this->addSingleSiteToEntity($entity,"fellapp");
        }

        if( strpos((string)$role,'GYNECOLOGICPATHOLOGY') !== false ) {
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:FellowshipSubspecialty'] by [FellowshipSubspecialty::class]
            $GYNECOLOGICPATHOLOGY = $em->getRepository(FellowshipSubspecialty::class)->findOneByName("Gynecologic Pathology");
            $entity->setFellowshipSubspecialty($GYNECOLOGICPATHOLOGY);
            $this->addSingleSiteToEntity($entity,"fellapp");
        }

        if( strpos((string)$role,'GASTROINTESTINALPATHOLOGY') !== false ) {
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:FellowshipSubspecialty'] by [FellowshipSubspecialty::class]
            $GASTROINTESTINALPATHOLOGY = $em->getRepository(FellowshipSubspecialty::class)->findOneByName("Gastrointestinal Pathology");
            $entity->setFellowshipSubspecialty($GASTROINTESTINALPATHOLOGY);
            $this->addSingleSiteToEntity($entity,"fellapp");
        }

        if( strpos((string)$role,'GENITOURINARYPATHOLOGY') !== false ) {
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:FellowshipSubspecialty'] by [FellowshipSubspecialty::class]
            $GENITOURINARYPATHOLOGY = $em->getRepository(FellowshipSubspecialty::class)->findOneByName("Genitourinary Pathology");
            $entity->setFellowshipSubspecialty($GENITOURINARYPATHOLOGY);
            $this->addSingleSiteToEntity($entity,"fellapp");
        }

        if( strpos((string)$role,'HEMATOPATHOLOGY') !== false ) {
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:FellowshipSubspecialty'] by [FellowshipSubspecialty::class]
            $HEMATOPATHOLOGY = $em->getRepository(FellowshipSubspecialty::class)->findOneByName("Hematopathology");
            $entity->setFellowshipSubspecialty($HEMATOPATHOLOGY);
            $this->addSingleSiteToEntity($entity,"fellapp");
        }

        if( strpos((string)$role,'MOLECULARGENETICPATHOLOGY') !== false ) {
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:FellowshipSubspecialty'] by [FellowshipSubspecialty::class]
            $MOLECULARGENETICPATHOLOGY = $em->getRepository(FellowshipSubspecialty::class)->findOneByName("Molecular Genetic Pathology");
            $entity->setFellowshipSubspecialty($MOLECULARGENETICPATHOLOGY);
            $this->addSingleSiteToEntity($entity,"fellapp");
        }

    }

    //entity - role object
    //role - role string
    public function setInstitutionResidency($entity,$role) {

        if( strpos((string)$role,'_WCM_') === false ) {
            return;
        }

        if( strpos((string)$role,'_RESAPP_') === false ) {
            return;
        }

        $em = $this->getDoctrine()->getManager();

        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:Institution'] by [Institution::class]
        $wcmc = $em->getRepository(Institution::class)->findOneByAbbreviation("WCM");
        $entity->setInstitution($wcmc);

        if( strpos((string)$role,'AP') !== false ) {
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:ResidencyTrackList'] by [ResidencyTrackList::class]
            $residencyAP = $em->getRepository(ResidencyTrackList::class)->findOneByName("AP");
            $entity->setResidencyTrack($residencyAP);

            $this->addSingleSiteToEntity($entity,"resapp");
            $this->addResAppPermission($entity);
        }

        if( strpos((string)$role,'CP') !== false ) {
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:ResidencyTrackList'] by [ResidencyTrackList::class]
            $residencyCP = $em->getRepository(ResidencyTrackList::class)->findOneByName("CP");
            $entity->setResidencyTrack($residencyCP);

            $this->addSingleSiteToEntity($entity,"resapp");
            $this->addResAppPermission($entity);
        }

        if( strpos((string)$role,'APCP') !== false ) {
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:ResidencyTrackList'] by [ResidencyTrackList::class]
            $residencyAPCP = $em->getRepository(ResidencyTrackList::class)->findOneByName("AP/CP");
            $entity->setResidencyTrack($residencyAPCP);

            $this->addSingleSiteToEntity($entity,"resapp");
            $this->addResAppPermission($entity);
        }

        if( strpos((string)$role,'APEXP') !== false ) {
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:ResidencyTrackList'] by [ResidencyTrackList::class]
            $residencyAPEXP = $em->getRepository(ResidencyTrackList::class)->findOneByName("AP/EXP");
            $entity->setResidencyTrack($residencyAPEXP);

            $this->addSingleSiteToEntity($entity,"resapp");
            $this->addResAppPermission($entity);
        }

        if( strpos((string)$role,'CPEXP') !== false ) {
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:ResidencyTrackList'] by [ResidencyTrackList::class]
            $residencyCPEXP = $em->getRepository(ResidencyTrackList::class)->findOneByName("CP/EXP");
            $entity->setResidencyTrack($residencyCPEXP);

            $this->addSingleSiteToEntity($entity,"resapp");
            $this->addResAppPermission($entity);
        }
    }
    //entity - role object
    //role - role string
    public function resetResidencyTrack($entity,$role) {
        //return NULL; //testing

//        if( $role == "ROLE_RESAPP_COORDINATOR_WCM_APCP" ) {
//            exit("Role $entity has residency track: " . $entity->getResidencyTrack());
//        }

        if( $entity->getResidencyTrack() ) {
            return NULL;
        }
        
        if( strpos((string)$role,'_WCM_') === false ) {
            return NULL;
        }

        if( strpos((string)$role,'_RESAPP_') === false ) {
            return NULL;
        }

        $em = $this->getDoctrine()->getManager();

        //$wcmc = $em->getRepository('AppUserdirectoryBundle:Institution')->findOneByAbbreviation("WCM");
        //$entity->setInstitution($wcmc);
        //echo "role=$role<br>";
        //exit('111');

        if( strpos((string)$role,'_APCP') !== false ) {
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:ResidencyTrackList'] by [ResidencyTrackList::class]
            $residencyAPCP = $em->getRepository(ResidencyTrackList::class)->findOneByName("AP/CP");
            if( !$residencyAPCP ) {
                exit("ResidencyTrackList not found: AP/CP");
            }
            //exit("ResidencyTrackList found: $residencyAPCP");
            $entity->setResidencyTrack($residencyAPCP);
            return $entity;
        }

        if( strpos((string)$role,'_APEXP') !== false ) {
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:ResidencyTrackList'] by [ResidencyTrackList::class]
            $residencyAPEXP = $em->getRepository(ResidencyTrackList::class)->findOneByName("AP/EXP");
            if( !$residencyAPEXP ) {
                exit("ResidencyTrackList not found: AP/EXP");
            }
            $entity->setResidencyTrack($residencyAPEXP);
            return $entity;
        }

        if( strpos((string)$role,'_CPEXP') !== false ) {
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:ResidencyTrackList'] by [ResidencyTrackList::class]
            $residencyCPEXP = $em->getRepository(ResidencyTrackList::class)->findOneByName("CP/EXP");
            if( !$residencyCPEXP ) {
                exit("ResidencyTrackList not found: CP/EXP");
            }
            $entity->setResidencyTrack($residencyCPEXP);
            return $entity;
        }

        if( strpos((string)$role,'_AP') !== false ) {
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:ResidencyTrackList'] by [ResidencyTrackList::class]
            $residencyAP = $em->getRepository(ResidencyTrackList::class)->findOneByName("AP");
            if( !$residencyAP ) {
                exit("ResidencyTrackList not found: AP");
            }
            $entity->setResidencyTrack($residencyAP);
            return $entity;
        }

        if( strpos((string)$role,'_CP') !== false ) {
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:ResidencyTrackList'] by [ResidencyTrackList::class]
            $residencyCP = $em->getRepository(ResidencyTrackList::class)->findOneByName("CP");
            if( !$residencyCP ) {
                exit("ResidencyTrackList not found: CP");
            }
            $entity->setResidencyTrack($residencyCP);
            return $entity;
        }

        return NULL;
    }

    //entity - role object
    public function setInstitutionVacReqRole($entity) {

        //role - role string
        $role = $entity->getName()."";

        if( strpos((string)$role,'_VACREQ_') === false ) {
            return;
        }

        $em = $this->getDoctrine()->getManager();
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:Institution'] by [Institution::class]
        $wcmc = $em->getRepository(Institution::class)->findOneByAbbreviation("WCM");

        //should be 8:

        //create "Executive Committee" in the Pathology Department and name the type of that group "Committee":
        //Create organizational group "Committee" with default level -2, because all other levels are taken by regular tree elements
        //-2 is mirroring of the same level 2 - "Division". This solution should work and don not cause any errors.
        //Other solution is to remove restriction for level uniqueness in the organizational group object. But, how it will affect the logic?
        //EXECUTIVE: Executive Committee
        $this->vacreqRoleSetSingleUserInstitution($entity,"EXECUTIVE",$wcmc,"Executive Committee","cwid");

        //CLINICALPATHOLOGY: Laboratory Medicine
        $this->vacreqRoleSetSingleUserInstitution($entity,"CLINICALPATHOLOGY",$wcmc,"Laboratory Medicine","cwid");

        //EXPERIMENTALPATHOLOGY (Barry Sleckman): Experimental Pathology (create new under WCM => Pathology and Laboratory Medicine)
        //bas2022@med.cornell.edu
        // +1 212 746 4842
        //Pathology and Laboratory Medicine (WCM)
        //$this->vacreqRoleSetSingleUserInstitution($entity,"EXPERIMENTALPATHOLOGY",$wcmc,"Experimental Pathology","cwid");
        //Please rename the Experimental Pathology group to Cell and Cancer Pathobiology in the faculty vacation site
        $this->vacreqRoleSetSingleUserInstitution($entity,"EXPERIMENTALPATHOLOGY",$wcmc,"Cell and Cancer Pathobiology","cwid");

        //VASCULARBIOLOGY : "Vascular Biology" (in NYP onlys. Create a new under WCM => Pathology and Laboratory Medicine => Research)
        $this->vacreqRoleSetSingleUserInstitution($entity,"VASCULARBIOLOGY",$wcmc,"Vascular Biology","cwid");

        //HEMATOPATHOLOGY : "Hematopathology" - use division, not service
        $this->vacreqRoleSetSingleUserInstitution($entity,"HEMATOPATHOLOGY",$wcmc,"Hematopathology","cwid");

        //SURGICALPATHOLOGY : Anatomic Pathology
        $this->vacreqRoleSetSingleUserInstitution($entity,"SURGICALPATHOLOGY",$wcmc,"Anatomic Pathology","cwid");

        //CYTOPATHOLOGY : Cytopathology
        $this->vacreqRoleSetSingleUserInstitution($entity,"CYTOPATHOLOGY",$wcmc,"Cytopathology","cwid");

        //DERMATOPATHOLOGY : Dermatopathology
        $this->vacreqRoleSetSingleUserInstitution($entity,"DERMATOPATHOLOGY",$wcmc,"Dermatopathology","cwid");

        //SUPERVISOR : Pathology and Laboratory Medicine
        $this->vacreqRoleSetSingleUserInstitution($entity,"SUPERVISOR",$wcmc,"Pathology and Laboratory Medicine","cwid");

        return 0;
    }
    //Assign Institution to a Role Object
    //$VacReqGroupStr: "DERMATOPATHOLOGY" string
    //$root: $wcmc object
    //$instName: "Dermatology" institution name string
    public function vacreqRoleSetSingleUserInstitution($entity,$VacReqGroupStr,$root,$instName,$cwid) {
        $em = $this->getDoctrine()->getManager();
        $role = $entity->getName()."";
        //echo "role=".$role."<br>";
        //DERMATOPATHOLOGY: ?
        if( strpos((string)$role,$VacReqGroupStr) !== false ) {
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:Institution'] by [Institution::class]
            $groupObject = $em->getRepository(Institution::class)->findNodeByNameAndRoot($root->getId(),$instName);
            if( !$groupObject ) {
                echo "vacreqRoleSetSingleUserInstitution: ".$root." (ID ".$root->getId()."): no child found with name=".$instName."<br>";
                exit();
                //return;
            }
            $entity->setInstitution($groupObject);

            //assign approver APPROVER
            //echo "cwid=".$cwid."<br>";
            if( $cwid && strpos((string)$role,"ROLE_VACREQ_APPROVER") !== false ) {
                $approver = $em->getRepository(User::class)->findOneByPrimaryPublicUserId($cwid);
                //echo "approver=".$approver."<br>";
                if( $approver ) {
                    $approver->addRole($entity);
                    //$em->flush($approver);
                    $em->flush();
                    //echo "user found by cwid=".$cwid."<br>";
                } else {
                    //exit("user not found by cwid=".$cwid);
                }
            }

            //assign SUPERVISOR
            if( $cwid && strpos((string)$role,"ROLE_VACREQ_SUPERVISOR") !== false ) {
                $supervisor = $em->getRepository(User::class)->findOneByPrimaryPublicUserId($cwid);
                //echo "supervisor=".$supervisor."<br>";
                if( $supervisor ) {
                    $supervisor->addRole($entity);
                    //$em->flush($supervisor);
                    $em->flush();
                    //echo "user found by cwid=".$cwid."<br>";
                } else {
                    //exit("user not found by cwid=".$cwid);
                }
            }

            //Don't use this method: Use Role's Institution to link a role and VacReqRequest's Institution
            //Create and add appropriate permission to this role:
            //Permission Holder: "" - Permission: "Submit a Vacation Request", Institution(s): ""
        }
    }

    public function generateSiteParameters() {
        $userServiceUtil = $this->container->get('user_service_utility');
        return $userServiceUtil->generateSiteParameters();
    }

    public function generateDefaultOrgGroupSiteParameters() {
        $userSecUtil = $this->container->get('user_security_utility');
        $em = $this->getDoctrine()->getManager();
        $entities = $em->getRepository(SiteParameters::class)->findAll();

        if( count($entities) != 1 ) {
            throw new \Exception( 'Must have only one parameter object. Found '.count($entities).'object(s)' );
        }

        $entity = $entities[0];

        if( !$entity ) {
            exit('SiteParameters not found');
        }

        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:Institution'] by [Institution::class]
        $nyp = $em->getRepository(Institution::class)->findOneByAbbreviation("NYP");
        if( !$nyp ) {
            exit('No Institution: "NYP"');
        }

        $autoAssignInstitution = $userSecUtil->getAutoAssignInstitution();

        if( !$autoAssignInstitution ) {
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:Institution'] by [Institution::class]
            $wcmc = $em->getRepository(Institution::class)->findOneByAbbreviation("WCM");
            if( !$wcmc ) {
                exit('generateDefaultOrgGroupSiteParameters: No Institution: "WCM"');
            }

            $mapper = array(
                'prefix' => 'App',
                'bundleName' => 'UserdirectoryBundle',
                'className' => 'Institution',
                'fullClassName' => "App\\UserdirectoryBundle\\Entity\\Institution",
                'entityNamespace' => "App\\UserdirectoryBundle\\Entity"
            );
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:Institution'] by [Institution::class]
            $autoAssignInstitution = $em->getRepository(Institution::class)->findByChildnameAndParent(
                "Pathology and Laboratory Medicine",
                $wcmc,
                $mapper
            );
        }

        if( !$autoAssignInstitution ) {
            exit('No Default Auto Assign Institution found.');
        }

        $pathDefaultGroup = null;

        foreach( $entity->getOrganizationalGroupDefaults() as $groupDefault ) {
            if( $groupDefault->getInstitution() ) {
              if( $groupDefault->getInstitution()->getId() == $autoAssignInstitution->getId() ) {
                  $pathDefaultGroup = $groupDefault;
                  break;
              }
            }
        }

        if( $pathDefaultGroup ) {
            return 0;
        }

        $pathDefaultGroup = new OrganizationalGroupDefault();

        //target Institution
        $pathDefaultGroup->setInstitution($autoAssignInstitution);

        //primaryPublicUserIdType (WCM CWID)
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:UsernameType'] by [UsernameType::class]
        $primaryPublicUserIdType = $em->getRepository(UsernameType::class)->findOneByName("Active Directory (LDAP)");
        if( !$primaryPublicUserIdType ) {
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:UsernameType'] by [UsernameType::class]
            $primaryPublicUserIdTypes = $em->getRepository(UsernameType::class)->findAll();
            if( count($primaryPublicUserIdTypes) > 0 ) {
                $primaryPublicUserIdType = $primaryPublicUserIdTypes[0];
            }
        }
        if( !$primaryPublicUserIdType ) {
            exit('No UsernameType found.');
        }
        $pathDefaultGroup->setPrimaryPublicUserIdType($primaryPublicUserIdType);

        //email
        $pathDefaultGroup->setEmail("@med.cornell.edu");

        //roles
        //ROLE_SCANORDER_SUBMITTER
        $role = $em->getRepository(Roles::class)->findOneByName("ROLE_SCANORDER_SUBMITTER");
        if( !$role ) {
            exit('No Role: "ROLE_SCANORDER_SUBMITTER"');
        }
        $pathDefaultGroup->addRole($role);
        //ROLE_USERDIRECTORY_OBSERVER
        $role = $em->getRepository(Roles::class)->findOneByName("ROLE_USERDIRECTORY_OBSERVER");
        if( !$role ) {
            exit('No Role: "ROLE_USERDIRECTORY_OBSERVER"');
        }
        $pathDefaultGroup->addRole($role);
        //ROLE_VACREQ_OBSERVER_WCM_PATHOLOGY
        $role = $em->getRepository(Roles::class)->findOneByName("ROLE_VACREQ_OBSERVER_WCM_PATHOLOGY");
        if( !$role ) {
            exit('No Role: "ROLE_VACREQ_OBSERVER_WCM_PATHOLOGY"');
        }
        $pathDefaultGroup->addRole($role);

        //timezone
        //$timezone = new \DateTimeZone('America/New_York');
        $pathDefaultGroup->setTimezone('America/New_York');

        //tooltipe
        $pathDefaultGroup->setTooltip(true);

        //showToInstitutions: WCM, NYP
        $pathDefaultGroup->addShowToInstitution($autoAssignInstitution);
        $pathDefaultGroup->addShowToInstitution($nyp);

        //defaultInstitution
        $pathDefaultGroup->setDefaultInstitution($autoAssignInstitution);

        //permittedInstitutionalPHIScope: WCM
        $pathDefaultGroup->addPermittedInstitutionalPHIScope($autoAssignInstitution);

        //employmentType
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:EmploymentType'] by [EmploymentType::class]
        $employmentType = $em->getRepository(EmploymentType::class)->findOneByName("Full Time");
        if( !$employmentType ) {
            exit('No object EmploymentType: "Full Time"');
        }
        $pathDefaultGroup->setEmploymentType($employmentType);

        //locale: en_US - English (United States)
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:LocaleList'] by [LocaleList::class]
        $locale = $em->getRepository(LocaleList::class)->findOneByName("en_US");
        if( !$locale ) {
            exit('No object LocaleList: "en_US"');
        }
        $pathDefaultGroup->setLocale($locale);

        //languages
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:LanguageList'] by [LanguageList::class]
        $language = $em->getRepository(LanguageList::class)->findOneByName("American English");
        if( !$language ) {
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:LanguageList'] by [LanguageList::class]
            $language = $em->getRepository(LanguageList::class)->findOneByName("English");
        }
        if( !$language ) {
            exit('No object LanguageList: "American English or English"');
        }
        $pathDefaultGroup->addLanguage($language);

        //administrativeTitleInstitution
        $pathDefaultGroup->setAdministrativeTitleInstitution($autoAssignInstitution);

        //academicTitleInstitution
        $pathDefaultGroup->setAcademicTitleInstitution($autoAssignInstitution);

        //medicalTitleInstitution
        $pathDefaultGroup->setMedicalTitleInstitution($autoAssignInstitution);

        //locationTypes
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:LocationTypeList'] by [LocationTypeList::class]
        $locationType = $em->getRepository(LocationTypeList::class)->findOneByName("Employee Office");
        if( !$locationType ) {
            exit('No object LocationTypeList: "Employee Office"');
        }
        $pathDefaultGroup->addLocationType($locationType);

        //locationInstitution
        $pathDefaultGroup->setLocationInstitution($autoAssignInstitution);

        //city
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:CityList'] by [CityList::class]
        $city = $em->getRepository(CityList::class)->findOneByName("New York");
        if( !$city ) {
            exit('No object CityList: "New York"');
        }
        $pathDefaultGroup->setCity($city);

        //state
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:States'] by [States::class]
        $state = $em->getRepository(States::class)->findOneByName("New York");
        if( !$state ) {
            exit('No object States: "New York"');
        }
        $pathDefaultGroup->setState($state);

        //zip
        $pathDefaultGroup->setZip("10065");

        //country
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:Countries'] by [Countries::class]
        $country = $em->getRepository(Countries::class)->findOneByName("United States");
        if( !$country ) {
            exit('No object Countries: "United States"');
        }
        $pathDefaultGroup->setCountry($country);

        //medicalLicenseCountry
        $pathDefaultGroup->setMedicalLicenseCountry($country);

        //medicalLicenseState
        $pathDefaultGroup->setMedicalLicenseState($state);


        $entity->addOrganizationalGroupDefault($pathDefaultGroup);

        $em->persist($pathDefaultGroup);
        $em->flush();

        return 1;
    }


    public function generateSitenameList($user=null) {

        $em = $this->getDoctrine()->getManager();
        
        $elements = $this->getSiteList();
        $descriptions = $this->getSiteDescription();

        $count = 10;
        foreach( $elements as $name => $abbreviation ) {

            $entity = $em->getRepository(SiteList::class)->findOneByName($name);
            if( $entity ) {

                if( !$entity->getDescription() ) {
                    if( isset($descriptions[$name]) ) {
                        $entity->setDescription($descriptions[$name]);
                        $em->flush();
                    }
                }

                continue;
            }

            $entity = new SiteList();
            $this->setDefaultList($entity,$count,$user,$name);

            $entity->setAbbreviation($abbreviation);

            if( isset($descriptions[$name]) ) {
                $entity->setDescription($descriptions[$name]);
            }

            $em->persist($entity);
            $em->flush();

            $count = $count + 10;

        } //foreach

        return round($count/10);

    }
    public function getSiteList() {
        $elements = array(
            'directory' => 'employees',
            'scan' => 'scan',
            'fellowship-applications' => 'fellapp',
            'residency-applications' => 'resapp',
            'deidentifier' => 'deidentifier',
            'time-away-request' => 'vacreq',
            'call-log-book' => 'calllog',
            'critical-result-notifications' => 'crn',
            'translational-research' => 'translationalresearch',
            'dashboards' => 'dashboard'
        );
        return $elements;
    }
    public function getSiteDescription() {

        //https://view.med.cornell.edu/translational-research/project/select-new-project-type
        $trpSelectNewProjectLink = $this->container->get('router')->generate(
            'translationalresearch_project_new_selector',
            array(),
            UrlGeneratorInterface::ABSOLUTE_URL
        );
        $trpSelectNewProjectLink = '<a target="_blank" href="' . $trpSelectNewProjectLink . '">'.$trpSelectNewProjectLink.'</a>';

        $elements = array(
            'directory' => '"Employee Directory" is a site for finding and managing contact information,'
            .' locations, titles, institutional affiliations, and documents associated with user profiles.',

            'scan' => '"Scan Order" is a site for submission, management, and status tracking of requests to scan glass slides.',

            'fellowship-applications' => '"Fellowship Applications" is a site for online submission'
            .' and management of fellowship applications, recommendation letter submissions,'
            .' interview scheduling, and candidate interview evaluation.',

            'residency-applications' => '"Residency Applications" is a site for management of residency (ERAS) applications,'
            .' interview scheduling, and candidate interview evaluation.',

            'deidentifier' => '"De-identifier" is a site for assigning and tracking unique'
            .' identifiers to specimens or specimen containers for research projects'
            .' requiring deidentification of clinical specimens and aiding honest brokers.',

            'time-away-request' => '"Away Request" is a site for submission and approval of vacation requests,'
            .' business travel requests, year-to-year carryover requests'
            .' and floating day requests for faculty and fellows.'
            .' It includes an away calendar and summary statistics.',

            'call-log-book' => 'Call Log Book" is a site for documenting interactions with'
            .' the resident physicians on call and reviewing previous'
            .' documentation related to specific patients or issue types.',

            'critical-result-notifications' => '"Critical Result Notifications" is a site for logging and'
            .' initiating manual and automated notifications of healthcare'
            .' providers regarding critical test results,'
            .' as well as for acknowledgement tracking confirming the'
            .' receipt of automated notifications.',

            'translational-research' => '"Translational Research" is a site for specimen-associated project request submission,'
            .' multi-stage approval, subsequent work order submission and processing,'
            .' as well as invoice generation and payment tracking with automated reminders.'
            .' To submit a new project request without requesting access, please visit '.$trpSelectNewProjectLink,

            'dashboards' => '"Dashboards" is a site for displaying categorized chart groups to authorized users,'
            .' including charts to monitor productivity, financial statistics,'
            .' turnaround times, applicant scores, and site activity.'
        );
        return $elements;
    }

    public function generateInstitutionTypes() {

        $em = $this->getDoctrine()->getManager();

        $elements = array(
            'Medical',
            'Educational',
            'Collaboration',
            'Research Lab'
        );


        $username = $this->getUser();

        $count = 10;
        foreach( $elements as $name ) {

        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:InstitutionType'] by [InstitutionType::class]
            $entity = $em->getRepository(InstitutionType::class)->findOneByName($name);
            if( $entity ) {
                continue;
            }

            $entity = new InstitutionType();
            $this->setDefaultList($entity,$count,$username,$name);

            $em->persist($entity);
            $em->flush();

            $count = $count + 10;

        } //foreach

        return round($count/10);

    }

    public function generateOrganizationalGroupType() {

        $em = $this->getDoctrine()->getManager();
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:OrganizationalGroupType'] by [OrganizationalGroupType::class]
        $entities = $em->getRepository(OrganizationalGroupType::class)->findAll();

        if( $entities ) {
            return -1;
        }

        $elements = array(
            'Institution' => 0, //positive level - default level's title
            'Department' => 1,
            'Division' => 2,
            'Service' => 3,
            'Committee' => -21, //negative level - all other title: Committee is under Department, so it's -2. Additional index -21 is just for indication that this level has another title "Research Lab"
            'Research Lab' => -22
        );

        $username = $this->getUser();

        $count = 10;
        foreach( $elements as $name=>$level ) {

            $entity = new OrganizationalGroupType();
            $this->setDefaultList($entity,$count,$username,$name);

            $entity->setLevel($level);

            $em->persist($entity);
            $em->flush();

            $count = $count + 10;

        } //foreach

        return round($count/10);

    }

    //https://bitbucket.org/weillcornellpathology/scanorder/issue/221/multiple-office-locations-and-phone
    public function generateInstitutions() {

        $username = $this->getUser();

        $em = $this->getDoctrine()->getManager();

        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:Institution'] by [Institution::class]
        $entities = $em->getRepository(Institution::class)->findAll();

        if( $entities ) {
            return -1;
        }

        $wcmcDep = array(
            'Anesthesiology',
            'Biochemistry',
            'Cardiothoracic Surgery' => array(
                'Thoracic Surgery'
            ),
            'Cell and Developmental Biology' => null,
            'Dermatology' => null,
            'Feil Family Brain and Mind Research Institute',
            'Genetic Medicine' => null,
            'Healthcare Policy and Research' => array(
                'Biostatistics and Epidemiology',
                'Comparative Effectiveness and Outcomes Research',
                'Health Informatics',
                'Health Policy and Economics',
                'Health Systems Innovation and Implementation Science'
            ),
            'Microbiology and Immunology' => null,
            'Neurological Surgery' => null,
            'Neurology' => array(
                "Alzheimer's Disease & Memory Disorders",
                "Diagnostic Testing - Evoked Potentials, EEG & EMG",
                "Doppler (Transcranial and Carotid Duplex) Ultrasound Studies"              //continue
            ),
            'Obstetrics and Gynecology' => array(
                'General Ob/Gyn',
                'Gynecology',
                'Gynecologic Oncology'                                                     //continue
            ),
            'Ophthalmology' => null,
            'Orthopaedic Surgery' => null,
            'Otolaryngology - Head and Neck Surgery' => null,
            'Pathology and Laboratory Medicine' => array(
                'shortname' => 'Pathology',
                //divisions
                'Anatomic Pathology' => array(
                    //services
                    'Autopsy Pathology',
                    'Breast Pathology',
                    'Cardiopulmonary Pathology',
                    'Cytopathology',
                    'Dermatopathology',
                    'Gastrointestinal and Liver Pathology',
                    'Genitourinary Pathology',
                    'Gynecologic Pathology',
                    'Head and Neck Pathology',
                    'Hematopathology',
                    'Neuropathology',
                    'Pediatric Pathology',
                    'Perinatal and Obstetric Pathology',
                    'Renal Pathology',
                    'Surgical Pathology'
                ),
                'Hematopathology' => array(
                    'Immunopathology',
                    'Molecular Hematopathology'
                ),
                'Weill Cornell Pathology Consultation Services' => array(
                    'Breast Pathology',
                    'Dermatopathology',
                    'Gastrointestinal and Liver Pathology',
                    'Genitourinary Pathology',
                    'Gynecologic Pathology',
                    'Hematopathology',
                    'Perinatal and Obstetrical Pathology',
                    'Renal Pathology'
                ),
                'Laboratory Medicine' => array(
                    'Clinical Chemistry',
                    'Cytogenetics',
                    'Routine and special coagulation',
                    'Endocrinology',
                    'Routine and special hematology',
                    'Immunochemistry',
                    'Serology',
                    'Immunohematology',
                    'Microbiology',
                    'Molecular diagnostics',
                    'Toxicology',
                    'Mycology',
                    'Therapeutic drug monitoring',
                    'Parasitology',
                    'Virology'
                ),
                'Pathology Informatics' => array(
                    'Scanning Service',
                ),
                'Pathology Fellowship Programs',
                'Center for Translational Pathology'
            ),
            'Pediatrics' => array(
                'Cardiology',
                'Child Development',
                'Child Neurology'                                                           //continue
            ),
            'Pharmacology' => null,
            'Physiology and Biophysics' => null,
            'Primary Care' => null,
            'Psychiatry' => array(
                'Sackler Institute for Developmental Psychobiology'
            ),
            'Radiation Oncology' => null,
            'Radiology' => null,
            'Rehabilitation Medicine' => null,
            'Reproductive Medicine' => array(
                'Center for Reproductive Medicine and Infertility (CRMI)',
                'Center for Male Reproductive Medicine and Microsurgery'
            ),
            'Surgery' => array(
                'Breast Surgery',
                'Burn, Critical Care and Trauma',
                'Colon & Rectal Surgery',                                                   //continue
            ),
            'Urology' => array(
                'Brady Urologic Health Center'
            ),
            'Weill Department of Medicine' => array(
                'Cardiology',
                'Clinical Epidemiology and Evaluative Sciences Research',
                'Clinical Pharmacology'                                                     //continue  dep
            ),
            'Other Centers' => array(
                'Ansary Stem Cell Institute',
                'Center for Complementary and Integrative Medicine',
                'Center for Healthcare Informatics and Policy'                              //continue
            )

        );
        $wcmc = array(
            'abbreviation'=>'WCM',
            'departments'=>$wcmcDep
        );

        //http://nyp.org/services/index.html
        $nyhDep = array(
            'Allergy, Immunology and Pulmonology' => null,
            'Anesthesiology' => null,
            'Cancer (Oncology)' => null,
            'Cancer Screening and Awareness' => null,
            'Cardiology' => null,
			'Complementary, Alternative, and Integrative Medicine' => null,
            'Dermatology' => null,
            'Diabetes and Endocrinology' => null,
            'Digestive Diseases' => null,
            'Ear, Nose, and Throat (Otorhinolaryngology)' => null,
            'Geriatrics' => null,
            'Hematology (Blood Disorders)' => null,
            'Infectious Diseases/International Medicine' => null,
            'Internal Medicine' => null,
            'Nephrology (Kidney Disease)' => null,
            'Neurology and Neuroscience' => null,
            'Obstetrics and Gynecology' => null,
            'Ophthalmology' => null,
            'Pain Medicine' => null,
            'Pathology and Laboratory Medicine' => null,
            'Pediatrics' => null,
            'Preventive Medicine and Nutrition' => null,
            'Psychiatry and Mental Health' => null,
            'Radiation Oncology' => null,
            'Radiology' => null,
            'Rehabilitation Medicine' => null,
            'Rheumatology' => null,
            "Women's Health" => null
        );

        $nyh = array(
            'abbreviation'=>'NYP',
            'departments'=>$nyhDep
        );


        $wcmcq = array(
            'abbreviation'=>'WCMQ',
            'departments'
        );

        $mskDep = array(
            'Anesthesiology and Critical Care Medicine' => null,
            'Laboratory Medicine' => null,
            'Medicine' => null
            //continue
        );
        $msk = array(
            'abbreviation'=>'MSK',
            'departments'=>$mskDep
        );

        $hssDep = array(
            'Orthopedic Surgery' => null,
            'Anesthesiology' => null,
            'Medicine' => null
            //continue
        );
        $hss = array(
            'abbreviation'=>'HSS',
            'departments'=>$hssDep
        );

        $institutions = array(
            "Weill Cornell Medical College"=>$wcmc,
            "New York Presbyterian Hospital"=>$nyh,
            "Weill Cornell Medical College Qatar"=>$wcmcq,
            "Memorial Sloan Kettering Cancer Center"=>$msk,
            "Hospital for Special Surgery"=>$hss
        );


        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:InstitutionType'] by [InstitutionType::class]
        $medicalType = $em->getRepository(InstitutionType::class)->findOneByName('Medical');

        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:OrganizationalGroupType'] by [OrganizationalGroupType::class]
        $levelInstitution = $em->getRepository(OrganizationalGroupType::class)->findOneByName('Institution');
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:OrganizationalGroupType'] by [OrganizationalGroupType::class]
        $levelDepartment = $em->getRepository(OrganizationalGroupType::class)->findOneByName('Department');
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:OrganizationalGroupType'] by [OrganizationalGroupType::class]
        $levelDivision = $em->getRepository(OrganizationalGroupType::class)->findOneByName('Division');
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:OrganizationalGroupType'] by [OrganizationalGroupType::class]
        $levelService = $em->getRepository(OrganizationalGroupType::class)->findOneByName('Service');

        $treeCount = 10;

        foreach( $institutions as $institutionname=>$infos ) {

        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:Institution'] by [Institution::class]
            if( $em->getRepository(Institution::class)->findOneByName($institutionname) ) {
                continue;
            }

            $institution = new Institution();
            $this->setDefaultList($institution,$treeCount,$username,$institutionname);
            $treeCount = $treeCount + 10;
            $institution->setAbbreviation( trim((string)$infos['abbreviation']) );

            $institution->addType($medicalType);
            $institution->setOrganizationalGroupType($levelInstitution);

            if( array_key_exists('departments', $infos) && $infos['departments'] && is_array($infos['departments'])  ) {

                foreach( $infos['departments'] as $departmentname=>$divisions ) {

                    if( is_numeric($departmentname) ){
                        $departmentname = $infos['departments'][$departmentname];
                    }

        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:Institution'] by [Institution::class]
                    if( $em->getRepository(Institution::class)->findOneByName($departmentname) ) {
                        continue;
                    }

                    $department = new Institution();

                    //echo "departmentname=".$departmentname."<br>";
                    $this->setDefaultList($department,$treeCount,$username,$departmentname);
                    $treeCount = $treeCount + 10;
                    $department->setOrganizationalGroupType($levelDepartment);

                    if( $divisions && is_array($divisions) ) {

                        foreach( $divisions as $divisionname=>$services ) {

                            //shortname
                            if( $divisionname === 'shortname' && $services ) {
                                //echo "<br> services=".$services."<br>";
                                $department->setShortname($services);
                                continue;
                            }

                            if( is_numeric($divisionname) ){
                                $divisionname = $divisions[$divisionname];
                            }

        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:Institution'] by [Institution::class]
                            if( $em->getRepository(Institution::class)->findOneByName($divisionname) ) {
                                continue;
                            }

                            $division = new Institution();

                            $this->setDefaultList($division,$treeCount,$username,$divisionname);
                            $treeCount = $treeCount + 10;
                            $division->setOrganizationalGroupType($levelDivision);

                            if( $services && is_array($services) ) {

                                foreach( $services as $servicename ) {

                                    if( is_numeric($servicename) ){
                                        $servicename = $services[$servicename];
                                    }

        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:Institution'] by [Institution::class]
                                    if( $em->getRepository(Institution::class)->findOneByName($servicename) ) {
                                        continue;
                                    }

                                    $service = new Institution();
                                    $this->setDefaultList($service,$treeCount,$username,$servicename);
                                    $treeCount = $treeCount + 10;
                                    $service->setOrganizationalGroupType($levelService);

                                    $division->addChild($service);
                                }
                            }//services


                            $department->addChild($division);
                        }
                    }//divisions

                    $institution->addChild($department);
                }
            }//departmets

            $em->persist($institution);
            $em->flush();
        } //foreach

        return round($treeCount/10);
    }

    public function generateAuxiliaryInstitutions() {

        $em = $this->getDoctrine()->getManager();
        $username = $this->getUser();
        $count = 0;

        //echo 'generate Auxiliary Institutions <br>';

        //All Institutions
        //echo 'All Institutions <br>';
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:Institution'] by [Institution::class]
        $allInst = $em->getRepository(Institution::class)->findOneByAbbreviation("All Institutions");
        if( !$allInst ) {
            $allInst = new Institution();
            $this->setDefaultList($allInst,1,$username,"All Institutions");
            $allInst->setAbbreviation("All Institutions");
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:InstitutionType'] by [InstitutionType::class]
            $medicalType = $em->getRepository(InstitutionType::class)->findOneByName('Medical');
            $allInst->addType($medicalType);
            //$allInst->setOrganizationalGroupType($levelInstitution);

            $em->persist($allInst);
            //$em->flush($allInst);
            $em->flush();
            $count++;
        }

        //All Collaborations
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:InstitutionType'] by [InstitutionType::class]
        $collaborationType = $em->getRepository(InstitutionType::class)->findOneByName('Collaboration');
        //echo 'All Collaborations <br>';
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:Institution'] by [Institution::class]
        $allCollaborationInst = $em->getRepository(Institution::class)->findOneByAbbreviation("All Collaborations");
        if( !$allCollaborationInst ) {
            $allCollaborationInst = new Institution();
            $this->setDefaultList($allCollaborationInst,2,$username,"All Collaborations");
            $allCollaborationInst->setAbbreviation("All Collaborations");
            $allCollaborationInst->addType($collaborationType);
            //$allCollaborationInst->setOrganizationalGroupType($levelInstitution);
            $em->persist($allCollaborationInst);
            //$em->flush($allCollaborationInst);
            $em->flush();
            $count++;
        }

        //add 'WCM-NYP Collaboration'
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:Institution'] by [Institution::class]
        $wcmcnypCollaborationInst = $em->getRepository(Institution::class)->findOneByName('WCM-NYP Collaboration');
        if( !$wcmcnypCollaborationInst ) {
            $wcmcnypCollaborationInst = new Institution();
            $this->setDefaultList($wcmcnypCollaborationInst,3,$username,"WCM-NYP Collaboration");
            $wcmcnypCollaborationInst->setAbbreviation("WCM-NYP Collaboration");

            $wcmcnypCollaborationInst->addType($collaborationType);

        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:Institution'] by [Institution::class]
            $wcmc = $em->getRepository(Institution::class)->findOneByAbbreviation("WCM");
            $wcmcnypCollaborationInst->addCollaborationInstitution($wcmc);

        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:Institution'] by [Institution::class]
            $nyp = $em->getRepository(Institution::class)->findOneByAbbreviation("NYP");
            $wcmcnypCollaborationInst->addCollaborationInstitution($nyp);

        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:CollaborationTypeList'] by [CollaborationTypeList::class]
            $unionCollaborationType = $em->getRepository(CollaborationTypeList::class)->findOneByName("Union");
            $wcmcnypCollaborationInst->setCollaborationType($unionCollaborationType);

            $allCollaborationInst->addChild($wcmcnypCollaborationInst);

            $em->persist($allCollaborationInst);
            $em->persist($wcmcnypCollaborationInst);
            $em->flush();
            $count++;
        }

//            //add WCM-NYP collaboration object to this "WCM-NYP" institution above
//            $wcmcnypCollaboration = $em->getRepository('AppUserdirectoryBundle:Collaboration')->findOneByName("WCM-NYP");
//            if( !$wcmcnypCollaboration ) {
//                $wcmcnypCollaboration = new Collaboration();
//                $this->setDefaultList($wcmcnypCollaboration,10,$username,"WCM-NYP");
//
//                //add institutions
//                //WCM
//                $wcmc = $em->getRepository('AppUserdirectoryBundle:Institution')->findOneByName("WCM");
//                if( !$wcmc ) {
//                    exit('No Institution: "WCM"');
//                }
//                $wcmcnypCollaboration->addInstitution($wcmc);
//                //NYP
//                $nyp = $em->getRepository('AppUserdirectoryBundle:Institution')->findOneByName("NYP");
//                if( !$nyp ) {
//                    exit('No Institution: "NYP"');
//                }
//                $wcmcnypCollaboration->addInstitution($nyp);
//
//                //set type
//                $collaborationType = $em->getRepository('AppUserdirectoryBundle:CollaborationTypeList')->findOneByName("Union");
//                if( !$collaborationType ) {
//                    exit('No CollaborationTypeList: "Union"');
//                }
//                $wcmcnypCollaboration->setCollaborationType($collaborationType);
//            }
//            $wcmcnypCollaborationInst->addCollaboration($wcmcnypCollaboration);
//
//            $em->persist($allCollaborationInst);
//            $em->flush();
//            $count++;


        return $count;
    }

    public function generateStates() {

        $em = $this->getDoctrine()->getManager();
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:States'] by [States::class]
        $entities = $em->getRepository(States::class)->findAll();

        if( $entities ) {
            return -1;
        }

        $states = array(
            'AL'=>"Alabama",
            'AK'=>"Alaska",
            'AZ'=>"Arizona",
            'AR'=>"Arkansas",
            'CA'=>"California",
            'CO'=>"Colorado",
            'CT'=>"Connecticut",
            'DE'=>"Delaware",
            'DC'=>"District Of Columbia",
            'FL'=>"Florida",
            'GA'=>"Georgia",
            'HI'=>"Hawaii",
            'ID'=>"Idaho",
            'IL'=>"Illinois",
            'IN'=>"Indiana",
            'IA'=>"Iowa",
            'KS'=>"Kansas",
            'KY'=>"Kentucky",
            'LA'=>"Louisiana",
            'ME'=>"Maine",
            'MD'=>"Maryland",
            'MA'=>"Massachusetts",
            'MI'=>"Michigan",
            'MN'=>"Minnesota",
            'MS'=>"Mississippi",
            'MO'=>"Missouri",
            'MT'=>"Montana",
            'NE'=>"Nebraska",
            'NV'=>"Nevada",
            'NH'=>"New Hampshire",
            'NJ'=>"New Jersey",
            'NM'=>"New Mexico",
            'NY'=>"New York",
            'NC'=>"North Carolina",
            'ND'=>"North Dakota",
            'OH'=>"Ohio",
            'OK'=>"Oklahoma",
            'OR'=>"Oregon",
            'PA'=>"Pennsylvania",
            'RI'=>"Rhode Island",
            'SC'=>"South Carolina",
            'SD'=>"South Dakota",
            'TN'=>"Tennessee",
            'TX'=>"Texas",
            'UT'=>"Utah",
            'VT'=>"Vermont",
            'VA'=>"Virginia",
            'WA'=>"Washington",
            'WV'=>"West Virginia",
            'WI'=>"Wisconsin",
            'WY'=>"Wyoming"
        );

        $username = $this->getUser();

        $count = 10;
        foreach( $states as $key => $value ) {

            $entity = new States();
            $this->setDefaultList($entity,$count,$username,null);
            $entity->setName( trim((string)$value) );
            $entity->setAbbreviation( trim((string)$key) );

            $em->persist($entity);
            $em->flush();

            $count = $count + 10;

        } //foreach

        return round($count/10);
    }


    public function generate_Old_CountryList_Old() {

        $em = $this->getDoctrine()->getManager();

//        $entities = $em->getRepository('AppUserdirectoryBundle:Countries')->findAll();
//        if( $entities ) {
//            //return -1;
//        }

//        $elements = Intl::getRegionBundle()->getCountryNames();
//        print_r($elements);
//        exit();

        $elements = array(
            "Afghanistan", "Albania", "Algeria", "American Samoa", "Andorra", "Angola", "Anguilla", "Antarctica", "Antigua and Barbuda",
            "Argentina", "Armenia", "Aruba", "Australia", "Austria", "Azerbaijan", "Bahamas", "Bahrain", "Bangladesh", "Barbados", "Belarus",
            "Belgium", "Belize", "Benin", "Bermuda", "Bhutan", "Bolivia", "Bosnia and Herzegowina", "Botswana", "Bouvet Island", "Brazil",
            "British Indian Ocean Territory", "Brunei Darussalam", "Bulgaria", "Burkina Faso", "Burundi", "Cambodia", "Cameroon", "Canada",
            "Cape Verde", "Cayman Islands", "Central African Republic", "Chad", "Chile", "China", "Christmas Island", "Cocos (Keeling) Islands",
            "Colombia", "Comoros", "Congo", "Congo, the Democratic Republic of the", "Cook Islands", "Costa Rica", "Cote d'Ivoire",
            "Croatia (Hrvatska)", "Cuba", "Cyprus", "Czech Republic", "Denmark", "Djibouti", "Dominica", "Dominican Republic",
            "East Timor", "Ecuador", "Egypt", "El Salvador", "Equatorial Guinea", "Eritrea", "Estonia", "Ethiopia", "Falkland Islands (Malvinas)",
            "Faroe Islands", "Fiji", "Finland", "France", "France Metropolitan", "French Guiana", "French Polynesia", "French Southern Territories",
            "Gabon", "Gambia", "Georgia", "Germany", "Ghana", "Gibraltar", "Greece", "Greenland", "Grenada", "Guadeloupe", "Guam", "Guatemala",
            "Guinea", "Guinea-Bissau", "Guyana", "Haiti", "Heard and Mc Donald Islands", "Holy See (Vatican City State)", "Honduras", "Hong Kong",
            "Hungary", "Iceland", "India", "Indonesia", "Iran (Islamic Republic of)", "Iraq", "Ireland", "Israel", "Italy", "Jamaica", "Japan",
            "Jordan", "Kazakhstan", "Kenya", "Kiribati", "Korea, Democratic People's Republic of", "Korea, Republic of", "Kuwait", "Kyrgyzstan",
            "Lao, People's Democratic Republic", "Latvia", "Lebanon", "Lesotho", "Liberia", "Libyan Arab Jamahiriya", "Liechtenstein", "Lithuania",
            "Luxembourg", "Macau", "Macedonia, The Former Yugoslav Republic of", "Madagascar", "Malawi", "Malaysia", "Maldives", "Mali", "Malta",
            "Marshall Islands", "Martinique", "Mauritania", "Mauritius", "Mayotte", "Mexico", "Micronesia, Federated States of", "Moldova, Republic of",
            "Monaco", "Mongolia", "Montserrat", "Morocco", "Mozambique", "Myanmar", "Namibia", "Nauru", "Nepal", "Netherlands", "Netherlands Antilles",
            "New Caledonia", "New Zealand", "Nicaragua", "Niger", "Nigeria", "Niue", "Norfolk Island", "Northern Mariana Islands", "Norway", "Oman",
            "Pakistan", "Palau", "Panama", "Papua New Guinea", "Paraguay", "Peru", "Philippines", "Pitcairn", "Poland", "Portugal", "Puerto Rico",
            "Qatar", "Reunion", "Romania", "Russian Federation", "Rwanda", "Saint Kitts and Nevis", "Saint Lucia", "Saint Vincent and the Grenadines",
            "Samoa", "San Marino", "Sao Tome and Principe", "Saudi Arabia", "Senegal", "Seychelles", "Sierra Leone", "Singapore", "Slovakia (Slovak Republic)",
            "Slovenia", "Solomon Islands", "Somalia", "South Africa", "South Georgia and the South Sandwich Islands", "Spain", "Sri Lanka", "St. Helena",
            "St. Pierre and Miquelon", "Sudan", "Suriname", "Svalbard and Jan Mayen Islands", "Swaziland", "Sweden", "Switzerland", "Syrian Arab Republic",
            "Taiwan, Province of China", "Tajikistan", "Tanzania, United Republic of", "Thailand", "Togo", "Tokelau", "Tonga", "Trinidad and Tobago",
            "Tunisia", "Turkey", "Turkmenistan", "Turks and Caicos Islands", "Tuvalu", "Uganda", "Ukraine", "United Arab Emirates", "United Kingdom",
            "United States", "United States Minor Outlying Islands", "Uruguay", "Uzbekistan", "Vanuatu", "Venezuela", "Vietnam", "Virgin Islands (British)",
            "Virgin Islands (U.S.)", "Wallis and Futuna Islands", "Western Sahara", "Yemen", "Yugoslavia", "Zambia", "Zimbabwe"
        );



        $username = $this->getUser();

        $count = 10;
        foreach( $elements as $value ) {

            $entity = new Countries();
            $this->setDefaultList($entity,$count,$username,null);
            $entity->setName( trim((string)$value) );

            $em->persist($entity);
            $em->flush();

            $count = $count + 10;

        } //foreach

        return round($count/10);

    }


    public function generateCountryList() {

        $em = $this->getDoctrine()->getManager();

        $user = $this->getUser();

        $inputFileName = __DIR__ . '/../Util/Cities.xlsx';

        try {
            $inputFileType = \PhpOffice\PhpSpreadsheet\IOFactory::identify($inputFileName);
            $objReader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader($inputFileType);
            $objPHPExcel = $objReader->load($inputFileName);
        } catch(\Exception $e) {
            die('Error loading file "'.pathinfo($inputFileName,PATHINFO_BASENAME).'": '.$e->getMessage());
        }

        $sheet = $objPHPExcel->getSheet(0);
        $highestRow = $sheet->getHighestRow();
        $highestColumn = $sheet->getHighestColumn();

        $countryCount = 1;
        $cityCount = 1;

        $batchSize = 20;

        //for each row in excel
        for( $row = 2; $row <= $highestRow; $row++ ) {

            //  Read a row of data into an array
            $rowData = $sheet->rangeToArray(
                'A' . $row . ':' . $highestColumn . $row,
                NULL,
                TRUE,
                FALSE
            );

            //echo $row.": ";
            //var_dump($rowData);
            //echo "<br>";

            //$countryPersisted = false;
            //$cityPersisted = false;

            $country = trim((string)$rowData[0][0]);
            $city = trim((string)$rowData[0][1]);

            //country
            //echo "country=".$country."<br>";
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:Countries'] by [Countries::class]
            $countryDb = $em->getRepository(Countries::class)->findOneByName($country);

            if( !$countryDb ) {
                //echo "add country=".$country."<br>";

                $newCountry = new Countries();
                $this->setDefaultList($newCountry,$countryCount,$user,$country);


                $em->persist($newCountry);
                $em->flush();
                //$countryPersisted = true;

                $countryCount = $countryCount + 10;
            }

            //city
            //echo "city=".$city."<br>";
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:CityList'] by [CityList::class]
            $cityDb = $em->getRepository(CityList::class)->findOneByName($city);

            if( !$cityDb ) {
                //echo "add city=".$city."<br>";

                $newCity = new CityList();
                $this->setDefaultList($newCity,$cityCount,$user,$city);

                $em->persist($newCity);
                //$cityPersisted = true;

                $cityCount = $cityCount + 10;
            }

            //if( $countryPersisted || $cityPersisted ) {
                if( ($row % $batchSize) === 0 ) {
                    $em->flush();
                    //$em->clear(); // Detaches all objects from Doctrine!
                }
            //}

        } //for loop

        $em->flush(); //Persist objects that did not make up an entire batch
        $em->clear();

        $countArr = array();
        $countArr['country'] = round($countryCount/10);
        $countArr['city'] = round($cityCount/10);

        return $countArr;
    }


    public function generateLanguages() {

        $logger = $this->container->get('logger');
        $em = $this->getDoctrine()->getManager();

        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:LanguageList'] by [LanguageList::class]
        $entities = $em->getRepository(LanguageList::class)->findAll();
        if( count($entities) > 0 ) {
            $logger->notice("Exit generateLanguages. LanguageList already generated. count=".count($entities));
            return -1;
        }

        $logger->notice("Start generateLanguages. before getLanguageNames");

        \Locale::setDefault('en');
        $elements = array();
        $language = Languages::getName('en');
        $elements['en'] = $language;
        $language = Languages::getName('en_US');
        $elements['en_US'] = $language;

        //print_r($elements);
        //exit();
        $logger->notice("Start generateLanguages. count=".count($entities));

        $username = $this->getUser();

        $count = 10;
        foreach( $elements as $abbreviation=>$name ) {

            //$entity = $em->getRepository('AppUserdirectoryBundle:LanguageList')->findOneByAbbreviation($abbreviation);

            //testing
//            if( $entity ) {
//                $em->remove($entity);
//                $em->flush();
//                echo "remove entity with ".$abbreviation."<br>";
//            }

            $entity = null;

            if( !$entity ) {
                $entity = new LanguageList();
                $this->setDefaultList($entity,$count,$username,null);
                $entity->setName( trim((string)$name) );
                $entity->setAbbreviation( trim((string)$abbreviation) );
                $logger->notice("Created LanguageList: name=".$name.", abbreviation=".$abbreviation);
            }

            //\Locale::setDefault($abbreviation);
            //$languageNativeName = $languageBundle->getLanguageName($abbreviation);
            //uppercase the first letter
            //$languageNativeName = mb_convert_case(mb_strtolower($languageNativeName), MB_CASE_TITLE, "UTF-8");
//            if( $abbreviation == 'ru' ) {
//                echo $abbreviation."=(".$languageNativeName.")<br>";
//                exit();
//            }
            //$entity->setNativeName($languageNativeName);

            $em->persist($entity);
            $em->flush();

            //$logger->notice("set languageNativeName=".$languageNativeName);

            $count = $count + 10;

        } //foreach
        //exit('1');

        \Locale::setDefault('en');

        $logger->notice("Finished generateLanguages. count=".$count/10);

        return round($count/10);
    }


    public function generateLocales() {

        $logger = $this->container->get('logger');
        $em = $this->getDoctrine()->getManager();

        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:LocaleList'] by [LocaleList::class]
        $entities = $em->getRepository(LocaleList::class)->findAll();
        if( count($entities) > 0 ) {
            $logger->notice("Exit generateLocales. LocaleList already generated. count=".count($entities));
            return -1;
        }

        $logger->notice("Start generateLocales. before getLocaleNames");

        \Locale::setDefault('en');
        $elements = array();
        $locale = Locales::getName('en');
        $elements['en'] = $locale;
        $locale = Locales::getName('en_US');
        $elements['en_US'] = $locale;

        //print_r($elements);
        //exit();
        $logger->notice("Start generateLocales. after getLocaleNames. count=".count($elements));

        $username = $this->getUser();

        $count = 10;
        foreach( $elements as $locale=>$description ) {

//            $entities = $em->getRepository('AppUserdirectoryBundle:LocaleList')->findByName($locale);
//            foreach( $entities as $entity ) {
//                $em->remove($entity);
//                $em->flush();
//                //echo "remove entity with ".$locale."<br>";
//            }

            $entity = null;
            if( !$entity ) {
                $entity = new LocaleList();
                $this->setDefaultList($entity,$count,$username,null);
            }

            $entity->setName( trim((string)$locale) );
            $entity->setDescription( trim((string)$description) );

            $em->persist($entity);
            $em->flush();

            $count = $count + 10;

        } //foreach
        //exit('1');

        return round($count/10);
    }


    public function generateBoardSpecialties() {

        $em = $this->getDoctrine()->getManager();
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:BoardCertifiedSpecialties'] by [BoardCertifiedSpecialties::class]
        $entities = $em->getRepository(BoardCertifiedSpecialties::class)->findAll();

        if( $entities ) {
            return -1;
        }

        $elements = array(
            'Anatomic Pathology',
            'Clinical Pathology',
            'Hematopathology',
            'Cytopathology',
            'Molecular Genetic Pathology',
            'Immunopathology',
            'Pediatric Pathology',
            'Neuropathology',
            'Dermatopathology',
            'Medical Microbiology',
            'Blood Banking/Transfusion Medicine',
            'Forensic Pathology',
            'Chemical Pathology'
        );


        $username = $this->getUser();

        $count = 10;
        foreach( $elements as $value ) {

            $entity = new BoardCertifiedSpecialties();
            $this->setDefaultList($entity,$count,$username,null);
            $entity->setName( trim((string)$value) );

            $em->persist($entity);
            $em->flush();

            $count = $count + 10;

        } //foreach

        return round($count/10);

    }


    public function generateSourceSystems() {

        $em = $this->getDoctrine()->getManager();

        //$entities = $em->getRepository('AppUserdirectoryBundle:SourceSystemList')->findAll();
        //if( $entities ) {
        //    return -1;
        //}

        $elements = array(
            'ORDER Employee Directory',
            'ORDER Deidentifier',
            'ORDER Call Log Book',
            'ORDER Critical Result Notifications',
            'ORDER Fellowship Applications',
            'ORDER Vacation Request',
            'ORDER Translational Research',
            'ORDER Scan Order', //used as default in getDefaultSourceSystem //'Scan Order',
            'WCM Epic Practice Management',
            'WCM Epic Ambulatory EMR',
            'NYH Paper Requisition',
            'Written or oral referral',
            'PACS on C.MED.CORNELL.EDU',
            'Indica HALO'
        );


        $username = $this->getUser();

        $count = 10;
        foreach( $elements as $value ) {

        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:SourceSystemList'] by [SourceSystemList::class]
            if( $em->getRepository(SourceSystemList::class)->findOneByName($value) ) {
                continue;
            }

            $entity = new SourceSystemList();
            $this->setDefaultList($entity,null,$username,$value);

            $em->persist($entity);
            $em->flush();

            $count = $count + 10;

        } //foreach

        return round($count/10);

    }

    public function generateViewModeList() {

        $em = $this->getDoctrine()->getManager();

        $elements = array(
            'Clear',
            'Empowered'
        );

        $username = $this->getUser();

        $count = 10;
        foreach( $elements as $value ) {

        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:ViewModeList'] by [ViewModeList::class]
            if( $em->getRepository(ViewModeList::class)->findOneByName($value) ) {
                continue;
            }

            $entity = new ViewModeList();
            $this->setDefaultList($entity,null,$username,$value);

            $em->persist($entity);
            $em->flush();

            $count = $count + 10;

        } //foreach

        return round($count/10);

    }

    public function generateDocumentTypes() {

        $em = $this->getDoctrine()->getManager();

//        $entities = $em->getRepository('AppUserdirectoryBundle:DocumentTypeList')->findAll();
//        if( $entities ) {
//            return -1;
//        }

        $elements = array(

            //'Generic Document',
            'Avatar Image',
            'Comment Document',
            'Autopsy Image',
            'Gross Image',
            'Part Document',
            'Block Image',
            'Microscopic Image',
            'Whole Slide Image',
            'Requisition Form Image',
            'Outside Report Reference Representation',
            'Grant Document',
            'Medical License Document',
            'Certificate of Qualification Document',

            'Fellowship Application Spreadsheet',
            'Fellowship Application Document',
            'Complete Fellowship Application PDF',
            'Old Complete Fellowship Application PDF',

            'Fellowship Photo',
            'Fellowship CV',
            'Fellowship Cover Letter',
            'Fellowship USMLE Scores',
            'Fellowship Interview Itinerary',
            'Fellowship Recommendation',
            'Fellowship Reprimand',
            'Fellowship Legal Suit',

            'Invoice Logo',
            'Invoice PDF'

        );


        $username = $this->getUser();

        $count = 10;
        foreach( $elements as $value ) {

        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:DocumentTypeList'] by [DocumentTypeList::class]
            if( $em->getRepository(DocumentTypeList::class)->findOneByName($value) ) {
                continue;
            }

            $entity = new DocumentTypeList();
            $this->setDefaultList($entity,$count,$username,$value);

            $em->persist($entity);
            $em->flush();

            $count = $count + 10;

        } //foreach

        return round($count/10);

    }


    public function generateLinkTypes() {

        $em = $this->getDoctrine()->getManager();
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:LinkTypeList'] by [LinkTypeList::class]
        $entities = $em->getRepository(LinkTypeList::class)->findAll();

        if( $entities ) {
            return -1;
        }

        $elements = array(
            'Thumbnail',
            'Label',
            'Via WebScope',
            'Via ImageScope',
            'Download'
        );

        $username = $this->getUser();

        $count = 10;
        foreach( $elements as $value ) {

            $entity = new LinkTypeList();
            $this->setDefaultList($entity,$count,$username,$value);

            $em->persist($entity);
            $em->flush();

            $count = $count + 10;

        } //foreach

        return round($count/10);

    }

    public function generateEmploymentTypes() {

        $em = $this->getDoctrine()->getManager();

        //$entities = $em->getRepository('AppUserdirectoryBundle:EmploymentType')->findAll();
        //if( $entities ) {
        //    return -1;
        //}

        $elements = array(
            'Full Time',
            'Part Time',
            'Pathology Fellowship Applicant',
            'Pathology Residency Applicant' //should we filter users similarly as employmentType.name != 'Pathology Fellowship Applicant'?
        );

        $username = $this->getUser();

        $count = 10;
        foreach( $elements as $value ) {

        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:EmploymentType'] by [EmploymentType::class]
            if( $em->getRepository(EmploymentType::class)->findOneByName($value) ) {
                continue;
            }

            $entity = new EmploymentType();
            $this->setDefaultList($entity,$count,$username,$value);

            $em->persist($entity);
            $em->flush();

            $count = $count + 10;

        } //foreach

        return round($count/10);

    }


    public function generateTerminationTypes() {

        $em = $this->getDoctrine()->getManager();
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:EmploymentTerminationType'] by [EmploymentTerminationType::class]
        $entities = $em->getRepository(EmploymentTerminationType::class)->findAll();

        if( $entities ) {
            return -1;
        }

        $elements = array(
            'Graduated',
            'Quit',
            'Retired',
            'Fired'
        );

        $username = $this->getUser();

        $count = 10;
        foreach( $elements as $value ) {

            $entity = new EmploymentTerminationType();
            $this->setDefaultList($entity,$count,$username,null);
            $entity->setName( trim((string)$value) );

            $em->persist($entity);
            $em->flush();

            $count = $count + 10;

        } //foreach

        return round($count/10);

    }

    public function generateEventTypeList() {
        $em = $this->getDoctrine()->getManager();

        $elements = array(
            'Login Page Visit',
            'Successful Login',
            'Bad Credentials',
            'Unsuccessful Login Attempt',
            'Unapproved User Login Attempt',
            'Banned User Login Attempt',
            'New user record added',
            'User record updated',

            'Import of Fellowship Applications Spreadsheet',
            'Import of Fellowship Application data to DB',
            'Fellowship Application Created',
            'Fellowship Application Creation Failed',
            'Fellowship Application Updated',
            'Fellowship Application Rating Invitation Emails Resent',
            'Fellowship Application Page Viewed',

            'Complete Fellowship Application PDF Downloaded',
            'Fellowship Interview Itinerary Downloaded',
            'Fellowship CV Downloaded',
            'Fellowship Cover Letter Downloaded',
            'Fellowship USMLE Scores Downloaded',
            'Fellowship Recommendation Downloaded',

            'Complete Fellowship Application PDF Uploaded',
            'Fellowship Interview Itinerary Uploaded',
            'Fellowship CV Uploaded',
            'Fellowship Cover Letter Uploaded',
            'Fellowship USMLE Scores Uploaded',
            'Fellowship Recommendation Uploaded',

            'Fellowship Application Status changed to Active',
            'Fellowship Application Status changed to Archived',
            'Fellowship Application Status changed to Hidden',
            'Fellowship Application Status changed to Complete',
            'Fellowship Application Status changed to Interviewee',
            'Fellowship Application Status changed to Rejected',
            'Fellowship Application Status changed to On Hold',
            'Fellowship Interview Evaluation Updated',
            "Deleted Fellowship Application From Google Drive",
            "Failed Deleted Fellowship Application From Google Drive",

            'Role Permission Updated',

            'Generate Accession Deidentifier ID',
            'Search by Deidentifier ID conducted',

            'New Call Log Book Entry Submitted',

            'Warning',
            'Error'
        );

        $username = $this->getUser();

        $count = 10;
        foreach( $elements as $value ) {
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:EventTypeList'] by [EventTypeList::class]
            if( $em->getRepository(EventTypeList::class)->findOneByName($value) ) {
                continue;
            }
            //echo 'AppUserdirectoryBundle:EventTypeList' . " name=" . $value . "<br>";
            $entity = new EventTypeList();
            $this->setDefaultList($entity,$count,$username,null);
            $entity->setName( trim((string)$value) );

            $em->persist($entity);
            $em->flush();

            $count = $count + 10;
            //echo 'EOF AppUserdirectoryBundle:EventTypeList' . " name=" . $value . "<br>";
        } //foreach

        return round($count/10);
    }


    public function generateIdentifierTypeList() {
        $em = $this->getDoctrine()->getManager();

//        $entities = $em->getRepository('AppUserdirectoryBundle:IdentifierTypeList')->findAll();
//        if( $entities ) {
//            return -1;
//        }

        $elements = array(
            'Employee Identification Number (EIN)',
            'National Provider Identifier (NPI)',
            'MRN',
            'Local User',
            'Active Directory (LDAP)',
            'NYP CWID',
            'WCM CWID'
        );

        $username = $this->getUser();

        $count = 10;
        foreach( $elements as $value ) {

        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:IdentifierTypeList'] by [IdentifierTypeList::class]
            if( $em->getRepository(IdentifierTypeList::class)->findOneByName($value) ) {
                continue;
            }

            $entity = new IdentifierTypeList();
            $this->setDefaultList($entity,$count,$username,null);
            $entity->setName( trim((string)$value) );

            $em->persist($entity);
            $em->flush();

            $count = $count + 10;

        } //foreach

        return round($count/10);
    }


    public function generateFellowshipTypeList() {
        $em = $this->getDoctrine()->getManager();
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:FellowshipTypeList'] by [FellowshipTypeList::class]
        $entities = $em->getRepository(FellowshipTypeList::class)->findAll();

        if( $entities ) {
            return -1;
        }

        $elements = array(
            "Blood banking/Transfusion medicine",
            "Chemistry",
            "Dermatopathology",
            "Forensic pathology",
            "Genitourinary pathology",
            "Hematopathology",
            "Molecular genetic pathology",
            "Pathology informatics",
            "Pulmonary/Mediastinal pathology",
            "Soft tissue/Bone pathology",
            "Breast pathology",
            "Cytopathology",
            "Diagnostic immunology",
            "Gastrointestinal pathology",
            "Gynecologic pathology",
            "Medical microbiology",
            "Neuropathology",
            "Pediatric pathology",
            "Renal pathology",
            "Surgical/Oncologic pathology"
        );

        $username = $this->getUser();

        $count = 10;
        foreach( $elements as $value ) {

            $entity = new FellowshipTypeList();
            $this->setDefaultList($entity,$count,$username,null);
            $entity->setName( trim((string)$value) );

            $em->persist($entity);
            $em->flush();

            $count = $count + 10;

        } //foreach

        return round($count/10);
    }

    public function generateResidencyTrackList() {
        $username = $this->getUser();
        $em = $this->getDoctrine()->getManager();

//        $entities = $em->getRepository('AppUserdirectoryBundle:ResidencyTrackList')->findAll();
//        if( $entities ) {
//            return -1;
//        }

//        A- For AP/CP, “Expected Duration (in years): 4
//        B- For AP/EXP, “Expected Duration (in years): 4
//        C- For CP/EXP, “Expected Duration (in years): 4
//        D- For AP, “Expected Duration (in years): 3
//        E- For CP, “Expected Duration (in years): 3

        $elements = array(
            'AP'=>3,
            'CP'=>3,
            'AP/CP'=>4,
            'AP/EXP'=>4,
            'CP/EXP'=>4
        );

        $count = 10;
        foreach( $elements as $value=>$duration ) {

        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:ResidencyTrackList'] by [ResidencyTrackList::class]
            $entity = $em->getRepository(ResidencyTrackList::class)->findOneByName($value);
            if( $entity ) {

                //update $entity->setDuration($duration);
//                if( !$entity->getDuration() ) {
//                    $entity->setDuration($duration);
//                    $em->flush();
//                }

                continue;
            }

            $entity = new ResidencyTrackList();
            $this->setDefaultList($entity,$count,$username,null);
            $entity->setName( trim((string)$value) );

            $entity->setDuration($duration);

            $em->persist($entity);
            $em->flush();

            $count = $count + 10;

        } //foreach

        return round($count/10);
    }


    public function generateMedicalTitleList() {
        $em = $this->getDoctrine()->getManager();

        $elements = array(
            'Assistant Attending Pathologist',
            'Associate Attending Pathologist',
            'Attending Pathologist',
            'Resident',
            'Fellow'
        );

        $username = $this->getUser();

        $count = 10;
        foreach( $elements as $value ) {

            $value = trim((string)$value);

        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:MedicalTitleList'] by [MedicalTitleList::class]
            if( $em->getRepository(MedicalTitleList::class)->findOneByName($value) ) {
                continue;
            }

            $entity = new MedicalTitleList();
            $this->setDefaultList($entity,$count,$username,$value);

            $em->persist($entity);
            $em->flush();

            $count = $count + 10;

        } //foreach

        return round($count/10);
    }

    public function generateMedicalSpecialties() {
        $em = $this->getDoctrine()->getManager();

        $elements = array(
            'Autopsy Pathology',
            'Breast Pathology',
            'Cardiopulmonary Pathology',
            'Clinical Microbiology',
            'Cytogenetics',
            'Cytopathology',
            'Dermatopathology',
            'Gastrointestinal and Liver Pathology',
            'Genitourinary Pathology',
            'Gynecologic Pathology',
            'Head and Neck Pathology',
            'Hematopathology',
            'Immunopathology',
            'Molecular and Genomic Pathology',
            'Molecular Hematopathology',
            'Neuropathology',
            'Pathology Informatics',
            'Pediatric Pathology',
            'Perinatal and Obstetric Pathology',
            'Renal Pathology',
            'Surgical Pathology',
            'Transfusion Medicine'
        );

        $username = $this->getUser();

        $count = 10;
        foreach( $elements as $value ) {

            $value = trim((string)$value);

        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:MedicalSpecialties'] by [MedicalSpecialties::class]
            if( $em->getRepository(MedicalSpecialties::class)->findOneByName($value) ) {
                continue;
            }

            $entity = new MedicalSpecialties();
            $this->setDefaultList($entity,$count,$username,$value);

            $em->persist($entity);
            $em->flush();

            $count = $count + 10;

        } //foreach

        return round($count/10);
    }

    public function generateLocationTypeList() {
        $em = $this->getDoctrine()->getManager();
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:LocationTypeList'] by [LocationTypeList::class]
        $entities = $em->getRepository(LocationTypeList::class)->findAll();

        if( $entities ) {
            return -1;
        }

        $elements = array(
            'Employee Office',
            'Employee Desk',
            'Employee Cubicle',
            'Employee Suite',
            'Employee Mailbox',
            'Employee Home',
            'Conference Room',
            'Sign Out Room',
            'Clinical Laboratory',
            'Research Laboratory',
            'Medical Office',
            'Inpatient Room',
            "Patient's Primary Contact Information",
            "Patient's Contact Information",
            'Pick Up',
            'Accessioning',
            'Storage',
            'Filing Room',
            'Off Site Slide Storage',
            'Present Address',
            'Permanent Address',
            'Work Address',
            'Encounter Location',
            'WCM Pathology Department Common Location For Phone Directory'
        );

        $username = $this->getUser();

        $count = 10;
        foreach( $elements as $value ) {

            $entity = new LocationTypeList();
            $this->setDefaultList($entity,$count,$username,null);
            $entity->setName( trim((string)$value) );

            $em->persist($entity);
            $em->flush();

            $count = $count + 10;

        } //foreach

        return round($count/10);
    }




    public function generateEquipmentType() {

        $username = $this->getUser();

        $em = $this->getDoctrine()->getManager();

//        $entities = $em->getRepository('AppUserdirectoryBundle:EquipmentType')->findAll();
//
//        if( $entities ) {
//            return -1;
//        }

        $types = array(
            'Whole Slide Scanner',
            'Microtome',
            'Centrifuge',
            'Slide Stainer',
            'Microscope Camera',
            'Autopsy Camera',
            'Gross Image Camera',
            'Tissue Processor',
            'Xray Machine',
            'Block Imaging Camera',
            'Requisition Form Scanner'
        );

        $count = 10;
        foreach( $types as $type ) {

        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:EquipmentType'] by [EquipmentType::class]
            if( $em->getRepository(EquipmentType::class)->findOneByName($type) ) {
                continue;
            }

            $listEntity = new EquipmentType();
            $this->setDefaultList($listEntity,$count,$username,$type);

            $em->persist($listEntity);
            $em->flush();

            $count = $count + 10;
        }

        return round($count/10);
    }

    public function generateEquipment() {

        $username = $this->getUser();

        $em = $this->getDoctrine()->getManager();

//        $entities = $em->getRepository('AppUserdirectoryBundle:Equipment')->findAll();
//
//        if( $entities ) {
//            return -1;
//        }

        $types = array(
            'Aperio ScanScope AT' => 'Whole Slide Scanner',
            'Lumix LX5' => 'Autopsy Camera',
            'Canon 60D' => 'Autopsy Camera',
            'Milestone MacroPath D' => 'Gross Image Camera',
            'Block Processing Device' => 'Tissue Processor',
            'Faxitron' => 'Xray Machine',
            'Block Image Device' => 'Block Imaging Camera',
            'Microtome Device' => 'Microtome',
            'Microtome Device' => 'Centrifuge',
            'Slide Stainer Device' => 'Slide Stainer',
            'Olympus Camera' => 'Microscope Camera',
            'Generic Desktop Scanner' => 'Requisition Form Scanner'
        );

        $count = 10;
        foreach( $types as $device => $keytype ) {

        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:Equipment'] by [Equipment::class]
            if( $em->getRepository(Equipment::class)->findOneByName($device) ) {
                continue;
            }

        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:EquipmentType'] by [EquipmentType::class]
            $keytype = $em->getRepository(EquipmentType::class)->findOneByName($keytype);

            if( !$keytype ) {
                //continue;
                //exit('equipment keytype is null');
                throw new \Exception( 'Equipment keytype is null, name="' . $keytype .'"' );
            }

            $listEntity = new Equipment();
            $this->setDefaultList($listEntity,$count,$username,$device);

            $keytype->addEquipment($listEntity);

            $em->persist($listEntity);
            $em->flush();

            $count = $count + 10;
        }

        return round($count/10);
    }


    public function generateLocationPrivacy() {

        $username = $this->getUser();

        $em = $this->getDoctrine()->getManager();
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:LocationPrivacyList'] by [LocationPrivacyList::class]
        $entities = $em->getRepository(LocationPrivacyList::class)->findAll();

        if( $entities ) {
            return -1;
        }

        $types = array(
            "Administration; Those 'on call' can see these phone numbers & email",
            "Administration can see and edit this contact information",
            "Any approved user of Employee Directory can see these phone numbers and email",
            "Any approved user of Employee Directory can see this contact information if logged in",
            "Anyone can see this contact information"
        );

        $count = 10;
        foreach( $types as $type ) {

            $listEntity = new LocationPrivacyList();
            $this->setDefaultList($listEntity,$count,$username,$type);

            $em->persist($listEntity);
            $em->flush();

            $count = $count + 10;
        }

        return round($count/10);
    }


    public function generateResLabs_OLD() {

        $username = $this->getUser();

        $em = $this->getDoctrine()->getManager();
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:ResearchLab'] by [ResearchLab::class]
        $entities = $em->getRepository(ResearchLab::class)->findAll();

        if( $entities ) {
            return -1;
        }

        $types = array(
            "Laboratory of Prostate Cancer Research Group",
            "Proteolytic Oncogenesis",
            "Macrophages and Tissue Remodeling",
            "Antiphospholipid Syndrome",
            "Laboratory of Stem Cell Aging and Cancer",
            "Molecular Pathology",
            "Skeletal Biology",
            "Viral Oncogenesis",
            "Vascular Biology",
            "Cell Cycle",
            "Molecular Gynecologic Pathology",
            "Cancer Biology",
            "Cell Metabolism",
            "Oncogenic Transcription Factors in Prostate Cancer",
        );

        $count = 10;
        foreach( $types as $type ) {

            $listEntity = new ResearchLab();
            $this->setDefaultList($listEntity,$count,$username,$type);

            $em->persist($listEntity);
            $em->flush();

            $count = $count + 10;
        }

        return round($count/10);
    }
    //http://cornellpathology.com/research/laboratories
    //add new reseacrh lab institutions with "Research Lab" OrganizationalGroupType under "WCM-Pathology"
    //add manually already existing lab's institutions:
    //"Skeletal Biology", "Dr. Inghirami's Lab", "Wayne Tam Lab"
    public function generateResLabs() {

        $username = $this->getUser();
        $userSecUtil = $this->container->get('user_security_utility');

        $em = $this->getDoctrine()->getManager();

        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:OrganizationalGroupType'] by [OrganizationalGroupType::class]
        $researchLabOrgGroup = $em->getRepository(OrganizationalGroupType::class)->findOneByName("Research Lab");
        if( !$researchLabOrgGroup ) {
            exit('No OrganizationalGroupType: "Research Lab"');
        }
        //echo "researchLabOrgGroup=".$researchLabOrgGroup."<br>";

        $mapper = array(
            'prefix' => 'App',
            'bundleName' => 'UserdirectoryBundle',
            'className' => 'Institution',
            'fullClassName' => "App\\UserdirectoryBundle\\Entity\\Institution",
            'entityNamespace' => "App\\UserdirectoryBundle\\Entity"
        );

        //AutoAssignInstitution
        $pathology = $userSecUtil->getAutoAssignInstitution();

        if( !$pathology ) {
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:Institution'] by [Institution::class]
            $wcmc = $em->getRepository(Institution::class)->findOneByAbbreviation("WCM");
            if( !$wcmc ) {
                exit('generateResLabs: No Institution: "WCM"');
            }
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:Institution'] by [Institution::class]
            $pathology = $em->getRepository(Institution::class)->findByChildnameAndParent(
                "Pathology and Laboratory Medicine",
                $wcmc,
                $mapper
            );
        }

        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:InstitutionType'] by [InstitutionType::class]
        $medicalType = $em->getRepository(InstitutionType::class)->findOneByName('Medical');

        $labs = array(
            "Prostate Cancer Research Group" => "Laboratory of Prostate Cancer Research Group",
            "Viral Oncogenesis" => "Viral Oncogenesis",
            "Vascular Biology" => "Center for Vascular Biology",
            "Cell Cycle" => "Cell Cycle",
            "Epigenetics and Genomic Integrity" => "Laboratory of Epigenetics and Genomic Integrity",
            "Laboratory of Stem Cell Aging and Cancer" => "Laboratory of Stem Cell Aging and Cancer",
            "Molecular Pathology" => "Molecular Pathology",
            "Oncogenic Transcription Factors in Prostate Cancer" => "Oncogenic Transcription Factors in Prostate Cancer", //Oncogenic Transcription Factors in Prostate Cancer; Prostate Cancer Research Group

            "DNA Repair and Molecular Immunology" => "DNA Repair and Molecular Immunology Laboratory",
            "Proteolytic Oncogenesis" => "Proteolytic Oncogenesis",
            "Macrophages and Tissue Remodeling" => "Macrophages and Tissue Remodeling", //Macrophages and Tissue Remodeling; Vascular Biology
            "Antiphospholipid Syndrome" => "Antiphospholipid Syndrome (APS) Research Laboratory", //Dr. Rand's Lab
            "Molecular Gynecologic Pathology" => "Molecular Gynecologic Pathology",
            "Regulation of Bone Mass Laboratory" => "Regulation of Bone Mass Laboratory",
            "Cell Metabolism" => "Laboratory of Cell Metabolism",
            "Cancer Biology" => "Cancer Biology",

            "Skeletal Biology" => "Skeletal Biology",
            "Dr. Inghirami's Lab" => "Dr. Inghirami's Lab",
            "Wayne Tam Lab" => "Wayne Tam Lab"

//            "Viral Oncogenesis",
//            "Center for Vascular Biology",
//
//
//            "Proteolytic Oncogenesis",
//            "Macrophages and Tissue Remodeling",
//            "Antiphospholipid Syndrome",
//            "Laboratory of Stem Cell Aging and Cancer",
//            "Molecular Pathology",
//            "Skeletal Biology",
//            "Viral Oncogenesis",
//            "Vascular Biology",
//            "Cell Cycle",
//            "Molecular Gynecologic Pathology",
//            "Cancer Biology",
//            "Cell Metabolism",
//            "Oncogenic Transcription Factors in Prostate Cancer",
        );

        $count = 10;
        foreach( $labs as $labName => $pageName ) {

            //1) create a new Research Institution with "Research Lab" OrganizationalGroupType under "WCM-Pathology"
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:Institution'] by [Institution::class]
            $researchInstitution = $em->getRepository(Institution::class)->findByChildnameAndParent(
                $labName,
                $pathology,
                $mapper
            );
            if( $researchInstitution ) {
                continue;
            }
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:Institution'] by [Institution::class]
            $researchInstitution = $em->getRepository(Institution::class)->findByChildnameAndParent(
                $pageName,
                $pathology,
                $mapper
            );
            if( $researchInstitution ) {
                continue;
            }

            $researchInstitution = new Institution();
            $this->setDefaultList($researchInstitution,1,$username,$labName);
            $researchInstitution->setOrganizationalGroupType($researchLabOrgGroup);
            $researchInstitution->addType($medicalType);
            $pathology->addChild($researchInstitution);
            //echo "researchInstitution=".$researchInstitution."(".$researchInstitution->getOrganizationalGroupType().")<br>";
            //exit('1');

            $em->persist($researchInstitution);
            $em->flush();
            //echo "added new Research Institution=".$labName."<br>";


            //2) create Research Lab object
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:ResearchLab'] by [ResearchLab::class]
            $researchLab = $em->getRepository(ResearchLab::class)->findOneByName($labName);
            if( $researchLab ) {
                if( !$researchLab->getInstitution() ) {
                    $researchLab->setInstitution($researchInstitution);
                    $em->persist($researchLab);
                    $em->flush();
                }
                continue;
            }
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:ResearchLab'] by [ResearchLab::class]
            $researchLab = $em->getRepository(ResearchLab::class)->findOneByName($pageName);
            if( $researchLab ) {
                if( !$researchLab->getInstitution() ) {
                    $researchLab->setInstitution($researchInstitution);
                    $em->persist($researchLab);
                    $em->flush();
                }
                continue;
            }

            $researchLab = new ResearchLab();
            $this->setDefaultList($researchLab, $count, $username, $labName);

            $researchLab->setInstitution($researchInstitution);

            $em->persist($researchLab);
            $em->flush();
            //echo "added new ResearchLab=".$labName."<br>";


            $count = $count + 10;

            //exit("finished adding ".$labName)."<br>";
        }

        return round($count/10);
    }


    public function generateBuildings() {

        $username = $this->getUser();

        $em = $this->getDoctrine()->getManager();
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:BuildingList'] by [BuildingList::class]
        $entities = $em->getRepository(BuildingList::class)->findAll();

        if( $entities ) {
            return -1;
        }

        $buildings = array(
            array('name'=>"Weill Cornell Medical College", 'street1'=>'1300 York Ave','abbr'=>'C','inst'=>'WCM'),
            array('name'=>"Belfer Research Building", 'street1'=>'413 East 69th Street','abbr'=>null,'inst'=>'WCM'),
            array('name'=>"Helmsley Medical Tower", 'street1'=>'1320 York Ave','abbr'=>null,'inst'=>'WCM'),
            array('name'=>"Weill Greenberg Center",'street1'=>'1305 York Ave','abbr'=>null,'inst'=>'WCM'),
            array('name'=>"Olin Hall",'street1'=>'445 East 69th Street','abbr'=>null,'inst'=>'WCM'),
            array('name'=>"",'street1'=>'575 Lexington Ave','abbr'=>null,'inst'=>'WCM'),                        //WCM - 575 Lexington Ave
            array('name'=>"",'street1'=>'402 East 67th Street','abbr'=>null,'inst'=>'WCM'),                     //WCM - 402 East 67th Street
            array('name'=>"",'street1'=>'425 East 61st Street','abbr'=>null,'inst'=>'WCM'),                     //WCM - 425 East 61st Street
            array('name'=>"Starr Pavilion",'street1'=>'520 East 70th Street','abbr'=>'ST','inst'=>'NYP'),
            array('name'=>"J Corridor",'street1'=>'525 East 68th Street','abbr'=>'J','inst'=>'NYP'),
            array('name'=>"L Corridor",'street1'=>'525 East 68th Street','abbr'=>'L','inst'=>'NYP'),
            array('name'=>"K Wing",'street1'=>'525 East 68th Street','abbr'=>'K','inst'=>'NYP'),
            array('name'=>"F Wing, Floors 2-9",'street1'=>'525 East 68th Street','abbr'=>'F','inst'=>'NYP'),
            array('name'=>"Baker Pavilion - F Wing",'street1'=>'525 East 68th Street','abbr'=>'B','inst'=>'NYP'),
            array('name'=>"Payson Pavilion",'street1'=>'525 East 68th Street','abbr'=>'P','inst'=>'NYP'),
            array('name'=>"Whitney Pavilion",'street1'=>'525 East 68th Street','abbr'=>'W','inst'=>'NYP'),
            array('name'=>"M Wing",'street1'=>'530 East 70th Street','abbr'=>'M','inst'=>'NYP'),
            array('name'=>"N Wing",'street1'=>'530 East 70th Street','abbr'=>'N','inst'=>'NYP'),
            array('name'=>"Weill Cornell Medical Assoc. Eastside",'street1'=>'201 East 80th Street','abbr'=>null,'inst'=>'NYP'),
            array('name'=>"Weill Cornell Medical Assoc. Westside",'street1'=>'12 West 72nd Street','abbr'=>null,'inst'=>'NYP'),
            array('name'=>"Iris Cantor Women’s Health Center",'street1'=>'425 East 61st Street','abbr'=>null,'inst'=>'NYP'),
            array('name'=>"Weill Cornell Imaging at NewYork-Presbyterian",'street1'=>'416 East 55th Street','abbr'=>null,'inst'=>'NYP'),    //NYP - Weill Cornell Imaging at NewYork-Presbyterian / 416 East 55th Street
            array('name'=>"Weill Cornell Imaging at NewYork-Presbyterian, 9th Floor",'street1'=>'425 East 61st Street','abbr'=>null,'inst'=>'NYP'),    //NYP - Weill Cornell Imaging at NewYork-Presbyterian / 425 East 61st Street, 9th Floor
            array('name'=>"Weill Cornell Imaging at NewYork-Presbyterian, lobby level",'street1'=>'520 East 70th Street','abbr'=>null,'inst'=>'NYP'),    //NYP - Weill Cornell Imaging at NewYork-Presbyterian / 520 East 70th Street, lobby level
            array('name'=>"Weill Cornell Imaging at NewYork-Presbyterian, 3rd Floor",'street1'=>'1305 York Avenue','abbr'=>null,'inst'=>'NYP'),    //NYP - Weill Cornell Imaging at NewYork-Presbyterian / 1305 York Avenue, 3rd Floor
            array('name'=>"Oxford Medical Offices",'street1'=>'428 East 72nd Street','abbr'=>null,'inst'=>'NYP'),
            array('name'=>"Stich Building",'street1'=>'1315 York Ave','abbr'=>null,'inst'=>'NYP'),
            array('name'=>"Kips Bay Medical Offices",'street1'=>'411 East 69th Street','abbr'=>null,'inst'=>'NYP'),
            array('name'=>"Phipps House Medical Offices",'street1'=>'449 East 68th Street','abbr'=>null,'inst'=>'NYP'),
            array('name'=>"",'street1'=>'333 East 38th Street','abbr'=>null,'inst'=>'NYP'),  //NYP - 333 East 38th Street
            array('name'=>"Greenberg Pavilion",'street1'=>'525 East 68th Street','abbr'=>null,'inst'=>'NYP'),
        );

        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:CityList'] by [CityList::class]
        $city = $em->getRepository(CityList::class)->findOneByName("New York");
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:States'] by [States::class]
        $state = $em->getRepository(States::class)->findOneByName("New York");
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:Countries'] by [Countries::class]
        $country = $em->getRepository(Countries::class)->findOneByName("United States");
        if( !$country ) {
            //exit('ERROR: country null');
            $errorMsg = 'Failed to create Building List. Country is not found by name=' . 'United States.'.
            'Please populate Country and City Lists first or create a country with name "United States"';
            //throw new \Exception( $errorMsg );
            return $errorMsg;
        }

        $count = 10;
        foreach( $buildings as $building ) {

            $name = $building['name'];

            if( !$name ) {
                continue;
            }

        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:BuildingList'] by [BuildingList::class]
            if( $em->getRepository(BuildingList::class)->findOneByName($name) ) {
                continue;
            }

            $listEntity = new BuildingList();
            $this->setDefaultList($listEntity,$count,$username,$name);

            //add buildings attributes
            $street1 = $building['street1'];
            $buildingAbbr = $building['abbr'];

            $geo = new GeoLocation();
            $geo->setStreet1($street1);
            $geo->setCity($city);
            $geo->setState($state);
            $geo->setCountry($country);

            $listEntity->setGeoLocation($geo);
            $listEntity->setAbbreviation($buildingAbbr);

            $instAbbr = $building['inst'];
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:Institution'] by [Institution::class]
            $inst = $em->getRepository(Institution::class)->findOneByAbbreviation($instAbbr);
            if( $inst ) {
                $listEntity->addInstitution($inst);
            }

            //echo $count.": name=".$name.", street1=".$street1."<br>";

            $em->persist($listEntity);
            $em->flush();

            $count = $count + 10;
        }

        return round($count/10);
    }


    public function generateLocations() {

        $userSecUtil = $this->container->get('user_security_utility');
        $username = $this->getUser();

        $em = $this->getDoctrine()->getManager();

//        $entities = $em->getRepository('AppUserdirectoryBundle:Location')->findAll();
//        if( $entities ) {
//            return -1;
//        }

        $locations = array(
            "Surgical Pathology Filing Room" => array('street1'=>'520 East 70th Street','phone'=>'222-0059','room'=>'ST-1012','inst'=>'NYP'),
        );

        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:CityList'] by [CityList::class]
        $city = $em->getRepository(CityList::class)->findOneByName("New York");
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:States'] by [States::class]
        $state = $em->getRepository(States::class)->findOneByName("New York");
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:Countries'] by [Countries::class]
        $country = $em->getRepository(Countries::class)->findOneByName("United States");
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:LocationTypeList'] by [LocationTypeList::class]
        $locationType = $em->getRepository(LocationTypeList::class)->findOneByName("Filing Room");
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:LocationPrivacyList'] by [LocationPrivacyList::class]
        $locationPrivacy = $em->getRepository(LocationPrivacyList::class)->findOneByName("Anyone can see this contact information");
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:BuildingList'] by [BuildingList::class]
        $building = $em->getRepository(BuildingList::class)->findOneByName("Starr Pavilion");

        if( !$country ) {
            $errorMsg = 'Failed to create Building List. Country is not found by name=' . 'United States.'.
                'Please populate Country and City Lists first or create a country with name "United States"';
            //throw new \Exception( $errorMsg );
            return $errorMsg;
        }

        $count = 10;
        foreach( $locations as $location => $attr ) {

        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:Location'] by [Location::class]
            if( $em->getRepository(Location::class)->findOneByName($location) ) {
                continue;
            }

            $listEntity = new Location();
            $this->setDefaultList($listEntity,$count,$username,$location);

            //add buildings attributes
            $street1 = $attr['street1'];
            $phone = $attr['phone'];
            $room = $attr['room'];
            $instAbbr = $attr['inst'];

        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:Institution'] by [Institution::class]
            $inst = $em->getRepository(Institution::class)->findOneByAbbreviation($instAbbr);
            if( $inst ) {
                $listEntity->setInstitution($inst);
            }

            $geo = new GeoLocation();
            $geo->setStreet1($street1);
            $geo->setCity($city);
            $geo->setState($state);
            $geo->setCountry($country);

            $listEntity->setGeoLocation($geo);
            $listEntity->setPhone($phone);
            $listEntity->setRoom($room);
            $listEntity->setStatus($listEntity::STATUS_VERIFIED);
            $listEntity->addLocationType($locationType);
            $listEntity->setPrivacy($locationPrivacy);
            $listEntity->setBuilding($building);

            //set room object
            //$userUtil = new UserUtil();
            //$roomObj = $userUtil->getObjectByNameTransformer($room,$username,'RoomList',$em);
            //getObjectByNameTransformer($user,"Message",'UserdirectoryBundle','EventObjectTypeList');
            $roomObj = $userSecUtil->getObjectByNameTransformer($username,$room,'UserdirectoryBundle','RoomList');
            $listEntity->setRoom($roomObj);

            $em->persist($listEntity);
            $em->flush();

            //exit('RoomList='.$roomObj);
            $count = $count + 10;
        }

        $countNew = $this->generateLocationsFromExcel();
        $count = $count + $countNew;

        return round($count/10);
    }

    public function generateLocationsFromExcel( $count=null ) {
        $username = $this->getUser();
        $em = $this->getDoctrine()->getManager();

//        $entities = $em->getRepository('AppUserdirectoryBundle:Location')->findAll();
//        if( count($entities) > 3 ) {
//            return -1;
//        }

        $userSecUtil = $this->container->get('user_security_utility');

        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:LocationPrivacyList'] by [LocationPrivacyList::class]
        $locationPrivacy = $em->getRepository(LocationPrivacyList::class)->findOneByName("Anyone can see this contact information");
        if( !$locationPrivacy ) {
            exit("Location privacy is not found by name "."'Anyone can see this contact information'");
        }

        $inputFileName = __DIR__ . '/../Util/Encounter Locations (Import Columns A through O)-2 - Fixed-Ready For Import.xlsx';

        //TODO: check if file exists before opening (for all excel files)
        try {
            $inputFileType = \PhpOffice\PhpSpreadsheet\IOFactory::identify($inputFileName);
            $objReader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader($inputFileType);
            $objPHPExcel = $objReader->load($inputFileName);
        } catch( \Exception $e ) {
            die('Error loading file "'.pathinfo($inputFileName,PATHINFO_BASENAME).'": '.$e->getMessage());
        }

        $sheet = $objPHPExcel->getSheet(0);
        $highestRow = $sheet->getHighestRow();
        $highestColumn = $sheet->getHighestColumn();

        for( $row = 2; $row <= $highestRow; $row++ ) {

            //Read a row of data into an array
            $rowData = $sheet->rangeToArray('A' . $row . ':' . $highestColumn . $row,
                NULL,
                TRUE,
                FALSE);

            $name = trim((string)$rowData[0][0]);
            $locationTypeName = trim((string)$rowData[0][1]);
            $locationPhone = trim((string)$rowData[0][2]);
            $locationRoom = trim((string)$rowData[0][3]);
            $locationSuite = trim((string)$rowData[0][4]);
            $locationFloor = trim((string)$rowData[0][5]);
            $locationFloorSide = trim((string)$rowData[0][6]);
            $locationBuildingName = trim((string)$rowData[0][7]);

//            print "<pre>";
//            print_r($rowData);
//            print "</pre>";
//            print "</pre>";
            //echo "name=$name, locationTypeName=$locationTypeName, locationPhone=$locationPhone, locationRoom=$locationRoom, locationSuite=$locationSuite, locationFloor=$locationFloor, locationFloorSide=$locationFloorSide, locationBuildingName=$locationBuildingName <br>";
            //exit();

            if( !$name ) {
                exit("Location name is empty");
            }

        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:Location'] by [Location::class]
            $listEntity = $em->getRepository(Location::class)->findOneByName($name);
            if( $listEntity ) {
                continue;
            }

            $listEntity = new Location();
            $this->setDefaultList($listEntity,null,$username,$name);

            $listEntity->setStatus($listEntity::STATUS_VERIFIED);
            $listEntity->setPrivacy($locationPrivacy);

            if( $locationTypeName ) {
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:LocationTypeList'] by [LocationTypeList::class]
                $locationType = $em->getRepository(LocationTypeList::class)->findOneByName($locationTypeName);
                if (!$locationType) {
                    exit("No location found by name " . $locationTypeName);
                }
                $listEntity->addLocationType($locationType);
            }

            if( $locationPhone ) {
                $listEntity->setPhone($locationPhone);
            }

            if( $locationRoom ) {
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:RoomList'] by [RoomList::class]
                $room = $em->getRepository(RoomList::class)->findOneByName($locationRoom);
                if (!$room) {
                    exit("No room found by name " . $locationRoom);
                }
                $listEntity->setRoom($room);
            }

            if( $locationSuite ) {
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:SuiteList'] by [SuiteList::class]
                $suite = $em->getRepository(SuiteList::class)->findOneByName($locationSuite);
                if (!$suite) {
                    exit("No suite found by name " . $locationSuite);
                }
                $listEntity->setSuite($suite);
            }

            if( $locationFloor ) {
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:FloorList'] by [FloorList::class]
                $floor = $em->getRepository(FloorList::class)->findOneByName($locationFloor);
                if( !$floor ) {
                    //exit("No floor found by name " . $locationFloor);
                    $floor = $userSecUtil->getObjectByNameTransformer($username,$locationFloor,"UserdirectoryBundle","FloorList");
                    $em->persist($floor);
                    //$em->flush($floor);
                    $em->flush();
                }
                //$floor = $em->getRepository('AppUserdirectoryBundle:FloorList')->findOneByName($locationFloor);
                if( !$floor ) {
                    exit("No floor found by name " . $locationFloor);
                }
                $listEntity->setFloor($floor);
            }

            if( $locationFloorSide ) {
                $listEntity->setFloorSide($locationFloorSide);
            }

            if( $locationBuildingName ) {
                if( $locationBuildingName == "Greenberg Pavillion" ) {
                    $locationBuildingName = "Greenberg Pavilion";
                }
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:BuildingList'] by [BuildingList::class]
                $building = $em->getRepository(BuildingList::class)->findOneByName($locationBuildingName);
                if (!$building) {
                    continue;
                    exit("No building type found by name " . $locationBuildingName);
                }
                $listEntity->setBuilding($building);
            }

            //exit('exit generateObjectTypeActions');
            $em->persist($listEntity);
            $em->flush();

            $count = $count + 10;
            //exit('1');
        }

        $em->clear();

        return round($count/10);
    }

    public function generateTestUsers() {

        $testusers = array(
            "testplatformadministrator" => array("ROLE_PLATFORM_ADMIN"),
            "testdeputyplatformadministrator" => array("ROLE_PLATFORM_DEPUTY_ADMIN"),

            "testscanadministrator" => array("ROLE_SCANORDER_ADMIN"),
            "testscanprocessor" => array("ROLE_SCANORDER_PROCESSOR"), //TODO: check auth logic: it ask for access request for scan site
            "testscansubmitter" => array("ROLE_SCANORDER_SUBMITTER"),

            "testuseradministrator" => array("ROLE_SCANORDER_SUBMITTER","ROLE_USERDIRECTORY_ADMIN"),
            "testusereditor" => array("ROLE_SCANORDER_SUBMITTER","ROLE_USERDIRECTORY_EDITOR"),  //TODO: check auth logic: it ask for access request for directory site
            "testuserobserver" => array("ROLE_SCANORDER_SUBMITTER","ROLE_USERDIRECTORY_OBSERVER")
        );

        $userSecUtil = $this->container->get('user_security_utility');
        $userGenerator = $this->container->get('user_generator');
        //$userUtil = new UserUtil();
        $userUtil = $this->container->get('user_utility');
        $em = $this->getDoctrine()->getManager();
        $systemuser = $userUtil->createSystemUser(null,null);  //$this->getUser();
        $default_time_zone = $this->getParameter('default_time_zone');
        //echo "systemuser ".$systemuser.", id=".$systemuser->getId()."<br>";

//        $em = $this->getDoctrine()->getManager();
//        $entities = $em->getRepository('AppUserdirectoryBundle:ResearchLab')->findAll();
//
//        if( $entities ) {
//            return -1;
//        }

        $count = 1;
        foreach( $testusers as $testusername => $roles ) {

            $user = new User();
            $userkeytype = $userSecUtil->getUsernameType("local"); //"external"
            $user->setKeytype($userkeytype);
            $user->setPrimaryPublicUserId($testusername);

            //echo "username=".$user->getPrimaryPublicUserId()."<br>";
            $found_user = $em->getRepository(User::class)->findOneByPrimaryPublicUserId( $user->getPrimaryPublicUserId() );
            if( $found_user ) {
                //add scanorder Roles
                foreach( $roles as $role ) {
                    $found_user->addRole($role);
                }
                $em->flush();
                continue;
            }

            //set unique username
            $usernameUnique = $user->createUniqueUsername();
            $user->setUsername($usernameUnique);
            $user->setUsernameCanonical($usernameUnique);

            //$user->setEmail($email);
            //$user->setEmailCanonical($email);
            $user->setFirstName($testusername);
            $user->setLastName($testusername);
            $user->setDisplayName($testusername." ".$testusername);
            $user->setPassword("");
            $user->setCreatedby('system');
            $user->getPreferences()->setTimezone($default_time_zone);

            //add default locations
            $user = $userGenerator->addDefaultLocations($user,$systemuser);

            //phone, fax, office are stored in Location object
            $mainLocation = $user->getMainLocation();
            //$mainLocation->setPhone($phone);
            //$mainLocation->setFax($fax);

            //title is stored in Administrative Title
            $administrativeTitle = new AdministrativeTitle($systemuser);
            $user->addAdministrativeTitle($administrativeTitle);

            //add scanorder Roles
            foreach( $roles as $role ) {
                $user->addRole($role);
            }

            $user->setEnabled(true);
            //$user->setLocked(false);
            //$user->setExpired(false);

            //record user log create
            $event = "User ".$user." has been created by ".$systemuser."<br>";
            $userSecUtil->createUserEditEvent($this->getParameter('employees.sitename'),$event,$systemuser,$user,null,'New user record added');

            $em->persist($user);
            $em->flush();

            $userId = $user->getId();

            //**************** create PerSiteSettings for this user **************//
            $userSettings = $user->getPerSiteSettings();
            if( !$userSettings ) {
                //get user from DB to avoid An exception occurred while executing 'INSERT INTO scan_perSiteSettings ... Key (fosuser)=(8) already exists
                $user = $em->getRepository(User::class)->find($userId);
                //echo "create new PerSiteSettings for user " . $user . ", id=" . $user->getId() . "<br>";
                $perSiteSettings = new PerSiteSettings($systemuser);
                $perSiteSettings->setUser($user);

//                $params = $em->getRepository(SiteParameters::class)->findAll();
//                if (count($params) != 1) {
//                    throw new \Exception('Must have only one parameter object. Found ' . count($params) . ' object(s)');
//                }
//                $param = $params[0];
//                $institution = $param->getAutoAssignInstitution();
                $institution = $userSecUtil->getAutoAssignInstitution();

                $perSiteSettings->addPermittedInstitutionalPHIScope($institution);
                $em->persist($perSiteSettings);
                $em->flush();
            }
            //**************** EOF create PerSiteSettings for this user **************//

            $count++;
        }

        return $count;
    }



    public function generateCompletionReasons() {

        $username = $this->getUser();

        $em = $this->getDoctrine()->getManager();
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:CompletionReasonList'] by [CompletionReasonList::class]
        $entities = $em->getRepository(CompletionReasonList::class)->findAll();

        if( $entities ) {
            return -1;
        }

        $types = array(
            "Graduated",
            "Transferred"
        );

        $count = 10;
        foreach( $types as $type ) {

            $listEntity = new CompletionReasonList();
            $this->setDefaultList($listEntity,$count,$username,$type);

            $em->persist($listEntity);
            $em->flush();

            $count = $count + 10;
        }

        return round($count/10);
    }

    public function generateTrainingDegrees() {

        $username = $this->getUser();

        $em = $this->getDoctrine()->getManager();
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:TrainingDegreeList'] by [TrainingDegreeList::class]
        $entities = $em->getRepository(TrainingDegreeList::class)->findAll();

        if( $entities ) {
            return -1;
        }

        $types = array(
            "MD", "DO", "PhD", "JD", "MBA", "MHA", "MA", "MS", "BS", "BA", "MBBS", "MDCM", "MBChB", "BMed",
            "Dr.Med", "Dr.MuD", "Cand.med", "DMD", "BDent", "DDS", "BDS", "BDSc", "BChD", "CD", "Cand.Odont.",
            "Dr.Med.Dent.", "DNP", "DNAP", "DNS", "DNSc", "OTD", "DrOT", "MSOT", "MOT", "OD", "B.Optom", "BEd",
            "BME", "BSE", "BSocSc", "BSc", "BPharm", "BScPhm", "PharmB", "MPharm", "PharmD", "DPT", "DPhysio",
            "MPT", "BSPT", "MPAS", "MPS", "DPM", "DP", "BPod", "PodB", "PodD", "MPA", "MPS", "PsyD",
            "ClinPsyD", "EdS", "BSN", "DVM", "VMD", "BVS", "BVSc", "BVMS", "MLIS", "MLS", "MSLIS", "BSW"
        );

        $count = 10;
        foreach( $types as $type ) {

            $listEntity = new TrainingDegreeList();
            $this->setDefaultList($listEntity,$count,$username,$type);

            $listEntity->setAbbreviation($type);

            //set "MBBS" and "DO" to be synonyms of "MD" in the List Manager for Degrees
            if( $type == "DO" || $type == "MBBS" ) {
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:TrainingDegreeList'] by [TrainingDegreeList::class]
                $mdOriginal = $em->getRepository(TrainingDegreeList::class)->findOneByName("MD");
                $listEntity->setOriginal($mdOriginal);
            }

            $em->persist($listEntity);
            $em->flush();

            $count = $count + 10;
        }

        return round($count/10);
    }

    public function generateResidencySpecialties() {
        $username = $this->getUser();
        $em = $this->getDoctrine()->getManager();

        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:Institution'] by [Institution::class]
        $wcmc = $em->getRepository(Institution::class)->findOneByAbbreviation("WCM");
        if( !$wcmc ) {
            exit('generateDefaultOrgGroupSiteParameters: No Institution: "WCM"');
        }

        $mapper = array(
            'prefix' => 'App',
            'bundleName' => 'UserdirectoryBundle',
            'className' => 'Institution',
            'fullClassName' => "App\\UserdirectoryBundle\\Entity\\Institution",
            'entityNamespace' => "App\\UserdirectoryBundle\\Entity"
        );
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:Institution'] by [Institution::class]
        $pathologyInstitution = $em->getRepository(Institution::class)->findByChildnameAndParent(
            "Pathology and Laboratory Medicine",
            $wcmc,
            $mapper
        );

        $residencies = array(
            "AP",
            "CP",
            "AP/CP",
            'Pathology AP/EXP',
            'Pathology CP/EXP'
        );

        $count = 10;
        foreach( $residencies as $residency ) {

        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:ResidencySpecialty'] by [ResidencySpecialty::class]
            $listEntity = $em->getRepository(ResidencySpecialty::class)->findOneByName($residency);
            if( $listEntity ) {
                if( !$listEntity->getInstitution() ) {
                    $listEntity->setInstitution($pathologyInstitution);
                }
                continue;
            }

            $listEntity = new ResidencySpecialty();
            $this->setDefaultList($listEntity,$count,$username,$residency);

            $listEntity->setInstitution($pathologyInstitution);
            //$listEntity->setBoardCertificateAvailable(true);

            $em->persist($listEntity);
            $em->flush();

            $count = $count + 10;
        }

        return round($count/10);
    }
    //NOT USED
    public function generateSpreadsheetResidencySpecialties() {

        $username = $this->getUser();

        $em = $this->getDoctrine()->getManager();

        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:ResidencySpecialty'] by [ResidencySpecialty::class]
        $entities = $em->getRepository(ResidencySpecialty::class)->findAll();
        if( count($entities) > 0 ) {
            return -1;
        }

        $inputFileName = __DIR__ . '/../Util/SpecialtiesResidenciesFellowshipsCertified.xlsx';

        try {
            $inputFileType = \PhpOffice\PhpSpreadsheet\IOFactory::identify($inputFileName);
            $objReader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader($inputFileType);
            $objPHPExcel = $objReader->load($inputFileName);
        } catch(\Exception $e) {
            die('Error loading file "'.pathinfo($inputFileName,PATHINFO_BASENAME).'": '.$e->getMessage());
        }

        $sheet = $objPHPExcel->getSheet(0);
        $highestRow = $sheet->getHighestRow();
        $highestColumn = $sheet->getHighestColumn();

        $lastResidencySpecialtyEntity = null;

        $count = 10;
        $subcount = 1;

        //for each row in excel
        for ($row = 2; $row <= $highestRow; $row++){
            //  Read a row of data into an array
            $rowData = $sheet->rangeToArray('A' . $row . ':' . $highestColumn . $row,
                NULL,
                TRUE,
                FALSE);

            //echo $row.": ";
            //var_dump($rowData);
            //echo "<br>";

            //ResidencySpecialty	FellowshipSubspecialty	BoardCertificationAvailable
            $residencySpecialty = $rowData[0][0];
            $fellowshipSubspecialty = $rowData[0][1];
            $boardCertificationAvailable = $rowData[0][2];
            //echo "residencySpecialty=".$residencySpecialty."<br>";
            //echo "fellowshipSubspecialty=".$fellowshipSubspecialty."<br>";
            //echo "boardCertificationAvailable=".$boardCertificationAvailable."<br>";

            $residencySpecialtyEntity = null;

            if( $residencySpecialty ) {

                $residencySpecialty = trim((string)$residencySpecialty);
                //echo "residencySpecialty=".$residencySpecialty."<br>";

        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:ResidencySpecialty'] by [ResidencySpecialty::class]
                $residencySpecialtyEntity = $em->getRepository(ResidencySpecialty::class)->findOneByName($residencySpecialty."");

                //if( $em->getRepository('AppUserdirectoryBundle:ResidencySpecialty')->findOneByName($residencySpecialty."") ) {
                //    continue;
                //}

                if( !$residencySpecialtyEntity ) {
                    $residencySpecialtyEntity = new ResidencySpecialty();
                    $this->setDefaultList($residencySpecialtyEntity,$count,$username,$residencySpecialty);
                }

                if( $boardCertificationAvailable && $boardCertificationAvailable == "Yes" ) {
                    $residencySpecialtyEntity->setBoardCertificateAvailable(true);
                }

                $em->persist($residencySpecialtyEntity);
                $em->flush();

                $lastResidencySpecialtyEntity = $residencySpecialtyEntity;

                $count = $count + 10;
            }

            if( $fellowshipSubspecialty ) {

                $fellowshipSubspecialty = trim((string)$fellowshipSubspecialty);
                //echo "fellowshipSubspecialty=".$fellowshipSubspecialty."<br>";
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:FellowshipSubspecialty'] by [FellowshipSubspecialty::class]
                $fellowshipSubspecialtyEntity = $em->getRepository(FellowshipSubspecialty::class)->findOneByName($fellowshipSubspecialty."");

                //if( $fellowshipSubspecialtyEntity ) {
                //    continue;
                //}

                if( !$fellowshipSubspecialtyEntity ) {
                    $fellowshipSubspecialtyEntity = new FellowshipSubspecialty();
                    $this->setDefaultList($fellowshipSubspecialtyEntity,$subcount,$username,$fellowshipSubspecialty);
                }


                if( $boardCertificationAvailable && $boardCertificationAvailable == "Yes" ) {
                    $fellowshipSubspecialtyEntity->setBoardCertificateAvailable(true);
                }

                if( $lastResidencySpecialtyEntity ) {
                    $lastResidencySpecialtyEntity->addChild($fellowshipSubspecialtyEntity);
                }

                $em->persist($lastResidencySpecialtyEntity);
                $em->persist($fellowshipSubspecialtyEntity);
                $em->flush();

                $subcount = $subcount + 10;
            }

        }

        $em->clear();

        return round($count/10);
    }

    //Create default Fellowship Subspecialty 'Clinical Informatics'
    public function generateDefaultFellowshipSubspecialties() {

        $username = $this->getUser();

        $em = $this->getDoctrine()->getManager();

        //ResidencySpecialty: Anatomic Pathology and Clinical Pathology
        // |
        //FellowshipSubspecialty: Clinical Informatics (Institution: Weill Cornell Medical College => Pathology and Laboratory Medicine)

        $count = 0;

        $residencySpecialty = "Anatomic Pathology and Clinical Pathology";
        //$residencySpecialty = "AP/CP";
        $order = 0;
        $newResidencySpecialtyEntity = false;
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:ResidencySpecialty'] by [ResidencySpecialty::class]
        $residencySpecialtyEntity = $em->getRepository(ResidencySpecialty::class)->findOneByName($residencySpecialty."");
        if( !$residencySpecialtyEntity ) {
            $residencySpecialtyEntity = new ResidencySpecialty();
            $this->setDefaultList($residencySpecialtyEntity,$order,$username,$residencySpecialty);
            $residencySpecialtyEntity->setBoardCertificateAvailable(true);
            $newResidencySpecialtyEntity = true;
            $em->persist($residencySpecialtyEntity);
            $count++;
        }

        $fellowshipSubspecialty = "Clinical Informatics";
        $order = 0;
        $newFellowshipSubspecialtyEntity = false;
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:FellowshipSubspecialty'] by [FellowshipSubspecialty::class]
        $fellowshipSubspecialtyEntity = $em->getRepository(FellowshipSubspecialty::class)->findOneByName($fellowshipSubspecialty."");
        if( !$fellowshipSubspecialtyEntity ) {
            $fellowshipSubspecialtyEntity = new FellowshipSubspecialty();
            $this->setDefaultList($fellowshipSubspecialtyEntity,$order,$username,$fellowshipSubspecialty);
            $fellowshipSubspecialtyEntity->setBoardCertificateAvailable(true);
            $newFellowshipSubspecialtyEntity = true;
            $em->persist($fellowshipSubspecialtyEntity);
            $count++;
        }

        if( $newResidencySpecialtyEntity || $newFellowshipSubspecialtyEntity ) {
            $residencySpecialtyEntity->addChild($fellowshipSubspecialtyEntity);
            $em->flush();
        }

        return $count;
    }

    public function generateHonorTrainings() {

        $username = $this->getUser();

        $em = $this->getDoctrine()->getManager();
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:HonorTrainingList'] by [HonorTrainingList::class]
        $entities = $em->getRepository(HonorTrainingList::class)->findAll();

        if( $entities ) {
            return -1;
        }

        $types = array(
            "Magna Cum Laude", "Summa Cum Laude", "Cum Laude", "AOA Member"
        );

        $count = 10;
        foreach( $types as $type ) {

            $listEntity = new HonorTrainingList();
            $this->setDefaultList($listEntity,$count,$username,$type);

            $em->persist($listEntity);
            $em->flush();

            $count = $count + 10;
        }

        return round($count/10);
    }

    //Professional Fellowship Title
    public function generateFellowshipTitles() {

        $username = $this->getUser();

        $em = $this->getDoctrine()->getManager();
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:FellowshipTitleList'] by [FellowshipTitleList::class]
        $entities = $em->getRepository(FellowshipTitleList::class)->findAll();

        if( $entities ) {
            return -1;
        }

        $types = array(
            "F.C.A.P." => "Fellow of the College of American Pathologists",
            "F.A.A.E.M." => "Fellow of the American Academy of Emergency Medicine",
            "F.A.A.F.P." => "Fellow of the American Academy of Family Physicians",
            "F.A.C.C." => "Fellow of the American College of Cardiologists",
            "F.A.C.E." => "Fellow of the American College of Endocrinology",
            "F.A.C.E.P." => "Fellow of the American College of Emergency Physicians",
            "F.A.C.G." => "Fellow of the American College of Gastroenterology",
            "F.A.C.F.A.S." => "Fellow of the American College of Foot and Ankle Surgeons",
            "F.A.C.O.G." => "Fellow of the American College of Obstetrics and Gynecologists",
            "F.A.C.O.S." => "Fellow of the American College of Osteopathic Surgeons",
            "F.A.C.P." => "Fellow of the American College of Physicians",
            "F.A.C.C.P." => "Fellow of the American College of Chest Physicians",
            "F.A.C.S." => "Fellow of the American College of Surgeons",
            "F.A.S.P.S." => "Fellow of the American Society of Podiatric Surgeons",
            "F.H.M." => "Fellow in Hospital Medicine",
            "F.I.C.S." => "Fellow of the International College of Surgeons",
            "F.S.C.A.I." => "Fellow of the Society for Cardiovascular Angiography and Interventions",
            "F.S.T.S." => "Fellow of the Society of Thoracic Surgeons"
        );

        $count = 10;
        foreach( $types as $abbr => $name ) {

            $listEntity = new FellowshipTitleList();
            $this->setDefaultList($listEntity,$count,$username,$name);

            $listEntity->setAbbreviation($abbr);

            $em->persist($listEntity);
            $em->flush();

            $count = $count + 10;
        }

        return round($count/10);
    }


    public function generatesourceOrganizations() {

        $username = $this->getUser();

        $em = $this->getDoctrine()->getManager();
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:SourceOrganization'] by [SourceOrganization::class]
        $entities = $em->getRepository(SourceOrganization::class)->findAll();

        if( $entities ) {
            return -1;
        }

        $types = array(
            "National Institutes of Health" => "NIH"
        );

        $count = 10;
        foreach( $types as $name => $abbreviation ) {

            $listEntity = new SourceOrganization();
            $this->setDefaultList($listEntity,$count,$username,$name);

            $listEntity->setAbbreviation($abbreviation);

            $em->persist($listEntity);
            $em->flush();

            $count = $count + 10;
        }

        return round($count/10);
    }


    public function generateImportances() {

        $username = $this->getUser();

        $em = $this->getDoctrine()->getManager();
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:ImportanceList'] by [ImportanceList::class]
        $entities = $em->getRepository(ImportanceList::class)->findAll();

        if( $entities ) {
            return -1;
        }

        $types = array(
            "#1 - First most important",
            "#2 - Second most important",
            "#3 - Third most important",
            "#4 - Fourth most important",
            "#5 - Fifth most important",
            "Other"
        );

        $count = 10;
        foreach( $types as $name ) {

            $listEntity = new ImportanceList();
            $this->setDefaultList($listEntity,$count,$username,$name);

            $em->persist($listEntity);
            $em->flush();

            $count = $count + 10;
        }

        return round($count/10);
    }


    public function generateAuthorshipRoles() {

        $username = $this->getUser();

        $em = $this->getDoctrine()->getManager();
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:AuthorshipRoles'] by [AuthorshipRoles::class]
        $entities = $em->getRepository(AuthorshipRoles::class)->findAll();

        if( $entities ) {
            return -1;
        }

        $types = array(
            "Editor",
            "Chapter Author"
        );

        $count = 10;
        foreach( $types as $name ) {

            $listEntity = new AuthorshipRoles();
            $this->setDefaultList($listEntity,$count,$username,$name);

            $em->persist($listEntity);
            $em->flush();

            $count = $count + 10;
        }

        return round($count/10);
    }


//    public function generateTitlePositionTypes() {
//
//        $username = $this->getUser();
//
//        $em = $this->getDoctrine()->getManager();
//        $entities = $em->getRepository('AppUserdirectoryBundle:TitlePositionType')->findAll();
//
//        if( $entities ) {
//            return -1;
//        }
//
////        $types = array(
////            'Head',
////            'Manager',
////            'Primary Contact',
////            'Transcriptionist',
////        );
//
//        $types = array(
//            'Head of Institution',
//            'Head of Department',
//            'Head of Division',
//            'Head of Service',
//            'Manager of Institution',
//            'Manager of Department',
//            'Manager of Division',
//            'Manager of Service',
//            'Primary Contact of Institution',
//            'Primary Contact of Department',
//            'Primary Contact of Division',
//            'Primary Contact of Service',
//            'Transcriptionist for the Institution',
//            'Transcriptionist for the Department',
//            'Transcriptionist for the Division',
//            'Transcriptionist for the Service'
//        );
//
//        $count = 10;
//        foreach( $types as $name ) {
//
//            $listEntity = new TitlePositionType();
//            $this->setDefaultList($listEntity,$count,$username,$name);
//
//            $em->persist($listEntity);
//            $em->flush();
//
//            $count = $count + 10;
//        }
//
//        return round($count/10);
//    }


    public function generateSex() {

        $username = $this->getUser();

        $em = $this->getDoctrine()->getManager();
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:SexList'] by [SexList::class]
        $entities = $em->getRepository(SexList::class)->findAll();

        if( $entities ) {
            return -1;
        }

        //http://nces.ed.gov/ipeds/reic/definitions.asp
        $types = array(
            'Female' => 'F',
            'Male' => 'M',
            'Unspecified' => 'U'
        );

        $count = 10;
        foreach( $types as $type => $abbreviation ) {

            $listEntity = new SexList();
            $this->setDefaultList($listEntity,$count,$username,$type);
            $listEntity->setAbbreviation($abbreviation);

            $em->persist($listEntity);
            $em->flush();

            $count = $count + 10;
        }

        return round($count/10);
    }


    public function generatePositionTypeList() {

        $username = $this->getUser();

        $em = $this->getDoctrine()->getManager();
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:PositionTypeList'] by [PositionTypeList::class]
        $entities = $em->getRepository(PositionTypeList::class)->findAll();

        if( $entities ) {
            return -1;
        }

        //https://bitbucket.org/weillcornellpathology/scanorder/issue/438/change-institution-division-department
        $types = array(
            'Head of Institution',
            'Head of Department',
            'Head of Division',
            'Head of Service',

            'Manager of Institution',
            'Manager of Department',
            'Manager of Division',
            'Manager of Service',

            'Primary Contact of Institution',
            'Primary Contact of Department',
            'Primary Contact of Division',
            'Primary Contact of Service',

            'Transcriptionist of Institution',
            'Transcriptionist of Department',
            'Transcriptionist of Division',
            'Transcriptionist of Service',
        );

        $count = 10;
        foreach( $types as $type ) {

            $listEntity = new PositionTypeList();
            $this->setDefaultList($listEntity,$count,$username,$type);

            $em->persist($listEntity);
            $em->flush();

            $count = $count + 10;
        }

        return round($count/10);
    }

    public function generateCommentGroupType() {

        $em = $this->getDoctrine()->getManager();
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:CommentGroupType'] by [CommentGroupType::class]
        $entities = $em->getRepository(CommentGroupType::class)->findAll();

        if( $entities ) {
            return -1;
        }

        $elements = array(
            'Comment Category' => 0,
            'Comment Name' => 1,
        );

        $username = $this->getUser();

        $count = 10;
        foreach( $elements as $name=>$level ) {

            $entity = new CommentGroupType();
            $this->setDefaultList($entity,$count,$username,$name);

            $entity->setLevel($level);

            $em->persist($entity);
            $em->flush();

            $count = $count + 10;

        } //foreach

        return round($count/10);

    }

    public function generateSpotPurpose() {

        $em = $this->getDoctrine()->getManager();
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:SpotPurpose'] by [SpotPurpose::class]
        $entities = $em->getRepository(SpotPurpose::class)->findAll();

        if( $entities ) {
            return -1;
        }

        $elements = array(
            'Initial Patient Encounter - Address Entry',
//            'Encounter',
//            'Procedure',
//            'Accession',
//            'Part',
//            'Block',
//            'Slide'
        );

        $username = $this->getUser();

        $count = 10;
        foreach( $elements as $name ) {

            $entity = new SpotPurpose();
            $this->setDefaultList($entity,$count,$username,$name);

            $em->persist($entity);
            $em->flush();

            $count = $count + 10;

        } //foreach

        return round($count/10);

    }

    public function generateMedicalLicenseStatus() {

        $em = $this->getDoctrine()->getManager();
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:MedicalLicenseStatus'] by [MedicalLicenseStatus::class]
        $entities = $em->getRepository(MedicalLicenseStatus::class)->findAll();

        if( $entities ) {
            return -1;
        }

        $elements = array(
            'Yes',
            'No',
        );

        $username = $this->getUser();

        $count = 10;
        foreach( $elements as $name ) {

            $entity = new MedicalLicenseStatus();
            $this->setDefaultList($entity,$count,$username,$name);

            $em->persist($entity);
            $em->flush();

            $count = $count + 10;

        } //foreach

        return round($count/10);

    }


    public function generateCertifyingBoardOrganization() {

        $em = $this->getDoctrine()->getManager();
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:CertifyingBoardOrganization'] by [CertifyingBoardOrganization::class]
        $entities = $em->getRepository(CertifyingBoardOrganization::class)->findAll();

        if( $entities ) {
            return -1;
        }

        $elements = array(
            'American Board of Pathology',
        );

        $username = $this->getUser();

        $count = 10;
        foreach( $elements as $name ) {

            $entity = new CertifyingBoardOrganization();
            $this->setDefaultList($entity,$count,$username,$name);

            $em->persist($entity);
            $em->flush();

            $count = $count + 10;

        } //foreach

        return round($count/10);

    }

    public function generateTrainingTypeList() {

        $em = $this->getDoctrine()->getManager();

        //$entities = $em->getRepository('AppUserdirectoryBundle:TrainingTypeList')->findAll();
        //if( $entities ) {
        //    return -1;
        //}

        $elements = array(
            'Undergraduate',
            'Graduate',
            'Medical',
            'Residency',
            'Post-Residency Fellowship',
            'GME',
            'Other'
        );

        $username = $this->getUser();

        $count = 10;
        foreach( $elements as $name ) {

        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:TrainingTypeList'] by [TrainingTypeList::class]
            $entity = $em->getRepository(TrainingTypeList::class)->findOneByName($name);
            if( $entity ) {
                continue;
            }

            $entity = new TrainingTypeList();
            $this->setDefaultList($entity,$count,$username,$name);

            $em->persist($entity);
            $em->flush();

            $count = $count + 10;

        } //foreach

        return round($count/10);

    }

    public function generateAppTitlePositions() {

        $em = $this->getDoctrine()->getManager();

        //$entities = $em->getRepository('AppUserdirectoryBundle:PositionTrackTypeList')->findAll();
        //if( $entities ) {
        //    return -1;
        //}

        $elements = array(
            'Resident',
            'Fellow',
            'Clinical Faculty',
            'Research Faculty',
            'Postdoc',
            'Research Fellow',
            'Research Associate'
        );

        $username = $this->getUser();

        $count = 10;
        foreach( $elements as $name ) {

        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:PositionTrackTypeList'] by [PositionTrackTypeList::class]
            $listEntity = $em->getRepository(PositionTrackTypeList::class)->findOneByName($name);
            if( $listEntity ) {
                continue;
            }

            $entity = new PositionTrackTypeList();
            $this->setDefaultList($entity,null,$username,$name);

            $em->persist($entity);
            $em->flush();

            $count = $count + 10;

        } //foreach

        return round($count/10);

    }


    public function generateFellAppStatus() {

        $em = $this->getDoctrine()->getManager();

        //$entities = $em->getRepository('AppFellAppBundle:FellAppStatus')->findAll();
        //if( $entities ) {
        //    return -1;
        //}

        $elements = array(
            'active'=>'Active',
            'complete'=>'Complete',
            'interviewee'=>'Interviewee',
            'onhold'=>'On Hold',
            'reject'=>'Rejected',
            'hide'=>'Hidden',
            'priority'=>'Priority',
            'archive'=>'Archived',
            'accepted'=>'Accepted',
            'declined'=>'Declined',
            'acceptedandnotified'=>'Accepted and Notified',
            'rejectedandnotified'=>'Rejected and Notified'

        );

        $username = $this->getUser();

        $count = 10;
        foreach( $elements as $name=>$action ) {

        //process.py script: replaced namespace by ::class: ['AppFellAppBundle:FellAppStatus'] by [FellAppStatus::class]
            $listEntity = $em->getRepository(FellAppStatus::class)->findOneByName($name);
            if( $listEntity ) {
                continue;
            }

            $entity = new FellAppStatus();
            $this->setDefaultList($entity,$count,$username,$name);
            $entity->setAction($action);

            $em->persist($entity);
            $em->flush();

            $count = $count + 10;

        } //foreach

        return round($count/10);

    }

    public function generateFellAppRank() {

        $em = $this->getDoctrine()->getManager();
        //process.py script: replaced namespace by ::class: ['AppFellAppBundle:FellAppRank'] by [FellAppRank::class]
        $entities = $em->getRepository(FellAppRank::class)->findAll();

        if( $entities ) {
            return -1;
        }

        $elements = array(
            '1 (Excellent)'=>1,
            '1.5'=>1.5,
            '2 (Average)'=>2,
            '2.5'=>2.5,
            '3 (Below Average)'=>3
        );

        $username = $this->getUser();

        $count = 10;
        foreach( $elements as $name=>$value ) {

            $entity = new FellAppRank();
            $this->setDefaultList($entity,$count,$username,$name);
            $entity->setValue($value);

            $em->persist($entity);
            $em->flush();

            $count = $count + 10;

        } //foreach

        return round($count/10);

    }

    public function generateFellAppVisaStatus() {

        $em = $this->getDoctrine()->getManager();
        //process.py script: replaced namespace by ::class: ['AppFellAppBundle:VisaStatus'] by [VisaStatus::class]
        $entities = $em->getRepository("App\\FellAppBundle\\Entity\\VisaStatus")->findAll();

        if( $entities ) {
            return -1;
        }

        $elements = array(
            "N/A (US Citizenship)",
            "J-1 visa",
            "H-1B visa",
            "Green card/Permanent Residency",
            "EAD",
            "Other-please contact the program coordinator"
        );

        $username = $this->getUser();

        $count = 10;
        foreach( $elements as $name ) {

            $entity = new \App\FellAppBundle\Entity\VisaStatus();
            $this->setDefaultList($entity,$count,$username,$name);

            $em->persist($entity);
            $em->flush();

            $count = $count + 10;

        } //foreach

        return round($count/10);

    }

    public function generateLanguageProficiency() {

        $em = $this->getDoctrine()->getManager();
        //process.py script: replaced namespace by ::class: ['AppFellAppBundle:LanguageProficiency'] by [LanguageProficiency::class]
        $entities = $em->getRepository("App\\FellAppBundle\\Entity\\LanguageProficiency")->findAll();

        if( $entities ) {
            return -1;
        }

        $elements = array(
            "Excellent",
            "Adequate",
            "Inadequate",
            "N/A"
        );

        $username = $this->getUser();

        $count = 10;
        foreach( $elements as $name ) {

            $entity = new \App\FellAppBundle\Entity\LanguageProficiency();
            $this->setDefaultList($entity,$count,$username,$name);

            $em->persist($entity);
            $em->flush();

            $count = $count + 10;

        } //foreach

        return round($count/10);

    }

    ///////////////////// ResApp ///////////////////////////////
    public function generateResAppStatus() {

        $em = $this->getDoctrine()->getManager();

        $elements = array(
            'active'=>'Active',
            'complete'=>'Complete',
            'interviewee'=>'Interviewee',
            'onhold'=>'On Hold',
            'reject'=>'Rejected',
            'hide'=>'Hidden',
            'priority'=>'Priority',
            'archive'=>'Archived',
            'accepted'=>'Accepted',
            'declined'=>'Declined',
            'acceptedandnotified'=>'Accepted and Notified',
            'rejectedandnotified'=>'Rejected and Notified'

        );

        $username = $this->getUser();

        $count = 10;
        foreach( $elements as $name=>$action ) {

        //process.py script: replaced namespace by ::class: ['AppResAppBundle:ResAppStatus'] by [ResAppStatus::class]
            $listEntity = $em->getRepository(ResAppStatus::class)->findOneByName($name);
            if( $listEntity ) {
                continue;
            }

            $entity = new ResAppStatus();
            $this->setDefaultList($entity,$count,$username,$name);
            $entity->setAction($action);

            $em->persist($entity);
            $em->flush();

            $count = $count + 10;

        } //foreach

        return round($count/10);

    }

    public function generateResAppRank() {

        $em = $this->getDoctrine()->getManager();
        //process.py script: replaced namespace by ::class: ['AppResAppBundle:ResAppRank'] by [ResAppRank::class]
        $entities = $em->getRepository(ResAppRank::class)->findAll();

        if( $entities ) {
            return -1;
        }

        $elements = array(
            '1 (Excellent)'=>1,
            '1.5'=>1.5,
            '2 (Average)'=>2,
            '2.5'=>2.5,
            '3 (Below Average)'=>3
        );

        $username = $this->getUser();

        $count = 10;
        foreach( $elements as $name=>$value ) {

            $entity = new ResAppRank();
            $this->setDefaultList($entity,$count,$username,$name);
            $entity->setValue($value);

            $em->persist($entity);
            $em->flush();

            $count = $count + 10;

        } //foreach

        return round($count/10);

    }

    public function generateResAppVisaStatus() {

        $em = $this->getDoctrine()->getManager();
        //process.py script: replaced namespace by ::class: ['AppResAppBundle:VisaStatus'] by [VisaStatus::class]
        $entities = $em->getRepository("App\\ResAppBundle\\Entity\\VisaStatus")->findAll();

        if( $entities ) {
            return -1;
        }

        $elements = array(
            "N/A (US Citizenship)",
            "J-1 visa",
            "H-1B visa",
            "Green card/Permanent Residency",
            "EAD",
            "Other-please contact the program coordinator"
        );

        $username = $this->getUser();

        $count = 10;
        foreach( $elements as $name ) {

            $entity = new \App\ResAppBundle\Entity\VisaStatus();
            $this->setDefaultList($entity,$count,$username,$name);

            $em->persist($entity);
            $em->flush();

            $count = $count + 10;

        } //foreach

        return round($count/10);

    }

    public function generatePostSophList() {

        $em = $this->getDoctrine()->getManager();

        //$entities = $em->getRepository('AppResAppBundle:PostSophList')->findAll();
        //if( $entities ) {
        //    return -1;
        //}

        $elements = array(
            "Pathology",
            "None"
        );

        $username = $this->getUser();

        $count = 10;
        foreach( $elements as $name ) {

        //process.py script: replaced namespace by ::class: ['AppResAppBundle:PostSophList'] by [PostSophList::class]
            $listEntity = $em->getRepository(PostSophList::class)->findOneByName($name);
            if( $listEntity ) {
                continue;
            }

            $entity = new PostSophList();
            $this->setDefaultList($entity,$count,$username,$name);

            $em->persist($entity);
            $em->flush();

            $count = $count + 10;

        } //foreach

        return round($count/10);

    }

    public function generateResAppLanguageProficiency() {

        $em = $this->getDoctrine()->getManager();
        //process.py script: replaced namespace by ::class: ['AppResAppBundle:LanguageProficiency'] by [LanguageProficiency::class]
        $entities = $em->getRepository("App\\ResAppBundle\\Entity\\LanguageProficiency")->findAll();

        if( $entities ) {
            return -1;
        }

        $elements = array(
            "Excellent",
            "Adequate",
            "Inadequate",
            "N/A"
        );

        $username = $this->getUser();

        $count = 10;
        foreach( $elements as $name ) {

            $entity = new \App\ResAppBundle\Entity\LanguageProficiency();
            $this->setDefaultList($entity,$count,$username,$name);

            $em->persist($entity);
            $em->flush();

            $count = $count + 10;

        } //foreach

        return round($count/10);

    }

    public function generateResAppFitForProgram() {

        $em = $this->getDoctrine()->getManager();
        //process.py script: replaced namespace by ::class: ['AppResAppBundle:ResAppFitForProgram'] by [ResAppFitForProgram::class]
        $entities = $em->getRepository(ResAppFitForProgram::class)->findAll();

        if( $entities ) {
            return -1;
        }

        $elements = array(
            "A" => 1,
            "B" => 2,
            "C" => 3,
            "Do not rank" => 4
        );

        $username = $this->getUser();

        $count = 10;
        foreach( $elements as $name=>$abbreviation ) {

            $entity = new ResAppFitForProgram();
            $this->setDefaultList($entity,$count,$username,$name);
            $entity->setAbbreviation($abbreviation);

            $em->persist($entity);
            $em->flush();

            $count = $count + 10;

        } //foreach

        return round($count/10);
    }

    public function generateResAppApplyingResidencyTrack() {
        $em = $this->getDoctrine()->getManager();

        $elements = array(
            "AP/CP",
            "AP Only",
            "CP Only",
            "CP/Physician Scientist Training Program (PSTP)",
            "AP/Physician Scientist Training Program (PSTP)"
        );

        $username = $this->getUser();

        $count = 10;
        foreach( $elements as $name ) {

        //process.py script: replaced namespace by ::class: ['AppResAppBundle:ApplyingResidencyTrack'] by [ApplyingResidencyTrack::class]
            $entity = $em->getRepository(ApplyingResidencyTrack::class)->findOneByName($name);
            if( $entity ) {
                continue;
            }

            $entity = new ApplyingResidencyTrack();
            $this->setDefaultList($entity,$count,$username,$name);

            $em->persist($entity);
            $em->flush();

            $count = $count + 10;

        } //foreach

        return round($count/10);
    }

    public function generateResAppLearnAreaList() {
        $em = $this->getDoctrine()->getManager();

        $elements = array(
            "No Specific Preference",
            "Autopsy",
            "Breast Pathology",
            "Cellular Therapy",
            "Chemistry",
            "Cytopathology",
            "Dermatopathology",
            "GI and Liver Pathology",
            "GU Pathology",
            "GYN Pathology",
            "Head and Neck Pathology",
            "Hematopathology",
            "Hematology/Coagulation",
            "Informatics",
            "Laboratory Management",
            "Microbiology",
            "Molecular Pathology",
            "Neuropathology",
            "Renal Pathology",
            "Thoracic Pathology",
            "Transfusion Medicine"
        );

        $username = $this->getUser();

        $count = 10;
        foreach( $elements as $name ) {

        //process.py script: replaced namespace by ::class: ['AppResAppBundle:LearnAreaList'] by [LearnAreaList::class]
            $entity = $em->getRepository(LearnAreaList::class)->findOneByName($name);
            if( $entity ) {
                continue;
            }

            $entity = new LearnAreaList();
            $this->setDefaultList($entity,$count,$username,$name);

            $em->persist($entity);
            $em->flush();

            $count = $count + 10;

        } //foreach

        return round($count/10);
    }

    public function generateResAppSpecificIndividualList() {
        $em = $this->getDoctrine()->getManager();

        //blank list, no default values
        $elements = array();

        $username = $this->getUser();

        $count = 10;
        foreach( $elements as $name ) {

        //process.py script: replaced namespace by ::class: ['AppResAppBundle:SpecificIndividualList'] by [SpecificIndividualList::class]
            $entity = $em->getRepository(SpecificIndividualList::class)->findOneByName($name);
            if( $entity ) {
                continue;
            }

            $entity = new SpecificIndividualList();
            $this->setDefaultList($entity,$count,$username,$name);

            $em->persist($entity);
            $em->flush();

            $count = $count + 10;

        } //foreach

        return round($count/10);
    }
    ////////////////////// EOF ResApp //////////////////////////////////////


    public function generateVacReqApprovalTypeList() {
        $user = $this->getUser();
        $em = $this->getDoctrine()->getManager();

        $types = array(
            array(
                "name" => "Faculty",
                "vacationAccruedDaysPerMonth" => 2.0,
                "maxVacationDays" => 24,
                "noteForVacationDays" => NULL,
                "maxCarryOverVacationDays" => 10,
                "noteForCarryOverDays" => "As per policy, the number of days that can be carried over to the following year is limited to the maximum of 10",
                "allowCarryOver" => true
            ),
            array(
                "name" => "Fellows",
                "vacationAccruedDaysPerMonth" => 1.666,
                "maxVacationDays" => 20,
                "noteForVacationDays" => "",
                "maxCarryOverVacationDays" => NULL,
                "noteForCarryOverDays" => NULL,
                "allowCarryOver" => false
            ),
        );

        $count = 10;
        foreach( $types as $type ) {

            $name = $type["name"];
            $vacationAccruedDaysPerMonth = $type["vacationAccruedDaysPerMonth"];
            $maxVacationDays = $type["maxVacationDays"];
            $noteForVacationDays = $type["noteForVacationDays"];
            $maxCarryOverVacationDays = $type["maxCarryOverVacationDays"];
            $noteForCarryOverDays = $type["noteForCarryOverDays"];
            $allowCarryOver = $type["allowCarryOver"];

        //process.py script: replaced namespace by ::class: ['AppVacReqBundle:VacReqApprovalTypeList'] by [VacReqApprovalTypeList::class]
            $listEntity = $em->getRepository(VacReqApprovalTypeList::class)->findOneByName($name);
            if( $listEntity ) {
                continue;
            }

            $listEntity = new VacReqApprovalTypeList();
            $this->setDefaultList($listEntity,$count,$user,$name);

            $listEntity->setVacationAccruedDaysPerMonth($vacationAccruedDaysPerMonth);
            $listEntity->setMaxVacationDays($maxVacationDays);
            $listEntity->setNoteForVacationDays($noteForVacationDays);
            $listEntity->setMaxCarryOverVacationDays($maxCarryOverVacationDays);
            $listEntity->setNoteForCarryOverDays($noteForCarryOverDays);
            $listEntity->setAllowCarryOver($allowCarryOver);

            $em->persist($listEntity);
            $em->flush();

            $count = $count + 10;
        }

        return round($count/10);
    }
    public function generateVacReqRequestTypeList() {

        $username = $this->getUser();

        $em = $this->getDoctrine()->getManager();

        $types = array(
            "Business Travel / Vacation Request" => "business-vacation",
            "Carry Over Request" => "carryover",
            "Floating Day" => "floatingday",
        );

        $count = 10;
        foreach( $types as $name => $abbreviation ) {

        //process.py script: replaced namespace by ::class: ['AppVacReqBundle:VacReqRequestTypeList'] by [VacReqRequestTypeList::class]
            $listEntity = $em->getRepository(VacReqRequestTypeList::class)->findOneByName($name);
            if( $listEntity ) {
                continue;
            }

            $listEntity = new VacReqRequestTypeList();
            $this->setDefaultList($listEntity,$count,$username,$name);

            $listEntity->setAbbreviation($abbreviation);

            $em->persist($listEntity);
            $em->flush();

            $count = $count + 10;
        }

        return round($count/10);
    }
    public function generateVacReqFloatingTextList() {

        $username = $this->getUser();

        $em = $this->getDoctrine()->getManager();

        $types = array(
            "During this academic year I have worked on",
            "During this academic year I will work on",
        );

        $count = 10;
        foreach( $types as $name ) {

        //process.py script: replaced namespace by ::class: ['AppVacReqBundle:VacReqFloatingTextList'] by [VacReqFloatingTextList::class]
            $listEntity = $em->getRepository(VacReqFloatingTextList::class)->findOneByName($name);
            if( $listEntity ) {
                continue;
            }

            $listEntity = new VacReqFloatingTextList();
            $this->setDefaultList($listEntity,$count,$username,$name);

            $em->persist($listEntity);
            $em->flush();

            $count = $count + 10;
        }

        return round($count/10);
    }
    public function generateVacReqFloatingTypeList() {

        $username = $this->getUser();

        $em = $this->getDoctrine()->getManager();

        $types = array(
            "Juneteenth",
        );

        $count = 10;
        foreach( $types as $name ) {

        //process.py script: replaced namespace by ::class: ['AppVacReqBundle:VacReqFloatingTypeList'] by [VacReqFloatingTypeList::class]
            $listEntity = $em->getRepository(VacReqFloatingTypeList::class)->findOneByName($name);
            if( $listEntity ) {
                continue;
            }

            $listEntity = new VacReqFloatingTypeList();
            $this->setDefaultList($listEntity,$count,$username,$name);

            $em->persist($listEntity);
            $em->flush();

            $count = $count + 10;
        }

        return round($count/10);
    }

    public function generateHealthcareProviderSpecialtiesList() {

        $username = $this->getUser();

        $em = $this->getDoctrine()->getManager();

        $types = array(
            "Blood Bank Personnel",
            "Microbiology Lab Personnel",
            "Cellular Therapy Lab Personnel",
            "Coagulation Lab Personnel",
            "Toxicology Lab Personnel",
            "Molecular Lab Personnel",
            "Cytogenetics Lab Personnel",
            "Central Lab Personnel",
            "OR nurse",
            "OR anesthesiologist",
            "OR surgeon",
            "Floor nurse",
            "Floor physician",
            "Floor PA",
            "Hospital administrator",
            "Infusion center nurse",
            "Infusion center physician",
            "Infusion center nurse practitioner",
            "Medical student",
            "ER physician",
            "ER nurse",
            "ER PA",
            "Interventional radiology physician",
            "Interventional radiology nurse",
            "Interventional radiology PA",
            "Endoscopy physician",
            "Endoscopy nurse",
            "Endoscopy PA"
        );

        $count = 10;
        foreach( $types as $name ) {

        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:HealthcareProviderSpecialtiesList'] by [HealthcareProviderSpecialtiesList::class]
            $listEntity = $em->getRepository(HealthcareProviderSpecialtiesList::class)->findOneByName($name);
            if( $listEntity ) {
                continue;
            }

            $listEntity = new HealthcareProviderSpecialtiesList();
            $this->setDefaultList($listEntity,$count,$username,$name);

            $em->persist($listEntity);
            $em->flush();

            $count = $count + 10;
        }

        return round($count/10);
    }

    public function generateHealthcareProviderCommunicationsList() {

        $username = $this->getUser();

        $em = $this->getDoctrine()->getManager();

        $types = array(
            "Inbound",
            "Outbound"
        );

        $count = 10;
        foreach( $types as $name ) {

        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:HealthcareProviderCommunicationList'] by [HealthcareProviderCommunicationList::class]
            $listEntity = $em->getRepository(HealthcareProviderCommunicationList::class)->findOneByName($name);
            if( $listEntity ) {
                continue;
            }

            $listEntity = new HealthcareProviderCommunicationList();
            $this->setDefaultList($listEntity,$count,$username,$name);

            $em->persist($listEntity);
            $em->flush();

            $count = $count + 10;
        }

        return round($count/10);
    }

    public function generateCollaborationtypes() {

        $em = $this->getDoctrine()->getManager();
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:CollaborationTypeList'] by [CollaborationTypeList::class]
        $entities = $em->getRepository(CollaborationTypeList::class)->findAll();

        if( $entities ) {
            return -1;
        }

//        "Menu Search Action" - "Search patients data and orders within collaboration institutions on the top search"
//        "Check Button Retrieve Action" - "Retrieve patients data within collaboration institutions on the check button click"
//        "View Action" - "View patient data on check button pressed in order form and view orders within collaboration institutions"
//        "Submit Action" - "Submit orders for collaboration institution"

        $elements = array(
            "Union" => 'Bidirectional collaboration: Users within this type of collaboration have full access to the patient data.'.
                       'Supported actions: "Menu Search Action", "Check Button Retrieve Action", "View Action", "Submit Action""',
            "Intersection" => 'Unidirectional trusted collaboration: Users withini this type of collaboration can view and submit new orders in the same way as bidirectional collaboration.'.
                              'Supported actions: "Check Button Retrieve Action", "View Action", "Submit Action"',
            "Untrusted Intersection" => 'Unidirectional untrusted: Supported actions: "Submit Action".'.
                                        'If the user enters an existing MRN number and click check button it will retrieve empty data, so the user can enter a new data which will marked as "invalid".'
        );

        $username = $this->getUser();

        $count = 10;
        foreach( $elements as $name => $description ) {

            $entity = new CollaborationTypeList();
            $this->setDefaultList($entity,$count,$username,$name);

            $entity->setDescription($description);

            $em->persist($entity);
            $em->flush();

            $count = $count + 10;

        } //foreach

        return round($count/10);

    }

    //3) Create Permission objects by name. This Permission Object will be linked to permission's by entity name and action.
    //This Permission Object will be linked with name "Create a New Residency Application" has permission's object "ResidencyApplication" and action "create"
    //As the final step, this permission will be attached to the role.
    public function generatePermissions() {

        $username = $this->getUser();

        $em = $this->getDoctrine()->getManager();
//        $entities = $em->getRepository('AppUserdirectoryBundle:PermissionList')->findAll();
//        if( $entities ) {
//            return -1;
//        }

        $types = array(
            //"View Patient Data for a given patient", //(our "check" button AND our "Test Patient" view page)
            //"Add Patient Data",
            //"Modify Patient Data",
            "Delete Patient Data", //(or mark it inactive/invalid since we don't delete; this and 3 above are for Data Quality role)
            "Add a New Patient",
            "Add a New Encounter",
            "Add a New Procedure",
            "Add a New Accession",
            "Add a New Part",
            "Add a New Block",
            "Add a New Slide",
            "Add a New Image",
            "Submit Orders",
            "Sign Orders",      //(if it is a two-step process - submit into a queue then someone else signs)
            "Submit Results",
            "Sign Results",     //(if it is a two-step process - submit into a queue then someone else signs)
            "Change the status of an order",
            "Change a status of a result",
            "Browse/search incoming orders for a given organizational group",
            "Browse/search outgoing orders for a given organizational group",
            "Browse/search incoming results for a given organizational group",
            "Browse/search outgoing results for a given organizational group",
            "Browse/search patients that 'belong' to a given organizational group",
            "Browse/search accessions that 'belong' to a given organizational group",

            "Search by Deidentifier ID",
            "Generate new Deidentifier ID",

            "Submit an interview evaluation",
            "Create a New Fellowship Application",
            "Modify a Fellowship Application",
            "View a Fellowship Application",

            //"Submit an residency interview evaluation",
            "Create a New Residency Application",
            "Modify a Residency Application",
            "View a Residency Application",

            "Submit a Vacation Request",
            "Approve a Vacation Request",
            "Approve a Carry Over Request",
            "Approve a Floating Day Request",
            "Submit a Floating Day Request",

            "Create Call Log Entry",
            "Edit Call Log Entry",
            "Change Status of Call Log Entry",
            "Hide Call Log Entry",
            "Change Status of Patient to Complex",
            "Change Status of Patient away from Complex",
            //"Create Patient Record", //the same as "Add a New Patient"
            "Edit Patient Record",
            "Hide Patient Record",
            "Delete Patient Record",
            "Read Patient Record",
            "Merge Patient Record"
        );

        $count = 10;
        foreach( $types as $type ) {

        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:PermissionList'] by [PermissionList::class]
            $listEntity = $em->getRepository(PermissionList::class)->findOneByName($type);
            if( $listEntity ) {
                continue;
            }

            $listEntity = new PermissionList();
            $this->setDefaultList($listEntity,$count,$username,$type);

            $em->persist($listEntity);
            $em->flush();

            $count = $count + 10;
        }

        return round($count/10);
    }

    //2) create permission objects associated with sites (i.e. "ResidencyApplication" has site "resapp")
    public function generatePermissionObjects() {

        $username = $this->getUser();

        $em = $this->getDoctrine()->getManager();

//        $entities = $em->getRepository('AppUserdirectoryBundle:PermissionObjectList')->findAll();
//        if( $entities ) {
//            return -1;
//        }

        //use only real object names corresponding to the entity's DB name
        $types = array(
            "Patient" => array("",array("scan","calllog")),
            //"Patient Record" => array("",array("scan","calllog")), //TODEL
            "Encounter" => array("",array("scan","calllog")),
            "Procedure" => array("",array("scan")),

            "Accession" => array("",array("scan","deidentifier")),

            "Part" => array("",array("scan")),
            "Block" => array("",array("scan")),
            "Slide" => array("",array("scan")),
            "Image" => array("Imaging",array("scan")),
            //"Image Analysis" => array("",array("scan")),
            //"Order" => array("Message",array("scan")),
            "Message" => array("",array("scan","calllog")),
            "Report" => array("",array("scan")),

            "Interview" => array("",array("fellapp","resapp")),
            "FellowshipApplication" => array("",array("fellapp")),

            //"Interview" => array("",array("resapp")),
            "ResidencyApplication" => array("",array("resapp")),

            "VacReqRequest" => array("",array("vacreq")), //"Business/Vacation Request"
            "VacReqRequestFloating" => array("",array("vacreq")), //"Floating Day Request"

            //"Call Log Entry" => array("",array("calllog")),
            //"Complex Patient" => array("",array("calllog")), //TODEL


        );

        $count = 10;
        foreach( $types as $name => $abbreviationSiteArr ) {

            if( !$name || $name == "" ) {
                continue;
            }

        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:PermissionObjectList'] by [PermissionObjectList::class]
            $listEntity = $em->getRepository(PermissionObjectList::class)->findOneByName($name);
            if( $listEntity ) {

                $abbreviation = $abbreviationSiteArr[0];
                if( $abbreviation && $abbreviation != "" ) {
                    if( !$listEntity->getAbbreviation() ) {
                        $listEntity->setAbbreviation($abbreviation);
                        $em->persist($listEntity);
                        $em->flush();
                    }
                }

                $sites = $abbreviationSiteArr[1];
                foreach( $sites as $site ) {
                    $siteObject = $em->getRepository(SiteList::class)->findOneByAbbreviation($site);
                    if( !$listEntity->getSites()->contains($siteObject) ) {
                        $listEntity->addSite($siteObject);
                        $em->persist($listEntity);
                        $em->flush();
                    }
                }

                continue;
            }

            //Create new
            $listEntity = new PermissionObjectList();
            $this->setDefaultList($listEntity,$count,$username,$name);

            $sites = $abbreviationSiteArr[1];
            foreach( $sites as $site ) {
                $siteObject = $em->getRepository(SiteList::class)->findOneByAbbreviation($site);
                if( !$listEntity->getSites()->contains($siteObject) ) {
                    $listEntity->addSite($siteObject);
                    //$em->persist($listEntity);
                    //$em->flush();
                }
            }

            $em->persist($listEntity);
            $em->flush();

            $count = $count + 10;
        }

        return round($count/10);
    }

    //1) create independent actions
    public function generatePermissionActions() {

        $username = $this->getUser();

        $em = $this->getDoctrine()->getManager();
//        $entities = $em->getRepository('AppUserdirectoryBundle:PermissionActionList')->findAll();
//        if( $entities ) {
//            return -1;
//        }

        $types = array(
            "create",
            "read",
            "update",
            "delete",
            "changestatus",

            "changestatus-carryover",
            "view-away-calendar",

            "merge",
            "hide",
            "changestatus-to-complex",
            "changestatus-from-complex",

        );

        $count = 10;
        foreach( $types as $type ) {

        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:PermissionActionList'] by [PermissionActionList::class]
            $listEntity = $em->getRepository(PermissionActionList::class)->findOneByName($type);
            if( $listEntity ) {
                continue;
            }

            $listEntity = new PermissionActionList();
            $this->setDefaultList($listEntity,$count,$username,$type);

            $em->persist($listEntity);
            $em->flush();

            $count = $count + 10;
        }

        return round($count/10);
    }

    public function generateObjectTypeActions() {

        $username = $this->getUser();

        $em = $this->getDoctrine()->getManager();
//        $entities = $em->getRepository('AppUserdirectoryBundle:PermissionActionList')->findAll();
//        if( $entities ) {
//            return -1;
//        }

        $types = array(
            "Form Group",
            "Form",
            "Form Section",
            "Form Section Array",

            //String
            array(
                'name' => "Form Field - Free Text, Single Line",
                'receivedValueEntityNamespace' => 'App\UserdirectoryBundle\Entity',
                'receivedValueEntityName' => 'ObjectTypeString'
            ),
            array(
                'name' => "Form Field - Free Text, Single Line, Locked, Calculated, Stored",
                'receivedValueEntityNamespace' => 'App\UserdirectoryBundle\Entity',
                'receivedValueEntityName' => 'ObjectTypeString'
            ),
            array(
                'name' => "Form Field - Free Text, Single Line, Unlocked, Calculated, Stored",
                'receivedValueEntityNamespace' => 'App\UserdirectoryBundle\Entity',
                'receivedValueEntityName' => 'ObjectTypeString'
            ),
            array(
                'name' => "Form Field - Free Text, Single Line, Numeric, Unsigned Positive Integer",
                'receivedValueEntityNamespace' => 'App\UserdirectoryBundle\Entity',
                'receivedValueEntityName' => 'ObjectTypeString'
            ),
            array(
                'name' => "Form Field - Free Text, Single Line, Numeric, Signed Integer",
                'receivedValueEntityNamespace' => 'App\UserdirectoryBundle\Entity',
                'receivedValueEntityName' => 'ObjectTypeString'
            ),
            array(
                'name' => "Form Field - Free Text, Single Line, Numeric, Signed Float",
                'receivedValueEntityNamespace' => 'App\UserdirectoryBundle\Entity',
                'receivedValueEntityName' => 'ObjectTypeString'
            ),
            array(
                'name' => "Form Field - Free Text, Single Line, Locked, Calculated, Visual Aid",
                'receivedValueEntityNamespace' => 'App\UserdirectoryBundle\Entity',
                'receivedValueEntityName' => 'ObjectTypeString'
            ),

            //Text
            array(
                'name' => "Form Field - Free Text",
                'receivedValueEntityNamespace' => 'App\UserdirectoryBundle\Entity',
                'receivedValueEntityName' => 'ObjectTypeText'
            ),
            array(
                'name' => "Form Field - Free Text, RTF",
                'receivedValueEntityNamespace' => 'App\UserdirectoryBundle\Entity',
                'receivedValueEntityName' => 'ObjectTypeText'
            ),
            array(
                'name' => "Form Field - Free Text, HTML",
                'receivedValueEntityNamespace' => 'App\UserdirectoryBundle\Entity',
                'receivedValueEntityName' => 'ObjectTypeText'
            ),

            //Dates
            array(
                'name' => "Form Field - Full Date",
                'receivedValueEntityNamespace' => 'App\UserdirectoryBundle\Entity',
                'receivedValueEntityName' => 'ObjectTypeDateTime'
            ),
            array(
                'name' => "Form Field - Time",
                'receivedValueEntityNamespace' => 'App\UserdirectoryBundle\Entity',
                'receivedValueEntityName' => 'ObjectTypeDateTime'
            ),
            array(
                'name' => "Form Field - Full Date and Time",
                'receivedValueEntityNamespace' => 'App\UserdirectoryBundle\Entity',
                'receivedValueEntityName' => 'ObjectTypeDateTime'
            ),
            array(
                'name' => "Form Field - Year",
                'receivedValueEntityNamespace' => 'App\UserdirectoryBundle\Entity',
                'receivedValueEntityName' => 'ObjectTypeDateTime'
            ),
            array(
                'name' => "Form Field - Month",
                'receivedValueEntityNamespace' => 'App\UserdirectoryBundle\Entity',
                'receivedValueEntityName' => 'ObjectTypeDateTime',
            ),
            array(
                'name' => "Form Field - Date",
                'receivedValueEntityNamespace' => 'App\UserdirectoryBundle\Entity',
                'receivedValueEntityName' => 'ObjectTypeDateTime'
            ),
            array(
                'name' => "Form Field - Day of the Week",
                'receivedValueEntityNamespace' => 'App\UserdirectoryBundle\Entity',
                'receivedValueEntityName' => 'ObjectTypeDateTime',
            ),
            array(
                'name' => "Form Field - Time, with Time Zone",
                'receivedValueEntityNamespace' => 'App\UserdirectoryBundle\Entity',
                'receivedValueEntityName' => 'ObjectTypeDateTime'
            ),
            array(
                'name' => "Form Field - Full Date and Time, with Time Zone",
                'receivedValueEntityNamespace' => 'App\UserdirectoryBundle\Entity',
                'receivedValueEntityName' => 'ObjectTypeDateTime'
            ),

            //Dropdown
            array(
                'name' => "Form Field - Dropdown Menu",
                'receivedValueEntityNamespace' => 'App\UserdirectoryBundle\Entity',
                'receivedValueEntityName' => 'ObjectTypeDropdown'
            ),
            array(
                'name' => "Form Field - Dropdown Menu - Allow New Entries",
                'receivedValueEntityNamespace' => 'App\UserdirectoryBundle\Entity',
                'receivedValueEntityName' => 'ObjectTypeDropdown'
            ),
            array(
                'name' => "Form Field - Dropdown Menu - Allow Multiple Selections - Allow New Entries",
                'receivedValueEntityNamespace' => 'App\UserdirectoryBundle\Entity',
                'receivedValueEntityName' => 'ObjectTypeDropdown'
            ),
            array(
                'name' => "Form Field - Dropdown Menu - Allow Multiple Selections",
                'receivedValueEntityNamespace' => 'App\UserdirectoryBundle\Entity',
                'receivedValueEntityName' => 'ObjectTypeDropdown'
            ),
            //"Dropdown Menu Value",

            //Patient
            array(
                'name' => "Linked Object - Patient",
                'receivedValueEntityNamespace' => 'App\OrderformBundle\Entity',
                'receivedValueEntityName' => 'Patient'
            ),

            //Checkbox
            array(
                'name' => "Form Field - Checkbox",
                'receivedValueEntityNamespace' => 'App\UserdirectoryBundle\Entity',
                'receivedValueEntityName' => 'ObjectTypeCheckbox'
            ),
            array(
                'name' => "Form Field - Checkboxes",
                'receivedValueEntityNamespace' => 'App\UserdirectoryBundle\Entity',
                'receivedValueEntityName' => 'ObjectTypeCheckbox'
            ),

            //Radio
            array(
                'name' => "Form Field - Radio Button",
                'receivedValueEntityNamespace' => 'App\UserdirectoryBundle\Entity',
                'receivedValueEntityName' => 'ObjectTypeRadioButton' //radio button is very similar to the dropdown menu
            ),

            //User
//            array(
//                'name' => "Linked Object - User",
//                'receivedValueEntityNamespace' => 'App\OrderformBundle\Entity',
//                'receivedValueEntityName' => 'PathologyResultSignatories'
//            ),



        );

        $count = 10;
        foreach( $types as $type ) {

            if( is_array($type) ) {
                $name = $type['name'];
                $receivedValueEntityNamespace = $type['receivedValueEntityNamespace'];
                $receivedValueEntityName = $type['receivedValueEntityName'];
            } else {
                //echo "string ";
                $name = $type;
                $receivedValueEntityNamespace = NULL;
                $receivedValueEntityName = NULL;
            }

            //echo "name=".$name."<br>";

        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:ObjectTypeList'] by [ObjectTypeList::class]
            $listEntity = $em->getRepository(ObjectTypeList::class)->findOneByName($name);
            if( $listEntity ) {
                $updated = false;
                if( !$listEntity->getReceivedValueEntityNamespace() && $receivedValueEntityNamespace ) {
                    $listEntity->setReceivedValueEntityNamespace($receivedValueEntityNamespace);
                    $updated = true;
                }
                if( !$listEntity->getReceivedValueEntityName() && $receivedValueEntityName ) {
                    $listEntity->setReceivedValueEntityName($receivedValueEntityName);
                    $updated = true;
                }

                if( $listEntity->getEntityNamespace() ) {
                    $listEntity->setEntityNamespace(NULL);
                    $updated = true;
                }
                if( $listEntity->getEntityName() ) {
                    $listEntity->setEntityName(NULL);
                    $updated = true;
                }

                if( $updated ) {
                    $em->persist($listEntity);
                    $em->flush();
                }
                continue;
            }

            $listEntity = new ObjectTypeList();
            $this->setDefaultList($listEntity,null,$username,$name);

            if( $receivedValueEntityNamespace ) {
                //echo "receivedValueEntityNamespace=".$receivedValueEntityNamespace."<br>";
                $listEntity->setReceivedValueEntityNamespace($receivedValueEntityNamespace);
            }
            if( $receivedValueEntityName ) {
                //echo "entityName=".$receivedValueEntityName."<br>";
                $listEntity->setReceivedValueEntityName($receivedValueEntityName);
            }

            //exit('exit generateObjectTypeActions');
            $em->persist($listEntity);
            $em->flush();

            $count = $count + 10;
        }

        return round($count/10);
    }
    //set "Object Type"="Dropdown Menu Value" (referring to this list) for all items/rows on all lists
    // except the root Platform List Manager List where all items should have "Object Type"="Form Field - Dropdown Menu"
    public function setObjectTypeForAllLists() {
        return -1;

        $children  = array();
        $classes = get_declared_classes();
        print_r($classes);
        foreach( $classes as $class ){
            //echo "0 class=".$class."<br>";
            if( $class instanceof ListAbstract ) {
            //if( is_subclass_of( $class, 'ListAbstract' ) ) {
                $children[] = $class;
                echo "ListAbstract class=".$class."<br>";
            }
        }

        //echo $this->get_extends_number('ListAbstract')."<br>";

        exit('exit');
    }


    public function generateEventObjectTypeList() {

        $username = $this->getUser();

        $em = $this->getDoctrine()->getManager();

        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:Logger'] by [Logger::class]
        $repository = $this->getDoctrine()->getRepository(Logger::class);
        $query = $repository->createQueryBuilder('logger')
            ->select('logger.entityName')
            ->distinct()
            ->getQuery();
        $types = $query->getResult();

//        if( count($types) == 0 ) {
//            $types = array(
//                "User",
//                "SiteList",
//                "FellowshipApplication",
//                "Accession",
//                "AccessionAccession",
//                "Roles"
//            );
//        }

        $count = 10;
        foreach( $types as $type ) {

            //print_r($type);
            //$entityName = $type;    //$type['entityName'];
            $entityName = $type['entityName'];
            //echo "entityName=".$entityName."<br>";
            //exit('1');

            if( !$entityName ) {
                continue;
            }
            //echo "entityName=".$entityName."<br>";

            $listEntity = $em->getRepository(EventObjectTypeList::class)->findOneByName($entityName);
            if( $listEntity ) {
                continue;
            }

            $listEntity = new EventObjectTypeList();
            $this->setDefaultList($listEntity,$count,$username,$entityName);

            $em->persist($listEntity);
            $em->flush();

            $count = $count + 10;
        }

        return round($count/10);
    }

    #[Route(path: '/populate-class-url/', name: 'user_populate_class_url', methods: ['GET'])]
    public function populateClassUrlAction( Request $request=null ) {
        $this->populateClassUrl();
    }
    public function populateClassUrl() {
        $em = $this->getDoctrine()->getManager();
        $userServiceUtil = $this->container->get('user_service_utility');

        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:Logger'] by [Logger::class]
        $repository = $this->getDoctrine()->getRepository(Logger::class);
        $query = $repository->createQueryBuilder('logger')
            ->select('logger.entityName')
            ->distinct()
            ->getQuery();
        $types = $query->getResult();

        $count = 0;
        foreach( $types as $type ) {
            $entityName = $type['entityName'];
            //echo "entityName=".$entityName."<br>";

            if( !$entityName ) {
                continue;
            }

            $listEntity = $em->getRepository(EventObjectTypeList::class)->findOneByName($entityName);
            //echo "listEntity=".$listEntity."<br>";
            if( !$listEntity ) {
                continue;
            }

            $url = $userServiceUtil->classNameUrlMapper($entityName);
            //echo "url=".$url."<br>";

            $flushFlag = false;

            if( $entityName == "Patient" ) {
                //add SiteList scan
                $scanSite = $this->getDoctrine()->getRepository(SiteList::class)->findOneByAbbreviation('scan');
                $res = $listEntity->addExclusivelySite($scanSite); //get add result: true if added
                if( $res ) {
                    $flushFlag = true;
                }
            }

            $currentUrl = $listEntity->getUrl();
            if( $currentUrl != $url ) {
                $listEntity->setUrl($url);
                $flushFlag = true;
            }

            if( $flushFlag ) {
                $em->persist($listEntity);
                $em->flush();
                //echo "Set url=[".$url."]<br><br>";
                $count++;
            }

        }

        //exit("populateClassUrl count=".$count);
        return $count;
    }

    /**
     * populate Platform List Manager Root List: url="order/directory/admin/list-manager-populate/"
     */
    #[Route(path: '/list/generate-empty-lists/{withcustom}', name: 'user_populate_platform_list_manager', methods: ['GET'])]
    public function generatePlatformListManagerList( Request $request=null, $withcustom=null ) {

        $username = $this->getUser();

        $em = $this->getDoctrine()->getManager();

        $scanListController = new ScanListController();

        $types = array(
            "1"  => array('PlatformListManagerRootList','platformlistmanager-list'),
            "10" => array('SiteList','sites-list'),
            "20" => array('SourceSystemList','sourcesystems-list'),
            "30" => array('Roles','role-list'),
            "40" => array('Institution','institutions-list'),
            "50" => array('States','states-list'),
            "60" => array('Countries','countries-list'),
            "70" => array('BoardCertifiedSpecialties','boardcertifications-list'),
            "80" => array('EmploymentType','employmenttypes-list'),
            "90" => array('EmploymentTerminationType','employmentterminations-list'),

            "100" => array('EventTypeList','loggereventtypes-list'),
            "110" => array('UsernameType','usernametypes-list'),
            "120" => array('IdentifierTypeList','identifiers-list'),
            "130" => array('ResidencyTrackList','residencytracks-list'),
            "140" => array('FellowshipTypeList','fellowshiptypes-list'),
            "150" => array('LocationTypeList','locationtypes-list'),
            "160" => array('Equipment','equipments-list'),
            "170" => array('EquipmentType','equipmenttypes-list'),
            "180" => array('LocationPrivacyList','locationprivacy-list'),
            "190" => array('RoleAttributeList','roleattributes-list'),

            "200" => array('BuildingList','buildings-list'),
            "210" => array('RoomList','rooms-list'),
            "220" => array('SuiteList','suites-list'),
            "230" => array('FloorList','floors-list'),
            "240" => array('MailboxList','mailboxes-list'),
            "250" => array('EffortList','efforts-list'),
            "260" => array('AdminTitleList','admintitles-list'),
            "270" => array('AppTitleList','apptitles-list'),
            "280" => array('CompletionReasonList','completionreasons-list'),
            "290" => array('TrainingDegreeList','trainingdegrees-list'),
            "295" => array('Grant','grants-list'),

            "300" => array('MajorTrainingList','trainingmajors-list'),
            "310" => array('MinorTrainingList','trainingminors-list'),
            "320" => array('HonorTrainingList','traininghonors-list'),
            "330" => array('FellowshipTitleList','fellowshiptitles-list'),
            "340" => array('ResidencySpecialty','residencyspecialtys-list'),
            "350" => array('FellowshipSubspecialty','fellowshipsubspecialtys-list'),
            "360" => array('InstitutionType','institutiontypes-list'),
            "370" => array('DocumentTypeList','documenttypes-list'),
            "380" => array('MedicalTitleList','medicaltitles-list'),
            "390" => array('MedicalSpecialties','medicalspecialties-list'),

            "400" => array('SourceOrganization','sourceorganizations-list'),
            "410" => array('LanguageList','languages-list'),
            "420" => array('ImportanceList','importances-list'),
            "430" => array('LocaleList','locales-list'),
            "450" => array('AuthorshipRoles','authorshiproles-list'),
            "460" => array('OrganizationList','organizations-list'),
            "470" => array('CityList','cities-list'),
            "480" => array('LinkTypeList','linktypes-list'),
            "490" => array('SexList','sexes-list'),

            "500" => array('PositionTypeList','positiontypes-list'),
            "510" => array('OrganizationalGroupType','organizationalgrouptypes-list'),
            "520" => array('CommentTypeList','commenttypes-list'),
            "530" => array('CommentGroupType','commentgrouptypes-list'),
            "540" => array('UserWrapper','userwrappers-list'),
            "550" => array('SpotPurpose','spotpurposes-list'),
            "560" => array('MedicalLicenseStatus','medicalstatuses-list'),
            "570" => array('CertifyingBoardOrganization','certifyingboardorganizations-list'),
            "580" => array('TrainingTypeList','trainingtypes-list'),
            "590" => array('JobTitleList','joblists-list'),

            "600" => array('FellAppStatus','fellappstatuses-list','Fellowship Application Status'),
            "610" => array('FellAppRank','fellappranks-list','Fellowship Application Score'),
            "620" => array('LanguageProficiency','fellapplanguageproficiency-list'),
            "630" => array('CollaborationTypeList','collaborationtypes-list'),
            "640" => array('PermissionList','permission-list'),
            "650" => array('PermissionObjectList','permissionobject-list'),
            "660" => array('PermissionActionList','permissionaction-list'),
            "670" => array('EventObjectTypeList','eventobjecttypes-list'),
            "680" => array('VacReqRequestTypeList','vacreqrequesttypes-list','Vacation Request Type'),

            //added on December 7 2016, after July 19th, 2016
            "690" => array('HealthcareProviderSpecialtiesList','healthcareproviderspecialty-list','Healthcare Provider Specialty'),
            "700" => array('MessageTypeClassifiers','messagetypeclassifiers-list'),
            "710" => array('AmendmentReasonList','amendmentreasons-list'),
            "720" => array('ObjectTypeList','objecttypes-list'),
            //"730" => array('PathologyCallComplexPatients','pathologycallcomplexpatients-list'),
            "730" => array('PatientListHierarchy','patientlisthierarchys-list'),
            "740" => array('PatientListHierarchyGroupType','patientlisthierarchygrouptype-list'),
            "750" => array('ObjectTypeText','objecttypetexts-list'),
            "760" => array('ObjectTypeString','objecttypestrings-list'),
            "770" => array('ObjectTypeDropdown','objecttypedropdowns-list'),
            "780" => array('BloodProductTransfusedList','bloodproducttransfusions-list'),
            "790" => array('TransfusionReactionTypeList','transfusionreactiontypes-list'),
            "800" => array('BloodTypeList','bloodtypes-list'),
            "810" => array('TransfusionAntibodyScreenResultsList','transfusionantibodyscreenresults-list'),
            "820" => array('TransfusionCrossmatchResultsList','transfusioncrossmatchresults-list'),
            "830" => array('TransfusionDATResultsList','transfusiondatresults-list','Transfusion DAT Results List'),
            "840" => array('TransfusionHemolysisCheckResultsList','transfusionhemolysischeckresults-list'),
            "850" => array('ObjectTypeDateTime','objecttypedatetimes-list'),
            "860" => array('ComplexPlateletSummaryAntibodiesList','complexplateletsummaryantibodies-list'),
            "870" => array('CCIUnitPlateletCountDefaultValueList','cciunitplateletcountdefaultvalues-list','CCI Unit Platelet Count Default Value List'),
            "880" => array('CCIPlateletTypeTransfusedList','cciplatelettypetransfuseds-list','CCI Platelet Type Transfused List'),
            "890" => array('PlateletTransfusionProductReceivingList','platelettransfusionproductreceivings-list'),
            "900" => array('TransfusionProductStatusList','transfusionproductstatus-list'),
            "910" => array('EncounterInfoTypeList','encounterinfotypes-list'),
            "920" => array('EncounterStatusList','encounterstatuses-list'),
            "930" => array('PatientRecordStatusList','patientrecordstatuses-list'),
            "940" => array('MessageStatusList','messagestatuses-list'),
            "950" => array('WeekDaysList','weekdays-list'),
            "960" => array('MonthsList','months-list'),
            "970" => array('FormNode','formnodes-list','Flat Form Tree'),
            "980" => array('ClericalErrorList','clericalerrors-list'),
            "990" => array('LabResultNameList','labresultnames-list','Lab Result Names'),
            "1000" => array('LabResultUnitsMeasureList','labresultunitsmeasures-list','Lab Result Units of Measure List'),
            "1010" => array('LabResultFlagList','labresultflags-list','Lab Result Flag List'),
            "1020" => array('PathologyResultSignatoriesList','pathologyresultsignatories-list','Pathology Result Signatories List'),
            "1030" => array('ObjectTypeCheckbox','objecttypecheckboxs-list','Object Type Checkbox'),
            "1040" => array('ObjectTypeRadioButton','objecttyperadiobuttons-list','Object Type Radio Button'),
            "1050" => array('Location','employees_locations_pathaction_list','Locations'),
            "1060" => array('LifeFormList','lifeforms-list','Life Form'),
            "1070" => array('PositionTrackTypeList','positiontracktypes-list','Position Track Type List'),
            "1080" => array('SuggestedMessageCategoriesList','suggestedmessagecategorys-list','Suggested Message Categories List'),
            //"1090" => array('CalllogEntryTagsList','calllogentrytags-list','Call Log Entry Tags List'),
            "1100" => array('SpecialtyList','transresprojectspecialties-list','Translational Research Project Specialty List'),
            "1110" => array('ProjectTypeList','transresprojecttypes-list','Translational Research Project Type List'),
            "1120" => array('RequestCategoryTypeList','transresrequestcategorytypes-list','Translational Research Request Products/Services (Fee Schedule) List'),
            //"1050" => array('','-list'),
            //Don't add in the format above ("1120" => array ...). Add in format "transresrequestcategorytypes" => array(

            //Add scan order lists
            "ProjectTitleTree" => array('ProjectTitleTree','researchprojecttitles-list',"Project Titles"),
            "ResearchGroupType" => array('ResearchGroupType','researchprojectgrouptype-list',"Research Project Group Types"),
            "CourseTitleTree" => array('CourseTitleTree','educationalcoursetitles-list','Course Titles'),
            "CourseGroupType" => array('CourseGroupType','educationalcoursegrouptypes-list','Educational Course Group Types'),
            "mrntype" => array('','',''),
            "AccessionType" => array('AccessionType','accessiontype-list','Accession Types'),
            "EncounterType" => array('EncounterType','encountertype-list',"Encounter Number Types"),
            "ProcedureType" => array('ProcedureType','proceduretype-list','Procedure Number Types'),
            "stain" => array('','-list'),
            "organ" => array('','-list'),
            "encounter" => array('','-list'),
            "procedure" => array('','-list'),
            "slidetype" => array('','-list'),
            "messagecategorys" => array('','-list'),
            "status" => array('','-list'),
            "orderdelivery" => array('','-list'),
            "regiontoscan" => array('','-list'),
            "processorcomment" => array('','-list'),
            "accounts" => array('','-list'),
            "urgency" => array('','-list'),
            "progresscommentseventtypes" => array('','-list'),
            "scanloggereventtypes" => array('','-list'),
            "races" => array('','-list'),
            "reporttype" => array('','-list'),
            "instruction" => array('','-list'),
            "patienttype" => array('','-list'),
            "magnifications" => array('','-list'),
            "imageanalysisalgorithm" => array('','-list'),
            "diseasetypes" => array('','-list'),
            "diseaseorigins" => array('','-list'),
            "labtesttype" => array('','-list'),
            "parttitle" => array('','-list'),
            "messagetypeclassifiers" => array('','-list'),
            "amendmentreasons" => array('','-list'),
            "patientlisthierarchys" => array('','-list'),
            "pathologycallcomplexpatients" => array('','-list'),
            "patientlisthierarchygrouptype" => array('','-list'),
            "encounterstatuses" => array('','-list'),
            "patientrecordstatuses" => array('','-list'),
            "messagestatuses" => array('','-list'),
            "encounterinfotypes" => array('','-list'),
            "suggestedmessagecategorys" => array('','-list'),
            //"calllogentrytags" => array('','-list'),
            "calllogattachmenttypes" => array('','-list'),
            "calllogtasktypes" => array('','-list'),

            "antibodies" => array('AntibodyList','antibodies-list','Antibody List'),
            "transrestissueprocessingservices" => array('TissueProcessingServiceList','transrestissueprocessingservices-list','Translational Research Tissue Processing Service List'),
            "transresotherrequestedservices" => array('OtherRequestedServiceList','transresotherrequestedservices-list','Translational Research Other Requested Service List'),
            "transresbusinesspurposes" => array('BusinessPurposeList','transresbusinesspurposes-list','Translational Research Work Request Business Purposes'),
            "transrespricetypes" => array('PriceTypeList','transrespricetypes-list','Translational Research Price Type List'),
            "workqueuetypes" => array('WorkQueueList','workqueuetypes-list','Work Queue Type List'),
            "orderablestatus" => array('OrderableStatusList','orderablestatus-list','Orderable Status List'),
            "transrescolllabs" => array('CollLabList','transrescolllabs-list','Translational Research Collaboration Laboratory List'),
            "transrescolldivs" => array('CollDivList','transrescolldivs-list','Translational Research Collaboration Division List'),
            "transresirbstatus" => array('IrbStatusList','transresirbstatus-list','Translational Research Irb Approval Status List'),
            "transresirbapprovaltypes" => array('IrbApprovalTypeList','transresirbapprovaltypes-list','Translational Research Irb Approval Type List"'),
            "transresrequestergroup" => array('RequesterGroupList','transresrequestergroup-list','Translational Research Requester Group List'),
            "transrescomptypes" => array('CompCategoryList','transrescomptypes-list','Translational Research Computational Categories List'),
            "antibodycategorytag" => array('AntibodyCategoryTagList','antibodycategorytag-list','Translational Research Antibody Category Tag List'),
            "antibodylabs" => array('AntibodyLabList','antibodylabs-list','Translational Research Antibody Lab List'),
            "antibodypanels" => array('AntibodyPanelList','antibodypanels-list','Translational Research Antibody Panel List'),

            "visastatus" => array('VisaStatus','visastatus-list','Visa Status'),
            "healthcareprovidercommunication" => array('HealthcareProviderCommunicationList','healthcareprovidercommunication-list','Healthcare Provider Initial Communication List'),
            "additionalcommunications" => array('AdditionalCommunicationList','additionalcommunications-list','Additional Communication List'),

            //"crnentrytags" => array('CrnEntryTagsList','crnentrytags-list','Crn Entry Tags List'),
            "messagetagtypes" => array('MessageTagTypesList','messagetagtypes-list','Message Tag Types List'),
            "messagetags" => array('MessageTagsList','messagetags-list','Message Tags List'),

            "accessionlisthierarchys" => array('AccessionListHierarchy','accessionlisthierarchys-list','Accession List Hierarchy'),
            "accessionlisthierarchygrouptype" => array('AccessionListHierarchyGroupType','accessionlisthierarchygrouptype-list','Accession List Hierarchy Group Type'),
            "accessionlisttype" => array('AccessionListType','accessionlisttype-list','Accession List Type'),

            "resappstatuses" => array('ResAppStatus','resappstatuses-list','Residency Application Status'),
            "resappranks" => array('ResAppRank','resappranks-list','Residency Application Score'),
            "resapplanguageproficiency" => array('LanguageProficiency','resapplanguageproficiency-list','Residency Language Proficiency'),
            "resappvisastatus" => array('VisaStatus','resappvisastatus-list','Residency Visa Status'),
            "postsoph" => array('PostSophList','postsoph-list','Post Soph List'),
            "resappfitforprogram" => array('ResAppFitForProgram','resappfitforprogram-list','Residency Fit For Program'),

            "resappapplyingresidencytrack" => array('ApplyingResidencyTrack','resappapplyingresidencytrack-list','Applying Residency Track'),
            "resapplearnarealist" => array('LearnAreaList','resapplearnarealist-list','Learn Area List'),
            "resappspecificindividuallist" => array('SpecificIndividualList','resappspecificindividuallist-list','Specific Individuals Meet List'),

            "viewmodes" => array('ViewModeList','viewmodes-list','View Mode List'),

            //Dashboards (7 lists)
            "charttypes" => array('ChartTypeList','charttypes-list','Chart Type List'),
            "charttopics" => array('TopicList','charttopics-list','Chart Topic List'),
            "chartfilters" => array('FilterList','chartfilters-list','Chart Filter List'),
            "charts" => array('ChartList','charts-list','Chart List'),
            "chartdatasources" => array('DataSourceList','chartdatasources-list','Chart Data Source List'),
            "chartupdatefrequencies" => array('UpdateFrequencyList','chartupdatefrequencies-list','Chart Update Frequency List'),
            "chartvisualizations" => array('VisualizationList','chartvisualizations-list','Chart Visualization List'),

            "vacreqfloatingtexts" => array('VacReqFloatingTextList','vacreqfloatingtexts-list','Vacation Request Floating Text List'),
            "vacreqfloatingtypes" => array('VacReqFloatingTypeList','vacreqfloatingtypes-list','Vacation Request Floating Type List'),
            "vacreqapprovaltypes" => array('VacReqApprovalTypeList','vacreqapprovaltypes-list','Vacation Request Approval Type List'),
            "vacreqholidays" => array('VacReqHolidayList','vacreqholidays-list','Vacation Request Holidays List'),
            "vacreqobservedholidays" => array('VacReqObservedHolidayList','vacreqobservedholidays-list','Vacation Request Observed Holidays List'),

            "authusergroup" => array('AuthUserGroupList','authusergroup-list','Dual Authentication User Group List'),
            "authservernetwork" => array('AuthServerNetworkList','authservernetwork-list','Dual Authentication Server Network Accessibility and Role'),
            "authpartnerserver" => array('AuthPartnerServerList','authpartnerserver-list','Dual Authentication Tandem Partner Server URL'),
            //"hostedusergroups" => array('HostedUserGroupList','hostedusergroups-list','Hosted User Groups'),
            "tenanturls" => array('TenantUrlList','tenanturls-list','Tenant Url List'),

            "transferstatus" => array('TransferStatusList','transferstatus-list','Transfer Status List'),
            "interfacetransfers" => array('InterfaceTransferList','interfacetransfers-list','Interface Transfer List'),

            "samlconfig" => array('SamlConfig','samlconfig-list','Saml Configuration List'),
        );

        if( $withcustom ) {
            $types = array(
                "custom000" => array('Custom000List','custom000-list','Custom000 List'),
                "custom001" => array('Custom001List','custom001-list','Custom001 List'),
                "custom002" => array('Custom002List','custom002-list','Custom002 List'),
                "custom003" => array('Custom003List','custom003-list','Custom003 List'),
                "custom004" => array('Custom004List','custom004-list','Custom004 List'),
                "custom005" => array('Custom005List','custom005-list','Custom005 List'),
                "custom006" => array('Custom006List','custom006-list','Custom006 List'),
                "custom007" => array('Custom007List','custom007-list','Custom007 List'),
                "custom008" => array('Custom008List','custom008-list','Custom008 List'),
                "custom009" => array('Custom009List','custom009-list','Custom009 List'),
                "custom010" => array('Custom010List','custom010-list','Custom010 List'),
                "custom011" => array('Custom011List','custom011-list','Custom011 List'),
                "custom012" => array('Custom012List','custom012-list','Custom012 List'),
                "custom013" => array('Custom013List','custom013-list','Custom013 List'),
                "custom014" => array('Custom014List','custom014-list','Custom014 List'),
                "custom015" => array('Custom015List','custom015-list','Custom015 List'),
                "custom016" => array('Custom016List','custom016-list','Custom016 List'),
                "custom017" => array('Custom017List','custom017-list','Custom017 List'),
                "custom018" => array('Custom018List','custom018-list','Custom018 List'),
                "custom019" => array('Custom019List','custom019-list','Custom019 List'),
                "custom020" => array('Custom020List','custom020-list','Custom020 List'),
                "custom021" => array('Custom021List','custom021-list','Custom021 List'),
                "custom022" => array('Custom022List','custom022-list','Custom022 List'),
                "custom023" => array('Custom023List','custom023-list','Custom023 List'),
                "custom024" => array('Custom024List','custom024-list','Custom024 List'),
                "custom025" => array('Custom025List','custom025-list','Custom025 List'),
                "custom026" => array('Custom026List','custom026-list','Custom026 List'),
                "custom027" => array('Custom027List','custom027-list','Custom027 List'),
                "custom028" => array('Custom028List','custom028-list','Custom028 List'),
                "custom029" => array('Custom029List','custom029-list','Custom029 List'),
                "custom030" => array('Custom030List','custom030-list','Custom030 List'),
                "custom031" => array('Custom031List','custom031-list','Custom031 List'),
                "custom032" => array('Custom032List','custom032-list','Custom032 List'),
                "custom033" => array('Custom033List','custom033-list','Custom033 List'),
                "custom034" => array('Custom034List','custom034-list','Custom034 List'),
                "custom035" => array('Custom035List','custom035-list','Custom035 List'),
                "custom036" => array('Custom036List','custom036-list','Custom036 List'),
                "custom037" => array('Custom037List','custom037-list','Custom037 List'),
                "custom038" => array('Custom038List','custom038-list','Custom038 List'),
                "custom039" => array('Custom039List','custom039-list','Custom039 List'),
                "custom040" => array('Custom040List','custom040-list','Custom040 List'),
                "custom041" => array('Custom041List','custom041-list','Custom041 List'),
                "custom042" => array('Custom042List','custom042-list','Custom042 List'),
                "custom043" => array('Custom043List','custom043-list','Custom043 List'),
                "custom044" => array('Custom044List','custom044-list','Custom044 List'),
                "custom045" => array('Custom045List','custom045-list','Custom045 List'),
                "custom046" => array('Custom046List','custom046-list','Custom046 List'),
                "custom047" => array('Custom047List','custom047-list','Custom047 List'),
                "custom048" => array('Custom048List','custom048-list','Custom048 List'),
                "custom049" => array('Custom049List','custom049-list','Custom049 List'),
                "custom050" => array('Custom050List','custom050-list','Custom050 List'),
                "custom051" => array('Custom051List','custom051-list','Custom051 List'),
                "custom052" => array('Custom052List','custom052-list','Custom052 List'),
                "custom053" => array('Custom053List','custom053-list','Custom053 List'),
                "custom054" => array('Custom054List','custom054-list','Custom054 List'),
                "custom055" => array('Custom055List','custom055-list','Custom055 List'),
                "custom056" => array('Custom056List','custom056-list','Custom056 List'),
                "custom057" => array('Custom057List','custom057-list','Custom057 List'),
                "custom058" => array('Custom058List','custom058-list','Custom058 List'),
                "custom059" => array('Custom059List','custom059-list','Custom059 List'),
                "custom060" => array('Custom060List','custom060-list','Custom060 List'),
                "custom061" => array('Custom061List','custom061-list','Custom061 List'),
                "custom062" => array('Custom062List','custom062-list','Custom062 List'),
                "custom063" => array('Custom063List','custom063-list','Custom063 List'),
                "custom064" => array('Custom064List','custom064-list','Custom064 List'),
                "custom065" => array('Custom065List','custom065-list','Custom065 List'),
                "custom066" => array('Custom066List','custom066-list','Custom066 List'),
                "custom067" => array('Custom067List','custom067-list','Custom067 List'),
                "custom068" => array('Custom068List','custom068-list','Custom068 List'),
                "custom069" => array('Custom069List','custom069-list','Custom069 List'),
                "custom070" => array('Custom070List','custom070-list','Custom070 List'),
                "custom071" => array('Custom071List','custom071-list','Custom071 List'),
                "custom072" => array('Custom072List','custom072-list','Custom072 List'),
                "custom073" => array('Custom073List','custom073-list','Custom073 List'),
                "custom074" => array('Custom074List','custom074-list','Custom074 List'),
                "custom075" => array('Custom075List','custom075-list','Custom075 List'),
                "custom076" => array('Custom076List','custom076-list','Custom076 List'),
                "custom077" => array('Custom077List','custom077-list','Custom077 List'),
                "custom078" => array('Custom078List','custom078-list','Custom078 List'),
                "custom079" => array('Custom079List','custom079-list','Custom079 List'),
                "custom080" => array('Custom080List','custom080-list','Custom080 List'),
                "custom081" => array('Custom081List','custom081-list','Custom081 List'),
                "custom082" => array('Custom082List','custom082-list','Custom082 List'),
                "custom083" => array('Custom083List','custom083-list','Custom083 List'),
                "custom084" => array('Custom084List','custom084-list','Custom084 List'),
                "custom085" => array('Custom085List','custom085-list','Custom085 List'),
                "custom086" => array('Custom086List','custom086-list','Custom086 List'),
                "custom087" => array('Custom087List','custom087-list','Custom087 List'),
                "custom088" => array('Custom088List','custom088-list','Custom088 List'),
                "custom089" => array('Custom089List','custom089-list','Custom089 List'),
                "custom090" => array('Custom090List','custom090-list','Custom090 List'),
                "custom091" => array('Custom091List','custom091-list','Custom091 List'),
                "custom092" => array('Custom092List','custom092-list','Custom092 List'),
                "custom093" => array('Custom093List','custom093-list','Custom093 List'),
                "custom094" => array('Custom094List','custom094-list','Custom094 List'),
                "custom095" => array('Custom095List','custom095-list','Custom095 List'),
                "custom096" => array('Custom096List','custom096-list','Custom096 List'),
                "custom097" => array('Custom097List','custom097-list','Custom097 List'),
                "custom098" => array('Custom098List','custom098-list','Custom098 List'),
                "custom099" => array('Custom099List','custom099-list','Custom099 List'),
            );
        }

        $count = 10;
        foreach( $types as $listId => $listArr ) {

            $listName = $listArr[0];        //$className
            $listRootName = $listArr[1];    //root

            if( count($listArr) == 3 ) {
                $nameClean = $listArr[2];
            } else {
                $nameClean = null;
            }

            $entityNamespace = "App\\"."UserdirectoryBundle"."\\Entity";

            if( !$listName ) {
                //get it from ScanListController
                $mapper = $scanListController->classListMapper($listId,$request);
                //$className = $mapper['className'];
                //$bundleName = $mapper['bundleName'];
                //$displayName = $mapper['displayName'];
                //$bundleName = str_replace("App","",$bundleName);

                $listName = $mapper['className'];
                $listRootName = $listId.'-list';
                $nameClean = $mapper['displayName'];
                $entityNamespace = $mapper['entityNamespace'];
                //exit('Get from ScanListController: listName='.$listName."; listRootName=".$listRootName."; nameClean=".$nameClean);
            }

//            $listEntity = $em->getRepository('AppUserdirectoryBundle:PlatformListManagerRootList')->findOneByListId($listId);
//            if( $listEntity ) {
//                echo 'exists listId='.$listId."<br>";
//                continue;
//            }

        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:PlatformListManagerRootList'] by [PlatformListManagerRootList::class]
            $listEntity = $em->getRepository(PlatformListManagerRootList::class)->findOneByListRootName($listRootName);
            if( $listEntity ) {
                //exit('exists listRootName='.$listRootName);
                continue;
            }

            //We can have two identical class names (i.e. VisaStatus in FellApp and ResApp bundles)
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:PlatformListManagerRootList'] by [PlatformListManagerRootList::class]
            $listEntity = $em->getRepository(PlatformListManagerRootList::class)->findOneByListName($listName);
            if( $listEntity ) {
                //exit('exists listName='.$listName);
                $listEntityNamespace = $listEntity->getEntityNamespace();
                if( $listEntityNamespace && $entityNamespace && $listEntityNamespace == $entityNamespace ) {
                    continue;
                }
            }

            echo "nameClean=$nameClean || listName=$listName, listRootName=$listRootName <br>";

            //construct the "Name" from the entity name:
            // "SourceSystemList" => "Source System", "BoardCertifiedSpecialties" => "Board Certified Specialty"
            if( !$nameClean ) {
                //1) split with upper case
                $nameArr = $this->splitAtUpperCase($listName);

                //2) remove the last element if == "List"
                $lastIndex = count($nameArr) - 1;
                if ($nameArr[$lastIndex] == "List") {
                    unset($nameArr[$lastIndex]);
                }

                //3) singularize
                $nameArrClean = array();
                foreach ($nameArr as $thisName) {
                    //Countries => Country
                    if (substr((string)$thisName, -3) == "ies") {
                        //$len = strlen((string)$thisName);
                        //$thisName = substr_replace("ies", "y", $len-3, $len);
                        $thisName = $this->str_lreplace("ies", "y", $thisName);
                    }

                    //Roles => Role
                    if (substr((string)$thisName, -2) == "es") {
                        //$len = strlen((string)$thisName);
                        //$thisName = substr_replace("es", "e", $len-2, $len);
                        $thisName = $this->str_lreplace("es", "e", $thisName);
                    }

                    $nameArrClean[] = $thisName;
                }

                $nameClean = implode(" ", $nameArrClean);
            }

            //echo "nameClean=$nameClean || listName=$listName, listRootName=$listRootName <br>";
            //echo "nameClean=$nameClean <br>";

            $listEntity = new PlatformListManagerRootList();
            $this->setDefaultList($listEntity,null,$username,$nameClean);

            //$listEntity->setLinkToListId($listId);
            $listEntity->setListName($listName);
            $listEntity->setListRootName($listRootName);
            $listEntity->setEntityNamespace($entityNamespace);
            $listEntity->setEntityName($listName);

            $em->persist($listEntity);
            //$em->flush($listEntity);
            $em->flush();

            //set linkToListId the same as ID
            if( $listEntity->getId() ) {
                $listEntity->setLinkToListId($listEntity->getId());
                $em->persist($listEntity);
                //$em->flush($listEntity);
                $em->flush();
            }

            $count = $count + 10;
        }

        $res = 'Inserted PlatformListManagerRootList objects count='.round($count/10);

        $this->addFlash(
            'notice',
            $res
        );

        //exit($res);

        //if( $request->get('_route') == "user_populate_platform_list_manager" ) {
        if( $request || $withcustom ) {
            return $this->redirect($this->generateUrl('employees_siteparameters'));
        }

        return round($count/10);
    }
    function splitAtUpperCase($s) {
        return preg_split('/(?=[A-Z])/', $s, -1, PREG_SPLIT_NO_EMPTY);
    }
    function str_lreplace($search, $replace, $subject)
    {
        $pos = strrpos($subject, $search);

        if($pos !== false)
        {
            $subject = substr_replace($subject, $replace, $pos, strlen((string)$search));
        }

        return $subject;
    }


    #[Route(path: '/set-institution-employment-period/', name: 'user_institution_employment_period', methods: ['GET'])]
    public function setInstitutionEmploymentPeriodAction()
    {

        if (false === $this->isGranted('ROLE_PLATFORM_DEPUTY_ADMIN')) {
            return $this->redirect($this->generateUrl($this->getParameter('employees.sitename') . '-nopermission'));
        }

        $userSecUtil = $this->container->get('user_security_utility');
        $em = $this->getDoctrine()->getManager();

        $pathology = $userSecUtil->getAutoAssignInstitution();
        if( !$pathology ) {
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:Institution'] by [Institution::class]
            $wcmc = $em->getRepository(Institution::class)->findOneByAbbreviation("WCM");
            if( !$wcmc ) {
                exit('setInstitutionEmploymentPeriodAction: No Institution: "WCM"');
            }
            $mapper = array(
                'prefix' => 'App',
                'bundleName' => 'UserdirectoryBundle',
                'className' => 'Institution',
                'fullClassName' => "App\\UserdirectoryBundle\\Entity\\Institution",
                'entityNamespace' => "App\\UserdirectoryBundle\\Entity"
            );
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:Institution'] by [Institution::class]
            $pathology = $em->getRepository(Institution::class)->findByChildnameAndParent(
                "Pathology and Laboratory Medicine",
                $wcmc,
                $mapper
            );
        }

        if( !$pathology ) {
            exit('No Institution: "Pathology and Laboratory Medicine"');
        }

        //$query = $em->createQuery('UPDATE AppUserdirectoryBundle:EmploymentStatus p SET p.institution = '.$pathology->getId().' WHERE p.institution IS NULL');
        $query = $em->createQuery('UPDATE App\\UserdirectoryBundle\\Entity\\EmploymentStatus p SET p.institution = '.$pathology->getId().' WHERE p.institution IS NULL');
        $numUpdated = $query->execute();

        exit("set-institution-employment-period; numUpdated=".$numUpdated);
    }

    /**
     * For all users in the live C.MED system EXCEPT FELLOWSHIP APPLICANTS, set "Pathology and Laboratory Medicine"
     */
    #[Route(path: '/set-default-org-group/', name: 'user_set-default-org-group', methods: ['GET'])]
    public function setDefaultOrgGroupAction()
    {

        if (false === $this->isGranted('ROLE_PLATFORM_DEPUTY_ADMIN')) {
            return $this->redirect($this->generateUrl($this->getParameter('employees.sitename') . '-nopermission'));
        }

        $userSecUtil = $this->container->get('user_security_utility');
        $em = $this->getDoctrine()->getManager();

        $pathology = $userSecUtil->getAutoAssignInstitution();
        if( !$pathology ) {
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:Institution'] by [Institution::class]
            $wcmc = $em->getRepository(Institution::class)->findOneByAbbreviation("WCM");
            if (!$wcmc) {
                exit('setDefaultOrgGroupAction: No Institution: "WCM"');
            }
            $mapper = array(
                'prefix' => 'App',
                'bundleName' => 'UserdirectoryBundle',
                'className' => 'Institution',
                'fullClassName' => "App\\UserdirectoryBundle\\Entity\\Institution",
                'entityNamespace' => "App\\UserdirectoryBundle\\Entity"
            );
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:Institution'] by [Institution::class]
            $pathology = $em->getRepository(Institution::class)->findByChildnameAndParent(
                "Pathology and Laboratory Medicine",
                $wcmc,
                $mapper
            );
        }
        if( !$pathology ) {
            exit('No Institution: "Pathology and Laboratory Medicine"');
        }

//        $repository = $em->getRepository(User::class);
//        $dql =  $repository->createQueryBuilder("user");
//        $dql->select('user');
//        $dql->leftJoin("user.perSiteSettings", "perSiteSettings");
//        $dql->leftJoin("user.employmentStatus", "employmentStatus");
//        $dql->leftJoin("employmentStatus.employmentType", "employmentType");
//        $dql->where("perSiteSettings.organizationalGroupDefault IS NULL OR perSiteSettings IS NULL");
//        $dql->andWhere("employmentType.name != :employmentType OR employmentStatus IS NULL");
//
//        $query = $em->createQuery($dql);
//        $query->setParameter('employmentType', "Pathology Fellowship Applicant");
//
//        $users = $query->getResult();

        $users = $em->getRepository(User::class)->findAll();
        echo "user count=".count($users)."<br>";

        $totalCount = 0;
        $count = 0;
        foreach( $users as $user ) {
            echo "<br>".$totalCount.": user=".$user."<br>";

            $employmentStatuses = $user->getEmploymentStatus();
            if( count($employmentStatuses) > 0 ) {
                $employmentStatus = $employmentStatuses->first();
                if( $employmentStatus->getEmploymentType()."" == "Pathology Fellowship Applicant" ) {
                    echo "skip fellowship applicant <br>";
                    continue;
                }
            }

            $userSettings = $user->getPerSiteSettings();
            if( $userSettings ) {
                echo "userSetting=".$userSettings->getId()."; orgGroupDefault=".$userSettings->getOrganizationalGroupDefault()."<br>";
                if( !$userSettings->getOrganizationalGroupDefault() ) {
                    $userSettings->setOrganizationalGroupDefault($pathology);
                    $em->persist($userSettings);
                    //$em->flush($userSettings);
                    $em->flush();
                    $count++;
                } else {
                    //exit('OrganizationalGroupDefault='.$userSettings->getOrganizationalGroupDefault());
                }
            } else {
                $userSettings = new PerSiteSettings();
                $userSettings->setOrganizationalGroupDefault($pathology);
                $user->setPerSiteSettings($userSettings);
                $em->persist($userSettings);
                //$em->flush($userSettings);
                $em->flush();
                $count++;
            }
            $totalCount++;
        }

        exit("<br><br>set-default-org-group; count=".$count);
    }

    #[Route(path: '/convert-logger-site/', name: 'user_convert-logger-site', methods: ['GET'])]
    public function convertLoggerSitenameToSiteObectAction(Request $request) {

        if( false === $this->isGranted('ROLE_PLATFORM_DEPUTY_ADMIN') ) {
            return $this->redirect( $this->generateUrl($this->getParameter('employees.sitename').'-nopermission') );
        }

        $em = $this->getDoctrine()->getManager();

        //$logger = $em->getRepository('AppUserdirectoryBundle:Logger')->find(7789);
        //$loggers = array($logger);
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:Logger'] by [Logger::class]
        $loggers = $em->getRepository(Logger::class)->findAll();

        //map sitename to object
        $siteMap = array(
            'employees' => $this->getDoctrine()->getRepository(SiteList::class)->findOneByAbbreviation('employees'),
            'scan' => $this->getDoctrine()->getRepository(SiteList::class)->findOneByAbbreviation('scan'),
            'fellapp' => $this->getDoctrine()->getRepository(SiteList::class)->findOneByAbbreviation('fellapp'),
            'deidentifier' => $this->getDoctrine()->getRepository(SiteList::class)->findOneByAbbreviation('deidentifier'),
            'vacreq' => $this->getDoctrine()->getRepository(SiteList::class)->findOneByAbbreviation('vacreq'),
            'calllog' => $this->getDoctrine()->getRepository(SiteList::class)->findOneByAbbreviation('calllog'),
        );

        $count = 1;
        foreach( $loggers as $logger ) {

            $site = $logger->getSite();
            if( $site ) {
                continue;
            }

            $site = $siteMap[$logger->getSiteName()];
            if( $site ) {
                $logger->setSite($site);
                //$em->flush($logger);
                $em->flush();

                $count++;
            }
        }

        exit('Inserted site objects to loggers count='.$count);

        $this->addFlash(
            'notice',
            'Inserted site objects to loggers count='.$count
        );

        return $this->redirect($this->generateUrl('employees_siteparameters'));
    }


    /**
     * http://hosthame/order/directory/admin/sync-vacreq-roles/
     */
    #[Route(path: '/sync-vacreq-roles/', name: 'user_vacreq_roles', methods: ['GET'])]
    public function syncVacreqRolesAction()
    {
        if( false === $this->isGranted('ROLE_PLATFORM_DEPUTY_ADMIN') ) {
            return $this->redirect($this->generateUrl('employees-nopermission'));
        }

        $em = $this->getDoctrine()->getManager();
        $roles = $em->getRepository(Roles::class)->findAll();

        $count = 0;

        foreach( $roles as $role ) {
            $thisCount = $this->addVacReqPermission( $role );
            
            if( $thisCount > 0 ) {
                $em->persist($role);
                $em->flush();
            }

            $count = $count + $thisCount;
        }


        $this->addFlash(
            'notice',
            'Vacreq roles sync count='.$count
        );

        exit('EOF syncVacreqRolesAction. count='.$count);
        return $this->redirect($this->generateUrl('employees_siteparameters'));
    }

    /**
     * http://hosthame/order/directory/admin/sync-db/
     */
    #[Route(path: '/sync-db/', name: 'user_sync_db', methods: ['GET'])]
    public function syncEventTypeListDbAction()
    {
        if( false === $this->isGranted('ROLE_PLATFORM_DEPUTY_ADMIN') ) {
            return $this->redirect($this->generateUrl('employees-nopermission'));
        }

        $count = $this->syncEventTypeListDb();
        $this->addFlash(
            'notice',
            'syncEventTypeListDb count='.$count
        );

        $count = $this->syncRolesDb();
        $this->addFlash(
            'notice',
            'sync RolesDb count='.$count
        );

        //List of Research Labs clean
        $count = $this->syncResearchLabsDb();
        $this->addFlash(
            'notice',
            'Research Labs clean count='.$count
        );

        return $this->redirect($this->generateUrl('employees_siteparameters'));
    }
    public function syncEventTypeListDb() {

        $count = 0;

        //User Created -> New user record added
        //$count = $count + $this->singleSyncDb('AppUserdirectoryBundle:EventTypeList',"User Created","New user record added");
        $count = $count + $this->singleSyncDb(EventTypeList::class,"User Created","New user record added");

        //User Updated -> User record updated
        $count = $count + $this->singleSyncDb(EventTypeList::class,"User Updated","User record updated");

        //Populate of Fellowship Applications -> Import of Fellowship Application data to DB
        $count = $count + $this->singleSyncDb(EventTypeList::class,"Populate of Fellowship Applications","Import of Fellowship Application data to DB");

        //Import of Fellowship Applications -> Import of Fellowship Applications Spreadsheet
        $count = $count + $this->singleSyncDb(EventTypeList::class,"Import of Fellowship Applications","Import of Fellowship Applications Spreadsheet");

        //Fellowship Application Resend Emails -> Fellowship Application Rating Invitation Emails Resent
        $count = $count + $this->singleSyncDb(EventTypeList::class,"Fellowship Application Resend Emails","Fellowship Application Rating Invitation Emails Resent");

        //Fellowship Applicant Page Viewed -> Fellowship Application Page Viewed
        $count = $count + $this->singleSyncDb(EventTypeList::class,"Fellowship Applicant Page Viewed","Fellowship Application Page Viewed");

        return $count;
    }
    public function singleSyncDb($repStr,$oldName,$newName) {
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository($repStr)->findOneByName($oldName);
        //echo $repStr . " oldName=" . $oldName . "<br>";
        if( $entity ) {
            $entity->setName($newName);
            //echo $repStr . " name=" . $newName . "<br>";
            $em->persist($entity);
            $em->flush();
            return 1;
        }
        return 0;
    }

    //add sitename to the existing roles using role name
    public function syncRolesDb() {

        $em = $this->getDoctrine()->getManager();
        $roles = $em->getRepository(Roles::class)->findAll();

        $count = 0;

        foreach( $roles as $role ) {

//            if( strpos((string)$role, '_DEIDENTIFICATOR_') !== false ) {
//                $site = $em->getRepository(SiteList::class)->findOneByName('deidentifier');
//                if( $role->getSites() && !$role->getSites()->contains($site) ) {
//                    $role->addSite($site);
//                    $count++;
//                }
//            }
            $resCount = 0;

            $resCount = $resCount + $this->addSites( $role, '_DEIDENTIFICATOR_', 'deidentifier' );

            $resCount = $resCount + $this->addSites( $role, '_VACREQ_', 'time-away-request' );

            $resCount = $resCount + $this->addSites( $role, '_FELLAPP_', 'fellowship-applications' );

            $resCount = $resCount + $this->addSites( $role, '_RESAPP_', 'residency-applications' );

            $resCount = $resCount + $this->addSites( $role, '_SCANORDER_', 'scan' );

            $resCount = $resCount + $this->addSites( $role, '_USERDIRECTORY_', 'directory' );

            $resCount = $resCount + $this->addSites( $role, '_CALLLOG_', 'call-log-book' );

            $resCount = $resCount + $this->addSites( $role, '_CRN_', 'critical-result-notifications' );

            $resCount = $resCount + $this->addSites( $role, '_DASHBOARD_', 'dashboards' ); //Dashboard

            $resCount = $resCount + $this->addFellAppPermission( $role );
            $resCount = $resCount + $this->addResAppPermission( $role );

            $resCount = $resCount + $this->addVacReqPermission( $role );
            $resCount = $resCount + $this->setInstitutionVacReqRole($role); //set institution and Fellowship Subspecialty types to role


            //disable/remove already existing general roles
            if(
                $role == "ROLE_FELLAPP_USER"        ||
                $role == "ROLE_FELLAPP_INTERVIEWER" ||
                $role == "ROLE_FELLAPP_COORDINATOR" ||
                $role == "ROLE_FELLAPP_DIRECTOR"
            ) {
                //$role->setType('disabled');
                //remove role
                foreach( $role->getSites() as $site ) {
                    $role->removeSite($site);
                }
                $em->remove($role);
                $em->flush();
                $count++;
                //$resCount++;
                continue;
            }


            if( $resCount > 0 ) {
                $count++;
                $em->persist($role);
                $em->flush();
            }
        }

        //exit('resCount='.$resCount);
        return $count;
    }
    public function addSites( $role, $roleStr, $sitename ) {
        $count = 0;
        if( strpos((string)$role, $roleStr) !== false ) {
            $em = $this->getDoctrine()->getManager();
            $site = $em->getRepository(SiteList::class)->findOneByName($sitename);
            if( !$site ) {
                $site = $em->getRepository(SiteList::class)->findOneByAbbreviation($sitename);
            }
            if( $role->getSites() && !$role->getSites()->contains($site) ) {
                //echo "add site=".$site."<br>";
                $role->addSite($site);
                $count++;
            }
        }

        //testing
//        foreach($role->getSites() as $site) {
//            echo "site=".$site."<br>";
//        }
//        exit("exit count=".$count.", sites=".count($role->getSites()));

        return $count;
    }
    public function addSingleSite( $role, $roleStr, $sitename ) {
        $count = 0;
        if( strpos((string)$role, $roleStr) !== false ) {
            $em = $this->getDoctrine()->getManager();
            $site = $em->getRepository(SiteList::class)->findOneByName($sitename);
            if( !$site ) {
                $site = $em->getRepository(SiteList::class)->findOneByAbbreviation($sitename);
            }
            if( !$role->getSites()->contains($site) ) {
                $role->addSite($site);
                $count++;
            }
        }
        return $count;
    }
    public function addSingleSiteToEntity( $entity, $siteAbbreviation ) {
        $em = $this->getDoctrine()->getManager();
        $siteObject = $em->getRepository(SiteList::class)->findOneByAbbreviation($siteAbbreviation);
        if( $siteObject ) {
            if( !$entity->getSites()->contains($siteObject) ) {
                $entity->addSite($siteObject);
            }
        }
        return $entity;
    }

    public function addFellAppPermission( $role ) {
        $count = 0;

        $userSecUtil = $this->container->get('user_security_utility');

        //ROLE_FELLAPP_INTERVIEWER: permission="Submit an interview evaluation", object="Interview", action="create"
        if( strpos((string)$role, "ROLE_FELLAPP_INTERVIEWER") !== false ) {
            $count = $count + $userSecUtil->checkAndAddPermissionToRole($role,"Submit an interview evaluation","Interview","create");
        }

        //ROLE_FELLAPP_DIRECTOR:
        //ROLE_FELLAPP_COORDINATOR:
        // permission="Create a New Fellowship Application", object="FellowshipApplication", action="create"
        // permission="Modify a Fellowship Application", object="FellowshipApplication", action="update"
        if( strpos((string)$role, "ROLE_FELLAPP_COORDINATOR") !== false || strpos((string)$role, "ROLE_FELLAPP_DIRECTOR") !== false ) {
            $count = $count + $userSecUtil->checkAndAddPermissionToRole($role,"Create a New Fellowship Application","FellowshipApplication","create");
            $count = $count + $userSecUtil->checkAndAddPermissionToRole($role,"Modify a Fellowship Application","FellowshipApplication","update");
        }

        //ROLE_FELLAPP_OBSERVER: permission="View a Fellowship Application", object="FellowshipApplication", action="read"
        if( strpos((string)$role, "ROLE_FELLAPP_OBSERVER") !== false ) {
            $count = $count + $userSecUtil->checkAndAddPermissionToRole($role,"View a Fellowship Application","FellowshipApplication","read");
        }

        return $count;
    }
    //$role - Role Name (string: "ROLE_RESAPP_INTERVIEWER_WCM_AP")
    public function addResAppPermission( $role ) {
        $count = 0;

        $userSecUtil = $this->container->get('user_security_utility');

        //                            $role, $permissionListStr,                $permissionObjectListStr,   $permissionActionListStr
        //checkAndAddPermissionToRole($role, "Submit an interview evaluation",  "Interview",                "create")

        //ROLE_RESAPP_INTERVIEWER: permission="Submit an interview evaluation", object="Interview", action="create"
        if( strpos((string)$role, "ROLE_RESAPP_INTERVIEWER") !== false ) {
            $count = $count + $userSecUtil->checkAndAddPermissionToRole($role,"Submit an interview evaluation","Interview","create");
        }

        //ROLE_RESAPP_DIRECTOR:
        //ROLE_RESAPP_COORDINATOR:
        // permission="Create a New Residency Application", object="ResidencyApplication", action="create"
        // permission="Modify a Residency Application", object="ResidencyApplication", action="update"
        if( strpos((string)$role, "ROLE_RESAPP_COORDINATOR") !== false || strpos((string)$role, "ROLE_RESAPP_DIRECTOR") !== false ) {
            $count = $count + $userSecUtil->checkAndAddPermissionToRole($role,"Create a New Residency Application","ResidencyApplication","create");
            $count = $count + $userSecUtil->checkAndAddPermissionToRole($role,"Modify a Residency Application","ResidencyApplication","update");
        }

        //ROLE_FELLAPP_OBSERVER: permission="View a Residency Application", object="ResidencyApplication", action="read"
        if( strpos((string)$role, "ROLE_RESAPP_OBSERVER") !== false ) {
            $count = $count + $userSecUtil->checkAndAddPermissionToRole($role,"View a Residency Application","ResidencyApplication","read");
        }

        return $count;
    }

    public function addVacReqPermission( $role ) {
        $count = 0;

        $userSecUtil = $this->container->get('user_security_utility');

        //ROLE_VACREQ_APPROVER: permission="Approve a Vacation Request", object="VacReqRequest", action="changestatus"
        if( strpos((string)$role, "ROLE_VACREQ_APPROVER") !== false ) {
            //$count = $count + $userSecUtil->checkAndAddPermissionToRole($role,"Submit a Vacation Request","VacReqRequest","create");
            $count = $count + $userSecUtil->checkAndAddPermissionToRole($role,"Approve a Vacation Request","VacReqRequest","changestatus");
            $count = $count + $userSecUtil->checkAndAddPermissionToRole($role,"Approve a Floating Day Request","VacReqRequestFloating","changestatus");
        }

        //ROLE_VACREQ_APPROVER: permission="Approve a Vacation Request", object="VacReqRequest", action="create"
        if( strpos((string)$role, "ROLE_VACREQ_SUBMITTER") !== false ) {
            $count = $count + $userSecUtil->checkAndAddPermissionToRole($role,"Submit a Vacation Request","VacReqRequest","create");
            $count = $count + $userSecUtil->checkAndAddPermissionToRole($role,"Submit a Floating Day Request","VacReqRequestFloating","create");
        }

        //ROLE_VACREQ_SUPERVISOR: permission="Approve a Carry Over Request", object="VacReqRequest", action="changestatus-carryover"
        if( strpos((string)$role, "ROLE_VACREQ_SUPERVISOR") !== false ) {
            $count = $count + $userSecUtil->checkAndAddPermissionToRole($role,"Submit a Vacation Request","VacReqRequest","create");
            $count = $count + $userSecUtil->checkAndAddPermissionToRole($role,"Approve a Vacation Request","VacReqRequest","changestatus");
            $count = $count + $userSecUtil->checkAndAddPermissionToRole($role,"Approve a Carry Over Request","VacReqRequest","changestatus-carryover");

            $count = $count + $userSecUtil->checkAndAddPermissionToRole($role,"Submit a Floating Day Request","VacReqRequestFloating","create");
            $count = $count + $userSecUtil->checkAndAddPermissionToRole($role,"Approve a Floating Day Request","VacReqRequestFloating","changestatus");
        }

        if( strpos((string)$role, "ROLE_VACREQ_PROXYSUBMITTER") !== false ) {
            $count = $count + $userSecUtil->checkAndAddPermissionToRole($role,"Proxy Submit a Vacation Request","VacReqRequest","create");
            $count = $count + $userSecUtil->checkAndAddPermissionToRole($role,"Proxy Submit a Floating Day Request","VacReqRequestFloating","create");
        }

        return $count;
    }


    public function syncResearchLabsDb() {

        //check
        $em = $this->getDoctrine()->getManager();
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:ResearchLab'] by [ResearchLab::class]
        $researchLabs = $em->getRepository(ResearchLab::class)->findBy(array(),array('name'=>'asc','id'=>'asc'));
        //echo "researchLab count=".count($researchLabs)."<br>";

        $count = 0;
        $currentResLab = null;

        foreach( $researchLabs as $researchLab ) {

            //echo "researchLab:".$researchLab->getId()." => ".$researchLab->getName()."<br>";
            if( $currentResLab == null || $currentResLab->getName() != $researchLab->getName() ) {
                //if( $researchLab->getType() != "default" ) {
                //    exit("researchLab type is not default: type=".$researchLab->getType());
                //}
                $currentResLab = $researchLab;
                continue;
            }

            //1) re-assign all users from $rgiesearchLab to $currentResLab
            foreach( $researchLab->getUser() as $user ) {
                //remove
                $researchLab->removeUser($user);
                $user->removeResearchLab($researchLab);
                //add
                $user->addResearchLab($currentResLab);
            }

            //2) remove $researchLab if no user attached to it
            if( count($researchLab->getUser()) == 0 ) {
                $em->remove($researchLab);
                $em->flush();
                //echo $researchLab->getId().": researchLab removed <br>";
                $count++;
            } else {
                exit("There are still users attached to researchLab: user count=".count($researchLab->getUser()));
            }

        }//foreach

        //echo "removed count=".$count."<br>";
        //exit('1');
        return $count;
    }


    ////////////////// Employee Tree Util //////////////////////
    // #[Route(path: '/list/hostedusergroups-tree/', name: 'employees_tree_hostedusergroups_list', methods: ['GET'])]
    //to initialize JS, add "getJstree('OrderformBundle','MessageCategory');" to user-formReady.js
    #[Route(path: '/list/institutional-tree/', name: 'employees_tree_institutiontree_list', methods: ['GET'])]
    #[Route(path: '/list/comment-tree/', name: 'employees_tree_commenttree_list', methods: ['GET'])]
    #[Route(path: '/list/form-tree/', name: 'employees_tree_formnode_list', methods: ['GET'])]
    #[Route(path: '/list/message-categories-tree/', name: 'employees_tree_messagecategories_list', methods: ['GET'])]
    #[Route(path: '/list/charttypes-tree/', name: 'employees_tree_charttypes_list', methods: ['GET'])]
    #[Route(path: '/list/charttopics-tree/', name: 'employees_tree_charttopics_list', methods: ['GET'])]
    #[Route(path: '/list/tenanturls-tree/', name: 'employees_tree_tenanturls_list', methods: ['GET'])]
    public function institutionTreeAction(Request $request)
    {
        if( false === $this->isGranted('ROLE_USERDIRECTORY_OBSERVER') ) {
            return $this->redirect( $this->generateUrl($this->getParameter('employees.sitename').'-nopermission') );
        }

        return $this->compositeTree($request,$this->getParameter('employees.sitename'));
    }

    //To load JS tree, add getJstree with class, i.e. getJstree('UserdirectoryBundle','Institution');
    //Set the organizational group in TreeController->classMapper (now it is optional)
    //Add options: ['expose' => true] to the associated route names
    public function compositeTree(Request $request, $sitename)
    {
        $mapper = $this->getMapper($request->get('_route'));

//        //show html tree
//        if( 0 ) {
//            $em = $this->getDoctrine()->getManager();
//            $repo = $em->getRepository($mapper['bundlePreffix'].$mapper['bundleName'].':'.$mapper['className']);
//            $htmlTree = $repo->childrenHierarchy(
//                null, /* starting from root nodes */
//                false, /* true: load all children, false: only direct */
//                array(
//                    'decorate' => true,
//                    'representationField' => 'slug',
//                    'html' => true
//                )
//            );
//            echo $htmlTree;
//        }

        //$filterList = array('default','user-added','disabled','draft');
        //$filters = trim((string)$request->get('filters') );
//        $filter = null;
//        if( $filters ) {
//            $filter = implode(",", $filters);
//        }
        //get filter types from request
        $filterform = $this->createForm(HierarchyFilterType::class,null,array('form_custom_value'=>null));
        $formname = $filterform->getName();
        //dump($formname);
        //exit('111');
        //Error: Input value "filter" contains a non-scalar value.
        //The root cause for this is Symfony's InputBag, in that they changed their get()
        //method to only allow scalar values to be returned - thus not arrays!
        //=> replace $request->query->get($formname) by $request->query->all($formname)
        //$formData = $request->query->get($formname);
        $formData = $request->query->all($formname);
        if( isset($formData['types']) ) {
            $types = $formData['types'];
        } else {
            $types = NULL;
        }
        //print_r($types);
//        if( !$types ) {
//            $types = array(
//                "default"=>"default",
//                "user-added"=>"user-added",
//            );
//        }
        $params = array('types'=>$types);
        //create final filter form with data params
        $filterform = $this->createForm(HierarchyFilterType::class,null,array('form_custom_value'=>$params));
        //$filterform->submit($request);
        //$filterform->handleRequest($request);
        //$types = $filterform['types']->getData();
        //$types = $filterform->get('types')->getData();
        //$data = $filterform->getData();
        //$types = $data['types'];
        //$types = $filterform->get('types');
        //var_dump($filterform->getData());die;
        //echo "types=".$types."<br>";
        //print_r($types);
        //die;
//        if( $types ) {
//            $dql->andWhere("ent.id LIKE :search OR ent.name LIKE :search OR ent.abbreviation LIKE :search OR ent.shortname LIKE :search OR ent.description LIKE :search");
//            $dqlParameters['search'] = '%'.$search.'%';
//        }

        return $this->render('AppUserdirectoryBundle/Tree/composition-tree.html.twig',
            array(
                'title' => $mapper['title'],
                'bundleName' => $mapper['bundleName'],
                'entityName' => $mapper['className'],
                'nodeshowpath' => $mapper['nodeshowpath'],
                'sitename' => $sitename,
                'filterform' => $filterform->createView(),
                //'filters' => $filters,
                //'filterList' => $filterList,
                'routename' => $request->get('_route')
            )
        );
    }


    public function getMapper($routeName) {

        $bundlePreffix = "App";
        $bundleName = "UserdirectoryBundle";
        $className = null;
        $title = null;
        $nodeshowpath = null;

        if( $routeName == "employees_tree_institutiontree_list" ) {
            $bundleName = "UserdirectoryBundle";
            $className = "Institution";
            $title = "Institutional Tree Management";
            $nodeshowpath = "institutions_show";
        }

        if( $routeName == "employees_tree_commenttree_list" ) {
            $bundleName = "UserdirectoryBundle";
            $className = "CommentTypeList";
            $title = "Comment Type Tree Management";
            $nodeshowpath = "commenttypes_show";
        }

        if( $routeName == "employees_tree_formnode_list" ) {
            $bundleName = "UserdirectoryBundle";
            $className = "FormNode";
            $title = "Form Tree Management";
            $nodeshowpath = "formnodes_show";
        }

        if( $routeName == "employees_tree_messagecategories_list" ) {
            $bundleName = "OrderformBundle";
            $className = "MessageCategory";
            $title = "Message Categories Tree Management";
            $nodeshowpath = "messagecategorys_show";
        }

        if( $routeName == "employees_tree_charttypes_list" ) {
            $bundleName = "DashboardBundle";
            $className = "ChartTypeList";
            $title = "Chart Types Tree Management";
            $nodeshowpath = "charttypes_show";
        }
        if( $routeName == "employees_tree_charttopics_list" ) {
            $bundleName = "DashboardBundle";
            $className = "TopicList";
            $title = "Chart Topics Tree Management";
            $nodeshowpath = "charttopics_show";
        }
//        if( $routeName == "employees_tree_hostedusergroups_list" ) {
//            $bundleName = "UserdirectoryBundle";
//            $className = "HostedUserGroupList";
//            $title = "Hosted User Group (Tenant IDs) Tree Management";
//            $nodeshowpath = "hostedusergroups_show";
//        }
        if( $routeName == "employees_tree_tenanturls_list" ) {
            $bundleName = "UserdirectoryBundle";
            $className = "TenantUrlList";
            $title = "Tenenat Url Tree Management";
            $nodeshowpath = "tenanturls_show";
        }

        $mapper = array(
            'bundlePreffix' => $bundlePreffix,
            'bundleName' => $bundleName,
            'className' => $className,
            'title' => $title,
            'nodeshowpath' => $nodeshowpath,
            'fullClassName' => $bundlePreffix."\\".$bundleName."\\Entity\\".$className,
            'entityNamespace' => $bundlePreffix."\\".$bundleName."\\Entity"
        );

        return $mapper;
    }

    public function generateAdministratorAction($force=false) {

        if( $force == false ) {
            if (false === $this->isGranted('ROLE_PLATFORM_DEPUTY_ADMIN')) {
                //exit('testing0');
                return $this->redirect($this->generateUrl('employees-nopermission'));
            }
        }

        $logger = $this->container->get('logger');
        $em = $this->getDoctrine()->getManager();

        //$user = $this->getUser();
        $primaryPublicUserId = 'administrator';
        //$primaryPublicUserId = 'Administrator1';

        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:UsernameType'] by [UsernameType::class]
        $localUserType = $em->getRepository(UsernameType::class)->findOneByAbbreviation('local-user');

        $administrators = $em->getRepository(User::class)->findBy(
            array(
                'primaryPublicUserId' => $primaryPublicUserId,
                'keytype' => $localUserType->getId()
            )
        );
        $logger->notice("generate AdministratorAction: count=".count($administrators));
        //exit('testing1');

        if( $administrators && count($administrators) > 1 ) {
            return "Found multiple $primaryPublicUserId. Found ".count($administrators)."users";
            //throw new \Exception( "Found multiple $primaryPublicUserId . Found ".count($administrators)."users" );
        }

        if( $administrators && count($administrators) == 1 ) {
            $administrator = $administrators[0];
        } else {
            $administrator = NULL;
        }

        //$encoder = $this->container->get('security.password_encoder');
        //echo 'testing2 <br>';

        //$administrator = NULL; //testing
        if( $administrator ) {

            $logger->notice("generate AdministratorAction: Existed administrator=".$administrator);

            $flush = false;
            $res = $primaryPublicUserId." user already exists.";

            //echo 'testing3 <br>';

            ////////////// Update password ///////////////////
//            if(0) {
//                //$encodedPassword = $encoder->encodePassword($administrator, "1234567890");
//                $authUtil = $this->container->get('authenticator_utility');
//                $encodedPassword = $authUtil->getEncodedPassword($administrator, "1234567890");
//                //echo 'testing4 $encodedPassword=['.$encodedPassword.']<br>';
//                //$encodedPassword = strval($encodedPassword);
//                $encodedPassword = (string)$encodedPassword;
//
//                $bool = hash_equals($administrator->getPassword(), $encodedPassword);
//
//                //echo "admin id=".$administrator->getId()."<br>";
//
//                //echo 'testing4 $encodedPassword=['.$encodedPassword.']<br>';
//                //exit('111');
//                //return 'testing res='.$res.', $encodedPassword='.$encodedPassword;
//
//                if ($bool == false) {
//                    $administrator->setPassword($encodedPassword);
//                    $flush = true;
//                    $res .= " Password updated.";
//                }
//            }
            ////////////// EOF Update password ///////////////////

            ////////////// Update Role ///////////////////
            if (!$administrator->hasRole('ROLE_PLATFORM_ADMIN')) {
                $administrator->addRole('ROLE_PLATFORM_ADMIN');
                $flush = true;
                $res .= " Role ROLE_PLATFORM_ADMIN added.";
            }
            ////////////// EOF Update Role ///////////////////

            //assign PHI scope to administrator (permittedInstitutionalPHIScope, user_perSiteSettings_permittedInstitutionalPHIScope)
            if( $administrator->getPerSiteSettings() ) {
                $phis =  $administrator->getPerSiteSettings()->getPermittedInstitutionalPHIScope();
                if( count($phis) == 0 ) {
                    $wcmc = $em->getRepository(Institution::class)->findOneByAbbreviation("WCM");
                    if( $wcmc ) {
                        $administrator->getPerSiteSettings()->addPermittedInstitutionalPHIScope($wcmc);
                        $flush = true;
                        $res .= " Added PHI scope to administrator.";
                    }
                }
            } 

            //echo 'testing5 <br>';

            if( $flush ) {
                $logger->notice("generate AdministratorAction: before flush administrator=" . $administrator);
                $em->persist($administrator);
                //$em->flush($administrator);
                $em->flush();
                $logger->notice("generate AdministratorAction: after flush administrator=" . $administrator);
                //echo "flash ";
            } else {
                //echo "no flash ";
            }


            //$res = "test res";
            //return 'testing res='.$res;
            //exit('testing6 res='.$res.', encodedPassword='.$encodedPassword.'<br>');

        } else {

            $logger->notice("generate AdministratorAction: create new administrator.");

            $userSecUtil = $this->container->get('user_security_utility');
            $administrator = $userSecUtil->constractNewUser($primaryPublicUserId.'_@_local-user');

            $systemEmail = $userSecUtil->getSiteSettingParameter('siteEmail');
            $administrator->setEmail($systemEmail);
            $administrator->setEmailCanonical($systemEmail);
            $administrator->setCreatedby('system');
            $administrator->addRole('ROLE_PLATFORM_ADMIN');
            $administrator->setEnabled(true);
            //$administrator->setLocked(true);
            //$administrator->setExpired(false);

            //$encodedPassword = $encoder->encodePassword($administrator, "1234567890");
            $authUtil = $this->container->get('authenticator_utility');
            $encodedPassword = $authUtil->getEncodedPassword($administrator, "1234567890");
            //$encodedPassword = $argon2id$v=19$m=65536,t=4,p=1$qQUcnDgdNns+KHgHyFrTXQ$XDuWLo1F2TyPhxzEOp8fZ0zXX94EYSACt+f/vjOZYX4
            //exit("encodedPassword=$encodedPassword"); //testing
            $administrator->setPassword($encodedPassword);

            $default_time_zone = $this->getParameter('default_time_zone');
            $administrator->getPreferences()->setTimezone($default_time_zone);

            $res = "Congratulations! You have successfully installed the system.".
                " Please select 'Local User' from the menu below and enter the user name 'administrator' and the password '1234567890' to log in.".
                " Then visit [/order/directory/settings/] and run the initialization scripts 1 through 7".
                " in the listed order (skipping 4a and 4b) in the Miscellaneous section.".
                " After that, change the administrator password!";

            $logger->notice("generate AdministratorAction: before flush new administrator=".$administrator);
            $em->persist($administrator);
            //$em->flush($administrator);
            $em->flush();
            $logger->notice("generate AdministratorAction: after flush new administrator=".$administrator);
        }
        //exit('testing');

        $logger->notice("Finished generate AdministratorAction: res=".$res);
        return $res;
    }

    #[Route(path: '/list/generate-form-node-tree/', name: 'employees_generate_form_node_tree', methods: ['GET'])]
    public function generateFormNodeAction(Request $request)
    {
        if( false === $this->isGranted('ROLE_PLATFORM_DEPUTY_ADMIN') ) {
            return $this->redirect( $this->generateUrl($this->getParameter('employees.sitename').'-nopermission') );
        }

        $formNodeUtil = $this->container->get('user_formnode_utility');
        $count = $formNodeUtil->generateFormNode();

        $this->addFlash(
            'notice',
            'CallLog Form Node Fields generated='.$count
        );

        return $this->redirect($this->generateUrl('employees_siteparameters'));
        //exit("Form Node Tree generated: ".$count);
    }

    #[Route(path: '/list/generate-dermatopathology-form-node-tree/', name: 'employees_generate_dermatopathology_form_node_tree', methods: ['GET'])]
    public function generateFormNodeDermatopathologyAction(Request $request)
    {
        if( false === $this->isGranted('ROLE_PLATFORM_DEPUTY_ADMIN') ) {
            return $this->redirect( $this->generateUrl($this->getParameter('employees.sitename').'-nopermission') );
        }

        $formNodeUtil = $this->container->get('user_formnode_utility');
        $count = $formNodeUtil->generateDermatopathologyFormNode();

        $this->addFlash(
            'notice',
            'Dermatopathology Form Node Fields generated='.$count
        );

        return $this->redirect($this->generateUrl('employees_siteparameters'));
        //exit("Form Node Tree generated: ".$count);
    }

    #[Route(path: '/list/generate-test-form-node-tree/', name: 'employees_generate_test_form_node_tree', methods: ['GET'])]
    public function generateTestFormNodeAction(Request $request)
    {
        if( false === $this->isGranted('ROLE_PLATFORM_DEPUTY_ADMIN') ) {
            return $this->redirect( $this->generateUrl($this->getParameter('employees.sitename').'-nopermission') );
        }

        $formNodeUtil = $this->container->get('user_formnode_utility');
        $formNodeUtil->createTestFormNodes();

        exit("Test Form Node Tree generated");
    }

    #[Route(path: '/list/generate-cron-jobs/', name: 'user_populate_cron_jobs', methods: ['GET'])]
    public function generateCronJobsAction(Request $request)
    {
        if( false === $this->isGranted('ROLE_PLATFORM_DEPUTY_ADMIN') ) {
            return $this->redirect( $this->generateUrl($this->getParameter('employees.sitename').'-nopermission') );
        }

        $userServiceUtil = $this->container->get('user_service_utility');
        $userServiceUtil->createCrons();

        $this->addFlash(
            'notice',
            'All cron Jobs are generated (Email, Fellowship Import, Unpaid Invoices).'
        );

        //return $this->redirect($this->generateUrl('employees_siteparameters'));
        return $this->redirect($this->generateUrl('employees_general_cron_jobs'));
        //exit("Form Node Tree generated: ".$count);
    }

    #[Route(path: '/list/generate-useradstatus-cron/', name: 'user_generate_useradstatus_cron', methods: ['GET'])]
    public function generateUserADStatusCronAction(Request $request)
    {
        if( false === $this->isGranted('ROLE_PLATFORM_DEPUTY_ADMIN') ) {
            return $this->redirect( $this->generateUrl($this->getParameter('employees.sitename').'-nopermission') );
        }

        $userServiceUtil = $this->container->get('user_service_utility');
        $res = $userServiceUtil->createUserADStatusCron('6h');

        $this->addFlash(
            'notice',
            $res //'User AD status cron job is generated.'
        );

        //return $this->redirect($this->generateUrl('employees_siteparameters'));
        return $this->redirect($this->generateUrl('employees_general_cron_jobs'));
    }

    #[Route(path: '/list/generate-cron-jobs/status', name: 'user_populate_cron_status_jobs', methods: ['GET'])]
    public function generateCronStatusJobAction(Request $request)
    {
        if( false === $this->isGranted('ROLE_PLATFORM_DEPUTY_ADMIN') ) {
            return $this->redirect( $this->generateUrl($this->getParameter('employees.sitename').'-nopermission') );
        }

        $userServiceUtil = $this->container->get('user_service_utility');

        $res = $userServiceUtil->createStatusCronLinux();

        //add test job
        //$userServiceUtil->createTestStatusCronLinux();

        $this->addFlash(
            'notice',
            'Status cron job to check the Maintenance state: '.$res
        );

        //return $this->redirect($this->generateUrl('employees_siteparameters'));
        return $this->redirect($this->generateUrl('employees_general_cron_jobs'));
        //exit("Form Node Tree generated: ".$count);
    }

    #[Route(path: '/list/generate-cron-jobs/statustest', name: 'user_populate_cron_status_test_jobs', methods: ['GET'])]
    public function generateCronStatusTestJobAction(Request $request)
    {
        if( false === $this->isGranted('ROLE_PLATFORM_DEPUTY_ADMIN') ) {
            return $this->redirect( $this->generateUrl($this->getParameter('employees.sitename').'-nopermission') );
        }

        $userServiceUtil = $this->container->get('user_service_utility');

        //add test job
        $res = $userServiceUtil->createTestStatusCronLinux();

        $this->addFlash(
            'notice',
            $res
        );

        //return $this->redirect($this->generateUrl('employees_siteparameters'));
        return $this->redirect($this->generateUrl('employees_general_cron_jobs'));
        //exit("Form Node Tree generated: ".$count);
    }

    #[Route(path: '/list/generate-cron-jobs/externalurlmonitor', name: 'user_generate_cron_externalurlmonitor', methods: ['GET'])]
    public function generateExternalUrlMonitorCronAction(Request $request)
    {
        if( false === $this->isGranted('ROLE_PLATFORM_DEPUTY_ADMIN') ) {
            return $this->redirect( $this->generateUrl($this->getParameter('employees.sitename').'-nopermission') );
        }

        $userServiceUtil = $this->container->get('user_service_utility');

        //add ExternalUrlMonitor: view-test monitors view
        $res = $userServiceUtil->createExternalUrlMonitorCronLinux();

        $this->addFlash(
            'notice',
            $res
        );

        return $this->redirect($this->generateUrl('employees_siteparameters'));
    }

    #[Route(path: '/list/generate-cron-jobs/independentmonitor', name: 'user_generate_cron_independentmonitor', methods: ['GET'])]
    public function generateIndependentMonitorCronAction(Request $request)
    {
        if( false === $this->isGranted('ROLE_PLATFORM_DEPUTY_ADMIN') ) {
            return $this->redirect( $this->generateUrl($this->getParameter('employees.sitename').'-nopermission') );
        }

        $userServiceUtil = $this->container->get('user_service_utility');

        //add independent monitor (i.e. python)
        $res = $userServiceUtil->createIndependentMonitorCronLinux();

        $this->addFlash(
            'notice',
            $res
        );

        return $this->redirect($this->generateUrl('employees_siteparameters'));
    }

    #[Route(path: '/list/create-cron-job/{cronJobName}/{configFieldName}', name: 'user_create_cron_job', methods: ['GET'])]
    public function createCronJobAction(Request $request, $cronJobName, $configFieldName)
    {
        if( false === $this->isGranted('ROLE_PLATFORM_DEPUTY_ADMIN') ) {
            return $this->redirect( $this->generateUrl($this->getParameter('employees.sitename').'-nopermission') );
        }

        $userServiceUtil = $this->container->get('user_service_utility');

        //createBackupCronLinux( $commandName="filesbackup", $configFieldName="dbBackupConfig" )
        $res = $userServiceUtil->createBackupCronLinux($cronJobName,$configFieldName);

        $this->addFlash(
            'notice',
            $res
        );

        //exit('111');
        return $this->redirect($this->generateUrl('employees_data_backup_management'));
    }
    #[Route(path: '/list/remove-cron-job/{cronJobName}', name: 'user_remove_cron_job', methods: ['GET'])]
    public function removeCronJobAction(Request $request, $cronJobName)
    {
        if( false === $this->isGranted('ROLE_PLATFORM_DEPUTY_ADMIN') ) {
            return $this->redirect( $this->generateUrl($this->getParameter('employees.sitename').'-nopermission') );
        }

        $redirectPath = 'employees_data_backup_management';
        if(
            str_contains($cronJobName, 'useradstatus') ||
            str_contains($cronJobName, 'status') ||
            str_contains($cronJobName, 'statustest') ||
            str_contains($cronJobName, 'swift') ||
            str_contains($cronJobName, 'importfellapp') ||
            str_contains($cronJobName, 'verifyimport') ||
            str_contains($cronJobName, 'invoice-reminder-emails') ||
            str_contains($cronJobName, 'expiration-reminder-emails') ||
            str_contains($cronJobName, 'project-sync')
        ) {
            $redirectPath = 'employees_general_cron_jobs';
        }
        if(
            str_contains($cronJobName, 'externalurlmonitor') ||
            str_contains($cronJobName, 'webmonitor')
        ) {
            $redirectPath = 'employees_health_monitor';
        }

        $userServiceUtil = $this->container->get('user_service_utility');

        if( $userServiceUtil->isWindows() ){
            $this->addFlash(
                'warning',
                "Windows is not supported"
            );
            return $this->redirect($this->generateUrl($redirectPath));
        }

        //$commandName = "cron:".$cronJobName;
        $commandName = $cronJobName;

        //remove test job
        $userServiceUtil->removeCronJobLinuxByCommandName($commandName);

        $this->addFlash(
            'notice',
            'Cron job '.$cronJobName.' has been removed.'
        );

        return $this->redirect($this->generateUrl($redirectPath));
    }
    #[Route(path: '/list/update-cron-job/{cronJobName}/{configFieldName}', name: 'user_update_cron_job', methods: ['GET'])]
    public function updateCronJobAction(Request $request, $cronJobName, $configFieldName)
    {
        if( false === $this->isGranted('ROLE_PLATFORM_DEPUTY_ADMIN') ) {
            return $this->redirect( $this->generateUrl($this->getParameter('employees.sitename').'-nopermission') );
        }

        //exit("not implemented");

        $userServiceUtil = $this->container->get('user_service_utility');

        if( $userServiceUtil->isWindows() ){
            $this->addFlash(
                'warning',
                "Windows is not supported"
            );
            return $this->redirect($this->generateUrl('employees_data_backup_management'));
        }

        $commandName = $cronJobName;

        //remove cron job
        $res = $userServiceUtil->removeCronJobLinuxByCommandName($commandName);
        if( $res ) {
            $this->addFlash(
                'notice',
                'Cron job ' . $cronJobName . ' has been removed.'
            );
        }

        //create cron job
        $res = $userServiceUtil->createBackupCronLinux($cronJobName,$configFieldName);
        $this->addFlash(
            'notice',
            $res
        );

        return $this->redirect($this->generateUrl('employees_data_backup_management'));
    }

    #[Route(path: '/list/init-dashboard-charts', name: 'user_init_dashboard_charts', methods: ['GET'])]
    public function initDashboardChartsAction(Request $request)
    {
        if( false === $this->isGranted('ROLE_PLATFORM_DEPUTY_ADMIN') ) {
            return $this->redirect( $this->generateUrl($this->getParameter('employees.sitename').'-nopermission') );
        }

        //run after populating chart and topic
        $dashboardInit = $this->container->get('dashboard_init');

        //$testing = true;
        $testing = false;

        $chartInitCount = $dashboardInit->initCharts($testing);

        $this->addFlash(
            'notice',
            'Initialized '.$chartInitCount.' charts'
        );

        return $this->redirect($this->generateUrl('employees_siteparameters'));
    }

    //Blood Product Transfused
    public function generateBloodProductTransfused() {

        $username = $this->getUser();
        $em = $this->getDoctrine()->getManager();

        $types = array(
            "Red blood cells",
            "Platelets",
            "Plasma",
            "Cryoprecipitate",
            "Stem cells",
            "Other"
        );

        $count = 10;
        foreach( $types as $name ) {

        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:BloodProductTransfusedList'] by [BloodProductTransfusedList::class]
            $listEntity = $em->getRepository(BloodProductTransfusedList::class)->findOneByName($name);
            if( $listEntity ) {
                continue;
            }

            $listEntity = new BloodProductTransfusedList();
            $this->setDefaultList($listEntity,$count,$username,$name);

            //exit('exit generateObjectTypeActions');
            $em->persist($listEntity);
            $em->flush();

            $count = $count + 10;
        }

        return round($count/10);
    }

    //ClericalErrorList
    public function generateClericalErrorList() {

        $username = $this->getUser();
        $em = $this->getDoctrine()->getManager();

        $types = array(
            "Yes",
            "None",
        );

        $count = 10;
        foreach( $types as $name ) {

        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:ClericalErrorList'] by [ClericalErrorList::class]
            $listEntity = $em->getRepository(ClericalErrorList::class)->findOneByName($name);
            if( $listEntity ) {
                continue;
            }

            $listEntity = new ClericalErrorList();
            $this->setDefaultList($listEntity,$count,$username,$name);

            //exit('exit generateObjectTypeActions');
            $em->persist($listEntity);
            $em->flush();

            $count = $count + 10;
        }

        return round($count/10);
    }

    //LabResultNames
    public function generateLabResultNames() {
        $username = $this->getUser();
        $em = $this->getDoctrine()->getManager();

        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:LabResultNameList'] by [LabResultNameList::class]
        $entities = $em->getRepository(LabResultNameList::class)->findAll();
        if( count($entities) > 3 ) {
            //return -1;
        }

        ini_set('max_execution_time', 3600);

        //$inputFileName = __DIR__ . '/../Util/Lab Result Names For Import.xlsx';
        //Lab Result Names For Import 2.xlsx
        $inputFileName = __DIR__ . '/../Util/Lab Result Names For Import.xlsx';

        try {
            $inputFileType = \PhpOffice\PhpSpreadsheet\IOFactory::identify($inputFileName);
            $objReader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader($inputFileType);
            $objPHPExcel = $objReader->load($inputFileName);
        } catch( \Exception $e ) {
            die('Error loading file "'.pathinfo($inputFileName,PATHINFO_BASENAME).'": '.$e->getMessage());
        }

        $sheet = $objPHPExcel->getSheet(0);
        $highestRow = $sheet->getHighestRow();
        $highestColumn = $sheet->getHighestColumn();

        $batchSize = 20;
        $loopCount = 0;
        $count = 10;
        for( $row = 2; $row <= $highestRow; $row++ ) {

            //Read a row of data into an array
            $rowData = $sheet->rangeToArray('A' . $row . ':' . $highestColumn . $row,
                NULL,
                TRUE,
                FALSE);

            $name = $rowData[0][0];
            $shortname = $rowData[0][1];
            $abbreviation = $rowData[0][2];

//            print "<pre>";
//            print_r($rowData);
//            print "</pre>";
//            print "</pre>";
            //echo "name=$name, shortname=$shortname, abbreviation=$abbreviation <br>";
            //exit('1');

        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:LabResultNameList'] by [LabResultNameList::class]
            $listEntity = $em->getRepository(LabResultNameList::class)->findOneByName($name);
            if( $listEntity ) {
                continue;
            }

            $listEntity = new LabResultNameList();
            $this->setDefaultList($listEntity,null,$username,$name);

            if( $shortname ) {
                $listEntity->setShortname($shortname);
            }

            if( $abbreviation ) {
                $listEntity->setAbbreviation($abbreviation);
            }

            //exit('exit generateObjectTypeActions');
            $em->persist($listEntity);
            if( ($loopCount % $batchSize) === 0) {
                //$em->persist($listEntity);
                $em->flush();
                //$em->clear(); // Detaches all objects from Doctrine!
            }

            $count = $count + 10;
            $loopCount++;
        }

        $em->flush(); //Persist objects that did not make up an entire batch
        $em->clear();

        //exit('1');

        return round($count/10);
    }


    //LabResultUnitsMeasureList
    public function generateLabResultUnitsMeasureList() {

        $username = $this->getUser();
        $em = $this->getDoctrine()->getManager();

        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:LabResultUnitsMeasureList'] by [LabResultUnitsMeasureList::class]
        $entities = $em->getRepository(LabResultUnitsMeasureList::class)->findAll();
        if( count($entities) > 3 ) {
            return -1;
        }

        $inputFileName = __DIR__ . '/../Util/Laboratory Units of Measure Compilation-1.xlsx';

        try {
            $inputFileType = \PhpOffice\PhpSpreadsheet\IOFactory::identify($inputFileName);
            $objReader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader($inputFileType);
            $objPHPExcel = $objReader->load($inputFileName);
        } catch( \Exception $e ) {
            die('Error loading file "'.pathinfo($inputFileName,PATHINFO_BASENAME).'": '.$e->getMessage());
        }

        $sheet = $objPHPExcel->getSheet(0);
        $highestRow = $sheet->getHighestRow();
        $highestColumn = $sheet->getHighestColumn();

        $types = array(
            //"Sample Lab Result Unit of Measure 01",
            //"Sample Lab Result Unit of Measure 02",
        );

        $count = 10;
        for( $row = 2; $row <= $highestRow; $row++ ) {

            //Read a row of data into an array
            $rowData = $sheet->rangeToArray('A' . $row . ':' . $highestColumn . $row,
                NULL,
                TRUE,
                FALSE);

            $name = $rowData[0][0];
            $abbreviation = $rowData[0][1];

//            print "<pre>";
//            print_r($rowData);
//            print "</pre>";
//            print "</pre>";
//            echo "name=$name, abbreviation=$abbreviation <br>";

        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:LabResultUnitsMeasureList'] by [LabResultUnitsMeasureList::class]
            $listEntity = $em->getRepository(LabResultUnitsMeasureList::class)->findOneByName($name);
            if( $listEntity ) {
                continue;
            }

            $listEntity = new LabResultUnitsMeasureList();
            $this->setDefaultList($listEntity,null,$username,$name);

            $listEntity->setAbbreviation($abbreviation);

            //exit('exit generateObjectTypeActions');
            $em->persist($listEntity);
            $em->flush();

            $count = $count + 10;
            //exit('1');
        }

        $em->clear();

        return round($count/10);
    }

    //LabResultFlagList
    public function generateLabResultFlagList() {

        $username = $this->getUser();
        $em = $this->getDoctrine()->getManager();

        $types = array(
            "Abnormal (applies to non-numeric results)"=>"A",
            "Above absolute high-off instrument scale"=>">",
            "Above high normal"=>"H",
            "Above upper panic limits"=>"HH",
            "Below absolute low-off instrument scale"=>"<",
            "Below low normal"=>"L",
            "Below lower panic limits"=>"LL",
            "Better--use when direction not relevant"=>"B",
            "Intermediate. Indicates for microbiology susceptibilities only"=>"I",
            "Moderately susceptible. Indicates for microbiology susceptibilities only"=>"MS",
            "No range defined, or normal ranges don't apply"=>"null",
            "Normal (applies to non-numeric results)"=>"N",
            "Resistant. Indicates for microbiology susceptibilities only"=>"R",
            "Significant change down"=>"D",
            "Significant change up"=>"U"
        );

        $count = 10;
        foreach( $types as $name=>$abbreviation ) {

        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:LabResultFlagList'] by [LabResultFlagList::class]
            $listEntity = $em->getRepository(LabResultFlagList::class)->findOneByName($name);
            if( $listEntity ) {
                continue;
            }

            $listEntity = new LabResultFlagList();
            $this->setDefaultList($listEntity,$count,$username,$name);

            $listEntity->setAbbreviation($abbreviation);

            //exit('exit generateObjectTypeActions');
            $em->persist($listEntity);
            $em->flush();

            $count = $count + 10;
        }

        return round($count/10);
    }

    //PathologyResultSignatoriesList
    public function generatePathologyResultSignatoriesList() {

        $username = $this->getUser();
        $em = $this->getDoctrine()->getManager();

        $types = array(

        );

        $count = 10;
        foreach( $types as $name ) {

        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:PathologyResultSignatoriesList'] by [PathologyResultSignatoriesList::class]
            $listEntity = $em->getRepository(PathologyResultSignatoriesList::class)->findOneByName($name);
            if( $listEntity ) {
                continue;
            }

            $listEntity = new PathologyResultSignatoriesList();
            $this->setDefaultList($listEntity,$count,$username,$name);

            $em->persist($listEntity);
            $em->flush();

            $count = $count + 10;
        }

        return round($count/10);
    }

    //Transfusion Reaction Type
    public function generateTransfusionReactionType() {

        $username = $this->getUser();
        $em = $this->getDoctrine()->getManager();

        $types = array(
            "FNHTR",
            "Allergic",
            "Underlying condition",
            "No transfusion reaction",
            "Anaphylactic",
            "TACO",
            "TRALI",
            "Hemolytic",
            "Delayed serologic",
            "Hypotensive",
            "Bacterial",
            "Other",
        );

        $count = 10;
        foreach( $types as $name ) {

        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:TransfusionReactionTypeList'] by [TransfusionReactionTypeList::class]
            $listEntity = $em->getRepository(TransfusionReactionTypeList::class)->findOneByName($name);
            if( $listEntity ) {
                continue;
            }

            $listEntity = new TransfusionReactionTypeList();
            $this->setDefaultList($listEntity,$count,$username,$name);

            //exit('exit generateObjectTypeActions');
            $em->persist($listEntity);
            $em->flush();

            $count = $count + 10;
        }

        return round($count/10);
    }

    //BloodTypeList
    public function generateBloodTypeList() {

        $username = $this->getUser();
        $em = $this->getDoctrine()->getManager();

        $types = array(
            "A+",
            "A-",
            "AB-",
            "AB+",
            "B+",
            "B-",
            "O+",
            "O-",
        );

        $count = 10;
        foreach( $types as $name ) {

        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:BloodTypeList'] by [BloodTypeList::class]
            $listEntity = $em->getRepository(BloodTypeList::class)->findOneByName($name);
            if( $listEntity ) {
                continue;
            }

            $listEntity = new BloodTypeList();
            $this->setDefaultList($listEntity,$count,$username,$name);

            //exit('exit generateObjectTypeActions');
            $em->persist($listEntity);
            $em->flush();

            $count = $count + 10;
        }

        return round($count/10);
    }

    public function generateAdditionalCommunicationList() {

        $username = $this->getUser();
        $em = $this->getDoctrine()->getManager();

        $types = array(
            "will be necessary",
            "completed",
            "not needed"
        );

        $count = 10;
        foreach( $types as $name ) {

        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:AdditionalCommunicationList'] by [AdditionalCommunicationList::class]
            $listEntity = $em->getRepository(AdditionalCommunicationList::class)->findOneByName($name);
            if( $listEntity ) {
                continue;
            }

            $listEntity = new AdditionalCommunicationList();
            $this->setDefaultList($listEntity,$count,$username,$name);

            //exit('exit generateObjectTypeActions');
            $em->persist($listEntity);
            $em->flush();

            $count = $count + 10;
        }

        return round($count/10);
    }

    public function generateTransfusionAntibodyScreenResultsList() {

        $username = $this->getUser();
        $em = $this->getDoctrine()->getManager();

        $types = array(
            "Positive",
            "Negative",
        );

        $count = 10;
        foreach( $types as $name ) {

        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:TransfusionAntibodyScreenResultsList'] by [TransfusionAntibodyScreenResultsList::class]
            $listEntity = $em->getRepository(TransfusionAntibodyScreenResultsList::class)->findOneByName($name);
            if( $listEntity ) {
                continue;
            }

            $listEntity = new TransfusionAntibodyScreenResultsList();
            $this->setDefaultList($listEntity,$count,$username,$name);

            //exit('exit generateObjectTypeActions');
            $em->persist($listEntity);
            $em->flush();

            $count = $count + 10;
        }

        return round($count/10);
    }

    public function generateTransfusionDATResultsList() {

        $username = $this->getUser();
        $em = $this->getDoctrine()->getManager();

        $types = array(
            "Positive",
            "Negative",
        );

        $count = 10;
        foreach( $types as $name ) {

        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:TransfusionDATResultsList'] by [TransfusionDATResultsList::class]
            $listEntity = $em->getRepository(TransfusionDATResultsList::class)->findOneByName($name);
            if( $listEntity ) {
                continue;
            }

            $listEntity = new TransfusionDATResultsList();
            $this->setDefaultList($listEntity,$count,$username,$name);

            //exit('exit generateObjectTypeActions');
            $em->persist($listEntity);
            $em->flush();

            $count = $count + 10;
        }

        return round($count/10);
    }

    public function generateTransfusionCrossmatchResultsList() {

        $username = $this->getUser();
        $em = $this->getDoctrine()->getManager();

        $types = array(
            "Compatible",
            "Incompatible",
        );

        $count = 10;
        foreach( $types as $name ) {

        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:TransfusionCrossmatchResultsList'] by [TransfusionCrossmatchResultsList::class]
            $listEntity = $em->getRepository(TransfusionCrossmatchResultsList::class)->findOneByName($name);
            if( $listEntity ) {
                continue;
            }

            $listEntity = new TransfusionCrossmatchResultsList();
            $this->setDefaultList($listEntity,$count,$username,$name);

            //exit('exit generateObjectTypeActions');
            $em->persist($listEntity);
            $em->flush();

            $count = $count + 10;
        }

        return round($count/10);
    }

    public function generateTransfusionHemolysisCheckResultsList() {

        $username = $this->getUser();
        $em = $this->getDoctrine()->getManager();

        $types = array(
            "Hemolysis",
            "No hemolysis",
        );

        $count = 10;
        foreach( $types as $name ) {

        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:TransfusionHemolysisCheckResultsList'] by [TransfusionHemolysisCheckResultsList::class]
            $listEntity = $em->getRepository(TransfusionHemolysisCheckResultsList::class)->findOneByName($name);
            if( $listEntity ) {
                continue;
            }

            $listEntity = new TransfusionHemolysisCheckResultsList();
            $this->setDefaultList($listEntity,$count,$username,$name);

            //exit('exit generateObjectTypeActions');
            $em->persist($listEntity);
            $em->flush();

            $count = $count + 10;
        }

        return round($count/10);
    }

    public function generateComplexPlateletSummaryAntibodiesList() {

        $username = $this->getUser();
        $em = $this->getDoctrine()->getManager();

        $types = array(
            "HLA",
            "HPA",
            "HLA and HPA",
            "None"
        );

        $count = 10;
        foreach( $types as $name ) {

        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:ComplexPlateletSummaryAntibodiesList'] by [ComplexPlateletSummaryAntibodiesList::class]
            $listEntity = $em->getRepository(ComplexPlateletSummaryAntibodiesList::class)->findOneByName($name);
            if( $listEntity ) {
                continue;
            }

            $listEntity = new ComplexPlateletSummaryAntibodiesList();
            $this->setDefaultList($listEntity,$count,$username,$name);

            //exit('exit generateObjectTypeActions');
            $em->persist($listEntity);
            $em->flush();

            $count = $count + 10;
        }

        return round($count/10);
    }


    public function generateCCIUnitPlateletCountDefaultValueList() {

        $username = $this->getUser();
        $em = $this->getDoctrine()->getManager();

        $types = array(
            "3",
        );

        $count = 10;
        foreach( $types as $name ) {

        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:CCIUnitPlateletCountDefaultValueList'] by [CCIUnitPlateletCountDefaultValueList::class]
            $listEntity = $em->getRepository(CCIUnitPlateletCountDefaultValueList::class)->findOneByName($name);
            if( $listEntity ) {
                continue;
            }

            $listEntity = new CCIUnitPlateletCountDefaultValueList();
            $this->setDefaultList($listEntity,$count,$username,$name);

            //exit('exit generateObjectTypeActions');
            $em->persist($listEntity);
            $em->flush();

            $count = $count + 10;
        }

        return round($count/10);
    }

    public function generateCCIPlateletTypeTransfusedList() {

        $username = $this->getUser();
        $em = $this->getDoctrine()->getManager();

        $types = array(
            "Regular Platelets",
            "Crossmatched",
            "HLA matched",
            "ABO matched",
        );

        $count = 10;
        foreach( $types as $name ) {

        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:CCIPlateletTypeTransfusedList'] by [CCIPlateletTypeTransfusedList::class]
            $listEntity = $em->getRepository(CCIPlateletTypeTransfusedList::class)->findOneByName($name);
            if( $listEntity ) {
                continue;
            }

            $listEntity = new CCIPlateletTypeTransfusedList();
            $this->setDefaultList($listEntity,$count,$username,$name);

            //exit('exit generateObjectTypeActions');
            $em->persist($listEntity);
            $em->flush();

            $count = $count + 10;
        }

        return round($count/10);
    }

    public function generatePlateletTransfusionProductReceivingList() {

        $username = $this->getUser();
        $em = $this->getDoctrine()->getManager();

        $types = array(
            "HLA Platelets",
            "XM Platelets",
            "Regular Platelets",
            "Platelet Drip"
        );

        $count = 10;
        foreach( $types as $name ) {

        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:PlateletTransfusionProductReceivingList'] by [PlateletTransfusionProductReceivingList::class]
            $listEntity = $em->getRepository(PlateletTransfusionProductReceivingList::class)->findOneByName($name);
            if( $listEntity ) {
                continue;
            }

            $listEntity = new PlateletTransfusionProductReceivingList();
            $this->setDefaultList($listEntity,$count,$username,$name);

            //exit('exit generateObjectTypeActions');
            $em->persist($listEntity);
            $em->flush();

            $count = $count + 10;
        }

        return round($count/10);
    }

    public function generateTransfusionProductStatusList() {

        $username = $this->getUser();
        $em = $this->getDoctrine()->getManager();

        $types = array(
            "Ordered",
            "Not Ordered",
            "Pending",
            "In-house"
        );

        $count = 10;
        foreach( $types as $name ) {

        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:TransfusionProductStatusList'] by [TransfusionProductStatusList::class]
            $listEntity = $em->getRepository(TransfusionProductStatusList::class)->findOneByName($name);
            if( $listEntity ) {
                continue;
            }

            $listEntity = new TransfusionProductStatusList();
            $this->setDefaultList($listEntity,$count,$username,$name);

            //exit('exit generateObjectTypeActions');
            $em->persist($listEntity);
            $em->flush();

            $count = $count + 10;
        }

        return round($count/10);
    }


    public function generateWeekDaysList() {

        $username = $this->getUser();
        $em = $this->getDoctrine()->getManager();

        $types = array(
            "Monday",
            "Tuesday",
            "Wednesday",
            "Thursday",
            "Friday",
            "Saturday",
            "Sunday",
        );

        $count = 10;
        foreach( $types as $name ) {

        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:WeekDaysList'] by [WeekDaysList::class]
            $listEntity = $em->getRepository(WeekDaysList::class)->findOneByName($name);
            if( $listEntity ) {
                continue;
            }

            $listEntity = new WeekDaysList();
            $this->setDefaultList($listEntity,$count,$username,$name);

            //exit('exit generateObjectTypeActions');
            $em->persist($listEntity);
            $em->flush();

            $count = $count + 10;
        }

        return round($count/10);
    }

    public function generateMonthsList() {

        $username = $this->getUser();
        $em = $this->getDoctrine()->getManager();

        $types = array(
            "January",
            "February",
            "March",
            "April",
            "May",
            "June",
            "July",
            "August",
            "September",
            "October",
            "November",
            "December",
        );

        $count = 10;
        foreach( $types as $name ) {

        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:MonthsList'] by [MonthsList::class]
            $listEntity = $em->getRepository(MonthsList::class)->findOneByName($name);
            if( $listEntity ) {
                continue;
            }

            $listEntity = new MonthsList();
            $this->setDefaultList($listEntity,$count,$username,$name);

            //exit('exit generateObjectTypeActions');
            $em->persist($listEntity);
            $em->flush();

            $count = $count + 10;
        }

        return round($count/10);
    }


    public function generateLifeForm() {

        $username = $this->getUser();
        $em = $this->getDoctrine()->getManager();

        $types = array(
            "Homo Sapiens",
        );

        $count = 10;
        foreach( $types as $name ) {

        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:LifeFormList'] by [LifeFormList::class]
            $listEntity = $em->getRepository(LifeFormList::class)->findOneByName($name);
            if( $listEntity ) {
                continue;
            }

            $listEntity = new LifeFormList();
            $this->setDefaultList($listEntity,$count,$username,$name);

            //exit('exit generateObjectTypeActions');
            $em->persist($listEntity);
            $em->flush();

            $count = $count + 10;
        }

        return round($count/10);
    }

    //Run after generateRoles
    public function generateTransResProjectSpecialty() {

        $transresUtil = $this->container->get('transres_util');
        $username = $this->getUser();
        $em = $this->getDoctrine()->getManager();

        //                                                                  ROLE_               OID      project specialty shown to users as user friendly name
        //$name =>                                  array(abbreviation,     rolename,        shortname, friendlyname)
        $types = array(
            "Hematopathology" =>                    array("hematopathology","HEMATOPATHOLOGY",  "HP",   "Hematopathology"),
            "AP/CP" =>                              array("ap-cp",          "APCP",             "APCP", "AP/CP"),
            "COVID-19" =>                           array("covid19",        "COVID19",          "COVID", "COVID-19"),
            "Multiparametric In Situ Imaging" =>    array("misi",           "MISI",             "MISI", "MISI"), //Multiparametric In Situ Imaging (MISI)

            "USCAP" =>                              array("uscap",          "USCAP",            "USCAP", "USCAP"), //USCAP (prefix USCAP)
            "Anatomic Pathology" =>                 array("ap",             "AP",               "AP",    "AP"),    //Anatomic Pathology (prefix AP)
            "Clinical Pathology" =>                 array("cp",             "CP",               "CP",    "CP"), //Clinical Pathology (prefix CP)

            "Computational & Systems Pathology" =>  array("csp",            "CSP",              "CSP",   "CSP")
        );

        $flush = false;
        $count = 10;
        foreach( $types as $name => $nameArr ) {

            $abbreviation = $nameArr[0];
            $rolename =     $nameArr[1];
            $shortname =    $nameArr[2];
            $friendlyname = $nameArr[3];

        //process.py script: replaced namespace by ::class: ['AppTranslationalResearchBundle:SpecialtyList'] by [SpecialtyList::class]
            $listEntity = $em->getRepository(SpecialtyList::class)->findOneByName($name);
            if( $listEntity ) {

                if( !$listEntity->getAbbreviation() ) {
                    $listEntity->setAbbreviation($abbreviation);
                    $flush = true;
                }

                if( !$listEntity->getRolename() ) {
                    $listEntity->setRolename($rolename);
                    $flush = true;
                }

                if( !$listEntity->getShortname() ) {
                    $listEntity->setShortname($shortname);
                    $flush = true;
                }

                if( !$listEntity->getFriendlyname() ) {
                    $listEntity->setFriendlyname($friendlyname);
                    $flush = true;
                }

                //add not existing _TRANSRES_ roles
                $transresUtil->addTransresRolesBySpecialty($listEntity);

                continue;
            }

            $listEntity = new SpecialtyList();
            $this->setDefaultList($listEntity,$count,$username,$name);

            $listEntity->setAbbreviation($abbreviation);
            $listEntity->setRolename($rolename);
            $listEntity->setShortname($shortname);
            $listEntity->setFriendlyname($friendlyname);

            //exit('exit generateObjectTypeActions');
            $em->persist($listEntity);
            $em->flush();

            $count = $count + 10;


            //add not existing _TRANSRES_ roles
            $transresUtil->addTransresRolesBySpecialty($listEntity);
        }

        if( $flush ) {
            $em->flush();
        }

        //scan and add Work Queue roles
        //$transresUtil->addTransresRolesBySpecialtyWorkQueue();

        return round($count/10);
    }

    public function generateWorkQueueList() {
        $transresUtil = $this->container->get('transres_util');
        $username = $this->getUser();
        $em = $this->getDoctrine()->getManager();

        $types = array(
            "CTP Lab" => array("QUEUECTP"),
            "MISI Lab" => array("QUEUEMISI"),
            "CSP Lab" => array("QUEUECSP"),
        );

        $count = 10;
        foreach( $types as $name=>$nameArr ) {

            $abbreviation = $nameArr[0];
            //echo "name=$name<br>";
            //echo "abbreviation=$abbreviation<br>";

        //process.py script: replaced namespace by ::class: ['AppTranslationalResearchBundle:WorkQueueList'] by [WorkQueueList::class]
            $listEntity = $em->getRepository(WorkQueueList::class)->findOneByName($name);
            //echo "name=".$listEntity->getName()."; abbreviation=".$listEntity->getAbbreviation()."<br>";
            if( $listEntity ) {
                continue;
            }

            $listEntity = new WorkQueueList();
            $this->setDefaultList($listEntity,$count,$username,$name);

            $listEntity->setAbbreviation($abbreviation);

            $em->persist($listEntity);
            $em->flush();

            $count = $count + 10;
        }
        //exit('111');

        //scan and add Work Queue roles
        $transresUtil->addTransresRolesBySpecialtyWorkQueue();

        return round($count/10);
    }

    public function generateOrderableStatusList() {
        $username = $this->getUser();
        $em = $this->getDoctrine()->getManager();

        //“Requested”, “Pending Additional Info”, “In Progress”, and “Completed”
        $types = array(
            "Requested" => array("requested"),
            "Pending Additional Info" => array("pending-additional-info"),
            "In Progress" => array("in-progress"),
            "Completed" => array("completed"),
            "Canceled by Requestor" => array("canceled-by-requestor"),
            "Canceled by Performer" => array("canceled-by-performer"),
        );

        $count = 10;
        foreach( $types as $name=>$nameArr ) {

            $abbreviation = $nameArr[0];
            //echo "name=$name<br>";
            //echo "abbreviation=$abbreviation<br>";

        //process.py script: replaced namespace by ::class: ['AppTranslationalResearchBundle:OrderableStatusList'] by [OrderableStatusList::class]
            $listEntity = $em->getRepository(OrderableStatusList::class)->findOneByName($name);
            if( $listEntity ) {
                continue;
            }

            $listEntity = new OrderableStatusList();
            $this->setDefaultList($listEntity,$count,$username,$name);

            $listEntity->setAbbreviation($abbreviation);

            $em->persist($listEntity);
            $em->flush();

            $count = $count + 10;
        }

        return round($count/10);
    }

    public function generateTransResPriceTypeList() {

        $username = $this->getUser();
        $em = $this->getDoctrine()->getManager();

        $types = array(
            "External Pricing"=>"e",
            "Internal Pricing"=>"i"
        );

        $count = 10;
        foreach( $types as $name=>$abbreviation ) {

        //process.py script: replaced namespace by ::class: ['AppTranslationalResearchBundle:PriceTypeList'] by [PriceTypeList::class]
            $listEntity = $em->getRepository(PriceTypeList::class)->findOneByName($name);
            if( $listEntity ) {
                continue;
            }

            $listEntity = new PriceTypeList();
            $this->setDefaultList($listEntity,$count,$username,$name);

            $listEntity->setAbbreviation($abbreviation);

            //exit('exit generateObjectTypeActions');
            $em->persist($listEntity);
            $em->flush();

            $count = $count + 10;
        }

        return round($count/10);
    }

    public function generateTransResProjectTypeList() {

        $username = $this->getUser();
        $em = $this->getDoctrine()->getManager();

        $types = array(
            "Exploratory Research (Preliminary Study)",
            "Experimental Research (Descriptive Study)",
            "Clinical Research (Case Study)",
            "Clinical Research (Observational Study)",
            "Clinical trial (JCTO & Clinical Trials)",
            "Education/Teaching (Pathology Faculty)",
            "USCAP Submission"
        );

        $count = 10;
        foreach( $types as $name ) {

        //process.py script: replaced namespace by ::class: ['AppTranslationalResearchBundle:ProjectTypeList'] by [ProjectTypeList::class]
            $listEntity = $em->getRepository(ProjectTypeList::class)->findOneByName($name);
            if( $listEntity ) {
                continue;
            }

            $listEntity = new ProjectTypeList();
            $this->setDefaultList($listEntity,$count,$username,$name);

            //exit('exit generateObjectTypeActions');
            $em->persist($listEntity);
            $em->flush();

            $count = $count + 10;
        }

        return round($count/10);
    }

    public function generateIrbApprovalTypeList() {
        $username = $this->getUser();
        $em = $this->getDoctrine()->getManager();

        $types = array(
            "Not Exempt",
            "Exempt",
        );

        $count = 10;
        foreach( $types as $name ) {

        //process.py script: replaced namespace by ::class: ['AppTranslationalResearchBundle:IrbApprovalTypeList'] by [IrbApprovalTypeList::class]
            $listEntity = $em->getRepository(IrbApprovalTypeList::class)->findOneByName($name);
            if( $listEntity ) {
                continue;
            }

            $listEntity = new IrbApprovalTypeList();
            $this->setDefaultList($listEntity,$count,$username,$name);

            //exit('exit generateObjectTypeActions');
            $em->persist($listEntity);
            $em->flush();

            $count = $count + 10;
        }

        return round($count/10);
    }

    public function generateTissueProcessingServiceList() {
        $username = $this->getUser();
        $em = $this->getDoctrine()->getManager();

        $types = array(
            "Paraffin Block Processing",
            "Fresh/Frozen Tissue Procurement",
            "Frozen Tissue Storage",
        );

        $count = 10;
        foreach( $types as $name ) {

        //process.py script: replaced namespace by ::class: ['AppTranslationalResearchBundle:TissueProcessingServiceList'] by [TissueProcessingServiceList::class]
            $listEntity = $em->getRepository(TissueProcessingServiceList::class)->findOneByName($name);
            if( $listEntity ) {
                continue;
            }

            $listEntity = new TissueProcessingServiceList();
            $this->setDefaultList($listEntity,$count,$username,$name);

            //exit('exit generateObjectTypeActions');
            $em->persist($listEntity);
            $em->flush();

            $count = $count + 10;
        }

        return round($count/10);
    }

    public function generateCollLabList() {
        $username = $this->getUser();
        $em = $this->getDoctrine()->getManager();

        $types = array(
            "Central Lab",
            "Cytogenetics",
            "Molecular",
            "Transfusion Medicine",
            "Cellular Therapy",
            "Microbiology",
            "N/A"
        );

        $count = 10;
        foreach( $types as $name ) {

        //process.py script: replaced namespace by ::class: ['AppTranslationalResearchBundle:CollLabList'] by [CollLabList::class]
            $listEntity = $em->getRepository(CollLabList::class)->findOneByName($name);
            if( $listEntity ) {
                continue;
            }

            $listEntity = new CollLabList();
            $this->setDefaultList($listEntity,$count,$username,$name);

            //exit('exit generateObjectTypeActions');
            $em->persist($listEntity);
            $em->flush();

            $count = $count + 10;
        }

        return round($count/10);
    }
    public function generateCollDivList() {
        $username = $this->getUser();
        $em = $this->getDoctrine()->getManager();

        $types = array(
            "Anatomic Pathology" => "ap",
            "Hematopathology" => "hematopathology",
            "Clinical Pathology" => "cp",
            "Molecular Pathology" => "",
            "Experimental Pathology" => "",
            "Computational Pathology" => "csp",
            "N/A" => ""
        );

        $count = 10;
        foreach( $types as $name => $urlSlug ) {

        //process.py script: replaced namespace by ::class: ['AppTranslationalResearchBundle:CollDivList'] by [CollDivList::class]
            $listEntity = $em->getRepository(CollDivList::class)->findOneByName($name);
            if( $listEntity ) {

                //$listEntity->setUrlSlug($urlSlug);
                //$em->flush();

                continue;
            }

            $listEntity = new CollDivList();
            $this->setDefaultList($listEntity,$count,$username,$name);

            $listEntity->setUrlSlug($urlSlug);

            //exit('exit generateObjectTypeActions');
            $em->persist($listEntity);
            $em->flush();

            $count = $count + 10;
        }

        return round($count/10);
    }
    public function generateIrbStatusList() {
        $username = $this->getUser();
        $em = $this->getDoctrine()->getManager();

        $types = array(
            "Approved",
            "Submitted, in review",
            "Pending submission",
            "Not applicable"
        );

        $count = 10;
        foreach( $types as $name ) {

        //process.py script: replaced namespace by ::class: ['AppTranslationalResearchBundle:IrbStatusList'] by [IrbStatusList::class]
            $listEntity = $em->getRepository(IrbStatusList::class)->findOneByName($name);
            if( $listEntity ) {
                continue;
            }

            $listEntity = new IrbStatusList();
            $this->setDefaultList($listEntity,$count,$username,$name);

            //exit('exit generateObjectTypeActions');
            $em->persist($listEntity);
            $em->flush();

            $count = $count + 10;
        }

        return round($count/10);
    }

    public function generateRequesterGroupList() {
        $username = $this->getUser();
        $em = $this->getDoctrine()->getManager();

        $types = array(
            "Internal - WCM Pathology Faculty" => "Internal",
            //"External - Non-WCM Pathology Faculty" => "External"
            "External - WCM Faculty of Other Departments and Members of Other Institutions" => "External"
        );

        $count = 10;
        foreach( $types as $name => $urlSlug ) {

            //exit("name=$name, abbreviation=$abbreviation");

        //process.py script: replaced namespace by ::class: ['AppTranslationalResearchBundle:RequesterGroupList'] by [RequesterGroupList::class]
            $listEntity = $em->getRepository(RequesterGroupList::class)->findOneByName($name);
            if( $listEntity ) {

                //temp
                //$listEntity->setUrlSlug($urlSlug);
                //$em->flush();

                continue;
            }

            //$listEntity = $em->getRepository('AppTranslationalResearchBundle:RequesterGroupList')->findOneByAbbreviation($abbreviation);
            //if( $listEntity ) {
            //    continue;
            //}

            $listEntity = new RequesterGroupList();
            $this->setDefaultList($listEntity,$count,$username,$name);

            $listEntity->setAbbreviation($urlSlug);
            $listEntity->setUrlSlug($urlSlug);

            //exit('exit generateObjectTypeActions');
            $em->persist($listEntity);
            $em->flush();

            $count = $count + 10;
        }

        return round($count/10);
    }

    public function generateCompCategoryList() {
        $username = $this->getUser();
        $em = $this->getDoctrine()->getManager();

        $types = array(
            "Transcriptomics",
            "Genomics",
            "Epigenomics",
            "Multiomics",
            "Imaging"
        );

        $count = 10;
        foreach( $types as $name ) {

            //exit("name=$name, abbreviation=$abbreviation");

        //process.py script: replaced namespace by ::class: ['AppTranslationalResearchBundle:CompCategoryList'] by [CompCategoryList::class]
            $listEntity = $em->getRepository(CompCategoryList::class)->findOneByName($name);
            if( $listEntity ) {
                continue;
            }

            $listEntity = new CompCategoryList();
            $this->setDefaultList($listEntity,$count,$username,$name);

            //exit('exit generateObjectTypeActions');
            $em->persist($listEntity);
            $em->flush();

            $count = $count + 10;
        }

        return round($count/10);
    }

    public function generateRestrictedServiceList() {
        $username = $this->getUser();
        $em = $this->getDoctrine()->getManager();

        $types = array(
            "Flow Cytometry",
            "Immunohistochemistry",
            "FISH",
            "Tissue Microarray",
            "Laser Capture Microdissection"
        );

        $count = 10;
        foreach( $types as $name ) {

        //process.py script: replaced namespace by ::class: ['AppTranslationalResearchBundle:OtherRequestedServiceList'] by [OtherRequestedServiceList::class]
            $listEntity = $em->getRepository(OtherRequestedServiceList::class)->findOneByName($name);
            if( $listEntity ) {
                continue;
            }

            $listEntity = new OtherRequestedServiceList();
            $this->setDefaultList($listEntity,$count,$username,$name);

            //exit('exit generateObjectTypeActions');
            $em->persist($listEntity);
            $em->flush();

            $count = $count + 10;
        }

        return round($count/10);
    }

    public function generateAntibodyCategoryTagList() {
        $username = $this->getUser();
        $em = $this->getDoctrine()->getManager();

        $types = array(
            "Human"                 => array(true,null),  //"public",
            "Mouse"                 => array(true,null),  //"public",
            "Pig"                   => array(true,null),  //"public",
            "In situ hybridization" => array(true,"ISH"), //"public",
            "Pending validation"    => array(false,null),
            "Failed"                => array(false,null),
        );

        $count = 10;
        foreach( $types as $name => $infoArr ) {

            $openToPublic = $infoArr[0];
            $abbreviation = $infoArr[1];

            //process.py script: replaced namespace by ::class: ['AppTranslationalResearchBundle:OtherRequestedServiceList'] by [OtherRequestedServiceList::class]
            $listEntity = $em->getRepository(AntibodyCategoryTagList::class)->findOneByName($name);
            if( $listEntity ) {
                continue;
            }

            $listEntity = new AntibodyCategoryTagList();
            $this->setDefaultList($listEntity,$count,$username,$name);

            $listEntity->setAbbreviation($abbreviation);
            $listEntity->setOpenToPublic($openToPublic);

            $em->persist($listEntity);
            $em->flush();

            $count = $count + 10;
        }

        return round($count/10);
    }

    public function generateAntibodyLabList() {
        $username = $this->getUser();
        $em = $this->getDoctrine()->getManager();

        $types = array(
            'Translational Research Program' => "TRP",
            'Multiparametric In Situ Imaging' => "MISI"
        );

        $count = 10;
        foreach( $types as $name => $abbreviation ) {

            $listEntity = $em->getRepository(AntibodyLabList::class)->findOneByName($name);
            if( $listEntity ) {
                continue;
            }

            $listEntity = $em->getRepository(AntibodyLabList::class)->findOneByAbbreviation($abbreviation);
            if( $listEntity ) {
                continue;
            }

            //exit('generateAntibodyLabList');
            $listEntity = new AntibodyLabList();
            $this->setDefaultList($listEntity,$count,$username,$name);
            $listEntity->setAbbreviation($abbreviation);

            $em->persist($listEntity);
            $em->flush();

            $count = $count + 10;
        }

        return round($count/10);
    }

    //Add numbers 1 through 71 as titles to this list as placeholders
    //Get list of Unique MISI Antibody Panel Names (for each PDF section) from
    // Fabio and replace placeholder panel titles (1 through 71) with the provided unique names
    public function generateAntibodyPanelList() {
        $username = $this->getUser();
        $em = $this->getDoctrine()->getManager();

        $types = array(
            "1",
            "2",
        );

        $count = 10;
        foreach( $types as $name ) {

            $listEntity = $em->getRepository(AntibodyPanelList::class)->findOneByName($name);
            if( $listEntity ) {
                continue;
            }

            $listEntity = new AntibodyPanelList();
            $this->setDefaultList($listEntity,$count,$username,$name);

            $em->persist($listEntity);
            $em->flush();

            $count = $count + 10;
        }

        return round($count/10);
    }


    public function generateTransferStatusList() {
        $username = $this->getUser();
        $em = $this->getDoctrine()->getManager();

        $types = array(
            "Ready",
            "Completed",
            "Failed",
        );

        $count = 10;
        foreach( $types as $name ) {

            $listEntity = $em->getRepository(TransferStatusList::class)->findOneByName($name);
            if( $listEntity ) {
                continue;
            }

            $listEntity = new TransferStatusList();
            $this->setDefaultList($listEntity,$count,$username,$name);

            $em->persist($listEntity);
            $em->flush();

            $count = $count + 10;
        }

        return round($count/10);
    }

//    public function generateCrnEntryTagsList() {
//
//        $em = $this->getDoctrine()->getManager();
//
//        $elements = array(
//            "Follow Up Needed",
//            "Addendum Needed",
//            "Amendment Needed",
//            "Specimen Issue",
//            "Requisition Form Issue",
//            "Patient ID Issue",
//            "Melanoma",
//            "Basal Cell Carcinoma",
//            "Squamous Cell Carcinoma"
//        );
//
//        $username = $this->getUser();
//
//        $count = 10;
//        foreach( $elements as $name ) {
//
//            $entity = $em->getRepository('AppCrnBundle:CrnEntryTagsList')->findOneByName($name);
//            if( $entity ) {
//                continue;
//            }
//
//            $entity = new CrnEntryTagsList();
//            $this->setDefaultList($entity,$count,$username,$name);
//
//            $em->persist($entity);
//            $em->flush();
//
//            $count = $count + 10;
//
//        } //foreach
//
//        return round($count/10);
//
//    }

    public function generateBusinessPurposes() {
        $username = $this->getUser();
        $em = $this->getDoctrine()->getManager();

        $types = array(
            "Deliverable for the main project",
            "USCAP-related",
            "Related to a professional meeting (non-USCAP)",
            "Related to a planned manuscript"
        );

        $count = 10;
        foreach( $types as $name ) {

        //process.py script: replaced namespace by ::class: ['AppTranslationalResearchBundle:BusinessPurposeList'] by [BusinessPurposeList::class]
            $listEntity = $em->getRepository(BusinessPurposeList::class)->findOneByName($name);
            if( $listEntity ) {
                continue;
            }

            $listEntity = new BusinessPurposeList();
            $this->setDefaultList($listEntity,$count,$username,$name);

            $em->persist($listEntity);
            $em->flush();

            $count = $count + 10;
        }

        return round($count/10);
    }


    public function generateDashboardRoles() {

        $username = $this->getUser();
        $em = $this->getDoctrine()->getManager();

        $dashboardInit = $this->container->get('dashboard_init');
        $types = $dashboardInit->getDashboardRolesArr();

        $count = 10;
        foreach( $types as $role => $aliasDescription ) {

            $alias = trim((string)$aliasDescription[0]);
            $description = trim((string)$aliasDescription[1]);
            $level = trim((string)$aliasDescription[2]);

            //Ignore not finished roles
            if( $alias == "Dashboards alias" ) {
                continue;
            }
            if( strpos((string)$alias, 'alias') !== false ) {
                continue;
            }

            $role = str_replace("-","_",$role);
            $role = str_replace(" ","_",$role);

            $entity = $em->getRepository(Roles::class)->findOneByName($role);

            if( $entity ) {
                continue;
            }

            $entity = new Roles();
            $this->setDefaultList($entity,$count,$username,$role);

            $entity->setName($role);
            $entity->setAlias($alias);
            if( $description ) {
                $entity->setDescription($description);
            }
            $entity->setLevel($level);

            //set sitename dashboard
            $this->addSingleSiteToEntity($entity,"dashboard");

            //set abbreviation
            if( isset($aliasDescription[4]) ) {
                $entity->setAbbreviation(trim((string)$aliasDescription[4]));
            }

            $em->persist($entity);
            $em->flush();

            $count = $count + 10;

        } //foreach

        //exit("EOF generate Roles");

        return round($count/10);
    }

    //hierarchical list titled ”Dashboard Chart Type” (same as Organizational Groups) (charttypes)
    public function generateChartTypeList() {
        //return NULL; //TODO: hierarchy

        $username = $this->getUser();
        $em = $this->getDoctrine()->getManager();

        //On this list add the “top”/root” category item titled “All Chart Types”
        //1) create root "All Chart Types"
        $rootName = "All Chart Types";
        $rootEntity = $this->generateSingleChartTypeList($rootName);
        if( !$rootEntity ) {
            exit("Root ChartType does not exist");
        }

        $types = array(
            "Line",
            "Pie",
            "Bar",
            "Column",
            "Scatter",
            "Area",
            "Violin",
            "Candlestick",
            "Boxplot",
            "Heatmap",
            "Sunburst",
            "Graph",
            "Lines",
            "Tree",
            "Treemap",
            "Histogram",
            "Pareto",
            "Box and Whisker",
            "Waterfall",
            "Funnel",
            "Stock",
            "Surface",
            "Radar",
            "Map",
            "Calendar",
            "Parallel",
            "Sankey",
            "Gauge",
            "Theme River",
            "3D Bar",
            "3D Scatter",
            "3D Surface",
            "3D Map",
            "3D Line"
        );

        $count = 20;
        foreach( $types as $name ) {

        //process.py script: replaced namespace by ::class: ['AppDashboardBundle:ChartTypeList'] by [ChartTypeList::class]
            $listEntity = $em->getRepository(ChartTypeList::class)->findOneByName($name);
            if( $listEntity ) {
                continue;
            }

            $listEntity = new ChartTypeList();
            $this->setDefaultList($listEntity,$count,$username,$name);
            $listEntity->setLevel(1);

            $rootEntity->addChild($listEntity);

            $em->persist($listEntity);
            $em->flush();

            $count = $count + 10;
        }

        $count = $count - 10;

        return round($count/10);
    }
    public function generateSingleChartTypeList($name) {
        $username = $this->getUser();
        $em = $this->getDoctrine()->getManager();

        //process.py script: replaced namespace by ::class: ['AppDashboardBundle:ChartTypeList'] by [ChartTypeList::class]
        $listEntity = $em->getRepository(ChartTypeList::class)->findOneByName($name);
        if( $listEntity ) {
            return $listEntity;
        }

        $listEntity = new ChartTypeList();
        $count = NULL;
        $this->setDefaultList($listEntity,$count,$username,$name);
        $listEntity->setLevel(0);

        $em->persist($listEntity);
        $em->flush();
        return $listEntity;
    }

    //hierarchical list titled “Dashboard Topic” (same as Organizational Groups) (charttopics)
//    public function generateChartTopicList_OLD() {
//        //return NULL; //TODO: hierarchy
//
//        $username = $this->getUser();
//        $em = $this->getDoctrine()->getManager();
//
//        //On this list add the “top”/root” category item titled “All Charts”
//        //addChild()
////        $service = new Institution();
////        $this->setDefaultList($service,$treeCount,$username,$servicename);
////        $treeCount = $treeCount + 10;
////        $service->setOrganizationalGroupType($levelService);
////        $division->addChild($service);
//
//        //1) create root "All Charts"
//        $rootName = "All Charts";
//        $rootEntity = $this->generateSingleChartTopicList($rootName);
//        if( !$rootEntity ) {
//            exit("Root ChartTopic does not exist");
//        }
//
////        $types = array(
////            "Financial",
////            "Productivity",
////            "Clinical",
////            "Research",
////            "Educational",
////            "Site Utilization"
////        );
//
////        $types = array(
////            array("All Charts","Financial"),
////            array("All Charts","Productivity",array("Turnaround Times","Specimen Tracking")),
////            array("All Charts","Clinical"),
////            array("All Charts","Research"),
////            array("All Charts","Educational",array("Fellowship Candidate Statistics","Residency Candidate Statistics")),
////            array("All Charts","Site Utilization")
////        );
//
//        //2) create under root
//        $types = array(
//            "Financial" => array(),
//            "Productivity" => array("Turnaround Times","Specimen Tracking"),
//            "Clinical" => array(),
//            "Research" => array(),
//            "Educational" => array("Fellowship Candidate Statistics","Residency Candidate Statistics"),
//            "Site Utilization" => array()
//        );
//
//        $count = 20;
//        foreach( $types as $name=>$childrenArr ) {
//
//            $listEntity = $em->getRepository('AppDashboardBundle:TopicList')->findOneByName($name);
//            if( $listEntity ) {
//                continue;
//            }
//
//            $listEntity = new TopicList();
//            $this->setDefaultList($listEntity,$count,$username,$name);
//            $listEntity->setLevel(1);
//            $rootEntity->addChild($listEntity);
//
//            $em->persist($listEntity);
//            $count = $count + 10;
//
//            //3) add children
//            if( $childrenArr ) {
//                foreach ($childrenArr as $childName) {
//                    $childEntity = $em->getRepository('AppDashboardBundle:TopicList')->findOneByName($childName);
//                    if ($childEntity) {
//                        continue;
//                    }
//
//                    $childEntity = new TopicList();
//                    $this->setDefaultList($childEntity, $count, $username, $childName);
//                    $listEntity->setLevel(2);
//                    $listEntity->addChild($childEntity);
//
//                    $em->persist($childEntity);
//                    $count = $count + 10;
//                }
//            }
//
//            $em->flush();
//        }
//
//        $count = $count - 10;
//
//        return round($count/10);
//    }
    //hierarchical list titled “Dashboard Topic” (same as Organizational Groups) (charttopics)
//    public function generateChartTopicList_OLD1() {
//        //return NULL;
//
//        $username = $this->getUser();
//        $em = $this->getDoctrine()->getManager();
//
//        //On this list add the “top”/root” category item titled “All Charts”
//        //1) create root "All Charts"
//        $rootName = "All Charts";
//        $rootEntity = $this->generateSingleChartTopicList($rootName);
//        if( !$rootEntity ) {
//            exit("Root ChartTopic does not exist");
//        }
//
//        $mapper = array(
//            'prefix' => 'App',
//            'bundleName' => 'DashboardBundle',
//            'className' => 'TopicList'
//        );
//
//        //2) create under root
//        $types = array(
//            "Financial" => array("Translational Research"),
//            //level 1
//            "Productivity" => array(
//                //level 2
//                "Turnaround Times" => array(
//                    //level 3
//                    "Translational Research" => array()
//                ),
//                "Specimen Tracking" => array(),
//                "Translational Research" => array(),
//                "Pathologist Involvement in Translational Research" => array()
//            ),
//            "Clinical" => array(),
//            "Research" => array(
//                "Translational Projects" => array()
//            ),
//            "Educational" => array(
//                "Fellowship Candidate Statistics" => array(),
//                "Residency Candidate Statistics" => array()
//            ),
//            "Site Utilization" => array(
//                "Platform" => array() ,
//                "Call Log" => array()
//            )
//        );
//
//        $processedFlag = false;
//        $count = 20;
//        foreach( $types as $name=>$child2Arr ) {
//            if( !$name ) {
//                continue;
//            }
//            echo "adding 1-".$name."<br>";
//            //findByChildnameAndParent
//            $listEntity = $em->getRepository('AppDashboardBundle:TopicList')->findByChildnameAndParent($name,$rootEntity,$mapper);
//            if( $listEntity ) {
//                //continue;
//                echo "already exists:".$name."<br>";
//            } else {
//                //1) add level 1
//                $listEntity = new TopicList();
//                $this->setDefaultList($listEntity, $count, $username, $name);
//                $listEntity->setLevel(1);
//                $rootEntity->addChild($listEntity);
//
//                $em->persist($listEntity);
//                $processedFlag = true;
//                $count = $count + 10;
//                echo "add 1-" . $name . "<br>";
//            }
//
//            //2) add level 2
//            if( $child2Arr ) {
//                foreach ($child2Arr as $child2Name => $child3Arr) {
//                    if( !$child2Name ) {
//                        continue;
//                    }
//                    echo "adding 2-".$child2Name."<br>";
//                    //$child2Entity = $em->getRepository('AppDashboardBundle:TopicList')->findOneByName($child2Name);
//                    $child2Entity = $em->getRepository('AppDashboardBundle:TopicList')->findByChildnameAndParent($child2Name,$listEntity,$mapper);
//                    if ($child2Entity) {
//                        echo "already exists:".$child2Name."<br>";
//                        //continue;
//                    } else {
//                        $child2Entity = new TopicList();
//                        $this->setDefaultList($child2Entity, $count, $username, $child2Name);
//                        $child2Entity->setLevel(2);
//                        $listEntity->addChild($child2Entity);
//
//                        $em->persist($child2Entity);
//                        $processedFlag = true;
//                        $count = $count + 10;
//                        echo "add 2-" . $name . "<br>";
//                    }
//
//                    //3) add level 3
//                    if( $child3Arr ) {
//                        foreach ($child3Arr as $child3Name) {
//                            if( !$child3Name ) {
//                                continue;
//                            }
//                            echo "adding 3-".$child3Name."<br>";
//                            //$child3Entity = $em->getRepository('AppDashboardBundle:TopicList')->findOneByName($child3Name);
//                            $child3Entity = $em->getRepository('AppDashboardBundle:TopicList')->findByChildnameAndParent($child3Name,$child2Entity,$mapper);
//                            if ($child3Entity) {
//                                //continue;
//                                echo "already exists:".$child3Name."<br>";
//                            } else {
//                                $child3Entity = new TopicList();
//                                $this->setDefaultList($child3Entity, $count, $username, $child3Name);
//                                $child3Entity->setLevel(3);
//                                $child2Entity->addChild($child3Entity);
//
//                                $em->persist($child3Entity);
//                                $processedFlag = true;
//                                $count = $count + 10;
//                                echo "add 3-".$name."<br>";
//                            }
//                        }//foreach $child3Arr
//                    }//if $child3Arr
//
//                }//foreach $child2Arr
//            }//if $child2Arr
//
//        }//foreach $types ($child1Arr)
//
//        dump($rootEntity->printTree("<br>"));
//        exit("eof topic test");
//
//        if( $processedFlag ) {
//            //$em->flush();
//        }
//
//        $count = $count - 10;
//
//        return round($count/10);
//    }
    //Generate Topic with recursion
    public function generateChartTopicList() {
        //On this list add the “top”/root” category item titled “All Charts”
        //1) create root "All Charts"
        $rootName = "All Charts";
        $rootEntity = $this->generateSingleChartTopicList($rootName);
        if( !$rootEntity ) {
            exit("Root ChartTopic does not exist");
        }

        $mapper = array(
            'prefix' => 'App',
            'bundleName' => 'DashboardBundle',
            'className' => 'TopicList',
            'fullClassName' => "App\\DashboardBundle\\Entity\\TopicList",
            'entityNamespace' => "App\\DashboardBundle\\Entity"
        );

        //2) create under root
        $types = array(
            "Financial" => array(
                "Translational Research"// => array()
            ),
            //level 1
            "Productivity" => array(
                //level 2
                "Turnaround Times" => array(
                    //level 3
                    "Translational Research" => array()
                ),
                "Specimen Tracking" => array(),
                "Translational Research" => array(),
                "Pathologist Involvement in Translational Research" => array()
            ),
            "Clinical" => array(
                "Call log site utilization" => array()
            ),
            "Research" => array(
                "Translational Projects" => array()
            ),
            "Educational" => array(
                "Fellowship Candidate Statistics" => array(),
                "Residency Candidate Statistics" => array(),
                "Interview Statistics" => array(),
                "Residency Interview Statistics" => array(),
                "Fellowship Interview Statistics" => array()
            ),
            "Site Utilization" => array(
                "Platform" => array(),
                "Call Log" => array(),
                "Dashboards" => array()
            )
        );

        $addedCount = 0;
        $level = 1;

        if ($this->isAssoc($types)) {
            foreach( $types as $name=>$childrens ) {
                if (!$name) {
                    continue;
                }
                $addedCount = $this->generateHierarchyTopics( $rootEntity, $name, $level, $childrens, $mapper, $addedCount );
            }//foreach key=>value
        } else {
            foreach( $types as $name ) {
                if (!$name) {
                    continue;
                }
                $childrens = array();
                $addedCount = $this->generateHierarchyTopics( $rootEntity, $name, $level, $childrens, $mapper, $addedCount );
            }//foreach
        }

        //dump($rootEntity->printTree("<br>"));
        //exit("eof topic test: $addedCount");

        if( $addedCount > 0 ) {
            $em = $this->getDoctrine()->getManager();
            $em->flush();
        }

        return $addedCount;
    }
    public function generateHierarchyTopics( $parentEntity, $name, $level, $childrens, $mapper, $addedCount=0 )
    {
        $username = $this->getUser();
        $em = $this->getDoctrine()->getManager();

        if (!$name) {
            return NULL;
        }
        //echo "adding $level-".$name."<br>";
        //findByChildnameAndParent
        //process.py script: replaced namespace by ::class: ['AppDashboardBundle:TopicList'] by [TopicList::class]
        $listEntity = $em->getRepository(TopicList::class)->findByChildnameAndParent($name, $parentEntity, $mapper);
        if( $listEntity ) {
            //echo "already exists:".$name."<br>";
        } else {
            //1) add level 1
            $listEntity = new TopicList();
            $this->setDefaultList($listEntity, NULL, $username, $name);
            $listEntity->setLevel($level);
            $parentEntity->addChild($listEntity);

            $em->persist($listEntity);
            //$processedFlag = true;
            //$count = $count + 10;
            //echo "add $level-" . $name . "<br>";
            $addedCount = $addedCount + 1;
        }

        if ($this->isAssoc($childrens)) {
            foreach ($childrens as $childName => $childChildrenArr) {
                if (!$childName) {
                    continue;
                }
                $childLevel = $level + 1;
                $addedCount = $this->generateHierarchyTopics($listEntity, $childName, $childLevel, $childChildrenArr, $mapper, $addedCount);
            }
        } else {
//            echo "simple array <br>";
//            if( count($childrens) > 0 ) {
//                echo " #####children=".$childrens[0]."<br>";
//            }
            foreach ($childrens as $childName) {
                if (!$childName) {
                    continue;
                }
                $childLevel = $level + 1;
                $childChildrenArr = array();
                $addedCount = $this->generateHierarchyTopics($listEntity, $childName, $childLevel, $childChildrenArr, $mapper, $addedCount);
            }
        }

        return $addedCount;
    }
    function isAssoc(array $arr)
    {
        if (array() === $arr) return false;
        return array_keys($arr) !== range(0, count($arr) - 1);
    }

    public function generateSingleChartTopicList($name) {
        $username = $this->getUser();
        $em = $this->getDoctrine()->getManager();

        //process.py script: replaced namespace by ::class: ['AppDashboardBundle:TopicList'] by [TopicList::class]
        $listEntity = $em->getRepository(TopicList::class)->findOneByName($name);
        if( $listEntity ) {
            return $listEntity;
        }

        $listEntity = new TopicList();
        $count = NULL;
        $this->setDefaultList($listEntity,$count,$username,$name);
        $listEntity->setLevel(0);

        $em->persist($listEntity);
        $em->flush();

        return $listEntity;
    }

    //chartfilters
    public function generateChartFilterList() {
        //return NULL; //testing
        $username = $this->getUser();
        $em = $this->getDoctrine()->getManager();

        $types = array(
            "From Date",
            "From Time",
            "To Date",
            "To Time",
            "Project Type",
            "Pathologist",
            "PI"
        );

        $count = 10;
        foreach( $types as $name ) {

        //process.py script: replaced namespace by ::class: ['AppDashboardBundle:FilterList'] by [FilterList::class]
            $listEntity = $em->getRepository(FilterList::class)->findOneByName($name);
            if( $listEntity ) {
                continue;
            }

            $listEntity = new FilterList();
            $this->setDefaultList($listEntity,$count,$username,$name);

            $em->persist($listEntity);
            $em->flush();

            $count = $count + 10;
        }

        return round($count/10);
    }
    //"Dashboard Charts" - list of existing charts from DashboardUtil.php (charts)
    public function generateChartsList() {
        //return NULL; //testing
        $username = $this->getUser();
        $em = $this->getDoctrine()->getManager();

        //get charts from DashboardUtil.php getChartTypes()
//        $types = array(
//            "pi-by-affiliation",
//            "projects-per-pi"
//        );

        $dashboardUtil = $this->container->get('dashboard_util');
        $types = $dashboardUtil->getChartTypesInit();

        //dump($types);
        //exit("exit generateChartsList");

        //process.py script: replaced namespace by ::class: ['AppDashboardBundle:ChartList'] by [ChartList::class]
        $listCharts = $em->getRepository(ChartList::class)->findAll();
        if( count($listCharts) > 0 ) {
            $newList = false;
        } else {
            $newList = true;
        }

        $count = 10; //new init

        foreach( $types as $name=>$abbreviation ) {

            if( !$name ) {
                continue;
            }

        //process.py script: replaced namespace by ::class: ['AppDashboardBundle:ChartList'] by [ChartList::class]
            $listEntity = $em->getRepository(ChartList::class)->findOneByName($name);
            if( $listEntity ) {
                continue;
            }
        //process.py script: replaced namespace by ::class: ['AppDashboardBundle:ChartList'] by [ChartList::class]
            $listEntity = $em->getRepository(ChartList::class)->findOneByAbbreviation($abbreviation);
            if( $listEntity ) {
                continue;
            }

            $listEntity = new ChartList();

            if( $newList ) {
                $index = $count;
            } else {
                $index = NULL;
            }

            $this->setDefaultList($listEntity,$index,$username,$name);

            if( $abbreviation ) {
                $listEntity->setAbbreviation($abbreviation);
            }

            $em->persist($listEntity);
            $em->flush();

            $count = $count + 10;
        }

        //run after populating chart and topic
        //$dashboardInit = $this->container->get('dashboard_init');
        //$chartInitCount = $dashboardInit->initCharts();
        //$count = $count + $chartInitCount;

        return round($count/10);
    }
    //chartdatasources
    public function generateChartDataSourceList() {
        //return NULL; //testing
        $username = $this->getUser();
        $em = $this->getDoctrine()->getManager();

        $types = array(
            "Internal Database",
        );

        $count = 10;
        foreach( $types as $name ) {

        //process.py script: replaced namespace by ::class: ['AppDashboardBundle:DataSourceList'] by [DataSourceList::class]
            $listEntity = $em->getRepository(DataSourceList::class)->findOneByName($name);
            if( $listEntity ) {
                continue;
            }

            $listEntity = new DataSourceList();
            $this->setDefaultList($listEntity,$count,$username,$name);

            $em->persist($listEntity);
            $em->flush();

            $count = $count + 10;
        }

        return round($count/10);
    }
    //chartupdatefrequencies
    public function generateChartUpdateFrequencyList() {
        //return NULL; //testing
        $username = $this->getUser();
        $em = $this->getDoctrine()->getManager();

        $types = array(
            "Live Query",
            //"Every 1 hour",
            //"Every 2 hours",
            //"Every 3 hours",
        );

        $count = 10;
        foreach( $types as $name ) {

        //process.py script: replaced namespace by ::class: ['AppDashboardBundle:UpdateFrequencyList'] by [UpdateFrequencyList::class]
            $listEntity = $em->getRepository(UpdateFrequencyList::class)->findOneByName($name);
            if( $listEntity ) {
                continue;
            }

            $listEntity = new UpdateFrequencyList();
            $this->setDefaultList($listEntity,$count,$username,$name);

            $em->persist($listEntity);
            $em->flush();

            $count = $count + 10;
        }

        return round($count/10);
    }
    //Dashboard Visualization Method (chartvisualizations)
    public function generateChartVisualizationList() {
        //return NULL; //testing
        $username = $this->getUser();
        $em = $this->getDoctrine()->getManager();

        $types = array(
            "Plotly",
        );

        $count = 10;
        foreach( $types as $name ) {

        //process.py script: replaced namespace by ::class: ['AppDashboardBundle:VisualizationList'] by [VisualizationList::class]
            $listEntity = $em->getRepository(VisualizationList::class)->findOneByName($name);
            if( $listEntity ) {
                continue;
            }

            $listEntity = new VisualizationList();
            $this->setDefaultList($listEntity,$count,$username,$name);

            $em->persist($listEntity);
            $em->flush();

            $count = $count + 10;
        }

        return round($count/10);
    }

    public function generateAuthUserGroupList() {
        $username = $this->getUser();
        $em = $this->getDoctrine()->getManager();

        $types = array(
            "WCM Department of Pathology and Laboratory Medicine",
            "Multi-tenant"
        );

        $count = 10;
        foreach( $types as $name ) {

            $listEntity = $em->getRepository(AuthUserGroupList::class)->findOneByName($name);
            if( $listEntity ) {
                continue;
            }

            $listEntity = new AuthUserGroupList();
            $this->setDefaultList($listEntity,$count,$username,$name);

            $em->persist($listEntity);
            $em->flush();

            $count = $count + 10;
        }

        return round($count/10);
    }
    public function generateAuthServerNetworkList() {
        $username = $this->getUser();
        $em = $this->getDoctrine()->getManager();

        $types = array(
            "Intranet (Solo)",
            "Intranet (Tandem)",
            "Internet (Solo) ",
            "Internet (Tandem)",
            "Internet (Hub)"
        );

        $count = 10;
        foreach( $types as $name ) {

            $listEntity = $em->getRepository(AuthServerNetworkList::class)->findOneByName($name);
            if( $listEntity ) {
                continue;
            }

            $listEntity = new AuthServerNetworkList();
            $this->setDefaultList($listEntity,$count,$username,$name);

            $em->persist($listEntity);
            $em->flush();

            $count = $count + 10;
        }

        return round($count/10);
    }
    public function generateAuthPartnerServerList() {
        $username = $this->getUser();
        $em = $this->getDoctrine()->getManager();

        $types = array(
            "https://view.med.cornell.edu",
        );

        $count = 10;
        foreach( $types as $name ) {

            $listEntity = $em->getRepository(AuthPartnerServerList::class)->findOneByName($name);
            if( $listEntity ) {
                continue;
            }

            $listEntity = new AuthPartnerServerList();
            $this->setDefaultList($listEntity,$count,$username,$name);

            $em->persist($listEntity);
            $em->flush();

            $count = $count + 10;
        }

        return round($count/10);
    }
    public function generateHostedUserGroupList_TODELETE() {
        //Generate Tenant IDs i.e. 'c/wcm/pathology' or 'c/lmh/pathology'
        //Similar to generateResLabs()

        $username = $this->getUser();
        $em = $this->getDoctrine()->getManager();

        $count = 10;

        $rootName = "All Hosted User Groups";
        $rootAbbrev = "c";
        $root = $em->getRepository(HostedUserGroupList::class)->findOneByName($rootName);
        if( !$root ) {
            //exit('generateResLabs: No HostedUserGroupList: "c"');

            $root = new HostedUserGroupList();
            $count = NULL;
            $this->setDefaultList($root,$count,$username,$rootName);

            $root->setAbbreviation($rootAbbrev);
            $root->setUrlSlug($rootAbbrev);
            $root->setLevel(0);
            $count = $count + 10;

            $em->persist($root);
            $em->flush();
        }

        $types = array(
            //"All Hosted User Groups" => "c", //Parent list item ID = NULL
            // c/test-institution/test-department
            //"Test Institution" => array("Test Institution" => "test-institution"),
            //"Test Department" => "test-department", //Parent list item {Test Institution}
            array(
                1 => array("Test Institution", "test-institution"),
                2 => array("Test Department", "test-department"),
            ),

            // c/demo-institution/demo-department
            //"Demo Institution" => "demo-institution",
            //"Demo Department" => "demo-department", //Parent list item {Demo Institution}
            array(
                1 => array("Demo Institution", "demo-institution"),
                2 => array("Demo Department", "demo-department"),
            ),

            // c/wcm/pathology
            //"Weill Cornell Medicine" => "wcm",
            //"WCM Department of Pathology and Laboratory Medicine" => "pathology", //Parent list item {Weill Cornell Medicine}
            array(
                1 => array("Weill Cornell Medicine", "wcm"),
                2 => array("WCM Department of Pathology and Laboratory Medicine", "pathology"),
            ),
        );

        foreach( $types as $typeArr ) {

            $toFlush = false;
            reset($typeArr);
            //dump($typeArr);
            //exit('111');
            $instLevel = key($typeArr);
            echo 'instLevel='.$instLevel.'<br>';
            $instArr = $typeArr[$instLevel];
            dump($instArr);

            $instName = $instArr[0];
            $instAbbrev = $instArr[1];
            echo 'instName='.$instName.', instAbbrev='.$instAbbrev.'<br>';

            next($typeArr);
            $departLevel = key($typeArr);
            echo 'departLevel='.$departLevel.'<br>';
            $departArr = $typeArr[$departLevel];
            $departName = $departArr[0];
            $departAbbrev = $departArr[1];
            echo 'departName='.$departName.', departAbbrev='.$departAbbrev.'<br>';

            //exit('111');

            if( $instName && $instAbbrev ) {
                $instEntity = $em->getRepository(HostedUserGroupList::class)->findOneByName($instName);
                if( $instEntity ) {
                    continue;
                }

                $instEntity = new HostedUserGroupList();
                $this->setDefaultList($instEntity,$count,$username,$instName);
                $instEntity->setAbbreviation($instAbbrev);
                $instEntity->setUrlSlug($instAbbrev);
                $instEntity->setLevel($instLevel);
                $root->addChild($instEntity);
                $em->persist($instEntity);
                $toFlush = true;
                $count = $count + 10;

                if( $instEntity && $departName && $departAbbrev ) {
                    $departEntity = $em->getRepository(HostedUserGroupList::class)->findOneByName($departName);
                    if( $departEntity ) {
                        continue;
                    }

                    $departEntity = new HostedUserGroupList();
                    $this->setDefaultList($departEntity,$count,$username,$departName);
                    $departEntity->setAbbreviation($departAbbrev);
                    $departEntity->setUrlSlug($departAbbrev);
                    $departEntity->setLevel($departLevel);
                    $instEntity->addChild($departEntity);
                    $em->persist($departEntity);
                    $toFlush = true;
                    $count = $count + 10;
                }

            }

            $em->flush();
        }

        //exit('exit generateHostedUserGroupList');
        return round($count/10);
    }
    public function generateTenantUrlList() {
        //Generate Tenant urls i.e. 'c/wcm/pathology' or 'c/lmh/pathology'

        $username = $this->getUser();
        $em = $this->getDoctrine()->getManager();

        $count = 10;

        $rootName = "All Tenants";
        $rootAbbrev = "c";
        $root = $em->getRepository(TenantUrlList::class)->findOneByName($rootName);
        if( !$root ) {
            //exit('generateResLabs: No TenantUrlList: "c"');

            $root = new TenantUrlList();
            $count = NULL;
            $this->setDefaultList($root,$count,$username,$rootName);

            $root->setAbbreviation($rootAbbrev);
            $root->setUrlSlug($rootAbbrev);
            $root->setLevel(0);
            $count = $count + 10;

            $em->persist($root);
            $em->flush();
        }

        $types = array(
            //"All Hosted User Groups" => "c", //Parent list item ID = NULL
            // c/test-institution/test-department
            //"Test Institution" => array("Test Institution" => "test-institution"),
            //"Test Department" => "test-department", //Parent list item {Test Institution}
            array(
                1 => array("Test Institution", "test-institution"),
                2 => array("Test Department", "test-department"),
            ),

            // c/demo-institution/demo-department
            //"Demo Institution" => "demo-institution",
            //"Demo Department" => "demo-department", //Parent list item {Demo Institution}
            array(
                1 => array("Demo Institution", "demo-institution"),
                2 => array("Demo Department", "demo-department"),
            ),

            // c/wcm/pathology
            //"Weill Cornell Medicine" => "wcm",
            //"WCM Department of Pathology and Laboratory Medicine" => "pathology", //Parent list item {Weill Cornell Medicine}
            array(
                1 => array("Weill Cornell Medicine", "wcm"),
                2 => array("WCM Department of Pathology and Laboratory Medicine", "pathology"),
            ),
        );

        foreach( $types as $typeArr ) {

            $toFlush = false;
            reset($typeArr);
            //dump($typeArr);
            //exit('111');
            $instLevel = key($typeArr);
            echo 'instLevel='.$instLevel.'<br>';
            $instArr = $typeArr[$instLevel];
            dump($instArr);

            $instName = $instArr[0];
            $instAbbrev = $instArr[1];
            echo 'instName='.$instName.', instAbbrev='.$instAbbrev.'<br>';

            next($typeArr);
            $departLevel = key($typeArr);
            echo 'departLevel='.$departLevel.'<br>';
            $departArr = $typeArr[$departLevel];
            $departName = $departArr[0];
            $departAbbrev = $departArr[1];
            echo 'departName='.$departName.', departAbbrev='.$departAbbrev.'<br>';

            //exit('111');

            if( $instName && $instAbbrev ) {
                $instEntity = $em->getRepository(TenantUrlList::class)->findOneByName($instName);
                if( $instEntity ) {
                    continue;
                }

                $instEntity = new TenantUrlList();
                $this->setDefaultList($instEntity,$count,$username,$instName);
                $instEntity->setAbbreviation($instAbbrev);
                $instEntity->setUrlSlug($instAbbrev);
                $instEntity->setLevel($instLevel);
                $root->addChild($instEntity);
                $em->persist($instEntity);
                $toFlush = true;
                $count = $count + 10;

                if( $instEntity && $departName && $departAbbrev ) {
                    $departEntity = $em->getRepository(TenantUrlList::class)->findOneByName($departName);
                    if( $departEntity ) {
                        continue;
                    }

                    $departEntity = new TenantUrlList();
                    $this->setDefaultList($departEntity,$count,$username,$departName);
                    $departEntity->setAbbreviation($departAbbrev);
                    $departEntity->setUrlSlug($departAbbrev);
                    $departEntity->setLevel($departLevel);
                    $instEntity->addChild($departEntity);
                    $em->persist($departEntity);
                    $toFlush = true;
                    $count = $count + 10;
                }

            }

            $em->flush();
        }

        //exit('exit generateTenantUrlList');
        return round($count/10);
    }


    //https://pathology.weill.cornell.edu/research/translational-research-services/fee-schedule
    public function generateTransResRequestCategoryType() {

        $transresUtil = $this->container->get('transres_util');
        $username = $this->getUser();
        $em = $this->getDoctrine()->getManager();


        //disable all where productId is NULL
        //$query = $em->createQuery("UPDATE AppTranslationalResearchBundle:RequestCategoryTypeList list SET list.type = 'disabled' WHERE list.productId IS NULL");
        $query = $em->createQuery("UPDATE App\\TranslationalResearchBundle\\Entity\\RequestCategoryTypeList list SET list.type = 'disabled' WHERE list.productId IS NULL");
        $numUpdated = $query->execute();
        //echo "Disabled elements in RequestCategoryTypeList, where productId IS NULL = ".$numUpdated."<br>";

        //WorkQueueList: "MISI Lab", "CTP Lab"
        $misiWorkQueue = $transresUtil->getWorkQueueObject("MISI Lab");
        $ctpWorkQueue = $transresUtil->getWorkQueueObject("CTP Lab");

        //(rev.08/17) *Contact: Bing He 212-746-6230
        $types = array(
            //array("Section", "TRP-0000","description","0","null"),
            array("Histology", "TRP-1001","Processing fixed tissue & embedding in paraffin block only","18","block"),
            array("Histology", "TRP-1002","Embedding frozen tissue in OCT block","10","block"),
            array("Histology", "TRP-1003","Unstained slides from paraffin-embedded or frozen tissue","10","slide"),
            array("Histology", "TRP-1004","Unstained slides from TMA block","10","slide"),
            array("Histology", "TRP-1005","Processing tissue and providing one H&E stained slide from paraffin-embedded block or frozen tissue","25","slide"),
            array("Histology", "TRP-1006","Prepare one H&E stained slide from paraffin-embedded block or frozen tissue","12","slide"),
            array("Histology", "TRP-1007","Prepare one H&E stained slide from TMA block","12","slide"),
            array("Histology", "TRP-1008","Sectioning or coring from paraffin-embedded or frozen tissue (Eppendorf tube)","15","tube"),
            array("Histology", "TRP-1009","Weigert's elastic staining","28","slide"),
            array("Histology", "TRP-1010","Giemsa staining","28","slide"),
            array("Histology", "TRP-1011","Iron staining","28","slide"),
            array("Histology", "TRP-1012","Alcian Blue staining","28","slide"),
            array("Histology", "TRP-1013","Periodic Acid-Schiff (PAS) staining","28","slide"),
            array("Histology", "TRP-1014","Reticulocyte staining","15","slide"),
            array("Histology", "TRP-1015","Masson's trichrome staining (manual)","35","slide"),
            array("Histology", "TRP-1016","Von Kossa staining (manual)","35","slide"),
            array("Histology", "TRP-1017","Alzarin Red staining (manual)","35","slide"),
            array("Histology", "TRP-1018","Oil Red O staining (manual)","28","slide"),
            array("Histology", "TRP-1019","Warthin Starry (Spirochetes)","35","slide"),
            array("Histology", "TRP-1020","Congo Red (Amyloid)","28","slide"),
            array("Histology", "TRP-1021","Picrosirius Red ( Collagen)","28","slide"),
            array("Histology", "TRP-1022","Modified H&E (Harris and Gill 3 Hematoxylin) Overnight staining.","12","slide"),

            array("Immunopathology", "TRP-2001","Staining - IHC regular","35","slide"),
            array("Immunopathology", "TRP-2002","Staining - IHC regular double","70","slide"),
            array("Immunopathology", "TRP-2003","Staining - IHC fluorescent","35","slide"),
            array("Immunopathology", "TRP-2004","Staining - IHC fluorescent double","70","slide"),
            array("Immunopathology", "TRP-2005","Staining - IHC fluorescent triple","100","slide"),
            array("Immunopathology", "TRP-2006","Antibody titering (one antibody)","250","request"),
            array("Immunopathology", "TRP-2007","Antibody titering (double staining)","400","request"),
            array("Immunopathology", "TRP-2008","TMA construction (base charge)","400","TMA block"),
            array("Immunopathology", "TRP-2009","TMA construction (additional cores)","10","tissue core"),
            array("Immunopathology", "TRP-2010","DNA in situ Hybridization (DNA probe)","100","slide"),
            array("Immunopathology", "TRP-2011","RNA in situ Hybridization (RNASCOPE)","100","slide"),

            array("Molecular Pathology", "TRP-3001","DNA/RNA extraction from blood, FFPE tissue, frozen tissue, etc","30","reaction"),
            array("Molecular Pathology", "TRP-3002","Quantitation of DNA/RNA utilizing Qubit 3.0","5","reaction"),
            array("Molecular Pathology", "TRP-3003","Applied Biosystems Real Time PCR","20","run"),
            array("Molecular Pathology", "TRP-3004","RNA Probe Hybridization","","Project-specific"),
            //array("Molecular Pathology", "TRP-3005","description","0","null"),
            array("Molecular Pathology", "TRP-3006","Mutation analysis platform","30","reaction"),
            array("Molecular Pathology", "TRP-3007","Fluorescent In-Situ Hybridization (FISH) Probe Development","","Project-specific"),
            array("Molecular Pathology", "TRP-3008","FISH Probe Hybridization","","Project-specific"),
            array("Molecular Pathology", "TRP-3009","Laser Capture Microdisection (User)","","Project-specific"),
            array("Molecular Pathology", "TRP-3010","Enzyme-linked immunosorbent assay (ELISA)","","Project-specific"),

            array("Imaging", "TRP-4001","Aperio scanning rate -20X - each slide","10","scan"),
            array("Imaging", "TRP-4002","Aperio scanning rate -40X - each slide","15","scan"),
            array("Imaging", "TRP-4003","Hosting images - per slide (20X) - per year","","Project-specific"),
            array("Imaging", "TRP-4004","Hosting images - per slide (40X) - per year","","Project-specific"),
            array("Imaging", "TRP-4005","Aperio scanning rate -20X - TMA slide","100","scan"),
            array("Imaging", "TRP-4006","Aperio scanning rate -40X - TMA slide","150","scan"),

            array("Pathology Service", "TRP-5001","Data Search w/ MD Review of Reports (Up to 100 reports)","250","request"),

            array("Genomics", "TRP-6001","Custom genotyping","","Project-specific"),
            array("Genomics", "TRP-6002","Gene expression analysis (including miRNAs)","","Project-specific"),
            array("Genomics", "TRP-6003","DNA copy number analysis","","Project-specific"),
            array("Genomics", "TRP-6004","Methylation analysis","","Project-specific"),

            array("Other Service Pricing", "TRP-7001","Search through up to 10 patient records/blocks outside requests only","100","request"),

            array("Biostatistics and Informatics", "TRP-8001","Biostatistics consultation - Study design, data collection & analysis, clinical trials protocol support","","Project-specific"),

            array("Administration", "TRP-9000","Professional fee (outside requests, Pathologist consulting service for selection of block and determination of adequacy)","100","request"),
            array("Administration", "TRP-9001","Administration fee (outside requests)","25","request"),
        );

        $count = 10;
        foreach( $types as $paramsArr ) {

            if( count($paramsArr) > 0 ) {
                $section = $paramsArr[0];
                $productId = $paramsArr[1];
                $name = $paramsArr[2];
                $fee = $paramsArr[3];
                $feeUnit = $paramsArr[4];
            } else {
                continue;
            }

        //process.py script: replaced namespace by ::class: ['AppTranslationalResearchBundle:RequestCategoryTypeList'] by [RequestCategoryTypeList::class]
            $listEntity = $em->getRepository(RequestCategoryTypeList::class)->findOneByProductId($productId);
            if( $listEntity ) {
                continue;
            }

            $listEntity = new RequestCategoryTypeList();
            $this->setDefaultList($listEntity,$count,$username,$name);

            $listEntity->setSection($section);
            $listEntity->setProductId($productId);
            $listEntity->setFee($fee);
            $listEntity->setFeeUnit($feeUnit);

            //TODO: assign CTP or MISI Work Queues according to the $productId: all 'TRP' -> CTP Work Queue, all 'MISI' -> MISI Work Queue.
            if( $misiWorkQueue ) {
                if( strpos((string)$productId, 'MISI-') !== false ) {
                    $listEntity->addWorkQueue($misiWorkQueue);
                }
            }
            if( $ctpWorkQueue ) {
                if( strpos((string)$productId, 'TRP-') !== false ) {
                    $listEntity->addWorkQueue($ctpWorkQueue);
                }
            }

            //exit('exit generateObjectTypeActions');
            $em->persist($listEntity);
            $em->flush();

            $count = $count + 10;
        }

        //sync fees with $productId 'TRP-' and 'MISI-' => CTP or MISI Work Queues respectively
        //TODO: assign CTP or MISI Work Queues according to the $productId: all 'TRP' -> CTP Work Queue, all 'MISI' -> MISI Work Queue.
        //$testing = true;
        $testing = false;
        $syncRes = $transresUtil->syncFeeAndWorkQueue($testing); //$testing=true
        //Flash
        $this->addFlash(
            'notice',
            $syncRes
        );

        return round($count/10);
    }
//    public function assignWorkQueueToFee( $fee, $workQueue ) {
    //        if( !$fee ) {
    //            return NULL;
    //        }
    //        if( !$workQueue ) {
    //            return NULL;
    //        }
    //    }
    //add all MD users to "Pathology Result Signatories" (set the name of each list item to "FirstName LastName, MD" and set the "Object ID" to the corresponding user ID)
    #[Route(path: '/list/add-mdusers-to-pathology-result-signatories/', name: 'employees_add-mdusers-to-pathology-result-signatories', methods: ['GET'])]
    public function addMDUsersToPathologyResultSignatoriesList(Request $request)
    {
        if( false === $this->isGranted('ROLE_PLATFORM_DEPUTY_ADMIN') ) {
            return $this->redirect( $this->generateUrl($this->getParameter('employees.sitename').'-nopermission') );
        }

        $em = $this->getDoctrine()->getManager();
        $creator = $this->getUser();
        $userSecUtil = $this->container->get('user_security_utility');

        //user_trainings_0_degree
        $repository = $em->getRepository(User::class);
        $dql =  $repository->createQueryBuilder("user");
        $dql->select('user');

        $dql->leftJoin("user.trainings", "trainings");
        $dql->leftJoin("trainings.degree", "degree");

        $dql->leftJoin("user.employmentStatus", "employmentStatus");
        $dql->leftJoin("employmentStatus.employmentType", "employmentType");


        $dql->where("degree.name = :degreeMd");
        $dql->andWhere("employmentType.name != :fellappType");

        $query = $dql->getQuery(); //$query = $em->createQuery($dql);

        $query->setParameters(array(
            'degreeMd' => 'MD',
            'fellappType' => "'Pathology Fellowship Applicant'"
        ));

        $users = $query->getResult();
        //$count = count($users);

        $count = 1;

        //add users to PathologyResultSignatoriesList
        foreach( $users as $user ) {
            //"FirstName LastName, MD"
            $name = $user->getUsernameOptimal();
            //echo "<br> $count User: ".$name."<br>";

        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:PathologyResultSignatoriesList'] by [PathologyResultSignatoriesList::class]
            $listEntity = $em->getRepository(PathologyResultSignatoriesList::class)->findOneByName($name);
            if( $listEntity ) {
                continue;
            }

            $listEntity = new PathologyResultSignatoriesList();
            $this->setDefaultList($listEntity,null,$creator,$name);

            $listEntity->setObject($user);

            //set object type
            //$eventObjectType = $userSecUtil->getObjectByNameTransformer($creator,"User",'UserdirectoryBundle','EventObjectTypeList');
            //if( $eventObjectType ) {
            //    $listEntity->setObjectType($eventObjectType);
            //}

            echo "<br> $count: adding user: ".$name."<br>";

            $userWrapper = $listEntity->getUserWrapper();
            echo "userWrapper=".$userWrapper."<br>";
            if( !$userWrapper ) {
                echo "User wrapper is null <br>";
                $userWrapperTransformer = new SingleUserWrapperTransformer($em, $this->container, $creator, 'UserWrapper');
                $userWrapper = $userWrapperTransformer->createNewUserWrapperByUserId($user->getId());
                $listEntity->setUserWrapper($userWrapper);
            }

            $em->persist($listEntity);
            $em->flush();

            $count++;

            echo "Added user: ".$name."<br>";
            //exit('end');
        }

        $count = $count - 1;

        exit("<br>Added MD users: ".$count);
    }
    /**
     * Remove all "Pathology Fellowship Applicant" users from PathologyResultSignatoriesList
     */
    #[Route(path: '/list/remove-fellapp-mdusers-to-pathology-result-signatories/', name: 'employees_remove-fellapp-mdusers-to-pathology-result-signatories', methods: ['GET'])]
    public function removeFellappMDUsersToPathologyResultSignatoriesList(Request $request) {
        $em = $this->getDoctrine()->getManager();
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:PathologyResultSignatoriesList'] by [PathologyResultSignatoriesList::class]
        $pathologists = $em->getRepository(PathologyResultSignatoriesList::class)->findAll();
        $count = 0;

        foreach( $pathologists as $pathologist ) {

            $userWrapper = $pathologist->getUserWrapper();
            if( $userWrapper ) {

                $user = $userWrapper->getUser();
                if( $this->hasEmploymentType($user,"Pathology Fellowship Applicant") ) {

                    echo "remove user=".$user."<br>";
                    //$pathologist->set
                    //$em->remove($userWrapper);
                    $em->remove($pathologist);
                    $em->flush();
                    $count++;
                    continue;

                } else {
                    //echo "do not remove user=".$user."<br>";
                }//else

            }//if

        }//foreach

        exit("<br>Removed MD users: ".$count);
    }
    public function hasEmploymentType( $user, $employmentTypeStr ) {
        if( $user ) {
            $employmentStatuses = $user->getEmploymentStatus();
            foreach( $employmentStatuses as $employmentStatus ) {
                $employmentType = $employmentStatus->getEmploymentType();
                if( $employmentType && $employmentType->getName() == $employmentTypeStr ) {
                    return true;
                }
            }
        }
        return false;
    }


    public function setFormNodeVersion() {
        $em = $this->getDoctrine()->getManager();
        //$query = $em->createQuery("UPDATE AppUserdirectoryBundle:FormNode node SET node.version = '1' WHERE node.version IS NULL");
        $query = $em->createQuery("UPDATE App\\UserdirectoryBundle\\Entity\\FormNode node SET node.version = '1' WHERE node.version IS NULL");
        $numUpdated = $query->execute();
        return "set formnode versions count ".$numUpdated;
    }

    /**
     * Generate metaphone key for th epatient last, first, middle names
     * run: http://localhost/order/directory/admin/generate-patient-metaphone-name/
     */
    #[Route(path: '/generate-patient-metaphone-name/', name: 'user_generate-patient-metaphone-name')]
    public function generatePatientMetaphoneNameKeyAction() {
        if( false === $this->isGranted('ROLE_PLATFORM_DEPUTY_ADMIN') ) {
            return $this->redirect( $this->generateUrl($this->getParameter('employees.sitename').'-nopermission') );
        }

        $userServiceUtil = $this->container->get('user_service_utility');
        $em = $this->getDoctrine()->getManager();

        //process.py script: replaced namespace by ::class: ['AppOrderformBundle:Patient'] by [Patient::class]
        $repository = $em->getRepository(Patient::class);
        $dql = $repository->createQueryBuilder("patient");
        $dql->select("patient");
        $dql->leftJoin('patient.lastname','lastname');
        $dql->leftJoin('patient.firstname','firstname');
        $dql->leftJoin('patient.middlename','middlename');
        //$criterion = "(lastname IS NOT NULL AND lastname.fieldMetaphone IS NULL)";
        $criterion = "(lastname.fieldMetaphone IS NULL)";
        $criterion = $criterion . " OR (firstname.fieldMetaphone IS NULL)";
        $criterion = $criterion . " OR (middlename.fieldMetaphone IS NULL)";

        $dql->where($criterion);
        $query = $dql->getQuery(); //$query = $em->createQuery($dql);
        $patients = $query->getResult();
        echo "Number of patients without metaphone key: ".count($patients)."<br>";

        foreach( $patients as $patient ) {
            echo "patient ".$patient->getId().": ".$patient->obtainFullObjectName()."<br>";
            //patient last name
            foreach( $patient->getLastname() as $name ) {
                if( !$name->getFieldMetaphone() ) {
                    $metaphoneKey = $userServiceUtil->getMetaphoneKey($name->getField());
                    $name->setFieldMetaphone($metaphoneKey);
                    echo "Last Name: set metaphone key: ".$name->getField()."=>".$metaphoneKey."<br>";
                }
            }

            //patient first name
            foreach( $patient->getFirstname() as $name ) {
                if( !$name->getFieldMetaphone() ) {
                    $metaphoneKey = $userServiceUtil->getMetaphoneKey($name->getField());
                    $name->setFieldMetaphone($metaphoneKey);
                    echo "First Name: set metaphone key: ".$name->getField()."=>".$metaphoneKey."<br>";
                }
            }

            //patient middle name
            foreach( $patient->getMiddlename() as $name ) {
                if( !$name->getFieldMetaphone() ) {
                    $metaphoneKey = $userServiceUtil->getMetaphoneKey($name->getField());
                    $name->setFieldMetaphone($metaphoneKey);
                    echo "Middle Name: set metaphone key: ".$name->getField()."=>".$metaphoneKey."<br>";
                }
            }

            $em->flush();
        }

        exit("Finished.");
    }

    /**
     * Update roles
     * run: http://127.0.0.1/order/directory/admin/opcache-reset/
     */
    #[Route(path: '/opcache-reset/', name: 'user_opcache-reset')]
    public function opcacheResetAction()
    {
        if (false === $this->isGranted('ROLE_PLATFORM_DEPUTY_ADMIN')) {
            return $this->redirect($this->generateUrl($this->getParameter('employees.sitename') . '-nopermission'));
        }

        $opcache_reset = opcache_reset();
        exit('opcache_reset=' . $opcache_reset);
    }

    /**
     * Update roles
     * run: http://127.0.0.1/order/directory/admin/update-user-roles/
     */
    #[Route(path: '/update-user-roles/', name: 'user_update_user_roles')]
    public function updateUserRolesAction()
    {
        if (false === $this->isGranted('ROLE_PLATFORM_DEPUTY_ADMIN')) {
            return $this->redirect($this->generateUrl($this->getParameter('employees.sitename') . '-nopermission'));
        }

        //testing
        //$opcache_reset = opcache_reset();
        //exit('opcache_reset='.$opcache_reset);

        exit("not permitted. It has been used only for changing the TRP roles.");

        $em = $this->getDoctrine()->getManager();

        $roles = array(
            "ROLE_TRANSRES_ADMIN",
            "ROLE_TRANSRES_TECHNICIAN",
            "ROLE_TRANSRES_REQUESTER",
            "ROLE_TRANSRES_IRB_REVIEWER",
            "ROLE_TRANSRES_COMMITTEE_REVIEWER",
            "ROLE_TRANSRES_PRIMARY_REVIEWER",
            "ROLE_TRANSRES_BILLING_ADMIN",
            "ROLE_TRANSRES_HEMATOPATHOLOGY",
            "ROLE_TRANSRES_APCP",
            "ROLE_TRANSRES_COVID19"
            //"ROLE_TRANSRES_EXECUTIVE_APCP",
            //"ROLE_TRANSRES_EXECUTIVE_HEMATOPATHOLOGY"
        );

        //1) get all users with TRANSRES roles
        $users = $em->getRepository(User::class)->findUsersByRoles($roles);

        $msg = "Found ".count($users). " transres users <br>";

        foreach($users as $user) {
            $msg = $msg . "User $user: ";
            $hema = false;
            $apcp = false;
            $covid19 = false;
            if( $user->hasRole("ROLE_TRANSRES_HEMATOPATHOLOGY") ) {
                $user->removeRole("ROLE_TRANSRES_HEMATOPATHOLOGY");
                $msg = $msg . "Remove ROLE_TRANSRES_HEMATOPATHOLOGY role; ";
                $hema = true;
            }
            if( $user->hasRole("ROLE_TRANSRES_APCP") ) {
                $user->removeRole("ROLE_TRANSRES_APCP");
                $msg = $msg . "Remove ROLE_TRANSRES_APCP role; ";
                $apcp = true;
            }
            if( $user->hasRole("ROLE_TRANSRES_COVID19") ) {
                $user->removeRole("ROLE_TRANSRES_COVID19");
                $msg = $msg . "Remove ROLE_TRANSRES_COVID19 role; ";
                $covid19 = true;
            }

            if( $apcp == false && $hema == false && $covid19 == false ) {
                $apcp = true;
            }

            foreach($roles as $role) {
                //$msg = $msg . "[$role]: ";
                if( $role == "ROLE_TRANSRES_EXECUTIVE_APCP" || $role == "ROLE_TRANSRES_EXECUTIVE_HEMATOPATHOLOGY" ) {
                    continue; //skip
                }
                if( $role == "ROLE_TRANSRES_APCP" || $role == "ROLE_TRANSRES_HEMATOPATHOLOGY" ) {
                    continue; //skip
                }
                if( $user->hasRole($role) ) {
                    //$msg = $msg . "#######Has $role ##########; ";
                    if( $apcp ) {
                        $user->addRole($role . "_APCP");
                        $msg = $msg . "Update $role by _APCP; ";
                    }
                    if( $hema ) {
                        $user->addRole($role . "_HEMATOPATHOLOGY");
                        $msg = $msg . "Update $role by _HEMATOPATHOLOGY; ";
                    }
                    if( $covid19 ) {
                        $user->addRole($role . "_COVID19");
                        $msg = $msg . "Update $role by _COVID19; ";
                    }
                    if( $apcp || $hema || $covid19 ) {
                        $user->removeRole($role);
                        $msg = $msg . "###Remove $role###; ";
                    }
                }
            }

            $msg = $msg . "<br>Roles:" . implode(",",$user->getRoles())."<br>";

            $msg = $msg . "<br><br>";

            //$em->flush($user);
            $em->flush();

            //exit($msg);
        }

        exit($msg);
    }

    /**
     * Update user's postfix
     * username+postfix is required by symfony authentication token having only username $token->getUsername().
     * Postfix is used to determine the correspondint auth mechanism (ldap, local, external etc.)
     * Auth Transition:
     * 1) edit Username type (Platform List Manager Root List): add  "NYH User" - "ldap2-user"
     * 2) Set checkmark "Send request to both authentication Active Directory/LDAP servers when the first is selected for a single log in attempt"
     * 3) set "LDAP/AD Mapper Email Postfix (med.cornell.edu)"
     * 4) set section "AD/LDAP 2:" in site settings
     * 5) run http://c.med.cornell.edu/order/directory/admin/update-user-postfix/
     * 6) remove line 93 'case "wcmc-cwid":' from CustomAuthentication.php
     * 7) remove lines 226-230 from AuthUtil.php
     * 8) enable line 8980 exit() in this file
     * 9) Test ldap login
     *
     * run: http://127.0.0.1/order/directory/admin/update-user-postfix/
     */
    #[Route(path: '/update-user-postfix/', name: 'user_update_user_postfix')]
    public function updateUserPostfixAction()
    {
        if (false === $this->isGranted('ROLE_PLATFORM_DEPUTY_ADMIN')) {
            return $this->redirect($this->generateUrl($this->getParameter('employees.sitename') . '-nopermission'));
        }

        exit("not permitted. It has been used only for changing user's postfix once.");

        $em = $this->getDoctrine()->getManager();

        //1) get all users with TRANSRES roles
        $users = $em->getRepository(User::class)->findAll();

        $msg = "Found ".count($users). " users <br>";
        $count = 1;

        foreach($users as $user) {

            if( $user->getPrimaryPublicUserId() != "system" ) {
                if (!$user->usernameIsValid()) {
                    exit("Username is not valid for " . $user);
                }
            }

            //$cleanUsername = $user->createCleanUsername();
            
            if( $user->getUsernamePrefix() == "wcmc-cwid" )
            {
                //$newPostfix = "ldap-user";
                $newUsername = $user->createUniqueUsername();
                $user->setUsernameForce($newUsername);
                $user->setUsernameCanonicalForce($newUsername);
                echo $count.": Update postfix for " . $user . " to [" . $user->getUsername() . "],[" .$user->getUsernameCanonical(). "]<br>";
                //$em->flush($user);
                $em->flush();
            }
            elseif ( $user->getUsernamePrefix() == "aperio" )
            {
                //$newPostfix = "external";
                $newUsername = $user->createUniqueUsername();
                $user->setUsernameForce($newUsername);
                $user->setUsernameCanonicalForce($newUsername);
                echo $count.": Update postfix for " . $user . " to [" . $user->getUsername() . "],[" .$user->getUsernameCanonical(). "]<br>";
                //$em->flush($user);
                $em->flush();
            } else {
                echo $count." user is OK ".$user."<br>";
            }

            $count++;
        }

        exit($msg);
    }


//    /**
//     * TODO: NOT USED: Instead of this, try to use bundle: https://github.com/jr-k/JrkLevenshteinBundle
//     *
//     * run: http://localhost/order/directory/admin/init-fuzzy/
//     * @Route("/init-fuzzy/", name="user_init_fuzzy")
//     */
//    public function runDBFuzzyAction() {
//        exit("NOT USED");
//        $results = null;
//        $em = $this->getDoctrine()->getManager();
//        $connection = $em->getConnection();
//
//        //https://snippets.aktagon.com/snippets/610-levenshtein-distance-for-mysql
//        $sqlCode =
//        "CREATE FUNCTION levenshtein( s1 VARCHAR(255), s2 VARCHAR(255) )
//              RETURNS INT
//              DETERMINISTIC
//              BEGIN
//                DECLARE s1_len, s2_len, i, j, c, c_temp, cost INT;
//                DECLARE s1_char CHAR;
//                -- max strlen=255
//                DECLARE cv0, cv1 VARBINARY(256);
//                SET s1_len = CHAR_LENGTH(s1), s2_len = CHAR_LENGTH(s2), cv1 = 0x00, j = 1, i = 1, c = 0;
//                IF s1 = s2 THEN
//                  RETURN 0;
//                ELSEIF s1_len = 0 THEN
//                  RETURN s2_len;
//                ELSEIF s2_len = 0 THEN
//                  RETURN s1_len;
//                ELSE
//                  WHILE j <= s2_len DO
//                    SET cv1 = CONCAT(cv1, UNHEX(HEX(j))), j = j + 1;
//                  END WHILE;
//                  WHILE i <= s1_len DO
//                    SET s1_char = SUBSTRING(s1, i, 1), c = i, cv0 = UNHEX(HEX(i)), j = 1;
//                    WHILE j <= s2_len DO
//                      SET c = c + 1;
//                      IF s1_char = SUBSTRING(s2, j, 1) THEN
//                        SET cost = 0; ELSE SET cost = 1;
//                      END IF;
//                      SET c_temp = CONV(HEX(SUBSTRING(cv1, j, 1)), 16, 10) + cost;
//                      IF c > c_temp THEN SET c = c_temp; END IF;
//                        SET c_temp = CONV(HEX(SUBSTRING(cv1, j+1, 1)), 16, 10) + 1;
//                        IF c > c_temp THEN
//                          SET c = c_temp;
//                        END IF;
//                        SET cv0 = CONCAT(cv0, UNHEX(HEX(c))), j = j + 1;
//                    END WHILE;
//                    SET cv1 = cv0, i = i + 1;
//                  END WHILE;
//                END IF;
//                RETURN c;
//              END$$";
//
//        $statement = $connection->prepare($sqlCode);
//        $statement->execute();
//
//        exit("results ".$results);
//    }
}
