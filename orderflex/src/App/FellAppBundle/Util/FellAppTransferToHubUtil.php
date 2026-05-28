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

use Symfony\Component\HttpFoundation\JsonResponse;
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

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx as XlsxReader;
use PhpOffice\PhpSpreadsheet\IOFactory;


//$fellappImportPopulateUtil = $this->container->get('fellapp_importpopulate_util');

class FellAppTransferToHubUtil {

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

    public function transferParametersToHub() {
        $userSecUtil = $this->container->get('user_security_utility');
        $logger = $this->container->get('logger');
        $em = $this->em;

        
    }
    
} 