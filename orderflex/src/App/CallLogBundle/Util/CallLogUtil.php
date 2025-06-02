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

namespace App\CallLogBundle\Util;



use App\OrderformBundle\Entity\AccessionType;
use App\UserdirectoryBundle\Entity\Logger; //process.py script: replaced namespace by ::class: added use line for classname=Logger


use App\UserdirectoryBundle\Entity\FormNode; //process.py script: replaced namespace by ::class: added use line for classname=FormNode


use App\OrderformBundle\Entity\PatientListHierarchyGroupType; //process.py script: replaced namespace by ::class: added use line for classname=PatientListHierarchyGroupType


use App\OrderformBundle\Entity\MessageCategory; //process.py script: replaced namespace by ::class: added use line for classname=MessageCategory


use App\OrderformBundle\Entity\MessageStatusList; //process.py script: replaced namespace by ::class: added use line for classname=MessageStatusList


use App\OrderformBundle\Entity\Message; //process.py script: replaced namespace by ::class: added use line for classname=Message


use App\UserdirectoryBundle\Entity\LocationTypeList; //process.py script: replaced namespace by ::class: added use line for classname=LocationTypeList


use App\UserdirectoryBundle\Entity\CityList; //process.py script: replaced namespace by ::class: added use line for classname=CityList


use App\UserdirectoryBundle\Entity\States; //process.py script: replaced namespace by ::class: added use line for classname=States


use App\UserdirectoryBundle\Entity\Countries; //process.py script: replaced namespace by ::class: added use line for classname=Countries


use App\OrderformBundle\Entity\AccessionListType; //process.py script: replaced namespace by ::class: added use line for classname=AccessionListType
use App\OrderformBundle\Entity\AccessionAccession;
use App\OrderformBundle\Entity\AccessionAccessionDate;
use App\OrderformBundle\Form\DataTransformer\AccessionTypeTransformer;
use App\UserdirectoryBundle\Entity\User;
use App\UserdirectoryBundle\Entity\UserWrapper;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\ResultSetMapping;
use App\CallLogBundle\Form\CalllogNavbarFilterType;
use App\OrderformBundle\Entity\Accession;
use App\OrderformBundle\Entity\Block;
use App\OrderformBundle\Entity\CalllogEntryMessage;
use App\OrderformBundle\Entity\Encounter;
use App\OrderformBundle\Entity\EncounterAttendingPhysician;
use App\OrderformBundle\Entity\EncounterDate;
use App\OrderformBundle\Entity\EncounterReferringProvider;
use App\OrderformBundle\Entity\FormVersion;
use App\OrderformBundle\Entity\MrnType;
use App\OrderformBundle\Entity\Part;
use App\OrderformBundle\Entity\Patient;
use App\OrderformBundle\Entity\PatientDob;
use App\OrderformBundle\Entity\PatientFirstName;
use App\OrderformBundle\Entity\PatientLastName;
use App\OrderformBundle\Entity\PatientListHierarchy;
use App\OrderformBundle\Entity\PatientMasterMergeRecord;
use App\OrderformBundle\Entity\PatientMiddleName;
use App\OrderformBundle\Entity\PatientMrn;
use App\OrderformBundle\Entity\PatientSex;
use App\OrderformBundle\Entity\PatientSuffix;
use App\OrderformBundle\Entity\Procedure;
use App\OrderformBundle\Form\DataTransformer\MrnTypeTransformer;
use App\UserdirectoryBundle\Entity\Document;
use App\UserdirectoryBundle\Entity\GeoLocation;
use App\UserdirectoryBundle\Entity\Location;
use App\UserdirectoryBundle\Entity\ObjectTypeText;
use App\UserdirectoryBundle\Entity\Spot;
use App\UserdirectoryBundle\Entity\Tracker;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Bundle\SecurityBundle\Security;

/**
 * Created by PhpStorm.
 * User: ch3
 * Date: 6/10/2016
 * Time: 3:04 PM
 */
class CallLogUtil
{

    protected $em;
    protected $container;
    protected $security;
    protected $formFactory;

    public function __construct( EntityManagerInterface $em, ContainerInterface $container, Security $security, FormFactoryInterface $formFactory ) {
        $this->em = $em;
        $this->container = $container;
        $this->security = $security;
        $this->formFactory = $formFactory;
    }


//    public function processMerge( $patientsArr ) {
//
//        foreach( $patientsArr as $patient ) {
//
//
//
//        }
//
//    }


    //auto-generating a unique MRN on Scan Order, but prepend a prefix "MERGE"
    public function autoGenerateMergeMrn( $patient ) {

        //process.py script: replaced namespace by ::class: ['AppOrderformBundle:MrnType'] by [MrnType::class]
        $keyTypeMergeID = $this->em->getRepository(MrnType::class)->findOneByName("Merge ID");
        if( !$keyTypeMergeID ) {
            $msg = 'MrnType not found by name Merge ID';
            throw new \Exception($msg);
            //return $msg;
        }
        $extra = array( "keytype" => $keyTypeMergeID->getId() );
        //$extra = null;

        //process.py script: replaced namespace by ::class: ['AppOrderformBundle:Patient'] by [Patient::class]
        $nextKey = $this->em->getRepository(Patient::class)->getNextNonProvided($patient,$extra,null,"MERGE-ID");

        //convert NOMRNPROVIDED-0000000002 to MERGE-ID-0000000002
        //$nextKey = str_replace("NOMRNPROVIDED","",$nextKey);
        //$nextKey = "MERGE-ID".$nextKey;
        //echo "nextKey=".$nextKey."<br>";
        //exit('1');

        return $nextKey;
    }

    public function addGenerateMergeMrnToPatient( $patient, $autoGeneratedMergeMrn, $provider ) {
        $newMrn = $this->createPatientMergeMrn($provider,$patient,$autoGeneratedMergeMrn);
        if( !($newMrn instanceof PatientMrn) ) {
            return $newMrn; //this is an error message
        }

        return $patient;
    }

    //Always create a new MRN
    public function createPatientMergeMrn( $provider, $patient, $mrnId ) {

        //Source System: ORDER Call Log Book
        $securityUtil = $this->container->get('user_security_utility');
        $sourcesystem = $securityUtil->getDefaultSourceSystem($this->container->getParameter('calllog.sitename'));
        if( !$sourcesystem ) {
            $msg = 'Source system not found by name ORDER Call Log Book';
            //throw new \Exception($msg);
            return $msg;
        }

        //process.py script: replaced namespace by ::class: ['AppOrderformBundle:MrnType'] by [MrnType::class]
        $keyTypeMergeID = $this->em->getRepository(MrnType::class)->findOneByName("Merge ID");
        if( !$keyTypeMergeID ) {
            $msg = 'MrnType not found by name Merge ID';
            //throw new \Exception($msg);
            return $msg;
        }

        $newMrn = null;
        $status = 'valid';

        //Create a new MRN
        $newMrn = new PatientMrn($status,$provider,$sourcesystem);
        $newMrn->setKeytype($keyTypeMergeID);
        $newMrn->setField($mrnId);
        $patient->addMRn($newMrn);

        //exit('create Patient Merge Mrn exit; mrnId='.$mrnId);
        return $newMrn;
    }
    //NOT USED: check if invalid MRN already exists or create a new one
    public function createWithCheckPatientMergeMrn( $provider, $patient, $mrnId ) {

        //Source System: ORDER Call Log Book
        $securityUtil = $this->container->get('user_security_utility');
        $sourcesystem = $securityUtil->getDefaultSourceSystem($this->container->getParameter('calllog.sitename'));
        if( !$sourcesystem ) {
            $msg = 'Source system not found by name ORDER Call Log Book';
            //throw new \Exception($msg);
            return $msg;
        }

        //process.py script: replaced namespace by ::class: ['AppOrderformBundle:MrnType'] by [MrnType::class]
        $keyTypeMergeID = $this->em->getRepository(MrnType::class)->findOneByName("Merge ID");
        if( !$keyTypeMergeID ) {
            $msg = 'MrnType not found by name Merge ID';
            //throw new \Exception($msg);
            return $msg;
        }

        //check if invalid PatientMrn already exists by field and keytype
//        $patientMrns = $this->em->getRepository('AppOrderformBundle:PatientMrn')->findBy(
//            array(
//                'keytype' => $keyTypeMergeID->getId(),
//                'field' => $mrnId,
//                'patient' => $patient->getId(),
//            )
//        );
        $patientMrns = $patient->obtainMergeMrnArr();

        $newMrn = null;
        $status = 'valid';

        if( count($patientMrns) == 0 ) {
            //OK: create a new MRN
            $newMrn = new PatientMrn($status,$provider,$sourcesystem);
            $newMrn->setKeytype($keyTypeMergeID);
            $patient->addMRn($newMrn);
        }

        if( count($patientMrns) > 1 ) {
            foreach( $patientMrns as $patientMrn ) {
                if( $patientMrn->getField() == $mrnId && $patientMrn->getStatus() == 'invalid' ) {
                    $newMrn = $patientMrn;
                    $newMrn->setStatus($status);
                    $newMrn->setProvider($provider);
                    $newMrn->setCreationdate();
                    break;
                }
            }
        }

        if( count($patientMrns) == 1 ) {
            if( $patientMrns[0]->getField() == $mrnId && $patientMrns[0]->getStatus() == 'invalid' ) {
                $newMrn = $patientMrns[0];
                $newMrn->setStatus($status);
                $newMrn->setProvider($provider);
                $newMrn->setCreationdate();
                //return "Found 1 invalid Merged MRN ID=".$patientMrns[0]->getField()."; status=".$patientMrns[0]->getStatus().".<br>";
            }
        }

        if( !$newMrn ) {
            $msg = 'PatientMrn has not been created. Found patientMrns count='.count($patientMrns);
            return $msg;
        }

        return $newMrn;
    }

    public function getMergedPatients( $mergeId, $mergedPatients=null, $existingPatientIds=null ) {

        //process.py script: replaced namespace by ::class: ['AppOrderformBundle:MrnType'] by [MrnType::class]
        $keyTypeMergeID = $this->em->getRepository(MrnType::class)->findOneByName("Merge ID");
        if( !$keyTypeMergeID ) {
            $msg = 'MrnType not found by name Merge ID';
            throw new \Exception($msg);
            //return $msg;
        }

        $parameters = array();

        //process.py script: replaced namespace by ::class: ['AppOrderformBundle:Patient'] by [Patient::class]
        $repository = $this->em->getRepository(Patient::class);
        $dql = $repository->createQueryBuilder("patient");
        $dql->leftJoin("patient.mrn", "mrn");

        $dql->andWhere("mrn.status = 'valid'");

        $dql->andWhere("mrn.keytype = :keytype AND mrn.field = :mrn");
        $parameters['keytype'] = $keyTypeMergeID->getId();
        $parameters['mrn'] = $mergeId;

        if( $existingPatientIds ) {
            $dql->andWhere("patient.id NOT IN (" . implode(",", $existingPatientIds) . ")");
            //$dql->andWhere("patient.id NOT IN (:existingPatientIds)");
            //$parameters['existingPatientIds'] = $existingPatientIds;
        }

        $dql->orderBy("patient.id","ASC"); //show latest first

        $query = $dql->getQuery();
        $query->setParameters($parameters);
        //echo $mergeId.":sql=".$query->getSql()."<br>";
        $patients = $query->getResult();
        //echo "merged patients = ".count($patients)."<br>";

        if( $mergedPatients == null ) {
            $mergedPatients = new ArrayCollection();
        }

        //make unique array of the merged patients
        foreach( $patients as $patient ) {
            //echo "tryingAddPatient=".$patient->getId()."<br>";
            //$mergedPatients[] = $patient;
            if( !$mergedPatients->contains($patient) ) {
                //echo "addedPatient=".$patient->getId()."<br>";
                $mergedPatients->add($patient);
            }
        }

        return $mergedPatients;
    }

    //FirstNameOfAuthorOfMRN LastNameofAuthorOfMRN on DateOfMergeIDAdditionToPatientOne /
    // DateOfMergeIDAdditionToPatientTwo via Merge ID [MergeID-MRN]
    public function obtainSameMergeMrnInfoStr( $mrn1, $mrn2 ) {
        if( $mrn1->getField() != $mrn2->getField() ) {
            return null;
        }
        //1) get earliest author and creationdate
        if( $mrn1->getCreationdate() > $mrn2->getCreationdate() ) {
            //$mrn2 is the earliest one
            $author = $mrn2->getProvider();
            $creationDate = $mrn2->getCreationdate();
            $creationDateTwo = $mrn1->getCreationdate();
        } else {
            //$mrn1 is the earliest one
            $author = $mrn1->getProvider();
            $creationDate = $mrn1->getCreationdate();
            $creationDateTwo = $mrn2->getCreationdate();
        }
        $resStr = $author." on ".$creationDate->format("m/d/Y");

        //DateOfMergeIDAdditionToPatientTwo
        $resStr .= " / ".$creationDateTwo->format("m/d/Y");

        //via Merge ID [MergeID-MRN]
        $resStr .= " via Merge ID ".$mrn1->getField();

        return $resStr;
    }

    public function hasSameID( $patient1, $patient2 ) {
        $status = 'valid';
        $mergedMrn1Arr = $patient1->obtainMergeMrnArr($status);
        $mergedMrn2Arr = $patient2->obtainMergeMrnArr($status);

        foreach( $mergedMrn1Arr as $mergedMrn1 ) {
            foreach( $mergedMrn2Arr as $mergedMrn2 ) {
                if( $mergedMrn1->getField() == $mergedMrn2->getField() ) {
                    return true;
                }
            }
        }

        return false;
    }

    //If not equal, copy the MRN with the oldest timestamp of the ones available from
    // one patient with the type of "Merge ID" to the second patient as a new MRN
    public function copyOldestMrnToSecondPatient( $user, $patient1, $mergedMrn1, $patient2, $mergedMrn2 ) {
        if( $mergedMrn1->getCreationdate() > $mergedMrn2->getCreationdate() ) {
            //$mergedMrn2 is the oldest (earliest) one
            $oldestMrnId = $mergedMrn2->getField();
            $secondPatient = $patient1;
        } else {
            //$mergedMrn1 is the oldest (earliest) one
            $oldestMrnId = $mergedMrn1->getField();
            $secondPatient = $patient2;
        }

        $newMrn = $this->createPatientMergeMrn($user,$secondPatient,$oldestMrnId);

        //if( $newMrn instanceof PatientMrn ) {
            //$newMrn->setField($oldestMrnId);
            //$secondPatient->addMrn($newMrn);
        //}

        return $newMrn;
    }

    public function getJsonEncodedPatient( $patient ) {

        $status = 'valid';
        $fieldnameArr = array('patlastname','patfirstname','patmiddlename','patsuffix','patsex');

        //to get a single field only use obtainStatusField
        $mrnRes = $patient->obtainStatusField('mrn', $status);
        $dobRes = $patient->obtainStatusField('dob', $status);
        //echo "dob=".$dobRes."<br>";
        //echo "mrntype=".$mrnRes->getKeytype()->getId()."<br>";
        //exit("1");

        //values: patient vs encounters
        //Show the "Valid" values for First Name, Last Name, etc from the encounter (not from patient object).
        // If there are multiple "Valid" values, show the ones with the most recent time stamp.

        $fieldnameResArr = $patient->obtainSingleEncounterValues($fieldnameArr,$status);

        $lastNameRes = $fieldnameResArr['patlastname']; //$patient->obtainStatusField('lastname', $status);
        $firstNameRes = $fieldnameResArr['patfirstname']; //$patient->obtainStatusField('firstname', $status);
        $middleNameRes = $fieldnameResArr['patmiddlename'];  //$patient->obtainStatusField('middlename', $status);
        $suffixRes = $fieldnameResArr['patsuffix'];   //$patient->obtainStatusField('suffix', $status);
        $sexRes = $fieldnameResArr['patsex'];    //$patient->obtainStatusField('sex', $status);

        $contactinfo = $patient->obtainPatientContactinfo("Patient's Primary Contact Information");

//        if( $patient->isMasterMergeRecord() ) {
//            $masterStr = "+";
//        } else {
//            $masterStr = "";
//        }

        //if patient does not have encounter => use patient's values
        if( !$lastNameRes ) {
            $lastNameRes = $patient->obtainStatusField('lastname', $status);
        }
        if( !$firstNameRes ) {
            $firstNameRes = $patient->obtainStatusField('firstname', $status);
        }
        if( !$middleNameRes ) {
            $middleNameRes = $patient->obtainStatusField('middlename', $status);
        }
        if( !$suffixRes ) {
            $suffixRes = $patient->obtainStatusField('suffix', $status);
        }
        if( !$sexRes ) {
            $sexRes = $patient->obtainStatusField('sex', $status);
        }

        $mrntypeId = null;
        $mrntypeStr = null;
        if( $mrnRes->getKeytype() ) {
            $mrntypeObject = $this->convertAutoGeneratedMrntype($mrnRes->getKeytype()->getId(), true);
            if( $mrntypeObject ) {
                $mrntypeId = $mrntypeObject->getId();
                $mrntypeStr = $mrntypeObject->getOptimalName();
            }
        }

        //get most recent location's institution
        //$locationInstitution = NULL;

        $accessions = $this->getAccessionsByPatient($patient,true);
        if( !$accessions ) {
            $accessions = '';
        }

        //$emptyValue = NULL;
        $emptyValue = "";

        $patientInfo = array(
            'id' => $patient->getId(),
            'mrntype' => $mrntypeId,        //$mrnRes->getKeytype()->getId(),
            'mrntypestr' => $mrntypeStr,    //$mrnRes->getKeytype()->getName(),
            'mrn' => $mrnRes->getField(),
            'dob' => $dobRes."",
            'age' => $patient->calculateAge()."",

            'lastname' => (($lastNameRes) ? $lastNameRes->getField() : $emptyValue),  //$lastNameRes->getField(),
            'lastnameStatus' => (($lastNameRes) ? $lastNameRes->getStatus() : $emptyValue),
            //'lastnameStatus' => 'alias',

            'firstname' => (($firstNameRes) ? $firstNameRes->getField() : $emptyValue),  //$firstNameStr,
            'firstnameStatus' => (($firstNameRes) ? $firstNameRes->getStatus() : $emptyValue),

            'middlename' => (($middleNameRes) ? $middleNameRes->getField() : $emptyValue), //$middleNameRes->getField(),
            'middlenameStatus' => (($middleNameRes) ? $middleNameRes->getStatus() : $emptyValue),

            'suffix' => (($suffixRes) ? $suffixRes->getField() : $emptyValue),   //$suffixRes->getField(),
            'suffixStatus' => (($suffixRes) ? $suffixRes->getStatus() : $emptyValue),

            'sex' => (($sexRes && $sexRes->getField()) ? $sexRes->getField()->getId() : $emptyValue),    //$sexRes->getId(),
            'sexstr' => $sexRes."",

            'email' => $patient->getEmail(),
            'phone' => $patient->getPhone(),

            'contactinfo' => $contactinfo,

            'fullName' => $patient->getFullPatientName(),

            'mergeInfo' => $patient->obtainMergeInfo("<br>"),

            'mergedPatientsInfo' => NULL,

            'masterPatientId' => NULL,

            //'patientInfoStr' => "Patient ID# ".$patient->getId().": ",    //.$masterStr.": "//testing
            'patientInfoStr' => $patient->getId(),
            
            'accessions' => $accessions

        );

        return $patientInfo;
    }


    //set master patient: create a new, valid masterMergeRecord and set all others to invalid
    public function setMasterPatientRecord( $patients, $masterMergeRecordId, $provider ) {

        $securityUtil = $this->container->get('user_security_utility');
        $sourcesystem = $securityUtil->getDefaultSourceSystem($this->container->getParameter('calllog.sitename'));
        if( !$sourcesystem ) {
            $msg = 'Source system not found by name ORDER Call Log Book';
            throw new \Exception($msg);
            //return $msg;
        }

        //add all merged patients
        $patients = $this->getAllMergedPatients($patients);

        $ids = array();

        foreach( $patients as $patient ) {

            $ids[] = $patient->getId();

            //invalidate all merge master records objects
            $patient->invalidateMasterMergeRecord('invalid');

            //create a new merge record object with new timestamp and creator
            if( $masterMergeRecordId == $patient->getId() ) {
                //$status = 'valid', $provider = null, $source = null
                $masterMergeRecord = new PatientMasterMergeRecord('valid',$provider,$sourcesystem);
                $masterMergeRecord->setField(true);
                $patient->addMasterMergeRecord($masterMergeRecord);
            }

            //$msg .= $patient->getId().": before patient mrn count=".count($patient->getMrn())."<br>";
            //testing
//                    foreach( $patient->getMrn() as $mrn ) {
//                        $msg .= $patient->getId().": before MRNID=".$mrn->getID()." mrn=".$mrn->obtainOptimalName()."; status=".$mrn->getStatus()."<br>";
//                    }

            //save patients to DB
            $this->em->persist($patient);
            //$msg .= $patient->getId().": after patient mrn count=".count($patient->getMrn())."<br>";
        }

        return $ids;
    }

    public function getAllMergedPatients( $patients, $mergeMrnsArr=array(), $masterFirst=true ) {

        $existingPatientIds = array();
        foreach( $patients as $patient ) {
            $existingPatientIds[] = $patient->getId();
        }
        //$existingPatientIds = null;

        $resPatients = new ArrayCollection();

        foreach( $patients as $patient ) {

            //echo "!!!checkPatient=".$patient->getId()."<br>";
            //continue;

            if( !$resPatients->contains($patient) ) {

                $resPatients->add($patient);

                //set master patient as the first record
                if( $masterFirst ) {
                    if( $patient->isMasterMergeRecord() ) {

                        //get current first element
                        $firstPatient = $resPatients->get(0);

                        //set master patient as the first element
                        $resPatients->set(0,$patient);

                        //add the original first element to the end
                        if( $firstPatient ) {
                            if( !$resPatients->contains($firstPatient) ) {
                                $resPatients->add($firstPatient);
                            }
                        }
                    }
                }

            }

            //get valid mrns
            $mergeMrns = $patient->obtainMergeMrnArr('valid');

            foreach( $mergeMrns as $mergeMrn ) {

                $mid = $mergeMrn->getField();

                if( in_array($mid,$mergeMrnsArr) ) {
                    //this MID has already processed => skip it
                } else {
                    //echo "process MID=".$mid."<br>";
                    $mergeMrnsArr[] = $mid;
                    $resPatients = $this->getMergedPatients($mid, $resPatients, $existingPatientIds);

                    //recursive call
                    $resPatients = $this->getAllMergedPatients($resPatients,$mergeMrnsArr);
                }

            }

        }//foreach

        //foreach( $resPatients as $resPatient ) {
        //    echo "###resPatient=".$resPatient->getId()."<br>";
        //}
        //echo "<br><br>";

        return $resPatients;
    }

    public function getMasterRecordPatients( $patients ) {
        foreach( $patients as $patient ) {
            if( $patient->isMasterMergeRecord() ) {
                return $patient;
            }
        }
        return null;
    }

    public function getMergeInfo( $patient ) {
        $mergedMrnArr = $patient->obtainMergeMrnArr("valid");
        $str = "";
        foreach( $mergedMrnArr as $mergedMrn ) {
            $str .= "Merge ID ".$mergedMrn->getField().", merged by " . $mergedMrn->getProvider() . " on " . $mergedMrn->getCreationdate()->format('m/d/Y');
        }
        return $str;
    }


    public function processMasterRecordPatients( $unmergedPatients, $masterId, $user ) {
        $res = array();
        $res['error'] = false;
        $res['msg'] = "";

        $unmergedMasterPatients = array();
        foreach( $unmergedPatients as $unmergedPatient ) {
            //echo "patientID=".$unmergedPatient->getId()."<br>";
            if( $unmergedPatient->isMasterMergeRecord() ) {
                $unmergedMasterPatients[] = $unmergedPatient;
            }
        }

        if( count($unmergedMasterPatients) == 1 ) {
            if( $unmergedMasterPatients[0]->getId() != $masterId ) {
                //re-set master record
                $ids = $this->setMasterPatientRecord($unmergedPatients,$masterId,$user);
                $res['msg'] = " Patient with ID $masterId has been set as a primary patient record; Patients affected ids=".implode(", ",$ids);
            } else {
                //un-merged master record is the same: error
                $res['error'] = true;
                $res['msg'] = " You are trying to un-merge master patient ID #$masterId without providing a new master patient record.";
            }
        }

        if( count($unmergedMasterPatients) > 1 ) {
            //logical error
            $res['error'] = true;
            $res['msg'] = " Found multiple number of primary patient records: ".count($unmergedMasterPatients)." primary patient records found.";
        }

        return $res;
    }

//    // 1) A*-B(MID1) C*-D(MID2)
//    // 2) Merge B and C (B - master record)
//    // 3) Result: A-B*-C(MID1) and C-D(MID2); linked node - C(MID1,MID2)
//    // 4) E*-F(MID3)
//    // 5) Merge C and E (B - master record; find a way to display to choose master record)
//    // 6) Result: A-B*-C-E(MID1) C-D(MID2) E-F(MID3); linked node - C(MID1,MID2), E(MID2,MID3)
//    // 7) Un-merge C: Un-merging C in this case should merge A, B, D, E, F by copying the oldest time-stamped Merge ID to E and ...
//    // 8) a) Find all patients with MID1,2
//    // 8) b) Add not existing MID2 to the first node (D)

