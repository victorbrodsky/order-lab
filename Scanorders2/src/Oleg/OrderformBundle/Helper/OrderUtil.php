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

    public function changeStatus( $id, $status ) {

        $em = $this->em;

        $entity = $em->getRepository('OlegOrderformBundle:OrderInfo')->find($id);

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

            $entity->setStatus($status_entity);

            //change status for all orderinfo children to "deleted-by-canceled-order"
            //IF their source is ="scanorder" AND there are no child objects with status == 'valid'
            //AND there are no fields that belong to this object that were added by another order
            if( $status == 'Cancel' ) {

                $statusStr = "deleted-by-canceled-order";
                $message = $this->processObjects( $entity, $status_entity, $statusStr );
                $em->persist($entity);
                $em->flush();

            } else if( $status == 'Amend' ) {

                $statusStr = "deleted-by-amended-order";
                $message = $this->processObjects( $entity, $status_entity, $statusStr );
                $em->persist($entity);
                $em->flush();

            } else if( $status == 'Submit' ) {

                $statusStr = "valid";

                //1) clone orderinfo object
                //2) validate MRN-Accession
                //3) change status to 'valid' and 'submit'

                $originalId = $entity->getId();

                $newOrderinfo = clone $entity;
                //$em->detach($entity);
                $em->detach($newOrderinfo);                            
                
                echo "<br><br>orig entity1##########: final patients count=".count($entity->getPatient())."<br>";
                foreach( $entity->getPatient() as $patient ) {
                    echo $entity;
                    echo "<br>--------------------------<br>";
                    $em->getRepository('OlegOrderformBundle:OrderInfo')->printTree( $patient );
                    echo "--------------------------<br>";
                }

                $newOrderinfo->setCicle('submit');
                //$newOrderinfo->setId(null);
                $newOrderinfo->setOriginalid($originalId);

//                foreach( $newOrderinfo->getPatient() as $patient ) {
//                    $patient->cloneChildren();
//                } 
                //$newOrderinfo = $this->iterateOrderInfo( $newOrderinfo, $statusStr );

                echo "<br><br>orig entity2###########: final patients count=".count($entity->getPatient())."<br>";
                foreach( $entity->getPatient() as $patient ) {
                    echo $entity;
                    echo "<br>--------------------------<br>";
                    $em->getRepository('OlegOrderformBundle:OrderInfo')->printTree( $patient );
                    echo "--------------------------<br>";
                }

                echo "<br><br>newOrderinfo1 ##########: final patients count=".count($newOrderinfo->getPatient())."<br>";
                foreach( $newOrderinfo->getPatient() as $patient ) {
                    echo $newOrderinfo;
                    echo "<br>--------------------------<br>";
                    $em->getRepository('OlegOrderformBundle:OrderInfo')->printTree( $patient );
                    echo "--------------------------<br>";
                }

                //exit("order util exit on submit");

                $message = $this->processObjects( $newOrderinfo, $status_entity, $statusStr );

                echo "<br><br>newOrderinfo2 ##########: final patients count=".count($newOrderinfo->getPatient())."<br>";
                foreach( $newOrderinfo->getPatient() as $patient ) {
                    echo $newOrderinfo;
                    echo "<br>--------------------------<br>";
                    $em->getRepository('OlegOrderformBundle:OrderInfo')->printTree( $patient );
                    echo "--------------------------<br>";
                }                             
                
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

    public function iterateOrderInfo( $orderinfo, $statusStr ) {

        //patient
        $patients = $orderinfo->getPatient();
        //$orderinfo->clearPatient();

        foreach( $patients as $patient ) {
           
            $orderinfo->removePatient($patient);
            $new_patient = clone $patient;
            $new_patient->setId(null);
            $new_patient->setStatus($statusStr);
            $orderinfo->addPatient($new_patient);

            //procdeure
            foreach( $patient->getProcedure() as $procedure ) {

                $new_patient->removeChildren($procedure);
                $new_procedure = clone $procedure;
                $new_procedure->setId(null);
                $new_procedure->setStatus($statusStr);
                $new_patient->addChildren($new_procedure);

                //accession
                foreach( $procedure->getAccession() as $accession ) {

                    $new_procedure->removeChildren($accession);
                    $new_accession = clone $accession;
                    $new_accession->setId(null);
                    $new_accession->setStatus($statusStr);
                    $new_procedure->addChildren($new_accession);

                    //part
                    foreach( $accession->getPart() as $part ) {

                        $new_accession->removeChildren($part);
                        $new_part = clone $part;
                        $new_part->setId(null);
                        $new_part->setStatus($statusStr);
                        $new_accession->addChildren($new_part);

                        //slide
                        foreach( $part->getChildren() as $child ) {

                            $new_part->removeChildren($child);
                            $new_child = clone $child;
                            $new_child->setId(null);
                            $new_child->setStatus($statusStr);
                            $new_part->addChildren($new_child);

                        }//slide

//                        //block
//                        foreach( $part->getBlock() as $block ) {
//
//                            $new_part->removeChildren($block);
//                            echo "clone block";
//                            $new_block = clone $block;
//                            echo "...done <br>";
//                            $new_block->setId(null);
//                            $new_block->setStatus($statusStr);
//                            $new_part->addChildren($new_block);
//
//                            //slide
//                            foreach( $block->getSlide() as $slide ) {
//
//                                $new_block->removeChildren($slide);
//                                $new_slide = clone $slide;
//                                $new_slide->setId(null);
//                                $new_slide->setStatus($statusStr);
//                                $new_block->addChildren($new_slide);
//
//                            }//slide
//
//                        }//block

                    }//part
                }//accession
            }//procedure
        }//patient

        return $orderinfo;

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
                //set ID to null if status is valid (un-cancel procedure)
                if( $statusStr == 'valid' ) {

                    //$newChild = clone $child;

                    $child->setId(null);
                    //$child->removeOrderInfo($orderinfo);
                    //$newChild->clearOrderinfo();
                    //$em->persist($child);                   
                    $em->detach($child);
                    $em->persist($child);
                }
                $count++;
            }

        }

        return $count;
    }

}