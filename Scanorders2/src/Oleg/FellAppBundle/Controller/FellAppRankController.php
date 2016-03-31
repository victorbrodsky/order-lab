<?php

namespace Oleg\FellAppBundle\Controller;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityNotFoundException;
use Oleg\FellAppBundle\Entity\FellowshipApplication;
use Oleg\FellAppBundle\Entity\Interview;
use Oleg\FellAppBundle\Entity\Rank;
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
     * @Method("GET")
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

        $rank = $fellApp->getRank();

//        if( !$rank ) {
//            $rank = new Rank();
//            $fellApp->setRank($rank);
//        }

        $form = $this->createForm(new RankType(), $rank, array(
            'action' => $this->generateUrl('fellapp_rank_update', array('fellappid' => $fellappid)),
            'method' => 'PUT',
        ));
        
        return array(
            'entity' => $fellApp,
            'form' => $form->createView(),
        );
    }




    /**
     * Show home page
     *
     * @Route("/rank/update/{fellappid}", name="fellapp_rank_update")
     * @Method("PUT")
     * @Template("OlegFellAppBundle:Rank:rank_modal.html.twig")
     */
    public function rankUpdateAction(Request $request, $fellappid) {

        if( false == $this->get('security.context')->isGranted("read","FellowshipApplication") ){
            return $this->redirect( $this->generateUrl('fellapp-nopermission') );
        }

        $logger = $this->container->get('logger');
        $logger->warning('create rank');

        $em = $this->getDoctrine()->getManager();

        $fellApp = $this->getDoctrine()->getRepository('OlegFellAppBundle:FellowshipApplication')->find($fellappid);
        if( !$fellApp ) {
            throw $this->createNotFoundException('Unable to find Fellowship Application by id='.$fellappid);
        }

        $user = $this->get('security.context')->getToken()->getUser();

        $rank = $fellApp->getRank();

        if( !$rank ) {
            //exit('no rank');
            $rank = new Rank();
            $rank->setUser($user);
            $rank->setUserroles($user->getRoles());
            $fellApp->setRank($rank);
        } else {
            $rank->setUpdateuser($user);
            $rank->setUpdateuserroles($user->getRoles());
        }

        //$form = $this->createForm(new RankType(), $rank);
        $form = $this->createForm(new RankType(), $rank, array(
            'action' => $this->generateUrl('fellapp_rank_update', array('fellappid' => $fellappid)),
            'method' => 'PUT',
        ));

        $form->handleRequest($request);

//        echo "errors:<br>";
//        $string = (string) $form->getErrors(true);
//        echo "string errors=".$string."<br>";
//        echo "getErrors count=".count($form->getErrors())."<br>";
//        echo "getErrorsAsString()=".$form->getErrorsAsString()."<br>";
//        print_r($form->getErrors());
//        echo "<br>string errors:<br>";
//        print_r($form->getErrorsAsString());
//        echo "<br>";
//        exit();


        if( $form->isValid() ) {

            echo "rank=".$rank->getRank()."<br>";
            //exit('submit rank');

            $em->persist($rank);
            $em->flush();

            //return $this->redirect($this->generateUrl('fellapp_show',array('id' => $fellappid)));
            return $this->redirect($this->generateUrl('fellapp_home'));
        }
        exit('form is invalid');

        //return $this->redirect($this->generateUrl('fellapp_show',array('id' => $fellappid)));
        return $this->redirect($this->generateUrl('fellapp_home'));
    }




}
