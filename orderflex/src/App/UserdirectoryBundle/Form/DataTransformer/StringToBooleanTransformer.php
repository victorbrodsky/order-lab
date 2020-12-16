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
 * Created by JetBrains PhpStorm.
 * User: oli2002
 * Date: 9/12/13
 * Time: 3:47 PM
 * To change this template use File | Settings | File Templates.
 */

namespace App\UserdirectoryBundle\Form\DataTransformer;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Doctrine\Common\Persistence\ObjectManager;

class StringToBooleanTransformer implements DataTransformerInterface
{
    private $em;
    private $user;

    public function __construct(EntityManagerInterface $em=null, $user=null)
    {
        $this->em = $em;
        $this->user = $user;
    }

    /**
     * Transforms a string to boolean.
     */
    public function transform($string)
    {
        if( strpos($string,'Yes') !== false || strpos($string,'yes') !== false ) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Transforms boolean to a string.
     */
    public function reverseTransform($boolean)
    {
        if( $boolean == true ) {
            return 'Yes';
        } else {
            return 'No';
        }
    }

}