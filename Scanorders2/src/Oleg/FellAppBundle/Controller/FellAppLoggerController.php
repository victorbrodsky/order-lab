<?php

namespace Oleg\FellAppBundle\Controller;

use Doctrine\ORM\Query\ResultSetMapping;
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

        $dql->select('logger');

        //WHERE logger.entityId IS NOT NULL AND fell.id=logger.entityId IN ('.implode(",", $fellowshipTypes).')
        $dql->select('logger,F');

        $dql->addSelect(
            '(SELECT F FROM \Oleg\FellAppBundle\Entity\FellowshipApplication F '.
            'LEFT JOIN F.fellowshipSubspecialty fellowshipSubspecialty WHERE '.
            'F.id=logger.entityId AND fellowshipSubspecialty.id IN('.implode(",", $fellowshipTypes).')) AS loggerEntity'
        );

        //$dql->addSelect('COUNT(loggerEntity) as loggerEntityCount');

        //$dql->leftJoin('loggerEntity.fellowshipSubspecialty', 'fellowshipSubspecialty');
        //$dql->where("logger.entityName IS NULL OR (logger.entityName='FellowshipApplication' AND loggerEntity IS NOT NULL)"); //AND loggerEntity=2

        $dql->andWhere("logger.entityName IS NULL OR loggerEntity IS NOT NULL");

        //$dql->where("logger.entityName IS NULL OR (logger.entityName='FellowshipApplication' AND loggerEntityCount>0)");

//        $whereSelect = '(SELECT F FROM \Oleg\FellAppBundle\Entity\FellowshipApplication F '.
//            'LEFT JOIN F.fellowshipSubspecialty fellowshipSubspecialty WHERE '.
//            'F.id=logger.entityId AND fellowshipSubspecialty.id IN('.implode(",", $fellowshipTypes).'))!=""';
//        $dql->where("(logger.entityName='FellowshipApplication' AND $whereSelect)");

