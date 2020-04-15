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
 * Created by JetBrains PhpStorm.
 * User: oli2002
 * Date: 10/4/13
 * Time: 12:56 PM
 * To change this template use File | Settings | File Templates.
 */

namespace App\OrderformBundle\Util;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

use Doctrine\Common\Collections\ArrayCollection;
use App\UserdirectoryBundle\Entity\InstitutionWrapper;
use App\UserdirectoryBundle\Entity\PerSiteSettings;
use Symfony\Component\HttpFoundation\RedirectResponse;

use App\OrderformBundle\Entity\History;
use App\OrderformBundle\Entity\DataQualityMrnAcc;

use App\UserdirectoryBundle\Entity\User;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;


class OrderUtil {

    private $em;
    private $container;
    private $secTokenStorage;
    private $secAuthChecker;

    public function __construct( EntityManagerInterface $em, ContainerInterface $container ) {
        $this->em = $em;

        $this->container = $container;
        $this->secAuthChecker = $container->get('security.authorization_checker');
        $this->secTokenStorage = $container->get('security.token_storage');
    }

    public function redirectOrderByStatus($order,$routeName) {

        if( $order->getMessageCategory()->getName() == "Table-View Scan Order" ) {
            $edit = "table_edit";
            $amend = "table_amend";
            $show = "table_show";
        } else {
            $edit = "multy_edit";
            $amend = "order_amend";
            $show = "multy_show";
        }
        //echo "show=".$show." <br>";

        $router = $this->container->get('router');

        //if order is not submitted with edit url => change url to edit
        if( $order->getStatus()."" == "Not Submitted" && $routeName != $edit ) {
            return new RedirectResponse( $router->generate($edit,array('id'=>$order->getOid())) );
        }

        //if order is submitted with edit url => change url to show
        if( $order->getStatus()."" != "Not Submitted" && $routeName == $edit ) {
            return new RedirectResponse( $router->generate($show,array('id'=>$order->getOid())) );
        }

        //if order is not submitted with amend url => change url to edit
        if( $order->getStatus()."" == "Not Submitted" && $routeName == $amend ) {
            return new RedirectResponse( $router->generate($edit,array('id'=>$order->getOid())) );
        }

        //if order Filled or Canceled by Processor or Canceled by Submitter or Superseded with amend url => change url to show
        if(
            $routeName == $amend && (
                strpos($order->getStatus()."",'Filled') !== false ||
                strpos($order->getStatus()."",'Canceled') !== false ||
                $order->getStatus()."" == "Superseded"
            )
        ) {
            return new RedirectResponse( $router->generate($show,array('id'=>$order->getOid())) );
        }

        //echo "no redirect <br>";
        
        return null;

    }

