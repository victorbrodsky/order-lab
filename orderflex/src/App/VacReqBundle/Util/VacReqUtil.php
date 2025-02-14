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

namespace App\VacReqBundle\Util;



use App\UserdirectoryBundle\Entity\EmploymentStatus;
use App\VacReqBundle\Entity\VacReqSettings; //process.py script: replaced namespace by ::class: added use line for classname=VacReqSettings


use App\UserdirectoryBundle\Entity\User; //process.py script: replaced namespace by ::class: added use line for classname=User


use App\VacReqBundle\Entity\VacReqApprovalTypeList; //process.py script: replaced namespace by ::class: added use line for classname=VacReqApprovalTypeList


use App\UserdirectoryBundle\Entity\SiteList; //process.py script: replaced namespace by ::class: added use line for classname=SiteList


use App\VacReqBundle\Entity\VacReqRequestTypeList; //process.py script: replaced namespace by ::class: added use line for classname=VacReqRequestTypeList


use App\UserdirectoryBundle\Entity\Institution; //process.py script: replaced namespace by ::class: added use line for classname=Institution


use App\VacReqBundle\Entity\VacReqRequest; //process.py script: replaced namespace by ::class: added use line for classname=VacReqRequest


use App\VacReqBundle\Entity\VacReqFloatingTypeList; //process.py script: replaced namespace by ::class: added use line for classname=VacReqFloatingTypeList
use App\UserdirectoryBundle\Entity\Roles;
use App\VacReqBundle\Entity\VacReqRequestFloating;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\PersistentCollection;
use App\VacReqBundle\Entity\VacReqCarryOver;
use App\VacReqBundle\Entity\VacReqUserCarryOver;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
#use Psr\Container\ContainerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Form\Extension\Core\DataTransformer\DateTimeToStringTransformer;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Csrf\TokenStorage\TokenStorageInterface;
use Symfony\Component\Validator\Constraints\Date;

//use Box\Spout\Common\Type;
//use Box\Spout\Writer\WriterFactory;
//use Box\Spout\Writer\Common\Creator\Style\BorderBuilder;
//use Box\Spout\Common\Entity\Style\Border;
//use Box\Spout\Common\Entity\Style\Color;
//use Box\Spout\Writer\Common\Creator\Style\StyleBuilder;

use Box\Spout\Common\Entity\Style\Border;
use Box\Spout\Common\Entity\Style\Color;
use Box\Spout\Writer\Common\Creator\Style\StyleBuilder;
use Box\Spout\Writer\Common\Creator\Style\BorderBuilder;
use Box\Spout\Writer\Common\Creator\WriterEntityFactory;

/**
 * Created by PhpStorm.
 * User: oli2002 Oleg Ivanov
 * Date: 4/25/2016
 * Time: 11:16 AM
 */
class VacReqUtil
{

    protected $em;
    protected $security;
    //protected $secAuth;
    protected $container;

    private $academicYearStartDateStr = '';
    private $academicYearEndDateStr = '';


    public function __construct( EntityManagerInterface $em, Security $security, ContainerInterface $container ) {

        $this->em = $em;
        $this->security = $security;
        //$this->secAuth = $secAuth;
        $this->container = $container;

    }


    public function getSettingsByInstitution($instid) {
        //process.py script: replaced namespace by ::class: ['AppVacReqBundle:VacReqSettings'] by [VacReqSettings::class]
        $setting = $this->em->getRepository(VacReqSettings::class)->findOneByInstitution($instid);
        return $setting;
    }

    public function getInstitutionSettingArray() {
        //process.py script: replaced namespace by ::class: ['AppVacReqBundle:VacReqSettings'] by [VacReqSettings::class]
        $settings = $this->em->getRepository(VacReqSettings::class)->findAll();

        $arraySettings = array();

        foreach( $settings as $setting ) {
            if( $setting->getInstitution() ) {
                $instid = $setting->getInstitution()->getId();
                $arraySettings[$instid] = $setting;
            }
        }

        return $arraySettings;
    }

    public function showDefaultInformUsers( $institutionId, $asString=false ) {
        //echo '$institutionId='.$institutionId;
        if( !$institutionId ) {
            return NULL;
        }

        //if( is_int($institutionId) ===  false ) {
        //    return NULL;
        //}
        if( filter_var($institutionId, FILTER_VALIDATE_INT) === false ) {
            //echo "Your variable is not an integer";
            return NULL;
        }
        if( $institutionId <= 0 ) {
            return NULL;
        }

        //return array();
        $vacreqSettings = $this->getSettingsByInstitution($institutionId);
        if( $vacreqSettings ) {
            $defaultUsers = $vacreqSettings->getDefaultInformUsers();
            //echo "defaultUsers=".count($defaultUsers)."<br>";
            if( $asString ) {
                $defaultUsersArr = array();
                foreach($defaultUsers as $defaultUser) {
                    $defaultUsersArr[] = $defaultUser."";
                }
                return implode(", ",$defaultUsersArr);
            }
            return $defaultUsers;
        }
        return NULL;
    }

    //get inform and default users
    public function getAllInformUsers( $vacreqRequest ) {
        $informUsers = array();
        //1) get informUsers from $vacreqRequest->getInformUsers();
        foreach( $vacreqRequest->getInformUsers() as $informUser ) {
            if( !in_array($informUser, $informUsers) ) {
                $informUsers[] = $informUser;
            }
        }
        //2) get default inform users
//        $orgInstitution = $vacreqRequest->getInstitution();
//        if( $orgInstitution ) {
//            $vacreqSettings = $this->getSettingsByInstitution($orgInstitution->getId());
//            echo $orgInstitution->getId().": vacreqSettings=$vacreqSettings <br>"; //testing
//            foreach ($vacreqSettings->getDefaultInformUsers() as $defaultInformUser) {
//                if( !in_array($defaultInformUser, $informUsers) ) {
//                    $informUsers[] = $defaultInformUser;
//                }
//            }
//        }
        //2) get default inform users
        $vacreqSettings = $this->getSettingsByVacreq($vacreqRequest);
        if( $vacreqSettings ) {
            foreach ($vacreqSettings->getDefaultInformUsers() as $defaultInformUser) {
                if( !in_array($defaultInformUser, $informUsers) ) {
                    $informUsers[] = $defaultInformUser;
                }
            }
        }
//        else {
//            throw $this->createNotFoundException('Unable to find orgInstitution in vacreq request: '.$vacreqRequest);
//        }
        //echo "count=".count($vacreqRequest->getInformUsers())."<br>";
        //exit('eof setInformUsers');
        return $informUsers;
    }

    public function getSettingsByVacreq( $vacreqRequest ) {
        if( !$vacreqRequest ) {
            return NULL;
        }

        $abbreviation = $vacreqRequest->getRequestTypeAbbreviation();

        if( $abbreviation == "carryover" ) {
            $orgInstitution = $vacreqRequest->getTentativeInstitution();
            if( !$orgInstitution ) {
                $orgInstitution = $vacreqRequest->getInstitution();
            }
            if( $orgInstitution ) {
                return $this->getSettingsByInstitution($orgInstitution->getId());
            }
        }

        if( $abbreviation == "business-vacation" ) {
            $orgInstitution = $vacreqRequest->getInstitution();
            if( !$orgInstitution ) {
                $orgInstitution = $vacreqRequest->getTentativeInstitution();
            }
            if( $orgInstitution ) {
                return $this->getSettingsByInstitution($orgInstitution->getId());
            }
        }

        return NULL;
    }

    public function getVacReqApprovalGroupType( $groupInstitution, $asString=false ) {
        if( $groupInstitution ) {
            $settings = $this->getSettingsByInstitution($groupInstitution->getId());
            if( $settings ) {
                if( $asString ) {
                    $approvalType = $settings->getApprovalType();
                    if( $approvalType ) {
                        return $approvalType->getName();
                    }
                } else {
                    return $settings->getApprovalType();
                }
            }
        }
        return NULL;
    }

    public function settingsAddRemoveUsers( $settings, $userIds ) {
        $originalUsers = $settings->getEmailUsers();

        $newUsers = new ArrayCollection();
        foreach( explode(",",$userIds) as $userId ) {
            //echo "userId=" . $userId . "<br>";
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:User'] by [User::class]
            $emailUser = $this->em->getRepository(User::class)->find($userId);
            if( $emailUser ) {
                $newUsers->add($emailUser);
            }
        }

        if( $originalUsers == $newUsers ) {
            return null;
        }

        $originalUsersNames = array();
        foreach( $originalUsers as $originalUser ) {
            $originalUsersNames[] = $originalUser;
            $settings->removeEmailUser($originalUser);
        }

        $newUsersNames = array();
        foreach( $newUsers as $newUser ) {
            $newUsersNames[] = $newUser;
            $settings->addEmailUser($newUser);
        }

        //$arrayDiff = array_diff($originalUserSiteRoles, $newUserSiteRoles);
        $res = array(
            'originalUsers' => $originalUsersNames,
            'newUsers' => $newUsersNames
        );

        return $res;
    }
    public function settingsAddRemoveApprovalTypes( $settingsEntity, $approvaltypeid ) {
        //return true;
        //$institution = $settingsEntity->getInstitution();
        $originalApprovalTypes = $settingsEntity->getApprovalTypes();

        $originalApprovalType = NULL;
        $newApprovalType = NULL;

        if( count($originalApprovalTypes) > 0 ) {
            $originalApprovalType = $originalApprovalTypes[0];
        }

//        $res = array(
//            'originalApprovalType' => $originalApprovalType,
//            'newApprovalType' => NULL
//        );
        
        if( $approvaltypeid ) {
        //process.py script: replaced namespace by ::class: ['AppVacReqBundle:VacReqApprovalTypeList'] by [VacReqApprovalTypeList::class]
            $newApprovalType = $this->em->getRepository(VacReqApprovalTypeList::class)->find($approvaltypeid);
        }

        $originalApprovalTypeId = NULL;
        $newApprovalTypeId = NULL;
        if( $originalApprovalType ) {
            $originalApprovalTypeId = $originalApprovalType->getId();
        }
        if( $newApprovalType ) {
            $newApprovalTypeId = $newApprovalType->getId();
        }

        if( $originalApprovalTypeId == $newApprovalTypeId ) {
            //the same => not updated
            return NULL;
//            //not updated
//            $res = array(
//                'originalApprovalType' => $originalApprovalType,
//                'newApprovalType' => $newApprovalType
//            );
//            return $res;
        } else {
            //ok updated
        }

        //always remove original approval type
        $settingsEntity->removeApprovalType($originalApprovalType);
        //add original approval type
        $settingsEntity->addApprovalType($newApprovalType);

        //$arrayDiff = array_diff($originalUserSiteRoles, $newUserSiteRoles);
        $res = array(
            'originalApprovalType' => $originalApprovalType,
            'newApprovalType' => $newApprovalType
        );

        return $res;
    }
    public function settingsAddRemoveDefaultInformUsers( $settings, $userIds ) {
        $originalUsers = $settings->getDefaultInformUsers();

        $newUsers = new ArrayCollection();
        foreach( explode(",",$userIds) as $userId ) {
            //echo "userId=" . $userId . "<br>";
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:User'] by [User::class]
            $defaultInformUser = $this->em->getRepository(User::class)->find($userId);
            if( $defaultInformUser ) {
                $newUsers->add($defaultInformUser);
            }
        }

        if( $originalUsers == $newUsers ) {
            return null;
        }

        $originalUsersNames = array();
        foreach( $originalUsers as $originalUser ) {
            $originalUsersNames[] = $originalUser;
            $settings->removeDefaultInformUser($originalUser);
        }

        $newUsersNames = array();
        foreach( $newUsers as $newUser ) {
            $newUsersNames[] = $newUser;
            $settings->addDefaultInformUser($newUser);
        }

        //$arrayDiff = array_diff($originalUserSiteRoles, $newUserSiteRoles);
        $res = array(
            'originalUsers' => $originalUsersNames,
            'newUsers' => $newUsersNames
        );

        return $res;
    }
    public function settingsAddRemoveProxySubmitterUsers( $settings, $userIds ) {
        $originalUsers = $settings->getProxySubmitterUsers();

        $newUsers = new ArrayCollection();
        foreach( explode(",",$userIds) as $userId ) {
            //echo "userId=" . $userId . "<br>";
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:User'] by [User::class]
            $proxySubmitterUser = $this->em->getRepository(User::class)->find($userId);
            if( $proxySubmitterUser ) {
                $newUsers->add($proxySubmitterUser);
            }
        }

        if( $originalUsers == $newUsers ) {
            return null;
        }

        $originalUsersNames = array();
        foreach( $originalUsers as $originalUser ) {
            $originalUsersNames[] = $originalUser;
            $settings->removeProxySubmitterUser($originalUser);
        }

        $newUsersNames = array();
        foreach( $newUsers as $newUser ) {
            $newUsersNames[] = $newUser;
            $settings->addProxySubmitterUser($newUser);
        }

        //$arrayDiff = array_diff($originalUserSiteRoles, $newUserSiteRoles);
        $res = array(
            'originalUsers' => $originalUsersNames,
            'newUsers' => $newUsersNames
        );

        return $res;
    }

    //find role approvers by institution
    public function getRequestApprovers( $entity, $institutionType="institution", $forceApproverRole=null, $onlyWorking=false ) {

        $institution = $entity->getInstitution();

//        if( $institutionType == "institution" ) {
//            $institution = $entity->getInstitution();
//            //echo "institution <br>";
//        }
//        if( $institutionType == "tentativeInstitution" ) {
//            $institution = $entity->getTentativeInstitution();
//            //echo "tentativeInstitution <br>";
//        }

        if( $entity->getRequestTypeAbbreviation() == "carryover" ) {
            if( !$institution ) {
                $institution = $entity->getTentativeInstitution();
            }
        }

        //echo "institution=$institution <br>";
        //exit('111');
        if( !$institution ) {
            return array();
        }

        //echo "VacReq Request ID=".$entity->getId()."<br>";
        //echo "<br>institution=".$institution."<br>";
        //echo "tentative institution=".$entity->getTentativeInstitution()."<br>";

        if( $entity->getRequestTypeAbbreviation() == "carryover" ) {

            $tentativeInstitution = $entity->getTentativeInstitution();
            //echo "2 tentative institution=".$tentativeInstitution."<br>";

            //echo "getTentativeStatus=".$entity->getTentativeStatus()."<br>";
            if( $tentativeInstitution && $entity->getTentativeStatus() == 'pending' ) {
                $approverRole = "ROLE_VACREQ_APPROVER";
                $institution = $tentativeInstitution;
            } else {
                $approverRole = "ROLE_VACREQ_SUPERVISOR";
            }

            //specifically asked for tentative approvers
            if( $tentativeInstitution && $institutionType == "tentativeInstitution" ) {
                $approverRole = "ROLE_VACREQ_APPROVER";
                $institution = $tentativeInstitution;
            }

        } else {
            $approverRole = "ROLE_VACREQ_APPROVER";
        }

        if( $forceApproverRole ) {
            $approverRole = $forceApproverRole;
        }

        //echo "approverRole=".$approverRole."<br>";
        //echo "institution=".$institution."<br>";

        $approvers = array();

        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:User'] by [User::class]
        $roleApprovers = $this->em->getRepository(User::class)->
            findRolesBySiteAndPartialRoleName( "vacreq", $approverRole, $institution->getId());
        //echo "roleApprovers count=".count($roleApprovers)."<br>";

        $roleApprover = null;
        if( count($roleApprovers) > 0 ) {
            $roleApprover = $roleApprovers[0];
        }

        if( $roleApprover ) {
            //echo "roleApprover=".$roleApprover."<br>";
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:User'] by [User::class]
            $approvers = $this->em->getRepository(User::class)->findUserByRole($roleApprover->getName(),"infos.lastName",$onlyWorking);
        }

//        if( $entity->getRequestTypeAbbreviation() == "carryover" ) {
//            //if role not found for carry-over request=> try tentative generic, minimum approver role
//            if( count($approvers) == 0 ) {
//                                  //getRequestApprovers( $entity, $institutionType="institution", $forceApproverRole=null, $onlyWorking=false )
//                $approvers = $this->getRequestApprovers($entity,"tentativeInstitution","ROLE_VACREQ_APPROVER",$onlyWorking);
//            }
//        }

        return $approvers;
    }

    //$groupId - group (institution) ID
    //$rolePartialName - "ROLE_VACREQ_SUBMITTER", "ROLE_VACREQ_APPROVER", "ROLE_VACREQ_SUPERVISOR"
    public function getUsersByGroupId( $groupId, $rolePartialName="ROLE_VACREQ_SUBMITTER", $onlyWorking=false ) {
        $users = array();
        
        if( !$groupId || filter_var($groupId, FILTER_VALIDATE_INT) === false  ) { //|| is_int($groupId) === false
            $users = $this->em->getRepository(User::class)->findUserByRole($rolePartialName,"infos.lastName",$onlyWorking);
            //echo "0user count=".count($users)."<br>";
            //exit('111');
            return $users;
        }

        $roles = $this->em->getRepository(User::class)->
                            findRolesBySiteAndPartialRoleName( "vacreq", $rolePartialName, $groupId);

        if( count($roles) == 0 ) {
            return array();
        }

//        foreach($roles as $role){
//            echo "role=".$role."<br>";
//        }

        $role = $roles[0];

        //echo "role=".$role."<br>";
        if( $role ) {
            $users = $this->em->getRepository(User::class)->findUserByRole($role->getName(),"infos.lastName",$onlyWorking);
        }

        return $users;
    }
    public function getUsersByGroupSelect( $groupId, $rolePartialName="ROLE_VACREQ_SUBMITTER", $onlyWorking=false ) {
        $users = $this->getUsersByGroupId($groupId,$rolePartialName,$onlyWorking);
        $resArr = array();
        foreach($users as $user){
            //$resArr[$user.""] = $user->getId();
            $resArr[] = array(
                'id' => $user->getId(),
                'text' => $user.""
            );
        }
        return $resArr;
    }

    //Create new role for org institution if role does not exists
    public function checkAndCreateVacReqRoles( $institution, $request=null ) {
        $testing = false;
        //$testing = true;

        $user = $this->security->getUser();
        $userSecUtil = $this->container->get('user_security_utility');
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:SiteList'] by [SiteList::class]
        $site = $this->em->getRepository(SiteList::class)->findOneByAbbreviation('vacreq');

        $count = 0;
        $addedRoles = array();

        //get ROLE NAME: Pathology Informatics => PATHOLOGYINFORMATCS
        $roleNameBase = str_replace(" ","",$institution->getName());
        $roleNameBase = strtoupper($roleNameBase);

        if( $institution->getName() == "Executive Committee" ) {
            $roleNameBase = "EXECUTIVE";
        }
        if( $institution->getName() == "Laboratory Medicine" ) {
            $roleNameBase = "CLINICALPATHOLOGY";
        }
        if( $institution->getName() == "Cell and Cancer Pathobiology" ) {
            $roleNameBase = "EXPERIMENTALPATHOLOGY";
        }
        if( $institution->getName() == "Anatomic Pathology" ) {
            $roleNameBase = "SURGICALPATHOLOGY";
        }

        //create approver role
        $roleName = "ROLE_VACREQ_APPROVER_".$roleNameBase;
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:Roles'] by [Roles::class]
        $approverRole = $this->em->getRepository(Roles::class)->findOneByName($roleName);
        if( !$approverRole ) {
            $approverRole = new Roles();
            $approverRole = $userSecUtil->setDefaultList($approverRole, null, $user, $roleName);
            $approverRole->setLevel(50);
            $approverRole->setAlias('Vacation Request Approver for the ' . $institution->getName());
            $approverRole->setDescription('Can search and approve vacation requests for specified service');
            $approverRole->addSite($site);
            $approverRole->setInstitution($institution);
            $userSecUtil->checkAndAddPermissionToRole($approverRole, "Approve a Vacation Request", "VacReqRequest", "changestatus");

            if( $testing === false ) {
                $this->em->persist($approverRole);
                $this->em->flush();
            }

            $addedRoles[] = $roleName;
            $count++;
        } else {
            $approverType = $approverRole->getType();
            if( $approverType != 'default' && $approverType != 'user-added' ) {
                $approverRole->setType('default');
                if( $testing === false ) {
                    $this->em->persist($approverRole);
                    $this->em->flush();
                }
                $count++;
            }
        }

        //create submitter role
        $roleName = "ROLE_VACREQ_SUBMITTER_".$roleNameBase;
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:Roles'] by [Roles::class]
        $submitterRole = $this->em->getRepository(Roles::class)->findOneByName($roleName);
        if( !$submitterRole ) {
            $submitterRole = new Roles();
            $submitterRole = $userSecUtil->setDefaultList($submitterRole, null, $user, $roleName);
            $submitterRole->setLevel(30);
            $submitterRole->setAlias('Vacation Request Submitter for the ' . $institution->getName());
            $submitterRole->setDescription('Can search and create vacation requests for specified service');
            $submitterRole->addSite($site);
            $submitterRole->setInstitution($institution);
            $userSecUtil->checkAndAddPermissionToRole($submitterRole, "Submit a Vacation Request", "VacReqRequest", "create");

            if( $testing === false ) {
                $this->em->persist($submitterRole);
                $this->em->flush();
            }

            $addedRoles[] = $roleName;
            $count++;
        } else {
            $submitterType = $submitterRole->getType();
            if( $submitterType != 'default' && $submitterType != 'user-added' ) {
                $submitterRole->setType('default');
                if( $testing === false ) {
                    $this->em->persist($submitterRole);
                    $this->em->flush();
                }
                $count++;
            }
        }

        //create submitter role
        $roleName = "ROLE_VACREQ_PROXYSUBMITTER_".$roleNameBase;
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:Roles'] by [Roles::class]
        $submitterRole = $this->em->getRepository(Roles::class)->findOneByName($roleName);
        if( !$submitterRole ) {
            $submitterRole = new Roles();
            $submitterRole = $userSecUtil->setDefaultList($submitterRole, null, $user, $roleName);
            $submitterRole->setLevel(35);
            $submitterRole->setAlias('Vacation Request Proxy Submitter for the ' . $institution->getName());
            $submitterRole->setDescription('Can search and create vacation requests for specified service on behalf of another person');
            $submitterRole->addSite($site);
            $submitterRole->setInstitution($institution);
            $userSecUtil->checkAndAddPermissionToRole($submitterRole, "Submit a Vacation Request", "VacReqRequest", "create");

            if( $testing === false ) {
                $this->em->persist($submitterRole);
                $this->em->flush();
            }

            $addedRoles[] = $roleName;
            $count++;
        } else {
            $submitterType = $submitterRole->getType();
            if( $submitterType != 'default' && $submitterType != 'user-added' ) {
                $submitterRole->setType('default');
                if( $testing === false ) {
                    $this->em->persist($submitterRole);
                    $this->em->flush();
                }
                $count++;
            }
        }

        //echo "count=$count <br>";
        if( $count > 0 ) {
            //Event Log
            //$event = "New Business/Vacation Group " . $roleNameBase . " has been created for " . $institution->getName();
            $event = "New roles ".implode(", ",$addedRoles)." for Business/Vacation Group ".$institution->getName()." have been created.";
            $userSecUtil = $this->container->get('user_security_utility');

            if( $testing === false ) {
                $userSecUtil->createUserEditEvent($this->container->getParameter('vacreq.sitename'), $event, $user, $institution, $request, 'Business/Vacation Group Created');
            }

            //Flash
            //echo "event=$event <br>";
            if( $request ) {
                $session = $request->getSession();
                if ($session) {
                    $session->getFlashBag()->add(
                        'notice',
                        $event
                    );
                }
            }
        }

        return $addedRoles;
    }


    //set confirmation email to approver and email users
    public function sendConfirmationEmailToApprovers( $entity, $sendCopy=true ) {
        $subject = $entity->getEmailSubject();
        $message = $this->createEmailBody($entity);
        return $this->sendGeneralEmailToApproversAndEmailUsers($entity,$subject,$message,$sendCopy);
    }
    public function createEmailBody( $entity, $emailToUser=null, $addText=null, $withLinks=true ) {

        //$break = "\r\n";
        $break = "<br>";

        $submitter = $entity->getUser();

        //$message = "Dear " . $emailToUser->getUsernameOptimal() . "," . $break.$break;
        $message = "Dear ###emailuser###," . $break.$break;

        $requestName = $entity->getRequestName();

        $message .= $submitter->getUsernameOptimal()." has submitted the ".$requestName." ID #".$entity->getId()." and it is ready for review.";
        $message .= $break.$break.$entity->printRequest($this->container)."";

        // IF (and only if) the automatically calculated quantity of total days away was changed by the submitter to a different value.
        $vacreqCalendarUtil = $this->container->get('vacreq_calendar_util');
        $daysDifferenceNote = $vacreqCalendarUtil->getDaysDifferenceNote($entity);
        if( $daysDifferenceNote ) {
            $message = $message . $break . $daysDifferenceNote . $break;
        }

        $reviewRequestUrl = $this->container->get('router')->generate(
            'vacreq_review',
            array(
                'id' => $entity->getId()
            ),
            UrlGeneratorInterface::ABSOLUTE_URL
        );
        $message .= $break . $break . "Please follow the link below to review this ".$requestName." ID #".$entity->getId().":" . $break;
        $message .= $reviewRequestUrl . $break . $break;

        //$message .= $break . "Please click on the URLs below for quick actions to approve or reject ".$requestName." ID #".$entity->getId().".";

        if( $entity->hasBusinessRequest() ) {
            //href="{{ path(vacreq_sitename~'_status_email_change', { 'id': entity.id,  'requestName':requestName, 'status': 'approved' }) }}
            //approved
            $actionRequestUrl = $this->container->get('router')->generate(
                'vacreq_status_email_change',
                array(
                    'id' => $entity->getId(),
                    'requestName' => 'business',
                    'status' => 'approved'
                ),
                UrlGeneratorInterface::ABSOLUTE_URL
            );
            $message .= $break . $break . "Please follow the link below to Approve the ".$requestName." ID #".$entity->getId().":" . $break;
            $message .= $actionRequestUrl;

            //rejected
            $actionRequestUrl = $this->container->get('router')->generate(
                'vacreq_status_email_change',
                array(
                    'id' => $entity->getId(),
                    'requestName' => 'business',
                    'status' => 'rejected'
                ),
                UrlGeneratorInterface::ABSOLUTE_URL
            );
            $message .= $break . $break . "Please follow the link below to Reject the ".$requestName." ID #".$entity->getId().":" . $break;
            $message .= $actionRequestUrl;
        }

        if( $entity->hasVacationRequest() ) {
            //href="{{ path(vacreq_sitename~'_status_email_change', { 'id': entity.id,  'requestName':requestName, 'status': 'approved' }) }}
            //approved
            $actionRequestUrl = $this->container->get('router')->generate(
                'vacreq_status_email_change',
                array(
                    'id' => $entity->getId(),
                    'requestName' => 'vacation',
                    'status' => 'approved'
                ),
                UrlGeneratorInterface::ABSOLUTE_URL
            );
            $message .= $break . $break . "Please follow the link below to Approve the ".$requestName." ID #".$entity->getId().":" . $break;
            $message .= $actionRequestUrl;

            //rejected
            $actionRequestUrl = $this->container->get('router')->generate(
                'vacreq_status_email_change',
                array(
                    'id' => $entity->getId(),
                    'requestName' => 'vacation',
                    'status' => 'rejected'
                ),
                UrlGeneratorInterface::ABSOLUTE_URL
            );
            $message .= $break . $break . "Please follow the link below to Reject the ".$requestName." ID #".$entity->getId().":" . $break;
            $message .= $actionRequestUrl;
        }

        //CARRYOVER body
        if( $entity->getRequestTypeAbbreviation() == "carryover" ) {

            $yearRange = $this->getCurrentAcademicYearRange();

            //vacation
            $resVacationDays = $this->getApprovedTotalDays($entity->getUser(),"vacation");
            $approvedVacationDays = $resVacationDays['numberOfDays'];
            if( !$resVacationDays['accurate'] ) {
                $approvedVacationDays .= " (".$this->getInaccuracyMessage().")";
            }

            //business
//            $resBusinessDays = $this->getApprovedTotalDays($entity->getUser(),"business");
//            $approvedBusinessDays = $resBusinessDays['numberOfDays'];
//            $accurateBusiness = $resBusinessDays['accurate'];
//            if( !$accurateBusiness ) {
//                $approvedBusinessDays .= " (".$this->getInaccuracyMessage().")";
//            }

            //FirstName LastName requested carry over of X vacation days from [Source Academic Year] to [Destination Academic Year].
            //As of [date of request submission], FirstName LastName has accrued Y days in the current [current academic year as 2015-2016] academic year,
            // had Z days carried over from [current academic year -1] to [current academic year],
            // and has been approved for M vacation days and N business travel days during [current academic year as 2015-2016] so far.

            //Dear ...
            $message = "Dear ###emailuser###," . $break.$break;

            //FirstName LastName requested carry over of X vacation days from [Source Academic Year] to [Destination Academic Year].
            $message .= $entity->getEmailSubject().".";

            //comment
            if( $entity->getComment() ) {
                $message .= $break . "Comment: " . $entity->getComment();
            }

            $message .= $break.$break;

//            //As of [date of request submission], FirstName LastName has accrued Y days in the current [current academic year as 2015-2016] academic year,
//            $message .= "As of ".$entity->getCreateDate()->format("F jS Y").", ".$entity->getUser()->getUsernameOptimal()." has accrued ".
//                $accruedDays." days in the current ".$yearRange." academic year,";
//            //had Z days carried over from [current academic year -1] to [current academic year],
//            $message .= " had ".$carriedOverDays." days carried over from ".$previousYear." to ".$currentYear.",";
//            //and has been approved for M vacation days and N business travel days during [current academic year as 2015-2016] so far.
//            $message .= " and has been approved for ".$approvedVacationDays." days and ".$approvedBusinessDays.
//                " business travel days during ".$yearRange." so far.";

            ///// Add warning message if carry over days > remainingDays /////
            $warningCarryOverMsg = "";
            $carryOverDays = $entity->getCarryOverDays();
            $remainingDaysRes = $this->totalVacationRemainingDays($submitter);
            $remainingDays = $remainingDaysRes['numberOfDays'];
            if( $carryOverDays > $remainingDays ) {
                //$remainingDaysString .= " (" . $this->getInaccuracyMessage() . ")";
                // According to system records FirstName LastName had Y remaining vacation days
                // (Z less than the requested X carry-over days) during request
                // submission at HH:MM on MM/DD/YYYY.
                // To review the summary statistics, please visit:
                // https://LINK
                //VacReqApprovalTypeList
                $approverTypes = $this->getApprovalGroupTypes();
                $summaryStatLinkArr = array();
                $summaryStatLinkArr['filter[users][0]'] = $submitter->getId();
                $filterTypesCount = 0;
                foreach($approverTypes as $approverType) {
                    $summaryStatLinkArr['filter[types]['.$filterTypesCount.']'] = $approverType->getId();
                    $filterTypesCount++;
                }
                $summaryStatLink = $this->container->get('router')->generate(
                    'vacreq_summary',
                    $summaryStatLinkArr,
//                    array(
//                        'filter[users][0]' => $submitter->getId(),
//                        'filter[types][0]' => '',
//                        'filter[types][1]' => ''
//                    ),
                    UrlGeneratorInterface::ABSOLUTE_URL
                );
                //$href = '<a href="'.$summaryStatLink.'">Summary Stats'</a>';

                $lessDays = (int) $carryOverDays - (int) $remainingDays;
                //$submissionDateStr = $entity->getCreateDate()->format("M d Y h:i A T");
                $creationDate = $entity->getCreateDate();
                $creationDate->setTimezone(new \DateTimeZone('America/New_York'));
                $submissionDateStr = $creationDate->format("M d Y h:i A");
                $warningCarryOverMsg = "According to system records " . $submitter->getUsernameOptimal().
                    " had ".$remainingDays." remaining vacation days (".$lessDays.
                    " less than the requested ".$carryOverDays." carry-over days) ".
                    "during request submission on ".$submissionDateStr.".".$break.
                    "To review the summary statistics, please visit:".$break.
                    $summaryStatLink
                ;
                $message = $message . $warningCarryOverMsg . $break.$break;
            }


            //subject + SubmitterFirstName SubmitterLastName has M approved vacation days during [CURRENT 20XX-20YY] year.
            $message .= $entity->getUser()->getUsernameOptimal()." has ".$approvedVacationDays." approved vacation days during ".$yearRange." year.";

            if( $withLinks ) {
                $prefix = " ";
                if ($entity->getTentativeStatus() == 'pending') {
                    $prefix = " tentatively ";
                }

                if ($entity->getTentativeStatus() == 'approved' && $entity->getTentativeApprovedRejectDate() && $entity->getTentativeApprover()) {
                    //This request has been tentatively approved by [VacationApproverFirstName, VacationApproverLastName] on
                    // DateOfStatusChange at TimeOfStatusChange.
                    $tentativeApprovedRejectDate = $entity->getTentativeApprovedRejectDate()->setTimezone(new \DateTimeZone('America/New_York'));
                    $message .= $break . $break . "This request has been tentatively approved by " . $entity->getTentativeApprover() .
                        " on " . $tentativeApprovedRejectDate->format("M d Y h:i A T") . ".";
                    $prefix = " final ";
                }

                $actionRequestApproveUrl = $this->container->get('router')->generate(
                    'vacreq_status_email_change_carryover',
                    array(
                        'id' => $entity->getId(),
                        'requestName' => 'entire',
                        'status' => 'approved'
                    ),
                    UrlGeneratorInterface::ABSOLUTE_URL
                );
                $message .= $break . $break . "To" . $prefix . "approve this request, please follow this link:" . $break;
                $message .= $actionRequestApproveUrl;

                //rejected
                $actionRequestRejectUrl = $this->container->get('router')->generate(
                    'vacreq_status_email_change_carryover',
                    array(
                        'id' => $entity->getId(),
                        'requestName' => 'entire',
                        'status' => 'rejected'
                    ),
                    UrlGeneratorInterface::ABSOLUTE_URL
                );
                $message .= $break . $break . "To deny this request, please follow this link:" . $break;
                $message .= $actionRequestRejectUrl;

                //To review SubmitterFirstName SubmitterLastName's past requests, please follow this link:
                //[link to incoming requests filtered by person away = submitter]
                $reviewUrl = $this->container->get('router')->generate(
                    'vacreq_incomingrequests',
                    array(
                        'filter[requestType]' => $entity->getRequestType()->getId(),
                        'filter[user]' => $submitter->getId()
                    ),
                    UrlGeneratorInterface::ABSOLUTE_URL
                );
                $message .= $break . $break . "To review " . $submitter->getUsernameShortest() . "'s past requests, please follow this link:" . $break;
                $message .= $reviewUrl;
            }//$withLinks
        }//if carryover

        $message .= $break.$break."To approve or reject requests, Division Approvers must be on site or using vpn when off site.";
        $message .= $break.$break."**** PLEASE DO NOT REPLY TO THIS EMAIL ****";

        if( $addText ) {
            $message = $addText.$break.$break.$message;
        }

        return $message;
    }

    //set respond confirmation email to a submitter and email users
    public function sendSingleRespondEmailToSubmitter( $entity, $approver, $status, $message=null ) {

        $logger = $this->container->get('logger');
        $emailUtil = $this->container->get('user_mailer_utility');
        //$break = "\r\n";
        $break = "<br>";

        $institution = $entity->getInstitution();

        if( $entity->getRequestTypeAbbreviation() == "carryover" ) {
            if( !$institution ) {
                $institution = $entity->getTentativeInstitution();
            }
        }

        if( !$institution ) {
            return null;
        }

        $requestName = $entity->getRequestName();

        //$subject = "Respond Confirmation for ".$requestName." ID #".$entity->getId();
        $subject = $entity->getRequestSubject();

        $submitter = $entity->getUser();

        if( !$message ) {
            $message = "Dear " . $submitter->getUsernameOptimal() . "," . $break . $break;

//            $message .= "Your " . $requestName . " ID #" . $entity->getId();
//            if ($status == 'pending') {
//                $status = 'set to Pending';
//            }
//            $message .= " has been " . $status . " by " . $approver->getUsernameOptimal() . ":" . $break;
//            $message .= $entity->getDetailedStatus().".".$break.$break;

            $message .= "Your " . $entity->getRequestMessageHeader() . " by " . $approver->getUsernameOptimal() . ":" . $break.$break;

            $message .= $entity->printRequest($this->container)."".$break.$break;

            // IF (and only if) the automatically calculated quantity of total days away was changed by the submitter to a different value.
            $vacreqCalendarUtil = $this->container->get('vacreq_calendar_util');
            $daysDifferenceNote = $vacreqCalendarUtil->getDaysDifferenceNote($entity);
            if( $daysDifferenceNote ) {
                $message = $message . " " . $daysDifferenceNote . $break.$break;
            }

            $message .= "**** PLEASE DO NOT REPLY TO THIS EMAIL ****";
        }

        $emailUtil->sendEmail( $submitter->getSingleEmail(), $subject, $message, null, null );
        $logger->notice("sendSingleRespondEmailToSubmitter: sent confirmation email to submitter ".$submitter->getSingleEmail());

        //css to email users
        //$approversNameArr = array();
        $cssArr = array();
        $settings = $this->getSettingsByInstitution($institution->getId());
        if( $settings ) {
            foreach ($settings->getEmailUsers() as $emailUser) {
                $emailUserEmail = $emailUser->getSingleEmail();
                if( $emailUserEmail ) {
                    $cssArr[] = $emailUserEmail;
                    //$approversNameArr[] = $emailUser."";
                }
            }
        }

//        //add approvers to css
//        if( 0 ) { //don't send a copy of the confirmation email to approvers
//            $approvers = $this->getRequestApprovers($entity);
//            foreach ($approvers as $approver) {
//                $approverSingleEmail = $approver->getSingleEmail();
//                if ($approverSingleEmail) {
//                    $cssArr[] = $approverSingleEmail;
//                    //$approversNameArr[] = $approver . "";
//                }
//            } //foreach approver
//        }

        //$emailUtil->sendEmail( $submitter->getSingleEmail(), $subject, $message, $cssArr, null );

        if( count($cssArr) > 0 ) {
            $subject = "Copy of the email: " . $subject;
            $addText = "### This is a copy of the email sent to the submitter " . $submitter . "###";
            $message = $addText . $break . $break . $message;
            $emailUtil->sendEmail($cssArr, $subject, $message, null, null);

            $logger->notice("sendSingleRespondEmailToSubmitter: sent confirmation email to all related users " . implode("; ", $cssArr));
        }
    }

    //totalAllocatedDays - vacationDays + carryOverDays for given $yearRange
    //carryOver days are from getUserCarryOverDays
    //TODO: might add "date of hire" and "end of employment date" to calculate the total vacation days
    public function totalVacationRemainingDays(
        $user,
        $totalAllocatedDays=null,
        $vacationDays=null,
        $carryOverDaysToNextYear=null,
        $carryOverDaysFromPreviousYear=null,
        $yearRange=null
    ) {

        if( !$yearRange ) {
            $yearRange = $this->getCurrentAcademicYearRange();
        }

        //echo "1totalAllocatedDays=$totalAllocatedDays <br>";
        if( !$totalAllocatedDays ) {
            $totalAllocatedDays = $this->getTotalAccruedDays($user,$yearRange); //$yearRange year
            //$totalAllocatedDays = $this->getTotalAccruedDaysByGroup($approvalGroupType);
        }
        //$totalAllocatedDays = 20;
        //echo "2totalAllocatedDays=$totalAllocatedDays <br>";

        $vacationAccurate = true;
        if( !$vacationDays ) {
            $vacationDaysRes = $this->getApprovedTotalDaysAcademicYear($user,'vacation',$yearRange);
            $vacationDays = $vacationDaysRes['numberOfDays'];
            $vacationAccurate = $vacationDaysRes['accurate'];
        }

        if( !$carryOverDaysFromPreviousYear ) {
            //carried over days from previous year
            $carryOverDaysFromPreviousYear = $this->getUserCarryOverDays($user,$yearRange);
        }

        if( !$carryOverDaysToNextYear ) {
            //subtract carried over days from the current year to the next year.
            $nextYearRange = $this->getNextAcademicYearRange();
            //echo "nextYearRange=".$nextYearRange."<br>";
            $carryOverDaysToNextYear = $this->getUserCarryOverDays($user, $nextYearRange);
            //echo "carryOverDaysToNextYear=".$carryOverDaysToNextYear."<br>";
        }

        //echo "yearRange=".$yearRange."<br>";
        //echo "totalAllocatedDays=".$totalAllocatedDays."<br>";
        //echo "vacationDays=".$vacationDays."<br>";
        //echo "carryOverDaysFromPreviousYear=".$carryOverDaysFromPreviousYear."<br>";
        //echo "carryOverDaysToNextYear=".$carryOverDaysToNextYear."<br>";

        $res = array(
            'numberOfDays' => ( (int)$totalAllocatedDays - (int)$vacationDays + (int)$carryOverDaysFromPreviousYear ) - (int)$carryOverDaysToNextYear,
            'accurate' => $vacationAccurate
        );

        //dump($res);
        //exit('111');
        
        return $res;
    }

    //"During the current academic year, you have received X approved vacation days in total."
    // (if X = 1, show "During the current academic year, you have received X approved vacation day."
    // if X = 0, show "During the current academic year, you have received no approved vacation days."
    public function getApprovedDaysString( $user ) { //$bruteForce=false

        //process.py script: replaced namespace by ::class: ['AppVacReqBundle:VacReqRequestTypeList'] by [VacReqRequestTypeList::class]
        $requestType = $this->em->getRepository(VacReqRequestTypeList::class)->findOneByAbbreviation("business-vacation");

        $yearRange = $this->getCurrentAcademicYearRange();

        //testing
//        $testingDates = $this->getCurrentAcademicYearStartEndDates();
//        echo "<pre>";
//        print_r($testingDates);
//        echo "</pre>";
//        echo "yearRange=".$yearRange."<br>";
//        exit('1');

        $result = "During the current ".$yearRange." academic year, you have received ";

        $yearRangeArr = explode("-",$yearRange);
        $academicYear = $yearRangeArr[0];

        //////////////////////// Business /////////////////////
        $requestTypeStr = 'business';
        $res = $this->getApprovedTotalDays($user,$requestTypeStr); //$bruteForce
        $numberOfDays = $res['numberOfDays'];
        $accurate = $res['accurate'];

        if( !$numberOfDays || $numberOfDays == 0 ) {
            $businessDaysUrlText = "no approved ".$requestTypeStr." travel days";
        }
        if( $numberOfDays == 1 ) {
            $businessDaysUrlText = $numberOfDays." approved ".$requestTypeStr." travel day";
        }
        if( $numberOfDays > 1 ) {
            $businessDaysUrlText = $numberOfDays." approved ".$requestTypeStr." travel days";
        }

        //convert "X approved business travel days" to link
        $businessDaysUrl = $this->container->get('router')->generate(
            'vacreq_myrequests',
            array(
                'filter[requestType]' => $requestType->getId(),
                'filter[businessRequest]' => 1,
                'filter[approved]' => 1,
                'filter[academicYear]' => $academicYear
            )
        );
        $result .= '<a href="'.$businessDaysUrl.'">'.$businessDaysUrlText.'</a>';

        if( $numberOfDays > 1 ) {
            $result .= " in total";
        }
        if( !$accurate ) {
            $result .= " (".$this->getInaccuracyMessage().")";
        }
        //////////////////////// Eof Business /////////////////////

        $result .= " and ";

        //////////////////////// Vacation /////////////////////
        $requestTypeStr = 'vacation';
        $res = $this->getApprovedTotalDays($user,$requestTypeStr); //$bruteForce
        $numberOfDays = $res['numberOfDays'];
        $accurate = $res['accurate'];
        if( !$numberOfDays || $numberOfDays == 0 ) {
            $vacationDaysUrlText = "no approved ".$requestTypeStr." days";
        }
        if( $numberOfDays == 1 ) {
            $vacationDaysUrlText = $numberOfDays." approved ".$requestTypeStr." day";
        }
        if( $numberOfDays > 1 ) {
            $vacationDaysUrlText = $numberOfDays." approved ".$requestTypeStr." days";
        }

        //convert "X approved vacation days" to link
        $vacationDaysUrl = $this->container->get('router')->generate(
            'vacreq_myrequests',
            array(
                'filter[requestType]' => $requestType->getId(),
                'filter[vacationRequest]' => 1,
                'filter[approved]' => 1,
                'filter[academicYear]' => $academicYear
            )
        );
        $result .= '<a href="'.$vacationDaysUrl.'">'.$vacationDaysUrlText.'</a>';

        if( $numberOfDays > 1 ) {
            $result .= " in total";
        }
        if( !$accurate ) {
            $result .= " (".$this->getInaccuracyMessage().")";
        }
        //////////////////////// EOF Vacation /////////////////////

        $result .= ".";

        //if your requests included holidays, they are not automatically removed from these counts
        //If the number of days in the current academic year for both vacation and business travel = 0, this sentence should not be shown.
        if( $numberOfDays > 0 ) {

            $userSecUtil = $this->container->get('user_security_utility');
            $holidaysUrl = $userSecUtil->getSiteSettingParameter('holidaysUrl','vacreq');
            if (!$holidaysUrl) {
                //throw new \InvalidArgumentException('holidaysUrl is not defined in Site Parameters.');
            }
            $holidayLink = '<a href="' . $holidaysUrl . '" target="_blank">holidays</a>';

            $result .= "<br>If your requests included " . $holidayLink . ", they are not automatically removed from these counts.";
        }

        return $result;
    }

    //$yearRange: '2015-2016' or '2015'
    public function getUserCarryOverDays( $user, $yearRange, $asObject=false ) {

        //echo "yearRange=[$yearRange]<br>";

        $startYearArr = $this->getYearsFromYearRangeStr($yearRange);
        $startYear = $startYearArr[0];

        //echo "startYear=[$startYear]<br>";

        //process.py script: replaced namespace by ::class: ['AppVacReqBundle:VacReqCarryOver'] by [VacReqCarryOver::class]
        $repository = $this->em->getRepository(VacReqCarryOver::class);
        $dql = $repository->createQueryBuilder('carryOver');

        $dql->leftJoin("carryOver.userCarryOver", "userCarryOver");

        $dql->where("userCarryOver.user = :user");
        $dql->andWhere("carryOver.year = :year");

        $query = $dql->getQuery(); //$query = $this->em->createQuery($dql);

        $query->setParameter('user', $user->getId());
        $query->setParameter('year', $startYear);

        //echo "dql=".$dql."<br>";

        $carryOvers = $query->getResult();
        //echo "carryOvers=".count($carryOvers)."<br>";

        if( count($carryOvers) > 0 ) {
            if( $asObject ) {
                return $carryOvers[0];
            }
            $days = $carryOvers[0]->getDays();
            //echo "days=".$days."<br>";
            return $days;
        }
        //echo "days=null<br>";

        return null;
    }

    //$entity - AppVacReqBundle:VacReqRequest
    //Used in processChangeStatusCarryOverRequest (edit, review, vacreq_status_change, vacreq_status_email_change)
    //and when status='approved' and in vacreq_status_change, vacreq_status_email_change
    public function processVacReqCarryOverRequest( $entity, $onlyCheck=false ) {

        $logger = $this->container->get('logger');
        $requestType = $entity->getRequestType();

        if( !$requestType || ($requestType && $requestType->getAbbreviation() != "carryover") ) {
            return null;
        }

        $subjectUser = $entity->getUser();

        //get userCarryOver. This does not distinguish between approved, rejected or pending requests.
        //Each user has only one VacReqUserCarryOver. VacReqUserCarryOver has multiple carryOvers(VacReqCarryOver: year, days)
        //process.py script: replaced namespace by ::class: ['AppVacReqBundle:VacReqUserCarryOver'] by [VacReqUserCarryOver::class]
        $userCarryOver = $this->em->getRepository(VacReqUserCarryOver::class)->findOneByUser($subjectUser->getId());
        //echo "found userCarryOverID=".$userCarryOver->getId()."<br>";

        if( !$userCarryOver ) {
            $userCarryOver = new VacReqUserCarryOver($subjectUser);
        }

        //get VacReqCarryOver for request's destination year
        //If "2020-2021" as the current academic year => The current year is FY21 (CarryOver entity is referred by the destination year)
        $carryOverYear = $entity->getDestinationYear();

        //find CarryOver entity by destination year $carryOverYear
        $carryOver = null;
        foreach( $userCarryOver->getCarryOvers() as $carryOverThis ) {
            //echo "carryOverThis ID=".$carryOverThis->getId().": year=".$carryOverYear." ?= ".$carryOverThis->getYear()."<br>";
            if( $carryOverThis->getYear() == $carryOverYear ) {
                $carryOver = $carryOverThis;
                break;
            }
        }
        //$logger->notice("carryOver=".$carryOver);

        $carryOverDays = null;

        if( !$carryOver ) {
            if( $onlyCheck == false ) {
                //create CarryOver if not existing
                $carryOver = new VacReqCarryOver();
                $carryOver->setYear($carryOverYear);
                $userCarryOver->addCarryOver($carryOver);
            }
        } else {
            $carryOverDays = $carryOver->getDays();
        }

        //Get $carryOverDays
        //echo "carryOverDays=".$carryOverDays."<br>";
        $res = array('userCarryOver'=>$userCarryOver);

        if( $carryOverDays ) {

            //"FirstName LastName already has X days carried over from 20YY-20ZZ academic year to the 20ZZ-20MM academic year on file.
            $carryOverWarningMessageLog = $entity->getUser()->getUsernameOptimal()." already has ".$carryOverDays." days carried over from ".
                $entity->getSourceYearRange()." academic year to the ".$entity->getDestinationYearRange()." academic year on file.<br>";

            //$carryOverWarningMessageLog .= "If this request would be approved, all previously already approved carry over requests for the same destination ".
            //    $entity->getDestinationYearRange()." academic year would be canceled automatically.<br>";
            $carryOverWarningMessageLog .= "Approving this carry over request cancels all previously approved carry over requests".
            " for the same destination ".$entity->getDestinationYearRange()." academic year.<br>";

            // This carry over request asks for N days to be carried over from 20YY-20ZZ academic year to the 20ZZ-20MM academic year.
            $carryOverWarningMessageLog .= "This carry over request asks for ".$entity->getCarryOverDays()." days to be carried over from ".
                $entity->getSourceYearRange()." academic year to the ".$entity->getDestinationYearRange()." academic year on file.<br>";
            // Please enter the total amount of days that should be carried over 20YY-20ZZ academic year to the 20ZZ-20MM academic year: [ ]"
            $carryOverWarningMessage = $carryOverWarningMessageLog . "Please enter the total amount of days that should be carried over ".
                $entity->getSourceYearRange()." academic year to the ".$entity->getDestinationYearRange()." academic year on file.";

            $res['exists'] = true;
            $res['days'] = $carryOverDays;
            $res['carryOverWarningMessage'] = $carryOverWarningMessage;
            $res['carryOverWarningMessageLog'] = $carryOverWarningMessageLog;

        } else {

            $carryOverDays = $entity->getCarryOverDays();
            if( $carryOver ) {
                //$logger->notice("exists carryover=".$carryOver);
                if( $onlyCheck == false ) {
                    //$logger->notice("set carryover days=".$carryOverDays);
                    $carryOver->setDays($carryOverDays);
                    //$em = $this->getDoctrine()->getManager();
                    //$em->persist($carryOver);
                    //$em->flush($carryOver);
                }
            }
            $res['exists'] = false;
            $res['days'] = $carryOverDays;
            $res['carryOverWarningMessage'] = null;
            $res['carryOverWarningMessageLog'] = null;
        }

        if( $carryOver ) {
            //$logger->notice("exists carryover=".$carryOver);
            if( $onlyCheck == false ) {
                $carryOverDays = $entity->getCarryOverDays();
                //$logger->notice("final set carryover days=".$carryOverDays);
                $carryOver->setDays($carryOverDays);
                //$em = $this->getDoctrine()->getManager();
                //$em->persist($carryOver);
                //$em->flush($carryOver);
                //exit("EOF process VacReqCarryOverRequest");
            }
        }

        return $res;
    }

    //Assume all entities are in DB
    //This will make sure that only one carryover request exists for the current year, and it will cancel all previous carryover requests
    //Used in
    //CarryOverController #365: vacreq_status_change_carryover, vacreq_status_email_change_carryover
    //RequestController: vacreq_status_change, vacreq_status_email_change
    //don't need parameter $originalCarryOverDays=NULL
    public function syncVacReqCarryOverRequest( $entity, $originalStatus ) {

        if( $entity->getRequestTypeAbbreviation() == "carryover" ) {
            //OK
        } else {
            //exit("Not Carry Over request");
            return "Not Carry Over request";
        }

        $newStatus = $entity->getStatus();
        $newTentativeStatus = $entity->getTentativeStatus();
        $carryOverDays = $entity->getCarryOverDays();

        //echo "originalStatus=$originalStatus, newStatus=$newStatus, newTentativeStatus=$newTentativeStatus, carryOverDays=$carryOverDays <br>";
        //exit(111);

        //Final status: pending, approved, rejected, cancel (ignore extraStatus: cancellation-request, cancellation-approve, cancellation-reject)

        //any state -> approved (pending->approved, cancel->approved, rejected->approved):
        //1) Copy $carryOverDays to this $carryOver
        //2) Cancel all other approved carry over requests

        //approved -> any state (approved -> pending, approved -> cancel, approved -> rejected):
        //Remove $carryOverDays from $carryOver

        //$newStatus and $originalStatus are unchanged and approved
        // AND $carryOverDays = 0 or NULL
        //Remove $carryOverDays from $carryOver

        $res = NULL;
        $update = false;
        $eventLog = NULL;
        $loggedinUser = $this->security->getUser();
        $subjectUser = $entity->getUser();
        $carryOverYear = $entity->getDestinationYear();

        //find CarryOver entity by destination year $carryOverYear
        //process.py script: replaced namespace by ::class: ['AppVacReqBundle:VacReqUserCarryOver'] by [VacReqUserCarryOver::class]
        $userCarryOver = $this->em->getRepository(VacReqUserCarryOver::class)->findOneByUser($subjectUser->getId());
        if( !$userCarryOver ) {
            //exit("VacReqUserCarryOver container not found for $subjectUser");
            return "VacReqUserCarryOver container not found for $subjectUser";
        }

        $carryOver = null;
        foreach( $userCarryOver->getCarryOvers() as $carryOverThis ) {
            //echo "carryOverThis ID=".$carryOverThis->getId().": year=".$carryOverYear." ?= ".$carryOverThis->getYear()."<br>";
            if( $carryOverThis->getYear() == $carryOverYear ) {
                $carryOver = $carryOverThis;
                break;
            }
        }

        if( !$carryOver ) {
            //exit("CarryOver object not found for destination year $carryOverYear for $subjectUser");
            return "CarryOver object not found for destination year $carryOverYear for $subjectUser";
        }

        //any state -> approved
        if( $newStatus == 'approved' && $newStatus != $originalStatus ) {
            //exit("case: any state -> approved");
            //1) Copy $carryOverDays to this $carryOver
            $carryOver->setDays($carryOverDays);

            //2) Cancel all other approved carry over requests
            $approvedRequests = $this->getCarryOverRequestsByUserStatusYear($subjectUser,'approved',$carryOverYear,$entity);
            //echo "approvedRequests=".count($approvedRequests)."<br>";

            $recordEventLog = false;
            $cancelStr = $this->cancelApprovedCarryOverRequests($approvedRequests,$entity,$recordEventLog);

            $eventLog = "Set $carryOverDays carryover days to destination year $carryOverYear for $subjectUser.";
            $res = $eventLog . "<br><br>" . $cancelStr;
            //exit($res);

            $update = true;
        }

        //approved -> any state
        if( $originalStatus == 'approved' && $newStatus != $originalStatus ) {
            //exit("case: approved -> any state");
            //Remove $carryOverDays from $carryOver
            $carryOver->setDays(NULL);
            $requestName = $entity->getRequestName() . " ID# " . $entity->getId();
            $res = "Remove $carryOverDays carryover days from destination year $carryOverYear for $subjectUser ".
            "by $newStatus action for the $requestName";

            //find approved carry over request for the same year and update $carryOver with days from this found approved carry over request
            $approvedRequests = $this->getCarryOverRequestsByUserStatusYear($subjectUser,'approved',$carryOverYear);
            //echo "approvedRequests=".count($approvedRequests)."<br>";
            if( count($approvedRequests) > 0 ) {
                $approvedRequest = $approvedRequests[0];
                $approvedCarryOverDays = $approvedRequest->getCarryOverDays();
                $carryOver->setDays($approvedCarryOverDays);

                $requestName = $approvedRequest->getRequestName() . " ID# " . $approvedRequest->getId();
                $res = $res . "<br><br>" . "Set $approvedCarryOverDays carryover days for destination year $carryOverYear ".
                "for $subjectUser by the latest existing approved $requestName";
            }

            $eventLog = $res;

            $update = true;
        }

        //$newStatus and $originalStatus are unchanged and approved
        //1) $carryOverDays = 0 or NULL
        //Remove $carryOverDays from $carryOver
        //2) $carryOverDays != $originalThisCarryOverDays
        //Update $carryOverDays
        if( $newStatus == 'approved' && $newStatus == $originalStatus ) {
            //exit("case: approved and update days");

            $originalThisCarryOverDays = $carryOver->getDays();

            if( $originalThisCarryOverDays != $carryOverDays ) {

                if ($carryOverDays) {
                    $carryOver->setDays($carryOverDays);

                    $requestName = $entity->getRequestName() . " ID# " . $entity->getId();
                    $res = $eventLog = "Carry over days are changed from $originalThisCarryOverDays to $carryOverDays for destination year $carryOverYear for $subjectUser" .
                        ", because the carry over days in approved $requestName have been updated by $loggedinUser";
                } else {
                    $originalThisCarryOverDays = $carryOver->getDays();
                    //Remove $carryOverDays from $carryOver
                    $carryOver->setDays(NULL);

                    $requestName = $entity->getRequestName() . " ID# " . $entity->getId();
                    $res = $eventLog = "Remove $originalThisCarryOverDays carryover days from destination year $carryOverYear for $subjectUser" .
                        ", because the carry over days in approved $requestName is set to zero by $loggedinUser";
                }

                $update = true;
            }
        }

        if( $update ) {
            //echo $res;
            //exit(111);
            $this->em->flush();
        }

        if( $eventLog ) {
            $userSecUtil = $this->container->get('user_security_utility');
            $loggedinUser = $this->security->getUser();

            //Event Log
            $eventType = 'Carry Over Request Updated';
            $userSecUtil->createUserEditEvent($this->container->getParameter('vacreq.sitename'),$eventLog,$loggedinUser,$entity,null,$eventType);
        }

        return $res;
    }
    public function cancelApprovedCarryOverRequests( $approvedRequests, $changedByEntity, $recordEventLog=true ) {
        $userSecUtil = $this->container->get('user_security_utility');
        $loggedinUser = $this->security->getUser();
        $eventArr = array();
        $res = NULL;

        foreach($approvedRequests as $approvedRequest) {
            //$msg = "approvedRequest=$approvedRequest <br>";

            //set status to canceled
            $approvedRequest->setStatus('canceled');

            //Event Log
            $requestName = $approvedRequest->getRequestName() . " ID# " . $approvedRequest->getId();
            $eventType = 'Carry Over Request Updated';
            $event = "Previously approved $requestName for " . $approvedRequest->getUser() . " has been canceled by " . $loggedinUser . " " .
                " by approving the carry over request ID# " . $changedByEntity->getId() . " for " . $changedByEntity->getUser();

            if( $recordEventLog ) {
                $userSecUtil->createUserEditEvent($this->container->getParameter('vacreq.sitename'), $event, $loggedinUser, $approvedRequest, null, $eventType);
            }

            $eventArr[] = $event;

            //echo $event;
            //$logger->notice($event);
        }

        if( count($eventArr) > 0 ) {
            $res = implode("<br>",$eventArr);
        }

        return $res;
    }

    //Do not use it (do not delete CarryOver for canceled carry over request).
    //Use syncVacReqCarryOverRequest instead to take care update days in CarryOver
    //if multiple carry over requests are existed, then the VacReqUserCarryOver should be changed according to them.
    //we might have one carry over request approved and one denied for the same year.
    //Currently, VacReqUserCarryOver is synchronised to the latest approved request. All preceding carryover requests are canceled.
    public function deleteCanceledVacReqCarryOverRequest( $entity )
    {

        //echo "start deleteCanceledVacReqCarryOverRequest <br>";

        $logger = $this->container->get('logger');
        $requestType = $entity->getRequestType();

        if (!$requestType || ($requestType && $requestType->getAbbreviation() != "carryover")) {
            return;
        }

        $subjectUser = $entity->getUser();

        //get userCarryOver
        //process.py script: replaced namespace by ::class: ['AppVacReqBundle:VacReqUserCarryOver'] by [VacReqUserCarryOver::class]
        $userCarryOver = $this->em->getRepository(VacReqUserCarryOver::class)->findOneByUser($subjectUser->getId());

        if (!$userCarryOver) {
            //$logger->notice("VacReqUserCarryOver not found by userid=".$subjectUser->getId());
            return;
        }

        //get VacReqCarryOver for request's destination year
        $carryOverYear = $entity->getDestinationYear();
        $carryOverDays = $entity->getCarryOverDays();

        $carryOver = null;
        foreach ($userCarryOver->getCarryOvers() as $carryOverThis) {
            //$logger->notice("carryOverThis->getYear()=".$carryOverThis->getYear());
            if( $carryOverThis->getYear() == $carryOverYear && $carryOverThis->getDays() == $carryOverDays ) {
                $carryOver = $carryOverThis;
                break;
            }
        }

        $removeCarryoverStr = "";

        if( $carryOver ) {

            $removeCarryoverStr = "Removed CarryOver data (".$carryOver->getDays()." day from destination ".$carryOverYear." year for ".$subjectUser;

            $userCarryOver->removeCarryOver($carryOver);

            $this->em->persist($carryOver);
            $this->em->remove($carryOver);

            $this->em->persist($userCarryOver);
            $this->em->flush();

            //$logger->notice($removeCarryoverStr);
        } else {
            //$logger->notice("VacReqUserCarryOver does not carryOver object with destination year=".$carryOverYear);
        }

        return $removeCarryoverStr;
    }

    public function getYearsFromYearRangeStr($yearRangeStr) {
        if( !$yearRangeStr ) {
            throw new \InvalidArgumentException('Year Range of the Academic year is not defined: yearRangeStr='.$yearRangeStr);
        }
        if( strpos((string)$yearRangeStr, '-') === false ) {
            //echo "no '-' in ".$yearRangeStr."<br>";
            $yearRangeArr = array($yearRangeStr);
            return $yearRangeArr;
        }
        $yearRangeArr = explode("-",$yearRangeStr);
        if( count($yearRangeArr) != 2 ) {
            throw new \InvalidArgumentException('Start or End Academic years are not defined: yearRangeStr='.$yearRangeStr);
        }
        return $yearRangeArr;
    }

    public function getPendingTotalDaysAcademicYear( $user, $yearRange ) {

        $requestTypeStr = "business";
        $resB = $this->getApprovedTotalDaysAcademicYear( $user, $requestTypeStr, $yearRange, "pending" );
        $numberOfDaysB = $resB['numberOfDays'];

        $requestTypeStr = "vacation";
        $resV = $this->getApprovedTotalDaysAcademicYear( $user, $requestTypeStr, $yearRange, "pending" );
        $numberOfDaysV = $resV['numberOfDays'];

        $totalPendingDays = $numberOfDaysB + $numberOfDaysV;

        return $totalPendingDays;
    }

    //$centuryToStr - Replace century to string, i.e. $centuryToStr='FY': 2022 -> FY22
    //Use the last year for FY: 2024-2025 => FY25
    public function getCurrentAcademicYearRange( $centuryToStr=null ) {
        $dates = $this->getCurrentAcademicYearStartEndDates();
        $startDate = $dates['startDate']; //Y-m-d
        //echo "startDate=".$startDate."<br>";
        $currentYearStartDateArr = explode("-",$startDate);
        $startYear = $currentYearStartDateArr[0];

        $endDate = $dates['endDate']; //Y-m-d
        //echo "endDate=".$endDate."<br>";
        $currentYearEndDateArr = explode("-",$endDate);
        $endYear = $currentYearEndDateArr[0];

        if( $centuryToStr ) {
            //replace first two chars by $centuryToStr: 2022 -> FY22
            $startYear = $centuryToStr . substr($startYear, 2);
            $endYear = $centuryToStr . substr($endYear, 2);
        }

        $yearRange = $startYear."-".$endYear;

        return $yearRange;
    }

    //$offset = 0 => previous year
    //$offset = 1 => previous previous year
    public function getPreviousAcademicYearRange( $offset = null, $centuryToStr=null ) {
        $dates = $this->getCurrentAcademicYearStartEndDates();
        $startDate = $dates['startDate']; //Y-m-d
        //echo "startDate=".$startDate."<br>";

        $currentYearStartDateArr = explode("-",$startDate);
        $year = $currentYearStartDateArr[0];

        $endYear = ((int)$year);     //previous year end
        $startYear = $endYear - 1;   //previous year start

        if( $offset ) {
            $endYear = $endYear - 1;
            $startYear = $startYear - 1;
        }

        if( $centuryToStr ) {
            //replace first two chars by $centuryToStr: 2022 -> FY22
            $startYear = $centuryToStr . substr($startYear, 2);
            $endYear = $centuryToStr . substr($endYear, 2);
        }

        //echo "previous year=".$year."<br>";

        $yearRange = $startYear."-".$endYear;

        return $yearRange;
    }

    public function getNextAcademicYearRange( $centuryToStr=null ) {
        $dates = $this->getCurrentAcademicYearStartEndDates();
        $startDate = $dates['startDate']; //Y-m-d
        //echo "startDate=".$startDate."<br>";

        $currentYearStartDateArr = explode("-",$startDate);
        $year = $currentYearStartDateArr[0];

        $startYear = ((int)$year) + 1;   //next year start
        $endYear = $startYear + 1; //next year end

        //echo "next year=".$year."<br>";

        if( $centuryToStr ) {
            //replace first two chars by $centuryToStr: 2022 -> FY22
            $startYear = $centuryToStr . substr($startYear, 2);
            $endYear = $centuryToStr . substr($endYear, 2);
        }

        $yearRange = $startYear."-".$endYear;

        return $yearRange;
    }

    //calculate approved total days for current academical year
    public function getApprovedTotalDays( $user, $requestTypeStr ) { //, $bruteForce=false
        $yearRange = $this->getCurrentAcademicYearRange();
        //echo "getApprovedTotalDays yearRange=".$yearRange."<br>";
        $res = $this->getApprovedTotalDaysAcademicYear( $user, $requestTypeStr, $yearRange, "approved" ); //, $bruteForce
        return $res;
    }

    //calculate approved total days for current academical year
    public function getPreviousYearApprovedTotalDays( $user, $requestTypeStr ) { //, $bruteForce=false
        $yearRange = $this->getPreviousAcademicYearRange();
        //echo "getPreviousYearApprovedTotalDays yearRange=".$yearRange."<br>";
        $res = $this->getApprovedTotalDaysAcademicYear( $user, $requestTypeStr, $yearRange, "approved" ); //, $bruteForce
        return $res;
    }

    public function getInaccuracyMessage() {
        //return "the count may be imprecise due to included holidays";
        return "the count may be imprecise due to included transition between academic years";
    }

    //Main function to calculate total approved total days for the academical year specified by $yearRange (2015-2016 - current academic year)
    //TODO: include holidays: compare $requestNumberOfDays with submitted days ($subRequest->getNumberOfDays()) for request submitted with modified away days
    public function getApprovedTotalDaysAcademicYear( $user, $requestTypeStr, $yearRange, $status="approved" ) { //, $bruteForce=false
        //exit('get ApprovedTotalDaysAcademicYear');
        $academicYearStart = $this->getAcademicYearStart();
        $academicYearEnd = $this->getAcademicYearEnd();

        //constract start and end date for DB select "Y-m-d"
        //academicYearStart
        $academicYearStartStr = $academicYearStart->format('m-d');

        //years
        $yearRangeArr = $this->getYearsFromYearRangeStr($yearRange);
        $previousYear = $yearRangeArr[0];
        $currentYear = $yearRangeArr[1];

        $academicYearStartStr = $previousYear."-".$academicYearStartStr;
        //echo "current academicYearStartStr=".$academicYearStartStr."<br>";
        //academicYearEnd
        $academicYearEndStr = $academicYearEnd->format('m-d');

        $academicYearEndStr = $currentYear."-".$academicYearEndStr;
        //echo "current academicYearEndStr=".$academicYearEndStr."<br>";

        //step1: get requests within current academic Year (2015-07-01 - 2016-06-30)
        //getApprovedYearDays($user, $requestTypeStr, $startStr=null, $endStr=null, $type=null, $asObject=false, $status='approved', $bruteForce=false)
        $numberOfDaysInside = $this->getApprovedYearDays(
            $user,
            $requestTypeStr,
            $academicYearStartStr,
            $academicYearEndStr,
            "inside",
            $asObject=false,
            $status
            //$bruteForce
        );
        //echo $status.": numberOfDaysInside=".$numberOfDaysInside.", startYear=".$academicYearStartStr.", endYear=".$academicYearEndStr."<br>";

//        //testing
//        $numberOfDaysInsideRequests = $this->getApprovedYearDays($user,$requestTypeStr,$academicYearStartStr,$academicYearEndStr,"inside",true,$status,$bruteForce);
//        echo $status.": numberOfDaysInsideRequests count=".count($numberOfDaysInsideRequests)."<br>";
//        if( $status=='pending' && $academicYearStartStr=='2019-07-01' && count($numberOfDaysInsideRequests)>0 ) {
//            exit('111');
//        }

        //step2: get requests with start date earlier than academic Year Start
        $numberOfDaysBeforeRes = $this->getApprovedBeforeAcademicYearDays($user,$requestTypeStr,$academicYearStartStr,$academicYearEndStr,$status); //,$bruteForce
        $numberOfDaysBefore = $numberOfDaysBeforeRes['numberOfDays'];
        $accurateBefore = $numberOfDaysBeforeRes['accurate'];
        //$accurateBefore = false;
        //echo $status.":numberOfDaysBefore=".$numberOfDaysBefore."<br>";

        //step3: get requests with start date later than academic Year End
        $numberOfDaysAfterRes = $this->getApprovedAfterAcademicYearDays($user,$requestTypeStr,$academicYearStartStr,$academicYearEndStr,$status); //,$bruteForce
        $numberOfDaysAfter = $numberOfDaysAfterRes['numberOfDays'];
        $accurateAfter = $numberOfDaysAfterRes['accurate'];
        //echo $status.":numberOfDaysAfter=".$numberOfDaysAfter."<br>";

        $res = array();

        $numberOfDays = $numberOfDaysBefore+$numberOfDaysInside+$numberOfDaysAfter;
        //echo $status.": sum numberOfDays=".$numberOfDays."<br>";

        $res['numberOfDays'] = $numberOfDays;
        $res['accurate'] = true;

        if( !$accurateBefore || !$accurateAfter ) {
            $res['accurate'] = false; //accurate = false indicates that calculation contains transition between academic year
        }

//        $res['accurate'] = false;//testing
//        dump($res);
//        exit('111');

        return $res;
    }

    // |-----start-----|year|-----end-----|year+1|----|
    // |-----start-----|2015-07-01|-----end-----|2016-06-30|----|
    //$requestTypeStr = 'vacation' or 'business'
    public function getApprovedBeforeAcademicYearDays( $user, $requestTypeStr, $startStr=null, $endStr=null, $status="approved" ) {
        //$logger = $this->container->get('logger');
        $days = 0;
        $accurate = true;
        $subRequestGetMethod = "getRequest".$requestTypeStr;

        //echo "before startStr=".$startStr."<br>";
        //echo "before endStr=".$endStr."<br>";
        $requests = $this->getApprovedYearDays($user,$requestTypeStr,$startStr,$endStr,"before",$asObject=true,$status);
        //echo "before requests count=".count($requests)."<br>";

        //$accurateNoteArr = array();

        foreach( $requests as $request ) {
            $subRequest = $request->$subRequestGetMethod();
            $requestEndAcademicYearStr = $this->getAcademicYearEdgeDateBetweenRequestStartEnd($request,"first");
            if( !$requestEndAcademicYearStr ) {
                //echo $request->getId()." continue <br>";
                continue;
            }
            //echo "requestStartDate=".$subRequest->getStartDate()->format('Y-m-d')."<br>";
            //echo "requestEndAcademicYearStr=".$requestEndAcademicYearStr."<br>";
            //echo $request->getId().": before: request days=".$subRequest->getNumberOfDays()."<br>";
            //$workingDays = $this->getNumberOfWorkingDaysBetweenDates( $subRequest->getStartDate(), new \DateTime($requestEndAcademicYearStr) );
            $workingDays = $this->getNumberOfWorkingDaysBetweenDates( new \DateTime($requestEndAcademicYearStr), $subRequest->getEndDate() );
            //echo "workingDays=".$workingDays."<br>";

            $requestNumberOfDays = $subRequest->getNumberOfDays();

            if( $workingDays > $requestNumberOfDays ) {
                //$logger->warning("Logical error getApprovedBeforeAcademicYearDays: number of calculated working days (".$workingDays.") are more than number of days in request (".$subRequest->getNumberOfDays().")");
                $workingDays = $requestNumberOfDays;
            }

            if( $workingDays != $requestNumberOfDays ) {
                //inaccuracy
                //echo "user=".$request->getUser()."<br>";
                //echo "Before: ID# ".$request->getId()." inaccurate: workingDays=".$workingDays." enteredDays=".$subRequest->getNumberOfDays()."<br>";
                $accurate = false;
                //$accurateNoteArr[$request->getId()] = "Submitted $requestNumberOfDays days off differ from $workingDays weekly working days"; //based on 5 days per week
            }

//            //TODO: Use holiday dates to calculate the WCM working days
//            if( $accurate ) {
//                //$requestTypeStr = 'vacation' or 'business'
//                //getDaysDifferenceNote($vacreqRequest)
//                //count number of vacation days from $startDate and $endDate
//                $startDate = null;
//                $endDate = null;
//                if( $startStr ) {
//                    $startDate = \DateTime::createfromformat('Y-m-d H:i:s', $startStr);
//                }
//                if( $endStr ) {
//                    $endDate = \DateTime::createfromformat('Y-m-d H:i:s', $endStr);
//                }
//                $institution = $request->getInstitution();
//                $institutionId = null;
//                if( $institution ) {
//                    $institutionId = $institution->getId();
//                }
//                //$countedNumberOfDays = $this->getNumberOfWorkingDaysBetweenDates($startDate,$endDate);
//                $holidays = $this->getHolidaysInRange($startDate,$endDate,$institutionId,$custom=false);
//                //$holidays = array(); $holidays[] = 1;//testing
//                $countedNumberOfDays = intval($workingDays) - count($holidays);
//
//                if( $countedNumberOfDays != $requestNumberOfDays ) {
//                    $accurate = false;
//                    $accurateNoteArr[$request->getId()] = "Submitted $requestNumberOfDays days off differ".
//                        " from $countedNumberOfDays working days corrected by holidays".
//                        " (".intval($workingDays)."-".count($holidays).")";
//                }
//                //$accurate = false;
//            }

            //echo $request->getId().": before: request days=".$workingDays."<br>";
            $days = $days + $workingDays;
        }

        $res = array(
            'numberOfDays' => $days,
            'accurate' => $accurate, //accurate = false indicates that calculation contains transition between academic year
            //'accurateNoteArr' => $accurateNoteArr
        );

        return $res;
    }

    // |----|year|-----start-----|year+1|-----end-----|
    // |----|2015-07-01|-----start-----|2016-06-30|-----end-----|
    public function getApprovedAfterAcademicYearDays( $user, $requestTypeStr, $startStr=null, $endStr=null, $status="approved" ) {
        //$logger = $this->container->get('logger');
        $vacreqCalendarUtil = $this->container->get('vacreq_calendar_util');
        $days = 0;
        $accurate = true;
        $subRequestGetMethod = "getRequest".$requestTypeStr;

        //echo "after startStr=".$startStr."<br>";
        //echo "after endStr=".$endStr."<br>";
        $requests = $this->getApprovedYearDays($user,$requestTypeStr,$startStr,$endStr,"after",$asObject=true,$status);
        //echo "after requests count=".count($requests)."<br>";

        foreach( $requests as $request ) {
            $subRequest = $request->$subRequestGetMethod();
            $requestEndAcademicYearStr = $this->getAcademicYearEdgeDateBetweenRequestStartEnd($request,"last"); //get the academic year edge date after the request's start
            //echo $request->getId().": after: request days=".$subRequest->getNumberOfDays()."<br>";
            if( !$requestEndAcademicYearStr ) {
                continue;
            }
            //echo "requestEndAcademicYearStr=".$requestEndAcademicYearStr."<br>";
            //echo "requestStartAcademicYearStr=".$subRequest->getStartDate()->format('Y-m-d')."<br>";

            $startDate = $subRequest->getStartDate();
            $endDate = new \DateTime($requestEndAcademicYearStr);

//            ///// get holidays /////
//            //TODO: include holidays: compare $requestNumberOfDays with submitted days $subRequest->getNumberOfDays()
//            $holidays = array();
//            $institution = $request->getInstitution();
//            $institutionId = null;
//            if( $institution ) {
//                $institutionId = $institution->getId();
//            }
//            $holidays = $vacreqCalendarUtil->getHolidaysInRange($startDate,$endDate,$institutionId,$custom=false);
//            ///// EOF get holidays /////

            //$workingDays = $this->getNumberOfWorkingDaysBetweenDates( new \DateTime($requestStartAcademicYearStr), $subRequest->getEndDate() );
            //$workingDays = $this->getNumberOfWorkingDaysBetweenDates( $subRequest->getStartDate(), new \DateTime($requestEndAcademicYearStr) );
            $workingDays = $this->getNumberOfWorkingDaysBetweenDates($startDate,$endDate);
            //echo "calculated workingDays=".$workingDays."<br>";

            $requestNumberOfDays = $subRequest->getNumberOfDays();

            if( $workingDays > $requestNumberOfDays ) {
                //$logger->warning("Logical error getApprovedAfterAcademicYearDays: number of calculated working days (".$workingDays.") are more than number of days in request (".$subRequest->getNumberOfDays().")");
                $workingDays = $requestNumberOfDays;
            }

            if( $workingDays != $requestNumberOfDays ) {
                //inaccuracy
                //echo "After: ID# ".$request->getId()." inaccurate: workingDays=".$workingDays." enteredDays=".$requestNumberOfDays."<br>";
                $accurate = false;
            }

            //echo $request->getId().": after: request days=".$workingDays."<br>";
            $days = $days + $workingDays;
        }

        $res = array(
            'numberOfDays' => $days,
            'accurate' => $accurate //accurate = false indicates that calculation contains transition between academic year
        );

        return $res;
    }

    //get prior approved days for the request's academic year:
    // SUM numberOfDays from this request's academic start date and this request's first day away
    public function getPriorApprovedDays( $request, $requestTypeStr ) {

//        $userSecUtil = $this->container->get('user_security_utility');
        //academicYearStart
//        $academicYearStart = $userSecUtil->getSiteSettingParameter('academicYearStart','vacreq');
//        if( !$academicYearStart ) {
//            $academicYearStart = \DateTime::createFromFormat('Y-m-d', date("Y")."-07-01");
//            //throw new \InvalidArgumentException('academicYearStart is not defined in Site Parameters.');
//        }
        $academicYearStart = $this->getAcademicYearStart();

        //academicYearStart
        $academicYearStartStr = $academicYearStart->format('m-d');

        //get request's academic year
        $academicYearArr = $this->getRequestAcademicYears($request);
        if( count($academicYearArr) > 0 ) {
            $yearsArr = $this->getYearsFromYearRangeStr($academicYearArr[0]);
            $startYear = $yearsArr[0];
            $academicYearStartStr = $startYear."-".$academicYearStartStr;
        } else {
            return null;
            //throw new \InvalidArgumentException("Request's academic start year is not defined in request ID#".$request->getId());
        }
        //echo "academicYearStartStr=".$academicYearStartStr."<br>";

        $user = $request->getUser();

        //get the first day away
        $requestFirstDateAway = $request->getFirstDateAway('approved',$requestTypeStr);
        if( $requestFirstDateAway == null ) {
            //exit("Request's first day away is not defined.");
            //throw new \InvalidArgumentException("Request's first day away is not defined.");
            return null;
        }
        $requestFirstDateAwayStr = $requestFirstDateAway->format('Y-m-d');
        //$requestFirstDateAwayStr = date("Y-m-d", strtotime('+1 days', strtotime($requestFirstDateAwayStr)) );
        //echo "requestFirstDateAwayStr=".$requestFirstDateAwayStr."<br>";

        //use the last day, otherwise duplicates requests are not counted
//        $finalStartEndDates = $request->getFinalStartEndDates($requestTypeStr);
//        $requestFirstDateAwayStr = $finalStartEndDates['endDate']->format('Y-m-d');
//        $requestFirstDateAwayStr = date("Y-m-d", strtotime('+1 days', strtotime($requestFirstDateAwayStr)) );

        $days = $this->getApprovedYearDays($user,$requestTypeStr,$academicYearStartStr,$requestFirstDateAwayStr,"inside",false);

        return $days;
    }

//    //NOT USED
//    //http://stackoverflow.com/questions/7224792/sql-to-find-time-elapsed-from-multiple-overlapping-intervals
//    public function getApprovedYearDays_SingleQuery( $user, $requestTypeStr, $startStr=null, $endStr=null, $type=null, $asObject=false, $status='approved' ) {
//
//        echo $user.": ".$type.": requestTypeStr=".$requestTypeStr."; status=".$status."<br>";
//        echo "date range=".$startStr."<=>".$endStr."<br>";
//        $numberOfDays = 0;
//
//        if( $requestTypeStr == 'business' || $requestTypeStr == 'requestBusiness' ) {
//            $joinStr = " LEFT JOIN request2.requestBusiness requestType2 ";
//        }
//
//        if( $requestTypeStr == 'vacation' || $requestTypeStr == 'requestVacation' ) {
//            $joinStr = " LEFT JOIN request2.requestVacation requestType2 ";
//        }
//
//        //WITH  request.id <> request2.id AND request.user = request2.user AND user.id = ".$user->getId()." AND requestType.status='".$status."'
//        //AND requestType.startDate > '" . $startStr . "'" . " AND requestType.endDate < " . "'" . $endStr . "'
//        $query = $this->em->createQuery(
//            "SELECT
//              SUM(requestType.numberOfDays) as numberOfDays, COUNT(request) as totalCount
//            FROM AppVacReqBundle:VacReqRequest request
//            INNER JOIN request.user user
//            INNER JOIN request.requestVacation requestType
//            INNER JOIN AppVacReqBundle:VacReqRequest request2
//              WITH  request.id <> request2.id AND request.user = request2.user AND user.id = ".$user->getId()." AND requestType.status='$status'
//                    AND request.firstDayAway < request2.firstDayAway AND request.firstDayBackInOffice < request2.firstDayBackInOffice
//            WHERE requestType.startDate > '$startStr' AND requestType.endDate < '$endStr'
//            HAVING COUNT(request2.id) = 0
//            "
//        );
//
//        $query = $this->em->createQuery(
//            "SELECT request1
//            FROM AppVacReqBundle:VacReqRequest request1
//            INNER JOIN request1.user user
//            INNER JOIN request1.requestVacation requestType
//            INNER JOIN AppVacReqBundle:VacReqRequest request2
//              WITH
//              (request1.id <> request2.id)
//              AND ( request1.firstDayAway <> request2.firstDayAway )
//            WHERE
//            requestType.startDate > '$startStr'
//            AND requestType.endDate < '$endStr'
//            AND request1.user = request2.user
//            AND user.id = ".$user->getId()."
//            AND requestType.status='$status'
//            "
//        );
//
//        $requests = $query->getResult();
//        echo "requests count=".count($requests)."<br>";
//
//        foreach( $requests as $request ) {
//            //echo $request->getId()." days=".$request->getTotalDays($status,$requestTypeStr);
//            echo $request->getId()." days=".$request->getRequestVacation()->getNumberOfDays()."<br>";
//        }
//        exit();
//
//
//        //INNER JOIN request2.requestVacation requestType2
//        //WHERE user.id = ".$user->getId()." AND requestType.status='".$status."'"
//        //." AND request.firstDayAway > request2.firstDayAway AND request.firstDayAway > request2.firstDayBackInOffice "
//        //."GROUP BY request.user,requestType.startDate,requestType.endDate"
//        //.""
//
////        $requests = $query->getResult();
////        foreach( $requests as $request ) {
////            $thisNumberOfDays = $request->getTotalDays($status,$requestTypeStr);
////            $finalStartEndDatesArr = $request->getFinalStartEndDates();
////            $startendStr = $finalStartEndDatesArr['startDate']->format('Y/m/d')."-".$finalStartEndDatesArr['endDate']->format('Y/m/d');
////            echo "request = ".$request->getId()." ".$startendStr.": days=".$thisNumberOfDays."<br>";
////            $numberOfDays = $numberOfDays + (int)$thisNumberOfDays;
////        }
////        echo "### get numberOfDays = ".$numberOfDays."<br><br>";
//
//        $numberOfDaysItems = $query->getResult();
//        echo "numberOfDaysItems count=".count($numberOfDaysItems)."<br>";
//
//        if( $numberOfDaysItems ) {
//            //$numberOfDaysItems = $numberOfDaysRes['numberOfDays'];
//            if( count($numberOfDaysItems) > 1 ) {
//                //$logger = $this->container->get('logger');
//                //$logger->warning('Logical error: found more than one SUM: count='.count($numberOfDaysItems));
//            }
//            foreach( $numberOfDaysItems as $numberOfDaysItem ) {
//                echo "+numberOfDays = ".$numberOfDaysItem['numberOfDays']."; count=".$numberOfDaysItem['totalCount']."<br>";
//                $numberOfDays = $numberOfDays + $numberOfDaysItem['numberOfDays'];
//            }
//            echo "### get numberOfDays = ".$numberOfDays."<br><br>";
//        }
//
//        return $numberOfDays;
//
//
//        //process.py script: replaced namespace by ::class: ['AppVacReqBundle:VacReqRequest'] by [VacReqRequest::class]
//        $repository = $this->em->getRepository(VacReqRequest::class);
//        $dql =  $repository->createQueryBuilder("request");
//
//        if( $asObject ) {
//            $dql->select('request');
//        } else {
//            //$dql->select('request');
//            $dql->select('SUM(requestType.numberOfDays) as numberOfDays, COUNT(request) as totalCount');
//            //$dql->select('DISTINCT request.id, user.id, SUM(requestType.numberOfDays) as numberOfDays');
//        }
//
////        $dql->innerJoin(
////            "AppVacReqBundle:VacReqRequest",
////            "request2",
////            "WITH",
////            "request2.id <> request.id AND request2.user = request.user"
////        );
//
//        $dql->leftJoin("request.user", "user");
//
//        if( $requestTypeStr == 'business' || $requestTypeStr == 'requestBusiness' ) {
//            $dql->leftJoin("request.requestBusiness", "requestType");
//            //$dql->leftJoin("request2.requestBusiness", "requestType2");
//            //$joinStr = " LEFT JOIN request2.requestBusiness requestType2";
//        }
//
//        if( $requestTypeStr == 'vacation' || $requestTypeStr == 'requestVacation' ) {
//            $dql->leftJoin("request.requestVacation", "requestType");
//            //$dql->leftJoin("request2.requestVacation", "requestType2");
//            //$joinStr = " LEFT JOIN request2.requestVacation requestType2";
//        }
//
//        $dql->where("requestType.id IS NOT NULL AND user.id = :userId AND requestType.status = :status");
//
//        // |----|year|-----start-----end-----|year+1|----|
//        // |----|2015-07-01|-----start-----end-----|2016-06-30|----|
//        if( $type == "inside" && $startStr && $endStr ) {
//            $dql->andWhere("requestType.startDate > '" . $startStr . "'" . " AND requestType.endDate < " . "'" . $endStr . "'");
//        }
//
//        // |-----start-----|year|-----end-----|year+1|----|
//        // |-----rstart-----|2015-07-01|-----rend-----|2016-06-30|----|
//        if( $type == "before" && $startStr ) {
//            //echo "startStr=".$startStr."<br>";
//            $dql->andWhere("requestType.startDate < '" . $startStr . "'" . " AND requestType.endDate > '".$startStr."'"); // . " AND requestType.endDate > " . "'" . $startStr . "'");
//        }
//
//        // |----|year|-----start-----|year+1|-----end-----|
//        // |----|2015-07-01|-----start-----|2016-06-30|-----end-----|
//        if( $type == "after" && $startStr && $endStr ) {
//            //echo "sql endStr=".$endStr."<br>";
//            //$dql->andWhere("requestType.endDate > '" . $endStr . "'" . " AND requestType.startDate < '".$endStr."'");  // . " AND requestType.endDate < " . "'" . $endStr . "'");
//            //$dql->andWhere("requestType.startDate > '" . $startStr . "'" . " AND requestType.endDate > " . "'" . $endStr . "'");
//            $dql->andWhere("requestType.startDate < '" . $endStr . "'" . " AND requestType.endDate > '".$endStr."'");
//        }
//
//
//        //if( !$asObject ) {
////                $dql->innerJoin(
////                    "AppVacReqBundle:VacReqRequest",
////                    "request2",
////                    "WITH",
////                    //"request2.firstDayAway > request.firstDayAway AND request2.firstDayBackInOffice > request.firstDayBackInOffice"
////                    "request2.firstDayAway > request.firstDayAway".
////                    " AND request2.firstDayBackInOffice > request.firstDayBackInOffice".
////                    " AND request2.user = request.user".
////                    " AND request2.id <> request.id"
////                );
//
////            $dql->innerJoin(
////                "AppVacReqBundle:VacReqRequest",
////                "request2",
////                "WITH",
////                "request2.id <> request.id AND request2.user = request.user"
////            );
////            $dql->andWhere(
////                "request2.firstDayAway > request.firstDayAway"
////                ." AND request2.firstDayBackInOffice > request.firstDayBackInOffice"
////                ." AND request2.firstDayAway > request.firstDayBackInOffice"
////                //." AND request2.user = request.user"
////                //." AND request2.id <> request.id"
////            );
//
////        $dql->andWhere(
////            "requestType2.startDate > requestType.startDate"
////            ." AND requestType2.endDate > requestType.endDate"
////            ." AND requestType2.startDate > requestType.endDate"
////            ." AND request2.user = request.user"
////            ." AND request2.id <> request.id"
////        );
//
////        $dql->andWhere(
////            "requestType.startDate > requestType2.startDate"
////            ." AND requestType.endDate > requestType2.endDate"
////            ." AND requestType.startDate > requestType2.endDate"
////            ." AND request2.user = request.user"
////            ." AND request2.id <> request.id"
////        );
//
////        $dql->andWhere( "EXISTS (SELECT 1".
////            " FROM AppVacReqBundle:VacReqRequest as request2 ".$joinStr
////            ." WHERE request2.user = request.user AND request2.id <> request.id"
////            //." AND NOT (requestType.startDate >= requestType2.endDate OR requestType.endDate <= requestType2.startDate)"  //detect overlap
////            ." AND (requestType.startDate < requestType2.startDate AND requestType.endDate < requestType2.startDate)"     //no overlap
////            .")"
////        );
//
//        //select user, distinct start, end dates
//        //$dql->addSelect("DISTINCT (requestBusiness.startDate) as startDate ");
//        //$dql->groupBy('requestBusiness.startDate','requestBusiness.endDate','requestVacation.startDate','requestVacation.endDate');
//        //$dql->groupBy('request.user,requestBusiness.startDate,requestBusiness.endDate,requestVacation.startDate,requestVacation.endDate');
//        //$dql->distinct('requestBusiness.startDate','requestBusiness.endDate','requestVacation.startDate','requestVacation.endDate');
//        //$dql->groupBy('request.user,requestType.startDate,requestType.endDate');
//        //$dql->groupBy('request.id');
//        //}
//
//        $query = $dql->getQuery(); //$query = $this->em->createQuery($dql);
//
//        //echo "query=".$query->getSql()."<br>";
//        //echo "dql=".$dql."<br>";
//
//        $query->setParameters( array(
//            'userId' => $user->getId(),
//            'status' => $status
//        ));
//
//        if( $asObject ) {
//            $requests = $query->getResult();
//            return $requests;
//        } else {
//
//            //testing
//            $requests = $query->getResult();
//            foreach( $requests as $request ) {
//                $thisNumberOfDays = $request->getTotalDays($status,$requestTypeStr);
//                $finalStartEndDatesArr = $request->getFinalStartEndDates();
//                $startendStr = $finalStartEndDatesArr['startDate']->format('Y/m/d')."-".$finalStartEndDatesArr['endDate']->format('Y/m/d');
//                echo "request = ".$request->getId()." ".$startendStr.": days=".$thisNumberOfDays."<br>";
//                $numberOfDays = $numberOfDays + (int)$thisNumberOfDays;
//            }
//            echo "### get numberOfDays = ".$numberOfDays."<br><br>";
//            return $numberOfDays;
//            //EOF testing
//
//            if(0) {
//                $numberOfDaysRes = $query->getSingleResult();
//                $numberOfDays = $numberOfDaysRes['numberOfDays'];
//            } else {
//                //$numberOfDaysRes = $query->getOneOrNullResult();
//                $numberOfDaysItems = $query->getResult();
//                if( $numberOfDaysItems ) {
//                    echo "numberOfDaysItems count=".count($numberOfDaysItems)."<br>";
//                    //$numberOfDaysItems = $numberOfDaysRes['numberOfDays'];
//                    if( count($numberOfDaysItems) > 1 ) {
//                        //$logger = $this->container->get('logger');
//                        //$logger->warning('Logical error: found more than one SUM: count='.count($numberOfDaysItems));
//                    }
//                    foreach( $numberOfDaysItems as $numberOfDaysItem ) {
//                        echo "+numberOfDays = ".$numberOfDaysItem['numberOfDays']."; count=".$numberOfDaysItem['totalCount']."<br>";
//                        $numberOfDays = $numberOfDays + $numberOfDaysItem['numberOfDays'];
//                    }
//                    echo "### get numberOfDays = ".$numberOfDays."<br><br>";
//                }
//            }
//        }
//
//        return $numberOfDays;
//    }

    //$asObject=false => Return number of days or
    //$asObject=true => Return specific requests.
    // If $requestTypeStr=business/requestBusiness => return business requests
    // If $requestTypeStr=vacation/requestVacation => return vacation requests
    public function getApprovedYearDays(
        $user,
        $requestTypeStr,    //business/vacation
        $startStr=null,     //period start date (1 July 2023)
        $endStr=null,       //period end date (30 June 2023)
        $type=null,
        $asObject=false,
        $status='approved'
        //$bruteForce=false
    ) {

        //testing
//        $startStr = "2023-06-29";
//        $endStr = "2023-07-02";
//        $startStr = "2023-07-02";
//        $endStr = "2023-06-29";

        //echo $type.": requestTypeStr=".$requestTypeStr."<br>";
        $numberOfDays = 0;

        //process.py script: replaced namespace by ::class: ['AppVacReqBundle:VacReqRequest'] by [VacReqRequest::class]
        $repository = $this->em->getRepository(VacReqRequest::class);
        $dql =  $repository->createQueryBuilder("request");

//        if( $bruteForce == true ) {
//            $asObject = true;
//        }

        if( $asObject ) {
            $dql->select('request');
        } else {
            $dql->select('DISTINCT user.id, requestType.startDate as startDate, requestType.endDate as endDate, requestType.numberOfDays as numberOfDays');
            //$dql->select('SUM(requestType.numberOfDays) as numberOfDays');
        }

//        $dql->innerJoin(
//            "AppVacReqBundle:VacReqRequest",
//            "request2",
//            "WITH",
//            "request2.id <> request.id AND request2.user = request.user"
//        );

        $dql->leftJoin("request.user", "user");

        if( $requestTypeStr == 'business' || $requestTypeStr == 'requestBusiness' ) {
            $dql->leftJoin("request.requestBusiness", "requestType");
            //$dql->leftJoin("request2.requestBusiness", "requestType2");
            //$joinStr = " LEFT JOIN request2.requestBusiness requestType2";
        }

        if( $requestTypeStr == 'vacation' || $requestTypeStr == 'requestVacation' ) {
            $dql->leftJoin("request.requestVacation", "requestType");
            //$dql->leftJoin("request2.requestVacation", "requestType2");
            //$joinStr = " LEFT JOIN request2.requestVacation requestType2";
        }

        $dql->where("requestType.id IS NOT NULL AND user.id = :userId AND requestType.status = :status");

        // |----|year|-----start-----end-----|year+1|----|
        // |----|2015-07-01|-----start-----end-----|2016-06-30|----|
        if( $type == "inside" && $startStr && $endStr ) {
            //echo "range=".$startStr." > ".$endStr."<br>";
            $dql->andWhere("requestType.startDate >= '" . $startStr . "'" . " AND requestType.endDate <= " . "'" . $endStr . "'");
        }

        // |-----start-----|year|-----end-----|year+1|----|
        // |-----rstart-----|2015-07-01|-----rend-----|2016-06-30|----|
        if( $type == "before" && $startStr ) {
            //echo "startStr=".$startStr."<br>";
            $dql->andWhere("requestType.startDate < '" . $startStr . "'" . " AND requestType.endDate > '".$startStr."'"); // . " AND requestType.endDate > " . "'" . $startStr . "'");
        }

        // |----|year|-----start-----|year+1|-----end-----|
        // |----|2015-07-01|-----start-----|2016-06-30|-----end-----|
        if( $type == "after" && $startStr && $endStr ) {
            //echo "sql endStr=".$endStr."<br>";
            //$dql->andWhere("requestType.endDate > '" . $endStr . "'" . " AND requestType.startDate < '".$endStr."'");  // . " AND requestType.endDate < " . "'" . $endStr . "'");
            //$dql->andWhere("requestType.startDate > '" . $startStr . "'" . " AND requestType.endDate > " . "'" . $endStr . "'");
            $dql->andWhere("requestType.startDate < '" . $endStr . "'" . " AND requestType.endDate > '".$endStr."'");
        }

        $query = $dql->getQuery(); //$query = $this->em->createQuery($dql);

        //echo "query=".$query->getSql()."<br>";
        //echo "dql=".$dql."<br>";

        $query->setParameters( array(
            'userId' => $user->getId(),
            'status' => $status
        ));

        //bruteForce not used!!! Instead, we prevent to submit and approve overlap requests
//        if( 0 && $bruteForce == true ) {
//            $requests = $query->getResult();
//            $numberOfDays = $this->getNotOverlapNumberOfWorkingDays($requests,$requestTypeStr);
//            //echo "bruteForce days=".$numberOfDays."<br>";
//            return $numberOfDays;
//        }

        if( $asObject ) {
            $requests = $query->getResult();
            return $requests;
        } else {

//            //sum the number of days
//            $requests = $query->getResult();
//            foreach( $requests as $request ) {
//                $thisNumberOfDays = $request->getTotalDays($status,$requestTypeStr);
//                $finalStartEndDatesArr = $request->getFinalStartEndDates();
//                $startendStr = $finalStartEndDatesArr['startDate']->format('Y/m/d')."-".$finalStartEndDatesArr['endDate']->format('Y/m/d');
//                echo "request = ".$request->getId()." ".$startendStr.": days=".$thisNumberOfDays."<br>";
//                $numberOfDays = $numberOfDays + (int)$thisNumberOfDays;
//            }
//            echo "### get numberOfDays = ".$numberOfDays."<br><br>";
//            return $numberOfDays;
//            //EOF sum the number of days

            //sum and return number of days
            if(0) {
                $numberOfDaysRes = $query->getSingleResult();
                $numberOfDays = $numberOfDaysRes['numberOfDays'];
                //echo $status.": numberOfDays=".$numberOfDays."<br>";
            } else {
                //$numberOfDaysRes = $query->getOneOrNullResult();
                $numberOfDaysItems = $query->getResult();
                if( $numberOfDaysItems ) {
                    //echo $status.": numberOfDaysItems count=".count($numberOfDaysItems)."<br>";
                    //$numberOfDaysItems = $numberOfDaysRes['numberOfDays'];
                    if( count($numberOfDaysItems) > 1 ) {
                        //$logger = $this->container->get('logger');
                        //$logger->warning('Logical error: found more than one SUM: count='.count($numberOfDaysItems));
                    }
                    foreach( $numberOfDaysItems as $numberOfDaysItem ) {
                        //echo "+numberOfDays = ".$numberOfDaysItem['numberOfDays']."; count=".$numberOfDaysItem['totalCount']."<br>";
                        //echo $status.": +numberOfDays = ".$numberOfDaysItem['numberOfDays']."<br>";
                        $numberOfDays = $numberOfDays + $numberOfDaysItem['numberOfDays'];

                        //TODO: adjust to holidays here or give a warning?
                    }
                    //echo "### get numberOfDays = ".$numberOfDays."<br><br>";
                }
            }
        }

        return $numberOfDays;
    }

//    //check for overlapped requests
//    public function getNotOverlapNumberOfWorkingDays( $requests, $requestTypeStr ) {
//        $logger = $this->container->get('logger');
//        $overlap = false;
//        $numberOfDays = 0;
//        $overlapRequests = array();
//        $dateRanges = array();
//        foreach( $requests as $request ) {
//            echo "request=".$request->getId()."<br>";
//            $thisDateRange = $request->getFinalStartEndDates($requestTypeStr);
//
//            foreach( $dateRanges as $requestId=>$dateRange ) {
//                $msg = "";
//                //overlap condition: (StartA <= EndB) and (EndA >= StartB)
//                if( ($thisDateRange['startDate'] <= $dateRange['endDate']) && ($thisDateRange['endDate'] >= $dateRange['startDate']) ) {
//                    //overlap!
//                    if( $thisDateRange['startDate'] == $dateRange['startDate'] && $thisDateRange['endDate'] == $dateRange['endDate'] ) {
//                        $msg = "Exact ";
//                    } else {
//                        $startDateStr = $thisDateRange['startDate']->format('Y/m/d');
//                        $endDateStr = $thisDateRange['endDate']->format('Y/m/d');
//                        $msg .= $requestId . ": overlap dates ID=" . $request->getId() .
//                            "; status=" . $request->getStatus() . "; dates=" . $startDateStr . "-" . $endDateStr.
//                            "; created=".$request->getCreateDate()->format('Y/m/d');
//                        echo $msg . "<br>";
//                        $logger->error($msg);
//                        $overlap = true;
//                        $overlapRequests[] = $request;
//                    }
//                    //TODO:
//                } else {
//                    //not overlap
//                    $dateRanges[$request->getId()] = $thisDateRange;
//                    $days = $request->getTotalDaysByType($requestTypeStr);
//                    $numberOfDays = $numberOfDays + $days;
//                    echo "not overlap dates; days=".$days."<br>";
//                }
//            }
//
//            if( count($dateRanges) == 0 ) {
//                $dateRanges[$request->getId()] = $thisDateRange;
//            }
//        }
//
//        return $overlapRequests;
//        //return $overlap;
//        //echo "unique days=".$numberOfDays."<br>";
//        return $numberOfDays;
//    }

    public function getHeaderOverlappedMessage($user) {
        //check for overlapped requests
        $overlappedMessage = null;
        $overlapRequests = $this->getOverlappedUserRequests($user);
        if( count($overlapRequests) > 0 ) {
            $overlappedRequestHrefs = array();
            foreach( $overlapRequests as $overlapRequest ) {
                $overlapRequestLink = $this->container->get('router')->generate(
                    'vacreq_show',
                    array(
                        'id' => $overlapRequest->getId(),
                    )
                //UrlGeneratorInterface::ABSOLUTE_URL
                );
                $thisDateRange = $overlapRequest->getFinalStartEndDates('requestVacation');
                //$startDateStr = $thisDateRange['startDate']->format('Y/m/d');
                //$endDateStr = $thisDateRange['endDate']->format('Y/m/d');
                $thisDateRange = "(".$thisDateRange['startDate']->format('Y/m/d')."-".$thisDateRange['endDate']->format('Y/m/d').")";
                $overlapRequestHref = '<a href="'.$overlapRequestLink.'">ID #'.$overlapRequest->getId().' '.$thisDateRange.'</a>';
                $overlappedRequestHrefs[] = $overlapRequestHref;
            }
            $overlappedMessage = "You have ".count($overlapRequests)." overlapping approved vacation request(s) for the current academic year: <br>".implode("<br>",$overlappedRequestHrefs);
            $overlappedMessage .= "<br>This will affect the accuracy of the calculations of the total approved and carry over days.";
            $overlappedMessage .= "<br>You can fix these overlapped vacation requests by canceling them (click a 'Request Cancellation' action link in 'My Requests' page).";
        }
        return $overlappedMessage;
    }

    //Get all user's requests by year range "2021-2022"
    //$yearRangeStr: '2021-2022'
    //$requestTypeStr: 'business' or 'vacation'
    //$status: 'approved'
    public function getRequestsByUserYears( $user, $yearRangeStr, $requestTypeStr, $status=null ) {

//        $userSecUtil = $this->container->get('user_security_utility');
//        //academicYearStart
//        $academicYearStart = $userSecUtil->getSiteSettingParameter('academicYearStart','vacreq');
//        if( !$academicYearStart ) {
//            $academicYearStart = \DateTime::createFromFormat('Y-m-d', date("Y")."-07-01");
//            //throw new \InvalidArgumentException('academicYearStart is not defined in Site Parameters.');
//        }
//        //academicYearEnd
//        $academicYearEnd = $userSecUtil->getSiteSettingParameter('academicYearEnd','vacreq');
//        if( !$academicYearEnd ) {
//            $academicYearEnd = \DateTime::createFromFormat('Y-m-d', date("Y")."-06-30");
//            //throw new \InvalidArgumentException('academicYearEnd is not defined in Site Parameters.');
//        }
        $academicYearStart = $this->getAcademicYearStart();
        $academicYearEnd = $this->getAcademicYearEnd();

        //constract start and end date for DB select "Y-m-d"
        $academicYearStartStr = $academicYearStart->format('m-d');

        //years
        $yearRangeArr = $this->getYearsFromYearRangeStr($yearRangeStr);
        $previousYear = $yearRangeArr[0];
        $currentYear = $yearRangeArr[1];

        $academicYearStartStr = $previousYear."-".$academicYearStartStr;
        //echo "current academicYearStartStr=".$academicYearStartStr."<br>";
        //academicYearEnd
        $academicYearEndStr = $academicYearEnd->format('m-d');

        $academicYearEndStr = $currentYear."-".$academicYearEndStr;
        //echo "current academicYearEndStr=".$academicYearEndStr."<br>";

        $parameters = array();

        //process.py script: replaced namespace by ::class: ['AppVacReqBundle:VacReqRequest'] by [VacReqRequest::class]
        $repository = $this->em->getRepository(VacReqRequest::class);

        $dql =  $repository->createQueryBuilder("request");
        $dql->select('request');

        $dql->leftJoin("request.user", "user");
        //$dql->leftJoin("request.requestType", "requestType");

        if( $requestTypeStr == 'business' || $requestTypeStr == 'requestBusiness' ) {
            $dql->leftJoin("request.requestBusiness", "requestType");
        }

        if( $requestTypeStr == 'vacation' || $requestTypeStr == 'requestVacation' ) {
            $dql->leftJoin("request.requestVacation", "requestType");
        }

        $dql->andWhere("requestType.id IS NOT NULL");

        //$dql->where("requestType.id IS NOT NULL AND user.id = :userId AND requestType.status = :status");

        // |----|year|-----start-----end-----|year+1|----|
        // |----|2015-07-01|-----start-----end-----|2016-06-30|----|
        //echo "range=".$academicYearStartStr." > ".$academicYearEndStr."<br>";
        $dql->andWhere("requestType.startDate >= '" . $academicYearStartStr . "'" . " AND requestType.endDate <= " . "'" . $academicYearEndStr . "'");

        $dql->andWhere("user.id = :userId");
        $parameters['userId'] = $user->getId();

        if( $status ) {
            $dql->andWhere("requestVacation.status = :status");
            $parameters['status'] = $status;
        }

        $dql->orderBy('request.createDate', 'ASC');

        $query = $dql->getQuery(); //$query = $this->em->createQuery($dql);

        if( count($parameters) > 0 ) {
            $query->setParameters($parameters);
        }

        $requests = $query->getResult();
        //echo "requests to analyze=".count($requests)."<br>";

        return $requests;
    }

    //get all user's overlapped requests
    public function getOverlappedUserRequests( $user, $currentYear=true, $log=false ) {

        //1) get all user approved vacation requests
        //process.py script: replaced namespace by ::class: ['AppVacReqBundle:VacReqRequest'] by [VacReqRequest::class]
        $repository = $this->em->getRepository(VacReqRequest::class);

        $dql =  $repository->createQueryBuilder("request");
        $dql->select('request');

        $dql->leftJoin("request.user", "user");
        $dql->leftJoin("request.requestType", "requestType");
        $dql->leftJoin("request.requestVacation", "requestVacation");

        $dql->where("requestType.abbreviation = 'business-vacation'");
        $dql->andWhere("requestVacation.status = 'approved'");
        $dql->andWhere("user.id = ".$user->getId());

        if( $currentYear ) {
            $dates = $this->getCurrentAcademicYearStartEndDates();
            //echo "dates=".$dates['startDate']." == ".$dates['endDate']."<br>";
            $currentYearStartDate = $dates['startDate'];
            $dql->andWhere("requestVacation.startDate > '".$currentYearStartDate."'");
        }

        $dql->orderBy('request.createDate', 'ASC');

        $query = $dql->getQuery(); //$query = $this->em->createQuery($dql);

        $requests = $query->getResult();
        //echo "requests to analyze=".count($requests)."<br>";

        $overlapRequests = $this->checkOverlapRequests($requests,'requestVacation',$log);
        //echo "overlapRequests=".count($overlapRequests)."<br>";

        return $overlapRequests;
    }
    //check for overlapped requests
    public function checkOverlapRequests( $requests, $requestTypeStr, $log=false ) {
        $logger = $this->container->get('logger');
        //$overlap = false;
        $overlapRequests = new ArrayCollection();
        $dateRanges = array();
        foreach( $requests as $request ) {

            $thisDateRange = $request->getFinalStartEndDates($requestTypeStr);
            //echo "check ID=" .$request->getId(). "<br>";

            foreach( $dateRanges as $requestId=>$requestInfo ) {

                $dateRange = $requestInfo['dateRange'];
                $currentRequest = $requestInfo['request'];

                //overlap condition: (StartA <= EndB) and (EndA >= StartB)
                if( ($thisDateRange['startDate'] <= $dateRange['endDate']) && ($thisDateRange['endDate'] >= $dateRange['startDate']) ) {
                    //overlap!
                    //echo "overlap ID=" .$requestId. "<br>";

                    //$overlapRequests[] = $request;
                    if( !$overlapRequests->contains($request) ) {
                        $overlapRequests->add($request);
                    }
                    if( !$overlapRequests->contains($currentRequest) ) {
                        $overlapRequests->add($currentRequest);
                    }

                    if( $thisDateRange['startDate'] == $dateRange['startDate'] && $thisDateRange['endDate'] == $dateRange['endDate'] ) {
                        $msgPrefix = "Exact";
                    } else {
                        $msgPrefix = "";
                        //$overlap = true;
                        //$overlapRequests[] = $request;
                    }

                    if( $log ) {
                        $firstRequest = "#".$requestId."(".$dateRange['startDate']->format('Y/m/d')."-".$dateRange['endDate']->format('Y/m/d').")";

                        $startDateStr = $thisDateRange['startDate']->format('Y/m/d');
                        $endDateStr = $thisDateRange['endDate']->format('Y/m/d');
                        $secondRequest = "#".$request->getId()."(".$startDateStr."-".$endDateStr.")";

                        $msg = $msgPrefix. " Overlap: ".$firstRequest." and " . $secondRequest;
                        //"; created=".$request->getCreateDate()->format('Y/m/d');

                        //echo $msg . "<br>";
                        $logger->error($msg);
                    }

                } else {
                    //not overlap
                    //echo "not overlap ID=" .$requestId. "<br>";
                    //$dateRanges[$request->getId()] = $thisDateRange;
                    $dateRanges[$request->getId()] = array('dateRange'=>$thisDateRange,'request'=>$request);
                }

            }//foreach $dateRanges

            if( count($dateRanges) == 0 ) {
                $dateRanges[$request->getId()] = array('dateRange'=>$thisDateRange,'request'=>$request);
            }

        }//foreach $requests

        //return $overlap;
        return $overlapRequests;
    }
    public function checkRequestForOverlapDates( $user, $subjectRequest ) {
        //$logger = $this->container->get('logger');
        //get all user approved vacation requests
        $requestTypeStr = "requestVacation";

        //process.py script: replaced namespace by ::class: ['AppVacReqBundle:VacReqRequest'] by [VacReqRequest::class]
        $repository = $this->em->getRepository(VacReqRequest::class);

        $dql =  $repository->createQueryBuilder("request");
        $dql->select('request');

        $dql->leftJoin("request.user", "user");
        $dql->leftJoin("request.requestType", "requestType");
        $dql->leftJoin("request.requestVacation", "requestVacation");

        $dql->where("requestType.abbreviation = 'business-vacation'");
        $dql->andWhere("requestVacation.status = 'approved'");
        //$dql->andWhere("user.id = ".$user->getId());
        $dql->andWhere("user.id = :userId");
        $dql->andWhere("request.id != :requestId");

        $dql->orderBy('request.id');

        $query = $dql->getQuery(); //$query = $this->em->createQuery($dql);

        $query->setParameter('userId', $user->getId());
        $query->setParameter('requestId', $subjectRequest->getId());

        $requests = $query->getResult();
        //EOF get all user approved vacation requests

        $overlappedRequests = array();

        $subjectDateRange = $subjectRequest->getFinalStartEndDates($requestTypeStr);
        //dump($subjectDateRange);
        if( !$subjectDateRange ) {
            return $overlappedRequests;
        }
        if( is_array($subjectDateRange) && count($subjectDateRange) == 0 ) {
            return $overlappedRequests;
        }

        //$overlappedIds = array();
        foreach( $requests as $request ) {
            //echo 'check reqid='.$request->getId()."<br>";
            $thisDateRange = $request->getFinalStartEndDates($requestTypeStr);

            $thisStartDate = $thisDateRange['startDate'];
            $subjectStartDate = $subjectDateRange['startDate'];
            //echo "subjectStartDate=$subjectStartDate <br>";
            $thisEndDate = $thisDateRange['endDate'];
            $subjectEndDate = $subjectDateRange['endDate'];
            if(
                ($thisStartDate <= $subjectStartDate)
                &&
                ($thisEndDate >= $subjectEndDate)
            )
            {
                $overlappedRequests[] = $request;
            }

            //$msg = "";
            //overlap condition: (StartA <= EndB) and (EndA >= StartB)
//            if( ($thisDateRange['startDate'] <= $subjectDateRange['endDate']) && ($thisDateRange['endDate'] >= $subjectDateRange['startDate']) ) {
//                $overlappedRequests[] = $request;
//            }

        }//foreach requests
        return $overlappedRequests;
    }
    public function hasOverlappedExactly( $subjectRequest, $overlappedRequests ) {
        $requestTypeStr = "requestVacation";
        $subjectDateRange = $subjectRequest->getFinalStartEndDates($requestTypeStr);
        foreach( $overlappedRequests as $overlappedRequest ) {
            $thisDateRange = $overlappedRequest->getFinalStartEndDates($requestTypeStr);
            $thisStartDate = $thisDateRange['startDate'];
            $subjectStartDate = $subjectDateRange['startDate'];
            $thisEndDate = $thisDateRange['endDate'];
            $subjectEndDate = $subjectDateRange['endDate'];
            if(
                ($thisStartDate == $subjectStartDate)
                &&
                ($thisEndDate == $subjectEndDate)
            )
            {
                return true;
            }
//            if(
//                ($thisDateRange['startDate'] == $subjectDateRange['startDate'])
//                &&
//                ($thisDateRange['endDate'] == $subjectDateRange['endDate']) )
//            {
//                return true;
//            }

        }
        return false;
    }
    public function getOverlappedMessage( $subjectRequest, $overlappedRequests, $absolute=null, $short=false ) {
        //$errorMsg = 'This request ID #'.$entity->getId().' has overlapped vacation date range with a previous approved vacation request(s) with ID #' . implode(',', $overlappedRequestIds);
        $errorMsg = null;
        //Your request includes dates (MM/DD/YYYY, MM/DD/YYYY, MM/DD/YYYY) already covered by your previous request(s) (Request ID LINK #1, Request ID LINK #2, Request ID LINK #3). Please exclude these dates from this request before re-submitting.
        if( count($overlappedRequests) > 0 ) {
            $dates = array();
            $hrefs = array();
            foreach( $overlappedRequests as $overlappedRequest ) {
                //echo "overlapped re id=".$overlappedRequest->getId()."<br>";

                $finalDates = $overlappedRequest->getFinalStartEndDates('requestVacation');
                $dates[] = $finalDates['startDate']->format('m/d/Y')."-".$finalDates['endDate']->format('m/d/Y');

                if( $absolute ) {
                    $absoluteFlag = UrlGeneratorInterface::ABSOLUTE_URL;
                } else {
                    $absoluteFlag = UrlGeneratorInterface::ABSOLUTE_PATH;
                }
                $link = $this->container->get('router')->generate(
                    'vacreq_show',
                    array(
                        'id' => $overlappedRequest->getId(),
                    ),
                    $absoluteFlag
                    //UrlGeneratorInterface::ABSOLUTE_URL
                );
                if( $absolute ) {
                    $href = 'Request ID '.$overlappedRequest->getId()." ".$link;
                } else {
                    $href = '<a href="'.$link.'">Request ID '.$overlappedRequest->getId().'</a>';
                }

                $hrefs[] = $href;
            }

            $errorMsg = "This request includes dates ".implode(", ",$dates)." already covered by your previous request(s) (".implode(", ",$hrefs).").";

            if( !$short ) {
                $errorMsg .= " Please exclude these dates from this request before submitting.";
            }
        }

        return $errorMsg;
    }

    public function getNumberOfWorkingDaysBetweenDates( $starDate, $endDate ) {
        $starDateStr = $starDate->format('Y-m-d');
        $endDateStr = $endDate->format('Y-m-d');
        //echo $starDateStr . " --- " . $endDateStr ."<br>"; //2023-06-29 --- 2023-06-30
        //exit('111');
        $holidays = array();
        //$holidays = ['*-12-25', '*-01-01', '*-07-04']; # variable and fixed holidays
        //return $this->getWorkingDays($starDateStr,$endDateStr,$holidays);
        return $this->number_of_working_days($starDateStr,$endDateStr,$holidays);
    }
    //http://stackoverflow.com/questions/336127/calculate-business-days
    //The function returns the no. of business days between two dates and it skips the holidays
    function getWorkingDays($startDate,$endDate,$holidays){
        // do strtotime calculations just once
        $endDate = strtotime($endDate);
        $startDate = strtotime($startDate);


        //The total number of days between the two dates. We compute the no. of seconds and divide it to 60*60*24
        //We add one to inlude both dates in the interval.
        $days = ($endDate - $startDate) / 86400 + 1;

        $no_full_weeks = floor($days / 7);
        $no_remaining_days = fmod($days, 7);

        //It will return 1 if it's Monday,.. ,7 for Sunday
        $the_first_day_of_week = date("N", $startDate);
        $the_last_day_of_week = date("N", $endDate);

        //---->The two can be equal in leap years when february has 29 days, the equal sign is added here
        //In the first case the whole interval is within a week, in the second case the interval falls in two weeks.
        if ($the_first_day_of_week <= $the_last_day_of_week) {
            if ($the_first_day_of_week <= 6 && 6 <= $the_last_day_of_week) $no_remaining_days--;
            if ($the_first_day_of_week <= 7 && 7 <= $the_last_day_of_week) $no_remaining_days--;
        }
        else {
            // (edit by Tokes to fix an edge case where the start day was a Sunday
            // and the end day was NOT a Saturday)

            // the day of the week for start is later than the day of the week for end
            if ($the_first_day_of_week == 7) {
                // if the start date is a Sunday, then we definitely subtract 1 day
                $no_remaining_days--;

                if ($the_last_day_of_week == 6) {
                    // if the end date is a Saturday, then we subtract another day
                    $no_remaining_days--;
                }
            }
            else {
                // the start date was a Saturday (or earlier), and the end date was (Mon..Fri)
                // so we skip an entire weekend and subtract 2 days
                $no_remaining_days -= 2;
            }
        }

        //The no. of business days is: (number of weeks between the two dates) * (5 working days) + the remainder
        //---->february in none leap years gave a remainder of 0 but still calculated weekends between first and last day, this is one way to fix it
        $workingDays = $no_full_weeks * 5;
        if ($no_remaining_days > 0 )
        {
            $workingDays += $no_remaining_days;
        }

        //We subtract the holidays
        foreach($holidays as $holiday){
            $time_stamp=strtotime($holiday);
            //If the holiday doesn't fall in weekend
            if ($startDate <= $time_stamp && $time_stamp <= $endDate && date("N",$time_stamp) != 6 && date("N",$time_stamp) != 7)
                $workingDays--;
        }

        return $workingDays;
    }
    function number_of_working_days($from, $to, $holidayDays) {
        $workingDays = [1, 2, 3, 4, 5]; # date format = N (1 = Monday, ...)
        //$holidayDays = ['*-12-25', '*-01-01', '2013-12-23']; # variable and fixed holidays

        $from = new \DateTime($from);
        $to = new \DateTime($to);
        $to->modify('+1 day');
        $interval = new \DateInterval('P1D');
        $periods = new \DatePeriod($from, $interval, $to);

        $days = 0;
        foreach ($periods as $period) {
            if (!in_array($period->format('N'), $workingDays)) continue;
            if (in_array($period->format('Y-m-d'), $holidayDays)) continue;
            if (in_array($period->format('*-m-d'), $holidayDays)) continue;
            $days++;
        }
        return $days;
    }

    //construct the academic year edge date between request's start and end dates, if request is completely inside then return null
    //$position describes which academic year edge to construct. In case if the request spans over couple years (probably impossible case).
    // rstart--|y|--|y+1|--|y+2|--rend => position==first=>return y; position==last=>return y+2
    public function getAcademicYearEdgeDateBetweenRequestStartEnd( $request, $position="first" ) {
        //$userSecUtil = $this->container->get('user_security_utility');

        $finalStartEndDates = $request->getFinalStartEndDates();
        $finalStartDate = $finalStartEndDates['startDate'];
        $finalEndDate = $finalStartEndDates['endDate'];
        $startDateMD = $finalStartDate->format('m-d');
        $endDateMD = $finalEndDate->format('m-d');

        //academicYearStart
//        $academicYearStart = $userSecUtil->getSiteSettingParameter('academicYearStart','vacreq');
//        if( !$academicYearStart ) {
//            $academicYearStart = \DateTime::createFromFormat('Y-m-d', date("Y")."-07-01");
//            //throw new \InvalidArgumentException('academicYearStart is not defined in Site Parameters.');
//        }
        $academicYearStart = $this->getAcademicYearStart();
        //academicYearStart String
        $academicYearStartMD = $academicYearStart->format('m-d');

        //academicYearEnd: June 30
//        $academicYearEnd = $userSecUtil->getSiteSettingParameter('academicYearEnd','vacreq');
//        if( !$academicYearEnd ) {
//            $academicYearEnd = \DateTime::createFromFormat('Y-m-d', date("Y")."-06-30");
//            //throw new \InvalidArgumentException('academicYearEnd is not defined in Site Parameters.');
//        }
        $academicYearEnd = $this->getAcademicYearEnd();
        //academicYearEnd String
        $academicYearEndMD = $academicYearEnd->format('m-d');

        //check if request is completely inside then return null
        if( $academicYearStart < $startDateMD && $academicYearEndMD > $endDateMD ) {
            //echo "request is completely inside";
            return null;
        }

        // rstart--|y|--|y+1|--|y+2|--rend => position==first=>return y; position==last=>return y+2
        //return y-$academicYearStartMD
        if( $position == "first" ) {
            $year = $finalStartDate->format('Y');
            $academicYearEdgeStr = $year . "-" . $academicYearStartMD;
        }

        //return (y+2)-$academicYearEndMD
        if( $position == "last" ) {
            $year = $finalEndDate->format('Y');
            $academicYearEdgeStr = $year . "-" . $academicYearEndMD;
        }

        return $academicYearEdgeStr;
    }
    //construct date string of the request's academical year edge - start (2016-06-30) or end (2016-07-01)
    public function getRequestEdgeAcademicYearDate( $request, $edge ) {
        $userSecUtil = $this->container->get('user_security_utility');

        //academicYearEdge
        $academicYearEdge = $userSecUtil->getSiteSettingParameter('academicYear'.$edge,'vacreq');
        if( !$academicYearEdge ) {
            throw new \InvalidArgumentException('academicYear'.$edge.' is not defined in Site Parameters.');
        }

        //academicYearEdge
        $academicYearEdgeStr = $academicYearEdge->format('m-d');

        //get request's academic year
        $academicYearArr = $this->getRequestAcademicYears($request);
        if( count($academicYearArr) > 0 ) {
            $yearsArr = $this->getYearsFromYearRangeStr($academicYearArr[0]);
            $edgeYear = $yearsArr[0];
            $academicYearEdgeStr = $edgeYear."-".$academicYearEdgeStr;
        } else {
            throw new \InvalidArgumentException("Request's academic ".$edge." year is not defined.");
        }
        //echo "academicYearEdgeStr=".$academicYearEdgeStr."<br>";

        return $academicYearEdgeStr;
    }

    //construct date string of the academical year edge - start (2016-06-30) or end (2016-07-01)
    public function getEdgeAcademicYearDate( $year, $edge ) {
        $userSecUtil = $this->container->get('user_security_utility');
        $academicYearEdge = NULL;
        $academicYearEdgeStr = NULL;

        //academicYearEdge
        $academicYearEdge = $userSecUtil->getSiteSettingParameter('academicYear'.$edge,'vacreq');

        ///// Testing: local get academicYearStart/academicYearEnd
//        $userServiceUtil = $this->container->get('user_service_utility');
//        $param = $userServiceUtil->getSingleSiteSettingParameter();
//        echo "param ID=[".$param->getId()."]<br>";
//        $specificSiteSettingParameter = $param->getVacreqSiteParameter();
//        echo "specificSiteSettingParameter ID=[".$specificSiteSettingParameter->getId()."]<br>";
//        if( $edge == "Start" || $edge == "start" ) {
//            $academicYearEdge = $specificSiteSettingParameter->getAcademicYearStart();
//        }
//        if( $edge == "End" || $edge == "end" ) {
//            $academicYearEdge = $specificSiteSettingParameter->getAcademicYearEnd();
//        }
        ///// Testing //////

        if( !$academicYearEdge ) {
            throw new \InvalidArgumentException('academicYear'.$edge.' is not defined in Site Parameters.');
        }
        //echo "academicYearEdge Str=[".$academicYearEdge->format('m-d-Y H:s:i')."]<br>";

//        //Testing
//        $userServiceUtil = $this->container->get('user_service_utility');
//        $param = $userServiceUtil->getSingleSiteSettingParameter();
//        $getterSiteParameter = "get".'vacreq'."SiteParameter";
//        $specificSiteSettingParameter = $param->$getterSiteParameter();
//        $resStart = $specificSiteSettingParameter->getAcademicYearStart();
//        echo 'testing $resStart='.$resStart->format('Y-m-d H:i:s T')."<br>";
//        $param = NULL;
//
//        $param = $userServiceUtil->getSingleSiteSettingParameter();
//        $getterSiteParameter = "get".'vacreq'."SiteParameter";
//        $specificSiteSettingParameter = $param->$getterSiteParameter();
//        $resEnd = $specificSiteSettingParameter->getAcademicYearEnd();
//        echo 'testing $resEnd='.$resEnd->format('Y-m-d H:i:s T')."<br>";
//        //EOF Testing

        //academicYearEdge
        //echo 'academicYearEdgeStr='.$academicYearEdge->format('Y-m-d H:i:s T')."<br>";
        $academicYearEdgeStr = $academicYearEdge->format('m-d');
        //echo "year=$year, edge=[$edge], academicYearEdgeStr=[".$academicYearEdgeStr."]<br>";

        if( $edge == "Start" || $edge == "start" ) {
            $year = (int)$year - 1;
        }

        $academicYearEdgeStr = $year."-".$academicYearEdgeStr;
        //echo "academicYearEdgeStr=".$academicYearEdgeStr."<br>";

        //$this->em->detach($academicYearEdge);

        return $academicYearEdgeStr;
    }
    //Testing
//    public function getSiteSettingsStartEndAcademicDates() {
//        //Testing: local get academicYearStart/academicYearEnd
//        $userServiceUtil = $this->container->get('user_service_utility');
//        $param = $userServiceUtil->getSingleSiteSettingParameter();
//        echo "param ID=[".$param->getId()."]<br>";
//        $specificSiteSettingParameter = $param->getVacreqSiteParameter();
//        echo "specificSiteSettingParameter ID=[".$specificSiteSettingParameter->getId()."]<br>";
//        $academicYearStart = $specificSiteSettingParameter->getAcademicYearStart();
//        echo "academicYearStart Str=[".$academicYearStart->format('m-d-Y H:s:i')."]<br>";
//        $academicYearEnd = $specificSiteSettingParameter->getAcademicYearEnd();
//        echo "academicYearEnd Str=[".$academicYearEnd->format('m-d-Y H:s:i')."]<br>";
//
//        return array(
//            'academicYearStart' => $academicYearStart->format('m-d'),
//            'academicYearEnd' => $academicYearEnd->format('m-d')
//        );
//    }

    public function getRequestAcademicYears( $request ) {

        $academicYearArr = array();

        if( $request->getRequestTypeAbbreviation() == "carryover" ) {
            if( $request->getSourceYear() && $request->getDestinationYear() ) {
                $sourceYear = $request->getSourceYear();
                $academicYearArr[] = $sourceYear."-".((int)$sourceYear+1);
                $destinationYear = $request->getDestinationYear();
                $academicYearArr[] = $destinationYear."-".((int)$destinationYear+1);
            }
            return $academicYearArr;
        }

        //return "2014-2015, 2015-2016";
        $academicYearStr = null;
//        $userSecUtil = $this->container->get('user_security_utility');
//        //academicYearStart: July 01
//        $academicYearStart = $userSecUtil->getSiteSettingParameter('academicYearStart','vacreq');
//        if( !$academicYearStart ) {
//            $academicYearStart = \DateTime::createFromFormat('Y-m-d', date("Y")."-07-01");
//            //throw new \InvalidArgumentException('academicYearStart is not defined in Site Parameters.');
//        }
//        //academicYearEnd: June 30
//        $academicYearEnd = $userSecUtil->getSiteSettingParameter('academicYearEnd','vacreq');
//        if( !$academicYearEnd ) {
//            $academicYearEnd = \DateTime::createFromFormat('Y-m-d', date("Y")."-06-30");
//            //throw new \InvalidArgumentException('academicYearEnd is not defined in Site Parameters.');
//        }
        $academicYearStart = $this->getAcademicYearStart();
        $academicYearEnd = $this->getAcademicYearEnd();

        //$res['academicYearStart'] = $academicYearStart;
        //$res['academicYearEnd'] = $academicYearEnd;

        $dates = $request->getFinalStartEndDates();
        $startDate = $dates['startDate'];
        $endDate = $dates['endDate'];

        //echo "startDate= ".$startDate->format('Y-m-d')."<br>";
        //echo "endDate= ".$endDate->format('Y-m-d')."<br>";

        $startDateMD = $startDate->format('m-d');
        $endDateMD = $endDate->format('m-d');

        $startYear = $startDate->format('Y');
        $endYear = $endDate->format('Y');

        //calculate year difference (span)
        //$yearDiff = $endYear - $startYear;
        //$yearDiff = $yearDiff + 1;

        $academicYearStartMD = $academicYearStart->format('m-d');
        //$academicStartDateStr = $startYear."-".$academicYearStartMD;
        //echo "academicStartDateStr= ".$academicStartDateStr."<br>";
        //$academicStartDate = new \DateTime($academicStartDateStr);

        //$endYear = $endYear + $yearDiff;
        $academicYearEndMD = $academicYearEnd->format('m-d');
        //$academicEndDateStr = $endYear."-".$academicYearEndMD;
        //echo "academicEndDateStr= ".$academicEndDateStr."<br>";
        //$academicEndDate = new \DateTime($academicEndDateStr);

        //case 1: start and end dates are inside of academic year
        //if( $startDateMD >= $academicYearStartMD && $endDateMD <= $academicYearEndMD ) {
            //echo "case 1: start and end dates are inside of academic year <br>";
        //}

        //case 2: start date is before start of academic year
        if( $startDateMD < $academicYearStartMD ) {
            //echo "case 2: start date is before start of academic year <br>";
            $startYear = $startYear - 1;
        }

        //case 3: end date is after end of academic year
        if( $endDateMD > $academicYearEndMD ) {
            //echo "case 3: end date is after end of academic year <br>";
            $endYear = $endYear + 1;
        }

        //$academicYearStr = "2014-2015, 2015-2016";
        //$academicYearStr = $startYear . "-" . $endYear;

        for( $year=$startYear; $year < $endYear; $year++ ) {
            //$academicYearStr = $startYear . "-" . $endYear;
            $endtyear = $year + 1;
            $academicYearArr[] = $year."-".$endtyear;
        }

        //$academicYearStr = implode(", ",$academicYearArr);

        return $academicYearArr;
    }

    //$yearOffset: 0=>current year, -1=>previous year
    //return format: Y-m-d
    public function getCurrentAcademicYearStartEndDates($asDateTimeObject=false, $yearOffset=null) {
        //$userSecUtil = $this->container->get('user_security_utility');
        //academicYearStart: July 01
//        $academicYearStart = $userSecUtil->getSiteSettingParameter('academicYearStart','vacreq');
//        if( !$academicYearStart ) {
//            $academicYearStart = \DateTime::createFromFormat('Y-m-d', date("Y")."-07-01");
//            //throw new \InvalidArgumentException('academicYearStart is not defined in Site Parameters.');
//        }
        //academicYearEnd: June 30
//        $academicYearEnd = $userSecUtil->getSiteSettingParameter('academicYearEnd','vacreq');
//        if( !$academicYearEnd ) {
//            $academicYearEnd = \DateTime::createFromFormat('Y-m-d', date("Y")."-06-30");
//            //throw new \InvalidArgumentException('academicYearEnd is not defined in Site Parameters.');
//        }
        $academicYearStart = $this->getAcademicYearStart();
        $academicYearEnd = $this->getAcademicYearEnd();

        $startDateMD = $academicYearStart->format('m-d');
        $endDateMD = $academicYearEnd->format('m-d');

        //$currentYear = new \DateTime();
        $nowDate = new \DateTime(); //2016-07-15
        //testing
        if( 0 ) {
            $nowDate = \DateTime::createFromFormat('Y-m-d', "2015-08-30"); //testing: expected 2015-2016
            $nowDate = \DateTime::createFromFormat('Y-m-d', "2016-06-30"); //testing: expected 2015-2016
            $nowDate = \DateTime::createFromFormat('Y-m-d', "2016-08-30"); //testing: expected 2016-2017
            $nowDate = \DateTime::createFromFormat('Y-m-d', "2017-01-30"); //testing: expected 2016-2017
            $nowDate = \DateTime::createFromFormat('Y-m-d', "2017-08-30"); //testing: expected 2017-2018
            $nowDate = \DateTime::createFromFormat('Y-m-d', "2018-08-26");
            //$nowDate = \DateTime::createFromFormat('Y-m-d', "2018-12-26");
        }

        $currentYear = $nowDate->format('Y'); //endDate

        //check if current date < academicYearStart date
        $academicYearStartDateStr = $currentYear."-".$startDateMD;
        $academicYearStartDate = \DateTime::createFromFormat('Y-m-d', $academicYearStartDateStr);
        //echo "compare: current date ".$nowDate->format('Y-M-d')." < ".$academicYearStartDate->format('Y-M-d')."<br>";
        if( $nowDate < $academicYearStartDate ) {
            $currentYear = $currentYear - 1; //testing
            //echo "adjust currentYear: $currentYear - 1<br>";
        }
        //echo "currentYear=".$currentYear."<br>";

        $previousYear = $currentYear - 1; //startDate

        $startDate = $previousYear."-".$startDateMD;
        $currentYearStartDate = \DateTime::createFromFormat('Y-m-d', $startDate);

        //echo "nowDate=".$nowDate->format('Y-M-d')."<br>";
        //echo "currentYearStartDate=".$currentYearStartDate->format('Y-M-d')."<br>";
        if( $nowDate > $currentYearStartDate ) {
            $previousYear = $currentYear;
            $currentYear = $currentYear + 1;
            //echo "nowDate>currentYearStartDate: "."previousYear=$previousYear"."; currentYear=$currentYear <br>";
        } else {
            //echo "else: previousYear=$previousYear"."; currentYear=$currentYear <br>";
        }

        if( $yearOffset ) {
            $previousYear = $previousYear + $yearOffset;
            $currentYear = $currentYear + $yearOffset;
        }

        $startDate = $previousYear."-".$startDateMD;
        $endDate = $currentYear."-".$endDateMD;
        //exit('<br> exit: startDate='.$startDate.'; endDate='.$endDate); //testing

        if( $asDateTimeObject ) {
            $startDate = \DateTime::createFromFormat('Y-m-d', $startDate);
            $endDate = \DateTime::createFromFormat('Y-m-d', $endDate);
        }

        return array(
            'startDate'=> $startDate,
            'endDate'=> $endDate,
        );
    }

    public function getAcademicYearStart() {
        $userSecUtil = $this->container->get('user_security_utility');
        $academicYearStart = $userSecUtil->getSiteSettingParameter('academicYearStart','vacreq');
        if( !$academicYearStart ) {
            $academicYearStart = $userSecUtil->getSiteSettingParameter('academicYearStart');
            //$academicYearStart = \DateTime::createFromFormat('Y-m-d', date("Y")."-07-01");
            //throw new \InvalidArgumentException('academicYearStart is not defined in Site Parameters.');
        }
        if( !$academicYearStart ) {
            //$academicYearStart = $userSecUtil->getSiteSettingParameter('academicYearStart');
            $academicYearStart = \DateTime::createFromFormat('Y-m-d', date("Y")."-07-01");
            //throw new \InvalidArgumentException('academicYearStart is not defined in Site Parameters.');
        }
        return $academicYearStart;
    }
    public function getAcademicYearEnd() {
        $userSecUtil = $this->container->get('user_security_utility');
        $academicYearEnd = $userSecUtil->getSiteSettingParameter('academicYearEnd','vacreq');
        if( !$academicYearEnd ) {
            $academicYearEnd = $userSecUtil->getSiteSettingParameter('academicYearEnd');
            //$academicYearEnd = \DateTime::createFromFormat('Y-m-d', date("Y")."-06-30");
            //throw new \InvalidArgumentException('academicYearEnd is not defined in Site Parameters.');
        }
        if( !$academicYearEnd ) {
            //$academicYearEnd = $userSecUtil->getSiteSettingParameter('academicYearEnd');
            $academicYearEnd = \DateTime::createFromFormat('Y-m-d', date("Y")."-06-30");
            //throw new \InvalidArgumentException('academicYearEnd is not defined in Site Parameters.');
        }
        return $academicYearEnd;
    }

    public function getApprovedRequestStartedBetweenDates( $requestTypeStr, $startDate, $endDate ) {

        //process.py script: replaced namespace by ::class: ['AppVacReqBundle:VacReqRequest'] by [VacReqRequest::class]
        $repository = $this->em->getRepository(VacReqRequest::class);
        $dql = $repository->createQueryBuilder('request');

        if( $requestTypeStr == 'business' || $requestTypeStr == 'requestBusiness' ) {
            $dql->leftJoin("request.requestBusiness", "requestType");
        }

        if( $requestTypeStr == 'vacation' || $requestTypeStr == 'requestVacation' ) {
            $dql->leftJoin("request.requestVacation", "requestType");
        }

        $dql->where("requestType.id IS NOT NULL AND requestType.status = :statusApproved");
        $dql->andWhere('(requestType.startDate BETWEEN :startDate and :endDate)');

        $query = $dql->getQuery(); //$query = $this->em->createQuery($dql);

        $query->setParameter('statusApproved', 'approved');
        $query->setParameter('startDate', $startDate->format('Y-m-d H:i:s'));
        $query->setParameter('endDate', $endDate->format('Y-m-d H:i:s'));

        //echo "dql=".$dql."<br>";

        $requests = $query->getResult();

        //echo "count=".count($requests)."<br>";

        return $requests;
    }

    //IF the person is away on the date the page is being loaded (approved request), show the heading "Away" and under it show one of three lines:
    //Vacation: StartDate - EndDate, Back on BackDate
    //Business Travel: StartDate - EndDate, Back on BackDate
    //Vacation + Business Travel: StartDate - EndDate, Back on BackDate
    //Followed by (if the phone number, email, or "other" field was filled in):
    //Emergency Contact Via: Phone - [field-from-phone]; Email - [field form email]; Other - [field form other]__
    public function getUserAwayInfo( $user ) {

        $dateformat = 'M d Y';
        $res = "";
        $resB = "";
        $resV = "";

        $requestsB = $this->getApprovedRequestToday( $user, 'requestBusiness' );
        $requestsV = $this->getApprovedRequestToday( $user, 'requestVacation' );

//        if( $requestB && $requestV ) {
//            //Vacation + Business Travel: StartDate - EndDate, Back on BackDate
//            $res = "Vacation + Business Travel:" . ;
//        }

        if( count($requestsB) > 0 ) {
            //Business Travel: StartDate - EndDate, Back on BackDate
            foreach( $requestsB as $request ) {
                $subRequest = $request->getRequestBusiness();
                $resB = "Business Travel: " . $subRequest->getStartDate()->format($dateformat) . " - " . $subRequest->getEndDate()->format($dateformat);
                $resB .= "<br>";

                $emergencyConatcs = $request->getEmergencyConatcs();
                if( $emergencyConatcs ) {
                    $resB .= "Emergency Contact Via: <br>" . "<strong>" . $emergencyConatcs . "</strong>";
                    $resB .= "<br>";
                }
            }
        }

        if( count($requestsV) > 0 ) {
            foreach( $requestsV as $request ) {
                $subRequest = $request->getRequestVacation();
                //Vacation: StartDate - EndDate, Back on BackDate
                $resV = "Vacation: " . $subRequest->getStartDate()->format($dateformat) . " - " . $subRequest->getEndDate()->format($dateformat);
                $resV .= "<br>";

                $emergencyConatcs = $request->getEmergencyConatcs();
                if( $emergencyConatcs ) {
                    $resV .= "Emergency Contact Via: <br>" . "<strong>" . $emergencyConatcs . "</strong>";
                    $resV .= "<br>";
                }
            }
        }

        if( $resB || $resV ) {
            $res = "<h4>Away:</h4>";
            $res .= '<div style="padding-left: 1em;">';
            $res .= $resB . $resV;
            $res .= '</div>';
        }

        $res = $res . "<br>";

        return $res;
    }
    public function getApprovedRequestToday( $user, $requestTypeStr ) {

        $today = new \DateTime();
        $todayStr = $today->format('Y-m-d');

        //process.py script: replaced namespace by ::class: ['AppVacReqBundle:VacReqRequest'] by [VacReqRequest::class]
        $repository = $this->em->getRepository(VacReqRequest::class);
        $dql = $repository->createQueryBuilder('request');
        $dql->leftJoin("request.user", "user");

        if( $requestTypeStr == 'business' || $requestTypeStr == 'requestBusiness' ) {
            $dql->leftJoin("request.requestBusiness", "requestType");
        }

        if( $requestTypeStr == 'vacation' || $requestTypeStr == 'requestVacation' ) {
            $dql->leftJoin("request.requestVacation", "requestType");
        }

        $dql->where("user.id = :userId AND requestType.id IS NOT NULL AND requestType.status = :statusApproved");

        $dql->andWhere('(:today BETWEEN requestType.startDate and requestType.endDate)');

        $query = $dql->getQuery(); //$query = $this->em->createQuery($dql);

        $query->setParameter('userId', $user->getId());
        $query->setParameter('statusApproved', 'approved');
        $query->setParameter('today', $todayStr);

        //echo "dql=".$dql."<br>";

        $requests = $query->getResult();

        //echo "count=".count($requests)."<br>";

        return $requests;
    }


    //find the first upper supervisor of this user's group
    public function getClosestSupervisor( $user, $onlyWorking=false ) {

        //1) get the submitter group Id of this user
        $groupParams = array('asObjectRole'=>true);
        $groupParams['permissions'][] = array('objectStr'=>'VacReqRequest','actionStr'=>'changestatus-carryover');
        $supervisorRoles = $this->getGroupsByPermission($user,$groupParams);
        //echo "supervisorRoles=".count($supervisorRoles)."<br>";

        //2) get a user with this role
        $supervisorsArr = array();
        foreach( $supervisorRoles as $supervisorRole ) {
            //echo "supervisorRole=".$supervisorRole."<br>";
//            //find users with this role
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:User'] by [User::class]
            $supervisors = $this->em->getRepository(User::class)->findUserByRole($supervisorRole->getName(),"infos.lastName",$onlyWorking);
            foreach( $supervisors as $supervisor ) {
                $supervisorsArr[] = $supervisor;
            }

        }

        //we can see which user to pick (user with the lowest role's institution) in case of multiple supervisors
        $supervisorUser = $supervisorsArr[0];
        //echo "supervisorUser=".$supervisorUser."<br>";

        return $supervisorUser;
    }

    //get all groups for this user children groups
    public function getAllGroupsByUser( $user ) {
        $groupParams = array();

        //1) get user groups
        $groupParams['permissions'][] = array('objectStr'=>'VacReqRequest','actionStr'=>'create');
        $groupParams['permissions'][] = array('objectStr'=>'VacReqRequest','actionStr'=>'view-away-calendar');
        $groupParams['permissions'][] = array('objectStr'=>'VacReqRequest','actionStr'=>'changestatus');
        $groupParams['permissions'][] = array('objectStr'=>'VacReqRequest','actionStr'=>'changestatus-carryover');
        $userOrganizationalInstitutions = $this->getGroupsByPermission($user,$groupParams);
//        echo "user group:<br><pre>";
//        print_r($userOrganizationalInstitutions);
//        echo "</pre>";
//        foreach($userOrganizationalInstitutions as $organizationalInstitution) {
//            echo "user group=".$organizationalInstitution."<br>";
//        }

        //2) get user's supervisor groups
        //to get the select filter with all groups under the supervisor group, find the first upper supervisor of this group.
        if( $this->security->isGranted('ROLE_VACREQ_SUPERVISOR') ) {
            $subjectUser = $user;
        } else {
            $groupParams['asSupervisor'] = true;
            $subjectUser = $this->getClosestSupervisor( $user );
        }
        //echo "subjectUser=".$subjectUser."<br>";
        if( !$subjectUser ) {
            $subjectUser = $user;
        }

        $supervisorOrganizationalInstitutions = $this->getGroupsByPermission($subjectUser,$groupParams);
//        echo "supervisor group:<br><pre>";
//        print_r($supervisorOrganizationalInstitutions);
//        echo "</pre>";

        //3) merge user and supervisor groups keeping original indexes
        $organizationalInstitutions = $userOrganizationalInstitutions + $supervisorOrganizationalInstitutions;
        $organizationalInstitutions = array_unique($organizationalInstitutions);

//        echo "res group:<br><pre>";
//        print_r($organizationalInstitutions);
//        echo "</pre>";

        return $organizationalInstitutions;
    }

    public function getAllGroups($asObject=true) {
        $groupParams = array();
        if( $asObject ) {
            $groupParams['asObject'] = true; // array('asObject'=>true);
        } else {
            //
        }
        //$groupParams = array();
        //$groupParams = array('asObject'=>true);
        $groupParams['permissions'][] = array('objectStr'=>'VacReqRequest','actionStr'=>'create');

        $user = null;
        $userOrganizationalInstitutions = $this->getGroupsByPermission($user,$groupParams);

        return $userOrganizationalInstitutions;

//        $permissions = ( array_key_exists('permissions', $groupParams) ? $groupParams['permissions'] : null);
//
//        foreach( $permissions as $permission ) {
//            $objectStr = $permission['objectStr'];
//            $actionStr = $permission['actionStr'];
//
//            $roles = $this->em->getRepository('AppUserdirectoryBundle:User')->
//                findRolesByObjectActionInstitutionSite($objectStr, $actionStr, null, 'vacreq', null);
//        }
//
//        foreach( $roles as $role ) {
//
//        }

    }

    //get all user's organizational groups and children specified to permission
    //get only institutions from the same institutional tree:
    //if submitter has CYTOPATHOLOGY submitter role, then the each resulting institution should be equal or be a parent of CYTOPATHOLOGY
    public function getGroupsByPermission( $user=null, $params=array() ) {

        //1) get roles associated with sitename and permission
        //2) for each role: get associated institution

        //dump($params);
        //exit('111');

        $asObject = ( array_key_exists('asObject', $params) ? $params['asObject'] : false);
        $asObjectRole = ( array_key_exists('asObjectRole', $params) ? $params['asObjectRole'] : false);
        $permissions = ( array_key_exists('permissions', $params) ? $params['permissions'] : null);
        $exceptPermissions = ( array_key_exists('exceptPermissions', $params) ? $params['exceptPermissions'] : null);
        $asSupervisor = ( array_key_exists('asSupervisor', $params) ? $params['asSupervisor'] : false);
        $asUser = ( array_key_exists('asUser', $params) ? $params['asUser'] : false);
        $statusArr = ( array_key_exists('statusArr', $params) ? $params['statusArr'] : array());

        //$sortBy = null;
        $sortBy = ( array_key_exists('sortBy', $params) ? $params['sortBy'] : null);
        //$sortBy='list.name';
        
        //$asUser = true;
//        echo "asUser=$asUser <br>";
//        if($asUser) {
//            echo "asUser=True<br>";
//        } else {
//            echo "asUser=False<br>";
//        }

        $institutions = array();
        $addedArr = array();

        foreach( $permissions as $permission ) {

            $objectStr = $permission['objectStr'];
            $actionStr = $permission['actionStr'];
            //echo "### objectStr=".$objectStr.", actionStr=".$actionStr."### <br>";

            //1) get roles
            $roles = new ArrayCollection();

            if( !$user ) {
                if( !$asUser ) {
                    //echo "roles try 0<br>";
                    $roles = $this->em->getRepository(User::class)->
                    findRolesByObjectActionInstitutionSite($objectStr, $actionStr, null, 'vacreq', null, $sortBy);
                }
            }

            if( count($roles)==0 && $this->security->isGranted('ROLE_VACREQ_ADMIN') ) {
                //echo "roles try 1<br>";
                if( !$asUser ) {
                    $roles = $this->em->getRepository(User::class)->
                    findRolesByObjectActionInstitutionSite($objectStr, $actionStr, null, 'vacreq', null, $sortBy);
                }
            }
            if( count($roles)==0 && ($this->security->isGranted('ROLE_VACREQ_SUPERVISOR') || $asSupervisor) ) {
                //echo "roles for ROLE_VACREQ_SUPERVISOR<br>";
                //echo "roles try 2<br>";
                if( !$asUser ) {
                    $roles = $this->em->getRepository(User::class)->
                    findUserChildRolesBySitePermissionObjectAction($user, 'vacreq', $objectStr, $actionStr);
                }
            }
            if( count($roles)==0 ) {
                //echo "roles try 3<br>";
                $roles = $this->em->getRepository(User::class)->
                    findUserRolesBySitePermissionObjectAction($user,'vacreq',$objectStr,$actionStr);
            }

            //second try to get group. This is the case for changestatus-carryover action
            if( count($roles)==0 && $actionStr == "changestatus-carryover" ) {
                //echo "second try 4<br>";
                //get all changestatus-carryover roles: changestatus-carryover and create
                $childObjectStr = $objectStr;
                $childActionStr = "create";
                $roles = $this->em->getRepository(User::class)->
                    findUserParentRolesBySitePermissionObjectAction($user,'vacreq',$objectStr,$actionStr,$childObjectStr,$childActionStr);
                //echo "1 role count=".count($roles)."<br>"; //testing this role count is 1 for wcmc pathology

                if( count($roles)==0 ) {
                    //echo "another try 5 for view-away-calendar action for role ROLE_VACREQ_OBSERVER_WCM_PATHOLOGY<br>";
                    $childActionStr = "view-away-calendar";
                    $roles = $this->em->getRepository(User::class)->
                    findUserParentRolesBySitePermissionObjectAction($user,'vacreq',$objectStr,$actionStr,$childObjectStr,$childActionStr);
                }
                //echo "2 role count=".count($roles)."<br>";
            }

            //echo "### EOF ".$actionStr.": final role count=".count($roles)."### <br>";

//            $adminRole = false;
//            if( $this->security->isGranted('ROLE_VACREQ_ADMIN') ) {
//                //echo "admin<br>";
//                $adminRole = true;
//            }
            //echo "<br><br>";

            //2) for each role: get associated institution
            foreach( $roles as $role ) {

                //echo "###role=".$role."<br>";
                $include = true;
                $institution = $role->getInstitution();

                if( $institution ) {

                    //avoid duplication
                    if( !in_array($institution->getId(), $addedArr) ) {
                        $addedArr[] = $institution->getId();
                    } else {
                        continue;
                    }

                    //$statusArr: include only statuses provided by $statusArr
                    if( $statusArr && count($statusArr)>0 ) {
                        $statusOk = false;
                        foreach( $statusArr as $thisStatus ) {
                            $roleStatus = $role->getType();
                            if( $roleStatus == $thisStatus ) {
                                $statusOk = true;
                                continue;
                            }
                        }

                        if( !$statusOk ) {
                            continue;
                        }
                    }

                    if( $exceptPermissions ) {
                        foreach( $exceptPermissions as $exceptPermission ) {
                            //echo "exceptPermission: ".$exceptPermission['objectStr']."=>".$exceptPermission['actionStr']."<br>";
                            foreach( $role->getPermissions() as $permission ) {
                                $roleObjectStr = $permission->getPermission()->getPermissionObjectList()->getName();
                                $roleActionStr = $permission->getPermission()->getPermissionActionList()->getName();
                                //echo "except: ".$roleObjectStr."=>".$roleActionStr."<br>";
                                if( $roleObjectStr == $exceptPermission['objectStr'] && $roleActionStr == $exceptPermission['actionStr'] ) {
                                    //echo "!!!!!!!!except role=".$role."<br>";
                                    $include = false;
                                    continue;
                                }
                            }
                        }
                    }

                    if( $include == false ) {
                        //echo "exclude role=".$role."<br>";
                        continue;
                    }

                    if( $asObjectRole ) {
                        $institutions[] = $role;
                        continue;
                    }

                    if( $asObject ) {
                        $institutions[] = $institution;
                        continue;
                    }

                    //Clinical Pathology (for review by Firstname Lastname)
                    //find approvers with the same institution
                    $approverStr = $this->getApproversBySubmitterRole($role);
                    if( $approverStr ) {
                        if( $this->security->isGranted('ROLE_PLATFORM_ADMIN') || $this->security->isGranted('ROLE_PLATFORM_DEPUTY_ADMIN') ) {
                            //$orgName = "ID#".$institution->getId()." ".$institution . " (for review by " . $approverStr . ")";
                            $orgName = $institution . " ID#" . $institution->getId() . " (for review by " . $approverStr . ")";
                        } else {
                            $orgName = $institution . " (for review by " . $approverStr . ")";
                        }
                    } else {
                        $orgName = $institution;
                        if( $this->security->isGranted('ROLE_PLATFORM_ADMIN') || $this->security->isGranted('ROLE_PLATFORM_DEPUTY_ADMIN') ) {
                            //$orgName = "ID#".$institution->getId()." ".$institution;
                            $orgName = $institution . " ID#".$institution->getId();
                        }
                    }
                    //echo "orgName=".$orgName."<br>";

                    $institutions[$institution->getId()] = $orgName;

                }

            }//foreach roles

        }//foreach permissions

//        foreach( $institutions as $key=>$value) {
//            echo $key."=>".$value."<br>";
//        }

        return $institutions;
    }
    //TODO: replace by getGroupsByPermission
    //get user's organizational group
    //get only institutions from the same institutional tree:
    //if submitter has CYTOPATHOLOGY submitter role, then the each resulting institution should be equal or be a parent of CYTOPATHOLOGY
    public function getVacReqOrganizationalInstitutions( $user, $params=array() ) {

        $asObject = ( array_key_exists('asObject', $params) ? $params['asObject'] : false);
        //$requestTypeAbbreviation = ( array_key_exists('requestTypeAbbreviation', $params) ? $params['requestTypeAbbreviation'] : null);
        //$routeName = ( array_key_exists('routeName', $params) ? $params['routeName'] : null);
        $roleSubStrArr = ( array_key_exists('roleSubStrArr', $params) ? $params['roleSubStrArr'] : array("ROLE_VACREQ_"));

        $institutions = array();

        //get this user institutions associated with this site
        $partialRoleName = "ROLE_VACREQ_";
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:User'] by [User::class]
        $userRoles = $this->em->getRepository(User::class)->findUserRolesBySiteAndPartialRoleName($user, "vacreq", $partialRoleName, null, false);
        $userInsts = new ArrayCollection();
        foreach( $userRoles as $userRole ) {
            $roleInst = $userRole->getInstitution();
            if( $roleInst && !$userInsts->contains($roleInst) ) {
                $userInsts->add($roleInst);
                //echo "add to userInsts=".$roleInst."<br>";
            }
        }

        //get user's roles by site and role name array
        $submitterRoles = new ArrayCollection();
        foreach( $roleSubStrArr as $roleSubStr ) {
            if( $this->security->isGranted('ROLE_VACREQ_ADMIN') || $this->security->isGranted('ROLE_VACREQ_SUPERVISOR') ) {
                //find all submitter role's institution
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:User'] by [User::class]
                $submitterSubRoles = $this->em->getRepository(User::class)->findRolesBySiteAndPartialRoleName("vacreq",$roleSubStr);
            } else {
                //echo "roleSubStr=".$roleSubStr."<br>";
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:User'] by [User::class]
                $submitterSubRoles = $this->em->getRepository(User::class)->findUserRolesBySiteAndPartialRoleName($user, "vacreq", $roleSubStr, null, false);
            }

            foreach( $submitterSubRoles as $submitterSubRole ) {
                if( $submitterSubRole && !$submitterRoles->contains($submitterSubRole) ) {
                    $submitterRoles->add($submitterSubRole);
                }
            }

        }

//        if( count($submitterRoles) == 0 ) {
//            //find all submitter role's institution
//            $submitterRoles = $this->em->getRepository('AppUserdirectoryBundle:User')->findRolesBySiteAndPartialRoleName("vacreq",$requestRoleSubStr);
//        }
//        echo "roles count=".count($submitterRoles)."<br>";

        //get only SUBMITTER roles: filter roles by SUBMITTER string
//        foreach( $submitterRoles as $role ) {
//            echo "role=".$role."<br>";
//        }

        foreach( $submitterRoles as $submitterRole ) {

            //echo "submitterRole=".$submitterRole."<br>";
            $institution = $submitterRole->getInstitution();

            if( $institution ) {

                //get only institutions from the same institutional tree:
                //  if submitter has CYTOPATHOLOGY submitter role, then the each resulting institution should be equal or be a parent of CYTOPATHOLOGY
                //check if this institution is equal or under user's site institution
                if( $this->security->isGranted('ROLE_VACREQ_ADMIN') == false ) {
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:Institution'] by [Institution::class]
                    if( $this->em->getRepository(Institution::class)->isNodeUnderParentnodes($userInsts, $institution) == false ) {
                        //echo "remove institution=".$institution."<br>";
                        continue;
                    }
                }
                //echo "add submitterRole=".$submitterRole."<br>";

                if( $asObject ) {
                    $institutions[] = $institution;
                    continue;
                }

                //Clinical Pathology (for review by Firstname Lastname)
                //find approvers with the same institution
                $approverStr = $this->getApproversBySubmitterRole($submitterRole);
                if( $approverStr ) {
                    $orgName = $institution . " (for review by " . $approverStr . ")";
                } else {
                    $orgName = $institution;
                }
                //echo "orgName=".$orgName."<br>";

                $institutions[$institution->getId()] = $orgName;
            }
        }

        //add request institution
//        if( $entity->getInstitution() ) {
//            $orgName = $institution . " (for review by " . $approverStr . ")";
//            $institutions[$entity->getInstitution()->getId()] = $orgName;
//        }
        //exit('1');

        return $institutions;
    }
    //$role - string; for example "ROLE_VACREQ_APPROVER_CYTOPATHOLOGY"
    public function getApproversBySubmitterRole( $role ) {
        $roleApprover = str_replace("SUBMITTER","APPROVER",$role);
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:User'] by [User::class]
        $approvers = $this->em->getRepository(User::class)->findUserByRole($roleApprover,"infos.lastName",true);

        $approversArr = array();
        foreach( $approvers as $approver ) {
            $approversArr[] = $approver->getUsernameShortest();
        }

        return implode(", ",$approversArr);
    }

    //not used. use getUsersByGroupId($groupId,"ROLE_VACREQ_SUBMITTER")
    public function getSubmittersFromSubmittedRequestsByGroup( $groupId ) {
        //TODO: this might optimized to get user objects in one query. groupBy does not work in MSSQL.
        //process.py script: replaced namespace by ::class: ['AppVacReqBundle:VacReqRequest'] by [VacReqRequest::class]
        $repository = $this->em->getRepository(VacReqRequest::class);
        $dql =  $repository->createQueryBuilder("request");
        //$dql->select('request');
        $dql->select('DISTINCT (user) as submitter');
        $dql->addSelect('infos.lastName');
        //$dql->select('user');
        //$dql->addSelect('request');
        //$dql->groupBy("user");
        //$dql->addGroupBy("request");
        //$dql->addGroupBy("infos");
        $dql->leftJoin("request.user", "user");
        $dql->leftJoin("user.infos", "infos");

        $dql->where("request.institution = :groupId");

        //Add a filter to the vacation request site for the "My Group" page: show stats only for current employees,
        // meaning check if the listed users have a non-empty "end" date in the Employment Period section of their profile.
        $dql->leftJoin("user.employmentStatus", "employmentStatus");
        $dql->andWhere("employmentStatus.terminationDate IS NULL");

        $dql->orderBy('infos.lastName', 'ASC');

        $query = $dql->getQuery(); //$query = $this->em->createQuery($dql);

        $query->setParameters( array(
            'groupId' => $groupId
        ));

        $results = $query->getResult();
        //echo "count results=".count($results)."<br>";

        $submitters = array();
        foreach( $results as $result ) {
            //$submitters[] = $result->getUser();
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:User'] by [User::class]
            $user = $this->em->getRepository(User::class)->find($result['submitter']);
            if( $user ) {
                $submitters[] = $user;
            } else {
                //exit('no user found');
            }
            //echo "user=".$result['submitter']."<br>";
            //echo "user=".$user."<br>";
            //print_r($result);
            //echo "res=".$result['id']."<br>";

        }
        //exit('1');

        return $submitters;
    }

    //$rolePartialNameArr - array of roles partial names. For example, array('ROLE_VACREQ_APPROVER','ROLE_VACREQ_SUPERVISOR')
    public function hasRoleNameAndGroup( $rolePartialNameArr, $institutionId=null ) {
        if( $this->security->isGranted('ROLE_VACREQ_ADMIN') ) {
            return true;
        }

        $user = $this->security->getUser();
        //$sitename = "vacreq";

        //TODO: fix this by permission

        //get user allowed groups
        $groupParams = array(
            'roleSubStrArr' => $rolePartialNameArr, //array('ROLE_VACREQ_APPROVER','ROLE_VACREQ_SUPERVISOR'),
            'asObject' => true
        );
        $groupInstitutions = $this->getVacReqOrganizationalInstitutions($user,$groupParams);
        //echo "inst count=".count($groupInstitutions)."<br>";

        //check if subject has at least one of the $groupInstitutions
        foreach( $groupInstitutions as $inst ) {
            //echo "inst=".$inst."<br>";
            if( $inst->getId() == $institutionId ) {
                return true;
            }
        }
        //echo "return false<br>";

        return false;
    }
    
    public function isAdminSupervisorApprover($entity) {
        if( $this->security->isGranted('ROLE_VACREQ_ADMIN') ) {
            return true;
        }

        if( $this->security->isGranted('ROLE_VACREQ_SUPERVISOR') ) {
            return true;
        }

        if( $this->security->isGranted('changestatus', $entity) ) {
            return true;
        }

        return false;
    }

    public function getSubmitterPhone($user) {

        //(a) prepopulate the phone number with the phone number from the user's profile
        $phones = $user->getAllPhones();
        if( count($phones) > 0 ) {
            return $phones[0]['phone'];
        }

        //(b) prepopulate from previous approved request (if there is one) for this user (person away)
        //$requests = $this->em->getRepository('AppVacReqBundle:VacReqRequest')->findByUser($user,array('ORDER'=>'approvedRejectDate'));
        //process.py script: replaced namespace by ::class: ['AppVacReqBundle:VacReqRequest'] by [VacReqRequest::class]
        $repository = $this->em->getRepository(VacReqRequest::class);
        $dql =  $repository->createQueryBuilder("request");
        $dql->select('request');

        $dql->leftJoin("request.user", "user");
        $dql->leftJoin("request.requestBusiness", "requestBusiness");
        $dql->leftJoin("request.requestVacation", "requestVacation");

        $dql->where("user.id = :userId");
        $dql->andWhere("requestBusiness.status = :statusApproved OR requestVacation.status = :statusApproved");
        $dql->andWhere("request.phone IS NOT NULL");

        $dql->orderBy('request.createDate', 'DESC');

        $query = $dql->getQuery(); //$query = $this->em->createQuery($dql);

        $query->setParameters( array(
            'userId' => $user->getId(),
            'statusApproved' => 'approved'
        ));

        $requests = $query->getResult();

        if( count($requests) > 0 ) {
            $request = $requests[0];
            if ($request->getPhone()) {
                return $request->getPhone();
            }
        }

        //(c) prepopulate from the approved request before the last one

        return null;
    }

    public function setEmergencyInfo( $user, $request ) {
        $emergencyInfoArr = $this->getSubmitterEmergencyInfo($user);
        if( $emergencyInfoArr['mobile'] ) {
            $request->setAvailableViaCellPhone(true);
            $request->setAvailableCellPhone($emergencyInfoArr['mobile']);
        }
        if( $emergencyInfoArr['email'] ) {
            $request->setAvailableViaEmail(true);
            $request->setAvailableEmail($emergencyInfoArr['email']);
        }
    }
    public function getSubmitterEmergencyInfo($user) {

        $res = array(
            'mobile' => null,
            'email' => null
        );

        //(1a) prepopulate the cell phone number from the user's profile
        if ($res['mobile'] == null) {
            $homeLocation = $user->getHomeLocation();
            if ($homeLocation && $homeLocation->getMobile()) {
                $res['mobile'] = $homeLocation->getMobile();
            }
        }
        if ($res['mobile'] == null) {
            $mainLocation = $user->getMainLocation();
            if ($mainLocation && $mainLocation->getMobile()) {
                $res['mobile'] = $mainLocation->getMobile();
            }
        }
        if ($res['mobile'] == null) {
            $locations = $user->getLocations();
            foreach ($locations as $location) {
                if ($res['mobile'] == null) {
                    if ($location && $location->getMobile()) {
                        $res['mobile'] = $location->getMobile();
                    }
                } else {
                    break;
                }
            }
        }
        //(1b) prepopulate the email from the user's profile
        $email = $user->getEmail();
        if ($email) {
            $res['email'] = $email;
        }

        //(2a) prepopulate mobile from previous approved request (if there is one) for this user (person away)
        if( $res['mobile'] == null ) {
        //process.py script: replaced namespace by ::class: ['AppVacReqBundle:VacReqRequest'] by [VacReqRequest::class]
            $repository = $this->em->getRepository(VacReqRequest::class);
            $dql = $repository->createQueryBuilder("request");
            $dql->select('request');
            $dql->leftJoin("request.user", "user");
            $dql->where("user.id = :userId");
            $dql->andWhere("request.availableCellPhone IS NOT NULL");
            $dql->orderBy('request.createDate', 'DESC');

            $query = $dql->getQuery(); //$query = $this->em->createQuery($dql);

            $query->setParameters(array(
                'userId' => $user->getId(),
            ));

            $requests = $query->getResult();

            foreach( $requests as $request ) {
                if( $request->getAvailableCellPhone() ) {
                    $res['mobile'] = $request->getAvailableCellPhone();
                    break;
                }
            }
        }
        //(2b) prepopulate email from previous approved request (if there is one) for this user (person away)
        if( $res['email'] == null ) {
        //process.py script: replaced namespace by ::class: ['AppVacReqBundle:VacReqRequest'] by [VacReqRequest::class]
            $repository = $this->em->getRepository(VacReqRequest::class);
            $dql = $repository->createQueryBuilder("request");
            $dql->select('request');
            $dql->leftJoin("request.user", "user");
            $dql->where("user.id = :userId");
            $dql->andWhere("request.availableEmail IS NOT NULL");
            $dql->orderBy('request.createDate', 'DESC');

            $query = $dql->getQuery(); //$query = $this->em->createQuery($dql);

            $query->setParameters(array(
                'userId' => $user->getId(),
            ));

            $requests = $query->getResult();

            foreach( $requests as $request ) {
                if( $request->getAvailableEmail() ) {
                    $res['email'] = $request->getAvailableEmail();
                    break;
                }
            }
        }

        //(c) prepopulate from the approved request before the last one

        return $res;
    }


    //set cancel email to approver and email users
    public function sendCancelEmailToApprovers( $entity, $user, $status, $sendCopy=true ) {
        $subject = $entity->getRequestName()." ID #" . $entity->getId() . " " . ucwords($status);
        $message = $this->createCancelEmailBody($entity);
        return $this->sendGeneralEmailToApproversAndEmailUsers($entity,$subject,$message,$sendCopy);
    }
    public function createCancelEmailBody( $entity, $emailUser=null, $addText=null ) {
        //$break = "\r\n";
        $break = "<br>";

        //$message = "Dear " . $emailUser->getUsernameOptimal() . "," . $break.$break;
        $message = "Dear ###emailuser###," . $break.$break;

        if( $addText ) {
            $message .= $addText.$break.$break;
        }

        $requestName = $entity->getRequestName();

        $message .= $entity->getUser()." canceled/withdrew the ".$requestName." ID #".$entity->getId()." described below:".$break.$break;

        $message .= $entity->printRequest($this->container)."";

        $message .= $break.$break."**** PLEASE DO NOT REPLY TO THIS EMAIL ****";

        return $message;
    }

    //send the general emails to approver and email users with given subject and message body
    public function sendGeneralEmailToApproversAndEmailUsers( $entity, $subject, $originalMessage, $sendCopy=true ) {

        $logger = $this->container->get('logger');

        $institution = $entity->getInstitution();

        if( $entity->getRequestTypeAbbreviation() == "carryover" ) {
            if( !$institution ) {
                $institution = $entity->getTentativeInstitution();
            }
        }

        if( !$institution ) {
            //$logger->error("sendGeneralEmailToApproversAndEmailUsers: Request ".$entity->getId()." does not have institution");
            return null;
        }

        $emailUtil = $this->container->get('user_mailer_utility');
        //$break = "\r\n";
        $break = "<br>";

        //$requestName = $entity->getRequestName();

        $approvers = $this->getRequestApprovers($entity);
        //echo "#### approvers=".count($approvers)."<br>";

        //$subject = $requestName." #" . $entity->getId() . " " . ucwords($status);
        $approversNameArr = array();
        $approverEmailArr = array();
        $approversShortNameArr = array();

        foreach( $approvers as $approver ) {

            //echo "approver=".$approver."<br>";
            $approverSingleEmail = $approver->getSingleEmail();

            if( $approverSingleEmail ) {
                $approverEmailArr[] = $approverSingleEmail;
                $approversNameArr[] = $approver." (".$approverSingleEmail.")";
                $approversShortNameArr[] = $approver->getUsernameOptimal();
            }

            //$message = $this->createCancelEmailBody($entity,$approver);
            //$message = str_replace("###emailuser###",$approver->getUsernameOptimal(),$originalMessage);
            //$emailUtil->sendEmail($approverSingleEmail, $subject, $message, null, null);

        } //foreach approver

        $message = str_replace("###emailuser###",implode("; ",$approversShortNameArr),$originalMessage);

        if( count($approverEmailArr) > 0 ) {
            $logger->notice("sendGeneralEmailToApproversAndEmailUsers: send confirmation emails to approvers=".implode("; ",$approverEmailArr)."; subject=".$subject."; message=".$message);
            $emailUtil->sendEmail($approverEmailArr, $subject, $message, null, null);
        }

        //send email to email users
        //echo "sendCopy=".$sendCopy."<br>";
        if( $sendCopy ) {
            $emailUserEmailArr = array();
            $subject = "Copy of the email: " . $subject;
            $addText = "### This is a copy of the email sent to the approvers " . implode("; ", $approversNameArr) . "###";
            $message = $addText . $break . $break . $message;
            //echo "settings for institution=".$institution."<br>";
            $settings = $this->getSettingsByInstitution($institution->getId());
            if ($settings) {
                //echo "settings OK<br>";
                foreach ($settings->getEmailUsers() as $emailUser) {
                    //echo "emailUser=".$emailUser."<br>";
                    $emailUserEmail = $emailUser->getSingleEmail();
                    if ($emailUserEmail) {
                        //$message = $this->createCancelEmailBody($entity, $emailUser, $addText);
                        //$emailUtil->sendEmail($emailUserEmail, $subject, $message, null, null);
                        $emailUserEmailArr[] = $emailUserEmail;
                    }
                }

            }

            //send email to the individuals to inform (VacReqSettings->defaultInformUsers) + additional inform users on request
            $informUsers = $this->getAllInformUsers($entity);
            foreach( $informUsers as $informUser ) {
                //echo "informUser=".$informUser."<br>";
                $informUserEmail = $informUser->getSingleEmail();
                if ($informUserEmail) {
                    $emailUserEmailArr[] = $informUserEmail;
                }
            }

            //Add approvers for a notification email for carry over request (if copy user is not in $approverEmailArr)
            if( $entity->getRequestTypeAbbreviation() == "carryover" ) {
                //echo "getting carry over request approvers<br>";
                $supervisors = $this->getUsersByGroupId($institution,"ROLE_VACREQ_SUPERVISOR");
                foreach( $supervisors as $supervisor ) {
                    $supervisorEmail = $supervisor->getSingleEmail();
                    //echo "supervisor=".$supervisor.", email=".$supervisorEmail."<br>";
                    if( $supervisorEmail && !in_array($supervisorEmail, $approverEmailArr) ) {
                        $emailUserEmailArr[] = $supervisorEmail;
                    }
                }

                //overwrite message without links
                //$entity, $emailToUser=null, $addText=null, $withLinks=true
                $message = $this->createEmailBody($entity,null,null,false);
                $message = str_replace("###emailuser###",implode("; ",$approversShortNameArr),$message);
            }


            //$logger->notice("sendGeneralEmailToApproversAndEmailUsers: emailUserEmailArr count=".count($emailUserEmailArr));
            if (count($emailUserEmailArr) > 0) {
                $logger->notice("sendGeneralEmailToApproversAndEmailUsers: send a copy of the confirmation emails to email users and supervisors=" . implode("; ", $emailUserEmailArr) . "; subject=" . $subject . "; message=" . $message);
                $emailUtil->sendEmail($emailUserEmailArr, $subject, $message, null, null);
            }
        }

        //if( count($approverEmailArr) > 0 ) {
            //$emailUtil->sendEmail($approverEmailArr, $subject, $message, $emailUserEmailArr, null);
        //}

        //dump($approversNameArr);
        //exit('eof sendGeneralEmailToApproversAndEmailUsers');

        $approversNameStr = implode(", ",$approversNameArr);

        if( !$approversNameStr ) {
            $approversNameStr = " None (No Approvers found for $institution)";
        }

        if( count($emailUserEmailArr) > 0 ) {
            $approversNameStr = $approversNameStr . ";<br> Copy sent to ".implode(", ",$emailUserEmailArr);
        }

        return $approversNameStr;
    }

    //User log should record all changes in user: subjectUser, Author, field, old value, new value.
    public function setEventLogChanges($request) {

        //$diffArr = $this->diffDoctrineObject($request);
        //echo "diffArr=".$diffArr."<br>";
        //print_r($diffArr);
        //exit('1');

        //$em = $this->em;

        //$uow = $em->getUnitOfWork();
        //$uow->computeChangeSets(); // do not compute changes if inside a listener

        $eventArr = array();

        //log simple fields
        //$changeset = $uow->getEntityChangeSet($request);
        $changeset = $this->diffDoctrineObject($request);
        $eventArr = $this->addChangesToEventLog( $eventArr, $changeset );

        //log Business Request
        if( $request->hasBusinessRequest() ) {
            $requestParticular = $request->getRequestBusiness();
            //$changeset = $uow->getEntityChangeSet($requestParticular);
            $changeset = $this->diffDoctrineObject($requestParticular);
            $text = "("."Business Travel Request ID ".$request->getId().")";
            $eventArr = $this->addChangesToEventLog( $eventArr, $changeset, $text );
        }

        //log Vacation Request
        if( $request->hasVacationRequest() ) {
            $requestParticular = $request->getRequestVacation();
            //$changeset = $uow->getEntityChangeSet($requestParticular);
            $changeset = $this->diffDoctrineObject($requestParticular);
            $text = "("."Vacation Request ID ".$request->getId().")";
            $eventArr = $this->addChangesToEventLog( $eventArr, $changeset, $text );
        }

        if( $request instanceof VacReqRequestFloating ) {
            $changeset = $this->diffDoctrineObject($request);
            $text = "("."Floating Day Request ID ".$request->getId().")";
            $eventArr = $this->addChangesToEventLog( $eventArr, $changeset, $text );
        }

        //exit('1');
        return $eventArr;

    }
    public function addChangesToEventLog( $eventArr, $changeset, $text="" ) {

        $changeArr = array();

        //echo "count changeset=".count($changeset)."<br>";

        //process $changeset: author, subjectuser, oldvalue, newvalue
        foreach( $changeset as $key => $value ) {
            if( $value[0] != $value[1] ) {

                if( is_object($key) ) {
                    //if $key is object then skip it, because we don't want to have non-informative record such as: credentials(stateLicense New): old value=, new value=Credentials
                    continue;
                }

                $field = $key;

                $oldValue = $value[0];
                $newValue = $value[1];

                if( $oldValue instanceof \DateTime ) {
                    $oldValue = $this->convertDateTimeToStr($value[0]);
                }
                if( $newValue instanceof \DateTime ) {
                    $newValue = $this->convertDateTimeToStr($value[1]);
                }

                if( is_array($oldValue) ) {
                    $oldValue = implode(", ",$oldValue);
                }
                if( is_array($newValue) ) {
                    $newValue = implode(", ",$newValue);
                }

                $event = "<strong>".$field.$text."</strong>".": "."old value=".$oldValue.", new value=".$newValue;

                //echo "event=".$event."<br>";
                //exit();

                $changeArr[] = $event;
            }
        }

        if( count($changeArr) > 0 ) {
            $eventArr[] = implode("<br>", $changeArr);
        }

        return $eventArr;

    }
    public function diffDoctrineObject($entity) {
        $uow = $this->em->getUnitOfWork();
        $originalEntityData = $uow->getOriginalEntityData($entity);
        //echo "originalEntityData=".$originalEntityData."<br>";
        //print_r($originalEntityData);

//        if( $originalEntityData['phone'] != $entity->getPhone() ) {
//            echo "phone changed:".$originalEntityData['phone'] ."=>". $entity->getPhone()."<br>";
//        }

        $changeSet = array();

        foreach( $entity->getArrayFields() as $field ) {
            $getMethod = "get".$field;
            $oldValue = $originalEntityData[$field];
            $newValue = $entity->$getMethod();
            if( $oldValue != $newValue ) {
                //echo "phone changed:".$originalEntityData['phone'] ."=>". $entity->getPhone()."<br>";
                $changeSet[$field] = array($oldValue,$newValue);
            }
        }

        //print_r($changeSet);
        return $changeSet;
    }

    //Used in header
//    public function getAccruedDaysUpToThisMonth_OLD() {
//        //accrued days up to this month calculated by vacationAccruedDaysPerMonth
//        $userSecUtil = $this->container->get('user_security_utility');
//        $vacationAccruedDaysPerMonth = $userSecUtil->getSiteSettingParameter('vacationAccruedDaysPerMonth','vacreq');
//        if( !$vacationAccruedDaysPerMonth ) {
//            throw new \InvalidArgumentException('vacationAccruedDaysPerMonth is not defined in Site Parameters.');
//        }
//
//        //get start academic date
//        $dates = $this->getCurrentAcademicYearStartEndDates(true);
//        $startAcademicYearDate = $dates['startDate'];
//
//        //get month difference between now and $startAcademicYearDate
//        $nowDate = new \DateTime();
//        $monthCount = $this->diffInMonths($startAcademicYearDate, $nowDate);
//        //$monthCount = $monthCount - 1;
//
//        //echo "monthCount=".$monthCount."<br>";
//        $accruedDays = (int)$monthCount * (int)$vacationAccruedDaysPerMonth;
//        return $accruedDays;
//    }
    //NOT USED
    public function getAccruedDaysUpToThisMonth( $user, $approverType=null ) {
        if( !$approverType ) {
            $approverType = $this->getSingleApprovalGroupType($user);
        }
        if( !$approverType ) {
            throw new \InvalidArgumentException('Error: group approve type is not found');
        }
        $vacationAccruedDaysPerMonth = $approverType->getVacationAccruedDaysPerMonth();
        return $this->getAccruedDaysUpToThisMonthByDaysPerMonth($vacationAccruedDaysPerMonth);
    }

    //Used to show average accrued days for each group on the 'summary' page
    public function getAccruedDaysUpToThisMonthByInstitution( $instituionId ) {
        //1) find VacReqSettings by institution
        //2) VacReqSettings has many approvalTypes (VacReqApprovalTypeList)
        //use the Display Order to determine the "first" (lower number is better)
        //3) find vacationAccruedDaysPerMonth from VacReqApprovalTypeList

        //echo "instituionId=$instituionId <br>";

        $approverType = $this->getApprovalGroupTypeByInstitution($instituionId);

        if( !$approverType ) {
            return 0;
        }

        $vacationAccruedDaysPerMonth = $approverType->getVacationAccruedDaysPerMonth();

        return $this->getAccruedDaysUpToThisMonthByDaysPerMonth($vacationAccruedDaysPerMonth);
    }
    //Used to show average accrued days for each group on the 'summary' page. Use full month for accrued days = 2*months
    public function getAccruedDaysUpToThisMonthByDaysPerMonth( $vacationAccruedDaysPerMonth ) {
        //get start academic date
        $dates = $this->getCurrentAcademicYearStartEndDates(true);
        $startAcademicYearDate = $dates['startDate'];

        //get month difference between now and $startAcademicYearDate
        $nowDate = new \DateTime();
        //$monthCount = $this->diffInMonths($startAcademicYearDate, $nowDate);
        $diffDates = $this->diffBetweenTwoDates($startAcademicYearDate, $nowDate);
        $totalAccruedMonths = $diffDates['totalMonths'];
        $days = $diffDates['days'];
        if ($days > 0 && $days <= 15) {
            $monthCount = $totalAccruedMonths + 0.5;
        }
        if ($days > 15) {
            $monthCount = $totalAccruedMonths + 1;
        }

        //echo "monthCount=".$monthCount."<br>";
        $accruedDays = (int)$monthCount * $vacationAccruedDaysPerMonth;
        $accruedDays = round($accruedDays);
        return $accruedDays;
    }

//    /**
//     * Calculate the difference in months between two dates (v1 / 18.11.2013)
//     *
//     * @param \DateTime $date1
//     * @param \DateTime $date2
//     * @return int
//     */
//    public static function diffInMonths_Old(\DateTime $date1, \DateTime $date2)
//    {
//        $diff =  $date1->diff($date2);
//
//        $months = $diff->y * 12 + $diff->m + $diff->d / 30;
//        $months = (int) round($months);
//        $months = $months - 1;
//        echo "months=".$months."<br>";
//
//        return $months;
//    }

    //Not used: replaced by diffBetweenTwoDates
    //Used directly by getAccruedDaysUpToThisMonthByDaysPerMonth, getTotalAccruedMonths
    //if days in month 1-15 => 1 day
    //if days ib month 16-30 => 2 days
    //who started 10/3/23, we should accrue two days for the month of October 2023.
    //who started July 15th (mid-month), we should account for just 1 day of vacation time accrued this month
    //http://www.tricksofit.com/2013/12/calculate-the-difference-between-two-dates-in-php#.V1GMSL69GgM
    public static function diffInMonths(\DateTime $date1, \DateTime $date2)
    {
        //Calculate difference with disregard of the partial month: partial month counted as a full month
        //echo "date1=".$date1->format('d-m-Y H:i:s').", date2=".$date2->format('d-m-Y H:i:s')."<br>";
        $months = $date1->diff($date2)->m + ($date1->diff($date2)->y*12);
        //echo "diffInMonths=".$months." <= date1=".$date1->format('d-m-Y H:i:s').", date2=".$date2->format('d-m-Y H:i:s')."<br>";
        return (int)$months;
    }

    //https://stackoverflow.com/questions/1519228/get-interval-seconds-between-two-datetime-in-php
    public static function diffBetweenTwoDates(\DateTime $date1=NULL, \DateTime $date2=NULL)
    {
//        $timezone = new \DateTimeZone('UTC');
//        $date1->setTimezone($timezone);
//        $date2->setTimezone($timezone);
//        $date1->setTime(0,0,0);
//        $date2->setTime(24,60,60);

        //Calculate difference with disregard of the partial month: partial month counted as a full month
        //echo "diffBetweenTwoDates: date1=".$date1->format('d F Y H:i:s').", date2=".$date2->format('d F Y H:i:s')."<br>";
        //$date1Str = strtotime((string) $date1->format('m/d/y'));
        //$date2Str = strtotime((string) $date2->format('m/d/y'));
        //$date_diff = abs(strtotime($date1Str) - strtotime($date2Str));

        if( !$date1 || !$date2 ) {
            //exit("date1 date2 null");
            return array(
                'years' => 0,
                'months' => 0,   //full months
                'days' => 0,     //leftover days
                'totalMonths' => 0,
                //'totalDays' => $totalDays
            );
        }

        $interval = $date1->diff($date2);
        $totalMonths = $interval->m + $interval->y * 12;

        return array(
            'years' => $interval->y,
            'months' => $interval->m,   //full months
            'days' => $interval->d,     //leftover days
            'totalMonths' => $totalMonths, //$interval->m + $interval->y * 12
            //'totalDays' => $totalDays
        );

        //The calc below is asuming 30 days per month which is not correct
        $timestamp1 = $date1->getTimestamp();
        $timestamp2 = $date2->getTimestamp();

        $diffInSeconds = abs($timestamp1 - $timestamp2);


        // Calculate the number of years in the difference
        $years = floor($diffInSeconds / (365*60*60*24));
        // Calculate the number of months in the remaining difference
        $months = floor(($diffInSeconds - $years * 365*60*60*24) / (30*60*60*24));
        // Calculate the number of days in the remaining difference
        $days = floor(($diffInSeconds - $years * 365*60*60*24 - $months*30*60*60*24)/ (60*60*24));

        $totalMonths = $months + $years * 12;
        $totalDays = $diffInSeconds/(60*60*24);

        echo "diffBetweenTwoDates: $years years, $months months, $days days, totalMonths=$totalMonths, totalDays=$totalDays <br>";

        return array(
            'years' => $years,
            'months' => $months,
            'days' => $days,
            'totalMonths' => $totalMonths,
            'totalDays' => $totalDays
        );
    }

//    public function diffInMonths1(\DateTime $date1, \DateTime $date2)
//    {
//        //Calculate difference with disregard of the partial month: partial month counted as a full month
//        //echo "date1=".$date1->format('d-m-Y H:i:s').", date2=".$date2->format('d-m-Y H:i:s')."<br>";
//
//        //$Months = $date2->diff($date1);
//        //$howeverManyMonths = (($Months->y) * 12) + ($Months->m);
//        //return $howeverManyMonths;
//
//        $year1 = $date1->format('Y');
//        $year2 = $date2->format('Y');
//
//        $month1 = $date1->format('m');
//        $month2 = $date2->format('m');
//
//        //$diff = (($year2 - $year1) * 12) + ($month2 - $month1);
//        $diff = ($month2 - $month1);
//        return $diff;
//    }
//    public function diffInMonths2( $date1, $date2 )
//    {
//        $begin = $date1;
//        $end = $date2;
//        $end = $end->modify( '+1 month' );
//
//        $interval = \DateInterval::createFromDateString('1 month');
//
//        $period = new \DatePeriod($begin, $interval, $end);
//        $counter = 0;
//        //foreach($period as $dt) {
//        //    $counter++;
//        //}
//        $counter = iterator_count($period);
//
//        return $counter;
//    }

    //Use in totalVacationRemainingDays, getHeaderInfoMessages,
    // getCurrentYearUnusedDays, getPreviousYearUnusedDays,
    // getAvailableCurrentYearCarryOverRequestString, mySingleGroupAction (ApproverController)
//    public function getTotalAccruedDays_OLD() {
//        //accrued days up to this month calculated by vacationAccruedDaysPerMonth
//        $userSecUtil = $this->container->get('user_security_utility');
//        $vacationAccruedDaysPerMonth = $userSecUtil->getSiteSettingParameter('vacationAccruedDaysPerMonth','vacreq');
//        if( !$vacationAccruedDaysPerMonth ) {
//            throw new \InvalidArgumentException('vacationAccruedDaysPerMonth is not defined in Site Parameters.');
//        }
//        //echo "monthCount=".$monthCount."<br>";
//        $totalAccruedDays = 12 * $vacationAccruedDaysPerMonth;
//        return $totalAccruedDays;
//    }

    //TODO: use multiple employee periods to get effort %
    //Old version without user's start/end dates: branch master: 06a6f239c7ef8a5b74a708eddac4634903b0d9fe; July 17 2024 11:23
    //total accrued days calculated by vacationAccruedDaysPerMonth
    public function getTotalAccruedDays( $user=NULL, $yearRange=NULL, $approvalGroupType=NULL ) {

        if( $user ) {
            return $this->getTotalAccruedDaysUsingEmplPeriods($user, $yearRange, $approvalGroupType);
        }

        //$vacationAccruedDaysPerMonth = $userSecUtil->getSiteSettingParameter('vacationAccruedDaysPerMonth','vacreq');
        $vacationAccruedDaysPerMonth = $this->getValueApprovalGroupTypeByUser("vacationAccruedDaysPerMonth",$user,$approvalGroupType);

        if( !$vacationAccruedDaysPerMonth ) {
            $vacationAccruedDaysPerMonth = 2;
            //throw new \InvalidArgumentException('vacationAccruedDaysPerMonth is not defined in Site Parameters.');
        }
        //echo "vacationAccruedDaysPerMonth=$vacationAccruedDaysPerMonth <br>"; //fellows 1.666

        //TODO: fix summary: http://127.0.0.1/time-away-request/summary/?filter%5Busers%5D%5B%5D=762&filter%5Btypes%5D%5B%5D=1&filter%5Btypes%5D%5B%5D=2&filter%5Bsubmit%5D=
        if( !$yearRange ) {
            $yearRange = $this->getCurrentAcademicYearRange();
        }
        //echo "user=".$user.", approvalGroupType=".$approvalGroupType.", yearRange=".$yearRange."<br>";

        //Use EmploymentStartEnd to get number of month
        $totalAccruedMonths = $this->getTotalAccruedMonths($user,$yearRange);
        //$totalAccruedMonths = 12; //Old version without user's start/end dates

        //echo "totalAccruedMonths=".$totalAccruedMonths."<br>";
        //$totalAccruedDays = 12 * $vacationAccruedDaysPerMonth;
        $totalAccruedDays = $totalAccruedMonths * $vacationAccruedDaysPerMonth;

        //TODO: Use Employment period to get effort %

        $maxVacationDays = $this->getValueApprovalGroupTypeByUser("maxVacationDays", $user, $approvalGroupType);
        if ($maxVacationDays && $totalAccruedDays > $maxVacationDays) {
            $totalAccruedDays = $maxVacationDays;
        }
        //echo "totalAccruedDays=".$totalAccruedDays."<br>";

        $totalAccruedDays = round($totalAccruedDays);
        //echo "### totalAccruedDays=".$totalAccruedDays."<br>";

        return $totalAccruedDays;
    }

    //$yearRange=2024-2025
    //$approvalGroupType='Faculty', 'Fellows', 'Residents' ...
    public function getTotalAccruedDaysUsingEmplPeriods( $user=NULL, $yearRange=NULL, $approvalGroupType=NULL ) {
        // Split the yearRange period by Empl Periods.
        // For each emplPeriod get number of accrued months and effort in %
        // Effort % (in fraction, i.e. 0.6) multiple by number of accrued month  and save in accruedDays
        // Sum all accruedDays in totalAccruedDays
        // return totalAccruedDays

        $totalAccruedDays = 0;

        echo "yearRange=$yearRange <br>";

        // Split the yearRange period by Empl Periods.
        // Get EmploymentStatus sorted by startdate in $yearRange
        //$employmentStatuses = $user->getEmploymentStatus();

        $dates = $this->getCurrentAcademicYearStartEndDates();
        $startAcademicYearDateStr = $dates['startDate'];
        $endAcademicYearDateStr = $dates['endDate'];

        $parameters = array();
        $repository = $this->em->getRepository(EmploymentStatus::class);
        $dql =  $repository->createQueryBuilder("emplstatus");
        $dql->leftJoin("emplstatus.user", "user");

        $dql->where("emplstatus.hireDate BETWEEN :startDate AND :endDate");
        $parameters['startDate'] = $startAcademicYearDateStr;
        $parameters['endDate'] = $endAcademicYearDateStr;

        if( $user ) {
            $dql->andWhere("user.id = :userid");
            $parameters['userid'] = $user->getId();
        }

        $query = $dql->getQuery(); //$query = $this->em->createQuery($dql);

        $query->setParameters($parameters);

        $emplPeriods = $query->getResult();

        echo "emplPeriods=".count($emplPeriods)."<br>";

        foreach($emplPeriods as $emplPeriod) {

        }

        return $totalAccruedDays;
    }


    //Calculate number of month for user according to the start/end dates
    //$yearRange=2024-2025
    //Calculate number of month for user according to the start/end dates
    //$yearRange=2024-2025
    public function getTotalAccruedMonths( $user, $yearRangeStr, $startDate=NULL, $endDate=NULL, $testing=FALSE ) {
        //echo "<br>get Total Accrued Months yearRangeStr=[$yearRangeStr]<br>";
        $totalAccruedMonths = NULL;
        //return $totalAccruedMonths;

        if( $testing === FALSE ) {
            if (!$user) {
                //echo "No user => totalAccruedMonths=12"."<br>";
                return 12; //$totalAccruedMonths; //remove for testing
            }

            if (!$startDate || !$endDate) {
                $userStartEndDates = $user->getEmploymentStartEndDates($asString = false);
                if (!$startDate) {
                    $startDate = $userStartEndDates['startDate'];
                }
                if (!$endDate) {
                    $endDate = $userStartEndDates['endDate'];
                }
            }
        }
        //echo "startDate=".$startDate.", endDate=".$endDate."<br>";

        //years
        $yearRangeArr = $this->getYearsFromYearRangeStr($yearRangeStr);
        //$previousYear = $yearRangeArr[0];
        $currentYear = $yearRangeArr[1];
        //echo "currentYear=$currentYear <br>";

        //Use the class global academicYearStartDateStr and academicYearEndDateStr
        // to prevent modification on the repeating DB calls.
        // As the result the end date is not consistent: 2025-06-30, 2025-06-30, 2025-05-30
        //TODO: why end year date is changed?
        //??? https://stackoverflow.com/questions/7956027/how-to-stop-doctrine-2-from-caching-a-result-in-symfony-2
        //Use $entityManager->detach($post);
        if(0) {
            if (!$this->academicYearStartDateStr || !$this->academicYearEndDateStr) {
                $academicYearStartDateStrThis = $this->getEdgeAcademicYearDate($currentYear, 'Start');
                $academicYearEndDateStrThis = $this->getEdgeAcademicYearDate($currentYear, 'End');
//            $academicYearEndDateArray = $this->getSiteSettingsStartEndAcademicDates();
//            $academicYearStartDateStrThis = $academicYearEndDateArray['academicYearStart'];
//            $academicYearEndDateStrThis = $academicYearEndDateArray['academicYearEnd'];
//            $academicYearStartDateStrThis = ((int)$currentYear - 1)."-".$academicYearStartDateStrThis;
//            $academicYearEndDateStrThis = $currentYear."-".$academicYearEndDateStrThis;
                $this->academicYearStartDateStr = $academicYearStartDateStrThis;
                $this->academicYearEndDateStr = $academicYearEndDateStrThis;
                //echo "academicYearStartDateStr=".$this->academicYearStartDateStr.", academicYearEndDateStr=".$this->academicYearEndDateStr."<br>";
            }

            $academicYearStartDateStr = $this->academicYearStartDateStr;
            $academicYearEndDateStr = $this->academicYearEndDateStr;
        } else {
            $academicYearStartDateStr = $this->getEdgeAcademicYearDate($currentYear, 'Start');
            $academicYearEndDateStr = $this->getEdgeAcademicYearDate($currentYear, 'End');
        }
        //echo "academicYearStartDateStr=".$academicYearStartDateStr.", academicYearEndDateStr=".$academicYearEndDateStr."<br>";

        //convert $academicYearStartDateStr to $academicYearStartDate
        $academicYearStartDate = \DateTime::createFromFormat('Y-m-d', $academicYearStartDateStr);
        $academicYearEndDate = \DateTime::createFromFormat('Y-m-d', $academicYearEndDateStr);


        //S - start employment date
        //E - end employment date
        //Case 1: ------1July2024-----S-----E----30June2025-----------
        //interval: between S and E

        //Case 2: ---S---1July2024-------E-------30June2025-----------
        //interval: between 1July2024 and E

        //Case 3: ------1July2024-----S---------30June2025------E-----
        //interval: between S and 30June2025

        //Case 4: --S--E----1July2024--------------30June2025-----------
        //interval: 0 months

        //Case 5: ------1July2024--------------30June2025----S---E----
        //interval: 0 months

        if( $startDate && $endDate ) {
            //Common cases 1,2,3: S and/or E inside 1July2024 and 30June2025
            //Case 1: ------1July2024-----S-----E----30June2025-----------
            //interval: between S and E
            if( $totalAccruedMonths === NULL &&
                $startDate > $academicYearStartDate && $startDate < $academicYearEndDate
                && $endDate > $academicYearStartDate && $endDate < $academicYearEndDate
            ) {
                if($testing) {
                    echo "Case 1: ------1July2024-----S-----E----30June2025----------- <br>";
                }
                $diffDates = $this->diffBetweenTwoDates($startDate, $endDate);
                $totalAccruedMonths = $diffDates['totalMonths'];
                $days = $diffDates['days'];
                if ($days > 0 && $days <= 15) {
                    $totalAccruedMonths = $totalAccruedMonths + 0.5;
                }
                if ($days > 15) {
                    $totalAccruedMonths = $totalAccruedMonths + 1;
                }
            }

            //Case 2: ---S---1July2024-------E-------30June2025-----------
            //interval: between 1July2024 and E
            if( $totalAccruedMonths === NULL
                && $startDate < $academicYearStartDate
                && $endDate > $academicYearStartDate && $endDate < $academicYearEndDate
            ) {
                if($testing) {
                    echo "Case 2: ---S---1July2024-------E-------30June2025----------- <br>";
                }
                $diffDates = $this->diffBetweenTwoDates($academicYearStartDate, $endDate);
                $totalAccruedMonths = $diffDates['totalMonths'];
                $days = $diffDates['days'];
                if ($days > 0 && $days <= 15) {
                    $totalAccruedMonths = $totalAccruedMonths + 0.5;
                }
                if ($days > 15) {
                    $totalAccruedMonths = $totalAccruedMonths + 1;
                }
            }

            //Case 3: ------1July2024-----S---------30June2025------E-----
            //interval: between S and 30June2025
            if( $totalAccruedMonths === NULL
                && $startDate > $academicYearStartDate && $startDate < $academicYearEndDate
                && $endDate > $academicYearEndDate
            ) {
                if($testing) {
                    echo "Case 3: ------1July2024-----S---------30June2025------E----- <br>";
                }
                $diffDates = $this->diffBetweenTwoDates($startDate, $academicYearEndDate);
                $totalAccruedMonths = $diffDates['totalMonths'];
                $days = $diffDates['days'];
                if ($days > 0 && $days <= 15) {
                    $totalAccruedMonths = $totalAccruedMonths + 0.5;
                }
                if ($days > 15) {
                    $totalAccruedMonths = $totalAccruedMonths + 1;
                }
            }

            //Case 4: --S--E----1July2024--------------30June2025-----------
            //interval: 0 months
            if( $totalAccruedMonths === NULL
                && $startDate < $academicYearStartDate && $endDate < $academicYearStartDate
            ) {
                if($testing) {
                    echo "Case 4: --S--E----1July2024--------------30June2025----------- <br>";
                }
                $totalAccruedMonths = 0;
            }

            //Case 5: ------1July2024--------------30June2025----S---E----
            //interval: 0 months
            if( $totalAccruedMonths === NULL
                && $startDate > $academicYearEndDate && $endDate > $academicYearEndDate
            ) {
                if($testing) {
                    echo "Case 5: ------1July2024--------------30June2025----S---E---- <br>";
                }
                $totalAccruedMonths = 0;
            }
        }

        //Case 1a: ------1July2024-----S------30June2025-----------
        //interval: between S and 30June2025
        if(
            $totalAccruedMonths === NULL
            && $startDate
            && $startDate > $academicYearStartDate && $startDate < $academicYearEndDate
        ) {
            if($testing) {
                echo "Case 1a: ------1July2024-----S------30June2025----------- <br>";
            }
            $diffDates = $this->diffBetweenTwoDates($startDate, $academicYearEndDate);
            $totalAccruedMonths = $diffDates['totalMonths'];
            $days = $diffDates['days'];
            if ($days > 0 && $days <= 15) {
                $totalAccruedMonths = $totalAccruedMonths + 0.5;
            }
            if ($days > 15) {
                $totalAccruedMonths = $totalAccruedMonths + 1;
            }
        }

        //Case 1b: ------1July2024-----E------30June2025-----------
        //interval: between S and 30June2025
        if( $totalAccruedMonths === NULL
            && $endDate
            && $endDate > $academicYearStartDate && $endDate < $academicYearEndDate
        ) {
            if($testing) {
                echo "Case 1b: ------1July2024-----E------30June2025----------- <br>";
            }
            $diffDates = $this->diffBetweenTwoDates($academicYearStartDate, $endDate);
            $totalAccruedMonths = $diffDates['totalMonths'];
            $days = $diffDates['days'];
            if ($days > 0 && $days <= 15) {
                $totalAccruedMonths = $totalAccruedMonths + 0.5;
            }
            if ($days > 15) {
                $totalAccruedMonths = $totalAccruedMonths + 1;
            }
        }

//        else {
//            //$startDate && $endDate are not set => 12 month
//            echo "Case 0: startDate && endDate are not set => 12 month <br>";
//            $totalAccruedMonths = 12;
//        }

        if( $totalAccruedMonths === NULL ) {
            if($testing) {
                echo "Case 0: exception => 12 month <br>";
            }
            $totalAccruedMonths = 12;
        }

        //echo 'yearRangeStr='.$yearRangeStr.", totalAccruedMonths=".$totalAccruedMonths.
        //", monthCount=".$monthCount.
        //": totalAccruedMonths=".$totalAccruedMonths.", monthCount=".$monthCount."<br>";

        //$this->academicYearStartDateStr = '';
        //$this->academicYearEndDateStr = '';

        return $totalAccruedMonths;
    }
//    public function getTotalAccruedMonths_ORIG( $user, $yearRangeStr, $startDate=NULL, $endDate=NULL, $testing=FALSE ) {
//        //echo "<br>get Total Accrued Months yearRangeStr=[$yearRangeStr]<br>";
//        $totalAccruedMonths = 12;
//        //return $totalAccruedMonths;
//
//        if( !$user ) {
//            //echo "No user => totalAccruedMonths=$totalAccruedMonths"."<br>";
//            return $totalAccruedMonths; //remove for testing
//        }
//
//        if( !$startDate || !$endDate ) {
//            $userStartEndDates = $user->getEmploymentStartEndDates($asString = false);
//            if( !$startDate ) {
//                $startDate = $userStartEndDates['startDate'];
//            }
//            if( !$endDate ) {
//                $endDate = $userStartEndDates['endDate'];
//            }
//        }
//        //echo "startDate=".$startDate.", endDate=".$endDate."<br>";
//
////        $startDateStr = null;
////        if( $startDate ) {
////            $startDateStr = $startDate->format('d/m/Y');
////        }
////        $endDateStr = null;
////        if( $endDate ) {
////            $endDateStr = $endDate->format('d/m/Y');
////        }
//        //echo "startDateStr=".$startDateStr.", endDateStr=".$endDateStr."<br>";
//        //startDate=01/07/2020, endDate=01/07/2021
//        //$yearRange = 2024-2025
//
//        //years
//        $yearRangeArr = $this->getYearsFromYearRangeStr($yearRangeStr);
//        //$previousYear = $yearRangeArr[0];
//        $currentYear = $yearRangeArr[1];
//        //echo "previousYear=$previousYear, currentYear=$currentYear <br>";
//
//        //Testing
//        if(0) {
//            $academicYearStartDateStr = $this->getEdgeAcademicYearDate($currentYear, 'Start');
//            $academicYearEndDateStr = $this->getEdgeAcademicYearDate($currentYear, 'End');
//            echo "###### academicYearStartDateStr=" . $academicYearStartDateStr . ", academicYearEndDateStr=" . $academicYearEndDateStr . "<br>";
//            $academicYearStartDateStr = $this->getEdgeAcademicYearDate($currentYear, 'Start');
//            $academicYearEndDateStr = $this->getEdgeAcademicYearDate($currentYear, 'End');
//            echo "###### academicYearStartDateStr=" . $academicYearStartDateStr . ", academicYearEndDateStr=" . $academicYearEndDateStr . "<br>";
//            $academicYearStartDateStr = $this->getEdgeAcademicYearDate($currentYear, 'Start');
//            $academicYearEndDateStr = $this->getEdgeAcademicYearDate($currentYear, 'End');
//            echo "###### academicYearStartDateStr=" . $academicYearStartDateStr . ", academicYearEndDateStr=" . $academicYearEndDateStr . "<br>";
//            $academicYearStartDateStr = $this->getEdgeAcademicYearDate($currentYear, 'Start');
//            $academicYearEndDateStr = $this->getEdgeAcademicYearDate($currentYear, 'End');
//            echo "###### academicYearStartDateStr=" . $academicYearStartDateStr . ", academicYearEndDateStr=" . $academicYearEndDateStr . "<br>";
//            $academicYearStartDateStr = $this->getEdgeAcademicYearDate($currentYear, 'Start');
//            $academicYearEndDateStr = $this->getEdgeAcademicYearDate($currentYear, 'End');
//            echo "###### academicYearStartDateStr=" . $academicYearStartDateStr . ", academicYearEndDateStr=" . $academicYearEndDateStr . "<br>";
//            //exit('1111');
//        }
//
//        //Use the class global academicYearStartDateStr and academicYearEndDateStr
//        // to prevent modification on the repeating DB calls. As the result the end date is not consistent: 2025-06-30, 2025-06-30, 2025-05-30
//        //TODO: why end year date is changed?
//        if(1) {
//            if (!$this->academicYearStartDateStr || !$this->academicYearEndDateStr) {
//                $academicYearStartDateStrThis = $this->getEdgeAcademicYearDate($currentYear, 'Start');
//                $academicYearEndDateStrThis = $this->getEdgeAcademicYearDate($currentYear, 'End');
////            $academicYearEndDateArray = $this->getSiteSettingsStartEndAcademicDates();
////            $academicYearStartDateStrThis = $academicYearEndDateArray['academicYearStart'];
////            $academicYearEndDateStrThis = $academicYearEndDateArray['academicYearEnd'];
////            $academicYearStartDateStrThis = ((int)$currentYear - 1)."-".$academicYearStartDateStrThis;
////            $academicYearEndDateStrThis = $currentYear."-".$academicYearEndDateStrThis;
//                $this->academicYearStartDateStr = $academicYearStartDateStrThis;
//                $this->academicYearEndDateStr = $academicYearEndDateStrThis;
//                //echo "academicYearStartDateStr=".$this->academicYearStartDateStr.", academicYearEndDateStr=".$this->academicYearEndDateStr."<br>";
//            }
//
//            $academicYearStartDateStr = $this->academicYearStartDateStr;
//            $academicYearEndDateStr = $this->academicYearEndDateStr;
//        } else {
//            $academicYearStartDateStr = $this->getEdgeAcademicYearDate($currentYear, 'Start');
//            $academicYearEndDateStr = $this->getEdgeAcademicYearDate($currentYear, 'End');
//        }
//
//        //$academicYearStartDateStr = '2024-07-01';
//        //$academicYearEndDateStr = '2025-06-30';
//        //echo "academicYearStartDateStr=".$academicYearStartDateStr.", academicYearEndDateStr=".$academicYearEndDateStr."<br>";
//        //return $totalAccruedMonths;
//
//        //convert $academicYearStartDateStr to $academicYearStartDate
//        $academicYearStartDate = \DateTime::createFromFormat('Y-m-d', $academicYearStartDateStr);
//        $academicYearEndDate = \DateTime::createFromFormat('Y-m-d', $academicYearEndDateStr);
//        //$academicYearStartDate = NULL;
//        //$academicYearEndDate = NULL;
//
//
////        $startDate->setTime(0, 0, 0);
////        $endDate->setTime(0, 0, 0);
////        $academicYearStartDate->setTime(0, 0, 0);
////        $academicYearEndDate->setTime(0, 0, 0);
//
//        $monthCount = 0;
//        $user = true;
//        //check if user startDate > $academicYearStartDate
//        if( $startDate && $academicYearStartDate && $startDate > $academicYearStartDate ) {
//            $monthCount = $this->diffInMonths($startDate, $academicYearStartDate); //accrued for this year
//            $totalAccruedMonths = $totalAccruedMonths - $monthCount;
//
//            //echo "monthCount=".$monthCount."<br>";
//
//            if( $user ) {
//                //Use prorated days for intervals: 1-15=>1 day, 16-31=>2 days
//                echo "User started after beginning academic year: " .
//                    $startDate->format('d-F-Y') . " > " .
//                    $academicYearStartDate->format('d-F-Y') .
//                    ", monthCount=" . $monthCount .
//                    ", totalAccruedMonths=" . $totalAccruedMonths .
//                    "<br>";
//            }
//        }
////        if( $endDate && $academicYearStartDate && $endDate > $academicYearStartDate ) {
////            $monthCount = $this->diffInMonths($endDate, $academicYearStartDate); //12-$monthCount=total vacation days for this year
////            $totalAccruedMonths = $totalAccruedMonths + $monthCount;
////            echo "User ended before ending academic year: ".
////                $endDate->format('d-F-Y')." > ".
////                $academicYearStartDate->format('d-F-Y').
////                ", monthCount=".$monthCount.
////                ", totalAccruedMonths=".$totalAccruedMonths.
////                "<br>";
////        }
//        if( $endDate && $academicYearEndDate && $endDate < $academicYearEndDate ) {
//            $monthCount = $this->diffInMonths($academicYearEndDate, $endDate); //12-$monthCount=total vacation days for this year
//            $totalAccruedMonths = $totalAccruedMonths - $monthCount;
//
//            if( $user ) {
//                echo "User ended before ending academic year: " .
//                    $endDate->format('d-F-Y') . " < " .
//                    $academicYearEndDate->format('d-F-Y') .
//                    ", monthCount=" . $monthCount .
//                    ", totalAccruedMonths=" . $totalAccruedMonths .
//                    "<br>";
//            }
//        }
//
//        if(1) {
//            echo "1 totalAccruedMonths=$totalAccruedMonths <br>";
//            //Prorate days:
//            //If employment date in the interval 1-15 days => add 0.5 month
//            //If employment date in the interval 15-31 days => add 1 full month
//            if ($startDate) {
//                $day = $startDate->format('d');
//                if ($day > 0 && $day <= 15) {
//                    $totalAccruedMonths = $totalAccruedMonths - 0.5;
//                }
//                if ($day > 15 && $day <= 31) {
//                    //$totalAccruedMonths = $totalAccruedMonths - 1;
//                }
//            }
//
//            if ($endDate) {
//                $day = $endDate->format('d');
//                if ($day > 0 && $day <= 15) {
//                    $totalAccruedMonths = $totalAccruedMonths - 0.5;
//                }
//                if ($day > 15 && $day <= 31) {
//                    //$totalAccruedMonths = $totalAccruedMonths + 1;
//                }
//            }
//            echo "2 totalAccruedMonths=$totalAccruedMonths <br>";
//        }
//
//        //echo 'yearRangeStr='.$yearRangeStr.", totalAccruedMonths=".$totalAccruedMonths.", monthCount=".$monthCount.": totalAccruedMonths=".$totalAccruedMonths.", monthCount=".$monthCount."<br>";
//        if( $user ) {
//            //exit('end of total accrued month');
//        }
//
//        return $totalAccruedMonths;
//    }
    
    public function testAccruedDays() {
        $count = 0;

        $yearRangeStr = '2023-2024';
        $count += $this->getTestTotalAccruedMonths($yearRangeStr, NULL, '08/31/2024', 24);

        $yearRangeStr = '2024-2025';
        $count += $this->getTestTotalAccruedMonths($yearRangeStr, '07/22/2024', '08/25/2024', 3); //1 day for month 7, 2 days for month 8 => 3
        $count += $this->getTestTotalAccruedMonths($yearRangeStr, '07/01/2024', '08/01/2034', 24); //12 month * 2 = 24. July-August - next year => not counted
        $count += $this->getTestTotalAccruedMonths($yearRangeStr, '07/10/2024', '08/12/2024', 3); //2 day for month 7, 1 day for month 8 => 3
        $count += $this->getTestTotalAccruedMonths($yearRangeStr, '07/01/2024', '08/31/2024', 4); //2 day for month 7, 2 day for month 8 => 4
        $count += $this->getTestTotalAccruedMonths($yearRangeStr, '07/01/2024', '08/31/2024', 4); //2 day for month 7, 2 day for month 8 => 4
        $count += $this->getTestTotalAccruedMonths($yearRangeStr, '07/01/2024', '09/01/2024', 4); //2 day for month 7, 2 day for month 8 => 4
        $count += $this->getTestTotalAccruedMonths($yearRangeStr, '07/01/2024', '09/02/2024', 5); //2 day for month 7, 2 day for month 8, 1 day for month 9 => 5
        $count += $this->getTestTotalAccruedMonths($yearRangeStr, '07/01/2024', '09/02/2024', 5); //2 day for month 7, 2 day for month 8 and 1 day for month 9 => 5
        $count += $this->getTestTotalAccruedMonths($yearRangeStr, '07/01/2024', '01/01/2025', 12); //July,Aug,Sep,Oct,Nov,Dec = 6 month => 12 days

        return $count;
        //exit('EOF testAccruedDays');
    }
    public function getTestTotalAccruedMonths($yearRangeStr,$startDateStr,$endDateStr,$expectedResult=NULL) {
        $datetime = new \DateTime();
        //$timezone = new \DateTimeZone('America/New_York');
        //$datetime->setTimezone($timezone);
        //$datetime->setTime(0, 0, 0);

        $startDate = NULL;
        $endDate = NULL;
        if( $startDateStr ) {
            $startDate = $datetime->createFromFormat('m/d/Y', $startDateStr);
        }
        if( $endDateStr ) {
            $endDate = $datetime->createFromFormat('m/d/Y', $endDateStr);
        }
        //$startDate = $datetime->createFromFormat('m/d/Y H:i:s',$startDateStr." 00:00:00");
        //$endDate = $datetime->createFromFormat('m/d/Y H:i:s',$endDateStr." 23:59:59");

        //test 1 diffBetweenTwoDates
        if(0) {
            echo "<br>";
            $diffBetweenTwoDates = $this->diffBetweenTwoDates($startDate, $endDate);
            dump($diffBetweenTwoDates);
            $totalAccruedMonths = $diffBetweenTwoDates['totalMonths'];
            $months = $diffBetweenTwoDates['months'];
            $days = $diffBetweenTwoDates['days'];

            $startDateStr2 = "";
            $endDateStr2 = "";
            if( $startDate ) {
                $startDateStr2 = $startDate->format('d F Y');
            }
            if( $endDate ) {
                $endDateStr2 = $endDate->format('d F Y');
            }
            //echo "startDate=".$startDate->format('m F Y H:i:s').", endDate=".$endDate->format('m F Y H:i:s')." => ";
            echo $startDateStr2 . " - " . $endDateStr2 . " => ";

            echo "totalAccruedMonths=$totalAccruedMonths, months=$months, days=$days <br>";
            return $months;
        }

        //Test getTotalAccruedMonths
        //$startDate = strtotime($startDateStr);
        //$endDate = strtotime($endDateStr);
        //echo "<br>";

        //echo $expectedResult.": startDate=".$startDate->format('m/d/Y').", endDate=".$endDate->format('m/d/Y')."<br>";

//        $startDateStr2 = "";
//        $endDateStr2 = "";
//        if( $startDate ) {
//            $startDateStr2 = $startDate->format('d F Y H:i:s');
//        }if( $endDate ) {
//            $endDateStr2 = $endDate->format('d F Y H:i:s');
//        }
        //echo $expectedResult.": startDate=".$startDateStr2.", endDate=".$endDateStr2.", yearRangeStr=$yearRangeStr"."<br>";

        $totalAccruedMonths = $this->getTotalAccruedMonths(NULL,$yearRangeStr,$startDate,$endDate,$testing=TRUE);
        //echo "totalAccruedMonths===$totalAccruedMonths <= $startDateStr - $endDateStr <br>";

        $vacationAccruedDaysPerMonth = 2;
        $totalAccruedDays = $vacationAccruedDaysPerMonth * $totalAccruedMonths;
        //echo "totalAccruedDays===$totalAccruedDays <br>";

        if( $expectedResult ) {
            if( $expectedResult == (int) $totalAccruedDays ) {
                //echo "Pass <br>";
                return 1;
            } else {
                //echo "Fail <br>";
                return 0;
            }
        }
        return 0;
    }

    public function getTotalAccruedDaysByGroup( $approvalGroupType ) {
        $vacationAccruedDaysPerMonth = $approvalGroupType->getVacationAccruedDaysPerMonth();
        if( !$vacationAccruedDaysPerMonth ) {
            //return 0;
            $vacationAccruedDaysPerMonth = 2;
            //throw new \InvalidArgumentException('vacationAccruedDaysPerMonth is not defined.');
        }
        $totalAccruedDays = 12 * $vacationAccruedDaysPerMonth;

        $maxVacationDays = $approvalGroupType->getMaxVacationDays();
        if( $maxVacationDays && $totalAccruedDays > $maxVacationDays ) {
            $totalAccruedDays = $maxVacationDays;
        }

        $totalAccruedDays = round($totalAccruedDays);
        return $totalAccruedDays;
    }

    public function getPendingCarryOverRequests($user) {

        if( $this->security->isGranted('ROLE_VACREQ_SUPERVISOR') == false ) {
           return null;
        }

        //1) get user's supervisor group
        //$userRoles = $this->em->getRepository('AppUserdirectoryBundle:User')->
            //findUserChildRolesBySitePermissionObjectAction($user,'vacreq',"vacReqRequest","changestatus-carryover");

        $groupParams = array('asObject'=>true);
        $groupParams['permissions'][] = array('objectStr'=>'VacReqRequest','actionStr'=>'changestatus-carryover');
        $groups = $this->getGroupsByPermission($user,$groupParams);

        $idArr = array();
        foreach( $groups as $group ) {
            $idArr[] = $group->getId();
        }

        //process.py script: replaced namespace by ::class: ['AppVacReqBundle:VacReqRequest'] by [VacReqRequest::class]
        $repository = $this->em->getRepository(VacReqRequest::class);
        $dql =  $repository->createQueryBuilder("request");
        $dql->leftJoin("request.institution", "institution");
        $dql->leftJoin("request.requestType", "requestType");

        $dql->where("request.status = 'pending' AND requestType.abbreviation = 'carryover'");

        $idsStr = implode(",", $idArr);
        if( $idsStr ) {
            //exit("idsStr=".$idsStr);
            $dql->andWhere("institution.id IN (" . $idsStr . ") ");
        }

        $query = $dql->getQuery(); //$query = $this->em->createQuery($dql);

//        $query->setParameters(array(
//            'groupIds' => implode(",",$idArr),
//        ));

        $requests = $query->getResult();

        return $requests;
    }

    public function getCarryOverRequestsByUserStatusYear($user,$status,$year,$exceptRequest=NULL) {

        //echo "status=$status, year=$year <br>";

        $params = array();

        //process.py script: replaced namespace by ::class: ['AppVacReqBundle:VacReqRequest'] by [VacReqRequest::class]
        $repository = $this->em->getRepository(VacReqRequest::class);
        $dql =  $repository->createQueryBuilder("request");

        $dql->leftJoin("request.user", "user");
        $dql->leftJoin("request.requestType", "requestType");

        $dql->where("requestType.abbreviation = 'carryover'");

        $dql->andWhere("user.id = :userId");
        $params['userId'] = $user->getId();

        $dql->andWhere("request.status = :status");
        $params['status'] = $status;

        $dql->andWhere("request.destinationYear = :destinationYear");
        $params['destinationYear'] = $year;

        if( $exceptRequest ) {
            //echo "exceptRequest=".$exceptRequest->getId()."<br>";
            $dql->andWhere("request.id != :exceptRequestId");
            $params['exceptRequestId'] = $exceptRequest->getId();
        }

        $dql->orderBy("request.id","DESC"); //highest id on top

        $query = $dql->getQuery(); //$query = $this->em->createQuery($dql);

        if( count($params) > 0 ) {
            $query->setParameters($params);
        }

        $requests = $query->getResult();

        return $requests;
    }

    //used in navbar. return HTML
    public function getTotalPendingCarryoverRequests($user) {

        //{{ path('vacreq_incomingrequests',{'filter[pending]':1}) }}
        $html = '
                <a id="incoming-orders-menu-badge"
                      class="element-with-tooltip-always"
                      title="Pending Approval" data-toggle="tooltip"
                      data-placement="bottom"
                      href="www.google.com"
                    ><span class="badge">33</span></a>';

        $html = "test";

        return "";
    }

    //Get a full header message based on the noteForVacationDays and noteForCarryOverDays
    //replace numbers from VacReqApprovalTypeList (accrued days, max days)
    //Used in new vacation and carryover request new page
    public function getHeaderInfoMessages($user, $approvalGroupType=null) {
        //$userSecUtil = $this->container->get('user_security_utility');

        if( !$approvalGroupType ) {
            $approvalGroupType = $this->getSingleApprovalGroupType($user);
        }

        if( $approvalGroupType ) {
            $approvalGroupTypeName = $approvalGroupType->getName();
        } else {
            $approvalGroupTypeName = "Faculty";
        }

        //{{ yearRange }} Accrued Vacation Days as of today: {{ accruedDays }}
        //"You have accrued X vacation days this academic year (and will accrue X*12 by [date of academic year start from site settings, show as July 1st, 20XX]."
        //"You have accrued 10 vacation days this academic year (and will accrue 24 by July 1st, 2016."
        //accrued days up to this month calculated by vacationAccruedDaysPerMonth
        //$accruedDays = $this->getAccruedDaysUpToThisMonth($user,$approvalGroupType);
        $facultyTotalAccruedDays = $this->getTotalAccruedDays(NULL,NULL,$approvalGroupType); //current year
        $totalAccruedDays = $this->getTotalAccruedDays($user,NULL,$approvalGroupType); //current year

        //$currentStartYear
        //$yearRange = $this->getCurrentAcademicYearRange();
        //$yearRangeArr = explode("-",$yearRange);
        //$currentStartYear = $yearRangeArr[1];

        //$startAcademicYearStr = $this->getEdgeAcademicYearDate( $currentStartYear, "End" );
        //$startAcademicYearDate = new \DateTime($startAcademicYearStr);
        //$startAcademicYearDateStr = $startAcademicYearDate->format("F jS, Y");

//        $academicYearStart = $userSecUtil->getSiteSettingParameter('academicYearStart','vacreq');
//        if( !$academicYearStart ) {
//            throw new \InvalidArgumentException('academicYearStart is not defined in Site Parameters.');
//        }
        $academicYearStart = $this->getAcademicYearStart();
        $academicYearStartString = $academicYearStart->format("F jS");

        //$vacationAccruedDaysPerMonth = $userSecUtil->getSiteSettingParameter('vacationAccruedDaysPerMonth','vacreq');
        $vacationAccruedDaysPerMonth = $this->getValueApprovalGroupTypeByUser('vacationAccruedDaysPerMonth',$user,$approvalGroupType);
        if( !$vacationAccruedDaysPerMonth ) {
            $vacationAccruedDaysPerMonth = 2;
            //throw new \InvalidArgumentException('vacationAccruedDaysPerMonth is not defined in Site Parameters.');
        }
        if( floor($vacationAccruedDaysPerMonth) == $vacationAccruedDaysPerMonth ) {
            //echo $vacationAccruedDaysPerMonth." is int <br>";
            $vacationAccruedDaysPerMonthStr = intval($vacationAccruedDaysPerMonth);
        } else {
            //echo $vacationAccruedDaysPerMonth." is not int <br>";
            $vacationAccruedDaysPerMonthStr = number_format((float)$vacationAccruedDaysPerMonth, 2, '.', '');
        }

        //Calculate May 30th as 1 month before End Year
//        $academicYearEnd = $userSecUtil->getSiteSettingParameter('academicYearEnd','vacreq');
//        if( !$academicYearEnd ) {
//            throw new \InvalidArgumentException('academicYearEnd is not defined in Site Parameters.');
//        }
        $academicYearEnd = $this->getAcademicYearEnd();
        //shift $academicYearEnd by month back
        $academicYearEnd->modify("-1 month"); //May 30th
        //$academicYearEnd->modify("last day of previous month"); //May 31st
        $academicYearEndString = $academicYearEnd->format("F jS");

        //get max carry over days
        //$maxCarryOverVacationDays = $userSecUtil->getSiteSettingParameter('maxCarryOverVacationDays','vacreq');
        $maxCarryOverVacationDays = $this->getValueApprovalGroupTypeByUser('maxCarryOverVacationDays',$user,$approvalGroupType);
        if( !$maxCarryOverVacationDays ) {
            $maxCarryOverVacationDays = 10;
        }

        //TODO: ask why fellows don't have the actual accrued days message:
        // Based on your start employment dates (06/10/2024), you have 24 accrued days per year
        // Should I add this message to fellows? Answer: fellows can not carry over request

        //Faculty accrue 24 vacation days per year, or 2 days per month. If you start employment after July 1, it is prorated.
        //The maximum one can carry over to the next fiscal year 10 days, no exceptions.
        //This request must be made in writing and approved by your Vice Chair. The request is due by May 30th of the same fiscal year.
        $accruedDaysString = $approvalGroupTypeName." accrue $facultyTotalAccruedDays vacation days per year, or"; //$totalAccruedDays
        $accruedDaysString .= " " . $vacationAccruedDaysPerMonthStr . " days per month.";
        $accruedDaysString .= " If you start employment after $academicYearStartString, it is prorated.";

        ////////// Based on ... message //////////////
        $startDateStr = NULL;
        $endDateStr = NULL;
        $userStartEndDates = $user->getEmploymentStartEndDates($asString = false);
        $startDate = $userStartEndDates['startDate'];
        if( $startDate ) {
            $startDateStr = $startDate->format('m/d/Y');
        }
        $endDate = $userStartEndDates['endDate'];
        if( $endDate ) {
            $endDateStr = $endDate->format('m/d/Y');
        }
        $totalAccruedDaysStr = "Based on the assumed [24] accrued days per year";
        if( $startDateStr && $endDateStr ) {
            $totalAccruedDaysStr = "Based on your start/end employment dates ($startDateStr - $endDateStr)";
        }
        elseif( $startDateStr  ) {
            $totalAccruedDaysStr = "Based on your start employment dates ($startDateStr)";
        }
        elseif( $endDateStr  ) {
            $totalAccruedDaysStr = "Based on your end employment dates ($endDateStr)";
        }
        ////////// EOF Based on ... message //////////////

        ////////////// carry over allowed ///////////////////
        $carriedOverDaysString = null;
        $remainingDaysString = null;
        $allowCarryOver = $this->getValueApprovalGroupTypeByUser('allowCarryOver',$user,$approvalGroupType);
        if( $allowCarryOver ) {
            $accruedDaysString .= "<br>The maximum one can carry over to the next fiscal year is $maxCarryOverVacationDays days, no exceptions.";
            $accruedDaysString .= " This request must be made in writing and approved by your Vice Chair.";
            $accruedDaysString .= " The request is due by $academicYearEndString of the same fiscal year.";


            //If for the current academic year the value of carried over vacation days is not empty and not zero for the logged in user,
            // append a third sentence stating "You have Y additional vacation days carried over from [Current Academic Year -1, show as 2014-2015]."
            $currentYearRange = $this->getCurrentAcademicYearRange();
            $carriedOverDays = $this->getUserCarryOverDays($user, $currentYearRange);
            //echo "carriedOverDays=".$carriedOverDays."<br>";
            $carriedOverDaysString = null;
            if ($carriedOverDays) {
                $lastYearRange = $this->getPreviousAcademicYearRange();
                $carriedOverDaysString = "You have " . $carriedOverDays . " additional vacation days carried over from " . $lastYearRange;
            }

            //Carry over days to the next academic year
            $nextYearRange = $this->getNextAcademicYearRange();
            $carriedOverDaysNextYear = $this->getUserCarryOverDays($user, $nextYearRange);
            //echo "carriedOverDaysNextYear=".$carriedOverDaysNextYear."<br>";
            //$carriedOverDaysNextYearString = null;
            if ($carriedOverDaysNextYear) {
                if ($carriedOverDaysString) {
                    $carriedOverDaysString = $carriedOverDaysString . " and " . $carriedOverDaysNextYear . " subtracted vacation days carried over to the next year " . $nextYearRange;
                } else {
                    $carriedOverDaysString = "You have " . $carriedOverDaysNextYear . " subtracted vacation days carried over to the next year " . $nextYearRange;
                }
            }

            if ($carriedOverDaysString) {
                $carriedOverDaysString = $carriedOverDaysString . ".";
            }

            //totalAllocatedDays - vacationDays + carryOverDays
            $remainingDaysRes = $this->totalVacationRemainingDays($user);
            //$remainingDaysString = "You have ".$remainingDaysRes['numberOfDays']." remaining vacation days during the current academic year";
            ////Based on the assumed [24] accrued days per year and on approved carry over requests documented in this system,
            // You have [17] remaining vacation days during the current academic year.
//            $remainingDaysString = "Based on the assumed " . $totalAccruedDays . " accrued days per year and on approved carry over " .
//                "requests documented in this system," .
//                " you have " . $remainingDaysRes['numberOfDays'] . " remaining vacation days during the current academic year";

            $remainingDaysString =
                //"Based on your start/end employment dates ($userEmplDatesStr)".
                $totalAccruedDaysStr .
                ", you have " . $totalAccruedDays .
                " accrued days per year and on approved carry over " .
                "requests documented in this system," .
                " you have " . $remainingDaysRes['numberOfDays'] .
                " remaining vacation days during the current academic year";
            if (!$remainingDaysRes['accurate']) {
                $remainingDaysString .= " (" . $this->getInaccuracyMessage() . ")";
            }
            $remainingDaysString .= ".";
        } else {
            $remainingDaysString = $totalAccruedDaysStr .
                ", you have " . $totalAccruedDays .
                " accrued days per year.";
        }
        ////////////// EOF carry over allowed ///////////////////

        $messages = array();
        $messages['accruedDaysString'] = $accruedDaysString;
        //$messages['accruedDays'] = $accruedDays;
        $messages['totalAccruedDays'] = $totalAccruedDays;
        $messages['carriedOverDaysString'] = $carriedOverDaysString;
        //$messages['carriedOverDaysNextYearString'] = $carriedOverDaysNextYearString;
        $messages['remainingDaysString'] = $remainingDaysString;
        $messages['remainingDays'] = $remainingDaysRes['numberOfDays'];

        return $messages;
    }
    
    //get noteForVacationDays
//    public function getNoteForVacationDays($user) {
//        //If user member of two group Faculty and Fellows, consider Faculty as default
//        //set Faculty's orderinlist to the lower value (lower value is default)
//        $approvalGroup = $this->getSingleApprovalGroupType($user);
//        //echo "approvalGroup=$approvalGroup <br>";
//
//        if( $approvalGroup ) {
//            return $approvalGroup->getNoteForVacationDays();
//        }
//
//        //return "Vacreq header N/A";
//        return NULL; //"Test vac header";
//    }
//    public function getNoteForCarryOverDays($user) {
//        $approvalGroup = $this->getSingleApprovalGroupType($user);
//        //echo "approvalGroup=$approvalGroup <br>";
//
//        if( $approvalGroup ) {
//            return $approvalGroup->getNoteForCarryOverDays();
//        }
//
//        //return "Carryover header N/A";
//        return NULL;
//    }
    //$getterValue = for example "noteForCarryOverDays"
    public function getValueApprovalGroupTypeByUser( $getterValue, $user=NULL, $approvalGroupType=NULL ) {
        if( !$approvalGroupType ) {
            if( !$user ) {
                $user = $this->security->getUser();
            }
            $approvalGroupType = $this->getSingleApprovalGroupType($user);
            //echo "getSingleApprovalGroupType=$approvalGroupType <br>";
            //exit('111');
        }

        if( !$approvalGroupType ) {
            //echo $getterValue.": approvalGroupType=$approvalGroupType <br>";
            //echo "get default approval group type <br>";
            //exit('222');
            //return NULL;
            //if group is not assigned => get default group
            $approvalGroupType = $this->getDefaultApprovalGroupType();
        }
        //echo $getterValue.": approvalGroupType=$approvalGroupType <br>";

        if( $approvalGroupType ) {
            $getterMethod = "get".$getterValue;
            $value = $approvalGroupType->$getterMethod();
            return $value;
        }

        //return "Carryover header N/A";
        return NULL;
    }

    //If user member of two group Faculty and Fellows, consider Faculty as default
    //set Faculty's orderinlist to the lower value (lower value is default)
    public function getSingleApprovalGroupType( $user ) {
        $approvalGroup = NULL;

        //get user's associated group, get only submiiter group
//        $groupParams = array(
//            'asObject' => true,
//            'permissions' => array(
//                array('objectStr'=>'VacReqRequest','actionStr'=>'create')
//            ),
//            'asUser' => true //get only submitters
//        );
        $groupParams = array();
        $groupParams['statusArr'] = array('default','user-added');
        $groupParams['asObject'] = true;
        $groupParams['asUser'] = true;
        $groupParams['permissions'][] = array('objectStr'=>'VacReqRequest','actionStr'=>'create');
        $groupParams['exceptPermissions'][] = array('objectStr' => 'VacReqRequest', 'actionStr' => 'changestatus-carryover');
        $groupInstitutions = $this->getGroupsByPermission($user,$groupParams);
        //foreach($groupInstitutions as $groupInstitution) {
        //    echo "$groupInstitution <br>";
        //}
        //dump($groupInstitutions);
        //exit();
        //echo "inst count=".count($groupInstitutions)."<br>";

        if( count($groupInstitutions) == 1 ) {
            $groupInstitution = $groupInstitutions[0];
            $approvalGroup = $this->getVacReqApprovalGroupType($groupInstitution);
            //echo "approvalType by single inst <br>";
            return $approvalGroup;
        }

        //get all user's institutions => get all settings => get approvalType
        //if only one approvalType (i.e. Faculty) => return this approvalType
        //if multiple go next step
        if( count($groupInstitutions) > 1 ) {
            $approvalTypeArr = array();
            foreach($groupInstitutions as $groupInstitution) {
                //get VacReqSettings
                $settings = $this->getSettingsByInstitution($groupInstitution->getId());
                //echo "settings=$settings <br>";
                if( $settings ) {
                    //get getApprovalTypes
                    $approvalTypes = $settings->getApprovalTypes();
                    //we suppuse to have only one approvalType per setting, but just in case iterate over all
                    foreach( $approvalTypes as $approvalType ) {
                        //echo "approvalType=".$approvalType."<br>";
                        $approvalTypeArr[] = $approvalType;
                    }
                }
            }
            $approvalTypeArr = array_unique($approvalTypeArr);
            //echo "approvalType count=".count($approvalTypeArr)."<br>";
            if( count($approvalTypeArr) == 1 ) {
                return $approvalTypeArr[0];
            }
            if( count($approvalTypeArr) > 1 ) {
                $minApprovalType = NULL;
                $minOrderinlist = NULL;
                foreach($approvalTypeArr as $approvalType) {
                    $orderinlist = $approvalType->getOrderinlist();
                    //echo $approvalType.": ".$orderinlist."<br>";
                    if( !$minOrderinlist ) {
                        $minOrderinlist = $orderinlist;
                        $minApprovalType = $approvalType;
                    } else {
                        if( $orderinlist < $minOrderinlist ) {
                            $minOrderinlist = $orderinlist;
                            $minApprovalType = $approvalType;
                        }
                    }
                }
                //echo "approvalType by inst <br>";
                return $minApprovalType;
            }
        }

        if( !$approvalGroup ) {
            //get default approval group with the lowest orderinlist
            //echo "approvalType default <br>";
            return $this->getDefaultApprovalGroupType();
        }
        
        return NULL;
    }
    //get approval types (VacReqApprovalTypeList) with the lowest orderinlist
    public function getApprovalGroupTypes() {
        //process.py script: replaced namespace by ::class: ['AppVacReqBundle:VacReqApprovalTypeList'] by [VacReqApprovalTypeList::class]
        $approverTypes = $this->em->getRepository(VacReqApprovalTypeList::class)->findBy(
            array('type' => array('default','user-added')),
            array('orderinlist' => 'ASC') //first with lower orderinlist
        );

        return $approverTypes;
    }
    //get default approval type (VacReqApprovalTypeList) with the lowest orderinlist
    public function getDefaultApprovalGroupType() {
        $approverTypes = $this->getApprovalGroupTypes();

        
        if( count($approverTypes) > 0 ) {
            return $approverTypes[0];
        }
        
        return NULL;
    }
    public function getApprovalGroupTypeByInstitution( $instituionId ) {
        $params = array();

        //process.py script: replaced namespace by ::class: ['AppVacReqBundle:VacReqApprovalTypeList'] by [VacReqApprovalTypeList::class]
        $repository = $this->em->getRepository(VacReqApprovalTypeList::class);
        $dql =  $repository->createQueryBuilder("list");
        $dql->leftJoin("list.vacreqSettings", "vacreqSettings");
        $dql->leftJoin("vacreqSettings.institution", "institution");

        $dql->where("institution.id = :institutionId");
        $params['institutionId'] = $instituionId;

        $dql->orderBy("list.orderinlist","ASC"); //first with lower orderinlist

        $query = $dql->getQuery(); //$query = $this->em->createQuery($dql);

        $query->setParameters($params);

        $approverTypes = $query->getResult();

//        foreach($approverTypes as $approverType) {
//            echo "approverType=$approverType <br>";
//        }

        $approverType = NULL;
        if( count($approverTypes) > 0 ) {
            $approverType = $approverTypes[0];
        }

        return $approverType;
    }

    //get approval types (VacReqApprovalTypeList) with the lowest orderinlist
    public function getApprovalGroupTypeFilters() {
        $approverTypes = $this->getApprovalGroupTypes();
        //'filter[types][0]': '1'
        //UrlGeneratorInterface::ABSOLUTE_URL
        $absoluteFlag = UrlGeneratorInterface::ABSOLUTE_PATH;
        $hrefFilters = array();
        $allHrefFilters = array();
        $count = 0;
        foreach($approverTypes as $approverType) {
            //<a href="{{ path('vacreq_summary', {'filter[types][0]': '1'}) }}">Faculty</a>
            $link = $this->container->get('router')->generate(
                'vacreq_summary',
                array(
                    'filter[types]['.$count.']' => $approverType->getId(),
                ),
                $absoluteFlag
            );
            $href = '<a href="'.$link.'">'.$approverType->getName().'</a>';
            $hrefFilters[] = $href;

            $allHrefFilters['filter[types]['.$count.']'] = $approverType->getId();

            $count++;
        }

        if( count($hrefFilters) > 1 ) {
            $linkAll = $this->container->get('router')->generate(
                'vacreq_summary',
                $allHrefFilters,
                $absoluteFlag
            );
            $hrefAll = '<a href="'.$linkAll.'">All</a>';
            array_unshift($hrefFilters , $hrefAll);
        }

        return $hrefFilters;
    }
    
    public function canCreateNewCarryOverRequest( $user=null ) {
        if( !$user ) {
            $user = $this->security->getUser();
        }
        $allowCarryOver = $this->getValueApprovalGroupTypeByUser("allowCarryOver",$user);
        if( $allowCarryOver === true ) {
            return true;
        }
        return false;
    }

    //(number of days accrued per month from site settings x 12) + days carried over from previous academic year
    // - approved vacation days for this academic year based on the requests
    // with the status of "Approved" or "Cancelation denied (Approved)"
    //If the current month is July or August, AND the logged in user has the number of remaining vacation days > 0 IN THE PREVIOUS ACADEMIC YEAR
    public function getNewCarryOverRequestString( $user, $approvalGroupType=null ) {

        //check if this group is allow carry over
        if( !$approvalGroupType ) {
            $approvalGroupType = $this->getSingleApprovalGroupType($user);
        }
        //echo "approvalGroupType=$approvalGroupType<br>";

        if( $approvalGroupType ) {
            $allowCarryOver = $this->getValueApprovalGroupTypeByUser("allowCarryOver",$user,$approvalGroupType);
            if( !$allowCarryOver ) {
                //echo "NOT allowCarryOver<br>";
                return NULL;
            }
        } else {
            return NULL;
        }

        //$res = $this->getAvailableCurrentYearCarryOverRequestString($user);
        //exit('current res='.$res);

        //If the logged in user has the number of remaining vacation days > 0 IN THE PREVIOUS ACADEMIC YEAR
        //TODO: test it in july (2 months after start academic year)
        $currentMonth = date('n');

        //$currentMonth = "8"; //testing
        //echo "currentMonth=".$currentMonth."<br>";

        //get first month of the academical year
        $dates = $this->getCurrentAcademicYearStartEndDates(true);
        $startAcademicYearDate = $dates['startDate'];
        $startMonth = $startAcademicYearDate->format('n');
        //echo "startMonth=".$startMonth."<br>";
        $nextStartMonth = $startMonth + 1;
        $nextNextStartMonth = $startMonth + 2;
        //echo "nextStartMonth=".$nextStartMonth."<br>";
        //echo "nextNextStartMonth=".$nextNextStartMonth."<br>";

        $previousYearUnusedDaysMessage = null;
        //$previousYearUnusedDaysMessage = 1;

        //if( $currentMonth == '07' || $currentMonth == '08' ) {
        if( $currentMonth == $nextStartMonth || $currentMonth == $nextNextStartMonth ) {
            $previousYearUnusedDaysMessage = $this->getPreviousYearUnusedDays($user);
            //echo "previousYearUnusedDaysMessage=".$previousYearUnusedDaysMessage."<br>";
        }

        if( $previousYearUnusedDaysMessage ) {

            return $previousYearUnusedDaysMessage;

        } else {

            $currentYearUnusedDaysMessage = $this->getCurrentYearUnusedDays($user);
            if( $currentYearUnusedDaysMessage ) {
                return $currentYearUnusedDaysMessage;
            }

        }

        return null;
    }

    public function getCurrentYearUnusedDays( $user, $asString=true ) {
        $userSecUtil = $this->container->get('user_security_utility');
        $totalAccruedDays = $this->getTotalAccruedDays($user); //current year
        $requestTypeStr = 'vacation';

        $currentYearRange = $this->getCurrentAcademicYearRange();
        $carryOverDaysPreviousYear = $this->getUserCarryOverDays($user,$currentYearRange);

        //carried over days from the current year to the next year.
        $nextYearRange = $this->getNextAcademicYearRange();
        $carryOverDaysToNextYear = $this->getUserCarryOverDays($user,$nextYearRange);

        $res = $this->getApprovedTotalDays($user,$requestTypeStr);
        $approvedVacationDays = $res['numberOfDays'];
        //$accurate = $res['accurate'];

        //echo "current $totalAccruedDays + $carryOverDaysPreviousYear - $approvedVacationDays <br>";

        //                      12*2             carryover days from PREVIOUS year   approved days for CURRENT year
        $unusedDays = (int)$totalAccruedDays + (int)$carryOverDaysPreviousYear - (int)$approvedVacationDays - (int)$carryOverDaysToNextYear;

        if( $asString && $unusedDays == 0 ) {
            //return $unusedDays;
            return "According to our vacation request system,".
            " you don't have any remaining vacation days.";
        }

        if( $asString && $unusedDays < 0 ) {
            return "According to our vacation request system,".
            " you have exceeded the number of accrued vacation days by  " .
            abs($unusedDays);
        }

        if( $asString && $unusedDays > 0 ) {
            $actionRequestUrl = $this->container->get('router')->generate(
                'vacreq_carryoverrequest'
                //array(
                //    'days' => $unusedDays,
                //)
            //UrlGeneratorInterface::ABSOLUTE_URL
            );

            //check maxCarryOverVacationDays
            $carryOverNote = null;
            //$useCarryOverNoteAndMaxdaysTogether = true;
            $useCarryOverNoteAndMaxdaysTogether = false;
            if( $useCarryOverNoteAndMaxdaysTogether ) {
                //$maxCarryOverVacationDays = $userSecUtil->getSiteSettingParameter('maxCarryOverVacationDays', 'vacreq');
                $maxCarryOverVacationDays = $this->getValueApprovalGroupTypeByUser('maxCarryOverVacationDays',$user);
                if ($maxCarryOverVacationDays && $unusedDays > $maxCarryOverVacationDays) {
                    //$noteForCarryOverDays = $userSecUtil->getSiteSettingParameter('noteForCarryOverDays', 'vacreq');
                    $noteForCarryOverDays = $this->getValueApprovalGroupTypeByUser('noteForCarryOverDays',$user);
                    if (!$noteForCarryOverDays) {
                        $noteForCarryOverDays = "As per policy, the number of days that can be carried over to the following year is limited to the maximum of "
                            . $maxCarryOverVacationDays;
                    }
                    $carryOverNote = " (" . $noteForCarryOverDays . ")";
                }
            } else {
                //$noteForCarryOverDays = $userSecUtil->getSiteSettingParameter('noteForCarryOverDays','vacreq');
                $noteForCarryOverDays = $this->getValueApprovalGroupTypeByUser('noteForCarryOverDays',$user);
                if( $noteForCarryOverDays ) {
                    $carryOverNote = " (" . $noteForCarryOverDays . ")";
                }
            }

            //show only on vacation request page, hide on carryover page
            //$link = '<a href="' . $actionRequestUrl . '">Request to carry over the remaining ' . $unusedDays . ' vacation days' . $carryOverNote . '</a>';
            $link = '<a href="' . $actionRequestUrl . 
                '">Request to carry over the remaining vacation days' . 
                $carryOverNote . '</a>';

            return $link;
        }

        return $unusedDays;
    }

    public function getPreviousYearUnusedDays( $user, $asString=true ) {
        $requestTypeStr = 'vacation';
        //$break = "\r\n";

        $yearRange = $this->getPreviousAcademicYearRange();
        $carryOverDaysPreviousYear = $this->getUserCarryOverDays($user,$yearRange);
        //echo "carryOverDaysPreviousYear=$carryOverDaysPreviousYear<br>";
        $totalAccruedDays = $this->getTotalAccruedDays($user,$yearRange); //previous year

        //TODO: test it: carried over days from the current year to THIS year (from prospective of the previous year).
        //For previous year. Use this year carry over days
        $thisYearRange = $this->getCurrentAcademicYearRange();
        $carryOverDaysToThisYear = $this->getUserCarryOverDays($user,$thisYearRange);

        $res = $this->getPreviousYearApprovedTotalDays($user,$requestTypeStr);
        $approvedVacationDays = $res['numberOfDays'];
        //echo "previous: $totalAccruedDays + $carryOverDaysPreviousYear - $approvedVacationDays <br>";
        //                      12*2             carryover days from PREVIOUS year   approved days for CURRENT year
        $unusedDays = (int)$totalAccruedDays + (int)$carryOverDaysPreviousYear - (int)$approvedVacationDays - (int)$carryOverDaysToThisYear;
        //echo "unusedDays=$unusedDays<br>";

        if( $asString && $unusedDays == 0 ) {
            //return $unusedDays;
            return "According to our vacation request system,".
            " you don't have any remaining vacation days.";
        }

        if( $asString && $unusedDays < 0 ) {
            return "According to our vacation request system," .
            " you have exceeded the number of accrued vacation days by  " .
            abs($unusedDays);
        }

        // if the logged in user has a carry over request from the previous academic year to the current academic year
        if( $asString && $unusedDays > 0 ) {

            $yearRangeArr = explode("-", $yearRange);
            $sourceYear = $yearRangeArr[0];
            $destinationYear = $yearRangeArr[1];

            $actionRequestUrl = $this->container->get('router')->generate(
                'vacreq_carryoverrequest',
                array(
                    //'days' => $unusedDays,
                    'sourceYear' => $sourceYear,
                    'destinationYear' => $destinationYear,
                )
            //UrlGeneratorInterface::ABSOLUTE_URL
            );

            $link = "You have " . $unusedDays . " unused vacation days in the previous " . $yearRange . " academic year.";
            //$link .= ' <a href="' . $actionRequestUrl . '" target="_blank">Request to carry over the remaining ' . $unusedDays . ' vacation days</a>';
            $link .= ' <a href="' . 
                $actionRequestUrl . 
                '" target="_blank">Request to carry over the remaining vacation days</a>';


            // If the carry over request has a status of "Approved" (final),
            // show the following statement in the second well INSTEAD of the one that is shown now (link the word "request" to the request page):
            //"Your request to carry over X vacation days from the 20XX-20YY academic year to the 20YY-20ZZ academic year
            // has been approved by FirstName LastName."
            $finalApprovedRequests = $this->getCarryOverRequests( $user, -1, "approved" );
            $countFinalApprovedRequests = count($finalApprovedRequests);
            foreach( $finalApprovedRequests as $thisRequest ) {
                $reqId = "";
                if( $countFinalApprovedRequests > 1 ) {
                    $reqId = " (ID #" . $thisRequest->getId() . ") ";
                }
                $link .= "<br> Your request $reqId to carry over ".$thisRequest->getCarryOverDays().
                    " vacation days from the ".$thisRequest->getSourceYearRange().
                    " academic year to the ".$thisRequest->getDestinationYearRange()." academic year".
                    " has been approved by ".$thisRequest->getApprover().".";
            }

            //If the carry over request has a status of "Tentatively Approved", show the following statement
            // in the second well INSTEAD of the one that is shown now:
            //"Your request to carry over X vacation days from the 20XX-20YY academic year to the 20YY-20ZZ academic year
            // has been tentatively approved by FirstName LastName.
            // Your request still has to be approved by FirstName LastName in order for the X vacation days to carried over to 20YY-20ZZ."
            $tentativelyApprovedRequests = $this->getCarryOverRequests( $user, -1, "pending", "approved" );
            $countTentativelyApprovedRequests = count($tentativelyApprovedRequests);
            foreach( $tentativelyApprovedRequests as $thisRequest ) {
                $reqId = "";
                if( $countTentativelyApprovedRequests > 1 ) {
                    $reqId = " (ID #" . $thisRequest->getId() . ") ";
                }
                $link .= "<br> Your request $reqId to carry over ".$thisRequest->getCarryOverDays().
                    " vacation days from the ".$thisRequest->getSourceYearRange().
                    " academic year to the ".$thisRequest->getDestinationYearRange()." academic year".
                    " has been tentatively approved by ".$thisRequest->getTentativeApprover().".";

                $approvers = $this->getRequestApprovers($thisRequest);
                $approverNamesArr = array();
                foreach( $approvers as $approver ) {
                    $approverNamesArr[] = $approver;
                }
                $finalApproversStr = implode(", ",$approverNamesArr);

                $link .= "<br>Your request still has to be approved by ".$finalApproversStr.
                    " in order for the ".$thisRequest->getCarryOverDays()." vacation days to carried over to ".
                    $thisRequest->getDestinationYearRange().".";
            }

            //If the carry over request has a status of "Submitted" (before "tentatively approved"), show the following statement in
            // the second well INSTEAD of the one that is shown now:
            //"Your request to carry over X vacation days from the 20XX-20YY academic year to the 20YY-20ZZ academic year
            // is awaiting tentative approval of FirstName LastName."
            $pendingRequests = $this->getCarryOverRequests( $user, -1, "pending", "pending" );
            $countPendingRequests = count($pendingRequests);
            foreach( $pendingRequests as $thisRequest ) {
                $reqId = "";
                if( $countPendingRequests > 1 ) {
                    $reqId = " (ID #" . $thisRequest->getId() . ") ";
                }

                $approvers = $this->getRequestApprovers($thisRequest);
                $approverNamesArr = array();
                foreach( $approvers as $approver ) {
                    $approverNamesArr[] = $approver;
                }
                $approversStr = implode(", ",$approverNamesArr);

                $link .= "<br> Your request $reqId to carry over ".$thisRequest->getCarryOverDays().
                    " vacation days from the ".$thisRequest->getSourceYearRange().
                    " academic year to the ".$thisRequest->getDestinationYearRange()." academic year".
                    " is awaiting tentative approval of ".$approversStr.".";
            }

            return $link;
        }

        return $unusedDays;
    }

    //$yearOffset: 0=>current year, -1=>previous year
    public function getCarryOverRequests( $user, $yearOffset=null, $finalStatus=null, $tentativeStatus=null ) {

        //process.py script: replaced namespace by ::class: ['AppVacReqBundle:VacReqRequest'] by [VacReqRequest::class]
        $repository = $this->em->getRepository(VacReqRequest::class);
        $dql = $repository->createQueryBuilder('request');

        $dql->leftJoin("request.user", "user");
        $dql->leftJoin("request.requestType", "requestType");

        $dql->where("user.id = :userId AND requestType.abbreviation = 'carryover'");

        if( $finalStatus ) {
            $dql->andWhere('request.status = :finalStatus');
        }
        if( $tentativeStatus ) {
            $dql->andWhere('request.tentativeStatus = :tentativeStatus');
        }

        if( $yearOffset != null ) {
            //previous year: $yearOffset = -1;
            $dates = $this->getCurrentAcademicYearStartEndDates(false,$yearOffset); //in July 12: 2016-2017
            $startAcademicYearDate = $dates['startDate'];
            //$endAcademicYearDate = $dates['startDate'];
            $startAcademicYearDateArr = explode("-",$startAcademicYearDate);
            $startAcademicYearStr = $startAcademicYearDateArr[0];

            $dql->andWhere("request.destinationYear = '".$startAcademicYearStr."'");
        }

        $query = $dql->getQuery(); //$query = $this->em->createQuery($dql);

        $query->setParameter('userId', $user->getId());

        if( $finalStatus ) {
            $query->setParameter('finalStatus', $finalStatus);
        }
        if( $tentativeStatus ) {
            $query->setParameter('tentativeStatus', $tentativeStatus);
        }

        $requests = $query->getResult();

        return $requests;
    }

    //UNUSED function
    //Get available carry over days for the CURRENT academical year.
    //Case 1, today is May 10: current academical year is 2015-2016 or "July 1st 2015 - 2016 June 30"
    //Case 2, today is July 12: current academical year is 2016-2017 or "July 1st 2016 - 2017 June 30"
    //(number of days accrued per month from site settings x 12) + days carried over from previous academic year
    // - approved vacation days for this academic year based on the requests
    // with the status of "Approved" or "Cancelation denied (Approved)"
    public function getAvailableCurrentYearCarryOverRequestString( $user ) {

        $dates = $this->getCurrentAcademicYearStartEndDates();
        //echo "dates=".$dates['startDate']." == ".$dates['endDate']."<br>";
        $currentYearStart = $dates['startDate']; //Y-m-d
        //echo "currentYearStart=".$currentYearStart."<br>";
        $endDate = $dates['endDate']; //Y-m-d
        //echo "endDate=".$endDate."<br>";

        $currentYearStartDateArr = explode("-",$currentYearStart);
        $year = $currentYearStartDateArr[0];
        $year = ((int)$year) - 1;   //previous year
        //echo "year=".$year."<br>";

        $totalAccruedDays = $this->getTotalAccruedDays($user); //current year
        $carryOverDaysPreviousYear = $this->getUserCarryOverDays($user,$year); //2015

        //carried over days from the current year to the next year.
        $nextYearRange = $this->getNextAcademicYearRange();
        $carryOverDaysToNextYear = $this->getUserCarryOverDays($user,$nextYearRange);

        $requestTypeStr = 'vacation';
        $res = $this->getApprovedTotalDays($user,$requestTypeStr);
        $approvedVacationDays = $res['numberOfDays'];
        //$accurate = $res['accurate'];

        //echo "$totalAccruedDays + $carryOverDaysPreviousYear - $approvedVacationDays <br>";

        //                      12*2             carryover days from PREVIOUS year   approved days for CURRENT year
        $daysToRequest = (int)$totalAccruedDays + (int)$carryOverDaysPreviousYear - (int)$approvedVacationDays - (int)$carryOverDaysToNextYear;

//        if( $daysToRequest < 0 ) {
//            return "According to our vacation request system, you have $unusedDays exceeded vacation days.";
//        }

        if( $daysToRequest && $daysToRequest > 0 ) {
            $actionRequestUrl = $this->container->get('router')->generate(
                'vacreq_carryoverrequest'
                //array(
                //    'days' => $daysToRequest,
                //)
            //UrlGeneratorInterface::ABSOLUTE_URL
            );

            //$link = '<a href="'.$actionRequestUrl.'">Request to carry over the remaining '.$daysToRequest.' vacation days</a>';
            $link = '<a href="'.$actionRequestUrl.
                '">Request to carry over the remaining vacation days</a>';
            return $link;
        }

        return null;
    }

    //Get pending (non-approved, non-rejected) requests for the logged in approver
    public function getTotalPendingRequests( $approver, $groupId=null ) {
        $requestsB = $this->getTotalStatusTypeRequests($approver,"business",$groupId);
        $requestsV = $this->getTotalStatusTypeRequests($approver,"vacation",$groupId);

        return count($requestsB) + count($requestsV);
    }
    public function getTotalStatusTypeRequests( $approver, $requestTypeStr, $groupId=null, $asObject=true, $status = "pending" ) {

        //process.py script: replaced namespace by ::class: ['AppVacReqBundle:VacReqRequest'] by [VacReqRequest::class]
        $repository = $this->em->getRepository(VacReqRequest::class);
        $dql =  $repository->createQueryBuilder("request");

        if( $asObject ) {
            $dql->select('request');
        } else {
            $dql->select('SUM(requestType.numberOfDays) as numberOfDays');
        }

        $dql->leftJoin("request.user", "user");

        if( $requestTypeStr == 'business' || $requestTypeStr == 'requestBusiness' ) {
            $dql->leftJoin("request.requestBusiness", "requestType");
        }

        if( $requestTypeStr == 'vacation' || $requestTypeStr == 'requestVacation' ) {
            $dql->leftJoin("request.requestVacation", "requestType");
        }

        $dql->where("requestType.status = :status");

        $queryParameters = array(
            'status' => $status
        );

        if( $groupId ) {
            $dql->andWhere("request.institution = :groupId");
            $queryParameters['groupId'] = $groupId;
        } else {
            //get approver groups
            $groupParams = array('asObject' => true);
            $groupParams['permissions'][] = array('objectStr' => 'VacReqRequest', 'actionStr' => 'changestatus');
            $groupRoles = $this->getGroupsByPermission($approver, $groupParams);
            $groupIds = array();
            foreach ($groupRoles as $role) {
                $groupIds[] = $role->getId();
            }
            if ($groupIds and count($groupIds) > 0) {
                $dql->andWhere("request.institution IN (" . implode(",", $groupIds) . ")");
            }
        }

        $query = $dql->getQuery(); //$query = $this->em->createQuery($dql);

        //echo "query=".$query->getSql()."<br>";
        //echo "dql=".$dql."<br>";

        $query->setParameters($queryParameters);

        if( $asObject ) {
            $requests = $query->getResult();
            return $requests;
        } else {
            $numberOfDaysRes = $query->getSingleResult();
            $numberOfDays = $numberOfDaysRes['numberOfDays'];
            //echo "numberOfDays=".$numberOfDays."<br>";
            return $numberOfDays;
        }

        return null;
    }

    //Get pending (non-approved, non-rejected) floating requests for the logged in approver
    public function getTotalFloatingPendingRequests( $approver, $groupId=null, $status = "pending" ) {

        //process.py script: replaced namespace by ::class: ['AppVacReqBundle:VacReqRequestFloating'] by [VacReqRequestFloating::class]
        $repository = $this->em->getRepository(VacReqRequestFloating::class);
        $dql =  $repository->createQueryBuilder("request");

        $dql->select('request');

        $dql->leftJoin("request.user", "user");

        $dql->where("request.status = :status");

        $queryParameters = array(
            'status' => $status
        );

        if( $groupId ) {
            $dql->andWhere("request.institution = :groupId");
            $queryParameters['groupId'] = $groupId;
        } else {
            //get approver groups
            $groupParams = array('asObject' => true);
            $groupParams['permissions'][] = array('objectStr' => 'VacReqRequest', 'actionStr' => 'changestatus');
            $groupRoles = $this->getGroupsByPermission($approver, $groupParams);
            $groupIds = array();
            foreach ($groupRoles as $role) {
                $groupIds[] = $role->getId();
            }
            if ($groupIds and count($groupIds) > 0) {
                $dql->andWhere("request.institution IN (" . implode(",", $groupIds) . ")");
            }
        }

        $query = $dql->getQuery(); //$query = $this->em->createQuery($dql);

        //echo "query=".$query->getSql()."<br>";
        //echo "dql=".$dql."<br>";

        $query->setParameters($queryParameters);

        $requests = $query->getResult();

        return count($requests);
    }

    public function getFloatingRequestTypeId() {
        //process.py script: replaced namespace by ::class: ['AppVacReqBundle:VacReqRequestTypeList'] by [VacReqRequestTypeList::class]
        $requestType = $this->em->getRepository(VacReqRequestTypeList::class)->findOneByAbbreviation("floatingday");
        if( $requestType ) {
            return $requestType->getId();
        }
        return NULL;
    }

    public function convertDateTimeToStr($datetime) {
        $transformer = new DateTimeToStringTransformer(null,null,'m/d/Y');
        $dateStr = $transformer->transform($datetime);
        return $dateStr;
    }

//    //Used by vacreq_status_change_carryover, vacreq_status_email_change_carryover
//    //$status - pre-approval or final status (the one has been changed) ($status - new status)
//    //Pass 'first-step' or 'second-step' approval stage flag (default='first-step')
//    public function processChangeStatus_CarryOverRequest_ORIG( $entity, $status, $user, $request, $withRedirect=true, $update=true ) {
//
//        //echo "<br><br>Testing: process ChangeStatusCarryOverRequest: request ID=".$entity->getId()."<br>";
//        //echo "Tentative inst=".$entity->getTentativeInstitution()."<br>";
//
//        $logger = $this->container->get('logger');
//        $session = $this->container->get('session');
//
//        /////////////// check permission: if user is in approvers => ok ///////////////
//        if( false == $this->security->isGranted('ROLE_VACREQ_ADMIN') ) {
//            $permitted = false;
//            //echo "########## processChangeStatus_CarryOverRequest <br>";
//            $approvers = $this->getRequestApprovers($entity);
//            //echo "inst approvers=".count($approvers)."<br>"; //testing
////            if( count($approvers) == 0 ) {
////                //getRequestApprovers( $entity, $institutionType="institution", $forceApproverRole=null, $onlyWorking=false )
////                $approvers = $this->getRequestApprovers($entity,"tentativeInstitution","ROLE_VACREQ_APPROVER");
////            }
//            $approversName = array();
//            //echo "tent approvers=".count($approvers)."<br>"; //testing
//            foreach ($approvers as $approver) {
//                if( $user->getId() == $approver->getId() ) {
//                    //ok
//                    $permitted = true;
//                }
//                $approversName[] = $approver . "";
//            }
//            if ($permitted == false) {
//                //Flash
//                $session->getFlashBag()->add(
//                    'notice',
//                    "You can not review this request. This request can be approved or rejected by " . implode("; ", $approversName)
//                );
//                //exit("testing: no permission to approve this request."); //testing
//                //return $this->redirect($this->generateUrl('vacreq-nopermission'));
//                return 'vacreq-nopermission';
//            }
//        }
//        /////////////// EOF check permission: if user is in approvers => ok ///////////////
//
//
//        $em = $this->em;
//        $emailUtil = $this->container->get('user_mailer_utility');
//        $userSecUtil = $this->container->get('user_security_utility');
//        //$break = "\r\n";
//        $break = "<br>";
//        $action = null;
//
////        //echo "status=$status <br>";
////        $institution = $entity->getInstitution();
////        $tentativeInstitution = $entity->getTentativeInstitution();
////        //if( $institution && $tentativeInstitution && $institution == $tentativeInstitution ) {
////        if( $tentativeInstitution && !$institution ) {
////            $entity->setTentativeStatus($status);
////            $entity->setStatus($status);
////        }
//
//        //two cases:
//        //Case 1: tentative institution is set, org institution = null => one stage => second step only
//        //Case 2: tentative institution is set, org institution is set => two stage
//
//        /////////////////// TWO CASES: pre-approval and final approval ///////////////////
//        if( $entity->getTentativeInstitution() && $entity->getTentativeStatus() == 'pending' ) {
//            ////////////// FIRST STEP: group pre-approver ///////////////////
//            //echo $entity->getId().": FIRST STEP: group pre-approver <br>"; exit('111'); //testing
//
//            //setTentativeInstitution to approved or rejected
//
//            $action = "Tentatively ".$status;
//            $logger->notice("process ChangeStatusCarryOverRequest: action=".$action);
//
//            $entity->setTentativeStatus($status);
//
//            if( $status == "pending" ) { //set tentative status back to pending
//                $entity->setTentativeApprover(null);
//                $entity->setApprover(null);
//                $entity->setStatus('pending');
//            } else {
//                $entity->setTentativeApprover($user);
//                $entity->setTentativeApprovedRejectDate(new \DateTime());
//            }
//
//            //send email to supervisor for a final approval
//            if( $status == 'approved' ) {
//
//                //Event Log
//                $requestName = $entity->getRequestName();
//                $eventType = 'Carry Over Request Updated';
//                $institution = $entity->getInstitution();
//                $tentativeInstitution = $entity->getTentativeInstitution();
//
//                if( $institution && $institution != $tentativeInstitution )
//                {
//                    //if carry over request has main organizational group => regular flow: set status to pending and send email to the final approvers
//                    $entity->setApprover(null);
//                    $entity->setStatus('pending');
//
//                    $approversNameStr = $this->sendConfirmationEmailToApprovers($entity); //send email to second step approval
//
//                    $event = $requestName . " ID #".$entity->getId()." for ".$entity->getUser()." has been tentatively approved by ".$entity->getTentativeApprover().". ".
//                        "Email for a final approval has been sent to ".$approversNameStr;
//                } elseif( !$institution || ($institution && $tentativeInstitution && $institution == $tentativeInstitution) )
//                {
//                    //if carry over request has main organizational group the same as tentative => set status as approved
//                    //if carry over request does not have main organizational group => set status as approved
//                    $entity->setApprover($user);
//                    $entity->setStatus($status);
//                    $event = $requestName . " for ".$entity->getUser()." has been approved by ".$entity->getApprover().
//                        ". Confirmation email has been sent to the submitter ".$entity->getUser()->getSingleEmail();
//
//                    $subjectApproved = "Your request ID #".$entity->getId()." to carry over ".$entity->getCarryOverDays()." vacation days from ".
//                        $entity->getSourceYearRange() . " to " . $entity->getDestinationYearRange()." has been approved.";
//
//                    $bodyApproved = $entity->getTentativeApprover(). " has approved your request ID #".$entity->getId()." to carry over ".
//                        $entity->getCarryOverDays()." vacation days from ".
//                        $entity->getSourceYearRange() . " to " . $entity->getDestinationYearRange();
//
//                    //request info
//                    $bodyApproved .= $break.$break.$entity->printRequest($this->container);
//
//                    $emailUtil->sendEmail( $entity->getUser()->getSingleEmail(), $subjectApproved, $bodyApproved, null, null );
//                }
//
//                $userSecUtil->createUserEditEvent($this->container->getParameter('vacreq.sitename'),$event,$user,$entity,$request,$eventType);
//
//                //Flash
//                $session->getFlashBag()->add(
//                    'notice',
//                    $event
//                );
//            }
//
//            //send email to submitter
//            if( $status == 'rejected' ) {
//
//                //since it's rejected then set status to rejected
//                $entity->setApprover($user);
//                $entity->setStatus($status);
//
//                //Subject: Your request to carry over X vacation days from 20XX-20YY to 20YY-20ZZ was rejected.
//                $subjectRejected = "Your request ID #".$entity->getId()." to carry over ".$entity->getCarryOverDays()." vacation days from ".
//                    $entity->getSourceYearRange() . " to " . $entity->getDestinationYearRange()." was rejected.";
//
//                //Message: FirstNameOfTentativeApprover LastNameOfTentativeApprover has rejected your request to
//                // carry over X vacation days from 20XX-20YY to 20YY-20ZZ.
//                $bodyRejected = $entity->getTentativeApprover(). " has rejected your request ID #".$entity->getId()." to carry over ".
//                    $entity->getCarryOverDays()." vacation days from ".
//                    $entity->getSourceYearRange() . " to " . $entity->getDestinationYearRange();
//
//                //request info
//                $bodyRejected .= $break.$break.$entity->printRequest($this->container);
//
//                $emailUtil->sendEmail( $entity->getUser()->getSingleEmail(), $subjectRejected, $bodyRejected, null, null );
//
//                //Event Log
//                $requestName = $entity->getRequestName();
//                $eventType = 'Carry Over Request Updated';
//                $event = $requestName . " ID #".$entity->getId()." for ".$entity->getUser()." has been tentatively rejected by ".
//                    $entity->getTentativeApprover().". ".
//                    "Confirmation email has been sent to the submitter ".$entity->getUser().".";
//                $userSecUtil->createUserEditEvent($this->container->getParameter('vacreq.sitename'),$event,$user,$entity,$request,$eventType);
//
//                //Flash
//                $session->getFlashBag()->add(
//                    'notice',
//                    $event
//                );
//            }
//
//
//        } else {
//            ////////////// SECOND STEP: supervisor //////////////
//            //echo $entity->getId().": SECOND STEP: supervisor. status=$status <br>";exit('111'); //testing
//
//            $action = "Final ".$status;
//            $logger->notice("process ChangeStatusCarryOverRequest: action=".$action);
//
//            $entity->setStatus($status);
//
//            if( $status == "pending" ) {
//                $entity->setApprover(null);
//            } else {
//                $entity->setApprover($user);
//            }
//
//            if( $status == "approved" ) {
//
//                //process carry over request days if request is approved
//                $res = $this->processVacReqCarryOverRequest($entity);
//                if( $res && $res['exists'] == true && $withRedirect ) {
//                    //warning for overwrite:
//                    //"FirstName LastName already has X days carried over from 20YY-20ZZ academic year to the 20ZZ-20MM academic year on file.
//                    // This carry over request asks for N days to be carried over from 20YY-20ZZ academic year to the 20ZZ-20MM academic year.
//                    // Please enter the total amount of days that should be carried over 20YY-20ZZ academic year to the 20ZZ-20MM academic year: [ ]"
//                    //exit('exists days='.$res['days']);
//                    //return $this->redirectToRoute('vacreq_review',array('id'=>$entity->getId()));
//                    return "vacreq_review";
//                }
//                if( $res && ($res['exists'] == false || $update == true) ) {
//                    //save
//                    $userCarryOver = $res['userCarryOver'];
//                    //$logger->notice("process ChangeStatusCarryOverRequest: update userCarryOver=".$userCarryOver);
//                    $em->persist($userCarryOver);
//                    //$em->flush($userCarryOver);
//                    $em->flush();
//                }
//
//                //send a confirmation email to submitter
//                //Subject: Your request to carry over X vacation days from 20XX-20YY to 20YY-20ZZ was approved.
//                $subjectApproved = "Your request ID #".$entity->getId()." to carry over ".$entity->getCarryOverDays()." vacation days from ".
//                    $entity->getSourceYearRange() . " to " . $entity->getDestinationYearRange() . " was approved.";
//
//                //Message: FirstNameOfTentativeApprover LastNameOfTentativeApprover has rejected your request to
//                // carry over X vacation days from 20XX-20YY to 20YY-20ZZ.
//                $bodyApproved = $entity->getApprover(). " has approved your request in the final phase to carry over ".
//                    $entity->getCarryOverDays()." vacation days from ".
//                    $entity->getSourceYearRange() . " to " . $entity->getDestinationYearRange();
//
//                //request info
//                $bodyApproved .= $break.$break.$entity->printRequest($this->container);
//
//                $emailUtil->sendEmail( $entity->getUser()->getSingleEmail(), $subjectApproved, $bodyApproved, null, null );
//
//                //Event Log
//                $requestName = $entity->getRequestName();
//                $eventType = 'Carry Over Request Updated';
//                $event = $requestName . " for ".$entity->getUser()." has been approved in the final phase by ".$entity->getApprover().
//                    ". Confirmation email has been sent to the submitter ".$entity->getUser()->getSingleEmail();
//                $userSecUtil->createUserEditEvent($this->container->getParameter('vacreq.sitename'),$event,$user,$entity,$request,$eventType);
//
//                //Flash
//                $session->getFlashBag()->add(
//                    'notice',
//                    $event
//                );
//            }//approved
//
//
//            //send email to submitter
//            if( $status == 'rejected' ) {
//                //Subject: Your request to carry over X vacation days from 20XX-20YY to 20YY-20ZZ was rejected.
//                $subjectRejected = "Your request ID #".$entity->getId()." to carry over ".$entity->getCarryOverDays()." vacation days from ".
//                    $entity->getSourceYearRange() . " to " . $entity->getDestinationYearRange() . " was rejected.";
//
//                //Message: FirstNameOfTentativeApprover LastNameOfTentativeApprover has rejected your request to
//                // carry over X vacation days from 20XX-20YY to 20YY-20ZZ.
//                $bodyRejected = $entity->getApprover(). " has rejected your request in the final phase to carry over ".
//                    $entity->getCarryOverDays()." vacation days from ".
//                    $entity->getSourceYearRange() . " to " . $entity->getDestinationYearRange();
//
//                //request info
//                $bodyRejected .= $break.$break.$entity->printRequest($this->container);
//
//                $emailUtil->sendEmail( $entity->getUser()->getSingleEmail(), $subjectRejected, $bodyRejected, null, null );
//
//                //Event Log
//                $requestName = $entity->getRequestName();
//                $eventType = 'Carry Over Request Updated';
//                $event = $requestName . " for ".$entity->getUser()." has been rejected in the final phase by ".$entity->getApprover().
//                    ". Confirmation email has been sent to the submitter ".$entity->getUser()->getSingleEmail();
//                $userSecUtil->createUserEditEvent($this->container->getParameter('vacreq.sitename'),$event,$user,$entity,$request,$eventType);
//
//                //Flash
//                $session->getFlashBag()->add(
//                    'notice',
//                    $event
//                );
//            }//rejected
//        }
//        /////////////////// EOF TWO CASES: pre-approval and final approval ///////////////////
//
//        return $action;
//    }
    //Used by vacreq_status_change_carryover, vacreq_status_email_change_carryover
    //$status - pre-approval or final status (the one has been changed) ($status - new status)
    //Pass 'first-step' or 'second-step' approval stage flag (default='first-step')
    public function processChangeStatusCarryOverRequest(
        $entity,
        $status,
        $user,
        $request,
        $withRedirect=true,
        $update=true,
        $step='first-step',
        $testing=false
    ) {

        //echo "<br><br>Testing: process ChangeStatusCarryOverRequest: request ID=".$entity->getId()."<br>";
        //echo "Tentative inst=".$entity->getTentativeInstitution()."<br>";

        $logger = $this->container->get('logger');

        //$session = $this->container->get('session');
        //$userUtil = $this->container->get('user_utility');
        $session = $request->getSession(); //$userUtil->getSession();

        /////////////// check permission: if user is in approvers => ok ///////////////
        if( false == $this->security->isGranted('ROLE_VACREQ_ADMIN') ) {
            $permitted = false;
            //echo "########## processChangeStatusCarryOverRequest <br>";
            $approvers = $this->getRequestApprovers($entity);
            //echo "inst approvers=".count($approvers)."<br>"; //testing
//            if( count($approvers) == 0 ) {
//                //getRequestApprovers( $entity, $institutionType="institution", $forceApproverRole=null, $onlyWorking=false )
//                $approvers = $this->getRequestApprovers($entity,"tentativeInstitution","ROLE_VACREQ_APPROVER");
//            }
            $approversName = array();
            //echo "tent approvers=".count($approvers)."<br>"; //testing
            foreach ($approvers as $approver) {
                if( $user->getId() == $approver->getId() ) {
                    //ok
                    $permitted = true;
                }
                $approversName[] = $approver . "";
            }
            if ($permitted == false) {
                //Flash
                $session->getFlashBag()->add(
                    'notice',
                    "You can not review this request. This request can be approved or rejected by " . implode("; ", $approversName)
                );
                //exit("testing: no permission to approve this request."); //testing
                //return $this->redirect($this->generateUrl('vacreq-nopermission'));
                return 'vacreq-nopermission';
            }
        }
        /////////////// EOF check permission: if user is in approvers => ok ///////////////


        $em = $this->em;
        $emailUtil = $this->container->get('user_mailer_utility');
        $userSecUtil = $this->container->get('user_security_utility');
        //$break = "\r\n";
        $break = "<br>";
        $action = null;

        //echo "status=$status <br>";
        $institution = $entity->getInstitution();
        $tentativeInstitution = $entity->getTentativeInstitution();
        //sync tentative status and org status
        if( $tentativeInstitution && !$institution ) {
            $entity->setTentativeStatus($status);
            $entity->setStatus($status);
        }

        //two cases:
        //Case 1: tentative institution is set, org institution = null => one stage => second step only
        //Case 2: tentative institution is set, org institution is set => two stage

        /////////////////// TWO CASES: pre-approval and final approval ///////////////////
        if( $step == 'first-step' && $entity->getTentativeStatus() == 'pending' ) {
            ////////////// FIRST STEP: group pre-approver ///////////////////
            //echo $entity->getId().": FIRST STEP: group pre-approver <br>"; exit('111'); //testing

            //setTentativeInstitution to approved or rejected

            $action = "Tentatively ".$status;
            $logger->notice("process ChangeStatusCarryOverRequest: action=".$action);

            $entity->setTentativeStatus($status);

            if( $status == "pending" ) { //set tentative status back to pending
                $entity->setTentativeApprover(null);
                $entity->setApprover(null);
                $entity->setStatus('pending');
            } else {
                $entity->setTentativeApprover($user);
                $entity->setTentativeApprovedRejectDate(new \DateTime());
            }

            //send email to supervisor for a final approval
            if( $status == 'approved' ) {

                $entity->setApprover(null);
                $entity->setStatus('pending');

                $approversNameStr = $this->sendConfirmationEmailToApprovers($entity);

                //Event Log
                $requestName = $entity->getRequestName();
                $eventType = 'Carry Over Request Updated';
                $event = $requestName . " ID #".$entity->getId()." for ".$entity->getUser()." has been tentatively approved by ".$entity->getTentativeApprover().". ".
                    "Email for a final approval has been sent to ".$approversNameStr;
                $userSecUtil->createUserEditEvent($this->container->getParameter('vacreq.sitename'),$event,$user,$entity,$request,$eventType);

                //Flash
                if( $session ) {
                    $session->getFlashBag()->add(
                        'notice',
                        $event
                    );
                }
            }

            //send email to submitter
            if( $status == 'rejected' ) {

                //since it's rejected then set status to rejected
                $entity->setApprover($user);
                $entity->setStatus($status);

                //Subject: Your request to carry over X vacation days from 20XX-20YY to 20YY-20ZZ was rejected.
                $subjectRejected = "Your request ID #".$entity->getId()." to carry over ".$entity->getCarryOverDays()." vacation days from ".
                    $entity->getSourceYearRange() . " to " . $entity->getDestinationYearRange()." was rejected.";

                //Message: FirstNameOfTentativeApprover LastNameOfTentativeApprover has rejected your request to
                // carry over X vacation days from 20XX-20YY to 20YY-20ZZ.
                $bodyRejected = $entity->getTentativeApprover(). " has rejected your request ID #".$entity->getId()." to carry over ".
                    $entity->getCarryOverDays()." vacation days from ".
                    $entity->getSourceYearRange() . " to " . $entity->getDestinationYearRange();

                //request info
                $bodyRejected .= $break.$break.$entity->printRequest($this->container);

                $emailUtil->sendEmail( $entity->getUser()->getSingleEmail(), $subjectRejected, $bodyRejected, null, null );

                //Event Log
                $requestName = $entity->getRequestName();
                $eventType = 'Carry Over Request Updated';
                $event = $requestName . " ID #".$entity->getId()." for ".$entity->getUser()." has been rejected (tentatively, in the first step) by ".
                    $entity->getTentativeApprover().". ".
                    "Confirmation email has been sent to the submitter ".$entity->getUser().".";
                $userSecUtil->createUserEditEvent($this->container->getParameter('vacreq.sitename'),$event,$user,$entity,$request,$eventType);

                //Flash
                if( $session ) {
                    $session->getFlashBag()->add(
                        'notice',
                        $event
                    );
                }
            }


        } else {
            ////////////// SECOND STEP: supervisor //////////////
            //echo $entity->getId().": SECOND STEP: supervisor. status=$status <br>";exit('111'); //testing

            //This is a second step, therefore set $step = 'second-step';
            //$step = 'second-step';

            //A request is either approved or rejected (in that second step)
            //$action = "Final ".$status;
            $action = $status;

            $logger->notice("process ChangeStatusCarryOverRequest: action=".$action);

            $entity->setStatus($status);

            if( $step == 'second-step' ) {
                $entity->setTentativeStatus($status);
            }

            if( $status == "pending" ) {
                $entity->setApprover(null);
                if( $step == 'second-step' ) {
                    $entity->setTentativeApprover(null);
                }
            } else {
                $entity->setApprover($user);
                if( $step == 'second-step' ) {
                    $entity->setTentativeApprover($user);
                }
            }

            if( $status == "approved" ) {

                //process carry over request days if request is approved
                $res = $this->processVacReqCarryOverRequest($entity);
                if( $res && $res['exists'] == true && $withRedirect ) {
                    //warning for overwrite:
                    //"FirstName LastName already has X days carried over from 20YY-20ZZ academic year to the 20ZZ-20MM academic year on file.
                    // This carry over request asks for N days to be carried over from 20YY-20ZZ academic year to the 20ZZ-20MM academic year.
                    // Please enter the total amount of days that should be carried over 20YY-20ZZ academic year to the 20ZZ-20MM academic year: [ ]"
                    //exit('exists days='.$res['days']);
                    //return $this->redirectToRoute('vacreq_review',array('id'=>$entity->getId()));
                    return "vacreq_review";
                }
                if( $res && ($res['exists'] == false || $update == true) ) {
                    //save
                    $userCarryOver = $res['userCarryOver'];
                    //$logger->notice("process ChangeStatusCarryOverRequest: update userCarryOver=".$userCarryOver);
                    $em->persist($userCarryOver);
                    //$em->flush($userCarryOver);
                    $em->flush();
                }

                //send a confirmation email to submitter
                //Subject: Your request to carry over X vacation days from 20XX-20YY to 20YY-20ZZ was approved.
                $subjectApproved = "Your request ID #".$entity->getId()." to carry over ".$entity->getCarryOverDays()." vacation days from ".
                    $entity->getSourceYearRange() . " to " . $entity->getDestinationYearRange() . " was approved.";

                //Message: FirstNameOfTentativeApprover LastNameOfTentativeApprover has rejected your request to
                // carry over X vacation days from 20XX-20YY to 20YY-20ZZ.
                $bodyApproved = $entity->getApprover(). " has approved your request to carry over ".
                    $entity->getCarryOverDays()." vacation days from ".
                    $entity->getSourceYearRange() . " to " . $entity->getDestinationYearRange();

                //request info
                $bodyApproved .= $break.$break.$entity->printRequest($this->container);

                $emailUtil->sendEmail( $entity->getUser()->getSingleEmail(), $subjectApproved, $bodyApproved, null, null );

                //Event Log
                $requestName = $entity->getRequestName();
                $eventType = 'Carry Over Request Updated';
                $event = $requestName . " for ".$entity->getUser()." has been approved (in the final phase) by ".$entity->getApprover().
                    ". Confirmation email has been sent to the submitter ".$entity->getUser()->getSingleEmail();
                $userSecUtil->createUserEditEvent($this->container->getParameter('vacreq.sitename'),$event,$user,$entity,$request,$eventType);

                //Flash
                if( $session ) {
                    $session->getFlashBag()->add(
                        'notice',
                        $event
                    );
                }
            }//approved


            //send email to submitter
            if( $status == 'rejected' ) {
                //Subject: Your request to carry over X vacation days from 20XX-20YY to 20YY-20ZZ was rejected.
                $subjectRejected = "Your request ID #".$entity->getId()." to carry over ".$entity->getCarryOverDays()." vacation days from ".
                    $entity->getSourceYearRange() . " to " . $entity->getDestinationYearRange() . " was rejected.";

                //Message: FirstNameOfTentativeApprover LastNameOfTentativeApprover has rejected your request to
                // carry over X vacation days from 20XX-20YY to 20YY-20ZZ.
                $bodyRejected = $entity->getApprover(). " has rejected your request to carry over ".
                    $entity->getCarryOverDays()." vacation days from ".
                    $entity->getSourceYearRange() . " to " . $entity->getDestinationYearRange();

                //request info
                $bodyRejected .= $break.$break.$entity->printRequest($this->container);

                $emailUtil->sendEmail( $entity->getUser()->getSingleEmail(), $subjectRejected, $bodyRejected, null, null );

                //Event Log
                $requestName = $entity->getRequestName();
                $eventType = 'Carry Over Request Updated';
                $event = $requestName . " for ".$entity->getUser()." has been rejected (in the final phase) by ".$entity->getApprover().
                    ". Confirmation email has been sent to the submitter ".$entity->getUser()->getSingleEmail();
                $userSecUtil->createUserEditEvent($this->container->getParameter('vacreq.sitename'),$event,$user,$entity,$request,$eventType);

                //Flash
                if( $session ) {
                    $session->getFlashBag()->add(
                        'notice',
                        $event
                    );
                }
            }//rejected
        }
        /////////////////// EOF TWO CASES: pre-approval and final approval ///////////////////

        return $action;
    }


    public function addRequestInstitutionToOrgGroup( $entity, $organizationalInstitutions, $institutionType="institution" ) {
        //echo "entity group=".$entity->getInstitution()."<br>";

        if( $institutionType == "institution" ) {
            $institution = $entity->getInstitution();
            //echo "add institution=$institution<br>";
        }
        if( $institutionType == "tentativeInstitution" ) {
            $institution = $entity->getTentativeInstitution();
            //echo "add tentativeInstitution=$institution <br>";
        }

        //if( $organizationalInstitutions && $institution ) { //$organizationalInstitutions &&
//        if( $institution ) { //$organizationalInstitutions &&
//            //echo "add to organizationalInstitutions; count=".count($organizationalInstitutions)."<br>";
//            if( $organizationalInstitutions === null ||
//                (   $organizationalInstitutions &&
//                    is_array($organizationalInstitutions) &&
//                    !array_key_exists($institution->getId(), $organizationalInstitutions)
//                )
//            ) {
//                $thisApprovers = $this->getRequestApprovers( $entity, $institutionType );
//                $approversArr = array();
//                if( $thisApprovers && is_array($thisApprovers) ) {
//                    foreach ($thisApprovers as $thisApprover) {
//                        $approversArr[] = $thisApprover->getUsernameShortest();
//                    }
//                }
//                if( count($approversArr) > 0 ) {
//                    $orgName = $institution . " (for review by " . implode(", ",$approversArr) . ")";
//                } else {
//                    $orgName = $institution."";
//                }
//                $organizationalInstitutions[$institution->getId()] = $orgName;
//            }
//        }
        if( $institution ) { //$organizationalInstitutions &&
            //echo "add to organizationalInstitutions; count=".count($organizationalInstitutions)."<br>";
            if( $organizationalInstitutions === null ||
                (   $organizationalInstitutions &&
                    is_array($organizationalInstitutions) &&
                    !array_key_exists($institution->getId(), $organizationalInstitutions)
                )
            ) {
                $thisApprovers = $this->getRequestApprovers( $entity, $institutionType );
                $approversArr = array();
                if( $thisApprovers && is_array($thisApprovers) ) {
                    foreach ($thisApprovers as $thisApprover) {
                        $approversArr[] = $thisApprover->getUsernameShortest();
                    }
                }
                if( count($approversArr) > 0 ) {
                    $orgName = $institution . " (for review by " . implode(", ",$approversArr) . ")";
                } else {
                    $orgName = $institution."";
                }
                $organizationalInstitutions[$institution->getId()] = $orgName;
            }
        }

        return $organizationalInstitutions;
    }

    public function getVacReqIdsArrByDqlParameters($dql,$dqlParameters) {
        $dql->select('request.id');

        $dql->groupBy('request.id');

        $query = $dql->getQuery();

        if( count($dqlParameters) > 0 ) {
            $query->setParameters($dqlParameters);
        }

        $result = $query->getScalarResult();
        $ids = array_map('current', $result);
        //$ids = array_unique($ids);

        return $ids;
    }

    public function createtListExcel( $ids ) {

        $author = $this->security->getUser();
        //$transformer = new DateTimeToStringTransformer(null,null,'d/m/Y');

        $ea = new Spreadsheet(); // ea is short for Excel Application

        $ea->getProperties()
            ->setCreator($author."")
            ->setTitle('Business/Vacation Requests')
            ->setLastModifiedBy($author."")
            ->setDescription('Business/Vacation Requests list in spreadsheet format')
            ->setSubject('PHP spreadsheet manipulation')
            ->setKeywords('spreadsheet php office')
            ->setCategory('programming')
        ;

        $ews = $ea->getSheet(0);
        $ews->setTitle('Business and Vacation Requests');

        //align all cells to left
        $style = array(
            'alignment' => array(
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT,
            )
        );
        $ews->getParent()->getDefaultStyle()->applyFromArray($style);
        //$ews->getPageSetup()->setHorizontalCentered(true);

        $ews->setCellValue('A1', 'ID');
        $ews->setCellValue('B1', 'Person');
        $ews->setCellValue('C1', 'Academic Year');
        $ews->setCellValue('D1', 'Group');

        $ews->setCellValue('E1', 'Business Days');
        $ews->setCellValue('F1', 'Start Date');
        $ews->setCellValue('G1', 'End Date');
        $ews->setCellValue('H1', 'Status');

        $ews->setCellValue('I1', 'Vacation Days');
        $ews->setCellValue('J1', 'Start Date');
        $ews->setCellValue('K1', 'End Date');
        $ews->setCellValue('L1', 'Status');


        $totalNumberBusinessDays = 0;
        $totalNumberVacationDays = 0;

        $row = 2;
        foreach( explode("-",$ids) as $vacreqId ) {

        //process.py script: replaced namespace by ::class: ['AppVacReqBundle:VacReqRequest'] by [VacReqRequest::class]
            $vacreq = $this->em->getRepository(VacReqRequest::class)->find($vacreqId);
            if( !$vacreq ) {
                continue;
            }

            //check if author can have access to view this request
            if( false == $this->security->isGranted("read", $vacreq) ) {
                continue; //skip this applicant because the current user does not permission to view this applicant
            }

            $ews->setCellValue('A'.$row, $vacreq->getId());

            $academicYearArr = $this->getRequestAcademicYears($vacreq);
            if( count($academicYearArr) > 0 ) {
                $academicYear = $academicYearArr[0];
            } else {
                $academicYear = null;
            }

            $ews->setCellValue('B'.$row, $vacreq->getUser());
            $ews->setCellValue('C'.$row, $academicYear);

            //Group
            $ews->setCellValue('D'.$row, $vacreq->getInstitution()."");

            $businessRequest = $vacreq->getRequestBusiness();
            if( $businessRequest ) {
                $numberBusinessDays = $this->specificRequestExcelInfo($ews,$row,$vacreq,$businessRequest,array('E','F','G','H'));
                if( $numberBusinessDays ) {
                    $totalNumberBusinessDays = $totalNumberBusinessDays + intval($numberBusinessDays);
                }
            }

            $vacationRequest = $vacreq->getRequestVacation();
            if( $vacationRequest ) {
                $numberVacationDays = $this->specificRequestExcelInfo($ews,$row,$vacreq,$vacationRequest,array('I','J','K','L'));
                if( $numberVacationDays ) {
                    $totalNumberVacationDays = $totalNumberVacationDays + intval($numberVacationDays);
                }
            }

            $row = $row + 1;
        }//foreach

        $ews->setCellValue('B'.$row, "Total");
        $ews->setCellValue('E'.$row, $totalNumberBusinessDays);
        $ews->setCellValue('I'.$row, $totalNumberVacationDays);

        $styleLastRow = [
            'font' => [
                'bold' => true,
            ],
//            'alignment' => [
//                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT,
//            ],
//            'borders' => [
//                'bottom' => [
//                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
//                ],
//            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_GRADIENT_LINEAR,
                'rotation' => 90,
                'startColor' => [
                    'argb' => 'ebf1de',
                ],
                'endColor' => [
                    'argb' => 'ebf1de',
                ],
            ],
        ];

        //set color light green to the last Total row
        $ews->getStyle('A'.$row.':'.'L'.$row)->applyFromArray($styleLastRow);

        //exit("ids=".$fellappids);


        // Auto size columns for each worksheet
        //\PHPExcel_Shared_Font::setAutoSizeMethod(\PHPExcel_Shared_Font::AUTOSIZE_METHOD_EXACT);
        foreach ($ea->getWorksheetIterator() as $worksheet) {

            $ea->setActiveSheetIndex($ea->getIndex($worksheet));

            $sheet = $ea->getActiveSheet();
            $cellIterator = $sheet->getRowIterator()->current()->getCellIterator();
            $cellIterator->setIterateOnlyExistingCells(true);
            foreach ($cellIterator as $cell) {
                $sheet->getColumnDimension($cell->getColumn())->setAutoSize(true);
            }
        }


        return $ea;
    }
    public function specificRequestExcelInfo( $ews, $row, $vacreq, $request, $columnArr ) {
        if( $request ) {
            $numberDays = $request->getNumberOfDays();
            //Business Days
            $ews->setCellValue($columnArr[0].$row, $numberDays."");

            //Start Date
            $startDate = $request->getStartDate();
            if( $startDate ) {
                $startDate->setTimezone(new \DateTimeZone("UTC"));
                $ews->setCellValue($columnArr[1].$row, $startDate->format('m/d/Y'));
            }

            //End Date
            $endDate = $request->getEndDate();
            if( $endDate ) {
                $endDate->setTimezone(new \DateTimeZone("UTC"));
                $ews->setCellValue($columnArr[2].$row, $endDate->format('m/d/Y'));
            }

            //Status
            $status = null;
            if( $request && $request->getStatus() ) {
                if( $vacreq->getExtraStatus() ) {
                    $extraStatus = $vacreq->getExtraStatus();
                    $extraStatus = str_replace('(Approved)','',$extraStatus);
                    $extraStatus = str_replace('(Canceled)','',$extraStatus);
                    $status = $request->getStatus()." (".$extraStatus.")";
                } else {
                    $status = $request->getStatus();
                }
            }
            if( $status ) {
                $ews->setCellValue($columnArr[3].$row, ucfirst($status));
            }

            return $numberDays;
        }

        return 0;
    }

    public function createtListExcelSpout( $ids, $fileName ) {

        set_time_limit(600);

        $author = $this->security->getUser();
        //$transformer = new DateTimeToStringTransformer(null,null,'d/m/Y');

        //$writer = WriterFactory::create(Type::XLSX);
        $writer = WriterEntityFactory::createXLSXWriter();
        $writer->openToBrowser($fileName);

        $headerStyle = (new StyleBuilder())
            ->setFontBold()
            //->setFontItalic()
            ->setFontSize(12)
            ->setFontColor(Color::BLACK)
            ->setShouldWrapText()
            ->setBackgroundColor(Color::toARGB("E0E0E0"))
            ->build();

        $requestStyle = (new StyleBuilder())
            ->setFontSize(10)
            //->setShouldWrapText()
            ->build();

        $border = (new BorderBuilder())
            ->setBorderBottom(Color::GREEN, Border::WIDTH_THIN, Border::STYLE_DASHED)
            ->build();
        $footerStyle = (new StyleBuilder())
            ->setFontBold()
            //->setFontItalic()
            ->setFontSize(12)
            ->setFontColor(Color::BLACK)
            ->setShouldWrapText()
            ->setBackgroundColor(Color::toARGB("EBF1DE"))
            ->setBorder($border)
            ->build();

        //setTitle('Business/Vacation Requests')
        //$ews = $ea->getSheet(0);
        //$ews->setTitle('Business and Vacation Requests');


//        $ews->setCellValue('A1', 'ID');
//        $ews->setCellValue('B1', 'Person');
//        $ews->setCellValue('C1', 'Academic Year');
//        $ews->setCellValue('D1', 'Group');
//
//        $ews->setCellValue('E1', 'Business Days');
//        $ews->setCellValue('F1', 'Start Date');
//        $ews->setCellValue('G1', 'End Date');
//        $ews->setCellValue('H1', 'Status');
//
//        $ews->setCellValue('I1', 'Vacation Days');
//        $ews->setCellValue('J1', 'Start Date');
//        $ews->setCellValue('K1', 'End Date');
//        $ews->setCellValue('L1', 'Status');

//        $writer->addRowWithStyle(
//            [
//                'ID',                  //0 - A
//                'Person',              //1 - B
//                'Academic Year',       //2 - C
//                'Group',               //3 - D
//
//                'Business Days',       //4 - E
//                'Start Date',          //5 - F
//                'End Date',            //6 - G
//                'Status',              //7 - H
//
//                'Vacation Days',       //8 - I
//                'Start Date',          //9 - J
//                'End Date',            //10 - K
//                'Status',              //11 - L
//
//            ],
//            $headerStyle
//        );
        $spoutRow = WriterEntityFactory::createRowFromArray(
            [
                'ID',                  //0 - A
                'Person',              //1 - B
                'Academic Year',       //2 - C
                'Group',               //3 - D

                'Business Days',       //4 - E
                'Start Date',          //5 - F
                'End Date',            //6 - G
                'Status',              //7 - H

                'Vacation Days',       //8 - I
                'Start Date',          //9 - J
                'End Date',            //10 - K
                'Status',              //11 - L

            ],
            $headerStyle
        );
        $writer->addRow($spoutRow);


        $totalNumberBusinessDays = 0;
        $totalNumberVacationDays = 0;

        $row = 2;
        foreach( explode("-",$ids) as $vacreqId ) {

        //process.py script: replaced namespace by ::class: ['AppVacReqBundle:VacReqRequest'] by [VacReqRequest::class]
            $vacreq = $this->em->getRepository(VacReqRequest::class)->find($vacreqId);
            if( !$vacreq ) {
                continue;
            }

            //check if author can have access to view this request
            if( false == $this->security->isGranted("read", $vacreq) ) {
                continue; //skip this applicant because the current user does not permission to view this applicant
            }

            $data = array();

            //$ews->setCellValue('A'.$row, $vacreq->getId());
            $data[0] = $vacreq->getId();

            $academicYearArr = $this->getRequestAcademicYears($vacreq);
            if( count($academicYearArr) > 0 ) {
                $academicYear = $academicYearArr[0];
            } else {
                $academicYear = null;
            }

            //$ews->setCellValue('B'.$row, $vacreq->getUser());
            $data[1] = $vacreq->getUser()."";
            //$ews->setCellValue('C'.$row, $academicYear);
            $data[2] = $academicYear;

            //Group
            //$ews->setCellValue('D'.$row, $vacreq->getInstitution()."");
            $data[3] = $vacreq->getInstitution()."";

            $businessRequest = $vacreq->getRequestBusiness();
            if( $businessRequest ) {
                //$numberBusinessDays = $this->specificRequestExcelSpoutInfo($writer,$vacreq,$businessRequest,array('E','F','G','H'));
                $numberBusinessDays = $this->specificRequestExcelSpoutInfo($data,$vacreq,$businessRequest,array(4,5,6,7));
                if( $numberBusinessDays ) {
                    $totalNumberBusinessDays = $totalNumberBusinessDays + intval($numberBusinessDays);
                }
            } else {
                $data[4] = NULL;
                $data[5] = NULL;
                $data[6] = NULL;
                $data[7] = NULL;
            }
            //print_r($data);

            $vacationRequest = $vacreq->getRequestVacation();
            if( $vacationRequest ) {
                //$numberVacationDays = $this->specificRequestExcelSpoutInfo($writer,$vacreq,$vacationRequest,array('I','J','K','L'));
                $numberVacationDays = $this->specificRequestExcelSpoutInfo($data,$vacreq,$vacationRequest,array(8,9,10,11));
                if( $numberVacationDays ) {
                    $totalNumberVacationDays = $totalNumberVacationDays + intval($numberVacationDays);
                }
            } else {
                $data[8] = NULL;
                $data[9] = NULL;
                $data[10] = NULL;
                $data[11] = NULL;
            }

            //print_r($data);
            //exit('111');

            //$writer->addRowWithStyle($data,$requestStyle);
            $spoutRow = WriterEntityFactory::createRowFromArray($data, $requestStyle);
            $writer->addRow($spoutRow);
            //$row = $row + 1;
        }//foreach

        $data = array();
        $data[0] = NULL;
        $data[1] = NULL;
        $data[2] = NULL;
        $data[3] = NULL;
        $data[4] = NULL;
        $data[5] = NULL;
        $data[6] = NULL;
        $data[7] = NULL;
        $data[8] = NULL;

        //$ews->setCellValue('B'.$row, "Total"); //1
        $data[0] = "Total Days";
        //$ews->setCellValue('E'.$row, $totalNumberBusinessDays); //4
        $data[4] = $totalNumberBusinessDays;
        //$ews->setCellValue('I'.$row, $totalNumberVacationDays); //8
        $data[8] = $totalNumberVacationDays;
        //$writer->addRowWithStyle($data,$footerStyle);
        $spoutRow = WriterEntityFactory::createRowFromArray($data, $footerStyle);
        $writer->addRow($spoutRow);

        //set color light green to the last Total row
        //$ews->getStyle('A'.$row.':'.'L'.$row)->applyFromArray($styleLastRow);

        //exit("ids=".$fellappids);

        $writer->close();
    }
    public function specificRequestExcelSpoutInfo( &$data, $vacreq, $request, $columnArr ) {
        if( $request ) {
            $numberDays = $request->getNumberOfDays();
            //Business Days
            //$ews->setCellValue($columnArr[0].$row, $numberDays."");
            $data[$columnArr[0]] = $numberDays."";

            //Start Date
            $startDate = $request->getStartDate();
            if( $startDate ) {
                $startDate->setTimezone(new \DateTimeZone("UTC"));
                //$ews->setCellValue($columnArr[1].$row, $startDate->format('m/d/Y'));
                $data[$columnArr[1]] = $startDate->format('m/d/Y');
            } else {
                $data[$columnArr[1]] = NULL;
            }

            //End Date
            $endDate = $request->getEndDate();
            if( $endDate ) {
                $endDate->setTimezone(new \DateTimeZone("UTC"));
                //$ews->setCellValue($columnArr[2].$row, $endDate->format('m/d/Y'));
                $data[$columnArr[2]] = $endDate->format('m/d/Y');
            } else {
                $data[$columnArr[2]] = NULL;
            }

            //Status
            $status = null;
            if( $request && $request->getStatus() ) {
                if( $vacreq->getExtraStatus() ) {
                    $extraStatus = $vacreq->getExtraStatus();
                    $extraStatus = str_replace('(Approved)','',$extraStatus);
                    $extraStatus = str_replace('(Canceled)','',$extraStatus);
                    $status = $request->getStatus()." (".$extraStatus.")";
                } else {
                    $status = $request->getStatus();
                }
            }
            if( $status ) {
                //$ews->setCellValue($columnArr[3].$row, ucfirst($status));
                $data[$columnArr[3]] = ucfirst($status);
            } else {
                $data[$columnArr[3]] = NULL;
            }

            return $numberDays;
        }

        return 0;
    }

//    //NOT USED
//    //get all unique users with vacreq requests
//    public function getVacReqUsers() {
//
//        if(0) {
//        //process.py script: replaced namespace by ::class: ['AppVacReqBundle:VacReqRequest'] by [VacReqRequest::class]
//            $repository = $this->em->getRepository(VacReqRequest::class);
//            $dql = $repository->createQueryBuilder("request");
//
//            //$dql->select('request, DISTINCT user as users');
//            //$dql->select('DISTINCT request.user');
//            //$dql->select('user');
//            $dql->select('request');
//
//            //COALESCE(requestBusiness.numberOfDays,0) replace NULL with 0 (similar to ISNULL)
//            //$dql->addSelect('(COALESCE(requestBusiness.numberOfDays,0) + COALESCE(requestVacation.numberOfDays,0)) as thisRequestTotalDays');
//
//            $dql->leftJoin("request.user", "user");
//            //$dql->leftJoin("request.submitter", "submitter");
//            //$dql->leftJoin("user.infos", "infos");
//            //$dql->leftJoin("request.institution", "institution");
//            //$dql->leftJoin("request.tentativeInstitution", "tentativeInstitution");
//
//            //$dql->leftJoin("request.requestBusiness", "requestBusiness");
//            //$dql->leftJoin("request.requestVacation", "requestVacation");
//
//            //$dql->leftJoin("request.requestType", "requestType");
//
//            //$dql->groupBy('request,user.id');
//            //$dql->groupBy('request.id,user.id');
//            //$dql->groupBy('user.id');
//
//            $query = $dql->getQuery();
//            $requests = $query->getResult();
//            $uniqueUsers = array();
//            foreach ($requests as $request) {
//                $thisUser = $request->getUser();
//                $uniqueUsers[$thisUser->getId()] = 1;
//            }
//            echo "uniqueUsers=" . count($uniqueUsers) . "<br>";
//        }
//
//        //WITH  request.id <> request2.id
//        //WHERE duser.id = user.id
//        //INNER JOIN request.user user
//
////        $str =
////              "SELECT fosuser.id".
////              " FROM AppVacReqBundle:VacReqRequest request".
////              " INNER JOIN AppUserdirectoryBundle:User fosuser"
////              ." ON request.user = fosuser.id"
////              ." WHERE request.user IS NOT NULL"
////        ;
//
//        //When you include fields from the joined entity in the SELECT clause you get a fetch join
//        //" FROM AppVacReqBundle:VacReqRequest request".
//        $str =
//            "SELECT DISTINCT user.id as id".
//            " FROM App\\VacReqBundle\\Entity\\VacReqRequest request".
//            " INNER JOIN request.user user"
//            //." ON request.user = fosuser.id"
//            ." WHERE user IS NOT NULL"
//        ;
//
//        //$str = 'SELECT DISTINCT user.id FROM AppVacReqBundle:VacReqRequest r INNER JOIN r.user user';
//
//        $query = $this->em->createQuery($str);
//        //$query = $str->getQuery($str);
//
//        //$query->setMaxResults(50); //testing
//
//        $users = $query->getResult();
//        $ids = array_map('current', $users);
//        //echo "ids=".count($ids)."<br>";
//
////        //dump($users);
////        dump($ids);
////        exit('222');
////
////        foreach($users as $user) {
////            echo "user=".$user."<br>";
////        }
////
////        echo "users=".count($users)."<br>";
//        //exit('111');
//
//        return $ids;
//    }

    public function getVacReqUserIdsArrByDqlParameters($dql,$dqlParameters) {
        $dql->select('user.id');

        $dql->groupBy('user.id');

        $query = $dql->getQuery();

        if( count($dqlParameters) > 0 ) {
            $query->setParameters($dqlParameters);
        }

        $result = $query->getScalarResult();
        $ids = array_map('current', $result);
        //$ids = array_unique($ids);

        return $ids;
    }

    //127.0.0.1/order/index_dev.php/time-away-request/download-summary-report-spreadsheet/
    public function createtSummaryReportByNameSpout( $userIdsStr, $fileName, $yearRangeStr ) {

        set_time_limit(600);

        //echo "userIds=".count($userIds)."<br>";
        //exit('1');

        $userIds = explode("-",$userIdsStr);

        //$testing = true;
        $testing = false;

        $author = $this->security->getUser();
        //$transformer = new DateTimeToStringTransformer(null,null,'d/m/Y');

        $newline =  "\n"; //"<br>\n";

        $columns = array(
            //'',
            'Person',                   //0 - A
            'Email',                    //1 - B
            'Group',                    //3 - D
//            'Approvers',                            //Name(s)ofApprover(s)
//
            'Approved Vacation Days',               //TotalNumberOfApprovedVacationDaysDuringSelectedFiscalYear
            'Approved Business Days',               //TotalNumberOfBusinessTripDaysDuringSelectedFiscalYear
            'Approved Vacation and Business Days',  //TotalNumberOfApprovedDaysAway(VacationAndBusiness)DuringSelectedFiscalYear
            'Pending Vacation Days',                //TotalNumberOfRequestedVacationDaysForSelectedYearPendingApproval
            'Total Number of Vacation Requests',    //TotalNumberOfVacationRequests
//
            'Approved Carry Over Days',             //TotalNumberOfApprovedCarriedOverDaysFromLastYear
            'Approved Floating Days',               //TotalNumberOfApprovedFloatingDaysDuringSelectedFiscalYear

            //LinksToEachRequestForReview
        );

        //exit( "Person col=".array_search('Person', $columns) );

        if( $testing == false ) {
            //$writer = WriterFactory::create(Type::XLSX);
            $writer = WriterEntityFactory::createXLSXWriter();

            //$writer->setColumnsWidth(25); //setDefaultColumnWidth(25);

            $writer->openToBrowser($fileName);

            $headerStyle = (new StyleBuilder())
                ->setFontBold()
                //->setFontItalic()
                ->setFontSize(12)
                ->setFontColor(Color::BLACK)
                ->setShouldWrapText()
                ->setBackgroundColor(Color::toARGB("E0E0E0"))
                ->build();

            $requestStyle = (new StyleBuilder())
                ->setFontSize(10)
                //->setShouldWrapText()
                ->build();

            $border = (new BorderBuilder())
                ->setBorderBottom(Color::GREEN, Border::WIDTH_THIN, Border::STYLE_DASHED)
                ->build();
            $footerStyle = (new StyleBuilder())
                ->setFontBold()
                //->setFontItalic()
                ->setFontSize(12)
                ->setFontColor(Color::BLACK)
                ->setShouldWrapText()
                ->setBackgroundColor(Color::toARGB("EBF1DE"))
                ->setBorder($border)
                ->build();

            $spoutRow = WriterEntityFactory::createRowFromArray(
                $columns,
                $headerStyle
            );
            $writer->addRow($spoutRow);
        }
        
        $totalNumberBusinessDays = 0;
        $totalNumberVacationDays = 0;
        $totalNumberPendingVacationDays = 0;
        $totalRequests = 0;
        $totalCarryoverApprovedRequests = 0;
        $totalApprovedFloatingDays = 0;

        $row = 2;
        foreach( $userIds as $userId ) {

        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:User'] by [User::class]
            $subjectUser = $this->em->getRepository(User::class)->find($userId);
            if( !$subjectUser ) {
                continue;
            }

//            //check if author can have access to view this request
//            if( false == $this->security->isGranted("read", $vacreq) ) {
//                continue; //skip this applicant because the current user does not permission to view this applicant
//            }

            $data = array();

            //$data[0] = ""; //$subjectUser->getId();

            $data[array_search('Person', $columns)] = $subjectUser."";
            //$data[0] = $subjectUser->getSingleEmail()."";

            $data[array_search('Email', $columns)] = $subjectUser->getSingleEmail();

            //Group
            $groups = "";
            $groupParams = array();
            $groupParams['statusArr'] = array('default','user-added');
            $groupParams['asObject'] = true;
            $groupParams['asUser'] = true;
            $groupParams['permissions'][] = array('objectStr'=>'VacReqRequest','actionStr'=>'create');
            $groupParams['exceptPermissions'][] = array('objectStr' => 'VacReqRequest', 'actionStr' => 'changestatus-carryover');
            $organizationalInstitutions = $this->getGroupsByPermission($subjectUser,$groupParams);
            //dump($organizationalInstitutions);
            //exit('111');
            foreach($organizationalInstitutions as $organizationalInstitution) {
                if( $groups ) {
                    $groups = $groups . $newline;
                }
                $groups = $groups . $organizationalInstitution->getShortestName();
            }
            $data[array_search('Group', $columns)] = $groups;

            $vacationDaysRes = $this->getApprovedTotalDaysAcademicYear($subjectUser, 'vacation', $yearRangeStr);
            $approvedVacDays = $vacationDaysRes['numberOfDays'];
            $approvedVacDays = intval($approvedVacDays);
            $totalNumberVacationDays = $totalNumberVacationDays + $approvedVacDays;
            $data[array_search('Approved Vacation Days', $columns)] = $approvedVacDays;


            $businessDaysRes = $this->getApprovedTotalDaysAcademicYear($subjectUser, 'business', $yearRangeStr);
            $approvedBusDays = $businessDaysRes['numberOfDays'];
            $approvedBusDays = intval($approvedBusDays);
            $totalNumberBusinessDays = $totalNumberBusinessDays + $approvedBusDays;
            $data[array_search('Approved Business Days', $columns)] = $approvedBusDays;

            $data[array_search('Approved Vacation and Business Days', $columns)] = $approvedVacDays + $approvedBusDays;

            $vacationPendingDaysRes = $this->getApprovedTotalDaysAcademicYear($subjectUser, 'vacation', $yearRangeStr, "pending");
            $pendingVacDays = $vacationPendingDaysRes['numberOfDays'];
            $pendingVacDays = intval($pendingVacDays);
            $totalNumberPendingVacationDays = $totalNumberPendingVacationDays + $pendingVacDays;
            $data[array_search('Pending Vacation Days', $columns)] = $pendingVacDays;

            //Total Number of Vacation Requests
            $vacationRequests = $this->getRequestsByUserYears($subjectUser, $yearRangeStr, 'vacation');
            $businessRequests = $this->getRequestsByUserYears($subjectUser, $yearRangeStr, 'business');
            $totalThisRequests = count($vacationRequests) + count($businessRequests);
            $totalRequests = $totalRequests + $totalThisRequests;
            $data[array_search('Total Number of Vacation Requests', $columns)] = $totalThisRequests;

            //$carryOverYear = '2022'; //2021-2022
            $startYearArr = $this->getYearsFromYearRangeStr($yearRangeStr);
            $carryOverYear = $startYearArr[0];
            $approvedRequests = $this->getCarryOverRequestsByUserStatusYear($subjectUser, 'approved', $carryOverYear);
            $carryoverApprovedRequests = count($approvedRequests);
            $totalCarryoverApprovedRequests = $totalCarryoverApprovedRequests + $carryoverApprovedRequests;
            $data[array_search('Approved Carry Over Days', $columns)] = $carryoverApprovedRequests;

            //Approved Floating Days
            $approvedFloatingDays = $this->getUserFloatingDay($subjectUser, $yearRangeStr, array('approved'));
            $approvedFloatingDays = intval($approvedFloatingDays);
            $totalApprovedFloatingDays = $totalApprovedFloatingDays + $approvedFloatingDays;
            $data[array_search('Approved Floating Days', $columns)] = $approvedFloatingDays;

            //print_r($data);
            //exit('111');

            if( $testing == false ) {
                //$writer->addRowWithStyle($data,$requestStyle);
                $spoutRow = WriterEntityFactory::createRowFromArray($data, $requestStyle);
                //$spoutRow = WriterEntityFactory::createRowFromArray($data);
                $writer->addRow($spoutRow);
            }
        }//foreach

        //exit('111');

        $data = array();

//        $data[0] = "";
//        $data[array_search('Person', $columns)] = NULL;
//        $data[array_search('Email', $columns)] = NULL;
//        $data[array_search('Group', $columns)] = NULL;
//        $data[array_search('Approved Vacation Days', $columns)] = NULL;
//        $data[array_search('Approved Business Days', $columns)] = NULL;
//        $data[array_search('Approved Vacation and Business Days', $columns)] = NULL;
//        $data[array_search('Pending Vacation Days', $columns)] = NULL;
//        $data[array_search('Total Number of Vacation Requests', $columns)] = NULL;
//        $data[array_search('Approved Carry Over Days', $columns)] = NULL;
//        $data[array_search('Approved Floating Days', $columns)] = NULL;

        //$data[0] = "";
        $data[array_search('Person', $columns)] = "Total";
        $data[array_search('Email', $columns)] = NULL;
        $data[array_search('Group', $columns)] = NULL;
        $data[array_search('Approved Vacation Days', $columns)] = $totalNumberVacationDays;
        $data[array_search('Approved Business Days', $columns)] = $totalNumberBusinessDays;
        $data[array_search('Approved Vacation and Business Days', $columns)] = $totalNumberVacationDays + $totalNumberBusinessDays;
        $data[array_search('Pending Vacation Days', $columns)] = $totalNumberPendingVacationDays;
        $data[array_search('Total Number of Vacation Requests', $columns)] = $totalRequests;
        $data[array_search('Approved Carry Over Days', $columns)] = $totalCarryoverApprovedRequests;
        $data[array_search('Approved Floating Days', $columns)] = $totalApprovedFloatingDays;

        if( $testing == false ) {
            $spoutRow = WriterEntityFactory::createRowFromArray($data, $footerStyle);
            //$spoutRow = WriterEntityFactory::createRowFromArray($data);
            $writer->addRow($spoutRow);

            //set color light green to the last Total row
            //$ews->getStyle('A'.$row.':'.'L'.$row)->applyFromArray($styleLastRow);

            //exit("ids=".$fellappids);

            $writer->close();
        } else {
            print_r($data);
            exit('111');
        }

    }

    //127.0.0.1/order/index_dev.php/time-away-request/download-summary-report-spreadsheet/
    //TODO: switch to https://packagist.org/packages/wilsonglasser/spout or https://packagist.org/packages/exment-git/spout
    public function createtSummaryMultiYears( $userId, $fileName, $yearRangeStr ) {

        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:User'] by [User::class]
        $subjectUser = $this->em->getRepository(User::class)->find($userId);
        if( !$subjectUser ) {
            return null;
        }

        set_time_limit(600);

        //echo "userIds=".count($userIds)."<br>";
        //exit('1');

        //$testing = true;
        $testing = false;

        $author = $this->security->getUser();
        $newline =  "\n"; //"<br>\n";

        //rows:
        //Prior FY carry-over
        //Accrued vacation days
        //Total days available
        //Less days taken
        //Days available for carry-over

        $columns = array(
            '',                   //0 - A
            //'Y1',                 //1 - B
            //'Y2',                 //2 - C
            //'Y3'
        );

        $centuryToStr = 'FY';
        $yearsStr = array();
        if( $centuryToStr ) {
            foreach( array_reverse($yearRangeStr) as $yearRange) {
                list($startYear,$endYear) = explode("-", $yearRange);
                //replace first two chars by $centuryToStr: 2022 -> FY22
                $startYear = $centuryToStr . substr($startYear, 2);
                $endYear = $centuryToStr . substr($endYear, 2);
                $colTitle = $startYear . "-" . $endYear;
                $columns[] = $colTitle;
                $yearsStr[] = $colTitle;
            }
        }
        //dump($columns);
        //exit('fileName='.$fileName);

        if( $testing == false ) {
            //$writer = WriterFactory::create(Type::XLSX);
            $writer = WriterEntityFactory::createXLSXWriter();

            //$writer->setColumnsWidth(25); //setDefaultColumnWidth(25);
            //$writer->setDefaultColumnWidth(25);

            $writer->openToBrowser($fileName);

            $headerStyle = (new StyleBuilder())
                ->setFontBold()
                //->setFontItalic()
                ->setFontSize(12)
                ->setFontColor(Color::BLACK)
                ->setShouldWrapText()
                ->setBackgroundColor(Color::toARGB("E0E0E0"))
                ->build();

            $requestStyle = (new StyleBuilder())
                ->setFontSize(10)
                //->setShouldWrapText()
                ->build();

            $border = (new BorderBuilder())
                ->setBorderBottom(Color::GREEN, Border::WIDTH_THIN, Border::STYLE_DASHED)
                ->build();
            $footerStyle = (new StyleBuilder())
                ->setFontBold()
                //->setFontItalic()
                ->setFontSize(12)
                ->setFontColor(Color::BLACK)
                ->setShouldWrapText()
                ->setBackgroundColor(Color::toARGB("EBF1DE"))
                ->setBorder($border)
                ->build();

            //Add Title "Vacation Audit User Name"
            $data[0] = "Vacation Audit " . $subjectUser->getDisplayName();
            $spoutRow = WriterEntityFactory::createRowFromArray($data, $footerStyle);
            //$spoutRow = WriterEntityFactory::createRowFromArray($data);
            $writer->addRow($spoutRow);

            //Add FY** row: FY20-FY21 | FY21-FY22 | FY22-FY23
            $spoutRow = WriterEntityFactory::createRowFromArray(
                $columns,
                $headerStyle
            );
            $writer->addRow($spoutRow);
        }

        //$inaccuracyMessage = "The count of the vacation days may be imprecise due to included holidays"; //$this->getInaccuracyMessage();
        $inaccuracyMessage = $this->getInaccuracyMessage();

        $totalNumberBusinessDays = 0;
        $totalNumberVacationDays = 0;
        $totalNumberPendingVacationDays = 0;
        $totalRequests = 0;
        $totalCarryoverApprovedRequests = 0;
        $totalApprovedFloatingDays = 0;

        $row = 2;

//            //check if author can have access to view this request
//            if( false == $this->security->isGranted("read", $vacreq) ) {
//                continue; //skip this applicant because the current user does not permission to view this applicant
//            }

        $yearData = array();
        
        //Add row with "Prior FY carry-over"
        $data = array();
        $data[0] = "Prior FY carry-over";
        $col = 1;
        foreach( array_reverse($yearRangeStr) as $yearRange) {
            $carryoverDays = $this->getUserCarryOverDays($subjectUser,$yearRange);
            $data[$col] = $carryoverDays;
            $yearData[$yearRange]['carryoverDays'] = $carryoverDays;
            $col++;
        }
        //$spoutRow = WriterEntityFactory::createRowFromArray($data, $footerStyle);
        $spoutRow = WriterEntityFactory::createRowFromArray($data,$requestStyle);
        $writer->addRow($spoutRow);

        //Add row with "Accrued vacation days"
        $data = array();
        $data[0] = "Accrued vacation days";
        $col = 1;
        foreach( array_reverse($yearRangeStr) as $yearRange) {
            //$accruedDays = 24;
            $accruedDays = $this->getTotalAccruedDays($subjectUser,$yearRange);
            $data[$col] = $accruedDays;
            $yearData[$yearRange]['accruedDays'] = $accruedDays;
            $col++;
        }
        $spoutRow = WriterEntityFactory::createRowFromArray($data,$requestStyle);
        $writer->addRow($spoutRow);

        //Add row with "Total days available"
        $data = array();
        $data[0] = "Total days available";
        $col = 1;
        foreach( array_reverse($yearRangeStr) as $yearRange) {
            $accruedDays = $yearData[$yearRange]['accruedDays']; //24;
            $carryoverDays = $yearData[$yearRange]['carryoverDays']; //$this->getUserCarryOverDays($subjectUser,$yearRange);
            $totalDays = intval($accruedDays) + intval($carryoverDays);
            $data[$col] = $totalDays;
            $yearData[$yearRange]['totalDays'] = $totalDays;
            $col++;
        }
        $spoutRow = WriterEntityFactory::createRowFromArray($data,$requestStyle);
        $writer->addRow($spoutRow);

        //Add row with "Less days taken"
        $data = array();
        $data[0] = "Vacation days taken";
        $col = 1;
        foreach( array_reverse($yearRangeStr) as $yearRange) {
            $vacationDaysRes = $this->getApprovedTotalDaysAcademicYear($subjectUser,'vacation',$yearRange); //TODO: test it
            $vacationDays = $vacationDaysRes['numberOfDays'];
            $vacationAccurate = $vacationDaysRes['accurate'];
            //$vacationAccurate = false;
            $data[$col] = "-".$vacationDays;
            $yearData[$yearRange]['vacationDays'] = $vacationDays;
            $yearData[$yearRange]['vacationAccurate'] = $vacationAccurate;
            $col++;
        }
        $spoutRow = WriterEntityFactory::createRowFromArray($data,$requestStyle);
        $writer->addRow($spoutRow);

        //Days available for carry-over
        $data = array();
        $data[0] = "Days available for carry-over";
        $col = 1;
        foreach( array_reverse($yearRangeStr) as $yearRange) {
            $totalDays = $yearData[$yearRange]['totalDays'];
            $vacationDays = $yearData[$yearRange]['vacationDays'];
            $data[$col] = $totalDays - $vacationDays;
            $col++;
        }
        $spoutRow = WriterEntityFactory::createRowFromArray($data,$requestStyle);
        $writer->addRow($spoutRow);

        //Add accurate message
        $data = array();
        $data[0] = "Accuracy note";
        $col = 1;
        foreach( array_reverse($yearRangeStr) as $yearRange) {
            $vacationAccurate = $yearData[$yearRange]['vacationAccurate'];
            //$vacationAccurate = false;
            if( !$vacationAccurate ) {
                $data[$col] = "* ".$inaccuracyMessage;
            } else {
                $data[$col] = "";
            }
            $col++;
        }
        $spoutRow = WriterEntityFactory::createRowFromArray($data,$requestStyle);
        $writer->addRow($spoutRow);

        $writer->close();

        //TODO: working on summary
        return;



        //$data[0] = ""; //$subjectUser->getId();

        $data[array_search('Person', $columns)] = $subjectUser."";
        //$data[0] = $subjectUser->getSingleEmail()."";

        $data[array_search('Email', $columns)] = $subjectUser->getSingleEmail();

        //Group
        $groups = "";
        $groupParams = array();
        $groupParams['statusArr'] = array('default','user-added');
        $groupParams['asObject'] = true;
        $groupParams['asUser'] = true;
        $groupParams['permissions'][] = array('objectStr'=>'VacReqRequest','actionStr'=>'create');
        $groupParams['exceptPermissions'][] = array('objectStr' => 'VacReqRequest', 'actionStr' => 'changestatus-carryover');
        $organizationalInstitutions = $this->getGroupsByPermission($subjectUser,$groupParams);
        //dump($organizationalInstitutions);
        //exit('111');
        foreach($organizationalInstitutions as $organizationalInstitution) {
            if( $groups ) {
                $groups = $groups . $newline;
            }
            $groups = $groups . $organizationalInstitution->getShortestName();
        }
        $data[array_search('Group', $columns)] = $groups;

        $vacationDaysRes = $this->getApprovedTotalDaysAcademicYear($subjectUser, 'vacation', $yearRangeStr);
        $approvedVacDays = $vacationDaysRes['numberOfDays'];
        $approvedVacDays = intval($approvedVacDays);
        $totalNumberVacationDays = $totalNumberVacationDays + $approvedVacDays;
        $data[array_search('Approved Vacation Days', $columns)] = $approvedVacDays;


        $businessDaysRes = $this->getApprovedTotalDaysAcademicYear($subjectUser, 'business', $yearRangeStr);
        $approvedBusDays = $businessDaysRes['numberOfDays'];
        $approvedBusDays = intval($approvedBusDays);
        $totalNumberBusinessDays = $totalNumberBusinessDays + $approvedBusDays;
        $data[array_search('Approved Business Days', $columns)] = $approvedBusDays;

        $data[array_search('Approved Vacation and Business Days', $columns)] = $approvedVacDays + $approvedBusDays;

        $vacationPendingDaysRes = $this->getApprovedTotalDaysAcademicYear($subjectUser, 'vacation', $yearRangeStr, "pending");
        $pendingVacDays = $vacationPendingDaysRes['numberOfDays'];
        $pendingVacDays = intval($pendingVacDays);
        $totalNumberPendingVacationDays = $totalNumberPendingVacationDays + $pendingVacDays;
        $data[array_search('Pending Vacation Days', $columns)] = $pendingVacDays;

        //Total Number of Vacation Requests
        $vacationRequests = $this->getRequestsByUserYears($subjectUser, $yearRangeStr, 'vacation');
        $businessRequests = $this->getRequestsByUserYears($subjectUser, $yearRangeStr, 'business');
        $totalThisRequests = count($vacationRequests) + count($businessRequests);
        $totalRequests = $totalRequests + $totalThisRequests;
        $data[array_search('Total Number of Vacation Requests', $columns)] = $totalThisRequests;

        //$carryOverYear = '2022'; //2021-2022
        $startYearArr = $this->getYearsFromYearRangeStr($yearRangeStr);
        $carryOverYear = $startYearArr[0];
        $approvedRequests = $this->getCarryOverRequestsByUserStatusYear($subjectUser, 'approved', $carryOverYear);
        $carryoverApprovedRequests = count($approvedRequests);
        $totalCarryoverApprovedRequests = $totalCarryoverApprovedRequests + $carryoverApprovedRequests;
        $data[array_search('Approved Carry Over Days', $columns)] = $carryoverApprovedRequests;

        //Approved Floating Days
        $approvedFloatingDays = $this->getUserFloatingDay($subjectUser, $yearRangeStr, array('approved'));
        $approvedFloatingDays = intval($approvedFloatingDays);
        $totalApprovedFloatingDays = $totalApprovedFloatingDays + $approvedFloatingDays;
        $data[array_search('Approved Floating Days', $columns)] = $approvedFloatingDays;

        //print_r($data);
        //exit('111');

        if( $testing == false ) {
            //$writer->addRowWithStyle($data,$requestStyle);
            $spoutRow = WriterEntityFactory::createRowFromArray($data, $requestStyle);
            //$spoutRow = WriterEntityFactory::createRowFromArray($data);
            $writer->addRow($spoutRow);
        }

        //exit('111');

        $data = array();

        //$data[0] = "";
        $data[array_search('Person', $columns)] = "Total";
        $data[array_search('Email', $columns)] = NULL;
        $data[array_search('Group', $columns)] = NULL;
        $data[array_search('Approved Vacation Days', $columns)] = $totalNumberVacationDays;
        $data[array_search('Approved Business Days', $columns)] = $totalNumberBusinessDays;
        $data[array_search('Approved Vacation and Business Days', $columns)] = $totalNumberVacationDays + $totalNumberBusinessDays;
        $data[array_search('Pending Vacation Days', $columns)] = $totalNumberPendingVacationDays;
        $data[array_search('Total Number of Vacation Requests', $columns)] = $totalRequests;
        $data[array_search('Approved Carry Over Days', $columns)] = $totalCarryoverApprovedRequests;
        $data[array_search('Approved Floating Days', $columns)] = $totalApprovedFloatingDays;

        if( $testing == false ) {
            $spoutRow = WriterEntityFactory::createRowFromArray($data, $footerStyle);
            //$spoutRow = WriterEntityFactory::createRowFromArray($data);
            $writer->addRow($spoutRow);

            //set color light green to the last Total row
            //$ews->getStyle('A'.$row.':'.'L'.$row)->applyFromArray($styleLastRow);

            //exit("ids=".$fellappids);

            $writer->close();
        } else {
            print_r($data);
            exit('111');
        }

    }

    public function redirectIndex( $request ) {
        $routeName = $request->get('_route');
        $requestType = NULL;
        $requestTypeOriginal = false;
        $redirect = false;
        $toRouteName = $routeName;

        $requestParams = $request->query->all();
        if( $requestParams && array_key_exists("filter", $requestParams) ) {
            if (array_key_exists("requestType", $requestParams["filter"])) {
                $requestTypeOriginal = true;
                $requestTypeId = $requestParams["filter"]["requestType"];
                if( $requestTypeId ) {
        //process.py script: replaced namespace by ::class: ['AppVacReqBundle:VacReqRequestTypeList'] by [VacReqRequestTypeList::class]
                    $requestType = $this->em->getRepository(VacReqRequestTypeList::class)->find($requestTypeId);
                    if (!$requestType) {
                        throw $this->createNotFoundException('Unable to find Request Type by id=' . $requestTypeId);
                    }
                }
            }
        }

        if( !$requestType ) {

            //return NULL;
            if( $routeName == "vacreq_incomingrequests" || $routeName == "vacreq_myrequests" ) {
        //process.py script: replaced namespace by ::class: ['AppVacReqBundle:VacReqRequestTypeList'] by [VacReqRequestTypeList::class]
                $requestType = $this->em->getRepository(VacReqRequestTypeList::class)->findOneByAbbreviation('business-vacation');
            }

            if( $routeName == "vacreq_floatingrequests" || $routeName == "vacreq_myfloatingrequests" ) {
        //process.py script: replaced namespace by ::class: ['AppVacReqBundle:VacReqRequestTypeList'] by [VacReqRequestTypeList::class]
                $requestType = $this->em->getRepository(VacReqRequestTypeList::class)->findOneByAbbreviation('floatingday');
            }
        }
        //echo $routeName.": RequestType=".$requestType."<br>";

        //if( !$requestType ) {
        //    exit('No request type');
        //}

        ///////// get filter ////////////
        $startdate = NULL;
        if( isset($requestParams["filter"]["startdate"]) ) {
            $startdate = $requestParams["filter"]["startdate"];
        }

        $enddate = NULL;
        if( isset($requestParams["filter"]["enddate"]) ) {
            $enddate = $requestParams["filter"]["enddate"];
        }

        $academicYear = NULL;
        if( isset($requestParams["filter"]["academicYear"]) ) {
            $academicYear = $requestParams["filter"]["academicYear"];
        }

        $subjectUser = NULL;
        if( isset($requestParams["filter"]["user"]) ) {
            $subjectUser = $requestParams["filter"]["user"];
        }

        $submitter = NULL;
        if( isset($requestParams["filter"]["submitter"]) ) {
            $submitter = $requestParams["filter"]["submitter"];
        }

        $organizationalInstitutions = NULL;
        if( isset($requestParams["filter"]["organizationalInstitutions"]) ) {
            $organizationalInstitutions = $requestParams["filter"]["organizationalInstitutions"];
        }
        ///////// EOF get filter ////////////

        if( $routeName == "vacreq_incomingrequests" || $routeName == "vacreq_myrequests" ) {

            //requests list => floating list
            if( $requestType->getAbbreviation() == 'floatingday' ) {
                //if( $requestType->getAbbreviation() == 'carryover' ) {
                    $organizationalInstitutions = NULL;
                //}

                $toRouteName = 'vacreq_floatingrequests';
                if( $routeName == "vacreq_myrequests" ) {
                    $toRouteName = 'vacreq_myfloatingrequests';
                }

                $redirect = true;
            }//if( $requestType->getAbbreviation() == 'floatingday' ) {

        }//if( $routeName == "vacreq_incomingrequests" || $routeName == "vacreq_myrequests" )
        
        
        if( $routeName == "vacreq_floatingrequests" || $routeName == "vacreq_myfloatingrequests" ) {

            //floating list => requests list
            if( $requestType->getAbbreviation() == 'carryover' || $requestType->getAbbreviation() == 'business-vacation' ) {
                $toRouteName = 'vacreq_incomingrequests';
                if( $routeName == "vacreq_myfloatingrequests" ) {
                    $toRouteName = 'vacreq_myrequests';
                }

                $redirect = true;
            } //if requestType->getAbbreviation() 'carryover' || 'business-vacation' ) {

            //floating list without request type => floating list with request type
            if( $requestType->getAbbreviation() == 'floatingday' && $requestTypeOriginal === false ) {
                $toRouteName = 'vacreq_incomingrequests';
                if( $routeName == "vacreq_myfloatingrequests" ) {
                    $toRouteName = 'vacreq_myrequests';
                }

                $redirect = true;
            }
        }

        if( $redirect ) {
            return array(
                "routeName" => $toRouteName,
                "params" => array(
                    'filter[requestType]' => $requestType->getId(),
                    'filter[startdate]' => $startdate,
                    'filter[enddate]' => $enddate,
                    'filter[academicYear]' => $academicYear,
                    'filter[user]' => $subjectUser,
                    'filter[submitter]' => $submitter,
                    'filter[organizationalInstitutions]' => $organizationalInstitutions
                )
            );
        }


        return NULL;
    }

    //check if exact floating day already approved or pending (NOT USED)
    public function getCheckExactExistedFloatingDay( $floatingTypeId, $floatingDay, $subjectUserId ) {

        $newline =  "<br>\n";
        $resArr['error'] = false;
        $resArr['errorMsg'] = "";
        
        if( $floatingDay ) {
            $floatingDayDate = \DateTime::createfromformat('m/d/Y',$floatingDay);
            $floatingDayDateFrom = new \DateTime($floatingDayDate->format("Y-m-d")." 00:00:00");
            $floatingDayDateTo = new \DateTime($floatingDayDate->format("Y-m-d")." 23:59:59");
            //echo "floatingDayDateFrom=".$floatingDayDateFrom->format('Y-m-d H:i:s')."<br>";
            //echo "floatingDayDateTo=".$floatingDayDateTo->format('Y-m-d H:i:s')."<br>";
        }

        $parameters = array();
        //process.py script: replaced namespace by ::class: ['AppVacReqBundle:VacReqRequestFloating'] by [VacReqRequestFloating::class]
        $repository = $this->em->getRepository(VacReqRequestFloating::class);
        $dql = $repository->createQueryBuilder('floating');

        $dql->where("floating.user = :userId AND floating.floatingType=:floatingType");
        $parameters['userId'] = $subjectUserId;
        $parameters['floatingType'] = $floatingTypeId;

        //$dql->andWhere("floating.floatingDay = :floatingDay");
        //$parameters['floatingDay'] = $floatingDayDate->format('Y-m-d'); //2022-02-23
        $dql->andWhere("floating.floatingDay BETWEEN :floatingDayDateFrom AND :floatingDayDateTo");
        $parameters['floatingDayDateFrom'] = $floatingDayDateFrom; //2022-02-23
        $parameters['floatingDayDateTo'] = $floatingDayDateTo; //2022-02-23

        $dql->andWhere("(floating.status = 'pending' OR floating.status = 'approved')");

        $query = $dql->getQuery(); //$query = $this->em->createQuery($dql);

        if( count($parameters) > 0 ) {
            $query->setParameters($parameters);
        }

        $floatingRequests = $query->getResult();
        //echo "floatingRequests=".count($floatingRequests)."<br>";

        if( count($floatingRequests) > 0 ) {

        //process.py script: replaced namespace by ::class: ['AppVacReqBundle:VacReqFloatingTypeList'] by [VacReqFloatingTypeList::class]
            $floatingType = $this->em->getRepository(VacReqFloatingTypeList::class)->find($floatingTypeId);

            //getRequestAcademicYears
            //getAcademicYearEdgeDateBetweenRequestStartEnd
            //getRequestEdgeAcademicYearDate
            //$yearRange = $this->getCurrentAcademicYearRange();
            //$academicYearStartStr = "";
//            $academicYearArr = $vacreqUtil->getRequestAcademicYears($floatingDay);
//            if( count($academicYearArr) > 0 ) {
//                $academicYearStartStr = $academicYearArr[0]." ";
//            }
            //$academicYearStartStr = $this->getAcademicYearFromDate($floatingDay);
            $yearRangeStr = $this->getCurrentAcademicYearRange();

            $errorMsgArr = array();
            foreach($floatingRequests as $floatingRequest) {
                $status = $floatingRequest->getStatus();
                $floatingDay = $floatingRequest->getFloatingDay();
                $approver = $floatingRequest->getApprover();
                //echo "ID=".$floatingRequest->getId()."<br>";
                $approverDate = $floatingRequest->getApprovedRejectDate(); //MM/DD/YYYY and HH:MM.
                $createDate = $floatingRequest->getCreateDate();
                //echo $floatingRequest->getId().": floatingDay=".$floatingDay->format('d/m/Y')."<br>";
                //echo "approver=$approver <br>";
                //echo "approverDate=".$approverDate->format('d/m/Y')."<br>";

                $approverStr = "Unknown Approver";
                if( $approver ) {
                    $approverStr = $approver->getUsernameOptimal();
                }

                $approverDateStr = "Unknown Approved Date";
                if( $approverDate ) {
                    $approverDateStr = $approverDate->format('m/d/Y \a\t H:i');
                }

                $errorMsg = "Logical error to verify existing floating day";

                if( $floatingDay ) { //&& $approver && $approverDate
                    //$academicYear = ''; //[2021-2022]
                    if ($status == 'pending') {
                        $errorMsg =
                            "A pending Floating day of " . $floatingDay->format('m/d/Y') .
                            " has already been requested for this " . $yearRangeStr . " academic year" .
                            " on " . $createDate->format('m/d/Y \a\t H:i').". ".
                            $newline.
                            "Only one " . $floatingType->getName() . " floating day can be approved per academic year."; //(NOT USED)
                    }
                    if ($status == 'approved') {
                        $errorMsg =
                            "A Floating day of " . $floatingDay->format('m/d/Y') .
                            " has already been approved for this " . $yearRangeStr . " academic year by " .
                            $approverStr .
                            " on " . $approverDateStr . ". ".
                            $newline.
                            "Only one " . $floatingType->getName() . " floating day can be approved per academic year."; //(NOT USED)
                    }
//                    if ($status == 'canceled') {
//                        $errorMsg =
//                            "A Floating day of " . $floatingDay->format('m/d/Y') .
//                            " has already been approved for this " . $yearRangeStr . " academic year by " .
//                            $approver->getUsernameOptimal() .
//                            " on " . $approverDate->format('m/d/Y \a\t H:i') . ".";
//                        "Only one " . $floatingType->getName() . " floating day can be approved per academic year";
//                    }
//                    if ($status == 'rejected') {
//                        $errorMsg =
//                            "A Floating day of " . $floatingDay->format('m/d/Y') .
//                            " has already been rejected for this " . $yearRangeStr . " academic year by " .
//                            $approver->getUsernameOptimal() .
//                            " on " . $approverDate->format('m/d/Y \a\t H:i') . ".".
//                            $newline.
//                            "Only one " . $floatingType->getName() . " floating day can be approved per academic year";
//                    }
                }
//                else {
//                    $errorMsg = "Logical error to verify existing floating day";
//                }
                $errorMsgArr[] = $errorMsg;
            }//foreach

            if( count($errorMsgArr) > 0 ) {
                $resArr['error'] = true;
                $resArr['errorMsg'] = implode($newline.$newline,$errorMsgArr);
            }
        }//if( count($floatingRequests) > 0 )
        
        return $resArr;
    }

    //check if floating day already approved or pending in this academic year
    //$exceptEntityId - except this Floating Request entity.
    public function getCheckExistedFloatingDayInAcademicYear( $floatingTypeId, $floatingDay, $subjectUserId, $statusArr=array('approved'), $exceptEntityId=null ) {

        $newline =  "<br>\n";
        //$newline =  "\n";

        $resArr['error'] = false;
        $resArr['errorMsg'] = "";

        //process.py script: replaced namespace by ::class: ['AppVacReqBundle:VacReqFloatingTypeList'] by [VacReqFloatingTypeList::class]
        $floatingType = $this->em->getRepository(VacReqFloatingTypeList::class)->find($floatingTypeId);
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:User'] by [User::class]
        $user = $this->em->getRepository(User::class)->find($subjectUserId);

        //startDate=2021-07-01
        //endDate=2022-06-30
        //yearRangeStr=2021-2022
        //$yearRange = $this->getCurrentAcademicYearRange();
        //$yearRangeStr = $this->getCurrentAcademicYearRange();

//        if( $floatingDay ) {
//            $floatingDayDate = \DateTime::createfromformat('m/d/Y',$floatingDay);
//            $floatingDayDateFrom = new \DateTime($floatingDayDate->format("Y-m-d")." 00:00:00");
//            $floatingDayDateTo = new \DateTime($floatingDayDate->format("Y-m-d")." 23:59:59");
//            //echo "floatingDayDateFrom=".$floatingDayDateFrom->format('Y-m-d H:i:s')."<br>";
//            //echo "floatingDayDateTo=".$floatingDayDateTo->format('Y-m-d H:i:s')."<br>";
//        }

        //$floatingDay = "06/29/2021";
        //$floatingDay = "08/29/2021";

        $floatingDayDate = \DateTime::createfromformat('m/d/Y',$floatingDay);

        $yearRangeStr = $this->getAcademicYearBySingleDate($floatingDayDate);
        //echo "yearRangeStr=$yearRangeStr <br>";

        //yearRange: "2021-2022"
        $floatingRequests = $this->getUserFloatingDay($user,$yearRangeStr,$statusArr);
        //echo "floatingRequests=".count($floatingRequests)."<br>";

        if (count($floatingRequests) > 0) {
            $errorMsgArr = array();
            foreach ($floatingRequests as $floatingRequest) {
                
                if( $exceptEntityId && $exceptEntityId == $floatingRequest->getId() ) {
                    continue;
                }
                
                $status = $floatingRequest->getStatus();
                $extraStatus = $floatingRequest->getExtraStatus();
                $floatingDay = $floatingRequest->getFloatingDay();
                $approver = $floatingRequest->getApprover();
                $personAway = $floatingRequest->getUser();
                //echo "ID=".$floatingRequest->getId()."<br>";
                $approverDate = $floatingRequest->getApprovedRejectDate(); //MM/DD/YYYY and HH:MM.
                $createDate = $floatingRequest->getCreateDate();
                //echo $floatingRequest->getId().": floatingDay=".$floatingDay->format('d/m/Y')."<br>";
                //echo "approver=$approver <br>";
                //echo "approverDate=".$approverDate->format('d/m/Y')."<br>";

                $approverStr = "Unknown Approver";
                if ($approver) {
                    $approverStr = $approver->getUsernameOptimal();
                }

                $approverDateStr = "Unknown Approved Date";
                if ($approverDate) {
                    $approverDateStr = $approverDate->format('m/d/Y \a\t H:i');
                }

                $personAwayStr = "Unknown Person";
                if ($personAway) {
                    $personAwayStr = $personAway->getUsernameOptimal();
                }

                $errorMsg = "Logical error to verify existing floating day";

                if ($floatingDay) { //&& $approver && $approverDate
                    //$academicYear = ''; //[2021-2022]
                    if ($status == 'pending') {

                        $confirm = "Are you sure you would like to cancel this ".
                            $floatingType->getName()." floating Day request with ID #".
                            $floatingRequest->getId()."?";

                        $linkMsg = "Cancel the ".$floatingDay->format('m/d/Y').
                            " ".$floatingType->getName()." floating day request";

//                        $statusChangeUrl = $this->container->get('router')->generate(
//                            'vacreq_floating_status_change',
//                            array(
//                                'id' => $floatingRequest->getId(),
//                                'status' => 'canceled'
//                            )
//                            //UrlGeneratorInterface::ABSOLUTE_URL
//                        );
//                        $link =
//                            '<a
//                            class="btn btn-default"
//                            general-data-confirm="Are you sure you would like to cancel this '.
//                            $floatingType->getName().' floating Day request with ID #'.
//                            $floatingRequest->getId().'?"
//                            href="'.$statusChangeUrl.'">
//                            Cancel of the '.$floatingDay->format('m/d/Y').
//                            ' '.$floatingType->getName().' floating day
//                            </a>';

                        $routeName = "'vacreq_floating_status_ajax_change'";
                        $toStatus = "'canceled'";

                        $link = '<a'.
                           ' class="btn btn-default vacreq-status-change-action"'.
                           ' general-data-confirm="'.$confirm.'"'.
                           ' general-data-callback="changeFloatingStatusAjax('.$floatingRequest->getId().','.$toStatus.','.$routeName.');"'.
                            '>'.
                            $linkMsg.
                        '</a>';

                        $errorMsg =
                            "<div id='warning-existing-".$floatingRequest->getId()."'".
                            " class='well alert alert-info error-holder'>".
                            "A ".$floatingType->getName()." floating day request ID #".
                            $floatingRequest->getId()." for ".
                            $floatingDay->format('m/d/Y')." has already been submitted for this ".
                            $yearRangeStr." academic year for ".$personAwayStr." on ".
                            $createDate->format('m/d/Y \a\t H:i').", but it is still pending review. ".$newline.
                            "Only one ".$floatingType->getName()." floating day can be approved per academic year. ".
                            $newline.
                            "To submit a new floating day request for the same academic year, ".
                            "you would first need to cancel this previous request ". //request cancellation
                            "by pressing ".$link. //[Request cancelation of the 10/19/2022 Juneteenth floating day]."
                            "</div>".
                            "<div id='error-existing-".$floatingRequest->getId()."' class='alert alert-warning' style='display:none;'>".
                            "</div>"
                        ;
                    }
                    if( $status == 'approved' ) {

                        if( $extraStatus && strtolower($extraStatus) == strtolower('Cancellation Requested') ) {
                            $errorMsg =
                                "<div id='warning-existing-".$floatingRequest->getId()."'".
                                " class='well alert alert-info error-holder'>".
                                "A ".$floatingType->getName()." floating day ID #".
                                $floatingRequest->getId()." of ".$floatingDay->format('m/d/Y').
                                " has already been approved for this ".$yearRangeStr.
                                " academic year by ".$approverStr.
                                " for ".$personAwayStr." on ".$createDate->format('m/d/Y \a\t H:i').".".
                                $newline.
                                "The cancelation request has already been submitted for this approved floating day.".
                                $newline.
                                "Only one ".$floatingType->getName()." floating day can be approved per academic year. ".
                                $newline.
                                "To submit a new floating day request for the same academic year, ".
                                "this previous request must be canceled first by the group's approver.".
                                "</div>".
                                "<div id='error-existing-".$floatingRequest->getId()."' class='alert alert-warning' style='display:none;'>".
                                "</div>"
                            ;
                        } else {
                            $confirm = "Are you sure you would like to Request cancellation this,".
                                " already approved ".
                                $floatingType->getName(). " floating day request with ID #".
                                $floatingRequest->getId(). "?";

                            $linkMsg = "Request cancelation of the ".$floatingDay->format('m/d/Y').
                                " ".$floatingType->getName()." floating day request";

//                        $statusChangeUrl = $this->container->get('router')->generate(
//                            'vacreq_floating_status_cancellation_request',
//                            array(
//                                'id' => $floatingRequest->getId(),
//                                'status' => 'cancellation-request'
//                            )
//                        //UrlGeneratorInterface::ABSOLUTE_URL
//                        );
//                        $link =
//                            '<a
//                            class="btn btn-default"
//                            general-data-confirm="Are you sure you would like to Request cancellation this, '.
//                            'already approved '.
//                            $floatingType->getName(). ' floating day request with ID #'.
//                            $floatingRequest->getId(). '?"
//                            href="'.$statusChangeUrl.'">
//                            Request cancelation of the '.$floatingDay->format('m/d/Y').
//                            ' '.$floatingType->getName().' floating day
//                            </a>';

                            $routeName = "'vacreq_floating_status_cancellation_request_ajax'";
                            $toStatus = "'cancellation-request'";

                            $link = '<a'.
                                ' class="btn btn-default vacreq-status-change-action"'.
                                ' general-data-confirm="'.$confirm.'"'.
                                ' general-data-callback="changeFloatingStatusAjax('.$floatingRequest->getId().','.$toStatus.','.$routeName.');"'.
                                '>'.
                                $linkMsg.
                                '</a>';

                            $errorMsg =
                                "<div id='warning-existing-".$floatingRequest->getId()."'".
                                " class='well alert alert-info error-holder'>".
                                "A ".$floatingType->getName()." floating day ID #".
                                $floatingRequest->getId()." of ".$floatingDay->format('m/d/Y').
                                " has already been approved for this ".$yearRangeStr.
                                " academic year by ".$approverStr.
                                " for ".$personAwayStr." on ".$createDate->format('m/d/Y \a\t H:i').".".
                                $newline.
                                "Only one ".$floatingType->getName()." floating day can be approved per academic year. ".
                                $newline.
                                "To submit a new floating day request for the same academic year, ".
                                "you would first need to request cancellation ".
                                "of this previous request by pressing ".$link. //[Request cancelation of the 10/19/2022 Juneteenth floating day]."
                                "</div>".
                                "<div id='error-existing-".$floatingRequest->getId()."' class='alert alert-warning' style='display:none;'>".
                                "</div>"
                            ;
                        }//if else

                    }
//                    if ($status == 'canceled') {
//                        $errorMsg =
//                            "A Floating day of " . $floatingDay->format('m/d/Y') .
//                            " has already been approved for this " . $yearRangeStr . " academic year by " .
//                            $approver->getUsernameOptimal() .
//                            " on " . $approverDate->format('m/d/Y \a\t H:i') . ".";
//                        "Only one " . $floatingType->getName() . " floating day can be approved per academic year";
//                    }
//                    if ($status == 'rejected') {
//                        $errorMsg =
//                            "A Floating day of " . $floatingDay->format('m/d/Y') .
//                            " has already been rejected for this " . $yearRangeStr . " academic year by " .
//                            $approver->getUsernameOptimal() .
//                            " on " . $approverDate->format('m/d/Y \a\t H:i') . ".".
//                            $newline.
//                            "Only one " . $floatingType->getName() . " floating day can be approved per academic year";
//                    }
                }
//                else {
//                    $errorMsg = "Logical error to verify existing floating day";
//                }
                $errorMsgArr[] = $errorMsg;
            }//foreach

            if (count($errorMsgArr) > 0) {
                $resArr['error'] = true;
                $resArr['errorMsg'] = implode($newline, $errorMsgArr);
            }
        }//if( count($floatingRequests) > 0 )

        return $resArr;
    }

    public function getAcademicYearBySingleDate( $floatingDayDate ) {

        //$academicYearArr = array();
        //return "2014-2015, 2015-2016";
        //$academicYearStr = null;
//        $userSecUtil = $this->container->get('user_security_utility');
//        //academicYearStart: July 01
//        $academicYearStart = $userSecUtil->getSiteSettingParameter('academicYearStart','vacreq');
//        if( !$academicYearStart ) {
//            throw new \InvalidArgumentException('academicYearStart is not defined in Site Parameters.');
//        }
//        //academicYearEnd: June 30
//        $academicYearEnd = $userSecUtil->getSiteSettingParameter('academicYearEnd','vacreq');
//        if( !$academicYearEnd ) {
//            throw new \InvalidArgumentException('academicYearEnd is not defined in Site Parameters.');
//        }
        //$academicYearStart = $this->getAcademicYearStart();
        //$academicYearEnd = $this->getAcademicYearEnd();

        $dates = $this->getCurrentAcademicYearStartEndDates();
        $startDateStr = $dates['startDate']; //Y-m-d
        $endDateStr = $dates['endDate']; //Y-m-d

        $startDateStr = $startDateStr." 00:00:00";
        $endDateStr = $endDateStr." 23:59:59";

//        $dates = $request->getFinalStartEndDates();
//        $startDate = $dates['startDate'];
//        $endDate = $dates['endDate'];

        //echo "startDate= ".$startDate->format('Y-m-d')."<br>";
        //echo "endDate= ".$endDate->format('Y-m-d')."<br>";
        //echo "floatingDayDate= ".$floatingDayDate->format('Y-m-d H:i:s')."<br>";
        //echo "startDateStr= ".$startDateStr."<br>"; //2021-07-01
        //echo "endDateStr= ".$endDateStr."<br>"; //2022-06-30

        $academicYearStartDate = \DateTime::createfromformat('Y-m-d H:i:s',$startDateStr);
        $academicYearEndDate = \DateTime::createfromformat('Y-m-d H:i:s',$endDateStr);
        //echo "academicYearStartDate= ".$academicYearStartDate->format('Y-m-d H:i:s')."<br>";
        //echo "academicYearEndDate= ".$academicYearEndDate->format('Y-m-d H:i:s')."<br>";

        $startYear = $academicYearStartDate->format('Y');
        $endYear = $academicYearEndDate->format('Y');
        $singleDayYear = $floatingDayDate->format('Y');

        $startYear = intval($startYear);
        $endYear = intval($endYear);
        $singleDayYear = intval($singleDayYear);

        //diff between academic year and date year
        //count number of leaps
        $diffYear = abs($singleDayYear - $startYear);
        //echo "<br>diffYear= ".$diffYear."<br>";

        //------July 01------day-------June 30--------//
        //case 1: start and end dates are inside of academic year
        if( $floatingDayDate >= $academicYearStartDate && $floatingDayDate <= $academicYearEndDate ) {
            //echo "case 1: date is inside of academic year => current academic year <br>";
            return $startYear."-".$endYear;
        }

        //----day----July 01-------------June 30--------//
        //case 2: start date is before start of academic year
        if( $floatingDayDate < $academicYearStartDate ) {
            //echo "case 2: date is before start of academic year => previous academic year <br>";
            $startYear = $startYear - 1;
            $endYear = $endYear - 1;

            //count number of leaps
            $leaps = 0;
            for ($x = 1; $x <= $diffYear; $x++) {
                //echo "The number is: $x <br>";
                $academicYearStartDate->modify("- 1 year");
                if( $floatingDayDate < $academicYearStartDate ) {
                    $leaps++;
                }
            }
            //echo "leaps: $leaps <br>";

            $startYear = $startYear - $leaps;
            $endYear = $endYear - $leaps;

            return $startYear."-".$endYear;
        }

        //------July 01-------------June 30----day------//
        //case 3: end date is after end of academic year
        if( $floatingDayDate > $academicYearEndDate ) {
            //echo "case 3: date is after end of academic year => next academic year <br>";
            $startYear = $startYear + 1;
            $endYear = $endYear + 1;

            //count number of leaps
            $leaps = 0;
            for ($x = 1; $x <= $diffYear; $x++) {
                //echo "The number is: $x <br>";
                $academicYearEndDate->modify("+ 1 year");
                if( $floatingDayDate > $academicYearEndDate ) {
                    $leaps++;
                }
            }
            //echo "leaps: $leaps <br>";

            $startYear = $startYear + $leaps;
            $endYear = $endYear + $leaps;

            return $startYear."-".$endYear;
        }

        return NULL;
    }

    //get approved floating day for the academical year specified by $yearRange (2015-2016 - current academic year)
    //yearRange: "2021-2022"
    public function getUserFloatingDay( $user, $yearRange, $statusArr=array('approved') ) {
//        $userSecUtil = $this->container->get('user_security_utility');
        //echo "yearRange=".$yearRange."<br>";
//        //academicYearStart
//        $academicYearStart = $userSecUtil->getSiteSettingParameter('academicYearStart','vacreq');
//        if( !$academicYearStart ) {
//            throw new \InvalidArgumentException('academicYearStart is not defined in Site Parameters.');
//        }
//        //academicYearEnd
//        $academicYearEnd = $userSecUtil->getSiteSettingParameter('academicYearEnd','vacreq');
//        if( !$academicYearEnd ) {
//            throw new \InvalidArgumentException('academicYearEnd is not defined in Site Parameters.');
//        }
        $academicYearStart = $this->getAcademicYearStart();
        $academicYearEnd = $this->getAcademicYearEnd();

        //$floatingDayDateFrom = new \DateTime($floatingDayDate->format("Y-m-d")." 00:00:00");
        //$floatingDayDateTo = new \DateTime($floatingDayDate->format("Y-m-d")." 23:59:59");

        //constract start and end date for DB select "Y-m-d"
        //academicYearStart
        $academicYearStartStr = $academicYearStart->format('m-d')." 00:00:00";

        //years
        $yearRangeArr = $this->getYearsFromYearRangeStr($yearRange);
        $previousYear = $yearRangeArr[0];
        $currentYear = $yearRangeArr[1];

        $academicYearStartStr = $previousYear."-".$academicYearStartStr;
        //echo "current academicYearStartStr=".$academicYearStartStr."<br>";
        //academicYearEnd
        $academicYearEndStr = $academicYearEnd->format('m-d')." 23:59:59";

        $academicYearEndStr = $currentYear."-".$academicYearEndStr;
        //echo "current academicYearEndStr=".$academicYearEndStr."<br>";

        $totalFloatingDays = array();

        foreach($statusArr as $status) {
            //step1: get requests within current academic Year (2015-07-01 - 2016-06-30)
            $floatingDays = $this->getFloatingDaysByYearByStatus($user,$academicYearStartStr,$academicYearEndStr,true,$status);
            //echo $status.": numberOfDaysInside=".$numberOfDaysInside.", startYear=".$academicYearStartStr.", endYear=".$academicYearEndStr."<br>";
            $totalFloatingDays = array_merge($totalFloatingDays,$floatingDays);
        }

        return $totalFloatingDays;
    }
    public function getFloatingDaysByYearByStatus( $user, $startStr=null, $endStr=null, $asObject=false, $status='approved' ) {

        //echo $type.": requestTypeStr=".$requestTypeStr."<br>";
        //$numberOfDays = 0;

        //process.py script: replaced namespace by ::class: ['AppVacReqBundle:VacReqRequestFloating'] by [VacReqRequestFloating::class]
        $repository = $this->em->getRepository(VacReqRequestFloating::class);
        $dql =  $repository->createQueryBuilder("request");

        //if( $asObject ) {
            $dql->select('request');
        //} else {
        //    $dql->select('DISTINCT user.id, request.floatingDay, request.floatingType');
        //}

        $dql->leftJoin("request.user", "user");

        $dql->where("user.id = :userId AND request.status = :status");

        // |----|year|-----start-----end-----|year+1|----|
        // |----|2015-07-01|-----start-----end-----|2016-06-30|----|
        //if( $type == "inside" && $startStr && $endStr ) {
            //echo "range=".$startStr." > ".$endStr."<br>";
            //$dql->andWhere("request.floatingDay >= '" . $startStr . "'" . " AND request.floatingDay <= " . "'" . $endStr . "'");
        //}

        $dql->andWhere("request.floatingDay BETWEEN :floatingDayDateFrom AND :floatingDayDateTo");

        $query = $dql->getQuery(); //$query = $this->em->createQuery($dql);

        //echo "query=".$query->getSql()."<br>";
        //echo "dql=".$dql."<br>";

        $query->setParameters( array(
            'userId' => $user->getId(),
            'status' => $status,
            'floatingDayDateFrom' => $startStr,
            'floatingDayDateTo' => $endStr
        ));

        if( $asObject ) {
            $requests = $query->getResult();
            return $requests;
        } else {
            //$numberOfDaysRes = $query->getOneOrNullResult();
            $floatingDaysItems = $query->getResult();
            $floatingDaysArr = array();
            foreach($floatingDaysItems as $floatingDaysItem) {
                //Juneteenth: 6/27/2022 (Approved)
                $floatingDaysArr[] = $floatingDaysItem->printRequestShort();
            }
            return $floatingDaysArr;

//            if( $numberOfDaysItems ) {
//                //echo $status.": numberOfDaysItems count=".count($numberOfDaysItems)."<br>";
//                //$numberOfDaysItems = $numberOfDaysRes['numberOfDays'];
//                if( count($numberOfDaysItems) > 1 ) {
//                    //$logger = $this->container->get('logger');
//                    //$logger->warning('Logical error: found more than one SUM: count='.count($numberOfDaysItems));
//                }
//                foreach( $numberOfDaysItems as $numberOfDaysItem ) {
//                    //echo "+numberOfDays = ".$numberOfDaysItem['numberOfDays']."; count=".$numberOfDaysItem['totalCount']."<br>";
//                    //echo $status.": +numberOfDays = ".$numberOfDaysItem['numberOfDays']."<br>";
//                    $numberOfDays = $numberOfDays + $numberOfDaysItem['numberOfDays'];
//                }
//                //echo "### get numberOfDays = ".$numberOfDays."<br><br>";
//            }


        }

        return NULL;
    }

    public function getFloatingDayRangeNote() {
        //Please make sure the date for your requested day off occurs during the current fiscal year (7/1/CURRENT_YEAR and 6/30/CURRENT_YEAR).
        //Please make sure the date for the requested day off occurs during the current fiscal year between 07/01/2021 and 06/30/2022

        $userSecUtil = $this->container->get('user_security_utility');

        $floatingRestrictDateRange = $userSecUtil->getSiteSettingParameter('floatingRestrictDateRange','vacreq');
        if( $floatingRestrictDateRange === NULL ) {
            $floatingRestrictDateRange = true;
        }

        $calendarStartDate = NULL;
        $calendarEndDate = NULL;
        if( $floatingRestrictDateRange === true ) {
            //echo "floatingRestrictDateRange is TRUE <br>";
            $dates = $this->getCurrentAcademicYearStartEndDates(true);
            $startDate = $dates['startDate']; //Y-m-d
            $endDate = $dates['endDate']; //Y-m-d

            //$calendarStartDateStr = $startDateStr." 00:00:00";
            //$calendarEndDateStr = $endDateStr." 23:59:59";

            //$calendarStartDate = \DateTime::createFromFormat('Y-m-d', $calendarStartDateStr);
            //$calendarEndDate = \DateTime::createFromFormat('Y-m-d', $calendarEndDateStr);

            $calendarStartDate = $startDate->format('m/d/Y');
            $calendarEndDate = $endDate->format('m/d/Y');

//            $note = "Please make sure the date for your ".
//                "requested day off occurs during the current fiscal year ".
//                $calendarStartDate . " and " . $calendarEndDate;

            $note = "Please make sure the date for the requested ".
                "day off occurs during the current fiscal year between ".
                $calendarStartDate . " and " . $calendarEndDate;

            return $note;
        }

        return NULL;
    }

    public function getAuditYearRange() {
        $yearRanges = array();

        //Current Academic Year
        $currentAcademicYearRange = $this->getCurrentAcademicYearRange();
        list($year1,$maxYear) = explode("-", $currentAcademicYearRange);
        //$maxYear = $year2;
        $yearRanges[] = $currentAcademicYearRange;

        //Current Academic Year - 1
        $yearRanges[] = $this->getPreviousAcademicYearRange();

        //Current Academic Year - 2
        $previousAcademicYearRange = $this->getPreviousAcademicYearRange(1);
        $yearRanges[] = $previousAcademicYearRange;
        list($year1,$minYear) = explode("-", $previousAcademicYearRange);
        //$minYear = $year1;

        $yearRangeStr = $minYear."-".$maxYear;
        //echo "yearRangeStr=$yearRangeStr <br>";

        return array($yearRanges, $yearRangeStr);
    }

    public function downloadSummarySpreadsheet() {

    }

}