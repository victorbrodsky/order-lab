<?php

namespace Oleg\OrderformBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Oleg\OrderformBundle\Entity as Entity;
use Oleg\OrderformBundle\Form as Form;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

/**
 * @Route("/order")
 */
class SlideController extends Controller
{
    /**
     * By default, displays form to add a Slide.
     * If form has been posted, validates and adds Slide to database.
     * @return \Symfony\Bundle\FrameworkBundle\Controller\Response
     */
    /**
     * @Route("/")
     * @Template()
     */
    public function addAction()
    {
        $slide = new Entity\Slide();
        $form = $this->get('form.factory')->create(new Form\AddSlideForm(), $slide);
        $request = $this->get('request');

        if ($request->getMethod() == 'POST')
        {
            $form->bindRequest($request);
            if ($form->isValid())
            {
                $em = $this->get('doctrine')->getEntityManager();
                //$slide->setDateAdded(new \DateTime());
                $em->persist($slide);
                $em->flush();
                $this->get('session')->setFlash('notice', 'You have successfully added '
                        .$slide->getAccession().' to the database!');
                return $this->redirect($this->generateUrl('oleg_add_slide')); //oleg_add_slide
            }
        }

        return $this->render('OlegOrderformBundle:Default:add.html.twig',
            array(
                'form' => $form->createView()
            ));
    }
}

?>
