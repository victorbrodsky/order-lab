<?php

namespace Oleg\DeidentifierBundle\Controller;

use Oleg\UserdirectoryBundle\Entity\AccessRequest;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="deidentifier_home")
     * @Template("OlegDeidentifierBundle:Default:index.html.twig")
     * @Method("GET")
     */
    public function indexAction( Request $request ) {

        if( false == $this->get('security.context')->isGranted('ROLE_DEIDENTIFICATOR_USER') ){
            return $this->redirect( $this->generateUrl('deidentifier-nopermission') );
        }

        //check for active access requests
        $accessreqs = $this->getActiveAccessReq();

        //search box
        $search = trim( $request->get('search') );
        $userid = trim( $request->get('userid') );

        if( $search != "" || $userid != "" ) {

            //location search
            $userUtil = new UserUtil();
            $locations = $userUtil->indexLocation($search, $request, $this->container, $this->getDoctrine());

            //user search
            $params = array('time'=>'current_only','search'=>$search,'userid'=>$userid);
            $res = $this->indexUser($params);
            $pagination = $res['entities'];
            $roles = $res['roles'];
        }

        return array(
            'accessreqs' => count($accessreqs),
            'returnpage' => '',
        );
    }



    //check for active access requests
    public function getActiveAccessReq() {
        if( !$this->get('security.context')->isGranted('ROLE_DEIDENTIFICATOR_ADMIN') ) {
            return null;
        }
        $userSecUtil = $this->get('user_security_utility');
        $accessreqs = $userSecUtil->getUserAccessRequestsByStatus($this->container->getParameter('deidentifier.sitename'),AccessRequest::STATUS_ACTIVE);
        return $accessreqs;
    }
}
