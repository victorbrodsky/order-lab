<?php

namespace Oleg\TranslationalResearchBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;

class DefaultController extends Controller
{


    /**
     * @Route("/", name="translationalresearch_home")
     * @Template("OlegTranslationalResearchBundle:Default:index.html.twig")
     * @Method("GET")
     */
    public function indexAction( Request $request ) {

        if( false == $this->get('security.context')->isGranted('ROLE_TRANSLATIONALRESEARCH_USER') ){
            //exit('deidentifier: no permission');
            return $this->redirect( $this->generateUrl('translationalresearch-nopermission') );
        }

        return array(

        );
    }
}
