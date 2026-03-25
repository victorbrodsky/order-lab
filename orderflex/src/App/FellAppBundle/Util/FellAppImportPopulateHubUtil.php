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

/**
 * Created by PhpStorm.
 * User: DevServer
 * Date: 8/20/15
 * Time: 4:21 PM
 */

namespace App\FellAppBundle\Util;



use App\UserdirectoryBundle\Entity\EmploymentType; //process.py script: replaced namespace by ::class: added use line for classname=EmploymentType
use App\UserdirectoryBundle\Entity\LocationTypeList; //process.py script: replaced namespace by ::class: added use line for classname=LocationTypeList
use App\FellAppBundle\Entity\FellAppStatus; //process.py script: replaced namespace by ::class: added use line for classname=FellAppStatus
use App\UserdirectoryBundle\Entity\TrainingTypeList; //process.py script: replaced namespace by ::class: added use line for classname=TrainingTypeList
use App\UserdirectoryBundle\Entity\EventTypeList; //process.py script: replaced namespace by ::class: added use line for classname=EventTypeList
use App\UserdirectoryBundle\Entity\Logger; //process.py script: replaced namespace by ::class: added use line for classname=Logger

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityNotFoundException;
use App\FellAppBundle\Entity\DataFile;
use App\FellAppBundle\Entity\Interview;
use App\UserdirectoryBundle\Entity\AccessRequest;
use App\UserdirectoryBundle\Entity\BoardCertification;
use App\UserdirectoryBundle\Entity\Citizenship;
use App\UserdirectoryBundle\Entity\Document;
use App\UserdirectoryBundle\Entity\EmploymentStatus;
use App\UserdirectoryBundle\Entity\Examination;
use App\FellAppBundle\Entity\FellowshipApplication;
use App\UserdirectoryBundle\Entity\GeoLocation;
use App\UserdirectoryBundle\Entity\JobTitleList;
use App\UserdirectoryBundle\Entity\Location;
use App\FellAppBundle\Entity\Reference;
use App\UserdirectoryBundle\Entity\StateLicense;
use App\UserdirectoryBundle\Entity\Training;
use App\UserdirectoryBundle\Entity\User;
use App\UserdirectoryBundle\Form\DataTransformer\GenericTreeTransformer;
use App\UserdirectoryBundle\Util\EmailUtil;
use App\UserdirectoryBundle\Util\UserUtil;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Form\Extension\Core\DataTransformer\DateTimeToStringTransformer;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

use PhpOffice\PhpSpreadsheet\Reader\Xlsx as XlsxReader;

//$fellappImportPopulateUtil = $this->container->get('fellapp_importpopulate_util');

class FellAppImportPopulateHubUtil {

    protected $em;
    protected $container;

    protected $uploadDir;
    //protected $systemEmail;


    public function __construct(
        EntityManagerInterface $em,
        ContainerInterface $container
    ) {

        $this->em = $em;
        $this->container = $container;

        $this->uploadDir = 'Uploaded';
    }

    public function populateFellappFromFile( $file ) {
        $logger = $this->container->get('logger');
        $userSecUtil = $this->container->get('user_security_utility');
        $fellappImportPopulateUtil = $this->container->get('fellapp_importpopulate_util');
        $fellappRecLetterUtil = $this->container->get('fellapp_rec_letter_util');

        $systemUser = $userSecUtil->findSystemUser();
        $environment = $userSecUtil->getSiteSettingParameter('environment');

        // Load spreadsheet
        $reader = new XlsxReader();
        $spreadsheet = $reader->load($file);
        $sheet = $spreadsheet->getActiveSheet();

        $highestRow = $sheet->getHighestRow();
        $highestColumn = $sheet->getHighestColumn();

        // Get headers from row 1
        $headers = $sheet->rangeToArray('A' . 1 . ':' . $highestColumn . 1, NULL, TRUE, FALSE)[0];

        $populatedFellowshipApplications = new ArrayCollection();

        // Process each data row (starting from row 2)
        for ($row = 2; $row <= $highestRow; $row++) {

            if( $row > 2 ) {
                break; //testing
            }

            $rowData = $sheet->rangeToArray('A' . $row . ':' . $highestColumn . $row, NULL, TRUE, FALSE)[0];

            $googleFormId = $this->getValueByHeaderName('ID', $rowData, $headers);
            echo 'Processing $googleFormId=' . $googleFormId . "<br>";
            $logger->notice('Processing $googleFormId=' . $googleFormId);
            if (!$googleFormId) {
                continue; // Skip rows without ID
            }

            // Check if already exists
            $existingApp = $this->em->getRepository(FellowshipApplication::class)->findOneByGoogleFormId($googleFormId);
            if( $existingApp ) {
                $logger->notice('Skipping existing application with ID: ' . $googleFormId);
                exit('Skipping existing application with ID: ' . $googleFormId);
                continue;
            }

            try {
                $fellowshipApplication = $this->createFellappFromRow($rowData, $headers, $systemUser);
                if ($fellowshipApplication) {
                    $populatedFellowshipApplications->add($fellowshipApplication);
                }
            } catch (\Exception $e) {
                $logger->error('Error creating fellowship application from row ' . $row . ': ' . $e->getMessage());
            }
        }

        return $populatedFellowshipApplications;
    }

    /**
     * Create a single FellowshipApplication from a spreadsheet row
     */
    private function createFellappFromRow($rowData, $headers, $systemUser) {
        $logger = $this->container->get('logger');
        $userSecUtil = $this->container->get('user_security_utility');
        $fellappImportPopulateUtil = $this->container->get('fellapp_importpopulate_util');

        // Get required lookup entities
        $activeStatus = $this->em->getRepository(FellAppStatus::class)->findOneByName("active");
        if( !$activeStatus ) {
            throw new EntityNotFoundException('Unable to find entity by name='."active");
        }

        $employmentType = $this->em->getRepository(EmploymentType::class)->findOneByName("Pathology Fellowship Applicant");
        if( !$employmentType ) {
            throw new EntityNotFoundException('Unable to find entity by name='."Pathology Fellowship Applicant");
        }

        $userkeytype = $userSecUtil->getUsernameType('local-user');
        if( !$userkeytype ) {
            throw new EntityNotFoundException('Unable to find local user keytype');
        }

        // Get field values
        $googleFormId = $this->getValueByHeaderName('ID', $rowData, $headers);
        $originalAppId = $this->getValueByHeaderName('originalAppId', $rowData, $headers);
        $timestamp = $this->getValueByHeaderName('timestamp', $rowData, $headers);
        $lastName = $this->getValueByHeaderName('lastName', $rowData, $headers);
        $firstName = $this->getValueByHeaderName('firstName', $rowData, $headers);
        $middleName = $this->getValueByHeaderName('middleName', $rowData, $headers);
        $email = $this->getValueByHeaderName('email', $rowData, $headers);
        $primaryPublicUserId = $this->getValueByHeaderName('primaryPublicUserId', $rowData, $headers);

        if (!$email || !$lastName || !$firstName) {
            $logger->warning('Missing required fields (email, lastName, or firstName) for ID: ' . $googleFormId);
            return null;
        }

        // Create username
        $lastNameCap = $fellappImportPopulateUtil->capitalizeIfNotAllCapital($lastName);
        $firstNameCap = $fellappImportPopulateUtil->capitalizeIfNotAllCapital($firstName);
        $lastNameCap = preg_replace('/\s+/', '_', $lastNameCap);
        $firstNameCap = preg_replace('/\s+/', '_', $firstNameCap);
        $emailCanonical = trim(strtolower($email));
        $username = $lastNameCap . "_" . $firstNameCap . "_" . $emailCanonical;
        $usernameCanonical = trim(strtolower($username));

        $displayName = $firstName . " " . $lastName;
        if ($middleName) {
            $displayName = $firstName . " " . $middleName . " " . $lastName;
        }

        echo "emailCanonical=$emailCanonical, usernameCanonical=$usernameCanonical, primaryPublicUserId=$primaryPublicUserId <br>";

        // Check if user exists: doe_john_3_cinava1@yahoo.com_@_local-user
        //$user = $this->em->getRepository(User::class)->findOneByPrimaryPublicUserId($username);
        $user = $this->em->getRepository(User::class)->findOneByPrimaryPublicUserId($usernameCanonical);
        echo "1 Found findOneByPrimaryPublicUserId by usernameCanonical=$usernameCanonical => user=$user <br>";

        if (!$user) {
            $user = $this->em->getRepository(User::class)->findOneByPrimaryPublicUserId($primaryPublicUserId);
            echo "2 Found findOneByPrimaryPublicUserId by primaryPublicUserId=$primaryPublicUserId => user=$user <br>";
        }
        if (!$user) {
            $user = $this->em->getRepository(User::class)->findOneByEmailCanonical($emailCanonical);
        }
        if (!$user) {
            $users = $this->em->getRepository(User::class)->findUserByUserInfoEmail($emailCanonical);
            if (count($users) > 0) {
                $user = $users[0];
            }
        }
        if (!$user) {
            //Check if username is email
            $user = $userSecUtil->findUserByUsernameAsEmail($usernameCanonical);
        }
        if( !$user ) {
            $user = $userSecUtil->getUserByUserstr($usernameCanonical);
        }

        if (!$user) {
            exit('Create new user='.$usernameCanonical);
            // Create new user
            $user = new User(false);
            $user->setKeytype($userkeytype);
            //$user->setPrimaryPublicUserId($username);
            $user->setPrimaryPublicUserId($emailCanonical);
            $usernameUnique = $user->createUniqueUsername();
            $user->setUsername($usernameUnique);
            $user->setUsernameCanonical($usernameUnique);
            $user->setEmail($emailCanonical);
            $user->setEmailCanonical($emailCanonical);
            $user->setFirstName($firstName);
            $user->setLastName($lastName);
            $user->setMiddleName($middleName);
            $user->setDisplayName($displayName);
            $user->setPassword("");
            $user->setCreatedby('hubimport');
            $user->setLocked(true);

            // Employment status
            $employmentStatus = new EmploymentStatus($systemUser);
            $employmentStatus->setEmploymentType($employmentType);
            $user->addEmploymentStatus($employmentStatus);
        } else {
            echo 'Found user='.$user."<br>";
            //exit('Found usernameCanonical='.$usernameCanonical.', $primaryPublicUserId='.$primaryPublicUserId);
        }

        // Create Fellowship Application
        $fellowshipApplication = new FellowshipApplication($systemUser);
        $fellowshipApplication->setAppStatus($activeStatus);
        $fellowshipApplication->setGoogleFormId($googleFormId);
        $fellowshipApplication->setRemoteId($originalAppId);
        //exit("after set originalAppId=$originalAppId");
        $user->addFellowshipApplication($fellowshipApplication);

        // Set timestamp
        if ($timestamp) {
            $fellowshipApplication->setTimestamp($this->transformDatestrToDate($timestamp));
        }

        //TODO: use hash to get fellowshipType object
        // Fellowship Type
        $fellowshipType = $this->getValueByHeaderName('fellowshipType', $rowData, $headers);
        if ($fellowshipType) {
            $fellowshipType = trim((string)$fellowshipType);
            $fellowshipType = $fellappImportPopulateUtil->capitalizeIfNotAllCapital($fellowshipType);
            $transformer = new GenericTreeTransformer($this->em, $systemUser, 'FellowshipSubspecialty');
            $fellowshipTypeEntity = $transformer->reverseTransform($fellowshipType);
            $fellowshipApplication->setFellowshipSubspecialty($fellowshipTypeEntity);
        }

        // Institution
        $instPathologyFellowshipProgram = $userSecUtil->getSiteSettingParameter('localInstitutionFellApp', $this->container->getParameter('fellapp.sitename'));
        if ($instPathologyFellowshipProgram) {
            $fellowshipApplication->setInstitution($instPathologyFellowshipProgram);
        }

        // Training Period
        $trainingPeriodStart = $this->getValueByHeaderName('trainingPeriodStart', $rowData, $headers);
        $trainingPeriodEnd = $this->getValueByHeaderName('trainingPeriodEnd', $rowData, $headers);
        $fellowshipApplication->setStartDate($this->transformDatestrToDate($trainingPeriodStart));
        $fellowshipApplication->setEndDate($this->transformDatestrToDate($trainingPeriodEnd));

        // Document URLs (will need to be downloaded separately - just storing URLs for now)
        $uploadedPhotoUrl = $this->getValueByHeaderName('uploadedPhotoUrl', $rowData, $headers);
        $uploadedCVUrl = $this->getValueByHeaderName('uploadedCVUrl', $rowData, $headers);
        $uploadedCoverLetterUrl = $this->getValueByHeaderName('uploadedCoverLetterUrl', $rowData, $headers);

        // Present Address
        $presentLocationType = $this->em->getRepository(LocationTypeList::class)->findOneByName("Present Address");
        $presentLocation = new Location($systemUser);
        $presentLocation->setName('Fellowship Applicant Present Address');
        $presentLocation->addLocationType($presentLocationType);
        $geoLocation = $this->createGeoLocation($this->em, $systemUser, 'presentAddress', $rowData, $headers);
        if ($geoLocation) {
            $presentLocation->setGeoLocation($geoLocation);
        }
        $user->addLocation($presentLocation);
        $fellowshipApplication->addLocation($presentLocation);

        // Phone numbers on present address
        $telephoneHome = $this->getValueByHeaderName('telephoneHome', $rowData, $headers);
        $telephoneMobile = $this->getValueByHeaderName('telephoneMobile', $rowData, $headers);
        $telephoneFax = $this->getValueByHeaderName('telephoneFax', $rowData, $headers);
        $presentLocation->setPhone($telephoneHome . "");
        $presentLocation->setMobile($telephoneMobile . "");
        $presentLocation->setFax($telephoneFax . "");

        // Permanent Address
        $permanentLocationType = $this->em->getRepository(LocationTypeList::class)->findOneByName("Permanent Address");
        $permanentLocation = new Location($systemUser);
        $permanentLocation->setName('Fellowship Applicant Permanent Address');
        $permanentLocation->addLocationType($permanentLocationType);
        $geoLocation = $this->createGeoLocation($this->em, $systemUser, 'permanentAddress', $rowData, $headers);
        if ($geoLocation) {
            $permanentLocation->setGeoLocation($geoLocation);
        }
        $user->addLocation($permanentLocation);
        $fellowshipApplication->addLocation($permanentLocation);

        // Work Phone
        $telephoneWork = $this->getValueByHeaderName('telephoneWork', $rowData, $headers);
        if ($telephoneWork) {
            $workLocationType = $this->em->getRepository(LocationTypeList::class)->findOneByName("Work Address");
            $workLocation = new Location($systemUser);
            $workLocation->setName('Fellowship Applicant Work Address');
            $workLocation->addLocationType($workLocationType);
            $workLocation->setPhone($telephoneWork . "");
            $user->addLocation($workLocation);
            $fellowshipApplication->addLocation($workLocation);
        }

        // Citizenship
        $citizenship = new Citizenship($systemUser);
        $fellowshipApplication->addCitizenship($citizenship);
        $visaStatus = $this->getValueByHeaderName('visaStatus', $rowData, $headers);
        $citizenshipCountry = $this->getValueByHeaderName('citizenshipCountry', $rowData, $headers);
        $citizenship->setVisa($visaStatus);
        if ($citizenshipCountry) {
            $citizenshipCountry = trim((string)$citizenshipCountry);
            $transformer = new GenericTreeTransformer($this->em, $systemUser, 'Countries');
            $citizenshipCountryEntity = $transformer->reverseTransform($citizenshipCountry);
            $citizenship->setCountry($citizenshipCountryEntity);
        }

        // Date of Birth
        $dateOfBirth = $this->getValueByHeaderName('dateOfBirth', $rowData, $headers);
        if ($dateOfBirth) {
            $fellowshipApplication->getUser()->getCredentials()->setDob($this->transformDatestrToDate($dateOfBirth));
        }

        // Trainings
        $this->createFellAppTraining($this->em, $fellowshipApplication, $systemUser, "undergraduateSchool", $rowData, $headers, 1);
        $this->createFellAppTraining($this->em, $fellowshipApplication, $systemUser, "graduateSchool", $rowData, $headers, 2);
        $this->createFellAppTraining($this->em, $fellowshipApplication, $systemUser, "medicalSchool", $rowData, $headers, 3);
        $this->createFellAppTraining($this->em, $fellowshipApplication, $systemUser, "residency", $rowData, $headers, 4);
        $this->createFellAppTraining($this->em, $fellowshipApplication, $systemUser, "gme1", $rowData, $headers, 5);
        $this->createFellAppTraining($this->em, $fellowshipApplication, $systemUser, "gme2", $rowData, $headers, 6);
        $this->createFellAppTraining($this->em, $fellowshipApplication, $systemUser, "otherExperience1", $rowData, $headers, 7);
        $this->createFellAppTraining($this->em, $fellowshipApplication, $systemUser, "otherExperience2", $rowData, $headers, 8);
        $this->createFellAppTraining($this->em, $fellowshipApplication, $systemUser, "otherExperience3", $rowData, $headers, 9);

        // Examination
        $examination = new Examination($systemUser);
        $fellowshipApplication->addExamination($examination);
        $examination->setUSMLEStep1DatePassed($this->transformDatestrToDate($this->getValueByHeaderName('USMLEStep1DatePassed', $rowData, $headers)));
        $examination->setUSMLEStep1Score($this->getValueByHeaderName('USMLEStep1Score', $rowData, $headers));
        $examination->setUSMLEStep1Percentile($this->getValueByHeaderName('USMLEStep1Percentile', $rowData, $headers));
        $examination->setUSMLEStep2CKDatePassed($this->transformDatestrToDate($this->getValueByHeaderName('USMLEStep2CKDatePassed', $rowData, $headers)));
        $examination->setUSMLEStep2CKScore($this->getValueByHeaderName('USMLEStep2CKScore', $rowData, $headers));
        $examination->setUSMLEStep2CKPercentile($this->getValueByHeaderName('USMLEStep2CKPercentile', $rowData, $headers));
        $examination->setUSMLEStep2CSDatePassed($this->transformDatestrToDate($this->getValueByHeaderName('USMLEStep2CSDatePassed', $rowData, $headers)));
        $examination->setUSMLEStep2CSScore($this->getValueByHeaderName('USMLEStep2CSScore', $rowData, $headers));
        $examination->setUSMLEStep2CSPercentile($this->getValueByHeaderName('USMLEStep2CSPercentile', $rowData, $headers));
        $examination->setUSMLEStep3DatePassed($this->transformDatestrToDate($this->getValueByHeaderName('USMLEStep3DatePassed', $rowData, $headers)));
        $examination->setUSMLEStep3Score($this->getValueByHeaderName('USMLEStep3Score', $rowData, $headers));
        $examination->setUSMLEStep3Percentile($this->getValueByHeaderName('USMLEStep3Percentile', $rowData, $headers));
        
        $ECFMGCertificate = $this->getValueByHeaderName('ECFMGCertificate', $rowData, $headers);
        $examination->setECFMGCertificate($ECFMGCertificate == 'Yes');
        $examination->setECFMGCertificateNumber($this->getValueByHeaderName('ECFMGCertificateNumber', $rowData, $headers));
        $examination->setECFMGCertificateDate($this->transformDatestrToDate($this->getValueByHeaderName('ECFMGCertificateDate', $rowData, $headers)));
        
        $examination->setCOMLEXLevel1Score($this->getValueByHeaderName('COMLEXLevel1Score', $rowData, $headers));
        $examination->setCOMLEXLevel1Percentile($this->getValueByHeaderName('COMLEXLevel1Percentile', $rowData, $headers));
        $examination->setCOMLEXLevel1DatePassed($this->transformDatestrToDate($this->getValueByHeaderName('COMLEXLevel1DatePassed', $rowData, $headers)));
        $examination->setCOMLEXLevel2Score($this->getValueByHeaderName('COMLEXLevel2Score', $rowData, $headers));
        $examination->setCOMLEXLevel2Percentile($this->getValueByHeaderName('COMLEXLevel2Percentile', $rowData, $headers));
        $examination->setCOMLEXLevel2DatePassed($this->transformDatestrToDate($this->getValueByHeaderName('COMLEXLevel2DatePassed', $rowData, $headers)));
        $examination->setCOMLEXLevel3Score($this->getValueByHeaderName('COMLEXLevel3Score', $rowData, $headers));
        $examination->setCOMLEXLevel3Percentile($this->getValueByHeaderName('COMLEXLevel3Percentile', $rowData, $headers));
        $examination->setCOMLEXLevel3DatePassed($this->transformDatestrToDate($this->getValueByHeaderName('COMLEXLevel3DatePassed', $rowData, $headers)));

        // Medical Licenses
        $this->createFellAppMedicalLicense($this->em, $fellowshipApplication, $systemUser, "medicalLicensure1", $rowData, $headers);
        $this->createFellAppMedicalLicense($this->em, $fellowshipApplication, $systemUser, "medicalLicensure2", $rowData, $headers);

        // Suspended Licensure and Legal Suit
        $suspendedLicensure = $this->getValueByHeaderName('suspendedLicensure', $rowData, $headers);
        $legalSuit = $this->getValueByHeaderName('legalSuit', $rowData, $headers);
        $fellowshipApplication->setReprimand($suspendedLicensure);
        $fellowshipApplication->setLawsuit($legalSuit);

        // Board Certifications
        $this->createFellAppBoardCertification($this->em, $fellowshipApplication, $systemUser, "boardCertification1", $rowData, $headers);
        $this->createFellAppBoardCertification($this->em, $fellowshipApplication, $systemUser, "boardCertification2", $rowData, $headers);
        $this->createFellAppBoardCertification($this->em, $fellowshipApplication, $systemUser, "boardCertification3", $rowData, $headers);

        // References
        $ref1 = $this->createFellAppReference($this->em, $systemUser, 'recommendation1', $rowData, $headers);
        if ($ref1) {
            $fellowshipApplication->addReference($ref1);
        }
        $ref2 = $this->createFellAppReference($this->em, $systemUser, 'recommendation2', $rowData, $headers);
        if ($ref2) {
            $fellowshipApplication->addReference($ref2);
        }
        $ref3 = $this->createFellAppReference($this->em, $systemUser, 'recommendation3', $rowData, $headers);
        if ($ref3) {
            $fellowshipApplication->addReference($ref3);
        }
        $ref4 = $this->createFellAppReference($this->em, $systemUser, 'recommendation4', $rowData, $headers);
        if ($ref4) {
            $fellowshipApplication->addReference($ref4);
        }

        // Honors, Publications, Memberships
        $fellowshipApplication->setHonors($this->getValueByHeaderName('honors', $rowData, $headers));
        $fellowshipApplication->setPublications($this->getValueByHeaderName('publications', $rowData, $headers));
        $fellowshipApplication->setMemberships($this->getValueByHeaderName('memberships', $rowData, $headers));

        // Signature
        $signatureName = $this->getValueByHeaderName('signatureName', $rowData, $headers);
        $signatureDate = $this->getValueByHeaderName('signatureDate', $rowData, $headers);
        $fellowshipApplication->setSignatureName($signatureName);
        $fellowshipApplication->setSignatureDate($this->transformDatestrToDate($signatureDate));

        if(0) {
            dump($fellowshipApplication);
            exit('Created fellowship application: ' . $fellowshipApplication->getId() .
                ', $googleFormId=' . $googleFormId .
                ', fellowshipSubspecialty=' . $fellowshipApplication->getFellowshipSubspecialty() .
                ', globalFellowshipSpecialty=' . $fellowshipApplication->getGlobalFellowshipSpecialty() .
                ',<br> applicant=' . $displayName .
                ', primaryPublicUserId=' . $fellowshipApplication->getUser()->getPrimaryPublicUserId()
            );
        }

        // Persist to database
        //The FellowshipApplication is added to the User via
        // $user->addFellowshipApplication($fellowshipApplication),
        // so when the User is persisted, the application cascades to the database.
        $this->em->persist($user);
        $this->em->flush();

        $logger->notice('Created fellowship application: ' . $fellowshipApplication->getId() . ' for applicant: ' . $displayName);

        return $fellowshipApplication;
    }




