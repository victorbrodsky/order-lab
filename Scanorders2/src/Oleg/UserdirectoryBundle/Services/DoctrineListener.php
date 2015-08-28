<?php
/**
 * Created by PhpStorm.
 * User: oli2002
 * Date: 10/16/14
 * Time: 9:55 AM
 */

namespace Oleg\UserdirectoryBundle\Services;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Oleg\FellAppBundle\Entity\FellowshipApplication;
use Oleg\OrderformBundle\Entity\Message;
use Oleg\UserdirectoryBundle\Entity\AdministrativeTitle;
use Oleg\UserdirectoryBundle\Entity\CompositeNodeInterface;

class DoctrineListener {


    private $container;

    public function __construct($container)
    {
        $this->container = $container;
    }


    public function postPersist(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();
        $em = $args->getEntityManager();

        if( $entity instanceof Message ) {

            if( !$entity->getOid() ) {

                //echo "listener: insert oid <br>";
                //echo "listener: id=".$entity->getId()."<br>";

                $entity->setOid( $entity->getId() );
                //echo "listener: entity=".$entity."<br>";

                $em->flush();
            }

        }


        if( $entity instanceof FellowshipApplication ) {
            //update report
            $fellappRepGen = $this->container->get('fellapp_reportgenerator');
            $fellappRepGen->addFellAppReportToQueue( $entity->getId() );
        }


    }


//    public function postUpdate(LifecycleEventArgs $args)
//    {
//
//        //$em = $args->getEntityManager();
//        $entity = $args->getEntity();
//
//        if( $entity instanceof FellowshipApplication ) {
//            //update report
//            $fellappUtil = $this->container->get('fellapp_util');
//            $fellappUtil->addFellAppReportToQueue( $entity->getId() );
//        }
//
//    }
//
//
//    public function onFlush(OnFlushEventArgs $args)
//    {
//
//        $em = $args->getEntityManager();
//        $uow = $em->getUnitOfWork();
//
//        foreach ($uow->getScheduledEntityUpdates() as $entity) {
//
//            if( $entity instanceof FellowshipApplication ) {
//                //update report
//                $fellappUtil = $this->container->get('fellapp_util');
//                $fellappUtil->addFellAppReportToQueue( $entity->getId() );
//            }
//
//        }
//
//    }
//
//
//    public function postFlush(PostFlushEventArgs $args)
//    {
//
//        $em = $args->getEntityManager();
//
////        $entity = $args->getEntity();
////        if( $entity instanceof FellowshipApplication ) {
////            //update report
////            $fellappUtil = $this->container->get('fellapp_util');
////            $fellappUtil->addFellAppReportToQueue( $entity->getId() );
////        }
//
//        foreach( $em->getUnitOfWork()->getScheduledEntityDeletions() as $entity ) {
//            if( $entity instanceof FellowshipApplication ) {
//                //update report
//                $fellappUtil = $this->container->get('fellapp_util');
//                $fellappUtil->addFellAppReportToQueue( $entity->getId() );
//            }
//        }
//
//    }


} 