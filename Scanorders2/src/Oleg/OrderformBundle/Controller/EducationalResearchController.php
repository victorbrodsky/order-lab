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

namespace Oleg\OrderformBundle\Controller;

use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Oleg\OrderformBundle\Entity\Educational;
use Oleg\OrderformBundle\Form\EducationalType;
use Oleg\OrderformBundle\Entity\Research;
use Oleg\OrderformBundle\Form\ResearchType;
use Oleg\OrderformBundle\Entity\History;

/**
 * Educational and Research controller.
 */
class EducationalResearchController extends Controller {


    /**
     * Displays a form to edit an existing entity.
     *
     * @Route("/educational/{id}/edit", name="educational_edit")
     * @Route("/research/{id}/edit", name="research_edit")
     * @Method("GET")
     */
    public function editAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $routeName = $request->get('_route');
        //echo "routeName=".$routeName; //mrntype

        $pieces = explode("_", $routeName);
        $type = $pieces[0];
        //echo "type=".$type."<br>";
        //exit();

        $entity = $em->getRepository('OlegOrderformBundle:'.$type)->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find '.$type.' entity.');
        }

        $editForm = $this->createEditForm($entity);
        //$deleteForm = $this->createDeleteForm($id);

        return $this->render('OlegOrderformBundle:'.$type.':edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            //'cycle' => 'show'
        ));
    }

    /**
     * Edits an existing entity.
     *
     * @Route("/educational/{id}/edit", name="educational_update")
     * @Route("/research/{id}/edit", name="research_update")
     * @Method("PUT")
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $routeName = $request->get('_route');
        $pieces = explode("_", $routeName);
        $type = $pieces[0];

        //$entity = $em->getRepository('OlegOrderformBundle:'.$type)->find($id);
        $entity = $em->getRepository('OlegOrderformBundle:'.$type)->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find '.$type.' entity.');
        }

        $entityHolder = $em->getRepository('OlegOrderformBundle:'.$type)->find($id);
        $editForm = $this->createEditForm($entityHolder);

        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            //exit("form is valid!");

//            echo "user wrappers count=".count($entity->getUserWrappers())."<br>";
//            foreach( $entity->getUserWrappers() as $userWrapper ) {
//                echo "userWrapper=".$userWrapper->getId().", name=".$userWrapper->getName().", user=".$userWrapper->getUser()."<br>";
//                echo "research id=".$entity->getId()."<br>";
//            }

            $em->persist($entity);
            $em->flush();

            $orderoid = $entity->getMessage()->getOid();


            if( $routeName == "educational_update" ) {
                $principalDirector = "";
                $principalsArr = array();
                if( $entity->getUserWrappers() ) {
                    $principals = $entity->getUserWrappers();
                    foreach( $principals as $principal ) {
                        if( $principal->getUser() ) {
                            $principalsArr[] = $principal->getName();
                        }
                    }
                }

                if( $entity->getPrimaryPrincipal() ) {
                    $principalDirector = $entity->getPrimaryPrincipal();
                }

                $msg = "Values saved for Order ".$orderoid.": User Associations for Course Director(s) = ".implode(", ", $principalsArr)."; Primary Course Director = ".$principalDirector;
                $url = $this->generateUrl( 'educational_edit', array('id' => $id) );
                $reviewLink = '<br> <a href="'.$url.'">Back to Data Review</a>';
            }
            if( $routeName == "research_update" ) {

                $principalInvestigator = "";
                $principalsArr = array();
                if( $entity->getUserWrappers() ) {
                    $principals = $entity->getUserWrappers();
                    foreach( $principals as $principal ) {
                        if( $principal->getUser() ) {
                            $principalsArr[] = $principal->getName();
                        }
                    }
                }

                if( $entity->getPrimaryPrincipal() ) {
                    $principalInvestigator = $entity->getPrimaryPrincipal();
                }

                $msg = "Values saved for Order ".$orderoid.": User Associations for Principal Investigator(s) = ".implode(", ", $principalsArr)."; Primary Principal Investigator = ".$principalInvestigator;
                $url = $this->generateUrl( 'scan-order-data-review-full', array('id' => $orderoid) );
                $reviewLink = '<br> <a href="'.$url.'">Back to Data Review</a>';
            }

            $this->get('session')->getFlashBag()->add(
                'status-changed',
                $msg
            );

            //add event log to History
            $user = $this->get('security.token_storage')->getToken()->getUser();
            $history = new History();

            $eventtype = $em->getRepository('OlegOrderformBundle:ProgressCommentsEventTypeList')->findOneByName('Data Reviewed');
            $history->setEventtype($eventtype);

            $history->setMessage($entity->getMessage());
            $history->setProvider($user);
            $history->setCurrentid($entity->getMessage()->getOid());
            $history->setCurrentstatus($entity->getMessage()->getStatus());
            $history->setNote($msg.$reviewLink);
            $history->setRoles($user->getRoles());
            $em->persist($history);
            $em->flush();


            //return $this->redirect($this->generateUrl($type.'_show', array('id' => $id)));
            return $this->redirect($this->generateUrl('scan-order-data-review-full', array('id' => $orderoid)));
        } else {
            //exit("form is not valid ???");
        }

        return $this->render('OlegOrderformBundle:'.$type.':edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            //'cycle' => 'show'
        ));

    }

    private function createEditForm($entity, $cycle = null) {

        $params = array(
            'type' => 'SingleObject',
            'em' => $this->getDoctrine()->getManager(),
            'container' => $this->container
        );

        $disable = false;

        if( $cycle == 'show' ) {
            $disable = true;
        }

        if( $entity instanceof Educational ) {
            //$typeform = new EducationalType($params);
            $typeform = EducationalType::class;
            $type = "educational";
            $btnMsg = "Course Director";
        }

        if( $entity instanceof Research ) {
            $typeform = ResearchType::class;
            $type = "research";
            $btnMsg = "Principal Investigator";
        }

        $form = $this->createForm( $typeform, $entity, array(
            'form_custom_value' => $params,
            'action' => $this->generateUrl($type.'_update', array('id' => $entity->getId())),
            'method' => 'PUT',
            'disabled' => $disable
        ));

        if( $cycle != 'show' ) {
            $form->add('submit', SubmitType::class, array('label' => 'Save '.$btnMsg.' Information', 'attr' => array('class' => 'btn btn-primary')));
        }

        return $form;
    }
}
