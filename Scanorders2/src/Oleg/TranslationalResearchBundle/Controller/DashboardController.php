<?php

namespace Oleg\TranslationalResearchBundle\Controller;

use Oleg\UserdirectoryBundle\Util\LargeFileDownloader;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;


/**
 * @Route("dashboard")
 */
class DashboardController extends Controller
{

    /**
     * @Route("/pi-level/", name="translationalresearch_dashboard_pilevel")
     * @Template("OlegTranslationalResearchBundle:Dashboard:pilevel.html.twig")
     */
    public function piLevelAction( Request $request ) {

        if( false === $this->get('security.authorization_checker')->isGranted('ROLE_TRANSRES_USER') ) {
            return $this->redirect( $this->generateUrl($this->container->getParameter('translationalresearch.sitename').'-nopermission') );
        }

        $title = "Dashboard for PI Level";
        $infos = array();

        return array(
            'infos' => $infos,
            'title' => $title,
        );
    }


    /**
     * @Route("/funded-level/", name="translationalresearch_dashboard_fundedlevel")
     * @Template("OlegTranslationalResearchBundle:Dashboard:pilevel.html.twig")
     */
    public function fundedLevelAction( Request $request ) {

        if( false === $this->get('security.authorization_checker')->isGranted('ROLE_TRANSRES_USER') ) {
            return $this->redirect( $this->generateUrl($this->container->getParameter('translationalresearch.sitename').'-nopermission') );
        }

        $title = "Dashboard for Funded Project Level";
        $infos = array();

        return array(
            'infos' => $infos,
            'title' => $title,
        );
    }

}
