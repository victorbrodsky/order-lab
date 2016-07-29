<?php

namespace Oleg\CallLogBundle\Controller;

use Oleg\CallLogBundle\Form\PatientType;
use Oleg\OrderformBundle\Entity\Encounter;
use Oleg\OrderformBundle\Entity\EncounterPatfirstname;
use Oleg\OrderformBundle\Entity\EncounterPatlastname;
use Oleg\OrderformBundle\Entity\EncounterPatmiddlename;
use Oleg\OrderformBundle\Entity\EncounterPatsex;
use Oleg\OrderformBundle\Entity\EncounterPatsuffix;
use Oleg\OrderformBundle\Entity\MrnType;
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





    /**
     * @Route("/merge-patient-records-ajax", name="calllog_merge_patient_records_ajax", options={"expose"=true})
     */
    public function mergePatientAjaxAction(Request $request)
    {

        $user = $this->get('security.context')->getToken()->getUser();
        //$securityUtil = $this->get('order_security_utility');
        $calllogUtil = $this->get('calllog_util');
        $em = $this->getDoctrine()->getManager();

        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');

        $id1 = trim($request->get('id1'));
        $id2 = trim($request->get('id2'));
        $masterMergeRecordId = trim($request->get('masterMergeRecordId'));
        //echo "id1=$id1; id2=$id2 <br>";

        $msg = "";
        //$res = null;
        $merged = false;
        $error = false;
        $patient1 = null;
        $patient2 = null;
        $patientsArr = array();

        if( $id1 ) {
            $patient1 = $this->getDoctrine()->getRepository('OlegOrderformBundle:Patient')->find($id1);
            if( !$patient1 ) {
                $msg = "Patient 1 not found by id=".$id1;
                $error = true;
            }
            //$res = $patient1->getId();
            $patientsArr[] = $patient1;
        } else {
            $msg .= "Patient 1 id is invalid";
        }

        if( $id2 ) {
            $patient2 = $this->getDoctrine()->getRepository('OlegOrderformBundle:Patient')->find($id2);
            if( !$patient2 ) {
                $msg = "Patient 2 not found by id=".$id2;
                $error = true;
            }
            //$res = $patient2->getId();
            $patientsArr[] = $patient2;
        } else {
            $msg .= "Patient 2 id is invalid";
        }


        if( !$error && $patient1 && $patient2 ) {

            $mergedMrn1 = $patient1->obtainMergeMrn();
            $mergedMrn2 = $patient2->obtainMergeMrn();

            //a) If neither of the patients has an MRN of type="Merge ID"
            //Add the generated MRN to both patients with an MRN Type of "Merge ID"
            //MergeID: auto-generate unique, but prepend a prefix "MERGE" (ID MERGE123456)
            if( !$mergedMrn1 && !$mergedMrn2 ) {
                $merged = true;
                $msg .= 'Case (a): neither of the patients has an MRN of type="Merge ID"';
                $autoGeneratedMergeMrn = $calllogUtil->autoGenerateMergeMrn($patient1);

                $patRes = $calllogUtil->addGenerateMergeMrnToPatient($patient1,$autoGeneratedMergeMrn,$user);
                if( !($patRes instanceof Patient) ) {
                    $msg .= $patRes;
                    $error = true;
                }

                $patRes = $calllogUtil->addGenerateMergeMrnToPatient($patient2,$autoGeneratedMergeMrn,$user);
                if( !($patRes instanceof Patient) ) {
                    $msg .= $patRes;
                    $error = true;
                }
            }

            //b) If one of the patients has one MRN of type = "Merge ID",
            // copy that MRN with the type of "Merge ID" to the second patient as a new MRN.
            if( !$mergedMrn1 && $mergedMrn2 || $mergedMrn1 && !$mergedMrn2 ) {

                $msg .= 'Case (b): one of the patients has one MRN of type = "Merge ID".';

                if( $mergedMrn1 ) {
                    $msg .= " Patient with ID ".$id1." has Merged MRN. ";

                    $newMrn = $calllogUtil->createPatientMergeMrn($user);
                    if( $newMrn instanceof PatientMrn ) {
                        $merged = true;
                        $newMrn->setField($mergedMrn1->getField());
                        $patient2->addMrn($newMrn);
                    } else {
                        $msg .= $newMrn;
                    }
                }

                if( $mergedMrn2 ) {
                    $msg .= " Patient with ID ".$id2." has Merged MRN. ";

                    $newMrn = $calllogUtil->createPatientMergeMrn($user);
                    if( $newMrn instanceof PatientMrn ) {
                        $merged = true;
                        $newMrn->setField($mergedMrn2->getField());
                        $patient1->addMrn($newMrn);
                    } else {
                        $msg .= $newMrn;
                    }
                }

            }


            if( !$error && $merged ) {

                //set master patient
//                $msg .= "<br>";
//                foreach( $patientsArr as $patient ) {
//                    foreach( $patient->getMrn() as $mrn ) {
//                        $msg .= $patient->getId().": before mrn=".$mrn->obtainOptimalName()."<br>";
//                    }
//                }

                $ids = array();
                foreach( $patientsArr as $patient ) {
                    $ids[] = $patient->getId();
                    if( $masterMergeRecordId == $patient->getId() ) {
                        $patient->setMasterMergeRecord(true);
                    } else {
                        $patient->setMasterMergeRecord(false);
                    }

                    //$msg .= $patient->getId().": before patient mrn count=".count($patient->getMrn())."<br>";

                    //save patients to DB
                    $em->persist($patient);
                    $em->flush($patient);
                    //$msg .= $patient->getId().": after patient mrn count=".count($patient->getMrn())."<br>";
                }

//                foreach( $patientsArr as $patient ) {
//                    foreach( $patient->getMrn() as $mrn ) {
//                        $msg .= $patient->getId().": after mrn=".$mrn->obtainOptimalName()."<br>";
//                    }
//                }

                //"You have successfully merged patient records: Master Patient ID #."
                $msg .= "You have successfully merged patient records (IDs ".implode(", ",$ids).") with Master Patient ID # $masterMergeRecordId.";
            } else {
                $msg .= "No merged cases found.";
            }

            //$result['res'] = 'OK';
        }

        $result['error'] = $error;
        $result['msg'] = $msg;

        $response->setContent(json_encode($result));
        return $response;
    }





    /**
     * @Route("/unmerge-patient-records-ajax", name="calllog_unmerge_patient_records_ajax", options={"expose"=true})
     */
    public function unmergePatientAjaxAction(Request $request)
    {

        $user = $this->get('security.context')->getToken()->getUser();
        $securityUtil = $this->get('order_security_utility');
        $em = $this->getDoctrine()->getManager();

        $system = $securityUtil->getDefaultSourceSystem(); //'scanorder';
        $status = 'valid';
        $cycle = 'new';

        $result = 'OK';

        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');
        $response->setContent(json_encode($result));
        return $response;
    }

}
