<?php
/**
 * Created by PhpStorm.
 * User: ch3
 * Date: 2/21/2020
 * Time: 7:59 AM
 */

namespace App\UserdirectoryBundle\Controller;

use App\CallLogBundle\Util\CallLogUtil;

use App\UserdirectoryBundle\User\Model\UserManager;
use App\UserdirectoryBundle\Util\EmailUtil;
use App\UserdirectoryBundle\Util\FormNodeUtil;
use App\UserdirectoryBundle\Util\UserGenerator;
use App\UserdirectoryBundle\Util\UserSecurityUtil;
use App\UserdirectoryBundle\Util\UserServiceUtil;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

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

        $subscribedServices['user_security_utility'] = '?'.UserSecurityUtil::class;
        $subscribedServices['user_generator'] = '?'.UserGenerator::class;
        $subscribedServices['user_download_utility'] = '?'.UserDownloadUtil::class;
        $subscribedServices['user_mailer_utility'] = '?'.EmailUtil::class;
        $subscribedServices['user_formnode_utility'] = '?'.FormNodeUtil::class;
        $subscribedServices['user_service_utility'] = '?'.UserServiceUtil::class;
        $subscribedServices['user_manager'] = '?'.UserManager::class;

        $subscribedServices['calllog_util'] = '?'.CallLogUtil::class;

//        $subscribedServices['user_generator'] = '?'.UserGenerator::class;
//        $subscribedServices['user_generator'] = '?'.UserGenerator::class;
//        $subscribedServices['user_generator'] = '?'.UserGenerator::class;

        return $subscribedServices;
    }

}