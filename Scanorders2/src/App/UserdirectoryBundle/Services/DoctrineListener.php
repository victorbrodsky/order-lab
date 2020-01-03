<?php
/**
 * Copyright (c) 2017 Cornell University
 *
 *  Licensed under the Apache License, Version 2.0 (the "License");
 *  you may not use this file except in compliance with the License.
 *  You may obtain a copy of the License at
 *
 *  http://www.apache.org/licenses/LICENSE-2.0
 *
 *  Unless required by applicable law or agreed to in writing, software
 *  distributed under the License is distributed on an "AS IS" BASIS,
 *  WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 *  See the License for the specific language governing permissions and
 *  limitations under the License.
 */

/**
 * Created by PhpStorm.
 * User: oli2002
 * Date: 10/16/14
 * Time: 9:55 AM
 */

namespace App\UserdirectoryBundle\Services;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use App\FellAppBundle\Entity\FellowshipApplication;
use App\OrderformBundle\Entity\Message;
use App\OrderformBundle\Entity\PatientLastName;
use App\UserdirectoryBundle\Entity\AdministrativeTitle;
use App\UserdirectoryBundle\Entity\CompositeNodeInterface;

class DoctrineListener {


    private $container;

    public function __construct($container)
    {
        $this->container = $container;
    }


    //create new entity
    public function postPersist(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();
        $em = $args->getEntityManager();
        //$logger = $this->container->get('logger');
        //$logger->notice("doctrine listener postPersist: ".get_class($entity));

        if( $entity instanceof Message ) {

            if( !$entity->getOid() ) {

                //echo "listener: insert oid <br>";
                //echo "listener: id=".$entity->getId()."<br>";

                $entity->setOid( $entity->getId() );
                //echo "listener: entity=".$entity."<br>";

                $em->flush();
            }

        }

        //if( $entity instanceof PatientLastName ) {
        //}
        if( $this->setMetaphoneField($entity,true) ) {
            $em->flush();
        }

//        if( $entity instanceof FellowshipApplication ) {
//            //update report
//            $fellappRepGen = $this->container->get('fellapp_reportgenerator');
//            $fellappRepGen->addFellAppReportToQueue( $entity->getId() );
//        }


    }


    public function preUpdate( PreUpdateEventArgs $args )
    {
        $entity = $args->getEntity();

        //$logger = $this->container->get('logger');
        //$logger->notice("doctrine listener preUpdate: ".get_class($entity));

        $this->setMetaphoneField($entity);

    }

    public function setMetaphoneField( $entity ) {
        //$logger = $this->container->get('logger');
        //if( $entity instanceof PatientLastName ) {
        if( method_exists($entity, 'setFieldMetaphone') ) {
            $userServiceUtil = $this->container->get('user_service_utility');
            $metaphone = $userServiceUtil->getMetaphoneKey($entity->getField());
            $entity->setFieldMetaphone($metaphone);
            //$logger->notice("setFieldMetaphone [ID# " . $entity->getId() . "]:" . $entity->getField() . "=>" . $metaphone);
            return $metaphone;
        }
        return false;
    }

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