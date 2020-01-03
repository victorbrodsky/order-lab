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
 * Date: 8/24/15
 * Time: 11:08 AM
 */

namespace App\CallLogBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Form\Extension\Core\DataTransformer\DateTimeToStringTransformer;

//one patient has many CallInfo?

///**
// * @ORM\Entity
// * @ORM\Table(name="calllog_callInfo")
// */

class CallInfo {


} 