<?php

namespace Oleg\OrderformBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Oleg\OrderformBundle\Entity\History;
use Oleg\OrderformBundle\Form\HistoryType;
use Oleg\OrderformBundle\Helper\OrderUtil;

/**
 * History controller.
 *
 * @Route("/history")
 */
class HistoryController extends Controller
{

    /**
     * Lists all History entities.
     *
     * @Route("/", name="history")
     * @Method("GET")
     * @Template()
     */
    public function indexAction()
    {

        if( false === $this->get('security.context')->isGranted('ROLE_PROCESSOR') ) {
            return $this->redirect( $this->generateUrl('logout') );
        }

        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('OlegOrderformBundle:History')->findAll();

        if( count($entities) > 0 ) {
            $roles = $em->getRepository('OlegOrderformBundle:Roles')->findAll();
            $rolesArr = array();
            foreach( $roles as $role ) {
                $rolesArr[$role->getName()] = $role->getAlias();
            }
        } else {
            $rolesArr = '';
        }

        return array(
            'entities' => $entities,
            'roles' => $rolesArr
        );
    }
    /**
     * Creates a new History entity.
     *
     * @Route("/", name="history_create")
     * @Method("POST")
     * @Template("OlegOrderformBundle:History:new.html.twig")
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
        $form = $this->createForm(new HistoryType(), $entity, array(
            'action' => $this->generateUrl('history_create'),
            'method' => 'POST',
        ));

        $form->add('submit', 'submit', array('label' => 'Create'));

        return $form;
    }

    /**
     * Displays a form to create a new History entity.
     *
     * @Route("/new", name="history_new")
     * @Method("GET")
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
     * @Route("/{id}", name="history_show")
     * @Method("GET")
     * @Template()
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('OlegOrderformBundle:History')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find History entity.');
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
     * @Route("/{id}/edit", name="history_edit")
     * @Method("GET")
     * @Template()
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('OlegOrderformBundle:History')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find History entity.');
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
        $form = $this->createForm(new HistoryType(), $entity, array(
            'action' => $this->generateUrl('history_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));

        $form->add('submit', 'submit', array('label' => 'Update'));

        return $form;
    }
    /**
     * Edits an existing History entity.
     *
     * @Route("/{id}", name="history_update")
     * @Method("PUT")
     * @Template("OlegOrderformBundle:History:edit.html.twig")
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('OlegOrderformBundle:History')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find History entity.');
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
     * @Route("/{id}", name="history_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('OlegOrderformBundle:History')->find($id);

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
            ->add('submit', 'submit', array('label' => 'Delete'))
            ->getForm()
        ;
    }




    //History of OrderInfo
    /**
     * Finds and displays a History entity for OrderInfo.
     *
     * @Route("/order/{id}", name="history_orderinfo_show")
     * @Method("GET")
     * @Template("OlegOrderformBundle:History:index.html.twig")
     */
    public function showHistoryOrderinfoAction($id)
    {

        if( false === $this->get('security.context')->isGranted('ROLE_SUBMITTER') &&
            false === $this->get('security.context')->isGranted('ROLE_ORDERING_PROVIDER') &&
            false === $this->get('security.context')->isGranted('ROLE_EXTERNAL_SUBMITTER') &&
            false === $this->get('security.context')->isGranted('ROLE_EXTERNAL_ORDERING_PROVIDER')
        )
        {
            return $this->redirect( $this->generateUrl('logout') );
        }

        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('OlegOrderformBundle:History')->findByCurrentid($id,array('changedate'=>'DESC'));

        foreach( $entities as $entity ) {

            if( $entity->getViewed() ) {
                //echo "viewed! ";
                continue;
            }

            $provider = $entity->getProvider();

            $user = $this->get('security.context')->getToken()->getUser();

            $viewed = false;

            if( $this->get('security.context')->isGranted('ROLE_PROCESSOR') ) {
                //processor can see only histories created by user without processor role
                if( !$entity->hasProviderRole('ROLE_PROCESSOR') ) {
                    $viewed = true;
                }
            } else {
                //submitter can see only histories created by user with processor or admin role for history's orders belongs to this user as provider or proxy
                if( $entity->hasProviderRole('ROLE_PROCESSOR') || $entity->hasProviderRole('ROLE_ADMIN') ) {
                    //echo "role admin! <br>";
                    $viewed = true;
                }
            }

            //echo "admin role=".$entity->hasProviderRole('ROLE_ADMIN')."<br>";
            //echo "viewed=".$viewed." <br>";

            //if the user the same as author of comment => $viewed = false ( proxy user will make this history as viewed! )
            if( $viewed && $provider->getId() == $user->getId() ) {
                $viewed = false;
            }

            if( $viewed ) {
                $entity->setViewed($user);
                $entity->setVieweddate( new \DateTime() );
                $em->persist($entity);
                $em->flush();
            }
        }

//        if( !$entities || count($entities) == 0 ) {
//            throw $this->createNotFoundException('Unable to find History entity.');
//        }

        if( count($entities) > 0 ) {
            $roles = $em->getRepository('OlegOrderformBundle:Roles')->findAll();
            $rolesArr = array();
            foreach( $roles as $role ) {
                $rolesArr[$role->getName()] = $role->getAlias();
            }
        } else {
            $rolesArr = '';
        }

        return array(
            'entities'      => $entities,
            'orderid'      => $id,
            'roles' => $rolesArr
        );
    }


    /**
     * Finds and displays a History entity for OrderInfo.
     *
     * @Route("/order/create/", name="history_orderinfo_new")
     * @Method("POST")
     * @Template("OlegOrderformBundle:History:index.html.twig")
     */
    public function createHistoryOrderinfoAction(Request $request)
    {

        $text_value = $request->request->get('text');
        $id = $request->request->get('id');
        //echo "id=".$id.", text_value=".$text_value."<br>";

        $res = 1;

        if( $text_value == "" ) {
            $res = 'Comment was not provided';
        } else {

            $em = $this->getDoctrine()->getManager();
            $user = $this->get('security.context')->getToken()->getUser();
            $orderinfo = $em->getRepository('OlegOrderformBundle:OrderInfo')->findOneByOid($id);

            $history = new History();

            $history->setOrderinfo($orderinfo);
            $history->setProvider($user);
            $history->setCurrentid($id);
            //$history->setNewid($id);
            $history->setCurrentstatus($orderinfo->getStatus());
            //$history->setNewstatus($orderinfo->getStatus());
            $history->setChangedate( new \DateTime() );
            $history->setNote($text_value);
            $history->setRoles($user->getRoles());

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
     * Finds and displays a History entity for OrderInfo.
     *
     * @Route("/order/notviewedcomments/", name="history_not_viewed_comments")
     * @Method("GET")
     * @Template("OlegOrderformBundle:History:index.html.twig")
     */
    public function notViewedCommentsAction()
    {
        $comments = 0;

        $em = $this->getDoctrine()->getManager();
        $orderUtil = new OrderUtil($em);
        $histories = $orderUtil->getNotViewedComments($this->get('security.context'));

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