    //NOT USED
    public function xlsxFileParser( $xlsxFile ) {
        $logger = $this->container->get('logger');
        $fellappImportPopulateUtil = $this->container->get('fellapp_importpopulate_util');
        $userSecUtil = $this->container->get('user_security_utility');

        $systemUser = $userSecUtil->findSystemUser();

        $employmentType = $this->em->getRepository(EmploymentType::class)->findOneByName("Pathology Fellowship Applicant");
        if( !$employmentType ) {
            throw new EntityNotFoundException('Unable to find entity by name='."Pathology Fellowship Applicant");
        }
        $presentLocationType = $this->em->getRepository(LocationTypeList::class)->findOneByName("Present Address");
        if( !$presentLocationType ) {
            throw new EntityNotFoundException('Unable to find entity by name='."Present Address");
        }
        $permanentLocationType = $this->em->getRepository(LocationTypeList::class)->findOneByName("Permanent Address");
        if( !$permanentLocationType ) {
            throw new EntityNotFoundException('Unable to find entity by name='."Permanent Address");
        }
        $workLocationType = $this->em->getRepository(LocationTypeList::class)->findOneByName("Work Address");
        if( !$workLocationType ) {
            throw new EntityNotFoundException('Unable to find entity by name='."Work Address");
        }
        $activeStatus = $this->em->getRepository(FellAppStatus::class)->findOneByName("active");
        if( !$activeStatus ) {
            throw new EntityNotFoundException('Unable to find entity by name='."active");
        }

        // Load spreadsheet
        $reader = new XlsxReader();
        $spreadsheet = $reader->load($xlsxFile);

        // Remove temp file
        //unlink($xlsxFile);

        // Convert to array
        $rows = $spreadsheet->getActiveSheet()->toArray();
        // Dump or loop
        //dump($rows);

        $header = $rows[0];
        $headerLen = count($header);
        echo "header count=".$headerLen."<br>";
        array_shift($rows);   // removes row 0 - header

        foreach($rows as $row) {
            //dump($row);

            $googleFormId = $this->getValueByHeaderName('ID',$row,$header);
            //echo "ID value=$googleFormId <br>";
            if (!$googleFormId) {
                continue; //skip this fell application, because googleFormId does not exists
            }

            $fellowshipApplicationDb = $this->em->getRepository(FellowshipApplication::class)->findOneByGoogleFormId($googleFormId);
            if( $fellowshipApplicationDb ) {
                //$logger->notice('Skip this fell application, because it already exists in DB. googleFormId='.$googleFormId);
                continue; //skip this fell application, because it already exists in DB
            }

            //Failed to import a received fellowship application - will automatically attempt to re-import in X hours
            $subjectError = "Failed to import a received fellowship application - will automatically attempt to re-import (ID=$googleFormId)";

            //parseFields_TEST();

            echo "#### $googleFormId #### <br>";

            $valRes = $fellappImportPopulateUtil->validateSpreadsheet($row, $header, null, $testing=false);
            if( $valRes !== true ) {
                continue;
            }

            try {
                $originalAppId = $fellappImportPopulateUtil->getValueByHeaderName('originalAppId',$row,$header);
                $instanceId = $fellappImportPopulateUtil->getValueByHeaderName('instanceId',$row,$header);
                $timestamp = $fellappImportPopulateUtil->getValueByHeaderName('timestamp',$row,$header);
                $lastName = $fellappImportPopulateUtil->getValueByHeaderName('lastName',$row,$header);
                $firstName = $fellappImportPopulateUtil->getValueByHeaderName('firstName',$row,$header);
                $email = $fellappImportPopulateUtil->getValueByHeaderName('email', $row, $header);
                //exit('email='.$email);

                $fellowshipApplicationDb = $this->em->getRepository(FellowshipApplication::class)->findOneByGoogleFormId($googleFormId);
                if( $fellowshipApplicationDb ) {
                    //$logger->notice('Skip this fell application, because it already exists in DB. googleFormId='.$googleFormId);
                    continue; //skip this fell application, because it already exists in DB
                }

                $middleName = $fellappImportPopulateUtil->getValueByHeaderName('middleName', $row, $header);

                $lastNameCap = $fellappImportPopulateUtil->capitalizeIfNotAllCapital($lastName);
                $firstNameCap = $fellappImportPopulateUtil->capitalizeIfNotAllCapital($firstName);

                $lastNameCap = preg_replace('/\s+/', '_', $lastNameCap);
                $firstNameCap = preg_replace('/\s+/', '_', $firstNameCap);

                //Last Name + First Name + Email
                $username = $lastNameCap . "_" . $firstNameCap . "_" . $email;

                $displayName = $firstName . " " . $lastName;
                if ($middleName) {
                    $displayName = $firstName . " " . $middleName . " " . $lastName;
                }

                //create logger which must be deleted on successefull creation of application
                $eventAttempt = "Attempt of creating Fellowship Applicant " . $displayName . " with unique Google Applicant ID=" . $googleFormId;

                if( $testing == false ) {
                    $eventLogAttempt = $userSecUtil->createUserEditEvent(
                        $this->container->getParameter('fellapp.sitename'),
                        $eventAttempt,
                        $systemUser,
                        null,
                        null,
                        'Fellowship Application Creation Failed'
                    );
                }

                $user = $fellappImportPopulateUtil->createFellappUser($row,$header);

                //create new Fellowship Applicantion
                $fellowshipApplication = new FellowshipApplication($systemUser);

                $fellowshipApplication->setRemoteId($originalAppId);
                exit("after set originalAppId=$originalAppId");

                $fellowshipApplication->setAppStatus($activeStatus);
                //For HUB server, $googleFormId can be used to store unique application ID submitted via HUB server,
                // maybe in the same format 'dpino_dhs_lacounty_gov_Pino_Devon_2024-12-16_04_56_45'
                //Therefore, we can treat $googleFormId as remote form ID $remoteFormId
                $fellowshipApplication->setGoogleFormId($googleFormId);

                //Upon retreval form, set the retrievalMethod according to the site setting
                //$retrievalMethod = $userSecUtil->getSiteSettingParameter('retrievalMethod',$this->container->getParameter('fellapp.sitename'));
                //$fellowshipApplication->setRetrievalMethod($retrievalMethod);

                $user->addFellowshipApplication($fellowshipApplication);
                //if( $fellowshipApplication && !$user->getFellowshipApplications()->contains($fellowshipApplication) ) {
                //    $user->addFellowshipApplication($fellowshipApplication);
                //}

                //timestamp
                $fellowshipApplication->setTimestamp($this->transformDatestrToDate($this->getValueByHeaderName('timestamp', $row,$header)));

                //fellowshipType
                //TODO: use hash to get fellowshipType object
                $fellowshipType = $this->getValueByHeaderName('fellowshipType',$row,$header);
                if ($fellowshipType) {
                    //$logger->notice("fellowshipType=[".$fellowshipType."]");
                    $fellowshipType = trim((string)$fellowshipType);
                    $fellowshipType = $fellappImportPopulateUtil->capitalizeIfNotAllCapital($fellowshipType);
                    $transformer = new GenericTreeTransformer($this->em, $systemUser, 'FellowshipSubspecialty');
                    $fellowshipTypeEntity = $transformer->reverseTransform($fellowshipType);
                    $fellowshipApplication->setFellowshipSubspecialty($fellowshipTypeEntity);
                }

                //////////////////////// assign local institution from SiteParameters ////////////////////////
                //$instPathologyFellowshipProgram = null;
                //$localInstitutionFellApp = $userSecUtil->getSiteSettingParameter('localInstitutionFellApp');
                $instPathologyFellowshipProgram = $userSecUtil->getSiteSettingParameter(
                    'localInstitutionFellApp',
                    $this->container->getParameter('fellapp.sitename')
                );

                if( $instPathologyFellowshipProgram ) {
                    $fellowshipApplication->setInstitution($instPathologyFellowshipProgram);
                } else {
                    $logger->warning('Local institution for import fellowship application is not set or invalid; instPathologyFellowshipProgram='.$instPathologyFellowshipProgram);
                }
                //////////////////////// EOF assign local institution from SiteParameters ////////////////////////



            } catch( \Doctrine\DBAL\DBALException $e ) {
                $event = "Error creating fellowship applicant with unique Google Applicant ID=".$googleFormId."; Exception=".$e->getMessage();
                //$emailUtil->sendEmail( $emails, $subjectError, $event );
                $this->sendEmailToSystemEmail($subjectError, $event);

                //logger
                $logger->error($event);

                $userUtil = $this->container->get('user_utility');
                if( $userUtil->getSession() ) {
                    $userUtil->getSession()->getFlashBag()->add(
                        'warning',
                        $event
                    );
                }
            } //try/catch



        }
        //die;
    }

    public function getValueByHeaderName( $keyName, $row, $header ) {
        $res = null;
        if( !$header ) {
            return $res;
        }
        $key = array_search($keyName, $header);
        if( $key === false ) {
            //echo "key is false !!!!!!!!!!<br>";
            return $res;
        }
        if( array_key_exists($key, $row) ) {
            $res = $row[$key];
        }
        //echo "$keyName=$res <br>";
        return $res;
    }





