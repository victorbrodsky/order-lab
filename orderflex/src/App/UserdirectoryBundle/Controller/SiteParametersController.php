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



use App\UserdirectoryBundle\Entity\AuthServerNetworkList;
use App\UserdirectoryBundle\Entity\Document; //process.py script: replaced namespace by ::class: added use line for classname=Document


use App\UserdirectoryBundle\Entity\Roles; //process.py script: replaced namespace by ::class: added use line for classname=Roles
use App\UserdirectoryBundle\Entity\User;
//use App\UserdirectoryBundle\Form\TenancyManagementType;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\PersistentCollection;
use App\UserdirectoryBundle\Entity\OrganizationalGroupDefault;
use App\UserdirectoryBundle\Form\InitialConfigurationType;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use App\UserdirectoryBundle\Controller\OrderAbstractController;


use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\Annotation\Route;
use App\UserdirectoryBundle\Entity\SiteParameters;
use App\UserdirectoryBundle\Form\SiteParametersType;
use App\UserdirectoryBundle\Util\UserUtil;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * SiteParameters controller.
 */
#[Route(path: '/settings')]
class SiteParametersController extends OrderAbstractController
{

    /**
     * Lists all SiteParameters entities.
     */
    #[Route(path: '/settings-id/{id}', name: 'employees_siteparameters_id', methods: ['GET'])]
    #[Template('AppUserdirectoryBundle/SiteParameters/index.html.twig')]
    public function indexIdAction(Request $request, $id=null)
    {
        if (false === $this->isGranted('ROLE_PLATFORM_DEPUTY_ADMIN')) {
            return $this->redirect($this->generateUrl('employees-nopermission'));
        }

        return $this->redirect($this->generateUrl('employees_siteparameters'));
    }

    //@Route("/{id}", name="employees_siteparameters_id")
    //, $id=null
    /**
     * Lists all SiteParameters entities.
     */
    #[Route(path: '/', name: 'employees_siteparameters', methods: ['GET'])]
    #[Template('AppUserdirectoryBundle/SiteParameters/index.html.twig')]
    public function indexAction(Request $request)
    {
        if( false === $this->isGranted('ROLE_PLATFORM_DEPUTY_ADMIN') ) {
            return $this->redirect( $this->generateUrl('employees-nopermission') );
        }

        //testing
//        $userServiceUtil = $this->container->get('user_service_utility');
//        if( $userServiceUtil->isWinOs() ) {
//            echo "Windows <br>";
//        } else {
//            echo "Not Windows <br>";
//        }

        //testing
        //exit("user indexAction");
        //$connection_channel = $this->getParameter('connection_channel');
        //echo "connection_channel=".$connection_channel."<br>";

        return $this->indexParameters($request);
    }