    public function changeStatus( $id, $status, $user, $swapId=null ) {

        //exit('change status');

        $em = $this->em;

        $entity = $em->getRepository('AppOrderformBundle:Message')->findOneByOid($id);

        if (!$entity) {
            throw new \Exception( 'Unable to find Message entity by id'.$id );
        }

        $securityUtil = $this->container->get('user_security_utility');
        if( !$securityUtil->isUserAllowOrderActions($entity, $user, array('changestatus')) ) {
            $res = array();
            $res['result'] = 'nopermission';
            return $res;
        }

        if( $status == 'Un-Cancel' ) {
            $statusSearch = 'Submit';
        } else {
            $statusSearch = $status;
        }

        //echo "status=".$status."<br>";
        $status_entity = $em->getRepository('AppOrderformBundle:Status')->findOneByAction($statusSearch);
        //echo "status_entity=".$status_entity->getName()."<br>";
        //exit();

        if( $status_entity == null ) {
            throw new \Exception( 'Unable to find status for status=' . $statusSearch );
        }

        //record history
        $history = new History();
        $eventtype = $em->getRepository('AppOrderformBundle:ProgressCommentsEventTypeList')->findOneByName('Status Changed');
        $history->setEventtype($eventtype);
        $history->setMessage($entity);
        $history->setCurrentid($entity->getOid());
        $history->setCurrentstatus($entity->getStatus());
        $history->setProvider($user);
        $history->setRoles($user->getRoles());

        //change status for all message children to "deleted-by-canceled-order"
        //IF their source is ='scanorder' AND there are no child objects with status == 'valid'
        //AND there are no fields that belong to this object that were added by another order
        if( $status == 'Cancel' ) {

            //exit('case Cancel');

            $fieldStatusStr = "deleted-by-canceled-order";

            if( $entity->getProvider() == $user || $this->secAuthChecker->isGranted("ROLE_SCANORDER_ORDERING_PROVIDER") ) {
                $status_entity = $em->getRepository('AppOrderformBundle:Status')->findOneByName("Canceled by Submitter");
            } else
            if( $this->secAuthChecker->isGranted("ROLE_SCANORDER_ADMIN") || $this->secAuthChecker->isGranted("ROLE_SCANORDER_PROCESSOR") ) {
                $status_entity = $em->getRepository('AppOrderformBundle:Status')->findOneByName("Canceled by Processor");
            } else {
                $status_entity = $em->getRepository('AppOrderformBundle:Status')->findOneByName("Canceled by Submitter");
            }

            if( $status_entity == null ) {
                throw new \Exception( 'Unable to find status for canceled order' );
            }

            $entity->setStatus($status_entity);
            $message = $this->processObjects( $entity, $status_entity, $fieldStatusStr );

            //record history
            $history->setCurrentid($entity->getOid());
            $history->setCurrentstatus($entity->getStatus());

            $em->persist($entity);
            $em->persist($history);
            $em->flush();
            $em->clear();

        } else if( $status == 'Supersede' ) {

            //exit('case Supersede');

            //$status_entity = $em->getRepository('AppOrderformBundle:Status')->findOneByName("Superseded");
            $fieldStatusStr = "deleted-by-amended-order";
            $entity->setStatus($status_entity);
            $message = $this->processObjects( $entity, $status_entity, $fieldStatusStr );
            $entity->setOid($swapId);
            //$entity->setCycle("superseded");

            //record history
            $history->setCurrentid($entity->getOid());
            $history->setCurrentstatus($entity->getStatus());

            $router = $this->container->get('router');
            $url = $router->generate( 'multy_show', array('id' => $id) );
            $link = '<a href="'.$url.'">order '.$id.'</a>';
            $notemsg = 'This order is an old superseded version of '.$link;
            $history->setNote($notemsg);


            $em->persist($entity);
            $em->persist($history);
            $em->flush();
            //$em->clear();   //testing clear
            //unset($entity);
            //unset($history);
            //gc_collect_cycles();

        } else if( $status == 'Un-Cancel' || $status == 'Submit' ) {  //this is un-cancel action

            //exit('case Un-Cancel or Submit');

            //1) clone message object
            //2) validate MRN-Accession
            //3) change status to 'valid' and 'submit'

//            echo "<br><br>newMessage Patient's count=".count($entity->getPatient())."<br>";
//            echo $entity;
//            foreach( $entity->getPatient() as $patient ) {
//                echo "<br>--------------------------<br>";
//                $em->getRepository('AppOrderformBundle:Message')->printTree( $patient );
//                echo "--------------------------<br>";
//            }
            //exit();

            //VALIDATION Accession-MRN
            $validity = array('valid');
            $conflict = false;
            foreach( $entity->getAccession() as $accession ) {

                //                     procedure    encounter     patient
                $patient = $accession->getParent()->getParent()->getParent();

                $patientKey = $patient->obtainValidKeyField();
                if( !$patientKey ) {
                    throw new \Exception( 'Object does not have a valid key field. Object: '.$patient );
                }

                $accessionKey = $accession->obtainValidKeyField();
                if( !$accessionKey ) {
                    throw new \Exception( 'Object does not have a valid key field. Object: '.$accession );
                }

                //echo "accessionKey=".$accessionKey."<br>";
                $accessionDb = $em->getRepository('AppOrderformBundle:Accession')->findOneByIdJoinedToField(array($accession->getInstitution()->getId()),$accessionKey,"Accession","accession",$validity, true); //validity was true

                $mrn = $patientKey; //mrn
                $mrnTypeId = $patientKey->getKeytype()->getId();
                //$extra = $patientKey->obtainExtraKey();

                if( $accessionDb ) {
                    //echo "similar accession found=".$accessionDb;
                    $patientDb = $accessionDb->getParent()->getParent();
                    if( $patientDb ) {
                        $mrnDb = $patientDb->obtainValidKeyField();
                        $mrnTypeIdDb = $mrnDb->getKeytype()->getId();

                        //echo $mrn . "?=". $mrnDb ." && ". $mrnTypeId . "==". $mrnTypeIdDb . "<br>";

                        if( $mrn == $mrnDb && $mrnTypeId == $mrnTypeIdDb ) {
                            //ok
                            //exit("no conflict <br>");
                        } else {
                            //echo "there is a conflict <br>";
                            //conflict => render the message in the amend view 'order_amend'
                            //exit('un-canceling order. id='.$entity->getOid());
                            $conflict = true;
                        }
                    }
                }

            }

            if( $conflict ) {

                //exit('conflict un-canceling order. id='.$entity->getOid());

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
                $history->setCurrentid($entity->getOid());
                $history->setCurrentstatus($entity->getStatus());

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
            $history->setCurrentid($entity->getOid());
            $history->setCurrentstatus($entity->getStatus());

            $em->persist($entity);
            $em->persist($history);
            $em->flush();
            $em->clear();

        }

        //$message = 'Status of Order '.$id.' has been changed to "'.$status.'"'.$message;
        $message = 'Status of Order '.$id.' succesfully changed to "'.$entity->getStatus()->getName().'"';

        $res = array();
        $res['result'] = 'ok';
        $res['message'] = $message;

        return $res;
    }

//    public function makeMessageClone( $entity, $status_entity, $statusStr ) {
//
//        $em = $this->em;
//
//        if( !$status_entity  ) {
//            $status_entity = $em->getRepository('AppOrderformBundle:Status')->findOneByAction('Submit');
//        }
//
//        //CLONING
//        $oid = $entity->getOid();
//        $oidArr = explode("-", $oid);
//        $originalId = $oidArr[0];
//
//        $newMessage = clone $entity;
//
//        $em->detach($entity);
//        $em->detach($newMessage);
//
//        $newMessage->setStatus($status_entity);
//        //$newMessage->setCycle('submit');
//        $newMessage->setOid($originalId);
//
//        //$newMessage = $this->iterateMessage( $newMessage, $statusStr );
//
//        //change status to valid
//        $message = $this->processObjects( $newMessage, $status_entity, $statusStr );
//
//        $res = array();
//        $res['message'] = $message;
//        $res['message'] = $newMessage;
//
//        return $res;
//    }

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

    public function iterateEntity( $message, $children, $status_entity, $statusStr ) {

        $em = $this->em;

        if( !$children->first() ) {
            return 0;
        }

        //echo "iterate children count=".count($children)."<br>";

        $class = new \ReflectionClass($children->first());
        $className = $class->getShortName();
        //echo "class name=".$className."<br>";

        //$securityUtil = $this->container->get('user_security_utility');
        //$source = $securityUtil->getDefaultSourceSystem();

        $count = 0;

        foreach( $children as $child ) {

            $noOtherMessage = true;

            //echo "message count=".count($child->getMessage()).", order id=".$child->getMessage()->first()->getId()."<br>";

            //check if this object is used by any other message (for cancel and amend only)
            //TODO: check all parents too(?)
            if( $statusStr != 'valid' ) {
                foreach( $child->getMessage() as $order ) {
                    //echo "message id=".$order->getId().", oid=".$order->getOid()."<br>";
                    if( $message->getId() != $order->getId() ) {  //&& $order->getStatus() != $status_entity->getId()  ) {
                        $noOtherMessage = false;
                        break;
                    }
                }
            }

            //echo "noOtherMessage=".$noOtherMessage."<br>";

            //TODO: is it logical to check if source == scanorder? Why we have to limit to scanorder source?
            //Change status to a new $statusStr if the field is not used by other orders
            //if( $child->getSource()->getId() == $source->getId() && $noOtherMessage ) {
            if( $noOtherMessage ) {
                //echo "change status to (".$statusStr.") <br>";
                $child->setStatus($statusStr);
                $em->getRepository('AppOrderformBundle:'.$className)->processFieldArrays($child,null,null,$statusStr);
                $count++;
            } else {
                //this entity (i.e. accession object) is used by another order
                //TODO: what should we do? If we don't change the status, then we will have 2 entity with the same name (2 accessions with the same accession name and accession type)
            }

        }

        return $count;
    }




    //$flag = 'admin'-show only comments from users to admin or null-show only comments to the orders belonging to admin
    public function getNotViewedComments($flag=null)
    {
        $repository = $this->em->getRepository('AppOrderformBundle:History');
        $dql =  $repository->createQueryBuilder('history');
        //$dql->select('COUNT(history) as historycount');
        $dql->select('history');
        $dql->leftJoin("history.eventtype", "eventtype");

        $dql->innerJoin("history.message", "message");
        $dql->innerJoin("message.provider", "provider");

        $dql->leftJoin("message.proxyuser", "proxyuserWrapper");
        $dql->leftJoin("proxyuserWrapper.user", "proxyuser");

        //$dql->innerJoin("history.orderProvider", "provider");
        //$dql->leftJoin("history.orderProxyuser", "proxyuser");
        //$dql->leftJoin("history.orderInstitution", "institution");

        $dql->leftJoin("message.institution", "institution");
        $criteriastr = $this->getCommentsCriteriaStr(null, $flag);

        if( $criteriastr == "" ) {
            return null;
        }

        $dql->where($criteriastr);

        $dql->addOrderBy("history.changedate","DESC");

        //echo "<br>".$flag.": dql=".$dql."<br>";

        $query = $dql->getQuery();

        $entities = $query->getResult();

        //echo "=> count=".count($entities)."<br>";
//        foreach( $entities as $ent ) {
//            echo $ent->getId().", provider=".$ent->getProvider()."<br>";
//        }

        return $entities;
    }

    //$commentFlag = 'admin'-show only comments from users to admin;
    //$commentFlag = null-show only comments to the orders belonging to admin
    public function getCommentsCriteriaStr($flag = 'new_comments', $commentFlag = null) {

        if( !$this->secTokenStorage->getToken() ) {
            return "";
        }

        $criteriastr = "eventtype.name='Comment Added' ";

        if( $flag != 'all_comments' ) {
            $criteriastr = $criteriastr . "AND history.viewed is NULL ";
        } else {
            return $criteriastr;
        }

        $user = $this->secTokenStorage->getToken()->getUser();

        if( !is_object($user) ) {
            return "";
        }

        $role = "ROLE_SCANORDER_PROCESSOR";
        $role2 = "ROLE_SCANORDER_ADMIN";

        if( $commentFlag && $commentFlag == 'admin' ) {
            //echo "comments to admin only!, userid=".$user->getId()." =>";
            //processor can see all histories created by user without processor role, but not for orders belonging to this processor
            $criteriastr = $criteriastr . " AND history.roles NOT LIKE '%".$role."%' AND history.roles NOT LIKE '%".$role2."%'";

            //comments to admin only: can be addressed by another admin (so any user role) and for orders created by not current user (all new comments for the orders not created by current admin)
            $criteriastr = $criteriastr . " AND history.provider != " . $user->getId() . " AND provider != " . $user->getId();
        } else {
            //echo "comments about my orders only, placed by not me! =>";
            //submitter can see only histories created by user with processor or admin role for history's orders belongs to this user as provider or proxy
            //$criteriastr = $criteriastr . " AND ( history.roles LIKE '%".$role."%' OR history.roles LIKE '%".$role2."%' )";
            //submitters can only see the comments about thier order created by another user
            $criteriastr = $criteriastr . " AND ( (provider = ".$user->getId()." OR proxyuser = ".$user->getId().") AND history.provider != ".$user->getId()." )";
        }

        /////////// institution ///////////
        $criteriastr = $this->addInstitutionQueryCriterion($user,$criteriastr);
        /////////// EOF institution ///////////

        //echo "criteriastr=".$criteriastr."<br>";

        return $criteriastr;
    }

    //Used by addInstitutionQueryCriterion() here in getCommentsCriteriaStr() and in ScanOrderController in getDql()
    //Used by getUnporcesseOrders and getUnprocessedSlideRequests
    //check if node belongs to the parentNode tree. For example, 1wcmc6->2path5->3inf4 => if inf.lft > wcmc.lft AND inf.rgt < wcmc.rgt => return true.
    //check if user's institution is under message's institution node
    //$collaborationTypesStrArr - array of collaboration types; if null - ignore collaboration
    public function getInstitutionQueryCriterion( $user, $collaborationTypesStrArr=array("Union") ) {
        $securityUtil = $this->container->get('user_security_utility');

        $instStr = "";
        //return $instStr;

        //User's PermittedInstitutions
        $permittedInstitutions = $securityUtil->getUserPermittedInstitutions($user);

        //show institutions including collaborations
        $permittedInstitutions = $this->getAllScopeInstitutions($permittedInstitutions,null,$collaborationTypesStrArr);

        //echo "permittedInstitutions=".count($permittedInstitutions)."<br>";

        if( $collaborationTypesStrArr && count($collaborationTypesStrArr) > 0 ) {
            $instComparatorDefault = true;  //lft < getId
            $collComparatorDefault = false; //lft > getId

            foreach( $permittedInstitutions as $permittedInstitution ) {

                $institutionAndCollaborationStr = $this->em->getRepository('AppUserdirectoryBundle:Institution')->
                    getCriterionStrForCollaborationsByNode($permittedInstitution,"institution",$collaborationTypesStrArr, $instComparatorDefault, $collComparatorDefault );

                if( $instStr != "" ) {
                    $instStr = $instStr . " OR (" . $institutionAndCollaborationStr . ")";
                } else {
                    $instStr = $institutionAndCollaborationStr;
                }

            }
        }

//if(0){
//        if(1) {
//            foreach( $permittedInstitutions as $permittedInstitution ) {
//                if( $instStr != "" ) {
//                    $instStr = $instStr . " OR ";
//                }
//                $fieldstr = "institution";
//                $instStr .= "(";
//                $instStr .= $fieldstr.".root = " . $permittedInstitution->getRoot();
//                $instStr .= " AND ";
//                $instStr .= $fieldstr.".lft < " . $permittedInstitution->getLft();
//                $instStr .= " AND ";
//                $instStr .= $fieldstr.".rgt > " . $permittedInstitution->getRgt();
//                $instStr .= " OR ";
//                $instStr .= $fieldstr.".id = " . $permittedInstitution->getId();
//                $instStr .= ")";
//            }
//        }
//
//        if(1) {
//            //Collaboration check:
//            //1) find collaboration for each user's permitted institution
//            //2) if collaboration exists, check if message's institution belongs to any institution of this collaboration
//            foreach( $permittedInstitutions as $permittedInstitution ) {
//                $collaborations = $this->em->getRepository('AppUserdirectoryBundle:Institution')->
//                    findCollaborationsByNode( $permittedInstitution, array("Union","Intersection") );
//                foreach( $collaborations as $collaboration ) {
//                    foreach( $collaboration->getInstitutions() as $collaborationInstitution ) {
//                        if( $instStr != "" ) {
//                            $instStr = $instStr . " OR ";
//                        }
//                        $fieldstr = "institution";
//                        $instStr .= "(";
//                        $instStr .= $fieldstr.".root = " . $collaborationInstitution->getRoot();
//                        $instStr .= " AND ";
//                        $instStr .= $fieldstr.".lft > " . $collaborationInstitution->getLft();
//                        $instStr .= " AND ";
//                        $instStr .= $fieldstr.".rgt < " . $collaborationInstitution->getRgt();
//                        $instStr .= " OR ";
//                        $instStr .= $fieldstr.".id = " . $collaborationInstitution->getId();
//                        $instStr .= ")";
//                    }
//                }
//            }
//        }//if
//}

        if( $instStr == "" ) {
            $instStr = "1=0";
        }

        return $instStr;
    }
//    public function getInstitutionQuerySingleMatchCriterion($user) {
//        $securityUtil = $this->container->get('user_security_utility');
//        $institutions = $securityUtil->getUserPermittedInstitutions($user);
//        $instStr = "";
//        foreach( $institutions as $inst ) {
//            if( $instStr != "" ) {
//                $instStr = $instStr . " OR ";
//            }
//            $instStr = $instStr . 'message.institution='.$inst->getId();
//            //$instStr = $instStr . 'institution='.$inst->getId();
//        }
//        if( $instStr == "" ) {
//            $instStr = "1=0";
//        }
//        return $instStr;
//    }

    //$collaborationTypesStrArr: array("Union","Intersection","Untrusted Intersection"). If null => ignore collaboration. Default: array("Union","Intersection")
    public function addInstitutionQueryCriterion( $user, $criteriastr, $collaborationTypesStrArr=array("Union") ) {
        $instStr = $this->getInstitutionQueryCriterion($user,$collaborationTypesStrArr);
        if( $instStr != "" ) {
            if( $criteriastr && $criteriastr != "" ) {
                $criteriastr = $criteriastr . " AND (" . $instStr . ") ";
            } else {
                //$criteriastr = $criteriastr . " (" . $instStr . ") ";
                $criteriastr = $instStr;
            }
        }
        return $criteriastr;
    }

    //$dataqualities is a form data: $dataqualities = $form->get('conflicts')->getData();
    public function setDataQualityAccMrn( $entity, $dataqualities ) {

        $em = $this->em;

        foreach( $dataqualities as $dataquality ) {
//            echo "dataquality description= ".$dataquality['description']."<br>";
//            echo "dataquality accession= ".$dataquality['accession']."<br>";
//            echo "dataquality accessiontype= ".$dataquality['accessiontype']."<br>";
//            echo "dataquality mrn= ".$dataquality['mrn']."<br>";
//            echo "dataquality mrntype= ".$dataquality['mrntype']."<br>";

            //set correct mrntype (convert text keytype to the object)
            $mrntype = $em->getRepository('AppOrderformBundle:MrnType')->findOneById( $dataquality['mrntype'] );

            //set correct accessiontype
            $accessiontype = $em->getRepository('AppOrderformBundle:AccessionType')->findOneById( $dataquality['accessiontype'] );

            $dataqualityObj = new DataQualityMrnAcc();
            $dataqualityObj->setDescription($dataquality['description']);
            $dataqualityObj->setMrn($dataquality['mrn']);
            $dataqualityObj->setMrntype($mrntype);
            $dataqualityObj->setAccession($dataquality['accession']);
            $dataqualityObj->setAccessiontype($accessiontype);

            $dataqualityObj->setMessage($entity);
            $dataqualityObj->setProvider($entity->getProvider());
            $dataqualityObj->setStatus('active');

//            echo "dataquality: description=".$dataqualityObj->getDescription()."<br>";
//            echo "dataquality: accession=".$dataqualityObj->getAccession()."<br>";
//            echo "dataquality: accessionType=".$dataqualityObj->getAccessiontype()."<br>";
//            echo "dataquality: mrn=".$dataqualityObj->getMrn()."<br>";
//            echo "dataquality: mrn text=".$dataqualityObj->getMrntype()."<br>";

            $entity->addDataqualityMrnAcc($dataqualityObj);
        }

    }


    public function setWarningMessageNoInstitution( $user ) {

        $router = $this->container->get('router');
        $userUrl = $router->generate('scan_showuser', array('id' => $user->getId()),UrlGeneratorInterface::ABSOLUTE_URL);
        $homeUrl = $router->generate('main_common_home',array(),UrlGeneratorInterface::ABSOLUTE_URL);
        $sysemail = $this->container->getParameter('default_system_email');
        $flashBag = $this->container->get('session')->getFlashBag();

        if( $this->secAuthChecker->isGranted('ROLE_SCANORDER_PROCESSOR') || $this->secAuthChecker->isGranted('ROLE_SCANORDER_ADMIN') ) {

            $webUserUrl = "<a href=".$userUrl.">profile</a>";
            $msg =  "Please add at least one institution to your user ".$webUserUrl." (in the form field titled 'Order data visible to members of (Institutional PHI Scope)') in order to be able to submit data.";

        } else {

            $webUserUrl = "<a href=".$userUrl.">profile</a>";

            $msg =  'Please contact the System Administrator by emailing '.$sysemail.' and request to add to your '.$webUserUrl.
                    ' the name of the Institution that employs you (such as "Weill Cornell Medical College"). '.
                    'You will not be able to place orders until the system administrator completes this step.';

            //send Email
            $subject = "User ".$user." needs to have an Institution added to their profile";

            $message =  "Please visit the ".$userUrl." of user ".$user.
                        " and add the name of the institution that employs this user to enable her or him to place orders & and to view patient information known to that institution:<br><br>".
                        $userUrl."<br>".
                        "<br><br>".
                        "Agent Smith<br>".
                        "Virtual Keeper of O R D E R: ".$homeUrl."<br>".
                        "Weill Cornell Medical College";

            $emailUtil = $this->container->get('user_mailer_utility');
            $emailUtil->sendEmail($sysemail, $subject, $message);

        }
        $flashBag->add(
            'warning',
            $msg
        );

    }

    public function addDefaultInstitutionalPhiScope( $user, $session=null ) {

        $permittedInstitutions = $this->getAtleastOneInstitutionPHI($user);
        if( count($permittedInstitutions) > 0 ) {
            return $permittedInstitutions;
        }

        $userSecUtil = $this->container->get('user_security_utility');
        //$enableAutoAssignmentInstitutionalScope = $userSecUtil->getSiteSettingParameter('enableAutoAssignmentInstitutionalScope');
        //$autoAssignInstitution = $userSecUtil->getSiteSettingParameter('autoAssignInstitution');
        $autoAssignInstitution = $userSecUtil->getAutoAssignInstitution();
        
        if( $autoAssignInstitution ) {
            $userSiteSettings = $user->getPerSiteSettings();
            if( !$userSiteSettings ) {
                //set institution to per site settings
                $userSiteSettings = new PerSiteSettings();
                //$userSiteSettings->setAuthor($creator);
                $systemUser = $userSecUtil->findSystemUser();
                $userSiteSettings->setAuthor($systemUser);
                $userSiteSettings->setUser($user);
                //$this->em->persist($userSiteSettings);
            }
            $userSiteSettings->addPermittedInstitutionalPHIScope($autoAssignInstitution);
            $this->em->flush($userSiteSettings);
            $this->em->flush($user);

            if( $session ) {
                $session->getFlashBag()->add(
                    'notice',
                    "Institutional PHI Scope has been set to ".$autoAssignInstitution
                );
            }
        }

        $permittedInstitutions = $this->getAtleastOneInstitutionPHI($user);

        return $permittedInstitutions;
    }
    public function getAndAddAtleastOneInstitutionPHI( $user, $session=null ) {
        $permittedInstitutions = $this->getAtleastOneInstitutionPHI($user);

        if( count($permittedInstitutions) == 0 ) {
            $permittedInstitutions = $this->addDefaultInstitutionalPhiScope($user,$session);
        }
        //echo "permittedInstitutions count=".count($permittedInstitutions)."<br>";

        return $permittedInstitutions;
    }
    public function getAtleastOneInstitutionPHI( $user ) {
        $permittedInstitutions = array();
        $securityUtil = $this->container->get('user_security_utility');
        $userSiteSettings = $securityUtil->getUserPerSiteSettings($user);
        if( $userSiteSettings ) {
            $permittedInstitutions = $userSiteSettings->getPermittedInstitutionalPHIScope();
        }
        return $permittedInstitutions;
    }


    public function generateUserFilterOptions( $user ) {

        $choicesInst = array(
            "My Orders"=>"My Orders",
            "Where I am the Submitter"=>"Where I am the Submitter",
            "Where I am the Ordering Provider"=>"Where I am the Ordering Provider",
            "Where I am the Course Director"=>"Where I am the Course Director",
            "Where I am the Principal Investigator"=>"Where I am the Principal Investigator",
            "Where I am the Amendment Author"=>"Where I am the Amendment Author"
        );

        if( is_object($user) && $user instanceof User ) {

            $secUtil = $this->container->get('user_security_utility');

            //service scope
            $userServices = $secUtil->getScanOrdersServicesScope($user);

            //chief scope
            $userSiteSettings = $secUtil->getUserPerSiteSettings($user);
            if( $this->secAuthChecker->isGranted('ROLE_SCANORDER_SERVICE_CHIEF') ) {
                $chiefServices = $userSiteSettings->getChiefServices();
                foreach( $chiefServices as $serv ) {
                    if( !$userServices->contains($serv) ) {
                        //echo "add=".$serv."<br>";
                        $userServices->add($serv);
                    }
                }
            }

            foreach( $userServices as $userService ) {
                //echo "service=".$userService->getName().'<br>';
                //$choicesInst[$userService->getId()] = "All ".$userService->getName()." Orders";
                $choicesInst["All ".$userService->getName()." Orders"] = $userService->getId(); //flipped
            }

//            echo "<pre>";
//            print_r($choicesInst);
//            echo "</pre>";

            //add all collaboration institutions
            $securityUtil = $this->container->get('user_security_utility');
            $permittedInstitutions = $securityUtil->getUserPermittedInstitutions($user);
            foreach( $permittedInstitutions as $permittedInstitution ) {

                $collaborations = $this->em->getRepository('AppUserdirectoryBundle:Institution')->
                    findCollaborationsByNode( $permittedInstitution, array("Union","Intersection") );

                foreach( $collaborations as $collaborationInstitution ) {
                    $key = "collaborationkey-".$collaborationInstitution->getId();
                    if( !array_key_exists($key,$choicesInst) ) {
                        //echo "1 Adding Collaborating Inst=".$collaborationInstitution."<br>";
                        //$choicesInst[$key] = "All ".$collaborationInstitution." Orders";
                        $choicesInst["All ".$collaborationInstitution." Orders"] = $key; //flipped
                    }
                }

                //add all PHI scope institutions, i.e. "All NYP Orders"
                $key = "collaborationkey-".$permittedInstitution->getId();
                if( !array_key_exists($key,$choicesInst) ) {
                    //echo "2 Adding Collaborating Inst=".$collaborationInstitution."<br>";
                    //$choicesInst[$key] = "All ".$permittedInstitution." Orders";
                    $choicesInst["All ".$permittedInstitution." Orders"] = $key; //flipped
                }
            }//foreach

        }

//        echo "<pre>";
//        print_r($choicesInst);
//        echo "</pre>";

        return $choicesInst;
    }

    //get the last order
    public function getPreviousMessage( $categoryStr=null ) {
        $previousOrder = null;

        $user = $this->secTokenStorage->getToken()->getUser();

//        $previousOrders = $this->em->getRepository('AppOrderformBundle:Message')->findBy(
//            array('provider'=>$user),
//            array('orderdate' => 'DESC'),
//            1                                   //limit to one result
//        );

        $repository = $this->em->getRepository('AppOrderformBundle:Message');
        $dql =  $repository->createQueryBuilder("message");
        $dql->leftJoin('message.provider','provider');
        $dql->leftJoin('message.messageCategory','category');

        $criteria = "provider=".$user->getId(); //." AND category LIKE '%".$categoryStr."%'";
        if( $categoryStr && $categoryStr != "" ) {
            $criteria = $criteria . " AND category.name LIKE '%".$categoryStr."%'";
        }

        $dql->where($criteria);
        $dql->orderBy('message.orderdate','DESC');

        $query = $this->em->createQuery($dql)->setMaxResults(1);
        $previousOrders = $query->getResult();

        if( $previousOrders && count($previousOrders) > 0 ) {
            $previousOrder = $previousOrders[0];
        }

        //echo "prev order=".$previousOrder."<br>";

        return $previousOrder;
    }


    public function getOrderReturnLocations( $message, $providerid=null, $proxyid=null ) {

        $provider = null;
        $proxy = null;

        if( $message ) {
            $provider = $message->getProvider();
            $proxys = $message->getProxyuser();
            if( count($proxys) > 0 ) {
                $firstProxyWrapper = $proxys->first();
                if( $firstProxyWrapper ) {
                    $proxy = $firstProxyWrapper->getUser();
                }
            }
        } else {
            if( $providerid && $providerid != "" ) {
                $provider = $this->em->getRepository('AppUserdirectoryBundle:User')->find($providerid);
            }
            if( $proxyid && $proxyid != "" && $proxyid != $providerid ) {
                $proxy = $this->em->getRepository('AppUserdirectoryBundle:User')->find($proxyid);
            }
        }

        //get default returnLocation option
        //the top three choices should be the Submitter's Main Office location (from the user who is logged in; selected by default),
        //followed by the "Filing Room",
        //followed by the Ordering Provider's Main Office location, and then everyone else in alphabetical order.

        $mainOfficeLocation = null;
        $surgicalPathLocation = null;
        $orderingProviderMainOfficeLocation = null;

        //1) get submitter location
        if( $provider ) {
            $mainOfficeLocation = $provider->getMainLocation();
        }

        //2) get "Filing Room" location
        $repository = $this->em->getRepository('AppUserdirectoryBundle:Location');
        $dql =  $repository->createQueryBuilder("location");
        $dql->select('location');
        $dql->leftJoin("location.locationTypes", "locationTypes");
        $dql->where("locationTypes.name = 'Filing Room'");
        $query = $this->em->createQuery($dql);
        $surgicalPathLocations = $query->getResult();
        if( $surgicalPathLocations && count($surgicalPathLocations) == 1 ) {
            $surgicalPathLocation = $surgicalPathLocations[0];
        } else {
            $surgicalPathLocation = null;
        }
        //echo "surgicalPathLocation=".$surgicalPathLocation."<br>";

        //3) orderring provider location
        if( $proxy ) {
            $orderingProviderMainOfficeLocation = $proxy->getMainLocation();
        }

        if( $mainOfficeLocation ) {
            $defaultLocation = $mainOfficeLocation;
        } else if( $surgicalPathLocation ) {
            $defaultLocation = $surgicalPathLocation;
        } else if( $orderingProviderMainOfficeLocation ) {
            $defaultLocation = $surgicalPathLocation;
        } else {
            $defaultLocation = null;
        }

        $preferredChoices = array();
        if( $mainOfficeLocation ) {
            $preferredChoices[] = $mainOfficeLocation;
        }
        if( $surgicalPathLocation ) {
            $preferredChoices[] = $surgicalPathLocation;
        }
        if( $orderingProviderMainOfficeLocation ) {
            $preferredChoices[] = $orderingProviderMainOfficeLocation;
        }

        //echo "defaultLocation=".$defaultLocation."<br>";

        $res = array();
        $res['data'] = $defaultLocation;
        $res['preferred_choices'] = $preferredChoices;

        //print_r($res);

        return $res;
    }

    //get ordering provider from the most recent order
    public function setLastOrderWithProxyuser($user,$message=null) {
        $repository = $this->em->getRepository('AppOrderformBundle:Message');
        $dql =  $repository->createQueryBuilder("message");
        $dql->select('message');
        $dql->innerJoin("message.provider", "provider");
        $dql->leftJoin("message.proxyuser", "proxyuser");
        $dql->where("provider=:user AND proxyuser.id IS NOT NULL");
        $dql->orderBy("message.orderdate","DESC");
        $query = $this->em->createQuery($dql)->setParameter('user', $user)->setMaxResults(1);
        $lastOrderWithProxies = $query->getResult();
        //echo "count=".count($lastOrderWithProxies)."<br>";

        $proxyusers = new ArrayCollection();

        if( $message ) {
            if( count($lastOrderWithProxies) > 0 ) {
                foreach( $lastOrderWithProxies as $order ) {
                    //echo "order=".$order->getId()."<br>";
                    foreach( $order->getProxyuser() as $proxyuser ) {
                        //echo "proxyuser=".$proxyuser."<br>";
                        $message->addProxyuser($proxyuser);
                        $proxyusers->add($proxyuser);
                    }
                }
            } else {
                //echo "add [".$user."] as proxyuser <br>";
                $message->addProxyuserAsUser($user);
                foreach( $message->getProxyuser() as $proxyuser ) {
                    $proxyusers->add($proxyuser);
                }

            }
        }

//        foreach( $proxyusers as $proxyuser ) {
//            echo "[".$proxyuser."] is added as proxyuser <br>";
//        }

        $res = array(
            'proxyusers' => $proxyusers,
            'orders' => $lastOrderWithProxies
        );

        return $res;
    }


    public function removeAllOrdersPatients() {

        $em = $this->em;

        //find slides using this stain: slide(1)->(n)stain(n)->(1)stainList
//        $repository = $em->getRepository('AppOrderformBundle:Message');
//        $dql =  $repository->createQueryBuilder("message");
//        $dql->select('message');
//        $dql->leftJoin("message.slide","slide");
//        $dql->leftJoin("slide.stain","stain");
//        $dql->leftJoin("stain.field","stainList");
//        $dql->where('stainList.id = :stainid');
//        $query = $em->createQuery($dql)->setParameter('stainid', $stain->getId());
//        $messages = $query->getResult();

        if(1) {
            $patientTypes = $this->em->getRepository('AppOrderformBundle:PatientType')->findAll();
            foreach( $patientTypes as $patientType ) {
                $em->remove($patientType);
            }
            $em->flush();
        }

        $messages = $this->em->getRepository('AppOrderformBundle:Message')->findAll();

        foreach( $messages as $message ) {

            foreach( $message->getPatient() as $patient ) {

                foreach( $patient->getDeceased() as $item ) {
                    //$patient->removeDeceased($item);
                    $em->remove($item);
                }

                foreach( $patient->getRace() as $item ) {
                    //$patient->removeRace($item);
                    $em->remove($item);
                }

                foreach( $patient->getType() as $patientType ) {

//                    $patientType->setProvider(null);
//                    $patientType->setSource(null);
//                    $patientType->setMessage(null);
//                    $patientType->setPatient(null);
//                    $patientType->setField(null);

                    //foreach( $patientType->getSources() as $item ) {
                        //$em->remove($item);
                    //}

                    $patient->removeType($patientType);
                    $em->remove($patientType);
                }

                $em->remove($patient);
            }

            foreach( $message->getEncounter() as $encounter ) {

                foreach( $encounter->getInpatientinfo() as $item ) {
                    //$encounter->removeInpatientinfo($item);
                    $em->remove($item);
                }

                foreach( $encounter->getLocation() as $item ) {
                    //$encounter->removeLocation($item);
                    $em->remove($item);
                }

                $em->remove($encounter);
            }

            foreach( $message->getProcedure() as $procedure ) {

                foreach( $procedure->getLocation() as $item ) {
                    //$procedure->removeLocation($item);
                    $em->remove($item);
                }

                $em->remove($procedure);
            }

            foreach( $message->getAccession() as $item ) {
                $em->remove($item);
            }

            foreach( $message->getPart() as $part ) {

                foreach( $part->getDiseaseType() as $item ) {
                    //$part->removeDiseaseType($item);
                    $em->remove($item);
                }

                $em->remove($part);
            }

            foreach( $message->getBlock() as $item ) {
                $em->remove($item);
            }

            foreach( $message->getSlide() as $slide ) {

                foreach( $slide->getStain() as $stain ) {
                    //$slide->removeStain($stain);
                    //$stain->setSlide(null);
                    $em->remove($stain);
                }

                foreach( $slide->getScan() as $scan ) {
                    //$slide->removeScan($scan);
                    $em->remove($scan);
                }

                foreach( $slide->getRelevantscan() as $relevantscan ) {
                    //$slide->removeRelevantscan($relevantscan);
                    //$relevantscan->setSlide(null);
                    $em->remove($relevantscan);
                }

                $em->remove($slide);
            }



            $em->remove($message);
        } //message

        //$em->remove($stain);
        $em->flush();

        return count($messages);
    }

    public function removeAllStains() {

        $em = $this->em;
        $stains = $em->getRepository('AppOrderformBundle:StainList')->findAll();

        $count = 0;

        //stain depenedencies: scan_stain and scan_blockSpecialStain
        foreach( $stains as $stain ) {
            //echo "stain=".$stain->getId()." ".$stain."<br>";
            //$em->persist($stain);

//            $original = $stain->getOriginal();
//            if( $original ) {
//                echo "original stain=".$original."<br>";
//                $em->persist($original);
//                $original->removeSynonym($stain);
//                $stain->setOriginal(null);
//                //$em->remove($original);
//                //$em->flush();
//            }
//
//            foreach( $stain->getSynonyms() as $synonim ) {
//                $stain->removeSynonym($synonim);
//                $synonim->setOriginal(null);
//                $stain->setOriginal(null);
//                $em->persist($synonim);
//                $em->remove($synonim);
//            }

            $em->remove($stain);
            //$em->flush($stain);

            $count++;
        }
        $em->flush();

        return $count;
    }

    //Used in new order field: "Order data visible to members of (Institutional PHI Scope)"
    public function getAllScopeInstitutions( $originalPermittedInstitutions, $message, $collaborationTypesStrArr=array("Union","Intersection","Untrusted Intersection") ) {

        //$permittedInstitutions = $this->getPermittedScopeCollaborations($originalPermittedInstitutions,array("Union","Intersection","Untrusted Intersection"));
        $permittedInstitutions = $this->getPermittedScopeCollaborationInstitutions($originalPermittedInstitutions,$collaborationTypesStrArr);

        //include current message institution to the $permittedInstitutions
        $permittedInstitutions = $this->addPhiScopeCurrentMessageInstitution($permittedInstitutions,$message);

        return $permittedInstitutions;
    }

    //The same as getPermittedScopeCollaborationInstitutions
    //get collaborations in the Institutional tree (i.e. "WCM-NYP Collaboration")
//    public function getPermittedScopeCollaborations( $originalPermittedInstitutions, $collaborationTypesStrArr, $withOriginal=true ) {
//        $permittedInstitutions = new ArrayCollection();
//
//        //include collaboration (any type) institutions by user
//        //permittedInstitutionalPHIScope - institutions
//        foreach( $originalPermittedInstitutions as $originalPermittedInstitution ) {
//
//            //add collaboration institutions
//            foreach( $originalPermittedInstitution->getCollaborationInstitutions() as $collaborationInstitution ) {
//                //echo "collaborationInstitution=".$collaborationInstitution."<br>";
//                $collaborationObjType = $originalPermittedInstitution->getCollaborationType()."";
//                //echo "collaborationObjType=".$collaborationObjType."<br>";
//                if( $collaborationObjType && in_array($collaborationObjType, $collaborationTypesStrArr) ) {
//                    if( $collaborationInstitution && !$permittedInstitutions->contains($collaborationInstitution)  ) {
//                        $permittedInstitutions->add($collaborationInstitution);
//                    }
//                }
//            }
//
//            //add original permitted intsitutions
//            if( $withOriginal ) {
//                if( $originalPermittedInstitution && !$permittedInstitutions->contains($originalPermittedInstitution)  ) {
//                    $permittedInstitutions->add($originalPermittedInstitution);
//                }
//            }
//
//        }//foreach
//
//        return $permittedInstitutions;
//    }

    //get collaborations as single institutions
    public function getPermittedScopeCollaborationInstitutions( $originalPermittedInstitutions, $collaborationTypesStrArr, $withOriginal=true ) {

        $permittedInstitutions = new ArrayCollection();

        //include collaboration (any type) institutions by user
        //permittedInstitutionalPHIScope - institutions
        foreach( $originalPermittedInstitutions as $originalPermittedInstitution ) {

            if( $withOriginal ) {
                $permittedInstitutions->add($originalPermittedInstitution);
            }
            //echo "### permittedInstitution=".$originalPermittedInstitution->getId().":".$originalPermittedInstitution->getName()."<br>";

            //get all collaboration to show them in the Order's Institutional PHI Scope
            $collaborationInstitutions = $this->em->getRepository('AppUserdirectoryBundle:Institution')->
                                findCollaborationsByNode( $originalPermittedInstitution, $collaborationTypesStrArr );

            foreach( $collaborationInstitutions as $collaborationInstitution ) {
                //echo "collaborationInstitution=".$collaborationInstitution."<br>";

                if( $collaborationInstitution && !$permittedInstitutions->contains($collaborationInstitution) ) {
                    //echo "add collaboration inst=".$collaborationInstitution->getId().":".$collaborationInstitution->getName()."<br>";
                    //add collaboration institution at the first position, so ->first() will auto-set to "WCM-NYP Collaboration" by default
                    $firstInst = $permittedInstitutions->first();               //1) save the first element
                    $permittedInstitutions->set(0,$collaborationInstitution);   //2) set to the first position
                    $permittedInstitutions->add($firstInst);                    //3) add back the previous first element
                }

            }

        }//foreach

        return $permittedInstitutions;
    }

    public function addPhiScopeCurrentMessageInstitution($permittedInstitutions,$message) {
        //include current message institution to the $permittedInstitutions
        if( $message && $message->getInstitution() && !$permittedInstitutions->contains($message->getInstitution()) ) {
            //echo "add permittedInstitutions=".$message->getInstitution()->getName()."<br>";
            $permittedInstitutions->add($message->getInstitution());
        }

        return $permittedInstitutions;
    }

    //set Performing organization:
    //"Weill Cornell Medical College > Department of Pathology and Laboratory Medicine > Pathology Informatics > Scanning Service"
    public function setDefaultPerformingOrganization($message) {
//        $mapper = array(
//            'prefix' => 'App',
//            'bundleName' => 'UserdirectoryBundle',
//            'className' => 'Institution'
//        );
//        $wcmc = $this->em->getRepository('AppUserdirectoryBundle:Institution')->findOneByAbbreviation("WCM");
//        $pathology = $this->em->getRepository('AppUserdirectoryBundle:Institution')->findByChildnameAndParent(
//            "Pathology and Laboratory Medicine",
//            $wcmc,
//            $mapper
//        );
//        $pathologyInformatcs = $this->em->getRepository('AppUserdirectoryBundle:Institution')->findByChildnameAndParent(
//            "Pathology Informatics",
//            $pathology,
//            $mapper
//        );
//        $scanningService = $this->em->getRepository('AppUserdirectoryBundle:Institution')->findByChildnameAndParent(
//            "Scanning Service",
//            $pathologyInformatcs,
//            $mapper
//        );

        $userSecUtil = $this->container->get('user_security_utility');
        $scanningService = $userSecUtil->getNotEmptyDefaultSiteParameter('defaultOrganizationRecipient',null);

        if( $scanningService ) {
            $organizationRecipient = new InstitutionWrapper();
            $organizationRecipient->setInstitution($scanningService);
            $message->addOrganizationRecipient($organizationRecipient);
        }
    }


    //get default Accession list objects
    public function getDefaultAccessionLists() {
        //Accession list currently is level=3
        $level = 3;

        $parent = $this->em->getRepository('AppOrderformBundle:AccessionListHierarchy')->findOneByName("Accession Lists");

        $accessionLists = $this->em->getRepository('AppOrderformBundle:AccessionListHierarchy')->findBy(
            array(
                'type' => array('default','user-added'),
                'level' => $level,
                'parent' => $parent->getId()
            )
        );

        return $accessionLists;
    }

}