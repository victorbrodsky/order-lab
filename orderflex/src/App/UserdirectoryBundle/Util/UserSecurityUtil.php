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
 * Created by JetBrains PhpStorm.
 * User: oli2002
 * Date: 10/4/13
 * Time: 12:56 PM
 * To change this template use File | Settings | File Templates.
 */

namespace App\UserdirectoryBundle\Util;



use App\OrderformBundle\Entity\AccessionType;
use App\UserdirectoryBundle\Entity\Document; //process.py script: replaced namespace by ::class: added use line for classname=Document


use App\UserdirectoryBundle\Entity\Institution; //process.py script: replaced namespace by ::class: added use line for classname=Institution


use App\UserdirectoryBundle\Entity\AccessRequest; //process.py script: replaced namespace by ::class: added use line for classname=AccessRequest


use App\UserdirectoryBundle\Entity\UsernameType; //process.py script: replaced namespace by ::class: added use line for classname=UsernameType


use App\UserdirectoryBundle\Entity\InstitutionType; //process.py script: replaced namespace by ::class: added use line for classname=InstitutionType


use App\UserdirectoryBundle\Entity\OrganizationalGroupType; //process.py script: replaced namespace by ::class: added use line for classname=OrganizationalGroupType


use App\UserdirectoryBundle\Entity\SourceSystemList; //process.py script: replaced namespace by ::class: added use line for classname=SourceSystemList


use App\UserdirectoryBundle\Entity\EventTypeList; //process.py script: replaced namespace by ::class: added use line for classname=EventTypeList


use App\UserdirectoryBundle\Entity\PermissionList; //process.py script: replaced namespace by ::class: added use line for classname=PermissionList


use App\UserdirectoryBundle\Entity\PermissionObjectList; //process.py script: replaced namespace by ::class: added use line for classname=PermissionObjectList


use App\UserdirectoryBundle\Entity\PermissionActionList; //process.py script: replaced namespace by ::class: added use line for classname=PermissionActionList


use App\OrderformBundle\Entity\AccessionAccession; //process.py script: replaced namespace by ::class: added use line for classname=AccessionAccession


use App\UserdirectoryBundle\Entity\Roles;
use App\UserdirectoryBundle\Entity\SiteList;
use App\UserdirectoryBundle\Entity\SiteParameters;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use OneLogin\Saml2\Auth;
use Symfony\Component\DependencyInjection\ContainerInterface;
//use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
//use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

use App\UserdirectoryBundle\Entity\Permission;
use App\UserdirectoryBundle\Entity\PerSiteSettings;
use App\UserdirectoryBundle\Form\DataTransformer\GenericTreeTransformer;
//use Symfony\Component\Form\Extension\Core\DataTransformer\DateTimeToStringTransformer;
use Symfony\Component\HttpFoundation\RedirectResponse;

use App\UserdirectoryBundle\Entity\User;
use App\UserdirectoryBundle\Util\UserUtil;
use App\UserdirectoryBundle\Entity\Logger;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

use Sinergi\BrowserDetector\Browser;
use Sinergi\BrowserDetector\Os;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\RequestStack;


//Note: This class might be called from repository and transformer with only the first argument in constractor $em
//Methods used when this class is created by "new UserSecurityUtil": getDefaultSourceSystem findSystemUser addDefaultType

class UserSecurityUtil {

    protected $em;
    protected $container;
    protected $security;
    protected $tokenStorage;
    protected $requestStack;

    protected $siteSettingsParam = null;
    //protected $initCountTest = 0;

    public function __construct(
        EntityManagerInterface $em,
        ContainerInterface $container=null,
        Security $security=null,
        TokenStorageInterface $tokenStorage=null,
        RequestStack $requestStack=null
    ) {
        $this->em = $em;
        $this->container = $container;
        $this->security = $security;
        $this->tokenStorage = $tokenStorage;
        $this->requestStack = $requestStack;
        //if( $container ) {
            //$this->secToken = $container->get('security.token_storage');
            //$this->secAuth = $container->get('security.authorization_checker');
        //}

        //$this->siteSettingsParam = $this->getSingleSiteSettingsParam();
    }

    //TODO: optimize by using AppUserdirectoryBundle:SiteParameters as a service to query from DB only once
    //Deprecated, use $userServiceUtil->getSingleSiteSettingParameter()
//    public function getSingleSiteSettingsParam() {
//        if( $this->siteSettingsParam === null ) {
//
//            //$this->initCountTest++;
//            //echo "initCountTest=".$this->initCountTest."<br>";
//
//            //$params = $this->em->getRepository('AppUserdirectoryBundle:SiteParameters')->findAll();
//            //$dbName = $this->em->getConnection()->getDatabase();
//            $logger = $this->container->get('logger');
//            //$logger->notice("getSingleSiteSettingsParam: dbName=[$dbName]");
//
//            //doctrine Deprecate short namespace aliases
//            $params = $this->em->getRepository(SiteParameters::class)->findAll();
//            //echo "params count=".count($params)."<br>";
//
//            if (count($params) == 0) {
//                //return -1;
//                return null;
//                //return "[Site Settings is not initialized]";
//            }
//
//            if (count($params) > 1) {
//                $logger = $this->container->get('logger');
//                $msg = 'getSingleSiteSettingsParam: Must have only one parameter object. Found ' .
//                    count($params) .
//                    ' object(s). Please follow the initialization instructions.';
//                $logger->error($msg);
//                exit($msg);
//                //throw new \Exception( 'Must have only one parameter object. Found '.count($params).' object(s)' );
//            }
//
//            //$param = $params[0];
//            $this->siteSettingsParam = $params[0];
//        }
//
//        if( $this->siteSettingsParam === null ) {
//            return null;
//        }
//
//        return $this->siteSettingsParam;
//    }

    public function isCurrentUser( $id ) {

        if( !$id ) {
            return false;
        }

        if( !$this->security ) {
            return false;
        }

        $user = $this->security->getUser();

        $entity = $this->em->getRepository(User::class)->find($id);

        if( $entity && $entity->getId() === $user->getId() ) {
            return true;
        }

        return false;
    }

    //check for user preferences:
    //hide - Hide this profile
    //showToInstitutions - Only show this profile to members of the following institution(s)
    //showToRoles - Only show this profile to users with the following roles
    public function isUserVisible( $subjectUser, $currentUser ) {

        //always visible to Platform Administrator and Deputy Platform Administrator
        if( $this->security->isGranted('ROLE_PLATFORM_DEPUTY_ADMIN') ) {
            return true;
        }

        //always visible to current user
        if( $currentUser->getId() == $subjectUser->getId() ) {
            return true;
        }

        $preferences = $subjectUser->getPreferences();

        //hide - Hide this profile
        $hide = false;
        //If checked, profile View page should only show this profile to the user "owner" of the profile
        //and to users with Platform Administrator and Deputy Platform Administrator roles
        if( $preferences->getHide() ) {
            $hide = true;
        }

        //hide overwrite the two other checks below
        if( $hide ) {
            return false; //not visible
        }

        //showToInstitutions: false - if empty or check institutions if not empty
        $hideInstitution = false;
        $showToInstitutions = $preferences->getShowToInstitutions();
        //echo "showToInstitutions count=".count($showToInstitutions)."<br>";
        if( count($showToInstitutions) > 0 ) {
            $hideInstitution = true;

            //check if $currentUser has one of the verified Institutions
            $type = null; //all types: AdministrativeTitle, AppointmentTitle, MedicalTitle
            $status = 1;  //1-verified
            $currentUserInstitutions = $currentUser->getInstitutions($type,$status);
            //echo "currentUserInstitutions count=".count($currentUserInstitutions)."<br>";
            foreach( $currentUserInstitutions as $currentUserInstitution ) {
                //echo "currentUserInstitution=".$currentUserInstitution."<br>";
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:Institution'] by [Institution::class]
                if( $this->em->getRepository(Institution::class)->isNodeUnderParentnodes($showToInstitutions, $currentUserInstitution) ) {
                    $hideInstitution = false;
                    break;
                }
            }

            //check if $currentUser has one of the Institutional PHI Scope
            $securityUtil = $this->container->get('user_security_utility');
            //$userSiteSettings = $securityUtil->getUserPerSiteSettings($subjectUser);
            $userSiteSettings = $securityUtil->getUserPerSiteSettings($currentUser);
            $currentUserPermittedInstitutions = $userSiteSettings->getPermittedInstitutionalPHIScope();
            //echo "currentUserPermittedInstitutions count=".count($currentUserPermittedInstitutions)."<br>";
            foreach( $currentUserPermittedInstitutions as $currentUserPermittedInstitution ) {
                //echo "currentUserPermittedInstitution=".$currentUserPermittedInstitution."<br>";
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:Institution'] by [Institution::class]
                if( $this->em->getRepository(Institution::class)->isNodeUnderParentnodes($showToInstitutions, $currentUserPermittedInstitution) ) {
                    $hideInstitution = false;
                    break;
                }
            }

//            foreach( $showToInstitutions as $showToInstitution ) {
//                //echo "verified inst count=".count($currentUserInst)."<br>";
//                //echo "showToInstitution=".$showToInstitution."<br>";
//                if( $currentUserInstitutions->contains($showToInstitution) ) {
//                    $hideInstitution = false;
//                    break;
//                }
//            }
        }


        //showToRoles
        $hideRole = false;
        $showToRoles = $preferences->getShowToRoles();
        if( $showToRoles && count($showToRoles) > 0 ) {
            $hideRole = true;
            //check if current user has one of the role
            foreach( $showToRoles as $role ) {
                //echo "role=".$role."<br>";
                if( $this->security->isGranted($role."") ) {
                    $hideRole = false;
                    break;
                }
            }
        }

        //echo "hideInstitution=".$hideInstitution."<br>";
        //echo "hideRole=".$hideRole."<br>";
        //exit();

        if( $hide ) {
            //exit('hide');
            //return false;
        }
        if( $hideInstitution ) {
            //exit('hideInstitution is true => not visible');
            //return false;
        } else {
            //exit('hideInstitution is false => is visble');
        }
        if( $hideRole ) {
            //exit('hideRole');
            //return false;
        }

        if( $hide || $hideInstitution || $hideRole ) {
            //exit('???');
            return false; //not visible
        } else {
            return true; //visible
        }
    }


    //used by login success handler to get user has access request
    public function getUserAccessRequest($user,$sitename) {
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:AccessRequest'] by [AccessRequest::class]
        $accessRequest = $this->em->getRepository(AccessRequest::class)->findOneBy(
            array('user' => $user, 'siteName' => $sitename)
        );

        //echo "accessRequest=".$accessRequest."<br>";
        //exit('111');

        return $accessRequest;
    }

    public function getUserAccessRequestsByStatus($sitename, $status) {
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:AccessRequest'] by [AccessRequest::class]
        $accessRequests = $this->em->getRepository(AccessRequest::class)->findBy(
            array('siteName' => $sitename, 'status' => $status)
        );

        return $accessRequests;
    }


    //check for the role in security context and in the user DB
    public function hasGlobalUserRole( $role, $user=null ) {

        if( false === $this->security->isGranted('IS_AUTHENTICATED_FULLY') ) {
            return false;
        }

        if( $this->security->isGranted($role) ) {
            return true;
        }

        //get user from DB?

        if( $user == null ) {
            if( $this->security ) {
                $user = $this->security->getUser();
            }
        }

//        if( $this->security->isGranted('PUBLIC_ACCESS') )
//            return false;

        if( !is_object($user) ) {
            //echo "user is not object: return false <br>";
            //exit();
            return false;
        } else {
            //echo "user is object <br>";
        }
        //exit();

        if( $user && $user->hasRole($role) ) {
            return true;
        }

        //echo "no role=".$role." => return false <br>";
        //exit();

        return false;
    }


    function idleLogout( $request, $sitename, $flag = null ) {

        //$userUtil = new UserUtil();
        //$res = $userUtil->getMaxIdleTimeAndMaintenance($this->em,$this->security,$this->container);

        $res = $this->getMaxIdleTimeAndMaintenance();
        $maxIdleTime = $res['maxIdleTime'];
        $maintenance = $res['maintenance'];

        //In order to keep session onLogout, set firewall logout: invalidate_session: false then $session->invalidate();
        $samlLogoutStr = "";
        $session = $request->getSession();
        $logintype = $session->get('logintype');
        $logger = $this->container->get('logger');
        $logger->notice("idleLogout: logintype=".$logintype);
        if( $logintype === 'saml-sso' ) {
            $samlLogoutStr = "(with SAML logout)";
        }

        if( $maintenance ) {

            //$msg = $userUtil->getSiteSetting($this->em,'MaintenancelogoutmsgWithDate');
            //$userSecUtil = $this->container->get('user_security_utility');
            //$msg = $userSecUtil->getSiteSettingParameter('MaintenancelogoutmsgWithDate');
            $msg = $this->getSiteSettingParameter('MaintenancelogoutmsgWithDate');

        } else {

            if( $flag && $flag == 'saveorder' ) {
                $msg = 'You have been logged out '.$samlLogoutStr.' after '.($maxIdleTime/60).' minutes of inactivity. You can find the order you have been working on in the list of your orders once you log back in.';
            } else {
                $msg = 'You have been logged out '.$samlLogoutStr.' after '.($maxIdleTime/60).' minutes of inactivity.';
            }

        }

        $user = $this->security->getUser();
        $eventType = "User Auto Logged Out";
        $eventStr = "User has been auto logged out with message: ".$msg;

        //EventLog
        $this->createUserEditEvent(
            $this->container->getParameter('employees.sitename'),   //$sitename
            $eventStr,                                              //$event (Event description)
            $user,                                                  //$user
            $user,                                                  //$subjectEntities
            $request,                                               //$request
            $eventType                                              //$action (Event Type)
        );

//        $this->container->get('session')->getFlashBag()->add(
//            'notice',
//            $msg
//        );
        $request->getSession()->getFlashBag()->add(
            'notice',
            $msg
        );
        //return new RedirectResponse( $this->container->get('router')->generate($sitename.'_home') );//testing
        //exit($msg);

        //$this->container->get('security.token_storage')->setToken(null);
        //$this->security->setToken(null); //testing
        //$this->get('request')->getSession()->invalidate();
        //$request->getSession()->invalidate();

        $logger->notice("idleLogout: before security->logout");
        //$this->tokenStorage->setToken(null);
        //$this->security->logout();
        $this->security->logout(false); //This will trigger onLogout event

        //invalidate_session manually
        //$this->security->setToken(null);
        //$session->invalidate();
        //$this->security->logout(false);

        //samlLogout will redirect by $auth->logout(); to $sitename homepage
        $this->samlLogout($user,$logintype,$sitename,false);

        //return $this->redirect($this->generateUrl($sitename.'_login'));
        return new RedirectResponse( $this->container->get('router')->generate($sitename.'_login') );
        //return new RedirectResponse( $this->container->get('router')->generate($sitename.'_logout') );
    }

