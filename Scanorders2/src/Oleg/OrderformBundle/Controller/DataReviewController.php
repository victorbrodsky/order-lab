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

        $orderinfo = $em->getRepository('OlegOrderformBundle:OrderInfo')->findOneByOid($id);

        //$educational = $em->getRepository('OlegOrderformBundle:Educational')->findByOrderinfo($orderinfo);
        //$research = $em->getRepository('OlegOrderformBundle:Research')->findByOrderinfo($orderinfo);

        $queryE = $em->createQueryBuilder()
            ->from('OlegOrderformBundle:Educational', 'e')
            ->select("e")
            ->leftJoin("e.orderinfo", "orderinfo")
            ->where("orderinfo.id=:id")
            ->setParameter("id",$id);

        $educational = $queryE->getQuery()->getResult();


        $queryR = $em->createQueryBuilder()
            ->from('OlegOrderformBundle:Research', 'e')
            ->select("e")
            ->leftJoin("e.orderinfo", "orderinfo")
            ->where("orderinfo.id=:id")
            ->setParameter("id",$id);

        $research = $queryR->getQuery()->getResult();

        return array(
            'educationals' => $educational,
            'researches' => $research,
            'entity' => $orderinfo
        );

    }


}