//        $repository = $this->getDoctrine()->getRepository('OlegFellAppBundle:FellowshipApplication');
//        $dql2 =  $repository->createQueryBuilder("fellapp");
//        $dql2->leftJoin('fellapp.fellowshipSubspecialty', 'fellowshipSubspecialty');
//
//        $dql->where(
//            $dql->expr()->in(
//                'logger.id',
//                $dql2->select('o2.id')
//                    ->from('Order', 'o2')
//                    ->join('Item',
//                        'i2',
//                        \Doctrine\ORM\Query\Expr\Join::WITH,
//                        $dql2->expr()->andX(
//                            $dql2->expr()->eq('i2.order', 'o2'),
//                            $dql2->expr()->eq('i2.id', '?1')
//                        )
//                    )
//                    ->getDQL()
//            )
//        );

        return $dql;
    }

    public function addCustomDql($dql) {
        $em = $this->getDoctrine()->getManager();

        $user = $this->get('security.context')->getToken()->getUser();
        $roleObjects = $em->getRepository('OlegUserdirectoryBundle:User')->findUserRolesBySiteAndPartialRoleName($user, 'fellapp', "ROLE_FELLAPP_");
        $fellowshipTypes = array();
        foreach ($roleObjects as $roleObject) {
            if ($roleObject->getFellowshipSubspecialty()) {
                $fellowshipTypes[] = $roleObject->getFellowshipSubspecialty()->getId() . "";  //$roleObject->getFellowshipSubspecialty()."";
                //echo "role add=" . $roleObject->getFellowshipSubspecialty()->getId() . ":" . $roleObject->getFellowshipSubspecialty()->getName() . "<br>";
            }
        }
        //echo "count=" . count($fellowshipTypes) . "<br>";

        //subquery to get a fellowship application object
        $subquery = $em->createQueryBuilder()
            ->select('fellapp.id')
            ->from('OlegFellAppBundle:FellowshipApplication', 'fellapp')
            ->leftJoin('fellapp.fellowshipSubspecialty','fellowshipSubspecialty')
            ->where('fellapp.id = logger.entityId AND fellowshipSubspecialty.id IN('.implode(",", $fellowshipTypes).')') //AND fellowshipSubspecialty.id IN(37)
            ->getDQL();
        $subquery = '('.$subquery.')';

        //main query to get logger objects
        $entityName = 'FellowshipApplication';
        $query = $em->createQueryBuilder();
        $query->select('logger');
        $query->from('OlegUserdirectoryBundle:Logger', 'logger');

        $query->andWhere("logger.entityName != '".$entityName."' OR ( logger.entityName = '".$entityName."' AND logger.entityId=".$subquery.")");
        //$query->andWhere("logger.entityName = '".$entityName."' AND logger.entityId=".$subquery);

        //$query->andWhere("logger.entityName IS NULL OR (logger.entityName='FellowshipApplication' AND loggerEntity.id IS NOT NULL)");

        return $query;
    }


    protected function listLogger_1( $params, $request ) {
        $em = $this->getDoctrine()->getManager();

//        $dql =
//            "SELECT ".
//            "logger,".
//            //"(SELECT F.id FROM OlegFellAppBundle:FellowshipApplication F) as loggerEntity ".
//            "(SELECT COUNT(F.id) FROM OlegFellAppBundle:FellowshipApplication F ". //
//            "LEFT JOIN F.fellowshipSubspecialty fellowshipSubspecialty ".
//            "WHERE F.id=logger.entityId AND fellowshipSubspecialty.id IN(37)) AS loggerEntity ".
//            "FROM OlegUserdirectoryBundle:Logger logger ". //OlegUserdirectoryBundle:Logger
//            "WHERE logger.siteName='fellapp' AND logger.entityId IS NOT NULL ".
//            "AND loggerEntity>0";
        //$query = $em->createQuery($dql);

        //////////////// 2 ///////////////////
        $subquery = $em->createQueryBuilder()
        ->select('fellapp.id')
        ->from('OlegFellAppBundle:FellowshipApplication', 'fellapp')
        ->leftJoin('fellapp.fellowshipSubspecialty','fellowshipSubspecialty')
        ->where('fellapp.id = logger.entityId AND fellowshipSubspecialty.id IN(37)') //AND fellowshipSubspecialty.id IN(37)
        ->getDQL();
        $subquery = '('.$subquery.')';

        $query = $em->createQueryBuilder();
        $query->select('logger');
        $query->from('OlegUserdirectoryBundle:Logger', 'logger');
        $query->where(
            "logger.siteName='fellapp'"
//            $query->expr()->andX(
//                $query->expr()->eq('logger.siteName', "'fellapp'"),
//                //$query->expr()->eq('logger.entityId', $subquery),
//                //$query->expr()->eq('logger.entityName', "'FellowshipApplication'")
//                $query->expr()->eq('logger.entityName', '?1'),
//                $query->expr()->orX(
//                    //$query->expr()->eq('logger.entityName', '?1'),
//                    $query->expr()->andX(
//                        $query->expr()->eq('logger.entityId', $subquery),
//                        $query->expr()->eq('logger.entityName', "'FellowshipApplication'")
//                    )
//                )
//            )
            //$query->expr()->orX(
            //    $query->expr()->eq('logger.entityId', $subquery),
            //    $query->expr()->eq('logger.entityName', "'FellowshipApplication'")
            //)
        );

        $query->andWhere("logger.entityName != :entityName OR ( logger.entityName = :entityName AND logger.entityId=".$subquery.")");

        //$query->setParameter(1, NULL);
        //$query->andWhere("logger.entityName IS NULL OR (logger.entityName='FellowshipApplication' AND loggerEntity.id IS NOT NULL)");
        $query = $query->getQuery();
        $query->setParameters(array('entityName'=>'FellowshipApplication'));
        //////////////// EOF 2 ///////////////////

        //////////////// 3 ///////////////////
        if(0) {
            $qb2 = $em->createQueryBuilder();
            $qb = $em->createQueryBuilder();
            $qb->select('logger')
                ->from('OlegUserdirectoryBundle:Logger', 'logger')
                //->join('i.order', 'o')
                ->where(
                    $qb->expr()->eq(
                        'logger.entityId',
                        '(' . $qb2->select('fellapp.id')
                            ->from('OlegFellAppBundle:FellowshipApplication', 'fellapp')
                            ->join('fellapp.fellowshipSubspecialty',
                                'fellowshipSubspecialty',
                                \Doctrine\ORM\Query\Expr\Join::WITH,
                                $qb2->expr()->in('fellowshipSubspecialty.id', '?1')
                            )
                            ->getDQL() . ')'
                    )
                )
                //->andWhere($qb->expr()->neq('i.id', '?2'))
                //->orderBy('o.orderdate', 'DESC')
                ->setParameter(1, 37)//->setParameter(2, 5)
            ;
            $query = $qb->getQuery();
        }
        //////////////// EOF 3 ///////////////////

        echo "dql=".$query->getSql()."<br>";

        //$pagination = $query->getResult();

        $paginator  = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $query,
            $this->get('request')->query->get('page', 1), /*page number*/
            300/*limit per page*/
        );
        echo "<br>pagination=".count($pagination)."<br>";

        foreach( $pagination as $logger ) {
            echo "logger entity = ". $logger->getEntityName() . " " .$logger->getEntityId() . "<br>";
            //echo "record logger = ". $logger['loggerEntity']['entityName'] . "<br>";
            //echo "loggerEntity = (". $logger['loggerEntity'] . ")<br>";
            //print_r($row);
        }
        exit('pagination exit');

        return array(
            'filterform' => $filterform,
            'loggerfilter' => $filterform->createView(),
            'pagination' => $pagination,
            'roles' => $rolesArr,
            'sitename' => $sitename,
            'createLogger' => $createLogger,
            'updateLogger' => $updateLogger,
            'filtered' => $filtered,
            'routename' => $request->get('_route'),
            'titlePostfix' => ""
        );
    }

}
