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
     * @Route("/scan-order/data-review", name="scan-order-data-review")
     * @Method("GET")
     * @Template("OlegOrderformBundle:DataReview:index.html.twig")
     */
    public function getDataReviewAction() {
        
        $em = $this->getDoctrine()->getManager();

        $educationals = $em->getRepository('OlegOrderformBundle:Educational')->findByDirector(null);

        $researches = $em->getRepository('OlegOrderformBundle:Research')->findByPrincipal(null);

        //echo "count edu=".count($educationals)."<br>";

        return array(
            'educationals' => $educationals,
            'researches' => $researches
        );

    }


}
