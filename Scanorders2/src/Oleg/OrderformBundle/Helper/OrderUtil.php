<?php
/**
 * Created by JetBrains PhpStorm.
 * User: oli2002
 * Date: 10/4/13
 * Time: 12:56 PM
 * To change this template use File | Settings | File Templates.
 */

namespace Oleg\OrderformBundle\Helper;


//use Oleg\OrderformBundle\Entity\OrderInfo;
//use Doctrine\Common\Collections\ArrayCollection;
use Oleg\OrderformBundle\Controller\MultyScanOrderController;
use Symfony\Component\HttpFoundation\RedirectResponse;

use Oleg\OrderformBundle\Entity\History;

class OrderUtil {

    private $em;

    public function __construct( $em ) {
        $this->em = $em;
    }

    public function changeStatus( $id, $status, $user, $swapId=null ) {

        $em = $this->em;

        $entity = $em->getRepository('OlegOrderformBundle:OrderInfo')->findOneByOid($id);

        if (!$entity) {
            throw new \Exception( 'Unable to find OrderInfo entity by id'.$id );
        }

        if( $status == 'Un-Cancel' ) {
            $statusSearch = 'Submit';
        } else {
            $statusSearch = $status;
        }

//        echo "status=".$status."<br>";
        $status_entity = $em->getRepository('OlegOrderformBundle:Status')->findOneByAction($statusSearch);
//        echo "status_entity=".$status_entity->getName()."<br>";
//        //exit();

        if( $status_entity ) {

            //record history
            $history = new History();
            $history->setOrderinfo($entity);
            $history->setCurrentid($entity->getOid());
            $history->setCurrentstatus($entity->getStatus());
            $history->setProvider($user);

            foreach( $user->getRoles() as $role ) {
                //echo "Role=".$role."<br>";
                $history->addRole($role."");
            }

            //change status for all orderinfo children to "deleted-by-canceled-order"
            //IF their source is ="scanorder" AND there are no child objects with status == 'valid'
            //AND there are no fields that belong to this object that were added by another order
            if( $status == 'Cancel' ) {

                $fieldStatusStr = "deleted-by-canceled-order";

                if( $entity->getProvider() == $user || $user->hasRole("ROLE_ORDERING_PROVIDER") || $user->hasRole("ROLE_EXTERNAL_ORDERING_PROVIDER") ) {
                    $status_entity = $em->getRepository('OlegOrderformBundle:Status')->findOneByName("Canceled by Submitter");
                } else
                if( $user->hasRole("ROLE_ADMIN") || $user->hasRole("ROLE_PROCESSOR") ) {
                    $status_entity = $em->getRepository('OlegOrderformBundle:Status')->findOneByAction("Canceled by Processor");
                } else {
                    $status_entity = $em->getRepository('OlegOrderformBundle:Status')->findOneByName("Canceled by Submitter");
                }

                $entity->setStatus($status_entity);
                $message = $this->processObjects( $entity, $status_entity, $fieldStatusStr );
                //$entity->setOid($entity->getId()."-c");

                //record history
                $history->setNewid($entity->getOid());
                $history->setNewstatus($entity->getStatus());

                $em->persist($entity);
                $em->persist($history);
                $em->flush();
                $em->clear();

            } else if( $status == 'Supersede' ) {

                //$status_entity = $em->getRepository('OlegOrderformBundle:Status')->findOneByName("Superseded");
                $fieldStatusStr = "deleted-by-amended-order";
                $entity->setStatus($status_entity);
                $message = $this->processObjects( $entity, $status_entity, $fieldStatusStr );
                $entity->setOid($swapId);
                //$entity->setCicle("superseded");

                //record history
                $history->setNewid($entity->getOid());
                $history->setNewstatus($entity->getStatus());

                $em->persist($entity);
                $em->persist($history);
                $em->flush();
                //$em->clear();

            } else if( $status == 'Un-Cancel' ) {  //this is un-cancel action

                //1) clone orderinfo object
                //2) validate MRN-Accession
                //3) change status to 'valid' and 'submit'

//                echo "<br><br>newOrderinfo Patient's count=".count($entity->getPatient())."<br>";
//                echo $entity;
//                foreach( $entity->getPatient() as $patient ) {
//                    echo "<br>--------------------------<br>";
//                    $em->getRepository('OlegOrderformBundle:OrderInfo')->printTree( $patient );
//                    echo "--------------------------<br>";
//                }
//                //exit();

                //VALIDATION Accession-MRN
                $conflict = false;
                foreach( $entity->getAccession() as $accession ) {
                    $patient = $accession->getParent()->getParent();

                    $patientKey = $patient->obtainValidKeyField();
                    if( !$patientKey ) {
                        throw new \Exception( 'Object does not have a valid key field. Object: '.$patient );
                    }

                    $accessionKey = $accession->obtainValidKeyField();
                    if( !$accessionKey ) {
                        throw new \Exception( 'Object does not have a valid key field. Object: '.$accession );
                    }

                    //echo "accessionKey=".$accessionKey."<br>";
                    $accessionDb = $em->getRepository('OlegOrderformBundle:Accession')->findOneByIdJoinedToField($accessionKey,"Accession","accession",true, true);

                    $mrn = $patientKey; //mrn
                    $mrnTypeId = $patientKey->getMrntype()->getId();
                    //$extra = $patientKey->obtainExtraKey();

                    if( $accessionDb ) {
                        //echo "similar accession found=".$accessionDb;
                        $patientDb = $accessionDb->getParent()->getParent();
                        if( $patientDb ) {
                            $mrnDb = $patientDb->obtainValidKeyField();
                            $mrnTypeIdDb = $mrnDb->getMrntype()->getId();

                            //echo $mrn . "?=". $mrnDb ." && ". $mrnTypeId . "==". $mrnTypeIdDb . "<br>";

                            if( $mrn == $mrnDb && $mrnTypeId == $mrnTypeIdDb ) {
                                //ok
                                //echo "no conflict <br>";
                            } else {
                                //echo "there is a conflict <br>";
                                //conflict => render the orderinfo in the amend view 'order_amend'
                                //exit('un-canceling order. id='.$newOrderinfo->getOid());
                                $conflict = true;
                            }
                        }
                    }

                }

                if( $conflict ) {

                    $res = array();
                    $res['result'] = 'conflict';
                    $res['oid'] = $entity->getOid();

                    return $res;

                } else {
                     //exit("un-cancel no conflict! <br>");
                    //if no conflict: change status without creating new order
                    //record new history for modifying Superseded Order
                    $entity->setStatus($status_entity);
                    $message = $this->processObjects( $entity, $status_entity, 'valid' );
                    $history->setNewid($entity->getOid());
                    $history->setNewstatus($entity->getStatus());

                    $em->persist($entity);
                    $em->persist($history);
                    $em->flush();
                    $em->clear();

                }

            } else {
                //exit('regular xit');
                //throw new \Exception( 'Status '.$status.' can not be processed' );
                $entity->setStatus($status_entity);

                //record history
                $history->setNewid($entity->getOid());
                $history->setNewstatus($entity->getStatus());

                $em->persist($entity);
                $em->persist($history);
                $em->flush();
                $em->clear();

            }

            $message = 'Status of Order #'.$id.' has been changed to "'.$status.'"'.$message;

        } else {
            //$message = 'Status: "'.$status.'" is not found';
            throw new \Exception( 'Status '.$status.' can not be processed' );
        }

        $res = array();
        $res['result'] = 'ok';
        $res['message'] = $message;

        return $res;
    }