    public function indexParameters($request) {

//        if( false === $this->isGranted('ROLE_PLATFORM_DEPUTY_ADMIN') ) {
//            return $this->redirect( $this->generateUrl('employees-nopermission') );
//        }

        //testing email
        //$emailUtil = $this->container->get('user_mailer_utility');
        //$emailUtil->sendEmail( "oli2002@med.cornell.edu", "testing email", "testing email" );

        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository(SiteParameters::class)->findAll();

        //make sure sitesettings is initialized
        if( count($entities) != 1 ) {
            $userServiceUtil = $this->container->get('user_service_utility');
            $userServiceUtil->generateSiteParameters();
            $entities = $em->getRepository(SiteParameters::class)->findAll();
        }

        if( count($entities) != 1 ) {
            throw new \Exception( 'Must have only one parameter object. Found '.count($entities).' object(s)' );
        }

        $entity = $entities[0];

        $routeName = $request->get('_route');
        $routeArr = explode("_", $routeName);
        $sitename = $routeArr[0];

//        if( $sitename == "translationalresearch" ) {
//            //exception for transres site
//            if( false === $this->isGranted('ROLE_TRANSRES_ADMIN') ) {
//                return $this->redirect( $this->generateUrl('employees-nopermission') );
//            }
//        } else {
//            if( false === $this->isGranted('ROLE_PLATFORM_DEPUTY_ADMIN') ) {
//                return $this->redirect( $this->generateUrl('employees-nopermission') );
//            }
//        }

        $disabled = true;

        //Hide password
        if(0) {
            $passw = "*******";
            if ($entity->getPacsvendorSlideManagerDBPassword() != '')
                $entity->setPacsvendorSlideManagerDBPassword($passw);

            if ($entity->getLisDBAccountPassword() != '')
                $entity->setLisDBAccountPassword($passw);

            if ($entity->getADLDAPServerAccountPassword() != '')
                $entity->setADLDAPServerAccountPassword($passw);

            if ($entity->getDbServerAccountPassword() != '')
                $entity->setDbServerAccountPassword($passw);

            if ($entity->getMailerPassword() != '')
                $entity->setMailerPassword($passw);

            if ($entity->getCaptchaSiteKey() != '')
                $entity->setCaptchaSiteKey($passw);

            if ($entity->getCaptchaSecretKey() != '')
                $entity->setCaptchaSecretKey($passw);
        }

        //testing
        //$organizationalGroupDefault = new OrganizationalGroupDefault();
        //$entity->addOrganizationalGroupDefault($organizationalGroupDefault);
//        foreach( $entity->getOrganizationalGroupDefaults() as $groupDefault ) {
//            echo "roles=".$groupDefault->getRoles()."<br>";
//            print_r($groupDefault->getRoles());
//        }

        $singleField = false;

        //$sitename,SiteParameters $entity, $param=null, $disabled=false
        $editForm = $this->createEditForm($sitename,$entity,null,$disabled,$singleField);

        //$link = realpath($_SERVER['DOCUMENT_ROOT']).DIRECTORY_SEPARATOR.'scanorder'.DIRECTORY_SEPARATOR.'Scanorders2'.
        //    DIRECTORY_SEPARATOR.'app'.DIRECTORY_SEPARATOR.'config'.DIRECTORY_SEPARATOR.'parameters.yml';
        $link = 'parameters.yml';
        //echo "link=".$link."<br>";

        //get absolute path prefix for Upload folder
        //$rootDir = $this->container->get('kernel')->getRootDir(); //C:\Users\ch3\Documents\MyDocs\WCMC\ORDER\scanorder\Scanorders2\app
        //$rootDir = str_replace('app','',$rootDir);
        //$uploadPath = $rootDir . 'public' . DIRECTORY_SEPARATOR;
        $projectDir = $this->container->get('kernel')->getProjectDir();
        //$projectDir = $this->getProjectDir();
        $uploadPath = $projectDir . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR;

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'cycle' => 'show',
            'link' => $link,
            'uploadPath' => $uploadPath,
            'sitename' => $sitename,
            'phphostname' => gethostname()
        );
    }

    /**
     * Displays a form to edit an existing SiteParameters entity.
     */
    #[Route(path: '/{id}/edit', name: 'employees_siteparameters_edit', methods: ['GET'])]
    #[Template('AppUserdirectoryBundle/SiteParameters/edit.html.twig')]
    public function editAction( Request $request, $id )
    {
        //exit("user editAction");
        return $this->editParameters($request,$id);
    }

    public function editParameters( Request $request, $id, $role=null )
    {
        $param = trim((string)$request->get('param') );

        $routeName = $request->get('_route');
        $routeArr = explode("_", $routeName);
        $sitename = $routeArr[0];
        //exit('role='.$role."; sitename=".$sitename.", param=".$param);

        if( $role ) {
            if( false === $this->isGranted($role) ) {
                return $this->redirect( $this->generateUrl($sitename.'-nopermission') );
            }
        } else {
            if( false === $this->isGranted('ROLE_PLATFORM_DEPUTY_ADMIN') ) {
                return $this->redirect( $this->generateUrl($sitename.'-nopermission') );
            }
        }

        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository(SiteParameters::class)->find($id);

        if( !$entity ) {
            throw $this->createNotFoundException('Unable to find SiteParameters entity.');
        }

        $editForm = $this->createEditForm($sitename,$entity,$param,false);
        //$deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'cycle' => 'edit',
            'param' => $param,
            'sitename' => $sitename,
            //'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Edits an existing SiteParameters entity.
     */
    #[Route(path: '/{id}', name: 'employees_siteparameters_update', methods: ['PUT'])]
    #[Template('AppUserdirectoryBundle/SiteParameters/edit.html.twig')]
    public function updateAction(Request $request, $id)
    {
        return $this->updateParameters($request, $id);
    }

    public function updateParameters(Request $request, $id, $role=null)
    {

        $param = trim((string)$request->get('param') );
        //echo "param=".$param."<br>";
        //exit('111');

        $routeName = $request->get('_route');
        $routeArr = explode("_", $routeName);
        $sitename = $routeArr[0];

        if( $role ) {
            if( false === $this->isGranted($role) ) {
                return $this->redirect( $this->generateUrl($sitename.'-nopermission') );
            }
        } else {
            if( false === $this->isGranted('ROLE_PLATFORM_DEPUTY_ADMIN') ) {
                return $this->redirect( $this->generateUrl($sitename.'-nopermission') );
            }
        }

        $userServiceUtil = $this->container->get('user_service_utility');
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository(SiteParameters::class)->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find SiteParameters entity.');
        }

        //get method
        $getMethod = "get".$param;
        $originalParam = $entity->$getMethod();

        $editForm = $this->createEditForm($sitename,$entity,$param,false);

        $editForm->handleRequest($request);

//        foreach ($editForm->all() as $field) {
//            if (!$field->isValid()) {
//                foreach ($field->getErrors() as $error) {
//                    echo $field->getName() . ": " . $error->getMessage() . "<br>";
//                }
//            }
//        }

        if( $editForm->isValid() ) {
            //exit("updateParameters: valid");
            $updatedParam = $entity->$getMethod();

            if( $param == 'platformLogos' ) {
                $em->getRepository(Document::class)->processDocuments($entity,"platformLogo");
                if( $originalParam && count($originalParam)>0 ) {
                    $platformLogo = $originalParam->first();
                    //$originalParam = $platformLogo->getAbsoluteUploadFullPath();
                    $originalParam = $userServiceUtil->getDocumentAbsoluteUrl($platformLogo);
                } else {
                    $originalParam = null;
                }
                if( $updatedParam && count($updatedParam)>0 ) {
                    $platformLogo = $updatedParam->first();
                    //$updatedParam = $platformLogo->getAbsoluteUploadFullPath();
                    $updatedParam = $userServiceUtil->getDocumentAbsoluteUrl($platformLogo);
                } else {
                    $updatedParam = null;
                }
                //exit("originalParam=$originalParam; updatedParam=$updatedParam");
            }

            $em->flush();

            $redirectPathPostfix = '_siteparameters';

            //Exception: edit "calllogResources" param can be invoked from a specific controller "calllog_siteparameters_resources_edit" by non-admin user =>
            //redirect to another page, in this case 'calllog_resources' instead of the _siteparameters page which is accessible only by admin.
            if( $param == 'calllogResources' ) {
                $redirectPathPostfix = '_resources';
            }

            if( $param == 'mailerSpool' || $param == 'mailerFlushQueueFrequency' ) {
                $emailUtil = $this->container->get('user_mailer_utility');
                $emailUtil->createEmailCronJob();
            }

            //convert possible array to string
            if( is_array($originalParam) || $originalParam instanceof PersistentCollection ) {
                $originalParamArr = array();
                foreach($originalParam as $singleOriginalParam) {
                    $originalParamArr[] = $singleOriginalParam."";
                }
                $originalParam = implode("; ",$originalParamArr);
            }
            if( is_array($updatedParam) || $updatedParam instanceof PersistentCollection ) {
                $updatedParamArr = array();
                foreach($updatedParam as $singleOriginalParam) {
                    $updatedParamArr[] = $singleOriginalParam."";
                }
                $updatedParam = implode("; ",$updatedParamArr);
            }

            if( $param == 'academicYearStart' || $param == 'academicYearEnd' ) {
                if( $originalParam )
                    $originalParam = $originalParam->format('m/d');
                if( $updatedParam )
                    $updatedParam = $updatedParam->format('m/d');
            }

            //add a new eventlog record for an updated parameter
            $user = $this->getUser();
            $userSecUtil = $this->container->get('user_security_utility');
            $eventType = "Site Settings Parameter Updated";
            $eventStr = "Site Settings parameter [$param] has been updated by ".$user;
            $eventStr = $eventStr . "<br>original value:<br>".$originalParam;
            $eventStr = $eventStr . "<br>updated value:<br>".$updatedParam;
            $userSecUtil->createUserEditEvent($sitename, $eventStr, $user, $entity, $request, $eventType);

            ///// redirect to the individual site setting parameters pages /////
            if( $param == 'mailerSpool' || $param == 'mailerFlushQueueFrequency' ) {
                return $this->redirect($this->generateUrl('employees_general_cron_jobs'));
            }
            if( $param == 'monitorCheckInterval' || $param == 'externalMonitorUrl' || $param == 'monitorScript' ) {
                return $this->redirect($this->generateUrl('employees_health_monitor'));
            }
            if( $param == 'filesBackupConfig' ) {
                return $this->redirect($this->generateUrl('employees_data_backup_management'));
            }
            ///// EOF redirect to the individual site setting parameters pages /////

            return $this->redirect($this->generateUrl($sitename.$redirectPathPostfix)); //'_siteparameters'
        } else {
            exit("updateParameters: invalid");
        }

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'cycle' => 'edit',
            'param' => '',
            'sitename' => $sitename
        );
    }


    /**
     * Edits an existing SiteParameters entity.
     */
    #[Route(path: '/organizational-group-default-management/{id}', name: 'employees_management_organizationalgroupdefault', methods: ['GET', 'POST'])]
    #[Template('AppUserdirectoryBundle/SiteParameters/group-management-form.html.twig')]
    public function manageOrgGroupDefaultAction(Request $request, $id)
    {

        if( false === $this->isGranted('ROLE_PLATFORM_DEPUTY_ADMIN') ) {
            return $this->redirect( $this->generateUrl('employees-nopermission') );
        }

        $em = $this->getDoctrine()->getManager();

        $routeName = $request->get('_route');
        $routeArr = explode("_", $routeName);
        $sitename = $routeArr[0];

        $entity = $em->getRepository(SiteParameters::class)->find($id);

        if( !$entity ) {
            throw $this->createNotFoundException('Unable to find SiteParameters entity.');
        }

        //testing
        if( count($entity->getOrganizationalGroupDefaults()) == 0 ) {
            $organizationalGroupDefault = new OrganizationalGroupDefault();
            $entity->addOrganizationalGroupDefault($organizationalGroupDefault);
        }

        $originalGroups = new ArrayCollection();
        // Create an ArrayCollection of the current Tag objects in the database
        foreach( $entity->getOrganizationalGroupDefaults() as $groupDefault ) {
            $originalGroups->add($groupDefault);
        }

        //Roles
        $rolesArr = $this->getUserRoles(); //site settings org group form SiteParametersType

        $params = array(
            'sitename'=>$sitename,
            'cycle'=>"edit",
            'em'=>$em,
            'roles' => $rolesArr,
            'singleField' => false
        );

        $form = $this->createForm(SiteParametersType::class, $entity, array(
            'form_custom_value' => $params,
            'action' => $this->generateUrl('employees_management_organizationalgroupdefault', array('id' => $entity->getId() )),
            'method' => 'POST',
            'disabled' => false,
        ));

        $form->add('save', SubmitType::class, array(
            'label' => 'Update',
            'attr' => array('class'=>'btn btn-primary')
        ));

        $form->handleRequest($request);

        //check for empty target institution
        if( $form->isSubmitted() ) {

            $instArr = new ArrayCollection();
            foreach ($entity->getOrganizationalGroupDefaults() as $organizationalGroupDefault) {
                if (!$organizationalGroupDefault->getInstitution()) {
                    $form->addError(new FormError('Please select the Target Institution for all Organizational Group Management Sections'));
                    //$form['organizationalGroupDefaults']->addError(new FormError('Please select the Target Institution'));
                    //$entity->removeOrganizationalGroupDefault($organizationalGroupDefault);
                } else {
                    if( $instArr->contains($organizationalGroupDefault->getInstitution()) ) {
                        $form->addError(new FormError('Please make sure that the Target Institutions are Unique in all Organizational Group Management Sections'));
                    } else {
                        $instArr->add($organizationalGroupDefault->getInstitution());
                    }
                }
            }

        }

//        if( $form->isSubmitted() ) {
//            echo "form is submitted<br>";
//
//            if( $form->isValid() ) {
//                echo "form is valid<br>";
//            } else {
//                print_r((string) $form->getErrors());     // Main errors
//                print_r((string) $form->getErrors(true)); // Main and child errors
//                //exit('1');
//            }
//        }

        if( $form->isSubmitted() && $form->isValid() ) {

            //exit("form is valid");

            // remove the relationship between the tag and the Task
            foreach( $originalGroups as $originalGroup ) {
                $currentGroups = $entity->getOrganizationalGroupDefaults();
                if( false === $currentGroups->contains($originalGroup) ) {
                    // remove the Task from the Tag
                    $entity->removeOrganizationalGroupDefault($originalGroup);

                    // if it was a many-to-one relationship, remove the relationship like this
                    $originalGroup->setSiteParameter(null);

                    $em->persist($originalGroup);
                    // if you wanted to delete the Tag entirely, you can also do that
                    $em->remove($originalGroup);
                }
            }

            //testing
//            foreach( $entity->getOrganizationalGroupDefaults() as $group ) {
//                echo "primary Type=".$group->getPrimaryPublicUserIdType()."<br>";
//            }
            //exit('1');

            //$em->persist($entity);
            $em->flush();

            $this->addFlash(
                'notice',
                "Defaults for an Organizational Group have been updated."
            );

            return $this->redirect($this->generateUrl($sitename.'_siteparameters'));
        }

        return array(
            'entity'      => $entity,
            'form'   => $form->createView(),
            'cycle' => 'edit',
            'sitename' => $sitename
        );
    }



    /**
     * Creates a form to edit a SiteParameters entity.
     *
     * @param SiteParameters $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createEditForm( $sitename, SiteParameters $entity, $param=null, $disabled=false, $singleField=true )
    {
        $em = $this->getDoctrine()->getManager();

        $cycle = 'show';

        if( !$disabled ) {
            $cycle = 'edit';
        }

        //Roles
        $rolesArr = $this->getUserRoles(); //site setting edit form SiteParametersType

        $params = array(
            'sitename'=>$sitename,
            'cycle'=>$cycle,
            'em'=>$em,
            'param'=>$param,
            'roles'=>$rolesArr,
            'singleField'=>$singleField
        );

        $form = $this->createForm(SiteParametersType::class, $entity, array(
            'form_custom_value' => $params,
            'action' => $this->generateUrl($sitename.'_siteparameters_update', array('id' => $entity->getId(), 'param' => $param )),
            'method' => 'PUT',
            'disabled' => $disabled
        ));

        if( $disabled === false ) {
            $form->add('submit', SubmitType::class, array('label' => 'Update', 'attr'=>array('class'=>'btn btn-warning','style'=>'margin-top: 15px;')));
        }

        return $form;
    }

    public function getUserRoles() {
        $rolesArr = array();
        $em = $this->getDoctrine()->getManager();
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:Roles'] by [Roles::class]
        $roles = $em->getRepository(Roles::class)->findBy(
            array('type' => array('default','user-added')),
            array('orderinlist' => 'ASC')
        );  //findAll();
        foreach( $roles as $role ) {
            //$rolesArr[$role->getName()] = $role->getAlias();
            $rolesArr[$role->getAlias()] = $role->getName();
        }
        return $rolesArr;
    }



    /**
     * http://127.0.0.1/order/index_dev.php/directory/settings/initial-configuration
     * Initial Configuration Completed
     */
    #[Route(path: '/initial-configuration', name: 'employees_initial_configuration', methods: ['GET', 'POST'])]
    #[Template('AppUserdirectoryBundle/SiteParameters/initial-configuration.html.twig')]
    public function initialConfigurationAction(Request $request)
    {
        //exit('EXIT: initialConfigurationAction');
        if( false === $this->isGranted('ROLE_PLATFORM_DEPUTY_ADMIN') ) {
            return $this->redirect( $this->generateUrl('employees-nopermission') );
        }

        $em = $this->getDoctrine()->getManager();
        //$encoder = $this->container->get('security.password_encoder');
        //$userSecUtil = $this->container->get('user_security_utility');
        //$routeName = $request->get('_route');

        $administratorUser = $this->getUser();
        if( strtolower($administratorUser->getPrimaryPublicUserId()) != "administrator" ) {
            $administratorUser = $em->getRepository(User::class)->findOneByPrimaryPublicUserId("administrator");
            if( !$administratorUser ) {
                throw new \Exception('Initial Configuration: administrator user not found.');
            }
        }

        $entities = $em->getRepository(SiteParameters::class)->findAll();

        if( count($entities) != 1 ) {
            $userServiceUtil = $this->container->get('user_service_utility');
            $userServiceUtil->generateSiteParameters();
            $entities = $em->getRepository(SiteParameters::class)->findAll();
        }

        if( count($entities) != 1 ) {
            throw new \Exception( 'Must have only one parameter object. Found '.count($entities).' object(s)' );
        }

        $entity = $entities[0];

        //exit('initial ConfigurationAction');
        //echo "SiteParameters=".$entity->getId()."<br>";
        //exit('EXIT: SiteParameters Found');

        $form = $this->createForm(InitialConfigurationType::class, $entity, array(
            'action' => $this->generateUrl('employees_initial_configuration', array('id' => $entity->getId() )),
            'method' => 'POST',
            'disabled' => false,
        ));

        $form->handleRequest($request);

        if( $form->isSubmitted() && $form->isValid() ) {

            //exit("initialConfigurationAction: form is valid");

            $modifiedAdminUser = false;

            //unmapped password
            $password = $form['password']->getData();
            //echo "password=".$password."<br>";
            if( $password ) {
                //$encoded = $encoder->encodePassword($administratorUser, $password);
                $authUtil = $this->container->get('authenticator_utility');
                $encoded = $authUtil->getEncodedPassword($administratorUser, $password);
                //echo "encoded=" . $encoded . "<br>";
                $administratorUser->setPassword($encoded);
                $modifiedAdminUser = true;
            }

            //email
            $email = $entity->getSiteEmail();
            if( $email ) {
                $administratorUser->setEmail($email);
                $administratorUser->setEmailCanonical($email);
                $modifiedAdminUser = true;
            }

            //$initialConfigurationCompleted = $userSecUtil->getSiteSettingParameter('initialConfigurationCompleted');
            $entity->setInitialConfigurationCompleted(true);

            if( $modifiedAdminUser ) {
                $em->persist($administratorUser);
                //$em->flush($administratorUser);
                $em->flush();
            }

            $em->persist($entity);
            //$em->flush($entity);
            $em->flush();

            $emailUtil = $this->container->get('user_mailer_utility');
            $emailUtil->createEmailCronJob();

            //exit("form is valid");

            //url: user_update_system_cache_assets
            $urlUpdateCacheAssets = $this->container->get('router')->generate(
                'user_update_system_cache_assets',
                array(),
                UrlGeneratorInterface::ABSOLUTE_URL
            );

            $this->addFlash(
                'notice',
                "Thank You for completing the initial configuration!<br>".
                'Please update <a href="' . $urlUpdateCacheAssets . '">System\'s Cache and Assets</a> to update the footer.<br>'.
                "You can set other options to ensure proper operation on this 'Site Settings' page!"
            );

            //echo "SiteParameters Submit Done! <br>";
            //exit('EXIT: SiteParameters Submit Done!');
            return $this->redirect($this->generateUrl('employees_siteparameters'));
        }
        //echo "SiteParameters show form <br>";

        return array(
            'title' => "Thank You for installing O R D E R!",
            'entity'      => $entity,
            'form'   => $form->createView(),
            'cycle' => 'edit',
            'sitename' => 'employees'
        );
    }

    //https://view.online/c/test-institution/test-department/directory/settings/update-parameters
    #[Route(path: '/update-parameters', name: 'employees_update_parameters', methods: ['GET'])]
    public function setTenantUrlAction(Request $request)
    {
        //exit('EXIT: setTenantUrlAction');
        if (false === $this->isGranted('ROLE_PLATFORM_DEPUTY_ADMIN')) {
            return $this->redirect($this->generateUrl('employees-nopermission'));
        }
        $currentUrl = $request->getUri();
        $projectDir = $this->container->get('kernel')->getProjectDir();
        $yamlPath = $projectDir . '/config/parameters.yml';
        $res = $this->updateTenantBaseFromUrl($currentUrl, $yamlPath);

        if( $res ) {
            $this->addFlash(
                'notice',
                "Updated $yamlPath"
            );
        } else {
            $this->addFlash(
                'notice',
                "Error updating $yamlPath"
            );
        }

        return $this->redirect( $this->generateUrl('main_common_home') );
    }
    function updateTenantBaseFromUrl(string $url, string $yamlPath): bool
    {
        // Step 1: Extract tenant base from URL
        $parsed = parse_url($url);
        $path = $parsed['path'] ?? '';
        $segments = explode('/', trim($path, '/'));

        // Look for 'c' followed by two segments
        $cIndex = array_search('c', $segments);
        if ($cIndex === false || !isset($segments[$cIndex + 1], $segments[$cIndex + 2])) {
            return false; // Invalid structure
        }

        $tenantBase = 'c/' . $segments[$cIndex + 1] . '/' . $segments[$cIndex + 2];

        // Step 2: Load and update parameters.yaml
        $yaml = file_get_contents($yamlPath);
        if ($yaml === false) return false;

        // Replace or insert tenant_base
        if (preg_match('/tenant_base:\s*[^\n]*/', $yaml)) {
            $yaml = preg_replace('/tenant_base:\s*[^\n]*/', "tenant_base: $tenantBase", $yaml);
        } else {
            $yaml .= "\ntenant_base: $tenantBase\n";
        }

        // Step 3: Write back to file
        return file_put_contents($yamlPath, $yaml) !== false;
    }


