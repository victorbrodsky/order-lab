<?php

namespace Oleg\FellAppBundle\Controller;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityNotFoundException;
use Oleg\FellAppBundle\Entity\FellowshipApplication;
use Oleg\FellAppBundle\Entity\Interview;
use Oleg\FellAppBundle\Form\InterviewType;
use Oleg\UserdirectoryBundle\Entity\User;
use Oleg\OrderformBundle\Helper\ErrorHelper;
use Oleg\UserdirectoryBundle\Entity\AccessRequest;
use Oleg\UserdirectoryBundle\Entity\Reference;
use Oleg\FellAppBundle\Form\FellAppFilterType;
use Oleg\FellAppBundle\Form\FellowshipApplicationType;
use Oleg\UserdirectoryBundle\Util\EmailUtil;
use Oleg\UserdirectoryBundle\Util\UserUtil;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Form\Extension\Core\DataTransformer\DateTimeToStringTransformer;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;



class FellAppManagement extends Controller {

    /**
     * Show home page
     *
     * @Route("/fellowship-types-settings", name="fellapp_settings")
     * @Template("OlegFellAppBundle:Management:management.html.twig")
     */
    public function indexAction(Request $request) {

        if( false == $this->get('security.context')->isGranted('ROLE_FELLAPP_COORDINATOR') && false == $this->get('security.context')->isGranted('ROLE_FELLAPP_DIRECTOR') ){
            return $this->redirect( $this->generateUrl('fellapp-nopermission') );
        }


        //get all fellowship types using role


    }

}
