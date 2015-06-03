<?php


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
            $query->where("list.user=:user OR list.userStr=:userStr");
            $query->setParameters( array(
                'user' => $user,
                'userStr' => $userStr
            ));
        } else {
            //echo "no userStr=".$userStr."<br>";
            $query->where("list.user=:user");
            $query->setParameters( array(
                'user' => $user
            ));
        }

        $userWrappers = $query->getQuery()->getResult();
        //echo "userWrappers count=".count($userWrappers)."<br>";

        if( count($userWrappers) > 0 ) {
            $userWrapper = $userWrappers[0];
        }

        return $userWrapper;
    }


}

