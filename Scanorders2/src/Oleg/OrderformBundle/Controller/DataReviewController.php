<?php

namespace Oleg\OrderformBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;


class DataReviewController extends Controller {
      

    /**
     * @Route("/scan-order/{id}/data-review", name="scan-order-data-review-full", requirements={"id" = "\d+"})
     * @Method("GET")
     * @Template("OlegOrderformBundle:DataReview:index-order.html.twig")
     */
    public function getDataReviewAction($id) {

        $em = $this->getDoctrine()->getManager();

        $message = $em->getRepository('OlegOrderformBundle:Message')->findOneByOid($id);

        $queryE = $em->createQueryBuilder()
            ->from('OlegOrderformBundle:Educational', 'e')
            ->select("e")
            ->leftJoin("e.message", "message")
            ->where("message.id=:id")
            ->setParameter("id",$id);

        $educational = $queryE->getQuery()->getResult();


        $queryR = $em->createQueryBuilder()
            ->from('OlegOrderformBundle:Research', 'e')
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
