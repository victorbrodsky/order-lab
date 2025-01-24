<?php
/**
 * Created by PhpStorm.
 * User: ch3
 * Date: 2/21/2020
 * Time: 7:59 AM
 */

namespace App\UserdirectoryBundle\Controller;

use App\CallLogBundle\Util\CallLogUtil;
use App\CallLogBundle\Util\CallLogUtilForm;
use App\CrnBundle\Util\CrnUtil;
use App\CrnBundle\Util\CrnUtilForm;
use App\DemoDbBundle\Util\DemoDbUtil;
use App\FellAppBundle\Util\FellAppImportPopulateUtil;
use App\FellAppBundle\Util\FellAppUtil;
use App\FellAppBundle\Util\GoogleSheetManagement;
use App\FellAppBundle\Util\GoogleSheetManagementV2;
use App\FellAppBundle\Util\RecLetterUtil;
use App\FellAppBundle\Util\ReportGenerator;
use App\OrderformBundle\Util\SearchUtil;
use App\OrderformBundle\Util\OrderUtil;
use App\ResAppBundle\Util\ImportFromOldSystem;
use App\ResAppBundle\Util\PdfUtil;
use App\ResAppBundle\Util\ResAppUtil;
//use App\Routing\DBAL\MultiDbConnectionWrapper;
use App\Saml\Util\SamlConfigProvider;
use App\TranslationalResearchBundle\Util\PdfGenerator;
use App\TranslationalResearchBundle\Util\ReminderUtil;
use App\TranslationalResearchBundle\Util\TransResFormNodeUtil;
use App\TranslationalResearchBundle\Util\TransResImportData;
use App\TranslationalResearchBundle\Util\TransResPermissionUtil;
use App\TranslationalResearchBundle\Util\TransResRequestUtil;
use App\TranslationalResearchBundle\Util\TransResUtil;
use App\UserdirectoryBundle\Security\Authentication\AuthUtil;
use App\UserdirectoryBundle\Services\MultiDbConnectionWrapper;
use App\UserdirectoryBundle\User\Model\UserManager;
use App\UserdirectoryBundle\Util\EmailUtil;
use App\UserdirectoryBundle\Util\FormNodeUtil;
use App\UserdirectoryBundle\Util\InterfaceTransferUtil;
use App\UserdirectoryBundle\Util\UserDownloadUtil;
use App\UserdirectoryBundle\Util\UserGenerator;
use App\UserdirectoryBundle\Util\UserSecurityUtil;
use App\UserdirectoryBundle\Util\UserServiceUtil;
use App\UserdirectoryBundle\Util\UserUtil;
use App\UserdirectoryBundle\Util\UserTenantUtil;
use App\UtilBundles\FOSCommentBundle\Util\FosCommentListenerUtil;
use App\UtilBundles\FOSCommentBundle\Util\UserCommentUtil;
use App\VacReqBundle\Util\VacReqCalendarUtil;
use App\VacReqBundle\Util\VacReqImportData;
use App\VacReqBundle\Util\VacReqUtil;
use Doctrine\Persistence\ManagerRegistry;
use Knp\Component\Pager\PaginatorInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\KernelInterface;
//use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;


class OrderAbstractController extends AbstractController {

//    protected $managerRegistry;
//    public function __construct(ManagerRegistry $managerRegistry) {
//        $this->managerRegistry = $managerRegistry;
//    }
//    public function getDoctrine(): ManagerRegistry
//    {
//        return $this->managerRegistry;
//    }

    //AbstractController::getDoctrine()  is now deprecated.
    //New alternative for getDoctrine() in Symfony 5.4 and up
    public function getDoctrine() : ManagerRegistry
    {
        return $this->container->get('user_service_utility')->getDoctrine();
    }

