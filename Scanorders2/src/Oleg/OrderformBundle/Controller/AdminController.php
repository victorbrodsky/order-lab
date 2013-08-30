<?php

namespace Oleg\OrderformBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Oleg\OrderformBundle\Entity\StainList;
use Oleg\OrderformBundle\Entity\OrganList;
use Oleg\OrderformBundle\Entity\ProcedureList;
use Oleg\OrderformBundle\Helper\FormHelper;
use Symfony\Component\HttpFoundation\Session\Session;

/**
 * StainList controller.
 *
 * @Route("/admin")
 */
class AdminController extends Controller
{

    /**
     * Admin Page
     *
     * @Route("/", name="admin_index")
     * @Method("GET")
     * @Template("OlegOrderformBundle:Admin:index.html.twig")
     */
    public function indexAction()
    {

        return $this->render('OlegOrderformBundle:Admin:index.html.twig');
    }


    /**
     * Populate DB
     *
     * @Route("/genstain", name="generate_stain")
     * @Method("GET")
     * @Template()
     */
    public function generateStainAction()
    {

        if( false === $this->get('security.context')->isGranted('ROLE_ADMIN') ) {
            return $this->render('OlegOrderformBundle:Security:login.html.twig');
        }

        $helper = new FormHelper();
        $stains = $helper->getStains();

        $username = $this->get('security.context')->getToken()->getUser();

        $em = $this->getDoctrine()->getManager();
        $entities = $em->getRepository('OlegOrderformBundle:StainList')->findAll();

        if( $entities ) {

            $this->get('session')->getFlashBag()->add(
                'notice',
                'This table is already exists!'
            );

            return $this->redirect($this->generateUrl('admin_index'));
        }

        $count = 0;
        foreach( $stains as $stain ) {
            $stainList = new StainList();
            $stainList->setCreator( $username );
            $stainList->setCreatedate( new \DateTime() );
            $stainList->setName( $stain );
            $stainList->setType('original');

            $em = $this->getDoctrine()->getManager();
            $em->persist($stainList);
            $em->flush();
            $count++;
        }

        $this->get('session')->getFlashBag()->add(
                    'notice',
                    'Created '.$count. ' stain records'
                );

        return $this->redirect($this->generateUrl('stainlist'));

    }


    /**
     * Populate DB
     *
     * @Route("/genorgan", name="generate_organ")
     * @Method("GET")
     * @Template()
     */
    public function generateOrganAction()
    {

        if( false === $this->get('security.context')->isGranted('ROLE_ADMIN') ) {
            return $this->render('OlegOrderformBundle:Security:login.html.twig');
        }

        $em = $this->getDoctrine()->getManager();
        $entities = $em->getRepository('OlegOrderformBundle:OrganList')->findAll();

        if( $entities ) {

            $this->get('session')->getFlashBag()->add(
                'notice',
                'This table is already exists!'
            );

            return $this->redirect($this->generateUrl('admin_index'));
        }

        $helper = new FormHelper();
        $organs = $helper->getSourceOrgan();

        $username = $this->get('security.context')->getToken()->getUser();

        $count = 0;
        foreach( $organs as $organ ) {
            $list = new OrganList();
            $list->setCreator( $username );
            $list->setCreatedate( new \DateTime() );
            $list->setName( $organ );
            $list->setType('original');

            $em = $this->getDoctrine()->getManager();
            $em->persist($list);
            $em->flush();
            $count++;
        }

        $this->get('session')->getFlashBag()->add(
            'notice',
            'Created '.$count. ' organ records'
        );

        return $this->redirect($this->generateUrl('organlist'));

    }



    /**
     * Populate DB
     *
     * @Route("/genprocedure", name="generate_procedure")
     * @Method("GET")
     * @Template()
     */
    public function generateProcedureAction()
    {

        if( false === $this->get('security.context')->isGranted('ROLE_ADMIN') ) {
            return $this->render('OlegOrderformBundle:Security:login.html.twig');
        }

        $em = $this->getDoctrine()->getManager();
        $entities = $em->getRepository('OlegOrderformBundle:ProcedureList')->findAll();

        if( $entities ) {

            $this->get('session')->getFlashBag()->add(
                'notice',
                'This table is already exists!'
            );

            return $this->redirect($this->generateUrl('admin_index'));
        }

        $helper = new FormHelper();
        $organs = $helper->getProcedure();

        $username = $this->get('security.context')->getToken()->getUser();

        $count = 0;
        foreach( $organs as $organ ) {
            $list = new ProcedureList();
            $list->setCreator( $username );
            $list->setCreatedate( new \DateTime() );
            $list->setName( $organ );
            $list->setType('original');

            $em = $this->getDoctrine()->getManager();
            $em->persist($list);
            $em->flush();
            $count++;
        }

        $this->get('session')->getFlashBag()->add(
            'notice',
            'Created '.$count. ' procedure records'
        );

        return $this->redirect($this->generateUrl('procedurelist'));

    }

}