    //check for orphan for the same MRN ID for each valid MRN ID for this patient.
    //If there is only one sibling with the same valid MRN ID, then this sibling is orphan.
    //un-merge this orphan patient:
    // 1) invalidate Merge MRN object
    // 2) invalidate all merge master records for this orphan patient
    // 3) if removed patient is the linked node (holding MID for different chains, i.e. 1,2,3)
//    public function processOrphan( $patient, $mergeId ) {
//
//        //get valid mrns
//        $mergeMrns = $patient->obtainMergeMrnArr('valid');
//
//        foreach( $mergeMrns as $mergeMrn ) {
//            echo "<br><br>Merge ID ".$mergeMrn->getField().":<br>";
//
//            if( !$mergeMrn->getField() ) {
//                //ignore mergeId is null (can not happen in the normal conditions)
//                continue;
//            }
//
//            if( $mergeMrn->getField() == $mergeId ) {
//                //ok: go ahead and process this mergeId
//            } else {
//                //ignore not this mergeId
//                continue;
//            }
//
//            $patients = $this->getMergedPatients($mergeMrn->getField(), null, array($patient->getId()));
//
//            if( count($patients) == 1 ) {
//                //there is only one patient with this mergeId => orphan
//
//                // 1) invalidate Merge MRN object
//                $mergeMrn->setStatus('invalid');
//
//                // 2) invalidate all merge master records for this orphan patient
//
//
//                //
//
//            }
////            else {
////                foreach( $patients as $thisPatient ) {
////                    echo "Patient ID ".$thisPatient->getId().":<br>";
////                }
////            }
//
//        }
//
//    }

    // A) if only one merged patient exists with this mergeId (except this patient) => orphan
    // un-merge this orphan patient:
    // 1) invalidate Merge MRN object
    // 2) invalidate all merge master records for this orphan patient
    // B) if multiple patients found (except this patient) => copy all merged IDs to the first patient in the chain
//    public function processUnmergedPatient( $patient, $mergeId, $user ) {
//
//        $res = array();
//        $res['error'] = false;
//        $res['msg'] = "";
//
//        //get valid mrns
//        $mergeMrns = $patient->obtainMergeMrnArr('valid');
//
//        //get other merged patients in the same group
//        $patients = $this->getMergedPatients($mergeId, null, array($patient->getId()));
//
//        if( count($patients) == 0 ) {
//            //do nothing
//        }
//
//        // A) if only one merged patient exists with this mergeId (except this patient) => orphan
//        if( count($patients) == 1 ) {
//            // un-merge this orphan patient
//            $mergedPatient = $patients[0];
//
//            //get valid Merge MRN object
//            $mergeMrn = $mergedPatient->obtainMergeMrnById($mergeId,'valid');
//
//            //1) un-merge: set valid status Merge MRN object to invalid
//            $mergeMrn->setStatus('invalid');
//
//            //2) un-merge: invalidate all merge master records objects
//            $mergedPatient->invalidateMasterMergeRecord('invalid');
//        }
//
//        // B) if multiple patients found (except this patient) in the same group specified by the merge ID =>
//        // copy all merged IDs to the first patient in the chain (group)
//        // By diagram:
//        // un-merge C3: 2 other merged patients in this group (E,F) => two independent chains: A-B-C-D and E-F
//        // ////////
//        // This method: copy all other merged IDs to the first patient in the group:
//        // copy merge IDs 1 and 2 to the first patient E => now E has merge IDs 1,2,3
//        // Result: A(1)-B(1)-C(1,2), C(2,1)-D(2), E(3,1,2)-F(3) => we have the same chain A-B-C-D-E-F wrong!
//        if(0) { //disable because it is wrong!
//            if (count($patients) > 1) {
//                //copy all valid merged IDs to the first patient in the same group
//                $mergedPatient = $patients[0];
//
//                //get valid mrns
//                $mergeMrns = $patient->obtainMergeMrnArr('valid');
//
//                foreach ($mergeMrns as $mergeMrn) {
//                    //create new merge mrn and add it to the $mergedPatient
//                    $newMrn = $this->createPatientMergeMrn($user, $mergedPatient, $mergeMrn->getField());
//                    if (!($newMrn instanceof PatientMrn)) {
//                        $res['error'] = true;
//                        $res['msg'] .= $newMrn . ". "; //this is an error message
//                    }
//                }
//
//            }
//        }
//
//
//        return $res;
//    }

    //1) get number of valid merge IDs in this patient
    //2) invalidate each mergeID in this patient (if there is only one merge ID => just invalidate this merge ID)
    //3) if more than one merge IDs (linked node) => add these (that does not exist yet) to the master Patient (making this patient as a linked node)
    //4) check for the orphans: for each mergeID if only one merged patient exists with this mergeId (except this patient) => orphan
    //5) invalidate all merge master records objects for this patient
    //6) record all steps to the event log
    public function processUnmergedPatient( $patient, $masterId, $user ) {

        $res = array();
        $res['error'] = false;
        $res['msg'] = "";

        //get valid mrns
        $mergeMrns = $patient->obtainMergeMrnArr('valid');

        //2) if there is only one merge ID => just invalidate this merge ID (linked node)
        //if( count($mergeMrns) == 1 ) {
        //}

        //3) if more than one merge IDs => add these (that does not exist yet) to the master Patient (making this patient as a linked node)
        $copyToMaster = false;
        if( count($mergeMrns) > 1 ) {
            $copyToMaster = true;
            //get all merged patients
            $mergedPatients = $this->getAllMergedPatients( array($patient) );
            $masterPatient = $this->getMasterRecordPatients($mergedPatients);
        }

        $orphansArr = array();

        //invalidate each merge ID check for the orphans for each mergeIDs
        foreach( $mergeMrns as $mergeMrn ) {

            //get other merged patients in the same group
            $orphansArr[$mergeMrn->getField()] = $this->getMergedPatients($mergeMrn->getField(), null, array($patient->getId()));
            //$res .= "Orphan check: other patients found = ".count($patients)."; ";

            //2) invalidate this merge ID
            $mergeMrn->setStatus('invalid');
            $res['msg'] .= "Invalidate merge MRN ".$mergeMrn->getField()." for patient ID# ".$patient->getId()."<br>";

            //3) if more than one merge IDs (linked node) =>
            // add these (that does not exist yet) to the master Patient (making this patient as a linked node)
            if( $copyToMaster ) {
                //get valid Merge MRN object
                $masterPatientMergeMrn = $masterPatient->obtainMergeMrnById($mergeMrn->getField(),'valid');

                //add if not exists yet
                if( !$masterPatientMergeMrn ) {
                    //create new merge mrn and add it to the $masterPatient
                    $newMrn = $this->createPatientMergeMrn($user, $masterPatient, $mergeMrn->getField());
                    if (!($newMrn instanceof PatientMrn)) {
                        //error
                        $res['error'] = true;
                        $res['msg'] .= $newMrn . ". "; //this is an error message
                    } else {
                        //ok
                        $res['msg'] .= "Copy merge MRN " . $newMrn->getField() . " to master patient ID# " . $masterPatient->getId() . "<br>";
                        $this->em->persist($newMrn);
                        $this->em->persist($masterPatient);
                    }
                }
            }

            //4) check for the orphans for each mergeIDs
            $resOrphanMsg = $this->processOrphansArr($patient,$orphansArr,$masterId);  //$patient,$mergeMrn);
            $res['msg'] .= $resOrphanMsg."<br>";

        }

        //2) invalidate all merge master records objects for this patient
        $patient->invalidateMasterMergeRecord('invalid');

        //exit('exit processUnmergedPatient. res: <br>'.$res['msg']);

        return $res;
    }

//    //check for orphan for the same MRN ID for each valid MRN ID for this patient.
//    //If there is only one sibling with the same valid MRN ID, then this sibling is orphan.
//    //un-merge this orphan patient:
//    // 1) invalidate Merge MRN object
//    // 2) invalidate all merge master records for this orphan patient
//    // 3) if removed patient is the linked node (holding MID for different chains, i.e. 1,2,3)
//    public function processOrphan( $patient, $mergeId ) {
//
//        $res = "";
//
//        //get other merged patients in the same group
//        $patients = $this->getMergedPatients($mergeId, null, array($patient->getId()));
//        $res .= "Orphan check: other patients found = ".count($patients)."; ";
//
//        if( count($patients) == 0 ) {
//            //do nothing
//        }
//
//        // A) if only one merged patient exists with this mergeId (except this patient) => orphan
//        if( count($patients) == 1 ) {
//            // un-merge this orphan patient
//            $orphanMergedPatient = $patients[0];
//
//            //get valid Merge MRN object
//            $mergeMrn = $orphanMergedPatient->obtainMergeMrnById($mergeId,'valid');
//
//            //1) un-merge: set valid status Merge MRN object to invalid
//            $mergeMrn->setStatus('invalid');
//
//            //2) un-merge: invalidate all merge master records objects
//            $orphanMergedPatient->invalidateMasterMergeRecord('invalid');
//
//            $this->em->persist($orphanMergedPatient);
//            //$this->em->persist($mergeMrn);
//
//            $res .= "Invalidate merge MRN ".$mergeId." for the orphan patient ID# ".$orphanMergedPatient->getId();
//        }
//
//        return $res;
//    }

    //check for orphan for the same MRN ID for each valid MRN ID for this patient.
    //If there is only one sibling with the same valid MRN ID, then this sibling is orphan.
    //un-merge this orphan patient:
    // 1) invalidate Merge MRN object
    // 2) invalidate all merge master records for this orphan patient
    // 3) if removed patient is the linked node (holding MID for different chains, i.e. 1,2,3)
    public function processOrphansArr( $patient, $orphansArr, $masterId ) {

        $res = "";

        foreach( $orphansArr as $mergeId => $patients ) {

            //echo "orphans count=".count($patients)."<br>";

            if( count($patients) == 1 ) {
                // un-merge this orphan patient
                $orphanMergedPatient = $patients[0];

                //get valid Merge MRN object
                $mergeMrn = $orphanMergedPatient->obtainMergeMrnById($mergeId,'valid');

                //1) un-merge: set valid status Merge MRN object to invalid
                $mergeMrn->setStatus('invalid');

                //2) un-merge: invalidate all merge master records objects
                if( $masterId != $orphanMergedPatient->getId() ) {
                    $orphanMergedPatient->invalidateMasterMergeRecord('invalid');
                }

                $this->em->persist($orphanMergedPatient);
                //$this->em->persist($mergeMrn);

                $res .= "Invalidate merge MRN ".$mergeId." for the orphan patient ID# ".$orphanMergedPatient->getId()."<br>";
            }

        }

        return $res;
    }

    //DO NOT USE "Existing Auto-generated MRN", use only "Auto-generated MRN"
    public function convertAutoGeneratedMrntype( $mrntypeId, $asObject=false ) {

        if( strval($mrntypeId) != strval(intval($mrntypeId)) ) {
            //exit("not integer");

            $mrntypeTransformer = new MrnTypeTransformer($this->em,$this->security->getUser());
            $mrntypeNew = $mrntypeTransformer->reverseTransform($mrntypeId,false);

            return $mrntypeNew;
        }
        //echo "mrntypeId=".$mrntypeId."<br>";

        //process.py script: replaced namespace by ::class: ['AppOrderformBundle:MrnType'] by [MrnType::class]
        $mrntype = $this->em->getRepository(MrnType::class)->find($mrntypeId);
        if( !$mrntype ) {
            //exit("MrnType not found");
            //$msg = 'MrnType not found by ID ' . $mrntypeId;
            //throw new \Exception($msg);
            //return $msg;
            return null;
        }

        //DO NOT convert use the original mrn type (select box in form shows only "Auto-generated MRN", so it supposed to be "Auto-generated MRN")
        return $mrntype;

//        if( $mrntype->getName() == "Auto-generated MRN" ) {
//            $convertedMrntype = $this->em->getRepository('AppOrderformBundle:MrnType')->findOneByName("Existing Auto-generated MRN");
//
//            if( $asObject ) {
//                return $convertedMrntype;
//            } else {
//                return $convertedMrntype->getId();
//            }
//        }
//
//        if( $mrntype->getName() == "Existing Auto-generated MRN" ) {
//            $convertedMrntype = $this->em->getRepository('AppOrderformBundle:MrnType')->findOneByName("Auto-generated MRN");
//
//            if( $asObject ) {
//                return $convertedMrntype;
//            } else {
//                return $convertedMrntype->getId();
//            }
//        }
//
//        if( $asObject ) {
//            return $mrntype;
//        } else {
//            return $mrntypeId;
//        }
    }

    public function convertAutoGeneratedAccessiontype( $accessiontypeId, $asObject=false ) {

        if( !is_object($accessiontypeId) && strval($accessiontypeId) != strval(intval($accessiontypeId)) ) {
            //exit("not integer");

            $accessiontypeTransformer = new AccessionTypeTransformer($this->em,$this->security->getUser());
            $accessiontypeNew = $accessiontypeTransformer->reverseTransform($accessiontypeId,false);

            return $accessiontypeNew;
        }
//        echo "accessiontypeId=".$accessiontypeId."<br>";

        //process.py script: replaced namespace by ::class: ['AppOrderformBundle:AccessionType'] by [AccessionType::class]
        $accessiontype = $this->em->getRepository(AccessionType::class)->find($accessiontypeId);
        if( !$accessiontype ) {
            //exit("accessiontype not found");
            //$msg = 'accessiontype not found by ID ' . $accessiontypeId;
            //throw new \Exception($msg);
            //return $msg;
            return null;
        }

        //DO NOT convert use the original mrn type (select box in form shows only "Auto-generated MRN", so it supposed to be "Auto-generated MRN")
        return $accessiontype;
    }

    public function getNextEncounterGeneratedId($user=null) {
        $userSecUtil = $this->container->get('user_security_utility');
        $institution = $userSecUtil->getCurrentUserInstitution($user);
        $encounter = new Encounter();
        $encounter->setInstitution($institution);
        //process.py script: replaced namespace by ::class: ['AppOrderformBundle:Encounter'] by [Encounter::class]
        $nextKey = $this->em->getRepository(Encounter::class)->getNextNonProvided($encounter);
        return $nextKey;
    }

//    public function checkNextEncounterGeneratedId($user=null) {
//        $userSecUtil = $this->container->get('user_security_utility');
//        $institution = $userSecUtil->getCurrentUserInstitution($user);
//
//        //patient
//        $patient = new Patient();
//        $patient->setInstitution($institution);
//        $nextKey = $this->em->getRepository('AppOrderformBundle:Patient')->getNextNonProvided($patient);
//        echo "next key=".$nextKey."<br>";
//
//        //encounter
//        $encounter = new Encounter();
//        $encounter->setInstitution($institution);
//        $patient->addEncounter($encounter);
//        $nextKey = $this->em->getRepository('AppOrderformBundle:Encounter')->getNextNonProvided($encounter);
//        echo "next key=".$nextKey."<br>";
//
//        //procedure
//        $procedure = new Procedure();
//        $procedure->setInstitution($institution);
//        $encounter->addProcedure($procedure);
//        $nextKey = $this->em->getRepository('AppOrderformBundle:Procedure')->getNextNonProvided($procedure);
//        echo "next key=".$nextKey."<br>";
//
//        //accession
//        $accession = new Accession(true,'valid');
//        $accession->setInstitution($institution);
//        $procedure->addAccession($accession);
//        $accessionKey = $this->em->getRepository('AppOrderformBundle:AccessionType')->findOneByName('Auto-generated Accession Number');
//        $accession->getAccession()->first()->setKeytype($accessionKey);
//        $nextKey = $this->em->getRepository('AppOrderformBundle:Accession')->getNextNonProvided($accession);
//        echo "next key=".$nextKey."<br>";
//
//        //part
//        $part = new Part(true,'valid');
//        $part->setInstitution($institution);
//        $accession->addPart($part);
//        $nextKey = $this->em->getRepository('AppOrderformBundle:Part')->getNextNonProvided($part);
//        echo "next key=".$nextKey."<br>";
//
//        //block
//        $block = new Block(true,'valid');
//        $block->setInstitution($institution);
//        $part->addBlock($block);
//        $nextKey = $this->em->getRepository('AppOrderformBundle:Block')->getNextNonProvided($block);
//        echo "next key=".$nextKey."<br>";
//
//        return $nextKey;
//    }


    public function getEventLogDescription( $message, $patient, $encounter )
    {

        //PatientLastName, Patient FirstName (DOB: MM/DD/YY, [Gender], [MRN Type(short name)]: [MRN])
        // at [EncounterLocation'sName] / [EncounterLocation'sPhoneNumber]
        // referred by [ReferringProvider] ([Specialty], [Phone Number]/[ReferringProviderEmail])
        // for [MessageType:Service] / [MessageType:Issue]
        if( $patient && $patient->getId() ) {
            $event = "";
            //PatientLastName, Patient FirstName (DOB: MM/DD/YY, [Gender], [MRN Type(short name)]: [MRN])
            if ($patient && $patient->getId()) {
                $event .= $patient->obtainPatientInfoSimple();
            }
            //echo 'patient event=' . $event . "<br>";

            $addInfo = "";
            // at [EncounterLocation'sName] / [EncounterLocation'sPhoneNumber]
            $encounterLocation = $encounter->obtainLocationInfo();
            if ($encounterLocation) {
                $addInfo .= " at " . $encounterLocation;
            }

            // referred by [ReferringProvider] ([Specialty], [Phone Number]/[ReferringProviderEmail])
            $referringProviderInfo = $encounter->obtainReferringProviderInfo();
            if ($referringProviderInfo) {
                $addInfo = $addInfo . " referred by " . $referringProviderInfo;
            }

            // for [MessageType:Service] / [MessageType:Issue]
            $messageCategoryInfo = $this->getMessageCategoryString($message);
            if ($messageCategoryInfo) {
                $addInfo = $addInfo . " for " . $messageCategoryInfo;
            }

            if( $addInfo ) {
                $event = $event . $addInfo . ".";
            } else {
                $event = "Pathology Department was contacted regarding " . $event . ".";
            }

        } else {

            $event = "";

            $referringProviderInfo = $encounter->obtainReferringProviderInfo();

            // for [MessageType:Service] / [MessageType:Issue]
            $messageCategoryInfo = $this->getMessageCategoryString($message);
            //echo "messageCategoryInfo=".$messageCategoryInfo."<br>";

            //if the Patient is not present / patient is not identified, but the Encounter Location is provided:
            $encounterLocation = $encounter->obtainLocationInfo();
            if( $encounterLocation ) {
                if( !$referringProviderInfo ) {
                    $referringProviderInfo = "Pathology Department was contacted";
                }
                //firstname lastname - cwid (Blood Bank Personnel, [Phone Number]/[ReferringProviderEmail])
                // from 5th floor / 12345 reached out regarding Transfusion Medicine / First dose plasma.
                if( $messageCategoryInfo ) {
                    $event .= $referringProviderInfo . " from " . $encounterLocation . " reached out regarding " . $messageCategoryInfo . ".";
                } else {
                    $event .= $referringProviderInfo . " from " . $encounterLocation . " reached out to the Pathology Department.";
                }

            } else {
                //firstname lastname - cwid (Blood Bank Personnel, [Phone Number]/[ReferringProviderEmail])
                // reached out regarding Transfusion Medicine / First dose plasma.
                if( $messageCategoryInfo ) {
                    if( !$referringProviderInfo ) {
                        $referringProviderInfo = "Pathology Department was contacted";
                    }
                    $event .= $referringProviderInfo . " regarding " . $messageCategoryInfo . ".";
                } else {
                    //...the Patient is not present / patient is not identified, AND the Encounter Location is not provided AND Referring Provider Info is not present:
                    if( $referringProviderInfo ) {
                        $event .= $referringProviderInfo . " reached out Pathology Department.";
                    } else {
                        //Pathology Department was contacted.
                        $event .= "Pathology Department was contacted.";
                    }
                }
            }

        }//if

        //add [Attending: firstname lastname - cwid]
        if( $encounter ) {
            $attendingInfo = $encounter->obtainAttendingPhysicianInfo();
            if( $attendingInfo ) {
                $event = $event . " [Attending Physician: " . $attendingInfo . "]";
            }
        }

        //add message info: ID and status
        $event = $event . " [Message Entry ID#" . $message->getMessageOidVersion() . "; Status: " . $message->getMessageStatus()->getName() . "]";

        //Tasks Info
        $taskInfoArr = array();
        foreach( $message->getCalllogEntryMessage()->getCalllogTasks() as $task ) {
            $taskInfoArr[] = $task->getTaskFullInfo();
        }
        if( count($taskInfoArr) > 0 ) {
            $event = $event . "<br><br>" . implode("<br>",$taskInfoArr);
        }

        //exit('event='.$event);
        return $event;
    }

    //[MessageType:Service] / [MessageType:Issue]
    public function getMessageCategoryString($message) {
        $info = "";
        if( $message->getMessageCategory() ) {
            //echo "case 1 cat=".$message->getMessageCategory()."<br>";
            $nodes = $message->getMessageCategory()->getEntityBreadcrumbs();
            $infoArr = array();
            foreach( $nodes as $node ) {
                //echo "node=".$node."<br>";
                if( $node->getOrganizationalGroupType() ) {
                    $orgGroupName = $node->getOrganizationalGroupType()->getName() . "";
                    //echo "orgGroupName=".$orgGroupName."<br>";
                    if ($orgGroupName == "Issue" || $orgGroupName == "Service") {
                        $infoArr[] = $node->getName() . "";
                    }
                }
            }
            $info = implode(" / ",$infoArr);
        }

        if( !$info ){
            //echo "case 2<br>";
            //$info = "Message ID# ".$message->getId();
            //$info = "Pathology Department";
        }

        return $info;
    }



    public function getPatientList() {

        $patientLists = $this->getDefaultPatientLists();

        //$request = $this->container->get('request');
        //$siteName = 'call-log-book';

        //list.name = "Pathology Call Complex Patients"
        //list.url = "http://collage.med.cornell.edu/order/call-log-book/patient-list/pathology-call-complex-patients"
        $resList = array();

        $listId = "recent-patient-96-hours";
        $listName = "Recent Patients (96 hours)";
        $url = $this->container->get('router')->generate('calllog_recent_patients');
        $resList[] = array(
            'listid' => $listId,
            'name' => $listName,
            'url' => $url   //"order/call-log-book/patient-list/pathology-call-complex-patients"
        );

        foreach( $patientLists as $list ) {

            //$listUrl = "patient-list/pathology-call-complex-patients";
            //$listUrl = "complex-patient-list";
            //$baseUrl = $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath();
            //$url = $baseUrl . '/' . $siteName . '/' . $listUrl;

            $listName = $list->getName()."";
            $listNameUrl = str_replace(" ","-",$listName);
            $listNameUrl = strtolower($listNameUrl);

            //path(calllog_sitename~'_complex_patient_list')
            $url = $this->container->get('router')->generate('calllog_complex_patient_list',array('listname'=>$listNameUrl,'listid'=>$list->getId()));

            $resList[] = array(
                'listid' => $list->getId(),
                'name' => $list->getName()."",
                'url' => $url   //"order/call-log-book/patient-list/pathology-call-complex-patients"
            );
        }

        return $resList;
    }

    //get default patient list objects
    public function getDefaultPatientLists() {
        //patient list currently is level=3
        $level = 3;

        //process.py script: replaced namespace by ::class: ['AppOrderformBundle:PatientListHierarchy'] by [PatientListHierarchy::class]
        $parent = $this->em->getRepository(PatientListHierarchy::class)->findOneByName("Pathology Call Log Book Lists");
        
        //process.py script: replaced namespace by ::class: ['AppOrderformBundle:PatientListHierarchy'] by [PatientListHierarchy::class]
        $patientLists = $this->em->getRepository(PatientListHierarchy::class)->findBy(
            array(
                'type' => array('default','user-added'),
                'level' => $level,
                'parent' => $parent->getId()
            )
        );

        //PatientListHierarchy
        //AppOrderformBundle
//        $patientLists = $this->em->getRepository('AppOrderformBundle:PatientListHierarchy')->findBy(
//            array(
//                'type' => array('default','user-added'),
//                'level' => $level
//            )
//        );

        return $patientLists;
    }

