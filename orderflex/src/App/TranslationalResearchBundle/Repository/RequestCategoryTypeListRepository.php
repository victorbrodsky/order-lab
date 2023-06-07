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
 * Date: 4/23/14
 * Time: 3:16 PM
 * To change this template use File | Settings | File Templates.
 */

namespace App\TranslationalResearchBundle\Repository;

//use Doctrine\ORM\EntityRepository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

class RequestCategoryTypeListRepository extends ServiceEntityRepository {

    public function findByProjectSpecialties($specialty)
    {
        $entityManager = $this->getEntityManager();

        $sql = "
                SELECT list
                FROM AppTranslationalResearchBundle:RequestCategoryTypeList list
                INNER JOIN AppTranslationalResearchBundle:SpecialtyList specialty
                WHERE list.id = :id
            ";
        $query = $entityManager->createQuery($sql)->setParameter('id', $specialty);

        return $query->getOneOrNullResult();
    }

    public function findOneByProjectSpecialties($id) {
        $em = $this->_em;
        $categoryDb = $em->getRepository('AppTranslationalResearchBundle:RequestCategoryTypeList')->findOneByProjectSpecialties($id);
        return $categoryDb;
    }

}