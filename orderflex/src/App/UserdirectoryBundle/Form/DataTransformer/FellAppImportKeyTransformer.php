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

use App\FellAppBundle\Entity\FellAppImportKey;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

class FellAppImportKeyTransformer implements DataTransformerInterface
{
    private $em;
    private $user;
    //private $className;
    
    public function __construct(EntityManagerInterface $em=null, $user=null) //$className=null
    {
        $this->em = $em;
        $this->user = $user;
        //$this->className = $className;
    }

    /**
     * Transforms id or name to an object
     */
    public function transform($value): mixed
    {
        //dump($value);
        //exit('111');
        return $value;
    }

    /**
     * Transforms a string (number) to an object (i.e. stain).
     */
    public function reverseTransform($values)
    {
        //dump($values);
        //exit('111');
        if ($values === null || $values === '') {
            return [];
        }

        // Normalize: convert comma-separated string to array
        if (is_string($values)) {
            $values = array_map('trim', explode(',', $values));
        }

        $result = [];

        foreach ($values as $value) {
            if (is_numeric($value)) {
                // existing entity
                $result[] = $this->em->getRepository(FellAppImportKey::class)->find($value);
            } else {
                // NEW tag → create entity
                $key = new FellAppImportKey();
                $key->setName($value);
                $key->setApiKeyValue($value);
                $key->setCreator($this->user);
                $key->setCreatedate(new \DateTime());
                $key->setType('user-added');

                $fullClassName = "App\\"."FellAppBundle"."\\Entity\\"."FellAppImportKey";
                $query = $this->em->createQuery('SELECT MAX(c.orderinlist) as maxorderinlist FROM '.$fullClassName.' c');
                $nextorder = $query->getSingleResult()['maxorderinlist']+10;
                $key->setOrderinlist($nextorder);

                $result[] = $key;
            }
        }

        return $result;
    }


}