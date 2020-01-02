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

namespace Oleg\UserdirectoryBundle\Repository;

use Doctrine\ORM\EntityRepository;


class UserWrapperRepository extends EntityRepository {

    public function findSimilarEntity( $user, $userStr=null ) {

        //echo "wrapper repo: user=".$user."<br>";

        $userWrapper = null;

        $query = $this->_em->createQueryBuilder()
            ->from('OlegUserdirectoryBundle:UserWrapper', 'list')
            ->select("list");
            //->leftJoin("list.user", "user")

        if( $userStr && $userStr != "" ) {
            //echo "userStr=".$userStr."<br>";
            $query->where("list.user=:user OR list.name=:userStr");
            $query->setParameters( array(
                'user' => $user,
                'userStr' => $userStr
            ));
        } else {

            if( $user ) {
                $userId = $user->getId();
                //echo "use userId=".$userId."<br>";
                $query->where("list.user=:user");
                $query->setParameters( array(
                    'user' => $userId
                ));
            } else {
                $query->where("1=0");
            }

        }

        $userWrappers = $query->getQuery()->getResult();
        //echo "userWrappers count=".count($userWrappers)."<br>";

        if( count($userWrappers) > 0 ) {
            $userWrapper = $userWrappers[0];
        }

        return $userWrapper;
    }


}