    //Check for auto-injection deprecation notice
    //1) Create OrderAbstarctController extends OrderAbstractController
    //2) Override getSubscribedServices adding util services
    //3) replace in controller $this->get by $this->container->get
    public static function getSubscribedServices(): array
    {
        $subscribedServices = parent::getSubscribedServices();

        //$subscribedServices['security'] = '?'.Security::class;
        //$subscribedServices['security.authentication_utils'] = '?'.AuthenticationUtils::class;
        //$subscribedServices['security.password_encoder'] = '?'.UserPasswordEncoderInterface::class;
        //$subscribedServices['security.password_encoder'] = '?'.UserPasswordHasherInterface::class;

        $subscribedServices['user_utility'] = '?'.UserUtil::class;
        $subscribedServices['user_security_utility'] = '?'.UserSecurityUtil::class;
        $subscribedServices['user_generator'] = '?'.UserGenerator::class;
        $subscribedServices['user_download_utility'] = '?'.UserDownloadUtil::class;
        $subscribedServices['user_mailer_utility'] = '?'.EmailUtil::class;
        $subscribedServices['user_formnode_utility'] = '?'.FormNodeUtil::class;
        $subscribedServices['user_service_utility'] = '?'.UserServiceUtil::class;
        $subscribedServices['user_manager'] = '?'.UserManager::class;
        $subscribedServices['authenticator_utility'] = '?'.AuthUtil::class;
        $subscribedServices['user_tenant_utility'] = '?'.UserTenantUtil::class;
        $subscribedServices['interface_transfer_utility'] = '?'.InterfaceTransferUtil::class;

        $subscribedServices['calllog_util'] = '?'.CallLogUtil::class;
        $subscribedServices['calllog_util_form'] = '?'.CallLogUtilForm::class;

        $subscribedServices['crn_util'] = '?'.CrnUtil::class;
        $subscribedServices['crn_util_form'] = '?'.CrnUtilForm::class;

        $subscribedServices['fellapp_util'] = '?'.FellAppUtil::class;
        $subscribedServices['fellapp_importpopulate_util'] = '?'.FellAppImportPopulateUtil::class;
        $subscribedServices['fellapp_reportgenerator'] = '?'.ReportGenerator::class;
        $subscribedServices['fellapp_googlesheetmanagement'] = '?'.GoogleSheetManagement::class;
        $subscribedServices['fellapp_googlesheetmanagement_v2'] = '?'.GoogleSheetManagementV2::class;
        $subscribedServices['fellapp_rec_letter_util'] = '?'.RecLetterUtil::class;

        $subscribedServices['resapp_util'] = '?'.ResAppUtil::class;
        $subscribedServices['resapp_reportgenerator'] = '?'.\App\ResAppBundle\Util\ReportGenerator::class;
        $subscribedServices['resapp_pdfutil'] = '?'.PdfUtil::class;
        $subscribedServices['resapp_import_from_old_system_util'] = '?'.ImportFromOldSystem::class;
        //$subscribedServices['resapp_rec_letter_util'] = '?'.\App\ResAppBundle\Util\RecLetterUtil::class;

        $subscribedServices['transres_util'] = '?'.TransResUtil::class;
        $subscribedServices['transres_request_util'] = '?'.TransResRequestUtil::class;
        $subscribedServices['transres_permission_util'] = '?'.TransResPermissionUtil::class;
        $subscribedServices['transres_formnode_util'] = '?'.TransResFormNodeUtil::class;

        $subscribedServices['transres_pdf_generator'] = '?'.PdfGenerator::class;
        $subscribedServices['transres_import'] = '?'.TransResImportData::class;
        $subscribedServices['transres_reminder_util'] = '?'.ReminderUtil::class;

        $subscribedServices['vacreq_util'] = '?'.VacReqUtil::class;
        $subscribedServices['vacreq_import_data'] = '?'.VacReqImportData::class;
        $subscribedServices['vacreq_calendar_util'] = '?'.VacReqCalendarUtil::class;

        $subscribedServices['scanorder_utility'] = '?'.OrderUtil::class;
        $subscribedServices['search_utility'] = '?'.SearchUtil::class;
        $subscribedServices['user_generator'] = '?'.UserGenerator::class;
        $subscribedServices['user_generator'] = '?'.UserGenerator::class;

        $subscribedServices['knp_paginator'] = '?'.PaginatorInterface::class;
        $subscribedServices['kernel'] = '?'.KernelInterface::class;
        $subscribedServices['logger'] = '?'.LoggerInterface::class;

        //$subscribedServices['fos_comment.manager.comment'] = '?'.CommentManagerInterface::class;
        //$subscribedServices['fos_comment.manager.thread'] = '?'.ThreadManagerInterface::class;
        //$subscribedServices['fos_comment.manager.comment'] = '?'.CommentManager::class;
        //$subscribedServices['fos_comment.manager.thread'] = '?'.ThreadManager::class;
//        $subscribedServices['fos_comment'] = '?'.CommentInterface::class;
        $subscribedServices['user_comment_utility'] = '?'.UserCommentUtil::class;
        $subscribedServices['user_comment_listener_utility'] = '?'.FosCommentListenerUtil::class;

        $subscribedServices['dashboard_util'] = '?'.\App\DashboardBundle\Util\DashboardUtil::class;
        $subscribedServices['dashboard_init'] = '?'.\App\DashboardBundle\Util\DashboardInit::class;

        $subscribedServices['demodb_utility'] = '?'.DemoDbUtil::class;

        $subscribedServices['saml_config_provider_util'] = '?'.SamlConfigProvider::class;

        //$subscribedServices['routing_dbal'] = '?'.\App\Routing\DBAL\MultiDbConnectionWrapper::class;
        //$subscribedServices['routing_dbal'] = '?'.MultiDbConnectionWrapper::class;
        //$subscribedServices[] = MultiDbConnectionWrapper::class;

//        $subscribedServices['user_generator'] = '?'.UserGenerator::class;
//        $subscribedServices['user_generator'] = '?'.UserGenerator::class;
//        $subscribedServices['user_generator'] = '?'.UserGenerator::class;
//        $subscribedServices['user_generator'] = '?'.UserGenerator::class;

        return $subscribedServices;
    }

}