    function userLogout( $request, $sitename ) {
        //$this->container->get('security.token_storage')->setToken(null);
        //$this->security->setToken(null); //testing
        //$this->get('request')->getSession()->invalidate();
        //$request->getSession()->invalidate();

        $this->tokenStorage->setToken(null);

        //return $this->redirect($this->generateUrl($sitename.'_login'));
        return new RedirectResponse( $this->container->get('router')->generate($sitename.'_login') );
        //return new RedirectResponse( $this->container->get('router')->generate($sitename.'_logout') );
    }
    function userLogoutSymfony7( $stay = false ) {

//        $user = $this->security->getUser();
//        if( $user ) {
//            //exit('User exists='.$user->getId());
//            $this->tokenStorage->setToken(null);
//        }
        //exit('User does not exist');

        // logout the user in on the current firewall
        $response = $this->security->logout();

        // you can also disable the csrf logout
        //$response = $this->security->logout(false);

        if( $stay == false ) {
            return $response;
        }

        return true;

        //$this->container->get('security.token_storage')->setToken(null);
        //$this->security->setToken(null); //testing
        //$this->get('request')->getSession()->invalidate();
        //$request->getSession()->invalidate();

        //$this->tokenStorage->setToken(null);

        //return $this->redirect($this->generateUrl($sitename.'_login'));
        //return new RedirectResponse( $this->container->get('router')->generate($sitename.'_login') );
        //return new RedirectResponse( $this->container->get('router')->generate($sitename.'_logout') );
    }
    public function samlLogout( $user, $logintype=NULL, $sitename=NULL, $forceLogout=false ) {
        //return false; //testing - disable.

        if( !$user ) {
            return false;
        }

        $logger = $this->container->get('logger');

        //check $session = $request->getSession();
        $session = $this->requestStack->getCurrentRequest()->getSession();
        if( !$logintype ) {
            $logintype = $session->get('logintype');
            $logger->notice("samlLogout: logintype=".$logintype);
        }

        if( $logintype != 'saml-sso' ) {
            $logger->notice("samlLogout: NO SAML logout");
            return false;
        }
        $logger->notice("samlLogout: SAML logout");

        $returnUrl = $this->container->get('router')->generate(
            $sitename.'_login',
            array(),
            UrlGeneratorInterface::ABSOLUTE_URL
        );

        //$returnUrl = str_replace("http","https",$returnUrl);

        $samlConfigProviderUtil = $this->container->get('saml_config_provider_util');
        $email = $user->getSingleEmail();
        $logger->notice("samlLogout: Starting SAML logout: email=".$email);
        if( $email ) {
            $config = $samlConfigProviderUtil->getConfig($email);
            if( $config) {
                try {
                    //$logger->notice("samlLogout: Starting SAML logout: try");
                    $auth = new Auth($config['settings']);
                    //if( $auth->isAuthenticated() ) {

                    if( $forceLogout ) {
                        //$this->tokenStorage->setToken(null);
                        //$this->security->logout();
                        //$this->security->logout(false); //This will trigger onLogout event

                        //invalidate_session manually
                        //$this->security->setToken(null);
                        //$session->invalidate();
                    }

                    $logger->notice("samlLogout: returnUrl={$returnUrl}");
                    $auth->logout($returnUrl);
                    //$auth->logout(); //testing
                    $logger->notice("samlLogout: Starting SAML logout: after logout");
                    // The logout method does a redirect, so we won't reach this line
                    //return new Response('Redirecting to IdP for logout...', 302);
                    return true;
                } catch (Error $e) {
                    //$this->logger->critical(sprintf('Unable to logout client with message: "%s"', $e->getMessage()));
                    throw new UnprocessableEntityHttpException('Error while trying to logout');
                }
            }
        }
        //$logger->debug("samlLogout: End of SAML logout");
        return false;
    }
    
    function getLoggedInUsers($request) {
        //to list logged in users we can search for users with following:
        //delay = now - maxIdleTime
        //if( lastActive > (delay) ) => user is active

        $res = $this->getMaxIdleTimeAndMaintenance();
        $maxIdleTime = $res['maxIdleTime']; //sec
        //$maintenance = $res['maintenance'];
        //echo "maxIdleTime=".$maxIdleTime."<br>";

        //$delay = new \DateTime() - $maxIdleTime;
        //$delay = time() - $maxIdleTime;
        $delay = new \DateTime();
        $delay->modify("-".$maxIdleTime." second");

        $repository = $this->em->getRepository(User::class);
        $dql =  $repository->createQueryBuilder("user");
        $dql->select('user');
        $dql->leftJoin('user.keytype','keytype');
        $dql->leftJoin('user.infos','infos');

        //use lastActivity > delay (now - $maxIdleTime)
        $dql->where("user.lastActivity > :delay");

        //and logout > lastActivity
        //This might provide inaccurate result: if user logged in in two different browser
        // and then logout in only one of them, the second browser will still have user logged in,
        // but the page will show that user is logged out
        //The most accurate way is set lastActivity on every user request, but it might be heavy,
        // because it will query and modify DB on each request
        //$dql->where("list.lastActivity > :delay");

        //$query = $this->em->createQuery($dql);
        //$query->setParameters(array('delay'=>$delay));
        //$users = $query->getResult();

        $limit = 30;
        $query = $dql->getQuery(); //$query = $this->em->createQuery($dql);
        $query->setParameters(array('delay'=>$delay));
        $paginator  = $this->container->get('knp_paginator');
        $pagination = $paginator->paginate(
            $query,
            $request->query->get('page', 1), /*page number*/
            $limit,     /*limit per page*/
            array(
                'defaultSortFieldName' => 'user.lastActivity',
                'defaultSortDirection' => 'DESC',
                'wrap-queries'=>true
            )
        );

        return $pagination;
    }

    function getLoggedInUserEntities() {
        //to list logged in users we can search for users with following:
        //delay = now - maxIdleTime
        //if( lastActive > (delay) ) => user is active

        $res = $this->getMaxIdleTimeAndMaintenance();
        $maxIdleTime = $res['maxIdleTime']; //sec
        //$maintenance = $res['maintenance'];
        //echo "maxIdleTime=".$maxIdleTime."<br>";

        //$delay = new \DateTime() - $maxIdleTime;
        //$delay = time() - $maxIdleTime;
        $delay = new \DateTime();
        $delay->modify("-".$maxIdleTime." second");

        $repository = $this->em->getRepository(User::class);
        $dql =  $repository->createQueryBuilder("user");
        $dql->select('user');
        //$dql->leftJoin('user.keytype','keytype');
        //$dql->leftJoin('user.infos','infos');

        //use lastActivity > delay (now - $maxIdleTime)
        $dql->where("user.lastActivity > :delay");

        //and logout > lastActivity
        //This might provide inaccurate result: if user logged in in two different browser
        // and then logout in only one of them, the second browser will still have user logged in,
        // but the page will show that user is logged out
        //The most accurate way is set lastActivity on every user request, but it might be heavy,
        // because it will query and modify DB on each request
        //$dql->where("list.lastActivity > :delay");

        $query = $dql->getQuery();
        $query->setParameters(array('delay'=>$delay));
        $users = $query->getResult();

        //dump($users);
        //exit('111');

        return $users;
    }

    function constructEventLog( $sitename, $user, $request ) {

        //get abbreviation from sitename:
        $siteObject = $this->getSiteBySitename($sitename,true);

        $logger = new Logger($siteObject);

        if( $user ) {
            $logger->setUser($user);
            $logger->setRoles($user->getRoles());
            $logger->setUsername($user . "");
        }

        if( $request ) {

            //IP is always 127.0.0.1: this is caused by a proxy server because the requests are redirected from a localhost.
            //$logger2 = $this->container->get('logger');
            //$clientIp = $this->container->get('request_stack')->getCurrentRequest()->getClientIp();
            //echo "clientIp=".$clientIp."<br>";
            //$logger2->notice("clientIp=".$clientIp);
            //$clientIp = $this->container->get('request_stack')->getMasterRequest()->getClientIp();
            //echo "clientIp=".$clientIp."<br>";
            //$logger2->notice("clientIp=".$clientIp);
            //$clientIp = $this->container->get('request')->server->get("REMOTE_ADDR");
            //echo "clientIp=".$clientIp."<br>";
            //$logger2->notice("clientIp=".$clientIp);
            //$clientIp = $request->getClientIp();
            //echo "clientIp=".$clientIp."<br>";
            //$logger2->notice("clientIp=".$clientIp);
            //exit('1');

            if( isset($_SERVER['HTTP_USER_AGENT']) ) {
                $logger->setUseragent($_SERVER['HTTP_USER_AGENT']);
            }
            $logger->setIp($request->getClientIp());
            $logger->setWidth($request->get('display_width'));
            $logger->setHeight($request->get('display_height'));
        }

        return $logger;
    }

    //get abbreviation from sitename:
    // fellowship-applications => fellapp
    // fellapp => fellapp
    public function getSiteBySitename( $sitename, $asObject=true ) {
        if( !$sitename ) {
            return null;
        }
        $repository = $this->em->getRepository(SiteList::class);
        $dql =  $repository->createQueryBuilder("list");
        $dql->select('list');
        $dql->where("list.name = :sitename OR list.abbreviation = :sitename");
        $query = $dql->getQuery(); //$query = $this->em->createQuery($dql);

        $query->setParameters(array('sitename'=>$sitename));

        $sitenameObject = $query->getSingleResult();

        if( $asObject ) {
            if( !$sitenameObject ) {
                throw $this->createNotFoundException('Unable to find SiteList entity by site name string='.$sitename);
            }
            return $sitenameObject;
        }

        if( !$sitenameObject ) {
            return $sitename;
        }

        return $sitenameObject->getAbbreviation();
    }

//    public function getDefaultUserKeytypeSafe() {
//        $userUtil = new UserUtil();
//        $userkeytype = $userUtil->getDefaultUsernameType($this->em);
//        if( $userkeytype == null ) {
//            //generate user keytypes
//            $userUtil->generateUsernameTypes($this->em,null);
//            $userkeytype = $userUtil->getDefaultUsernameType($this->em);
//        }
//        return $userkeytype;
//    }
//    public function getDefaultUsernameType() {
//        $userUtil = new UserUtil();
//        $userkeytype = $userUtil->getDefaultUsernameType($this->em);
//        return $userkeytype;
//    }
    public function getDefaultUsernameType() {
        $userkeytype = null;
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:UsernameType'] by [UsernameType::class]
        $userkeytypes = $this->em->getRepository(UsernameType::class)->findBy(array(),array('orderinlist' => 'ASC'),1);   //limit result by 1
        //echo "userkeytypes=".$userkeytypes."<br>";
        //print_r($userkeytypes);
        if( $userkeytypes && count($userkeytypes) > 0 ) {
            $userkeytype = $userkeytypes[0];
        }
        return $userkeytype;
    }


    public function getUsernameType($abbreviation=null) {
        $userkeytype = null;
        if( $abbreviation ) {
            $userkeytype = $this->em->getRepository(UsernameType::class)->findOneBy(
                array(
                    'type' => array('default', 'user-added'),
                    'abbreviation' => array($abbreviation)
                ),
                array('orderinlist' => 'ASC')
            );

            return $userkeytype;
        } else {
            $userkeytypes = $this->em->getRepository(UsernameType::class)->findBy(
                array('type' => array('default', 'user-added')),
                array('orderinlist' => 'ASC')
            );

            //echo "userkeytypes=".$userkeytypes."<br>";
            //print_r($userkeytypes);
            if( $userkeytypes && count($userkeytypes) > 0 ) {
                $userkeytype = $userkeytypes[0];
                return $userkeytype;
            }
        }
        return null;
    }

    //Get CWID
    public function createCleanUsername($username) {
        //return User::createCleanUsername($username);
        $user = new User();
        return $user->createCleanUsername($username);
    }

    public function getUsernamePrefix($username) {
        //return User::getUsernamePrefix($username);
        $user = new User();
        return $user->getUsernamePrefix($username);
    }

    public function usernameIsValid($username) {
        //return User::usernameIsValid($username);
        $user = new User();
        return $user->usernameIsValid($username);
    }

    //array of emails for Admin users
    public function getUserEmailsByRole($sitename,$userRole,$roles=null) {

        if( $userRole === null ) {
            //use roles array
            return null;
        }
        else if( $userRole == "Platform Administrator" ) {

            $roles = array("ROLE_PLATFORM_ADMIN","ROLE_PLATFORM_DEPUTY_ADMIN");

        } else if( $userRole == "Administrator" ) {

            $roles = array("ROLE_PLATFORM_ADMIN","ROLE_PLATFORM_DEPUTY_ADMIN"); //default for admin

            if( $sitename == $this->container->getParameter('scan.sitename') ) {
                $roles = array("ROLE_SCANORDER_ADMIN");
            }

            if( $sitename == $this->container->getParameter('employees.sitename') ) {
                $roles = array("ROLE_USERDIRECTORY_ADMIN");
            }

            if( $sitename == $this->container->getParameter('fellapp.sitename') ) {
                $roles = array("ROLE_FELLAPP_COORDINATOR");
            }

            if( $sitename == $this->container->getParameter('deidentifier.sitename') ) {
                $roles = array("ROLE_DEIDENTIFICATOR_ADMIN");
            }

            if( $sitename == $this->container->getParameter('vacreq.sitename') ) {
                $roles = array("ROLE_VACREQ_ADMIN");
            }

            if( $sitename == $this->container->getParameter('calllog.sitename') ) {
                $roles = array("ROLE_CALLLOG_ADMIN");
            }

            if( $sitename == $this->container->getParameter('crn.sitename') ) {
                $roles = array("ROLE_CRN_ADMIN");
            }

            if( $sitename == $this->container->getParameter('translationalresearch.sitename') ) {
                $roles = array("ROLE_TRANSRES_ADMIN");
            }

            if( $sitename == $this->container->getParameter('dashboard.sitename') ) {
                $roles = array("ROLE_DASHBOARD_ADMIN");
            }

        } else {
            return null;
        }

        $users = $this->em->getRepository(User::class)->findUsersByRoles($roles); //supports partial role name

        //echo "user count=".count($users)."<br>";

        $emails = array();
        if( $users && count($users) > 0 ) {

            foreach( $users as $user ) {
                //echo "user=".$user."<br>";
                if( $user->getEmail() ) {
                    //echo "email=".$user->getEmail()."<br>";
                    if( $user->getEmail() != "-1" ) {
                        $emails[] = $user->getEmail();
                    }
                }
            }

        }
        //print_r($emails);

        //return implode(", ", $emails);
        return $emails;
    }

//    //$roles: role or partial role name
//    public function findByRoles($roles) {
//
//        $whereArr = array();
//        foreach($roles as $role) {
//            $whereArr[] = 'u.roles LIKE '."'%\"" . $role . "\"%'";
//        }
//
//        $qb = $this->em->createQueryBuilder();
//        $qb->select('u')
//            ->from('AppUserdirectoryBundle:User', 'u')
//            ->where( implode(' OR ',$whereArr) );
//
//        //echo "query=".$qb."<br>";
//
//        return $qb->getQuery()->getResult();
//    }

    public function findSystemUser() {

        //error_reporting(E_ALL ^ E_WARNING);

        $systemusers = $this->em->getRepository(User::class)->findOneByPrimaryPublicUserId('system');
        //$systemusers = $this->em->getRepository(User::class)->find(1);
        return $systemusers;

        $systemusers = $this->em->getRepository(User::class)->findBy(
            array(
                'primaryPublicUserId' => 'system'
            )
        );

        if( !$systemusers || count($systemusers) == 0  ) {
            return null;
        }

        $systemuser = $systemusers[0];

        return $systemuser;
    }

    //$nameStr: example string "Unknown Event"
    //$bundleName: UserdirectoryBundle
    //$className: EventTypeList
    //$params: array('type'=>'Medical')
    public function getObjectByNameTransformer( $author, $nameStr, $bundleName, $className, $params=null) {
        $transformer = new GenericTreeTransformer($this->em, $author, $className, $bundleName, $params);
        $nameStr = trim((string)$nameStr);
        return $transformer->reverseTransform($nameStr);
    }

