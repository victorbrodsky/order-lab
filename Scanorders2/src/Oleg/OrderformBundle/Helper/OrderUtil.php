<?php
/**
 * Created by JetBrains PhpStorm.
 * User: oli2002
 * Date: 10/4/13
 * Time: 12:56 PM
 * To change this template use File | Settings | File Templates.
 */

namespace Oleg\OrderformBundle\Helper;


class OrderUtil {

    private $em;

    public function __construct( $em ) {
        $this->em = $em;
    }

    public function changeStatus($id, $status ) {

        $em = $this->em;

        $entity = $em->getRepository('OlegOrderformBundle:OrderInfo')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find OrderInfo entity.');
        }

        //check if user permission

        //$editForm = $this->createForm(new OrderInfoType(), $entity);
        //$deleteForm = $this->createDeleteForm($id);

        //$entity->setStatus($status);
        //echo "status=".$status."<br>";
        $status_entity = $em->getRepository('OlegOrderformBundle:Status')->findOneByAction($status);

        if( $status_entity ) {

            $entity->setStatus($status_entity);

            //change status for all orderinfo children to "deleted-by-canceled-order"
            //IF their source is ="scanorder" AND there are no child objects with status == 'valid'
            //AND there are no fields that belong to this object that were added by another order
            if( $status == 'Cancel' ) {
                $statusStr = "deleted-by-canceled-order";
            } else if( $status == 'Amend' ) {
                $statusStr = "deleted-by-amended-order";
            } else if( $status == 'Submit' ) {
                $statusStr = "valid";
            } else {
                $statusStr = null;
            }

            //echo "statusStr=".$statusStr."<br>";
            $message = "";

            if( $statusStr ) {

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

                $message = " (changed children: patients ".$patCount.", procedures ".$procCount.", accessions ".$accCount.", parts ".$partCount.", blocks ".$blockCount." slides ".$slideCount.")";

            }
            //exit("exit status testing");

            $em->persist($entity);
            $em->flush();

            $message = 'Status of Order #'.$id.' has been changed to "'.$status.'"'.$message;

        } else {
            $message = 'Status: "'.$status.'" is not found';
        }

        return $message;
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

            //echo "orderinfo count=".count($child->getOrderinfo())."<br>";

            foreach( $child->getOrderinfo() as $order ) {
                if( $orderinfo->getId() != $order->getId() && $order->getStatus()->getId() != $status_entity->getId()  ) {
                    $noOtherOrderinfo = false;
                    break;
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

}