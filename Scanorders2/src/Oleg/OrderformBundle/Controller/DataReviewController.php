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
     * @Route("/scan-order/data-review-old", name="scan-order-data-review")
     * @Method("GET")
     * @Template("OlegOrderformBundle:DataReview:index.html.twig")
     */
    public function getDataReviewOldAction() {
        
        $em = $this->getDoctrine()->getManager();

        $educationals = $em->getRepository('OlegOrderformBundle:Educational')->findByDirector(null);

        $researches = $em->getRepository('OlegOrderformBundle:Research')->findByPrincipal(null);

        //echo "count edu=".count($educationals)."<br>";

        return array(
            'educationals' => $educationals,
            'researches' => $researches
        );

    }


    /**
     * @Route("/scan-order/data-review/{id}", name="scan-order-data-review-full", requirements={"id" = "\d+"})
     * @Method("GET")
     * @Template("OlegOrderformBundle:DataReview:index-order.html.twig")
     */
    public function getDataReviewAction($id) {

        $em = $this->getDoctrine()->getManager();

        $orderinfo = $em->getRepository('OlegOrderformBundle:OrderInfo')->findByOid($id);

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
