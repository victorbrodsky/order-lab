<?php

namespace Oleg\CallLogBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;


class CallEntryController extends Controller
{

    /**
     * @Route("/", name="calllog_home")
     * @Template("OlegCallLogBundle:CallLog:home.html.twig")
     */
    public function homeAction(Request $request)
    {

        //1) search box: MRN,



        $title = "Call Entry";

        return array(
            //'entity' => $entity,
            //'form' => $form->createView(),
            //'cycle' => $cycle,
            'title' => $title,
        );

    }



}