    //if the location id is provided, then find this location in DB by id and replace it.
    //TODO: if the location name is already exist in DB replace it?
    public function processTrackerLocation($encounter) {
        //[tracker][spots][0][currentLocation][name]
        if( !$encounter->getTracker() ) {
            //echo "return: no tracker found in the encounter=".$encounter."<br>";
            return "";
        }

        //echo "spot count=".count($encounter->getTracker()->getSpots())."<br>";

        foreach( $encounter->getTracker()->getSpots() as $spot ) {
            $location = $spot->getCurrentLocation();
            //echo "location=".$location."<br>";
            if( $location ) {

                $locationDb = NULL;

                if( $location->getId() ) {
                    //echo "find location by ID=".$location->getId()."<br>";
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:Location'] by [Location::class]
                    $locationDb = $this->em->getRepository(Location::class)->find($location->getId());
                    if( $locationDb ) {
                        //echo "set the found location by ID=".$location->getId()."<br>";
                        $spot->setCurrentLocation($locationDb);
                    } else {
                        //echo "use and create a current location =".$location->getName()."<br>";
                    }
                }

                if( !$locationDb ) {

                    ///////// re-set name by current institution if institution name is default (location name has not been changed) /////////
                    $userSecUtil = $this->container->get('user_security_utility');
                    $sitename = $this->container->getParameter('calllog.sitename');
                    $defaultInstitution = $userSecUtil->getSiteSettingParameter('institution',$sitename);
                    if( $defaultInstitution ) {
                        $defaultLocationName = $defaultInstitution->getName();//." Location";
                    }
                    if( $defaultLocationName && $location->getInstitution() ) {
                        //echo "compare location name:[".$location->getName()."]==[".$defaultLocationName."] <br>";
                        if( $location->getName() == $defaultLocationName ) {
                            $newLocationName = $location->getInstitution()->getName();//." Location";
                            $location->setName($newLocationName);
                        }
                    }
                    //echo "locationName=".$location->getName()."<br>";
                    ///////// EOF re-set name by current institution if institution name is default (location name has not been changed) /////////

                    //$location = new Location(); //testing
                    $search = false;
                    $parameters = array();

                    //if id is not provided, then find location by the fields (except prefilled locationTypes="Encounter Location" and name="New York Hospital Location")
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:Location'] by [Location::class]
                    $repository = $this->em->getRepository(Location::class);
                    $dql = $repository->createQueryBuilder("list");

                    //exit("before search location");

                    if( $location->getName() ) {
                        $dql->andWhere("list.name = :locationName");
                        $parameters['locationName'] = $location->getName();
                        $search = true;
                    }

                    if( $location->getInstitution() ) {
                        //echo "location Institution =".$location->getInstitution()."<br>";
                        $dql->andWhere("list.institution = :institutionId");
                        $parameters['institutionId'] = $location->getInstitution()->getId();
                        $search = true;
                    }

                    if( $location->getPhone() ) {
                        $dql->andWhere("list.phone = :phone");
                        $parameters['phone'] = $location->getPhone();
                        $search = true;
                    }

                    if( $location->getRoom() ) {
                        $dql->andWhere("list.room = :room");
                        $parameters['room'] = $location->getRoom()->getId();
                        $search = true;
                    }

                    if( $location->getSuite() ) {
                        $dql->andWhere("list.suite = :suite");
                        $parameters['suite'] = $location->getSuite()->getId();
                        $search = true;
                    }

                    if( $location->getFloor() ) {
                        $dql->andWhere("list.floor = :floor");
                        $parameters['floor'] = $location->getFloor()->getId();
                        $search = true;
                    }

                    if( $location->getFloorSide() ) {
                        $dql->andWhere("list.floorSide = :floorSide");
                        $parameters['floorSide'] = $location->getFloorSide();
                        $search = true;
                    }

                    if( $location->getBuilding() ) {
                        $dql->andWhere("list.building = :building");
                        $parameters['building'] = $location->getBuilding()->getId();
                        $search = true;
                    }

                    if( $location->getComment() ) {
                        $dql->andWhere("list.comment = :comment");
                        $parameters['comment'] = $location->getComment();
                        $search = true;
                    }

                    $geoLocation = $location->getGeoLocation();
                    if( $geoLocation ) {
                        $dql->leftJoin('list.geoLocation', 'geoLocation');
                        //$geoLocation = new GeoLocation(); //testing
                        if( $geoLocation->getStreet1() ) {
                            $dql->andWhere("geoLocation.street1 = :street1");
                            $parameters['street1'] = $geoLocation->getStreet1();
                            $search = true;
                        }

                        if( $geoLocation->getStreet2() ) {
                            $dql->andWhere("geoLocation.street2 = :street2");
                            $parameters['street2'] = $geoLocation->getStreet2();
                            $search = true;
                        }

                        if( $geoLocation->getCity() ) {
                            $dql->andWhere("geoLocation.city = :city");
                            $parameters['city'] = $geoLocation->getCity()->getId();
                            $search = true;
                        }

                        if( $geoLocation->getState() ) {
                            $dql->andWhere("geoLocation.state = :state");
                            $parameters['state'] = $geoLocation->getState()->getId();
                            $search = true;
                        }

                        if( $geoLocation->getZip() ) {
                            $dql->andWhere("geoLocation.zip = :zip");
                            $parameters['zip'] = $geoLocation->getZip();
                            $search = true;
                        }

                        if( $geoLocation->getCounty() ) {
                            $dql->andWhere("geoLocation.county = :county");
                            $parameters['county'] = $geoLocation->getCounty();
                            $search = true;
                        }

                        if( $geoLocation->getCountry() ) {
                            $dql->andWhere("geoLocation.country = :country");
                            $parameters['country'] = $geoLocation->getCountry()->getId();
                            $search = true;
                        }
                    } //if geoLocation

                    if( $search ) {
                        //EncounterType by alone is not enough to make search
                        $dql->leftJoin('list.locationTypes', 'locationTypes');
                        $dql->andWhere("locationTypes.name = :locationTypesName");
                        $parameters['locationTypesName'] = "Encounter Location";

                        $dql->orderBy("list.id", "DESC"); //last entered showed first

                        //$dql->andWhere("(list.type = :typedef OR list.type = :typeadd)");
                        //$parameters['typedef'] = 'default';
                        //$parameters['typeadd'] = 'user-added';

                        $query = $dql->getQuery();

                        if (count($parameters) > 0) {
                            //print_r($parameters);
                            $query->setParameters($parameters);
                        }

                        $locations = $query->getResult();

                        $testing = false;
                        if( $testing ) {
                            print_r($parameters);
                            echo "locations count=" . count($locations) . "<br>";
                            foreach ($locations as $location) {
                                echo "location=" . $location->getId() . "; getNameFull=" . $location->getNameFull() . "; getLocationAddress=" . $location->getLocationAddress() . "<br>";
                            }
                            exit('end of process location');
                        }

                        if( count($locations) > 0 ) {
                            $locationDb = $locations[0]; //use the last created location
                            $spot->setCurrentLocation($locationDb);
                            return $locationDb;
                        }
                    }//if search
                }//if( $location->getId() )  else
            }//$location
        }//foreach spots

        return NULL;
    }

    //set Specialty, Phone and Email for a new userWrapper
    public function processReferringProviders($encounter,$source=null) {
        foreach( $encounter->getReferringProviders() as $referringProvider ) {
            $userWrapper = $referringProvider->getField();

            if( $userWrapper ) {

                $phone = $referringProvider->getReferringProviderPhone();
                if( $phone ) {
                    $userWrapper->setUserWrapperPhone($phone);
                }

                $phone = $referringProvider->getReferringProviderPhone();
                if( $phone && !$userWrapper->getUserWrapperPhone() ) {
                    $userWrapper->setUserWrapperPhone($phone);
                }

                $email = $referringProvider->getReferringProviderEmail();
                if( $email && !$userWrapper->getUserWrapperEmail() ) {
                    $userWrapper->setUserWrapperEmail($email);
                }

                $specialty = $referringProvider->getReferringProviderSpecialty();
                if( $specialty && !$userWrapper->getUserWrapperSpecialty() ) {
                    $userWrapper->setUserWrapperSpecialty($specialty);
                }

                //$referringProviderCommunication
                $communication = $referringProvider->getReferringProviderCommunication();
                if( $communication && !$userWrapper->getUserWrapperCommunication() ) {
                    $userWrapper->setUserWrapperCommunication($communication);
                }

                //source
                if( $source && !$userWrapper->getUserWrapperSource() ) {
                    $userWrapper->setUserWrapperSource($source);
                }

            }
        }
    }

    public function getNavbarFilterForm($request) {
        $params = array();
        $params['navbarSearchTypes'] = $this->getNavbarSearchTypes();
        $params['container'] = $this->container;

        //get submitted parameters
        $navbarfilterform = $this->createForm(CalllogNavbarFilterType::class, null, array(
            //'action' => $this->generateUrl('calllog_home'),
            'method'=>'GET',
            'form_custom_value'=>$params
        ));
        //$navbarfilterform->submit($request);
        $navbarfilterform->handleRequest($request);
        $calllogsearchtype = $navbarfilterform['searchtype']->getData();
        $calllogsearch = $navbarfilterform['search']->getData();

        $params['calllogsearchtype'] = $calllogsearchtype;
        $params['calllogsearch'] = $calllogsearch;

        //build final filter form
        $navbarfilterform = $this->createForm(CalllogNavbarFilterType::class, null, array(
            'method'=>'GET',
            'form_custom_value'=>$params
        ));

        //echo "calllogsearchtype=".$calllogsearchtype."; calllogsearch=".$calllogsearch."<br>";
        return $navbarfilterform->createView();
    }
    public function getNavbarSearchTypes() {

        $navbarSearchTypes = array(
            'MRN or Last Name' => 'MRN or Last Name',
            //'NYH MRN' => 'NYH MRN',
            'Last Name' => 'Last Name',
            'Last Name similar to' => 'Last Name similar to',
            'Message Type' => 'Message Type',
            'Entry full text' => 'Entry full text'
        );

        $mrnType = $this->getDefaultMrnType();
        if( $mrnType ) {
            $mrnTypeStr = $mrnType."";
            $inserted = array($mrnTypeStr=>$mrnTypeStr);
            $navbarSearchTypes = $this->array_insert_after($navbarSearchTypes,'MRN or Last Name',$inserted);
        }

        //check if metaphone is enabled
        $userSecUtil = $this->container->get('user_security_utility');
        if( !$userSecUtil->getSiteSettingParameter('enableMetaphone') ) {
            unset($navbarSearchTypes['Last Name similar to']);
        }

        return $navbarSearchTypes;
    }
    public function createForm($type, $data = null, array $options = array())
    {
        //return $this->container->get('form.factory')->create($type, $data, $options);
        //return $this->container->get('user_utility')->createForm($type, $data, $options);
        return $this->formFactory->create($type, $data, $options);
    }

    /**
     * Insert a value or key/value pair after a specific key in an array.  If key doesn't exist, value is appended
     * to the end of the array.
     *
     * @param array $array
     * @param string $key
     * @param array $new
     *
     * @return array
     */
    function array_insert_after( array $array, $key, array $new ) {
        $keys = array_keys( $array );
        $index = array_search( $key, $keys );
        $pos = false === $index ? count( $array ) : $index + 1;
        return array_merge( array_slice( $array, 0, $pos ), $new, array_slice( $array, $pos ) );
    }

    //Upon submission of a new entry on /entry/new , send an email to the Preferred Email of the "Attending:"
    // with the following info
    // (Patient Info like Name and/or MRN should never be sent via email, so even if the entry has patient info, treat it as if patient info is missing):
    public function sendConfirmationEmail($message,$patient,$encounter) {
        $userServiceUtil = $this->container->get('user_service_utility');

        //only send the notification email if the box noAttendingEmail is not checked
        $currentUser = $this->security->getUser();
        if( $currentUser && $currentUser->getPreferences() ) {
            $noAttendingEmail = $currentUser->getPreferences()->getNoAttendingEmail();
            if( $noAttendingEmail ) {
                //echo "Do not send a confirmation email to attendings <br>";
                return;
            }
        }

        $attendings = $encounter->getAttendingPhysicians();
        if( count($attendings) == 0 ) {
            return;
        }

        //$break = "\r\n";
        $break = "<br>";

        $emails = array();
        foreach( $attendings as $attending ) {
            $emails[] = $attending->getEmail();
        }
        //echo "Send a confirmation email to attendings: ".implode("; ",$emails)."<br>";

        $submitter = $message->getProvider();

        $senderEmail = null;
        if( $submitter ) {
            $senderEmail = $submitter->getSingleEmail();
        }

        //Subject: [Call Log Book] <FirstNameOfSubmitter LastNameOfSubmitter> added a new entry
        $subject = "[Call Log Book] ".$submitter->getUsernameOptimal()." added a new entry";

        //use the "If the Patient is not present / patient is not identified" variation to avoid sending patient info by email
        $body = $this->getEventLogDescription($message,null,$encounter);

        if( $message->getId() ) {
            //View the Pathology Call Log Book entry 12345 submitted by SubmitterFirstName SubmitterLastName at [submission timestamp] by visiting:
            $body = $body . $break . $break . "View the Pathology Call Log Book entry " . $message->getId() . " submitted on " . $userServiceUtil->getSubmitterInfo($message) . " by visiting:";

            // http://collage.med.cornell.edu/order/call-log-book/entry/view/XXXID
            $messageUrl = $this->container->get('router')->generate(
                'calllog_callentry_view',
                array(
                    'messageOid' => $message->getOid(),
                    'messageVersion' => $message->getVersion()
                ),
                UrlGeneratorInterface::ABSOLUTE_URL
            );
            $body = $body . $break . $messageUrl;
        } else {
            $body = $body . $break . $break . "The Pathology Call Log Book entry submitted on " . $userServiceUtil->getSubmitterInfo($message);
        }

        //exit('body='.$body);

        $emailUtil = $this->container->get('user_mailer_utility');
        //                    $emails, $subject, $message, $ccs=null, $fromEmail=null
        $emailUtil->sendEmail( $emails, $subject, $body, null, $senderEmail );

        //testing
//        $eventType = "New Call Log Book Entry Submitted";
//        $user = $this->security->getUser();
//        $userSecUtil = $this->container->get('user_security_utility');
//        $userSecUtil->createUserEditEvent($this->container->getParameter('calllog.sitename'), $body, $user, $message, null, $eventType);
    }


    //create a new PatientListHierarchy node and add as a child to the $patientList
    public function addToPatientLists( $patient, $message, $testing ) {

        //echo "patientList count=".count($calllogMessage = $message->getCalllogEntryMessage()->getPatientLists())."<br>";

        //check if addPatientToList is checked
        if( $message->getCalllogEntryMessage() ) {
            if( !$message->getCalllogEntryMessage()->getAddPatientToList() ) {
                //echo "AddPatientToList is NULL <br>";
                return null;
            }
        } else {
            return null;
        }
        //echo "continue addToPatientLists<br>";
        //exit('1');

        if( !$patient ) {
            return null;
        }

        $calllogMessage = $message->getCalllogEntryMessage();
        if( !$calllogMessage ) {
            return null;
        }

        $patientLists = $calllogMessage->getPatientLists();
        if( count($patientLists) == 0 ) {
            return null;
        }

        $newListElements = $this->addPatientToPatientLists($patient,$patientLists,$message,$testing);

        return $newListElements;
    }

    public function addPatientToPatientLists( $patient, $patientLists, $message=null, $testing=false ) {
        if( !$patient ) {
            return null;
        }
        if( count($patientLists) == 0 ) {
            return null;
        }

        $newListElementArr = array();

        foreach( $patientLists as $patientList ) {
            //echo "patientList=".$patientList."<br>";
            $newListElement = $this->addPatientToPatientList( $patient,$patientList,$message,$testing);
            if( $newListElement ) {
                $newListElementArr[] = $newListElement;
            }
        }

        return $newListElementArr;
    }
    public function addPatientToPatientList( $patient, $patientList, $message=null, $testing=false ) {

        if( !$patient ) {
            return null;
        }

        if( !$patientList ) {
            return null;
        }

        $userSecUtil = $this->container->get('user_security_utility');
        $user = $this->security->getUser();

        //add only if the patient does not exists in the list
        $similarPatients = $this->getSamePatientsInList($patientList,$patient);
        if( $similarPatients && count($similarPatients) > 0 ) {
            //check and set type to user-added if type is disabled
            foreach( $similarPatients as $similarPatient ) {
                if( $similarPatient->getType() == 'disabled' ) {
                    $similarPatient->setType('user-added');
                    if( !$testing ) {
                        $this->em->flush();
                    }
                    return $similarPatient;
                }
            }
            return null;
        }

        //create a new node in the list PatientListHierarchyand attach it as a child to the $patientList
        $newListElement = new PatientListHierarchy();

        $patientDescription = "Patient ID# " . $patient->getId() . ": " . $patient->obtainPatientInfoTitle();
        $patientName = "Patient ID# " . $patient->getId();
        $count = null;
        $userSecUtil->setDefaultList($newListElement, $count, $user, $patientName);
        $newListElement->setPatient($patient);
        $newListElement->setDescription($patientDescription);

        if( $message ) {
            $newListElement->setObject($message);
        }

        //tree variables
        //set level
        $level = $patientList->getLevel();
        if( !$level ) {
            $defaultPatientList = $this->getDefaultPatientList();
            if( $defaultPatientList ) {
                //set level the same as default patient list
                $level = $defaultPatientList->getLevel();
                $patientList->setLevel($level);
                //attach this new patient list to the parent of the default patient list
                $defaultPatientListParent = $defaultPatientList->getParent();
                if( $defaultPatientListParent ) {
                    $defaultPatientListParent->addChild($patientList);
                }
            }
        }
        //echo "level=$level ";
        $level = $level + 1;
        //echo " (+1)=> $level <br>";
        $newListElement->setLevel($level);
        //set group
        //process.py script: replaced namespace by ::class: ['AppOrderformBundle:PatientListHierarchyGroupType'] by [PatientListHierarchyGroupType::class]
        $group = $this->em->getRepository(PatientListHierarchyGroupType::class)->findOneByName('Patient');
        $newListElement->setOrganizationalGroupType($group);

        $patientList->addChild($newListElement);

        $this->em->persist($newListElement);

        if( !$testing ) {
            $this->em->flush();
        }

        return $newListElement;
    }

    public function getSamePatientsInList( $patientList, $patient ) {
        if( $patientList && $patientList->getId() && $patient && $patient->getId() ) {
            //ok continue
        } else {
            return null;
        }
        //process.py script: replaced namespace by ::class: ['AppOrderformBundle:PatientListHierarchy'] by [PatientListHierarchy::class]
        $repository = $this->em->getRepository(PatientListHierarchy::class);
        $dql = $repository->createQueryBuilder("list");

        $dql->where("list.parent = :parentId AND list.patient = :patientId");
        $parameters['parentId'] = $patientList->getId();
        $parameters['patientId'] = $patient->getId();

        $dql->andWhere("(list.type = :typedef OR list.type = :typeadd)");
        $parameters['typedef'] = 'default';
        $parameters['typeadd'] = 'user-added';

        $query = $dql->getQuery();
        $query->setParameters($parameters);
        $patients = $query->getResult();
        if( count($patients) > 0 ) {
            return $patients;
        }
        return null;
    }

    //create a new AccessionListHierarchy node and add as a child to the $accessionList
    public function addToCalllogAccessionLists( $message, $testing ) {
        if( !$message->getAddAccessionToList() ) {
            //echo "addAccessionToList is NULL <br>";
            return null;
        }

        $scanorderUtil = $this->container->get('scanorder_utility');
        $accessionListType = $this->getCalllogAccessionListType();
        return $scanorderUtil->addToAccessionLists( $accessionListType, $message, $testing );
    }

    //get Accession lists by CallLog's accession type (similar to getPatientList())
    public function getAccessionList() {

        $scanorderUtil = $this->container->get('scanorder_utility');
        $accessionListType = $this->getCalllogAccessionListType();

        $accessionLists = $scanorderUtil->getDefaultAccessionLists(1,$accessionListType);

        //list.url = "http://collage.med.cornell.edu/order/calllog-book/accession-list/calllog-accessions"
        $resList = array();

        $listId = "recent-accession-96-hours";
        $listName = "Recent Accessions (96 hours)";
        $url = $this->container->get('router')->generate('calllog_recent_accessions');
        $resList[] = array(
            'listid' => $listId,
            'name' => $listName,
            'url' => $url   //"order/calllog/accession-list/calllog-accessions"
        );

        foreach( $accessionLists as $list ) {

            $listName = $list->getName()."";
            $listNameUrl = str_replace(" ","-",$listName);
            $listNameUrl = strtolower($listNameUrl);

            $url = $this->container->get('router')->generate('calllog_accession_list',array('listname'=>$listNameUrl,'listid'=>$list->getId()));

            $resList[] = array(
                'listid' => $list->getId(),
                'name' => $list->getName()."",
                'url' => $url   //"order/calllog/accession-list/calllog-accessions"
            );
        }

        return $resList;
    }

//    public function getDefaultAccessionList() {
//        $scanorderUtil = $this->container->get('scanorder_utility');
//        $accessionListType = $this->getCalllogAccessionListType();
//
//        $accessionLists = $scanorderUtil->getDefaultAccessionLists(1,$accessionListType);
//
//        foreach( $accessionLists as $accessionList ) {
//            if( $accessionList->getName()."" == 'Accessions for Follow-Up' ) {
//                return $accessionList;
//            }
//        }
//
//        return NULL;
//    }

    public function getDefaultPatientList() {

        $userSecUtil = $this->container->get('user_security_utility');
        $sitename = $this->container->getParameter('calllog.sitename');
        $patientList = $userSecUtil->getSiteSettingParameter('patientList',$sitename);
        //echo "patientList=".$patientList."<br>";

        if( !$patientList ) {
            $patientListName = "Pathology Call Complex Patients";

            //$patientList = $this->em->getRepository('AppOrderformBundle:PatientListHierarchy')->findOneByName($patientListName);
            $patientList = null;

        //process.py script: replaced namespace by ::class: ['AppOrderformBundle:PatientListHierarchy'] by [PatientListHierarchy::class]
            $patientLists = $this->em->getRepository(PatientListHierarchy::class)->findBy(array('name'=>$patientListName,'type'=>array('default','user-added')));
            if( count($patientLists) > 0 ) {
                $patientList = $patientLists[0];
            }
        }

        if( !$patientList ) {
            throw new \Exception( "Patient list is not found by name '".$patientListName."'" );
        }
        return $patientList;
    }

    // If the user types in the Date of Birth, it should be added to the "Patient" hierarchy level
    // of the selected patient as a "valid" value and the previous "valid" value should be marked "invalid" on the server side.
    public function updatePatientInfoFromEncounter( $patient, $encounter, $user, $system ) {
        //add new DOB (if exists) to the Patient
        //Use unmapped encounter's "patientDob" to update patient's DOB
        $patientDob = $encounter->getPatientDob();
        if( $patientDob ) {
            //invalidate all other patient's DOB
//            $validDOBs = $patient->obtainStatusFieldArray("dob","valid");
//            foreach( $validDOBs as $validDOB) {
//                $validDOB->setStatus("invalid");
//            }
            //$patient->changeStatusAllFields('dob','valid','invalid');

            //echo "encounter patientDob=" . $patientDob->format('Y-m-d') . "<br>";
            $newPatientDob = new PatientDob("valid",$user,$system);
            $newPatientDob->setField($patientDob);
            $patient->addDob($newPatientDob);
            //echo "patient patientDob=" . $newPatientDob . "<br>";
        }

        //lastname
        $lastName = $encounter->obtainValidField('patlastname');
        if( $lastName && !$lastName->getAlias() ) {
            //invalidate all other patient's lastname
            //$patient->changeStatusAllFields('lastname', 'valid', 'invalid');

            $newPatientLastname = new PatientLastName("valid",$user,$system);
            $newPatientLastname->setField($lastName->getField());
            $patient->addLastname($newPatientLastname);
        }

        //firstname
        $firstname = $encounter->obtainValidField('patfirstname');
        if( $firstname && !$firstname->getAlias() ) {
            //invalidate all other patient's firstname
            //$patient->changeStatusAllFields('firstname','valid','invalid');

            $newPatientFirstname = new PatientFirstName("valid",$user,$system);
            $newPatientFirstname->setField($firstname->getField());
            $patient->addFirstname($newPatientFirstname);
        }

        //middlename
        $middlename = $encounter->obtainValidField('patmiddlename');
        if( $middlename && !$middlename->getAlias() ) {
            //invalidate all other patient's middlename
            //$patient->changeStatusAllFields('middlename','valid','invalid');

            $newPatientMiddlename = new PatientMiddleName("valid",$user,$system);
            $newPatientMiddlename->setField($middlename->getField());
            $patient->addMiddlename($newPatientMiddlename);
        }

        //suffix
        $suffix = $encounter->obtainValidField('patsuffix');
        if( $suffix && !$suffix->getAlias() ) {
            //invalidate all other patient's suffix
            //$patient->changeStatusAllFields('suffix','valid','invalid');

            $newPatientsuffix = new PatientSuffix("valid",$user,$system);
            $newPatientsuffix->setField($suffix->getField());
            $patient->addSuffix($newPatientsuffix);
        }

        //sex
        $sex = $encounter->obtainValidField('patsex');
        if( $sex ) {
            //invalidate all other patient's sex
            //$patient->changeStatusAllFields('sex','valid','invalid');

            $newPatientsex = new PatientSex("valid",$user,$system);
            $newPatientsex->setField($sex->getField());
            $patient->addSex($newPatientsex);
        }
    }

    //real users + EncounterReferringProvider's wrappers without linked users
    public function getReferringProvidersWithUserWrappers() {
        $em = $this->em;

        $output = array();
        //return $output;

        ///////////// 1) get all real users /////////////
        $query = $em->createQueryBuilder()
            //->from('AppUserdirectoryBundle:User', 'list')
            ->from(User::class, 'list')
            ->select("list")
            //->groupBy('list.id')
            ->leftJoin("list.infos", "infos")
            ->leftJoin("list.employmentStatus", "employmentStatus")
            ->leftJoin("employmentStatus.employmentType", "employmentType")
            ->where("(employmentType.name != 'Pathology Fellowship Applicant' OR employmentType.id IS NULL)")
            //->andWhere("(employmentType.name NOT LIKE 'Pathology % Applicant' OR employmentType.id IS NULL)")
            ->andWhere("(list.testingAccount = false OR list.testingAccount IS NULL)")
            ->andWhere("(list.keytype IS NOT NULL AND list.primaryPublicUserId != 'system')")
            ->orderBy("infos.displayName", "ASC");

        $users = $query->getQuery()->getResult();
        //echo "users count=".count($users)."<br>";

        foreach ($users as $user) {
            //$output[$user->getId()] = $user . "";
            $output[$user . ""] = $user->getId();
        }
        ///////////// EOF 1) get all real users /////////////


        ///////////// 2) default user wrappers for this source ///////////////
        ///////////// 3) user-added user wrappers created by logged in user for this source ///////////////
        $query = $em->createQueryBuilder()
            //->from('AppUserdirectoryBundle:UserWrapper', 'list')
            ->from(UserWrapper::class, 'list')
            ->select("list")
            ->leftJoin("list.user", "user")
            ->leftJoin("user.infos", "infos")
            ->where("user.id IS NULL")
            ->orderBy("infos.displayName", "ASC");

        //echo "query=".$query." <br><br>";
        //exit();

        $userWrappers = $query->getQuery()->getResult();
        foreach ($userWrappers as $userWrapper) {
            $output[$userWrapper.""] = $userWrapper."";
//                if( !$this->in_complex_array($userWrapper . "", $output, 'id') ) {
//                    $output[] = $element;
//                }

            //print_r($output);
            //exit('1');
        }
        ///////////// EOF 2) 3) user wrappers for this source ///////////////

        //$output = array_merge($users,$output);

        return $output;
    }