    /////////////// populate methods: create fellapp from a spreadsheet ($document) /////////////////
    public function populateSpreadsheet( $document, $datafile=null, $deleteSourceRow=false, $testing=false ) {

        //echo "inputFileName=".$inputFileName."<br>";
        $logger = $this->container->get('logger');
        $userSecUtil = $this->container->get('user_security_utility');
        $userUtil = $this->container->get('user_utility');
        $googlesheetmanagement = $this->container->get('fellapp_googlesheetmanagement');
        $fellappRecLetterUtil = $this->container->get('fellapp_rec_letter_util');

        $environment = $userSecUtil->getSiteSettingParameter('environment');

        ini_set('max_execution_time', 3000); //30000 seconds = 50 minutes
        //ini_set('memory_limit', '512M');

        $service = $googlesheetmanagement->getGoogleService();
        if( !$service ) {
            $event = "Google API service failed!";
            $logger->error($event);
            $this->sendEmailToSystemEmail($event, $event);
            $logger->error($event. " while processing ".$document->getServerPath());
            return false;
        }

        $inputFileName = $document->getServerPath();    //'Uploaded/fellapp/Spreadsheets/Pathology Fellowships Application Form (Responses).xlsx';
        $logger->notice("Population a single application sheet (document ID=".$document->getId().") with filename=".$inputFileName);

        //if ruuning from cron path must be: $path = getcwd() . "/web";
        //$inputFileName = $path . "/" . $inputFileName;
        //$inputFileName = realpath($this->container->get('kernel')->getRootDir() . "/../public/" . $inputFileName);
        $inputFileName = $this->container->get('kernel')->getProjectDir() . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . $inputFileName;
        //echo "inputFileName=".$inputFileName."<br>";
        if( !file_exists($inputFileName) ) {
            $logger->error("Source sheet does not exists with filename=".$inputFileName);
            return false;
        }

        ////////// process $inputFileName ///////////
        try {
            //$inputFileNameOrig = NULL;
            //inputFileName=/opt/order-lab/orderflex/public/Uploaded/fellapp/Spreadsheets/1648736219ID1-L_TCY1vrhXyl4KBEZ_x7g-iC_CoKQbcjnvdjgdVR-o.edu_First_Lastname_2021-05-23_20_21_18
            $extension = pathinfo($inputFileName,PATHINFO_EXTENSION);
            //echo "extension=[".$extension."]<br>";
            //TODO: why '/srv/order-lab/orderflex/public/Uploaded/fellapp/Spreadsheets/1657771205ID1Kd9HLl0fymlfO0UlICArHeB4xAHHxvJKShiTReHFx-Q'
            // cannot read by PhpSpreadsheet?
            //$forceCreateCopy = true;
            $forceCreateCopy = false;
            if( $forceCreateCopy || !$extension || ($extension && strlen($extension) > 9) ) {
                //$inputFileType = 'Xlsx'; //'Csv'; //'Xlsx';

                $inputFileNameNew = $this->createTempSpreadsheetCopy($inputFileName,$forceCreateCopy);
                if( !$inputFileNameNew ) {
                    $errorSubject = "Can not create temp file for the source spreadsheet";
                    $errorEvent = $errorSubject . ". Filename=" .
                        $inputFileName . ", extension=" . $extension .
                        ", documentId=" . $document->getId();
                    //exit($errorEvent); //testing
                    $logger->error($errorEvent);
                    $this->sendEmailToSystemEmail($errorSubject, $errorEvent);
                    return false;
                    //exit('$inputFileNameNew is NULL');
                }

                $inputFileType = \PhpOffice\PhpSpreadsheet\IOFactory::identify($inputFileNameNew); //Google spreadsheet: identify $inputFileType='Csv'
                $objReader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader($inputFileType);
                $objPHPExcel = $objReader->load($inputFileNameNew);

                //remove temp file $inputFileNameNew
                unlink($inputFileNameNew);

            } else {
                $logger->warning("Before identify input file type: inputFileName=[".$inputFileName."]");
                //IOFactory::identify filename='1657771205ID1Kd9HLl0fymlfO0UlICArHeB4xAHHxvJKShiTReHFx-Q':
                //Error: Unable to identify a reader for this file with code0
                //$inputFileType = \PhpOffice\PhpSpreadsheet\IOFactory::identify($inputFileName); //Google spreadsheet: identify $inputFileType='Csv'
                ////$inputFileType = 'Csv';
                //$objReader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader($inputFileType);
                
                $objReader = \PhpOffice\PhpSpreadsheet\IOFactory::createReaderForFile($inputFileName);
                $objPHPExcel = $objReader->load($inputFileName);
            }
        } catch(\Exception $e) {
            $event = 'Error loading file "'.pathinfo($inputFileName,PATHINFO_BASENAME).'": '.$e->getMessage();
            $logger->error($event);
            $this->sendEmailToSystemEmail($event, $event);
            throw new IOException($event);
        }

        //$logger->notice("Successfully obtained sheet with filename=".$inputFileName);

        //$uploadPath = $this->uploadDir.'/FellowshipApplicantUploads';
        $applicantsUploadPathFellApp = $userSecUtil->getSiteSettingParameter(
            'applicantsUploadPathFellApp',
            $this->container->getParameter('fellapp.sitename')
        );
        if( !$applicantsUploadPathFellApp ) {
            $applicantsUploadPathFellApp = "FellowshipApplicantUploads";
            $logger->warning('applicantsUploadPathFellApp is not defined in Fellowship Site Parameters. Use default "'.
                $applicantsUploadPathFellApp.'" folder.');
        }
        $uploadPath = $this->uploadDir.'/'.$applicantsUploadPathFellApp;

        $em = $this->em;
        $default_time_zone = $this->container->getParameter('default_time_zone');
        $emailUtil = $this->container->get('user_mailer_utility');

        $userkeytype = $userSecUtil->getUsernameType('local-user');
        if( !$userkeytype ) {
            throw new EntityNotFoundException('Unable to find local user keytype');
        }

        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:EmploymentType'] by [EmploymentType::class]
        $employmentType = $em->getRepository(EmploymentType::class)->findOneByName("Pathology Fellowship Applicant");
        if( !$employmentType ) {
            throw new EntityNotFoundException('Unable to find entity by name='."Pathology Fellowship Applicant");
        }
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:LocationTypeList'] by [LocationTypeList::class]
        $presentLocationType = $em->getRepository(LocationTypeList::class)->findOneByName("Present Address");
        if( !$presentLocationType ) {
            throw new EntityNotFoundException('Unable to find entity by name='."Present Address");
        }
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:LocationTypeList'] by [LocationTypeList::class]
        $permanentLocationType = $em->getRepository(LocationTypeList::class)->findOneByName("Permanent Address");
        if( !$permanentLocationType ) {
            throw new EntityNotFoundException('Unable to find entity by name='."Permanent Address");
        }
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:LocationTypeList'] by [LocationTypeList::class]
        $workLocationType = $em->getRepository(LocationTypeList::class)->findOneByName("Work Address");
        if( !$workLocationType ) {
            throw new EntityNotFoundException('Unable to find entity by name='."Work Address");
        }

        //process.py script: replaced namespace by ::class: ['AppFellAppBundle:FellAppStatus'] by [FellAppStatus::class]
        $activeStatus = $em->getRepository(FellAppStatus::class)->findOneByName("active");
        if( !$activeStatus ) {
            throw new EntityNotFoundException('Unable to find entity by name='."active");
        }


        ////////////// add system user /////////////////
        $systemUser = $userSecUtil->findSystemUser();
        ////////////// end of add system user /////////////////

        $sheet = $objPHPExcel->getSheet(0);
        $highestRow = $sheet->getHighestRow();
        $highestColumn = $sheet->getHighestColumn();
        //echo "rows=$highestRow columns=$highestColumn <br>";
        //$logger->notice("rows=$highestRow columns=$highestColumn");

        $headers = $rowData = $sheet->rangeToArray('A' . 1 . ':' . $highestColumn . 1,
            NULL,
            TRUE,
            FALSE);
        //print_r($headers);

        $populatedFellowshipApplications = new ArrayCollection();

        ////////////////// Potential ERROR //////////////////
        //$useWarning = false;
        $useWarning = true;
        if( $useWarning ) {
            if (!$highestRow || $highestRow < 3) {

                $createDateStr = NULL;
                $createDate = $document->getCreateDate();
                if ($createDate) {
                    $createDateStr = $createDate->format('d-m-Y H:i:s');
                }

                //Create error notification email [ORDER]
                $subject = "Error: Invalid number of rows in Fellowship Application Spreadsheet";
                $body = "Invalid number of rows in Fellowship Application Spreadsheet." .
                    " The applicant data is located in row number 3. The applicant data might be missing." .
                    " Number of rows: $highestRow." . ", document ID=" . $document->getId() .
                    ", title=" . $document->getTitle() .
                    ", originalName=" . $document->getOriginalname() .
                    ", createDate=" . $createDateStr .
                    ", size=" . $document->getSize() .
                    ", filename=" . $inputFileName;

                $logger->error($body);

                $userSecUtil = $this->container->get('user_security_utility');
                $systemUser = $userSecUtil->findSystemUser();

                $userSecUtil->sendEmailToSystemEmail($subject, $body);

                //Send email to admins
                $emails = $userSecUtil->getUserEmailsByRole($this->container->getParameter('fellapp.sitename'), "Platform Administrator");
                $ccs = $userSecUtil->getUserEmailsByRole($this->container->getParameter('fellapp.sitename'), "Administrator");
                if (!$emails) {
                    $emails = $ccs;
                    $ccs = null;
                }
                //$emails = $ccs = 'oli2002@med.cornell.edu'; //testing
                $emailUtil = $this->container->get('user_mailer_utility');
                $emailUtil->sendEmail($emails, $subject, $body, $ccs);

                if ($testing == false) {
                    $userSecUtil->createUserEditEvent($this->container->getParameter('fellapp.sitename'), $body, $systemUser, null, null, 'Fellowship Application Creation Failed');
                }

                ///////////// Delete erroneous spreadsheet $datafile and associated document /////////////
                $removeErrorFile = true;
                $removeErrorFile = false;
                if( $removeErrorFile ) {
                    $datafileId = NULL;
                    if ($datafile) {
                        $datafileId = $datafile->getId();
                    }
                    $logger->error("Removing erroneous spreadsheet ($inputFileName): datafileId=" . $datafileId . " and associated documentId=" . $document->getId());
                    unlink($inputFileName);
                    $em->remove($document);
                    if ($datafile) {
                        $em->remove($datafile);
                    }

                    if ($testing == false) {
                        $em->flush();
                    }
                }

                //testing
                throw new IOException("Testing: ".$subject);

                return false;
            }
        }
        ////////////////// EOF Potential ERROR //////////////////

        //for each user in excel
        for( $row = 3; $row <= $highestRow; $row++ ){

            //  Read a row of data into an array
            $rowData = $sheet->rangeToArray('A' . $row . ':' . $highestColumn . $row,
                NULL,
                TRUE,
                FALSE);

            //dump($rowData);
            //exit("EXIT: document ID=".$document->getId().", filename=".$inputFileName.", highestRow=$highestRow");

            //$googleFormId = $rowData[0][0];
            $googleFormId = $this->getValueByHeaderName('ID',$rowData,$headers);
            $email = $this->getValueByHeaderName('email', $rowData, $headers);
            $lastName = $this->getValueByHeaderName('lastName', $rowData, $headers);
            $firstName = $this->getValueByHeaderName('firstName', $rowData, $headers);

            if( !$googleFormId ) {
                continue; //skip this fell application, because googleFormId does not exists
            }

            //ID=".$googleFormId
            //subject for error email
            //Failed to import a received fellowship application - will automatically attempt to re-import in X hours
            $subjectError = "Failed to import a received fellowship application - will automatically attempt to re-import (ID=$googleFormId)";

            ////////////////// validate spreadsheet /////////////////////////
            $errorMsgArr = array();
            $fellowshipType = $this->getValueByHeaderName('fellowshipType', $rowData, $headers);
            if( !$fellowshipType ) {
                $errorMsgArr[] = "Fellowship Type is null";
            }
            $ref1 = $this->createFellAppReference($em,$systemUser,'recommendation1',$rowData,$headers,true);
            if( !$ref1 ) {
                $errorMsgArr[] = "Reference1 is null";
            }
            $ref2 = $this->createFellAppReference($em,$systemUser,'recommendation2',$rowData,$headers,true);
            if( !$ref2 ) {
                $errorMsgArr[] = "Reference2 is null";
            }
            $ref3 = $this->createFellAppReference($em,$systemUser,'recommendation3',$rowData,$headers,true);
            if( !$ref3 ) {
                $errorMsgArr[] = "Reference3 is null";
            }

            if( !$lastName ) {
                $errorMsgArr[] = "Applicant last name is null";
            }
            if( !$firstName ) {
                $errorMsgArr[] = "Applicant first name is null";
            }

            if( !$email ) {
                $errorMsgArr[] = "Applicant email is null";
            }

            $signatureName = $this->getValueByHeaderName('signatureName',$rowData,$headers);
            if( !$signatureName ) {
                $errorMsgArr[] = "Signature is null";
            }
            $signatureDate = $this->getValueByHeaderName('signatureDate',$rowData,$headers);
            if( !$signatureDate ) {
                $errorMsgArr[] = "Signature Date is null";
            }
            $trainingPeriodStart = $this->getValueByHeaderName('trainingPeriodStart',$rowData,$headers);
            if( !$trainingPeriodStart ) {
                $errorMsgArr[] = "Start Date is null";
            }
            $trainingPeriodEnd = $this->getValueByHeaderName('trainingPeriodEnd',$rowData,$headers);
            if( !$trainingPeriodEnd ) {
                $errorMsgArr[] = "End Date is null";
            }


            if( $environment == 'live' ) {
                //getFellowshipSubspecialty
                //if( !$fellowshipApplication->getFellowshipSubspecialty() ) { //getSignatureName() - not reliable - some applicants managed to submit the form without signature
                if( $errorMsgArr && count($errorMsgArr) > 0 ) {

                    //delete erroneous spreadsheet from filesystem and $document from DB
                    if( file_exists($inputFileName) ) {
                        //$logger->error("Source sheet does not exists with filename=".$inputFileName);
                        //remove from DB
                        $em->remove($document);
                        if( $datafile ) {
                            $em->remove($datafile);
                        }

                        if( $testing == false ) {
                            $em->flush();
                        }
                        //delete file
                        unlink($inputFileName); // or die("Couldn't delete erroneous spreadsheet inputFileName=[".$inputFileName."]");
                        $logger->error("Erroneous spreadsheet deleted from server: $inputFileName=".$inputFileName);
                    }

                    $event = "First spreadsheet validation error:".
                        " Empty required fields after trying to populate the Fellowship Application with Google Applicant ID=[" . $googleFormId . "]" .
                        ": " . implode("; ",$errorMsgArr);

                    if( $testing == false ) {
                        $userSecUtil->createUserEditEvent($this->container->getParameter('fellapp.sitename'), $event, $systemUser, null, null, 'Fellowship Application Creation Failed');
                    }

                    $logger->error($event);

                    //send email
                    $sendErrorEmail = true;
                    //$sendErrorEmail = false;
                    if( $sendErrorEmail ) {
                        $userSecUtil = $this->container->get('user_security_utility');
                        $emails = $userSecUtil->getUserEmailsByRole($this->container->getParameter('fellapp.sitename'), "Administrator");
                        $ccs = $userSecUtil->getUserEmailsByRole($this->container->getParameter('fellapp.sitename'), "Platform Administrator");
                        if (!$emails) {
                            $emails = $ccs;
                            $ccs = null;
                        }
                        $emailUtil->sendEmail($emails, $subjectError, $event, $ccs);
                        $this->sendEmailToSystemEmail($subjectError, $event);
                    }

                    continue; //skip this fell application, because getFellowshipSubspecialty is null => something is wrong
                }
            } else {
                $logger->error("Not live server: No deleted erroneous spreadsheet from filesystem and $document from DB");
            }
            ////////////////// EOF validate spreadsheet ////////////////////////


            //exit('exit');

            try {
                $fellowshipApplicationDb = $em->getRepository(FellowshipApplication::class)->findOneByGoogleFormId($googleFormId);
                if( $fellowshipApplicationDb ) {
                    //$logger->notice('Skip this fell application, because it already exists in DB. googleFormId='.$googleFormId);
                    continue; //skip this fell application, because it already exists in DB
                }

                $middleName = $this->getValueByHeaderName('middleName', $rowData, $headers);

                $lastNameCap = $this->capitalizeIfNotAllCapital($lastName);
                $firstNameCap = $this->capitalizeIfNotAllCapital($firstName);

                $lastNameCap = preg_replace('/\s+/', '_', $lastNameCap);
                $firstNameCap = preg_replace('/\s+/', '_', $firstNameCap);

                //Last Name + First Name + Email
                $username = $lastNameCap . "_" . $firstNameCap . "_" . $email;

                $displayName = $firstName . " " . $lastName;
                if ($middleName) {
                    $displayName = $firstName . " " . $middleName . " " . $lastName;
                }

                //create logger which must be deleted on successefull creation of application
                $eventAttempt = "Attempt of creating Fellowship Applicant " . $displayName . " with unique Google Applicant ID=" . $googleFormId;

                if( $testing == false ) {
                    $eventLogAttempt = $userSecUtil->createUserEditEvent($this->container->getParameter('fellapp.sitename'), $eventAttempt, $systemUser, null, null, 'Fellowship Application Creation Failed');
                }

                //check if the user already exists in DB by $googleFormId
                $user = $em->getRepository(User::class)->findOneByPrimaryPublicUserId($username);

                if (!$user) {
                    //create excel user
                    $addobjects = false;
                    $user = new User($addobjects);
                    $user->setKeytype($userkeytype);
                    $user->setPrimaryPublicUserId($username);

                    //set unique username
                    $usernameUnique = $user->createUniqueUsername();
                    $user->setUsername($usernameUnique);
                    $user->setUsernameCanonical($usernameUnique);


                    $user->setEmail($email);
                    $user->setEmailCanonical($email);

                    $user->setFirstName($firstName);
                    $user->setLastName($lastName);
                    $user->setMiddleName($middleName);
                    $user->setDisplayName($displayName);
                    $user->setPassword("");
                    $user->setCreatedby('googleapi');
                    $user->getPreferences()->setTimezone($default_time_zone);
                    $user->setLocked(true);

                    //Pathology Fellowship Applicant in EmploymentStatus
                    $employmentStatus = new EmploymentStatus($systemUser);
                    $employmentStatus->setEmploymentType($employmentType);
                    $user->addEmploymentStatus($employmentStatus);
                }

                //create new Fellowship Applicantion
                $fellowshipApplication = new FellowshipApplication($systemUser);
                //if( !$fellowshipApplication ) {
                //    $fellowshipApplication = new FellowshipApplication($systemUser);
                //}

                $fellowshipApplication->setAppStatus($activeStatus);
                //For HUB server, $googleFormId can be used to store unique application ID submitted via HUB server,
                // maybe in the same format 'dpino_dhs_lacounty_gov_Pino_Devon_2024-12-16_04_56_45'
                //Therefore, we can treat $googleFormId as remote form ID $remoteFormId
                $fellowshipApplication->setGoogleFormId($googleFormId);

                //Upon retreval form, set the retrievalMethod according to the site setting
                //$retrievalMethod = $userSecUtil->getSiteSettingParameter('retrievalMethod',$this->container->getParameter('fellapp.sitename'));
                //$fellowshipApplication->setRetrievalMethod($retrievalMethod);

                $user->addFellowshipApplication($fellowshipApplication);

                //timestamp
                $fellowshipApplication->setTimestamp($this->transformDatestrToDate($this->getValueByHeaderName('timestamp', $rowData, $headers)));

                //fellowshipType
                $fellowshipType = $this->getValueByHeaderName('fellowshipType', $rowData, $headers);
                if ($fellowshipType) {
                    //$logger->notice("fellowshipType=[".$fellowshipType."]");
                    $fellowshipType = trim((string)$fellowshipType);
                    $fellowshipType = $this->capitalizeIfNotAllCapital($fellowshipType);
                    $transformer = new GenericTreeTransformer($em, $systemUser, 'FellowshipSubspecialty');
                    $fellowshipTypeEntity = $transformer->reverseTransform($fellowshipType);
                    $fellowshipApplication->setFellowshipSubspecialty($fellowshipTypeEntity);
                }

                //////////////////////// assign local institution from SiteParameters ////////////////////////
                //$instPathologyFellowshipProgram = null;
                //$localInstitutionFellApp = $userSecUtil->getSiteSettingParameter('localInstitutionFellApp');
                $instPathologyFellowshipProgram = $userSecUtil->getSiteSettingParameter('localInstitutionFellApp',$this->container->getParameter('fellapp.sitename'));
                
                if( $instPathologyFellowshipProgram ) {
                    $fellowshipApplication->setInstitution($instPathologyFellowshipProgram);
                } else {
                    $logger->warning('Local institution for import fellowship application is not set or invalid; instPathologyFellowshipProgram='.$instPathologyFellowshipProgram);
                }
                //////////////////////// EOF assign local institution from SiteParameters ////////////////////////


                //trainingPeriodStart
                $fellowshipApplication->setStartDate($this->transformDatestrToDate($this->getValueByHeaderName('trainingPeriodStart',$rowData,$headers)));

                //trainingPeriodEnd
                $fellowshipApplication->setEndDate($this->transformDatestrToDate($this->getValueByHeaderName('trainingPeriodEnd',$rowData,$headers)));

                //uploadedPhotoUrl
                $uploadedPhotoUrl = $this->getValueByHeaderName('uploadedPhotoUrl',$rowData,$headers);
                $uploadedPhotoId = $this->getFileIdByUrl( $uploadedPhotoUrl );
                if( $uploadedPhotoId ) {
                    $uploadedPhotoDb = $googlesheetmanagement->downloadFileToServer($systemUser, $service, $uploadedPhotoId, 'Fellowship Photo', $uploadPath);
                    if( !$uploadedPhotoDb ) {
                        throw new IOException('Unable to download file to server: uploadedPhotoUrl='.$uploadedPhotoUrl.', fileDB='.$uploadedPhotoDb);
                    }
                    //$user->setAvatar($uploadedPhotoDb); //set this file as Avatar
                    $fellowshipApplication->addAvatar($uploadedPhotoDb);
                }

                //uploadedCVUrl
                $uploadedCVUrl = $this->getValueByHeaderName('uploadedCVUrl',$rowData,$headers);
                $uploadedCVUrlId = $this->getFileIdByUrl( $uploadedCVUrl );
                if( $uploadedCVUrlId ) {
                    $uploadedCVUrlDb = $googlesheetmanagement->downloadFileToServer($systemUser, $service, $uploadedCVUrlId, 'Fellowship CV', $uploadPath);
                    if( !$uploadedCVUrlDb ) {
                        throw new IOException('Unable to download file to server: uploadedCVUrl='.$uploadedCVUrl.', fileDB='.$uploadedCVUrlDb);
                    }
                    $fellowshipApplication->addCv($uploadedCVUrlDb);
                }

                //uploadedCoverLetterUrl
                $uploadedCoverLetterUrl = $this->getValueByHeaderName('uploadedCoverLetterUrl',$rowData,$headers);
                $uploadedCoverLetterUrlId = $this->getFileIdByUrl( $uploadedCoverLetterUrl );
                if( $uploadedCoverLetterUrlId ) {
                    $uploadedCoverLetterUrlDb = $googlesheetmanagement->downloadFileToServer($systemUser, $service, $uploadedCoverLetterUrlId, 'Fellowship Cover Letter', $uploadPath);
                    if( !$uploadedCoverLetterUrlDb ) {
                        throw new IOException('Unable to download file to server: uploadedCoverLetterUrl='.$uploadedCoverLetterUrl.', fileDB='.$uploadedCoverLetterUrlDb);
                    }
                    $fellowshipApplication->addCoverLetter($uploadedCoverLetterUrlDb);
                }

                $examination = new Examination($systemUser);
                //$user->getCredentials()->addExamination($examination);
                $fellowshipApplication->addExamination($examination);
                //uploadedUSMLEScoresUrl
                $uploadedUSMLEScoresUrl = $this->getValueByHeaderName('uploadedUSMLEScoresUrl',$rowData,$headers);
                $uploadedUSMLEScoresUrlId = $this->getFileIdByUrl( $uploadedUSMLEScoresUrl );
                if( $uploadedUSMLEScoresUrlId ) {
                    $uploadedUSMLEScoresUrlDb = $googlesheetmanagement->downloadFileToServer($systemUser, $service, $uploadedUSMLEScoresUrlId, 'Fellowship USMLE Scores', $uploadPath);
                    if( !$uploadedUSMLEScoresUrlDb ) {
                        throw new IOException('Unable to download file to server: uploadedUSMLEScoresUrl='.$uploadedUSMLEScoresUrl.', fileDB='.$uploadedUSMLEScoresUrlDb);
                    }
                    $examination->addScore($uploadedUSMLEScoresUrlDb);
                }

                //presentAddress
                $presentLocation = new Location($systemUser);
                $presentLocation->setName('Fellowship Applicant Present Address');
                $presentLocation->addLocationType($presentLocationType);
                $geoLocation = $this->createGeoLocation($em,$systemUser,'presentAddress',$rowData,$headers);
                if( $geoLocation ) {
                    $presentLocation->setGeoLocation($geoLocation);
                }
                $user->addLocation($presentLocation);
                $fellowshipApplication->addLocation($presentLocation);

                //telephoneHome
                //telephoneMobile
                //telephoneFax
                $presentLocation->setPhone($this->getValueByHeaderName('telephoneHome',$rowData,$headers)."");
                $presentLocation->setMobile($this->getValueByHeaderName('telephoneMobile',$rowData,$headers)."");
                $presentLocation->setFax($this->getValueByHeaderName('telephoneFax',$rowData,$headers)."");

                //permanentAddress
                $permanentLocation = new Location($systemUser);
                $permanentLocation->setName('Fellowship Applicant Permanent Address');
                $permanentLocation->addLocationType($permanentLocationType);
                $geoLocation = $this->createGeoLocation($em,$systemUser,'permanentAddress',$rowData,$headers);
                if( $geoLocation ) {
                    $permanentLocation->setGeoLocation($geoLocation);
                }
                $user->addLocation($permanentLocation);
                $fellowshipApplication->addLocation($permanentLocation);

                //telephoneWork
                $telephoneWork = $this->getValueByHeaderName('telephoneWork',$rowData,$headers);
                if( $telephoneWork ) {
                    $workLocation = new Location($systemUser);
                    $workLocation->setName('Fellowship Applicant Work Address');
                    $workLocation->addLocationType($workLocationType);
                    $workLocation->setPhone($telephoneWork."");
                    $user->addLocation($workLocation);
                    $fellowshipApplication->addLocation($workLocation);
                }


                $citizenship = new Citizenship($systemUser);
                //$user->getCredentials()->addCitizenship($citizenship);
                $fellowshipApplication->addCitizenship($citizenship);
                //visaStatus
                $citizenship->setVisa($this->getValueByHeaderName('visaStatus',$rowData,$headers));
                //citizenshipCountry
                $citizenshipCountry = $this->getValueByHeaderName('citizenshipCountry',$rowData,$headers);
                if( $citizenshipCountry ) {
                    $citizenshipCountry = trim((string)$citizenshipCountry);
                    $transformer = new GenericTreeTransformer($em, $systemUser, 'Countries');
                    $citizenshipCountryEntity = $transformer->reverseTransform($citizenshipCountry);
                    $citizenship->setCountry($citizenshipCountryEntity);
                }

                //DOB: oleg_userdirectorybundle_user_credentials_dob
                $dobDate = $this->transformDatestrToDate($this->getValueByHeaderName('dateOfBirth',$rowData,$headers));
                $fellowshipApplication->getUser()->getCredentials()->setDob($dobDate);

                //undergraduate
                $this->createFellAppTraining($em,$fellowshipApplication,$systemUser,"undergraduateSchool",$rowData,$headers,1);

                //graduate
                $this->createFellAppTraining($em,$fellowshipApplication,$systemUser,"graduateSchool",$rowData,$headers,2);

                //medical
                $this->createFellAppTraining($em,$fellowshipApplication,$systemUser,"medicalSchool",$rowData,$headers,3);

                //residency: residencyStart	residencyEnd	residencyName	residencyArea
                $this->createFellAppTraining($em,$fellowshipApplication,$systemUser,"residency",$rowData,$headers,4);

                //gme1: gme1Start, gme1End, gme1Name, gme1Area => Major
                $this->createFellAppTraining($em,$fellowshipApplication,$systemUser,"gme1",$rowData,$headers,5);

                //gme2: gme2Start, gme2End, gme2Name, gme2Area => Major
                $this->createFellAppTraining($em,$fellowshipApplication,$systemUser,"gme2",$rowData,$headers,6);

                //otherExperience1Start	otherExperience1End	otherExperience1Name=>Major
                $this->createFellAppTraining($em,$fellowshipApplication,$systemUser,"otherExperience1",$rowData,$headers,7);

                //otherExperience2Start	otherExperience2End	otherExperience2Name=>Major
                $this->createFellAppTraining($em,$fellowshipApplication,$systemUser,"otherExperience2",$rowData,$headers,8);

                //otherExperience3Start	otherExperience3End	otherExperience3Name=>Major
                $this->createFellAppTraining($em,$fellowshipApplication,$systemUser,"otherExperience3",$rowData,$headers,9);

                //USMLEStep1DatePassed	USMLEStep1Score
                $examination->setUSMLEStep1DatePassed($this->transformDatestrToDate($this->getValueByHeaderName('USMLEStep1DatePassed',$rowData,$headers)));
                $examination->setUSMLEStep1Score($this->getValueByHeaderName('USMLEStep1Score',$rowData,$headers));
                $examination->setUSMLEStep1Percentile($this->getValueByHeaderName('USMLEStep1Percentile',$rowData,$headers));

                //USMLEStep2CKDatePassed	USMLEStep2CKScore	USMLEStep2CSDatePassed	USMLEStep2CSScore
                $examination->setUSMLEStep2CKDatePassed($this->transformDatestrToDate($this->getValueByHeaderName('USMLEStep2CKDatePassed',$rowData,$headers)));
                $examination->setUSMLEStep2CKScore($this->getValueByHeaderName('USMLEStep2CKScore',$rowData,$headers));
                $examination->setUSMLEStep2CKPercentile($this->getValueByHeaderName('USMLEStep2CKPercentile',$rowData,$headers));
                $examination->setUSMLEStep2CSDatePassed($this->transformDatestrToDate($this->getValueByHeaderName('USMLEStep2CSDatePassed',$rowData,$headers)));
                $examination->setUSMLEStep2CSScore($this->getValueByHeaderName('USMLEStep2CSScore',$rowData,$headers));
                $examination->setUSMLEStep2CSPercentile($this->getValueByHeaderName('USMLEStep2CSPercentile',$rowData,$headers));

                //USMLEStep3DatePassed	USMLEStep3Score
                $examination->setUSMLEStep3DatePassed($this->transformDatestrToDate($this->getValueByHeaderName('USMLEStep3DatePassed',$rowData,$headers)));
                $examination->setUSMLEStep3Score($this->getValueByHeaderName('USMLEStep3Score',$rowData,$headers));
                $examination->setUSMLEStep3Percentile($this->getValueByHeaderName('USMLEStep3Percentile',$rowData,$headers));

                //ECFMGCertificate
                $ECFMGCertificateStr = $this->getValueByHeaderName('ECFMGCertificate',$rowData,$headers);
                $ECFMGCertificate = false;
                if( $ECFMGCertificateStr == 'Yes' ) {
                    $ECFMGCertificate = true;
                }
                $examination->setECFMGCertificate($ECFMGCertificate);

                //ECFMGCertificateNumber	ECFMGCertificateDate
                $examination->setECFMGCertificateNumber($this->getValueByHeaderName('ECFMGCertificateNumber',$rowData,$headers));
                $examination->setECFMGCertificateDate($this->transformDatestrToDate($this->getValueByHeaderName('ECFMGCertificateDate',$rowData,$headers)));

                //COMLEXLevel1DatePassed	COMLEXLevel1Score	COMLEXLevel2DatePassed	COMLEXLevel2Score	COMLEXLevel3DatePassed	COMLEXLevel3Score
                $examination->setCOMLEXLevel1Score($this->getValueByHeaderName('COMLEXLevel1Score',$rowData,$headers));
                $examination->setCOMLEXLevel1Percentile($this->getValueByHeaderName('COMLEXLevel1Percentile',$rowData,$headers));
                $examination->setCOMLEXLevel1DatePassed($this->transformDatestrToDate($this->getValueByHeaderName('COMLEXLevel1DatePassed',$rowData,$headers)));
                $examination->setCOMLEXLevel2Score($this->getValueByHeaderName('COMLEXLevel2Score',$rowData,$headers));
                $examination->setCOMLEXLevel2Percentile($this->getValueByHeaderName('COMLEXLevel2Percentile',$rowData,$headers));
                $examination->setCOMLEXLevel2DatePassed($this->transformDatestrToDate($this->getValueByHeaderName('COMLEXLevel2DatePassed',$rowData,$headers)));
                $examination->setCOMLEXLevel3Score($this->getValueByHeaderName('COMLEXLevel3Score',$rowData,$headers));
                $examination->setCOMLEXLevel3Percentile($this->getValueByHeaderName('COMLEXLevel3Percentile',$rowData,$headers));
                $examination->setCOMLEXLevel3DatePassed($this->transformDatestrToDate($this->getValueByHeaderName('COMLEXLevel3DatePassed',$rowData,$headers)));

                //medicalLicensure1Country	medicalLicensure1State	medicalLicensure1DateIssued	medicalLicensure1Number	medicalLicensure1Active
                $this->createFellAppMedicalLicense($em,$fellowshipApplication,$systemUser,"medicalLicensure1",$rowData,$headers);

                //medicalLicensure2
                $this->createFellAppMedicalLicense($em,$fellowshipApplication,$systemUser,"medicalLicensure2",$rowData,$headers);

                //suspendedLicensure
                $fellowshipApplication->setReprimand($this->getValueByHeaderName('suspendedLicensure',$rowData,$headers));
                //uploadedReprimandExplanationUrl
                $uploadedReprimandExplanationUrl = $this->getValueByHeaderName('uploadedReprimandExplanationUrl',$rowData,$headers);
                $uploadedReprimandExplanationUrlId = $this->getFileIdByUrl( $uploadedReprimandExplanationUrl );
                if( $uploadedReprimandExplanationUrlId ) {
                    $uploadedReprimandExplanationUrlDb = $googlesheetmanagement->downloadFileToServer($systemUser, $service, $uploadedReprimandExplanationUrlId, 'Fellowship Reprimand', $uploadPath);
                    if( !$uploadedReprimandExplanationUrlDb ) {
                        throw new IOException('Unable to download file to server: uploadedReprimandExplanationUrl='.$uploadedReprimandExplanationUrl.', fileID='.$uploadedReprimandExplanationUrlDb->getId());
                    }
                    $fellowshipApplication->addReprimandDocument($uploadedReprimandExplanationUrlDb);
                }

                //legalSuit
                $fellowshipApplication->setLawsuit($this->getValueByHeaderName('legalSuit',$rowData,$headers));
                //uploadedLegalExplanationUrl
                $uploadedLegalExplanationUrl = $this->getValueByHeaderName('uploadedLegalExplanationUrl',$rowData,$headers);
                $uploadedLegalExplanationUrlId = $this->getFileIdByUrl( $uploadedLegalExplanationUrl );
                if( $uploadedLegalExplanationUrlId ) {
                    $uploadedLegalExplanationUrlDb = $googlesheetmanagement->downloadFileToServer($systemUser, $service, $uploadedLegalExplanationUrlId, 'Fellowship Legal Suit', $uploadPath);
                    if( !$uploadedLegalExplanationUrlDb ) {
                        throw new IOException('Unable to download file to server: uploadedLegalExplanationUrl='.$uploadedLegalExplanationUrl.', fileID='.$uploadedLegalExplanationUrlDb->getId());
                    }
                    $fellowshipApplication->addReprimandDocument($uploadedLegalExplanationUrlDb);
                }

                //boardCertification1Board	boardCertification1Area	boardCertification1Date
                $this->createFellAppBoardCertification($em,$fellowshipApplication,$systemUser,"boardCertification1",$rowData,$headers);
                //boardCertification2
                $this->createFellAppBoardCertification($em,$fellowshipApplication,$systemUser,"boardCertification2",$rowData,$headers);
                //boardCertification3
                $this->createFellAppBoardCertification($em,$fellowshipApplication,$systemUser,"boardCertification3",$rowData,$headers);

                //recommendation1Name	recommendation1Title	recommendation1Institution	recommendation1AddressStreet1	recommendation1AddressStreet2	recommendation1AddressCity	recommendation1AddressState	recommendation1AddressZip	recommendation1AddressCountry
                $ref1 = $this->createFellAppReference($em,$systemUser,'recommendation1',$rowData,$headers);
                if( $ref1 ) {
                    $fellowshipApplication->addReference($ref1);
                }
                $ref2 = $this->createFellAppReference($em,$systemUser,'recommendation2',$rowData,$headers);
                if( $ref2 ) {
                    $fellowshipApplication->addReference($ref2);
                }
                $ref3 = $this->createFellAppReference($em,$systemUser,'recommendation3',$rowData,$headers);
                if( $ref3 ) {
                    $fellowshipApplication->addReference($ref3);
                }
                $ref4 = $this->createFellAppReference($em,$systemUser,'recommendation4',$rowData,$headers);
                if( $ref4 ) {
                    $fellowshipApplication->addReference($ref4);
                }

                //honors
                $fellowshipApplication->setHonors($this->getValueByHeaderName('honors',$rowData,$headers));
                //publications
                $fellowshipApplication->setPublications($this->getValueByHeaderName('publications',$rowData,$headers));
                //memberships
                $fellowshipApplication->setMemberships($this->getValueByHeaderName('memberships',$rowData,$headers));

                //signatureName
                $fellowshipApplication->setSignatureName($this->getValueByHeaderName('signatureName',$rowData,$headers));
                //signatureDate
                $signatureDate = $this->transformDatestrToDate($this->getValueByHeaderName('signatureDate',$rowData,$headers));
                $fellowshipApplication->setSignatureDate($signatureDate);

                //////////////////// second validate the application //////////////////////
                $errorMsgArr = array();
                if( !$fellowshipApplication->getFellowshipSubspecialty() ) {
                    $errorMsgArr[] = "Fellowship Type is null";
                }
                if( count($fellowshipApplication->getReferences()) == 0 ) {
                    $errorMsgArr[] = "References are null";
                }
                if( !$displayName ) {
                    $errorMsgArr[] = "Applicant name is null";
                }
                if( !$fellowshipApplication->getSignatureName() ) {
                    $errorMsgArr[] = "Signature is null";
                }
                if( !$fellowshipApplication->getSignatureDate() ) {
                    $errorMsgArr[] = "Signature Date is null";
                }
                if( !$fellowshipApplication->getStartDate() ) {
                    $errorMsgArr[] = "Start Date is null";
                }
                if( !$fellowshipApplication->getEndDate() ) {
                    $errorMsgArr[] = "End Date is null";
                }

                if( $environment == 'live' ) {
                    //This condition (count($errorMsgArr) > 0) should never happen theoretically, because the first validation should catch the erroneous spreadsheet
                    //if( !$fellowshipApplication->getFellowshipSubspecialty() ) { //getSignatureName() - not reliable - some applicants managed to submit the form without signature
                    if ($errorMsgArr && count($errorMsgArr) > 0) {

                        //delete erroneous spreadsheet from filesystem and $document from DB
                        if (file_exists($inputFileName)) {
                            //$logger->error("Source sheet does not exists with filename=".$inputFileName);
                            //remove from DB
                            $em->remove($document);
                            if ($datafile) {
                                $em->remove($datafile);
                            }
                            //$em->flush($document);

                            if ($testing == false) {
                                $em->flush();
                            }
                            //delete file
                            unlink($inputFileName); // or die("Couldn't delete erroneous spreadsheet inputFileName=[".$inputFileName."]");
                            $logger->error("Erroneous spreadsheet deleted from server: inputFileName=" . $inputFileName);
                        }

                        $event = "Second spreadsheet validation error:" .
                            " (Applicant=[" . $displayName . "], Application ID=[" . $fellowshipApplication->getId() . "])" .
                            " Empty required fields after trying to populate the Fellowship Application with Google Applicant ID=[" . $googleFormId . "]" .
                            ": " . implode("; ", $errorMsgArr);

                        if ($testing == false) {
                            $userSecUtil->createUserEditEvent($this->container->getParameter('fellapp.sitename'), $event, $systemUser, null, null, 'Fellowship Application Creation Failed');
                        }

                        $logger->error($event);

                        //send email
                        //$sendErrorEmail = true;
                        $sendErrorEmail = false;
                        if ($sendErrorEmail) {
                            $userSecUtil = $this->container->get('user_security_utility');
                            $emails = $userSecUtil->getUserEmailsByRole($this->container->getParameter('fellapp.sitename'), "Administrator");
                            $ccs = $userSecUtil->getUserEmailsByRole($this->container->getParameter('fellapp.sitename'), "Platform Administrator");
                            if (!$emails) {
                                $emails = $ccs;
                                $ccs = null;
                            }
                            $emailUtil->sendEmail($emails, $subjectError, $event, $ccs);
                            $this->sendEmailToSystemEmail($subjectError, $event);
                        }

                        continue; //skip this fell application, because getFellowshipSubspecialty is null => something is wrong
                    }
                } else {
                    $logger->error("Not live server:"."No Erroneous spreadsheet deleted from server: inputFileName=" . $inputFileName);
                }
                //////////////////// EOF second validate the application //////////////////////

                //exit('end applicant');

                if( $testing == false ) {
                    $em->persist($user);
                    $em->flush();
                }

                //everything looks fine => remove creation attempt log
                $em->remove($eventLogAttempt);
                if( $testing == false ) {
                    $em->flush();
                }

                $event = "Populated fellowship applicant " . $displayName . "; Application ID " . $fellowshipApplication->getId();
                if( $testing == false ) {
                    $userSecUtil->createUserEditEvent($this->container->getParameter('fellapp.sitename'), $event, $systemUser, $fellowshipApplication, null, 'Fellowship Application Created');
                }

                //add application pdf generation to queue
                $fellappRepGen = $this->container->get('fellapp_reportgenerator');
                $fellappRepGen->addFellAppReportToQueue( $fellowshipApplication->getId() );

                $logger->notice($event);

                //send confirmation email to this applicant for prod server
                $environment = $userSecUtil->getSiteSettingParameter('environment');
                if( $environment == 'live' ) {
                    //send confirmation email to this applicant
                    //$confirmationEmailFellApp = $userSecUtil->getSiteSettingParameter('confirmationEmailFellApp');
                    $confirmationEmailFellApp = $userSecUtil->getSiteSettingParameter('confirmationEmailFellApp',$this->container->getParameter('fellapp.sitename'));
                    $confirmationSubjectFellApp = $userSecUtil->getSiteSettingParameter('confirmationSubjectFellApp',$this->container->getParameter('fellapp.sitename'));
                    $confirmationBodyFellApp = $userSecUtil->getSiteSettingParameter('confirmationBodyFellApp',$this->container->getParameter('fellapp.sitename'));
                    //$logger->notice("Before Send confirmation email to " . $email . " from " . $confirmationEmailFellApp);
                    if ($email && $confirmationEmailFellApp && $confirmationSubjectFellApp && $confirmationBodyFellApp) {
                        $logger->notice("Send confirmation email (fellowship application " . $fellowshipApplication->getId() . " populated in DB) to the applicant email " . $email . " from " . $confirmationEmailFellApp);
                        $emailUtil->sendEmail($email, $confirmationSubjectFellApp, $confirmationBodyFellApp, null, $confirmationEmailFellApp);
                    } else {
                        $logger->error("ERROR: confirmation email has not been sent (fellowship application " . $fellowshipApplication->getId() . " populated in DB) to the applicant email " . $email . " from " . $confirmationEmailFellApp);

                    }
                    
                }//if live

                if( $environment == 'live' ) {
                    //send confirmation email to the corresponding Fellowship director and coordinator
                    $fellappUtil = $this->container->get('fellapp_util');
                    $fellappUtil->sendConfirmationEmailsOnApplicationPopulation( $fellowshipApplication, $user );
                }

                //create reference hash ID. Must run after fellowship is in DB and has IDs
                $fellappRecLetterUtil->generateFellappRecLetterId($fellowshipApplication,true);
                if( $environment == 'live' ) {
                    // send invitation email to upload recommendation letter to references
                    $fellappRecLetterUtil->sendInvitationEmailsToReferences($fellowshipApplication,true);
                }
                
                //delete: imported rows from the sheet on Google Drive and associated uploaded files from the Google Drive.
                if( $deleteSourceRow ) {

                    $userSecUtil = $this->container->get('user_security_utility');
                    $deleteImportedAplicationsFellApp = $userSecUtil->getSiteSettingParameter('deleteImportedAplicationsFellApp',$this->container->getParameter('fellapp.sitename'));
                    if( $deleteImportedAplicationsFellApp ) {

                        //$backupFileIdFellApp = $userSecUtil->getSiteSettingParameter('backupFileIdFellApp');
                        $backupFileIdFellApp = $googlesheetmanagement->getGoogleConfigParameter('felBackupTemplateFileId');
                        if( $backupFileIdFellApp ) {
                            $googleSheetManagement = $this->container->get('fellapp_googlesheetmanagement');
                            $rowId = $fellowshipApplication->getGoogleFormId();

                            $worksheet = $googleSheetManagement->getSheetByFileId($backupFileIdFellApp);

                            $deletedRows = $googleSheetManagement->deleteImportedApplicationAndUploadsFromGoogleDrive($worksheet, $rowId);

                            if( $deletedRows ) {
                                $event = "Fellowship Application (and all uploaded files) with Google Applicant ID=".$googleFormId." Application ID " . $fellowshipApplication->getId() . " has been successful deleted from Google Drive";
                                $eventTypeStr = "Deleted Fellowship Application Backup From Google Drive";
                            } else {
                                $event = "Error: Fellowship Application with Google Applicant ID=".$googleFormId." Application ID " . $fellowshipApplication->getId() . "failed to delete from Google Drive";
                                $eventTypeStr = "Failed Deleted Fellowship Application Backup From Google Drive";
                            }

                            if( $testing == false ) {
                                $userSecUtil->createUserEditEvent($this->container->getParameter('fellapp.sitename'), $event, $systemUser, $fellowshipApplication, null, $eventTypeStr);
                            }
                            $logger->notice($event);

                        }//if

                    }

                }

                if( $fellowshipApplication && !$populatedFellowshipApplications->contains($fellowshipApplication) ) {
                    $populatedFellowshipApplications->add($fellowshipApplication);
                }

            } catch( \Doctrine\DBAL\DBALException $e ) {
                $event = "Error creating fellowship applicant with unique Google Applicant ID=".$googleFormId."; Exception=".$e->getMessage();
                //$emailUtil->sendEmail( $emails, $subjectError, $event );
                $this->sendEmailToSystemEmail($subjectError, $event);

                //logger
                $logger->error($event);

                $userUtil = $this->container->get('user_utility');
                if( $userUtil->getSession() ) {
                    $userUtil->getSession()->getFlashBag()->add(
                        'warning',
                        $event
                    );
                }
            } //try/catch


        } //for

        //echo "count=".$count."<br>";
        //exit('end populate');

        return $populatedFellowshipApplications;
    } //populateSpreadsheet

