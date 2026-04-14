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
use App\UserdirectoryBundle\Entity\FellowshipSubspecialty;
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
use Symfony\Component\HttpClient\HttpClient;

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
     * Caller (local) server: Create a single FellowshipApplication from a spreadsheet row
     */
    private function createFellappFromRow($rowData, $headers, $systemUser) {
        $logger = $this->container->get('logger');
        $userSecUtil = $this->container->get('user_security_utility');
        $fellappImportPopulateUtil = $this->container->get('fellapp_importpopulate_util');
        $fellappUtil = $this->container->get('fellapp_util');
        $testing = false;

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

        //Get retrieval method from fellapp site parameters
        $siteParam = $userSecUtil->getSpecificSiteSettingParameter($this->container->getParameter('fellapp.sitename'));
        $retrievalMethod = $siteParam->getRetrievalMethod();
        if( !$retrievalMethod ) {
            throw new EntityNotFoundException('Retrieval method is not set in the fellowship site settings');
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

        //check apiimportkeyglobal
        //$apiHashImportKeyGlobal = $this->getValueByHeaderName('apiimportkeyglobal', $rowData, $headers);
        $apiHashImportKeyGlobal = $this->getValueByHeaderName('apihashconnectionglobalkey', $rowData, $headers);
        if( $apiHashImportKeyGlobal ) {
            $apiHashImportKeyGlobal = trim($apiHashImportKeyGlobal);

//            $apiImportKeyGlobals = json_decode($apiImportKeyGlobal, true) ?? [];
//            //find local fellowship specialty  with $apiimportkeyglobal
//            $foundSpecialty = false;
//            foreach($apiImportKeyGlobals as $apiImportKeyGlobal) {
//                //$localSpecialty = $this->em->getRepository(FellowshipSubspecialty::class)->findOneByApiImportKeys($apiImportKeyGlobal);
//                //$localSpecialty = $this->em->getRepository(FellowshipSubspecialty::class)->findOneByName($apiImportKeyGlobal);
//                $localSpecialty = $this->em->getRepository(FellowshipSubspecialty::class)
//                    ->createQueryBuilder('fs')
//                    ->join('fs.apiImportKeys', 'k')
//                    ->where('k.name = :name')
//                    ->setParameter('name', $apiImportKeyGlobal)
//                    ->getQuery()
//                    ->getOneOrNullResult();
//
//                if( $localSpecialty ) {
//                    $foundSpecialty = true;
//                    break;
//                }
//            }
//            if( $foundSpecialty === false ) {
//                //echo 'Local FellowshipSubspecialty not found by API import key=[' . $apiImportKeyGlobal . ']'."<br>";
//                $logger->warning('Local FellowshipSubspecialty not found by API import key=[' . $apiImportKeyGlobal . ']');
//                return null;
//            }
//            $localSpecialty = $this->em->getRepository(FellowshipSubspecialty::class)
//                ->createQueryBuilder('fs')
//                //->join('fs.apiHashConnectionKey', 'k')
//                //->where('k.name = :name')
//                ->where('fs.apiConnectionKey = :name')
//                ->setParameter('name', $apiHashImportKeyGlobal)
//                ->getQuery()
//                ->getOneOrNullResult();

            $localSpecialty = $fellappUtil->findFellowshipSpeciatlyByApiHashKey($apiHashImportKeyGlobal);

            if( !$localSpecialty ) {
                //echo 'Local FellowshipSubspecialty not found by API import key=[' . $apiImportKeyGlobal . ']'."<br>";
                $logger->warning('Local FellowshipSubspecialty not found by API Hash import key=[' . $apiHashImportKeyGlobal . ']');
                return null;
            }
        }
        $logger->notice('Local FellowshipSubspecialty found by API import key=[' . $apiHashImportKeyGlobal . ']');

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

        echo "originalAppId=$originalAppId, emailCanonical=$emailCanonical, usernameCanonical=$usernameCanonical, primaryPublicUserId=$primaryPublicUserId <br>";

        //create logger which must be deleted on successefull creation of application
        $eventAttempt = "Attempt of creating Fellowship Applicant " . $displayName . " with unique Google Applicant ID=" . $googleFormId;
        if( $testing == false ) {
            //TODO: test delete $eventLogAttempt
            $eventLogAttempt = $userSecUtil->createUserEditEvent($this->container->getParameter('fellapp.sitename'), $eventAttempt, $systemUser, null, null, 'Fellowship Application Creation Failed');
        }

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
        $fellowshipApplication->setRetrievalMethod($retrievalMethod);
        //exit("after set originalAppId=$originalAppId");
        $user->addFellowshipApplication($fellowshipApplication);

        // Set timestamp
        if ($timestamp) {
            $fellowshipApplication->setTimestamp($this->transformDatestrToDate($timestamp));
        }

        //using the HASH values for each specialty - only download applications for which the HASH value for Fellowship Specialty matches
        // Fellowship Type
        $fellowshipType = $this->getValueByHeaderName('fellowshipType', $rowData, $headers);
        if ($fellowshipType) {
//            $fellowshipTypeEntity = $this->em->getRepository(FellowshipSubspecialty::class)
//                ->createQueryBuilder('fs')
//                ->join('fs.apiImportKeys', 'k')
//                ->where('k.name = :name')
//                ->setParameter('name', $apiImportKeyGlobal)
//                ->getQuery()
//                ->getOneOrNullResult();
//            if( $fellowshipTypeEntity ) {
//                if( strtolower(trim($fellowshipType)) != strtolower(trim($fellowshipTypeEntity->getName())) ) {
//                    $logger->warning('Matched API import key fellowship type found, but names are different[' .
//                        '$fellowshipType=['.trim($fellowshipType).']'.
//                        '$fellowshipTypeEntity->getName()=['.trim($fellowshipTypeEntity->getName()). ']'
//                    );
//                }
//                $logger->notice($fellowshipType.': Found $fellowshipTypeEntity=' . $fellowshipTypeEntity->getNameInstitution() . "]");
//                $fellowshipApplication->setFellowshipSubspecialty($fellowshipTypeEntity);
//            } else {
//                $logger->warning('Not found matched API import key=[' . $apiImportKeyGlobal.']');
//                return null;
//            }
//            $fellowshipTypeEntity = $this->em->getRepository(FellowshipSubspecialty::class)
//                ->createQueryBuilder('fs')
//                //->join('fs.apiHashConnectionKey', 'k')
//                //->where('k.name = :name')
//                ->where('fs.apiConnectionKey = :name')
//                ->setParameter('name', $apiHashImportKeyGlobal)
//                ->getQuery()
//                ->getOneOrNullResult();

            $fellowshipTypeEntity = $fellappUtil->findFellowshipSpeciatlyByApiHashKey($apiHashImportKeyGlobal);

            if( $fellowshipTypeEntity ) {
                if( strtolower(trim($fellowshipType)) != strtolower(trim($fellowshipTypeEntity->getName())) ) {
                    $logger->warning('Matched API import key fellowship type found, but names are different[' .
                        '$fellowshipType=['.trim($fellowshipType).']'.
                        '$fellowshipTypeEntity->getName()=['.trim($fellowshipTypeEntity->getName()). ']'
                    );
                }
                $logger->notice($fellowshipType.': Found $fellowshipTypeEntity=' . $fellowshipTypeEntity->getNameInstitution() . "]");
                $fellowshipApplication->setFellowshipSubspecialty($fellowshipTypeEntity);
            } else {
                $logger->warning('Not found matched API Hash import key=[' . $apiHashImportKeyGlobal.']');
                return null;
            }

//            if( $keyMatch ) {
//                $fellowshipApplication->setFellowshipSubspecialty($fellowshipTypeEntity);
//            } else {
//                $logger->warning('Not found matched API import key=[' . $apiImportKeyGlobal.']');
//                return null;
//            }
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

        // Examination
        $examination = new Examination($systemUser); //create new Examination
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

        // Document URLs (will need to be downloaded separately - just storing URLs for now)
        //Use documenthash to check if file already exists
        //$uploadedPhotoUrl = $this->getValueByHeaderName('uploadedPhotoUrl', $rowData, $headers);
        //$uploadedCVUrl = $this->getValueByHeaderName('uploadedCVUrl', $rowData, $headers);
        //$uploadedCoverLetterUrl = $this->getValueByHeaderName('uploadedCoverLetterUrl', $rowData, $headers);
        //public function downloadRemoteDocuments( $fellowshipApplication, $rowData, $headers )
        //Use uploadGoogleDocuments
        $this->downloadRemoteDocuments($fellowshipApplication,$rowData,$headers,$examination); //create new Examination

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
        //$ref4 = $this->createFellAppReference($this->em, $systemUser, 'recommendation4', $rowData, $headers);
        //if ($ref4) {
        //    $fellowshipApplication->addReference($ref4);
        //}

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

        //everything looks fine => remove creation attempt log
        //TODO: test all below
        if( $testing == false ) {
            $this->em->remove($eventLogAttempt);
            if ($testing == false) {
                $this->em->flush();
            }
        }

        $event = "Populated fellowship applicant " . $displayName . "; Application ID " . $fellowshipApplication->getId();
        if( $testing == false ) {
            $userSecUtil->createUserEditEvent($this->container->getParameter('fellapp.sitename'), $event, $systemUser, $fellowshipApplication, null, 'Fellowship Application Created');
        }

        //add application pdf generation to queue
        $fellappRepGen = $this->container->get('fellapp_reportgenerator');
        $fellappRepGen->addFellAppReportToQueue( $fellowshipApplication->getId() );

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
                $emailUtil = $this->container->get('user_mailer_utility');
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
        //$fellappRecLetterUtil = $this->container->get('fellapp_rec_letter_util');
        //$fellappRecLetterUtil->generateFellappRecLetterId($fellowshipApplication,true);

        if( $environment == 'live' ) {
            // send invitation email to upload recommendation letter to references
            $fellappRecLetterUtil = $this->container->get('fellapp_rec_letter_util');
            $fellappRecLetterUtil->sendInvitationEmailsToReferences($fellowshipApplication,true);
        }

        //if( $deleteSourceRow ) {
        //
        //}

        return $fellowshipApplication;
    }//createFellappFromRow


    //Run on Local server to download a document from remote server
    public function downloadRemoteDocuments($fellowshipApplication,$rowData,$headers,$examination) {
        $logger = $this->container->get('logger');
        $userSecUtil = $this->container->get('user_security_utility');
        $fellappImportPopulateHubUtil = $this->container->get('fellapp_importpopulate_hub_util');
        //$fellappUtil = $this->container->get('fellapp_util');

        $systemUser = $userSecUtil->findSystemUser();

        // Get storage path
        $applicantsUploadPathFellApp = $userSecUtil->getSiteSettingParameter(
            'applicantsUploadPathFellApp',
            $this->container->getParameter('fellapp.sitename')
        );
        if( !$applicantsUploadPathFellApp ) {
            $applicantsUploadPathFellApp = "FellowshipApplicantUploads";
        }
        $storagePath = $this->container->get('kernel')->getProjectDir() . '/public/Uploaded/fellapp/' . $applicantsUploadPathFellApp;

        // Get remote server URL from site settings
        $remoteUrl = $userSecUtil->getSiteSettingParameter(
            'hubServerApiUrl',
            $this->container->getParameter('fellapp.sitename'));
        if( !$remoteUrl ) {
            $logger->warning('fellappRemoteServerUrl is not defined in Site Parameters. Cannot download remote documents.');
            return false;
        }
        //$remoteUrl = https://view.online/fellowship-applications/download-application-data
        //Get $remoteBaseUrl=https://view.online
        $parts = parse_url($remoteUrl);
        $remoteBaseUrl = $parts['scheme'] . '://' . $parts['host'];

        $localApiConnectionHashKey = null;
        //$apiHashConnectionKey = $fellappImportPopulateHubUtil->getInstitutionApiHashConnectionKey();
        $localApiConnectionKey = $fellappImportPopulateHubUtil->getInstitutionApiConnectionKey(); //Run on Local server
        //exit('$localApiConnectionKey='.$localApiConnectionKey);

        if( $localApiConnectionKey ) {
            $localApiConnectionHashKey = hash('sha256', $localApiConnectionKey);
        } else {
            $logger->warning('downloadRemoteDocuments: Local apiConnectionKey is empty');
            return false;
        }

        // Document types to download with their corresponding row field names and attachment methods
        $documentTypes = [
            [
                'urlField' => 'uploadedPhotoUrl',
                'hashField' => 'uploadedPhotoHash',
                'docType' => 'Fellowship Photo',
                'attachMethod' => 'addAvatar'
            ],
            [
                'urlField' => 'uploadedCVUrl',
                'hashField' => 'uploadedCVHash',
                'docType' => 'Fellowship CV',
                'attachMethod' => 'addCv'
            ],
            [
                'urlField' => 'uploadedCoverLetterUrl',
                'hashField' => 'uploadedCoverLetterHash',
                'docType' => 'Fellowship Cover Letter',
                'attachMethod' => 'addCoverLetter'
            ],
            [
                'urlField' => 'uploadedUSMLEScoresUrl',
                'hashField' => 'uploadedUSMLEScoresHash',
                'docType' => 'Fellowship USMLE Scores',
                'attachMethod' => 'addScore',
                'attachTo' => 'examination'
            ],
            [
                'urlField' => 'uploadedReprimandExplanationUrl',
                'hashField' => 'uploadedReprimandExplanationHash',
                'docType' => 'Fellowship Reprimand',
                'attachMethod' => 'addReprimandDocument'
            ],
            [
                'urlField' => 'uploadedLegalExplanationUrl',
                'hashField' => 'uploadedLegalExplanationHash',
                'docType' => 'Fellowship Legal Suit',
                'attachMethod' => 'addReprimandDocument'
            ]
        ];

        //$examination = null;

        foreach ($documentTypes as $docConfig) {
            $fileUrl = $this->getValueByHeaderName($docConfig['urlField'], $rowData, $headers);
            $fileHash = $this->getValueByHeaderName($docConfig['hashField'], $rowData, $headers);

            if (!$fileUrl || !$fileHash) {
                continue; // Skip if no URL or hash provided
            }

            // Check if document already exists locally by hash
            $existingDoc = $this->em->getRepository(Document::class)->findOneByDocumentHash($fileHash);
            if ($existingDoc) {
                $logger->notice('Skipping download: Document '.$docConfig['docType'].', ID='.$existingDoc->getId().' with hash ' . $fileHash . ' already exists locally.');
                // Attach existing document to fellowship application
                //$this->attachDocumentToFellowship($fellowshipApplication, $existingDoc, $docConfig, $examination);
                $this->attachDocumentToFellowship($fellowshipApplication, $existingDoc, $docConfig, $examination);
                continue;
            }

            // Download file from remote server
            try {
                $document = $this->downloadFileFromRemote(
                    $fileUrl,
                    $fileHash,
                    $docConfig['docType'],
                    $storagePath,
                    $systemUser,
                    $remoteBaseUrl,
                    $localApiConnectionHashKey
                );

                if ($document) {
                    // Attach document to fellowship application
                    //$this->attachDocumentToFellowship($fellowshipApplication, $document, $docConfig, $examination);
                    $this->attachDocumentToFellowship($fellowshipApplication, $document, $docConfig, $examination);
                    $logger->notice('Downloaded and attached docType=' . $docConfig['docType'] . ' for application ID ' . $fellowshipApplication->getId());
                }
            } catch (\Exception $e) {
                $logger->error('Error downloading ' . $docConfig['docType'] . ': ' . $e->getMessage());
            }
        }

        return true;
    }

    /**
     * Attach a document to the FellowshipApplication based on configuration
     */
    private function attachDocumentToFellowship($fellowshipApplication, $document, $docConfig, $examination) {
        $logger = $this->container->get('logger');
        //return; //testing

        //check if document is valid (has ID)
        if( $document && $document->getId() ) {
            //ok
            $logger->notice("attachDocumentToFellowship: add document ID=".$document->getId());
        } else {
            $logger->notice("attachDocumentToFellowship: skip document without ID");
        }

        $attachMethod = $docConfig['attachMethod'];

        if (isset($docConfig['attachTo']) && $docConfig['attachTo'] === 'examination') {
            // For examination documents (USMLE scores)
//            $examinations = $fellowshipApplication->getExaminations();
//            $logger->notice("attachDocumentToFellowship: examination count=".count($examinations));
//            if( count($examinations) > 0 ) {
//                $examination = $examinations->first();
//            }
            if( !$examination ) {
                $logger->notice("attachDocumentToFellowship: create new examination");
                $systemUser = $this->container->get('user_security_utility')->findSystemUser();
                $examination = new Examination($systemUser);
                $fellowshipApplication->addExamination($examination);
            }
            $logger->notice("attachDocumentToFellowship: add document to examination docType=".$docConfig['docType']);
            $examination->$attachMethod($document);
        } else {
            // For regular fellowship application documents
            $logger->notice("attachDocumentToFellowship: add regular document docType=".$docConfig['docType']);
            $fellowshipApplication->$attachMethod($document);
        }
        $logger->notice("attachDocumentToFellowship: holder examination count=".count($fellowshipApplication->getExaminations()));
    }
//    private function attachDocumentToFellowship_ORIG($fellowshipApplication, $document, $docConfig, &$examination) {
//        $attachMethod = $docConfig['attachMethod'];
//
//        if (isset($docConfig['attachTo']) && $docConfig['attachTo'] === 'examination') {
//            // For examination documents (USMLE scores)
//            if (!$examination) {
//                $examination = $fellowshipApplication->getExaminations()->first();
//                if (!$examination) {
//                    $systemUser = $this->container->get('user_security_utility')->findSystemUser();
//                    $examination = new \App\UserdirectoryBundle\Entity\Examination($systemUser);
//                    $fellowshipApplication->addExamination($examination);
//                }
//            }
//            $examination->$attachMethod($document);
//        } else {
//            // For regular fellowship application documents
//            $fellowshipApplication->$attachMethod($document);
//        }
//    }

    /**
     * Download a file from the remote server using HMAC authentication
     */
    private function downloadFileFromRemote($fileUrl, $fileHash, $documentType, $storagePath, $systemUser, $remoteBaseUrl, $secretKey) {
        $logger = $this->container->get('logger');

        // Generate HMAC for authentication
        $timestamp = time();
        $hmac = hash_hmac('sha256', 'fellapp-api:' . $timestamp, $secretKey);

        // Construct API URL $remoteBaseUrl=https://view.online
        $apiUrl = $remoteBaseUrl . '/fellowship-applications/download-application-file?document_hash=' . urlencode($fileHash);

        // Make API request with authentication headers
        //$httpClient = new \Symfony\Component\HttpClient\HttpClient();
        $client = HttpClient::create([
            'verify_peer' => false,
            'verify_host' => false
        ]);

        $response = $client->request('GET', $apiUrl, [
            'headers' => [
                'X-HMAC' => $hmac,
                'X-Timestamp' => $timestamp,
            ],
            'timeout' => 60,
        ]);

        $statusCode = $response->getStatusCode();
        if ($statusCode !== 200) {
            throw new \Exception('Remote server returned status code: ' . $statusCode);
        }

        $data = $response->toArray();

        if (!isset($data['success']) || !$data['success']) {
            throw new \Exception('Remote server error: ' . ($data['message'] ?? 'Unknown error'));
        }

        // Decode base64 file content
        $fileContent = base64_decode($data['file_base64']);
        if ($fileContent === false) {
            throw new \Exception('Failed to decode base64 file content');
        }

        // Create unique filename
        $currentDatetime = new \DateTime();
        $currentDatetimeTimestamp = $currentDatetime->getTimestamp();
        $filename = $data['filename'] ?? 'downloaded_file';
        $fileExt = pathinfo($filename, PATHINFO_EXTENSION);
        $fileExtStr = $fileExt ? '.' . $fileExt : '';
        $fileUniqueName = $currentDatetimeTimestamp . 'ID' . $fileHash . $fileExtStr;

        $logger->notice("downloadFileFromRemote: fileUrl=$fileUrl, filename=$filename, fileHash=$fileHash");

        // Ensure storage directory exists
        if (!file_exists($storagePath)) {
            mkdir($storagePath, 0700, true);
            chmod($storagePath, 0700);
        }

        // Save file to storage
        $targetFile = $storagePath . DIRECTORY_SEPARATOR . $fileUniqueName;
        file_put_contents($targetFile, $fileContent);

        $logger->notice("downloadFileFromRemote: file saved in targetFile=$targetFile");

        // Calculate file size
        $filesize = strlen($fileContent) / 1024; // KB

        // Create Document entity
        $document = new Document($systemUser);
        $document->setDocumentHash($fileHash);
        $document->setUniquename($fileUniqueName);
        $document->setUploadDirectory(str_replace($this->container->get('kernel')->getProjectDir() . '/public/', '', $storagePath));
        $document->setSize($filesize);
        $document->setCleanOriginalname($filename);

        // Set document type using transformer
        $transformer = new GenericTreeTransformer($this->em, $systemUser, "DocumentTypeList", "UserdirectoryBundle");
        $documentTypeObject = $transformer->reverseTransform($documentType);
        if ($documentTypeObject) {
            $document->setType($documentTypeObject);
        }

        //return $document; //testing
        // Persist document
        if(1) {
            $this->em->persist($document);
            $this->em->flush();
        }

        // Generate thumbnails
        $userServiceUtil = $this->container->get('user_service_utility');
        $resImage = $userServiceUtil->generateTwoThumbnails($document);
        if ($resImage) {
            $logger->notice("Thumbnails generated for document ID=" . $document->getId());
        }

        return $document;
    }


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

        $examination = new Examination($systemUser); //uploadGoogleDocuments not used
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
        $fellappImportPopulateUtil = $this->container->get('fellapp_importpopulate_util');

        //recommendation1Name	recommendation1Title	recommendation1Institution	recommendation1AddressStreet1
        //recommendation1AddressStreet2	recommendation1AddressCity	recommendation1AddressState	recommendation1AddressZip	recommendation1AddressCountry

        $recommendationFirstName = $this->getValueByHeaderName($typeStr."FirstName",$rowData,$headers);
        $recommendationLastName = $this->getValueByHeaderName($typeStr."LastName",$rowData,$headers);
        $recommendationHash = $this->getValueByHeaderName($typeStr."Hash",$rowData,$headers);

        //echo "recommendationFirstName=".$recommendationFirstName."<br>";
        //echo "recommendationLastName=".$recommendationLastName."<br>";

        if( !$recommendationFirstName && !$recommendationLastName && !$recommendationHash ) {
            //echo "no ref<br>";
            return null;
        }

        if( $testOnly ) {
            return true;
        }

        //Capitalize
        if( $recommendationFirstName ) {
            $recommendationFirstName = $fellappImportPopulateUtil->capitalizeIfNotAllCapital($recommendationFirstName);
        }
        if( $recommendationLastName ) {
            $recommendationLastName = $fellappImportPopulateUtil->capitalizeIfNotAllCapital($recommendationLastName);
        }

        $reference = new Reference($author);

        //recommendation1FirstName
        $reference->setFirstName($recommendationFirstName);

        //recommendation1LastName
        $reference->setName($recommendationLastName);

        $recommendationHash = trim($recommendationHash);
        $reference->setRecLetterHashId($recommendationHash);

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
            $instStr = $fellappImportPopulateUtil->capitalizeIfNotAllCapital($instStr);
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
        $fellappImportPopulateUtil = $this->container->get('fellapp_importpopulate_util');

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
            $schoolName = $fellappImportPopulateUtil->capitalizeIfNotAllCapital($schoolName);
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

    public function getValueByHeaderName($keyName, $row, $headers) {
        $fellappImportPopulateUtil = $this->container->get('fellapp_importpopulate_util');
        return $fellappImportPopulateUtil->getValueByHeaderName($keyName, $row, $headers);
    }

    public function authenticateHmac( $hmacHeader, $timestampHeader ) {
        $logger = $this->container->get('logger');
        $fellappUtil = $this->container->get('fellapp_util');
        /////////// Verify HMAC Get secret key for HMAC verification ///////////
        //$userSecUtil = $this->container->get('user_security_utility');
        //$secretKey = $userSecUtil->getSiteSettingParameter('secretKey');
        $authenticated = false;
        $institutions = $fellappUtil->getFellowshipInstitutionsWithHash(); //Remote Server API Endpoint
        if( count($institutions) == 0 ) {
            $logger->notice('downloadApplicationDataAction: Error retrieving apiConnectionKey: No institutions found with apiConnectionKey');
//            return new JsonResponse([
//                'success' => false,
//                'message' => 'Error retrieving apiConnectionKey: No institutions found with apiConnectionKey'
//            ], 404);
        } else {
//            $apiConnectionKeys = array_map(fn($i) => $i->getApiConnectionKey(), $institutions);
//            foreach($apiConnectionKeys as $apiConnectionKey) {
//                // Verify HMAC (use hash_equals for constant-time comparison)
//                $expectedHmac = hash_hmac('sha256', 'fellapp-api:' . $timestampHeader, $apiConnectionKey);
//                if( hash_equals($expectedHmac, $hmacHeader) ) {
//                    $authenticated = true;
//                    break;
//                }
//            }

            $apiHashConnectionKeys = array_map(fn($i) => $i->getApiHashConnectionKey(), $institutions); //use apiHashConnectionKey
            foreach($apiHashConnectionKeys as $apiHashConnectionKey) {
                // Verify HMAC (use hash_equals for constant-time comparison)
                $expectedHmac = hash_hmac('sha256', 'fellapp-api:' . $timestampHeader, $apiHashConnectionKey);
                if( hash_equals($expectedHmac, $hmacHeader) ) {
                    $authenticated = true;
                    break;
                }
            }
        }
        $logger->notice('downloadApplicationDataAction: $authenticated='.$authenticated);

        return $authenticated;

//        if( !$authenticated ) {
//            return new JsonResponse([
//                'success' => false,
//                'message' => 'Invalid HMAC authentication'
//            ], 401);
//        }
        /////////// EOF Verify HMAC Get secret key for HMAC verification ///////////
    }

    public function getInstitutionApiHashConnectionKey( $getInstitutions=false )
    {
        $logger = $this->container->get('logger');
        $fellappUtil = $this->container->get('fellapp_util');

        $apiHashConnectionKey = null;
        $institutions = $fellappUtil->getFellowshipInstitutionsWithHash();
        if ($getInstitutions) {
            return $institutions;
        }
        if(count($institutions) == 1) {
            //$apiConnectionKey = $institutions[0]->getApiConnectionKey();
            $apiHashConnectionKey = $institutions[0]->getApiHashConnectionKey();
        } elseif(count($institutions) == 0) {
            $logger->warning('Error retrieving apiHashConnectionKey: No institutions found with apiHashConnectionKey');
        }
        elseif( count($institutions) > 1) {
            $ids = array_map(fn($i) => $i->getId().":".$i->getName(), $institutions);
            $idsString = implode(',', $ids);
            $logger->warning('Error retrieving apiHashConnectionKey: multiple institutions found with apiHashConnectionKey, count='
                . count($institutions) .
                ', Institution ids='.$idsString
            );
        } else {
            $logger->warning('Error retrieving apiHashConnectionKey: Logical error. Institution count='.count($institutions));
        }
        return $apiHashConnectionKey;
    }

    public function getInstitutionApiConnectionKey( $getInstitutions=false ) {
        $logger = $this->container->get('logger');
        $fellappUtil = $this->container->get('fellapp_util');

        $apiConnectionKey = null;
        $institutions = $fellappUtil->getFellowshipInstitutionsWithApiKey();
        if( $getInstitutions ) {
            return $institutions;
        }
        //exit('inst count='.count($institutions));

        if(count($institutions) == 1) {
            //$apiConnectionKey = $institutions[0]->getApiConnectionKey();
            $apiConnectionKey = $institutions[0]->getApiConnectionKey();
        } elseif(count($institutions) == 0) {
            $logger->warning('Error retrieving apiConnectionKey: No institutions found with apiConnectionKey');
        }
        elseif( count($institutions) > 1) {
            $ids = array_map(fn($i) => $i->getId().":".$i->getName(), $institutions);
            $idsString = implode(',', $ids);
            $logger->warning('Error retrieving apiConnectionKey: multiple institutions found with apiConnectionKey, count='
                . count($institutions) .
                ', Institution ids='.$idsString
            );
        } else {
            $logger->warning('Error retrieving apiConnectionKey: Logical error. Institution count='.count($institutions));
        }

        return $apiConnectionKey;
    }

    public function generateDocumentHash( $document ) {
        //$filename = $this->getFullServerPath();
        $filename = $document->getUniquename();

        if (!is_file($filename)) {
            return null;
        }

        $data = json_encode([
            'content' => hash_file('sha256', $filename),
            'mtime'   => filemtime($filename),
            'size'    => filesize($filename),
            'name'    => basename($filename),
        ]);

        return hash('sha256', $data);
    }

} 