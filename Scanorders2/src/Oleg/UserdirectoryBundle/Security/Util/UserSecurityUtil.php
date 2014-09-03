<?php
/**
 * Created by JetBrains PhpStorm.
 * User: oli2002
 * Date: 10/4/13
 * Time: 12:56 PM
 * To change this template use File | Settings | File Templates.
 */

namespace Oleg\UserdirectoryBundle\Security\Util;


class UserSecurityUtil {

    protected $em;
    protected $sc;
    protected $container;

    public function __construct( $em, $sc, $container ) {
        $this->em = $em;
        $this->sc = $sc;
        $this->container = $container;
    }

    public function isCurrentUser( $id ) {

        $user = $this->sc->getToken()->getUser();

        $entity = $this->em->getRepository('OlegUserdirectoryBundle:User')->find($id);

        if( $entity && $entity->getId() === $user->getId() ) {
            return true;
        }

        return false;
    }


    //used by login success handler to get user has access request
    public function getUserAccessRequest($user,$sitename) {
        $accessRequest = $this->em->getRepository('OlegUserdirectoryBundle:AccessRequest')->findOneBy(
            array('user' => $user, 'siteName' => $sitename)
        );

        return $accessRequest;
    }

    public function getUserAccessRequestsByStatus($sitename, $status) {
        $accessRequests = $this->em->getRepository('OlegUserdirectoryBundle:AccessRequest')->findBy(
            array('siteName' => $sitename, 'status' => $status)
        );

        return $accessRequests;
    }


    //check for the role in security context and in the user DB
    public function hasGlobalUserRole( $role, $user=null ) {

        if( false === $this->sc->isGranted('IS_AUTHENTICATED_FULLY') )
            return false;

        if( $this->sc->isGranted($role) )
            return true;

        if( $user == null )
            $user = $this->sc->getToken()->getUser();

//        if( $this->sc->isGranted('IS_AUTHENTICATED_ANONYMOUSLY') )
//            return false;

        if( !is_object($user) ) {
            //echo "user is not object: return false <br>";
            return false;
        } else {
            //echo "user is object <br>";
        }
        //exit();

        if( $user && $user->hasRole($role) )
            return true;

        return false;
    }

}