    //$subjectEntities: single object or array of objects
    public function createUserEditEvent($sitename,$event,$user,$subjectEntities,$request,$action='Unknown Event') {

        //testing
        //return null;

        $logger = $this->container->get('logger');
        $em = $this->em;
        $userServiceUtil = $this->container->get('user_service_utility');

        $saveEventObjectType = false;

        //$em->clear(); //testing: prevent errors related to "not persisted objects"

        //if( !$user ) {
        //    $logger->warning("createUserEditEvent: "."User is not defined for $sitename for event=".$event);
        //    return null;
        //}

        if( $user ) {
            if( $user instanceof User ) {
                $user = $em->getRepository(User::class)->find($user->getId()); //to fix error "new un persisted entity found"
            } else {
                $user = $this->findSystemUser();
            }
        } else {
            $user = $this->findSystemUser();
        }

        $eventLog = $this->constructEventLog($sitename,$user,$request);

        if( !$eventLog ) {
            $logger->warning("createUserEditEvent: "."eventLog entity has not been generated for sitename ".$sitename);
        }

        $eventLog->setEvent($event);

        //make sure timezone set to UTC
        //date_default_timezone_set('UTC');

        //set Event Type
//        $eventtype = $em->getRepository('AppUserdirectoryBundle:EventTypeList')->findOneByName($action);
//        if( !$eventtype ) {
//            //$eventtype = $em->getRepository('AppUserdirectoryBundle:EventTypeList')->findOneByName('Entity Updated');
//            $eventtype = new EventTypeList();
//            $userutil = new UserUtil();
//            return $userutil->setDefaultList( $eventtype, null, $user, $action );
//            $em->persist($eventtype);
//        }
//        $objectParams = array(
//            'className' => 'EventTypeList',
//            'fullClassName' => "App\\UserdirectoryBundle\\Entity\\"."EventTypeList",
//            'fullBundleName' => 'UserdirectoryBundle'
//        );
//        $eventtype = $em->getRepository('AppUserdirectoryBundle:EventTypeList')->convertStrToObject( $action, $objectParams, $user );
        $eventtype = $this->getObjectByNameTransformer($user,$action,'UserdirectoryBundle','EventTypeList');

        $eventLog->setEventType($eventtype);

        //set logger entity(s)
        if( $subjectEntities ) {

            if( is_array($subjectEntities) == false && method_exists($subjectEntities,'getId') ) {

                $subjectEntity = $subjectEntities;
                $ids = $subjectEntity->getId();

            } else {

                $idsArr = array();

                foreach( $subjectEntities as $subjectObject ) {
                    $idsArr[] = $subjectObject->getId();
                }

                $ids = implode(", ",$idsArr);
                $ids = substr((string)$ids,0,255); //max length for string field
                $subjectEntity = $subjectEntities[0];

            }

            //get classname, entity name and id of subject entity
            $class = new \ReflectionClass($subjectEntity);
            $className = $class->getShortName();
            $classNamespace = $class->getNamespaceName();

            //set classname, entity name and id of subject entity
            $eventLog->setEntityNamespace($classNamespace);
            $eventLog->setEntityName($className);
            $eventLog->setEntityId($ids);

            //create EventObjectTypeList if not exists
            $eventObjectType = $this->getObjectByNameTransformer($user,$className,'UserdirectoryBundle','EventObjectTypeList');
            if( $eventObjectType ) {
                $eventLog->setObjectType($eventObjectType);

                //generate url if not exists
                $url = $eventObjectType->getUrl();
                //echo "url=".$url."<br>";
                //exit();
                if( !$url ) {
                    $url = $userServiceUtil->classNameUrlMapper($className);
                    $eventObjectType->setUrl($url);
                    $saveEventObjectType = true;
                }
            }

        } else {
            //$logger->warning("createUserEditEvent: "."subjectEntities are not provided");
        }

        //$logger = $this->container->get('logger');
        //$logger->notice("usersec: timezone=".date_default_timezone_get());

        $em->persist($eventLog);
        //$em->flush($eventLog);
        $em->flush();

//        if( $flush ) {
//            $em->flush();
//        }

        //if( $saveEventObjectType ) {
            //$em->flush($eventObjectType);
            //$em->flush();
        //}

        return $eventLog;
    }

    //add type to tree entity if exists
    public function addDefaultType($entity,$params) {
        $fullClassName = new \ReflectionClass($entity);
        $className = $fullClassName->getShortName();

        //add institutional type
        if( $className == "Institution" ) {
            if( array_key_exists('type',$params) && $params['type'] ) {
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:InstitutionType'] by [InstitutionType::class]
                $type = $this->em->getRepository(InstitutionType::class)->findOneByName($params['type']);
                $entity->addType($type);
            }
            if( array_key_exists('organizationalGroupType',$params) && $params['organizationalGroupType'] ) {
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:OrganizationalGroupType'] by [OrganizationalGroupType::class]
                $organizationalGroupType = $this->em->getRepository(OrganizationalGroupType::class)->findOneByName($params['organizationalGroupType']);
                $entity->setOrganizationalGroupType($organizationalGroupType);
            }
        }

        return $entity;
    }

    public function getDefaultSourceSystem($sitename=null)
    {

        $defaultSourceSystemName = 'ORDER Scan Order';

        if( $sitename == 'translationalresearch' ) {
            $defaultSourceSystemName = 'ORDER Translational Research';
        }
        if( $sitename == 'calllog' ) {
            $defaultSourceSystemName = 'ORDER Call Log Book';
        }
        if( $sitename == 'crn' ) {
            $defaultSourceSystemName = 'ORDER Critical Result Notifications';
        }
        if ($sitename == 'deidentifier' ) {
            $defaultSourceSystemName = 'ORDER Deidentifier';
        }
        if ($sitename == 'dashboard' ) {
            $defaultSourceSystemName = 'ORDER Dashboards';
        }
        if ($sitename == 'scan' || $sitename == null ) {
            $defaultSourceSystemName = 'ORDER Scan Order';  //'Scan Order';
        }

        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:SourceSystemList'] by [SourceSystemList::class]
        $source = $this->em->getRepository(SourceSystemList::class)->findOneByName($defaultSourceSystemName);

        if( !$source ) {
            if( $this->container ) {
                $logger = $this->container->get('logger');
                $logger->warning('Warning (Not Found): Default Source System with name "'.$defaultSourceSystemName.'"');
            }
        }

        //echo "source=".$source."<br>";
        return $source;
    }


    public function getDefaultSourceSystemByRequest( $request )
    {
        $sitename = $request->query->get('sitename');
        //$subdomain = "/order";
        $subdomain = "";
        $sitename = $subdomain.'/'.$sitename.'/';
        //echo "sitenamel=".$sitename."<br>";
        return $this->getDefaultSourceSystemByRequestUrl($sitename);
    }

    public function getDefaultSourceSystemByRequestUrl( $url, $request=null )
    {

        if( !$url ) {
            $url = $request->getUri();  // .../order/scan/...
        }
        //echo "url=".$url."<br>";

        $defaultSourceSystemName = null;    //'ORDER Scan Order';

        //$subdomain = "/order";
        $subdomain = "";

        if( strpos((string)$url, $subdomain.'/call-log-book/') !== false ) {
            $defaultSourceSystemName = 'ORDER Call Log Book';
        }
        if( strpos((string)$url, $subdomain.'/critical-result-notifications/') !== false ) {
            $defaultSourceSystemName = 'ORDER Critical Result Notifications';
        }
//        if( strpos((string)$url, '/order/deidentifier/') !== false ) {
//            $defaultSourceSystemName = 'ORDER Deidentifier';
//        }
        if( strpos((string)$url, $subdomain.'/deidentifier/') !== false ) {
            $defaultSourceSystemName = 'ORDER Deidentifier';
        }
        if( strpos((string)$url, $subdomain.'/scan/') !== false ) {
            $defaultSourceSystemName = 'ORDER Scan Order';  //'Scan Order';
        }
        if( strpos((string)$url, $subdomain.'/dashboards/') !== false ) {
            $defaultSourceSystemName = 'ORDER Dashboards';
        }

        if( !$defaultSourceSystemName ) {
            if( $this->container ) {
                $logger = $this->container->get('logger');
                $logger->warning('Warning (Not Found): Default Source System by url '.$url);
            }
            return null;
        }

        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:SourceSystemList'] by [SourceSystemList::class]
        $source = $this->em->getRepository(SourceSystemList::class)->findOneByName($defaultSourceSystemName);

        if( !$source ) {
            if( $this->container ) {
                $logger = $this->container->get('logger');
                $logger->warning('Warning (Not Found): Default Source System with name '.$defaultSourceSystemName);
            }
        }

        //echo "source=".$source."<br>";
        return $source;
    }


    //username - full username including user type ie external_username_@_ldap-user
    public function constractNewUser($username) {

        $serviceContainer = $this->container;

        //Check if username is email
        $user = $this->findUserByUsernameAsEmail($username);
        if( $user ) {
            throw new \Exception("User [$user] already exists with ID=".$user->getId());
            return $user;
        }
        $user = $this->getUserByUserstr($username);
        if( $user ) {
            throw new \Exception("User [$user] already exists with ID=".$user->getId());
            return $user;
        }

        //$userManager = $serviceContainer->get('fos_user.user_manager');
        $userManager = $this->container->get('user_manager');
        $userSecUtil = $serviceContainer->get('user_security_utility');

        $author = $userSecUtil->findSystemUser();
        //$author = $this->security->getUser();

        $usernamePrefix = $userSecUtil->getUsernamePrefix($username);
        $usernameClean = $userSecUtil->createCleanUsername($username);

        $default_time_zone = $serviceContainer->getParameter('default_time_zone');

        $user = $userManager->createUser();

        //////////////////////////////// get usertype ////////////////////////////////
        $userkeytype = $userSecUtil->getUsernameType($usernamePrefix);
        //echo "keytype=".$userkeytype.", id=".$userkeytype->getId()."<br>";

        //first time login when DB is clean
        if( !$userkeytype ) {
            //$userUtil = new UserUtil();
            //$userUtil = $this->container->get('user_utility');
            //$count_usernameTypeList = $userUtil->generateUsernameTypes();
            $userkeytype = $userSecUtil->getUsernameType($usernamePrefix);
        }

        //If $userkeytype is null (disabled or hidden), use defaultPrimaryPublicUserIdType from site settings
        if( !$userkeytype ) {
            $userkeytype = $userSecUtil->getSiteSettingParameter('defaultPrimaryPublicUserIdType');
        }

        if( !$userkeytype ) {
            throw new \Exception('User keytype is empty for prefix '.$usernamePrefix." and username=".$username);
        }
        //////////////////////////////// EOF get usertype ////////////////////////////////

        $user->setKeytype($userkeytype);
        $user->setPrimaryPublicUserId($usernameClean);
        $user->setUniqueUsername();

        $user->setEnabled(true);
        $user->getPreferences()->setTimezone($default_time_zone);

        //add default locations
        $userGenerator = $this->container->get('user_generator');
        $userGenerator->addDefaultLocations($user,null);

        $user->setPassword("");

        //set salt
        $salt = rtrim(str_replace('+', '.', base64_encode(random_bytes(32))), '=');
        $user->setSalt($salt);

        $user->setAuthor($author);
        $user->setCreateDate(new \DateTime());

        //$userManager->updateUser($user);

        return $user;
    }

    public function findUserByUsernameAsEmail($username) {

        $cwid = NULL;

        if( strpos((string)$username, '@') !== false ) {
            $cwidArr = explode("@",$username);
            if( count($cwidArr) > 1 ) {
                $cwid = $cwidArr[0];
                if( $cwid ) {
                    $cwid = trim((string)$cwid);
                }
            }
        }
        //exit("cwid=[$cwid]");

        if( !$cwid ) {
            return NULL;
        }

        $query = $this->em->createQueryBuilder()
            ->from(User::class, 'user')
            ->select("user")
            ->leftJoin("user.infos", "infos")
            ->where("infos.email LIKE :cwid OR infos.displayName LIKE :cwid")
            ->setParameters( array(
                'cwid' => $cwid
            ));

        $users = $query->getQuery()->getResult();

        if( count($users) > 0 ) {
            $user = $users[0];
            return $user;
        }

        return NULL;
    }


    //$name is entered by a user username. $name can be a guessed username
    //Use primaryPublicUserId as cwid
    public function getUserByUserstr( $name ) {

        //echo "get cwid name=".$name."<br>";

        $user = null;
        $cwid = null;

        if( $name ) {
            $name = trim((string)$name);
        }

        if( !$name ) {
            return NULL;
        }

        //get cwid
        $strArr = explode(" ",$name);

        if( count($strArr) > 0 ) {
            $cwid = $strArr[0];
        }

        //1) try first part
        if( $cwid ) {
            //echo "cwid=".$cwid."<br>";
            $user = $this->em->getRepository(User::class)->findOneByPrimaryPublicUserId($cwid);
        }

        //2) try full name
        if( !$user ) {
            $user = $this->em->getRepository(User::class)->findOneByPrimaryPublicUserId($name);
        }

        //3) try full name
        if( !$user ) {

            $query = $this->em->createQueryBuilder()
                ->from(User::class, 'user')
                ->select("user")
                ->leftJoin("user.infos", "infos")
                ->where("infos.email=:name OR infos.displayName=:name")
                ->setParameters( array(
                    'name' => $name
                ));

            $users = $query->getQuery()->getResult();

            if( count($users) > 0 ) {
                $user = $users[0];
            }

        }

        //4) try username cwid_@_ldap-user
//        if( !$user ) {
//            $userManager = $this->container->get('fos_user.user_manager');
//            $user = $userManager->findUserByUsername($name);
//        }
        if( !$user ) {
            $user = $this->em->getRepository(User::class)->findOneByUsername($name);
        }

        //5) try firstname lastname - cwid
        if( !$user ) {
            $strArr = explode("-",$name);

            if( count($strArr) > 0 ) {
                $displayName = trim((string)$strArr[0]);

                $query = $this->em->createQueryBuilder()
                    ->from(User::class, 'user')
                    ->select("user")
                    ->leftJoin("user.infos", "infos")
                    ->where("infos.displayName=:name")
                    ->setParameters(array(
                        'name' => $displayName
                    ));

                $users = $query->getQuery()->getResult();

                if (count($users) > 0) {
                    $user = $users[0];

                    if( count($strArr) > 1 ) {
                        //echo "strArr[1]=".$strArr[1]."<br>";
                        $strArr2 = explode(" ",trim((string)$strArr[1]));

                        if( count($strArr2) > 0 ) {
                            $cwid = $strArr2[0];
                        }

                        //try first part cwid
                        if( $cwid ) {
                            //echo "cwid=".$cwid."<br>";
                            $user = $this->em->getRepository(User::class)->findOneByPrimaryPublicUserId($cwid);
                        }
                    }

                }
            }
        }

        return $user;
    }

    //mimic depreciated mysql_real_escape_string
    public function mysql_escape_mimic($inp) {

        //return mysql_real_escape_string($inp);

        $search=array("'",'"');
        $replace=array("","");
        $inp = str_replace($search,$replace,$inp);

        if(is_array($inp))
            return array_map(__METHOD__, $inp);

        if(!empty($inp) && is_string($inp)) {
            return str_replace(array('\\', "\0", "\n", "\r", "'", '"', "\x1a"), array('\\\\', '\\0', '\\n', '\\r', "\\'", '\\"', '\\Z'), $inp);
        }

        return $inp;
    }




