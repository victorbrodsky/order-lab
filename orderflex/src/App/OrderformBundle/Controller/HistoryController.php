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

namespace App\OrderformBundle\Controller;

use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use App\UserdirectoryBundle\Controller\OrderAbstractController;
//use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
//use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Routing\Annotation\Route;
use App\OrderformBundle\Entity\History;
use App\OrderformBundle\Form\HistoryType;
use App\OrderformBundle\Helper\OrderUtil;
use App\UserdirectoryBundle\Util\UserUtil;

/**
 * History controller.
 */
class HistoryController extends OrderAbstractController
{

//    /**
//     * Lists all History entities.
//     *
//     * @Route("/scan-order/progress-and-comments/", name="history", methods={"GET"})
//     * @Template()
//     */
//    public function indexAction()
//    {
//
//        if( false === $this->get('security.authorization_checker')->isGranted('ROLE_SCANORDER_PROCESSOR') ) {
//            return $this->redirect( $this->generateUrl('scan-nopermission') );
//        }
//
//        $em = $this->getDoctrine()->getManager();
//
//        //$entities = $em->getRepository('AppOrderformBundle:History')->findAll();
//        $repository = $this->getDoctrine()->getRepository('AppOrderformBundle:History');
//        $dql =  $repository->createQueryBuilder("hist");
//        $dql->innerJoin("hist.message", "message");
//
//        /////////// institution ///////////
//        $instStr = "";
//        $user = $this->get('security.token_storage')->getToken()->getUser();
//        foreach( $user->getInstitutions() as $inst ) {
//            if( $instStr != "" ) {
//                $instStr = $instStr . " OR ";
//            }
//            $instStr = $instStr . 'message.institution='.$inst->getId();
//        }
//        if( $instStr == "" ) {
//            $instStr = "1=0";
//        }
//        //echo "instStr=".$instStr."<br>";
//        $dql->where($instStr);
//        /////////// EOF institution ///////////
//
//        //echo "dql=".$dql;
//        $query = $em->createQuery($dql);
//        $entities = $query->getResult();
//
//        if( count($entities) > 0 ) {
//            $roles = $em->getRepository('AppUserdirectoryBundle:Roles')->findAll();
//            $rolesArr = array();
//            foreach( $roles as $role ) {
//                $rolesArr[$role->getName()] = $role->getAlias();
//            }
//        } else {
//            $rolesArr = '';
//        }
//
//        return array(
//            'entities' => $entities,
//            'roles' => $rolesArr,
//        );
//    }

