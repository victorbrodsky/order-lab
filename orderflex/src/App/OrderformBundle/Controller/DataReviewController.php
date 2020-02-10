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

namespace App\OrderformBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;


class DataReviewController extends AbstractController {
      

    /**
     * @Route("/scan-order/{id}/data-review", name="scan-order-data-review-full", requirements={"id" = "\d+"})
     * @Method("GET")
     * @Template("AppOrderformBundle/DataReview/index-order.html.twig")
     */
    public function getDataReviewAction($id) {

        $em = $this->getDoctrine()->getManager();

        $message = $em->getRepository('AppOrderformBundle:Message')->findOneByOid($id);

        $queryE = $em->createQueryBuilder()
            ->from('AppOrderformBundle:Educational', 'e')
            ->select("e")
            ->leftJoin("e.message", "message")
            ->where("message.id=:id")
            ->setParameter("id",$id);

        $educational = $queryE->getQuery()->getResult();


        $queryR = $em->createQueryBuilder()
            ->from('AppOrderformBundle:Research', 'e')
            ->select("e")
            ->leftJoin("e.message", "message")
            ->where("message.id=:id")
            ->setParameter("id",$id);

        $research = $queryR->getQuery()->getResult();

        return array(
            'educationals' => $educational,
            'researches' => $research,
            'entity' => $message
        );

    }


}
