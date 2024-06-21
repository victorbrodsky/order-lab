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

use App\TranslationalResearchBundle\Entity\Project;
use App\UserdirectoryBundle\Entity\InterfaceTransferList;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Doctrine\ORM\Event\PostUpdateEventArgs;
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

        if( $this->setTrabsferable($entity,$event='create') ) {
            //exit('postPersist');
            $em->flush();
        }


    }


    //preUpdate is firing only if there is a change in the entity
    public function preUpdate( PreUpdateEventArgs $args )
    {
        //exit("DoctrineListener->preUpdate");
        //$entity = $args->getEntity();
        $entity = $args->getObject();

        //$logger = $this->container->get('logger');
        //$logger->notice("doctrine listener preUpdate: ".get_class($entity));

        $this->setMetaphoneField($entity);

//        if( $this->setTrabsferable($entity) ) {
//            $em = $args->getObjectManager();
//            $em->flush();
//            //exit('preUpdate: setTrabsferable yes');
//        }
    }

    public function postUpdate( PostUpdateEventArgs  $args )
    {
        //exit("DoctrineListener->postUpdate");
        $em = $args->getObjectManager();
        $entity = $args->getObject();

        //exit("DoctrineListener->postUpdate: "."classname=".get_class($entity));

        //$logger = $this->container->get('logger');
        //$logger->notice("doctrine listener postUpdate: ".get_class($entity));

        if( $this->setTrabsferable($entity, $event='update') ) {
            $em->flush();
            //exit('postUpdate');
        }
    }

