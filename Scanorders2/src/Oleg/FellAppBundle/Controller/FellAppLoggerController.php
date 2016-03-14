<?php

namespace Oleg\FellAppBundle\Controller;

use Oleg\UserdirectoryBundle\Controller\LoggerController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Oleg\UserdirectoryBundle\Entity\Logger;
use Oleg\UserdirectoryBundle\Form\LoggerType;

/**
 * Logger controller.
 *
 * @Route("/event-log")
 */
class FellAppLoggerController extends LoggerController
{

    /**
     * Lists all Logger entities.
     *
     * @Route("/", name="fellapp_logger")
     * @Method("GET")
     * @Template("OlegFellAppBundle:Logger:index.html.twig")
     */
    public function indexAction(Request $request)
    {
        if(
            false == $this->get('security.context')->isGranted('ROLE_FELLAPP_COORDINATOR') &&
            false == $this->get('security.context')->isGranted('ROLE_FELLAPP_DIRECTOR') &&
            false == $this->get('security.context')->isGranted('ROLE_FELLAPP_ADMIN')
        ) {
            return $this->redirect( $this->generateUrl('fellapp-nopermission') );
        }
//        if( false == $this->get('security.context')->isGranted("read","FellowshipApplication") ){
//            return $this->redirect( $this->generateUrl('fellapp-nopermission') );
//        }

        //TODO: add fellowship type filtering for each object:
        //1) get fellowship type useing ObjectType and ObjectId
        //2) keep only objects with fellowship type equal to a fellowship type of the user's role

        $params = array(
            'sitename'=>$this->container->getParameter('fellapp.sitename')
        );
        return $this->listLogger($params,$request);
    }


    /**
     * Filter by Object Type "FellowshipApplication" and Object ID
     *
     * @Route("/application-log/{id}", name="fellapp_application_log")
     * @Method("GET")
     * @Template("OlegFellAppBundle:Logger:index.html.twig")
     */
    public function applicationLogAction(Request $request,$id) {

        if(
            false == $this->get('security.context')->isGranted('ROLE_FELLAPP_COORDINATOR') &&
            false == $this->get('security.context')->isGranted('ROLE_FELLAPP_DIRECTOR') &&
            false == $this->get('security.context')->isGranted('ROLE_FELLAPP_ADMIN')
        ) {
            return $this->redirect( $this->generateUrl('fellapp-nopermission') );
        }

//        if( false == $this->get('security.context')->isGranted("read","FellowshipApplication") ){
//            return $this->redirect( $this->generateUrl('fellapp-nopermission') );
//        }

        $em = $this->getDoctrine()->getManager();

        $fellApp = $em->getRepository('OlegFellAppBundle:FellowshipApplication')->find($id);
        if( !$fellApp ) {
            throw $this->createNotFoundException('Unable to find Fellowship Application by id='.$id);
        }

        if( false == $this->get('security.context')->isGranted("read",$fellApp) ) {
            return $this->redirect( $this->generateUrl('fellapp-nopermission') );
        }

        $objectType = $em->getRepository('OlegUserdirectoryBundle:EventObjectTypeList')->findOneByName("FellowshipApplication");
        if( !$objectType ) {
            throw $this->createNotFoundException('Unable to find EventObjectTypeList by name='."FellowshipApplication");
        }

        return $this->redirect($this->generateUrl(
            'fellapp_event-log-per-object_log',
            array(
                'filter[objectType][]' => $objectType->getId(),
                'filter[objectId]' => $id)
            )
        );
    }

    /**
     * Filter by Object Type "FellowshipApplication" and Object ID
     *
     * @Route("/event-log-per-object/", name="fellapp_event-log-per-object_log")
     * @Method("GET")
     * @Template("OlegFellAppBundle:Logger:index.html.twig")
     */
    public function applicationPerObjectLogAction(Request $request) {

        $params = array(
            'sitename' => $this->container->getParameter('fellapp.sitename'),
//            'hideObjectType' => true,
//            'hideObjectId' => true,
//            'hideIp' => true,
//            'hideRoles' => true,
            //'hideId' => true
        );
        $loggerFormParams = $this->listLogger($params,$request);

        $loggerFormParams['hideUserAgent'] = true;
        $loggerFormParams['hideWidth'] = true;
        $loggerFormParams['hideHeight'] = true;
        $loggerFormParams['hideADServerResponse'] = true;

        $loggerFormParams['hideIp'] = true;
        $loggerFormParams['hideRoles'] = true;
        $loggerFormParams['hideId'] = true;         //Event ID
        $loggerFormParams['hideObjectType'] = true;
        $loggerFormParams['hideObjectId'] = true;

        //get title postfix
        $filterform = $loggerFormParams['filterform'];
        $objectTypes = $filterform['objectType']->getData();
        $objectId = $filterform['objectId']->getData();

        $em = $this->getDoctrine()->getManager();
        $objectType = $em->getRepository('OlegUserdirectoryBundle:EventObjectTypeList')->find($objectTypes[0]);

        $loggerFormParams['titlePostfix'] = " for ".$objectType.": ".$objectId;//for FellowshipApplication: 162

        return $loggerFormParams;
    }