    public function makeOrderInfoClone( $entity, $status_entity, $statusStr ) {

        $em = $this->em;

        if( !$status_entity  ) {
            $status_entity = $em->getRepository('OlegOrderformBundle:Status')->findOneByAction('Submit');
        }

        //CLONING
        $oid = $entity->getOid();
        $oidArr = explode("-", $oid);
        $originalId = $oidArr[0];

        $newOrderinfo = clone $entity;

        $em->detach($entity);
        $em->detach($newOrderinfo);

        $newOrderinfo->setStatus($status_entity);
        //$newOrderinfo->setCicle('submit');
        $newOrderinfo->setOid($originalId);

        //$newOrderinfo = $this->iterateOrderInfo( $newOrderinfo, $statusStr );

        //change status to valid
        $message = $this->processObjects( $newOrderinfo, $status_entity, $statusStr );

        $res = array();
        $res['message'] = $message;
        $res['orderinfo'] = $newOrderinfo;

        return $res;
    }

    public function processObjects( $entity, $status_entity, $statusStr ) {

        $patients = $entity->getPatient();
        $patCount = $this->iterateEntity( $entity, $patients, $status_entity, $statusStr );

        $procedures = $entity->getProcedure();
        $procCount = $this->iterateEntity( $entity, $procedures, $status_entity, $statusStr );

        $accessions = $entity->getAccession();
        $accCount = $this->iterateEntity( $entity, $accessions, $status_entity, $statusStr );

        $parts = $entity->getPart();
        $partCount = $this->iterateEntity( $entity, $parts, $status_entity, $statusStr );

        $blocks = $entity->getBlock();
        $blockCount = $this->iterateEntity( $entity, $blocks, $status_entity, $statusStr );

        $slides = $entity->getSlide();
        $slideCount = $this->iterateEntity( $entity, $slides, $status_entity, $statusStr );

        return " (changed children: patients ".$patCount.", procedures ".$procCount.", accessions ".$accCount.", parts ".$partCount.", blocks ".$blockCount." slides ".$slideCount.")";
    }

