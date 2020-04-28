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

namespace App\ResAppBundle\Controller;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityNotFoundException;
use App\ResAppBundle\Entity\ResidencyApplication;
use App\ResAppBundle\Entity\Interview;
use App\ResAppBundle\Entity\Rank;
use App\ResAppBundle\Form\InterviewType;
use App\ResAppBundle\Form\RankType;
use App\UserdirectoryBundle\Entity\User;
use App\OrderformBundle\Helper\ErrorHelper;
use App\UserdirectoryBundle\Entity\AccessRequest;
use App\UserdirectoryBundle\Entity\Reference;
use App\ResAppBundle\Form\ResAppFilterType;
use App\ResAppBundle\Form\ResidencyApplicationType;
use App\UserdirectoryBundle\Util\UserUtil;
use App\UserdirectoryBundle\Controller\OrderAbstractController;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Form\Extension\Core\DataTransformer\DateTimeToStringTransformer;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
//use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
//use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Routing\Annotation\Route;



class ResAppRankController extends OrderAbstractController {

    /**
     * @Route("/rank/edit/{resappid}", name="resapp_rank_edit", methods={"GET"})
     * @Template("AppResAppBundle/Rank/rank_modal.html.twig")
     */
    public function rankEditAction(Request $request, $resappid) {

        if( false == $this->get('security.authorization_checker')->isGranted("read","ResidencyApplication") ){
            return $this->redirect( $this->generateUrl('resapp-nopermission') );
        }

        $em = $this->getDoctrine()->getManager();

        $resApp = $this->getDoctrine()->getRepository('AppResAppBundle:ResidencyApplication')->find($resappid);
        if( !$resApp ) {
            throw $this->createNotFoundException('Unable to find Residency Application by id='.$resappid);
        }

        $user = $this->get('security.token_storage')->getToken()->getUser();

        $rank = $resApp->getRank();

//        if( !$rank ) {
//            $rank = new Rank();
//            $resApp->setRank($rank);
//        }

        $form = $this->createForm(RankType::class, $rank, array(
            'action' => $this->generateUrl('resapp_rank_update', array('resappid' => $resappid)),
            'method' => 'PUT',
        ));
        
        return array(
            'entity' => $resApp,
            'form' => $form->createView(),
        );
    }


    /**
     * @Route("/rank/update-ajax/{resappid}", name="resapp_rank_update", methods={"PUT"})
     */
    public function rankUpdateAjaxAction(Request $request, $resappid) {

        if( false == $this->get('security.authorization_checker')->isGranted("read","ResidencyApplication") ){
            return $this->redirect( $this->generateUrl('resapp-nopermission') );
        }

        $rankValue = $request->request->get('rankValue');
        //echo "rankValue=".$rankValue."<br>";
        //echo "resappid=".$resappid."<br>";

        $logger = $this->container->get('logger');
        $logger->warning('create rank: resappid='.$resappid);

        $em = $this->getDoctrine()->getManager();

        $resApp = $this->getDoctrine()->getRepository('AppResAppBundle:ResidencyApplication')->find($resappid);
        if( !$resApp ) {
            throw $this->createNotFoundException('Unable to find Residency Application by id='.$resappid);
        }

        $user = $this->get('security.token_storage')->getToken()->getUser();

        $rank = $resApp->getRank();

        if( !$rank ) {
            //exit('no rank');
            $rank = new Rank();
            $rank->setUser($user);
            $rank->setUserroles($user->getRoles());
            $resApp->setRank($rank);
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
     * @Route("/rank/update/{resappid}", name="resapp_rank_update", methods={"PUT"})
     */
    public function rankUpdateAction(Request $request, $resappid) {

        if( false == $this->get('security.authorization_checker')->isGranted("read","ResidencyApplication") ){
            return $this->redirect( $this->generateUrl('resapp-nopermission') );
        }

        $logger = $this->container->get('logger');
        $logger->warning('create rank: resappid='.$resappid);

        $em = $this->getDoctrine()->getManager();

        $resApp = $this->getDoctrine()->getRepository('AppResAppBundle:ResidencyApplication')->find($resappid);
        if( !$resApp ) {
            throw $this->createNotFoundException('Unable to find Residency Application by id='.$resappid);
        }

        $user = $this->get('security.token_storage')->getToken()->getUser();

        $rank = $resApp->getRank();

        if( !$rank ) {
            //exit('no rank');
            $rank = new Rank();
            $rank->setUser($user);
            $rank->setUserroles($user->getRoles());
            $resApp->setRank($rank);
        } else {
            $rank->setUpdateuser($user);
            $rank->setUpdateuserroles($user->getRoles());
        }

        $form = $this->createForm(RankType::class, $rank, array(
            'action' => $this->generateUrl('resapp_rank_update', array('resappid' => $resappid)),
            'method' => 'PUT',
        ));

        $form->handleRequest($request);


        if( $form->isValid() ) {

            echo "rank=".$rank->getRank()."<br>";
            exit('submit rank');

            $em->persist($rank);
            $em->flush();

            //return $this->redirect($this->generateUrl('resapp_show',array('id' => $resappid)));
            return $this->redirect($this->generateUrl('resapp_home'));
        }
        //exit('form is invalid');

        //return $this->redirect($this->generateUrl('resapp_show',array('id' => $resappid)));
        return $this->redirect($this->generateUrl('resapp_home'));
    }
}