    ///////////////////////// User Role methods /////////////////////////
    public function getObjectRolesBySite( $object, $sitename, $associated=true ) {
        $objectSiteRoles = array();

        $roles = $this->getRolesBySite($sitename,$associated);

        foreach( $roles as $roleObject ) {
            if( $roleObject && $object->hasRole($roleObject->getName()) ) {
                $objectSiteRoles[] = $roleObject;
            }
        }

        return $objectSiteRoles;
    }

    public function getUserRolesBySite( $user, $sitename, $associated=true ) {
        $userSiteRoles = array();

        $roles = $this->getRolesBySite($sitename,$associated);

        foreach( $roles as $roleObject ) {
            //echo "roleObject=".$roleObject."<br>";
//            if( !$roleObject ) {
//                continue;
//            }
//            if( $associated ) {
//                if( $roleObject && $user->hasRole($roleObject->getName()) ) {
//                    $userSiteRoles[] = $roleObject;
//                }
//            } else {
//                //echo "not associated <br>";
//                if( $roleObject && !$user->hasRole($roleObject->getName()) ) {
//                    $userSiteRoles[] = $roleObject;
//                }
//            }
            if( $roleObject && $user->hasRole($roleObject->getName()) ) {
                $userSiteRoles[] = $roleObject;
            }
        }

        return $userSiteRoles;
    }
    public function getUserRoleIdsBySite( $user, $sitename, $associated=true ) {
        $userSiteRoles = array();

        $roles = $this->getRolesBySite($sitename,$associated);

        foreach( $roles as $roleObject ) {
            if( $roleObject && $user->hasRole($roleObject->getName()) ) {
                $userSiteRoles[] = $roleObject->getId();
            }
        }

        return $userSiteRoles;
    }

    public function getRolesByRoleNames( $roles, $glueStr=", " ) {
        $strRoles = array();
        foreach( $roles as $roleName ) {
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:Roles'] by [Roles::class]
            $role = $this->em->getRepository(Roles::class)->findOneByName($roleName);
            if($role) {
                $strRoles[] = $role->getAlias();
            }
        }
        return implode($glueStr,$strRoles);
    }

    public function getQueryUserBySite( $sitename ) {
        $dql = $this->getDqlUserBySite($sitename);
        $query = $dql->getQuery(); //$query = $this->em->createQuery($dql);
        return $query;
    }

    public function getDqlUserBySite( $sitename ) {

        //roles with sitename
        $roles = $this->getRolesBySite($sitename);
        //echo "roles count=".count($roles)."<br>";
        //print_r($roles);
        //exit('1');

        $repository = $this->em->getRepository(User::class);
        $dql =  $repository->createQueryBuilder("user");
        $dql->select('user');
        $dql->leftJoin("user.infos", "infos");
        $dql->leftJoin("user.keytype", "keytype");

        //roles where
        $whereArr = array();
        $where = "";
        $count = 0;
        foreach( $roles as $role ) {
            //$whereArr[] = "'".$role['name']."'";
            if( $count > 0 ) {
                $where .= " OR ";
            }
            $where .= "user.roles LIKE " . "'%".$role->getName()."%'";
            $count++;
        }
        //echo "where=".$where."<br>";

        if( !$where ) {
            $where = "1=0";
        }

        $dql->where($where);

        //Sort listed users on all /authorized-users/ pages for other sites alphabetically by last name.
        $dql->orderBy('infos.lastName', 'ASC');

        //echo "dql=".$dql."<br>";

        return $dql;
    }
    public function getRolesBySite( $sitename, $associated=true, $levelOnly=false ) {
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:Roles'] by [Roles::class]
        $repository = $this->em->getRepository(Roles::class);
        $dql =  $repository->createQueryBuilder("roles");
        $dql->select('roles');
        $dql->leftJoin("roles.sites", "sites");

        if( $associated ) {
            $dql->where("sites.name = :sitename OR sites.abbreviation = :sitename");
        } else {
            $dql->where("sites.name != :sitename AND sites.abbreviation != :sitename");
        }

        if( $levelOnly !== false ) {
            $dql->andWhere("roles.level = " . $levelOnly);
        }

        //only default and user-added types
        $dql->andWhere("roles.type = :typedef OR roles.type = :typeadd");

        $query = $dql->getQuery(); //$query = $this->em->createQuery($dql);

        $query->setParameters(array(
            "sitename" => $sitename,
            'typedef' => 'default',
            'typeadd' => 'user-added',
        ));

        $roles = $query->getResult();

        //dump($roles);
        //exit('111');

        return $roles;
    }
    //NOT working. Not used.
    public function getQueryUserBySite_SingleQuery( $sitename ) {
        $repository = $this->em->getRepository(User::class);
        $dql =  $repository->createQueryBuilder("user");
        $dql->select('user');
        $dql->leftJoin("user.infos", "infos");

        //$dql->leftJoin('AppUserdirectoryBundle:Roles', 'roles');
        //$dql->leftJoin("AppUserdirectoryBundle:Roles", "roles", "WITH", "user.roles LIKE '%roles.name%'");
        $dql->leftJoin(Roles::class, "roles", "WITH", "user.roles LIKE '%roles.name%'");
        $dql->leftJoin("roles.sites", "sites");

        $dql->where("sites.name LIKE :sitename");
        //$dql->where("sites IS NULL");
        //$dql->where("sites.id=4");
        //$dql->where("roles.name = 'ROLE_DEIDENTIFICATOR_WCM_NYP_ENQUIRER'");

        //echo "dql=".$dql."<br>";

        $query = $dql->getQuery(); //$query = $this->em->createQuery($dql);

        $query->setParameters(array(
            "sitename" => "'%".$this->siteName."%'"
        ));

        return $query;
    }

    public function getSiteRolesKeyValue( $sitename ) {
        $rolesArr = array();

        $roles = $this->getRolesBySite($sitename);

        foreach( $roles as $role ) {
            //$rolesArr[$role->getName()] = $role->getAlias(); //Symfony <2.8
            $rolesArr[$role->getAlias()] = $role->getName(); //Symfony >2.8
        }
        return $rolesArr;
    }

    public function getSiteRolesIdKeyValue( $sitename ) {
        $rolesArr = array();

        $roles = $this->getRolesBySite($sitename);

        foreach( $roles as $role ) {
            $rolesArr[$role->getId()] = $role->getAlias();
        }
        return $rolesArr;
    }

    //lowest level role == roles with level = 1
    public function getLowestRolesBySite( $sitename ) {
        return $this->getRolesBySite($sitename, true, 1);
    }

    //Not used!
    public function addOnlySiteRoles( $subjectUser, $newUserSiteRoles, $sitename ) {

        //TODO: this is not correct! We don't need to update the roles from the Group Management page. We need only add or remove user.
        return null;

        $originalUserSiteRoles = $this->getUserRolesBySite( $subjectUser, $sitename, true );

        if( $originalUserSiteRoles == $newUserSiteRoles ) {
            return null;
        }

        //TODO: this is not correct!
//        foreach( $originalUserSiteRoles as $originalUserSiteRole ) {
//            $subjectUser->removeRole($originalUserSiteRole);
//        }
//
//        foreach( $newUserSiteRoles as $newUserSiteRole ) {
//            $subjectUser->addRole($newUserSiteRole);
//        }

        //$arrayDiff = array_diff($originalUserSiteRoles, $newUserSiteRoles);
        $res = array(
            'originalUserSiteRoles' => $originalUserSiteRoles,
            'newUserSiteRoles' => $newUserSiteRoles
        );

        return $res;
    }
    ///////////////////////// EOF User Role methods /////////////////////////


    //$documentTypeFlag: only or except
    public function deleteOrphanFiles( $days, $documentType='Fellowship Application Spreadsheet', $documentTypeFlag='only' ) {

        if( $days == null || $days == "" || !is_int($days) ) {
            return "Invalid days parameter days=" . $days;
        }

        //$beforeDate = $startDate->format('Y');
        //DB date format: 2015-09-29 20:26:13.000000
        $nowDate = new \DateTime('now');
        $dateCorrectionStr = '-'.$days.' days';

        $beforeDate = $nowDate->modify($dateCorrectionStr)->format('Y-m-d');
        //echo "beforeDate=".$beforeDate."<br>";

        //get spreadsheets older than X year
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:Document'] by [Document::class]
        $repository = $this->em->getRepository(Document::class);
        $dql =  $repository->createQueryBuilder("document");
        $dql->select('document');
        $dql->leftJoin('document.type','documentType');

        $dql->where("document.entityNamespace IS NULL AND document.entityName IS NULL AND document.entityId IS NULL");
        $dql->andWhere("document.createdate < :beforeDate");

        $queryParameters = array(
            'beforeDate' => $beforeDate
        );

        if( $documentType ) {
            if( $documentTypeFlag == 'only' ) {
                $dql->andWhere("documentType.name = :documentType OR documentType.abbreviation = :documentType");
            }
            if( $documentTypeFlag == 'except' ) {
                $dql->andWhere("documentType.id IS NULL OR documentType.name != :documentType");
            }
            $queryParameters['documentType'] = $documentType;
        }

        $query = $dql->getQuery(); //$query = $this->em->createQuery($dql);

        //echo "query=".$query->getSql()."<br>";

        $query->setParameters($queryParameters);

        $documents = $query->getResult();

        //echo "documents count=".count($documents)."<br>";

        $deletedDocumentIdsArr = array();

        //foreach documents unlink and delete from DB
        foreach( $documents as $document ) {
            $deletedDocumentIdsArr[] = $document->getId();

            //document absolute path
            //$documentPath = $document->getAbsoluteUploadFullPath();
            //$documentPath = $this->container->get('kernel')->getRootDir() . '/../public/' . $document->getUploadDirectory().'/'.$document->getUniquename();
            $documentPath = $this->container->get('kernel')->getProjectDir() . DIRECTORY_SEPARATOR . 'public' .
                DIRECTORY_SEPARATOR . $document->getUploadDirectory() . DIRECTORY_SEPARATOR . $document->getUniquename();
            //$documentPath = "Uploaded/scan-order/documents/test.jpeg";
            //echo "documentPath=".$documentPath."<br>";

            //continue; //testing

            $this->em->remove($document);
            $this->em->flush();

            //remove file from folder
            if( is_file($documentPath) ) {
                //echo "file exists!!! ";
                unlink($documentPath);
            } else {
                //echo "file does exists??? ";
            }

            //break; //testing
        }

        return implode(",",$deletedDocumentIdsArr);
    }

//    //mirror function
//    public function getSiteSetting($parameter) {
//        return $this->getSiteSettingParameter($parameter);
//    }
    //return parameter specified by $parameter. If the first time login when site parameter does not exist yet, return -1.
    public function getSiteSettingParameter_ORIG( $parameter, $sitename=null ) {
        $params = $this->em->getRepository(SiteParameters::class)->findAll();

//        if( !$params ) {
//            //throw new \Exception( 'Parameter object is not found' );
//        }

        //echo "params count=".count($params)."<br>";

        if( count($params) == 0 ) {
            //return -1;
            return null;
            //return "[Site Settings is not initialized]";
        }

        if( count($params) > 1 ) {
            $logger = $this->container->get('logger');
            $msg = 'getSiteSettingParameter_ORIG: Must have only one parameter object. Found '.count($params).' object(s). Please follow the initialization instructions.';
            $logger->error($msg);
            exit($msg);
            //throw new \Exception( 'Must have only one parameter object. Found '.count($params).' object(s)' );
        }

        $param = $params[0];

        if( $parameter == null ) {
            return $param;
        }

        $getSettingMethod = "get".$parameter;

        //Get specific site setting parameter
        if( $sitename ) {
            //Convention name: CalllogSiteParameter
            $getterSiteParameter = "get".$sitename."SiteParameter"; //getCallogSiteParameter

            $specificSiteSettingParameter = $param->$getterSiteParameter();
            if( $specificSiteSettingParameter ) {

                if( !method_exists($specificSiteSettingParameter, $getSettingMethod) ){
                    return null;
                }

                $res = $specificSiteSettingParameter->$getSettingMethod();
            } else {
                return null;
                //return "[$sitename Site Settings is not initialized]";
            }

        } else {
            $res = $param->$getSettingMethod();
        }

        return $res;
    }

    //Prevent doctrine to cache the result, use: $this->em->detach
    //https://stackoverflow.com/questions/7956027/how-to-stop-doctrine-2-from-caching-a-result-in-symfony-2
    public function getSiteSettingParameter( $parameter, $sitename=null ) {
        $userServiceUtil = $this->container->get('user_service_utility');
        $param = $userServiceUtil->getSingleSiteSettingParameter();
        //$this->em->refresh($param);
        //$this->em->detach($param);

        if( $param === null ) {
            return null;
        }

        if( $parameter == null ) {
            return $param;
        }

        $getSettingMethod = "get".$parameter;

        //Get specific site setting parameter
        if( $sitename ) {
            //Convention name: CalllogSiteParameter
            $getterSiteParameter = "get".$sitename."SiteParameter"; //getCallogSiteParameter

            $specificSiteSettingParameter = $param->$getterSiteParameter();
            if( $specificSiteSettingParameter ) {

                if( !method_exists($specificSiteSettingParameter, $getSettingMethod) ){
                    return null;
                }

                $res = $specificSiteSettingParameter->$getSettingMethod();

                //$this->em->refresh($specificSiteSettingParameter);
                $this->em->detach($specificSiteSettingParameter);
            } else {
                return null;
                //return "[$sitename Site Settings is not initialized]";
            }

        } else {
            $res = $param->$getSettingMethod();
        }
        
        $this->em->detach($param);

        return $res;
    }