    public function populateSpreadsheet_TEST( $document, $datafile=null, $deleteSourceRow=false, $testing=false ) {

        //echo "inputFileName=".$inputFileName."<br>";
        $logger = $this->container->get('logger');
        $userSecUtil = $this->container->get('user_security_utility');
        $userUtil = $this->container->get('user_utility');
        $googlesheetmanagement = $this->container->get('fellapp_googlesheetmanagement');
        $fellappRecLetterUtil = $this->container->get('fellapp_rec_letter_util');

        $environment = $userSecUtil->getSiteSettingParameter('environment');

        ini_set('max_execution_time', 3000); //30000 seconds = 50 minutes
        //ini_set('memory_limit', '512M');

//        $service = $googlesheetmanagement->getGoogleService();
//        if( !$service ) {
//            $event = "Google API service failed!";
//            $logger->error($event);
//            $this->sendEmailToSystemEmail($event, $event);
//            $logger->error($event. " while processing ".$document->getServerPath());
//            return false;
//        }

        $inputFileName = $document->getServerPath();    //'Uploaded/fellapp/Spreadsheets/Pathology Fellowships Application Form (Responses).xlsx';
        $logger->notice("Population a single application sheet (document ID=".$document->getId().") with filename=".$inputFileName);

        //if ruuning from cron path must be: $path = getcwd() . "/web";
        //$inputFileName = $path . "/" . $inputFileName;
        //$inputFileName = realpath($this->container->get('kernel')->getRootDir() . "/../public/" . $inputFileName);
        $inputFileName = $this->container->get('kernel')->getProjectDir() . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . $inputFileName;
        //echo "inputFileName=".$inputFileName."<br>";
        if( !file_exists($inputFileName) ) {
            $logger->error("Source sheet does not exists with filename=".$inputFileName);
            return false;
        }

        ////////// process $inputFileName ///////////
        //$logger->notice("Getting source sheet with filename=".$inputFileName);
        //echo "Getting source sheet with filename=".$inputFileName."<br>";
        //$inputFileName = "C:\Users\ch3\Documents\MyDocs\WCMC\ORDER\order-lab\orderflex\public\Uploaded/fellapp/Spreadsheets\1647382888ID1-L_TCY1vrhXyl4KBEZ_x7g-iC_CoKQbcjnvdjgdVR-o.edu_Ali_Mahmoud_2021-05-23_20_21_18";

        try {
            $extension = pathinfo($inputFileName,PATHINFO_EXTENSION);
            //$forceCreateCopy = true;
            $forceCreateCopy = false;
            if( $forceCreateCopy || !$extension || ($extension && strlen($extension) > 9) ) {

                $inputFileNameNew = $this->createTempSpreadsheetCopy($inputFileName,$forceCreateCopy);
                if( !$inputFileNameNew ) {
                    $errorSubject = "Can not create temp file for the source spreadsheet";
                    $errorEvent = $errorSubject . ". Filename=" .
                        $inputFileName . ", extension=" . $extension .
                        ", documentId=" . $document->getId();
                    //exit($errorEvent); //testing
                    $logger->error($errorEvent);
                    $this->sendEmailToSystemEmail($errorSubject, $errorEvent);
                    return false;
                    //exit('$inputFileNameNew is NULL');
                }

                $inputFileType = \PhpOffice\PhpSpreadsheet\IOFactory::identify($inputFileNameNew); //Google spreadsheet: identify $inputFileType='Csv'
                $objReader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader($inputFileType);
                $objPHPExcel = $objReader->load($inputFileNameNew);

                //remove temp file $inputFileNameNew
                unlink($inputFileNameNew);

            } else {
                $logger->warning("Before identify input file type: inputFileName=[".$inputFileName."]");
                $objReader = \PhpOffice\PhpSpreadsheet\IOFactory::createReaderForFile($inputFileName);
                $objPHPExcel = $objReader->load($inputFileName);
            }
        } catch(\Exception $e) {
            $event = 'Error loading file "'.pathinfo($inputFileName,PATHINFO_BASENAME).'": '.$e->getMessage();
            $logger->error($event);
            $this->sendEmailToSystemEmail($event, $event);
            throw new IOException($event);
        }

        //$uploadPath = $this->uploadDir.'/FellowshipApplicantUploads';
        $applicantsUploadPathFellApp = $userSecUtil->getSiteSettingParameter(
            'applicantsUploadPathFellApp',
            $this->container->getParameter('fellapp.sitename')
        );
        if( !$applicantsUploadPathFellApp ) {
            $applicantsUploadPathFellApp = "FellowshipApplicantUploads";
            $logger->warning('applicantsUploadPathFellApp is not defined in Fellowship Site Parameters. Use default "'.
                $applicantsUploadPathFellApp.'" folder.');
        }
        $uploadPath = $this->uploadDir.'/'.$applicantsUploadPathFellApp;

        $em = $this->em;
        $default_time_zone = $this->container->getParameter('default_time_zone');
        $emailUtil = $this->container->get('user_mailer_utility');

        $userkeytype = $userSecUtil->getUsernameType('local-user');
        if( !$userkeytype ) {
            throw new EntityNotFoundException('Unable to find local user keytype');
        }

        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:EmploymentType'] by [EmploymentType::class]
        $employmentType = $em->getRepository(EmploymentType::class)->findOneByName("Pathology Fellowship Applicant");
        if( !$employmentType ) {
            throw new EntityNotFoundException('Unable to find entity by name='."Pathology Fellowship Applicant");
        }
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:LocationTypeList'] by [LocationTypeList::class]
        $presentLocationType = $em->getRepository(LocationTypeList::class)->findOneByName("Present Address");
        if( !$presentLocationType ) {
            throw new EntityNotFoundException('Unable to find entity by name='."Present Address");
        }
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:LocationTypeList'] by [LocationTypeList::class]
        $permanentLocationType = $em->getRepository(LocationTypeList::class)->findOneByName("Permanent Address");
        if( !$permanentLocationType ) {
            throw new EntityNotFoundException('Unable to find entity by name='."Permanent Address");
        }
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:LocationTypeList'] by [LocationTypeList::class]
        $workLocationType = $em->getRepository(LocationTypeList::class)->findOneByName("Work Address");
        if( !$workLocationType ) {
            throw new EntityNotFoundException('Unable to find entity by name='."Work Address");
        }

        //process.py script: replaced namespace by ::class: ['AppFellAppBundle:FellAppStatus'] by [FellAppStatus::class]
        $activeStatus = $em->getRepository(FellAppStatus::class)->findOneByName("active");
        if( !$activeStatus ) {
            throw new EntityNotFoundException('Unable to find entity by name='."active");
        }

        ////////////// add system user /////////////////
        $systemUser = $userSecUtil->findSystemUser();
        ////////////// end of add system user /////////////////

        $sheet = $objPHPExcel->getSheet(0);
        $highestRow = $sheet->getHighestRow();
        $highestColumn = $sheet->getHighestColumn();
        //echo "rows=$highestRow columns=$highestColumn <br>";
        //$logger->notice("rows=$highestRow columns=$highestColumn");

        $headers = $rowData = $sheet->rangeToArray('A' . 1 . ':' . $highestColumn . 1,
            NULL,
            TRUE,
            FALSE);
        //print_r($headers);

        $populatedFellowshipApplications = new ArrayCollection();

        ////////////////// Potential ERROR //////////////////
        //$useWarning = false;
        $useWarning = true;
        if( $useWarning ) {
            if (!$highestRow || $highestRow < 3) {

                $createDateStr = NULL;
                $createDate = $document->getCreateDate();
                if ($createDate) {
                    $createDateStr = $createDate->format('d-m-Y H:i:s');
                }

                //Create error notification email [ORDER]
                $subject = "Error: Invalid number of rows in Fellowship Application Spreadsheet";
                $body = "Invalid number of rows in Fellowship Application Spreadsheet." .
                    " The applicant data is located in row number 3. The applicant data might be missing." .
                    " Number of rows: $highestRow." . ", document ID=" . $document->getId() .
                    ", title=" . $document->getTitle() .
                    ", originalName=" . $document->getOriginalname() .
                    ", createDate=" . $createDateStr .
                    ", size=" . $document->getSize() .
                    ", filename=" . $inputFileName;

                $logger->error($body);

                $userSecUtil = $this->container->get('user_security_utility');
                $systemUser = $userSecUtil->findSystemUser();

                $userSecUtil->sendEmailToSystemEmail($subject, $body);

                //Send email to admins
                $emails = $userSecUtil->getUserEmailsByRole($this->container->getParameter('fellapp.sitename'), "Platform Administrator");
                $ccs = $userSecUtil->getUserEmailsByRole($this->container->getParameter('fellapp.sitename'), "Administrator");
                if (!$emails) {
                    $emails = $ccs;
                    $ccs = null;
                }
                //$emails = $ccs = 'oli2002@med.cornell.edu'; //testing
                $emailUtil = $this->container->get('user_mailer_utility');
                $emailUtil->sendEmail($emails, $subject, $body, $ccs);

                if ($testing == false) {
                    $userSecUtil->createUserEditEvent($this->container->getParameter('fellapp.sitename'), $body, $systemUser, null, null, 'Fellowship Application Creation Failed');
                }

                ///////////// Delete erroneous spreadsheet $datafile and associated document /////////////
                $removeErrorFile = true;
                $removeErrorFile = false;
                if( $removeErrorFile ) {
                    $datafileId = NULL;
                    if ($datafile) {
                        $datafileId = $datafile->getId();
                    }
                    $logger->error("Removing erroneous spreadsheet ($inputFileName): datafileId=" . $datafileId . " and associated documentId=" . $document->getId());
                    unlink($inputFileName);
                    $em->remove($document);
                    if ($datafile) {
                        $em->remove($datafile);
                    }

                    if ($testing == false) {
                        $em->flush();
                    }
                }

                //testing
                throw new IOException("Testing: ".$subject);

                return false;
            }
        }
        ////////////////// EOF Potential ERROR //////////////////

        //for each user in excel
        if(1) { //if test
            for ($row = 3; $row <= $highestRow; $row++) {

                //  Read a row of data into an array
                $rowData = $sheet->rangeToArray('A' . $row . ':' . $highestColumn . $row,
                    NULL,
                    TRUE,
                    FALSE);

                ///////////////// parse field parseFields ///////////////////
                //need $inputFileName, $document=null, $datafile=null, $testing=false
                $parseRes = $this->parseFields_TEST($rowData,$headers,$populatedFellowshipApplications,$inputFileName, $document=null, $datafile=null, $testing=false);
                if( !$parseRes ) {
                    continue;
                }
                //dump($rowData);
                //exit("EXIT: document ID=".$document->getId().", filename=".$inputFileName.", highestRow=$highestRow");
                ///////////////// parse field ///////////////////

            } //for
        }//if(1 //if test


        //echo "count=".$count."<br>";
        //exit('end populate');

        return $populatedFellowshipApplications;
    }
    public function validateSpreadsheet_TEST( $rowData, $headers, $inputFileName, $document=null, $datafile=null, $testing=false ) {
        $em = $this->em;
        $logger = $this->container->get('logger');
        $userSecUtil = $this->container->get('user_security_utility');
        $emailUtil = $this->container->get('user_mailer_utility');

        $googleFormId = $this->getValueByHeaderName('ID',$rowData,$headers);
        $subjectError = "Failed to import a received fellowship application - will automatically attempt to re-import (ID=$googleFormId)";

        $systemUser = $userSecUtil->findSystemUser();
        $environment = $userSecUtil->getSiteSettingParameter('environment');
        $userkeytype = $userSecUtil->getUsernameType('local-user');
        $default_time_zone = $this->container->getParameter('default_time_zone');

        $email = $this->getValueByHeaderName('email', $rowData, $headers);
        $lastName = $this->getValueByHeaderName('lastName', $rowData, $headers);
        $firstName = $this->getValueByHeaderName('firstName', $rowData, $headers);

        $errorMsgArr = array();
        $fellowshipType = $this->getValueByHeaderName('fellowshipType', $rowData, $headers);
        if (!$fellowshipType) {
            $errorMsgArr[] = "Fellowship Type is null";
        }
        $ref1 = $this->createFellAppReference($em, $systemUser, 'recommendation1', $rowData, $headers, true);
        if (!$ref1) {
            $errorMsgArr[] = "Reference1 is null";
        }
        $ref2 = $this->createFellAppReference($em, $systemUser, 'recommendation2', $rowData, $headers, true);
        if (!$ref2) {
            $errorMsgArr[] = "Reference2 is null";
        }
        $ref3 = $this->createFellAppReference($em, $systemUser, 'recommendation3', $rowData, $headers, true);
        if (!$ref3) {
            $errorMsgArr[] = "Reference3 is null";
        }

        if (!$lastName) {
            $errorMsgArr[] = "Applicant last name is null";
        }
        if (!$firstName) {
            $errorMsgArr[] = "Applicant first name is null";
        }

        if (!$email) {
            $errorMsgArr[] = "Applicant email is null";
        }

        $signatureName = $this->getValueByHeaderName('signatureName', $rowData, $headers);
        if (!$signatureName) {
            $errorMsgArr[] = "Signature is null";
        }
        $signatureDate = $this->getValueByHeaderName('signatureDate', $rowData, $headers);
        if (!$signatureDate) {
            $errorMsgArr[] = "Signature Date is null";
        }
        $trainingPeriodStart = $this->getValueByHeaderName('trainingPeriodStart', $rowData, $headers);
        if (!$trainingPeriodStart) {
            $errorMsgArr[] = "Start Date is null";
        }
        $trainingPeriodEnd = $this->getValueByHeaderName('trainingPeriodEnd', $rowData, $headers);
        if (!$trainingPeriodEnd) {
            $errorMsgArr[] = "End Date is null";
        }


        if ($environment == 'live') {
            //getFellowshipSubspecialty
            //if( !$fellowshipApplication->getFellowshipSubspecialty() ) { //getSignatureName() - not reliable - some applicants managed to submit the form without signature
            if ($errorMsgArr && count($errorMsgArr) > 0) {

                //delete erroneous spreadsheet from filesystem and $document from DB
                if( file_exists($inputFileName) ) {
                    //$logger->error("Source sheet does not exists with filename=".$inputFileName);
                    //remove from DB
                    if( $document ) {
                        $em->remove($document);
                    }
                    if( $datafile ) {
                        $em->remove($datafile);
                    }

                    if ($testing == false) {
                        $em->flush();
                    }
                    //delete file
                    unlink($inputFileName); // or die("Couldn't delete erroneous spreadsheet inputFileName=[".$inputFileName."]");
                    $logger->error("Erroneous spreadsheet deleted from server: $inputFileName=" . $inputFileName);
                }

                $event = "First spreadsheet validation error:" .
                    " Empty required fields after trying to populate the Fellowship Application with Google Applicant ID=[" . $googleFormId . "]" .
                    ": " . implode("; ", $errorMsgArr);

                if ($testing == false) {
                    $userSecUtil->createUserEditEvent($this->container->getParameter('fellapp.sitename'), $event, $systemUser, null, null, 'Fellowship Application Creation Failed');
                }

                $logger->error($event);

                //send email
                $sendErrorEmail = true;
                //$sendErrorEmail = false;
                if ($sendErrorEmail) {
                    $userSecUtil = $this->container->get('user_security_utility');
                    $emails = $userSecUtil->getUserEmailsByRole($this->container->getParameter('fellapp.sitename'), "Administrator");
                    $ccs = $userSecUtil->getUserEmailsByRole($this->container->getParameter('fellapp.sitename'), "Platform Administrator");
                    if (!$emails) {
                        $emails = $ccs;
                        $ccs = null;
                    }
                    $emailUtil->sendEmail($emails, $subjectError, $event, $ccs);
                    $this->sendEmailToSystemEmail($subjectError, $event);
                }

                //continue; //skip this fell application, because getFellowshipSubspecialty is null => something is wrong
                return false;
            }
        } else {
            $logger->error("Not live server: No deleted erroneous spreadsheet from filesystem and document $document from DB");
        }
        return true;
    }
    public function parseFields_TEST( $rowData,
                                      $headers,
                                      $populatedFellowshipApplications,
                                      $inputFileName=null,
                                      $document=null,
                                      $datafile=null,
                                      $deleteSourceRow=false,
                                      $testing=false
    ) {
        $em = $this->em;
        $logger = $this->container->get('logger');
        $userSecUtil = $this->container->get('user_security_utility');
        $emailUtil = $this->container->get('user_mailer_utility');
        $fellappRecLetterUtil = $this->container->get('fellapp_rec_letter_util');
        $googleSheetManagement = $this->container->get('fellapp_googlesheetmanagement');

        $systemUser = $userSecUtil->findSystemUser();
        $environment = $userSecUtil->getSiteSettingParameter('environment');
        $userkeytype = $userSecUtil->getUsernameType('local-user');
        $default_time_zone = $this->container->getParameter('default_time_zone');

        $employmentType = $em->getRepository(EmploymentType::class)->findOneByName("Pathology Fellowship Applicant");
        if( !$employmentType ) {
            throw new EntityNotFoundException('Unable to find entity by name='."Pathology Fellowship Applicant");
        }
        $presentLocationType = $em->getRepository(LocationTypeList::class)->findOneByName("Present Address");
        if( !$presentLocationType ) {
            throw new EntityNotFoundException('Unable to find entity by name='."Present Address");
        }
        $permanentLocationType = $em->getRepository(LocationTypeList::class)->findOneByName("Permanent Address");
        if( !$permanentLocationType ) {
            throw new EntityNotFoundException('Unable to find entity by name='."Permanent Address");
        }
        $workLocationType = $em->getRepository(LocationTypeList::class)->findOneByName("Work Address");
        if( !$workLocationType ) {
            throw new EntityNotFoundException('Unable to find entity by name='."Work Address");
        }
        $activeStatus = $em->getRepository(FellAppStatus::class)->findOneByName("active");
        if( !$activeStatus ) {
            throw new EntityNotFoundException('Unable to find entity by name='."active");
        }

        ///////////////// parse field ///////////////////
        $googleFormId = $this->getValueByHeaderName('ID', $rowData, $headers);
        $email = $this->getValueByHeaderName('email', $rowData, $headers);
        $lastName = $this->getValueByHeaderName('lastName', $rowData, $headers);
        $firstName = $this->getValueByHeaderName('firstName', $rowData, $headers);

        if (!$googleFormId) {
            //continue; //skip this fell application, because googleFormId does not exists
            return null;
        }

        //Failed to import a received fellowship application - will automatically attempt to re-import in X hours
        $subjectError = "Failed to import a received fellowship application - will automatically attempt to re-import (ID=$googleFormId)";

        ////////////////// validate spreadsheet /////////////////////////
        // validate Spreadsheet_TEST( $rowData, $headers, $inputFileName, $document=null, $datafile=null, $testing=false )
        $valRes = $this->validateSpreadsheet_TEST( $rowData, $headers, $inputFileName );
        if( $valRes === false ) {
            //continue; //skip this fell application, because getFellowshipSubspecialty is null => something is wrong
            return null;
        }
        ////////////////// EOF validate spreadsheet ////////////////////////


        //exit('exit');

        try {
            $fellowshipApplicationDb = $em->getRepository(FellowshipApplication::class)->findOneByGoogleFormId($googleFormId);
            if ($fellowshipApplicationDb) {
                //continue; //skip this fell application, because it already exists in DB
                return null;
            }

            $middleName = $this->getValueByHeaderName('middleName', $rowData, $headers);

            $lastNameCap = $this->capitalizeIfNotAllCapital($lastName);
            $firstNameCap = $this->capitalizeIfNotAllCapital($firstName);
            //$middleNameCap = $this->capitalizeIfNotAllCapital($middleName);

            $lastNameCap = preg_replace('/\s+/', '_', $lastNameCap);
            $firstNameCap = preg_replace('/\s+/', '_', $firstNameCap);

            //Last Name + First Name + Email
            $username = $lastNameCap . "_" . $firstNameCap . "_" . $email;

            $displayName = $firstName . " " . $lastName;
            if ($middleName) {
                $displayName = $firstName . " " . $middleName . " " . $lastName;
            }

            //create logger which must be deleted on successefull creation of application
            $eventAttempt = "Attempt of creating Fellowship Applicant " . $displayName . " with unique Google Applicant ID=" . $googleFormId;

            if ($testing == false) {
                $eventLogAttempt = $userSecUtil->createUserEditEvent($this->container->getParameter('fellapp.sitename'), $eventAttempt, $systemUser, null, null, 'Fellowship Application Creation Failed');
            }

            //check if the user already exists in DB by $googleFormId
            $user = $em->getRepository(User::class)->findOneByPrimaryPublicUserId($username);

            if (!$user) {
                //create excel user
                $addobjects = false;
                $user = new User($addobjects);
                $user->setKeytype($userkeytype);
                $user->setPrimaryPublicUserId($username);

                //set unique username
                $usernameUnique = $user->createUniqueUsername();
                $user->setUsername($usernameUnique);
                $user->setUsernameCanonical($usernameUnique);


                $user->setEmail($email);
                $user->setEmailCanonical($email);

                $user->setFirstName($firstName);
                $user->setLastName($lastName);
                $user->setMiddleName($middleName);
                $user->setDisplayName($displayName);
                $user->setPassword("");
                $user->setCreatedby('googleapi');
                $user->getPreferences()->setTimezone($default_time_zone);
                $user->setLocked(true);

                //Pathology Fellowship Applicant in EmploymentStatus
                $employmentStatus = new EmploymentStatus($systemUser);
                $employmentStatus->setEmploymentType($employmentType);
                $user->addEmploymentStatus($employmentStatus);
            }

            //create new Fellowship Applicantion
            $fellowshipApplication = new FellowshipApplication($systemUser);

            $fellowshipApplication->setAppStatus($activeStatus);
            //For HUB server, $googleFormId can be used to store unique application ID submitted via HUB server,
            // maybe in the same format 'dpino_dhs_lacounty_gov_Pino_Devon_2024-12-16_04_56_45'
            //Therefore, we can treat $googleFormId as remote form ID $remoteFormId
            $fellowshipApplication->setGoogleFormId($googleFormId);

            //Upon retreval form, set the retrievalMethod according to the site setting
            //$retrievalMethod = $userSecUtil->getSiteSettingParameter('retrievalMethod',$this->container->getParameter('fellapp.sitename'));
            //$fellowshipApplication->setRetrievalMethod($retrievalMethod);

            $user->addFellowshipApplication($fellowshipApplication);

            //timestamp
            $fellowshipApplication->setTimestamp($this->transformDatestrToDate($this->getValueByHeaderName('timestamp', $rowData, $headers)));

            //fellowshipType
            $fellowshipType = $this->getValueByHeaderName('fellowshipType', $rowData, $headers);
            if ($fellowshipType) {
                //$logger->notice("fellowshipType=[".$fellowshipType."]");
                $fellowshipType = trim((string)$fellowshipType);
                $fellowshipType = $this->capitalizeIfNotAllCapital($fellowshipType);
                $transformer = new GenericTreeTransformer($em, $systemUser, 'FellowshipSubspecialty');
                $fellowshipTypeEntity = $transformer->reverseTransform($fellowshipType);
                $fellowshipApplication->setFellowshipSubspecialty($fellowshipTypeEntity);
            }

            //////////////////////// assign local institution from SiteParameters ////////////////////////
            //$instPathologyFellowshipProgram = null;
            //$localInstitutionFellApp = $userSecUtil->getSiteSettingParameter('localInstitutionFellApp');
            $instPathologyFellowshipProgram = $userSecUtil->getSiteSettingParameter('localInstitutionFellApp', $this->container->getParameter('fellapp.sitename'));

            if ($instPathologyFellowshipProgram) {
                $fellowshipApplication->setInstitution($instPathologyFellowshipProgram);
            } else {
                $logger->warning('Local institution for import fellowship application is not set or invalid; instPathologyFellowshipProgram=' . $instPathologyFellowshipProgram);
            }
            //////////////////////// EOF assign local institution from SiteParameters ////////////////////////


            //trainingPeriodStart
            $fellowshipApplication->setStartDate($this->transformDatestrToDate($this->getValueByHeaderName('trainingPeriodStart', $rowData, $headers)));

            //trainingPeriodEnd
            $fellowshipApplication->setEndDate($this->transformDatestrToDate($this->getValueByHeaderName('trainingPeriodEnd', $rowData, $headers)));

            /////////////////// Upload document ////////////////////////
            $res = $this->uploadGoogleDocuments( $fellowshipApplication, $rowData, $headers );
            $examination = $res['examination'];
            /////////////////// EOF Upload document ////////////////////////

            //presentAddress
            $presentLocation = new Location($systemUser);
            $presentLocation->setName('Fellowship Applicant Present Address');
            $presentLocation->addLocationType($presentLocationType);
            $geoLocation = $this->createGeoLocation($em, $systemUser, 'presentAddress', $rowData, $headers);
            if ($geoLocation) {
                $presentLocation->setGeoLocation($geoLocation);
            }
            $user->addLocation($presentLocation);
            $fellowshipApplication->addLocation($presentLocation);

            //telephoneHome
            //telephoneMobile
            //telephoneFax
            $presentLocation->setPhone($this->getValueByHeaderName('telephoneHome', $rowData, $headers) . "");
            $presentLocation->setMobile($this->getValueByHeaderName('telephoneMobile', $rowData, $headers) . "");
            $presentLocation->setFax($this->getValueByHeaderName('telephoneFax', $rowData, $headers) . "");

            //permanentAddress
            $permanentLocation = new Location($systemUser);
            $permanentLocation->setName('Fellowship Applicant Permanent Address');
            $permanentLocation->addLocationType($permanentLocationType);
            $geoLocation = $this->createGeoLocation($em, $systemUser, 'permanentAddress', $rowData, $headers);
            if ($geoLocation) {
                $permanentLocation->setGeoLocation($geoLocation);
            }
            $user->addLocation($permanentLocation);
            $fellowshipApplication->addLocation($permanentLocation);

            //telephoneWork
            $telephoneWork = $this->getValueByHeaderName('telephoneWork', $rowData, $headers);
            if ($telephoneWork) {
                $workLocation = new Location($systemUser);
                $workLocation->setName('Fellowship Applicant Work Address');
                $workLocation->addLocationType($workLocationType);
                $workLocation->setPhone($telephoneWork . "");
                $user->addLocation($workLocation);
                $fellowshipApplication->addLocation($workLocation);
            }


            $citizenship = new Citizenship($systemUser);
            //$user->getCredentials()->addCitizenship($citizenship);
            $fellowshipApplication->addCitizenship($citizenship);
            //visaStatus
            $citizenship->setVisa($this->getValueByHeaderName('visaStatus', $rowData, $headers));
            //citizenshipCountry
            $citizenshipCountry = $this->getValueByHeaderName('citizenshipCountry', $rowData, $headers);
            if ($citizenshipCountry) {
                $citizenshipCountry = trim((string)$citizenshipCountry);
                $transformer = new GenericTreeTransformer($em, $systemUser, 'Countries');
                $citizenshipCountryEntity = $transformer->reverseTransform($citizenshipCountry);
                $citizenship->setCountry($citizenshipCountryEntity);
            }

            //DOB: oleg_userdirectorybundle_user_credentials_dob
            $dobDate = $this->transformDatestrToDate($this->getValueByHeaderName('dateOfBirth', $rowData, $headers));
            $fellowshipApplication->getUser()->getCredentials()->setDob($dobDate);

            //undergraduate
            $this->createFellAppTraining($em, $fellowshipApplication, $systemUser, "undergraduateSchool", $rowData, $headers, 1);

            //graduate
            $this->createFellAppTraining($em, $fellowshipApplication, $systemUser, "graduateSchool", $rowData, $headers, 2);

            //medical
            $this->createFellAppTraining($em, $fellowshipApplication, $systemUser, "medicalSchool", $rowData, $headers, 3);

            //residency: residencyStart	residencyEnd	residencyName	residencyArea
            $this->createFellAppTraining($em, $fellowshipApplication, $systemUser, "residency", $rowData, $headers, 4);

            //gme1: gme1Start, gme1End, gme1Name, gme1Area => Major
            $this->createFellAppTraining($em, $fellowshipApplication, $systemUser, "gme1", $rowData, $headers, 5);

            //gme2: gme2Start, gme2End, gme2Name, gme2Area => Major
            $this->createFellAppTraining($em, $fellowshipApplication, $systemUser, "gme2", $rowData, $headers, 6);

            //otherExperience1Start	otherExperience1End	otherExperience1Name=>Major
            $this->createFellAppTraining($em, $fellowshipApplication, $systemUser, "otherExperience1", $rowData, $headers, 7);

            //otherExperience2Start	otherExperience2End	otherExperience2Name=>Major
            $this->createFellAppTraining($em, $fellowshipApplication, $systemUser, "otherExperience2", $rowData, $headers, 8);

            //otherExperience3Start	otherExperience3End	otherExperience3Name=>Major
            $this->createFellAppTraining($em, $fellowshipApplication, $systemUser, "otherExperience3", $rowData, $headers, 9);

            //USMLEStep1DatePassed	USMLEStep1Score
            $examination->setUSMLEStep1DatePassed($this->transformDatestrToDate($this->getValueByHeaderName('USMLEStep1DatePassed', $rowData, $headers)));
            $examination->setUSMLEStep1Score($this->getValueByHeaderName('USMLEStep1Score', $rowData, $headers));
            $examination->setUSMLEStep1Percentile($this->getValueByHeaderName('USMLEStep1Percentile', $rowData, $headers));

            //USMLEStep2CKDatePassed	USMLEStep2CKScore	USMLEStep2CSDatePassed	USMLEStep2CSScore
            $examination->setUSMLEStep2CKDatePassed($this->transformDatestrToDate($this->getValueByHeaderName('USMLEStep2CKDatePassed', $rowData, $headers)));
            $examination->setUSMLEStep2CKScore($this->getValueByHeaderName('USMLEStep2CKScore', $rowData, $headers));
            $examination->setUSMLEStep2CKPercentile($this->getValueByHeaderName('USMLEStep2CKPercentile', $rowData, $headers));
            $examination->setUSMLEStep2CSDatePassed($this->transformDatestrToDate($this->getValueByHeaderName('USMLEStep2CSDatePassed', $rowData, $headers)));
            $examination->setUSMLEStep2CSScore($this->getValueByHeaderName('USMLEStep2CSScore', $rowData, $headers));
            $examination->setUSMLEStep2CSPercentile($this->getValueByHeaderName('USMLEStep2CSPercentile', $rowData, $headers));

            //USMLEStep3DatePassed	USMLEStep3Score
            $examination->setUSMLEStep3DatePassed($this->transformDatestrToDate($this->getValueByHeaderName('USMLEStep3DatePassed', $rowData, $headers)));
            $examination->setUSMLEStep3Score($this->getValueByHeaderName('USMLEStep3Score', $rowData, $headers));
            $examination->setUSMLEStep3Percentile($this->getValueByHeaderName('USMLEStep3Percentile', $rowData, $headers));

            //ECFMGCertificate
            $ECFMGCertificateStr = $this->getValueByHeaderName('ECFMGCertificate', $rowData, $headers);
            $ECFMGCertificate = false;
            if ($ECFMGCertificateStr == 'Yes') {
                $ECFMGCertificate = true;
            }
            $examination->setECFMGCertificate($ECFMGCertificate);

            //ECFMGCertificateNumber	ECFMGCertificateDate
            $examination->setECFMGCertificateNumber($this->getValueByHeaderName('ECFMGCertificateNumber', $rowData, $headers));
            $examination->setECFMGCertificateDate($this->transformDatestrToDate($this->getValueByHeaderName('ECFMGCertificateDate', $rowData, $headers)));

            //COMLEXLevel1DatePassed	COMLEXLevel1Score	COMLEXLevel2DatePassed	COMLEXLevel2Score	COMLEXLevel3DatePassed	COMLEXLevel3Score
            $examination->setCOMLEXLevel1Score($this->getValueByHeaderName('COMLEXLevel1Score', $rowData, $headers));
            $examination->setCOMLEXLevel1Percentile($this->getValueByHeaderName('COMLEXLevel1Percentile', $rowData, $headers));
            $examination->setCOMLEXLevel1DatePassed($this->transformDatestrToDate($this->getValueByHeaderName('COMLEXLevel1DatePassed', $rowData, $headers)));
            $examination->setCOMLEXLevel2Score($this->getValueByHeaderName('COMLEXLevel2Score', $rowData, $headers));
            $examination->setCOMLEXLevel2Percentile($this->getValueByHeaderName('COMLEXLevel2Percentile', $rowData, $headers));
            $examination->setCOMLEXLevel2DatePassed($this->transformDatestrToDate($this->getValueByHeaderName('COMLEXLevel2DatePassed', $rowData, $headers)));
            $examination->setCOMLEXLevel3Score($this->getValueByHeaderName('COMLEXLevel3Score', $rowData, $headers));
            $examination->setCOMLEXLevel3Percentile($this->getValueByHeaderName('COMLEXLevel3Percentile', $rowData, $headers));
            $examination->setCOMLEXLevel3DatePassed($this->transformDatestrToDate($this->getValueByHeaderName('COMLEXLevel3DatePassed', $rowData, $headers)));

            //medicalLicensure1Country	medicalLicensure1State	medicalLicensure1DateIssued	medicalLicensure1Number	medicalLicensure1Active
            $this->createFellAppMedicalLicense($em, $fellowshipApplication, $systemUser, "medicalLicensure1", $rowData, $headers);

            //medicalLicensure2
            $this->createFellAppMedicalLicense($em, $fellowshipApplication, $systemUser, "medicalLicensure2", $rowData, $headers);

            //boardCertification1Board	boardCertification1Area	boardCertification1Date
            $this->createFellAppBoardCertification($em, $fellowshipApplication, $systemUser, "boardCertification1", $rowData, $headers);
            //boardCertification2
            $this->createFellAppBoardCertification($em, $fellowshipApplication, $systemUser, "boardCertification2", $rowData, $headers);
            //boardCertification3
            $this->createFellAppBoardCertification($em, $fellowshipApplication, $systemUser, "boardCertification3", $rowData, $headers);

            //recommendation1Name	recommendation1Title	recommendation1Institution	recommendation1AddressStreet1	recommendation1AddressStreet2	recommendation1AddressCity	recommendation1AddressState	recommendation1AddressZip	recommendation1AddressCountry
            $ref1 = $this->createFellAppReference($em, $systemUser, 'recommendation1', $rowData, $headers);
            if ($ref1) {
                $fellowshipApplication->addReference($ref1);
            }
            $ref2 = $this->createFellAppReference($em, $systemUser, 'recommendation2', $rowData, $headers);
            if ($ref2) {
                $fellowshipApplication->addReference($ref2);
            }
            $ref3 = $this->createFellAppReference($em, $systemUser, 'recommendation3', $rowData, $headers);
            if ($ref3) {
                $fellowshipApplication->addReference($ref3);
            }
            $ref4 = $this->createFellAppReference($em, $systemUser, 'recommendation4', $rowData, $headers);
            if ($ref4) {
                $fellowshipApplication->addReference($ref4);
            }

            //honors
            $fellowshipApplication->setHonors($this->getValueByHeaderName('honors', $rowData, $headers));
            //publications
            $fellowshipApplication->setPublications($this->getValueByHeaderName('publications', $rowData, $headers));
            //memberships
            $fellowshipApplication->setMemberships($this->getValueByHeaderName('memberships', $rowData, $headers));

            //signatureName
            $fellowshipApplication->setSignatureName($this->getValueByHeaderName('signatureName', $rowData, $headers));
            //signatureDate
            $signatureDate = $this->transformDatestrToDate($this->getValueByHeaderName('signatureDate', $rowData, $headers));
            $fellowshipApplication->setSignatureDate($signatureDate);

            //////////////////// second validate the application //////////////////////
            $errorMsgArr = array();
            if (!$fellowshipApplication->getFellowshipSubspecialty()) {
                $errorMsgArr[] = "Fellowship Type is null";
            }
            if (count($fellowshipApplication->getReferences()) == 0) {
                $errorMsgArr[] = "References are null";
            }
            if (!$displayName) {
                $errorMsgArr[] = "Applicant name is null";
            }
            if (!$fellowshipApplication->getSignatureName()) {
                $errorMsgArr[] = "Signature is null";
            }
            if (!$fellowshipApplication->getSignatureDate()) {
                $errorMsgArr[] = "Signature Date is null";
            }
            if (!$fellowshipApplication->getStartDate()) {
                $errorMsgArr[] = "Start Date is null";
            }
            if (!$fellowshipApplication->getEndDate()) {
                $errorMsgArr[] = "End Date is null";
            }

            if ($environment == 'live') {
                //This condition (count($errorMsgArr) > 0) should never happen theoretically, because the first validation should catch the erroneous spreadsheet
                //if( !$fellowshipApplication->getFellowshipSubspecialty() ) { //getSignatureName() - not reliable - some applicants managed to submit the form without signature
                if ($errorMsgArr && count($errorMsgArr) > 0) {

                    //delete erroneous spreadsheet from filesystem and $document from DB
                    if( $inputFileName && file_exists($inputFileName) ) {
                        //$logger->error("Source sheet does not exists with filename=".$inputFileName);
                        //remove from DB
                        if( $document ) {
                            $em->remove($document);
                        }
                        if ($datafile) {
                            $em->remove($datafile);
                        }
                        //$em->flush($document);

                        if ($testing == false) {
                            $em->flush();
                        }
                        //delete file
                        unlink($inputFileName); // or die("Couldn't delete erroneous spreadsheet inputFileName=[".$inputFileName."]");
                        $logger->error("Erroneous spreadsheet deleted from server: inputFileName=" . $inputFileName);
                    }

                    $event = "Second spreadsheet validation error:" .
                        " (Applicant=[" . $displayName . "], Application ID=[" . $fellowshipApplication->getId() . "])" .
                        " Empty required fields after trying to populate the Fellowship Application with Google Applicant ID=[" . $googleFormId . "]" .
                        ": " . implode("; ", $errorMsgArr);

                    if ($testing == false) {
                        $userSecUtil->createUserEditEvent(
                            $this->container->getParameter('fellapp.sitename'),
                            $event,
                            $systemUser,
                            null,
                            null,
                            'Fellowship Application Creation Failed');
                    }

                    $logger->error($event);

                    //send email
                    //$sendErrorEmail = true;
                    $sendErrorEmail = false;
                    if ($sendErrorEmail) {
                        $userSecUtil = $this->container->get('user_security_utility');
                        $emails = $userSecUtil->getUserEmailsByRole($this->container->getParameter('fellapp.sitename'), "Administrator");
                        $ccs = $userSecUtil->getUserEmailsByRole($this->container->getParameter('fellapp.sitename'), "Platform Administrator");
                        if (!$emails) {
                            $emails = $ccs;
                            $ccs = null;
                        }
                        $emailUtil->sendEmail($emails, $subjectError, $event, $ccs);
                        $this->sendEmailToSystemEmail($subjectError, $event);
                    }

                    //continue; //skip this fell application, because getFellowshipSubspecialty is null => something is wrong
                    return null;
                }
            } else {
                $logger->error("Not live server:" . "No Erroneous spreadsheet deleted from server: inputFileName=" . $inputFileName);
            }
            //////////////////// EOF second validate the application //////////////////////

            //exit('end applicant');

            if ($testing == false) {
                $em->persist($user);
                $em->flush();
            }

            //everything looks fine => remove creation attempt log
            $em->remove($eventLogAttempt);
            if ($testing == false) {
                $em->flush();
            }

            $event = "Populated fellowship applicant " . $displayName . "; Application ID " . $fellowshipApplication->getId();
            if ($testing == false) {
                $userSecUtil->createUserEditEvent($this->container->getParameter('fellapp.sitename'), $event, $systemUser, $fellowshipApplication, null, 'Fellowship Application Created');
            }

            //add application pdf generation to queue
            $fellappRepGen = $this->container->get('fellapp_reportgenerator');
            $fellappRepGen->addFellAppReportToQueue($fellowshipApplication->getId());

            $logger->notice($event);

            //send confirmation email to this applicant for prod server
            $environment = $userSecUtil->getSiteSettingParameter('environment');
            if ($environment == 'live') {
                //send confirmation email to this applicant
                //$confirmationEmailFellApp = $userSecUtil->getSiteSettingParameter('confirmationEmailFellApp');
                $confirmationEmailFellApp = $userSecUtil->getSiteSettingParameter('confirmationEmailFellApp', $this->container->getParameter('fellapp.sitename'));
                $confirmationSubjectFellApp = $userSecUtil->getSiteSettingParameter('confirmationSubjectFellApp', $this->container->getParameter('fellapp.sitename'));
                $confirmationBodyFellApp = $userSecUtil->getSiteSettingParameter('confirmationBodyFellApp', $this->container->getParameter('fellapp.sitename'));
                //$logger->notice("Before Send confirmation email to " . $email . " from " . $confirmationEmailFellApp);
                if ($email && $confirmationEmailFellApp && $confirmationSubjectFellApp && $confirmationBodyFellApp) {
                    $logger->notice("Send confirmation email (fellowship application " . $fellowshipApplication->getId() . " populated in DB) to the applicant email " . $email . " from " . $confirmationEmailFellApp);
                    $emailUtil->sendEmail($email, $confirmationSubjectFellApp, $confirmationBodyFellApp, null, $confirmationEmailFellApp);
                } else {
                    $logger->error("ERROR: confirmation email has not been sent (fellowship application " . $fellowshipApplication->getId() . " populated in DB) to the applicant email " . $email . " from " . $confirmationEmailFellApp);

                }

            }//if live

            if ($environment == 'live') {
                //send confirmation email to the corresponding Fellowship director and coordinator
                $fellappUtil = $this->container->get('fellapp_util');
                $fellappUtil->sendConfirmationEmailsOnApplicationPopulation($fellowshipApplication, $user);
            }

            //create reference hash ID. Must run after fellowship is in DB and has IDs
            $fellappRecLetterUtil->generateFellappRecLetterId($fellowshipApplication, true);
            if ($environment == 'live') {
                // send invitation email to upload recommendation letter to references
                $fellappRecLetterUtil->sendInvitationEmailsToReferences($fellowshipApplication, true);
            }

            //delete: imported rows from the sheet on Google Drive and associated uploaded files from the Google Drive.
            if ($deleteSourceRow) {

                $userSecUtil = $this->container->get('user_security_utility');
                $deleteImportedAplicationsFellApp = $userSecUtil->getSiteSettingParameter('deleteImportedAplicationsFellApp', $this->container->getParameter('fellapp.sitename'));
                if ($deleteImportedAplicationsFellApp) {

                    //$backupFileIdFellApp = $userSecUtil->getSiteSettingParameter('backupFileIdFellApp');
                    $backupFileIdFellApp = $googleSheetManagement->getGoogleConfigParameter('felBackupTemplateFileId');
                    if ($backupFileIdFellApp) {
                        $googleSheetManagement = $this->container->get('fellapp_googlesheetmanagement');
                        $rowId = $fellowshipApplication->getGoogleFormId();

                        $worksheet = $googleSheetManagement->getSheetByFileId($backupFileIdFellApp);

                        $deletedRows = $googleSheetManagement->deleteImportedApplicationAndUploadsFromGoogleDrive($worksheet, $rowId);

                        if ($deletedRows) {
                            $event = "Fellowship Application (and all uploaded files) with Google Applicant ID=" . $googleFormId . " Application ID " . $fellowshipApplication->getId() . " has been successful deleted from Google Drive";
                            $eventTypeStr = "Deleted Fellowship Application Backup From Google Drive";
                        } else {
                            $event = "Error: Fellowship Application with Google Applicant ID=" . $googleFormId . " Application ID " . $fellowshipApplication->getId() . "failed to delete from Google Drive";
                            $eventTypeStr = "Failed Deleted Fellowship Application Backup From Google Drive";
                        }

                        if ($testing == false) {
                            $userSecUtil->createUserEditEvent($this->container->getParameter('fellapp.sitename'), $event, $systemUser, $fellowshipApplication, null, $eventTypeStr);
                        }
                        $logger->notice($event);

                    }//if

                }

            }

            //$count++;
            if ($fellowshipApplication && !$populatedFellowshipApplications->contains($fellowshipApplication)) {
                $populatedFellowshipApplications->add($fellowshipApplication);
            }

            //exit( 'Test: end of fellowship applicant id='.$fellowshipApplication->getId() );

        } catch (\Doctrine\DBAL\DBALException $e) {
            $event = "Error creating fellowship applicant with unique Google Applicant ID=" . $googleFormId . "; Exception=" . $e->getMessage();
            //$emailUtil->sendEmail( $emails, $subjectError, $event );
            $this->sendEmailToSystemEmail($subjectError, $event);

            //logger
            $logger->error($event);

            //flash
            $userUtil = $this->container->get('user_utility');
            if ($userUtil->getSession()) {
                $userUtil->getSession()->getFlashBag()->add(
                    'warning',
                    $event
                );
            }
        } //try/catch

        ///////////////// parse field ///////////////////

        return true;
    }//parseFields

