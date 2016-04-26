<?php

namespace Oleg\VacReqBundle\Util;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Created by PhpStorm.
 * User: ch3
 * Date: 4/25/2016
 * Time: 11:16 AM
 */
class VacReqUtil
{

    protected $em;
    protected $sc;
    protected $container;


    public function __construct( $em, $sc, $container ) {

        $this->em = $em;
        $this->sc = $sc;
        $this->container = $container;

    }


    public function getSettingsByInstitution($instid) {
        $setting = $this->em->getRepository('OlegVacReqBundle:VacReqSettings')->findOneByInstitution($instid);
        return $setting;
    }


    public function getInstitutionSettingArray() {
        $settings = $this->em->getRepository('OlegVacReqBundle:VacReqSettings')->findAll();

        $arraySettings = array();

        foreach( $settings as $setting ) {
            if( $setting->getInstitution() ) {
                $instid = $setting->getInstitution()->getId();
                $arraySettings[$instid] = $setting;
            }
        }

        return $arraySettings;
    }



    public function settingsAddRemoveUsers( $settings, $userIds ) {
        $originalUsers = $settings->getEmailUsers();

        $newUsers = new ArrayCollection();
        foreach( explode(",",$userIds) as $userId ) {
            //echo "userId=" . $userId . "<br>";
            $emailUser = $this->em->getRepository('OlegUserdirectoryBundle:User')->find($userId);
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


    //find role approvers by institution
    public function getRequestApprovers( $entity ) {

        $institution = $entity->getInstitution();
        if( !$institution ) {
            return null;
        }

        $approvers = array();
        $roleApprovers = $this->em->getRepository('OlegUserdirectoryBundle:User')->findRolesBySiteAndPartialRoleName( "vacreq", 'ROLE_VACREQ_APPROVER', $institution->getId());
        $roleApprover = $roleApprovers[0];
        //echo "roleApprover=".$roleApprover."<br>";
        if( $roleApprover ) {
            $approvers = $this->em->getRepository('OlegUserdirectoryBundle:User')->findUserByRole($roleApprover->getName());
        }

        return $approvers;
    }


    public function sendConfirmationEmailToApprovers( $entity ) {

        $institution = $entity->getInstitution();
        if( !$institution ) {
            return null;
        }

        $emailUtil = $this->get('user_mailer_utility');
        //$break = "\r\n";

        $approvers = $this->getRequestApprovers($entity);

        $approversNameArr = array();

        foreach( $approvers as $approver ) {

            if( !$approver->getSingleEmail() ) {
                continue;
            }

            $approversNameArr[] = $approver;

//            $submitter = $entity->getUser();
//
//            $subject = "Review Faculty Vacation/Business Request #" . $entity->getId() . " Confirmation";
//
//            $message = "Dear " . $approver->getUsernameOptimal() . "," . $break.$break;
//            $message .= $submitter->getUsernameOptimal()." has submitted the pathology faculty vacation/business travel request and it is ready for review.";
//
//            if( $entity->getRequestBusiness() ) {
//                //href="{{ path(vacreq_sitename~'_status_change', { 'id': entity.id,  'requestName':requestName, 'status': 'approved' }) }}
//                //approved
//                $actionRequestUrl = $url = $this->container->get('router')->generate(
//                    'vacreq_status_change',
//                    array(
//                        'id' => $entity->getId(),
//                        'requestName' => 'business',
//                        'status' => 'approved'
//                    ),
//                    UrlGeneratorInterface::ABSOLUTE_URL
//                );
//                $message .= $break . $break . "Please click on the below URL to approve the business request:" . $break;
//                $message .= $actionRequestUrl;
//
//                //rejected
//                $actionRequestUrl = $url = $this->container->get('router')->generate(
//                    'vacreq_status_change',
//                    array(
//                        'id' => $entity->getId(),
//                        'requestName' => 'business',
//                        'status' => 'rejected'
//                    ),
//                    UrlGeneratorInterface::ABSOLUTE_URL
//                );
//                $message .= $break . $break . "Please click on the below URL to reject the business request:" . $break;
//                $message .= $actionRequestUrl;
//            }
//
//            if( $entity->getRequestVacation() ) {
//                //href="{{ path(vacreq_sitename~'_status_change', { 'id': entity.id,  'requestName':requestName, 'status': 'approved' }) }}
//                //approved
//                $actionRequestUrl = $url = $this->container->get('router')->generate(
//                    'vacreq_status_change',
//                    array(
//                        'id' => $entity->getId(),
//                        'requestName' => 'vacation',
//                        'status' => 'approved'
//                    ),
//                    UrlGeneratorInterface::ABSOLUTE_URL
//                );
//                $message .= $break . $break . "Please click on the below URL to approve the vacation request:" . $break;
//                $message .= $actionRequestUrl;
//
//                //rejected
//                $actionRequestUrl = $url = $this->container->get('router')->generate(
//                    'vacreq_status_change',
//                    array(
//                        'id' => $entity->getId(),
//                        'requestName' => 'vacation',
//                        'status' => 'rejected'
//                    ),
//                    UrlGeneratorInterface::ABSOLUTE_URL
//                );
//                $message .= $break . $break . "Please click on the below URL to reject the vacation request:" . $break;
//                $message .= $actionRequestUrl;
//            }
//
//            $message .= $break.$break."To approve or reject requests, Division Approvers must be on site or using vpn when off site";
//            $message .= $break.$break."**** PLEASE DON'T REPLY TO THIS EMAIL ****";

            $subject = "Review Faculty Vacation/Business Request #" . $entity->getId() . " Confirmation";
            $message = $this->createEmailBody($entity,$approver);
            $emailUtil->sendEmail($approver->getSingleEmail(), $subject, $message, null, null);

        } //foreach approver

        //send email to email users
        $subject = "Copy of the Review Faculty Vacation/Business Request #" . $entity->getId() . " Confirmation";
        $addText = "### This is a copy of a confirmation email sent to the approvers ".implode(", ",$approversNameArr)."###";
        $settings = $this->getSettingsByInstitution($institution->getId());
        foreach( $settings->getEmailUsers() as $emailUser ) {
            $emailUserEmail = $emailUser->getSingleEmail();
            if( $emailUserEmail ) {
                $message = $this->createEmailBody($entity,$emailUser,$addText);
                $emailUtil->sendEmail($emailUserEmail, $subject, $message, null, null);
            }
        }

    }
    public function createEmailBody($entity,$emailToUser,$addText=null) {

        $break = "\r\n";

        $submitter = $entity->getUser();

        $message = "Dear " . $emailToUser->getUsernameOptimal() . "," . $break.$break;

        if( $addText ) {
            $message .= $addText.$break.$break;
        }

        $message .= $submitter->getUsernameOptimal()." has submitted the pathology faculty vacation/business travel request and it is ready for review.";

        if( $entity->getRequestBusiness() ) {
            //href="{{ path(vacreq_sitename~'_status_change', { 'id': entity.id,  'requestName':requestName, 'status': 'approved' }) }}
            //approved
            $actionRequestUrl = $url = $this->container->get('router')->generate(
                'vacreq_status_change',
                array(
                    'id' => $entity->getId(),
                    'requestName' => 'business',
                    'status' => 'approved'
                ),
                UrlGeneratorInterface::ABSOLUTE_URL
            );
            $message .= $break . $break . "Please click on the below URL to approve the business request:" . $break;
            $message .= $actionRequestUrl;

            //rejected
            $actionRequestUrl = $url = $this->container->get('router')->generate(
                'vacreq_status_change',
                array(
                    'id' => $entity->getId(),
                    'requestName' => 'business',
                    'status' => 'rejected'
                ),
                UrlGeneratorInterface::ABSOLUTE_URL
            );
            $message .= $break . $break . "Please click on the below URL to reject the business request:" . $break;
            $message .= $actionRequestUrl;
        }

        if( $entity->getRequestVacation() ) {
            //href="{{ path(vacreq_sitename~'_status_change', { 'id': entity.id,  'requestName':requestName, 'status': 'approved' }) }}
            //approved
            $actionRequestUrl = $url = $this->container->get('router')->generate(
                'vacreq_status_change',
                array(
                    'id' => $entity->getId(),
                    'requestName' => 'vacation',
                    'status' => 'approved'
                ),
                UrlGeneratorInterface::ABSOLUTE_URL
            );
            $message .= $break . $break . "Please click on the below URL to approve the vacation request:" . $break;
            $message .= $actionRequestUrl;

            //rejected
            $actionRequestUrl = $url = $this->container->get('router')->generate(
                'vacreq_status_change',
                array(
                    'id' => $entity->getId(),
                    'requestName' => 'vacation',
                    'status' => 'rejected'
                ),
                UrlGeneratorInterface::ABSOLUTE_URL
            );
            $message .= $break . $break . "Please click on the below URL to reject the vacation request:" . $break;
            $message .= $actionRequestUrl;
        }

        $message .= $break.$break."To approve or reject requests, Division Approvers must be on site or using vpn when off site";
        $message .= $break.$break."**** PLEASE DON'T REPLY TO THIS EMAIL ****";

        return $message;
    }


    public function sendSingleRespondEmailToSubmitter( $entity, $requestName=null, $status ) {



    }

}