    public function setLoginAttempt( $request, $options ) {

        //return;

        $user = null;
        $username = null;
        $roles = null;

        if( !array_key_exists('serverresponse', $options) ) {
            //$options['serverresponse'] = null;
            $options['serverresponse'] = http_response_code();
        }

        //find site object by sitename
        $site = $this->em->getRepository(SiteList::class)->findOneByAbbreviation($options['sitename']);
        if( !$site ) {
            //throw new NotFoundHttpException('Unable to find SiteList entity by abbreviation='.$options['sitename']);
        }

        $logger = new Logger($site);

        //$token = $this->secToken->getToken();

        if( $this->security && $this->security->getUser() ) {

            //$user = $this->secToken->getToken()->getUser();
            //$username = $token->getUsername();
            $user = $this->security->getUser();
            //$user = $this->tokenStorage->getToken()->getUser();

//            if( $this->security->getToken() ) {
//                $username = $this->security->getToken()->getUsername();
//            } else {
//                $username = $user."";
//            }
            $username = $user."";

            //$this->security->setToken(null);
            //$this->security->getToken()->getSession()->invalidate();
            //$request->getSession()->invalidate();
            //$this->tokenStorage->setToken(null);
            //exit('$username='.$username);

            if( $user && is_object($user) ) {
                $roles = $user->getRoles();
            } else {
                $user = null;
            }

            $logger->setUser($user);

        } else {

            $username = $request->get('_username');

            $userDb = $this->em->getRepository(User::class)->findOneByUsername($username);
            $user = $userDb;

            $logger->setUser($userDb);

        }

        if( $options['eventtype'] == "Bad Credentials" ) {
            $options['event'] = $options['event'] . ". Username=".$username;
        }

        $logger->setRoles($roles);
        $logger->setUsername($username);
        $logger->setIp($request->getClientIp());
        $logger->setWidth($request->get('display_width'));
        $logger->setHeight($request->get('display_height'));
        $logger->setEvent($options['event']);
        $logger->setServerresponse($options['serverresponse']);

        ////////////// browser info //////////////
        //$browser = BrowserInfo::Instance();
        //$name = $browser->getBrowser();
        //$version = $browser->getVersion();
        //$platform = $browser->getPlatform();
        $browser = new Browser();
        $name = $browser->getName();
        $version = $browser->getVersion();

        $os = new Os();
        $platform = $os->getName();

        $browserInfo = $name . " " . $version . " on " . $platform;
        //echo "Your browser: " . $browserInfo . "<br>";
        ////////////// EOF browser info //////////////

        $userAgent = $browserInfo . "; User Agent: " . $_SERVER['HTTP_USER_AGENT'];
        $logger->setUseragent($userAgent);

        //set Event Type
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:EventTypeList'] by [EventTypeList::class]
        $eventtype = $this->em->getRepository(EventTypeList::class)->findOneByName($options['eventtype']);
        $logger->setEventType($eventtype);

        //set eventEntity
        $eventEntity = null;

        if( array_key_exists('eventEntity', $options) && $options['eventEntity'] ) {

            $eventEntity = $options['eventEntity'];

        } elseif( $user && $user instanceof User && $user->getId() ) {

            $eventEntity = $user;
        }

        if( $eventEntity ) {
            //get classname, entity name and id of subject entity
            $class = new \ReflectionClass($eventEntity);
            $className = $class->getShortName();
            $classNamespace = $class->getNamespaceName();

            //set classname, entity name and id of subject entity
            $logger->setEntityNamespace($classNamespace);
            $logger->setEntityName($className);
            $logger->setEntityId($eventEntity->getId());

            //create EventObjectTypeList if not exists
            $eventObjectType = $this->getObjectByNameTransformer($user,$className,'UserdirectoryBundle','EventObjectTypeList');
            if( $eventObjectType ) {
                $logger->setObjectType($eventObjectType);
            }
        }

        $this->em->persist($logger);
        $this->em->flush();
    }

    public function getMaxIdleTime() {

//        $params = $this->em->getRepository('AppUserdirectoryBundle:SiteParameters')->findAll();
//
//        if( !$params ) {
//            //new DB does not have SiteParameters object
//            return 1800; //30 min
//            //throw new \Exception( 'Parameter object is not found' );
//        }
//
//        if( count($params) != 1 ) {
//            throw new \Exception( 'Must have only one parameter object. Found '.count($params).'object(s)' );
//        }
//
//        $param = $params[0];
//        $maxIdleTime = $param->getMaxIdleTime();

        $maxIdleTime = $this->getSiteSettingParameter("maxIdleTime");
        //echo "maxIdleTime=".$maxIdleTime."<br>";

        if( !$maxIdleTime ) {
            //new DB does not have SiteParameters object
            return 1800; //30 min default
        }

        //return time in seconds
        $maxIdleTime = $maxIdleTime * 60;

        return $maxIdleTime;
    }
    public function getMaxIdleTimeAndMaintenance() {

//        $params = $this->em->getRepository('AppUserdirectoryBundle:SiteParameters')->findAll();
//
//        if( !$params ) {
//            //new DB does not have SiteParameters object
//            $res = array(
//                'maxIdleTime' => 1800,
//                'maintenance' => false
//            );
//            return $res; //30 min
//        }
//
//        if( count($params) != 1 ) {
//            throw new \Exception( 'Must have only one parameter object. Found '.count($params).'object(s)' );
//        }
//
//        $param = $params[0];

        //$param = $this->getSingleSiteSettingsParam();
        //$param = $this->siteSettingsParam;
        $userServiceUtil = $this->container->get('user_service_utility');
        $param = $userServiceUtil->getSingleSiteSettingParameter();

        if( !$param ) {
            $res = array(
                'maxIdleTime' => 1800,
                'maintenance' => false
            );
            return $res; //30 min
        }

        $maxIdleTime = $param->getMaxIdleTime();
        $maintenance = $param->getMaintenance();

        //do not use maintenance for admin
        //if( $secAuth->isGranted('ROLE_PLATFORM_DEPUTY_ADMIN') ) {
        if( $this->security->isGranted('ROLE_PLATFORM_DEPUTY_ADMIN') ) {
            $maintenance = false;
        }

        $debug = in_array( $this->container->get('kernel')->getEnvironment(), array('test', 'dev') );
        if( $debug ) {
            $maintenance = false;
        }

        //return time in seconds
        $maxIdleTime = $maxIdleTime * 60;

        $res = array(
            'maxIdleTime' => $maxIdleTime,
            'maintenance' => $maintenance
        );

        return $res;
    }


    public function getAutoAssignInstitution( $withAutoAssignEnable=true ) {
        if( $withAutoAssignEnable ) {
            $enableAutoAssignmentInstitutionalScope = $this->getSiteSettingParameter('enableAutoAssignmentInstitutionalScope');
            if( $enableAutoAssignmentInstitutionalScope ) {
                $autoAssignInstitution = $this->getSiteSettingParameter('autoAssignInstitution');
                return $autoAssignInstitution;
            }
        } else {
            $autoAssignInstitution = $this->getSiteSettingParameter('autoAssignInstitution');
            return $autoAssignInstitution;
        }

        return null;
    }

    //return absolute file name on the server which will work for web and command
    public function getAbsoluteServerFilePath( $document ) {
        //return realpath($this->container->get('kernel')->getRootDir() . "/../public/" . $document->getServerPath());
        return $this->container->get('kernel')->getProjectDir() . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . $document->getServerPath();
    }

    //checkAndAddPermissionToRole($role,"Submit a Vacation Request","VacReqRequest","create")
    public function checkAndAddPermissionToRole($role,$permissionListStr,$permissionObjectListStr,$permissionActionListStr) {

        $count = 0;
        $em = $this->em;
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:PermissionList'] by [PermissionList::class]
        $permission = $em->getRepository(PermissionList::class)->findOneByName($permissionListStr);
        if( !$permission ) {
            exit("Permission is not found by name=".$permissionListStr);
        }

        //make sure permission is added to role: role->permissions(Permission)->permission(PermissionList)->(PermissionObjectList,PermissionActionList)
        //check if role has permission (Permission): PermissionList with $permissionListStr
        $permissionExists = false;
        foreach( $role->getPermissions() as $rolePermission ) {
            if( $rolePermission->getPermission() && $rolePermission->getPermission()->getId() == $permission->getId() ) {
                $permissionExists = true;
            }
        }
        if( !$permissionExists ) {
            //exit('create new permission='.$permissionListStr);//testing
            //echo $role.': create new permission='.$permissionListStr."<br>";
            $rolePermission = new Permission();
            $rolePermission->setPermission($permission);
            $role->addPermission($rolePermission);
            $count++;
        }

        //make sure object is set
        if( !$permission->getPermissionObjectList() ) {
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:PermissionObjectList'] by [PermissionObjectList::class]
            $permissionObject = $em->getRepository(PermissionObjectList::class)->findOneByName($permissionObjectListStr);
            if( $permissionObject ) {
                $permission->setPermissionObjectList($permissionObject);
                $count++;
                echo 'set permission object: '.$permissionObjectListStr."<br>";
            } else {
                exit("Permission Object is not found by name=".$permissionObjectListStr);
            }
        }

        //make sure action is set
        if( !$permission->getPermissionActionList() ) {
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:PermissionActionList'] by [PermissionActionList::class]
            $permissionAction = $em->getRepository(PermissionActionList::class)->findOneByName($permissionActionListStr);
            if( $permissionAction ) {
                $permission->setPermissionActionList($permissionAction);
                $count++;
                echo 'set permission action: '.$permissionActionListStr."<br>";
            } else {
                exit("Permission Action is not found by name=".$permissionActionListStr);
            }
        }

        return $count;
    }


    public function transformDatestrToDateWithSiteEventLog($datestr,$sitename) {
        $date = null;

        if( !$datestr ) {
            return $date;
        }
        $datestr = trim((string)$datestr);
        //echo "###datestr=".$datestr."<br>";

        if( strtotime($datestr) === false ) {
            // bad format
            $msg = 'transformDatestrToDate: Bad format of datetime string='.$datestr;
            //throw new \UnexpectedValueException($msg);
            $logger = $this->container->get('logger');
            $logger->error($msg);

            //send email
            //$userSecUtil = $this->container->get('user_security_utility');
            //$systemUser = $userSecUtil->findSystemUser();
            //$event = $sitename." warning: " . $msg;
            //$userSecUtil->createUserEditEvent($sitename,$event,$systemUser,null,null,'Warning');

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
            //this is used to convert data string as entered in new-york time to UTC
            $utcTz = new \DateTimeZone("UTC");
            $nyTz = new \DateTimeZone("America/New_York");
            $date = new \DateTime($datestr);
            $date->setTime(12, 00);
            //echo "Original: data=".$date->format('M d Y H:i:s')."<br>";
            $date->setTimezone( $nyTz );
            //echo "after set: data=".$date->format('M d Y H:i:s')."<br>";
            if( $date ) {
                $date->setTimezone($utcTz);
            //    echo "set timezone UTC: data=".$date->format('M d Y H:i:s')."<br>";
            //    //exit('1');
            } else {
            //    //exit("date object is null for datestr=".$datestr);
            }
        } catch (\Exception $e) {
            $msg = 'Failed to convert string'.$datestr.'to DateTime:'.$e->getMessage();
            //throw new \UnexpectedValueException($msg);
            $logger = $this->container->get('logger');
            $logger->error($msg);
            $this->sendEmailToSystemEmail("Bad format of datetime string", $msg);
        }

        return $date;
    }
    public function sendEmailToSystemEmail($subject, $message, $toEmailsArr=array()) {
        //$logger = $this->container->get('logger');
        $userSecUtil = $this->container->get('user_security_utility');
        $emailUtil = $this->container->get('user_mailer_utility');

        $systemEmail = $userSecUtil->getSiteSettingParameter('siteEmail');
        $toEmailsArr[] = $systemEmail;

        //$logger->notice("sendEmailToSystemEmail: systemEmail=".$systemEmail."; subject=".$subject."; message=".$message);
        $emailUtil->sendEmail( $toEmailsArr, $subject, $message );
    }


    //{% set baseUrl = app.request.scheme ~'://'~app.request.host~app.request.getBaseURL() %}
    //{% set fullUrl = baseUrl ~ '/' ~ sitenameFull ~ '/' ~ entity.objectType.url ~ '/' ~ entity.entityId %}
    public function getAbsoluetFullLoggerUrl( $logger, $request ) {
        $url = null;
        //{% set baseUrl = app.request.scheme ~'://'~app.request.host~app.request.getBaseURL() %}
        //baseUrl ~ '/' ~ sitenameFull ~ '/' ~ entity.objectType.url ~ '/' ~ entity.entityId

        if( !$logger->getObjectType() ) {
            //echo "no object type url=".$url."<br>";
            return $url;
        }

        $siteName = $logger->getSite()->getName();

        //if exclusivelySites and $siteName in the exclusivelySites => OK, if not replace siteName by the first exclusivelySite
        $exclusivelySiteOk = false;
        foreach( $logger->getObjectType()->getExclusivelySites() as $site ) {
            if( $site == $siteName ) {
                $exclusivelySiteOk = true;
            }
        }
        if( count($logger->getObjectType()->getExclusivelySites()) > 0 && $exclusivelySiteOk === false ) {
            $siteName = $logger->getObjectType()->getExclusivelySites()[0]->getName()."";
        }

        //exception for "Accession"
        if( $logger->getObjectType() == 'Accession' ) {
            ///re-identify/?accessionType=WHATEVER-YOU-NEED-TO-SET-THIS-TO&accessionNumber=

        //process.py script: replaced namespace by ::class: ['AppOrderformBundle:AccessionType'] by [AccessionType::class]
            $accessionType = $this->em->getRepository(AccessionType::class)->findOneByName('Deidentifier ID');
            //echo "accessionType=".$accessionType."<br>";

            //find one valid accession
        //process.py script: replaced namespace by ::class: ['AppOrderformBundle:AccessionAccession'] by [AccessionAccession::class]
            $accessionAccession = $this->em->getRepository(AccessionAccession::class)->findOneBy(
                array(
                    'accession' => $logger->getEntityId(),
                    'status' => 'deidentified-valid'
                )
            );

            if( $accessionAccession && $accessionType ) {

                $accessionNumber = $accessionAccession->getField();

                //path=deidentifier_search
                $url = $this->container->get('router')->generate(
                    'deidentifier_search',
                    array(
                        'accessionType' => $accessionType->getId(),
                        'accessionNumber' => $accessionNumber
                    )
                    //UrlGeneratorInterface::ABSOLUTE_URL
                );
                //echo "url=".$url."<br>";

            }//if accession

        }

        if( $logger->getObjectType() == "SiteList" || $logger->getObjectType() == "Institution" ) {
            $siteName = "directory";
        }

        if( !$url ) {
            //Make sure url (i.e. 'entry/view') is set in the object type (EventObjectTypeList)
            $baseUrl = $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath();

            $objectUrl = $logger->getObjectType()->getUrl();
            if( !$objectUrl ) {
                $message = "Object can not be shown. Please set up the 'Url' field in the 'Event Object Type' list for the object '".$logger->getObjectType()."'.";
                //exit($objectUrl);

                $logger = $this->container->get('logger');
                $logger->warning($message);

                $url = $this->container->get('router')->generate(
                    "logger_warning_message",
                    array(
                        'message' => $message,
                    )
                    //UrlGeneratorInterface::ABSOLUTE_URL
                );

                //echo "no url=".$url."<br>";
                return $url;
            }

            $url = $baseUrl . '/' . $siteName . '/' . $objectUrl . '/' . $logger->getEntityId();
            //echo "logger->getObjectType()->getUrl()=".$logger->getObjectType()->getUrl()."<br>";
            //echo "url=".$url."<br>";
        }

        return $url;
    }
    public function getAbsoluetFullListUrl( $listEntity, $request ) {
        $path = null;
        $url = null;

        if( !$listEntity->getEntityNamespace() || !$listEntity->getEntityName() || !$listEntity->getEntityId() ) {
            return $url;
        }

        if( $listEntity->getEntityName() == 'User' ) {
            $path = 'employees_showuser';
        }

        if( $path ) {
            $url = $this->container->get('router')->generate(
                $path,
                array(
                    'id' => $listEntity->getEntityId(),
                )
                //UrlGeneratorInterface::ABSOLUTE_URL
            );
        }

//        if( !$url ) {
//            $baseUrl = $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath();
//            $url = $baseUrl . '/' . $siteName . '/' . $logger->getObjectType()->getUrl() . '/' . $logger->getEntityId();
//        }

        return $url;
    }