    //On the server side write in the "Versions" of the associated forms into this "Form Version" field in the same order as the Form titles+IDs
    public function setFormVersions( $message, $cycle ) {

        //return null; //testing error: 'INSERT INTO scan_formVersion (id, formId, formTitle, formVersion, message_id) VALUES (?, ?, ?, ?, ?)', array(), array())

        $targetMessageCategory = $message->getMessageCategory();
        if( !$targetMessageCategory ) {
            return null;
        }

        $formNodeUtil = $this->container->get('user_formnode_utility');

        $messageCategories = $targetMessageCategory->getEntityBreadcrumbs(); //message category hierarchy

        foreach( $messageCategories as $messageCategory ) {

            //get only 'real' fields as $formNodes
            $formNodes = $formNodeUtil->getAllRealFormNodes($messageCategory,$cycle);

            foreach( $formNodes as $formNode ) {

                $formVersion = new FormVersion();
                //$this->em->persist($formVersion);
                $formVersion->setFormNode($formNode);
                $message->addFormVersion($formVersion);
                //echo "formVersionEntity: ".$formVersion."<br>";
            }

        }

        //exit('form version');
    }


    //If more than one is found, pick the message type where the search string is
    // found earlier in the message type name (for example, if the user searches
    // for "transfusion", you would find both "Payson transfusion" and
    // "Transfusion medicine" message types, but in "Transfusion medicine"
    // the search string starts at character 1, so it should be selected.
    // If there is still more than one matching message type
    // (for example "Transfusion medicine" and "Transfusion reaction"),
    // pick the one that is closer to the root of the hierarchy ("Transfusion medicine").
    // If there is still more than one matching message type, ("Other"),
    // then pick the one with the lowest ID.
    //<option value="Incompatible crossmatch">Pathology Call Log Entry: Transfusion Medicine: Incompatible crossmatch</option>
    public function getMessageTypeByString( $string, $messageCategories, $messageCategorieDefaultIdStr ) {

        //$messageCategories is array: "Incompatible crossmatch" => "Pathology Call Log Entry: Transfusion Medicine: Incompatible crossmatch"
        //foreach( $messageCategories as $name=>$fullname ) {
        foreach( $messageCategories as $fullname=>$name ) {
            //echo $name." ?= ".$fullname."<br>";
            if( stripos ($fullname, $string) !== false ) {
                return $name;
            }
        }

        return $messageCategorieDefaultIdStr;
    }

    //$messageCategoryIdStr: Microbiology_48
    public function getMessageCategoryEntityByIdStr( $messageCategoryIdStr ) {
        if( strpos((string)$messageCategoryIdStr, '_') !== false ) {
            list($messageCategoryStr, $messageCategoryId) = explode('_', $messageCategoryIdStr);
            //echo "search messageCategoryId=".$messageCategoryId."<br>";
        //process.py script: replaced namespace by ::class: ['AppOrderformBundle:MessageCategory'] by [MessageCategory::class]
            $messageCategoryEntity = $this->em->getRepository(MessageCategory::class)->find($messageCategoryId);
        } else {
            $mapper = array(
                'prefix' => "App",
                'className' => "MessageCategory",
                'bundleName' => "OrderformBundle",
                'fullClassName' => "App\\OrderformBundle\\Entity\\MessageCategory",
                'entityNamespace' => "App\\OrderformBundle\\Entity"
            );
            //$messageCategoryEntity = $em->getRepository('AppOrderformBundle:MessageCategory')->findOneByName($messageCategory);
        //process.py script: replaced namespace by ::class: ['AppOrderformBundle:MessageCategory'] by [MessageCategory::class]
            $messageCategoryEntity = $this->em->getRepository(MessageCategory::class)->findNodeByPartialName($messageCategoryIdStr,$mapper);
        }

        return $messageCategoryEntity;
    }

    //determine the new message status based on the current (latest) message version and clicked button.
    //$currentStatusObj
    //$buttonStatusObj - "Draft", "Signed"
    public function getNewMessageStatus( $currentStatusObj, $buttonStatusObj, $oid ) {
        $newMessageStatusObj = $buttonStatusObj;

        if( !$currentStatusObj ) {
            return $newMessageStatusObj;
        }

        $newStatusStr = null;
        $currentStatusStr = $currentStatusObj->getName() . "";
        //echo "currentStatusStr=".$currentStatusStr."<br>";
        //echo "buttonStatus=".$buttonStatusObj->getName()."<br>";

        if( $buttonStatusObj->getName() . "" == "Draft" ) {
            switch ($currentStatusStr) {
                case "Draft":
                    //If current message status = "Draft", and the user clicks "save draft",
                    // save the new copy/version of the message with message status = "Draft".
                    $newStatusStr = "Draft";
                    break;
                case "Deleted":
                    //If current message status = "Deleted", and the user clicks "save draft",
                    // save the new copy/version of the message with message status = "Post-deletion Draft".
                    $newStatusStr = "Post-deletion Draft";
                    break;
                case "Signed":
                    //If current message status = "Signed" and the user clicks "save draft",
                    // save the new copy/version of the message with message status = "Post-signature Draft".
                    $newStatusStr = "Post-signature Draft";
                    break;
                case "Signed, Amended":
                    //If current message status = "Signed, Amended" and the user clicks "save draft",
                    // save the new copy/version of the message with message status = "Post-amendment Draft".
                    $newStatusStr = "Post-amendment Draft";
                    break;
                case "Post-signature Draft":
                    //If current message status = "Post-signature Draft" and the user clicks "save draft",
                    // save the new copy/version of the message with message status = "Post-signature Draft".
                    $newStatusStr = "Post-signature Draft";
                    break;
                case "Post-amendment Draft":
                    //If current message status = "Post-amendment Draft" and the user clicks "save draft",
                    // save the new copy/version of the message with message status = "Post-amendment Draft".
                    $newStatusStr = "Post-amendment Draft";
                    break;
                case "Post-deletion Draft":
                    //If current message status = "Post-deletion Draft", and the user clicks "save draft",
                    // save the new copy/version of the message with message status = "Post-deletion Draft".
                    $newStatusStr = "Post-deletion Draft";
                    break;
            }
        }

        if( $buttonStatusObj->getName() . "" == "Signed" ) {
            switch ($currentStatusStr) {
                case "Draft":
                    $newStatusStr = "Signed";
                    break;
                case "Deleted":
                    //the message status of the submitted message should be set to "Post-deletion Draft"
                    // no matter what the value of the message status is in the received message.
                    $newStatusStr = "Post-deletion Draft";
                    break;
                case "Signed":
                    $newStatusStr = "Signed, Amended";
                    break;
                case "Signed, Amended":
                    $newStatusStr = "Signed, Amended";
                    break;
                case "Post-signature Draft":
                    $newStatusStr = "Signed, Amended";
                    break;
                case "Post-amendment Draft":
                    $newStatusStr = "Signed, Amended";
                    break;
                case "Post-deletion Draft":
                    //If current message status = "Post-deletion Draft",
                    // and the user clicks "Finalize & Sign", check in the Event Log if this message ever had a status of "Signed",

                    //$signedMessages = $this->em->getRepository('AppOrderformBundle:Message')->findByOidAndStatus($oid,"Signed");
                    //if( count($signedMessages) > 0 ) {
                    if( $this->hadMessageStatus($oid,"Signed") ) {
                        // if yes - save the new copy/version of the message with message status = "Signed, Amended",
                        $newStatusStr = "Signed, Amended";
                    } else {
                        // if not - save the new copy/version of the message with message status = "Signed".
                        $newStatusStr = "Signed";
                    }
                    break;
            }
        }

        if( $newStatusStr ) {
        //process.py script: replaced namespace by ::class: ['AppOrderformBundle:MessageStatusList'] by [MessageStatusList::class]
            $newMessageStatusObj = $this->em->getRepository(MessageStatusList::class)->findOneByName($newStatusStr);
        }
        //exit("newMessageStatusObj=".$newMessageStatusObj);

        return $newMessageStatusObj;
    }
    //check in the Event Log if this message ever had a status of "Signed"
    //test event log for [Message Entry ID#:252 .1; Status:Signed]
    public function hadMessageStatus( $oid, $statusName="Signed" ) {
        $sitename = "calllog";

        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:Logger'] by [Logger::class]
        $repository = $this->em->getRepository(Logger::class);
        $dql = $repository->createQueryBuilder("logger");

        $dql->innerJoin('logger.eventType', 'eventType');
        $dql->leftJoin('logger.objectType', 'objectType');
        $dql->leftJoin('logger.site', 'site');

        //$dql->andWhere("logger.entityNamespace = 'App\OrderformBundle\Entity'");
        $dql->andWhere("logger.entityName = 'Message'");
        $dql->andWhere("site.abbreviation = '".$sitename."'");

        //Message Entry ID#:
        $eventStr1 = "Message Entry ID#:".$oid;
        $dql->andWhere("logger.event LIKE :eventStr1");
        $queryParameters['eventStr1'] = '%'.$eventStr1.'%';

        //Message Entry ID#:
        $eventStr2 = "Status:".$statusName;
        $dql->andWhere("logger.event LIKE :eventStr2");
        $queryParameters['eventStr2'] = '%'.$eventStr2.'%';

        $query = $dql->getQuery();
        $query->setParameters( $queryParameters );
        $logs = $query->getResult();
        //echo $oid."[".$statusName."]: logs count=".count($logs)."<br>";

        if( count($logs) > 0 ) {
            return true;
        }

        return false;
    }

    //set encounter status invalid for all other encounter objects found by encounter number and type
    //increment encounter version for current encounter
    public function processEncounterFamily( $encounter ) {
        $maxVersion = 0;

        //get encounters found by provided encounter's key (keytype and number)
        //process.py script: replaced namespace by ::class: ['AppOrderformBundle:Encounter'] by [Encounter::class]
        $otherEncounters = $this->em->getRepository(Encounter::class)->findAllEncountersByEncounter($encounter);

        foreach( $otherEncounters as $otherEncounter ) {
            $otherEncounter->setStatus('invalid');

            $thisVersion = intval($otherEncounter->getVersion());
            if( $thisVersion && $thisVersion > $maxVersion ) {
                $maxVersion = $thisVersion;
            }
        }

        //set status valid for current encounter
        $encounter->setStatus('valid');

        //increment encounter version for current encounter
        $incrementedVersion = $maxVersion + 1;
        //echo "incrementedVersion=".$incrementedVersion."<br>";
        $encounter->setVersion($incrementedVersion);

        return $otherEncounters;
    }

    public function incrementVersionEncounterFamily( $encounter ) {
        //process.py script: replaced namespace by ::class: ['AppOrderformBundle:Encounter'] by [Encounter::class]
        $maxVersion = $this->em->getRepository(Encounter::class)->getMaxEncounterVersion($encounter);
        //increment encounter version for current encounter
        $encounterIncrementedVersion = $maxVersion + 1;
        //echo "encounterIncrementedVersion=".$encounterIncrementedVersion."<br>";
        $encounter->setVersion($encounterIncrementedVersion);
    }

    public function isMessageVersionMatch( $message, $latestNextMessageVersion ) {
        //process.py script: replaced namespace by ::class: ['AppOrderformBundle:Message'] by [Message::class]
        $maxVersion = $this->em->getRepository(Message::class)->getMaxMessageVersion($message);

        //$maxVersion = $maxVersion + 1; //test
        //echo "$maxVersion < $latestNextMessageVersion<br>";
        if( $maxVersion < $latestNextMessageVersion ) {
            return true;
        }

        return false;
    }

    public function isEncounterVersionMatch( $encounter, $latestNextEncounterVersion ) {
        //process.py script: replaced namespace by ::class: ['AppOrderformBundle:Encounter'] by [Encounter::class]
        $maxVersion = $this->em->getRepository(Encounter::class)->getMaxEncounterVersion($encounter);

        //echo "$maxVersion < $latestNextEncounterVersion<br>";
        if( $maxVersion < $latestNextEncounterVersion ) {
            return true;
        }

        return false;
    }

    public function isLatestEncounterVersion( $encounter ) {
        //process.py script: replaced namespace by ::class: ['AppOrderformBundle:Encounter'] by [Encounter::class]
        $maxVersion = $this->em->getRepository(Encounter::class)->getMaxEncounterVersion($encounter);

        //echo "$maxVersion < $latestNextEncounterVersion<br>";
        if( $maxVersion == $encounter->getVersion() ) {
            return true;
        }

        return false;
    }

    public function getUrlWithLatestEncounterIfDifferent( $message, $encounter ) {
        $url = null;    //"test";

        if( !$encounter ) {
            //echo "encounter is null <br>";
            return $url;
        }
        //echo "encounter=".$encounter->getId()."<br>";

        //process.py script: replaced namespace by ::class: ['AppOrderformBundle:Encounter'] by [Encounter::class]
        $maxVersion = $this->em->getRepository(Encounter::class)->getMaxEncounterVersion($encounter);

        //echo "$maxVersion == ".$encounter->getVersion()."<br>";
        if( $maxVersion != $encounter->getVersion() ) {
            // TODO: Create path that will show message with the latest encounter.
            // This will be relevant only for messages with shared encounter object (case: Create a new Message with the Same Encounter)
            $url = $this->container->get('router')->generate(
                'calllog_callentry_view_latest_encounter',
                array(
                    'messageOid' => $message->getOid(),
                    'messageVersion' => $message->getVersion()
                ),
                UrlGeneratorInterface::ABSOLUTE_URL
            );
            $url = 'Please click <a href="' . $url . '" target="_blank">HERE</a> to see the latest updated encounter information on a new page.';
        }

        return $url;
    }


    public function copyEncounterBackupToMessage( $message, $encounter ) {

        $calllogEntryMessage = $message->getCalllogEntryMessage();
        if( !$calllogEntryMessage ) {
            return;
        }

        $key = $encounter->obtainAllKeyfield()->first();

        if( $key ) {
            //Encounter Number Type Backup
            $keytype = $key->getKeytype();
            if( $keytype ) {
                //echo "Set keytype=$keytype <br>";
                $calllogEntryMessage->setEncounterTypeBackup($keytype);
            }

            //Encounter Number Backup
            $number = $key->getField();
            if( $number ) {
                //echo "Set number=$number <br>";
                $calllogEntryMessage->setEncounterNumberBackup($number);
            }
        }

        //Encounter Date Backup
        $date = $encounter->getDate()->first();
        if( $date ) {
            //echo "Set date=".$date."<br>";
            //$calllogEntryMessage->setEncounterDateBackup($date);
            //Construct a new EncounterDate
            $encounterDate = new EncounterDate($date->getStatus(),$date->getProvider(),$date->getSource());

            //not for previous encounter
            $encounterDates = $encounter->getDate();
            if( count($encounterDates) == 0 ) {
                $encounterDate->setEncounter($encounter);
            }

            $encounterDate->setField($date->getField());
            $encounterDate->setTime($date->getTime());
            $encounterDate->setTimezone($date->getTimezone());
            $calllogEntryMessage->setEncounterDateBackup($encounterDate);
        }
    }

    public function copyPatientBackupToMessage( $message, $patient ) {
        if( !$patient ) {
            return;
        }

        $calllogEntryMessage = $message->getCalllogEntryMessage();
        if( !$calllogEntryMessage ) {
            return;
        }

        $status = 'valid';

        //Patient Last Name Backup
        $lastName = $patient->obtainStatusField('lastname', $status);
        if( $lastName ) {
            $calllogEntryMessage->setPatientLastNameBackup($lastName);
        }

        //Patient First Name Backup
        $firstName = $patient->obtainStatusField('firstname', $status);
        if( $firstName ) {
            $calllogEntryMessage->setPatientFirstNameBackup($firstName);
        }

        //Patient Middle Name Backup
        $middleName = $patient->obtainStatusField('middlename', $status);
        if( $middleName ) {
            $calllogEntryMessage->setPatientMiddleNameBackup($middleName);
        }

        //Patient Date of Birth Backup
        $dob = $patient->obtainStatusField('dob', $status);
        if( $dob && $dob->getField() ) {
            $calllogEntryMessage->setPatientDOBBackup($dob->getField());
        }

        $mrnRes = $patient->obtainStatusField('mrn', $status);
        if( $mrnRes ) {
            //Patient MRN Type Backup
            $mrntype = $mrnRes->getKeytype();
            if( $mrntype ) {
                $calllogEntryMessage->setPatientMRNTypeBackup($mrntype);
            }

            //Patient MRN Backup
            $mrn = $mrnRes->getField();
            if( $mrn ) {
                $calllogEntryMessage->setPatientMRNBackup($mrn);
            }
        }

    }

    public function getTotalTimeSpentMinutes( $user=null ) {
        if( !$user ) {
            $user = $this->security->getUser();
        }

        $msg = null;

        $monday = strtotime( 'monday this week' );
        $sunday = strtotime( 'sunday this week' );
        //echo "strtotime( 'monday this week' )=".$monday."<br>";

        $mondayStr = date( 'm/d/Y', $monday );
        $sundayStr = date( 'm/d/Y', $sunday );

        $mondayDBStr = date( 'Y-m-d', $monday );
        $sundayDBStr = date( 'Y-m-d', $sunday );
        //echo "sundayDBStr=".$sundayDBStr."<br>";

        //get the sum of timeSpentMinutes from CalllogEntryMessage for Message's provider for this week by orderdate
        //process.py script: replaced namespace by ::class: ['AppOrderformBundle:Message'] by [Message::class]
        $repository = $this->em->getRepository(Message::class);
        $dql =  $repository->createQueryBuilder("message");
        $dql->leftJoin("message.calllogEntryMessage","calllogEntryMessage");
        $dql->leftJoin("message.provider","provider");

        $dql->select('SUM(calllogEntryMessage.timeSpentMinutes) as totalTimeSpentMinutes');

        $dql->where("provider.id = :userId");

        $dql->andWhere("message.orderdate BETWEEN :monday AND :sunday");

        $query = $dql->getQuery();

        $query->setParameters( array(
            'userId' => $user->getId(),
            'monday' => $mondayDBStr,
            'sunday' => $sundayDBStr
        ));

        $results = $query->getResult();
        //echo "count=".count($results)."<br>";

        if( count($results) > 0 ) {
            //$result = $results[0];
            //print_r($result);
            $totalTimeSpentMinutes = $results[0]['totalTimeSpentMinutes'];

            if( $totalTimeSpentMinutes ) {

                //$totalTimeSpentMinutes = 1; //testing
                //$totalTimeSpentMinutesStr = date('H:i', mktime(0,$totalTimeSpentMinutes));

                $totalTimeSpentMinutesStr = $this->convertToHoursMins($totalTimeSpentMinutes);


                //"During the current week (MM/DD/YYYY to MM/DD/YYYY) you have spent HH:MM on call activities."
                $msg = "During the current week ($mondayStr to $sundayDBStr) you have spent $totalTimeSpentMinutesStr on call activities.";
            }
        }

        return $msg;
    }
    public function convertToHoursMins($time) {
        $totalTimeSpentMinutesStr = "";

        $zero = new \DateTime('@0');
        $offset = new \DateTime('@' . $time * 60);
        $diff = $zero->diff($offset);
        $days = $diff->format('%a');
        $hours = $diff->format('%h');
        $minutes = $diff->format('%i');
        if ($days) {
            $str = "day";
            if ($days > 1) {
                $str = $str . "s";
            }
            $totalTimeSpentMinutesStr .= $days . " " . $str . " ";
        }

        if ($hours) {
            $str = "hour";
            if ($hours > 1) {
                $str = $str . "s";
            }
            $totalTimeSpentMinutesStr .= $hours . " " . $str . " ";
        }

        if ($minutes) {
            $str = "minute";
            if ($minutes > 1) {
                $str = $str . "s";
            }
            $totalTimeSpentMinutesStr .= $minutes . " " . $str . " ";
        }

        return $totalTimeSpentMinutesStr;
    }

    //get the date of last entry for this patient
    public function getLastEntryDate( $patient ) {
        //get the sum of timeSpentMinutes from CalllogEntryMessage for Message's provider for this week by orderdate
        //process.py script: replaced namespace by ::class: ['AppOrderformBundle:Message'] by [Message::class]
        $repository = $this->em->getRepository(Message::class);
        $dql =  $repository->createQueryBuilder("message");
        $dql->select('message');
        $dql->leftJoin("message.messageStatus","messageStatus");
        $dql->leftJoin("message.patient","patient");

        $dql->where("patient.id = :patientId");
        $dql->andWhere("messageStatus.name != :deletedMessageStatus");

        $dql->leftJoin("message.calllogEntryMessage","calllogEntryMessage");
        $dql->andWhere("calllogEntryMessage IS NOT NULL");

        $dql->orderBy("message.orderdate","DESC");

        $query = $dql->getQuery();

        $query->setParameters( array(
            'patientId' => $patient->getId(),
            'deletedMessageStatus' => "Deleted"
        ));

        $messages = $query->getResult();

        $date = null;
        
        if( count($messages) > 0 ) {
            $message = $messages[0];
            if( $message && $message->getOrderdate() ) {
                //$date = $message->getOrderdate()->format("m/d/Y H:i:s");
                //convert to user timezone
                $userServiceUtil = $this->container->get('user_service_utility');
                $date = $userServiceUtil->convertToUserTimezone($message->getOrderdate());
                if( $date ) {
                    $date = $date->format("m/d/Y H:i:s");
                }
            }
        } else {
            $date = null;
        }

        return $date;
    }

    public function getLastEntryDateByAccession( $accession ) {
        //get the sum of timeSpentMinutes from CrnEntryMessage for Message's provider for this week by orderdate
        //process.py script: replaced namespace by ::class: ['AppOrderformBundle:Message'] by [Message::class]
        $repository = $this->em->getRepository(Message::class);
        $dql =  $repository->createQueryBuilder("message");
        $dql->select('message');
        $dql->leftJoin("message.messageStatus","messageStatus");
        $dql->leftJoin("message.accession","accession");

        $dql->where("accession.id = :accessionId");
        $dql->andWhere("messageStatus.name != :deletedMessageStatus");

        $dql->leftJoin("message.calllogEntryMessage","calllogEntryMessage");
        $dql->andWhere("calllogEntryMessage IS NOT NULL");

        $dql->orderBy("message.orderdate","DESC");

        $query = $dql->getQuery();

        $query->setParameters( array(
            'accessionId' => $accession->getId(),
            'deletedMessageStatus' => "Deleted"
        ));

        $messages = $query->getResult();

        $date = null;

        if( count($messages) > 0 ) {
            $message = $messages[0];
            if( $message && $message->getOrderdate() ) {
                //$date = $message->getOrderdate()->format("m/d/Y H:i:s");
                //convert to user timezone
                $userServiceUtil = $this->container->get('user_service_utility');
                $date = $userServiceUtil->convertToUserTimezone($message->getOrderdate());
                if( $date ) {
                    $date = $date->format("m/d/Y H:i:s");
                }
            }
        } else {
            $date = null;
        }

        return $date;
    }

    public function getSubmitterInfoSimpleDate($message) {
        $info = $this->getOrderSimpleDateStr($message);
        if( $message && $message->getProvider() ) {
            $info = $info . " by ".$message->getProvider()->getUsernameOptimal();
        }
        return $info;
    }

    public function getOrderSimpleDateStr( $message, $delimiter = " at " ) {
        $userServiceUtil = $this->container->get('user_service_utility');
        $user = $this->security->getUser();

        $dateStr = "Undefined Date";

        if( !$message ) {
            return $dateStr;
        }

        $date = $message->getOrderSimpleDate();

        $dateTz = $userServiceUtil->convertToUserTimezone($date,$user);

        if( $dateTz ) {
            $dateStr = $dateTz->format('m/d/Y') . $delimiter . $dateTz->format('H:i:s');
        }

        return $dateStr;
    }

    public function getPreviousAuthorsInfoStr($message) {
        $info = NULL;
        //$info = "Testaaa authors";

        $messageOid = $message->getOid();
        //process.py script: replaced namespace by ::class: ['AppOrderformBundle:Message'] by [Message::class]
        $messages = $this->em->getRepository(Message::class)->findAllMessagesByOid($messageOid);

        if( $message->getProvider() ) {
            $providerId = $message->getProvider()->getId();
        } else {
            $providerId = NULL;
        }

        //echo $message->getMessageOidVersion().": ########## count=".count($messages)." ##########";
        $infoArr = array();
        foreach($messages as $thisMessage) {

            //echo "message ID ".$thisMessage->getId().", provider=".$thisMessage->getProvider()."<br>";

            if( $thisMessage->getId() == $message->getId() ) {
                continue;
            }

            if( $thisMessage->getProvider() && $thisMessage->getProvider()->getId() == $providerId ) {
                continue;
            }

            $infoArr[$thisMessage->getProvider()->getId()] = $thisMessage->getProvider()->getUsernameOptimal();
        }

        //testing
        //echo "##########";
        //$infoArr[] = "testuser1";
        //$infoArr[] = "testuser2";

        if( count($infoArr) > 0 ) {
            $info = implode("; ",$infoArr);
        }

        if( count($infoArr) == 1 ) {
            $info = "Preceding author: ".$info;
        }
        if( count($infoArr) > 1 ) {
            $info = "Preceding authors: ".$info;
        }

        return $info;
    }

    public function getInitialMessage($message) {
        $initialMessage = null;
        $oid = $message->getOid();
        $parameters = array();

        //process.py script: replaced namespace by ::class: ['AppOrderformBundle:Message'] by [Message::class]
        $repository = $this->em->getRepository(Message::class);
        $dql = $repository->createQueryBuilder("message");

        $dql->where("message.oid = :oid");
        $parameters['oid'] = $oid;

        $dql->orderBy('message.version','ASC');

        $query = $dql->getQuery();
        $query->setParameters($parameters);
        $messages = $query->getResult();

        if( count($messages) > 0 ) {
            $initialMessage = $messages[0]; //first message
        }

        return $initialMessage;
    }

