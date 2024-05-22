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

use Symfony\Component\DependencyInjection\ContainerInterface;
//use Doctrine\ORM\Event\LifecycleEventArgs;
//use Doctrine\ORM\Event\OnFlushEventArgs;
//use Doctrine\ORM\Event\PostFlushEventArgs;
use Doctrine\ORM\Event\PostPersistEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;

//use App\FellAppBundle\Entity\FellowshipApplication;
//use App\UserdirectoryBundle\Entity\AdministrativeTitle;
//use App\UserdirectoryBundle\Entity\CompositeNodeInterface;
use App\OrderformBundle\Entity\Message;
use App\OrderformBundle\Entity\PatientLastName;
use App\OrderformBundle\Entity\PatientFirstName;
use App\OrderformBundle\Entity\PatientMiddleName;

use App\TranslationalResearchBundle\Entity\AntibodyList;

class DoctrineListener {


    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }


    //create new entity
    //public function postPersist(LifecycleEventArgs $args)
    public function postPersist(PostPersistEventArgs $args)
    {
        //$entity = $args->getEntity();
        $entity = $args->getObject();
        //$em = $args->getEntityManager();
        $em = $args->getObjectManager();
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

        if( $this->setMetaphoneField($entity) ) {
            $em->flush();
        }

//        if( $entity instanceof FellowshipApplication ) {
//            //update report
//            $fellappRepGen = $this->container->get('fellapp_reportgenerator');
//            $fellappRepGen->addFellAppReportToQueue( $entity->getId() );
//        }

        if( $this->setTrabsferable($entity) ) {
            $em->flush();
        }


    }


    public function preUpdate( PreUpdateEventArgs $args )
    {
        //exit("DoctrineListener->preUpdate");
        //$entity = $args->getEntity();
        $entity = $args->getObject();

        //$logger = $this->container->get('logger');
        //$logger->notice("doctrine listener preUpdate: ".get_class($entity));

        $this->setMetaphoneField($entity);

        $this->setTrabsferable($entity);
    }

    public function setMetaphoneField( $entity ) {

        if( !$entity instanceof PatientFirstName ) {
            return false;
        }
        if( !$entity instanceof PatientLastName ) {
            return false;
        }
        if( !$entity instanceof PatientMiddleName ) {
            return false;
        }

        //$logger = $this->container->get('logger');
        //if( $entity instanceof PatientLastName ) {
        if( method_exists($entity, 'setFieldMetaphone') ) {
            $userServiceUtil = $this->container->get('user_service_utility');
            $metaphone = $userServiceUtil->getMetaphoneKey($entity->getField());
            $entity->setFieldMetaphone($metaphone);
            //$logger->notice("setFieldMetaphone [ID# " . $entity->getId() . "]:" . $entity->getField() . "=>" . $metaphone);
            //return $metaphone;
            return true;
        }
        return false;
    }

    public function setTrabsferable($entity) {
        if( $entity instanceof AntibodyList ) {
            $interfaceTransferUtil = $this->container->get('interface_transfer_utility');

            //1) find if TransferData has this antibody with status 'Ready'
            if( $interfaceTransferUtil->findTransferData($entity,'Ready') ) {
                return false; //do nothing
            }

            //2 add antibody to the TransferData table

            return true;
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