    public function getCurrentUserInstitution($user=null)
    {
        $em = $this->em;
        $securityUtil = $this->container->get('user_security_utility');
        $institution = null;

        if( $user ) {
            $userSiteSettings = $securityUtil->getUserPerSiteSettings($user);
            $institution = $userSiteSettings->getDefaultInstitution();
            //echo "1 inst=".$institution."<br>";
            if (!$institution) {
                $institutions = $securityUtil->getUserPermittedInstitutions($user);
                //echo "count inst=".count($institutions)."<br>";
                if (count($institutions) > 0) {
                    $institution = $institutions[0];
                }
                //echo "2 inst=".$institution."<br>";
            }
        }
        if (!$institution) {
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:Institution'] by [Institution::class]
            $institution = $em->getRepository(Institution::class)->findOneByAbbreviation("WCM");
        }

        return $institution;
    }

    /////////////////////// getHeadInfo Return: Chief, Eyebrow Pathology ///////////////////////
    //Group by institutions
    public function getHeadInfo( $user ) {

        //testing
        //return $user->getHeadInfo();

        $instArr = array();

        $instArr = $this->addTitleInfo($instArr,'administrativeTitle',$user->getAdministrativeTitles());

        $instArr = $this->addTitleInfo($instArr,'appointmentTitle',$user->getAppointmentTitles());

        $instArr = $this->addTitleInfo($instArr,'medicalTitle',$user->getMedicalTitles());

        $instArr = $this->groupByInst($instArr);

        return $instArr;
    }
    public function addTitleInfo( $instArr, $tablename, $titles ) {
        foreach( $titles as $title ) {
            $headServiceId = null;
            $elementInfo = null;
            if( $title->getName() ) {
                $name = $title->getName()->getName()."";
                $titleId = null;
                if( $title->getName()->getId() ) {
                    $titleId = $title->getName()->getId();
                    //echo "titleId=".$titleId."<br>";
                }

                //If a title has Position Type = Head of Service, don't merge this title with any others.
                //$headService = false;
                if( method_exists($title,'getUserPositions') ) {
                    foreach( $title->getUserPositions() as $userPosition ) {
                        if ($userPosition && $userPosition->getName() == 'Head of Service') {
                            //$headService = true;
                            $headServiceId = $title->getId();
                            break;
                        }
                    }
                }

                //add missing "Position Type" values to user's profiles
                $positionTypesStr = null;
                if( method_exists($title,'getUserPositions') ) {
                    $positionTypes = $title->getUserPositions();
                    $positionTypesArr = array();
                    foreach ($positionTypes as $positionType) {
                        $positionTypesArr[] = $positionType->getName();
                    }
                    $positionTypesStr = implode(", ", $positionTypesArr);
                }

                $elementInfo = array(
                    'tablename'=>$tablename,
                    'id'=>$titleId,
                    'name'=>$name,
                    'positiontypes'=>$positionTypesStr
                    //'headService'=>$headServiceId
                );
                //$elementInfo = $this->getSearchSameObjectUrl($elementInfo);
            }

            //$headInfo[] = 'break-br';

            $instId = 0;
            $institution = null;
            if( $title->getInstitution() ) {
                $institution = $title->getInstitution();
                $instId = $title->getInstitution()->getId();

                if( $headServiceId ) {
                    $instId = $instId."-".$headServiceId;
                }

            }

            if( array_key_exists($instId,$instArr) ) {
                //echo $instId." => instId already exists<br>";
            } else {
                //echo $instId." => instId does not exists<br>";
                $instArr[$instId]['instInfo'] = $this->getHeadInstitutionInfoArr($institution,$headServiceId);
            }

            if( $elementInfo ) {
                //echo "titleInfo titleId=".$elementInfo['id']."<br>";
                $instArr[$instId]['titleInfo'][] = $elementInfo;
            }

            //might add here the position types $instArr[$instId]['positiontypes'][]
//            if( $elementInfo ) {
//                $instArr[$instId]['positiontypes'][] = $elementInfo;
//            }

        }//foreach titles

        return $instArr;
    }
    public function getHeadInstitutionInfoArr($institution,$headServiceId) {

        //echo "inst=".$institution."<br>";
        //echo "count=".count($headInfo)."<br>";
        $pid = null;

        $headInfo = array();

        //service
        if( $institution ) {

            $institutionThis = $institution;
            //echo "inst=".$institutionThis."<br>";

            $name = $institutionThis->getName()."";
            $titleId = null;
            if( $institutionThis->getId() ) {
                $titleId = $institutionThis->getId();
            }
            $pid = null;
            $parent = $institutionThis->getParent();
            if( $parent && $parent->getId() ) {
                $pid = $parent->getId();
            }

            if( $headServiceId ) {
                //$titleId = $titleId."-".$headServiceId;
                $pid = $pid."-".$headServiceId;
            }

            if( $name ) {
                $elementInfo = array('tablename' => 'Institution', 'id' => $titleId, 'pid' => $pid, 'name' => $name);
                //$elementInfo = $this->getSearchSameObjectUrl($elementInfo, 'small');
                $headInfo[] = $elementInfo;
            }

        }

        //division
        if( $institution && $institution->getParent() ) {

            $institutionThis = $institution->getParent();
            //echo "inst=".$institutionThis."<br>";

            $name = $institutionThis->getName()."";
            $titleId = null;
            if( $institutionThis->getId() ) {
                $titleId = $institutionThis->getId();
            }
            $pid = null;
            $parent = $institutionThis->getParent();
            if( $parent && $parent->getId() ) {
                $pid = $parent->getId();
            }

            if($name) {
                $elementInfo = array('tablename' => 'Institution', 'id' => $titleId, 'pid' => $pid, 'name' => $name);
                //$elementInfo = $this->getSearchSameObjectUrl($elementInfo, 'small');
                $headInfo[] = $elementInfo;
            }

        }

        //department
        if( $institution && $institution->getParent() && $institution->getParent()->getParent() ) {

            $institutionThis = $institution->getParent()->getParent();
            //echo "inst=".$institutionThis."<br>";

            $name = $institutionThis->getName()."";
            $titleId = null;
            if( $institutionThis->getId() ) {
                $titleId = $institutionThis->getId();
            }
            $pid = null;
            $parent = $institutionThis->getParent();
            if( $parent && $parent->getId() ) {
                $pid = $parent->getId();
            }

            if($name) {
                $elementInfo = array('tablename' => 'Institution', 'id' => $titleId, 'pid' => $pid, 'name' => $name);
                //$elementInfo = $this->getSearchSameObjectUrl($elementInfo, 'small');
                $headInfo[] = $elementInfo;
            }

        }

        //institution
        if( $institution && $institution->getParent() && $institution->getParent()->getParent() && $institution->getParent()->getParent()->getParent() ) {

            $institutionThis = $institution->getParent()->getParent()->getParent();
            //echo "inst=".$institutionThis."<br>";

            $name = $institutionThis->getName()."";
            $titleId = null;
            if( $institutionThis->getId() ) {
                $titleId = $institutionThis->getId();
            }
            $pid = null;
            $parent = $institutionThis->getParent();
            if( $parent && $parent->getId() ) {
                $pid = $parent->getId();
            }

            if($name) {
                $elementInfo = array('tablename' => 'Institution', 'id' => $titleId, 'pid' => $pid, 'name' => $name);
                //$elementInfo = $this->getSearchSameObjectUrl($elementInfo, 'small');
                $headInfo[] = $elementInfo;
            }

            //$headInfo[] = 'break-hr';
        }

        //$headInfo[] = 'break-hr';

        return $headInfo;
    }
    public function getSearchSameObjectUrl( $elementInfo, $style=null ) {
        $url = $this->container->get('router')->generate(
            'employees_search_same_object',
            array(
                'tablename' => $elementInfo['tablename'],
                'id' => $elementInfo['id'],
                'name'=> $elementInfo['name']
            )
            //UrlGeneratorInterface::ABSOLUTE_URL
        );

        //add missing "Position Type" values to user's profiles
        $elementInfoName = $elementInfo['name'];
        if( array_key_exists('positiontypes', $elementInfo) && $elementInfo['positiontypes'] ) {
            $elementInfoName = $elementInfoName . " (" . $elementInfo['positiontypes'] . ")";
        }

        if( $style ) {
            $name = "<$style>".$elementInfoName."</$style>";
        } else {
            $name = $elementInfoName;
        }

        $elementInfo = '<a href="'.$url.'">'.$name.'</a>';
        return $elementInfo;
    }
    public function groupByInst( $instArr ) {
        //group by last institution only:
        //Assistant Professor of Pathology and Laboratory Medicine
        //Cytopathology, Gynecologic Pathology
        //Anatomic Pathology
        //Pathology and Laboratory Medicine
        //Weill Cornell Medical College

//        echo "Input instArr: <pre>";
//        print_r($instArr);
//        echo "</pre><br>";
        //return $instArr;

        //1) get pid group
        $firstCombinedArr = array();
        foreach( $instArr as $recordArr ) {
            $firstTitleId = "";
            $firstInstPid = "";
            $firstInst = NULL;
            //echo "0firstTitleId=".$recordArr['titleInfo'][0]['id']."<br>";
            //if( array_key_exists('titleInfo',$recordArr) && count($recordArr['titleInfo']) > 0 ) {
                if( array_key_exists('instInfo',$recordArr) && count($recordArr['instInfo']) > 0 ) {
                    $firstInst = $recordArr['instInfo'][0];
                    $firstInstPid = $firstInst['pid'];
                }
                //$firstInstId = $recordArr['instInfo'][0]['id'];
                if( array_key_exists('titleInfo',$recordArr) && count($recordArr['titleInfo']) > 0 ) {
                    $firstTitleId = $recordArr['titleInfo'][0]['id'];
                }
                if( $firstTitleId ) {
                    $firstCombineId = $firstTitleId . "-" . $firstInstPid;
                    $firstCombinedArr[$firstCombineId][] = $firstInst;
                }
                //echo "1firstTitleId=$firstTitleId<br>";
            //}
        }
//        echo "firstCombinedArr:<pre>";
//        print_r($firstCombinedArr);
//        echo "</pre><br>";
//        echo "After 1 instArr:<pre>";
//        print_r($instArr);
//        echo "</pre><br>";

        //2) construct a new array using $firstCombinedArr
        $groupInstArr = array();
        foreach( $instArr as $recordArr ) {

            $firstInstPid = null;
            $firstTitleId = null;
            if( array_key_exists('instInfo',$recordArr) && count($recordArr['instInfo']) > 0 ) {
                $firstInstPid = $recordArr['instInfo'][0]['pid'];
                //$firstInstId = $recordArr['instInfo'][0]['id'];
                //$firstTitleId = $recordArr['titleInfo'][0]['id'];
                //$firstCombineId = $firstTitleId."-".$firstInstPid;
            }
            if( array_key_exists('titleInfo',$recordArr) && count($recordArr['titleInfo']) > 0 ) {
                //$firstInstPid = $recordArr['instInfo'][0]['pid'];
                //$firstInstId = $recordArr['instInfo'][0]['id'];
                $firstTitleId = $recordArr['titleInfo'][0]['id'];
                //$firstCombineId = $firstTitleId."-".$firstInstPid;
            }

            //if( $firstTitleId ) {
            //if( $recordArr['headService'] === false ) {
                if (array_key_exists('titleInfo', $recordArr)) {
                    foreach ($recordArr['titleInfo'] as $titleInfoArr) {
                        //echo "PID:$firstInstPid; titleInfoID:$firstTitleId; titleInfoArr:<pre>";
                        //print_r($titleInfoArr);
                        //echo "</pre><br>";
                        $firstTitleId = $titleInfoArr['id'];
                        $groupInstArr[$firstInstPid]['titleInfo'][$firstTitleId] = $this->getSearchSameObjectUrl($titleInfoArr);
                    }
                }
            //}

            $instHrefArr = array();
            foreach( $recordArr['instInfo'] as $instInfoArr ) {

                $thisFirstInstPid = $instInfoArr['pid'];
                $thisFirstCombineId = $firstTitleId."-".$instInfoArr['pid'];
                //echo "firstCombineId=".$firstCombineId."<br>";
                $instHrefEl = $this->getSearchSameObjectUrl($instInfoArr,'small');

                if( array_key_exists($thisFirstCombineId,$firstCombinedArr) ) {

                    $firstHrefElArr = array();
                    foreach( $firstCombinedArr[$thisFirstCombineId] as $firstEl ) {
                        $firstHrefElArr[] = $this->getSearchSameObjectUrl($firstEl,'small');
                    }
                    $instHrefEl = implode(", ",$firstHrefElArr);

                }

                $instHrefArr[$thisFirstInstPid] = $instHrefEl;
            }
            //echo "instInfo:<pre>";
            //print_r($instHrefArr);
            //echo "</pre><br>";
            $groupInstArr[$firstInstPid]['instInfo'] = $instHrefArr;



        }
//        echo "Final groupInstArr:<pre>";
//        print_r($groupInstArr);
//        echo "</pre><br>";

        return $groupInstArr;
    }
    /////////////////////// EOF getHeadInfo ///////////////////////



    public function setDefaultList( $entity, $count, $user, $name=null ) {

        if( $count == null ) {
            $class = new \ReflectionClass($entity);
            $className = $class->getShortName();          //ObjectTypeText
            $classNamespace = $class->getNamespaceName(); //App\UserdirectoryBundle\Entity

//            //format to: "AppUserdirectoryBundle:ObjectTypeText"
//            $classNamespaceArr = explode("\\",$classNamespace);
//            if( count($classNamespaceArr) > 2 ) {
//                $classNamespaceShort = $classNamespaceArr[0] . $classNamespaceArr[1];
//                $classFullName = $classNamespaceShort . ":" . $className;
//            } else {
//                throw new \Exception( 'Corresponding value list namespace is invalid: '.$classNamespace );
//            }

            $classFullName = $classNamespace."\\".$className;
            $count = $this->getMaxField($classFullName);
            //echo "count=".$count."<br>";
        }

        //[2016-12-31 16:19:50] request.CRITICAL: Uncaught PHP Exception Doctrine\ORM\ORMInvalidArgumentException:
        // "A new entity was found through the relationship 'App\UserdirectoryBundle\Entity\LabResultUnitsMeasureList#creator'
        // that was not configured to cascade persist operations for entity: firstname lastname - cwid.
        // To solve this issue: Either explicitly call EntityManager#persist() on this unknown entity or configure cascade persist
        if( $user instanceof User ) {
            $user = $this->em->getRepository(User::class)->find($user->getId());
            if (!$user) {
                exit("No user found by id " . $user->getId());
            }
        } else {
            $user = NULL;
        }

        $entity->setOrderinlist( $count );
        $entity->setCreator( $user );
        $entity->setCreatedate( new \DateTime() );
        $entity->setType('user-added');
        if( $name ) {
            $entity->setName( trim((string)$name) );
        }
        return $entity;
    }
    //get count by max id
    public function getMaxField( $classFullName, $field="orderinlist" ) {
        //echo "classFullName=" . $classFullName . "<br>";
        //$field = "id";
        //$field = "orderinlist";
        $repository = $this->em->getRepository($classFullName);
        $dql =  $repository->createQueryBuilder("u");
        $dql->select('MAX(u.'.$field.') as fieldMax');
        //$dql->setMaxResults(1);
        $res = $dql->getQuery()->getSingleResult();
        $fieldMax = $res['fieldMax'];
        if( $fieldMax ) {
            //echo "0 fieldMax=" . $fieldMax . "<br>";
            $fieldMax = intval($fieldMax);
            //round to the next 10th: 22 => 30
            $fieldMax = $this->roundUpToNextTen($fieldMax);
            //$fieldMax = $fieldMax + 10;
        } else {
            $fieldMax = 10;
        }
        //echo "1 fieldMax=" . $fieldMax . "<br>";
        return $fieldMax;
    }
    function roundUpToNextTen($roundee) {
        $r = $roundee % 10;
        if( $r == 0 ) {
            return $roundee + 10;
        }
        return $roundee + 10 - $r;
    }
//    //get count by max orderinlist
//    public function getMaxIdByOrderinlist($classFullName) {
//        $repository = $this->em->getRepository($classFullName);
//        $query = $repository->createQueryBuilder('s');
//        $query->select('s, MAX(s.orderinlist) AS maxOrderinlist');
//        $query->groupBy('s');
//        $query->setMaxResults(1);
//        $query->orderBy('maxOrderinlist', 'DESC');
//        $results = $query->getQuery()->getResult();
//        if( $results && count($results) > 0 ) {
//            $orderinlist = $results[0]['maxOrderinlist'];
//            $orderinlist = intval($orderinlist) + 10;
//        } else {
//            $orderinlist = 10;
//        }
//        return $orderinlist;
//    }

