<?php
/**
 * Created by PhpStorm.
 * User: oli2002
 * Date: 10/16/14
 * Time: 9:55 AM
 */

namespace Oleg\UserdirectoryBundle\Services;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Oleg\OrderformBundle\Entity\Message;
use Oleg\UserdirectoryBundle\Entity\AdministrativeTitle;
use Oleg\UserdirectoryBundle\Entity\CompositeNodeInterface;

class DoctrineListener {

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

    }



} 