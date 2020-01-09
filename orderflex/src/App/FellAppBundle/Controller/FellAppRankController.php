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

namespace App\FellAppBundle\Controller;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityNotFoundException;
use App\FellAppBundle\Entity\FellowshipApplication;
use App\FellAppBundle\Entity\Interview;
use App\FellAppBundle\Entity\Rank;
use App\FellAppBundle\Form\InterviewType;
use App\FellAppBundle\Form\RankType;
use App\UserdirectoryBundle\Entity\User;
use App\OrderformBundle\Helper\ErrorHelper;
use App\UserdirectoryBundle\Entity\AccessRequest;
use App\UserdirectoryBundle\Entity\Reference;
use App\FellAppBundle\Form\FellAppFilterType;
use App\FellAppBundle\Form\FellowshipApplicationType;
use App\UserdirectoryBundle\Util\UserUtil;
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
     * @Route("/rank/edit/{fellappid}", name="fellapp_rank_edit")
     * @Method("GET")
     * @Template("AppFellAppBundle/Rank/rank_modal.html.twig")
     */
    public function rankEditAction(Request $request, $fellappid) {

        if( false == $this->get('security.authorization_checker')->isGranted("read","FellowshipApplication") ){
            return $this->redirect( $this->generateUrl('fellapp-nopermission') );
        }

        $em = $this->getDoctrine()->getManager();

        $fellApp = $this->getDoctrine()->getRepository('AppFellAppBundle:FellowshipApplication')->find($fellappid);
        if( !$fellApp ) {
            throw $this->createNotFoundException('Unable to find Fellowship Application by id='.$fellappid);
        }

        $user = $this->get('security.token_storage')->getToken()->getUser();

        $rank = $fellApp->getRank();

//        if( !$rank ) {
//            $rank = new Rank();
//            $fellApp->setRank($rank);
//        }

        $form = $this->createForm(RankType::class, $rank, array(
            'action' => $this->generateUrl('fellapp_rank_update', array('fellappid' => $fellappid)),
            'method' => 'PUT',
        ));
        
        return array(
            'entity' => $fellApp,
            'form' => $form->createView(),
        );
    }


    /**
     * @Route("/rank/update-ajax/{fellappid}", name="fellapp_rank_update")
     * @Method("PUT")
     */
    public function rankUpdateAjaxAction(Request $request, $fellappid) {

        if( false == $this->get('security.authorization_checker')->isGranted("read","FellowshipApplication") ){
            return $this->redirect( $this->generateUrl('fellapp-nopermission') );
        }

        $rankValue = $request->request->get('rankValue');
        //echo "rankValue=".$rankValue."<br>";
        //echo "fellappid=".$fellappid."<br>";

        $logger = $this->container->get('logger');
        $logger->warning('create rank: fellappid='.$fellappid);

        $em = $this->getDoctrine()->getManager();

        $fellApp = $this->getDoctrine()->getRepository('AppFellAppBundle:FellowshipApplication')->find($fellappid);
        if( !$fellApp ) {
            throw $this->createNotFoundException('Unable to find Fellowship Application by id='.$fellappid);
        }

        $user = $this->get('security.token_storage')->getToken()->getUser();

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

        //$res = 'notok';
        //$res = 'ok';
        //if( $rankValue != "" ) {
            $rank->setRank($rankValue);

            //echo "rank=".$rank->getRank()."<br>";
            //exit('submit rank');

            $em->persist($rank);
            $em->flush();

            $res = 'ok';
        //}

        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');
        $response->setContent(json_encode($res));
        return $response;
    }








    /**
     * NOT USED
     *
     * @Route("/rank/update/{fellappid}", name="fellapp_rank_update")
     * @Method("PUT")
     */
    public function rankUpdateAction(Request $request, $fellappid) {

        if( false == $this->get('security.authorization_checker')->isGranted("read","FellowshipApplication") ){
            return $this->redirect( $this->generateUrl('fellapp-nopermission') );
        }

        $logger = $this->container->get('logger');
        $logger->warning('create rank: fellappid='.$fellappid);

        $em = $this->getDoctrine()->getManager();

        $fellApp = $this->getDoctrine()->getRepository('AppFellAppBundle:FellowshipApplication')->find($fellappid);
        if( !$fellApp ) {
            throw $this->createNotFoundException('Unable to find Fellowship Application by id='.$fellappid);
        }

        $user = $this->get('security.token_storage')->getToken()->getUser();

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

        $form = $this->createForm(RankType::class, $rank, array(
            'action' => $this->generateUrl('fellapp_rank_update', array('fellappid' => $fellappid)),
            'method' => 'PUT',
        ));

        $form->handleRequest($request);


        if( $form->isValid() ) {

            echo "rank=".$rank->getRank()."<br>";
            exit('submit rank');

            $em->persist($rank);
            $em->flush();

            //return $this->redirect($this->generateUrl('fellapp_show',array('id' => $fellappid)));
            return $this->redirect($this->generateUrl('fellapp_home'));
        }
        //exit('form is invalid');

        //return $this->redirect($this->generateUrl('fellapp_show',array('id' => $fellappid)));
        return $this->redirect($this->generateUrl('fellapp_home'));
    }
}
