<?php

namespace Oleg\OrderformBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Oleg\OrderformBundle\Entity\Scan;
use Oleg\OrderformBundle\Entity\Slide;
use Oleg\OrderformBundle\Form\ScanType;
use Oleg\OrderformBundle\Helper\FormHelper;

/**
 * Scan controller.
 *
 * @Route("/slide-return-request")
 */
class SlideReturnRequestController extends Controller
{

    /**
     * Lists all Slides for this order with oid=$id.
     *
     * @Route("/{id}", name="slide-return-request", requirements={"id" = "\d+"})
     * @Method("GET")
     * @Template("OlegOrderformBundle:SlideReturnRequest:index.html.twig")
     */
    public function indexAction( $id ) {

        $em = $this->getDoctrine()->getManager();

        $orderinfo = $em->getRepository('OlegOrderformBundle:OrderInfo')->findOneByOid($id);

        if (!$orderinfo) {
            throw $this->createNotFoundException('Unable to find OrderInfo entity with id='.$id);
        }

        $params = array();
        //$form = $this->createForm(new OrderInfoType($params,$orderinfo), $orderinfo);

        return array(
            'orderinfo' => $orderinfo,
        );
    }

    
}