    public function uploadGoogleDocuments( $fellowshipApplication, $rowData, $headers ) {
        $userSecUtil = $this->container->get('user_security_utility');
        $googlesheetmanagement = $this->container->get('fellapp_googlesheetmanagement');
        $logger = $this->container->get('logger');

        $applicantsUploadPathFellApp = $userSecUtil->getSiteSettingParameter(
            'applicantsUploadPathFellApp',
            $this->container->getParameter('fellapp.sitename')
        );
        if( !$applicantsUploadPathFellApp ) {
            $applicantsUploadPathFellApp = "FellowshipApplicantUploads";
            $logger->warning('applicantsUploadPathFellApp is not defined in Fellowship Site Parameters. Use default "'.
                $applicantsUploadPathFellApp.'" folder.');
        }
        $uploadPath = $this->uploadDir.'/'.$applicantsUploadPathFellApp;

        $systemUser = $userSecUtil->findSystemUser();

        $service = $googlesheetmanagement->getGoogleService();
        if( !$service ) {
            $event = "Google API service failed!";
            $logger->error($event);
            $this->sendEmailToSystemEmail($event, $event);
            $logger->error($event. " while processing GoogleFormId=".$fellowshipApplication->getGoogleFormId());
            return false;
        }

        //uploadedPhotoUrl
        $uploadedPhotoUrl = $this->getValueByHeaderName('uploadedPhotoUrl', $rowData, $headers);
        $uploadedPhotoId = $this->getFileIdByUrl($uploadedPhotoUrl);
        if ($uploadedPhotoId) {
            $uploadedPhotoDb = $googlesheetmanagement->downloadFileToServer($systemUser, $service, $uploadedPhotoId, 'Fellowship Photo', $uploadPath);
            if (!$uploadedPhotoDb) {
                throw new IOException('Unable to download file to server: uploadedPhotoUrl=' . $uploadedPhotoUrl . ', fileDB=' . $uploadedPhotoDb);
            }
            //$user->setAvatar($uploadedPhotoDb); //set this file as Avatar
            $fellowshipApplication->addAvatar($uploadedPhotoDb);
        }

        //uploadedCVUrl
        $uploadedCVUrl = $this->getValueByHeaderName('uploadedCVUrl', $rowData, $headers);
        $uploadedCVUrlId = $this->getFileIdByUrl($uploadedCVUrl);
        if ($uploadedCVUrlId) {
            $uploadedCVUrlDb = $googlesheetmanagement->downloadFileToServer($systemUser, $service, $uploadedCVUrlId, 'Fellowship CV', $uploadPath);
            if (!$uploadedCVUrlDb) {
                throw new IOException('Unable to download file to server: uploadedCVUrl=' . $uploadedCVUrl . ', fileDB=' . $uploadedCVUrlDb);
            }
            $fellowshipApplication->addCv($uploadedCVUrlDb);
        }

        //uploadedCoverLetterUrl
        $uploadedCoverLetterUrl = $this->getValueByHeaderName('uploadedCoverLetterUrl', $rowData, $headers);
        $uploadedCoverLetterUrlId = $this->getFileIdByUrl($uploadedCoverLetterUrl);
        if ($uploadedCoverLetterUrlId) {
            $uploadedCoverLetterUrlDb = $googlesheetmanagement->downloadFileToServer($systemUser, $service, $uploadedCoverLetterUrlId, 'Fellowship Cover Letter', $uploadPath);
            if (!$uploadedCoverLetterUrlDb) {
                throw new IOException('Unable to download file to server: uploadedCoverLetterUrl=' . $uploadedCoverLetterUrl . ', fileDB=' . $uploadedCoverLetterUrlDb);
            }
            $fellowshipApplication->addCoverLetter($uploadedCoverLetterUrlDb);
        }

        $examination = new Examination($systemUser);
        $fellowshipApplication->addExamination($examination);
        //uploadedUSMLEScoresUrl
        $uploadedUSMLEScoresUrl = $this->getValueByHeaderName('uploadedUSMLEScoresUrl', $rowData, $headers);
        $uploadedUSMLEScoresUrlId = $this->getFileIdByUrl($uploadedUSMLEScoresUrl);
        if ($uploadedUSMLEScoresUrlId) {
            $uploadedUSMLEScoresUrlDb = $googlesheetmanagement->downloadFileToServer($systemUser, $service, $uploadedUSMLEScoresUrlId, 'Fellowship USMLE Scores', $uploadPath);
            if (!$uploadedUSMLEScoresUrlDb) {
                throw new IOException('Unable to download file to server: uploadedUSMLEScoresUrl=' . $uploadedUSMLEScoresUrl . ', fileDB=' . $uploadedUSMLEScoresUrlDb);
            }
            $examination->addScore($uploadedUSMLEScoresUrlDb);
        }

        /////////
        //suspendedLicensure
        $fellowshipApplication->setReprimand($this->getValueByHeaderName('suspendedLicensure', $rowData, $headers));
        //uploadedReprimandExplanationUrl
        $uploadedReprimandExplanationUrl = $this->getValueByHeaderName('uploadedReprimandExplanationUrl', $rowData, $headers);
        $uploadedReprimandExplanationUrlId = $this->getFileIdByUrl($uploadedReprimandExplanationUrl);
        if ($uploadedReprimandExplanationUrlId) {
            $uploadedReprimandExplanationUrlDb = $googlesheetmanagement->downloadFileToServer($systemUser, $service, $uploadedReprimandExplanationUrlId, 'Fellowship Reprimand', $uploadPath);
            if (!$uploadedReprimandExplanationUrlDb) {
                throw new IOException('Unable to download file to server: uploadedReprimandExplanationUrl=' . $uploadedReprimandExplanationUrl . ', fileID=' . $uploadedReprimandExplanationUrlDb->getId());
            }
            $fellowshipApplication->addReprimandDocument($uploadedReprimandExplanationUrlDb);
        }

        //legalSuit
        $fellowshipApplication->setLawsuit($this->getValueByHeaderName('legalSuit', $rowData, $headers));
        //uploadedLegalExplanationUrl
        $uploadedLegalExplanationUrl = $this->getValueByHeaderName('uploadedLegalExplanationUrl', $rowData, $headers);
        $uploadedLegalExplanationUrlId = $this->getFileIdByUrl($uploadedLegalExplanationUrl);
        if ($uploadedLegalExplanationUrlId) {
            $uploadedLegalExplanationUrlDb = $googlesheetmanagement->downloadFileToServer($systemUser, $service, $uploadedLegalExplanationUrlId, 'Fellowship Legal Suit', $uploadPath);
            if (!$uploadedLegalExplanationUrlDb) {
                throw new IOException('Unable to download file to server: uploadedLegalExplanationUrl=' . $uploadedLegalExplanationUrl . ', fileID=' . $uploadedLegalExplanationUrlDb->getId());
            }
            $fellowshipApplication->addReprimandDocument($uploadedLegalExplanationUrlDb);
        }
        //////////

        $res = array(
            'examination' => $examination
        );

        return $res;
    }

