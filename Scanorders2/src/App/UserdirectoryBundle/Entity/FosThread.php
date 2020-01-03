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
 * User: ch3
 * Date: 10/6/2017
 * Time: 1:52 PM
 */

namespace App\UserdirectoryBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use FOS\CommentBundle\Entity\Thread as FosBaseThread;

/**
 * @ORM\Entity
 * @ORM\Table(name="user_fosThread")
 * @ORM\ChangeTrackingPolicy("DEFERRED_EXPLICIT")
 */
class FosThread extends FosBaseThread
{

    /**
     * @var string $id
     *
     * @ORM\Id
     * @ORM\Column(type="string")
     */
    protected $id;

}