//    public function postFlush(PostFlushEventArgs $args) {
//        $entity = $args->getObjectManager();
//        $em = $args->getObjectManager();
//        //exit("DoctrineListener->postFlush: "."classname=".get_class($entity));
//
//        if( $this->setTrabsferable($entity) ) {
//            $em->flush();
//            exit('postFlush');
//        }
//    }

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

    public function setTrabsferable( $entity, $event ) {
        //return false;

        //echo "classname=".get_class($entity)."<br>";
        $logger = $this->container->get('logger');
        $logger->notice("classname=".get_class($entity));

        if( $entity instanceof AntibodyList ) {
            //exit('setTrabsferable, ID='.$entity->getId());
            $logger->notice('setTrabsferable, AntibodyList ID='.$entity->getId());

            $interfaceTransferUtil = $this->container->get('interface_transfer_utility');

            //make sure it does not fired on the slave (remote) server
            $masterTransferServer = $interfaceTransferUtil->isMasterTransferServer($entity);
            if( !$masterTransferServer ) {
                $logger->notice('setTrabsferable: AntibodyList: is not masterTransferServer');
                return false;
            }

            if( $entity instanceof AntibodyList ) {
                //check if public
                if ($entity->getOpenToPublic() !== true) {
                    //exit('not public');
                    return false;
                }
            }

            //2) find if TransferData has this object with status 'Ready'
            $transferData = $interfaceTransferUtil->findCreateTransferData($entity);

            if( $transferData ) {
                return true;
            } else {
                //exit('createTransferData Antibody failed');
            }
        } else {
            //exit('not AntibodyList');
        }

        //Only create new project for now
        if( $entity instanceof Project ) {

            //TODO: add project to TransferData if status changed from draft to irb_review
            //if( $event !== 'create' ) {
            //    return false;
            //}

            //exit('setTrabsferable, ID='.$entity->getId());
            $logger->notice('setTrabsferable, Project ID='.$entity->getId());

            //if not 'irb_review'
            if( $entity->getState() == 'draft' ) {
                return false;
            }

            $interfaceTransferUtil = $this->container->get('interface_transfer_utility');

            //Remove for Project, if Project should be sync both ways
            //1) make sure it does not fired on the internal (master) server
//            $masterTransferServer = $interfaceTransferUtil->isMasterTransferServer($entity);
//            if( $masterTransferServer ) {
//                $logger->notice('setTrabsferable: Project: is masterTransferServer, however, for now, Project should be transferred from Slave to Master');
//                return false;
//            }
            $interfaceTransfer = $interfaceTransferUtil->getInterfaceTransferByEntity($entity);
            if( $interfaceTransfer ) {
                if( $interfaceTransfer->getTransferSource() ) {
                    $logger->notice(
                        'setTrabsferable: Project should be transferred from external (slave, source) to internal (master, destination),'.
                        ' therefore, do not add to TransferData if the source is set');
                    return false;
                }
            }
            //exit('doctrine listener');

            //2) find if TransferData has this object with status 'Ready'
            $transferData = $interfaceTransferUtil->findCreateTransferData($entity);

            if( $transferData ) {
                return true;
            } else {
                //exit('createTransferData Project failed');
            }
        }

        //exit('EOF setTrabsferable');
        return false;
    }

    public function setTrabsferable_ORIG($entity) {

        //echo "classname=".get_class($entity)."<br>";
        $logger = $this->container->get('logger');
        $logger->notice("classname=".get_class($entity));

        if( $entity instanceof AntibodyList ) {
            //exit('setTrabsferable, ID='.$entity->getId());
            $logger->notice('setTrabsferable, ID='.$entity->getId());

            $interfaceTransferUtil = $this->container->get('interface_transfer_utility');

            //make sure it does not fired on the slave (remote) server
            $masterTransferServer = $interfaceTransferUtil->isMasterTransferServer($entity);
            if( !$masterTransferServer ) {
                $logger->notice('setTrabsferable: Is not masterTransferServer');
                return false;
            }

            if( $entity instanceof AntibodyList ) {
                //check if public
                if ($entity->getOpenToPublic() !== true) {
                    //exit('not public');
                    return false;
                }
            }

            //1) find if TransferData has this antibody with status 'Ready'
            if( $interfaceTransferUtil->findTransferData($entity,'Ready') ) {
                //exit('Already in TransferData');
                $logger->notice('setTrabsferable: Already in TransferData');
                return false; //do nothing
            }

            //exit('before createTransferData');
            //2 add antibody to the TransferData table
            $transfer = $interfaceTransferUtil->createTransferData($entity,$status='Ready');
            //exit('after createTransferData');

            if( $transfer ) {
                return true;
            } else {
                //exit('createTransferData failed');
            }
        } else {
            //exit('not AntibodyList');
        }

        if( $entity instanceof Project ) {
            //exit('setTrabsferable, ID='.$entity->getId());
            $logger->notice('setTrabsferable, ID='.$entity->getId());

            $interfaceTransferUtil = $this->container->get('interface_transfer_utility');

            //Remove for Project, if Project should be sync both ways
            //make sure it does not fired on the slave (remote) server
//            $masterTransferServer = $interfaceTransferUtil->isMasterTransferServer($entity);
//            if( $masterTransferServer ) {
//                $logger->notice('setTrabsferable: is masterTransferServer, however, for now, Project should be transferred from Slave to Master');
//                return false;
//            }
            $interfaceTransfer = $interfaceTransferUtil->getInterfaceTransferByEntity($entity);
            if( $interfaceTransfer ) {
                if( $interfaceTransfer->getTransferSource() ) {
                    $logger->notice(
                        'setTrabsferable: Project should be transferred from external (slave, source) to internal (master, destination),'.
                        ' therefore, do not add to TransferData if the source is set');
                    return false;
                }
            }
            //exit('doctrine listener');

            //1) find if TransferData has this antibody with status 'Ready'
            if( $interfaceTransferUtil->findTransferData($entity,'Ready') ) {
                //exit('Already in TransferData');
                $logger->notice('setTrabsferable: Already in TransferData');
                return false; //do nothing
            }

            //exit('before createTransferData');
            //2 add antibody to the TransferData table
            $transfer = $interfaceTransferUtil->createTransferData($entity,$status='Ready');
            //exit('after createTransferData');

            if( $transfer ) {
                return true;
            } else {
                //exit('createTransferData failed');
            }
        }

        //exit('EOF setTrabsferable');
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