    /**
     * Creates a new History entity.
     *
     * @Route("/scan-order/progress-and-comments/new", name="history_create", methods={"POST"})
     * @Template("AppOrderformBundle/History/new.html.twig")
     */
    public function createAction(Request $request)
    {
        $entity = new History();
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('history_show', array('id' => $entity->getId())));
        }

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
    * Creates a form to create a History entity.
    *
    * @param History $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createCreateForm(History $entity)
    {
        $form = $this->createForm(HistoryType::class, $entity, array(
            'action' => $this->generateUrl('history_create'),
            'method' => 'POST',
        ));

        $form->add('submit', SubmitType::class, array('label' => 'Create'));

        return $form;
    }

    /**
     * Displays a form to create a new History entity.
     *
     * @Route("/scan-order/progress-and-comments/new", name="history_new", methods={"GET"})
     * @Template()
     */
    public function newAction()
    {
        $entity = new History();
        $form   = $this->createCreateForm($entity);

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * Finds and displays a History entity.
     *
     * @Route("/scan-order/progress-and-comments/{id}", name="history_show", methods={"GET"}, requirements={"id" = "\d+"})
     * @Template()
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('AppOrderformBundle:History')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find History entity.');
        }

        $securityUtil = $this->get('user_security_utility');
        $user = $this->get('security.token_storage')->getToken()->getUser();
        if( $entity && !$securityUtil->hasUserPermission($entity->getMessage(),$user) ) {
            return $this->redirect( $this->generateUrl('scan-nopermission') );
        }

        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Displays a form to edit an existing History entity.
     *
     * @Route("/scan-order/progress-and-comments/{id}/edit", name="history_edit", methods={"GET"}, requirements={"id" = "\d+"})
     * @Template()
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('AppOrderformBundle:History')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find History entity.');
        }

        $securityUtil = $this->get('user_security_utility');
        $user = $this->get('security.token_storage')->getToken()->getUser();
        if( $entity && !$securityUtil->hasUserPermission($entity->getMessage(),$user) ) {
            return $this->redirect( $this->generateUrl('scan-nopermission') );
        }

        $editForm = $this->createEditForm($entity);
        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
    * Creates a form to edit a History entity.
    *
    * @param History $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createEditForm(History $entity)
    {
        $form = $this->createForm(HistoryType::class, $entity, array(
            'action' => $this->generateUrl('history_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));

        $form->add('submit', SubmitType::class, array('label' => 'Update'));

        return $form;
    }
    /**
     * Edits an existing History entity.
     *
     * @Route("/scan-order/progress-and-comments/{id}", name="history_update", methods={"PUT"}, requirements={"id" = "\d+"})
     * @Template("AppOrderformBundle/History/edit.html.twig")
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('AppOrderformBundle:History')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find History entity.');
        }

        $securityUtil = $this->get('user_security_utility');
        $user = $this->get('security.token_storage')->getToken()->getUser();
        if( $entity && !$securityUtil->hasUserPermission($entity->getMessage(),$user) ) {
            return $this->redirect( $this->generateUrl('scan-nopermission') );
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $em->flush();

            return $this->redirect($this->generateUrl('history_edit', array('id' => $id)));
        }

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }
    /**
     * Deletes a History entity.
     *
     * @Route("/scan-order/progress-and-comments/{id}", name="history_delete", methods={"DELETE"}, requirements={"id" = "\d+"})
     */
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('AppOrderformBundle:History')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find History entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('history'));
    }

    /**
     * Creates a form to delete a History entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('history_delete', array('id' => $id)))
            ->setMethod('DELETE')
            ->add('submit', SubmitType::class, array('label' => 'Delete'))
            ->getForm()
        ;
    }




    //History of Message
    /**
     * Finds and displays a History entity for Message.
     *
     * @Route("/scan-order/{id}/progress-and-comments", name="history_message_show", methods={"GET"}, requirements={"id" = "\d+"})
     * @Template("AppOrderformBundle/History/index.html.twig")
     */
    public function showHistoryMessageAction($id)
    {

        if( false === $this->get('security.authorization_checker')->isGranted('ROLE_SCANORDER_SUBMITTER') &&
            false === $this->get('security.authorization_checker')->isGranted('ROLE_SCANORDER_ORDERING_PROVIDER')
        )
        {
            return $this->redirect( $this->generateUrl('scan-nopermission') );
        }

        $em = $this->getDoctrine()->getManager();
        $user = $this->get('security.token_storage')->getToken()->getUser();

        $entities = $em->getRepository('AppOrderformBundle:History')->findByCurrentid($id,array('changedate'=>'DESC'));
        //echo "hist count=".count($entities)."<br>";

        $securityUtil = $this->get('user_security_utility');
        if( count($entities)>0 && !$securityUtil->hasUserPermission($entities[0]->getMessage(),$user) ) {
            return $this->redirect( $this->generateUrl('scan-nopermission') );
        }

        $viewcount = 0;

        foreach( $entities as $entity ) {

            if( $entity->getEventType()->getName() != 'Comment Added' ) {
                continue;
            }

            if( $entity->getViewed() ) {
                continue;
            }

            //echo $entity->getId().", eventtype=".$entity->getEventtype().", note=".$entity->getNote().": ".$entity->getProvider()->getId()."?=".$user->getId()."<br>";

            //don't mark with view comments placed by the current user
            if( $entity->getProvider()->getId() == $user->getId() ) {
                continue;
            }

            $provider = $entity->getProvider();

            $viewed = false;

            if( $this->get('security.authorization_checker')->isGranted('ROLE_SCANORDER_PROCESSOR') ) {

                //don't mark with view comments placed by PROCESSOR to User and viewed by another PROCESSOR (order->provider does not have role PROCESSOR)
//                $orderprovider = $entity->getMessage()->getProvider();
//                echo $orderprovider."<br>";
//                if( $orderprovider->hasRole('ROLE_SCANORDER_ADMIN') || $orderprovider->hasRole('ROLE_SCANORDER_PROCESSOR') ) {
//                    //
//                } else {
//                    echo "not viewed! ";
//                    continue;
//                }

//                //don't mark with view if: current Admin is not author of the comment
//                if( $entity->getProvider()->getId() != $user->getId() ) {
//                    echo "not viewed! ";
//                    continue;
//                }

                //echo " #######viewed! ";


                //processor can see only histories created by user without processor role
//                if( !$entity->hasProviderRole('ROLE_SCANORDER_PROCESSOR') ) {
//                    $viewed = true;
//                }

                $viewed = true;

            } else {
                //submitter can see only histories created by user with processor or admin role for history's orders belongs to this user as provider or proxy
                if( $entity->hasProviderRole('ROLE_SCANORDER_PROCESSOR') || $entity->hasProviderRole('ROLE_SCANORDER_ADMIN') ) {
                    //echo "role admin! <br>";
                    $viewed = true;
                }
            }

            //echo "admin role=".$entity->hasProviderRole('ROLE_SCANORDER_ADMIN')."<br>";
            //echo "viewed=".$viewed." <br>";

            //if the user the same as author of comment => $viewed = false ( proxy user will make this history as viewed! )
            if( $viewed && $provider->getId() == $user->getId() ) {
                $viewed = false;
            }

            if( $viewed ) {
                //echo 'set as viewed! <br>';
                //exit();

                $entity->setViewed($user);
                $entity->setVieweddate( new \DateTime() );
                $em->persist($entity);
                $em->flush();

                $viewcount++;
            }
        }//foreach

//        if( !$entities || count($entities) == 0 ) {
//            throw $this->createNotFoundException('Unable to find History entity.');
//        }

        $message = $em->getRepository('AppOrderformBundle:Message')->findOneByOid($id);

        //if( $viewcount > 0 && $message->getProvider()->getId() != $user->getId()) {
        if( 1 ) {
            //add a new record in history
            $history = new History();
            $history->setMessage($message);
            $history->setProvider($user);
            $history->setCurrentid($id);
            //$history->setNewid($id);
            $history->setCurrentstatus($message->getStatus());
            //$history->setNewstatus($message->getStatus());
            //$history->setNote($text_value);
            //$history->setSelectednote($selectednote);
            $history->setRoles($user->getRoles());
            //$history->setViewed($user);
            //$history->setVieweddate( new \DateTime() );

            $eventtype = $em->getRepository('AppOrderformBundle:ProgressCommentsEventTypeList')->findOneByName('Progress & Comments Viewed');
            $history->setEventtype($eventtype);

            $em->persist($history);
            $em->flush();
            //echo "viewed !!! <br>";
        } else {
            //echo "not viewed <br>";
        }

        if( count($entities) > 0 ) {
            $roles = $em->getRepository('AppUserdirectoryBundle:Roles')->findAll();
            $rolesArr = array();
            foreach( $roles as $role ) {
                $rolesArr[$role->getName()] = $role->getAlias();
            }
        } else {
            $rolesArr = '';
        }

        $processorComments = $em->getRepository('AppOrderformBundle:ProcessorComments')->findAll();

        $curdatetime = new \DateTime();

        return array(
            'entities' => $entities,
            'orderid' => $id,
            'roles' => $rolesArr,
            'comments' => $processorComments
        );
    }


    /**
     * Finds and displays a History entity for Message.
     *
     * @Route("/scan-order/progress-and-comments/create", name="history_message_new", methods={"POST"})
     * @Template("AppOrderformBundle/History/index.html.twig")
     */
    public function createHistoryMessageAction(Request $request)
    {

        $text_value = $request->request->get('text');
        $id = $request->request->get('id');
        $selectednote = $request->request->get('selectednote');
        //echo "id=".$id.", text_value=".$text_value."<br>";

        $res = 1;

        if( $text_value == "" ) {
            $res = 'Comment was not provided';
        } else {

            $em = $this->getDoctrine()->getManager();
            $user = $this->get('security.token_storage')->getToken()->getUser();
            $message = $em->getRepository('AppOrderformBundle:Message')->findOneByOid($id);

            $history = new History();
            $history->setMessage($message);
            $history->setProvider($user);
            $history->setCurrentid($id);
            //$history->setNewid($id);
            $history->setCurrentstatus($message->getStatus());
            //$history->setNewstatus($message->getStatus());
            $history->setNote($text_value);
            $history->setSelectednote($selectednote);
            $history->setRoles($user->getRoles());

            $eventtype = $em->getRepository('AppOrderformBundle:ProgressCommentsEventTypeList')->findOneByName('Comment Added');
            $history->setEventtype($eventtype);

            //echo "ok";
            $em->persist($history);
            $em->flush();

        }

        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');
        $response->setContent(json_encode($res));
        return $response;
    }


    /**
     * Finds and displays a History entity for Message.
     *
     * @Route("/scan-order/progress-and-comments/notviewedcomments", name="history_not_viewed_comments", methods={"GET"})
     * @Template("AppOrderformBundle/History/index.html.twig")
     */
    public function notViewedCommentsAction()
    {
        $comments = 0;

        $orderUtil = $this->get('scanorder_utility');
        $histories = $orderUtil->getNotViewedComments();

        if( $histories ) {
            $comments = count($histories);
        } else {
            //echo "no res found <br>";
        }

        $response = new Response();
        $response->setContent($comments);

        return $response;
    }

    /**
     * Finds and displays a History entity for Message.
     *
     * @Route("/scan-order/progress-and-comments/notviewedadmincomments", name="history_not_viewed_admincomments", methods={"GET"})
     * @Template("AppOrderformBundle/History/index.html.twig")
     */
    public function notViewedAdminCommentsAction()
    {
        $comments = 0;

        $em = $this->getDoctrine()->getManager();
        $orderUtil = $this->get('scanorder_utility');	
        $histories = $orderUtil->getNotViewedComments('admin');

        if( $histories ) {
            $comments = count($histories);
        } else {
            //echo "no res found <br>";
        }

        $response = new Response();
        $response->setContent($comments);

        return $response;
    }



}