    public function createFellAppReference($em,$author,$typeStr,$rowData,$headers,$testOnly=false) {

        //recommendation1Name	recommendation1Title	recommendation1Institution	recommendation1AddressStreet1
        //recommendation1AddressStreet2	recommendation1AddressCity	recommendation1AddressState	recommendation1AddressZip	recommendation1AddressCountry

        $recommendationFirstName = $this->getValueByHeaderName($typeStr."FirstName",$rowData,$headers);
        $recommendationLastName = $this->getValueByHeaderName($typeStr."LastName",$rowData,$headers);

        //echo "recommendationFirstName=".$recommendationFirstName."<br>";
        //echo "recommendationLastName=".$recommendationLastName."<br>";

        if( !$recommendationFirstName && !$recommendationLastName ) {
            //echo "no ref<br>";
            return null;
        }

        if( $testOnly ) {
            return true;
        }

        //Capitalize
        if( $recommendationFirstName ) {
            $recommendationFirstName = $this->capitalizeIfNotAllCapital($recommendationFirstName);
        }
        if( $recommendationLastName ) {
            $recommendationLastName = $this->capitalizeIfNotAllCapital($recommendationLastName);
        }

        $reference = new Reference($author);

        //recommendation1FirstName
        $reference->setFirstName($recommendationFirstName);

        //recommendation1LastName
        $reference->setName($recommendationLastName);

        //recommendation1Degree
        $recommendationDegree = $this->getValueByHeaderName($typeStr."Degree",$rowData,$headers);
        if( $recommendationDegree ) {
            $reference->setDegree($recommendationDegree);
        }

        //recommendation1Title
        $recommendationTitle = $this->getValueByHeaderName($typeStr."Title",$rowData,$headers);
        if( $recommendationTitle ) {
            $reference->setTitle($recommendationTitle);
        }

        //recommendation1Email
        $recommendationEmail = $this->getValueByHeaderName($typeStr."Email",$rowData,$headers);
        if( $recommendationEmail ) {
            $reference->setEmail($recommendationEmail);
        }

        //recommendation1Phone
        $recommendationPhone = $this->getValueByHeaderName($typeStr."Phone",$rowData,$headers);
        if( $recommendationPhone ) {
            $reference->setPhone($recommendationPhone);
        }

        $instStr = $this->getValueByHeaderName($typeStr."Institution",$rowData,$headers);
        if( $instStr ) {
            $params = array('type'=>'Educational');
            $instStr = trim((string)$instStr);
            $instStr = $this->capitalizeIfNotAllCapital($instStr);
            $transformer = new GenericTreeTransformer($em, $author, 'Institution', null, $params);
            $instEntity = $transformer->reverseTransform($instStr);
            $reference->setInstitution($instEntity);
        }

        $geoLocation = $this->createGeoLocation($em,$author,$typeStr."Address",$rowData,$headers);
        if( $geoLocation ) {
            $reference->setGeoLocation($geoLocation);
        }

//        //generate hash ID
//        $this->generateRecLetterId($reference);

        return $reference;
    }

