<?php

namespace Oleg\FellAppBundle\Controller;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityNotFoundException;
use Oleg\FellAppBundle\Entity\FellowshipApplication;
use Oleg\FellAppBundle\Entity\Interview;
use Oleg\FellAppBundle\Form\InterviewType;
use Oleg\FellAppBundle\Form\RankType;
use Oleg\UserdirectoryBundle\Entity\User;
use Oleg\OrderformBundle\Helper\ErrorHelper;
use Oleg\UserdirectoryBundle\Entity\AccessRequest;
use Oleg\UserdirectoryBundle\Entity\Reference;
use Oleg\FellAppBundle\Form\FellAppFilterType;
use Oleg\FellAppBundle\Form\FellowshipApplicationType;
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



class FellAppRankController extends Controller {

    /**
     * Show home page
     *
     * @Route("/rank/edit/{fellappid}", name="fellapp_rank_edit")
     *
     * @Template("OlegFellAppBundle:Rank:rank_modal.html.twig")
     */
    public function rankEditAction(Request $request, $fellappid) {

        if( false == $this->get('security.context')->isGranted("read","FellowshipApplication") ){
            return $this->redirect( $this->generateUrl('fellapp-nopermission') );
        }

        $em = $this->getDoctrine()->getManager();

        $fellApp = $this->getDoctrine()->getRepository('OlegFellAppBundle:FellowshipApplication')->find($fellappid);
        if( !$fellApp ) {
            throw $this->createNotFoundException('Unable to find Fellowship Application by id='.$fellappid);
        }

        $user = $this->get('security.context')->getToken()->getUser();

        $form = $this->createForm(new RankType(), $fellApp->getRank());

        
        return array(
            'entity' => $fellApp,
            'form' => $form->createView(),
        );
    }


}
