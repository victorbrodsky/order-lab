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

namespace App\FellAppBundle\Controller;

use App\FellAppBundle\Entity\FellAppStatus;
use App\FellAppBundle\Entity\FellowshipApplication;
use App\UserdirectoryBundle\Controller\OrderAbstractController;

use App\UserdirectoryBundle\Entity\Document;
use App\UserdirectoryBundle\Entity\Institution;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpClient\HttpClient;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx as XlsxReader;
use PhpOffice\PhpSpreadsheet\IOFactory;


//API key $hashkey is generated on Caller and Remote servers must be the same in order for Remote server data back.
//Use Hash-based message authentication code (or HMAC)
//HMAC is used to authenticate API calls between Caller and Remote servers using a shared secret key

/*
In C:\Users\cinav\Documents\WCMC\ORDER\order-lab\orderflex\src\App\FellAppBundle\Controller\FellAppTransferParametersHubController.php,
make API calls between local and remote servers using the same authentication as in C:\Users\cinav\Documents\WCMC\ORDER\order-lab\orderflex\src\App\FellAppBundle\Controller\FellAppRetrievalController.php
Transfer the parameters from the local server from entity
C:\Users\cinav\Documents\WCMC\ORDER\order-lab\orderflex\src\App\UserdirectoryBundle\Entity\FellowshipSubspecialty.php
to the remote server (HUB) to the entity
C:\Users\cinav\Documents\WCMC\ORDER\order-lab\orderflex\src\App\FellAppBundle\Entity\GlobalFellowshipSpecialty.php:
$duration, $submissionStart, $submissionEnd, $acceptingApplication.
On the remote server, if the current date is between $submissionStart and $submissionEnd, then set $acceptingApplication to true on the GlobalFellowshipSpecialty

*/


#[Route(path: '/')]
class FellAppTransferParametersHubController extends OrderAbstractController
{

}