    public function addCustomDql_1($dql) {
        $em = $this->getDoctrine()->getManager();

        $dql->select(
            'logger,'.
            '(SELECT fell FROM OlegFellAppBundle:FellowshipApplication fell '.
            'WHERE logger.entityId IS NOT NULL AND fell.id=logger.entityId) as fellapp'
        //',(SELECT fell2 FROM OlegFellAppBundle:FellowshipApplication fell2 WHERE fell2.id=fellapp) as fellappObject'
        );

        //testing fellapp (like by voter)
        //only fellapp with fellowshipSubspecialty equal to Roles fellowshipSubspecialty
        if(1) {
            $user = $this->get('security.context')->getToken()->getUser();
            $roleObjects = $em->getRepository('OlegUserdirectoryBundle:User')->findUserRolesBySiteAndPartialRoleName($user, $sitename, "ROLE_FELLAPP_");
            $fellowshipTypes = array();
            foreach ($roleObjects as $roleObject) {
                if ($roleObject->getFellowshipSubspecialty()) {
                    $fellowshipTypes[] = $roleObject->getFellowshipSubspecialty()->getId() . "";  //$roleObject->getFellowshipSubspecialty()."";
                    echo "role add=" . $roleObject->getFellowshipSubspecialty()->getName() . "<br>";
                }
            }
            echo "count=" . count($fellowshipTypes) . "<br>";
            if (count($fellowshipTypes) > 0) {
                //$dql->leftJoin('fellapp.fellowshipSubspecialty', 'fellowshipSubspecialty');
                //$dql->andWhere("(fellapp IS NOT NULL AND fellowshipSubspecialty.id IN (" . implode(",", $fellowshipTypes) . "))");
                //$dql->andWhere("(fellapp IS NOT NULL AND fellowshipSubspecialty IS NOT NULL AND fellowshipSubspecialty=" . $fellowshipTypes[0] . "))");
            }
            echo "after JOIN<br>";
        }

        //$dql->select('logger');
//        //Oleg\UserdirectoryBundle\Entity
//        $objectNamespaceArr = explode("\\",$objectNamespace);
//        $objectNamespaceClean = $objectNamespaceArr[0].$objectNamespaceArr[1];
//        $objectName = $em->getRepository('OlegUserdirectoryBundle:EventObjectTypeList')->find($objectType);
//        if( !$objectName ) {
//            throw $this->createNotFoundException('Unable to find EventObjectTypeList by objectType id='.$objectType);
//        }
//        $subjectEntity = $em->getRepository($objectNamespaceClean.':'.$objectName)->find($objectId);

        return $dql;
    }

    //testing fellapp (like by voter)
    //only fellapp with fellowshipSubspecialty equal to Roles fellowshipSubspecialty
    public function addCustomDql_2($dql) {
        $em = $this->getDoctrine()->getManager();

        $user = $this->get('security.context')->getToken()->getUser();
        $roleObjects = $em->getRepository('OlegUserdirectoryBundle:User')->findUserRolesBySiteAndPartialRoleName($user, 'fellapp', "ROLE_FELLAPP_");
        $fellowshipTypes = array();
        foreach ($roleObjects as $roleObject) {
            if ($roleObject->getFellowshipSubspecialty()) {
                $fellowshipTypes[] = $roleObject->getFellowshipSubspecialty()->getId() . "";  //$roleObject->getFellowshipSubspecialty()."";
                echo "role add=" . $roleObject->getFellowshipSubspecialty()->getId() . ":" . $roleObject->getFellowshipSubspecialty()->getName() . "<br>";
            }
        }
        echo "count=" . count($fellowshipTypes) . "<br>";

        //WHERE logger.entityId IS NOT NULL AND fell.id=logger.entityId IN ('.implode(",", $fellowshipTypes).')
        $dql->select(
            'logger,'.
            '(SELECT F FROM \Oleg\FellAppBundle\Entity\FellowshipApplication F '.
            'LEFT JOIN F.fellowshipSubspecialty fellowshipSubspecialty WHERE '.
            'F.id=logger.entityId AND fellowshipSubspecialty.id IN('.implode(",", $fellowshipTypes).')) AS loggerEntity'
        );

        //$dql->leftJoin('loggerEntity.fellowshipSubspecialty', 'fellowshipSubspecialty');
        //$dql->where("logger.entityId IS NOT NULL AND loggerEntity='' AND loggerEntity IS NULL"); //AND loggerEntity=2

        return $dql;
    }
}