    public function getAllMessagesByOid($messageOid,$asCommaSeparetedString=true) {
        //process.py script: replaced namespace by ::class: ['AppOrderformBundle:Message'] by [Message::class]
        $messages = $this->em->getRepository(Message::class)->findAllMessagesByOid($messageOid);

        if( !$asCommaSeparetedString ) {
            return $messages;
        }

        $messageIdArr = array();
        foreach($messages as $message) {
            $messageIdArr[] = $message->getId();
        }
        if( count($messageIdArr) > 0 ) {
            //return as comma seperated string of Ids
            return implode(",",$messageIdArr);
        }

        return null;
    }

    //set all other messages status to deleted
    public function deleteAllOtherMessagesByOid( $message, $cycle, $testing=false ) {
        $userSecUtil = $this->container->get('user_security_utility');
        $user = $this->security->getUser();
        $em = $this->em;

        //message muts have an ID
        if( !$message->getId() ) {
            return false;
        }

        //process.py script: replaced namespace by ::class: ['AppOrderformBundle:MessageStatusList'] by [MessageStatusList::class]
        $messageStatusDeleted = $em->getRepository(MessageStatusList::class)->findOneByName("Deleted");
        if( !$messageStatusDeleted ) {
            throw new \Exception( "Message Status is not found by name '"."Deleted"."'" );
        }

        //process.py script: replaced namespace by ::class: ['AppOrderformBundle:Message'] by [Message::class]
        $messages = $em->getRepository(Message::class)->findByOid($message->getOid());

        $deletedMessageInfos = array();

        foreach($messages as $thisMessage) {
            //set all other messages to deleted
            if( $message->getId() != $thisMessage->getId() ) {
                if( $thisMessage->getMessageStatus()->getName()."" != "Deleted" ) {
                    $thisMessage->setMessageStatusPrior($thisMessage->getMessageStatus());
                    $thisMessage->setMessageStatus($messageStatusDeleted);
                    if( !$testing ) {
                        //$em->flush($thisMessage);
                        $em->flush();

                        //save message info
                        $patientInfoStr = $thisMessage->getPatientNameMrnInfo();
                        if( $patientInfoStr ) {
                            $patientInfoStr = "for ".$patientInfoStr;
                        }
                        $deletedMessageInfos[] = $thisMessage->getMessageOidVersion()." ".$patientInfoStr;
                    }
                }
            }
        }

        if( !$testing && count($deletedMessageInfos) > 0 ) {
            $msg = "The following Call Entry(s) are deleted by ".$cycle." action ".$message->getMessageOidVersion().":<br>".implode("<br>",$deletedMessageInfos);

            //Event Log
            $eventType = "Call Log Book Entry Deleted";
            $userSecUtil->createUserEditEvent($this->container->getParameter('calllog.sitename'), $msg, $user, $messages, null, $eventType);

            return true;
        }

        return false;
    }

    public function getDefaultMessageCategory() {
        $sitename = $this->container->getParameter('calllog.sitename');
        $userSecUtil = $this->container->get('user_security_utility');

        $messageCategory = $userSecUtil->getSiteSettingParameter('messageCategory',$sitename);
        if( !$messageCategory ) {
        //process.py script: replaced namespace by ::class: ['AppOrderformBundle:MessageCategory'] by [MessageCategory::class]
            $messageCategory = $this->em->getRepository(MessageCategory::class)->findOneByName("Pathology Call Log Entry");
        }

        return $messageCategory;
    }

    public function getDefaultMrnType() {
        //retrieving the MRN Type with the lowest “Display Order” value instead of the “default MRN Type”…
        // So Default MRN Type = MRN Type with the lowest Display order value...
        $myLimit = 1;
        //process.py script: replaced namespace by ::class: ['AppOrderformBundle:MrnType'] by [MrnType::class]
        $keytypemrns = $this->em->getRepository(MrnType::class)->findBy(
            array(),                        //All
            array('orderinlist' => 'ASC'),  //ASC - lowest firts
            $myLimit                        //Limit
        );
        if( count($keytypemrns) > 0 ) {
            $keytypemrn = $keytypemrns[0];
        }
        return $keytypemrn;

        //Below is not used
        $sitename = $this->container->getParameter('calllog.sitename');
        $userSecUtil = $this->container->get('user_security_utility');

        $keytypemrn = $userSecUtil->getSiteSettingParameter('keytypemrn',$sitename);
        if( !$keytypemrn ) {
        //process.py script: replaced namespace by ::class: ['AppOrderformBundle:MrnType'] by [MrnType::class]
            $keytypemrn = $this->em->getRepository(MrnType::class)->findOneByName("New York Hospital MRN");
        }

        if( !$keytypemrn ) {
        //process.py script: replaced namespace by ::class: ['AppOrderformBundle:MrnType'] by [MrnType::class]
            $keytypemrns = $this->em->getRepository(MrnType::class)->findAll();
            if( count($keytypemrns) > 0 ) {
                $keytypemrn = $keytypemrns[0];
            }
        }

        return $keytypemrn;
    }

//    public function getAccessionTypes() {
//        $accessionTypes = $this->em->getRepository('AppOrderformBundle:AccessionType')->findBy( array('type'=>array('default','user-added')) );
//
//        $accessionTypeArr = array();
//        foreach( $accessionTypes as $accessionType) {
//            $accessionTypeObject = array('id'=>$accessionType->getId(),'text'=>$accessionType."");
//            $accessionTypeArr[] = $accessionTypeObject;
//        }
//
//        return $accessionTypes;
//    }

    public function addDefaultLocation($encounter,$user,$system) {

        $sitename = $this->container->getParameter('calllog.sitename');
        $userSecUtil = $this->container->get('user_security_utility');

        $withdummyfields = true;
        //$locationTypePrimary = null;
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:LocationTypeList'] by [LocationTypeList::class]
        $locationType = $this->em->getRepository(LocationTypeList::class)->findOneByName("Encounter Location");
        if (!$locationType) {
            throw new \Exception('Location type is not found by name Encounter Location');
        }
        $locationName = null;   //""; //"Encounter's Location";
        $spotEntity = null;
        $removable = 0;

        $location = new Location($user);

        if( $locationType ) {
            $location->addLocationType($locationType);
        }

//        $defaultInstitution = $userSecUtil->getSiteSettingParameter('institution',$sitename);
//        if( $defaultInstitution ) {
//            $locationName = $defaultInstitution->getName() . " Location";
//        }

        $location->setName($locationName);
        $location->setStatus(1);
        $location->setRemovable($removable);

        $defaultInstitution = $userSecUtil->getSiteSettingParameter('institution',$sitename);
        if( $defaultInstitution ) {
            $location->setInstitution($defaultInstitution);
            $location->setName($defaultInstitution->getName());//." Location");
        }

        $geoLocation = new GeoLocation();
        $location->setGeoLocation($geoLocation);

        if( $withdummyfields ) {

            //zip
            //$geoLocation->setZip("10065");
            $zip = $userSecUtil->getSiteSettingParameter('zip',$sitename);
            $geoLocation->setZip($zip);

            //city
            $city = $userSecUtil->getSiteSettingParameter('city',$sitename);
            if( !$city ) {
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:CityList'] by [CityList::class]
                $city = $this->em->getRepository(CityList::class)->findOneByName('New York');
            }
            $geoLocation->setCity($city);

            //state
            $state = $userSecUtil->getSiteSettingParameter('state',$sitename);
            if( !$state ) {
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:States'] by [States::class]
                $state = $this->em->getRepository(States::class)->findOneByName('New York');
            }
            $geoLocation->setState($state);

            //county
            $county = $userSecUtil->getSiteSettingParameter('county',$sitename);
            if( !$county ) {
                //$county = "New York County";
            }
            $geoLocation->setCounty($county);

            //country
            $country = $userSecUtil->getSiteSettingParameter('country',$sitename);
            if( !$country ) {
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:Countries'] by [Countries::class]
                $country = $this->em->getRepository(Countries::class)->findOneByName('New York');
            }
            $geoLocation->setCountry($country);

        }

        $tracker = $encounter->getTracker();
        if( !$tracker) {
            $tracker = new Tracker();
            $encounter->setTracker($tracker);
        }

        if( !$spotEntity ) {
            $spotEntity = new Spot($user,$system);
        }
        $spotEntity->setCurrentLocation($location);
        $spotEntity->setCreation(new \DateTime());
        $spotEntity->setSpottedOn(new \DateTime());

        $tracker->addSpot($spotEntity);

        return $encounter;
    }

    public function getDefaultViewMode() {
        $sitename = $this->container->getParameter('calllog.sitename');
        $userSecUtil = $this->container->get('user_security_utility');

        $viewMode = $userSecUtil->getSiteSettingParameter('viewMode',$sitename);
        if( !$viewMode ) {
        //process.py script: replaced namespace by ::class: ['AppOrderformBundle:MessageCategory'] by [MessageCategory::class]
            $viewMode = $this->em->getRepository(MessageCategory::class)->findOneByName("Clear");
        }

        if( !$viewMode ) {
            $viewMode = "Empowered";
        }

        return $viewMode."";
    }

//    //TODO: save call log entry short info to setShortInfo($shortInfo)
//    //as table:{{ user_formnode_utility.getFormNodeHolderShortInfo(message,message.messageCategory,1,trclassname)|raw }}
//    public function updateMessageShortInfo($message) {
//
//        return null; //testing
//
//        $formNodeUtil = $this->container->get('user_formnode_utility');
//        //$shortInfo = $formNodeUtil->getFormNodeHolderShortInfo($message,$message->getMessageCategory(),false,"");
//        $shortInfo = $formNodeUtil->getFormNodeHolderShortInfo($message,$message->getMessageCategory(),1,"");
//        //exit("shortInfo=$shortInfo");
//        echo "shortInfo=$shortInfo <br>";
//
//        if( 0 && $shortInfo ) {
//            $calllogEntryMessage = $message->getCalllogEntryMessage();
//            if( $calllogEntryMessage ) {
//
//                //divide results by chunks of 21 rows in order to fit them in the excel row max height
//                $snapshotArrChunks = array_chunk($shortInfo, 21);
//
////                foreach ($snapshotArrChunks as $snapshotArrChunk) {
////                    //$objRichText = new \PhpOffice\PhpSpreadsheet\RichText\RichText();
////                    foreach ($snapshotArrChunk as $snapshotRow) {
////                        if (strpos((string)$snapshotRow, "[###excel_section_flag###]") === false) {
////                            //$objRichText->createText($snapshotRow."\n");
////                        } else {
////                            $snapshotRow = str_replace("[###excel_section_flag###]", "", $snapshotRow);
////                            //$objItalic = $objRichText->createTextRun($snapshotRow."\n");
////                            //$objItalic->getFont()->setItalic(true);
////                        }
////                    }
////                }
//
//                $snapshotRowArr = array();
//                foreach ($snapshotArrChunks as $snapshotArrChunk) {
//                    foreach ($snapshotArrChunk as $snapshotRow) {
//                        if (strpos((string)$snapshotRow, "[###excel_section_flag###]") === false) {
//                            //
//                        } else {
//                            $snapshotRow = str_replace("[###excel_section_flag###]", "", $snapshotRow);
//                            $snapshotRowArr[] = $snapshotRow;
//                        }
//                    }
//                }
//
//                $snapshotArrChunksText = implode("\n\r",$snapshotRowArr);
//                echo "snapshotArrChunksText=$snapshotArrChunksText <br>";
//
//                //$calllogEntryMessage->setShortInfo($snapshotArrChunksText);
//                //$this->em->flush($message);
//            }
//        }
//    }













    public function getUnprocessedTextObjects($sourceFormNodeId,$destinationFormNodeId) {
        $logger = $this->container->get('logger');
        $em = $this->em;
        //$formNodeUtil = $this->container->get('user_formnode_utility');
        //$userSecUtil = $this->container->get('user_security_utility');

//        $historySourceFormNode = $this->getSourceFormNodeByName("History/Findings");
//        if( !$historySourceFormNode ) {
//            exit("Error: no source form node History/Findings");
//        }
//        $impressionSourceFormNode = $this->getSourceFormNodeByName("Impression/Outcome");
//        if( !$impressionSourceFormNode ) {
//            exit("Error: no source form node Impression/Outcome");
//        }
//
//        $historyDestinationFormNode = $this->getDestinationFormNodeByName("History/Findings HTML");
//        if( !$historyDestinationFormNode ) {
//            exit("Error: no destination form node History/Findings HTML");
//        }
//        $historyDestinationFormNodeId = $historyDestinationFormNode->getId();
//
//        $impressionDestinationFormNode = $this->getDestinationFormNodeByName("Impression/Outcome HTML");
//        if( !$impressionDestinationFormNode ) {
//            exit("Error: no destination form node Impression/Outcome HTML");
//        }
//        $impressionDestinationFormNodeId = $impressionDestinationFormNode->getId();

        //1) subquery to get a fellowship application object with logger.entityId and fellowshipSubspecialty in the $fellowshipTypes array
        $subquery = $em->createQueryBuilder()
            ->select('COUNT(html.id)')
            //->select('html.id')
            //->from('AppUserdirectoryBundle:ObjectTypeText', 'html')
            ->from(ObjectTypeText::class, 'html')
            ->leftJoin('html.formNode','formNodeHtml')
            ->where("formNodeHtml.id = " . $destinationFormNodeId)
            //->andWhere("html.value IS NOT NULL")
            //->andWhere("html.entityId IS NOT NULL")
            ->andWhere("html.entityName = 'Message'")
            ->andWhere("html.entityId = list.entityId")
            ->getDQL();
        $subquery = '('.$subquery.')';

        //query
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:ObjectTypeText'] by [ObjectTypeText::class]
        $repository = $em->getRepository(ObjectTypeText::class);
        $dql = $repository->createQueryBuilder("list");;
        $dql->select('list');
        $dql->leftJoin("list.formNode", "formNode");
        $dql->where("formNode.id = " . $sourceFormNodeId);
        $dql->andWhere("list.entityName = 'Message'");
        //$dql->andWhere("list.value IS NOT NULL");
        $dql->andWhere("list.entityId IS NOT NULL");
        $dql->andWhere($subquery."=0");
        //$dql->andWhere("list.entityId = html.entityId");

        $query = $dql->getQuery(); //$query = $em->createQuery($dql);

        $unprocessedSourceTextObjects = $query->getResult();
        //echo "\n\r getUnprocessedTextObjects: UnprocessedSourceTextObjects count=".count($unprocessedSourceTextObjects)."<br>";
        //$logger->notice("getUnprocessedTextObjects: UnprocessedSourceTextObjects count=".count($unprocessedSourceTextObjects));

        return $unprocessedSourceTextObjects;
    }
    //NOT USED
    public function getLoopUnprocessedTextObjects() {
        $logger = $this->container->get('logger');
        $em = $this->em;

        $historySourceFormNode = $this->getSourceFormNodeByName("History/Findings");
        if( !$historySourceFormNode ) {
            exit("Error: no source form node History/Findings");
        }
        $impressionSourceFormNode = $this->getSourceFormNodeByName("Impression/Outcome");
        if( !$impressionSourceFormNode ) {
            exit("Error: no source form node Impression/Outcome");
        }

        $historyDestinationFormNode = $this->getDestinationFormNodeByName("History/Findings HTML");
        if( !$historyDestinationFormNode ) {
            exit("Error: no destination form node History/Findings HTML");
        }
        $historyDestinationFormNodeId = $historyDestinationFormNode->getId();

        $impressionDestinationFormNode = $this->getDestinationFormNodeByName("Impression/Outcome HTML");
        if( !$impressionDestinationFormNode ) {
            exit("Error: no destination form node Impression/Outcome HTML");
        }
        $impressionDestinationFormNodeId = $impressionDestinationFormNode->getId();

        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:ObjectTypeText'] by [ObjectTypeText::class]
        $repository = $em->getRepository(ObjectTypeText::class);
        $dql = $repository->createQueryBuilder("list");;
        $dql->select('list');
        $dql->leftJoin("list.formNode", "formNode");
        $dql->where("formNode.id = " . $historySourceFormNode->getId());
        $dql->andWhere("list.entityId IS NOT NULL");

        $query = $dql->getQuery(); //$query = $em->createQuery($dql);

        $sourceTextObjects = $query->getResult();
        //echo "\n\rSearching text objects by formnode ID ".$historySourceFormNode->getId()." and ".$impressionSourceFormNode->getId()."<br>";
        echo "\n\r ##### Loop test: History total SourceTextObjects count=".count($sourceTextObjects)."#####<br>";
        //$logger->notice("SourceTextObjects count=".count($sourceTextObjects));

        $unprocessedSourceTextObjects = array();
        foreach($sourceTextObjects as $textObject) {
            $formValue = $textObject->getValue();
            $formNode = $textObject->getFormNode();
            $entityNamespace = $textObject->getEntityNamespace();
            $entityName = $textObject->getEntityName();
            $entityId = $textObject->getEntityId();
            $existingHtmlText = $this->findExistingTextHtmlByName($formNode,$formValue,$historyDestinationFormNodeId,null,$entityNamespace,$entityName,$entityId);
            if( !$existingHtmlText ) {
                //echo $totalCounter.": Skipped (".$formNode->getName()."): Text HTML does not exist value=[$formValue], existingHtml=[$existingHtmlText]<br>";
                $unprocessedSourceTextObjects[] = $textObject;
            }
        }
        echo "\n\r ##### EOF Loop test: Loop unprocessedSourceTextObjects count=".count($unprocessedSourceTextObjects)."#####<br>";
        //$logger->notice("Loop unprocessedSourceTextObjects count=".count($unprocessedSourceTextObjects));
        //exit('EOF counting');
    }

    //127.0.0.1/order/call-log-book/update-text-html
    //php app/console cron:util-command --env=prod
    //Copy text to html text for "History/Findings" and "Impression/Outcome" fields
    public function updateTextHtml() {
        set_time_limit(900); //600 seconds => 10 mins; 900=15min; 1800=30 min

        $newline = "\n\r";
        $logger = $this->container->get('logger');

        $historySourceFormNode = $this->getSourceFormNodeByName("History/Findings");
        if( !$historySourceFormNode ) {
            exit("Error: no source form node History/Findings");
        }
        $historyDestinationFormNode = $this->getDestinationFormNodeByName("History/Findings HTML");
        if( !$historyDestinationFormNode ) {
            exit("Error: no destination form node History/Findings HTML");
        }
        $impressionSourceFormNode = $this->getSourceFormNodeByName("Impression/Outcome");
        if( !$impressionSourceFormNode ) {
            exit("Error: no source form node Impression/Outcome");
        }
        $impressionDestinationFormNode = $this->getDestinationFormNodeByName("Impression/Outcome HTML");
        if( !$impressionDestinationFormNode ) {
            exit("Error: no destination form node Impression/Outcome HTML");
        }


        //testing
        //History
        $unprocessedHistorySourceTextObjects = $this->getUnprocessedTextObjects($historySourceFormNode->getId(),$historyDestinationFormNode->getId());
        echo $newline."### History unprocessedSourceTextObjects=".count($unprocessedHistorySourceTextObjects)."<br>";
        $logger->notice("History unprocessedSourceTextObjects=".count($unprocessedHistorySourceTextObjects));
        //foreach($unprocessedHistorySourceTextObjects as $unprocessedHistorySourceTextObject){
        //    echo "unprocessedHistorySourceTextObject ID=".$unprocessedHistorySourceTextObject->getId()."<br>";
        //}
        $processedHistoryCounter = $this->updateUnprocessedSourceTextHtml($unprocessedHistorySourceTextObjects);
        echo $newline."Processed History $processedHistoryCounter text objects"."<br>";
        $logger->notice("Processed History $processedHistoryCounter text objects");

        //Impression
        $unprocessedImpressionSourceTextObjects = $this->getUnprocessedTextObjects($impressionSourceFormNode->getId(),$impressionDestinationFormNode->getId());
        echo $newline."### Impression unprocessedSourceTextObjects=".count($unprocessedImpressionSourceTextObjects)."<br>";
        $logger->notice("Impression unprocessedSourceTextObjects=".count($unprocessedImpressionSourceTextObjects));
        $processedImpressionCounter = $this->updateUnprocessedSourceTextHtml($unprocessedImpressionSourceTextObjects);
        echo $newline."Processed Impression $processedImpressionCounter text objects"."<br>";
        $logger->notice("Processed Impression $processedImpressionCounter text objects");

        //$this->getLoopUnprocessedTextObjects();

        $logger->notice('### EOF update TextHtml');
        exit($newline.'### EOF update TextHtml');
    }
    public function updateUnprocessedSourceTextHtml($sourceTextObjects)
    {
        return 0; //run only once after swap simple and rich text fields

        $logger = $this->container->get('logger');
        $em = $this->em;
        $formNodeUtil = $this->container->get('user_formnode_utility');
        $userSecUtil = $this->container->get('user_security_utility');

        $historySourceFormNode = $this->getSourceFormNodeByName("History/Findings");
        if( !$historySourceFormNode ) {
            exit("Error: no source form node History/Findings");
        }
        $historyDestinationFormNode = $this->getDestinationFormNodeByName("History/Findings HTML");
        if( !$historyDestinationFormNode ) {
            exit("Error: no destination form node History/Findings HTML");
        }
        $impressionSourceFormNode = $this->getSourceFormNodeByName("Impression/Outcome");
        if( !$impressionSourceFormNode ) {
            exit("Error: no source form node Impression/Outcome");
        }
        $impressionDestinationFormNode = $this->getDestinationFormNodeByName("Impression/Outcome HTML");
        if( !$impressionDestinationFormNode ) {
            exit("Error: no destination form node Impression/Outcome HTML");
        }

        $totalCounter = 0;
        $processedCounter = 0;

        $batchSize = 20;
        $i = 0;

        foreach($sourceTextObjects as $textObject) {
            //create a new ObjectTypeText Html
            //echo "Copy this textObject: ".$textObject."<br>";

            $totalCounter++;

            $creator = $textObject->getCreator();
            $createDate = $textObject->getCreatedate();

            $updatedby = $textObject->getUpdatedby();
            $updatedon = $textObject->getUpdatedon();

            $name = $textObject->getName();
            $abbreviation = $textObject->getAbbreviation();
            $shortName = $textObject->getShortname();
            $description = $textObject->getDescription();
            $type = $textObject->getType();

            $updateAuthorRoles = $textObject->getUpdateAuthorRoles();
            $fulltitle = $textObject->getFulltitle();
            $linkToListId = $textObject->getLinkToListId();

            $version = $textObject->getVersion();

            $formValue = $textObject->getValue();
            $formNode = $textObject->getFormNode();

            $entityNamespace = $textObject->getEntityNamespace();
            $entityName = $textObject->getEntityName();
            $entityId = $textObject->getEntityId();

            $arraySectionId = $textObject->getArraySectionId();
            $arraySectionIndex = $textObject->getArraySectionIndex();

            //$formNode,$historyDestinationFormNodeId,$impressionDestinationFormNodeId,$entityName,$entityId
            $existingHtmlText = $this->findExistingTextHtmlByDestination($formNode,$historyDestinationFormNode->getId(),$impressionDestinationFormNode->getId(),$entityName,$entityId);
            if( $existingHtmlText ) {
                echo $totalCounter.": Skipped (".$formNode->getName()."): Text HTML already exists value=[$formValue], existingHtml=[$existingHtmlText]<br>";
                continue;
            }

            //Create new text object
            $textHtmlObject = new ObjectTypeText();

            $count = null;
            $userSecUtil->setDefaultList($textHtmlObject,$count,$creator,$name);

            //Set form node according to the source
            if( $formNode->getName() == 'History/Findings' ) {
                if( $historyDestinationFormNode ) {
                    $textHtmlObject->setFormNode($historyDestinationFormNode);
                    $msgLog = $processedCounter.": ".$entityId."(".$entityName."): Copy History/Findings html text [$formValue] to formnode [$historyDestinationFormNode]";
                } else {
                    echo $totalCounter.": Skip historyDestinationFormNodeByName not found <br>";
                    continue;
                }
            }
            if( $formNode->getName() == 'Impression/Outcome' ) {
                if( $impressionDestinationFormNode ) {
                    $textHtmlObject->setFormNode($impressionDestinationFormNode);
                    $msgLog = $processedCounter.": ".$entityId."(".$entityName."): Copy Impression/Outcome html text [$formValue] to formnode [$impressionDestinationFormNode]";
                } else {
                    echo $totalCounter.": Skip impressionDestinationFormNodeByName not found <br>";
                    continue;
                }
            }

            //Set list parameters
            $textHtmlObject->setCreatedate($createDate);
            $textHtmlObject->setUpdatedby($updatedby);
            $textHtmlObject->setUpdatedon($updatedon);
            $textHtmlObject->setAbbreviation($abbreviation);
            $textHtmlObject->setShortname($shortName);
            $textHtmlObject->setDescription($description);
            $textHtmlObject->setType($type);
            $textHtmlObject->setFulltitle($fulltitle);

            $textHtmlObject->setFulltitle($fulltitle);
            $textHtmlObject->setLinkToListId($linkToListId);
            $textHtmlObject->setVersion($version);
            $textHtmlObject->setFulltitle($fulltitle);
            $textHtmlObject->setFulltitle($fulltitle);
            $textHtmlObject->setUpdateAuthorRoles($updateAuthorRoles);

            //Set ObjectTypeReceivingBase parameters
            $textHtmlObject->setArraySectionId($arraySectionId);
            $textHtmlObject->setArraySectionIndex($arraySectionIndex);

            //3) set message by entityName to the created list
            //$textHtmlObject->setObject($holderEntity);
            $textHtmlObject->setEntityNamespace($entityNamespace);
            $textHtmlObject->setEntityName($entityName);
            $textHtmlObject->setEntityId($entityId);

            //$formValue = "<p>test <b>test </b><u>test</u><br></p>"; //testing

            //last step assign value. This setValue will trigger to make a copy to the plain text in the ObjectTypeText object if the formNode is set
            $textHtmlObject->setValue($formValue);

            if( $formValue ) {
                $secondaryValue = $textHtmlObject->getSecondaryValue();
                //echo "formValue=$formValue; secondaryValue=$secondaryValue <br>";
                if (!$secondaryValue && $formValue) {
                    $secondaryValue = $textHtmlObject->convertHtmlToPlainText($formValue);
                    //echo "setSecondaryValue: secondaryValue=$secondaryValue <br>";
                    $textHtmlObject->setSecondaryValue($secondaryValue);
                }
                //else {
                //    echo "Skip setSecondaryValue<br>";
                //}
                //exit('111');
            }

            //echo "textHtmlObject: Namespace=" . $textHtmlObject->getEntityNamespace() . ", Name=" . $textHtmlObject->getEntityName() . ", Value=" . $textHtmlObject->getValue() . "<br>";
            $processedCounter++;

            $testing = true;
            $testing = false;
            if( !$testing ) {

                //$updateCache = false;
                $updateCache = true;
                if( $updateCache ) {
                    $message = null;
                    if ($entityId) {
        //process.py script: replaced namespace by ::class: ['AppOrderformBundle:Message'] by [Message::class]
                        $message = $em->getRepository(Message::class)->find($entityId);
                        if (!$message) {
                            throw new \Exception("Message is not found by id " . $entityId);
                        }
                        //Save fields as cache in the field $formnodesCache ($holderEntity->setFormnodesCache($text))
                        $testing = false;
                        $formNodeUtil->updateFieldsCache($message, $testing);
                    }
                }

                $em->persist($textHtmlObject);
                //$em->flush();
                //$em->clear();

                if (($i % $batchSize) === 0) {
                    $em->flush(); // Executes all updates.
                    //$em->clear(); // Detaches all objects from Doctrine!
                }
                ++$i;

                //EventLog
                //$eventType = "Call Log Book Entry Updated";
                //$userSecUtil->createUserEditEvent($this->container->getParameter('calllog.sitename'), $msgLog, $user, $message, $request, $eventType);
            }//$testing

            //echo $msgLog . "<br>";
            $logger->notice($msgLog);

            if( $processedCounter >= 300 ) {
                if( !$testing ) {
                    $em->flush();
                    $em->clear();
                }
                $logger->notice("Break processing $totalCounter text objects after copying $processedCounter text objects");
                exit("\n\rBreak processing $totalCounter text objects after copying $processedCounter text objects");
            }

        }//foreach

        if( !$testing ) {
            $em->flush();
            $em->clear();
        }

        //$logger->notice("Processed $processedCounter text objects");
        //exit("\n\rProcessed $processedCounter text objects");
        return $processedCounter;
    }