    public function iterateEntity( $orderinfo, $children, $status_entity, $statusStr ) {

        $em = $this->em;

        if( !$children->first() ) {
            return 0;
        }

        //echo "iterate children count=".count($children)."<br>";

        $class = new \ReflectionClass($children->first());
        $className = $class->getShortName();
        //echo "class name=".$className."<br>";

        $count = 0;

        foreach( $children as $child ) {

            $noOtherOrderinfo = true;

            //echo "orderinfo count=".count($child->getOrderinfo()).", order id=".$child->getOrderinfo()->first()->getId()."<br>";

            //check if this object is used by any other orderinfo (for cancel and amend only)
            if( $statusStr != 'valid' ) {
                foreach( $child->getOrderinfo() as $order ) {
                    //echo "orderinfo id=".$order->getId().", oid=".$order->getOid()."<br>";
                    if( $orderinfo->getId() != $order->getId() ) {  //&& $order->getStatus() != $status_entity->getId()  ) {
                        $noOtherOrderinfo = false;
                        break;
                    }
                }
            }

            //echo "noOtherOrderinfo=".$noOtherOrderinfo."<br>";

            if( $child->getSource() == 'scanorder' && $noOtherOrderinfo ) {
                //echo "change status to (".$statusStr.") <br>";
                $child->setStatus($statusStr);
                $em->getRepository('OlegOrderformBundle:'.$className)->processFieldArrays($child,null,null,$statusStr);
                $count++;
            }

        }

        return $count;
    }


    public function getNotViewedComments($security_context)
    {
        $repository = $this->em->getRepository('OlegOrderformBundle:History');
        $dql =  $repository->createQueryBuilder('history');
        //$dql->select('COUNT(history) as historycount');
        $dql->select('history');
        //$dql->groupBy('history');
        $dql->innerJoin("history.provider", "provider");
        $dql->innerJoin("history.orderinfo", "orderinfo");
        $dql->innerJoin("orderinfo.provider", "orderinfo_provider");
        $dql->leftJoin("orderinfo.proxyuser", "orderinfo_proxyuser");
        $role = "ROLE_PROCESSOR";
        $role2 = "ROLE_ADMIN";
        $user = $security_context->getToken()->getUser();
        $criteriastr = 'history.viewed is NULL';

        if( $security_context->isGranted('ROLE_PROCESSOR') ) {
            //processor can see all histories created by user without processor role
            $criteriastr = $criteriastr . " AND history.roles NOT LIKE :role AND history.roles NOT LIKE :role2";
        } else {
            //submitter can see only histories created by user with processor or admin role for history's orders belongs to this user as provider or proxy
            $criteriastr = $criteriastr . " AND ( history.roles LIKE :role OR history.roles LIKE :role2 )";
            $criteriastr = $criteriastr . " AND ( orderinfo_provider = :provider OR orderinfo_proxyuser = :provider )";
        }

        $dql->where($criteriastr);
        //$dql->addGroupBy('history.changedate');
        $dql->addOrderBy("history.changedate","DESC");
        $query = $dql->getQuery()->setParameter('role', '%"'.$role.'"%')->setParameter('role2', '%"'.$role2.'"%');

        if( false === $security_context->isGranted('ROLE_PROCESSOR') ) {
            $query->setParameter('provider', $user);
        }

        $entities = $query->getResult();

        return $entities;
    }

}