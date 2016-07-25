<?php

namespace Oleg\CallLogBundle\Controller;

use Oleg\CallLogBundle\Form\PatientType;
use Oleg\OrderformBundle\Entity\Encounter;
use Oleg\OrderformBundle\Entity\EncounterPatfirstname;
use Oleg\OrderformBundle\Entity\EncounterPatlastname;
use Oleg\OrderformBundle\Entity\EncounterPatmiddlename;
use Oleg\OrderformBundle\Entity\EncounterPatsex;
use Oleg\OrderformBundle\Entity\EncounterPatsuffix;
use Oleg\OrderformBundle\Entity\Patient;
use Oleg\OrderformBundle\Entity\PatientDob;
use Oleg\OrderformBundle\Entity\PatientFirstName;
use Oleg\OrderformBundle\Entity\PatientLastName;
use Oleg\OrderformBundle\Entity\PatientMiddleName;
use Oleg\OrderformBundle\Entity\PatientMrn;
use Oleg\OrderformBundle\Entity\PatientSex;
use Oleg\OrderformBundle\Entity\PatientSuffix;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;


class DataQualityController extends CallEntryController
{


    /**
     * @Route("/merge-patient-records", name="calllog_merge_patient_records")
     * @Template("OlegCallLogBundle:DataQuality:merge-records.html.twig")
     */
    public function mergePatientAction(Request $request)
    {

        $user = $this->get('security.context')->getToken()->getUser();
        $securityUtil = $this->get('order_security_utility');
        $em = $this->getDoctrine()->getManager();

        $title = "Merge Patient Records";

        $system = $securityUtil->getDefaultSourceSystem(); //'scanorder';
        $status = 'valid';
        $cycle = 'new';


        $patient1 = new Patient(true,$status,$user,$system);
        $encounter1 = new Encounter(true,$status,$user,$system);
        $patient1->addEncounter($encounter1);
        $form1 = $this->createPatientForm($patient1);

        $patient2 = new Patient(true,$status,$user,$system);
        $encounter2 = new Encounter(true,$status,$user,$system);
        $patient2->addEncounter($encounter2);
        $form2 = $this->createPatientForm($patient2);


        return array(
            //'entity' => $entity,
            'form1' => $form1->createView(),
            'form2' => $form2->createView(),
            'cycle' => $cycle,
            'title' => $title,
        );
    }



    /**
     * @Route("/un-merge-patient-records", name="calllog_unmerge_patient_records")
     * @Template("OlegCallLogBundle:DataQuality:un-merge-records.html.twig")
     */
    public function unmergePatientAction(Request $request)
    {

        $user = $this->get('security.context')->getToken()->getUser();
        $securityUtil = $this->get('order_security_utility');
        $em = $this->getDoctrine()->getManager();

        $title = "Un-merge Patient Records";

        $system = $securityUtil->getDefaultSourceSystem(); //'scanorder';
        $status = 'valid';
        $cycle = 'new';

        $patient1 = new Patient(true,$status,$user,$system);
        $encounter1 = new Encounter(true,$status,$user,$system);
        $patient1->addEncounter($encounter1);
        $form1 = $this->createPatientForm($patient1);

        $patient2 = new Patient(true,$status,$user,$system);
        $encounter2 = new Encounter(true,$status,$user,$system);
        $patient2->addEncounter($encounter2);
        $form2 = $this->createPatientForm($patient2);

        return array(
            //'entity' => $entity,
            'form1' => $form1->createView(),
            'form2' => $form2->createView(),
            'cycle' => $cycle,
            'title' => $title,
        );
    }

}