    public function createGeoLocation($em,$author,$typeStr,$rowData,$headers) {

        $geoLocationStreet1 = $this->getValueByHeaderName($typeStr.'Street1',$rowData,$headers);
        $geoLocationStreet2 = $this->getValueByHeaderName($typeStr.'Street2',$rowData,$headers);
        //echo "geoLocationStreet1=".$geoLocationStreet1."<br>";
        //echo "geoLocationStreet2=".$geoLocationStreet2."<br>";

        if( !$geoLocationStreet1 && !$geoLocationStreet2 ) {
            //echo "no geoLocation<br>";
            return null;
        }

        $geoLocation = new GeoLocation();
        //popuilate geoLocation
        $geoLocation->setStreet1($this->getValueByHeaderName($typeStr.'Street1',$rowData,$headers));
        $geoLocation->setStreet2($this->getValueByHeaderName($typeStr.'Street2',$rowData,$headers));
        $geoLocation->setZip($this->getValueByHeaderName($typeStr.'Zip',$rowData,$headers));
        //presentAddressCity
        $presentAddressCity = $this->getValueByHeaderName($typeStr.'City',$rowData,$headers);
        if( $presentAddressCity ) {
            $presentAddressCity = trim((string)$presentAddressCity);
            $transformer = new GenericTreeTransformer($em, $author, 'CityList');
            $presentAddressCityEntity = $transformer->reverseTransform($presentAddressCity);
            $geoLocation->setCity($presentAddressCityEntity);
        }
        //presentAddressState
        $presentAddressState = $this->getValueByHeaderName($typeStr.'State',$rowData,$headers);
        if( $presentAddressState ) {
            $presentAddressState = trim((string)$presentAddressState);
            $transformer = new GenericTreeTransformer($em, $author, 'States');
            $presentAddressStateEntity = $transformer->reverseTransform($presentAddressState);
            $geoLocation->setState($presentAddressStateEntity);
        }
        //presentAddressCountry
        $presentAddressCountry = $this->getValueByHeaderName($typeStr.'Country',$rowData,$headers);
        if( $presentAddressCountry ) {
            $presentAddressCountry = trim((string)$presentAddressCountry);
            $transformer = new GenericTreeTransformer($em, $author, 'Countries');
            $presentAddressCountryEntity = $transformer->reverseTransform($presentAddressCountry);
            $geoLocation->setCountry($presentAddressCountryEntity);
        }

        return $geoLocation;
    }