    //127.0.0.1/order/call-log-book/update-text-html
    //php app/console cron:util-command --env=prod
    //Copy text to html text for "History/Findings" and "Impression/Outcome" fields
    public function updateLoopTextHtml_OLD()
    {
//        if (false === $this->security->isGranted('ROLE_PLATFORM_DEPUTY_ADMIN')) {
//            return $this->redirect($this->generateUrl('employees-nopermission'));
//        }

        $newline = "\n\r";

        set_time_limit(900); //600 seconds => 10 mins; 900=15min; 1800=30 min

        $logger = $this->container->get('logger');
        $em = $this->em;
        $formNodeUtil = $this->container->get('user_formnode_utility');
        $userSecUtil = $this->container->get('user_security_utility');
        //$user = $this->security->getUser();

        //$objectTypeText = $formNodeUtil->getObjectTypeByName('Form Field - Free Text, HTML');

        $historySourceFormNode = $this->getSourceFormNodeByName("History/Findings");
        if( !$historySourceFormNode ) {
            exit("Error: no source form node History/Findings");
        }

        $historyDestinationFormNode = $this->getDestinationFormNodeByName("History/Findings HTML");
        if( !$historyDestinationFormNode ) {
            exit("Error: no destination form node History/Findings HTML");
        }
        $historyDestinationFormNodeId = $historyDestinationFormNode->getId();

        $impressionSourceFormNode = $this->getSourceFormNodeByName("Impression/Outcome");
        if( !$impressionSourceFormNode ) {
            exit("Error: no source form node Impression/Outcome");
        }

        $impressionDestinationFormNode = $this->getDestinationFormNodeByName("Impression/Outcome HTML");
        if( !$impressionDestinationFormNode ) {
            exit("Error: no destination form node Impression/Outcome HTML");
        }
        $impressionDestinationFormNodeId = $impressionDestinationFormNode->getId();


        //testing
        //History
        $unprocessedHistorySourceTextObjects = $this->getUnprocessedTextObjects($historySourceFormNode->getId(),$historyDestinationFormNode->getId());
        echo $newline."History unprocessedSourceTextObjects=".count($unprocessedHistorySourceTextObjects)."<br>";

        //Impression
        $unprocessedImpressionSourceTextObjects = $this->getUnprocessedTextObjects($impressionSourceFormNode->getId(),$impressionDestinationFormNode->getId());
        echo $newline."Impression unprocessedSourceTextObjects=".count($unprocessedImpressionSourceTextObjects)."<br>";

        //$this->getLoopUnprocessedTextObjects();
        exit($newline.'EOF Loop testing counting');


        //$formNodeHtml = $em->getRepository('AppUserdirectoryBundle:ObjectTypeText')->findAll();

        //$sourceTextObjects = $em->getRepository('AppUserdirectoryBundle:FormNode')->findOneByName("History/Findings");
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:ObjectTypeText'] by [ObjectTypeText::class]
        $repository = $em->getRepository(ObjectTypeText::class);
        $dql = $repository->createQueryBuilder("list");;
        $dql->select('list');
        $dql->leftJoin("list.formNode", "formNode");
        //$dql->leftJoin("list.objectType", "objectType");
        //$dql->leftJoin("list.parent", "parent");
        //$dql->leftJoin("parent.parent", "grandParent");
        //$dql->where("list.level = 4 AND objectType.id = ".$objectTypeText->getId()." AND parent.level = 3 AND grandParent.name = 'Pathology Call Log Entry'");
        //$dql->andWhere("list.name = 'History/Findings' OR list.name = 'Impression/Outcome'");
        $dql->where("formNode.id = " . $historySourceFormNode->getId() . " OR formNode.id = " . $impressionSourceFormNode->getId());
        $dql->andWhere("list.entityId IS NOT NULL");


//        //////////////
//        $dql->leftJoin("list.formNode", "formNode");
//
//        if( $formNode->getName() == 'History/Findings' ) {
//            $dql->where("formNode.id = " . $historyDestinationFormNodeId);
//        }
//        if( $formNode->getName() == 'Impression/Outcome' ) {
//            $dql->where("formNode.id = " . $impressionDestinationFormNodeId);
//        }
//        //$dql->andWhere("list.value = '$formValue'");
//        $dql->andWhere("list.value IS NOT NULL");
//        $dql->andWhere("list.entityName = '$entityName' AND list.entityId = '$entityId'");
//        //////////////


        //$dql->orderBy('list.arraySectionIndex','DESC');
        //$dql->addOrderBy('list.orderinlist', 'ASC');
        $query = $dql->getQuery(); //$query = $em->createQuery($dql);

        $sourceTextObjects = $query->getResult();
        echo "\n\rSearching text objects by formnode ID ".$historySourceFormNode->getId()." and ".$impressionSourceFormNode->getId()."<br>";
        echo "\n\rSourceTextObjects count=".count($sourceTextObjects)."<br>";
        $logger->notice("SourceTextObjects count=".count($sourceTextObjects));
        //exit("EOF testing");

        //$iterableResult = $query->iterate();
        // echo "iterableResult count=".count($iterableResult)."<br>";

        $unprocessedSourceTextObjects = array();
        foreach($sourceTextObjects as $textObject) {
            $formValue = $textObject->getValue();
            $formNode = $textObject->getFormNode();
            $entityNamespace = $textObject->getEntityNamespace();
            $entityName = $textObject->getEntityName();
            $entityId = $textObject->getEntityId();
            //NOT USED
            $existingHtmlText = $this->findExistingTextHtmlByName($formNode,$formValue,$historyDestinationFormNodeId,$impressionDestinationFormNodeId,$entityNamespace,$entityName,$entityId);
            if( !$existingHtmlText ) {
                //echo $totalCounter.": Skipped (".$formNode->getName()."): Text HTML does not exist value=[$formValue], existingHtml=[$existingHtmlText]<br>";
                $unprocessedSourceTextObjects[] = $textObject;
            }
        }
        echo "\n\runprocessedSourceTextObjects count=".count($unprocessedSourceTextObjects)."<br>";
        $logger->notice("unprocessedSourceTextObjects count=".count($unprocessedSourceTextObjects));
        exit('EOF counting');



        $totalCounter = 0;
        $processedCounter = 0;

        $batchSize = 20;
        $i = 0;

        foreach($sourceTextObjects as $textObject) {
            //foreach($iterableResult as $row) {
            //$textObject = $row[0];


            //check if parent is section (level = 3)
//            if( $textObject->getParent() && $textObject->getParent()->getLevel() == 3 ) {
//                //ok
//            } else {
//                echo "Skip this textObject: ".$textObject."<br>";
//                continue;
//            }

            //create a new ObjectTypeText Html
            //echo "Copy this textObject: ".$textObject."<br>";

            $totalCounter++;

            $creator = $textObject->getCreator();
            $createDate = $textObject->getCreatedate();

            $updatedby = $textObject->getUpdatedby();
            $updatedon = $textObject->getUpdatedon();

            $name = $textObject->getName();
            $abbreviation = $textObject->getAbbreviation();
            $shortName = $textObject->getShortname();
            $description = $textObject->getDescription();
            $type = $textObject->getType();

            $updateAuthorRoles = $textObject->getUpdateAuthorRoles();
            $fulltitle = $textObject->getFulltitle();
            $linkToListId = $textObject->getLinkToListId();

            $version = $textObject->getVersion();

            $formValue = $textObject->getValue();
            $formNode = $textObject->getFormNode();

            $entityNamespace = $textObject->getEntityNamespace();
            $entityName = $textObject->getEntityName();
            $entityId = $textObject->getEntityId();

            $arraySectionId = $textObject->getArraySectionId();
            $arraySectionIndex = $textObject->getArraySectionIndex();
            //NOT USED
            $existingHtmlText = $this->findExistingTextHtmlByName($formNode,$formValue,$historyDestinationFormNodeId,$impressionDestinationFormNodeId,$entityNamespace,$entityName,$entityId);
            if( $existingHtmlText ) {
                //echo $totalCounter.": Skipped (".$formNode->getName()."): Text HTML already exists value=[$formValue], existingHtml=[$existingHtmlText]<br>";
                continue;
            }

            //Create new text object
            $textHtmlObject = new ObjectTypeText();

            $count = null;
            $userSecUtil->setDefaultList($textHtmlObject,$count,$creator,$name);

            //Set form node according to the source
            if( $formNode->getName() == 'History/Findings' ) {
                if( $historyDestinationFormNode ) {
                    $textHtmlObject->setFormNode($historyDestinationFormNode);
                    $msgLog = $processedCounter.": ".$entityId."(".$entityName."): Copy History/Findings html text [$formValue] to formnode [$historyDestinationFormNode]";
                } else {
                    echo $totalCounter.": Skip historyDestinationFormNodeByName not found <br>";
                    continue;
                }
            }
            if( $formNode->getName() == 'Impression/Outcome' ) {
                if( $impressionDestinationFormNode ) {
                    $textHtmlObject->setFormNode($impressionDestinationFormNode);
                    $msgLog = $processedCounter.": ".$entityId."(".$entityName."): Copy Impression/Outcome html text [$formValue] to formnode [$impressionDestinationFormNode]";
                } else {
                    echo $totalCounter.": Skip impressionDestinationFormNodeByName not found <br>";
                    continue;
                }
            }

            //Set list parameters
            $textHtmlObject->setCreatedate($createDate);
            $textHtmlObject->setUpdatedby($updatedby);
            $textHtmlObject->setUpdatedon($updatedon);
            $textHtmlObject->setAbbreviation($abbreviation);
            $textHtmlObject->setShortname($shortName);
            $textHtmlObject->setDescription($description);
            $textHtmlObject->setType($type);
            $textHtmlObject->setFulltitle($fulltitle);

            $textHtmlObject->setFulltitle($fulltitle);
            $textHtmlObject->setLinkToListId($linkToListId);
            $textHtmlObject->setVersion($version);
            $textHtmlObject->setFulltitle($fulltitle);
            $textHtmlObject->setFulltitle($fulltitle);
            $textHtmlObject->setUpdateAuthorRoles($updateAuthorRoles);

            //Set ObjectTypeReceivingBase parameters
            $textHtmlObject->setArraySectionId($arraySectionId);
            $textHtmlObject->setArraySectionIndex($arraySectionIndex);

            //3) set message by entityName to the created list
            //$textHtmlObject->setObject($holderEntity);
            $textHtmlObject->setEntityNamespace($entityNamespace);
            $textHtmlObject->setEntityName($entityName);
            $textHtmlObject->setEntityId($entityId);

            //$formValue = "<p>test <b>test </b><u>test</u><br></p>"; //testing

            //last step assign value. This setValue will trigger to make a copy to the plain text in the ObjectTypeText object if the formNode is set
            $textHtmlObject->setValue($formValue);

            if( $formValue ) {
                $secondaryValue = $textHtmlObject->getSecondaryValue();
                //echo "formValue=$formValue; secondaryValue=$secondaryValue <br>";
                if (!$secondaryValue && $formValue) {
                    $secondaryValue = $textHtmlObject->convertHtmlToPlainText($formValue);
                    //echo "setSecondaryValue: secondaryValue=$secondaryValue <br>";
                    $textHtmlObject->setSecondaryValue($secondaryValue);
                }
                //else {
                //    echo "Skip setSecondaryValue<br>";
                //}
                //exit('111');
            }

            //echo "textHtmlObject: Namespace=" . $textHtmlObject->getEntityNamespace() . ", Name=" . $textHtmlObject->getEntityName() . ", Value=" . $textHtmlObject->getValue() . "<br>";
            $processedCounter++;

            //$testing = true;
            $testing = false;
            if( !$testing ) {

                //$updateCache = false;
                $updateCache = true;
                if( $updateCache ) {
                    $message = null;
                    if ($entityId) {
        //process.py script: replaced namespace by ::class: ['AppOrderformBundle:Message'] by [Message::class]
                        $message = $em->getRepository(Message::class)->find($entityId);
                        if (!$message) {
                            throw new \Exception("Message is not found by id " . $entityId);
                        }
                        //Save fields as cache in the field $formnodesCache ($holderEntity->setFormnodesCache($text))
                        $testing = false;
                        $formNodeUtil->updateFieldsCache($message, $testing);
                    }
                }

                $em->persist($textHtmlObject);
                //$em->flush();
                //$em->clear();

                if (($i % $batchSize) === 0) {
                    $em->flush(); // Executes all updates.
                    //$em->clear(); // Detaches all objects from Doctrine!
                }
                ++$i;

                //EventLog
                //$eventType = "Call Log Book Entry Updated";
                //$userSecUtil->createUserEditEvent($this->container->getParameter('calllog.sitename'), $msgLog, $user, $message, $request, $eventType);
            }

            //echo $msgLog . "<br>";
            $logger->notice($msgLog);

            if( $processedCounter > 300 ) {
                $em->flush(); //testing
                $em->clear();
                $logger->notice("Break processing $totalCounter text objects after copying $processedCounter text objects");
                exit("\n\rBreak processing $totalCounter text objects after copying $processedCounter text objects");
            }

        }//foreach

        $em->flush();
        $em->clear();

        $logger->notice("Processed $processedCounter text objects");
        exit("\n\rProcessed $processedCounter text objects");
    }
    public function findExistingTextHtmlByDestination($formNode,$historyDestinationFormNodeId,$impressionDestinationFormNodeId,$entityName,$entityId) {

        //return false; //testing

        $em = $this->em;

        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:ObjectTypeText'] by [ObjectTypeText::class]
        $repository = $em->getRepository(ObjectTypeText::class);
        $dql = $repository->createQueryBuilder("list");;
        $dql->select('list');
        $dql->leftJoin("list.formNode", "formNode");

        if( $formNode->getName() == 'History/Findings' && $historyDestinationFormNodeId ) {
            $dql->where("formNode.id = " . $historyDestinationFormNodeId);
        }
        if( $formNode->getName() == 'Impression/Outcome' && $impressionDestinationFormNodeId ) {
            $dql->where("formNode.id = " . $impressionDestinationFormNodeId);
        }
        //$dql->where("formNode.id = " . $destinationFormNodeId);

        //$dql->andWhere("list.value = '$formValue'");
        //$dql->andWhere("list.value IS NOT NULL");
        $dql->andWhere("list.entityId IS NOT NULL");

        //$dql->andWhere("list.entityNamespace = '$entityNamespace' AND list.entityName = '$entityName' AND list.entityId = '$entityId'");
        $dql->andWhere("list.entityName = '$entityName' AND list.entityId = '$entityId'");

        $query = $dql->getQuery(); //$query = $em->createQuery($dql);
        $destinationTextObjects = $query->getResult();
        //echo "Existing destinationTextObjects count=".count($destinationTextObjects)."<br>";

        //exit("eof");

        if( count($destinationTextObjects) > 0 ) {
            //return $destinationTextObjects[0]->getValue();
            return true;
        }

        return false;
    }
    public function findExistingTextHtmlByName($formNode,$formValue,$historyDestinationFormNodeId,$impressionDestinationFormNodeId,$entityNamespace,$entityName,$entityId) {

        //return false; //testing

        $em = $this->em;

        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:ObjectTypeText'] by [ObjectTypeText::class]
        $repository = $em->getRepository(ObjectTypeText::class);
        $dql = $repository->createQueryBuilder("list");;
        $dql->select('list');
        $dql->leftJoin("list.formNode", "formNode");

        if( $formNode->getName() == 'History/Findings' && $historyDestinationFormNodeId ) {
            $dql->where("formNode.id = " . $historyDestinationFormNodeId);
        }
        if( $formNode->getName() == 'Impression/Outcome' && $impressionDestinationFormNodeId ) {
            $dql->where("formNode.id = " . $impressionDestinationFormNodeId);
        }

        //$dql->andWhere("list.value = '$formValue'");
        //$dql->andWhere("list.value IS NOT NULL");
        $dql->andWhere("list.entityId IS NOT NULL");

        //$dql->andWhere("list.entityNamespace = '$entityNamespace' AND list.entityName = '$entityName' AND list.entityId = '$entityId'");
        $dql->andWhere("list.entityName = '$entityName' AND list.entityId = '$entityId'");

        $query = $dql->getQuery(); //$query = $em->createQuery($dql);
        $destinationTextObjects = $query->getResult();
        //echo "Existing destinationTextObjects count=".count($destinationTextObjects)."<br>";

        //exit("eof");

        if( count($destinationTextObjects) > 0 ) {
            //return $destinationTextObjects[0]->getValue();
            return true;
        }

        return false;
    }
    //$name - "History/Findings", "Impression/Outcome"
    public function getSourceFormNodeByName($name) {
        $em = $this->em;
        $formNodeUtil = $this->container->get('user_formnode_utility');

        $objectTypeText = $formNodeUtil->getObjectTypeByName('Form Field - Free Text');

        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:FormNode'] by [FormNode::class]
        $repository = $em->getRepository(FormNode::class);
        $dql = $repository->createQueryBuilder("list");;
        $dql->select('list');
        $dql->leftJoin("list.objectType", "objectType");
        $dql->leftJoin("list.parent", "parent");
        $dql->leftJoin("parent.parent", "grandParent");
        $dql->where("list.level = 4 AND objectType.id = ".$objectTypeText->getId()." AND parent.level = 3 AND grandParent.name = 'Pathology Call Log Entry'");
        $dql->andWhere("list.name = '".$name."'");
        $query = $dql->getQuery(); //$query = $em->createQuery($dql);
        $sourceTextObjects = $query->getResult();
        // "sourceTextObjects count=".count($sourceTextObjects)."<br>";

        if( count($sourceTextObjects) == 1 ) {
            return $sourceTextObjects[0];
        }

        return NULL;
    }
    //$name - "History/Findings HTML", "Impression/Outcome HTML"
    public function getDestinationFormNodeByName($name) {
        $em = $this->em;
        $formNodeUtil = $this->container->get('user_formnode_utility');

        $objectTypeText = $formNodeUtil->getObjectTypeByName('Form Field - Free Text, HTML');

        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:FormNode'] by [FormNode::class]
        $repository = $em->getRepository(FormNode::class);
        $dql = $repository->createQueryBuilder("list");;
        $dql->select('list');
        $dql->leftJoin("list.objectType", "objectType");
        $dql->leftJoin("list.parent", "parent");
        $dql->leftJoin("parent.parent", "grandParent");
        //$dql->where('list.level = 4 AND objectType.id = '.$objectTypeText->getId().' AND parent.level = 3');
        $dql->where("list.level = 4 AND objectType.id = ".$objectTypeText->getId()." AND parent.level = 3 AND grandParent.name = 'Pathology Call Log Entry'");
        $dql->andWhere("list.name = '".$name."'");
        $query = $dql->getQuery(); //$query = $em->createQuery($dql);
        $destinationTextObjects = $query->getResult();

        if( count($destinationTextObjects) == 1 ) {
            return $destinationTextObjects[0];
        }

        return NULL;
    }

    public function getPreviousEncounterByMessage($message) {
        $previousEncounters = array();

        if(0) {
            //////////// get new encounter //////////////
            $securityUtil = $this->container->get('user_security_utility');
            $userSecUtil = $this->container->get('user_security_utility');
            $user = $this->security->getUser();
            $system = $securityUtil->getDefaultSourceSystem($this->container->getParameter('calllog.sitename'));

            $institution = $userSecUtil->getCurrentUserInstitution($user);
            $encounter2 = new Encounter(true, 'valid', $user, $system);
            $encounter2->setVersion(1);
            $encounter2->setInstitution($institution);
            //ReferringProvider
            $encounterReferringProvider = new EncounterReferringProvider('valid', $user, $system);
            $encounter2->addReferringProvider($encounterReferringProvider);
            //AttendingPhysician
            $encounterAttendingPhysician = new EncounterAttendingPhysician('valid', $user, $system);
            $encounter2->addAttendingPhysician($encounterAttendingPhysician);

            $encounter2->setProvider($user);

            //set encounter generated id
            $key = $encounter2->obtainAllKeyfield()->first();
        //process.py script: replaced namespace by ::class: ['AppOrderformBundle:Encounter'] by [Encounter::class]
            $encounter2 = $this->em->getRepository(Encounter::class)->setEncounterKey($key, $encounter2, $user);
            $this->em->persist($encounter2);
            $previousEncounters[] = $encounter2;
            //$previousEncounters[$encounter2->obtainEncounterNumberOnlyAndDate()] = $encounter2->getId();
        }

        //Autogenerated new ID, selected by default
        //$newkeytypeEntity = $this->em->getRepository('AppOrderformBundle:EncounterType')->findOneByName("Auto-generated Encounter Number");
        //$nextKey = $this->em->getRepository('AppOrderformBundle:Encounter')->getNextNonProvided($encounter2,null,$message);
        //$newEncounterStr = $nextKey." (".$newkeytypeEntity.")";
        //$previousEncounters[$newEncounterStr] = $newEncounterStr;

        $patient = NULL;
        if( $message ) {
            //patient exists
            $patients = $message->getPatient();
            if( count($patients) > 0 ) {
                $patient = $patients->first();
            }
        }

        //$patient = $this->em->getRepository('AppOrderformBundle:Patient')->find(47); //testing
        if( $patient ) {
            //if patient exists
            //$encounters = "";
            $encounters = $patient->getEncounter();
            foreach($encounters as $thisEncounter) {
                if( $thisEncounter && $thisEncounter->getStatus() == 'valid' ) {
                    $this->em->persist($thisEncounter);
                    $previousEncounters[] = $thisEncounter;
                    //$previousEncounters[$thisEncounter->obtainEncounterNumberOnlyAndDate()] = $thisEncounter->getId();
                }
            }
        } else {
            //if patient does not exists
            //return NULL as previous encounter?
        }

        /// testing ///
//        $choices = array();
//        foreach($previousEncounters as $previousEncounter) {
//            //$choices[$previousEncounter->getId()] = $previousEncounter->obtainEncounterNumberOnlyAndDate();
//            $choices[$previousEncounter->obtainEncounterNumberOnlyAndDate()] = $previousEncounter->getId();
//        }
//        return $choices;
        /// EOF testing ///

//        echo "previousEncounters count=".count($previousEncounters)."<br>";
//        foreach($previousEncounters as $previousEncounter) {
//            echo "previousEncounter=".$previousEncounter."<br>";
//        }

        return $previousEncounters;
    }
    public function getPreviousEncounterByPatient( $patient, $asCombobox=true ) {
        $previousEncounters = array();
        
        if( $patient ) {
            //if patient exists
            //$encounters = "";
            //$encounters = $patient->getEncounter();
        //process.py script: replaced namespace by ::class: ['AppOrderformBundle:Encounter'] by [Encounter::class]
            $encounters = $this->em->getRepository(Encounter::class)->findBy(
                array('patient'=> $patient->getId()),
                //array('id' => 'ASC')
                array('id' => 'DESC')
            );

            foreach($encounters as $thisEncounter) {
                //echo "id=".$thisEncounter->getId()."<br>";
                if( $asCombobox ) {
                    //$previousEncounters[$thisEncounter->getId()] = $thisEncounter->obtainEncounterNumberOnlyAndDate();
                    $previousEncounters[] = array('id'=>$thisEncounter->getId(),'text'=>$thisEncounter->obtainEncounterNumberOnlyAndDate());
                } else {
                    //$this->em->persist($thisEncounter);
                    $previousEncounters[] = $thisEncounter;
                }
            }
        } else {
            //if patient does not exists
            //return NULL as previous encounter?
        }
        //exit("111");

        return $previousEncounters;
    }

