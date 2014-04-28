<?php
/**
 * Created by JetBrains PhpStorm.
 * User: oli2002
 * Date: 4/28/14
 * Time: 1:34 PM
 * To change this template use File | Settings | File Templates.
 */

namespace Oleg\OrderformBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Form\Extension\Core\DataTransformer\DateTimeToStringTransformer;

use Oleg\OrderformBundle\Entity\OrderInfo;
use Oleg\OrderformBundle\Form\OrderInfoType;
use Oleg\OrderformBundle\Entity\Patient;
use Oleg\OrderformBundle\Form\PatientType;
use Oleg\OrderformBundle\Entity\ClinicalHistory;
use Oleg\OrderformBundle\Entity\PatientMrn;
use Oleg\OrderformBundle\Entity\Procedure;
use Oleg\OrderformBundle\Form\ProcedureType;
use Oleg\OrderformBundle\Entity\Accession;
use Oleg\OrderformBundle\Form\AccessionType;
use Oleg\OrderformBundle\Entity\Part;
//use Oleg\OrderformBundle\Entity\DiffDiagnoses;
use Oleg\OrderformBundle\Entity\RelevantScans;
use Oleg\OrderformBundle\Entity\SpecialStains;
use Oleg\OrderformBundle\Form\PartType;
use Oleg\OrderformBundle\Entity\Block;
use Oleg\OrderformBundle\Form\BlockType;
use Oleg\OrderformBundle\Entity\Slide;
use Oleg\OrderformBundle\Form\SlideType;
use Oleg\OrderformBundle\Entity\Scan;
use Oleg\OrderformBundle\Entity\Stain;
//use Oleg\OrderformBundle\Entity\PartPaper;

use Oleg\OrderformBundle\Entity\Educational;
//use Oleg\OrderformBundle\Form\EducationalType;
use Oleg\OrderformBundle\Entity\Research;
//use Oleg\OrderformBundle\Form\ResearchType;

use Oleg\OrderformBundle\Form\SlideMultiType;

use Oleg\OrderformBundle\Helper\ErrorHelper;
use Oleg\OrderformBundle\Helper\FormHelper;
use Oleg\OrderformBundle\Helper\EmailUtil;
use Oleg\OrderformBundle\Helper\UserUtil;
use Oleg\OrderformBundle\Security\Util\SecurityUtil;


class TableController extends Controller {


    /**
     * @Route("/scan-order/multi-slide-table-view/new", name="table_create")
     * @Template("OlegOrderformBundle:MultyScanOrder:multitable.html.twig")
     */
    public function multiTableCreationAction()
    {

        $entity = new OrderInfo();
        $user = $this->get('security.context')->getToken()->getUser();

        $source = 'scanorder';

        $entity->setProvider($user);

        $patient = new Patient(true,'invalid',$user,$source);
        $entity->addPatient($patient);

        $edu = new Educational();
        $entity->setEducational($edu);

        $res = new Research();
        $entity->setResearch($res);

        $service = $user->getPathologyServices();

        $type = "Table-View Scan Order";

        $params = array('type'=>$type, 'cicle'=>'new', 'service'=>$service);
        $form   = $this->createForm( new OrderInfoType($params, $entity), $entity );

        return $this->render('OlegOrderformBundle:MultyScanOrder:newtable.html.twig', array(
            'form' => $form->createView(),
            'cycle' => 'new',
            'formtype' => $type
        ));
    }

    /**
     * Creates a new OrderInfo entity.
     *
     * @Route("/scan-order/multi-slide-table-view/new", name="singleorder_create")
     * @Method("POST")
     * @Template("OlegOrderformBundle:MultyScanOrder:multitable.html.twig")
     */
    public function multyCreateAction(Request $request)
    {

        //echo "multi new controller !!!! <br>";
        //exit();

        if( false === $this->get('security.context')->isGranted('ROLE_SUBMITTER') &&
            false === $this->get('security.context')->isGranted('ROLE_EXTERNAL_SUBMITTER')
        ) {
            return $this->redirect( $this->generateUrl('scan-order-home') );
        }

        $em = $this->getDoctrine()->getManager();
        $entity  = new OrderInfo();
        $user = $this->get('security.context')->getToken()->getUser();
        $conflicts = array();
        $cicle = 'new';
        $type = "Table-View Scan Order";

        $params = array('type'=>$type, 'cicle'=>$cicle, 'service'=>null);

        $form = $this->createForm(new OrderInfoType($params,$entity), $entity);

        //$form->bind($request);
        $form->handleRequest($request);

        $entity = $em->getRepository('OlegOrderformBundle:OrderInfo')->processOrderInfoEntity( $entity, $user, $type, $this->get('router') );



        return $this->render('OlegOrderformBundle:ScanOrder:thanks.html.twig', array(
            'oid' => $entity->getOid(),
            'conflicts' => $conflicts,
            'cicle' => $cicle
        ));

    }

}