//    #[Route(path: '/tenancy-management', name: 'employees_tenancy_management', methods: ['GET', 'POST'])]
//    #[Template('AppSystemBundle/tenancy-management.html.twig')]
//    public function tenancyManagementAction( Request $request, KernelInterface $kernel )
//    {
//        $tenantRole = $this->getParameter('tenant_role');
//        if( $tenantRole != 'tenantmanager' ) {
//            if( !$tenantRole ) {
//                $tenantRole = 'undefined';
//            }
//            $this->addFlash(
//                'warning',
//                "Tenancy settings is accessible only from tenant manager system. Current system is $tenantRole"
//            );
//            return $this->redirect( $this->generateUrl('employees-nopermission') );
//        }
//
//        //ROLE_PLATFORM_DEPUTY_ADMIN or ROLE_SUPER_DEPUTY_ADMIN
//        if( false === $this->isGranted('ROLE_PLATFORM_DEPUTY_ADMIN') ) {
//            $this->addFlash(
//                'warning',
//                "Tenancy settings is accessible only by ROLE_SUPER_DEPUTY_ADMIN."
//            );
//            return $this->redirect( $this->generateUrl('employees-nopermission') );
//        }
//
//        //only if local is system
////        $locale = $request->getLocale();
////        //exit('$locale='.$locale);
////        if( $locale != "system" ) {
////            $this->addFlash(
////                'warning',
////                "Tenancy settings is accessible only for system database. Please relogin to /system"
////            );
////            return $this->redirect( $this->generateUrl('employees-nopermission') );
////        }
//
//        $em = $this->getDoctrine()->getManager();
//        //$userSecUtil = $this->container->get('user_security_utility');
//        //$siteParam = $userSecUtil->getSingleSiteSettingsParam();
//        $userServiceUtil = $this->container->get('user_service_utility');
//        $siteParam = $userServiceUtil->getSingleSiteSettingParameter();
//
//        if( !$siteParam ) {
//            throw $this->createNotFoundException('Unable to find SiteParameters entity.');
//        }
//
//        $title = "Tenancy Management";
//
//        //find AuthServerNetworkList by name "Internet (Hub)" => show hostedGroupHolders (authservernetwork_edit)
//        $authServerNetwork = $em->getRepository(AuthServerNetworkList::class)->findOneByName('Internet (Hub)');
//        $authServerNetworkId = null;
//        if( $authServerNetwork ) {
//            $authServerNetworkId = $authServerNetwork->getId();
//        }
//
//        $params = array(
//            //'cycle'=>"edit",
//            //'em'=>$em,
//        );
//
//        $form = $this->createForm(TenancyManagementType::class, $siteParam, array(
//            'form_custom_value' => $params,
//        ));
//
//        $form->handleRequest($request);
//
//        if( $form->isSubmitted() && $form->isValid() ) {
//
//            //exit("form is valid");
//
//            $em->flush();
//
//            $this->addFlash(
//                'notice',
//                "Tenancy settings have been updated."
//            );
//
////            //runDeployScript
////            $userServiceUtil = $this->container->get('user_service_utility');
////            //$userServiceUtil->runDeployScript(false,false,true);
////            $output = $userServiceUtil->clearCacheInstallAssets($kernel);
////            $this->addFlash(
////                'notice',
////                "Container rebuilded, cache cleared, assets dumped. Output=".$output
////            );
//
//            //exit('111');
//            return $this->redirect($this->generateUrl('employees_tenancy_management'));
//        }
//
//        return array(
//            'entity' => $siteParam,
//            'title' => $title,
//            'form' => $form->createView(),
//            'authServerNetworkId' => $authServerNetworkId,
//        );
//    }
//
//    #[Route(path: '/tenancy-management-update', name: 'employees_tenancy_management_update', methods: ['GET', 'POST'])]
//    #[Template('AppSystemBundle/tenancy-management.html.twig')]
//    public function updateTenancyManagementAction( Request $request, KernelInterface $kernel )
//    {
//        $tenantRole = $this->getParameter('tenant_role');
//        if( $tenantRole != 'tenantmanager' ) {
//            $this->addFlash(
//                'warning',
//                "Tenancy settings is accessible only from the tenant manager system."
//            );
//            return $this->redirect( $this->generateUrl('employees-nopermission') );
//        }
//
//        //ROLE_PLATFORM_DEPUTY_ADMIN or ROLE_SUPER_DEPUTY_ADMIN
//        if( false === $this->isGranted('ROLE_PLATFORM_DEPUTY_ADMIN') ) {
//            $this->addFlash(
//                'warning',
//                "Tenancy settings is accessible only by ROLE_SUPER_DEPUTY_ADMIN."
//            );
//            return $this->redirect( $this->generateUrl('employees-nopermission') );
//        }
//
//        $userServiceUtil = $this->container->get('user_service_utility');
//
//        $em = $this->getDoctrine()->getManager();
//        $authServerNetwork = $em->getRepository(AuthServerNetworkList::class)->findOneByName('Internet (Hub)');
//        $authServerNetworkId = null;
//        if( $authServerNetwork ) {
//            $authServerNetworkId = $authServerNetwork->getId();
//        }
//
//        //Create DB if not exists
//        $output = null;
//        //https://carlos-compains.medium.com/multi-database-doctrine-symfony-based-project-0c1e175b64bf
//        $output = $userServiceUtil->checkAndCreateNewDBs($request,$authServerNetwork,$kernel);
//        $this->addFlash(
//            'notice',
//            "New DBs verified and created if not existed.<br> Output:<br>".$output
//        );
//
//        //runDeployScript
//        if(1) {
//            //$userServiceUtil->runDeployScript(false,false,true);
//            $output = $userServiceUtil->clearCacheInstallAssets($kernel);
//            $this->addFlash(
//                'notice',
//                "Container rebuilded, cache cleared, assets dumped. Output=" . $output
//            );
//        }
//
//        return $this->redirect($this->generateUrl('employees_tenancy_management'));
//    }

}