    public function createCopyDocument($document,$holderEntity) {
        $logger = $this->container->get('logger');
        $user = $this->security->getUser();
        $newDocument = new Document($user);

        $originalname = $document->getOriginalname();
        if( $originalname ) {
            $newDocument->setOriginalname($originalname);
        }

        //$uniquename;
        $uniquename = $document->getUniquename();
        //echo $document->getId().": uniquename=".$uniquename."<br>";
        //echo $document->getId().": originalname=".$document->getOriginalname()."<br>";
        if( $uniquename ) {
            $uniquename = "copy_".$uniquename;
            $newDocument->setUniquename($uniquename);
        }
        //echo "newDocument->getUniquename=".$newDocument->getUniquename()."<br>";

        //$uploadDirectory;
        $uploadDirectory = $document->getUploadDirectory();
        if( $uploadDirectory ) {
            $newDocument->setUploadDirectory($uploadDirectory);
        }

        //$size;
        $size = $document->getSize();
        if( $size ) {
            $newDocument->setSize($size);
        }

        //$file;

        //$type;
        $type = $document->getType();
        if( $type ) {
            $newDocument->setType($type);
        }

        //$creator;
        $creator = $document->getCreator();
        if( $creator ) {
            $newDocument->setCreator($creator);
        }

        //$createdate;
        $createdate = $document->getCreatedate();
        if( $createdate ) {
            $newDocument->setCreatedate($createdate);
        }

        //$externalCreatedate;
        $externalCreatedate = $document->getExternalCreatedate();
        if( $externalCreatedate ) {
            $newDocument->setExternalCreatedate($externalCreatedate);
        }

        //$title;
        $title = $document->getTitle();
        if( $title ) {
            $newDocument->setTitle($title);
        }

        //$uniqueid;
        $uniqueid = $document->getUniqueid();
        if( $uniqueid ) {
            $uniqueid = "copy_".$uniqueid;
            $newDocument->setUniqueid($uniqueid);
        }

        $originalFile = $document->getFullServerPath();
        //echo "originalFile=$originalFile<br>";
        $newFile = $newDocument->getFullServerPath(false); //use $withRealPath=false because file does not exists yet
        //echo "newFile=$newFile<br>";
        //exit('test');

        if (!copy($originalFile, $newFile)) {
            //echo "failed to copy $originalFile...\n";
            //exit('test');
            $logger->error("createCopyDocument: failed to copy $originalFile");
        }

        if( file_exists($newFile) ) {
            // 0700 - Read and write, execute for owner, nothing for everybody else
            //chmod($newDocument, 0700);
        } else {
            //echo "File does not exists: [".$newFile."] <br>";
            //exit('test');
            $logger->error("createCopyDocument: file does not exists: [".$newFile."]");
        }

//        private $entityNamespace;
//        private $entityName;
//        private $entityId;
        $newDocument->setObject($holderEntity);

        $this->em->persist($newDocument);

        return $newDocument;
    }

    public function processCalllogTask($message,$originalTasks) {
        // remove the relationship between the CalllogEntryMessage and the Task

//        //testing
//        foreach($message->getCalllogEntryMessage()->getCalllogTasks() as $task) {
//            echo "Current task=".$task."<br>";
//        }
//        foreach($originalTasks as $task) {
//            echo "Original task=".$task."<br>";
//        }

        $calllogEntryMessage = $message->getCalllogEntryMessage();

        $taskUpdateArr = array();
        foreach($originalTasks as $task) {
            //if( false === $calllogEntryMessage->getCalllogTasks()->contains($task) ) {
            if( $this->taskExists($task,$calllogEntryMessage->getCalllogTasks()) === false ) {
                //$taskUpdateArr[] = "Removed task ID#".$task->getId().": ".$task->getTaskFullInfo();
                $taskUpdateArr[] = "Removed task: ".$task->getTaskFullInfo();
                // remove the Task from the Tag
                $calllogEntryMessage->getCalllogTasks()->removeElement($task);
                // if it was a many-to-one relationship, remove the relationship like this
                //$task->setCalllogEntryMessage(null);
                //$this->em->persist($task);
                // if you wanted to delete the Tag entirely, you can also do that
                //$this->em->remove($task);
            }
        }

        //set creator for remaining tasks
        $user = $this->security->getUser();
        foreach( $calllogEntryMessage->getCalllogTasks() as $task ) {

            //remove empty tasks
            if( $task->isEmpty() ) {
                $taskUpdateArr[] = "Removed empty (no description) task: ".$task->getTaskFullInfo();
                $calllogEntryMessage->removeCalllogTask($task);
                $task->setCalllogEntryMessage(NULL);
            }

            if( !$task->getCreatedBy() ) {
                $task->setCreatedBy($user);
            }
        }

        $taskUpdateStr = NULL;
        if( count($taskUpdateArr) > 0 ) {
            $taskUpdateStr = implode("<br>", $taskUpdateArr);
        }

//        //testing
//        foreach($message->getCalllogEntryMessage()->getCalllogTasks() as $task) {
//            echo "Final task=".$task."<br>";
//        }

        return $taskUpdateStr;
    }
    public function taskExists($task,$tasks) {
        foreach($tasks as $thisTask) {
            if( $thisTask->getId() == $task->getId() ) {
                return true;
            }
        }
        return false;
    }

    public function getTasksInfo( $message ) {

        $colspan = 9;
        $colspan1 = 2;
        $colspan2 = 3;
        $colspan3 = $colspan - $colspan1 - $colspan2;

        if( !$message->getCalllogEntryMessage() ) {
            return null;
        }

        $tasks = $message->getCalllogEntryMessage()->getCalllogTasks();
        if( count($tasks) == 0 ) {
            return null;
        }
        //return null;

//        <tr class="table-no-border">
//           <td style="display: none">
//              <a href="/order/call-log-book/entry/view/548/1" target="_blank">548.1</a>
//           </td>
//           <td colspan="9">
//              <table class="table table-hover table-condensed">
//                  <tbody>
//                      <tr class="table-row-separator-white">
//                          <td colspan="9" class="rowlink-skip"><i>History/Findings</i></td>
//                      </tr>
//                      <tr class="table-row-separator-white">
//                          <td colspan="3" class="rowlink-skip" style="width:20%"></td>
//                          <td colspan="6" class="rowlink-skip" style="width:80%"><p>hhh<br></p></td>
//                      </tr>
//                      <tr class="table-row-separator-white">
//                          <td colspan="9" class="rowlink-skip"><i>Impression/Outcome</i></td>
//                      </tr>
//                      <tr class="table-row-separator-white">
//                          <td colspan="3" class="rowlink-skip" style="width:20%"></td>
//                          <td colspan="6" class="rowlink-skip" style="width:80%"><p>iii<br></p></td>
//                      </tr>
//                  </tbody>
//              </table>
//            </td>
//        </tr>

        //$tdClass = '"rowlink-skip"';
        //$tdClass = 'rowlink-skip';
        $tdClass = '';

        //$tdClass2 = '"rowlink-skip"';
        $tdClass2 = '';

        $body = "";
        $body = $body . '<tr class="table-no-border">';
        //$body = $body . '<tr class="table-no-border rowlink-skip">';

//        $body = $body . '<td style="display: none">';
//        $messageUrl = $this->container->get('router')->generate(
//            'calllog_callentry_view',
//            array(
//                'messageOid' => $message->getOid(),
//                'messageVersion' => $message->getVersion()
//            ),
//            UrlGeneratorInterface::ABSOLUTE_URL
//        );
//        $body = $body . '<a target="_blank" href="' . $messageUrl . '"</a>';
//        $body = $body . '</td>';

        $errHolder = '<div class="alert alert-danger calllog-danger-box" style="display: none; margin-top: 5px;"></div>';
        $body = $body . '<td colspan="9" class='.$tdClass.'>Task(s)'.' '.$errHolder;
        $body = $body . '<table class="table table-hover table-condensed">';
        //$body = $body . '<tbody data-link="row" class="rowlink">';
        $body = $body . '<tbody>';

        foreach( $tasks as $task ) {

            $body = $body . '<tr class="table-row-separator-white calllog-task-tr">';

            /////////// Checkbox ///////////
            $taskInfo = $task->getTaskInfo();
            if( $taskInfo ) {
                $taskInfo = " (" . $taskInfo . ")";
            }

            if( $task->getStatus() ) {
                $statusValue = "checked";
                $tdClass2 = '"calllog-task-td bg-success"';
            } else {
                $statusValue = "";
                $tdClass2 = '"calllog-task-td bg-danger"';
            }

            $status = null;
            //$status = "ID#".$task->getId();
            $status = $status.'<input data-toggle="tooltip" title="Check to mark task as completed" type="checkbox" class="task-status-checkbox" data-taskstatus="'.$statusValue.'"';
            $status = $status . ' id="' . $task->getId() . '"';
            //$status = $status . 'name="' . 'taskid-'.$task->getId() . '"';
            //$status = $status . ' value="' . $statusValue . '"';
            $status = $status . ' ' . $statusValue;
            $status = $status . ' onClick="calllogTaskStatusCheckboxClick(this);"';
            $status = $status . '>';

            //Update button
            $cycle = '"list"';
            $cycle = "'list'";
            $updateBtn = '&nbsp; <div class="btn btn-sm btn-primary btn-update-task" style="display: none;" onClick="calllogUpdateTaskBtnClicked(this,'.$cycle.')">Update</div>';

            //calllog-danger-box
            $dangerBox = '&nbsp; <div class="alert alert-danger calllog-danger-box" style="display: none;">Update</div>';

            $body = $body . '<td colspan='.$colspan1 . ' class="calllog-checkbox-checkbox" style="width:2%">' . $status . $updateBtn . $dangerBox . '</td>';
            /////////// EOF Checkbox ///////////

            $body = $body . '<td colspan='.$colspan2 . ' class='.$tdClass2.' style="width:18%">' . '' . $task->getCalllogTaskType() . '</td>';
            $body = $body . '<td colspan='.$colspan3 . ' class='.$tdClass2.' style="width:80%">' . $task->getDescription() . $taskInfo . '</td>';

            $body = $body . '</tr>';

        }

        $body = $body . '</tbody>';
        $body = $body . '</table>';
        $body = $body . '</td>';
        $body = $body . '</tr>';

//        $result =
//            '<td colspan='.$colspan.'>'.
//            '<table class = "table table-hover table-condensed">' .
//            $body .
//            '</table></td>';

        return $body;
    }
    public function getTasksInfo2( $message ) {

        $colspan = 9;
        $colspan1 = 1;
        $colspan2 = 3;
        $colspan3 = $colspan - $colspan1 - $colspan2;

        $tasks = $message->getCalllogEntryMessage()->getCalllogTasks();
        if( count($tasks) == 0 ) {
            return null;
        }
        //return null;

        $tdClass = '"rowlink-skip"';
        //$tdClass = '';

        $body = "";
        //$body = $body . '<tr class="table-no-border">';

        //$body = $body . '<td colspan="9" class='.$tdClass.'>';
        //$body = $body . '<table class="table table-hover table-condensed">';
        //$body = $body . '<tbody data-link="row" class="rowlink">';
        //$body = $body . '<tbody>';

        foreach( $tasks as $task ) {

            $body = $body . '<tr class="table-row-separator-white rowlink-skip">';

            $taskInfo = $task->getTaskInfo();
            if( $taskInfo ) {
                $taskInfo = " (" . $taskInfo . ")";
            }

            if( $task->getStatus() ) {
                $statusValue = "checked";
            } else {
                $statusValue = "";
            }

            $status = '<input type="checkbox" class="task-status-checkbox"';
            $status = $status . ' id="' . $task->getId() . '"';
            //$status = $status . 'name="' . 'taskid-'.$task->getId() . '"';
            //$status = $status . ' value="' . $statusValue . '"';
            $status = $status . ' ' . $statusValue;
            //$status = $status . 'onClick="this.checked=!this.checked;"';
            $status = $status . 'onClick="calllogTaskStatusCheckboxClick(this)"';
            $status = $status . '>';
            $body = $body . '<td colspan='.$colspan1 . ' class='.$tdClass.' style="width:5%">' . $status . '</td>';

            $body = $body . '<td colspan='.$colspan2 . ' class='.$tdClass.' style="width:20%">' . '' . $task->getCalllogTaskType() . '</td>';
            $body = $body . '<td colspan='.$colspan3 . ' class='.$tdClass.' style="width:75%">' . $task->getDescription() . $taskInfo . '</td>';

            $body = $body . '</tr>';

        }

        //$body = $body . '</tbody>';
        //$body = $body . '</table>';
        //$body = $body . '</td>';
        //$body = $body . '</tr>';


        return $body;
    }
    
    public function searchPatientByAccession($request, $params, $evenlog=false, $turnOffMetaphone=false) {

        //$userServiceUtil = $this->container->get('user_service_utility');
        //$calllogUtil = $this->container->get('calllog_util');
        $em = $this->em;

        $mrntype = ( array_key_exists('mrntype', $params) ? $params['mrntype'] : null);
        $mrn = ( array_key_exists('mrn', $params) ? $params['mrn'] : null);
        $accessionnumber = ( array_key_exists('accessionnumber', $params) ? $params['accessionnumber'] : null);
        $accessiontype = ( array_key_exists('accessiontype', $params) ? $params['accessiontype'] : null);
        $dob = ( array_key_exists('dob', $params) ? $params['dob'] : null);
        $lastname = ( array_key_exists('lastname', $params) ? $params['lastname'] : null);
        $firstname = ( array_key_exists('firstname', $params) ? $params['firstname'] : null);
        $phone = ( array_key_exists('phone', $params) ? $params['phone'] : null);
        $email = ( array_key_exists('email', $params) ? $params['email'] : null);
        $metaphone = ( array_key_exists('metaphone', $params) ? $params['metaphone'] : null);

        $exactMatch = true;
        //$matchAnd = true;
        $accessionFound = false;

        if( !$accessionnumber || !$accessiontype ) {
            //return null;
            $patientsData = array(
                'patients' => array(),
                'accessionFound' => $accessionFound,
                'searchStr' => "Logical Error: no accession is provided"
            );
            return $patientsData;
        }

        //echo "mrntype=".$mrntype."<br>";
        //echo "mrn=".$mrn."<br>";

        if( $turnOffMetaphone ) {
            $metaphone = null;
        }

        $parameters = array();

        //if accession 

        //process.py script: replaced namespace by ::class: ['AppOrderformBundle:Patient'] by [Patient::class]
        $repository = $em->getRepository(Patient::class);
        $dql = $repository->createQueryBuilder("patient");
        $dql->leftJoin("patient.mrn", "mrn");
        $dql->leftJoin("patient.dob", "dob");
        $dql->leftJoin("patient.lastname", "lastname");
        $dql->leftJoin("patient.firstname", "firstname");
        $dql->leftJoin("patient.encounter", "encounter");
        $dql->leftJoin("encounter.patlastname", "encounterLastname");
        $dql->leftJoin("encounter.patfirstname", "encounterFirstname");
        $dql->leftJoin("encounter.procedure", "procedure");
        $dql->leftJoin("procedure.accession", "accession");
        $dql->leftJoin("accession.accession", "accessionaccession");

        //$dql->where("mrn.status = :statusValid");

        //$where = false;
        $searchBy = "unknown parameters";
        $searchArr = array();

        $accessiontype = $this->convertAutoGeneratedAccessiontype($accessiontype,true);

        $accessionnumber = strtolower($accessionnumber);

        //echo "accessiontype=".$accessiontype."<br>";
        //echo "accessionnumber=".$accessionnumber."<br>";


        ///////// 1. Search by Accession ///////////////

        $dql->andWhere("accessionaccession.keytype = :keytype");
        $parameters['keytype'] = $accessiontype->getId();

        if( $exactMatch ) {
            $accessionnumberLtrim = ltrim((string)$accessionnumber, '0');
            //$accessionnumberRtrim = rtrim((string)$accessionnumber, '0');

            //$dql->andWhere("LOWER(accessionaccession.field) = :accessionnumber OR LOWER(accessionaccession.field) = :accessionnumberLtrim OR LOWER(accessionaccession.field) = :accessionnumberRtrim");
//            $dql->andWhere("LOWER(accessionaccession.field) = :accessionnumber OR LOWER(accessionaccession.field) = :accessionnumberLtrim");
//            $parameters['accessionnumber'] = $accessionnumber;
//            $parameters['accessionnumberLtrim'] = $accessionnumberLtrim;
            //$parameters['accessionnumberRtrim'] = $accessionnumberRtrim;

            //echo "accessionnumber: ".$accessionnumber."?=".$accessionnumberLtrim."<br>";
            if( $accessionnumber === $accessionnumberLtrim ) {
                //echo "equal <br>";
                $dql->andWhere("LOWER(accessionaccession.field) = :accessionnumber");
                $parameters['accessionnumber'] = $accessionnumber;
            } else {
                //echo "not equal <br>";
                $dql->andWhere("LOWER(accessionaccession.field) = :accessionnumber OR LOWER(accessionaccession.field) = :accessionnumberLtrim");
                $parameters['accessionnumber'] = $accessionnumber;
                $parameters['accessionnumberLtrim'] = $accessionnumberLtrim;
            }

        } else {
            $dql->andWhere("LOWER(accessionaccession.field) LIKE LOWER(:accession)");
            $parameters['accession'] = '%' . $accessionnumber . '%';
        }

        $dql->andWhere("accessionaccession.status = :statusValid OR accessionaccession.status = :statusAlias");
        $parameters['statusValid'] = 'valid';
        $parameters['statusAlias'] = 'alias';

        $where = true;
        $searchArr[] = "Accession Type: ".$accessiontype."; Accession Number: ".$accessionnumber;


        if( count($searchArr) > 0 ) {
            $searchBy = implode("; ",$searchArr);
        }

        if( $where ) {

            $query = $dql->getQuery(); //$query = $em->createQuery($dql);
            $query->setParameters($parameters);
            $patients = $query->getResult();

            //log search action
            if( $evenlog ) {
                if( count($patients) == 0 ) {
                    $patientEntities = null;
                } else {
                    $patientEntities = $patients;
                }
                $user = $this->security->getUser();
                $userSecUtil = $this->container->get('user_security_utility');
                $eventType = "Patient Searched";
                $event = "Patient searched by ".$searchBy;
                $event = $event . "; found ".count($patients)." patient(s).";
                $userSecUtil->createUserEditEvent($this->container->getParameter('calllog.sitename'),$event,$user,$patientEntities,$request,$eventType); //searchPatient
            }

        } else {
            $patients = array();
        }
        ///////// EOF 1. Search by Accession ///////////////

        if( count($patients) == 0 ) {

            ///////// Search by Patient info (use a regular logic searchPatientByMrn) ///////////////
            $patientsData =  $this->searchPatientByMrn($request,$params,$evenlog,$turnOffMetaphone);

            $patients = $patientsData['patients'];
            $searchStr = $patientsData['searchStr'];
            $searchBy = $searchBy . "; " . $searchStr;

            if( count($patients) > 0 ) {
                if( count($patients) == 1 ) {
                    //TODO: Add this accession to this patient
                } else {
                    //TODO: Multiple patients
                }
            } else {
                //TODO: Add new patient record and accession number
            }

        } elseif ( count($patients) == 1 ) {

            ///////// Compare entered patient info with found by accession /////////

            $patient = $patients[0];
            $samePatient = true;
            $accessionFound = true;

            //mrn
            $mrnRes = $patient->obtainStatusField('mrn', "valid");
            if( $mrn && $mrntype ) {
                if( $mrntype && $mrntype != $mrnRes->getKeytype() ) {
                    $samePatient = false;
                }
                if( $mrn && $mrn != $mrnRes->getField() ) {
                    $samePatient = false;
                }
            }

            //lastname
            $patientLastName = $patient->obtainStatusField('lastname', "valid");
            if( $lastname && $lastname != $patientLastName ) {
                $samePatient = false;
            }

            //firstname
            $patientFirstName = $patient->obtainStatusField('firstname', "valid");
            if( $firstname && $firstname != $patientFirstName ) {
                $samePatient = false;
            }

            //dob
            $patientDob = $patient->obtainStatusField('dob', "valid");
            //TODO: test date
            //echo "dob=".$dob."<br>";
            //echo "patientDob=".$patientDob."<br>";
            if( $dob && $dob != $patientDob ) { //$dob->format('m/d/Y')
                $samePatient = false;
            }

            //email
            $patientEmail = $patient->getEmailCanonical();
            if( $email && strtolower($email) != $patientEmail ) {
                $samePatient = false;
            }

            //phone
            $patientPhone = $patient->getPhoneCanonical();
            if( $phone && $patientPhone ) {
                $phone = str_replace(" ","",$phone);
                $phone = str_replace("-","",$phone);
                $phone = str_replace("(","",$phone);
                $phone = str_replace(")","",$phone);

                $patientPhone = str_replace(" ","",$patientPhone);
                $patientPhone = str_replace("-","",$patientPhone);
                $patientPhone = str_replace("(","",$patientPhone);
                $patientPhone = str_replace(")","",$patientPhone);

                if( $phone && strtolower($phone) != $patientPhone ) {
                    $samePatient = false;
                }
            }

            if( $samePatient ) {
                //Populate all fields, show previous notes
            } else {
                //$accessionFound but (count(patients) == 0) => found patient does not match entered patient's info
                //Not the same patient
                $patients = array();
                $searchBy = $searchBy . " " . "Entered patient information does not match entered accession number.";
            }

            ///////// EOF Compare entered patient info with found by accession /////////

        } elseif ( count($patients) > 1 ) {

            //We should have only one patient associated with Accession
            $searchBy = $searchBy . " " . "Multiple patients associated with this accession number.";
            $accessionFound = true;

        } else {

            //We should not reach this place
            $searchBy = $searchBy . " " . "Logical error. Found patients: " . count($patients);
        }


        $patientsData = array(
            'patients' => $patients,
            'searchStr' => $searchBy,
            'accessionFound' => $accessionFound
        );

        return $patientsData;
    }//searchPatientByAccession


