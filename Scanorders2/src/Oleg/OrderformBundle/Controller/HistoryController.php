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
        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('OlegOrderformBundle:History')->findAll();

        return array(
            'entities' => $entities,
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
        ) {
            return $this->redirect( $this->generateUrl('logout') );
        }

        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('OlegOrderformBundle:History')->findByCurrentid($id);

        foreach( $entities as $entity ) {
            $provider = $entity->getProvider();
            $user = $this->get('security.context')->getToken()->getUser();
            if( //u * ( P + !P*!A)
                $provider->getId() != $user->getId() &&
                ( $this->get('security.context')->isGranted('ROLE_PROCESSOR') ) ||
                ( !$provider->hasRole('ROLE_PROCESSOR') && !$provider->hasRole('ROLE_ADMIN') && $provider->getId() != $user->getId() )
            ) {
                $entity->setViewed($user);
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
     * @Route("/order/create/{id}", name="history_orderinfo_new")
     * @Method("POST")
     * @Template("OlegOrderformBundle:History:index.html.twig")
     */
    public function createHistoryOrderinfoAction( Request $request, $id )
    {

        $data = $request->request->all();
        //var_dump($data);
        $text_value = $data['addcomment'];

        //echo "id=".$id.", text_value=".$text_value."<br>";
        //exit();

        if( $text_value == "" ) {
            $this->get('session')->getFlashBag()->add(
                'notice',
                'Comment was not provided'
            );
        } else {

            $em = $this->getDoctrine()->getManager();
            $user = $this->get('security.context')->getToken()->getUser();
            $orderinfo = $em->getRepository('OlegOrderformBundle:OrderInfo')->findOneByOid($id);

            $history = new History();

            $history->setProvider($user);
            $history->setCurrentid($id);
            //$history->setNewid($id);
            $history->setCurrentstatus($orderinfo->getStatus());
            //$history->setNewstatus($orderinfo->getStatus());
            $history->setChangedate( new \DateTime() );
            $history->setNote($text_value);

            foreach( $user->getRoles() as $role ) {
                $history->addRole($role."");
            }

            $em->persist($history);
            $em->flush();

        }

        return $this->redirect( $this->generateUrl('history_orderinfo_show',array('id'=>$id) ) );
    }


    /**
     * Finds and displays a History entity for OrderInfo.
     *
     * @Route("/order/notviewedcomments/", name="history_not_viewed_comments")
     * @Method("POST")
     * @Template("OlegOrderformBundle:History:index.html.twig")
     */
    public function notViewedCommentsAction()
    {
        $comments = 0;
        $repository = $this->getDoctrine()->getRepository('OlegOrderformBundle:History');

        if( $this->get('security.context')->isGranted('ROLE_PROCESSOR') ) {
            $res = $repository->findBy(
                array('viewed' => null)
            );
        } else {
            $user = $this->get('security.context')->getToken()->getUser();
            $res = $repository->findBy(
                array(
                    'viewed' => null,
                    //'provider' => $user
                )
            );
        }

        if( $res ) {
            $comments = count($res);
        }

        $response = new Response();
        $response->setContent($comments);

        return $response;
    }


}
