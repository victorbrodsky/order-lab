<?php
/**
 * Created by JetBrains PhpStorm.
 * User: oli2002
 * Date: 10/4/13
 * Time: 12:56 PM
 * To change this template use File | Settings | File Templates.
 */

namespace Oleg\OrderformBundle\Helper;


use Symfony\Component\HttpFoundation\RedirectResponse;

use Oleg\OrderformBundle\Entity\History;
use Oleg\OrderformBundle\Entity\DataQualityMrnAcc;

use Oleg\UserdirectoryBundle\Entity\User;
use Oleg\UserdirectoryBundle\Util\EmailUtil;


class OrderUtil {

    private $em;
    private $container;
    private $sc;

    public function __construct( $em, $container, $sc ) {
        $this->em = $em;
        $this->container = $container;
        $this->sc = $sc;
    }

    public function redirectOrderByStatus($order,$routeName) {

        if( $order->getMessageCategory() == "Table-View Scan Order" ) {
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

        $entity = $em->getRepository('OlegOrderformBundle:OrderInfo')->findOneByOid($id);

        if (!$entity) {
            throw new \Exception( 'Unable to find OrderInfo entity by id'.$id );
        }

        $securityUtil = $this->container->get('order_security_utility');
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
        $status_entity = $em->getRepository('OlegOrderformBundle:Status')->findOneByAction($statusSearch);
        //echo "status_entity=".$status_entity->getName()."<br>";
        //exit();

        if( $status_entity == null ) {
            throw new \Exception( 'Unable to find status for status=' . $statusSearch );
        }

        //record history
        $history = new History();
        $eventtype = $em->getRepository('OlegOrderformBundle:ProgressCommentsEventTypeList')->findOneByName('Status Changed');
        $history->setEventtype($eventtype);
        $history->setOrderinfo($entity);
        $history->setCurrentid($entity->getOid());
        $history->setCurrentstatus($entity->getStatus());
        $history->setProvider($user);
        $history->setRoles($user->getRoles());

        //change status for all orderinfo children to "deleted-by-canceled-order"
        //IF their source is ='scanorder' AND there are no child objects with status == 'valid'
        //AND there are no fields that belong to this object that were added by another order
        if( $status == 'Cancel' ) {

            //exit('case Cancel');

            $fieldStatusStr = "deleted-by-canceled-order";

            if( $entity->getProvider() == $user || $this->sc->isGranted("ROLE_SCANORDER_ORDERING_PROVIDER") ) {
                $status_entity = $em->getRepository('OlegOrderformBundle:Status')->findOneByName("Canceled by Submitter");
            } else
            if( $this->sc->isGranted("ROLE_SCANORDER_ADMIN") || $this->sc->isGranted("ROLE_SCANORDER_PROCESSOR") ) {
                $status_entity = $em->getRepository('OlegOrderformBundle:Status')->findOneByName("Canceled by Processor");
            } else {
                $status_entity = $em->getRepository('OlegOrderformBundle:Status')->findOneByName("Canceled by Submitter");
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

            //$status_entity = $em->getRepository('OlegOrderformBundle:Status')->findOneByName("Superseded");
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

            //1) clone orderinfo object
            //2) validate MRN-Accession
            //3) change status to 'valid' and 'submit'

//            echo "<br><br>newOrderinfo Patient's count=".count($entity->getPatient())."<br>";
//            echo $entity;
//            foreach( $entity->getPatient() as $patient ) {
//                echo "<br>--------------------------<br>";
//                $em->getRepository('OlegOrderformBundle:OrderInfo')->printTree( $patient );
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
                $accessionDb = $em->getRepository('OlegOrderformBundle:Accession')->findOneByIdJoinedToField(array($accession->getInstitution()->getId()),$accessionKey,"Accession","accession",$validity, true); //validity was true

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
                            //conflict => render the orderinfo in the amend view 'order_amend'
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

//    public function makeOrderInfoClone( $entity, $status_entity, $statusStr ) {
//
//        $em = $this->em;
//
//        if( !$status_entity  ) {
//            $status_entity = $em->getRepository('OlegOrderformBundle:Status')->findOneByAction('Submit');
//        }
//
//        //CLONING
//        $oid = $entity->getOid();
//        $oidArr = explode("-", $oid);
//        $originalId = $oidArr[0];
//
//        $newOrderinfo = clone $entity;
//
//        $em->detach($entity);
//        $em->detach($newOrderinfo);
//
//        $newOrderinfo->setStatus($status_entity);
//        //$newOrderinfo->setCycle('submit');
//        $newOrderinfo->setOid($originalId);
//
//        //$newOrderinfo = $this->iterateOrderInfo( $newOrderinfo, $statusStr );
//
//        //change status to valid
//        $message = $this->processObjects( $newOrderinfo, $status_entity, $statusStr );
//
//        $res = array();
//        $res['message'] = $message;
//        $res['orderinfo'] = $newOrderinfo;
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

    public function iterateEntity( $orderinfo, $children, $status_entity, $statusStr ) {

        $em = $this->em;

        if( !$children->first() ) {
            return 0;
        }

        //echo "iterate children count=".count($children)."<br>";

        $class = new \ReflectionClass($children->first());
        $className = $class->getShortName();
        //echo "class name=".$className."<br>";

        $securityUtil = $this->container->get('order_security_utility');
        $source = $source = $securityUtil->getDefaultSourceSystem();

        $count = 0;

        foreach( $children as $child ) {

            $noOtherOrderinfo = true;

            //echo "orderinfo count=".count($child->getOrderinfo()).", order id=".$child->getOrderinfo()->first()->getId()."<br>";

            //check if this object is used by any other orderinfo (for cancel and amend only)
            //TODO: check all parents too(?)
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

            //TODO: is it logical to check if source == scanorder? Why we have to limit to scanorder source?
            //Change status to a new $statusStr if the field is not used by other orders
            //if( $child->getSource()->getId() == $source->getId() && $noOtherOrderinfo ) {
            if( $noOtherOrderinfo ) {
                //echo "change status to (".$statusStr.") <br>";
                $child->setStatus($statusStr);
                $em->getRepository('OlegOrderformBundle:'.$className)->processFieldArrays($child,null,null,$statusStr);
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
        $repository = $this->em->getRepository('OlegOrderformBundle:History');
        $dql =  $repository->createQueryBuilder('history');
        //$dql->select('COUNT(history) as historycount');
        $dql->select('history');
        $dql->leftJoin("history.eventtype", "eventtype");

        $dql->innerJoin("history.orderinfo", "orderinfo");
        $dql->innerJoin("orderinfo.provider", "provider");
        $dql->leftJoin("orderinfo.proxyuser", "proxyuser");

        //$dql->innerJoin("history.orderProvider", "provider");
        //$dql->leftJoin("history.orderProxyuser", "proxyuser");
        //$dql->leftJoin("history.orderInstitution", "institution");

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

        if( !$this->sc->getToken() ) {
            return "";
        }

        $criteriastr = "eventtype.name='Comment Added' ";

        if( $flag != 'all_comments' ) {
            $criteriastr = $criteriastr . "AND history.viewed is NULL ";
        } else {
            return $criteriastr;
        }

        $user = $this->sc->getToken()->getUser();

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

    public function getInstitutionQueryCriterion($user) {
        $securityUtil = $this->container->get('order_security_utility');
        $institutions = $securityUtil->getUserPermittedInstitutions($user);
        $instStr = "";
        foreach( $institutions as $inst ) {
            if( $instStr != "" ) {
                $instStr = $instStr . " OR ";
            }
            $instStr = $instStr . 'orderinfo.institution='.$inst->getId();
            //$instStr = $instStr . 'institution='.$inst->getId();
        }
        if( $instStr == "" ) {
            $instStr = "1=0";
        }
        return $instStr;
    }

    public function addInstitutionQueryCriterion($user,$criteriastr) {
        $instStr = $this->getInstitutionQueryCriterion($user);
        if( $instStr != "" ) {
            if( $criteriastr != "" ) {
                $criteriastr = $criteriastr . " AND (" . $instStr . ") ";
            } else {
                $criteriastr = $criteriastr . " (" . $instStr . ") ";
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
            $mrntype = $em->getRepository('OlegOrderformBundle:MrnType')->findOneById( $dataquality['mrntype'] );

            //set correct accessiontype
            $accessiontype = $em->getRepository('OlegOrderformBundle:AccessionType')->findOneById( $dataquality['accessiontype'] );

            $dataqualityObj = new DataQualityMrnAcc();
            $dataqualityObj->setDescription($dataquality['description']);
            $dataqualityObj->setMrn($dataquality['mrn']);
            $dataqualityObj->setMrntype($mrntype);
            $dataqualityObj->setAccession($dataquality['accession']);
            $dataqualityObj->setAccessiontype($accessiontype);

            $dataqualityObj->setOrderinfo($entity);
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
        $userUrl = $router->generate('scan_showuser', array('id' => $user->getId()),true);
        $homeUrl = $router->generate('main_common_home',array(),true);
        $sysemail = $this->container->getParameter('default_system_email');
        $flashBag = $this->container->get('session')->getFlashBag();

        $emailUtil = new EmailUtil();

        if( $this->sc->isGranted('ROLE_SCANORDER_PROCESSOR') || $this->sc->isGranted('ROLE_SCANORDER_ADMIN') ) {

            $webUserUrl = "<a href=".$userUrl.">profile</a>";
            $msg =  "Please add at least one institution to your ".$webUserUrl." in order to be able to place orders.";

        } else {

            $webUserUrl = "<a href=".$userUrl.">profile</a>";

            $msg =  'Please contact the System Administrator by emailing '.$sysemail.' and request to add to your '.$webUserUrl.
                    ' the name of the Institution that employs you (such as "Weill Cornell Medical College"). '.
                    'You will not be able to place orders until the system administrator completes this step.';

            //send Email
            $subject = "User ".$user." needs to have an Institution added to their profile";

            $message =  "Please visit the ".$userUrl." of user ".$user.
                        " and add the name of the institution that employs this user to enable her or him to place orders & and to view patient information known to that institution:\r\n\r\n".
                        $userUrl."\r\n".
                        "\r\n\r\n".
                        "Agent Smith\r\n".
                        "Virtual Keeper of O R D E R: ".$homeUrl."\r\n".
                        "Weill Cornell Medical College";

            $emailUtil->sendEmail($sysemail, $subject, $message, $this->em);

        }
        $flashBag->add(
            'warning',
            $msg
        );

    }


    public function generateUserFilterOptions( $user ) {

        $choicesServ = array(
            "My Orders"=>"My Orders",
            "Where I am the Submitter"=>"Where I am the Submitter",
            "Where I am the Ordering Provider"=>"Where I am the Ordering Provider",
            "Where I am the Course Director"=>"Where I am the Course Director",
            "Where I am the Principal Investigator"=>"Where I am the Principal Investigator",
            "Where I am the Amendment Author"=>"Where I am the Amendment Author"
        );

        if( is_object($user) && $user instanceof User ) {
            $secUtil = $this->container->get('order_security_utility');
            $services = $secUtil->getUserScanorderServices($user);
            foreach( $services as $service ) {
                $choicesServ[$service->getId()] = "All ".$service->getName()." Orders";
            }
        }

        return $choicesServ;
    }

    //get the last order
    public function getPreviousOrderinfo( $categoryStr=null ) {
        $previousOrder = null;

        $user = $this->sc->getToken()->getUser();

//        $previousOrders = $this->em->getRepository('OlegOrderformBundle:OrderInfo')->findBy(
//            array('provider'=>$user),
//            array('orderdate' => 'DESC'),
//            1                                   //limit to one result
//        );

        $repository = $this->em->getRepository('OlegOrderformBundle:OrderInfo');
        $dql =  $repository->createQueryBuilder("orderinfo");
        $dql->leftJoin('orderinfo.provider','provider');
        $dql->leftJoin('orderinfo.messageCategory','category');

        $criteria = "provider=".$user->getId(); //." AND category LIKE '%".$categoryStr."%'";
        if( $categoryStr && $categoryStr != "" ) {
            $criteria = $criteria . " AND category.name LIKE '%".$categoryStr."%'";
        }

        $dql->where($criteria);
        $dql->orderBy('orderinfo.orderdate','DESC');

        $query = $this->em->createQuery($dql)->setMaxResults(1);
        $previousOrders = $query->getResult();

        if( $previousOrders && count($previousOrders) > 0 ) {
            $previousOrder = $previousOrders[0];
        }

        //echo "prev order=".$previousOrder."<br>";

        return $previousOrder;
    }


    public function getOrderReturnLocations( $orderinfo, $providerid=null, $proxyid=null ) {

        $provider = null;
        $proxy = null;

        if( $orderinfo ) {
            $provider = $orderinfo->getProvider();
            $proxy = $orderinfo->getProxyuser();
        } else {
            if( $providerid && $providerid != "" ) {
                $provider = $this->em->getRepository('OlegUserdirectoryBundle:User')->find($providerid);
            }
            if( $proxyid && $proxyid != "" && $proxyid != $providerid ) {
                $proxy = $this->em->getRepository('OlegUserdirectoryBundle:User')->find($proxyid);
            }
        }

        //get default returnLocation option
        //the top three choices should be the Submitter's Main Office location (from the user who is logged in; selected by default),
        //followed by the "Surgical Pathology Filing Room",
        //followed by the Ordering Provider's Main Office location, and then everyone else in alphabetical order.

        $mainOfficeLocation = null;
        $surgicalPathLocation = null;
        $orderingProviderMainOfficeLocation = null;

        //1) get submitter location
        if( $provider ) {
            $mainOfficeLocation = $provider->getMainLocation();
        }

        //2) get "Surgical Pathology Filing Room" location
        $repository = $this->em->getRepository('OlegUserdirectoryBundle:Location');
        $dql =  $repository->createQueryBuilder("location");
        $dql->select('location');
        $dql->leftJoin("location.locationTypes", "locationTypes");
        $dql->where("locationTypes.name = 'Surgical Pathology Filing Room'");
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

        $res = array();
        $res['data'] = $defaultLocation;
        $res['preferred_choices'] = $preferredChoices;

        return $res;
    }


}