    public function transformDatestrToDate($datestr) {
        $userSecUtil = $this->container->get('user_security_utility');
        return $userSecUtil->transformDatestrToDateWithSiteEventLog($datestr,$this->container->getParameter('fellapp.sitename'));
    }

    public function createFellAppBoardCertification($em,$fellowshipApplication,$author,$typeStr,$rowData,$headers) {

        $boardCertificationIssueDate = $this->getValueByHeaderName($typeStr.'Date',$rowData,$headers);
        if( !$boardCertificationIssueDate ) {
            return null;
        }

        $boardCertification = new BoardCertification($author);
        $fellowshipApplication->addBoardCertification($boardCertification);
        $fellowshipApplication->getUser()->getCredentials()->addBoardCertification($boardCertification);

        //boardCertification1Board
        $boardCertificationBoard = $this->getValueByHeaderName($typeStr.'Board',$rowData,$headers);
        if( $boardCertificationBoard ) {
            $boardCertificationBoard = trim((string)$boardCertificationBoard);
            $transformer = new GenericTreeTransformer($em, $author, 'CertifyingBoardOrganization');
            $CertifyingBoardOrganizationEntity = $transformer->reverseTransform($boardCertificationBoard);
            $boardCertification->setCertifyingBoardOrganization($CertifyingBoardOrganizationEntity);
        }

        //boardCertification1Area => BoardCertifiedSpecialties
        $boardCertificationArea = $this->getValueByHeaderName($typeStr.'Area',$rowData,$headers);
        if( $boardCertificationArea ) {
            $boardCertificationArea = trim((string)$boardCertificationArea);
            $transformer = new GenericTreeTransformer($em, $author, 'BoardCertifiedSpecialties');
            $boardCertificationAreaEntity = $transformer->reverseTransform($boardCertificationArea);
            $boardCertification->setSpecialty($boardCertificationAreaEntity);
        }

        //boardCertification1Date
        $boardCertification->setIssueDate($this->transformDatestrToDate($boardCertificationIssueDate));

        return $boardCertification;
    }

    public function createFellAppMedicalLicense($em,$fellowshipApplication,$author,$typeStr,$rowData,$headers) {

        //medicalLicensure1Country	medicalLicensure1State	medicalLicensure1DateIssued	medicalLicensure1Number	medicalLicensure1Active

        $licenseNumber = $this->getValueByHeaderName($typeStr.'Number',$rowData,$headers);
        $licenseIssuedDate = $this->getValueByHeaderName($typeStr.'DateIssued',$rowData,$headers);

        if( !$licenseNumber && !$licenseIssuedDate ) {
            return null;
        }

        $license = new StateLicense($author);
        $fellowshipApplication->addStateLicense($license);
        $fellowshipApplication->getUser()->getCredentials()->addStateLicense($license);

        //medicalLicensure1DateIssued
        $license->setLicenseIssuedDate($this->transformDatestrToDate($licenseIssuedDate));

        //medicalLicensure1Active
        $medicalLicensureActive = $this->getValueByHeaderName($typeStr.'Active',$rowData,$headers);
        if( $medicalLicensureActive ) {
            $transformer = new GenericTreeTransformer($em, $author, 'MedicalLicenseStatus');
            $medicalLicensureActiveEntity = $transformer->reverseTransform($medicalLicensureActive);
            $license->setActive($medicalLicensureActiveEntity);
        }

        //medicalLicensure1Country
        $medicalLicensureCountry = $this->getValueByHeaderName($typeStr.'Country',$rowData,$headers);
        if( $medicalLicensureCountry ) {
            $medicalLicensureCountry = trim((string)$medicalLicensureCountry);
            $transformer = new GenericTreeTransformer($em, $author, 'Countries');
            $medicalLicensureCountryEntity = $transformer->reverseTransform($medicalLicensureCountry);
            //echo "MedCountry=".$medicalLicensureCountryEntity.", ID+".$medicalLicensureCountryEntity->getId()."<br>";
            $license->setCountry($medicalLicensureCountryEntity);
        }

        //medicalLicensure1State
        $medicalLicensureState = $this->getValueByHeaderName($typeStr.'State',$rowData,$headers);
        if( $medicalLicensureState ) {
            $medicalLicensureState = trim((string)$medicalLicensureState);
            $transformer = new GenericTreeTransformer($em, $author, 'States');
            $medicalLicensureStateEntity = $transformer->reverseTransform($medicalLicensureState);
            //echo "MedState=".$medicalLicensureStateEntity."<br>";
            $license->setState($medicalLicensureStateEntity);
        }

        //medicalLicensure1Number
        $license->setLicenseNumber($licenseNumber);

        return $license;
    }

    public function createFellAppTraining($em,$fellowshipApplication,$author,$typeStr,$rowData,$headers,$orderinlist) {

        //Start
        $trainingStart = $this->getValueByHeaderName($typeStr.'Start',$rowData,$headers);
        //End
        $trainingEnd = $this->getValueByHeaderName($typeStr.'End',$rowData,$headers);

        if( !$trainingStart && !$trainingEnd ) {
            return null;
        }

        $training = new Training($author);
        $training->setOrderinlist($orderinlist);
        $fellowshipApplication->addTraining($training);
        $fellowshipApplication->getUser()->addTraining($training);

        //set TrainingType
        if( $typeStr == 'undergraduateSchool' ) {
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:TrainingTypeList'] by [TrainingTypeList::class]
            $trainingType = $em->getRepository(TrainingTypeList::class)->findOneByName('Undergraduate');
            $training->setTrainingType($trainingType);
        }
        if( $typeStr == 'graduateSchool' ) {
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:TrainingTypeList'] by [TrainingTypeList::class]
            $trainingType = $em->getRepository(TrainingTypeList::class)->findOneByName('Graduate');
            $training->setTrainingType($trainingType);
        }
        if( strpos((string)$typeStr,'medical') !== false ) {
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:TrainingTypeList'] by [TrainingTypeList::class]
            $trainingType = $em->getRepository(TrainingTypeList::class)->findOneByName('Medical');
            $training->setTrainingType($trainingType);
        }
        if( strpos((string)$typeStr,'residency') !== false ) {
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:TrainingTypeList'] by [TrainingTypeList::class]
            $trainingType = $em->getRepository(TrainingTypeList::class)->findOneByName('Residency');
            $training->setTrainingType($trainingType);
        }
        if( strpos((string)$typeStr,'gme1') !== false ) {
            //Post-Residency Fellowship
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:TrainingTypeList'] by [TrainingTypeList::class]
            $trainingType = $em->getRepository(TrainingTypeList::class)->findOneByName('Post-Residency Fellowship');
            $training->setTrainingType($trainingType);
        }
        if( strpos((string)$typeStr,'gme2') !== false ) {
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:TrainingTypeList'] by [TrainingTypeList::class]
            $trainingType = $em->getRepository(TrainingTypeList::class)->findOneByName('GME');
            $training->setTrainingType($trainingType);
        }
        if( strpos((string)$typeStr,'other') !== false ) {
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:TrainingTypeList'] by [TrainingTypeList::class]
            $trainingType = $em->getRepository(TrainingTypeList::class)->findOneByName('Other');
            $training->setTrainingType($trainingType);
        }

        $majorMatchString = $typeStr.'Major';
        $nameMatchString = $typeStr.'Name';

        if( strpos((string)$typeStr,'otherExperience') !== false ) {
            //otherExperience1Name => jobTitle
            $nameMatchString = null;
            $majorMatchString = null;
            $jobTitle = $this->getValueByHeaderName($typeStr.'Name',$rowData,$headers);
            $jobTitle = trim((string)$jobTitle);
            $transformer = new GenericTreeTransformer($em, $author, 'JobTitleList');
            $jobTitleEntity = $transformer->reverseTransform($jobTitle);
            $training->setJobTitle($jobTitleEntity);
        }

        if( strpos((string)$typeStr,'gme') !== false ) {
            //gme1Start	gme1End	gme1Name gme1Area
            //exception for Area: gmeArea => Major
            $majorMatchString = $typeStr.'Area';
        }

        if( strpos((string)$typeStr,'residency') !== false ) {
            //residencyStart	residencyEnd	residencyName	residencyArea
            //residencyArea => ResidencySpecialty
            $residencyArea = $this->getValueByHeaderName('residencyArea',$rowData,$headers);
            $transformer = new GenericTreeTransformer($em, $author, 'ResidencySpecialty');
            $residencyArea = trim((string)$residencyArea);
            $residencyAreaEntity = $transformer->reverseTransform($residencyArea);
            $training->setResidencySpecialty($residencyAreaEntity);
        }

        //Start
        $training->setStartDate($this->transformDatestrToDate($this->getValueByHeaderName($typeStr.'Start',$rowData,$headers)));

        //End
        $training->setCompletionDate($this->transformDatestrToDate($this->getValueByHeaderName($typeStr.'End',$rowData,$headers)));

        //City, Country, State
        $city = $this->getValueByHeaderName($typeStr.'City',$rowData,$headers);
        $country = $this->getValueByHeaderName($typeStr.'Country',$rowData,$headers);
        $state = $this->getValueByHeaderName($typeStr.'State',$rowData,$headers);

        if( $city || $country || $state ) {
            $trainingGeo = new GeoLocation();
            $training->setGeoLocation($trainingGeo);

            if( $city ) {
                $city = trim((string)$city);
                $transformer = new GenericTreeTransformer($em, $author, 'CityList');
                $cityEntity = $transformer->reverseTransform($city);
                $trainingGeo->setCity($cityEntity);
            }

            if( $country ) {
                $country = trim((string)$country);
                $transformer = new GenericTreeTransformer($em, $author, 'Countries');
                $countryEntity = $transformer->reverseTransform($country);
                $trainingGeo->setCountry($countryEntity);
            }

            if( $state ) {
                $state = trim((string)$state);
                $transformer = new GenericTreeTransformer($em, $author, 'States');
                $stateEntity = $transformer->reverseTransform($state);
                $trainingGeo->setState($stateEntity);
            }
        }

        //Name
        $schoolName = $this->getValueByHeaderName($nameMatchString,$rowData,$headers);
        if( $schoolName ) {
            $params = array('type'=>'Educational');
            $schoolName = trim((string)$schoolName);
            $schoolName = $this->capitalizeIfNotAllCapital($schoolName);
            $transformer = new GenericTreeTransformer($em, $author, 'Institution', null, $params);
            $schoolNameEntity = $transformer->reverseTransform($schoolName);
            $training->setInstitution($schoolNameEntity);
        }

        //Description
        $schoolDescription = $this->getValueByHeaderName($typeStr.'Description',$rowData,$headers);
        if( $schoolDescription ) {
            $schoolDescription = trim((string)$schoolDescription);
            $training->setDescription($schoolDescription);
        }

        //Major
        $schoolMajor = $this->getValueByHeaderName($majorMatchString,$rowData,$headers);
        if( $schoolMajor ) {
            $schoolMajor = trim((string)$schoolMajor);
            $transformer = new GenericTreeTransformer($em, $author, 'MajorTrainingList');
            $schoolMajorEntity = $transformer->reverseTransform($schoolMajor);
            $training->addMajor($schoolMajorEntity);
        }

        //Degree
        $schoolDegree = $this->getValueByHeaderName($typeStr.'Degree',$rowData,$headers);
        if( $schoolDegree ) {
            $schoolDegree = trim((string)$schoolDegree);
            $transformer = new GenericTreeTransformer($em, $author, 'TrainingDegreeList');
            $schoolDegreeEntity = $transformer->reverseTransform($schoolDegree);
            $training->setDegree($schoolDegreeEntity);
        }

        return $training;
    }



} 