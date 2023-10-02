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

namespace App\UserdirectoryBundle\Controller;

use TusPhp\Tus\Server as TusServer;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class TusController extends OrderAbstractController
{
    // ...

//    /**
//     * Create tus server. Route matches /tus/, /tus and /tus/* endpoints.
//     *
//     * @Route("/tus/", name="tus_post")
//     * @Route("/tus/{token?}", name="tus", requirements={"token"=".+"})
//     *
//     * @param TusService $tusService
//     *
//     * @return Response
//     */

    #[Route(path: '/tus/', name: 'tus_post', options: ['expose' => true])]
    #[Route(path: '/tus/{token?}', name: 'tus', requirements: ['token' => '.+'], options: ['expose' => true])]
    public function server(TusServer $server)
    {
        return $server->serve();
    }

    // ...
}