    public function getListByNameAndObject( $object, $mapper ) {

        if( !$object ) {
            return null;
        }

        if( !$mapper || count($mapper) == 0 ) {
            return null;
        }

        $class = new \ReflectionClass($object);
        $className = $class->getShortName();          //ObjectTypeText
        $classNamespace = $class->getNamespaceName(); //App\UserdirectoryBundle\Entity

        //echo "classNamespace=".$classNamespace."<br>";
        //echo "className=".$className."<br>";
        //echo "entityId=".$object->getId()."<br>";
        //print_r($mapper);

        //$treeRepository = $this->em->getRepository($mapper['prefix'].$mapper['bundleName'].':'.$mapper['className']);
        $treeRepository = $this->em->getRepository($mapper['prefix']."\\".$mapper['bundleName'].'\\Entity\\'.$mapper['className']);
        $dql =  $treeRepository->createQueryBuilder("list");
        $dql->select('list');
        $dql->where("list.entityName = :entityName AND list.entityNamespace = :entityNamespace AND list.entityId = :entityId");

        $query = $dql->getQuery(); //$query = $this->em->createQuery($dql);

        //echo "query=".$query->getSql()."<br>";

        $query->setParameters(
            array(
                'entityName' => $className,
                'entityNamespace' => $classNamespace,
                //'entityId' => "'".$object->getId()."'"
                'entityId' => $object->getId().""
            )
        );

        $results = $query->getResult();
        //echo "count=".count($results)."<br>";

        if( count($results) > 0 ) {
            return $results[0];
        }

        return null;
    }

    public function getRoleAliasByName( $name ) {
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:Roles'] by [Roles::class]
        $role = $this->em->getRepository(Roles::class)->findOneByName($name);
        if( $role ) {
            return $role->getAlias();
        }
        return null;
    }

    public function isSelfSignUp( $sitename ) {
        $siteObject = $this->em->getRepository(SiteList::class)->findOneByAbbreviation($sitename);
        if( $siteObject && $siteObject->getSelfSignUp() === true ) {
            return true;
        }
        return false;
    }

    public function isRequireVerifyMobilePhone( $sitename ) {
        $siteObject = $this->em->getRepository(SiteList::class)->findOneByAbbreviation($sitename);
        if( $siteObject && $siteObject->getRequireVerifyMobilePhone() === true ) {
            return true;
        }
        return false;
    }
    public function isRequireMobilePhoneToLogin( $sitename ) {
        $siteObject = $this->em->getRepository(SiteList::class)->findOneByAbbreviation($sitename);
        if( $siteObject && $siteObject->getRequireMobilePhoneToLogin() === true ) {
            return true;
        }
        return false;
    }

    public function isSiteAccessible( $sitename ) {
        if( $sitename == "employees" ) {
            //always enabled for employees site
            return true;
        }

        $siteObject = $this->em->getRepository(SiteList::class)->findOneByAbbreviation($sitename);
        if( $siteObject && $siteObject->getAccessibility() === true ) {
            return true;
        }

        //show login page, but not allowed when authenticated and visit the not accessible sites
        if( $this->security ) {
            $user = $this->security->getUser();
            //exit("user=".$user);
            if ($user && $user instanceof User) {
                if ($this->security->isGranted('ROLE_PLATFORM_DEPUTY_ADMIN')) {
                    //echo "admin <br>";
                    return true;
                }
            }
            if ($user && !($user instanceof User)) {
                //anon. user -> not logged in (login page)
                return true;
            }
        }

        return false;
    }

    public function isSiteShowLinkHomePage( $sitename ) {
        if( $sitename == "employees" ) {
            //always show for employees site
            return true;
        }
        $siteObject = $this->em->getRepository(SiteList::class)->findOneByAbbreviation($sitename);
        if( $siteObject && ($siteObject->getShowLinkHomePage() === true || $siteObject->getShowLinkHomePage() === null) ) {
            return true;
        }
        return false;
    }

    public function isSiteShowLinkNavbar( $sitename ) {
        if( $sitename == "employees" ) {
            //always show for employees site
            return true;
        }
        $siteObject = $this->em->getRepository(SiteList::class)->findOneByAbbreviation($sitename);
        if( $siteObject && ($siteObject->getShowLinkNavbar() === true || $siteObject->getShowLinkNavbar() === null) ) {
            return true;
        }
        return false;
    }

    public function getSiteFromEmail( $sitenameAbbreviation ) {
        $fromEmail = null;
        if( $sitenameAbbreviation ) {
            $siteObject = $this->em->getRepository(SiteList::class)->findOneByAbbreviation($sitenameAbbreviation);
            if ($siteObject) {
                $fromEmail = $siteObject->getFromEmail();
            }
        }
        if( !$fromEmail ) {
            $fromEmail = $this->getSiteSettingParameter('siteEmail');
        }
        return $fromEmail;
    }

    public function allowSiteLogin($sitename) {
        $environment = $this->getSiteSettingParameter('environment');
        if( $environment == 'live' && $this->isSiteAccessible($sitename) === false ) {
            return false;
        }
        return true;
    }

    public function getNotEmptyDefaultSiteParameter($parameterName, $classBundleName=null) {
        $defaultParameter = $this->getSiteSettingParameter($parameterName);
        if( !$defaultParameter && $classBundleName ) {
            $listEls = $this->em->getRepository($classBundleName)->findAll();
            if( count($listEls) > 0 ) {
                $defaultParameter = $listEls[0];
            }
        }

        return $defaultParameter;
    }

    public function getPlatformLogo() {
        $platformLogoPath = null;
        $platformLogos = $this->getSiteSettingParameter('platformLogos');
        if( count($platformLogos) > 0 ) {
            $platformLogo = $platformLogos->first();
            //$platformLogoPath = $platformLogo->getAbsoluteUploadFullPath();
            $userServiceUtil = $this->container->get('user_service_utility');
            $platformLogoPath = $userServiceUtil->getDocumentAbsoluteUrl($platformLogo);
        }
        return $platformLogoPath;
    }

    //Configuring the Request Context per Command . Set it to liveSiteRootUrl instead of localhost
    // http://symfony.com/doc/current/cookbook/console/request_context.html
    public function getRequestContextRouter() {
        $environment = $this->getSiteSettingParameter('environment');
        if( $environment != 'live' ) {
            return $this->container->get('router');
        }
        $request = $this->container->get('request_stack')->getCurrentRequest();
        if( !$request ) {
            $userSecUtil = $this->container->get('user_security_utility');
            $liveSiteRootUrl = $userSecUtil->getSiteSettingParameter('liveSiteRootUrl');    //http://c.med.cornell.edu/order/
            $liveSiteHost = parse_url($liveSiteRootUrl, PHP_URL_HOST); //c.med.cornell.edu
            //echo "liveSiteHost=".$liveSiteHost."\n";
            //exit('111');

            $connectionChannel = $userSecUtil->getSiteSettingParameter('connectionChannel');
            if( !$connectionChannel ) {
                $connectionChannel = 'http';
            }

            $context = $this->container->get('router')->getContext();
            $context->setHost($liveSiteHost);
            $context->setScheme($connectionChannel);
            //$context->setBaseUrl('/order');
        }
        return $this->container->get('router');
    }


















    // Note for institution permissions:

//OrderUtil.php
// addInstitutionQueryCriterion($user,$criteriastr)
//  -> getInstitutionQueryCriterion($user) {
//     1) User's PermittedInstitutions
//        $permittedInstitutions = $securityUtil->getUserPermittedInstitutions($user);
//     2) Collaboration check
//        $collaborations = $this->em->getRepository('AppUserdirectoryBundle:Institution')->findCollaborationsByNode($permittedInstitution);
//        foreach( $collaborations as $collaboration ) {
//          foreach( $collaboration->getInstitutions() as $collaborationInstitution ) {
//          }
//        }

//SecurityUtil.php
// hasUserPermission( $entity, $user )
//  -> getUserPermittedInstitutions($user) {
//    1) check if the user belongs to the same institution
//        $permittedInstitutions = $this->getUserPermittedInstitutions($user);
//    2) Check for collaboration
//        $collaborations = $this->em->getRepository('AppUserdirectoryBundle:Institution')->findCollaborationsByNode($permittedInstitution);
//        foreach( $collaborations as $collaboration ) {
//            foreach( $collaboration->getInstitutions() as $collaborationInstitution ) {
//                if(getRepository('AppUserdirectoryBundle:Institution')->isNodeUnderParentnode($collaborationInstitution,$entity->getInstitution()) ) {
//                    $hasCollaborationInst = true;
//                    break;
//                }
//            }
//        }



    ///////////////// From Orderform SecurityUtil ///////////////////
    //user has permission to perform the view/edit the valid field, created by someone else, if he/she is submitter or ROLE_SCANORDER_PROCESSOR or service chief or division chief
    //Added 25Nov2015: If user A submits a scan order with WCMC as the Institutional PHI Scope in Order Info and user B belongs to the institution NYP,
    // they can not see each other's orders/patient data/etc.
    //$entity is object: message or patient, accession, part ...
    //$collaborationTypesStrArr: array("Union","Intersection","Untrusted Intersection"); if null - ignore collaboration.
    //$actionStrArr: array("show","edit","amend"); if null - ignore (allow) all actions; if not supported action - allow this action.
    //Used by: CheckController (check button on patient hierarchy), MultiScanOrderController (show patient hierarchy in the order)
    public function hasUserPermission( $entity, $user, $collaborationTypesStrArr=array("Union"), $actionStrArr=array("show") ) {
        //echo "hasUserPermission <br>";
        if( $entity == null ) {
            return true;
        }

        if( $user == null ) {
            return false;
        }

        if( !$entity->getInstitution() ) {
            throw new \Exception( 'Entity is not linked to any Institution. Entity:'.$entity );
        }

        ///////////////// 1) check if the object is under user's permitted institutions /////////////////
        //check if entity is under user's permitted and collaborated institutions
        if( $this->isObjectUnderUserPermittedCollaboratedInstitutions( $entity, $user, $collaborationTypesStrArr ) == false ) {
            //exit("isObjectUnderUserPermittedCollaboratedInstitutions false");
            return false;
        }
        ///////////////// EOF 1) /////////////////

        ///////////////// 2) check if logged in user is granted given action for a given object $entity (using voter) /////////////////
        if( $this->isLoggedUserGrantedObjectActions($entity,$actionStrArr) ) {
            return true;
        }
        ///////////////// EOF /////////////////

        //exit("hasUserPermission: no permission to show ".$entity);
        return false;
    }

    //check user actions
    private function isLoggedUserGrantedObjectActions( $entity, $actionStrArr ) {
        if( !$actionStrArr ) {
            return false;
        }
        foreach( $actionStrArr as $action ) {
            //echo "check action=".$action."<br>";
            if( false === $this->security->isGranted($action, $entity) ) {
                return false;
            }
        }

        //echo "Logged in user can perform action=".$action." on object=".$entity."<br>";
        return true;
    }

    public function isObjectUnderUserPermittedCollaboratedInstitutions( $entity, $user, $collaborationTypesStrArr ) {
        $permittedInstitutions = $this->getUserPermittedInstitutions($user);

        //a) check permitted institutions
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:Institution'] by [Institution::class]
        if( $this->em->getRepository(Institution::class)->isNodeUnderParentnodes($permittedInstitutions,$entity->getInstitution()) ) {
            return true;
        }

        //b) if user's permitted institutions are not enough to access this entity => check for collaboration institutions
        $orderUtil = $this->container->get('scanorder_utility');
        $collaborationInstitutions = $orderUtil->getPermittedScopeCollaborationInstitutions($permittedInstitutions,$collaborationTypesStrArr,false);

        //echo "collaborationInstitutions count=".count($collaborationInstitutions)."<br>";
        //foreach( $collaborationInstitutions as $collaborationInstitution ) {
        //echo "collaborationInstitution=".$collaborationInstitution."<br>";
        //}

        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:Institution'] by [Institution::class]
        if( $this->em->getRepository(Institution::class)->isNodeUnderParentnodes($collaborationInstitutions,$entity->getInstitution()) ) {
            return true;
        }
        //exit("no collaboration institutions");

        return false;
    }


//    //wrapper for hasUserPermission
//    public function hasPermission( $entity, $security_content ) {
//        return $this->hasUserPermission($entity,$security_content->getToken()->getUser());
//    }

    //check if the given user can perform given actions on the content of the given order
    public function isUserAllowOrderActions( $order, $user, $actions=null ) {
        //echo "is User Allow OrderActions <br>";
        if( !$this->hasUserPermission( $order, $user, array("Union"), $actions ) ) {
            //exit('has permission false');
            return false;
        }

        //if actions are not specified => allow all actions
        if( $actions == null ) {
            return true;
        }

        //if actions is not array => return false
        if( !is_array($actions) ) {
            throw new \Exception('Actions must be an array');
            //return false;
        }

        //at this point, actions array has list of actions to performed by this user
        //echo "order=".$order->getId()."<br>";
        //print_r($actions);

        //processor and division chief can perform any actions
        if(
            $this->security->isGranted('ROLE_SCANORDER_ADMIN') ||
            $this->security->isGranted('ROLE_SCANORDER_PROCESSOR') ||
            $this->security->isGranted('ROLE_SCANORDER_DIVISION_CHIEF')
        ) {
            return true;
        }

        //submitter(owner) and ordering provider can perform any actions
        //echo $order->getProvider()->getId() . " ?= " . $user->getId() . "<br>";
        $isProxyUser = false;
        foreach( $order->getProxyuser() as $proxyuser ) {
            if( $proxyuser->getUser() && $proxyuser->getUser()->getId() === $user->getId() ) {
                $isProxyUser = true;
                break;
            }
        }

        if( $order->getProvider()->getId() === $user->getId() || $isProxyUser ) {
            return true;
        }

        //order's institution
        $orderInstitution = $order->getInstitution();

        $userSiteSettings = $this->getUserPerSiteSettings($user);
        $userChiefServices = $userSiteSettings->getChiefServices();

        //service chief can perform any actions
        //if( $userChiefServices->contains($service) ) {
        //    return true;
        //}

        //service chief can perform any actions for all orders under his/her service scope
        foreach( $userChiefServices as $userChiefService ) {
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:Institution'] by [Institution::class]
            if( $this->em->getRepository(Institution::class)->isNodeUnderParentnode($userChiefService, $orderInstitution) ) {
                return true;
            }
        }

        //At this point we have only regular users
        //print_r($actions);

        $actionAllowed = false;

        //for each action
        foreach( $actions as $action ) {

            //echo "action=".$action."<br>";

            //status change can be done only by submitter(owner), ordering provider, or service chief: it would not get here, so not allowed
            if( $action == 'changestatus' ) {
                return false;
            }

            //amend can be done only by submitter(owner), ordering provider, or service chief: it would not get here, so not allowed
            if( $action == 'amend' ) {
                return false;
            }

            //edit can be done only by submitter(owner), ordering provider, or service chief: it would not get here, so not allowed
            if( $action == 'edit' ) {
                return false;
            }

            //show is allowed if the user belongs to the same service
            if( $action == 'show' ) {
                $actionAllowed = true;
                //show action is allowed to all users which passed hasUserPermission and reached this point, so disable the code below.
//                $userServices = $userSiteSettings->getScanOrderInstitutionScope();
//                foreach( $userServices as $userService ) {
//                    if( $this->em->getRepository('AppUserdirectoryBundle:Institution')->isNodeUnderParentnode($userService, $orderInstitution) ) {
//                        return true;
//                    }
//                }
            }
        }

        if( $actionAllowed ) {
            return true;
        }

        //exit('is User Allow Order Actions: no permission');
        return false;
    }