    //search patients: used by JS when search for patient in the new entry page (calllog_search_patient)
    // and to verify before creating patient if already exists (calllog_create_patient)
    public function searchPatientByMrn( $request, $params, $evenlog=false, $turnOffMetaphone=false ) {

        $userServiceUtil = $this->container->get('user_service_utility');
        $calllogUtil = $this->container->get('calllog_util');

//        $mrntype = trim((string)$request->get('mrntype')); //ID of mrn type
//        $mrn = trim((string)$request->get('mrn'));
//        $accessionnumber = trim((string)$request->get('accessionnumber'));
//        $accessiontype = trim((string)$request->get('accessiontype'));
//        $dob = trim((string)$request->get('dob'));
//        $lastname = trim((string)$request->get('lastname'));
//        $firstname = trim((string)$request->get('firstname'));
//        $phone = trim((string)$request->get('phone'));
//        $email = trim((string)$request->get('email'));
//        $metaphone = trim((string)$request->get('metaphone'));
//        //echo "phone=".$phone.", email=".$email."<br>";
//        //print_r($allgets);
//        //echo "metaphone=".$metaphone."<br>";
//        //exit('1');

        $mrntype = ( array_key_exists('mrntype', $params) ? $params['mrntype'] : null);
        $mrn = ( array_key_exists('mrn', $params) ? $params['mrn'] : null);
        $accessionnumber = ( array_key_exists('accessionnumber', $params) ? $params['accessionnumber'] : null);
        $accessiontype = ( array_key_exists('accessiontype', $params) ? $params['accessiontype'] : null);
        $dob = ( array_key_exists('dob', $params) ? $params['dob'] : null);
        $lastname = ( array_key_exists('lastname', $params) ? $params['lastname'] : null);
        $firstname = ( array_key_exists('firstname', $params) ? $params['firstname'] : null);
        $phone = ( array_key_exists('phone', $params) ? $params['phone'] : null);
        $email = ( array_key_exists('email', $params) ? $params['email'] : null);
        $metaphone = ( array_key_exists('metaphone', $params) ? $params['metaphone'] : null);

        $exactMatch = true;
        $matchAnd = true;

        //echo "mrntype=".$mrntype."<br>";
        //echo "mrn=".$mrn."<br>";

        if( $turnOffMetaphone ) {
            $metaphone = null;
        }

        $em = $this->em; //getDoctrine()->getManager();

        $parameters = array();

        //process.py script: replaced namespace by ::class: ['AppOrderformBundle:Patient'] by [Patient::class]
        $repository = $em->getRepository(Patient::class);
        $dql = $repository->createQueryBuilder("patient");
        $dql->leftJoin("patient.mrn", "mrn");
        $dql->leftJoin("patient.dob", "dob");
        $dql->leftJoin("patient.lastname", "lastname");
        $dql->leftJoin("patient.firstname", "firstname");
        $dql->leftJoin("patient.encounter", "encounter");
        $dql->leftJoin("encounter.patlastname", "encounterLastname");
        $dql->leftJoin("encounter.patfirstname", "encounterFirstname");

        //$dql->where("mrn.status = :statusValid");

        $where = false;
        $searchBy = "unknown parameters";
        $searchArr = array();

//        //accession (If anything was entered into the Accession Number field, ignore content of all other fields)
//        if( $accessionnumber && $accessiontype ) {
//            return $calllogUtil->searchByAccession($request, $evenlog, $params, $turnOffMetaphone);
//        }

        if( $mrntype ) {
            $mrntype = $this->convertAutoGeneratedMrntype($mrntype,true);
        }

        //echo "mrntype=".$mrntype."<br>";
        //echo "mrn=".$mrn."<br>";

        //mrn
        if( $mrntype && $mrn ) {

            //echo "mrntype=".$mrntype."<br>";
            //echo "mrn=".$mrn."<br>";

            $dql->andWhere("mrn.keytype = :keytype");
            $parameters['keytype'] = $mrntype->getId();

            if( $exactMatch ) {
                $mrnClean = ltrim((string)$mrn, '0');
                //echo "mrn: ".$mrn."?=".$mrnClean."<br>";
                if( $mrn === $mrnClean ) {
                    //echo "equal <br>";
                    $dql->andWhere("mrn.field = :mrn");
                    $parameters['mrn'] = $mrn;
                } else {
                    //echo "not equal <br>";
                    $dql->andWhere("mrn.field = :mrn OR mrn.field = :mrnClean");
                    $parameters['mrn'] = $mrn;
                    $parameters['mrnClean'] = $mrnClean;
                }

            } else {
                $dql->andWhere("LOWER(mrn.field) LIKE LOWER(:mrn)");
                $parameters['mrn'] = '%' . $mrn . '%';
            }

            //search by only valid, alias status
            //$dql->andWhere("mrn.status = :statusValid OR mrn.status = :statusAlias");
            //$parameters['statusValid'] = 'valid';
            //$parameters['statusAlias'] = 'alias';

            $where = true;
            $searchArr[] = "MRN Type: ".$mrntype."; MRN: ".$mrn;
        }

        //DOB
        if( $dob && ($where == false || $matchAnd == true) ) {
            //echo "dob=".$dob."<br>";
            $searchArr[] = "DOB: " . $dob;
            //echo "doblen=".strlen((string)$dob);
            if( strlen((string)$dob) == 10 ) {
                $dobDateTime = \DateTime::createFromFormat('m/d/Y', $dob)->format('Y-m-d');
                //return $d && $d->format($format) === $date;
                //echo "dob=".$dob." => ".$dobDateTime."<br>";
                $dql->andWhere("dob.status = :statusValid OR dob.status = :statusAlias");
                $dql->andWhere("dob.field = :dob");
                $parameters['dob'] = $dobDateTime;
                $parameters['statusValid'] = 'valid';
                $parameters['statusAlias'] = 'alias';
                $where = true;
            } else {
                $searchArr[] = "DOB '$dob' is not in the valid format (mm/dd/YYYY)";
            }
        }

        //$lastname = null;
        //$firstname = null;
        //Last Name AND First Name
        if( ($lastname || $firstname) && ($where == false || $matchAnd == true) ) {
            //$lastname = "Doe";
            //echo "1 lastname=".$lastname."<br>";
            //echo "1 firstname=".$firstname."<br>";

            $searchCriterionArr = array();

            //only last name
            if( $lastname && !$firstname ) {
                $searchArr[] = "Last Name: " . $lastname;

                $statusStr = "(lastname.status = :statusValid OR lastname.status = :statusAlias)";

                if( $metaphone ) {
                    $lastnameCriterion = $userServiceUtil->getMetaphoneStrLike("lastname.field","lastname.fieldMetaphone",$lastname,$parameters);
                    if( $lastnameCriterion ) {
                        $searchCriterionArr[] = $lastnameCriterion . " AND " . $statusStr;

                        $parameters['statusValid'] = 'valid';
                        $parameters['statusAlias'] = 'alias';

                        $where = true;
                    }
                } else {
                    //exact search
                    $searchCriterionArr[] = "LOWER(lastname.field) LIKE LOWER(:lastname) AND $statusStr";
                    $parameters['lastname'] = "%".$lastname."%";
                    $parameters['statusValid'] = 'valid';
                    $parameters['statusAlias'] = 'alias';
                    $where = true;
                }
            }

            //only first name
            if( $firstname && !$lastname ) {
                $searchArr[] = "First Name: " . $firstname;

                $statusStr = "(firstname.status = :statusValid OR firstname.status = :statusAlias)";

                if( $metaphone ) {
                    $firstnameCriterion = $userServiceUtil->getMetaphoneStrLike("firstname.field","firstname.fieldMetaphone",$firstname,$parameters);
                    if( $firstnameCriterion ) {
                        $searchCriterionArr[] = $firstnameCriterion . " AND " . $statusStr;

                        $parameters['statusValid'] = 'valid';
                        $parameters['statusAlias'] = 'alias';

                        $where = true;
                    }
                } else {
                    //exact search
                    $searchCriterionArr[] = "LOWER(firstname.field) LIKE LOWER(:firstname) AND $statusStr";
                    $parameters['firstname'] = "%".$firstname."%";
                    $parameters['statusValid'] = 'valid';
                    $parameters['statusAlias'] = 'alias';
                    $where = true;
                }
            }

            if( $firstname && $lastname ) {
                $searchArr[] = "Last Name: " . $lastname;
                $searchArr[] = "First Name: " . $firstname;

                if( $metaphone ) {

                    $lastnameStatusStr = "(lastname.status = :statusValid OR lastname.status = :statusAlias)";
                    $lastnameCriterion = $userServiceUtil->getMetaphoneStrLike("lastname.field","lastname.fieldMetaphone",$lastname,$parameters,"lastname");
                    if ($lastnameCriterion) {
                        $searchCriterionArr[] = $lastnameCriterion . " AND " . $lastnameStatusStr;
                        //$searchCriterionArr[] = $lastnameCriterion;

                        $parameters['statusValid'] = 'valid';
                        $parameters['statusAlias'] = 'alias';

                        $where = true;
                    }

                    $firstnameStatusStr = "(firstname.status = :statusValid OR firstname.status = :statusAlias)";
                    $firstnameCriterion = $userServiceUtil->getMetaphoneStrLike("firstname.field","firstname.fieldMetaphone",$firstname,$parameters,"firstname");
                    if ($firstnameCriterion) {
                        $searchCriterionArr[] = $firstnameCriterion . " AND " . $firstnameStatusStr;
                        //$searchCriterionArr[] = $firstnameCriterion;

                        $parameters['statusValid'] = 'valid';
                        $parameters['statusAlias'] = 'alias';

                        $where = true;
                    }

                } else {

                    //exact search
                    //last name: status
                    $statusStrLastname = "(lastname.status = :statusValid OR lastname.status = :statusAlias)";
                    //$searchCriterionArr[] = "lastname.field LIKE :lastname AND $statusStr";
                    //$parameters['lastname'] = '%'.$lastname.'%';
                    $searchCriterionArr[] = "LOWER(lastname.field) LIKE LOWER(:lastname) AND $statusStrLastname";
                    $parameters['lastname'] = "%".$lastname."%";

                    //first name: status
                    $statusStrFirstname = "(firstname.status = :statusValid OR firstname.status = :statusAlias)";
                    //$searchCriterionArr[] = "firstname.field LIKE :firstname AND $statusStr";
                    //$parameters['firstname'] = '%'.$firstname.'%';
                    $searchCriterionArr[] = "LOWER(firstname.field) LIKE LOWER(:firstname) AND $statusStrFirstname";
                    $parameters['firstname'] = "%".$firstname."%";

                    $parameters['statusValid'] = 'valid';
                    $parameters['statusAlias'] = 'alias';
                    $where = true;

                }//if

                //testing
                if(0) {
                    echo "metaphone=".$metaphone."<br>";
                    echo "<pre>";
                    print_r($searchCriterionArr);
                    echo "</pre>";
                    echo "parameters:"."<br><pre>";
                    print_r($parameters);
                    echo "</pre>";
                    exit();
                }
            }

            if( count($searchCriterionArr) > 0 ) {
                //" OR " or " AND "
                $searchCriterionStr = implode(" AND ", $searchCriterionArr);
                $dql->andWhere($searchCriterionStr);
            }
        }

        //phone & email: search as AND
        //echo "phone=".$phone.", email=".$email."<br>";
        //if( $phone && ($where == false || $matchAnd == true) ) {
        if( $phone ) {
            $searchArr[] = "Phone: " . $phone;
            //$statusStr = "(patient.phoneCanonical = :phoneCanonical)";
            //$searchCriterionArr[] = $statusStr;
            $dql->andWhere("(patient.phoneCanonical LIKE :phoneCanonical)");
            $phoneCanonical = $this->obtainPhoneCanonical($phone);
            //echo "phoneCanonical=".$phoneCanonical."<br>";
            $parameters['phoneCanonical'] = "%".$phoneCanonical."%";
            $where = true;
        }

        //if( $email && ($where == false || $matchAnd == true) ) {
        if( $email ) {
            $searchArr[] = "E-Mail: " . $email;
            //$statusStr = "(patient.emailCanonical = :emailCanonical)";
            $dql->andWhere("(patient.emailCanonical LIKE :emailCanonical)");
            //$searchCriterionArr[] = $statusStr;
            $emailCanonical = strtolower($email);
            $parameters['emailCanonical'] = "%".$emailCanonical."%";
            $where = true;
        }


        if( count($searchArr) > 0 ) {
            $searchBy = implode("; ",$searchArr);
        }

        if( $where ) {

            $query = $dql->getQuery(); //$query = $em->createQuery($dql);
            $query->setParameters($parameters);
            //dump($parameters);
            //echo "sql=".$query->getSql()."<br>";
            //exit('test');
            $patients = $query->getResult();

            //testing
            //echo "sql=".$query->getSql()."<br>";
            //echo "parameters:"."<br><pre>";
            //print_r($query->getParameters());
            //exit();
            //echo "</pre>";
//            echo "<br>";
//            foreach( $patients as $patient ) {
//                echo "ID=".$patient->getId().": ".$patient->getFullPatientName()."<br>";
//                echo "patient=".$patient."<br>";
//            }
//            exit('patients count='.count($patients));

            //log search action
            if( $evenlog ) {
                if( count($patients) == 0 ) {
                    $patientEntities = null;
                } else {
                    $patientEntities = $patients;
                }
                $user = $this->security->getUser();
                $userSecUtil = $this->container->get('user_security_utility');
                $eventType = "Patient Searched";
                $event = "Patient searched by ".$searchBy;
                $event = $event . "; found ".count($patients)." patient(s).";
                $userSecUtil->createUserEditEvent($this->container->getParameter('calllog.sitename'),$event,$user,$patientEntities,$request,$eventType); //searchPatient
            }

        } else {
            $patients = array();
        }

        //search for merged
        //$calllogUtil = $this->get('calllog_util');
        //$patients = $calllogUtil->getAllMergedPatients( $patients );
        //exit('Finished.');

        $res = array();
        $res['patients'] = $patients;
        $res['searchStr'] = $searchBy;

        return $res;
    }

    public function createNewOrFindExistingAccession( $accessionNumber, $accessionType, $user=NULL ) {
        $accession = $this->findExistingAccession($accessionNumber,$accessionType);

        if( !$accession ) {
            $accession = $this->createNewAccession($accessionNumber,$accessionType,$user);
        }

        return $accession;
    }
    public function findExistingAccession( $accessionNumber, $accessionType, $returnAccessions=false ) {
        $accession = NULL;

        if( !$accessionType || !$accessionNumber ) {
            return $accession;
        }

        $accessionType = $this->convertAutoGeneratedAccessiontype($accessionType,true);

        $parameters = array();

        //process.py script: replaced namespace by ::class: ['AppOrderformBundle:Accession'] by [Accession::class]
        $repository = $this->em->getRepository(Accession::class);
        $dql = $repository->createQueryBuilder("accession");
        $dql->leftJoin("accession.accession", "accessionaccession");

        $dql->andWhere("accessionaccession.keytype = :keytype");
        $parameters['keytype'] = $accessionType->getId();

        $accessionNumber = strtolower($accessionNumber);
        $accessionNumberClean = ltrim((string)$accessionNumber, '0');

        //echo "accessionnumber: ".$accessionNumber."?=".$accessionNumberClean."<br>";
        if( $accessionNumber === $accessionNumberClean ) {
            //echo "equal <br>";
            $dql->andWhere("LOWER(accessionaccession.field) = :accessionnumber");
            $parameters['accessionnumber'] = $accessionNumber;
        } else {
            //echo "not equal <br>";
            $dql->andWhere("LOWER(accessionaccession.field) = :accessionnumber OR LOWER(accessionaccession.field) = :accessionnumberClean");
            $parameters['accessionnumber'] = $accessionNumber;
            $parameters['accessionnumberClean'] = $accessionNumberClean;
        }

        $dql->andWhere("accessionaccession.status = :statusValid OR accessionaccession.status = :statusAlias");
        $parameters['statusValid'] = 'valid';
        $parameters['statusAlias'] = 'alias';

        $query = $dql->getQuery();
        $query->setParameters($parameters);
        $accessions = $query->getResult();

        if( $returnAccessions ) {
            return $accessions;
        }

        if( count($accessions) > 0 ) {
            $accession = $accessions[0];
        }

        return $accession;
    }
    public function createNewAccession( $accessionNumber, $accessionType, $user=null ) {
        $accession = NULL;

        if( !$accessionType || !$accessionNumber ) {
            return $accession;
        }

        $securityUtil = $this->container->get('user_security_utility');

        if( !$user ) {
            $user = $this->security->getUser();
        }

        $accessionType = $this->convertAutoGeneratedAccessiontype($accessionType,true);

        $status = 'valid';
        $sourcesystem = $securityUtil->getDefaultSourceSystem($this->container->getParameter('calllog.sitename'));
        $accession = new Accession(false, $status, $user, $sourcesystem); //$withfields=false, $status='invalid', $provider=null, $source=null
        $accessionAccession = new AccessionAccession($status, $user, $sourcesystem);
        //add accession type
        $accessionAccession->setKeytype($accessionType);
        //add accession number
        $accessionAccession->setField($accessionNumber);
        $accessionDate = new AccessionAccessionDate($status, $user, $sourcesystem);
        $accession->addAccession($accessionAccession);
        $accession->addAccessionDate($accessionDate);
        
        return $accession;
    }

    public function getPatientsByAccessions( $request, $accessionnumber, $accessiontype ) {

        $output = null;
        $patients = array();
        $res = array(
            'output' => $output,
            'patients' => $patients
        );
        
        if( !$accessionnumber || !$accessiontype ) {
            return $res;
        }

        $accessionParams = array();
        $accessionParams['accessiontype'] = $accessiontype;
        $accessionParams['accessionnumber'] = $accessionnumber;
        //$patientsDataStrict = $this->searchPatientByAccession($request, false, $accessionParams);
        $patientsDataStrict = $this->searchPatientByAccession($request, $accessionParams, false);
        $patientsStrict = $patientsDataStrict['patients'];

        if (array_key_exists("accessionFound", $patientsDataStrict)) {
            $accessionFound = $patientsDataStrict['accessionFound'];
        } else {
            $accessionFound = false;
        }

        //$searchedStrStrict = $patientsDataStrict['searchStr'];
        if( $accessionFound ) {

            $searchedArr = array();

            foreach( $patientsStrict as $patientStrict ) {
                //Accession 001 of Accession type NYH Accession appears to belong to a patient with a last name of LLL, first name of FFFF, and a MM/DD/YYYY date of birth.
                $patientInfoStrict = $patientStrict->obtainPatientInfoShort();
                $searchedArr[] = "<br>Accession $accessionnumber of Accession type $accessiontype appears to belong to a patient $patientInfoStrict";
            }

            if( count($patientsStrict) > 0 ) {
                $output = "Can not create a new Patient. The patient with specified Accession already exists:<br>";
                if( $accessiontype ) {
                    $output .= "Accession Type: ".$accessiontype."<br>";
                }
                if( $accessionnumber ) {
                    $output .= "Accession: " . $accessionnumber . "<br>";
                }

                if( count($searchedArr) > 0 ) {
                    $output .= implode("<br>",$searchedArr);
                }
            }

        }//if( $accessionFound )

        $res['output'] = $output;
        $res['patients'] = $patientsStrict;

        return $res;
    }

    public function getAccessionsByPatient( $patient, $asHtml=false ) {

        $parameters = array();

        //process.py script: replaced namespace by ::class: ['AppOrderformBundle:Accession'] by [Accession::class]
        $repository = $this->em->getRepository(Accession::class);
        $dql = $repository->createQueryBuilder("accession");
        $dql->leftJoin("accession.procedure", "procedure");
        $dql->leftJoin("procedure.encounter", "encounter");
        $dql->leftJoin("encounter.patient", "patient");

        $dql->andWhere("patient.id = :patientId");
        $parameters['patientId'] = $patient->getId();

        $query = $dql->getQuery();
        $query->setParameters($parameters);
        $accessions = $query->getResult();

        if( $asHtml ) {
            $accessionInfoArr = array();
            foreach( $accessions as $accession ) {
                $accessionInfoArr[] = $accession->obtainFullValidKeyName();
            }

            if( count($accessionInfoArr) > 0 ) {
                return implode("<br>",$accessionInfoArr);
            }

            return NULL;
        }

        return $accessions;
    }

    public function getCalllogAccessionListType() {
        $accessionListTypeName = "Call Log";
        //process.py script: replaced namespace by ::class: ['AppOrderformBundle:AccessionListType'] by [AccessionListType::class]
        $accessionListType = $this->em->getRepository(AccessionListType::class)->findOneByName($accessionListTypeName);
        if( !$accessionListType ) {
            throw new \Exception( "CallLog AccessionListType not found by name ".$accessionListTypeName );
        }
        return $accessionListType;
    }

    public function obtainPhoneCanonical($phone) {
        //echo "original phone=".$phoneCanonical."<br>";
        $phoneCanonical = str_replace(' ', '', $phone); // Replaces all spaces with hyphens.
        $phoneCanonical = preg_replace('/[^0-9]/', '', $phoneCanonical); // Removes special chars.
        //exit("phoneCanonical=".$phoneCanonical);
        return $phoneCanonical;
    }

    public function getPatientMrn( $patient ) {

        $numberOfMrnToDisplay = 0; //0 or NULL - show only one valid MRN
        $numberOfMrnToDisplay = 2;
        //$numberOfMrnToDisplay = 500;

        //Get $numberOfMrnToDisplay from site settings
        $userSecUtil = $this->container->get('user_security_utility');
        $sitename = $this->container->getParameter('calllog.sitename');
        $numberOfMrnToDisplay = $userSecUtil->getSiteSettingParameter('numberOfMrnToDisplay',$sitename);
        if( !$numberOfMrnToDisplay ) {
            $numberOfMrnToDisplay = 0;
        }

        if( $numberOfMrnToDisplay && $numberOfMrnToDisplay > 0 ) {
            $resArr = $patient->obtainStatusFieldArray('mrn',null);
            //dump($resArr);
            //exit('111');

            $mrnArr = array();
            for( $x = 0; $x < $numberOfMrnToDisplay; $x++ ) {
                //echo "The number is: $x <br>";
                //exit('eee');
                if( isset($resArr[$x]) ) {
                    $mrn = $resArr[$x];
                } else {
                    break;
                }

                if( $mrn ) {
                    $mrnStr = $mrn->obtainOptimalName();
                    if( $mrn->getStatus() == 'invalid' ) {
                        $mrnStr = $mrnStr." (old)";
                    }
                    $mrnArr[] = $mrnStr;
                }
            }

            if( count($mrnArr) > 0 ) {
                $mrnRes = implode("<br>",$mrnArr);
            } else {
                $mrnRes = $patient->obtainFullValidKeyName();
            }

            return $mrnRes;
        }

        return $patient->obtainFullValidKeyName();
    }
    
    
    //Dashboards: moved from dashboard util
    public function getCalllogEntriesCount($startDate, $endDate, $eventTypeNameArr=array()) {

        $site = "calllog";

        $dqlParameters = array();

        //get the date from event log
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:Logger'] by [Logger::class]
        $repository = $this->em->getRepository(Logger::class);
        $dql = $repository->createQueryBuilder("logger");

        $dql->select("logger.id");

        $dql->leftJoin('logger.user', 'user');
        $dql->leftJoin('logger.eventType', 'eventType');

        $eventTypeNameStrArr = array();
        $eventTypeNameStr = "";
        foreach($eventTypeNameArr as $eventTypeName) {
            $eventTypeNameStrArr[] = "eventType.name = '$eventTypeName'";
        }

        if( count($eventTypeNameStrArr) > 0 ) {
            $eventTypeNameStr = implode(" OR ",$eventTypeNameStrArr);
        }

        $dql->andWhere($eventTypeNameStr);

        //$dql->andWhere("eventType.name = :eventTypeName");
        //$dqlParameters['eventTypeName'] = $eventTypeName;

        if( $site ) {
            $dql->andWhere("logger.siteName = :siteName");
            $dqlParameters['siteName'] = $site;
        }

        //$dql->andWhere("logger.creationdate > :startDate AND logger.creationdate < :endDate");
        $dql->andWhere('logger.creationdate >= :startDate'); //>=
        //$startDate->modify('-1 day');
        $dqlParameters['startDate'] = $startDate->format('Y-m-d H:i:s');

        $dql->andWhere('logger.creationdate <= :endDate'); //<=
        $endDate->modify('+1 day');
        $dqlParameters['endDate'] = $endDate->format('Y-m-d H:i:s');

        //$dql->orderBy("logger.id","DESC");
        $query = $dql->getQuery();

        $query->setParameters($dqlParameters);

        $loggers = $query->getResult();

        //echo "loggers=".count($loggers)."<br>";
        //exit();

        return count($loggers);
        //return 3;
    }
//    public function getTotalUniqueCalllogEntriesCount_ORIG($startDate, $endDate, $eventTypeNameArr=array()) {
//
//        $site = "calllog";
//
//        $dqlParameters = array();
//
//        //get the date from event log
//        $repository = $this->em->getRepository('AppUserdirectoryBundle:Logger');
//        $dql = $repository->createQueryBuilder("logger");
//
//        $dql->select("logger.entityId");
//
//        $dql->leftJoin('logger.eventType', 'eventType');
//
//        $dql->distinct();
//
//        $eventTypeNameStrArr = array();
//        $eventTypeNameStr = "";
//        foreach($eventTypeNameArr as $eventTypeName) {
//            $eventTypeNameStrArr[] = "eventType.name = '$eventTypeName'";
//        }
//
//        if( count($eventTypeNameStrArr) > 0 ) {
//            $eventTypeNameStr = implode(" OR ",$eventTypeNameStrArr);
//        }
//
//        $dql->andWhere($eventTypeNameStr);
//
//        //$dql->andWhere("eventType.name = :eventTypeName");
//        //$dqlParameters['eventTypeName'] = $eventTypeName;
//
//        if( $site ) {
//            $dql->andWhere("logger.siteName = :siteName");
//            $dqlParameters['siteName'] = $site;
//        }
//
//        //$dql->andWhere("logger.creationdate > :startDate AND logger.creationdate < :endDate");
//        $dql->andWhere('logger.creationdate >= :startDate'); //>=
//        //$startDate->modify('-1 day');
//        $dqlParameters['startDate'] = $startDate->format('Y-m-d H:i:s');
//
//        $dql->andWhere('logger.creationdate <= :endDate'); //<=
//        $endDate->modify('+1 day');
//        $dqlParameters['endDate'] = $endDate->format('Y-m-d H:i:s');
//
//        //$dql->orderBy("logger.id","DESC");
//        $query = $this->em->createQuery($dql);
//
//        $query->setParameters($dqlParameters);
//
//        $loggers = $query->getResult();
//
//        //echo "loggers=".count($loggers)."<br>";
//        //exit();
//
//        return count($loggers);
//        //return 3;
//    }
    public function getTotalUniqueCalllogEntriesCount($startDate, $endDate, $unique=false) {
        $dqlParameters = array();

        //get the date from event log
        //process.py script: replaced namespace by ::class: ['AppOrderformBundle:Message'] by [Message::class]
        $repository = $this->em->getRepository(Message::class);
        $dql = $repository->createQueryBuilder("message");

        $dql->select("message.oid");

        $dql->leftJoin("message.calllogEntryMessage","calllogEntryMessage");
        $dql->andWhere("calllogEntryMessage IS NOT NULL");

        $dql->andWhere("message.version > 1");

        if( $unique ) {
            $dql->distinct();
        }

        if( $startDate ) {
            //$dql->andWhere("logger.creationdate > :startDate AND logger.creationdate < :endDate");
            $dql->andWhere('message.orderdate >= :startDate');
            //$startDate->modify('-1 day');
            $dqlParameters['startDate'] = $startDate->format('Y-m-d H:i:s');
        }

        if( $endDate ) {
            $dql->andWhere('message.orderdate <= :endDate');
            $endDate->modify('+1 day');
            $dqlParameters['endDate'] = $endDate->format('Y-m-d H:i:s');
        }

        //$dql->orderBy("logger.id","DESC");
        $query = $dql->getQuery();

        $query->setParameters($dqlParameters);

        $messages = $query->getResult();

        //echo "loggers=".count($loggers)."<br>";
        //exit();

        return count($messages);
    }
    public function getCalllogPatientEntriesCount($startDate, $endDate, $unique=false) {
        $dqlParameters = array();

        //get the date from event log
        //process.py script: replaced namespace by ::class: ['AppOrderformBundle:Message'] by [Message::class]
        $repository = $this->em->getRepository(Message::class);
        $dql = $repository->createQueryBuilder("message");

        $dql->select("patient.id");

        $dql->leftJoin('message.patient', 'patient');

        $dql->leftJoin("message.calllogEntryMessage","calllogEntryMessage");
        $dql->andWhere("calllogEntryMessage IS NOT NULL");

        if( $unique ) {
            $dql->distinct();
        }

        //$dql->andWhere("logger.creationdate > :startDate AND logger.creationdate < :endDate");
        $dql->andWhere('message.orderdate >= :startDate');
        //$startDate->modify('-1 day');
        $dqlParameters['startDate'] = $startDate->format('Y-m-d H:i:s');

        $dql->andWhere('message.orderdate <= :endDate');
        $endDate->modify('+1 day');
        $dqlParameters['endDate'] = $endDate->format('Y-m-d H:i:s');

        //$dql->orderBy("logger.id","DESC");
        $query = $dql->getQuery();

        $query->setParameters($dqlParameters);

        $patients = $query->getResult();

        //echo "loggers=".count($loggers)."<br>";
        //exit();

        return count($patients);
    }
}