<?php
/**
 * Created by JetBrains PhpStorm.
 * User: oli2002
 * Date: 10/4/13
 * Time: 12:56 PM
 * To change this template use File | Settings | File Templates.
 */

namespace Oleg\OrderformBundle\Helper;


use Oleg\OrderformBundle\Entity\OrderInfo;
use Doctrine\Common\Collections\ArrayCollection;

class OrderUtil {

    private $em;

    public function __construct( $em ) {
        $this->em = $em;
    }

    public function changeStatus( $id, $status ) {

        $em = $this->em;

        $entity = $em->getRepository('OlegOrderformBundle:OrderInfo')->findOneByOid($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find OrderInfo entity.');
        }

        //check if user permission

        //$editForm = $this->createForm(new OrderInfoType(), $entity);
        //$deleteForm = $this->createDeleteForm($id);

        //$entity->setStatus($status);
        echo "status=".$status."<br>";
        $status_entity = $em->getRepository('OlegOrderformBundle:Status')->findOneByAction($status);
        //echo "status_entity=".$status_entity->getName()."<br>";
        //exit();

        if( $status_entity ) {

            //change status for all orderinfo children to "deleted-by-canceled-order"
            //IF their source is ="scanorder" AND there are no child objects with status == 'valid'
            //AND there are no fields that belong to this object that were added by another order
            if( $status == 'Cancel' ) {

                $statusStr = "deleted-by-canceled-order";
                $entity->setStatus($status_entity);
                $message = $this->processObjects( $entity, $status_entity, $statusStr );
                $entity->setOid($entity->getOid()."-del");
                $entity->setCicle($statusStr);
                $em->persist($entity);
                $em->flush();

            } else if( $status == 'Amend' ) {

                $statusStr = "deleted-by-amended-order";
                $entity->setStatus($status_entity);
                $message = $this->processObjects( $entity, $status_entity, $statusStr );
                $entity->setOid($entity->getOid()."-del");
                $entity->setCicle($statusStr);
                $em->persist($entity);
                $em->flush();

            } else if( $status == 'Submit' ) {

                $statusStr = "valid";

                //1) clone orderinfo object
                //2) validate MRN-Accession
                //3) change status to 'valid' and 'submit'

                $oid = $entity->getOid();
                $oidArr = explode("-del", $oid);
                $originalId = $oidArr[0];

                $newOrderinfo = clone $entity;

                //$em->detach($entity);
                $em->detach($newOrderinfo);
                //$em->persist($newOrderinfo);
                //$em->clear();
                //$em->flush();

                $newOrderinfo->setStatus($status_entity);
                $newOrderinfo->setCicle('submit');
                $newOrderinfo->setOid($originalId);

                //$newOrderinfo = $this->iterateOrderInfo( $newOrderinfo, $statusStr );

                //exit("order util exit on submit");

                //change status to valid
                $message = $this->processObjects( $newOrderinfo, $status_entity, $statusStr );

                echo "<br><br>newOrderinfo Patient's count=".count($newOrderinfo->getPatient())."<br>";
                echo $newOrderinfo;
                foreach( $newOrderinfo->getPatient() as $patient ) {
                    echo "<br>--------------------------<br>";
                    $em->getRepository('OlegOrderformBundle:OrderInfo')->printTree( $patient );
                    echo "--------------------------<br>";
                }

                echo "@@@@@@@@@@ count Patient=".count($newOrderinfo->getPatient())."<br>";
                echo "count Procedure=".count($newOrderinfo->getProcedure())."<br>";
                echo "count Accession=".count($newOrderinfo->getAccession())."<br>";
                echo "count Part=".count($newOrderinfo->getPart())."<br>";
                foreach( $newOrderinfo->getPart() as $part ) {
                    $accession = $part->getAccession();
                    echo "acc part count=".count($accession->getPart())."<br>";
                    echo $part;
                }
                echo "count Block=".count($newOrderinfo->getBlock())."<br>";
                echo "count Slide=".count($newOrderinfo->getSlide())."<br>";

                $newOrderinfo = $em->getRepository('OlegOrderformBundle:OrderInfo')->processOrderInfoEntity( $newOrderinfo );

            } else {

                throw new \Exception( 'Status '.$status.' can not be processed' );

            }

            $message = 'Status of Order #'.$id.' has been changed to "'.$status.'"'.$message;

        } else {
            $message = 'Status: "'.$status.'" is not found';
        }

        return $message;
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

            if( $statusStr != 'valid' ) {
                //check if this object is used by another orderinfo (for cancel and amend only)
                foreach( $child->getOrderinfo() as $order ) {
                    echo "orderinfo id=".$order->getId().", oid=".$order->getOid()."<br>";
                    if( $orderinfo->getId() != $order->getId() && $order->getStatus()->getId() != $status_entity->getId()  ) {
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
                //set ID to null if status is valid (un-cancel procedure)
                if( $statusStr == 'valid' ) {

                    //$newChild = clone $child;

                    //$child->setId(null);

//                    echo "orderinfo count=".count($child->getOrderinfo())."<br>";
//                    foreach( $child->getOrderinfo() as $oi ) {
//                        echo $oi;
//                        $child->removeOrderinfo($oi);
//                        $child->addOrderinfo($orderinfo);
//                    }

                    //remove from orderinfo
//                    $childClass = new \ReflectionClass($child);
//                    $childClassName = $childClass->getShortName();
//                    $removeMethod = "remove".$childClassName;
//                    $addMethod = "add".$childClassName;
//                    $getMethod = "get".$childClassName;
//                    echo "child count in orderinfo=".count($orderinfo->$getMethod())."<br>";
//                    foreach( $orderinfo->$getMethod() as $orderchild ) {
//                        echo $orderchild;
//                        $orderinfo->$removeMethod($orderchild);
//                    }
                    //echo $child;
                    //$orderinfo->$addMethod($child);

                    //echo "removing ".$childClassName." from orderinfo oid=".$orderinfo->getOid().", id=".$orderinfo->getId()."<br>";
                    //$orderinfo->$removeMethod($child);

                    //$child->removeOrderInfo($orderinfo);
                    //$newChild->clearOrderinfo();

                    //$em->persist($child);                   
                    //$em->detach($child);
                    //$em->persist($child);
                }
                $count++;
            }

        }

        return $count;
    }

}