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
use App\FellAppBundle\Util\FellAppImportPopulateUtil;
use App\FellAppBundle\Util\FellAppUtil;
use App\FellAppBundle\Util\GoogleSheetManagement;
use App\FellAppBundle\Util\RecLetterUtil;
use App\FellAppBundle\Util\ReportGenerator;
use App\OrderformBundle\Util\SearchUtil;
use App\OrderformBundle\Util\OrderUtil;
use App\ResAppBundle\Util\ImportFromOldSystem;
use App\ResAppBundle\Util\PdfUtil;
use App\ResAppBundle\Util\ResAppUtil;
use App\TranslationalResearchBundle\Util\DashboardUtil;
use App\TranslationalResearchBundle\Util\PdfGenerator;
use App\TranslationalResearchBundle\Util\ReminderUtil;
use App\TranslationalResearchBundle\Util\TransResFormNodeUtil;
use App\TranslationalResearchBundle\Util\TransResImportData;
use App\TranslationalResearchBundle\Util\TransResPermissionUtil;
use App\TranslationalResearchBundle\Util\TransResRequestUtil;
use App\TranslationalResearchBundle\Util\TransResUtil;
use App\UserdirectoryBundle\Security\Authentication\AuthUtil;
use App\UserdirectoryBundle\User\Model\UserManager;
use App\UserdirectoryBundle\Util\EmailUtil;
use App\UserdirectoryBundle\Util\FormNodeUtil;
use App\UserdirectoryBundle\Util\UserDownloadUtil;
use App\UserdirectoryBundle\Util\UserGenerator;
use App\UserdirectoryBundle\Util\UserSecurityUtil;
use App\UserdirectoryBundle\Util\UserServiceUtil;
use App\VacReqBundle\Util\VacReqImportData;
use App\VacReqBundle\Util\VacReqUtil;
use FOS\CommentBundle\Model\CommentInterface;
use FOS\CommentBundle\Model\CommentManagerInterface;
use FOS\CommentBundle\Model\ThreadManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Routing\Annotation\Route;

//use Symfony\Component\Routing\Annotation\Route;
//use Symfony\Component\Routing\Annotation\Template;
//use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
//use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;


class OrderAbstractController extends AbstractController {

//    protected $calllogUtil;
//    public function __construct( CallLogUtil $calllogUtil ) {
//        $this->calllogUtil = $calllogUtil;
//    }

    //Check for auto-injection deprecation notice
    //1) Create OrderAbstarctController extends OrderAbstractController
    //2) Override getSubscribedServices adding util services
    //3) replace in controller $this->get by $this->container->get
    public static function getSubscribedServices()
    {
        $subscribedServices = parent::getSubscribedServices();

        //$subscribedServices['security.authentication_utils'] = '?'.AuthenticationUtils::class;
        $subscribedServices['security.password_encoder'] = '?'.UserPasswordEncoderInterface::class;
        
        $subscribedServices['user_security_utility'] = '?'.UserSecurityUtil::class;
        $subscribedServices['user_generator'] = '?'.UserGenerator::class;
        $subscribedServices['user_download_utility'] = '?'.UserDownloadUtil::class;
        $subscribedServices['user_mailer_utility'] = '?'.EmailUtil::class;
        $subscribedServices['user_formnode_utility'] = '?'.FormNodeUtil::class;
        $subscribedServices['user_service_utility'] = '?'.UserServiceUtil::class;
        $subscribedServices['user_manager'] = '?'.UserManager::class;
        $subscribedServices['authenticator_utility'] = '?'.AuthUtil::class;

        $subscribedServices['calllog_util'] = '?'.CallLogUtil::class;
        $subscribedServices['calllog_util_form'] = '?'.CallLogUtilForm::class;

        $subscribedServices['crn_util'] = '?'.CrnUtil::class;
        $subscribedServices['crn_util_form'] = '?'.CrnUtilForm::class;

        $subscribedServices['fellapp_util'] = '?'.FellAppUtil::class;
        $subscribedServices['fellapp_importpopulate_util'] = '?'.FellAppImportPopulateUtil::class;
        $subscribedServices['fellapp_reportgenerator'] = '?'.ReportGenerator::class;
        $subscribedServices['fellapp_googlesheetmanagement'] = '?'.GoogleSheetManagement::class;
        $subscribedServices['fellapp_rec_letter_util'] = '?'.RecLetterUtil::class;

        $subscribedServices['resapp_util'] = '?'.ResAppUtil::class;
        $subscribedServices['resapp_reportgenerator'] = '?'.\App\ResAppBundle\Util\ReportGenerator::class;
        $subscribedServices['resapp_pdfutil'] = '?'.PdfUtil::class;
        $subscribedServices['resapp_import_from_old_system_util'] = '?'.ImportFromOldSystem::class;

        $subscribedServices['transres_util'] = '?'.TransResUtil::class;
        $subscribedServices['transres_request_util'] = '?'.TransResRequestUtil::class;
        $subscribedServices['transres_permission_util'] = '?'.TransResPermissionUtil::class;
        $subscribedServices['transres_formnode_util'] = '?'.TransResFormNodeUtil::class;

        $subscribedServices['transres_pdf_generator'] = '?'.PdfGenerator::class;
        $subscribedServices['transres_import'] = '?'.TransResImportData::class;
        $subscribedServices['transres_dashboard'] = '?'.DashboardUtil::class;
        $subscribedServices['transres_reminder_util'] = '?'.ReminderUtil::class;

        $subscribedServices['vacreq_util'] = '?'.VacReqUtil::class;
        $subscribedServices['vacreq_import_data'] = '?'.VacReqImportData::class;

        $subscribedServices['scanorder_utility'] = '?'.OrderUtil::class;
        $subscribedServices['search_utility'] = '?'.SearchUtil::class;
        $subscribedServices['user_generator'] = '?'.UserGenerator::class;
        $subscribedServices['user_generator'] = '?'.UserGenerator::class;

        $subscribedServices['knp_paginator'] = '?'.PaginatorInterface::class;
        $subscribedServices['kernel'] = '?'.KernelInterface::class;
        $subscribedServices['logger'] = '?'.LoggerInterface::class;

        $subscribedServices['fos_comment.manager.comment'] = '?'.CommentManagerInterface::class;
        $subscribedServices['fos_comment.manager.thread'] = '?'.ThreadManagerInterface::class;
//        $subscribedServices['fos_comment'] = '?'.CommentInterface::class;

//        $subscribedServices['user_generator'] = '?'.UserGenerator::class;
//        $subscribedServices['user_generator'] = '?'.UserGenerator::class;
//        $subscribedServices['user_generator'] = '?'.UserGenerator::class;
//        $subscribedServices['user_generator'] = '?'.UserGenerator::class;


        return $subscribedServices;
    }

}