    public function getUserPermittedInstitutions($user) {

        $institutions = new ArrayCollection();

        $entity = $this->getUserPerSiteSettings($user);

        if( !$entity ) {
            //echo "no UserPerSiteSettings found for ".$user."<br>";
            return $institutions;
        }

        $institutions = $entity->getPermittedInstitutionalPHIScope();

        return $institutions;
    }

//    public function getUserDefaultService($user) {
//        $entity = $this->getUserPerSiteSettings($user);
//
//        if( !$entity )
//            return null;
//
//        return $entity->getDefaultService();
//    }

    public function getScanOrdersServicesScope($user) {

        $institutions = new ArrayCollection();
        //$institution = null;

        $entity = $this->getUserPerSiteSettings($user);

        if( !$entity ) {
            //echo "!entity <br>";
            return $institutions;
        }

        $institutions = $entity->getScanOrderInstitutionScope();

        return $institutions;
    }

    public function getUserChiefServices($user) {

        $services = new ArrayCollection();

        $entity = $this->getUserPerSiteSettings($user);

        if( !$entity )
            return $services;

        $services = $entity->getChiefServices();

        return $services;
    }

    public function getUserPerSiteSettings($user) {
        if( $user instanceof User ) {
            return $user->getPerSiteSettings();
        } else {
            return null;
        }
        //$entity = $this->em->getRepository('AppOrderformBundle:PerSiteSettings')->findOneByUser($user);
        //return $entity;
    }

//    public function getDefaultDepartmentDivision($message,$userSiteSettings) {
//
//        if( $service = $message->getScanorder()->getService() ) {
//            $division = $service->getParent();
//            $department = $division->getParent();
//        } else {
//            //first get default division and department
//            $department = $userSiteSettings->getDefaultDepartment();
//            if( !$department ) {
//                //set default department to Pathology and Laboratory Medicine
//                $department = $this->em->getRepository('AppUserdirectoryBundle:Department')->findOneByName('Pathology and Laboratory Medicine');
//
//            }
//            if( $message->getInstitution() == null || ($message->getInstitution() && $department->getParent()->getId() != $message->getInstitution()->getId()) ) {
//                $department = null;
//            }
//
//            $division = $userSiteSettings->getDefaultDivision();
//            if( !$division ) {
//                //set default division to Anatomic Pathology
//                $division = $this->em->getRepository('AppUserdirectoryBundle:Division')->findOneByName('Anatomic Pathology');
//            }
//            if( $department == null || ($department && $division && $division->getParent()->getId() != $department->getId()) ) {
//                $division = null;
//            }
//
//        }
////        echo $department->getParent()->getId()."?=?".$message->getInstitution()->getId()."<br>";
//
//
//        $params = array();
//        $params['department'] = $department;
//        $params['division'] = $division;
//
//        return $params;
//    }

    public function addInstitutionalPhiScopeWCMC($user,$creator) {
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:Institution'] by [Institution::class]
        $inst = $this->em->getRepository(Institution::class)->findOneByAbbreviation('WCM');
        $persitesettings = $this->getUserPerSiteSettings($user);
        if( !$persitesettings ) {
            //set institution to per site settings
            $persitesettings = new PerSiteSettings();
            $persitesettings->setAuthor($creator);
            $persitesettings->setUser($user);
            $persitesettings->addPermittedInstitutionalPHIScope($inst);
            ////////// EOF assign Institution //////////
        }
        $persitesettings->addPermittedInstitutionalPHIScope($inst);
        return $persitesettings;
    }

    public function getTooltip($user) {
        $tooltip = true;
        $siteSettings = $this->getUserPerSiteSettings($user);
        if( $siteSettings ) {
            $tooltip = $siteSettings->getTooltip();
        } else {
            //echo 'siteSettings not exists';
            //exit();
        }
        return $tooltip;
    }

//    //Only can verify its own mobile phone number
//    public function canVerifyMobilePhoneNumber() {
//        if( $user->getId() == ) {
//            
//        }
//    }

//    //Run when specialty is added via Site Setting's '2) Populate All Lists with Default Values (Part A)'
//    //Run when add specialty via Platform List Manager's (directory/admin/list-manager/?filter%5Bsearch%5D=specialty):
//    //'Translational Research Project Specialty List, class: [SpecialtyList]' => 'Create a new entry'
//    public function addTransresRolesBySpecialty($specialty) {
//        if( !$specialty ) {
//            return NULL;
//        }
//
//        $user = $this->secToken->getToken()->getUser();
//
//        $rolename = $specialty->getRolename(); //MISI
//        if( !$rolename ) {
//            throw new \Exception('Rolename in the Project Specialty is empty');
//            //exit('Rolename in the Project Specialty is empty');
//        }
//
//        //9 roles (i.e. 'ROLE_TRANSRES_TECHNICIAN_MISI')
//        $transresRoleBases = array(
//            'ROLE_TRANSRES_TECHNICIAN'              => array(
//                'Translational Research [[ROLENAME]] Technician',
//                "View and Edit a Translational Research [[ROLENAME]] Request",
//                50,
//            ),
//
//            'ROLE_TRANSRES_REQUESTER'               => array(
//                'Translational Research [[ROLENAME]] Project Requester',
//                "Submit, View and Edit a Translational Research [[ROLENAME]] Project",
//                30,
//            ),
//
//            'ROLE_TRANSRES_BILLING_ADMIN'           => array(
//                'Translational Research [[ROLENAME]] Billing Administrator',
//                "Create, View, Edit and Send an Invoice for Translational Research [[ROLENAME]] Project",
//                50,
//            ),
//
//            'ROLE_TRANSRES_EXECUTIVE'               => array(
//                'Translational Research [[ROLENAME]] Executive Committee',
//                'Full View Access for [[ROLENAME]] Translational Research site',
//                70
//            ),
//
//            'ROLE_TRANSRES_ADMIN'                   => array(
//                'Translational Research [[ROLENAME]] Admin',
//                'Full Access for Translational Research [[ROLENAME]] site',
//                90
//            ),
//
//            'ROLE_TRANSRES_IRB_REVIEWER'            => array(
//                "Translational Research [[ROLENAME]] IRB Reviewer",
//                "[[ROLENAME]] IRB Review",
//                50,
//            ),
//
//            'ROLE_TRANSRES_COMMITTEE_REVIEWER'      => array(
//                "Translational Research [[ROLENAME]] Committee Reviewer",
//                "[[ROLENAME]] Committee Review",
//                50,
//            ),
//
//            'ROLE_TRANSRES_PRIMARY_COMMITTEE_REVIEWER' => array(
//                "Translational Research [[ROLENAME]] Primary Committee Reviewer",
//                "[[ROLENAME]] Committee Review",
//                50
//            ),
//
//            'ROLE_TRANSRES_PRIMARY_REVIEWER' => array(
//                "Translational Research [[ROLENAME]] Final Reviewer",
//                "Review for all states for [[ROLENAME]]",
//                80
//            ),
//        );
//
//        $sitenameAbbreviation = "translationalresearch"; //"translational-research";
//
//        foreach($transresRoleBases as $transresRoleBase=>$roleInfoArr) {
//
//            $role = $transresRoleBase."_".$rolename; //ROLE_TRANSRES_TECHNICIAN_MISI
//
//            $entity = $this->em->getRepository('AppUserdirectoryBundle:Roles')->findOneByName($role);
//
//            if( $entity ) {
//                continue;
//            }
//
//            $entity = new Roles();
//
//            //$entity, $count, $user, $name=null
//            $count = null;
//            $entity = $this->setDefaultList( $entity, $count, $user, $role );
//            $entity->setType('default');
//
//            $alias = $roleInfoArr[0];
//            $description = $roleInfoArr[1];
//            $level = $roleInfoArr[2];
//
//            if( $alias ) {
//                $alias = str_replace('[[ROLENAME]]',$rolename,$alias);
//            }
//            if( $description ) {
//                $description = str_replace('[[ROLENAME]]',$rolename,$description);
//            }
//
//            $entity->setName( $role );
//            $entity->setAlias( trim((string)$alias) );
//            $entity->setDescription( trim((string)$description) );
//            $entity->setLevel($level);
//
//            //set sitename
//            if( $sitenameAbbreviation ) {
//                $this->addSingleSiteToEntity($entity,$sitenameAbbreviation);
//            }
//
//            $this->em->persist($entity);
//            $this->em->flush();
//
//            $msg = "Added role=[$role]: alias=[$alias], description=[$description] <br>";
//
//            //Flash
//            $this->container->get('session')->getFlashBag()->add(
//                'notice',
//                $msg
//            );
//
//        }//foreach
//
//        //exit("EOF addTransresRoles");
//    }
    public function createNewRole( $roleName, $sitenameAbbreviation=NULL, $alias=NULL, $description=NULL, $level=NULL ) {

        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:Roles'] by [Roles::class]
        $roleObject = $this->em->getRepository(Roles::class)->findOneByName($roleName);
        if( $roleObject ) {
            return NULL;
        }

        $user = NULL;
        if( $this->security ) {
            $user = $this->security->getUser();
        }

        $entity = new Roles();

        //$entity, $count, $user, $name=null
        $count = null;
        $entity = $this->setDefaultList( $entity, $count, $user, $roleName );
        $entity->setType('default');

        $entity->setName( $roleName );

        //$alias - human readable role name
        if( $alias ) {
            $entity->setAlias(trim((string)$alias));
        }

        if( $description ) {
            $entity->setDescription(trim((string)$description));
        }

        if( $level ) {
            $entity->setLevel($level);
        }

        //set sitename
        if( $sitenameAbbreviation ) {
            $this->addSingleSiteToEntity($entity,$sitenameAbbreviation);
        }

        return $entity;
    }
    public function addSingleSiteToEntity( $entity, $siteAbbreviation ) {
        $siteObject = $this->em->getRepository(SiteList::class)->findOneByAbbreviation($siteAbbreviation);
        if( $siteObject ) {
            if( !$entity->getSites()->contains($siteObject) ) {
                $entity->addSite($siteObject);
            }
        }
        return $entity;
    }

    //Test hierarchy roles
    public function roleHierarchyTest() {
        //testing transres ROLE hierarchy
        $user = $this->security->getUser();
        echo "$user: <br><br>";

        $roles = array(

            'ROLE_TRANSRES_TECHNICIAN_APCP',
            'ROLE_TRANSRES_REQUESTER_APCP',
            'ROLE_TRANSRES_IRB_REVIEWER_APCP',
            'ROLE_TRANSRES_COMMITTEE_REVIEWER_APCP',
            'ROLE_TRANSRES_PRIMARY_REVIEWER_APCP',
            'ROLE_TRANSRES_BILLING_ADMIN_APCP',
            'ROLE_TRANSRES_EXECUTIVE_APCP',
            'ROLE_TRANSRES_ADMIN_APCP',

            '',

            'ROLE_TRANSRES_TECHNICIAN_USCAP',
            'ROLE_TRANSRES_REQUESTER_USCAP',
            'ROLE_TRANSRES_IRB_REVIEWER_USCAP',
            'ROLE_TRANSRES_COMMITTEE_REVIEWER_USCAP',
            'ROLE_TRANSRES_PRIMARY_REVIEWER_USCAP',
            'ROLE_TRANSRES_BILLING_ADMIN_USCAP',
            'ROLE_TRANSRES_EXECUTIVE_USCAP',
            'ROLE_TRANSRES_ADMIN_USCAP',
            //'_USCAP',

            '',
            
            'ROLE_TRANSRES_USER',
            'ROLE_TRANSRES_TECHNICIAN',
            'ROLE_TRANSRES_ADMIN',
            'ROLE_TRANSRES_REQUESTER',
            'ROLE_TRANSRES_IRB_REVIEWER',
            'ROLE_TRANSRES_BILLING_ADMIN',
            'ROLE_TRANSRES_EXECUTIVE'

        );

//        $roles = array(
//            'ROLE_TRANSRES_ADMIN',
//        );

//        $roles = array(
//            'ROLE_TRANSRES_TECHNICIAN_USCAP',
//        );

//        $roles = array(
//            'ROLE_TRANSRES_ADMIN_USCAP',
//            'ROLE_TRANSRES_TECHNICIAN_USCAP',
//            'ROLE_TRANSRES_TECHNICIAN_APCP',
//            'ROLE_TRANSRES_TECHNICIAN_HEMATOPATHOLOGY',
//            'ROLE_TRANSRES_TECHNICIAN',
//        );

        foreach($roles as $role) {
            $this->singleRoleHierarchyTest($role);
        }

        //exit("<br>EOF role testing");
    }
    public function singleRoleHierarchyTest($role) {
        if( !$role ) {
            echo "<br>";
            return NULL;
        }
        //testing transres ROLE hierarchy
        if( !$this->security->isGranted($role) ) {
            echo "No $role <br>";
        } else {
            echo "Yes! $role <br>";
        }
        return NULL;
    }

    public function getUploadPath() {
        $projectDir = $this->container->get('kernel')->getProjectDir();
        $uploadPath = $projectDir . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR;
        return $uploadPath;
    }

    public function getSessionParam( $param ) {
        //$userUtil = $this->container->get('user_utility');
        //$session = $userUtil->getSession();
        $session = $this->requestStack->getCurrentRequest()->getSession();
        if( $session && $session->has($param) ) {
            $locale = $session->get($param);
            return $locale;
        }
        return null;
    }
    public function getSessionLocale() {
        $locale = $this->getSessionParam('locale');
        return $locale;
    }

//    //NOT USED
//    public function switchDb()
//    {
//        $connection = $this->em->getConnection();
//        $request = $this->requestStack->getCurrentRequest();
//        //$session = $request->getSession();
//        $uri = $request->getUri();
//
//        if (str_contains($uri, 'c/lmh/pathology')) {
//            $dbName = 'Tenant2';
//        } else {
//            return false;
//        }
//
//        $params = $connection->getParams();
//
//        if ($connection->isConnected()) {
//            $connection->close();
//        }
//
//        $params['dbname'] = $dbName;
//
//        $connection->__construct(
//            $params, $connection->getDriver(), $connection->getConfiguration(),
//            $connection->getEventManager()
//        );
//
//        try {
//            $connection->connect();
//        } catch (Exception $e) {
//            // log and handle exception
//        }
//        return true;
//    }

}