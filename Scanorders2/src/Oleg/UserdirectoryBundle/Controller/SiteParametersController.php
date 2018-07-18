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

namespace Oleg\UserdirectoryBundle\Controller;

use Doctrine\Common\Collections\ArrayCollection;
use Oleg\UserdirectoryBundle\Entity\OrganizationalGroupDefault;
use Oleg\UserdirectoryBundle\Form\InitialConfigurationType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Oleg\UserdirectoryBundle\Entity\SiteParameters;
use Oleg\UserdirectoryBundle\Form\SiteParametersType;
use Oleg\UserdirectoryBundle\Util\UserUtil;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * SiteParameters controller.
 *
 * @Route("/settings")
 */
class SiteParametersController extends Controller
{

    //@Route("/{id}", name="employees_siteparameters_id")
    //, $id=null
    /**
     * Lists all SiteParameters entities.
     *
     * @Route("/", name="employees_siteparameters")
     * @Method("GET")
     * @Template("OlegUserdirectoryBundle:SiteParameters:index.html.twig")
     */
    public function indexAction(Request $request)
    {
        if( false === $this->get('security.authorization_checker')->isGranted('ROLE_PLATFORM_DEPUTY_ADMIN') ) {
            return $this->redirect( $this->generateUrl('employees-nopermission') );
        }

        //testing
//        $userServiceUtil = $this->container->get('user_service_utility');
//        if( $userServiceUtil->isWinOs() ) {
//            echo "Windows <br>";
//        } else {
//            echo "Not Windows <br>";
//        }

        return $this->indexParameters($request);
    }

