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

use App\UserdirectoryBundle\Controller\ListController;


use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

class FellAppRecomLetterController extends ListController
{


    //https://view.online/fellowship-applications/submit-a-letter-of-recommendation
    //https://view.online/fellowship-applications/submit-a-letter-of-recommendation?HASHofLETTER
    #[Route(path: '/submit-a-letter-of-recommendation', name: 'fellapp_recom_letter', methods: ['GET'])]
    #[Template('AppFellAppBundle/RecomLetter/recomLetter.html.twig')]
    public function recomLetterAction(Request $request)
    {
//        if( false == $this->isGranted('ROLE_FELLAPP_ADMIN') ) {
//            return $this->redirect($this->generateUrl('fellapp-nopermission'));
//        }

        


    }



}