    public function indexParameters($request) {

//        if( false === $this->get('security.authorization_checker')->isGranted('ROLE_PLATFORM_DEPUTY_ADMIN') ) {
//            return $this->redirect( $this->generateUrl('employees-nopermission') );
//        }

        //testing email
        //$emailUtil = $this->container->get('user_mailer_utility');
        //$emailUtil->sendEmail( "oli2002@med.cornell.edu", "testing email", "testing email" );

        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('OlegUserdirectoryBundle:SiteParameters')->findAll();

//        //make sure sitesettings is initialized
//        if( count($entities) != 1 ) {
//            $userServiceUtil = $this->get('user_service_utility');
//            $userServiceUtil->generateSiteParameters();
//            $entities = $em->getRepository('OlegUserdirectoryBundle:SiteParameters')->findAll();
//        }

        if( count($entities) != 1 ) {
            throw new \Exception( 'Must have only one parameter object. Found '.count($entities).' object(s)' );
        }

        $entity = $entities[0];

        $routeName = $request->get('_route');
        $routeArr = explode("_", $routeName);
        $sitename = $routeArr[0];

//        if( $sitename == "translationalresearch" ) {
//            //exception for transres site
//            if( false === $this->get('security.authorization_checker')->isGranted('ROLE_TRANSRES_ADMIN') ) {
//                return $this->redirect( $this->generateUrl('employees-nopermission') );
//            }
//        } else {
//            if( false === $this->get('security.authorization_checker')->isGranted('ROLE_PLATFORM_DEPUTY_ADMIN') ) {
//                return $this->redirect( $this->generateUrl('employees-nopermission') );
//            }
//        }

        $disabled = true;

        $passw = "*******";
        if( $entity->getPacsvendorSlideManagerDBPassword() != '' )
            $entity->setPacsvendorSlideManagerDBPassword($passw);

        if( $entity->getLisDBAccountPassword() != '' )
            $entity->setLisDBAccountPassword($passw);

        if( $entity->getADLDAPServerAccountPassword() != '' )
            $entity->setADLDAPServerAccountPassword($passw);

        if( $entity->getDbServerAccountPassword() != '' )
            $entity->setDbServerAccountPassword($passw);

        if( $entity->getMailerPassword() != '' )
            $entity->setMailerPassword($passw);

        if( $entity->getCaptchaSiteKey() != '' )
            $entity->setCaptchaSiteKey($passw);

        if( $entity->getCaptchaSecretKey() != '' )
            $entity->setCaptchaSecretKey($passw);

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

        $link = realpath($_SERVER['DOCUMENT_ROOT']).DIRECTORY_SEPARATOR.'scanorder'.DIRECTORY_SEPARATOR.'Scanorders2'.
            DIRECTORY_SEPARATOR.'app'.DIRECTORY_SEPARATOR.'config'.DIRECTORY_SEPARATOR.'parameters.yml';
        //echo "link=".$link."<br>";

        //get absolute path prefix for Upload folder
        $rootDir = $this->container->get('kernel')->getRootDir(); //C:\Users\ch3\Documents\MyDocs\WCMC\ORDER\scanorder\Scanorders2\app
        $rootDir = str_replace('app','',$rootDir);
        $uploadPath = $rootDir . 'web'.DIRECTORY_SEPARATOR;

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
     *
     * @Route("/{id}/edit", name="employees_siteparameters_edit")
     * @Method("GET")
     * @Template("OlegUserdirectoryBundle:SiteParameters:edit.html.twig")
     */
    public function editAction(Request $request,$id)
    {
        return $this->editParameters($request,$id);
    }

    public function editParameters( Request $request, $id, $role=null )
    {
        $param = trim( $request->get('param') );

        $routeName = $request->get('_route');
        $routeArr = explode("_", $routeName);
        $sitename = $routeArr[0];
        //exit('role='.$role."; sitename=".$sitename);

        if( $role ) {
            if( false === $this->get('security.authorization_checker')->isGranted($role) ) {
                return $this->redirect( $this->generateUrl($sitename.'-nopermission') );
            }
        } else {
            if( false === $this->get('security.authorization_checker')->isGranted('ROLE_PLATFORM_DEPUTY_ADMIN') ) {
                return $this->redirect( $this->generateUrl($sitename.'-nopermission') );
            }
        }

        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('OlegUserdirectoryBundle:SiteParameters')->find($id);

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
            'sitename' => $sitename
            //'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Edits an existing SiteParameters entity.
     *
     * @Route("/{id}", name="employees_siteparameters_update")
     * @Method("PUT")
     * @Template("OlegUserdirectoryBundle:SiteParameters:edit.html.twig")
     */
    public function updateAction(Request $request, $id)
    {
        return $this->updateParameters($request, $id);
    }

    public function updateParameters(Request $request, $id, $role=null)
    {

        $param = trim( $request->get('param') );
        //echo "param=".$param."<br>";

        $routeName = $request->get('_route');
        $routeArr = explode("_", $routeName);
        $sitename = $routeArr[0];

        if( $role ) {
            if( false === $this->get('security.authorization_checker')->isGranted($role) ) {
                return $this->redirect( $this->generateUrl($sitename.'-nopermission') );
            }
        } else {
            if( false === $this->get('security.authorization_checker')->isGranted('ROLE_PLATFORM_DEPUTY_ADMIN') ) {
                return $this->redirect( $this->generateUrl($sitename.'-nopermission') );
            }
        }

        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('OlegUserdirectoryBundle:SiteParameters')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find SiteParameters entity.');
        }

        //get method
        $getMethod = "get".$param;
        $originalParam = $entity->$getMethod();

        $editForm = $this->createEditForm($sitename,$entity,$param,false);

        $editForm->handleRequest($request);

        if( $editForm->isValid() ) {
            $em->flush();

            $redirectPathPostfix = '_siteparameters';

            //Exception: edit "calllogResources" param can be invoked from a specific controller "calllog_siteparameters_resources_edit" by non-admin user =>
            //redirect to another page, in this case 'calllog_resources' instead of the _siteparameters page which is accessible only by admin.
            if( $param == 'calllogResources' ) {
                $redirectPathPostfix = '_resources';
            }

            if( $param == 'mailerSpool' || $param == 'mailerFlushQueueFrequency' ) {
                $emailUtil = $this->get('user_mailer_utility');
                $emailUtil->createEmailCronJob();
            }

            //add a new eventlog record for an updated parameter
            $user = $this->get('security.token_storage')->getToken()->getUser();
            $userSecUtil = $this->get('user_security_utility');
            $eventType = "Site Settings Parameter Updated";
            $eventStr = "Site Settings parameter [$param] has been updated by ".$user;
            $eventStr = $eventStr . "<br>original value:<br>".$originalParam;
            $eventStr = $eventStr . "<br>updated value:<br>".$entity->$getMethod();
            $userSecUtil->createUserEditEvent($sitename, $eventStr, $user, $entity, $request, $eventType);

            return $this->redirect($this->generateUrl($sitename.$redirectPathPostfix)); //'_siteparameters'
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
     *
     * @Route("/organizational-group-default-management/{id}", name="employees_management_organizationalgroupdefault")
     * @Method({"GET","POST"})
     * @Template("OlegUserdirectoryBundle:SiteParameters:group-management-form.html.twig")
     */
    public function manageOrgGroupDefaultAction(Request $request, $id)
    {

        if( false === $this->get('security.authorization_checker')->isGranted('ROLE_PLATFORM_DEPUTY_ADMIN') ) {
            return $this->redirect( $this->generateUrl('employees-nopermission') );
        }

        $em = $this->getDoctrine()->getManager();

        $routeName = $request->get('_route');
        $routeArr = explode("_", $routeName);
        $sitename = $routeArr[0];

        $entity = $em->getRepository('OlegUserdirectoryBundle:SiteParameters')->find($id);

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

            $this->get('session')->getFlashBag()->add(
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
        $roles = $em->getRepository('OlegUserdirectoryBundle:Roles')->findBy(
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
     * Initial Configuration Completed
     *
     * @Route("/initial-configuration", name="employees_initial_configuration")
     * @Method({"GET","POST"})
     * @Template("OlegUserdirectoryBundle:SiteParameters:initial-configuration.html.twig")
     */
    public function initialConfigurationAction(Request $request)
    {
        //exit('EXIT: initialConfigurationAction');
        if( false === $this->get('security.authorization_checker')->isGranted('ROLE_PLATFORM_DEPUTY_ADMIN') ) {
            return $this->redirect( $this->generateUrl('employees-nopermission') );
        }

        $em = $this->getDoctrine()->getManager();
        $encoder = $this->container->get('security.password_encoder');
        //$userSecUtil = $this->container->get('user_security_utility');
        //$routeName = $request->get('_route');

        $administratorUser = $this->get('security.token_storage')->getToken()->getUser();
        if( $administratorUser->getPrimaryPublicUserId() != "Administrator" ) {
            $administratorUser = $em->getRepository('OlegUserdirectoryBundle:User')->findOneByPrimaryPublicUserId("Administrator");
            if( !$administratorUser ) {
                throw new \Exception('Initial Configuration: Administrator user not found.');
            }
        }

        $entities = $em->getRepository('OlegUserdirectoryBundle:SiteParameters')->findAll();

        if( count($entities) != 1 ) {
            $userServiceUtil = $this->get('user_service_utility');
            $userServiceUtil->generateSiteParameters();
            $entities = $em->getRepository('OlegUserdirectoryBundle:SiteParameters')->findAll();
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
                $encoded = $encoder->encodePassword($administratorUser, $password);
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
                $em->flush($administratorUser);
            }

            $em->persist($entity);
            $em->flush($entity);

            $emailUtil = $this->get('user_mailer_utility');
            $emailUtil->createEmailCronJob();

            //exit("form is valid");

            //url: user_update_system_cache_assets
            $urlUpdateCacheAssets = $this->container->get('router')->generate(
                'user_update_system_cache_assets',
                null,
                UrlGeneratorInterface::ABSOLUTE_URL
            );

            $this->get('session')->getFlashBag()->add(
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

}
